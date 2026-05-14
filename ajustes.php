<?php
session_start();
require_once 'conexion.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }

$uid = $_SESSION['usuario_id'];
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

// Si no hay foto, usamos un avatar por defecto con su inicial
$foto_perfil = (!empty($user['avatar']) && $user['avatar'] != 'default.png') 
               ? 'uploads/' . $user['avatar'] 
               : 'https://ui-avatars.com/api/?name='.urlencode($user['nombre']).'&background=00ffa3&color=030712&bold=true';
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <title>InvestFlow - Ajustes Terminal</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { dark: { 950: '#030712', 900: '#0b1120', 800: '#111827' }, brand: '#00ffa3' } } }
        }
    </script>
    <style>
        body { background-color: #030712; font-family: 'Outfit', sans-serif; overflow-x: hidden; }
        .main-content { margin-left: 280px; padding: 40px; min-height: 100vh; }
        .glass-panel { background: rgba(17, 24, 39, 0.6); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .input-setting { background: rgba(3, 7, 18, 0.6); border: 1px solid rgba(255,255,255,0.1); padding: 12px 16px; border-radius: 12px; width: 100%; outline: none; transition: 0.3s; color: white; }
        .input-setting:focus { border-color: #00ffa3; box-shadow: 0 0 15px rgba(0, 255, 163, 0.1); }
        .tab-btn.active { background: rgba(0,255,163,0.1); color: #00ffa3; border-color: rgba(0,255,163,0.3); }
    </style>
</head>
<body class="text-gray-100 flex relative">

    <div class="fixed top-[-20%] right-[-10%] w-[800px] h-[800px] bg-brand/5 rounded-full blur-[150px] pointer-events-none z-0"></div>

    <?php require_once 'sidebar.php'; ?> <main class="main-content flex-1 z-10">
        <header class="mb-12">
            <h1 class="text-5xl font-black text-white">Configuración</h1>
            <p class="text-gray-500 mt-2">Personaliza tu experiencia en el terminal InvestFlow.</p>
        </header>

        <div class="grid grid-cols-12 gap-8">
            <div class="col-span-12 lg:col-span-3 space-y-2">
                <button onclick="showTab('perfil')" id="btn-perfil" class="tab-btn active w-full flex items-center gap-3 p-4 rounded-2xl border border-white/5 hover:bg-white/5 transition-all font-bold text-sm">Mi Perfil</button>
                <button onclick="showTab('seguridad')" id="btn-seguridad" class="tab-btn w-full flex items-center gap-3 p-4 rounded-2xl border border-white/5 hover:bg-white/5 transition-all font-bold text-sm text-gray-400">Seguridad</button>
                <button onclick="showTab('preferencias')" id="btn-preferencias" class="tab-btn w-full flex items-center gap-3 p-4 rounded-2xl border border-white/5 hover:bg-white/5 transition-all font-bold text-sm text-gray-400">Preferencias</button>
            </div>

            <div class="col-span-12 lg:col-span-9 space-y-6">
                
                <div id="tab-perfil" class="tab-content glass-panel p-8 rounded-[2.5rem] space-y-8 animate-in fade-in duration-500">
                    <form onsubmit="saveSettings(event)" enctype="multipart/form-data" class="space-y-8">
                        <input type="hidden" name="accion" value="perfil">
                        
                        <div class="flex items-center gap-6 p-6 bg-dark-900/40 rounded-3xl border border-white/5">
                            <img src="<?= $foto_perfil ?>" class="w-24 h-24 rounded-2xl object-cover border-2 border-brand/20 shadow-xl" id="img-preview">
                            <div>
                                <label class="bg-white/5 hover:bg-white/10 px-4 py-2 rounded-xl text-xs font-bold cursor-pointer transition-all border border-white/10">
                                    Seleccionar Foto
                                    <input type="file" name="foto" class="hidden" onchange="preview(this)">
                                </label>
                                <p class="text-[10px] text-gray-500 mt-3 uppercase tracking-widest font-black">JPG o PNG. Máx 2MB.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[10px] font-black text-gray-500 uppercase block mb-2 ml-2">Nombre Público</label>
                                <input type="text" name="nombre" class="input-setting" value="<?= htmlspecialchars($user['nombre']) ?>">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-500 uppercase block mb-2 ml-2">Email Profesional</label>
                                <input type="email" name="email" class="input-setting" value="<?= htmlspecialchars($user['email']) ?>">
                            </div>
                        </div>
                        <button type="submit" class="bg-brand text-dark-950 font-black px-10 py-4 rounded-2xl hover:scale-105 transition-all text-xs uppercase tracking-widest">Actualizar Perfil</button>
                    </form>
                </div>

                <div id="tab-seguridad" class="tab-content hidden glass-panel p-8 rounded-[2.5rem] space-y-8 animate-in fade-in duration-500">
                    <h2 class="text-2xl font-black">Seguridad</h2>
                    <form onsubmit="saveSettings(event)" class="space-y-6">
                        <input type="hidden" name="accion" value="password">
                        <div>
                            <label class="text-[10px] font-black text-gray-500 uppercase block mb-2 ml-2">Nueva Contraseña</label>
                            <input type="password" name="password" class="input-setting" placeholder="Mínimo 8 caracteres">
                        </div>
                        <button type="submit" class="bg-white/10 text-white font-black px-8 py-3 rounded-xl border border-white/10 uppercase text-xs">Guardar Nueva Clave</button>
                    </form>
                </div>

                <div id="tab-preferencias" class="tab-content hidden glass-panel p-8 rounded-[2.5rem] space-y-8 animate-in fade-in duration-500">
                    <h2 class="text-2xl font-black">Preferencias de Sistema</h2>
                    <form onsubmit="saveSettings(event)" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <input type="hidden" name="accion" value="preferencias">
                        <div>
                            <label class="text-[10px] font-black text-gray-500 uppercase block mb-2 ml-2">Divisa Base</label>
                            <select name="moneda" class="input-setting bg-dark-950">
                                <option value="EUR" <?= $user['moneda'] == 'EUR' ? 'selected' : '' ?>>Euro (€)</option>
                                <option value="USD" <?= $user['moneda'] == 'USD' ? 'selected' : '' ?>>Dólar ($)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-500 uppercase block mb-2 ml-2">Frecuencia API</label>
                            <select name="refresco" class="input-setting bg-dark-950">
                                <option value="15" <?= $user['refresco_api'] == 15 ? 'selected' : '' ?>>15 Segundos</option>
                                <option value="60" <?= $user['refresco_api'] == 60 ? 'selected' : '' ?>>1 Minuto</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between p-5 bg-dark-950/40 rounded-2xl border border-white/5 col-span-2">
                            <div>
                                <p class="font-bold text-sm">Modo Privacidad</p>
                                <p class="text-[10px] text-gray-500">Oculta tus beneficios en la tabla principal.</p>
                            </div>
                            <input type="checkbox" name="privacidad" <?= $user['modo_privacidad'] ? 'checked' : '' ?> class="w-6 h-6 accent-brand">
                        </div>
                        <button type="submit" class="bg-brand text-dark-950 font-black px-8 py-3 rounded-xl col-span-2 uppercase text-xs">Sincronizar Preferencias</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        async function saveSettings(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const res = await fetch('actualizar_ajustes.php', { method: 'POST', body: formData });
                const status = await res.text();
                if (status.trim() === 'ok') {
                    alert('¡Cambios guardados con éxito!');
                    location.reload(); // Recargamos para ver foto y nombre nuevos
                }
            } catch (err) { alert('Error en la conexión.'); }
        }

        function preview(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => document.getElementById('img-preview').src = e.target.result;
                reader.readAsDataURL(input.files[0]);
            }
        }

        function showTab(id) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
            document.getElementById('tab-' + id).classList.remove('hidden');
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active', 'text-brand'));
            document.getElementById('btn-' + id).classList.add('active', 'text-brand');
        }
    </script>
</body>
</html>