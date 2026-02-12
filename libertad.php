<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
// Recuperamos el nombre para el saludo personalizado
$nombre = $_SESSION['usuario_nombre'] ?? 'Inversor'; 
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <title>InvestFlow - Calculadora de Libertad</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { dark: { 900: '#0b111b', 800: '#162130', 700: '#1f2d40' }, brand: '#00ff88' } } }
        }
    </script>
    <style>
        body { background-color: #0b111b; font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; height: 100vh; position: fixed; border-right: 1px solid #1f2d40; }
        .main-content { margin-left: 260px; padding: 40px; }
        
        /* Estilos para los sliders */
        input[type=range] { -webkit-appearance: none; background: #1f2d40; height: 6px; border-radius: 5px; }
        input[type=range]::-webkit-slider-thumb { 
            -webkit-appearance: none; height: 20px; width: 20px; border-radius: 50%; 
            background: #00ff88; cursor: pointer; box-shadow: 0 0 10px rgba(0, 255, 136, 0.5); 
        }
        /* Estilo del hover rojo en el logout */
        .logout-link:hover { color: #ff4d4d !important; background: rgba(255, 77, 77, 0.1); }
        
        /* Animación suave para la barra de progreso */
        .progress-bar-transition { transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="text-gray-100 flex">

    <aside class="sidebar bg-dark-900 p-6 flex flex-col justify-between shadow-2xl">
        <div>
            <div class="flex items-center gap-2 mb-10">
                <div class="w-8 h-8 bg-brand rounded-lg flex items-center justify-center text-dark-900 font-bold shadow-[0_0_15px_rgba(0,255,136,0.3)]">$</div>
                <span class="text-xl font-bold italic tracking-tighter">InvestFlow</span>
            </div>
             <nav class="space-y-4">
                <a href="dashboard.php" class="block text-gray-400 hover:text-brand py-2 px-3 transition">Resumen General</a>
                <a href="inversiones.php" class="block text-gray-400 hover:text-brand py-2 px-3 transition">Mis Inversiones</a>
                <a href="hipotecas.php" class="block text-gray-400 hover:text-brand py-2 px-3 transition">Simulador Hipotecario</a>
                <a href="planificador.php" class="block text-gray-400 hover:text-brand py-2 px-3 transition">Planificador</a>
                <a href="libertad.php" class="block text-brand font-bold bg-brand/10 p-2 rounded-lg py-2 px-3">Libertad Financiera</a>
            </nav>
        </div>
        <a href="logout.php" class="logout-link text-gray-500 py-3 px-4 rounded-xl transition-all font-medium">Cerrar Sesión</a>
    </aside>

    <main class="main-content flex-1">
        <header class="mb-10">
            <h1 class="text-4xl font-black italic">Tu Número de <span class="text-brand">Libertad</span></h1>
            <p class="text-gray-500">¿Cuánto necesitas para vivir de tus rentas, <span class="text-brand font-bold"><?php echo htmlspecialchars($nombre); ?></span>?</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="bg-dark-800 p-8 rounded-3xl border border-dark-700 shadow-2xl space-y-10 h-fit">
                <div class="space-y-4">
                    <div class="flex justify-between"><label class="font-medium">Gastos Mensuales Deseados</label><span class="text-brand font-black text-xl" id="vGasto">1.500 €</span></div>
                    <input type="range" id="rGasto" class="w-full" min="500" max="10000" step="100" value="1500" oninput="calcLibertad()">
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between"><label class="font-medium">Tu Ahorro Actual</label><span class="text-brand font-black text-xl" id="vAhorro">30.000 €</span></div>
                    <input type="range" id="rAhorro" class="w-full" min="0" max="1000000" step="5000" value="30000" oninput="calcLibertad()">
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <label class="font-medium flex flex-col">Tasa de Retiro Segura <span class="text-xs text-gray-500 font-normal">(Regla del 4% recomendada)</span></label>
                        <span class="text-brand font-black text-xl" id="vRetiro">4.0 %</span>
                    </div>
                    <input type="range" id="rRetiro" class="w-full" min="2" max="6" step="0.1" value="4" oninput="calcLibertad()">
                </div>
            </div>

            <div class="space-y-8">
                <div class="bg-dark-800/80 p-8 rounded-3xl border border-brand/30 text-center shadow-[0_0_30px_rgba(0,255,136,0.1)] backdrop-blur-md">
                    <p class="text-gray-400 font-bold uppercase text-xs tracking-widest mb-2">Tu Meta de Capital (El Número FIRE)</p>
                    <h2 class="text-6xl font-black text-brand tracking-tight" id="resMeta">0 €</h2>
                    <p class="text-gray-300 mt-4 text-sm leading-relaxed" id="resInfo"></p>
                </div>

                <div class="bg-dark-800 p-8 rounded-3xl border border-dark-700 shadow-xl relative overflow-hidden">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                        Progreso hacia tu Libertad
                        <span id="statusIcon" class="text-brand animate-pulse">🚀</span>
                    </h3>

                    <div class="h-7 w-full bg-dark-900/80 rounded-full overflow-hidden p-1 border border-dark-700/50">
                        <div id="progressBar" class="h-full bg-gradient-to-r from-brand/80 to-brand rounded-full progress-bar-transition relative shadow-[0_0_15px_rgba(0,255,136,0.4)]" style="width: 0%">
                             <div class="absolute right-0 top-0 h-full w-1 bg-white/60 blur-[2px]"></div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-4 font-mono font-bold">
                        <div class="flex flex-col">
                            <span id="progressPercent" class="text-brand text-2xl leading-none">0%</span>
                            <span class="text-gray-500 text-[10px] uppercase tracking-wider mt-1">Completado</span>
                        </div>
                        <div class="text-right flex flex-col items-end">
                             <span class="text-white text-lg leading-none flex items-center gap-1">
                                🏁 META
                             </span>
                            <span id="progressGoalLabel" class="text-gray-400 text-xs mt-1">0 €</span>
                        </div>
                    </div>

                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-brand/5 rounded-full blur-2xl pointer-events-none"></div>
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
            document.getElementById('vGasto').innerText = gasto.toLocaleString('es-ES') + " €";
            document.getElementById('vAhorro').innerText = ahorro.toLocaleString('es-ES') + " €";
            document.getElementById('vRetiro').innerText = retiroPct.toFixed(1) + " %";

            // 3. Cálculos Financieros
            // El "Número de Libertad" es: (Gasto Anual / Tasa de Retiro Decimal)
            const gastoAnual = gasto * 12;
            const meta = gastoAnual / retiroDecimal;
            
            // Cálculo del porcentaje (limitado a 100% para que la barra no se salga)
            let porcentaje = (ahorro / meta) * 100;
            if (porcentaje > 100) porcentaje = 100;
            if (isNaN(porcentaje)) porcentaje = 0;

            // 4. Actualizar Resultados en Pantalla
            document.getElementById('resMeta').innerText = Math.round(meta).toLocaleString('es-ES') + " €";
            document.getElementById('resInfo').innerHTML = `Si acumulas esta cantidad y la inviertes, podrías retirar <strong class="text-brand">${gasto.toLocaleString('es-ES')}€ al mes</strong> indefinidamente según la regla del ${retiroPct}%.`;

            // 5. Actualizar la NUEVA BARRA DE PROGRESO
            const bar = document.getElementById('progressBar');
            const percentLabel = document.getElementById('progressPercent');
            const goalLabel = document.getElementById('progressGoalLabel');
            const statusIcon = document.getElementById('statusIcon');

            // Ancho de la barra
            bar.style.width = `${porcentaje}%`;
            
            // Texto del porcentaje
            percentLabel.innerText = porcentaje.toFixed(1) + "%";
            
            // Texto de la meta debajo de la barra
            goalLabel.innerText = Math.round(meta).toLocaleString('es-ES') + " €";

            // Cambiar icono y color si se alcanza la meta
            if (porcentaje >= 100) {
                statusIcon.innerText = "🎉 ¡META ALCANZADA!";
                percentLabel.classList.add('text-white', 'drop-shadow-[0_0_10px_rgba(0,255,136,0.8)]');
            } else {
                statusIcon.innerText = "🚀";
                percentLabel.classList.remove('text-white', 'drop-shadow-[0_0_10px_rgba(0,255,136,0.8)]');
            }
        }

        // Ejecutar al cargar la página
        window.onload = calcLibertad;
    </script>
</body>
</html>