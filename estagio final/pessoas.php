<?php
$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';
$empresa_id = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PESSOAS</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
 <style>
  .a {
    text-decoration: none;
    color: white;
}
.img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}
.fundo-tabela {
    background-image: url('img/fundologin.png');
    background-size: cover;
    background-position: center;
    padding: 20px;
    border-radius: 10px;
}
.table td,
.table th {
    vertical-align: middle;
    text-align: center;
    font-size: 14px;
}

.col-acoes {
    white-space: nowrap;
}

.col-acoes .btn {
    padding: 3px 8px;
    font-size: 14px;
    margin: 1px;
}

</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

<script src="js/pessoas.js"></script>
<div class="container mt-4 fundo-tabela">
  <h2>PESSOAS</h2>  
 <?php if (in_array($_SESSION['CATEGORIA_ID'] ?? null, [1,2,3])){ ?>
 
<button type="button"
        onclick="window.location.href='pag1.php?pagina=CADASTRO1&empresa_id=<?php echo isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0; ?>'"
        class="btn btn-dark">
    Nova Pessoa
</button>
<?php } ?>
<a href="relatorios_pessoas.php?nome=<?php echo urlencode($nome); ?>&empresa_id=<?php echo $empresa_id; ?>"
   class="btn btn-dark"
   target="_blank"
   rel="noopener noreferrer">
   Relatório
</a>
  </br>
  <?php if (($_SESSION['CATEGORIA_ID'] ?? null) != 0) { ?>

<form method="GET" action="">
    <input type="hidden" name="pagina" value="PESSOAS">
    <input type="hidden" name="empresa_id" value="<?php echo $empresa_id; ?>">

    <div class="row align-items-center">
      <div class="col-md-6 mb-3">
        <br>
       <input type="text"
       class="form-control"
       id="myInput"
       placeholder="Pesquisar pessoa..."
       onkeyup="myFunction()">
        </br>
      </div>
     <div class="col-md-3 mb-3 d-flex align-items-center">
  <button type="submit" class="btn btn-dark mr-3">
    <i class="bi bi-search"></i>
  </button>
  <a href="pag1.php?pagina=PESSOAS" class="btn btn-dark px-4">
    Limpar
  </a>
</div>

    </div>
    <br>
  </form>
<?php } ?>
</div>
<div class="container mt-4">

<?php
include_once("conexao.php");
include_once("logs.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nome = $_GET['nome'] ?? '';
$empresa_id = intval($_GET['empresa_id'] ?? 0);

$pagina = max(1, intval($_GET['pagina_num'] ?? 1));
$limite = 10;
$offset = ($pagina - 1) * $limite;

$filtro = " WHERE 1=1 ";

if (($_SESSION['CATEGORIA_ID'] ?? null) == 3) {
    $empresaUsuario = intval($_SESSION['EMPRESA_ID']);
    $filtro .= " AND p.EMPRESA_ID = $empresaUsuario";
}

if ($empresa_id > 0) {
    $filtro .= " AND p.EMPRESA_ID = $empresa_id";
}

if (!empty($nome)) {
    $nome = $conexao->real_escape_string($nome);
    $filtro .= " AND LOWER(p.NOME) LIKE LOWER('%$nome%')";
}

$sql_total = "
SELECT COUNT(*) as total
FROM pessoas p
LEFT JOIN empresas e ON p.EMPRESA_ID = e.ID
LEFT JOIN cargos c ON p.CARGO_ID = c.ID
$filtro
";

$total_registros = $conexao->query($sql_total)->fetch_assoc()['total'];
$total_paginas = max(1, ceil($total_registros / $limite));

$total = $total_registros;

$sql = "
SELECT 
    p.ID,
    p.EMPRESA_ID,
    p.NOME,
    p.INGRESSO_PERMANENTE,
    p.FOTO,
    p.CPF,
    p.DOCUMENTO,
    p.TELEFONE,
    e.NOME_FANTASIA,
    c.NOME AS CARGO_NOME
FROM pessoas p
LEFT JOIN empresas e ON p.EMPRESA_ID = e.ID
LEFT JOIN cargos c ON p.CARGO_ID = c.ID
$filtro
ORDER BY NOME ASC
LIMIT $limite OFFSET $offset
";

$result = $conexao->query($sql);
?>

<table id="myTable" class="table table-striped align-middle">
<thead>
<tr>
    <th colspan="10" class="text-left bg-light">
        Total de Pessoas: <?php echo $total; ?>
    </th>
</tr>
      <tr>
        <?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
        <th>#</th>
        <?php } ?>
        <th>Empresa</th>
        <th>Nome</th>
        <th>Ingresso Permanente</th>
        <th>Foto</th>
        <th>CPF</th> 
        <th>Documento</th>
        <th>Telefone</th>
        <th>Cargo</th>
        <?php if (($_SESSION['CATEGORIA_ID'] ?? null) != 0) { ?>
        <th>Ações</th>
        <?php } ?>
      </tr>
    </thead>
    <tbody>

<?php
$empresa_id = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0;
$ingresso_permanente = isset($_POST['ingresso']) ? trim($_POST['ingresso']) : '';
$cpf = isset($_POST['cpf']) ? trim($_POST['cpf']) : '';
$telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
$documento = isset($_POST['documento']) ? trim($_POST['documento']) : '';

if (($_SESSION['CATEGORIA_ID'] ?? null) == 3) {
    $empresaUsuario = intval($_SESSION['EMPRESA_ID']);
}
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $categoria = $_SESSION['CATEGORIA_ID'] ?? 0;

    if (!in_array($categoria, [1,3])) {
        die("Sem permissão");
    }

    if ($categoria == 3) {
        $empresaUsuario = intval($_SESSION['EMPRESA_ID']);

        $check = $conexao->query("SELECT EMPRESA_ID FROM pessoas WHERE ID = $id");
        $dados = $check->fetch_assoc();

        if (!$dados || $dados['EMPRESA_ID'] != $empresaUsuario) {
            die("Você não pode excluir essa pessoa");
        }
    }
    $buscaPessoa = $conexao->query("
    SELECT * FROM PESSOAS
    WHERE ID = $id
");

$pessoa = $buscaPessoa->fetch_assoc();

if ($pessoa) {

    $dadosLog =
        "ID=" . $pessoa['ID'] . "," .
        "EMPRESA_ID=" . $pessoa['EMPRESA_ID'] . "," .
        "NOME=" . $pessoa['NOME'] . "," .
        "INGRESSO_PERMANENTE=" . $pessoa['INGRESSO_PERMANENTE'] . "," .
        "FOTO=" . $pessoa['FOTO'] . "," .
        "CPF=" . $pessoa['CPF'] . "," .
        "DOCUMENTO=" . $pessoa['DOCUMENTO'] . "," .
        "TELEFONE=" . $pessoa['TELEFONE'] . "," .
        "CARGO_ID=" . $pessoa['CARGO_ID'];

    registrarLog(
        'EXCLUSAO',
        'PESSOAS',
        $dadosLog
    );
}
    $conexao->query("DELETE FROM PESSOAS WHERE ID = $id");

    echo "<script>window.location.href='pag1.php?pagina=PESSOAS';</script>";
    exit;
}
$filtro = " WHERE 1=1 ";

if (($_SESSION['CATEGORIA_ID'] ?? null) == 3) {
    $empresaUsuario = intval($_SESSION['EMPRESA_ID']);
    $filtro .= " AND p.EMPRESA_ID = $empresaUsuario";
}

if ($empresa_id > 0) {
    $filtro .= " AND p.EMPRESA_ID = $empresa_id";
}

if (!empty($nome)) {
    $filtro .= " AND LOWER(p.NOME) LIKE LOWER('%$nome%')";
}

if (!empty($ingresso_permanente)) {
    $filtro .= " AND p.INGRESSO_PERMANENTE LIKE '%$ingresso_permanente%'";
}

if (!empty($cpf)) {
    $filtro .= " AND p.CPF LIKE '%$cpf%'";
}

if (!empty($telefone)) {
    $filtro .= " AND p.TELEFONE LIKE '%$telefone%'";
}

if (!empty($documento)) {
    $filtro .= " AND p.DOCUMENTO LIKE '%$documento%'";
}
$result = $conexao->query($sql);
if ($result) {
    if ($total > 0) {
        $i = 1;
    while ($linha = $result->fetch_assoc()) {
echo '<tr>';
            if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) {
            echo '<td>'.$linha['ID'].'</td>';
}
            echo '<td>' . htmlspecialchars($linha['NOME_FANTASIA'] ?? 'Sem empresa') . '</td>';
            echo '<td>' . htmlspecialchars($linha['NOME']) . '</td>';
            echo '<td>' . ($linha['INGRESSO_PERMANENTE'] == 'S' ? 'Sim' : 'Não') . '</td>';
            echo '<td>';
            if (!empty($linha['FOTO'])) {
            echo '<img src="img/' . htmlspecialchars($linha['FOTO']) . '" class="img" >';}
            echo '</td>';
            echo '<td>' . htmlspecialchars($linha['CPF']) . '</td>';
            echo '<td>' . htmlspecialchars($linha['DOCUMENTO']) . '</td>';
            echo '<td>' . htmlspecialchars($linha['TELEFONE']) . '</td>';
          $cargo = $linha['CARGO_NOME'] ?? null;
echo '<td>';
if (!empty($cargo)) {
    echo htmlspecialchars($cargo);
} else {
    echo '<span class="text-muted">Sem cargo</span>';
}
echo '</td>';
if (($_SESSION['CATEGORIA_ID'] ?? null) != 0) {

    $categoria = $_SESSION['CATEGORIA_ID'] ?? 0;

    echo '<td class="col-acoes">';
    echo '<a href="gerar_cracha.php?id=' . $linha['ID'] . '" 
        class="btn btn-dark btn-sm" 
        target="_blank">
        Crachá
      </a>';

    if (in_array($categoria, [1,3])) {

        echo '<a href="pag1.php?pagina=ALTERARPE&id=' . $linha['ID'] . '" 
                  class="btn btn-success btn-sm">
                  Alterar
              </a>';

        echo '<a href="pag1.php?pagina=PESSOAS&delete=' . $linha['ID'] . '" 
                  class="btn btn-danger btn-sm"
                  onclick="return confirm(\'Deseja excluir esta pessoa?\')">
                  Excluir
              </a>';

    } elseif ($categoria == 2) {

        echo '<button class="btn btn-success btn-sm" disabled>
                Alterar
              </button>';

        echo '<button class="btn btn-danger btn-sm" disabled>
                Excluir
              </button>';
    }

    echo '</td>';
}
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="11" class="text-center text-muted">Nenhum cadastro registrado.</td></tr>';
    }
} else {
    echo '<tr><td colspan="11" class="text-danger">Erro: ' . $conexao->error . '</td></tr>';
}
$conexao->close();
?>
</body>
</table>
<style>
.pagination-custom {
    margin-top: 20px;
    margin-bottom: 20px;
    text-align: center;
}

.pagination-custom a,
.pagination-custom .active-page {
    display: inline-block;
    min-width: 38px;
    height: 38px;
    line-height: 24px;
    margin: 0 1px;
    border-radius: 6px;
    font-weight: 500;
    text-decoration: none;
}

.pagination-custom .active-page {
    background: #343a40;
    color: #fff;
    border: 1px solid #343a40;
    box-shadow: 0 2px 5px rgba(0,0,0,.15);
}

.pagination-custom a:hover {
    transform: none;
    opacity: 0.9;
}

.pagination-custom .dots {
    display: inline-block;
    padding: 6px;
    font-weight: bold;
}
</style>

<div class="text-center mt-2 pagination-custom">

<?php
$max_links = 2;

if ($pagina > 1) {
    echo '<a class="btn btn-dark btn-sm"
            href="?pagina=PESSOAS&pagina_num=' . ($pagina - 1) . '&nome=' . urlencode($nome) . '&empresa_id=' . $empresa_id . '">
            «
          </a>';
} else {
    echo '<span class="btn btn-secondary btn-sm disabled">«</span>';
}

if ($pagina > ($max_links + 1)) {
    echo '<a class="btn btn-outline-dark btn-sm"
            href="?pagina=PESSOAS&pagina_num=1&nome=' . urlencode($nome) . '&empresa_id=' . $empresa_id . '">1</a>';
    echo '<span class="dots">...</span>';
}

for ($i = $pagina - $max_links; $i <= $pagina + $max_links; $i++) {

    if ($i < 1 || $i > $total_paginas) continue;

    if ($i == $pagina) {
        echo '<span class="active-page">' . $i . '</span>';
    } else {
        echo '<a class="btn btn-outline-dark btn-sm"
                href="?pagina=PESSOAS&pagina_num=' . $i . '&nome=' . urlencode($nome) . '&empresa_id=' . $empresa_id . '">'
                . $i .
             '</a>';
    }
}

if ($pagina < $total_paginas - $max_links) {
    echo '<span class="dots">...</span>';
    echo '<a class="btn btn-outline-dark btn-sm"
            href="?pagina=PESSOAS&pagina_num=' . $total_paginas . '&nome=' . urlencode($nome) . '&empresa_id=' . $empresa_id . '">'
            . $total_paginas .
         '</a>';
}

if ($pagina < $total_paginas) {
    echo '<a class="btn btn-dark btn-sm"
            href="?pagina=PESSOAS&pagina_num=' . ($pagina + 1) . '&nome=' . urlencode($nome) . '&empresa_id=' . $empresa_id . '">
            »
          </a>';
} else {
    echo '<span class="btn btn-secondary btn-sm disabled">»</span>';
}
?>

<br><br>

<p class="text-muted mt-2">
    Página <?= $pagina ?> de <?= $total_paginas ?>
</p>


</div>
<?php if (isset($total)) echo "<p><strong>Total de Pessoas:</strong> $total</p>"; ?>
</div>


</html>
