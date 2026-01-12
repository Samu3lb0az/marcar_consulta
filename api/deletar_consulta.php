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

$data = json_decode(file_get_contents('php://input'), true);
$consulta_id = $data['consulta_id'] ?? null;

if (!$consulta_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID da consulta não fornecido']);
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
        echo json_encode(['status' => 'error', 'message' => 'Você não tem permissão para deletar esta consulta']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM agendamentos WHERE id = ?");
    $stmt->execute([$consulta_id]);

    echo json_encode(['status' => 'success', 'message' => 'Consulta deletada com sucesso']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao deletar consulta: ' . $e->getMessage()]);
}
?>