<?php

/*
Autor: Broggi
Creado: 01/06/2004

$Author: cestila $
$Revision: 1.2 $
$Date: 2004/11/18 17:15:10 $
*/

require_once "../../config.php";
//print_r($_SERVER["PHP_SELF"]);


//variable para volver a ordcompra
$back=$parametros['back'] or $back=$_POST['back'];
if ($back && $_ses_global_back!=$back) {
	phpss_svars_set("_ses_global_back", $back);
	phpss_svars_set("_ses_global_nro_orden_asociada", $parametros['nro_orden_old']);
	phpss_svars_set("_ses_global_pag", $parametros['pag']);
	
}

if ($_POST['enviar_mail']=="Enviar Mail")
{require_once("ordenes_mail.php");
 die;
}

echo $html_header;
variables_form_busqueda("ordenes_ver_old");//para que funcione el form busqueda

//armo la barra de navegacion
if ($cmd == "") {
	$cmd="apa";
    phpss_svars_set("_ses_ordenes_ver_cmd", $cmd);
}
//si vengo desde ordenes de quemado
if ($parametros["back"]=="../ordquem/listado_ordenes.php"){
	$cmd="ac";
    phpss_svars_set("_ses_ordenes_ver_cmd", $cmd);	
}
//apa= Pendientes
//aa= Autorizadas
//ac= Enviadas
//at= Terminadas
//ta= Todas
$datos_barra = array(
					array(
						"descripcion"	=> "PENDIENTES",
						"cmd"			=> "apa",
						),
					array(
						"descripcion"	=> "AUTORIZADAS",
						"cmd"			=> "aa"
						),
				    array(
						"descripcion"	=> "ENVIADAS",
						"cmd"			=> "ac"
						),
				    array(
						"descripcion"	=> "TERMINADAS",
						"cmd"			=> "at"
						),
					array(
						"descripcion"	=> "TODAS",
						"cmd"			=> "ta"	
						),
			      
				     );//Prepara los datos para armar la barra de navegación

echo "</br>";
generar_barra_nav($datos_barra);
//fin de barra de navegacion

?>
<form name="ordenes_ver" action="ordenes_ver_old.php" method="post">
<!--*******************para el from busqueda************************-->
<?

$orden = array(
		"default" => "2",
 		"default_up" => "0",
		"1" => "orden_de_produccion_old.fecha_entrega ",
		"2" => "cliente_final.nombre",
		"3" => "orden_de_produccion_old.lugar_entrega",
		"4" => "orden_de_produccion_old.cantidad",
		"5" => "ensamblador.nombre",
		"6" => "orden_de_produccion_old.nro_orden_old",
              );

$filtro = array(
		"maquina.nro_serie" => "Nro. de Serie",
		"ensamblador.nombre" => "Ensamblador",
		"cliente_final.nombre" => "Cliente",
		"orden_de_produccion_old.fecha_entrega" => "Fecha Entrega",
		"orden_de_produccion_old.fecha_inicio" => "Fecha Inicio",
		"orden_de_produccion_old.nro_orden_old" => "Nro. Orden",
		"orden_de_produccion_old.id_licitacion" => "ID. Licitación"
	      );	
	

$query="select distinct (orden_de_produccion_old.nro_orden_old), orden_de_produccion_old.*,cliente_final.nombre, ensamblador.nombre as nombre_ensamblador from orden_de_produccion_old
        join cliente_final using (id_cliente)
        join ensamblador using (id_ensamblador)
        left join maquina on maquina.nro_orden=orden_de_produccion_old.nro_orden_old";
        	
$where="";

if($cmd=="apa")
{$where=" orden_de_produccion_old.estado=0";
 $contar="select count(*) from orden_de_produccion_old where orden_de_produccion_old.estado=0";
}
if($cmd=="aa")
{$where=" orden_de_produccion_old.estado=1";
 $contar="select count(*) from orden_de_produccion_old where orden_de_produccion_old.estado=1";
}
if($cmd=="ac")
{$where=" orden_de_produccion_old.estado=4";
 $contar="select count(*) from orden_de_produccion_old where orden_de_produccion_old.estado=3";
}
if($cmd=="at")
{$where=" orden_de_produccion_old.estado=2";
 $contar="select count(*) from orden_de_produccion_old where orden_de_produccion_old.estado=2";
}
if($cmd=="ta")
{
 $contar="select count(*) from orden_de_produccion_old";
}


echo "<br>";
echo "<center>";
if($_POST['keyword'] || $keyword || $_POST['estado']!="all")// en la variable de sesion para keyword hay datos)
 $contar="buscar";


list($sql,$total_pedidos,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,$contar); 
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
  <td align="left" colspan="4">
   <b>Total: <?=$total_pedidos?> Orden/es Encontrada/s.</b>
   <input name="total_pedidos" type="hidden" value=<?=$total_pedidos?>>
  </td>
  <td align="right" colspan="3">
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
  <td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Entrega</a></b></td>
  <td width="20%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Cliente</a></b></td>
  <td width="30%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Dirección de Entrega</a></b></td>
  <td width="7%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Cantidad</a></b></td>
  <td width="15%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Ensamblador</a></b></td>
  <td width="5%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Nro. orden</a></b></td>
  <td width="8%">&nbsp;</td>
 </tr>
<?
$j=0;
while (!$resultado->EOF) 
{
?>
 <tr <?=atrib_tr()?> <?if ($resultado->fields['id_licitacion']) {?> title="Licitación: <?=$resultado->fields['id_licitacion']?>"<?}?>><? if ($_ses_global_pag=='asociar') 
                         {?>
                           <a href="<?=encode_link($_ses_global_back,array('orden_prod'=>$resultado->fields["nro_orden_old"]))?>"> 
                         <? 
                         } else 
                          {
                         ?>
                           <a href="<?='control_orden.php?nro_orden='.$resultado->fields["nro_orden_old"].'&campo='.$campo.'&est='.$_GET['est'].'&keyword='.$campo2; ?>">
                          <?
                          }
                          ?>  

<?
if ($cmd=="aa")
{
?>
      <td>
      <input type="checkbox" name="check[<?= $j; ?>]" value="<?=$resultado->fields['nro_orden_old'];?>" onclick="habilitar_aceptar(this)">
      </td>
      
<?
$j++;
}
?>
  <td width="8%" align="center">
   <?=fecha($resultado->fields['fecha_entrega'])?>
  </td>
  <td width="20%" align="center">
   <?=$resultado->fields['nombre']?>
  </td>
  <td width="30%" align="center">
   <?=$resultado->fields['lugar_entrega']?>
  </td>
  <td width="7%" align="center">
   <?=$resultado->fields['cantidad']?>
  </td>
  <td width="15%" align="center">
   <?=$resultado->fields['nombre_ensamblador']?>
  </td>
  <td width="5%" align="center">
   <?=$resultado->fields['nro_orden_old']?>
  </td>
  <td width="8%" align="center">
   <div align="center"><?
       if($resultado->fields['aprobada']!=0)
       {?><A href="<? echo './pdf/ordendeproduccion_'.$resultado->fields["nro_orden_old"].'.pdf';?>"><img border=0 src="img/pdf.gif" width="16" height="16"></A></div>
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
?>
</center>
</form>
<?=fin_pagina();?>