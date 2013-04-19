<?
/*
Author: Fernando

MODIFICADA POR
$Author: ferni $
$Revision: 1.3 $
$Date: 2007/03/14 20:55:54 $
*/

require_once("../../config.php");
variables_form_busqueda("listado_ordenes_monitores");


echo $html_header;

if (!$cmd)
        $cmd="activas";
          


$datos_barra = array (
                   array ("descripcion"=>"Activas","cmd"=>"activas"),
                   array ("descripcion"=>"historial","cmd"=>"eliminadas")
               );


generar_barra_nav($datos_barra);


$orden = array(
        "default" => "1",
        "1" => "nro_orden_monitores",
        "2" => "cantidad",
        "3" => "fecha",
       
       
        );
$filtro = array(
        "nro_orden_monitores" => "Nro Orden",
        "cantidad" => "Cantidad",
        "fecha"    => "Fecha",
        );

$sql = " select  ordenes_monitores.*
         from ordenes.ordenes_monitores";

$where = ($cmd == "activas")?" activo=1":"activo=2";
?>
<br>
<form name="form1" method="POST" action="listado_ordenes_monitores.php">
<table cellspacing=2 cellpadding=5 class='bordes' width=100% align=center bgcolor=<?=$bgcolor3?>>
<tr>
 <td align=center>
      <?
      list($sql,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,"buscar");
      $res = sql($sql) or fin_pagina();
      ?>
      <input type=submit name=buscar value='Buscar'>
      &nbsp;
      <input type="button" name="nueva_orden" value="Nueva Orden" onclick="document.location='ordenes_monitores.php'">
      <input type="button" name="kaizen" value="Kaizen" onclick="window.open('kaizen.php')">
      </td>
</td>
</table>
<br>
<table class="bordes" width="100%" align="center">
    <tr id=ma>
      <td>
        <table width="100%">
          <tr id=ma>
            <td width="50%" align="left">Cantidad :<?=$total?></td>
             <td width="50%" align="right"><?=$link_pagina?></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
     <td width="100%" align="center">
          <table width="100%" align="center">
                 <tr id="mo">
			        <td><a id=mo href='<?=encode_link("listado_ordenes_monitores.php",array("sort"=>"1","up"=>$up))?>'>Nro Orden </a></td>
			        <td><a id=mo href='<?=encode_link("listado_ordenes_monitores.php",array("sort"=>"2","up"=>$up))?>'>Cantidad  </a></td>
			        <td><a id=mo href='<?=encode_link("listado_ordenes_monitores.php",array("sort"=>"3","up"=>$up))?>'>Fecha     </a></td>
			        <td><a id=mo href='<?=encode_link("listado_ordenes_monitores.php",array("sort"=>"3","up"=>$up))?>'>Nro Serie Desde </a></td>
			       	<td><a id=mo href='<?=encode_link("listado_ordenes_monitores.php",array("sort"=>"3","up"=>$up))?>'>Nro Serie Hasta </a></td>
			    </tr>
			    <?
			     for($i = 0;$i<$res->recordcount();$i++) {
			     	
			       	$link = encode_link("ordenes_monitores.php",array("nro_orden_monitores" => $res->fields["nro_orden_monitores"]));
			       	$descripcion = $res->fields["descripcion"];
			       	tr_tag($link,"title='$descripcion'");
                ?>
                    <td width="10%" align="center"><font color=red><b><?=$res->fields["nro_orden_monitores"]?></b></font></td>
                    <td width="10%" align="center"><?=$res->fields["cantidad"]?></td>
                    <td width="10%"align="center"><?=fecha($res->fields["fecha"])?></td>
                    <td width="10%" align="center"><?=$res->fields["nro_serie_desde"]?></td>
                    <td width="10%" align="center"><?=$res->fields["nro_serie_hasta"]?></td>                    
                  </tr>
			     <?
			        $res->movenext();
			     } //del for
			     ?>
        </table>       
     </td>
    </tr>
  </table>
</form>

<?
echo fin_pagina();
?>


