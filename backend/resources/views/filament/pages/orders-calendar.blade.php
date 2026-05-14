<x-filament-panels::page>
    <style>
        .oc-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .oc-month {
            border: 1px solid #d9dee7;
            border-radius: 6px;
            background: #fff;
            overflow: hidden;
        }

        .oc-month-title {
            padding: 10px;
            text-align: center;
            font-weight: 700;
            font-size: 13px;
            color: #293544;
            border-bottom: 1px solid #e4e9f0;
            background: #f7f9fc;
        }

        .oc-weekdays,
        .oc-cells {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .oc-weekdays span {
            font-size: 10px;
            font-weight: 700;
            text-align: center;
            color: #607080;
            padding: 6px 0;
            border-bottom: 1px solid #edf1f6;
        }

        .oc-cell {
            min-height: 34px;
            border-right: 1px solid #f1f4f8;
            border-bottom: 1px solid #f1f4f8;
            font-size: 11px;
            text-align: center;
            line-height: 34px;
            color: #2a3644;
            background: #fff;
        }

        .oc-cell:nth-child(7n) {
            border-right: 0;
        }

        .oc-cell--out {
            color: #b9c1cb;
            background: #fbfcfe;
        }

        .oc-cell--busy {
            background: #eda74f;
            color: #fff;
            font-weight: 700;
        }

        @media (max-width: 960px) {
            .oc-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="oc-grid">
        @foreach ($this->months as $month)
            <section class="oc-month">
                <div class="oc-month-title">{{ $month['label'] }}</div>
                <div class="oc-weekdays">
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                    <span>Sun</span>
                </div>
                <div class="oc-cells">
                    @foreach ($month['cells'] as $cell)
                        <div
                            class="oc-cell {{ $cell['inMonth'] ? '' : 'oc-cell--out' }} {{ $cell['reservations'] > 0 ? 'oc-cell--busy' : '' }}"
                            title="{{ $cell['date'] }} @if($cell['reservations'] > 0)- {{ $cell['reservations'] }} reservation(s) @endif"
                        >
                            {{ $cell['day'] }}
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
