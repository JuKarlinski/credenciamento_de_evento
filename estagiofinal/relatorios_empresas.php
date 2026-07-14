<?php
session_start();
require 'vendor/autoload.php';
require 'conexao.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();

$categoria = $_SESSION['CATEGORIA_ID'] ?? null;
$empresa_id = $_SESSION['EMPRESA_ID'] ?? null;

$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';

$sql = "
SELECT
    e.ID,
    e.ANO,
    e.NOME_FANTASIA,
    e.RAZAO_SOCIAL,
    e.CNPJ,
    e.QUANTIDADE_ESPACOS,
    t.NOME AS TIPO
FROM empresas e
LEFT JOIN tipos t ON e.TIPO_ID = t.ID
WHERE 1=1
";

if ($categoria == 3 && !empty($empresa_id)) {
    $sql .= " AND e.ID = " . intval($empresa_id);
}

if (!empty($nome)) {
    $nome = $conexao->real_escape_string($nome);

    $sql .= " AND (
        e.NOME_FANTASIA LIKE '%$nome%'
        OR e.RAZAO_SOCIAL LIKE '%$nome%'
        OR e.CNPJ LIKE '%$nome%'
    )";
}

$result = $conexao->query($sql);

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body{ font-family: Arial; font-size: 12px; }
h2{ text-align:center; }
table{ width:100%; border-collapse:collapse; }
th, td{ border:1px solid #000; padding:5px; text-align:center; }
th{ background:#f2f2f2; }
</style>
</head>
<body>

<h2>Relatório de Empresas</h2>

<table>
<tr>
    <th>ID</th>
    <th>Empresa</th>
    <th>Tipo</th>
    <th>Ano</th>
    <th>CNPJ</th>
    <th>Razão Social</th>
    <th>Espaços</th>
</tr>
';

while ($linha = $result->fetch_assoc()) {
    $html .= '
    <tr>
        <td>'.$linha['ID'].'</td>
        <td>'.$linha['NOME_FANTASIA'].'</td>
        <td>'.$linha['TIPO'].'</td>
        <td>'.$linha['ANO'].'</td>
        <td>'.$linha['CNPJ'].'</td>
        <td>'.$linha['RAZAO_SOCIAL'].'</td>
        <td>'.$linha['QUANTIDADE_ESPACOS'].'</td>
    </tr>';
}

$html .= '
</table>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("relatorio_empresas.pdf", ["Attachment" => false]);
?>