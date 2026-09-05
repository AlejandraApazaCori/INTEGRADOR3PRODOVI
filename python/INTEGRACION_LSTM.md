# LSTM integrada con publicaciones y Meta

## Estado del modelo instalado

Se instaló el ZIP `lstm_horarios_meta_v4_20260905T155010882979Z.zip` en
`python/modelos/meta_v4_20260905`. Ambos modelos usan ventanas de 3 publicaciones.
El archivo original y sus metadatos permanecen intactos. La inferencia utiliza los modelos `.keras`
exportados, no una tabla de horarios prefijada. `php artisan lstm:check` verifica que las salidas
coincidan con los fixtures del entrenamiento.

Los modelos fueron entrenados con datos de simulación. El formulario lo indica como experimento;
aplicarlos al histórico real de una cuenta no convierte el entrenamiento en una validación real.
La medida mostrada es un puntaje ponderado de interacciones, no un porcentaje.

## Uso en el formulario

1. Vincula las cuentas Facebook e Instagram a la empresa/cliente correspondiente a la tarea.
2. Abre `/administrador/publicaciones/publicar?tarea_id=55` (o la tarea correspondiente).
3. Selecciona las redes y activa «Optimizar tiempo de publicación».
4. Cada red muestra su cuenta, cantidad de publicaciones utilizables, fecha de medición, cinco
   fechas/horas estimadas y la comparación LSTM/referencia histórica para los próximos siete días.
5. Elige un horario y guarda la programación. **La fecha elegida se aplica a todas las redes
   seleccionadas en esa tarea**: el formulario conserva una fecha de programación compartida.

Se usa `America/La_Paz` tanto para características del modelo como para guardar la fecha seleccionada.
Las franjas sin publicaciones previas se identifican como extrapolaciones. «Mejor hora histórica»
muestra el cálculo estadístico del mismo servicio que alimenta las analíticas Meta, separado de la LSTM.
No se mezclan sus diferentes fórmulas de puntaje.

La ruta `/administrador/publicaciones/horarios?tarea_id=...` resuelve las cuentas desde la tarea;
no acepta IDs arbitrarios de cuentas del navegador. Exige autenticación y acceso administrativo o
participación en la gestión de la tarea/campaña. La respuesta no incluye tokens ni el histórico completo.

## Instalación en otro equipo o servidor

En este equipo ya se creó el entorno aislado Python 3.13 y se aplicó la migración del histórico.
Para reproducirlo en Windows, desde la raíz del proyecto:

```powershell
py -3.13 -m venv python/.venv-lstm
python/.venv-lstm/Scripts/python.exe -m pip install -r python/lstm_v4/requirements-runtime.txt
python python/lstm_v4/install_model.py python/modelos/lstm_horarios_meta_v4_20260905T155010882979Z.zip python/modelos/meta_v4_20260905
php artisan migrate --path=database/migrations/2026_09_05_160000_create_meta_post_snapshots_table.php --force
php artisan lstm:check
```

Si el directorio del modelo ya existe, omite `install_model.py`. El instalador rechaza sobrescribirlo;
para otra versión usa otro directorio y configura `LSTM_MODELS_PATH`.
En Linux crea el venv con Python 3.13 y usa `python/.venv-lstm/bin/python`.
La configuración elige la ruta del intérprete según el sistema operativo. Permite sobrescribirla
con `LSTM_PYTHON` y `LSTM_MODELS_PATH` en `.env`. Si hay configuración en caché, regénérala después.

PHP necesita poder ejecutar procesos locales; un hosting que deshabilite `proc_open` requerirá
otro despliegue para inferencia. No se inicia un servidor Python ni se expone un puerto adicional.
La primera consulta puede tardar por la carga de TensorFlow y la consulta a Meta. Los resultados se
guardan en caché durante 15 minutos por contenido del histórico, candidatos y versión del ZIP.
Laravel aplica un timeout configurable mediante `LSTM_TIMEOUT`.

## Histórico real y reentrenamiento

La tabla `meta_post_snapshots` conserva métricas por cuenta, publicación y fecha real de observación
(fechas almacenadas en UTC). Las consultas de analíticas y predicción guardan snapshots. Los registros
sin reacciones/me gusta o comentarios disponibles se excluyen de la entrada del modelo.

```powershell
php artisan meta:sync-training-history
php artisan meta:export-training-history
```

La sincronización consulta las publicaciones recientes de las cuentas vinculadas. El exportador elige
la primera medición de cada publicación tomada entre 48 y 54 horas de edad; no inventa fechas de medición.
Escribe `storage/app/private/dataset_meta_real_<fecha>.csv`, compatible con el notebook V4, sin tokens
ni textos ni imágenes. Si todavía no existen mediciones dentro de esa ventana, lo indica y no genera
un dataset engañoso. Opcionalmente filtra por ID interno: `--account=123`.

Para seguir acumulando métricas, el servidor debe ejecutar el scheduler de Laravel cada minuto.
En desarrollo puede usarse `php artisan schedule:work`. **Ese scheduler también ejecuta las
publicaciones ya programadas** y los otros trabajos configurados: inícialo cuando corresponda operar
esas tareas. No se inició automáticamente durante esta implementación.

`php artisan schedule:list` permite verificar los trabajos sin ejecutarlos. La sincronización Meta
se programa cada hora y las publicaciones vencidas cada minuto. El publicador comparte un bloqueo
entre la ejecución web existente y el scheduler para reducir ejecuciones simultáneas.

Cuando exista suficiente histórico real, entrena el notebook con ese nuevo CSV y evalúa el ZIP nuevo
antes de sustituir los modelos. La evaluación prospectiva en cada cuenta sigue pendiente.

## Estados y diagnóstico

- `not_connected`: no hay cuenta vinculada para esa red y tarea.
- `insufficient_data`: menos publicaciones utilizables que la ventana requerida.
- `unavailable`: fallo de ejecución/modelo; no se ofrecen horarios inventados.
- `ok` con `experimental=true`: se ejecutó la LSTM sobre el histórico de esa cuenta, con modelo experimental.

Si Meta falla temporalmente, se puede usar el histórico ya almacenado indicando su fecha de medición.
Si no hay histórico, se conserva la programación manual. No hay respaldo con el antiguo JSON estático
en esta nueva ruta. Los paneles globales anteriores ajenos al formulario no se migraron a esta ruta.

En la comprobación de esta instalación local, la tarea 55 pertenece a la campaña 20 y no había registros
en `social_accounts`. Por ello devolvió correctamente `not_connected` para ambas redes. Es necesario
vincular las cuentas en esta base de datos para verificar el flujo con Meta real.
