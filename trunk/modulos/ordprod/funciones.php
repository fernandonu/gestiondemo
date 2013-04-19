<?
/*
$Author: ferni $
$Revision: 1.61 $
$Date: 2006/08/09 20:29:42 $*/

require_once("../../config.php");

//variables globales
$resultado; //para el resultado de los queries que se ejecuten
$filas_encontradas;//
$serialp;
$serialu;
$parte_serial;
$primer_ser;
$letra;
$pserial;
$userial;

//FUNCIONES DE CODIFICACION Y DECODIFICACION DE ATRIBUTOS DE LA BD

//dado el codigo de accesorio, devuelve el nombre correspondiente o
//el string "Codigo desconocido" si hay error.
function decod_accesorio($codigo) {
	switch ($codigo) {
		case(0):return "Teclado";break;
		case(1):return "Mouse";break;
		case(2):return "Parlantes";break;
		case(3):return "Microfono";break;
		default:return "Codigo desconocido";
	}
}

//dado el nombre del accesorio, devuelve el codigo correspondiente
//o -1 en caso de desconocer el modelo.
function encod_accesorio($acc) {
	switch ($acc) {
		case("Teclado"):return 0;break;
		case("Mouse"):return 1;break;
		case("Parlantes"):return 2;break;
		case("Microfono"):return 3;break;
		default: return -1;
	}
}

//dado el codigo de componente, devuelve el nombre correspondiente o
//el string "Codigo desconocido" si hay error.
function decod_componente($codigo) {
	switch ($codigo) {
		case(0):return "Tarjeta Madre";break;
		case(1):return "Microprocesador";break;
		case(2):return "Memoria";break;
		case(3):return "Disco Rigido";break;
		case(4):return "Floppy";break;
		case(5):return "Lectora CD";break;
		case(6):return "Grabadora CD";break;
		case(7):return "Lectora DVD";break;
		case(8):return "Modem";break;
		case(9):return "Placa de Red";break;
		case(10):return "Placa de Sonido";break;
		case(11):return "Placa de Video";break;
		case(12):return "Plataforma";break;
		default:return "Codigo desconocido";
	}
}

//dado el nombre del componente, devuelve el codigo correspondiente
//o -1 en caso de desconocer el modelo.
function encod_componente($compo) {
	switch ($compo) {
		case("Tarjeta Madre"):return 0;break;
		case("Microprocesador"):return 1;break;
		case("Memoria"):return 2;break;
		case("Disco Rigido"):return 3;break;
		case("Floppy"):return 4;break;
		case("Lectora CD"):return 5;break;
		case("Grabadora CD"):return 6;break;
		case("Lectora DVD"):return 7;break;
		case("Modem"):return 8;break;
		case("Placa de Red"):return 9;break;
		case("Placa de Sonido"):return 10;break;
		case("Placa de Video"):return 11;break;
		case("Plataforma"):return 12;break;
		default:return -1;
	}
}

//dado el codigo de tipo garantia de la orden de produccion,
//devuelve el nombre correspondiente o
//el string "Codigo desconocido" si hay error.
function decod_garantia($codigo) {
	switch ($codigo) {
		case(0):return "Partes";break;
		case(1):return "Laboratorio";break;
		case(2):return "Domicilio";break;
		default:return "Codigo desconocido";
	}
}


//dado el nombre del tipo de garantia de la orden de produccion,
//devuelve el codigo correspondiente
//o -1 en caso de desconocer el modelo.
function encod_garantia($acc) {
	switch ($acc) {
		case("Partes"):return 0;break;
		case("Laboratorio"):return 1;break;
		case("Domicilio"):return 2;break;
		default: return -1;
	}
}

//dado el codigo del estado de la orden de produccion,
//devuelve el nombre correspondiente o
//el string "Codigo desconocido" si hay error.
function decod_estado($codigo) {
	switch ($codigo) {
		case(0):return "Pendiente";break;
		case(1):return "En Proceso";break;
		case(2):return "Terminada";break;
		default:return "Codigo desconocido";
	}
}


//dado el nombre del Estado de la orden de produccion,
//devuelve el codigo correspondiente
//o -1 en caso de desconocer el modelo.
function encod_estado($estado) {
	switch ($estado) {
		case("Pendiente"):return 0;break;
		case("En Proceso"):return 1;break;
		case("Terminada"):return 2;break;
		default: return -1;
	}
}

function gen_serial($ens,$fecha,$modelo,$producto) {
//calculamos ensamblador
	global $db;
	if($fecha!=""){
		global $resultado,$serialp,$serialu,$parte_serial,$primer_ser,$letra,$pserial,$userial,$serial_ant;
		$sql="select * from ensamblador";
		$resultado=$db->Execute($sql) or die($db->ErrorMsg());
		while (!$resultado->EOF) {
			if ($resultado->fields['nombre']==$ens)
				$serialu=$serialp=$resultado->fields['letra'];
			$resultado->MoveNext();
		}
		list($d,$m,$a)=explode("/",$fecha);
		switch ($a) { //calculamos año
			case 2003:$serialu.='Q';$serialp.='Q';break;
			case 2004:$serialu.='R';$serialp.='R';break;
			case 2005:$serialu.='S';$serialp.='S';break;
			case 2006:$serialu.='T';$serialp.='T';break;
			case 2007:$serialu.='U';$serialp.='U';break;
			case 2008:$serialu.='V';$serialp.='V';break;
		} //fin switch
		$serialp.=$d; //calculamos dia
		$serialu.=$d;
		switch ($m) { //calculamos el mes
			case '01':$serialu.='CE';$serialp.='CE';break;
			case '02':$serialu.='CF';$serialp.='CF';break;
			case '03':$serialu.='CM';$serialp.='CM';break;
			case '04':$serialu.='AA';$serialp.='AA';break;
			case '05':$serialu.='AM';$serialp.='AM';break;
			case '06':$serialu.='AJ';$serialp.='AJ';break;
			case '07':$serialu.='JJ';$serialp.='JJ';break;
			case '08':$serialu.='JA';$serialp.='JA';break;
			case '09':$serialu.='JS';$serialp.='JS';break;
			case '10':$serialu.='OO';$serialp.='OO';break;
			case '11':$serialu.='ON';$serialp.='ON';break;
			case '12':$serialu.='OD';$serialp.='OD';break;
		}// fin switch
		if (($producto!="Computadoras CDR")&&($producto!="Server")) {
			$serialu.='OM';$serialp.='OM';
		}
		else {
			switch($modelo) {
				case "ENTERPRISE":$serialu.='EN';$serialp.='EN';break;
				case "MATRIX":$serialu.='MA';$serialp.='MA';break;
				case "SERVER":$serialu.='SE';$serialp.='SE';break;
				default:$serialu.='OM';$serialp.='OM';break;
			}// fin switch
		}
		$query="Select * from serial";
		$temp = $db->Execute($query) or die($db->ErrorMsg());
		/* While ($temp['lock']==1) //espero que lock se desbloqueada
		{$query="select * from serial";
		ejecutar_query($query);
		$temp=$resultado[0]; //uso variable temporaria
		}
		$query="update serial set lock='1' where nro=".$temp['nro'].";";
		ejecutar_query($query);
		*/
		$primer=0;
		//$serial_ant=$_POST['serial_ant'];
		if ($_POST['modi']==1) {
			$primer=$serial_ant;
			if (substr($serial_ant,0,1)==0)
				$primer=substr($serial_ant,1);
			if (substr($serial_ant,1,2)==0)
				$primer=substr($serial_ant,2);
		}
		else
			$primer=($temp->fields['nro']+1)%1000; //obtengo la primer maquina
		$pserial=$primer-1;

		if (($primer==000) && ($_POST['modi']!=1))
			$serialp.=chr(ord($temp->fields['letra'])+1);
		else
			$serialp.=trim($temp->fields['letra']);
		$parte_serial=$serialp; //obtengo primer parte de serial
		$primer_ser=$primer;    //obtengo el primer numero de serial
		$ultimo=($primer+$_POST['cant']-1)%1000; //obtengo la primer maquina
		if ($_POST['cant']+$temp->fields['nro']>1000) //actualizamos la letra
			$temp->fields['letra']=chr(ord($temp->fields['letra'])+1);
		$userial=$ultimo;
		$serialu.=trim($temp->fields['letra']);
		$letra=trim($temp->fields['letra']);//$temp->fields['letra'];
		if ($primer<100) //concateno valor con los 0 que pueden llegar a faltar
			$serialp.='0';
		if ($primer<10)
			$serialp.='0';
		if ($ultimo<100) //concateno valor con los 0 que pueden llegar a faltar
			$serialu.='0';
		if ($ultimo<10)
			$serialu.='0';
		$serialp.=$primer;
		$serialu.=$ultimo;
	}//if fecha!=""
}//fin funcion gen_serial

//funcion temporal para que personal de entrada de datos genere nros seriales a partir de
//el primer serial que ingresen ellos modificacion de gen_serial

function gen_serial2($ens,$fecha,$modelo, $ing_prim, $letra,$verificacion,$producto){
global $db;
//calculamos ensamblador
global $resultado,$serialp,$serialu,$parte_serial,$primer_ser,$letra,$pserial,$userial;
if ($verificacion=="on")
{$s='';
 $s='ND';
 $s.=$_POST['nrord'];
 $s.='S';
 $serialp=$s;
 $serialp.=1;
 $serialu=$s;
 $serialu.=$_POST['cant'];
 $primer_ser=1;
 $parte_serial=$s;
}
else
{$sql="select * from ensamblador";
 $resultado=$db->Execute($sql) or die($db->ErrorMsg());
 while (!$resultado->EOF)
 {if ($resultado->fields['nombre']==$ens)
   $serialu=$serialp=$resultado->fields['letra'];
  $resultado->MoveNext();
 }
 list($d,$m,$a)=explode("/",$fecha);
  switch ($a) //calculamos año
  {case 2003:$serialu.='Q';$serialp.='Q';break;
   case 2004:$serialu.='R';$serialp.='R';break;
   case 2005:$serialu.='S';$serialp.='S';break;
   case 2006:$serialu.='T';$serialp.='T';break;
   case 2007:$serialu.='U';$serialp.='U';break;
   case 2008:$serialu.='V';$serialp.='V';break;
  } //fin switch
  $serialp.=$d; //calculamos dia
  $serialu.=$d;
  switch ($m)  //calculamos el mes
  {case '01':$serialu.='CE';$serialp.='CE';break;
   case '02':$serialu.='CF';$serialp.='CF';break;
   case '03':$serialu.='CM';$serialp.='CM';break;
   case '04':$serialu.='AA';$serialp.='AA';break;
   case '05':$serialu.='AM';$serialp.='AM';break;
   case '06':$serialu.='AJ';$serialp.='AJ';break;
   case '07':$serialu.='JJ';$serialp.='JJ';break;
   case '08':$serialu.='JA';$serialp.='JA';break;
   case '09':$serialu.='JS';$serialp.='JS';break;
   case '10':$serialu.='OO';$serialp.='OO';break;
   case '11':$serialu.='ON';$serialp.='ON';break;
   case '12':$serialu.='OD';$serialp.='OD';break;
  }// fin switch
if (($producto!="Computadoras CDR")&&($producto!="Server"))
{$serialu.='OM';$serialp.='OM';}
else
{switch($modelo)
  {case "ENTERPRISE":$serialu.='EN';$serialp.='EN';break;
   case "MATRIX":$serialu.='MA';$serialp.='MA';break;
   case "SERVER":$serialu.='SE';$serialp.='SE';break;
   default:$serialu.='OM';$serialp.='OM';break;
  }// fin switch
}
 $primer=$ing_prim;
 $serialp.=$letra;
 $parte_serial=$serialp; //obtengo primer parte de serial
 $primer_ser=$primer;    //obtengo el primer numero de serial
 $ultimo=($primer+$_POST['cant']-1)%1000; //obtengo la primer maquina
 //if ($_POST['cant']+$temp['nro']>1000) //actualizamos la letra
 // $temp['letra']=chr(ord($temp['letra'])+1);
 $userial=$ultimo;
 $serialu.=$letra;
 //$letra=$temp['letra'];
 if ($primer<100) //concateno valor con los 0 que pueden llegar a faltar
  $serialp.='0';
 if ($primer<10)
  $serialp.='0';
 if ($ultimo<100) //concateno valor con los 0 que pueden llegar a faltar
  $serialu.='0';
 if ($ultimo<10)
  $serialu.='0';
 $serialp.=$primer;
 $serialu.=$ultimo;
}
}//fin funcion gen_serial




/***************************************************************************************
 * dias_habiles_anteriores BY GACZ
 * @return fecha (dd/mm/aa)
 * @param fecha (dd/mm/aa) fecha a la cual se restan dias
 * @param cant cantidad de dias a restar
 * @desc retorna el dia habil que esta 'cant' dias antes a la 'fecha' ingresada en $fecha
 ***************************************************************************************/
function dias_habiles_anteriores($fecha,$cant) {

$fecha_aux=$fecha;
$feriado=0;
$dia_anterior=0;
$i=$cant;
//!$dia_anterior &&
while($i>0) {
  $fecha_total=split("/",$fecha_aux);
  $dfecha=date("d/m/Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-1,$fecha_total[2]));
  $fecha_aux=date("d/m/Y/w",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-1,$fecha_total[2]));
  $fecha_test=split("/",$fecha_aux);
  if($fecha_test[3]!=0 && !feriado($dfecha)) {
      $i--;
  }
}
$fecha_retornar=split("/",$fecha_aux);
$a=date("d/m/Y",mktime(0,0,0,$fecha_retornar[1],$fecha_retornar[0],$fecha_retornar[2]));
return $a;
}

/***************************************************************************************
 * Funcion para generar reporte de licitacion
 Esta funcion arma tres tablas:
 Una de renglones y precio final vendido
 Otra con los gastos comunes
 Otra con los gastos extraordinarios
 Finalmente reporta el monto ganado, el monto estimado, y totales de gastos
 ***************************************************************************************/
function detalle_general_licitacion($id_entrega_estimada){
$color1="#5090C0";
$color2="#D5D5D5";
$ret = "";
$sql = "select id_licitacion,id_entidad,total_ofertado,monto_ganado,monto_estimado,fecha_apertura,
entidad.nombre,valor_dolar_lic,moneda.simbolo,entrega_estimada.nro
from licitacion join entrega_estimada using(id_licitacion)
join entidad using(id_entidad) join moneda using(id_moneda)
where entrega_estimada.id_entrega_estimada = ".$id_entrega_estimada;
$result = sql($sql,"Error recolectando datos: $sql");

//recupero los rma para descontar del monto total
$id_lic_rma=$result->fields["id_licitacion"];
$sql_rma="select cantidad, descripcion, (cantidad*precio_stock) as monto, precio_stock
						from stock.info_rma
						join stock.en_stock using (id_en_stock)
						left join general.producto_especifico using(id_prod_esp)
						where (info_rma.id_licitacion=$id_lic_rma)";
$result_rma=sql($sql_rma,"no se puede traer el rma")or fin_pagina();

$sql_rma="select sum(cantidad*precio_stock)as monto 
						from stock.info_rma
						join stock.en_stock using (id_en_stock)
						left join general.producto_especifico using(id_prod_esp)
						where (info_rma.id_licitacion=$id_lic_rma)";
$result_sql_rma=sql($sql_rma,"no se puede traer el rma")or fin_pagina();
$monto_rma=$result_sql_rma->fields['monto'];
if ($monto_rma=='')$monto_rma=0;
//fin de recupero los rma para descontar del monto total

$nro_licitacion = $result->fields["id_licitacion"];
$seguimiento = $result->fields["nro"];
$id_entidad = $result->fields["id_entidad"];
$total_ofertado = $result->fields["monto_ofertado"];
$monto_ganado = $result->fields["monto_ganado"];
$monto_estimado = $result->fields["monto_estimado"];
$fecha_apertura = $result->fields["fecha_apertura"];
$entidad = $result->fields["nombre"];
$valor_dolar_lic = $result->fields["valor_dolar_lic"];
$moneda_lic = $result->fields["simbolo"];

//recupero los tipo de datos conexo para descontar del monto total de gastos
$sql_conexo="select sum (producto.precio_licitacion * producto.cantidad * renglon.cantidad)as monto
				from licitaciones.subido_lic_oc 
				left join licitaciones.renglones_oc using(id_subir) 
				left join licitaciones.renglon using(id_renglon)
				left join licitaciones.producto using (id_renglon)
				where subido_lic_oc.id_entrega_estimada = $id_entrega_estimada and producto.tipo='conexos'";
$result_conexo=sql($sql_conexo,"no se puede traer el conexo")or fin_pagina();
$monto_conexo_sin_ganancia=$result_conexo->fields['monto'];

/*====================================================
		Parte de calculo de mostrar licitacion
======================================================*/

$ret .= "<table width='65%'  bgcolor='$color1' align='center' style='border: 2px solid #000000; font-size=12px;'>\n";
$ret .= "<tr bgcolor='$color1'>\n";
$ret .= "<td align='center'>\n";
$ret .= "<b>Licitación: $nro_licitacion seguimiento: $seguimiento</b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "<tr bgcolor='$color1'>\n";
$ret .= "<td align='center'>\n";
$ret .= "<b>Cliente: $entidad</b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "<tr bgcolor='$color1'>\n";
$ret .= "<td align='center'>\n";
$ret .= "<b>Fecha de apertura: ".Fecha($fecha_apertura)."</b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "</table><br>\n";

//===================
//	renglones		|
//===================

$sql="select renglon.titulo,renglon.codigo_renglon,renglon.ganancia,
renglones_oc.cantidad,renglones_oc.precio,subido_lic_oc.id_subir
from subido_lic_oc join renglones_oc using(id_subir) join renglon using(id_renglon)
where subido_lic_oc.id_entrega_estimada = $id_entrega_estimada order by codigo_renglon ";
$resultado_renglon = sql($sql,"error en consulta de renglon: ".$sql);


$cantidad_renglones = $resultado_renglon->RecordCount();
$i =0;
//este if esta para que me pongo un cartel
if ($cantidad_renglones>=1) {
$ret .= "<table width=95% align=center style='font-size=10px'>\n";
$ret .= "<tr>\n";
 $ret .= "<td align=center>\n";
  $ret .= "<b> RESUMEN DE LOS RENGLONES\n";
 $ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "</table>\n";

$ret .= "<table bgcolor='$color1' width='95%' align='center' style='font-size=10px'>\n";
  $ret .= "<tr bgcolor='$color1'>\n" ;
    $ret .= "<td width='20%' align='left'>\n" ;
    $ret .= "<b>Renglón";
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>Título";
    $ret .= "</b></td>\n" ;
    $ret .= "<td width='10%'>" ;
    $ret .= "<b>Cantidad";
    $ret .= "</b></td>\n" ;
    $ret .= "<td width='15%'>" ;
    $ret .= "<b>Monto";
    $ret .= "</b></td>\n" ;
  $ret .= "</tr>" ;

}
$suma_total_renglones = 0;
$suma_total_renglones_sin_ganancia = 0;
while ($i<$cantidad_renglones) {
 $id_subir = $resultado_renglon->fields['id_subir'];
 $cantidad=$resultado_renglon->fields['cantidad'];
 $precio=$resultado_renglon->fields['precio'];
 $ganancia=$resultado_renglon->fields['ganancia'];
 $total_producto= $precio*$cantidad;
 $total_producto_sin_ganancia = $precio*$ganancia*$cantidad;

  $ret .= "<tr bgcolor='$color2'>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$resultado_renglon->fields['codigo_renglon'];
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$resultado_renglon->fields['titulo'];
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$cantidad;
    $ret .= "</b></td>\n" ;
    $ret .= "<td align='right'>" ;
    $ret .= "<b>$moneda_lic ".number_format($total_producto,'2','.','');
    $ret .= "</b></td>" ;
    $ret .= "</tr>\n" ;

 $resultado_renglon->MoveNext();
 $i++;
 $suma_total_renglones +=$total_producto;
 $suma_total_renglones_sin_ganancia += $total_producto_sin_ganancia;
}//del while

$sql_tot_reng_sin_gan="select sum (producto.precio_licitacion * producto.cantidad * renglon.cantidad)as monto
				from licitaciones.subido_lic_oc 
				left join licitaciones.renglones_oc using(id_subir) 
				left join licitaciones.renglon using(id_renglon)
				left join licitaciones.producto using (id_renglon)
				where subido_lic_oc.id_entrega_estimada = $id_entrega_estimada";
$tot_reng_sin_gan=sql($sql_tot_reng_sin_gan,"no se puede traer el conexo")or fin_pagina();
$suma_total_renglones_sin_ganancia=$tot_reng_sin_gan->fields['monto'];

//multiplica por el valor del dolar en la variable y le resta el monto de los conexos
$suma_total_renglones_sin_ganancia=$suma_total_renglones_sin_ganancia-$monto_conexo_sin_ganancia;
$suma_total_renglones_sin_ganancia=$suma_total_renglones_sin_ganancia*$valor_dolar_lic;

if ($cantidad_renglones>=1) {
 $ret .= "</table>\n";
$ret .= "<table width=95% align=center style='font-size=10px'>\n";
$ret .= "<tr>\n";
 $ret .= "<td>\n";
  $ret .= "<b> TOTAL RENGLONES: ";
 $ret .= "</b></td>\n";
 $ret .= "<td align=right><b>\n";
  $ret .="$moneda_lic:".number_format($suma_total_renglones,2,'.','');
 $ret .= "</b></td>\n";
$ret .= "</tr>\n";
$ret .= "</table><br>\n";
}

//=======================
//	Pedido de Material	|
//=======================

if ($nro_licitacion) {
$sql="select id_movimiento_material, descripcion, cantidad, (cantidad*precio) as monto
		from mov_material.movimiento_material
		left join mov_material.detalle_movimiento
		using (id_movimiento_material)
		where  id_licitacion = $nro_licitacion and estado <> 3";

$resultado_PM = sql($sql,"error en consulta de Pedido de Material: ".$sql);

$sql="select sum (cantidad*precio) as precio_total
		from mov_material.movimiento_material
		left join mov_material.detalle_movimiento
		using (id_movimiento_material)
		where  id_licitacion = $nro_licitacion and estado <> 3";

$resultado_monto_total_PM = sql($sql,"error en consulta de Pedido de Material: ".$sql);


$cantidad_pm = $resultado_PM->RecordCount();
//este if esta para que me pongo un cartel
if ($cantidad_pm>=1) {
	$ret .= "<table width=95% align=center style='font-size=10px'>\n";
	$ret .= "<tr>\n";
	 $ret .= "<td align=center>\n";
	  $ret .= "<b> RESUMEN DE LOS PEDIDO DE MATERIAL\n";
	 $ret .= "</td>\n";
	$ret .= "</tr>\n";
	$ret .= "</table>\n";

	$ret .= "<table bgcolor='$color1' width='95%' align='center' style='font-size=10px'>\n";
	  $ret .= "<tr bgcolor='$color1'>\n" ;
	    $ret .= "<td width='20%' align='left'>\n" ;
	    $ret .= "<b>ID Pedido de Material";
	    $ret .= "</b></td>\n" ;
	    $ret .= "<td>" ;
	    $ret .= "<b>Descripción del Producto";
	    $ret .= "</b></td>\n" ;
	    $ret .= "<td width='10%'>" ;
	    $ret .= "<b>Cantidad";
	    $ret .= "</b></td>\n" ;
	    $ret .= "<td width='15%'>" ;
	    $ret .= "<b>Monto";
	    $ret .= "</b></td>\n" ;
	  $ret .= "</tr>" ;

	$resultado_PM->MoveFirst();
	while (!$resultado_PM->EOF) {
	  $ret .= "<tr bgcolor='$color2'>\n" ;
	    $ret .= "<td>" ;
	    $ret .= "<b>".$resultado_PM->fields['id_movimiento_material'];
	    $ret .= "</b></td>\n" ;
	    $ret .= "<td>" ;
	    $ret .= "<b>".$resultado_PM->fields['descripcion'];
	    $ret .= "</b></td>\n" ;
	    $ret .= "<td>" ;
	    $ret .= "<b>".$resultado_PM->fields['cantidad'];
	    $ret .= "</b></td>\n" ;
	    $ret .= "<td align='right'>" ;
	    $ret .= "<b>U\$S: ".number_format($resultado_PM->fields['monto'],'2','.','');
	    $ret .= "</b></td>" ;
	    $ret .= "</tr>\n" ;

	 $resultado_PM->MoveNext();
	}//del while

	$ret .= "</table>\n";
	$ret .= "<table width=95% align=center style='font-size=10px'>\n";
	$ret .= "<tr>\n";
	 $ret .= "<td>\n";
	  $ret .= "<b> TOTAL PEDIDO MATERIAL: ";
	 $ret .= "</b></td>\n";
	 $ret .= "<td align=right><b>\n";
	  $ret .="U\$S: ".number_format($resultado_monto_total_PM->fields['precio_total'],2,'.','');
	 $ret .= "</b></td>\n";
	$ret .= "</tr>\n";
	$ret .= "</table><br>\n";
	$monto_total_pm = $resultado_monto_total_PM->fields['precio_total'];

	$sql="select valor_dolar as valor from general.dolar_comparacion
		   where valor_dolar<>0 order by fecha DESC ";
    $valor_dolar = sql($sql,"error en consulta de Valor Dolar: ".$sql);
    $valor_dolar->MoveFirst();
	$monto_total_pm_en_pesos = ($monto_total_pm * ($valor_dolar->fields['valor']));
}//del if ($cantidad_pm>=1)

}//del if ($nro_licitacion)



/*====================================================
		Resumen del RMA
======================================================*/

if ($monto_rma!=0){
	$ret .= "<table width=95% align=center style='font-size=10px'>\n";
$ret .= "<tr>\n";
 $ret .= "<td align=center>\n";
  $ret .= "<b> RESUMEN DE LOS RMA\n";
 $ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "</table>\n";

$ret .= "<table bgcolor='$color1' width='95%' align='center' style='font-size=10px'>\n";
  $ret .= "<tr bgcolor='$color1'>\n" ;
    $ret .= "<td width='20%' align='left'>\n" ;
    $ret .= "<b>Cantidad";
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>Descripción";
    $ret .= "</b></td>\n" ;
    $ret .= "<td width='10%'>" ;
    $ret .= "<b>Precio Unitario";
    $ret .= "</b></td>\n" ;
    $ret .= "<td width='15%'>" ;
    $ret .= "<b>Precio Total";
    $ret .= "</b></td>\n" ;
  $ret .= "</tr>" ;

while(!$result_rma->EOF){
   $ret .= "<tr bgcolor='$color2'>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$result_rma->fields['cantidad'];
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$result_rma->fields['descripcion'];
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>U\$S ".number_format($result_rma->fields['precio_stock'],'2','.','');
    $ret .= "</b></td>\n" ;
    $ret .= "<td align='right'>" ;
    $ret .= "<b>U\$S ".number_format($result_rma->fields['monto'],'2','.','');
    $ret .= "</b></td>" ;
    $ret .= "</tr>\n" ;

 $result_rma->MoveNext();
 
}//del while


 $ret .= "</table>\n";
$ret .= "<table width=95% align=center style='font-size=10px'>\n";
$ret .= "<tr>\n";
 $ret .= "<td>\n";
  $ret .= "<b> TOTAL RMA: ";
 $ret .= "</b></td>\n";
 $ret .= "<td align=right><b>\n";
  $ret .="U\$S: -".number_format($monto_rma,2,'.','');
 $ret .= "</b></td>\n";
$ret .= "</tr>\n";
$ret .= "</table><br><br>\n";

$sql="select valor_dolar as valor from general.dolar_comparacion
		   where valor_dolar<>0 order by fecha DESC ";
    $valor_dolar = sql($sql,"error en consulta de Valor Dolar: ".$sql);
    $valor_dolar->MoveFirst();
$monto_total_rma_en_pesos = ($monto_rma * ($valor_dolar->fields['valor']));
//echo $monto_total_rma_en_pesos;
//echo $valor_dolar->fields['valor'];
}

/*====================================================
		Parte de calculo de gastos
======================================================*/
$op=2;
while ($op>0) {

if ($op == 1) $nnn = "id_subir is null";
else $nnn = "id_subir = $id_subir";

$sql="select nro_orden,fecha,razon_social,moneda.simbolo,valor_dolar from orden_de_compra join proveedor using(id_proveedor) join moneda using(id_moneda) where id_licitacion = $nro_licitacion and  $nnn  and estado <> 'n' order by fecha asc";
$resultado_oc = sql($sql,"error en consulta de gastos: ".$sql);
$cantidad_oc = $resultado_oc->RecordCount();

$i =0;

if ($cantidad_oc>=1) {
$ret .= "<table width=95% align=center style='font-size=10px'>\n";
$ret .= "<tr>\n";
 $ret .= "<td align=center>\n";
if ($op == 2)  $ret .= "<b> RESUMEN DE GASTOS\n";
else  $ret .= "<b> RESUMEN DE GASTOS EXTRAORDINARIOS\n";
 $ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "</table>\n";
  $ret .= "<table bgcolor='$color1' width='95%' align='center' style='font-size=10px'>\n";
  $ret .= "<tr bgcolor='$color1'>\n" ;
    $ret .= "<td width='10%'>\n" ;
    $ret .= "<b>OC";
    $ret .= "</b></td>\n" ;
    $ret .= "<td width='15%'>" ;
    $ret .= "<b>Fecha";
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>Proveedor";
    $ret .= "</b></td>\n" ;
  	$ret .= "<td width='15%'>" ;
    $ret .= "<b>Monto";
    $ret .= "</b></td>\n" ;
  	$ret .= "</tr>" ;
}

$suma_total_gastos = 0;
while ($i<$cantidad_oc) {
	$moneda = $resultado_oc->fields["simbolo"];
	$valor_dolar = $resultado_oc->fields["valor_dolar"];
  $ret .= "<tr bgcolor='$color2'>\n" ;
    $ret .= "<td>\n" ;
    $ret .= "<b>OC: ".$resultado_oc->fields["nro_orden"];
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>Fecha: ".Fecha($resultado_oc->fields["fecha"]);
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>Proveedor: ".$resultado_oc->fields["razon_social"];
    $ret .= "</b></td>\n" ;

    $sql="select * from fila where nro_orden =".$resultado_oc->fields["nro_orden"];
	$resultado_oc_filas = sql($sql,"error en consulta de gastos: ".$sql);
	$cantidad_oc_filas = $resultado_oc_filas->RecordCount();

	$j=0;
	$precio_oc = 0;
	while($j<$cantidad_oc_filas){
    	$precio_fila = $resultado_oc_filas->fields["precio_unitario"]*$resultado_oc_filas->fields["cantidad"];
    	$precio_oc += $precio_fila;
    	$resultado_oc_filas->MoveNext();
    	$j++;
	}

$ret .= "<td align='right'>" ;
//if($moneda == "U\$S")
    $ret .= "<b>$moneda:".number_format($precio_oc,'2','.','');
//else $ret .= $moneda;
    $ret .= "</b></td>\n" ;
  $ret .= "</tr>" ;


 //contabilizar los totales de gastos
 if ($moneda == "\$") $suma_total_oc_pesos[$op] += $precio_oc;
 else $suma_total_oc_dolares[$op] += $precio_oc;

 $resultado_oc->MoveNext();
 $i++;
}
if ($cantidad_oc>=1) {
 $ret .= "</table>\n";
$ret .= "<table width=95% align=center style='font-size=10px'>\n";
$ret .= "<tr>\n";
 $ret .= "<td>\n";

 if ($op == 2)  $ret .= "<b> TOTAL GASTOS: ";
else $ret .= "<b> TOTAL GASTOS EXTRAORDINARIOS: ";
 $ret .= "</b></td>\n";
$ret .= "<td align=right><b>\n";
if (isset($suma_total_oc_pesos[$op])) {
  $ret .="\$:".number_format($suma_total_oc_pesos[$op],2,'.','');
}

if (isset($suma_total_oc_dolares[$op])) {
  $ret .="<br>U\$S:".number_format($suma_total_oc_dolares[$op],2,'.','');
}

 $ret .= "</b></td>\n";
$ret .= "</tr>\n";
$ret .= "</table><br>\n";
}
$op--;
}

/*====================================================
		Resumen del  reporte
======================================================*/
$ret .= "<table width=60% align='center' bgcolor='$color1' style='font-size=10px' >\n";
$ret .= "<tr bgcolor='$color1'>\n";
 $ret .= "<td colspan='2' align='center'>\n";
 $ret .= "<b>RESUMEN DEL SEGUIMIENTO\n";
 $ret .= "</td>\n";
 $ret .= "<td align='center'>\n";
 $ret .= "<b>GANANCIA\n";
 $ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "<tr bgcolor='$color2'>\n";
 $ret .= "<td>\n";
 $ret .= "<b>MONTO TOTAL GANADO \n";
 $ret .= "</td>\n";
 $ret .= "<td align='right' width=25%>\n";
 $ret .= "<b><font color='blue'>$moneda_lic:".number_format($suma_total_renglones,2,'.','')."</font></b>\n";
 $ret .= "</td>\n";
 $ret .= "<td align='right' width=25%>\n";
 $ret .= "<b><font color='blue'>&nbsp;</font></b>\n";
 $ret .= "</td>\n";
 $ret .= "</tr>\n";
 $ret .= "<tr bgcolor='$color2'>\n";
 $ret .= "<td>\n";
 $ret .= "<b>MONTO TOTAL PREVISTO \n";
 $ret .= "</td>\n";
 $ret .= "<td align=right>\n";
 $ret .= "<b><font color='green'>$moneda_lic:".number_format($suma_total_renglones_sin_ganancia,2,'.','')."</font></b>\n";
 $ret .= "</td>\n";
 $ret .= "<td align=right>\n";
 $ret .= "<b><font color='black'>".number_format(($suma_total_renglones)?($suma_total_renglones_sin_ganancia/$suma_total_renglones):0,2,'.','') ."</font></b>\n";
 $ret .= "</td>\n";
 $ret .= "</tr>\n";
 $ret .= "<tr bgcolor='$color2'>\n";
 $ret .= "<td>\n";
 $ret .= "<b>MONTO TOTAL DE COSTOS \n";
 $ret .= "</td>\n";
 $ret .= "<td align=right>\n";
 $ret .= "<b><font color='red'>";
 $ret .="[\$:".number_format(($monto_total_pm_en_pesos)?($monto_total_pm_en_pesos-$monto_total_rma_en_pesos):0,2,'.','')."]";
 $ret .= "</font></b></td>\n";
 $ret .= "<td align=right>\n";

 //si la licitacion esta en pesos calculo los PM y los RMA en pesos
 //si la licitacion esta en dolares calculo los PM y los RMA como vienen (en dolares)
 if ($moneda_lic=='$') $ret .= "<b><font color='black'>".number_format(($suma_total_renglones)?(($monto_total_pm_en_pesos-$monto_total_rma_en_pesos)/$suma_total_renglones):0,2,'.',''). "</font></b>\n";
 else $ret .= "<b><font color='black'>".number_format(($suma_total_renglones)?(($monto_total_pm-$monto_rma)/$suma_total_renglones):0,2,'.',''). "</font></b>\n";

 $ret .= "</td>\n";
 $ret .= "</tr>\n";

 $ret .= "</table>\n";

	if($nro_licitacion){
		$consulta="select c.nro_factura, m.simbolo, total_monto, ec.nombre as estado
			from licitaciones.cobranzas c
				left join (
					select sum(monto_factura) as total_monto, id_factura, tmp.nro_factura
					from(
						select cant_prod*precio as monto_factura, id_factura, f.nro_factura
						from facturacion.items_factura join facturacion.facturas f using(id_factura)
						group by id_factura, items_factura.cant_prod, items_factura.precio, f.nro_factura
					)as tmp
					group by id_factura, tmp.nro_factura
				)as mnt on ((c.id_factura=mnt.id_factura)or(c.nro_factura=mnt.nro_factura))
				left join licitaciones.estado_cobranzas ec on (estado_nombre=id_estado_cobranza)
				left join licitaciones.moneda m using(id_moneda)
				join facturacion.facturas on(mnt.id_factura=facturas.id_factura)
			where (facturas.estado!='a') and c.id_licitacion=".$nro_licitacion;
		$rta_consulta=sql($consulta, "C661") or fin_pagina();
		$ret .= "<br><table width=60% align='center' bgcolor='$color1' style='font-size=10px' >\n";
		$ret .= "<tr bgcolor='$color1'>\n";
			$ret .= "<td colspan='3' align='center'><b>FACTURAS</td></tr>\n";
		$ret .= "<tr bgcolor='$color2'>\n" ;
	    $ret .= "<td width='10%'>\n<b>Nro. factura</b></td>\n" ;
  	  $ret .= "<td width='15%'><b>Monto</b></td>\n" ;
    	$ret .= "<td><b>Estado del seg. de cobro</b></td>" ;
  	$ret .= "</tr>" ;
		while (!$rta_consulta->EOF){
			$ret.="<tr><td>".$rta_consulta->fields["nro_factura"]."</td><td align='right'>"
				.$rta_consulta->fields["simbolo"].formato_money($rta_consulta->fields["total_monto"])."</td><td align='center'>".$rta_consulta->fields["estado"]."</td></tr>";
			$rta_consulta->moveNext();
		}
		$ret.="</table>";
	}

return $ret;
}
/***************************************************************************************
 * Funcion para guardar los datos del contacto en seguimiento de produccion
 ***************************************************************************************/
function guardar_contactos_segumientos($id_ent,$estado,$nom,$tele,$mail,$otros,$nro_cuit,$razon_social_para_factura,$domicilio_para_factura,$fact_orig,$rem_orig,$libre_deuda,$ultimo_sus,$ing_brutos,$lugar_pres_fact){
 global $_ses_user,$db;
 $fecha=date("Y-m-d H:i:s");
 $db->StartTrans();
 $sql = "select id_contacto_seguimiento from contacto_seguimiento where id_entrega_estimada =$id_ent and estado=$estado";
 $result = sql($sql,"Error recolectando datos: $sql");
 if($result->Recordcount()>0)
 {
  $sql_upd="update contacto_seguimiento set nombre='$nom',telefono='$tele',mail='$mail',otros='$otros', nro_cuit='$nro_cuit', razon_social_para_factura='$razon_social_para_factura', 
  										domicilio_para_factura='$domicilio_para_factura', fact_orig='$fact_orig', rem_orig='$rem_orig', libre_deuda='$libre_deuda', ultimo_sus='$ultimo_sus', ing_brutos='$ing_brutos',lugar_pres_fact='$lugar_pres_fact'
  			where id_entrega_estimada =$id_ent and estado=$estado";
  sql($sql_upd,"No se pudo actualizar contacto_seguimiento") or fin_pagina();
 }
 else
 {
 $sql_cont="Insert into contacto_seguimiento 
 			       (id_entrega_estimada,nombre,telefono,mail,estado,otros,nro_cuit,razon_social_para_factura,domicilio_para_factura,fact_orig,rem_orig,libre_deuda,ultimo_sus,ing_brutos,lugar_pres_fact,fecha_crea) 
 			values ($id_ent,'$nom','$tele','$mail','$estado','$otros','$nro_cuit','$razon_social_para_factura','$domicilio_para_factura','$fact_orig','$rem_orig','$libre_deuda','$ultimo_sus','$ing_brutos','$lugar_pres_fact','$fecha')";
 sql($sql_cont,"No se pudo guardar el contacto")or fin_pagina();
 }
 $db->CompleteTrans();
}

/***************************************************************************************
 * Funcion para mostrar los datos del contacto en seguimiento de produccion
   @disabled_guardar si este parametro tiene la palabra disabled, muestra
                     el boton de guardar como disabled
 ***************************************************************************************/
function mostrar_contactos_segumientos($id_ent,$luga,$mostrar,$disabled_guardar="",$fecha1){
 global $_ses_user,$db;
 ?>
 <script>
 var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
 var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido
 function control_entrega()
{
 if(document.all.nom_entrega.value=="")
 {
  alert('Debe llenar el campo nombre del Contacto Entrega Informática');	
  return false;
 }
 if(document.all.tele_entrega.value=="")
 {
  alert('Debe llenar el campo telefono del Contacto Entrega Informática');	
  return false;
 }
 if(document.all.fecha.value=="")
 {
  alert('Debe llenar el campo fecha del Contacto Entrega Informática');	
  return false;
 }
 return true;
}

 </script>
 <?
 $db->StartTrans();
 $sql = "select nombre,telefono,mail from contacto_seguimiento where id_entrega_estimada =$id_ent and estado=1";
 $result = sql($sql,"Error recolectando datos: $sql");
 $sql1 = "select nombre,telefono,mail from contacto_seguimiento where id_entrega_estimada =$id_ent";
 $result1 = sql($sql1,"Error recolectando datos: $sql");
 if($result1->Recordcount()==2)
 {
  $tab="";
 }
else
 {	
	$tab="<table border=1 width='100%' cellpadding=0>";
	$tab.="<tr><td align='center'><b><font color=Red size='5'>NO HAY REGISTRO DE LA COMUNICACIÓN CON EL CLIENTE</font></b></td></tr>";
	$tab.="</table>";
 }
    $nomb=$result->fields['nombre'];
    $tele=$result->fields['telefono'];
    $mail=$result->fields['mail'];
    $fecha1=Fecha($fecha1);
	$tab.="<table border=1 width='100%' cellpadding=0>";
	$tab.="<tr align='center' id='mo'>";
	$tab.="<td align='center' width='3%'>";
	$tab.="<img id='imagen_8' src='$img_cont' border=0 title='Mostrar Contacto Entrega' align='left' style='cursor:hand;' onclick='muestra_tabla(document.all.informatica,8);' >";
	$tab.="</td><td align='center'><b>Contacto Entrega Informática</b></td></tr></table>";
	$tab.="<table id='informatica' border='1' width='100%' style='display:none;border:thin groove' border=1 bordercolor=black cellpadding=0 cellspacing=1 rules='none'>";
	$tab.="<tr><td>";
	$tab.="<table align=center>";
	$tab.="<tr><td><b>Nombre&nbsp;&nbsp;<font color=Red>(*)</font></b><input type=text name=nom_entrega value='$nomb' size='80'></td>";
	$tab.="<td><b><font color=Red>(*)</font>Fecha Entrega Pactada</b></td></tr>";
	$tab.="<tr><td><b>Telefono<font color=Red>(*)</font></b><input type=text name=tele_entrega value='$tele' size='80'></td>";
	$tab.="<td><b><input type=text name=fecha value=$fecha1>&nbsp;";
	$tab.=link_calendario('fecha');
	$tab.="</td></tr>";
	$tab.="<tr><td colspan=2><b>E-Mail&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><input type=text name=mail_entrega value='$mail' size='80'></td></tr>";
	$tab.="<tr><td colspan=2>";
	$tab.="<br>";
	$tab.="<table width='100%' border='1'>";
	$tab.="<tr><td><b>Recordatorio: Al hablar con el cliente confirmar lugar de entrega</b></td></tr>";
	$tab.="<tr><td><textarea name='com' cols='100' rows='".row_count($luga,100)."' readonly>$luga </textarea></td></tr>";
	$tab.="</table></td></tr>";
	$tab.="<tr><td><font color='Red'><b>* Campos obligatorios</b></font></td></tr>";
	$tab.="<br></table>";
	$tab.="</td></tr>";
	$tab.="<tr><td align='center'><input type='submit' name='gua_entrega' value='Guardar' $disabled_guardar onclick='return (control_entrega());'></td></tr>";
	$tab.="</table>";

	$db->CompleteTrans();

	return $tab;
}

/***************************************************************************************
 * Funcion para mostrar los datos del contacto en seguimiento de produccion
    @disabled_guardar si este parametro tiene la palabra disabled, muestra
                     el boton de guardar como disabled
 ***************************************************************************************/
function mostrar_contactos_segumientos1($id_ent,$disabled_guardar=""){

 global $_ses_user,$db;
 ?>
 <script>
 var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
 var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido
 </script>
 <?
 $db->StartTrans();
 $sql = "select * from licitaciones_datos_adicionales.contacto_seguimiento where id_entrega_estimada =$id_ent and estado=2";
 $result = sql($sql,"Error recolectando datos: $sql");
 if($result->Recordcount()>0){
  $nomb=$result->fields['nombre'];
  $tele=$result->fields['telefono'];
  $mail=$result->fields['mail'];
  $otros=$result->fields['otros'];
  $nro_cuit=$result->fields['nro_cuit'];
  $razon_social_para_factura=$result->fields['razon_social_para_factura'];
  $domicilio_para_factura=$result->fields['domicilio_para_factura'];
  $fact_orig=$result->fields['fact_orig'];
  $rem_orig=$result->fields['rem_orig'];
  $libre_deuda=$result->fields['libre_deuda'];
  $ultimo_sus=$result->fields['ultimo_sus'];
  $ing_brutos=$result->fields['ing_brutos'];
  $lugar_pres_fact=$result->fields['lugar_pres_fact'];
 }
 $tab="<table border=1 width='100%' cellpadding=0>";
 $tab.="<tr align='center' id='mo'>";
 $tab.="<td align='center' width='3%'>";
 $tab.="<img id='imagen_9' src='$img_cont' border=0 title='Contacto Precentación' align='left' style='cursor:hand;' onclick='muestra_tabla(document.all.presentacion,9);' >";
 $tab.="</td><td align='center'><b>Contacto Presentación de la Factura</b></td></tr></table>";
 $tab.="<table id='presentacion' border='1' width='100%' style='display:none;border:thin groove' border=1 bordercolor=black cellpadding=0 cellspacing=1 rules='none'>";
 $tab.="<tr>";
 $tab.="<td width='42%'>";
 $tab.="<table align=left>";
 $tab.="<tr><td>";
 $tab.="<tr><td><b>Nombre&nbsp;&nbsp;<font color=Red>(*)</font></b><input type=text name=nom_par value='$nomb' size='46'></td></tr>";
 $tab.="<tr><td><b>Telefono<font color=Red>(*)</font></b><input type=text name=tele_par value='$tele' size='46'></td></tr>";
 $tab.="<tr><td><b>E-Mail&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><input type=text name=mail_par value='$mail' size='46'></td></tr>";
 $tab.="<tr align='center'><td align='center'><b>Lugar para la Presentación de la Factura</b></td></tr>";
 $tab.="<tr><td><textarea name=lugar_pres_fact cols='58' rows='4'>$lugar_pres_fact</textarea></td></tr>";
 $tab.="</table>";
 $tab.="</td>";
 $tab.="<td width='58%'>";
 $tab.="<table align=left>";
 $tab.="<tr><td>";
 $tab.="<tr align='center'><td align='center'><font color='Red'><b>Requisitos para la Presentación de la Factura</b></font></td></tr>";
 if ($libre_deuda==1) $chek_libre_deuda='checked';
 else $chek_libre_deuda='';
 if ($ultimo_sus==1) $chek_ultimo_sus='checked';
 else $chek_ultimo_sus='';
 if ($ing_brutos==1) $chek_ing_brutos='checked';
 else $chek_ing_brutos='';
 $tab.="<tr><td><b>Nro de Cuit &nbsp;<input type=text name=nro_cuit value='$nro_cuit' size='20'>&nbsp;&nbsp;
 				   Libre Deuda<input type='checkbox' name='libre_deuda' $chek_libre_deuda>&nbsp;&nbsp;
                   Ult. S.U.S.S.<input type='checkbox' name='ultimo_sus' $chek_ultimo_sus>&nbsp;&nbsp;
                   Ing. Brutos<input type='checkbox' name='ing_brutos' $chek_ing_brutos></b>
 		</td></tr>";
 $tab.="<tr><td><b>Razón Social p/ Factura </b><input type=text name=razon_social_para_factura value='$razon_social_para_factura' size='60'></td></tr>";
 $tab.="<tr><td><b>Dirección p/ Factura &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><input type=text name=domicilio_para_factura value='$domicilio_para_factura' size='60'></td></tr>";
 $tab.="<tr><td><b>Factura Original y <input type=text name=fact_orig value='$fact_orig' size='5'> Copias &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 				   Remito Original y <input type=text name=rem_orig value='$rem_orig' size='5'> Copias</b></td></tr>";
 $tab.="<tr><td><b>Otros &nbsp;</b><textarea name=otros cols='79' rows='2'>$otros</textarea></td></tr>";
 $tab.="</td></tr>";
 $tab.="</table>";
 $tab.="</td>";
 $tab.="</tr>";
 $tab.="</td></tr>";
 $tab.="<tr><td colspan='2'><font color='Red'><b>* Campos obligatorios</b></font></td></tr>";
 $tab.="<tr><td align='center' colspan='2'><br><input type='submit' name='gua_presentacion' value='Guardar' $disabled_guardar onclick='return (control_entrega1());'></td></tr>";
 $tab.="</table>";
 return $tab;
 $db->CompleteTrans();
}

/***************************************************************************************
 * Funcion para enviar mail cuando se termina un seguimiento de produccion
 ***************************************************************************************/
function enviar_mail_lic_entregada($id){

$sql = "select id_licitacion,entrega_estimada.nro from licitacion join entrega_estimada using(id_licitacion) where entrega_estimada.id_entrega_estimada = ".$id;
$result = sql($sql,"Error recolectando datos: $sql");
$id_licitacion = $result->fields["id_licitacion"];
$nro_seguimiento = $result->fields["nro"];

$para = "juanmanuel@coradir.com.ar,noelia@coradir.com.ar,ferni@coradir.com.ar";

$asunto = "Licitación $id_licitacion:$nro_seguimiento entregada...";

$contenido = "<html><body>";
$contenido .= detalle_general_licitacion($id)."<br><br>".firma_coradir_mail()."</body></html>";

enviar_mail_html($para,$asunto,$contenido,'','',0);
}
class Word_Envio {
	var $columna=1;		//Word_Envio::columna int  	Columna actual del documento.
	var $fila=1;		//Word_Envio::fila int  	fila actual del documento.
	var $buffer="";		//Word_Envio::buffer str  	Contenido del documento.

	//function __constructor() {
	//	$this->Encabesado();
	//}
	function Encabesado() {
		$this->buffer="MIME-Version: 1.0
Content-Type: multipart/related; boundary=\"----=_NextPart_01C4D850.CC47B010\"

Este documento es una página Web de un solo archivo, también conocido como archivo de almacenamiento Web. Si está viendo este mensaje, su explorador o editor no admite archivos de almacenamiento Web. Descargue un explorador que admita este tipo de archivos, como Microsoft Internet Explorer.

------=_NextPart_01C4D850.CC47B010
Content-Location: file:///C:/2EEB2DE1/word2003-mejorULTIMA.htm
Content-Transfer-Encoding: quoted-printable
Content-Type: text/html; charset=\"us-ascii\"


<html xmlns:o=3D\"urn:schemas-microsoft-com:office:office\"
xmlns:w=3D\"urn:schemas-microsoft-com:office:word\"
xmlns=3D\"http://www.w3.org/TR/REC-html40\">

<head>
<meta http-equiv=3DContent-Type content=3D'text/html; charset=3Des-ascii'>
<meta name=3DProgId content=3DWord.Document>
<meta name=3DGenerator content=3D'Microsoft Word 11'>
<meta name=3DOriginator content=3D'Microsoft Word 11'>
<link rel=3DFile-List href=3D'template_archivos/filelist.xml'>
<title>{Cliente}</title>
<!--[if gte mso 9]><xml>
 <o:DocumentProperties>
  <o:Author>Carlitox</o:Author>
  <o:LastAuthor>Carlitox</o:LastAuthor>
  <o:Revision>2</o:Revision>
  <o:TotalTime>32</o:TotalTime>
  <o:Created>2005-05-23T19:24:00Z</o:Created>
  <o:LastSaved>2005-05-23T19:24:00Z</o:LastSaved>
  <o:Pages>1</o:Pages>
  <o:Words>32</o:Words>
  <o:Characters>181</o:Characters>
  <o:Company>Coradir S.R.L.</o:Company>
  <o:Lines>1</o:Lines>
  <o:Paragraphs>1</o:Paragraphs>
  <o:CharactersWithSpaces>212</o:CharactersWithSpaces>
  <o:Version>11.5606</o:Version>
 </o:DocumentProperties>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:SpellingState>Clean</w:SpellingState>
  <w:GrammarState>Clean</w:GrammarState>
  <w:HyphenationZone>21</w:HyphenationZone>
  <w:DoNotHyphenateCaps/>
  <w:PunctuationKerning/>
  <w:ValidateAgainstSchemas>false</w:ValidateAgainstSchemas>
  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
  <w:DoNotUnderlineInvalidXML/>
  <w:Compatibility>
   <w:BreakWrappedTables/>
   <w:SnapToGridInCell/>
   <w:WrapTextWithPunct/>
   <w:UseAsianBreakRules/>
   <w:DontGrowAutofit/>
  </w:Compatibility>
  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
 </w:WordDocument>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:LatentStyles DefLockedState=3D'false' LatentStyleCount=3D'156'>
 </w:LatentStyles>
</xml><![endif]-->
<style>
<!--
 /* Font Definitions */
 @font-face
	{font-family:'Arial Black';
	panose-1:2 11 10 4 2 1 2 2 2 4;
	mso-font-charset:0;
	mso-generic-font-family:swiss;
	mso-font-pitch:variable;
	mso-font-signature:647 0 0 0 159 0;}
 /* Style Definitions */
 p.MsoNormal, li.MsoNormal, div.MsoNormal
	{mso-style-parent:'';
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:12.0pt;
	font-family:'Times New Roman';
	mso-fareast-font-family:'Times New Roman';}
h1
	{mso-style-next:Normal;
	margin-top:12.0pt;
	margin-right:0cm;
	margin-bottom:3.0pt;
	margin-left:0cm;
	mso-pagination:widow-orphan;
	page-break-after:avoid;
	mso-outline-level:1;
	font-size:16.0pt;
	font-family:Arial;
	mso-font-kerning:16.0pt;}
span.SpellE
	{mso-style-name:'';
	mso-spl-e:yes;}
span.GramE
	{mso-style-name:'';
	mso-gram-e:yes;}
@page Section1
	{size:841.9pt 595.3pt;
	mso-page-orientation:landscape;
	margin:0.5cm 0.95cm 0.5cm 0.95cm;
	mso-header-margin:0.9cm;
	mso-footer-margin:0.9cm;
	mso-paper-source:0;}
div.Section1
	{page:Section1;}
-->
</style>
<!--[if gte mso 10]>
<style>
 /* Style Definitions */
 table.MsoNormalTable
	{mso-style-name:'Tabla normal';
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	mso-style-noshow:yes;
	mso-style-parent:'';
	mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
	mso-para-margin:0cm;
	mso-para-margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:10.0pt;
	font-family:'Times New Roman';
	mso-ansi-language:#0400;
	mso-fareast-language:#0400;
	mso-bidi-language:#0400;}
table.MsoTableGrid
	{mso-style-name:'Tabla con cuadr\00EDcula';
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	border:solid windowtext 1.0pt;
	mso-border-alt:solid windowtext .5pt;
	mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
	mso-border-insideh:.5pt solid windowtext;
	mso-border-insidev:.5pt solid windowtext;
	mso-para-margin:0cm;
	mso-para-margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:10.0pt;
	font-family:'Times New Roman';
	mso-ansi-language:#0400;
	mso-fareast-language:#0400;}
</style>
<![endif]--><!--[if gte mso 9]><xml>
 <o:shapedefaults v:ext=3D'edit' spidmax=3D'14338'/>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <o:shapelayout v:ext=3D'edit'>
  <o:idmap v:ext=3D'edit' data=3D'1'/>
 </o:shapelayout></xml><![endif]-->
</head>

<body lang=3DES style=3D'tab-interval:35.4pt'>

<div class=3DSection1>
";

	}

	function Agregar_celda($cliente,$direccion,$id_lic,$nro_renglon,$nro_lic,$bultos,$nro_bulto,$serie) {
		if (($this->fila==1) && ($this->columna==1)) {//nueva tabla
		//$buffer.="$cantidad_impresa";
			$this->buffer.="<table class=3DMsoTableGrid border=3D0 cellspacing=3D0 cellpadding=3D0
 style=3D'mso-cellspacing:0cm;margin-left:1.0pt;mso-table-layout-alt:fixed;
 mso-yfti-tbllook:480;mso-padding-alt:0cm 0cm 0cm 0cm'>";
		}
		if ($this->columna==1)
			$this->buffer.="<tr style=3D'mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes;
  page-break-inside:avoid;'>";

		$this->buffer.="<td width=3D227 valign=3Dmiddle align=3Dcenter border=3D0 style=3D'width:13.9cm;height:9.9cm;padding:0cm 0cm 0cm 0cm'>
		<table class=3DMsoTableGrid border=3D0 cellspacing=3D0 cellpadding=3D0
   style=3D'mso-cellspacing:0cm;mso-table-layout-alt:fixed;mso-yfti-tbllook=
:480;
   mso-padding-alt:0cm 5.4pt 0cm 5.4pt'>
   <tr style=3D'mso-yfti-irow:0;mso-yfti-firstrow:yes;height:42.25pt'>
    <td width=3D45 rowspan=3D3 valign=3Dmiddle border=3D0 style=3D'width:45pt;padding:0cm 0cm 0cm 0cm'><p class=3DMsoNormal align=3Dcenter style=3D'text-align:center'><span
      lang=3DES-AR style=3D'mso-ansi-language:ES-AR;mso-fareast-language:ES-AR'><!--[if gte vml 1]>
	  <v:shape
       id=3D\"_x0000_i1025\" type=3D\"#_x0000_t75\" alt=3D\"\" style=3D'width:44pt;
	   height:250pt'>
       <v:imagedata src=3D\"word2003-mejorULTIMA_archivos/image003.png\" o:title=3D\"image004\"/>
      </v:shape><![endif]--><![if !vml]><img width=3D44 height=3D250
      src=3D\"word2003-mejorULTIMA_archivos/image004.jpg\" v:shapes=3D\"_x0000_i1025\"><![endif]></span></p>
    </td>
	<td width=3D300 colspan=3D2 border=3D0 valign=3Dtop style=3D'width:300pt;padding:0cm 0cm 0cm 0cm;height:42.=
25pt'>
    <p class=3DMsoNormal><b><span
    lang=3DES-AR style=3D'font-size:16.0pt;mso-ansi-language:ES-AR'>$cliente<o:p></o:p></span></b></p>
    <p class=3DMsoNormal><span lang=3DES-AR
    style=3D'font-size:16.0pt;mso-ansi-language:ES-AR'><span class=3DSpell=
E><span

    class=3DGramE>$direccion</span></span><o:p></o:p></span></p>

    <p class=3DMsoNormal align=3Dleft style=3D'text-align:left'><b><span
    lang=3DES-AR style=3D'font-size:16.0pt;mso-ansi-language:ES-AR'>$nro_lic</span></b></p>
    <p class=3DMsoNormal align=3Dleft style=3D'text-align:left'><b><span
     style=3D'font-size:16.0pt'>Renglón: $nro_renglon</span></b></p>
    </td>
   </tr>
   <tr style=3D'mso-yfti-irow:1'>
    <td width=3D193 colspan=3D2 border=3D0 valign=3Dtop style=3D'width:145.05pt;padding:0cm 0cm 0cm 0cm'>
    <p class=3DMsoNormal align=3Dleft style=3D'text-align:left'><span lan=
g=3DES-TRAD
    style=3D'font-size:16.0pt;mso-ansi-language:ES-TRAD'>&nbsp;0810-22-CORADIR<o:p></o:p></span>
	<span lang=3DES-TRAD align=3Dright
    style=3D'text-align:right;font-size:16.0pt;mso-ansi-language:ES-TRAD'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ID: $id_lic<o:p></o:p></span></p>
    </td>
   </tr>
   <tr style=3D'mso-yfti-irow:2;mso-yfti-lastrow:yes;height:21.95pt'>
    <td width=3D80 valign=3Dmiddle border=3D0 style=3D'width:80pt;padding:0cm 0cm 0cm 0cm;height:21.=
95pt'>
    <p class=3DMsoNormal align=3Dcenter style='text-align:center'><span lang=3DES-TRAD style=3D'font-size:16.0pt;mso-=
ansi-language:
    ES-TRAD'><b>Bulto: $nro_bulto/$bultos</b><o:p></o:p></span></p>
    </td>
    <td width=3D54 border=3D0 style=3D'width:40.65pt;padding:0cm 0cm 0cm 0cm;height:21.95pt'>
    <p class=3DMsoNormal align=3Dcenter style='text-align:center'><span lang=3DES-TRAD style=3D'font-size:16.0pt;mso-=
ansi-language:
    ES-TRAD'>Envío: $serie</span>
	<span
      lang=3DES-AR style=3D'mso-ansi-language:ES-AR;mso-fareast-language:ES-AR'><!--[if gte vml 1]><v:shape
       id=3D\"_x0000_i1031\" type=3D\"#_x0000_t75\" style=3D'width:280;height:35'>
       <v:imagedata src=3D\"word2003-mejorULTIMA_archivos/$serie.png\" o:title=3D\"CDR%20Computers%20black\"/>
      </v:shape><![endif]--><![if !vml]><img width=3D280 height=3D35 style=3D'width:280;height:35'
      src=3D\"word2003-mejorULTIMA_archivos/$serie.png\" v:shapes=3D\"_x0000_i1031\"><![endif]></span></p>
    </td>
   </tr>
  </table>
  <p class=3DMsoNormal><span lang=3DES style=3D'font-size:18.0pt;mso-ansi-l=
anguage:
  ES'><o:p></o:p></span></p>
  </td>";
		if ($this->columna==2) {
			$this->buffer.="</tr>";
		}
		if(($this->fila==2) && ($this->columna==2)) { //inserto salto de pagina
			$this->buffer.="</table>
<span style=3D'font-size:12.0pt;font-family:\"Times New Roman\";mso-fareast-font-family:
\"Times New Roman\";mso-ansi-language:ES;mso-fareast-language:ES;mso-bidi-language:
AR-SA'><br clear=3Dall style=3D'mso-special-character:line-break;page-break-before:
always'>
</span>";
}
		//actualizo columna y fila
		switch ($this->columna){
			case 1:$this->columna=2;
				break;
			case 2:$this->columna=1;
				$this->fila++;
				if ($this->fila==3) $this->fila=1;
				break;
		}

	}
	function Agregar_celda_vacia() {
		if (($this->fila==1) && ($this->columna==1)) {//nueva tabla
		//$buffer.="$cantidad_impresa";
			$this->buffer.="<table class=3DMsoTableGrid border=3D0 cellspacing=3D0 cellpadding=3D0
 style=3D'mso-cellspacing:0cm;margin-left:1.0pt;mso-table-layout-alt:fixed;
 mso-yfti-tbllook:480;mso-padding-alt:0cm 0cm 0cm 0cm'>";
		}
		if ($this->columna==1)
			$this->buffer.="<tr style=3D'mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes;
  page-break-inside:avoid;'>";

		$this->buffer.="<td width=3D227 valign=3Dtop style=3D'width:13.9cm;height:9.9cm;padding:0cm 0.1cm 0cm 0.1cm'>
		<table class=3DMsoTableGrid border=3D0 cellspacing=3D0 cellpadding=3D0
   style=3D'mso-cellspacing:0cm;mso-table-layout-alt:fixed;mso-yfti-tbllook=
:480;
   mso-padding-alt:0cm 5.4pt 0cm 5.4pt'>
   <tr style=3D'mso-yfti-irow:0;mso-yfti-firstrow:yes;height:42.25pt'>
    <td width=3D193 valign=3Dtop border=3D0 style=3D'width:145.05pt;padding:0cm 0cm 0cm 0cm;height:42.=
25pt'>&nbsp;
	</td>
   </tr>
   </table>
   <p class=3DMsoNormal><span lang=3DES style=3D'font-size:18.0pt;mso-ansi-l=
anguage:
  ES'><o:p></o:p></span></p>
  </td>";
		if ($this->columna==2) {
			$this->buffer.="</tr>";
		}
		if(($this->fila==2) && ($this->columna==2)) { //inserto salto de pagina
			$this->buffer.="</table>
<span style=3D'font-size:12.0pt;font-family:\"Times New Roman\";mso-fareast-font-family:
\"Times New Roman\";mso-ansi-language:ES;mso-fareast-language:ES;mso-bidi-language:
AR-SA'><br clear=3Dall style=3D'mso-special-character:line-break;page-break-before:
always'>
</span>";
		}
		//actualizo columna y fila
		switch ($this->columna){
			case 1:$this->columna=2;
				break;
			case 2:$this->columna=1;
				$this->fila++;
				if ($this->fila==3) $this->fila=1;
				break;
		}

	}

	function Pie($serie) {
		$this->buffer.="</div>
	</body>
	</html>";

		$this->buffer.="


------=_NextPart_01C4D850.CC47B010
Content-Location: file:///C:/2EEB2DE1/word2003-mejorULTIMA_archivos/image003.png
Content-Transfer-Encoding: base64
Content-Type: image/png

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
TIw3E+PNeDNlhRCl1Ijfa/55ypkhv99vaKtlo3gzMX/OxHgzMd5MjDcT483EeDMx3kyMN+PNlHaF
QiF2PnDmKxwONzc3V1dXl5WVGX0+MGaPO40KBoN79uxpaWkx2qwZ7zT77aeffvrpp582f0uJ8TbV
b7e0tOzZsycYDKbrGhhvM9TS0rJ//34z/TbjnR6/vXv37paWFuukAjDeRvnt3bt3Gz3ZZrwt4beb
m5ste4WMd2b6bcY7u/w2451iv63HSWx35Yz3FNTe3r5nz57m5ua0L6sYb2P9dnNz8549e2zktxnv
7PLbjHd2+W3Ge1LLKt2gM8BvM94Tqbm5WY9vZ/ydZjXv9vZ2PU6SeX6b8R7rt3fv3p2FFY3ZxTt7
/HZW885Cv52NvNOYNsR4mycrpA0x3mbI/HRPxpv5bcab+W3GeyayTron422s37ZL2hDjPVO/ba+0
IcZ7+n7byumejDfz24w389uM9xi/nWFpQ4z3OMrgtKFpyOv11tTU+Hy+TOOdSemeM5ff7w8EAhs2
bPD7/Zlm38xvJxQIBNasWWOCQaeBN/PbyR571apVNTU1hja9Tg/vbEj3nIx8Pl8Cc/qvhhqjpqYm
lPXavHlzW1sbtZKM6sfV1dXFBuk1a9aYMwubvFj/tewS4814MzHeTIw3E+OdEaqpqQkEAox3Jsvr
9dbW1u7du7evr2/v3r2rV6/OyNvM9npgk7crGO+0eWw9wGnydgXjbarHTvt2BeNtuPTtijVr1mTq
FIzxBkjfBjPjzTw2422Ax7bKBjPjbdxSasOGDYFAIEuWUtnI2+v1JgZm5rEznHdNTc327dvZ/Gsm
slM8taWlpaysrLKy8oknnmC5zJnPW1d7e/sTTzxRWVlZVla2ZcsWlt2c4bwTCoVCO3fuXL9+/axZ
s9avX8/ynTOcd0J6oWF9ff2sWbMqKyt37tzJCkszmfcYb79ly5aysjLd27PWLhnOe4y3r66unjVr
Vn19PfP2Gc472ds3Nzfr3r66ujrLvX125bcEg0Hd21dWVm7ZsiULF3VZms/U3t6+c+fOyspK3dtn
T2uvbM9f07198qIus709O8/iqlpaWvTojd/vz9TgPOM9vrdn/pyJ8WZivJkYb6ZM5+3xeNjDtaD4
hoYGI37v+9///rVr1+bk5Jw4cUKSpOx8uAghj8djqYQcRCk1YV2bzW3X9CxpveAh7ct6M3jrYu1w
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

------=_NextPart_01C4D850.CC47B010
Content-Location: file:///C:/2EEB2DE1/word2003-mejorULTIMA_archivos/image004.jpg
Content-Transfer-Encoding: base64
Content-Type: image/jpg

/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAoHBwgHBgoICAgLCgoLDhgQDg0NDh0VFhEYIx8lJCIf
IiEmKzcvJik0KSEiMEExNDk7Pj4+JS5ESUM8SDc9Pjv/2wBDAQoLCw4NDhwQEBw7KCIoOzs7Ozs7
Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozv/wAARCAElADUDASIA
AhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQA
AAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3
ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWm
p6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEA
AwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSEx
BhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElK
U1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3
uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD2WuT8
deP9P8EWkfmxG6vZwTDbq2MgfxMew/nXWV83fGS6e5+It4jElbeKKNR6DaG/mxoA3ofj9qwuQZ9F
s2gzyqOwbH1OR+ler+HvF+keI/D51q2uFit4wfPExCmAgZIb/GvnUfD7xKfDH/CRiw/0Hy/NzvG/
Z/f29cd/pzWNBqt7baZdabDOy2t26PNGOjFM4/n/ACoA9h8WfHKCAyWnhi3E7jI+2TjCD3Vep+px
9K1/gzrWpa9o+q3uqXkt1ObwDdIfujYOAOgHsK8I0rR9R1y8Wz0uzlu52/hjXOPcnoB7mvon4X+D
r/wdoE9vqUkTT3M3mlIjkR/KBgnueO1AHa0UUUAFfM3xa/5KTqn/AGz/APRa19M18zfFr/kpOqf9
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

------=_NextPart_01C4D850.CC47B010
Content-Location: file:///C:/2EEB2DE1/word2003-mejorULTIMA_archivos/$serie.png
Content-Transfer-Encoding: base64
Content-Type: image/png

".chunk_split(base64_encode(generar_codigo_barra(str_replace("-","",$serie),'png',280,35,1,'','off','off','','off')));

		$this->buffer.="

------=_NextPart_01C4D850.CC47B010
Content-Location: file:///C:/2EEB2DE1/word2003-mejorULTIMA_archivos/filelist.xml
Content-Transfer-Encoding: quoted-printable
Content-Type: text/xml; charset=\"utf-8\"

<xml xmlns:o=3D\"urn:schemas-microsoft-com:office:office\">
 <o:MainFile HRef=3D\"../word2003-mejorULTIMA.htm\"/>
 <o:File HRef=3D\"image003.png\"/>
 <o:File HRef=3D\"image004.jpg\"/>
 <o:File HRef=3D\"filelist.xml\"/>
";
		$this->buffer.="<o:File HRef=3D\"$serie.png\"/>\n";
		$this->buffer.="
</xml>
------=_NextPart_01C4D850.CC47B010--

";

	}
	function enviar($nombre_archivo) {
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate");
		header("Content-Transfer-Encoding: binary");
		Header('Content-Type: application/dummy');
		Header('Content-Length: '.strlen($this->buffer));
		Header('Content-disposition: attachment; filename='.$nombre_archivo);
		echo $this->buffer;
	}
}

?>