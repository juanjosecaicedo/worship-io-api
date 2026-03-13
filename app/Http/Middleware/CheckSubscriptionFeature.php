<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return response()->json([
                'message' => 'No tienes una suscripción activa.',
                'upgrade' => true,
            ], 403);
        }

        if (!$subscription->hasFeature($feature)) {
            return response()->json([
                'message' => 'Tu plan no incluye esta funcionalidad.',
                'feature' => $feature,
                'upgrade' => true,
            ], 403);
        }

        return $next($request);
    }
}
