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

$consulta_id = $_POST['consulta_id'] ?? null;
$data = $_POST['data'] ?? null;
$horario = $_POST['horario'] ?? null;
$tipo = $_POST['tipo'] ?? null;

if (!$consulta_id || !$data || !$horario || !$tipo) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Dados incompletos']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT profissional_id FROM agendamentos WHERE id = ?");
    $stmt->execute([$consulta_id]);
    $consulta = $stmt->fetch();

    if (!$consulta) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Consulta não encontrada']);
        exit;
    }

    if ($consulta['profissional_id'] != $_SESSION['admin_id']) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Você não tem permissão para editar esta consulta']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE agendamentos SET data_consulta = ?, horario_consulta = ?, tipo_consulta = ? WHERE id = ?");
    $stmt->execute([$data, $horario, $tipo, $consulta_id]);

    echo json_encode(['status' => 'success', 'message' => 'Consulta atualizada com sucesso']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar consulta: ' . $e->getMessage()]);
}
?>