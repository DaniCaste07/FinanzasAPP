<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['usuario_id'];

// Función para obtener precios reales (Binance API con protección anti-cuelgues)
function getLivePrice($activo) {
    $activo = strtoupper(trim($activo));
    $map = ['BITCOIN' => 'BTCEUR', 'ETHEREUM' => 'ETHEUR', 'SOLANA' => 'SOLEUR', 'APPLE' => 'AAPLUSDT']; 
    $symbol = $map[$activo] ?? $activo . "EUR";

    $url = "https://api.binance.com/api/v3/ticker/price?symbol=$symbol";
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $response = @file_get_contents($url, false, $ctx);
    
    if ($response) {
        $data = json_decode($response, true);
        return isset($data['price']) ? floatval($data['price']) : null;
    }
    return null;
}

// 1. Obtener todas las inversiones del usuario
$stmt = $conexion->prepare("SELECT * FROM inversiones WHERE usuario_id = ?");
$stmt->execute([$uid]);
$inversiones = $stmt->fetchAll();

$totalInvertido = 0;
$valorActualTotal = 0;
$labels = [];
$dataChart = [];

// 2. Calcular métricas globales en tiempo real
foreach ($inversiones as $inv) {
    $precioLive = getLivePrice($inv['activo']) ?? $inv['valor_actual'];
    
    if ($inv['valor_actual'] > 0) {
        $valorHoy = ($inv['cantidad_invertida'] * $precioLive) / $inv['valor_actual'];
    } else {
        $valorHoy = $inv['cantidad_invertida'];
    }

    $totalInvertido += $inv['cantidad_invertida'];
    $valorActualTotal += $valorHoy;
    
    $labels[] = strtoupper($inv['activo']);
    $dataChart[] = round($valorHoy, 2);
}

$beneficioGlobal = $valorActualTotal - $totalInvertido;
$porcentajeCrecimiento = ($totalInvertido > 0) ? ($beneficioGlobal / $totalInvertido) * 100 : 0;

// Variables dinámicas de color para ganancias/pérdidas
$colorBeneficio = $beneficioGlobal >= 0 ? 'text-brand' : 'text-red-500';
$bgBeneficio = $beneficioGlobal >= 0 ? 'from-brand/10 to-transparent border-brand/20' : 'from-red-500/10 to-transparent border-red-500/20';
$glowBeneficio = $beneficioGlobal >= 0 ? 'shadow-[0_0_30px_rgba(0,255,163,0.15)]' : 'shadow-[0_0_30px_rgba(239,68,68,0.15)]';
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Dashboard Terminal</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <div class="fixed top-[-20%] left-[-10%] w-[800px] h-[800px] bg-brand/5 rounded-full blur-[150px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-600/5 rounded-full blur-[150px] pointer-events-none z-0"></div>

    <aside class="sidebar bg-dark-950/80 backdrop-blur-xl p-6 flex flex-col justify-between shadow-2xl">
        <div>
            <div class="flex items-center gap-3 mb-12 px-2 mt-4 cursor-pointer hover:scale-105 transition-transform">
                <div class="w-10 h-10 bg-brand rounded-xl flex items-center justify-center text-dark-950 font-black text-xl shadow-[0_0_20px_rgba(0,255,163,0.4)]">IF</div>
                <span class="text-2xl font-black tracking-tight text-white">Invest<span class="text-brand">Flow</span></span>
            </div>
            
            <nav class="space-y-2">
                <a href="dashboard.php" class="nav-active flex items-center gap-3 py-3 px-4 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Resumen General
                </a>
                <a href="inversiones.php" class="nav-link flex items-center gap-3 text-gray-400 py-3 px-4 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    Mis Inversiones
                </a>
                <a href="hipotecas.php" class="nav-link flex items-center gap-3 text-gray-400 py-3 px-4 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Simulador Hipotecario
                </a>
                <a href="planificador.php" class="nav-link flex items-center gap-3 text-gray-400 py-3 px-4 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Planificador
                </a>
                <a href="libertad.php" class="nav-link flex items-center gap-3 text-gray-400 py-3 px-4 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Libertad Financiera
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
                    Terminal Operativo Activo
                </p>
                <h1 class="text-5xl font-black tracking-tight text-white">
                    Panel General
                </h1>
            </div>
            <div class="hidden lg:block text-right">
                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-1">Cifrado AES-256</p>
                <p class="font-mono text-sm text-gray-400 bg-dark-900/50 py-1.5 px-3 rounded-lg border border-white/5">IP: SECURE-CONNECTION</p>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            
            <div class="glass-panel p-8 rounded-[2rem] relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10 group-hover:bg-white/10 transition-colors"></div>
                <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-2">Patrimonio Neto Estimado</p>
                <h2 class="text-4xl font-black text-white"><?php echo number_format($valorActualTotal, 2, ',', '.'); ?> <span class="text-brand text-2xl">€</span></h2>
            </div>

            <div class="glass-panel p-8 rounded-[2rem] relative overflow-hidden">
                <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-2">Total Invertido</p>
                <h2 class="text-4xl font-black text-gray-300"><?php echo number_format($totalInvertido, 2, ',', '.'); ?> <span class="text-gray-500 text-2xl">€</span></h2>
            </div>

            <div class="glass-panel p-8 rounded-[2rem] border bg-gradient-to-br <?php echo $bgBeneficio; ?> <?php echo $glowBeneficio; ?> relative overflow-hidden">
                <p class="<?php echo $colorBeneficio; ?> text-[10px] font-black uppercase tracking-widest mb-2 opacity-80">Rendimiento Total</p>
                <h2 class="text-4xl font-black <?php echo $colorBeneficio; ?>">
                    <?php echo ($beneficioGlobal >= 0 ? '+' : '') . number_format($beneficioGlobal, 2, ',', '.'); ?> <span class="text-2xl">€</span>
                </h2>
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-xs font-black uppercase px-2 py-1 rounded bg-dark-950/50 <?php echo $colorBeneficio; ?>">
                        <?php echo ($beneficioGlobal >= 0 ? '▲ ' : '▼ ') . number_format($porcentajeCrecimiento, 2); ?>% ROI
                    </span>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-12 gap-6">
            
            <div class="lg:col-span-7 glass-panel p-8 rounded-[2rem] flex flex-col items-center relative">
                <div class="w-full flex justify-between items-center mb-6">
                    <h3 class="text-sm font-black text-white uppercase tracking-widest">Distribución de Activos</h3>
                    <span class="text-[10px] text-gray-500 uppercase bg-dark-950 py-1 px-3 rounded-full border border-white/5">Tiempo Real</span>
                </div>
                
                <div class="w-full max-w-sm flex-grow flex items-center justify-center">
                    <?php if (count($labels) > 0): ?>
                        <canvas id="portfolioChart"></canvas>
                    <?php else: ?>
                        <div class="text-center text-gray-500 py-20">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <p class="font-bold text-sm">Aún no hay activos registrados.</p>
                            <a href="inversiones.php" class="text-brand text-xs font-bold mt-2 inline-block border-b border-brand/30 pb-0.5 hover:border-brand">Añadir primera inversión →</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-5 glass-panel p-8 rounded-[2rem] flex flex-col">
                <div class="flex items-center gap-3 mb-6 pb-6 border-b border-white/5">
                    <div class="w-8 h-8 rounded bg-blue-500/20 text-blue-400 flex items-center justify-center border border-blue-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest">InvestFlow AI</h3>
                        <p class="text-[10px] text-gray-500 uppercase">Análisis algorítmico activo</p>
                    </div>
                </div>

                <div class="p-6 bg-dark-950/50 rounded-2xl border border-white/5 flex-grow relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1 h-full bg-brand/30">
                        <div class="w-full h-1/3 bg-brand animate-[float_3s_ease-in-out_infinite] shadow-[0_0_10px_#00ffa3]"></div>
                    </div>
                    
                    <div class="pl-4">
                        <?php if ($beneficioGlobal > 0): ?>
                            <p class="text-white text-sm leading-relaxed font-medium">
                                Análisis completado. Tu cartera muestra una fuerte tendencia alcista con un <span class="text-brand font-black bg-brand/10 px-1.5 py-0.5 rounded"><?php echo number_format($porcentajeCrecimiento, 1); ?>% de crecimiento</span>. 
                            </p>
                            <p class="text-gray-400 text-xs leading-relaxed mt-4">
                                <span class="font-bold text-gray-300">Recomendación:</span> Considera proteger parte de estas ganancias trasladando capital a activos de menor volatilidad o utilizando el <em>Planificador</em> para proyectar este rendimiento a largo plazo.
                            </p>
                        <?php elseif ($beneficioGlobal < 0 && $totalInvertido > 0): ?>
                            <p class="text-white text-sm leading-relaxed font-medium">
                                Se ha detectado una corrección en el mercado. Actualmente tu exposición refleja una variación de <span class="text-red-400 font-black bg-red-400/10 px-1.5 py-0.5 rounded"><?php echo number_format($porcentajeCrecimiento, 1); ?>%</span>.
                            </p>
                            <p class="text-gray-400 text-xs leading-relaxed mt-4">
                                <span class="font-bold text-gray-300">Recomendación:</span> Históricamente, las ventas en pánico consolidan las pérdidas. Revisa el estado de la red en la pestaña <em>Mis Inversiones</em> y evalúa si los fundamentales técnicos de tus activos han cambiado.
                            </p>
                        <?php else: ?>
                            <p class="text-white text-sm leading-relaxed font-medium">
                                El sistema está a la espera de datos.
                            </p>
                            <p class="text-gray-400 text-xs leading-relaxed mt-4">
                                Ingresa datos en tu cartera para que el motor algorítmico pueda calcular tu rendimiento, dibujar las proyecciones y emitir consejos personalizados.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php if (count($labels) > 0): ?>
    <script>
        // Configuración profesional del Chart.js con paleta Ciberpunk
        const ctx = document.getElementById('portfolioChart').getContext('2d');
        
        // Hacemos un degradado dinámico para la leyenda
        Chart.defaults.color = '#6b7280';
        Chart.defaults.font.family = "'Outfit', sans-serif";
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($dataChart); ?>,
                    // Paleta de colores Neón/Dark Mode
                    backgroundColor: [
                        '#00ffa3', // Brand Green
                        '#3b82f6', // Blue
                        '#8b5cf6', // Purple
                        '#ec4899', // Pink
                        '#f59e0b', // Amber
                        '#10b981', // Emerald
                        '#06b6d4'  // Cyan
                    ],
                    borderWidth: 2,
                    borderColor: '#030712', // El mismo color del fondo para que parezca que hay hueco
                    hoverOffset: 15,
                    hoverBorderColor: '#1f2937'
                }]
            },
            options: {
                cutout: '75%', // Anillo más delgado y elegante
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: 20
                },
                plugins: {
                    legend: { 
                        position: 'bottom', 
                        labels: { 
                            padding: 20, 
                            font: { size: 11, weight: '800' },
                            usePointStyle: true,
                            boxWidth: 8
                        } 
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleFont: { size: 13, family: "'Outfit', sans-serif" },
                        bodyFont: { size: 14, weight: 'bold', family: "'Outfit', sans-serif" },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(context.parsed);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>