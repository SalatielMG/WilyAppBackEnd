<?php
Class Cliente extends DB{

    public function mostrar($where = 1, $tabla="vistaclientes", $select="*", $limit="", $bnd=true){
        return $this->getDatos($tabla, $select, $where, $limit , $bnd);//["x"=>"id","nom"=>"nombreA"]
    }

    public function addCliente($nombre,$apellido, $sexo,$direccion,$telefono){
        $cliente = [
            "id"=>0,
            "nombre"=>"'$nombre'",
            "apellido"=>"'$apellido'",
            "sexo"=>$sexo,
            "direccion"=>"'$direccion'",
            "telefono"=>"'$telefono'",
            "status"=>1

        ];

        return $this->insert("cliente",$cliente);
    }

    public function editarCliente($idCliente, $nombre, $apellido,$sexo, $direccion, $telefono){
        $cliente = [
            "nombre"=>"'$nombre'",
            "apellido"=>"'$apellido'",
            "sexo"=>$sexo,
            "direccion"=>"'$direccion'",
            "telefono"=>"'$telefono'"
        ];
        return $this->update("cliente",$cliente,"id = $idCliente");
    }

    public function elimPmnt($idC){
        return $this -> funcionAlm("SELECT elimPermCliente($idC)");
    }

    public function restaurarCliente($idC){
        $Cliente = [
            "status"=>1
        ];
        return $this -> update("cliente", $Cliente, "id = $idC");
    }

    public function eliminarLogica($clientes){
        $i = 0;
        $clientesConPrestamo = 0;
        $arreglo = array();
        foreach ($clientes as $k => $v) {
            $cliente = [
                "status"=>0
            ];
            //$sql .= "('$folio',$k),"
            $tienePrestamo = $this->mostrar("idCliente = ".$v->id,"vistaprestamosactivos","id");
            if($this->update("cliente",$cliente,"id = ".$v->id))
                $i ++;
            if(count($tienePrestamo)>0)
                $clientesConPrestamo++;
        }
        if(count($clientes)==$i)
            $arreglo["delete"] = true;
        else
            $arreglo["delete"] = false;
        $arreglo["clientesConPrestamo"] = $clientesConPrestamo;
        // "INSERT escribe VALUES ('$folio',$k),('$folio',$k),"
        //$sql = rtrim($sql,",");
        //return $this->solicitud($sql);
        //return $i;
        return $arreglo;
    }

}