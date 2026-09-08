# Demostración: hosting Laravel + Python en tu computadora

## Iniciar

Ejecuta este comando **en la computadora Windows que tiene Python y los modelos**, no en el hosting:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File python/start_demo.ps1
```

Un hosting Laravel/PHP convencional no puede ejecutar este comando PowerShell. El script arranca la API
en `127.0.0.1:8765`, carga y verifica los dos modelos, abre un túnel HTTPS y comprueba que también responda
desde la dirección pública.
Los procesos quedan en segundo plano, sin ventanas adicionales. La clave permanece en
`python/.demo/api-token.txt`, fuera de Git. El túnel permite acceder únicamente a la API de inferencia,
no al proyecto Laravel ni al sistema de archivos.

Al terminar verás la URL pública y estas rutas:

- `python/.demo/production.env`: variables y clave para copiar al hosting.
- `python/.demo/lstm-hosting-update.zip`: tres archivos PHP actualizados, sin secretos.
- `python/.demo/tunnel-url.txt`: dirección pública actual.

El iniciador actualiza automáticamente las variables LSTM del `.env` **de esta computadora**. No puede
editar el `.env` privado de un servidor remoto: cada vez que cambie la URL hay que copiar las cinco líneas
de `production.env` al hosting y limpiar su caché de configuración.

En otro equipo instala primero Python 3.13, crea `python/.venv-lstm` e instala
`python/lstm_v4/requirements-api.txt`. Los modelos deben estar en `python/modelos/meta_v4_20260905`.
El script descarga cloudflared desde el repositorio oficial y verifica su SHA-256.

## Actualizar el hosting, sin Python ni cron

### Si despliegas mediante GitHub

Los cambios ya están aplicados directamente en `app/Services/LstmInferenceService.php`,
`app/Services/PublicationTimingService.php` y `config/lstm.php`. Estos tres archivos están
versionados por Git: inclúyelos en tu commit y despliega normalmente. No necesitas subir ni
extraer `lstm-hosting-update.zip`; es solo una alternativa para la carga manual.

La carpeta `python/.demo/` permanece excluida de Git porque contiene la clave privada.
Después del despliegue, actualiza tú el `.env` del hosting y limpia la caché de configuración,
como se indica en los pasos 2 y 3 siguientes. El `.env` no se despliega mediante Git.

### Si subes los archivos manualmente

1. Sube y extrae `lstm-hosting-update.zip` en la **raíz de Laravel**, donde están `artisan`, `app` y
   `config`. Reemplaza solamente los tres archivos incluidos. No lo extraigas dentro de `public`.
2. Abre `production.env` en tu computadora y copia sus cinco variables al `.env` del hosting.
   Reemplaza cualquier variable LSTM del mismo nombre; evita líneas duplicadas.
   No reemplaces el `.env` completo: conserva base de datos, APP_KEY y las demás credenciales del servidor.
3. Si hay terminal, ejecuta `php artisan config:clear`. Sin terminal, usa el administrador de archivos
   para eliminar **solo `bootstrap/cache/config.php` si existe**; Laravel volverá a leer el `.env`.
   No borres otros archivos ni directorios.
4. Abre tu publicación en producción y activa «Optimizar tiempo de publicación».
   Laravel consultará la API HTTPS con su clave, enviando solo los campos numéricos, identificadores
   y fechas del histórico. La gráfica existente mostrará las predicciones devueltas.

El paquete contiene:

```text
app/Services/LstmInferenceService.php
app/Services/PublicationTimingService.php
config/lstm.php
```

No hay migraciones adicionales para cambiar de ejecución local a API. El servicio no recibe los
tokens Meta y no guarda ni registra los cuerpos de las peticiones. No habilita CORS: Laravel lo
consulta desde el servidor, no desde el navegador. Se autentican incluso las consultas de salud.

Si tienes terminal en el hosting, `php artisan lstm:check` también funciona en modo HTTP.
Si no, el formulario y los logs muestran errores diferenciados de clave, túnel o formato.

## Durante la defensa

- Mantén la computadora encendida, conectada a internet y sin suspensión.
- El túnel es temporal. Si se reinicia y cambia la URL, vuelve a copiar `LSTM_API_URL` al hosting y
  limpia la caché de configuración. El script reutiliza un túnel que todavía esté activo.
- Conserva la clave solo en tu equipo y el `.env` privado del hosting; no la pegues en capturas,
  archivos públicos ni en el repositorio.
- La API acepta un cálculo a la vez. Si devuelve «ocupada», espera y vuelve a consultar.
- No hace falta cron para consultar la predicción al abrir el apartado. Esta API no ejecuta las
  publicaciones programadas ni la recopilación periódica: esos procesos siguen siendo independientes.
- Los modelos conservan su procedencia de simulación y su estado experimental.

## Detener

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File python/stop_demo.ps1
```

Solo detiene los procesos registrados por el iniciador, verificando PID y hora de inicio. Al apagarlos,
la gráfica del hosting mostrará que la API no está disponible, sin inventar horarios de respaldo.
Para volver a la ejecución Python dentro de un servidor que sí la admita, configura `LSTM_DRIVER=local`.

## Diagnóstico

- **401/403:** clave incorrecta o `.env` en caché.
- **404/502/530 o conexión fallida:** túnel cerrado, URL antigua o computadora desconectada.
- **422:** histórico/candidatos incompatibles; consulta los logs Laravel sin exponer el cuerpo del histórico.
- **429/503:** servicio ocupado; vuelve a intentar después.
- **La API local no inicia:** revisa `python/.demo/api-*.err.log`.
- **El túnel no inicia:** revisa `python/.demo/tunnel-*.err.log`.
- **PHP no puede verificar el certificado (cURL 60):** actualiza los certificados CA del hosting,
  o configura `LSTM_CA_BUNDLE` con la ruta absoluta a un paquete CA válido. No desactives HTTPS.
  En este equipo Windows se puede usar el paquete instalado en
  `python/.venv-lstm/Lib/site-packages/certifi/cacert.pem`; esa ruta local no sirve para el hosting.

Cloudflare ofrece [Quick Tunnels](https://developers.cloudflare.com/cloudflare-one/networks/connectors/cloudflare-tunnel/do-more-with-tunnels/trycloudflare/)
con direcciones aleatorias para pruebas. No son un servicio con disponibilidad garantizada; encajan
con esta demostración breve. La autenticación usa [Bearer en HTTP](https://fastapi.tiangolo.com/tutorial/security/).
