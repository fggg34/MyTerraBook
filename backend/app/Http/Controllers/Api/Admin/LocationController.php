<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;

class LocationController extends Controller
{
    public function index()
    {
        return LocationResource::collection(Location::query()->latest()->paginate(20));
    }

    public function store(StoreLocationRequest $request)
    {
        return LocationResource::make(Location::query()->create($request->validated()));
    }

    public function show(Location $location)
    {
        return LocationResource::make($location);
    }

    public function update(StoreLocationRequest $request, Location $location)
    {
        $location->update($request->validated());

        return LocationResource::make($location);
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return response()->noContent();
    }
}
