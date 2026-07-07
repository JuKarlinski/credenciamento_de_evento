<?php
include_once("conexao.php");
include_once("logs.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {

    session_start();
    header('Content-Type: application/json');

    $nome = $conexao->real_escape_string(trim($_POST['nome']));
    $id_empresa = intval($_POST['id_empresa']);

    if ($nome == '' || $id_empresa == 0) {
        echo json_encode(["sucesso" => false]);
        exit;
    }

    $sql = "INSERT INTO CARGOS (NOME, ID_EMPRESA)
            VALUES ('$nome', '$id_empresa')";

    if ($conexao->query($sql)) {

        echo json_encode([
            "sucesso" => true,
            "id" => $conexao->insert_id,
            "nome" => $nome,
            "empresa" => $_POST['empresa_texto']
        ]);

    } else {
        echo json_encode(["sucesso" => false]);
    }

    exit;
}
$empresa_id = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0;
$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';

if ($empresa_id > 0) {
    $nome = ''; 
}
$nome_empresa = '';

if ($empresa_id > 0) {
    $res = $conexao->query("
        SELECT NOME_FANTASIA 
        FROM EMPRESAS 
        WHERE ID = $empresa_id
    ");

    if ($res && $res->num_rows > 0) {
        $nome_empresa = $res->fetch_assoc()['NOME_FANTASIA'];
    }
}
$editar = false;
$dados_editar = null;
$erro_empresa = '';

if (isset($_GET['editar'])) {
    $editar = true;
    $id_editar = intval($_GET['editar']);

    $sql_editar = "SELECT * FROM CARGOS WHERE ID = $id_editar";
    $result_editar = $conexao->query($sql_editar);
    $dados_editar = $result_editar->fetch_assoc();
}

$veio_empresa = isset($_GET['empresa_id']) ? true : false;

$pagina = isset($_GET['pagina_num']) ? intval($_GET['pagina_num']) : 1;
$pagina = max(1, $pagina);

$por_pagina = 10;
$inicio = ($pagina - 1) * $por_pagina;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['alterar'])) {

        $id = intval($_POST['id']);
        $nome = $conexao->real_escape_string(trim($_POST['nome']));

        $buscaAntigo = $conexao->query("SELECT * FROM CARGOS WHERE ID = $id");
        $antigo = $buscaAntigo->fetch_assoc();

        if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) {

            $id_empresa = intval($_POST['id_empresa']);

            if (empty($id_empresa)) {
                die("Selecione uma empresa.");
            }

            $sql = "
                UPDATE CARGOS
                SET NOME = '$nome',
                    ID_EMPRESA = '$id_empresa'
                WHERE ID = $id
            ";

            $empresa_log = $id_empresa;

        } else {

            $sql = "
                UPDATE CARGOS
                SET NOME = '$nome'
                WHERE ID = $id
            ";

            $empresa_log = $antigo['ID_EMPRESA'];
        }

        $conexao->query($sql);

        $dadosAntigos =
            "ID={$antigo['ID']},NOME={$antigo['NOME']},ID_EMPRESA={$antigo['ID_EMPRESA']}";

        $dadosNovos =
            "ID=$id,NOME=$nome,ID_EMPRESA=$empresa_log";

        registrarLog('ALTERACAO', 'CARGOS', $dadosAntigos, $dadosNovos);

        header("Location: pag1.php?pagina=CARGOS&empresa_id=$empresa_id");
        exit;
    }

    if (($_SESSION['CATEGORIA_ID'] ?? null) == 2) {
        die("Acesso negado");
    }


    $nome = $conexao->real_escape_string(trim($_POST['nome']));

    if (($_SESSION['CATEGORIA_ID'] ?? null) != 3) {

        $id_empresa = isset($_POST['id_empresa']) ? intval($_POST['id_empresa']) : 0;

        if (empty($id_empresa)) {
            $erro_empresa = "Selecione uma empresa.";
        }

    } else {
        $id_empresa = $_SESSION['EMPRESA_ID'];
    }

    if (empty($erro_empresa) && !empty($id_empresa)) {

        $sql = "INSERT INTO CARGOS (NOME, ID_EMPRESA)
                VALUES ('$nome', '$id_empresa')";

        if ($conexao->query($sql)) {

    $id_cargo = $conexao->insert_id;

    $dadoslog =
        "ID=$id_cargo,NOME=$nome,ID_EMPRESA=$id_empresa";

    registrarLog('INCLUSAO', 'CARGOS', $dadoslog);
$empresa_id = $id_empresa;
}
    }
}

if (isset($_GET['id'])) {

    if (($_SESSION['CATEGORIA_ID'] ?? null) == 2) {
        die("Acesso negado");
    }

    $id = intval($_GET['id']);

    $buscaCargo = $conexao->query("SELECT * FROM CARGOS WHERE ID = $id");
    $cargo = $buscaCargo->fetch_assoc();

    if ($cargo) {

        $dadoslog =
            "ID={$cargo['ID']},NOME={$cargo['NOME']},ID_EMPRESA={$cargo['ID_EMPRESA']}";

        registrarLog('EXCLUSAO', 'CARGOS', $dadoslog);

        if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) {
            $conexao->query("DELETE FROM CARGOS WHERE ID = $id");
        } else {
            $empresa = $_SESSION['EMPRESA_ID'];
            $conexao->query("DELETE FROM CARGOS WHERE ID = $id AND ID_EMPRESA = $empresa");
        }
    }

   header("Location: pag1.php?pagina=CARGOS&empresa_id=$empresa_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>CARGOS</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
<script src="js/cargos.js"></script>
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
</head>

<body>
    <div class="container mt-4 fundo-tabela">

<h2>
    CARGOS
    <?php if ($empresa_id > 0 && !empty($nome_empresa)) { ?>
        - <?= htmlspecialchars($nome_empresa) ?>
    <?php } ?>
</h2>
<form method="POST" id="formCargo">

<input type="hidden" name="empresa_texto" id="empresa_texto">

<div class="form-group">
<label>Nome do Cargo</label>
<input type="text"
       name="nome"
       class="form-control"
       placeholder="Digite o cargo..."
       value="<?= $editar ? htmlspecialchars($dados_editar['NOME']) : '' ?>"
       required>
</div>

<?php if (($_SESSION['CATEGORIA_ID'] ?? null) != 3) { ?>
<div class="form-group">
<label>Empresa</label>

<select name="id_empresa" class="form-control">

<?php if(!$editar){ ?>
<option value="" disabled selected>Escolha a empresa</option>
<?php } ?>

<?php
if ($empresa_id > 0) {
    $empresas = $conexao->query("
        SELECT * FROM EMPRESAS
        WHERE ID = '$empresa_id'
    ");
} else {
    $empresas = $conexao->query("SELECT * FROM EMPRESAS");
}

while($empresa = $empresas->fetch_assoc()){

$selected = '';

if($editar && intval($dados_editar['ID_EMPRESA']) == intval($empresa['ID'])){
    $selected = 'selected';
}

echo '
<option value="'.$empresa['ID'].'" '.$selected.'>
    '.$empresa['NOME_FANTASIA'].'
</option>';
}
?>

</select>

<?php if(!empty($erro_empresa)){ ?>
<small class="text-danger"><?= $erro_empresa ?></small>
<?php } ?>
</div>
<?php } ?>
<?php if (($_SESSION['CATEGORIA_ID'] ?? null) != 2) { ?>
<?php if($editar){ ?>
<input type="hidden" name="alterar" value="1">
<input type="hidden" name="id" value="<?= $dados_editar['ID'] ?>">
<button class="btn btn-success btn-block">Salvar Alterações</button>
<a href="pag1.php?pagina=CARGOS" class="btn btn-secondary btn-block">Cancelar</a>
<?php } else { ?>
<button class="btn btn-dark btn-block">Cadastrar</button>
<?php } ?>
<?php } else { ?>
<button class="btn btn-dark btn-block" disabled>Sem permissão</button>
<?php } ?>
<a href="relatorios_cargos.php?nome=<?= urlencode($_GET['nome'] ?? '') ?>&empresa_id=<?= $empresa_id ?>"
   class="btn btn-dark btn-block"
   target="_blank"
   rel="noopener noreferrer">
    Relatório
</a>
</form>
</div>
<?php
$categoria = $_SESSION['CATEGORIA_ID'] ?? 0;

$sql = "
SELECT CARGOS.*, EMPRESAS.NOME_FANTASIA AS EMPRESA
FROM CARGOS
INNER JOIN EMPRESAS ON EMPRESAS.ID = CARGOS.ID_EMPRESA
WHERE 1=1
";

if (!empty($nome_busca)) {
    $nome_safe = $conexao->real_escape_string($nome_busca);
    $sql .= " AND CARGOS.NOME LIKE '%$nome_safe%'";
}

if ($categoria == 3) {
    $sql .= " AND CARGOS.ID_EMPRESA = '" . $_SESSION['EMPRESA_ID'] . "'";
}
if ($empresa_id > 0) {
    $sql .= " AND CARGOS.ID_EMPRESA = '$empresa_id'";
}
$sql_total = $sql;
$result_total = $conexao->query($sql_total);
$total_registros = $result_total->num_rows;

$total_paginas = ceil($total_registros / $por_pagina);

$sql .= " ORDER BY CARGOS.ID ASC LIMIT $inicio, $por_pagina";

$result = $conexao->query($sql);
$total = $total_registros;
?>
<form method="GET" action="pag1.php">
        <input type="hidden" name="pagina" value="CARGOS">
        <input type="hidden" name="empresa_id" value="<?= $empresa_id ?>">

   <div class="row align-items-center">
  <div class="col-md-6 mb-3">
    <br><input type="text"
class="form-control"
id="myInput"
placeholder="Pesquisar cargo ou empresa..."
onkeyup="myFunction()"></br>
  </div>
  <div class="col-md-3 mb-3 d-flex align-items-center">
  <button type="submit" class="btn btn-dark mr-3">
    <i class="bi bi-search"></i>
  </button>
<a href="pag1.php?pagina=CARGOS&empresa_id=<?= $empresa_id ?>" class="btn btn-dark px-4">
    Limpar
  </a>
</div>
</div>
</form>
<table id="myTable" class="table table-striped mt-4">
<thead>
    <th colspan="12" class="text-left bg-light">
        Total de Cargos: <?php echo $total; ?>
    </th>
<tr>
<?php if ($categoria == 1) { ?>
<th>#</th>
<?php } ?>
<th>Nome</th>

<?php if ($categoria != 3) { ?>
<th>Empresa</th>
<?php } ?>

<th>Ação</th>
</tr>
</thead>

<tbody id="listaCargos">
<?php while($linha = $result->fetch_assoc()){ ?>
<tr>

<?php if ($categoria == 1) { ?>
<td><?= $linha['ID'] ?></td>
<?php } ?>

<td><?= htmlspecialchars($linha['NOME']) ?></td>

<?php if ($categoria != 3) { ?>
<td><?= htmlspecialchars($linha['EMPRESA']) ?></td>
<?php } ?>

<td>

<?php if ($categoria != 2) { ?>

<a href="pag1.php?pagina=CARGOS&editar=<?= $linha['ID'] ?>&empresa_id=<?= $empresa_id ?>"
  class="btn btn-success btn-sm">Alterar</a>

<a href="pag1.php?pagina=CARGOS&id=<?= $linha['ID'] ?>&empresa_id=<?= $empresa_id ?>"
   class="btn btn-danger btn-sm"
   onclick="return confirm('Deseja excluir?')">
Excluir
</a>

<?php } else { ?>
<button class="btn btn-danger btn-sm" disabled style="opacity:0.5;">Excluir</button>
<?php } ?>
</td>
</tr>
<?php } ?>

</tbody>
<script>
document.getElementById("formCargo").addEventListener("submit", function(e){
    e.preventDefault();

    const empresaSelect = document.querySelector('[name="id_empresa"]');

    document.getElementById("empresa_texto").value =
        empresaSelect.options[empresaSelect.selectedIndex].text;

    const formData = new FormData(this);
    formData.append("ajax", "1");

    fetch(window.location.href, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if (!data.sucesso) {
            alert("Erro ao salvar cargo");
            return;
        }

        const tabela = document.getElementById("listaCargos");

        const linha = `
<tr>
    <?php if ($categoria == 1) { ?>
    <td>${data.id}</td>
    <?php } ?>

    <td>${data.nome}</td>

    <?php if ($categoria != 3) { ?>
    <td>${data.empresa}</td>
    <?php } ?>

    <td>
        <button class="btn btn-success btn-sm" disabled>
            Salvo
        </button>
    </td>
</tr>
`;
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
            href="?pagina=CARGOS&pagina_num=' . ($pagina - 1) . '&nome=' . urlencode($nome) . '">
            «
          </a>';

} else {

    echo '<span class="btn btn-secondary btn-sm disabled">
            «
          </span>';

}

if ($pagina > ($max_links + 1)) {
    echo '<a class="btn btn-outline-dark btn-sm"
            href="?pagina=CARGOS&pagina_num=1&nome=' . urlencode($nome) . '">1</a>';
    echo '<span class="dots">...</span>';
}

for ($i = $pagina - $max_links; $i <= $pagina + $max_links; $i++) {

    if ($i < 1 || $i > $total_paginas) continue;

    if ($i == $pagina) {
        echo '<span class="active-page">' . $i . '</span>';
    } else {
        echo '<a class="btn btn-outline-dark btn-sm"
                href="?pagina=CARGOS&pagina_num=' . $i . '&nome=' . urlencode($nome) . '">'
                . $i .
             '</a>';
    }
}

if ($pagina < $total_paginas - $max_links) {
    echo '<span class="dots">...</span>';
    echo '<a class="btn btn-outline-dark btn-sm"
            href="?pagina=CARGOS&pagina_num=' . $total_paginas . '&nome=' . urlencode($nome) . '">'
            . $total_paginas .
         '</a>';
}

if ($pagina < $total_paginas) {

    echo '<a class="btn btn-dark btn-sm"
            href="?pagina=CARGOS&pagina_num=' . ($pagina + 1) . '&nome=' . urlencode($nome) . '">
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
<p><strong>Total de Cargos:</strong> <?= $total ?></p>
</div>

</html>