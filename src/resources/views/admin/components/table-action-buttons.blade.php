@props(['showRoute', 'model', 'deleteRoute', 'confirmMessage', 'editRoute' => null])
<a href="{{ $showRoute }}" class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-secondary">
    <i class="ti ti-eye text-xl leading-none"></i>
</a>
@if ($editRoute)
    @can('update', $model)
        <a href="{{ $editRoute }}" class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-secondary">
            <i class="ti ti-edit text-xl leading-none"></i>
        </a>
    @endcan
@endif
@can('delete', $model)
    <button
        type="button"
        class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-secondary"
        data-pc-toggle="modal"
        data-pc-target="#delete-confirm-modal"
        data-delete-action="{{ $deleteRoute }}"
        data-delete-message="{{ $confirmMessage }}"
    >
        <i class="ti ti-trash text-xl leading-none"></i>
    </button>
@endcan
