@php
    $usedBy = static function (\App\Models\TaxRate $rate): string {
        $parts = [];
        if ($rate->price_types_count) {
            $parts[] = 'Price types';
        }
        if ($rate->location_fees_count) {
            $parts[] = 'location fees';
        }
        if ($rate->out_of_hours_fees_count) {
            $parts[] = 'Out-of-hours fees';
        }
        if ($rate->rental_options_count) {
            $parts[] = 'Add-ons';
        }

        return $parts !== [] ? implode(', ', $parts) : '-';
    };
    $rateLabel = static function (\App\Models\TaxRate $rate): string {
        $percent = ((int) $rate->basis_points) / 100;

        return rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.').'%';
    };
@endphp
<div class="tb-dual-tables" id="types-of-price">
    <section class="tb-side-card">
        <h2>Tax rates</h2>
        <table class="tb-plain-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Rate</th>
                    <th>Used by</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($taxRates as $rate)
                    <tr>
                        <td>{{ $rate->name }}</td>
                        <td class="tabular-nums">{{ $rateLabel($rate) }}</td>
                        <td>{{ $usedBy($rate) }}</td>
                        <td>
                            <a class="tb-icon-btn" href="{{ \App\Filament\Resources\TaxRates\TaxRateResource::getUrl('edit', ['record' => $rate]) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No tax rates yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
    <section class="tb-side-card">
        <h2>Types of price</h2>
        <table class="tb-plain-table">
            <thead>
                <tr>
                    <th>Price type</th>
                    <th>Attributes</th>
                    <th>Tax rate</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($priceTypes as $type)
                    <tr>
                        <td>{{ $type->name }}</td>
                        <td>{{ $type->attribute_label ?: '-' }}</td>
                        <td>
                            @if ($type->taxRate)
                                {{ $type->taxRate->name }}
                            @else
                                <span class="tb-pill tb-pill--warn">No tax rate</span>
                            @endif
                        </td>
                        <td>
                            <a class="tb-icon-btn" href="{{ \App\Filament\Resources\PriceTypes\PriceTypeResource::getUrl('edit', ['record' => $type]) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No price types yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
