"""Entrena en Colab; un modelo por red, ventanas exclusivas por cuenta, prueba temporal."""
import hashlib
import json
import platform as runtime_platform
import shutil
from datetime import datetime, timezone
from pathlib import Path

import numpy as np
import pandas as pd
import tensorflow as tf

from data_pipeline import (VERSION, TIMEZONE, HISTORY_FEATURES, CANDIDATE_FEATURES,
                           read_dataset, build_examples, fit_scaler, transform, invert, combine)

SEED = 623
WINDOWS = [3, 7, 14]
STRENGTH = 3.0


def save_json(path, value):
    Path(path).write_text(json.dumps(value, ensure_ascii=False, indent=2, allow_nan=False), encoding='utf-8')


def metrics(actual, predicted):
    actual, predicted = np.asarray(actual), np.asarray(predicted)
    error = actual - predicted
    total = float(np.sum((actual - actual.mean())**2))
    correlation = pd.Series(actual).rank().corr(pd.Series(predicted).rank())
    return dict(MAE=float(np.abs(error).mean()), RMSE=float(np.sqrt((error**2).mean())),
                R2=float(1 - np.sum(error**2)/total) if total else None,
                Spearman=float(correlation) if np.isfinite(correlation) else None)


def model_for(window):
    history = tf.keras.layers.Input((window, len(HISTORY_FEATURES)), name='history_sequence')
    candidate = tf.keras.layers.Input((len(CANDIDATE_FEATURES),), name='candidate_slot')
    h = tf.keras.layers.LSTM(32, dropout=0.15)(history)
    c = tf.keras.layers.Dense(12, activation='relu')(candidate)
    x = tf.keras.layers.Concatenate()([h, c])
    x = tf.keras.layers.Dense(24, activation='relu')(x)
    x = tf.keras.layers.Dropout(0.15)(x)
    output = tf.keras.layers.Dense(1, name='residual_log')(x)
    model = tf.keras.Model([history, candidate], output)
    model.compile(optimizer=tf.keras.optimizers.Adam(0.001), loss='mse')
    return model


def train_platform(frame, network, output, epochs=100):
    # Fronteras globales por red: ninguna cuenta entrena con fechas futuras de otra.
    dates = sorted(frame.published_at.unique())
    if len(frame) < 150 or len(dates) < 30:
        return dict(status='insufficient_data', rows=len(frame),
                    reason='Se requieren al menos 150 filas y 30 fechas/horas distintas por red para este piloto.')
    train_end, val_end = dates[int(len(dates)*0.70)], dates[int(len(dates)*0.85)]
    out = output / network
    out.mkdir()
    selected, experiments = None, []
    for window in WINDOWS:
        h, c, y, records = build_examples(frame, window, STRENGTH)
        if records.empty:
            experiments.append(dict(window=window, status='no_causal_sequences'))
            continue
        masks = {
            'train': ((records.published_at < train_end) & (records.available_at < train_end)).to_numpy(),
            'validation': ((records.published_at >= train_end) & (records.published_at < val_end)
                           & (records.available_at < val_end)).to_numpy(),
            'test': (records.published_at >= val_end).to_numpy(),
        }
        counts = {name: int(mask.sum()) for name, mask in masks.items()}
        if counts['train'] < 60 or counts['validation'] < 12 or counts['test'] < 12:
            experiments.append(dict(window=window, status='insufficient_sequences', **counts))
            continue
        tf.keras.backend.clear_session()
        tf.keras.utils.set_random_seed(SEED)
        scalers = dict(history=fit_scaler(h[masks['train']].reshape(-1, h.shape[-1])),
                       candidate=fit_scaler(c[masks['train']]), residual=fit_scaler(y[masks['train']]))
        hs, cs, ys = transform(h, scalers['history']), transform(c, scalers['candidate']), transform(y, scalers['residual'])
        model = model_for(window)
        inputs = lambda mask: {'history_sequence': hs[mask], 'candidate_slot': cs[mask]}
        print(f'{network}: ventana {window}, secuencias {counts}', flush=True)
        history = model.fit(inputs(masks['train']), ys[masks['train']],
                            validation_data=(inputs(masks['validation']), ys[masks['validation']]),
                            epochs=epochs, batch_size=32, shuffle=False, verbose=2,
                            callbacks=[tf.keras.callbacks.EarlyStopping(monitor='val_loss', patience=12, restore_best_weights=True),
                                       tf.keras.callbacks.ReduceLROnPlateau(monitor='val_loss', patience=5, factor=0.5, min_lr=1e-5)])
        residual = invert(model.predict(inputs(masks['validation']), verbose=0), scalers['residual']).ravel()
        val = records.loc[masks['validation']]
        scores = [(float(alpha), metrics(val.actual, combine(val.prior_log, residual, alpha))['MAE'])
                  for alpha in np.linspace(0, 1, 21)]
        alpha, mae = min(scores, key=lambda item: item[1])
        experiments.append(dict(window=window, status='trained', alpha=alpha, validation_MAE=mae,
                                best_epoch=int(np.argmin(history.history['val_loss'])+1), **counts))
        if selected is None or mae < selected['mae']:
            # Guardar ahora: clear_session del siguiente experimento no altera el artefacto elegido.
            model.save(out / 'model.keras')
            selected = dict(mae=mae, window=window, alpha=alpha, scalers=scalers,
                            records=records, masks=masks, hs=hs, cs=cs,
                            history=history.history, counts=counts)
    save_json(out / 'validation_selection.json', experiments)
    if selected is None:
        return dict(status='insufficient_causal_history', rows=len(frame),
                    reason='No hay suficientes métricas conocidas antes de cada candidato y frontera temporal.',
                    experiments=experiments)
    s = selected
    model = tf.keras.models.load_model(out / 'model.keras', compile=False)
    mask = s['masks']['test']
    test = s['records'].loc[mask].copy()
    test_inputs = {'history_sequence': s['hs'][mask], 'candidate_slot': s['cs'][mask]}
    residual = invert(model.predict(test_inputs, verbose=0), s['scalers']['residual']).ravel()
    test['prediction_lstm_hybrid'] = combine(test.prior_log, residual, s['alpha'])
    test['prediction_slot_history'] = combine(test.prior_log, 0, 0)
    test['prediction_account_mean_log'] = combine(test.global_log, 0, 0)
    report = {name: metrics(test.actual, test[column]) for name, column in {
        'lstm_hybrid': 'prediction_lstm_hybrid', 'slot_history': 'prediction_slot_history',
        'account_mean_log': 'prediction_account_mean_log'}.items()}
    baseline = min(report['slot_history']['MAE'], report['account_mean_log']['MAE'])
    improvement = 100*(baseline-report['lstm_hybrid']['MAE'])/baseline if baseline else None
    per_account = []
    for account, group in test.groupby('account_id'):
        per_account.append(dict(account_id=account, samples=len(group),
                                hybrid=metrics(group.actual, group.prediction_lstm_hybrid),
                                historical=metrics(group.actual, group.prediction_slot_history)))
    # Incluso un buen resultado retrospectivo necesita verificación de procedencia y un piloto prospectivo.
    verified = bool(frame.source.eq('meta_export').all())
    protocols = sorted(frame.measurement_protocol.unique().tolist())
    controlled = bool(len(protocols) == 1 and protocols[0].startswith('fixed_'))
    offline_better = bool(s['alpha'] > 0 and baseline > report['lstm_hybrid']['MAE'])
    metadata = dict(model_version=VERSION, platform=network, window=s['window'], alpha=s['alpha'],
                    smoothing_strength=STRENGTH, timezone=TIMEZONE, day_convention='0=Monday,6=Sunday',
                    history_features=HISTORY_FEATURES, candidate_features=CANDIDATE_FEATURES,
                    engagement_formula='likes + 2 * comments; Facebook likes means reactions',
                    output_unit='weighted_interaction_score_not_percentage',
                    inference_contract='data_pipeline.make_inputs -> scalers -> model -> combine',
                    personalized_by='same-account causal history and same-account slot prior; no account embedding',
                    dataset_rows=len(frame), accounts=int(frame.account_id.nunique()),
                    sources=sorted(frame.source.unique().tolist()), measurement_protocols=protocols,
                    verified_provenance=verified, controlled_measurement=controlled,
                    offline_lstm_beats_baselines=offline_better, test_improvement_percent=improvement,
                    ready_for_production=False, requires_prospective_validation=True,
                    status='candidate_for_review' if verified and controlled and offline_better else 'experimental_only',
                    split={'strategy': 'global_chronological_per_platform_with_label_availability',
                           'train_before': str(train_end), 'validation_before': str(val_end), **s['counts']},
                    limitations=['No demuestra que cambiar la hora cause más interacciones.',
                                 'No incluye contenido, campañas pagadas ni tamaño de audiencia.',
                                 'Legado sin fecha de medición usa disponibilidad aproximada: evaluación exploratoria.',
                                 'Un identificador legacy no acredita pertenencia a una cuenta real.',
                                 'MAE/RMSE evalúan publicaciones observadas; no validan horas sin publicaciones.',
                                 'No activar Instagram con un modelo entrenado únicamente para Facebook.'])
    save_json(out / 'metadata.json', metadata)
    save_json(out / 'scalers.json', s['scalers'])
    save_json(out / 'test_metrics.json', report)
    save_json(out / 'per_account_metrics.json', per_account)
    test.to_csv(out / 'test_predictions.csv', index=False)
    pd.DataFrame(s['history']).to_csv(out / 'training_history.csv', index=False)
    np.savez_compressed(out / 'inference_smoke_test.npz', history=s['hs'][mask][:2],
                        candidate=s['cs'][mask][:2],
                        expected_residual_scaled=model.predict({k: v[:2] for k, v in test_inputs.items()}, verbose=0))
    print(network, metadata['status'], report, flush=True)
    return dict(status=metadata['status'], window=s['window'], alpha=s['alpha'], metrics=report,
                test_improvement_percent=improvement, ready_for_production=False)


def run_training(csv_path, output_parent='artifacts', epochs=100):
    tf.keras.utils.set_random_seed(SEED)
    try:
        tf.config.experimental.enable_op_determinism()
    except (AttributeError, RuntimeError):
        pass
    frame = read_dataset(csv_path)
    # Directorio nuevo por ejecución: un ZIP nunca hereda un modelo de otra ejecución.
    stamp = datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%S%fZ')
    output = Path(output_parent) / f'lstm_horarios_meta_v4_{stamp}'
    output.mkdir(parents=True, exist_ok=False)
    report = {}
    for network in ('facebook', 'instagram'):
        subset = frame[frame.platform.eq(network)].copy()
        report[network] = train_platform(subset, network, output, epochs) if len(subset) else {'status': 'no_data'}
    save_json(output / 'training_report.json', report)
    save_json(output / 'environment.json', dict(python=runtime_platform.python_version(), tensorflow=tf.__version__,
              keras=tf.keras.__version__, numpy=np.__version__, pandas=pd.__version__, seed=SEED,
              dataset_sha256=hashlib.sha256(Path(csv_path).read_bytes()).hexdigest()))
    for name in ('data_pipeline.py', 'predict.py', 'train.py'):
        shutil.copyfile(Path(__file__).parent / name, output / name)
    save_json(output / 'deployment_gate.json', dict(ready_for_production=False,
              instruction='Revisar procedencia, métricas, cobertura por cuenta y piloto antes de integrar recomendaciones.'))
    archive = shutil.make_archive(str(output), 'zip', root_dir=output)
    print(json.dumps(report, ensure_ascii=False, indent=2))
    print('ZIP para devolver al proyecto:', archive)
    return archive, report
