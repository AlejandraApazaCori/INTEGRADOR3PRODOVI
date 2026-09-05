"""API de demostración: solo inferencia, autenticada y sin almacenar peticiones."""
import asyncio
from contextlib import asynccontextmanager
import logging
import os
from pathlib import Path
import secrets
from typing import Literal

from fastapi import FastAPI, HTTPException, Request
from pydantic import BaseModel, ConfigDict, Field, ValidationError, model_validator
from starlette.concurrency import run_in_threadpool

from runtime import execute

MAX_BODY = 8 * 1024 * 1024
ROOT = Path(__file__).resolve().parents[1]


class Post(BaseModel):
    model_config = ConfigDict(extra='forbid')
    platform: Literal['facebook', 'instagram']
    account_id: str = Field(min_length=1, max_length=100)
    post_id: str = Field(min_length=1, max_length=100)
    published_at: str = Field(max_length=60)
    likes: int = Field(ge=0, le=10**12, strict=True)
    comments: int = Field(ge=0, le=10**12, strict=True)
    metrics_observed_at: str = Field(max_length=60)
    source: Literal['meta_export', 'synthetic', 'legacy_unverified']
    measurement_protocol: str = Field(max_length=100)


class Account(BaseModel):
    model_config = ConfigDict(extra='forbid')
    platform: Literal['facebook', 'instagram']
    account_id: str | None = Field(default=None, min_length=1, max_length=100)
    posts: list[Post] = Field(default_factory=list, max_length=10000)


class PredictionRequest(BaseModel):
    model_config = ConfigDict(extra='forbid')
    health: bool = False
    accounts: list[Account] = Field(min_length=1, max_length=2)
    candidates: list[str] = Field(default_factory=list, max_length=336)

    @model_validator(mode='after')
    def validate_scope(self):
        if len({a.platform for a in self.accounts}) != len(self.accounts):
            raise ValueError('Una cuenta por red en cada solicitud')
        if not self.health:
            if not self.candidates or any(len(t) > 60 for t in self.candidates):
                raise ValueError('Faltan fechas candidatas válidas')
            for account in self.accounts:
                if not account.account_id:
                    raise ValueError('Falta account_id')
                if any(p.account_id != account.account_id or p.platform != account.platform for p in account.posts):
                    raise ValueError('Histórico ajeno a la cuenta')
        return self


def read_token():
    value = os.environ.get('LSTM_API_TOKEN', '')
    path = Path(os.environ.get('LSTM_API_TOKEN_FILE', str(ROOT / '.demo' / 'api-token.txt')))
    if not value and path.is_file():
        value = path.read_text(encoding='utf-8').strip()
    if len(value) < 32 or not value.isascii():
        raise RuntimeError('Falta una clave de API válida. Ejecuta start_demo.ps1.')
    return value


@asynccontextmanager
async def lifespan(app):
    app.state.token = read_token()
    app.state.models = Path(os.environ.get('LSTM_MODELS_PATH', str(ROOT / 'modelos' / 'meta_v4_20260905')))
    app.state.lock = asyncio.Lock()
    # Cargar una sola vez y verificar ambos artefactos antes de aceptar conexiones.
    app.state.health = await run_in_threadpool(execute, {
        'health': True, 'accounts': [{'platform': 'facebook'}, {'platform': 'instagram'}]
    }, app.state.models)
    yield


app = FastAPI(lifespan=lifespan, docs_url=None, redoc_url=None, openapi_url=None)


def authenticate(request):
    supplied = request.headers.get('authorization', '')
    if not secrets.compare_digest(supplied.encode(), ('Bearer ' + request.app.state.token).encode()):
        raise HTTPException(401, 'Acceso no autorizado', headers={'WWW-Authenticate': 'Bearer'})


@app.get('/health')
async def health(request: Request):
    authenticate(request)
    return request.app.state.health


@app.post('/v1/predict')
async def predict(request: Request):
    authenticate(request)
    if request.app.state.lock.locked():
        raise HTTPException(429, 'Cálculo en curso; vuelve a intentarlo', headers={'Retry-After': '5'})
    # Limitar antes de parsear JSON; también funciona con transferencia chunked.
    body = bytearray()
    async for chunk in request.stream():
        body.extend(chunk)
        if len(body) > MAX_BODY:
            raise HTTPException(413, 'Histórico demasiado grande')
    try:
        payload = PredictionRequest.model_validate_json(bytes(body))
    except (ValidationError, ValueError):
        # No devolver ni registrar valores del histórico en los mensajes de error.
        raise HTTPException(422, 'Formato de solicitud inválido') from None
    if payload.health:
        return {'platforms': {a.platform: request.app.state.health['platforms'][a.platform] for a in payload.accounts}}
    if request.app.state.lock.locked():
        raise HTTPException(429, 'Cálculo en curso; vuelve a intentarlo')
    async with request.app.state.lock:
        try:
            result = await run_in_threadpool(execute, payload.model_dump(), request.app.state.models)
            return result
        except (ValueError, KeyError, TypeError):
            raise HTTPException(422, 'El histórico o los horarios no son válidos') from None
        except Exception:
            logging.error('LSTM inference failed; request data omitted')
            raise HTTPException(500, 'No se pudo ejecutar el modelo') from None
