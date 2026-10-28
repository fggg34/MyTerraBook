<section class="tb-side-card">
    <h2>Activity</h2>
    @if ($events === [])
        <p class="tb-side-muted">Activity appears after the booking is saved.</p>
    @else
        <ol class="tb-activity">
            @foreach ($events as $event)
                <li>
                    <strong>{{ $event['label'] }}</strong>
                    <span>{{ $event['at'] }}</span>
                </li>
            @endforeach
        </ol>
    @endif
</section>
