<#
Usage: run from project root in PowerShell:
  .\tools\set-mailhog.ps1

This script copies .env.example to .env (if missing) and writes MailHog SMTP settings.
It will also try to start `mailhog` if it's in PATH (Chocolatey install or binary available).
#>

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Definition
Set-Location $projectRoot

$example = Join-Path $projectRoot '.env.example'
$envFile = Join-Path $projectRoot '.env'

if (Test-Path $envFile) {
    Write-Host ".env already exists — backing up to .env.bak"
    Copy-Item $envFile "$envFile.bak" -Force
}

if (Test-Path $example) {
    Copy-Item $example $envFile -Force
}

$content = @(
    'APP_BASE_URL=http://localhost/php-email-auth'
    'APP_ENV=development'
    ''
    'SMTP_HOST=localhost'
    'SMTP_PORT=1025'
    'SMTP_USER='
    'SMTP_PASS='
    'SMTP_AUTH=false'
    'MAIL_FROM=no-reply@example.com'
    'MAIL_FROM_NAME="Auth System"'
    ''
    'MAIL_DEBUG=1'
)

Set-Content -Path $envFile -Value $content -Encoding UTF8
Write-Host "Wrote MailHog settings to .env"

# Try to start MailHog if available
try {
    $mailhogExe = Get-Command mailhog -ErrorAction SilentlyContinue
    if ($mailhogExe) {
        Start-Process -FilePath $mailhogExe.Source -WindowStyle Minimized
        Write-Host "Started MailHog via command 'mailhog'. UI: http://localhost:8025"
    } else {
        Write-Host "MailHog not found in PATH. Install via 'choco install mailhog' or download binary."
        Write-Host "After installing, run 'mailhog' and open http://localhost:8025"
    }
} catch {
    Write-Host "Could not start MailHog automatically: $_"
}

Write-Host "Done. Restart Apache if necessary and run registration to capture email in MailHog."
