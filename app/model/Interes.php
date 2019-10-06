<?php
Class Interes extends DB{

    public function mostrar($where = 1)    {
        return $this->getDatos("interes", "*", $where);//["x"=>"id","nom"=>"nombreA"]
    }

}