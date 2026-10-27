<x-filament-panels::header
    :actions="$actions"
    :heading="$heading"
    :subheading="$subheading"
/>

<div class="ir-summary">
    <div class="ir-summary-grid">
        @foreach ($fields as $field)
            <div>
                <p>{{ $field['label'] }}</p>
                <strong>{{ $field['value'] }}</strong>
            </div>
        @endforeach
    </div>
</div>
<div class="ir-detail-tab">{{ $tab }}</div>

<style>
    .ir-summary {
        margin-top: 0.25rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        padding: 1rem 1.25rem;
    }
    .ir-summary-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    @media (min-width: 640px) {
        .ir-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 768px) {
        .ir-summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (min-width: 1024px) {
        .ir-summary-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    .ir-summary p {
        margin: 0;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
    }
    .ir-summary strong {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #0f172a;
    }
    .ir-detail-tab {
        margin-top: 1rem;
        padding-bottom: 0.45rem;
        border-bottom: 2px solid #0164a3;
        width: fit-content;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #0164a3;
    }
</style>
