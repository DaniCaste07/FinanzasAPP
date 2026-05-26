<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['usuario_id'];

$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

$avatar_actual = $_SESSION['avatar'] ?? $user['avatar'];

$foto_perfil = (!empty($avatar_actual) && $avatar_actual != 'default.png') ? 'uploads/' . $avatar_actual : 'https://ui-avatars.com/api/?name=' . urlencode($user['nombre']) . '&background=00ffa3&color=030712&bold=true';
?>

<!DOCTYPE html>
<html lang="es" class="dark">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>InvestFlow - Configuración</title>

        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">

        <script src="https://cdn.tailwindcss.com"></script>

        <link rel="stylesheet" href="style.css">

        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            dark: {
                                950: '#030712',
                                900: '#0b1120',
                                800: '#111827'
                            },
                            brand: '#00ffa3'
                        }
                    }
                }
            }
        </script>
    </head>

    <body class="bg-dark-950 text-gray-100 relative">

        <div class="fixed top-[-20%] right-[-10%] w-[800px] h-[800px] bg-brand/5 rounded-full blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed bottom-[-10%] left-[20%] w-[600px] h-[600px] bg-blue-500/5 rounded-full blur-[130px] pointer-events-none z-0"></div>

        <div id="toast-container" class="fixed top-6 right-6 z-50 space-y-4 pointer-events-none"></div>

        <?php require_once 'sidebar.php'; ?>

        <main class="main-content relative z-10">

            <div class="content-wrapper max-w-[1800px] mx-auto">

                <header class="mb-12">
                    <p class="text-brand text-xs font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 bg-brand rounded-full shadow-[0_0_8px_#00ffa3]"></span>
                        Seguridad & Preferencias del Terminal
                    </p>

                    <h1 class="text-5xl font-black text-white tracking-tight">
                        Configuración
                    </h1>

                    <p class="text-gray-500 mt-2 text-sm max-w-xl">
                        Administra los parámetros de tu perfil operativo, credenciales de acceso y preferencias de la plataforma.
                    </p>
                </header>

                <div class="settings-grid">

                    <!-- MENU LATERAL -->
                    <div class="space-y-3">

                        <button onclick="showTab('perfil')" id="btn-perfil"
                                class="tab-btn active w-full flex items-center gap-3.5 p-4 rounded-2xl border border-white/5 hover:bg-white/5 transition-all font-bold text-xs uppercase tracking-wider text-left">

                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>

                            Mi Perfil
                        </button>

                        <button onclick="showTab('seguridad')" id="btn-seguridad"
                                class="tab-btn w-full flex items-center gap-3.5 p-4 rounded-2xl border border-white/5 hover:bg-white/5 transition-all font-bold text-xs uppercase tracking-wider text-left text-gray-400">

                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>

                            Seguridad
                        </button>

                        <button onclick="showTab('preferencias')" id="btn-preferencias"
                                class="tab-btn w-full flex items-center gap-3.5 p-4 rounded-2xl border border-white/5 hover:bg-white/5 transition-all font-bold text-xs uppercase tracking-wider text-left text-gray-400">

                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0"></path>
                            </svg>

                            Preferencias
                        </button>

                    </div>

                    <!-- CONTENIDO -->
                    <div class="space-y-6 min-w-0">

                        <!-- PERFIL -->
                        <div id="tab-perfil" class="tab-content active glass-panel p-8 rounded-[2.5rem] space-y-8">

                            <div>
                                <h2 class="text-xl font-black text-white">
                                    Información de Cuenta
                                </h2>

                                <p class="text-xs text-gray-500 mt-1">
                                    Modifica tu identidad visual dentro de InvestFlow.
                                </p>
                            </div>

                            <form onsubmit="saveSettings(event)" enctype="multipart/form-data" class="space-y-8">

                                <input type="hidden" name="accion" value="perfil">

                                <div class="flex flex-col md:flex-row items-start md:items-center gap-6 p-6 bg-dark-900/40 rounded-3xl border border-white/5">

                                    <img src="<?= $foto_perfil ?>"
                                         class="w-20 h-20 rounded-2xl object-cover border border-brand/20 shadow-xl"
                                         id="img-preview">

                                    <div>
                                        <label class="bg-white/5 hover:bg-white/10 px-4 py-2.5 rounded-xl text-xs font-bold cursor-pointer transition-all border border-white/10 text-gray-200 inline-block">
                                            Subir Nueva Foto

                                            <input type="file" name="foto" class="hidden" onchange="preview(this)">
                                        </label>

                                        <p class="text-[10px] text-gray-500 mt-3 uppercase tracking-widest font-black">
                                            Formato JPG o PNG. Máximo 2MB.
                                        </p>
                                    </div>

                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block ml-1">
                                            Nombre Público
                                        </label>

                                        <input type="text"
                                               name="nombre"
                                               class="input-setting"
                                               value="<?= htmlspecialchars($user['nombre']) ?>"
                                               required>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block ml-1">
                                            Email Profesional
                                        </label>

                                        <input type="email"
                                               name="email"
                                               class="input-setting"
                                               value="<?= htmlspecialchars($user['email']) ?>"
                                               required>
                                    </div>

                                </div>

                                <button type="submit"
                                        class="bg-brand text-dark-950 font-black px-8 py-3.5 rounded-2xl hover:scale-[1.03] transition-all text-xs uppercase tracking-widest">
                                    Actualizar Perfil
                                </button>

                            </form>

                        </div>

                        <!-- SEGURIDAD -->
                        <div id="tab-seguridad" class="tab-content hidden glass-panel p-8 rounded-[2.5rem] space-y-8">

                            <div>
                                <h2 class="text-xl font-black text-white">
                                    Seguridad
                                </h2>

                                <p class="text-xs text-gray-500 mt-1">
                                    Actualiza periódicamente tus credenciales.
                                </p>
                            </div>

                            <form onsubmit="saveSettings(event)" class="space-y-6">

                                <input type="hidden" name="accion" value="password">

                                <div class="space-y-2 max-w-md">

                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block ml-1">
                                        Nueva Contraseña
                                    </label>

                                    <input type="password"
                                           name="password"
                                           class="input-setting"
                                           minlength="8"
                                           required>

                                </div>

                                <button type="submit"
                                        class="bg-white/10 hover:bg-white/15 text-white font-black px-7 py-3.5 rounded-xl border border-white/10 uppercase text-xs tracking-widest transition-all">
                                    Guardar Nueva Clave
                                </button>

                            </form>

                        </div>

                        <!-- PREFERENCIAS -->
                        <div id="tab-preferencias" class="tab-content hidden glass-panel p-8 rounded-[2.5rem] space-y-8">

                            <div>
                                <h2 class="text-xl font-black text-white">
                                    Preferencias
                                </h2>

                                <p class="text-xs text-gray-500 mt-1">
                                    Ajusta el comportamiento del sistema.
                                </p>
                            </div>

                            <form onsubmit="saveSettings(event)" class="grid grid-cols-1 md:grid-cols-2 gap-8">

                                <input type="hidden" name="accion" value="preferencias">

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block ml-1">
                                        Divisa Base
                                    </label>

                                    <select name="moneda" class="input-setting bg-dark-950">
                                        <option value="EUR">Euro (€)</option>
                                        <option value="USD">Dólar ($)</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block ml-1">
                                        Frecuencia API
                                    </label>

                                    <select name="refresco" class="input-setting bg-dark-950">
                                        <option value="15">15 Segundos</option>
                                        <option value="60">1 Minuto</option>
                                    </select>
                                </div>

                                <button type="submit"
                                        class="bg-brand text-dark-950 font-black px-7 py-3.5 rounded-xl col-span-2 uppercase text-xs tracking-widest transition-all">
                                    Guardar Preferencias
                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </main>

        <script>

            function preview(input) {

                if (input.files && input.files[0]) {

                    const reader = new FileReader();

                    reader.onload = e => {
                        document.getElementById('img-preview').src = e.target.result;
                    };

                    reader.readAsDataURL(input.files[0]);
                }
            }

            function showTab(id) {

                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.add('hidden');
                    tab.classList.remove('active');
                });

                const selectedTab = document.getElementById('tab-' + id);
                selectedTab.classList.remove('hidden');
                selectedTab.classList.add('active');

                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active', 'text-brand');
                    btn.classList.add('text-gray-400');
                });

                const activeBtn = document.getElementById('btn-' + id);
                activeBtn.classList.add('active', 'text-brand');
                activeBtn.classList.remove('text-gray-400');
            }

            async function saveSettings(e) {

                e.preventDefault();

                const formData = new FormData(e.target);

                try {

                    const res = await fetch('actualizar_ajustes.php', {
                        method: 'POST',
                        body: formData
                    });

                    const status = await res.text();

                    if (status.trim() === 'ok') {

                        location.reload();

                    } else {

                        alert('Error al guardar ajustes');
                    }

                } catch (err) {

                    alert('Error de conexión');
                }
            }

        </script>

    </body>
</html>