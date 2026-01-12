<?php
if (!isset($c)) return;
?>
<div class="consulta-card" data-status="<?= $c['status'] ?>">
    <div class="consulta-header">
        <div class="paciente-info">
            <h3><?= htmlspecialchars($c['nome_paciente']) ?></h3>
            <p class="consulta-telefone">
                <i class="fas fa-phone"></i>
                <?= htmlspecialchars($c['telefone']) ?>
            </p>
        </div>
        <div class="consulta-status <?= $c['status'] == 'Agendada' ? 'status-agendada' : 'status-realizada' ?>">
            <?= $c['status'] ?>
        </div>
    </div>
    
    <div class="consulta-details">
        <div class="detail-item">
            <i class="fas fa-calendar"></i>
            <div>
                <span class="detail-label">Data</span>
                <span class="detail-value"><?= date('d/m/Y', strtotime($c['data_consulta'])) ?></span>
            </div>
        </div>
        <div class="detail-item">
            <i class="fas fa-clock"></i>
            <div>
                <span class="detail-label">Horário</span>
                <span class="detail-value"><?= $c['horario_consulta'] ?></span>
            </div>
        </div>
        <div class="detail-item">
            <i class="fas fa-video"></i>
            <div>
                <span class="detail-label">Tipo</span>
                <span class="detail-value">
                    <span class="consulta-tipo"><?= $c['tipo_consulta'] ?></span>
                </span>
            </div>
        </div>
        <div class="detail-item">
            <i class="fas fa-user-md"></i>
            <div>
                <span class="detail-label">Profissional</span>
                <span class="detail-value"><?= htmlspecialchars($c['profissional_nome']) ?></span>
            </div>
        </div>
    </div>
    
    <div class="consulta-actions">
        <button class="action-btn contact-btn" onclick="contactClient('<?= $c['telefone'] ?>', '<?= htmlspecialchars(addslashes($c['nome_paciente'])) ?>')">
            <i class="fas fa-phone"></i> Contatar
        </button>
        <?php if ($c['status'] == 'Agendada'): ?>
            <button class="action-btn config-btn" onclick="openEditModal(
                '<?= $c['id'] ?>',
                '<?= htmlspecialchars(addslashes($c['nome_paciente'])) ?>',
                '<?= $c['data_consulta'] ?>',
                '<?= $c['horario_consulta'] ?>',
                '<?= $c['tipo_consulta'] ?>'
            )">
                <i class="fas fa-edit"></i> Editar
            </button>
            <button class="action-btn delete-btn" onclick="deleteConsulta('<?= $c['id'] ?>', '<?= htmlspecialchars(addslashes($c['nome_paciente'])) ?>')">
                <i class="fas fa-trash"></i> Excluir
            </button>
        <?php else: ?>
            <button class="action-btn details-btn" onclick="contactClient('<?= $c['telefone'] ?>', '<?= htmlspecialchars(addslashes($c['nome_paciente'])) ?>')">
                <i class="fas fa-info-circle"></i> Detalhes
            </button>
        <?php endif; ?>
    </div>
</div>