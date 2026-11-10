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
    .backups-dashboard-back { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 13px; padding: 7px 12px; border: 1px solid rgba(255,255,255,.36); border-radius: 9px; background: rgba(255,255,255,.12); color: #fff; font-size: .7rem; font-weight: 800; text-decoration: none; transition: .18s; }
    .backups-dashboard-back:hover,.backups-dashboard-back:focus-visible { border-color: #fff; background: #fff; color: #2563eb; }
    .backups-heading p { margin: 0; color: #dbeafe; font-size: .74rem; font-weight: 600; }
    .backups-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; }
    .backup-btn { min-height: 43px; padding: 0 17px; border: 0; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; gap: 9px; font-size: .82rem; font-weight: 800; text-decoration: none; cursor: pointer; transition: .2s ease; }
    .backup-btn:hover { transform: translateY(-1px); }
    .backup-btn-light { background: #ef6c22; color: #fff; box-shadow: 0 8px 20px rgba(15,23,42,.15); }
    .backup-btn-ghost { background: #fff; color: #4f46e5; border: 1px solid #fff; box-shadow: 0 8px 20px rgba(15,23,42,.15); }
    .backup-btn-primary { background: #1d4ed8; color: #fff; box-shadow: 0 8px 18px rgba(29,78,216,.2); }
    .backup-btn-secondary { background: #eef2ff; color: #3730a3; }
    .backup-btn-disabled { pointer-events: none; opacity: .55; }
    .backup-confirm-dialog { width: min(420px, calc(100% - 32px)); max-width: none; padding: 28px; border: 1px solid #e5e7eb; border-radius: 18px; background: #fff; color: #172033; box-shadow: 0 24px 70px rgba(15,23,42,.25); text-align: center; }
    .backup-confirm-dialog::backdrop { background: rgba(15,23,42,.6); }
    .backup-confirm-icon { width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 50%; display: grid; place-items: center; background: #eff6ff; color: #2563eb; font-size: 24px; }
    .backup-confirm-dialog h2 { margin: 0 0 10px; font-size: 1.1rem; font-weight: 800; }
    .backup-confirm-dialog p { margin: 0; color: #4b5563; font-size: .9rem; line-height: 1.5; }
    .backup-confirm-actions { display: flex; justify-content: center; gap: 10px; margin-top: 24px; }
    .backup-confirm-actions > .backup-btn, .backup-confirm-actions form { flex: 1; }
    .backup-confirm-actions form .backup-btn { width: 100%; }
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
    .history-download { min-width: 58px; height: 34px; padding: 0 9px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; color: #1d4ed8; background: #eff6ff; text-decoration: none; font-weight: 800; }
    .history-download:hover { background: #dbeafe; }
    .history-error { display: block; max-width: 210px; margin-top: 4px; color: #b91c1c; font-size: .67rem; line-height: 1.35; }
    .history-empty { padding: 46px 20px; text-align: center; color: #94a3b8; }
    .history-empty i { display: block; margin-bottom: 10px; font-size: 30px; }
    .history-pagination { padding: 14px 18px; border-top: 1px solid #f1f5f9; }
    html.admin-dark-nav .backups-page { background:#141414; color:#e5e5e5; }
    html.admin-dark-nav .backup-alert-success { background:#1c3928; border-color:#376f49; color:#b8edc4; }
    html.admin-dark-nav .backup-alert-error { background:#472a2a; border-color:#855050; color:#ffc1c1; }
    html.admin-dark-nav .backup-confirm-dialog { background:#202020; border-color:#4a4a4a; color:#f5f5f5; }
    html.admin-dark-nav .backup-confirm-dialog p { color:#c0c0c0; }
    html.admin-dark-nav .backup-confirm-icon { background:#1c3e42; color:#8dd7e3; }
    html.admin-dark-nav .backup-stat,html.admin-dark-nav .backup-panel { background:#202020; border-color:#353535; box-shadow:0 9px 24px rgba(0,0,0,.2); }
    html.admin-dark-nav .backup-stat-label,html.admin-dark-nav .backup-stat-meta,html.admin-dark-nav .backup-panel-header p,html.admin-dark-nav .calendar-label,html.admin-dark-nav .calendar-note,html.admin-dark-nav .calendar-disabled { color:#c0c0c0; }
    html.admin-dark-nav .backup-stat-value,html.admin-dark-nav .backup-panel-header h2 { color:#f5f5f5; }
    html.admin-dark-nav .backup-stat-icon,html.admin-dark-nav .history-file-icon { background:#1c3e42; color:#8dd7e3; }
    html.admin-dark-nav .backup-panel-header,html.admin-dark-nav .history-pagination { border-color:#353535; }
    html.admin-dark-nav .frequency-card label { background:#292929; border-color:#4a4a4a; color:#c0c0c0; }
    html.admin-dark-nav .frequency-card label strong { color:#f5f5f5; }
    html.admin-dark-nav .frequency-card input:checked + label { background:#1c3e42; border-color:#3e7278; color:#8dd7e3; box-shadow:inset 0 0 0 1px #3e7278; }
    html.admin-dark-nav .frequency-card input:checked + label strong { color:#b9edf3; }
    html.admin-dark-nav .schedule-field label { color:#e5e5e5; }
    html.admin-dark-nav .schedule-field input,html.admin-dark-nav .schedule-field select { background:#292929; border-color:#4a4a4a; color:#f5f5f5; color-scheme:dark; }
    html.admin-dark-nav .schedule-field select option { background:#292929; color:#f5f5f5; }
    html.admin-dark-nav .schedule-switch { background:#292929; border-color:#4a4a4a; }
    html.admin-dark-nav .schedule-switch strong { color:#f5f5f5; }
    html.admin-dark-nav .schedule-switch small { color:#c0c0c0; }
    html.admin-dark-nav .switch-slider { background:#555; }
    html.admin-dark-nav .switch-control input:checked + .switch-slider { background:#2563eb; }
    html.admin-dark-nav .calendar-card { background:#242424; border-color:#3e7278; }
    html.admin-dark-nav .calendar-day { color:#f5f5f5; }
    html.admin-dark-nav .calendar-weekday { color:#c0c0c0; }
    html.admin-dark-nav .calendar-time { background:#292929; border-color:#4a4a4a; color:#8dd7e3; }
    html.admin-dark-nav .history-table th { background:#292929; border-color:#4a4a4a; color:#d0d0d0; }
    html.admin-dark-nav .history-table td { border-color:#353535; color:#e5e5e5; }
    html.admin-dark-nav .history-table tbody tr:nth-child(even) { background:#292929; }
    html.admin-dark-nav .history-table tbody tr:hover { background:#333d2d; }
    html.admin-dark-nav .history-file strong { color:#f5f5f5; }
    html.admin-dark-nav .history-file small { color:#c0c0c0; }
    html.admin-dark-nav .status-completed { background:#1c3928; color:#b8edc4; }
    html.admin-dark-nav .status-processing { background:#493821; color:#ffe0a0; }
    html.admin-dark-nav .status-failed { background:#472a2a; color:#ffc1c1; }
    html.admin-dark-nav .history-download { background:#1c3e42; color:#8dd7e3; }
    html.admin-dark-nav .history-download:hover { background:#28545a; }
    html.admin-dark-nav .history-error { color:#ffc1c1; }
    html.admin-dark-nav .history-empty { color:#c0c0c0; }
    html.admin-dark-nav .history-pagination { color:#e5e5e5; }
    html.admin-dark-nav .history-pagination a,html.admin-dark-nav .history-pagination span { color:#e5e5e5; }
    html.admin-dark-nav .history-pagination .bg-white,html.admin-dark-nav .history-pagination .bg-gray-100 { background:#303030!important; border-color:#4a4a4a!important; }
    @media (max-width: 900px) { .backup-stats { grid-template-columns: repeat(2, 1fr); } .backup-grid { grid-template-columns: 1fr; } }
    @media (max-width: 680px) { .backups-hero { min-height: 220px; } .backups-hero-content { min-height: 220px; padding: 26px 20px; align-items: flex-start; flex-direction: column; justify-content: center; } .backups-heading { align-items: center; } .backups-actions { width: 100%; justify-content: flex-start; } .backup-btn { flex: 1; } .backup-alert { width: calc(100% - 24px); } .backup-stats { grid-template-columns: 1fr; margin-right: 12px; margin-left: 12px; } .backup-grid { margin-right: 12px; margin-left: 12px; } .history-panel { margin-right: 12px; margin-left: 12px; } .frequency-options { grid-template-columns: 1fr; } .frequency-card label { min-height: 65px; flex-direction: row; } .schedule-fields { grid-template-columns: 1fr; } }
    .backup-scroll-hint { display: none; }
    @media (max-width: 680px) {
        .backups-hero, .backups-hero-content { min-height: 0; }
        .backups-hero-content { padding: 22px 16px; gap: 18px; }
        .backups-heading-icon { display: none; }
        .backups-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .backups-actions form { grid-column: 1 / -1; }
        .backups-actions .backup-btn { width: 100%; min-width: 0; min-height: 42px; padding: 8px; font-size: .7rem; text-align: center; }
        .backup-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .backup-stat { min-width: 0; min-height: 125px; padding: 12px; }
        .backup-stat-top { align-items: flex-start; gap: 5px; }
        .backup-stat-label { min-width: 0; font-size: .62rem; line-height: 1.25; overflow-wrap: anywhere; }
        .backup-stat-icon { width: 30px; height: 30px; flex: none; font-size: .8rem; }
        .backup-stat-value { font-size: .83rem; white-space: normal; overflow-wrap: anywhere; line-height: 1.25; }
        .backup-stat-meta { font-size: .64rem; overflow-wrap: anywhere; }
        .backup-panel-header { padding: 16px; }
        .backup-panel-body, .calendar-wrap { padding: 16px; }
        .frequency-options { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 7px; }
        .frequency-card label { min-height: 72px; flex-direction: column; gap: 4px; padding: 8px 4px; text-align: center; }
        .frequency-card label strong { font-size: .7rem; }
        .backup-scroll-hint { display: block; margin: 0; padding: 9px 16px; color: #64748b; font-size: .7rem; }
        html.admin-dark-nav .backup-scroll-hint { color: #b8b8b8; }
        .history-table { min-width: 720px; }
        .history-table th, .history-table td { padding: 11px 12px; }
    }
    @media (max-width: 1100px) {
        .backups-hero-content { flex-direction: column; align-items: center; justify-content: center; padding: 24px; text-align: center; }
        .backups-heading { position: relative; width: 100%; justify-content: center; text-align: center; }
        .backups-heading-icon { position: absolute; left: 0; top: 50%; transform: translateY(-50%); }
        .backups-actions { width: 100%; justify-content: center; }
    }
    @media (max-width: 680px) {
        .backups-hero-content { align-items: center; padding: 22px 16px; }
        .backups-heading-icon { display: none; }
    }
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
                    <div>
                        <a href="{{ route('administrador.dashboard') }}" class="backups-dashboard-back"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al dashboard</a>
                        <h1>Respaldos de base de datos</h1>

                    </div>
                </div>
                <div class="backups-actions">
                    <a href="{{ $latestBackup ? route('administrador.backups.download-latest') : '#' }}" class="backup-btn backup-btn-ghost {{ $latestBackup ? '' : 'backup-btn-disabled' }}" @if(!$latestBackup) aria-disabled="true" @endif>
                        <i class="fas fa-download"></i> Descargar último SQL
                    </a>
                    <a href="{{ $latestBackup ? route('administrador.backups.download-latest-excel') : '#' }}" class="backup-btn backup-btn-ghost {{ $latestBackup ? '' : 'backup-btn-disabled' }}" @if(!$latestBackup) aria-disabled="true" @endif>
                        <i class="fas fa-file-excel"></i> Descargar último Excel
                    </a>
                    <a href="{{ $latestBackup ? route('administrador.backups.download-latest-csv') : '#' }}" class="backup-btn backup-btn-ghost {{ $latestBackup ? '' : 'backup-btn-disabled' }}" @if(!$latestBackup) aria-disabled="true" @endif title="Un CSV por tabla dentro de un ZIP">
                        <i class="fas fa-file-csv"></i> Descargar último CSV (ZIP)
                    </a>
                    <button type="button" class="backup-btn backup-btn-light" id="openBackupConfirm"><i class="fas fa-plus-circle"></i> Crear respaldo ahora</button>
                </div>
            </div>
        </section>

        <dialog class="backup-confirm-dialog" id="backupConfirmDialog" aria-labelledby="backupConfirmTitle" aria-describedby="backupConfirmMessage">
            <div class="backup-confirm-icon"><i class="fas fa-database" aria-hidden="true"></i></div>
            <h2 id="backupConfirmTitle">Crear respaldo</h2>
            <p id="backupConfirmMessage">¿Deseas crear un respaldo completo de la base de datos ahora?</p>
            <div class="backup-confirm-actions">
                <button type="button" class="backup-btn backup-btn-secondary" id="cancelBackupConfirm">Cancelar</button>
                <form method="POST" action="{{ route('administrador.backups.store') }}" id="createBackupForm">
                    @csrf
                    <button type="submit" class="backup-btn backup-btn-primary">Sí, crear respaldo</button>
                </form>
            </div>
        </dialog>

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
                <p class="backup-scroll-hint"><i class="fas fa-arrows-left-right mr-1"></i>Desliza la tabla para ver más columnas</p>
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
                                            <a href="{{ route('administrador.backups.download', $backup) }}" class="history-download" title="Descargar SQL"><i class="fas fa-file-code"></i> SQL</a>
                                            <a href="{{ route('administrador.backups.download-excel', $backup) }}" class="history-download" title="Descargar Excel"><i class="fas fa-file-excel"></i> Excel</a>
                                            <a href="{{ route('administrador.backups.download-csv', $backup) }}" class="history-download" title="Descargar CSV: un archivo por tabla dentro de un ZIP"><i class="fas fa-file-csv"></i> CSV</a>
                                        @else
                                            <span class="history-download backup-btn-disabled"><i class="fas fa-file-code"></i> SQL</span>
                                            <span class="history-download backup-btn-disabled"><i class="fas fa-file-excel"></i> Excel</span>
                                            <span class="history-download backup-btn-disabled"><i class="fas fa-file-csv"></i> CSV</span>
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
        const backupDialog = document.getElementById('backupConfirmDialog');
        document.getElementById('openBackupConfirm').addEventListener('click', () => backupDialog.showModal());
        document.getElementById('cancelBackupConfirm').addEventListener('click', () => backupDialog.close());
        document.getElementById('createBackupForm').addEventListener('submit', function () {
            this.querySelector('button[type="submit"]').disabled = true;
        });

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
