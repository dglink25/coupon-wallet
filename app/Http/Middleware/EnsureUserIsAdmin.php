<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint l'acces au back-office administrateur.
 *
 * Applique apres le middleware "auth" : on suppose ici que l'utilisateur
 * est deja authentifie. On verifie simplement son role.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, "Acces reserve a l'administrateur.");
        }

        return $next($request);
    }
}
