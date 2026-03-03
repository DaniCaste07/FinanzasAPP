<?php
session_start();
require_once 'conexion.php'; 

$error = "";

if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_btn'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $conexion->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Acceso denegado. Credenciales incorrectas.";
        }
    } catch (PDOException $e) {
        $error = "Error de conexión con el servidor.";
    }
}
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow | Plataforma Financiera</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { 950: '#030712', 900: '#0b1120', 800: '#111827', 700: '#1f2937' },
                        brand: { DEFAULT: '#00ffa3', hover: '#00e693' }
                    },
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
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
        /* AQUÍ ESTÁ EL ARREGLO PRINCIPAL: Permitimos scroll vertical si no cabe */
        body { background-color: #030712; overflow-x: hidden; } 
        .glass-panel { 
            background: rgba(17, 24, 39, 0.4); 
            backdrop-filter: blur(20px); 
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08); 
        }
        .text-glow { text-shadow: 0 0 20px rgba(0, 255, 163, 0.5); }
        .input-field {
            background: rgba(3, 7, 18, 0.6);
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .input-field:focus {
            border-color: #00ffa3;
            box-shadow: 0 0 15px rgba(0, 255, 163, 0.2);
            background: rgba(0, 255, 163, 0.02);
        }
        
        /* Ticker Continuo CSS */
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
    <div class="fixed top-1/3 right-1/4 w-96 h-96 bg-blue-600/10 rounded-full mix-blend-screen filter blur-[100px] opacity-70 animate-blob animation-delay-2000 pointer-events-none z-0"></div>
    <div class="fixed -bottom-32 left-1/3 w-96 h-96 bg-purple-600/10 rounded-full mix-blend-screen filter blur-[100px] opacity-70 animate-blob animation-delay-4000 pointer-events-none z-0"></div>

    <div class="ticker-wrap z-50 relative shadow-md shrink-0">
        <div class="ticker-content animate-ticker text-[11px] font-bold uppercase tracking-widest text-gray-400" id="live-ticker">
            </div>
    </div>

    <nav class="relative z-50 w-full px-8 py-6 flex justify-between items-center shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-brand text-dark-950 rounded-xl flex items-center justify-center font-black text-xl shadow-[0_0_20px_rgba(0,255,163,0.5)]">
                IF
            </div>
            <span class="text-2xl font-black tracking-tight text-white">Invest<span class="text-brand">Flow</span></span>
        </div>
        <div class="hidden md:flex items-center gap-8">
            <span class="text-sm font-semibold text-gray-400 bg-white/5 px-4 py-1.5 rounded-full border border-white/10 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand animate-pulse"></span> Sistema Operativo
            </span>
            <a href="registro.php" class="text-sm font-bold text-white hover:text-brand transition-colors">Abrir Cuenta Nueva →</a>
        </div>
    </nav>

    <main class="flex-grow flex items-center justify-center px-4 md:px-8 py-12 relative z-10 w-full max-w-7xl mx-auto">
        <div class="w-full flex flex-col lg:flex-row items-center gap-16">
            
            <div class="w-full lg:w-5/12 z-40">
                <div class="glass-panel p-10 rounded-[2rem] shadow-2xl relative">
                    <div class="absolute top-0 left-10 w-20 h-1 bg-gradient-to-r from-brand to-transparent rounded-b-full"></div>
                    
                    <h1 class="text-4xl font-black text-white mb-2">Terminal Core</h1>
                    <p class="text-gray-400 text-sm font-medium mb-10">Conéctate al motor de análisis financiero.</p>

                    <form action="" method="POST" class="space-y-6">
                        <?php if ($error): ?>
                            <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-xl text-xs font-bold uppercase tracking-widest text-center animate-pulse">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <div>
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] ml-2 mb-2 block">Email de Acceso</label>
                            <input type="email" name="email" required 
                                   class="input-field w-full px-5 py-4 rounded-2xl text-white outline-none placeholder-gray-600 font-medium" 
                                   placeholder="usuario@finanzas.com">
                        </div>

                        <div>
                            <div class="flex justify-between items-center ml-2 mb-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em]">Clave de Cifrado</label>
                                <a href="#" class="text-[10px] text-brand/70 hover:text-brand uppercase font-bold tracking-wider">¿Restablecer?</a>
                            </div>
                            <input type="password" name="password" required 
                                   class="input-field w-full px-5 py-4 rounded-2xl text-white outline-none placeholder-gray-600 font-medium" 
                                   placeholder="••••••••">
                        </div>

                        <button type="submit" name="login_btn" 
                                class="w-full relative group mt-4">
                            <div class="absolute -inset-1 bg-brand rounded-2xl blur opacity-40 group-hover:opacity-100 transition duration-300"></div>
                            <div class="relative w-full bg-brand text-dark-950 py-4 rounded-2xl font-black text-lg uppercase tracking-widest hover:scale-[1.02] transition-transform flex justify-center items-center gap-2">
                                Iniciar Sesión
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </div>
                        </button>
                    </form>
                </div>
            </div>

            <div class="hidden lg:flex w-7/12 relative h-full min-h-[500px] items-center justify-center">
                
                <div class="text-center absolute z-0 opacity-[0.03] pointer-events-none">
                    <h2 class="text-[14rem] font-black leading-none">TFG</h2>
                </div>

                <div class="absolute z-30 glass-panel p-6 rounded-3xl w-72 animate-float -left-8 top-[10%] border-l-4 border-l-brand shadow-2xl">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Patrimonio Estimado</p>
                    <h3 class="text-3xl font-black text-white mb-4">124.500 <span class="text-brand text-lg">€</span></h3>
                    <div class="w-full h-1.5 bg-dark-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-brand/50 to-brand w-[75%] relative">
                            <div class="absolute right-0 top-0 h-full w-2 bg-white blur-[2px]"></div>
                        </div>
                    </div>
                </div>

                <div class="absolute z-20 glass-panel p-6 rounded-3xl w-80 animate-float-delayed right-4 bottom-8 bg-gradient-to-br from-dark-900/80 to-blue-900/40 shadow-2xl">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center text-blue-400 border border-blue-500/30">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11 5V2h2v3h-2zm0 14v3h2v-3h-2zM4.929 6.343l-2.121-2.121 1.414-1.414 2.121 2.121-1.414 1.414zm14.142 11.314l-2.121-2.121 1.414-1.414 2.121 2.121-1.414 1.414zM2 11h3v2H2v-2zm17 0h3v2h-3v-2zM6.343 19.071l-2.121 2.121-1.414-1.414 2.121-2.121 1.414 1.414zm11.314-14.142l-2.121 2.121-1.414-1.414 2.121-2.121 1.414 1.414zM12 7a5 5 0 100 10 5 5 0 000-10z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Motor Backend</p>
                            <p class="text-white font-bold">Java Calculator .JAR</p>
                        </div>
                    </div>
                    <div class="flex justify-between text-xs font-mono text-gray-400 bg-dark-950/80 p-2.5 rounded-lg border border-white/5">
                        <span>Estado del Socket:</span>
                        <span class="text-brand flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-brand animate-pulse shadow-[0_0_8px_#00ffa3]"></span> OK</span>
                    </div>
                </div>

                <div class="absolute z-10 glass-panel p-5 rounded-2xl w-56 animate-float-fast top-[5%] right-10 border border-white/10 shadow-2xl flex items-center gap-4">
                    <div class="relative w-12 h-12 rounded-full border-4 border-dark-800 flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full border-4 border-purple-500 border-l-transparent border-b-transparent transform rotate-45"></div>
                        <span class="text-xs font-black text-white">65%</span>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Objetivo FIRE</p>
                        <p class="text-sm font-bold text-white tracking-tight">En progreso</p>
                    </div>
                </div>

                <div class="absolute z-20 glass-panel p-4 rounded-2xl w-52 animate-float top-[2%] left-1/3 border border-white/5 shadow-2xl backdrop-blur-xl">
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-bold text-white text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-brand shadow-[0_0_5px_#00ffa3]"></span> AAPL/TSLA
                        </span>
                        <span class="text-brand text-xs font-black">+4.2%</span>
                    </div>
                    <svg class="w-full h-10 drop-shadow-[0_0_8px_rgba(0,255,163,0.4)]" viewBox="0 0 100 40" fill="none" stroke="#00ffa3" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 35 L 20 25 L 35 28 L 50 15 L 65 20 L 80 5 L 95 10" />
                    </svg>
                </div>

                <div class="absolute z-40 glass-panel px-4 py-3 rounded-xl animate-float bottom-14 left-4 border border-brand/20 flex items-center gap-3 shadow-lg bg-brand/5 backdrop-blur-md">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span class="text-[10px] text-brand font-black uppercase tracking-widest">Cifrado AES-256</span>
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
                <span class="ticker-item whitespace-nowrap"><span class="w-2 h-2 rounded-full bg-brand mr-2 shadow-[0_0_5px_#00ffa3]"></span><span class="mr-2 text-white font-bold">MSFT</span> <span class="font-mono text-brand">$420.55 (+0.8%)</span></span>
                <span class="ticker-item whitespace-nowrap"><span class="w-2 h-2 rounded-full bg-brand mr-2 shadow-[0_0_5px_#00ffa3]"></span><span class="mr-2 text-white font-bold">AMZN</span> <span class="font-mono text-brand">$178.15 (+1.5%)</span></span>
            `;

            function updateTickerUI(cryptoDataHtml = '') {
                const baseBlock = traditionalStocks + cryptoDataHtml;
                const repeatedBlock = baseBlock.repeat(6);
                tickerContainer.innerHTML = repeatedBlock + repeatedBlock;
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
                    console.log("Manteniendo datos estáticos debido a error de red/bloqueador.");
                }
            }

            fetchCryptos();
            setInterval(fetchCryptos, 40000);
        });
    </script>
</body>
</html>