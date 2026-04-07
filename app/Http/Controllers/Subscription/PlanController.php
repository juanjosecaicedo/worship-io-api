<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    /**
     * List active plans
     * 
     * Returns a list of all subscription plans that are currently active.
     */
    public function index(): JsonResponse
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->with('features')
            ->orderBy('price')
            ->get();

        return response()->json([
            'data' => SubscriptionPlanResource::collection($plans),
        ]);
    }

    /**
     * Get plan details
     */
    public function show(SubscriptionPlan $plan): JsonResponse
    {
        $plan->load('features');

        return response()->json([
            'data' => new SubscriptionPlanResource($plan),
        ]);
    }
}
