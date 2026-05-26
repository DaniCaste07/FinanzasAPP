<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['usuario_id'];

// 1. Obtener la divisa y el refresco elegidos por el usuario desde la base de datos
$stmtUser = $conexion->prepare("SELECT moneda, refresco_api FROM usuarios WHERE id = ?");
$stmtUser->execute([$uid]);
$userPrefs = $stmtUser->fetch();

$monedaUsuario = $userPrefs['moneda'] ?? 'EUR';
$simboloMoneda = ($monedaUsuario === 'USD') ? '$' : '€';
$intervaloMilisegundos = ($userPrefs['refresco_api'] ?? 15) * 1000; 

// 2. Tasa de cambio dinámica: Si usa USD, obtenemos la cotización en vivo de Euro a Dólar mediante Binance
$tasaCambio = 1.0;
if ($monedaUsuario === 'USD') {
    $url = "https://api.binance.com/api/v3/ticker/price?symbol=EURUSDT";
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $response = @file_get_contents($url, false, $ctx);
    if ($response) {
        $data = json_decode($response, true);
        $tasaCambio = isset($data['price']) ? floatval($data['price']) : 1.08;
    } else {
        $tasaCambio = 1.08; // Fallback por si falla la red externa
    }
}

// 3. Consultamos las inversiones (que SIEMPRE están almacenadas en Euros en la BBDD)
$stmt = $conexion->prepare("SELECT * FROM inversiones WHERE usuario_id = ? ORDER BY id DESC");
$stmt->execute([$uid]);
$inversiones = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Mis Inversiones</title>
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
        .main-content { margin-left: 280px; padding: 40px; min-height: 100vh; }
        .glass-panel { background: rgba(17, 24, 39, 0.6); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .celda-precio { transition: color 0.5s ease; }
    </style>
</head>
<body class="text-gray-100 flex relative">

    <div class="fixed top-[-20%] right-[-10%] w-[800px] h-[800px] bg-brand/5 rounded-full blur-[150px] pointer-events-none z-0"></div>

    <?php require_once 'sidebar.php'; ?>

    <main class="main-content flex-1 z-10">
        <header class="mb-12 flex justify-between items-end">
            <div>
                <p class="text-brand text-xs font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                    <span class="w-2 h-2 bg-brand rounded-full animate-pulse shadow-[0_0_8px_#00ffa3]"></span>
                    Cartera en Tiempo Real
                </p>
                <h1 class="text-5xl font-black text-white">Inversiones</h1>
            </div>
            
            <div class="flex items-center gap-4">
                <button onclick="toggleModal()" class="bg-brand text-dark-950 font-black px-5 py-2.5 rounded-xl text-xs uppercase tracking-widest hover:scale-[1.03] transition-all shadow-[0_0_15px_rgba(0,255,163,0.3)] flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                    Añadir Activo
                </button>
            </div>
        </header>

        <div id="modalInversion" class="fixed inset-0 bg-dark-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
            <div class="glass-panel p-8 rounded-[2rem] w-full max-w-md border border-brand/20 shadow-[0_0_40px_rgba(0,255,163,0.15)] transform scale-95 transition-transform duration-300" id="modalContent">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-2xl font-black text-white">Nueva Orden</h3>
                        <p class="text-[10px] text-gray-500 uppercase tracking-widest font-bold mt-1">Ejecución a precio de mercado binance</p>
                    </div>
                    <button onclick="toggleModal()" class="text-gray-500 hover:text-red-400 bg-white/5 p-2 rounded-xl transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                
                <form id="formNuevaInversion" onsubmit="ejecutarInversion(event)" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block ml-1">Activo Soportado por la API</label>
                        <div class="relative group">
                            <select id="activoInput" required class="w-full bg-dark-900/50 border border-white/10 px-4 py-3.5 rounded-xl text-white outline-none focus:border-brand focus:bg-brand/5 transition-all font-bold appearance-none cursor-pointer hover:border-white/20">
                                <option value="" disabled selected>Selecciona un activo...</option>
                                
                                <optgroup label="Blue Chips (Mayor Capitalización)" class="bg-dark-950 text-gray-400 font-normal">
                                    <option value="BTC" class="text-white font-bold">Bitcoin (BTC)</option>
                                    <option value="ETH" class="text-white font-bold">Ethereum (ETH)</option>
                                    <option value="BNB" class="text-white font-bold">Binance Coin (BNB)</option>
                                    <option value="SOL" class="text-white font-bold">Solana (SOL)</option>
                                    <option value="XRP" class="text-white font-bold">Ripple (XRP)</option>
                                    <option value="ADA" class="text-white font-bold">Cardano (ADA)</option>
                                    <option value="AVAX" class="text-white font-bold">Avalanche (AVAX)</option>
                                    <option value="DOT" class="text-white font-bold">Polkadot (DOT)</option>
                                </optgroup>

                                <optgroup label="Redes Capa 1 y Capa 2 (Layer 1/2)" class="bg-dark-950 text-gray-400 font-normal">
                                    <option value="MATIC" class="text-white font-bold">Polygon (MATIC)</option>
                                    <option value="NEAR" class="text-white font-bold">NEAR Protocol (NEAR)</option>
                                    <option value="APT" class="text-white font-bold">Aptos (APT)</option>
                                    <option value="OP" class="text-white font-bold">Optimism (OP)</option>
                                    <option value="ARB" class="text-white font-bold">Arbitrum (ARB)</option>
                                    <option value="INJ" class="text-white font-bold">Injective (INJ)</option>
                                    <option value="FTM" class="text-white font-bold">Fantom (FTM)</option>
                                    <option value="KAS" class="text-white font-bold">Kaspa (KAS)</option>
                                    <option value="ATOM" class="text-white font-bold">Cosmos (ATOM)</option>
                                </optgroup>

                                <optgroup label="Clásicos e Históricos" class="bg-dark-950 text-gray-400 font-normal">
                                    <option value="LTC" class="text-white font-bold">Litecoin (LTC)</option>
                                    <option value="BCH" class="text-white font-bold">Bitcoin Cash (BCH)</option>
                                    <option value="TRX" class="text-white font-bold">Tron (TRX)</option>
                                    <option value="XLM" class="text-white font-bold">Stellar (XLM)</option>
                                    <option value="XMR" class="text-white font-bold">Monero (XMR)</option>
                                    <option value="ALGO" class="text-white font-bold">Algorand (ALGO)</option>
                                    <option value="VET" class="text-white font-bold">VeChain (VET)</option>
                                </optgroup>

                                <optgroup label="DeFi (Finanzas Descentralizadas)" class="bg-dark-950 text-gray-400 font-normal">
                                    <option value="UNI" class="text-white font-bold">Uniswap (UNI)</option>
                                    <option value="AAVE" class="text-white font-bold">Aave (AAVE)</option>
                                    <option value="MKR" class="text-white font-bold">Maker (MKR)</option>
                                    <option value="SNX" class="text-white font-bold">Synthetix (SNX)</option>
                                    <option value="CRV" class="text-white font-bold">Curve DAO (CRV)</option>
                                    <option value="LDO" class="text-white font-bold">Lido DAO (LDO)</option>
                                </optgroup>

                                <optgroup label="Web3, IA y Oráculos" class="bg-dark-950 text-gray-400 font-normal">
                                    <option value="LINK" class="text-white font-bold">Chainlink (LINK)</option>
                                    <option value="FET" class="text-white font-bold">Fetch.ai (FET)</option>
                                    <option value="RNDR" class="text-white font-bold">Render (RNDR)</option>
                                    <option value="GRT" class="text-white font-bold">The Graph (GRT)</option>
                                    <option value="FIL" class="text-white font-bold">Filecoin (FIL)</option>
                                    <option value="ICP" class="text-white font-bold">Internet Computer (ICP)</option>
                                </optgroup>

                                <optgroup label="Metaverso & Memecoins" class="bg-dark-950 text-gray-400 font-normal">
                                    <option value="DOGE" class="text-white font-bold">Dogecoin (DOGE)</option>
                                    <option value="SHIB" class="text-white font-bold">Shiba Inu (SHIB)</option>
                                    <option value="PEPE" class="text-white font-bold">Pepe (PEPE)</option>
                                    <option value="WIF" class="text-white font-bold">dogwifhat (WIF)</option>
                                    <option value="FLOKI" class="text-white font-bold">Floki (FLOKI)</option>
                                    <option value="APE" class="text-white font-bold">ApeCoin (APE)</option>
                                    <option value="SAND" class="text-white font-bold">The Sandbox (SAND)</option>
                                    <option value="MANA" class="text-white font-bold">Decentraland (MANA)</option>
                                    <option value="GALA" class="text-white font-bold">Gala (GALA)</option>
                                </optgroup>
                            </select>
                            <div class="absolute right-4 top-[14px] pointer-events-none text-gray-500 group-hover:text-brand transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block ml-1">Capital Asignado a la Compra</label>
                        <div class="relative">
                            <input type="number" id="cantidadInput" step="0.01" required class="w-full bg-dark-900/50 border border-white/10 px-4 py-3.5 rounded-xl text-white outline-none focus:border-brand focus:bg-brand/5 transition-all font-mono text-lg" placeholder="0.00">
                            <div class="absolute right-3 top-3 bg-dark-950 px-3 py-1 rounded-lg border border-white/5">
                                <span class="text-brand font-black"><?= $simboloMoneda ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" id="btnGuardar" class="w-full bg-brand text-dark-950 font-black py-4 rounded-xl uppercase tracking-widest text-xs hover:bg-brand/90 transition-colors shadow-[0_0_20px_rgba(0,255,163,0.2)]">
                        Confirmar Transacción
                    </button>
                </form>
            </div>
        </div>

        <div class="glass-panel rounded-[2.5rem] overflow-hidden shadow-2xl">
            <table class="w-full text-left">
                <thead class="bg-dark-950/80 text-[10px] font-black text-gray-500 uppercase tracking-widest border-b border-white/5">
                    <tr>
                        <th class="p-6">Activo</th>
                        <th class="p-6 text-right">Invertido</th>
                        <th class="p-6 text-right">Precio Entrada</th>
                        <th class="p-6 text-right">Precio Mercado</th>
                        <th class="p-6 text-right">Ganancia / Pérdida</th>
                        <th class="p-6 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach($inversiones as $inv): 
                        // CONVERSIÓN VISUAL INICIAL EN SERVIDOR: multiplicamos directo el Euro de la BBDD por la tasa cambiaria
                        $invertidoConvertido = $inv['cantidad_invertida'] * $tasaCambio;
                        $entradaConvertida = $inv['valor_actual'] * $tasaCambio;
                    ?>
                    <tr class="row-inversion group hover:bg-white/5 transition-colors" 
                        data-activo="<?php echo $inv['activo']; ?>" 
                        data-entrada="<?php echo $inv['valor_actual']; ?>" 
                        data-cantidad="<?php echo $inv['cantidad_invertida']; ?>">
                        
                        <td class="p-6">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-dark-900 border border-white/10 flex items-center justify-center font-black text-xs text-white uppercase group-hover:border-brand transition-colors">
                                    <?php echo substr($inv['activo'], 0, 2); ?>
                                </div>
                                <span class="font-black text-white uppercase tracking-wider"><?php echo htmlspecialchars($inv['activo']); ?></span>
                            </div>
                        </td>
                        
                        <td class="p-6 text-right font-mono text-gray-400">
                            <?php echo number_format($invertidoConvertido, 2, ',', '.'); ?> <?= $simboloMoneda ?>
                        </td>
                        
                        <td class="p-6 text-right font-mono text-gray-500">
                            <?php echo number_format($entradaConvertida, 2, ',', '.'); ?> <?= $simboloMoneda ?>
                        </td>
                        
                        <td class="p-6 text-right font-mono text-blue-400 font-bold celda-precio">
                            Cargando...
                        </td>
                        
                        <td class="p-6 text-right">
                            <span class="celda-beneficio px-3 py-1.5 rounded-lg font-mono font-black text-sm bg-dark-900 border border-white/5 transition-all duration-500">
                                --
                            </span>
                        </td>
                        
                        <td class="p-6 text-center">
                            <button onclick="borrarInversion(<?php echo $inv['id']; ?>)" class="p-2 rounded-lg text-gray-600 hover:text-red-500 transition-all opacity-40 group-hover:opacity-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php require_once 'footer.php'; ?>
    </main>

    <script>
        const formatDinero = new Intl.NumberFormat('es-ES', { style: 'currency', currency: '<?= $monedaUsuario ?>' });
        const tasaCambioJS = <?= $tasaCambio ?>;

        // Modales de Interfaz
        const modal = document.getElementById('modalInversion');
        const modalContent = document.getElementById('modalContent');
        
        function toggleModal() {
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    modalContent.classList.remove('scale-95');
                }, 10);
            } else {
                modal.classList.add('opacity-0');
                modalContent.classList.add('scale-95');
                setTimeout(() => modal.classList.add('hidden'), 300);
            }
        }

        // Enviar Inversión al Backend
        async function ejecutarInversion(e) {
            e.preventDefault();
            const btn = document.getElementById('btnGuardar');
            btn.innerText = "Procesando...";
            btn.classList.add('animate-pulse', 'opacity-50');

            const activo = document.getElementById('activoInput').value;
            const cantidadUsuario = parseFloat(document.getElementById('cantidadInput').value);

            // Si escribe en dólares, convertimos a Euros dividiendo antes de guardar en la BBDD
            const cantidadEuros = cantidadUsuario / tasaCambioJS;

            const formData = new FormData();
            formData.append('activo', activo);
            formData.append('cantidad', cantidadEuros); 

            try {
                const res = await fetch('guardarInversion.php', { method: 'POST', body: formData });
                const status = await res.text();
                
                if (status.trim() === 'ok') {
                    location.reload(); 
                } else {
                    alert("Error en la respuesta de la API interna.");
                    btn.innerText = "Confirmar Transacción";
                    btn.classList.remove('animate-pulse', 'opacity-50');
                }
            } catch (err) {
                alert("Fallo de enlace de red.");
            }
        }

        // MOTOR ASÍNCRONO DE PRECIOS EN TIEMPO REAL (CORREGIDO)
        async function refrescarMercado() {
            const filas = document.querySelectorAll('.row-inversion');
            if (filas.length === 0) return;

            const activos = Array.from(filas).map(f => f.dataset.activo).join(',');
            
            try {
                // EXTRACCIÓN CRÍTICA: Forzamos la API externa a traer los precios siempre en EUROS
                const response = await fetch(`api_precios.php?activos=${activos}&moneda=EUR`);
                const data = await response.json();

                filas.forEach(fila => {
                    const ticker = fila.dataset.activo;
                    const entradaEUR = parseFloat(fila.dataset.entrada);   // Euro nativo
                    const invertidoEUR = parseFloat(fila.dataset.cantidad); // Euro nativo
                    const actualEUR = data[ticker];                         // Euro en vivo de la API

                    if (actualEUR) {
                        const celdaPrecio = fila.querySelector('.celda-precio');
                        const celdaBen = fila.querySelector('.celda-beneficio');

                        celdaPrecio.style.color = '#fff';
                        setTimeout(() => { celdaPrecio.style.color = '#60a5fa'; }, 500);

                        // CONVERSIÓN VISUAL: Multiplicamos el precio en vivo por la tasa cambiaria para mostrar al usuario
                        const actualMostrar = actualEUR * tasaCambioJS;
                        celdaPrecio.innerText = formatDinero.format(actualMostrar);

                        // CÁLCULO MATEMÁTICO INTEGRAL EN EUROS (Previene inversiones de signo y ROI falsos)
                        const beneficioEUR = (invertidoEUR * actualEUR / entradaEUR) - invertidoEUR;
                        
                        // CONVERSIÓN VISUAL FINAL DEL BENEFICIO
                        const beneficioMostrar = beneficioEUR * tasaCambioJS;
                        
                        celdaBen.innerText = (beneficioMostrar >= 0 ? '+' : '') + beneficioMostrar.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' <?= $simboloMoneda ?>';
                        
                        if (beneficioMostrar >= 0) {
                            celdaBen.className = "celda-beneficio px-3 py-1.5 rounded-lg font-mono font-black text-sm bg-brand/10 text-brand border border-brand/20 shadow-[0_0_15px_rgba(0,255,163,0.1)]";
                        } else {
                            celdaBen.className = "celda-beneficio px-3 py-1.5 rounded-lg font-mono font-black text-sm bg-red-500/10 text-red-400 border border-red-500/20 shadow-[0_0_15px_rgba(239,68,68,0.1)]";
                        }
                    }
                });
            } catch (err) {
                console.error("Fallo de red en Mercado Live.");
            }
        }

        function borrarInversion(id) {
            if (confirm('¿Confirmas el cierre de esta posición?')) {
                fetch('eliminarInversiones.php?id=' + id)
                .then(r => r.text()).then(res => { if (res.trim() === 'ok') location.reload(); });
            }
        }

        refrescarMercado();
        setInterval(refrescarMercado, <?= $intervaloMilisegundos ?>);
    </script>
</body>
</html>