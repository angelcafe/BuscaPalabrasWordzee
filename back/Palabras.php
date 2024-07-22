<?php

class Palabras
{
    private PDO $bd_palabras;
    private PDO $bd_sugerencias;
    private array $letras = [];
    private array $palabras = [];
    private array $resultado = [];

    public function __construct(array $letras)
    {
        $this->inicializarBD();
        if (!empty($letras)) {
            $this->letras = $letras;
            $this->obtenerPalabras();
            unset($this->bd_palabras);
        }
    }

    public function getPalabras(): array
    {
        return $this->palabras;
    }

    public function getResultado(): array
    {
        return $this->resultado;
    }

    public function setBorrar(string $palabra): void
    {
        $query = $this->bd_sugerencias->prepare('INSERT INTO borrar (palabra) VALUES (:palabra)');
        $query->execute([':palabra' => $palabra]);
    }

    public function setNueva(string $palabra): void
    {
        $query = $this->bd_sugerencias->prepare('INSERT INTO nuevas (palabra) VALUES (:palabra)');
        $query->execute([':palabra' => $palabra]);
    }

    private function inicializarBD(): void
    {
        $bd = __DIR__ . DIRECTORY_SEPARATOR . 'palabras.sqlite';
        $this->bd_palabras = new PDO('sqlite:' . $bd);
        $this->bd_palabras->sqliteCreateFunction('regexp_like', 'preg_match', 2);

        $bd = __DIR__ . DIRECTORY_SEPARATOR . 'sugerencias.sqlite';
        $this->bd_sugerencias = new PDO('sqlite:' . $bd);
    }

    private function obtenerPalabras(): void
    {
        $query = $this->bd_palabras->query('SELECT palabra FROM palabras ORDER BY palabra ASC');

        while ($palabra = $query->fetch(PDO::FETCH_COLUMN, 0)) {
            $this->palabras[] = $palabra;

            if ($this->coincidencia($palabra)) {
                $this->resultado[] = $palabra;
            }
        }
    }

    private function coincidencia(string $palabra): bool
    {
        $letras = $this->letras;
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
}
