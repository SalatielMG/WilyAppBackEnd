<?php
require_once(APP_PATH."model/Usuario.php");
class ControlUsuario extends Valida{
    private $u;
    public function __construct(){
        parent::__construct();
        $this->u = new Usuario;
    }
    function probarXD(){
        $arreglo = array();
        $pass = Form::getValue("contrasena");
        $arreglo["pass"] = password_hash($pass,PASSWORD_DEFAULT);
        return $arreglo;
    }
    function login(){
        $form = new Form;
        $form->setRules("usuario","Usuario","required");
        $form->setRules("contrasena","Contraseña","required");

        $arreglo = array();

        if(count($form->errores) > 0 ){
            $arreglo["error"] = 1;
            $arreglo["msj"] = $form->errores;
        }else{
            $u = $this->u->mostrar("usuario = '".Form::getValue("usuario")."'");
            if(count($u) > 0){
                $u = $u[0];
                $pass = Form::getValue("contrasena");
                if(password_verify($pass,$u->pass)){
                    $arreglo["error"] = 0;
                    $arreglo["id"] = base64_encode($u->id);
                    $arreglo["token"] = password_hash($arreglo["id"].date("d-m-Y:h:g:s"),PASSWORD_DEFAULT);
                    $arreglo["msj"] = 'Bienvenido '.$u->usuario;
                    $this->u->setToken($u->id,$arreglo["token"]);
                }else{
                    $arreglo["error"] = 1;
                    $arreglo["msj"] = 'Contraseña incorrecta';
                }
            }else{
                $arreglo["error"] = 1;
                $arreglo["msj"] = 'Usuario no encontrado';
            }
        }
        return $arreglo;
    }
}
