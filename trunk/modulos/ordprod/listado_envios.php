<?
/*
$Author: lizi

MODIFICADA POR
$Author: ferni $
$Revision: 1.15 $
$Date: 2006/03/30 14:51:44 $
*/

require_once("../../config.php");

$msg=$_POST['msg'] or $msg=$parametros['msg'];
$id_envio_renglones=$_POST['id_envio_renglones'] or $id_envio_renglones=$parametros['id_envio_renglones'];
echo $html_header;

variables_form_busqueda("listado_envios");
//print_r($_ses_listado_envios);
//print_r($_POST);


if (!$cmd) {
	$cmd="pendientes";
	$_ses_listado_envios["cmd"]=$cmd;
	phpss_svars_set("_ses_listado_envios", $_ses_listado_envios);
	
}
$datos_barra = array(
					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "pendientes",
						 ),
					array(
						"descripcion"	=> "Ya Enviados",
						"cmd"			=> "ya enviados",
						),
						array(
						"descripcion"	=> "Anulados",
						"cmd"			=> "anulados"
						)
	            );


generar_barra_nav($datos_barra);
?>
<form name="form1" method="post" action="listado_envios.php">
<tr align="center">
	<td align="center">
		<font color="Red" size="3"><?=$parametos['msg']?></font>
	</td>
</tr>
<?


echo "<table align=center cellpadding=5 cellspacing=0 >";
echo "<tr><td>";

$itemspp=50;

// Fin variables necesarias
if ($up=="") $up = "1";   // 1 ASC 0 DESC

$seleccion = Array ( "serie" => "id_renglones_bultos in (select id_renglones_bultos
                                        from licitaciones_datos_adicionales.nro_serie_renglon join 
                                        licitaciones_datos_adicionales.renglones_bultos using (id_renglones_bultos)
                                        where nro_serie ILIKE '%$keyword%')"
);

$ignorar = Array ( 0 => "serie");
$orden = Array (
//"default" => "1",
        "1" => "id_envio_renglones",
        "2" => "id_licitacion",
        "3" => "nombre_envio_origen",
        "4" => "nombre_envio_destino",
        "5" => "entidad_mod",
        "6" => "nombre_transporte",
        "7" => "telefono_transporte",
        "8" => "nrocaso"
      );
$filtro = Array (
   "id_envio_renglones" => "Nro. de Envío",
   "id_licitacion" => "ID licitación",
   "entidad_mod" => "Entidad",
   "nombre_envio_origen" => "Origen",
   "distrito.nombre" => "Destino",
   "serie" => "Nro.de series",
);

//entidad_mod, dir_entrega_mod, contacto_mod, nro_lic_mod, 
$sql_tmp="select distinct id_envio_renglones, 
          nro_oc_mod, envio_renglones.id_licitacion, entidad_mod, nombre_envio_origen, 
          licitaciones.distrito.nombre as nombre_envio_destino, deco_envio_destino, deco_envio_origen, 
          nombre_transporte, telefono_transporte, nrocaso
        from licitaciones_datos_adicionales.envio_renglones 
          left join licitaciones_datos_adicionales.renglones_bultos using (id_envio_renglones) 
          left join licitaciones_datos_adicionales.datos_envio using (id_envio_renglones)         
          left join licitaciones_datos_adicionales.envio_origen using (id_envio_origen)         
          left join licitaciones_datos_adicionales.envio_destino using (id_envio_destino)         
          left join licitaciones.distrito using (id_distrito)         
          left join licitaciones_datos_adicionales.log_envio_renglones using (id_envio_renglones) 
          left join licitaciones.renglones_oc using (id_renglones_oc)
          left join licitaciones.subido_lic_oc using (id_licitacion) 
          left join licitaciones_datos_adicionales.transporte using (id_transporte)
          left join casos.casos_cdr using (idcaso)
         ";

 if ($cmd=="pendientes") 
 {
 $where_tmp= " envio_cerrado=0";
 }
 else 
 {
 if ($cmd=="anulados") $where_tmp= " envio_cerrado=2";
 else $where_tmp= " envio_cerrado=1";
 }
$where_tmp.=" and tipo_log='creacion'"; 
 
if($_POST['keyword'] || $keyword)// en la variable de sesion para keyword hay datos)
     $contar="buscar";
?>  

<input type=button name="nuevo" title="Envios que no estan ligados a una licitacion"  value="Nuevo Envio" onClick="document.location='../logistica/nuevos_envios.php'">
<?
list($sql,$total,$link_pagina,$up2) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar","",$ignorar,$seleccion);

$res_query = sql($sql) or fin_pagina();
//print_r($res_query);
echo "<input type=submit name='buscar' value='Buscar'>";
echo "</td>";
echo "</tr>\n";
echo "</table>\n";
echo "<center><b><font size=2>".$msg."</font></b></center>";
?>

<table border=0 width=95% cellspacing=2 cellpadding=3 align="center">
  <tr>
  <td colspan=10 align=left id=ma> <? echo "\n";?>
	<table width=100%>
	 <tr id=ma><? echo "\n";?>
	  <td width=60% align=left><b><? echo "Total:</b> $total Envios</td>\n";?>
      <td width=40% align=right><? echo $link_pagina ?></td> <? echo"\n";?>
	 </tr>
	</table> <? echo "\n";?>
  </td>
  </tr>
  <tr>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("listado_envios.php",Array('sort'=>1,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Nro. Envio</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("listado_envios.php",Array('sort'=>6,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Transporte</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("listado_envios.php",Array('sort'=>7,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Tel&eacute;fono</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("listado_envios.php",Array('sort'=>2,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Id. Licitación</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("listado_envios.php",Array('sort'=>3,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Origen</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("listado_envios.php",Array('sort'=>3,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Destino</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("listado_envios.php",Array('sort'=>4,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Entidad</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("listado_envios.php",Array('sort'=>5,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Cant. Bultos</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("listado_envios.php",Array('sort'=>8,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Nro.Serv.Tec.</b></a></td>
  </tr>

 <?
 while (!$res_query->EOF) {
 	 $nro_envio=$res_query->fields['id_envio_renglones'];
     $id=$res_query->fields['id_licitacion'];
     $entidad=$res_query->fields['entidad_mod'];
     $id_envio_renglones=$res_query->fields['id_envio_renglones'];
     $serial=$res_query->fields["deco_envio_origen"]."-".$res_query->fields["deco_envio_destino"]."-";
     $serial.=str_pad($id_envio_renglones,10,'0',STR_PAD_LEFT);
	
    $q_a="select distinct  id_entrega_estimada, cantidad_total
           from licitaciones.subido_lic_oc 
           left join licitaciones_datos_adicionales.envio_renglones using (id_subir) 
		   where id_envio_renglones=$id_envio_renglones";
     $res_q_a=sql($q_a, "Error al traer el id_entrega_estimada") or fin_pagina();
     
     $q_c="select cantidad_total
           from licitaciones_datos_adicionales.envio_renglones  
		   where id_envio_renglones=$id_envio_renglones";
     $res_q_c=sql($q_c, "Error al traer el id_entrega_estimada") or fin_pagina();
     
     $id_entrega_estimada=$res_q_a->fields['id_entrega_estimada']; 
         
     $cant_b=$res_q_c->fields['cantidad_total'];
     if($id=='')
     {
     $ref=encode_link("../logistica/nuevos_envios.php", array("id_envio_renglones"=>$id_envio_renglones,"id_entidad"=>$id_ent,"pagina"=>"nuevos_envios","serial_1"=>$serial,"tipo"=>$cmd));
     tr_tag($ref);	
     }
     else {
     $ref=encode_link("preparar_envios.php", array("id_entrega_estimada"=>$id_entrega_estimada, "id_licitacion"=>$id, "id_envio_renglones"=>$id_envio_renglones, "pagina"=>"listado","id_entidad"=>$id_ent,"tipo"=>$cmd));
     tr_tag($ref);
     }
    
    ?>
    <td align="center" style="cursor:hand"><?=$serial?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['nombre_transporte'] ?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['telefono_transporte'] ?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['id_licitacion'] ?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['nombre_envio_origen']?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['nombre_envio_destino']?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['entidad_mod'] ?></td>
	<td align="center" style="cursor:hand"><?=$cant_b?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['nrocaso'] ?></td>
   </tr>
   <? 		
   $res_query->MoveNext();
   } 
   ?>
</table>
<br>
</form>
</html>
<?echo fin_pagina();?>