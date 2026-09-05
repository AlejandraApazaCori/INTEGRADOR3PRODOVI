"""Inferencia local JSON por stdin/stdout para Laravel. Nunca recibe credenciales Meta."""
import os
os.environ.setdefault('TF_CPP_MIN_LOG_LEVEL', '2')
os.environ.setdefault('TF_NUM_INTRAOP_THREADS', '2')
os.environ.setdefault('TF_NUM_INTEROP_THREADS', '2')

import argparse
import io
import json
import sys
from pathlib import Path

import numpy as np
import pandas as pd
import tensorflow as tf

from data_pipeline import VERSION, HISTORY_FEATURES, CANDIDATE_FEATURES, read_dataset, make_inputs, transform, invert, combine


def load_artifact(root, network):
    folder = root / network
    metadata = json.loads((folder / 'metadata.json').read_text(encoding='utf-8'))
    if (metadata['model_version'] != VERSION or metadata['platform'] != network
            or metadata['history_features'] != HISTORY_FEATURES
            or metadata['candidate_features'] != CANDIDATE_FEATURES):
        raise ValueError('Contrato de modelo incompatible')
    scalers = json.loads((folder / 'scalers.json').read_text(encoding='utf-8'))
    model = tf.keras.models.load_model(folder / 'model.keras', compile=False, safe_mode=True)
    return folder, metadata, scalers, model


def execute(payload, root):
    output = {}
    now = pd.Timestamp.now(tz='UTC')
    for item in payload.get('accounts', []):
        network = item['platform']
        if network not in ('facebook', 'instagram') or network in output:
            raise ValueError('Plataforma inválida o repetida')
        folder, meta, scalers, model = load_artifact(root, network)
        if payload.get('health'):
            fixture = np.load(folder / 'inference_smoke_test.npz', allow_pickle=False)
            actual = model({'history_sequence': fixture['history'], 'candidate_slot': fixture['candidate']}, training=False).numpy()
            np.testing.assert_allclose(actual, fixture['expected_residual_scaled'], rtol=1e-4, atol=1e-5)
            output[network] = {'status': meta['status'], 'window': meta['window'], 'model_version': meta['model_version'], 'verified_load': True}
            continue
        base = {'account_id': str(item['account_id']), 'model_version': meta['model_version'],
                'model_status': meta['status'], 'training_sources': meta['sources'],
                'experimental': not meta.get('ready_for_production', False), 'window': meta['window'],
                'alpha': meta['alpha'], 'unit': 'weighted_interaction_score', 'slots': []}
        rows = item.get('posts', [])
        if not rows:
            output[network] = {**base, 'status': 'insufficient_data', 'history_count': 0}
            continue
        history = read_dataset(io.StringIO(pd.DataFrame(rows).to_csv(index=False)))
        if (not history.platform.eq(network).all() or not history.account_id.eq(str(item['account_id'])).all()):
            raise ValueError('El histórico no pertenece a la cuenta solicitada')
        history = history[(history.available_at <= now) & (history.published_at <= now)]
        base['history_count'] = len(history)
        if len(history) < meta['window']:
            output[network] = {**base, 'status': 'insufficient_data'}
            continue
        candidates = [pd.Timestamp(value) for value in payload['candidates']]
        if not candidates or len(candidates) > 336 or any(t.tzinfo is None or t <= now for t in candidates):
            raise ValueError('Se requieren de 1 a 336 horarios futuros con zona horaria')
        hs, cs, priors, slots = [], [], [], []
        for timestamp in candidates:
            h, c, prior, samples = make_inputs(history, timestamp, meta['window'], meta['smoothing_strength'])
            hs.append(h)
            cs.append(c)
            priors.append(prior)
            slots.append({'timestamp': timestamp.tz_convert(meta['timezone']).isoformat(),
                          'samples': samples, 'historical_score': float(np.expm1(prior)), 'unseen_slot': samples == 0})
        raw = model({'history_sequence': transform(hs, scalers['history']),
                     'candidate_slot': transform(cs, scalers['candidate'])}, training=False).numpy()
        scores = combine(priors, invert(raw, scalers['residual']).ravel(), meta['alpha'])
        if not np.isfinite(scores).all():
            raise ValueError('El modelo devolvió valores no finitos')
        for slot, score in zip(slots, scores):
            slot['predicted_score'] = round(float(score), 4)
        output[network] = {**base, 'status': 'ok', 'slots': slots}
    return {'platforms': output}


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--models', type=Path, required=True)
    args = parser.parse_args()
    try:
        result = execute(json.load(sys.stdin), args.models)
        print(json.dumps(result, ensure_ascii=True, allow_nan=False))
    except Exception as exc:
        print(f'LSTM: {type(exc).__name__}: {exc}', file=sys.stderr)
        sys.exit(1)
