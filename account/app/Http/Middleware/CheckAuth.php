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

        // Se a sessão foi invalidada (logout), não reautenticar
        // Verificar se a sessão atual existe no banco
        $currentSessionId = $request->session()->getId();
        $currentSession = Session::where('id', $currentSessionId)->first();

        // Se a sessão não existe no banco, significa que foi deletada no logout
        // Não tentar reautenticar
        if (!$currentSession) {
            return $next($request);
        }

        // Se a sessão existe mas não tem user_id, não reautenticar
        if ($currentSession && !$currentSession->user_id) {
            return $next($request);
        }

        // Se a sessão tem user_id, verificar se o usuário ainda existe
        if ($currentSession && $currentSession->user_id) {
            $user = User::find($currentSession->user_id);
            if ($user) {
                // Reutilizar a sessão existente em vez de criar uma nova
                Auth::setUser($user);
                return $next($request);
            } else {
                // Usuário não existe mais, limpar a sessão
                $currentSession->delete();
                return $next($request);
            }
        }

        return $next($request);
    }
}

