@extends('layouts.app')

@section('title', 'Registrar Pago Manual')

@section('content')
<style>
/* ── Page wrapper ────────────────────────────────────────────── */
.pwiz-page-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);
  padding: 2rem 0;
}

/* ── Container ────────────────────────────────────────────────── */
.pwiz-container { max-width: 1280px; margin: 0 auto; padding: 0 1rem; }

/* ── Banner con fondo geométrico ──────────────────────────────── */
.pwiz-banner {
  position: relative;
  overflow: hidden;
  border-radius: 24px;
  background:
    linear-gradient(135deg, #4f46e5 25%, transparent 25%) -50px 0,
    linear-gradient(225deg, #4f46e5 25%, transparent 25%) -50px 0,
    linear-gradient(315deg, #4f46e5 25%, transparent 25%),
    linear-gradient(45deg,  #4f46e5 25%, transparent 25%),
    linear-gradient(to bottom, #3b82f6 0%, #2563eb 100%);
  background-size: 100px 100px, 100px 100px, 100px 100px, 100px 100px, 100% 100%;
  background-color: #1d4ed8;
  box-shadow: 0 20px 40px rgba(37, 99, 235, 0.18);
  padding: 2rem 2.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  margin-bottom: 1.75rem;
}

.pwiz-banner::before {
  content: ''; position: absolute; right: -50px; top: -50px;
  width: 200px; height: 200px; background: rgba(255,255,255,0.06); border-radius: 50%;
}
.pwiz-banner::after {
  content: ''; position: absolute; right: 80px; bottom: -70px;
  width: 240px; height: 240px; background: rgba(255,255,255,0.04); border-radius: 50%;
}

.pwiz-banner-overlay {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 0% 0%, rgba(255,255,255,0.2) 0%, transparent 50%),
    radial-gradient(circle at 100% 0%, rgba(255,255,255,0.2) 0%, transparent 50%),
    radial-gradient(circle at 100% 100%, rgba(255,255,255,0.2) 0%, transparent 50%),
    radial-gradient(circle at 0% 100%, rgba(255,255,255,0.2) 0%, transparent 50%);
  background-size: 50% 50%;
  background-position: 0 0, 100% 0, 100% 100%, 0 100%;
  background-repeat: no-repeat;
}

.pwiz-banner-icon {
  width: 58px; height: 58px; background: rgba(255,255,255,0.18);
  border-radius: 16px; display: flex; align-items: center; justify-content: center;
  font-size: 24px; color: white; flex-shrink: 0; z-index: 1;
}
.pwiz-banner-text { z-index: 1; }
.pwiz-banner-text h1 { font-size: 1.55rem; font-weight: 800; color: white; margin: 0 0 4px; letter-spacing: -0.02em; }
.pwiz-banner-text p { font-size: 0.85rem; color: rgba(255,255,255,0.72); margin: 0; }
.pwiz-banner-back {
  margin-left: auto; display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 18px; background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.28);
  border-radius: 10px; color: white; font-size: 0.82rem; font-weight: 600;
  text-decoration: none; transition: background 0.15s; z-index: 1; white-space: nowrap;
}
.pwiz-banner-back:hover { background: rgba(255,255,255,0.28); }

/* ── Stepper ──────────────────────────────────────────────────── */
.pwiz-stepper {
  display: flex; align-items: center; background: white;
  border-radius: 16px; padding: 1.25rem 2rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e8edf3;
  margin-bottom: 1.75rem;
}
.pwiz-step { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.pwiz-step-circle {
  width: 40px; height: 40px; border-radius: 50%;
  border: 2px solid #e2e8f0; background: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.875rem; font-weight: 700; color: #94a3b8;
  transition: all 0.3s; position: relative; z-index: 1;
}
.pwiz-step.active .pwiz-step-circle {
  background: #2563eb; border-color: #2563eb; color: white;
  box-shadow: 0 0 0 5px rgba(37,99,235,0.15);
}
.pwiz-step.done .pwiz-step-circle { background: #10b981; border-color: #10b981; color: white; }
.pwiz-step-label { font-size: 0.7rem; font-weight: 600; color: #94a3b8; white-space: nowrap; transition: color 0.3s; }
.pwiz-step.active .pwiz-step-label { color: #2563eb; }
.pwiz-step.done .pwiz-step-label { color: #10b981; }
.pwiz-step-line {
  flex: 1; height: 2px; background: #e2e8f0; margin: 0 10px;
  margin-bottom: 24px; transition: background 0.3s;
}
.pwiz-step-line.done { background: #10b981; }
.pwiz-step-line.active { background: linear-gradient(90deg, #10b981, #2563eb); }

/* ── Panels ───────────────────────────────────────────────────── */
.pwiz-panel {
  background: white; border-radius: 20px; border: 1px solid #e8edf3;
  padding: 2rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  animation: pwizFade 0.22s ease;
}
@keyframes pwizFade {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}
.pwiz-panel-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
.pwiz-panel-subtitle { font-size: 0.8rem; color: #64748b; margin-bottom: 1.75rem; }

/* ── Choice cards ────────────────────────────────────────────── */
.pwiz-choices { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.pwiz-choice-card {
  display: flex; flex-direction: column; align-items: center;
  gap: 12px; padding: 2rem 1.5rem;
  border: 2px solid #e2e8f0; border-radius: 18px;
  cursor: pointer; transition: all 0.2s;
  background: white; text-align: center; text-decoration: none;
}
.pwiz-choice-card:hover {
  border-color: #93c5fd; background: #f8fbff;
  transform: translateY(-4px); box-shadow: 0 10px 28px rgba(37,99,235,0.14);
}
.pwiz-choice-card.danger:hover {
  border-color: #fca5a5; background: #fff5f5;
  box-shadow: 0 10px 28px rgba(239,68,68,0.12);
}
.pwiz-choice-icon {
  width: 66px; height: 66px; border-radius: 18px;
  display: flex; align-items: center; justify-content: center;
  font-size: 28px; transition: all 0.2s;
}
.pwiz-choice-card:not(.danger) .pwiz-choice-icon { background: #eff6ff; color: #2563eb; }
.pwiz-choice-card:not(.danger):hover .pwiz-choice-icon { background: #dbeafe; color: #1d4ed8; }
.pwiz-choice-card.danger .pwiz-choice-icon { background: #fef2f2; color: #ef4444; }
.pwiz-choice-title { font-size: 1rem; font-weight: 700; color: #0f172a; }
.pwiz-choice-desc { font-size: 0.78rem; color: #64748b; line-height: 1.45; }
.pwiz-choice-arrow { font-size: 0.75rem; color: #94a3b8; margin-top: 4px; }
.pwiz-choice-card.danger .pwiz-choice-arrow { color: #ef4444; }

/* ── Back button ──────────────────────────────────────────────── */
.pwiz-back-btn {
  display: inline-flex; align-items: center; gap: 7px;
  margin-top: 1.25rem; padding: 9px 18px;
  border: 1px solid #e2e8f0; border-radius: 10px;
  background: white; color: #64748b;
  font-size: 0.8rem; font-weight: 600;
  cursor: pointer; transition: all 0.15s;
}
.pwiz-back-btn:hover { color: #374151; background: #f8fafc; }

/* ── Form fields ──────────────────────────────────────────────── */
.pwiz-field { margin-bottom: 1rem; }
.pwiz-field label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 5px; }
.pwiz-input {
  width: 100%; padding: 9px 13px;
  border: 1px solid #e2e8f0; border-radius: 10px;
  font-size: 0.875rem; color: #0f172a; background: #fafbfc;
  transition: border-color 0.15s, box-shadow 0.15s; outline: none;
}
.pwiz-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); background: white; }
.pwiz-input-wrap { position: relative; }
.pwiz-input-wrap .pi-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; pointer-events: none; }
.pwiz-input-wrap .pi-prefix { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; font-weight: 700; color: #64748b; }
.pwiz-input-wrap .pi-icon ~ .pwiz-input,
.pwiz-input-wrap .pi-prefix ~ .pwiz-input { padding-left: 36px; }

/* ── Search dropdown ──────────────────────────────────────────── */
.pwiz-dropdown {
  position: absolute; top: calc(100% + 4px); left: 0; right: 0;
  background: white; border: 1px solid #e2e8f0; border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.09); max-height: 210px;
  overflow-y: auto; z-index: 50;
}
.pwiz-user-opt { padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f8fafc; transition: background 0.1s; }
.pwiz-user-opt:last-child { border-bottom: 0; }
.pwiz-user-opt:hover { background: #eff6ff; }
.pwiz-user-opt .n { font-size: 0.85rem; font-weight: 600; color: #0f172a; }
.pwiz-user-opt .e { font-size: 0.75rem; color: #94a3b8; margin-top: 1px; }

/* ── User chip ────────────────────────────────────────────────── */
.pwiz-user-chip {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 14px; background: #eff6ff;
  border: 1px solid #bfdbfe; border-radius: 12px; margin-top: 8px;
}
.pwiz-user-avatar {
  width: 38px; height: 38px; border-radius: 50%; background: #2563eb;
  display: flex; align-items: center; justify-content: center;
  color: white; font-weight: 700; font-size: 15px; flex-shrink: 0;
}
.pwiz-user-chip-info { flex: 1; }
.pwiz-user-chip-name { font-size: 0.875rem; font-weight: 600; color: #1e3a5f; }
.pwiz-user-chip-email { font-size: 0.75rem; color: #3b82f6; margin-top: 1px; }
.pwiz-chip-remove { background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 16px; padding: 4px; border-radius: 6px; transition: all 0.15s; }
.pwiz-chip-remove:hover { color: #ef4444; background: #fef2f2; }

/* ── Method badge ────────────────────────────────────────────── */
.pwiz-method-badge {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 6px 14px; border-radius: 20px;
  font-size: 0.75rem; font-weight: 700; margin-bottom: 1.5rem;
}
.pwiz-method-badge.fisico { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.pwiz-method-badge.qr { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }

/* ── Section label ────────────────────────────────────────────── */
.pwiz-section-label {
  font-size: 0.75rem; font-weight: 700; color: #374151;
  text-transform: uppercase; letter-spacing: 0.05em;
  display: flex; align-items: center; gap: 7px; margin-bottom: 10px;
}

/* ── Grid ─────────────────────────────────────────────────────── */
.pwiz-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

/* ── Cambio box ───────────────────────────────────────────────── */
.pwiz-cambio-box {
  background: #f0fdf4; border: 1px solid #bbf7d0;
  border-radius: 12px; padding: 1rem 1.25rem; margin-top: 0.5rem;
}
.pwiz-cambio-label { font-size: 0.72rem; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px; }
.pwiz-cambio-value { font-size: 1.5rem; font-weight: 800; color: #16a34a; }
.pwiz-cambio-value.neg { font-size: 0.95rem; color: #dc2626; }

/* ── QR box ───────────────────────────────────────────────────── */
.pwiz-qr-box {
  border: 2px dashed #c7d2fe; border-radius: 16px;
  padding: 2rem 1.5rem; text-align: center;
  display: flex; flex-direction: column; align-items: center; gap: 14px;
  background: #f8faff;
}
.pwiz-qr-placeholder {
  width: 120px; height: 120px; border-radius: 14px;
  background: #e0e7ff; display: flex; align-items: center;
  justify-content: center; font-size: 44px; color: #818cf8;
  transition: all 0.3s;
}
.pwiz-qr-placeholder.ok { background: white; border: 2px solid #bbf7d0; color: #10b981; box-shadow: 0 4px 16px rgba(16,185,129,0.15); }
.btn-gen-qr {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 10px 24px; border: none; border-radius: 12px;
  background: #2563eb; color: white; font-size: 0.875rem; font-weight: 700;
  cursor: pointer; transition: all 0.15s; box-shadow: 0 2px 8px rgba(37,99,235,0.28);
}
.btn-gen-qr:hover { background: #1d4ed8; transform: translateY(-1px); }
.btn-gen-qr:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

/* ── Divider ──────────────────────────────────────────────────── */
.pwiz-hr { border: none; border-top: 1px solid #f1f5f9; margin: 1.5rem 0; }

/* ── Form footer ──────────────────────────────────────────────── */
.pwiz-form-footer {
  display: flex; justify-content: space-between; align-items: center;
  margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9;
}
.pwiz-submit-btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 11px 32px; border: none; border-radius: 12px;
  background: #1d4ed8; color: white; font-size: 0.875rem; font-weight: 700;
  cursor: pointer; transition: all 0.15s; box-shadow: 0 2px 8px rgba(37,99,235,0.28);
}
.pwiz-submit-btn:hover { background: #1e40af; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(37,99,235,0.34); }
.pwiz-submit-btn:active { transform: none; }
.pwiz-submit-btn:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

/* ── Success overlay ──────────────────────────────────────────── */
.pwiz-overlay {
  position: fixed; inset: 0; background: rgba(15,23,42,0.55);
  backdrop-filter: blur(5px); z-index: 9999;
  display: flex; align-items: center; justify-content: center;
  animation: overlayIn 0.3s ease forwards;
}
@keyframes overlayIn { from { opacity: 0; } to { opacity: 1; } }
.pwiz-success-modal {
  background: white; border-radius: 24px; padding: 3rem 2.5rem;
  text-align: center; max-width: 380px; width: 90%;
  box-shadow: 0 28px 60px rgba(0,0,0,0.18);
  animation: modalIn 0.38s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}
@keyframes modalIn {
  from { opacity: 0; transform: scale(0.8) translateY(24px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}
.pwiz-success-check {
  width: 84px; height: 84px; border-radius: 50%;
  background: linear-gradient(135deg, #10b981, #059669);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1.5rem; font-size: 34px; color: white;
  box-shadow: 0 8px 28px rgba(16,185,129,0.38);
  animation: checkIn 0.45s 0.2s cubic-bezier(0.34,1.56,0.64,1) both;
}
@keyframes checkIn { from { transform: scale(0); } to { transform: scale(1); } }
.pwiz-success-modal h2 { font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; }
.pwiz-success-modal p { font-size: 0.875rem; color: #64748b; margin-bottom: 1.5rem; }
.pwiz-progress-bar { height: 4px; background: #e2e8f0; border-radius: 4px; overflow: hidden; margin-bottom: 0.75rem; }
.pwiz-progress-fill { height: 100%; background: linear-gradient(90deg, #10b981, #059669); border-radius: 4px; width: 0%; transition: width 2.6s linear; }
.pwiz-redirect-label { font-size: 0.75rem; color: #94a3b8; }

/* ── Hidden ───────────────────────────────────────────────────── */
.pwiz-hidden { display: none !important; }

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 600px) {
  .pwiz-choices, .pwiz-grid-2 { grid-template-columns: 1fr; }
  .pwiz-banner { flex-wrap: wrap; padding: 1.5rem 1.25rem; }
  .pwiz-banner-back { margin-left: 0; }
  .pwiz-panel { padding: 1.25rem; }
  .pwiz-stepper { padding: 1rem 1.25rem; }
  .btn-action { flex: 1; min-width: 120px; }
}

/* ── Action buttons styles ───────────────────────────────────── */
.btn-action {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border: none;
  border-radius: 0.875rem;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  overflow: hidden;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.btn-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.btn-action:active {
  transform: translateY(0);
}

.btn-action__mark {
  position: absolute;
  right: -20px;
  top: -20px;
  width: 80px;
  height: 80px;
  opacity: 0.15;
  pointer-events: none;
  overflow: hidden;
}

.btn-action__mark svg {
  width: 100%;
  height: 100%;
  fill: currentColor;
}

.btn-blue {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  color: white;
}

.btn-blue:hover {
  background: linear-gradient(135deg, #1d4ed8, #1e40af);
}

.btn-indigo {
  background: linear-gradient(135deg, #4f46e5, #4338ca);
  color: white;
}

.btn-indigo:hover {
  background: linear-gradient(135deg, #4338ca, #3730a3);
}

.btn-purple {
  background: linear-gradient(135deg, #a855f7, #9333ea);
  color: white;
}

.btn-purple:hover {
  background: linear-gradient(135deg, #9333ea, #7e22ce);
}
</style>

<div class="pwiz-page-wrapper">
  <div class="pwiz-container">

    <!-- Action buttons -->
    <div class="flex flex-wrap gap-3 pb-6 pt-2">
      <a href="{{ route('administrador.pagos.index') }}" class="btn-action btn-blue">
        <i class="fas fa-table-columns"></i>
        General
        <span class="btn-action__mark" aria-hidden="true">
          <svg viewBox="0 0 392.94 418.13">
            <path d="M243.7,418.13C198.37,312.3,118.14,268.5,0,294.73,135.19,238.54,203.38,148.99,149.24,0c49.45,103.91,130.68,145.05,243.7,123.4-127.69,63.18-168.91,165.26-149.24,294.73Z"></path>
          </svg>
        </span>
      </a>
      <a href="{{ route('administrador.pagos.analiticas') }}" class="btn-action btn-indigo">
        <i class="fas fa-chart-line"></i>
        Analíticas
        <span class="btn-action__mark" aria-hidden="true">
          <svg viewBox="0 0 392.94 418.13">
            <path d="M243.7,418.13C198.37,312.3,118.14,268.5,0,294.73,135.19,238.54,203.38,148.99,149.24,0c49.45,103.91,130.68,145.05,243.7,123.4-127.69,63.18-168.91,165.26-149.24,294.73Z"></path>
          </svg>
        </span>
      </a>
      <button type="button" id="abrirReporteMensual" class="btn-action btn-purple">
        <i class="fas fa-file-pdf"></i>
        Reporte mensual
        <span class="btn-action__mark" aria-hidden="true">
          <svg viewBox="0 0 392.94 418.13">
            <path d="M243.7,418.13C198.37,312.3,118.14,268.5,0,294.73,135.19,238.54,203.38,148.99,149.24,0c49.45,103.91,130.68,145.05,243.7,123.4-127.69,63.18-168.91,165.26-149.24,294.73Z"></path>
          </svg>
        </span>
      </button>
    </div>

    <div style="max-width: 800px; margin: 0 auto;">

      {{-- ── Banner ─────────────────────────────────────────────────── --}}
      <div class="pwiz-banner">
        <div class="pwiz-banner-overlay"></div>
        <div class="pwiz-banner-icon"><i class="fas fa-credit-card"></i></div>
        <div class="pwiz-banner-text">
          <h1>Registrar pago manual</h1>
          <p>Registra una suscripción y pago para un cliente en pocos pasos.</p>
        </div>
        <a href="{{ route('administrador.pagos.index') }}" class="pwiz-banner-back">
          <i class="fas fa-arrow-left"></i> Volver
        </a>
      </div>

      {{-- ── Stepper ───────────────────────────────────────────────── --}}
      <div class="pwiz-stepper">
        <div class="pwiz-step active" id="step-ind-1">
          <div class="pwiz-step-circle" id="step-circle-1">1</div>
          <div class="pwiz-step-label">Tipo de pago</div>
        </div>
        <div class="pwiz-step-line" id="step-line-1"></div>
        <div class="pwiz-step" id="step-ind-2">
          <div class="pwiz-step-circle" id="step-circle-2">2</div>
          <div class="pwiz-step-label">Cliente</div>
        </div>
        <div class="pwiz-step-line" id="step-line-2"></div>
        <div class="pwiz-step" id="step-ind-3">
          <div class="pwiz-step-circle" id="step-circle-3">3</div>
          <div class="pwiz-step-label">Datos del pago</div>
        </div>
      </div>

      {{-- ── Validation errors (returning from failed submit) ── --}}
      @if ($errors->any())
      <div style="margin-bottom:1.25rem;">
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg flex gap-3">
          <i class="fas fa-exclamation-circle text-red-400 mt-0.5 flex-shrink-0"></i>
          <div>
            <p class="text-sm font-semibold text-red-700">Corrige los siguientes errores:</p>
            <ul class="mt-1 list-disc list-inside text-sm text-red-600">
              @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
          </div>
        </div>
      </div>
      @endif

      {{-- ── AJAX error container ───────────────────────────────── --}}
      <div id="ajaxErrors" class="pwiz-hidden" style="margin-bottom:1.25rem;">
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg flex gap-3">
          <i class="fas fa-exclamation-circle text-red-400 mt-0.5 flex-shrink-0"></i>
          <div>
            <p class="text-sm font-semibold text-red-700">Corrige los siguientes errores:</p>
            <ul id="ajaxErrorList" class="mt-1 list-disc list-inside text-sm text-red-600"></ul>
          </div>
        </div>
      </div>

      {{-- ═══ PASO 1 — Tipo de pago ═══ --}}
      <div class="pwiz-panel" id="panel-step1">
        <p class="pwiz-panel-title">Selecciona el tipo de pago</p>
        <p class="pwiz-panel-subtitle">Elige cómo se realizará el cobro al cliente.</p>
        <div class="pwiz-choices">
          <div class="pwiz-choice-card" onclick="pwizSelectMethod('fisico')">
            <div class="pwiz-choice-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="pwiz-choice-title">Pago Físico</div>
            <div class="pwiz-choice-desc">El cliente paga en efectivo de forma presencial.</div>
            <div class="pwiz-choice-arrow"><i class="fas fa-arrow-right"></i> Seleccionar</div>
          </div>
          <div class="pwiz-choice-card" onclick="pwizSelectMethod('qr')">
            <div class="pwiz-choice-icon"><i class="fas fa-qrcode"></i></div>
            <div class="pwiz-choice-title">Pago QR</div>
            <div class="pwiz-choice-desc">El cliente paga escaneando un código QR bancario.</div>
            <div class="pwiz-choice-arrow"><i class="fas fa-arrow-right"></i> Seleccionar</div>
          </div>
        </div>
      </div>

      {{-- ═══ PASO 2 — ¿El cliente existe? ═══ --}}
      <div class="pwiz-panel pwiz-hidden" id="panel-step2">
        <p class="pwiz-panel-title">¿El cliente ya está registrado?</p>
        <p class="pwiz-panel-subtitle">Indica si el cliente tiene una cuenta en el sistema.</p>
        <div class="pwiz-choices">
          <div class="pwiz-choice-card" onclick="pwizSelectClient(true)">
            <div class="pwiz-choice-icon"><i class="fas fa-user-check"></i></div>
            <div class="pwiz-choice-title">Sí, ya existe</div>
            <div class="pwiz-choice-desc">Buscaré al cliente en el sistema para asignar la suscripción.</div>
            <div class="pwiz-choice-arrow"><i class="fas fa-arrow-right"></i> Continuar</div>
          </div>
          <a href="{{ url('/administrador/usuarios/create') }}" class="pwiz-choice-card danger">
            <div class="pwiz-choice-icon"><i class="fas fa-user-plus"></i></div>
            <div class="pwiz-choice-title">No existe aún</div>
            <div class="pwiz-choice-desc">Ir a crear el cliente antes de registrar el pago.</div>
            <div class="pwiz-choice-arrow"><i class="fas fa-external-link-alt"></i> Crear cliente</div>
          </a>
        </div>
        <button type="button" class="pwiz-back-btn" onclick="pwizGoTo(1)">
          <i class="fas fa-arrow-left"></i> Atrás
        </button>
      </div>

      {{-- ═══ PASO 3 — Datos del pago ═══ --}}
      <div class="pwiz-panel pwiz-hidden" id="panel-step3">

        <form action="{{ route('administrador.pagos.manual.store') }}" method="POST" id="pagoForm">
          @csrf
          <input type="hidden" name="metodo"          id="metodoInput"   value="{{ old('metodo', 'fisico') }}">
          <input type="hidden" name="create_new_user"                     value="0">
          <input type="hidden" name="fecha_inicio"                        value="{{ date('Y-m-d') }}">
          <input type="hidden" name="monto"           id="montoFinal"     value="{{ old('monto') }}">

          {{-- Method badge --}}
          <div id="methodBadge" class="pwiz-method-badge fisico">
            <i class="fas fa-money-bill-wave"></i>
            <span id="methodBadgeText">Pago Físico</span>
          </div>

          {{-- ── Buscar cliente ───────────────────────────────────── --}}
          <div class="pwiz-section-label">
            <i class="fas fa-user" style="color:#2563eb;"></i> Buscar cliente
          </div>
          <div class="pwiz-field">
            <div class="pwiz-input-wrap" style="position:relative;" id="searchWrap">
              <i class="pi-icon fas fa-search"></i>
              <input type="text" id="userSearch" class="pwiz-input"
                     placeholder="Escribe el nombre o correo del cliente..." autocomplete="off">
              <div id="userDropdown" class="pwiz-dropdown pwiz-hidden">
                @foreach($usuarios as $usuario)
                  <div class="pwiz-user-opt"
                       data-id="{{ $usuario->id }}"
                       data-name="{{ $usuario->name }}"
                       data-email="{{ $usuario->email }}">
                    <div class="n">{{ $usuario->name }}</div>
                    <div class="e">{{ $usuario->email }}</div>
                  </div>
                @endforeach
              </div>
            </div>
            <div id="selectedUserChip" class="pwiz-hidden">
              <div class="pwiz-user-chip">
                <div class="pwiz-user-avatar" id="userInitial">U</div>
                <div class="pwiz-user-chip-info">
                  <div class="pwiz-user-chip-name" id="selectedUserName">-</div>
                  <div class="pwiz-user-chip-email" id="selectedUserEmail">-</div>
                </div>
                <button type="button" class="pwiz-chip-remove" id="btnRemoveUser" title="Quitar">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
            <input type="hidden" name="usuario_id" id="usuario_id" value="{{ old('usuario_id') }}">
          </div>

          <hr class="pwiz-hr">

          {{-- ── Plan + Monto ─────────────────────────────────────── --}}
          <div class="pwiz-grid-2">
            <div>
              <div class="pwiz-section-label" style="margin-bottom:8px;">
                <i class="fas fa-box" style="color:#2563eb;"></i> Plan de suscripción
              </div>
              <div class="pwiz-field">
                <select name="plan_id" id="selectPlan" class="pwiz-input" required>
                  <option value="">- Elige un plan -</option>
                  @foreach($planes as $plan)
                    <option value="{{ $plan->id }}"
                            data-precio="{{ $plan->precio }}"
                            data-moneda="{{ $plan->moneda ?? 'BS' }}"
                            {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                      {{ $plan->nombre }} - {{ $plan->precio }} {{ $plan->moneda ?? 'BS' }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div>
              <div class="pwiz-section-label" style="margin-bottom:8px;">
                <i class="fas fa-tag" style="color:#2563eb;"></i> Monto a cobrar
              </div>
              <div class="pwiz-field">
                <div class="pwiz-input-wrap">
                  <span class="pi-prefix" id="monedaSymbol">Bs</span>
                  <input type="number" step="0.01" id="inputMontoCobrar" class="pwiz-input"
                         placeholder="0.00" value="{{ old('monto') }}"
                         style="font-weight:700; color:#1d4ed8; font-size:1.05rem;">
                </div>
              </div>
            </div>
          </div>

          <hr class="pwiz-hr">

          {{-- ── FÍSICO: Monto entregado + Cambio ────────────────── --}}
          <div id="fisicoSection">
            <div class="pwiz-section-label">
              <i class="fas fa-money-bill-wave" style="color:#16a34a;"></i> Efectivo recibido
            </div>
            <div class="pwiz-field" style="max-width:260px;">
              <label>Monto entregado por el cliente</label>
              <div class="pwiz-input-wrap">
                <span class="pi-prefix" id="monedaSymbolEnt">Bs</span>
                <input type="number" step="0.01" id="montoEntregado" class="pwiz-input" placeholder="0.00">
              </div>
            </div>
            <div id="cambioBox" class="pwiz-cambio-box pwiz-hidden">
              <div class="pwiz-cambio-label"><i class="fas fa-exchange-alt"></i> Cambio a devolver</div>
              <div class="pwiz-cambio-value" id="cambioValue">Bs 0.00</div>
            </div>
          </div>

          {{-- ── QR: Generar código QR ─────────────────────────────── --}}
          <div id="qrSection" class="pwiz-hidden">
            <div class="pwiz-section-label">
              <i class="fas fa-qrcode" style="color:#2563eb;"></i> Código QR de pago
            </div>
            <div class="pwiz-qr-box">
              <div class="pwiz-qr-placeholder" id="qrPlaceholder">
                <i class="fas fa-qrcode"></i>
              </div>
              <p style="font-size:0.82rem; color:#64748b; margin:0;" id="qrHint">
                Genera el código QR para que el cliente realice el pago.
              </p>
              <button type="button" class="btn-gen-qr" id="btnGenQr" onclick="pwizGenerateQR()">
                <i class="fas fa-qrcode"></i> Generar QR
              </button>
            </div>
          </div>

          {{-- ── Footer ───────────────────────────────────────────── --}}
          <div class="pwiz-form-footer">
            <button type="button" class="pwiz-back-btn" onclick="pwizGoTo(2)" style="margin-top:0;">
              <i class="fas fa-arrow-left"></i> Atrás
            </button>
            <button type="submit" class="pwiz-submit-btn" id="btnConfirmar">
              <i class="fas fa-check-circle"></i>
              <span id="submitBtnText">Confirmar registro</span>
            </button>
          </div>
        </form>

      </div>{{-- /panel-step3 --}}
    </div>{{-- /inner container --}}
  </div>{{-- /pwiz-container --}}
</div>{{-- /pwiz-page-wrapper --}}

{{-- ── Success overlay ─────────────────────────────────────────── --}}
<div class="pwiz-overlay pwiz-hidden" id="successOverlay">
  <div class="pwiz-success-modal">
    <div class="pwiz-success-check"><i class="fas fa-check"></i></div>
    <h2>¡Pago registrado!</h2>
    <p>El pago se registró exitosamente en el sistema.</p>
    <div class="pwiz-progress-bar">
      <div class="pwiz-progress-fill" id="progressFill"></div>
    </div>
    <div class="pwiz-redirect-label">Redirigiendo a pagos...</div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  /* ── State ───────────────────────────────────────────────────── */
  let selectedMethod = '{{ old("metodo", "fisico") }}';

  /* ── Stepper ─────────────────────────────────────────────────── */
  function updateStepper(step) {
    [1, 2, 3].forEach(i => {
      const ind    = document.getElementById('step-ind-' + i);
      const circle = document.getElementById('step-circle-' + i);
      ind.classList.remove('active', 'done');
      if (i < step) {
        ind.classList.add('done');
        circle.innerHTML = '<i class="fas fa-check" style="font-size:12px;"></i>';
      } else if (i === step) {
        ind.classList.add('active');
        circle.textContent = i;
      } else {
        circle.textContent = i;
      }
    });
    [1, 2].forEach(i => {
      const line = document.getElementById('step-line-' + i);
      line.classList.remove('done', 'active');
      if (i < step - 1)    line.classList.add('done');
      else if (i === step - 1) line.classList.add('active');
    });
  }

  function pwizGoTo(step) {
    [1, 2, 3].forEach(i => document.getElementById('panel-step' + i).classList.add('pwiz-hidden'));
    document.getElementById('panel-step' + step).classList.remove('pwiz-hidden');
    updateStepper(step);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
  window.pwizGoTo = pwizGoTo;

  /* ── Step 1: choose method ───────────────────────────────────── */
  window.pwizSelectMethod = function (method) {
    selectedMethod = method;
    document.getElementById('metodoInput').value = method;

    const badge     = document.getElementById('methodBadge');
    const badgeText = document.getElementById('methodBadgeText');
    if (method === 'fisico') {
      badge.className  = 'pwiz-method-badge fisico';
      badgeText.innerHTML = '<i class="fas fa-money-bill-wave"></i> Pago Físico';
    } else {
      badge.className  = 'pwiz-method-badge qr';
      badgeText.innerHTML = '<i class="fas fa-qrcode"></i> Pago QR';
    }
    pwizGoTo(2);
  };

  /* ── Step 2: client exists? ──────────────────────────────────── */
  window.pwizSelectClient = function (exists) {
    if (exists) {
      applyMethodSections(selectedMethod);
      pwizGoTo(3);
    }
    /* if !exists — the card is an <a> that navigates to create user */
  };

  function applyMethodSections(method) {
    const fisico = document.getElementById('fisicoSection');
    const qr     = document.getElementById('qrSection');
    if (method === 'fisico') {
      fisico.classList.remove('pwiz-hidden');
      qr.classList.add('pwiz-hidden');
    } else {
      fisico.classList.add('pwiz-hidden');
      qr.classList.remove('pwiz-hidden');
    }
  }

  /* ── User search ─────────────────────────────────────────────── */
  const userSearch       = document.getElementById('userSearch');
  const userDropdown     = document.getElementById('userDropdown');
  const selectedUserChip = document.getElementById('selectedUserChip');
  const searchWrap       = document.getElementById('searchWrap');
  const usuarioId        = document.getElementById('usuario_id');

  userSearch.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    const opts = userDropdown.querySelectorAll('.pwiz-user-opt');
    let found = false;
    if (q.length > 0) {
      userDropdown.classList.remove('pwiz-hidden');
      opts.forEach(o => {
        const match = o.dataset.name.toLowerCase().includes(q) || o.dataset.email.toLowerCase().includes(q);
        o.classList.toggle('pwiz-hidden', !match);
        if (match) found = true;
      });
      if (!found) userDropdown.classList.add('pwiz-hidden');
    } else {
      userDropdown.classList.add('pwiz-hidden');
    }
  });

  userDropdown.querySelectorAll('.pwiz-user-opt').forEach(o => {
    o.addEventListener('click', function () {
      usuarioId.value = this.dataset.id;
      document.getElementById('selectedUserName').textContent  = this.dataset.name;
      document.getElementById('selectedUserEmail').textContent = this.dataset.email;
      document.getElementById('userInitial').textContent       = this.dataset.name.charAt(0).toUpperCase();
      searchWrap.style.display = 'none';
      userDropdown.classList.add('pwiz-hidden');
      selectedUserChip.classList.remove('pwiz-hidden');
    });
  });

  document.getElementById('btnRemoveUser').addEventListener('click', function () {
    usuarioId.value = '';
    userSearch.value = '';
    searchWrap.style.display = '';
    selectedUserChip.classList.add('pwiz-hidden');
  });

  document.addEventListener('click', function (e) {
    if (!searchWrap.contains(e.target)) userDropdown.classList.add('pwiz-hidden');
  });

  /* ── Plan selection ──────────────────────────────────────────── */
  const selectPlan     = document.getElementById('selectPlan');
  const inputMontoCobrar = document.getElementById('inputMontoCobrar');
  const monedaSymbol   = document.getElementById('monedaSymbol');
  const monedaSymbolEnt = document.getElementById('monedaSymbolEnt');
  const montoFinal     = document.getElementById('montoFinal');

  selectPlan.addEventListener('change', function () {
    const sel    = this.options[this.selectedIndex];
    const precio = sel.getAttribute('data-precio');
    const moneda = sel.getAttribute('data-moneda');
    if (precio) {
      inputMontoCobrar.value = precio;
      montoFinal.value = precio;
      const sym = moneda === 'BS' ? 'Bs' : '$';
      monedaSymbol.textContent = sym;
      monedaSymbolEnt.textContent = sym;
    } else {
      inputMontoCobrar.value = '';
      montoFinal.value = '';
    }
    calculateCambio();
  });

  inputMontoCobrar.addEventListener('input', function () {
    montoFinal.value = this.value;
    calculateCambio();
  });

  /* ── Cambio calculation ──────────────────────────────────────── */
  const montoEntregado = document.getElementById('montoEntregado');
  const cambioBox      = document.getElementById('cambioBox');
  const cambioValue    = document.getElementById('cambioValue');

  function calculateCambio() {
    const ent    = parseFloat(montoEntregado.value) || 0;
    const cobrar = parseFloat(inputMontoCobrar.value) || 0;
    if (ent > 0 && cobrar > 0) {
      cambioBox.classList.remove('pwiz-hidden');
      const cambio = ent - cobrar;
      if (cambio >= 0) {
        cambioValue.textContent = 'Bs ' + cambio.toFixed(2);
        cambioValue.className = 'pwiz-cambio-value';
      } else {
        cambioValue.textContent = 'Monto insuficiente (' + cambio.toFixed(2) + ')';
        cambioValue.className = 'pwiz-cambio-value neg';
      }
    } else {
      cambioBox.classList.add('pwiz-hidden');
    }
  }
  montoEntregado.addEventListener('input', calculateCambio);

  /* ── QR generation (visual) ──────────────────────────────────── */
  window.pwizGenerateQR = function () {
    const btn         = document.getElementById('btnGenQr');
    const placeholder = document.getElementById('qrPlaceholder');
    const hint        = document.getElementById('qrHint');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
    setTimeout(() => {
      placeholder.classList.add('ok');
      placeholder.innerHTML = '<i class="fas fa-check-circle" style="font-size:40px;"></i>';
      hint.innerHTML = '<strong style="color:#16a34a;">QR generado.</strong> El cliente puede escanear y pagar.';
      btn.innerHTML  = '<i class="fas fa-sync"></i> Regenerar QR';
      btn.disabled   = false;
      document.getElementById('submitBtnText').textContent = 'Confirmar pago QR';
    }, 1200);
  };

  /* ── Form submit via AJAX ────────────────────────────────────── */
  document.getElementById('pagoForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('btnConfirmar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    // Sync monto final
    montoFinal.value = inputMontoCobrar.value;

    const formData = new FormData(this);
    try {
      const response = await fetch(this.action, {
        method: 'POST',
        body: formData,
        redirect: 'follow',
      });

      const finalUrl = response.url || '';
      const isSuccess = finalUrl.length > 0 &&
                        finalUrl.includes('/administrador/pagos') &&
                        !finalUrl.includes('manual');

      if (isSuccess) {
        showSuccess();
      } else {
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const errItems = doc.querySelectorAll('.text-red-600 li, ul.list-disc.list-inside.text-sm.text-red-600 li');

        if (errItems.length > 0) {
          const list = document.getElementById('ajaxErrorList');
          list.innerHTML = '';
          errItems.forEach(li => {
            const el = document.createElement('li');
            el.textContent = li.textContent.trim();
            list.appendChild(el);
          });
          document.getElementById('ajaxErrors').classList.remove('pwiz-hidden');
          window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
          /* Fallback: assume success if response was OK */
          if (response.ok) { showSuccess(); return; }
        }

        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle"></i> <span id="submitBtnText">Confirmar registro</span>';
      }
    } catch (err) {
      /* Network issue — submit normally */
      this.submit();
    }
  });

  function showSuccess() {
    const overlay = document.getElementById('successOverlay');
    overlay.classList.remove('pwiz-hidden');
    setTimeout(() => {
      document.getElementById('progressFill').style.width = '100%';
    }, 60);
    setTimeout(() => {
      window.location.href = '{{ route("administrador.pagos.index") }}';
    }, 2800);
  }

  /* ── Init: returning from validation error ──────────────────── */
  @if ($errors->any())
    selectedMethod = '{{ old("metodo", "fisico") }}';
    document.getElementById('metodoInput').value = selectedMethod;
    applyMethodSections(selectedMethod);
    const badge     = document.getElementById('methodBadge');
    const badgeText = document.getElementById('methodBadgeText');
    if (selectedMethod === 'qr') {
      badge.className = 'pwiz-method-badge qr';
      badgeText.innerHTML = '<i class="fas fa-qrcode"></i> Pago QR';
    }
    pwizGoTo(3);
  @endif

  @if (old('usuario_id'))
    /* Restore selected user chip */
    (function() {
      const uid = '{{ old("usuario_id") }}';
      const opt = document.querySelector('.pwiz-user-opt[data-id="' + uid + '"]');
      if (opt) {
        document.getElementById('usuario_id').value = uid;
        document.getElementById('selectedUserName').textContent  = opt.dataset.name;
        document.getElementById('selectedUserEmail').textContent = opt.dataset.email;
        document.getElementById('userInitial').textContent       = opt.dataset.name.charAt(0).toUpperCase();
        document.getElementById('searchWrap').style.display = 'none';
        document.getElementById('selectedUserChip').classList.remove('pwiz-hidden');
      }
    })();
  @endif

});
</script>
@endsection