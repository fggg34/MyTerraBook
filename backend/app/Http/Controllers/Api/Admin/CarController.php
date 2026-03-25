<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCarRequest;
use App\Http\Resources\CarResource;
use App\Models\Car;

class CarController extends Controller
{
    public function index()
    {
        return CarResource::collection(Car::query()->with(['category', 'images'])->latest()->paginate(20));
    }

    public function store(StoreCarRequest $request)
    {
        $car = Car::query()->create($request->validated());

        return CarResource::make($car->load(['category', 'images']));
    }

    public function show(Car $car)
    {
        return CarResource::make($car->load(['category', 'images']));
    }

    public function update(StoreCarRequest $request, Car $car)
    {
        $car->update($request->validated());

        return CarResource::make($car->load(['category', 'images']));
    }

    public function destroy(Car $car)
    {
        $car->delete();

        return response()->noContent();
    }
}
