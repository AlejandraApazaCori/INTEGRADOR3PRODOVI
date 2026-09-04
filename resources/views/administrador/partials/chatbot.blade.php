@push('scripts')
    <script>
        // Pasa los datos necesarios desde Laravel al JS
        window.adminChatbotData = {
            activeCampaignsCount: {{ \App\Models\Campania::where('estado', 'activa')->count() }},
            pausedCampaignsCount: {{ \App\Models\Campania::where('estado', 'pausada')->count() }},
            completedCampaignsCount: {{ \App\Models\Campania::where('estado', 'finalizada')->count() }},
            totalCampaignsCount: {{ \App\Models\Campania::count() }},
            
            pendingTasksCount: {{ \App\Models\Tarea::whereIn('estado', ['no_iniciado', 'pendiente'])->count() }},
            inProgressTasksCount: {{ \App\Models\Tarea::where('estado', 'en_curso')->count() }},
            completedTasksCount: {{ \App\Models\Tarea::whereIn('estado', ['entregado', 'aprobado', 'publicado'])->count() }},
            rejectedTasksCount: {{ \App\Models\Tarea::where('estado', 'reformular')->count() }},
            
            activeSubscriptionsCount: {{ \App\Models\Suscripcion::where('estado', 'activa')->count() }},
            pendingSubscriptionsCount: {{ \App\Models\Suscripcion::where('estado', 'pendiente')->count() }},
            canceledSubscriptionsCount: {{ \App\Models\Suscripcion::where('estado', 'cancelada')->count() }}
        };
    </script>
    <script src="{{ asset('js/admin-chatbot.js') }}"></script>
@endpush
