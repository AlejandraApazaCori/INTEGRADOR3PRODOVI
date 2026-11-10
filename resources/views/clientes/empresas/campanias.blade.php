@extends('layouts.app2')

@section('title', 'Campañas de la empresa')

@section('content')
<main class="client-campaign-page">
    <a class="client-campaign-back" href="{{ route('empresas.show', $empresa) }}"><i class="fas fa-arrow-left"></i> {{ $empresa->nombre_empresa }}</a>
    <header><small>CAMPAÑAS DE TU EMPRESA</small><h1>Campañas</h1><p>Consulta las campañas activas y finalizadas de {{ $empresa->nombre_empresa }}.</p></header>
    <div class="client-campaign-cards">
        @forelse($campanias as $campania)
            <a href="{{ route('empresas.campanias.show', [$empresa, $campania]) }}" class="client-campaign-card">
                <span class="client-campaign-card-icon"><i class="fas fa-bullhorn"></i></span>
                <span><small>{{ ucfirst($campania->estado) }}</small><strong>{{ $campania->nombre }}</strong><em>{{ $campania->tareas_count }} {{ $campania->tareas_count === 1 ? 'tarea' : 'tareas' }} · {{ $campania->fecha_inicio ? \Illuminate\Support\Carbon::parse($campania->fecha_inicio)->format('d/m/Y') : '' }}</em></span>
                <i class="fas fa-arrow-right"></i>
            </a>
        @empty
            <div class="client-campaign-empty">Todavía no hay campañas asociadas a esta empresa.</div>
        @endforelse
    </div>
</main>
<style>
.client-campaign-page{max-width:1080px;margin:0 auto;padding:32px 22px 70px;color:#302834}.client-campaign-back{display:inline-flex;align-items:center;gap:8px;margin-bottom:22px;color:#117e8c;font-size:.78rem;font-weight:800;text-decoration:none}.client-campaign-page>header{padding:27px;border-radius:12px;background:#242426;color:#fff}.client-campaign-page>header small{color:#9bc249;font-size:.62rem;font-weight:900;letter-spacing:.1em}.client-campaign-page h1{margin:6px 0;font-size:1.8rem}.client-campaign-page>header p{margin:0;color:#dedee0;font-size:.8rem}.client-campaign-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:15px;margin-top:22px}.client-campaign-card{display:flex;align-items:center;gap:14px;padding:20px;border:1px solid #e2e4df;border-radius:12px;background:#fff;color:inherit;text-decoration:none;box-shadow:0 5px 18px #3028340a}.client-campaign-card:hover{border-color:#7da533}.client-campaign-card-icon{display:grid;place-items:center;width:44px;height:44px;flex:none;border-radius:10px;background:#eaf3db;color:#638522}.client-campaign-card>span:nth-child(2){min-width:0;flex:1}.client-campaign-card small,.client-campaign-card strong,.client-campaign-card em{display:block}.client-campaign-card small{color:#117e8c;font-size:.6rem;font-weight:900;text-transform:uppercase}.client-campaign-card strong{margin:4px 0;color:#302834;font-size:.92rem}.client-campaign-card em{color:#788078;font-size:.67rem;font-style:normal}.client-campaign-card>i{color:#7da533}.client-campaign-empty{padding:28px;border:1px dashed #d8ddd2;border-radius:12px;color:#687168}
</style>
@endsection
