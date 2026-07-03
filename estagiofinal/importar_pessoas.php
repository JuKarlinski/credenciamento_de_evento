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
            $erros = 0;
            $duplicados = 0;

            foreach ($linhas as $linha) {

                $linha = trim($linha);

                if (empty($linha)) {
                    continue;
                }

                $dados = explode(';', $linha);

                $nome      = trim($dados[0] ?? '');
                $cpf       = trim($dados[1] ?? '');
                $documento = trim($dados[2] ?? '');
                $telefone  = trim($dados[3] ?? '');

                if (empty($nome)) {
                    continue;
                }
                if (!empty($documento)) {

              $verifica = $conexao->prepare("
              SELECT ID
              FROM pessoas
              WHERE DOCUMENTO = ?
    ");

             $verifica->bind_param("s", $documento);

} else {

             $verifica = $conexao->prepare("
             SELECT ID
            FROM pessoas
            WHERE UPPER(NOME) = UPPER(?)
    ");

            $verifica->bind_param("s", $nome);
}  

            $verifica->execute();
            $resultado = $verifica->get_result();

           if ($resultado->num_rows > 0) {
    $duplicados++;
    $verifica->close();
    continue;
}
            $verifica->close();

                $stmt = $conexao->prepare("
                    INSERT INTO pessoas
                    (
                        EMPRESA_ID,
                        NOME,
                        CPF,
                        DOCUMENTO,
                        TELEFONE,
                        INGRESSO_PERMANENTE,
                        FOTO,
                        CARGO_ID
                    )
                    VALUES
                    (
                        ?, ?, ?, ?, ?, '', '', NULL
                    )
                ");

                if (!$stmt) {
                    $erros++;
                    continue;
                }

                $stmt->bind_param(
                    "issss",
                    $empresa_id,
                    $nome,
                    $cpf,
                    $documento,
                    $telefone
                );

                if ($stmt->execute()) {

                    registrarLog(
                        'IMPORTACAO',
                        'PESSOAS',
                        "EMPRESA_ID=$empresa_id,NOME=$nome,CPF=$cpf,DOCUMENTO=$documento,TELEFONE=$telefone"
                    );

                    $importados++;

                } else {

                    $erros++;
                }

                $stmt->close();
            }


if ($importados > 0 && $duplicados == 0 && $erros == 0) {

    $mensagem = "
        <div class='alert alert-success'>
            $importados pessoa(s) importada(s) com sucesso!
        </div>
    ";

} else {

    if ($duplicados > 0) {
        $mensagem .= "
            <div class='alert alert-warning'>
                $duplicados linha(s) duplicada(s) ignorada(s).
            </div>
        ";
    }

    if ($erros > 0) {
        $mensagem .= "
            <div class='alert alert-danger'>
                $erros registro(s) não puderam ser importados.
            </div>
        ";
    }

    if ($importados > 0) {
        $mensagem .= "
            <div class='alert alert-info'>
                $importados pessoa(s) importada(s) com sucesso!
            </div>
        ";
    }
}
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