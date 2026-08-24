<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\PlanMarketing;
use App\Models\Suscripcion;
use App\Services\MarketingPlanService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class PlanMarketingController extends Controller
{
    protected $marketingPlanService;

    /**
     * Inyecta el servicio de planes de marketing.
     *
     * @param MarketingPlanService $marketingPlanService
     */
    public function __construct(MarketingPlanService $marketingPlanService)
    {
        $this->marketingPlanService = $marketingPlanService;
    }

    /**
     * Muestra el formulario para crear un nuevo plan de marketing para una empresa.
     *
     * @param Empresa $empresa
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function create(Empresa $empresa)
    {
        if (!$empresa->resumen_ejecutivo) {
            return back()->with('error', 'No se puede crear un plan de marketing sin un resumen ejecutivo generado primero.');
        }

        $suscripcionActiva = $empresa->suscripcion_id
            ? $empresa->suscripcion()->where('estado', 'activa')->first()
            : $empresa->usuario->suscripciones()
                ->where('estado', 'activa')
                ->latest()
                ->first();

        if (!$suscripcionActiva) {
            return back()->with('error', 'El usuario no tiene una suscripción activa para generar un plan de marketing.');
        }

        if ($suscripcionActiva->planMarketing()->exists()) {
            return back()->with('error', 'Ya existe un plan de marketing para la suscripción actual.');
        }

        $caracteristicasPlan = $suscripcionActiva->plan->caracteristicas;

        return view('administrador.empresas.crear-plan', compact('empresa', 'suscripcionActiva', 'caracteristicasPlan'));
    }

    /**
     * Almacena un nuevo plan de marketing en la base de datos.
     *
     * @param Request $request
     * @param Empresa $empresa
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Empresa $empresa)
    {
        $request->validate([
            'suscripcion_id' => 'required|exists:suscripciones,id',
        ]);

        $suscripcion = Suscripcion::with([
            'plan.caracteristicas',
            'plan.planCaracteristicas.caracteristica',
        ])->findOrFail($request->suscripcion_id);

        if (
            $suscripcion->usuario_id !== $empresa->usuario_id
            || $suscripcion->estado !== 'activa'
            || ($empresa->suscripcion_id && $suscripcion->id !== $empresa->suscripcion_id)
        ) {
            return back()->with('error', 'Suscripción no válida para esta empresa.');
        }

        if ($suscripcion->planMarketing()->exists()) {
            return back()->with('error', 'Ya existe un plan para esta suscripción.');
        }

        $caracteristicas = $suscripcion->plan->planCaracteristicas
            ->sortBy('orden')
            ->values()
            ->map(function ($planCaracteristica) {
                $nombre = $planCaracteristica->caracteristica->nombre ?? '';

                return [
                    'nombre' => $nombre,
                    'cantidad' => $planCaracteristica->cantidad,
                    'unidad' => $this->inferirUnidadDesdeNombre($nombre),
                    'frecuencia' => $planCaracteristica->frecuencia,
                    'descripcion' => $planCaracteristica->caracteristica->descripcion,
                    'orden' => $planCaracteristica->orden,
                    'es_destacado' => (bool) $planCaracteristica->es_destacado,
                ];
            })->toArray();

        $planContexto = [
            'nombre' => $suscripcion->plan->nombre,
            'descripcion' => $suscripcion->plan->descripcion,
            'periodo_facturacion' => $suscripcion->plan->periodo_facturacion,
            'precio' => $suscripcion->plan->precio,
            'moneda' => $suscripcion->plan->moneda,
        ];

        $contenidoPlan = $this->marketingPlanService->generateMarketingPlan(
            $empresa->nombre_empresa,
            $empresa->resumen_ejecutivo,
            $caracteristicas,
            $planContexto
        );

        if (!$contenidoPlan || str_contains($contenidoPlan, 'Hubo un error')) {
            return back()->with('error', 'No se pudo generar el contenido del plan de marketing. Inténtelo de nuevo.');
        }

        PlanMarketing::create([
            'empresa_id' => $empresa->id,
            'suscripcion_id' => $suscripcion->id,
            'contenido' => $contenidoPlan,
            'estado' => 'activo',
        ]);

        return redirect()->route('administrador.empresas.show', $empresa->id)
            ->with('success', '¡Plan de marketing creado y guardado con éxito!');
    }

    /**
     * Muestra un plan de marketing específico.
     *
     * @param PlanMarketing $planMarketing
     * @return \Illuminate\View\View
     */
    public function show(PlanMarketing $planMarketing)
    {
        $planMarketing->load(['empresa', 'suscripcion.plan']);

        return view('administrador.planes-marketing.show', compact('planMarketing'));
    }

    public function edit(PlanMarketing $planMarketing)
    {
        $planMarketing->load(['empresa', 'suscripcion.plan']);

        return view('administrador.planes-marketing.edit', compact('planMarketing'));
    }

    public function update(Request $request, PlanMarketing $planMarketing)
    {
        $request->validate([
            'contenido' => 'required|string',
        ]);

        $planMarketing->contenido = $request->input('contenido');
        $planMarketing->save();

        return redirect()->route('administrador.empresas.planes-marketing.show', $planMarketing->id)
            ->with('success', '¡Plan de marketing actualizado con éxito!');
    }

    public function downloadPDF(PlanMarketing $planMarketing)
    {
        $planMarketing->load(['empresa', 'suscripcion.plan']);

        $contenidoCompleto = $planMarketing->contenido;
        $partes = preg_split('/^##\s+(.+)$/m', $contenidoCompleto, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $seccionesParseadas = [];
        for ($i = 0; $i < count($partes); $i += 2) {
            if (isset($partes[$i + 1])) {
                $titulo = trim($partes[$i]);
                $contenido = trim($partes[$i + 1]);
                $seccionesParseadas[] = ['titulo' => $titulo, 'contenido' => $contenido];
            }
        }

        $pdf = Pdf::loadView('administrador.planes-marketing.pdf', compact('planMarketing', 'seccionesParseadas'))
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'sans-serif');

        return $pdf->download('plan-marketing-' . $planMarketing->empresa->nombre_empresa . '.pdf');
    }

    public function downloadWord(PlanMarketing $planMarketing)
    {
        $planMarketing->load(['empresa', 'suscripcion.plan']);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addTitle('Plan de Marketing', 1);
        $section->addTextBreak(2);
        $section->addText('Empresa: ' . $planMarketing->empresa->nombre_empresa);
        $section->addText('Plan de Suscripción: ' . $planMarketing->suscripcion->plan->nombre);
        $section->addText('Fecha de generación: ' . $planMarketing->created_at->format('d/m/Y H:i'));
        $section->addTextBreak(2);

        $contenidoLimpio = strip_tags($planMarketing->contenido);
        $section->addText($contenidoLimpio);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        return response()->stream(
            function () use ($objWriter) {
                echo $objWriter->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="plan-marketing-' . $planMarketing->empresa->nombre_empresa . '.docx"',
            ]
        );
    }

    /**
     * Elimina el plan de marketing de la empresa.
     */
    public function destroy(PlanMarketing $planMarketing)
    {
        $planMarketing->delete();

        return redirect()->route('administrador.empresas.show', $planMarketing->empresa_id)
            ->with('success', '¡Plan de marketing eliminado con éxito!');
    }

    private function inferirUnidadDesdeNombre(string $nombreCaracteristica): ?string
    {
        $nombre = mb_strtolower(trim($nombreCaracteristica));

        return match (true) {
            str_contains($nombre, 'post') => 'posts',
            str_contains($nombre, 'diseño'), str_contains($nombre, 'diseno') => 'disenos',
            str_contains($nombre, 'video') => 'videos',
            str_contains($nombre, 'gif') => 'gifs',
            str_contains($nombre, 'foto'), str_contains($nombre, 'fotografía'), str_contains($nombre, 'fotografia') => 'sesiones',
            str_contains($nombre, 'tiktok'), str_contains($nombre, 'tik tok') => 'piezas',
            str_contains($nombre, 'catálogo'), str_contains($nombre, 'catalogo') => 'catalogos',
            default => null,
        };
    }
}
