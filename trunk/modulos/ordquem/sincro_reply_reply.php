<?php
/*AUTOR: MAD
               1 julio 2004
$Author: marcelo $
$Revision: 1.6 $
$Date: 2004/08/09 22:02:16 $
*/
/*
Script que da respuesta a la parte final de la sincronización de datos
- primero pone el ok en los datos que fueron llevados hacia el server de quemado
- segundo recibe los datos desde el server de quemado (reportes no sincronizados aun)
- Por ultimo hace un submit con el la respuesta de Ok hacia el server de quemado.  
*/
require_once("../../config.php");
echo $html_header;

if (!isset($_POST["retorno"])) {
echo "Error en parametro de entrada";
}


//=============== Parte de ok de sincronizacion ==========================
//descriptacion de los datos de retorno
$aux = str_replace("#",'"',$_POST["retorno"]);
$datos_in_retorno = unserialize($aux);

//si la sincronizacion hacia el server de quemado fue ok marco como sincronizado
if ($datos_in_retorno != '') {

	foreach ($datos_in_retorno as $aux) {
		$sql_update = "update orden_quemado set sinc = 1 where nro_orden = ".$aux;
		$db->Execute($sql_update) or die($db->ErrorMsg()."<br>".$sql_update);
	}

}

//================= Parte de recibir los datos ==============================
$err=0;

//reportes a sincronizar
$aux = str_replace("&",'"',$_POST["reportes"]);
$datos_in_reportes = unserialize($aux);

//detalle de reportes  a sincronizar
$aux = str_replace("#",'"',$_POST["detalle"]);
$datos_in_detalle = unserialize($aux);

//a que orden asocia
$aux = str_replace("#",'"',$_POST["orden"]);
$datos_in_orden = unserialize($aux);

//ordenes de quemado que fueron actualizadas
$aux = str_replace("#",'"',$_POST["ord_quemado"]);
$datos_in_ord_quemado = unserialize($aux);

if($datos_in_reportes == "") {
	echo "<center><h4>Transferencia de Datos terminada...</h4></center>";
	echo "<center><input type='button' value='Cerrar' onclick='window.close()'></center>";
	exit();
} 



	$i=0;
	$j=0;
	$k=0;
	foreach ($datos_in_reportes as $aux_reportes) {

		
		//control de concordancia
		$sql_concord = "select * from reportes where nro_serie = '".$aux_reportes["nro_serie"]."' and fecha = '".$aux_reportes["fecha"]."'";
		$res_concord = $db->Execute($sql_concord) or die($sql_concord);  
		$repite = 0;
		
		if ($res_concord->RecordCount() > 0) {
			$repite = 1;
			$id_reporte = $res_concord->fields["id_reporte"];
		}

		$db->StartTrans();
		//calcular siguiente serial 
		if(!$repite){	
			$sql_next ="Select nextval('ordenes.reportes_id_reporte_seq') as id_reporte";
			if(!($resultado = $db->Execute($sql_next))) $err=1;  
			$id_reporte = $resultado->fields['id_reporte'];
		
			//reportes a sincronizar
			$sql_insert = "insert into reportes (id_reporte,nro_serie,mac,resultado,disco,cpu,placabase,fecha)
			values (".$id_reporte.",'".$aux_reportes["nro_serie"]."','".$aux_reportes["mac"]."',".$aux_reportes
			["resultado"].",'".$aux_reportes["disco"]."','".$aux_reportes["cpu"]."','".$aux_reportes["placabase"]."','".$aux_reportes["fecha"]."')";

			if(!$db->Execute($sql_insert)) {
 				$err=1;
				echo $sql_insert;
			}
		} else {//por concordancia
			//reportes a sincronizar
			$sql_update = "update reportes set resultado = ".$aux_reportes["resultado"]." where id_reporte = $id_reporte";

			if(!$db->Execute($sql_update)) {
 				$err=1;
				echo $sql_insert;
			}
			
		} //fin concordancia
 		
		//detalle de reportes  a sincronizar

		if(!$repite){
			$cont = 0;
			while ($cont < $aux_reportes["cant_rep"]) {
				$sql_insert = "insert into reporte_detalle (id_reporte,num_rep,detalle)
				values (".$id_reporte.",".$datos_in_detalle[$j]["num_rep"].",'".$datos_in_detalle[$j]["detalle"]."')";

				if(!$db->Execute($sql_insert)) {
 	 				$err=1;
					echo $sql_insert;
				}
 				$j++;
 				$cont++;
			}	
		} else { //por concordancia
			$cont = 0;
			while ($cont < $aux_reportes["cant_rep"]) {
				
				//comprobacion de concordancia a nivel de detalle
				$sql_concord_detalle = "select * from reporte_detalle where id_reporte = $id_reporte and num_rep = ".$datos_in_detalle[$j]["num_rep"];
				$res_concord_detalle = $db->Execute($sql_concord_detalle) or die($sql_concord_detalle);  

				if($res_concord_detalle->RecordCount() == 0) { //es nuevo reporte			
					$sql_insert = "insert into reporte_detalle (id_reporte,num_rep,detalle)
					values (".$id_reporte.",".$datos_in_detalle[$j]["num_rep"].",'".$datos_in_detalle[$j]["detalle"]."')";

					if(!$db->Execute($sql_insert)) {
 	 					$err=1;
						echo $sql_insert;
					}
				}
 				$j++;
 				$cont++;
			}				
		}
 		

		//a que orden asocia
 		if(!$repite){
			$sql_insert = "insert into reporteorden (id_reporte,id_orden)
			values (".$id_reporte.",".$datos_in_orden[$k]["id_orden"].")";
 			if(!$db->Execute($sql_insert)) {
				echo $sql_insert;
 		 		$err=1;
 			}
 		}
 		
 		//orden tocada

 		$sql_update="update orden_quemado set fecha_ini_quemado = '".$datos_in_ord_quemado[$k]["fecha_ini_quemado"]."', fecha_fin_quemado = '".$datos_in_ord_quemado[$k]["fecha_fin_quemado"]."', maq_quemadas = ".$datos_in_ord_quemado[$k]["maq_quemadas"]." where nro_orden = ".$datos_in_ord_quemado[$k]["nro_orden"];
		if(!$db->Execute($sql_update)) {
			echo $sql_update;
       		$err=1;
 		}

 		$db->CompleteTrans();

 			
 		if ($err == 0) //codigos de retorno
 			$ret_rep[$i] = $aux_reportes["id_reporte"];
 		 		
 		$i++;
 		$k++;

	}	//fel foreach
?>
<FORM id="sincro" action="<?=$_POST["ret"]?>" method="POST">

<TABLE width="90%" align="center" class="bordes">
<TR id="mo">
	<TD align="center">
	<H3>Ensamblador: <?=$_POST["ensamblador"]?> </FONT>
	</TD>
</TR>
<TR>
	<TD>
	<B>
	<?
	if($err==1)
	echo "<font color='red'>Error completando la transferencia de datos desde el servidor de quemado. La operacion fue anulada...</font>";
 	else
	echo "<font color='green'>Los datos fueron sicronizados desde el servidor de quemado.</font>";
	?>
	</b>
	</TD>
</TR>
<TR id="mo">
	<TD>

	<INPUT type="hidden" name="retorno" value="<?=str_replace('"','#',serialize($ret_rep))?>">

	<INPUT type="button" name="cerrar" value="Cerrar" onclick="window.close()">
	<INPUT type="submit" name="finalizar" value="Finalizar" style= "font : 'bold'" tabindex="0">			
	</TD>
</TR>
<TR>
	<TD align="left">
	[Paso 3]
	</TD>
</TR>
</TABLE>
</FORM>