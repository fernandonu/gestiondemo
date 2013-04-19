<?
/*
este archivo se llama stock_cambio_producto pero en realidad cambia el proveedor y no
el producto. Perdon pero no tenia ganas de renegar con los permisos.
Autor: Broggi
Creado: viernes 10/11/04

MODIFICADA POR
$Author: enrique $
$Revision: 1.2 $
$Date: 2006/02/20 16:41:43 $
*/

require_once("../../config.php");



//$onclick['cargar']=$parametros['onclickcargar'] or $onclick['cargar']=$_POST['onclickcargar'];
//$onclick['cancelar']=$parametros['onclickcancelar'] or $onclick['cancelar']=$_POST['onclickcancelar'] or $onclick['cancelar']="window.close()";
$producto=$_GET['id_producto'] or $producto=$_POST['id_producto'];

//extract($_POST,EXTR_OVERWRITE);


echo $html_header;

if ($_POST['bcargar']=="Cargar") {
	  
	$oc=$_POST['oc'];      
	$selec="select nro_orden from compras.orden_de_compra where nro_orden=$oc";
	$sel=sql($selec,"No se pudo recuperar la orden de compra") or fin_pagina();
	if($sel->RecordCount()==0)
	{
     ?>       
     <script>
     alert("La orden de compra no existe");
     </script>
    <?
	}
	else 
	{	
    ?>
    
     <input name="oc" type="hidden" value="<?=$oc?>">       
     <script>window.opener.cargar_3();
     window.close();
     </script>
   <?
	} 
   }	 

?>

<html>
</body>
<form name='stock_buscar_codigo' action="stock_buscar_codigo.php" method="POST">
<table align="center" class="bordes">
 <tr>
  <td align="center">
   <font size="2"><b>Ingresar OC</b></font>
  </td>
 </tr> 
 <tr>
  <td align="center"> 
  <input name="oc" value="" type="text">
  </td>
  </tr>
 <tr>
  <td align="center" colspan="2">
   <input name="bcargar" type="submit" value="Cargar">
   <!--onclick="<?//=$onclick['cargar']?>-->
  </td>
 </tr>
</table>
</form>
</body>
</html>