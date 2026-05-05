<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1f2937; }
        .header { background: #7c3aed; color: white; padding: 20px 24px; margin-bottom: 24px; }
        .header h1 { font-size: 20px; font-weight: 700; }
        .header p { font-size: 11px; opacity: 0.85; margin-top: 4px; }
        .meta { padding: 0 24px; margin-bottom: 16px; font-size: 11px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        thead tr { background: #7c3aed; color: white; }
        thead th { padding: 7px 10px; text-align: left; font-weight: 600; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody tr:nth-child(odd) { background: #ffffff; }
        tbody td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; }
        .footer { margin-top: 24px; padding: 12px 24px; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Audit Log</h1>
        <p>Gerado em {{ $generated_at }}</p>
    </div>

    <div class="meta">
        Total: {{ $activities->count() }} registo(s)
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Log</th>
                <th>Descrição</th>
                <th>Utilizador</th>
                <th>Modelo</th>
                <th>ID Modelo</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($activities as $activity)
                <tr>
                    <td>{{ $activity->id }}</td>
                    <td>{{ $activity->log_name }}</td>
                    <td>{{ $activity->description }}</td>
                    <td>{{ $activity->causer?->name ?? '—' }}</td>
                    <td>{{ $activity->subject_type ? class_basename($activity->subject_type) : '—' }}</td>
                    <td>{{ $activity->subject_id ?? '—' }}</td>
                    <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:#9ca3af; padding: 24px;">
                        Sem registos para exportar.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Exportado automaticamente pelo sistema.
    </div>
</body>
</html>
