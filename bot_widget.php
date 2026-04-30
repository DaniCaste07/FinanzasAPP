<!-- Botón Flotante -->
<button id="ai-bot-toggle" class="fixed bottom-6 right-6 w-14 h-14 bg-dark-900 border border-brand/30 rounded-full shadow-[0_0_20px_rgba(0,255,163,0.2)] flex items-center justify-center text-brand hover:scale-110 transition-transform z-50 group">
    <svg class="w-6 h-6 group-hover:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
</button>

<!-- Ventana de Chat -->
<div id="ai-chat-window" class="fixed bottom-24 right-6 w-80 glass-panel border-brand/20 rounded-2xl shadow-2xl z-50 hidden flex-col overflow-hidden transition-all duration-300 transform scale-95 opacity-0">
    <div class="bg-dark-950 p-4 border-b border-white/5 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-brand animate-pulse"></div>
            <span class="text-sm font-black text-white tracking-widest uppercase">Invest<span class="text-brand">IA</span></span>
        </div>
        <button id="ai-close" class="text-gray-500 hover:text-red-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
    </div>
    
    <div id="ai-messages" class="p-4 h-64 overflow-y-auto flex flex-col gap-3 text-sm font-medium">
        <div class="bg-dark-900 text-gray-300 p-3 rounded-tr-xl rounded-b-xl border border-white/5 w-11/12 self-start">
            Hola <?= htmlspecialchars($_SESSION['nombre'] ?? ''); ?>, soy tu asistente financiero. ¿Necesitas recomendaciones de inversión o ayuda con la plataforma?
        </div>
    </div>
    
    <div class="p-3 bg-dark-950 border-t border-white/5 flex gap-2">
        <input type="text" id="ai-input" class="flex-1 bg-dark-900 text-white rounded-lg px-3 py-2 text-xs border border-white/10 outline-none focus:border-brand/50" placeholder="Pregúntame algo...">
        <button id="ai-send" class="bg-brand text-dark-950 p-2 rounded-lg font-black"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg></button>
    </div>
</div>

<script>
    const aiToggle = document.getElementById('ai-bot-toggle');
    const aiWindow = document.getElementById('ai-chat-window');
    const aiClose = document.getElementById('ai-close');
    const aiInput = document.getElementById('ai-input');
    const aiSend = document.getElementById('ai-send');
    const aiMessages = document.getElementById('ai-messages');

    function toggleChat() {
        if(aiWindow.classList.contains('hidden')) {
            aiWindow.classList.remove('hidden');
            setTimeout(() => aiWindow.classList.remove('scale-95', 'opacity-0'), 10);
        } else {
            aiWindow.classList.add('scale-95', 'opacity-0');
            setTimeout(() => aiWindow.classList.add('hidden'), 300);
        }
    }

    aiToggle.addEventListener('click', toggleChat);
    aiClose.addEventListener('click', toggleChat);

    function addMessage(text, isUser = false) {
        const div = document.createElement('div');
        div.className = isUser 
            ? "bg-brand/10 text-brand p-3 rounded-tl-xl rounded-b-xl border border-brand/20 w-10/12 self-end text-right"
            : "bg-dark-900 text-gray-300 p-3 rounded-tr-xl rounded-b-xl border border-white/5 w-11/12 self-start";
        div.innerText = text;
        aiMessages.appendChild(div);
        aiMessages.scrollTop = aiMessages.scrollHeight;
    }

    function sendMessage() {
        const text = aiInput.value.trim();
        if(!text) return;
        
        addMessage(text, true);
        aiInput.value = '';

        // Simulación de "escribiendo..."
        setTimeout(() => {
            fetch('api_ia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'mensaje=' + encodeURIComponent(text)
            })
            .then(res => res.text())
            .then(respuesta => addMessage(respuesta))
            .catch(err => addMessage("Error de conexión con el motor IA."));
        }, 500);
    }

    aiSend.addEventListener('click', sendMessage);
    aiInput.addEventListener('keypress', (e) => { if(e.key === 'Enter') sendMessage(); });
</script>