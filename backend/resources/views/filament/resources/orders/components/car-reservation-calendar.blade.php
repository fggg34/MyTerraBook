<style>
    .orc-shell {
        border: 0;
        border-radius: 0;
        background: transparent;
        padding: 0;
    }

    .orc-top {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: center;
        margin-bottom: 10px;
    }

    .orc-title {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
    }

    .orc-meta {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: #64748b;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 3px;
        padding: 4px 7px;
    }

    .orc-image {
        width: 100%;
        max-height: 180px;
        object-fit: contain;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        background: #f8fafc;
        margin-bottom: 10px;
    }

    .orc-cal-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .orc-month {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        overflow: hidden;
        background: #fff;
    }

    .orc-month-name {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        text-align: center;
        padding: 8px 6px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .orc-weekdays,
    .orc-week {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }

    .orc-weekdays span {
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        text-align: center;
        padding: 6px 0;
        border-bottom: 1px solid #edf1f6;
    }

    .orc-day {
        height: 24px;
        font-size: 10px;
        text-align: center;
        line-height: 24px;
        color: #223142;
        border-right: 1px solid #f1f4f8;
        border-bottom: 1px solid #f1f4f8;
        background: #fff;
    }

    .orc-day:nth-child(7n) {
        border-right: 0;
    }

    .orc-day--out {
        color: #b8c0cb;
        background: #fbfcfe;
    }

    .orc-day--selected,
    .orc-day--this {
        background: #041e37;
        color: #fff;
        font-weight: 700;
        box-shadow: none;
        border-radius: 4px;
    }

    .orc-day--other {
        background: #d7e6f7;
        color: #1d4f86;
        font-weight: 700;
    }

    .orc-day--blocked {
        background: #edf1f6;
        color: #5b6b7d;
        font-weight: 600;
    }

    .orc-shell.is-compact .orc-cal-grid,
    .orc-cal-grid.is-one {
        grid-template-columns: 1fr;
    }

    .orc-shell.is-compact .orc-month {
        border: 0;
        border-radius: 0;
    }

    .orc-shell.is-compact .orc-month-name {
        display: none;
    }

    .orc-compact-label {
        text-align: right;
        font-size: 12px;
        font-weight: 600;
        color: #5b6b7d;
        margin-bottom: 8px;
    }

    .orc-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 10px;
        font-size: 12px;
        color: #5b6b7d;
    }

    .orc-legend i {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        margin-right: 6px;
        vertical-align: middle;
    }

    .orc-legend .is-this i { background: #041e37; }
    .orc-legend .is-blocked i { background: #c5d0dc; }
    .orc-legend .is-other i { background: #4474a4; }

    .orc-ical-note {
        margin: 8px 0 0;
        font-size: 12px;
        line-height: 16px;
        color: #8a99a9;
    }

    @media (max-width: 1200px) {
        .orc-cal-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .orc-shell.is-compact .orc-cal-grid,
        .orc-cal-grid.is-one {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 760px) {
        .orc-cal-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="orc-shell {{ ($compact ?? false) ? 'is-compact' : '' }}">
    @unless ($compact ?? false)
    <div class="orc-top">
        <div class="orc-title">{{ $carName }}</div>
        <span class="orc-meta">{{ $calendarLabel }}</span>
    </div>
    @else
        <div class="orc-compact-label">{{ $calendarLabel }}</div>
    @endunless

    @if ($carImageUrl)
        <img src="{{ $carImageUrl }}" alt="{{ $carName }}" class="orc-image" />
    @endif

    <div class="orc-cal-grid {{ ($compact ?? false) ? 'is-one' : '' }}">
        @foreach ($months as $month)
            <div class="orc-month">
                <div class="orc-month-name">{{ $month['label'] }}</div>
                <div class="orc-weekdays">
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                    <span>Sun</span>
                </div>

                @foreach ($month['weeks'] as $week)
                    <div class="orc-week">
                        @foreach ($week as $day)
                            <div
                                class="orc-day {{ $day['inMonth'] ? '' : 'orc-day--out' }} {{ ! empty($day['kind']) ? 'orc-day--'.$day['kind'] : '' }}"
                                title="{{ $day['date'] }}"
                            >
                                {{ $day['day'] }}
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
    @if ($compact ?? false)
        <div class="orc-legend">
            <span class="is-this"><i></i>This booking</span>
            <span class="is-blocked"><i></i>Blocked</span>
            <span class="is-other"><i></i>Other booking</span>
        </div>
        <p class="orc-ical-note">iCal sync and the 6-month view stay available under Open full calendar.</p>
    @endif
</div>
