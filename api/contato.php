<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function responder(int $status, array $body): void {
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['status' => 'error', 'message' => 'Metodo nao permitido']);
}

// Honeypot: bots costumam preencher campos ocultos. Se vier preenchido,
// fingimos sucesso sem gravar nada, sem revelar a armadilha.
if (!empty($_POST['website'])) {
    responder(200, ['status' => 'ok']);
}

$nome = trim((string)($_POST['nome'] ?? ''));
$sobrenome = trim((string)($_POST['sobrenome'] ?? ''));
$telefone = trim((string)($_POST['telefone'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$mensagem = trim((string)($_POST['mensagem'] ?? ''));

if ($nome === '' || $sobrenome === '' || $telefone === '' || $email === '' || $mensagem === '') {
    responder(400, ['status' => 'error', 'message' => 'Preencha todos os campos obrigatorios']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(400, ['status' => 'error', 'message' => 'E-mail invalido']);
}

$registro = [
    'id' => uniqid('c_', true),
    'data_hora' => date('d/m/Y H:i:s'),
    'nome' => $nome,
    'sobrenome' => $sobrenome,
    'telefone' => $telefone,
    'email' => $email,
    'mensagem' => $mensagem,
];

$dataDir = __DIR__ . '/../dados';
$arquivo = $dataDir . '/contatos.json';

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$fp = fopen($arquivo, 'c+');
if ($fp === false) {
    responder(500, ['status' => 'error', 'message' => 'Nao foi possivel salvar sua mensagem']);
}

if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    responder(500, ['status' => 'error', 'message' => 'Nao foi possivel salvar sua mensagem']);
}

$conteudo = stream_get_contents($fp);
$mensagens = json_decode($conteudo !== false ? $conteudo : '', true);
if (!is_array($mensagens)) {
    $mensagens = [];
}

$mensagens[] = $registro;

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, json_encode($mensagens, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

// Aviso por e-mail em melhor esforco (sem SMTP). Se falhar, a mensagem
// ja esta salva no arquivo, entao nao derrubamos a resposta de sucesso.
$destino = 'vetforma.cursos@gmail.com';
$assunto = '=?UTF-8?B?' . base64_encode('Novo contato pelo site - ' . $nome . ' ' . $sobrenome) . '?=';
$corpo = "Nova mensagem recebida pelo formulario de Contato do site:\n\n"
    . "Nome: {$nome} {$sobrenome}\n"
    . "Telefone: {$telefone}\n"
    . "E-mail: {$email}\n"
    . "Data/Hora: {$registro['data_hora']}\n\n"
    . "Mensagem:\n{$mensagem}\n";
$headers = "From: contato@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n"
    . "Reply-To: {$email}\r\n"
    . "Content-Type: text/plain; charset=UTF-8";

@mail($destino, $assunto, $corpo, $headers);

responder(200, ['status' => 'ok']);
