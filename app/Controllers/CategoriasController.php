<?php

namespace App\Controllers;
require (__DIR__.'/../../vendor/autoload.php');
use App\Models\GeneralFunctions;
use App\Models\Categorias;

class CategoriasController
{
    private array $dataCategoria;

    public function __construct(array $_FORM)
    {
        $this->dataCategoria = array();
        $this->dataCategoria['id'] = $_FORM['id'] ?? NULL;
        $this->dataCategoria['nombres'] = $_FORM['nombres'] ?? NULL;
        $this->dataCategoria['orden'] = $_FORM['orden'] ?? null;
        $this->dataCategoria['estado'] = $_FORM['estado'] ?? 'Activo';
    }

    public function create($withFiles = null) {
        try {
            if (!empty($this->dataCategoria['nombres']) && !Categorias::categoriaRegistrada($this->dataCategoria['nombres'])) {
                $Categoria = new Categorias ($this->dataCategoria);
                if ($Categoria->insert()) {
                    unset($_SESSION['frmCategorias']);
                    header("Location: ../../views/modules/categorias/index.php?respuesta=success&mensaje=Categoria Registrada");
                }
            } else {
                header("Location: ../../views/modules/categorias/create.php?respuesta=error&mensaje=Categoria ya registrada");
            }
        } catch (\Exception $e) {
            GeneralFunctions::logFile('Exception',$e, 'error');
        }
    }

    public function edit($withFiles = null)
    {
        try {
            $categoria = new Categorias($this->dataCategoria);
            if($categoria->update()){
                unset($_SESSION['frmCategorias']);
            }
            header("Location: ../../views/modules/categorias/show.php?id=" . $categoria->getId() . "&respuesta=success&mensaje=Categoria Actualizado");
        } catch (\Exception $e) {
            GeneralFunctions::logFile('Exception',$e, 'error');
        }
    }

    static public function searchForID(array $data)
    {
        try {
            $result = Categorias::searchForId($data['id']);
            if (!empty($data['request']) and $data['request'] === 'ajax' and !empty($result)) {
                header('Content-type: application/json; charset=utf-8');
                $result = json_encode($result->jsonSerialize());
            }
            return $result;
        } catch (\Exception $e) {
            GeneralFunctions::logFile('Exception',$e, 'error');
        }
        return null;
    }

    static public function getAll(array $data = null)
    {
        try {
            $result = Categorias::getAll();
            if (!empty($data['request']) and $data['request'] === 'ajax') {
                header('Content-type: application/json; charset=utf-8');
                $result = json_encode($result);
            }
            return $result;
        } catch (\Exception $e) {
            GeneralFunctions::logFile('Exception',$e, 'error');
        }
        return null;
    }

    static public function activate(int $id)
    {
        try {
            $ObjCategoria = Categorias::searchForId($id);
            $ObjCategoria->setEstado("Activo");
            if ($ObjCategoria->update()) {
                header("Location: ../../views/modules/categorias/index.php?respuesta=success&mensaje=Registro actualizado");
            } else {
                header("Location: ../../views/modules/categorias/index.php?respuesta=error&mensaje=Error al guardar");
            }
        } catch (\Exception $e) {
            GeneralFunctions::logFile('Exception',$e, 'error');
        }
    }

    static public function inactivate(int $id)
    {
        try {
            $ObjCategoria = Categorias::searchForId($id);
            $ObjCategoria->setEstado("Inactivo");
            if ($ObjCategoria->update()) {
                header("Location: ../../views/modules/categorias/index.php?respuesta=success&mensaje=Registro actualizado");
            } else {
                header("Location: ../../views/modules/categorias/index.php?respuesta=error&mensaje=Error al guardar");
            }
        } catch (\Exception $e) {
            GeneralFunctions::logFile('Exception',$e, 'error');
        }
    }

    static public function selectCategoria(array $params = []) {

        $params['isMultiple'] = $params['isMultiple'] ?? false;
        $params['isRequired'] = $params['isRequired'] ?? true;
        $params['id'] = $params['id'] ?? "categoria_id";
        $params['name'] = $params['name'] ?? "categoria_id";
        $params['defaultValue'] = $params['defaultValue'] ?? "";
        $params['class'] = $params['class'] ?? "form-control";
        $params['where'] = $params['where'] ?? "";
        $params['arrExcluir'] = $params['arrExcluir'] ?? array();
        $params['request'] = $params['request'] ?? 'html';

        $arrCategorias = array();
        if ($params['where'] != "") { //Si hay filtro
            $base = "SELECT * FROM categoria WHERE ";
            $arrCategorias = Categorias::search($base . ' ' . $params['where']);
        } else {
            $arrCategorias = Categorias::getAll();
        }
        $htmlSelect = "<select " . (($params['isMultiple']) ? "multiple" : "") . " " . (($params['isRequired']) ? "required" : "") . " id= '" . $params['id'] . "' name='" . $params['name'] . "' class='" . $params['class'] . "' style='width: 100%;'>";
        $htmlSelect .= "<option value='' >Seleccione</option>";
        if (is_array($arrCategorias) && count($arrCategorias) > 0) {
            /* @var $arrCategorias Categorias[] */
            foreach ($arrCategorias as $categoria)
                if (!CategoriasController::categoriaIsInArray($categoria->getId(), $params['arrExcluir']))
                    $htmlSelect .= "<option " . (($categoria != "") ? (($params['defaultValue'] == $categoria->getId()) ? "selected" : "") : "") . " value='" . $categoria->getId() . "'>" . $categoria->getNombres() . "</option>";
        }
        $htmlSelect .= "</select>";
        return $htmlSelect;
    }

    private static function categoriaIsInArray($idCategoria, $ArrCategorias)
    {
        if (count($ArrCategorias) > 0) {
            foreach ($ArrCategorias as $Categoria) {
                if ($Categoria->getId() == $idCategoria) {
                    return true;
                }
            }
        }
        return false;
    }


}