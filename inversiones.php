<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['usuario_id'];

// Función para obtener precios reales (Binance API)
function getLivePrice($activo) {
    $activo = strtoupper(trim($activo));
    // Mapeo para activos específicos si es necesario
    $map = ['BITCOIN' => 'BTCEUR', 'ETHEREUM' => 'ETHEUR', 'SOLANA' => 'SOLEUR', 'APPLE' => 'AAPLUSDT']; 
    $symbol = $map[$activo] ?? $activo . "EUR";

    $url = "https://api.binance.com/api/v3/ticker/price?symbol=$symbol";
    $ctx = stream_context_create(['http' => ['timeout' => 3]]);
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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>InvestFlow - Cartera Inteligente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { dark: { 900: '#0b111b', 800: '#162130', 700: '#1f2d40' }, brand: '#00ff88' } } } }
    </script>
    <style>
        body { background-color: #0b111b; font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; height: 100vh; position: fixed; left: 0; top: 0; border-right: 1px solid #1f2d40; background: #0b111b; z-index: 50; }
        .main-content { margin-left: 260px; padding: 40px; }
    </style>
</head>
<body class="text-gray-100 flex">

    <aside class="sidebar p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-2 mb-10">
                <div class="w-8 h-8 bg-brand rounded-lg flex items-center justify-center text-dark-900 font-bold shadow-[0_0_15px_rgba(0,255,136,0.3)]">$</div>
                <span class="text-xl font-bold text-brand italic tracking-tighter">InvestFlow</span>
            </div>
            <nav class="space-y-4">
                <a href="dashboard.php" class="block text-gray-400 hover:text-brand py-2 px-3 transition">Resumen General</a>
                <a href="inversiones.php" class="block text-brand font-bold bg-brand/10 p-2 rounded-lg py-2 px-3">Mis Inversiones</a>
                <a href="hipotecas.php" class="block text-gray-400 hover:text-brand py-2 px-3 transition">Simulador Hipotecario</a>
                <a href="planificador.php" class="block text-gray-400 hover:text-brand py-2 px-3 transition">Planificador</a>
                <a href="libertad.php" class="block text-gray-400 hover:text-brand py-2 px-3 transition">Libertad Financiera</a>
            </nav>
        </div>
        <a href="logout.php" class="text-gray-500 hover:text-red-400 px-3 pb-4">Cerrar Sesión</a>
    </aside>

    <main class="main-content flex-1">
        <header class="mb-8"><h1 class="text-4xl font-black italic">Mi Cartera <span class="text-brand">Pro</span></h1></header>

        <div class="bg-dark-800/50 p-6 rounded-3xl border border-dark-700 shadow-2xl mb-10 backdrop-blur-md">
            <form id="formInv" class="flex flex-wrap items-end gap-6">
                <div class="flex-1 min-w-[250px]">
                    <label class="text-[10px] font-bold text-gray-500 uppercase block mb-2 tracking-widest">Activo (Buscador 500+)</label>
                    <input list="assets-list" id="activo" class="w-full p-3 rounded-xl bg-dark-900 border border-dark-700 outline-none focus:border-brand text-white" placeholder="Escribe para buscar...">
                    <datalist id="assets-list">
                        <option value="BTC">Bitcoin (BTC)</option><option value="ETH">Ethereum (ETH)</option><option value="SOL">Solana (SOL)</option>
                        <option value="ADA">Cardano (ADA)</option><option value="XRP">Ripple (XRP)</option><option value="DOT">Polkadot (DOT)</option>
                        <option value="DOGE">Dogecoin (DOGE)</option><option value="AVAX">Avalanche (AVAX)</option><option value="LINK">Chainlink (LINK)</option>
                        <option value="SHIB">Shiba Inu (SHIB)</option><option value="MATIC">Polygon (MATIC)</option><option value="LTC">Litecoin (LTC)</option>
                        <option value="UNI">Uniswap (UNI)</option><option value="BCH">Bitcoin Cash (BCH)</option><option value="TRX">TRON (TRX)</option>
                        </datalist>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="text-[10px] font-bold text-gray-500 uppercase block mb-2 tracking-widest">Inversión (€)</label>
                    <input type="number" id="cantidad" min="0.01" step="0.01" class="w-full p-3 rounded-xl bg-dark-900 border border-dark-700 outline-none focus:border-brand text-white" placeholder="Mínimo 0.01">
                </div>
                <button type="button" onclick="registrar()" class="bg-brand text-dark-900 font-bold px-10 py-3.5 rounded-xl hover:scale-105 transition shadow-lg uppercase text-xs tracking-widest">
                    Ejecutar Compra
                </button>
            </form>
        </div>

        <div class="bg-dark-800 rounded-3xl border border-dark-700 overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-[800px]">
                    <thead class="bg-dark-900 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-dark-700">
                        <tr>
                            <th class="p-6">Activo</th>
                            <th class="p-6 text-right">Invertido</th>
                            <th class="p-6 text-right">P. Compra (DB)</th>
                            <th class="p-6 text-right">P. Actual (API)</th>
                            <th class="p-6 text-right">G/P Real</th>
                            <th class="p-6 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-700">
                        <?php foreach($inversiones as $inv): 
                            $actual = getLivePrice($inv['activo']) ?? $inv['valor_actual'];
                            // Fórmula de beneficio real
                            if ($inv['valor_actual'] > 0) {
                                $beneficio = ($inv['cantidad_invertida'] * $actual / $inv['valor_actual']) - $inv['cantidad_invertida'];
                            } else { $beneficio = 0; }
                            $win = $beneficio >= 0;
                        ?>
                        <tr class="hover:bg-dark-700/30 transition-all">
                            <td class="p-6 font-black uppercase text-white italic text-lg"><?php echo htmlspecialchars($inv['activo']); ?></td>
                            <td class="p-6 text-right font-mono <?php echo ($inv['cantidad_invertida'] < 0) ? 'text-red-400' : 'text-gray-400'; ?>">
                                <?php echo number_format($inv['cantidad_invertida'], 2); ?> €
                            </td>
                            <td class="p-6 text-right font-mono text-gray-500"><?php echo number_format($inv['valor_actual'], 2); ?> €</td>
                            <td class="p-6 text-right font-black text-brand font-mono"><?php echo number_format($actual, 2); ?> €</td>
                            <td class="p-6 text-right">
                                <span class="font-black px-4 py-1.5 rounded-full text-xs <?php echo $win ? 'text-brand bg-brand/10 border border-brand/20' : 'text-red-500 bg-red-500/10 border border-red-500/20'; ?>">
                                    <?php echo ($win ? '▲ +' : '▼ ') . number_format($beneficio, 2, ',', '.'); ?> €
                                </span>
                            </td>
                            <td class="p-6 text-center">
                                <button onclick="borrarInversion(<?php echo $inv['id']; ?>)" class="bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white px-4 py-2 rounded-xl border border-red-500/30 text-[10px] font-black transition-all">
                                    BORRAR
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function registrar() {
            const activo = document.getElementById('activo').value.trim();
            const cantidad = parseFloat(document.getElementById('cantidad').value);

            if(!activo) return alert("Selecciona un activo.");
            if(isNaN(cantidad) || cantidad <= 0) return alert("La inversión debe ser mayor a 0.");

            const fd = new FormData();
            fd.append('activo', activo);
            fd.append('cantidad', cantidad);

            fetch('guardarInversion.php', { method: 'POST', body: fd })
                .then(r => r.text()).then(res => {
                    if(res.trim() === "ok") location.reload();
                    else alert("Error al guardar: " + res);
                });
        }

        function borrarInversion(id) {
            if(confirm('¿Seguro que quieres eliminar esta inversión?')) {
                fetch('eliminarInversiones.php?id=' + id)
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === 'ok') {
                            location.reload();
                        } else {
                            alert('Error al borrar: ' + data);
                        }
                    })
                    .catch(err => alert('Error de conexión al borrar.'));
            }
        }
    </script>
</body>
</html>