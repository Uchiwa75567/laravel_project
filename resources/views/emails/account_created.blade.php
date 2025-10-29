<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏦 Bienvenue chez BankAPI - Votre compte est créé !</title>
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
            max-width: 650px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
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
            border-left: 4px solid #3498db;
        }

        .account-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }

        .account-card h3 {
            color: #2c3e50;
            font-size: 22px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            font-weight: 600;
        }

        .account-card h3::before {
            content: '📋';
            margin-right: 10px;
            font-size: 24px;
        }

        .account-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .account-detail:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #5a6c7d;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-weight: 700;
            color: #2c3e50;
            font-size: 16px;
        }

        .account-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .credentials-card {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            border: 2px solid #ff9f43;
            border-radius: 16px;
            padding: 25px;
            margin: 30px 0;
            position: relative;
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
            border: 3px solid #ff9f43;
        }

        .credentials-card h3 {
            color: #d63031;
            font-size: 20px;
            margin-bottom: 20px;
            margin-left: 35px;
            font-weight: 600;
        }

        .credential-item {
            background: rgba(255, 255, 255, 0.8);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #ff9f43;
        }

        .credential-label {
            font-weight: 600;
            color: #d63031;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .credential-value {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            display: flex;
            align-items: flex-start;
        }

        .warning-box::before {
            content: '⚠️';
            font-size: 20px;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .warning-text {
            color: #856404;
            font-size: 14px;
            line-height: 1.5;
        }

        .cta-section {
            text-align: center;
            margin: 40px 0;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 10px;
            transition: transform 0.2s ease;
        }

        .cta-button:hover {
            transform: translateY(-2px);
        }

        .contact-info {
            background: #e8f4f8;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }

        .contact-info h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .contact-info p {
            color: #5a6c7d;
            margin: 5px 0;
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

        .social-links {
            margin: 20px 0;
        }

        .social-links a {
            color: white;
            margin: 0 10px;
            font-size: 20px;
            text-decoration: none;
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

            .account-card {
                padding: 20px;
            }

            .account-detail {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🏦 Bienvenue chez BankAPI</h1>
            <p>Votre compte bancaire a été créé avec succès !</p>
        </div>

        <div class="content">
            <div class="welcome-message">
                <strong>Cher(e) {{ $client->name }},</strong><br><br>
                Félicitations ! Votre compte bancaire a été ouvert avec succès. Vous faites maintenant partie de notre communauté bancaire sécurisée.
            </div>

            <div class="account-card">
                <h3>Détails de votre compte</h3>

                <div class="account-detail">
                    <span class="detail-label">Numéro de compte</span>
                    <span class="detail-value account-number">{{ $compte->numero }}</span>
                </div>

                <div class="account-detail">
                    <span class="detail-label">Type de compte</span>
                    <span class="detail-value">{{ ucfirst($compte->type) }}</span>
                </div>


                <div class="account-detail">
                    <span class="detail-label">Date d'ouverture</span>
                    <span class="detail-value">{{ $compte->date_ouverture->format('d/m/Y') }}</span>
                </div>

                <div class="account-detail">
                    <span class="detail-label">Devise</span>
                    <span class="detail-value">{{ $compte->devise }}</span>
                </div>
            </div>

            @if($generatedPassword)
            <div class="credentials-card">
                <h3>Vos identifiants de connexion</h3>

                <div class="credential-item">
                    <div class="credential-label">Adresse email</div>
                    <div class="credential-value">{{ $client->email }}</div>
                </div>

                <div class="credential-item">
                    <div class="credential-label">Mot de passe temporaire</div>
                    <div class="credential-value">{{ $generatedPassword }}</div>
                </div>

                <div class="warning-box">
                    <div class="warning-text">
                        <strong>Important :</strong> Veuillez changer votre mot de passe lors de votre première connexion pour des raisons de sécurité.
                    </div>
                </div>
            </div>
            @endif

            <div class="cta-section">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">Prêt à commencer ?</h3>
                <p style="color: #5a6c7d; margin-bottom: 25px;">
                    Accédez à votre espace client pour consulter vos comptes, effectuer des transactions et gérer vos finances.
                </p>
                <a href="#" class="cta-button">Accéder à mon espace</a>
            </div>

            <div class="contact-info">
                <h4>Besoin d'aide ?</h4>
                <p>📧 support@bankapi.com</p>
                <p>📞 +221 33 123 45 67</p>
                <p>🕒 Lundi au Vendredi : 8h00 - 18h00</p>
            </div>
        </div>

        <div class="footer">
            <div class="social-links">
                <a href="#">📘</a>
                <a href="#">🐦</a>
                <a href="#">📷</a>
                <a href="#">💼</a>
            </div>
            <p><strong>BankAPI - Votre banque digitale de confiance</strong></p>
            <p>Sécurité • Innovation • Service</p>
            <p class="copyright">
                Cet email a été envoyé automatiquement depuis notre plateforme sécurisée.<br>
                © {{ date('Y') }} BankAPI - Tous droits réservés | Dakar, Sénégal
            </p>
        </div>
    </div>
</body>
</html>
