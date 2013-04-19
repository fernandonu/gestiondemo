<?
/*
Author: Cesar

MODIFICADA POR
$Author: mari $
$Revision: 1.9 $
$Date: 2007/01/04 15:30:19 $
*/
require_once("../../config.php");
//DATOS del remito se extraen del arreglo POST
/*
print_r($_POST);
print_r($parametros);
print_r($_ses_global_backto);
*/



extract($_POST,EXTR_SKIP);

//borra la variable en caso de que venga desde licitaciones
if ($_ses_global_backto)
{
	extract($_ses_global_extra,EXTR_SKIP);
 	phpss_svars_set("_ses_global_backto", "");
 	phpss_svars_set("_ses_global_extra", array());
}

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

if ($remito)
 $id_remito=$remito;

?>
<html>
<head>
<title>Remitos Internos</title>

<?=$html_header?>
</head>
<script src="../../lib/popcalendar.js"></script>
<script language="JavaScript" src="funciones.js"></script>
<script>
//funcion que chequea que no se hayan puesto caracteres del tipo comillas dobles (")
//para que no salte un error de JavaScript...ver Bd de errores para mas info

function control_datos()
{
	if (document.all.nbre.value==''||document.all.nbre.value==' ')
 {
  alert('Falta ingresar el nombre del Cliente');
  return false;
 }
	if(document.all.nbre.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Nombre');
        return 0;
    }
    if(document.all.dir.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Dirección');
        return 0;
    }

    return 1;
}
/*****************************************************/

/********FUNCIONES QUE USA LA VENTANA HIJO**********
NOTA:
		LAS FUNCIONES TOMAN EL CONTEXTO DONDE SON DEFINIDAS
		OJO CON EL ACCESO A LAS VARIABLES*/
//variable que contiene la ventana para selecciona el cliente
var wcliente=0;

//variable que contiene la ventana que termina el remito
var wterminar=0;


//variable que contiene la ventana hijo productos
var wproductos=0;

function cargar()
{
/*Para insertar una fila*/
var items=document.all.items.value++;
//inserta al final
var fila=document.all.productos.insertRow(document.all.productos.rows.length );
//inserta al principio
//var fila=document.all.productos.insertRow(1);


fila.insertCell(0).innerHTML="<div align='center'> <input name='chk' type='checkbox' id='chk' value='1'></div><input type='hidden' name='idp_"+
items +"' value='"+ wproductos.document.all.select_producto.value+"'>";

fila.insertCell(1).innerHTML="<div align='center'> <input name='cant_"+
items+"' type='text' id='cantidad' size='4' value='1' style='text-align:right' "+
"onchange='calcular(this)' ></div>";

fila.insertCell(2).innerHTML="<div align='center'><textarea name='desc_"+
items +"' cols='60' rows='5' wrap='VIRTUAL' id='descripcion'>"+
wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].text +"</textarea></div>";

fila.insertCell(3).innerHTML="<div align='center'> <input name='precio_"+
items+"' type='text' id='unitario' size='10' style='text-align:right' value='"+
wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].id +"' "+
"onchange='calcular(this)'></div> ";

fila.insertCell(4).innerHTML="<div align='center'> <input name='subtotal_"+
items+"' type='text' readonly id='subtotal' size='10' style='text-align:right' value='"+
wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].id +"'></div>";


if (document.all.boton[0].disabled)
	document.all.boton[0].disabled=0;

document.all.guardar.value++;
//total();

//document.location.href="#botones";
//document.all.descripcion[0].value=wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].text;
//document.all.chk[0].value=wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].value;
}

/**************************************************/

function nuevo_item()
{
var items=document.all.items.value++;
var fila=document.all.productos.insertRow(document.all.productos.rows.length);

fila.insertCell(0).innerHTML="<div align='center'> <input name='chk' type='checkbox' id='chk' value='1'></div>";

fila.insertCell(1).innerHTML="<div align='center'> <input name='cant_"+
items+"' type='text' id='cantidad' size='4' value='1' style='text-align:right' "+
"></div>";

fila.insertCell(2).innerHTML="<div align='center'><textarea name='desc_"+
items +"' cols='100' rows='2' wrap='VIRTUAL' id='descripcion'></textarea></div>";


if (document.all.boton[0].disabled)
	document.all.boton[0].disabled=0;

document.all.guardar.value++;
}
/*************************************************/

function borrar_items()
{
var i=0;
while (typeof(document.all.chk)!='undefined' &&
		 typeof(document.all.chk.length)!='undefined' &&
		 i < document.all.chk.length)
{
   /*Para borrar una fila*/
  if (document.all.chk[i].checked)
   document.all.productos.deleteRow(i+1);
  else
  	i++;
}

if (typeof(document.all.chk)!='undefined' && document.all.chk.checked)
{
   document.all.productos.deleteRow(1);
   document.all.boton[0].disabled=1;
}
else if (typeof(document.all.chk)=='undefined')
   		document.all.boton[0].disabled=1;

//total();
}
//--------------------------------------------------------
//FUNCION PARA CHECKEAR LOS CAMPOS
var msg;
function chk_campos(terminar)
{
var ret_value=0;
msg="---------------------------------------------\t\n";
msg+="Falta Completar:\n\n";

if (document.all.nbre.value=="" || document.all.nbre.value==" " || document.all.nbre.value=="Haga click en Cliente para ver la lista")
 {
  msg+="\tNombre del cliente\n";
  ret_value++;
 }


//maximo de 25 productos
if (document.all.productos.rows.length > 26 && ret_value==0)
{
  msg="----------------------------------------------------------------------\t\n";
  msg+="Solo puede agregar un maximo de 25 productos diferentes\n";
  msg+="Haga otra remito para los demas!\n";
  ret_value=27;
}
if (ret_value < 27)
	msg+="---------------------------------------------\t";
else
  msg+="----------------------------------------------------------------------\t";

 return ret_value;
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
<script language='javascript'>
var winW=window.screen.Width;
var valor=(winW*25)/100;
var nombre1;
var titulo1;
function insertar(){
 ventana.document.all.titulo.innerText=titulo1;
 ventana.frames.frame1.location=nombre1;
 }

</script>

</head>
<body bgcolor="#E0E0E0">
<form name="remito" method="post" action="remito_int_proc.php">
<!--
<div align='right'>
<a href="#" onClick="abrir_ventana('/gestion/modulos/ayuda/remito_interno/ayuda_rem_int_nuevo.htm', 'LLENAR UN REMITO INTERNO')"> Ayuda </a>
</div>
-->
<?
 $estado='p';
 $nuevo=1;
 $q="SELECT * FROM remito_interno WHERE id_remito=$id_remito";
 if ($id_remito && $id_remito!=-1)
 {
 	$remito=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");
 	if ($remito->fields['estado']=='h')
         $permiso=" disabled ";
 	
	$fecha_remito=date("d/m/Y",strtotime($remito->fields['fecha_remito']));
	$nro_remito=$remito->fields['id_remito'];
//Añadir las condiciones si se permite cargar al remito ya creado
//los items de una factura
	$cliente=$remito->fields['cliente'];
	$direccion=$remito->fields['direccion'];
//recuperar el campo de entrega	
    $entrega=$remito->fields['entrega'];
	$estado=$remito->fields['estado'];
	$remito_oc=$remito->fields['nro_orden'];
	if (!$licitacion)
		$licitacion=$remito->fields['id_licitacion'];
	 unset($remito);
	 $nuevo=0;
 }

if (!$fecha_remito)
	$fecha_remito=date("d/m/Y");

if (!$id_remito || $id_remito==-1 || $estado=='p')
	$can_finish=1;

//original -> ($can_finish && (!$id_remito || $id_remito==-1))
//la nueva le permite a noelia cambiar el nro_facura
if (($can_finish && (!$id_remito || $id_remito==-1))||  $estado!='t' && ($_ses_user[login]=='noelia' || $_ses_user[login]=='cesar'))
	$can_search=1;

$q="select * from log_remito_interno where id_remito=";
$q.=($id_remito)?$id_remito:-1;
$q.=" order by fecha desc";
$log=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");


?>
<!-- tabla de registro -->
<div style="overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;' ?> "  >
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<?

do
{
?>
<tr>
    <?$fecha_log_temp=fecha($log->fields['fecha'])." ".Hora($log->fields['fecha']);?>
      <td height="20" nowrap>
        Fecha de <?if($log->fields['tipo_log'])
                     echo $log->fields['tipo_log'].": ";
                   else  
                     echo "creacion: ";
                     
         if($log->fields['fecha']!="")
          echo $fecha_log_temp;
         else 
          echo date("d/m/Y H:i:s",mktime());
         ?> 
      </td>
      <td nowrap > Usuario : <?=(($log->fields['usuario'])?$log->fields['usuario']:$_ses_user['name']); ?> </td>
</tr>
<?
 $log->MoveNext();
}
while (!$log->EOF);

?>
</table>
</div>

  <hr align="center" size="0">

   <input type="hidden" name="id_entidad" value="<?=$id_entidad?>">
  <table width="100%" border="0" cellspacing="1" cellpadding="1" align="center">
    <?if ($licitacion && !$id_remito){     //$parametros[licitacion]    
      $sql="select nombre,direccion from licitacion join entidad using (id_entidad) where id_licitacion=$licitacion";
      $res=sql($sql) or fin_pagina();
      $cliente=$res->fields['nombre'];
      $direccion=$res->fields['direccion'];
    }
    ?>
    
    
    <tr>
    <td height="29" colspan="2" align="center" valign="top">
        <font size='4' color=<?=$text_color_over?>><strong>Remito Interno 
          <?=($id_remito && $id_remito!=-1)? "Nº $id_remito":"" ?> <br> <? if ($licitacion) echo "<br> ASOCIADO A LICITACION N° $licitacion "?><strong></font>
        <font size='4' color=<?=$text_color_over?>>  
       <?if ($remito_oc) echo "Remito generado por la Orden de Compra Nro. $remito_oc" ?>
       </font>
        </td>
    </tr>
   
    <tr>
      <td width="54%" align="center" valign="top" nowrap>
        <table width="90%" border="1" cellpadding="0" cellspacing="0" bgcolor=<?=$bgcolor_out?>>
          <tr align="center" class="tablaEnc">
            <td height="20" colspan="2"> <strong>Cliente </strong> </td>
          </tr>
          <tr>
                <td><table>
                        <tr>
                                <td width="20%"><strong>Nombre</strong></td>
                                <td align="rigth"><input name="nbre" type="text" value="<?=$cliente?>" <?=$permiso?> size="47"></td>
                        </tr>
                        </table></td>
          </tr>
                <td><table>
                        <tr align="left">
                                <td  width="20%"><strong>Dirección</strong></td>
                                <td align="rigth"><input name="dir" type="text" value="<?=$direccion ?>" <?=$permiso?>  size="47"></td>
                        </tr>
                </table></td>
          </tr>      
                <td><table>
                        <tr align="left">
                                <td  width="35%"><strong>Entrega <font color=red>*</font></strong></td>
                                <td align="rigth"><textarea name='entrega' <?=$permiso?> rows="5" cols="45"><?=$entrega?></textarea></td>
                        </tr>
                        <tr><td colspan="2"><STRONG><font color=red>*</font> Se incluye en el pdf</STRONG></td></tr>
                </table></td>
          </tr>
        </table>
      </td>
      <td width="46%" align="center" valign="top" nowrap>
<table width="90%" border="1" cellpadding="0" cellspacing="0" bgcolor=<?=$bgcolor_out?>>
          <tr class="tablaEnc">
            <td colspan="2" height="20" align="center"><strong>Remito</strong></td>
          </tr>
          <tr>
            <td id="td_fecha_remito" height="25"><strong>Fecha Remito</strong></td>
            <td align="right"> <input name="fecha_remito" type="text" <?=$permiso?> value="<?= $fecha_remito ?>" readonly size="10">
              <img <?=$permiso?> src=../../imagenes/cal.gif border=0 align=center style='cursor:hand;' alt='Haga click aqui para
seleccionar la fecha'  onClick="javascript:popUpCalendar(td_fecha_remito, fecha_remito, 'dd/mm/yyyy');">
            </td>
          </tr>
        </table>
      </td>
    </tr>

  </table>
  
  <br>

  <table id="productos" width="100%" border="1" cellpadding="0" cellspacing="0" bgcolor=<?=$bgcolor_out?>>
    <tr bgcolor="#006699" class="tablaEnc">
      <td valign="middle" width="5%" height="18">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      <td width="10%"><div align="center"><strong>Cantidad</strong></div></td>
      <td width="85%"><div align="center"><strong>Item - Descripci&oacute;n</strong></div></td>
    </tr>
<?
$q="select * from items_remito_interno where id_remito=$id_remito";

if ($id_remito && $id_remito!=-1)
 	$items_remito=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");

$i=0; 	
$total=0;
while ($items_remito && !$items_remito->EOF)                  
{
?>
    <tr>
      <td valign="middle" align="center"> <input type="checkbox" name="chk" <?=$permiso?>></td>
      <td height="15"> <div align="center">
          <input name="cant_<?=$i ?>" type="text" style="text-align: right" size="4" value="<?= $items_remito->fields['cant_prod'] ?>" <?=$permiso?>>
          <input type="hidden" name="idi_<?=$i ?>" value="<?= $items_remito->fields['id_item'] ?>" >
        </div></td>
      <td height="15"> <div align="center">
          <textarea name="desc_<?=$i ?>" cols="100" rows="2" wrap="VIRTUAL" <?=$permiso?>><?= $items_remito->fields['descripcion'] ?></textarea>
        </div></td>
    </tr>
<?
	//$total+=$items_remito->fields['precio']*$items_remito->fields['cant_prod'];
	$items_remito->MoveNext();
        $i++;
}
?>

  </table>
  <br>
  <table width="95%" height="28" border="0" cellpadding="1" cellspacing="1" align="center">
    <tr> 

      <td height="26" align="center">
        <input name="boton" type="button"  value="Eliminar" <? if ($i==0) echo " disabled " ?> <?=$permiso?> onclick=
"if (confirm('Seguro que quieres eliminar los Items seleccionados'))
   borrar_items();
" title="Elimina los elementos seleccionados">
        <input name="boton" type="button"  value="Agregar" title="Agrega uno o mas productos" onclick="nuevo_item()" <?=$permiso?>>
        <input name="boton" type="submit"  value="Guardar" title="Guarda los cambios y permite posteriores modificaciones" <?=$permiso?> onclick=
"if (chk_campos()!=0)
{
alert (msg);
msg='';
return false;
}
if(control_datos()==0)
	return false;
if (document.all.licitacion.value=='' && document.all.oc_rem.value=='')
	return (confirm('El Remito no esta asociado con una licitacion\n¿desea continuar?')) ;
">
        <input name="boton" type="submit" value="Pasar Historial" title="No se permitiran posteriores modificaciones" <?=$permiso?> onclick=
"
if (chk_campos(1)!=0)
{
alert (msg);
msg='';
return false;
}
if(control_datos()==0)
	return false;
">
    <? /*   
    //echo  $pagina_viene;
     if ($pagina_viene=='ord_compra_fin') {
     */
     $link=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$remito_oc));	
     ?>	 
     <input type="button" name="boton" value="Volver" onclick="window.location='<?=$link?>'"> 
     <?//}?>
      </td>

    </tr>
  </table>
<input type='hidden' name='items' value='<?= $i ?>'>
<input type='hidden' name='nuevo' value='1'>
<input type='hidden' name='guardar' value='0'>
<input type='hidden' name='id_remito' value='<?= ($id_remito)?$id_remito:-1 ?>'>
<input type="hidden" name="estado" value="<?= $estado ?>">
<input type="hidden" name="licitacion" value="<?= $licitacion ?>">
<input type="hidden" name="oc_rem" value="<?= $remito_oc ?>">
</form>
</body>
</html> 