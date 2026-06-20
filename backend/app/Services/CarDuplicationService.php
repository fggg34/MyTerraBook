<?php

namespace App\Services;

use App\Models\Car;
use App\Models\OutOfHoursFee;
use App\Models\SpecialPrice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CarDuplicationService
{
    /**
     * @return array<int, Car>
     */
    public function duplicate(Car $car, int $copies = 1): array
    {
        $copies = max(1, $copies);

        $car->loadMissing([
            'characteristics',
            'rentalOptions',
            'rentalConditions',
            'locations',
            'dailyFares',
            'hourlyFares',
            'extraHourFares',
            'locationFees',
            'distinctiveFeatureDefinitions',
            'carUnits.distinctiveValues',
        ]);

        $outOfHoursFees = OutOfHoursFee::query()
            ->whereJsonContains('vehicle_ids', $car->id)
            ->get();

        $specialPrices = SpecialPrice::query()
            ->forVehicle($car->id)
            ->get();

        return DB::transaction(function () use ($car, $copies, $outOfHoursFees, $specialPrices): array {
            $replicas = [];

            for ($i = 1; $i <= $copies; $i++) {
                $replicas[] = $this->duplicateSingle($car, $i, $outOfHoursFees, $specialPrices);
            }

            return $replicas;
        });
    }

    /**
     * @param  Collection<int, OutOfHoursFee>  $outOfHoursFees
     * @param  Collection<int, SpecialPrice>  $specialPrices
     */
    private function duplicateSingle(
        Car $car,
        int $copyIndex,
        Collection $outOfHoursFees,
        Collection $specialPrices,
    ): Car {
        $replica = $car->replicate([
            'id',
            'slug',
            'created_at',
            'updated_at',
        ]);
        $replica->name = $this->copyName($car->name, $copyIndex);
        $replica->slug = null;
        $replica->save();

        $replica->characteristics()->sync($car->characteristics->pluck('id')->all());
        $replica->rentalConditions()->sync($car->rentalConditions->pluck('id')->all());

        $rentalOptionRows = [];
        foreach ($car->rentalOptions as $option) {
            $rentalOptionRows[$option->id] = [
                'cost_cents' => (int) $option->pivot->cost_cents,
                'is_daily_cost' => (bool) $option->pivot->is_daily_cost,
            ];
        }
        $replica->rentalOptions()->sync($rentalOptionRows);

        $locationRows = [];
        foreach ($car->locations as $location) {
            $locationRows[$location->id] = [
                'allows_pickup' => (bool) $location->pivot->allows_pickup,
                'allows_dropoff' => (bool) $location->pivot->allows_dropoff,
            ];
        }
        $replica->locations()->sync($locationRows);

        foreach ($car->dailyFares as $fare) {
            $newFare = $fare->replicate(['id', 'car_id', 'created_at', 'updated_at']);
            $newFare->car_id = $replica->id;
            $newFare->save();
        }

        foreach ($car->hourlyFares as $fare) {
            $newFare = $fare->replicate(['id', 'car_id', 'created_at', 'updated_at']);
            $newFare->car_id = $replica->id;
            $newFare->save();
        }

        foreach ($car->extraHourFares as $fare) {
            $newFare = $fare->replicate(['id', 'car_id', 'created_at', 'updated_at']);
            $newFare->car_id = $replica->id;
            $newFare->save();
        }

        foreach ($car->locationFees as $fee) {
            $newFee = $fee->replicate(['id', 'car_id', 'created_at', 'updated_at']);
            $newFee->car_id = $replica->id;
            $newFee->save();
        }

        $definitionMap = [];
        foreach ($car->distinctiveFeatureDefinitions as $definition) {
            $newDefinition = $definition->replicate(['id', 'car_id', 'created_at', 'updated_at']);
            $newDefinition->car_id = $replica->id;
            $newDefinition->save();
            $definitionMap[$definition->id] = $newDefinition->id;
        }

        foreach ($car->carUnits as $unit) {
            $newUnit = $unit->replicate(['id', 'car_id', 'created_at', 'updated_at']);
            $newUnit->car_id = $replica->id;
            $newUnit->save();

            foreach ($unit->distinctiveValues as $value) {
                $definitionId = $definitionMap[$value->car_distinctive_feature_definition_id] ?? null;
                if ($definitionId === null) {
                    continue;
                }

                $newValue = $value->replicate(['id', 'car_unit_id', 'created_at', 'updated_at']);
                $newValue->car_unit_id = $newUnit->id;
                $newValue->car_distinctive_feature_definition_id = $definitionId;
                $newValue->save();
            }
        }

        foreach ($outOfHoursFees as $fee) {
            $newFee = $fee->replicate(['id', 'created_at', 'updated_at']);
            $newFee->vehicle_ids = [$replica->id];
            $newFee->save();
        }

        foreach ($specialPrices as $price) {
            $newPrice = $price->replicate(['id', 'created_at', 'updated_at']);
            $newPrice->vehicle_ids = [$replica->id];
            $newPrice->save();
        }

        return $replica;
    }

    private function copyName(string $name, int $copyIndex): string
    {
        return $copyIndex === 1
            ? $name.' (copy)'
            : $name.' (copy '.$copyIndex.')';
    }
}
