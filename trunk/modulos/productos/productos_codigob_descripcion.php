<?php
/* MAD
$Author: marco_canderle $
$Revision: 1.29 $
$Date: 2006/05/22 22:07:14 $
*/
/*
Atencion!!!!!!!!!!
puesto_servicio_tecnico es equivalente a RMA en este caso
*/
include("../../config.php");

$code = $_POST["code"] or $code = $parametros["code"];

if($parametros["borrar"]==1)
{$db->StartTrans();
 //primero vemos que el codigo a borrar no sea padre de algún otro código
 $query="select codigo_barra from codigos_barra where codigo_padre='$code'";
 $hijos=sql($query) or fin_pagina();

 $tiene_hijos="";
 $hay_hijos=0;
 while (!$hijos->EOF)
 {//si el codigo de barras es distinto de $code, significa que
  //$code tiene un hijo, por lo que no se puede borrar
  if($hijos->fields['codigo_barra']!=$code)
  {$tiene_hijos.="<br>".$hijos->fields['codigo_barra'];
   $hay_hijos=1;
  }
  $hijos->MoveNext();
 }

 //si es padre de otro código no se puede borrar. Primero debe borrar
 //los códigos que son hijos, para despues borrar el padre
 if($hay_hijos)
 {$link_borrar=encode_link("productos_codigob_descripcion.php",array("code"=>$code));
  $error_tiene_hijos="<font color=red><b>-----------------------------------------<BR>\n
                        EL CÓDIGO DE BARRAS: ".$_POST['cod_barra_padre'].", NO SE PUEDE BORRAR PORQUE CONTIENE CÓDIGOS QUE ESTÁN ATADOS A ÉL.
                        <BR>PARA PODER BORRARLO, PRIMERO DEBER BORRAR LOS SIGUIENTES CÓDIGOS DE BARRA:$tiene_hijos.<br><br>\n
                        <input type='button' value='Volver' onclick=\"document.location='$link_borrar'\">\n
                        <BR>-----------------------------------------<BR>\n
                        ";
  echo $error_tiene_hijos;
  die;
 }//de if($hay_hijos)
 else//no tiene hijos asi que se puede borrar
 {

  $query="delete from log_codigos_barra where codigo_barra='$code'";
  sql($query) or fin_pagina();
  $query="delete from codigos_barra where codigo_barra='$code'";
  sql($query) or fin_pagina();

  if($parametros["nro_orden"])
   $part_text="de la Orden de compra ".$parametros["nro_orden"];
  else
   $part_text="de RMA";
  //enviamos e-mail avisando que se borró un código de barras
  $text="El código de barra $code se eliminó del sistema\n\n";
  $text.="Usuario que eliminó el código de barras: ".$_ses_user['name']."\n\n";
  $text.="El código de barras pertenecía al producto '".$parametros["producto_nombre"]."', ingresado a través $part_text \n";
  enviar_mail(to_group(array("cod_barra")),"El código de barra $code ha sido eliminado del sistema",$text,'','','');

 }
 $db->CompleteTrans();
 $msg="<b><center>El código de barras $code, se eliminó con éxito</center></b>";
 $link_listado=encode_link("productos_codigob.php",array("msg"=>$msg));
 header("location:$link_listado");
}//de if($_POST["borrar"]==1)


if ($_POST["comentario"]=="Guardar Comentario") {
	$codigo = $_POST["codigo_comentario"] or die("Error en parametro codigo para cambiar el comentario");
	$comentario = $_POST["texto_comentario"];
	$sql_update_c = "update codigos_barra set comentario='$comentario' where codigo_barra='$codigo'";
	sql($sql_update_c,"Error en la consulta de actualización del comentario");
 	echo "<b><center>El comentario se actualizó con éxito</center></b>";
}


if ($_POST["modificar"]) {
    $db->StartTrans();
    $usuario=$_ses_user["name"];
    $fecha=date("Y-m-d H:i:s");
    if ($_POST["puesto_st"]){
                   $puesto_servicio_tecnico=1;
                   $tipo_log=" Se agrego check de puesto de servicio técnico";
                   }
                   else{
                   $puesto_servicio_tecnico=0;
                   $tipo_log=" Se elimino check de puesto de servicio técnico";
                   }
    $sql="update codigos_barra set puesto_servicio_tecnico=$puesto_servicio_tecnico where codigo_barra='$code'";
    sql($sql) or fin_pagina();
	$sql = "insert into log_codigos_barra (codigo_barra,usuario,fecha,tipo) values ('$code','$usuario','$fecha','$tipo_log')";
	sql($sql,"Error insertando el log") or fin_pagina();
   $db->CompleteTrans();
}

$query="select tipos_prod.descripcion as tipo, codigos_barra.codigo_barra, codigos_barra.codigo_padre, codigos_barra.comentario,
        codigos_barra.puesto_servicio_tecnico, log_codigos_barra.nro_orden, orden_de_compra.fecha_entrega,
		(case when (codigos_barra.id_producto is not null) then productos.desc_gral
			else producto_especifico.descripcion
		end) as descripcion,
		(case when (codigos_barra.id_producto is not null) then productos.precio_licitacion
			else producto_especifico.precio_stock
		end) as precio
	from general.codigos_barra
		left join general.producto_especifico using(id_prod_esp)
		left join general.productos on(productos.id_producto=codigos_barra.id_producto)
		join general.log_codigos_barra using(codigo_barra)
		left join compras.orden_de_compra using(nro_orden)
		join general.tipos_prod on (tipos_prod.id_tipo_prod=productos.id_tipo_prod or tipos_prod.id_tipo_prod=producto_especifico.id_tipo_prod)
	where codigos_barra.codigo_barra = '$code'
		and log_codigos_barra.tipo ilike '%Ingresado%'";

$result = sql($query,"Error consultando datos de la orden") or fin_pagina();

if ($result->RecordCount()==0)
{
	fin_pagina();
	die("No hay nada que mostrar..");
}

$oc = $result->fields["nro_orden"];
$codigo = $result->fields["codigo_barra"];
$codigo_padre = $result->fields["codigo_padre"];
$comentario= $result->fields["comentario"];
$tipo=  $result->fields["tipo"];
$precio =  $result->fields["precio"];
$desc_gral =  $result->fields["descripcion"];
$observaciones =  $result->fields["observaciones"];
//$fecha_log =  $result->fields["fecha_log"];
$fecha_oc =  $result->fields["fecha_entrega"];
//$usuario =  $result->fields["usuario"];
//$tipo_log =  $result->fields["tipo_log"];
$id_rma = $result->fields["id_info_rma"];
$puesto_servicio_tecnico = $result->fields["puesto_servicio_tecnico"];

echo $html_header;


$query_logs = "Select usuario,fecha,tipo from log_codigos_barra where codigo_barra like '$codigo' order by fecha asc";
$result_logs = sql($query_logs,"Error en consulta de logs.") or fin_pagina();
function cargar(&$arr)
{
 $i=0;
 while($i<sizeof($arr)){
    if ($arr[$i][0]!=-1){
	   $id=$arr[$i];
	   $arr[$i][0]=-1;
	   return $id;
	}
	else $i++;
	}
 return false;
}//de function cargar(&$arr)
?>
<BR>
<FORM id="form1" method="POST" action="productos_codigob_descripcion.php">
<TABLE align="center" width="90%" class="bordes" bgcolor="Silver">
<TR id="mo"><TD>Ver Logs
	<input type="checkbox" name="ver_logs" onclick="if (this.checked) document.all.logs.style.display='block'; else document.all.logs.style.display='none';" class="estilos_check">
</TD></TR>
<TR><TD>
<DIV id="logs" style="display:none; overflow:auto; height:50;" >
<TABLE width="100%" align="center" cellpadding="0" cellspacing="0">
<TR id="mo">
<TD width="10%">Fecha</TD>
<TD width="20%">Usuario</TD>
<TD>Evento</TD>
</TR>
<?while(!$result_logs->EOF){
	$tipo_log = $result_logs->fields("tipo");
	if (ereg("OC",$tipo_log))
	{
		ereg("([0-9]+)",$tipo_log,$reg);
		$link = encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$reg[1]));
		$tipo_log=eregi_replace("([0-9]+)","<a href='$link' target='_blank' title='Ir a la Orden de Compra'>\\1</a>",$tipo_log);
	}
	elseif ($pos_rma=stripos($tipo_log,"RMA Nº"))
	{
		$subst=substr($tipo_log,$pos_rma);
		$subst=split(" ",$subst);
		$link = encode_link("../stock/stock_rma.php",array("id_info_rma"=>$subst[2],"pagina"=>"10"));
		$tipo_log=eregi_replace("([0-9]+)","<a href='$link' target='_blank' title='Ir a listado de RMA'>".$subst[2]."</a>",$tipo_log);
	}
	elseif (ereg("RMA",$tipo_log))
	{	ereg("(C\.A\.S )([0-9]+)",$tipo_log,$reg);
		$link = encode_link("../stock/listar_rma.php",array("keyword"=>$reg[2],"filter"=>"nrocaso"));
		$tipo_log=eregi_replace("(C\.A\.S )([0-9]+)","\\1<a href='$link' target='_blank' title='Ir a listado por Nº de Caso'>\\2</a>",$tipo_log);
	}

?>
<TR bgcolor="#F0F8FF">
<TD><?=fecha($result_logs->fields("fecha"))?></TD>
<TD align="center"><?=$result_logs->fields("usuario")?></TD>
<TD align="center"><?=$tipo_log?></TD>
</TR>
<TR>
<?$result_logs->MoveNext();}?>
</TABLE>
<BR>
</DIV>
</TD></TR>
</TABLE>
<TABLE class="bordes" align="center" width="90%" border=0>
<TR id="mo">
<TD colspan="2">Descripción del producto con código de barras</TD>
</TR>
<TR id="ma">
	<TD width="60%">
	<TABLE class="bordes" cellspacing="1" width="100%" style="font-weight: bold" >
	<TR bgcolor="#F0F8FF" >
	<TD width="40%">Código Barra</TD>
	<TD width="60%" align="center"><font color="red"><?=$codigo?></font></TD>
	</TR>
	<?if ($codigo <> $codigo_padre) {?>
	<TR bgcolor="#F0F8FF" >
	<TD>Código Padre</TD>
	<TD align="center"><font color="Blue"><?=$codigo_padre?></font></TD>
	</TR>
	<?}?>

	<TR bgcolor="#F0F8FF">
	<TD>Tipo de Producto</TD>
	<TD><?=$tipo?></TD>
	</TR>
	<TR bgcolor="#F0F8FF">
	<TD>Descripción</TD>
	<TD><?=$desc_gral?></TD>
	</TR>
	<TR bgcolor="#F0F8FF">
	<TD>Precio</TD>
	<TD align="right">U$S <?=formato_money($precio)?></TD>
	</TR>
	</TABLE>
</TD>
<TD width="40%" valign="top">
	<TABLE width="100%" class="bordes" style="font-weight: bold">
	<TR id="mo">
<?if ($oc) {?>
	<TD colspan="2">Orden de compra asociada</TD>
	</TR>
	<TR bgcolor="#F0F8FF">
	<TD >Número de orden</TD>
	<?$link = encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$oc));?>
	<TD align="center" onclick="window.open('<?=$link?>');" style="cursor:hand" title="Ir a la orden de compra"><u><?=$oc?></u></TD>
	</TR>
	<TR bgcolor="#F0F8FF">
	<TD>Fecha de orden</TD>
	<TD><?=Fecha($fecha_oc)?></TD>

	<TR bgcolor="#F0F8FF">
	<TD>Proveedor</TD>
	<?
	//levanto el id_proveedor de la tabla orden_de_compra (nro_orden es clave)
	$sql_ord_compra="select id_proveedor from orden_de_compra where nro_orden = $oc";
	$result_ord_compra=sql($sql_ord_compra,"Error obteniendo datos de la Orden de Compra") or die();
	//asigno la variable del id_proveedor para usar en la consulta para traer la razon social del proveedor
	$idProveedor = $result_ord_compra->fields["id_proveedor"];
	//levanto la razon social para mostrar
	$sql_proveedor="select razon_social from proveedor where id_proveedor = $idProveedor";
	$result_proveedor=sql($sql_proveedor,"Error obteniendo datos del Proveedor") or die();
	?>
	<TD><?=$result_proveedor->fields["razon_social"]?></TD>

<?} elseif ($id_rma) {
	$sqq="select id_deposito,id_producto,id_proveedor from info_rma where id_info_rma = $id_rma";
	$rqq=sql($sqq,"Error obteniendo datos del RMA") or die();
	$id_deposito = $rqq->fields["id_deposito"];
	$id_proveedor = $rqq->fields["id_proveedor"];
	$id_producto = $rqq->fields["id_producto"];
	$ref = encode_link("../stock/stock_descontar_rma.php",array("id_producto"=>$id_producto,
					        "id_deposito"=>$id_deposito,"id_proveedor"=>$id_proveedor,
				        "id_info_rma"=>$id_rma,"pagina_listado"=>"real"
					));

	echo "<td id=mo onclick='window.open(\"$ref\")' style=cursor:hand title='Ver el RMA asociado'> Asociado a RMA</td>";
}
	?>

	</TR>
	</TABLE>
</TD>
</TR>

<?
if ($puesto_servicio_tecnico)
                $checked=" checked";
                else
                $checked=" ";
/*
?>
<tr bgcolor="">
   <TD colspan=2 >
      <table width="100%" align=center>
        <tr>
         <td width=5%><input type=checkbox name=puesto_st value=1 <?=$checked?>></td>
          <td align=left><b>Este producto ha pasado por el puesto de R.M.A</b></td>
          <td><input type=submit name=modificar value=Modificar></td>
        </tr>
      </table>
   </TD>
</TR>
*/
?>
<?if ($oc) {
$query_asoc="select fact_prov.fecha_emision, compras.factura_asociadas.* from compras.factura_asociadas join general.fact_prov using(id_factura) where nro_orden=$oc";
  $res_asoc=sql($query_asoc) or fin_pagina();
  ?>
  <tr><td colspan="2">
   <br>
  <TABLE width="100%" class="bordes" style="font-weight: bold">
  <tr id="mo"><td align="center"><b>Facturas asociadas a la orden de compra</b></td></tr>
  <tr>
  <td>

  <table border="1" align="center" width="100%">
		   <tr id=ma_sf>
		    <td width="49%" align="center"><strong> ID Factura </strong> </td>
		    <td width="47%" align="center" id="td_fecha_factura"><strong>Fecha Factura</strong>&nbsp;
		    <td width="47%" align="center" id="archi"><strong>Archivos</strong>&nbsp;
		    </td>
		   </tr>
		   <?
		    $filas=$res_asoc->RecordCount();
		    $cant_factura=$res_asoc->RecordCount();
			if ($filas>0){ //armo un arreglo con los id  y las fechas asociados a la orden
			  for ($i=0;$i<$filas;$i++){
			     $aux=array();
				 $aux[0]=$res_asoc->fields['id_factura'];
				 $aux[1]=$res_asoc->fields['fecha_emision'];
		         $list[$i]=$aux;
				 $res_asoc->MoveNext();
		      }
		    }

			for ($i=0; $i<$cant_factura;$i++)
			{
				$id=cargar($list);
				if ($datos_orden->fields['nro_factura']) $value=$datos_orden->fields['nro_factura'];
				   else
				  $value=$_POST["id_factura_$i"] or $value=$id[0];

				$query_arch="select * from arch_prov where id_factura=$value";
		        $res_arch=sql($query_arch,"al seleccionar archivos") or fin_pagina();

				if ($datos_orden->fields['fecha_factura']) $value_fecha=Fecha($datos_orden->fields['fecha_factura']);
			    else
				 $value_fecha= $_POST["fecha_factura_$i"] or $value_fecha=Fecha($id[1]);
				 //Fecha($parametros['fecha']) or
				 ?>
				 <tr>
			    <td align="center"> <input name="ver_factura_<?=$i?>" type="button"  value="ir" title='ver detalles de la factura'  onclick="<? if ($datos_orden->fields['nro_factura']) {?>alert ('La factura asociada no está cargada'); return false; <? }?> if (document.all.id_factura_<?=$i?>.value=='') return false; window.open('<?=encode_link("../factura_proveedores/fact_prov_subir.php",array("nro_orden"=>$nro_orden,"estado"=>$estado,"fila"=>$i)) ?>&id_fact='+document.all.id_factura_<?=$i ?>.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=50,top=30,width=700,height=400')">
			      <input name="id_factura_<?=$i?>" type="text" value="<?=$value?>" readonly>
			    </td>
			    <td align="center"><input name="fecha_factura_<?=$i ?>" <?= $permiso ?> readonly="true" type="text" value="<?=$value_fecha;?>" size="10">
			    </td>
			    <td>
			    <?
			    while (!$res_arch->EOF) {
			     if (is_file("../../uploads/facturacion/".$value.'-'.$res_arch->fields["nbre_arch"]))
			     {
                  echo "<a href='".encode_link("./../factura_proveedores/fact_prov_subir.php",array ("rma"=>1,"fact"=>$value,"file" =>$res_arch->fields["nbre_arch"],"size" => $res_arch->fields["tam_arch"],"cmd" => "download","fila"=>$parametros['fila']))."'>";
                  echo $res_arch->fields["nbre_arch"]."</a>";
                  echo"<br>";
                  $i=1;
			     }
			    $res_arch->MoveNext();
			    }
			    if($i!=1)
			    {
			     echo"&nbsp;&nbsp; ";
			    }
			    ?>
			    </td>
			   </tr>
		   <?


		   }//de for ($i=0; $i<$cant_factura;$i++)
		   ?>
		 </table>
	</td>
    </tr>
  </table>
</tr></td>
<?}?>
<TR>
  <td width=100% colspan=2>
  <br>
	<TABLE width="100%"%" align="center" class="bordes">
	<tr id="mo">
	<TD colspan="2">Comentarios</TD>
	</tr>
	<TR id="ma">
	<td>
	<?if ($codigo<>$codigo_padre) {
		$sql_auxil = "select comentario from codigos_barra where codigo_barra = '$codigo_padre'";
		$result_auxil = sql($sql_auxil,"Error en traer el comentario del codigo de barra padre...") or fin_pagina();
		if ($result_auxil->RecordCount()>0) $comentario = $result_auxil->fields("comentario");
	}?>
	<TEXTAREA cols="80"   rows="5" name="texto_comentario" onchange="document.all.comentario.disabled = 0;"><?=$comentario;?></TEXTAREA>
	</TD>
	<TD width="20%">
	<INPUT type="hidden" name="code" value="<?=$codigo;?>">
	<INPUT type="hidden" name="codigo_comentario" value="<?=$codigo_padre;?>">
<?
	if(permisos_check("inicio","permiso_boton_hermanar_cb"))
		$disabled = "";
	else
		$disabled = "disabled";
?>
	<INPUT type="submit" value="Guardar Comentario" name="comentario"  <?=$disabled?>>
	</TD>
	</TR>
	</TABLE>
</TR>
</TABLE>
</form>
<br>
<center>
<?
if(permisos_check("inicio","permiso_boton_borrar_cb"))
{
 $link_borrar=encode_link("productos_codigob_descripcion.php",array("code"=>$codigo,"borrar"=>1,"producto_nombre"=>"$desc_gral","nro_orden"=>"$oc"));
?>
 <input type="button" name="borrar" value="Borrar" onclick="if(confirm('¿Está seguro que desea borrar el código de barras?'))document.location='<?=$link_borrar?>'">
<?
}
?>
<input type="button" name="volver" value="Volver" onclick="document.location='productos_codigob.php'">
</center>
<br>
<?
fin_pagina();
?>