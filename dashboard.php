<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>InvestFlow - Dashboard</title>
        <link rel="stylesheet" href="style.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body class="dashboard-layout">
        <nav class="sidebar">
            <h2>InvestFlow</h2>
            <ul>
                <li><a href="dashboard.php">Resumen General</a></li>
                <li><a href="inversiones.php">Mis Inversiones</a></li>
                <li><a href="hipotecas.php">Simulador Hipotecario</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>

        <main class="content">
            <header>
                <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
            </header>

            <div class="grid-cards">
                <div class="card">
                    <h3>Patrimonio Total</h3>
                    <p class="price"><span id="total_patrimonio">0,00</span> €</p>
                    <span class="trend up">Actualizado</span>
                </div>

                <div class="card chart-container">
                    <h3>Distribución de Activos</h3>
                    <canvas id="dashboardChart"></canvas>
                </div>
            </div>
        </main>

        <script>
            // Llamamos al nuevo archivo unificado
            fetch('getDataChart.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error("Error del servidor:", data.error);
                            return;
                        }

                        // 1. Actualizamos el texto del patrimonio
                        document.getElementById('total_patrimonio').innerText = data.patrimonio;

                        // 2. Dibujamos el gráfico de donut
                        const ctx = document.getElementById('dashboardChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: data.chartLabels,
                                datasets: [{
                                        label: 'Valor en €',
                                        data: data.chartData,
                                        backgroundColor: ['#00ff88', '#00cc6e', '#1f2d40', '#94a3b8', '#ffffff'],
                                        hoverOffset: 4,
                                        borderWidth: 0
                                    }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {color: '#94a3b8'}
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => console.error('Error en fetch:', error));
        </script>
    </body>
</html>
