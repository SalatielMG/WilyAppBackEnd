<?php
require_once(APP_PATH."model/Pago.php");
session_start();

class ControlPago extends Valida{

    private $p;
    private $pagina = 0;
    private $IDPrestamo;
    private $FechaActual;
    private $ESActivo;


    public function __construct(){
        parent::__construct();
        $this -> p = new Pago;
    }
    private function pagoActualizado($idP){
        $a = array();
        $select = $this -> p -> mostrar("id = $idP", "vistapagototalmes");
        $total = count($select);
        if($total > 0){
            $a["pago"] = $select[0];
            $a["total"] = $total;
        }else{
            $a["total"] = 0;
        }
        return $a;
    }
    private function varGlobales(){
        $this -> IDPrestamo = Form::getValue("idPrestamo");
        $this -> FechaActual = Form::getValue("fechaActual");
        $this -> ESActivo = Form::getValue("esActivo");
    }
    public function anularMes(){
        $this->validaToken();
        $a = array();
        $this -> varGlobales();
        $anularMes = $this -> p -> anularM(Form::getValue("idPago"));
        if($anularMes){
            $a["error"] = 0;
            $a["titulo"] = "¡ Felicitaciones !";
            $a["msj"] = "Mes anulado correctamente";
            $a["totalGral"] = $this -> calcularTotalGral();
            $a["pagoActualizado"] = $this -> pagoActualizado(Form::getValue("idPago"));
        }else{
            $a["error"] = 1;
            $a["titulo"] = "¡ Error ! ";
            $a["msj"] = "Hubo un problema al intentar anular el Mes.";
        }
        return $a;
    }
    public function eliminarPermMes(){//No se necesita actualizar los totales
        $this->validaToken();
        $a = array();
        $anularMes = $this -> p -> elimPM(Form::getValue("idPago"));
        if($anularMes){
            $a["error"] = 0;
            $a["titulo"] = "¡ Felicitaciones !";
            $a["msj"] = "Mes eliminado permanentemente con exito";
            //$a["pagoActualizado"] = $this -> pagoActualizado(Form::getValue("idPago"));
        }else{
            $a["error"] = 1;
            $a["titulo"] = "¡ Error ! ";
            $a["msj"] = "Hubo un problema al intentar eliminar el Mes de la Base de Datos.";
        }
        return $a;
    }
    public function restaurarMes(){
        $this->validaToken();
        $a = array();
        /*
        * Procedimientos
        * Validar si el prestamo tiene algun pago abierto y que este activo
        */
        $this -> varGlobales();
        $pagosAbiertos = $this ->p -> mostrar("prestamo = " . $this -> IDPrestamo . " and esAbierto = 1 and status = 1", "vistapagototalmes", "count(*) as total");
        if($pagosAbiertos[0] -> total > 0){
            $a["error"] = $pagosAbiertos[0] -> total;
            $a["titulo"] = "¡ Error ! ";
            $a["msj"] = ($a["error"] > 1) ?" ¡ Existen " . $a["error"]. " Meses Abiertos Actualmente ! ": " ¡ Existe un Mes Abierto Actualmente ! ";
        }else{
            $idP = Form::getValue("idPago");
            $restaurarMes = $this -> p -> restaurarM($idP);
            if($restaurarMes){
                $a["error"] = 0;
                $a["titulo"] = "¡ Felicitaciones !";
                $a["msj"] = "El Mes ha sido Restaurado con Exito";
                $a["totalGral"] = $this -> calcularTotalGral();
                $a["pagoActualizado"] = $this -> pagoActualizado($idP);
            }else{
                $a["error"] = 1;
                $a["titulo"] = "¡ Error ! ";
                $a["msj"] = "Hubo un problema al intentar Restaurar el Mes.";
            }
        }
        return $a;
    }
    public function cerrarMes(){
        $this->validaToken();
        $this -> varGlobales();
        $a = array();
        $cerrarMes = $this -> p -> cerrarM(Form::getValue("idPago"), Form::getValue("fecha"));
        if($cerrarMes){
            $a["error"] = 0;
            $a["titulo"] = "¡ Felicitaciones !";
            $a["msj"] = "Mes cerrado correctamente";
            $a["totalGral"] = $this -> calcularTotalGral();
            $a["pagoActualizado"] = $this -> pagoActualizado(Form::getValue("idPago"));
        }else{
            $a["error"] = 1;
            $a["titulo"] = "¡ Error ! ";
            $a["msj"] = "Hubo un problema al intentar cerrar el Mes.";
        }
        return $a;
    }
    public function volverAbrirMes(){
        $this->validaToken();
        $this -> varGlobales();
        $a = array();
        /*Procedimiento:
        * 1.- Verficar si existe algun pago abierto.
        * 2.- si existe mandar un error
        * 3.- sino continuar.
        * Una vez continuado, se Actualiza el Mes a Abierto. y se obtiene le Mes Actualizado
        */
        $pagosAbiertos = $this ->p -> mostrar("prestamo = " . $this -> IDPrestamo . " and esAbierto = 1 and status = 1", "vistapagototalmes", "count(*) as total");
        if($pagosAbiertos[0] -> total > 0){
            $a["error"] = $pagosAbiertos[0] -> total;
            $a["titulo"] = "¡ Error ! ";
            $a["msj"] = ($a["error"] > 1) ?" ¡ Existen " . $a["error"]. " Meses Abierto Actualmente ! ": " ¡ Existe un Mes Abierto Actualmente ! ";
        }else{
            $idP = Form::getValue("idPago");
            $volverAbrirMes = $this -> p -> volverAbrirM($idP);
            if($volverAbrirMes){
                $a["error"] = 0;
                $a["titulo"] = "¡ Felicitaciones !";
                $a["msj"] = "El Mes ha sido vuelto Abrir Otra Vez con Exito";
                $a["totalGral"] = $this -> calcularTotalGral();
                $a["pagoActualizado"] = $this -> pagoActualizado($idP);
            }else{
                $a["error"] = 1;
                $a["titulo"] = "¡ Error ! ";
                $a["msj"] = "Hubo un problema al intentar Abrir de nuevo el Mes.";
            }
        }
        return $a;
    }
    public function abrirMes(){
        $this->validaToken();
        $this -> varGlobales();
        $a = array();
        /*verificar si algun pago del prestamo esta con esabierto = 1
        */
        $tieneMesAbierto = $this -> p -> mostrar("status = 1 and prestamo = " . $this -> IDPrestamo . " and esAbierto = 1 ", "pago", "count(id) as total");
        $total = $tieneMesAbierto[0] -> total;
        if($total > 0){//Si tiene mas de un pago (Mes) abierto, se detiene y mada u error
            $a["error"] = $total;
            $a["titulo"] = "¡ Error: Se encontraron meses abiertos !";
            $a["msj"] = "El prestamo actualmente tiene $total " . ($total == 1) ? "mes abierto": "meses abiertos";
        }else{//Sino, entonces sigue y verifica el ultimo mes cerrado y le suma 1
            /*
             * Se tendra que buscar el ultimo pago con status = 1
             * Luego de caludar el mes se calcula el capital actual y el intresacumulado
             * 1.- Mes
             * 2.- CapitalActual
             * 3.- InteresAcumulado.
             * 4.- (Pendiente) Periodo:= la cual tendra como variable un fechaInicial y una fecha final
             * */
            $numMes = $this -> p -> mostrar("status = 1 and prestamo = " . $this -> IDPrestamo, "pago", "count(id) as mes");
            /*Si es mes == 0; entonces se obtiene los datos directamente de la tabla prestamos*/
            $prestamo = $this -> p-> mostrar("id = " . $this -> IDPrestamo, "vistaprestamosactivos", "porcentaje, cantidad, fecha");
            if($numMes[0] -> mes == 0){//no se tienen nigun registro de pagos activos, se abrira un nuevo mes y se calcularan los dato a parir del prestamo
                $capActual = $prestamo[0] -> cantidad;
                $intRestMesAnt = 0;
                $intGenerado = ($capActual * $prestamo[0] -> porcentaje);
                //$intAcumulado = $capActual * $prestamo[0] -> porcentaje;
            }else{
                $pago = $this -> p -> mostrar("status = 1 and prestamo = " . $this -> IDPrestamo . " and mes = " . $numMes[0] -> mes, "vistapagototalmes", "interesRestante, capitalRestante");
                $capActual = $pago[0] -> capitalRestante;
                $intRestMesAnt = $pago[0] -> interesRestante;
                $intGenerado = ($capActual * $prestamo[0] -> porcentaje);
                //$intAcumulado = ($capActual * $prestamo[0] -> porcentaje) + $pago[0] -> interesRestante;
            }
            $periodo = $this -> calcularPeriodo($prestamo[0] -> fecha,$numMes[0] -> mes + 1);
            $insert = $this -> p -> abrirM($this -> IDPrestamo, $numMes[0] -> mes + 1, $capActual, $intRestMesAnt, $intGenerado, $periodo["fechaInicial"], $periodo["fechaFinal"]);
            if($insert){
                $a["mes"] = $numMes[0] -> mes;
                $a["numMes"] = $numMes[0] -> mes + 1;
                $a["capActual"] = $capActual;
                $a["intRestMesAnt"] = $intRestMesAnt;
                $a["intGenerado"] = $intGenerado;
                $a["fecha"] = $prestamo[0] -> fecha;
                $a["periodoI"] = $periodo["fechaInicial"];
                $a["periodoF"] = $periodo["fechaFinal"];
                //$a["periodoF"] = $periodo[0] -> fechaFinal;
                $a["error"] = 0;
                $a["titulo"] = "¡ Felicitaciones !";
                $a["msj"] = "Mes abierto correctamente";
                $a["totalGral"] = $this -> calcularTotalGral();
                $newPago = $this -> p -> mostrar("id = (select max(id) from pago)", "vistapagototalmes");
                $totalUP = count($newPago);
                if($totalUP > 0){
                    $a["newPago"] = $newPago[0];
                    $a["totalNewPago"] = $totalUP;
                }else{
                    $a["totalNewPago"] = 0;
                }
            }else{
                $a["error"] = 1;
                $a["titulo"] = "¡ Error ! ";
                $a["msj"] = "Hubo un problema al intentar abrir el nuevo Mes. ¡ Porfavor verifique el mes abierto !";
            }
        }
        return $a;
    }
    public function rehacerAbono(){
        $this->validaToken();
        $this -> varGlobales();
        $a = array();
        $restaurar = $this -> p -> updateStatusAbono( Form::getValue("idAbono"), 1);
        if($restaurar){
            $a["error"] = 0;
            $a["msj"] = "Abono Restaurado Correctamente";
            /**Obtener el pago actualizado*/
            $a["totalGral"] = $this -> calcularTotalGral();
            $pagoActualizado = $this -> p -> mostrar("id = " . Form::getValue("idPago"), "vistapagototalmes");
            $totalPago = count($pagoActualizado);
            if($totalPago > 0){
                $a["totalPago"] = $totalPago;
                $a["pagoActualizado"] = $pagoActualizado[0];
                $a["abonos"] = $this -> p -> mostrar("pago = " . Form::getValue("idPago") . " order by fecha ASC", "abonoP as ab, (SELECT @rownum:=0) as r","if(ab.status = 1 , @rownum:=@rownum+1, 0) as pos, ab.*");
                $a["totalAbonos"] = count($a["abonos"]);
            }else{
                $a["totalPago"] = 0;
            }
        }else{
            $a["error"] = 1;
            $a["titulo"] = "¡ Error ! ";
            $a["msj"] = "Hubo un problema al intentar restaurar el Abono.";
        }
        return $a;
    }
    public function editarAbono(){
        $this->validaToken();
        $this -> varGlobales();
        $nota = Form::getValue("nota");
        $form = new Form;
        $form->setRules("fecha","Fecha de abono","required");
        $form->setRules("cantidad","Cantidad abonado","required|enterosPositivos");
        if($nota != ""){
            $form->setRules("nota","Abono a capital","max[255]");
        }
        $a = array();
        if(count($form -> errores) > 0 ){
            $a["error"] = count($form -> errores);
            $a["titulo"] = "¡ Error ! ";
            $a["msj"] = $form -> errores;
        }else{//$idAbono, $fecha, $cantidad, $nota
            $editar = $this -> p -> updateAbono( Form::getValue("idAbono"), Form::getValue("fecha"), Form::getValue("cantidad"), Form::getValue("nota"));
            if($editar){
                $a["error"] = 0;
                $a["msj"] = "Abono Editado Correctamente";
                /**Obtener el pago actualizado*/
                $a["totalGral"] = $this -> calcularTotalGral();
                $pagoActualizado = $this -> p -> mostrar("id = " . Form::getValue("idPago"), "vistapagototalmes");
                $totalPago = count($pagoActualizado);
                if($totalPago > 0){
                    $a["totalPago"] = $totalPago;
                    $a["pagoActualizado"] = $pagoActualizado[0];
                    $a["abonos"] = $this -> p -> mostrar("pago = " . Form::getValue("idPago") . " order by fecha ASC", "abonoP as ab, (SELECT @rownum:=0) as r","if(ab.status = 1 , @rownum:=@rownum+1, 0) as pos, ab.*");
                    $a["totalAbonos"] = count($a["abonos"]);
                }else{
                    $a["totalPago"] = 0;
                }
            }else{
                $a["error"] = 1;
                $a["titulo"] = "¡ Error ! ";
                $a["msj"] = "Hubo un problema al intentar editar el Abono.";
            }
        }
        return $a;
    }
    public function elimPmnteAbono(){
        $this->validaToken();
        $a = array();
        $elimPmnteAbono = $this -> p -> elimPMA(Form::getValue("idAbono"));
        if($elimPmnteAbono){
            $a["error"] = 0;
            $a["titulo"] = "¡ Felicitaciones !";
            $a["msj"] = "Abono eliminado permanentemente con exito";
        }else{
            $a["error"] = 1;
            $a["titulo"] = "¡ Error ! ";
            $a["msj"] = "Hubo un problema al intentar eliminar el Abono de la Base de Datos.";
        }
        return $a;
    }
    public function cancelarAbono(){
        $this->validaToken();
        $this -> varGlobales();
        $a = array();
        $cancelar = $this -> p -> updateStatusAbono( Form::getValue("idAbono"), 0);
        if($cancelar){
            $a["error"] = 0;
            $a["msj"] = "Abono Cancelado Correctamente";
            /**Obtener el pago actualizado*/
            $a["totalGral"] = $this -> calcularTotalGral();
            $pagoActualizado = $this -> p -> mostrar("id = " . Form::getValue("idPago"), "vistapagototalmes");
            $totalPago = count($pagoActualizado);
            if($totalPago > 0){
                $a["totalPago"] = $totalPago;
                $a["pagoActualizado"] = $pagoActualizado[0];
                $a["abonos"] = $this -> p -> mostrar("pago = " . Form::getValue("idPago") . " order by fecha ASC", "abonoP as ab, (SELECT @rownum:=0) as r","if(ab.status = 1 , @rownum:=@rownum+1, 0) as pos, ab.*");
                $a["totalAbonos"] = count($a["abonos"]);
            }else{
                $a["totalPago"] = 0;
            }
        }else{
            $a["error"] = 1;
            $a["titulo"] = "¡ Error ! ";
            $a["msj"] = "Hubo un problema al intentar cancelar el Abono.";
        }
        return $a;
    }
    public function agregarAbono(){
        $this->validaToken();
        $this -> varGlobales();
        $nota = Form::getValue("nota");
        $form = new Form;
        $form->setRules("fecha","Fecha de abono","required");
        $form->setRules("cantidad","Cantidad abonado","required|enterosPositivos");
        if($nota != ""){
            $form->setRules("nota","Abono a capital","max[255]");
        }
        $a = array();
        if(count($form -> errores) > 0 ){
            $a["error"] = count($form -> errores);
            $a["titulo"] = "¡ Error ! ";
            $a["msj"] = $form -> errores;
        }else{
            $insert= $this -> p -> addAbono(
                Form::getValue("idPago"),
                Form::getValue("fecha"),
                Form::getValue("cantidad"),
                Form::getValue("nota")
            );
            if($insert){
                $a["error"] = 0;
                $a["msj"] = "Abono Registrado Correctamente";
                /**Obtener el pago actualizado*/
                $a["totalGral"] = $this -> calcularTotalGral();
                $pagoActualizado = $this -> p -> mostrar("id = " . Form::getValue("idPago"), "vistapagototalmes");
                $totalPago = count($pagoActualizado);
                if($totalPago > 0){
                    $a["totalPago"] = $totalPago;
                    $a["pagoActualizado"] = $pagoActualizado[0];
                    $a["abonos"] = $this -> p -> mostrar("pago = " . Form::getValue("idPago") . " order by fecha ASC", "abonoP as ab, (SELECT @rownum:=0) as r","if(ab.status = 1 , @rownum:=@rownum+1, 0) as pos, ab.*");
                    $a["totalAbonos"] = count($a["abonos"]);
                }else{
                    $a["totalPago"] = 0;
                }
            }else{
                $a["error"] = 1;
                $a["prueba"] = $insert;
                $a["titulo"] = "¡ Error ! ";
                $a["msj"] = "Hubo un problema al intentar insertar el Abono. ¡ Porfavor verifique los datos ingresados !";
            }
        }
        return $a;
    }
    public function calcularPeriodo($fechaaPrestamo, $mes){
        /*
         * Extraer el mes y el año de la fecha de prestamo,
         * si (mesPrestamo + mes > 12) entonces
         * añoPrestamo incrementa en (mesPrestamo + mes) / 12; (solo la parte entera)
         * mesproximo = (mesPrestamo + mes) - (añoPrestamo * 12)
         * sino
         * año prestamo no le pasa nada.
         * $mesproximo = mesPrestamo + mes;
         * Hay que crear una nuva fechaproxima
         * $fecha = "$añoProximo-$mesProximo-01"
         * Se obtiene el ultimo dia de ese mes y se comrara si el dia de prestamo es menor o igual
         * al ultimo dia del mes proximo        *          *
         * if(true){
         *    $fecha final = sumar $mes al la fecha de prestamo
         * }else{
         *    $fecha final = ultimo dia del mes proximo.
         * }
         * */
        $arrarXd = array();
        $fechaPFinal = new DateTime($fechaaPrestamo);
        $fechaPInicial = new DateTime($fechaaPrestamo);
        $añoPrestamo = $fechaPFinal -> format("Y");
        $mesPrestamo = $fechaPFinal -> format("m");
        $diaPrestamo = $fechaPFinal -> format("d");
        $mesCalculado = $mesPrestamo + $mes;
        //$arrarXd["$mesCalculado"] = $mesCalculado;
        if($mesCalculado > 12){
            $arr = explode(".", ($mesCalculado / 12));
            $añoProximo = $añoPrestamo + $arr[0];
            $mesProximo = $mesCalculado - ($arr[0] * 12);
            //$arrarXd["arr"] = $arr;
        }else{
            $añoProximo = $añoPrestamo;
            $mesProximo = $mesCalculado;
        }
        $fechaproxima = new DateTime("$añoProximo-$mesProximo-01");
        //$arrarXd["fechaproxima"] = $fechaproxima;
        $fechaproxima -> modify("last day of this month");
        if($diaPrestamo <= $fechaproxima -> format("d")){//Esto siginfica que se $mes a la fecha de prestamo
            /*
             * Dos variables una con sumart meses y la otra con crear una variable string3
             * */
            //$fechaFinal["fecahaString"] = "$añoProximo-$mesProximo-$diaPrestamo";
            $fechaFinal = $fechaPFinal -> modify("+$mes month") -> modify("-1 day") -> format("Y-m-d");
            //$fechaFinal["fecahaString"] = "$añoProximo-$mesProximo-$diaPrestamo";
        }else{
            $fechaFinal = $fechaproxima ->format("Y-m-d");
        }
        //$arrarXd["fechaInicial"] = $fechaPInicial -> modify("+".($mes-1)." month") -> format("Y-m-d");
        //Caso especial cuando el dia del sigueinte mes inicail es mayor.
        $mesInicialProximo = $mesPrestamo + ($mes - 1);
        if($mesInicialProximo > 12){
            $arr = explode(".", ($mesInicialProximo / 12));
            $añoProximoInicial = $añoPrestamo + $arr[0];
            $mesProximoInicial = $mesInicialProximo - ($arr[0] * 12);
            //$arrarXd["arr"] = $arr;
        }else{
            $añoProximoInicial = $añoPrestamo;
            $mesProximoInicial = $mesInicialProximo;
        }
        $fechaproximaInicial = new DateTime("$añoProximoInicial-$mesProximoInicial-01");
        $fechaproximaInicial -> modify("last day of this month");//El ultimo dia del me inicial
        if($diaPrestamo > $fechaproximaInicial -> format("d")){
            //Se suma un dia mas
            $fechaInicial = $fechaproximaInicial -> modify("+1 day") -> format("Y-m-d");
        }else{
            $fechaInicial = $fechaPInicial -> modify("+".($mes-1)." month") -> format("Y-m-d");
        }
        //Caso especial cuando el dia del sigueinte mes inicail es mayor.
        $arrarXd["fechaInicial"] = $fechaInicial;
        $arrarXd["fechaFinal"] = $fechaFinal;
        //$arrarXd["fechaFinal"] = $fechaFinal;
        //$arrarXd["añoProximo"] = $añoProximo;
        //$arrarXd["mesProximo"] = $mesProximo;
        //$arrarXd["diaPrestamo"] = $diaPrestamo;
        return $arrarXd;
    }
    public function ajustarPagosMes(){
        $this->validaToken();
        $a = array();
        $error = array();
        $pagos = json_decode(Form::getValue("pagos", false, false));
        /*Recorrer cada mes.
        1.- Detectar si el mes se tiene que abrir.
        2.- Detectar si se tiene que abonar
        3.- Cerrar el mes*/
        //foreach ($pagos as $k => $v)
        $error["abrir"] = array();
        $error["abonar"] = array();
        $error["cerrar"] = array();
        foreach ($pagos as $v){
            $a[$v -> prestamo -> mes] = $v -> prestamo;
            if($v -> prestamo -> abrir){//Abrir el mes
                $insertPago = $this -> p -> abrirM(
                    $v -> prestamo -> prestamo,
                    $v -> prestamo -> mes,
                    $v -> prestamo -> capitalActual,
                    $v -> prestamo -> interesRestMesAnt,
                    $v -> prestamo -> interesGenerado,
                    $v -> prestamo -> fechaInicial,
                    $v -> prestamo -> fechaFinal);
                if(!$insertPago){
                    $error["abrir"][$v -> prestamo -> mes] = $v -> prestamo -> mes;
                }
            }
            /*Consultar el id del ultimo pago activo
            * Consultar pago por mes and status activo and idPrestamo
            *
            */
            $pago = $this -> p -> mostrar("status = 1 and prestamo = " . $v -> prestamo -> prestamo . " and mes = " . $v -> prestamo -> mes, "pago", "max(id) as idPago");

            if($v -> prestamo -> abonar){//Abonar el mes
                $insertAbono= $this -> p -> addAbono(
                    $pago[0] -> idPago,
                    $v -> prestamo -> fecha,
                    $v -> prestamo -> abono,
                    $v -> prestamo -> nota);
                if(!$insertAbono){
                    $error["abonar"][$v -> prestamo -> mes] = $v -> prestamo -> mes;
                }
            }
            if($v -> prestamo -> esAbierto == 0){//Cerrar el mes
                $cerrarMes = $this -> p -> cerrarM(
                    $pago[0] -> idPago,
                    $v -> prestamo -> fecha);
                if(!$cerrarMes){
                    $error["cerrar"][$v -> prestamo -> mes] = $v -> prestamo -> mes;
                }
            }
        }
        $cantERRORGRAL = count($error);
        $cantERRORABRIR = count($error["abrir"]);
        $cantERRORABONAR = count($error["abonar"]);
        $cantERRORCERRAR = count($error["cerrar"]);
        if($cantERRORABRIR > 0 || $cantERRORABONAR > 0 || $cantERRORCERRAR > 0){
            $a["error"] = $cantERRORABRIR + $cantERRORABONAR + $cantERRORCERRAR;
            $a["titulo"] = "Error al ajustar los pagos en meses";
            $a["errorABRIR"] = $cantERRORABRIR;
            if($cantERRORABRIR > 0){
                $a["tituloMsjEAbrir"] = "Meses con errores al Abrir";
                $msjEA = "[";
                foreach ($error["abrir"] as $k => $v) {
                    $msjEA = $msjEA . $v -> mes ."°, ";
                }
                $msjEA = $msjEA . "]";
                $a["MsjEAbrir"] = $msjEA;
            }
            $a["errorABONAR"] = $cantERRORABONAR;
            if($cantERRORABONAR > 0){
                $a["tituloMsjEAbonar"] = "Meses con errores al Abonar";
                $msjEAB = "[";
                foreach ($error["abonar"] as $k => $v) {
                    $msjEAB = $msjEAB . $v -> mes ."°, ";
                }
                $msjEAB = $msjEAB . "]";
                $a["MsjEAbonar"] = $msjEAB;
            }
            $a["errorCERRAR"] = $cantERRORCERRAR;
            if($cantERRORCERRAR > 0){
                $a["tituloMsjECerrar"] = "Meses con errores al Cerrar";
                $msjEC = "[";
                foreach ($error["cerrar"] as $k => $v) {
                    $msjEC = $msjEC . $v -> mes ."°, ";
                }
                $msjEC = $msjEC . "]";
                $a["MsjECerrar"] = $msjEC;
            }
        }else{
            $a["error"] = 0;
            $a["msj"] = "¡ Pagos ajustados en meses correctamente !";
        }

        $a["pagos"] = $pagos;
        $a["erroresGRAL"] = $error;
        return $a;
    }
    public function calcFPInteresGenerado(){
        $ultimoPago = json_decode(Form::getValue("ultimoPago",false,false));//Esto no es neecesario
        $total = json_decode(Form::getValue("total", false, false));
        $idPrestamo = Form::getValue("idPrestamo");
        $fecha = Form::getValue("fechaPago");//Esta es la fecha en la que se realiza el ajuste de pago.
        $a = array();
        $a["prestamo"] = $this -> p -> mostrar("id = $idPrestamo", "vistaprestamosactivos");
        $mesGeneradoFechaPago = $this -> diasTranscurridos(($ultimoPago -> mes > 0) ? $ultimoPago -> fechaFinal: $total -> fechaInicial, $fecha, 1);
        $interesGeneradoFechaPgo = $mesGeneradoFechaPago * ($total -> capitalRestante * $a["prestamo"][0] -> porcentaje);
        $a["mesGeneradoFechaPago"] = $mesGeneradoFechaPago;
        $a["interesGeneradoFechaPgo"] = $interesGeneradoFechaPgo;

        return $a;
    }
    public function calcularPgoMes(){
        $this->validaToken();
        $a = array();
        $ultimoPago = json_decode(Form::getValue("ultimoPago",false,false));//Esto no es neecesario
        $total = json_decode(Form::getValue("totalGenerado", false, false));
        $idPrestamo = Form::getValue("idPrestamo");

        $fecha = Form::getValue("fecha");//Esta es la fecha en la que se realiza el ajuste de pago.
        $cantidadPagar = Form::getValue("cantPagar");
        $notaPagar = Form::getValue("notaPagar");
        $a["fechaPagar"] = $fecha;
        $a["cantidadPagar"] = $cantidadPagar;
        $a["notaPagar"] = $notaPagar;

        /*
         * Realizar una consulta de todos los pagos activos
         *
         * */
        $a["prestamo"] = $this -> p -> mostrar("id = $idPrestamo", "vistaprestamosactivos");

        //Calculo de los mese trancurridos hasta la fecha de Pago
        $mesGeneradoFechaPago = $this -> diasTranscurridos(($ultimoPago -> mes > 0) ? $ultimoPago -> fechaFinal: $total -> fechaInicial, $fecha, 1);
        $interesGeneradoFechaPgo = $mesGeneradoFechaPago * ($total -> capitalRestante * $a["prestamo"][0] -> porcentaje);
        $a["mesGeneradoFechaPago"] = $mesGeneradoFechaPago + $ultimoPago -> mes;
        $a["interesGeneradoFechaPgo"] = $interesGeneradoFechaPgo;
        //Calculo de los mese trancurridos hasta la fecha de Pago

        if($ultimoPago -> mes > 0){//Si el ultimo mes es un numero mayor que cero, osea que exista al menos un mes activo para el prestamo
            $a["pgo"] = $this -> p -> mostrar("v.prestamo = $idPrestamo and id = " . $ultimoPago -> id  . " and v.status = 1", "vistapagototalmes as v");
            $a["numPgo"] = count($a["pgo"]);
            $total -> mesTranscurrido = $total -> mesTranscurrido + $ultimoPago -> mes;
            if($a["pgo"][0] -> esAbierto == 1){//El mes esta abierto
            //if($ultimoPago -> esAbierto == 1){
                /*Hay que corroborar si el interes restate  del ultimo pago esta cmpletado
                 * Calcular los meses transcurridos despues de
                 * Verificar si el interes restante es igual a cero                 *
                 * Si el mes tiene interes restante hay que pagar
                 *Hay que cerrar el ultimo mes
                 * $arregloMes[$mes]["prestamo"]
                 * */
                $interesaplicado = $ultimoPago -> capitalRestante * $a["prestamo"][0] -> porcentaje;
                $ultimoPago -> fecha = $fecha;
                //$ultimoPago -> capitalActual = $ultimoPago -> capitalRestante;
                $ultimoPago -> esAbierto = 0;
                $ultimoPago -> nota = $notaPagar;
                $ultimoPago -> abrir = false;
                $ultimoPago -> abonar = true;

                if($ultimoPago -> interesRestante == 0){//Interes acumulapo pagado, (en cero) pero hay que revisar si se haya pagado algo a capital-.
                    /*Hay que cerrar el ultimo mes y calcular los meses siguientes.*/
                    /*Como no hay interesses a pagar y ya se ceero el ultimo es abierto, lo que sigue es calcular los mese siguientes.*/
                    $ultimoPago -> abonar = false;
                    //$ultimoPago -> abono = false;//No es necesario porque solo se cerrara el mes.

                    $a["MesAjustado"] = $this -> MesAjust($interesaplicado, $fecha, $cantidadPagar, $ultimoPago -> mes + 1, $total, $xd = array(), $a["prestamo"][0], $ultimoPago -> capitalRestante, $a["mesGeneradoFechaPago"], $a["interesGeneradoFechaPgo"], $notaPagar);

                }else{//Falta pagar interes Acumulado
                    /*Comparar si el pago alcnazo el interes restante del ultimo mes*/
                    if($cantidadPagar > $ultimoPago -> interesRestante ){//Si me alcanza el money para pagar el interResrestante del ultimo mes
                        /*Nuevo monto de cantidad a pagar*/
                        $ultimoPago -> abono = $ultimoPago -> interesRestante;//
                        $ultimoPago -> abonoInteres = $ultimoPago -> interesAcumulado;
                        $cantidadPagar = $cantidadPagar - $ultimoPago -> interesRestante;

                        $total -> abonoInteres = $total -> abonoInteres + $ultimoPago -> interesRestante;//El interes restante es el que se pa
                        $total -> interesRestante = $total -> interesAcumulado - $total -> abonoInteres;
                        $total -> totalPagado = $total -> abonoInteres + $total -> abonoCapital;
                        if($a["mesGeneradoFechaPago"] > $ultimoPago -> mes)/*PENDIENTE:[mesgeneradofechappago+ultimopago->mes]*/
                            $a["MesAjustado"] = $this -> MesAjust($interesaplicado, $fecha, $cantidadPagar, $ultimoPago -> mes + 1, $total, $xd = array(), $a["prestamo"][0], $ultimoPago -> capitalRestante, $a["mesGeneradoFechaPago"], $a["interesGeneradoFechaPgo"], $notaPagar);
                        else{
                            $ultimoPago -> abono = $ultimoPago -> interesRestante + $cantidadPagar;
                            $ultimoPago -> abonoCapital = $cantidadPagar;
                            $total -> totalPagado = $total -> abonoInteres + $total -> abonoCapital;
                        }
                    }else{//No me alcanza el money jejeje; solo pagaria lo que alcanze al interesRestante del ultimo mes y yap.
                        //$ultimoPago -> fecha = null;
                        $ultimoPago -> esAbierto = 1;
                        $ultimoPago -> abonoInteres = $ultimoPago -> abonoInteres + $cantidadPagar;
                        $ultimoPago -> abono = $cantidadPagar;//
                    }

                }

                $a["MesAjustado"][$ultimoPago -> mes]["prestamo"] = $ultimoPago;
                //Calculo de total para el primerMesSiguiente
                $a["MesAjustado"][$ultimoPago -> mes]["totalPagado"] = $ultimoPago -> abonoCapital + $ultimoPago -> abonoInteres;
                $a["MesAjustado"][$ultimoPago -> mes]["capitalRestante"] = $ultimoPago -> capitalActual - $ultimoPago -> abonoCapital;
                $a["MesAjustado"][$ultimoPago -> mes]["interesAcumulado"] = $ultimoPago -> interesAcumulado;
                $a["MesAjustado"][$ultimoPago -> mes]["interesRestante"] = $a["MesAjustado"][$ultimoPago -> mes]["interesAcumulado"] - $ultimoPago -> abonoInteres;
            }else{//El mes esta cerrado
                /*
                 * Empezar con el mes siguiente* (calcular manualmete el primer mes que sigue)*/
                /*
                Si el interes restante del ultimo mes sobra
                Si el pago es mayor que la diferencia de
                */
                //Pendiente calcular cuando el interes es igual a cero y exien abonos a capitaL.
                if($ultimoPago -> interesRestante == 0){//El ultimo mes cerro interes acumulado, no importa el capitalRestante.
                    //Faltaria calcular el nuevo cantidad de meses transcurridos.//HECHO.
                    $interesaplicado = $ultimoPago -> capitalRestante * $a["prestamo"][0] -> porcentaje;
                    //$nuevoTotal = $total -> mesTranscurrido + $ultimoPago -> mes;
                    //$total -> mesTranscurrido = $nuevoTotal;
                    $a["MesAjustado"] = $this -> MesAjust($interesaplicado, $fecha, $cantidadPagar, $ultimoPago -> mes + 1, $total, $xd = array(), $a["prestamo"][0], $ultimoPago -> capitalRestante, $a["mesGeneradoFechaPago"], $a["interesGeneradoFechaPgo"], $notaPagar);
                }else{//Aun resta por pagar el interes acumulado
                    /*Calcular el primer mes que sigue
                    Comparar si el pago alcanza o rebasa el interes restante del ultimo pago*/
                    $periodo = $this -> calcularPeriodo($a["prestamo"][0] -> fecha, $ultimoPago -> mes + 1);
                    $interesaplicado = $ultimoPago -> capitalRestante * $a["prestamo"][0] -> porcentaje;
                    $interesAcumulado = $ultimoPago -> interesRestante + $interesaplicado;
                    $primerMesSiguiente = [
                        "prestamo" => $a["prestamo"][0] -> id,
                        "mes" => $ultimoPago -> mes + 1,
                        "periodo" => date("d/m/Y", strtotime($periodo["fechaInicial"])) . " al " . date("d/m/Y", strtotime($periodo["fechaFinal"])),
                        "fechaInicial" => $periodo["fechaInicial"],
                        "fechaFinal" => $periodo["fechaFinal"],
                        "abonoInteres" => $interesAcumulado,
                        "abonoCapital" => 0,
                        "interesRestMesAnt" => $ultimoPago -> interesRestante,
                        "interesGenerado" => $interesaplicado,
                        "capitalActual" => $ultimoPago -> capitalRestante,
                        "abono" => $interesAcumulado,
                        "fecha" => $fecha,
                        "nota" => $notaPagar,
                        "abrir" => true,
                        "abonar" => true,
                        "esAbierto" => 0,
                        "status" => 1,
                    ];
                    //Calcular el nuevo total:
                    $total -> interesMesAcumulado = $total -> interesMesAcumulado + $primerMesSiguiente["interesGenerado"];
                    $total -> abonoInteres = $total -> abonoInteres + $primerMesSiguiente["abonoInteres"];
                    $total -> interesPendiente = $total -> interesPendiente - $primerMesSiguiente["interesGenerado"];
                    $total -> interesAcumulado = $total -> interesMesAcumulado + $total -> interesPendiente;
                    $total -> interesRestante = $total -> interesAcumulado - $total -> abonoInteres;
                    $total -> totalPagado = $total -> abonoInteres + $total -> abonoCapital;

                    if($cantidadPagar > $interesAcumulado){//Si traigo money suficiente para pagar el interes acumulado del prmier siguiete
                        $cantidadPagar = $cantidadPagar - $interesAcumulado;
                        $a["interesGeneradoFechaPgo"] =  $a["interesGeneradoFechaPgo"] - $interesAcumulado;
                        $interesaplicado = $primerMesSiguiente["capitalActual"] * $a["prestamo"][0] -> porcentaje;//Interes aplicado el segundo mesSiguinte
                        /* El primer mes ya esta calculado
                         * Se calculan los mese restantes.
                         * Se seguiran calculando otro meses.
                        Se calcula el monto para este mes y se calculan los meses resantes que aplica para el monto a pagar.*/
                        //abonoInteres: "14250"
                        //$total -> interesMesAcumulado = $total -> interesMesAcumulado + $interesAcumulado;
                        //$total -> interesPendiente = $total -> interesPendiente - $primerMesSiguiente -> abonoInteres;
                        //$total -> interesRestante = $total -> interesAcumulado - $total -> abonoInteres;
                        //$a["primerMesSiguiente"] = $primerMesSiguiente;////
                        //$a["mesGeneradoFechaPago"] = $a["mesGeneradoFechaPago"] + 1;
                        if(($a["mesGeneradoFechaPago"]) > $primerMesSiguiente["mes"])/*PENDIENTE[mesgeneradofechapago+primerMesSiguiente["mes"]]*/
                            $a["MesAjustado"] = $this -> MesAjust($interesaplicado, $fecha, $cantidadPagar, $ultimoPago -> mes + 2, $total, $xd = array(), $a["prestamo"][0], $ultimoPago -> capitalRestante, $a["mesGeneradoFechaPago"], $a["interesGeneradoFechaPgo"], $notaPagar);
                        else{
                            $primerMesSiguiente["abono"] = $primerMesSiguiente["abono"] +  $cantidadPagar;
                            $primerMesSiguiente["abonoCapital"] = $cantidadPagar;
                            $total -> totalPagado = $total -> abonoInteres + $total -> abonoCapital;
                        }
                    }else{
                        /*El pago solo aplicara para este mes y ya*/
                        $primerMesSiguiente["abono"] = $cantidadPagar;
                        $primerMesSiguiente["abonoInteres"] = $cantidadPagar;
                        //$primerMesSiguiente -> fecha = null;
                        $primerMesSiguiente["esAbierto"] = 1;
                    }
                    $a["MesAjustado"][$ultimoPago -> mes + 1]["prestamo"] = $primerMesSiguiente;

                    //Calculo de total para el primerMesSiguiente
                    $a["MesAjustado"][$ultimoPago -> mes + 1]["totalPagado"] = $primerMesSiguiente["abonoCapital"] + $primerMesSiguiente["abonoInteres"];
                    $a["MesAjustado"][$ultimoPago -> mes + 1]["capitalRestante"] = $primerMesSiguiente["capitalActual"] - $primerMesSiguiente["abonoCapital"];
                    $a["MesAjustado"][$ultimoPago -> mes + 1]["interesAcumulado"] = $primerMesSiguiente["interesRestMesAnt"] + $primerMesSiguiente["interesGenerado"];
                    $a["MesAjustado"][$ultimoPago -> mes + 1]["interesRestante"] = $a["MesAjustado"][$ultimoPago -> mes + 1]["interesAcumulado"] - $primerMesSiguiente["abonoInteres"];
                }
            }
        }else{//No tiene registros, o sea el prestamo aun no tiene pagos.
            $a["numPgo"] = count($a["prestamo"]);
            $interesaplicado = $a["prestamo"][0] -> cantidad * $a["prestamo"][0] -> porcentaje;
            //$pagoRestante = $total -> interesPendiente - $cantidadPagar;
            /*  Si el pagorestante es mayor que 0, se aplicara al capital restante
                Sino, solo se aplicara para el interesPendiente.*/
            $a["MesAjustado"] = $this -> MesAjust($interesaplicado, $fecha, $cantidadPagar, 1, $total, $xd = array(), $a["prestamo"][0], $a["prestamo"][0] -> cantidad, $a["mesGeneradoFechaPago"], $a["interesGeneradoFechaPgo"], $notaPagar);
        }
        $a["total"] = $total;
        $a["ultimoPago"] = $ultimoPago;
        return $a;
    }
    private function MesAjust($interesAplicado, $fechaPago, $cantPago, $mes, $total, $arregloMes, $prestamo, $capitalActual, $FPMesGenerado, $FPInteresGenerado, $nota){
        if($cantPago == 0 || $FPInteresGenerado == 0)return [];
        if($cantPago >= $FPInteresGenerado){//Quiere decir que sobrara para aplicarlo a abono capital en el ultimo mex
            return $this -> calcMesAjustt($interesAplicado, $fechaPago, $cantPago, $mes, $total, $arregloMes, $prestamo, $capitalActual, $FPMesGenerado, $nota);
        }else{//Significa que no se acompletarn  todos los pagos de los meses transcurridos y quedaran pendientes mese por pagar intereses
            return $this -> calcMesAjust($interesAplicado, $fechaPago, $cantPago, $mes, $arregloMes, $prestamo, $capitalActual, $nota);
        }
    }
    private function calcMesAjustt($interesAplicado, $fechaPago, $cantPago, $mes, $total, $arregloMes, $prestamo, $capitalActual, $FPMesGenerado, $nota){
        if ($mes == ($FPMesGenerado + 1)){
            $arregloMes[$mes - 1]["prestamo"]["abonoCapital"] = $cantPago;
            $arregloMes[$mes - 1]["prestamo"]["abono"] = $arregloMes[$mes - 1]["prestamo"]["abonoCapital"] + $arregloMes[$mes - 1]["prestamo"]["abonoInteres"];
            //$arregloMes[$mes - 1]["prestamo"]["fecha"] = null;
            $arregloMes[$mes - 1]["prestamo"]["esAbierto"] = ($arregloMes[$mes - 1]["interesRestante"] == 0) ? 0 : 1;//Si es mayor a cero abono abono a capital

            $arregloMes[$mes - 1]["totalPagado"] = $arregloMes[$mes - 1]["prestamo"]["abonoCapital"] + $arregloMes[$mes - 1]["prestamo"]["abonoInteres"];
            $arregloMes[$mes - 1]["capitalRestante"] = $arregloMes[$mes - 1]["prestamo"]["capitalActual"] - $arregloMes[$mes - 1]["prestamo"]["abonoCapital"];

            return $arregloMes;
        }else{
            $periodo = $this -> calcularPeriodo($prestamo -> fecha, $mes);
            $arregloMes[$mes]["prestamo"] = [
                "prestamo" => $prestamo -> id,
                "mes" => $mes,
                "periodo" => date("d/m/Y", strtotime($periodo["fechaInicial"])) . " al " . date("d/m/Y", strtotime($periodo["fechaFinal"])),
                "fechaInicial" => $periodo["fechaInicial"],
                "fechaFinal" => $periodo["fechaFinal"],
                "abonoInteres" => ($cantPago >= $interesAplicado) ? $interesAplicado : $cantPago,
                "abonoCapital" => 0,
                "interesRestMesAnt" => 0,
                "interesGenerado" => $interesAplicado,
                "capitalActual" => $capitalActual,
                "abono" => ($cantPago >= $interesAplicado) ? $interesAplicado : $cantPago,
                "fecha" => $fechaPago,
                "nota" => $nota,
                "abrir" => true,
                "abonar" => true,
                "esAbierto" => 0,
                "status" => 1,
            ];
            /*$arregloMes[$mes]["mes"] = $mes;
            //$arregloMes[$mes]["abonoCapital"] = 0;
            $arregloMes[$mes]["abonoInteres"] = $interesAplicado;
            $arregloMes[$mes]["interesAplicado"] = $interesAplicado;
            $arregloMes[$mes]["pagoRestante"] = $cantPago;*/

            $arregloMes[$mes]["totalPagado"] = $arregloMes[$mes]["prestamo"]["abonoCapital"] + $arregloMes[$mes]["prestamo"]["abonoInteres"];
            $arregloMes[$mes]["capitalRestante"] = $arregloMes[$mes]["prestamo"]["capitalActual"] - $arregloMes[$mes]["prestamo"]["abonoCapital"];
            $arregloMes[$mes]["interesAcumulado"] = $arregloMes[$mes]["prestamo"]["interesRestMesAnt"] + $arregloMes[$mes]["prestamo"]["interesGenerado"];
            $arregloMes[$mes]["interesRestante"] = $arregloMes[$mes]["interesAcumulado"] - $arregloMes[$mes]["prestamo"]["abonoInteres"];

            $cantPago = $cantPago - $interesAplicado;
            return $this -> calcMesAjustt($interesAplicado, $fechaPago, $cantPago, ($mes + 1), $total, $arregloMes, $prestamo, $capitalActual, $FPMesGenerado, $nota);
        }
    }
    private function calcMesAjust($interesAplicado, $fechaPago, $cantPago, $mes, $arregloMes, $prestamo, $capitalActual, $nota){
        if ($cantPago > 0) {
            $periodo = $this -> calcularPeriodo($prestamo -> fecha, $mes);
            $arregloMes[$mes]["prestamo"] = [
                "prestamo" => $prestamo -> id,
                "mes" => $mes,
                "periodo" => date("d/m/Y", strtotime($periodo["fechaInicial"])) . " al " . date("d/m/Y", strtotime($periodo["fechaFinal"])),
                "fechaInicial" => $periodo["fechaInicial"],
                "fechaFinal" => $periodo["fechaFinal"],
                "abonoInteres" => ($cantPago >= $interesAplicado) ? $interesAplicado : $cantPago,
                "abonoCapital" => 0,
                "interesRestMesAnt" => 0,
                "interesGenerado" => $interesAplicado,
                "capitalActual" => $capitalActual,
                "abono" => ($cantPago >= $interesAplicado) ? $interesAplicado : $cantPago,
                "fecha" => $fechaPago,
                "nota" => $nota,
                "abrir" => true,
                "abonar" => true,
                "esAbierto" => 0,
                "status" => 1,
            ];
            /*$arregloMes[$mes]["mes"] = $mes;
            $arregloMes[$mes]["abonoCapital"] = 0;
            $arregloMes[$mes]["abonoInteres"] = ($cantPago >= $interesAplicado)? $interesAplicado : $cantPago;
            $arregloMes[$mes]["interesAplicado"] = $interesAplicado;
            $arregloMes[$mes]["pagoRestante"] = $cantPago;*/

            $arregloMes[$mes]["totalPagado"] = $arregloMes[$mes]["prestamo"]["abonoCapital"] + $arregloMes[$mes]["prestamo"]["abonoInteres"];
            $arregloMes[$mes]["capitalRestante"] = $arregloMes[$mes]["prestamo"]["capitalActual"] - $arregloMes[$mes]["prestamo"]["abonoCapital"];
            $arregloMes[$mes]["interesAcumulado"] = $arregloMes[$mes]["prestamo"]["interesRestMesAnt"] + $arregloMes[$mes]["prestamo"]["interesGenerado"];
            $arregloMes[$mes]["interesRestante"] = $arregloMes[$mes]["interesAcumulado"] - $arregloMes[$mes]["prestamo"]["abonoInteres"];

            $cantPago = $cantPago - $interesAplicado;
            return $this -> calcMesAjust($interesAplicado, $fechaPago, $cantPago, ($mes + 1), $arregloMes, $prestamo, $capitalActual, $nota);
        } else {
            //$arregloMes[$mes - 1]["prestamo"]["fecha"] = null;
            //$arregloMes[$mes - 1]["prestamo"]["esAbierto"] = 1;
            $arregloMes[$mes - 1]["prestamo"]["esAbierto"] = ($arregloMes[$mes - 1]["interesRestante"] == 0) ? 0 : 1;//Si es mayor a cero abono abono a capital

            return $arregloMes;
        }
    }

    public function cargarPagos(){
        $this -> validaToken();
        $this -> varGlobales();

        $Pagos = array();
        $select = $this -> p -> mostrar("prestamo = " . $this -> IDPrestamo, "vistapagototalmes");
        $Pagos["error"] = 0;
        $total = count($select);
        $Pagos["totalGral"] = $this -> calcularTotalGral();
        if($total > 0){
            $Pagos["row"] = $total;
            $Pagos["msj"] = "Pagos encontrados";
            $Abonos = array();
            foreach ($select as $pago){
                $Abonos["". $pago -> id . ""] =  $this -> p ->  mostrar("pago = " .$pago -> id . " order by fecha ASC", "abonoP as ab, (SELECT @rownum:=0) as r", "if(ab.status = 1 , @rownum:=@rownum+1, 0) as pos, ab.*");      //   $Pagos["pruebaJejeje"] = Form::esMenor("2019-02-02", "2019-02-01");

            }
            $Pagos["pagos"] = $select;
            $Pagos["abonos"] = $Abonos;
            $Pagos["ultimoPagoActivo"] = $this -> p -> mostrar("status = 1 and prestamo = " . $this -> IDPrestamo . " and id = (select max(id) from )", "vistapagototalmes", "capitalRestante");
            $Pagos["rowTotal"] = count($Pagos["ultimoPagoActivo"]);
            $Pagos["cerrarPrestamo"] = ($Pagos["rowTotal"] > 0) ? ($Pagos["ultimoPagoActivo"][0] -> capitalRestante == 0) ? true: false : false;

        }else{
            $Pagos["row"] = 0;
            $Pagos["titulo"] = ($this -> ESActivo == 1) ? "Sin Pagos" : "Error de solicitud" ;
            $Pagos["msj"] = ($this -> ESActivo == 1) ? "¡ El prestamo solicitado aun no tiene ningun pago realizado !" : "¡ Error al recuperar los pagos del prestamo solicitado !" ;
            $Pagos["cerrarPrestamo"] = false;
        }
        return $Pagos;
    }
    private function calcularTotalGral(){
        $tabla = ($this -> ESActivo == 1)? "vistatotalgralmespa": "vistatotalgralmespc";
        $totalGenerado = $this -> p -> mostrar("idPrestamo = " . $this -> IDPrestamo, $tabla);
        $array = array();
        if(count($totalGenerado) > 0){//Validando si existen pagos de este mes
            $array["abonoCapital"] = $totalGenerado[0] -> abonoCapital;
            $array["abonoInteres"] = $totalGenerado[0] -> abonoInteres;
            $array["capitalPrestado"] = $totalGenerado[0] -> capitalPrestado;
            $array["capitalRestante"] = $array["capitalPrestado"] - $array["abonoCapital"];
            $array["totalPagado"] = $array["abonoCapital"] + $array["abonoInteres"];
            if($this -> ESActivo == 1) {
                //$Pagos["difFechas"] = Form::diferenciaFechas($totalGenerado[0] -> fechaPrestamo, $this -> FechaActual);
                $mesTranscurrido = $this -> diasTranscurridos($totalGenerado[0] -> fechaFinal, $this -> FechaActual, 1);

                $interesPendiente = ($mesTranscurrido * (($totalGenerado[0] -> capitalPrestado - $totalGenerado[0] -> abonoCapital) * $totalGenerado[0] -> porcentaje));
                $interesMesAcumulado = $totalGenerado[0] -> interesGenerado;

                //$interesGenerado = ($mesTranscurrido * (($totalGenerado[0] -> capitalPrestado - $totalGenerado[0] -> abonoCapital) * $totalGenerado[0] -> porcentaje)) + ($totalGenerado[0] -> interesGenerado);
                //$Pagos["totalGenerado"] = $totalGenerado;
                $array["mesTranscurrido"] = $mesTranscurrido;
                $array["fechaInicial"] = $totalGenerado[0] -> fechaFinal;
                $array["fechaActual"] = $this -> FechaActual;
                $array["interesPendiente"] = $interesPendiente;
                $array["interesMesAcumulado"] = $interesMesAcumulado;

                $array["interesAcumulado"] = $interesPendiente + $interesMesAcumulado;
                $array["interesRestante"] = $array["interesAcumulado"] - $array["abonoInteres"];

                $array["granTotal"] = $array["interesRestante"] + $array["capitalRestante"];
                $array["cerrarPrestamo"] = ($array["capitalRestante"] == 0 && $array["interesRestante"] == 0) ? true : false;
            }
        }else{//No existen pagos de este mes
            /*Programr todo para la accion de n encontrar nicung registro devolver lasc cantifad totales y ceros y al fianl un return*/
            $tabla = ($this -> ESActivo == 1)? "vistaprestamosactivos": "vistaprestamoscerrados";
            $totalGenerado = $this -> p -> mostrar("id = " . $this -> IDPrestamo, $tabla);

            $array["abonoCapital"] = 0;
            $array["abonoInteres"] = 0;
            $array["capitalPrestado"] = $totalGenerado[0] -> cantidad;
            $array["capitalRestante"] = $totalGenerado[0] -> cantidad;
            $array["totalPagado"] = 0;

            if($this -> ESActivo == 1) {
                $mesTranscurrido = $this->diasTranscurridos($totalGenerado[0] -> fecha, $this -> FechaActual, 1);

                $interesPendiente = ($mesTranscurrido * ($array["capitalPrestado"] * $totalGenerado[0] -> porcentaje));
                $interesMesAcumulado = 0;

                //$interesGenerado = ($mesTranscurrido * ($array["capitalPrestado"] * $totalGenerado[0] -> porcentaje));
                //$Pagos["totalGenerado"] = $totalGenerado;
                $array["mesTranscurrido"] = $mesTranscurrido;
                $array["fechaInicial"] = $totalGenerado[0] -> fecha;
                $array["fechaActual"] = $this -> FechaActual;
                $array["interesPendiente"] = $interesPendiente;
                $array["interesMesAcumulado"] = $interesMesAcumulado;

                $array["interesAcumulado"] = $interesPendiente;
                $array["interesRestante"] = $interesPendiente;

                $array["granTotal"] = $array["interesRestante"] + $array["capitalRestante"];
                $array["cerrarPrestamo"] = ($array["capitalRestante"] == 0 && $array["interesRestante"] == 0) ? true : false;
            }
        }
        return $array;
    }
    private function dayTrans($fechaPrestamo, $fechaActual, $mes){
        $fechaP = new DateTime($fechaPrestamo);
        $añoP = $fechaP -> format("Y");
        $mesP = $fechaP -> format("m");
        $diaP = $fechaP -> format("d");
        $fechaA = new DateTime($fechaActual);
        $añoA = $fechaA -> format("Y");
        $mesA = $fechaA -> format("m");
        $diaA = $fechaA -> format("d");
        if($añoP == $añoA && $mesP == $mesA){
            if($diaA <= $diaP) return 0; else return 1;
        }else{
            $mesCalculado = $mesP + $mes;
            if($mesCalculado > 12){
                $arr = explode(".", ($mesCalculado / 12));
                $añoProximo = $añoP + $arr[0];
                $mesProximo = $mesCalculado - ($arr[0] * 12);
                //$arrarXd["arr"] = $arr;
            }else{
                $añoProximo = $añoP;
                $mesProximo = $mesCalculado;
            }
            $fechaProxima = new DateTime("$añoProximo-$mesProximo-01") ;
            $fechaProxima -> modify("last day of this month");
            $diaProximo = $fechaProxima -> format("d");
            if($diaP <= $diaProximo){//Si el dia de prestamo es menor o igual al ultimo dia del mes que viene
                $diaP = $diaP;
            }else{
                $diaP = $diaProximo;
            }
            if($añoProximo == $añoA && $mesProximo == $mesA){//
                if($diaA <= $diaP){
                    return $mes;
                }else{
                    return $mes + 1;
                }
            }else{
                return $this -> dayTrans($fechaPrestamo, $fechaActual, $mes + 1);
            }
        }
    }
    private function diasTranscurridos($fechaPrestamo, $fechaActual, $mes){
        if(Form::esMenor($fechaActual, $fechaPrestamo)){//La fecha actual es menor que la fecha inicial
            return 0;
        }else{
            return $this -> dayTrans($fechaPrestamo, $fechaActual, $mes);
        }
    }
}