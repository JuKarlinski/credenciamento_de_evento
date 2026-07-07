<?php
include_once("conexao.php");
include_once("logs.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$empresa_id = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0;

if ($empresa_id <= 0) {
    die("Empresa não informada.");
}

$empresa = null;

$sqlEmpresa = $conexao->query("
    SELECT NOME_FANTASIA
    FROM empresas
    WHERE ID = $empresa_id
");

if ($sqlEmpresa && $sqlEmpresa->num_rows > 0) {
    $empresa = $sqlEmpresa->fetch_assoc();
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] != 0) {

        $mensagem = "
            <div class='alert alert-danger'>
                Selecione um arquivo válido.
            </div>
        ";

    } else {

        $linhas = file($_FILES['arquivo']['tmp_name']);

        if (!$linhas) {

            $mensagem = "
                <div class='alert alert-danger'>
                    Não foi possível ler o arquivo.
                </div>
            ";
        } else {

            array_shift($linhas);

           $importados = 0;
$ignorados = 0;
$linhasIgnoradas = [];
$numeroLinha = 1;
foreach ($linhas as $linha) {

    $numeroLinha++;

    $linha = trim($linha);

    if (empty($linha)) {
        $linhasIgnoradas[] = [
            'linha' => $numeroLinha,
            'dados' => $linha,
            'motivo' => 'Linha vazia'
        ];
        $ignorados++;
        continue;
    }

    $dados = explode(';', $linha);

    $nome      = trim($dados[0] ?? '');
    $cpf       = trim($dados[1] ?? '');
    $documento = trim($dados[2] ?? '');
    $telefone  = trim($dados[3] ?? '');

    // NOME VAZIO
    if (empty($nome)) {
        $linhasIgnoradas[] = [
            'linha' => $numeroLinha,
            'dados' => $linha,
            'motivo' => 'Nome vazio'
        ];
        $ignorados++;
        continue;
    }

if (!empty($cpf)) {

    $verifica = $conexao->prepare("
        SELECT ID
        FROM pessoas
        WHERE EMPRESA_ID = ?
          AND CPF = ?
    ");
    $verifica->bind_param("is", $empresa_id, $cpf);

} else {

    $verifica = $conexao->prepare("
        SELECT ID
        FROM pessoas
        WHERE EMPRESA_ID = ?
          AND UPPER(NOME) = UPPER(?)
    ");
    $verifica->bind_param("is", $empresa_id, $nome);

}

$verifica->execute();
$resultado = $verifica->get_result();

// DUPLICADO
if ($resultado->num_rows > 0) {

    $linhasIgnoradas[] = [
        'linha' => $numeroLinha,
        'dados' => $linha,
        'motivo' => 'Registro duplicado'
    ];

    $ignorados++;
    $verifica->close();
    continue;
}

$verifica->close();

    $stmt = $conexao->prepare("
        INSERT INTO pessoas
        (EMPRESA_ID, NOME, CPF, DOCUMENTO, TELEFONE, INGRESSO_PERMANENTE, FOTO, CARGO_ID)
        VALUES (?, ?, ?, ?, ?, '', '', NULL)
    ");

    if (!$stmt) {
        $linhasIgnoradas[] = [
            'linha' => $numeroLinha,
            'dados' => $linha,
            'motivo' => 'Erro ao preparar INSERT'
        ];
        $ignorados++;
        continue;
    }

    $stmt->bind_param("issss", $empresa_id, $nome, $cpf, $documento, $telefone);

    if ($stmt->execute()) {
        registrarLog(
            'IMPORTACAO',
            'PESSOAS',
            "EMPRESA_ID=$empresa_id,NOME=$nome,CPF=$cpf,DOCUMENTO=$documento,TELEFONE=$telefone"
        );

        $importados++;

    } else {
        $linhasIgnoradas[] = [
            'linha' => $numeroLinha,
            'dados' => $linha,
            'motivo' => 'Erro ao inserir no banco'
        ];
        $ignorados++;
    }

    $stmt->close();
}

         $mensagem = "
<div class='alert alert-primary' style='font-weight:500'>
    Importação concluída: $importados pessoas importadas com sucesso!<br>
    $ignorados linhas ignoradas.
</div>
";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Importar Pessoas</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
            <style>
.fundo-tabela {
    background-image: url('img/fundologin.png');
    background-size: cover;
    background-position: center;
    padding: 20px;
    border-radius: 10px;
}

.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
}

.upload-icon {
    font-size: 60px;
    text-align: center;
    margin-bottom: 10px;
}

.titulo-upload {
    text-align: center;
    color: #666;
    margin-bottom: 25px;
}
</style>
</head>
<body>

<div class="container mt-4">
<div class="fundo-tabela mb-4">
    <h2>IMPORTAR PESSOAS</h2>

    <h5>
        Empresa:
        <strong>
            <?php echo htmlspecialchars($empresa['NOME_FANTASIA'] ?? 'Empresa não encontrada'); ?>
        </strong>
    </h5>
</div>

    <?php echo $mensagem; ?>
<?php if (!empty($linhasIgnoradas)) { ?>

<div class="alert alert-warning">
    <b>Linhas ignoradas:</b><br><br>

    <?php foreach ($linhasIgnoradas as $erro) { ?>

        <b>Linha:</b> <?php echo $erro['linha']; ?><br>
        <b>Motivo:</b> <?php echo $erro['motivo']; ?><br>
        <b>Dados:</b> <?php echo $erro['dados']; ?><br>
        <hr>

    <?php } ?>
</div>

<?php } ?>
    <div class="card">
        <div class="card-body">

       <div class="text-center mb-3">
    <i class="bi bi-file-earmark-arrow-up" style="font-size:48px;"></i>
</div>

<div class="titulo-upload">
    Selecione o arquivo CSV/TXT para importar as pessoas desta empresa.
</div>
            <form method="post" enctype="multipart/form-data">

                <div class="form-group">
                   <label><strong>Selecione o arquivo</strong></label>

                    <input
                        type="file"
                        name="arquivo"
                        class="form-control"
                        accept=".csv,.txt"
                        required>
                </div>

                <div class="text-center mt-4">
    <button type="submit" class="btn btn-success mr-2">
        <i class="bi bi-upload"></i> Importar
    </button>

    <a href="pag1.php?pagina=PESSOAS&empresa_id=<?php echo $empresa_id; ?>"
       class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar</a>
</div>

            </form>
        </div>
    </div>

</div>

</body>
</html>