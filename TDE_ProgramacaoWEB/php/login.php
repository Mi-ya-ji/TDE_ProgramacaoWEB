<?php

ini_set('display_errors', 0);
error_reporting(0);

header("Content-Type: application/json");

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

$decrypted_key = "";

$ok = openssl_private_decrypt(
    $encrypted_key,
    $decrypted_key,
    $privateKeyRes
);

if (!$ok) {
    echo json_encode(["status" => "e", "mensagem" => "Falha ao descriptografar chave AES"]);
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

$email = $data["username"] ?? null; 
$senha = $data["password"] ?? null;

if (!$email || !$senha) {
    echo json_encode(["status" => "e", "mensagem" => "Campos insuficientes."]);
    exit;
}

require_once __DIR__ . "/config/conexao.php";

$sql = $conn->prepare("SELECT email, nome, senha FROM usuario WHERE email = ?");
$sql->bind_param("s", $email);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "e", "mensagem" => "Usuário não encontrado"]);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($senha, $user["senha"])) {
    echo json_encode(["status" => "e", "mensagem" => "Senha incorreta"]);
    exit;
}

echo json_encode(["status" => "s", "mensagem" => "Login realizado!"]);
?>