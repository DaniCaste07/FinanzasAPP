<?php

include('conexion.php');

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_raw = $_POST['password'] ?? '';

    if (empty($nombre) || empty($email) || empty($password_raw)) {

        $mensaje = "<div class='alert error'>Todos los campos son obligatorios.</div>";

    } else {

        try {

            // Verificar si el email ya existe
            $check = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
            $check->execute([$email]);

            if ($check->fetch()) {

                $mensaje = "<div class='alert error'>El correo ya está registrado.</div>";

            } else {

                $password = password_hash($password_raw, PASSWORD_BCRYPT);

                $sql = "INSERT INTO usuarios (nombre, email, password, rol)
                        VALUES (?, ?, ?, ?)";

                $stmt = $conexion->prepare($sql);

                $stmt->execute([
                    $nombre,
                    $email,
                    $password,
                    'usuario'
                ]);

                header("Location: index.php?registro=exito");
                exit();

            }

        } catch (PDOException $e) {

            $mensaje = "<div class='alert error'>Error interno del sistema.</div>";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewpo<?php
include('conexion.php');

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_raw = $_POST['password'] ?? '';

    if (empty($nombre) || empty($email) || empty($password_raw)) {

        $mensaje = "<div class='alert error'>Todos los campos son obligatorios.</div>";

    } else {

        $password = password_hash($password_raw, PASSWORD_BCRYPT);

        try {

            $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);

            if ($stmt->execute([$nombre, $email, $password, 'usuario'])) {
                header("Location: index.php?registro=exito");
                exit();
            }

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                $mensaje = "<div class='alert error'>El correo ya está registrado.</div>";
            } else {
                $mensaje = "<div class='alert error'>Error en el servidor. Inténtalo más tarde.</div>";
            }

        }

    }
}
?>

<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Crear Cuenta</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            950: '#030712',
                            900: '#0b1120',
                            800: '#111827',
                            700: '#1f2937'
                        },
                        brand: {
                            DEFAULT: '#00ffa3',
                            hover: '#00e693'
                        }
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif']
                    },
                    animation: {
                        'blob': 'blob 10s infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 3s infinite',
                        'float-fast': 'float 4s ease-in-out 1s infinite',
                        'ticker': 'ticker 60s linear infinite',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        ticker: {
                            '0%': { transform: 'translateX(0)' },
                            '100%': { transform: 'translateX(-50%)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #030712;
            overflow-x: hidden;
            font-family: 'Outfit', sans-serif;
        }

        .glass-panel {
            background: rgba(17, 24, 39, 0.4);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .input-field {
            background: rgba(3, 7, 18, 0.6);
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
        }

        .input-field:focus {
            border-color: #00ffa3;
            box-shadow: 0 0 15px rgba(0, 255, 163, 0.2);
            background: rgba(0, 255, 163, 0.02);
        }

        .alert {
            padding: 14px;
            border-radius: 14px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .12em;
        }

        .success {
            background: rgba(0, 255, 163, 0.1);
            color: #00ffa3;
            border: 1px solid rgba(0, 255, 163, 0.4);
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
        }

        .ticker-wrap {
            width: 100%;
            height: 48px;
            overflow: hidden;
            background: rgba(3, 7, 18, 0.95);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
        }

        .ticker-content {
            display: flex;
            align-items: center;
            width: max-content;
            height: 100%;
        }

        .ticker-item {
            display: flex;
            align-items: center;
            padding: 0 2.5rem;
            height: 100%;
            white-space: nowrap;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col text-gray-100 relative">

    <div class="fixed top-0 left-1/4 w-96 h-96 bg-brand/10 rounded-full mix-blend-screen filter blur-[100px] opacity-70 animate-blob pointer-events-none z-0"></div>
    <div class="fixed top-1/3 right-1/4 w-96 h-96 bg-blue-600/10 rounded-full mix-blend-screen filter blur-[100px] opacity-70 animate-blob pointer-events-none z-0"></div>
    <div class="fixed -bottom-32 left-1/3 w-96 h-96 bg-purple-600/10 rounded-full mix-blend-screen filter blur-[100px] opacity-70 animate-blob pointer-events-none z-0"></div>

    <div class="ticker-wrap z-50 relative shadow-md shrink-0">
        <div class="ticker-content animate-ticker text-[11px] font-bold uppercase tracking-widest text-gray-400" id="live-ticker">
        </div>
    </div>

    <nav class="relative z-50 w-full px-8 py-6 flex justify-between items-center shrink-0">
        <a href="index.php" class="flex items-center gap-3 group">
            <div class="w-10 h-10 bg-brand text-dark-950 rounded-xl flex items-center justify-center font-black text-xl shadow-[0_0_20px_rgba(0,255,163,0.5)] group-hover:scale-105 transition-transform">
                IF
            </div>
            <span class="text-2xl font-black tracking-tight text-white">
                Invest<span class="text-brand">Flow</span>
            </span>
        </a>

        <div class="hidden md:flex items-center gap-8">
            <span class="text-sm font-semibold text-gray-400 bg-white/5 px-4 py-1.5 rounded-full border border-white/10 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand animate-pulse"></span>
                Registro Seguro
            </span>

            <a href="index.php" class="text-sm font-bold text-white hover:text-brand transition-colors">
                Ya tengo cuenta →
            </a>
        </div>
    </nav>

    <main class="flex-grow flex items-center justify-center px-4 md:px-8 py-12 relative z-10 w-full max-w-7xl mx-auto">

        <div class="w-full flex flex-col lg:flex-row items-center gap-16">

            <div class="w-full lg:w-5/12 z-40">

                <div class="glass-panel p-10 rounded-[2rem] shadow-2xl relative">

                    <div class="absolute top-0 left-10 w-20 h-1 bg-gradient-to-r from-brand to-transparent rounded-b-full"></div>

                    <p class="text-brand text-xs font-black uppercase tracking-widest mb-3">
                        Alta de usuario
                    </p>

                    <h1 class="text-4xl font-black text-white mb-2">
                        Crear Cuenta
                    </h1>

                    <p class="text-gray-400 text-sm font-medium mb-10">
                        Regístrate para acceder a tu panel financiero InvestFlow.
                    </p>

                    <?php echo $mensaje; ?>

                    <form action="registro.php" method="POST" class="space-y-6">

                        <div>
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] ml-2 mb-2 block">
                                Nombre Completo
                            </label>

                            <input type="text"
                                   name="nombre"
                                   required
                                   class="input-field px-5 py-4 rounded-2xl text-white outline-none placeholder-gray-600 font-medium"
                                   placeholder="Ej. Daniel Castejón">
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] ml-2 mb-2 block">
                                Email de Acceso
                            </label>

                            <input type="email"
                                   name="email"
                                   required
                                   class="input-field px-5 py-4 rounded-2xl text-white outline-none placeholder-gray-600 font-medium"
                                   placeholder="usuario@investflow.com">
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] ml-2 mb-2 block">
                                Contraseña
                            </label>

                            <input type="password"
                                   name="password"
                                   required
                                   minlength="6"
                                   class="input-field px-5 py-4 rounded-2xl text-white outline-none placeholder-gray-600 font-medium"
                                   placeholder="••••••••">
                        </div>

                        <button type="submit"
                                class="w-full relative group mt-4">

                            <div class="absolute -inset-1 bg-brand rounded-2xl blur opacity-40 group-hover:opacity-100 transition duration-300"></div>

                            <div class="relative w-full bg-brand text-dark-950 py-4 rounded-2xl font-black text-lg uppercase tracking-widest hover:scale-[1.02] transition-transform flex justify-center items-center gap-2">

                                Crear Cuenta

                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>

                            </div>
                        </button>

                    </form>

                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-500">
                            ¿Ya tienes cuenta?
                            <a href="index.php" class="text-brand font-bold hover:text-white transition-colors">
                                Inicia sesión
                            </a>
                        </p>
                    </div>

                </div>

            </div>

            <div class="hidden lg:flex w-7/12 relative h-full min-h-[500px] items-center justify-center">

                <div class="text-center absolute z-0 opacity-[0.03] pointer-events-none">
                    <h2 class="text-[14rem] font-black leading-none">IF</h2>
                </div>

                <div class="absolute z-30 glass-panel p-6 rounded-3xl w-72 animate-float -left-8 top-[10%] border-l-4 border-l-brand shadow-2xl">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">
                        Perfil Financiero
                    </p>

                    <h3 class="text-3xl font-black text-white mb-4">
                        Nuevo <span class="text-brand text-lg">Usuario</span>
                    </h3>

                    <div class="w-full h-1.5 bg-dark-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-brand/50 to-brand w-[45%] relative">
                            <div class="absolute right-0 top-0 h-full w-2 bg-white blur-[2px]"></div>
                        </div>
                    </div>
                </div>

                <div class="absolute z-20 glass-panel p-6 rounded-3xl w-80 animate-float-delayed right-4 bottom-8 bg-gradient-to-br from-dark-900/80 to-blue-900/40 shadow-2xl">

                    <div class="flex items-center gap-4 mb-4">

                        <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center text-blue-400 border border-blue-500/30">

                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.105.895-2 2-2s2 .895 2 2-.895 2-2 2-2-.895-2-2zM6 20v-1a6 6 0 0112 0v1M4 4h16v4H4V4z"></path>
                            </svg>

                        </div>

                        <div>
                            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">
                                Seguridad
                            </p>
                            <p class="text-white font-bold">
                                Contraseña BCRYPT
                            </p>
                        </div>

                    </div>

                    <div class="flex justify-between text-xs font-mono text-gray-400 bg-dark-950/80 p-2.5 rounded-lg border border-white/5">
                        <span>Estado del registro:</span>
                        <span class="text-brand flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-brand animate-pulse shadow-[0_0_8px_#00ffa3]"></span>
                            Seguro
                        </span>
                    </div>

                </div>

                <div class="absolute z-10 glass-panel p-5 rounded-2xl w-56 animate-float-fast top-[5%] right-10 border border-white/10 shadow-2xl flex items-center gap-4">
                    <div class="relative w-12 h-12 rounded-full border-4 border-dark-800 flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full border-4 border-purple-500 border-l-transparent border-b-transparent transform rotate-45"></div>
                        <span class="text-xs font-black text-white">100%</span>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">
                            Acceso
                        </p>
                        <p class="text-sm font-bold text-white tracking-tight">
                            Verificado
                        </p>
                    </div>
                </div>

                <div class="absolute z-40 glass-panel px-4 py-3 rounded-xl animate-float bottom-14 left-4 border border-brand/20 flex items-center gap-3 shadow-lg bg-brand/5 backdrop-blur-md">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>

                    <span class="text-[10px] text-brand font-black uppercase tracking-widest">
                        Registro protegido
                    </span>
                </div>

            </div>

        </div>

    </main>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const tickerContainer = document.getElementById('live-ticker');

            const traditionalStocks = `
                <span class="ticker-item whitespace-nowrap"><span class="w-2 h-2 rounded-full bg-red-500 mr-2 shadow-[0_0_5px_#ef4444]"></span><span class="mr-2 text-white font-bold">S&P 500</span> <span class="font-mono text-red-400">$5,123.41 (-0.2%)</span></span>
                <span class="ticker-item whitespace-nowrap"><span class="w-2 h-2 rounded-full bg-brand mr-2 shadow-[0_0_5px_#00ffa3]"></span><span class="mr-2 text-white font-bold">AAPL</span> <span class="font-mono text-brand">$172.45 (+1.2%)</span></span>
                <span class="ticker-item whitespace-nowrap"><span class="w-2 h-2 rounded-full bg-red-500 mr-2 shadow-[0_0_5px_#ef4444]"></span><span class="mr-2 text-white font-bold">TSLA</span> <span class="font-mono text-red-400">$175.22 (-1.5%)</span></span>
                <span class="ticker-item whitespace-nowrap"><span class="w-2 h-2 rounded-full bg-brand mr-2 shadow-[0_0_5px_#00ffa3]"></span><span class="mr-2 text-white font-bold">NVDA</span> <span class="font-mono text-brand">$892.10 (+3.4%)</span></span>
            `;

            function updateTickerUI(cryptoDataHtml = '') {
                const baseBlock = traditionalStocks + cryptoDataHtml;
                tickerContainer.innerHTML = baseBlock.repeat(12);
            }

            updateTickerUI('');

            async function fetchCryptos() {
                try {
                    const response = await fetch('https://api.coincap.io/v2/assets?ids=bitcoin,ethereum,solana,binance-coin');
                    const { data } = await response.json();

                    let cryptoHtml = '';

                    data.forEach(coin => {
                        const price = parseFloat(coin.priceUsd);
                        const formattedPrice = price >= 1 ? price.toFixed(2) : price.toFixed(4);

                        cryptoHtml += `
                            <span class="ticker-item whitespace-nowrap">
                                <span class="w-2 h-2 rounded-full bg-brand mr-2 shadow-[0_0_5px_#00ffa3]"></span>
                                <span class="mr-2 text-white font-bold">${coin.symbol}/USD</span>
                                <span class="font-mono text-brand">$${formattedPrice}</span>
                            </span>
                        `;
                    });

                    updateTickerUI(cryptoHtml);

                } catch (error) {
                    console.log("Ticker funcionando con datos estáticos.");
                }
            }

            fetchCryptos();
            setInterval(fetchCryptos, 40000);
        });
    </script>

</body>
</html>