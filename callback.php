<?php
session_start();

require_once 'vendor/autoload.php';

use Jumbojett\OpenIDConnectClient;

$oidc = new OpenIDConnectClient(
    'https://login.microsoftonline.com/{tenant-id}/v2.0',
    '{client-id}',
    '{client-secret}'
);

$oidc->setRedirectURL('https://deine-domain.de/callback.php');
$oidc->addScope(['openid', 'profile', 'email']);

// Verarbeite die Antwort von Microsoft Entra ID
$oidc->authenticate();

$claims = $oidc->getVerifiedClaims();

$_SESSION['username'] = $claims['name'] ?? '';
$_SESSION['user_email'] = $claims['preferred_username'] ?? '';

header('Location: index.php');
exit;
