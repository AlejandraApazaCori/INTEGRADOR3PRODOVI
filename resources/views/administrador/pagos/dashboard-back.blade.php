<a href="{{ route('administrador.dashboard') }}" class="payments-dashboard-back"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al dashboard</a>

@once
    @push('styles')
        <style>
            .payments-dashboard-back{display:inline-flex;align-items:center;gap:8px;margin-bottom:13px;padding:7px 12px;border:1px solid rgba(255,255,255,.36);border-radius:9px;background:rgba(255,255,255,.12);color:#fff;font-size:.7rem;font-weight:800;text-decoration:none;transition:.18s}
            .payments-dashboard-back:hover,.payments-dashboard-back:focus-visible{border-color:#fff;background:#fff;color:#2563eb}
        </style>
    @endpush
@endonce
