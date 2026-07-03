<?php
date_default_timezone_set('America/Sao_Paulo');

function registrarLog($tipo, $tabela, $dadosAntigos, $dadosNovos = null)
{
    try {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $usuario = $_SESSION['ID'] ?? 0;
        $mes = date('Y-m');
        $dia = date('Y-m-d');
        $pasta = __DIR__ . "/logs/$mes";

        if (!is_dir($pasta)) {
            mkdir($pasta, 0755, true);
        }

        $arquivo = "$pasta/$dia.txt";

        if (rand(1, 100) === 50) {
            $limite = time() - (90 * 24 * 60 * 60);
            foreach (glob(__DIR__ . "/logs/*/*.txt") as $file) {
                if (is_file($file) && filemtime($file) < $limite) {
                    unlink($file);
                }
            }
        }

        $dataHora = date('Y-m-d H:i:s');

        $dadosAntigosClean = str_replace(';', ' ', $dadosAntigos);
        $dadosNovosClean   = $dadosNovos !== null ? str_replace(';', ' ', $dadosNovos) : '';

        $dadosAntigosClean = preg_replace('/[\r\n\t]+/', ' ', $dadosAntigosClean);
        $dadosNovosClean   = preg_replace('/[\r\n\t]+/', ' ', $dadosNovosClean);

        $dadosAntigosClean = trim(preg_replace('/\s+/', ' ', $dadosAntigosClean));
        $dadosNovosClean   = trim(preg_replace('/\s+/', ' ', $dadosNovosClean));

        $linha = $usuario . ";" .
                 $tipo . ";" .
                 $tabela . ";" .
                 $dataHora . ";" .
                 $dadosAntigosClean . ";" .
                 $dadosNovosClean . "\n"; 

        file_put_contents($arquivo, $linha, FILE_APPEND | LOCK_EX);

    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}
?>