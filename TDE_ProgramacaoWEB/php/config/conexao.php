<?php
$conn = new mysqli("localhost", "root", "Cmy22#07", "tde_programacaoweb");

if ($conn->connect_error) {
    header("Content-Type: application/json");
    echo json_encode([
        'status' => 'e',
        'mensagem' => 'Falha na conexão com o banco de dados: ' . $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8mb4");
?>
