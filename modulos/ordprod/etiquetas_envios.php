<?
/*
Autor: elizabeth

MODIFICADA POR
$Author: enrique $
$Revision: 1.7 $
$Date: 2006/02/07 17:34:39 $
*/
require_once("../../config.php");
require("funciones.php");
//require("../../lib/barcode/barcode.php");
//require("../../lib/barcode/c128aobject.php");

//print_r($parametros);
$id_envio_renglones=$parametros['id_envio_renglones'] or $id_envio_renglones=$_POST['id_envio_renglones'];
$indice_elegido=$_POST['indice_elegido'] or $indice_elegido=$parametros['indice_elegido'];
$nuevo=$_POST['nuevo'] or $nuevo=$parametros['nuevo'];

if ($_POST['word_etiquetas']) {
	$sql="select envio_renglones.id_licitacion,codigo_renglon,deco_envio_origen,deco_envio_destino,entidad_mod,dir_entrega_mod,nro_lic_mod,cantidad_total,titulo_mod,bultos_ocupados from envio_renglones 
	left join renglones_bultos USING (id_envio_renglones)
	left join licitaciones.renglones_oc using (id_renglones_oc)
	left join licitaciones.renglon using (id_renglon)
	left join datos_envio USING(id_envio_renglones)  
	left join envio_origen USING(id_envio_origen)  
	left join envio_destino USING(id_envio_destino)  
	where id_envio_renglones=$id_envio_renglones";
	$datos_envio=sql($sql) or fin_pagina();
	if($nuevo==1)
	{
	$sql="select direccion from envio_renglones  
	left join lugar_entrega USING (id_lugar_entrega)
	where id_envio_renglones=$id_envio_renglones";
	$lugar_entrega=sql($sql) or fin_pagina();
	$lugar=$lugar_entrega->fields["direccion"];
	}
	else
	$lugar=$datos_envio->fields["dir_entrega_mod"] ;
	$lugar="Entrega: $lugar";
	$serial=$datos_envio->fields["deco_envio_origen"]."-".$datos_envio->fields["deco_envio_destino"]."-";
	$serial.=str_pad($id_envio_renglones,10,'0',STR_PAD_LEFT);
	$word=new Word_Envio();
	$word->encabesado();
	$cant_vacia=1;
	while ($cant_vacia<$indice_elegido) {
		$word->Agregar_celda_vacia();
		$cant_vacia++;
	}
	$nro_bulto=1;
	$bulto_renglon=1;
	$cantidad_bultos=$datos_envio->fields["cantidad_total"];
	while ($nro_bulto<=$cantidad_bultos) {
		//echo "$bulto_renglon - ".$datos_envio->fields["bultos_ocupados"];
		if ($bulto_renglon>$datos_envio->fields["bultos_ocupados"]) {
			$datos_envio->movenext();
			$bulto_renglon=1;
		}
		$word->Agregar_celda($datos_envio->fields["entidad_mod"],$lugar,$datos_envio->fields["id_licitacion"],$datos_envio->fields["codigo_renglon"],$datos_envio->fields["nro_lic_mod"],$cantidad_bultos,$nro_bulto,$serial);
		$nro_bulto++;
		$bulto_renglon++;
	}
	$word->Pie($serial);
	//echo "<pre>".$word->buffer."</pre>";
	$word->enviar("etiquetas.doc");
	/*while ($fila=$datos_envio->fetchrow()) {
		print_r($fila);
		echo "<br>";
	}
	fin_pagina();*/
}

echo $html_header;
?>
<script>
function indice(index){
   if (confirm("A partir de la Celda "+index+" comenzará la impresión de las Etiquetas")) {
       document.all.indice_elegido.value=index;
   }
}
</script>
<form action="" method="POST" name="form1">

<input type="hidden" name="indice_elegido" value="">
<input type="hidden" name="id_envio_renglones" value="<?=$id_envio_renglones?>">
<input type="hidden" name="nuevo" value="<?=$nuevo?>">

<table width="100%">
  <tr><td colspan="3" id="mo"><b><font size="3">Configurar Impresión de <br>las Etiquetas para el Envío</td></tr>
</table>
<br>
<table width="80%" height="60%" border="1" bordercolor="000000" cellpadding="0" cellspacing="0" bgcolor="ffffff"
title="Click sobre la Celda a partir de la cual se comenzará la impresión de las Etiquetas" align="center">
   <tr>
     <td align="center" onclick="indice(1)"><b>1</td>
     <td align="center" onclick="indice(2)"><b>2</td>
  </tr>
  <tr>
     <td align="center" onclick="indice(3)"><b>3</td>
	 <td align="center" onclick="indice(4)"><b>4</td>
  </tr>
</table>
<br>
<table width="100%" align="center">
  <tr>
     <td align="center"><input type="submit" name="word_etiquetas" value="Generar Word de Etiquetas para el Envío"></td>
  </tr>
</table>
</form>
</body>
</html>