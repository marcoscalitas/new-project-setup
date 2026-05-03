@props(['column', 'label', 'currentSort', 'currentDirection'])

@php
    $isActive  = $currentSort === $column;
    $nextDir   = ($isActive && $currentDirection === 'asc') ? 'desc' : 'asc';
    $url       = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir, 'page' => 1]);
    $icon      = $isActive
        ? ($currentDirection === 'asc' ? 'ti-sort-ascending' : 'ti-sort-descending')
        : 'ti-arrows-sort';
@endphp

<th>
    <a href="{{ $url }}" class="flex items-center gap-1 text-inherit hover:text-primary-500 whitespace-nowrap">
        {{ $label }}
        <i class="ti {{ $icon }} text-sm {{ $isActive ? 'text-primary-500' : 'opacity-40' }}"></i>
    </a>
</th>
