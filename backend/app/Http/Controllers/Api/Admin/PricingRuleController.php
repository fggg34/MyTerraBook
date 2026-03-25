<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePricingRuleRequest;
use App\Http\Resources\PricingRuleResource;
use App\Models\PricingRule;

class PricingRuleController extends Controller
{
    public function index()
    {
        return PricingRuleResource::collection(PricingRule::query()->latest()->paginate(20));
    }

    public function store(StorePricingRuleRequest $request)
    {
        return PricingRuleResource::make(PricingRule::query()->create($request->validated()));
    }

    public function show(PricingRule $pricingRule)
    {
        return PricingRuleResource::make($pricingRule);
    }

    public function update(StorePricingRuleRequest $request, PricingRule $pricingRule)
    {
        $pricingRule->update($request->validated());

        return PricingRuleResource::make($pricingRule);
    }

    public function destroy(PricingRule $pricingRule)
    {
        $pricingRule->delete();

        return response()->noContent();
    }
}
