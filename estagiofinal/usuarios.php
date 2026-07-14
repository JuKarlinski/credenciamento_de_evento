<?php
include_once("conexao.php");
include_once("logs.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$empresa_id = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0;
$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';

if ($empresa_id > 0) {
    $nome = ''; 
}

if ($empresa_id > 0 && !isset($_GET['nome'])) {
    $nome = '';
}
$categoria = isset($_SESSION['CATEGORIA_ID']) ? intval($_SESSION['CATEGORIA_ID']) : 0;


$pagina = isset($_GET['pagina_num']) ? intval($_GET['pagina_num']) : 1;
$pagina = max(1, $pagina);

$por_pagina = 10;
$inicio = ($pagina - 1) * $por_pagina;
if ($empresa_id > 0) {
    $buscaEmpresa = $conexao->query("
        SELECT NOME_FANTASIA
        FROM empresas
        WHERE ID = $empresa_id
    ");

    if ($buscaEmpresa && $buscaEmpresa->num_rows > 0) {
        $empresa = $buscaEmpresa->fetch_assoc();
        $nome_empresa = $empresa['NOME_FANTASIA'];
    }
    
}

if (isset($_GET['excluir'])) {
    if (!isset($_SESSION['CATEGORIA_ID']) || (int)$_SESSION['CATEGORIA_ID'] !== 1) {
    die("Acesso negado");
}
    $id = intval($_GET['excluir']);
$buscaUsuario = $conexao->query("
    SELECT * FROM usuarios
    WHERE ID = $id
");
$usuario = $buscaUsuario->fetch_assoc();

if ($usuario) {

    $dadosLog =
        "ID=".$usuario['ID'].
        ",NOME=".$usuario['NOME'].
        ",EMAIL=".$usuario['EMAIL'].
        ",CATEGORIA_ID=".$usuario['CATEGORIA_ID'].
        ",EMPRESA_ID=".$usuario['EMPRESA_ID'];

    registrarLog(
        'EXCLUSAO',
        'usuarios',
        $dadosLog
    );
}
$conexao->query("DELETE FROM usuarios WHERE ID = $id");

header("Location: pag1.php?pagina=usuarios&empresa_id=$empresa_id");
exit;
}

$sql = "
SELECT usuarios.*, 
       categorias.nome as categoria_nome, 
       e.NOME_FANTASIA as nome_empresa
FROM usuarios
LEFT JOIN categorias ON usuarios.CATEGORIA_ID = categorias.id
LEFT JOIN empresas e ON usuarios.EMPRESA_ID = e.ID
WHERE 1=1
";

if ($empresa_id > 0) {
    $sql .= " AND usuarios.EMPRESA_ID = $empresa_id";
} else {

    if (!empty($_SESSION['EMPRESA_ID'])) {
        $sql .= " AND usuarios.EMPRESA_ID = " . intval($_SESSION['EMPRESA_ID']);
    }

    if ($categoria == 2) {
        $sql .= " AND usuarios.CATEGORIA_ID = 3";
    }
}

if (!empty($nome)) {
    $nome = $conexao->real_escape_string($nome);

    $sql .= " AND (
        usuarios.NOME LIKE '%$nome%'
        OR usuarios.EMAIL LIKE '%$nome%'
    )";
}

$sql_total = $sql;
$res_total = $conexao->query($sql_total);
$total_registros = ($res_total) ? $res_total->num_rows : 0;

$total_paginas = ($por_pagina > 0) ? ceil($total_registros / $por_pagina) : 1;

$sql .= " ORDER BY usuarios.NOME ASC LIMIT $inicio, $por_pagina";

$usuarios = $conexao->query($sql);

if (!$usuarios) {
    die("Erro SQL: " . $conexao->error);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Usuários</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="js/usuarios.js"></script>
<style>
.fundo-tabela {
  background-image: url('img/fundologin.png');
  background-size: cover;
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
</head>

<body>
<div class="container mt-4 fundo-tabela">
  <h2>
    USUÁRIOS
    <?php if (!empty($nome_empresa)) { ?>
        - <?= htmlspecialchars($nome_empresa) ?>
    <?php } ?>
</h2>
<?php if (($_SESSION['CATEGORIA_ID'] ?? null) != 3) { ?>

    <?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
        <a href="pag1.php?pagina=novousuario&empresa_id=<?= $empresa_id ?>" class="btn btn-dark">
            Novo Usuário
        </a>
    <?php } else { ?>
        <button class="btn btn-dark" disabled>
            Novo Usuário
        </button>
    <?php } ?>
<a href="relatorios_usuarios.php?nome=<?= urlencode($nome) ?>&empresa_id=<?= $empresa_id ?>"
   class="btn btn-dark"
   target="_blank"
   rel="noopener noreferrer">
    Relatório
</a>

<form method="GET" action="pag1.php">
        <input type="hidden" name="pagina" value="usuarios">
        <input type="hidden" name="empresa_id" value="<?= $empresa_id ?>">

   <div class="row align-items-center">
  <div class="col-md-6 mb-3">
    <br><input type="text"
class="form-control"
id="myInput"
placeholder="Pesquisar usuário..."
onkeyup="myFunction()"></br>
  </div>
  <div class="col-md-3 mb-3 d-flex align-items-center">
  <button type="submit" class="btn btn-dark mr-3">
    <i class="bi bi-search"></i>
  </button>
<a href="pag1.php?pagina=usuarios&empresa_id=<?= $empresa_id ?>" class="btn btn-dark px-4">
    Limpar
</a>
</div>
</div>
   <br>
</form>
<?php } ?>
</div>
<div class="container mt-4">

  <table id="myTable" class="table table-striped">
    <thead>
        <tr>
    <th colspan="12" class="text-left bg-light">
        Total de Usuários: <?php echo $total_registros; ?>
    </th>
</tr>
      <tr>
        <th>Nome</th>
        <th>Login</th>
        <?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
        <th>Categoria</th>
<?php } ?>
        <?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
    <th>Empresa</th>
<?php } ?>

<?php if (($_SESSION['CATEGORIA_ID'] ?? null) != 3) { ?>
    <th>Ações</th>
<?php } ?>
      </tr>
    </thead>
    <tbody>
  <?php if ($usuarios && $usuarios->num_rows > 0) { ?>
    <?php while($u = $usuarios->fetch_assoc()) { ?>
      <tr>
        <td><?= htmlspecialchars($u['NOME']) ?></td>
        <td><?= htmlspecialchars($u['EMAIL']) ?></td>
        <?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
        <td><?= htmlspecialchars($u['categoria_nome']) ?></td>
        <?php } ?>
        <?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
    <td><?= htmlspecialchars($u['nome_empresa']) ?></td>
<?php } ?>

<?php if (($_SESSION['CATEGORIA_ID'] ?? null) != 3) { ?>
<td>

<?php 
if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
    <a href="pag1.php?pagina=editar&id=<?= $u['ID'] ?>" class="btn btn-success btn-sm">
        Alterar
    </a>
    <a href="pag1.php?pagina=usuarios&excluir=<?= $u['ID'] ?>&empresa_id=<?= $empresa_id ?>"
       class="btn btn-danger btn-sm"
       onclick="return confirm('Deseja excluir este usuário?')">
        Excluir
    </a>

<?php 
} elseif (($_SESSION['CATEGORIA_ID'] ?? null) == 2) { ?>

    <button class="btn btn-success btn-sm" disabled 
        style="opacity: 0.5; cursor: not-allowed;" 
        title="Sem permissão">
    Editar
</button>
<button class="btn btn-danger btn-sm" disabled 
        style="opacity: 0.5; cursor: not-allowed;" 
        title="Sem permissão">
    Excluir
</button>
<?php } ?>

</td>

<?php } ?>
<?php }  ?>
<?php } else { ?>
    <tr>
      <td colspan="10">Nenhum usuário encontrado.</td>
    </tr>
  <?php } ?>
</tbody>
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
           href="?pagina=usuarios&pagina_num=' . ($pagina - 1) . '&nome=' . urlencode($nome) . '&empresa_id=' . $empresa_id . '">
            «
          </a>';

} else {

    echo '<span class="btn btn-secondary btn-sm disabled">
            «
          </span>';

}

if ($pagina < $total_paginas - $max_links) {
    echo '<span class="dots">...</span>';
    echo '<a class="btn btn-outline-dark btn-sm"
          href="?pagina=usuarios&pagina_num=' . $total_paginas . '&nome=' . urlencode($nome) . '&empresa_id=' . $empresa_id . '">'
          . $total_paginas .
         '</a>';
}

for ($i = $pagina - $max_links; $i <= $pagina + $max_links; $i++) {

    if ($i < 1 || $i > $total_paginas) continue;

    if ($i == $pagina) {
        echo '<span class="active-page">' . $i . '</span>';
    } else {
        echo '<a class="btn btn-outline-dark btn-sm"
                href="?pagina=usuarios&pagina_num=' . $i . '&nome=' . urlencode($nome) . '&empresa_id=' . $empresa_id . '">'
                . $i .
             '</a>';
    }
}

if ($pagina < $total_paginas - $max_links) {
    echo '<span class="dots">...</span>';
    echo '<a class="btn btn-outline-dark btn-sm"
          href="?pagina=usuarios&pagina_num=1&nome=' . urlencode($nome) . '&empresa_id=' . $empresa_id . '">'
            . $total_paginas .
         '</a>';
}

if ($pagina < $total_paginas) {

  echo '<a class="btn btn-dark btn-sm"
        href="?pagina=usuarios&pagina_num=' . $total_paginas . '&nome=' . urlencode($nome) . '&empresa_id=' . $empresa_id . '">
            »
          </a>';

} else {

    echo '<span class="btn btn-secondary btn-sm disabled">
            »
          </span>';

}
?>
<p class="text-muted mt-2">
    Página <?= $pagina ?> de <?= $total_paginas ?>
</p>
</div>
<p>
    <strong>Total de Usuários:</strong> <?= $total_registros ?>
</p>
</div>
</body>
</html>