<?php
session_start();
require_once 'conexion.php';

function getPrice($sym) {
    $sym = strtoupper(trim($sym));
    $url = "https://api.binance.com/api/v3/ticker/price?symbol=" . $sym . "EUR";
    $res = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if ($res) {
        $data = json_decode($res, true);
        return isset($data['price']) ? floatval($data['price']) : null;
    }
    return null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['usuario_id'])) {
    $cantidad = floatval($_POST['cantidad']);
    
    // BLOQUEO DE NEGATIVOS
    if ($cantidad <= 0) {
        die("La cantidad debe ser positiva.");
    }
    
    $precio = getPrice($_POST['activo']);
    if ($precio > 0) {
        try {
            $sql = "INSERT INTO inversiones (usuario_id, activo, cantidad_invertida, valor_actual, fecha_compra) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$_SESSION['usuario_id'], $_POST['activo'], $cantidad, $precio, date('Y-m-d')]);
            echo "ok";
        } catch (PDOException $e) { echo "error_db"; }
    } else { echo "No se pudo obtener el precio de este activo."; }
}
?>