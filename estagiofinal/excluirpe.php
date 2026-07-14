<?php
include_once('conexao.php');
include_once("logs.php");

if(isset($_GET['id'])){

    $id = intval($_GET['id']);
    $busca = $conexao->query("
        SELECT * FROM pessoas
        WHERE ID = $id
    ");
    if ($busca && $busca->num_rows > 0) {
    $pessoa = $busca->fetch_assoc();

    $dadosLog =
        "ID=" . $pessoa['ID'] . "," .
        "NOME=" . $pessoa['NOME'] . "," .
        "CPF=" . $pessoa['CPF'];
        
    registrarLog(
        'EXCLUSAO',
        'pessoas',
        $dadosLog
    );
}
    $sql = "DELETE FROM pessoas WHERE ID = $id";
    $conexao->query($sql);
    $check = $conexao->query("
        SELECT COUNT(*) as total 
        FROM pessoas
    ");
    $dados = $check->fetch_assoc();
    if($dados['total'] == 0){

        $conexao->query("
            ALTER TABLE pessoas 
            AUTO_INCREMENT = 1
        ");
    }
    header("Location: pag1.php?pagina=pessoas");
    exit;
}
?>