<?php
include_once('conexao.php');
include_once("logs.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$uploaddir = 'img/';
if (($_SESSION['CATEGORIA_ID'] ?? null) == 3) {
    $id_empresa = $_SESSION['EMPRESA_ID'];
    $cargos = $conexao->query("SELECT * FROM cargos WHERE ID_EMPRESA = '$id_empresa'");
} else {
    $cargos = $conexao->query("SELECT * FROM cargos");
}

$empresas = $conexao->query("SELECT * FROM empresas WHERE NOME_FANTASIA <> 'teste'");
$aviso = '';
$erro = '';
$total_pessoas = 0;
$limite = 0;
if (in_array($_SESSION['CATEGORIA_ID'] ?? null, [1,2])) {
   $empresa_id = isset($_POST['empresa_id'])
    ? intval($_POST['empresa_id'])
    : (isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0);
} else {
    $empresa_id = intval($_SESSION['EMPRESA_ID'] ?? 0);
}
$empresa_fixa = isset($_GET['empresa_id']) && intval($_GET['empresa_id']) > 0;

if ($empresa_id > 0) {
    $sql_total = "SELECT COUNT(*) AS total FROM pessoas WHERE EMPRESA_ID = '$empresa_id'";
    $result_total = $conexao->query($sql_total);
    if ($result_total && $result_total->num_rows > 0) {
        $dados_total = $result_total->fetch_assoc();
        $total_pessoas = $dados_total['total'];
    }

   $sql_limite = "
SELECT
    tipos.LIMITE_PESSOAS,
    empresas.QUANTIDADE_ESPACOS
FROM empresas
INNER JOIN tipos ON empresas.TIPO_ID = tipos.ID
WHERE empresas.ID = '$empresa_id'
";

$result_limite = $conexao->query($sql_limite);

if ($result_limite && $result_limite->num_rows > 0) {
    $dados_limite = $result_limite->fetch_assoc();

    $limite = intval($dados_limite['LIMITE_PESSOAS']) *
               intval($dados_limite['QUANTIDADE_ESPACOS']);
}
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim(strip_tags($_POST['nome'] ?? ''));
    $ingresso = strtoupper(trim($_POST['ingresso'] ?? 'N'));
    $ingresso = ($ingresso === 'S' || $ingresso === 'N') ? $ingresso : 'N';
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $telefone = isset($_POST['telefone']) ? preg_replace('/[^0-9]/', '', $_POST['telefone']) : '';
    $foto_nome = $_POST['foto_atual'] ?? '';
    $documento = trim($_POST['documento'] ?? '');
    $cargo_id = !empty($_POST['cargo_id']) ? intval($_POST['cargo_id']) : null;
    
    if (($_SESSION['CATEGORIA_ID'] ?? null) == 3) {
        $empresa_id = $_SESSION['EMPRESA_ID'];
    }

    if ($empresas->num_rows == 0) {
        $erro = "Cadastre uma empresa primeiro.";
    } else if (in_array($_SESSION['CATEGORIA_ID'] ?? null, [1,2]) && empty($empresa_id)) {
        $erro = "Selecione uma empresa.";
    }


    if (empty($erro) && $empresa_id > 0) {
        

        $sql_total = "SELECT COUNT(*) AS total FROM pessoas WHERE EMPRESA_ID = '$empresa_id'";
        $dados_total = $conexao->query($sql_total)->fetch_assoc();
        $total_pessoas = $dados_total['total'];

        $sql_limite = "
SELECT
    tipos.LIMITE_PESSOAS,
    empresas.QUANTIDADE_ESPACOS
FROM empresas
INNER JOIN tipos ON empresas.TIPO_ID = tipos.ID
WHERE empresas.ID = '$empresa_id'
";

$dados_limite = $conexao->query($sql_limite)->fetch_assoc();

$limite = $dados_limite
    ? intval($dados_limite['LIMITE_PESSOAS']) *
      intval($dados_limite['QUANTIDADE_ESPACOS'])
    : 0;

        if ($limite > 0 && $total_pessoas >= $limite) {
            
            if ($_SESSION['CATEGORIA_ID'] == 2) {
                if (empty($_POST['senha_admin'])) {
                    $erro = "Limite atingido! Informe a senha do Administrador para prosseguir.";
                } else {
                    $senha_admin = md5($_POST['senha_admin']);
                    $sql_admin = "SELECT * FROM usuarios WHERE SENHA = '$senha_admin' AND CATEGORIA_ID = 1";
                    $result_admin = $conexao->query($sql_admin);
                    
                    if ($result_admin->num_rows === 0) {
                        $erro = "Senha do administrador INCORRETA! O cadastro foi cancelado.";
                    } else {
                        $aviso = "Cadastro autorizado pelo Administrador.";
                    }
                }
            }
            elseif ($_SESSION['CATEGORIA_ID'] == 1) {
                $aviso = "Limite de pessoas atingido (Liberado por você ser Admin).";
            }
        }
    }

  
    if (empty($erro)) {
        if (!empty($_FILES['foto']['tmp_name'])) {
            $foto_nome = time() . '_' . $_FILES['foto']['name'];
            move_uploaded_file($_FILES['foto']['tmp_name'], $uploaddir . $foto_nome);
        }

        $sql = "INSERT INTO pessoas 
        (EMPRESA_ID, NOME, INGRESSO_PERMANENTE, FOTO, CPF, DOCUMENTO, TELEFONE, CARGO_ID)
        VALUES 
        ('$empresa_id', '$nome', '$ingresso', '$foto_nome', '$cpf', '$documento', '$telefone', " . ($cargo_id === null ? "NULL" : $cargo_id) . ")";
        
        if ($conexao->query($sql)) {

        $id_pessoa = mysqli_insert_id($conexao);
       
 $dadosLog =
    "ID=" . $id_pessoa . "," .
    "EMPRESA_ID=" . $empresa_id . "," .
    "NOME=" . $nome . "," .
    "INGRESSO=" . $ingresso . "," .
    "FOTO=" . $foto_nome . "," .
    "CPF=" . $cpf . "," .
    "DOCUMENTO=" . $documento . "," .
    "TELEFONE=" . $telefone . "," .
    "CARGO_ID=" . ($cargo_id === null ? "NULL" : $cargo_id);

registrarLog(
    'INCLUSAO',
    'pessoas',
    $dadosLog
);
            $total_pessoas++; 
            $aviso = "Pessoa cadastrada com sucesso!";
            $_POST = array(); 
            $foto_nome = ''; 
            
        } else {
            $erro = "Erro ao gravar no banco de dados: " . $conexao->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>PESSOAS</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
</head>
<body>
<style>
.a { text-decoration: none; color: white; }
.img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
.fundo-tabela {
  background-image: url('img/fundologin.png');
  background-size: cover;
  background-position: center;
  padding: 20px;
  border-radius: 10px;
}
</style>
<div class="container mt-4 fundo-tabela">
<br /><br/>

<?php if($_SESSION['CATEGORIA_ID'] == 3 && $limite > 0 && $total_pessoas >= $limite) { ?>
    <div class="alert alert-danger text-center font-weight-bold">
        Limite máximo atingido. Cadastro indisponível para o seu nível de acesso.
    </div>
<?php } ?>

<h4 class="text-center">Cadastro de pessoas:</h4>
  <form action="#" enctype="multipart/form-data" method="POST">
    <div class="mb-3">

    <label for="nome" class="form-label">Nome:</label>
    <input type="text" class="form-control" name="nome" placeholder="Digite o nome..." value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
    
    <label for="ingresso_permanente" class="form-label">Ingresso Permanente:</label>
    <select name="ingresso" class="form-control">
        <option value="">Selecione...</option>
        <option value="S" <?= (($_POST['ingresso'] ?? '') == 'S') ? 'selected' : '' ?>>Sim (Permanente)</option>
        <option value="N" <?= (($_POST['ingresso'] ?? '') == 'N') ? 'selected' : '' ?>>Não</option>
    </select>

    <label for="foto" class="form-label">Selecione a foto:</label>
    <input type="file" class="form-control" name="foto" accept="image/*" <?= empty($foto_nome) ? 'required' : '' ?>>
    <input type="hidden" name="foto_atual" value="<?= htmlspecialchars($foto_nome ?? '') ?>">
    <?php if (!empty($foto_nome)) { ?>
        <div class="mt-2">
            <img src="img/<?= htmlspecialchars($foto_nome) ?>" class="img">
        </div>
    <?php } ?>

    <label for="cpf" class="form-label">CPF:</label>
<input
    type="text"
    class="form-control"
    id="cpf"
    name="cpf"
    placeholder="Digite o CPF..."
    value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>"
    onblur="validarCPF()"
    required>

<small id="erroCPF" style="color:red"></small>
<br>
    <label for="documento" class="form-label">Documento:</label>
    <input type="text" class="form-control" name="documento" placeholder="Digite o documento..." value="<?= htmlspecialchars($_POST['documento'] ?? '') ?>">
    
    <label for="telefone" class="form-label">Telefone:</label>
    <input type="text" class="form-control" name="telefone" placeholder="Digite o telefone..." value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
   
    <label>Empresa</label>
    <?php if (in_array($_SESSION['CATEGORIA_ID'] ?? null, [1,2])) { ?>
        <select name="empresa_id" id="empresa_id" class="form-control">
        <?php if ($empresas->num_rows > 0) { ?>
            <option value="">Selecione uma empresa</option>
            <?php while($e = $empresas->fetch_assoc()){ ?>
              <option value="<?= $e['ID'] ?? $e['id'] ?>" <?= (($_POST['empresa_id'] ?? $empresa_id) == ($e['ID'] ?? $e['id'])) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['NOME_FANTASIA'] ?? $e['nome_fantasia']) ?>
                </option>
            <?php } ?>
        <?php } else { ?>
          <option value="">Sem empresa cadastrada.</option>
        <?php } ?>
        </select>
    <?php } else { ?>
        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['NOME_EMPRESA'] ?? 'Sua empresa') ?>" disabled>
        <input type="hidden" name="empresa_id" id="empresa_id" value="<?= $_SESSION['EMPRESA_ID'] ?>">
    <?php } ?>

    <br>

    <label>Cargo</label>
    <select name="cargo_id" id="cargo_id" class="form-control">
        <option value="">Selecione uma empresa primeiro</option>
    </select>

    </div>

    <?php if (!empty($aviso)) { ?>
        <div class="alert alert-success text-center font-weight-bold"><?= $aviso ?></div>
    <?php } ?>
    
    <?php if (!empty($erro)) { ?>
        <div class="alert alert-danger text-center font-weight-bold"><?= $erro ?></div>
    <?php } ?>

    <div class="alert alert-info text-center">
        Pessoas cadastradas: <strong><?= $total_pessoas ?></strong> de <strong><?= $limite ?></strong>
    </div>

    <?php if($_SESSION['CATEGORIA_ID'] == 2 && $limite > 0 && $total_pessoas >= $limite){ ?>
        <div class="form-group border border-danger p-3 bg-light rounded">
            <label class="text-danger font-weight-bold">⚠️ Liberação Requerida (Limite Atingido):</label>
            <input type="password" name="senha_admin" class="form-control" placeholder="Digite a senha do Administrador Geral" required>
        </div>
    <?php } ?>
<?php if(!($_SESSION['CATEGORIA_ID'] == 3 && $limite > 0 && $total_pessoas >= $limite)) { ?>
    <button type="submit" class="btn btn-dark btn-lg w-100">
        Cadastrar
    </button>
<?php } ?>
  </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const empresaSelect = document.getElementById('empresa_id') || document.querySelector('[name="empresa_id"]');
    const cargoSelect = document.getElementById('cargo_id');
    const cargos = <?php
    $lista = [];
    $cargos_js = $conexao->query("SELECT * FROM cargos");
    if ($cargos_js) {
        while($c = $cargos_js->fetch_assoc()){
            $empresa_vinculada = $c['ID_EMPRESA'] ?? $c['id_empresa'] ?? $c['EMPRESA_ID'] ?? $c['empresa_id'] ?? '';
            $id_cargo = $c['ID'] ?? $c['id'] ?? '';
            $nome_cargo = $c['NOME'] ?? $c['nome'] ?? '';
            $lista[] = [
                'id' => $id_cargo,
                'nome' => $nome_cargo,
                'empresa' => $empresa_vinculada
            ];
        }
    }
    echo json_encode($lista);
    ?>;

    function atualizarcargos() {
        if (!cargoSelect) return;
        let empresa = empresaSelect ? empresaSelect.value : '';
        if (!empresa || empresa === '') {
            empresa = "<?= $_SESSION['EMPRESA_ID'] ?? '' ?>";
        }

        cargoSelect.innerHTML = '';
        if(!empresa || empresa == ''){
            cargoSelect.innerHTML = '<option value="">Selecione uma empresa primeiro</option>';
            return;
        }

        let encontrou = false;
        const cargoSelecionado = "<?= $_POST['cargo_id'] ?? '' ?>";

        cargos.forEach(function(cargo){
            if(String(cargo.empresa) === String(empresa)){
                encontrou = true;
                const selected = String(cargo.id) === String(cargoSelecionado) ? ' selected' : '';
                cargoSelect.innerHTML += '<option value="' + cargo.id + '"' + selected + '>' + cargo.nome + '</option>';
            }
        });

        if(!encontrou){
            cargoSelect.innerHTML = '<option value="">Sem cargos nesta empresa</option>';
        }
    }

    if (empresaSelect) {
        empresaSelect.addEventListener('change', atualizarcargos);
    }
    atualizarcargos();
});
</script>
    <script src="js/validacoes.js"></script>
</body>
</html>