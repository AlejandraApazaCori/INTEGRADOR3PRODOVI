"""Conversión explícita de CSV legado o JSON de MetaCampaignAnalyticsService; solo biblioteca estándar."""
import argparse
import csv
import hashlib
import json
from datetime import datetime, timezone, timedelta
from pathlib import Path

COLUMNS = ['platform', 'account_id', 'post_id', 'published_at', 'likes', 'comments',
           'metrics_observed_at', 'source', 'measurement_protocol']


def legacy_rows(path, source='legacy_unverified'):
    with open(path, encoding='utf-8-sig', newline='') as stream:
        rows = list(csv.DictReader(stream))
    result = []
    for index, row in enumerate(rows, 1):
        date = datetime.fromisoformat(row['fecha_publicacion']).replace(
            hour=int(row['hora_publicacion']), tzinfo=timezone(timedelta(hours=-4)))
        result.append(dict(platform='facebook', account_id='legacy_facebook_unknown',
                           post_id=f'legacy_row_{index:06}', published_at=date.isoformat(),
                           likes=row['reacciones'], comments=row['comentarios'],
                           metrics_observed_at='', source=source, measurement_protocol='unknown'))
    return result


def meta_rows(path):
    payload = json.loads(Path(path).read_text(encoding='utf-8-sig'))
    observed = payload.get('generated_at')
    if not observed:
        raise ValueError('El JSON necesita generated_at.')
    rows = []
    skipped = []
    for platform in ('facebook', 'instagram'):
        data = payload.get('platforms', {}).get(platform, {})
        account_id = (data.get('account') or {}).get('id')
        if not account_id:
            continue
        for post in data.get('posts', []):
            likes = post.get('reactions') if platform == 'facebook' else post.get('likes')
            comments = post.get('comments')
            if likes is None or comments is None or not post.get('id') or not post.get('timestamp'):
                skipped.append(post.get('id'))
                continue
            rows.append(dict(platform=platform, account_id=str(account_id), post_id=str(post['id']),
                             published_at=post['timestamp'], likes=likes, comments=comments,
                             metrics_observed_at=observed, source='meta_export',
                             measurement_protocol='snapshot_variable_age'))
    print(f'Meta: {len(rows)} filas; {len(skipped)} omitidas por campos faltantes.')
    return rows


def write_csv(path, rows):
    path = Path(path)
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open('w', encoding='utf-8', newline='') as stream:
        writer = csv.DictWriter(stream, fieldnames=COLUMNS)
        writer.writeheader()
        writer.writerows(rows)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--legacy', type=Path)
    parser.add_argument('--legacy-source', choices=['legacy_unverified', 'synthetic'], default='legacy_unverified')
    parser.add_argument('--meta-json', type=Path, nargs='*', default=[])
    parser.add_argument('--output', type=Path, required=True)
    args = parser.parse_args()
    rows = legacy_rows(args.legacy, args.legacy_source) if args.legacy else []
    for path in args.meta_json:
        rows.extend(meta_rows(path))
    if not rows:
        parser.error('No hay publicaciones. Usa --legacy o --meta-json.')
    keys = [(r['platform'], r['account_id'], r['post_id']) for r in rows]
    if len(set(keys)) != len(keys):
        raise ValueError('Hay publicaciones repetidas entre las fuentes; selecciona una medición por publicación.')
    write_csv(args.output, rows)
    manifest = {'rows': len(rows), 'platforms': sorted({r['platform'] for r in rows}),
                'source_sha256': hashlib.sha256(args.legacy.read_bytes()).hexdigest() if args.legacy else None,
                'dataset_sha256': hashlib.sha256(args.output.read_bytes()).hexdigest(),
                'notes': ['No se generaron publicaciones artificiales.',
                          'legacy_facebook_unknown es un agrupador provisional, no una cuenta verificada.',
                          'Objetivo V4: likes/reacciones + 2*comentarios; distinto del objetivo V3.',
                          'No usar errores del legado como evidencia de eficacia en cuentas reales.']}
    args.output.with_suffix('.manifest.json').write_text(json.dumps(manifest, ensure_ascii=False, indent=2), encoding='utf-8')
    print(json.dumps(manifest, ensure_ascii=False, indent=2))
