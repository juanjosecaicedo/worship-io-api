<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionInvoiceResource;
use App\Models\SubscriptionInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * User invoice history
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $invoices = SubscriptionInvoice::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => SubscriptionInvoiceResource::collection($invoices),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page'    => $invoices->lastPage(),
                'total'        => $invoices->total(),
            ],
        ]);
    }

    /**
     * See specific invoice
     * @param Request $request
     * @param SubscriptionInvoice $invoice
     * @return JsonResponse
     */
    public function show(Request $request, SubscriptionInvoice $invoice): JsonResponse
    {
        abort_if($invoice->user_id !== $request->user()->id, 403);

        return response()->json([
            'data' => new SubscriptionInvoiceResource($invoice),
        ]);
    }
}
