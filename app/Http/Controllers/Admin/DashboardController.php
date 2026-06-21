<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Models\Pago;
use App\Models\Campania;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Obtener el número de campañas activas (Sincronizado con CampañasController)
        $activeCampaigns = Campania::whereIn('estado', ['activa', 'pausada'])
                                   ->where('fecha_fin', '>', now())
                                   ->count();
        
        // Obtener el total de usuarios registrados
        $totalUsers = User::count();
        
        // Obtener el total de empresas
        $totalCompanies = Empresa::count();
        
        // Obtener ingresos mensuales
        $currentMonthIncome = Pago::whereMonth('fecha_pago', now()->month)
                                  ->whereYear('fecha_pago', now()->year)
                                  ->where('estado', 'completado')
                                  ->sum('monto');
        
        $previousMonthIncome = Pago::whereMonth('fecha_pago', now()->subMonth()->month)
                                   ->whereYear('fecha_pago', now()->subMonth()->year)
                                   ->where('estado', 'completado')
                                   ->sum('monto');

        // Calcular el porcentaje de cambio de forma segura
        $monthlyIncomeChangePercentage = null;
        if ($previousMonthIncome > 0) {
            $monthlyIncomeChangePercentage = (($currentMonthIncome - $previousMonthIncome) / $previousMonthIncome) * 100;
        }                        
        
        // Obtener el plan más contratado (Basado solo en suscripciones ACTIVAS)
        $mostContractedPlan = Plan::withCount(['suscripciones as activas_count' => function($query) {
                                    $query->where('estado', 'activa')
                                          ->where('fecha_fin', '>', now());
                                  }])
                                  ->orderBy('activas_count', 'desc')
                                  ->first();
        
        // Contar suscripciones por estado (Garantizando consistencia con PagoAdminController)
        $countActivos = Suscripcion::where('estado', 'activa')
                                   ->where('fecha_fin', '>', now())
                                   ->count();
                                   
        $countPendientes = Pago::where('estado', 'pendiente')
                                ->where('metodo', 'fisico')
                                ->count();
                                
        $countFinalizados = Suscripcion::whereIn('estado', ['finalizada', 'cancelada'])
                                     ->count();
        
        // Obtener datos para el gráfico mensual (últimos 6 meses)
        $monthlyIncome = Pago::select(
                                DB::raw('MONTH(fecha_pago) as month'),
                                DB::raw('YEAR(fecha_pago) as year'),
                                DB::raw('SUM(monto) as total')
                            )
                            ->where('estado', 'completado')
                            ->whereNotNull('fecha_pago')
                            ->where('fecha_pago', '>=', now()->subMonths(6))
                            ->groupBy('year', 'month')
                            ->orderBy('year', 'asc')
                            ->orderBy('month', 'asc')
                            ->get();
        
        // Obtener datos para el gráfico anual (últimos 5 años)
        $yearlyIncome = Pago::select(
                               DB::raw('YEAR(fecha_pago) as year'),
                               DB::raw('SUM(monto) as total')
                           )
                           ->where('estado', 'completado')
                           ->whereNotNull('fecha_pago')
                           ->where('fecha_pago', '>=', now()->subYears(5))
                           ->groupBy('year')
                           ->orderBy('year', 'asc')
                           ->get();
        
        return view('administrador.dashboard', compact(
            'activeCampaigns',
            'totalUsers',
            'totalCompanies',
            'currentMonthIncome',
            'monthlyIncomeChangePercentage',
            'mostContractedPlan',
            'countActivos',
            'countPendientes',
            'countFinalizados',
            'monthlyIncome',
            'yearlyIncome'
        ));
    }
}