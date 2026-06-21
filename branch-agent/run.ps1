$PhpExe   = "D:\xampp\php\php.exe"
$PhpIni   = "D:\xampp\php\php.ini"
$Agent    = "D:\branch-agent\agent.php"
$LogFile  = "D:\branch-agent\agent.log"
$Timeout  = 120  # ثانية

$proc = Start-Process -FilePath $PhpExe `
    -ArgumentList "-c `"$PhpIni`" `"$Agent`"" `
    -WorkingDirectory "D:\branch-agent" `
    -PassThru -NoNewWindow

if (-not $proc.WaitForExit($Timeout * 1000)) {
    $proc.Kill()
    $msg = "[" + (Get-Date -Format "yyyy-MM-dd HH:mm:ss") + "] [TIMEOUT] Agent killed after $Timeout seconds"
    Add-Content -Path $LogFile -Value $msg -Encoding UTF8
    exit 1
}

exit $proc.ExitCode
