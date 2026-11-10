@echo off
setlocal
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0python\start_demo.ps1"
exit /b %ERRORLEVEL%
