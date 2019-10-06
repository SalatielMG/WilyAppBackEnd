<?php
Class Prestamo extends DB{

    public function mostrar($where = 1, $tabla="vistaprestamosactivos", $select="*", $limit="", $bnd=true){
        return $this->getDatos($tabla, $select, $where, $limit , $bnd);//["x"=>"id","nom"=>"nombreA"]
    }

    public function addPrestamo($cliente, $interes, $fecha, $cantidad, $estado_bien, $observacion, $esGarantia, $bien = 'null'){
        $prestamo = [
            "id"=>0,
            "cliente"=>$cliente,
            "bien"=>$bien,
            "interes"=>$interes,
            "fecha"=>"'$fecha'",
            "cantidad"=>$cantidad,
            "estado_bien"=>"'$estado_bien'",
            "observacion" => "'$observacion'",
            "esPrestamo"=>1,
            "esGarantia" => $esGarantia,
            "status"=>1

        ];
        return $this->insert("prestamo",$prestamo);
    }

    /*
     * $insert= $this -> p -> addPrestamo(
                    Form::getValue("cliente"),
                    Form::getValue("interes"),
                    Form::getValue("fecha"),
                    Form::getValue("cantidad"),
                    Form::getValue("estado_bien"),
                    Form::getValue("observacion"),
                    Form::getValue("bien"));
     * */

    public function editarPrestamo($idP, $interes, $fecha, $cantidad, $estado_bien, $observacion, $esGarantia, $bien = 'null'){

        $prestamo = [
            "bien"=>$bien,
            "interes"=>$interes,
            "fecha"=>"'$fecha'",
            "cantidad"=>$cantidad,
            "estado_bien"=>"'$estado_bien'",
            "observacion" => "'$observacion'",
            "esPrestamo"=>1,
            "esGarantia" => $esGarantia,
            "status"=>1
        ];
        return $this->update("prestamo",$prestamo,"id = $idP");
    }
    public function elimPmnt($idP){
        return $this -> funcionAlm("SELECT elimPermPrestamo($idP)");
    }
    function eliminarPrestamo($idP, $status){
        $prestamo = [
            "status" => $status
        ];
        return $this->update("prestamo", $prestamo,"id = $idP");
    }
    function esPrestamo($idP, $status, $fechaCierre = null, $razon = ""){
        $prestamo = [
            "fechaCierre" => ($fechaCierre == null)? "null":"'$fechaCierre'",
            "razon" => "'$razon'",
            "esPrestamo" => $status
        ];
        return $this->update("prestamo", $prestamo,"id = $idP");
    }
    function eliminar($idP){
        return $this->delete("prestamo"," id = $idP");
    }

}