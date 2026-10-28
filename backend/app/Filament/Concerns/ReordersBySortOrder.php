<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait ReordersBySortOrder
{
    /**
     * @param  class-string<Model>  $model
     * @param  list<int|string>  $order
     */
    protected function reorderBySortOrder(string $model, array $order): void
    {
        $order = array_values(array_map(
            fn (int|string $id): string => (string) $id,
            array_filter($order, fn (mixed $id): bool => filled($id)),
        ));

        if ($order === []) {
            return;
        }

        $all = $model::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        $slots = [];
        foreach ($all as $index => $id) {
            if (in_array($id, $order, true)) {
                $slots[] = $index;
            }
        }

        if (count($slots) !== count($order)) {
            return;
        }

        foreach ($slots as $offset => $index) {
            $all[$index] = $order[$offset];
        }

        DB::transaction(function () use ($model, $all): void {
            foreach ($all as $index => $id) {
                $model::query()->whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });
    }
}
