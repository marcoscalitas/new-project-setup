@props(['showRoute', 'model', 'deleteRoute', 'confirmMessage', 'editRoute' => null])
<ul class="flex items-center gap-1 mb-0">
    <li class="list-none">
        <a href="{{ $showRoute }}"
           class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-secondary btn-pc-default">
            <i class="ti ti-eye text-lg leading-none"></i>
        </a>
    </li>
    @if ($editRoute)
        @can('update', $model)
            <li class="list-none">
                <a href="{{ $editRoute }}"
                   class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-success btn-pc-default">
                    <i class="ti ti-edit text-lg leading-none"></i>
                </a>
            </li>
        @endcan
    @endif
    @can('delete', $model)
        <li class="list-none">
            <button type="button"
                class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-danger btn-pc-default"
                data-pc-toggle="modal"
                data-pc-target="#delete-confirm-modal"
                data-delete-action="{{ $deleteRoute }}"
                data-delete-message="{{ $confirmMessage }}">
                <i class="ti ti-trash text-lg leading-none"></i>
            </button>
        </li>
    @endcan
</ul>
