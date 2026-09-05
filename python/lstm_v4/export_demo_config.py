"""Genera las variables para el hosting y un ZIP de actualización sin incluir secretos."""
import argparse
from pathlib import Path
import re
import zipfile

ROOT = Path(__file__).resolve().parents[2]
DEMO = ROOT / 'python' / '.demo'


def export(url):
    if not re.fullmatch(r'https://[a-z0-9-]+\.trycloudflare\.com', url):
        raise ValueError('Dirección de Quick Tunnel no válida')
    token = (DEMO / 'api-token.txt').read_text(encoding='ascii').strip()
    (DEMO / 'production.env').write_text(
        f'LSTM_DRIVER=http\nLSTM_API_URL={url}\nLSTM_API_TOKEN={token}\n'
        'LSTM_TIMEOUT=90\nLSTM_MODEL_REVISION=meta_v4_20260905\n', encoding='utf-8')
    (DEMO / 'tunnel-url.txt').write_text(url, encoding='ascii')
    with zipfile.ZipFile(DEMO / 'lstm-hosting-update.zip', 'w', zipfile.ZIP_DEFLATED) as bundle:
        for name in ('config/lstm.php', 'app/Services/LstmInferenceService.php',
                     'app/Services/PublicationTimingService.php'):
            bundle.write(ROOT / name, name)
    print('URL HTTPS:', url)
    print('Variables privadas:', DEMO / 'production.env')
    print('Actualización del hosting:', DEMO / 'lstm-hosting-update.zip')


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('url')
    export(parser.parse_args().url)
