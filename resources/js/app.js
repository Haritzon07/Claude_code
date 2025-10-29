// Bootstrap
import 'bootstrap';
import * as bootstrap from 'bootstrap';

// FullCalendar
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import esLocale from '@fullcalendar/core/locales/es';

// Chart.js
import Chart from 'chart.js/auto';

// SweetAlert2
import Swal from 'sweetalert2';

// Dragula (Drag and Drop)
import dragula from 'dragula';
import 'dragula/dist/dragula.css';

// Moment.js
import moment from 'moment';
import 'moment/locale/es';
moment.locale('es');

// Configuración global
window.bootstrap = bootstrap;
window.Calendar = Calendar;
window.dayGridPlugin = dayGridPlugin;
window.timeGridPlugin = timeGridPlugin;
window.interactionPlugin = interactionPlugin;
window.listPlugin = listPlugin;
window.esLocale = esLocale;
window.Chart = Chart;
window.Swal = Swal;
window.dragula = dragula;
window.moment = moment;

// CSRF Token para peticiones AJAX
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Funciones globales del sistema

/**
 * Muestra un mensaje de confirmación antes de eliminar
 */
window.confirmDelete = function(formId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2d5f3f',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}

/**
 * Muestra un toast de notificación
 */
window.showToast = function(message, type = 'success') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });

    Toast.fire({
        icon: type,
        title: message
    });
}

/**
 * Verifica conflictos en el horario
 */
window.checkScheduleConflicts = function(blockData) {
    return fetch('/api/schedules/check-conflicts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(blockData)
    })
    .then(response => response.json());
}

/**
 * Formatea una fecha
 */
window.formatDate = function(date, format = 'DD/MM/YYYY') {
    return moment(date).format(format);
}

/**
 * Formatea una hora
 */
window.formatTime = function(time) {
    return moment(time, 'HH:mm:ss').format('HH:mm');
}

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Auto-cerrar alertas después de 5 segundos
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Inicializar tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializar popovers de Bootstrap
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

console.log('Sistema de Cronograma Institucional - I.E. Campo Valdés - Inicializado');
