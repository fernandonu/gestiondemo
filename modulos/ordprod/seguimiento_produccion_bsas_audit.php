<?PHP
/*
Autor Gabriel (copiado y modificado a partir de seguimiento_produccion_bsas)

$Author: gabriel $
$Revision: 1.7 $
$Date: 2005/11/09 21:11:55 $
*/

require_once("../../config.php");
variables_form_busqueda("seguimiento_produccion_bsas_audit");
$datos_barra = array(
			array("descripcion"=> "Pendientes", "cmd"=> "pendientes"),
			array("descripcion"=> "Historial", "cmd"=> "historial")
	);
  if ($cmd == ""){
		$cmd="pendientes";
        phpss_svars_set("_ses_seg_ord_cmd", $cmd);
  }
  $orden = array(
  	"default" => "2",
    "default_up" => "0",
    "1" => "ordenes.orden_de_produccion.id_licitacion",
    "2" => "ordenes.orden_de_produccion.nro_orden",
    "4" => "licitaciones.entidad.nombre",
    "5" => "ordenes.orden_de_produccion.fecha_entrega",  
    "6" => "ordenes.orden_de_produccion.cantidad",  
  ); 
	$filtro = array(        
    "licitaciones.entidad.nombre" => "Entidad",                
    "ordenes.orden_de_produccion.id_licitacion" => "ID de licitación",        
		"ordenes.orden_de_produccion.nro_orden" => "Número de orden",		
  );     
	$sql_tmp="select entidad.nombre,id_entidad,fecha_entrega,cantidad,id_licitacion,orden_de_produccion.nro_orden,estado_bsas   
     from ordenes.orden_de_produccion left join licitaciones.entidad using(id_entidad)";

	if ($cmd=="pendientes") $where_tmp=" estado_audit='f'";
	if ($cmd=="historial") $where_tmp=" estado_audit='t'";
	$where_tmp.=" and estado != 'AN' ";
  $contar="buscar";
	if($_POST['keyword'] || $keyword) $contar="buscar";

	echo "<br>";
	echo $html_header; 
	generar_barra_nav($datos_barra);
	?>
 	<form name="produccion_bs_as" action='seguimiento_produccion_bsas_audit.php' method='post'>
		<table align="center" >
 			<tr>
			  <td>
					<?
 						list($sql,$total_lic,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar); 
 						$resul_consulta=sql($sql,"No se pudo realizar la consulta del form busqueda") or fin_pagina();
 					?>
  			</td>
  			<td>
   				<input type=submit name=form_busqueda value='Buscar'>
  			</td>
 			</tr>
		</table> 
		<br>
		<table width='100%' align="center" cellspacing="2" cellpadding="2" class="bordes">
 			<tr id=ma>
  			<td align="left" colspan="3">
   				<b>Total:</b> <?=$total_lic?> <b>Orden/es.</b>   
  			</td>
  			<td align="right" colspan="3">
   				<?=$link_pagina?>
  			</td>
 			</tr>
 			<tr id=mo>
  			<td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>ID</a></b></td>
  			<td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>OP</b></td>
  			<td width="50%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Entidad</b></td>
  			<td width="15%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Fecha Entrega</b></td>
  			<td width="15%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Cantidad PC</b></td>  
  			<td width="15%">&nbsp;</td>  
 			</tr>
 				<? 
    			while (!$resul_consulta->EOF){
    				$link=encode_link("ordenes_nueva_audit.php",array("nro_orden"=>$resul_consulta->fields['nro_orden'],"modo"=>"modificar","volver"=>"seguimiento_produccion_bsas","cmd"=>"$cmd"));
    				//////////////////////////////////////////////
    				$sql_audit='select * from auditorias where nro_orden='.$resul_consulta->fields['nro_orden'].' order by fecha_hora';
    				$result_audit=sql($sql_audit) or fin_pagina();
    				$result_audit->movelast();
    				if ($result_audit->fields['estado']=='f') $color_audit='#BB7777';
    				else $color_audit='#B7C7D0';
					////// Estado Produccion Bs As.
					if (!$resul_consulta->fields['estado_bsas']) $color_bsas="red";
					if ($resul_consulta->fields['estado_bsas']==1) $color_bsas="yellow";
					if ($resul_consulta->fields['estado_bsas']==2) $color_bsas="green";
					/////////////////////////////
    			//////////////////////////////////////////////
			  ?>
        <a href='<?=$link?>'>
					<tr <?=atrib_tr()?>>
	        	<td align="center" bgcolor="<?=$color_audit?>"><?=$resul_consulta->fields['id_licitacion']?></td>
  	        <td align="center" bgcolor="<?=$color_bsas?>"><?=$resul_consulta->fields['nro_orden']?></td>
    	      <td align="center" bgcolor="<?=$color_audit?>"><?=$resul_consulta->fields['nombre']?></td>
      	    <td align="center" bgcolor="<?=$color_audit?>"><?=fecha($resul_consulta->fields['fecha_entrega'])?></td>
        	  <td align="center" bgcolor="<?=$color_audit?>"><?=$resul_consulta->fields['cantidad']?></td>
        	  <td>
<?
	$q = "select nro_orden, licitaciones.unir_texto(nombre||', ') as archivos
		from ordenes.archivos_ordprod join general.subir_archivos on id_archivo=subir_archivos.id 
		where nro_orden=".$resul_consulta->fields['nro_orden']." group by nro_orden";
	$adjunto=sql($q, "c106") or fin_pagina();
	if ($adjunto->recordcount()>0) echo "<img id='imagen_1' src='../../imagenes/files1.gif' border=0 title='".substr($adjunto->fields["archivos"], 0, strlen($adjunto->fields["archivos"])-2)."' align='left'>";
	echo "&nbsp";
?>
						</td>
	        </tr>
        </a>
  			<?
        		$resul_consulta->MoveNext();
        	}       	
 				?>
		</table>
		<table width="40%" align="center" bgcolor="White" border="1" cellpadding="0" cellspacing="0">
			<tr>
				<td width="15" height="15" align='center' bgcolor='#BB7777'>
					<font color='#BB7777'>.</font>
				</td>
				<td>
					Reprob&oacute; la &uacute;ltima auditor&iacute;a.
				</td>
			</tr>
			<tr>
				<td width="15" height="15" align='center' bgcolor='#B7C7D0'>
					<font color='#B7C7D0'>.</font>
				</td>
				<td>
					Sin auditor&iacute;a hasta la fecha.
				</td>
			</tr>
			<tr>
				<td colspan=2 align=center>
					<b>Estado de la Producción en Bs. As. en la columna OP.</b>
				</td>
			</tr>
			<tr>
				<td width="15" height="15" align='center' bgcolor='red'>
					<font color='red'>.</font>
				</td>
				<td>
					Pendiente.
				</td>
			</tr>
			<tr>
				<td width="15" height="15" align='center' bgcolor='yellow'>
					<font color='yellow'>.</font>
				</td>
				<td>
					En Producción.
				</td>
			</tr>
			<tr>
				<td width="15" height="15" align='center' bgcolor='green'>
					<font color='green'>.</font>
				</td>
				<td>
					Historial.
				</td>
			</tr>
		</table>
	</form>
	<br>
<?=
fin_pagina(false);
?>
