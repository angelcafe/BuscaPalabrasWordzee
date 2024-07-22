<?php
spl_autoload_register(function ($nombre_clase) {
    require $nombre_clase . '.php';
});
if (!empty($_POST['ronda'])) {
    $pe_permitidos = ['DP', 'TP', 'DL', 'TL', ''];
    $puntos_extra = [];
    for ($x = 0; $x < 5; $x++) {
        for ($y = 0; $y < 3 + $x; $y++) {
            $tmp = 'pal' . ($x + 3) . 'let' . ($y + 1);
            $puntos_extra[$x][$y] = (isset($_POST[$tmp]) && in_array($_POST[$tmp], $pe_permitidos)) ? ($_POST[$tmp]) : ('');
        }
    }
    $letras_disponibles = [];
    $post_existe = true;
    for ($i = 0; $i < 7; $i++) {
        if (isset($_POST['letrasDisponibles'][$i]) && preg_match('/[A-JL-VX-ZÑa-jl-vx-zñ]/', $_POST['letrasDisponibles'][$i])) {
            $letras_disponibles[$i] = mb_strtoupper($_POST['letrasDisponibles'][$i]);
        } else {
            $letras_disponibles[$i] = '';
            $post_existe = false;
        }
    }

    if ($post_existe) {
        $ronda = intval($_POST['ronda']);

        $busca_palabras = new Palabras($letras_disponibles);
        $resultado = $busca_palabras->getResultado();
        $palabras = $busca_palabras->getPalabras();
        $palabras_puntos['encontradas'] = palabrasPuntos($resultado, $puntos_extra, $ronda);
        $palabras_puntos['sugeridas'] = palabrasPuntos($palabras, $puntos_extra, $ronda);
        arsort($palabras_puntos['encontradas']);
        arsort($palabras_puntos['sugeridas']);
        $palabras_puntos['sugeridas'] = palabrasSugeridas($palabras_puntos['sugeridas']);

        echo json_encode($palabras_puntos);
    }
} elseif (!empty($_POST['borrar'])) {
    $busca_palabras = new Palabras([]);
    $busca_palabras->setBorrar($_POST['borrar']);
} elseif (!empty($_POST['nueva'])) {
    $busca_palabras = new Palabras([]);
    $busca_palabras->setNueva($_POST['nueva']);
}

function palabrasPuntos(array $palabras, array $puntos_extra, int $ronda): array
{
    $aPuntos = [
        'A' => 1, 'B' => 3, 'C' => 3, 'D' => 2, 'E' => 1, 'F' => 4, 'G' => 2, 'H' => 4,
        'I' => 1, 'J' => 8, 'L' => 1, 'M' => 3, 'N' => 1, 'Ñ' => 8, 'O' => 1, 'P' => 3,
        'Q' => 5, 'R' => 1, 'S' => 1, 'T' => 1, 'U' => 1, 'V' => 4, 'X' => 8, 'Y' => 4, 'Z' => 10
    ];
    $pExtra = ['' => 1, 'DL' => 2, 'TL' => 3, 'DP' => 1, 'TP' => 1];
    $resultado = [];
    foreach ($palabras as $palabra) {
        $letras = mb_str_split($palabra);
        $total = 0;
        $letras_count = count($letras);
        $puntos_extra_ronda = $puntos_extra[$letras_count - 3];
        foreach ($letras as $key => $value) {
            $total += $aPuntos[$value] * $ronda * $pExtra[$puntos_extra_ronda[$key]];
        }
        if ($letras_count === 6 && in_array('DP', $puntos_extra_ronda)) {
            $total *= 2;
        } elseif ($letras_count === 7 && in_array('TP', $puntos_extra_ronda)) {
            $total *= 3;
        }
        $resultado += [$palabra => $total];
    }
    return $resultado;
}

function palabrasSugeridas(array $palabras): array
{
    $total = 5;
    $resultado = [];
    $procesado = [];
    foreach ($palabras as $palabra => $valor) {
        $longitud = mb_strlen($palabra);
        if (in_array($longitud, $procesado) === false) {
            $resultado += [$palabra => $valor];
            if (--$total === 0) {
                break;
            }
            $procesado[] = $longitud;
        }
    }
    return $resultado;
}
