@props(['model', 'deleteRoute', 'confirmMessage'])
@can('delete', $model)
    <button
        type="button"
        class="btn btn-outline-danger"
        onclick="window.dispatchEvent(new CustomEvent('confirm-delete', { detail: { action: '{{ $deleteRoute }}', message: '{{ $confirmMessage }}' } }))"
    >
        <i class="ti ti-trash mr-1"></i> {{ __('ui.delete') }}
    </button>
@endcan
