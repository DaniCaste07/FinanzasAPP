<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Consulta para obtener la lista de inversiones del usuario
$stmt = $conexion->prepare("SELECT * FROM inversiones WHERE usuario_id = ? ORDER BY fecha_compra DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$inversiones = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>InvestFlow - Mis Inversiones</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body class="dashboard-layout">
    <nav class="sidebar">
        <h2>InvestFlow</h2>
        <ul>
            <li><a href="dashboard.php">Resumen General</a></li>
            <li><a href="inversiones.php" class="active">Mis Inversiones</a></li>
            <li><a href="hipotecas.php">Simulador Hipotecario</a></li>
            <li><a href="logout.php">Cerrar Sesión</a></li>
        </ul>
    </nav>

    <main class="content">
        <header>
            <h1>Gestión de <span>Inversiones</span></h1>
        </header>

        <div class="grid-cards">
            <div class="card">
                <h3>Añadir Nuevo Activo</h3>
                <form id="formInversion">
                    <div class="input-group">
                        <label>Nombre del Activo</label>
                        <input type="text" id="activo" required placeholder="Ej: Apple Inc.">
                    </div>
                    <div class="input-group">
                        <label>Inversión Inicial (€)</label>
                        <input type="number" id="cantidad" step="0.01" required>
                    </div>
                    <button type="button" onclick="guardarInversion()" class="btn-primary">Registrar Compra</button>
                </form>
            </div>

            <div class="card chart-container">
                <h3>Cartera Actual</h3>
                <table class="fintech-table">
                    <thead>
                        <tr>
                            <th>Activo</th>
                            <th>Invertido</th>
                            <th>Valor Actual</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($inversiones as $inv): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($inv['activo']); ?></strong></td>
                            <td><?php echo number_format($inv['cantidad_invertida'], 2); ?> €</td>
                            <td class="accent-text"><?php echo number_format($inv['valor_actual'], 2); ?> €</td>
                            <td><?php echo $inv['fecha_compra']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="js/operaciones.js"></script>
</body>
</html>