<?php
class Permiso extends DB{

	public function verificarPermiso($us,$p){
		return $this->getDatos("permite","*","usuario = '$us' AND permiso = '$p'");
	}

	public function mostrar($where = 1,$select = "*"){
		return $this->getDatos("permiso",$select,$where);
	}
}