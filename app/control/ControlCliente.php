<?php
require_once(APP_PATH."model/Cliente.php");
class ControlCliente extends Valida{
    private $c;
    private $pagina = 0;
    public function __construct(){
        parent::__construct();
        $this->c = new Cliente;
    }

    public function consultarTodos(){
        $this->validaToken();
        $this->pagina = Form::getValue("pagina");
        $this->pagina = $this->pagina * 10;
        $b=Form::getValue("buscar");
        $where=" nombreCliente LIKE '%$b%' OR direccion LIKE '%$b%' OR telefono LIKE '%$b%'";
        if ($b=="") {
            $where="1";
        }
        $clientes["error"] = 0;
        $clientes["clientes"]=$this->c->mostrar($where,"vistaclientes","*"," limit ".$this->pagina.",10",false);
        $clientes["rows"]=count($clientes["clientes"]);
        return $clientes;//Ya se realizo el json_encode
    }
    public function consultaClienteEliminados(){
        $this->validaToken();
        $select = $this->c->mostrar("1","vistaclienteseliminados");
        $clientes = array();
        $total = count($select);
        if($total>0){
            $clientes["row"] = $total;
            $clientes["msj"] = "Clientes eliminados encontrados";
            $clientes["clientes"] = $select;
        }else{
            $clientes["row"] = $total;
            $clientes["msj"] = "Clientes eliminados no encontrados";
        }
        return $clientes;
    }
    public function obtUltimo(){
        $this->validaToken();
        $clientes["error"] = 0;
        $clientes["cliente"]=$this->c->mostrar("id = (select max(id)as id from cliente)");
        $clientes["rows"]=count($clientes["cliente"]);
        return $clientes;
    }
    public function agregarCliente(){
        $this->validaToken();
        $form = new Form;
        $form->setRules("nombre","Nombre","required|max[100]");
        $form->setRules("apellidos","Apellidos","required|max[100]");
        $form->setRules("sexo","Sexo","required");
        $form->setRules("direccion","Direccion","required|max[100]");
        $form->setRules("telefono","Telefono","required|lon[10]");
        // validar los campos restantes
        $a = array();
        if(count($form->errores) > 0 ){
            $a["error"] = count($form->errores);
            $a["msj"] = $form->errores;
        }else{
            $insert= $this->c->addCliente(
                Form::getValue("nombre"),
                Form::getValue("apellidos"),
                Form::getValue("sexo"),
                Form::getValue("direccion"),
                Form::getValue("telefono")
            );
            if($insert){
                $a["error"] = 0;
                $a["msj"] = "Cliente Registrado";
                $a["cliente"]=$this->c->mostrar("id = (select max(id)as id from cliente)");
                $a["rows"]=count($a["cliente"]);
            }else{
                $a["error"] = 1;
                $a["msj"] = "Error al insertar el Cliente";
            }

        }
        return $a;
    }

    public function  editarCliente(){
        $this->validaToken();
        $form = new Form;
        $form->setRules("nombre","Nombre","required|max[100]");
        $form->setRules("apellido","Apellidos","required|max[100]");
        $form->setRules("sexo","Sexo","required");
        $form->setRules("direccion","Direccion","required|max[100]");
        $form->setRules("telefono","Telefono","required|lon[10]");

        $a = array();
        if(count($form->errores) > 0 ){
            $a["error"] = count($form->errores);
            $a["msj"] = $form->errores;
        }else{
            $idC = Form::getValue("idCliente");
            $tienePrestamo = $this->c->mostrar("idCliente = $idC","vistaprestamosactivos","id");
            $update = $this->c->editarCliente(
                $idC,
                Form::getValue("nombre"),
                Form::getValue("apellido"),
                Form::getValue("sexo"),
                Form::getValue("direccion"),
                Form::getValue("telefono")
            );
            if($update){
                $a["error"] = 0;
                $a["msj"] = "Cliente Actualizado Correctamente";
                $a["cliente"] = $this->c->mostrar("id = $idC");
                $a["rows"] = count($a["cliente"]);
                $a["tienePrestamo"] = count($tienePrestamo);
            }else{
                $a["error"] = 1;
                $a["msj"] = "Error al Editar el Cliente";
            }
        }
        return $a;
    }

    public function restaurarCliente(){
        $this->validaToken();
        $idCliente = Form::getValue("idCliente");
        $Clte = array();
        $restaurar = $this -> c -> restaurarCliente($idCliente);
        if($restaurar){
            $Clte["error"] = 0;
            $Clte["msj"] = "Cliente restaurado correctamente :)";
            $Clte["prestamos"] = $this -> c -> mostrar("cliente = $idCliente and status = 1", "prestamo");
            $Clte["tienePrestamos"] = count($Clte["prestamos"]);
        }else{
            $Clte["error"] = 1;
            $Clte["titulo"] = "¡ Error al restaurar ! ";
            $Clte["msj"] = "Hubo un problema al intentar restaurar el Cliente :(.";
        }
        return $Clte;
    }

    public function eliminarPmnteCliente(){
        $this->validaToken();
        $idCliente = Form::getValue("idCliente");
        $res = $this -> c -> elimPmnt($idCliente);
        $a = array();
        if($res){
            $a["error"] = 0;
            $a["msj"] = "Cliente eliminado de la Base de Datos";
        }else{
            $a["error"] = 1;
            $a["titulo"] = "Error al eliminar de la BD";
            $a["msj"] = "El Cliente no se pudo Eliminar de la Base de Datos. Recargue y Verifique Porfavor";
        }
        return  $a;
    }

    public function eliminarCliente(){
        $this->validaToken();
        $a = array();
        $eliminar= $this->c->eliminarLogica(
            json_decode(Form::getValue("Clientes",false,false))
        );
        if($eliminar["delete"]){
            $a["error"] = 0;
            $a["msj"] = "Cliente(s) Eliminado(s)";
            $a["clientesConPrestamo"] = $eliminar["clientesConPrestamo"];
        }else{
            $a["error"] = 1;
            $a["msj"] = "Error al eliminar el(los) Cliente(s)";
        }
        return $a;
    }

}