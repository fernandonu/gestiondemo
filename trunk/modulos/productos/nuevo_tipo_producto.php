<?/*

----------------------------------------
 Autor: MAC
 Fecha: 21/06/2006
----------------------------------------

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.1 $
$Date: 2006/06/22 15:46:30 $

*/
include_once("../../config.php");
require_once("../../lib/funciones_arbol_xml.php");

?>
	<script>
	function agregar_select(id_sector,nbre_sector)
	{
		var select_tipo_prod=eval("window.opener.document.all.tipo_producto");

		//window.opener.document.all.telefono.value=nbre_sector;
		select_tipo_prod.length++;
	    select_tipo_prod.options[select_tipo_prod.length-1].text=nbre_sector;
	    select_tipo_prod.options[select_tipo_prod.length-1].value=id_sector;
		select_tipo_prod.selectedIndex=select_tipo_prod.length-1;

	}//de function agregar_select(id_sector,nbre_sector)


	function control_tipo()
	{
		if(document.all.nuevo_tipo.value.indexOf('"')>0)
		{
			alert("No se pueden ingresar comillas dobles");
			return false;
		}
		if(document.all.nuevo_tipo.value.indexOf('\'')>0)
		{
			alert("No se pueden ingresar comillas simples");
			return false;
		}

		return true;
	}//de function control_tipo()
</script>
<?

if($_POST["guardar"]=="Guardar")
{
	$db->StartTrans();
	$nombre_tipo_prod=$_POST["nuevo_tipo"];
	$fecha=date("Y-m-d H:i:s");

	try
	{
		$query="select id_tipo_prod,descripcion from general.tipos_prod	order by descripcion";
		$tipos=sql($query,"<br>Error al traer los tipos de productos<br>") or fin_pagina();

		$XMLtree=new Tree("../stock/tipo_prod.xml");
		//primero blanqueamos el arbol borrando todos los tipos de productos
		while (!$tipos->EOF)
		{
			$XMLtree->BorrarItem($tipos->fields["id_tipo_prod"]);

		 	$tipos->MoveNext();
		}//de while(!$tipos->EOF)


		//insertamos el nuevo tipo de producto
		$query="select nextval('general.tipos_prod_id_tipo_prod_seq') as id_tipo_prod";
		$idtipo=sql($query,"<br>Error al traer la secuencia del tipo de producto<br>") or fin_pagina();
		$id_tipo_prod=$idtipo->fields["id_tipo_prod"];

		$query="insert into general.tipos_prod(id_tipo_prod,codigo,descripcion,vigente_desde)
				values ($id_tipo_prod,'$nombre_tipo_prod','$nombre_tipo_prod','$fecha')";
		sql($query,"<br>Error al insertar el tipo de producto<br>") or fin_pagina();

		//Y por ultimo generamos nuevamente el arbol XML de los tipos de productos
		$query="select id_tipo_prod,descripcion from general.tipos_prod	order by descripcion";
		$tipos=sql($query,"<br>Error al traer los tipos de productos<br>") or fin_pagina();

		while (!$tipos->EOF)
		{
			$id_tipo=$tipos->fields["id_tipo_prod"];
			$nombre_tipo=$tipos->fields["descripcion"];
			$XMLtree->AgregarItem("raiz",$id_tipo,$nombre_tipo);

		 	$tipos->MoveNext();
		}//de while(!$tipos->EOF)


		$XMLtree->save("../stock/tipo_prod.xml");

		$sin_error=true;

 	}
	catch (Exception $a)
	{
	  $sin_error=false;
	  fin_pagina();
	}
	$db->CompleteTrans();
	?>

	<script>
     var id_tipo=<?=$id_tipo_prod?>;
     var nbre_tipo='<?=$nombre_tipo_prod?>';
     agregar_select(id_tipo,nbre_tipo);
     window.close();
    </script>
	<?

}//de if($_POST["guardar"]=="Guardar")

echo $html_header;
?>

<form name="form1" action="nuevo_tipo_producto.php" method="POST">
	<table class="bordes" align="center" width="95%">
		<tr>
			<td id="mo">
				Nuevo Tipo de Producto
			</td>
		</tr>
		<tr>
			<td>
				<table align="center" width="100%">
					<tr>
						<td>
							<b>Nombre del Nuevo Tipo de Producto</b>
						</td>
						<td>
							<input type="text" size="60" name="nuevo_tipo">
						</td>
					</tr>
				</table>
				<br><hr>
			</td>
		</tr>
		<tr>
			<td align="center">
				<input type="submit" name="guardar" value="Guardar" onclick="return control_tipo()">&nbsp;
				<input type="button" name="cerrar"	value="Cerrar" onclick="window.close();">
			</td>
		</tr>
	</table>
</form>
<script>
 document.all.nuevo_tipo.focus();
</script>
<?fin_pagina()?>