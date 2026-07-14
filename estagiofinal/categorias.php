<?php
if ($_SESSION['CATEGORIA_ID'] == 3) {
    header("Location: pag1.php?pagina=empresas");
    exit;
}
?>
<?php
include_once("conexao.php");

$sql = "SELECT * FROM categorias";
$result = $conexao->query($sql);
?>
<style>
.fundo-titulo {
  background-image: url('img/fundologin.png');
  background-size: cover;
  padding: 15px;
  border-radius: 10px;
}
.table td, .table th {
  text-align: center;
}
</style>
<div class="fundo-titulo">
  <h2>CATEGORIAS</h2>
</div>
<br>
<table class="table table-striped">
  <tr>
    <th>ID</th>
    <th>Nome</th>
  </tr>
  <?php while($c = $result->fetch_assoc()) { ?>
    <tr>
      <td><?= $c['id'] ?></td>
      <td><?= $c['nome'] ?></td>
    </tr>
  <?php } ?>
</table>