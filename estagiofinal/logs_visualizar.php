<?php
include "conexao.php";
if (($_SESSION['CATEGORIA_ID'] ?? null) != 1) {
    die("Acesso negado");
}
$pastaBase = __DIR__ . "/logs";
$usuario = $_GET['usuario'] ?? '';
$data = $_GET['data'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$tabela = $_GET['tabela'] ?? '';
$arquivos = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($pastaBase)
);
?>
<?php

$usuarios = [];

$sql = "SELECT nome FROM usuarios ORDER BY nome";

$resultado = mysqli_query($conexao, $sql);

if ($resultado) {

    while ($linha = mysqli_fetch_assoc($resultado)) {
        $usuarios[] = $linha['nome'];
    }

}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>LOGS DO SISTEMA</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<style>
.fundo-tabela {
  background-image: url('img/fundologin.png');
  background-size: cover;
  background-position: center;
  padding: 20px;
  border-radius: 10px;
}

.log-viewer {
  background:#0f172a;
  color:#00ff88;
  padding:15px;
  border-radius:10px;
  font-size:13px;
  max-height:520px;
  overflow:auto;
}

.file-item {
  padding:10px;
  border-bottom:1px solid #d1d5db;
  transition: 0.2s;
}

.file-item:hover {
  background:#e5e7eb;
}

.file-item a {
  text-decoration:none;
  color:#1e293b;
  display:block;
}

.file-item a:hover {
  color:#2563eb;
}

.card-log {
  background:#e5e7eb;
  border-radius:10px;
  padding:20px;
  box-shadow:0 2px 10px rgba(0,0,0,0.08);
}
.logs-sidebar {
    max-height: 650px;
    overflow-y: auto;
    padding-right: 5px;
}

.logs-sidebar::-webkit-scrollbar {
    width: 6px;
}

.logs-sidebar::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 10px;
}

.mes-log {
    font-weight: 600;
    color: #334155;
    margin-top: 12px;
    margin-bottom: 8px;
    font-size: 15px;
}

.arquivo-log {
    padding: 8px 10px;
    margin-left: 18px;
    border-radius: 8px;
    transition: 0.2s;
}

.arquivo-log:hover {
    background: #dbeafe;
}

.arquivo-log a {
    text-decoration: none;
    color: #1e293b;
    display: block;
    font-size: 14px;
}

.arquivo-log.ativo {
    background: #dbeafe;
}

.arquivo-log.ativo a {
    color: #2563eb;
    font-weight: 600;
}
</style>
</head>

<body>
<div class="container mt-4 fundo-tabela mb-4">
  <h2>LOGS DO SISTEMA</h2>
  <small> Monitoramento de ações do sistema </small>
  <hr>
<form method="GET">
<input type="hidden" name="pagina" value="logs_visualizar">
<div class="row">

    <div class="col-md-3 mb-2">
    <select name="usuario" class="form-control">
        <option value="">Usuários</option>
        <?php foreach($usuarios as $u): ?>
        <option value="<?= $u ?>"
        <?= $usuario == $u ? 'selected' : '' ?>>
            <?= $u ?>
        </option>
        <?php endforeach; ?>
    </select>
</div>

   <div class="col-md-3 mb-2">
    <select name="tipo" class="form-control">
        <option value="">Operações</option>
        <option value="ALTERACAO" 
        <?= $tipo == 'ALTERACAO' ? 'selected' : '' ?>>
            Alteração
        </option>
        <option value="EXCLUSAO"
        <?= $tipo == 'EXCLUSAO' ? 'selected' : '' ?>>
            Exclusão
        </option>
        <option value="IMPORTACAO"
        <?= $tipo == 'IMPORTACAO' ? 'selected' : '' ?>>
            Importação
        </option>
        <option value="LOGIN"
        <?= $tipo == 'LOGIN' ? 'selected' : '' ?>>
            Login
        </option>
    </select>
</div>

    <div class="col-md-3 mb-2">
        <select name="tabela" class="form-control">
            <option value="">Tabelas</option>
            <option value="empresas"
            <?= $tabela == 'empresas' ? 'selected' : '' ?>>
                Empresas
            </option>
            <option value="pessoas"
            <?= $tabela == 'pessoas' ? 'selected' : '' ?>>
                Pessoas
            </option>
            <option value="cargos"
            <?= $tabela == 'cargos' ? 'selected' : '' ?>>
                Cargos
            </option>
            <option value="usuarios"
            <?= $tabela == 'usuarios' ? 'selected' : '' ?>>
                Usuários
            </option>
            <option value="tipos"
            <?= $tabela == 'tipos' ? 'selected' : '' ?>>
                Tipos
            </option>
        </select>
    </div>


    <div class="col-md-3 mb-2">
        <input type="date"
               name="data"
               class="form-control"
               value="<?= htmlspecialchars($data) ?>">
    </div>
</div>

<div class="mt-2">
    <button type="submit" class="btn btn-dark">Pesquisar</button>
    <a href="pag1.php?pagina=logs_visualizar"class="btn btn-secondary">Reiniciar</a>
</div>

</form>
</div>
<div class="container">
<div class="row">
  <div class="col-md-4">
    <div class="card-log">
      <h5 class="mb-3">Arquivos de Log</h5>
    <div class="logs-sidebar">
<?php

$temArquivos = false;
$mesAtual = '';

foreach ($arquivos as $file):

    if ($file->isDir()) continue;
    $temArquivos = true;
    $nomePasta = basename(dirname($file->getPathname()));
    if ($mesAtual != $nomePasta) {
        $mesAtual = $nomePasta;

        echo "
        <div class='mes-log'>
            📁 $mesAtual
        </div>
        ";
    }

    $arquivoAtual = $_GET['file'] ?? '';
    $ativo = ($arquivoAtual == $file->getPathname())
        ? 'ativo'
        : '';

?>

<div class="arquivo-log <?= $ativo ?>">

<a href="?pagina=logs_visualizar
&file=<?= urlencode($file->getPathname()) ?>
&usuario=<?= urlencode($usuario) ?>
&tipo=<?= urlencode($tipo) ?>
&tabela=<?= urlencode($tabela) ?>
&data=<?= urlencode($data) ?>">
        📄 <?= basename($file->getPathname()) ?>
    </a>

</div>

<?php endforeach; ?>
<?php if (!$temArquivos): ?>
<p class="text-muted">
    Nenhum log encontrado.
</p>
<?php endif; ?>
</div>
      <?php if (!$temArquivos): ?>
        <p class="text-muted mt-2">
          Nenhum log encontrado.
        </p>
      <?php endif; ?>

    </div>

  </div>
  <div class="col-md-8">
    <div class="card-log">
      <h5 class="mb-3">Conteúdo do Log</h5>
      <?php

    if (
    isset($_GET['file']) &&
    file_exists($_GET['file']) &&
    is_file($_GET['file'])
) {

    $linhas = file(
        $_GET['file'],
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    echo "<div class='log-viewer'>";

    foreach ($linhas as $linha) {

     $linha = trim($linha);

        if (empty($linha)) {
            continue;
        }
if (substr_count($linha, ';') < 4) {
    continue;
}
       $partes = explode(';', $linha);

$usuarioLog   = trim($partes[0] ?? '');
$tipoLog      = trim($partes[1] ?? '');
$tabelaLog    = trim($partes[2] ?? '');
$dataLog      = trim($partes[3] ?? '');
$dadosAntigos = trim($partes[4] ?? '');
$dadosNovos   = trim($partes[5] ?? '');


        $dadosAntigos = str_replace(",", " | ", $dadosAntigos);
        $dadosNovos   = str_replace(",", " | ", $dadosNovos);


        if ($usuario != '' && strcasecmp(trim($usuarioLog), trim($usuario)) != 0) {
    continue;
}

        if ($tipo && stripos($tipoLog, $tipo) === false) {
            continue;
        }

        if ($tabela && stripos($tabelaLog, $tabela) === false) {
            continue;
        }

        if ($data && stripos($dataLog, $data) === false) {
            continue;
        }

echo "
<div style='margin-bottom:15px;'>
    <strong>Usuário:</strong> " . htmlspecialchars($usuarioLog, ENT_QUOTES, 'UTF-8') . " <br>
    <strong>Operação:</strong>
    <span style='color:#22c55e'>" . htmlspecialchars($tipoLog, ENT_QUOTES, 'UTF-8') . "</span>
    <br>
    <strong>Tabela:</strong> " . htmlspecialchars($tabelaLog, ENT_QUOTES, 'UTF-8') . " <br>
    <strong>Data:</strong> " . htmlspecialchars($dataLog, ENT_QUOTES, 'UTF-8') . " <br>
";

     if ($tipoLog == 'ALTERACAO') {

    if (!empty($dadosAntigos)) {
        echo "
        <br>
        <strong style='color:#facc15'>
            Dados Antigos:
        </strong><br>
        " . htmlspecialchars($dadosAntigos, ENT_QUOTES, 'UTF-8');
    }

    if (!empty($dadosNovos)) {
        echo "
        <br><br>
        <strong style='color:#22c55e'>
            Novos Dados:
        </strong><br>
        " . htmlspecialchars($dadosNovos, ENT_QUOTES, 'UTF-8');
    }

} else {

    $dadosCompletos = $dadosAntigos;

    if (!empty($dadosNovos)) {
       $dadosCompletos .= "\n" . $dadosNovos;
    }

 echo "
<br>
<strong style='color:#facc15'>
    Dados:
</strong><br>
" . nl2br(htmlspecialchars($dadosCompletos, ENT_QUOTES, 'UTF-8')) . "
";

       echo "<hr style='border-color:#334155;'>";

} 

echo "</div>";

}

echo "</div>";

} else {

    echo "
    <p class='text-muted'>
        Selecione um arquivo para visualizar
    </p>
    ";
}
      ?>
    </div>
  </div>
</div>
</div>
</body>
</html>