
<?php
$user = $_SERVER["HTTP_X_MS_CLIENT_PRINCIPAL_NAME"] ?? "Gast";
echo "<h1>Willkommen $user</h1>";
?>
