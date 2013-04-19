<?php
/*AUTOR: MAD
               1 julio 2004
$Author: mari $
$Revision: 1.46 $
$Date: 2006/05/18 15:16:47 $
*/
/*
Este script sirve para:
- listar todas las ordenes de quemado
- agregar ordenes de quemado desde ordenes de produccion en estado Enviadas
- clasificar entre ordenes pendientes, en curso e historial.
- pasar de un estado a otro 
*/
require_once("../../config.php");

if (isset($parametros["msg"])) $msg = $parametros["msg"];
//parte de respuesta a una orden nueva
$fecha = date("Y-m-d",mktime());
if (isset($parametros["orden_prod"])) {
	phpss_svars_set("_ses_global_back", "");//vaciar el back
	phpss_svars_set("_ses_global_pag", "");//vaciar el page
	
	$db->StartTrans();
	$sql_isin = "select nro_orden from orden_quemado where nro_orden =".$parametros["orden_prod"];
	$sql_insert = "Insert into orden_quemado (nro_orden,fecha_orden,obs,id_config) 
	values (".$parametros["orden_prod"].",'$fecha','Agregada a pendientes... ',3)";

	$result = $db->Execute($sql_isin) or die($db->ErrorMsg()."<br>$sql_isin");
	if ($result->RecordCount() > 0)
		$msg= "<font color='red'>La orden de producción ya existe como orden de quemado, verifique las listas de ordenes de quemado</font>"; 
	else {	
		if ($db->Execute($sql_insert) or die($db->ErrorMsg()."<br>$sql_insert")){
		$msg = "<font color='green'>Se incluyo una nueva orden de quemado</font>";
		
		//parte de logs de eventos del sistema
		$sql_next ="Select nextval('ordenes.logs_quemado_id_log_seq') as id_log";
		$resultado=$db->Execute($sql_next) or die($db->ErrorMsg()."<br>".$sql_next);
		$id_log = $resultado->fields['id_log'];

		$sql_log = "insert into logs_quemado (id_log,fecha,nro_orden,usuario,tipo) values 
		(".$id_log.",'".date("Y-m-d h:i:s")."',".$parametros["orden_prod"].",'".$_ses_user["name"]."','Registro la nueva orden de quemado')";
		$db->Execute($sql_log) or die($db->ErrorMsg()."<br>$sql_log");
		//fin de parte de logs
		}		
		else
		$msg = "<font color='red'>Hubo un error en la inserción de la orden de quemado</font>";
	}
	$db->CompleteTrans();
}
//fin de respuesta a orden nueva

//parte de respuesta del borrado
if($_POST['boton_borrar']=="Quitar de pendientes")
{
 	        $i=0; $ok=true; $err_status = false;
 	        while ($i<$_POST['cant'])
            {
                	//chequeo de los log para ves si volvió de pendiente
               	
              if ($_POST['select_'.$i]!=""){
 				  $sql_log = "select * from logs_quemado where nro_orden = ".$_POST['select_'.$i]." and tipo = 'Retornó la orden a pendiente'";
				  $result_logs = $db->Execute($sql_log) or die($db->ErrorMsg()."<br>Error en $sql_log");
                  	
                  if ($result_logs->RecordCount()==0)
                  {
                  	$db->StartTrans();
                   $sql_delete="delete from orden_quemado where nro_orden=".$_POST['select_'.$i];
				   if(!$db->Execute($sql_delete))
                  		$ok = false;
                   else {
					//parte de logs de eventos del sistema
					$sql_next ="Select nextval('ordenes.logs_quemado_id_log_seq') as id_log";
					$resultado=$db->Execute($sql_next) or die($db->ErrorMsg()."<br>".$sql_next);
					$id_log = $resultado->fields['id_log'];

					$sql_log = "insert into logs_quemado (id_log,fecha,nro_orden,usuario,tipo) values 
					(".$id_log.",'".date("Y-m-d h:i:s")."',".$_POST['select_'.$i].",'".$_ses_user["name"]."','Elimino la orden de pendientes')";
					$db->Execute($sql_log) or die($db->ErrorMsg()."<br>$sql_log");
					//fin de parte de logs
                   }
                  $db->CompleteTrans();
                  } else $err_status=true;
              }
              $i++;
           }
           if ($ok)  
              	$msg="<font color='green'>Los items seleccionados se quitaron de pendientes</font>"; 
           else 
              	$msg="<font color='red'>Los items seleccionados no se quitaron correctamente</font>";  
                
           if ($err_status) {
               	$msg="<font color='red'>Algunos items no se eliminaron porque ya habían comenzado el proceso de quemado.</font>";  
           }
}
//fin del borrado

//parte de respuesta pasar a en curso
if($_POST['boton_activar']=="Comenzar P. de vida")
{
 	            $i=0;
 	            while ($i<$_POST['cant'])
                {
                  if ($_POST['select_'.$i]!="")
                  {
                  	//parte de ver si hay alguna orden del mismo ensamblador, si no activo esta
                  	$sql_activa = "select ensamblador.id_ensamblador from orden_quemado join orden_de_produccion using(nro_orden) join ensamblador using (id_ensamblador) where nro_orden = ".$_POST['select_'.$i];
					$resultado_activa = $db->Execute($sql_activa) or die($db->ErrorMsg()."<br>".$sql_activa);					
					
					$id_ensamblador = $resultado_activa->fields["id_ensamblador"];
					/*
                  	$sql_activa = "select nro_orden from orden_quemado join orden_de_produccion using(nro_orden) join ensamblador using (id_ensamblador) where orden_quemado.estado = 1 and ensamblador.id_ensamblador = ".$id_ensamblador;
					$resultado_activa = $db->Execute($sql_activa) or die($db->ErrorMsg()."<br>".$sql_activa);					
					
                  	if($resultado_activa->RecordCount() == 0){ */
                   		//pregunto si el esnsamblador tiene definido proceso dequemado
                  		$sql_ensambla = "select * from ensamblador_quemado where id_ensamblador = $id_ensamblador and activo <> 0";
						$resultado_ensambla = $db->Execute($sql_ensambla) or die($db->ErrorMsg()."<br>".$sql_ensambla);					
                  		
						if($resultado_ensambla->RecordCount() > 0){ 
						
							$db->StartTrans();
                   			//parte de actualizar estado
							$sql_update="update orden_quemado set estado = 1, sinc = 0, fecha_orden = '$fecha', obs = obs||'Se inicio la orden...' where nro_orden=".$_POST['select_'.$i];
				   			if(!$db->Execute($sql_update))
                 				$msg="<font color='red'>Hubo un error al pasar algún item como en curso</font>";  
                   			else {
								//parte de logs de eventos del sistema
								$sql_next ="Select nextval('ordenes.logs_quemado_id_log_seq') as id_log";
								$resultado=$db->Execute($sql_next) or die($db->ErrorMsg()."<br>".$sql_next);
								$id_log = $resultado->fields['id_log'];

								$sql_log = "insert into logs_quemado (id_log,fecha,nro_orden,usuario,tipo) values 
								(".$id_log.",'".date("Y-m-d h:i:s")."',".$_POST['select_'.$i].",'".$_ses_user["name"]."','Paso la orden a ordenes en curso')";
								$db->Execute($sql_log) or die($db->ErrorMsg()."<br>$sql_log");
								//fin de parte de logs
                 				$msg="<font color='green'>Los items seleccionados se colocaron como ordenes en curso</font>"; 							
                   			}
                   			$db->CompleteTrans();
						} else $msg="<font color='red'>Hubo un error al pasar algún item a ordenes en curso: El ensamblador no tiene proceso de quemado activo.</font>";
                  	/*} else  $msg="<font color='red'>Hubo un arror al pasar algún item a ordenes en curso: solo puede haber uno para un mismo ensamblador.</font>";  */
                  	}
                  	$i++;
                }
}
//fin de en curso

//estado de alerta
$sql_alerta = "select sinc from orden_quemado where sinc = 0";
$resultado_alerta = $db->Execute($sql_alerta) or die($db->ErrorMsg()."<br>".$sql_alerta);					
$alerta = 0;
if ($resultado_alerta->RecordCount() > 0){
	$alerta = 1;
}

//lista de servidores a sincronizar
	$sql_sincro = "Select ensamblador_quemado.*,ensamblador.nombre from ensamblador_quemado join ensamblador using(id_ensamblador) where activo = 1";
	$result_sincro = $db->Execute($sql_sincro) or die($db->ErrorMsg()."<br>$sql_sincro");
	
	echo "<SCRIPT>";
	?>
		function muestra_tabla(obj_tabla){
 			if (obj_tabla.style.display=='none') obj_tabla.style.display='inline';
			else obj_tabla.style.display='none';
		}
	<?

	echo "var servers = new Array();\n";
	$i=0;
	while (!$result_sincro->EOF) {
		echo "servers[$i] = ".$result_sincro->fields["id_entrada"]."\n";
		$result_sincro->MoveNext();
		$i++;
	}
	echo "</SCRIPT>\n";


/*echo "
<html>
  <head>
	<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>
    <script languaje='javascript' src='$html_root/lib/funciones.js'></script>
    <script language='javascript'>
		var winW=window.screen.Width;
		var valor=(winW*25)/100;
		var nombre1;
		var titulo1;
		function insertar() {
			ventana.document.all.titulo.innerText=titulo1;
			ventana.frames.frame1.location=nombre1;
		}
		function abrir_ventana(nombre,titulo) {
			var winH=window.screen.availHeight;
			nombre1=nombre;
			titulo1=titulo;
			if ((typeof(ventana) == 'undefined') || ventana.closed) {
				ventana=window.open('$html_root/modulos/ayuda/TITULOS.htm','ventana_ayuda','width=' + valor + ',height=' + (winH)+ ', left=' + (winW - valor ) +'  ,top=0, scrollbars=0 ');
				window.top.resizeBy(-valor,0);
			}
			else { ventana.focus(); }
			setTimeout('insertar()',400);
		}
	</script>
  </head>
  <body background='$html_root/imagenes/fondo.gif' bgcolor=\"$bgcolor3\" onload='document.focus();alerta_sinc($alerta);'>";*/

echo $html_header;

variables_form_busqueda("quemado");
	
if ($cmd == "") {
	$cmd="cur";
    phpss_svars_set("_ses_quemado_cmd", $cmd);
}

$datos_barra = array(
					array(
						"descripcion"	=> "Ordenes Pendientes",
						"cmd"			=> "pend",
						),
					array(
						"descripcion"	=> "Ordenes en Curso",
						"cmd"			=> "cur"
						),
				    array(
						"descripcion"	=> "Historial",
						"cmd"			=> "hist"
						)
				 );
echo "<br>";
generar_barra_nav($datos_barra);
?>
<script>
var contador=0;
//esta funcion sirve para habilitar el boton de borrar 
function habilitar_borrar(valor)
{
 if (valor.checked)
             contador++;
             else
             contador--;
 if (contador>=1){
         window.document.all.boton_borrar.disabled=0;
         window.document.all.boton_activar.disabled=0;
 		}
        else{
         window.document.all.boton_borrar.disabled=1;
         window.document.all.boton_activar.disabled=1;
        }
}//fin function
//funcion de alerta
var op = 1;
function alerta(){
	if (op == 1 ) {
		document.all.sinc.style.background = 'red';
		op = 0;
	} else {
		document.all.sinc.style.background = 'white';
		op = 1;
	}
	alerta_sinc(1);
}
function alerta_sinc(valor){
	if (valor == 1) {
		setTimeout("alerta()",500);
	}
}
</script>
<br>
<form name="form1" method="POST" action="listado_ordenes.php">
<?
$orden = array(
		"default" => "3",
		"default_up"=>"0",
		"1" => "orden_quemado.nro_orden",
		"2" => "orden_quemado.fecha_orden",
		"3" => "ensamblador.nombre",
		"4" => "orden_de_produccion.id_licitacion",
		"5" => "entidad.nombre",
		"6" => "orden_de_produccion.cantidad",
		"7" => "orden_de_produccion.desc_prod",
		"8" => "usuarios.iniciales"
	);

$filtro = array(
		"orden_quemado.nro_orden" => "Número Orden",
		"orden_quemado.fecha_orden" => "Fecha de Orden",
		"ensamblador.nombre" => "Ensamblador",
		"orden_de_produccion.id_licitacion" => "Licitación",
		"entidad.nombre" => "Cliente",
		"orden_de_produccion.cantidad" => "Cantidad de maquinas",
		"orden_de_produccion.desc_prod" => "Modelo de Maquina",
		"usuarios.iniciales" => "Iniciales Lider"
	);

/*$query="select nro_orden,fecha_orden,ensamblador.nombre as ens_nombre,
		orden_de_produccion.id_licitacion,entidad.nombre,
		orden_de_produccion.cantidad,orden_de_produccion.desc_prod as modelo,
		maq_quemadas,id_config,config_quemado.duracion
 		from ordenes.orden_quemado join ordenes.orden_de_produccion using (nro_orden) 
		join entidad using(id_entidad) 
		join ordenes.ensamblador using (id_ensamblador) 
		join renglon using(id_renglon) 
		join config_quemado using(id_config)";*/
 $query="select estado_bsas,nro_orden,fecha_orden,ensamblador.nombre as ens_nombre,
         orden_de_produccion.id_licitacion,entidad.nombre,
         orden_de_produccion.cantidad,orden_de_produccion.desc_prod as modelo,
         maq_quemadas,id_config,config_quemado.duracion,
         color as color_estado, estado.nombre as nombre_estado,usuarios.iniciales
         from ordenes.orden_quemado 
         join ordenes.orden_de_produccion using (nro_orden) 
         join ordenes.ensamblador using (id_ensamblador) 
		 join ordenes.config_quemado using(id_config)
         left join licitaciones.entidad using(id_entidad)
         left join licitaciones.licitacion using (id_licitacion)
         left join licitaciones.estado on licitacion.id_estado = estado.id_estado 
         left join licitaciones.renglon using(id_renglon)
         left join sistema.usuarios on usuarios.id_usuario=licitacion.lider";
	
//$query="select nro_orden,fecha_orden,ensamblador.nombre as ens_nombre,orden_de_produccion.id_licitacion,cliente_final.nombre,orden_de_produccion.cantidad,configuracion_maquina.modelo,maq_quemadas";
//$query= $query." from ordenes.orden_quemado join ordenes.orden_de_produccion using (nro_orden) join ordenes.ensamblador using (id_ensamblador) join ordenes.cliente_final using(id_cliente) join ordenes.configuracion_maquina using(id_configuracion)";
//$where="";

if ($cmd == "pend") {//para ordenes pendientes	
	$where="orden_quemado.estado = 0";
}

if ($cmd == "cur") {//para ordenes en curso	
	$where="orden_quemado.estado = 1";
}

if ($cmd == "hist") {//para el historial	
	$where="orden_quemado.estado = 2";
}

echo "<center>";

list($sql,$total_quemado,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar"); 

//$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error en form busqueda");
$result = sql($sql) or fin_pagina();

?>
&nbsp;&nbsp;
<input type=submit name=form_busqueda value='Buscar'>
<input type=button name=busqueda_avanzada value='B&uacute;squeda avanzada' 
	onclick="muestra_tabla(document.all.tabla_expandible); document.all.h_modo_tabla.value=document.all.tabla_expandible.style.display">
<br>
<?///////////////////////////////////////////////////////////////////////////////////////////////
$consulta="select mac, estado_bsas,nro_orden,fecha_orden,ensamblador.nombre as ens_nombre,
	  orden_de_produccion.id_licitacion,entidad.nombre,
  	orden_de_produccion.cantidad,orden_de_produccion.desc_prod as modelo,
	  maq_quemadas,id_config,config_quemado.duracion,
  	color as color_estado, estado.nombre as nombre_estado
  from ordenes.orden_quemado 
	  join ordenes.orden_de_produccion using (nro_orden) 
  	join ordenes.ensamblador using (id_ensamblador) 
		join ordenes.config_quemado using(id_config)
  	left join licitaciones.entidad using(id_entidad)
	  left join licitaciones.licitacion using (id_licitacion)
  	left join licitaciones.estado on licitacion.id_estado = estado.id_estado 
  	left join licitaciones.renglon using(id_renglon)
  	left join ordenes.reporteorden on(nro_orden=id_orden)
		left join ordenes.reportes using(id_reporte)
	where (mac ilike '%".$_POST["t_nro_mac"]."%')";

if ($cmd == "pend") {//para ordenes pendientes	
	$consulta.=" and (orden_quemado.estado = 0)";
}
if ($cmd == "cur") {//para ordenes en curso	
	$consulta.=" and (orden_quemado.estado = 1)";
}
if ($cmd == "hist") {//para el historial	
	$consulta.="and (orden_quemado.estado = 2)";
}
//////////////////////////////////////////////////////////////////////////////////////////
	if ($_POST["t_nro_mac"]){
		$rta_consulta=sql($consulta, "366: ") or fin_pagina();
	}
?>
<input type="hidden" name="h_modo_tabla" value="none">
<table border="1" id="tabla_expandible" style="display:<?=(($_POST["h_modo_tabla"])?$_POST["h_modo_tabla"]:'none')?>" width="95%" bgcolor="<?=$bgcolor3?>">
	<tr>
		<td align="center" id="mo">
			<b>B&uacute;squeda avanzada:</b>
		</td>
	</tr>
	<tr>
		<td nowrap align="center" id="ma">
			<b>Nro. MAC:</b>&nbsp;
			<input type="text" name="t_nro_mac" value="<?=$_POST["t_nro_mac"]?>">
			<input type="submit" name="b_buscar" value="Buscar prueba de vida">
		</td>
	</tr>
<?
	if ($rta_consulta){
?>
	<tr>
		<td>
			<table width="100%">
				<tr id="mo">
					<td>Op.</td>
					<td>Lic.</td>
					<td width="10%">Fecha de P. de V.</td>
					<td>M&aacute;quinas</td>
					<td>Modelo</td>
					<td>Ensamblador</td>
					<td>Cliente</td>
					<td>T. P. de V.</td>
					<td>Nro. Mac</td>
					<td>Grupo (Máquina)</td>
					<td></td>
				</tr>
<?
				$i=0;
				while (!$rta_consulta->EOF){
					$link = encode_link("ver_reportes.php",array("id"=>$rta_consulta->fields["nro_orden"], "cant"=>$datos["cantidad"]));//link para pagina nueva
					//estado de la orden
					if (($cmd == "pend") && ($rta_consulta->fields['maq_quemadas']>0)) $fondo_estado = "#FF0000";
					elseif ($cmd == "pend") {
						$sql_log = "select * from logs_quemado where nro_orden = ".$rta_consulta->fields['nro_orden']." and tipo = 'Retornó la orden a pendiente'";
						$rta_consulta_logs = $db->Execute($sql_log) or die($db->ErrorMsg()."<br>Error en $sql_log");
						if ($rta_consulta_logs->RecordCount()>0) $fondo_estado = "#FFFF00";
						else $fondo_estado = $fondo_id;
					} else $fondo_estado = $fondo_id;
					if ($rta_consulta->fields['estado_bsas']=='') {$bsas_color='red'; $bsas_title='Pendiente';}
					if ($rta_consulta->fields['estado_bsas']==1) {$bsas_color='yellow'; $bsas_title='En Producción';}
					if ($rta_consulta->fields['estado_bsas']==2) {$bsas_color='green'; $bsas_title='Historial';}
					/////////////////////////////////////////////////////////////////////////////////////////////////////////////
					$consulta2="select id_orden from ordenes.reporteorden join ordenes.reportes using (id_reporte) 
						where mac ilike '".$rta_consulta->fields["mac"]."' order by nro_serie,fecha";
					$rta_consulta2= sql($consulta2, "<br>419: ") or fin_pagina();
					$id=$rta_consulta2->fields["id_orden"];
					$consulta2="select * from ordenes.reporteorden join ordenes.reportes using(id_reporte) 
						where id_orden=".$id." order by nro_serie,fecha";
					$rta_consulta2 = sql($consulta2, "<br>435: ") or fin_pagina();

					$cant_grupos = $rta_consulta2->RecordCount();
					$nro_serie=$rta_consulta2->fields["nro_serie"];
					For($j=1, $nro_maq=1;$j<=$cant_grupos;$j++){
						if ($rta_consulta2->fields["nro_serie"]!=$nro_serie){
							$nro_maq++;
							$nro_serie=$rta_consulta2->fields["nro_serie"];
						}
						if ($rta_consulta->fields["mac"]==$rta_consulta2->fields["mac"]){
							$grupo_reporte=$j;
							$nro_maquina=$nro_maq;
						}
						$rta_consulta2->MoveNext();
					}
					/////////////////////////////////////////////////////////////////////////////////////////////////
				?>
				<tr bgcolor="#b7c7d0">
 					<td align="center" bgcolor="<?=$fondo_estado?>" title="Estado: <?=$bsas_title?>">
	  				<span style='background-color:<?=$bsas_color?>;color:<?=contraste($bsas_color,"#006699","#ffffff")?>'>
				  	<font color="Black"><?=$rta_consulta->fields['nro_orden']?></font></span>
    			</td>
 					<td align="center" title='Estado: <?=$rta_consulta->fields['nombre_estado']?>'>
 						<span style='background-color:<?=$rta_consulta->fields['color_estado']?>;color:<?=contraste($rta_consulta->fields['color_estado'],"#006699","#ffffff")?>'>
						<?=$rta_consulta->fields['id_licitacion']?></span>  	
    			</td>    
					<td align="center" nowrap>
  					<?=fecha($rta_consulta->fields['fecha_orden'])?>
    			</td>
					<td align="center">
  					<?=$rta_consulta->fields['cantidad']?>
    			</td>
					<td align="center">
  					<?=$rta_consulta->fields['modelo']?>
    			</td>
					<td align="center">
  					<?=$rta_consulta->fields['ens_nombre']?>
    			</td>
					<td align="center">
  					<?=$rta_consulta->fields['nombre']?>
    			</td>
					<td align="center">
						<?=$rta_consulta->fields["duracion"]?> horas
    			</td>
    			<td align="center" nowrap>
    				<?=$rta_consulta->fields["mac"]?>
    			</td>
    			<td bgcolor="<?=$background?>" align="center">
    				<?echo $grupo_reporte." (".$nro_maquina.")"?>
    			</td>
    			<td>
    				<input type="button" name="ver_<?=$i?>" value="ver" onclick="window.open('<?=$link?>','','left=40,top=10,width=700,height=470,resizable=1,scrollbars=1,status=1')">
    			</td>
    		</tr>
	<?
				$i++;
				$rta_consulta->MoveNext();
					echo("</td>");
					echo("</tr>");
				}
?>
			</table>
		</td>
	</tr>
<?
	}
?>
</table>
<br>

</center>
<?
if (isset($msg))
	echo "<BR><CENTER><B>$msg</B></CENTER>";
?>
<BR>
<table class="bordes" width="95%" align="center">
 <tr id=ma bgcolor=<?=$bgcolor3?>>
    <td align="left" <?if ($cmd == "pend"){?>colspan="3"<?} else{?> colspan="2"<?}?> >
     <b>Ordenes:</b> <?=$total_quemado?>.
    </td>
	<td align="right" colspan="3">
	 <?=$link_pagina?>
	</td>
	<td align="right" colspan="4"> 
	<?$link=encode_link("../ordprod/ordenes_ver.php",array("pag"=>"asociar",'back'=>"../ordquem/listado_ordenes.php"));
	if(!permisos_check("inicio","finalizar_quemado") ) $disabled = "Disabled";
	?>
	<INPUT type="button" name="config" value="Configurar" onclick="window.open('config_quemado.php','','left=60,top=50,width=400,height=300,resizable=1,status=1,scrollbars=1')" <?=$disabled?>>
	<INPUT type="button" name="sinc" value="Sincronizar" onclick= "Sincroniz()">
	<INPUT type="button" name="nuevo" value="Nueva Orden" onclick="document.location='<?=$link?>'">
	</td>
  </tr>

<tr id=mo>
<? if ($cmd == "pend"){?>
 <td width='5%'><b>Sel.</b></td>
 <?}?>
 <td width='5%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>OP.</a></b></td>
 <td width='5%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Lic.</a></b></td>
 <td width='5%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"8","up"=>$up))?>'>Lider</a></b></td>
 <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Fecha de Orden de Q.</a></b></td>
 <td width='3%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>PC.</a></b></td>
 <!--<td width='5%'><b><a id=mo href='<?//=encode_link($_SERVER["PHP_SELF"],array("sort"=>"8","up"=>$up))?>'>Quemadas</a></b></td>-->
 <td width='37%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","up"=>$up))?>'>Modelo</a></b></td>
 <td width='13%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Ensamblador</a></b></td>
 <td width='22%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Cliente</A></b></td>
 <td width='5%'><b>T. Prueba de vida</b></td>
</tr>

<?
$i = 0;
while(!$result->EOF)
{
	$link = encode_link("ver_orden.php",array("id"=>$result->fields["nro_orden"]));
	//estado de la orden
	if (($cmd == "pend") && ($result->fields['maq_quemadas']>0)) $fondo_estado = "#FF0000";
	elseif ($cmd == "pend") {
		$sql_log = "select * from logs_quemado where nro_orden = ".$result->fields['nro_orden']." and tipo = 'Retornó la orden a pendiente'";
		$result_logs = $db->Execute($sql_log) or die($db->ErrorMsg()."<br>Error en $sql_log");
		if ($result_logs->RecordCount()>0) $fondo_estado = "#FFFF00";
		else $fondo_estado = $fondo_id;
	} else $fondo_estado = $fondo_id;
	if ($result->fields['estado_bsas']=='') {$bsas_color='red'; $bsas_title='Pendiente';}
	if ($result->fields['estado_bsas']==1) {$bsas_color='yellow'; $bsas_title='En Producción';}
	if ($result->fields['estado_bsas']==2) {$bsas_color='green'; $bsas_title='Historial';}
	
	?>
	<tr <?=atrib_tr();?> >
	<? if ($cmd == "pend"){?>
	<td align="center" bgcolor="<?=$fondo_id?>">
    <input type="checkbox" name="select_<? echo $i; ?>" value="<? echo $result->fields['nro_orden']; ?>" onclick="habilitar_borrar(this)">
    </td> <?}?>
 	<td align="center" bgcolor="<?=$fondo_estado?>" onclick="document.location='<?=$link?>'" title="Estado: <?=$bsas_title?>">
  	<span style='background-color:<?=$bsas_color?>;color:<?=contraste($bsas_color,"#006699","#ffffff")?>'>
  	<font color="Black"><?=$result->fields['nro_orden']?></font></span>
    </td>
 	<td align="center" bgcolor="<?=$fondo_id?>" onclick="document.location='<?=$link?>'" title='Estado: <?=$result->fields['nombre_estado']?>'>
 	<span style='background-color:<?=$result->fields['color_estado']?>;color:<?=contraste($result->fields['color_estado'],"#006699","#ffffff")?>'>
	<?=$result->fields['id_licitacion']?></span>  	
    </td>    
    <td align="center" onclick="document.location='<?=$link?>'" title='Estado: <?=$result->fields['nombre_estado']?>'>
	<?=$result->fields['iniciales']?>	
    </td> 
	<td align="center" bgcolor="<?=$fondo_id?>" onclick="document.location='<?=$link?>'">
  	<?=fecha($result->fields['fecha_orden'])?>
    </td>
	<td align="center" bgcolor="<?=$fondo_id?>" onclick="document.location='<?=$link?>'">
  	<?=$result->fields['cantidad']?>
    </td>
	<!--<td align="center" bgcolor="<?//=$fondo_id?>" onclick="document.location='<?//=$link?>'">
  	<?//=$result->fields['maq_quemadas']?>
    </td>-->
	<td align="center" bgcolor="<?=$fondo_id?>" onclick="document.location='<?=$link?>'">
  	<?=$result->fields['modelo']?>
    </td>
	<td align="center" bgcolor="<?=$fondo_id?>" onclick="document.location='<?=$link?>'">
  	<?=$result->fields['ens_nombre']?>
    </td>
	<td align="center" bgcolor="<?=$fondo_id?>" onclick="document.location='<?=$link?>'">
  	<?=$result->fields['nombre'] 
  	/*if ($result->fields['id_licitacion'] == "") //caso en que no es licitacion
  		    $sql_aux = "select nombre from ordenes.orden_de_produccion join ordenes.cliente_final using (id_cliente) where nro_orden =".$result->fields['nro_orden'];
  		 else 
  			$sql_aux = "select entidad.nombre from licitaciones.licitacion join licitaciones.entidad using (id_entidad) where id_licitacion =".$result->fields['id_licitacion'];  		
		$res = $db->Execute($sql_aux);
		if ($res->RecordCount()>0)
			echo $res->fields["nombre"];
  		*/?>
    </td>
  <?
 	////////////////////////////////// GABRIEL //////////////////////////////////////////////
 	if (($cmd=="pend")||($cmd=="cur")){
		$consulta="select nro_serie, mac, resultado
		from ordenes.reportes 
			join ordenes.reporteorden  on ( reportes.id_reporte = reporteorden.id_reporte)
		where resultado=1 and reporteorden.id_orden =".$result->fields['nro_orden']." group by nro_serie, mac, resultado";
		$rta_consulta=sql($consulta, "c607 ") or fin_pagina();
		$gColorFondo=$result->fields['cantidad'] - $rta_consulta->recordCount();
 	}else $gColorFondo=1;
	/////////////////////////////////////////////////////////////////////////////////////////
	?>
	<td align="center" bgcolor="<?=(($gColorFondo<=0)?"green":$fondo_id)?>" >
	<?=$result->fields["duracion"]?> horas
    </td>
    </tr>
	<?
	$result->MoveNext();
	$i++;
}
if ($cmd == "pend"){
?>
 <tr bgcolor=<?=$bgcolor3?>>
    <td align="center" colspan="10">
	<input type="hidden" name="cant" value="<? echo $result->RecordCount(); ?>">
	<INPUT type="submit" name="boton_borrar" value="Quitar de pendientes" disabled>
	<INPUT type="submit" name="boton_activar" value="Comenzar P. de vida" disabled>
    </td>
  </tr>
<?} ?>
</table>
</FORM>
<?if ($cmd == "pend"){
?>
<BR>
<TABLE align="center" width="50%" bgcolor="White" cellspacing="5">
 <tr>
    <td align="center" colspan="2">
	<B>Referencia de colores</b>
    </td>
 </tr>
 <tr>
    <td align="center" width="5%" bgcolor="#FFFF00">
    </td>
    <td align="center">
    La orden se paso de en curso a pendiente
    </td>
 </tr>
 <tr>
    <td align="center" width="5%" bgcolor="#FF0000">
    </td>
    <td align="center">
    La orden retorno de en curso y ya se quemaron máquinas
    </td>
 </tr>
</TABLE>
<?} ?>


<script>
var cascada=50;
function Sincroniz()
{
	
	var valor = servers.length;
	while(valor > 0) {
			window.open('sincro_prueba.php?id='+servers[valor-1],'','left='+cascada+',top='+cascada+',width=400,height=250,resizable=1,status=1,scrollbars=1');		
   			cascada+=10;
		valor--;
	}
}
</script>

<?
if($_ses_user['login']=="fernando"|| $_ses_user['login']=="marco" || $_ses_user['login']=="norberto")
{
	?>
 		<input type="button" name="ocultar_2d" value="ocultar_2d" onclick="window.open('oculta_2d_error.php')">
	<?
}
echo("<br>");
?>
	<table bgcolor="White" align="center">
		<tr>
			<td colspan="2">
				<b>Referencia de color de la columna "T. de prueba de vida":</b>
			</td>
		</tr>
		<tr>
			<td width="10" headers="10" bgcolor="Green">
				&nbsp;
			</td>
			<td>
				Orden de producción en la cual todas las máquinas tienen al menos un reporte exitoso.
			</td>
		</tr>
	</table>
<?
echo "<br>".fin_pagina();
?>