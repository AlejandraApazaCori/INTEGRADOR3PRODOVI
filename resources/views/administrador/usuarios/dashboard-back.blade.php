<a href="{{ route('administrador.dashboard') }}" class="users-dashboard-back"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al dashboard</a>

@once
    @push('styles')
        <style>
            .users-dashboard-back{display:inline-flex;align-items:center;gap:8px;margin-bottom:13px;padding:7px 12px;border:1px solid rgba(255,255,255,.36);border-radius:9px;background:rgba(255,255,255,.12);color:#fff;font-size:.7rem;font-weight:800;transition:.18s}
            .users-dashboard-back:hover,.users-dashboard-back:focus-visible{border-color:#fff;background:#fff;color:#2563eb}
        </style>
    @endpush
@endonce
