<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
$nombre = $_SESSION['nombre'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es" class="dark">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>InvestFlow - Documentación del Sistema</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">

        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="style.css">
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            dark: {950: '#030712', 900: '#0b1120', 800: '#111827', 700: '#1f2937'},
                            brand: {DEFAULT: '#00ffa3', hover: '#00e693'}
                        },
                        fontFamily: {sans: ['Outfit', 'sans-serif']}
                    }
                }
            }
        </script>
    </head>
    <body class="text-gray-100 flex relative">

        <div class="fixed top-[-20%] left-[-10%] w-[800px] h-[800px] bg-brand/5 rounded-full blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed bottom-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-600/5 rounded-full blur-[150px] pointer-events-none z-0"></div>

        <?php require_once 'sidebar.php'; ?>

        <main class="main-content relative z-10 w-full">
            <div class="content-wrapper">

                <header class="mb-12 flex justify-between items-end">
                    <div>
                        <p class="text-brand text-xs font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 bg-brand rounded-full animate-pulse shadow-[0_0_8px_#00ffa3]"></span>
                            Documentación
                        </p>
                        <h1 class="text-5xl font-black tracking-tight text-white">
                            Manual del <span class="italic text-transparent bg-clip-text bg-gradient-to-r from-brand to-cyan-400">Sistema</span>
                        </h1>
                    </div> 
                </header>



                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">

                    <div class="glass-panel p-8 rounded-3xl hover:bg-white/5 transition-colors border border-white/5 hover:border-brand/30 group">
                        <div class="w-12 h-12 rounded-xl bg-dark-900 border border-white/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">1. Resumen General</h3>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            Es el panel principal. Calcula el patrimonio neto sumando todas tus inversiones y conectándose a la API para obtener el precio en tiempo real. Incluye un anillo gráfico de distribución y un <strong class="text-brand">módulo de IA</strong> que lee tus ganancias/pérdidas y te da consejos dinámicos.
                        </p>
                    </div>

                    <div class="glass-panel p-8 rounded-3xl hover:bg-white/5 transition-colors border border-white/5 hover:border-brand/30 group">
                        <div class="w-12 h-12 rounded-xl bg-dark-900 border border-white/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">2. Gestor de Inversiones</h3>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            Libro de órdenes personal. Permite registrar compras de activos (CRUD con base de datos). Muestra el precio de compra histórico frente al valor de mercado en vivo, calculando matemáticamente si tu posición está en beneficio o pérdida exacta.
                        </p>
                    </div>

                    <div class="glass-panel p-8 rounded-3xl hover:bg-white/5 transition-colors border border-white/5 hover:border-brand/30 group">
                        <div class="w-12 h-12 rounded-xl bg-dark-900 border border-white/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">3. Simulador Hipotecario</h3>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            Herramienta de precisión. En lugar de usar matemáticas básicas de navegador, esta interfaz envía los datos por consola a un <strong class="text-white">ejecutable compilado en Java (.jar)</strong> alojado en el servidor, que procesa la lógica de amortización financiera y devuelve el resultado a PHP.
                        </p>
                    </div>

                    <div class="glass-panel p-8 rounded-3xl hover:bg-white/5 transition-colors border border-white/5 hover:border-brand/30 group">
                        <div class="w-12 h-12 rounded-xl bg-dark-900 border border-white/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">4. Planificador (Interés Compuesto)</h3>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            Proyector exponencial de riqueza. Utiliza la fórmula del interés compuesto para predecir el crecimiento a largo plazo basándose en aportaciones mensuales. Renderiza un gráfico dinámico donde se aprecia visualmente el efecto "bola de nieve" de los intereses sobre intereses.
                        </p>
                    </div>

                    <div class="glass-panel p-8 rounded-3xl hover:bg-white/5 transition-colors border border-white/5 hover:border-brand/30 group md:col-span-2">

                        <div class="w-12 h-12 rounded-xl bg-dark-900 border border-white/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">

                            <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-4H9m10 8H7"></path>
                            </svg>

                        </div>

                        <h3 class="text-lg font-bold text-white mb-2">
                            5. Noticias y Mercado en Tiempo Real
                        </h3>

                        <p class="text-sm text-gray-400 leading-relaxed max-w-3xl">

                            Este módulo integra un sistema de <strong class="text-white">noticias financieras y cotizaciones en tiempo real</strong> mediante APIs externas. 
                            La plataforma obtiene automáticamente información actualizada sobre criptomonedas, mercados y tendencias económicas, permitiendo al usuario mantenerse informado sin salir de la aplicación.

                            Además, el sistema muestra precios dinámicos de activos financieros y actualiza los datos de mercado de forma automática para ofrecer una experiencia más cercana a plataformas fintech profesionales.

                        </p>

                    </div>

                </div>

                <div class="mt-4 text-center text-gray-600 text-xs font-mono uppercase tracking-widest">
                    InvestFlow App 
                </div>
            </div>

            <?php require_once 'footer.php'; ?>
        </main>

    </body>
</html>