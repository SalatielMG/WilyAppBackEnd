<?php
Class Usuario extends DB{ 

	public function mostrar($where = 1){
		return $this->getDatos("usuario","*",$where);//["x"=>"id","nom"=>"nombreA"]
	}

	public function setToken($id,$token){
		$us = ["token"=>"'$token'"];
		return $this->update("usuario",$us,"id = $id");

	}

	public function getToken($id){
		$token = $this->getDatos("usuario","token","id = '$id'");
		if(count($token) > 0)
			return $token[0]->token;
		return null;
	}
    public function addUsuario($idUs,$us,$pass,$nombreUs,$status,$token){
        $password=password_hash("$pass",PASSWORD_DEFAULT);
        $usuario = [
            "id"=>$idUs,
            "usuario"=>"'$us'",
            "pass"=>"'$password'",
            "nombre"=>"'$nombreUs'",
            "status"=>$status,
            "token"=>"'$password'"

        ];

        return $this->insert("usuario",$usuario);
    }
    //delete usuario,permite from usuario,permite where usuario.idUs=permite.usuario AND permite.usuario=8
    public function eliminar($idUs){
        return $this->delete(" usuario"," id= $idUs");
    }

    function editarUser($idUs,$us,$nombreUs){
        $usuario = [
            "usuario" => "'$us'",
            "nombre" => "'$nombreUs'"
        ];
        return $this->update("usuario",$usuario,"idUs = '$idUs'");
    }

}