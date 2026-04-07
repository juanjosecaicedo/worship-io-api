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
     * Get active subscription
     */
    public function show(Request $request): JsonResponse
    {
        $subscription = $request->user()
            ->activeSubscription()
            ->with('plan.features', 'groups')
            ->first();

        if (! $subscription) {
            return response()->json([
                'message' => __('subscriptions.not_active'),
                'data' => null,
            ]);
        }

        return response()->json([
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Create subscription
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
                'message' => __('subscriptions.already_subscribed'),
            ], 409);
        }

        $subscription = $this->service->create(
            $request->user(),
            $plan,
            $request->gateway ?? 'stripe'
        );

        return response()->json([
            'message' => __('subscriptions.created_success'),
            'data' => new SubscriptionResource($subscription->load('plan.features')),
        ], 201);
    }

    /**
     * Change plan
     * 
     * Upgrades or downgrades the current subscription plan.
     */
    public function changePlan(ChangePlanRequest $request): JsonResponse
    {
        $subscription = $request->user()->activeSubscription()->first();

        abort_unless((bool)$subscription, 404, __('subscriptions.not_active'));

        $newPlan = SubscriptionPlan::where('slug', $request->plan_slug)
            ->where('is_active', true)
            ->firstOrFail();

        $subscription = $this->service->changePlan($subscription, $newPlan);

        return response()->json([
            'message' => __('subscriptions.updated_success'),
            'data' => new SubscriptionResource($subscription->load('plan.features')),
        ]);
    }

    /**
     * Cancel subscription
     * 
     * Cancels the active subscription. Access remains until end of period.
     */
    public function cancel(Request $request): JsonResponse
    {
        $subscription = $request->user()->activeSubscription()->first();

        abort_unless((bool)$subscription, 404, __('subscriptions.not_active'));
        abort_if($subscription->plan->price === 0, 422, __('subscriptions.cannot_cancel_free'));

        $subscription = $this->service->cancel($subscription);

        return response()->json([
            'message' => __('subscriptions.canceled_success'),
            'data' => new SubscriptionResource($subscription),
        ]);
    }
}
