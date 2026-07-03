<style>
.fundo-form {
    background-image: url('img/fundologin.png');
    background-size: cover;
    background-position: center;
    min-height: 540px;
    padding: 20px;
    border: 8px solid #ffffff;   
    border-radius: 13px;     
    overflow: hidden;
}
</style>
<?php
include_once('conexao.php');
include_once("logs.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM empresas WHERE ID = $id";
$resultado = $conexao->query($sql);
$tipos = $conexao->query("SELECT * FROM TIPOS");
$temTipos = ($tipos && $tipos->num_rows > 0);
$dados = $resultado->fetch_assoc();
$tipo_empresa = $dados['TIPO_ID'] ?? null;
$espacosAtual = $dados['QUANTIDADE_ESPACOS'] ?? 0;

$dadosAntigos =
    "ID=" . $dados['ID'] . "," .
    "NOME=" . $dados['NOME_FANTASIA'] . "," .
    "TIPO_ID=" . $dados['TIPO_ID'] . "," .
    "ANO=" . $dados['ANO'] . "," .
    "CNPJ=" . $dados['CNPJ'] . "," .
    "RAZAO_SOCIAL=" . $dados['RAZAO_SOCIAL'] . "," .
    "ESPACOS=" . $dados['QUANTIDADE_ESPACOS'];

if ($_POST) {
    $nome = $_POST['nome'] ?? '';
    $ano = 2026;
    $razao_social = $_POST['razao_social'] ?? '';
    $cnpj = $_POST['cnpj'] ?? '';
    $tipo_empresa = $_POST['tipo_empresa'] ?? null;
    $quantidade_espacos = $_POST['quantidade_espacos'] ?? 0;
    if ($tipo_empresa == "" || $tipo_empresa == null) {
        $tipo_empresa = "NULL";
        $quantidade_espacos = 0;
    } else {
        $res = $conexao->query("SELECT CONTROLA_ESPACOS FROM TIPOS WHERE ID = $tipo_empresa");
        if ($res && $res->num_rows > 0) {
            $tipo = $res->fetch_assoc();
            if ($tipo['CONTROLA_ESPACOS'] != 'S') {
                $quantidade_espacos = 0;
            }
        } else {
            $tipo_empresa = "NULL";
            $quantidade_espacos = 0;
        }
    }
    $sql = "UPDATE empresas SET 
        ANO = '$ano',
        NOME_FANTASIA = '$nome',
        RAZAO_SOCIAL = '$razao_social',
        CNPJ = '$cnpj',
        TIPO_ID = $tipo_empresa,
        QUANTIDADE_ESPACOS = '$quantidade_espacos'
        WHERE ID = $id";

    if ($conexao->query($sql)) {

$dadosNovos =
    "ID=" . $id . "," .
    "NOME=" . $nome . "," .
    "TIPO_ID=" . $tipo_empresa . "," .
    "ANO=" . $ano . "," .
    "CNPJ=" . $cnpj . "," .
    "RAZAO_SOCIAL=" . $razao_social . "," .
    "ESPACOS=" . $quantidade_espacos;

registrarLog(
    'ALTERACAO',
    'EMPRESAS',
    $dadosAntigos,
    $dadosNovos
);
        header("Location: pag1.php?pagina=EMPRESAS");
        exit;
    } else {
        echo "Erro ao atualizar: " . $conexao->error;
    }
}
?>
<div class="d-flex justify-content-center align-items-center">
<div class="fundo-form p-4 rounded" style="width:1100px; height:500px;">
<h4 class="text-center">Alterações da Empresa</h4>
<form method="POST">
    <div class="form-group">
        <label>Empresa:</label>
        <input type="text" name="nome" 
               value="<?php echo $dados['NOME_FANTASIA']; ?>" 
                 class="form-control" required>
       <label>Tipo:</label>
<select name="tipo_empresa" id="tipo_empresa" class="form-control" onchange="verificarEspacos()">
<?php if ($temTipos) { ?>
    <?php while($t = $tipos->fetch_assoc()) { ?>
        <option
            value="<?= $t['ID'] ?>"
            data-controla="<?= $t['CONTROLA_ESPACOS'] ?>"
            <?= ($t['ID'] == $tipo_empresa) ? 'selected' : '' ?>>
            <?= $t['NOME'] ?>
        </option>
    <?php } ?>
<?php } ?>
</select>

<label>CNPJ:</label>
<input type="text"
       name="cnpj"
       value="<?= $dados['CNPJ'] ?>"
       class="form-control"
       >

<label>Razão Social:</label>
<input type="text"
       name="razao_social"
       value="<?= $dados['RAZAO_SOCIAL'] ?>"
       class="form-control"
       >

<div id="campo_espacos" style="margin-top:10px; display:none;">
    <label>Quantidade de Espaços</label>
    <input type="number"
           name="quantidade_espacos"
           value="<?= $espacosAtual ?>"
           class="form-control">
</div>
</select>
 <div id="campo_espacos" style="margin-top:10px; display:none;">
    </div>

<script>
function verificarEspacos() {
    const select = document.getElementById('tipo_empresa');
    const option = select.options[select.selectedIndex];
    const controla = option.getAttribute('data-controla');

    const campo = document.getElementById('campo_espacos');

    if (controla === 'S') {
        campo.style.display = 'block';
    } else {
        campo.style.display = 'none';

        const qtd = document.querySelector('[name="quantidade_espacos"]');
        if (qtd) qtd.value = 0;
    }
}

document.addEventListener('DOMContentLoaded', verificarEspacos);
</script>
 <br>
    <button type="submit" class="btn btn-dark btn-lg w-100">
        Salvar
    </button>
    
</form>