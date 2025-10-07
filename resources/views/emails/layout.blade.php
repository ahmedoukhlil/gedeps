<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'GEDEPS Notification' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6; 
            color: #374151; 
            background-color: #f9fafb;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: #3b82f6;
            color: white;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }
        .content {
            padding: 24px;
        }
        .info-box {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px;
            margin: 16px 0;
        }
        .info-box h3 {
            color: #1f2937;
            font-size: 16px;
            margin: 0 0 8px 0;
        }
        .info-box p {
            margin: 4px 0;
            color: #6b7280;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            text-align: center;
            margin: 16px 0;
        }
        .button:hover {
            background: #2563eb;
        }
        .footer {
            background: #f9fafb;
            padding: 16px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-success { background: #dcfce7; color: #166534; }
        .status-info { background: #dbeafe; color: #1e40af; }
        .status-warning { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ $headerTitle ?? 'GEDEPS' }}</h1>
        </div>
        
        <div class="content">
            @yield('content')
        </div>
        
        <div class="footer">
            <p>GEDEPS - Système de Gestion Électronique de Documents</p>
            <p>Email automatique - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>
