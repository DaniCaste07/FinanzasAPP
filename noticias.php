<?php
// noticias.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
$inicial = strtoupper(substr($nombre_usuario, 0, 1));

try {

    $query = "
        SELECT 
            n.titulo,
            n.contenido,
            n.fecha,
            u.nombre AS autor
        FROM noticias n
        JOIN usuarios u ON n.autor_id = u.id
        ORDER BY n.fecha DESC
    ";

    $stmt = $conexion->prepare($query);
    $stmt->execute();

    $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {

    $noticias = [];
    $error_bbdd = "Error al cargar las noticias del sistema.";
}
?>

<!DOCTYPE html>
<html lang="es" class="dark">

    <head>

        <meta charset="UTF-8">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>InvestFlow - Noticias del Mercado</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">

        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">

        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="style.css">

        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            dark: {
                                950: '#030712',
                                900: '#0b1120',
                                800: '#111827',
                                700: '#1f2937'
                            },
                            brand: {
                                DEFAULT: '#00ffa3',
                                hover: '#00e693'
                            }
                        },
                        fontFamily: {
                            sans: ['Outfit', 'sans-serif']
                        }
                    }
                }
            }
        </script>

    </head>

    <body class="text-gray-100 flex relative">

<?php include 'sidebar.php'; ?>

        <main class="main-content relative z-10 w-full">

            <div class="content-wrapper">

                <header class="mb-12 flex justify-between items-end">

                    <div>

                        <p class="text-brand text-xs font-black uppercase tracking-widest mb-2 flex items-center gap-2">

                            <span class="w-2 h-2 bg-brand rounded-full animate-pulse shadow-[0_0_8px_#00ffa3]"></span>

                            Información Financiera

                        </p>

                        <h1 class="text-5xl font-black tracking-tight text-white">

                            Noticias del
                            <span class="italic text-transparent bg-clip-text bg-gradient-to-r from-brand to-cyan-400">
                                Mercado
                            </span>

                        </h1>

                    </div>

                  
                </header>

                <div class="space-y-6 max-w-5xl">

<?php if (isset($error_bbdd)): ?>

                        <div class="p-6 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-bold">

    <?= htmlspecialchars($error_bbdd) ?>

                        </div>

<?php elseif (empty($noticias)): ?>

                        <div class="text-center py-20 border border-dashed border-white/5 rounded-[2rem] bg-dark-900/10">

                            <p class="font-bold text-gray-400 text-sm">
                                No hay noticias publicadas todavía.
                            </p>

                        </div>

<?php else: ?>

                        <?php foreach ($noticias as $noticia): ?>

                            <?php $id_noticia = md5($noticia['titulo'] . $noticia['fecha']); ?>

                            <article class="glass-panel p-8 rounded-[2rem] shadow-2xl relative overflow-hidden group hover:border-brand/20 transition-all duration-300">

                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2 mb-4 pb-4 border-b border-white/5">

                                    <div>

                                        <h2 class="text-2xl font-black text-white group-hover:text-brand transition-colors">

        <?= htmlspecialchars($noticia['titulo']) ?>

                                        </h2>

                                        <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-400 font-medium">

                                            <span>

                                                Por:
                                                <span class="text-white font-bold">
        <?= htmlspecialchars($noticia['autor']) ?>
                                                </span>

                                            </span>

                                            <span class="text-gray-600">•</span>

                                            <span>

        <?= date('d/m/Y - H:i', strtotime($noticia['fecha'])) ?>

                                            </span>

                                        </div>

                                    </div>

                                    <span class="px-3 py-1 bg-brand/10 border border-brand/20 rounded-lg text-[10px] font-mono text-brand uppercase tracking-wider font-bold">

                                        Informe Oficial

                                    </span>

                                </div>

                                <div class="relative">

                                    <p id="contenido-<?= $id_noticia ?>"
                                       class="text-gray-400 text-sm leading-relaxed whitespace-pre-line line-clamp-3 transition-all duration-300">

        <?= htmlspecialchars($noticia['contenido']) ?>

                                    </p>

                                    <button
                                        onclick="toggleNoticia('<?= $id_noticia ?>', this)"
                                        class="mt-5 text-brand text-xs font-black uppercase tracking-widest hover:text-white transition-colors">

                                        Leer más →

                                    </button>

                                </div>

                            </article>

    <?php endforeach; ?>

                    <?php endif; ?>

                </div>

                <div class="mt-16 text-center text-gray-600 text-xs font-mono uppercase tracking-widest">

                    InvestFlow App - Sistema de Noticias Financieras

                </div>

            </div>

<?php include 'footer.php'; ?>

        </main>

        <script>

            function toggleNoticia(id, button) {

                const contenido = document.getElementById("contenido-" + id);

                if (contenido.classList.contains("line-clamp-3")) {

                    contenido.classList.remove("line-clamp-3");

                    button.innerHTML = "Ver menos ↑";

                } else {

                    contenido.classList.add("line-clamp-3");

                    button.innerHTML = "Leer más →";

                }
            }

        </script>

    </body>
</html>