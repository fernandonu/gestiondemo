<?php
/*
AUTOR: Gabriel
MODIFICADO POR:
$Author: ferni $
$Revision: 1.28 $
$Date: 2007/06/13 17:41:24 $
*/

	require("../../config.php");
	require("../personal/gutils.php");
	//////////////////////////////////////////////////////////////////////////////

	$valor_dolar=$parametros["valor_dolar"] or $valor_dolar=$_POST["valor_dolar"] or $valor_dolar=1;
	
	$cmd=$parametros["cmd"];
	if ($cmd=="download"){
    	$file=$parametros["file"];
    	$size=$parametros["size"];
    	Mostrar_Header($file,"application/octet-stream",$size);
    	$filefull = UPLOADS_DIR ."/venta_factura/". $file;
    	readfile($filefull);
    	exit();
	}
		
	if ($cmd=="borra_archivo"){
		$filename=$parametros["filename"];
		$id_archivo_venta_fact=$parametros["id_archivo_venta_fact"];
		
		$msg="El archivo '$filename' se eliminó correctamente";
    	if (is_file(UPLOADS_DIR."/venta_factura/$filename")){
        	unlink(UPLOADS_DIR."/venta_factura/$filename");
    	}         	
        else{
        	$msg="No se encontro el archivo '$filename'";
        }
         
        $sql="delete from bancos.archivo_venta_fact where id_archivo_venta_fact=$id_archivo_venta_fact;";    
    	sql($sql) or $error=$db->errormsg();
    	exit();  
	}
	
	echo $html_header;
	cargar_calendario();
	$var_id=array(
		"id_venta_factura"=>"",
		"id_factoring"=>"",
		"monto_prestamo"=>"",
		"ingresos_brutos"=>"",
		"ganancias"=>"",
		"multas"=>"",
		"comisiones"=>"",
		"intereses"=>"",
		"gastos_escribano"=>"",
		"gastos_varios"=>"",
		"gastos_varios_detalle"=>"",
		"fecha"=>"",
		"estado_venta"=>"1",
		"moneda"=>"",
		"comentario"=>"",
		"simbolo"=>"",
		"fecha_cierre"=>"",
		"usuario_cerrador"=>"",
		"modo"=>"modif"
	);
	variables_form_busqueda("facturas_venta_detalle", $var_id);
	$flag0=false;
	if ($_POST["chequeado"]=="si"){
		$estado_venta="-1";
		$fecha_cierre=date("Y-m-d H:i");
		$usuario_cerrador=$_ses_user["login"];
		$flag0=true;
	}
	if ((!$_POST["gastos_varios_detalle"])&&(!$parametros["gastos_varios_detalle"])){
		$_ses_facturas_venta_detalle["gastos_varios_detalle"]=$gastos_varios_detalle="";
		$flag=true;
	}
	
	if ((!$_POST["comentario"])&&(!$parametros["comentario"])){
		$comentario="";
		$flag=true;
	}
	if ($flag0) phpss_svars_set("_ses_facturas_venta_detalle", $_ses_facturas_venta_detalle);

	if ($modo=="nuevo"){
		$rta_consulta2=sql("select * from general.dolar_general", "c137") or fin_pagina();
		$valor_dolar=$rta_consulta2->fields["valor"];
	}
	$fecha_vencimiento=$parametros["fecha_vencimiento"] or $fecha_vencimiento=$_POST["fecha_vencimiento"];
	
	if($parametros['accion']!=""){ Aviso($parametros['accion']);}
	//////////////////////////////////////////////////////////////////////////////
	?>
	<script>
	function clearfields(){
		document.form1.fecha.value="";
		document.form1.fecha_vencimiento.value="";
		document.form1.monto_prestamo.value="";
		document.form1.ingresos_brutos.value="";
		document.form1.comisiones.value="";
		document.form1.ganancias.value="";
		document.form1.intereses.value="";
		document.form1.multas.value="";
		document.form1.gastos_escribano.value="";
		document.form1.gastos_varios.value="";
		document.form1.gastos_varios_detalle.value="";
		document.form1.comentario.value="";
		document.form1.id_factoring.selectedIndex="0";
	}

	function val_text(comando){
		
    var a=new Array();
    var b=new Array();
	var largo=document.form1.hlastid.value;

	  for(i=0, j=0; i<=largo; i++){
	  	var obj=eval("document.form1.factura"+i);
	  	var obj1=eval("document.form1.tipo"+i);
	  	   if ((typeof(obj)!="undefined")&&(obj.value!=''))	{
	  	   	    a[j]=obj.value;
	        	b[j++]=obj1.value; 
	  	   } 	
	  }
		document.form1.hfacturas.value=a;
		document.form1.tfacturas.value=b;
		if (comando=='guardar'){
			document.form1.hguardar.value="Guardar datos";
			document.form1.traer.value="";
		}else if (comando=='traer'){
			document.form1.traer.value="Traer facturas";
			document.form1.hguardar.value="";
		}
		//if ((typeof(document.form1.tcomentario.value)!="undefined")&&(document.form1.tcomentario.value!="")) document.form1.comentario.value=document.form1.tcomentario.value;
		//else document.form1.comentario.value=" ";
	}
	function valcheck(){
		if (document.form1.chequeado.value=='si'){
			document.form1.chequeado.value='no';
			document.form1.chFinalizada.checked=0;
		}else{
			document.form1.chequeado.value='si';
			document.form1.chFinalizada.checked=1;
		}
	}

	function showValorDolar(){
		var obj=document.getElementById("tabla_monto_prestamo");
		//alert("'"+obj.rows[0].cells[0].childNodes[1].innerText+"'");
		if (document.all.moneda.options[document.all.moneda.selectedIndex].value==2) 
			obj.rows[0].cells[1].childNodes[0].style.display='block';
		else obj.rows[0].cells[1].childNodes[0].style.display='none';
	}
	function restar(monto,j) {
	   var valor;
	   var b;
	   b = eval('document.all.quitar'+j);
	   b.disabled=true;
	   valor=parseFloat(document.all.monto_total.value)-monto;
	   document.all.monto_total.value=valor;
	}
	</script>
		<form name="form1" method="POST" action="facturas_venta_detalle.php" enctype='multipart/form-data'>
			<input type="hidden" name="hguardar" id="hguardar" value="">
			<input type="hidden" name="hfacturas" value="<?=$_POST["hfacturas"]?>">
			<input type="hidden" name="tfacturas" value="<?=$_POST["tfacturas"]?>">
			<input type="hidden" name="hlastid" value="0">
			<input type="hidden" name="id_venta_factura" value="<?=$id_venta_factura?>">
			<input type="hidden" name="traer" id="traer" value="">
			<input type="hidden" name="usuario_cerrador" id="usuario_cerrador" value="">
			<input type="hidden" name="fecha_cierre" id="fecha_cierre" value="">
			<!--<input type="hidden" name="fecha_vencimiento" id="fecha_vencimiento" value="<?//=$fecha_vencimiento?>">-->
			<input type="hidden" name="chequeado" id="chequeado" value="<?=(($estado_venta=="1")?"no":"si")?>">
			<input type="hidden" name="valor_dolar" id="valor_dolar" value="<?=$valor_dolar?>">
			
	<?

	if ($modo=="nuevo") {
		//$rta=sql("select nextval('facturas_venta_id_venta_factura_seq') as id_venta");
		//$_ses_facturas_venta_detalle["id_venta_factura"]=$rta->fields["id_venta"];
		$titulo_tabla="Venta de facturas id. ".$id_venta_factura;
		$_ses_facturas_venta_detalle["id_venta_factura"]=$id_venta_factura;
		$_ses_facturas_venta_detalle["id_factoring"]="";
		$_ses_facturas_venta_detalle["monto_prestamo"]="";
		$_ses_facturas_venta_detalle["ingresos_brutos"]="";
		$_ses_facturas_venta_detalle["ganancias"]="";
		$_ses_facturas_venta_detalle["multas"]="";
		$_ses_facturas_venta_detalle["comisiones"]="";
		$_ses_facturas_venta_detalle["intereses"]="";
		$_ses_facturas_venta_detalle["gastos_escribano"]="";
		$_ses_facturas_venta_detalle["gastos_varios"]="";
		$_ses_facturas_venta_detalle["gastos_varios_detalle"]="";
		$_ses_facturas_venta_detalle["fecha"]=date("d/m/Y");
		$_ses_facturas_venta_detalle["fecha_vencimiento"]=date("d/m/Y");
		$_ses_facturas_venta_detalle["estado_venta"]="1";
		$_ses_facturas_venta_detalle["moneda"]="";
		$_ses_facturas_venta_detalle["comentario"]="";
		$_ses_facturas_venta_detalle["simbolo"]="";
		$_ses_facturas_venta_detalle["fecha_cierre"]="";
		$_ses_facturas_venta_detalle["usuario_cerrador"]="";
		phpss_svars_set("_ses_facturas_venta_detalle", $_ses_facturas_venta_detalle);
	}
	if (($_POST["bguardar"])||($_POST["hguardar"])) {
		$db->starttrans();
		$modo="modif";
		$consulta1="select * from bancos.facturas_venta where id_venta_factura=".$id_venta_factura;
		$rta_consulta=sql($consulta1, "No se puede comprobar existencia de la venta id: ".$id_venta_factura) or fin_pagina();
		if ($rta_consulta->recordCount()==0) {
			$consulta1="insert into bancos.facturas_venta (id_venta_factura, ";
			$values1="values (".$id_venta_factura.", ";
			if ($id_factoring){ $consulta1.="id_factoring, "; $values1.=$id_factoring.", ";}
			if ($monto_prestamo){ $consulta1.="monto_prestamo, "; $values1.=$monto_prestamo.", ";}
			if ($ingresos_brutos){ $consulta1.="ingresos_brutos, "; $values1.=$ingresos_brutos.", ";}
			if ($ganancias){ $consulta1.="ganancias, "; $values1.=$ganancias.", ";}
			if ($multas){ $consulta1.="multas, "; $values1.=$multas.", ";}
			if ($comisiones){ $consulta1.="comisiones, "; $values1.=$comisiones.", ";}
			if ($intereses){ $consulta1.="intereses, "; $values1.=$intereses.", ";}
			if ($gastos_escribano){ $consulta1.="gastos_escribano, "; $values1.=$gastos_escribano.", ";}
			if ($gastos_varios){ $consulta1.="gastos_varios, "; $values1.=$gastos_varios.", ";}
			if ($gastos_varios_detalle!=""){ $consulta1.="gastos_varios_detalle, "; $values1.="'".$gastos_varios_detalle."', ";}
			else { $consulta1.="gastos_varios_detalle, "; $values1.="' ', ";}
			if ($fecha){ $consulta1.="fecha, "; $values1.="'".Fecha_db($fecha)."', ";}
			if ($fecha_vencimiento){ $consulta1.="fecha_vencimiento, "; $values1.="'".Fecha_db($fecha_vencimiento)."', ";}
			else {$consulta1.="fecha_vencimiento, "; $values1.="null ,";}
			if ($usuario_cerrador){ $consulta1.="usuario_cerrador, "; $values1.="'".$usuario_cerrador."', ";}
			if ($fecha_cierre){ $consulta1.="fecha_cierre, "; $values1.="'".$fecha_cierre."', ";}
			if ($estado_venta=="-1"){ $consulta1.="estado_venta, "; $values1.="0, ";}
			else { $consulta1.="estado_venta, "; $values1.="1, ";}
			if ($comentari!=""){ $consulta1.="comentario, "; $values1.="'".$comentario."', ";}
			else { $consulta1.="comentario, "; $values1.="' ', ";}
			if ($moneda){
				$consulta1.="moneda, "; $values1.=$moneda.", ";
				if ($valor_dolar){
					$consulta1.="valor_dolar, ";
					$values1.=$valor_dolar;
				}
			}
			$consulta1=substr($consulta1, 0, strlen($consulta1)-2).")";
			$values1=substr($values1, 0, strlen($values1)-2).")";
			$consulta1.=$values1;
			$rta_consulta1=sql($consulta1, "No se pudo insertar el registro en la base de datos") or fin_pagina();
			$flag_insup=true;
		}elseif ($rta_consulta->recordCount()==1) {
			$consulta1="update bancos.facturas_venta set ";
			if ($id_factoring){ $consulta1.="id_factoring=".$id_factoring.", ";}
			if ($monto_prestamo){ $consulta1.="monto_prestamo=".$monto_prestamo.", ";}
			if ($ingresos_brutos){ $consulta1.="ingresos_brutos=".$ingresos_brutos.", ";}
			if ($ganancias){ $consulta1.="ganancias=".$ganancias.", ";}
			if ($multas){ $consulta1.="multas=".$multas.", ";}
			if ($comisiones){ $consulta1.="comisiones=".$comisiones.", ";}
			if ($intereses){ $consulta1.="intereses=".$intereses.", ";}
			if ($gastos_escribano){ $consulta1.="gastos_escribano=".$gastos_escribano.", ";}
			if ($gastos_varios){ $consulta1.="gastos_varios=".$gastos_varios.", ";}
			if ($gastos_varios_detalle!=""){ $consulta1.="gastos_varios_detalle='".$gastos_varios_detalle."', ";}
			else { $consulta1.="gastos_varios_detalle=' ', ";}
			if ($fecha){ $consulta1.="fecha='".Fecha_db($fecha)."', ";}
			if ($fecha_vencimiento){ $consulta1.="fecha_vencimiento='".Fecha_db($fecha_vencimiento)."', ";}
			else {$consulta1.="fecha_vencimiento=null, ";}
			if ($usuario_cerrador){ $consulta1.="usuario_cerrador='".$usuario_cerrador."', ";}
			if ($fecha_cierre){ $consulta1.="fecha_cierre='".$fecha_cierre."', ";}
			if ($estado_venta=="-1"){ $consulta1.="estado_venta=0, ";}
			else { $consulta1.="estado_venta=1, ";}
			if ($comentario!=""){ $consulta1.="comentario="."'".$comentario."', ";}
			else { $consulta1.="comentario="."' ', ";}
			if ($moneda){
				$consulta1.="moneda=".$moneda.", ";
				if ($valor_dolar)	$consulta1.="valor_dolar=$valor_dolar, ";
			}
			$consulta1=substr($consulta1, 0, strlen($consulta1)-2);
			$consulta1.=" where id_venta_factura=".$id_venta_factura;
			$rta_consulta1=sql($consulta1, "No se pudo actualizar el registro en la base de datos") or fin_pagina();
			$flag_insup=true;
		}else{
			die("ERROR DE CONSISTENCIA EN LA BASE DE DATOS");
		}
		
		if ($flag_insup) {
			$arr_facturas=explode(",", $_POST["hfacturas"]);
			$arr_tipo_factura=explode(",", $_POST["tfacturas"]);
							
			//////////////////////// CONTROL PARA VENTA DE FACTURA CON SEGUIMIENTO DE COBRO FINALIZADO //////////////////////////
			if ($_POST["hfacturas"]) {
				$nro_facturas=explode(",",$_POST["hfacturas"]);
				$arr_tipo_factura=explode(",", $_POST["tfacturas"]);
				$arr_facturas=array();
				$j=0;
				$cant=count($nro_facturas);
				for ($i=0;$i<$cant;$i++) {
				   $div_fact=split("-",$nro_facturas[$i]);
				   $id_num=$div_fact[0];
				   $num_fact=$div_fact[1];
				   $tipo=$arr_tipo_factura[$i];
				   $sql="select id_factura from facturacion.facturas 
				         where id_numeracion_sucursal=$id_num and nro_factura='$num_fact' and tipo_factura='$tipo'";
				   $res=sql($sql,"$sql") or fin_pagina();
				   $arr_facturas[$j++]=$res->fields[id_factura];
				}
					
				$consulta="select licitaciones.unir_texto(id_factura||', ') as f_guardadas 
					       from bancos.facturas_venta_lista 
					       where id_venta_factura=".$id_venta_factura;
			
				$rta_consulta=sql($consulta, "C236") or fin_pagina();
				//facturas que ya están en la venta
				$f_guardadas=explode(",", substr($rta_consulta->fields["f_guardadas"], 0, strlen($rta_consulta->fields["f_guardadas"])-2));
				//separación en facturas que se deben mantener, q se deben quitar, y que se deben chequear
				$mantener=array();

				
				for ($i=0; $i<count($f_guardadas); $i++)
					for($j=0; $j<count($arr_facturas); $j++)
						if  ($f_guardadas[$i]==$arr_facturas[$j]){
							$mantener[]=$f_guardadas[$i];
							$arr_facturas[$j]=$f_guardadas[$i]="-1";
						}
						
		      } 
		      
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			sql("delete from bancos.facturas_venta_lista where id_venta_factura=".$id_venta_factura, "No se pudo borrar la lista de facturas para esta venta") or fin_pagina();
			for ($i=0; $i<count($mantener); $i++){
				if ($mantener[$i]!=-1){
					$consulta1="insert into bancos.facturas_venta_lista (id_venta_factura, id_factura) 
						values (".$id_venta_factura.", ".$mantener[$i].")";
					sql($consulta1, "No se pudo agregar el par (".$id_venta_factura.", ".$mantener[$i].") a la tabla facturas_venta_lista") or fin_pagina();
				}
			}

			for($i=0; $i<count($arr_facturas); $i++){
				if (($arr_facturas[$i])&&($arr_facturas[$i]!="-1")){
					$consulta2="select f.id_factura, f.nro_factura, f.id_moneda, f.estado, c.estado as estado_cobranza 
						        from facturacion.facturas f join licitaciones.cobranzas c using (id_factura) 
						        where c.estado ilike 'pendiente' and f.estado='t' and f.id_factura=".$arr_facturas[$i];
				
					$rta_consulta2=sql($consulta2, "No se pudo consultar la base de datos por una factura") or fin_pagina();
					if ($rta_consulta2->recordCount()!=0){
						$consulta1="select * from bancos.facturas_venta_lista where id_venta_factura=$id_venta_factura and id_factura=".$rta_consulta2->fields["id_factura"];
						$rta_consulta1=sql($consulta1, "No se pudo consultar la base de datos por una factura vendida") or fin_pagina();

						if (!$moneda_lista) $moneda_lista=$rta_consulta2->fields["id_moneda"];
						if (($rta_consulta1->recordCount()==0)&&($rta_consulta2->fields["estado"]=="t")&&($rta_consulta2->fields["estado_cobranza"]=="PENDIENTE")){
							$consulta1="insert into bancos.facturas_venta_lista (id_venta_factura, id_factura)
								values (".$id_venta_factura.", ".$rta_consulta2->fields["id_factura"].")";
							sql($consulta1, "No se pudo agregar el par (".$id_venta_factura.", ".$arr_facturas[$i].") a la tabla facturas_venta_lista") or fin_pagina();
						}
					}
				}
			}
		}
	$db->completetrans();	
	}
	/*****************************   MUESTRA DATOS  ********************************************/
	//if $modo== modif ya existe la venta de factura
	//if $modo==nuevo se esta creando la venta de factura
	if ($modo=="modif"){
		$titulo_tabla="Datos de la venta ".(($estado_venta==1)?"(pendiente)":"(finalizada)");
		if ($estado_venta!="1") $titulo_tabla.=" cerrada por ".$usuario_cerrador." (".$fecha_cierre.")";
	}
	echo "<input type='hidden' name='modo' value='modif'>";
	?>
		<table border="1" cellspacing="0" bgcolor="<?=$bgcolor2?>" width="90%" align="center">
			<th id="mo" align="center" colspan="4"><?=$titulo_tabla?></th>
			<tr><td colspan="4"><table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>
				<td id="mo" width="15%">Factoring:</td>
				<td width="25%">
					<?
						$consulta="select id_factoring,nombre from licitaciones.factoring";
						$rta_consulta=sql($consulta, "No se puede acceder a la tabla 'factoring'") or fin_pagina();
						while (!$rta_consulta->EOF){
							$factorings_id[]=$rta_consulta->fields["id_factoring"];
							$factorings_nombre[]=$rta_consulta->fields["nombre"];
							if ($rta_consulta->fields["id_factoring"]==$id_factoring) $selected_factoring=$rta_consulta->fields["nombre"];
							$rta_consulta->moveNext();
						}
						if (!$selected_factoring) $selected_factoring=$factorings_id[0];
						g_draw_value_select("id_factoring", $selected_factoring, $factorings_id, $factorings_nombre);
					?>
				</td>
				<td id="mo" width="15%">Fecha:</td>
				<td align="center" width="25%" nowrap>
					<input type="text" name="fecha" id="fecha" value="<?=$fecha?>" readonly></input>
					&nbsp;<?=link_calendario("fecha")?>
				</td>
				<td id="mo" width="15%">Fecha vencim.:</td>
				<td align="center" width="30%" nowrap>
					<input type="text" name="fecha_vencimiento" id="fecha_vencimiento" value="<?=$fecha_vencimiento?>"></input>
					&nbsp;<?=link_calendario("fecha_vencimiento")?>
				</td>
			</tr></table></td></tr>
			<tr>
				<td colspan="4">
					<table border="0" cellspacing="0" width="100%" align="center">
					<th align="center" id="mo" colspan="5">Facturas:</th>
					<tr>
						<td align="center" id="mo" width="20%">Nro. Factura</td>
						<td align="center" id="mo" width="20%">Tipo</td>
						<td align="center" id="mo" width="10%">Monto</td>
						<td align="center" id="mo">Cliente</td>
						<td align="center" id="mo">&nbsp;</td>
					</tr>
						<?
						$i=0;
						////////////////////////////////////////////////////////////////////////////////////////////////////////////
						// lista de facturas ya existentes en la venta
						if ((!$_POST["hfacturas"])||($_POST["hguardar"])){
							$consulta="select id_factura, (numeracion_sucursal.numeracion || text('-') || f.nro_factura) as nro_factura,tipo_factura, 
							           id_moneda, cliente, simbolo, id_moneda, sum(precio*cant_prod) as monto_factura
								       from bancos.facturas_venta_lista join facturacion.facturas f using (id_factura)
								       join facturacion.numeracion_sucursal using (id_numeracion_sucursal)
									   join facturacion.items_factura itf using (id_factura) join licitaciones.moneda using(id_moneda)
								       where id_venta_factura=".$id_venta_factura."
								       group by id_factura, nro_factura, id_moneda, cliente, simbolo, id_moneda,numeracion,tipo_factura";

							$lista_facturas=sql($consulta) or fin_pagina();
							while (!$lista_facturas->EOF){
								if ($lista_facturas->fields["id_moneda"]==2) {
										      $sum=$lista_facturas->fields["monto_factura"]*$valor_dolar;
										      $monto_total+=$lista_facturas->fields["monto_factura"]*$valor_dolar;
										    }
										else {
											 $sum=$lista_facturas->fields["monto_factura"];  
											 $monto_total+=$lista_facturas->fields["monto_factura"];  
										}
								
								$qnombre="quitar".$i;
								$fnombre="factura".$i;
								$tnombre="tipo".$i;
								if (!$moneda_fila) $moneda_fila=$lista_facturas->fields["id_moneda"];
								$facturas_interface[$i]=array(
									"nro"=>$lista_facturas->fields["nro_factura"],
									"text_id"=>$fnombre,
									"tipo"=>$tnombre,
									"selected"=>$lista_facturas->fields["tipo_factura"],
									"button_id"=>$qnombre,
									"monto"=>$lista_facturas->fields["simbolo"]." ".$lista_facturas->fields["monto_factura"],
									"cliente"=>$lista_facturas->fields["cliente"],
									"monto_g"=>$sum,
									"error"=>1
								);
								
								$i++;
								$lista_facturas->moveNext();
							}
						}
						//////////////////////////////////////////////////////////////////////////////////////////////////////////
						// lista de facturas por agregar
						if ($_POST["traer"]) {
							$j=$i;
							$arr_facturas_inteface=explode(",", $_POST["hfacturas"]);
							$arr_tipo_factura=explode(",", $_POST["tfacturas"]);
							$lim=count($arr_facturas_inteface);
							for ($k=0; $k<$lim; $k++){
								if($arr_facturas_inteface[$k]){
									$qnombre="quitar".($i);
									$fnombre="factura".($i);
									$tnombre="tipo".($i);
									$facturas_interface[$i]=array(
										"nro"=>$arr_facturas_inteface[$k],
										"text_id"=>$fnombre,
										"button_id"=>$qnombre,
										"tipo"=>$tnombre,
										"selected"=>$arr_tipo_factura[$k],
										"monto"=>"",
										"cliente"=>"",
										"error"=>0
									);
									$i++;
								}
							}
							$monto_total=0;
							for ($j=0; $j<count($facturas_interface); $j++){
								if ($facturas_interface[$j]["error"]==0) {
									$nro_factura_corriente=$facturas_interface[$j]["nro"];
									$tipo_factura_corriente=$facturas_interface[$j]["selected"];
								
									$consulta="select f.id_factura, f.nro_factura, f.id_moneda, cliente, simbolo,
											   sum(precio*cant_prod) as monto_factura, f.estado, tipo_factura, c.estado as estado_cobranza
										       from facturacion.facturas f join facturacion.items_factura itf using (id_factura)
											   join licitaciones.moneda using(id_moneda)
											   join licitaciones.cobranzas c using (id_factura)
										where c.nro_factura='".$nro_factura_corriente."' and tipo_factura='$tipo_factura_corriente'
										group by f.id_factura, f.nro_factura, f.id_moneda, cliente, simbolo, f.estado, tipo_factura, c.estado";
									
									$rta_consulta=sql($consulta, "No se pudieron traer los datos de la factura nro. ".$nro_factura_corriente) or fin_pagina();
                                  
									if ((!$moneda_fila)&&($rta_consulta->recordCount()==1)) $moneda_fila=$rta_consulta->fields["id_moneda"];
									if (($rta_consulta->recordCount()==1)&& ($rta_consulta->fields["estado"]=="t")
									    &&($rta_consulta->fields["estado_cobranza"]=="PENDIENTE")&& ($rta_consulta->fields["monto_factura"]>0)){
										$facturas_interface[$j]["monto"]=$rta_consulta->fields["simbolo"]." ".formato_money($rta_consulta->fields["monto_factura"]);
										$facturas_interface[$j]["cliente"]=$rta_consulta->fields["cliente"];
										$facturas_interface[$j]["selected"]=$rta_consulta->fields["tipo_factura"];
										$facturas_interface[$j]["error"]=1;
										$encontrada=true;
									
										if ($rta_consulta->fields["id_moneda"]==2) {
										      $monto_total+=$rta_consulta->fields["monto_factura"]*$valor_dolar;
										      $facturas_interface[$j]["monto_g"]=$rta_consulta->fields["monto_factura"]*$valor_dolar;  //para restar a monto total en el caso que presione el boton quitar
										}
										else {
											 $monto_total+=$rta_consulta->fields["monto_factura"];  
											 $facturas_interface[$j]["monto_g"]=$rta_consulta->fields["monto_factura"];  
										}
									}else{
										if ($rta_consulta->recordCount()==0)
											$facturas_interface[$j]["error"]='No se encontró alguna factura con este número';
										elseif ($rta_consulta->recordCount()>1)
											$facturas_interface[$j]["error"]='Inconsistencia en la base de datos (números de facturas duplicados)';
										//elseif ($moneda_fila!=$rta_consulta->fields["id_moneda"])
											//$facturas_interface[$j]["error"]="El signo monetario de esta factura no coincide con la(s) anterior(es)";
										elseif ($rta_consulta->fields["estado"]!="t")
											$facturas_interface[$j]["error"]="El estado de la factura no es 'terminada'";
										elseif ($rta_consulta->fields["estado_cobranza"]!="PENDIENTE")
											$facturas_interface[$j]["error"]="La cobranza de la factura no está pendiente";
										elseif ($rta_consulta->fields["monto_factura"]<=0)
											$facturas_interface[$j]["error"]="La factura tiene un monto menor o igual a 0 (cero)";
										else $facturas_interface[$j]["error"]="Error desconocido";
									}
								}
							}
						}
						for ($k=0; $k<5; $k++){
							$qnombre="quitar".($k+$i);
							$fnombre="factura".($k+$i);
							$tnombre="tipo".($k+$i);
							$facturas_interface[]=array(
								"nro"=>"",
								"text_id"=>$fnombre,
								"button_id"=>$qnombre,
								"tipo"=>$tnombre,
								"selected"=>"b", //por defecto es tipo B
								"monto"=>"",
								"cliente"=>"",
								"error"=>-1
							);
						}
					
						$i+=5;
						
						for ($j=0; $j<count($facturas_interface); $j++) {
							
							echo("<tr align='center'>");
							if ($facturas_interface[$j]["error"]==1){
								?>
									<td>
										<input class="text_4"	type="text" id="<?=$facturas_interface[$j]["text_id"]?>" name="<?=$facturas_interface[$j]["text_id"]?>"
											value="<?=$facturas_interface[$j]["nro"]?>" style="text-align:center">
									</td>
									<td nowrap>
										<select name="<?=$facturas_interface[$j]["tipo"]?>" >
									      <option value='a' <?if ($facturas_interface[$j]["selected"]=='a') echo 'selected';?>>A</option>
									      <option value='b' <?if ($facturas_interface[$j]["selected"]=='b') echo 'selected';?>>B</option>
									    </select>    
									</td>
									<td nowrap>
										<?=$facturas_interface[$j]["monto"]?>
									</td>
									<td>
										<?=$facturas_interface[$j]["cliente"]?>
									</td>
									<td>
										<input type="button" value="Quitar" name="<?=$facturas_interface[$j]["button_id"]?>" onclick="getElementById('<?=$facturas_interface[$j]["text_id"]?>').value='';restar('<?=$facturas_interface[$j]["monto_g"]?>','<?=$j?>')">
									</td>
								<?		
							}elseif ($facturas_interface[$j]["error"]==-1){
								?>
									<td>
										<input type="text" id="<?=$facturas_interface[$j]["text_id"]?>"	name="<?=$facturas_interface[$j]["text_id"]?>" value="">
									</td>
									<td>
									  <select name="<?=$facturas_interface[$j]["tipo"]?>" >
									      <option value='a' <?if ($facturas_interface[$j]["selected"]=='a') echo 'selected';?>>A</option>
									      <option value='b' <?if ($facturas_interface[$j]["selected"]=='b') echo 'selected';?>>B</option>
									  </select>    
									</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
								<?
							}else{
								?>
									<td>
										<input type="text" id="<?=$facturas_interface[$j]["text_id"]?>"
											name="<?=$facturas_interface[$j]["text_id"]?>" value="<?=$facturas_interface[$j]["nro"]?>">
									</td>
									<td> <select name="<?=$facturas_interface[$j]["tipo"]?>" >
									      <option value='a' <?if ($facturas_interface[$j]["selected"]=='a') echo 'selected';?>>A</option>
									      <option value='b' <?if ($facturas_interface[$j]["selected"]=='b') echo 'selected';?>>B</option>
									  </select>  </td>
									<td>-</td>
									<td>
										<font color="red"><?=$facturas_interface[$j]["error"]?></font>
									</td>
									<td>
										<input type="button" value="Borrar" name="<?=$facturas_interface[$j]["button_id"]?>"
											onclick="getElementById('<?=$facturas_interface[$j]["text_id"]?>').value=''">
									</td>
								<?
							}
							echo("</tr>");
						}
					?>
						<script>document.form1.hlastid.value=<?=count($facturas_interface)?></script>
						<tr>
						    <td align="right">  <font color="red" size="2pt"> Monto total:</font> $  <input name="monto_total"  type="text" style="text-align:left; background:inherit; border:none;" value="<?if ($monto_total) echo number_format($monto_total,2,".",""); else echo $_POST['monto_total'];?>" readonly size="10" > </td>
							<td align="center" colspan="3">
								<input type="button" value="Traer facturas" id="btraer" name="btraer" onclick="val_text('traer'); document.form1.submit();">
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td id="mo">Monto del pr&eacute;stamo:</td>
				<td nowrap>
					<table border="0" cellpadding="0" cellspacing="0" width="100%" id="tabla_monto_prestamo">
						<tr>
							<td>
								<span id="cell1">
					<?
						$consulta="select * from licitaciones.moneda";
						$rta_consulta=sql($consulta, "No se puede acceder a la tabla 'moneda'") or die();
						
						while (!$rta_consulta->EOF){
							$moneda_id[]=$rta_consulta->fields["id_moneda"];
							$moneda_nombre[]=$rta_consulta->fields["simbolo"];
							if ($rta_consulta->fields["id_moneda"]==$moneda) $selected_moneda=$rta_consulta->fields["simbolo"];
							$rta_consulta->moveNext();
						}
						if (!$selected_moneda) $selected_moneda=$moneda_id[0];
						g_draw_value_select("moneda", $selected_moneda, $moneda_id, $moneda_nombre, 1, " onchange='showValorDolar();'");
					?>
									<input type="text" name="monto_prestamo" value="<?=number_format($monto_prestamo, 2, ".", "")?>" id="emonto_prestamo" maxlength="11" size="11" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Monto préstamo');">
								</span>
							</td>
							<td>
								<!--<span id="cell2" style="display:<?//=(($selected_moneda=="U\$S")?"block":"none")?>">-->
									<?=(($valor_dolar==1)?"&nbsp;":"Dólar: $ <b>".formato_money($valor_dolar))."</b>"?>
								<!--</span>-->
							</td>
						</tr>
					</table>
				</td>
				<td id="mo" colspan="2">
					Finalizada:
					<input type="checkbox" name="chFinalizada" id="chFinalizada" <?=($estado_venta!="1")?"checked":"";?> value="finalizada"
						onclick="document.form1.usuario_cerrador.value='<?=$_ses_user["login"]?>'; document.form1.fecha_cierre.value='<?=date("Y-m-d H:i")?>'; valcheck();">
				</td>
			</tr>
			<tr>
				<td id="mo">Ingresos brutos:</td>
				<td>$<input type="text" name="ingresos_brutos" id="eingresos_brutos" value="<?=number_format($ingresos_brutos, 2, ".", "")?>" maxlength="11" size="11" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Ingresos brutos');"></td>
				<td id="mo">Comisiones:</td>
				<td>$<input type="text" name="comisiones" id="ecomisiones" value="<?=number_format($comisiones, 2, ".", "")?>" maxlength="11" size="11" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Comisiones');"></td>
			</tr>
			<tr>
				<td id="mo">Ganancias:</td>
				<td>$<input type="text" name="ganancias" id="eganancias" value="<?=number_format($ganancias, 2, ".", "")?>" maxlength="11" size="11" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Ganancias');"></td>
				<td id="mo">Intereses:</td>
				<td>$<input type="text" name="intereses" id="eintereses" value="<?=number_format($intereses, 2, ".", "")?>" maxlength="11" size="11" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Intereses');"></td>
			</tr>
			<tr>
				<td id="mo">Multas:</td>
				<td>$<input type="text" name="multas" id="emultas" value="<?=number_format($multas, 2, ".", "")?>" maxlength="11" size="11" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Multas');"></td>
				<td id="mo">Gastos escribano:</td>
				<td>$<input type="text" name="gastos_escribano" id="egastos_escribano" value="<?=number_format($gastos_escribano, 2, ".", "")?>" maxlength="11" size="11"  onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Gastos escribano');"></td>
			</tr>
			<tr>
				<td id="mo">Gastos varios:</td>
				<td>$<input type="text" name="gastos_varios" id="egastos_varios" value="<?=number_format($gastos_varios, 2, ".", "")?>" maxlength="11" size="11" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Gastos varios');"></td>
				<td id="mo">Detalle de gastos varios:</td>
				<td><input type="text" name="gastos_varios_detalle" id="edetalle" value="<?=$gastos_varios_detalle?>" style="width:100%"></td>
			<tr>
				<td id="mo">Comentario:</td>
				<td colspan="3"><textarea name="comentario" id="tcomentario" cols="50" style="width:100%"><?=$comentario?></textarea>
			</tr>
		</table>
		<br>
<?
if($_POST['guarda_arch']=="Guardar Archivo"){
	if($_FILES['archivo']["name"]!=""){
       $size=$_FILES["archivo"]["size"];
       $type=$_FILES["archivo"]["type"];
       $name=$id_venta_factura.$_FILES["archivo"]["name"];
       $temp=$_FILES["archivo"]["tmp_name"];
       $path=UPLOADS_DIR."/venta_factura";
       $FileSize="";
       $FileType="";
       if (!is_file(UPLOADS_DIR."/venta_factura/".$name)){
       	$ret = FileUpload($temp,$size,$name,$type,$max_file_size,$path,"",$extensiones,"",1,0,0);
       	if($ret["error"]==0){
       		$fecha_hoy=date("Y/m/d H:i:s");
       		$subido_por=$_ses_user['name'];
        	$query="insert into bancos.archivo_venta_fact(id_venta_factura,nombre,fecha,subido_por,size)
            	     values($id_venta_factura,'$name','$fecha_hoy','$subido_por',$size)";        	
         	sql($query) or fin_pagina();
         	$msg="El archivo se Subio con Exito";
       	}//de if($ret==0)
       	else {
       		$msg="Error al subir el archivo";
       	}
       }
       else {
       	$msg="El Archivo ya Existe en el directorio";
       }
      }	
}
if ($id_venta_factura!=""){
$q = "SELECT * from bancos.archivo_venta_fact where id_venta_factura=$id_venta_factura ";
$rs=sql($q) or fin_pagina();
?>
<a name=archivos></a>
<table width=99% align=center cellpadding=0 cellspacing=6 border=1 bordercolor="#111111">
<tr> <td id="ma_mg" > Archivos </td> </tr>
<tr>
<td align=right>
<table width="100%">
<tr>
<td align="left">
<b><?=$msg ?></b>
</td>
<td align="right">
<input type=file name='archivo' style="width=50%">
<input type="submit" name="guarda_arch" value="Guardar Archivo">
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td>
<table width='100%'  border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
<tr>
 <td colspan=7 style='border-right: 0;' id=ma style="text-align:left">
 <b>Total:</b><?=$total_archivos=$rs->recordcount() ?>
 </td>
<tr><td align=right id=mo>Archivo</td>
<td align=right id=mo>Fecha</td>
<td align=right id=mo>Subido por</td>
<td align=center id=mo>&nbsp;</td>
</tr>
<? while (!$rs->EOF) {
?>
    <tr style='font-size: 9pt' > 
    <td align=center>
<?    
    if (is_file("../../uploads/venta_factura/".$rs->fields["nombre"]))
        echo "<a target=_blank href='".encode_link("facturas_venta_detalle.php",array ("file" =>$rs->fields["nombre"],"size" => $rs->fields["size"],"cmd" => "download"))."'>";
    echo $rs->fields["nombre"]."</a></td>\n";
?>    
    <td align=center>&nbsp;<?= Fecha($rs->fields["fecha"]) ?></td>
    <td align=center>&nbsp;<?= $rs->fields["subido_por"] ?></td>    
    <td align=center>
<?    
	if (permisos_check("inicio","permiso_boton_elimina_archivo_venta_factura")){
		$lnk=encode_link("$_SERVER[PHP_SELF]",Array("id_archivo_venta_fact"=>$rs->fields["id_archivo_venta_fact"],"filename"=>$rs->fields["nombre"],"cmd" => "borra_archivo"));
    	echo "<a href='$lnk'><img src='../../imagenes/close1.gif' border=0 alt='Eliminar el archivo: \"". $rs->fields["nombre"] ."\"'></a>";
	}

?>
    </td>    
    </tr>
<?
    $rs->MoveNext();
}
}
?>
</table>
</td>
</tr>
</table>

		<table border="0" cellspacing="0" bgcolor="<?=$bgcolor2?>" width="90%" align="center">
			<tr>
				<td align="center">
					<input type="button" name="bguardar" value="Guardar cambios" onclick="val_text('guardar'); document.form1.submit();">
					<input type="button" name="volver" value="Volver" onclick="clearfields(); document.location='facturas_venta.php';">
				</td>
			</tr>
		</table>
		<br>
<?
	echo "</form>";
	if ($modo=="nuevo") {?><script>clearfields();</script><?}
	fin_pagina();
?>
