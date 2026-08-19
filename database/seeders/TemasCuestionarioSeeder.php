<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemasCuestionarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();
            DB::table('preguntas_cuestionario')->whereNull('deleted_at')->update(['deleted_at' => $now]);
            DB::table('temas_cuestionario')->whereNull('deleted_at')->update(['deleted_at' => $now]);

            $temaNombre = 'Brief inicial de la marca';
            $temaId = DB::table('temas_cuestionario')->where('nombre_tema', $temaNombre)->value('id');
            $temaData = [
                'nombre_tema' => $temaNombre,
                'descripcion_tema' => 'Cuéntanos lo esencial para preparar el contenido de tu marca. Te tomará aproximadamente 2 minutos.',
                'orden' => 1,
                'deleted_at' => null,
                'updated_at' => $now,
            ];

            if ($temaId) {
                DB::table('temas_cuestionario')->where('id', $temaId)->update($temaData);
            } else {
                $temaId = DB::table('temas_cuestionario')->insertGetId($temaData + ['created_at' => $now]);
            }

            $preguntas = [
                ['¿A qué se dedica tu empresa y qué vendes?', 'texto', 'Ej.: Somos una clínica dental especializada en ortodoncia y estética dental.', null, true],
                ['¿Qué producto o servicio quieres que impulsemos más?', 'texto', 'Ej.: Ortodoncia invisible, limpieza dental y blanqueamiento.', null, true],
                ['¿Quién es tu cliente ideal?', 'checkbox', 'Puedes elegir más de una opción.', ['Jóvenes', 'Adultos', 'Padres / familias', 'Profesionales', 'Emprendedores', 'Empresas', 'Negocios locales', 'Público general', 'Otro'], true],
                ['¿Cuál es tu objetivo principal?', 'checkbox', 'Elige la prioridad más importante para tu marca.', ['Conseguir más clientes', 'Vender más', 'Recibir más mensajes o consultas', 'Dar a conocer mi marca', 'Crecer en redes sociales', 'Promocionar un producto o servicio', 'Llevar personas a mi negocio/local', 'Otro'], true],
                ['¿Qué hace diferente a tu empresa?', 'checkbox', 'Selecciona todas las características que representen a tu negocio.', ['Buena calidad', 'Buenos precios', 'Atención personalizada', 'Rapidez', 'Experiencia', 'Especialización', 'Innovación', 'Confianza', 'Resultados', 'Otro'], true],
                ['¿Cómo quieres que se comunique tu marca?', 'checkbox', 'Elige el tono que mejor represente a tu marca.', ['Profesional', 'Cercana y amigable', 'Elegante / premium', 'Divertida', 'Juvenil', 'Educativa', 'Directa y comercial', 'Déjenlo en manos de ustedes'], true],
                ['¿Hay algo que debamos saber antes de publicar?', 'texto_largo', 'Puedes mencionar promociones, restricciones, palabras que debemos evitar, fechas importantes o cualquier indicación especial.', null, false],
            ];

            foreach ($preguntas as $index => [$texto, $tipo, $ayuda, $opciones, $requerido]) {
                $preguntaData = [
                    'tema_id' => $temaId,
                    'pregunta' => $texto,
                    'orden' => $index + 1,
                    'tipo_respuesta' => $tipo,
                    'opciones' => $opciones ? json_encode($opciones, JSON_UNESCAPED_UNICODE) : null,
                    'ayuda' => $ayuda,
                    'requerido' => $requerido,
                    'deleted_at' => null,
                    'updated_at' => $now,
                ];
                $preguntaId = DB::table('preguntas_cuestionario')->where('pregunta', $texto)->value('id');

                if ($preguntaId) {
                    DB::table('preguntas_cuestionario')->where('id', $preguntaId)->update($preguntaData);
                } else {
                    DB::table('preguntas_cuestionario')->insert($preguntaData + ['created_at' => $now]);
                }
            }
        });
    }
}
