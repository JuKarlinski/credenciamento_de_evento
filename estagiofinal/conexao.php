<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "estagio";

$erroConexao = "";
$conexao = null;

try {
    $conexao = new mysqli($host, $usuario, $senha, $banco);
} catch (mysqli_sql_exception $e) {

    $erroConexao = "Não foi possível conectar ao banco de dados. Tente novamente ou contate o suporte.";

    error_log($e->getMessage());
}
?>