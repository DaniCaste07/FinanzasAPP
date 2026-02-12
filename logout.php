<?php
session_start(); // Unirse a la sesión actual
session_unset(); // Limpiar todas las variables de sesión
session_destroy(); // Destruir la sesión físicamente

// Redirigir al login con un mensaje opcional
header("Location: index.php");
exit();
?>