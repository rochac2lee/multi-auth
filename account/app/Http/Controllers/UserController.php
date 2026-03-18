<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Throwable;
use App\Http\Resources\UserResource;

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
            'nome_completo' => ['required', 'string', 'max:100'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'senha' => ['nullable', 'string', 'min:6', 'max:100'],
        ]);

        $user->name = $data['nome_completo'];
        $user->country_id = $data['country_id'] ?? null;

        if ($request->filled('senha')) {
            $user->password = Hash::make($request->string('senha'));
        }

        $user->save();

        if ($request->expectsJson()) {
            return response()->json(['updated' => true]);
        }

        return back();
    }
}
