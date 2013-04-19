<?

/*
$Author: mari $
$Revision: 1.7 $
$Date: 2006/10/11 13:46:22 $
*/

require_once ("../../config.php");


$session=array("fecha_desde"=>"","fecha_hasta"=>"");

if ($_POST && $_POST['fecha_desde']==""){
	$_ses_informe_casos["fecha_desde"]="";
    phpss_svars_set("_ses_informe_casos",$_ses_informe_casos);
}

if ($_POST && $_POST['fecha_hasta']==""){
	$_ses_informe_casos["fecha_hasta"]="";
    phpss_svars_set("_ses_informe_casos",$_ses_informe_casos);
}

variables_form_busqueda("informe_casos",$session);

$orden = array(
     "default" => "1",
     "default_up" => "0",
     "1"=>"nrocaso",
     "2"=>"ensamblador.nombre",
     "3"=>"entidad.nombre",
     "4"=>"origen_falla.descripcion",
	 "5"=>"desc_falla",
	 "6"=>"orden_de_produccion.id_licitacion",
	 "7"=>"fechainicio",
	 "8"=>"casos_cdr.nserie"
		);

$filtro = array(
		"nrocaso"=>"Nro Caso",
		"ensamblador.nombre"=>"Ensamblador",
		"entidad.nombre"=>"Cliente",
		"origen_falla.descripcion"=>"Origen falla",
		"desc_falla"=>"Falla",
		"orden_de_produccion.id_licitacion"=>"Id Lic",
		"fechainicio"=>"Fecha Inicio",
		"casos_cdr.nserie"=>"Nº serie"
		);

echo $html_header;
cargar_calendario();

if (!$fecha_desde) $fecha_desde=date("d/m/Y");
if (!$fecha_hasta) $fecha_hasta=date("d/m/Y");

?>
<form name='form1' action="informe_casos.php" method="POST">
<?
$query="select nrocaso,entidad.nombre as cliente,orden_de_produccion.id_licitacion,casos_cdr.nserie,
        desc_falla as falla,origen_falla.descripcion as desc_origen_falla,
        idestuser,cas_ate.nombre as cas, ciudad,ensamblador.nombre as ensamblador,fechainicio
        from casos.casos_cdr
        join casos.estadousuarios using (idestuser)
        join casos.dependencias using (id_dependencia)
        join licitaciones.entidad using (id_entidad)
        join casos.cas_ate using (idate)
        left join casos.origen_falla using (id_origen_falla)
        left join casos.fallas using (id_falla)
        left join ordenes.maquina on casos_cdr.nserie=maquina.nro_serie
        left join ordenes.orden_de_produccion using(nro_orden)
        left join ordenes.ensamblador using(id_ensamblador)";

$where=" fechainicio between '".fecha_db($fecha_desde)."' and '".fecha_db($fecha_hasta)."'";
?>
<table align="center" width="100%">
<tr>
<td width="15%">&nbsp;</td>
<td width="70%" align="center">
  <font size="2" color="Blue"> Informe de casos derivados desde <?=$fecha_desde?> al <?=$fecha_hasta?></font>
</td>
<td align="right" width="15%"><input type='button' name='cerrar' value='Cerrar' onclick='window.close();'></td>
</tr>
</table>
<br>
<table align="center" width="95%" class="bordes" bgcolor="White">
 <tr>
   <td align="center">
    <?
    $itemspp=5000;
    list($query,$total,$link_pagina,$up,$suma) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar",$sumas);
    ?>
   </td>
   <td> <b>Desde</b> <input type="text" name="fecha_desde" value="<?=$fecha_desde?>" size="10" readonly><?=link_calendario("fecha_desde")?> </td>
   <td> <b>Hasta</b> <input type="text" name="fecha_hasta" value="<?=$fecha_hasta?>" size="10" readonly><?=link_calendario("fecha_hasta")?> </td>
   <td><input type='submit' name="Buscar" value='Buscar'></td>
   <? $link=encode_link("informe_excel_listado.php",array("sql"=>$query,"total"=>$total,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta));?>
   <td><img src="../../imagenes/excel.gif" style='cursor:hand;'  onclick="window.open('<?=$link?>')"></td>
   
   </tr>
</table>
  <?
 $datos=sql($query,"<br>Error al realizar consulta para traer los datos del listado<br>") or fin_pagina();

 ?>
 <br>
 <table width="100%" class="bordessininferior">
  <tr>
   <td id=ma_sf>
    <table width="100%">
     <tr  id=ma_sf>
      <td align=left>
       <b>Cant Casos : </b><?=$total?>
       </td>
       <td align="right">
        <?=($link_pagina)?$link_pagina:"&nbsp;"?>
       </td>
      </tr>
    </table>
   </td>
  </tr>
 </table>
 <table width="100%" class="bordessinsuperior">
  <tr id=mo >
   <td width="5%">
    <a id=mo href='<?=encode_link("informe_casos.php",array("sort"=>"1","up"=>$up))?>'>
     Caso
    </a>
   </td>
    <td width="5%">
    <a id=mo href='<?=encode_link("informe_casos.php",array("sort"=>"7","up"=>$up))?>'>
     Fecha Inicio
    </a>
   </td>
    <td width="5%">
    <a id=mo href='<?=encode_link("informe_casos.php",array("sort"=>"6","up"=>$up))?>'>
     Id Lic.
    </a>
   </td>
   <td width="10%">
    <a id=mo href='<?=encode_link("informe_casos.php",array("sort"=>"2","up"=>$up))?>'>
     Ensamblador
    </a>
   </td>
   <td width="10%">
    <a id=mo href='<?=encode_link("informe_casos.php",array("sort"=>"8","up"=>$up))?>'>
     Nro Serie
    </a>
   </td>
   <td width="25%">
    <a id=mo href='<?=encode_link("informe_casos.php",array("sort"=>"3","up"=>$up))?>'>
     Cliente
    </a>
   </td>
   <td width="5%">
    <a id=mo href='<?=encode_link("informe_casos.php",array("sort"=>"5","up"=>$up))?>'>
     Falla
    </a>
   </td>
   <td width="5%">
    <a id=mo href='<?=encode_link("informe_casos.php",array("sort"=>"4","up"=>$up))?>'>
     Origen Falla
    </a>
   </td>
   <td width="10%">
    <a id=mo>
     Estado
    </a>
   </td>
   <td width="10%">
    <a id=mo>
     CAS
    </a>
   </td>


  </tr>
  <?

  while (!$datos->EOF)
  { ?>
  <tr <?=$atrib_tr?>>
    <?$ca=substr($datos->fields["nrocaso"],-5)?>
    <td> <?=$ca=str_pad($ca,5,"0",STR_PAD_LEFT);?></td>
    <td> <?=fecha($datos->fields["fechainicio"])?></td>
    <td> <?=$datos->fields["id_licitacion"]?></td>
    <td> <?if ($datos->fields["ensamblador"]) echo $datos->fields["ensamblador"]; else "&nbsp;" ?></td>
    <td> <?=$datos->fields["nserie"]?> </td>
    <td> <?=$datos->fields["cliente"]?> </td>
    <td> <?=$datos->fields["falla"]?> </td>
    <td> <?=$datos->fields["desc_origen_falla"]?> </td>

       <? switch ($datos->fields["idestuser"])
        {
         case "1":$estado_caso='En curso';break;
         case "2":$estado_caso='Finalizado';break;
         case "7":$estado_caso='Pendiente';break;

        } ?>
      <td> <?=$estado_caso;?></td>
      <?$cas=$datos->fields["cas"];
        if (!stristr($datos->fields["cas"], "Coradir"))
             $cas.=" - ".$datos->fields["ciudad"];?>
      <td> <?=$cas?>  </td>
     </tr>
    <?
  	$datos->MoveNext();
  }//de while(!$datos->EOF)
  ?>
 </table>

</form>