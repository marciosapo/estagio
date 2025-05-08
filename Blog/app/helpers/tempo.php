<?php
function tempoDecorrido($data) {
    $dataComentario = new DateTime($data);
    $agora = new DateTime();
    $diferenca = $agora->diff($dataComentario);

    if ($diferenca->y > 0) {
        return "há " . $diferenca->y . " ano" . ($diferenca->y > 1 ? "s" : "");
    } elseif ($diferenca->m > 0) {
        return "há " . $diferenca->m . " mês" . ($diferenca->m > 1 ? "es" : "");
    } elseif ($diferenca->d > 0) {
        return "há " . $diferenca->d . " dia" . ($diferenca->d > 1 ? "s" : "");
    } elseif ($diferenca->h > 0) {
        return "há " . $diferenca->h . " hora" . ($diferenca->h > 1 ? "s" : "");
    } elseif ($diferenca->i > 0) {
        return "há " . $diferenca->i . " minuto" . ($diferenca->i > 1 ? "s" : "");
    } else {
        return "agora mesmo";
    }
}