<?php

session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || !isset($_POST['mensaje'])) {
    http_response_code(403);
    exit("Acceso denegado.");
}

$mensajeOriginal = trim($_POST['mensaje']);

function normalizarTexto($texto) {
    $texto = mb_strtolower($texto, 'UTF-8');

    $acentos = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'ü' => 'u', 'ñ' => 'n'
    ];

    return strtr($texto, $acentos);
}

function contiene($texto, $palabras) {
    foreach ($palabras as $palabra) {
        if (strpos($texto, $palabra) !== false) {
            return true;
        }
    }
    return false;
}

$mensaje = normalizarTexto($mensajeOriginal);

if (contiene($mensaje, ['invertir', 'inversion', 'inversiones', 'comprar', 'cripto', 'bitcoin', 'ethereum', 'solana', 'activo'])) {

    echo "Puedes usar el módulo de Mis Inversiones para registrar activos y ver su evolución. Como recomendación general, es importante diversificar y no concentrar todo el capital en un único activo.";
} elseif (contiene($mensaje, ['fire', 'libertad', 'retiro', 'jubilar', 'independencia financiera'])) {

    echo "El módulo FIRE calcula cuánto capital necesitarías para alcanzar independencia financiera usando tus gastos mensuales y una tasa de retiro. Es útil para estimar objetivos a largo plazo.";
} elseif (contiene($mensaje, ['hipoteca', 'prestamo', 'cuota', 'interes', 'tin', 'amortizacion'])) {

    echo "El simulador hipotecario permite calcular una cuota mensual estimada a partir del capital, el interés anual y el plazo. También muestra cuánto pagarías en intereses totales.";
} elseif (contiene($mensaje, ['noticia', 'noticias', 'mercado', 'blog', 'articulo'])) {

    echo "El módulo de noticias muestra publicaciones financieras creadas desde el panel de administración. Las noticias largas aparecen resumidas y pueden desplegarse con 'Leer más'.";
} elseif (contiene($mensaje, ['admin', 'administrador', 'usuario', 'rol', 'crear usuario'])) {

    echo "El panel de administración permite crear usuarios, asignar roles y publicar noticias. Solo pueden acceder los usuarios con rol de administrador.";
} elseif (contiene($mensaje, ['ajustes', 'configuracion', 'divisa', 'moneda', 'euro', 'dolar', 'usd', 'eur'])) {

    echo "Desde Ajustes puedes cambiar datos del perfil, contraseña, divisa principal y frecuencia de actualización. Si cambias la divisa, la plataforma adapta los importes mostrados.";
} elseif (contiene($mensaje, ['contrasena', 'password', 'clave', 'seguridad'])) {

    echo "La contraseña se puede cambiar desde Ajustes > Seguridad. La aplicación debe guardar las contraseñas cifradas usando password_hash para proteger los datos del usuario.";
} else {

    echo "Puedo ayudarte con inversiones, hipotecas, noticias, ajustes, administración, cambio de divisa o libertad financiera. Prueba a preguntarme por alguno de esos módulos.";
}
?>