<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
$nombre = $_SESSION['nombre'] ?? 'Usuario'; 
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Documentación del Sistema</title>
    
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
        .sidebar { width: 280px; height: 100vh; position: fixed; border-right: 1px solid rgba(255,255,255,0.05); z-index: 50; }
        .main-content { margin-left: 280px; padding: 40px; min-height: 100vh; }
        
        .glass-panel { 
            background: rgba(17, 24, 39, 0.6); 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05); 
        }
        
        .logout-btn:hover { color: #ef4444 !important; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); }
        .nav-link { transition: all 0.3s ease; }
        .nav-link:hover { padding-left: 1.5rem; background: rgba(255,255,255,0.03); color: #00ffa3; }
        .nav-active { background: rgba(0,255,163,0.1); color: #00ffa3; border-left: 4px solid #00ffa3; font-weight: 800; }
    </style>
</head>
<body class="text-gray-100 flex relative">

    <div class="fixed top-[-10%] left-[20%] w-[600px] h-[600px] bg-brand/5 rounded-full blur-[120px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-10%] right-[-5%] w-[500px] h-[500px] bg-blue-600/5 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <aside class="sidebar bg-dark-950/80 backdrop-blur-xl p-6 flex flex-col justify-between shadow-2xl">
        <div>
            <div class="flex items-center gap-3 mb-12 px-2 mt-4 cursor-pointer hover:scale-105 transition-transform">
                <div class="w-10 h-10 bg-brand rounded-xl flex items-center justify-center text-dark-950 font-black text-xl shadow-[0_0_20px_rgba(0,255,163,0.4)]">IF</div>
                <span class="text-2xl font-black tracking-tight text-white">Invest<span class="text-brand">Flow</span></span>
            </div>
            
            <nav class="space-y-2">
                <a href="dashboard.php" class="nav-link flex items-center gap-3 text-gray-400 py-3 px-4 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Resumen General
                </a>
                <a href="inversiones.php" class="nav-link flex items-center gap-3 text-gray-400 py-3 px-4 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    Mis Inversiones
                </a>
                <a href="hipotecas.php" class="nav-link flex items-center gap-3 text-gray-400 py-3 px-4 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Simulador Hipotecario
                </a>
                <a href="planificador.php" class="nav-link flex items-center gap-3 text-gray-400 py-3 px-4 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Planificador
                </a>
                <a href="libertad.php" class="nav-link flex items-center gap-3 text-gray-400 py-3 px-4 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Libertad Financiera
                </a>
                
                <div class="h-px bg-white/5 my-2"></div>
                
                <a href="ayuda.php" class="nav-active flex items-center gap-3 py-3 px-4 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Centro de Ayuda
                </a>
            </nav>
        </div>

        <div class="border-t border-white/5 pt-6">
            <div class="flex items-center gap-3 px-4 mb-4">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-brand to-blue-500 flex items-center justify-center font-black text-dark-950">
                    <?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?>
                </div>
                <div>
                    <p class="text-sm font-bold text-white leading-tight"><?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
                    <p class="text-[10px] text-gray-500 uppercase tracking-widest">Usuario Pro</p>
                </div>
            </div>
            <a href="logout.php" class="logout-btn flex items-center justify-center gap-2 text-gray-400 border border-white/5 transition-all py-3 rounded-xl font-bold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Cerrar Sesión
            </a>
        </div>
    </aside>

    <main class="main-content flex-1 relative z-10">
        
        <header class="mb-12 flex justify-between items-end">
            <div>
                <p class="text-brand text-xs font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                    <span class="w-2 h-2 bg-brand rounded-full animate-pulse shadow-[0_0_8px_#00ffa3]"></span>
                    Documentación TFG
                </p>
                <h1 class="text-5xl font-black tracking-tight text-white">
                    Manual del <span class="italic text-transparent bg-clip-text bg-gradient-to-r from-brand to-cyan-400">Sistema</span>
                </h1>
            </div>
            
            <div class="hidden lg:flex items-center gap-3 glass-panel px-4 py-2 rounded-xl border border-white/10">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <div>
                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Versión actual</p>
                    <p class="font-mono text-xs text-white font-bold">InvestFlow v2.0</p>
                </div>
            </div>
        </header>

        <div class="glass-panel p-8 rounded-[2rem] shadow-2xl mb-8 relative overflow-hidden group border-l-4 border-l-blue-500">
            <div class="absolute top-0 right-0 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl -mr-10 -mt-10 group-hover:bg-blue-500/20 transition-colors"></div>
            <h3 class="text-xl font-black text-white mb-4 flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                Arquitectura Técnica (Stack)
            </h3>
            <p class="text-gray-400 text-sm leading-relaxed mb-4 max-w-4xl">
                InvestFlow es una aplicación de grado institucional. Utiliza <strong class="text-white">PHP 8 con PDO</strong> para una conexión segura a bases de datos relacionales (MySQL). El frontend está renderizado mediante <strong class="text-white">Tailwind CSS y Chart.js</strong>.
            </p>
            <div class="flex flex-wrap gap-3">
                <span class="px-3 py-1 bg-dark-950 border border-white/10 rounded-lg text-xs font-mono text-brand">API Rest (Binance/CoinCap)</span>
                <span class="px-3 py-1 bg-dark-950 border border-white/10 rounded-lg text-xs font-mono text-purple-400">Java Backend Engine (.jar)</span>
                <span class="px-3 py-1 bg-dark-950 border border-white/10 rounded-lg text-xs font-mono text-blue-400">Cifrado de Contraseñas BCRYPT</span>
            </div>
        </div>

        <h2 class="text-2xl font-black text-white mb-6 mt-12">Módulos del Sistema</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="glass-panel p-8 rounded-3xl hover:bg-white/5 transition-colors border border-white/5 hover:border-brand/30 group">
                <div class="w-12 h-12 rounded-xl bg-dark-900 border border-white/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">1. Resumen General</h3>
                <p class="text-sm text-gray-400 leading-relaxed">
                    Es el panel principal. Calcula el patrimonio neto sumando todas tus inversiones y conectándose a la API para obtener el precio en tiempo real. Incluye un anillo gráfico de distribución y un <strong class="text-brand">módulo de IA</strong> que lee tus ganancias/pérdidas y te da consejos dinámicos.
                </p>
            </div>

            <div class="glass-panel p-8 rounded-3xl hover:bg-white/5 transition-colors border border-white/5 hover:border-brand/30 group">
                <div class="w-12 h-12 rounded-xl bg-dark-900 border border-white/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">2. Gestor de Inversiones</h3>
                <p class="text-sm text-gray-400 leading-relaxed">
                    Libro de órdenes personal. Permite registrar compras de activos (CRUD con base de datos). Muestra el precio de compra histórico frente al valor de mercado en vivo, calculando matemáticamente si tu posición está en beneficio o pérdida exacta.
                </p>
            </div>

            <div class="glass-panel p-8 rounded-3xl hover:bg-white/5 transition-colors border border-white/5 hover:border-brand/30 group">
                <div class="w-12 h-12 rounded-xl bg-dark-900 border border-white/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">3. Simulador Hipotecario</h3>
                <p class="text-sm text-gray-400 leading-relaxed">
                    Herramienta de precisión. En lugar de usar matemáticas básicas de navegador, esta interfaz envía los datos por consola a un <strong class="text-white">ejecutable compilado en Java (.jar)</strong> alojado en el servidor, que procesa la lógica de amortización financiera y devuelve el resultado a PHP.
                </p>
            </div>

            <div class="glass-panel p-8 rounded-3xl hover:bg-white/5 transition-colors border border-white/5 hover:border-brand/30 group">
                <div class="w-12 h-12 rounded-xl bg-dark-900 border border-white/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">4. Planificador (Interés Compuesto)</h3>
                <p class="text-sm text-gray-400 leading-relaxed">
                    Proyector exponencial de riqueza. Utiliza la fórmula del interés compuesto para predecir el crecimiento a largo plazo basándose en aportaciones mensuales. Renderiza un gráfico dinámico donde se aprecia visualmente el efecto "bola de nieve" de los intereses sobre intereses.
                </p>
            </div>

            <div class="glass-panel p-8 rounded-3xl hover:bg-white/5 transition-colors border border-white/5 hover:border-brand/30 group md:col-span-2">
                <div class="w-12 h-12 rounded-xl bg-dark-900 border border-white/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">5. Movimiento FIRE (Independencia Financiera)</h3>
                <p class="text-sm text-gray-400 leading-relaxed max-w-3xl">
                    Implementación del <strong class="text-white">"Trinity Study"</strong> de la Universidad de Trinity. Este módulo calcula cuál es tu "Número de Libertad" (el capital exacto que necesitas acumular) basándose en tus gastos mensuales y la regla segura del retiro del 4%. Incluye una barra de progreso que lee tus ahorros actuales y te dice cuánto te falta para poder jubilarte.
                </p>
            </div>

        </div>

        <div class="mt-12 text-center text-gray-600 text-xs font-mono uppercase tracking-widest">
            InvestFlow App - Trabajo de Fin de Grado - Creado para evaluación académica
        </div>
    </main>

</body>
</html>