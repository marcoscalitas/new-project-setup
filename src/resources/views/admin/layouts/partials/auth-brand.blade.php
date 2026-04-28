<div class="flex flex-col items-center gap-2 mb-6">
    @if (config('app.logo'))
        <img src="{{ config('app.logo') }}" alt="{{ config('app.name') }}" class="h-14 w-14 rounded-full object-cover" />
        <span class="text-lg font-medium text-theme-heading dark:text-themedark-heading">{{ config('app.name') }}</span>
    @else
        <img src="{{ asset('admin/theme/images/logo-dark.svg') }}" alt="{{ config('app.name') }}"
            class="h-10" />
    @endif
</div>
