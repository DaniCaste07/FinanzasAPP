<?php
include('conexion.php');

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        // Preparamos la consulta con marcadores de posición (?)
        $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        
        // Ejecutamos pasando los datos en un array
        if ($stmt->execute([$nombre, $email, $password])) {
            $mensaje = "<div class='alert success'>¡Registro con PDO completado! <a href='login.php'>Inicia sesión</a></div>";
        }
    } catch (PDOException $e) {
        // Si hay un error de "Duplicate entry" para el email
        if ($e->getCode() == 23000) {
            $mensaje = "<div class='alert error'>El correo ya está registrado.</div>";
        } else {
            $mensaje = "<div class='alert error'>Error en el servidor. Inténtalo más tarde.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Registro de Usuario</title>
    <style>
        /* Estética Fintech Oscura */
        body {
            background-color: #0b111b;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-container {
            background: #162130;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            border: 1px solid #1f2d40;
        }
        h2 { text-align: center; color: #00ff88; margin-bottom: 30px; }
        .input-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-size: 0.9rem; color: #94a3b8; }
        input {
            width: 100%;
            padding: 12px;
            background: #0b111b;
            border: 1px solid #1f2d40;
            border-radius: 8px;
            color: white;
            box-sizing: border-box; /* Importante para el padding */
        }
        input:focus { border-color: #00ff88; outline: none; }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background: #00ff88;
            border: none;
            border-radius: 8px;
            color: #0b111b;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-primary:hover { background: #00cc6e; transform: translateY(-2px); }
        .alert { padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .success { background: rgba(0, 255, 136, 0.1); color: #00ff88; border: 1px solid #00ff88; }
        .error { background: rgba(255, 71, 71, 0.1); color: #ff4747; border: 1px solid #ff4747; }
        a { color: #00ff88; text-decoration: none; }
        .footer-link { text-align: center; margin-top: 20px; font-size: 0.85rem; }
    </style>
</head>
<body>

    <div class="register-container">
        <h2>Crear Cuenta</h2>
        
        <?php echo $mensaje; ?>

        <form action="registro.php" method="POST">
            <div class="input-group">
                <label>Nombre Completo</label>
                <input type="text" name="nombre" placeholder="Ej: Juan Pérez" required>
            </div>
            
            <div class="input-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" placeholder="correo@ejemplo.com" required>
            </div>

            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-primary">Registrarse ahora</button>
        </form>

        <div class="footer-link">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
        </div>
    </div>

</body>
</html>