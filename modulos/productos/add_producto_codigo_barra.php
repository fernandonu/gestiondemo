<?php
/* MAD
$Author: marco_canderle $
$Revision: 1.5 $
$Date: 2006/01/04 07:58:05 $
*/

include("../../config.php");

/*
Atencion!!!!!!!!!!
puesto_servicio_tecnico es equivalente a RMA en este caso
*/
echo $html_header;

$msg = '';
//se asocia el producto al codigo de barras
if ($_POST["asociar"]=="Asociar")
{
	$cod_barra = $_POST["cod_barra"];
	$id_prod_esp = $_POST["id_prod_esp"];
	$producto = $_POST["producto"];
	$usuario = $_ses_user["name"];
	$fecha = date("Y-m-d H:i:s",mktime());
    if ($_POST["puesto_st"])
                         $puesto_servicio_tecnico=1;
                         else
                         $puesto_servicio_tecnico=0;
	$sql_check = "select count(*) from codigos_barra where codigo_barra = '$cod_barra'";
	$res = sql($sql_check,"<br>Error en la consulta por el código de barra<br>") or fin_pagina();
	if ($res->fields["count"] == 0) {
		$db->StartTrans();
		$sql="insert into codigos_barra (codigo_barra,id_prod_esp,codigo_padre,puesto_servicio_tecnico) values ('$cod_barra',$id_prod_esp,'$cod_barra',$puesto_servicio_tecnico)";
		sql($sql,"Error insertando el producto con codigo de barra") or fin_pagina();
		$sql_log = "insert into log_codigos_barra (codigo_barra,usuario,fecha,tipo) values ('$cod_barra','$usuario','$fecha','Ingresado manualmente')";
		sql($sql_log,"Error insertando el log") or fin_pagina();
        if ($puesto_servicio_tecnico){
          		$sql_log = "insert into log_codigos_barra (codigo_barra,usuario,fecha,tipo) values ('$cod_barra','$usuario','$fecha','Se agrego check de puesto de R.M.A.')";
   		        sql($sql_log,"Error insertando el log") or fin_pagina();
        }

		$msg = "<strong><font color='green'>El código de barras '$cod_barra' se ingresó correctamente</font></strong>";
		$db->CompleteTrans();
	} else $msg = "<strong><font color='red'>El código de barras que está ingresando ya estaba en el sistema.</font></strong>";
}//de if ($_POST["asociar"]=="Asociar")


?>
<SCRIPT>
function alProximoInput(elmnt,content)
{
  if (content.length==elmnt.maxLength){
	if (document.all.producto.value=='')
		traer_producto();
	document.all.asociar.focus();
  }
}
<?
 $onclick_cargar="
	window.opener.document.all.id_prod_esp.value=document.all.id_producto_seleccionado.value;
    window.opener.document.all.producto.value=document.all.nombre_producto_elegido.value;
    window.close();";
?>


function traer_producto(){

		pagina_prod="<?=encode_link('listado_productos_especificos.php',array("pagina_viene"=>"add_producto_codigo_barra.php","onclick_cargar"=>$onclick_cargar)) ?>"
    	wproductos=window.open(pagina_prod,'','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=25,top=10,width=750,height=300');


}

function control_submit(){
	var msg = 'Faltan completar los siguientes campos: \n';
	var ok = 1;
if (document.all.cod_barra.value==''){
	msg+='- Codigo de Barra del Producto. \n';
	ok = 0;
}
if (document.all.producto.value=='') {
	msg+='- Producto asociado a este codigo de barras. \n';
	ok = 0;
}


if (!ok) {
	alert(msg);
	return false;
}
return true;
}

</SCRIPT>

<CENTER><?=$msg?></CENTER>
<FORM id="form1" action="add_producto_codigo_barra.php" method="POST" onsubmit="return control_submit()">
<TABLE align="center" width="90%">
<TR id="mo">
<TD colspan="2">Nuevo Producto asociado a un Código de Barras</TD>
</TR>
<TR id="ma">
<TD>
Código de Barra:
</TD>
<TD align=left>
<input type="text" maxlength="9"  tabindex="1" name="cod_barra" size="28"   onkeyup="alProximoInput(this,this.value);" >
</TD>
</TR>
<TR id="ma">
<TD>Producto:</TD>
<TD align=left>
<input type="hidden" name="id_prod_esp" value="<?=$id_prod_esp?>">
<input type="text"  tabindex="2" name="producto" value="<?=$producto?>" size="80"  onkeypress="return false;" onfocus="document.all.get_producto.focus();"><INPUT type="button" value="..." name="get_producto" onclick="traer_producto();" title="Traer un producto para asociar.">
</TD>
<!--
<tr >
  <td colspan=2 id=ma_sf>
  <input type=checkbox name=puesto_st value=1>
  &nbsp;
  <b><font color=black>Este producto paso por el puesto de R.M.A.</font></b>
  </td>
</tr>
-->
</TR>
<TR id="mo">
<TD colspan="2"><INPUT type="submit" value="Asociar" name="asociar"></TD>
</TR>
<TR>
<TD colspan="2" align="right"><INPUT type="button" value="Cerrar" onclick="window.close();" style="font-size=9"></TD>
</TR>
</TABLE>
</FORM>
<SCRIPT>
document.all.cod_barra.focus();
</SCRIPT>