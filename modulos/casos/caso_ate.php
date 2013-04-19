<?php
/*
$Author: fernando $
$Revision: 1.27 $
$Date: 2006/10/26 14:49:49 $
*/
include "head.php";
variables_form_busqueda("caso_ate");//para que funcione el form busqueda

//Valores del formulario de busqueda
//$up=$parametros["up"] or $up=$_POST["up"];
//$sort=$parametros["sort"] or $sort=$_POST["sort"];
//$page=$parametros["page"] or $page=$_POST["page"];
//$keyword=$parametros["keyword"] or $keyword=$_POST["keyword"];
//$filter=$parametros["filter"] or $filter=$_POST["filter"];

$backto=$parametros['backto'] or $backto=$_POST['backto'];//variable para volver a otra pagina como ordcompra, remitos o facturas


if ($backto && $_ses_global_backto!=$backto) {
        phpss_svars_set("_ses_global_backto", $backto);
        phpss_svars_set("_ses_global_nro_orden_asociada", $parametros['nro_orden']);
        phpss_svars_set("_ses_global_pag", $parametros['pag']);
        phpss_svars_set("_ses_global_extra", $parametros['_ses_global_extra']);
        phpss_svars_set("_ses_gastos_serv_tec",$parametros["gastos_servicio_tecnico"]);
}


?>
<script language='javascript' src='../../lib/popcalendar.js'></script>


<form action='caso_ate.php' method='post' name='frm'>
<input type=hidden name=short value='$sort'>
<?

if (!$sort) $sort=1;

$orden = array("default" => "1",
               "default_up" => "0",
               "1" => "nombre",
               "2" => "contacto",
               "3" => "direccion",
               "4" => "tel",
               "5" => "comentario",
               "6" => "activo",
               "7" => "provincia",
               "8" => "fecha_inicio"
              );

$filtro = array("cas_ate.nombre"      => "CAS",
                "contacto"    => "Contacto",
                "direccion"   => "Dirección",
                "tel"         => "Teléfono",
                "comentario"  => "Comentarios",
                "distrito.nombre"   => "Provincia",
                "ciudad"      => "Ciudad"
               );


$sql = "SELECT idate,cas_ate.nombre as nombre,contacto,direccion,ciudad,cp,
               tel,mail,comentario,activo, distrito.nombre as provincia,fecha_inicio
               from cas_ate left join distrito using (id_distrito)";

if($_POST["descartadas_check"])
	$where="";
else 
	$where="activo=1";
$contar="buscar";
echo "<br>";
echo "<center>";

list($sql,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,$contar);
$rs=sql($sql) or fin_pagina();

?>
<input type=hidden name=sql value="<?=$sql?>">
<input type="checkbox" name="descartadas_check" value="1" <?if($_POST["descartadas_check"]) echo "checked"?>>
   <font size="1">Mostrar Todas</font>
 &nbsp;
<input type=submit name=envia value='Buscar'>
</center>
<br>
<table width="100%">
 <tr align="center">
  <td>
  <input type="button" name="enviar_mail_todos" value="Enviar Mail a todos" onclick="window.open('caso_enviar_mail_cas.php');" style="cursor:hand;" title="Haga click aqui para enviar mail a todos los  casos">
  &nbsp;
  <input type="button" name="boton" value="Nuevo CAS" onclick="ventana=window.open('<?=encode_link('caso_organismo.php',array('estado'=>'Nuevo'))?>','','left=40,top=80,width=700,height=350,resizable=1,scrollbars=1');" style="cursor:hand;" title="Haga click aqui para insertar nuevo caso"></td>
 </tr>
 <tr align="right">
  <td>
  <b>Descargar Excel</b> &nbsp (Solo Activos)&nbsp
   <?
   $link=encode_link("caso_ate_excel.php",array("id_deposito"=>$id_deposito,"id_producto"=>$id_producto,"download"=>1));
  ?>
   <a target=_blank title='Bajar datos en un excel' href='<?=$link?>'>
    <img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' >
   </a>
  </td>
  <td align="center">
  <?
   $link=encode_link("caso_destino_excel.php",array("sql"=>$sql));
  ?>
   <b>Envios</b>
   <a target=_blank title='Bajar datos en un excel' href='<?=$link?>'>
    <img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' >
   </a>
  </td>
 </tr>
</form><br>
<?
$rs = $db->Execute($sql) or die($db->ErrorMsg());
?>
<form action='caso_ate.php' method='POST' name=frm id=frm>

<table border=0 width="100%" align="center">
 <tr id=ma>
  <td  align="left" colspan="3">
   <b>Total:</b> <?=$total?> Organismos
   <input name="total_organismos" type="hidden" value=<?=$total?>>
  </td>
  <td align="right" colspan="6">
   <?=$link_pagina?>
  </td>
 </tr>
 <tr>
  <td align=right id=mo><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","up"=>$up))?>'>Provincia  </a></td>
  <td align=right id=mo><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>C A S      </a></td>
  <td align=right id=mo><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Contacto   </a></td>
  <td align=right id=mo><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Dirección  </a></td>
  <td align=right id=mo><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Teléfono   </a></td>
  <td align=right id=mo><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"8","up"=>$up))?>'>F. Inicio  </a></td>
  <td align=center id=mo><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Activo    </a></td>
  <td align=center id=mo>En Curso</td>
  <td align=center id=mo>Pendientes</td>
  <td align=center id=mo>Totales</td>
 </tr>

<?



while (!$rs->EOF) {
      if ($_ses_global_backto)
                {
                $link = encode_link($_ses_global_backto,array("idate"=>$rs->fields["idate"],"nro_orden"=>$_ses_global_nro_orden_asociada,"licitacion"=>"","pagina"=>$_ses_global_pag,"gastos_servicio_tecnico"=>$_ses_gastos_serv_tec));
                $onclick="location.href='$link'";
                }
                else
                {
                $link=encode_link('caso_organismo.php',array('idate'=>$rs->fields['idate'],'estado'=>'Modificar'));
                $onclick="ventana=window.open('$link')";
                }

?>
<tr <?echo $atrib_tr ?>>
<td align='center' style='font-size: 9pt;' onclick="<?=$onclick?>" style='cursor:hand;' title="Haga click aqui para modificar o eliminar el caso"><b>&nbsp;<?=$rs->fields["provincia"];?></td>
<td align='center' style='font-size: 9pt;' onclick="<?=$onclick?>" style='cursor:hand;' title="Haga click aqui para modificar o eliminar el caso"><b>&nbsp;<?=$rs->fields["nombre"];?></td>
<td align='center' style='font-size: 9pt;' onclick="<?=$onclick?>" style="cursor:hand;" title="Haga click aqui para modificar o eliminar el caso"><b>&nbsp;<?=$rs->fields["contacto"];?></td>
<td align='center' style='font-size: 9pt;' onclick="<?=$onclick?>" style="cursor:hand;" title="Haga click aqui para modificar o eliminar el caso"><b>&nbsp;<?=$rs->fields["direccion"];?></td>
<td align='center' style='font-size: 9pt;' onclick="<?=$onclick?>" style="cursor:hand;" title="Haga click aqui para modificar o eliminar el caso"><b>&nbsp;<?=$rs->fields["tel"]; ?></td>
<td align='center' style='font-size: 9pt;' onclick="<?=$onclick?>" style="cursor:hand;" title="Haga click aqui para modificar o eliminar el caso"><b>&nbsp;<?=fecha($rs->fields["fecha_inicio"]); ?></td>
<?
    $descrip = str_replace(chr(13).chr(10),"<n>",$rs->fields["comentarios"]);
    if ($rs->fields["activo"]) $act="Si";
    else $act="No";
    echo "<td align=center style='font-size: 9pt;'><font color=blue><b>&nbsp;".$act."</td>\n";

// calculo cantidad de casos que se encuentran en curso
$sql="select count(nrocaso) as cantidad from casos_cdr
     where idate=".$rs->fields['idate']." and idestuser=1";
$result_cant=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$sql="select count(nrocaso) as cantidad from casos_cdr
     where idate=".$rs->fields['idate'];
$result_total=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$total=$result_total->fields['cantidad'];
$cont=$result_cant->fields['cantidad'];
?>
<td onclick="window.open('<?=encode_link("atendido.php",array("id"=>$rs->fields["idate"],"estado"=>1));?>');" style="cursor:hand;" title="Haga click aqui para ver detalle de los casos en curso" align="center"><b><font color=blue><?=$cont; ?></td>
<?
// calculo cantidad de casos que se encuentran en pendientes
$sql="select count(nrocaso) as cantidad from  casos_cdr
       where idate=".$rs->fields['idate']." and idestuser=7";
$result_cant=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$cont=$result_cant->fields['cantidad'];
?>
<td onclick="window.open('<?=encode_link("atendido.php",array("id"=>$rs->fields["idate"],"estado"=>7));?>');" style="cursor:hand;" title="Haga click aqui para ver detalle de los casos pendientes" align="center"><b><font color=blue><?=$cont; ?></td>
<td style="cursor:hand;" align="center"><b><font color=blue><?=$total; ?></td>
<?
 echo "</tr>\n";
 $rs->MoveNext();
}
?>
</table>
<?=fin_pagina()?>