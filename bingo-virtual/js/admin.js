// =====================================================
// ADMIN PANEL - GENERAL SCRIPTS
// =====================================================

// Confirmación de eliminación
function confirmarEliminacion(mensaje) {
    return confirm(mensaje || '¿Está seguro de que desea eliminar este elemento?');
}

// Animación de entrada para elementos
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.stat-card, .card, .sorteo-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.animation = 'fadeIn 0.5s ease forwards';
        }, index * 100);
    });
});

// Auto-ocultar alertas después de 5 segundos
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
