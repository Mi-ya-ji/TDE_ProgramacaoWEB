<?php

ini_set('display_errors', 0);
error_reporting(0);

header("Content-Type: application/json");

try {
    $input = json_decode(file_get_contents("php://input"), true);

    $ciphertext_b64    = $input["ciphertext"] ?? null;
    $encrypted_key_b64 = $input["encrypted_key"] ?? null;
    $iv_b64            = $input["iv"] ?? null;

    if (!$ciphertext_b64 || !$encrypted_key_b64 || !$iv_b64) {
        echo json_encode(["status" => "e", "mensagem" => "Erro: dados criptografados ausentes."]);
        exit;
    }

$privateKeyPath = __DIR__ . "/keys/private_key.pem";

if (!file_exists($privateKeyPath)) {
    echo json_encode(["status" => "e", "mensagem" => "Erro: chave privada não encontrada."]);
    exit;
}

$privateKey = file_get_contents($privateKeyPath);
$privateKeyRes = openssl_pkey_get_private($privateKey);

$encrypted_key = base64_decode($encrypted_key_b64);

$decrypted_key = ""; // aqui vai a chave AES descriptografada

$ok = openssl_private_decrypt(
    $encrypted_key,
    $decrypted_key,
    $privateKeyRes
);

if (!$ok) {
    echo json_encode(["status" => "e", "mensagem" => "Erro ao descriptografar chave AES"]);
    exit;
}

$decrypted_key = base64_decode($decrypted_key);

$ciphertext = base64_decode($ciphertext_b64);
$iv         = base64_decode($iv_b64);

$plain_json = openssl_decrypt(
    $ciphertext,
    "AES-256-CBC",
    $decrypted_key,
    OPENSSL_RAW_DATA,
    $iv
);

if ($plain_json === false) {
    echo json_encode(["status" => "e", "mensagem" => "Erro ao descriptografar dados"]);
    exit;
}

$data = json_decode($plain_json, true);

$nome  = $data["username"] ?? null;
$email = $data["email"] ?? null;
$senha = $data["password"] ?? null;

if (!$nome || !$email || !$senha) {
    echo json_encode(["status" => "e", "mensagem" => "Campos insuficientes."]);
    exit;
}

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

require_once __DIR__ . "/config/conexao.php";

$sql = $conn->prepare("INSERT INTO usuario (email, nome, senha) VALUES (?, ?, ?)");
$sql->bind_param("sss", $email, $nome, $senha_hash);

if ($sql->execute()) {
    echo json_encode(["status" => "s", "mensagem" => "Usuário cadastrado com sucesso!"]);
} else {
    echo json_encode(["status" => "e", "mensagem" => "Erro ao cadastrar usuário: " . $conn->error]);
}

} catch (Exception $e) {
    echo json_encode(["status" => "e", "mensagem" => "Erro interno: " . $e->getMessage()]);
}
?>
