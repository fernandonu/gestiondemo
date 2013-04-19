<?
/*AUTOR: MAC - FECHA: 01/07/04

MODULO DE MOVIMIENTO DE MATERIAL (mov_material) ENTRE DEPOSITOS DE LA EMPRESA

MODIFICADO POR:
$Author: ferni $
$Revision: 1.41 $
$Date: 2007/06/22 17:44:52 $
*/

require_once("../../config.php");

$msg=$parametros['msg'];
$pedido_material = $_GET["pedido_material"] or $pedido_material = $parametros["pedido_material"];


//actualiza el campo producto_pedido de la tabla movimiento_material con los productos del PM
if ($_POST['act_script']=='Script Actualizar'){
	$db->StartTrans();
	$sql = "select * from mov_material.movimiento_material where es_pedido_material=1";
    $result_1 = sql($sql) or fin_pagina();

    while (!$result_1->EOF){
    	$id_mov1=$result_1->fields['id_movimiento_material'];
    	$sql = "select * from mov_material.detalle_movimiento where id_movimiento_material=$id_mov1";
    	$result_2 = sql($sql) or fin_pagina();
    	$productos_cargar="";
    		while (!$result_2->EOF){
    			$productos_cargar=$productos_cargar . $result_2->fields['descripcion'] . "\n";
    			$result_2->movenext();
    		}
    	$sql = "update mov_material.movimiento_material set producto_pedido='$productos_cargar'
    		where id_movimiento_material=$id_mov1";
    	sql($sql) or fin_pagina();
    	$result_1->movenext();
    }
    $db->CompleteTrans();
}

if($_POST["Autorizar"]=="Autorizar")
{include_once("func.php");
	$db->StartTrans();
	$datos=PostvartoArray("check_autorizar_");
	if ($datos) {
	foreach($datos as $key => $value) {
		autorizar_PM_MM($value,$_POST["comentarios_".$value]);
		
		$sql = " select produccion_sl,producto_sl,rma_producto_sl from
		                movimiento_material where id_movimiento_material = $value";
		$res = sql($sql) or fin_pagina();
		$pm_produccion_sl   = $res->fields["produccion_sl"];
		$pm_produccto_sl    = $res->fields["producto_sl"];
		$pm_rma_producto_sl = $res->fields["rma_producto_sl"];
        if ($pm_produccion_sl || $pm_producto_sl || $pm_rma_producto_sl)
               autorizar_entregar_pm($value);        
		
	}
	

	if($pedido_material)
		echo "<div align='center'><b>Los Pedidos de Material seleccionados se autorizaron con éxito<b></div>";
	else
		echo "<div align='center'><b>Los Movimientos de Material seleccionados se autorizaron con éxito<b></div>";
	}
	else 	echo "<div align='center'><font color='red'><b>ERROR:Seleccione al menos una fila para autorizar<b></font></div>";
	$db->CompleteTrans();
	
}//de if($_POST["Autorizar"]=="Autorizar")


if($_POST["Anular"]=="Anular")
{include_once("func.php");
	$db->StartTrans();
	$datos=PostvartoArray("check_autorizar_");
	if ($datos) {
	foreach($datos as $key => $value) 
	{
		anular_PM_MM($value,$_POST["comentarios_".$value]);

	}//de foreach
	if($pedido_material)
		echo "<div align='center'><b>Los Pedidos de Material seleccionados se anularon con éxito<b></div>";
	else
		echo "<div align='center'><b>Los Movimientos de Material seleccionados se anularon con éxito<b></div>";
	}
	else echo "<div align='center'><font color='red'><b>ERROR:Seleccione al menos una fila para Anular<b></font></div>";
	$db->CompleteTrans();
}//de if($_POST["Autorizar"]=="Autorizar")

echo $html_header;


if ($parametros['volver_casos'] || $parametros['volver_lic']) {
	phpss_svars_set("_ses_mov_material",$parametros);
}

$var_sesion=array(
                  "cb_tipo_pm"=>-1
				  );
				  
variables_form_busqueda("mov_material",$var_sesion);

if ($cmd == "")
{
	$cmd="para_autorizar";
	$_ses_mov_material["cmd"]=$cmd;
	phpss_svars_set("_ses_mov_material", $_ses_mov_material);
}

/*
if($parametros["pedido_material"])
{

	$_ses_mov_material["pedido_material"]=$pedido_material;
    phpss_svars_set("_ses_mov_material", $_ses_mov_material);
}*/


$datos_barra = array(
					array(
						"descripcion"	=> "Pendiente",
						"cmd"			=> "pendiente",
						"extra"			=> array("pedido_material"=>$pedido_material)
						),
					array(
						"descripcion"	=> "Para Autorizar",
						"cmd"			=> "para_autorizar",
						"extra"			=> array("pedido_material"=>$pedido_material)
						),
					array(
						"descripcion"	=> "Autorizados",
						"cmd"			=> "autorizados",
						"extra"			=> array("pedido_material"=>$pedido_material)
						)
						,
				    array(
						"descripcion"	=> "Todos",
						"cmd"			=> "todos",
						"extra"			=> array("pedido_material"=>$pedido_material)
						)
				 );
echo "<br>";
generar_barra_nav($datos_barra);
$link_form=encode_link("listado_mov_material.php",array("pedido_material"=>$pedido_material));
?>
<script>
function chk_todos(elegir){
 var valor,cant,i;

 if(elegir.checked==true) valor=true;
 else valor=false;

 cant=document.all.total_chk.value; //cantida de checkbox

 for (i=0;i<cant;i++) {
   c=eval("document.all.check_autorizar_"+i);
   c.checked=valor;
 }
}//de la funcion
</script>

<form name="form1" method="POST" action="<?=$link_form?>">

<?
//entra si es llamado en adminitracion->productos->stock->movimiento de material->listado de movimiento
if ($pedido_material!=1)
{
	$orden = array(
			"default" => "2",
			"default_up" => "0",
			"1" => "id_movimiento_material",
			"2" => "fecha_creacion",
			"3" => "titulo",
			"3" => "deposito_origen",
			"4" => "deposito_destino",
			"6" => "precio"
		);


	//los campos origen y destino, hacen referencia
	//al nombre del deposito de origen y destino , pero son renombres del campo nombre
	//en la tabla depositos, ya que se hace un doble join con dicha tabla
	$filtro = array(
			"id_movimiento_material"=>"Nro",
	        "movimiento_material.titulo" => "Título",
			"origen" => "Depósito Origen",
			"destino" => "Depósito Destino"

		);
}//de if ($pedido_material!=1)
//si es llamado en administracion->compra/venta->Pedido de Material
else
{
	$orden = array(
			"default" => "2",
			"default_up" => "0",
			"1" => "id_movimiento_material",
			"2" => "fecha_creacion",
			"3" => "id_licitacion",
			"4" => "iniciales",
			"5" => "nombre_cliente",
			"6" => "deposito_origen",
			"7" => "deposito_destino",
			"8" => "precio"
		);


	//los campos origen y destino, hacen referencia
	//al nombre del deposito de origen y destino , pero son renombres del campo nombre
	//en la tabla depositos, ya que se hace un doble join con dicha tabla
	$filtro = array(
	        "id_movimiento_material"=>"Nro",
	        "nombre_cliente" => "Nombre del Cliente",
			"origen" => "Depósito Origen",
			"destino" => "Depósito Destino",
			"nrocaso" => "Caso",
			"id_licitacion" => "ID Licitacion"
		);
}//del else de if ($pedido_material!=1)

$query="select movimiento_material.estado,movimiento_material.fecha_creacion,origen,destino,id_movimiento_material,
		movimiento_material.titulo,idcaso,nrocaso,nombre_cliente,movimiento_material.comentarios,
		movimiento_material.id_licitacion,lider, usuarios.iniciales ,acumulado_servicio_tecnico";
//if($cmd=="autorizados" || $cmd=="todos")
$query.=",prod_recibidos,prod_enviados,precio_pm.precio, movimiento_material.producto_pedido ";

$query.="from (
				mov_material.movimiento_material
				join ( select id_deposito,nombre as origen
						from general.depositos) as orig
				on orig.id_deposito=movimiento_material.deposito_origen)
          join (select id_deposito,nombre as destino from general.depositos) as dest
          on dest.id_deposito=movimiento_material.deposito_destino ";
//revisamos si faltan recibir productos para el movimiento (en caso de que sea
//estado autorizados, o todos
//if($cmd=="autorizados" || $cmd=="todos"){
$query.="left join
       (select id_movimiento_material,sum(enviados) as prod_enviados,sum(recibidos) as prod_recibidos
         from (select id_movimiento_material,id_detalle_movimiento,sum(cantidad) as enviados
                from mov_material.detalle_movimiento group by id_movimiento_material,id_detalle_movimiento) as env
        left join
        (select id_detalle_movimiento,sum(cantidad) as recibidos
          from mov_material.recibidos_mov
          where ent_rec=0
          group by id_detalle_movimiento) as rec
         using (id_detalle_movimiento)
        group by id_movimiento_material
       )as recib_env
       using (id_movimiento_material)
	   left join casos.casos_cdr
       using(idcaso)
       left join licitaciones.licitacion
	   using (id_licitacion)
	   left join sistema.usuarios
	   on lider=usuarios.id_usuario
";
// Para poder obtener el precio del pedido de material
$query.="left join (select id_movimiento_material,sum(precio * cantidad) as precio
         from mov_material.detalle_movimiento
         group by id_movimiento_material) as precio_pm using (id_movimiento_material)";


//}//de if($cmd=="autorizados" || $cmd=="todos")

//entra si es llamado en adminitracion->productos->stock->movimiento de material->listado de movimiento
if($pedido_material!=1){
		if($cmd=="pendiente")
		{$where=" estado=0";
		 $contar="select count(*) from movimiento_material where estado=0 and es_pedido_material<>1";
		}
		elseif($cmd=="para_autorizar")
		{$where=" estado=1";
		 $contar="select count(*) from movimiento_material where estado=1 and es_pedido_material<>1";
		}
		elseif($cmd=="autorizados")
		{$where=" estado=2";
		 $contar="select count(*) from movimiento_material where estado=2 and es_pedido_material<>1";
		}
		else
		 $contar="select count(*) from movimiento_material where es_pedido_material<>1";
}
//si es llamado en administracion->compra/venta->Pedido de Material
else{
		 if($cmd=="pendiente")
		{$where=" estado=0";
		 $contar="select count(*) from movimiento_material where estado=0 and es_pedido_material=1";
		}
		elseif($cmd=="para_autorizar")
		{$where=" estado=1";
		 $contar="select count(*) from movimiento_material where estado=1 and es_pedido_material=1";
		}
		elseif($cmd=="autorizados")
		{$where=" estado=2";
		 $contar="select count(*) from movimiento_material where estado=2 and es_pedido_material=1";
		}
		else
		 $contar="select count(*) from movimiento_material where es_pedido_material=1";
}

?>
<table width="95%" align="center">
 <tr>
  <td>
	<?
	if($pedido_material==1)
	{
	 $link_nuevo_pedido_material=encode_link("detalle_movimiento.php",array("modo"=>"asociar","pedido_material"=>$pedido_material));
	 ?>
	<input type="button" name="nuevo_pedido_material" value="Nuevo Pedido de Material" onclick="document.location.href='<?=$link_nuevo_pedido_material?>'">
	<?
	}//de if($pedido_material==1)
	else 
	{
	 $link_nuevo_mov_material=encode_link("detalle_movimiento.php",array());
	 ?>
	<input type="button" name="nuevo_mov_material" value="Nuevo Movimiento de Material" onclick="document.location.href='<?=$link_nuevo_mov_material?>'">
	<?
	} 
    ?>
  </td>
  <td>
	<?
	if($_POST['keyword'] || $keyword)// en la variable de sesion para keyword hay datos)
	     $contar="buscar";


	if($pedido_material==1)
	{
	  if($where!="")
	    $where.=" and";

	  $where.=" es_pedido_material=1";
	}
	else
	{
	  if($where!="")
	    $where.=" and";

	  $where.=" (es_pedido_material=0 or es_pedido_material isnull)";
	}
	$link_tmp = array("pedido_material"=>$pedido_material);
	
	if($pedido_material==1){		
		switch ($cb_tipo_pm){
		case "-1":
 			$where.="";
        	break;
 		case "0":
 			$where.=" and id_licitacion <> 0";
        	break;
        case "1":
 			$where.=" and movimiento_material.es_presupuesto <> 0";
        	break;
        case "2":
 			$where.=" and idcaso <> 0";
        	break;
        case "3":
 			$where.=" and acumulado_servicio_tecnico <> 0";
        	break;
        case "4":
 			$where.=" and movimiento_material.es_presupuesto = 0 and acumulado_servicio_tecnico = 0 and produccion_sl = 0  and producto_sl = 0 and rma_producto_sl = 0 and auditoria_ckd = 0 and venta_directa = 0 and id_licitacion is null and idcaso is null";
        	break;
        case "5":
 			$where.=" and produccion_sl <> 0";
        	break;
        case "6":
 			$where.=" and producto_sl <> 0";
        	break;
        case "7":
 			$where.=" and rma_producto_sl <> 0";
        	break;
        case "8":
 			$where.=" and auditoria_ckd <> 0";
        	break;
        case "9":
 			$where.=" and venta_directa <> 0";
        	break;  		
		}     
	}
	
	list($sql,$total_mov_material,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,$contar);
	$result = sql($sql) or fin_pagina();	
	?>
	&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>
	<?if ($_ses_user['login']=='ferni'){?>
		<input type=submit name=act_script value='Script Actualizar'>
	<?}
	
	if($pedido_material==1){
		$tipos_pm=$cb_tipo_pm?>			
 		&nbsp;<b>TipoPM</b>&nbsp;
     	<select name="cb_tipo_pm"
      		onKeypress="buscar_op(this);"
      		onblur="borrar_buffer();"
      		onclick="borrar_buffer();">
      		<option value="-1"<?if($tipos_pm==-1) echo "selected"?>>Todos</option>
      		<option value="0" <?if($tipos_pm==0) echo "selected"?>>Licitación</option>       
      		<option value="1" <?if($tipos_pm==1) echo "selected"?>>Presupuesto</option>       
      		<option value="2" <?if($tipos_pm==2) echo "selected"?>>Caso de Servicio Técnico</option>       
      		<option value="3" <?if($tipos_pm==3) echo "selected"?>>Acumulado Servicio Técnico</option>       
      		<option value="4" <?if($tipos_pm==4) echo "selected"?>>No asociado</option>       
      		<option value="5" <?if($tipos_pm==5) echo "selected"?>>Producción San Luis</option>       
      		<option value="6" <?if($tipos_pm==6) echo "selected"?>>Producto San Luis</option>       
      		<option value="7" <?if($tipos_pm==7) echo "selected"?>>RMA - Producto San Luis</option>       
      		<option value="8" <?if($tipos_pm==8) echo "selected"?>>PM de auditoria CKD Monitores</option>       
      		<option value="9" <?if($tipos_pm==9) echo "selected"?>>PM Venta Directa</option>             		
      	</select>&nbsp;
     <?}?>
          
  </td>
 </tr>
</table>

<br>
<?if ($msg) Aviso($msg);?>
<?
if($cmd=="autorizados" || $cmd=="todos")
{?>
<table width="95%" align="center" bgcolor="White">
 <tr>
  <td align="center" colspan="4">
   <b>Referencias de Colores</b>
  </td>
 </tr>
 <tr>
  <td width="3%" bgcolor="#FFFFCA">&nbsp;
  </td>
  <td>
   No se recibió ningún producto
  </td>
  <td width="3%" bgcolor="red">&nbsp;
  </td>
  <td>
   Faltan recibir algunos productos
  </td>
  </tr>
</table>
<?
}//de if($cmd=="autorizados" || $cmd=="todos")
$suma_total=0;
$result->MoveFirst();
while (!$result->EOF) {
	$suma_total+=$result->fields["precio"];
	$result->movenext();
}
$result->MoveFirst();

?>
<input type="hidden" name="total_chk" value='<?=$total_mov_material?>'>
<table border=0 width="95%" align="center" cellpadding="3" cellspacing='0' bgcolor=<?=$bgcolor3?>>
 <tr id=ma>
    <td align="left">
     <b>Total:</b> <?=$total_mov_material?>
     <?
	 if(permisos_check("inicio","permiso_autorizar_mov") && ($cmd=="para_autorizar" || $cmd=="pendiente"))
	 {
	   ?>
	   <input type="submit" name="Autorizar" value="Autorizar">
	   <?
	 }
	 ?>
    </td>
    <td align=center> Precio Total: U$S <?=formato_money($suma_total)?>  </td>
	<td align=right>
	 <?=$link_pagina?>
	 <?
	 if(permisos_check("inicio","permiso_autorizar_mov") && ($cmd=="para_autorizar" || $cmd=="pendiente"))
	 {
		 ?>
		 <input type="submit" name="Anular" value="Anular">
		 <?
	 }
	 ?>
	</td>
  </tr>
</table>

<?//entra si es llamado en adminitracion->productos->stock->movimiento de material->listado de movimiento
if ($pedido_material!=1)
{?>
<table width='95%' border='0' cellspacing='2' align="center">
<tr id=mo>
 <?
 if($cmd=="para_autorizar" || $cmd=="pendiente")
 {
 ?>
 <td width="1%"><input class="estilos_check" type="checkbox" title="Seleccionar Todos" name="check_todos" value=1 onclick="chk_todos(this)"></td>
 <?
 }
 ?>
 <td width='1%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"1","up"=>$up))?>'>ID</a></b></td>
 <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"2","up"=>$up))?>'>Fecha Creación</a></b></td>
 <td width='40%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"3","up"=>$up))?>'>Título</a></b></td>
 <td width='25%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"4","up"=>$up))?>'>Depósito Origen</a></b></td>
 <td width='25%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"5","up"=>$up))?>'>Depósito Destino</a></b></td> 
 <?
if($cmd=="autorizados" || $cmd=="todos")
 {?>
 <td><b>Enviados/<br>Recibidos</b></td>
<?
 }
?>
 <td width='25%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"6","up"=>$up))?>'>Montos</a></b></td>
</tr>
<?
$i=0;
while(!$result->EOF)
{
$color_env_rec="";
$link = encode_link("detalle_movimiento.php",array("pagina"=>"listado","id"=>$result->fields["id_movimiento_material"]));
if($result->fields['prod_recibidos']==0)//no se recibio ningun producto
 $color_env_rec="#FFFFCA";
elseif(($result->fields['prod_enviados']-$result->fields['prod_recibidos'])==0)//se recibieron todos los productos
 $color_env_rec="";
else //faltan recibir algunos productos
 $color_env_rec="red";

 $prod_pm= $result->fields['producto_pedido'];
if ($result->fields['estado'] != 3){
	$color_line = $bgcolor_out;
	$extra_line = "title= '$prod_pm'";
	}
	else{
	$color_line = '#CC9999';
	$extra_line = " title = 'Movimiento de Material anulados - $prod_pm' ";
	}

//tr_tag($link,$extra_line,$color_line);
 ?>
 <tr <?=$extra_line?> bgcolor="<?=$color_line?>" style="cursor:hand">
 <?
  if($cmd=="para_autorizar" || $cmd=="pendiente")
  {
  	?>
  	<td align="center">
  	 <input type="checkbox" class="estilos_check" name="check_autorizar_<?=$i++?>" value="<?=$result->fields['id_movimiento_material']?>">
  	 <input type="hidden" name="comentarios_<?=$result->fields['id_movimiento_material']?>" value="<?=$result->fields['comentarios']?>">
    </td>
  	<?
  }
?>
  <a href="<?=$link?>">
  <td align="center">
   <?=$result->fields['id_movimiento_material']?>
  </td>
  <td align="center">
   <?=fecha($result->fields['fecha_creacion']);?>
  </td>
  <td>
   <?=$result->fields['titulo']?>
  </td>
  <td>
   <?=$result->fields['origen']?>
  </td>
  <td>
   <?=$result->fields['destino']?>
  </td>
  <?
 if($cmd=="autorizados")
 {?>
  <td align="center" bgcolor="<?=$color_env_rec?>">
   <?=$result->fields['prod_enviados']?>/<?echo ($result->fields['prod_recibidos'])?$result->fields['prod_recibidos']:0;?>
  </td>
 <?
 }
 if($cmd=="todos")
 {
 if ($result->fields['estado'] != 3) {	?>
  <td align="center" bgcolor="<?=$color_env_rec;?>">
  <?
   echo $result->fields['prod_enviados']?>/<?echo ($result->fields['prod_recibidos'])?$result->fields['prod_recibidos']:0;
  ?>
 </td>
 <? } else {?>
  <td align="center">
    --/--
  </td>
 <?
 }
 }
 ?>
  <td>
   U$S <?=formato_money($result->fields['precio'])?>
  </td>
 </tr>
 </a>
 <?
 $result->MoveNext();
}//del while
?>
</table>

<?
}//de if ($pedido_material!=1)
//si es llamado en administracion->compra/venta->Pedido de Material
else
{?>
<table width='95%' border='0' cellspacing='2' align="center">
<tr id=mo>
 <?
 if($cmd=="para_autorizar" || $cmd=="pendiente")
 {
 ?>
 <td width="1%"><input class="estilos_check" type="checkbox"  title="Seleccionar Todos" name="check_todos" value=1 onclick="chk_todos(this)"></td>
 <?
 }
 ?>
 <td width='1%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"1","up"=>$up))?>'>Nro.</a></b></td>
 <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"2","up"=>$up))?>'>Fecha Creación</a></b></td>
 <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"3","up"=>$up))?>'>ID Lic./CASO</a></b></td>
 <td width='5%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"4","up"=>$up))?>'>Lider Lic.</a></b></td>
 <td width='35%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"5","up"=>$up))?>'>Cliente</a></b></td>
 <td width='15%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"6","up"=>$up))?>'>Depósito Origen</a></b></td>
 <td width='15%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"7","up"=>$up))?>'>Depósito Destino</a></b></td>
 <td width='9%'><b><a id=mo>Entregados</a></b></td>
 <td width='15%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("pedido_material"=>$pedido_material,"sort"=>"8","up"=>$up))?>'>Montos</a></b></td>
</tr>
<?
$i=0;
while(!$result->EOF)
{
$color_env_rec="";
$link = encode_link("detalle_movimiento.php",array("pagina"=>"listado","id"=>$result->fields["id_movimiento_material"]));
if($result->fields['prod_recibidos']==0)//no se recibio ningun producto
 $color_env_rec="#FFFFCA";
elseif(($result->fields['prod_enviados']-$result->fields['prod_recibidos'])==0)//se recibieron todos los productos
 $color_env_rec="";
else //faltan recibir algunos productos
 $color_env_rec="red";

 $prod_pm= $result->fields['producto_pedido'];
if ($result->fields['estado'] != 3){
	$color_line = $bgcolor_out;
	$extra_line = " title = '$prod_pm'";
	}
	else{
	$color_line = '#CC9999';
	$extra_line = " title = 'Pedido de Material anulado - $prod_pm' ";
	}

//tr_tag($link,$extra_line,$color_line);
 ?>
 <tr <?=$extra_line?> bgcolor="<?=$color_line?>" style="cursor:hand">
 <?
  if($cmd=="para_autorizar" || $cmd=="pendiente")
  {
  	?>
  	<td align="center">
  	 <input type="checkbox" class="estilos_check" name="check_autorizar_<?=$i++?>" value="<?=$result->fields['id_movimiento_material']?>">
  	 <input type="hidden" name="comentarios_<?=$result->fields['id_movimiento_material']?>" value="<?=$result->fields['comentarios']?>">
    </td>
  	<?
  }
?>
  <a href="<?=$link?>">
  <td align="center">
   <?=$result->fields['id_movimiento_material']?>
  </td>
  <td align="center">
   <?=fecha($result->fields['fecha_creacion']);?>
  </td>
  <td>
   <?
   	if ($result->fields['id_licitacion']!='')
   	{
   		echo $result->fields['id_licitacion'];
   	}
   	else
   	{
   		echo $result->fields['nrocaso'];
   	}
   ?>
  </td>
  <td>
   	<?echo $result->fields['iniciales'];?>
  </td>
  <td>
   	<?echo $result->fields['nombre_cliente'];?>
  </td>
  <td>
   <?=$result->fields['origen']?>
  </td>
  <td>
   <?=$result->fields['destino']?>
  </td>
  <?
 if($cmd!="todos")
 {?>
  <td align="center" bgcolor="<?=$color_env_rec?>">
   <?
    if ($result->fields["estado"]==2 && $result->fields["acumulado_servicio_tecnico"])
    {
      echo $result->fields['prod_enviados'];?>/<? echo $result->fields['prod_enviados'];    	
    }   
    else {
      echo ($result->fields['prod_recibidos'])?$result->fields['prod_recibidos']:0;?>/<?=($result->fields['prod_enviados'])?$result->fields['prod_enviados']:0;
    }
   ?>
  </td>
 <?
 }

 if($cmd=="todos")
 {
	 if ($result->fields['estado'] != 3)
	 {
?>
	  <td align="center" bgcolor="<?=$color_env_rec;?>">
	  <?if ($result->fields['estado']==2 && $result->fields["acumulado_servicio_tecnico"]) {  
           echo $result->fields['prod_enviados']?>/<? echo $result->fields['prod_enviados']; 
	   } 	    
       else {
	       echo ($result->fields['prod_recibidos'])?$result->fields['prod_recibidos']:0;?>/<?=($result->fields['prod_enviados'])?$result->fields['prod_enviados']:0;
       }
       ?>
	  </td>
	 <?
	 }
	 else
	 {?>
	  <td align="center">
	    --/--
	  </td>
	 <?
	 }
 }
 ?>
 <td>

   U$S <?=formato_money($result->fields['precio'])?>
  </td>
  </a>
 </tr>
 <?
 $result->MoveNext();
}//del while
?>
</table>

<?
}//del else de if ($pedido_material!=1)

if ($parametros["volver_lic"]) {
		$ref = encode_link($html_root."/index.php",array("menu" => "licitaciones_view","extra" => array("cmd1"=>"detalle","ID"=>$parametros["volver_lic"])));
		echo "<br><div align=center>
             <input type=button name=volver style='width:320;' value='Volver a los detalles de la licitacion' onClick=\"window.close();\">
             </div>\n";
}

if ($parametros['volver_casos'] || $parametros['boton_cerrar']  ) {
?>
<br>
<div align='center'>
<input type='button' name='cerrar' value='Cerrar' onclick="window.close();">
</div>
<?}

/*if($_ses_user["login"]=="marcos")
{
?>
 <input type="submit" name="entidad_lic" value="ENTIDAD_LIC" onclick="window.open('pasar_entidad_pm.php')"><br>
<?
}*/
?>
</form>
</body>
</html>
<?fin_pagina();?>