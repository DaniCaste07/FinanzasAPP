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
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { 900: '#0b111b', 800: '#162130', 700: '#1f2d40' },
                        brand: { DEFAULT: '#00ff88', hover: '#00cc6e' }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0b111b; font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; height: 100vh; position: fixed; border-right: 1px solid #1f2d40; }
        .main-content { margin-left: 260px; padding: 40px; }
        /* Estilo personalizado para los Sliders */
        input[type=range] { -webkit-appearance: none; background: #1f2d40; height: 6px; border-radius: 5px; }
        input[type=range]::-webkit-slider-thumb { 
            -webkit-appearance: none; height: 20px; width: 20px; border-radius: 50%; 
            background: #00ff88; cursor: pointer; box-shadow: 0 0 10px rgba(0, 255, 136, 0.5); 
        }
    </style>
</head>
<body class="text-gray-100">

    <aside class="sidebar bg-dark-900 p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-2 mb-10">
                <div class="w-8 h-8 bg-brand rounded-lg flex items-center justify-center text-dark-900 font-bold">$</div>
                <span class="text-xl font-bold">Invest<span class="text-brand">Flow</span></span>
            </div>
            <nav class="space-y-4">
                <a href="dashboard.php" class="block text-gray-400 hover:text-brand transition py-2 px-3">Resumen General</a>
                <a href="inversiones.php" class="block text-gray-400 hover:text-brand transition py-2 px-3">Mis Inversiones</a>
                <a href="hipotecas.php" class="block text-brand font-bold bg-brand/10 p-2 rounded-lg py-2 px-3">Simulador Hipotecario</a>
            </nav>
        </div>
        <a href="logout.php" class="block text-gray-500 hover:text-red-400 px-3 pb-4">Cerrar Sesión</a>
    </aside>

    <main class="main-content">
        <header class="mb-10">
            <h1 class="text-4xl font-extrabold tracking-tight">Simulador <span class="text-brand">Inteligente</span></h1>
            <p class="text-gray-500 mt-2">Calcula tu viabilidad financiera con precisión.</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="bg-dark-800 p-8 rounded-3xl border border-dark-700 shadow-2xl space-y-10">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Ajustes del Préstamo</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <label class="text-gray-300 font-medium">Capital Solicitado</label>
                        <span class="text-2xl font-black text-brand"><span id="txtCap">200.000</span> €</span>
                    </div>
                    <input type="range" id="rangeCap" class="w-full" min="10000" max="1000000" step="5000" value="200000" oninput="updateSim()">
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <label class="text-gray-300 font-medium">Interés Anual (TIN)</label>
                        <span class="text-2xl font-black text-brand"><span id="txtInt">3.5</span> %</span>
                    </div>
                    <input type="range" id="rangeInt" class="w-full" min="0.1" max="15" step="0.1" value="3.5" oninput="updateSim()">
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <label class="text-gray-300 font-medium">Plazo de Devolución</label>
                        <span class="text-2xl font-black text-brand"><span id="txtAnn">25</span> años</span>
                    </div>
                    <input type="range" id="rangeAnn" class="w-full" min="1" max="40" step="1" value="25" oninput="updateSim()">
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-brand/5 border border-brand/20 p-8 rounded-3xl text-center relative overflow-hidden">
                    <p class="text-brand font-bold uppercase text-xs tracking-widest mb-2">Cuota Mensual</p>
                    <h2 class="text-7xl font-black text-brand" id="totalCuota">0.00 €</h2>
                    <div class="absolute -bottom-6 -right-6 text-brand opacity-5">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M11.5 2C6.81 2 3 5.81 3 10.5S6.81 19 11.5 19 20 15.19 20 10.5 16.19 2 11.5 2zm0 14.5c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/></svg>
                    </div>
                </div>

                <div class="bg-dark-800 p-6 rounded-3xl border border-dark-700 flex flex-col md:flex-row items-center gap-8 shadow-xl">
                    <div style="width: 180px; height: 180px;">
                        <canvas id="mortgageChart"></canvas>
                    </div>
                    <div class="flex-1 space-y-4 w-full">
                        <div class="p-4 bg-dark-900/50 rounded-2xl border border-dark-700">
                            <p class="text-[10px] text-gray-500 font-bold uppercase mb-1">Total a pagar al banco</p>
                            <p class="text-xl font-bold text-white" id="resTotal">0 €</p>
                        </div>
                        <div class="p-4 bg-dark-900/50 rounded-2xl border border-dark-700">
                            <p class="text-[10px] text-gray-500 font-bold uppercase mb-1">Intereses generados</p>
                            <p class="text-xl font-bold text-red-400" id="resInt">0 €</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
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
            document.getElementById('txtInt').innerText = inte;
            document.getElementById('txtAnn').innerText = ann;

            // 3. Llamada al motor Java mediante PHP
            fetch(`calcularHipoteca.php?cap=${cap}&int=${inte}&ann=${ann}`)
                .then(r => r.text())
                .then(resultado => {
                    const cuota = parseFloat(resultado.trim());
                    
                    if (!isNaN(cuota)) {
                        const totalPagar = cuota * ann * 12;
                        const interesesTotales = totalPagar - cap;

                        // Actualizar textos de resultados
                        document.getElementById('totalCuota').innerText = cuota.toLocaleString('es-ES', {minimumFractionDigits: 2}) + " €";
                        document.getElementById('resTotal').innerText = totalPagar.toLocaleString('es-ES') + " €";
                        document.getElementById('resInt').innerText = interesesTotales.toLocaleString('es-ES') + " €";

                        // Actualizar gráfico
                        renderChart(cap, interesesTotales);
                    }
                });
        }

        function renderChart(capital, intereses) {
            const ctx = document.getElementById('mortgageChart').getContext('2d');
            
            if (myChart) myChart.destroy();
            
            myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Capital', 'Intereses'],
                    datasets: [{
                        data: [capital, intereses],
                        backgroundColor: ['#00ff88', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    cutout: '75%',
                    plugins: { legend: { display: false } }
                }
            });
        }

        window.onload = updateSim;
    </script>
</body>
</html>