<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['usuario_id'];

// EXTRAEMOS LA PREFERENCIA DE DIVISA DEL USUARIO
$stmtUser = $conexion->prepare("SELECT moneda FROM usuarios WHERE id = ?");
$stmtUser->execute([$uid]);
$userPrefs = $stmtUser->fetch();

$monedaUsuario = $userPrefs['moneda'] ?? 'EUR';
$simboloMoneda = ($monedaUsuario === 'USD') ? '$' : '€';

// Tasa de cambio dinámica de Euro a Dólar desde Binance
$tasaCambio = 1.0;
if ($monedaUsuario === 'USD') {
    $url = "https://api.binance.com/api/v3/ticker/price?symbol=EURUSDT";
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $response = @file_get_contents($url, false, $ctx);
    if ($response) {
        $data = json_decode($response, true);
        $tasaCambio = isset($data['price']) ? floatval($data['price']) : 1.08;
    } else {
        $tasaCambio = 1.08; // Fallback clásico
    }
}
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Planificación Patrimonial</title>
    
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
    <style>
        body { background-color: #030712; font-family: 'Outfit', sans-serif; overflow-x: hidden; }
        .main-content { margin-left: 280px; min-height: 100vh; display: flex; flex-direction: column; }
        .content-wrapper { padding: 40px; flex-grow: 1; }
        
        .glass-panel { 
            background: rgba(17, 24, 39, 0.6); 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05); 
        }

        /* Sliders Neón Generales (Verdes) */
        input[type=range] { 
            -webkit-appearance: none; 
            background: rgba(255,255,255,0.05); 
            height: 8px; 
            border-radius: 8px; 
            outline: none;
            border: 1px solid rgba(255,255,255,0.1);
        }
        input[type=range]::-webkit-slider-thumb { 
            -webkit-appearance: none; 
            height: 24px; 
            width: 24px; 
            border-radius: 50%; 
            background: #00ffa3; 
            cursor: pointer; 
            box-shadow: 0 0 15px rgba(0, 255, 163, 0.6); 
            transition: transform 0.1s;
        }
        input[type=range]::-moz-range-thumb {
            background: #00ffa3; 
            box-shadow: 0 0 15px rgba(0, 255, 163, 0.6); 
            border: none;
            height: 24px; 
            width: 24px; 
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.1s;
        }
        input[type=range]::-webkit-slider-thumb:hover { transform: scale(1.2); }
        input[type=range]::-moz-range-thumb:hover { transform: scale(1.2); }

        /* Sliders Morados para la pestaña FIRE */
        input[type=range].slider-purple::-webkit-slider-thumb { 
            background: #a855f7; 
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.6); 
        }
        input[type=range].slider-purple::-moz-range-thumb { 
            background: #a855f7; 
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.6); 
        }
        input[type=range].slider-purple::-webkit-slider-thumb:hover { transform: scale(1.2); }
        input[type=range].slider-purple::-moz-range-thumb:hover { transform: scale(1.2); }
        
        .progress-bar-transition { transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); }
        
        /* Animaciones para las pestañas */
        .tab-content { display: none; opacity: 0; transition: opacity 0.4s ease-in-out; }
        .tab-content.active { display: block; opacity: 1; }
    </style>
</head>
<body class="text-gray-100 flex relative">

    <div class="fixed top-[-20%] left-[-10%] w-[800px] h-[800px] bg-brand/5 rounded-full blur-[150px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-600/5 rounded-full blur-[150px] pointer-events-none z-0"></div>

    <?php require_once 'sidebar.php'; ?>

    <main class="main-content relative z-10">
        <div class="content-wrapper">
            
            <header class="mb-10 flex justify-between items-end">
                <div>
                    <p class="text-brand text-xs font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 bg-brand rounded-full animate-pulse shadow-[0_0_8px_#00ffa3]"></span>
                        Hub de Proyección
                    </p>
                    <h1 class="text-5xl font-black tracking-tight text-white">
                        Planificación Patrimonial
                    </h1>
                </div>
            </header>

            <div class="flex gap-4 mb-8 bg-dark-900/50 p-2 rounded-2xl w-max border border-white/5 shadow-inner">
                <button onclick="switchTab('tab-compound')" id="btn-compound" class="px-8 py-3 rounded-xl text-sm font-black uppercase tracking-widest transition-all duration-300 bg-brand text-dark-950 shadow-[0_0_15px_rgba(0,255,163,0.3)]">
                    Interés Compuesto
                </button>
                <button onclick="switchTab('tab-fire')" id="btn-fire" class="px-8 py-3 rounded-xl text-sm font-black uppercase tracking-widest text-gray-500 hover:text-white transition-all duration-300">
                    Movimiento FIRE
                </button>
            </div>

            <div id="tab-compound" class="tab-content active">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <div class="lg:col-span-4 glass-panel p-10 rounded-[2.5rem] shadow-2xl flex flex-col justify-center space-y-8">
                        
                        <div class="border-b border-white/5 pb-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 rounded bg-brand/10 flex items-center justify-center border border-brand/20">
                                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                </div>
                                <h3 class="text-sm font-black text-gray-300 uppercase tracking-widest">Parámetros</h3>
                            </div>
                            <p class="text-xs text-gray-500 font-medium leading-relaxed">
                                Modifica las variables para visualizar cómo el efecto "bola de nieve" multiplicará tu patrimonio.
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div class="flex justify-between items-end">
                                <label class="text-gray-400 font-bold text-xs uppercase tracking-widest">Capital Inicial</label>
                                <span class="text-xl font-black text-white"><span id="vCap">1.000</span> <span class="text-brand text-sm"><?= $simboloMoneda ?></span></span>
                            </div>
                            <input type="range" id="rCap" class="w-full" min="0" max="50000" step="500" value="1000" oninput="calc()">
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-end">
                                <label class="text-gray-400 font-bold text-xs uppercase tracking-widest">Aportación Mensual</label>
                                <span class="text-xl font-black text-white"><span id="vMes">200</span> <span class="text-brand text-sm"><?= $simboloMoneda ?></span></span>
                            </div>
                            <input type="range" id="rMes" class="w-full" min="0" max="3000" step="50" value="200" oninput="calc()">
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-end">
                                <label class="text-gray-400 font-bold text-xs uppercase tracking-widest">Rentabilidad Anual</label>
                                <span class="text-xl font-black text-white"><span id="vInt">8.0</span> <span class="text-brand text-sm">%</span></span>
                            </div>
                            <input type="range" id="rInt" class="w-full" min="1" max="20" step="0.5" value="8" oninput="calc()">
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-end">
                                <label class="text-gray-400 font-bold text-xs uppercase tracking-widest">Tiempo Estimado</label>
                                <span class="text-xl font-black text-white"><span id="vAnn">20</span> <span class="text-brand text-sm">Años</span></span>
                            </div>
                            <input type="range" id="rAnn" class="w-full" min="1" max="50" step="1" value="20" oninput="calc()">
                        </div>

                    </div>

                    <div class="lg:col-span-8 space-y-6 flex flex-col">
                        <div class="glass-panel p-10 rounded-[2.5rem] bg-gradient-to-br from-brand/10 to-transparent border-brand/20 relative overflow-hidden group shadow-[0_0_30px_rgba(0,255,163,0.1)]">
                            <div class="absolute -right-10 -top-10 w-40 h-40 bg-brand/20 blur-3xl rounded-full"></div>
                            <p class="text-brand font-black uppercase text-xs tracking-widest mb-2 opacity-80">Capital Final Estimado</p>
                            <h2 class="text-6xl lg:text-7xl font-black text-white tracking-tighter" id="resFinal">0 <span class="text-4xl text-brand"><?= $simboloMoneda ?></span></h2>
                            
                            <div class="mt-6 flex gap-6 border-t border-white/5 pt-6">
                                <div>
                                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Total Invertido</p>
                                    <p class="font-mono text-gray-300 font-bold" id="resAportado">0 <?= $simboloMoneda ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Intereses Generados</p>
                                    <p class="font-mono text-brand font-bold" id="resGanado">0 <?= $simboloMoneda ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="glass-panel p-6 rounded-[2.5rem] flex-grow relative">
                            <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4 ml-2">Curva de Crecimiento Exponencial</h3>
                            <div class="w-full h-64 relative">
                                <canvas id="growthChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

            <div id="tab-fire" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
                    <div class="lg:col-span-5 glass-panel p-10 rounded-[2.5rem] shadow-2xl flex flex-col justify-center space-y-12">
                        <div class="flex items-center gap-3 border-b border-white/5 pb-4">
                            <div class="w-8 h-8 rounded bg-purple-500/20 flex items-center justify-center border border-purple-500/30">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest">Variables de Vida</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="flex justify-between items-end">
                                <label class="text-gray-400 font-bold text-sm uppercase tracking-wider">Gastos Mensuales</label>
                                <span class="text-3xl font-black text-white"><span id="vGasto">1.500</span> <span class="text-purple-400 text-lg"><?= $simboloMoneda ?></span></span>
                            </div>
                            <input type="range" id="rGasto" class="w-full slider-purple" min="500" max="10000" step="100" value="1500" oninput="calcLibertad()">
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-end">
                                <label class="text-gray-400 font-bold text-sm uppercase tracking-wider">Tu Ahorro Actual</label>
                                <span class="text-3xl font-black text-white"><span id="vAhorro">30.000</span> <span class="text-purple-400 text-lg"><?= $simboloMoneda ?></span></span>
                            </div>
                            <input type="range" id="rAhorro" class="w-full slider-purple" min="0" max="1000000" step="5000" value="30000" oninput="calcLibertad()">
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-end">
                                <label class="text-gray-400 font-bold text-sm uppercase tracking-wider flex flex-col">
                                    Tasa de Retiro
                                    <span class="text-[10px] text-gray-600 normal-case tracking-normal">Recomendada: 4.0%</span>
                                </label>
                                <span class="text-3xl font-black text-white"><span id="vRetiro">4.0</span> <span class="text-purple-400 text-lg">%</span></span>
                            </div>
                            <input type="range" id="rRetiro" class="w-full slider-purple" min="2" max="6" step="0.1" value="4" oninput="calcLibertad()">
                        </div>
                    </div>

                    <div class="lg:col-span-7 space-y-8 flex flex-col">
                        <div class="glass-panel p-10 rounded-[2.5rem] bg-gradient-to-br from-purple-500/10 to-transparent border-purple-500/20 relative overflow-hidden group shadow-[0_0_30px_rgba(168,85,247,0.1)]">
                            <div class="absolute -right-10 -top-10 w-40 h-40 bg-purple-500/20 blur-3xl rounded-full"></div>
                            <p class="text-purple-400 font-black uppercase text-xs tracking-widest mb-2 opacity-80">Tu Meta de Capital (Número FIRE)</p>
                            <h2 class="text-6xl lg:text-7xl font-black text-white tracking-tighter" id="resMeta">0 <span class="text-4xl text-purple-400"><?= $simboloMoneda ?></span></h2>
                            
                            <div class="mt-6 border-t border-purple-500/20 pt-6">
                                <p class="text-gray-300 text-sm leading-relaxed font-medium" id="resInfo"></p>
                            </div>
                        </div>

                        <div class="glass-panel p-10 rounded-[2.5rem] flex-grow relative overflow-hidden flex flex-col justify-center">
                            <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-3">
                                Progreso del Objetivo <span id="statusIcon" class="text-purple-400 animate-bounce text-xl">🚀</span>
                            </h3>

                            <div class="h-8 w-full bg-dark-950 rounded-full overflow-hidden p-1 border border-white/5 shadow-inner">
                                <div id="progressBar" class="h-full bg-gradient-to-r from-purple-500/50 to-purple-500 rounded-full progress-bar-transition relative shadow-[0_0_20px_rgba(168,85,247,0.5)]" style="width: 0%">
                                     <div class="absolute right-0 top-0 h-full w-2 bg-white blur-[2px]"></div>
                                </div>
                            </div>

                            <div class="flex justify-between mt-6">
                                <div class="flex flex-col">
                                    <span id="progressPercent" class="text-purple-400 text-4xl font-black font-mono tracking-tighter">0.0%</span>
                                    <span class="text-gray-500 text-[10px] font-bold uppercase tracking-widest mt-1">Completado</span>
                                </div>
                                <div class="text-right flex flex-col items-end">
                                     <span class="text-white text-xl font-bold font-mono tracking-tighter flex items-center gap-2">🏁 META</span>
                                    <span id="progressGoalLabel" class="text-gray-400 text-xs font-bold font-mono mt-1">0 <?= $simboloMoneda ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <?php require_once 'footer.php'; ?>
    </main>

    <script>
        // --- MOTOR DE DIVISAS Y FORMATEO LIMPIO ---
        const simbolo = '<?= $simboloMoneda ?>';
        const tasaCambioJS = <?= $tasaCambio ?>;

        function formatearDineroLimpio(valor) {
            const numeroCrudo = new Intl.NumberFormat('es-ES', {minimumFractionDigits: 0, maximumFractionDigits: 0}).format(valor);
            return numeroCrudo + ' ' + simbolo;
        }

        // --- LÓGICA DE PESTAÑAS ---
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');

            const btnCompound = document.getElementById('btn-compound');
            const btnFire = document.getElementById('btn-fire');

            if(tabId === 'tab-compound') {
                btnCompound.className = "px-8 py-3 rounded-xl text-sm font-black uppercase tracking-widest transition-all duration-300 bg-brand text-dark-950 shadow-[0_0_15px_rgba(0,255,163,0.3)]";
                btnFire.className = "px-8 py-3 rounded-xl text-sm font-black uppercase tracking-widest text-gray-500 hover:text-white transition-all duration-300";
            } else {
                btnFire.className = "px-8 py-3 rounded-xl text-sm font-black uppercase tracking-widest transition-all duration-300 bg-purple-500 text-white shadow-[0_0_15px_rgba(168,85,247,0.3)]";
                btnCompound.className = "px-8 py-3 rounded-xl text-sm font-black uppercase tracking-widest text-gray-500 hover:text-white transition-all duration-300";
                
                if(chart) chart.resize();
            }
        }

        // --- LÓGICA INTERÉS COMPUESTO ---
        let chart;
        function calc() {
            // Base en Euros desde los Sliders
            const initialBase = parseFloat(document.getElementById('rCap').value);
            const monthlyBase = parseFloat(document.getElementById('rMes').value);
            const rate = parseFloat(document.getElementById('rInt').value) / 100 / 12;
            const years = parseInt(document.getElementById('rAnn').value);
            const months = years * 12;

            // UI Slider conversions
            const initialMostrar = initialBase * tasaCambioJS;
            const monthlyMostrar = monthlyBase * tasaCambioJS;

            document.getElementById('vCap').innerText = new Intl.NumberFormat('es-ES', {maximumFractionDigits: 0}).format(initialMostrar);
            document.getElementById('vMes').innerText = new Intl.NumberFormat('es-ES', {maximumFractionDigits: 0}).format(monthlyMostrar);
            document.getElementById('vInt').innerText = (rate*12*100).toFixed(1);
            document.getElementById('vAnn').innerText = years;

            // Lógica Matemática
            let balance = initialBase;
            let history = [initialBase * tasaCambioJS]; // Primer dato en gráfica ya convertido
            let labels = ["Año 0"];
            let totalAportado = initialBase + (monthlyBase * months);

            for (let i = 1; i <= months; i++) {
                balance = (balance + monthlyBase) * (1 + rate);
                if (i % 12 === 0) {
                    history.push(Math.round(balance * tasaCambioJS)); // Convertimos antes de pintar
                    labels.push("Año " + (i / 12));
                }
            }

            const interesesGenerados = balance - totalAportado;

            // Conversión Final
            const balanceMostrar = balance * tasaCambioJS;
            const totalAportadoMostrar = totalAportado * tasaCambioJS;
            const interesesGeneradosMostrar = interesesGenerados * tasaCambioJS;

            document.getElementById('resFinal').innerHTML = Math.round(balanceMostrar).toLocaleString('es-ES') + ' <span class="text-4xl text-brand">' + simbolo + '</span>';
            document.getElementById('resAportado').innerText = formatearDineroLimpio(totalAportadoMostrar);
            document.getElementById('resGanado').innerText = '+' + formatearDineroLimpio(interesesGeneradosMostrar);

            updateChart(labels, history);
        }

        function updateChart(labels, data) {
            const ctx = document.getElementById('growthChart').getContext('2d');
            Chart.defaults.font.family = "'Outfit', sans-serif";
            Chart.defaults.color = '#6b7280';
            
            if (chart) chart.destroy();

            let gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(0, 255, 163, 0.4)');
            gradient.addColorStop(1, 'rgba(0, 255, 163, 0.0)');

            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Patrimonio Estimado',
                        data: data,
                        borderColor: '#00ffa3',
                        borderWidth: 3,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#030712',
                        pointBorderColor: '#00ffa3',
                        pointBorderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: { bottom: 15 } // Solución para que no se corte por abajo
                    },
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    // Tooltip con divisa limpia
                                    return ' ' + formatearDineroLimpio(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: { 
                            grid: { display: false },
                            ticks: { 
                                maxRotation: 0, 
                                autoSkip: true, 
                                maxTicksLimit: 8, 
                                font: { size: 11, weight: 'bold', family: "'Outfit', sans-serif" }
                            }
                        },
                        y: { 
                            grid: { color: 'rgba(255, 255, 255, 0.05)' }, 
                            ticks: { 
                                font: { size: 11, weight: 'bold', family: "'Outfit', sans-serif" },
                                callback: function(value) { 
                                    // Eje Y con divisa dinámica
                                    return value >= 1000000 ? (value/1000000) + 'M ' + simbolo : (value >= 1000 ? (value/1000) + 'k ' + simbolo : value + ' ' + simbolo); 
                                } 
                            }
                        }
                    }
                }
            });
        }

        // --- LÓGICA LIBERTAD FINANCIERA (FIRE) ---
        function calcLibertad() {
            // Base en Euros
            const gastoBase = parseFloat(document.getElementById('rGasto').value);
            const ahorroBase = parseFloat(document.getElementById('rAhorro').value);
            const retiroPct = parseFloat(document.getElementById('rRetiro').value);
            const retiroDecimal = retiroPct / 100;

            // UI Slider conversions
            const gastoMostrar = gastoBase * tasaCambioJS;
            const ahorroMostrar = ahorroBase * tasaCambioJS;

            document.getElementById('vGasto').innerText = new Intl.NumberFormat('es-ES', {maximumFractionDigits: 0}).format(gastoMostrar);
            document.getElementById('vAhorro').innerText = new Intl.NumberFormat('es-ES', {maximumFractionDigits: 0}).format(ahorroMostrar);
            document.getElementById('vRetiro').innerText = retiroPct.toFixed(1);

            // Cálculos matemáticos
            const gastoAnualBase = gastoBase * 12;
            const metaBase = gastoAnualBase / retiroDecimal;
            const metaMostrar = metaBase * tasaCambioJS;
            
            let porcentaje = (ahorroBase / metaBase) * 100;
            if (porcentaje > 100) porcentaje = 100;
            if (isNaN(porcentaje)) porcentaje = 0;

            // UI Textos
            document.getElementById('resMeta').innerHTML = Math.round(metaMostrar).toLocaleString('es-ES') + ' <span class="text-4xl text-purple-400">' + simbolo + '</span>';
            document.getElementById('resInfo').innerHTML = `Con este capital, puedes retirar <strong class="text-purple-400 text-lg">${formatearDineroLimpio(gastoMostrar)} al mes</strong> de forma vitalicia (Regla del ${retiroPct}%).`;

            const bar = document.getElementById('progressBar');
            const percentLabel = document.getElementById('progressPercent');
            const goalLabel = document.getElementById('progressGoalLabel');
            const statusIcon = document.getElementById('statusIcon');

            bar.style.width = `${porcentaje}%`;
            percentLabel.innerText = porcentaje.toFixed(1) + "%";
            goalLabel.innerText = formatearDineroLimpio(metaMostrar);

            if (porcentaje >= 100) {
                statusIcon.innerText = "🏆";
                percentLabel.classList.add('text-white');
                percentLabel.style.textShadow = '0 0 15px rgba(168,85,247,0.8)';
            } else {
                statusIcon.innerText = "🚀";
                percentLabel.classList.remove('text-white');
                percentLabel.style.textShadow = 'none';
            }
        }

        // Ejecutar ambas calculadoras al iniciar
        window.onload = function() {
            calc();
            calcLibertad();
        };
    </script>
</body>
</html>