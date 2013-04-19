<?php
/*
$Author: diegoinga $
$Revision: 1.6 $
$Date: 2005/01/14 13:47:03 $
*/

require_once("../../config.php");
require_once("../general/funciones_contactos.php");

$id_prorroga = $parametros['id_prorroga'] or $id_prorroga = $_POST['id_prorroga'];
$id_entrega_estimada = $parametros['id_entrega_estimada'] or $id_entrega_estimada = $_POST['id_entrega_estimada'];

if ($_POST['guardar_comentario'])
{
/*if ($id_prorroga=="") //cargo la prorroga
{

$sql = "insert into prorroga(id_entrega_estimada) values($id_entrega_estimada)";
$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$sql = "select max(id_prorroga) from prorroga";
$result_prorroga = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$id_prorroga = $result_prorroga->fields['max'];

}
*/
$usuario = $_ses_user["id"];
$fecha = date("Y-m-d");
$comentario = $_POST['comentario_nuevo'];
$sql = "insert into comentario_prorroga(id_prorroga,comentario,fecha_comentario,id_usuario) values($id_prorroga,'$comentario','$fecha',$usuario)";
$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
}


/*

// Control de dias de vencimiento de la prorroga

//buscamos renglones de la licitacion
if ($id_prorroga!="")
{
$sql= "select renglon.cantidad,renglon.tipo from prorroga join entrega_estimada using(id_entrega_estimada) join licitacion using(id_licitacion) join renglon using(id_licitacion) join historial_estados using(id_renglon) where prorroga.id_prorroga = $id_prorroga and historial_estados.id_estado_renglon=3 and historial_estados.activo=1";
$resultado_renglones = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);

$comentarios = "";
//recorremos los renglones y guardo los comentarios segun los controles
while(!$resultado_renglones->EOF)
{
  switch ($resultado_renglones->fields['tipo'])
  {
   case "Computadora Enterprise":
    //controlo que la fecha actual con la fecha de vencimiento no sobrepase x dias
    $dias_tolerancia = 10;
    
    
   case "Computadora Matrix":break;
   case "Software":
   //controlo que la fecha actual con la fecha de vencimiento no sobrepase x dias
    $dias_tolerancia = 10;
   case "Otro":    
   //controlo que la fecha actual con la fecha de vencimiento no sobrepase x dias
    $dias_tolerancia = 10;
                    break;
   case "Impresora":
   //controlo que la fecha actual con la fecha de vencimiento no sobrepase x dias
    $dias_tolerancia = 10;
   break;
  
  
  }
  
  $resultado_renglones->MoveNext();

}

}
//fin de control de prorrogas

*/

echo $html_header;

echo "<script language='javascript' src='../../lib/popcalendar.js'> </script>\n";

if ($id_prorroga!="")
{
$sql = "select prorroga.id_entrega_estimada,licitacion.id_licitacion,entidad.nombre,entidad.id_entidad from prorroga join entrega_estimada using(id_entrega_estimada) join licitacion using (id_licitacion) join entidad using(id_entidad) where id_prorroga=$id_prorroga";
$result_prorroga=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
}
else
{
$sql = "select entrega_estimada.id_entrega_estimada,licitacion.id_licitacion,entidad.nombre,entidad.id_entidad from entrega_estimada join licitacion using (id_licitacion) join entidad using(id_entidad) where id_entrega_estimada=$id_entrega_estimada";
$result_prorroga=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
}

//contactos_existentes("Licitaciones",$result->fields['id_entidad']); usar esta funcion
?>
<br>
<font size="4"><b>Prorrogas - Seguimiento de Produccion</b></font>
<hr>
<form action="prorrogas.php" method="POST">
<input type="hidden" name="id_prorroga" value="<?=$id_prorroga;?>">
<input type="hidden" name="id_entrega_estimada" value="<?=$id_entrega_estimada;?>">
<table class="bordes" width="95%">
<tr>
<td><b><font size="1"> 
<?
if ($parametros['nro_orden_cliente'])
{
$sql = "select id_subir from subido_lic_oc where nro_orden='".$parametros['nro_orden_cliente']."' and id_licitacion=".$result_prorroga->fields['id_licitacion'];
$result_nro_orden=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$link_numero=encode_link("../../lib/archivo_orden_de_compra.php",array("id_subir"=>$result_nro_orden->fields['id_subir'],"solo_lectura"=>1));
}
?>
SEGUIMIENTO DE ORDEN: <a href="<?=$link_numero;?>" target="_blank"><?=$parametros['nro_orden_cliente']?></a>
</td>
<td></td>
<td></td>
</tr>
<tr>
<?
$link = encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$result_prorroga->fields['id_licitacion']));
?>
<td><b><font size="3">Lic.: </font><a href="<?=$link?>" target="_blank"><font color="Blue"><?=$result_prorroga->fields['id_licitacion'];?></a></td>
<td><font color="Blue"><b><?=$result_prorroga->fields['nombre'];?></td>
</tr>
<tr>
<td colspan="2" align="right"><font size="3"><b>Contactos:</b></td><td><?=contactos_existentes("Licitaciones",$result_prorroga->fields['id_entidad']);?></td>
</tr>
<tr>
<td colspan="4">
<table class="bordes" width="100%">
<tr>
<td align="center" colspan="2">
<font size="3"><b>Comentarios de la Prorroga
</td>
<tr id="ma">
   <td> Fecha </td><td>Comentarios</td>
</tr>
<?

//parte de los comentarios
$id_entrega_estimada = $result_prorroga->fields['id_entrega_estimada'];

if ($id_prorroga!="")
 $sql = "select comentario_prorroga.comentario,comentario_prorroga.fecha_comentario,comentario_prorroga.id_usuario,comentario_prorroga.id_prorroga from comentario_prorroga where id_prorroga=$id_prorroga";
else 
 $sql = "select comentario_prorroga.comentario,comentario_prorroga.fecha_comentario,comentario_prorroga.id_usuario,comentario_prorroga.id_prorroga from comentario_prorroga where id_prorroga=-1";

$sql2 = "select comentarios_seguimientos.comentario,comentarios_seguimientos.fecha_comentario,
         comentarios_seguimientos.id_usuario,NULL as id_prorroga from entrega_estimada 
         join subido_lic_oc using(id_entrega_estimada) 
         join comentarios_seguimientos using(id_subir) 
         where id_entrega_estimada=$id_entrega_estimada";
$sql3 = "$sql UNION ALL $sql2 order by fecha_comentario desc";

$result_comentarios=$db->execute($sql3) or die($db->errormsg()."<br>".$sql3);

$cantidad=$result_comentarios->recordcount();
for($i=0;$i<$cantidad;$i++) {
$fecha=Fecha($result_comentarios->fields["fecha_comentario"]);
$comentario=$result_comentarios->fields["comentario"];
$usuario = $result_comentarios->fields["id_usuario"];
$sql = "select  (nombre || ' ' || apellido) as nombre from usuarios where id_usuario = $usuario";
$result_usuario = $db->execute($sql) or die($db->errormsg()."<br>".$sql);
$usuario = $result_usuario->fields['nombre'];

if ($result_comentarios->fields['id_prorroga']=="")
 $modulo = "<font color='blue'><b>Cargado en Entregas</b></font>";
else
 $modulo = "<font color='blue'><b>Cargado en Prorrogas</b></font>";
?>
  <tr bgcolor='<?=$bgcolor2?>'>
	 <td align=center valign=top >
	 <b>
	  <?=$fecha?>
	  <br>
	  <?="$usuario<br>$modulo"?>
	 <b>
	 </td>
	 <td align=center valign=top width=80%>
	 <textarea name=comentario_<?=$i?> rows=4 readonly style="width:100%"><?=$comentario?></textarea>
	 </td>
  </tr>
<?
$result_comentarios->movenext();
}//del for
?>
<tr bgcolor='<?=$bgcolor2?>'>
	 <td align=center valign=top>
	 </td>
	 <td align=center valign=top width=80%>
	 <textarea name=comentario_nuevo rows=4  style="width:100%"></textarea>
	 </td>
  </tr>
</tr>
</table>
</td>
</tr>
<tr>
<td colspan="3" align="center"><input type=submit name=guardar_comentario value='Guardar Comentarios' style="cursor:hand" <?=($id_prorroga=="")?"disabled":"";?>></td>
</tr>
<tr>
<td colspan="4">
</form>
<table class="bordes" width="95%">
<tr>
<td colspan="3" align="center">
<font size="3"><b>Archivos Subidos
</td>
</tr>
<tr>
<td width="100%">
<form action="<?=$html_root?>/modulos/licitaciones/licitaciones_view.php" method="POST" target="_blank">
<center>
<? //aca van parte de archivos
lista_archivos_lic_prorroga($result_prorroga->fields['id_licitacion']);
$link_volver=encode_link("ver_prorrogas.php",array());
?>
</center>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td colspan="4" align="center">
<input type=hidden name=ID value='<?=$result_prorroga->fields['id_licitacion'];?>'>
<input type=submit name=det_addfile class='estilos_boton' style="cursor:hand" value='Agregar archivo'>
<input type="button" name=volver class='estilos_boton' style="cursor:hand;width:90px" value='Volver' onclick="location.href='<?=$link_volver?>'">
</td>
</tr>
</table>
</form>
<?
echo $html_footer;
?>