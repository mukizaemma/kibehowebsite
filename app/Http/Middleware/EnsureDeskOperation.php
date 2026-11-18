<?php

namespace App\Http\Middleware;

use App\Services\DeskAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeskOperation
{
    public function handle(Request $request, Closure $next, string $operation): Response
    {
        $user = $request->user();
        if (! $user || ! app(DeskAccess::class)->allowsOperation($user, $operation)) {
            abort(403, 'You do not have access to this action.');
        }

        return $next($request);
    }
}
