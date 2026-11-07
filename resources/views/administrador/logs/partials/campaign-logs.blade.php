@php
    $campaignActions = [
        'campaign_created' => 'Campaña creada',
        'campaign_updated' => 'Campaña editada',
        'campaign_deleted' => 'Campaña eliminada',
        'campaign_published' => 'Campaña publicada',
        'campaign_team_changed' => 'Equipo de campaña cambiado',
        'task_created' => 'Tarea creada',
        'task_updated' => 'Tarea editada',
        'task_deleted' => 'Tarea eliminada',
        'task_status_changed' => 'Estado de tarea cambiado',
        'task_published' => 'Tarea publicada',
        'task_assignees_changed' => 'Responsables cambiados',
    ];
    $campaignFields = [
        'nombre' => 'Nombre', 'titulo' => 'Título', 'descripcion' => 'Descripción',
        'objetivo_general' => 'Objetivo', 'publico_objetivo' => 'Público objetivo',
        'mensaje_principal' => 'Mensaje', 'tono_comunicacion' => 'Tono',
        'canales' => 'Canales', 'indicadores' => 'Indicadores',
        'modo_creacion' => 'Modo de creación', 'es_borrador' => 'Borrador',
        'fecha_inicio' => 'Inicio', 'fecha_fin' => 'Fin', 'fecha_limite' => 'Límite',
        'estado' => 'Estado', 'community_manager_id' => 'Community Manager',
        'disenador_id' => 'Diseñador', 'prioridad' => 'Prioridad',
        'tipo_contenido' => 'Tipo de contenido', 'entregable' => 'Entregable',
        'asignado_id' => 'Asignado', 'requiere_aprobacion' => 'Requiere aprobación',
        'visible_cliente' => 'Visible para cliente', 'publication_status' => 'Publicación',
        'published_at' => 'Publicado el',
        'disenadores' => 'Diseñadores', 'responsables' => 'Responsables',
    ];
    $selectedCampaign = $campaignOptions->firstWhere('campaign_id', (int) request('campaign_id'));
    $selectedClient = $clientOptions->firstWhere('client_id', (int) request('client_id'));
    $selectedStatus = request('campaign_status', 'all');
    $selectedAction = request('event_type', 'all');
@endphp

<div id="content-campaign" class="logs-panel campaign-logs-panel hidden bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100 mb-8">
    <div class="logs-panel-head px-6 py-4 border-b border-gray-100 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center"><i class="fas fa-bullhorn"></i></div>
            <div><h2 class="text-lg font-bold text-gray-800">Actividad de campañas</h2></div>
        </div>
        <span class="campaign-log-count">{{ $campaignLogs->total() }} {{ $campaignLogs->total() === 1 ? 'evento' : 'eventos' }}</span>
    </div>

    <form method="GET" action="{{ route('administrador.logs.index') }}" class="campaign-log-filters" id="campaign-log-filters">
        <input type="hidden" name="tab" value="campaign">
        <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
        <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">

        <div class="campaign-filter" data-campaign-dropdown>
            <label id="campaign-status-label">Estado actual</label>
            <input type="hidden" name="campaign_status" value="{{ request('campaign_status', 'all') }}">
            <button type="button" aria-labelledby="campaign-status-label" aria-expanded="false" data-dropdown-trigger><span data-dropdown-label>{{ ['all' => 'Todas', 'active' => 'Activas', 'inactive' => 'Inactivas'][$selectedStatus] ?? 'Todas' }}</span><i class="fas fa-chevron-down"></i></button>
            <div class="campaign-filter-menu" data-dropdown-menu hidden>
                <button type="button" data-value="all">Todas</button><button type="button" data-value="active">Activas</button><button type="button" data-value="inactive">Inactivas</button>
            </div>
        </div>
        <div class="campaign-filter" data-campaign-dropdown>
            <label id="campaign-client-label">Cliente</label>
            <input type="hidden" name="client_id" value="{{ request('client_id') }}">
            <button type="button" aria-labelledby="campaign-client-label" aria-expanded="false" data-dropdown-trigger><span data-dropdown-label>{{ $selectedClient?->client_name ?: 'Todos los clientes' }}</span><i class="fas fa-chevron-down"></i></button>
            <div class="campaign-filter-menu" data-dropdown-menu hidden>
                <button type="button" data-value="">Todos los clientes</button>
                @foreach($clientOptions as $client)
                    <button type="button" data-value="{{ $client->client_id }}">{{ $client->client_name ?: 'Cliente #'.$client->client_id }}</button>
                @endforeach
            </div>
        </div>
        <div class="campaign-filter" data-campaign-dropdown data-campaign-select>
            <label id="campaign-name-label">Campaña</label>
            <input type="hidden" name="campaign_id" value="{{ request('campaign_id') }}">
            <button type="button" aria-labelledby="campaign-name-label" aria-expanded="false" data-dropdown-trigger><span data-dropdown-label>{{ $selectedCampaign?->campaign_name ?: 'Todas las campañas' }}</span><i class="fas fa-chevron-down"></i></button>
            <div class="campaign-filter-menu" data-dropdown-menu hidden>
                <button type="button" data-value="">Todas las campañas</button>
                @foreach($campaignOptions as $campaign)
                    <button type="button" data-value="{{ $campaign->campaign_id }}" data-client-id="{{ $campaign->client_id }}">{{ $campaign->campaign_name }} · #{{ $campaign->campaign_id }}</button>
                @endforeach
            </div>
        </div>
        <div class="campaign-filter" data-campaign-dropdown>
            <label id="campaign-event-label">Tipo de cambio</label>
            <input type="hidden" name="event_type" value="{{ $selectedAction }}">
            <button type="button" aria-labelledby="campaign-event-label" aria-expanded="false" data-dropdown-trigger><span data-dropdown-label>{{ $campaignActions[$selectedAction] ?? 'Todos los eventos' }}</span><i class="fas fa-chevron-down"></i></button>
            <div class="campaign-filter-menu" data-dropdown-menu hidden>
                <button type="button" data-value="all">Todos los eventos</button>
                @foreach($campaignActions as $value => $label)<button type="button" data-value="{{ $value }}">{{ $label }}</button>@endforeach
            </div>
        </div>
        <div class="campaign-filter-actions">
            <button type="submit"><i class="fas fa-filter"></i> Filtrar</button>
            <a href="{{ route('administrador.logs.index', ['tab' => 'campaign']) }}">Limpiar</a>
        </div>
    </form>

    <p class="logs-scroll-hint"><i class="fas fa-arrows-left-right mr-1"></i>Desliza la tabla para ver más columnas</p>
    <div class="overflow-x-auto">
        <table class="logs-table min-w-full campaign-log-table">
            <thead><tr><th>Fecha y hora</th><th>Campaña / cliente</th><th>Evento</th><th>Usuario</th><th>Detalle</th></tr></thead>
            <tbody>
                @forelse($campaignLogs as $log)
                    <tr>
                        <td class="whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                        <td><strong>{{ $log->campaign_name }}</strong><small>{{ $log->client_name ?: 'Cliente #'.$log->client_id }} · Campaña #{{ $log->campaign_id }}</small></td>
                        <td><span class="campaign-event-badge">{{ $campaignActions[$log->action] ?? $log->action }}</span>@if($log->task_title)<small>Tarea: {{ $log->task_title }} #{{ $log->task_id }}</small>@endif</td>
                        <td><strong>{{ $log->user_name }}</strong><small>{{ $log->user_id ? 'Usuario #'.$log->user_id : 'Proceso del sistema' }}</small></td>
                        <td>
                            @if($log->changes)
                                <details class="campaign-log-details"><summary>Ver cambios ({{ count($log->changes) }})</summary>
                                    <ul>@foreach($log->changes as $field => $change)
                                        <li><strong>{{ $campaignFields[$field] ?? $field }}:</strong> {{ $change['from'] ?? '—' }} <span>→</span> {{ $change['to'] ?? '—' }}</li>
                                    @endforeach</ul>
                                </details>
                            @else
                                <span class="campaign-log-summary">{{ $log->action === 'campaign_deleted' ? 'Se eliminó la campaña' : ($log->action === 'task_deleted' ? 'Se eliminó la tarea' : 'Registro inicial') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No hay cambios de campañas para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($campaignLogs->hasPages())<div class="px-6 py-4 border-t border-gray-100 bg-gray-50">{{ $campaignLogs->links() }}</div>@endif
</div>

<style>
    .campaign-logs-panel .campaign-logs-intro{margin:3px 0 0;color:#76816e;font-size:.68rem}.campaign-log-count{padding:7px 10px;border-radius:999px;background:#edf4e4;color:#567521;font-size:.67rem;font-weight:800}
    .campaign-log-filters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr)) auto;gap:12px;padding:20px;border-bottom:1px solid #e2ead5;background:#f9fbf6}.campaign-filter{position:relative;min-width:0}.campaign-filter.is-open{z-index:20}.campaign-filter label{display:block;margin-bottom:6px;color:#4c5946;font-size:.65rem;font-weight:800}.campaign-filter [data-dropdown-trigger]{width:100%;height:42px;display:flex;align-items:center;justify-content:space-between;gap:8px;padding:0 11px;border:1px solid #cddbbd;border-radius:9px;background:#fff;color:#34452d;text-align:left;font-size:.71rem;font-weight:700}.campaign-filter [data-dropdown-label]{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.campaign-filter-menu{position:absolute;top:calc(100% + 4px);left:0;right:0;max-height:245px;overflow:auto;padding:5px;border:1px solid #cddbbd;border-radius:9px;background:#fff;box-shadow:0 12px 25px rgba(0,0,0,.16)}.campaign-filter-menu[hidden]{display:none}.campaign-filter-menu button{width:100%;padding:9px 8px;border:0;border-radius:6px;background:transparent;color:#34452d;text-align:left;font-size:.7rem}.campaign-filter-menu button:hover,.campaign-filter-menu button:focus{background:#edf4e4;outline:0}.campaign-filter-menu button[hidden]{display:none}.campaign-filter-actions{display:flex;align-items:end;gap:7px}.campaign-filter-actions button,.campaign-filter-actions a{height:42px;display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:0 12px;border-radius:9px;font-size:.69rem;font-weight:800;white-space:nowrap}.campaign-filter-actions button{background:#117e8c;color:#fff}.campaign-filter-actions a{border:1px solid #cddbbd;background:#fff;color:#53634b;text-decoration:none}.campaign-log-table strong,.campaign-log-table small{display:block}.campaign-log-table strong{color:#263044;font-size:.71rem}.campaign-log-table small{margin-top:3px;color:#788575;font-size:.64rem}.campaign-event-badge{display:inline-block;padding:5px 8px;border-radius:7px;background:#edf4e4;color:#486d22;font-size:.65rem;font-weight:800}.campaign-log-details summary{cursor:pointer;color:#117e8c;font-weight:800}.campaign-log-details ul{max-width:370px;margin:8px 0 0;padding-left:16px}.campaign-log-details li{margin:4px 0;overflow-wrap:anywhere}.campaign-log-details li strong{display:inline}.campaign-log-details li span{color:#7da533}.campaign-log-summary{color:#788575}
    html.admin-dark-nav .campaign-log-filters{background:#242424;border-color:#353535}html.admin-dark-nav .campaign-filter label,html.admin-dark-nav .campaign-log-table strong{color:#f5f5f5}html.admin-dark-nav .campaign-filter [data-dropdown-trigger],html.admin-dark-nav .campaign-filter-menu,html.admin-dark-nav .campaign-filter-actions a{background:#292929;border-color:#4a4a4a;color:#f5f5f5}html.admin-dark-nav .campaign-filter-menu button{color:#f5f5f5}html.admin-dark-nav .campaign-filter-menu button:hover,html.admin-dark-nav .campaign-filter-menu button:focus{background:#3b4a32}html.admin-dark-nav .campaign-log-count,html.admin-dark-nav .campaign-event-badge{background:#35442b;color:#c8eba5}html.admin-dark-nav .campaign-log-table small,html.admin-dark-nav .campaign-logs-intro{color:#b8b8b8}
    @media(max-width:1100px){.campaign-log-filters{grid-template-columns:repeat(2,minmax(0,1fr))}.campaign-filter-actions{grid-column:1/-1}}@media(max-width:620px){.campaign-log-filters{grid-template-columns:1fr}.campaign-filter-actions{grid-column:1}.campaign-filter-actions>*{flex:1}}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filters = document.getElementById('campaign-log-filters');
    if (!filters) return;
    const dropdowns = [...filters.querySelectorAll('[data-campaign-dropdown]')];
    const closeAll = () => dropdowns.forEach(dropdown => {
        dropdown.classList.remove('is-open');
        dropdown.querySelector('[data-dropdown-menu]').hidden = true;
        dropdown.querySelector('[data-dropdown-trigger]').setAttribute('aria-expanded', 'false');
    });
    const clientInput = filters.querySelector('[name="client_id"]');
    const campaignDropdown = filters.querySelector('[data-campaign-select]');
    const syncCampaigns = () => {
        campaignDropdown.querySelectorAll('[data-client-id]').forEach(option => {
            option.hidden = Boolean(clientInput.value && option.dataset.clientId !== clientInput.value);
        });
        const campaignInput = campaignDropdown.querySelector('[name="campaign_id"]');
        const selected = campaignDropdown.querySelector(`[data-value="${campaignInput.value}"]`);
        if (selected?.hidden) {
            campaignInput.value = '';
            campaignDropdown.querySelector('[data-dropdown-label]').textContent = 'Todas las campañas';
        }
    };
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        trigger.addEventListener('click', () => {
            const opening = menu.hidden;
            closeAll();
            if (opening) { menu.hidden = false; dropdown.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); }
        });
        menu.querySelectorAll('[data-value]').forEach(option => option.addEventListener('click', () => {
            dropdown.querySelector('input[type="hidden"]').value = option.dataset.value;
            dropdown.querySelector('[data-dropdown-label]').textContent = option.textContent.trim();
            closeAll();
            if (dropdown.contains(clientInput)) syncCampaigns();
            trigger.focus();
        }));
    });
    document.addEventListener('click', event => { if (!filters.contains(event.target)) closeAll(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeAll(); });
    syncCampaigns();
});
</script>
