<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'amount'              => $this->amount,
            'amount_formatted'    => $this->formattedAmount(),
            'currency'            => $this->currency,
            'status'              => $this->status,
            'gateway_invoice_id'  => $this->gateway_invoice_id,
            'gateway_payment_url' => $this->gateway_payment_url,
            'paid_at'             => $this->paid_at?->toDateTimeString(),
            'due_at'              => $this->due_at?->toDateTimeString(),
            'created_at'          => $this->created_at->toDateTimeString(),
        ];
    }
}
