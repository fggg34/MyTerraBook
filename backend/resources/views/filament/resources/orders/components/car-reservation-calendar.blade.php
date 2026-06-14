<style>
    .orc-shell {
        border: 1px solid #d7dde6;
        border-radius: 6px;
        background: #fff;
        padding: 12px;
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
        color: #1f2a35;
    }

    .orc-meta {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: #4a5b6f;
        border: 1px solid #d7dde6;
        background: #f5f8fb;
        border-radius: 3px;
        padding: 4px 7px;
    }

    .orc-image {
        width: 100%;
        max-height: 180px;
        object-fit: contain;
        border: 1px solid #e2e7ef;
        border-radius: 6px;
        background: #f8fbff;
        margin-bottom: 10px;
    }

    .orc-cal-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .orc-month {
        border: 1px solid #dce2ea;
        border-radius: 4px;
        overflow: hidden;
        background: #fff;
    }

    .orc-month-name {
        font-size: 13px;
        font-weight: 700;
        color: #2b3744;
        text-align: center;
        padding: 8px 6px;
        background: #f2f5f9;
        border-bottom: 1px solid #dce2ea;
    }

    .orc-weekdays,
    .orc-week {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }

    .orc-weekdays span {
        font-size: 10px;
        font-weight: 700;
        color: #5b6b7e;
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

    .orc-day--blocked {
        background: #eda74f;
        color: #fff;
        font-weight: 700;
    }

    .orc-day--selected {
        box-shadow: inset 0 0 0 2px #45a06a;
        font-weight: 700;
    }

    @media (max-width: 1200px) {
        .orc-cal-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .orc-cal-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="orc-shell">
    <div class="orc-top">
        <div class="orc-title">{{ $carName }}</div>
        <span class="orc-meta">{{ $calendarLabel }}</span>
    </div>

    @if ($carImageUrl)
        <img src="{{ $carImageUrl }}" alt="{{ $carName }}" class="orc-image" />
    @endif

    <div class="orc-cal-grid">
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
                                class="orc-day {{ $day['inMonth'] ? '' : 'orc-day--out' }} {{ $day['blocked'] ? 'orc-day--blocked' : '' }} {{ $day['selected'] ? 'orc-day--selected' : '' }}"
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
</div>
