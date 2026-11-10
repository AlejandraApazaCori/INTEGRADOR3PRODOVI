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
function Get-TunnelUrl([string]$LogPath) {
    if (-not $LogPath -or -not (Test-Path -LiteralPath $LogPath)) { return $null }
    $content = Get-Content -LiteralPath $LogPath -Raw
    if ($content -match 'https://[a-z0-9-]+\.trycloudflare\.com') { return $Matches[0] }
    return $null
}
function Wait-TunnelReady([string]$Url, $Process) {
    for ($attempt = 0; $attempt -lt 45; $attempt++) {
        if (Test-ApiReady $Url) { return $true }
        if ($Process.HasExited) { return $false }
        Start-Sleep -Seconds 2
    }
    return $false
}
function Show-DemoConfig([string]$Url) {
    & $pythonExe (Join-Path $sourceDir 'export_demo_config.py') $Url --sync-env
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo exportar la configuración del hosting.' }
    Write-Host "`nCopia estas variables al .env del hosting (mantén privada la clave):" -ForegroundColor Green
    Get-Content -LiteralPath (Join-Path $demoRoot 'production.env')
    Write-Host "`nDespués limpia la caché de configuración de Laravel en el hosting: php artisan config:clear" -ForegroundColor Yellow
    Write-Host 'La API y el túnel quedan en segundo plano. Para detenerlos: powershell -File python/stop_demo.ps1'
}
# Un túnel anterior puede estar activo aunque la URL guardada ya sea de otra sesión.
if ($state.ContainsKey('tunnel')) {
    $existing = Get-Process -Id $state['tunnel'].pid -ErrorAction SilentlyContinue
    if ($existing -and $existing.StartTime.ToUniversalTime().ToString('o') -eq $state['tunnel'].started) {
        $existingLog = $state['tunnel'].log
        if (-not $existingLog) {
            # Compatibilidad con processes.json creado antes de guardar la ruta del log.
            $started = [datetime]::Parse($state['tunnel'].started).ToLocalTime()
            $existingLog = Get-ChildItem -LiteralPath $demoRoot -Filter 'tunnel-*.err.log' |
                Where-Object { $_.CreationTime -ge $started.AddSeconds(-5) -and $_.CreationTime -le $started.AddSeconds(15) } |
                Sort-Object CreationTime -Descending | Select-Object -First 1 -ExpandProperty FullName
        }
        $existingUrl = Get-TunnelUrl $existingLog
        if (-not $existingUrl) { $existingUrl = $state['tunnel'].url }
        if ($existingUrl -and (Wait-TunnelReady $existingUrl $existing)) {
            $state['tunnel'] = @{pid = $existing.Id; started = $state['tunnel'].started; log = $existingLog; url = $existingUrl}
            $state | ConvertTo-Json | Set-Content -LiteralPath $statePath -Encoding UTF8
            Show-DemoConfig $existingUrl
            exit 0
        }
        throw 'El túnel sigue en ejecución, pero su URL no responde. Revisa python/.demo/tunnel-*.err.log o ejecuta python/stop_demo.ps1 antes de reiniciar.'
    }
}
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$tunnelLog = Join-Path $demoRoot "tunnel-$stamp.err.log"
$tunnelProcess = Start-Process -FilePath (Join-Path $demoRoot 'cloudflared.exe') -ArgumentList @('tunnel', '--no-autoupdate', '--protocol', 'http2', '--url', 'http://127.0.0.1:8765') -WorkingDirectory $demoRoot -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $demoRoot "tunnel-$stamp.out.log") -RedirectStandardError $tunnelLog
$state['tunnel'] = @{pid = $tunnelProcess.Id; started = $tunnelProcess.StartTime.ToUniversalTime().ToString('o'); log = $tunnelLog}
$state | ConvertTo-Json | Set-Content -LiteralPath $statePath -Encoding UTF8
Write-Host 'Abriendo túnel HTTPS...'
$url = $null
for ($attempt = 0; $attempt -lt 45; $attempt++) {
    $url = Get-TunnelUrl $tunnelLog
    if ($url) { break }
    if ($tunnelProcess.HasExited) { throw "El túnel no inició. Revisa $tunnelLog." }
    Start-Sleep -Seconds 1
}
if (-not $url) { throw "No se obtuvo la URL. Revisa $tunnelLog." }
if (-not (Wait-TunnelReady $url $tunnelProcess)) { throw "El túnel inició pero la API no responde por HTTPS. URL: $url. Revisa $tunnelLog." }
$state['tunnel'].url = $url
$state | ConvertTo-Json | Set-Content -LiteralPath $statePath -Encoding UTF8
Show-DemoConfig $url
