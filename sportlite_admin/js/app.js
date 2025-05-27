document.addEventListener('DOMContentLoaded', () => {
    // El objeto sportliteDB ya no se usa para almacenar los datos principales.
    // Se obtiene todo desde el backend.

    const mainContent = document.getElementById('mainContent');
    const navLinks = document.querySelectorAll('.sidebar-nav a');
    const modal = document.getElementById('editModal');
    const modalTitleElem = document.getElementById('modalTitle');
    const editForm = document.getElementById('editForm');
    const formFieldsContainer = document.getElementById('formFields');
    const closeModalButton = document.querySelector('.modal .close-button');
    const saveChangesButton = document.getElementById('saveChangesButton');

    let currentEditingItem = null;
    let currentItemType = '';
    let currentOperation = 'edit'; // 'create' or 'edit'

    // --- Helper para Fetch API ---
    async function fetchData(url, options = {}) {
        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: response.statusText }));
                throw new Error(`Error HTTP ${response.status}: ${errorData.message || 'Error desconocido'}`);
            }
            // Para DELETE o respuestas sin cuerpo JSON, response.json() fallaría.
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return await response.json();
            } else {
                return { status: 'success', message: 'Operación exitosa sin contenido JSON de respuesta.' }; // o await response.text();
            }
        } catch (error) {
            console.error('Error en fetchData:', error);
            alert(`Error al comunicar con el servidor: ${error.message}`);
            return null; // o throw error; para manejarlo más arriba
        }
    }

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const sectionId = link.getAttribute('data-section');
            showSection(sectionId);
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        });
    });

    function showSection(sectionId) {
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
            section.style.display = 'none';
        });
        const activeSection = document.getElementById(`${sectionId}-content`);
        if (activeSection) {
            loadSectionContent(sectionId, activeSection);
            activeSection.style.display = 'block';
            activeSection.classList.add('active');
        } else if (sectionId === 'dashboard') {
            const dashboardSection = document.getElementById('dashboard-content');
            dashboardSection.style.display = 'block';
            dashboardSection.classList.add('active');
            // Podrías cargar datos para el dashboard aquí si fuera necesario
        }
    }

    function generateFieldHtml(type, id, label, value = '', options = null, required = false, placeholder = '', disabled = false) {
        let fieldHtml = `<label for="${id}">${label}:</label>`;
        const reqAttr = required ? 'required' : '';
        const phAttr = placeholder ? `placeholder="${placeholder}"` : '';
        const disAttr = disabled ? 'disabled' : '';

        switch (type) {
            case 'text':
                fieldHtml += `<input type="text" id="${id}" name="${id}" value="${value}" ${reqAttr} ${phAttr} ${disAttr}>`;
                break;
            case 'number':
                fieldHtml += `<input type="number" id="${id}" name="${id}" value="${value}" ${reqAttr} ${phAttr} ${disAttr}>`;
                break;
            case 'textarea':
                fieldHtml += `<textarea id="${id}" name="${id}" rows="5" ${reqAttr} ${phAttr} ${disAttr}>${value}</textarea>`;
                break;
            case 'select':
                fieldHtml += `<select id="${id}" name="${id}" ${reqAttr} ${disAttr}>`;
                options.forEach(opt => {
                    fieldHtml += `<option value="${opt.value}" ${opt.value === value ? 'selected' : ''}>${opt.text}</option>`;
                });
                fieldHtml += `</select>`;
                break;
            case 'date':
                 fieldHtml += `<input type="date" id="${id}" name="${id}" value="${value}" ${reqAttr} ${disAttr}>`;
                 break;
        }
        return fieldHtml;
    }

    function openModalForCreate(type) {
        currentOperation = 'create';
        currentItemType = type;
        currentEditingItem = null;
        modalTitleElem.textContent = `Crear Nuevo ${type.charAt(0).toUpperCase() + type.slice(1)}`;
        populateFormFields(type, null);
        saveChangesButton.textContent = 'Crear';
        modal.style.display = 'block';
    }

    function openModalForEdit(type, item) {
        currentOperation = 'edit';
        currentItemType = type;
        currentEditingItem = item; // item es el objeto completo del backend
        modalTitleElem.textContent = `Editar ${type.charAt(0).toUpperCase() + type.slice(1)}: ${item.name || item.title || `ID ${item.id}`}`;
        populateFormFields(type, item);
        saveChangesButton.textContent = 'Guardar Cambios';
        modal.style.display = 'block';
    }

    function populateFormFields(type, item) {
        formFieldsContainer.innerHTML = '';
        let fieldsHtml = '';

        switch (type) {
            case 'tournament':
                const tournamentStatuses = [ {value: 'Planeado', text: 'Planeado'}, {value: 'Activo', text: 'Activo'}, {value: 'Finalizado', text: 'Finalizado'}, {value: 'Cancelado', text: 'Cancelado'} ];
                fieldsHtml += generateFieldHtml('text', 'name', 'Nombre del Torneo', item?.name || '', null, true);
                fieldsHtml += generateFieldHtml('select', 'status', 'Estado', item?.status || 'Planeado', tournamentStatuses, true);
                fieldsHtml += generateFieldHtml('number', 'teams', 'Nº de Equipos', item?.teams || 0);
                fieldsHtml += generateFieldHtml('text', 'currentPhase', 'Fase Actual', item?.currentPhase || 'Inscripciones');
                if (item) { // Si es edición, mostrar campos de estadísticas pero deshabilitados
                    fieldsHtml += `<h3>Estadísticas (modificables desde "Editar Stats" en la lista)</h3>`;
                    fieldsHtml += generateFieldHtml('number', 'matchesPlayed', 'Partidos Jugados', item?.matchesPlayed, null, false, 'No editable aquí', true);
                    fieldsHtml += generateFieldHtml('number', 'goalsScored', 'Goles Marcados', item?.goalsScored, null, false, 'No editable aquí', true);
                }
                break;
            case 'tournamentStats':
                 modalTitleElem.textContent = `Editar Estadísticas: ${item.name}`;
                 fieldsHtml += generateFieldHtml('number', 'matchesPlayed', 'Partidos Jugados', item?.matchesPlayed, null, true);
                 fieldsHtml += generateFieldHtml('number', 'goalsScored', 'Goles Marcados', item?.goalsScored, null, true);
                break;
            case 'news':
                fieldsHtml += generateFieldHtml('text', 'title', 'Título', item?.title || '', null, true);
                fieldsHtml += generateFieldHtml('textarea', 'content', 'Contenido', item?.content || '', null, true);
                fieldsHtml += generateFieldHtml('text', 'category', 'Categoría', item?.category || 'General');
                fieldsHtml += generateFieldHtml('text', 'author', 'Autor', item?.author || 'Admin');
                fieldsHtml += generateFieldHtml('date', 'publishedDate', item?.publishedDate || new Date().toISOString().split('T')[0]);
                break;
            // Casos para pagos, reseñas, reservas, usuarios no necesitan formularios de creación/edición complejos en este modal general.
            // Sus acciones (cambiar estado, rol) se manejan directamente en la lista.
        }
        formFieldsContainer.innerHTML = fieldsHtml;
    }

    closeModalButton.onclick = () => modal.style.display = 'none';
    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };

    editForm.onsubmit = async (event) => {
        event.preventDefault();
        const formData = new FormData(editForm);
        const data = Object.fromEntries(formData.entries());
        let endpoint = '';
        let options = {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        };

        if (currentOperation === 'create') {
            switch (currentItemType) {
                case 'tournament':
                    endpoint = './api/torneos.php';
                    // los campos matchesPlayed y goalsScored se inicializan en 0 en el backend
                    break;
                case 'news':
                    endpoint = './api/noticias.php';
                    // 'publishedDate' ya está en data si se incluyó en el form
                    break;
            }
        } else { // edit
            if (!currentEditingItem) return;
            data.id = currentEditingItem.id; // Asegurar que el ID está en los datos a enviar
            options.body = JSON.stringify(data); // Re-stringify con el ID

            switch (currentItemType) {
                case 'tournament':
                    endpoint = './api/torneos.php'; // El backend PHP distinguirá si es update o create por la presencia de ID
                    break;
                case 'tournamentStats':
                    endpoint = './api/torneos.php';
                    data.action = 'updateStats'; // Acción específica para el backend
                    options.body = JSON.stringify(data);
                    break;
                case 'news':
                    endpoint = './api/noticias.php';
                    break;
            }
        }

        if (endpoint) {
            const result = await fetchData(endpoint, options);
            if (result && (result.status === 'success' || result.id)) {
                modal.style.display = 'none';
                let sectionToRefresh = currentItemType.endsWith('Stats') ? 'torneos' : (currentItemType + 's');
                if (currentItemType === 'news') sectionToRefresh = 'noticias';
                showSection(sectionToRefresh); // Recargar la sección para ver los cambios
            } else {
                alert(`Error al guardar: ${result ? result.message : 'Error desconocido'}`);
            }
        }
    };

    function createActionButton(text, className, clickHandler) {
        const button = document.createElement('button');
        button.textContent = text;
        button.className = className; // Para CSS
        button.classList.add('action-button'); // Clase general para estilos comunes si se necesita
        button.addEventListener('click', clickHandler);
        return button;
    }

    function createSelectElement(id, options, selectedValue, changeHandler) {
        const select = document.createElement('select');
        select.id = id;
        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.text;
            if (opt.value === selectedValue) {
                option.selected = true;
            }
            select.appendChild(option);
        });
        if (changeHandler) {
             select.addEventListener('change', changeHandler); // Opcional, si se necesita acción al cambiar
        }
        return select;
    }

    async function loadSectionContent(sectionId, sectionElement) {
        sectionElement.innerHTML = ''; // Limpiar contenido anterior
        const title = document.createElement('h1');
        let sectionTitleText = sectionId.charAt(0).toUpperCase() + sectionId.slice(1);
        if (sectionId === "resenas") sectionTitleText = "Reseñas";
        title.textContent = sectionTitleText;
        sectionElement.appendChild(title);

        // Botón de "Crear Nuevo" solo para secciones que lo soportan
        if (['torneos', 'noticias'].includes(sectionId)) {
            const typeForCreate = sectionId === 'torneos' ? 'tournament' : 'news';
            const createButton = createActionButton(`Crear Nuevo ${typeForCreate === 'tournament' ? 'Torneo' : 'Noticia'}`, 'create-new-button', () => openModalForCreate(typeForCreate));
            sectionElement.appendChild(createButton);
        }

        const listContainer = document.createElement('ul');
        listContainer.className = 'item-list';
        sectionElement.appendChild(listContainer);

        let data = [];
        let apiEndpoint = '';

        switch (sectionId) {
            case 'torneos': apiEndpoint = './api/torneos.php'; break;
            case 'noticias': apiEndpoint = './api/noticias.php'; break;
            case 'pagos': apiEndpoint = './api/pagos.php'; break;
            case 'resenas': apiEndpoint = './api/resenas.php'; break;
            case 'reservas': apiEndpoint = './api/reservas.php'; break;
            case 'usuarios': apiEndpoint = './api/usuarios.php'; break;
            default:
                listContainer.innerHTML = '<p>Contenido no disponible.</p>';
                return;
        }

        if (apiEndpoint) {
            data = await fetchData(apiEndpoint);
            if (!data) { // Si fetchData devuelve null por un error
                listContainer.innerHTML = '<p>Error al cargar los datos. Revise la consola para más detalles.</p>';
                return;
            }
        }
        
        // Llama a la función de renderizado correspondiente
        switch (sectionId) {
            case 'torneos': renderTorneos(listContainer, data); break;
            case 'noticias': renderNoticias(listContainer, data); break;
            case 'pagos': renderPagos(listContainer, data); break;
            case 'resenas': renderResenas(listContainer, data); break;
            case 'reservas': renderReservas(listContainer, data); break;
            case 'usuarios': renderUsuarios(listContainer, data); break;
        }
    }

    // --- Funciones de Renderizado (Modificadas para usar datos del backend) ---

    function renderTorneos(container, torneos) {
        if (!torneos || torneos.length === 0) {
            container.innerHTML = '<p>No hay torneos para mostrar.</p>'; return;
        }
        torneos.forEach(torneo => {
            const li = document.createElement('li');
            const detailsDiv = document.createElement('div');
            detailsDiv.className = 'item-details';
            detailsDiv.innerHTML = `
                <strong>${torneo.name}</strong> (ID: ${torneo.id})<br>
                <span class="label">Estado:</span> <span class="value">${torneo.status}</span> |
                <span class="label">Equipos:</span> <span class="value">${torneo.teams}</span> |
                <span class="label">Fase:</span> <span class="value">${torneo.currentPhase}</span><br>
                <span class="label">Estadísticas:</span> <span class="value">${torneo.matchesPlayed} Partidos, ${torneo.goalsScored} Goles</span>
            `;

            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'item-actions';
            actionsDiv.appendChild(createActionButton('Editar Stats', 'edit-stats', () => openModalForEdit('tournamentStats', torneo)));
            actionsDiv.appendChild(createActionButton('Editar Torneo', 'edit', () => openModalForEdit('tournament', torneo)));
            actionsDiv.appendChild(createActionButton('Eliminar', 'delete', async () => {
                if (confirm(`¿Seguro que quieres eliminar el torneo "${torneo.name}"?`)) {
                    const result = await fetchData(`./api/torneos.php?id=${torneo.id}`, { method: 'DELETE' });
                    if (result && result.status === 'success') {
                        showSection('torneos');
                    } else {
                        alert(`Error al eliminar: ${result ? result.message : 'Error desconocido'}`);
                    }
                }
            }));

            li.appendChild(detailsDiv);
            li.appendChild(actionsDiv);
            container.appendChild(li);
        });
    }

    function renderNoticias(container, noticias) {
        if (!noticias || noticias.length === 0) {
            container.innerHTML = '<p>No hay noticias para mostrar.</p>'; return;
        }
        noticias.forEach(noticia => {
            const li = document.createElement('li');
            const detailsDiv = document.createElement('div');
            detailsDiv.className = 'item-details';
            detailsDiv.innerHTML = `
                <strong>${noticia.title}</strong> (ID: ${noticia.id})<br>
                <span class="label">Categoría:</span> <span class="value">${noticia.category}</span> |
                <span class="label">Autor:</span> <span class="value">${noticia.author}</span> |
                <span class="label">Publicado:</span> <span class="value">${noticia.publishedDate}</span><br>
                <span class="label">Contenido:</span> <span class="value">${(noticia.content || '').substring(0, 150)}...</span>
            `;

            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'item-actions';
            actionsDiv.appendChild(createActionButton('Editar', 'edit', () => openModalForEdit('news', noticia)));
            actionsDiv.appendChild(createActionButton('Eliminar', 'delete', async () => {
                if (confirm(`¿Seguro que quieres eliminar la noticia "${noticia.title}"?`)) {
                    const result = await fetchData(`./api/noticias.php?id=${noticia.id}`, { method: 'DELETE' });
                    if (result && result.status === 'success') {
                        showSection('noticias');
                    } else {
                         alert(`Error al eliminar: ${result ? result.message : 'Error desconocido'}`);
                    }
                }
            }));

            li.appendChild(detailsDiv);
            li.appendChild(actionsDiv);
            container.appendChild(li);
        });
    }

    function renderPagos(container, pagos) {
        if (!pagos || pagos.length === 0) {
            container.innerHTML = '<p>No hay pagos registrados.</p>'; return;
        }
        // container.parentElement.querySelector('h1').insertAdjacentHTML('afterend', '<h2>Historial y Gestión de Pagos</h2>'); // Esto puede duplicarse

        pagos.forEach(pago => {
            const li = document.createElement('li');
            const detailsDiv = document.createElement('div');
            detailsDiv.className = 'item-details';
            detailsDiv.innerHTML = `
                <strong>Pago ID: ${pago.id}</strong> (Usuario ID: ${pago.userId})<br>
                <span class="label">Descripción:</span> <span class="value">${pago.description}</span><br>
                <span class="label">Monto:</span> <span class="value">${pago.amount.toFixed(2)} ${pago.currency}</span> |
                <span class="label">Método:</span> <span class="value">${pago.method}</span> |
                <span class="label">Fecha:</span> <span class="value">${pago.date}</span><br>
                <span class="label">Estado:</span> <span class="payment-status-${pago.status.toLowerCase()}">${pago.status}</span> |
                <span class="label">Transacción ID:</span> <span class="value">${pago.transactionId}</span>
            `;

            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'item-actions';
            const paymentStatuses = [
                { value: 'Pendiente', text: 'Pendiente' }, { value: 'Pagado', text: 'Pagado' },
                { value: 'Fallido', text: 'Fallido' }, { value: 'Reembolsado', text: 'Reembolsado' }
            ];
            const statusSelectId = `payment-status-select-${pago.id}`;
            const statusSelect = createSelectElement(statusSelectId, paymentStatuses, pago.status);
            actionsDiv.appendChild(statusSelect);
            actionsDiv.appendChild(createActionButton('Actualizar Estado', 'manage', async () => {
                const selectedStatus = document.getElementById(statusSelectId).value;
                const payload = { id: pago.id, status: selectedStatus, action: 'updateStatus' };
                const result = await fetchData('./api/pagos.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                if (result && result.status === 'success') {
                    alert(`Estado del pago ID ${pago.id} actualizado a ${selectedStatus}.`);
                    showSection('pagos');
                } else {
                     alert(`Error al actualizar: ${result ? result.message : 'Error desconocido'}`);
                }
            }));

            li.appendChild(detailsDiv);
            li.appendChild(actionsDiv);
            container.appendChild(li);
        });
    }

    function renderResenas(container, resenas) {
        if (!resenas || resenas.length === 0) {
            container.innerHTML = '<p>No hay reseñas para mostrar.</p>'; return;
        }
        resenas.forEach(resena => {
            const li = document.createElement('li');
            const detailsDiv = document.createElement('div');
            detailsDiv.className = 'item-details';
            detailsDiv.innerHTML = `
                <strong>Reseña ID: ${resena.id}</strong> (Usuario ID: ${resena.userId})<br>
                <span class="label">Entidad:</span> <span class="value">${resena.entityType} - ${resena.entityName}</span><br>
                <span class="label">Calificación:</span> <span class="value">${'★'.repeat(resena.rating)}${'☆'.repeat(5 - resena.rating)}</span> |
                <span class="label">Fecha:</span> <span class="value">${resena.date}</span><br>
                <span class="label">Comentario:</span> <span class="value">"${resena.comment}"</span><br>
                <span class="label">Estado:</span> <span class="review-status-${resena.status.toLowerCase()}">${resena.status}</span>
            `;

            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'item-actions';
            const reviewStatuses = [
                { value: 'Aprobada', text: 'Aprobar' }, { value: 'Pendiente', text: 'Marcar Pendiente' },
                { value: 'Rechazada', text: 'Rechazar' }
            ];
            const statusSelectId = `review-status-select-${resena.id}`;
            const statusSelect = createSelectElement(statusSelectId, reviewStatuses, resena.status);
            actionsDiv.appendChild(statusSelect);

            actionsDiv.appendChild(createActionButton('Moderar Estado', 'moderate', async () => {
                 const selectedStatus = document.getElementById(statusSelectId).value;
                 const payload = { id: resena.id, status: selectedStatus, action: 'updateStatus' };
                 const result = await fetchData('./api/resenas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                 });
                 if(result && result.status === 'success') {
                    alert(`Reseña ID ${resena.id} actualizada a ${selectedStatus}.`);
                    showSection('resenas');
                 } else {
                    alert(`Error al moderar: ${result ? result.message : 'Error desconocido'}`);
                 }
            }));
            actionsDiv.appendChild(createActionButton('Eliminar', 'delete', async () => {
                if (confirm(`¿Seguro que quieres eliminar la reseña ID ${resena.id}?`)) {
                    const result = await fetchData(`./api/resenas.php?id=${resena.id}`, { method: 'DELETE' });
                    if (result && result.status === 'success') {
                        showSection('resenas');
                    } else {
                        alert(`Error al eliminar: ${result ? result.message : 'Error desconocido'}`);
                    }
                }
            }));

            li.appendChild(detailsDiv);
            li.appendChild(actionsDiv);
            container.appendChild(li);
        });
    }

    function renderReservas(container, reservas) {
        if (!reservas || reservas.length === 0) {
            container.innerHTML = '<p>No hay reservas para mostrar.</p>'; return;
        }
        reservas.forEach(reserva => {
            const li = document.createElement('li');
            const detailsDiv = document.createElement('div');
            detailsDiv.className = 'item-details';
            detailsDiv.innerHTML = `
                <strong>Reserva ID: ${reserva.id}</strong> (Usuario ID: ${reserva.userId})<br>
                <span class="label">Instalación:</span> <span class="value">${reserva.facilityName}</span><br>
                <span class="label">Fecha:</span> <span class="value">${reserva.date}</span> |
                <span class="label">Hora:</span> <span class="value">${reserva.time}</span> |
                <span class="label">Duración:</span> <span class="value">${reserva.duration}</span><br>
                <span class="label">Estado:</span> <span class="reservation-status-${reserva.status.toLowerCase().replace(/ /g, '-')}">${reserva.status}</span><br>
                <span class="label">Notas:</span> <span class="value">${reserva.notes || 'N/A'}</span>
            `;

            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'item-actions';
            const reservationStatuses = [
                { value: 'Confirmada', text: 'Confirmada' }, { value: 'Pendiente de Pago', text: 'Pendiente de Pago' },
                { value: 'Cancelada', text: 'Cancelada' }, { value: 'Completada', text: 'Completada' }
            ];
            const statusSelectId = `reservation-status-select-${reserva.id}`;
            const statusSelect = createSelectElement(statusSelectId, reservationStatuses, reserva.status);
            actionsDiv.appendChild(statusSelect);

            actionsDiv.appendChild(createActionButton('Gestionar Estado', 'manage', async () => {
                 const selectedStatus = document.getElementById(statusSelectId).value;
                 const payload = { id: reserva.id, status: selectedStatus, action: 'updateStatus' };
                 const result = await fetchData('./api/reservas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                 });
                 if(result && result.status === 'success') {
                    alert(`Reserva ID ${reserva.id} actualizada a ${selectedStatus}.`);
                    showSection('reservas');
                 } else {
                    alert(`Error al gestionar: ${result ? result.message : 'Error desconocido'}`);
                 }
            }));
            actionsDiv.appendChild(createActionButton('Eliminar', 'delete', async () => {
                if (confirm(`¿Seguro que quieres eliminar la reserva ID ${reserva.id} para "${reserva.facilityName}"?`)) {
                    const result = await fetchData(`./api/reservas.php?id=${reserva.id}`, { method: 'DELETE' });
                     if (result && result.status === 'success') {
                        showSection('reservas');
                    } else {
                        alert(`Error al eliminar: ${result ? result.message : 'Error desconocido'}`);
                    }
                }
            }));

            li.appendChild(detailsDiv);
            li.appendChild(actionsDiv);
            container.appendChild(li);
        });
    }

    function renderUsuarios(container, usuarios) {
        if (!usuarios || usuarios.length === 0) {
            container.innerHTML = '<p>No hay usuarios para mostrar.</p>'; return;
        }
        usuarios.forEach(usuario => {
            const li = document.createElement('li');
            const detailsDiv = document.createElement('div');
            detailsDiv.className = 'item-details';
            detailsDiv.innerHTML = `
                <strong>${usuario.name}</strong> (ID: ${usuario.id})<br>
                <span class="label">Email:</span> <span class="value">${usuario.email}</span> |
                <span class="label">Rol Actual:</span> <span class="value">${usuario.role}</span><br>
                <span class="label">Registrado:</span> <span class="value">${usuario.registrationDate}</span> |
                <span class="label">Último Acceso:</span> <span class="value">${usuario.lastLogin || 'N/A'}</span>
            `;

            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'item-actions';
            const userRoles = [
                { value: 'admin', text: 'Admin' }, { value: 'editor', text: 'Editor' },
                { value: 'usuario', text: 'Usuario' }, { value: 'cliente', text: 'Cliente' }, // 'cliente' era un rol posible, mantenlo si es relevante
                { value: 'bloqueado', text: 'Bloqueado' }
            ];
            const roleSelectId = `role-select-${usuario.id}`;
            const roleSelect = createSelectElement(roleSelectId, userRoles, usuario.role);
            actionsDiv.appendChild(roleSelect);

            actionsDiv.appendChild(createActionButton('Cambiar Rol', 'manage', async () => {
                const newRole = document.getElementById(roleSelectId).value;
                if (confirm(`¿Cambiar rol de ${usuario.name} de "${usuario.role}" a "${newRole}"?`)) {
                    const payload = { id: usuario.id, role: newRole, action: 'updateRole' };
                    const result = await fetchData('./api/usuarios.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    if(result && result.status === 'success') {
                       alert(`Rol de ${usuario.name} cambiado a ${newRole}.`);
                       showSection('usuarios');
                    } else {
                       alert(`Error al cambiar rol: ${result ? result.message : 'Error desconocido'}`);
                    }
                }
            }));
            // El botón de eliminar usuarios no se incluye por defecto para evitar borrados accidentales de admins,
            // pero podría añadirse de forma similar a otras secciones si es necesario.

            li.appendChild(detailsDiv);
            li.appendChild(actionsDiv);
            container.appendChild(li);
        });
    }

    // Cargar la sección inicial (Dashboard o la primera del menú)
    const initialSectionLink = document.querySelector('.sidebar-nav a'); // Carga la primera sección por defecto
    if (initialSectionLink) {
        const initialSectionId = initialSectionLink.getAttribute('data-section');
        if (initialSectionId === 'dashboard' || !initialSectionId) { // Asegurar que dashboard se maneja bien
             showSection('dashboard');
             document.getElementById('dashboard-content').style.display = 'block';
             document.getElementById('dashboard-content').classList.add('active');
             // Desactivar otros enlaces si es necesario
             navLinks.forEach(l => l.classList.remove('active'));
             // Podrías querer activar un enlace de 'Dashboard' si existe explícitamente
        } else {
            initialSectionLink.click();
        }
    } else { // Fallback si no hay enlaces, muestra el dashboard
        showSection('dashboard');
        document.getElementById('dashboard-content').style.display = 'block';
        document.getElementById('dashboard-content').classList.add('active');
    }
});