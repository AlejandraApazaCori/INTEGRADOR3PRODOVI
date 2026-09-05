"""Instala solo artefactos conocidos; no ejecuta código incluido en el ZIP."""
import argparse
import hashlib
import json
import zipfile
from pathlib import Path


def install(archive, destination):
    expected = ['environment.json', 'training_report.json', 'deployment_gate.json']
    for network in ('facebook', 'instagram'):
        expected += [f'{network}/{name}' for name in ('metadata.json', 'model.keras', 'scalers.json', 'inference_smoke_test.npz', 'test_metrics.json')]
    with zipfile.ZipFile(archive) as bundle:
        for name in expected:
            info = bundle.getinfo(name)
            if info.file_size > 100_000_000:
                raise ValueError('Artefacto demasiado grande')
        # Verificar contrato completo antes de escribir.
        for network in ('facebook', 'instagram'):
            meta = json.loads(bundle.read(f'{network}/metadata.json'))
            if meta['platform'] != network or meta['model_version'] != 'meta_lstm_hybrid_v4.0.0':
                raise ValueError('ZIP incompatible')
        if destination.exists():
            raise ValueError('El destino ya existe. Usa un nuevo directorio versionado.')
        destination.mkdir(parents=True)
        for name in expected:
            target = destination / name
            target.parent.mkdir(exist_ok=True)
            target.write_bytes(bundle.read(name))
    (destination / 'archive_sha256.txt').write_text(hashlib.sha256(archive.read_bytes()).hexdigest(), encoding='ascii')
    print(destination)


if __name__ == '__main__':
    p = argparse.ArgumentParser()
    p.add_argument('archive', type=Path)
    p.add_argument('destination', type=Path)
    a = p.parse_args()
    install(a.archive, a.destination)
