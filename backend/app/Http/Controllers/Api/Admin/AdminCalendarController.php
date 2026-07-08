<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use App\Services\Admin\AdminCalendarEventService;
use App\Services\Admin\AdminCalendarFilters;
use App\Services\Admin\AdminCalendarResourceService;
use App\Services\Admin\AdminCalendarSummaryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCalendarController extends Controller
{
    public function __construct(
        private readonly AdminCalendarResourceService $resources,
        private readonly AdminCalendarEventService $events,
        private readonly AdminCalendarSummaryService $summary,
    ) {}

    public function resources(Request $request): JsonResponse
    {
        $filters = AdminCalendarFilters::fromRequest($request);
        $result = $this->resources->list($filters, $request);

        return response()->json($result);
    }

    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $start = Carbon::parse($validated['start'])->startOfDay();
        $end = Carbon::parse($validated['end'])->endOfDay();
        $filters = AdminCalendarFilters::fromRequest($request);

        $result = $this->events->eventsForRange($start, $end, $filters);

        return response()->json($result);
    }

    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $start = Carbon::parse($validated['start'])->startOfDay();
        $end = Carbon::parse($validated['end'])->endOfDay();
        $filters = AdminCalendarFilters::fromRequest($request);

        return response()->json([
            'data' => $this->summary->summarize($start, $end, $filters),
        ]);
    }

    public function alerts(Request $request): JsonResponse
    {
        $filters = AdminCalendarFilters::fromRequest($request);
        $start = Carbon::now()->subDays(7)->startOfDay();
        $end = Carbon::now()->addDays(60)->endOfDay();

        $events = $this->events->eventsForRange($start, $end, $filters);

        $pending = collect($events['data'])
            ->filter(fn (array $e) => in_array($e['status'], ['pending', 'stand_by'], true))
            ->take(20)
            ->values()
            ->all();

        $conflicts = $events['meta']['conflicts'] ?? [];

        return response()->json([
            'data' => [
                'pending' => $pending,
                'conflicts' => array_slice($conflicts, 0, 20),
                'pendingCount' => count($pending),
                'conflictCount' => count($conflicts),
            ],
        ]);
    }

    public function showEvent(string $type, int $id): JsonResponse
    {
        $event = match ($type) {
            'car' => $this->events->showCarOrder(Order::query()->findOrFail($id)),
            'stay' => $this->events->showStayBooking(GuestHouseBooking::query()->findOrFail($id)),
            default => abort(404, 'Unknown booking type.'),
        };

        if ($event === null) {
            abort(404, 'Booking could not be loaded.');
        }

        return response()->json(['data' => $event]);
    }
}
