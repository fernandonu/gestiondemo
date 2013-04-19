<?
/*
Autor: MAC
Fecha: 03-05-06

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.1 $
$Date: 2006/05/03 22:18:24 $
*/
require_once("../../config.php");
require_once("fns.php");

$nro_orden=$_POST["h_nro_orden"];

if($_POST["guardar"]=="Guardar")
{
	$db->StartTrans();
	$items=get_items_fin($nro_orden);
   unset($items['estado']);
   unset($items['cantidad']);
    guardar_recepciones_oc($nro_orden,$items);
	$db->CompleteTrans();

	$msg="<b> La Orden de Compra Nº $nro_orden se actualizó exitosamente";

	mail_recibe_productos($nro_orden,$items);
}//de if($_POST["guardar"]=="Guardar")

echo $html_header;
if($msg!="")
	echo "<div align='center'><b>$msg</b></div>";
?>
<script>
	function controles()
	{
	   return control_recib_pedido();
	}//de function controles()
</script>
<form name="form1" action="recepciones_rapidas.php" method="POST">
<table class="bordes" align="center" width="100%">
 <tr>
  <td align="center">
   <font size="2"><b>Ingrese el Nº de Orden de Compra a Recibir</b>&nbsp;</font>
   <input type="text" name="nro_orden" value="<?=$nro_orden?>" size="8" onchange="control_numero(this,'Nº de Orden de Compra')">&nbsp;
   <input type="hidden" name="h_nro_orden" value="<?=$nro_orden?>">
   <input type="submit" name="traer_datos" value="Traer Datos" onclick="document.all.h_nro_orden.value=document.all.nro_orden.value">
   <hr>
  </td>
 </tr>
 <?
 if($nro_orden)
 {
	//revisamos si la OC existe y es Orden de Compra (y no Orden de Pago).
	$query="select orden_de_compra.nro_orden,orden_de_compra.estado,orden_de_compra.id_proveedor,
			proveedor.razon_social as nombre_proveedor
	        from compras.orden_de_compra
	        join general.proveedor using(id_proveedor)
	        where nro_orden=$nro_orden and (ord_pago isnull or ord_pago <> 'si')";
	$existe_oc=sql($query,"<br>Error al revisar si la OC existe en el sistema<br>") or fin_pagina();

	//Si no se encontro la OC o no es una Orden de Compra, mostramos el cartel avisando
	if($existe_oc->RecordCount()==0)
	{?>
		<tr>
		 <td align="center">
		  <font size="3" color="Red"><b>La Orden de Compra Nº <?=$nro_orden?> no está cargada en el sistema</b></font>
		 </td>
		</tr>
	 <?
	}
	else //si existe, mostramos la recepcion de productos solo si el estado es Enviada (e) o Parcial (d) o Totalmente pagada (g)
	{
		$estado=$existe_oc->fields["estado"];
		$nombre_proveedor=$existe_oc->fields["nombre_proveedor"];
		?>
		<tr>
		 <td>
		  <table width="100%" class="bordes" id="ma_sf">
		   <tr>
			 <td width="30%">
			  <font size="2">Orden de Compra Nº </font><font size="2" color="Blue"><a href="<?=encode_link("ord_compra.php",array("nro_orden"=>$nro_orden))?>"><?=$nro_orden?></a></font>
			 </td>
			 <td width="70%" align="right">
			  <font size="2">Proveedor </font><font size="2" color="Blue"><?=$nombre_proveedor?></font>
			 </td>
	       </tr>
		  </table>
		 </td>
		</tr>
		<?
		//si el estado no es Enviada, Parcialmente Pagada o Totalmente Pagada, mostramos cartel porque no se puede entregar para
		//Ordenes de Compra en otro estado que no sea ese
		if($estado!='e' && $estado!="d" && $estado!="g")
		{?>
			<tr>
			 <td align="center">
			  <font size="3" color="Blue">
			    <b>
			       La Orden de Compra Nº <?=$nro_orden?> no está Enviada, Parcial o Totalmente Pagada.<br>
			       No se pueden recibir productos para Ordenes de Compra que no están en este estado
			    </b>
			   </font>
			 </td>
			</tr>
		 <?
		}//de if($estado!='e' && $estado!="d" && $estado!="g")
		else
		{
			?>
			<tr id="mo">
			 <td>
			  <font size="2">Recepción de Productos</font>
			 </td>
			</tr>
			<tr>
			 <td>
		     	<?
				//averiguamos la fecha de recepcion de la OC, para ver que archivo mostramos
				$query="select fecha from log_ordenes where nro_orden=$nro_orden and tipo_log='de recepcion'";
				$fff=sql($query,"<br>Error al traer la fecha de recepcion de la OC<br>") or fin_pagina();
				$fecha_recepcion=$fff->fields["fecha"];
				$fecha_subida_gestion3="2006-01-04 00:00:00";

				//traemos las cantidades recibidas y entregadas de cada fila de la OC
				$filas_rec_ent=cant_rec_ent_por_fila($nro_orden);

				if($fecha_recepcion!="" && $fecha_recepcion<$fecha_subida_gestion3)
				 $filas_cambios_prod=filas_con_cambios_prod($nro_orden);

				//traemos los depositos de tipo stock
				$q="select id_deposito,nombre from depositos where tipo=0 order by nombre";
				$datos_depositos=sql($q,"<br>Error al traer los depositos de tipo stock<br>") or fin_pagina();


				generar_form_recepcion($nro_orden,1);
				?>
			 </td>
			</tr>
			<tr>
			 <td align="center">
			  <input type="submit" name="guardar" value="Guardar" onClick="if (controles())
			                                                               { <?/*if (!$es_stock && $re->RecordCount()==0)
			                                                                    echo "window.open('$link_calif','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=230,top=80,width=500,height=400');";
			                                                                    */
			                                                                 ?>
			                                                                 return true;
			                                                               }
			                                                               else
			                                                                return false;
			                                                             "
			  >
			 </td>
			</tr>
			<?
		}//del else de if($estado!='e' && $estado!="d" && $estado!="g")
	}//del else de if($existe_oc->RecordCount()==0)
 }//de if($nro_orden)

 //para la parte de calificar proveedores
 /*$link_calif=encode_link("../calidad/califique_proveedor.php",array("proveedor"=>"$id_proveedor","desde"=>"2"));
 $query="select * from general.calificacion_proveedor
         where fecha is not null and fecha>(current_date - 7)
         and id_proveedor=$id_proveedor";
 $re=sql($query,"<br>Error al traer informacion de la calificacion del proveedor<br>") or fin_pagina();*/
 ?>
</table>
</form>
<?
fin_pagina();
?>