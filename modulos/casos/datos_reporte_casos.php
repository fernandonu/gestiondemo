<?

/*
$Author: mari $
$Revision: 1.3 $
$Date: 2006/06/06 22:01:19 $
*/

require_once ("../../config.php");
require_once("funciones.php");

$session=array("fec"=>"","atendido"=>"","cas"=>"");
variables_form_busqueda("datos_reportes",$session);
$filtro = array(
			"nro_orden" => "Nº orden",
			"fecha" => "Fecha Emision",
			"razon_social" => "Proveedor",
			"total" => "Total"
		);

$orden = array(
			"default_up" => "0",
			"default" => "2",
			"1" => "nro_orden",
			"2" => "fecha",
			"3" => "razon_social",
			"4" => "total"
		);
				
echo $html_header;
		
?>
<form name='form1' action="datos_reporte_casos.php" method='post'>
<?
$fecha=$parametros['fec'] or $fecha=$_POST['fec'] or $fecha=$_ses_datos_reportes['fec'];
$atendido=$parametros['atendido'] or $atendido=$_POST['atendido'] or $atendido=$_ses_datos_reportes['atendido'];
$cas=$parametros['cas'] or $cas=$_POST['cas'] or $cas=$_ses_datos_reportes['cas'];
?>
<input type='hidden' name="fec" value='<?=$fecha?>'>
<input type='hidden' name="atendido" value='<?=$atendido?>'>
<input type='hidden' name="cas" value='<?=$cas?>'>
<?
$fecha_split=split("-",$fecha);

$mes=mes_let_a_num($fecha_split[0])+1;

$anio=$fecha_split[1];
if ($mes==13) {
$mes="01";
$anio++;
}
elseif ($mes <10) {
   $mes="0".$mes;
}
$fecha=$anio."-".$mes."-%";
   
    $sql="select distinct (nro_orden),total as total,orden_de_compra.fecha,simbolo,razon_social
          from 
          (select sum(cantidad*precio_unitario) as total,nro_orden
          from compras.orden_de_compra join compras.fila using(nro_orden)
          where (flag_honorario=1) and fecha ilike '$fecha' 
          and estado <> 'n'
          group by nro_orden) as r
          join compras.orden_de_compra using (nro_orden)
          join licitaciones.moneda using (id_moneda) 
          join general.proveedor using (id_proveedor)";
  if ($atendido != -1) {
   $sql.="join compras.fila using (nro_orden) 
          join casos.casos_cdr cas on fila.id_fila=cas.fila";
   $sql.= " where idate=$atendido";
  }

  ?>

<table width="95%" align="center" cellpadding="3" cellspacing='0' bgcolor="White" class="bordes">
<tr>
 <?echo "<div align='center'> <font color='blue' size='+1'>
     OC para honorarios de Serv. Técnico.</div></font>";?>
 </tr>
<tr align="center">
<td>
<?
list($sql,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,"",$where_tmp,"buscar");
$res=sql($sql,"$sql") or fin_pagina();
?>
<input type='submit' name='Buscar' value='Buscar'>
</td>
</tr>
</table>
<br>
<table class="bordessininferior" width="95%" align="center" cellpadding="3" cellspacing='0'>
   <tr id=ma>
      <td align=left> <b>Cantidad OC:</b>  <?=$total?></td>
      <td align="right"><?=$link_pagina;?></td>
   </tr>
</table>

<table width='95%' class="bordessinsuperior" cellspacing='2' align="center">   
   <tr id=mo>
     <td><a href='<?=encode_link('datos_reporte_casos.php',array("sort"=>"1","up"=>$up))?>'>Nº Orden</a></td>
     <td><a href='<?=encode_link('datos_reporte_casos.php',array("sort"=>"2","up"=>$up))?>'>Fecha Emision</a></td>
     <td><a href='<?=encode_link('datos_reporte_casos.php',array("sort"=>"3","up"=>$up))?>'>Proveedor</a></td>
     <td><a href='<?=encode_link('datos_reporte_casos.php',array("sort"=>"4","up"=>$up))?>'>Monto</a></td>
  </tr>
   <? 
   $sumas=0;
   while(!$res->EOF) {
        $nro_orden=$res->fields['nro_orden'];
   ?>
   <tr <?=atrib_tr();?>  style="cursor:hand">
 
   <a href='<?=encode_link('../ord_compra/ord_compra.php',array("nro_orden"=>$nro_orden))?>' target="_blank">
      <td align="center"><?=$res->fields['nro_orden']?></td>   
   </a>  
   <a href='<?=encode_link('../ord_compra/ord_compra.php',array("nro_orden"=>$nro_orden))?>' target="_blank">
      <td align="center"><?=fecha($res->fields['fecha'])?></td>   
   </a>  
   <a href='<?=encode_link('../ord_compra/ord_compra.php',array("nro_orden"=>$nro_orden))?>' target="_blank">
      <td align="center"><?=$res->fields['razon_social']?></td>   
   </a>     
   <a href='<?=encode_link('../ord_compra/ord_compra.php',array("nro_orden"=>$nro_orden))?>' target="_blank">
      <td >
         <table align="center" width="50%">
           <tr>
             <td align="left">
             <?=$res->fields['simbolo'];?>
             </td>
             <td align="right">
             <?=formato_money($res->fields['total'])?>
             </td>
            </tr>
         </table>    
     </td>   
   </a>
  
   </tr>
   <?
   $sumas+=$res->fields['total'];
   $res->MoveNext();
   }?>
   <tr align="center">
       <td colspan="3"><input type="button" name="cerrar" value="Cerrar" onclick="window.close();"> </td>
       <td><?="<b> TOTAL \$ ".formato_money($sumas)."</b>"?> </td>
   </tr>
</table>


</form>