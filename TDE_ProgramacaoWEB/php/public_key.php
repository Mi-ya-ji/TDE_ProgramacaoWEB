<?php
header('Content-Type: application/json');

$publicKeyPath = __DIR__ . '/keys/public_key.pem';

if (file_exists($publicKeyPath)) {
    $publicKey = file_get_contents($publicKeyPath);
    
    echo json_encode([
        'status' => 's',
        'public_key' => $publicKey
    ]);
} else {
    http_response_code(404);
    echo json_encode([
        'status' => 'e',
        'mensagem' => 'Chave pública não encontrada'
    ]);
}
?>
