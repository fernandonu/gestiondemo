<?php
/*
Author: ferni 

modificada por
$Author: ferni $
$Revision: 1.30 $
$Date: 2006/07/20 15:22:40 $
*/
require_once("../../config.php");

$pagina_viene=$parametros["pagina_viene"];//viene de la pagina de casos
$onclick_cargar=$parametros["onclick_cargar"];//viene de la pagina de casos

if($_POST["eliminar_aux"]=="true"){ 
	$id_reparador_h=$_POST["id_reparador_h"];
	$nro_remito_h=$_POST["nro_remito_h"];
	$fecha=date("Y-m-d H:i:s");
	$usuario=$_ses_user['name'];
	$db->StartTrans(); //inicia la transaccion
	//traigo el nombre del reparador para insertar en el remito_interno
    $sql="select nombre_reparador,direccion from casos.reparadores where id_reparador=$id_reparador_h";
    $nombre_reparador_sql = sql($sql,"no se puede traer el nombre del reparador");
    $nombre_reparador= $nombre_reparador_sql->fields['nombre_reparador'];
    $direccion_reparador= $nombre_reparador_sql->fields['direccion'];
	//trae el id de remito_interno
	$q="select nextval('remito_interno_id_remito_seq') as id_remito_interno";
    $id_remito_interno=sql($q) or fin_pagina();
    $id_remito_interno=$id_remito_interno->fields['id_remito_interno'];
    //inserto en la tabla remito_interno
    $sql="insert into remito_interno.remito_interno (id_remito,fecha_remito,cliente,direccion,estado,id_licitacion,nro_orden,entrega)
    		values ($id_remito_interno,'$fecha','$nombre_reparador','$direccion_reparador','h',NULL,NULL,'Se envian Monitores al Reparador: $nombre_reparador con el Remito Número: $nro_remito_h')";
    sql($sql,"no se puede insertar en la tabla remito_interno");
    //inserto en el log de remito interno como creacion
    $sql="insert into remito_interno.log_remito_interno (usuario,fecha,tipo_log,id_remito)
    		values ('$usuario','$fecha','creacion',$id_remito_interno)";
    sql($sql,"no se puede insertar en la tabla log_remito_interno");
    //inserto en el log de remito interno como finalizacion
    $sql="insert into remito_interno.log_remito_interno (usuario,fecha,tipo_log,id_remito)
    		values ('$usuario','$fecha','finalizacion',$id_remito_interno)";
    sql($sql,"no se puede insertar en la tabla log_remito_interno");    
	for ($cont=0; $cont <= 50 ;$cont++){	
		if ($_POST["check_$cont"]){
			$id_muleto_h=$_POST["check_$cont"];
			//actualizo la tabla de muletos
			$sql="update casos.muletos set id_estado_muleto=3, flag_prueba_vida=0, id_reparador=$id_reparador_h, fecha_llegada_estado='$fecha' where id_muleto=$id_muleto_h";
			sql($sql,'no se puede actualizar la tabla muletos') or fin_pagina();
			//inserto en la tabla de reparaciones
			$usuario=$_ses_user['name'];
			$sql="insert into casos.reparaciones (id_reparador,id_muleto,fecha_rep,nro_remito) 
					values ($id_reparador_h,$id_muleto_h,'$fecha','$nro_remito_h')";
			sql($sql,'no se puede insertar en la tabla reparaciones') or fin_pagina();
			//inserto log en el log_muletos
			$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) 
					values ($id_muleto_h, '$usuario', '$fecha','Paso a En Reparacion',$id_reparador_h,NULL)";
			sql($log,'no se puede insertar log') or fin_pagina();
			//traigo el numero de serie de muleto para insertar en la tabla items_remito_interno
			$sql="select nro_serie from casos.muletos where id_muleto=$id_muleto_h";
			$numero_serie=sql($sql,'no se puede traer el numero de serie del muleto');
			$numero_serie=$numero_serie->fields ['nro_serie'];
			//inserto en la tabla items_remito_interno
    		$sql="insert into remito_interno.items_remito_interno (cant_prod,descripcion,id_remito,id_producto) 
    				values ('1','Monitor con numero de Serie: $numero_serie',$id_remito_interno,NULL)";
    		sql($sql,"no se puede insertar los items en tabla items_remito_interno");
		}
	}
	$db->CompleteTrans();//completa transaccion
	echo "<b><center><font color='red' size='3'>Los Monitores Seleccionados se Pasaron a En Reparación con el Remito Interno con ID $id_remito_interno</font></center></b>";
}

if($parametros['accion']!="") $accion=$parametros['accion'];

variables_form_busqueda("listado_muletos");

$fecha_hoy=date("Y-m-d H:i:s");
$fecha_hoy=fecha($fecha_hoy);
if($parametros['estado_muleto_de_caso']=="1") $cmd="disponibles"; // si viene de la pagina de casos

if ($cmd == "")  $cmd="a_reparar";

if ($cmd!="todos"){
$orden = array(
        "default" => "5",
        "default_up"=>"0",
        "1" => "nro_serie",
        "2" => "marca",
        "3" => "modelo",
        "4" => "nrocaso",
        "5" => "id_muleto",
        "6" => "nombre_reparador",
        "7" => "precio_stock",
       );
$filtro = array(
		"id_muleto" => "ID Muleto",
        "nro_serie" => "Nº Serie",
        "marca" => "Marca",
        "modelo" => "Modelo",
        "nrocaso" => "Nº Caso",
        "muletos.observaciones" => "Observaciones",
       );
}
else{
$orden = array(
        "default" => "6",
        "default_up"=>"0",
        "1" => "nro_serie",
        "2" => "marca",
        "3" => "modelo",
        "4" => "estado_muleto",
        "5" => "nrocaso",
        "6" => "id_muleto",
       );
$filtro = array(
		"id_muleto" => "ID Muleto",
        "nro_serie" => "Nº Serie",
        "marca" => "Marca",
        "modelo" => "Modelo",
        "nrocaso" => "Nº Caso",
        "estado_muleto" => "Estado del Muleto",
        "muletos.observaciones" => "Observaciones",
       );
}//de else if ($cmd!="todos")

$datos_barra = array(
     array(
        "descripcion"=> "Disponibles",
        "cmd"        => "disponibles"
     ),
     array(
        "descripcion"=> "En Uso",
        "cmd"        => "en_uso"
     ),
     array(
        "descripcion"=> "A Reparar",
        "cmd"        => "a_reparar"
     ),
     array(
        "descripcion"=> "En Reparación",
        "cmd"        => "en_reparacion"
     ),
     array(
        "descripcion"=> "Reparados",
        "cmd"        => "reparados"
     ),
     array(
        "descripcion"=> "Historial",
        "cmd"        => "historial",
     ),
     
     array(
        "descripcion"=> "Todos",
        "cmd"        => "todos"
     ),
);

generar_barra_nav($datos_barra);

$sql_tmp=" select nro_serie,id_muleto, estado_muleto, marca, modelo, nro_serie, idcaso, nrocaso, flag_prueba_vida, muletos.observaciones
				  , muletos.id_estado_muleto, reparadores.nombre_reparador,fecha_llegada_estado,flag_monitor_cliente,precio_stock
			from casos.muletos 
			left join casos.estados_muletos using (id_estado_muleto)
			left join casos.casos_cdr using (idcaso)
			left join casos.reparadores using (id_reparador)
           	";


if ($cmd=="disponibles")
    $where_tmp=" (muletos.id_estado_muleto=1)";
    
if ($cmd=="en_uso")
    $where_tmp=" (muletos.id_estado_muleto=2 or muletos.id_estado_muleto=6)";
    
if ($cmd=="en_reparacion")
    $where_tmp=" (muletos.id_estado_muleto=3)";

if ($cmd=="a_reparar")
    $where_tmp=" (muletos.id_estado_muleto=4)";
    
if ($cmd=="historial")
    $where_tmp=" (muletos.id_estado_muleto=5)";

if ($cmd=="reparados")
    $where_tmp=" (muletos.id_estado_muleto=7)";

echo $html_header;
?>
<script>
	function control_pasar_en_reparacion(){
		var i=0;
		while (typeof(eval("document.all.check_"+i))!='undefined'){
			objeto=eval("document.all.check_"+i);
			if (objeto.checked){
				return true;
			}
			i++;
		}
		alert ('Debe Seleccionar Monitor');
		return false;
	}
</script>
<form name=form1 action="muletos_listado.php" method=POST>
<!--son tres hidden que sirven para pasar a estado en reparacion varios monitores-->
<input type="hidden" value="" name="nro_remito_h">
<input type="hidden" value="" name="id_reparador_h">
<input type="hidden" value="" name="eliminar_aux">
<?
 /*si se llamo a este listado para elegir un producto, ponemos
 los hiddens para pasar datos a la pagina que ha llamado al listado*/
 if($pagina_viene){//si viene de otra pagina para recuperar valores?>
  <input type="hidden" name="id_muleto_h" value="">
  <input type="hidden" name="observaciones_h" value="">
  <input type="hidden" name="marca_h" value="">
  <input type="hidden" name="modelo_h" value="">
  <input type="hidden" name="nro_serie_h" value="">
 <?}?>

<table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
     <tr>
      <td align=center>
		<?list($sql,$total_muletos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");?>
	    &nbsp;&nbsp;<input type=submit name="buscar" value='Buscar'>
	  </td>
      <td>
      	<?if (permisos_check("inicio","permiso_nuevo_muleto")){?>
        <input type='button' name="nuevo_muleto" value='Nuevo Muleto' onclick="document.location='muletos_admin.php'"> &nbsp;&nbsp;
        <?}?>
    	<input type='button' name="reparadores" value='Reparadores' onclick="window.open('editor_reparadores_muletos.php')">&nbsp;&nbsp;
    	<?if ($cmd=='a_reparar'){//muestro boton unicamente en estado a reparar?>
    	 <input type="button" value='Pasar a En Reparacion' name='en_reparacion' 
 			onclick="if (control_pasar_en_reparacion())
 						if (confirm('¿Está seguro que desea pasar los Monitores a En Reparación?'))
 							window.open('carga_reparador.php','','toolbar=1,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=10,width=500,height=200')"
 		  >
    	<?}?>
      </td>
     </tr>
</table>

<?$result = sql($sql) or die;
echo "<center><b><font size='2' color='red'>$accion</font></b></center>";

$sql="select sum (precio_stock) as total_monto
		from casos.muletos 
		where muletos.id_estado_muleto!=5";
$total_muletos_montos=sql($sql,'No se puede calcular el monto') or fin_pagina();
$total_muletos_montos=$total_muletos_montos->fields['total_monto'];
?>

<table border=0 width=100% cellspacing=2 cellpadding=2 bgcolor='<?=$bgcolor3?>' align=center>
  <tr>
  	<td colspan=9 align=left id=ma>
     <table width=100%>
      <tr id=ma>
       <td width=30% align=left><b>Total:</b> <?=$total_muletos?></td>
       <td width=30% align=left><font color="Black"><b>Monto Total: <?=number_format($total_muletos_montos,2,',','.')?></b></font></td>
       <td width=40% align=right><?=$link_pagina?></td>
      </tr>
    </table>
   </td>
  </tr>
  
<?if ($cmd!="todos"){?>
  <tr>
  	<?if ($cmd=="a_reparar"){?>
    <td align=right id=mo>&nbsp;</td>
    <?}?>
    <td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"5","up"=>$up))?>' >ID Muleto</a></td>
    <td align=right id=mo>Fecha Ing. Estado</td>
  	<td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"1","up"=>$up))?>' title="Nº de Muleto">Nº Serie</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"2","up"=>$up))?>'>Marca</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"3","up"=>$up))?>'>Modelo</a></td>
    <?if (($cmd=="en_uso")||($cmd=="a_reparar")){?>
    <td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"4","up"=>$up))?>'>Nro. Caso</a></td>
    <?}?>
    <?if ($cmd=="en_reparacion"){?>
    <td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"6","up"=>$up))?>'>Reparador</a></td>    
    <?}?>
    <td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"7","up"=>$up))?>'>Precio Stock</a></td>    
  </tr>
 <?$i=0;	
  while (!$result->EOF) {

  	$est_mul=$result->fields['estado_muleto'];
  	$id_muleto=$result->fields['id_muleto'];
  	
  if($pagina_viene==""){//si viene de otra pagina para recuperar valores
  	$ref = encode_link("muletos_admin.php",array("id_muleto"=>$id_muleto,"pagina"=>"muletos_listado.php","estado"=>$cmd));
    $onclick_elegir="location.href='$ref'";		
  }
  else{
  	$onclick_elegir="document.all.id_muleto_h.value='".$result->fields["id_muleto"]."';";
    $onclick_elegir.="document.all.observaciones_h.value='".ereg_replace("\r\n"," ",$result->fields["observaciones"])."';";
    $onclick_elegir.="document.all.marca_h.value='".$result->fields["marca"]."';";
    $onclick_elegir.="document.all.modelo_h.value='".$result->fields["modelo"]."';";
    $onclick_elegir.="document.all.nro_serie_h.value='".$result->fields["nro_serie"]."';";
    $onclick_elegir.=$onclick_cargar;
  }
  
  //pone color si esta en laboratorio
  if (($cmd=="reparados")&&($result->fields['flag_prueba_vida']==1)){
		$color_fondo="#FFCCCC";
  }
  //pone color si esta en uso (pendiente)
  elseif (($cmd=="en_uso")&&($result->fields['id_estado_muleto']==6)){
		$color_fondo="#FFFFCC";
  }
  elseif ($result->fields['flag_monitor_cliente']==1){
		$color_fondo_cliente="#99CC33";
  }
  else{
		$color_fondo="";
  }?>
  
    <tr <?=atrib_tr()?>>
     <?if ($cmd=="a_reparar"){//muetra check en estado a reparar unicamente?>
	     <td align="center">
	     	<input type="checkbox" name="check_<?=$i?>" value="<?=$result->fields['id_muleto']?>">
	     </td>
     <?}?>
     <td bgcolor='<?=$color_fondo?>' onclick="<?=$onclick_elegir?>"><b><?=$result->fields['id_muleto']?></td>
     <td align="center" bgcolor='<?=$color_fondo?>' onclick="<?=$onclick_elegir?>"><b><?=fecha($result->fields['fecha_llegada_estado']) . ' ' . Hora($result->fields['fecha_llegada_estado']);?></td>
	 <td align="center" <?=($result->fields['flag_monitor_cliente']==1)?"bgcolor='$color_fondo_cliente'":"bgcolor='$color_fondo'"?> onclick="<?=$onclick_elegir?>"><b><?=$result->fields['nro_serie'];?></td>
     <td bgcolor='<?=$color_fondo?>' onclick="<?=$onclick_elegir?>"><b><?=$result->fields['marca']?></td>
     <td bgcolor='<?=$color_fondo?>' onclick="<?=$onclick_elegir?>"><b><?=$result->fields['modelo'];?></td>
     <?if (($cmd=="en_uso")||($cmd=="a_reparar")){?>
     <td bgcolor='<?=$color_fondo?>' onclick="<?=$onclick_elegir?>"><b><?=$result->fields['nrocaso'];?></td>
     <?}?>
     <?if ($cmd=="en_reparacion"){?>
     <td bgcolor='<?=$color_fondo?>' onclick="<?=$onclick_elegir?>"><b><?=$result->fields['nombre_reparador'];?></td>
     <?}?>
     <td bgcolor='<?=$color_fondo?>' onclick="<?=$onclick_elegir?>"><b><?=number_format($result->fields['precio_stock'],'2',',','.');?></td>
    </tr>
	<?$result->MoveNext();
	$i++;
    }
}//de if ($cmd!="todos")
else{?>
  <tr>
  	<td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"6","up"=>$up))?>'>ID Muleto</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"1","up"=>$up))?>'>Nro. Serie</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"2","up"=>$up))?>'>Marca</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"3","up"=>$up))?>'>Modelo</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"4","up"=>$up))?>'>Estado</a></td>
    <td align=right id=mo>Fecha Ing. Estado</td>
    <td align=right id=mo><a id=mo href='<?=encode_link("muletos_listado.php",array("sort"=>"5","up"=>$up))?>'>Nro. Caso</a></td>
  </tr>
 <?while (!$result->EOF) {
  	$ncaso=$result->fields['nrocaso'];
  	$id_muleto=$result->fields['id_muleto'];
  	$id_reem_muleto=$result->fields['id_reem_muleto'];
    
  	$ref = encode_link("muletos_admin.php",array("id_muleto"=>$id_muleto,"id_reem_muleto"=>$id_reem_muleto, "pagina"=>"muletos_listado.php","estado"=>$est_mul));
    $onclick_elegir="location.href='$ref'";
    
    if ($result->fields['flag_monitor_cliente']==1){
		$color_fondo_cliente="#99CC33";
  	}?>
	<tr <?=atrib_tr()?> onclick="<?=$onclick_elegir?>">
		<td><b><?=$result->fields['id_muleto']?></td>
	    <td align="center" bgcolor="<?=$color_fondo_cliente?>"><b><?=$result->fields['nro_serie'];?></td>
	    <td><b><?=$result->fields['marca'];?></td>
	    <td><b><?=$result->fields['modelo'];?></td>
	    <td><b><?=$result->fields['estado_muleto'];?></td>
	    <td align="center" bgcolor='<?=$color_fondo?>'><b><?=fecha($result->fields['fecha_llegada_estado']) . ' ' . Hora($result->fields['fecha_llegada_estado']);?></td>
	    <td align="center"><b><?=$result->fields['nrocaso'];?></td>
   </tr>
   <?$result->MoveNext();
    }
}//del else que muestra el estado todos?>
</table>
<?//empieza Colores de Referencia?>
	<br>
	<table align='center' border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>
     <tr>
      <td colspan=10 bordercolor='#FFFFFF'><b>Colores de Referencia para la Columna Nº Serie:</b></td>
     <tr>
     <td width=30% bordercolor='#FFFFFF'>
      <table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%>
       <tr>
        <td width=15 bgcolor='#99CC33' bordercolor='#000000' height=15>&nbsp;</td>
        <td bordercolor='#FFFFFF'>Muleto del Cliente</td>
       </tr>
      </table>
     </td>
    </table>
    
	<?if ($cmd=="reparados"){?>
	<table align='center' border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>
     <tr>
      <td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia:</b></td>
     <tr>
     <td width=30% bordercolor='#FFFFFF'>
      <table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%>
       <tr>
        <td width=15 bgcolor='#FFCCCC' bordercolor='#000000' height=15>&nbsp;</td>
        <td bordercolor='#FFFFFF'>Muleto sin Prueba de Vida</td>
       </tr>
      </table>
     </td>
    </table>
	<?}
	if ($cmd=="en_uso"){?>
	<table align='center' border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>
     <tr>
      <td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia:</b></td>
     </tr>
     <tr>
      <td width=30% bordercolor='#FFFFFF'>
       <table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%>
        <tr>
         <td width=15 bgcolor='#FFFFCC' bordercolor='#000000' height=15>&nbsp;</td>
         <td bordercolor='#FFFFFF'>Muleto que esta PENDIENTE En Uso</td>
        </tr>
       </table>
      </td>
     </tr>
    </table>
	<?}?>
	<br>
<?=fin_pagina();// aca termino ?>