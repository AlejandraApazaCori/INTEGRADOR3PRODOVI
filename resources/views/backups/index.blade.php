@extends('layouts.app')

@section('title', 'Respaldos de base de datos')

@push('styles')
<style>
    .backups-page { min-height: 100vh; padding: 20px 0 48px; background: #fff; color: #172033; }
    .backups-shell { width: 100%; margin: 0; padding: 0 0 56px; }
    .backups-hero { position: relative; width: 100%; min-height: 180px; overflow: hidden; border-radius: 0; color: #fff; box-shadow: none; background: linear-gradient(135deg, #4f46e5 25%, transparent 25%) -50px 0, linear-gradient(225deg, #4f46e5 25%, transparent 25%) -50px 0, linear-gradient(315deg, #4f46e5 25%, transparent 25%), linear-gradient(45deg, #4f46e5 25%, transparent 25%), linear-gradient(to bottom, #3b82f6 0%, #2563eb 100%); background-size: 100px 100px, 100px 100px, 100px 100px, 100px 100px, 100% 100%; background-color: #1d4ed8; }
    .backups-hero .rp-banner-overlay { position: absolute; inset: 0; background: linear-gradient(rgba(15,23,42,.28), rgba(15,23,42,.28)), radial-gradient(circle at 0 0, rgba(255,255,255,.2), transparent 50%), radial-gradient(circle at 100% 0, rgba(255,255,255,.2), transparent 50%), radial-gradient(circle at 100% 100%, rgba(255,255,255,.2), transparent 50%), radial-gradient(circle at 0 100%, rgba(255,255,255,.2), transparent 50%); background-size: 100% 100%, 50% 50%, 50% 50%, 50% 50%, 50% 50%; background-position: 0 0, 0 0, 100% 0, 100% 100%, 0 100%; background-repeat: no-repeat; }
    .backups-hero-content { position: relative; z-index: 1; min-height: 180px; padding: 30px 48px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
    .backups-heading { display: flex; align-items: center; gap: 17px; }
    .backups-heading-icon { width: 52px; height: 52px; border-radius: 14px; display: grid; place-items: center; flex: 0 0 52px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.24); backdrop-filter: blur(5px); font-size: 22px; }
    .backups-heading h1 { margin: 0 0 4px; font-size: clamp(1.55rem, 3vw, 2.25rem); font-weight: 900; letter-spacing: -.04em; }
    .backups-heading h1::before { content: 'Seguridad de la información'; display: block; margin-bottom: 7px; color: #dbeafe; font-size: .68rem; font-weight: 900; letter-spacing: .15em; text-transform: uppercase; }
    .backups-heading p { margin: 0; color: #dbeafe; font-size: .74rem; font-weight: 600; }
    .backups-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; }
    .backup-btn { min-height: 43px; padding: 0 17px; border: 0; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; gap: 9px; font-size: .82rem; font-weight: 800; text-decoration: none; cursor: pointer; transition: .2s ease; }
    .backup-btn:hover { transform: translateY(-1px); }
    .backup-btn-light { background: #ef6c22; color: #fff; box-shadow: 0 8px 20px rgba(15,23,42,.15); }
    .backup-btn-ghost { background: #fff; color: #4f46e5; border: 1px solid #fff; box-shadow: 0 8px 20px rgba(15,23,42,.15); }
    .backup-btn-primary { background: #1d4ed8; color: #fff; box-shadow: 0 8px 18px rgba(29,78,216,.2); }
    .backup-btn-secondary { background: #eef2ff; color: #3730a3; }
    .backup-btn-disabled { pointer-events: none; opacity: .55; }
    .backup-alert { width: calc(100% - 48px); margin: 24px auto 0; border-radius: 13px; padding: 14px 17px; display: flex; gap: 11px; align-items: flex-start; font-size: .85rem; font-weight: 600; }
    .backup-alert-success { color: #166534; background: #f0fdf4; border: 1px solid #bbf7d0; }
    .backup-alert-error { color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; }
    .backup-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin: 24px 24px; }
    .backup-stat { padding: 19px; border-radius: 15px; background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 8px 24px rgba(15,23,42,.05); }
    .backup-stat-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .backup-stat-label { color: #6b7280; font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
    .backup-stat-icon { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; color: #2563eb; background: #eff6ff; }
    .backup-stat-value { display: block; margin-top: 11px; color: #111827; font-size: 1.02rem; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .backup-stat-meta { display: block; margin-top: 3px; color: #9ca3af; font-size: .72rem; }
    .backup-grid { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(270px, .75fr); gap: 20px; align-items: stretch; margin: 0 24px; }
    .backup-panel { border: 1px solid #e5e7eb; border-radius: 17px; background: #fff; box-shadow: 0 9px 28px rgba(15,23,42,.05); }
    .backup-panel-header { padding: 20px 22px 15px; border-bottom: 1px solid #f1f5f9; }
    .backup-panel-header h2 { margin: 0; color: #111827; font-size: 1rem; font-weight: 800; }
    .backup-panel-header p { margin: 4px 0 0; color: #6b7280; font-size: .78rem; }
    .backup-panel-body { padding: 20px 22px 22px; }
    .frequency-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .frequency-card { position: relative; }
    .frequency-card input { position: absolute; opacity: 0; pointer-events: none; }
    .frequency-card label { min-height: 88px; padding: 14px; border: 1px solid #e5e7eb; border-radius: 13px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 7px; color: #6b7280; cursor: pointer; transition: .18s ease; }
    .frequency-card label i { font-size: 17px; }
    .frequency-card label strong { color: #374151; font-size: .79rem; }
    .frequency-card input:checked + label { color: #2563eb; border-color: #93c5fd; background: #eff6ff; box-shadow: inset 0 0 0 1px #bfdbfe; }
    .frequency-card input:checked + label strong { color: #1d4ed8; }
    .schedule-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 13px; margin-top: 16px; }
    .schedule-field label { display: block; margin-bottom: 7px; color: #4b5563; font-size: .73rem; font-weight: 800; }
    .schedule-field input, .schedule-field select { width: 100%; height: 43px; padding: 0 12px; color: #1f2937; background: #f9fafb; border: 1px solid #d1d5db; border-radius: 10px; font-size: .82rem; outline: none; }
    .schedule-field input:focus, .schedule-field select:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .schedule-switch { margin: 18px 0; padding: 13px 14px; display: flex; align-items: center; justify-content: space-between; gap: 14px; border-radius: 11px; background: #f8fafc; border: 1px solid #e5e7eb; }
    .schedule-switch strong { display: block; font-size: .79rem; color: #1f2937; }
    .schedule-switch small { color: #6b7280; font-size: .7rem; }
    .switch-control { position: relative; width: 44px; height: 24px; flex: 0 0 44px; }
    .switch-control input { position: absolute; opacity: 0; }
    .switch-slider { position: absolute; inset: 0; border-radius: 999px; background: #cbd5e1; cursor: pointer; transition: .2s; }
    .switch-slider::after { content: ''; position: absolute; width: 18px; height: 18px; top: 3px; left: 3px; border-radius: 50%; background: #fff; transition: .2s; box-shadow: 0 2px 5px rgba(0,0,0,.18); }
    .switch-control input:checked + .switch-slider { background: #2563eb; }
    .switch-control input:checked + .switch-slider::after { transform: translateX(20px); }
    .schedule-submit { width: 100%; }
    .calendar-wrap { height: 100%; padding: 22px; display: flex; flex-direction: column; }
    .calendar-label { color: #6b7280; font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; }
    .calendar-card { margin-top: 16px; overflow: hidden; border: 1px solid #dbeafe; border-radius: 17px; background: #f8fbff; }
    .calendar-month { padding: 11px; color: #fff; background: #2563eb; text-align: center; font-size: .78rem; font-weight: 800; text-transform: uppercase; letter-spacing: .09em; }
    .calendar-day { padding: 22px 12px 7px; color: #0f172a; text-align: center; font-size: 3.35rem; font-weight: 800; line-height: 1; }
    .calendar-weekday { color: #475569; text-align: center; font-size: .88rem; font-weight: 700; text-transform: capitalize; }
    .calendar-time { margin: 17px; padding: 10px; color: #1d4ed8; background: #fff; border: 1px solid #dbeafe; border-radius: 10px; text-align: center; font-size: .82rem; font-weight: 800; }
    .calendar-note { margin: auto 0 0; padding-top: 17px; color: #64748b; font-size: .74rem; line-height: 1.55; text-align: center; }
    .calendar-disabled { margin: auto; padding: 35px 10px; color: #94a3b8; text-align: center; }
    .calendar-disabled i { display: block; margin-bottom: 12px; font-size: 32px; }
    .history-panel { margin: 20px 24px 0; overflow: hidden; }
    .history-table-wrap { overflow-x: auto; }
    .history-table { width: 100%; border-collapse: collapse; min-width: 790px; }
    .history-table th { padding: 12px 18px; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e5e7eb; text-align: left; font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
    .history-table td { padding: 15px 18px; border-bottom: 1px solid #f1f5f9; color: #374151; font-size: .79rem; vertical-align: middle; }
    .history-table tbody tr:last-child td { border-bottom: 0; }
    .history-file { display: flex; align-items: center; gap: 10px; min-width: 235px; }
    .history-file-icon { width: 34px; height: 34px; border-radius: 9px; display: grid; place-items: center; color: #2563eb; background: #eff6ff; flex: 0 0 34px; }
    .history-file strong { display: block; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #1f2937; font-size: .77rem; }
    .history-file small { color: #9ca3af; }
    .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; border-radius: 999px; font-size: .68rem; font-weight: 800; }
    .status-completed { color: #166534; background: #dcfce7; }
    .status-processing { color: #92400e; background: #fef3c7; }
    .status-failed { color: #991b1b; background: #fee2e2; }
    .history-download { width: 34px; height: 34px; border-radius: 9px; display: inline-grid; place-items: center; color: #1d4ed8; background: #eff6ff; text-decoration: none; }
    .history-download:hover { background: #dbeafe; }
    .history-error { display: block; max-width: 210px; margin-top: 4px; color: #b91c1c; font-size: .67rem; line-height: 1.35; }
    .history-empty { padding: 46px 20px; text-align: center; color: #94a3b8; }
    .history-empty i { display: block; margin-bottom: 10px; font-size: 30px; }
    .history-pagination { padding: 14px 18px; border-top: 1px solid #f1f5f9; }
    @media (max-width: 900px) { .backup-stats { grid-template-columns: repeat(2, 1fr); } .backup-grid { grid-template-columns: 1fr; } }
    @media (max-width: 680px) { .backups-hero { min-height: 220px; } .backups-hero-content { min-height: 220px; padding: 26px 20px; align-items: flex-start; flex-direction: column; justify-content: center; } .backups-heading { align-items: center; } .backups-actions { width: 100%; justify-content: flex-start; } .backup-btn { flex: 1; } .backup-alert { width: calc(100% - 24px); } .backup-stats { grid-template-columns: 1fr; margin-right: 12px; margin-left: 12px; } .backup-grid { margin-right: 12px; margin-left: 12px; } .history-panel { margin-right: 12px; margin-left: 12px; } .frequency-options { grid-template-columns: 1fr; } .frequency-card label { min-height: 65px; flex-direction: row; } .schedule-fields { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
@php
    $frequencyLabels = ['daily' => 'Diario', 'weekly' => 'Semanal', 'monthly' => 'Mensual'];
    $weekdayLabels = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
@endphp
<div class="backups-page">
    <div class="backups-shell">
        <section class="backups-hero rp-banner">
            <div class="rp-banner-overlay"></div>
            <div class="backups-hero-content">
                <div class="backups-heading">
                    <div class="backups-heading-icon"><i class="fas fa-database"></i></div>
                    <div>
                        <h1>Respaldos de base de datos</h1>
                        <p>Protege la información del sistema y descarga copias en formato SQL.</p>
                    </div>
                </div>
                <div class="backups-actions">
                    <a href="{{ $latestBackup ? route('administrador.backups.download-latest') : '#' }}" class="backup-btn backup-btn-ghost {{ $latestBackup ? '' : 'backup-btn-disabled' }}" @if(!$latestBackup) aria-disabled="true" @endif>
                        <i class="fas fa-download"></i> Descargar último SQL
                    </a>
                    <form method="POST" action="{{ route('administrador.backups.store') }}" onsubmit="return confirm('¿Deseas crear un respaldo completo de la base de datos ahora?');">
                        @csrf
                        <button type="submit" class="backup-btn backup-btn-light"><i class="fas fa-plus-circle"></i> Crear respaldo ahora</button>
                    </form>
                </div>
            </div>
        </section>

        @if(session('success'))
            <div class="backup-alert backup-alert-success"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>
        @endif
        @if(session('error'))
            <div class="backup-alert backup-alert-error"><i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span></div>
        @endif
        @if($errors->any())
            <div class="backup-alert backup-alert-error"><i class="fas fa-exclamation-triangle"></i><span>{{ $errors->first() }}</span></div>
        @endif

        <section class="backup-stats" aria-label="Resumen de respaldos">
            <article class="backup-stat">
                <div class="backup-stat-top"><span class="backup-stat-label">Último respaldo</span><span class="backup-stat-icon"><i class="fas fa-clock"></i></span></div>
                <span class="backup-stat-value">{{ $latestBackup?->completed_at?->format('d/m/Y H:i') ?? 'Sin respaldos' }}</span>
                <span class="backup-stat-meta">{{ $latestBackup?->completed_at?->diffForHumans() ?? 'Crea la primera copia SQL' }}</span>
            </article>
            <article class="backup-stat">
                <div class="backup-stat-top"><span class="backup-stat-label">Tamaño actual</span><span class="backup-stat-icon"><i class="fas fa-hard-drive"></i></span></div>
                <span class="backup-stat-value">{{ $latestBackup?->formatted_size ?? '—' }}</span>
                <span class="backup-stat-meta">Archivo SQL más reciente</span>
            </article>
            <article class="backup-stat">
                <div class="backup-stat-top"><span class="backup-stat-label">Frecuencia</span><span class="backup-stat-icon"><i class="fas fa-repeat"></i></span></div>
                <span class="backup-stat-value">{{ $setting->enabled ? ($frequencyLabels[$setting->frequency] ?? 'Diario') : 'Desactivada' }}</span>
                <span class="backup-stat-meta">Programación automática</span>
            </article>
            <article class="backup-stat">
                <div class="backup-stat-top"><span class="backup-stat-label">Próximo respaldo</span><span class="backup-stat-icon"><i class="fas fa-calendar-check"></i></span></div>
                <span class="backup-stat-value">{{ $setting->next_run_at?->format('d/m/Y') ?? 'No programado' }}</span>
                <span class="backup-stat-meta">{{ $setting->next_run_at ? 'A las '.$setting->next_run_at->format('H:i') : 'Activa la programación' }}</span>
            </article>
        </section>

        <div class="backup-grid">
            <section class="backup-panel">
                <header class="backup-panel-header">
                    <h2><i class="fas fa-sliders mr-2 text-blue-600"></i>Programación automática</h2>
                    <p>Selecciona cada cuánto tiempo deseas guardar una copia de la base de datos.</p>
                </header>
                <div class="backup-panel-body">
                    <form method="POST" action="{{ route('administrador.backups.schedule') }}" id="backupScheduleForm">
                        @csrf
                        @method('PUT')
                        <div class="frequency-options">
                            @foreach(['daily' => ['fa-sun', 'Diario'], 'weekly' => ['fa-calendar-week', 'Semanal'], 'monthly' => ['fa-calendar-days', 'Mensual']] as $value => [$icon, $label])
                                <div class="frequency-card">
                                    <input type="radio" name="frequency" id="frequency-{{ $value }}" value="{{ $value }}" {{ old('frequency', $setting->frequency) === $value ? 'checked' : '' }}>
                                    <label for="frequency-{{ $value }}"><i class="fas {{ $icon }}"></i><strong>{{ $label }}</strong></label>
                                </div>
                            @endforeach
                        </div>

                        <div class="schedule-fields">
                            <div class="schedule-field">
                                <label for="backup_time">Hora del respaldo</label>
                                <input type="time" id="backup_time" name="backup_time" value="{{ old('backup_time', substr((string) $setting->backup_time, 0, 5)) }}" required>
                            </div>
                            <div class="schedule-field" id="weeklyField">
                                <label for="weekday">Día de la semana</label>
                                <select id="weekday" name="weekday">
                                    @foreach($weekdayLabels as $index => $day)
                                        <option value="{{ $index }}" {{ (int) old('weekday', $setting->weekday ?? 1) === $index ? 'selected' : '' }}>{{ ucfirst($day) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="schedule-field" id="monthlyField">
                                <label for="month_day">Día de cada mes</label>
                                <select id="month_day" name="month_day">
                                    @for($day = 1; $day <= 28; $day++)
                                        <option value="{{ $day }}" {{ (int) old('month_day', $setting->month_day ?? 1) === $day ? 'selected' : '' }}>Día {{ $day }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="schedule-switch">
                            <div><strong>Respaldos automáticos</strong><small>El servidor ejecutará la programación seleccionada.</small></div>
                            <label class="switch-control">
                                <input type="hidden" name="enabled" value="0">
                                <input type="checkbox" name="enabled" value="1" {{ old('enabled', $setting->enabled) ? 'checked' : '' }}>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                        <button type="submit" class="backup-btn backup-btn-primary schedule-submit"><i class="fas fa-save"></i> Guardar programación</button>
                    </form>
                </div>
            </section>

            <aside class="backup-panel">
                <div class="calendar-wrap">
                    <span class="calendar-label">Fecha más próxima</span>
                    @if($setting->enabled && $setting->next_run_at)
                        <div class="calendar-card">
                            <div class="calendar-month">{{ $setting->next_run_at->copy()->locale('es')->translatedFormat('F Y') }}</div>
                            <div class="calendar-day">{{ $setting->next_run_at->format('d') }}</div>
                            <div class="calendar-weekday">{{ $setting->next_run_at->copy()->locale('es')->translatedFormat('l') }}</div>
                            <div class="calendar-time"><i class="far fa-clock mr-2"></i>{{ $setting->next_run_at->format('H:i') }}</div>
                        </div>
                        <p class="calendar-note"><i class="fas fa-circle-info mr-1"></i>El respaldo se ejecutará automáticamente si el programador de tareas del servidor está activo.</p>
                    @else
                        <div class="calendar-disabled"><i class="far fa-calendar-xmark"></i>No existe una fecha próxima porque la programación está desactivada.</div>
                    @endif
                </div>
            </aside>
        </div>

        <section class="backup-panel history-panel">
            <header class="backup-panel-header">
                <h2><i class="fas fa-clock-rotate-left mr-2 text-blue-600"></i>Historial de respaldos</h2>
                <p>Las copias se almacenan de forma privada y solo los administradores pueden descargarlas.</p>
            </header>
            @if($backups->count())
                <div class="history-table-wrap">
                    <table class="history-table">
                        <thead><tr><th>Archivo</th><th>Fecha</th><th>Origen</th><th>Tamaño</th><th>Estado</th><th>Acción</th></tr></thead>
                        <tbody>
                            @foreach($backups as $backup)
                                <tr>
                                    <td>
                                        <div class="history-file">
                                            <span class="history-file-icon"><i class="fas fa-file-code"></i></span>
                                            <div><strong title="{{ $backup->filename }}">{{ $backup->filename ?: 'Respaldo sin archivo' }}</strong><small>{{ $backup->creator?->name ?? ($backup->trigger === 'scheduled' ? 'Sistema automático' : 'Administrador') }}</small></div>
                                        </div>
                                    </td>
                                    <td>{{ $backup->started_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                                    <td>{{ $backup->trigger === 'scheduled' ? 'Programado' : 'Manual' }}</td>
                                    <td>{{ $backup->status === 'completed' ? $backup->formatted_size : '—' }}</td>
                                    <td>
                                        <span class="status-pill status-{{ $backup->status }}">
                                            <i class="fas {{ $backup->status === 'completed' ? 'fa-check' : ($backup->status === 'failed' ? 'fa-xmark' : 'fa-spinner fa-spin') }}"></i>
                                            {{ $backup->status === 'completed' ? 'Completado' : ($backup->status === 'failed' ? 'Fallido' : 'Procesando') }}
                                        </span>
                                        @if($backup->status === 'failed' && $backup->error_message)<span class="history-error" title="{{ $backup->error_message }}">{{ \Illuminate\Support\Str::limit($backup->error_message, 95) }}</span>@endif
                                    </td>
                                    <td>
                                        @if($backup->status === 'completed')
                                            <a href="{{ route('administrador.backups.download', $backup) }}" class="history-download" title="Descargar SQL"><i class="fas fa-download"></i></a>
                                        @else
                                            <span class="history-download backup-btn-disabled"><i class="fas fa-download"></i></span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($backups->hasPages())<div class="history-pagination">{{ $backups->links() }}</div>@endif
            @else
                <div class="history-empty"><i class="fas fa-database"></i>Aún no hay respaldos registrados.<br>Crea el primero con el botón superior.</div>
            @endif
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('input[name="frequency"]');
        const weeklyField = document.getElementById('weeklyField');
        const monthlyField = document.getElementById('monthlyField');

        function updateFrequencyFields() {
            const selected = document.querySelector('input[name="frequency"]:checked')?.value || 'daily';
            weeklyField.style.display = selected === 'weekly' ? 'block' : 'none';
            monthlyField.style.display = selected === 'monthly' ? 'block' : 'none';
        }

        radios.forEach(radio => radio.addEventListener('change', updateFrequencyFields));
        updateFrequencyFields();
    });
</script>
@endpush
