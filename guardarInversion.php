<?php
session_start();
include('conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['usuario_id'])) {
    $uid = $_SESSION['usuario_id'];
    $activo = $_POST['activo'];
    $cantidad = $_POST['cantidad'];
    $fecha = date('Y-m-d');

    try {
        // Usamos valor_actual igual a cantidad al empezar
        $sql = "INSERT INTO inversiones (usuario_id, activo, cantidad_invertida, valor_actual, fecha_compra) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$uid, $activo, $cantidad, $cantidad, $fecha]);
        
        echo "ok";
    } catch (PDOException $e) {
        echo "error";
    }
}
?>