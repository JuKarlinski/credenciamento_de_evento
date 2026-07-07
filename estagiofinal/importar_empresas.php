<?php
include_once("conexao.php");
include_once("logs.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (($_SESSION['CATEGORIA_ID'] ?? null) != 1) {
    die("Acesso negado");
}

$mensagem = '';

if (isset($_POST['importar'])) {
    if (!empty($_FILES['arquivo']['tmp_name'])) {
        $caminhoArquivo = $_FILES['arquivo']['tmp_name'];
        
    
        $linhasArquivo = file($caminhoArquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($linhasArquivo !== false) {
            $tipos = [];
            $sqlTipos = $conexao->query("
                SELECT ID, NOME 
                FROM TIPOS
            ");

            while ($tipo = $sqlTipos->fetch_assoc()) {
               $tipos[strtoupper(trim($tipo['NOME']))] = $tipo['ID'];
            }


            array_shift($linhasArquivo);

          $importadas = 0;
          $ignoradas = 0;
          $linhasIgnoradas = [];
          $numeroLinha = 1;

            foreach ($linhasArquivo as $linhaBruta) {
                $numeroLinha++;

                $linhaConvertida = mb_convert_encoding(
                    trim($linhaBruta),
                    'UTF-8',
                    'UTF-8, ISO-8859-1, Windows-1252'
                );

                $colunas = explode(';', $linhaConvertida);

               $nome_fantasia = mb_strtoupper(trim($colunas[0] ?? ''), 'UTF-8');
               $razao_social  = mb_strtoupper(trim($colunas[1] ?? ''), 'UTF-8');
               $tipo_nome     = strtoupper(trim($colunas[2] ?? ''));


               $tipo_id = $tipos[$tipo_nome] ?? null;
    if (empty($nome_fantasia) && empty($razao_social) && empty($tipo_nome)) {
    $linhasIgnoradas[] = [
        'linha' => $numeroLinha,
        'dados' => $linhaBruta,
        'motivo' => 'Linha vazia ou inválida'
    ];
    $ignoradas++;
    continue;
}
    if ($nome_fantasia === '' && $razao_social === '') {
    $linhasIgnoradas[] = [
        'linha' => $numeroLinha,
        'dados' => $linhaBruta,
        'motivo' => 'Nome fantasia e razão social vazios'
    ];
    $ignoradas++;
    continue;
}

                $ano = 2026;

    $verifica = $conexao->prepare("
    SELECT ID
    FROM EMPRESAS
    WHERE UPPER(NOME_FANTASIA) = UPPER(?)
    AND ANO = ?
");

$verifica->bind_param("si", $nome_fantasia, $ano);
$verifica->execute();
$resultado = $verifica->get_result();


               if ($resultado->num_rows > 0) {
    $linhasIgnoradas[] = [
        'linha' => $numeroLinha,
        'dados' => $linhaBruta,
        'motivo' => 'Empresa já cadastrada'
    ];
    $ignoradas++;
    $verifica->close();
    continue;
}
                $verifica->close();

$stmt = $conexao->prepare("
    INSERT INTO EMPRESAS (ANO, NOME_FANTASIA, RAZAO_SOCIAL, TIPO_ID)
VALUES (?, ?, ?, ?)
");

$stmt->bind_param("issi", $ano, $nome_fantasia, $razao_social, $tipo_id);

                if ($stmt->execute()) {
                    $empresa_id = $conexao->insert_id;

                    $nome_fantasia_limpo = str_replace([';', "\r", "\n"], [' ', ' ', ' '], $nome_fantasia);
                    $razao_social_limpo  = str_replace([';', "\r", "\n"], [' ', ' ', ' '], $razao_social);

                    $dadosLog = "ID=" . $empresa_id . " | " .
                                "NOME_FANTASIA=" . trim($nome_fantasia_limpo) . " | " .
                                "RAZAO_SOCIAL=" . trim($razao_social_limpo) . " | " .
                                "TIPO_ID=" . $tipo_id;

                    registrarLog('IMPORTACAO', 'EMPRESAS', $dadosLog);
                    $importadas++;
               } else {
    $linhasIgnoradas[] = [
        'linha' => $numeroLinha,
        'dados' => $linhaBruta,
        'motivo' => 'Erro ao inserir no banco'
    ];
    $ignoradas++;
}
                $stmt->close();
            }
        }
$mensagem = "Importação concluída: $importadas empresas importadas com sucesso!";

if ($ignoradas > 0) {
    $mensagem .= "<br>$ignoradas linhas ignoradas.";
}  
}
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>IMPORTAR EMPRESAS</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
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
        <h2>IMPORTAR EMPRESAS</h2>
        <h5>
            Importação de empresas para o sistema
        </h5>

    </div>

    <?php if (!empty($mensagem)) { ?>
        <div class="alert alert-info">
            <?php echo $mensagem; ?>
        </div>
    <?php } ?>

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
                <i class="bi bi-building-add" style="font-size:48px;"></i>
            </div>

            <div class="titulo-upload">
                Selecione o arquivo CSV/TXT para importar empresas.
            </div>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">

                    <label>
                        <strong>Selecione o arquivo</strong>
                    </label>

                    <input
                        type="file"
                        name="arquivo"
                        class="form-control"
                        accept=".txt,.csv"
                        required>

                </div>

                <div class="text-center mt-4">

                    <button
                        type="submit"
                        name="importar"
                        class="btn btn-success mr-2">

                        <i class="bi bi-upload"></i>
                        Importar

                    </button>

                    <a href="pag1.php?pagina=EMPRESAS"
                       class="btn btn-secondary">

                        <i class="bi bi-arrow-left"></i>
                        Voltar

                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>