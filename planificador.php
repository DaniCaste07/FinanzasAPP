<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
$nombre = $_SESSION['nombre'] ?? 'Inversor'; 
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Planificador de Futuro</title>
    
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
                <a href="planificador.php" class="nav-active flex items-center gap-3 py-3 px-4 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Planificador
                </a>
                <a href="libertad.php" class="nav-link flex items-center gap-3 text-gray-400 py-3 px-4 rounded-xl transition">
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
                    Simulación Predictiva
                </p>
                <h1 class="text-5xl font-black tracking-tight text-white">
                    Planificador <span class="italic text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-500">Avanzado</span>
                </h1>
            </div>
            
            <div class="hidden lg:flex items-center gap-3 glass-panel px-4 py-2 rounded-xl border border-blue-500/30 bg-blue-500/5 shadow-[0_0_15px_rgba(59,130,246,0.1)]">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <div>
                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Modelo Matemático</p>
                    <p class="font-mono text-xs text-blue-400 font-bold">Interés Compuesto Activo</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-4 glass-panel p-8 rounded-[2.5rem] shadow-2xl space-y-10 flex flex-col justify-center">
                
                <div class="space-y-3">
                    <div class="flex justify-between items-end">
                        <label class="text-gray-400 font-bold text-xs uppercase tracking-widest">Capital Inicial</label>
                        <span class="text-xl font-black text-white"><span id="vCap">1.000</span> <span class="text-brand text-sm">€</span></span>
                    </div>
                    <input type="range" id="rCap" class="w-full" min="0" max="50000" step="500" value="1000" oninput="calc()">
                </div>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-end">
                        <label class="text-gray-400 font-bold text-xs uppercase tracking-widest">Aportación Mensual</label>
                        <span class="text-xl font-black text-white"><span id="vMes">200</span> <span class="text-brand text-sm">€</span></span>
                    </div>
                    <input type="range" id="rMes" class="w-full" min="0" max="3000" step="50" value="200" oninput="calc()">
                </div>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-end">
                        <label class="text-gray-400 font-bold text-xs uppercase tracking-widest">Rentabilidad Anual</label>
                        <span class="text-xl font-black text-white"><span id="vInt">8.0</span> <span class="text-brand text-sm">%</span></span>
                    </div>
                    <input type="range" id="rInt" class="w-full" min="1" max="20" step="0.5" value="8" oninput="calc()">
                </div>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-end">
                        <label class="text-gray-400 font-bold text-xs uppercase tracking-widest">Tiempo Estimado</label>
                        <span class="text-xl font-black text-white"><span id="vAnn">20</span> <span class="text-brand text-sm">Años</span></span>
                    </div>
                    <input type="range" id="rAnn" class="w-full" min="1" max="50" step="1" value="20" oninput="calc()">
                </div>

            </div>

            <div class="lg:col-span-8 space-y-6 flex flex-col">
                
                <div class="glass-panel p-10 rounded-[2.5rem] bg-gradient-to-br from-brand/10 to-transparent border-brand/20 relative overflow-hidden group shadow-[0_0_30px_rgba(0,255,163,0.1)]">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-brand/20 blur-3xl rounded-full group-hover:bg-brand/30 transition-all"></div>
                    <p class="text-brand font-black uppercase text-xs tracking-widest mb-2 opacity-80">Capital Final Estimado</p>
                    <h2 class="text-6xl lg:text-7xl font-black text-white tracking-tighter" id="resFinal">0 <span class="text-4xl text-brand">€</span></h2>
                    
                    <div class="mt-6 flex gap-6 border-t border-white/5 pt-6">
                        <div>
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Total Invertido</p>
                            <p class="font-mono text-gray-300 font-bold" id="resAportado">0 €</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Intereses Generados</p>
                            <p class="font-mono text-brand font-bold" id="resGanado">0 €</p>
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
    </main>

    <script>
        let chart;
        
        function calc() {
            // Recoger valores
            const initial = parseFloat(document.getElementById('rCap').value);
            const monthly = parseFloat(document.getElementById('rMes').value);
            const rate = parseFloat(document.getElementById('rInt').value) / 100 / 12; // Tasa mensual
            const years = parseInt(document.getElementById('rAnn').value);
            const months = years * 12;

            // Actualizar etiquetas en la UI
            document.getElementById('vCap').innerText = new Intl.NumberFormat('es-ES').format(initial);
            document.getElementById('vMes').innerText = new Intl.NumberFormat('es-ES').format(monthly);
            document.getElementById('vInt').innerText = (rate*12*100).toFixed(1);
            document.getElementById('vAnn').innerText = years;

            // Lógica de Interés Compuesto
            let balance = initial;
            let history = [initial];
            let labels = ["Año 0"];
            
            let totalAportado = initial + (monthly * months);

            for (let i = 1; i <= months; i++) {
                balance = (balance + monthly) * (1 + rate);
                
                // Guardar datos anualmente para no saturar el gráfico
                if (i % 12 === 0) {
                    history.push(Math.round(balance));
                    labels.push("Año " + (i / 12));
                }
            }

            const interesesGenerados = balance - totalAportado;

            // Mostrar resultados finales
            document.getElementById('resFinal').innerHTML = Math.round(balance).toLocaleString('es-ES') + ' <span class="text-4xl text-brand">€</span>';
            document.getElementById('resAportado').innerText = Math.round(totalAportado).toLocaleString('es-ES') + ' €';
            document.getElementById('resGanado').innerText = '+' + Math.round(interesesGenerados).toLocaleString('es-ES') + ' €';

            // Actualizar el gráfico con los nuevos datos
            updateChart(labels, history);
        }

        function updateChart(labels, data) {
            const ctx = document.getElementById('growthChart').getContext('2d');
            
            // Forzar tipografía global de Chart.js
            Chart.defaults.font.family = "'Outfit', sans-serif";
            Chart.defaults.color = '#6b7280'; // text-gray-500
            
            if (chart) chart.destroy();

            // Crear un degradado futurista para el fondo de la línea
            let gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(0, 255, 163, 0.4)'); // brand color con opacidad
            gradient.addColorStop(1, 'rgba(0, 255, 163, 0.0)'); // transparente abajo

            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Patrimonio Estimado',
                        data: data,
                        borderColor: '#00ffa3', // Línea verde neón
                        borderWidth: 3,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4, // Curvatura suave
                        pointBackgroundColor: '#030712',
                        pointBorderColor: '#00ffa3',
                        pointBorderWidth: 2,
                        pointRadius: 0, // Ocultar puntos por defecto
                        pointHoverRadius: 6 // Mostrar punto grande al pasar el ratón
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.9)',
                            titleFont: { size: 13 },
                            bodyFont: { size: 15, weight: 'bold', color: '#00ffa3' },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y.toLocaleString('es-ES') + ' €';
                                }
                            }
                        }
                    },
                    scales: {
                        x: { 
                            grid: { display: false, drawBorder: false }, 
                            ticks: { maxTicksLimit: 10, font: { size: 10, weight: '600' } } 
                        },
                        y: { 
                            grid: { color: 'rgba(255, 255, 255, 0.05)', borderDash: [5, 5] }, 
                            ticks: { 
                                callback: function(value) {
                                    if(value >= 1000000) return (value/1000000) + 'M €';
                                    if(value >= 1000) return (value/1000) + 'k €';
                                    return value + ' €';
                                },
                                font: { size: 10, weight: '600' }
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Ejecutar al iniciar la página
        window.onload = calc;
    </script>
</body>
</html>