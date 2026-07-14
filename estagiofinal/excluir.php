<?php
include_once('conexao.php');
include_once("logs.php");

if ($_SESSION['categoria'] != 1) {
    exit("Sem permissão");
}

if(isset($_GET['id'])){

    $id = intval($_GET['id']);
    $busca = $conexao->query("
        SELECT * FROM empresas 
        WHERE ID = $id
    ");

   if ($busca && $busca->num_rows > 0) {
    $empresa = $busca->fetch_assoc();

    $dadosLog =
        "ID=" . $empresa['ID'] . "," .
        "NOME=" . $empresa['NOME_FANTASIA'] . "," .
        "CNPJ=" . $empresa['CNPJ'];

    registrarLog(
        'EXCLUSAO',
        'empresas',
        $dadosLog
    );
}
    $sql = "DELETE FROM empresas WHERE ID = $id";

    $conexao->query($sql);

    $check = $conexao->query("
        SELECT COUNT(*) as total 
        FROM empresas
    ");

    $dados = $check->fetch_assoc();

    if($dados['total'] == 0){

        $conexao->query("
            ALTER TABLE empresas 
            AUTO_INCREMENT = 1
        ");

    }

    header("Location: pag1.php?pagina=empresas");
    exit;
}
?>