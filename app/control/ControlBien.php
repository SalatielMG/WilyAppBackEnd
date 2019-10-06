<?php
require_once(APP_PATH."model/Bien.php");
class ControlBien extends Valida{
    private $b;
    public function __construct(){
        parent::__construct();
        $this->b = new Bien;
    }

    public function consultarTodos(){
        $this->validaToken();
        $bienes["error"] = 0;
        $bienes["bienes"]=$this->b->mostrar("id > 0");
        $bienes["rows"]=count($bienes["bienes"]);

        return $bienes;//Ya se realizo el json_encode
    }

    public function agregarBien(){
        $this->validaToken();
        $form = new Form;
        $form->setRules("nombre","Nombre del bien","required|max[100]");
        $a = array();
        if(count($form -> errores) > 0 ){
            $a["error"] = count($form -> errores);
            $a["msj"] = $form -> errores;
        }else{
            $insert= $this -> b -> addBien(
                Form::getValue("nombre"),
                Form::getValue("tipo")
            );
            if($insert){
                $a["error"] = 0;
                $a["msj"] = "Bien Registrado Correctamente";
            }else{
                $a["error"] = 1;
                $a["titulo"] = "Error al agregar";
                $a["msj"] = 'Verifica si el "¡ Bien Ya Existe en la Base de Datos !"';
            }
        }
        return $a;
    }
}