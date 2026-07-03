<?php
require 'vendor/autoload.php';
require 'conexao.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();

$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';

$sql = "
SELECT
    ID,
    NOME,
    CONTROLA_ESPACOS,
    LIMITE_PESSOAS
FROM TIPOS
WHERE 1=1
";


if (!empty($nome)) {
    $nome = $conexao->real_escape_string($nome);

 $sql .= " AND NOME LIKE '%$nome%'";
}

$sql .= " ORDER BY NOME";

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

<h2>Relatório de Tipos</h2>

<table>
<tr>
    <th>ID</th>
    <th>Nome</th>
    <th>Controla Espaços</th>
    <th>Limite Pessoas</th>
</tr>
';
while ($linha = $result->fetch_assoc()) {

    $html .= '
    <tr>
        <td>'.$linha['ID'].'</td>
        <td>'.$linha['NOME'].'</td>
        <td>'.$linha['CONTROLA_ESPACOS'].'</td>
        <td>'.$linha['LIMITE_PESSOAS'].'</td>
    </tr>';
}
$html .= '
</table>

</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("relatorios_tipos.pdf", ["Attachment" => false]);