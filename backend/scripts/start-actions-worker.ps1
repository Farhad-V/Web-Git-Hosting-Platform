param([string]$PhpBinary = 'php')

$ErrorActionPreference = 'Stop'
$backendRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$artisanPath = Join-Path $backendRoot 'artisan'
$phpPath = (Get-Command $PhpBinary -ErrorAction Stop).Source
$pidFile = Join-Path $backendRoot 'storage\app\actions-worker.pid'

if (Test-Path -LiteralPath $pidFile) {
    $workerId = 0
    if ([int]::TryParse((Get-Content -LiteralPath $pidFile -Raw).Trim(), [ref]$workerId)) {
        $existingWorker = Get-CimInstance Win32_Process -Filter "ProcessId = $workerId"
        if ($existingWorker -and $existingWorker.ExecutablePath -eq $phpPath -and $existingWorker.CommandLine -and $existingWorker.CommandLine.IndexOf($artisanPath, [StringComparison]::OrdinalIgnoreCase) -ge 0 -and $existingWorker.CommandLine.Contains('--queue=gitforge-actions')) {
            Write-Output "Actions worker already running (PID $workerId)."
            exit 0
        }
    }
}

$outputPath = Join-Path $backendRoot 'storage\logs\actions-worker.log'
$errorPath = Join-Path $backendRoot 'storage\logs\actions-worker-error.log'
$worker = Start-Process -FilePath $phpPath -ArgumentList @(('"' + $artisanPath + '"'), 'queue:work', '--queue=gitforge-actions', '--tries=1', '--timeout=60', '--sleep=1') -WorkingDirectory $backendRoot -WindowStyle Hidden -RedirectStandardOutput $outputPath -RedirectStandardError $errorPath -PassThru
Set-Content -LiteralPath $pidFile -Value $worker.Id
Start-Sleep -Milliseconds 500
if (-not (Get-Process -Id $worker.Id -ErrorAction SilentlyContinue)) {
    throw 'The Actions worker exited. Check storage/logs/actions-worker-error.log.'
}
Write-Output "Actions worker running (PID $($worker.Id))."
