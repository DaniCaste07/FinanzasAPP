<?php

try {
    $conexion = new PDO("mysql:host=localhost;dbname=misfinanzas", "root", "");
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    echo "<p>Error en la base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
}