<?php
/*
Autor: Carlitos
Creado: 26/09/2004

ordenes_ver.php
$Author: fernando $
$Revision: 1.66 $
$Date: 2007/03/06 21:19:42 $
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
  {
  require_once("ordenes_mail.php");
  die;
  }

//se cambian los valores de las variables de sesion cuando viene de la pagina
//de licitaciones para que tome el id de lic y el estado todas
if ($parametros["volver_lic"]) {
	//phpss_svars_set("_ses_ordenes_ver",$parametros);        
	phpss_svars_set("_ses_ordenes_ver_nueva",$parametros);        
    }
//////

echo $html_header;

variables_form_busqueda("ordenes_ver_nueva");

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

				    array(
						"descripcion"	=> "Enviadas",
						"cmd"			=> "en"
						),

					array(
						"descripcion"	=> "Todas",
						"cmd"			=> "ta"	
						),
			      
				     );//Prepara los datos para armar la barra de navegación

echo "</br>";
generar_barra_nav($datos_barra);
//fin de barra de navegacion

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
/*$query="select orden_de_produccion.nro_orden,orden_de_produccion.id_licitacion,orden_de_produccion.estado,
		orden_de_produccion.fecha_entrega,orden_de_produccion.cantidad,orden_de_produccion.lugar_entrega,
		entidad.nombre,ensamblador.nombre as nombre_ensamblador from orden_de_produccion
        left join entidad using (id_entidad) 
        left join ensamblador using (id_ensamblador)";*/

$query="select ordenes.orden_de_produccion.nro_orden,ordenes.orden_de_produccion.id_licitacion,ordenes.orden_de_produccion.estado,
		ordenes.orden_de_produccion.fecha_entrega,ordenes.orden_de_produccion.cantidad,ordenes.orden_de_produccion.lugar_entrega,
		entidad.nombre,ensamblador.nombre as nombre_ensamblador,lider,iniciales from ordenes.orden_de_produccion
        left join licitaciones.entidad using (id_entidad) 
        left join ordenes.ensamblador using (id_ensamblador)
        left join licitaciones.licitacion using (id_licitacion) 
        left join sistema.usuarios u1 on (lider=u1.id_usuario) 
";
        	
$where="";

if($cmd=="apa")
        {
         $where=" orden_de_produccion.estado='P' or orden_de_produccion.estado='R'";
//         $contar="select count(nro_orden) from orden_de_produccion where (orden_de_produccion.estado='P' or orden_de_produccion.estado='R')";
//         if(($_POST['keyword'] || $keyword)&&($filter!='all')&&($filter!='nserie')) $contar .= " AND ($filter ILIKE '%$keyword%')";         
        }
if($cmd=="ap")
        {
         $where=" orden_de_produccion.estado='PA'";
//         $contar="select count(nro_orden) from orden_de_produccion where (orden_de_produccion.estado='PA')";
//         if(($_POST['keyword'] || $keyword)&&($filter!='all')&&($filter!='nserie')) $contar .= " AND ($filter ILIKE '%$keyword%')";
        }
if($cmd=="aa")
        {
         $where=" orden_de_produccion.estado='A'";
//         $contar="select count(nro_orden) from orden_de_produccion where (orden_de_produccion.estado='A')";
//         if(($_POST['keyword'] || $keyword)&&($filter!='all')&&($filter!='nserie')) $contar.= " AND ($filter ILIKE '%$keyword%')";         
        }
if($cmd=="en")
        {
         $where=" orden_de_produccion.estado='E'";
//         $contar="select count(nro_orden) from orden_de_produccion where (orden_de_produccion.estado='E')";
//         if(($_POST['keyword'] || $keyword)&&($filter!='all')&&($filter!='nserie')) $contar .= " AND ($filter ILIKE '%$keyword%')";         
        }

if($cmd=="ta"){
//	$contar="select count(nro_orden) from orden_de_produccion";
//	if(($_POST['keyword'] || $keyword)&&($filter!='all')&&($filter!='nserie'))$contar .= " where $filter ILIKE '%$keyword%'";	
}
  
?>
<form name="ordenes_ver_nueva" action="ordenes_ver.php" method="post">
<br>
<center>
<?
if($_POST['keyword'] || $keyword || $_POST['estado']!="all")
// en la variable de sesion para keyword hay datos)
$contar="buscar";

list($sql,$total_pedidos,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,$contar,"",$ignorar,$seleccion); 
$resultado=sql($sql,"$sql") or fin_pagina();

$link_mail = encode_link("mail_ordenes.php",array());
?>	
&nbsp;&nbsp;
<input type=submit name=form_busqueda value='Buscar'>
&nbsp;&nbsp;
<input type="button" name="mail_ordenes" value="Mail Ordenes" onclick="window.open('<?=$link_mail?>')">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/ordprod/busca_ord.htm" ?>', 'BUSCAR ORDENES DE PRODUCCION')">
</center>
<br>
<table align="center" width="95%" cellspacing="2" cellpadding="2" class="bordes">
<tr id=ma>
  <td align="left" colspan="6">
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
  <td width="5%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Nro. orden</a></b></td> 
  <td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","up"=>$up))?>'>Id. Lic.</a></b></td>
  <td width="3%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","up"=>$up))?>'>Lid</a></b></td>
  <td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Entrega</a></b></td>
  <td width="20%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Cliente</a></b></td>
  <td width="30%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Dirección de Entrega</a></b></td>
  <td width="7%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Cantidad</a></b></td>
  <? if ($cmd=="ta") {?><td width="7%"><b>Estado</b></td><?}?>
  <td width="15%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Ensamblador</a></b></td>

  <td width="8%">&nbsp;</td>
 </tr>
<?
$j=0;
while (!$resultado->EOF) 
{
	if ($cmd=="apa" && $resultado->fields["estado"]=="R") 
              $col="bgcolor=yellow";
	elseif ($cmd=="ta" && $resultado->fields["estado"]=="AN")
              $col="bgcolor=red";
	elseif ($cmd=="ta" && $resultado->fields["estado"]=="R") 
             $col="bgcolor=yellow";
	else $col="";
?>
 <tr <?= atrib_tr();?>>
 <? if ($_ses_global_pag=='asociar') 
                    {?>
                    <a href="<?=encode_link($_ses_global_back,array('orden_prod'=>$resultado->fields["nro_orden"], "cmd"=>$cmd))?>"> 
                    <? 
                    } 
                    else 
                        {
							if ($resultado->fields['nro_orden']<1141){?>
                    			<a href="<?=encode_link('ordenes_nueva_gestion_2.php',array("modo"=>"modificar","nro_orden"=>$resultado->fields["nro_orden"],"volver"=>"ordenes_ver.php", "cmd"=>$cmd));?>">
							<?}
							else{?>
								<a href="<?=encode_link('ordenes_nueva.php',array("modo"=>"modificar","nro_orden"=>$resultado->fields["nro_orden"],"volver"=>"ordenes_ver.php", "gag_cmd"=>$cmd));?>">
							<?}?>
					<?
                    }
                    ?>

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
  <td <?=$col;?> width="5%" align="center">
   <?=$resultado->fields['nro_orden']?>
  </td>
  <td <?=$col;?> width="8%" align="center">
   <?=$resultado->fields['id_licitacion']?>
  </td>
  <?
  /*$lider=$resultado->fields['id_licitacion'];
  if($lider!="")
  {
  $consult="select  lider,iniciales from licitaciones.licitacion l
	                      left join sistema.usuarios u1 on (lider=u1.id_usuario)   
                          where id_licitacion=$lider";
  $ejecuta=sql($consult,"no se pudo recuperar el lider") or fin_pagina();
  }*/
  ?>
  <td <?=$col;?> width="8%" align="center">
   <?=$resultado->fields['iniciales']?>
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

  <td <?=$col;?> width="8%" align="center">
   <div align="center">
       <?
       if($resultado->fields['estado']!='P' and $resultado->fields['estado']!='PA')
       {
       $link8=encode_link("word_orden_produccion.php", array("nro_orden"=>$resultado->fields['nro_orden'],"formato"=>'new'));	
       ?>
       <A target='_blank' href='<?=$link8?>'><IMG src='<?=$html_root?>/imagenes/word.gif' height='16' width='16' border='0'></a>
       <?
       }
       ?>  
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
		echo "<tr><td align=center colspan=2><br><input type=button name=volver style='width:320;' value='Volver a los detalles de la licitacion' onClick=\"window.close();\"></td></tr>\n";
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