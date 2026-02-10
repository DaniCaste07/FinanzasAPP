<?php
function obtenerPrecioActual($activo) {
    $activo = strtolower(trim($activo));
    
    // Mapeo de nombres comunes a IDs de la API (CoinGecko)
    $ids = [
        'bitcoin' => 'bitcoin',
        'ethereum' => 'ethereum',
        'solana' => 'solana',
        'gold' => 'pax-gold', // Oro tokenizado
        'oro' => 'pax-gold'
    ];

    if (array_key_exists($activo, $ids)) {
        $idApi = $ids[$activo];
        $url = "https://api.coingecko.com/api/v3/simple/price?ids=$idApi&vs_currencies=eur";
        
        // Configuración para evitar bloqueos de la API
        $options = ["http" => ["header" => "User-Agent: InvestFlowApp/1.0\r\n"]];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response) {
            $data = json_decode($response, true);
            return $data[$idApi]['eur'] ?? null;
        }
    }

    // Para activos que no encuentre (como Apple o Tesla), devolveremos el valor guardado + un pequeño azar
    // En un entorno real, aquí usarías una API como Alpha Vantage o Twelve Data.
    return null; 
}
?>