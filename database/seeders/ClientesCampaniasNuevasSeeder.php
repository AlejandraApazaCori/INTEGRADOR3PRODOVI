<?php

namespace Database\Seeders;

use App\Models\ComprobantePago;
use App\Models\Plan;
use App\Models\PreguntaCuestionario;
use Illuminate\Support\Facades\DB;

/**
 * Lote independiente: no invoca CampaniasDemoSeeder::run() ni modifica sus 12 campañas.
 */
class ClientesCampaniasNuevasSeeder extends CampaniasDemoSeeder
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
                throw new \RuntimeException('Se necesita al menos un plan activo para cargar los clientes nuevos.');
            }

            foreach ($this->definitions() as $index => $definition) {
                // Los identificadores 101-111 separan este lote de las campañas antiguas 01-12.
                $number = 101 + $index;
                $plan = $plans[$index % $plans->count()];

                if ($index < 6) {
                    $this->seedCampaign($definition, $number - 1, $plan, $team);
                    continue;
                }

                $client = $this->client($definition, $number);
                $subscription = $this->subscription($client, $plan, $definition, $number);
                $company = $this->company($client, $subscription, $definition);
                $payment = $this->payment($client, $subscription, $plan, $definition, $number, $team['admin']);
                $this->questionnaire($company, $definition);
                $this->marketingPlan($company, $subscription, $definition);
                $this->resources($company, $definition, $team['admin']);

                $receipt = ComprobantePago::withTrashed()->firstOrNew(['pago_id' => $payment->id]);
                $receipt->ruta_pdf = null;
                $receipt->deleted_at = null;
                $receipt->save();
            }
        });

        $this->command?->info('Lote nuevo cargado: 6 clientes con campañas activas y 5 clientes con empresa, plan y pago, sin campaña activa.');
    }

    protected function campaignName(int $number, array $definition): string
    {
        return $definition['campaign'];
    }

    private function definitions(): array
    {
        $profiles = [
            ['Senda Café', 'Cafetería', 'Elena Paredes', 'Café de origen boliviano, desayunos y repostería artesanal.', 'Menú de desayunos de temporada', 'Profesionales de 22 a 42 años', 'Personas que buscan una pausa de calidad cerca del trabajo.', 'Cálido y directo', 'Un buen día empieza con una pausa hecha para ti.', 'Aumentar reservas y visitas al local.', 'Desayunos que inspiran tu día', 'Sopocachi, La Paz'],
            ['Raíz Urbana', 'Moda sostenible', 'Paola Ibáñez', 'Prendas versátiles producidas en talleres locales.', 'Nueva colección de prendas esenciales', 'Mujeres de 24 a 40 años', 'Compradoras que priorizan comodidad, diseño y producción responsable.', 'Cercano y consciente', 'Viste piezas que acompañan tu ritmo y tus valores.', 'Generar consultas y pedidos de la nueva colección.', 'Esenciales para moverte con libertad', 'Calacoto, La Paz'],
            ['Vértice Salud', 'Fisioterapia', 'Javier Ríos', 'Centro de fisioterapia y rehabilitación con atención personalizada.', 'Evaluación inicial de movilidad', 'Adultos de 25 a 60 años', 'Personas activas que necesitan recuperar movilidad y prevenir lesiones.', 'Profesional y humano', 'Recupera movimiento con un plan pensado para ti.', 'Conseguir reservas de evaluaciones iniciales.', 'Muévete mejor cada día', 'Miraflores, La Paz'],
            ['Nube Creativa', 'Papelería', 'Rocío Beltrán', 'Papelería personalizada para negocios y celebraciones.', 'Kits de identidad impresa para emprendedores', 'Emprendedores de 23 a 45 años', 'Pequeños negocios que quieren presentar su marca con coherencia.', 'Creativo y práctico', 'Cada detalle impreso cuenta la historia de tu marca.', 'Aumentar solicitudes de cotización de kits.', 'Tu marca en cada detalle', 'Centro, La Paz'],
            ['Tierra Clara', 'Alimentos orgánicos', 'Mauricio Céspedes', 'Canastas de productos frescos de productores locales.', 'Canastas semanales a domicilio', 'Familias de 28 a 55 años', 'Hogares que buscan alimentos frescos y entregas confiables.', 'Fresco y confiable', 'Del productor a tu mesa, sin complicaciones.', 'Impulsar suscripciones a canastas semanales.', 'Frescura que llega a casa', 'Achumani, La Paz'],
            ['Impulso Studio', 'Entrenamiento', 'Daniela Aguirre', 'Estudio de entrenamiento funcional para grupos pequeños.', 'Programa de iniciación de cuatro semanas', 'Adultos de 25 a 45 años', 'Personas que quieren comenzar a entrenar con acompañamiento.', 'Motivador y claro', 'Empieza con apoyo y avanza a tu ritmo.', 'Generar inscripciones al programa de iniciación.', 'Cuatro semanas para comenzar', 'Obrajes, La Paz'],
            ['Casa Lirio', 'Decoración', 'Mónica Valdez', 'Objetos textiles y decoración para espacios cotidianos.', 'Asesoría de decoración para ambientes pequeños', 'Propietarios de 28 a 50 años', 'Personas que desean renovar un espacio sin grandes obras.', 'Inspirador y sencillo', 'Tu espacio puede sentirse nuevo con decisiones simples.', 'Preparar el lanzamiento de la asesoría.', 'Espacios que se sienten tuyos', 'San Miguel, La Paz'],
            ['Altiplano Tours', 'Turismo', 'Óscar Mamani', 'Excursiones culturales con guías locales.', 'Rutas de fin de semana por el altiplano', 'Viajeros de 24 a 48 años', 'Personas que buscan experiencias organizadas y auténticas.', 'Aventurero y seguro', 'Descubre historias cercanas que vale la pena vivir.', 'Definir audiencia y mensajes antes de lanzar.', 'Rutas con historia', 'Centro, La Paz'],
            ['Brisa Natural', 'Cuidado personal', 'Carolina Vera', 'Jabones y productos de cuidado personal hechos en Bolivia.', 'Línea de cuidado corporal', 'Adultos de 22 a 45 años', 'Consumidores interesados en ingredientes y procesos claros.', 'Natural y educativo', 'Cuida tu rutina con ingredientes que conoces.', 'Organizar el catálogo y el plan de contenidos.', 'Cuidado cotidiano consciente', 'Sopocachi, La Paz'],
            ['Punto Norte', 'Consultoría', 'Rodrigo Soria', 'Asesoría administrativa para pequeñas empresas.', 'Diagnóstico de procesos comerciales', 'Dueños de negocios de 30 a 55 años', 'Empresas que necesitan ordenar sus ventas y seguimiento.', 'Profesional y cercano', 'Organiza tus procesos para tomar mejores decisiones.', 'Preparar materiales para captar solicitudes de diagnóstico.', 'Orden para crecer', 'Calacoto, La Paz'],
            ['Semilla Viva', 'Educación infantil', 'Verónica Choque', 'Talleres creativos y de aprendizaje para niños.', 'Talleres de vacaciones', 'Familias de 27 a 45 años', 'Padres que buscan actividades guiadas y seguras.', 'Cálido y educativo', 'Aprender también puede ser una aventura compartida.', 'Planificar la campaña de inscripciones.', 'Vacaciones para descubrir', 'Miraflores, La Paz'],
        ];

        $start = now()->copy()->startOfMonth();
        $end = now()->copy()->endOfMonth();
        $secondaryAudiences = [
            'Senda Café' => ['Equipos de oficina de 25 a 50 años', 'Buscan un lugar cercano para reuniones breves y desayunos de trabajo.'],
            'Raíz Urbana' => ['Profesionales independientes de 28 a 45 años', 'Necesitan prendas combinables y duraderas para distintos momentos del día.'],
            'Vértice Salud' => ['Deportistas aficionados de 20 a 45 años', 'Quieren recuperarse de molestias físicas y volver a entrenar con seguridad.'],
            'Nube Creativa' => ['Organizadores de eventos de 25 a 50 años', 'Necesitan piezas impresas personalizadas con tiempos de entrega claros.'],
            'Tierra Clara' => ['Profesionales que viven solos de 25 a 40 años', 'Valoran recibir alimentos frescos en cantidades prácticas para la semana.'],
            'Impulso Studio' => ['Personas que retoman el ejercicio de 30 a 55 años', 'Buscan grupos pequeños, orientación inicial y horarios compatibles con su trabajo.'],
        ];

        return array_map(function (array $profile) use ($start, $end, $secondaryAudiences): array {
            [$company, $type, $client, $description, $offer, $audienceLabel, $audience, $tone, $message, $objective, $campaign, $address] = $profile;

            return compact('company', 'type', 'client', 'offer', 'audience', 'tone', 'message', 'objective', 'campaign', 'address') + [
                'company_description' => $description,
                'audience_label' => $audienceLabel,
                'secondary_audience_label' => $secondaryAudiences[$company][0] ?? null,
                'secondary_audience' => $secondaryAudiences[$company][1] ?? null,
                'description' => "Campaña para comunicar {$offer}, responder consultas y medir oportunidades comerciales de {$company}.",
                'start' => $start->copy(),
                'end' => $end->copy(),
                'status' => 'activa',
            ];
        }, $profiles);
    }
}
