<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class PublicConfigController extends Controller
{
    public function show(): JsonResponse
    {
        $mapsApiKey = (string) data_get(
            Setting::getValue('system.google_maps_api_key', ['key' => '']),
            'key',
            '',
        );

        return response()->json([
            'maps_api_key' => $mapsApiKey,
        ]);
    }
}
