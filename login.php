<?php
session_start();
include('conexion.php'); // Conexión PDO

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Buscamos al usuario por email usando sentencias preparadas de PDO
        $stmt = $conexion->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Login exitoso: Guardamos datos en la sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Email o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        $error = "Error en el sistema. Inténtalo más tarde.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Iniciar Sesión</title>
    <style>
        /* Mismo CSS que el registro para mantener la armonía */
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
        .login-container {
            background: #162130;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            border: 1px solid #1f2d40;
        }
        h2 { text-align: center; color: #00ff88; margin-bottom: 10px; }
        h2 span { color: white; font-weight: normal; font-size: 0.9rem; display: block; margin-top: 5px; }
        
        .input-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-size: 0.9rem; color: #94a3b8; }
        input {
            width: 100%;
            padding: 12px;
            background: #0b111b;
            border: 1px solid #1f2d40;
            border-radius: 8px;
            color: white;
            box-sizing: border-box;
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
            margin-top: 10px;
        }
        .btn-primary:hover { background: #00cc6e; transform: translateY(-2px); }
        
        .error-msg {
            background: rgba(255, 71, 71, 0.1);
            color: #ff4747;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ff4747;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }
        .success-msg {
            background: rgba(0, 255, 136, 0.1);
            color: #00ff88;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #00ff88;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .footer-link { text-align: center; margin-top: 25px; font-size: 0.85rem; color: #94a3b8; }
        a { color: #00ff88; text-decoration: none; }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>InvestFlow <span>Acceso a tu panel financiero</span></h2>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['registro']) && $_GET['registro'] == 'exito'): ?>
            <div class="success-msg">¡Cuenta creada! Ya puedes entrar.</div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="input-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" placeholder="usuario@investflow.com" required>
            </div>

            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-primary">Entrar al Sistema</button>
        </form>

        <div class="footer-link">
            ¿Aún no eres miembro? <a href="registro.php">Regístrate ahora</a>
        </div>
    </div>

</body>
</html>