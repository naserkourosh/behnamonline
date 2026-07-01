# Compile Tailwind CSS to a single minified file.
# Usage:  powershell -ExecutionPolicy Bypass -File tools\build.ps1   [-Watch]
param([switch]$Watch)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

$cli   = Join-Path $root "tools\tailwindcss.exe"
$config = Join-Path $root "resources\tailwind\tailwind.config.js"
$input  = Join-Path $root "resources\tailwind\input.css"
$output = Join-Path $root "public\assets\css\app.css"

if (-not (Test-Path $cli)) {
    Write-Error "Tailwind CLI not found at $cli. Download it from https://github.com/tailwindlabs/tailwindcss/releases (tailwindcss-windows-x64.exe) into tools\tailwindcss.exe"
    exit 1
}

$args = @("-c", $config, "-i", $input, "-o", $output)
if ($Watch) { $args += "--watch" } else { $args += "--minify" }

Write-Host "→ Building $output ..." -ForegroundColor Cyan
& $cli @args
