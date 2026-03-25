<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExtraRequest;
use App\Http\Resources\ExtraResource;
use App\Models\Extra;

class ExtraController extends Controller
{
    public function index()
    {
        return ExtraResource::collection(Extra::query()->latest()->paginate(20));
    }

    public function store(StoreExtraRequest $request)
    {
        return ExtraResource::make(Extra::query()->create($request->validated()));
    }

    public function show(Extra $extra)
    {
        return ExtraResource::make($extra);
    }

    public function update(StoreExtraRequest $request, Extra $extra)
    {
        $extra->update($request->validated());

        return ExtraResource::make($extra);
    }

    public function destroy(Extra $extra)
    {
        $extra->delete();

        return response()->noContent();
    }
}
