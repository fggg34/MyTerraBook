<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * List all payment methods for the admin, with Rapyd config presented safely.
     */
    public function index(): JsonResponse
    {
        $methods = PaymentMethod::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (PaymentMethod $method) => $this->present($method));

        return response()->json(['data' => $methods]);
    }

    /**
     * Create or update the Rapyd card payment method configuration.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'access_key' => ['nullable', 'string', 'max:255'],
            'secret_key' => ['nullable', 'string', 'max:255'],
            'environment' => ['nullable', 'in:sandbox,production'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $method = PaymentMethod::query()->firstOrNew(['code' => 'rapyd_card']);
        $config = $method->config ?? [];

        if (array_key_exists('access_key', $validated) && $validated['access_key'] !== null && $validated['access_key'] !== '') {
            $config['access_key'] = $validated['access_key'];
        }
        if (array_key_exists('secret_key', $validated) && $validated['secret_key'] !== null && $validated['secret_key'] !== '') {
            $config['secret_key'] = $validated['secret_key'];
        }
        $config['environment'] = $validated['environment'] ?? ($config['environment'] ?? 'sandbox');
        $config['commission_rate'] = (float) config('rapyd.commission_rate', 0.15);
        $config['webhook_url'] = rtrim((string) config('app.url'), '/').'/api/rapyd/webhook';

        $method->fill([
            'name' => 'Rapyd Card Payment',
            'is_enabled' => (bool) ($validated['is_active'] ?? $method->is_enabled ?? true),
            'config' => $config,
        ]);
        $method->save();

        return response()->json(['data' => $this->present($method)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(PaymentMethod $method): array
    {
        $config = $method->config ?? [];
        $commissionRate = (float) ($config['commission_rate'] ?? config('rapyd.commission_rate', 0.15));

        return [
            'id' => $method->id,
            'code' => $method->code,
            'name' => $method->name,
            'label' => $method->name,
            'is_active' => (bool) $method->is_enabled,
            'commission_rate' => $commissionRate,
            'commission_label' => sprintf(
                'Platform collects %d%% online • %d%% cash on arrival',
                (int) round($commissionRate * 100),
                (int) round((1 - $commissionRate) * 100),
            ),
            'environment' => $config['environment'] ?? 'sandbox',
            'access_key_last4' => isset($config['access_key']) ? substr((string) $config['access_key'], -4) : null,
            'has_secret_key' => ! empty($config['secret_key']),
            'webhook_url' => $config['webhook_url'] ?? (rtrim((string) config('app.url'), '/').'/api/rapyd/webhook'),
        ];
    }
}
