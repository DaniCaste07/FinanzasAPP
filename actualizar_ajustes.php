<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) { die("No autorizado"); }
$uid = $_SESSION['usuario_id'];
$accion = $_POST['accion'] ?? '';

if ($accion === 'perfil') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = "user_" . $uid . "_" . time() . "." . $ext;
        $ruta_destino = "uploads/" . $nombre_archivo;
        
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
            // Guardamos en la base de datos
            $stmt = $conexion->prepare("UPDATE usuarios SET avatar = ? WHERE id = ?");
            $stmt->execute([$nombre_archivo, $uid]);
            
            // ¡ESTO ES LO QUE ACTUALIZA EL SIDEBAR!
            $_SESSION['avatar'] = $nombre_archivo; 
        }
    }

    $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$nombre, $email, $uid])) {
        $_SESSION['nombre'] = $nombre;
        echo "ok";
    }
}

// Actualizar moneda, refresco y privacidad
if ($accion === 'preferencias') {
    $moneda = $_POST['moneda'];
    $refresco = (int)$_POST['refresco'];
    $privacidad = isset($_POST['privacidad']) ? 1 : 0;

    $stmt = $conexion->prepare("UPDATE usuarios SET moneda = ?, refresco_api = ?, modo_privacidad = ? WHERE id = ?");
    if ($stmt->execute([$moneda, $refresco, $privacidad, $uid])) {
        echo "ok";
    } else {
        echo "error";
    }
}

// Actualizar contraseña con encriptación BCRYPT
if ($accion === 'password') {
    $pass = $_POST['password'];
    if (!empty($pass)) {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        if ($stmt->execute([$hash, $uid])) {
            echo "ok";
        } else {
            echo "error";
        }
    } else {
        echo "vacio";
    }
}
?>