@props(['records', 'pageName', 'perPage' => null, 'perPageOptions' => null])

<div class="tb-foot">
    <span>
        @if (method_exists($records, 'total') && $records->total() > 0)
            Showing {{ $records->firstItem() }}–{{ $records->lastItem() }} of {{ $records->total() }}
            @if ($perPageOptions)
                <select class="tb-per-page" wire:model.live="tableRecordsPerPage" aria-label="Rows per page">
                    @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }} per page</option>
                    @endforeach
                </select>
            @elseif ($perPage)
                · {{ $perPage }} per page
            @endif
        @else
            Showing 0 of 0
        @endif
    </span>
    <button type="button" class="tb-btn tb-btn-sm" wire:click="previousPage(@js($pageName))" @disabled(method_exists($records, 'onFirstPage') && $records->onFirstPage())>Previous</button>
    <button type="button" class="tb-btn tb-btn-sm" wire:click="nextPage(@js($pageName))" @disabled(method_exists($records, 'hasMorePages') && ! $records->hasMorePages())>Next</button>
</div>
