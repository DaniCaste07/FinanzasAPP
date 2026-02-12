<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
$nombre = $_SESSION['nombre'] ?? 'Inversor'; //
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <title>InvestFlow - Planificador de Futuro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { dark: { 900: '#0b111b', 800: '#162130', 700: '#1f2d40' }, brand: '#00ff88' } } }
        }
    </script>
    <style>
        body { background-color: #0b111b; font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; height: 100vh; position: fixed; border-right: 1px solid #1f2d40; }
        .main-content { margin-left: 260px; padding: 40px; }
        input[type=range] { -webkit-appearance: none; background: #1f2d40; height: 6px; border-radius: 5px; }
        input[type=range]::-webkit-slider-thumb { 
            -webkit-appearance: none; height: 18px; width: 18px; border-radius: 50%; 
            background: #00ff88; cursor: pointer; box-shadow: 0 0 10px rgba(0, 255, 136, 0.5); 
        }
        .logout-link:hover { color: #ff4d4d !important; background: rgba(255, 77, 77, 0.1); } /* */
    </style>
</head>
<body class="text-gray-100 flex">

    <aside class="sidebar bg-dark-900 p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-2 mb-10">
                <div class="w-8 h-8 bg-brand rounded-lg flex items-center justify-center text-dark-900 font-bold">$</div>
                <span class="text-xl font-bold italic tracking-tighter">InvestFlow</span>
            </div>
            <nav class="space-y-4">
                <a href="dashboard.php" class="block text-gray-400 hover:text-brand py-2 px-3 transition">Resumen General</a>
                <a href="inversiones.php" class="block text-gray-400 hover:text-brand py-2 px-3 transition">Mis Inversiones</a>
                <a href="hipotecas.php" class="block text-gray-400 hover:text-brand py-2 px-3 transition">Simulador Hipotecario</a>
                <a href="planificador.php" class="block text-brand font-bold bg-brand/10 p-2 rounded-lg py-2 px-3">Planificador</a>
                <a href="libertad.php" class="block text-gray-400 hover:text-brand py-2 px-3 transition">Libertad Financiera</a>
            </nav>
        </div>
        <a href="logout.php" class="logout-link text-gray-500 py-3 px-4 rounded-xl transition-all font-medium">Cerrar Sesión</a>
    </aside>

    <main class="main-content flex-1">
        <header class="mb-10">
            <h1 class="text-4xl font-black italic">Hola <span class="text-brand"><?php echo htmlspecialchars($nombre); ?></span>,</h1>
            <p class="text-gray-500">Proyecta el crecimiento de tu riqueza a largo plazo.</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="bg-dark-800 p-8 rounded-3xl border border-dark-700 shadow-2xl space-y-8">
                <div class="space-y-4">
                    <div class="flex justify-between"><label>Capital Inicial</label><span class="text-brand font-bold" id="vCap">1000 €</span></div>
                    <input type="range" id="rCap" class="w-full" min="0" max="50000" step="500" value="1000" oninput="calc()">
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between"><label>Aportación Mensual</label><span class="text-brand font-bold" id="vMes">200 €</span></div>
                    <input type="range" id="rMes" class="w-full" min="0" max="2000" step="50" value="200" oninput="calc()">
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between"><label>Rentabilidad Anual (%)</label><span class="text-brand font-bold" id="vInt">8 %</span></div>
                    <input type="range" id="rInt" class="w-full" min="1" max="20" step="0.5" value="8" oninput="calc()">
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between"><label>Tiempo (Años)</label><span class="text-brand font-bold" id="vAnn">20 años</span></div>
                    <input type="range" id="rAnn" class="w-full" min="1" max="50" step="1" value="20" oninput="calc()">
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-brand/5 border border-brand/20 p-8 rounded-3xl text-center">
                    <p class="text-brand font-bold uppercase text-xs tracking-widest mb-1">Capital Final Estimado</p>
                    <h2 class="text-6xl font-black text-brand" id="resFinal">0 €</h2>
                </div>

                <div class="bg-dark-800 p-6 rounded-3xl border border-dark-700 shadow-xl">
                    <canvas id="growthChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </main>

    <script>
        let chart;
        function calc() {
            const initial = parseFloat(document.getElementById('rCap').value);
            const monthly = parseFloat(document.getElementById('rMes').value);
            const rate = parseFloat(document.getElementById('rInt').value) / 100 / 12;
            const years = parseInt(document.getElementById('rAnn').value);
            const months = years * 12;

            document.getElementById('vCap').innerText = initial.toLocaleString() + " €";
            document.getElementById('vMes').innerText = monthly.toLocaleString() + " €";
            document.getElementById('vInt').innerText = (rate*12*100).toFixed(1) + " %";
            document.getElementById('vAnn').innerText = years + " años";

            let balance = initial;
            let history = [initial];
            let labels = [0];

            for (let i = 1; i <= months; i++) {
                balance = (balance + monthly) * (1 + rate);
                if (i % 12 === 0) {
                    history.push(Math.round(balance));
                    labels.push(i / 12);
                }
            }

            document.getElementById('resFinal').innerText = Math.round(balance).toLocaleString() + " €";
            updateChart(labels, history);
        }

        function updateChart(labels, data) {
            const ctx = document.getElementById('growthChart').getContext('2d');
            if (chart) chart.destroy();
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Evolución del Capital',
                        data: data,
                        borderColor: '#00ff88',
                        backgroundColor: 'rgba(0, 255, 136, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#666' } },
                        y: { grid: { color: '#1f2d40' }, ticks: { color: '#666' } }
                    }
                }
            });
        }
        window.onload = calc;
    </script>
</body>
</html>