<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['usuario_id'];

// 1. Obtener preferencias de divisa y privacidad del usuario
$stmtUser = $conexion->prepare("SELECT moneda, modo_privacidad FROM usuarios WHERE id = ?");
$stmtUser->execute([$uid]);
$userPrefs = $stmtUser->fetch();

$monedaUsuario = $userPrefs['moneda'] ?? 'EUR';
$modoPrivacidad = $userPrefs['modo_privacidad'] ?? 0;
$simboloMoneda = ($monedaUsuario === 'USD') ? '$' : '€';

// Función para obtener precios de mercado siempre en Euros (Moneda base de la BBDD)
function getLivePriceEUR($activo) {
    $activo = strtoupper(trim($activo));
    $map = ['BITCOIN' => 'BTCEUR', 'ETHEREUM' => 'ETHEUR', 'SOLANA' => 'SOLEUR', 'APPLE' => 'AAPLUSDT']; 
    $symbol = $map[$activo] ?? $activo . "EUR";

    $url = "https://api.binance.com/api/v3/ticker/price?symbol=$symbol";
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $response = @file_get_contents($url, false, $ctx);
    
    if ($response) {
        $data = json_decode($response, true);
        $precio = isset($data['price']) ? floatval($data['price']) : null;
        
        // Corrección técnica para acciones americanas como Apple que cotizan en USD de forma nativa en la API
        if ($symbol === 'AAPLUSDT' && $precio !== null) {
            $precio = $precio / 1.08; // Conversión inversa provisional a EUR
        }
        return $precio;
    }
    return null;
}

// Función para obtener la tasa de cambio real de Euro a Dólar desde Binance
function getEURUSDExchangeRate() {
    $url = "https://api.binance.com/api/v3/ticker/price?symbol=EURUSDT";
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $response = @file_get_contents($url, false, $ctx);
    if ($response) {
        $data = json_decode($response, true);
        return isset($data['price']) ? floatval($data['price']) : 1.08;
    }
    return 1.08; // Fallback clásico en caso de caída de red
}

// 2. Obtener todas las inversiones del usuario
$stmt = $conexion->prepare("SELECT * FROM inversiones WHERE usuario_id = ?");
$stmt->execute([$uid]);
$inversiones = $stmt->fetchAll();

$totalInvertidoEUR = 0;
$valorActualTotalEUR = 0;
$labels = [];
$dataChartEUR = [];

// 3. Procesar y consolidar la contabilidad de la cartera en Euros
foreach ($inversiones as $inv) {
    $precioLiveEUR = getLivePriceEUR($inv['activo']) ?? $inv['valor_actual'];
    
    if ($inv['valor_actual'] > 0) {
        $valorHoyEUR = ($inv['cantidad_invertida'] * $precioLiveEUR) / $inv['valor_actual'];
    } else {
        $valorHoyEUR = $inv['cantidad_invertida'];
    }

    $totalInvertidoEUR += $inv['cantidad_invertida'];
    $valorActualTotalEUR += $valorHoyEUR;
    
    $labels[] = strtoupper($inv['activo']);
    $dataChartEUR[] = $valorHoyEUR;
}

$beneficioGlobalEUR = $valorActualTotalEUR - $totalInvertidoEUR;
$porcentajeCrecimiento = ($totalInvertidoEUR > 0) ? ($beneficioGlobalEUR / $totalInvertidoEUR) * 100 : 0;

// 4. Aplicar el multiplicador cambiario si el usuario solicita ver el sistema en USD
$tasaCambio = 1.0;
if ($monedaUsuario === 'USD') {
    $tasaCambio = getEURUSDExchangeRate();
}

$valorActualTotal = $valorActualTotalEUR * $tasaCambio;
$totalInvertido = $totalInvertidoEUR * $tasaCambio;
$beneficioGlobal = $beneficioGlobalEUR * $tasaCambio;

$dataChart = [];
foreach ($dataChartEUR as $valorEUR) {
    $dataChart[] = round($valorEUR * $tasaCambio, 2);
}

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
    <link rel="stylesheet" href="style.css">
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
</head>
<body class="text-gray-100 flex relative">

    <div class="fixed top-[-20%] left-[-10%] w-[800px] h-[800px] bg-brand/5 rounded-full blur-[150px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-600/5 rounded-full blur-[150px] pointer-events-none z-0"></div>

    <?php require_once 'sidebar.php'; ?>

    <main class="main-content relative z-10">
        <div class="content-wrapper">
            <header class="mb-12 flex justify-between items-end">
                <div>
                    <p class="text-brand text-xs font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 bg-brand rounded-full animate-pulse shadow-[0_0_8px_#00ffa3]"></span>
                        Terminal
                    </p>
                    <h1 class="text-5xl font-black tracking-tight text-white">Panel General</h1>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="glass-panel p-8 rounded-[2rem] relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10 group-hover:bg-white/10 transition-colors"></div>
                    <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-2">Patrimonio Neto Estimado</p>
                    <h2 class="text-4xl font-black text-white">
                        <?php echo $modoPrivacidad ? '••••••' : number_format($valorActualTotal, 2, ',', '.'); ?> 
                        <span class="text-brand text-2xl"><?= $simboloMoneda ?></span>
                    </h2>
                </div>

                <div class="glass-panel p-8 rounded-[2rem] relative overflow-hidden">
                    <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-2">Total Invertido</p>
                    <h2 class="text-4xl font-black text-gray-300">
                        <?php echo $modoPrivacidad ? '••••••' : number_format($totalInvertido, 2, ',', '.'); ?> 
                        <span class="text-gray-500 text-2xl"><?= $simboloMoneda ?></span>
                    </h2>
                </div>

                <div class="glass-panel p-8 rounded-[2rem] border bg-gradient-to-br <?php echo $bgBeneficio; ?> <?php echo $glowBeneficio; ?> relative overflow-hidden">
                    <p class="<?php echo $colorBeneficio; ?> text-[10px] font-black uppercase tracking-widest mb-2 opacity-80">Rendimiento Total</p>
                    <h2 class="text-4xl font-black <?php echo $colorBeneficio; ?>">
                        <?php 
                        if ($modoPrivacidad) {
                            echo "••••••";
                        } else {
                            echo ($beneficioGlobal >= 0 ? '+' : '') . number_format($beneficioGlobal, 2, ',', '.'); 
                        }
                        ?> 
                        <span class="text-2xl"><?= $simboloMoneda ?></span>
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
                                <p class="text-white text-sm leading-relaxed font-medium">El sistema está a la espera de datos.</p>
                                <p class="text-gray-400 text-xs leading-relaxed mt-4">Ingresa datos en tu cartera para que el motor algorítmico pueda calcular tu rendimiento, dibujar las proyecciones y emitir consejos personalizados.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once 'footer.php'; ?>
    </main>

    <?php if (count($labels) > 0): ?>
    <script>
        const ctx = document.getElementById('portfolioChart').getContext('2d');
        Chart.defaults.color = '#6b7280';
        Chart.defaults.font.family = "'Outfit', sans-serif";
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($dataChart); ?>,
                    backgroundColor: ['#00ffa3', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4'],
                    borderWidth: 2,
                    borderColor: '#030712',
                    hoverOffset: 15,
                    hoverBorderColor: '#1f2937'
                }]
            },
            options: {
                cutout: '75%',
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: 20 },
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, font: { size: 11, weight: '800' }, usePointStyle: true, boxWidth: 8 } },
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
                                    // Sincronización de Chart.js con la divisa dinámica del usuario
                               label += new Intl.NumberFormat('es-ES', { style: 'currency', currency: '<?= $monedaUsuario ?>' }).format(context.parsed);
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