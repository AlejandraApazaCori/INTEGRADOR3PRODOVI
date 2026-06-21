<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use App\Services\GroqImageService; // <-- IMPORTAR EL NUEVO SERVICIO
use Illuminate\Http\Request;

class PublicacionController extends Controller
{
    // Inyectamos el servicio para usarlo
    protected $groqImageService;

    public function __construct(GroqImageService $groqImageService)
    {
        $this->groqImageService = $groqImageService;
    }

    public function index(Request $request)
    {
        // Obtener el ID de la tarea desde la URL (si viene de vertareassubidas.blade.php)
        $tareaId = $request->input('tarea_id');
        
        // Si no hay tarea_id en la solicitud, redirigir o manejar el caso
        if (!$tareaId) {
            return redirect()->back()->with('error', 'No se especificó una tarea para publicar');
        }
        
        // Obtener la tarea con sus archivos aprobados
        $tarea = Tarea::with(['archivos' => function($query) {
            $query->where('estado', 'aprobado');
        }])->findOrFail($tareaId);
        
        return view('administrador.publicacion.publicar', compact('tarea'));
    }

    /**
     * Genera un copy publicitario usando IA basado en la imagen de la tarea.
     */
    public function generateCopy(Request $request)
    {
        $request->validate([
            'tarea_id' => 'required|integer|exists:tareas,id',
        ]);

        $copy = $this->groqImageService->generateCopyFromImage($request->tarea_id);

        return response()->json([
            'success' => true,
            'copy' => $copy
        ]);
    }
}