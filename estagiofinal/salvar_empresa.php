<?php
$tipo_id = $_POST['tipo_id'];

$tipo = $conexao->query("
    SELECT CONTROLA_ESPACOS 
    FROM tipos 
    WHERE ID = $tipo_id
")->fetch_assoc();

if ($tipo['CONTROLA_ESPACOS'] == 'S') {
    $quantidade = $_POST['quantidade_espacos'];
} else {
    $quantidade = 0;
}

$sql = "INSERT INTO empresas 
(TIPO_ID, QUANTIDADE_ESPACOS, NOME_FANTASIA, RAZAO_SOCIAL, CNPJ)
VALUES 
($tipo_id, $quantidade, '$nome', '$razao_social', '$cnpj')";
?>