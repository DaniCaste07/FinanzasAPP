<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
// Recuperamos el nombre para el saludo personalizado
$nombre = $_SESSION['nombre'] ?? 'Inversor'; 
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Calculadora de Libertad</title>
    
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
        
        /* Animación suave para la barra de progreso */
        .progress-bar-transition { transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="text-gray-100 flex relative">

    <div class="fixed top-[-20%] left-[-10%] w-[800px] h-[800px] bg-brand/5 rounded-full blur-[150px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[600px] h-[600px] bg-purple-600/5 rounded-full blur-[150px] pointer-events-none z-0"></div>

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
                <a href="libertad.php" class="nav-active flex items-center gap-3 py-3 px-4 rounded-xl transition">
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
                    Movimiento FIRE
                </p>
                <h1 class="text-5xl font-black tracking-tight text-white">
                    Independencia <span class="italic text-transparent bg-clip-text bg-gradient-to-r from-brand to-cyan-400">Financiera</span>
                </h1>
            </div>
            
            <div class="hidden lg:flex items-center gap-3 glass-panel px-4 py-2 rounded-xl border border-purple-500/30 bg-purple-500/5 shadow-[0_0_15px_rgba(168,85,247,0.1)]">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                <div>
                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Algoritmo de Retiro</p>
                    <p class="font-mono text-xs text-purple-400 font-bold">Trinity Study Rule (4%)</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-5 glass-panel p-10 rounded-[2.5rem] shadow-2xl flex flex-col justify-center space-y-12">
                <div class="flex items-center gap-3 border-b border-white/5 pb-4">
                    <div class="w-8 h-8 rounded bg-dark-800 flex items-center justify-center border border-white/10">
                        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    </div>
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest">Variables de Vida</h3>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-end">
                        <label class="text-gray-400 font-bold text-sm uppercase tracking-wider">Gastos Mensuales</label>
                        <span class="text-3xl font-black text-white"><span id="vGasto">1.500</span> <span class="text-brand text-lg">€</span></span>
                    </div>
                    <input type="range" id="rGasto" class="w-full" min="500" max="10000" step="100" value="1500" oninput="calcLibertad()">
                </div>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-end">
                        <label class="text-gray-400 font-bold text-sm uppercase tracking-wider">Tu Ahorro Actual</label>
                        <span class="text-3xl font-black text-white"><span id="vAhorro">30.000</span> <span class="text-brand text-lg">€</span></span>
                    </div>
                    <input type="range" id="rAhorro" class="w-full" min="0" max="1000000" step="5000" value="30000" oninput="calcLibertad()">
                </div>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-end">
                        <label class="text-gray-400 font-bold text-sm uppercase tracking-wider flex flex-col">
                            Tasa de Retiro
                            <span class="text-[10px] text-gray-600 normal-case tracking-normal">Recomendada: 4.0%</span>
                        </label>
                        <span class="text-3xl font-black text-white"><span id="vRetiro">4.0</span> <span class="text-brand text-lg">%</span></span>
                    </div>
                    <input type="range" id="rRetiro" class="w-full" min="2" max="6" step="0.1" value="4" oninput="calcLibertad()">
                </div>
            </div>

            <div class="lg:col-span-7 space-y-8 flex flex-col">
                
                <div class="glass-panel p-10 rounded-[2.5rem] bg-gradient-to-br from-brand/10 to-transparent border-brand/20 relative overflow-hidden group shadow-[0_0_30px_rgba(0,255,163,0.1)]">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-brand/20 blur-3xl rounded-full group-hover:bg-brand/30 transition-all"></div>
                    <p class="text-brand font-black uppercase text-xs tracking-widest mb-2 opacity-80">Tu Meta de Capital (Número FIRE)</p>
                    <h2 class="text-6xl lg:text-7xl font-black text-white tracking-tighter" id="resMeta">0 <span class="text-4xl text-brand">€</span></h2>
                    
                    <div class="mt-6 border-t border-brand/20 pt-6">
                        <p class="text-gray-300 text-sm leading-relaxed font-medium" id="resInfo"></p>
                    </div>
                </div>

                <div class="glass-panel p-10 rounded-[2.5rem] flex-grow relative overflow-hidden flex flex-col justify-center">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-3">
                        Progreso del Objetivo
                        <span id="statusIcon" class="text-brand animate-bounce text-xl">🚀</span>
                    </h3>

                    <div class="h-8 w-full bg-dark-950 rounded-full overflow-hidden p-1 border border-white/5 shadow-inner">
                        <div id="progressBar" class="h-full bg-gradient-to-r from-brand/50 to-brand rounded-full progress-bar-transition relative shadow-[0_0_20px_rgba(0,255,163,0.5)]" style="width: 0%">
                             <div class="absolute right-0 top-0 h-full w-2 bg-white blur-[2px]"></div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <div class="flex flex-col">
                            <span id="progressPercent" class="text-brand text-4xl font-black font-mono tracking-tighter">0.0%</span>
                            <span class="text-gray-500 text-[10px] font-bold uppercase tracking-widest mt-1">Completado</span>
                        </div>
                        <div class="text-right flex flex-col items-end">
                             <span class="text-white text-xl font-bold font-mono tracking-tighter flex items-center gap-2">
                                🏁 META
                             </span>
                            <span id="progressGoalLabel" class="text-gray-400 text-xs font-bold font-mono mt-1">0 €</span>
                        </div>
                    </div>

                    <div class="absolute top-1/2 right-10 -mt-10 w-32 h-32 bg-brand/5 rounded-full blur-[40px] pointer-events-none"></div>
                </div>
            </div>

        </div>
    </main>

    <script>
        function calcLibertad() {
            // 1. Obtener valores de los inputs
            const gasto = parseFloat(document.getElementById('rGasto').value);
            const ahorro = parseFloat(document.getElementById('rAhorro').value);
            const retiroPct = parseFloat(document.getElementById('rRetiro').value);
            const retiroDecimal = retiroPct / 100;

            // 2. Actualizar textos de los sliders
            document.getElementById('vGasto').innerText = new Intl.NumberFormat('es-ES').format(gasto);
            document.getElementById('vAhorro').innerText = new Intl.NumberFormat('es-ES').format(ahorro);
            document.getElementById('vRetiro').innerText = retiroPct.toFixed(1);

            // 3. Cálculos Financieros (FIRE: Gasto Anual / Tasa Retiro)
            const gastoAnual = gasto * 12;
            const meta = gastoAnual / retiroDecimal;
            
            // Cálculo del porcentaje
            let porcentaje = (ahorro / meta) * 100;
            if (porcentaje > 100) porcentaje = 100;
            if (isNaN(porcentaje)) porcentaje = 0;

            // 4. Actualizar Resultados
            document.getElementById('resMeta').innerHTML = Math.round(meta).toLocaleString('es-ES') + ' <span class="text-4xl text-brand">€</span>';
            
            document.getElementById('resInfo').innerHTML = `Si logras acumular este capital e inviertes siguiendo la teoría del mercado, podrás retirar <strong class="text-brand text-lg">${gasto.toLocaleString('es-ES')} € al mes</strong> de forma indefinida, sin que tu patrimonio se agote (Regla del ${retiroPct}%).`;

            // 5. Actualizar Barra de Progreso
            const bar = document.getElementById('progressBar');
            const percentLabel = document.getElementById('progressPercent');
            const goalLabel = document.getElementById('progressGoalLabel');
            const statusIcon = document.getElementById('statusIcon');

            bar.style.width = `${porcentaje}%`;
            percentLabel.innerText = porcentaje.toFixed(1) + "%";
            goalLabel.innerText = Math.round(meta).toLocaleString('es-ES') + " €";

            // Reacciones visuales si llegas a la meta
            if (porcentaje >= 100) {
                statusIcon.innerText = "🏆";
                percentLabel.classList.add('text-white');
                percentLabel.style.textShadow = '0 0 15px rgba(0,255,163,0.8)';
            } else {
                statusIcon.innerText = "🚀";
                percentLabel.classList.remove('text-white');
                percentLabel.style.textShadow = 'none';
            }
        }

        window.onload = calcLibertad;
    </script>
</body>
</html>