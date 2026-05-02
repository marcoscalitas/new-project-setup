<div
    x-data="{ open: false, action: '', message: '' }"
    @confirm-delete.window="open = true; action = $event.detail.action; message = $event.detail.message"
    x-show="open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    class="fixed inset-0 z-[1050] flex items-center justify-center"
>
    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>

    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6 z-10"
        @click.stop>
        <div class="flex items-start mb-4">
            <div class="w-12 h-12 shrink-0 rounded-full bg-danger-500/10 flex items-center justify-center mr-4">
                <i class="ti ti-trash text-danger-500 text-2xl leading-none"></i>
            </div>
            <div class="pt-1">
                <h5 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ __('ui.confirm_delete_title') }}</h5>
                <p class="text-sm text-muted mb-0" x-text="message"></p>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="open = false" class="btn btn-outline-secondary">
                {{ __('ui.cancel') }}
            </button>
            <form :action="action" method="POST" class="inline">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger">
                    <i class="ti ti-trash mr-1"></i> {{ __('ui.delete') }}
                </button>
            </form>
        </div>
    </div>
</div>
