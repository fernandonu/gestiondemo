<?
/*
Author: Broggi
Fecha: 28/07/2004

MODIFICADA POR
$Author: ferni $
$Revision: 1.19 $
$Date: 2005/11/29 15:02:52 $
*/

require_once("../../config.php");


//print_r($_POST);
$control_and=0;

$orde_por = "";

/*if ($_POST['filter']=="fila.descripcion_prod")
   {echo "paso por aca";
   	$busco=$_POST['keyword'];    
   	$where.="fila.desc_adic ILIKE '%$busco%'";
   	$control_and=1;
   }*/


//*********************Nro. Orden********************
if ($filtrar_nro_orden)
{if ($control_and==0) $and="";
 else $and="and";
 if ($nro_orden) $where.="  $and nro_orden=".$nro_orden;
 else $where.="  $and nro_orden is null";
 $control_and=1; 
}
//***************************************************

//******************Estados**************************
if ($filtrar_estado)
{if ($control_and==0) $and="";
 else $and="and";
 if($estado=='e' || $estado=='g')
  $where.=" $and (estado='".$estado."' or estado='d')";
 else
  $where.=" $and estado='".$estado."'";
 
 
 $control_and=1; 	
 
}
//***************************************************

//****************Tipo de Orden**********************
if ($filtrar_tipo_orden)
{if ($control_and==0) $and="";
 else $and="and";
 switch ($tipo_orden)
  {case "l" : $where.=" $and presupuesto=0";
              $control_and=1;
   break;
   case "p" : $where.=" $and presupuesto=1";
              $control_and=1;
   break;
   case "s" : $where.=" $and (nrocaso is not null and trim(nrocaso)!='')";
              $control_and=1;
   break;
   case "h" : $where.=" $and flag_honorario=1";
              $control_and=1;
   break;
   case "sc": $where.=" $and flag_stock=1";
              $control_and=1;
   break;
   case "r" : $where.=" $and orden_prod is not null";
              $control_and=1;
   break;
   case "o" : $where.=" $and (id_licitacion is null and (nrocaso is null or nrocaso='') 
                        and flag_honorario is null and flag_stock=0 and orden_prod is null)";                  
              $control_and=1;
   break;
   case "i" : $where.=" $and internacional=1";
              $control_and=1;
   
  }	 
}
//***************************************************

//*****************Monto Orden*********************
if ($filtrar_por_monto)
{if ($control_and==0) $and="";
 else $and="and";
 if ($monto_1==$monto_2)
    $where.=" $and suma_orden=$monto_1";
 else $where.=" $and (suma_orden>=$monto_1 
                 and suma_orden<=$monto_2)";    
 $control_and=1;
} 
//***************************************************

//*****************Forma Pago*********************
if ($filtrar_por_forma_pago)
{//die("en busqueda consulta");
 if ($control_and==0) $and="";
 else $and="and";
 if($forma_pago!=-1)
  $where.=" $and (plantilla_descripcion ilike '%$forma_pago%' or tipo_descripcion ilike '%$forma_pago%')";
 elseif($forma_pago_texto)
  $where.=" $and (plantilla_descripcion ilike '%$forma_pago_texto%' or tipo_descripcion ilike '%$forma_pago_texto%')";    
 if ($forma_pago_dias) $where.=" and dias_pagos=$forma_pago_dias";
 $control_and=1; 
} 
//***************************************************

//***********Tipo de producto************************
if ($filtrar_productos)
{if ($control_and==0) $and="";
 else $and="and"; 
 $where.=" $and tipo='".$productos."'";
 $control_and=1;
}

//***************************************************

//***********Entregado/Recibido************************
//Todos Entregados=1
//Tddos Recibidos=2
//Todos Ent. y todos Rec.=3
//Falta Ent. y falta Rec.=4
//Falta Entregar=5
//Falta Recibir=6
if ($filtrar_re_en)
{if ($control_and==0) $and="";
 else $and="and";
 if ($entregado_recibido==1) $where.=" $and falta_entregar=-1";
 if ($entregado_recibido==2) $where.=" $and falta_recibir=-1";
 if ($entregado_recibido==3) $where.=" $and (falta_entregar=-1 and falta_recibir=-1)";
 if ($entregado_recibido==4) $where.=" $and ((falta_entregar<>-1 or falta_entregar is NULL) and (falta_recibir<>-1 or falta_recibir is NULL))";
 if ($entregado_recibido==5) $where.=" $and (falta_entregar<>-1 or falta_entregar is NULL)";
 if ($entregado_recibido==6) $where.=" $and (falta_recibir<>-1 or falta_recibir is NULL)";
 $control_and=1;
}

//***************************************************

//***********Licitación******************************
if ($filtrar_id_licitacion)
{if ($control_and==0) $and="";
 else $and="and";
 if ($id_licitacion) $where.=" $and nro_licitacion=".$id_licitacion;
 else $where.=" $and nro_licitacion is null";
 $control_and=1;
} 
//***************************************************

//*******************Proveedor***********************
if ($filtrar_id_proveedor)
{if ($control_and==0) $and="";
 else $and="and";
 $where.=" $and id_proveedor=".$proveedor;
 $control_and=1;
} 
//***************************************************

//***************Tipo moneda*************************
if ($filtrar_id_moneda)
{if ($control_and==0) $and="";
 else $and="and";
 $where.=" $and id_moneda=".$moneda;
 $control_and=1;
} 
//***************************************************

//**************Entidad******************************
if ($filtrar_id_entidad)
{if ($control_and==0) $and="";
 else $and="and";
 $where.=" $and id_entidad=".$entidad;
 $control_and=1;
} 
//***************************************************

//***************Orden de produccion*****************
if ($filtrar_orden_prod)
{if ($control_and==0) $and="";
 else $and="and";
 if ($orden_prod) $where.=" $and orden_prod=".$orden_prod;
 else $where.=" $and orden_prod is null";
 $control_and=1;
}
//***************************************************

//***************Nro. de factura*********************
if ($filtrar_nro_factura)
{if ($control_and==0) $and="";
 else $and="and";
 $where.=" $and nro_factura ilike '%".$nro_factura."%'";
 $control_and=1;
}
//***************************************************

//***************Fecha Factura***********************
if ($filtrar_fecha_factura)
{if ($control_and==0) $and="";
 else $and="and";
 $fecha_factura_1=fecha_db($fecha_factura_1);
 $fecha_factura_2=fecha_db($fecha_factura_2);
 if ($fecha_factura_1==$fecha_factura_2)
    $where.=" $and fecha_factura='$fecha_factura_1'";
 else $where.=" $and (fecha_factura>='$fecha_factura_1' 
                 and fecha_factura<='$fecha_factura_2')";    
 $control_and=1;
}
//***************************************************

//****************Lugar Entrega**********************
if ($filtrar_lugar_entrega)
{if ($control_and==0) $and="";
 else $and="and";
 $where.=" $and lugar_entrega ilike '%".$lugar_entrega."%'";
 $control_and=1;
}
//***************************************************


//*****************Fecha Entrega*********************
if ($filtrar_fecha_entrega)
{if ($control_and==0) $and="";
 else $and="and";
 $fecha_orden_1=fecha_db($fecha_entrega_1);
 $fecha_orden_2=fecha_db($fecha_entrega_2);
 if ($fecha_orden_1==$fecha_orden_2)
    $where.=" $and fecha_entrega='$fecha_orden_1'";
 else $where.=" $and (fecha_entrega>='$fecha_orden_1' 
                 and fecha_entrega<='$fecha_orden_2')";    
 $control_and=1;
} 
//***************************************************


//echo $where;
?>