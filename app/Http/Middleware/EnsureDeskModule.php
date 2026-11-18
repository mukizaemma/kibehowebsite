<?php

namespace App\Http\Middleware;

use App\Services\DeskAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeskModule
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();
        if (! $user || ! app(DeskAccess::class)->allowsModule($user, $module)) {
            abort(403, 'You do not have access to this desk.');
        }

        return $next($request);
    }
}
