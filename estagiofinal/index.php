<?php
session_set_cookie_params([
    'path' => '/',
    'httponly' => true
]);
session_start();

include_once('conexao.php');
include_once('logs.php');

$erro = '';

if (!empty($erroConexao)) {
    $erro = $erroConexao;
}

if ($_POST && empty($erroConexao)) {

    $email = trim(strip_tags($_POST['email']));
    $senha = md5($_POST['senha']);

    if (empty($email) || empty($_POST['senha'])) {

        $erro = "Preencha todos os campos!";

    } else {

        $sql = "SELECT * FROM usuarios
                WHERE EMAIL = '$email'
                AND SENHA = '$senha'";

        $resultado = mysqli_query($conexao, $sql);

        $dados = mysqli_fetch_assoc($resultado);

        if ($dados) {

            $_SESSION['nome'] = $email;
            $_SESSION['ID'] = $dados['ID'];
            $_SESSION['CATEGORIA_ID'] = $dados['CATEGORIA_ID'];
            $_SESSION['EMPRESA_ID'] = $dados['EMPRESA_ID'];

            registrarLog(
            'LOGIN',
            'usuarios',
            'ID=' . $dados['ID'] . ',EMAIL=' . $email
);

            header("Location: pag1.php");
            exit;

        } else {
            $erro = "E-mail ou senha inválidos!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br"> 

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <style>    
        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('img/fundologin.png');
            background-size: cover;
            height: 50px;
            background-repeat: no-repeat;
        }
        .card{
    width:15px;
    border-radius:15px;
}
    </style>
</head>

<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow" style="width: 450px; border-radius:15px;">

        <h2 class="text-center">Login</h2>
       <form method="POST" autocomplete="off">
            <div class="mb-3">

             <label for="email" class="form-label">E-mail</label>
<input
    type="email"
    id="email"
    name="email"
    class="form-control form-control-lg"
    autocomplete="off"
    value=""
    required>

           <label for="senha" class="form-label">Senha</label>
<div class="input-group">
    <input
        type="password"
        id="senha"
        name="senha"
        class="form-control form-control-lg"
        autocomplete="new-password"
        value=""
        required>

    <div class="input-group-append">
        <button class="btn btn-outline-secondary"
                type="button"
                onclick="mostrarSenha()">
            👁
        </button>
    </div>
</div>
<br>
            <button type="submit"class="btn btn-dark w-100">Entrar</button>
        </form>

        <br>

        <div class="row">
            <div class="col-12">
                <?php if(!empty($erro)) { ?>
                    <div class="alert alert-danger text-center mt-3">
                        <?php echo $erro; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <script>
function mostrarSenha() {
    var campo = document.getElementById("senha");

    if (campo.type === "password") {
        campo.type = "text";
    } else {
        campo.type = "password";
    }
}
</script>
</body>
</html>