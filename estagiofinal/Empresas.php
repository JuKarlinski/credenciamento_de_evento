<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>EMPRESAS</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
  <script src="js/empresas.js"></script>
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

<div class="container mt-4 fundo-tabela">
  <h2>EMPRESAS</h2>  
    <?php if (isset($_SESSION['CATEGORIA_ID'])): ?>
   <?php if ($_SESSION['CATEGORIA_ID'] == 1): ?>
    <button type="button"
            onclick="window.location.href='?pagina=CADASTRO'"
            class="btn btn-dark">
        Nova Empresa
    </button>

<?php elseif ($_SESSION['CATEGORIA_ID'] == 2): ?>
    <button type="button"
            class="btn btn-dark"
            disabled>
        Nova Empresa
    </button>
<?php endif; ?>
<?php endif; ?>
<?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
    <a href="pag1.php?pagina=IMPORTAR_EMPRESAS" class="btn btn-dark">
    Importar
</a>
<?php } ?>
<?php
$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';
?>
<a href="relatorios_empresas.php?nome=<?php echo urlencode($nome); ?>"
   class="btn btn-dark"
   target="_blank"
   rel="noopener noreferrer">
   Relatório
</a>
  <?php if (($_SESSION['CATEGORIA_ID'] ?? null) != 3) { ?>
<form method="GET" action="">
    <input type="hidden" name="pagina" value="EMPRESAS">
   <div class="row align-items-center">
  <div class="col-md-6 mb-3">
    <br><input type="text"
       class="form-control"
       id="myInput"
       placeholder="Pesquisar empresa..."
       onkeyup="myFunction()"></br>
  </div>
  <div class="col-md-3 mb-3 d-flex align-items-center">
  <button type="submit" class="btn btn-dark mr-3">
    <i class="bi bi-search"></i>
  </button>
  <a href="pag1.php?pagina=EMPRESAS" class="btn btn-dark px-4">
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
$sql_total = "SELECT COUNT(*) as total FROM EMPRESAS e LEFT JOIN TIPOS t ON e.TIPO_ID = t.ID WHERE 1=1";

if (isset($_SESSION['CATEGORIA_ID']) && $_SESSION['CATEGORIA_ID'] == 3) {
    $empresa_id = $_SESSION['EMPRESA_ID'];
    $sql_total .= " AND e.ID = $empresa_id";
}

if (!empty($nome)) {
    $nome = $conexao->real_escape_string($nome);
    $sql_total .= " AND (
        e.NOME_FANTASIA LIKE '%$nome%'
        OR e.RAZAO_SOCIAL LIKE '%$nome%'
        OR e.CNPJ LIKE '%$nome%'
    )";
}

$total_result = $conexao->query($sql_total);
$total_row = $total_result->fetch_assoc();
$total_registros = $total_row['total'];

$limite = 10;
$total_paginas = ceil($total_registros / $limite);

$pagina = isset($_GET['pagina_num']) ? (int)$_GET['pagina_num'] : 1;
if ($pagina < 1) $pagina = 1;

$offset = ($pagina - 1) * $limite;
include_once("logs.php");

$tipos = $conexao->query("SELECT * FROM TIPOS");
$empresa_id = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0;
$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';
$ano = isset($_POST['ano']) ? trim($_POST['ano']) : '';
$razao_social = isset($_POST['razao_social']) ? trim($_POST['razao_social']) : '';
$cnpj = isset($_POST['cnpj']) ? trim($_POST['cnpj']) : '';

$sql = "
SELECT
    e.ID,
    e.ANO,
    e.NOME_FANTASIA,
    e.RAZAO_SOCIAL,
    e.CNPJ,
    e.QUANTIDADE_ESPACOS,
    t.NOME AS TIPO
FROM EMPRESAS e
LEFT JOIN TIPOS t ON e.TIPO_ID = t.ID
WHERE 1=1
";

if (isset($_SESSION['CATEGORIA_ID']) && $_SESSION['CATEGORIA_ID'] == 3) {
    $empresa_id = $_SESSION['EMPRESA_ID'];
    $sql .= " AND e.ID = $empresa_id";
}

if (!empty($nome)) {
    $nome = $conexao->real_escape_string($nome);
    $sql .= " AND (
        e.NOME_FANTASIA LIKE '%$nome%'
        OR e.RAZAO_SOCIAL LIKE '%$nome%'
        OR e.CNPJ LIKE '%$nome%'
    )";
}

$sql .= " ORDER BY e.NOME_FANTASIA ASC LIMIT $limite OFFSET $offset";

//$sql .= " ORDER BY e.ID DESC";
$total = $total_registros;
?>

<table id="myTable" class="table table-striped align-middle">
<thead>
<tr>
    <th colspan="12" class="text-left bg-light">
        Total de Empresas: <?php echo $total; ?>
    </th>
</tr>
<tr>
  <?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
  <th>#</th>
  <?php } ?> 
  <th>Empresa</th>
  <th>Tipo</th>
  <th>Ano</th>
  <th>CNPJ</th>
  <th>Razão Social</th>
  <th>Espaços</th>
  <th>Ações</th>
</tr>
</thead>
    <tbody>

<?php
if (isset($_GET['delete'])) {
    if (!isset($_SESSION['CATEGORIA_ID']) || $_SESSION['CATEGORIA_ID'] != 1) {
        die("Acesso negado");
    }
    $id = intval($_GET['delete']);
    $buscaEmpresa = $conexao->query("
    SELECT * FROM EMPRESAS
    WHERE ID = $id
");

$empresa = $buscaEmpresa->fetch_assoc();

$dadosLog =
    "ID=" . $empresa['ID'] . "," .
    "NOME=" . $empresa['NOME_FANTASIA'] . "," .
    "ANO=" . $empresa['ANO'] . "," .
    "CNPJ=" . $empresa['CNPJ'] . "," .
    "RAZAO_SOCIAL=" . $empresa['RAZAO_SOCIAL'] . "," .
    "ESPACOS=" . $empresa['QUANTIDADE_ESPACOS'];

  registrarLog(
    'EXCLUSAO',
    'EMPRESAS',
    $dadosLog
);
    $conexao->query("DELETE FROM EMPRESAS WHERE ID = $id");
    echo "<script>window.location.href='pag1.php?pagina=EMPRESAS';</script>";
exit;
}
$result = $conexao->query($sql);

if ($result && $result->num_rows > 0) {

    while ($linha = $result->fetch_assoc()) {

        echo "<tr>";

        if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) {
            echo "<td>{$linha['ID']}</td>";
        }

        echo "<td>{$linha['NOME_FANTASIA']}</td>";
        echo "<td>{$linha['TIPO']}</td>";
        echo "<td>{$linha['ANO']}</td>";
        echo "<td>{$linha['CNPJ']}</td>";
        echo "<td>{$linha['RAZAO_SOCIAL']}</td>";
        echo "<td>{$linha['QUANTIDADE_ESPACOS']}</td>";

       echo '<td class="col-acoes">';

echo '<a href="?pagina=PESSOAS&empresa_id=' . $linha['ID'] . '" 
          class="btn btn-dark btn-sm">
          Pessoas
      </a>';
if (in_array($_SESSION['CATEGORIA_ID'] ?? 0, [1,2,3])) {
    echo '<a href="pag1.php?pagina=IMPORTAR_PESSOAS&empresa_id=' . $linha['ID'] . '"
              class="btn btn-primary btn-sm">
              Importar Pessoa
          </a>';
}
echo '<a href="pag1.php?pagina=USUÁRIOS&empresa_id=' . $linha['ID'] . '" 
          class="btn btn-info btn-sm">
          Usuários
      </a>';
echo '<a href="pag1.php?pagina=CARGOS&empresa_id=' . $linha['ID'] . '" 
          class="btn btn-secondary btn-sm">
          Cargos
      </a>';

$cat = $_SESSION['CATEGORIA_ID'] ?? 0;


if ($cat == 1) {
    echo '<a href="pag1.php?pagina=ALTERAR&id=' . $linha['ID'] . '" class="btn btn-success btn-sm">Alterar</a>';

    echo '<a href="pag1.php?pagina=EMPRESAS&delete=' . $linha['ID'] . '" class="btn btn-danger btn-sm"
            onclick="return confirm(\'Deseja excluir esta empresa?\')">
            Excluir
          </a>';

} elseif ($cat == 2) {

echo '<span class="btn btn-success btn-sm" style="opacity:0.5; cursor:not-allowed;">Alterar</span>';
echo '<span class="btn btn-danger btn-sm" style="opacity:0.5; cursor:not-allowed;">Excluir</span>';

}

echo '</td>';
echo '</td>';
        echo "</tr>";
    }

} else {

    echo '<tr>
            <td colspan="12" class="text-center text-muted">
                Nenhum cadastro registrado.
            </td>
          </tr>';
}
$conexao->close();
?>
</form>
</div>
</div>

<script>
let tipo = document.getElementById('tipo');

if (tipo) {

    tipo.addEventListener('change', function () {

        let selecionado = this.options[this.selectedIndex];
        let controla = selecionado.getAttribute('data-controla');

        if (controla === 'S') {
            document.getElementById('campoEspacos').style.display = 'block';
        } else {
            document.getElementById('campoEspacos').style.display = 'none';
        }

    });

}
</script>
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
            href="?pagina=EMPRESAS&pagina_num=' . ($pagina - 1) . '&nome=' . urlencode($nome) . '">
            «
          </a>';

} else {

    echo '<span class="btn btn-secondary btn-sm disabled">
            «
          </span>';

}

if ($pagina > ($max_links + 1)) {
    echo '<a class="btn btn-outline-dark btn-sm"
            href="?pagina=EMPRESAS&pagina_num=1&nome=' . urlencode($nome) . '">1</a>';
    echo '<span class="dots">...</span>';
}

for ($i = $pagina - $max_links; $i <= $pagina + $max_links; $i++) {

    if ($i < 1 || $i > $total_paginas) continue;

    if ($i == $pagina) {
        echo '<span class="active-page">' . $i . '</span>';
    } else {
        echo '<a class="btn btn-outline-dark btn-sm"
                href="?pagina=EMPRESAS&pagina_num=' . $i . '&nome=' . urlencode($nome) . '">'
                . $i .
             '</a>';
    }
}

if ($pagina < $total_paginas - $max_links) {
    echo '<span class="dots">...</span>';
    echo '<a class="btn btn-outline-dark btn-sm"
            href="?pagina=EMPRESAS&pagina_num=' . $total_paginas . '&nome=' . urlencode($nome) . '">'
            . $total_paginas .
         '</a>';
}

if ($pagina < $total_paginas) {

    echo '<a class="btn btn-dark btn-sm"
            href="?pagina=EMPRESAS&pagina_num=' . ($pagina + 1) . '&nome=' . urlencode($nome) . '">
            »
          </a>';

} else {

    echo '<span class="btn btn-secondary btn-sm disabled">
            »
          </span>';

}
?>

<br><br>

<p class="text-muted mt-2">
    Página <?= $pagina ?> de <?= $total_paginas ?>
</p>
</div>
 <th colspan="12" class="text-left bg-light">
<?php if (isset($total))   echo "<p><strong>Total de Empresas:</strong> $total</p>"; ?>
    </th>
</html>