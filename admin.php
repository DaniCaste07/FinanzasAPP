<?php
// admin.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'conexion.php';

// CONTROL DE SEGURIDAD ESTRICTO PARA EL TFG
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$nombre_usuario = $_SESSION['nombre'] ?? 'Administrador';
$inicial = strtoupper(substr($nombre_usuario, 0, 1));
$mensaje_estado = "";

// 1. LÓGICA: CREAR NUEVO USUARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
    $nuevo_nombre = trim($_POST['nombre']);
    $nuevo_email = trim($_POST['email']);
    $nuevo_pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $nuevo_rol = $_POST['rol'] ?? 'usuario';

    if (!empty($nuevo_nombre) && !empty($nuevo_email) && !empty($_POST['password'])) {
        try {
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nuevo_nombre, $nuevo_email, $nuevo_pass, $nuevo_rol]);
            $mensaje_estado = "Usuario registrado con éxito en InvestFlow.";
        } catch (PDOException $e) {
            $mensaje_estado = "Error: El correo electrónico ya está en uso.";
        }
    }
}

// 2. LÓGICA: PUBLICAR NOTICIA EN EL BLOG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_noticia'])) {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $autor_id = $_SESSION['usuario_id'];

    if (!empty($titulo) && !empty($contenido)) {
        try {
            $stmt = $conexion->prepare("INSERT INTO noticias (titulo, contenido, autor_id) VALUES (?, ?, ?)");
            $stmt->execute([$titulo, $contenido, $autor_id]);
            $mensaje_estado = "Artículo publicado en el blog del sistema con éxito.";
        } catch (PDOException $e) {
            $mensaje_estado = "Error crítico al guardar la noticia: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Control de Administración</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { 950: '#030712', 900: '#0b1120', 800: '#111827', 700: '#1f2937' },
                        brand: { DEFAULT: '#00ffa3', hover: '#00e693' }
                    },
                    fontFamily: { sans: ['Outfit', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-950 text-white font-sans antialiased min-h-screen flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 min-h-screen ml-[280px] p-10 bg-gradient-to-br from-dark-950 via-dark-900 to-dark-950 overflow-y-auto">
        
        <header class="flex justify-between items-center mb-10 border-b border-white/5 pb-6">
            <div>
                <h1 class="text-4xl font-black tracking-tight text-red-400">Panel de <span class="text-white">Administración</span></h1>
                <p class="text-gray-400 text-sm mt-1">Nivel de acceso de seguridad: Administrador</p>
            </div>
            
        </header>

        <?php if (!empty($mensaje_estado)): ?>
            <div class="p-4 mb-6 rounded-2xl bg-white/5 border border-white/10 font-bold text-sm text-brand flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-brand shadow-[0_0_8px_#00ffa3]"></span>
                <?= htmlspecialchars($mensaje_estado) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="p-8 rounded-3xl bg-dark-900/40 border border-white/5 backdrop-blur-md flex flex-col justify-between">
                <div>
                    <h2 class="text-xl font-black tracking-tight text-white mb-2">Crear Nuevo Usuario</h2>
                    <p class="text-xs text-gray-400 mb-6 font-medium">Inyectar un registro de acceso directo con asignación de rol.</p>
                </div>
                
                <form action="admin.php" method="POST" class="space-y-4">
                    <input type="hidden" name="crear_usuario" value="1">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Nombre Completo</label>
                        <input type="text" name="nombre" required placeholder="Ej. Carlos Pérez" class="w-full bg-dark-950 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand text-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Correo Electrónico</label>
                        <input type="email" name="email" required placeholder="carlos@investflow.com" class="w-full bg-dark-950 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand text-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Contraseña Temporal</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full bg-dark-950 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand text-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Rol Corporativo</label>
                        <select name="rol" class="w-full bg-dark-950 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand text-white transition-colors">
                            <option value="usuario">Usuario Estándar</option>
                            <option value="admin">Administrador Global</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-3.5 bg-red-500 hover:bg-red-600 rounded-xl font-black text-sm text-white shadow-[0_0_20px_rgba(239,68,68,0.2)] transition-all">Registrar Operador</button>
                </form>
            </div>

            <div class="p-8 rounded-3xl bg-dark-900/40 border border-white/5 backdrop-blur-md flex flex-col justify-between">
                <div>
                    <h2 class="text-xl font-black tracking-tight text-white mb-2">Publicar en el Blog</h2>
                    <p class="text-xs text-gray-400 mb-6 font-medium">Lanzar noticias financieras o comunicados generales de la plataforma.</p>
                </div>
                
                <form action="admin.php" method="POST" class="space-y-4">
                    <input type="hidden" name="crear_noticia" value="1">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Título del Artículo</label>
                        <input type="text" name="titulo" required placeholder="Ej. Corrección del mercado cripto en Q2" class="w-full bg-dark-950 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand text-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Contenido de la Noticia</label>
                        <textarea name="contenido" rows="7" required placeholder="Escribe aquí el cuerpo del boletín informativo corporativo..." class="w-full bg-dark-950 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand text-white transition-colors resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full py-3.5 bg-brand hover:bg-brand-hover rounded-xl font-black text-sm text-dark-950 shadow-[0_0_20px_rgba(0,255,163,0.2)] transition-all">Publicar Artículo</button>
                </form>
            </div>

        </div>
        <?php require_once 'footer.php'; ?>
    </main>

</body>
</html>