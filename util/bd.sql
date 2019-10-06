/*DROP DATABASE IF EXISTS montezco_prestwily;
CREATE DATABASE montezco_prestwily;*/
USE montezco_prestwily;/*id8536940_prestwily;*/

CREATE TABLE usuario(
    id INT AUTO_INCREMENT,
    usuario VARCHAR(20),
    pass VARCHAR(255),
    nombre VARCHAR(100),
    status BIT,
    PRIMARY KEY(id),
    UNIQUE(usuario),
    token VARCHAR(255)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE permiso(
    clave VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(255),
    status BIT
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE permite(
    permiso VARCHAR(10),
    usuario INT,
    status BIT,
    FOREIGN KEY(permiso) REFERENCES permiso(clave) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY(usuario) REFERENCES usuario(id) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY(permiso,usuario)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE cliente(
  id INT AUTO_INCREMENT,
  nombre VARCHAR(100),
  apellido VARCHAR(100),
  sexo BIT,
  direccion VARCHAR(100),
  telefono VARCHAR(10),
  status BIT,
  PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE bien(
  id INT AUTO_INCREMENT,
  nombre VARCHAR(100) unique,
  tipo BIT,
  status BIT,
  PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE interes(
   id INT AUTO_INCREMENT,
   porcentaje FLOAT(3,3),
   status BIT,
   PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE prestamo(
   id INT AUTO_INCREMENT,
   cliente INT,
   bien INT NULL,
   interes INT,
   fecha DATE,
   fechaCierre DATE,
   razon VARCHAR(100),
   esGarantia BIT,/**/
   observacion VARCHAR(100) NULL,/**/
   cantidad INT,
   estado_bien VARCHAR(255),
   esPrestamo BIT,
   status BIT,
   PRIMARY KEY(id) ,
   FOREIGN KEY(cliente) REFERENCES cliente(id),
   FOREIGN KEY(bien) REFERENCES bien(id),
   FOREIGN KEY(interes) REFERENCES interes(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE pago(
   id INT AUTO_INCREMENT,
   prestamo INT,
   mes INT,
   fechaInicial DATE,
   fechaFinal DATE,
   abonoInteres INT default 0,
   abonoCapital INT default 0,
   interesRestMesAnt INT,
   interesGenerado INT,
   capitalActual INT,
   fecha DATE,
   nota VARCHAR(255),
   esAbierto BIT,
   status BIT,
   PRIMARY KEY(id),
   FOREIGN KEY(prestamo) REFERENCES prestamo(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE abonoP(
   id INT AUTO_INCREMENT,
   pago INT,
   fecha DATE,
   cantidad INT,
   nota VARCHAR(255),
   status BIT,
   PRIMARY KEY(id),
   FOREIGN KEY(pago) REFERENCES pago(id) ON DELETE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE VIEW
  vistaclientes AS
SELECT id, nombre, apellido, CONCAT(nombre, ' ', apellido) as nombreCliente, sexo, direccion, telefono FROM cliente where status=1 ORDER BY id DESC;

CREATE VIEW
  vistaclienteseliminados AS
SELECT id, CONCAT(nombre, ' ', apellido) as nombreCliente, sexo, direccion, telefono FROM cliente where status=0;

CREATE VIEW vistaprestamosactivos AS SELECT p.*, c.id as idCliente , CONCAT(c.nombre, ' ', c.apellido) as nombreCliente, c.sexo, c.direccion, c.telefono, b.id as idBien, b.nombre as nombreBien, b.tipo, i.id as idInteres ,i.porcentaje FROM prestamo as p, cliente as c, bien as b, interes as i where p.status=1 and p.esPrestamo=1 and c.status=1 and p.cliente=c.id and (p.bien = b.id OR (p.bien IS NULL and b.id = 1)) and p.interes=i.id ORDER BY p.fecha DESC;

CREATE VIEW vistaprestamoscerrados AS SELECT p.*, c.id as idCliente , CONCAT(c.nombre, ' ', c.apellido) as nombreCliente, c.sexo, c.direccion, c.telefono, b.id as idBien, b.nombre as nombreBien, b.tipo, i.id as idInteres ,i.porcentaje FROM prestamo as p, cliente as c, bien as b, interes as i where p.status=1 and p.esPrestamo=0 and c.status=1 and p.cliente=c.id and (p.bien = b.id OR (p.bien IS NULL and b.id = 1)) and p.interes=i.id ORDER BY p.fecha DESC;

CREATE VIEW vistaprestamosactivoseliminados AS SELECT p.*, c.id as idCliente , CONCAT(c.nombre, ' ', c.apellido) as nombreCliente, c.sexo, c.direccion, c.telefono, b.id as idBien, b.nombre as nombreBien, b.tipo, i.id as idInteres ,i.porcentaje FROM prestamo as p, cliente as c, bien as b, interes as i where p.status=0 and p.esPrestamo=1 and c.status=1 and p.cliente=c.id and (p.bien = b.id OR (p.bien IS NULL and b.id = 1)) and p.interes=i.id ORDER BY p.fecha DESC;

CREATE VIEW vistaprestamoscerradoseliminados AS SELECT p.*, c.id as idCliente , CONCAT(c.nombre, ' ', c.apellido) as nombreCliente, c.sexo, c.direccion, c.telefono, b.id as idBien, b.nombre as nombreBien, b.tipo, i.id as idInteres ,i.porcentaje FROM prestamo as p, cliente as c, bien as b, interes as i where p.status=0 and p.esPrestamo=0 and c.status=1 and p.cliente=c.id and (p.bien = b.id OR (p.bien IS NULL and b.id = 1)) and p.interes=i.id ORDER BY p.fecha DESC;


/*Funcion almacenada para eliminar permanentemente*/
DELIMITER //
CREATE FUNCTION elimPermCliente (idCliente int) RETURNS int
BEGIN

DECLARE done INT DEFAULT FALSE;
  DECLARE res INT;
  DECLARE idP INT;
  DECLARE consulta CURSOR FOR SELECT id FROM prestamo where cliente = idCliente;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN consulta;

  read_loop: LOOP
    FETCH consulta INTO idP;
    IF done THEN
      LEAVE read_loop;
    END IF;
    delete from prestamo where id = idP;
  END LOOP;

  CLOSE consulta;
  delete from cliente where id = idCliente;
  set res = (select id from cliente where id = idCliente);

  return res;
END; //
DELIMITER ;

DELIMITER //
CREATE FUNCTION elimPermPrestamo (idPrestamo int) RETURNS int
BEGIN

  DECLARE res INT;

  delete from prestamo where id = idPrestamo;
  set res = (select id from prestamo where id = idPrestamo);

  return res;
END; //
DELIMITER ;



/*Funcion pra sumar todos los abonos de un prestamo*/
DELIMITER //
CREATE FUNCTION sumarAbonosPago (idPago int) RETURNS int
BEGIN

  DECLARE suma INT default 0;

  set suma = (select sum(ab.cantidad) from abonoP as ab where ab.status = 1 and ab.pago = idPago);

  if(suma is null ) then
    set suma = 0;
  end if;

  return suma;

END; //
DELIMITER ;

DELIMITER //
CREATE FUNCTION OpenPago () RETURNS BOOLEAN
BEGIN
  return false;
END; //
DELIMITER ;

/*VistapagototalMes*/
CREATE VIEW
  vistapagototalmes AS
SELECT p.*, (OpenPago())as abierto,CONCAT(DATE_FORMAT(p.fechaInicial, '%d/%m/%Y'), ' al ', DATE_FORMAT(p.fechaFinal, '%d/%m/%Y')) as periodo, (p.abonoInteres + p.abonoCapital) as totalPagado, (p.interesRestMesAnt + p.interesGenerado) as interesAcumulado, ((p.interesRestMesAnt + p.interesGenerado) - p.abonoInteres) as interesRestante, (p.capitalActual - p.abonoCapital) as capitalRestante, (SELECT sumarAbonosPago(p.id)) as sumaAbonos FROM pago AS p
ORDER BY p.prestamo ASC;

CREATE VIEW
  vistatotalgralmespa AS
SELECT vpm.prestamo as idPrestamo, sum(vpm.abonoCapital) as abonoCapital, sum(vpm.abonoInteres) as abonoInteres, sum(vpm.interesGenerado) as interesGenerado, sum(vpm.interesAcumulado) as interesAcumulado, sum(vpm.interesRestante) as interesRestante, vpa.cantidad as capitalPrestado, vpa.fecha as fechaPrestamo, vpa.porcentaje, max(vpm.fechaInicial) as fechaInicial, max(vpm.fechaFinal) as fechaFinal FROM vistapagototalmes as vpm, vistaprestamosactivos as vpa where vpa.id = vpm.prestamo and vpm.status = 1 group by vpm.prestamo;

CREATE VIEW
  vistatotalgralmespc AS
SELECT vpm.prestamo as idPrestamo, sum(vpm.abonoCapital) as abonoCapital, sum(vpm.abonoInteres) as abonoInteres, sum(vpm.interesGenerado) as interesGenerado, sum(vpm.interesAcumulado) as interesAcumulado, sum(vpm.interesRestante) as interesRestante, vpa.cantidad as capitalPrestado, vpa.fecha as fechaPrestamo, vpa.porcentaje, max(vpm.fechaInicial) as fechaInicial, max(vpm.fechaFinal) as fechaFinal FROM vistapagototalmes as vpm, vistaprestamoscerrados as vpa where vpa.id = vpm.prestamo and vpm.status = 1 group by vpm.prestamo;


/*Triger para la tabla abonoP*/
DELIMITER //
CREATE TRIGGER actualizarTotalPagoDespuesAgregarAbono
    AFTER INSERT ON abonoP
    FOR EACH ROW
BEGIN

    declare sumaAbonos int;
    declare capitalAct int;
    declare interesAcum int;
    declare abInteres int DEFAULT 0;
    declare abCapital int DEFAULT 0;

    set sumaAbonos = (SELECT sumarAbonosPago(NEW.pago));
    set interesAcum = (select interesAcumulado from vistapagototalmes where id = NEW.pago);

    set abInteres = sumaAbonos;
    IF (sumaAbonos >= interesAcum) THEN
      set abInteres = interesAcum;
      set abCapital = sumaAbonos - interesAcum;
    END IF;

    UPDATE pago set abonoInteres = abInteres, abonoCapital = abCapital where id = NEW.pago;

END; //
DELIMITER ;

DELIMITER //
CREATE TRIGGER actualizarTotalPagoDespuesActualizarAbono
    AFTER UPDATE ON abonoP
    FOR EACH ROW
BEGIN

    declare sumaAbonos int;
    declare capitalAct int;
    declare interesAcum int;
    declare abInteres int DEFAULT 0;
    declare abCapital int DEFAULT 0;

    set sumaAbonos = (SELECT sumarAbonosPago(NEW.pago));
    set interesAcum = (select interesAcumulado from vistapagototalmes where id = NEW.pago);

    set abInteres = sumaAbonos;
    IF (sumaAbonos >= interesAcum) THEN
      set abInteres = interesAcum;
      set abCapital = sumaAbonos - interesAcum;
    END IF;

    UPDATE pago set abonoInteres = abInteres, abonoCapital = abCapital where id = NEW.pago;

END; //
DELIMITER ;

INSERT INTO usuario VALUES
(1, 'wily', '$2y$10$OZlG0OOU9KubBrXp1A/laOcDBI5Qjm4nLT/ESmP2SVYRFAGNV.k46', 'Guillermo Ventura', b'1', '$2y$10$40vLFlg.sXl85NRNXvf/EeSkkxuIJljOa93bqJuppHwLRzRws/IvG'),
(2, 'salaAdmin', '$2y$10$1.KKtBG/APWONWZ8WO8yrOzx6Qh3Ky3Mg/ifRihDeJ1nPLoGvTd.C', 'Salatiel', b'1', NULL);

/*
INSERT INTO `cliente` (`id`, `nombre`, `apellido`, `sexo`, `direccion`, `telefono`, `status`) VALUES
(1, 'Salatiel', 'Montero González', b'1', 'Conocido', '9711234563', b'1'),
(2, 'Pedro', 'Other Other', b'1', 'Conocido', '1234567890', b'1'),
(3, 'Yoli Celeste', 'Montero González', b'0', 'Conocida', '9874561230', b'1'),
(4, 'Alan Jared', 'Montero González', b'1', 'Conocido', '5612304789', b'1'),
(5, 'Amiga', 'Conocido', b'0', 'Av. Francisco I. Madero', '1234567890', b'1'),
(6, 'Hermana', 'Conocido', b'0', 'Conocido', '1234567890', b'1'),
(7, 'Ginak', 'Montero', b'1', 'Conocido', '1234567890', b'1'),
(8, 'Elvis', 'Olavarri Fonseca', b'1', 'Conocido', '1234567890', b'1'),
(9, 'Avengers', 'Avengers', b'1', 'Avengers', '9711235648', b'1'),
(10, 'IronMan', 'IronMan', b'1', 'IronMan', '9718564259', b'1'),
(11, 'Batman', 'Batman', b'1', 'Batman', '8795624102', b'1'),
(12, 'Capitan America', 'Capitan America', b'1', 'Capitan America', '9714568923', b'1'),
(13, 'Hulk', 'Hulk', b'1', 'Hulk', '9711452689', b'1'),
(14, 'Thor', 'Thor', b'1', 'Thor', '9714520350', b'1'),
(15, 'Lorena', 'Martinez Hernandez', b'0', 'Conocida', '9711425023', b'1'),
(16, 'Salatiel', 'Montero', b'1', 'Conocido', '9711205030', b'1'),
(17, 'Orlando', 'Gaspar Reyes', b'1', 'Conocido', '9711205036', b'1'),
(18, 'Gabriela', 'Cortes Ramirez', b'0', 'Conocido', '9711402056', b'1');*/

INSERT INTO `interes` (`id`, `porcentaje`, `status`) VALUES
(1, 0.000, b'1'),
(2, 0.050, b'1'),
(3, 0.100, b'1'),
(4, 0.150, b'1'),
(5, 0.200, b'1');



INSERT INTO `bien` (`id`, `nombre`, `tipo`, `status`) VALUES
(1, 'Celular', b'1', b'1');
/*(2, 'Motocicleta', b'1', b'1'),
(3, 'Celular', b'1', b'1'),
(4, 'Pagare', b'0', b'1'),
(5, 'Titulo de Propiedad', b'0', b'1');*/