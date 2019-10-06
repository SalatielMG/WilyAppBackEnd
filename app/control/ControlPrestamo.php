<?php
require_once(APP_PATH."model/Prestamo.php");
class ControlPrestamo extends Valida{
    private $p;
    private $pagina = 0;
    public function __construct(){
        parent::__construct();
        $this->p = new Prestamo;
    }
    private function nomCliente($id){
        $select = $this->p->mostrar("id = $id","vistaclientes","nombreCliente");
        return $select[0]->nombreCliente;
    }
    private function condicion($filter, $data, $idC){
        $consulta = "";
        if(in_array("1", $filter)){
            $filter = array_slice($filter, 1);
            $consulta = $consulta." and (fecha BETWEEN '$data[0]' and '$data[1]' )";
            foreach ($filter as $clave => $valor){
                $dato = $data[$clave+2];
                switch ($valor){//$clave+1
                    case 2:
                        $consulta = $consulta." and (nombreCliente LIKE '%$dato%' OR direccion LIKE '%$dato%' OR telefono LIKE '%$dato%')";
                        break;
                    case 3:
                        $consulta = $consulta." and (cantidad = $dato)";
                        break;
                    case 4:
                        $consulta = $consulta." and (idInteres = $dato)";
                        break;
                    case 5:
                        $consulta = $consulta." and (idBien = $dato)";
                        break;
                }
            }
        }else{
            foreach ($filter as $clave => $valor){
                $dato = $data[$clave];
                switch ($valor){//$clave+1
                    case 2:
                        $consulta = $consulta." and (nombreCliente LIKE '%$dato%' OR direccion LIKE '%$dato%' OR telefono LIKE '%$dato%')";
                        break;
                    case 3:
                        $consulta = $consulta." and (cantidad = $dato)";
                        break;
                    case 4:
                        $consulta = $consulta." and (idInteres = $dato)";
                        break;
                    case 5:
                        $consulta = $consulta." and (idBien = $dato)";
                        break;
                }
            }
        }
        if($idC>0)
            $consulta = $consulta." and (idCliente = $idC)";

        $consulta = substr($consulta, 4);
        return $consulta;
    }
    public function consultarPrestamosActivos(){
        $this->validaToken();
        $idC = Form::getValue("idC");
        $this->pagina = Form::getValue("pagina");
        $this->pagina = $this->pagina * 10;
        $filtros = json_decode(Form::getValue("filtros",false,false));
        $dataBusqueda = json_decode(Form::getValue("dataBusqueda",false, false));

        $Prestamos = array();
        $where = "";

        if(count($filtros)){//Esto es una busqueda
            $where = $this->condicion( $filtros, $dataBusqueda, $idC);
        }else{//No es una busqueda, es una carga general
            if ($idC > 0){//Busqueda de prestamos activos de un cliente en especifico
                $where = "idCliente = $idC";
            }else//Busqueda general de prestamos activos
                $where = "1";
        }

        $select = $this->p->mostrar($where,"vistaprestamosactivos","*"," limit ".$this->pagina.",10",false);
        $Prestamos["error"] = 0;
        $Prestamos["Consulta"] = $where;
        $total = count($select);

        if($total>0){
            $Prestamos["row"] = $total;
            $Prestamos["msj"] = "Prestamos Activos encontrados";
            $Prestamos["prestamos"] = $select;
            $Prestamos["filtros"] = $filtros;
            $Prestamos["dataBusqueda"] = $dataBusqueda;
        }else{
            $Prestamos["row"] = 0;
            $Prestamos["msj"] = ($idC > 0) ? 'No se encontraron registros de Prestamos Activos del cliente "'.$this->nomCliente($idC).'"' : "No se encontraron registros de Prestamos Activos";
        }
        return $Prestamos;
    }

    public function consultaPrestamosCerrados(){
        $this->validaToken();
        $idC = Form::getValue("idC");
        $this->pagina = Form::getValue("pagina");
        $this->pagina = $this->pagina * 10;
        $filtros = json_decode(Form::getValue("filtros",false,false));
        $dataBusqueda = json_decode(Form::getValue("dataBusqueda",false, false));

        $Prestamos = array();
        $where = "";

        if(count($filtros)){//Esto es una busqueda
            $where = $this->condicion( $filtros, $dataBusqueda, $idC);
        }else{//No es una busqueda, es una carga general
            if ($idC > 0){//Busqueda de prestamos activos de un cliente en especifico
                $where = "idCliente = $idC";
            }else//Busqueda general de prestamos activos
                $where = "1";
        }

        $select = $this->p->mostrar($where,"vistaprestamoscerrados","*"," limit ".$this->pagina.",10",false);
        $Prestamos["error"] = 0;
        $Prestamos["Consulta"] = $where;
        $total = count($select);

        if($total>0){
            $Prestamos["row"] = $total;
            $Prestamos["msj"] = "Prestamos Cerrados encontrados";
            $Prestamos["prestamos"] = $select;
            $Prestamos["filtros"] = $filtros;
            $Prestamos["dataBusqueda"] = $dataBusqueda;
        }else{
            $Prestamos["row"] = 0;
            $Prestamos["msj"] = ($idC > 0) ? 'No se encontraron registros de Prestamos Cerrados del cliente "'.$this->nomCliente($idC).'"' : "No se encontraron registros de Prestamos Cerrados";
        }
        return $Prestamos;
    }
    public function consultaPrestamosEliminados(){
        $this->validaToken();
        $esActivo = Form::getValue("esActivo");
        $tabla = "vistaprestamosactivoseliminados";
        if($esActivo == 2) $tabla = "vistaprestamoscerradoseliminados";

        $select = $this->p->mostrar("1",$tabla);
        $Prestamos = array();
        $total = count($select);
        if($total > 0){
            $Prestamos["row"] = $total;
            $Prestamos["msj"] = "Prestamos eliminados encontrados";
            $Prestamos["prestamos"] = $select;
        }else{
            $Prestamos["row"] = 0;
            $Prestamos["msj"] = ($esActivo == 1) ? "Prestamos Activos Eliminados No Encontrados" : "Prestamos Cerrados Eliminados No Encontrados";
        }
        return $Prestamos;
    }
    public function agregarPrestamo(){
        $this->validaToken();
        $form = new Form;
        $esGarantia = Form::getValue("esGarantia");
        $observacion = Form::getValue("observacion");
        $form->setRules("cliente","Cliente","required|enterosPositivos");
        $form->setRules("fecha","Fecha","required");
        //$form->setRules("esGarantia","opcion Garantia","required|enteros");
        //$form->setRules("bien","Bien","required|enterosPositivos");
        $form->setRules("estado_bien","Estado del Bien","required|max[255]");
        $form->setRules("cantidad","Cantidad a prestar","required|enterosPositivos");
        $form->setRules("interes","Interes","required|enterosPositivos");
        if($esGarantia == "true"){//Significa que si es requerido el bien
            $form->setRules("bien","Bien","required|enterosPositivos");
        }
        if($observacion != ""){
            $form->setRules("observacion","Observacion","max[100]");
        }
        $a = array();
        $a["esGarantia"] = $esGarantia;
        $a["observacion"] = $observacion;
        if(count($form->errores) > 0 ){
            $a["error"] = count($form->errores);
            $a["msj"] = $form->errores;
        }else{
            if($esGarantia == "true"){
                $insert= $this -> p -> addPrestamo(
                    Form::getValue("cliente"),
                    Form::getValue("interes"),
                    Form::getValue("fecha"),
                    Form::getValue("cantidad"),
                    Form::getValue("estado_bien"),
                    Form::getValue("observacion"),
                    Form::getValue("esGarantia"),
                    Form::getValue("bien"));
            }else{
                $insert= $this -> p -> addPrestamo(
                    Form::getValue("cliente"),
                    Form::getValue("interes"),
                    Form::getValue("fecha"),
                    Form::getValue("cantidad"),
                    Form::getValue("estado_bien"),
                    Form::getValue("observacion"),
                    Form::getValue("esGarantia"));
            }
            if($insert){
                $a["error"] = 0;
                $a["msj"] = "Prestamo Registrado";
                $a["prestamo"] = $this->p->mostrar("id = (select max(id)as id from prestamo)");
                $a["rows"]=count($a["prestamo"]);
            }else{
                $a["error"] = 1;
                $a["esGarantia"] = $esGarantia;
                $a["msj"] = "Error al insertar el Prestamo";
            }

        }
        return $a;
    }
    public function eliminarPmntePrestamo(){
        $this->validaToken();
        $idPrestamo = Form::getValue("idPrestamo");
        $res = $this -> p -> elimPmnt($idPrestamo);
        $a = array();
        if($res){
            $a["error"] = 0;
            $a["msj"] = "Prestamo eliminado de la Base de Datos";
        }else{
            $a["error"] = 1;
            $a["titulo"] = "Error al eliminar de la BD";
            $a["msj"] = "El PRestamo no se pudo Eliminar de la Base de Datos. Recargue y Verifique Porfavor";
        }
        return  $a;
    }
    public function restaurarPrestamo(){
        $this->validaToken();
        $idPrestamo = Form::getValue("idPrestamo");
        $Clte = array();
        $restaurar = $this -> p -> eliminarPrestamo($idPrestamo, 1);
        if($restaurar){
            $Clte["error"] = 0;
            $Clte["msj"] = "Prestamo restaurado correctamente :)";
        }else{
            $Clte["error"] = 1;
            $Clte["titulo"] = "¡ Error al restaurar ! ";
            $Clte["msj"] = "Hubo un problema al intentar restaurar el Prestamo :(.";
        }
        return $Clte;
    }
    public function eliminarLogicaPrestamo(){/*true := Activo; false := Cerrado*/
        $this->validaToken();
        $idPrestamo = Form::getValue("idPrestamo");
        $bnd = Form::getValue("bndActivo");
        $arreglo = array();
        if($this -> p -> eliminarPrestamo($idPrestamo, 0)){
            $arreglo["error"] = 0;
            $arreglo["msj"] = ($bnd == 1) ? "Prestamo Cancelado Correctamente" : "Prestamo Eliminado Correctamente";
        }else{
            $arreglo["error"] = 1;
            $arreglo["titulo"] = ($bnd == 1) ? "Error al cancelar el prestamo" : "Error al eliminar el prestamo";
            $arreglo["msj"] = ($bnd == 1) ? "¡ Se presento un error al intentar cancelar el prestamo !" : "¡ Se presento un error al intentar eliminar el prestamo !";
        }
        return $arreglo;
    }
    public function deshacerCierrePrestamo(){
        $this->validaToken();
        $idPrestamo = Form::getValue("idPrestamo");
        $arreglo = array();
        if($this -> p -> esPrestamo($idPrestamo, 1)){
            $arreglo["error"] = 0;
            $arreglo["msj"] = "Prestamo cambiado a estado 'Activo' correctamente :) ";
        }else{
            $arreglo["error"] = 1;
            $arreglo["titulo"] = "Error";
            $arreglo["msj"] = "¡ Se presento un error al intentar deshacer el cierre del prestamo :( !";
        }
        return $arreglo;
    }
    public function cerrarPrestamoActivo(){
        $this -> validaToken();
        $idPrestamo = Form::getValue("idPrestamo");
        $fechaCierre = Form::getValue("fechaCierre");
        $razon = Form::getValue("razon");

        $arreglo = array();
        if($this -> p -> esPrestamo($idPrestamo, 0, $fechaCierre, $razon)){
            $arreglo["error"] = 0;
            $arreglo["msj"] = "Prestamo cerrado correctamente :) ";
        }else{
            $arreglo["error"] = 1;
            $arreglo["titulo"] = "Error al cerrar";
            $arreglo["msj"] = "¡ Se presento un error al intentar cerrar el prestamo :( !";
        }
        return $arreglo;
    }
    public function editarPrestamo(){
        $this->validaToken();
        $form = new Form;
        $esGarantia = Form::getValue("esGarantia");
        $observacion = Form::getValue("observacion");
        $form->setRules("fecha","Fecha","required");
        //$form->setRules("bien","Bien","required|enterosPositivos");
        $form->setRules("estado_bien","Estado del Bien","required|max[255]");
        $form->setRules("cantidad","Cantodad a prestar","required|enterosPositivos");
        $form->setRules("interes","Interes","required|enterosPositivos");
        if($esGarantia == "true"){//Significa que si es requerido el bien
            $form->setRules("bien","Bien","required|enterosPositivos");
        }
        if($observacion != ""){
            $form->setRules("observacion","Observacion","max[100]");
        }
        $a = array();
        if(count($form->errores) > 0 ){
            $a["error"] = count($form->errores);
            $a["msj"] = $form->errores;
        }else{
            $idP = Form::getValue("idPrestamo");
            $fechaAct = Form::getValue("fecha");
            $fechaAnt = $this->p->mostrar("id = $idP", "vistaprestamosactivos","fecha");
            /*Codigo Alta*/
            if($esGarantia == "true"){
                $update= $this -> p -> editarPrestamo(
                    $idP,
                    Form::getValue("interes"),
                    $fechaAct,
                    Form::getValue("cantidad"),
                    Form::getValue("estado_bien"),
                    Form::getValue("observacion"),
                    Form::getValue("esGarantia"),
                    Form::getValue("bien"));
            }else{
                $update= $this -> p -> editarPrestamo(
                    $idP,
                    Form::getValue("interes"),
                    $fechaAct,
                    Form::getValue("cantidad"),
                    Form::getValue("estado_bien"),
                    Form::getValue("observacion"),
                    Form::getValue("esGarantia"));
            }
            /*Codigo Alta*/

            if($update){
                $a["fechaAnt"] = $form->esIgual($fechaAnt[0]->fecha, $fechaAct);
                $a["error"] = 0;
                $a["msj"] = "Prestamo Actualizado correctamente";
                $a["prestamo"] = $this->p->mostrar("id = $idP");
                $a["rows"]=count($a["prestamo"]);
            }else{
                $a["error"] = 1;
                $a["msj"] = "Error al editar el Prestamo";
            }
        }
        return $a;
    }

}