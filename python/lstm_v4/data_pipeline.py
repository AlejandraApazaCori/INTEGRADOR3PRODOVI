"""Contrato compartido de entrenamiento e inferencia. No consulta Meta ni inventa métricas."""
import numpy as np
import pandas as pd

VERSION = 'meta_lstm_hybrid_v4.0.0'
TIMEZONE = 'America/La_Paz'
COLUMNS = ['platform', 'account_id', 'post_id', 'published_at', 'likes', 'comments',
           'metrics_observed_at', 'source', 'measurement_protocol']
HISTORY_FEATURES = ['likes_log', 'comments_log', 'score_log', 'hour_sin',
                    'hour_cos', 'day_sin', 'day_cos', 'gap_log']
CANDIDATE_FEATURES = ['hour_sin', 'hour_cos', 'day_sin', 'day_cos', 'gap_log',
                      'account_prior_log', 'slot_samples_log']


def read_dataset(path):
    df = pd.read_csv(path, dtype=str, keep_default_na=False)
    missing = set(COLUMNS) - set(df.columns)
    if missing:
        raise ValueError(f'Faltan columnas: {sorted(missing)}')
    if df.empty:
        raise ValueError('El CSV no contiene publicaciones.')
    for field in ['platform', 'account_id', 'post_id', 'published_at', 'source', 'measurement_protocol']:
        df[field] = df[field].str.strip()
        if df[field].eq('').any():
            raise ValueError(f'Hay valores vacíos en {field}.')
    if not df.platform.isin(['facebook', 'instagram']).all():
        raise ValueError('platform debe ser facebook o instagram.')
    if not df.source.isin(['legacy_unverified', 'meta_export', 'synthetic']).all():
        raise ValueError('source debe ser legacy_unverified, meta_export o synthetic.')
    if df.duplicated(['platform', 'account_id', 'post_id']).any():
        raise ValueError('Publicaciones duplicadas. Selecciona una medición por publicación.')
    for field in ['likes', 'comments']:
        df[field] = pd.to_numeric(df[field], errors='raise')
        if not (np.isfinite(df[field]) & df[field].ge(0) & df[field].mod(1).eq(0)).all():
            raise ValueError(f'{field} debe contener conteos enteros no negativos, nunca valores faltantes.')
    # No interpretar fechas sin offset como UTC accidentalmente.
    for field in ['published_at', 'metrics_observed_at']:
        nonempty = df[field].ne('')
        if not df.loc[nonempty, field].str.contains(r'(?:Z|[+-]\d{2}:\d{2})$', regex=True).all():
            raise ValueError(f'{field} debe incluir zona horaria, por ejemplo -04:00.')
        df[field] = pd.to_datetime(df[field].replace('', None), utc=True, errors='raise', format='mixed')
    if (df.metrics_observed_at < df.published_at).any():
        raise ValueError('Una medición no puede preceder a la publicación.')
    if (df.source.eq('meta_export') & df.metrics_observed_at.isna()).any():
        raise ValueError('Las exportaciones Meta necesitan metrics_observed_at.')
    df['score'] = df.likes + 2 * df.comments
    df['score_log'] = np.log1p(df.score)
    # Solo para legado exploratorio: no se conoce cuándo se midieron las métricas.
    df['available_at'] = df.metrics_observed_at.fillna(df.published_at)
    return df.sort_values(['published_at', 'platform', 'account_id', 'post_id']).reset_index(drop=True)


def calendar(timestamp):
    local = pd.Timestamp(timestamp).tz_convert(TIMEZONE)
    return [np.sin(2*np.pi*local.hour/24), np.cos(2*np.pi*local.hour/24),
            np.sin(2*np.pi*local.dayofweek/7), np.cos(2*np.pi*local.dayofweek/7)]


def slot_prior(history, timestamp, strength=3.0):
    """Referencia exclusiva de esta cuenta, calculada con datos disponibles antes del candidato."""
    local = history.published_at.dt.tz_convert(TIMEZONE)
    candidate = pd.Timestamp(timestamp).tz_convert(TIMEZONE)
    slot = history[(local.dt.dayofweek == candidate.dayofweek) & (local.dt.hour == candidate.hour)]
    average = float(history.score_log.mean())
    prior = (float(slot.score_log.sum()) + strength * average) / (len(slot) + strength)
    return prior, len(slot)


def make_inputs(history, timestamp, window, strength=3.0):
    timestamp = pd.Timestamp(timestamp)
    if timestamp.tzinfo is None:
        raise ValueError('El candidato necesita zona horaria.')
    if history[['platform', 'account_id']].drop_duplicates().shape[0] != 1:
        raise ValueError('La secuencia debe pertenecer a una sola cuenta y plataforma.')
    history = history[(history.published_at < timestamp) & (history.available_at < timestamp)]
    history = history.sort_values(['published_at', 'post_id'])
    if len(history) < window:
        raise ValueError(f'Histórico insuficiente: requiere {window} publicaciones medidas antes del candidato.')
    prior, samples = slot_prior(history, timestamp, strength)
    dates = history.published_at
    gaps = dates.diff().dt.total_seconds().div(3600).clip(0, 720).fillna(24).to_numpy()
    matrix = [[np.log1p(row.likes), np.log1p(row.comments), row.score_log,
               *calendar(row.published_at), np.log1p(gap)]
              for row, gap in zip(history.tail(window).itertuples(), gaps[-window:])]
    gap = min(max((timestamp - dates.iloc[-1]).total_seconds()/3600, 0), 720)
    candidate = [*calendar(timestamp), np.log1p(gap), prior, np.log1p(samples)]
    return np.asarray(matrix[-window:], dtype='float32'), np.asarray(candidate, dtype='float32'), prior, samples


def build_examples(frame, window, strength=3.0):
    histories, candidates, targets, records = [], [], [], []
    for (platform, account), group in frame.groupby(['platform', 'account_id'], sort=False):
        for row in group.itertuples():
            eligible = group[(group.published_at < row.published_at) & (group.available_at < row.published_at)]
            if len(eligible) < window:
                continue
            h, c, prior, count = make_inputs(eligible, row.published_at, window, strength)
            histories.append(h)
            candidates.append(c)
            targets.append(row.score_log - prior)
            records.append(dict(platform=platform, account_id=account, post_id=row.post_id,
                                published_at=row.published_at, available_at=row.available_at,
                                actual=float(row.score), prior_log=prior, samples=count,
                                global_log=float(eligible.score_log.mean())))
    return (np.asarray(histories, dtype='float32'), np.asarray(candidates, dtype='float32'),
            np.asarray(targets, dtype='float32').reshape(-1, 1), pd.DataFrame(records))


def fit_scaler(values):
    values = np.asarray(values)
    return {'mean': values.mean(axis=0).tolist(), 'scale': np.where(values.std(axis=0) < 1e-8, 1, values.std(axis=0)).tolist()}


def transform(values, scaler):
    return ((np.asarray(values) - np.asarray(scaler['mean'])) / np.asarray(scaler['scale'])).astype('float32')


def invert(values, scaler):
    return np.asarray(values) * np.asarray(scaler['scale']) + np.asarray(scaler['mean'])


def combine(prior, residual, alpha):
    # Evita overflow; el techo técnico no representa garantía de rendimiento.
    return np.expm1(np.clip(np.asarray(prior) + alpha*np.asarray(residual), 0, 20))
