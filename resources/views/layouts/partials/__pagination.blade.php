@if ($items->total() > 0)
    <div class="card-footer clearfix">
        <div class="float-start pt-1 fs-7 text-body-secondary">
            Showing {{ $items->firstItem() }} to {{ $items->lastItem() }}
            of {{ $items->total() }} {{ $label ?? 'records' }}
        </div>

        @if ($items->hasPages())
            <div class="float-end">
                {{ $items->onEachSide(1)->links('admin.layouts.partials.pagination-links') }}
            </div>
        @endif
    </div>
@endif
