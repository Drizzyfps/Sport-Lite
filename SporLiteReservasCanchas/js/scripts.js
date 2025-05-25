// js/scripts.js

document.addEventListener('DOMContentLoaded', () => {
    const fechaReservaInput = document.getElementById('fecha_reserva');
    const canchaSelect = document.getElementById('cancha_id');
    const horaInicioSelect = document.getElementById('hora_inicio');
    const horaFinSelect = document.getElementById('hora_fin');
    const costoTotalDisplay = document.getElementById('costo_total_display');

    // --- Inicialización de la fecha mínima ---
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const minDate = `${year}-${month}-${day}`;
    fechaReservaInput.setAttribute('min', minDate);

    // --- Variables globales para datos de PHP ---
    const canchasData = PHP_CANCHAS;
    const businessSegments = PHP_BUSINESS_SEGMENTS;
    let existingReservations = PHP_EXISTING_RESERVATIONS;
    let selectedCanchaId = PHP_SELECTED_CANCHA_ID;
    let selectedDate = PHP_SELECTED_DATE;
    let selectedHourStart = PHP_SELECTED_HOUR_START;
    let selectedHourEnd = PHP_SELECTED_HOUR_END;

    // --- Funciones de Utilidad ---

    // Formatear un número a moneda COP
    function formatCurrency(amount) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    // Calcula la diferencia en horas entre dos tiempos (HH:mm) del mismo día
    function calculateDurationInHours(startTime, endTime) {
        if (!startTime || !endTime) return 0;

        const [startH, startM] = startTime.split(':').map(Number);
        const [endH, endM] = endTime.split(':').map(Number);

        const startDate = new Date();
        startDate.setHours(startH, startM, 0, 0);

        const endDate = new Date();
        endDate.setHours(endH, endM, 0, 0);

        // Si la hora de fin es anterior a la de inicio (implica cruce de medianoche, que no manejamos aquí)
        // O si la hora de fin es la misma que la de inicio (duración 0)
        if (endDate <= startDate) {
            return 0;
        }

        const diffMilliseconds = endDate - startDate;
        return diffMilliseconds / (1000 * 60 * 60); // Convertir ms a horas
    }

    // Calcula el costo total basado en la duración y los precios de la cancha
    function calculateTotalCost(durationHours, precioHora, precioMediaHora) {
        if (durationHours <= 0) return 0;

        const horasCompletas = Math.floor(durationHours);
        const mediasHorasRestantes = (durationHours - horasCompletas) * 2; // Será 0 o 1

        let totalCost = (horasCompletas * precioHora) + (mediasHorasRestantes * precioMediaHora);
        return totalCost;
    }

    // --- Lógica para actualizar las opciones de Hora Inicio y Hora Fin ---

    function updateTimeSelectors() {
        const canchaId = canchaSelect.value;
        const fecha = fechaReservaInput.value;

        // Resetear selectores si no hay cancha o fecha seleccionada
        if (!canchaId || !fecha) {
            horaInicioSelect.innerHTML = '<option value="">Selecciona cancha y fecha</option>';
            horaInicioSelect.disabled = true;
            horaFinSelect.innerHTML = '<option value="">Selecciona hora de inicio</option>';
            horaFinSelect.disabled = true;
            costoTotalDisplay.textContent = '---';
            displayCanchaPricesIfNoHoursSelected(); // Mostrar precios de cancha si no hay horas seleccionadas
            return;
        }

        // Habilitar Hora Inicio
        horaInicioSelect.disabled = false;

        const availableStartTimes = [];
        const currentDateTime = new Date();
        const selectedDateDt = new Date(fecha + 'T00:00:00'); // Usar 'T00:00:00' para evitar problemas de zona horaria

        businessSegments.forEach(segment => {
            const segmentStart = new Date(`${fecha}T${segment.start}`);
            const segmentEnd = new Date(`${fecha}T${segment.end}`);

            let hourIter = new Date(segmentStart);
            while (hourIter < segmentEnd) {
                const slotStartTime = hourIter.toTimeString().substring(0, 5); // HH:mm format

                // No mostrar horas pasadas para la fecha actual
                if (selectedDateDt.toDateString() === currentDateTime.toDateString() && hourIter < currentDateTime) {
                    hourIter.setMinutes(hourIter.getMinutes() + 30);
                    continue;
                }

                let isSlotAvailable = true;
                // Check if this slot start time is within any existing reservation
                for (const res of existingReservations) {
                    const resStart = new Date(`${fecha}T${res.hora_inicio}`);
                    const resEnd = new Date(`${fecha}T${res.hora_fin}`);
                    if (hourIter >= resStart && hourIter < resEnd) {
                        isSlotAvailable = false;
                        break;
                    }
                }

                if (isSlotAvailable) {
                    availableStartTimes.push(slotStartTime);
                }
                hourIter.setMinutes(hourIter.getMinutes() + 30);
            }
        });

        // Populate Hora Inicio selector
        horaInicioSelect.innerHTML = '<option value="">Selecciona una hora</option>';
        availableStartTimes.sort().forEach(time => {
            const option = document.createElement('option');
            option.value = time;
            option.textContent = time;
            if (time === selectedHourStart) { // Mantener la selección si es válida
                option.selected = true;
            }
            horaInicioSelect.appendChild(option);
        });

        updateHoraFinOptions(); // Llamar para actualizar Hora Fin después de Hora Inicio
        updateCost(); // Llamar para actualizar el costo inicial o mostrar precios de cancha
    }

    function updateHoraFinOptions() {
        const canchaId = canchaSelect.value;
        const fecha = fechaReservaInput.value;
        const horaInicio = horaInicioSelect.value;

        if (!canchaId || !fecha || !horaInicio) {
            horaFinSelect.innerHTML = '<option value="">Selecciona hora de inicio</option>';
            horaFinSelect.disabled = true;
            costoTotalDisplay.textContent = '---';
            displayCanchaPricesIfNoHoursSelected(); // Mostrar precios de cancha si no hay horas seleccionadas
            return;
        }

        horaFinSelect.disabled = false;
        const selectedStartDt = new Date(`${fecha}T${horaInicio}`);
        const availableEndTimes = [];

        // Determinar el final máximo del horario de negocio para la fecha seleccionada
        let maxBusinessEndDt = null;
        businessSegments.forEach(segment => {
            const segmentStartDt = new Date(`${fecha}T${segment.start}`);
            const segmentEndDt = new Date(`${fecha}T${segment.end}`);
            // Asegurar que la hora de inicio esté dentro de un segmento de negocio
            if (selectedStartDt >= segmentStartDt && selectedStartDt < segmentEndDt) {
                if (!maxBusinessEndDt || segmentEndDt > maxBusinessEndDt) {
                    maxBusinessEndDt = segmentEndDt;
                }
            }
        });

        if (!maxBusinessEndDt) {
            horaFinSelect.innerHTML = '<option value="">Hora de inicio fuera de horario</option>';
            horaFinSelect.disabled = true;
            costoTotalDisplay.textContent = '---';
            displayCanchaPricesIfNoHoursSelected(); // Mostrar precios de cancha si no hay horas seleccionadas
            return;
        }

        let hourIter = new Date(selectedStartDt);
        hourIter.setMinutes(hourIter.getMinutes() + 30); // Start 30 mins after selected start

        while (hourIter <= maxBusinessEndDt) {
            const slotEndTime = hourIter.toTimeString().substring(0, 5);
            
            let isSlotAvailable = true;
            // Check if the proposed reservation range [selectedStartDt, hourIter] overlaps with any existing reservation
            for (const res of existingReservations) {
                const resStart = new Date(`${fecha}T${res.hora_inicio}`);
                const resEnd = new Date(`${fecha}T${res.hora_fin}`);
                
                // Overlap condition: (start_proposed < end_existing) AND (end_proposed > start_existing)
                if ((selectedStartDt < resEnd) && (hourIter > resStart)) {
                    isSlotAvailable = false;
                    break;
                }
            }
            
            // Also ensure the *entire* proposed reservation falls within a single business segment
            let isWithinOneSegment = false;
            for (const segment of businessSegments) {
                const segmentStartDt = new Date(`${fecha}T${segment.start}`);
                const segmentEndDt = new Date(`${fecha}T${segment.end}`);
                if (selectedStartDt >= segmentStartDt && hourIter <= segmentEndDt) {
                    isWithinOneSegment = true;
                    break;
                }
            }

            if (isSlotAvailable && isWithinOneSegment) {
                availableEndTimes.push(slotEndTime);
            }
            hourIter.setMinutes(hourIter.getMinutes() + 30);
        }

        // Populate Hora Fin selector
        horaFinSelect.innerHTML = '<option value="">Selecciona una hora</option>';
        availableEndTimes.forEach(time => {
            const option = document.createElement('option');
            option.value = time;
            option.textContent = time;
            if (time === selectedHourEnd) { // Mantener la selección si es válida
                option.selected = true;
            }
            horaFinSelect.appendChild(option);
        });

        updateCost(); // Recalcular costo después de actualizar Hora Fin
    }

    // --- Lógica para calcular y mostrar el costo o precios de cancha ---
    function updateCost() {
        const canchaId = canchaSelect.value;
        const horaInicio = horaInicioSelect.value;
        const horaFin = horaFinSelect.value;

        // Si no hay cancha, hora de inicio o hora de fin seleccionadas, mostrar precios de cancha
        if (!canchaId || !horaInicio || !horaFin) {
            displayCanchaPricesIfNoHoursSelected();
            return;
        }

        const selectedCancha = canchasData.find(c => c.id == canchaId); // Usar == porque canchaId es string
        if (!selectedCancha) {
            costoTotalDisplay.textContent = 'Cancha no encontrada';
            return;
        }

        const precioHora = parseFloat(selectedCancha.precio_hora);
        const precioMediaHora = parseFloat(selectedCancha.precio_media_hora);

        const durationHours = calculateDurationInHours(horaInicio, horaFin);

        if (durationHours === 0) {
            costoTotalDisplay.textContent = 'Duración inválida';
            return;
        }
        
        // Asegurar que la duración sea en múltiplos de 0.5 (30 minutos)
        if (durationHours % 0.5 !== 0) {
             costoTotalDisplay.textContent = 'Duración inválida'; // No es múltiplo de 30 mins
             return;
        }

        // Validar que la hora fin sea estrictamente después de la hora inicio
        const [startH, startM] = horaInicio.split(':').map(Number);
        const [endH, endM] = horaFin.split(':').map(Number);
        if (endH * 60 + endM <= startH * 60 + startM) {
             costoTotalDisplay.textContent = 'Hora fin debe ser mayor que hora inicio';
             return;
        }


        const totalCost = calculateTotalCost(durationHours, precioHora, precioMediaHora);
        costoTotalDisplay.textContent = formatCurrency(totalCost);
        costoTotalDisplay.style.color = '#4CAF50'; // Color verde para el total a pagar
        costoTotalDisplay.style.fontSize = '1.8em'; // Tamaño original
    }

    // --- Función para mostrar los precios de la cancha seleccionada si no hay horas elegidas ---
    function displayCanchaPricesIfNoHoursSelected() {
        const canchaId = canchaSelect.value;
        const selectedCancha = canchasData.find(c => c.id == canchaId);

        if (selectedCancha && !horaInicioSelect.value && !horaFinSelect.value) {
            const precioHora = formatCurrency(parseFloat(selectedCancha.precio_hora));
            const precioMediaHora = formatCurrency(parseFloat(selectedCancha.precio_media_hora));
            // Ajustar el tamaño de fuente para que quepa bien
            costoTotalDisplay.style.fontSize = '1.1em';
            costoTotalDisplay.style.color = '#e0f2f7'; // Un color neutro para los precios
            costoTotalDisplay.textContent = `Precio/Hora: ${precioHora} | 30 min: ${precioMediaHora}`;
        } else if (!selectedCancha) {
            costoTotalDisplay.textContent = '---';
            costoTotalDisplay.style.fontSize = '1.8em'; 
            costoTotalDisplay.style.color = '#4CAF50'; 
        }
    }


    canchaSelect.addEventListener('change', () => {
        selectedCanchaId = canchaSelect.value; 
        if (selectedCanchaId && !fechaReservaInput.value) {
            fechaReservaInput.value = minDate; 
        }
        selectedHourStart = ''; 
        selectedHourEnd = '';  
        updateTimeSelectors(); 
    });

    fechaReservaInput.addEventListener('change', () => {
        selectedDate = fechaReservaInput.value; 
        selectedHourStart = ''; 
        selectedHourEnd = '';   
        updateTimeSelectors();
    });

    horaInicioSelect.addEventListener('change', () => {
        selectedHourStart = horaInicioSelect.value;
        selectedHourEnd = ''; 
        updateHoraFinOptions();
    });

    horaFinSelect.addEventListener('change', () => {
        selectedHourEnd = horaFinSelect.value; 
        updateCost();
    });

    // --- Inicialización al cargar la página ---
    if (selectedCanchaId && selectedDate) {
        updateTimeSelectors(); 
    } else {

        costoTotalDisplay.textContent = '---';
    }

    if (selectedCanchaId && !selectedHourStart && !selectedHourEnd) {
        displayCanchaPricesIfNoHoursSelected();
    }
});