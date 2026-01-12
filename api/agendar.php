<?php
require_once '../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $proId = $_POST['profissionalId'] ?? '';
    $data = $_POST['data'] ?? '';
    $horario = $_POST['horario'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    if (empty($nome) || empty($telefone) || empty($proId) || empty($data) || empty($horario) || empty($tipo)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos os campos são obrigatórios.']);
        exit;
    }

    $apenasNumeros = preg_replace('/\D/', '', $telefone);

    if (strlen($apenasNumeros) !== 11) {
        echo json_encode(['status' => 'error', 'message' => 'Telefone inválido. O número deve conter DDD e 9 dígitos.']);
        exit;
    }

    try {
        $stmtCheck = $conn->prepare("SELECT id FROM agendamentos WHERE data_consulta = ? AND horario_consulta = ? AND profissional_id = ?");
        $stmtCheck->execute([$data, $horario, $proId]);

        if ($stmtCheck->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Este horário já foi reservado. Por favor, escolha outro.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO agendamentos (nome_paciente, telefone, profissional_id, data_consulta, horario_consulta, tipo_consulta, status) VALUES (?, ?, ?, ?, ?, ?, 'Agendada')");
        
        $stmt->execute([$nome, $telefone, $proId, $data, $horario, $tipo]);

        echo json_encode(['status' => 'success', 'message' => 'Agendamento realizado com sucesso.']);
    } catch (PDOException $e) {
        error_log("Erro no agendamento: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Erro interno no servidor.']);
    }
}
?>