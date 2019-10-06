<?php
Class Abono extends DB{

    public function mostrar($where = 1, $tablas = "vistaabonosprestamoactivo", $select = "*")    {
        return $this->getDatos($tablas, $select, $where);//["x"=>"id","nom"=>"nombreA"]
    }

    public function addAbono($prestamo, $fecha , $interes, $abono, $mora , $nota){
        $abonoP = [
            "id" => 0,
            "prestamo" => $prestamo,
            "fecha" => "'$fecha'",
            "abono" => $abono,
            "interes" => $interes,
            "mora" => $mora,
            "nota" => "'$nota'",
            "status" => 1
        ];
        return $this->insert("abono",$abonoP);
    }

    public function anularAbono($idA){
        $abono = [ "status" => 0];
        return $this -> update("abono", $abono, "id = $idA");
    }

    public function eliminarPAbono($idA){
        return $this->delete("abono", "id = $idA");
    }

    public function restaurarAbono($idA){
        $abono = [ "status" => 1];
        return $this -> update("abono", $abono, "id = $idA");
    }
    public function editarAbono($idA, $fecha, $interes, $abono, $mora, $nota){
        $abonoP = [
            "fecha" => "'$fecha'",
            "abono" => $abono,
            "interes" => $interes,
            "mora" => $mora,
            "nota" => "'$nota'"
        ];
        return $this -> update("abono", $abonoP, "id = $idA" );
    }

}