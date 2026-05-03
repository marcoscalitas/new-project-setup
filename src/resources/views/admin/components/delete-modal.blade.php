<div class="modal fade" id="delete-confirm-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('ui.confirm_delete_title') }}</h5>
                <button type="button"
                    data-pc-modal-dismiss="#delete-confirm-modal"
                    class="text-lg flex items-center justify-center rounded w-7 h-7 text-secondary-500 hover:bg-danger-500/10 hover:text-danger-500">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-0" id="delete-confirm-message"></p>
            </div>
            <div class="modal-footer">
                <button type="button"
                    data-pc-modal-dismiss="#delete-confirm-modal"
                    class="btn btn-secondary">
                    {{ __('ui.cancel') }}
                </button>
                <form id="delete-confirm-form" method="POST" class="inline ml-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        {{ __('ui.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-pc-target="#delete-confirm-modal"]');
        if (!btn) return;
        document.getElementById('delete-confirm-message').textContent = btn.dataset.deleteMessage || '';
        document.getElementById('delete-confirm-form').action = btn.dataset.deleteAction || '';
    });
</script>
