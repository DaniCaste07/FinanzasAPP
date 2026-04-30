<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || !isset($_POST['mensaje'])) {
    die("Acceso denegado.");
}

$mensaje = strtolower(trim($_POST['mensaje']));

// Lógica de respuesta basada en palabras clave
if (strpos($mensaje, 'invertir') !== false || strpos($mensaje, 'recomienda') !== false || strpos($mensaje, 'comprar') !== false) {
    // Podrías hacer consultas a la BBDD aquí para ver su portafolio real
    echo "Analizando el mercado actual... Te recomiendo diversificar. Si buscas bajo riesgo, los ETFs ligados al S&P 500 son sólidos. Si buscas exposición asimétrica y toleras volatilidad, asignar un 5-10% a Bitcoin (BTC) o Solana (SOL) es la estrategia institucional del momento. Usa el módulo 'Gestor de Activos' para registrar la compra.";

} elseif (strpos($mensaje, 'libertad') !== false || strpos($mensaje, 'fire') !== false || strpos($mensaje, 'retiro') !== false) {
    echo "El movimiento FIRE se basa en la Regla del 4%. Ve a la pestaña 'Libertad Financiera' en el menú. Allí el algoritmo cruzará tus gastos mensuales para decirte exactamente cuánto capital necesitas acumular para poder vivir de rentas.";

} elseif (strpos($mensaje, 'hipoteca') !== false || strpos($mensaje, 'interes') !== false) {
    echo "Nuestro Simulador Hipotecario está conectado a un núcleo de Java que calcula la amortización exacta. Cuanto menor sea el TIN y menor el plazo, menos dinero regalarás al banco en intereses. ¡Pruébalo en la pestaña Simulador!";

} else {
    echo "Como asistente financiero virtual, puedo ayudarte a analizar tu portafolio, explicarte conceptos como el Interés Compuesto, o recomendarte distribuciones de activos. ¿En qué prefieres enfocarte hoy?";
}
?>