<?php
session_start();
require_once 'conexion.php';

// Verificación de seguridad
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Simulador Avanzado</title>
    
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
        .main-content { margin-left: 280px; min-height: 100vh; display: flex; flex-direction: column; }
        .content-wrapper { padding: 40px; flex-grow: 1; }
        
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

        /* Sliders Neón Personalizados */
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
        input[type=range]::-webkit-slider-thumb:hover {
            transform: scale(1.2);
        }
    </style>
</head>
<body class="text-gray-100 flex relative">

    <div class="fixed top-[-20%] left-[-10%] w-[800px] h-[800px] bg-brand/5 rounded-full blur-[150px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-600/5 rounded-full blur-[150px] pointer-events-none z-0"></div>

    <?php require_once 'sidebar.php'; ?>

    <main class="main-content relative z-10 w-full">
        <div class="content-wrapper">
            
            <header class="mb-12 flex justify-between items-end">
                <div>
                    <p class="text-brand text-xs font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 bg-brand rounded-full animate-pulse shadow-[0_0_8px_#00ffa3]"></span>
                        Simulador Inteligente
                    </p>
                    <h1 class="text-5xl font-black tracking-tight text-white">
                        Préstamos y Amortización
                    </h1>
                </div>
                
                <div class="hidden lg:flex items-center gap-3 glass-panel px-4 py-2 rounded-xl border border-brand/30 bg-brand/5 shadow-[0_0_15px_rgba(0,255,163,0.1)]">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    <div>
                        <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Motor Backend Activo</p>
                        <p class="font-mono text-xs text-brand font-bold">MisFinanzasApp.jar (Java Core)</p>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-5 glass-panel p-10 rounded-[2.5rem] shadow-2xl flex flex-col justify-center space-y-12">
                    <div class="flex items-center gap-3 border-b border-white/5 pb-4">
                        <div class="w-8 h-8 rounded bg-dark-800 flex items-center justify-center border border-white/10">
                            <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        </div>
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest">Ajustes del Préstamo</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <label class="text-gray-400 font-bold text-sm uppercase tracking-wider">Capital Solicitado</label>
                            <span class="text-3xl font-black text-white"><span id="txtCap">200.000</span> <span class="text-brand text-lg">€</span></span>
                        </div>
                        <input type="range" id="rangeCap" class="w-full" min="10000" max="1000000" step="5000" value="200000" oninput="updateSim()">
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <label class="text-gray-400 font-bold text-sm uppercase tracking-wider">Interés Anual (TIN)</label>
                            <span class="text-3xl font-black text-white"><span id="txtInt">3.5</span> <span class="text-brand text-lg">%</span></span>
                        </div>
                        <input type="range" id="rangeInt" class="w-full" min="0.1" max="15" step="0.1" value="3.5" oninput="updateSim()">
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <label class="text-gray-400 font-bold text-sm uppercase tracking-wider">Plazo de Devolución</label>
                            <span class="text-3xl font-black text-white"><span id="txtAnn">25</span> <span class="text-brand text-lg">Años</span></span>
                        </div>
                        <input type="range" id="rangeAnn" class="w-full" min="1" max="40" step="1" value="25" oninput="updateSim()">
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-6 flex flex-col">
                    
                    <div class="glass-panel p-10 rounded-[2.5rem] bg-gradient-to-br from-brand/10 to-transparent border-brand/20 relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-brand/20 blur-3xl rounded-full group-hover:bg-brand/30 transition-all"></div>
                        <p class="text-brand font-black uppercase text-xs tracking-widest mb-2 opacity-80">Cuota Mensual Estimada</p>
                        <h2 class="text-7xl font-black text-white tracking-tighter" id="totalCuota">0,00 <span class="text-4xl text-brand">€</span></h2>
                    </div>

                    <div class="glass-panel p-8 rounded-[2.5rem] flex-grow flex flex-col md:flex-row items-center gap-10">
                        
                        <div class="relative w-48 h-48 flex-shrink-0 flex items-center justify-center">
                            <canvas id="mortgageChart"></canvas>
                            <div class="absolute inset-0 m-auto w-32 h-32 rounded-full border border-white/5 flex items-center justify-center flex-col">
                                <span class="text-[9px] text-gray-500 font-bold uppercase tracking-widest">Distribución</span>
                            </div>
                        </div>
                        
                        <div class="flex-1 space-y-4 w-full">
                            <div class="p-5 bg-dark-950/50 rounded-2xl border border-white/5 hover:border-brand/30 transition-colors">
                                <p class="text-[10px] text-gray-500 font-black uppercase mb-1 tracking-widest">Total a pagar al banco</p>
                                <p class="text-2xl font-black text-white" id="resTotal">0 €</p>
                            </div>
                            <div class="p-5 bg-dark-950/50 rounded-2xl border border-white/5 hover:border-red-500/30 transition-colors">
                                <p class="text-[10px] text-gray-500 font-black uppercase mb-1 tracking-widest">Intereses generados</p>
                                <p class="text-2xl font-black text-red-400" id="resInt">0 €</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <?php require_once 'footer.php'; ?>
    </main>

    <script>
        let myChart;

        function updateSim() {
            // 1. Obtener valores
            const cap = parseFloat(document.getElementById('rangeCap').value);
            const inte = parseFloat(document.getElementById('rangeInt').value);
            const ann = parseInt(document.getElementById('rangeAnn').value);

            // 2. Actualizar UI de Sliders
            document.getElementById('txtCap').innerText = new Intl.NumberFormat('es-ES').format(cap);
            document.getElementById('txtInt').innerText = inte.toFixed(1);
            document.getElementById('txtAnn').innerText = ann;

            // 3. Llamada al motor Java mediante PHP
            fetch(`calcularHipoteca.php?cap=${cap}&int=${inte}&ann=${ann}`)
                .then(r => r.text())
                .then(resultado => {
                    const cuotaLimpia = resultado.trim().replace(',', '.');
                    const cuota = parseFloat(cuotaLimpia);
                    
                    if (!isNaN(cuota)) {
                        const totalPagar = cuota * ann * 12;
                        const interesesTotales = totalPagar - cap;

                        // Actualizar textos de resultados
                        document.getElementById('totalCuota').innerHTML = cuota.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' <span class="text-4xl text-brand">€</span>';
                        document.getElementById('resTotal').innerText = totalPagar.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + " €";
                        document.getElementById('resInt').innerText = interesesTotales.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + " €";

                        // Actualizar gráfico
                        renderChart(cap, interesesTotales);
                    }
                })
                .catch(err => {
                    console.error("Error contactando al motor Java:", err);
                    document.getElementById('totalCuota').innerText = "Error";
                });
        }

        function renderChart(capital, intereses) {
            const ctx = document.getElementById('mortgageChart').getContext('2d');
            
            Chart.defaults.font.family = "'Outfit', sans-serif";
            
            if (myChart) myChart.destroy();
            
            myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Capital Prestado', 'Intereses Generados'],
                    datasets: [{
                        data: [capital, intereses],
                        backgroundColor: ['#00ffa3', '#ef4444'],
                        borderWidth: 2,
                        borderColor: '#030712',
                        hoverOffset: 10
                    }]
                },
                options: {
                    cutout: '80%',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.9)',
                            titleFont: { size: 13, family: "'Outfit', sans-serif" },
                            bodyFont: { size: 14, weight: 'bold', family: "'Outfit', sans-serif" },
                            padding: 12,
                            cornerRadius: 8,
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
        }

        window.onload = updateSim;
    </script>
</body>
</html>