<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentGatewayRequest;
use App\Models\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    public function index(): View
    {
        $gateways = PaymentGateway::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.payment-gateways.index', compact('gateways'));
    }

    public function store(StorePaymentGatewayRequest $request): RedirectResponse
    {
        PaymentGateway::create($request->validated());

        return back()->with('success', 'Payment gateway created successfully.');
    }

    public function update(
        StorePaymentGatewayRequest $request,
        PaymentGateway $gateway
    ): RedirectResponse {
        $gateway->update($request->validated());

        return back()->with('success', 'Payment gateway updated successfully.');
    }

    public function destroy(PaymentGateway $gateway): RedirectResponse
    {
        if ($gateway->transactions()->exists()) {
            return back()->with(
                'error',
                'This gateway has payment history and cannot be deleted.'
            );
        }

        $gateway->delete();

        return back()->with('success', 'Payment gateway deleted successfully.');
    }
}