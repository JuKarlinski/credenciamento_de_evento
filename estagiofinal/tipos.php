<?php
include_once('conexao.php');
include_once("logs.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';
$editar = false;
$dadosEditar = null;
$erro_empresa = '';

if (isset($_GET['editar']) && !empty($_GET['editar'])) {
    $editar = true;
    $id_editar = intval($_GET['editar']);

    $sql_editar = "SELECT * FROM tipos WHERE ID = $id_editar";
    $result_editar = $conexao->query($sql_editar);
    $dadosEditar = $result_editar->fetch_assoc();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['alterar'])) {

        $id = intval($_POST['id']);
        $nome = trim($_POST['nome']);
        $controla = $_POST['controla_espacos'];
        $limite = intval($_POST['limite_pessoas']);

        $sql = "
            UPDATE tipos
            SET NOME = '$nome',
                CONTROLA_ESPACOS = '$controla',
                LIMITE_PESSOAS = '$limite'
            WHERE ID = $id
        ";

      $buscaAntigo = $conexao->query("
    SELECT * FROM tipos
    WHERE ID = $id
");

$dadosAntigos = $buscaAntigo->fetch_assoc();

$dadosAntigos =
    "ID=" . $dadosAntigos['ID'] . "," .
    "NOME=" . $dadosAntigos['NOME'] . "," .
    "CONTROLA_ESPACOS=" . $dadosAntigos['CONTROLA_ESPACOS'] . "," .
    "LIMITE_PESSOAS=" . $dadosAntigos['LIMITE_PESSOAS'];

$conexao->query($sql);

$dadosNovos =
    "ID=" . $id . "," .
    "NOME=" . $nome . "," .
    "CONTROLA_ESPACOS=" . $controla . "," .
    "LIMITE_PESSOAS=" . $limite;

registrarLog(
    'ALTERACAO',
    'tipos',
    $dadosAntigos,
    $dadosNovos
);

header("Location: pag1.php?pagina=tipos");
exit;
    }
    if (($_SESSION['CATEGORIA_ID'] ?? null) == 2) {
        die("Acesso negado");
    }

    $nome = trim($_POST['nome']);
    $controla = $_POST['controla_espacos'];
    $limite = intval($_POST['limite_pessoas']);
    $sql = "
        INSERT INTO tipos (NOME, CONTROLA_ESPACOS, LIMITE_PESSOAS)
        VALUES ('$nome', '$controla', '$limite')
    ";

   $conexao->query($sql);

$idNovo = $conexao->insert_id;

$dadosLog =
    "ID=" . $idNovo . "," .
    "NOME=" . $nome . "," .
    "CONTROLA_ESPACOS=" . $controla . "," .
    "LIMITE_PESSOAS=" . $limite;

registrarLog(
    'INCLUSAO',
    'tipos',
    $dadosLog
);

header("Location: pag1.php?pagina=tipos");
exit;
}
if (isset($_GET['delete'])) {
    if (($_SESSION['CATEGORIA_ID'] ?? null) != 1) {
        die("Acesso negado");
    }
$id = intval($_GET['delete']);

$buscaTipo = $conexao->query("
    SELECT * FROM tipos
    WHERE ID = $id
");

if ($buscaTipo && $buscaTipo->num_rows > 0) {

    $tipo = $buscaTipo->fetch_assoc();

    $dadosLog =
        "ID=" . $tipo['ID'] . "," .
        "NOME=" . $tipo['NOME'] . "," .
        "CONTROLA_ESPACOS=" . $tipo['CONTROLA_ESPACOS'] . "," .
        "LIMITE_PESSOAS=" . $tipo['LIMITE_PESSOAS'];
        
registrarLog(
    'EXCLUSAO',
    'tipos',
    $dadosLog
);
}

$sql = "DELETE FROM tipos WHERE ID = $id";
$conexao->query($sql);

header("Location: pag1.php?pagina=tipos");
exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TIPOS</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
 <script src="js/tipos.js"></script>
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
  padding: 15px;
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

<h2>TIPOS</h2>
<form method="POST">
    <div class="form-group">
      <label>Nome do tipo</label>
      <input type="text"
             class="form-control"
             name="nome"
             placeholder="Digite o tipo..."
             required
             value="<?= $editar ? $dadosEditar['NOME'] : '' ?>">
    </div>

    <div class="form-group">
      <label>Controla espaços?</label>
      <select class="form-control" name="controla_espacos" required>
        <option value="Nao" <?= ($editar && $dadosEditar['CONTROLA_ESPACOS'] == 'Não') ? 'selected' : '' ?>>
          Não
        </option>
        <option value="Sim" <?= ($editar && $dadosEditar['CONTROLA_ESPACOS'] == 'Sim') ? 'selected' : '' ?>>
          Sim
        </option>
      </select>
    </div>

    <div class="form-group">
      <label>Limite de pessoas</label>
      <input type="number"
             class="form-control"
             name="limite_pessoas"
             placeholder="Digite o limite..."
             required
             value="<?= $editar ? $dadosEditar['LIMITE_PESSOAS'] : '' ?>">
    </div>

    <?php if ($editar) { ?>
        <input type="hidden" name="alterar" value="1">
        <input type="hidden" name="id" value="<?= $dadosEditar['ID'] ?>">

        <button class="btn btn-success btn-block">Salvar Alterações</button>

        <a href="pag1.php?pagina=tipos"
           class="btn btn-secondary btn-block">
           Cancelar
        </a>

    <?php } else { ?>

        <?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
        <button    class="btn btn-dark btn-block">Cadastrar</button>
        <?php } else { ?>
            <button    class="btn btn-dark btn-block" disabled>Sem permissão</button>
        <?php } ?>

    <?php } ?>
<a href="relatorios_tipos.php?nome=<?= urlencode($_GET['nome'] ?? '') ?>"
   class="btn btn-dark btn-block"
   target="_blank"
   rel="noopener noreferrer">
    Relatório
</a>
 </form> 
 </div>
 <br>
<form method="GET" action="">
    <input type="hidden" name="pagina" value="tipos">

    <div class="form-row align-items-end">

        <div class="col-md-6">
           <input type="text"
       class="form-control"
       id="myInput"
       placeholder="Pesquisar tipo..."
       onkeyup="myFunction()">
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-dark">
                Pesquisar
            </button>
        </div>

        <div class="col-auto">
            <a href="pag1.php?pagina=tipos"
               class="btn btn-dark">
                Limpar
            </a>
        </div>

    </div>
</form>
</div>

<div class="container mt-4">

<?php
$sql = "SELECT * FROM tipos WHERE 1=1";
$por_pagina = 10;
$pagina = isset($_GET['pagina_num']) ? intval($_GET['pagina_num']) : 1;
$pagina = max(1, $pagina);

$inicio = ($pagina - 1) * $por_pagina;

if (!empty($nome)) {
$nome_safe = $conexao->real_escape_string($nome);
    $sql .= " AND NOME LIKE '%$nome_safe%'";
}
$sql_total = "SELECT * FROM tipos WHERE 1=1";

if (!empty($nome)) {
    $nome_safe = $conexao->real_escape_string($nome);
    $sql_total .= " AND NOME LIKE '%$nome_safe%'";
}

$res_total = $conexao->query($sql_total);
$total = ($res_total) ? $res_total->num_rows : 0;

$total_paginas = ceil($total / $por_pagina);
$sql .= " ORDER BY ID DESC LIMIT $inicio, $por_pagina";
$result = $conexao->query($sql);
$por_pagina = 10;
$inicio = ($pagina - 1) * $por_pagina;

$total_paginas = ceil($total / $por_pagina);
?>

<table id="myTable" class="table table-striped">
    <br>
    <thead>
      <tr>
    <th colspan="12" class="text-left bg-light">
        Total de Tipos: <?php echo $total; ?>
    </th>
</tr>
      <tr>
        <?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
          <th>#</th>
        <?php } ?>
        <th>Nome</th>
        <th>Controla Espaços</th>
        <th>Limite</th>
        <th>Ações</th>
      </tr>
    </thead>

    <tbody>
      <?php while ($linha = $result->fetch_assoc()) { ?>
        <tr>

          <?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
            <td><?= $linha['ID'] ?></td>
          <?php } ?>

          <td><?= htmlspecialchars($linha['NOME']) ?></td>
          <td><?= htmlspecialchars($linha['CONTROLA_ESPACOS']) ?></td>
          <td><?= htmlspecialchars($linha['LIMITE_PESSOAS']) ?></td>

          <td>

            <?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
              <a href="pag1.php?pagina=tipos&editar=<?= $linha['ID'] ?>"
                class="btn btn-success btn-sm">
                 Alterar
              </a>
              <a href="pag1.php?pagina=tipos&delete=<?= $linha['ID'] ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Deseja excluir este tipo?')">
                 Excluir
              </a>

            <?php } else { ?>

              <button class="btn btn-primary btn-sm" disabled>Alterar</button>
              <button class="btn btn-danger btn-sm" disabled>Excluir</button>

            <?php } ?>

          </td>

        </tr>
      <?php } ?>
    </tbody> 
  </table>
</body>
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
            href="?pagina=tipos&pagina_num=' . ($pagina - 1) . '&nome=' . urlencode($nome) . '">
            «
          </a>';

} else {

    echo '<span class="btn btn-secondary btn-sm disabled">
            «
          </span>';

}

if ($pagina > ($max_links + 1)) {
    echo '<a class="btn btn-outline-dark btn-sm"
            href="?pagina=tipos&pagina_num=1&nome=' . urlencode($nome) . '">1</a>';
    echo '<span class="dots">...</span>';
}

for ($i = $pagina - $max_links; $i <= $pagina + $max_links; $i++) {

    if ($i < 1 || $i > $total_paginas) continue;

    if ($i == $pagina) {
        echo '<span class="active-page">' . $i . '</span>';
    } else {
        echo '<a class="btn btn-outline-dark btn-sm"
                href="?pagina=tipos&pagina_num=' . $i . '&nome=' . urlencode($nome) . '">'
                . $i .
             '</a>';
    }
}

if ($pagina < $total_paginas - $max_links) {
    echo '<span class="dots">...</span>';
    echo '<a class="btn btn-outline-dark btn-sm"
            href="?pagina=tipos&pagina_num=' . $total_paginas . '&nome=' . urlencode($nome) . '">'
            . $total_paginas .
         '</a>';
}

if ($pagina < $total_paginas) {

    echo '<a class="btn btn-dark btn-sm"
            href="?pagina=tipos&pagina_num=' . ($pagina + 1) . '&nome=' . urlencode($nome) . '">
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
    <div class="mt-4">
    <strong>Total de Tipos:</strong> <?= $total ?>
  </div>
  <br>
</html>