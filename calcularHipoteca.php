<?php
// 1. SANITIZACIÓN ESTRICTA DE ENTRADAS
// filter_input asegura que lo que entra por URL sea exclusivamente un número, evitando Inyección de Comandos.
$cap = filter_input(INPUT_GET, 'cap', FILTER_VALIDATE_FLOAT);
$int = filter_input(INPUT_GET, 'int', FILTER_VALIDATE_FLOAT);
$ann = filter_input(INPUT_GET, 'ann', FILTER_VALIDATE_INT);

// Valores por defecto por si el usuario intenta borrar las variables de la URL
if ($cap === false || $cap === null) $cap = 150000;
if ($int === false || $int === null) $int = 3.5;
if ($ann === false || $ann === null) $ann = 25;

// 2. PREPARACIÓN SEGURA DEL COMANDO DE SHELL
// escapeshellarg añade comillas alrededor de la cadena y escapa cualquier comilla existente, 
// garantizando que el sistema operativo lo trate como una única cadena segura.
$jarPath = escapeshellarg("MisFinanzasApp.jar");

// 3. CONSTRUCCIÓN Y EJECUCIÓN (sprintf evita concatenación directa)
// %F asegura que sea interpretado como número de punto flotante (sin notación científica)
// %d asegura que sea interpretado como un número entero.
$comando = sprintf("java -cp %s CalculadoraHipotecaria %F %F %d 2>&1", $jarPath, $cap, $int, $ann);

// 4. EJECUCIÓN DE LA COMUNICACIÓN CON JAVA
$resultado = shell_exec($comando);

// Enviamos el resultado limpio (solo el número) de vuelta a JavaScript
echo trim($resultado); 
?>