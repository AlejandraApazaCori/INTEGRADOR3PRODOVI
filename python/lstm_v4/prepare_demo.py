"""Prepara la clave privada y cloudflared oficial para la demostración Windows."""
import hashlib
from pathlib import Path
import secrets
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
DEMO = ROOT / '.demo'
VERSION = '2026.8.3'
SHA256 = '83e726ed18ea78c5ad5213c4c3a3a27051393950d2bc8ed4de69bec12d14eaae'
URL = f'https://github.com/cloudflare/cloudflared/releases/download/{VERSION}/cloudflared-windows-amd64.exe'


def prepare():
    DEMO.mkdir(exist_ok=True)
    key = DEMO / 'api-token.txt'
    if not key.exists():
        key.write_text(secrets.token_urlsafe(48), encoding='ascii')
    executable = DEMO / 'cloudflared.exe'
    if not executable.exists():
        print('Descargando cloudflared oficial...', flush=True)
        with urllib.request.urlopen(URL, timeout=60) as response:
            data = response.read(80_000_000)
        if hashlib.sha256(data).hexdigest() != SHA256:
            raise RuntimeError('El ejecutable descargado no coincide con el checksum oficial')
        executable.write_bytes(data)
    if hashlib.sha256(executable.read_bytes()).hexdigest() != SHA256:
        raise RuntimeError('cloudflared no coincide con la versión verificada')
    print('Clave local preparada (no se muestra); cloudflared verificado.', flush=True)


if __name__ == '__main__':
    prepare()
