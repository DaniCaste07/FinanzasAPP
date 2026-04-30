<footer class="relative z-10 mt-auto pt-20">
    <!-- Divisor decorativo superior con gradiente neón -->
    <div class="h-px w-full bg-gradient-to-r from-transparent via-brand/50 to-transparent opacity-50"></div>

    <div class="glass-panel border-t border-white/5 bg-dark-950/80 backdrop-blur-xl py-10 px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
            
            <!-- Columna Izquierda: Marca y Copyright -->
            <div class="flex flex-col items-center md:items-start gap-4">
                <div class="flex items-center gap-3 cursor-pointer group">
                    <div class="w-8 h-8 bg-brand rounded-lg flex items-center justify-center text-dark-950 font-black text-sm shadow-[0_0_15px_rgba(0,255,163,0.3)] group-hover:shadow-[0_0_25px_rgba(0,255,163,0.6)] transition-all">IF</div>
                    <span class="text-xl font-black tracking-tight text-white group-hover:text-brand transition-colors">Invest<span class="text-brand">Flow</span></span>
                </div>
                <p class="text-xs text-gray-500 font-medium text-center md:text-left max-w-xs leading-relaxed">
                    Plataforma institucional de análisis financiero, gestión de patrimonio y proyecciones basadas en IA.
                </p>
                <p class="text-[10px] text-gray-600 font-bold uppercase tracking-widest mt-2">
                    &copy; <?= date('Y'); ?> Trabajo de Fin de Grado.
                </p>
            </div>

            <!-- Columna Central: Enlaces Rápidos -->
            <div class="flex flex-col items-center gap-4">
                <h4 class="text-[10px] font-black uppercase tracking-widest text-gray-400">Navegación Rápida</h4>
                <div class="flex gap-6 text-sm font-medium">
                    <a href="dashboard.php" class="text-gray-500 hover:text-brand transition-colors">Dashboard</a>
                    <a href="inversiones.php" class="text-gray-500 hover:text-brand transition-colors">Mercado</a>
                    <a href="ayuda.php" class="text-gray-500 hover:text-brand transition-colors">Soporte</a>
                </div>
            </div>

            <!-- Columna Derecha: Estado del Sistema -->
            <div class="flex flex-col items-center md:items-end gap-3 text-right">
                <h4 class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Estado del Servidor</h4>
                
                <div class="flex items-center gap-3 bg-dark-900/50 px-4 py-2 rounded-xl border border-white/5 shadow-inner">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-brand shadow-[0_0_8px_#00ffa3]"></span>
                    </span>
                    <span class="text-xs font-bold text-gray-300 tracking-wide">Sistemas Online</span>
                </div>

                <div class="flex gap-4 text-[9px] font-bold uppercase tracking-widest text-gray-600 mt-2">
                    <span class="flex items-center gap-1"><svg class="w-3 h-3 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg> AES-256</span>
                    <span>v2.0 PRO</span>
                </div>
            </div>

        </div>
    </div>
</footer>

<!-- Aquí inyectamos el Bot de IA para que persista en todas las páginas -->
<?php include_once 'bot_widget.php'; ?>