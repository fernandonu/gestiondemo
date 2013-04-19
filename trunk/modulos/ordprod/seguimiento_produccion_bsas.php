<?PHP
/*
Autor Broggi

$Author: ferni $
$Revision: 1.22 $
$Date: 2006/12/18 18:17:04 $
*/

require_once("../../config.php");
//require_once("../ord_compra/fns.php");

if($_POST['borrar']) {
	//no lo muestra en el listado
	//actualiza el campo mostrar_en_produccion
$db->starttrans();
  $select=PostvartoArray('borrar_'); //crea un arreglo con los checkbox chequeados
  if ($select) {  //para ver si hay check seleccionados
  $list='(';
  foreach($select as $key => $value){
    $list.=$value.',';
  }
 $list=substr_replace($list,')',(strrpos($list,',')));
}
  $sql="update renglon set mostrar_en_produccion=0 where id_renglon in $list";
  $res=sql($sql,"$sql") or fin_pagina();
$db->completetrans();
}

variables_form_busqueda("seguimiento_produccion_bsas");

///////////////////////////// GABRIEL ////////////////////////////////////////
	if (($_POST["cambiar"])&&($_POST["cambiar_nro"])&&($_POST["estado_linea_produccion"])){
		$cambiar_nro=$_POST["cambiar_nro"];
		$estado_linea_produccion=$_POST["estado_linea_produccion"];
		$rta_consulta=sql("update licitaciones.linea_produccion_bsas set estado_linea_produccion='#AAFFAA' where nro_orden=
			values ($cambiar_nro, '".$_ses_user["login"]."', '$estado_linea_produccion')", "c47") or fin_pagina();
	}
	if(($_POST["mas_prioridad"])&&($_POST["cambiar_pop"])){
		$prioridad=substr($_POST["cambiar_pop"], strpos($_POST["cambiar_pop"], ",")+1);
		$op=substr($_POST["cambiar_pop"], 0, strpos($_POST["cambiar_pop"], ","));
		if ($prioridad<9)	sql("update licitaciones.linea_produccion_bsas set prioridad=".($prioridad+1)." where nro_orden= $op", "c70") or fin_pagina();
	}
	if(($_POST["menos_prioridad"])&&($_POST["cambiar_pop"])){
		$prioridad=substr($_POST["cambiar_pop"], strpos($_POST["cambiar_pop"], ",")+1);
		$op=substr($_POST["cambiar_pop"], 0, strpos($_POST["cambiar_pop"], ","));
		if ($prioridad>0)	sql("update licitaciones.linea_produccion_bsas set prioridad=".($prioridad-1)." where nro_orden= $op", "c70") or fin_pagina();
	}
	
	//prioridad para el estado a programar
	if ($_POST["mas_prioridad_prog"]) {
		$prioridad=$_POST["cambiar_prior"];
		$id_renglon=$_POST['id_renglon'];
		if ($prioridad < 9) {
		$sql="select id_prioridad from prioridad_prod_bsas where id_renglon=$id_renglon";
		$res=sql($sql,"$sql") or fin_pagina();
		if ($res->Recordcount()> 0 ) {
            $sql="update prioridad_prod_bsas set prioridad_prog=".($prioridad+1)." where id_renglon=$id_renglon";
		}
		 else {	
			$sql="insert into licitaciones.prioridad_prod_bsas (id_renglon,prioridad_prog) 
		          values ($id_renglon,".($prioridad+1).")";
		}
		sql($sql,"$sql") or fin_pagina();
		}
	}
	if ($_POST["menos_prioridad_prog"]) {
		$prioridad=$_POST["cambiar_prior"];
		$id_renglon=$_POST['id_renglon'];
		if ($prioridad > 0) {
		$sql="select id_prioridad from prioridad_prod_bsas where id_renglon=$id_renglon";
		$res=sql($sql,"$sql") or fin_pagina();
		if ($res->Recordcount()> 0 ) {
            $sql="update prioridad_prod_bsas set prioridad_prog=".($prioridad-1)." where id_renglon=$id_renglon";
		}
		 else {	
			$sql="insert into licitaciones.prioridad_prod_bsas (id_renglon,prioridad_prog) 
		          values ($id_renglon,".($prioridad-1).")";
		}
		sql($sql,"$sql") or fin_pagina();
		}
	}
	
	
//////////////////////////////////////////////////////////////////////////////

$datos_barra = array(
            array(
			      "descripcion"=> "A programar",
			      "cmd"=> "a_prog",
			     ), 
			array(
			      "descripcion"=> "Pendientes",
			      "cmd"=> "pendientes",
			     ),
			array(
			      "descripcion"=> "En Producción",
			      "cmd"=> "produccion"
			    ),
			array(
			      "descripcion"=> "En Inspección",
			      "cmd"=> "inspeccion",
			    ),
			array(
			      "descripcion"=> "En Embalaje",
			      "cmd"=> "embalaje",
			    ),
			array(
			      "descripcion"=> "Calidad",
			      "cmd"=> "calidad",
			    ),                 
			array(
			      "descripcion"=> "Historial",
			      "cmd"=> "historial"
			    )
			);
    if ($cmd == "") {
	$cmd="pendientes";
        phpss_svars_set("_ses_seg_ord_cmd", $cmd);
       }
echo $html_header; 
generar_barra_nav($datos_barra);

if ($cmd=='a_prog') {
$orden=array(
             "default" => "1",
             "default_up" => "1",
             "1"=>"licitacion.id_licitacion",
             "2"=>" usuarios.iniciales",
             "3"=>"entidad.nombre",
             "4"=>"renglon.cantidad",
             "5"=>"vence_oc",
             "6"=>"codigo_renglon",
             "7"=>"subido_lic_oc.nro_orden",
             "8"=>"prioridad_prog"
             );
             
$filtro=array(          
              "licitacion.id_licitacion" => "ID de licitación",
              "usuarios.iniciales"=>"Iniciales Lider",
              "entidad.nombre" => "Entidad",  
              "renglon.cantidad"=>"Cantidad Pc",   
              "vence_oc"=>"Vence Seg",      
              "codigo_renglon"=>"Renglón",
              "subido_lic_oc.nro_orden"=>"Nro OC",
              "prioridad_prog" => "Prioridad"
              );
        
$sql_tmp="select entidad.nombre,entidad.id_entidad,vence_oc,
          subido_lic_oc.nro_orden as nro_oc,codigo_renglon,
          renglon.cantidad,licitacion.id_licitacion,id_renglon,
		  usuarios.iniciales as lider_iniciales,
		  case when prioridad_prog is not null then prioridad_prog
          else 0 end as prioridad_prog
		  from licitaciones.licitacion
		  join licitaciones.entrega_estimada using(id_licitacion)
		  join licitaciones.entidad using(id_entidad)
		  join licitaciones.renglon using(id_licitacion)
		  join licitaciones.historial_estados using(id_renglon)
		  left join sistema.usuarios on usuarios.id_usuario=licitacion.lider
		  join licitaciones.subido_lic_oc using(id_entrega_estimada)
		  left join licitaciones.prioridad_prod_bsas using (id_renglon)"; 

$where_tmp="renglon.tipo ilike 'Computadora%'
            and id_estado_renglon=3 and activo=1
            and entrega_estimada.id_ensamblador=4
            and finalizada=0 and mostrar_en_produccion=1
            and id_renglon not in (select id_renglon
			from ordenes.orden_de_produccion
			where id_renglon is not null and 
            nro_orden not in (select nro_orden from muestras.ordprod_muestra)
            order by id_renglon) ";
}
else {
$orden = array(
       "default" => "7",
       "default_up" => "1",
       "1" => "ordenes.orden_de_produccion.id_licitacion",
       "2" => "ordenes.orden_de_produccion.nro_orden",
       //"3" => "licitaciones.subido_lic_oc.vence_oc",       
       "4" => "licitaciones.entidad.nombre",
       "5" => "ordenes.orden_de_produccion.fecha_entrega",  
       "6" => "ordenes.orden_de_produccion.cantidad",  
       "7"=>"lpb.prioridad DESC, orden_de_produccion.fecha_entrega"
      ); 
      
$filtro = array(        
        "licitaciones.entidad.nombre" => "Entidad",                
        "ordenes.orden_de_produccion.id_licitacion" => "ID de licitación",        
		"ordenes.orden_de_produccion.nro_orden" => "Número de orden",		
    );     
   
switch ($cmd) {
    case "pendientes" : $cond=" =0";break;
    case "produccion" : $cond=" =1";break;
    case "inspeccion" : $cond=" =5";break;
    case "embalaje" : $cond=" =3";break;
    case "calidad" : $cond=" =4";break;
    case "historial" : $cond=" =2";break;
}

$sql_tmp="select licitacion.id_estado,entidad.nombre,entidad.id_entidad,orden_de_produccion.fecha_entrega,id_licitacion,reprobo_calidad,reprobo_calidad_bsas,
		orden_de_produccion.nro_orden, usuarios.iniciales as lider_iniciales, 
		lpb.id_linea_produccion_bsas, usuario, fecha, estado_linea_produccion, lpb.comentario, prioridad,cuantas.cant,archivos,
		(tmp1.comprados - tmp1.recibido_entregado) as diferencia,orden_de_produccion.por_tanda,orden_de_produccion.cantidad as cant_total,
		case when por_tanda=0 then cantidad
            else tanda.cantidad_por_tanda end as cantidad
	from ordenes.orden_de_produccion 
    left join licitaciones.entidad using(id_entidad)
    left join licitaciones.licitacion using(id_licitacion)
    left join(select count(id_linea_produccion_bsas)as cant,nro_orden from licitaciones.linea_produccion_bsas group by nro_orden)
    as cuantas using (nro_orden)
    left join(select nro_orden, licitaciones.unir_texto(nombre||', ') as archivos
	      from ordenes.archivos_ordprod join general.subir_archivos on id_archivo=subir_archivos.id group by nro_orden)as archi using(nro_orden)
    LEFT JOIN sistema.usuarios on usuarios.id_usuario=licitacion.lider
    left join (select prod_bsas_por_tanda.nro_orden,prod_bsas_por_tanda.cantidad_por_tanda,
                prod_bsas_por_tanda.estado_bsas_por_tanda,prod_bsas_por_tanda.reprobo_calidad_bsas
           from ordenes.prod_bsas_por_tanda where estado_bsas_por_tanda $cond and cantidad_por_tanda <>0) as tanda
     using (nro_orden) 
    left join licitaciones.linea_produccion_bsas lpb on(orden_de_produccion.nro_orden=lpb.nro_orden)
		left join(
			select id_licitacion, sum(fila.cantidad) as comprados,
				case when sum(recibido_entregado.cantidad) is null then 0
					else sum(recibido_entregado.cantidad)
				end as recibido_entregado
			from compras.orden_de_compra
				join compras.fila using (nro_orden)
				left join compras.recibido_entregado using (id_fila)
				left join general.proveedor using (id_proveedor)
			where id_licitacion is not null
				and estado <> 'n' and razon_social not ilike '%Stock%' and (ent_rec=1 or ent_rec is null) and (es_agregado is null or es_agregado<>1)
			group by id_licitacion
		)as tmp1 using (id_licitacion)";
/*$sql_tmp="select entidad.nombre,entidad.id_entidad,orden_de_produccion.fecha_entrega,cantidad,id_licitacion,orden_de_produccion.nro_orden
          from ordenes.orden_de_produccion 
          left join licitaciones.entidad using(id_entidad)";*/
          //left join licitaciones.subido_lic_oc using(id_licitacion)";
if ($cmd=="pendientes") $where_tmp=" (estado_bsas is null) and estado!='AN'";
if ($cmd=="produccion") $where_tmp=" (estado_bsas=1 or tanda.estado_bsas_por_tanda=1) and estado!='AN'";
if ($cmd=="inspeccion") $where_tmp=" (estado_bsas=5 or tanda.estado_bsas_por_tanda=5) and estado!='AN'";
if ($cmd=="embalaje") $where_tmp=" (estado_bsas=3 or tanda.estado_bsas_por_tanda=3) and estado!='AN'";
if ($cmd=="calidad") $where_tmp=" (estado_bsas=4 or tanda.estado_bsas_por_tanda=4) and estado!='AN'";
if ($cmd=="historial") $where_tmp=" (estado_bsas=2 or tanda.estado_bsas_por_tanda=2) and estado!='AN'";
}
if ($cmd=='a_prog'){
   $contar="buscar";
}
else 
{ //$contar="buscar";
	$contar="select count(nro_orden)as total		
    from ordenes.orden_de_produccion 
    left join licitaciones.entidad using(id_entidad)
    left join licitaciones.licitacion using(id_licitacion)
    left join (select prod_bsas_por_tanda.nro_orden,prod_bsas_por_tanda.cantidad_por_tanda,
                prod_bsas_por_tanda.estado_bsas_por_tanda,prod_bsas_por_tanda.reprobo_calidad_bsas
           from ordenes.prod_bsas_por_tanda where estado_bsas_por_tanda $cond and cantidad_por_tanda <>0) as tanda
     using (nro_orden) ";
	$contar.="where $where_tmp";
 
}
if($_POST['keyword'] || $keyword)
{
     if(($filter!='all')&&($cmd!='a_prog'))
	{
    $contar.=" and $filter ILIKE '%$keyword%'";
	}
	
   else
   $contar="buscar";
}
?>
<script>
function cant_chequeados() {
var cant=0;
var i,sum=0;

cant=window.document.all.cant.value;

  for (i=0;i<cant;i++) {
  	c=eval("window.document.all.borrar_"+i);
  	if (typeof(c) !='undefined') {
	if (c.checked) {
  		sum++;
	}
  	}
}

if (sum > 0) return true;
else {
	 alert ('Debe seleccionar al menos un fila a eliminar');
	 return false;
}
}

</script>
<?$sql_est = "SELECT id_estado,nombre,color FROM estado";
$result = sql($sql_est) or die($db->ErrorMsg());
$estados = array();
while (!$result->EOF) {
	$estados[$result->fields["id_estado"]] = array(
		"color" => $result->fields["color"],
		"texto" => $result->fields["nombre"]
	);
	$result->MoveNext();
}
?>
<form name="produccion_bs_as" action='seguimiento_produccion_bsas.php' method='post'>
	<input type="hidden" name="cambio_nro" value="">
	<input type="hidden" name="estado_linea_produccion" value="">
	<input type="hidden" name="comentario" value="<?=$_POST["comentario"]?>">
	<input type="hidden" name="cambiar_pop" value="<?=$_POST["cambiar_pop"]?>">
	<input type="hidden" name="cambiar_prior" value="<?=$_POST["cambiar_prior"]?>">
	<input type="hidden" name="id_renglon" value="<?=$_POST["id_renglon"]?>">
<table align="center" >
 <tr>
  <td>
<?
 list($sql,$total_lic,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar); 
 $resul_consulta=sql($sql,"No se pudo realizar la consulta del form busqueda $sql ") or fin_pagina();
 $color_tanda="#5F9F9F";
 ?>
  </td>
   <input type='hidden' name="cant" value='<?=$total_lic?>'>
  <td>
   <input type=submit name=form_busqueda value='Buscar'>&nbsp;&nbsp;
   <?if (permisos_check("inicio", "permiso_reportes")){?>
   <input type="button" name=Reporte_1 value='Reporte 1' onclick="window.open('prod_bsas_reporte1.php')">
   <input type="button" name=Reporte_1 value='Reporte 2' onclick="window.open('prod_bsas_reporte2.php','DescriptiveWindowName','width=900,height=500,resizable,scrollbars=yes,status=1')">
   <?}?>
   <?
    if (permisos_check("inicio","permiso_borrar_prod_bsas") &&  $cmd=='a_prog') {?>
       <input type="submit" value="Borrar" name='borrar' onclick="return (cant_chequeados());">
    <?}?>
   
  </td>
 </tr>
</table> 
<br>
<?
if ($cmd=='a_prog') {
	$cantidad_registros=$resul_consulta->RecordCount();
	?>
<table width='100%' align="center" cellspacing="2" cellpadding="2" class="bordessininferior">
 <tr id=ma>
  <td align="left" >
   <b>Total:</b> <?=$total_lic?> <b>Orden/es.</b>   
  </td>
  <td align="right">
   <?=$link_pagina?>
  </td>
 </tr>
 </table>
 <table width='100%' align="center" cellspacing="2" cellpadding="2" class="bordessinsuperior">
 <tr id=mo>
   <td>&nbsp;</td>
   <?if (permisos_check("inicio", "permiso_cambiar_prioridad")){?>
      <td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"8","up"=>$up))?>'>Prioridad</a></b></td>
    <?}?>
   <td width="8%"> <b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>ID</a></b></td>
   <td width="8%"> <b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Renglon</a></b></td>
   <td width="8%"> <b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","up"=>$up))?>'>Nro OC</a></b></td>
   <td width="4%"> <b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Lider</a></b></td>
   <td width="50%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Entidad</b></td>
   <td width="15%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Cantidad PC</b></td>  
   <td width="15%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Vence Seg</b></td>  
 </tr>
 <? 
 $i=0;
 $renglon='(';
 while (!$resul_consulta->EOF) {
 	$renglon.=$resul_consulta->fields['id_renglon'].",";         
 	?>
 <tr <?=$atrib_tr?>> 
     <td><input type='checkbox' title='No mostrar en el listado' name='borrar_<?=$i?>' value='<?=$resul_consulta->fields['id_renglon']?>' class="estilos_check"></td>
     <? if (permisos_check("inicio", "permiso_cambiar_prioridad")) {
      	      $prior=$resul_consulta->fields["prioridad_prog"];
	 ?>
      <td nowrap align="center"> 
		 <input type="submit" name="mas_prioridad_prog" value="+" 
     	     onclick="document.all.cambiar_prior.value='<?=$prior?>';document.all.id_renglon.value='<?=$resul_consulta->fields["id_renglon"]?>'">
		 	<b><?=$prior?></b>
		 <input type="submit" name="menos_prioridad_prog" value="-" onclick="document.all.cambiar_prior.value='<?=$prior?>';document.all.id_renglon.value='<?=$resul_consulta->fields["id_renglon"]?>';">
	  </td>	
	 <?}?>	
     <td align="center"><?=$resul_consulta->fields['id_licitacion']?></td>  
     <td align="center"><?=$resul_consulta->fields['codigo_renglon']?></td>  
     <td align="center"><?=$resul_consulta->fields['nro_oc']?></td>  
     <td align="center"><?=$resul_consulta->fields['lider_iniciales']?></td>  
     <td align="center"><?=$resul_consulta->fields['nombre']?></td>  
     <td align="center"><?=$resul_consulta->fields['cantidad']?></td>  
     <td align="center"><?=fecha($resul_consulta->fields['vence_oc'])?></td>  
 </tr>    
    <?
 $i++;   
 $resul_consulta->MoveNext();
 }
   $renglon=substr_replace($renglon,')',(strrpos($renglon,',')));  
   
   $db->StartTrans();
   
   if ($cantidad_registros>0) {
   //busco si se un renglon pasó a pendientes y tenía prioridad asignada se manda un mail
   $sql_aviso="select prioridad_prod_bsas.id_renglon,prioridad_prog,
               id_licitacion,codigo_renglon 
               from licitaciones.prioridad_prod_bsas 
               join licitaciones.renglon using (id_renglon)
               where prioridad_prod_bsas.id_renglon not in $renglon
               and aviso=0";
   $res_aviso=sql($sql_aviso,"$sql_aviso ERROR ANTES DE MAIL") or fin_pagina();
   if ($res_aviso->RecordCount()>0) {
   $para="juanmanuel@coradir.com.ar";
   $asunto="Controlar prioridad en Producción Bs AS";
   $contenido="Paso a estado Pendientes desde el estado A Programar
               del Listado de Producción de Bs As \n";
   $contenido.="\n";  
   
   $list='(';
   while (!$res_aviso->EOF) {
   	$contenido.="Licitacion Id ".$res_aviso->fields['id_licitacion'];
   	$contenido.=" Renglon ".$res_aviso->fields['id_renglon'];   
   	$contenido.=" Con  Prioridad ".$res_aviso->fields['prioridad_prog']."\n";   
   	$contenido.="\n";  
   	$list.=$res_aviso->fields['id_renglon'].","; 
   	$res_aviso->MoveNext();   
   }   
   $list=substr_replace($list,')',(strrpos($list,',')));  
   $sql_update="update licitaciones.prioridad_prod_bsas 
                set aviso=1 
                where id_renglon in $list";

   sql($sql_update,"$sql_update") or fin_pagina();
   enviar_mail($para,$asunto,$contenido,'','',0);
  
   }
}
   $db->CompleteTrans();
  ?>
 </table> 
<?}
else  { ?>


<table width='100%' align="center" cellspacing="2" cellpadding="2" class="bordes">
 <tr id=ma>
  <td align="left" colspan="<?=(($cmd=="pendientes")?5:3)?>">
   <b>Total:</b> <?=$total_lic?> <b>Orden/es.</b>   
  </td>
  <td align="right" colspan="4">
   <?=$link_pagina?>
  </td>
 </tr>
 <tr id=mo>
 <?if ($cmd=="pendientes"){?>
 	<td width="1%">&nbsp;</td>
	<td width="1%">Prioridad</td>
<?}?>
  <td width="8%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>ID</a></b></td>
  <td width="4%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Lider</a></b></td>
  <td width="8%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>OP</b></td>
  <?//<td width="10%"><b><a href='encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))'>Vencimiento</a></b></td>?>
  <td width="50%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Entidad</b></td>
  <td width="15%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Fecha Entrega</b></td>
  <td width="15%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Cantidad PC</b></td>  
  <td width="15%">&nbsp;</td>
  </tr>
 <? 
    while (!$resul_consulta->EOF) {
    	$link=encode_link("ordenes_nueva.php",array("nro_orden"=>$resul_consulta->fields['nro_orden'],"modo"=>"modificar","volver"=>"seguimiento_produccion_bsas","cmd"=>"$cmd","por_tanda"=>$resul_consulta->fields['por_tanda'],"reprueba"=>$resul_consulta->fields['reprobo_calidad_bsas']));
    	if ($resul_consulta->fields["diferencia"]=="0") 
    		if ($resul_consulta->fields["estado_linea_produccion"]!="#EE6677") $color_celda="#00FF00";
    		else $color_celda="#EE6677";
			else $color_celda=$resul_consulta->fields["estado_linea_produccion"] or $color_celda="#FF0000";
    	
			//$rta_consulta2=sql("select * from licitaciones.linea_produccion_bsas where nro_orden=".$resul_consulta->fields["nro_orden"], "c105") or fin_pagina();

		  if ($resul_consulta->fields['cant']>0) sql("update licitaciones.linea_produccion_bsas set estado_linea_produccion='".$color_celda."' where id_linea_produccion_bsas=".$resul_consulta->fields["nro_orden"], "c101") or fin_pagina();
		  else sql("insert into licitaciones.linea_produccion_bsas (nro_orden, usuario, estado_linea_produccion)values (".$resul_consulta->fields["nro_orden"].", '', '".$color_celda."')", "c102") or fin_pagina();
		  $id=$resul_consulta->fields['id_licitacion'];
		  $id_nro=$resul_consulta->fields["nro_orden"];
		  $estado=$resul_consulta->fields["estado"];
		  $fecha_pd=Fecha($resul_consulta->fields["fecha"]);
		  //le pongo color si reprueba calidad
           if ($resul_consulta->fields["reprobo_calidad"]!=0 || $resul_consulta->fields["reprobo_calidad_bsas"]) 
                $color_reprobo_calidad = "#FF9999";
		   else $color_reprobo_calidad="";
          
  ?>
          <a href='<?=$link?>'>
	<tr <?=atrib_tr()?> title="<?if(($color_celda=="#AAFFAA")||($color_celda=="#EE6677")) echo $resul_consulta->fields["usuario"]." (".$fecha_pd.")\n".$resul_consulta->fields["comentario"]?>">
	<?if($cmd=="pendientes"){?>
	 	<td nowrap align="center" bgcolor="<?=$color_reprobo_calidad?>">
		<?if (permisos_check("inicio", "permiso_cambiar_estado_linea_produccion")){?>
			<input type="button" name="cambiar" value="C" onclick="window.open('<?= encode_link('editar_orden.php',array("usuario"=>$_ses_user["login"], "nro_orden"=>$resul_consulta->fields["nro_orden"]))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1, height=300, width=625');">
		<?}else echo("&nbsp;");?>
		</td>
		<td nowrap align="center" bgcolor="<?=$color_reprobo_calidad?>"> 
		<?if (permisos_check("inicio", "permiso_cambiar_prioridad")){?>
			&nbsp;
			<input type="submit" name="mas_prioridad" value="+" onclick='document.all.cambiar_pop.value="<?=$resul_consulta->fields["nro_orden"].", ".$resul_consulta->fields["prioridad"]?>"'>
		<?}?>
			<b><?=$resul_consulta->fields["prioridad"]?></b>
		<?if (permisos_check("inicio", "permiso_cambiar_prioridad")){?>
			<input type="submit" name="menos_prioridad" value="-" onclick='document.all.cambiar_pop.value="<?=$resul_consulta->fields["nro_orden"].", ".$resul_consulta->fields["prioridad"]?>"'>
		<?}?>
		</td>
		<?}?>
		   <?
		   if ($cmd=='calidad'){
		   		echo "<td align=center bgcolor='".$estados[$resul_consulta->fields["id_estado"]]["color"]."' title='".$estados[$resul_consulta->fields["id_estado"]]["texto"]."'><b><a style='color=".contraste($estados[$resul_consulta->fields["id_estado"]]["color"],"#000000","#ffffff").";'>";
			}
		    else{?>
		    	<td align="center" bgcolor="<?=$color_reprobo_calidad?>">
		    <?}?>
           <?=$resul_consulta->fields['id_licitacion']?></td>
           <td align="center" bgcolor="<?=$color_reprobo_calidad?>"><?=$resul_consulta->fields['lider_iniciales']?></td>
          <td <?=(($color_celda&&($cmd=="pendientes"))?"bgcolor='".$color_celda."'":"")?> <?if ($cmd!="pendientes") echo "td bgcolor='$color_reprobo_calidad'"?>><?=$resul_consulta->fields['nro_orden']?></td>
           <?//<td align="center">fecha($resul_consulta->fields['vence_oc'])</td>?>
           <td align="center" bgcolor="<?=$color_reprobo_calidad?>"><?=$resul_consulta->fields['nombre']?></td>
           <td align="center" bgcolor="<?=$color_reprobo_calidad?>"><?=fecha($resul_consulta->fields['fecha_entrega'])?></td>
            <?if($resul_consulta->fields['por_tanda']==1) {
            	  $color_cantidad="$color_tanda";
            	  $total="/".$resul_consulta->fields['cant_total'];
            	  $title=$resul_consulta->fields['cantidad']." Maq en ".$cmd." de un Total de ".$resul_consulta->fields['cant_total'];
             }
                 else {
                  $color_cantidad=$color_reprobo_calidad;
                  $total="";
                  $title="";
                 }?>
           <td align="center" bgcolor="<?=$color_cantidad?>" title="<?=$title?>"><?=$resul_consulta->fields['cantidad'].$total?></td>
            <td bgcolor="<?=$color_reprobo_calidad?>">
<?
	/*$q = "select nro_orden, licitaciones.unir_texto(nombre||', ') as archivos
		from ordenes.archivos_ordprod join general.subir_archivos on id_archivo=subir_archivos.id 
		where nro_orden=".$resul_consulta->fields['nro_orden']." group by nro_orden";
	$adjunto=sql($q, "c106") or fin_pagina();*/
	if ($resul_consulta->fields["archivos"]!="") echo "<img id='imagen_1' src='../../imagenes/files1.gif' border=0 title='".substr($resul_consulta->fields["archivos"], 0, strlen($resul_consulta->fields["archivos"])-2)."' align='left'>";
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
<br>
<?if ($cmd=="pendientes"){?>

<table align="center" width="100%">
  <tr>
   <td>
  
	<table border="1" bordercolor='black' cellpadding="2" cellspacing="2" align="center" bgcolor="White">
		<tr bordercolor='white'>
			<td bgcolor="#00FF00" width="15" height="15" bordercolor='black'><font color="#00FF00">&nbsp;</font></td><td bordercolor='white'> Lista para producir</td>
		</tr>
		<tr bordercolor='white'>
			<td bgcolor="#AAFFAA" width="15" height="15" bordercolor='black'><font color="#AAFFAA">&nbsp;</font></td><td bordercolor='white'> Lista para producir (a pesar de los faltantes)</td>
		</tr>
		<tr bordercolor='white'>
			<td bgcolor="#FF0000" width="15" height="15" bordercolor='black'><font color="#FF0000">&nbsp;</font></td><td bordercolor='white'> No está lista para producir</td>
		</tr>
		<tr bordercolor='white'>
			<td bgcolor="#EE6677" width="15" height="15" bordercolor='black'><font color="#EE6677">&nbsp;</font></td><td bordercolor='white'> No se <b>debe</b> producir aún</td>
		</tr>
		<tr bordercolor='white'>
			<td bgcolor="<?=$color_tanda?>" width="15" height="15" bordercolor='black'><font color="#EE6677">&nbsp;</font></td><td bordercolor='white'> Dividado Por Tanda (Col. Cantidad Pc)</td>
		</tr>
	</table>
	</td>
	
	<td>
	<table border="1" bordercolor='black' cellpadding="2" cellspacing="2" align="center" bgcolor="White">
		<tr>
		<td colspan="4" bordercolor ='#FFFFFF'><strong>Colores de referencia para filas</strong> </td></tr>
		<tr>
		<td bordercolor ='#FFFFFF'>&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td width="3%" bgcolor="#FF9999" align="left">&nbsp;</td>
		<td width="97%" bordercolor ='#FFFFFF'>&nbsp;Orden de Produccion que Reprobó la Auditoría de Calidad</td>
	</table>
	
  </td>
 </tr>
</table>
<?}
else{?>
  
	<br>
	<table border="1" bordercolor='black' cellpadding="2" cellspacing="2" align="center" bgcolor="White">
		<tr bordercolor='white'>
		  <td colspan="2"><b>Colores de referencia para filas</b> </td>
		  <td colspan="2"><b>Colores de referencia Cantidad Pc </b></td>
		</tr>
		<tr bordercolor='white'>
			<td bgcolor="#FF9999" width="15" height="15" bordercolor='black'><font color="#AAFFAA">&nbsp;</font></td>
			<td bordercolor='#FFFFFF'> Orden de Produccion que Reprobó la Auditoría de Calidad</td>
			<td bgcolor="<?=$color_tanda?>" width="15" height="15" bordercolor='black'><font color="#AAFFAA">&nbsp;</font></td>
			<td bordercolor='#FFFFFF'> Dividido Por Tanda (Col. Cantidad Pc)</td>
		</tr>
	</table>
<?}
if ($cmd=="calidad"){
	echo "<table width='95%' border=0 align=center>\n";
	echo "<tr><td colspan=6 align=center><br>\n";
	echo "<table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>\n";
	echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia ID:</b></td></tr>\n";
	echo "<tr>\n";
	$cont=0;
	foreach ($estados as $est => $arr) {
	if (!($cont % 3)) { echo "</tr><tr>"; }
		echo "<td width=33% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 wdith=100%><tr>";
		echo "<td width=15 bgcolor='".$estados[$est]["color"]."' bordercolor='#000000' height=15>&nbsp;</td>\n";
		echo "<td bordercolor='#FFFFFF'>".$estados[$est]["texto"]."</td>\n";
		echo "</tr></table></td>";
	   $cont++;
	}
	echo "</tr>\n";        
	echo "</table>\n";
	echo "</td></tr>\n";
	echo "</table><br>\n";
}
}?>
</form>
<?=fin_pagina();?>

