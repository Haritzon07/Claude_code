// =====================================================
// JUGADOR PANEL - GENERAL SCRIPTS
// =====================================================

document.addEventListener('DOMContentLoaded', () => {
    // Animaciones de entrada
    const cards = document.querySelectorAll('.carton-card, .sorteo-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.animation = 'fadeIn 0.5s ease forwards';
        }, index * 100);
    });

    // Mostrar mensaje de éxito si existe en sesión
    const mensajeExito = sessionStorage.getItem('mensaje_exito');
    if (mensajeExito) {
        mostrarNotificacion(mensajeExito, 'success');
        sessionStorage.removeItem('mensaje_exito');
    }
});

// Mostrar notificación
function mostrarNotificacion(mensaje, tipo = 'info') {
    const notificacion = document.createElement('div');
    notificacion.className = `alert alert-${tipo}`;
    notificacion.textContent = mensaje;
    notificacion.style.position = 'fixed';
    notificacion.style.top = '20px';
    notificacion.style.right = '20px';
    notificacion.style.zIndex = '10000';
    notificacion.style.animation = 'fadeIn 0.5s ease';

    document.body.appendChild(notificacion);

    setTimeout(() => {
        notificacion.style.transition = 'opacity 0.5s ease';
        notificacion.style.opacity = '0';
        setTimeout(() => {
            notificacion.remove();
        }, 500);
    }, 5000);
}

// Auto-ocultar alertas
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => {
            alert.remove();
        }, 500);
    });
}, 5000);
