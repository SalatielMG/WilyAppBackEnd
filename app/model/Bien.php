<?php
Class Bien extends DB{

    public function mostrar($where = 1)    {
        return $this->getDatos("bien", "*", $where);//["x"=>"id","nom"=>"nombreA"]
    }
    public function addBien($nombre, $tipo){
        $bien = [
            "id"=>0,
            "nombre"=>"'$nombre'",
            "tipo"=>$tipo,
            "status"=>1
        ];
        return $this->insert("bien",$bien);
    }
}