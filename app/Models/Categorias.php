<?php

namespace App\Models;

use App\Enums\EstadoCategorias;
use App\Interfaces\Model;
use Carbon\Carbon;
use Exception;
use JetBrains\PhpStorm\Pure;
use JsonSerializable;
use ReflectionEnum;

require_once ("AbstractDBConnection.php");
require_once (__DIR__."\..\Interfaces\Model.php");
require_once (__DIR__.'/../../vendor/autoload.php');

class Categorias extends AbstractDBConnection implements \App\Interfaces\Model
{

    private ?int $id;
    private string $nombres;
    private int $orden;
    private EstadoCategorias $estado;

    /* Relaciones */
    private ?array $SubcategoriasCategoria;

    /**
     * @param array $categoria
     */
    public function __construct(array $categoria = [])
    {
        parent::__construct(); // Llamada al constructor padre
        $this->setId($categoria['id'] ?? null);
        $this->setNombres($categoria['nombres'] ?? '');
        $this->setOrden($categoria['orden'] ?? 0);
        $this->setEstado($categoria['estado'] ?? EstadoCategorias::INACTIVO);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getNombres(): string
    {
        return $this->nombres;
    }

    /**
     * @param string $nombres
     */
    public function setNombres(string $nombres): void
    {
        $this->nombres = $nombres;
    }

    /**
     * @return int
     */
    public function getOrden(): int
    {
        return $this->orden;
    }

    /**
     * @param int|mixed $orden
     */
    public function setOrden(int $orden): void
    {
        $this->orden = $orden;
    }

    /**
     * @return EstadoCategorias
     */
    public function getEstado(): string
    {
        return $this->estado->toString();
    }

    /**
     * @param EstadoCategorias|null $estado
     */
    public function setEstado(null|string|EstadoCategorias $estado): void
    {
        if(is_string($estado)){
            $this->estado = EstadoCategorias::from($estado);
        }else{
            $this->estado = $estado;
        }
    }

    /**
     * @return array|null
     */
    public function getSubcategoriasCategoria(): ?array
    {
        // No tengo la clase de subcategorias.....
        return $this->SubcategoriasCategoria;
    }


    protected function save(string $query): ?bool
    {
        $arrData = [
            ':id' =>    $this->getId(),
            ':nombres' =>   $this->getNombres(),
            ':orden' =>   $this->getOrden(),
            ':estado' =>   $this->getEstado(),
        ];
        $this->Connect();
        $result = $this->insertRow($query, $arrData);
        $this->Disconnect();
        return $result;
    }

    function insert(): ?bool
    {
        $query = "INSERT INTO lista_tareas.categoria VALUES (
            :id,:nombres,:orden,:estado)";
        return $this->save($query);
    }

    function update(): ?bool
    {
        $query = "UPDATE lista_tareas.categoria SET 
            nombres = :nombres, orden = :orden, estado = :estado
            WHERE id = :id";
        return $this->save($query);
    }

    function deleted(): ?bool
    {
        $this->setEstado(EstadoCategorias::INACTIVO); //Cambia el estado del Usuario
        return $this->update();                    //Guarda los cambios..
    }

    static function search($query): ?array
    {
        try {
            $arrCategorias = array();
            $tmp = new Categorias();
            $tmp->Connect();
            $getrows = $tmp->getRows($query);
            $tmp->Disconnect();

            if (!empty($getrows)) {
                foreach ($getrows as $valor) {
                    $Categoria = new Categorias($valor);
                    array_push($arrCategorias, $Categoria);
                    unset($Categoria);
                }
                return $arrCategorias;
            }
            return null;
        } catch (Exception $e) {
            GeneralFunctions::logFile('Exception', $e);
        }
        return null;
    }

    static function searchForId(int $id): ?object
    {
        try {
            if ($id > 0) {
                $tmpCategoria = new Categorias();
                $tmpCategoria->Connect();
                $getrow = $tmpCategoria->getRow("SELECT * FROM lista_tareas.categoria WHERE id =?", array($id));
                $tmpCategoria->Disconnect();
                return ($getrow) ? new Categorias($getrow) : null;
            } else {
                throw new Exception('Id de categoria Invalido');
            }
        } catch (Exception $e) {
            GeneralFunctions::logFile('Exception', $e);
        }
        return null;
    }

    static function getAll(): ?array
    {
        return Categorias::search("SELECT * FROM lista_tareas.categoria");
    }


    /**
     * @param $documento
     * @return bool
     * @throws Exception
     */
    public static function categoriaRegistrada($nombre): bool
    {
        $result = Categorias::search("SELECT * FROM lista_tareas.categoria where nombres = '" . $nombre."' ");
        if (!empty($result) && count($result)>0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "Nombres: $this->nombres, 
                Orden: $this->orden, 
                Estado: $this->estado";
    }
    
    /**
     * @inheritDoc
     */
    public function jsonSerialize() : array
    {
        return [
            'id' => $this->getId(),
            'nombres' => $this->getNombres(),
            'orden' => $this->getOrden(),
            'estado' => $this->getEstado()
        ];
    }
}