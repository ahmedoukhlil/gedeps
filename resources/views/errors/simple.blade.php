<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur - Signatures Séquentielles</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error-icon { font-size: 48px; color: #dc3545; text-align: center; margin-bottom: 20px; }
        .error-title { font-size: 24px; color: #dc3545; text-align: center; margin-bottom: 20px; }
        .error-message { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .actions { text-align: center; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background-color: #0056b3; }
        .btn-secondary { background-color: #6c757d; }
        .btn-secondary:hover { background-color: #545b62; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Erreur dans le Système de Signatures</h1>
        
        <div class="error-message">
            <strong>Message d'erreur :</strong><br>
            {{ $message ?? 'Une erreur inattendue s\'est produite.' }}
        </div>
        
        <div class="actions">
            <a href="/signatures-simple" class="btn">🔄 Réessayer</a>
            <a href="/" class="btn btn-secondary">🏠 Retour à l'accueil</a>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background-color: #d1ecf1; border-radius: 5px;">
            <h3>🔧 Solutions possibles :</h3>
            <ul>
                <li>Vérifiez que vous êtes connecté avec un compte signataire</li>
                <li>Assurez-vous qu'il y a des documents avec signatures séquentielles</li>
                <li>Contactez l'administrateur si le problème persiste</li>
            </ul>
        </div>
    </div>
</body>
</html>
