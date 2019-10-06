<?php
class DB {
    private $conexion;
    private $baseDatos;
    private $servidor;
    private $usuario;
    private $contrasena;
    private $puerto;
    protected $result;  
    
    function __construct() {
        $config = $GLOBALS['config']["mysql"];
        
        try { 
            $this->servidor = $config["host"];
            $this->usuario = $config["us"];
            $this->contrasena = $config["pass"];
            $this->puerto = $config["puerto"];
            $this->baseDatos = $config["db"];
            $this->conecta();
        }catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }

    public function conecta(){
		@$this->conexion = new mysqli($this->servidor,$this->usuario,$this->contrasena,"",$this->puerto);
        @$this->conexion->set_charset("utf8");
        if(!empty($this->conexion->connect_error))
            throw new Exception(json_encode(["error"=>500,"msj"=>"Error de conexion con el servidor de BD"]));
        @$c = $this->conexion->query("use " . $this->baseDatos);
        if(!$c)
            throw new Exception(json_encode(["error"=>500,"msj"=>"No existe la BD"]));
        
        
    }

    public function solicitud($sql){
        $this->conecta();
        //echo $sql;
		$this->result = $this->conexion->query($sql);
		$this->conexion->close();
		if($this->result)
            return true;
        else{
			$this->result = null;
			return false;
        }
    }
	public function getResultado(){
		return $this->result;
	}
    public function funcionAlm($nomFunAlm){
        return $this -> solicitud($nomFunAlm);
    }
    public function getDatos($tablas,$select = "*",$where = 1, $limit="", $bnd=true){
        $sql = "SELECT $select FROM $tablas WHERE $where $limit";
        if($bnd)
            $sql = "SELECT $select FROM $tablas WHERE $where";
        if($this->solicitud($sql))
            return $this->arreglo();
        return [];
    }
    public function arreglo(){
        $arreglo = array();
        if($this->result->num_rows > 0){
            while($r = $this->result->fetch_object()){
                //array_push($arreglo, $r);
                //$arreglo[count($arreglo)] = $r;
                $arreglo[] = $r;
            }
        }
        return $arreglo;
    }

    //creando metodo de inserccion
    public function insert($tabla,$datos){
        $campos = array_keys($datos);
        $values = array_values($datos);
        $sql = "INSERT INTO ".$tabla." (".implode(", " , $campos).") VALUES (".implode(", ", $values).");";
        return $this->solicitud($sql);
    }

    //creando metodo de actualizacion
    public function update($tabla,$datos,$where){
        $sql = "UPDATE ".$tabla." SET ";
        $u = array();
        foreach ($datos as $k=>$v) 
            $u[] = $k."=".$v; //creando los campos a actualizar
        $sql .= implode(", ", $u)." WHERE $where"; //con el metodo implode convertimos el arreglo $u en una cadena
        
        return $this->solicitud($sql);
    }

    public function delete($tabla,$where){
        $sql = "DELETE FROM $tabla WHERE $where";
        return $this->solicitud($sql);
    }

}