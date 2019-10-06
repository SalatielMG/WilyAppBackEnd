<?php
require_once(APP_PATH."model/Interes.php");
class ControlInteres extends Valida{
    private $i;
    public function __construct(){
        parent::__construct();
        $this->i = new Interes;
    }

    public function consultarTodos(){
        $this->validaToken();
        $intereses["error"] = 0;
        $intereses["intereses"]= $this->i->mostrar("id > 0");
        $intereses["rows"]=count($intereses["intereses"]);

        return $intereses;
    }
}