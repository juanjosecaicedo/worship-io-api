<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\ChangePlanRequest;
use App\Http\Requests\Subscription\CreateSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(protected SubscriptionService $service) {}

    /**
     * View user's active subscription
     */
    public function show(Request $request): JsonResponse
    {
        $subscription = $request->user()
            ->activeSubscription()
            ->with('plan.features', 'groups')
            ->first();

        if (! $subscription) {
            return response()->json([
                'message' => 'You do not have an active subscription.',
                'data' => null,
            ]);
        }

        return response()->json([
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Subscribe to a plan
     */
    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        $plan = SubscriptionPlan::where('slug', $request->plan_slug)
            ->where('is_active', true)
            ->firstOrFail();

        // If the user already has an active subscription to the same plan
        $current = $request->user()->activeSubscription()->first();

        if ($current && $current->plan_id === $plan->id) {
            return response()->json([
                'message' => 'You already have an active subscription to this plan.',
            ], 409);
        }

        $subscription = $this->service->create(
            $request->user(),
            $plan,
            $request->gateway ?? 'stripe'
        );

        return response()->json([
            'message' => 'Subscription created successfully.',
            'data' => new SubscriptionResource($subscription->load('plan.features')),
        ], 201);
    }

    /**
     * Change plan (upgrade/downgrade)
     */
    public function changePlan(ChangePlanRequest $request): JsonResponse
    {
        $subscription = $request->user()->activeSubscription()->first();

        abort_unless(! $subscription, 404, 'You do not have an active subscription.');

        $newPlan = SubscriptionPlan::where('slug', $request->plan_slug)
            ->where('is_active', true)
            ->firstOrFail();

        $subscription = $this->service->changePlan($subscription, $newPlan);

        return response()->json([
            'message' => 'Plan updated successfully.',
            'data' => new SubscriptionResource($subscription->load('plan.features')),
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request): JsonResponse
    {
        $subscription = $request->user()->activeSubscription()->first();

        abort_unless(! $subscription, 404, 'You do not have an active subscription.');
        abort_if($subscription->plan->price === 0, 422, 'The free plan cannot be canceled.');

        $subscription = $this->service->cancel($subscription);

        return response()->json([
            'message' => 'Subscription canceled. You will have access until the end of the period.',
            'data' => new SubscriptionResource($subscription),
        ]);
    }
}
