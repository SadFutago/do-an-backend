while ($true) {
    & "C:\xampp\php\php.exe" artisan schedule:run | Out-File -FilePath "C:\Users\DELL\Documents\do-an\server\storage\logs\scheduler.log" -Append
    Start-Sleep -Seconds 60
}