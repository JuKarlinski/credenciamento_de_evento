<?php
session_set_cookie_params(['path' => '/']);
session_start();
if(empty($_SESSION['nome'])){
    header("Location: index.php");
    exit;
    var_dump(session_id());
exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <style>
.fundo-direita {
    background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
                      url('img/fundopag1.png');
    background-size: cover;
    background-position: center;
}
.nav-custom {
    background-color: #929292;
}
.menu-custom {
    background-color: #000000;
    height: 91.2vh;
}
.menu-custom a {
    color: white;
}
.menu-custom a:hover {
    background-color: #495057;
    text-decoration: none;
}
</style>
  <title>Sistema de Credenciamento</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
</head>
<body style="overflow: hidden;">
<nav class="navbar navbar-expand-md navbar-dark nav-custom">
 <a class="navbar-brand">SISTEMA DE CREDENCIAMENTO</a>
  
 <div class="ml-auto d-flex align-items-center text-white">
<?php
$nome = $_SESSION['nome'] ?? '';
if($nome != ''){
    $nome = explode("@", $nome)[0];
    $nome = explode("-", $nome)[0];
    $nome = ucfirst(strtolower($nome));
} else {
    header("Location: index.php");
    exit;
}
?>

<span class="mr-2">Bem-vindo, <?php echo $nome; ?></span>
<img src="img/cabe.png" alt="foto" 
     style="width:50px; height:35px; border-radius:50%;">
</div>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
    <span class="navbar-toggler-icon"></span>
  </button>
</nav>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-3 col-lg-2 menu-custom text-white">
<a href="pag1.php" class="d-block text-white p-2">
    <i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="?pagina=empresas" class="d-block text-white p-2">
    <i class="bi bi-building"></i> Empresas</a>
    <a href="?pagina=pessoas" class="d-block text-white p-2">
    <i class="bi bi-people"></i> Pessoas</a>

<?php if (isset($_SESSION['CATEGORIA_ID']) && $_SESSION['CATEGORIA_ID'] == 1) { ?>
<a href="?pagina=usuarios" class="d-block text-white p-2">
    <i class="bi bi-person-circle"></i> Usuários</a>
<?php } ?>

<?php if (($_SESSION['CATEGORIA_ID'] ?? null) != 3) { ?>
<a href="?pagina=tipos" class="d-block text-white p-2">
    <i class="bi bi-tags"></i> Tipos</a>
<a href="?pagina=cargos" class="d-block text-white p-2">
    <i class="bi bi-briefcase"></i> Cargos</a>
<?php } ?>
<?php if (isset($_SESSION['CATEGORIA_ID']) && $_SESSION['CATEGORIA_ID'] == 1) { ?>
<a href="?pagina=logs_visualizar" class="d-block text-white p-2">
    <i class="fas fa-clipboard-list"></i> Logs do Sistema</a>
<?php } ?>
<a href="index.php" class="d-block text-white p-2">
    <i class="bi bi-box-arrow-right"></i> Sair</a>


</div>

<?php
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : '';

?>
    <div class="col-md-9 col-lg-10 p-0 <?php echo ($pagina == '') ? 'fundo-direita' : ''; ?>" 
     style="height: calc(100vh - 56px); overflow-y: auto;">
     <div class="container mt-4">

<?php

if($pagina == "empresas"){
    include("empresas.php");
} elseif($pagina == "cadastro"){
    include("cadastro.php");
} elseif($pagina == "alterar"){
    include("alterar.php");
}  elseif($pagina == "pessoas"){
    include("pessoas.php");
}elseif($pagina == "cadastro1"){
    include("cadastro1.php");
}elseif($pagina == "alterarpe"){
    include("alterarpe.php");
}elseif($pagina == "cargos"){
    include("cargos.php");
}elseif($pagina == "tipos"){
    include("tipos.php");
}elseif($pagina == "usuarios"){
    include("usuarios.php");
}elseif($pagina == "categorias"){
    include("categorias.php");
}elseif($pagina == "novousuario"){
    include("novousuario.php");
}elseif($pagina == "editar"){
    include("editar.php");
}elseif ($pagina == "logs_visualizar") {
    include_once("logs_visualizar.php");
}elseif ($pagina == "importar_empresas") {
    include_once("importar_empresas.php");
}elseif ($pagina == "importar_pessoas") {
    include_once("importar_pessoas.php");
}



?>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
