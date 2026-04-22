<?php

namespace Modules\Export\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Export\Models\Export;

class ExportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Export $export) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'export_uuid' => $this->export->uuid,
            'module'      => $this->export->module,
            'format'      => $this->export->format,
            'filename'    => $this->export->filename,
            'expires_at'  => $this->export->expires_at?->toISOString(),
            'download_url' => route('api.exports.download', $this->export->uuid),
        ];
    }
}
