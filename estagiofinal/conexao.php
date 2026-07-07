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

    // mensagem amigável para o usuário
    $erroConexao = "Não foi possível conectar ao banco de dados. Tente novamente ou contate o suporte.";

    // erro técnico fica salvo no log (não aparece na tela)
    error_log($e->getMessage());
}
?>