<?php
if (!empty($_POST['ronda'])) {
    $puntos_extra = obtenerPuntosExtra($_POST);
    $letras_disponibles = obtenerLetrasDisponibles($_POST);
    if ($letras_disponibles !== false) {
        $ronda = intval($_POST['ronda']);
        list($palabras, $encontradas) = obtenerPalabras($letras_disponibles);
        $puntos_palabras = palabrasPuntos($palabras, $puntos_extra, $ronda);
        $puntos_encontradas = array_intersect_key($puntos_palabras, array_flip($encontradas));
        arsort($puntos_encontradas);
        $palabras_puntos = [
            'encontradas' => $puntos_encontradas,
            'sugeridas'   => palabrasSugeridas($puntos_palabras)
        ];
        echo json_encode($palabras_puntos);
    }
}

function coincidencia(string $palabra, array $letras): bool
{
    $palabra = mb_str_split($palabra);

    foreach ($palabra as $letra) {
        if (in_array($letra, $letras)) {
            $index = array_search($letra, $letras);
            unset($letras[$index]);
        } else {
            return false;
        }
    }

    return true;
}

function obtenerLetrasDisponibles($post)
{
    $letras = [];
    for ($i = 0; $i < 7; $i++) {
        if (isset($post['letrasDisponibles'][$i]) && preg_match('/[A-JL-VX-ZÑa-jl-vx-zñ]/', $post['letrasDisponibles'][$i])) {
            $letras[$i] = mb_strtoupper($post['letrasDisponibles'][$i]);
        } else {
            return false;
        }
    }
    return $letras;
}

function obtenerPalabras(array $letras): array
{
    $bd = __DIR__ . DIRECTORY_SEPARATOR . 'palabras.sqlite';
    $bd_palabras = new PDO('sqlite:' . $bd);
    $bd_palabras->sqliteCreateFunction('regexp_like', 'preg_match', 2);
    $query = $bd_palabras->query('SELECT palabra FROM palabras ORDER BY palabra ASC');

    $palabras = [];
    $encontradas = [];

    while ($palabra = $query->fetch(PDO::FETCH_COLUMN, 0)) {
        $palabras[] = $palabra;

        if (coincidencia($palabra, $letras)) {
            $encontradas[] = $palabra;
        }
    }

    return [$palabras, $encontradas];
}

function obtenerPuntosExtra($post)
{
    $pe_permitidos = ['DP', 'TP', 'DL', 'TL', ''];
    $puntos_extra = [];
    for ($x = 0; $x < 5; $x++) {
        for ($y = 0; $y < 3 + $x; $y++) {
            $tmp = 'pal' . ($x + 3) . 'let' . ($y + 1);
            $puntos_extra[$x][$y] = (isset($post[$tmp]) && in_array($post[$tmp], $pe_permitidos)) ? ($post[$tmp]) : ('');
        }
    }
    return $puntos_extra;
}

function palabrasPuntos(array $palabras, array $puntos_extra, int $ronda): array
{
    $aPuntos = [
        'A' => 1,
        'B' => 3,
        'C' => 3,
        'D' => 2,
        'E' => 1,
        'F' => 4,
        'G' => 2,
        'H' => 4,
        'I' => 1,
        'J' => 8,
        'L' => 1,
        'M' => 3,
        'N' => 1,
        'Ñ' => 8,
        'O' => 1,
        'P' => 3,
        'Q' => 5,
        'R' => 1,
        'S' => 1,
        'T' => 1,
        'U' => 1,
        'V' => 4,
        'X' => 8,
        'Y' => 4,
        'Z' => 10
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
