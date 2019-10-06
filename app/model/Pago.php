<?php
/**
 * Created by PhpStorm.
 * User: hills
 * Date: 03/02/2019
 * Time: 09:16 AM
 */

class Pago extends DB{

    public function mostrar($where = 1, $tablas = "pago", $select = "*")    {
        return $this -> getDatos($tablas, $select, $where);//["x"=>"id","nom"=>"nombreA"]
    }
    public function anularM($idPago){
        $pago = [
            "status" => 0,
        ];
        return $this -> update("pago", $pago, "id = $idPago");
    }
    public function elimPM($idPago){
        return $this -> delete("pago", "id = $idPago");
    }
    public function volverAbrirM($idPago){
        $pago = [
            "fecha" => "null",
            "esAbierto" => 1
        ];
        return $this -> update("pago", $pago, "id = $idPago");
    }
    public function restaurarM($idPago){
        $pago = [
            "status" => 1
        ];
        return $this -> update("pago", $pago, "id = $idPago");
    }
    public function cerrarM($idPago, $fecha){
        $pago = [
            "fecha" => "'$fecha'",
            "esAbierto" => 0
        ];
        return $this -> update("pago", $pago, "id = $idPago");
    }
    public function abrirM($idP, $mes, $capActual, $intRestMesAnt, $intGenerado, $fechaInicial, $fechaFinal){
        $pago = [
            "id" => 0,
            "prestamo" => $idP,
            "mes" => $mes,
            "fechaInicial" => "'$fechaInicial'",
            "fechaFinal" => "'$fechaFinal'",

            "interesRestMesAnt" => $intRestMesAnt,
            "interesGenerado" => $intGenerado,

            "capitalActual" => $capActual,
            "esAbierto" => 1,
            "status" => 1
        ];
        return $this -> insert("pago", $pago);
    }
    public function elimPMA($idAbono){
        return $this -> delete("abonoP", "id = $idAbono");
    }
    public function updateStatusAbono($idAbono, $status){
        $abono = [
            "status" => $status
        ];
        return $this -> update("abonoP", $abono, "id = $idAbono");
    }
    public function updateAbono($idAbono, $fecha, $cantidad, $nota){
        $abono = [
            "fecha" => "'$fecha'",
            "cantidad" => $cantidad,
            "nota" => "'$nota'"
        ];
        return $this -> update("abonoP", $abono,"id = $idAbono");
    }
    public function addAbono($idPago, $fecha, $cantidad, $nota){
        $abono = [
            "id" => 0,
            "pago" => $idPago,
            "fecha" => "'$fecha'",
            "cantidad" => $cantidad,
            "nota" => "'$nota'",
            "status" => 1
        ];
        return $this -> insert("abonoP", $abono);
    }

}