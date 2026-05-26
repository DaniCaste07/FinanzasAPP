<?php
session_start();
require_once 'conexion.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }

$uid = $_SESSION['usuario_id'];
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

// Prioridad al avatar en la sesión y fallback al de la base de datos o UI-Avatars
$avatar_actual = $_SESSION['avatar'] ?? $user['avatar'];
$foto_perfil = (!empty($avatar_actual) && $avatar_actual != 'default.png') 
               ? 'uploads/' . $avatar_actual 
               : 'https://ui-avatars.com/api/?name='.urlencode($user['nombre']).'&background=00ffa3&color=030712&bold=true';
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvestFlow - Panel de Configuración</title>
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
        .input-setting { background: rgba(3, 7, 18, 0.6); border: 1px solid rgba(255,255,255,0.1); padding: 14px 18px; border-radius: 14px; width: 100%; outline: none; transition: 0.3s; color: white; font-size: 0.9rem; }
        .input-setting:focus { border-color: #00ffa3; box-shadow: 0 0 15px rgba(0, 255, 163, 0.1); background: rgba(0, 255, 163, 0.01); }
        .tab-btn.active { background: rgba(0,255,163,0.08); color: #00ffa3; border-color: rgba(0,255,163,0.2); shadow: 0 0 20px rgba(0,255,163,0.05); }
        
        /* Interruptor de palanca neón (Toggle Switch) */
        .switch { position: relative; display: inline-block; width: 48px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider-switch { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 34px; border: 1px solid rgba(255,255,255,0.05); }
        .slider-switch:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: #94a3b8; transition: .4s; border-radius: 50%; }
        input:checked + .slider-switch { background-color: rgba(0, 255, 163, 0.1); border-color: rgba(0, 255, 163, 0.3); }
        input:checked + .slider-switch:before { transform: translateX(22px); background-color: #00ffa3; box-shadow: 0 0 10px #00ffa3; }
    </style>
</head>
<body class="text-gray-100 flex relative">

    <div class="fixed top-[-20%] right-[-10%] w-[800px] h-[800px] bg-brand/5 rounded-full blur-[150px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-10%] left-[20%] w-[600px] h-[600px] bg-blue-500/5 rounded-full blur-[130px] pointer-events-none z-0"></div>

    <div id="toast-container" class="fixed top-6 right-6 z-50 space-y-4 pointer-events-none"></div>

    <?php require_once 'sidebar.php'; ?> 

    <main class="main-content flex-1 z-10">
        <header class="mb-12">
            <p class="text-brand text-xs font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                <span class="w-2 h-2 bg-brand rounded-full shadow-[0_0_8px_#00ffa3]"></span>
                Seguridad & Preferencias del Terminal
            </p>
            <h1 class="text-5xl font-black text-white tracking-tight">Configuración</h1>
            <p class="text-gray-500 mt-2 text-sm max-w-xl">Administra los parámetros de tu perfil operativo, credenciales de acceso y frecuencias de conexión de la API.</p>
        </header>

        <div class="grid grid-cols-12 gap-8">
            <div class="col-span-12 lg:col-span-3 space-y-2">
                <button onclick="showTab('perfil')" id="btn-perfil" class="tab-btn active w-full flex items-center gap-3.5 p-4 rounded-2xl border border-white/5 hover:bg-white/5 transition-all font-bold text-xs uppercase tracking-wider text-left">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Mi Perfil
                </button>
                <button onclick="showTab('seguridad')" id="btn-seguridad" class="tab-btn w-full flex items-center gap-3.5 p-4 rounded-2xl border border-white/5 hover:bg-white/5 transition-all font-bold text-xs uppercase tracking-wider text-left text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Seguridad
                </button>
                <button onclick="showTab('preferencias')" id="btn-preferencias" class="tab-btn w-full flex items-center gap-3.5 p-4 rounded-2xl border border-white/5 hover:bg-white/5 transition-all font-bold text-xs uppercase tracking-wider text-left text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    Preferencias
                </button>
            </div>

            <div class="col-span-12 lg:col-span-9 space-y-6">
                
                <div id="tab-perfil" class="tab-content glass-panel p-8 rounded-[2.5rem] space-y-8">
                    <div>
                        <h2 class="text-xl font-black text-white">Información de Cuenta</h2>
                        <p class="text-xs text-gray-500 mt-1">Modifica tu identidad visual dentro de InvestFlow.</p>
                    </div>

                    <form onsubmit="saveSettings(event)" enctype="multipart/form-data" class="space-y-8">
                        <input type="hidden" name="accion" value="perfil">
                        
                        <div class="flex items-center gap-6 p-6 bg-dark-900/40 rounded-3xl border border-white/5">
                            <img src="<?= $foto_perfil ?>" class="w-20 h-20 rounded-2xl object-cover border border-brand/20 shadow-xl transition-transform duration-300 hover:scale-105" id="img-preview">
                            <div>
                                <label class="bg-white/5 hover:bg-white/10 px-4 py-2.5 rounded-xl text-xs font-bold cursor-pointer transition-all border border-white/10 text-gray-200">
                                    Subir Nueva Foto
                                    <input type="file" name="foto" class="hidden" onchange="preview(this)">
                                </label>
                                <p class="text-[10px] text-gray-500 mt-3 uppercase tracking-widest font-black">Formato JPG o PNG. Máximo 2MB.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block ml-1">Nombre Público</label>
                                <input type="text" name="nombre" class="input-setting" value="<?= htmlspecialchars($user['nombre']) ?>" required>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block ml-1">Email Profesional</label>
                                <input type="email" name="email" class="input-setting" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="bg-brand text-dark-950 font-black px-8 py-3.5 rounded-2xl hover:scale-[1.03] active:scale-[0.98] transition-all text-xs uppercase tracking-widest shadow-[0_0_25px_rgba(0,255,163,0.15)]">Actualizar Perfil</button>
                    </form>
                </div>

                <div id="tab-seguridad" class="tab-content hidden glass-panel p-8 rounded-[2.5rem] space-y-8">
                    <div>
                        <h2 class="text-xl font-black text-white">Credenciales de Acceso</h2>
                        <p class="text-xs text-gray-500 mt-1">Renueva tu clave criptográfica periódicamente para salvaguardar el acceso.</p>
                    </div>

                    <form onsubmit="saveSettings(event)" class="space-y-6">
                        <input type="hidden" name="accion" value="password">
                        <div class="space-y-2 max-w-md">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block ml-1">Nueva Contraseña de Cifrado</label>
                            <input type="password" name="password" class="input-setting" placeholder="Mínimo 8 caracteres mixtos" minlength="8" required>
                        </div>
                        <button type="submit" class="bg-white/10 hover:bg-white/15 text-white font-black px-7 py-3.5 rounded-xl border border-white/10 uppercase text-xs tracking-wider transition-all">Guardar Nueva Clave</button>
                    </form>
                </div>

                <div id="tab-preferencias" class="tab-content hidden glass-panel p-8 rounded-[2.5rem] space-y-8">
                    <div>
                        <h2 class="text-xl font-black text-white">Métricas del Sistema</h2>
                        <p class="text-xs text-gray-500 mt-1">Ajusta los entornos operativos globales del core.</p>
                    </div>

                    <form onsubmit="saveSettings(event)" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <input type="hidden" name="accion" value="preferencias">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block ml-1">Divisa Base Financiera</label>
                            <select name="moneda" class="input-setting bg-dark-950">
                                <option value="EUR" <?= $user['moneda'] == 'EUR' ? 'selected' : '' ?>>Euro (€)</option>
                                <option value="USD" <?= $user['moneda'] == 'USD' ? 'selected' : '' ?>>Dólar Estadounidense ($)</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block ml-1">Frecuencia de Refresco API</label>
                            <select name="refresco" class="input-setting bg-dark-950">
                                <option value="15" <?= $user['refresco_api'] == 15 ? 'selected' : '' ?>>Alta - 15 Segundos</option>
                                <option value="60" <?= $user['refresco_api'] == 60 ? 'selected' : '' ?>>Estándar - 1 Minuto</option>
                            </select>
                        </div>
                        
                        <div class="flex items-center justify-between p-5 bg-dark-900/40 rounded-2xl border border-white/5 col-span-2 shadow-inner">
                            <div>
                                <p class="font-bold text-sm text-gray-200">Modo Privacidad Activo</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Aplica un desenfoque matemático sobre tus ganancias en la vista principal.</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="privacidad" <?= $user['modo_privacidad'] ? 'checked' : '' ?>>
                                <span class="slider-switch"></span>
                            </label>
                        </div>
                        
                        <button type="submit" class="bg-brand text-dark-950 font-black px-7 py-3.5 rounded-xl col-span-2 uppercase text-xs tracking-widest hover:scale-[1.01] transition-all">Sincronizar Preferencias</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Sistema de Notificaciones Toast de Código Limpio (Adiós al alert nativo)
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            toast.className = `flex items-center gap-3 px-5 py-4 rounded-xl border font-bold text-xs uppercase tracking-wider shadow-2xl backdrop-blur-md transition-all duration-300 transform translate-y-2 opacity-0 pointer-events-auto`;
            
            if (type === 'success') {
                toast.className += ' bg-brand/10 text-brand border-brand/20 shadow-[0_0_20px_rgba(0,255,163,0.15)]';
            } else {
                toast.className += ' bg-red-500/10 text-red-400 border-red-500/20 shadow-[0_0_20px_rgba(239,68,68,0.15)]';
            }
            
            toast.innerHTML = `
                <span class="w-1.5 h-1.5 rounded-full ${type === 'success' ? 'bg-brand' : 'bg-red-400'}"></span>
                <span>${message}</span>
            `;
            
            container.appendChild(toast);
            
            // Animación de entrada
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 50);
            
            // Remoción programada
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-[-4px]');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        async function saveSettings(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const res = await fetch('actualizar_ajustes.php', { method: 'POST', body: formData });
                const status = await res.text();
                
                if (status.trim() === 'ok') {
                    showToast('¡Cambios sincronizados en el terminal!');
                    setTimeout(() => {
                        location.reload(); // Recarga diferida para refrescar la foto en el sidebar
                    }, 1000);
                } else {
                    showToast('Error al actualizar las directivas del sistema.', 'error');
                }
            } catch (err) { 
                showToast('Fallo en el enlace con el backend de base de datos.', 'error'); 
            }
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