<?php

namespace Database\Seeders;

use App\Models\Campania;
use App\Models\CampaniaMensaje;
use App\Models\CampaniaMensajeContexto;
use App\Models\CodigoPago;
use App\Models\ComprobantePago;
use App\Models\Empresa;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\PlanMarketing;
use App\Models\PreguntaCuestionario;
use App\Models\RecursoEmpresa;
use App\Models\RespuestaCuestionario;
use App\Models\Reunion;
use App\Models\Role;
use App\Models\Suscripcion;
use App\Models\Tarea;
use App\Models\TareaArchivo;
use App\Models\TareaComentario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CampaniasDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesSeeder::class);

        if (! Plan::query()->exists()) {
            $this->call(PlanesSeeder::class);
        }

        if (! PreguntaCuestionario::query()->exists()) {
            $this->call(TemasCuestionarioSeeder::class);
        }

        DB::transaction(function () {
            $team = $this->team();
            $plans = Plan::query()->where('activo', true)->orderBy('orden')->get()->values();

            if ($plans->isEmpty()) {
                throw new \RuntimeException('No existen planes activos para crear las campañas de demostración.');
            }

            foreach ($this->campaignDefinitions() as $index => $definition) {
                $plan = $plans[$index % $plans->count()];
                $this->seedCampaign($definition, $index, $plan, $team);
            }
        });

        $this->command?->info('Datos demo creados: 2 campañas activas del mes y 10 finalizadas del año, con pagos y contenido relacionado.');
    }

    private function seedCampaign(array $definition, int $index, Plan $plan, array $team): void
    {
        $number = $index + 1;
        $client = $this->client($definition, $number);
        $subscription = $this->subscription($client, $plan, $definition, $number);
        $company = $this->company($client, $subscription, $definition);
        $payment = $this->payment($client, $subscription, $plan, $definition, $number, $team['admin']);

        $this->questionnaire($company, $definition);
        $this->marketingPlan($company, $subscription, $definition);

        $campaign = $this->campaign(
            $client,
            $subscription,
            $company,
            $definition,
            $number,
            $team
        );

        $tasks = $this->tasks($campaign, $definition, $number, $team);
        $this->resources($company, $definition, $team['admin']);
        $this->meetings($campaign, $definition, $team);
        $this->feedback($campaign, $tasks, $client, $definition, $team);

        $receipt = ComprobantePago::withTrashed()->firstOrNew(['pago_id' => $payment->id]);
        $receipt->ruta_pdf = null;
        $receipt->deleted_at = null;
        $receipt->save();
    }

    private function team(): array
    {
        return [
            'admin' => $this->teamMember(
                ['Super Administrador', 'Administrador'],
                'Administrador de Datos Demo',
                'admin.datos.demo@prodovidigital.test',
                'Administrador'
            ),
            'cm' => $this->teamMember(
                ['Community Manager'],
                'Carla Mendoza',
                'carla_mendoza_cm@prodovidigital.com',
                'Community Manager'
            ),
            'designer' => $this->teamMember(
                ['Diseñador', 'Disenador'],
                'Manuel Paye',
                'manuel_paye_disenador@prodovidigital.com',
                'Diseñador'
            ),
        ];
    }

    private function teamMember(array $acceptedRoles, string $name, string $email, string $fallbackRole): User
    {
        $existing = User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('nombre_rol', $acceptedRoles))
            ->orderBy('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $role = Role::withTrashed()->firstOrNew(['nombre_rol' => $fallbackRole]);
        $role->deleted_at = null;
        $role->save();

        $user = User::withTrashed()->firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->email_verified_at = now();
        $user->deleted_at = null;
        if (! $user->exists || blank($user->password)) {
            $user->password = Hash::make(bin2hex(random_bytes(24)));
        }
        $user->save();
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function client(array $definition, int $number): User
    {
        $role = Role::query()->where('nombre_rol', 'Cliente')->firstOrFail();
        $email = sprintf('cliente.campania.%02d@demo.prodovi.test', $number);
        $client = User::withTrashed()->firstOrNew(['email' => $email]);
        $client->name = $definition['client'];
        $client->phone = '7'.str_pad((string) (6100000 + $number), 7, '0', STR_PAD_LEFT);
        $client->email_verified_at = $definition['start']->copy()->subDays(8);
        $client->social_setup_skipped = true;
        $client->deleted_at = null;
        if (! $client->exists || blank($client->password)) {
            $client->password = Hash::make(bin2hex(random_bytes(24)));
        }
        $client->save();
        $client->roles()->sync([$role->id]);
        $this->stamp($client, $definition['start']->copy()->subDays(10));

        return $client;
    }

    private function subscription(User $client, Plan $plan, array $definition, int $number): Suscripcion
    {
        $paymentCode = $this->paymentCode($number);
        $existingPayment = Pago::withTrashed()->where('codigo_pago', $paymentCode)->first();
        $subscription = $existingPayment
            ? Suscripcion::withTrashed()->find($existingPayment->suscripcion_id)
            : null;
        $subscription ??= new Suscripcion;

        $subscription->fill([
            'usuario_id' => $client->id,
            'plan_id' => $plan->id,
            'estado' => $definition['status'] === 'activa' ? 'activa' : 'finalizada',
            'fecha_inicio' => $definition['start']->copy()->startOfDay(),
            'fecha_fin' => $definition['end']->copy()->endOfDay(),
            'vigencia_activada_at' => $definition['start']->copy()->startOfDay(),
            'fecha_cancelacion' => $definition['status'] === 'finalizada'
                ? $definition['end']->copy()->endOfDay()
                : null,
            'metodo_pago' => $number % 2 === 0 ? 'qr' : 'fisico',
        ]);
        $subscription->deleted_at = null;
        $subscription->save();
        $this->stamp($subscription, $definition['start']->copy()->subDays(7));

        return $subscription;
    }

    private function company(User $client, Suscripcion $subscription, array $definition): Empresa
    {
        $company = Empresa::withTrashed()->firstOrNew(['suscripcion_id' => $subscription->id]);
        $company->fill([
            'usuario_id' => $client->id,
            'nombre_empresa' => $definition['company'],
            'tipo_empresa' => $definition['type'],
            'direccion' => $definition['address'],
            'descripcion' => $definition['company_description'],
            'cuestionario_completado' => true,
            'resumen_ejecutivo' => $this->executiveSummary($definition),
        ]);
        $company->deleted_at = null;
        $company->save();
        $this->stamp($company, $definition['start']->copy()->subDays(6));

        return $company;
    }

    private function payment(
        User $client,
        Suscripcion $subscription,
        Plan $plan,
        array $definition,
        int $number,
        User $admin
    ): Pago {
        $paidAt = $definition['start']->copy()->subDays(7)->setTime(10, 15);
        if ($paidAt->year < now()->year) {
            $paidAt = now()->copy()->startOfYear()->setTime(10, 15);
        }

        $payment = Pago::withTrashed()->firstOrNew(['codigo_pago' => $this->paymentCode($number)]);
        $payment->fill([
            'usuario_id' => $client->id,
            'suscripcion_id' => $subscription->id,
            'plan_id' => $plan->id,
            'monto' => $plan->precio,
            'moneda' => $plan->moneda,
            'metodo' => $number % 2 === 0 ? 'qr' : 'fisico',
            'estado' => 'completado',
            'aprobado_por' => $admin->id,
            'fecha_aprobacion' => $paidAt->copy()->addMinutes(20),
            'fecha_pago' => $paidAt,
            'confirmacion_email_enviada_at' => $paidAt->copy()->addMinutes(22),
            'visto' => true,
            'provider' => null,
            'provider_transaction_id' => null,
            'provider_reference' => null,
        ]);
        $payment->deleted_at = null;
        $payment->save();
        $this->stamp($payment, $paidAt);

        if ($payment->metodo === 'fisico') {
            $code = CodigoPago::query()->firstOrNew(['codigo' => sprintf('FISD%04d', $number)]);
            $code->fill([
                'usuario_id' => $client->id,
                'pago_id' => $payment->id,
                'utilizado' => true,
                'fecha_utilizacion' => $paidAt,
                'descargado_at' => $paidAt->copy()->subHour(),
            ]);
            $code->save();
            $this->stamp($code, $paidAt->copy()->subHour());
        }

        return $payment;
    }

    private function questionnaire(Empresa $company, array $definition): void
    {
        $answers = [
            $definition['company_description'],
            $definition['offer'],
            $definition['audience_label'],
            'Conseguir más clientes | Dar a conocer mi marca',
            'Buena calidad | Atención personalizada | Confianza',
            $definition['tone'].' | Cercana y amigable',
            'Mantener mensajes claros, precios transparentes y responder consultas durante el horario comercial.',
        ];

        PreguntaCuestionario::query()->orderBy('orden')->get()->each(
            function (PreguntaCuestionario $question, int $index) use ($company, $answers) {
                $answer = RespuestaCuestionario::withTrashed()->firstOrNew([
                    'empresa_id' => $company->id,
                    'pregunta_id' => $question->id,
                ]);
                $answer->respuesta = $answers[$index] ?? 'Información validada por el cliente.';
                $answer->deleted_at = null;
                $answer->save();
            }
        );
    }

    private function marketingPlan(Empresa $company, Suscripcion $subscription, array $definition): void
    {
        $plan = PlanMarketing::withTrashed()->firstOrNew(['suscripcion_id' => $subscription->id]);
        $plan->fill([
            'empresa_id' => $company->id,
            'contenido' => $this->marketingPlanContent($definition),
            'estado' => $definition['status'] === 'activa' ? 'activo' : 'archivado',
        ]);
        $plan->deleted_at = null;
        $plan->save();
        $this->stamp($plan, $definition['start']->copy()->subDays(3));
    }

    private function campaign(
        User $client,
        Suscripcion $subscription,
        Empresa $company,
        array $definition,
        int $number,
        array $team
    ): Campania {
        $campaign = Campania::withTrashed()->firstOrNew([
            'nombre' => sprintf('DEMO %02d · %s', $number, $definition['campaign']),
        ]);
        $campaign->fill([
            'descripcion' => $definition['description'],
            'objetivo_general' => $definition['objective'],
            'publico_objetivo' => json_encode([
                ['tipo_edades' => $definition['audience_label'], 'descripcion' => $definition['audience']],
                ['tipo_edades' => 'Clientes recurrentes de 28 a 55 años', 'descripcion' => 'Personas que ya conocen la marca y pueden recomendarla o volver a comprar.'],
            ], JSON_UNESCAPED_UNICODE),
            'mensaje_principal' => $definition['message'],
            'tono_comunicacion' => $definition['tone'],
            'canales' => ['Facebook', 'Instagram', 'WhatsApp'],
            'indicadores' => ['Alcance', 'Interacciones', 'Consultas recibidas', 'Conversiones'],
            'modo_creacion' => 'manual',
            'es_borrador' => false,
            'ai_generation_metadata' => null,
            'fecha_inicio' => $definition['start']->toDateString(),
            'fecha_fin' => $definition['end']->toDateString(),
            'estado' => $definition['status'],
            'visto' => true,
            'usuario_creador_id' => $team['admin']->id,
            'community_manager_id' => $team['cm']->id,
            'disenador_id' => $team['designer']->id,
            'usuario_cliente_id' => $client->id,
            'reuniones_cliente_por_mes' => 2,
            'suscripcion_id' => $subscription->id,
        ]);
        $campaign->deleted_at = null;
        $campaign->save();
        $this->stamp($campaign, $definition['start']);
        $campaign->empresas()->syncWithoutDetaching([$company->id]);
        $campaign->disenadores()->syncWithoutDetaching([$team['designer']->id]);

        return $campaign;
    }

    private function tasks(Campania $campaign, array $definition, int $number, array $team): array
    {
        $templates = [
            ['Definir línea visual', 'Preparar paleta, tipografías y criterios gráficos para las piezas.', 'Guía visual aprobada', 'otro', 'alta', 'designer'],
            ['Crear carrusel educativo', 'Diseñar un carrusel que explique el principal beneficio de la oferta.', 'Carrusel de 6 láminas', 'carrusel', 'media', 'designer'],
            ['Redactar contenido comercial', 'Preparar textos con llamada a la acción y respuestas para consultas frecuentes.', 'Copys y banco de respuestas', 'post', 'alta', 'cm'],
            ['Revisar resultados del periodo', 'Consolidar avances, aprendizajes y próximos ajustes de la campaña.', 'Informe de resultados', 'otro', 'media', 'cm'],
        ];

        $tasks = [];
        $duration = max(1, $definition['start']->diffInDays($definition['end']));
        foreach ($templates as $taskIndex => [$title, $description, $deliverable, $type, $priority, $assigneeKey]) {
            $offset = (int) floor(($duration * $taskIndex) / count($templates));
            $start = $definition['start']->copy()->addDays($offset);
            $due = $start->copy()->addDays(max(2, (int) floor($duration / 5)));
            if ($due->greaterThan($definition['end'])) {
                $due = $definition['end']->copy();
            }

            $isFinished = $definition['status'] === 'finalizada';
            $state = $isFinished ? 'publicado' : ['aprobado', 'en_curso', 'pendiente', 'no_iniciado'][$taskIndex];
            $assignee = $team[$assigneeKey];
            $task = Tarea::withTrashed()->firstOrNew([
                'campania_id' => $campaign->id,
                'titulo' => $title,
            ]);
            $task->fill([
                'descripcion' => $description.' Marca: '.$definition['company'].'.',
                'entregable' => $deliverable,
                'tipo_contenido' => $type,
                'fecha_inicio' => $start->toDateString(),
                'fecha_limite' => $due->toDateString(),
                'estado' => $state,
                'prioridad' => $priority,
                'requiere_aprobacion' => $taskIndex < 3,
                'visible_cliente' => $taskIndex !== 0,
                'creador_id' => $team['admin']->id,
                'asignado_id' => $assignee->id,
                'publication_status' => null,
                'publication_scheduled_at' => null,
                'published_at' => null,
                'facebook_post_id' => null,
                'instagram_media_id' => null,
                'publication_error' => null,
                'publication_message' => null,
                'publication_platforms' => null,
            ]);
            $task->deleted_at = null;
            $task->save();
            $this->stamp($task, $start);
            $task->responsables()->syncWithoutDetaching([$assignee->id, $team['admin']->id]);
            $this->taskEvidence($task, $number, $taskIndex, $assignee, $definition, $state);
            $tasks[] = $task;
        }

        return $tasks;
    }

    private function taskEvidence(
        Tarea $task,
        int $campaignNumber,
        int $taskIndex,
        User $assignee,
        array $definition,
        string $state
    ): void {
        if ($taskIndex !== 1) {
            return;
        }

        $path = sprintf('demo-campanias/campania-%02d/entregable-tarea-%d.txt', $campaignNumber, $taskIndex + 1);
        $contents = "Entregable de demostración\nCampaña: {$definition['campaign']}\nTarea: {$task->titulo}\n";
        Storage::disk('public')->put($path, $contents);

        $file = TareaArchivo::withTrashed()->firstOrNew([
            'tarea_id' => $task->id,
            'nombre_original' => 'entregable-carrusel.txt',
        ]);
        $file->fill([
            'user_id' => $assignee->id,
            'ruta_archivo' => $path,
            'extension' => 'txt',
            'mime_type' => 'text/plain',
            'tamanio' => strlen($contents),
            'descripcion' => 'Borrador y lineamientos del entregable de demostración.',
            'estado' => in_array($state, ['aprobado', 'publicado'], true) ? 'aprobado' : 'pendiente',
            'visto' => true,
        ]);
        $file->deleted_at = null;
        $file->save();

        $comment = TareaComentario::withTrashed()->firstOrNew([
            'comentable_id' => $task->id,
            'comentable_type' => Tarea::class,
            'contenido' => 'Se revisó el enfoque y el entregable ya cuenta con observaciones del equipo.',
        ]);
        $comment->user_id = $assignee->id;
        $comment->deleted_at = null;
        $comment->save();
    }

    private function resources(Empresa $company, array $definition, User $admin): void
    {
        $resources = [
            ['Manual de marca y referencias', 'cliente', true, 'https://example.com/recursos/manual-de-marca'],
            ['Tablero de contenidos aprobado', 'administracion', true, 'https://example.com/recursos/tablero-de-contenidos'],
            ['Notas internas de producción', 'administracion', false, 'https://example.com/recursos/notas-produccion'],
        ];

        foreach ($resources as [$name, $origin, $visible, $url]) {
            $resource = RecursoEmpresa::withTrashed()->firstOrNew([
                'empresa_id' => $company->id,
                'nombre' => $name,
            ]);
            $resource->fill([
                'tipo' => 'enlace',
                'archivo_path' => null,
                'url' => $url.'?empresa='.urlencode($definition['company']),
                'origen' => $origin,
                'visible_cliente' => $visible,
                'creado_por_id' => $origin === 'administracion' ? $admin->id : null,
            ]);
            $resource->deleted_at = null;
            $resource->save();
        }
    }

    private function meetings(Campania $campaign, array $definition, array $team): void
    {
        $dates = [
            $definition['start']->copy()->addDays(3)->setTime(10, 0),
            $definition['start']->copy()->addDays(max(7, (int) floor($definition['start']->diffInDays($definition['end']) * .65)))->setTime(16, 0),
        ];

        foreach ($dates as $meetingIndex => $startsAt) {
            if ($startsAt->greaterThan($definition['end']->copy()->endOfDay())) {
                $startsAt = $definition['end']->copy()->subDay()->setTime(16, 0);
            }
            $isPast = $startsAt->isPast();
            $meeting = Reunion::query()->firstOrNew([
                'campania_id' => $campaign->id,
                'titulo' => $meetingIndex === 0 ? 'Reunión de inicio y objetivos' : 'Revisión de avances y próximos pasos',
            ]);
            $meeting->fill([
                'creador_id' => $team['admin']->id,
                'descripcion' => $meetingIndex === 0
                    ? 'Alineación de objetivos, público, mensajes y responsabilidades.'
                    : 'Presentación de avances, retroalimentación y acuerdos del siguiente periodo.',
                'plataforma' => 'meet',
                'enlace' => 'https://example.com/reunion-demo/'.$campaign->id.'/'.($meetingIndex + 1),
                'fecha_inicio' => $startsAt,
                'fecha_fin' => $startsAt->copy()->addMinutes(45),
                'estado' => $isPast ? 'realizada' : 'agendada',
                'origen' => 'administrador',
            ]);
            $meeting->save();
            $meeting->participantes()->syncWithoutDetaching([
                $team['admin']->id,
                $team['cm']->id,
                $team['designer']->id,
                $campaign->usuario_cliente_id,
            ]);
        }
    }

    private function feedback(Campania $campaign, array $tasks, User $client, array $definition, array $team): void
    {
        $context = CampaniaMensajeContexto::query()->firstOrCreate(
            ['campania_id' => $campaign->id, 'nombre' => 'Revisión creativa'],
            ['creado_por_id' => $team['admin']->id]
        );

        $messages = [
            [$team['admin'], null, null, 'cliente_equipo', 'Bienvenidos. En este espacio centralizaremos acuerdos, avances y aprobaciones de la campaña.'],
            [$team['cm'], $tasks[2] ?? null, null, 'equipo', 'Los textos iniciales están listos para revisión interna antes de compartirlos con el cliente.'],
            [$client, null, $context, 'cliente_equipo', 'La propuesta mantiene el tono de la marca. Por favor destaquen también la atención personalizada.'],
        ];

        foreach ($messages as $messageIndex => [$sender, $task, $messageContext, $audience, $content]) {
            $message = CampaniaMensaje::withTrashed()->firstOrNew([
                'campania_id' => $campaign->id,
                'contenido' => $content,
            ]);
            $message->fill([
                'remitente_id' => $sender->id,
                'tarea_id' => $task?->id,
                'contexto_id' => $messageContext?->id,
                'mensaje_padre_id' => null,
                'audiencia' => $audience,
            ]);
            $message->deleted_at = null;
            $message->save();
            $this->stamp($message, $definition['start']->copy()->addDays($messageIndex + 1)->setTime(9 + $messageIndex, 15));

            $recipientIds = collect([$team['admin']->id, $team['cm']->id, $team['designer']->id, $client->id])
                ->reject(fn ($id) => (int) $id === (int) $sender->id)
                ->values()
                ->all();
            $message->destinatarios()->syncWithPivotValues($recipientIds, [
                'leido_at' => $messageIndex < 2 ? $message->created_at->copy()->addHour() : null,
            ], false);
        }
    }

    private function campaignDefinitions(): array
    {
        $activeStart = now()->copy()->startOfMonth();
        $activeEnd = now()->copy()->endOfMonth();
        $businesses = [
            ['Alma Café', 'Gastronomía', 'Valeria Arce', 'Café de especialidad, repostería artesanal y desayunos.', 'Café de especialidad y desayunos', 'Jóvenes y profesionales de 20 a 40 años', 'Personas que buscan un lugar cómodo para reunirse, trabajar y disfrutar productos artesanales.', 'Cercano y cálido', 'Cada pausa puede convertirse en un momento especial.', 'Incrementar visitas al local y consultas por WhatsApp.', 'Temporada de desayunos que conectan'],
            ['Nativa Botánica', 'Cosmética natural', 'Mariana Flores', 'Marca boliviana de cuidado personal con ingredientes naturales.', 'Línea de cuidado facial natural', 'Mujeres y hombres de 22 a 45 años', 'Consumidores interesados en bienestar, trazabilidad y productos responsables.', 'Educativo y fresco', 'Cuida tu piel con fórmulas simples y conscientes.', 'Aumentar el reconocimiento y las ventas de la línea facial.', 'Rutinas simples, piel naturalmente sana'],
            ['Andes Fit', 'Centro deportivo', 'Diego Salvatierra', 'Entrenamiento funcional con acompañamiento personalizado.', 'Planes mensuales de entrenamiento', 'Adultos de 24 a 44 años', 'Profesionales que necesitan rutinas eficientes y seguimiento cercano.', 'Motivador y directo', 'Tu progreso comienza con una rutina que sí puedes sostener.', 'Generar inscripciones para los planes mensuales.', 'Volver a moverse, volver a sentirse bien'],
            ['Luz de Luna', 'Joyería', 'Camila Rojas', 'Joyería contemporánea elaborada por artesanos locales.', 'Colección de piezas personalizadas', 'Mujeres de 25 a 50 años', 'Compradoras que valoran diseño, significado y producción local.', 'Elegante y emotivo', 'Una pieza especial guarda una historia irrepetible.', 'Impulsar consultas y pedidos personalizados.', 'Historias que brillan contigo'],
            ['Kawsay Verde', 'Alimentos saludables', 'José Quispe', 'Productos saludables listos para integrar a la rutina diaria.', 'Packs semanales saludables', 'Familias y profesionales de 28 a 50 años', 'Personas que desean comer mejor sin complicar su agenda.', 'Claro y optimista', 'Comer bien puede ser práctico, rico y cotidiano.', 'Aumentar pedidos de packs semanales.', 'Bienestar práctico para cada semana'],
            ['Estudio Norte', 'Arquitectura', 'Andrea Molina', 'Diseño arquitectónico y remodelaciones para hogares y comercios.', 'Remodelación integral', 'Propietarios y emprendedores de 30 a 55 años', 'Personas que buscan espacios funcionales, estéticos y bien planificados.', 'Profesional y inspirador', 'Diseñamos espacios que trabajan a favor de tu vida.', 'Conseguir reuniones de diagnóstico con nuevos prospectos.', 'Espacios que transforman la rutina'],
            ['Mundo Pequeño', 'Educación infantil', 'Lucía Pérez', 'Centro de estimulación y aprendizaje temprano.', 'Programa de estimulación temprana', 'Madres, padres y familias de 25 a 42 años', 'Familias que buscan acompañamiento profesional durante la primera infancia.', 'Cálido y educativo', 'Cada descubrimiento merece un entorno seguro para crecer.', 'Incrementar visitas informativas e inscripciones.', 'Aprender jugando, crecer acompañados'],
            ['Altura Travel', 'Turismo', 'Marco Villarroel', 'Experiencias turísticas responsables dentro de Bolivia.', 'Circuitos nacionales de fin de semana', 'Viajeros de 23 a 45 años', 'Personas que prefieren experiencias organizadas, auténticas y responsables.', 'Aventurero y confiable', 'Bolivia se vive mejor cuando cada detalle está resuelto.', 'Generar reservas para circuitos nacionales.', 'Escapadas que se convierten en historias'],
            ['Punto Legal', 'Servicios profesionales', 'Natalia Suárez', 'Asesoría legal accesible para emprendedores y pequeñas empresas.', 'Paquetes de asesoría para negocios', 'Emprendedores de 25 a 50 años', 'Dueños de negocios que necesitan prevenir riesgos y ordenar su documentación.', 'Profesional y sencillo', 'Las decisiones seguras comienzan con información clara.', 'Conseguir solicitudes de diagnóstico legal.', 'Tu negocio crece sobre bases seguras'],
            ['Casa Menta', 'Decoración', 'Sofía Vargas', 'Objetos decorativos y textiles para hogares contemporáneos.', 'Colección de textiles para el hogar', 'Adultos de 25 a 48 años', 'Personas interesadas en renovar sus espacios con piezas funcionales.', 'Inspirador y cercano', 'Pequeños cambios pueden renovar por completo un espacio.', 'Aumentar ventas de la colección de textiles.', 'Renueva tu espacio, renueva tu energía'],
            ['Clínica Armonía', 'Salud dental', 'Fernando López', 'Clínica odontológica enfocada en prevención y estética dental.', 'Evaluación dental preventiva', 'Familias y profesionales de 20 a 55 años', 'Personas que priorizan atención confiable, prevención y seguimiento.', 'Profesional y humano', 'Prevenir hoy es cuidar tu sonrisa para mañana.', 'Incrementar reservas para evaluaciones preventivas.', 'Una sonrisa cuidada cambia tu día'],
            ['Tecno Hogar', 'Comercio', 'Gabriela Condori', 'Soluciones tecnológicas prácticas para el hogar y la oficina.', 'Accesorios para productividad', 'Estudiantes y profesionales de 20 a 45 años', 'Compradores que comparan utilidad, garantía y soporte antes de elegir.', 'Directo y útil', 'Tecnología confiable para resolver mejor cada día.', 'Impulsar consultas y ventas de accesorios seleccionados.', 'Productividad sin complicaciones'],
        ];

        $definitions = [];
        foreach ($businesses as $index => [$company, $type, $client, $companyDescription, $offer, $audienceLabel, $audience, $tone, $message, $objective, $campaign]) {
            if ($index < 2) {
                $start = $activeStart->copy()->addDays($index);
                $end = $activeEnd->copy();
                $status = 'activa';
            } else {
                $position = $index - 1;
                $yearStart = now()->copy()->startOfYear();
                $latestEnd = now()->copy()->subDays(4)->startOfDay();
                $availableDays = max(1, $yearStart->diffInDays($latestEnd));
                $end = $yearStart->copy()->addDays((int) floor($availableDays * $position / 10));
                $start = $end->copy()->subDays(20);
                if ($start->lessThan($yearStart)) {
                    $start = $yearStart->copy();
                }
                $status = 'finalizada';
            }

            $definitions[] = compact(
                'company', 'type', 'client', 'companyDescription', 'offer', 'audienceLabel',
                'audience', 'tone', 'message', 'objective', 'campaign', 'start', 'end', 'status'
            ) + [
                'company_description' => $companyDescription,
                'audience_label' => $audienceLabel,
                'description' => "Campaña integral para posicionar {$offer}, generar conversación con la audiencia y convertir el interés en consultas medibles.",
                'address' => 'La Paz, Bolivia',
            ];
        }

        return $definitions;
    }

    private function executiveSummary(array $definition): string
    {
        return "## 1. Perfil de la empresa\n{$definition['company']} es una empresa del sector {$definition['type']}. {$definition['company_description']}\n\n"
            ."## 2. Oferta prioritaria\nDurante este periodo se impulsará: {$definition['offer']}.\n\n"
            ."## 3. Público principal\n{$definition['audience_label']}: {$definition['audience']}\n\n"
            ."## 4. Objetivo\n{$definition['objective']}\n\n"
            ."## 5. Comunicación\nTono {$definition['tone']}. Mensaje central: {$definition['message']}";
    }

    private function marketingPlanContent(array $definition): string
    {
        return "## 1 Diagnóstico de marca\n{$definition['company_description']} La oportunidad está en comunicar su propuesta con constancia y claridad.\n\n"
            ."## 2 Objetivo comercial\n{$definition['objective']}\n\n"
            ."## 3 Público objetivo\n{$definition['audience_label']}. {$definition['audience']}\n\n"
            ."## 4 Propuesta de valor\n{$definition['message']}\n\n"
            ."## 5 Estrategia de contenidos\nCombinar contenido educativo, demostraciones, testimonios y llamados a la acción orientados a consultas.\n\n"
            ."## 6 Canales y medición\nPlanificar contenido para Facebook, Instagram y WhatsApp sin vincular cuentas externas. Medir alcance, interacción, consultas y conversiones.\n\n"
            ."## 7 Calendario operativo mensual\nSemana 1: definición visual. Semana 2: carrusel educativo. Semana 3: contenido comercial. Semana 4: revisión de resultados.\n\n"
            ."## 8 Optimización\nRevisar aprendizajes con el cliente y ajustar mensajes, formatos y llamados a la acción para el siguiente periodo.";
    }

    private function paymentCode(int $number): string
    {
        return sprintf('DEMO-CAMP-%02d', $number);
    }

    private function stamp(Model $model, Carbon $date): void
    {
        DB::table($model->getTable())->where('id', $model->getKey())->update([
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        $model->setRawAttributes(array_merge($model->getAttributes(), [
            'created_at' => $date,
            'updated_at' => $date,
        ]), true);
    }
}
