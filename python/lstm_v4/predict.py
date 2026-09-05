"""Referencia de inferencia para la futura integración. No publica contenido."""
import json
from pathlib import Path

import numpy as np
import pandas as pd
import tensorflow as tf

from data_pipeline import make_inputs, transform, invert, combine, TIMEZONE, VERSION


def rank_slots(model_dir, account_history, candidates):
    folder = Path(model_dir)
    meta = json.loads((folder / 'metadata.json').read_text(encoding='utf-8'))
    scalers = json.loads((folder / 'scalers.json').read_text(encoding='utf-8'))
    if meta['model_version'] != VERSION:
        raise ValueError('Versión de contrato incompatible.')
    if account_history.empty or account_history[['platform', 'account_id']].drop_duplicates().shape[0] != 1:
        raise ValueError('Entrega el histórico de una sola cuenta y red.')
    if account_history.platform.iloc[0] != meta['platform']:
        raise ValueError('No se puede usar el modelo de una red para otra.')
    model = tf.keras.models.load_model(folder / 'model.keras', compile=False)
    hs, cs, priors, records = [], [], [], []
    now = pd.Timestamp.now(tz=TIMEZONE)
    # Congelar información conocida ahora: no simular métricas futuras entre candidatos.
    available = account_history[(account_history.available_at <= now) & (account_history.published_at <= now)]
    for timestamp in candidates:
        timestamp = pd.Timestamp(timestamp)
        if timestamp.tzinfo is None or timestamp <= now:
            raise ValueError('Todos los candidatos deben ser futuros y tener zona horaria.')
        h, c, prior, count = make_inputs(available, timestamp, meta['window'], meta['smoothing_strength'])
        hs.append(h)
        cs.append(c)
        priors.append(prior)
        records.append(dict(timestamp=timestamp.tz_convert(TIMEZONE).isoformat(), samples_in_slot=count,
                            account_id=str(available.account_id.iloc[0]), platform=meta['platform'],
                            historical_score=float(np.expm1(prior)), status=meta['status'],
                            ready_for_production=False, unseen_slot=count == 0))
    if not records:
        return []
    raw = model.predict({'history_sequence': transform(hs, scalers['history']),
                         'candidate_slot': transform(cs, scalers['candidate'])}, verbose=0)
    residual = invert(raw, scalers['residual']).ravel()
    predicted = combine(priors, residual, meta['alpha'])
    for row, value in zip(records, predicted):
        row['predicted_score'] = float(value)
    return sorted(records, key=lambda row: row['predicted_score'], reverse=True)
