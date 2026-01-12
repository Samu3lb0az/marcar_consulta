let currentStep = 0;
const totalSteps = 7; // Aumentamos para 7 passos (agora temos etapa de confirmação)
const appointmentData = {
    nome: '',
    telefone: '',
    profissional: '',
    profissionalId: '',
    data: '',
    horario: '',
    tipo: ''
};

const chatContainer = document.getElementById('chat-container');
const inputArea = document.getElementById('input-area');
const progressFill = document.getElementById('progress-fill');

const profissionais = [
    { id: 1, nome: 'Dr. João Silva', especialidade: 'Psicólogo', icon: 'fas fa-brain' },
    { id: 2, nome: 'Dra. Maria Oliveira', especialidade: 'Psicóloga', icon: 'fas fa-brain' }
];

window.onload = () => {
    updateProgress();
    setTimeout(() => {
        showBotMessage("Olá, seja bem-vindo ao sistema de agendamento de consultas. Estou aqui para facilitar o seu agendamento de forma rápida e segura. Para começarmos, como posso te chamar?");
        renderInput("text", "Digite seu nome completo", "validarNome", "fas fa-user");
    }, 800);
};

function updateProgress() {
    const progress = (currentStep / totalSteps) * 100;
    progressFill.style.width = `${progress}%`;
}

function showBotMessage(text) {
    const div = document.createElement('div');
    div.className = 'message bot';
    
    const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    div.innerHTML = `${text}<span class="message-time">${time}</span>`;
    
    chatContainer.appendChild(div);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function showUserMessage(text) {
    const div = document.createElement('div');
    div.className = 'message user';
    
    const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    div.innerHTML = `${text}<span class="message-time">${time}</span>`;
    
    chatContainer.appendChild(div);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function renderInput(type, placeholder, func, icon = 'fas fa-edit') {
    inputArea.innerHTML = `
        <div class="input-group">
            <div class="input-wrapper">
                <i class="input-icon ${icon}"></i>
                <input type="${type}" id="user-input" class="input-field" placeholder="${placeholder}" autocomplete="off" onkeypress="if(event.key==='Enter')${func}()">
            </div>
            <button class="btn-send" onclick="${func}()">
                <i class="fas fa-paper-plane"></i>
                Enviar
            </button>
        </div>
    `;
    
    const input = document.getElementById('user-input');
    input.focus();
    
    if(type === 'tel') {
        input.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, "");
            if (v.length > 11) v = v.slice(0, 11);
            
            if (v.length > 10) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
            } else if (v.length > 6) {
                v = v.replace(/^(\d{2})(\d{4,5})(\d{0,4}).*/, "($1) $2-$3");
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5})/, "($1) $2");
            } else if (v.length > 0) {
                v = v.replace(/^(\d*)/, "($1");
            }
            e.target.value = v;
        });
    }
}

function validarNome() {
    const val = document.getElementById('user-input').value.trim();
    const nameRegex = /^[a-zA-ZÀ-ÿ\s]{2,} [a-zA-ZÀ-ÿ\s]{2,}/;
    
    if (nameRegex.test(val)) {
        appointmentData.nome = val;
        showUserMessage(val);
        currentStep = 1;
        updateProgress();
        
        setTimeout(() => {
            showBotMessage(`Perfeito, ${val.split(' ')[0]}! Agora preciso do seu telefone com DDD para finalizarmos sua identificação.`);
            renderInput("tel", "(00) 00000-0000", "validarTel", "fas fa-phone");
        }, 600);
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Nome incompleto',
            text: 'Por favor, insira seu nome e sobrenome (apenas letras).',
            confirmButtonColor: '#4f46e5'
        });
    }
}

function validarTel() {
    const val = document.getElementById('user-input').value;
    const numeros = val.replace(/\D/g, "");
    
    if (numeros.length === 11) {
        appointmentData.telefone = val;
        showUserMessage(val);
        currentStep = 2;
        updateProgress();
        
        setTimeout(() => {
            showBotMessage("Ótimo! Agora, selecione o profissional que realizará sua consulta:");
            renderProSelection();
        }, 600);
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Telefone inválido',
            text: 'Por favor, digite um número com DDD e 9 dígitos.',
            confirmButtonColor: '#4f46e5'
        });
    }
}

function renderProSelection() {
    const carouselContainer = document.createElement('div');
    carouselContainer.className = 'carousel-container';
    
    const title = document.createElement('div');
    title.className = 'carousel-title';
    title.textContent = 'Selecione um profissional:';
    
    const carousel = document.createElement('div');
    carousel.className = 'carousel';
    
    profissionais.forEach(prof => {
        const card = document.createElement('div');
        card.className = 'option-card';
        card.innerHTML = `
            <i class="${prof.icon}"></i>
            <div class="card-label">${prof.nome}</div>
            <div class="card-description">${prof.especialidade}</div>
        `;
        card.onclick = () => selectPro(prof.nome, prof.id);
        carousel.appendChild(card);
    });
    
    carouselContainer.appendChild(title);
    carouselContainer.appendChild(carousel);
    inputArea.innerHTML = '';
    inputArea.appendChild(carouselContainer);
}

function selectPro(name, id) {
    const cards = document.querySelectorAll('.option-card');
    cards.forEach(card => card.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    
    appointmentData.profissional = name;
    appointmentData.profissionalId = id;
    showUserMessage(name);
    
    setTimeout(() => {
        showBotMessage(`Excelente escolha! Agora selecione a data para sua consulta com ${name.split(' ')[1]}:`);
        renderDateCarousel();
    }, 600);
}

function renderDateCarousel() {
    currentStep = 3;
    updateProgress();
    
    const carouselContainer = document.createElement('div');
    carouselContainer.className = 'carousel-container';
    
    const title = document.createElement('div');
    title.className = 'carousel-title';
    title.textContent = 'Selecione uma data disponível:';
    
    const carousel = document.createElement('div');
    carousel.className = 'carousel';
    
    for (let i = 1; i <= 7; i++) {
        let d = new Date();
        d.setDate(d.getDate() + i);
        
        const diaSemana = d.toLocaleDateString('pt-BR', { weekday: 'short' });
        const dia = d.getDate().toString().padStart(2, '0');
        const mes = (d.getMonth() + 1).toString().padStart(2, '0');
        const label = `${diaSemana}, ${dia}/${mes}`;
        const fullDate = d.toISOString().split('T')[0];
        
        const card = document.createElement('div');
        card.className = 'option-card date-card';
        card.innerHTML = `<div class="card-label">${label}</div>`;
        card.onclick = () => selectDate(label, fullDate);
        carousel.appendChild(card);
    }
    
    carouselContainer.appendChild(title);
    carouselContainer.appendChild(carousel);
    
    const backButton = document.createElement('button');
    backButton.className = 'btn-back';
    backButton.innerHTML = '<i class="fas fa-arrow-left"></i> Voltar para profissionais';
    backButton.onclick = renderProSelection;
    
    inputArea.innerHTML = '';
    inputArea.appendChild(carouselContainer);
    inputArea.appendChild(backButton);
}

function selectDate(label, val) {
    const cards = document.querySelectorAll('.option-card');
    cards.forEach(card => card.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    
    appointmentData.data = val;
    showUserMessage(label);
    
    setTimeout(() => {
        showBotMessage("Data registrada! Agora selecione um horário disponível:");
        renderTimeCarousel();
    }, 600);
}

function renderTimeCarousel() {
    currentStep = 4;
    updateProgress();
    
    const carouselContainer = document.createElement('div');
    carouselContainer.className = 'carousel-container';
    
    const title = document.createElement('div');
    title.className = 'carousel-title';
    title.textContent = 'Selecione um horário:';
    
    const carousel = document.createElement('div');
    carousel.className = 'carousel';
    
    for (let h = 9; h <= 17; h++) {
        ['00', '30'].forEach(m => {
            if (h === 17 && m === '30') return;
            
            let time = `${h.toString().padStart(2, '0')}:${m}`;
            const card = document.createElement('div');
            card.className = 'option-card time-card';
            card.innerHTML = `<div class="card-label">${time}</div>`;
            card.onclick = () => confirmTime(time);
            carousel.appendChild(card);
        });
    }
    
    carouselContainer.appendChild(title);
    carouselContainer.appendChild(carousel);
    
    const backButton = document.createElement('button');
    backButton.className = 'btn-back';
    backButton.innerHTML = '<i class="fas fa-arrow-left"></i> Voltar para datas';
    backButton.onclick = renderDateCarousel;
    
    inputArea.innerHTML = '';
    inputArea.appendChild(carouselContainer);
    inputArea.appendChild(backButton);
}

function confirmTime(time) {
    const cards = document.querySelectorAll('.option-card');
    cards.forEach(card => card.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    
    appointmentData.horario = time;
    showUserMessage(time);
    
    setTimeout(() => {
        showBotMessage("Perfeito! Por último, escolha o tipo de atendimento:");
        renderTypeSelection();
    }, 600);
}

function renderTypeSelection() {
    currentStep = 5;
    updateProgress();
    
    const carouselContainer = document.createElement('div');
    carouselContainer.className = 'carousel-container';
    
    const title = document.createElement('div');
    title.className = 'carousel-title';
    title.textContent = 'Selecione o tipo de atendimento:';
    
    const carousel = document.createElement('div');
    carousel.className = 'carousel';
    
    const tipos = [
        { tipo: 'Presencial', icon: 'fas fa-hospital', desc: 'Consulta no consultório' },
        { tipo: 'Online', icon: 'fas fa-video', desc: 'Consulta por vídeo' }
    ];
    
    tipos.forEach(t => {
        const card = document.createElement('div');
        card.className = 'option-card';
        card.innerHTML = `
            <i class="${t.icon}"></i>
            <div class="card-label">${t.tipo}</div>
            <div class="card-description">${t.desc}</div>
        `;
        card.onclick = () => {
            const cards = document.querySelectorAll('.option-card');
            cards.forEach(card => card.classList.remove('selected'));
            card.classList.add('selected');
            
            appointmentData.tipo = t.tipo;
            showUserMessage(t.tipo);
            
            setTimeout(() => {
                showConfirmationSummary();
            }, 600);
        };
        carousel.appendChild(card);
    });
    
    carouselContainer.appendChild(title);
    carouselContainer.appendChild(carousel);
    
    const backButton = document.createElement('button');
    backButton.className = 'btn-back';
    backButton.innerHTML = '<i class="fas fa-arrow-left"></i> Voltar para horários';
    backButton.onclick = renderTimeCarousel;
    
    inputArea.innerHTML = '';
    inputArea.appendChild(carouselContainer);
    inputArea.appendChild(backButton);
}

function showConfirmationSummary() {
    currentStep = 6;
    updateProgress();
    
    const formattedDate = new Date(appointmentData.data).toLocaleDateString('pt-BR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
    
    showBotMessage(`
        <strong>Resumo do Agendamento:</strong><br><br>
        <strong>Nome:</strong> ${appointmentData.nome}<br>
        <strong>Profissional:</strong> ${appointmentData.profissional}<br>
        <strong>Data:</strong> ${formattedDate}<br>
        <strong>Horário:</strong> ${appointmentData.horario}<br>
        <strong>Tipo:</strong> ${appointmentData.tipo}<br><br>
        Confirma essas informações?
    `);
    
    inputArea.innerHTML = `
        <div class="confirmation-actions">
            <button class="btn-cancel" onclick="goBackToEdit()">
                <i class="fas fa-edit"></i> Editar Informações
            </button>
            <button class="btn-confirm" onclick="finalizeAppointment()">
                <i class="fas fa-check"></i> Confirmar Agendamento
            </button>
        </div>
    `;
}

function goBackToEdit() {
    // Volta para a seleção do tipo (última etapa antes da confirmação)
    showBotMessage("Vamos ajustar as informações. Qual parte você gostaria de editar?");
    
    inputArea.innerHTML = `
        <div class="edit-options">
            <button class="edit-option-btn" onclick="renderProSelection()">
                <i class="fas fa-user-md"></i> Profissional
            </button>
            <button class="edit-option-btn" onclick="renderDateCarousel()">
                <i class="fas fa-calendar"></i> Data
            </button>
            <button class="edit-option-btn" onclick="renderTimeCarousel()">
                <i class="fas fa-clock"></i> Horário
            </button>
            <button class="edit-option-btn" onclick="renderTypeSelection()">
                <i class="fas fa-video"></i> Tipo de Atendimento
            </button>
        </div>
    `;
}

function finalizeAppointment() {
    currentStep = 7;
    updateProgress();
    
    showBotMessage("Estou processando seu agendamento <span class='loading-indicator'><span class='loading-dot'></span><span class='loading-dot'></span><span class='loading-dot'></span></span>");
    
    const fd = new FormData();
    for(let k in appointmentData) {
        fd.append(k, appointmentData[k]);
    }
    
    fetch('api/agendar.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        if(res.status === 'success') {
            showFinalConfirmation();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Horário indisponível',
                text: res.message,
                confirmButtonColor: '#4f46e5'
            }).then(() => {
                renderTimeCarousel();
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Erro de conexão',
            text: 'Não foi possível completar o agendamento. Tente novamente.',
            confirmButtonColor: '#4f46e5'
        }).then(() => {
            renderTimeCarousel();
        });
    });
}

function showFinalConfirmation() {
    const confirmationDiv = document.createElement('div');
    confirmationDiv.className = 'confirmation-message';
    
    const formattedDate = new Date(appointmentData.data).toLocaleDateString('pt-BR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
    
    confirmationDiv.innerHTML = `
        <div class="confirmation-header">
            <div class="confirmation-icon">
                <i class="fas fa-check"></i>
            </div>
            <div class="confirmation-title">Agendamento Confirmado!</div>
        </div>
        <div class="confirmation-details">
            <div class="confirmation-item">
                <div class="confirmation-label">Paciente</div>
                <div class="confirmation-value">${appointmentData.nome}</div>
            </div>
            <div class="confirmation-item">
                <div class="confirmation-label">Profissional</div>
                <div class="confirmation-value">${appointmentData.profissional}</div>
            </div>
            <div class="confirmation-item">
                <div class="confirmation-label">Data</div>
                <div class="confirmation-value">${formattedDate}</div>
            </div>
            <div class="confirmation-item">
                <div class="confirmation-label">Horário</div>
                <div class="confirmation-value">${appointmentData.horario}</div>
            </div>
            <div class="confirmation-item">
                <div class="confirmation-label">Tipo</div>
                <div class="confirmation-value">${appointmentData.tipo}</div>
            </div>
            <div class="confirmation-item">
                <div class="confirmation-label">Telefone</div>
                <div class="confirmation-value">${appointmentData.telefone}</div>
            </div>
        </div>
    `;
    
    chatContainer.appendChild(confirmationDiv);
    chatContainer.scrollTop = chatContainer.scrollHeight;
    
    inputArea.innerHTML = `
        <button class="btn-send" onclick="window.location.reload()" style="width: 100%;">
            <i class="fas fa-calendar-plus"></i>
            Novo Agendamento
        </button>
    `;
}

// Funções para o Dashboard (se existir o modal)
if (document.getElementById('editModal')) {
    function openEditModal(id, pacienteNome, data, horario, tipo) {
        const dataObj = new Date(data + 'T00:00:00');
        const dataFormatada = dataObj.toISOString().split('T')[0];
        
        document.getElementById('editConsultaId').value = id;
        document.getElementById('modalPatientName').textContent = pacienteNome;
        document.getElementById('editData').value = dataFormatada;
        document.getElementById('editHorario').value = horario;
        document.getElementById('editTipo').value = tipo;
        
        const errorMessages = document.querySelectorAll('.modal-error');
        errorMessages.forEach(msg => msg.remove());
        
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const consultaId = document.getElementById('editConsultaId').value;
            const data = document.getElementById('editData').value;
            const horario = document.getElementById('editHorario').value;
            
            if (!data || !horario) {
                showModalError('Por favor, preencha todos os campos.');
                return;
            }
            
            const dataAtual = new Date();
            const dataConsulta = new Date(data + 'T00:00:00');
            
            if (dataConsulta < dataAtual.setHours(0, 0, 0, 0)) {
                showModalError('Não é possível agendar para datas passadas.');
                return;
            }
            
            Swal.fire({
                title: 'Salvando alterações...',
                text: 'Por favor, aguarde.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('../api/editar_consulta.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if(data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message,
                        confirmButtonColor: '#4f46e5'
                    }).then(() => {
                        closeEditModal();
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    });
                } else {
                    showModalError(data.message);
                }
            })
            .catch(error => {
                Swal.close();
                showModalError('Erro de conexão. Tente novamente.');
            });
        });
    }

    function showModalError(message) {
        const errorMessages = document.querySelectorAll('.modal-error');
        errorMessages.forEach(msg => msg.remove());
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'modal-error';
        errorDiv.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                ${message}
            </div>
        `;
        
        const modalBody = document.querySelector('.modal-body');
        if (modalBody) {
            modalBody.insertBefore(errorDiv, modalBody.firstChild);
            
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('editModal').style.display === 'flex') {
            closeEditModal();
        }
    });

    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });
}

if (typeof contactClient !== 'function') {
    function contactClient(telefone, nome) {
        Swal.fire({
            title: `Contatar ${nome}`,
            html: `<div style="text-align: center; padding: 20px;">
                     <div style="font-size: 48px; color: #4f46e5; margin-bottom: 15px;">
                       <i class="fas fa-user-circle"></i>
                     </div>
                     <h3 style="margin-bottom: 10px;">${nome}</h3>
                     <p style="font-size: 18px; color: #4b5563;">
                       <i class="fas fa-phone"></i> ${telefone}
                     </p>
                   </div>`,
            icon: 'info',
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'OK'
        });
    }
}

if (typeof confirmLogout !== 'function') {
    function confirmLogout() {
        event.preventDefault();
        Swal.fire({
            title: 'Tem certeza?',
            text: "Você será desconectado do painel administrativo.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sim, sair',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
        return false;
    }
}