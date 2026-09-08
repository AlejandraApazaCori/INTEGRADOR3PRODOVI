param([switch]$LocalOnly)
$ErrorActionPreference = 'Stop'
$demoRoot = Join-Path $PSScriptRoot '.demo'
$pythonExe = Join-Path $PSScriptRoot '.venv-lstm/Scripts/python.exe'
$sourceDir = Join-Path $PSScriptRoot 'lstm_v4'
if (-not (Test-Path -LiteralPath $pythonExe)) { throw 'Falta el entorno Python. Revisa python/DEMO_API.md.' }
& $pythonExe (Join-Path $sourceDir 'prepare_demo.py')
if ($LASTEXITCODE -ne 0) { throw 'No se pudo preparar el servicio.' }
$statePath = Join-Path $demoRoot 'processes.json'
$state = @{}
if (Test-Path -LiteralPath $statePath) {
    $previous = Get-Content -LiteralPath $statePath -Raw | ConvertFrom-Json
    foreach ($prop in $previous.PSObject.Properties) { $state[$prop.Name] = $prop.Value }
}
$token = (Get-Content -LiteralPath (Join-Path $demoRoot 'api-token.txt') -Raw).Trim()
$headers = @{Authorization = "Bearer $token"}
function Test-ApiReady([string]$BaseUrl = 'http://127.0.0.1:8765') {
    try {
        $response = Invoke-RestMethod "$BaseUrl/health" -Headers $headers -TimeoutSec 5
        return ($response.platforms.facebook.verified_load -and $response.platforms.instagram.verified_load)
    } catch { return $false }
}
if (-not (Test-ApiReady)) {
    $stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
    $apiProcess = Start-Process -FilePath $pythonExe -ArgumentList @('-m', 'uvicorn', 'api:app', '--host', '127.0.0.1', '--port', '8765', '--workers', '1', '--no-access-log') -WorkingDirectory $sourceDir -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $demoRoot "api-$stamp.out.log") -RedirectStandardError (Join-Path $demoRoot "api-$stamp.err.log")
    $state['api'] = @{pid = $apiProcess.Id; started = $apiProcess.StartTime.ToUniversalTime().ToString('o')}
    $state | ConvertTo-Json | Set-Content -LiteralPath $statePath -Encoding UTF8
    Write-Host 'Cargando y comprobando los modelos...'
    $ready = $false
    for ($attempt = 0; $attempt -lt 45; $attempt++) {
        if (Test-ApiReady) { $ready = $true; break }
        if ($apiProcess.HasExited) { throw "La API no inició. Revisa python/.demo/api-$stamp.err.log." }
        Start-Sleep -Seconds 1
    }
    if (-not $ready) { throw 'La API tardó demasiado en iniciar. Revisa los logs de python/.demo.' }
}
Write-Host 'API local lista en 127.0.0.1:8765 (requiere clave).'
if ($LocalOnly) { exit 0 }
# Reutilizar el túnel activo conserva la URL durante la demostración.
if (Test-Path -LiteralPath (Join-Path $demoRoot 'tunnel-url.txt')) {
    $oldUrl = (Get-Content -LiteralPath (Join-Path $demoRoot 'tunnel-url.txt') -Raw).Trim()
    try {
        if (Test-ApiReady $oldUrl) {
            & $pythonExe (Join-Path $sourceDir 'export_demo_config.py') $oldUrl --sync-env
            Write-Host 'IMPORTANTE: copia python/.demo/production.env al .env del hosting y limpia la caché de Laravel.' -ForegroundColor Yellow
            exit $LASTEXITCODE
        }
    } catch { }
}
# No arrancar un segundo conector si el anterior sigue vivo pero temporalmente sin conexión.
if ($state.ContainsKey('tunnel')) {
    $existing = Get-Process -Id $state['tunnel'].pid -ErrorAction SilentlyContinue
    if ($existing -and $existing.StartTime.ToUniversalTime().ToString('o') -eq $state['tunnel'].started) {
        throw 'El túnel anterior sigue en ejecución. Espera o usa stop_demo.ps1 y vuelve a iniciar.'
    }
}
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$tunnelLog = Join-Path $demoRoot "tunnel-$stamp.err.log"
$tunnelProcess = Start-Process -FilePath (Join-Path $demoRoot 'cloudflared.exe') -ArgumentList @('tunnel', '--no-autoupdate', '--protocol', 'http2', '--url', 'http://127.0.0.1:8765') -WorkingDirectory $demoRoot -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $demoRoot "tunnel-$stamp.out.log") -RedirectStandardError $tunnelLog
$state['tunnel'] = @{pid = $tunnelProcess.Id; started = $tunnelProcess.StartTime.ToUniversalTime().ToString('o')}
$state | ConvertTo-Json | Set-Content -LiteralPath $statePath -Encoding UTF8
Write-Host 'Abriendo túnel HTTPS...'
$url = $null
for ($attempt = 0; $attempt -lt 45; $attempt++) {
    if (Test-Path -LiteralPath $tunnelLog) {
        $content = Get-Content -LiteralPath $tunnelLog -Raw
        if ($content -match 'https://[a-z0-9-]+\.trycloudflare\.com') { $url = $Matches[0]; break }
    }
    if ($tunnelProcess.HasExited) { throw "El túnel no inició. Revisa $tunnelLog." }
    Start-Sleep -Seconds 1
}
if (-not $url) { throw "No se obtuvo la URL. Revisa $tunnelLog." }
$remoteReady = $false
for ($attempt = 0; $attempt -lt 15; $attempt++) {
    if (Test-ApiReady $url) { $remoteReady = $true; break }
    if ($tunnelProcess.HasExited) { throw "El túnel se cerró. Revisa $tunnelLog." }
    Start-Sleep -Seconds 1
}
if (-not $remoteReady) { throw "El túnel inició pero la API no responde por HTTPS. Revisa $tunnelLog." }
& $pythonExe (Join-Path $sourceDir 'export_demo_config.py') $url --sync-env
if ($LASTEXITCODE -ne 0) { throw 'No se pudo exportar la configuración del hosting.' }
Write-Host 'IMPORTANTE: copia python/.demo/production.env al .env del hosting y limpia la caché de Laravel.' -ForegroundColor Yellow
Write-Host 'La API y el túnel quedan en segundo plano. Para detenerlos: powershell -File python/stop_demo.ps1'
