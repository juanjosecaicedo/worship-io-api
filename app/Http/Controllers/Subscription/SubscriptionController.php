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
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $subscription = $request->user()
            ->activeSubscription()
            ->with('plan.features', 'groups')
            ->first();

        if (! $subscription) {
            return response()->json([
                'message' => 'No tienes una suscripción activa.',
                'data'    => null,
            ]);
        }

        return response()->json([
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Subscribe to a plan
     * @param CreateSubscriptionRequest $request
     * @return JsonResponse
     */
    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        $plan = SubscriptionPlan::where('slug', $request->plan_slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Si ya tiene subscripción activa al mismo plan
        $current = $request->user()->activeSubscription()->first();

        if ($current && $current->plan_id === $plan->id) {
            return response()->json([
                'message' => 'Ya tienes una suscripción activa en este plan.',
            ], 409);
        }

        $subscription = $this->service->create(
            $request->user(),
            $plan,
            $request->gateway ?? 'stripe'
        );

        return response()->json([
            'message' => 'Suscripción creada correctamente.',
            'data'    => new SubscriptionResource($subscription->load('plan.features')),
        ], 201);
    }


    /**
     * Change plan (upgrade/downgrade)
     * @param ChangePlanRequest $request
     * @return JsonResponse
     */
    public function changePlan(ChangePlanRequest $request): JsonResponse
    {
        $subscription = $request->user()->activeSubscription()->first();

        abort_unless(!$subscription, 404, 'No tienes una suscripción activa.');

        $newPlan = SubscriptionPlan::where('slug', $request->plan_slug)
            ->where('is_active', true)
            ->firstOrFail();

        $subscription = $this->service->changePlan($subscription, $newPlan);

        return response()->json([
            'message' => 'Plan actualizado correctamente.',
            'data'    => new SubscriptionResource($subscription->load('plan.features')),
        ]);
    }


    /**
     * Cancel subscription
     * @param Request $request
     * @return JsonResponse
     */
    public function cancel(Request $request): JsonResponse
    {
        $subscription = $request->user()->activeSubscription()->first();

        abort_unless(!$subscription, 404, 'No tienes una suscripción activa.');
        abort_if($subscription->plan->price === 0, 422, 'El plan gratuito no se puede cancelar.');

        $subscription = $this->service->cancel($subscription);

        return response()->json([
            'message' => 'Suscripción cancelada. Tendrás acceso hasta el final del período.',
            'data'    => new SubscriptionResource($subscription),
        ]);
    }
}
