<?php
session_start();
require_once 'conexion.php'; // Asegúrate de que este archivo define $conexion

if (isset($_GET['id']) && isset($_SESSION['usuario_id'])) {
    $id = intval($_GET['id']);
    $uid = $_SESSION['usuario_id'];

    try {
        // Borramos solo si pertenece al usuario logueado
        $stmt = $conexion->prepare("DELETE FROM inversiones WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id, $uid]);
        
        // Verificamos si realmente se borró algo
        if ($stmt->rowCount() > 0) {
            echo "ok";
        } else {
            echo "No se encontró el registro o no tienes permiso.";
        }
    } catch (PDOException $e) {
        echo "Error de base de datos: " . $e->getMessage();
    }
} else {
    echo "Faltan parámetros (ID o Sesión).";
}
?>