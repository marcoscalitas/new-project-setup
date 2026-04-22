<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1f2937; }
        .header { background: #2563eb; color: white; padding: 20px 24px; margin-bottom: 24px; }
        .header h1 { font-size: 20px; font-weight: 700; }
        .header p { font-size: 11px; opacity: 0.85; margin-top: 4px; }
        .meta { padding: 0 24px; margin-bottom: 16px; font-size: 11px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        thead tr { background: #2563eb; color: white; }
        thead th { padding: 8px 12px; text-align: left; font-weight: 600; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody tr:nth-child(odd) { background: #ffffff; }
        tbody td { padding: 7px 12px; border-bottom: 1px solid #e5e7eb; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 9999px; font-size: 10px; font-weight: 600; }
        .badge-yes { background: #d1fae5; color: #065f46; }
        .badge-no { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 24px; padding: 12px 24px; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Utilizadores</h1>
        <p>Gerado em {{ $generated_at }}</p>
    </div>

    <div class="meta">
        Total: {{ $users->count() }} utilizador(es)
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Verificado</th>
                <th>Criado em</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->roles->pluck('name')->implode(', ') ?: '—' }}</td>
                    <td>
                        @if ($user->email_verified_at)
                            <span class="badge badge-yes">Sim</span>
                        @else
                            <span class="badge badge-no">Não</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#9ca3af; padding: 24px;">
                        Sem utilizadores para exportar.
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
