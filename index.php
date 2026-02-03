<?php
session_start();
require_once 'conexion.php'; // Conexión PDO

$error = "";

// 1. Si el usuario ya tiene una sesión activa, lo mandamos directo al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

// 2. Lógica para procesar el inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_btn'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Buscamos al usuario por email
        $stmt = $conexion->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Éxito: Guardamos datos en la sesión y redirigimos
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
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow | Control Financiero Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { 900: '#0b111b', 800: '#162130', 700: '#1f2d40' },
                        brand: { DEFAULT: '#00ff88', hover: '#00cc6e' }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0b111b; }
        .gradient-bg { background: radial-gradient(circle at top right, rgba(0, 255, 136, 0.1), transparent 50%), linear-gradient(135deg, #0b111b 0%, #080d14 100%); }
        .tab-active { border-bottom: 2px solid #00ff88; color: #00ff88; }
    </style>
</head>
<body class="min-h-screen flex flex-col text-gray-100">

    <nav class="bg-dark-900/80 backdrop-blur-md sticky top-0 z-50 border-b border-dark-700">
        <div class="max-w-7xl mx-auto px-6 h-16 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-brand rounded-lg flex items-center justify-center shadow-lg shadow-brand/20">
                    <span class="text-dark-900 font-bold">$</span>
                </div>
                <span class="text-xl font-bold tracking-tight">Invest<span class="text-brand">Flow</span></span>
            </div>
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-400">
                <a href="hipotecas.php" class="hover:text-brand transition-colors">Calculadora Hipoteca</a>
                <a href="inversiones.php" class="hover:text-brand transition-colors">Mis Inversiones</a>
                <a href="registro.php" class="bg-brand text-dark-900 px-5 py-2 rounded-full hover:bg-brand-hover transition-all font-bold">Empezar</a>
            </div>
        </div>
    </nav>

    <main class="flex-grow flex flex-col md:flex-row">
        <section class="w-full md:w-[45%] flex items-center justify-center p-8 lg:p-16">
            <div class="w-full max-w-sm">
                <div class="mb-10">
                    <h1 class="text-3xl font-extrabold text-white mb-2 text-center md:text-left">Bienvenido</h1>
                    <p class="text-gray-400 text-center md:text-left">Ingresa tus credenciales para acceder.</p>
                </div>

                <form action="" method="POST" class="space-y-6">
                    <?php if ($error): ?>
                        <div class="bg-red-500/10 border border-red-500 text-red-500 p-3 rounded-xl text-sm text-center">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-2 tracking-widest">Correo Electrónico</label>
                        <input type="email" name="email" required class="w-full px-4 py-3 rounded-xl bg-dark-800 border border-dark-700 text-white focus:border-brand focus:ring-1 focus:ring-brand outline-none transition-all placeholder-gray-600" placeholder="usuario@investflow.com">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-2 tracking-widest">Contraseña</label>
                        <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl bg-dark-800 border border-dark-700 text-white focus:border-brand focus:ring-1 focus:ring-brand outline-none transition-all placeholder-gray-600" placeholder="••••••••">
                    </div>

                    <button type="submit" name="login_btn" class="w-full bg-brand text-dark-900 py-4 rounded-xl font-bold shadow-lg shadow-brand/20 hover:bg-brand-hover hover:-translate-y-0.5 transition-all">
                        Entrar al Sistema
                    </button>
                </form>

                <p class="mt-8 text-center text-xs text-gray-500">
                    ¿No tienes cuenta? <a href="registro.php" class="text-brand hover:underline">Regístrate gratis</a>
                </p>
            </div>
        </section>

        <section class="hidden md:flex w-[55%] gradient-bg items-center justify-center p-20 relative overflow-hidden border-l border-dark-700">
            <div class="relative z-10 max-w-lg">
                <h2 class="text-5xl font-extrabold text-white mb-8 leading-tight">Visualiza tu futuro <span class="text-brand">financiero.</span></h2>
                <div class="space-y-6">
                    <div class="flex items-center gap-4 bg-dark-800/50 p-4 rounded-2xl border border-dark-700">
                        <div class="w-10 h-10 bg-brand/20 rounded-lg flex items-center justify-center text-brand">✓</div>
                        <p class="text-gray-300 font-medium text-sm">Cálculos de hipoteca con motor Java de alta precisión.</p>
                    </div>
                    <div class="flex items-center gap-4 bg-dark-800/50 p-4 rounded-2xl border border-dark-700">
                        <div class="w-10 h-10 bg-brand/20 rounded-lg flex items-center justify-center text-brand">✓</div>
                        <p class="text-gray-300 font-medium text-sm">Gráficos interactivos de rendimiento de activos.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>