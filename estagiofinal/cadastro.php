<?php 
include_once('conexao.php');
include_once("logs.php");

$tipos = $conexao->query("SELECT * FROM TIPOS");
$temTipos = ($tipos && $tipos->num_rows > 0);

if ($_POST) {
    $nome = trim(strip_tags($_POST['nome']));
    $ano = 2026;
    $razao_social = trim(strip_tags($_POST['razao_social']));
    $cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj']);
    $tipo_empresa = isset($_POST['tipo_empresa']) ? intval($_POST['tipo_empresa']) : null;
    $res = $conexao->query("SELECT CONTROLA_ESPACOS FROM TIPOS WHERE ID = $tipo_empresa");
    $quantidade_espacos = isset($_POST['quantidade_espacos']) ? intval($_POST['quantidade_espacos']) : 0;
    $erro = '';
   if (empty($tipo_empresa)) {
    $erro = "Cadastre um tipo de empresa primeiro.";
}
   if (!empty($tipo_empresa)) {
        if ($res && $res->num_rows > 0) {
            $tipo = $res->fetch_assoc();
            if ($tipo['CONTROLA_ESPACOS'] != 'S') {
                $quantidade_espacos = 0;
            }
        } else {
            $tipo_empresa = null;
            $quantidade_espacos = 0;
        }
   }
    $sql = "INSERT INTO empresas 
(ANO, NOME_FANTASIA, RAZAO_SOCIAL, CNPJ, TIPO_ID, QUANTIDADE_ESPACOS)
VALUES ($ano,'$nome','$razao_social','$cnpj', $tipo_empresa, $quantidade_espacos)";
   if (empty($erro)) {
    if ($conexao->query($sql)) {

      $id_empresa = mysqli_insert_id($conexao);
      
$dadosLog =
    "ID=" . $id_empresa . "," .
    "NOME=" . $nome . "," .
    "ANO=" . $ano . "," .
    "RAZAO_SOCIAL=" . $razao_social . "," .
    "CNPJ=" . $cnpj . "," .
    "TIPO_ID=" . $tipo_empresa . "," .
    "ESPACOS=" . $quantidade_espacos;

registrarLog(
    'INCLUSAO',
    'EMPRESAS',
    $dadosLog
);
        header("Location: pag1.php?pagina=EMPRESAS");
        exit;
    } else {
        echo "<div class='alert alert-danger mt-3'>Erro ao salvar.</div>";
    }
}
}
?>
<!DOCTYPE html>
<head>
  <title>Bootstrap Example</title>
 <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>EMPRESAS</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
</head>
<body>
  <style>
 .fundo-tabela {
  background-image: url('img/fundologin.png');
  background-size: cover;
  background-position: center;
  padding: 25px;
  border-radius: 8px;
  min-height: 255px;
}
</style>
<div class="container mt-4 fundo-tabela">
<br /><br/>
 <h4 class="text-center">Cadastro da Empresa</h4>
  <form action="#" enctype="multipart/form-data" method="POST">
    <div class="mb-3">
      <label for="nome" class="form-label">Nome:</label>
      <input type="text" class="form-control" name="nome" placeholder="Digite o nome..." required>
      <label for="cnpj" class="form-label">CNPJ:</label>
<input
    type="text"
    class="form-control"
    id="cnpj"
    name="cnpj"
    placeholder="Digite o CNPJ..."
    onblur="validarCNPJ()"
    required>

<small id="erroCNPJ" style="color:red"></small>
<br>
      <label for="razao_social" class="form-label">Razao social:</label>
       <input type="text" class="form-control" name="razao_social" placeholder="Digite a razão social..." required>
      
      
      <label class="form-label">Tipo de Empresa:</label>
<select name="tipo_empresa" id="tipo_empresa" class="form-control" onchange="verificarEspacos()">
  <?php if ($temTipos) { ?>
    <option value="">Selecione o tipo</option>
    <?php while($t = $tipos->fetch_assoc()) { ?>
      <option value="<?= $t['ID'] ?>" data-controla="<?= $t['CONTROLA_ESPACOS'] ?>">
        <?= $t['NOME'] ?>
      </option>
    <?php } ?>
  <?php } else { ?>
    <option value="">Sem tipo de empresa.</option>
  <?php } ?>
</select>

<div id="campo_espacos" style="display:none;">
  <label>Quantidade de Espaços</label>
  <input type="number" name="quantidade_espacos" class="form-control">
</div>
  <br><div class="row">
  <div class="col-md-12">
    <?php if (!empty($erro)) { ?>
    <div class="alert alert-danger text-center">
        <?= $erro ?>
    </div>
<?php } ?>
    <button type="submit" class="btn btn-dark btn-lg w-100">Cadastrar</button>
  </div>
<script>
function verificarEspacos() {
    var select = document.getElementById("tipo_empresa");
    var selected = select.options[select.selectedIndex];
    var campo = document.getElementById("campo_espacos");
    if (!selected) {
        campo.style.display = "none";
        return;
    }
    var controla = selected.getAttribute("data-controla");
    if (controla == "S") {
        campo.style.display = "block";
    } else {
        campo.style.display = "none";
    }
}
</script>

  
</form>
  <script src="js/validacoes.js"></script>
</body>
</html>