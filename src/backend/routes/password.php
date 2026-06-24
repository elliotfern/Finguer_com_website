<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plainPassword = $_POST['password'] ?? '';

    if (!empty($plainPassword)) {
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador de hash de password</title>
</head>
<body>

<h2>Generar hash de contraseña</h2>

<form method="POST">
    <input type="text" name="password" placeholder="Escribe la contraseña">
    <button type="submit">Generar hash</button>
</form>

<?php if (!empty($hash)): ?>
    <h3>Resultado:</h3>
    <p><strong>Hash:</strong></p>
    <code><?= htmlspecialchars($hash) ?></code>
<?php endif; ?>

</body>
</html>