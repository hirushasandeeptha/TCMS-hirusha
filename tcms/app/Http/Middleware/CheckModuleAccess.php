<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: CheckModuleAccess
 *
 * Verifies the authenticated user has an active subscription to the
 * requested module. Route parameter {module} must match the module slug
 * in the system_modules table.
 *
 * Usage in routes:
 *   Route::middleware('module:student')->group(...);
 */
final class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->hasModuleAccess($module)) {
            abort(
                Response::HTTP_FORBIDDEN,
                "You do not have an active subscription to the '{$module}' module. Please visit the Marketplace to subscribe."
            );
        }

        return $next($request);
    }
}
