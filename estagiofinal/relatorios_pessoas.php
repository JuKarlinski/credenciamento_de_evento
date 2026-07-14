<?php
require 'vendor/autoload.php';
require 'conexao.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();

$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';
session_start();

$categoria = $_SESSION['CATEGORIA_ID'] ?? null;
$empresa_id = $_SESSION['EMPRESA_ID'] ?? 0;

$sql = "
SELECT
    p.ID,
    p.NOME,
    p.INGRESSO_PERMANENTE,
    p.CPF,
    p.DOCUMENTO,
    p.TELEFONE,
    e.NOME_FANTASIA,
    c.NOME AS CARGO_NOME
FROM pessoas p
LEFT JOIN empresas e ON p.EMPRESA_ID = e.ID
LEFT JOIN cargos c ON p.CARGO_ID = c.ID
WHERE 1=1
";

if ($categoria == 3 && $empresa_id > 0) {
    $sql .= " AND p.EMPRESA_ID = " . intval($empresa_id);
}

if (!empty($nome)) {
    $nome = $conexao->real_escape_string($nome);
    $sql .= " AND p.NOME LIKE '%$nome%'";
}

$result = $conexao->query($sql);

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body{
    font-family: Arial, sans-serif;
    font-size: 12px;
}

h2{
    text-align:center;
}

table{
    width:100%;
    border-collapse:collapse;
}

th, td{
    border:1px solid #000;
    padding:5px;
    text-align:center;
}

th{
    background:#f2f2f2;
}
</style>
</head>
<body>

<h2>Relatório de Pessoas</h2>

<table>
<tr>
    <th>ID</th>
    <th>Empresa</th>
    <th>Nome</th>
    <th>Ingresso Permanente</th>
    <th>CPF</th>
    <th>Documento</th>
    <th>Telefone</th>
    <th>Cargo</th>
</tr>
';

while ($linha = $result->fetch_assoc()) {

    $html .= '
    <tr>
        <td>'.$linha['ID'].'</td>
        <td>'.$linha['NOME_FANTASIA'].'</td>
        <td>'.$linha['NOME'].'</td>
        <td>'.$linha['INGRESSO_PERMANENTE'].'</td>
        <td>'.$linha['CPF'].'</td>
        <td>'.$linha['DOCUMENTO'].'</td>
        <td>'.$linha['TELEFONE'].'</td>
        <td>'.$linha['CARGO_NOME'].'</td>
    </tr>';
}

$html .= '
</table>

</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream(
    "relatorio_pessoas.pdf",
    ["Attachment" => false]
);

