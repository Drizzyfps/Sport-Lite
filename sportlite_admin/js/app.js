document.addEventListener('DOMContentLoaded', () => {
    let sportliteDB = {
        tournaments: [
            { id: 1, name: 'Copa Primavera 2025', status: 'Activo', teams: 16, matchesPlayed: 30, goalsScored: 75, currentPhase: 'Fase de Grupos' },
            { id: 2, name: 'Torneo Relámpago Finde', status: 'Planeado', teams: 8, matchesPlayed: 0, goalsScored: 0, currentPhase: 'Inscripciones Abiertas' },
            { id: 3, name: 'Liga Master Verano', status: 'Finalizado', teams: 12, matchesPlayed: 66, goalsScored: 150, currentPhase: 'Campeón Definido' }
        ],
        news: [
            { id: 1, title: 'Nuevo Patrocinador para Sportlite', content: 'Sportlite se complace en anunciar una nueva alianza estratégica con TechSolutions Inc. para mejorar la infraestructura tecnológica de nuestros torneos y plataforma online.', category: 'General', publishedDate: '2025-05-10', author: 'Admin' },
            { id: 2, title: 'Actualización de Reglas para Torneos de Fútbol 7', content: 'Se han actualizado las reglas para los torneos de fútbol 7, incluyendo nuevas directrices sobre sustituciones y tiempos de juego. Consultar el reglamento completo en la sección de documentos.', category: 'Torneos', publishedDate: '2025-05-12', author: 'Comité Deportivo' },
        ],
        payments: [
            { id: 1, userId: 101, description: 'Inscripción Copa Primavera', amount: 50.00, currency: 'USD', method: 'Tarjeta Crédito', status: 'Pagado', date: '2025-04-20', transactionId: 'TXN1001' },
            { id: 2, userId: 102, description: 'Reserva Cancha Central', amount: 25.00, currency: 'USD', method: 'PayPal', status: 'Pendiente', date: '2025-05-10', transactionId: 'TXN1002' },
        ],
        reviews: [
            { id: 1, userId: 201, entityType: 'tournament', entityName: 'Copa Primavera 2025', rating: 5, comment: '¡Excelente organización del torneo! Todo muy fluido y bien gestionado. Los campos estaban en perfectas condiciones.', status: 'Aprobada', date: '2025-05-01' },
            { id: 2, userId: 202, entityType: 'platform', entityName: 'Sportlite App', rating: 3, comment: 'La app móvil es un poco lenta para cargar los resultados en tiempo real, pero en general es funcional y útil.', status: 'Pendiente', date: '2025-05-05' },
        ],
        reservations: [
            { id: 1, userId: 301, facilityName: 'Cancha de Fútbol 7 - Sede Norte', date: '2025-05-20', time: '18:00', duration: '1 hora', status: 'Confirmada', notes: 'Equipo Azul vs Equipo Rojo' },
            { id: 2, userId: 302, facilityName: 'Pista de Tenis 1', date: '2025-05-22', time: '10:00', duration: '2 horas', status: 'Pendiente de Pago', notes: 'Clase particular con instructor.' },
        ],
        users: [
            { id: 1, name: 'Carlos Administrador', email: 'admin@sportlite.com', role: 'admin', registrationDate: '2024-01-15', lastLogin: '2025-05-15' },
            { id: 2, name: 'Laura Editora', email: 'editor@sportlite.com', role: 'editor', registrationDate: '2024-03-22', lastLogin: '2025-05-14' },
            { id: 3, name: 'Pedro Usuario', email: 'user1@example.com', role: 'usuario', registrationDate: '2024-06-10', lastLogin: '2025-05-10' },
        ],
        nextIds: { 
            tournaments: 4, news: 3, payments: 3, reviews: 3, reservations: 3, users: 4
        }
    };

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
    let currentOperation = 'edit'; 

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
        }
    }
    
    function generateFieldHtml(type, id, label, value = '', options = null, required = false, placeholder = '') {
        let fieldHtml = `<label for="${id}">${label}:</label>`;
        const reqAttr = required ? 'required' : '';
        const phAttr = placeholder ? `placeholder="${placeholder}"` : '';

        switch (type) {
            case 'text':
                fieldHtml += `<input type="text" id="${id}" name="${id}" value="${value}" ${reqAttr} ${phAttr}>`;
                break;
            case 'number':
                fieldHtml += `<input type="number" id="${id}" name="${id}" value="${value}" ${reqAttr} ${phAttr}>`;
                break;
            case 'textarea':
                fieldHtml += `<textarea id="${id}" name="${id}" rows="5" ${reqAttr} ${phAttr}>${value}</textarea>`;
                break;
            case 'select':
                fieldHtml += `<select id="${id}" name="${id}" ${reqAttr}>`;
                options.forEach(opt => {
                    fieldHtml += `<option value="${opt.value}" ${opt.value === value ? 'selected' : ''}>${opt.text}</option>`;
                });
                fieldHtml += `</select>`;
                break;
            case 'date':
                 fieldHtml += `<input type="date" id="${id}" name="${id}" value="${value}" ${reqAttr}>`;
                 break;
        }
        return fieldHtml;
    }


    // --- Modal Logic ---
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
        currentEditingItem = item;
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
                fieldsHtml += generateFieldHtml('text', 'name', 'Nombre del Torneo', item?.name, null, true);
                fieldsHtml += generateFieldHtml('select', 'status', 'Estado', item?.status || 'Planeado', tournamentStatuses, true);
                fieldsHtml += generateFieldHtml('number', 'teams', 'Nº de Equipos', item?.teams || 0);
                fieldsHtml += generateFieldHtml('text', 'currentPhase', 'Fase Actual', item?.currentPhase || 'Inscripciones');
                if (item) { 
                    fieldsHtml += `<h3>Estadísticas (solo modificable en "Editar Stats" desde la lista)</h3>`;
                    fieldsHtml += generateFieldHtml('number', 'matchesPlayed', 'Partidos Jugados', item?.matchesPlayed, null, false, 'No editable aquí');
                    fieldsHtml += generateFieldHtml('number', 'goalsScored', 'Goles Marcados', item?.goalsScored, null, false, 'No editable aquí');
                    formFieldsContainer.querySelector('#matchesPlayed')?.setAttribute('disabled', true); 
                    formFieldsContainer.querySelector('#goalsScored')?.setAttribute('disabled', true);
                }
                break;
            case 'tournamentStats': 
                 modalTitleElem.textContent = `Editar Estadísticas: ${item.name}`;
                 fieldsHtml += generateFieldHtml('number', 'matchesPlayed', 'Partidos Jugados', item?.matchesPlayed, null, true);
                 fieldsHtml += generateFieldHtml('number', 'goalsScored', 'Goles Marcados', item?.goalsScored, null, true);
                break;
            case 'news':
                fieldsHtml += generateFieldHtml('text', 'title', 'Título', item?.title, null, true);
                fieldsHtml += generateFieldHtml('textarea', 'content', 'Contenido', item?.content, null, true);
                fieldsHtml += generateFieldHtml('text', 'category', 'Categoría', item?.category || 'General');
                fieldsHtml += generateFieldHtml('text', 'author', 'Autor', item?.author || 'Admin');
                if (!item) { 
                    fieldsHtml += generateFieldHtml('date', 'publishedDate', 'Fecha de Publicación', new Date().toISOString().split('T')[0]);
                } else {
                    fieldsHtml += generateFieldHtml('date', 'publishedDate', 'Fecha de Publicación', item?.publishedDate);
                }
                break;
        }
        formFieldsContainer.innerHTML = fieldsHtml;
        if (type === 'tournament' && item) {
            const matchesField = formFieldsContainer.querySelector('#matchesPlayed');
            const goalsField = formFieldsContainer.querySelector('#goalsScored');
            if(matchesField) matchesField.disabled = true;
            if(goalsField) goalsField.disabled = true;
        }
    }


    closeModalButton.onclick = () => modal.style.display = 'none';
    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };

    editForm.onsubmit = (event) => {
        event.preventDefault();
        const formData = new FormData(editForm);
        const data = Object.fromEntries(formData.entries());

        if (currentOperation === 'create') {
            switch (currentItemType) {
                case 'tournament':
                    const newTournament = {
                        id: sportliteDB.nextIds.tournaments++,
                        name: data.name,
                        status: data.status,
                        teams: parseInt(data.teams) || 0,
                        matchesPlayed: 0, 
                        goalsScored: 0,   
                        currentPhase: data.currentPhase
                    };
                    sportliteDB.tournaments.push(newTournament);
                    break;
                case 'news':
                    const newNewsItem = {
                        id: sportliteDB.nextIds.news++,
                        title: data.title,
                        content: data.content,
                        category: data.category,
                        publishedDate: data.publishedDate || new Date().toISOString().split('T')[0],
                        author: data.author
                    };
                    sportliteDB.news.push(newNewsItem);
                    break;
            }
        } else {
            if (!currentEditingItem) return;
            switch (currentItemType) {
                case 'tournament':
                    currentEditingItem.name = data.name;
                    currentEditingItem.status = data.status;
                    currentEditingItem.teams = parseInt(data.teams) || currentEditingItem.teams;
                    currentEditingItem.currentPhase = data.currentPhase;
                    break;
                case 'tournamentStats':
                    currentEditingItem.matchesPlayed = parseInt(data.matchesPlayed) || 0;
                    currentEditingItem.goalsScored = parseInt(data.goalsScored) || 0;
                    break;
                case 'news':
                    currentEditingItem.title = data.title;
                    currentEditingItem.content = data.content;
                    currentEditingItem.category = data.category;
                    currentEditingItem.author = data.author;
                    currentEditingItem.publishedDate = data.publishedDate;
                    break;
            }
        }
        modal.style.display = 'none';
        let sectionToRefresh = currentItemType.endsWith('Stats') ? currentItemType.slice(0, -5) + 's' : currentItemType + 's';
        if (currentItemType === 'news') sectionToRefresh = 'noticias'; 
        if (currentItemType === 'tournament') sectionToRefresh = 'torneos';
        
        showSection(sectionToRefresh); 
    };
    
    function createActionButton(text, className, clickHandler) {
        const button = document.createElement('button');
        button.textContent = text;
        button.className = className;
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
        }
        return select;
    }


    function loadSectionContent(sectionId, sectionElement) {
        sectionElement.innerHTML = ''; 
        const title = document.createElement('h1');
        let sectionTitleText = sectionId.charAt(0).toUpperCase() + sectionId.slice(1);
        if (sectionId === "resenas") sectionTitleText = "Reseñas";
        
        title.textContent = sectionTitleText;
        sectionElement.appendChild(title);

        if (['torneos', 'noticias'].includes(sectionId)) {
            const typeForCreate = sectionId === 'torneos' ? 'tournament' : 'news';
            const createButton = createActionButton(`Crear Nuevo ${typeForCreate === 'tournament' ? 'Torneo' : 'Noticia'}`, 'create-new-button', () => openModalForCreate(typeForCreate));
            sectionElement.appendChild(createButton);
        }

        const listContainer = document.createElement('ul');
        listContainer.className = 'item-list';
        sectionElement.appendChild(listContainer);

        switch (sectionId) {
            case 'torneos': renderTorneos(listContainer); break;
            case 'noticias': renderNoticias(listContainer); break;
            case 'pagos': renderPagos(listContainer); break;
            case 'resenas': renderResenas(listContainer); break;
            case 'reservas': renderReservas(listContainer); break;
            case 'usuarios': renderUsuarios(listContainer); break;
            default:
                const p = document.createElement('p');
                p.textContent = 'Contenido no disponible.';
                listContainer.appendChild(p);
        }
    }

    function renderTorneos(container) {
        if (sportliteDB.tournaments.length === 0) {
            container.innerHTML = '<p>No hay torneos para mostrar.</p>'; return;
        }
        sportliteDB.tournaments.forEach(torneo => {
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
            actionsDiv.appendChild(createActionButton('Eliminar', 'delete', () => {
                if (confirm(`¿Seguro que quieres eliminar el torneo "${torneo.name}"?`)) {
                    sportliteDB.tournaments = sportliteDB.tournaments.filter(t => t.id !== torneo.id);
                    showSection('torneos');
                }
            }));
            
            li.appendChild(detailsDiv);
            li.appendChild(actionsDiv);
            container.appendChild(li);
        });
    }

    function renderNoticias(container) {
        if (sportliteDB.news.length === 0) {
            container.innerHTML = '<p>No hay noticias para mostrar.</p>'; return;
        }
        sportliteDB.news.forEach(noticia => {
            const li = document.createElement('li');
            const detailsDiv = document.createElement('div');
            detailsDiv.className = 'item-details';
            detailsDiv.innerHTML = `
                <strong>${noticia.title}</strong> (ID: ${noticia.id})<br>
                <span class="label">Categoría:</span> <span class="value">${noticia.category}</span> | 
                <span class="label">Autor:</span> <span class="value">${noticia.author}</span> | 
                <span class="label">Publicado:</span> <span class="value">${noticia.publishedDate}</span><br>
                <span class="label">Contenido:</span> <span class="value">${noticia.content.substring(0, 150)}...</span>
            `;

            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'item-actions';
            actionsDiv.appendChild(createActionButton('Editar', 'edit', () => openModalForEdit('news', noticia)));
            actionsDiv.appendChild(createActionButton('Eliminar', 'delete', () => {
                if (confirm(`¿Seguro que quieres eliminar la noticia "${noticia.title}"?`)) {
                    sportliteDB.news = sportliteDB.news.filter(n => n.id !== noticia.id);
                    showSection('noticias');
                }
            }));
            
            li.appendChild(detailsDiv);
            li.appendChild(actionsDiv);
            container.appendChild(li);
        });
    }

    function renderPagos(container) {
        if (sportliteDB.payments.length === 0) {
            container.innerHTML = '<p>No hay pagos registrados.</p>'; return;
        }
         container.parentElement.querySelector('h1').insertAdjacentHTML('afterend', '<h2>Historial y Gestión de Pagos</h2>');

        sportliteDB.payments.forEach(pago => {
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
            actionsDiv.appendChild(createActionButton('Actualizar Estado', 'manage', () => {
                const selectedStatus = document.getElementById(statusSelectId).value;
                const paymentToUpdate = sportliteDB.payments.find(p => p.id === pago.id);
                if (paymentToUpdate) {
                    paymentToUpdate.status = selectedStatus;
                    showSection('pagos');
                    alert(`Estado del pago ID ${pago.id} actualizado a ${selectedStatus}.`);
                }
            }));
            
            li.appendChild(detailsDiv);
            li.appendChild(actionsDiv);
            container.appendChild(li);
        });
    }

    function renderResenas(container) {
        if (sportliteDB.reviews.length === 0) {
            container.innerHTML = '<p>No hay reseñas para mostrar.</p>'; return;
        }
        sportliteDB.reviews.forEach(resena => {
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
            
            actionsDiv.appendChild(createActionButton('Moderar Estado', 'moderate', () => {
                 const selectedStatus = document.getElementById(statusSelectId).value;
                 const reviewToUpdate = sportliteDB.reviews.find(r => r.id === resena.id);
                 if(reviewToUpdate) {
                    reviewToUpdate.status = selectedStatus;
                    showSection('resenas');
                    alert(`Reseña ID ${resena.id} actualizada a ${selectedStatus}.`);
                 }
            }));
            actionsDiv.appendChild(createActionButton('Eliminar', 'delete', () => {
                if (confirm(`¿Seguro que quieres eliminar la reseña ID ${resena.id}?`)) {
                    sportliteDB.reviews = sportliteDB.reviews.filter(r => r.id !== resena.id);
                    showSection('resenas');
                }
            }));
            
            li.appendChild(detailsDiv);
            li.appendChild(actionsDiv);
            container.appendChild(li);
        });
    }

    function renderReservas(container) {
        if (sportliteDB.reservations.length === 0) {
            container.innerHTML = '<p>No hay reservas para mostrar.</p>'; return;
        }
        sportliteDB.reservations.forEach(reserva => {
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

            actionsDiv.appendChild(createActionButton('Gestionar Estado', 'manage', () => {
                 const selectedStatus = document.getElementById(statusSelectId).value;
                 const reservationToUpdate = sportliteDB.reservations.find(r => r.id === reserva.id);
                 if(reservationToUpdate) {
                    reservationToUpdate.status = selectedStatus;
                    showSection('reservas');
                    alert(`Reserva ID ${reserva.id} actualizada a ${selectedStatus}.`);
                 }
            }));
            actionsDiv.appendChild(createActionButton('Eliminar', 'delete', () => {
                if (confirm(`¿Seguro que quieres eliminar la reserva ID ${reserva.id} para "${reserva.facilityName}"?`)) {
                    sportliteDB.reservations = sportliteDB.reservations.filter(r => r.id !== reserva.id);
                    showSection('reservas');
                }
            }));
            
            li.appendChild(detailsDiv);
            li.appendChild(actionsDiv);
            container.appendChild(li);
        });
    }

    function renderUsuarios(container) {
        if (sportliteDB.users.length === 0) {
            container.innerHTML = '<p>No hay usuarios para mostrar.</p>'; return;
        }
        sportliteDB.users.forEach(usuario => {
            const li = document.createElement('li');
            const detailsDiv = document.createElement('div');
            detailsDiv.className = 'item-details';
            detailsDiv.innerHTML = `
                <strong>${usuario.name}</strong> (ID: ${usuario.id})<br>
                <span class="label">Email:</span> <span class="value">${usuario.email}</span> | 
                <span class="label">Rol Actual:</span> <span class="value">${usuario.role}</span><br>
                <span class="label">Registrado:</span> <span class="value">${usuario.registrationDate}</span> | 
                <span class="label">Último Acceso:</span> <span class="value">${usuario.lastLogin}</span>
            `;

            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'item-actions';
            const userRoles = [
                { value: 'admin', text: 'Admin' }, { value: 'editor', text: 'Editor' },
                { value: 'usuario', text: 'Usuario' }, { value: 'cliente', text: 'Cliente' },
                { value: 'bloqueado', text: 'Bloqueado' }
            ];
            const roleSelectId = `role-select-${usuario.id}`;
            const roleSelect = createSelectElement(roleSelectId, userRoles, usuario.role);
            actionsDiv.appendChild(roleSelect);
            
            actionsDiv.appendChild(createActionButton('Cambiar Rol', 'manage', () => {
                const newRole = document.getElementById(roleSelectId).value;
                const userToUpdate = sportliteDB.users.find(u => u.id === usuario.id);
                if (userToUpdate) {
                    if (confirm(`¿Cambiar rol de ${userToUpdate.name} de "${userToUpdate.role}" a "${newRole}"?`)) {
                        userToUpdate.role = newRole;
                        showSection('usuarios');
                        alert(`Rol de ${userToUpdate.name} cambiado a ${newRole}.`);
                    }
                }
            }));
            
            li.appendChild(detailsDiv);
            li.appendChild(actionsDiv);
            container.appendChild(li);
        });
    }


    const initialSectionLink = document.querySelector('.sidebar-nav a[data-section="dashboard"]');
    if (initialSectionLink) {
        initialSectionLink.click(); 
        showSection('dashboard'); 
    }
});