<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
    exit;
}

$nome_paciente = $_POST['nome_paciente'] ?? null;
$telefone = $_POST['telefone'] ?? null;
$data = $_POST['data'] ?? null;
$horario = $_POST['horario'] ?? null;
$tipo = $_POST['tipo'] ?? null;

if (!$nome_paciente || !$telefone || !$data || !$horario || !$tipo) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Dados incompletos']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM agendamentos WHERE data_consulta = ? AND horario_consulta = ? AND profissional_id = ?");
    $stmt->execute([$data, $horario, $_SESSION['admin_id']]);
    $result = $stmt->fetch();

    if ($result['total'] > 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Horário já agendado']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO agendamentos (nome_paciente, telefone, data_consulta, horario_consulta, tipo_consulta, profissional_id, status) VALUES (?, ?, ?, ?, ?, ?, 'Agendada')");
    $stmt->execute([$nome_paciente, $telefone, $data, $horario, $tipo, $_SESSION['admin_id']]);

    $consulta_id = $conn->lastInsertId();

    echo json_encode([
        'status' => 'success', 
        'message' => 'Consulta criada com sucesso',
        'consulta_id' => $consulta_id
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao criar consulta: ' . $e->getMessage()]);
}
?>