<?
/*
$Author: marco_canderle $
$Revision: 1.21 $
$Date: 2006/02/28 15:49:38 $
*/
require_once("../../config.php");
include_once("funciones.php");

$id_prod_esp=$parametros["id_prod_esp"];
$usuario=$_ses_user["name"];
$pagina_listado=$_ses_pagina_listado;
$usuario=$_ses_user["name"];
$fecha=date("Y-m-d H:i:s",mktime());
$nuevo_precio=$_POST["nuevo_precio"];
//esta funcion obtiene el ultimo precio ingresado y
//modifica el precio de ese producto con todos los proveedores


 //modificaciones a la base del dato
if ($_POST["Aceptar"]=="Aceptar")
  {
   $db->StartTrans();//hace que se ejecute todo o nada
   $precio_stock=$_POST["precio_stock"];  //nuevo precio ingresado
   $precio_anterior=$_POST["precio_anterior"];  //precio anterior
   $id_prod_esp=$_POST["id_prod_esp"];  //id prod especifico

   if ($precio_stock!="") {
   	    //modifica el precio de un producto especifico y guarda log
        modif_precio($id_prod_esp,$precio_stock);
    }

    $db->CompleteTrans();//hace que se ejecute todo o nada

    echo "<center><b>El precio se actualizó con éxito</b></center>";
  }
 //fin de las modificaciones a la base de datos


echo $html_header;
if ($_POST["Aceptar"]){
	?>
		<script>
			window.opener.document.form1.submit();
		</script>
	<?
}

$sql="select id_prod_esp, id_producto, id_tipo_prod, marca, modelo, descripcion, observaciones, precio_stock, activo";
$sql.=" from producto_especifico ";
$sql.=" where id_prod_esp=$id_prod_esp";
$resultado=$db->execute($sql) or die ("c50 ".$sql);
$cantidad=$resultado->recordcount();
$precio_stock=$resultado->fields["precio_stock"];
?>
<script>
function habilitar(i){
var sentencia;
sentencia="document.all.precio_"+i+".disabled=!document.all.precio_"+i+".disabled";
eval(sentencia);
}


function controlar_datos()
{
	var cant;
	var campo;
	var valor;

	valor=0;
	campo=eval("document.all.precio_stock.value");

	if (campo=="" || campo<=0)
	{
	   alert("Debe ingresar un precio válido");
	   return false;
	}
	else
	   return true;
}//de function controlar_datos()
</script>

<BODY bgcolor="#E0E0E0">
<link rel=stylesheet type='text/css' href='<? echo "$html_root/lib/estilos.css"?>'>
<?
 $link=encode_link("stock_mod_precio.php",
       array("id_prod_esp"=>$id_prod_esp));
?>
<form name="modificar_precio" action="<?echo $link?>" method="POST">
<input type="hidden" name="cantidad" value="<?echo $cantidad;?>"> <!--paso la variable cantidad al post-->
<input type="hidden" name="id_prod_esp" value="<?=$id_prod_esp;?>">

<?
/***********************************************
 TABLA LOG
************************************************/
mostrar_log_cambio_precio($id_prod_esp);
?>


<table align="center" width="70%" border="1" cellspacing="0" bordercolor="#A3A3A3" cellpadding="0">
 <tr>
   <td colspan=3 id="mo">Modificar Precio</td>
 </tr>
 <tr bgcolor=<?=$bgcolor_out?>>
   <td colspan=3 align=center><font color=red><B>PRECIO FINAL EN U$S<B></font></td>
 </tr>
 <tr>
  <td colspan=3>
     <table width=100%>
      <tr bgcolor=<?=$bgcolor_out?>>
        <td id="ma_sf">Producto:</td>
        <td align=center><b><?=$resultado->fields["descripcion"]?></b></td>
      </tr>
      <tr bgcolor=<?=$bgcolor_out?>>
        <td id="ma_sf">Precio de Stock:</td>
        <td align=center><b> <font color=red> U$S <?=formato_money($precio_stock)?> </font> </b> </td>
      </tr>
      <input type='hidden' name="precio_anterior" value='<?=$precio_stock?>'>
      <tr bgcolor=<?=$bgcolor_out?>>
        <td id="ma_sf">Nuevo Precio de Stock:</td>
        <td align=center><input type=text name=precio_stock value="" onchange="control_numero(this,'Nuevo Precio de Stock')"></td>
      </tr>
     </table>
  </td>
  </tr>

<tr>
  <td colspan=3>
  <table width=100%>
  <tr bgcolor=<?=$bgcolor_out?>>
  <td width=33% align="center"><input name="Aceptar" type="submit" value="Aceptar"  style="width:90%" onclick="return controlar_datos();"></td>
  <td width=34% align="center" ><input name="Volver" type="button" value="Cerrar" style="width:90%" onclick="window.close();"></td>
  </tr>
  </table>


  </td>
</tr>

</table>

</form>
</body>
</html>