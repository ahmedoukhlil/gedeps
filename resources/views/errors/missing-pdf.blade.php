<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Non Trouvé - GEDEPS</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            text-align: center;
        }
        .icon {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 1rem;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        .error-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
            text-align: left;
        }
        .filename {
            font-family: monospace;
            background: #e9ecef;
            padding: 0.5rem;
            border-radius: 4px;
            word-break: break-all;
        }
        .actions {
            margin-top: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 0.5rem;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-secondary {
            background: #95a5a6;
        }
        .btn-secondary:hover {
            background: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">📄❌</div>
        <h1>PDF Non Trouvé</h1>
        <p>Le fichier PDF demandé n'est pas disponible sur le serveur.</p>
        
        <div class="error-details">
            <h3>Détails de l'erreur :</h3>
            <p><strong>Fichier recherché :</strong></p>
            <div class="filename">{{ $filename }}</div>
            
            <p><strong>Emplacements vérifiés :</strong></p>
            <ul>
                @foreach($searched_paths as $path)
                    <li><code>{{ $path }}</code></li>
                @endforeach
            </ul>
        </div>
        
        <div class="actions">
            <a href="{{ url()->previous() }}" class="btn">← Retour</a>
            <a href="{{ route('documents.pending') }}" class="btn btn-secondary">📋 Documents</a>
        </div>
        
        <p style="margin-top: 2rem; color: #7f8c8d; font-size: 0.9rem;">
            Si ce problème persiste, contactez l'administrateur système.
        </p>
    </div>
</body>
</html>
