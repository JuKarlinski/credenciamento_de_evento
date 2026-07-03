<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once('conexao.php');
include_once("logs.php");

$erro = '';
$empresa_id = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $empresa_id = isset($_POST['empresa_id']) ? intval($_POST['empresa_id']) : 0;
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = md5($_POST['senha']);
    $categoria_id = isset($_POST['categoria_id']) ? intval($_POST['categoria_id']) : 3;
    $empresa_valor_db = ($empresa_id > 0) ? $empresa_id : "NULL";

    $sql = "INSERT INTO usuarios 
    (NOME, EMAIL, SENHA, CATEGORIA_ID, EMPRESA_ID)
    VALUES 
    ('$nome', '$email', '$senha', '$categoria_id', $empresa_valor_db)";

    if ($conexao->query($sql)) {
        $id_usuario = mysqli_insert_id($conexao);

$dadosLog =
    "ID=" . $id_usuario . "," .
    "NOME=" . $nome . "," .
    "EMAIL=" . $email . "," .
    "CATEGORIA_ID=" . $categoria_id . "," .
    "EMPRESA_ID=" . $empresa_id;

registrarLog(
    'INCLUSAO',
    'USUARIOS',
    $dadosLog
);
        if ($empresa_id > 0) {
            header("Location: pag1.php?pagina=USUÁRIOS&empresa_id=" . $empresa_id . "&sucesso=1");
        } else {
            header("Location: pag1.php?pagina=USUÁRIOS&sucesso=1");
        }
        exit;
    } else {
        $erro = "Erro ao salvar.";
    }
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<style>
.fundo-tabela {
  background-image: url('img/fundologin.png');
  background-size: cover;
  background-position: center;
  padding: 20px;
  border-radius: 10px;
}
</style>
<div class="container mt-4 fundo-tabela p-4 text-dark">
        <h3 class="text-center mb-4">Cadastro de Usuário</h3>

        <form method="POST">
            <input type="hidden" name="empresa_id" value="<?= $empresa_id ?>">

            <label>Nome</label>
            <input type="text" name="nome" class="form-control" required>
            <br>
            <label>Email</label>
            <input type="text" name="email" class="form-control" required>
            <br>
            <label>Senha</label>
            <input type="password" name="senha" class="form-control" required>
            <br>

            <?php if ($empresa_id > 0): ?>
                <input type="hidden" name="categoria_id" value="3">
            <?php else: ?>
                <div class="form-group">
                    <label for="categoria_id">Categoria</label>
                    <select name="categoria_id" id="categoria_id" class="form-control" onchange="mostrarEmpresa(this.value)" required>
                        <option value="">Selecione...</option>
                        <option value="1">Administrador</option>
                        <option value="2">Funcionário</option>
                        <option value="3">Expositor</option>
                    </select>
                </div>
            <?php endif; ?>
            
            <br>

            <?php if ($empresa_id == 0): ?>
            <div id="empresa" style="display:none;">
                <label>Empresa</label>
                <select name="empresa_id_select" id="empresa_id_select" class="form-control" onchange="atualizarEmpresaOculta(this.value)">
                    <option value="">Selecione</option>
                    <?php
                    $empresas = $conexao->query("SELECT * FROM empresas");
                    if($empresas){
                        while($empresa = $empresas->fetch_assoc()){
                           echo "<option value='".$empresa['ID']."'>".$empresa['NOME_FANTASIA']."</option>";
                        }
                    }
                    ?>
                </select>
                <br>
            </div>
            <?php endif; ?>

            <?php if (!empty($erro)) { ?>
                <div class="alert alert-danger">
                    <?php echo $erro; ?>
                </div>
            <?php } ?>

            <button type="submit" class="btn btn-dark w-100"> Cadastrar </button>
        </form>
    </div>
</div>

<script>
function mostrarEmpresa(valor){
    var empresa = document.getElementById('empresa');
    if(empresa) {
        if(valor == '3'){
            empresa.style.display = 'block';
        } else {
            empresa.style.display = 'none';
            atualizarEmpresaOculta('0'); 
        }
    }
}
function atualizarEmpresaOculta(valor) {
    document.getElementsByName('empresa_id')[0].value = valor;
}
</script>