<?php
require 'vendor/autoload.php';
require 'conexao.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();

$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';
$empresa_id = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0;

$sql = "
SELECT
    usuarios.ID,
    usuarios.NOME,
    usuarios.EMAIL,
    categorias.NOME AS CATEGORIA,
    empresas.NOME_FANTASIA AS EMPRESA
FROM usuarios
LEFT JOIN categorias ON usuarios.CATEGORIA_ID = categorias.ID
LEFT JOIN empresas ON usuarios.EMPRESA_ID = empresas.ID
WHERE 1=1
";

if ($empresa_id > 0) {
    $sql .= " AND usuarios.EMPRESA_ID = $empresa_id";
}

if (!empty($nome)) {
    $nome = $conexao->real_escape_string($nome);

    $sql .= " AND (
        usuarios.NOME LIKE '%$nome%'
        OR usuarios.EMAIL LIKE '%$nome%'
    )";
}

$sql .= " ORDER BY usuarios.NOME";

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

<h2>Relatório de Usuários</h2>

<table>
<tr>
    <th>ID</th>
    <th>Nome</th>
    <th>Login</th>
    <th>Categoria</th>
    <th>Empresa</th>
</tr>
';

while ($linha = $result->fetch_assoc()) {

    $html .= '
    <tr>
        <td>'.$linha['ID'].'</td>
        <td>'.$linha['NOME'].'</td>
        <td>'.$linha['EMAIL'].'</td>
        <td>'.$linha['CATEGORIA'].'</td>
        <td>'.$linha['EMPRESA'].'</td>
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
    "relatorio_usuarios.pdf",
    ["Attachment" => false]
);