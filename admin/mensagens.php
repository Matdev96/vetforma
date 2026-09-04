<?php
declare(strict_types=1);

$arquivo = __DIR__ . '/../dados/contatos.json';
$mensagens = [];

if (is_file($arquivo)) {
    $conteudo = file_get_contents($arquivo);
    $decodificado = json_decode($conteudo !== false ? $conteudo : '', true);
    if (is_array($decodificado)) {
        $mensagens = array_reverse($decodificado);
    }
}

function h(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mensagens de Contato | Vetforma</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
:root{ --navy-800:#002D4E; --gold:#CD8E2D; --container:1180px; --radius:14px; --font:'Mulish',sans-serif; }
*{box-sizing:border-box}
body{margin:0;font-family:var(--font);color:var(--navy-800);background:#f4f6f8;line-height:1.5}
.topo{background:var(--navy-800);color:#fff;padding:20px 24px}
.topo h1{margin:0;font-size:1.2rem}
.container{max-width:var(--container);margin:0 auto;padding:24px}
.contador{margin:0 0 16px;font-size:.9rem;color:#5b6b78}
.card{background:#fff;border-radius:var(--radius);padding:20px 24px;margin-bottom:14px;box-shadow:0 2px 10px rgba(0,0,0,.06)}
.card-top{display:flex;justify-content:space-between;align-items:baseline;gap:16px;flex-wrap:wrap;margin-bottom:10px}
.card-top h2{margin:0;font-size:1.05rem}
.data{font-size:.78rem;color:#8a97a3;font-weight:700}
.linha{margin:4px 0;font-size:.9rem}
.linha strong{color:var(--gold)}
.mensagem{margin-top:10px;padding-top:10px;border-top:1px solid #eef1f4;white-space:pre-wrap;font-size:.92rem}
.vazio{background:#fff;border-radius:var(--radius);padding:40px;text-align:center;color:#8a97a3}
</style>
</head>
<body>
<div class="topo"><h1>Mensagens recebidas pelo site - Vetforma</h1></div>
<div class="container">
  <?php if (empty($mensagens)): ?>
    <div class="vazio">Nenhuma mensagem recebida ainda.</div>
  <?php else: ?>
    <p class="contador"><?php echo count($mensagens); ?> mensagem(ns)</p>
    <?php foreach ($mensagens as $m): ?>
      <div class="card">
        <div class="card-top">
          <h2><?php echo h(($m['nome'] ?? '') . ' ' . ($m['sobrenome'] ?? '')); ?></h2>
          <span class="data"><?php echo h($m['data_hora'] ?? ''); ?></span>
        </div>
        <div class="linha"><strong>Telefone:</strong> <?php echo h($m['telefone'] ?? ''); ?></div>
        <div class="linha"><strong>E-mail:</strong> <?php echo h($m['email'] ?? ''); ?></div>
        <div class="mensagem"><?php echo h($m['mensagem'] ?? ''); ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
</body>
</html>
