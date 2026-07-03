<?php
require 'vendor/autoload.php';
require 'conexao.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();

$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';
$empresa_id = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0;

$sql = "
SELECT
    CARGOS.ID,
    CARGOS.NOME,
    EMPRESAS.NOME_FANTASIA AS EMPRESA
FROM CARGOS
INNER JOIN EMPRESAS
    ON EMPRESAS.ID = CARGOS.ID_EMPRESA
WHERE 1=1
";

if (!empty($nome)) {
    $nome = $conexao->real_escape_string($nome);
    $sql .= " AND CARGOS.NOME LIKE '%$nome%'";
}

if ($empresa_id > 0) {
    $sql .= " AND CARGOS.ID_EMPRESA = '$empresa_id'";
}

$sql .= " ORDER BY CARGOS.NOME";

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

<h2>Relatório de Cargos</h2>

<table>
<tr>
    <th>ID</th>
    <th>Nome do Cargo</th>
    <th>Empresa</th>
</tr>
';

while ($linha = $result->fetch_assoc()) {

    $html .= '
    <tr>
        <td>' . $linha['ID'] . '</td>
        <td>' . htmlspecialchars($linha['NOME']) . '</td>
        <td>' . htmlspecialchars($linha['EMPRESA']) . '</td>
    </tr>';
}

$html .= '
</table>

</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("relatorio_cargos.pdf", ["Attachment" => false]);
?>