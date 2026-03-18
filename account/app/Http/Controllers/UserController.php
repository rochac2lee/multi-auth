<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;
use App\Http\Resources\UserResource;
use App\Models\Niche;
use App\Models\EmailChangeToken;
use App\Mail\EmailChangeCodeMail;

class UserController extends Controller
{
    public function home(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $apps = $user->apps()->get()->map(fn($app) => [
            'id' => $app->id,
            'name' => $app->name,
            'logo' => $app->logo,
            'redirect_uri' => $app->redirect_uri
                ? rtrim(preg_replace('/[?#].*$/', '', $app->redirect_uri), '/') . '/'
                : null,
        ]);

        return Inertia::render('Home', [
            'user' => UserResource::make($user)->toArray($request),
            'avatarCdnUrl' => rtrim((string) env('AVATAR_CDN_URL'), '/'),
            'apps' => $apps,
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ]);

        $file = $request->file('avatar');
        if (!$file) {
            return back()->withErrors(['avatar' => 'Arquivo não enviado.']);
        }

        try {
            if ($user->avatar) {
                Storage::disk('s3')->delete($user->avatar);
            }

            $path = $file->storePublicly('avatar', 's3');

            if ($path === false || $path === 0 || $path === '0' || $path === '') {
                return back()->withErrors(['avatar' => 'Falha ao enviar para o S3.']);
            }

            $user->avatar = $path;
            $user->save();
        } catch (Throwable $e) {
            Log::error('Avatar upload failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['avatar' => 'Falha ao enviar imagem.']);
        }

        if ($request->expectsJson()) {
            return response()->json(['uploaded' => true]);
        }

        return back();
    }

    public function deleteAvatar(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->avatar) {
            Storage::disk('s3')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return back();
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            // Pode vir tanto do modal de edição de cadastro (obrigatório)
            // quanto do modal de informações adicionais (não obrigatório).
            'nome_completo' => ['sometimes', 'required', 'string', 'max:100'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'senha' => ['nullable', 'string', 'min:6', 'max:100'],

            // Campos do modal "Informações adicionais"
            'photography_studio' => ['nullable', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:100'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'nichos_principais' => ['nullable', 'array'],
            'nichos_principais.*' => ['string', 'max:100'],
        ]);

        if ($request->has('nome_completo')) {
            $user->name = $data['nome_completo'];
        }

        // Se não vier no request, não altera o country.
        if ($request->has('country_id')) {
            $user->country_id = $data['country_id'];
        }

        if ($request->filled('senha')) {
            $user->password = Hash::make($request->string('senha'));
        }

        if ($request->has('photography_studio')) {
            $user->photography_studio = $data['photography_studio'];
        }

        if ($request->has('surname')) {
            $user->surname = $data['surname'];
        }

        if ($request->has('instagram')) {
            $user->instagram = $data['instagram'];
        }

        // Nichos são many-to-many via pivot.
        if ($request->has('nichos_principais')) {
            $names = $data['nichos_principais'] ?? [];
            $uniqueNames = array_values(array_unique($names));

            $found = Niche::query()
                ->whereIn('name', $uniqueNames)
                ->pluck('name')
                ->all();

            if (count($found) !== count($uniqueNames)) {
                return response()->json(['message' => 'Invalid niches'], 422);
            }

            $nicheIds = Niche::query()
                ->whereIn('name', $uniqueNames)
                ->pluck('id')
                ->all();

            $user->niches()->sync($nicheIds);
        }

        $user->save();

        if ($request->expectsJson()) {
            return response()->json(['updated' => true]);
        }

        return back();
    }

    public function requestEmailChangeCode(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate([
            'newMail' => ['required', 'email', 'max:255'],
        ]);

        $newEmail = (string) $data['newMail'];

        if (strcasecmp($newEmail, (string) $user->email) === 0) {
            return response()->json(['message' => 'E-mail is already the same.'], 422);
        }

        $code = (string) random_int(100000, 999999);

        EmailChangeToken::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        $token = EmailChangeToken::createForUser($user->id, $newEmail, $code);

        try {
            Mail::mailer('smtp')->to($newEmail)->send(
                new EmailChangeCodeMail($newEmail, $code)
            );
        } catch (Throwable $e) {
            Log::error('Email change code send failed', [
                'user_id' => $user->id,
                'new_email' => $newEmail,
                'error' => $e->getMessage(),
            ]);

            return response()->json(
                ['message' => 'Falha ao enviar o código. Tente novamente.'],
                500
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['sent' => true]);
        }

        return back();
    }

    public function verifyEmailChangeCode(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate([
            'newMail' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
        ]);

        $newEmail = (string) $data['newMail'];
        $code = (string) $data['code'];

        $token = EmailChangeToken::query()
            ->where('user_id', $user->id)
            ->where('new_email', $newEmail)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->where('code', $code)
            ->first();

        if (!$token || !$token->isValid()) {
            return response()->json(['message' => 'Invalid or expired code.'], 422);
        }

        $user->email = $newEmail;
        $user->save();

        $token->update(['used_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['verified' => true]);
        }

        return back();
    }
}
