<?
/*Autor: Gabriel
MODIFICADO POR:
$Author: fernando $
$Revision: 1.10 $
$Date: 2007/03/02 17:57:04 $
*/
require("../../config.php");
require_once("../general/funciones_contactos.php");


$ID=$parametros["ID"] or $ID=$_POST["id"];

if ($parametros["cmd1"]=="download"){
	$ID = $parametros["ID"];
	download_file($ID);
}

$db->starttrans();

if ($_POST["guardar"]){
	 
     if ($_POST["ch_tipo"]==1) $fecha=Fecha_db($_POST["t_fecha_cerrada"]);
               elseif($_POST["ch_tipo"]==2) $fecha=Fecha_db($_POST["t_fecha_abierta"]);
     if ($_POST["ch_tipo"]==1) $tipo="cerrada";
               elseif($_POST["ch_tipo"]==2) $tipo="abierta";
         
	$rta_consulta=sql("select * from licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento where id_licitacion=".$_POST["id"], "c11") or fin_pagina();
	if ($rta_consulta->recordCount()==1){
		$consulta="update licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento 
			set tipo_garantia='$tipo', 
			fecha_vencimiento_garantia='$fecha'
			 where id_licitacion=".$_POST["id"];
		sql($consulta, "c17: ".$consulta) or fin_pagina();
	}else {
		die("No se encontró una única entrada para la licitación id=".$_POST["id"]);
	}
}


if ($_POST["pasar_historial"]) {
		$sql_up="update licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento
 				set estado_garantia='r'	where id_licitacion=".$_POST["id"];
 		sql($sql_up, "c67") or fin_pagina();
        $link=encode_link("garantias_contrato.php",array());
        header("location:$link");
}

if ($_POST["pasar_pendiente"]) {
		$sql_up="update licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento
 				set estado_garantia='pr' where id_licitacion=".$_POST["id"];
 		sql($sql_up, "c67") or fin_pagina();
        $link=encode_link("garantias_contrato.php",array());
        header("location:$link");
}




if ($_POST["comentario_nuevo"]){	
	$sql = nuevo_comentario($ID,"GARANTIAS_CONTRATO",$_POST["comentario_nuevo"]);
	sql($sql) or fin_pagina();	
}

if ($ID){
	$rta_consulta=sql("select * from licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento where id_licitacion=".$ID, "c11") or fin_pagina();
	if ($rta_consulta->fields["tipo_garantia"]=="cerrada"){
		$fecha_garantia_cerrada=$rta_consulta->fields["fecha_vencimiento_garantia"];
	}elseif ($rta_consulta->fields["tipo_garantia"]=="abierta"){
		$fecha_garantia_abierta=$rta_consulta->fields["fecha_vencimiento_garantia"];
	}
	//$ID=$parametros["ID"];
}

$db->completetrans();
echo($html_header);
cargar_calendario();
?>
<script>
function controlar_datos(){

	return true;
}
</script>
<form name="form1" method="POST" action="garantias_contrato_poliza.php">
<input type="hidden" id="id" name="id" value="<?=$ID?>">

<? ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////?>
	<table align=center width=95% id='detalle_licitacion' width=100% border=0>
		<tr>
			<td>
				<table width=100% border=1 cellspacing=1 cellpadding=2 bgcolor='<?=$bgcolor_out?>' align=center >
					<tr>
						<td style="border:<?=$bgcolor3?>" colspan=2 align=center id=mo>
							<font size=3><b>Detalles de la Licitación</b>
						</td>
					</tr>
					<?
					$sql = "SELECT licitacion.*, impugnacion.*, garantia_contrato.*, garantia_de_oferta.*,
					        entidad.id_entidad as id_entidad, normas.nombre as nombre_normas, componentes_nueva_lic.nombre as nombre_componentes,
					        entidad.nombre as nombre_entidad,lider.nombre_lider,patrocinador.nombre_patrocinador, 
					        entidad.perfil,id_responsable_apertura,distrito.nombre as nombre_distrito, 
					        moneda.nombre as nombre_moneda,estado.nombre as nombre_estado,
					        estado.color as color_estado,tipo_entidad.nombre as tipo_entidad,candado.estado as candado, exp_lic_codificado,lgcv.estado_garantia  
					        FROM licitacion
					        LEFT JOIN entidad  USING (id_entidad) 
					        LEFT JOIN tipo_entidad 	USING (id_tipo_entidad) 
					        LEFT JOIN distrito USING (id_distrito) 
					        LEFT JOIN moneda   USING (id_moneda) 
					        LEFT JOIN estado   USING (id_estado) 
					        LEFT JOIN candado  USING (id_licitacion) 
							LEFT JOIN licitaciones_datos_adicionales.normas USING (id_normas) 
					        LEFT JOIN licitaciones_datos_adicionales.componentes_nueva_lic 	USING (id_componentes_nueva_lic) 
					        LEFT JOIN licitaciones_datos_adicionales.garantia_de_oferta USING (id_garantia_de_oferta) 
					        LEFT JOIN licitaciones_datos_adicionales.garantia_contrato	USING (id_garantia_contrato) 
					        LEFT JOIN licitaciones_datos_adicionales.impugnacion USING (id_impugnacion) 
					        LEFT join (
                                       select (apellido || text(', ') ||nombre ) as nombre_lider,id_usuario from sistema.usuarios
                                       ) as lider on (lider.id_usuario=licitacion.lider)
       		                LEFT JOIN (
                                      select (apellido || text(', ') ||nombre ) as nombre_patrocinador,id_usuario from sistema.usuarios
                                      ) as patrocinador on (patrocinador.id_usuario=licitacion.patrocinador)
                            LEFT JOIN licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento lgcv using(id_licitacion)                                      
			                WHERE licitacion.id_licitacion=$ID";
			          $result = sql($sql, "c95") or fin_pagina();
			          
            $estado_garantia=$result->fields["estado_garantia"];               
if ($result->RecordCount() == 1) {

            $lider=$result->fields["nombre_lider"];
            $patrocinador=$result->fields["nombre_patrocinador"];
            $ma = substr($result->fields["fecha_apertura"],5,2);
			$da = substr($result->fields["fecha_apertura"],8,2);
			$ya = substr($result->fields["fecha_apertura"],0,4);
			$ha = substr($result->fields["fecha_apertura"],11,5);
			?>
			<tr>
				<td  width=50% align=left valign=middle>
					<table width=100% border=0>
					<tr> 
					<td>
					<?
			   	    $link=encode_link('datos_apertura.php',array("id_licitacion"=>$result->fields['id_licitacion']));
					$id_responsable_apertura=$result->fields['id_responsable_apertura'];
					if ($id_responsable_apertura !="") {
						$sql_responsable="select nombre,apellido,interno,mail,movil
					     	              from responsables_apertura
					 		              join usuarios using(id_usuario)
					 		              where id_responsable_apertura=$id_responsable_apertura";
						$res_responsable=sql($sql_responsable) or fin_pagina();
			        $value="   En la\n apertura\n VER";
			        $estilo="style='width:65px';'height:50px'";
			        $interno=$res_responsable->fields['interno'];
			        $mail=$res_responsable->fields['mail'];
			        $celular=$res_responsable->fields['movil'];
			        $nombre=$res_responsable->fields['apellido']." ".$res_responsable->fields['nombre'];
			        $apertura="<td align='right' rowspan='3' width='25%'>  </td>
			           <td align='right' rowspan=3 width='50%'> <b>Responsable de Apertura:</b><br>$nombre <br> <b>Int: </b>$interno <br> <b> Movil: </b>$celular <br> <b>Mail: </b>$mail <br>
			           </td>";
					}
					if($result->fields['candado']!=0)
							echo "<img align=middle src=$html_root/imagenes/candado1.gif border=0 title='Esta licitacion solo puede verse, pero no modificarse'> ";
					echo "<font size=4><b>ID:</b> ".$result->fields["id_licitacion"]."</font><br></td>";
					echo $apertura;
					
					echo "</tr>";
					?>
					<tr>
						<td>
								<b>Lider:</b> <?=$lider?> <br><b>Patrocinador: </b><?=$patrocinador?>
                         <?
					      $inner.="<input type='button' name='mailto' value='Mail To ...' onclick='document.location.href=
									\"mailto:?subject=Mail%20licitación%20id%20".$ID."&body=Id:%20".$ID."%0D%0A\"+
									\"Entidad:%20".$result->fields["nombre_entidad"]."%0D%0A\"+
									\"Dirección:%20".$result->fields["dir_entidad"]."%0D%0A\"+
									\"Número:%20".$result->fields["nro_lic_codificado"]."%0D%0A\"+
									\"Expediente:%20".$result->fields["exp_lic_codificado"]."%0D%0A\"+
									\"Fecha%20de%20apertura:%20".Fecha(substr($result->fields["fecha_apertura"],0, 10))."%0D%0A\"+
									\"Hora%20de%20apertura:%20".substr($result->fields["fecha_apertura"], 11)."%0D%0A\"+
									\"Líder:%20".$result->fields["nombre_lider"]."%0D%0A\"+
									\"Patrocinador:%20".$result->fields["nombre_patrocinador"]."%0D%0A\"'>";
						echo $inner;      
						?>
								
            	</td>
              </tr>
			  </table>
			  </td>
			  <td  width=50% align=left>
				<b>Apertura: <font color='blue'><?echo $da."/".$ma."/".$ya;?></font></b><br>
				<b>Hora: <font color='blue'><?=$ha?></font></b><br>
				<b>Tipo Norma: 
				<? if ($result->fields['nombre_normas']!="") echo "<font color='blue'>".$result->fields['nombre_normas']."</font>"; 
				   elseif ($result->fields['iso9001']=="t") echo "<font color='blue'>Exige la Norma ISO9001</font>"; 
				   else echo "<font color='red'><b>?????</b></font>";
				?>
            <br><b>Se pueden cotizar Alternativas: </b>
            <? if ($result->fields['cotizar_alternativas']==1) echo "<font color='blue'><b>Si</b></font>"; 
            	elseif ($result->fields['cotizar_alternativas']==0) echo "<font color='blue'><b>No</b></font>"; 
            	else echo "<font color='red'><b>?????</b></font>";?>
            <br><b>Monitor, CPU, Teclado y Mouse: </b>
            <? if ($result->fields['nombre_componentes']!="") 
            		echo "<b><font color='blue'>".$result->fields['nombre_componentes']."</font></b>"; 
            	else echo "<font color='red'><b>?????</b></font>";?>
					</td>
				</tr>
				<tr>
					<td align=left colspan=1>
						<table width='100%'>
							<tr>
								<td valign='top'>
									<?=$id_entidad=$result->fields["id_entidad"]?>
									<input type=hidden name='id_entidad' value='<?=$id_entidad?>'>
									<br><br>
									<? if (permisos_check("inicio","boton_sinc_entidad")) $sinc_entidad="";
										else $sinc_entidad="disabled";
									?>
								</td>
								<td width='95%'>
									<b>Distrito:</b> <?=$result->fields["nombre_distrito"]?><br><?=$nombre_entidad=$result->fields["nombre_entidad"]?>
									<input type=hidden name='nombre_entidad' value='<?=$nombre_entidad?>'>
									<br><b>Entidad:</b> <?=$nombre_entidad?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<br><b>Dirección: </b> <?=html_out($result->fields["dir_entidad"])?>
									<br><b>Tipo de Entidad:</b> <?=((html_out($result->fields["tipo_entidad"])=="")?"<br><font color=red size=+1>Falta definir un tipo para la Entidad</font>":html_out($result->fields["tipo_entidad"]))?>
								</td>
							</tr>
						</table>
                  </td>
						<td align=left>
							<table width='100%' align='right' border=0>
								<tr align='right'>
									<td align='right'>
									</td>
								</tr>
								<tr align='right'>
									<td>
										<?=contactos_existentes("Licitaciones",$result->fields['id_entidad'])?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align=left>
							<b>Mantenimiento de oferta:</b> <?if ($result->fields["mantenimiento_oferta"]!="") echo "".$result->fields["mantenimiento_oferta"]." días ".$result->fields["mant_oferta_especial"]."\n"; else echo $result->fields["mant_oferta_especial"];?>
							<br><b>Forma de pago:</b> <?=$result->fields["forma_de_pago"]?>
							<br><b>Plazo de entrega</b>: <br>
							<?=$result->fields["plazo_entrega"]?>
							<br><b>Fecha de entrega</b>:
							<?
				   			if ($result->fields["fecha_entrega"] != "") {
									echo fecha($result->fields["fecha_entrega"])."\n";
			    			}else {
                	             echo "N/A\n";
                             }
               ?>
					</td>
					<td align=right valign=top>
						<b>Número:</b> <?=html_out($result->fields["nro_lic_codificado"])." <br>"?>
						<?
							if ($result->fields["exp_lic_codificado"]=="") $expediente=0;
							else $expediente=$result->fields["exp_lic_codificado"];
						?>
						<b>Expediente:</b> <?=html_out($expediente)?>
						<br><b>Valor del pliego:</b> $<?=formato_money($result->fields["valor_pliego"])?>
					</td>
				</tr>
				<tr>
					<td align=left valign=top>
            <b>Moneda:</b> <?=$result->fields["nombre_moneda"]?></b>
						<br><b>Ofertado:</b> <?=formato_money($result->fields["monto_ofertado"])?>
						<br><b>Estimado:</b> <?=formato_money($result->fields["monto_estimado"])?>
						<br><b>Ganado:</b> <?=formato_money($result->fields["monto_ganado"])?>
        	</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">
						<b>Archivos:</b><br>
						<?=g_lista_archivos_lic($ID)?>
					</td>
				</tr>
			</table>
		</tr>
	</table>
<? }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

	<input type="hidden" name="id" value="<?=(($_POST["id"])?$_POST["id"]:$parametros["ID"])?>">
	<table width="95%" align="center" class="bordes" bgcolor="<?=$bgcolor3?>">
		<tr>
			<td colspan="2" id="mo">
				DATOS DE LA POLIZA
			</td>
		</tr>
		<tr>
		  <td id="ma" width="15%">Estado Garantia:</td>
		  <td> <?=$estado_garantia;?> </td>
		</tr>
		
		<tr id=ma>
			<td>Tipo</td>
			<td>Fecha de vencimiento</td>
		</tr>
 <?
 if ($fecha_garantia_cerrada)  $ch__cerrada="checked";
                         else  $ch_cerrada="";
 ?>       
        
		<tr>
			<td align="left" width="10%">
				<input type="radio" name="ch_tipo" value="1" <?=$ch__cerrada?> > <b>Cerrada</b>
			</td>
			<td align=left>
			  <input type="text" name="t_fecha_cerrada" value="<?=fecha($fecha_garantia_cerrada)?>">&nbsp;<?=link_calendario("t_fecha_cerrada")?>
			</td>
		</tr>
 <?
 if ($fecha_garantia_abierta)  $ch_abierta="checked";
                         else $ch_abierta="";
 ?>       
		<tr>
			<td align="left" width="10%">
				<input type="radio" name="ch_tipo" value="2" <?=$ch_abierta?>> <b>Abierta</b>
			</td>
			<td align=left>
				<input type="text" name="t_fecha_abierta" value="<?=fecha($fecha_garantia_abierta)?>">&nbsp;<?=link_calendario("t_fecha_abierta")?>
				<b>(fecha en la que desea ser avisado para recuperar la garantía)</b>
				&nbsp;
			</td>
		</tr> 

	</table>	

   <table align=center width=95% id='detalle_licitacion' width=100% border=0>
      <tr>
        <td align="center">
        <?
        gestiones_comentarios($ID,"GARANTIAS_CONTRATO",1);
        ?>
        </td>
      </tr>
    </table>	
   <table align=center width=95% id='detalle_licitacion' width=100% border=0>    
		<tr>
			<td align="center">
				<input type="submit" name="guardar" value="Guardar cambios" onclick="return controlar_datos();">&nbsp;&nbsp;
                <?
                if ($estado_garantia=="np" || $estado_garantia=="pr"){ 
                ?>
                <input type="submit" name="pasar_historial" value="Pasar a Historial">
                <?
                }
                elseif ($estado_garantia=="r" || $estado_garantia=="") {
                ?>
                <input type="submit" name="pasar_pendiente" value="Volver a Pendientes">
                <?
                }
				?>
				<input type="button" name="volver" value="Volver" onclick="document.location='garantias_contrato.php';">
			</td>
		</tr>  
	</table>	
		          
</form>
<?
echo fin_pagina();

function g_lista_archivos_lic($ID){
	global $bgcolor3,$html_root;
  
	?>
	<table cellpadding=3 cellspacing=3 width=100%>
		<tr><td colspan=4 align=left></td></tr>
	<?
  $sql="select archivos.*,tipo_archivo_licitacion.tipo as tipo_archivo
		from archivos	
			left join tipo_archivo_licitacion using(id_tipo_archivo)
    where id_licitacion=$ID and tipo_archivo_licitacion.tipo ilike 'garantia de contrato' order by subidofecha DESC";
  $result1 = sql($sql) or fin_pagina();
	if ($result1->RecordCount() > 0) {
	?>
		<tr bgcolor=<?=$bgcolor3?>>
			<td align=left><b>Nombre</b></td>
			<td align=center><b>Tipo</b></td>
			<td align=center><b>Fecha de cargado</b></td>
			<td align=left><b>Cargado por</b></td>
		</tr>
	<?
		while (!$result1->EOF) {
			$mc = substr($result1->fields["subidofecha"],5,2);
			$dc = substr($result1->fields["subidofecha"],8,2);
			$yc = substr($result1->fields["subidofecha"],0,4);
			$hc = substr($result1->fields["subidofecha"],11,5);
			$imprimir = $result1->fields["imprimir"];
			if ($imprimir == "t") $color_imprimir = "#00cc00";
			else $color_imprimir = "#cc2222";
	?>
		<tr bgcolor=<?=$bgcolor3?>>
			<td width=40% align=left>
				<a title='Archivo: <?=$result1->fields["nombrecomp"]?><br>Tamaño: <?=number_format($result1->fields["tamañocomp"]/1024)?> Kb' href='<?=encode_link($_SERVER["PHP_SELF"],array("ID"=>$ID,"FileID"=>$result1->fields["idarchivo"],"cmd1"=>"download","Comp"=>1))?>'>
					<img align=middle src=<?=$html_root?>/imagenes/zip.gif border=0>
				</a>&nbsp;&nbsp;
				<a title='Archivo: <?=$result1->fields["nombre"]?><br>Tamaño: <?=number_format($result1->fields["tamaño"]/1024)?> Kb' href='<?=encode_link($_SERVER["PHP_SELF"],array("ID"=>$ID,"FileID"=>$result1->fields["idarchivo"],"cmd1"=>"download"))?>'><?=$result1->fields["nombre"]?></a>
			</td>
      <td width=10% align=center>
	<?
  	if ($result1->fields["tipo_archivo"]){
			$id_archivo=$result1->fields["idarchivo"];
      echo $result1->fields["tipo_archivo"];
    }
  ?>
	  	</td>
			<td width=15% align=center><?echo $dc."/".$mc."/".$yc." ".$hc." hs"?></td>
			<td width=25% align=left><?=$result1->fields["subidousuario"]?></td>
		</tr>
	<?
			$result1->MoveNext();
		}
	}else {
	?>
		<tr><td colspan=5 align=center><b>No hay archivos disponibles para esta licitación</b></td></tr>
	<?
	}
	?>
 	</table>
  <?
}
?>