<?php
/*
Autor: Fernando

MODIFICADA POR
$Author: fernando $
$Revision: 1.1 $
$Date: 2004/09/13 18:27:53 $
*/

require("../../config.php");
?>
<script>
function habilitar_boton(select)
{
if (select[select.selectedIndex].id!=-1)
         {
         document.all.cargar.disabled=0;
         }
         else
         {
         document.all.cargar.disabled=1;
         }

}

function buscar_op_submit(obj){
   var letra = String.fromCharCode(event.keyCode)
   if(puntero >= digitos){
       cadena="";
       puntero=0;
    }
   //si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto...
   if (event.keyCode == 13)
   {
       borrar_buffer();
       form1.submit();

      // if(objfoco!=0) objfoco.focus(); //evita foco a otro objeto si objfoco=0
    }
   //sino busco la cadena tipeada dentro del combo...
   else{
       buffer[puntero]=letra;
       //guardo en la posicion puntero la letra tipeada
       cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array
       puntero++;

       //barro todas las opciones que contiene el combo y las comparo la cadena...
       //en el indice cero la opcion no es valida
       for (var opcombo=1;opcombo < obj.length;opcombo++){
          if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase()){
          obj.selectedIndex=opcombo;break;
          }
       }
    }
   event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter
}


</script>
<?
echo $html_header;
//PARAMETROS DE ENTRADA
$onclickcargar= $parametros['onclickcargar'];
$onclicksalir= $parametros['onclicksalir'];

?>
<body>
<?php
$sql="select descripcion, codigo from tipos_prod ORDER BY descripcion";
$resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);


$link=encode_link("elegir_producto.php",array("tipo"=>$tipo,'onclickcargar'=>$onclickcargar,'onclicksalir'=>$onclicksalir));

?>
<link rel=stylesheet type='text/css' href='<? echo "$html_root/lib/estilos.css"?>'>
<form name="form1" action="<?php echo $link; ?>" method="POST">
<input type=hidden name=cambio_producto>
<br>
<br>
<table width="100%" align=center>
<tr id=mo>
  <td colspan=3>Elija el producto a agregar
</tr>
<tr id=ma>
 <td width="50%"> Tipo de Producto </td>
 <td width="50%"> Productos        </td>
</tr>
<tr id=mo>
<td>
<select name="select_tipo" size="20" onKeypress="buscar_op_submit(this);"onblur="borrar_buffer();"onclick="borrar_buffer();">
<option value=0 selected>Seleccione un Tipo de Producto</option>
 <?php
  while (!$resultado->EOF)
  {
  ?>
<option value="<?php echo $resultado->fields['codigo']; ?>" <?php if ($resultado->fields['codigo']==$_POST['select_tipo']) echo "selected";?>><?php echo $resultado->fields['descripcion']; ?></option>
 <?php
 $resultado->MoveNext();
 }
?>
</select>
</td>
<td>
<?php
if ($_POST['select_tipo']!=""){
$sql="select distinct(pp.id_producto),pp.desc_gral";
$sql.=" from general.productos as pp";
$sql.=" where pp.tipo='".$_POST['select_tipo']."'";
$sql.=" order by pp.desc_gral";
$resultado_prod=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);
}
?>
<select name="select_producto" size="20" style="width:260px;" onKeypress="buscar_op_submit(this);"onblur="borrar_buffer();"onclick="borrar_buffer();" onchange="habilitar_boton(this)" >
<option value=0 id="0">Seleccione un producto</option>
<?php
if ($_POST['select_tipo']!=""){
while (!$resultado_prod->EOF)
{
 $id_producto=$resultado_prod->fields['id_producto'];
 $descripcion=$resultado_prod->fields['desc_gral'];

 if ($id_producto==$_POST['select_producto'])
						   $selected="selected";
						   else
						   $selected="";
?>
<option value="<?=$id_producto?>"<?=$selected;?>>
 <?=$descripcion?>
</option>
<?php
$resultado_prod->MoveNext();
}
}//del if que muestra los productos
?>
</select>
</td>
</tr>
<tr>
  <td colspan=2 align=center>
  <input type="button" name="cargar" value="Cargar" disabled style="width:70" onClick="<?=$onclickcargar ?>">
  <input type="button" name="salir" value="Salir"  style="width:70" onClick="window.close()">
  </td>
</tr>
</table>
</form>
</body>
</html>