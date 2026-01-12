<?php
require_once '../config/session_check.php';
require_once '../config/db.php';

$profissional_id = $_SESSION['admin_id'];
$nome_logado = $_SESSION['admin_nome'];

try {
    $data_atual = date('Y-m-d');
    $hora_atual = date('H:i:s');
    
    $stmtUpdate = $conn->prepare("UPDATE agendamentos SET status = 'Já realizada' WHERE profissional_id = ? AND (data_consulta < ? OR (data_consulta = ? AND horario_consulta <= ?)) AND status = 'Agendada'");
    $stmtUpdate->execute([$profissional_id, $data_atual, $data_atual, $hora_atual]);

    $stmt = $conn->prepare("SELECT a.*, p.nome as profissional_nome FROM agendamentos a JOIN profissionais p ON a.profissional_id = p.id WHERE a.profissional_id = ? ORDER BY a.data_consulta DESC, a.horario_consulta DESC");
    $stmt->execute([$profissional_id]);
    $consultas = $stmt->fetchAll();

    $consultas_agendadas = array_filter($consultas, function($c) {
        return $c['status'] == 'Agendada';
    });

    $consultas_realizadas = array_filter($consultas, function($c) {
        return $c['status'] == 'Já realizada';
    });

    $consultas_hoje = array_filter($consultas, function($c) {
        return $c['data_consulta'] == date('Y-m-d') && $c['status'] == 'Agendada';
    });

    $consultas_futuras = array_filter($consultas, function($c) {
        return $c['data_consulta'] > date('Y-m-d') && $c['status'] == 'Agendada';
    });

    $consultas_passadas = array_filter($consultas, function($c) {
        return ($c['data_consulta'] < date('Y-m-d') || ($c['data_consulta'] == date('Y-m-d') && $c['horario_consulta'] <= date('H:i:s'))) && $c['status'] == 'Já realizada';
    });

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="app-container dashboard-container">
        <header class="app-header">
            <div class="header-content">
                <div class="header-brand">
                    <div class="brand-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h1>Painel Administrativo</h1>
                        <p class="user-welcome">Bem-vindo, <strong><?= htmlspecialchars($nome_logado) ?></strong></p>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="action-btn config-btn" onclick="openCreateModal()">
                        <i class="fas fa-plus"></i> Nova Consulta
                    </button>
                    <a href="logout.php" class="logout-btn" onclick="return confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </a>
                </div>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= count($consultas_agendadas) ?></h3>
                        <p>Agendadas</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= count($consultas_futuras) ?></h3>
                        <p>Futuras</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= count($consultas_realizadas) ?></h3>
                        <p>Realizadas</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="dashboard-content">
            <div class="dashboard-header">
                <h2><i class="fas fa-list-alt"></i> Minhas Consultas</h2>
                <div class="filter-controls">
                    <button class="filter-btn active" data-filter="all">Todas</button>
                    <button class="filter-btn" data-filter="today">Hoje</button>
                    <button class="filter-btn" data-filter="future">Futuras</button>
                    <button class="filter-btn" data-filter="past">Passadas</button>
                </div>
            </div>

            <div class="consultas-container">
                <div class="consultas-section active" id="section-all">
                    <?php if (count($consultas_agendadas) > 0): ?>
                        <div class="section-header">
                            <h3><i class="fas fa-calendar-check"></i> Consultas Agendadas</h3>
                            <span class="section-count"><?= count($consultas_agendadas) ?></span>
                        </div>
                        <div class="consultas-grid">
                            <?php foreach ($consultas_agendadas as $c): ?>
                                <?php include '../telas/consulta_card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (count($consultas_realizadas) > 0): ?>
                        <div class="section-divider"></div>
                        
                        <div class="section-header">
                            <h3><i class="fas fa-history"></i> Consultas Já Realizadas</h3>
                            <span class="section-count"><?= count($consultas_realizadas) ?></span>
                        </div>
                        <div class="consultas-grid">
                            <?php foreach ($consultas_realizadas as $c): ?>
                                <?php include '../telas/consulta_card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (count($consultas) == 0): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <h3>Nenhuma consulta encontrada</h3>
                            <p>Você ainda não tem consultas agendadas.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="consultas-section" id="section-today">
                    <?php if (count($consultas_hoje) > 0): ?>
                        <div class="consultas-grid">
                            <?php foreach ($consultas_hoje as $c): ?>
                                <?php include 'components/consulta_card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <h3>Nenhuma consulta para hoje</h3>
                            <p>Não há consultas agendadas para o dia de hoje.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="consultas-section" id="section-future">
                    <?php if (count($consultas_futuras) > 0): ?>
                        <div class="consultas-grid">
                            <?php foreach ($consultas_futuras as $c): ?>
                                <?php include '../telas/consulta_card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <h3>Nenhuma consulta futura</h3>
                            <p>Não há consultas agendadas para datas futuras.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="consultas-section" id="section-past">
                    <?php if (count($consultas_passadas) > 0): ?>
                        <div class="consultas-grid">
                            <?php foreach ($consultas_passadas as $c): ?>
                                <?php include '../telas/consulta_card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <h3>Nenhuma consulta passada</h3>
                            <p>Não há consultas realizadas anteriormente.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <div id="editModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Configurar Consulta</h2>
                <button class="modal-close" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-patient-info">
                    <h3 id="modalPatientName"></h3>
                    <p>Edite os detalhes da consulta abaixo:</p>
                </div>
                
                <form id="editForm" class="modal-form">
                    <input type="hidden" id="editConsultaId" name="consulta_id">
                    
                    <div class="form-group">
                        <label for="editData" class="form-label">
                            <i class="fas fa-calendar"></i> Nova Data
                        </label>
                        <input type="date" id="editData" name="data" class="form-input" required min="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="editHorario" class="form-label">
                            <i class="fas fa-clock"></i> Novo Horário
                        </label>
                        <select id="editHorario" name="horario" class="form-input" required>
                            <option value="">Selecione um horário</option>
                            <?php for($h = 9; $h <= 17; $h++): ?>
                                <?php foreach(['00', '30'] as $m): ?>
                                    <?php if($h == 17 && $m == '30') continue; ?>
                                    <?php $time = sprintf('%02d:%s', $h, $m); ?>
                                    <option value="<?= $time ?>"><?= $time ?></option>
                                <?php endforeach; ?>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editTipo" class="form-label">
                            <i class="fas fa-video"></i> Tipo de Atendimento
                        </label>
                        <select id="editTipo" name="tipo" class="form-input" required>
                            <option value="Presencial">Presencial</option>
                            <option value="Online">Online</option>
                        </select>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeEditModal()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="createModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Nova Consulta</h2>
                <button class="modal-close" onclick="closeCreateModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="createForm" class="modal-form">
                    <div class="form-group">
                        <label for="createNome" class="form-label">
                            <i class="fas fa-user"></i> Nome do Paciente
                        </label>
                        <input type="text" id="createNome" name="nome_paciente" class="form-input" required placeholder="Digite o nome do paciente">
                    </div>
                    
                    <div class="form-group">
                        <label for="createTelefone" class="form-label">
                            <i class="fas fa-phone"></i> Telefone
                        </label>
                        <input type="tel" id="createTelefone" name="telefone" class="form-input" required placeholder="(11) 99999-9999" pattern="\([0-9]{2}\) [0-9]{4,5}-[0-9]{4}">
                    </div>
                    
                    <div class="form-group">
                        <label for="createData" class="form-label">
                            <i class="fas fa-calendar"></i> Data
                        </label>
                        <input type="date" id="createData" name="data" class="form-input" required min="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="createHorario" class="form-label">
                            <i class="fas fa-clock"></i> Horário
                        </label>
                        <select id="createHorario" name="horario" class="form-input" required>
                            <option value="">Selecione um horário</option>
                            <?php for($h = 9; $h <= 17; $h++): ?>
                                <?php foreach(['00', '30'] as $m): ?>
                                    <?php if($h == 17 && $m == '30') continue; ?>
                                    <?php $time = sprintf('%02d:%s', $h, $m); ?>
                                    <option value="<?= $time ?>"><?= $time ?></option>
                                <?php endforeach; ?>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="createTipo" class="form-label">
                            <i class="fas fa-video"></i> Tipo de Atendimento
                        </label>
                        <select id="createTipo" name="tipo" class="form-input" required>
                            <option value="Presencial">Presencial</option>
                            <option value="Online">Online</option>
                        </select>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeCreateModal()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> Criar Consulta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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

        function openEditModal(id, pacienteNome, data, horario, tipo) {
            document.getElementById('editConsultaId').value = id;
            document.getElementById('modalPatientName').textContent = pacienteNome;
            document.getElementById('editData').value = data;
            document.getElementById('editHorario').value = horario;
            document.getElementById('editTipo').value = tipo;
            
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('editForm').reset();
        }

        function openCreateModal() {
            document.getElementById('createModal').style.display = 'flex';
        }

        function closeCreateModal() {
            document.getElementById('createModal').style.display = 'none';
            document.getElementById('createForm').reset();
        }

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
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
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: data.message,
                        confirmButtonColor: '#4f46e5'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de conexão',
                    text: 'Não foi possível atualizar a consulta. Tente novamente.',
                    confirmButtonColor: '#4f46e5'
                });
            });
        });

        document.getElementById('createForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            Swal.fire({
                title: 'Criando consulta...',
                text: 'Por favor, aguarde.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('../api/criar_consulta.php', {
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
                        closeCreateModal();
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: data.message,
                        confirmButtonColor: '#4f46e5'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de conexão',
                    text: 'Não foi possível criar a consulta. Tente novamente.',
                    confirmButtonColor: '#4f46e5'
                });
            });
        });

        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.consultas-section').forEach(section => {
                    section.classList.remove('active');
                });
                
                const filter = this.dataset.filter;
                document.getElementById(`section-${filter}`).classList.add('active');
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
                closeCreateModal();
            }
        });

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateModal();
            }
        });

        const deleteConsulta = (id, nome) => {
            Swal.fire({
                title: 'Excluir consulta?',
                html: `Tem certeza que deseja excluir a consulta de <strong>${nome}</strong>? Esta ação não pode ser desfeita.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Excluindo...',
                        text: 'Por favor, aguarde.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('../api/deletar_consulta.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ consulta_id: id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Excluída!',
                                text: data.message,
                                confirmButtonColor: '#4f46e5'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: data.message,
                                confirmButtonColor: '#4f46e5'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro de conexão',
                            text: 'Não foi possível excluir a consulta. Tente novamente.',
                            confirmButtonColor: '#4f46e5'
                        });
                    });
                }
            });
        };
    </script>
</body>
</html>