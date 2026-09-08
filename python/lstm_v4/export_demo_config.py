"""Genera las variables para el hosting y un ZIP de actualización sin incluir secretos."""
import argparse
from pathlib import Path
import re
import zipfile

ROOT = Path(__file__).resolve().parents[2]
DEMO = ROOT / 'python' / '.demo'
LSTM_KEYS = {
    'LSTM_DRIVER', 'LSTM_API_URL', 'LSTM_API_TOKEN', 'LSTM_TIMEOUT',
    'LSTM_MODEL_REVISION',
}


def env_values(url, token):
    return {
        'LSTM_DRIVER': 'http',
        'LSTM_API_URL': url,
        'LSTM_API_TOKEN': token,
        'LSTM_TIMEOUT': '90',
        'LSTM_MODEL_REVISION': 'meta_v4_20260905',
    }


def sync_project_env(values):
    """Actualiza solo las variables LSTM; conserva el resto del .env local."""
    path = ROOT / '.env'
    if not path.is_file():
        return False
    lines = path.read_text(encoding='utf-8').splitlines()
    written = set()
    result = []
    for line in lines:
        match = re.match(r'^([A-Z0-9_]+)=', line)
        key = match.group(1) if match else None
        if key in LSTM_KEYS:
            if key not in written:
                result.append(f'{key}={values[key]}')
                written.add(key)
            continue
        result.append(line)
    if written != LSTM_KEYS:
        insert_at = next((i for i, line in enumerate(result) if line.startswith('APP_ENV=')), len(result))
        missing = [f'{key}={value}' for key, value in values.items() if key not in written]
        result[insert_at:insert_at] = missing
    path.write_text('\n'.join(result) + '\n', encoding='utf-8')
    return True


def export(url, sync_env=False):
    if not re.fullmatch(r'https://[^/\s]+', url):
        raise ValueError('La API debe usar una dirección HTTPS sin rutas adicionales')
    token = (DEMO / 'api-token.txt').read_text(encoding='ascii').strip()
    values = env_values(url, token)
    (DEMO / 'production.env').write_text(
        ''.join(f'{key}={value}\n' for key, value in values.items()), encoding='utf-8')
    (DEMO / 'tunnel-url.txt').write_text(url, encoding='ascii')
    with zipfile.ZipFile(DEMO / 'lstm-hosting-update.zip', 'w', zipfile.ZIP_DEFLATED) as bundle:
        for name in ('config/lstm.php', 'app/Services/LstmInferenceService.php',
                     'app/Services/PublicationTimingService.php'):
            bundle.write(ROOT / name, name)
    if sync_env:
        sync_project_env(values)
    print('URL HTTPS:', url)
    print('Variables privadas:', DEMO / 'production.env')
    print('Actualización del hosting:', DEMO / 'lstm-hosting-update.zip')
    if sync_env:
        print('El .env local quedó sincronizado con la URL actual.')


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('url')
    parser.add_argument('--sync-env', action='store_true', help='actualiza las variables LSTM del .env local')
    args = parser.parse_args()
    export(args.url, args.sync_env)
