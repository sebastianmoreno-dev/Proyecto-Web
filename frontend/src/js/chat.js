// js/chat.js
// Modulo de chat. Usa Auth y API definidos en navbar.js.
// Implementa la interfaz del ciclo de vida descrito en
// DiagramasSecuencia/Vendedor/CicloVidaChat.puml.
// Las transiciones de estado (Activo -> Bloqueado -> Finalizado) las hace
// la BD (trigger / event); aqui solo reflejamos el estado y bloqueamos
// el input cuando corresponde.

var API = '/4CV3/moreseba/Proyecto-Web/Backend/api';

const Chat = (() => {
    let chatActivoId = null;
    let pollInterval = null;
    const POLL_MS    = 5000;

    function injectStyles() {
        if (document.getElementById('chat-styles')) return;
        const s = document.createElement('style');
        s.id = 'chat-styles';
        s.textContent = `
            .chat-layout { display:grid; grid-template-columns: 280px 1fr; gap:15px; min-height:480px; }
            .chat-lista { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:8px; overflow-y:auto; max-height:600px; }
            .chat-item { padding:12px; border-radius:6px; cursor:pointer; border-bottom:1px solid #f1f1f1; position:relative; }
            .chat-item:hover { background:#f9fafb; }
            .chat-item.active { background:#e8f5ee; border-left:3px solid #1B4332; }
            .chat-item-titulo { font-weight:600; color:#1B4332; font-size:0.9rem; margin-bottom:3px; }
            .chat-item-sub { font-size:0.8rem; color:#6b7280; }
            .chat-item-estado { display:inline-block; margin-top:6px; padding:2px 8px; font-size:0.7rem; border-radius:10px; font-weight:600; text-transform:uppercase; }
            .chat-item-estado.estado-activo     { background:#d1fae5; color:#065f46; }
            .chat-item-estado.estado-bloqueado  { background:#fef3c7; color:#92400e; }
            .chat-item-estado.estado-extendido  { background:#dbeafe; color:#1e40af; }
            .chat-item-estado.estado-finalizado { background:#fee2e2; color:#991b1b; }
            .chat-item-badge { position:absolute; top:10px; right:10px; background:#E74C3C; color:#fff; font-size:0.7rem; padding:2px 7px; border-radius:10px; font-weight:700; }

            .chat-conversacion { background:#fff; border:1px solid #e5e7eb; border-radius:8px; display:flex; flex-direction:column; max-height:600px; }
            .chat-vacio { padding:40px; text-align:center; color:#9ca3af; }
            .chat-header { padding:12px 16px; border-bottom:1px solid #e5e7eb; font-size:0.9rem; color:#374151; display:flex; justify-content:space-between; align-items:center; }
            .chat-mensajes { flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:8px; min-height:300px; }
            .chat-msg { max-width:75%; padding:8px 12px; border-radius:10px; font-size:0.9rem; }
            .chat-msg.mio  { align-self:flex-end;   background:#1B4332; color:#fff; }
            .chat-msg.otro { align-self:flex-start; background:#f3f4f6; color:#111827; }
            .chat-msg-autor { font-size:0.7rem; opacity:0.7; margin-bottom:2px; font-weight:600; }
            .chat-msg-fecha { font-size:0.65rem; opacity:0.6; margin-top:3px; text-align:right; }

            .chat-aviso { padding:10px 16px; background:#FEF3C7; color:#92400E; font-size:0.85rem; border-top:1px solid #FCD34D; }
            .chat-aviso.finalizado { background:#FEE2E2; color:#991B1B; border-top-color:#FCA5A5; }

            .chat-form { display:flex; gap:8px; padding:12px; border-top:1px solid #e5e7eb; }
            .chat-form input { flex:1; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:0.9rem; }
            .chat-form input:disabled { background:#f3f4f6; cursor:not-allowed; }
            .chat-form button { padding:10px 16px; background:#1B4332; color:#fff; border:none; border-radius:6px; cursor:pointer; }
            .chat-form button:disabled { background:#9ca3af; cursor:not-allowed; }
        `;
        document.head.appendChild(s);
    }

    function escapeHtml(s) {
        if (s == null) return '';
        return String(s).replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[m]));
    }

    // Función auxiliar para obtener el token por si 'Auth' no está disponible globalmente
    function getToken() {
        if (typeof Auth !== 'undefined' && Auth.getToken) {
            return Auth.getToken();
        }
        return localStorage.getItem('token') || sessionStorage.getItem('token');
    }

    async function cargarLista() {
        const cont = document.getElementById('chat-lista');
        if (!cont) return;
        try {
            const res = await fetch(`${API}/chats`, {
                headers: { Authorization: `Bearer ${getToken()}` }
            });
            if (!res.ok) throw new Error('Error al cargar chats');
            const data = await res.json();

            // Protección: Si data no es un array, detenemos la ejecución limpia
            if (!Array.isArray(data)) {
                cont.innerHTML = `<div class="chat-vacio">No tienes chats activos.</div>`;
                return;
            }

            const stMensajes = document.getElementById('st-mensajes');
            if (stMensajes) {
                const noLeidos = data.reduce((acc, c) => acc + (parseInt(c.no_leidos) || 0), 0);
                stMensajes.textContent = noLeidos;
            }

            if (!data.length) {
                cont.innerHTML = `<div class="chat-vacio">No tienes chats todavia.</div>`;
                return;
            }

            cont.innerHTML = data.map(c => `
                <div class="chat-item ${c.id_chat == chatActivoId ? 'active' : ''}" data-id="${c.id_chat}">
                    <div class="chat-item-titulo">${escapeHtml(c.propiedad_titulo)}</div>
                    <div class="chat-item-sub">${escapeHtml(c.interlocutor_nombre)}</div>
                    <span class="chat-item-estado estado-${escapeHtml(c.estado)}">${escapeHtml(c.estado)}</span>
                    ${parseInt(c.no_leidos) > 0 ? `<span class="chat-item-badge">${c.no_leidos}</span>` : ''}
                </div>
            `).join('');

            cont.querySelectorAll('.chat-item').forEach(el => {
                el.addEventListener('click', () => abrirChat(el.dataset.id));
            });
        } catch (e) {
            cont.innerHTML = `<div class="chat-vacio" style="color:#C0392B;">No se pudieron cargar los chats.</div>`;
        }
    }

    async function abrirChat(idChat) {
        chatActivoId = idChat;
        document.querySelectorAll('.chat-item').forEach(el => {
            el.classList.toggle('active', el.dataset.id == idChat);
        });
        await renderConversacion();
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(() => renderConversacion(true), POLL_MS);
    }

    async function renderConversacion(silencioso = false) {
        if (!chatActivoId) return;
        const cont = document.getElementById('chat-conversacion');
        if (!cont) return;
        try {
            const res = await fetch(`${API}/chats/${chatActivoId}/mensajes`, {
                headers: { Authorization: `Bearer ${getToken()}` }
            });
            if (!res.ok) throw new Error('Error al cargar mensajes');
            const data = await res.json();

            const mensajesHTML = (data.mensajes && data.mensajes.length) ? data.mensajes.map(m => `
                <div class="chat-msg ${m.id_remitente == data.id_usuario_actual ? 'mio' : 'otro'}">
                    <div class="chat-msg-autor">${escapeHtml(m.remitente_nombre)}</div>
                    <div class="chat-msg-texto">${escapeHtml(m.men_texto)}</div>
                    <div class="chat-msg-fecha">${new Date(m.men_fecha).toLocaleString()}</div>
                </div>
            `).join('') : `<div class="chat-vacio">Sin mensajes todavia. Envia el primero.</div>`;

            let aviso = '';
            if (!data.puede_enviar) {
                const claseExtra = data.id_estatus_chat === 4 ? 'finalizado' : '';
                const motivo = data.motivo_cierre ? ` (${escapeHtml(data.motivo_cierre)})` : '';
                aviso = `<div class="chat-aviso ${claseExtra}">Chat <strong>${escapeHtml(data.estado)}</strong>${motivo}. No puedes enviar mensajes.</div>`;
            }

            // Si es recarga silenciosa (polling), no reescribimos el formulario ni la cabecera, solo los mensajes
            // para evitar parpadeos y que el usuario pierda el foco de lo que está escribiendo.
            if (silencioso) {
                const msgsContainer = document.getElementById('chat-mensajes');
                if (msgsContainer) {
                    // Validamos si el usuario estaba al final del scroll antes de actualizar
                    const estabaAbajo = msgsContainer.scrollHeight - msgsContainer.scrollTop <= msgsContainer.clientHeight + 50;
                    msgsContainer.innerHTML = mensajesHTML;
                    // Solo scrolleamos hacia abajo si el usuario ya estaba abajo
                    if (estabaAbajo) msgsContainer.scrollTop = msgsContainer.scrollHeight;
                }
            } else {
                // Renderizado inicial del chat seleccionado
                cont.innerHTML = `
                    <div class="chat-header">
                        <span>Estado: <strong>${escapeHtml(data.estado)}</strong></span>
                        <span style="font-size:0.75rem; color:#9ca3af;">Inicio: ${data.fecha_inicio ? new Date(data.fecha_inicio).toLocaleDateString() : '-'}</span>
                    </div>
                    <div class="chat-mensajes" id="chat-mensajes">${mensajesHTML}</div>
                    ${aviso}
                    <form class="chat-form" id="chat-form">
                        <input type="text" id="chat-input" placeholder="Escribe un mensaje..." maxlength="255" ${data.puede_enviar ? '' : 'disabled'}>
                        <button type="submit" ${data.puede_enviar ? '' : 'disabled'}><i class="fa-solid fa-paper-plane"></i></button>
                    </form>
                `;

                const msgs = document.getElementById('chat-mensajes');
                if (msgs) msgs.scrollTop = msgs.scrollHeight;

                if (data.puede_enviar) {
                    document.getElementById('chat-form').addEventListener('submit', enviarMensaje);
                    document.getElementById('chat-input').focus();
                }
            }
        } catch (e) {
            if (!silencioso) {
                cont.innerHTML = `<div class="chat-vacio" style="color:#C0392B;">No se pudo cargar la conversacion.</div>`;
            }
        }
    }

    async function enviarMensaje(e) {
        e.preventDefault();
        const input = document.getElementById('chat-input');
        const texto = input.value.trim();
        if (!texto) return;
        
        input.disabled = true;
        
        try {
            const res = await fetch(`${API}/chats/${chatActivoId}/mensajes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Authorization:  `Bearer ${getToken()}`
                },
                body: JSON.stringify({ texto })
            });
            const data = await res.json();
            
            if (!res.ok) {
                alert(data.mensaje || 'No se pudo enviar el mensaje.');
                return;
            }
            
            input.value = '';
            await renderConversacion(false); // Forzamos recarga visual completa tras enviar para ajustar el scroll
            cargarLista();
        } catch (err) {
            alert('No se pudo conectar al servidor.');
        } finally {
            input.disabled = false;
            input.focus();
        }
    }

    async function init() {
        injectStyles();
        await cargarLista(); // Esperamos a que se dibujen los contactos primero
        
        // --- MAGIA DEL AUTOCLIC ---
        // Leemos si en la URL nos mandaron un ID (ej: chat.php?chat_id=5)
        const params = new URLSearchParams(window.location.search);
        const chatUrlId = params.get('chat_id');
        
        if (chatUrlId) {
            abrirChat(chatUrlId); // Simulamos el clic para abrirlo de inmediato
            // (Opcional) Limpiamos la URL para que quede bonita
            window.history.replaceState({}, document.title, 'chat.php');
        }
    }

    function detener() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    return { init, detener, cargarLista };
})();