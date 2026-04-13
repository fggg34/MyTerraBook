<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Models\Order;
use PHPUnit\Framework\TestCase;

class OrderStateMachineTest extends TestCase
{
    public function test_allowed_order_transitions(): void
    {
        $this->assertTrue(Order::isAllowedOrderTransition(OrderStatus::Pending, OrderStatus::Confirmed));
        $this->assertTrue(Order::isAllowedOrderTransition(OrderStatus::Pending, OrderStatus::StandBy));
        $this->assertFalse(Order::isAllowedOrderTransition(OrderStatus::Cancelled, OrderStatus::Pending));
    }

    public function test_allowed_rental_transitions(): void
    {
        $this->assertTrue(Order::isAllowedRentalTransition(RentalStatus::Upcoming, RentalStatus::Started));
        $this->assertTrue(Order::isAllowedRentalTransition(RentalStatus::Started, RentalStatus::Terminated));
        $this->assertFalse(Order::isAllowedRentalTransition(RentalStatus::Terminated, RentalStatus::Started));
    }
}
