<?php

namespace Database\Seeders;

use App\Models\SolicitudContacto;
use Illuminate\Database\Seeder;

class SolicitudesContactoSeeder extends Seeder
{
    public function run(): void
    {
        $solicitudes = [
            ['Valeria Mendoza', 'valeria.mendoza@demo.prodovi.test', '76542101', 'social', 'Busco una estrategia mensual para Instagram y Facebook que nos ayude a aumentar consultas y posicionar nuestra marca de cosmética natural.'],
            ['Carlos Andrade', 'carlos.andrade@demo.prodovi.test', '72018342', 'publicidad', 'Necesitamos lanzar una campaña pagada para promocionar una nueva línea de productos durante las próximas seis semanas.'],
            ['Mariana Quiroga', 'mariana.quiroga@demo.prodovi.test', '77730415', 'audiovisual', 'Quisiera producir un video institucional y varias piezas cortas para mostrar nuestros servicios en redes sociales.'],
            ['Diego Salvatierra', 'diego.salvatierra@demo.prodovi.test', '68192530', 'eventos', 'Estamos organizando el aniversario de nuestra empresa y necesitamos apoyo con concepto, producción y cobertura del evento.'],
            ['Fernanda Rojas', 'fernanda.rojas@demo.prodovi.test', '75211648', 'bodas', 'Deseo conocer sus paquetes de planificación integral para una boda prevista para el segundo semestre del año.'],
            ['Mauricio Vargas', 'mauricio.vargas@demo.prodovi.test', '70143862', 'influencers', 'Queremos trabajar con creadores de contenido locales para presentar nuestra nueva aplicación y medir los resultados de la campaña.'],
            ['Alejandra Flores', 'alejandra.flores@demo.prodovi.test', '78923451', 'publicidad', 'Necesitamos una propuesta de marketing para incrementar las ventas de nuestra tienda en línea y recuperar clientes antiguos.'],
            ['Rodrigo Paredes', 'rodrigo.paredes@demo.prodovi.test', '67081529', 'social', 'Busco delegar la administración completa de redes sociales, incluyendo calendario, diseño, respuestas y reportes mensuales.'],
            ['Natalia Céspedes', 'natalia.cespedes@demo.prodovi.test', null, 'audiovisual', 'Necesito fotografías de producto y reels para el lanzamiento de una colección de accesorios hechos en Bolivia.'],
            ['Javier Miranda', 'javier.miranda@demo.prodovi.test', '73049618', 'eventos', 'Solicito una cotización para organizar una feria empresarial con aproximadamente doscientos asistentes y varios expositores.'],
            ['Camila Torrico', 'camila.torrico@demo.prodovi.test', '69851743', 'other', 'Quisiera una asesoría para definir la identidad y la estrategia digital más conveniente para un emprendimiento que recién comienza.'],
            ['Sebastián Arce', 'sebastian.arce@demo.prodovi.test', '77412036', 'influencers', 'Buscamos microinfluencers relacionados con gastronomía para generar contenido y atraer público a nuestras nuevas sucursales.'],
            ['Lucía Villarroel', 'lucia.villarroel@demo.prodovi.test', null, 'bodas', 'Necesito acompañamiento para coordinar proveedores, decoración, cronograma y comunicación de una boda pequeña en La Paz.'],
            ['Andrés Molina', 'andres.molina@demo.prodovi.test', '71290354', 'publicidad', 'Deseamos relanzar nuestra marca con una campaña que combine anuncios digitales, contenido orgánico y una landing page.'],
            ['Paola Gutiérrez', 'paola.gutierrez@demo.prodovi.test', '78654120', 'social', 'Requiero una auditoría de nuestras cuentas y una propuesta de contenido orientada a captar clientes corporativos.'],
        ];

        foreach ($solicitudes as $index => [$nombre, $correo, $telefono, $servicio, $mensaje]) {
            $fecha = now()->subDays($index * 2)->setTime(9 + ($index % 8), ($index * 7) % 60);
            $solicitud = SolicitudContacto::updateOrCreate(
                ['correo' => $correo],
                [
                    'nombre' => $nombre,
                    'telefono' => $telefono,
                    'servicio' => $servicio,
                    'mensaje' => $mensaje,
                    'correo_enviado_at' => $index % 4 === 0 ? null : $fecha->copy()->addMinutes(2),
                ]
            );

            $solicitud->forceFill([
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ])->saveQuietly();
        }

        $this->command?->info('15 solicitudes de contacto demo creadas o actualizadas correctamente.');
    }
}
