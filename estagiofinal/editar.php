<?php
include_once("conexao.php");
include_once("logs.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$empresa_origem = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0;
$nome_origem = isset($_GET['nome']) ? $_GET['nome'] : '';

if ($_POST) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $categoria_id = intval($_POST['categoria_id']);
    $empresa_id = ($categoria_id == 3 && isset($_POST['empresa_id'])) ? intval($_POST['empresa_id']) : 0;
    $empresa_valor_db = ($empresa_id > 0) ? $empresa_id : "NULL";

    if (($_SESSION['CATEGORIA_ID'] ?? null) == 2) {
        die("Acesso negado");
    }
    $senha_post = trim($_POST['senha']);
    $busca_senha = $conexao->query("SELECT SENHA FROM usuarios WHERE ID = $id");
    $senha_atual = $busca_senha->fetch_assoc()['SENHA'];

    if ($senha_post !== $senha_atual && !empty($senha_post)) {
        $senha = md5($senha_post);
    } else {
        $senha = $senha_atual;
    }
    $buscaAntigo = $conexao->query("
    SELECT * FROM usuarios
    WHERE ID = $id
");

$antigo = $buscaAntigo->fetch_assoc();

$dadosAntigos =
    "ID=" . $antigo['ID'] . "," .
    "NOME=" . $antigo['NOME'] . "," .
    "EMAIL=" . $antigo['EMAIL'] . "," .
    "CATEGORIA_ID=" . $antigo['CATEGORIA_ID'] . "," .
    "EMPRESA_ID=" . ($antigo['EMPRESA_ID'] ?? 'NULL');

    $sql = "UPDATE usuarios SET
    NOME = '$nome',
    EMAIL = '$email',
    SENHA = '$senha',
    CATEGORIA_ID = '$categoria_id',
    EMPRESA_ID = $empresa_valor_db
    WHERE ID = $id";

    if ($conexao->query($sql)) {
    $dadosNovos =
    "ID=" . $id . "," .
    "NOME=" . $nome . "," .
    "EMAIL=" . $email . "," .
    "CATEGORIA_ID=" . $categoria_id . "," .
    "EMPRESA_ID=" . ($empresa_id > 0 ? $empresa_id : 'NULL');

registrarLog(
    'ALTERACAO',
    'usuarios',
    $dadosAntigos,
    $dadosNovos
);
        if ($empresa_origem > 0) {
            if (empty($nome_origem)) {
                $res_emp = $conexao->query("SELECT NOME_FANTASIA FROM empresas WHERE ID = $empresa_origem");
                if ($res_emp && $res_emp->num_rows > 0) {
                    $nome_origem = $res_emp->fetch_assoc()['NOME_FANTASIA'];
                }
            }
            header("Location: pag1.php?pagina=usuarios&empresa_id=" . $empresa_origem . "&nome=" . urlencode($nome_origem));
        } else {
            header("Location: pag1.php?pagina=usuario");
        }
        exit;
        
    } else {
        echo "Erro ao atualizar";
    }
}

$result = $conexao->query("SELECT * FROM usuarios WHERE ID = $id");

if ($result && $result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
} else {
    echo "Usuário não encontrado";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Editar Usuário</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<style>
.fundo-tabela {
  background-image: url('img/fundologin.png');
  background-size: cover;
  padding: 25px;
  border-radius: 8px;
}
</style>
</head>
<body>

<div class="container mt-4 fundo-tabela">
<h4 class="text-center">Editar Usuário</h4>

<form method="POST">

<label>Nome:</label>
<input type="text" name="nome" class="form-control" 
value="<?= htmlspecialchars($usuario['NOME']) ?>" required>

<label>Email:</label>
<input type="text" name="email" class="form-control" 
value="<?= htmlspecialchars($usuario['EMAIL']) ?>" required>

<label>Senha:</label>
<input type="password" name="senha" class="form-control" 
value="<?= htmlspecialchars($usuario['SENHA']) ?>" required>

<label>Categoria:</label>
<select name="categoria_id" class="form-control" id="categoria" onchange="mostrarEmpresa(this.value)">
    <option value="1" <?= $usuario['CATEGORIA_ID'] == 1 ? 'selected' : '' ?>>Administrador</option>
    <option value="2" <?= $usuario['CATEGORIA_ID'] == 2 ? 'selected' : '' ?>>Funcionário</option>
    <option value="3" <?= $usuario['CATEGORIA_ID'] == 3 ? 'selected' : '' ?>>Expositor</option>
</select>

<div id="empresa" style="<?= $usuario['CATEGORIA_ID'] == 3 ? '' : 'display:none;' ?>">
<label>Empresa:</label>
<select name="empresa_id" class="form-control">
    <option value="">Selecione uma empresa</option>
<?php
$empresas = $conexao->query("SELECT * FROM empresas");
while($empresa = $empresas->fetch_assoc()){
?>
<option value="<?= $empresa['ID'] ?>" <?= $usuario['EMPRESA_ID'] == $empresa['ID'] ? 'selected' : '' ?>>
<?= htmlspecialchars($empresa['NOME_FANTASIA']) ?>
</option>
<?php } ?>
</select>
</div>
<br>
<button type="submit" class="btn btn-dark w-100">
Salvar Alterações
</button>

</form>
</div>

<script>
function mostrarEmpresa(valor){
    var empresaDiv = document.getElementById('empresa');
    if(valor == '3'){
        empresaDiv.style.display = 'block';
    } else {
        empresaDiv.style.display = 'none';
    }
}
</script>
</body>
</html>