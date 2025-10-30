<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Vos identifiants BankAPI</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="lock" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="2" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23lock)"/></svg>');
            opacity: 0.3;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .content {
            padding: 40px 30px;
            background: #f8f9fa;
        }

        .welcome-message {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            border-left: 4px solid #ff6b6b;
        }

        .credentials-card {
            background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            border: 2px solid #fdcb6e;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            position: relative;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .credentials-card::before {
            content: '🔐';
            position: absolute;
            top: -15px;
            left: 20px;
            background: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            border: 3px solid #fdcb6e;
        }

        .credentials-card h3 {
            color: #d63031;
            font-size: 22px;
            margin-bottom: 25px;
            margin-left: 35px;
            font-weight: 600;
        }

        .credential-item {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #fdcb6e;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .credential-label {
            font-weight: 600;
            color: #d63031;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .credential-value {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            word-break: break-all;
        }

        .warning-section {
            background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            border: 2px solid #e17055;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            display: flex;
            align-items: flex-start;
        }

        .warning-section::before {
            content: '⚠️';
            font-size: 24px;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .warning-text {
            color: #d63031;
            font-size: 15px;
            line-height: 1.6;
            font-weight: 500;
        }

        .warning-text strong {
            color: #c0392b;
        }

        .security-tips {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid #e9ecef;
        }

        .security-tips h4 {
            color: #2c3e50;
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .security-tips h4::before {
            content: '🛡️';
            margin-right: 10px;
        }

        .security-tips ul {
            list-style: none;
            padding: 0;
        }

        .security-tips li {
            padding: 8px 0;
            padding-left: 20px;
            position: relative;
            color: #5a6c7d;
        }

        .security-tips li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #27ae60;
            font-weight: bold;
        }

        .cta-section {
            text-align: center;
            margin: 35px 0;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }

        .footer {
            background: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .footer p {
            margin: 10px 0;
            font-size: 14px;
        }

        .footer .copyright {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 20px;
        }

        /* Responsive design */
        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 10px;
            }

            .header, .content {
                padding: 20px;
            }

            .credentials-card {
                padding: 20px;
            }

            .credential-value {
                font-size: 16px;
                padding: 10px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🔐 Accès sécurisé BankAPI</h1>
            <p>Vos identifiants de connexion</p>
        </div>

        <div class="content">
            <div class="welcome-message">
                <strong>Bonjour,</strong><br><br>
                Votre compte client BankAPI a été créé avec succès. Voici vos identifiants de connexion sécurisés.
            </div>

            <div class="credentials-card">
                <h3>Vos identifiants temporaires</h3>

                <div class="credential-item">
                    <div class="credential-label">Mot de passe temporaire</div>
                    <div class="credential-value">{{ $password }}</div>
                </div>
            </div>

            <div class="warning-section">
                <div class="warning-text">
                    <strong>Action requise immédiatement :</strong><br>
                    Veuillez vous connecter à votre compte et changer ce mot de passe temporaire dès que possible pour des raisons de sécurité.
                </div>
            </div>

            <div class="security-tips">
                <h4>Conseils de sécurité</h4>
                <ul>
                    <li>Ne partagez jamais vos identifiants avec qui que ce soit</li>
                    <li>Utilisez un mot de passe fort avec au moins 8 caractères</li>
                    <li>Activez l'authentification à deux facteurs si disponible</li>
                    <li>Déconnectez-vous après chaque session</li>
                    <li>Signalez immédiatement toute activité suspecte</li>
                </ul>
            </div>

            <div class="cta-section">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">Prêt à vous connecter ?</h3>
                <p style="color: #5a6c7d; margin-bottom: 25px;">
                    Accédez à votre espace client sécurisé et commencez à gérer vos comptes.
                </p>
                <a href="#" class="cta-button">Se connecter maintenant</a>
            </div>
        </div>

        <div class="footer">
            <p><strong>BankAPI - Sécurité et confiance</strong></p>
            <p>Votre sécurité est notre priorité absolue</p>
            <p class="copyright">
                Cet email contient des informations sensibles.<br>
                Ne le transmettez pas et supprimez-le après utilisation.<br>
                © {{ date('Y') }} BankAPI - Tous droits réservés | Dakar, Sénégal
            </p>
        </div>
    </div>
</body>
</html>
