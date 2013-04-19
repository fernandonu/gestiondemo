<?
/*
Author: GACZ

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.1 $
$Date: 2003/11/10 14:00:19 $
*/

require_once("../../config.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Llenado de Remitos</title>
<?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" src="funciones.js"></script>
<script language="JavaScript">
/***********************************
generacion de las variables que contienen los datos del cliente
************************************/
<?
/*
//traemos los clientes para mostrar en el select que sigue
$query="select nombre,id_cliente,direccion,cuit,id_iva,iib,id_condicion,observaciones from clientes order by nombre"; 
$res_cliente=$db->Execute($query) or die ($db->ErrorMsg());
			      

while (!$res_cliente->EOF)
{
?>
var cliente_<?php echo $res_cliente->fields["id_cliente"]; ?>=new Array();
cliente_<?php echo $res_cliente->fields["id_cliente"]; ?>["nombre"]="<?php if($res_cliente->fields["nombre"]){echo $res_cliente->fields["nombre"];}else echo "null";?>";
cliente_<?php echo $res_cliente->fields["id_cliente"]; ?>["direccion"]="<?php if($res_cliente->fields["direccion"]){echo $res_cliente->fields["direccion"];}else echo "null";?>";
cliente_<?php echo $res_cliente->fields["id_cliente"]; ?>["cuit"]="<?php if($res_cliente->fields["cuit"]){echo $res_cliente->fields["cuit"];}else echo "null";?>";
<?$query="select porcentaje from tasa_iva where id_iva=".$res_cliente->fields["id_iva"];
$res=$db->Execute($query) or die ($db->ErrorMsg()."query de tasa");?>
cliente_<?php echo $res_cliente->fields["id_cliente"]; ?>["tasa_iva"]="<?php if($res_cliente->fields["id_iva"]){echo $res->fields["porcentaje"];}else echo "null";?>";
<?$query="select nombre from condicion_iva where id_condicion=".$res_cliente->fields["id_condicion"];
$res=$db->Execute($query) or die ($db->ErrorMsg()."query de condicion");?>
cliente_<?php echo $res_cliente->fields["id_cliente"]; ?>["condicion_iva"]="<?php if($res_cliente->fields["id_condicion"]){echo $res->fields["nombre"];}else echo "null";?>";
cliente_<?php echo $res_cliente->fields["id_cliente"]; ?>["iib"]="<?php if($res_cliente->fields["iib"]){echo $res_cliente->fields["iib"];}else echo "null";?>";
cliente_<?php echo $res_cliente->fields["id_cliente"]; ?>["otros"]="<?php if($res_cliente->fields["observaciones"]){echo $res_cliente->fields["observaciones"];}else echo "null";?>";
<?
$res_cliente->MoveNext();
}
?>
function set_datos()
{var info=new Array();
 switch(document.all.select_nbre.options[document.all.select_nbre.selectedIndex].value)
    {<?PHP
     $res_cliente->Move(0);
     while(!$res_cliente->EOF)
     {?>
      case '<?echo $res_cliente->fields["id_cliente"]?>': info=cliente_<?echo $res_cliente->fields["id_cliente"];?>;break; 
     <?
      $res_cliente->MoveNext();
     }
     ?>
     default:info["nombre"]="null";info["cuit"]="null";info["iib"]="null";info["direccion"]="null";info["otros"]="null";
             info["condicion_iva"]="null";info["tasa_iva"]="null"; break;
    }
    if(info["nombre"]!="null")
     document.all.c_nbre.value=info["nombre"];
    else
     document.all.c_nbre.value="";
    if(info["direccion"]!="null")
     document.all.c_dir.value=info["direccion"];
    else
     document.all.c_dir.value="";
    if(info["cuit"]!="null")
     document.all.c_cuit.value=info["cuit"];
    else
     document.all.c_cuit.value=""; 
    if(info["tasa_iva"]!="null")
     document.all.c_iva.value=info["tasa_iva"];
    else
     document.all.c_iva.value=""; 
    if(info["condicion_iva"]!="null")
     document.all.c_cond.value=info["condicion_iva"];
    else
     document.all.c_cond.value="";
    if(info["iib"]!="null")
     document.all.c_iib.value=info["iib"];
    else
     document.all.c_iib.value=""; 
    if(info["otros"]!="null")
     document.all.otros.value=info["otros"];
    else
     document.all.otros.value="";
    
}
*/?>

var wcliente=0;

//funcion que chequea que no se hayan puesto caracteres del tipo comillas dobles (") 
//para que no salte un error de JavaScript...ver Bd de errores para mas info
function control_datos()
{
	if (document.all.c_nbre.value==''||document.all.c_nbre.value==' ')
 {
  alert('Falta seleccionar el nombre del Cliente');
  return false;
 }  
	if(document.all.c_nbre.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Nombre');
        return 0;
    }
    if(document.all.c_dir.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Dirección');
        return 0;
    }
    if(document.all.c_cuit.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Cuit');
        return 0;
    }
    if(document.all.c_iva.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Tasa de I.V.A.');
        return 0;
    }
    if(document.all.c_cond.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Condición de I.V.A.');
        return 0;
    }
    if(document.all.c_iib.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo teléfono del I.I.B.');
        return 0;
    }
    if(document.all.otros.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Otros');
        return 0;
    }	
    return 1;
}


var item;
var max;
/***********************************
funcion que verifica si hay stock suficiente en los depositos
@return ==0 si hay en stock
         !=0 si no hay 
VARIABLES GLOBALES
@max contiene el valor maximo para el deposito seleccionado
@item contiene el numero de item que tiene el valor incorrecto de stock
************************************/
function chk_stock()
{
 var i;
 var itemcount=1; 
 for (i=0; i < document.form.length ; i++)
 {
     if (document.form.elements[i].id=='item')
	 {
		 var id_item1=document.form.elements[i].name;
		 var id_item=id_item1.substring(id_item1.indexOf("_")+1,id_item1.lenght);
		 var cantidad=eval("document.all.cant_"+ id_item);
		 var deposito=eval("document.all.lista_"+id_item);
		 var cant_deposito;
         var id_deposito;
         id_deposito=deposito.options[deposito.selectedIndex].value;
		 cant_deposito=eval("document.all.hidden_"+id_item+id_deposito);
		 if (!(typeof(cant_deposito)=='undefined'))   
         {
           //si no alcanza el stock o no ingreso un numero como cantidad
           if (isNaN(parseInt(cantidad.value)) ||
              ((parseInt(cant_deposito.value)-parseInt(cantidad.value)) < 0))
           {
            item=itemcount;
            max=cant_deposito.value;
            return itemcount;
           }
         }
         else
         {
           item=itemcount;
           max=0;
           return itemcount;
         }
         itemcount++;
	}
	else if (document.form.elements[i].id=='nuevo')
	{
		 var cantidad=document.all.cant_;
		 var deposito=document.all.lista_;
		 var cant_deposito;
         var id_deposito;

         //puede dar error si deposito.selectedIndex no tiene valor
         id_deposito=deposito.options[deposito.selectedIndex].value;
		 cant_deposito=eval("document.all.hidden_n"+id_deposito);
		 
		 //si da error arriba se deberia hacer esto
		 // cant_deposito=eval("document.all.hidden_n"+deposito.options[0]);

         if (!(typeof(cant_deposito)=='undefined'))   
         {
           //si no alcanza el stock o no ingreso un numero como cantidad
           if (isNaN(parseInt(cantidad.value)) ||
              ((parseInt(cant_deposito.value)-parseInt(cantidad.value)) < 0))
           {
            item=itemcount;
            max=cant_deposito.value;
            return itemcount;
           }
         }
         else
         {
           item=itemcount;
           max=0;
           return itemcount;
         }
         itemcount++;
	}

 }
 return 0; //hay suficiente en stock
}

//calcula el subtotal dependiendo de la cantidad
function calcular(textfield)
{
 id_item=textfield.name.substring(textfield.name.indexOf("_")+1,textfield.name.lenght);
 var subtotal=eval("document.all.subtotal_"+ id_item);
 var precio=eval("document.all.precio_"+ id_item);
 var cantidad=eval("document.all.cant_"+ id_item);
 if ( 0 >= precio.value.indexOf(','))
 {
   precio.value[precio.value.indexOf(',')]='.'; //entra pero no lo cambia
 }  
 subtotal.value=cantidad.value*parseFloat(precio.value);
 total();
}
/**********************************************************************
TOTAL: ESTA FUNCION DEBERIA CALCULAR EL TOTAL CUANDO ALGUN VALOR CAMBIE
**********************************************************************/
function total()
{
  document.all.total.value=0;
  var i;
 for (i=0; i < document.form.length ; i++)
 {
     if (document.form.elements[i].id=='item')
	 {
		 var id_item1=document.form.elements[i].name;
		 var id_item=id_item1.substring(id_item1.indexOf("_")+1,id_item1.lenght);
		 var cantidad=eval("document.all.cant_"+ id_item);
		 var deposito=eval("document.all.lista_"+id_item);
		 var cant_deposito;
         var id_deposito;
		 if (typeof(deposito.selectedIdex)=='number')
		 {
		   id_deposito=deposito.options[deposito.selectedIndex].value;
		   cant_deposito=eval("document.all.hidden_"+id_item+id_deposito+".value");
		 }
         else
            cant_deposito=deposito.options[0].value;

		 var precio=eval("document.all.precio_"+ id_item);
		 var subtotal=eval("document.all.subtotal_"+ id_item);
		 subtotal.value=parseFloat(precio.value)*cantidad.value;
         document.all.total.value=parseFloat(subtotal.value)+parseFloat(document.all.total.value);
	 }
 }
 if (typeof(document.all.id_p)!='undefined' )
 {
		 var cantidad=document.all.cant_;
		 var deposito=document.all.lista_;
		 var precio=document.all.precio_;
		 var subtotal=document.all.subtotal_;
		 subtotal.value=parseFloat(precio.value)*cantidad.value;
         var cant_deposito;
         var id_deposito;
  if (typeof(deposito.selectedIdex)=='number')
  {
	id_deposito=deposito.options[deposito.selectedIndex].value;
    cant_deposito=eval("document.all.hidden_n"+id_deposito+".value");
  }
  else
     cant_deposito=deposito.options[0].value;
     
  document.all.total.value=parseFloat(document.all.total.value)+parseFloat(subtotal.value);
 }
}
/********************************************************************
CHK_DEP: chequea que no pongan el mismo item con el mismo deposito
			2 veces
********************************************************************/
function chk_dep()
{
  var i;
  var j;
  var dep_prev=-2;//id_deposito al inicio del primer for
  var prod_prev=-2;//id_producto al inicio del primer for
  var dep_next=-1;//id_deposito al inicio del 2 for
  var prod_next=-1;//id_producto al inicio del 2 for
  var posicion1=1; //posiciones de match
  var posicion2=2;
 for (i=0; i < document.form.length ; i++)
 {
  //controlo que sea un item	
  if (document.form.elements[i].id=='item')
  {
    var id_item1=document.form.elements[i].name;
	 var id_item=id_item1.substring(id_item1.indexOf("_")+1,id_item1.lenght);
	 var deposito=eval("document.all.lista_"+id_item);
	 var producto=eval("document.all.id_p"+id_item);
    dep_prev=deposito.options[deposito.selectedIndex].value;//id_deposito
	 prod_prev=producto.value;//id_producto
	
     for (j=i+1; j < document.form.length; j++)
	  {
	   	if (document.form.elements[j].id=='item')
			{
				 var id_item1=document.form.elements[j].name;
				 var id_item=id_item1.substring(id_item1.indexOf("_")+1,id_item1.lenght);
				 var deposito=eval("document.all.lista_"+id_item);
				 var producto=eval("document.all.id_p"+id_item);
  			    dep_next=deposito.options[deposito.selectedIndex].value;//id_deposito
				 prod_next=producto.value;//id_producto
   			//si es el mismo producto y selecciono el mismo deposito
				if (prod_prev==prod_next && dep_prev==dep_next)
    			  return(posicion1+" y "+posicion2); //para identificar las posiciones
				posicion2++; 
			}
	  }
	   //si hay un item nuevo tambien debo compararlo 
		if (typeof(document.all.id_p)!='undefined' )
		 {
		   var deposito=document.all.lista_;
         prod_next=document.all.id_p.value;//id_producto
	      dep_next=deposito.options[deposito.selectedIndex].value;//id_deposito
   		//si es el mismo producto y selecciono el mismo deposito
			if (prod_prev==prod_next && dep_prev==dep_next)
	   	  return(posicion1+" y "+posicion2); //para identificar las posiciones
		 }
		posicion1=posicion1+1;
		posicion2=posicion1+1; 
	}//fin primer if
  }//fin primer for
  return 0;//Todo OK
}
/***********************************************************************/

function chk_campos()
{
 if (document.all.nro_remito.value=="")
 {
  alert("Falta el numero de remito");
  return 1; 
 }  
/*
 if (document.all.pedido.value=="")
 {
  alert("Falta completar el pedido");
  return 2; 
 }  
 if (document.all.venta.value=="")
 {
  alert("Falta el tipo de venta");
  return 3; 
 }  

 if (document.all.c_dir.value=="")
 {
  alert("Falta la direccion del cliente. A donde se lo van a llevar???");
  return 7; 
 }  

 if (document.all.c_iva.value=="")
 {
  alert("Falta completar el iva");
  return 8; 
 }  
*/
 if (document.all.c_nbre.value=="-1")
 {
  alert("Falta completar el nombre del Cliente");
  return 9; 
 }  

 return 0;
}

function chk_recib()
{
 if (document.all.recib_ap.value=='')
 {
  alert('Falta el apellido');
  return 4; 
 }  

 if (document.all.recib_nbre.value=='')
 {
  alert('Falta el nombre');
  return 5; 
 }  
/*
 if (document.all.recib_nrodoc.value=='')
 {
  alert('Falta el numero de documento');
  return 6; 
 }
*/  
 return 0;
}

</script>
<style type="text/css">
<!--
.tablaEnc {
	background-color: #006699;
	color: #c0c6c9;
}
-->
</style>
<?php
//incluye funciones para mostrar ayuda
include("../ayuda/ayudas.php");
?>

</head>
<?
//VARIABLES CON EL ID DEL PRODUCTO Y EL REMITO
//$parametros['remito']=135;
//$_POST['producto']
//$_POST['proveedor'] or $parametros['proveedor']
//DATOS DEL formulario
extract($_POST,EXTR_SKIP);

//prioridad a los parametros por GET
if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

$nuevo=1;
$items=0;
$datos;
$datos_items;
$nbre2;
$total=0;
$stock; //variable que contiene los campos hidden
        //nombre hidden='id_item+deposito'
$stock['cantidad']=0;
$permiso=" ";//variable que dice si se puede modificar o no
$permiso2=" disabled='true'";
$estado="<font color='#006600' size='+2'><b> NUEVO </b></font>"; //variable que dice el estado actual del remito
 //si viene el parametro del remito
 if ($remito)
 {
	$query="SELECT * FROM facturacion.remitos where id_remito=$remito";
	$datos=$db->Execute($query) or die($db->ErrorMsg()."en query 1");
	if ($datos->RecordCount())
	{
	 $nuevo=0;
	 if ($datos->fields['estado']=='A')
	 {
	 	 $estado="<font color='red' size='+2'><b> ANULADO </b></font>";
	 	 $permiso=" disabled='true' ";
  		 $permiso2=" disabled='true' ";
  		 $permiso3=" disabled='true' ";
  		 
	 }
	 elseif ($datos->fields['estado']=='P')
	 {
	 	$estado="<font color='#006600' size='+2'><b> PENDIENTE </b></font>";
	 	$permiso=" disabled='true' ";
      $permiso2=" "; //permiso de ejecucion
	 }
	 elseif ($datos->fields['estado']=='C')
	 {
	 	$estado="<font color='#006600' size='+2'><b> CERRADO </b></font>";
	 	$permiso=" disabled='true' ";
      $permiso2=" disabled='true' "; //permiso de ejecucion
		$permiso3=" disabled='true' ";
	 }
	 elseif ($datos->fields['estado']=='N')
	 {
	 	$estado="<font color='#006600' size='+2'><b> En confeccion </b></font>";
	 }
	 
	 $campos="r.*, i.*";
	 //NOTA: SI SE PONEN LOS CAMPOS LOS JAVASCRIPT DEJAN DE FUNCIONAR ???
	 $query=
	 "SELECT * FROM".
	 " facturacion.remitos as r JOIN".
	 " items_remito as i on r.id_remito=i.id_remito".
	 " WHERE r.id_remito=$remito";
	 //para obtener la info sobre los remitos
	 $query_depositos=
	 "SELECT r.id_remito,i.id_item,s.*" .
	 " FROM facturacion.remitos as r JOIN items_remito as i on r.id_remito=i.id_remito".
	 " JOIN stock as s on i.id_producto=s.id_producto AND i.id_proveedor=s.id_proveedor".
	 " WHERE r.id_remito=$remito";
	 $datos2=$db->Execute($query) or die($db->ErrorMsg(). "en query 2");
	 if ($datos2->RecordCount())
	 {
	 	 $datos=$datos2;
	 	 $items=$datos->RecordCount();
	 }
    $datos_depositos=$db->Execute($query_depositos) or die($db->ErrorMsg()."en query_depositos");
	 //aqui se generan los datos del stock y el deposito
	 while (!$datos_depositos->EOF)
	 {
	     $stock[]="<input type='hidden' name='hidden_".
	     $datos_depositos->fields['id_item'].$datos_depositos->fields['id_deposito'].
	  	 "' value='".$datos_depositos->fields['cant_disp']."'>";
	
	  	$stock['cantidad']++;
	   // print_r($datos_depositos->fields);
	  	$datos_depositos->MoveNext();
	 }
	 $nbre2=explode(",",$datos->fields['cliente2']); //"apellido,nombre"
	 $query_depositos="SELECT * FROM depositos";
	 $depositos=$db->Execute($query_depositos) or die($db->ErrorMsg());
	 //Si se paso el parametro producto para añadir
	 //PRE: HAY STOCK DEL PRODUCTO ELEGIDO (AUNQUE SEA CERO)
	 if ($producto && $proveedor)
	 {
	    $query_nuevo_item="SELECT p.desc_gral,s.id_deposito,precios.* FROM ".
	    "productos as p LEFT JOIN ".
		 "precios on precios.id_producto=p.id_producto LEFT JOIN ". 
	    "stock as s on precios.id_producto=s.id_producto AND precios.id_proveedor=s.id_proveedor ".
		 "where precios.id_producto=$producto AND precios.id_proveedor=$proveedor";
        $datos_nuevo_item= $db->Execute($query_nuevo_item) or die($db->ErrorMsg()."<br>".$query_nuevo_item);
		 $nuevo_item=$datos_nuevo_item->RowCount();
		 while (!$datos_nuevo_item->EOF)
		 {
			 $stock[]="<input type='hidden' id='nuevo' name='hidden_n".
			 			 $datos_nuevo_item->fields['id_deposito'].
		  	 			 "' value='".$datos_nuevo_item->fields['cant_disp']."'>";
		  	 $stock['cantidad']++;
		  	 $datos_nuevo_item->MoveNext();
		   // print_r($datos_nuevo_item->fields);
		 }
		 $datos_nuevo_item->MoveFirst();
	 }
	}
	else 
	    $nuevo=1;
 }
 else 
   $nuevo=1;
echo $msg;
unset($parametros['msg']);
?>
<body bgcolor="<? echo $bgcolor3 ?>">
<form name="form" method="post" action="remito_proc.php">

<div align='right'>
<a href="#" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/remitos/ayuda_rem_nuevo.htm" ?>', 'LLENAR UN REMITO')"> Ayuda </a>
</div>
<br>

<table width="100%" height="100" border="1" cellpadding="1" cellspacing="1">
    <tr>
      <td width="43%" height="100"> 
        <div align="center"><strong><font size="+1">Remito </font></strong><?PHP echo $estado ?></div></td>
    <td width="20%"></td>
      <td width="37%" valign="top"><b>Remito:</b> 
        <table width="207" border="0" align="right" cellpadding="0" cellspacing="0" >
          <tr> 
            <td width="111"><strong><font size="-1">N&ordm;</font></strong></td>
            <td width="89" align="left"> <input name="nro_remito" type="text" value="<? echo $datos->fields['nro_remito'] ?>" <? echo $permiso ?> size="12"> 
            <? echo "<input type=hidden name='id_remito' value='".$datos->fields['id_remito']."'>" ?>
            </td>
          </tr>
          <tr> 
            <td><font size="-1"><strong>Pedido N&ordm;</strong></font></td>
            <td align="left"> <input name="pedido" type="text"  value="<? echo $datos->fields['nro_pedido'] ?>" <? echo $permiso ?> size="12"> 
            </td>
          </tr>
          <tr> 
            <td><font size="-1"><strong>Venta:</strong></font></td>
            <td align="left"> <input name="venta" type="text"  value="<? echo $datos->fields['tipo_venta'] ?>" <? echo $permiso ?> size="12"> 
            </td>
          </tr>
        </table></td>
  </tr>
</table>

  <table width="100%" height="250" border="0" cellpadding="0" cellspacing="0">
    <!--DWLayoutTable-->
    <tr> 
      <td width="345" rowspan="2" valign="top" > <table width="100%" height="250" border="0" cellpadding="1" cellspacing="1">
          <!--DWLayoutTable-->
          <tr> 
            <td width="41" height="21">&nbsp;</td>
            <td width="262" align="left" valign="top"> 
              </td>
            <td width="32">&nbsp;</td>
          </tr>
          <tr>
            <td height="250">&nbsp;</td>
            <td height="250" align="left" valign="top">
              <table width="100%" border="1" align="center" cellpadding="1" cellspacing="1" >
                <tr align="center" class="tablaEnc"> 
                  <td colspan="2"><strong>Cliente Destino</strong></td>
                </tr>
                <tr> 
                 <td width="41%">
                    <strong>Nombre</strong>
                 </td>
                 <td width="59%"> 
                  <table>
                  <?
                  /*
                  <tr>
                  <td>
                  <select name="select_nbre"  <?echo $permiso ?> onchange="set_datos()">
                   <option value=-1>Seleccione Cliente</option>-->
                  <?
                  $no_es_nuevo=0;
                  $res_cliente->Move(0);
                  while(!$res_cliente->EOF)
                  {$selected="";
                   if($datos->fields['cliente1']==$res_cliente->fields['nombre'])
                   {
                    $no_es_nuevo=1;
                    $selected="selected";
                   }
                  ?>
                   <option value=<?=$res_cliente->fields['id_cliente']?> <?=$selected?>><?=cortar($res_cliente->fields['nombre'],35);?></option>
                  <?
                   $res_cliente->MoveNext();
                  }
                  ?>
                  </select>
                  </td>
                  </tr>*/?>
                  <tr>
                   <td>
                     <?$link=encode_link('../modulo_clientes/nuevo_cliente.php',array('pagina'=>'remitos'))?>
                 <input type="button" name="clientes" value="Elegir cliente" <? echo $permiso ?> title="Permite elegir cliente para la factura" onclick="if(wcliente==0 || wcliente.closed) wcliente=window.open(<?echo "'$link'"?>,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=190,top=0,width=600,height=450'); else wcliente.focus()"> 
                   </td>
                  </tr>
                  <tr>
                  <td>
                   <input name="c_nbre" type="text"  value="<? echo $datos->fields['cliente1'] ?>" <? echo $permiso ?> size="39">
                  </td>
                  </tr>
                  </table>
                 </td>
                </tr>
                <tr> 
                  <td><strong>Direccion</strong></td>
                  <td> <div align="left"> 
                      <input name="c_dir" type="text"  value="<? echo $datos->fields['direccion'] ?>" <? echo $permiso ?> size="39">
                    </div></td>
                </tr>
                <tr> 
                  <td height="24"><strong>C.U.I.T</strong></td>
                  <td><input name="c_cuit" type="text"  value="<? echo $datos->fields['cuit_cliente1'] ?>" <? echo $permiso ?> size="39" ></td>
                </tr>
                <tr> 
                  <td><strong>Tasa de I.V.A</strong></td>
                  <?/*if($no_es_nuevo)
                    {$query="select porcentaje from tasa_iva where id_iva=$t_iva";
                     $res_tasa1=$db->Execute($query) or die($db->ErrorMsg());
                    }*/
                  ?>  
                  <td><input name="c_iva" type="text"  value="<? echo $datos->fields['iva_tasa'] ?>" <? echo $permiso ?> size="39" ></td>
                  <!--<td><select name="c_iva"  <? echo $permiso ?> >
                   <option value=-1></option>
                   <?//traemos las tasas de IVA
                   /*$query="select id_iva,porcentaje from tasa_iva";
                   $res_tasa=$db->Execute($query) or die($db->ErrorMsg());
                   while(!$res_tasa->EOF)
                   {$selected="";
                    if($t_iva==$res_tasa->fields['id_iva'])
                     $selected="selected";
                   	?>
                    <option value=<? echo $res_tasa->fields['id_iva']?> <?=$selected?>><?echo $res_tasa->fields['porcentaje']?></option>
                   <?
                    $res_tasa->MoveNext();
                   }*/
                   ?> 
                   </select 
                 </td> -->
                </tr>
                <tr>
                <td><strong>Condición de I.V.A</strong></td>
                <?/*if($no_es_nuevo)
                  {$query="select nombre from general.condicion_iva where id_condicion=$c_iva";
                   $res_cond1=$db->Execute($query) or die($db->ErrorMsg());
                  }*/
                ?>  	
                <td><input name="c_cond" type="text"  value="<? echo $datos->fields['iva_cond'] ?>" <? echo $permiso ?> size="39" ></td>
                <!--<td>
                <select name="c_cond"  <? //echo $permiso ?> >
                   <option value=-1></option>
                   <?//traemos las condiciones de IVA
                   /*$query="select * from general.condicion_iva";
                   $res_cond=$db->Execute($query) or die($db->ErrorMsg());
                   while(!$res_cond->EOF)
                   {$selected="";
                    if($c_iva==$res_tasa->fields['id_condicion'])
                     $selected="selected";
                   	?>
                    <option value=<? echo $res_cond->fields['id_condicion']?> <?=$selected?>><?echo $res_cond->fields['nombre']?></option>
                   <?
                    $res_cond->MoveNext();
                   }*/
                   ?>
                   </select>  
                </td>-->
                </tr>
                <tr> 
                  <td><strong>I.I.B.</strong></td>
                  <td> <div align="left"> 
                      <input name="c_iib" type="text"  value="<? echo $datos->fields['nro_iib'] ?>"<? echo $permiso ?> size="39" >
                    </div></td>
                </tr>
                <tr> 
                  <td><strong>Otros</strong></td>
                  <td> <div align="left"> 
                      <textarea name="otros" <? echo $permiso ?> cols="38" wrap="VIRTUAL" ><? echo $datos->fields['otros'] ?></textarea>
                    </div></td>
                </tr>
              </table></td>
            <td>&nbsp;</td>
            
          </tr>
        </table></td>
      <td width="407" height="250" valign="top" > 
        <table width="100%" height="215" border="0" cellpadding="1" cellspacing="1">
          <!--DWLayoutTable-->
          <tr> 
            <td width="42" height="21">&nbsp;</td>
            <td width="310" valign="top"> <p align="left"><strong><font size="+1"> 
                </font></strong></p></td>
            <td width="55">&nbsp;</td>
          </tr>
          <tr> 
            <td height="191">&nbsp;</td>
            <td valign="top"> <div align="right"> 
                <table width="308" border="1" cellspacing="1" cellpadding="1">
                  <tr class="tablaEnc"> 
                    <td colspan="2" align="center"><strong>Recibido por</strong></td>
                  </tr>
                  <tr> 
                    <td width="157"><strong>Apellido/s </strong></td>
                    <td width="144"><input name="recib_ap" type="text"  value="<? echo $nbre2[0] ?>"<? echo $permiso3 ?> size="24"></td>
                  </tr>
                  <tr> 
                    <td><strong>Nombre/s</strong></td>
                    <td><input name="recib_nbre" type="text"  value="<? echo $nbre2[1] ?>"<? echo $permiso3 ?> size="24"></td>
                  </tr>
                  <tr> 
                    <td><strong>Tipo de Documento</strong></td>
                    <td> <div align="right"> 
                        <! seleccionar el tipo de DNI >
                        <select name="recib_tipodoc" <? echo $permiso3 ?> <? echo $permiso ?> onchange="beginEditing(this)">
                          <? switch ($datos->fields['tipo_doc_c2'])
	{
		case "D.N.I": $dni="selected";break;
		case "C.I": $ci="selected";break;
		case "L.C": $lc="selected";break;
		case "": $dni="selected";break;
		default: $otro="<option selected>".$datos->fields['tipo_doc_c2']."</option>";break;						
	}
?>
                          <option <?=$dni ?>>D.N.I</option>
                          <option <?=$le ?>>L.E</option>
                          <option <?=$ci ?>>C.I</option>
                          <option <?=$lc ?>>L.C</option>
                          <? if ($otro) echo $otro ?>
                          <option id="editable">añadir</option>
                        </select>
                      </div></td>
                  </tr>
                  <tr> 
                    <td><strong>N&ordm; de Documento</strong></td>
                    <td><input name="recib_nrodoc" type="text"  value="<? echo $datos->fields['nro_doc_c2'] ?>" <? echo $permiso3 ?> size="24"></td>
                  </tr>
                </table>
                <br><DIV align="right" <? echo $permiso ?> >
                Imprimir con precios 
                <INPUT type="checkbox" align="right" name="con_precios" value="1" >
              </div></td>
            <td>&nbsp;</td>
          </tr>
        </table>
        </td>
    </tr>
    <tr> 
      <td height="24" align="right" valign="top" > 
      </td>
    </tr>
  </table>
  <br>
  <table width="100%" border="2" cellpadding="1" cellspacing="1">
    <tr bgcolor="#006699" class="tablaEnc">
	  <td valign="middle" width="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      <td width="9%"><div align="center"><strong>Cantidad</strong></div></td>
      <td width="14%"><div align="center"><strong>Dep&oacute;sito</strong></div></td>
      <td width="41%"><div align="center"><strong>Item - Descripci&oacute;n</strong></div></td>
      <td width="20%"><div align="center"><strong>Precio Unitario</strong></div></td>
      <td width="16%"><div align="center"><strong>Monto Parcial</strong></div></td>
	</tr>
<?
if (($nuevo || ($items==0)) && (!$nuevo_item))
{
?>
<! fila de datos de nuevo remito>
	<tr> 
	  <td valign="middle"> 
        <input type="checkbox"  disabled></td>
      <td height="25"> 
        <div align="center"> 
          <input name="cant_1" type="text" style="text-align: right" size="4"  disabled >
        </div></td>
      <td width="14%" height="25"> 
        <div align="center"> 
          <select name="lista_1"  disabled >
            <option>Buenos Aires</option>
            <option selected>San Luis</option>
            <option>Virtual</option>
          </select>
        </div></td>
      <td height="25"> 
        <div align="center"> 
          <textarea name="desc_1" cols="42" rows="1" wrap="VIRTUAL"  disabled></textarea>
        </div></td>
      <td height="25"> 
        <div align="center"> 
          <input name="precio_1" type="text" style="text-align: right" size="10" maxlength="4" readonly="true"  disabled>
        </div></td>
      <td height="25"> 
        <div align="center"> 
          <input name="parcial_1" type="text" style="text-align:right"  size="10" maxlength="10" readonly="true"  disabled >
        </div></td>
    </tr>
<?
}
else 
{
  for ($i=0; $i < $items ; $i++)
  {
?>
	<tr> 
	  <td valign="middle"> 
        <input type="checkbox" name="chk_<? echo $datos->fields['id_item'] ?>" id="item" <? echo $permiso ?> value="1" ></td>
        <? echo "<input type=hidden name='id_i" .$datos->fields['id_item']."' value='".$datos->fields['id_item']."'>" ?>
        <? echo "<input type=hidden name='id_p" .$datos->fields['id_item']."' value='".$datos->fields['id_producto']."'>" ?>
        <td height="25"> 
        <div align="center">
          <input name="cant_<? echo $datos->fields['id_item'] ?>" <? echo $permiso ?>type="text" style="text-align: right" value="<? echo $datos->fields['cant_prod'] ?>" size="4" onchange="calcular(this)">
        </div></td>
      <td width="14%" height="25"> 
        <div align="center"> 
          <select name="lista_<? echo $datos->fields['id_item'] ?>" <? echo $permiso ?> >

           <? $depositos->MoveFirst();
              while (!$depositos->EOF) 
              {
           ?>
            	<option value="<? echo $depositos->fields['id_deposito']?>" <? if ($depositos->fields['id_deposito']==$datos->fields['id_deposito']) echo " selected" ?>><? echo $depositos->fields['nombre'] ?></option>
           <?   $depositos->MoveNext();
              } 
           ?>
           </select>
        </div></td>
      <td height="25"> 
        <div align="center"> 
          <textarea name="desc_<? echo $datos->fields['id_item'] ?>" <? echo $permiso ?> cols="42" rows="2" wrap="VIRTUAL" ><? echo $datos->fields['descripcion'] ?></textarea>
        </div></td>
      <td height="25"> 
        <div align="center"> 
          <input name="<? echo "precio_".$datos->fields['id_item'] ?>"<? echo $permiso ?> type="text" style="text-align: right" value="<? echo $datos->fields['precio'] ?>" size="10" <? echo $permiso ?> onchange="calcular(this)">
        </div></td>
      <td height="25"> 
        <div align="center"> 
          <input name="subtotal_<? echo $datos->fields['id_item'] ?>"<? echo $permiso ?> type="text" style="text-align:right" value="<? echo ($datos->fields['precio']*$datos->fields['cant_prod']);$total+=($datos->fields['precio']*$datos->fields['cant_prod']) ?>" size="10" readonly="true" >
        </div></td>
    </tr>
<?
  $datos->MoveNext();
  }
  if ($nuevo_item)
  {
?>
	<tr> 
	  <td valign="middle"> 
        <input type="checkbox" name="chk_" id="nuevo" value="1"></td>
        <? echo "<input type=hidden name='id_p' value='$producto'>" ?>
        <? echo "<input type=hidden name='proveedor' value='$proveedor'>" ?>
        <td height="25"> 
        <div align="center"> 
          <input name="cant_" id="nuevo" value="0" type="text" style="text-align: right"  size="4" onchange="calcular(this)">
        </div></td>
      <td width="14%" height="25"> 
        <div align="center"> 
          <select name="lista_" id="nuevo" >

           <? $depositos->MoveFirst();
              while (!$depositos->EOF) 
              {
           ?>
            	<option value="<? echo $depositos->fields['id_deposito'] ?>" <? if ($deposito==$depositos->fields['id_deposito']) echo " selected" ?>><? echo $depositos->fields['nombre'] ?></option>
           <?   $depositos->MoveNext();
              } 
           ?>
           </select>
        </div></td>
      <td height="25"> 
        <div align="center"> 
          <textarea name="desc_" id="nuevo" cols="42" rows="2" wrap="VIRTUAL" ><? echo $datos_nuevo_item->fields['desc_gral'] ?></textarea>
        </div></td>
      <td height="25"> 
        <div align="center"> 
          <input name="precio_" id="nuevo" type="text" style="text-align: right" value="<? echo $datos_nuevo_item->fields['precio'] ?>" size="10" onchange="calcular(this)" >
        </div></td>
      <td height="25"> 
        <div align="center"> 
          <input name="subtotal_" id="nuevo" type="text" style="text-align:right" value="0" size="10" readonly="true" >
        </div></td>
    </tr>
<?
  }
}
?>
  </table>
  <br>
  <table width="100%" height="28" border="0" cellpadding="1" cellspacing="1">
    <tr> 
      <td height="26" align="right"> 
        <input name="boton" type="submit" id="anular" <? echo $permiso2 ?>value="Anular" title="Se anulara el remito y dejara de ser valido"> 
        <input name="boton" type="submit" id="eliminar" <? echo $permiso ?> value="Eliminar" onclick=
"if (confirm('Seguro que quieres eliminar los Items seleccionados')) 
 {
   return true;
 }
 return false;
" title="Elimina los elementos seleccionados"> 
        <input name="boton" type="submit" id="añadir"<? echo $permiso ?> value="Añadir Producto" title="Añadir un nuevo producto" onclick=
"
if(control_datos()==0)
{return false;
}

var item; 
if ((item=chk_dep())!=0)
{
 alert ('Tiene 2 productos iguales con iguales depositos'+' Items '+item);
 return false;
}
 

"> 
        <input name="boton" type="submit" id="guardar"<? echo $permiso ?> value="Guardar" title="Guarda los cambios y permite posteriores modificaciones" onclick=
"
var item; 
if(control_datos()==0)
{return false;
}
if ((item=chk_dep())!=0)
{
 alert ('Tiene 2 productos iguales con iguales depositos'+' Items '+item);
 return false;
}

"> 
        <input name="boton" type="submit" id="terminar"<? echo $permiso ?> value="Terminar" onclick=
"if(control_datos()==0)
{return false;
}
 if (chk_campos()==0) 
 { var item=0;
   if ((item=chk_stock())==0)
   {
/*checkea que no ponga el mismoitem con el mismo deposito 2 veces
   if ((item=chk_dep())!=0)
     {
		 alert ('Tiene 2 productos iguales con iguales depositos'+' Items '+item);
       return false;
     }
*/
   }
   else
   {
     alert ('EL DEPOSITO ELEGIDO PARA EL ITEM '+item+' CONTIENE '+max+' UNIDADES' );
     return false;
   }  
 }
 else
   return false;

" title="Terminar y guardar el remito">
        <input type="submit" name="boton" value="Cerrar" title="Cerrar Remito" <?=$permiso2 ?> onclick=
"
if (chk_recib())
 return false;
"> </td>
      <td width="87" align="right"><strong>TOTAL</strong></td>
      <td width="124" align="center"> <div align="center"> 
          <input name="total" type="text" style="text-align: right"<? echo $permiso ?> id="total" value="<? echo $total ?>" size="10" readonly="true">
        </div></td>
    </tr>
  </table>
<?  for ($i=0; $i < $stock['cantidad']; $i++)
      echo $stock[$i]; 
    echo "<input type='hidden' name='items' value='$items'>";
    echo "<input type='hidden' name='nuevo' value='$nuevo'>";
?>
  </form>
</body>
</html>