<?php
// Recogemos los datos de la URL
$cap = $_GET['cap'] ?? 150000;
$int = $_GET['int'] ?? 3.5;
$ann = $_GET['ann'] ?? 25;

$jarPath = "MisFinanzasApp.jar"; 

$comando = "java -cp $jarPath CalculadoraHipotecaria $cap $int $ann 2>&1";

$resultado = shell_exec($comando);

// Enviamos el resultado limpio (solo el número) a JavaScript
echo trim($resultado); 
?>