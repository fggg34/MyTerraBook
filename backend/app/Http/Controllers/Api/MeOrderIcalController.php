<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderIcsBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MeOrderIcalController extends Controller
{
    public function __construct(
        private readonly OrderIcsBuilder $icsBuilder,
    ) {}

    public function show(Request $request, Order $order): Response
    {
        $user = $request->user();
        if ($order->user_id !== $user->id && $user->role !== UserRole::Admin) {
            abort(403);
        }

        $order->load('car');

        $body = $this->icsBuilder->forOrder($order);

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="order-'.$order->reference.'.ics"',
        ]);
    }
}
