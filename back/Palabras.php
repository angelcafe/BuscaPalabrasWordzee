<?php

class Palabras
{
    private PDO $pdo_sqlite;
    private array $letras = [];
    private array $palabras = [];
    private array $resultado = [];

    public function __construct(array $letras)
    {
        $this->inicializarBD();
        $this->letras = $letras;
        $this->obtenerPalabras();
        unset($this->pdo_sqlite);
    }

    public function getPalabras(): array
    {
        return $this->palabras;
    }

    public function getResultado(): array
    {
        return $this->resultado;
    }

    private function inicializarBD(): void
    {
        $bd = __DIR__ . DIRECTORY_SEPARATOR . 'palabras.sqlite';
        $this->pdo_sqlite = new PDO('sqlite:' . $bd);
        $this->pdo_sqlite->sqliteCreateFunction('regexp_like', 'preg_match', 2);

        if (!file_exists($bd)) {
            $this->pdo_sqlite->exec('CREATE TABLE IF NOT EXISTS "palabras" ("palabra" STRING PRIMARY KEY)');
        }
    }

    private function obtenerPalabras(): void
    {
        $query = $this->pdo_sqlite->query('SELECT palabra FROM palabras ORDER BY palabra ASC');

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
