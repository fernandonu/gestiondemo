<?

/*AUTOR: MAC 
  FECHA: 10/06/04 

$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2004/07/01 17:58:28 $

ESTE ARCHIVO SE UTILIZA DESDE EL MODULO MUESTRAS PERO SE UBICA EN EL DIRECTORIO
DE STOCK PARA QUE FUNCIONE BIEN LA INCLUSION DE LOS ARCHIVOS DE STOCK

*/

require_once("../../config.php");

$pagina_oc=$parametros['pagina_oc'] or $pagina_oc=$_POST['pagina_oc'];
$onclickcargar=$parametros['onclickcargar'];
$onclicksalir=$parametros['onclicksalir'];

$post_deposito=$_POST['id_dep_ext'] or $post_deposito=$_POST['select_stock'];

if($parametros['stock_selec'])
{$post_deposito=$parametros['stock_selec'];
 $_POST['boton_ir']="Seleccionar Productos";
}

echo $html_header;

if($post_deposito !="")
{

 switch ($post_deposito)	
 {case "Buenos Aires":$pagina_include="stock_buenos_aires.php";break;
  case "San Luis":$pagina_include="stock_san_luis.php";break;
  case "ANECTIS":$pagina_include="stock_anectis.php";break;
  case "SICSA":$pagina_include="stock_sicsa.php";break;
  case "New Tree":$pagina_include="stock_new_tree.php";break;
 }	
}	


//traemos los depositos (de tipo stock) disponibles de la empresa
$query="select nombre from depositos where tipo=0 order by nombre";
$depositos=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer los depositos");

$link=encode_link('selec_prod_muestra.php',array('onclickcargar'=>"window.opener.cargar();",'onclicksalir'=>'window.close()','cambiar'=>0,'pagina_volver_muestra'=>1))
?>
<form method="POST" name="form_muestra" action="<?=$link?>">
<input type="hidden" name="pagina_oc" value="<?=$pagina_oc?>">
<input type="hidden" name="onclickcargar" value="<?=$onclickcargar?>">
<input type="hidden" name="onclicksalir" value="<?=$onclicksalir?>">
<table border="1" align="center" width="95%">
<tr>
<td>
<table width="100%" align="center">
 <tr id="mo">
  <td colspan="2">
   <b>Seleccione el stock del cual desea insertar los productos</b>
  </td>
 </tr>
 <tr>
  <td align="center">
   <select name="select_stock" onchange="if(this.value==-1) document.all.boton_ir.disabled=1;else document.all.boton_ir.disabled=0">
    <option value=-1>Seleccione el Stock</option> 
   <?

    while(!$depositos->EOF)
    {?>
     <option <?if($post_deposito==$depositos->fields['nombre'])echo "selected"?>><?=$depositos->fields['nombre']?></option>
     <?
     $depositos->MoveNext();
    }	 
   ?>
   </select>
  </td>
  <td align="center">
   <input type="submit" name="boton_ir" disabled value="Seleccionar Productos">
  </td>
 </tr>
</table> 
</td>
</tr>
</table>
</form>
<?

if($_POST['boton_ir']=="Seleccionar Productos" || $_POST['form_busqueda']== "Buscar")
{
 
 $_POST['pagina_oc']=1;
 $volver_a_muestra=1;
 include($pagina_include);
}
?> 


</html>