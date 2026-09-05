"""Genera un notebook autónomo: en Colab basta subir el CSV, no los módulos .py."""
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parent
cells = []


def markdown(text):
    cells.append(dict(cell_type='markdown', metadata={}, source=text.splitlines(keepends=True)))


def code(text):
    cells.append(dict(cell_type='code', metadata={}, execution_count=None, outputs=[], source=text.splitlines(keepends=True)))


markdown('''# PRODOVI · Horarios de publicación LSTM V4 · Facebook e Instagram

Entrenamiento híbrido por plataforma, condicionado al histórico de cada cuenta.
**El CSV inicial es SINTÉTICO: permite probar el proceso, no validar eficacia en Meta.**

1. En Colab selecciona Python 3 y, opcionalmente, GPU T4.
2. Ejecuta todas las celdas y sube `dataset_meta_v4.csv` cuando se solicite.
3. Revisa el reporte y descarga el ZIP al terminar.

El CSV inicial contiene 3.000 publicaciones simuladas: 1.500 Facebook y 1.500 Instagram,
con tres cuentas simuladas por plataforma y 500 publicaciones por cuenta.
Los patrones temporales fueron introducidos por un generador reproducible, no descubiertos en Meta.
Ambos modelos resultantes quedarán **experimentales** aunque sus métricas sean buenas.

El CSV original de 1.200 filas Facebook fue confirmado como ejemplo por el usuario y se conserva
convertido por separado en `dataset_facebook_legacy_v4.csv`. No se mezcló con el nuevo dataset.
Para entrenar con datos reales, sube un CSV del mismo contrato que conserve las cuentas y las
fechas reales de medición. Si falta una plataforma, quedará `no_data`.
''')
code('''from pathlib import Path
import sys
import tensorflow as tf
import pandas as pd
import numpy as np
print('Python:', sys.version)
print('TensorFlow:', tf.__version__)
print('GPU:', tf.config.list_physical_devices('GPU'))
WORK = Path('/content/prodovi_lstm_v4')
WORK.mkdir(parents=True, exist_ok=True)
''')
markdown('''## Contrato de datos y objetivo

Cada fila es una publicación de una cuenta identificada y una red: `facebook` o `instagram`.
`likes` significa reacciones en Facebook y me gusta en Instagram. Objetivo: **likes + 2 × comments**.
Es un puntaje ponderado, no una tasa ni una probabilidad. No es comparable numéricamente con el objetivo V3.

`published_at` y `metrics_observed_at` deben incluir offset, por ejemplo `2026-09-01T12:00:00-04:00`.
En datos Meta, la fecha de observación es obligatoria. La ventana solo usa métricas ya conocidas
antes de la publicación objetivo. No se mezclan cuentas, ni se completan faltantes con ceros.

En el dataset sintético se simula una medición a las 48 horas (`synthetic_fixed_48h`).
Para el legado se admite observación desconocida, con advertencia de evaluación exploratoria.
Un único snapshot actual de toda una cuenta no permite reconstruir qué métricas estaban disponibles
en el pasado. Puede terminar en `insufficient_causal_history`, aunque tenga muchas filas.
Recopila mediciones a una edad fija (por ejemplo 48 horas) para una evaluación temporal defendible.
''')
for name in ('data_pipeline.py', 'train.py', 'predict.py'):
    content = (ROOT / name).read_text(encoding='utf-8')
    code(f"# Módulo autocontenido: {name}\n(WORK / {name!r}).write_text({content!r}, encoding='utf-8')\n")
code('''# Recargar módulos si vuelves a ejecutar el notebook en la misma sesión.
sys.path.insert(0, str(WORK)) if str(WORK) not in sys.path else None
for name in ('train', 'predict', 'data_pipeline'):
    sys.modules.pop(name, None)
from google.colab import files
uploaded = files.upload()
csv_names = [name for name in uploaded if name.lower().endswith('.csv')]
if len(csv_names) != 1:
    raise ValueError('Sube exactamente un CSV unificado. No subas el CSV original V3.')
CSV_PATH = WORK / 'dataset_meta_v4.csv'
CSV_PATH.write_bytes(uploaded[csv_names[0]])
from data_pipeline import read_dataset
dataset = read_dataset(CSV_PATH)
display(dataset.groupby(['platform', 'source']).agg(publicaciones=('post_id', 'size'), cuentas=('account_id', 'nunique')))
print('Protocolos de medición:', dataset.measurement_protocol.unique().tolist())
print('Con observación conocida:', int(dataset.metrics_observed_at.notna().sum()), '/', len(dataset))
if not dataset.source.eq('meta_export').all():
    print('AVISO: hay datos de procedencia no verificada o sintéticos. Resultado experimental.')
if 'instagram' not in dataset.platform.values:
    print('Instagram: sin datos. No se fabricará un modelo para esa red.')
''')
markdown('''## Entrenamiento y evaluación

Se prueban ventanas de 3, 7 y 14 publicaciones. La selección de ventana y peso LSTM usa validación.
El 70 % / 15 % / 15 % se delimita cronológicamente por plataforma; se excluyen etiquetas que no
estaban disponibles antes de la frontera del conjunto. Los normalizadores se ajustan solo con entrenamiento.
Los promedios históricos de cada ejemplo se calculan solo con observaciones anteriores de su cuenta.

Se requieren 150 filas por red y al menos 60 / 12 / 12 secuencias de entrenamiento / validación / prueba.
Son umbrales operativos para el experimento, no una garantía de suficiencia estadística.

La prueba es secuencial: un resultado de prueba ya observado puede formar parte del histórico de
una predicción posterior. Los pesos del modelo no se actualizan. Esto reproduce inferencias sucesivas.
Si el peso LSTM elegido es cero, la validación prefirió el método histórico; no se fuerza una mejora.
''')
code('''from train import run_training
EPOCHS = 100  # Usa 2 solo para comprobar ejecución; para el experimento conserva 100.
archive, report = run_training(CSV_PATH, WORK / 'artifacts', epochs=EPOCHS)
''')
code('''import json
import matplotlib.pyplot as plt
artifact_dir = Path(archive).with_suffix('')
for network in ('facebook', 'instagram'):
    print(network.upper(), json.dumps(report[network], ensure_ascii=False, indent=2))
    folder = artifact_dir / network
    if not (folder / 'test_predictions.csv').exists():
        continue
    results = pd.read_csv(folder / 'test_predictions.csv')
    history = pd.read_csv(folder / 'training_history.csv')
    fig, axes = plt.subplots(1, 2, figsize=(15, 4))
    axes[0].plot(history.loss, label='Entrenamiento')
    axes[0].plot(history.val_loss, label='Validación')
    axes[0].set_title(f'{network}: pérdida del residuo normalizado')
    axes[0].legend()
    ordered = results.sort_values('published_at')
    for column, label in [('actual', 'Real'), ('prediction_lstm_hybrid', 'LSTM híbrida'), ('prediction_slot_history', 'Histórico')]:
        axes[1].plot(ordered[column].to_numpy(), label=label, alpha=0.8)
    axes[1].set_title(f'{network}: prueba temporal · puntaje ponderado')
    axes[1].legend()
    plt.show()
    display(pd.DataFrame(json.loads((folder / 'per_account_metrics.json').read_text())))
''')
markdown('''## Comprobar artefacto y descargar

El ZIP contiene modelos `.keras`, normalizadores JSON, contrato de características, métricas globales
y por cuenta, predicciones de prueba, versiones de bibliotecas y código de inferencia.
La prueba siguiente verifica que el modelo guardado reproduce su salida, no su eficacia comercial.

**Ningún modelo se declara listo para producción automáticamente.** La mejora de MAE no prueba
una mejora causal por cambiar la hora, ni una buena clasificación de horarios nunca observados.
La validación prospectiva y la integración Laravel se harán después de revisar este ZIP.
''')
code('''for network in ('facebook', 'instagram'):
    folder = artifact_dir / network
    if not (folder / 'inference_smoke_test.npz').exists():
        continue
    fixture = np.load(folder / 'inference_smoke_test.npz', allow_pickle=False)
    restored = tf.keras.models.load_model(folder / 'model.keras', compile=False)
    actual = restored.predict({'history_sequence': fixture['history'], 'candidate_slot': fixture['candidate']}, verbose=0)
    np.testing.assert_allclose(actual, fixture['expected_residual_scaled'], rtol=1e-5, atol=1e-6)
    print(network, 'modelo exportado verificado')
files.download(archive)
''')
markdown('''## Referencias técnicas

- [TensorFlow: series temporales, ventanas y separación temporal](https://www.tensorflow.org/tutorials/structured_data/time_series).
- [Keras: guardado y recuperación de modelos](https://keras.io/api/models/model_saving_apis/model_saving_and_loading/).
''')
notebook = dict(cells=cells, metadata={'colab': {'name': 'ENTRENAMIENTO_LSTM_HORARIOS_V4_META_COLAB.ipynb'},
               'kernelspec': {'display_name': 'Python 3', 'language': 'python', 'name': 'python3'},
               'language_info': {'name': 'python', 'version': '3.12'}}, nbformat=4, nbformat_minor=5)
for i, cell in enumerate(cells):
    cell['id'] = f'prodovi-v4-{i:02}'
target = ROOT.parent / 'ENTRENAMIENTO_LSTM_HORARIOS_V4_META_COLAB.ipynb'
target.write_text(json.dumps(notebook, ensure_ascii=False, indent=2), encoding='utf-8')
print(target)
