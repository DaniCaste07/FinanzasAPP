<?php

session_start();
require_once 'conexion.php'; 

if (!isset($_SESSION['usuario_id'])) {
    die(json_encode(['error' => 'Acceso denegado']));
}

$uid = $_SESSION['usuario_id'];

try {
    // 1. Calculamos el Patrimonio Total (Suma de todas las inversiones)
    $stmtTotal = $pdo->prepare("SELECT SUM(valor_actual) as total FROM inversiones WHERE usuario_id = ?");
    $stmtTotal->execute([$uid]);
    $resTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
    $totalSumado = $resTotal['total'] ?? 0;

    // 2. Consultamos los activos para el gráfico
    $stmtChart = $pdo->prepare("SELECT activo, valor_actual FROM inversiones WHERE usuario_id = ?");
    $stmtChart->execute([$uid]);
    $datosInversiones = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

    // 3. Empaquetamos todo en un solo JSON
    // Separamos labels y valores para que Chart.js lo lea directo
    echo json_encode([
        'patrimonio' => number_format($totalSumado, 2, ',', '.'),
        'chartLabels' => array_column($datosInversiones, 'activo'),
        'chartData' => array_column($datosInversiones, 'valor_actual')
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}