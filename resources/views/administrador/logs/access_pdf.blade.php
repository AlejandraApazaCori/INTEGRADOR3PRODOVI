@extends('administrador.logs.pdf_template')

@section('chart')
@if(isset($chartBase64) && $chartBase64)
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="{{ $chartBase64 }}" alt="Gráfico Estadístico" style="max-height: 250px; border-radius: 8px; border: 1px solid #d1d5db; padding: 10px; background: white;">
    </div>
@endif
@endsection

@section('table')
<table>
    <thead>
        <tr>
            <th style="width: 15%;">Fecha/Hora</th>
            <th style="width: 15%;">IP</th>
            <th style="width: 15%;">Usuario</th>
            <th style="width: 10%;">Método/Estado</th>
            <th style="width: 25%;">URL</th>
            <th style="width: 20%;">User Agent</th>
        </tr>
    </thead>
    <tbody>
        @foreach($logs as $log)
        <tr>
            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
            <td>{{ $log->ip_address }}</td>
            <td>{{ $log->user ? $log->user->name : 'Invitado' }}</td>
            <td style="text-align: center;">
                <strong>{{ $log->method }}</strong><br>
                {{ $log->status_code }} ({{ $log->response_time_ms }}ms)
            </td>
            <td>{{ Str::limit($log->url, 100) }}</td>
            <td style="font-size: 8px;">{{ Str::limit($log->user_agent, 80) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
