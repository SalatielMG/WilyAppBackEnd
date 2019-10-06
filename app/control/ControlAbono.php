<?php
require_once(APP_PATH."model/Abono.php");
class ControlAbono extends Valida{
    private $a;
    private $pagina = 0;
    public function __construct(){
        parent::__construct();
        $this->a = new Abono;
    }
    private function proximoPago($idP){
        /*
         * $fecha = new DateTime('2000-12-31');
            $fecha->modify('+1 month');
            echo $fecha->format('Y-m-d') . "\n";

            $fecha->modify('+1 month');
            echo $fecha->format('Y-m-d') . "\n";
        */
        $tieneAbono = $this -> a -> mostrar("status = 1 AND idPrestamo = $idP", "vistaabonosprestamoactivo", "count(*) as total, MAX(fechaAbono) as ultimaFechaAbono, fechaPrestamo");
        if($tieneAbono[0] -> total > 0){//Tiene abonos
            $diferencia = Form::diferenciaFechas($tieneAbono[0] -> fechaPrestamo, $tieneAbono[0] -> ultimaFechaAbono);
            $numMeses = ($diferencia["meses"] + ($diferencia["años"] * 12)) + 1;
            $fecha = new DateTime($tieneAbono[0] -> fechaPrestamo);
            $fecha -> modify("+$numMeses month");
        }else{//No tiene abonos
            $Prestamo = $this -> a -> mostrar("id = $idP", "vistaprestamosactivos", "fecha");
            $fecha = new DateTime($Prestamo[0] -> fecha);
            $fecha -> modify('+1 month');
        }
        return $fecha ->format('Y-m-d');
    }
    public function cargarAbonos(){
        $this->validaToken();
        $esActivo = Form::getValue("esActivo");
        $idP = Form::getValue("idPrestamo");
        $Abonos = array();
        $where = "idPrestamo = $idP";
        $tabla = "vistaabonosprestamoactivo";
        $table = "vistatotalgralabonospa";
        if($esActivo == 2){//Es Cerrado
            $tabla = "vistaabonosprestamocerrado";
            $table = "vistatotalgralabonospc";
        }
        $select = $this -> a -> mostrar($where, $tabla);
        $Abonos["error"] = 0;
        $total = count($select);
        if($total > 0){
            $Abonos["row"] = $total;
            $Abonos["msj"] = "Abonos encontrados";
            $Abonos["abonos"] = $select;
            $Abonos["total"] = $this -> a -> mostrar($where, $table);
            $Abonos["rowTotal"] = count($Abonos["total"]);
            $Abonos["cerrarPrestamo"] = ($Abonos["rowTotal"] > 0) ? ($Abonos["total"][0] -> capitalgralRestante == 0) ? true: false : false;
        }else{
            $Abonos["row"] = 0;
            $Abonos["titulo"] = ($esActivo == 1) ? "Sin Pagos" : "Error de solicitud" ;
            $Abonos["msj"] = ($esActivo == 1) ? "¡ El prestamo solicitado aun no tiene ningun pago realizado !" : "¡ Error al recuperar los abonos del prestamo solicitado !" ;
            $Abonos["cerrarPrestamo"] = false;
        }
        if($esActivo == 1){//Si esta activo; calcula la fecha proxima de pago
            $Abonos["fechaProxima"] = $this -> proximoPago($idP);
        }
        return $Abonos;
    }

    public function editarAbono(){
        $this->validaToken();
        $form = new Form;
        $form->setRules("fecha","Fecha de abono","required");
        $form->setRules("abono","Abono a capital","required|enterosPositivos");
        $form->setRules("interes","Abono a interes","required|enterosPositivos");
        $form->setRules("mora","Cargo o mora","required|enterosPositivos");
        //$form->setRules("nota","Nota de abono","max[255]");
        $a = array();
        if(count($form->errores) > 0 ){
            $a["error"] = count($form->errores);
            $a["titulo"] = "¡ Error ! ";
            $a["msj"] = $form->errores;
        }else{
            $insert= $this->a->editarAbono(
                Form::getValue("idAbono"),
                Form::getValue("fecha"),
                Form::getValue("interes"),
                Form::getValue("abono"),
                Form::getValue("mora"),
                Form::getValue("nota")
            );
            if($insert){
                $a["error"] = 0;
                $a["msj"] = "Pago Editado Correctamente";

            }else{
                $a["error"] = 1;
                $a["titulo"] = "¡ Error ! ";
                $a["msj"] = "Hubo un problema al intentar editar el Pago. ¡ Porfavor verifique los datos ingresados !";
            }
        }
        return $a;
    }
    public function agregarAbono(){
        $this->validaToken();
        $form = new Form;
        $form->setRules("fecha","Fecha de abono","required");
        $form->setRules("abono","Abono a capital","required|enterosPositivos");
        $form->setRules("interes","Abono a interes","required|enterosPositivos");
        $form->setRules("mora","Cargo o mora","required|enterosPositivos");
        //$form->setRules("nota","Nota de abono","max[255]");
        $a = array();
        if(count($form->errores) > 0 ){
            $a["error"] = count($form->errores);
            $a["titulo"] = "¡ Error ! ";
            $a["msj"] = $form->errores;
        }else{
            $insert= $this->a->addAbono(
                Form::getValue("idPrestamo"),
                Form::getValue("fecha"),
                Form::getValue("interes"),
                Form::getValue("abono"),
                Form::getValue("mora"),
                Form::getValue("nota")
            );
            if($insert){
                $a["error"] = 0;
                $a["msj"] = "Pago Registrado Correctamente";
                $a["abono"] = $this->a->mostrar("1", "vistaabonosactivos");
                $a["abonos"] = $this->a->mostrar("1", "vistaabonosprestamoactivo ");
                $a["rows"]=count($a["abono"]);
            }else{
                $a["error"] = 1;
                $a["titulo"] = "¡ Error ! ";
                $a["msj"] = "Hubo un problema al intentar insertar el Pago. ¡ Porfavor verifique los datos ingresados !";
            }
        }
        return $a;
    }

    public function anularAbono(){
        $this->validaToken();
        $idA = Form::getValue("idAbono");
        $Abono = array();
        $anular = $this -> a -> anularAbono($idA);
        if($anular){
            $Abono["error"] = 0;
            $Abono["msj"] = "Abono anulado correctamente";
        }else{
            $Abono["error"] = 1;
            $Abono["titulo"] = "¡ Error al anular ! ";
            $Abono["msj"] = "Hubo un problema al intentar anular el Abono.";
        }
        return $Abono;
    }

    public function eliminarPermanentementeAbono(){
        $this->validaToken();
        $idA = Form::getValue("idAbono");
        $Abono = array();
        $anular = $this -> a -> eliminarPAbono($idA);
        if($anular){
            $Abono["error"] = 0;
            $Abono["msj"] = "Abono eliminado correctamente";
        }else{
            $Abono["error"] = 1;
            $Abono["titulo"] = "¡ Error al eliminar ! ";
            $Abono["msj"] = "Hubo un problema al intentar eliminar permanentemente el Abono.";
        }
        return $Abono;
    }

    public function restaurarAbono(){
        $this->validaToken();
        $idA = Form::getValue("idAbono");
        $Abono = array();
        $anular = $this -> a -> restaurarAbono($idA);
        if($anular){
            $Abono["error"] = 0;
            $Abono["msj"] = "Abono restaurado correctamente";
        }else{
            $Abono["error"] = 1;
            $Abono["titulo"] = "¡ Error al restaurar ! ";
            $Abono["msj"] = "Hubo un problema al intentar restaurar el Abono.";
        }
        return $Abono;
    }

    public function recuperaDatos(){
        $this->validaToken();
        $idP = Form::getValue("idPrestamo");
        $Data = array();
        $tieneAbono = $this -> a -> mostrar("status = 1 AND idPrestamo = $idP", "vistaabonosprestamoactivo", "capitalRestante, fechaAbono, fechaPrestamo, cantidadPrestado");
        $total = count($tieneAbono);
        if($total > 1){//Tiene abonos activos ; se toma los datos del penultimo
            $diferencia = Form::diferenciaFechas($tieneAbono[$total - 2] -> fechaPrestamo, $tieneAbono[$total - 2] -> fechaAbono);
            $numMeses = ($diferencia["meses"] + ($diferencia["años"] * 12)) + 1;
            $fecha = new DateTime($tieneAbono[$total - 2] -> fechaPrestamo);
            $fecha -> modify("+$numMeses month");


            $Data["fechaInput"] = $tieneAbono[$total - 2] -> fechaAbono;
            $Data["CapAct"] = $tieneAbono[$total - 2] -> capitalRestante;
            $Data["proximoPago"] = $fecha ->format('Y-m-d');

        }else if($total == 1){//Tiene un solo abono activo; se toma los datos del prestamo
            //$Prestamo = $this -> a -> mostrar("id = $idP", "vistaprestamosactivos", "fecha, cantidad");
            $fecha = new DateTime( $tieneAbono[0] -> fechaPrestamo);
            $fecha -> modify('+1 month');

            $Data["fechaInput"] = $tieneAbono[0] -> fechaPrestamo;
            $Data["CapAct"] = $tieneAbono[0] -> cantidadPrestado;
            $Data["proximoPago"] = $fecha ->format('Y-m-d');
        }
        return $Data;
    }

    public function calcularMonto(){
        $this->validaToken();
        $fechaActual = Form::getValue("fecha");
        $idP = Form::getValue("idPrestamo");
        $op = Form::getValue("op");
        $Montos = array();
        if($op == 1){
            $tieneAbono = $this -> a -> mostrar("status = 1 AND idPrestamo = $idP", "vistaabonosprestamoactivo", "count(*) as total, MAX(fechaAbono) as ultimaFechaAbono");
            $Prestamo = $this -> a -> mostrar("id = $idP", "vistaprestamosactivos", "fecha, cantidad, porcentaje");
            //$tieneAbono[0]->total = dataNumerico;

            $diferencia = Form::diferenciaFechas($Prestamo[0] -> fecha, $fechaActual);
            if($tieneAbono[0] -> total > 0){//Tiene abonos Calcular el monto a partir d la ultma fecha de abono
                //$Montos = $tieneAbono;

                /*Pruebas*/
                $diferenciaFechaAUP = Form::diferenciaFechas($Prestamo[0] -> fecha, $tieneAbono[0] -> ultimaFechaAbono);
                $Montos["diferenciaFPrestamo"] = $Prestamo[0] -> fecha;
                $Montos["diferenciaFActual"] = $fechaActual;
                $Montos["diferencia"] = $diferencia;
                $Montos["diferenciaFechaAUPPrestamo"] = $Prestamo[0] -> fecha;
                $Montos["diferenciaFechaAUPUltimoAbono"] = $tieneAbono[0] -> ultimaFechaAbono;
                $Montos["diferenciaFechaAUP"] =$diferenciaFechaAUP;
                $Montos["pruebaJeje"] = Form::diferenciaFechas($tieneAbono[0] -> ultimaFechaAbono, $fechaActual);
                /*Pruebas*/

                /* Procedimientos
                 * Realizar un nuevo array con los valores de la difrencia de fecjha corecta.
                 * */
                $capActual = $this -> a -> mostrar("idPrestamo = $idP", "vistatotalgralabonospa", "capitalgralRestante");
                $mesesDiferencia = ($diferencia["meses"] + ($diferencia["años"] * 12));
                $diferenciaFechaAUP = ($diferenciaFechaAUP["meses"] + ($diferenciaFechaAUP["años"] * 12));
                //Del total de meses hay que calcular los ñaos y los meses.
                $mesesResutante = $mesesDiferencia - $diferenciaFechaAUP;
                $añosDecimal = $mesesResutante / 12;
                $arr = explode(".", $añosDecimal);
                $mesResultante = $mesesResutante - ($arr[0] * 12);
                $difFecha = array();
                $difFecha["años"] = intval($arr[0]);
                $difFecha["meses"] = $mesResultante;
                $difFecha["dias"] = $diferencia["dias"];
                $Montos["FechaResultante"] = $difFecha;
                $Montos["montoInteres"] = $mesesResutante * ($capActual[0] -> capitalgralRestante * $Prestamo[0] -> porcentaje);
                $Montos["montoMora"] = 0 ;
                $Montos["msjInteres"] = $this -> msjmontoInteres($difFecha);
                $Montos["msjMora"] = $this -> msjmontoMora($difFecha);
                $Montos["nota"] = $this -> msjNota($difFecha);
                $Montos["CapAct"] = $capActual[0] -> capitalgralRestante;
                $Montos["fechaInput"] = $tieneAbono[0] -> ultimaFechaAbono;
            }else{//No tiene, hay que calcular el monto a partir de la fecha de prestamo

                //$Montos = $fechaPrestamo;
                /*
                * 1.- Verificar los meses y dias transcurridos transcurridos a partir de la fecha de prestamo
                * 2.- A partir de las fechas
                 *
                 * $res["años"] = $diferencia -> y;
                 * $res["meses"] = $diferencia -> m;
                 * $res["dias"] = $diferencia -> d;
                * */
                //$mntMora = ($diferencia["meses"] > 0) ? ($diferencia["meses"] + ($diferencia["años"] * 12) - 1) * ($Prestamo[0] -> cantidad * $Prestamo[0] -> porcentaje) : 0;
                //                            0                      + (0*12) -1

                $Montos["FechaPrestamo"] = $Prestamo[0] -> fecha;
                $Montos["FechaActual"] = $fechaActual;

                $Montos["dif"] = $diferencia;
                $Montos["Prestamo"] = $Prestamo;
                //$Montos["montoInteres"] = ($diferencia["meses"] > 0) ? ($Prestamo[0] -> cantidad * $Prestamo[0] -> porcentaje): 0;
                $Montos["montoInteres"] = ($diferencia["meses"] + ($diferencia["años"] * 12)) * ($Prestamo[0] -> cantidad * $Prestamo[0] -> porcentaje);
                $Montos["montoMora"] = 0 ;
                $Montos["msjInteres"] = $this -> msjmontoInteres($diferencia);
                $Montos["msjMora"] = $this -> msjmontoMora($diferencia);
                $Montos["nota"] = $this -> msjNota($diferencia);
                $Montos["CapAct"] = $Prestamo[0] -> cantidad;
                $Montos["fechaInput"] = $Prestamo[0] -> fecha;
            }
        }else if($op == 2){
            $tieneAbono = $this -> a -> mostrar("status = 1 AND idPrestamo = $idP", "vistaabonosprestamoactivo", "capitalRestante, fechaAbono, fechaPrestamo, cantidadPrestado, porcentaje");
            $total = count($tieneAbono);

            $diferencia = Form::diferenciaFechas($tieneAbono[0] -> fechaPrestamo, $fechaActual);

            if($total > 1){//Tiene abonos activos ; se toma los datos del penultimo
                $penultimo = $total - 2;
                $diferenciaFechaAUP = Form::diferenciaFechas($tieneAbono[$penultimo] -> fechaPrestamo, $tieneAbono[$penultimo] -> fechaAbono);


                /* Procedimientos
                 * Realizar un nuevo array con los valores de la difrencia de fecjha corecta.
                 * */
                $mesesDiferencia = ($diferencia["meses"] + ($diferencia["años"] * 12));
                $diferenciaFechaAUP = ($diferenciaFechaAUP["meses"] + ($diferenciaFechaAUP["años"] * 12));
                //Del total de meses hay que calcular los ñaos y los meses.
                $mesesResutante = $mesesDiferencia - $diferenciaFechaAUP;
                $añosDecimal = $mesesResutante / 12;
                $arr = explode(".", $añosDecimal);
                $mesResultante = $mesesResutante - ($arr[0] * 12);
                $difFecha = array();
                $difFecha["años"] = intval($arr[0]);
                $difFecha["meses"] = $mesResultante;
                $difFecha["dias"] = $diferencia["dias"];

                $Montos["CapAct"] = $tieneAbono[$penultimo] -> capitalRestante;//
                $Montos["fechaInput"] = $tieneAbono[$penultimo] -> fechaAbono;//
                $Montos["FechaResultante"] = $difFecha;//
                $Montos["montoInteres"] = $mesesResutante * ($Montos["CapAct"] * $tieneAbono[$penultimo] -> porcentaje);//
                $Montos["montoMora"] = 0 ;
                $Montos["msjInteres"] = $this -> msjmontoInteres($difFecha);
                $Montos["msjMora"] = $this -> msjmontoMora($difFecha);
                $Montos["nota"] = $this -> msjNota($difFecha);

            }else if($total == 1){//Tiene un solo abono activo; se toma los datos del prestamo
                //$Prestamo = $this -> a -> mostrar("id = $idP", "vistaprestamosactivos", "fecha, cantidad");
                /*$Montos["FechaPrestamo"] = $Prestamo[0] -> fecha;
                $Montos["FechaActual"] = $fechaActual;*/

                //$Montos["dif"] = $diferencia;
                //$Montos["Prestamo"] = $Prestamo;
                //$Montos["montoInteres"] = ($diferencia["meses"] > 0) ? ($Prestamo[0] -> cantidad * $Prestamo[0] -> porcentaje): 0;
                $Montos["CapAct"] = $tieneAbono[0] -> cantidadPrestado;
                $Montos["fechaInput"] = $tieneAbono[0]-> fechaPrestamo;
                $Montos["montoInteres"] = ($diferencia["meses"] + ($diferencia["años"] * 12)) * ($Montos["CapAct"] * $tieneAbono[0] -> porcentaje);
                $Montos["montoMora"] = 0 ;
                $Montos["msjInteres"] = $this -> msjmontoInteres($diferencia);
                $Montos["msjMora"] = $this -> msjmontoMora($diferencia);
                $Montos["nota"] = $this -> msjNota($diferencia);
            }
        }

        return $Montos;
    }
    private function plural($a){
        return ($a > 1) ? "atrasados": "atrasado";
    }
    private function pluralNota($a){
        return ($a > 1) ? "transcurridos": "transcurrido";
    }
    private function msjmontoMora($diferencia){
        $msj = "";
        $año = $diferencia["años"];
        $mes = $diferencia["meses"];
        $dia = $diferencia["dias"];
        if($mes > 0 || $año > 0){
            if($dia > 0)
                $msj = $msj. $this -> dias($dia). $this -> plural($dia) ;
        }
        return $msj;
    }
    private function msjmontoInteres($diferencia){
        $msj = "";
        $meses = ($diferencia["meses"] + ($diferencia["años"] * 12));
        if($meses > 0){
            $msj = $this -> meses($meses);
        }
        return $msj;
    }
    private function msjNota($diferencia){
        $msj = "";
        $año = $diferencia["años"];
        $mes = $diferencia["meses"];
        $dia = $diferencia["dias"];
        if($año > 0){
            $msj = $this -> años($año);
            if($mes > 0){
                $msj = $msj. $this -> meses($mes);
                if($dia > 0){
                    $msj = $msj. $this -> dias($dia). $this -> pluralNota($dia) ;
                }else{
                    $msj = $msj. $this -> pluralNota($mes) ;
                }
            }else if($dia > 0){
                $msj = $msj. $this -> dias($dia). $this -> pluralNota($dia) ;
            }else{
                $msj = $msj. $this -> pluralNota($año) ;
            }
        }else if($mes > 0){
            $msj = $msj. $this -> meses($mes);
            if($dia > 0){
                $msj = $msj. $this -> dias($dia). $this -> pluralNota($dia) ;
            }else{
                $msj = $msj. $this -> pluralNota($mes) ;
            }
        }else if($dia > 0){
            $msj = $msj. $this -> dias($dia). $this -> pluralNota($dia) ;
        }
        return $msj;
    }
    private function años($a){
        return ($a > 1) ? "$a años ": "$a año ";
    }
    private function dias($a){
        return ($a > 1) ? "$a dias ": "$a dia ";
    }
    private function meses($a){
        return ($a > 1) ? "$a meses ": "$a mes ";
    }
}