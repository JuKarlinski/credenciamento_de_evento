<?php
include_once("conexao.php");
include_once(__DIR__ . "/phpqrcode/qrlib.php");

$idPessoa = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($idPessoa <= 0) {
    die("Pessoa não informada.");
}

$sql = $conexao->query("
    SELECT
        p.ID AS PESSOA_ID,
        p.NOME AS NOME_PESSOA,
        p.FOTO,
        e.ID AS EMPRESA_ID,
        e.NOME_FANTASIA AS EMPRESA,
        c.NOME AS CARGO
    FROM pessoas p
    LEFT JOIN empresas e ON e.ID = p.EMPRESA_ID
    LEFT JOIN cargos c ON c.ID = p.CARGO_ID
    WHERE p.ID = $idPessoa
");

$d = $sql->fetch_assoc();

if (!$d) {
    die("Pessoa não encontrada.");
}

function formatar6($id)
{
    return str_pad($id, 6, "0", STR_PAD_LEFT);
}

$idEmpresa = formatar6($d['EMPRESA_ID']);
$idPessoaF = formatar6($d['PESSOA_ID']);

$codigoQR = $idEmpresa . $idPessoaF;

if (!is_dir("qrcodes")) {
    mkdir("qrcodes", 0777, true);
}

$qrFile = "qrcodes/$codigoQR.png";
QRcode::png($codigoQR, $qrFile, QR_ECLEVEL_L, 6);

if (!file_exists($qrFile)) {
    die("Erro ao gerar QR Code.");
}


$img = imagecreatetruecolor(320, 480);

$fundoPath = "img/cracha.png";

if (file_exists($fundoPath)) {

    $fundo = imagecreatefromstring(file_get_contents($fundoPath));

    imagecopyresampled(
        $img,
        $fundo,
        0,
        0,
        0,
        0,
        320,
        480,
        imagesx($fundo),
        imagesy($fundo)
    );

} else {

    $bg = imagecolorallocate($img,245,245,245);
    imagefilledrectangle($img,0,0,320,480,$bg);
}

$black = imagecolorallocate($img,0,0,0);
$gray  = imagecolorallocate($img,90,90,90);
$white = imagecolorallocate($img,255,255,255);


$fotoW = 150;
$fotoH = 150;
$fotoX = 35;   
$fotoY = 170;

$qrW = 110;
$qrH = 120;
$qrX = 190;   
$qrY = 170;


$empresa = strtoupper($d['EMPRESA'] ?? '-');

imagestring(
    $img,
    5,
    (320 - strlen($empresa) * 9) / 2,
    90,
    $empresa,
    $white
);

if (!empty($d['FOTO']) && file_exists("img/".$d['FOTO'])) {

    $foto = imagecreatefromstring(file_get_contents("img/".$d['FOTO']));

    if ($foto !== false) {

        imagecopyresampled(
            $img,
            $foto,
            $fotoX,
            $fotoY,
            0,
            0,
            $fotoW,
            $fotoH,
            imagesx($foto),
            imagesy($foto)
        );

        imagerectangle(
            $img,
            $fotoX - 1,
            $fotoY - 1,
            $fotoX + $fotoW + 1,
            $fotoY + $fotoH + 1,
            $white
        );

        imagerectangle(
            $img,
            $fotoX,
            $fotoY,
            $fotoX + $fotoW,
            $fotoY + $fotoH,
            $black
        );
    }
}

$nome = strtoupper($d['NOME_PESSOA'] ?? '-');

imagestring(
    $img,
    5,
    (160 - strlen($nome) * 9) / 2,
    330,
    $nome,
    $black
);

$qrImg = imagecreatefrompng($qrFile);

if ($qrImg !== false) {

    imagecopyresampled(
        $img,
        $qrImg,
        $qrX,
        $qrY,
        0,
        0,
        $qrW,
        $qrH,
        imagesx($qrImg),
        imagesy($qrImg)
    );

    imagedestroy($qrImg);
}

$cargo = trim($d['CARGO'] ?? '');

if ($cargo != '') {

    $boxColor = imagecolorallocate($img,0,0,0);
    $whiteText = imagecolorallocate($img,255,255,255);

    imagefilledrectangle(
        $img,
        0,
        400,
        320,
        480,
        $boxColor
    );

    imagestring(
        $img,
        5,
        (320 - strlen($cargo) * 9) / 2,
        435,
        strtoupper($cargo),
        $whiteText
    );
}

$arquivoFinal = "cracha/cracha_" . $idPessoa . ".png";

imagepng($img, $arquivoFinal);
imagedestroy($img);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Crachá</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        body{
            background:#eaeaea;
        }
        .cracha-container{
            margin-top:25px;
        }
        .cracha-card{
            display:inline-block;
            background:#fff;
            padding:12px;
            border-radius:10px;
            box-shadow:0 3px 12px rgba(0,0,0,.15);
        }
        .cracha-img{
            width:320px;
            border-radius:8px;
        }
        .btn-download{
            margin-top:10px;
            font-size:13px;
            padding:6px 16px;
        }
    </style>

</head>
<body>
<div class="container text-center cracha-container">

    <div class="cracha-card">
        <img src="<?php echo $arquivoFinal . '?' . time(); ?>"  class="cracha-img"  alt="Crachá">   
        <div>
            <a href="<?php echo $arquivoFinal; ?>"   download   class="btn btn-dark btn-download">Baixar Crachá  </a>
        </div>
    </div>
</div>
</body>
</html>