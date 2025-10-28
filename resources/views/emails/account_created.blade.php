<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de votre compte bancaire</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        .account-details {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .credentials {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            font-size: 12px;
        }
        .highlight {
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏦 Bienvenue dans notre banque</h1>
        <p>Votre compte bancaire a été créé avec succès</p>
    </div>

    <div class="content">
        <p>Bonjour <strong>{{ $client->name }}</strong>,</p>

        <p>Nous avons le plaisir de vous informer que votre compte bancaire a été créé avec succès. Voici les détails de votre nouveau compte :</p>

        <div class="account-details">
            <h3>📋 Détails du compte</h3>
            <p><strong>Numéro de compte :</strong> <span class="highlight">{{ $compte->numero }}</span></p>
            <p><strong>Type de compte :</strong> {{ ucfirst($compte->type) }}</p>
            <p><strong>Solde initial :</strong> {{ number_format($compte->solde, 0, ',', ' ') }} {{ $compte->devise }}</p>
            <p><strong>Date d'ouverture :</strong> {{ $compte->date_ouverture->format('d/m/Y') }}</p>
            <p><strong>Devise :</strong> {{ $compte->devise }}</p>
        </div>

        @if($generatedPassword)
        <div class="credentials">
            <h3>🔐 Vos identifiants de connexion</h3>
            <p><strong>Email :</strong> {{ $client->email }}</p>
            <p><strong>Mot de passe temporaire :</strong> <span class="highlight">{{ $generatedPassword }}</span></p>
            <p style="color: #856404; font-size: 14px;">
                ⚠️ <strong>Important :</strong> Veuillez changer votre mot de passe lors de votre première connexion pour des raisons de sécurité.
            </p>
        </div>
        @endif

        <p>Vous pouvez maintenant accéder à votre espace client et commencer à utiliser votre compte bancaire. N'hésitez pas à nous contacter si vous avez des questions.</p>

        <p>Cordialement,<br>
        <strong>L'équipe de la Banque</strong></p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>© {{ date('Y') }} Banque - Tous droits réservés</p>
    </div>
</body>
</html>
