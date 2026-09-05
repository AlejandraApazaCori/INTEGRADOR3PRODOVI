"""Datos SINTÉTICOS reproducibles para probar ambas redes. Nunca son evidencia de eficacia en Meta."""
import hashlib
import json
import math
import random
from datetime import datetime, timedelta, timezone
from pathlib import Path

from prepare_dataset import write_csv

SEED = 20260905
POSTS_PER_ACCOUNT = 500
ACCOUNTS_PER_PLATFORM = 3


def generate():
    rows = []
    start = datetime(2025, 1, 1, tzinfo=timezone(timedelta(hours=-4)))
    # 600 días, 24 horas disponibles; muestreo sin reemplazo por cuenta.
    for network_index, network in enumerate(('facebook', 'instagram')):
        for account_index in range(ACCOUNTS_PER_PLATFORM):
            rng = random.Random(SEED + 1000*network_index + account_index)
            account = f'synthetic_{network}_{account_index+1:02}'
            instants = sorted(rng.sample(range(600*24), POSTS_PER_ACCOUNT))
            preferred_hour = (9 + 5*account_index + 3*network_index) % 24
            preferred_day = (account_index*2 + network_index) % 7
            state = 0.0
            for index, offset in enumerate(instants):
                date = start + timedelta(hours=offset)
                # Simulación explícita: hora, día, estacionalidad, estado persistente y ruido de contenido.
                state = 0.84*state + rng.gauss(0, 0.12)
                hour_effect = 0.48*math.cos(2*math.pi*(date.hour-preferred_hour)/24)
                day_effect = 0.20*math.cos(2*math.pi*(date.weekday()-preferred_day)/7)
                season = 0.18*math.sin(2*math.pi*(offset/24)/100)
                content_noise = rng.gauss(0, 0.30)
                log_mean = 3.7 + account_index*0.25 + network_index*0.15 + hour_effect + day_effect + season + state + content_noise
                likes = max(0, round(math.exp(log_mean) + rng.gauss(0, 4)))
                comments = max(0, round(likes*(0.065 + network_index*0.015) + rng.gauss(0, 2)))
                rows.append(dict(platform=network, account_id=account, post_id=f'{account}_post_{index+1:04}',
                                 published_at=date.isoformat(), likes=likes, comments=comments,
                                 metrics_observed_at=(date+timedelta(hours=48)).isoformat(),
                                 source='synthetic', measurement_protocol='synthetic_fixed_48h'))
    return sorted(rows, key=lambda row: (row['published_at'], row['platform'], row['account_id']))


if __name__ == '__main__':
    target = Path(__file__).resolve().parents[1] / 'datasets' / 'dataset_meta_v4.csv'
    rows = generate()
    write_csv(target, rows)
    manifest = dict(source='synthetic', seed=SEED, rows=len(rows),
                    rows_per_platform={p: sum(r['platform'] == p for r in rows) for p in ('facebook', 'instagram')},
                    accounts_per_platform=ACCOUNTS_PER_PLATFORM, posts_per_account=POSTS_PER_ACCOUNT,
                    dataset_sha256=hashlib.sha256(target.read_bytes()).hexdigest(),
                    generator='lstm_v4/generate_demo_dataset.py', measurement='simulated_at_48h',
                    real_meta_data=False, permitted_use='software_and_training_pipeline_experiment',
                    limitations=['Patrones día/hora y dependencia temporal fueron introducidos por el generador.',
                                 'Un buen resultado aprende la simulación, no demuestra eficacia en cuentas Meta.',
                                 'No utilizar este modelo para recomendaciones reales sin datos y validación reales.',
                                 'No deriva Instagram mediante renombrar filas de Facebook.'])
    target.with_suffix('.manifest.json').write_text(json.dumps(manifest, ensure_ascii=False, indent=2), encoding='utf-8')
    print(json.dumps(manifest, ensure_ascii=False, indent=2))
