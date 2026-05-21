<?php
// noticias.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
$inicial = strtoupper(substr($nombre_usuario, 0, 1));

try {
    // CONSULTA RELACIONAL (JOIN): Extraemos las noticias cruzándolas con el nombre del autor
    $query = "SELECT n.titulo, n.contenido, n.fecha, u.nombre AS autor 
              FROM noticias n 
              JOIN usuarios u ON n.autor_id = u.id 
              ORDER BY n.fecha DESC";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $noticias = [];
    $error_bbdd = "Error al conectar con la base de datos de boletines.";
}
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Boletín de Noticias</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
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
    <style>
        body { background-color: #030712; font-family: 'Outfit', sans-serif; overflow-x: hidden; }
        .main-content { margin-left: 280px; min-height: 100vh; display: flex; flex-direction: column; }
        .content-wrapper { padding: 40px; flex-grow: 1; }
        .glass-panel { 
            background: rgba(17, 24, 39, 0.6); 
            backdrop-filter: blur(16px); 
            border: 1px solid rgba(255, 255, 255, 0.05); 
        }
    </style>
</head>
<body class="text-gray-100 flex relative">

    <?php include 'sidebar.php'; ?>

    <main class="main-content relative z-10 w-full">
        <div class="content-wrapper">
        
            <header class="mb-12 flex justify-between items-end">
                <div>
                    <p class="text-brand text-xs font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 bg-brand rounded-full animate-pulse shadow-[0_0_8px_#00ffa3]"></span>
                        Información Financiera Conectada
                    </p>
                    <h1 class="text-5xl font-black tracking-tight text-white">
                        Noticias del <span class="italic text-transparent bg-clip-text bg-gradient-to-r from-brand to-cyan-400">Mercado</span>
                    </h1>
                </div>
                
                <div class="hidden lg:flex items-center gap-4 bg-dark-900/60 border border-white/5 px-5 py-2 rounded-2xl">
                    <div class="text-right">
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Terminal Activo</p>
                        <p class="text-sm font-black text-white"><?= htmlspecialchars($nombre_usuario) ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand to-cyan-500 flex items-center justify-center font-black text-dark-950 text-lg shadow-[0_0_15px_rgba(0,255,163,0.3)]">
                        <?= $inicial ?>
                    </div>
                </div>
            </header>

            <div class="space-y-6 max-w-5xl">
                <?php if (isset($error_bbdd)): ?>
                    <div class="p-6 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-bold">
                        <?= htmlspecialchars($error_bbdd) ?>
                    </div>
                <?php elseif (empty($noticias)): ?>
                    <div class="text-center py-20 border border-dashed border-white/5 rounded-[2rem] bg-dark-900/10">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-600 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="font-bold text-gray-400 text-sm">No hay comunicados publicados en el blog todavía.</p>
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                            <a href="admin.php" class="text-brand text-xs font-bold mt-2 inline-block border-b border-brand/30 pb-0.5 hover:border-brand">Redactar primera noticia →</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($noticias as $noticia): ?>
                        <article class="glass-panel p-8 rounded-[2rem] shadow-2xl relative overflow-hidden group hover:border-brand/20 transition-all duration-300">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2 mb-4 pb-4 border-b border-white/5">
                                <div>
                                    <h2 class="text-2xl font-black text-white group-hover:text-brand transition-colors">
                                        <?= htmlspecialchars($noticia['titulo']) ?>
                                    </h2>
                                    <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-400 font-medium">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            Por: <span class="text-gray-200 font-bold"><?= htmlspecialchars($noticia['autor']) ?></span>
                                        </span>
                                        <span class="text-gray-600">•</span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <?= date('d/m/Y - H:i', strtotime($noticia['fecha'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <span class="px-3 py-1 bg-brand/10 border border-brand/20 rounded-lg text-[10px] font-mono text-brand uppercase tracking-wider font-bold">
                                    Informe Oficial
                                </span>
                            </div>
                            <p class="text-gray-400 text-sm leading-relaxed whitespace-pre-line">
                                <?= htmlspecialchars($noticia['contenido']) ?>
                            </p>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="mt-16 text-center text-gray-600 text-xs font-mono uppercase tracking-widest">
                InvestFlow App - Módulo de Boletines Informativos
            </div>
        </div>

        <?php include 'footer.php'; ?>
    </main>

</body>
</html>
