<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Session;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se já estiver autenticado, continuar
        if (Auth::check()) {
            return $next($request);
        }

        // Primeiro, verificar se a sessão atual tem user_id
        $currentSessionId = $request->session()->getId();
        $currentSession = Session::where('id', $currentSessionId)->first();

        if ($currentSession && $currentSession->user_id) {
            $user = User::find($currentSession->user_id);
            if ($user) {
                // Reutilizar a sessão existente em vez de criar uma nova
                Auth::setUser($user);
                return $next($request);
            }
        }

        // Se não, verificar se há uma sessão ativa no banco de dados com user_id
        // Buscar nas últimas sessões ativas (últimas 2 horas) ordenadas por last_activity
        $twoHoursAgo = now()->subHours(2)->timestamp;
        $existingSession = Session::where('last_activity', '>', $twoHoursAgo)
            ->whereNotNull('user_id')
            ->orderBy('last_activity', 'desc')
            ->first();

        if ($existingSession && $existingSession->user_id) {
            $user = User::find($existingSession->user_id);

            if ($user) {
                // Limpar TODAS as outras sessões do mesmo usuário
                // Isso garante que apenas uma sessão seja mantida por usuário
                Session::where('user_id', $user->id)
                    ->delete();

                // Associar a sessão atual ao user_id, criando uma única sessão ativa
                $request->session()->save();
                Session::where('id', $currentSessionId)
                    ->update(['user_id' => $user->id]);

                // Autenticar o usuário
                Auth::setUser($user);
            }
        }

        return $next($request);
    }
}

