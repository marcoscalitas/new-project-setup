@props(['model', 'deleteRoute', 'confirmMessage'])
@can('delete', $model)
    <button
        type="button"
        class="btn btn-outline-danger"
        data-pc-toggle="modal"
        data-pc-target="#delete-confirm-modal"
        data-delete-action="{{ $deleteRoute }}"
        data-delete-message="{{ $confirmMessage }}"
    >
        <i class="ti ti-trash mr-1"></i> {{ __('ui.delete') }}
    </button>
@endcan
