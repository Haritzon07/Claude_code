// =====================================================
// SORTEO EN VIVO - PANEL ADMINISTRADOR
// =====================================================

// Cambiar modo de extracción
function cambiarModo(modo) {
    if (modo === 'manual') {
        document.getElementById('controlesManual').style.display = 'block';
        document.getElementById('controlesAuto').style.display = 'none';
        document.getElementById('modoManualBtn').classList.add('active');
        document.getElementById('modoAutoBtn').classList.remove('active');

        // Detener automático si está activo
        detenerAutomatico();
    } else {
        document.getElementById('controlesManual').style.display = 'none';
        document.getElementById('controlesAuto').style.display = 'block';
        document.getElementById('modoManualBtn').classList.remove('active');
        document.getElementById('modoAutoBtn').classList.add('active');
    }
}

// Extraer número aleatorio
async function extraerNumeroAleatorio() {
    try {
        const formData = new FormData();
        formData.append('sorteo_id', sorteoId);

        const response = await fetch('../api/extraer-numero.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            mostrarNumeroExtraido(data);
            actualizarTablero(data.numero);
            agregarBolitaTimeline(data);

            // Narrar número
            if (narradorActivo) {
                narrarNumero(data.letra, data.numero);
            }

            // Reproducir sonido
            reproducirSonido('ball');
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error al extraer número:', error);
        alert('Error al extraer número');
    }
}

// Extraer número manual
async function extraerNumeroManual() {
    const input = document.getElementById('numeroManual');
    const numero = parseInt(input.value);

    if (!numero || numero < 1 || numero > 75) {
        alert('Por favor ingrese un número válido entre 1 y 75');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('sorteo_id', sorteoId);
        formData.append('numero', numero);

        const response = await fetch('../api/extraer-numero.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            mostrarNumeroExtraido(data);
            actualizarTablero(data.numero);
            agregarBolitaTimeline(data);

            // Narrar número
            if (narradorActivo) {
                narrarNumero(data.letra, data.numero);
            }

            // Reproducir sonido
            reproducirSonido('ball');

            // Limpiar input
            input.value = '';
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error al extraer número:', error);
        alert('Error al extraer número');
    }
}

// Mostrar número extraído
function mostrarNumeroExtraido(data) {
    const display = document.getElementById('ultimoNumero');
    const numeroDiv = display.querySelector('.numero-display');
    const letraDiv = display.querySelector('.letra-display');

    // Animación de entrada
    display.classList.add('nuevo-numero');

    numeroDiv.textContent = data.numero;
    letraDiv.textContent = data.letra;

    // Aplicar color según la letra
    numeroDiv.className = 'numero-display letra-' + data.letra.toLowerCase();

    // Remover clase de animación después de 1 segundo
    setTimeout(() => {
        display.classList.remove('nuevo-numero');
    }, 1000);

    numerosExtraidos.push(data.numero);
}

// Actualizar tablero de números
function actualizarTablero(numero) {
    const bola = document.getElementById('bola-' + numero);
    if (bola) {
        bola.classList.add('extraido');
    }
}

// Agregar bolita a la timeline
function agregarBolitaTimeline(data) {
    const timeline = document.getElementById('bolitasTimeline');

    const bolita = document.createElement('div');
    bolita.className = 'bolita-timeline-item nuevo';
    bolita.innerHTML = `
        <div class="bolita-numero letra-${data.letra.toLowerCase()}">
            ${data.letra}-${data.numero}
        </div>
    `;

    // Insertar al inicio
    timeline.insertBefore(bolita, timeline.firstChild);

    // Limitar a 10 bolitas
    while (timeline.children.length > 10) {
        timeline.removeChild(timeline.lastChild);
    }

    // Remover clase de animación
    setTimeout(() => {
        bolita.classList.remove('nuevo');
    }, 500);
}

// Narrar número con Web Speech API
function narrarNumero(letra, numero) {
    if ('speechSynthesis' in window) {
        const texto = `${letra} - ${numero}`;

        const utterance = new SpeechSynthesisUtterance(texto);
        utterance.lang = 'es-ES';
        utterance.rate = 0.9;
        utterance.pitch = 1;
        utterance.volume = 1;

        window.speechSynthesis.speak(utterance);
    }
}

// Toggle narrador
function toggleNarrador() {
    narradorActivo = document.getElementById('narradorToggle').checked;
}

// Modo automático
let intervaloAutomaticoId = null;

function iniciarAutomatico() {
    const intervalo = parseInt(document.getElementById('intervaloAuto').value) * 1000;

    if (intervaloAutomaticoId) {
        detenerAutomatico();
    }

    modoAutomaticoActivo = true;
    document.getElementById('btnIniciarAuto').style.display = 'none';
    document.getElementById('btnDetenerAuto').style.display = 'block';

    // Primera extracción inmediata
    extraerNumeroAleatorio();

    // Extracciones periódicas
    intervaloAutomaticoId = setInterval(() => {
        extraerNumeroAleatorio();
    }, intervalo);
}

function detenerAutomatico() {
    if (intervaloAutomaticoId) {
        clearInterval(intervaloAutomaticoId);
        intervaloAutomaticoId = null;
    }

    modoAutomaticoActivo = false;
    document.getElementById('btnIniciarAuto').style.display = 'block';
    document.getElementById('btnDetenerAuto').style.display = 'none';
}

// Validar reclamo
async function validarReclamo(reclamoId) {
    if (!confirm('¿Validar este reclamo de BINGO?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('reclamo_id', reclamoId);

        const response = await fetch('../api/validar-reclamo.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            if (data.valido) {
                alert('✅ ¡Reclamo validado! El jugador ha ganado.');
                reproducirSonido('win');
            } else {
                alert('❌ Reclamo rechazado. El cartón no cumple con la figura.');
            }

            // Remover reclamo de la lista
            const reclamoElement = document.getElementById('reclamo-' + reclamoId);
            if (reclamoElement) {
                reclamoElement.remove();
            }

            actualizarContadorReclamos();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error al validar reclamo:', error);
        alert('Error al validar reclamo');
    }
}

// Actualizar contador de reclamos
function actualizarContadorReclamos() {
    const count = document.querySelectorAll('.reclamo-item').length;
    document.getElementById('reclamosCount').textContent = count;
}

// Polling para reclamos nuevos
setInterval(async () => {
    try {
        const response = await fetch(`../api/obtener-actualizaciones.php?sorteo_id=${sorteoId}&ultimo_orden=${numerosExtraidos.length}`);
        const data = await response.json();

        if (data.success && data.reclamos && data.reclamos.length > 0) {
            // Actualizar lista de reclamos
            const lista = document.getElementById('reclamosList');

            data.reclamos.forEach(reclamo => {
                // Verificar si ya existe
                if (!document.getElementById('reclamo-' + reclamo.id)) {
                    const item = document.createElement('div');
                    item.className = 'reclamo-item nuevo';
                    item.id = 'reclamo-' + reclamo.id;
                    item.innerHTML = `
                        <div class="reclamo-info">
                            <strong>${reclamo.nombre}</strong>
                            <span>Cartón #${reclamo.numero_carton}</span>
                            <span class="badge">${reclamo.tipo_premio}</span>
                        </div>
                        <div class="reclamo-actions">
                            <button class="btn btn-sm btn-primary" onclick="validarReclamo(${reclamo.id})">
                                ✅ Validar
                            </button>
                        </div>
                    `;

                    lista.insertBefore(item, lista.firstChild);

                    // Reproducir sonido de notificación
                    reproducirSonido('notification');

                    setTimeout(() => {
                        item.classList.remove('nuevo');
                    }, 500);
                }
            });

            actualizarContadorReclamos();
        }
    } catch (error) {
        console.error('Error al obtener actualizaciones:', error);
    }
}, 3000); // Cada 3 segundos

// Finalizar sorteo
function finalizarSorteo() {
    if (!confirm('¿Está seguro de finalizar este sorteo? Esta acción no se puede deshacer.')) {
        return;
    }

    detenerAutomatico();

    // Aquí podrías hacer una llamada AJAX para actualizar el estado del sorteo
    alert('Sorteo finalizado. Redirigiendo...');
    window.location.href = 'index.php';
}

// Reproducir sonidos
function reproducirSonido(tipo) {
    // Esta función puede reproducir sonidos si tienes archivos de audio
    // Por ahora solo es un placeholder
    console.log('Reproducir sonido:', tipo);
}

// Inicializar modo manual por defecto
cambiarModo('manual');
