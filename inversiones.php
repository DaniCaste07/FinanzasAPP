<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['usuario_id'];

// Función para obtener precios reales (Binance API con timeout para evitar cuelgues)
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

$stmt = $conexion->prepare("SELECT * FROM inversiones WHERE usuario_id = ? ORDER BY id DESC");
$stmt->execute([$uid]);
$inversiones = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Mercado y Cartera</title>
    
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

        .input-trade {
            background: rgba(3, 7, 18, 0.6);
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        .input-trade:focus {
            border-color: #00ffa3;
            box-shadow: 0 0 15px rgba(0, 255, 163, 0.2);
            background: rgba(0, 255, 163, 0.02);
            outline: none;
        }
        
        /* Personalizar el scrollbar de la tabla */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #030712; }
        ::-webkit-scrollbar-thumb { background: #1f2937; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #374151; }
    </style>
</head>
<body class="text-gray-100 flex relative">

    <div class="fixed top-[-20%] right-[-10%] w-[800px] h-[800px] bg-brand/5 rounded-full blur-[150px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-20%] left-[-10%] w-[600px] h-[600px] bg-blue-600/5 rounded-full blur-[150px] pointer-events-none z-0"></div>

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
                <a href="inversiones.php" class="nav-active flex items-center gap-3 py-3 px-4 rounded-xl transition">
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
                    Gestor de Activos
                </p>
                <h1 class="text-5xl font-black tracking-tight text-white">
                    Mi Cartera
                </h1>
            </div>
            
            <div class="hidden md:flex items-center gap-3 glass-panel px-4 py-2 rounded-xl border border-white/10">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                </span>
                <div>
                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Conexión de Mercado</p>
                    <p class="font-mono text-xs text-blue-400 font-bold">API Rest v3 Activa</p>
                </div>
            </div>
        </header>

        <div class="glass-panel p-8 rounded-[2rem] shadow-2xl mb-10 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-brand/5 rounded-full blur-3xl -mr-10 -mt-10 group-hover:bg-brand/10 transition-colors"></div>
            
            <h3 class="text-sm font-black text-white uppercase tracking-widest mb-6 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Añadir Nueva Posición
            </h3>

            <form id="formInv" class="flex flex-col md:flex-row items-end gap-6 relative z-10">
                <div class="flex-1 w-full md:w-auto">
                    <label class="text-[10px] font-black text-gray-500 uppercase block mb-2 tracking-[0.2em] ml-2">Ticker del Activo (Ej: BTC)</label>
                    <input list="assets-list" id="activo" class="input-trade w-full p-4 rounded-2xl text-white font-medium placeholder-gray-600 uppercase" placeholder="BUSCAR ACTIVO...">
                    <datalist id="assets-list">
                        <option value="BTC">Bitcoin (BTC)</option>
                        <option value="ETH">Ethereum (ETH)</option>
                        <option value="SOL">Solana (SOL)</option>
                        <option value="ADA">Cardano (ADA)</option>
                        <option value="XRP">Ripple (XRP)</option>
                        <option value="DOT">Polkadot (DOT)</option>
                        <option value="DOGE">Dogecoin (DOGE)</option>
                        <option value="AVAX">Avalanche (AVAX)</option>
                        <option value="LINK">Chainlink (LINK)</option>
                        <option value="MATIC">Polygon (MATIC)</option>
                    </datalist>
                </div>
                <div class="flex-1 w-full md:w-auto">
                    <label class="text-[10px] font-black text-gray-500 uppercase block mb-2 tracking-[0.2em] ml-2">Capital Invertido (€)</label>
                    <input type="number" id="cantidad" min="0.01" step="0.01" class="input-trade w-full p-4 rounded-2xl text-white font-mono placeholder-gray-600" placeholder="0.00">
                </div>
                <button type="button" onclick="registrar()" class="w-full md:w-auto bg-brand text-dark-950 font-black px-10 py-4 rounded-2xl hover:scale-[1.02] transition-transform shadow-[0_0_20px_rgba(0,255,163,0.3)] uppercase tracking-widest flex items-center justify-center gap-2">
                    Ejecutar Compra
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </button>
            </form>
        </div>

        <div class="glass-panel rounded-[2rem] overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-white/5 flex justify-between items-center bg-dark-950/30">
                <h3 class="text-sm font-black text-white uppercase tracking-widest">Cartera Activa</h3>
                <span class="text-[10px] text-gray-500 uppercase font-bold bg-dark-900 py-1 px-3 rounded border border-white/5"><?php echo count($inversiones); ?> Posiciones</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-[900px]">
                    <thead class="bg-dark-950/80 text-[10px] font-black text-gray-500 uppercase tracking-widest border-b border-white/5">
                        <tr>
                            <th class="p-6">Activo</th>
                            <th class="p-6 text-right">Invertido</th>
                            <th class="p-6 text-right">Precio Entrada</th>
                            <th class="p-6 text-right">Precio Actual (Vivo)</th>
                            <th class="p-6 text-right">Pérdida / Ganancia</th>
                            <th class="p-6 text-center">Gestión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (count($inversiones) == 0): ?>
                            <tr>
                                <td colspan="6" class="p-12 text-center text-gray-500 font-medium">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                    No hay activos registrados en tu cartera actualmente.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach($inversiones as $inv): 
                            $actual = getLivePrice($inv['activo']) ?? $inv['valor_actual'];
                            
                            // Cálculo exacto del beneficio
                            if ($inv['valor_actual'] > 0) {
                                $beneficio = ($inv['cantidad_invertida'] * $actual / $inv['valor_actual']) - $inv['cantidad_invertida'];
                            } else { 
                                $beneficio = 0; 
                            }
                            
                            $win = $beneficio >= 0;
                            $rowColor = $win ? 'text-brand' : 'text-red-400';
                            $rowBg = $win ? 'bg-brand/10 border-brand/20' : 'bg-red-500/10 border-red-500/20';
                            $icon = $win ? '▲' : '▼';
                        ?>
                        <tr class="hover:bg-white/5 transition-colors group">
                            
                            <td class="p-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-dark-900 border border-white/10 flex items-center justify-center font-black text-xs text-white">
                                        <?php echo substr($inv['activo'], 0, 1); ?>
                                    </div>
                                    <span class="font-black uppercase text-white tracking-wider"><?php echo htmlspecialchars($inv['activo']); ?></span>
                                </div>
                            </td>
                            
                            <td class="p-6 text-right font-mono text-sm text-gray-300">
                                <?php echo number_format($inv['cantidad_invertida'], 2, ',', '.'); ?> €
                            </td>
                            
                            <td class="p-6 text-right font-mono text-sm text-gray-500">
                                <?php echo number_format($inv['valor_actual'], 2, ',', '.'); ?> €
                            </td>
                            
                            <td class="p-6 text-right font-mono text-sm text-white font-bold group-hover:text-blue-400 transition-colors">
                                <?php echo number_format($actual, 2, ',', '.'); ?> €
                            </td>
                            
                            <td class="p-6 text-right">
                                <div class="inline-flex items-center justify-end gap-2 px-3 py-1.5 rounded-lg border <?php echo $rowBg; ?> <?php echo $rowColor; ?>">
                                    <span class="text-[10px]"><?php echo $icon; ?></span>
                                    <span class="font-mono font-black text-sm">
                                        <?php echo ($win ? '+' : '') . number_format($beneficio, 2, ',', '.'); ?> €
                                    </span>
                                </div>
                            </td>
                            
                            <td class="p-6 text-center">
                                <button onclick="borrarInversion(<?php echo $inv['id']; ?>)" 
                                        class="p-2 rounded-lg text-gray-500 hover:bg-red-500/10 hover:text-red-500 transition-colors group-hover:opacity-100 opacity-50"
                                        title="Cerrar Posición">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="toast" class="fixed bottom-10 right-10 transform translate-y-20 opacity-0 transition-all duration-300 z-50 flex items-center gap-3 glass-panel border-brand/30 bg-brand/10 px-6 py-4 rounded-2xl shadow-[0_0_30px_rgba(0,255,163,0.2)]">
        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-brand">Operación Exitosa</p>
            <p class="text-white text-sm font-medium" id="toast-msg">Orden ejecutada en mercado.</p>
        </div>
    </div>

    <script>
        function showToast(msg, isError = false) {
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toast-msg');
            toastMsg.innerText = msg;
            
            if (isError) {
                toast.classList.replace('border-brand/30', 'border-red-500/30');
                toast.classList.replace('bg-brand/10', 'bg-red-500/10');
                toast.classList.replace('shadow-[0_0_30px_rgba(0,255,163,0.2)]', 'shadow-[0_0_30px_rgba(239,68,68,0.2)]');
                toast.querySelector('svg').classList.replace('text-brand', 'text-red-500');
                toast.querySelector('p').classList.replace('text-brand', 'text-red-500');
                toast.querySelector('p').innerText = "Error en Operación";
            }
            
            toast.classList.remove('translate-y-20', 'opacity-0');
            
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
                setTimeout(() => location.reload(), 300); // Recargar después de esconder
            }, 2000);
        }

        function registrar() {
            const activo = document.getElementById('activo').value.trim();
            const cantidad = parseFloat(document.getElementById('cantidad').value);

            if(!activo) {
                alert("Selecciona o escribe el Ticker de un activo.");
                return;
            }
            if(isNaN(cantidad) || cantidad <= 0) {
                alert("El capital invertido debe ser mayor a 0.");
                return;
            }

            const fd = new FormData();
            fd.append('activo', activo);
            fd.append('cantidad', cantidad);

            fetch('guardarInversion.php', { method: 'POST', body: fd })
                .then(r => r.text()).then(res => {
                    if(res.trim() === "ok") {
                        showToast(`Compra de ${activo} registrada en libro.`);
                    } else {
                        showToast(res, true);
                    }
                })
                .catch(err => showToast("Fallo de red conectando al servidor.", true));
        }

        function borrarInversion(id) {
            if(confirm('ATENCIÓN: ¿Estás seguro de que quieres cerrar esta posición y borrarla del registro?')) {
                fetch('eliminarInversiones.php?id=' + id)
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === 'ok') {
                            showToast("Posición cerrada correctamente.");
                        } else {
                            showToast(data, true);
                        }
                    })
                    .catch(err => showToast("Error de conexión al intentar borrar.", true));
            }
        }
    </script>
</body>
</html>