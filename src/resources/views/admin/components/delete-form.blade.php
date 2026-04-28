@props(['model', 'deleteRoute', 'confirmMessage'])
@can('delete', $model)
    <form method="POST" action="{{ $deleteRoute }}" class="inline" onsubmit="return confirm('{{ $confirmMessage }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger">
            <i class="ti ti-trash mr-1"></i> {{ __('ui.delete') }}
        </button>
    </form>
@endcan
