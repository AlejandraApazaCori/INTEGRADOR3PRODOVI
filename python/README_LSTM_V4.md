# Entrenamiento de horarios V4 para PRODOVI

La integración con Laravel y el ZIP del 5 de septiembre ya está implementada. Consulta
[INTEGRACION_LSTM.md](INTEGRACION_LSTM.md) para usar la predicción, ejecutar el diagnóstico y recopilar
el histórico real. Este documento describe la preparación y el entrenamiento en Colab.

## Qué abrir en Colab

1. Sube `ENTRENAMIENTO_LSTM_HORARIOS_V4_META_COLAB.ipynb` a Google Colab.
2. Selecciona un entorno Python 3; puedes habilitar GPU T4.
3. Ejecuta todas las celdas. Cuando aparezca la carga de archivos, sube **`datasets/dataset_meta_v4.csv`**.
4. Conserva `EPOCHS = 100`. El entrenamiento tiene parada temprana y prueba ventanas de 3, 7 y 14 publicaciones.
5. Lee `training_report.json` y las métricas que muestra el notebook.
6. Descarga `lstm_horarios_meta_v4_<fecha>.zip`. Guárdalo en `python/modelos/` para la siguiente etapa de integración.

El notebook incluye los módulos Python: no necesitas subir los `.py` a Colab. Está diseñado para
el entorno estándar de Colab con TensorFlow, NumPy, pandas y matplotlib. Registra las versiones
exactas que se usen en `environment.json`; esas versiones servirán para reproducir la inferencia.

## Qué contiene el dataset inicial para entrenar ambas redes

**`datasets/dataset_meta_v4.csv` contiene 3.000 publicaciones SINTÉTICAS**:

- Facebook: 1.500 filas, tres cuentas simuladas.
- Instagram: 1.500 filas, tres cuentas simuladas.
- 500 publicaciones por cuenta; medición simulada a las 48 horas.
- `source=synthetic` y `measurement_protocol=synthetic_fixed_48h` en todas las filas.
- Diferentes patrones día/hora por cuenta, estacionalidad, dependencia temporal y ruido de contenido.
- Generación determinista con semilla `20260905`; no se renombraron filas Facebook como Instagram.

Sirve para ejecutar el entrenamiento completo, comprobar el aislamiento entre cuentas y producir
artefactos de las dos redes. **No demuestra que una LSTM recomiende buenas horas en cuentas reales.**
Si obtiene buenos resultados, habrá aprendido patrones introducidos en la simulación. Los modelos
permanecerán `experimental_only` y `ready_for_production=false`.

Para regenerarlo exactamente:

```powershell
python python/lstm_v4/generate_demo_dataset.py
```

El manifiesto `dataset_meta_v4.manifest.json` identifica su procedencia, semilla, cantidades y hash.
Después se debe entrenar/evaluar con datos reales antes de activar recomendaciones para clientes.

## Qué se hizo con el CSV original

El usuario confirmó que el CSV proporcionado contiene datos generados o de ejemplo.
Sus **1.200 filas** se conservaron convertidas en `datasets/dataset_facebook_legacy_v4.csv`,
sin mezclarlas con el dataset nuevo:

- Facebook: 1.200 filas; Instagram: **0 filas**.
- `account_id=legacy_facebook_unknown`: agrupación provisional porque el original carece de cuenta.
- `post_id=legacy_row_...`: identificador de fila, no identificador de publicación de Meta.
- `source=synthetic`: procedencia de ejemplo confirmada por el usuario.
- `measurement_protocol=unknown`: el original no indica cuándo se midieron sus resultados.
- Se interpreta la fecha/hora original como `America/La_Paz` (UTC−04:00), de acuerdo con el notebook V3.
- `likes` conserva `reacciones`; `comments` conserva `comentarios`.

`dataset_facebook_legacy_v4.manifest.json` contiene cantidades y hashes SHA-256 del original y de la conversión.
El CSV de escritorio y el entrenamiento V3 no se modificaron.

Este archivo alternativo permite ejecutar un **experimento de Facebook**. No acredita pertenencia
a ninguna cuenta real. Si lo subes a Colab en lugar del CSV nuevo, Instagram quedará `no_data`.
No cambies la etiqueta de las filas Facebook a Instagram.

## Contrato común para Facebook e Instagram

La plantilla vacía `datasets/plantilla_meta_real.csv` admite ambas redes. Una fila por publicación,
con una sola medición seleccionada según un protocolo consistente:

| Columna | Significado |
| --- | --- |
| `platform` | `facebook` o `instagram` |
| `account_id` | ID estable de página Facebook o cuenta Instagram |
| `post_id` | ID real de la publicación, único dentro de cuenta y red |
| `published_at` | Fecha ISO 8601 con offset, por ejemplo `2026-09-01T12:00:00-04:00` |
| `likes` | Reacciones Facebook o me gusta Instagram; entero no negativo |
| `comments` | Comentarios; entero no negativo |
| `metrics_observed_at` | Fecha real de consulta de los conteos, con offset |
| `source` | `meta_export`, `legacy_unverified` o `synthetic` |
| `measurement_protocol` | `snapshot_variable_age`, `unknown` o protocolo documentado como `fixed_48h` |

Una métrica faltante no es cero. El validador rechaza faltantes en los dos conteos utilizados.
El sistema Laravel actual convierte algunos campos ausentes a cero; antes de usar sus exportaciones
como evidencia, hay que comprobar que esos conteos se obtuvieron correctamente. El conversor no
puede reconstruir esa distinción si ya se perdió en el JSON.

No basta con escribir `fixed_48h`: las mediciones deben haberse recogido realmente a esa edad,
con la tolerancia documentada. Una consulta única hoy de todas las publicaciones no equivale
a una serie de snapshots históricos a las 48 horas.

## Incorporar el histórico de Meta sin modificar Laravel todavía

Si dispones del JSON que devuelve `MetaCampaignAnalyticsService` (estructura `generated_at`,
`platforms.facebook.account`, `platforms.facebook.posts`, y sus equivalentes Instagram), puedes
convertirlo sin tokens ni llamadas externas:

```powershell
python python/lstm_v4/prepare_dataset.py --meta-json ruta/analiticas.json --output python/datasets/dataset_meta_real.csv
```

Admite varios JSON con `--meta-json cuenta1.json cuenta2.json`. Rechaza publicaciones repetidas:
selecciona previamente la medición que corresponda al protocolo de cada publicación.
Solo exporta IDs, fechas, reacciones/me gusta y comentarios; no exporta tokens, textos ni imágenes.

Las exportaciones del panel actual son snapshots de edad variable. El conversor usa `generated_at`
como fecha de observación, no como si esas métricas se hubieran conocido al publicar. Si todas las
observaciones son posteriores a todas las publicaciones, no habrá secuencias causales de entrenamiento:
el resultado correcto será `insufficient_causal_history`. Guarda mediciones a una edad fija de nuevas
publicaciones para construir el histórico que falta. La captura automática se implementará después.

## Qué cambia respecto de V3

- **Objetivo:** `likes + 2 * comments`. Es un puntaje, no porcentaje de engagement. Excluye clics
  y usuarios únicos porque no están garantizados en las dos redes. V3 y V4 no tienen métricas de error
  directamente comparables porque predicen objetivos distintos.
- **Personalización:** cada candidato usa solamente publicaciones anteriores medidas de esa cuenta,
  y su promedio suavizado por día/hora. Los pesos se aprenden por plataforma con las cuentas disponibles.
  No se necesita una red por cuenta. La calidad en cuentas nuevas debe evaluarse por separado.
- **Histórico:** reacciones/me gusta, comentarios, puntaje, calendario cíclico y separación temporal.
- **Candidato:** día/hora, separación desde la última publicación con métricas disponibles, promedio
  histórico de la cuenta/franja y cantidad de observaciones. Los formatos y orden se exportan.
- **Referencia histórica:** se recalcula con la misma fórmula de V4; no se suma directamente al score
  actual del panel, que tiene pesos y denominadores diferentes.
- **Sin fuga temporal en datos fechados:** solo se usa información conocida antes del candidato;
  las etiquetas de entrenamiento/validación deben estar disponibles antes de su respectiva frontera.
- **Legado exploratorio:** cuando no se conoce la fecha de medición, se aproxima su disponibilidad
  por la fecha de publicación. Esta suposición puede sesgar la evaluación y bloquea aprobación automática.
- **Sin normalizadores pickle/joblib:** los parámetros se guardan como JSON y se utilizan con el mismo
  código en entrenamiento e inferencia.

## Cómo interpretar el resultado

Cada plataforma tiene su partición cronológica global 70/15/15, evitando que una cuenta de entrenamiento
aporte fechas posteriores a la validación de otra. Se exige un mínimo operativo de 150 filas y 30 instantes
distintos por red, y 60/12/12 secuencias utilizables por experimento. Son controles mínimos, no garantía
de representatividad ni una cantidad universal suficiente para LSTM.

Ventana y peso LSTM se seleccionan en validación; la prueba se evalúa después. La prueba es secuencial:
puede usar resultados previos de prueba cuando ya se han medido, sin volver a entrenar los pesos.
Los promedios estadísticos también usan solo información disponible, para una comparación coherente.

Se exportan MAE, RMSE, R² y correlación de rangos Spearman globales y errores por cuenta. Spearman aquí
compara publicaciones observadas: **no demuestra que el orden de horarios futuros sea correcto**.
No conocemos el resultado contrafactual de publicar el mismo contenido a otra hora.

- `no_data`: no hay filas de esa plataforma.
- `insufficient_data` / `insufficient_causal_history`: no se pudo entrenar con suficiente información.
- `experimental_only`: hay un modelo, pero la procedencia, protocolo o mejora no permiten proponerlo para piloto.
- `candidate_for_review`: pasa criterios retrospectivos básicos; aún requiere revisar procedencia y piloto.
- `ready_for_production` siempre es `false` en esta etapa.

`alpha=0` significa que la validación no encontró valor en la corrección LSTM. No se fuerza un peso
positivo para aparentar que funciona. Si la LSTM no supera el histórico, se conserva el resultado.

## Archivos exportados

El ZIP incluye, para cada red entrenada, `model.keras`, `scalers.json`, `metadata.json`, selección de
validación, historial de entrenamiento, métricas y predicciones de prueba, errores por cuenta y un fixture
para verificar la carga del modelo. Incluye también `data_pipeline.py`, `predict.py`, `train.py`, reporte
general, hash del dataset y versiones de bibliotecas. No incluye automáticamente el CSV completo de origen.

`predict.rank_slots` es una referencia para la futura integración, no una API desplegada. Recibe el histórico
validado de una sola cuenta y candidatos futuros con zona horaria. Rechaza mezclar redes o cuentas, congela
los datos conocidos al consultar y marca las franjas sin observaciones. Conserva el estado experimental.

## Comprobaciones locales

```powershell
python -m unittest discover -s python/lstm_v4 -p "test_*.py" -v
python python/lstm_v4/build_notebook.py
```

La primera orden usa NumPy y pandas, sin TensorFlow. Prueba aislamiento por cuenta y plataforma,
disponibilidad de métricas, exclusión de datos futuros, validación y normalización. La segunda regenera
el notebook autónomo después de cambiar los módulos. El entrenamiento TensorFlow y la comprobación
de carga del modelo se ejecutan en Colab.

Referencias: [series temporales de TensorFlow](https://www.tensorflow.org/tutorials/structured_data/time_series)
y [serialización de Keras](https://keras.io/api/models/model_saving_apis/model_saving_and_loading/).
