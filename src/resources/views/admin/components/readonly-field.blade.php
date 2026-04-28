@props(['label', 'value', 'span' => 6])
<div class="col-span-12 md:col-span-{{ $span }}">
    <div class="mb-3">
        <label class="form-label text-muted">{{ $label }}</label>
        <p class="mb-0 fw-medium">{{ $value }}</p>
    </div>
</div>
