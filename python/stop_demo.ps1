$ErrorActionPreference = 'Stop'
$statePath = Join-Path $PSScriptRoot '.demo/processes.json'
if (-not (Test-Path -LiteralPath $statePath)) { Write-Host 'No hay procesos registrados.'; exit 0 }
$state = Get-Content -LiteralPath $statePath -Raw | ConvertFrom-Json
foreach ($name in @('tunnel', 'api')) {
    $record = $state.$name
    if (-not $record) { continue }
    $process = Get-Process -Id $record.pid -ErrorAction SilentlyContinue
    # No detener otro proceso si Windows reutilizó el PID.
    if ($process -and $process.StartTime.ToUniversalTime().ToString('o') -eq $record.started) {
        Stop-Process -InputObject $process
        Write-Host "$name detenido."
    }
}
