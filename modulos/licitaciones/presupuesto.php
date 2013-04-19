<?
/*
modificado por
$Author: Fernando
$Revision: 1.27 $
$Date: 2004/09/21 19:27:00 $
*/
require_once("../../config.php");



if($_POST['valor_boton'])    $_POST['boton']=$_POST['valor_boton'];

$id_entrega_estimada=$parametros['id_entrega_estimada'] or $id_entrega_estimada=$_POST['id_entrega_estimada'];
$id=$parametros['ID'] or $id=$_POST['id'];
$id_subir=$parametros['id_subir'] or $id_subir=$_POST['id_subir'];
$nro_orden_cliente=$parametros['nro_orden_cliente'] or $nro_orden_cliente=$_POST['nro_orden_cliente'];
$id_prop=$parametros['id_lic_prop'] or $id_prop=$_POST['id_prop'];


if ($id_prop!="") //existe licitacion asociada
  {
  $sql="select titulo,estado,fecha_cotizacion
        from licitacion_presupuesto_new
        where id_licitacion_prop=".$id_prop;
  $resultado_licitacion=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
  }
switch($resultado_licitacion->fields['estado']) //voy a distintas paginas dependiendo el estado del "presupuesto"
{
 case 1:
 case 2:{
         require_once("detalle_presupuesto.php");
         break;
        } //se ve el detalle de la licitacion
 default:{
          switch($_POST['boton'])
          {
           case "Guardar":
           case "Comenzar pedido de presupuesto":{
                                                 require_once("guardar_presupuesto.php");
                                                 break;
                                                 }
           case "Volver":{
                           $link=encode_link("../ordprod/seguimiento_orden.php",array("id"=>$id,"id_entrega_estimada"=>$id_entrega_estimada,"id_subir"=>$id_subir,"nro_orden_cliente"=>$nro_orden_cliente));header("location: $link");
                           die;
                           break;
                           }
           default:
           {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<script languaje="javascript">

function check(nro_i)
{
 var ch=eval("document.all.check_"+nro_i);
 var hi=eval("document.all.insertar_renglon_"+nro_i);
 if(ch.checked)
           hi.value=1;
           else
           hi.value=0;

}

function val_text() //recupero proveedores elegidos
{var a=new Array();
 var largo=document.all.proveedores.length;
 var i=0;
 for(i;i<largo;i++)
  a[i]=document.all.proveedores.options[i].value;
 document.all.valores_prov.value=a;
}

function borrar_fila(nro_i,nro_j)
{var i=0;
 var j=1;
 var prod;
 var tabla=eval("document.all.tabla_"+nro_i);
 var chk=eval("document.all.chk_"+nro_i);
 var boton=eval("document.all.boton2_"+nro_i);
 while (typeof(chk)!='undefined' &&
		 typeof(chk.length)!='undefined' &&
		 i < chk.length)
 {
   /*Para borrar una fila*/
  //alert(parseInt(j));
  if (chk[i].checked)
  {//alert(parseInt(j));
   prod=eval("document.all.prod_"+nro_i+"_"+parseInt(j));
   prod.value=0;
   tabla.deleteRow(i+1);
  }
  else
  	i++;
  j++;
}//fin while

if (typeof(chk)!='undefined' && chk.checked)
{
   tabla.deleteRow(1);
   boton.disabled=1;
   prod=eval("document.all.prod_"+nro_i+"_"+1);
   prod.value=0;

}
else if (typeof(chk)=='undefined')
   		boton.disabled=1;
}//fin funcion

var wproductos=0;

function cargar_viejo(nro_i,nro_j)
{
 var items=eval("document.all.cant_prod_"+nro_i);
 items.value=parseInt(items.value)+1;
 nro_j=items.value;
 var tabla=eval("document.all.tabla_"+nro_i);
 var fila=tabla.insertRow(tabla.rows.length-1);

 //fila.insertCell(0).innerHTML="";
 fila.insertCell(0).innerHTML="<input type='text' name='cantidad_"+nro_i+""+nro_j+"' size='2' value='1'>";
 fila.insertCell(1).innerHTML="<b>"+wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].text+
   "<input type='hidden' name='prod_"+nro_i+"_"+nro_j+"' value='1'>"+
   "<input type='hidden' name='existe_prod_"+nro_i+"_"+nro_j+"' value='0'>"+
   "<input type='hidden' name='id_prod_"+nro_i+"_"+nro_j+"' value=''>"+
   "<input type='hidden' name='adicionales_"+nro_i+"_"+nro_j+"' value='1'>"+
   "<input type='hidden' name='id_prod2_"+nro_i+"_"+nro_j+"' value='"+wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].value+"'>";
fila.insertCell(2).innerHTML="<input type='text' name='desc_nueva"+nro_i+"_"+nro_j+"' value='"+wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].text+"' size='40'>";
fila.insertCell(3).innerHTML="<input type='checkbox' name='chk_"+nro_i+"' value=1>";
wproductos.close();
}

function cargar(nro_i,nro_j)
{
 var items=eval("document.all.cant_prod_"+nro_i);
 items.value=parseInt(items.value)+1;
 nro_j=items.value;
 var tabla=eval("document.all.tabla_"+nro_i);
 var fila=tabla.insertRow(tabla.rows.length-1);

 //fila.insertCell(0).innerHTML="";
 fila.insertCell(0).innerHTML="<input type='text' name='cantidad_"+nro_i+"_"+nro_j+"' size='2' value='1'>";
 fila.insertCell(1).innerHTML="<b>"+wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].text+
   "<input type='hidden' name='prod_"+nro_i+"_"+nro_j+"' value='1'>"+
   "<input type='hidden' name='existe_prod_"+nro_i+"_"+nro_j+"' value='0'>"+
   "<input type='hidden' name='id_prod_"+nro_i+"_"+nro_j+"' value=''>"+
   "<input type='hidden' name='adicionales_"+nro_i+"_"+nro_j+"' value='0'>"+
   "<input type='hidden' name='id_prod2_"+nro_i+"_"+nro_j+"' value='"+wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].value+"'>";
 fila.insertCell(2).innerHTML="<input type='text' name='desc_nueva"+nro_i+"_"+nro_j+"' value='"+wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].text+"' size='40'>";
 fila.insertCell(3).innerHTML="<input type='checkbox' name='chk_"+nro_i+"' value=1>";
 wproductos.close();
}



function chequear_prov()
{alert(options['proveedores'].length);
 /*for (var intLoop = 0; intLoop < optionsopt.length; intLoop++)
  {if (opt[intLoop].selected)
    return true;
  }//fin for
 alert("Debe elegir un proveedor para presupuestar la licitación");
 return false;*/
}//fin funcion chequear_prov

//inserta proveedores en el select
function insertar_prov(texto,value)
{
    isNew = true;
   	boxLength = document.form.proveedores.length;
   	selectedText=texto;
   	selectedValue=value;
   if (boxLength != 0) {
      for (i = 0;i < boxLength; i++) {
       thisitem = document.form.proveedores.options[i].value;
       if (thisitem == selectedValue)
         isNew = false;
     }//fin for
   }//fin if
   if (isNew) {
   	 newoption = new Option(selectedText, selectedValue, false, false);
     document.form.proveedores.options[boxLength] = newoption;
   }//fin if
}

//borra proveedor
function borrar_prov()
{var boxLength = document.form.proveedores.length;
 arrSelected = new Array();
 var count=0;
 for (i = 0; i < boxLength; i++) {
  if (document.form.proveedores.options[i].selected) {
   arrSelected[count] = document.form.proveedores.options[i].value;
   count++;
  }
 }
var x;
 for (i = 0; i < boxLength; i++) {
  for (x = 0; x < arrSelected.length; x++) {
   if (document.form.proveedores.options[i].value == arrSelected[x]) {
      document.form.proveedores.options[i] = null;
     }
    }
    boxLength = document.form.proveedores.length;
   }
}

//funcion que inserta los proveedores por defecto
function cargar_proveedores()
{var longt=document.all.proveedores_defecto.options.length;
 for(var i=0;i<longt;i++)
  {if(document.all.proveedores_defecto.options[i].selected)
  	insertar_prov(document.all.proveedores_defecto.options[i].text,document.all.proveedores_defecto.options[i].value);
  }
}

function mostrar_ocultar(div,check)
{var check1;
 check1=eval("document.all."+check);
 if (check1.checked==true)
  Mostrar(div);
 else
  Ocultar(div);
}

function control_boton()
{
if((document.all.titulo.value=='') &&(window.event.keyCode==13))
 {alert('Debe ingresar un titulo al presupuesto');
  return false;
 }
if (window.event.keyCode==13)
 {document.all.valor_boton.value='Guardar';
  document.all.form.submit();
 }
 if (window.event.keyCode==27)
 {document.all.valor_boton.value='Volver';
  document.all.form.submit();
 }
}


</script>

<title>Presupuestar Licitación</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script languaje="javascript" src="../../lib/funciones.js"></script>
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
</head>
<body bgcolor="<?=$bgcolor3;?>" onkeypress="control_boton();" onload="this.focus()">
<?
cargar_calendario();

if ($resultado_licitacion->fields['estado']=="")
{
 //no ingreso nada todavia
 $sql="select distinct(codigo_renglon),id_renglon,titulo,codigo_renglon,cantidad
       from renglon
       join historial_estados using (id_renglon)
       join estado_renglon using(id_estado_renglon)
       where renglon.id_licitacion=".$id." and estado_renglon.id_estado_renglon=3";
 $resultado=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
 $existe_reng=0;
}
else
{
//ya ingreso datos de presupuesto
 $query="select renglon_presupuesto_new.id_renglon_prop,renglon_presupuesto_new.id_renglon,
              renglon.titulo,renglon_presupuesto_new.cantidad,renglon.codigo_renglon
              from renglon_presupuesto_new
              join renglon using(id_renglon)
              where renglon_presupuesto_new.id_licitacion_prop=".$id_prop;
 $resultado=$db->Execute($query) or die ("".$db->ErrorMsg()."<br>".$query);
 $existe_reng=1;
 //echo "<br>".$query;
}
?>
<form name="form" action="presupuesto.php" method="POST" onkeypress="control_boton();">
<input type="hidden" name="id" value="<? echo $id; ?>">
<input type="hidden" name="id_prop" value="<? echo $id_prop; ?>">
<input type="hidden" name="id_subir" value="<? echo $id_subir; ?>">
<input type="hidden" name="nro_orden_cliente" value="<? echo $nro_orden_cliente; ?>">
<input type="hidden" name="cant_renglones" value="<? echo $resultado->RecordCount(); ?>">
<input type="hidden" name="id_entrega_estimada" value="<? echo $id_entrega_estimada; ?>">
<input type="hidden" name="valor_boton">
<table width="90%" align=center border=1>
<tr>
  <td id=mo>Presupuesto  de la Licitación</td>
</tr>
<tr>
 <td>
 <table width=100% align=center cellpading=1 cellspacing=1 >
   <tr id=ma_sf>
     <td width=20% align=left>
     Licitacion ID:
     </td>
     <td align=left>
     <font color="Blue" size="2" onclick='window.open("<?=encode_link("../licitaciones/licitaciones_view.php",array('cmd1'=>'detalle',"ID"=>$id));?>","","");' style="cursor:hand">
     <? echo $id; ?>
     </font>
     </td>
   </tr>
    <?
   $sql="select entidad.nombre
         from entidad
         join licitacion using(id_entidad)
         where licitacion.id_licitacion=".$id;
   $resultado_entidad=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
   ?>
   <tr id=ma_sf>
     <td>
     Entidad:
     </td>
     <td>
     <font color="Blue" size="2">
     <? echo $resultado_entidad->fields['nombre']; ?>
     </font>
     </td>
  </tr>
  <tr id=ma_sf>
     <td>Titulo</td>
     <td>
     <input type="text" name="titulo" size="70" value="<? echo $resultado_licitacion->fields['titulo']; ?>">
     </td>
  </tr>
 </table>
</td>
</tr>
<tr>
  <td  id=mo>Renglones de la Licitación</td>
</tr>
<tr>
  <td>
  <!-- Aca empieza las descripcion de los renglones -->
     <table width="100%" align=center border=0>

      <?
      $i=1;
      //while que recorre todos los renglones
      while (!$resultado->EOF) //traigo los renglones
      {

       //if ($i==1) die("se colgo me parece");
      ?>
       <tr>
         <td>
          <table border="0" width=100% align=center>
          <tr id=ma>
           <td width=1%>&nbsp;</td>
           <td width=60%><b>Renglon</b></td>
           <td width=10%><b>Cantidad</b></td>
           <td>Titulo</td>
         </tr>
         <input type="hidden" name="insertar_renglon_<? echo $i; ?>" value="<?=$existe_reng;?>">
         <input type="hidden" name="existe_renglon_<? echo $i; ?>" value="<? if($existe_reng) echo 1; else echo 0;?>">
         <input type="hidden" name="id_renglon_prop_<? echo $i; ?>" value="<? echo $resultado->fields['id_renglon_prop']; ?>">
         <input type="hidden" name="id_renglon_<? echo $i; ?>" value="<? echo $resultado->fields['id_renglon']; ?>">
         <tr>
           <td align=center>
           <input type="checkbox" name="check_<? echo $i; ?>" onClick="mostrar_ocultar('div_<? echo $i; ?>','check_<? echo $i; ?>');check(<? echo $i; ?>);" value=1" <? if($existe_reng) echo "checked";?> onfocus="control_boton();">
           </td>
           <td align="left"><b><? echo $resultado->fields['codigo_renglon']; ?></td>
           <td align="center"><input type="text" name="cant_renglon_<? echo $i; ?>" value="<? echo $resultado->fields['cantidad']; ?>" size="3"></td>
           <td align=left><b><? echo $resultado->fields['titulo']; ?></td>
         </tr>
      </table>
     <div id="div_<? echo $i; ?>" style="display:none;">
     <table width="100%" align="center" id="tabla_<? echo $i; ?>" border="1" cellspacing="0" cellpading=0>
     <tr id=mo_sf>
         <td width="1%"><b>Cant.</td>
         <td width="50%"><b>Producto</td>
         <td width="40%"><b>Descripcion para el proveedor</td>
         <td><b>Borrar</td>
    </tr>
    <? //muestro productos del renglon de presupuesto
    //die("<br>hasta aca llega.$id_prop");
    if ($id_prop!="") //traigo productos de presupuesto
        {//$sql="select producto_presupuesto.id_producto,producto_presupuesto.desc_nueva,producto.desc_gral,producto.cantidad,producto.id from producto left join producto_presupuesto renglon_presupuesto on producto.id_renglon=renglon_presupuesto.nro_renglon left join producto_presupuesto on producto_presupuesto.id_renglon=renglon_presupuesto.id_renglon and producto_presupuesto.id=producto.id where producto.id_renglon=".$resultado->fields['id_renglon'];
        $sql="select producto_presupuesto_new.id_producto,producto_presupuesto_new.desc_nueva,
                     producto_presupuesto_new.id_producto_presupuesto,
                     productos.desc_gral,producto_presupuesto_new.cantidad
         from licitaciones.renglon_presupuesto_new
         left join licitaciones.producto_presupuesto_new using(id_renglon_prop)
         join general.productos using(id_producto)
         where renglon_presupuesto_new.id_renglon_prop=".$resultado->fields['id_renglon_prop'];
         $resultado_producto=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);

       }
       else //traigo productos del renglon
          {
          $sql="select productos.desc_gral,cantidad,productos.id_producto
                from productos
                join producto using(id_producto)
                where producto.id_renglon=".$resultado->fields['id_renglon'];
         $resultado_producto=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
         if ($_ses_user["login"]=="fernando")echo "<br>$sql<br>";
         }

         $j=1;
         while(!$resultado_producto->EOF)
         {
         ?>
         <input type="hidden" name="prod_<? echo $i; ?>_<? echo $j; ?>" value="1">
         <input type="hidden" name="existe_prod_<? echo $i."_".$j; ?>" value="<? if ($resultado_producto->fields['id_producto_presupuesto']!="") echo 1; else echo 0; ?>">
         <input type="hidden" name="id_prod_<? echo $i."_".$j; ?>" value="<? echo $resultado_producto->fields['id_producto_presupuesto']; ?>">
         <input type="hidden" name="adicionales_<? echo $i."_".$j; ?>" value="0">
         <input type="hidden" name="id_prod2_<? echo $i."_".$j; ?>" value="<? echo $resultado_producto->fields['id_producto']; ?>">
         <?
         if ($resultado_producto->fields['desc_nueva']=="")
            {
            $desc=$resultado_producto->fields['desc_gral'];
            }
            else
            {
            $desc=$resultado_producto->fields['desc_nueva'];
            }
         ?>
       <tr>
        <td><b><input type="text" name="cantidad_<?php echo $i."_".$j; ?>" size="3" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;" value="<?php echo $resultado_producto->fields['cantidad']; ?>" readonly></td>
        <td><b><? echo $resultado_producto->fields['desc_gral']; ?></td>
        <td><b><input type="text" name="desc_nueva<? echo $i."_".$j; ?>" value="<? echo $desc; ?>" size="40"></td>
        <td width="1%" title="Oprima boton borrar"><input type="checkbox" name="chk_<? echo $i; ?>" value=1> </td>
        <input type="hidden" name="chk_<? echo $i; ?>_<? echo $j; ?>" value="0">
       </tr>
       <?
       $j++;
       $resultado_producto->MoveNext();
      }


   ?>
   <tr>
   <td align="center" colspan="5">
   <input type="button" name="boton" value="Añadir Producto" onclick="wproductos=window.open('<?=encode_link('../ord_compra/seleccionar_productos.php',array('onclickcargar'=>"window.opener.cargar($i,$j);",'onclicksalir'=>'window.close()','cambiar'=>0,'id_proveedor'=>0)) ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=600,height=300');">
   <input type="button" name="boton2_<? echo $i; ?>" value="Borrar" onclick="borrar_fila(<? echo $i; ?>,<? echo $j; ?>);"></td>
   </tr>
   <input type="hidden" name="cant_prod_<? echo $i; ?>" value="<? echo $resultado_producto->RecordCount()+$cant;?>">
   </table>
   </div>
</td>
</tr>
<?
$resultado->MoveNext();
$i++;
}
?>
   </table>
</td>
</tr>
</table>

   <!--
   <TABLE>
   <tr>
   <td>
   <p><b>Fecha Limite de Cotizaci&oacute;n
   <input name="fecha_cotizacion" type="text" value="<? echo Fecha($resultado_licitacion->fields['fecha_cotizacion']); ?>" readonly>&nbsp;<?php echo link_calendario("fecha_cotizacion"); ?>
   </p>
   </td>
   <td>
   <?
   $hora=substr($resultado_licitacion->fields['fecha_cotizacion'],11,2);
   $minuto=substr($resultado_licitacion->fields['fecha_cotizacion'],14,2);
   ?>
   hh<select name="hora">
   <option <? if ($hora=='09') echo "selected"; ?>>09</option>
   <option <? if ($hora=='10') echo "selected"; ?>>10</option>
   <option <? if ($hora=='11') echo "selected"; ?>>11</option>
   <option <? if ($hora=='12') echo "selected"; ?>>12</option>
   <option <? if ($hora=='13') echo "selected"; ?>>13</option>
   <option <? if ($hora=='14') echo "selected"; ?>>14</option>
   <option <? if ($hora=='15') echo "selected"; ?>>15</option>
   <option <? if ($hora=='16') echo "selected"; ?>>16</option>
   <option <? if ($hora=='17') echo "selected"; ?>>17</option>
   <option <? if ($hora=='18') echo "selected"; ?>>18</option>
   <option <? if ($hora=='19') echo "selected"; ?>>19</option>
   <option <? if ($hora=='20') echo "selected"; ?>>20</option>
   </select>
   </td>
   <td>
    mm<select name="minutos">
    <option value="10" <? if ($minuto=='10') echo "selected"; ?>>00-10</option>
    <option value="20" <? if ($minuto=='20') echo "selected"; ?>>10-20</option>
    <option value="30" <? if ($minuto=='30') echo "selected"; ?>>20-30</option>
    <option value="40" <? if ($minuto=='40') echo "selected"; ?>>30-40</option>
    <option value="50" <? if ($minuto=='50') echo "selected"; ?>>40-50</option>
    <option value="60" <? if ($minuto=='60') echo "selected"; ?>>50-60</option>
    </select>
    </td>
    </tr>
</table>
-->
<input type="hidden" name="valores_prov" value="">
<center>
<? if ($resultado->RecordCount()<=0)
{
?>
<font color="<?=$bgcolor1;?>" size="2"><b>NO EXISTE RENGLON GANADO EN ESTA LICITACION</FONT><br>
<?
}
else
{
?>
<input type="submit" name="boton" value="Guardar" onclick="  if(document.all.titulo.value=='')
                                                              {alert('Debe ingresar un titulo al presupuesto');
                                                               return false;
                                                              }
                                                              ">&nbsp;&nbsp;
<input type="submit" name="boton" value="Comenzar pedido de presupuesto" onclick="
                                                             if(document.all.titulo.value=='')
                                                              {alert('Debe ingresar un titulo al presupuesto');
                                                               return false;
                                                              }
                                                             ">
<?
}//fin else
?>
<input type="submit" name="boton" value="Volver">
</center>
</form>
</body>
</html>
<? }//fin default
  }//fin switch
 }//fin default
}//fin switch
?>
