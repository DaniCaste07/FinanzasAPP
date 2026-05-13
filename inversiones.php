<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['usuario_id'];

// Consultamos tus inversiones estandarizadas
$stmt = $conexion->prepare("SELECT * FROM inversiones WHERE usuario_id = ? ORDER BY id DESC");
$stmt->execute([$uid]);
$inversiones = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Gestión Live</title>
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
        body { background-color: #030712; font-family: 'Outfit', sans-serif; }
        .sidebar { width: 280px; height: 100vh; position: fixed; border-right: 1px solid rgba(255,255,255,0.05); }
        .main-content { margin-left: 280px; padding: 40px; }
        .glass-panel { background: rgba(17, 24, 39, 0.6); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="text-gray-100 flex">

    <?php require_once 'sidebar.php'; ?>

    <main class="main-content flex-1">
        <header class="mb-12 flex justify-between items-end">
            <div>
                <p class="text-brand text-xs font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                    <span class="w-2 h-2 bg-brand rounded-full animate-pulse"></span>
                    Terminal de Mercado Real
                </p>
                <h1 class="text-5xl font-black text-white">Mi Cartera</h1>
            </div>
            <div class="glass-panel px-4 py-2 rounded-xl border border-blue-500/20">
                <p class="font-mono text-xs text-blue-400 font-bold uppercase">Status: Live</p>
            </div>
        </header>

        <div class="glass-panel rounded-[2rem] overflow-hidden shadow-2xl">
            <table class="w-full text-left">
                <thead class="bg-dark-950/80 text-[10px] font-black text-gray-500 uppercase tracking-widest border-b border-white/5">
                    <tr>
                        <th class="p-6">Activo</th>
                        <th class="p-6 text-right">Invertido</th>
                        <th class="p-6 text-right">P. Compra (DB)</th>
                        <th class="p-6 text-right">P. Actual (Vivo)</th>
                        <th class="p-6 text-right">Beneficio Real</th>
                        <th class="p-6 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($inversiones as $inv): ?>
                    <tr class="row-inversion hover:bg-white/5 transition-colors" 
                        data-activo="<?php echo $inv['activo']; ?>" 
                        data-entrada="<?php echo $inv['valor_actual']; ?>" 
                        data-cantidad="<?php echo $inv['cantidad_invertida']; ?>">
                        
                        <td class="p-6 font-black text-white"><?php echo htmlspecialchars($inv['activo']); ?></td>
                        <td class="p-6 text-right font-mono"><?php echo number_format($inv['cantidad_invertida'], 2, ',', '.'); ?> €</td>
                        <td class="p-6 text-right font-mono text-gray-500"><?php echo number_format($inv['valor_actual'], 2, ',', '.'); ?> €</td>
                        
                        <td class="p-6 text-right font-mono text-blue-400 font-bold celda-precio">Cargando...</td>
                        
                        <td class="p-6 text-right">
                            <span class="celda-beneficio px-3 py-1.5 rounded-lg font-mono font-black text-sm bg-dark-900">--</span>
                        </td>
                        
                        <td class="p-6 text-center">
                            <button onclick="borrarInversion(<?php echo $inv['id']; ?>)" class="text-gray-600 hover:text-red-500 transition-all">
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
        const euro = new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' });

        async function actualizarUI() {
            const filas = document.querySelectorAll('.row-inversion');
            if (filas.length === 0) return;

            const activos = Array.from(filas).map(f => f.dataset.activo).join(',');
            
            try {
                const response = await fetch(`api_precios.php?activos=${activos}`);
                const data = await response.json();

                filas.forEach(fila => {
                    const ticker = fila.dataset.activo;
                    const entrada = parseFloat(fila.dataset.entrada);
                    const invertido = parseFloat(fila.dataset.cantidad);
                    const actual = data[ticker];

                    if (actual) {
                        fila.querySelector('.celda-precio').innerText = euro.format(actual);
                        
                        // Cálculo: Beneficio = (Invertido * Actual / Entrada) - Invertido
                        const beneficio = (invertido * actual / entrada) - invertido;
                        const celdaBen = fila.querySelector('.celda-beneficio');

                        celdaBen.innerText = (beneficio >= 0 ? '+' : '') + beneficio.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
                        celdaBen.className = `celda-beneficio px-3 py-1.5 rounded-lg font-mono font-black text-sm ${beneficio >= 0 ? 'bg-brand/10 text-brand' : 'bg-red-500/10 text-red-400'}`;
                    }
                });
            } catch (e) {
                console.error("Fallo de conexión Mercado Real.");
            }
        }

        function borrarInversion(id) {
            if(confirm('¿Deseas eliminar este registro?')) {
                fetch('eliminarInversiones.php?id=' + id)
                .then(r => r.text()).then(res => {
                    if(res.trim() === 'ok') location.reload();
                });
            }
        }

        // Ejecutar inmediatamente y luego cada 10 segundos
        actualizarUI();
        setInterval(actualizarUI, 10000);
    </script>
</body>
</html>