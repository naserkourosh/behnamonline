# Start the Behnam storefront on PHP's built-in server (quick local run).
# Usage:  powershell -ExecutionPolicy Bypass -File tools\serve.ps1  [-Port 8000]
param([int]$Port = 8000)

$root = Split-Path -Parent $PSScriptRoot

# Prefer Laragon's PHP 8.3; fall back to any php on PATH.
$php = Get-ChildItem "C:\laragon\bin\php\php-8.3*\php.exe" -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
if (-not $php) { $php = (Get-Command php -ErrorAction SilentlyContinue).Source }
if (-not $php) { Write-Error "PHP not found. Install PHP 8.3 or use Laragon."; exit 1 }

Write-Host "→ Behnam running at http://127.0.0.1:$Port  (Ctrl+C to stop)" -ForegroundColor Green
Set-Location $root
& $php -S "127.0.0.1:$Port" -t public tools/dev-router.php
