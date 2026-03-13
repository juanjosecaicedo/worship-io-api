<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    // Webhook de Stripe
    public function stripe(Request $request): Response
    {
        $payload = $request->all();
        $event   = $payload['type'] ?? null;

        match ($event) {
            'invoice.paid'                => $this->handleInvoicePaid($payload),
            'invoice.payment_failed'      => $this->handlePaymentFailed($payload),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($payload),
            default                       => null,
        };

        return response('OK', 200);
    }

    // Webhook de MercadoPago
    public function mercadopago(Request $request): Response
    {
        $payload = $request->all();
        $topic   = $payload['topic'] ?? $payload['type'] ?? null;

        match ($topic) {
            'payment'      => $this->handleMPPayment($payload),
            'subscription' => $this->handleMPSubscription($payload),
            default        => null,
        };

        return response('OK', 200);
    }

    //Webhook de paypal
    public function paypal(Request $request): Response
    {
        $payload = $request->all();
        $event   = $payload['event_type'] ?? null;

        match ($event) {
            'invoice.paid'                => $this->handleInvoicePaid($payload),
            'invoice.payment_failed'      => $this->handlePaymentFailed($payload),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($payload),
            default                       => null,
        };

        return response('OK', 200);
    }

    // ─── Handlers privados ────────────────────────────────

    private function handleInvoicePaid(array $payload): void
    {
        $stripeSubId = $payload['data']['object']['subscription'] ?? null;

        if (! $stripeSubId) return;

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubId)->first();

        if (! $subscription) return;

        // Registrar factura pagada
        SubscriptionInvoice::create([
            'subscription_id'    => $subscription->id,
            'user_id'            => $subscription->user_id,
            'amount'             => $payload['data']['object']['amount_paid'],
            'currency'           => strtoupper($payload['data']['object']['currency']),
            'status'             => 'paid',
            'gateway_invoice_id' => $payload['data']['object']['id'],
            'paid_at'            => now(),
        ]);

        // Renovar período
        $subscription->update([
            'status'               => 'active',
            'current_period_start' => now(),
            'current_period_end'   => now()->addMonth(),
        ]);
    }

    private function handlePaymentFailed(array $payload): void
    {
        $stripeSubId = $payload['data']['object']['subscription'] ?? null;

        if (! $stripeSubId) return;

        Subscription::where('stripe_subscription_id', $stripeSubId)
            ->update(['status' => 'past_due']);
    }

    private function handleSubscriptionDeleted(array $payload): void
    {
        $stripeSubId = $payload['data']['object']['id'] ?? null;

        if (! $stripeSubId) return;

        Subscription::where('stripe_subscription_id', $stripeSubId)
            ->update([
                'status'       => 'expired',
                'ends_at'      => now(),
            ]);
    }

    private function handleMPPayment(array $payload): void
    {
        // Implementar según la API de MercadoPago
    }

    private function handleMPSubscription(array $payload): void
    {
        // Implementar según la API de MercadoPago
    }
}
