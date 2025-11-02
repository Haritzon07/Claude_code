// =====================================================
// JUEGO - PANEL JUGADOR
// =====================================================

let ultimoOrden = 0;

// Marcar celda del cartón
function marcarCelda(celda, valor) {
    if (valor === 'FREE') {
        return; // No se puede marcar/desmarcar FREE
    }

    // Toggle clase marcada
    if (celda.classList.contains('marcada')) {
        celda.classList.remove('marcada');
        // Remover de numerosMarcados
        const index = numerosMarcados.indexOf(parseInt(valor));
        if (index > -1) {
            numerosMarcados.splice(index, 1);
        }
    } else {
        // Verificar si el número ya salió
        if (numerosExtraidos.includes(parseInt(valor))) {
            celda.classList.add('marcada');
            if (!numerosMarcados.includes(parseInt(valor))) {
                numerosMarcados.push(parseInt(valor));
            }
        } else {
            // Animación de error
            celda.classList.add('error-shake');
            setTimeout(() => {
                celda.classList.remove('error-shake');
            }, 500);
        }
    }

    actualizarEstadisticas();
}

// Marcar automáticamente números que han salido
function marcarAutomatico(numero) {
    const celdas = document.querySelectorAll(`td[data-numero="${numero}"]`);

    celdas.forEach(celda => {
        if (!celda.classList.contains('marcada') && celda.getAttribute('data-numero') !== 'FREE') {
            celda.classList.add('extraido');
            // Animación de destaque
            celda.classList.add('nuevo-extraido');
            setTimeout(() => {
                celda.classList.remove('nuevo-extraido');
            }, 2000);
        }
    });
}

// Actualizar estadísticas
function actualizarEstadisticas() {
    document.getElementById('numerosSalidos').textContent = numerosExtraidos.length;
    document.getElementById('numerosMarcados').textContent = numerosMarcados.length;
}

// Cantar BINGO
function cantarBingo() {
    document.getElementById('bingoModal').style.display = 'block';
}

function cerrarModalBingo() {
    document.getElementById('bingoModal').style.display = 'none';
}

// Reclamar premio
async function reclamarPremio(tipoPremio) {
    try {
        const formData = new FormData();
        formData.append('carton_id', cartonId);
        formData.append('tipo_premio', tipoPremio);
        formData.append('numeros_marcados', JSON.stringify(numerosMarcados));

        const response = await fetch('../api/cantar-bingo.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        cerrarModalBingo();

        if (data.success) {
            if (data.valido) {
                // ¡GANADOR!
                mostrarMensajeGanador(data);

                if (data.mostrar_confetti) {
                    lanzarConfetti(200);
                }

                reproducirSonido('win');
            } else {
                alert('✅ ' + data.message);
            }
        } else {
            alert('❌ ' + data.message);
        }
    } catch (error) {
        console.error('Error al cantar BINGO:', error);
        alert('Error al enviar reclamo');
    }
}

// Mostrar mensaje de ganador
function mostrarMensajeGanador(data) {
    const overlay = document.createElement('div');
    overlay.className = 'ganador-overlay';
    overlay.innerHTML = `
        <div class="ganador-mensaje">
            <h1>🎉 ¡FELICITACIONES! 🎉</h1>
            <h2>¡HAS GANADO!</h2>
            <p class="premio-ganado">${data.premio}</p>
            <button class="btn btn-success btn-lg" onclick="cerrarMensajeGanador()">
                Continuar
            </button>
        </div>
    `;

    document.body.appendChild(overlay);

    // Reproducir sonido de victoria
    reproducirSonido('win');
}

function cerrarMensajeGanador() {
    const overlay = document.querySelector('.ganador-overlay');
    if (overlay) {
        overlay.remove();
    }
}

// Obtener actualizaciones en tiempo real
async function obtenerActualizaciones() {
    try {
        const response = await fetch(`../api/obtener-actualizaciones.php?sorteo_id=${sorteoId}&ultimo_orden=${ultimoOrden}`);
        const data = await response.json();

        if (data.success) {
            // Procesar nuevas extracciones
            if (data.extracciones && data.extracciones.length > 0) {
                data.extracciones.forEach(extraccion => {
                    mostrarNumeroExtraido(extraccion);
                    agregarBolita(extraccion);
                    actualizarTablero(extraccion.numero);
                    marcarAutomatico(extraccion.numero);

                    // Actualizar números extraídos
                    if (!numerosExtraidos.includes(extraccion.numero)) {
                        numerosExtraidos.push(extraccion.numero);
                    }

                    // Narrar número
                    if (sonidoActivo) {
                        narrarNumero(extraccion.letra, extraccion.numero);
                    }

                    // Reproducir sonido
                    reproducirSonido('ball');

                    ultimoOrden = extraccion.orden;
                });

                actualizarEstadisticas();
            }

            // Verificar si ganó
            if (data.mi_reclamo && data.mi_reclamo.estado === 'validado') {
                mostrarMensajeGanador({
                    valido: true,
                    premio: data.mi_reclamo.premio_descripcion || '¡Premio!'
                });
            }

            // Verificar si el sorteo finalizó
            if (data.estado_sorteo === 'finalizado') {
                alert('El sorteo ha finalizado');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 3000);
            }
        }
    } catch (error) {
        console.error('Error al obtener actualizaciones:', error);
    }
}

// Mostrar número extraído
function mostrarNumeroExtraido(extraccion) {
    const display = document.getElementById('ultimoNumero');
    const numeroDiv = display.querySelector('.numero-valor');
    const letraDiv = display.querySelector('.letra-valor');

    numeroDiv.textContent = extraccion.numero;
    letraDiv.textContent = extraccion.letra;

    // Aplicar color según la letra
    display.className = 'numero-grande letra-' + extraccion.letra.toLowerCase();

    // Animación
    display.classList.add('pulse');
    setTimeout(() => {
        display.classList.remove('pulse');
    }, 1000);
}

// Agregar bolita a la lista
function agregarBolita(extraccion) {
    const container = document.getElementById('bolitasScroll');

    const bolita = document.createElement('div');
    bolita.className = 'bolita letra-' + extraccion.letra.toLowerCase() + ' nuevo';
    bolita.innerHTML = `
        <span class="bolita-letra">${extraccion.letra}</span>
        <span class="bolita-numero">${extraccion.numero}</span>
    `;

    // Insertar al inicio
    container.insertBefore(bolita, container.firstChild);

    // Limitar a 10 bolitas
    while (container.children.length > 10) {
        container.removeChild(container.lastChild);
    }

    // Remover animación
    setTimeout(() => {
        bolita.classList.remove('nuevo');
    }, 500);
}

// Actualizar tablero de números
function actualizarTablero(numero) {
    const mini = document.getElementById('mini-' + numero);
    if (mini && !mini.classList.contains('salido')) {
        mini.classList.add('salido');
        mini.classList.add('nuevo-salido');

        setTimeout(() => {
            mini.classList.remove('nuevo-salido');
        }, 1000);
    }
}

// Narrar número con Web Speech API
function narrarNumero(letra, numero) {
    if ('speechSynthesis' in window && sonidoActivo) {
        const texto = `${letra} - ${numero}`;

        const utterance = new SpeechSynthesisUtterance(texto);
        utterance.lang = 'es-ES';
        utterance.rate = 0.9;
        utterance.pitch = 1;
        utterance.volume = 1;

        window.speechSynthesis.speak(utterance);
    }
}

// Toggle sonido
function toggleSound() {
    sonidoActivo = !sonidoActivo;

    const btn = document.getElementById('soundToggle');
    btn.textContent = sonidoActivo ? '🔊' : '🔇';
}

// Reproducir sonidos
function reproducirSonido(tipo) {
    if (!sonidoActivo) return;

    // Aquí podrías reproducir archivos de audio reales
    // Por ahora solo es un placeholder
    console.log('Reproducir sonido:', tipo);
}

// Inicializar
document.addEventListener('DOMContentLoaded', () => {
    // Marcar automáticamente los números FREE
    const freeCells = document.querySelectorAll('td.free-cell');
    freeCells.forEach(cell => {
        cell.classList.add('marcada');
    });

    // Marcar números que ya salieron
    numerosExtraidos.forEach(numero => {
        marcarAutomatico(numero);
    });

    ultimoOrden = numerosExtraidos.length;

    actualizarEstadisticas();

    // Iniciar polling de actualizaciones cada 2 segundos
    setInterval(obtenerActualizaciones, 2000);
});

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('bingoModal');
    if (event.target == modal) {
        cerrarModalBingo();
    }
}
