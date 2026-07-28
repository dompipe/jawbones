$ErrorActionPreference = 'Stop'

$phpBinary = 'C:\Users\g0d77\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.5_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe'
if (-not (Test-Path -LiteralPath $phpBinary)) {
    $phpBinary = 'php'
}

$baseDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$cronPhpPath = Join-Path $baseDir 'wsl_portfolio_cron.php'
$registryPath = Join-Path $baseDir 'wsl_portfolio_targets.json'
$statusPath = Join-Path $baseDir 'wsl_portfolio_status.json'
$logPath = Join-Path $baseDir 'windows_portfolio_scheduler.log'

$arguments = @(
    $cronPhpPath
    "--registry=$registryPath"
    "--status=$statusPath"
)

$output = & $phpBinary @arguments 2>&1
$timestamp = Get-Date -Format 'yyyy-MM-ddTHH:mm:ssK'
$logLines = @(
    "[$timestamp] php runner start"
    ($output | Out-String).TrimEnd()
    "[$timestamp] php runner end"
    ""
)
Add-Content -LiteralPath $logPath -Value $logLines
