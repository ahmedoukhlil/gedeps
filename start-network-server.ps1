# GEDEPS - Serveur Reseau Local
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   GEDEPS - Serveur Reseau Local" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Obtenir l'adresse IP locale
$ipAddress = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.IPAddress -like "192.168.*" -or $_.IPAddress -like "10.*" -or $_.IPAddress -like "172.*"} | Select-Object -First 1).IPAddress

Write-Host "Demarrage du serveur Laravel..." -ForegroundColor Green
Write-Host "Le serveur sera accessible sur le reseau local" -ForegroundColor Green
Write-Host ""
Write-Host "URLs d'acces:" -ForegroundColor Yellow
Write-Host "- Local: http://localhost:8000" -ForegroundColor White
if ($ipAddress) {
    Write-Host "- Reseau: http://$ipAddress:8000" -ForegroundColor White
} else {
    Write-Host "- Reseau: http://[VOTRE_IP]:8000" -ForegroundColor White
}
Write-Host ""
Write-Host "Pour trouver votre adresse IP, utilisez: Get-NetIPAddress" -ForegroundColor Gray
Write-Host ""
Write-Host "Appuyez sur Ctrl+C pour arreter le serveur" -ForegroundColor Red
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Démarrer le serveur
php artisan serve --host=0.0.0.0 --port=8000
