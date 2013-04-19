<?
/*
Autor: Quique
Creado: viernes 10/11/04

MODIFICADA POR
$Author: enrique $
$Revision: 1.2 $
$Date: 2006/02/21 18:38:05 $
*/

require_once("../../config.php");



//$onclick['cargar']=$parametros['onclickcargar'] or $onclick['cargar']=$_POST['onclickcargar'];
//$onclick['cancelar']=$parametros['onclickcancelar'] or $onclick['cancelar']=$_POST['onclickcancelar'] or $onclick['cancelar']="window.close()";
$producto=$_GET['id_producto'] or $producto=$_POST['id_producto'];

//extract($_POST,EXTR_OVERWRITE);


echo $html_header;

if ($_POST['bcargar']=="Cargar") {
	  
	$caso=$_POST['caso'];      
	$selec="select nrocaso from casos.casos_cdr where nrocaso='$caso'";
	$sel=sql($selec,"No se pudo recuperar el numero de caso") or fin_pagina();
	if($sel->RecordCount()==0)
	{
     ?>       
     <script>
     alert("El numero de caso no existe");
     </script>
    <?
	}
	else 
	{	
    ?>
    
     <input name="caso" type="hidden" value="<?=$caso?>">       
     <script>window.opener.cargar_5();
     window.close();
     </script>
   <?
	} 
   }	 

?>

<html>
</body>
<form name='stock_buscar_caso' action="stock_buscar_caso.php" method="POST">
<table align="center" class="bordes">
 <tr>
  <td align="center">
   <font size="2"><b>Ingresar N° de Caso</b></font>
  </td>
 </tr> 
 <tr>
  <td align="center"> 
  <input name="caso" value="" type="text">
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