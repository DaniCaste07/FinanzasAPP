<?php
// api_precios.php
header('Content-Type: application/json');
error_reporting(0); 

/**
 * Obtiene el precio real desde CryptoCompare.
 * Al estar la BBDD estandarizada, ya no necesitamos mapas complejos.
 */
function obtenerPrecioMercado($ticker) {
    $ticker = strtoupper(trim($ticker));
    $url = "https://min-api.cryptocompare.com/data/price?fsym=$ticker&tsyms=EUR";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    // Ignoramos SSL para evitar fallos en XAMPP
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response && $httpCode == 200) {
        $data = json_decode($response, true);
        return isset($data['EUR']) ? floatval($data['EUR']) : null;
    }
    return null;
}

if (isset($_GET['activos'])) {
    $lista = explode(',', $_GET['activos']);
    $resultados = [];
    foreach ($lista as $a) {
        if(!empty($a)) {
            $resultados[$a] = obtenerPrecioMercado($a);
        }
    }
    echo json_encode($resultados);
}