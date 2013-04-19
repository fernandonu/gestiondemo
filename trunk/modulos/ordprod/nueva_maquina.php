<?
/*
AUTOR: Carlitos
MODIFICADO POR:
$Author: mari $
$Revision: 1.26 $
$Date: 2006/10/31 15:45:04 $
*/

require_once("../../config.php");
//print_r($_POST);

variables_form_busqueda("ordenes_listar_archivos");

$nro_orden=$parametros["nro_orden"] or $nro_orden=$_POST["nro_orden"];

if ($_POST["cmd1"]=="Nuevo"){
	$sql="select nro_serie from maquina where nro_orden=$nro_orden";
	$res=sql($sql) or fin_pagina();
	$cont=1;	
	while ($cont<$_POST['pos'])
	{if ($_POST["seleccion_$cont"])
	    {
        	while ($fila=$res->fetchrow()) {
	      	$query[]="INSERT INTO drivers (nro_serie,id_archivo,sync) VALUES ('".$fila["nro_serie"]."',".$_POST["id_ar_$cont"].",1)";
    	          }
     	    //sql($query) or fin_pagina();
	    }
	$res->MoveFirst();    
	$cont++;
	}
	sql($query) or fin_pagina();
	aviso("Los datos se guardaron correctamente.");
	$_POST['modelo']="";
}


function llenarModelo(){
         global $db;
         echo "<option value='todo' selected>Todos los modelos</option>\n";
         $sql="select distinct modelo from archivo_drivers";
         $rs=$db->execute($sql) or die($db->errormsg()." - ".$sql);
         while ($fila=$rs->fetchrow()) {
                echo "<option value='".$fila["modelo"]."' ";
                if ($_POST["modelo"]==$fila["modelo"]) echo "selected";
                echo ">".$fila["modelo"]."</option>\n";
         }
}
function llenardrivers(){
         global $db;
         $sql="select id_archivo,archivo from archivo_drivers";
         if ($_POST["modelo"] && $_POST["modelo"]!="todo") $sql.=" where modelo='".$_POST["modelo"]."'";
         $rs=$db->Execute($sql) or die($db->errormsg()." - ".$sql);
         while (!$rs->EOF) {
                echo "<option value='".$rs->fields["id_archivo"]."'>".$rs->fields["archivo"]."</option>\n";
                $rs->MoveNext();
         }
}
$sql="select orden_de_produccion.id_licitacion,orden_de_produccion.id_ensamblador"
	.",orden_de_produccion.fecha_inicio,orden_de_produccion.fecha_entrega,orden_de_produccion.lugar_entrega"
	.",orden_de_produccion.nserie_desde,orden_de_produccion.nserie_hasta,orden_de_produccion.cantidad"
	.",entidad.nombre,entidad.direccion,entidad.telefono,filas_ord_prod.descripcion as mdesc from ordenes.orden_de_produccion "
	."left join ordenes.filas_ord_prod using(nro_orden) "
	."left join producto_especifico using(id_prod_esp) "
	."left join entidad using(id_entidad) "
	."where nro_orden=$nro_orden and id_tipo_prod=1";
$licitacion=sql($sql) or fin_pagina();
if ($licitacion->recordcount() <= 0) {
	$sql="select orden_de_produccion.id_licitacion,orden_de_produccion.id_ensamblador"
	.",orden_de_produccion.fecha_inicio,orden_de_produccion.fecha_entrega,orden_de_produccion.lugar_entrega"
	.",orden_de_produccion.nserie_desde,orden_de_produccion.nserie_hasta,orden_de_produccion.cantidad"
	.",entidad.nombre,entidad.direccion,entidad.telefono,filas_ord_prod.descripcion as mdesc from ordenes.orden_de_produccion "
	."left join filas_ord_prod using(nro_orden) "
	."left join productos using(id_producto) "
	."left join entidad using(id_entidad) "
	."where nro_orden=$nro_orden and productos.id_tipo_prod=1";
	$licitacion=sql($sql) or fin_pagina();
}
$fechainicio=fecha($licitacion->fields["fecha_inicio"]);
$fechaentrega=fecha($licitacion->fields["fecha_entrega"]);
$cliente=$licitacion->fields["nombre"];
//$direccion=$licitacion->fields["direccion"];
$telefono=$licitacion->fields["telefono"];
$lugar_entrega=$licitacion->fields["lugar_entrega"];
$desc_prod=$licitacion->fields["titulo"];
$cant_prod=$licitacion->fields["cantidad"];
$serialp=$licitacion->fields["nserie_desde"];
$serialu=$licitacion->fields["nserie_hasta"];
$id_licitacion=$licitacion->fields["id_licitacion"];
$mdesc=$licitacion->fields["mdesc"];
if ($parametros["cmd1"]=="eliminar") {
	$sq="select sync from drivers left join maquina USING(nro_serie) WHERE nro_orden=$nro_orden and id_archivo=".$parametros["id_archivo"];
	$rs=sql($sq) or fin_pagina();
	$q="select id_drivers from drivers left join maquina USING(nro_serie) where nro_orden=$nro_orden and id_archivo=".$parametros["id_archivo"];
	$resu=sql($q) or fin_pagina();
	while ($fila=$resu->fetchrow()) {
		if ($rs->fields["sync"] && $rs->fields["sync"]==1)
			$query[]="DELETE FROM drivers WHERE id_drivers=".$fila["id_drivers"];
		else
			$query[]="UPDATE drivers SET sync=2 WHERE id_drivers=".$fila["id_drivers"];
	}
	//echo $query;
	sql($query) or fin_pagina();
	aviso("Los datos se borraron correctamente.");
}
echo $html_header;
?>
<br>
<form name='nuevo_drivers' action='nueva_maquina.php' method=post enctype='multipart/form-data'>
<input type=hidden name=volver value='<? echo $volver; ?>'>
<input type=hidden name=nro_orden value='<?=$nro_orden?>'>
<table width='95%' align='center'>
<tr>
	<tr id=mo><td colspan=2>
	<?$link_orden=encode_link('ordenes_nueva.php',array( "modo"=>"modificar","nro_orden"=>$nro_orden,"volver" =>"nueva_maquina.php" ));?>	
	<a target="_blank" href="<?=$link_orden?>"><font size=2>Datos de la Orden de Producción Nro: </font><font size=2 color="Blue"><u><? echo $nro_orden; ?></u></font></a>
	</td>
</tr>
<tr  bgcolor=<?=$bgcolor_out?>>
	<td>
	    <?$link2=encode_link('../licitaciones/licitaciones_view.php',array("ID"=>$id_licitacion,"cmd1"=>"detalle"));?>
		<a target="_blank" href="<?=$link2?>"><b>Orden de Producción Asociada a la Licitación ID: <? echo $id_licitacion; ?></b></a>
	</td>
	<td align='center'>
		<? $link_materiales=encode_link("seguimiento_orden_materiales_pm.php",array("id_licitacion"=>$id_licitacion,"mostrar_pedidos"=>1));
			if (permisos_check("inicio","seguimiento_boton_materiales")) { ?>
				<input type="button" name="boton_materiales" value="Materiales" onclick="window.open('<?=$link_materiales;?>','','left=40,top=80,width=700,height=300,resizable=1,status=1,scrollbars=1')" style="cursor:hand">&nbsp;&nbsp;&nbsp;
		<? } ?>
	</td>
</tr>
<tr  bgcolor=<?=$bgcolor_out?>>
	<td colspan=2>
	    <?/*left join licitaciones.licitacion USING (id_licitacion)
                left join licitaciones.entidad USING (id_entidad)*/
	      $sql="select id_renglon,id_fila,filas_ord_prod.garantia,filas_ord_prod.fact,id_producto,productos.desc_gral as producto,filas_ord_prod.cantidad,
	            filas_ord_prod.descripcion as desc_gral,proveedor.id_proveedor,proveedor.razon_social 
                from ordenes.orden_de_produccion 
                left join ordenes.filas_ord_prod using(nro_orden) 
			    left join general.productos using (id_producto)
			    left join general.proveedor using (id_proveedor) where id_renglon in 
                (select orden_de_produccion.id_renglon
                 from ordenes.orden_de_produccion 
                 left join licitaciones.renglon using(id_renglon) where nro_orden=$nro_orden
                ) and productos.tipo='placa madre'" ;
                /*$sql="select producto.id_producto,producto.tipo,producto.marca,producto.modelo,productos.desc_gral
                from licitaciones.producto
                left join general.productos USING (id_producto)
                left join licitaciones.renglon USING (id_renglon)
                where id_renglon in 
                (select orden_de_produccion.id_renglon
                 from ordenes.orden_de_produccion 
                 left join licitaciones.renglon using(id_renglon) where nro_orden=$nro_orden
                ) and productos.tipo='placa madre'
               ";*/
	       //$resul_madre=sql($sql,"No se puedo recuperar la placa madre") or fin_pagina();		   
        ?>	    
		Placa Madre: <b><? echo $mdesc; //$resul_madre->fields['desc_gral']; ?></b>
	</td>
</tr>
<tr bgcolor=<?=$bgcolor_out?>>
	<td>
		Fecha Inicio: <b><? echo $fechainicio; ?></b>
	</td>
	<td>
		Fecha Entrega: <b><? echo $fechaentrega; ?></b>
	</td>
</tr>
<tr id=mo>
	<td colspan=2>
		<font size=2>Datos del Cliente</font>
	</td>
</tr>
<tr bgcolor=<?=$bgcolor_out?>>
	<td>
		Cliente: <b><? echo $cliente; ?></b>
	</td>
	<td>
		Teléfono: <b><? echo $telefono; ?></b>
	</td>
</tr>
	<tr bgcolor=<?=$bgcolor_out?>>
	<td>
		<font size=2>Lugar de entrega: </font><br>
		<textarea cols=55 readonly rows=5><? echo $lugar_entrega; ?></textarea>
	</td>
	<td>
		Nro de Serie: <font color=red><b><?=$serialp;?></b></font><br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color=red><b>...</b></font><br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color=red><b><?=$serialu;?></b></font>
	</td>
</tr>
<?
$orden = array(
"1" => "drivers.nro_serie",
"2" => "archivo",
"3" => "modelo",
"4" => "descripcion",
"5" => "size"
);

$filtro = array(
"drivers.nro_serie"         => "Número de serie",
"archivo"       => "Archivo",
"modelo"      => "Modelo Mother",
"descripcion"       => "Descripción",
"size" => "Tamaño"
);
$sql_tmp="select drivers.id_archivo,drivers.nro_serie,nserie_desde,nserie_hasta,archivo,modelo,descripcion,size from drivers "
	."left join archivo_drivers USING(id_archivo)"
	."left join maquina USING(nro_serie) "
	."left join orden_de_produccion USING(nro_orden)";
$where_tmp="nro_serie='$serialp' and (drivers.sync is NULL or drivers.sync <> 2)";
?>
<tr bgcolor=<?=$bgcolor_out?> align=center>
	<td colspan=2>
		<br>
		<?
list($sql,$total_pedidos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
$resultado=sql($sql) or fin_pagina();
?>
		&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>
		<br>
		<table align="center" width="95%" cellspacing="2" cellpadding="2" class="bordes">
		<tr id=ma>
			<td align="left" colspan="2">
				<b>Total: <?=$total_pedidos?> Drivers Encontrado/s.</b>
			</td>
			<td align="right" colspan="4">
				<?=$link_pagina?>
			</td>
		</tr>
		<tr id=mo>
			<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up,"nro_orden"=>$nro_orden))?>'>Nro. Serie</a></b></td>
			<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up,"nro_orden"=>$nro_orden))?>'>Archivo</a></b></td>
			<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up,"nro_orden"=>$nro_orden))?>'>Modelo</a></b></td>
			<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up,"nro_orden"=>$nro_orden))?>'>Descripcion</a></b></td>
			<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up,"nro_orden"=>$nro_orden))?>'>Tamaño</a></b></td>
			<td><b>Func.</b></td>			
		</tr>
<?
		while ($fila=$resultado->FetchRow()) {
			//$ref=encode_link("nueva_maquina.php",Array("nro_orden"=>$fila["nro_orden"]));
			//tr_tag($ref,"Agregar drivers a las Maquinas.");
?>
		<tr id=ma>
			<td><? echo $fila["nserie_desde"]." ... ".$fila["nserie_hasta"];?></td>
			<td><? echo $fila["archivo"];?></td>
			<td><? echo $fila["modelo"];?></td>
			<td><? echo $fila["descripcion"];?></td>
<?
			$size=number_format(($fila["size"] / 1024));
?>
			<td><? echo $size ." Kb";?></td>
			<td><a href="<? echo encode_link("nueva_maquina.php",array("cmd1"=>"eliminar","id_archivo"=>$fila["id_archivo"],"nro_orden"=>$nro_orden)); ?>" alt="Quitar el drivers"><img src="../../imagenes/sin_desc.gif" border=0></a></td>
		<tr>
<?
		}
?>		
		<!--<tr>
			<td colspan=6 align=right>
				<select name=modelo OnChange='document.all.nuevo_drivers.submit();'>
				<? //llenarModelo(); ?>
				</select>
				<select name=nuevodriver>
				<? //llenardrivers(); ?>
				</select>
				<input type=submit name=cmd1 value='Nuevo'>&nbsp;&nbsp;
			</td>
		</tr>-->
		<tr>
		 <td colspan="6">
		  <table align="center">
           <tr>
            <td>
             <select name=modelo OnChange='document.all.nuevo_drivers.submit();'>
	         <? llenarModelo(); ?>
             </select>
            </td>
           </tr>
          </table>
         </td> 
       </tr>
      </td>
     </tr>
    </table>   
</table>

<?
 $control=$_POST['modelo'] or $control="todo";
 if ($control!="todo")
   {$sql="select id_archivo,archivo from maquinas.archivo_drivers where modelo='".$_POST["modelo"]."'";
    $rs=sql($sql,"Error al traer lso drivers") or fin_pagina();
?>
<table align="center" cellspacing="2" cellpadding="2" class="bordes">
<tr id="ma">
 <td colspan="2"><b>Seleccione los drivers a Subir</b></td>
</tr>
<tr id="mo">
 <td>&nbsp;</td>
 <td>Nombre Archivo</td>
</tr>
<?
 $pos=1;
 while (!$rs->EOF)
       {
       	?>
       	<tr bgcolor=<?=$bgcolor_out?>>
       	 <td><input name="seleccion_<?=$pos?>" type="checkbox" value="<?=$rs->fields['id_archivo']?>" checked></td>
       	 <input name="id_ar_<?=$pos?>" type="hidden" value="<?=$rs->fields['id_archivo']?>">
       	 <td><?=$rs->fields['archivo']?></td>
       	</tr>
       	<?
       	$pos++;
       	$rs->MoveNext();
       }?>
  <input name="pos" type="hidden" value="<?=$pos?>">     
 <tr bgcolor=<?=$bgcolor_out?>>
  <td colspan="2" align="center"><input type=submit name=cmd1 value='Nuevo'></td>
 </tr>      
</table>
<?       	  

}?>
</form>
<?
fin_pagina();
?>