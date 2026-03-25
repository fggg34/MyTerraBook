<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function index()
    {
        return CouponResource::collection(Coupon::query()->latest()->paginate(20));
    }

    public function store(StoreCouponRequest $request)
    {
        return CouponResource::make(Coupon::query()->create($request->validated()));
    }

    public function show(Coupon $coupon)
    {
        return CouponResource::make($coupon);
    }

    public function update(StoreCouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validated());

        return CouponResource::make($coupon);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->noContent();
    }
}
