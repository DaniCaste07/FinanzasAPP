<?php
session_start();
require_once 'conexion.php';

// Verificación de seguridad
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>InvestFlow - Simulador Hipotecario</title>
        <link rel="stylesheet" href="style.css">
        <style>
            /* Estilos específicos para que el simulador luzca impecable */
            .simulator-container {
                max-width: 800px;
                margin: 0 auto;
            }
            .result-display {
                background: rgba(0, 255, 136, 0.05);
                border: 1px solid var(--border);
                border-radius: 15px;
                padding: 30px;
                text-align: center;
                margin-top: 30px;
            }
            .result-display h2 {
                font-size: 3rem;
                color: var(--accent);
                margin: 10px 0;
            }
            .info-box {
                margin-top: 20px;
                font-size: 0.9rem;
                color: var(--text-muted);
                line-height: 1.6;
            }
            input[type="range"] {
                margin: 15px 0;
            }
        </style>
    </head>
    <body class="dashboard-layout">

        <nav class="sidebar">
            <h2>InvestFlow</h2>
            <ul>
                <li><a href="dashboard.php">Resumen General</a></li>
                <li><a href="inversiones.php">Mis Inversiones</a></li>
                <li><a href="hipotecas.php" class="active">Simulador Hipotecario</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>

        <main class="content">
            <header>
                <h1>Simulador de <span>Hipotecas</span></h1>
            </header>

            <div class="simulator-container">
                <div class="card">
                    <h3>Configuración del Préstamo</h3>

                    <div class="input-group">
                        <label>Capital solicitado: <strong id="txtCap">200.000</strong> €</label>
                        <input type="range" id="rangeCap" min="10000" max="1000000" step="5000" value="200000" oninput="updateSim()">
                    </div>

                    <div class="input-group">
                        <label>Tipo de Interés Anual: <strong id="txtInt">3.5</strong> %</label>
                        <input type="range" id="rangeInt" min="0.1" max="15" step="0.1" value="3.5" oninput="updateSim()">
                    </div>

                    <div class="input-group">
                        <label>Plazo de devolución: <strong id="txtAnn">25</strong> años</label>
                        <input type="range" id="rangeAnn" min="1" max="40" step="1" value="25" oninput="updateSim()">
                    </div>

                    <div class="result-display">
                        <p>Cuota Mensual Estimada</p>
                        <h2 id="totalCuota">0.00 €</h2>
                    </div>
                </div>
            </div>
        </main>

        <script>
            function updateSim() {
                // 1. Obtener valores de los sliders
                const cap = document.getElementById('rangeCap').value;
                const inte = document.getElementById('rangeInt').value;
                const ann = document.getElementById('rangeAnn').value;

                // 2. Actualizar etiquetas de texto visuales
                document.getElementById('txtCap').innerText = new Intl.NumberFormat('es-ES').format(cap);
                document.getElementById('txtInt').innerText = inte;
                document.getElementById('txtAnn').innerText = ann;

                // 3. Llamada AJAX al backend (Llamamos al archivo PHP que ejecuta el JAR)
                fetch(`calcularHipoteca.php?cap=${cap}&int=${inte}&ann=${ann}`)
                        .then(response => response.text())
                        .then(resultado => {
                            // Limpiamos espacios o caracteres raros que pueda enviar el servidor (trim)
                            const valorLimpio = resultado.trim();

                            // Verificamos si lo que devuelve el motor Java es un número válido
                            if (!isNaN(valorLimpio) && valorLimpio !== "") {
                                const cuotaFormateada = parseFloat(valorLimpio).toLocaleString('es-ES', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                document.getElementById('totalCuota').innerText = cuotaFormateada + " €";
                            } else {
                                document.getElementById('totalCuota').innerText = "Error Motor";
                                console.error("Respuesta no numérica del servidor (Java/PHP):", resultado);
                            }
                        })
                        .catch(error => {
                            console.error("Error en la petición Fetch:", error);
                            document.getElementById('totalCuota').innerText = "Error Conexión";
                        });
            }

            // Ejecutar al cargar la página para que no aparezca "0.00 €" al inicio
            window.onload = updateSim;
        </script>
    </body>
</html>