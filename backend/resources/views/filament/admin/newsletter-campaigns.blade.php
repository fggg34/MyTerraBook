@php
    use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
    use App\Support\AdminTableBadgeColors;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
@endphp

<div class="tb-list">
    <div class="tb-table-card">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search subject…" wire:model.live.debounce.300ms="tableSearch">
            </label>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:minmax(200px,1.6fr) 110px 110px 160px minmax(120px,1fr) 56px">
            <span>Subject</span><span>Status</span><span>Recipients</span><span>Sent at</span><span>Sent by</span><span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $status = $record->status?->value ?? (string) $record->status;
            @endphp
            <div class="tb-row tb-grid" wire:key="campaign-{{ $key }}" style="grid-template-columns:minmax(200px,1.6fr) 110px 110px 160px minmax(120px,1fr) 56px">
                <span>{{ $record->subject }}</span>
                <span><x-admin.status-pill :tone="$status === 'sent' ? 'ok' : 'muted'">{{ AdminTableBadgeColors::humanize($status) }}</x-admin.status-pill></span>
                <span>{{ (int) $record->recipient_count }}</span>
                <span>{{ $record->sent_at?->format('j M Y, H:i') ?: '—' }}</span>
                <span>{{ $record->sender?->name ?: '—' }}</span>
                <x-admin.row-menu :edit-url="NewsletterCampaignResource::getUrl('edit', ['record' => $record])" :items="[]" />
            </div>
        @empty
            <p class="tb-empty">No campaigns yet.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
