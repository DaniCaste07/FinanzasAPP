<?php
// sidebar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'conexion.php';

$current_page = basename($_SERVER['PHP_SELF']);
$uid = $_SESSION['usuario_id'] ?? 0;
$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
$inicial = strtoupper(substr($nombre_usuario, 0, 1));

// CONSULTA SEGURA: Obtenemos el avatar directamente de la base de datos
$avatar_db = 'default.png';
if ($uid > 0) {
    try {
        $stmt_side = $conexion->prepare("SELECT avatar FROM usuarios WHERE id = ?");
        $stmt_side->execute([$uid]);
        $user_side = $stmt_side->fetch();
        if ($user_side && !empty($user_side['avatar'])) {
            $avatar_db = $user_side['avatar'];
        }
    } catch (Exception $e) {
        // Fallback silencioso por si fallara la sincronización del esquema
    }
}

// Damos prioridad al avatar en la sesión (recién subido) y si no, al de la BBDD
$avatar_actual = $_SESSION['avatar'] ?? $avatar_db;

$foto_sidebar = (!empty($avatar_actual) && $avatar_actual != 'default.png') 
               ? 'uploads/' . $avatar_actual 
               : 'https://ui-avatars.com/api/?name='.urlencode($nombre_usuario).'&background=00ffa3&color=030712&bold=true';
?>

<style>
    /* CSS para el menú interactivo */
    .sidebar-link {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
    }
    .sidebar-link:hover:not(.is-active) {
        transform: translateX(12px);
        background: rgba(255, 255, 255, 0.03);
        color: #00ffa3;
    }
    .is-active {
        background: linear-gradient(90deg, rgba(0,255,163,0.15) 0%, rgba(0,255,163,0) 100%);
        color: #00ffa3;
        border-left: 4px solid #00ffa3;
        font-weight: 900;
        transform: scale(1.08) translateX(8px);
        box-shadow: -5px 0 25px rgba(0, 255, 163, 0.2);
    }
    .is-active svg { filter: drop-shadow(0 0 8px rgba(0, 255, 163, 0.8)); }
    .sidebar-scroll::-webkit-scrollbar { width: 4px; }
    .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
</style>

<aside class="sidebar bg-dark-950/95 backdrop-blur-2xl flex flex-col justify-between shadow-[10px_0_40px_rgba(0,0,0,0.5)] border-r border-white/5 w-[280px] h-screen fixed z-50 left-0 top-0 pt-8 pb-0">
    
    <div class="px-8 mb-8">
        <div class="flex items-center gap-3 cursor-pointer group transition-transform duration-300 hover:scale-105">
            <div class="w-11 h-11 bg-brand rounded-xl flex items-center justify-center text-dark-950 font-black text-2xl shadow-[0_0_20px_rgba(0,255,163,0.4)]">IF</div>
            <span class="text-3xl font-black tracking-tight text-white">Invest<span class="text-brand">Flow</span></span>
        </div>
    </div>
    
    <nav class="flex-1 overflow-y-auto overflow-x-hidden px-4 space-y-2 sidebar-scroll">
        <a href="dashboard.php" class="sidebar-link <?= $current_page == 'dashboard.php' ? 'is-active' : 'text-gray-400' ?> flex items-center gap-4 py-3.5 px-4 rounded-r-2xl mr-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            <span class="tracking-wide">Resumen General</span>
        </a>
        
        <a href="inversiones.php" class="sidebar-link <?= $current_page == 'inversiones.php' ? 'is-active' : 'text-gray-400' ?> flex items-center gap-4 py-3.5 px-4 rounded-r-2xl mr-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            <span class="tracking-wide">Mis Inversiones</span>
        </a>
        
        <a href="hipotecas.php" class="sidebar-link <?= $current_page == 'hipotecas.php' ? 'is-active' : 'text-gray-400' ?> flex items-center gap-4 py-3.5 px-4 rounded-r-2xl mr-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="tracking-wide">Simulador Hipoteca</span>
        </a>
        
        <a href="planificador.php" class="sidebar-link <?= $current_page == 'planificador.php' ? 'is-active' : 'text-gray-400' ?> flex items-center gap-4 py-3.5 px-4 rounded-r-2xl mr-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span class="tracking-wide">Planificador</span>
        </a>
        
        <a href="noticias.php" class="sidebar-link <?= $current_page == 'noticias.php' ? 'is-active' : 'text-gray-400' ?> flex items-center gap-4 py-3.5 px-4 rounded-r-2xl mr-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h5m-5 4h5m-5 4h10"></path>
            </svg>
            <span class="tracking-wide">Noticias Mercado</span>
        </a>
        
        <div class="h-px bg-white/5 mx-4 my-4"></div>
        
        <a href="ayuda.php" class="sidebar-link <?= $current_page == 'ayuda.php' ? 'is-active' : 'text-gray-400' ?> flex items-center gap-4 py-3.5 px-4 rounded-r-2xl mr-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="tracking-wide">Centro de Ayuda</span>
        </a>
        

        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
            <div class="h-px bg-white/5 mx-4 my-4"></div>
            
            <a href="admin.php" class="sidebar-link <?= $current_page == 'admin.php' ? 'is-active' : 'text-red-400/80 hover:text-red-400' ?> flex items-center gap-4 py-3.5 px-4 rounded-r-2xl mr-2">
                <svg class="w-5 h-5 flex-shrink-0 text-red-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                <span class="tracking-wide font-bold">Panel Admin</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="p-6 border-t border-white/5 bg-dark-900/50 relative overflow-hidden">
        <div class="absolute -bottom-10 -right-10 w-24 h-24 bg-brand/10 blur-xl rounded-full"></div>
        
        <a href="ajustes.php" class="flex items-center gap-3 mb-5 relative z-10 group cursor-pointer">
            <img src="<?= $foto_sidebar ?>" class="w-12 h-12 rounded-xl object-cover border border-brand/20 shadow-[0_0_15px_rgba(0,255,163,0.3)] group-hover:border-brand transition-colors">
            
            <div class="transition-transform group-hover:translate-x-1">
                <p class="text-sm font-bold text-white leading-tight truncate w-32 group-hover:text-brand transition-colors"><?= htmlspecialchars($nombre_usuario) ?></p>
                <div class="flex items-center gap-1 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand shadow-[0_0_5px_#00ffa3]"></span>
                    <p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold">Ajustes de Cuenta</p>
                </div>
            </div>
        </a>
        
        <a href="logout.php" class="flex items-center justify-center gap-2 text-gray-400 bg-white/5 border border-white/5 py-3 rounded-xl font-bold text-sm hover:text-red-400 hover:bg-red-500/10 transition-all">Cerrar Sesión</a>
    </div>
</aside>