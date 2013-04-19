<?
/*
este archivo se llama stock_cambio_producto pero en realidad cambia el proveedor y no
el producto. Perdon pero no tenia ganas de renegar con los permisos.
Autor: Broggi
Creado: viernes 10/11/04

MODIFICADA POR
$Author: enrique $
$Revision: 1.6 $
$Date: 2006/02/18 16:41:59 $
*/

require_once("../../config.php");



//$onclick['cargar']=$parametros['onclickcargar'] or $onclick['cargar']=$_POST['onclickcargar'];
//$onclick['cancelar']=$parametros['onclickcancelar'] or $onclick['cancelar']=$_POST['onclickcancelar'] or $onclick['cancelar']="window.close()";
$producto=$_GET['id_producto'] or $producto=$_POST['id_producto'];

//extract($_POST,EXTR_OVERWRITE);


echo $html_header;

if ($_POST['bcargar']=="Cargar") {
	$id_proveedor=$_POST['proveedor'];   
   	$prove=split("\|",$id_proveedor);   	   	
   	$id_proveedor=$prove[0];
   	$razon_social=$prove[1];   	   	   	
   /* $sql = "select precio from precios where id_proveedor=$id_proveedor and id_producto=$producto";
    $resul = sql($sql) or  fin_pagina($sql);
    if ($resul->RecordCount()!=0) $precio=$resul->fields['precio']; else $precio=0.00;*/
    ?>
     <input name="precio" type="hidden" value="<?=$precio?>">
     <input name="id_prov" type="hidden" value="<?=$id_proveedor?>">       
     <input name="razon_social" type="hidden" value="<?=$razon_social?>">       
     <script>window.opener.cargar_2();</script>
    <?
    
   }	 


$sql = "select id_proveedor, razon_social from proveedor order by razon_social";
$resul = sql($sql) or fin_pagina();
?>

<html>
</body>
<form name='stock_cambio_producto' action="stock_cambio_producto.php" method="POST">
<table align="center" class="bordes">
 <tr>
  <td align="center" colspan="2">
   <font size="2"><b>Seleccione el Proveedor</b></font>
  </td>
 </tr> 
 <tr>
  <input name="id_producto" type="hidden" value="<?=$producto?>">
  <td align="center" colspan="2"> 
   <select name="proveedor" onKeypress="buscar_op(this);" onblur="borrar_buffer()" onclick="borrar_buffer()">
    <? while (!$resul->EOF)
             {
             ?>
              <option value="<?=$resul->fields['id_proveedor']?>|<?=$resul->fields['razon_social']?>" ><?=$resul->fields['razon_social']?></option>
             <?
              $resul->MoveNext();	
             }	
     
    ?>
   </select>
  </td>
 </tr>
 <tr>
  <td align="center">
   <input name="bcargar" type="submit" value="Cargar">
   <!--onclick="<?//=$onclick['cargar']?>-->
  </td>
  <td align="center">
   <input name="bcancelar" type="button" value="Cancelar" onclick="window.close()">
  </td>
 </tr>
</table>
</form>
</body>
</html>