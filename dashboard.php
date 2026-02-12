<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['usuario_id'];

// Función para obtener precios reales de la bolsa/cripto (Binance)
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
    
    // Evitar errores si el precio de compra es 0
    if ($inv['valor_actual'] > 0) {
        $valorHoy = ($inv['cantidad_invertida'] * $precioLive) / $inv['valor_actual'];
    } else {
        $valorHoy = $inv['cantidad_invertida'];
    }

    $totalInvertido += $inv['cantidad_invertida'];
    $valorActualTotal += $valorHoy;
    
    // Preparar datos para el gráfico
    $labels[] = $inv['activo'];
    $dataChart[] = $valorHoy;
}

$beneficioGlobal = $valorActualTotal - $totalInvertido;
$porcentajeCrecimiento = ($totalInvertido > 0) ? ($beneficioGlobal / $totalInvertido) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <title>InvestFlow - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: { 900: '#0b111b', 800: '#162130', 700: '#1f2d40' },
                        brand: '#00ff88'
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0b111b; font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; height: 100vh; position: fixed; border-right: 1px solid #1f2d40; }
        .main-content { margin-left: 260px; padding: 40px; }
        /* Efecto ROJO en Hover para Cerrar Sesión */
        .logout-btn:hover { color: #ef4444 !important; background: rgba(239, 68, 68, 0.1); }
    </style>
</head>
<body class="text-gray-100 flex">

    <aside class="sidebar bg-dark-900 p-6 flex flex-col justify-between shadow-2xl">
        <div>
            <div class="flex items-center gap-3 mb-10 px-2">
                <div class="w-10 h-10 bg-brand rounded-xl flex items-center justify-center text-dark-900 font-black shadow-[0_0_20px_rgba(0,255,136,0.3)]">$</div>
                <span class="text-2xl font-black tracking-tighter">Invest<span class="text-brand">Flow</span></span>
            </div>
            
            <nav class="space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 text-brand font-bold bg-brand/10 py-3 px-4 rounded-xl">
                    <span>Resumen General</span>
                </a>
                <a href="inversiones.php" class="flex items-center gap-3 text-gray-400 hover:text-brand transition-all py-3 px-4 rounded-xl hover:bg-dark-800">
                    <span>Mis Inversiones</span>
                </a>
                <a href="hipotecas.php" class="flex items-center gap-3 text-gray-400 hover:text-brand transition-all py-3 px-4 rounded-xl hover:bg-dark-800">
                    <span>Simulador Hipotecario</span>
                </a>
            </nav>
        </div>

        <a href="logout.php" class="logout-btn flex items-center gap-3 text-gray-500 transition-all py-3 px-4 rounded-xl font-medium">
            <span>Cerrar Sesión</span>
        </a>
    </aside>

    <main class="main-content flex-1">
        <header class="mb-10">
    <h1 class="text-5xl font-black tracking-tight">
        Hola de nuevo, <span class="text-brand"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
    </h1>
    <p class="text-gray-500 mt-2">Este es el estado actual de tu patrimonio financiero.</p>
</header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-dark-800 p-8 rounded-[2.5rem] border border-dark-700 shadow-xl">
                <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-2">Patrimonio Neto</p>
                <h2 class="text-4xl font-black text-white"><?php echo number_format($valorActualTotal, 2); ?> €</h2>
            </div>

            <div class="bg-dark-800 p-8 rounded-[2.5rem] border border-dark-700 shadow-xl">
                <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-2">Total Invertido</p>
                <h2 class="text-4xl font-black text-gray-300"><?php echo number_format($totalInvertido, 2); ?> €</h2>
            </div>

            <div class="bg-dark-800 p-8 rounded-[2.5rem] border border-brand/20 bg-brand/5 shadow-xl">
                <p class="text-brand text-xs font-bold uppercase tracking-widest mb-2">Beneficio Total</p>
                <h2 class="text-4xl font-black <?php echo $beneficioGlobal >= 0 ? 'text-brand' : 'text-red-500'; ?>">
                    <?php echo ($beneficioGlobal >= 0 ? '+' : '') . number_format($beneficioGlobal, 2); ?> €
                </h2>
                <span class="text-xs font-bold opacity-60"><?php echo number_format($porcentajeCrecimiento, 1); ?>% de rentabilidad</span>
            </div>
        </div>

        <div class="grid lg:grid-cols-12 gap-8">
            <div class="lg:col-span-7 bg-dark-800 p-10 rounded-[2.5rem] border border-dark-700 shadow-2xl flex flex-col items-center">
                <h3 class="text-lg font-bold mb-8 text-gray-400 self-start uppercase tracking-widest">Distribución de Activos</h3>
                <div class="w-full max-w-md">
                    <canvas id="portfolioChart"></canvas>
                </div>
            </div>

            <div class="lg:col-span-5 bg-dark-800 p-10 rounded-[2.5rem] border border-dark-700 shadow-2xl">
                <h3 class="text-lg font-bold mb-6 text-gray-400 uppercase tracking-widest">Consejo Financiero</h3>
                <div class="p-6 bg-dark-900/50 rounded-3xl border border-dark-700 leading-relaxed text-gray-400">
                    <?php if ($beneficioGlobal > 0): ?>
                        ¡Buen trabajo! Tu cartera está creciendo un **<?php echo number_format($porcentajeCrecimiento, 1); ?>%**. Considera diversificar en nuevos activos para proteger tus ganancias.
                    <?php else: ?>
                        El mercado está volátil. Mantén la calma y revisa tus posiciones a largo plazo. La paciencia es la clave del éxito.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('portfolioChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($dataChart); ?>,
                    backgroundColor: ['#00ff88', '#00cc6e', '#1f2d40', '#3b82f6', '#ef4444', '#f59e0b'],
                    borderWidth: 0,
                    hoverOffset: 20
                }]
            },
            options: {
                cutout: '80%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#9ca3af', padding: 20, font: { size: 12, weight: '600' } } }
                }
            }
        });
    </script>
</body>
</html