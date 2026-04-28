@props(['cancelRoute', 'submitLabel'])
<div class="col-span-12 text-right">
    <a href="{{ $cancelRoute }}" class="btn btn-outline-secondary mr-1">{{ __('ui.cancel') }}</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
