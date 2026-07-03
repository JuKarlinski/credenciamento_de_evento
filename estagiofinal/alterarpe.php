<style>
.fundo-form {
    background-image: url('img/fundologin.png');
    background-size: cover;
    background-position: center;
    min-height: 530px;
    padding: 20px;
    border: 8px solid #ffffff;   
    border-radius: 14px;     
    overflow: hidden;
}
</style>

<?php
include_once('conexao.php');
include_once("logs.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$id = intval($_GET['id']);
if (($_SESSION['CATEGORIA_ID'] ?? null) == 3) {
    $empresaUsuario = intval($_SESSION['EMPRESA_ID']);
    $check = $conexao->query("SELECT EMPRESA_ID FROM pessoas WHERE ID = $id");
    $dados = $check->fetch_assoc();
    if (!$dados || $dados['EMPRESA_ID'] != $empresaUsuario) {
        die("Você não pode editar essa pessoa");
    }
}
$empresas = $conexao->query("SELECT * FROM empresas WHERE NOME_FANTASIA <> 'teste'");
$sql = "SELECT * FROM pessoas WHERE ID = $id";
$resultado = $conexao->query($sql);
$dados = $resultado->fetch_assoc();

$dadosAntigos =
    "ID=" . $dados['ID'] . "," .
    "EMPRESA_ID=" . $dados['EMPRESA_ID'] . "," .
    "NOME=" . $dados['NOME'] . "," .
    "INGRESSO_PERMANENTE=" . $dados['INGRESSO_PERMANENTE'] . "," .
    "FOTO=" . $dados['FOTO'] . "," .
    "CPF=" . $dados['CPF'] . "," .
    "DOCUMENTO=" . $dados['DOCUMENTO'] . "," .
    "TELEFONE=" . $dados['TELEFONE'] . "," .
    "CARGO_ID=" . $dados['CARGO_ID'];

if ($_POST) {
    $uploaddir = 'img/';
    $foto_nome = $dados['FOTO']; 
    $documento = $_POST['documento'] ?? '';
    $nome = $_POST['nome'] ?? '';
    if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) {
    $empresa_id = $_POST['empresa_id'] ?? '';
} else {
    $empresa_id = $_SESSION['EMPRESA_ID'];
}
    $ingresso = $_POST['ingresso'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $cargo_id = !empty($_POST['cargo']) ? $_POST['cargo'] : "NULL";

    if (!empty($_FILES['foto']['tmp_name'])) {
        $novo_nome = time() . '_' . basename($_FILES['foto']['name']);
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploaddir . $novo_nome)) {
            $foto_nome = $novo_nome;
        }
    }

    $sql = "UPDATE pessoas SET
        EMPRESA_ID = '$empresa_id',
        NOME = '$nome',
        INGRESSO_PERMANENTE = '$ingresso',
        FOTO = '$foto_nome',
        CPF = '$cpf',
        DOCUMENTO = '$documento',
        TELEFONE = '$telefone',
        CARGO_ID = $cargo_id
        WHERE ID = $id";

    if ($conexao->query($sql)) {
        
$dadosNovos =
    "ID=" . $id . "," .
    "EMPRESA_ID=" . $empresa_id . "," .
    "NOME=" . $nome . "," .
    "INGRESSO_PERMANENTE=" . $ingresso . "," .
    "FOTO=" . $foto_nome . "," .
    "CPF=" . $cpf . "," .
    "DOCUMENTO=" . $documento . "," .
    "TELEFONE=" . $telefone . "," .
    "CARGO_ID=" . $cargo_id;
    

registrarLog(
    'ALTERACAO',
    'PESSOAS',
    $dadosAntigos,
    $dadosNovos
);
        header("Location: pag1.php?pagina=PESSOAS");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Erro: " . $conexao->error . "</div>";
    }
}
?>
<div class="d-flex justify-content-center align-items-center">
<div class="fundo-form p-4 rounded" style="width:1100px;">
<h4 class="text-center">Alterações da Pessoa</h4>
<form method="POST" enctype="multipart/form-data">
<label>Nome:</label>
<input type="text" name="nome"
value="<?= $dados['NOME'] ?? '' ?>"
class="form-control" required>

<label>Empresa</label>
<?php if (($_SESSION['CATEGORIA_ID'] ?? null) == 1) { ?>
<select name="empresa_id" class="form-control">
<?php if ($empresas->num_rows > 0) { ?>
    <option value="">Selecione uma empresa</option>
    <?php while($e = $empresas->fetch_assoc()){ ?>
        <option value="<?= $e['ID'] ?>"
            <?= ($e['ID'] == ($dados['EMPRESA_ID'] ?? '')) ? 'selected' : '' ?>>
            <?= htmlspecialchars($e['NOME_FANTASIA']) ?>
        </option>
    <?php } ?>
<?php } else { ?>
  <option value="">Sem empresa cadastrada</option>
<?php } ?>
</select>
<?php } else { ?>
<input type="text" class="form-control"
       value="<?= htmlspecialchars($_SESSION['NOME_EMPRESA'] ?? 'Sua empresa') ?>"
       disabled>
<input type="hidden" name="empresa_id"
       value="<?= $_SESSION['EMPRESA_ID'] ?>">
<?php } ?>

<label>Ingresso Permanente:</label>
<select name="ingresso" class="form-control">
    <option value="">Selecione...</option>
    <option value="S" <?= (($dados['INGRESSO_PERMANENTE'] ?? '') == 'S') ? 'selected' : '' ?>>
        Sim (Permanente)
    </option>
    <option value="N" <?= (($dados['INGRESSO_PERMANENTE'] ?? '') == 'N') ? 'selected' : '' ?>>
        Não
    </option>
</select>
<label>Foto:</label>

<?php if (!empty($dados['FOTO'])) { ?>
    <div style="margin-bottom:10px;">
        <img src="img/<?= htmlspecialchars($dados['FOTO']) ?>" 
             style="width:50px; height:50px; object-fit:cover; border-radius:10px;">
    </div>
<?php } ?>

<div class="custom-file">
    <input type="file" name="foto" class="custom-file-input" id="foto">
    <label class="custom-file-label" for="foto">Alterar sua imagem</label>
</div>
<label>CPF:</label>
<input type="text" name="cpf"
value="<?= $dados['CPF'] ?? '' ?>"
class="form-control" required>
<label>Documento:</label>
<input type="text" name="documento"
value="<?= $dados['DOCUMENTO'] ?? '' ?>"
class="form-control">
<label>Telefone:</label>
<input type="text" name="telefone"
value="<?= $dados['TELEFONE'] ?? '' ?>"
class="form-control">
<?php
$empresaCargo = $dados['EMPRESA_ID'] ?? 0;

$cargos = $conexao->query("
    SELECT * FROM cargos
   WHERE ID_EMPRESA = $empresaCargo
");
echo '<label>Cargo:</label>';
echo '<select name="cargo" class="form-control">';
if ($cargos->num_rows > 0) {
    echo '<option value="">Selecione um cargo</option>';
    while($c = $cargos->fetch_assoc()) {
        $selected = ($c['ID'] == ($dados['CARGO_ID'] ?? '')) ? 'selected' : '';
        echo "<option value='{$c['ID']}' $selected>{$c['NOME']}</option>";
    }
} else {
    echo '<option value="">Sem cargo cadastrado</option>';
}
echo '</select>';
?>
<br>
<button type="submit" class="btn btn-dark btn-lg w-100">Salvar</button>
<script>
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    let nomeArquivo = e.target.files[0]?.name || "Alterar sua imagem";
    e.target.nextElementSibling.innerText = nomeArquivo;
});
</script>
</form>
</div>
</div>