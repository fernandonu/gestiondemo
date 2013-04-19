<?php
/*

Pagina para ver las ordenes del gestion 2
Autor: Carlitos
Creado: 26/09/2004

ordenes_ver.php
$Author: marco_canderle $
$Revision: 1.1 $
$Date: 2006/01/04 08:49:06 $
*/

require_once "../../config.php";
//print_r($_SERVER["PHP_SELF"]);


//variable para volver a ordcompra
$back=$parametros['back'] or $back=$_POST['back'];
if ($back && $_ses_global_back!=$back) {
	phpss_svars_set("_ses_global_back", $back);
	phpss_svars_set("_ses_global_nro_orden_asociada", $parametros['nro_orden']);
	phpss_svars_set("_ses_global_pag", $parametros['pag']);
	
}

if ($_POST['enviar_mail']=="Enviar Mail")
{require_once("ordenes_mail.php");
 die;
}

//se cambian los valores de las variables de sesion cuando viene de la pagina
//de licitaciones para que tome el id de lic y el estado todas
if ($parametros["volver_lic"]) {
	phpss_svars_set("_ses_ordenes_ver",$parametros);        
}
//////

echo $html_header;
variables_form_busqueda("ordenes_ver_nueva");//para que funcione el form busqueda

//armo la barra de navegacion
if ($cmd == "") {
	$cmd="apa";
    phpss_svars_set("_ses_ordenes_ver_cmd", $cmd);
}
//si vengo desde ordenes de quemado
if ($parametros["back"]=="../ordquem/listado_ordenes.php"){
	$cmd="at";
    phpss_svars_set("_ses_ordenes_ver_cmd", $cmd);	
}
//apa= Pendientes
//ap= Para Autorizadas
//aa= Autorizada
//an= Anuladas
//ar= Rechazadas
//at= Terminadas
//ta= Todas
$datos_barra = array(
					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "apa",
						),
					array(
						"descripcion"	=> "ParaAutorizar",
						"cmd"			=> "ap"
						),
					array(
						"descripcion"	=> "Autorizadas",
						"cmd"			=> "aa"
						),
					//array(
					//	"descripcion"	=> "Rechazadas",
					//	"cmd"			=> "ar"
					//	),
				    array(
						"descripcion"	=> "Enviadas",
						"cmd"			=> "en"
						),
				    //array(
					//	"descripcion"	=> "Anuladas",
					//	"cmd"			=> "an"
					//	),
				    //array(
					//	"descripcion"	=> "Terminadas",
					//	"cmd"			=> "at"
					//	),
					array(
						"descripcion"	=> "Todas",
						"cmd"			=> "ta"	
						),
			      
				     );//Prepara los datos para armar la barra de navegación

echo "</br>";
generar_barra_nav($datos_barra);
//fin de barra de navegacion

?>
<form name="ordenes_ver_nueva" action="ordenes_ver.php" method="post">
<!--*******************para el from busqueda************************-->
<?

$seleccion = array (
		"nserie" => "nro_orden in (select nro_orden from maquina where nro_serie ilike '%$keyword%')"
);
$ignorar = array(0  => "nserie");
$orden = array(
		"default" => "2",
 		"default_up" => "0",
		"1" => "orden_de_produccion.fecha_entrega ",
		"2" => "entidad.nombre",
		"3" => "orden_de_produccion.lugar_entrega",
		"4" => "orden_de_produccion.cantidad",
		"5" => "ensamblador.nombre",
		"6" => "orden_de_produccion.nro_orden",
		"7" => "orden_de_produccion.id_licitacion"
);

$filtro = array(
		//"maquina.nro_serie" => "Nro. de Serie",
		"nserie" => "Nro. de Serie",
		"ensamblador.nombre" => "Ensamblador",
		"entidad.nombre" => "Cliente",
		"orden_de_produccion.fecha_entrega" => "Fecha Entrega",
		"orden_de_produccion.fecha_inicio" => "Fecha Inicio",
		"orden_de_produccion.nro_orden" => "Nro. Orden",
		"orden_de_produccion.id_licitacion" => "ID. Licitación"
	      );	
	
$estados_array= Array(
	"A"=>"Autorizada",
	"T"=>"Terminada",
	"R"=>"Rechazada",
	"AN"=>"Anulada",
	"PA"=>"Para Autorizar",
	"E"=>"Enviada"
);
$query="select orden_de_produccion.nro_orden,orden_de_produccion.id_licitacion,orden_de_produccion.estado,
		orden_de_produccion.fecha_entrega,orden_de_produccion.cantidad,orden_de_produccion.lugar_entrega,
		entidad.nombre,ensamblador.nombre as nombre_ensamblador from orden_de_produccion
        left join entidad using (id_entidad) "
        //."left join maquina using (nro_orden)"
        ."left join ensamblador using (id_ensamblador)";
        	
$where="";

if($cmd=="apa")
{$where=" orden_de_produccion.estado='P' or orden_de_produccion.estado='R'";
 $contar="select count(nro_orden) from orden_de_produccion where orden_de_produccion.estado='P' or orden_de_produccion.estado='R'";
}
if($cmd=="ap")
{$where=" orden_de_produccion.estado='PA'";
 $contar="select count(nro_orden) from orden_de_produccion where orden_de_produccion.estado='PA'";
}
if($cmd=="aa")
{$where=" orden_de_produccion.estado='A'";
 $contar="select count(nro_orden) from orden_de_produccion where orden_de_produccion.estado='A'";
}
/*if($cmd=="ar")
{$where=" orden_de_produccion.estado='R'";
 $contar="select count(*) from orden_de_produccion where orden_de_produccion.estado='R'";
}*/
if($cmd=="en")
{$where=" orden_de_produccion.estado='E'";
 $contar="select count(nro_orden) from orden_de_produccion where orden_de_produccion.estado='E'";
}
/*if($cmd=="an")
{$where=" orden_de_produccion.estado='AN'";
 $contar="select count(*) from orden_de_produccion where orden_de_produccion.estado='AN'";
}
if($cmd=="at")
{$where=" orden_de_produccion.estado='T'";
 $contar="select count(*) from orden_de_produccion where orden_de_produccion.estado='T'";
}*/
if($cmd=="ta")
{
 $contar="select count(nro_orden) from orden_de_produccion";
}


echo "<br>";
echo "<center>";
if($_POST['keyword'] || $keyword || $_POST['estado']!="all")// en la variable de sesion para keyword hay datos)
//$contar="buscar";


list($sql,$total_pedidos,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,$contar,"",$ignorar,$seleccion); 
$resultado=sql($sql) or fin_pagina();

?>	
<script>
var contador=0;
function habilitar_aceptar(valor)
{
 if (valor.checked)
             contador++;
             else
             contador--;
 if (contador>=1)
    {window.document.all.enviar_mail.disabled=0;
      
    }    
    else
    {window.document.all.enviar_mail.disabled=1;
    
    }
}//fin function
</script>

&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/ordprod/busca_ord.htm" ?>', 'BUSCAR ORDENES DE PRODUCCION')">

</center>
<br>
<!--<table border=0 width="95%" align="center"  cellspacing='0' bgcolor=<?=$bgcolor3?>>
 <tr id=ma>
  <td align="left">
   <b>Total: <?=$total_pedidos?> Ordene/s Encontrada/s.</b>
   <input name="total_pedidos" type="hidden" value=<?=$total_pedidos?>>
  </td>
  <td align="right">
   <?=$link_pagina?>
  </td>
 </tr>
</table>-->
<table align="center" width="95%" cellspacing="2" cellpadding="2" class="bordes">
<tr id=ma>
  <td align="left" colspan="5">
   <b>Total: <?=$total_pedidos?> Orden/es Encontrada/s.</b>
   <input name="total_pedidos" type="hidden" value=<?=$total_pedidos?>>
  </td>
  <td align="right" colspan="<? if ($cmd=="aa" or $cmd=="ta") echo "4";else echo "3"; ?>">
   <?=$link_pagina?>
  </td>
 </tr>


 <tr id=mo>
 <?
  if ($cmd=="aa")
  {
 ?>
   <td>&nbsp;</td>
 <?
  }	 
 ?>
  <td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","up"=>$up))?>'>Id. Lic.</a></b></td>
  <td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Entrega</a></b></td>
  <td width="20%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Cliente</a></b></td>
  <td width="30%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Dirección de Entrega</a></b></td>
  <td width="7%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Cantidad</a></b></td>
  <? if ($cmd=="ta") {?><td width="7%"><b>Estado</b></td><?}?>
  <td width="15%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Ensamblador</a></b></td>
  <td width="5%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Nro. orden</a></b></td>
  <td width="8%">&nbsp;</td>
 </tr>
<?
$j=0;
while (!$resultado->EOF) 
{
	if ($cmd=="apa" && $resultado->fields["estado"]=="R") $col="bgcolor=yellow";
	elseif ($cmd=="ta" && $resultado->fields["estado"]=="AN") $col="bgcolor=red";
	elseif ($cmd=="ta" && $resultado->fields["estado"]=="R") $col="bgcolor=yellow";
	else $col="";
?>
 <tr <?= atrib_tr();?>><? if ($_ses_global_pag=='asociar') 
                         {?>
                           <a href="<?=encode_link($_ses_global_back,array('orden_prod'=>$resultado->fields["nro_orden"]))?>"> 
                         <? 
                         } else 
                          {
                         ?>
						<a href="<?=encode_link('ordenes_nueva_gestion_2.php',array("modo"=>"modificar","nro_orden"=>$resultado->fields["nro_orden"],"volver"=>"ordenes_ver.php"));?>">
						<?}?>

<?
if ($cmd=="aa")
{
?>
      <td>
      <input type="checkbox" name="check[<?= $j; ?>]" value="<?=$resultado->fields['nro_orden'];?>" onclick="habilitar_aceptar(this)">
      </td>
      
<?
$j++;
}
?>
  <td <?=$col;?> width="8%" align="center">
   <?=$resultado->fields['id_licitacion']?>
  </td>
  <td <?=$col;?> width="8%" align="center">
   <?=fecha($resultado->fields['fecha_entrega'])?>
  </td>
  <td <?=$col;?> width="20%" align="center">
   <?=$resultado->fields['nombre']?>
  </td>
  <td <?=$col;?> width="30%" align="center">
   <?=$resultado->fields['lugar_entrega']?>
  </td>
  <td <?=$col;?> width="7%" align="center">
   <?=$resultado->fields['cantidad']?>
  </td>
  <? if ($cmd=="ta") {?>
  <td <?=$col;?> width="15%" align="center">
   <?=$estados_array[$resultado->fields['estado']]?>
  </td>
  <? } ?>	
  <td <?=$col;?> width="15%" align="center">
   <?=$resultado->fields['nombre_ensamblador']?>
  </td>
  <td <?=$col;?> width="5%" align="center">
   <?=$resultado->fields['nro_orden']?>
  </td>
  <td <?=$col;?> width="8%" align="center">
   <div align="center"><?
       if($resultado->fields['estado']!='P' and $resultado->fields['estado']!='PA')
       {?><A href="<? echo './pdf/ordendeproduccion_'.$resultado->fields["nro_orden"].'.pdf';?>" target="_new"><img border=0 src="img/pdf.gif" width="16" height="16"></A></div>
       <?}?>  
  </td>
 </tr></a>
<?
 $resultado->MoveNext();
}
?>
</table>

<center>
<?
if ($cmd=="aa")
{
?>
<br>
<INPUT type="hidden" name="cant_check" value="<?=$resultado->RecordCount()-1;?>">
<input type="submit" name="enviar_mail" value="Enviar Mail" style="width=100" disabled>
<br>
<?
}
if ($parametros["volver_lic"]) {
		$ref = encode_link($html_root."/index.php",array("menu" => "licitaciones_view","extra" => array("cmd1"=>"detalle","ID"=>$parametros["volver_lic"])));
		echo "<tr><td align=center colspan=2><br><input type=button name=volver style='width:320;' value='Volver a los detalles de la licitacion' onClick=\"parent.document.location='$ref';\"></td></tr>\n";
	}
?>
</center>
</form>
<? if ($cmd=="ta"){ ?>
<br>
<center>
<div align="left" style="background-color: white;border: solid ;border-width: 1;overflow-y: auto; width: 95%;">
<b><font size=2>Colores de referencia</font><b><br>
<table border=0 width=600px>
<tr>
	<td align="right">
		Ordenes Anuladas: 
	</td>
	<td width="30px">
		<div style="background-color: red; border: solid;border-width: 1;width:30;height:10"></div>
	</td>
	<td align="right">
		Ordenes Rechazadas: 
	</td>
	<td width="30px">
		<div style="background-color: yellow; border: solid;border-width: 1;width:30;height:10"></div>
	</td>
</tr>
</table>
</div>
<center>
<? 
}
if ($cmd=="apa"){ ?>
<br>
<center>
<div align="left" style="background-color: white;border: solid ;border-width: 1;overflow-y: auto; width: 95%;">
<b><font size=2>Colores de referencia</font><b><br>
<table border=0 width=200px>
<tr>
	<td align="right">
		Ordenes Rechazadas: 
	</td>
	<td width="30px">
		<div style="background-color: yellow; border: solid;border-width: 1;width:30;height:10"></div>
	</td>
</tr>
</table>
</div>
<center>
<? 
}
fin_pagina();
?>