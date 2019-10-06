<?php
	Ruta::get("home","Home@index");

	Ruta::post("login","ControlUsuario@login");
	Ruta::post("usuario/login","ControlUsuario@login");
    Ruta::post("usuario/probarxd","ControlUsuario@probarXD");

    Ruta::get("clientes/mostrar","ControlCliente@consultarTodos");
    Ruta::get("clientes/mostrarClientesEliminados","ControlCliente@consultaClienteEliminados");
    Ruta::get("clientes/mostrarUltimo","ControlCliente@obtUltimo");
    Ruta::post("clientes/agregar","ControlCliente@agregarCliente");
    Ruta::post("clientes/editar","ControlCliente@editarCliente");
    Ruta::post("clientes/eliminar","ControlCliente@eliminarCliente");
Ruta::post("clientes/eliminarPmnteCliente","ControlCliente@eliminarPmnteCliente");
Ruta::post("clientes/restaurarCliente","ControlCliente@restaurarCliente");

    Ruta::get("bienes/mostrar","ControlBien@consultarTodos");
    Ruta::post("bienes/agregar","ControlBien@agregarBien");

    Ruta::get("intereses/mostrar","ControlInteres@consultarTodos");

    Ruta::post("prestamos/agregarActivos","ControlPrestamo@agregarPrestamo");
    Ruta::post("prestamos/editarActivos","ControlPrestamo@editarPrestamo");
    Ruta::get("prestamos/mostrarActivos","ControlPrestamo@consultarPrestamosActivos");
    Ruta::get("prestamos/mostrarPrestamosEliminados","ControlPrestamo@consultaPrestamosEliminados");
    Ruta::get("prestamos/mostrarPrestamosCerrados","ControlPrestamo@consultaPrestamosCerrados");
    Ruta::post("prestamos/eliminarLogicaPrestamo","ControlPrestamo@eliminarLogicaPrestamo");
    Ruta::post("prestamos/eliminarPmntePrestamo","ControlPrestamo@eliminarPmntePrestamo");
    Ruta::post("prestamos/cerrarPrestamoActivo","ControlPrestamo@cerrarPrestamoActivo");
    Ruta::post("prestamos/deshacerCierrePrestamo","ControlPrestamo@deshacerCierrePrestamo");
    Ruta::post("prestamos/restaurarPrestamo","ControlPrestamo@restaurarPrestamo");

    Ruta::post("pagos/abrirMes", "ControlPago@abrirMes");
    Ruta::post("pagos/cerrarMes", "ControlPago@cerrarMes");
    Ruta::post("pagos/anularMes", "ControlPago@anularMes");
    Ruta::post("pagos/volverAbrirMes", "ControlPago@volverAbrirMes");
    Ruta::post("pagos/elimPermMes", "ControlPago@eliminarPermMes");
    Ruta::post("pagos/restaurarMes", "ControlPago@restaurarMes");
    Ruta::get("pagos/cargarPagos", "ControlPago@cargarPagos");


    Ruta::post("pago/agregarAbono", "ControlPago@agregarAbono");
    Ruta::post("pago/cancelarAbono", "ControlPago@cancelarAbono");
    Ruta::post("pago/rehacerAbono", "ControlPago@rehacerAbono");
    Ruta::post("pago/editarAbono", "ControlPago@editarAbono");
    Ruta::post("pago/elimPmnteAbono", "ControlPago@elimPmnteAbono");
    Ruta::get("pago/calcularPgoMes", "ControlPago@calcularPgoMes");
    Ruta::post("pago/ajustarPGOMES", "ControlPago@ajustarPagosMes");
    Ruta::post("pagos/calcFechaPagoInteresGenerado", "ControlPago@calcFPInteresGenerado");




    Ruta::post("abonos/agregar","ControlAbono@agregarAbono");
    Ruta::post("abonos/editar","ControlAbono@editarAbono");
    Ruta::get("abonos/mostrarAbonos","ControlAbono@cargarAbonos");
    Ruta::get("abonos/calcularMonto","ControlAbono@calcularMonto");
Ruta::post("abonos/anularAbono","ControlAbono@anularAbono");
Ruta::post("abonos/eliminarPermanentementeAbono","ControlAbono@eliminarPermanentementeAbono");
Ruta::post("abonos/restaurarAbono","ControlAbono@restaurarAbono");
Ruta::post("abonos/recuperaDatos","ControlAbono@recuperaDatos");



