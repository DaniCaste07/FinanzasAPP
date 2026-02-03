<?php
session_start();
require_once 'conexion.php';

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
        input[type=range] { -webkit-appearance: none; background: #1f2d40; height: 6px; border-radius: 5px; }
        input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; height: 18px; width: 18px; border-radius: 50%; background: #00ff88; cursor: pointer; box-shadow: 0 0 10px rgba(0, 255, 136, 0.5); }
    </style>
</head>
<body class="text-gray-100">

    <aside class="sidebar bg-dark-900 p-6 flex flex-col">
        <div class="flex items-center gap-2 mb-10">
            <div class="w-8 h-8 bg-brand rounded-lg flex items-center justify-center text-dark-900 font-bold">$</div>
            <span class="text-xl font-bold">Invest<span class="text-brand">Flow</span></span>
        </div>
        <nav class="space-y-4">
            <a href="dashboard.php" class="block text-gray-400 hover:text-brand transition">Resumen General</a>
            <a href="inversiones.php" class="block text-gray-400 hover:text-brand transition">Mis Inversiones</a>
            <a href="hipotecas.php" class="block text-brand font-bold bg-brand/10 p-2 rounded-lg">Simulador Hipotecario</a>
            <a href="logout.php" class="block text-gray-400 hover:text-red-400 pt-10">Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="mb-10">
            <h1 class="text-4xl font-extrabold">Simulador de <span class="text-brand">Hipotecas</span></h1>
            <p class="text-gray-500 mt-2">Calcula tu cuota mensual con precisión bancaria.</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="bg-dark-800 p-8 rounded-3xl border border-dark-700 shadow-2xl">
                <h3 class="text-lg font-bold mb-8 uppercase tracking-widest text-gray-400">Configuración</h3>
                
                <div class="space-y-10">
                    <div>
                        <div class="flex justify-between mb-4">
                            <label class="text-gray-300">Capital Solicitado</label>
                            <span class="text-brand font-bold text-xl"><span id="txtCap">200.000</span> €</span>
                        </div>
                        <input type="range" id="rangeCap" class="w-full" min="10000" max="1000000" step="5000" value="200000" oninput="updateSim()">
                    </div>

                    <div>
                        <div class="flex justify-between mb-4">
                            <label class="text-gray-300">Interés Anual</label>
                            <span class="text-brand font-bold text-xl"><span id="txtInt">3.5</span> %</span>
                        </div>
                        <input type="range" id="rangeInt" class="w-full" min="0.1" max="15" step="0.1" value="3.5" oninput="updateSim()">
                    </div>

                    <div>
                        <div class="flex justify-between mb-4">
                            <label class="text-gray-300">Plazo (Años)</label>
                            <span class="text-brand font-bold text-xl"><span id="txtAnn">25</span> años</span>
                        </div>
                        <input type="range" id="rangeAnn" class="w-full" min="1" max="40" step="1" value="25" oninput="updateSim()">
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="bg-dark-800 p-8 rounded-3xl border border-dark-700 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-24 h-24 text-brand" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                    </div>
                    
                    <p class="text-gray-400 text-sm font-bold uppercase mb-2">Cuota Mensual Estimada</p>
                    <h2 class="text-6xl font-black text-brand mb-6" id="totalCuota">0.00 €</h2>
                    
                    <div class="grid grid-cols-2 gap-4 border-t border-dark-700 pt-6">
                        <div>
                            <p class="text-gray-500 text-xs">TOTAL A PAGAR</p>
                            <p class="font-bold text-gray-200" id="resTotal">0 €</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">INTERESES TOTALES</p>
                            <p class="font-bold text-red-400" id="resInt">0 €</p>
                        </div>
                    </div>
                </div>

                <div class="bg-dark-800 p-6 rounded-3xl border border-dark-700 flex items-center justify-center">
                    <div style="width: 200px;">
                        <canvas id="mortgageChart"></canvas>
                    </div>
                    <div class="ml-8 space-y-2">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-3 h-3 bg-brand rounded-full"></span> 
                            <span class="text-gray-400">Capital:</span> <span id="labelCap">0</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-3 h-3 bg-red-500 rounded-full"></span> 
                            <span class="text-gray-400">Intereses:</span> <span id="labelInt">0</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        let myChart;

        function updateSim() {
            const cap = parseFloat(document.getElementById('rangeCap').value);
            const inte = parseFloat(document.getElementById('rangeInt').value);
            const ann = parseInt(document.getElementById('rangeAnn').value);

            document.getElementById('txtCap').innerText = new Intl.NumberFormat('es-ES').format(cap);
            document.getElementById('txtInt').innerText = inte;
            document.getElementById('txtAnn').innerText = ann;

            // Llamada al motor Java a través de calcularHipoteca.php
            fetch(`calcularHipoteca.php?cap=${cap}&int=${inte}&ann=${ann}`)
                .then(r => r.text())
                .then(resultado => {
                    const cuota = parseFloat(resultado.replace(',', '.').trim());
                    
                    if (!isNaN(cuota)) {
                        const totalPagar = cuota * ann * 12;
                        const interesesTotales = totalPagar - cap;

                        // Actualizar UI
                        document.getElementById('totalCuota').innerText = cuota.toLocaleString('es-ES', {minimumFractionDigits: 2}) + " €";
                        document.getElementById('resTotal').innerText = totalPagar.toLocaleString('es-ES') + " €";
                        document.getElementById('resInt').innerText = interesesTotales.toLocaleString('es-ES') + " €";
                        
                        document.getElementById('labelCap').innerText = cap.toLocaleString('es-ES') + " €";
                        document.getElementById('labelInt').innerText = interesesTotales.toLocaleString('es-ES') + " €";

                        updateChart(cap, interesesTotales);
                    }
                });
        }

        function updateChart(cap, int) {
            const ctx = document.getElementById('mortgageChart').getContext('2d');
            if (myChart) myChart.destroy();
            
            myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [cap, int],
                        backgroundColor: ['#00ff88', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: { cutout: '80%', plugins: { legend: { display: false } } }
            });
        }

        window.onload = updateSim;
    </script>
</body>
</html>