<?php
// logout.php
session_start(); 

// 1. Limpiar todas las variables de sesión de la memoria de PHP
session_unset(); 

// 2. CORRECCIÓN CRÍTICA: Destruir la cookie física del navegador para evitar el bucle de roles
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruir la sesión físicamente en el servidor
session_destroy(); 

// 4. MEDIDA ANTI-CACHÉ: Forzar al navegador a pedir el menú de cero al cambiar de cuenta
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

// 5. Redirigir al archivo correcto de entrada
header("Location: login.php");
exit();
?>