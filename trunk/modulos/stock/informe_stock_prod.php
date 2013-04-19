<?PHP
/*
Autor Broggi

$Author: ferni $
$Revision: 1.1 $
$Date: 2006/03/09 19:16:14 $
*/



require_once("../../config.php");

variables_form_busqueda("seguimiento_produccion_bsas");
echo $html_header; 
   
$orden = array(
       "default" => "1",
       //"default_up" => "1",
       "1" => "id_licitacion",
       "2" => "total", 
      ); 
      
$filtro = array(        
        "id_licitacion" => "ID de licitación",        
    );     
    
$sql_tmp="select id_licitacion, total, color
			from (
				select ep.id_licitacion,sum (p.precio_stock*ep.cantidad) as total,estado.color
				from stock.en_produccion ep
				join stock.en_stock  es using  (id_en_stock)
				join general.producto_especifico p using (id_prod_esp)
				join licitaciones.licitacion using (id_licitacion)
				join licitaciones.estado using (id_estado)
				where  ep.cantidad <> 0
				group by id_licitacion,estado.color
			) as a";

//$where_tmp=" ep.cantidad <> 0";

$contar="buscar";
if($_POST['keyword'] || $keyword) $contar="buscar";
     
?>
<form name="informe_stock_prod" action='informe_stock_prod.php' method='post'>
	
<table align="center" >
 <tr>
  <td>
<?
 list($sql,$total_lic,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar); 
 //echo "<br>".$sql."<br>";  
 $resul_consulta=sql($sql,"No se pudo realizar la consulta del form busqueda") or fin_pagina();
 
 $sql_est = "SELECT id_estado,nombre,color FROM estado";
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
  </td>
  <td>
   <input type=submit name=form_busqueda value='Buscar'>&nbsp;
  </td>
 </tr>
</table> 
<br>
<table width='80%' align="center" cellspacing="2" cellpadding="2" class="bordes">
 <tr id=ma>
  <td align="left" colspan="1">
   <b>Total:</b> <?=$total_lic?> <b>Licitacion/es.</b>   
  </td>
  <td align="right" colspan="2">
   <?=$link_pagina?>
  </td>
 </tr>
 
 <tr id=mo>
  <td width="40%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>ID Licitacion</a></b></td>
  <td width="60%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Monto Total</b></td>
 </tr>
  
    <? 
    while (!$resul_consulta->EOF){
    ?>
    <tr id=ma<?//atrib_tr()?>>
		   <?$frente="#000000";
     		$reemplazo="#ffffff";
     		$estado_lic_color=$resul_consulta->fields['color'];
     		$color_link=contraste($estado_lic_color, $frente, $reemplazo);?>
           <td align="center" bgcolor="<?=$estado_lic_color?>" style="font-size='16'; color='<?=$color_link?>';"><?=$resul_consulta->fields['id_licitacion']?></td>
           <td align="center"><font size="2"><b><?echo 'U$S ' . number_format($resul_consulta->fields['total'],2,',','.')?></b></font></td>
    </tr> 
    
  	<?$resul_consulta->MoveNext();
     }?>
</table> 
<br>
<table width='95%' border=0 align=center>
<tr><td colspan=6 align=center><br>
<table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>
<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia ID/Est:</b></td></tr>
<tr>
	<?
	$cont=0;
	foreach ($estados as $est => $arr) {
	if (!($cont % 3)) { echo "</tr><tr>"; }
		echo "<td width=33% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 wdith=100%><tr>";
		echo "<td width=15 bgcolor='".$estados[$est]["color"]."' bordercolor='#000000' height=15>&nbsp;</td>\n";
		echo "<td bordercolor='#FFFFFF'>".$estados[$est]["texto"]."</td>\n";
		echo "</tr></table></td>";
	   $cont++;
	}?>
</tr>
</table>
</td></tr>
</table><br>
	
<br>
</form>
<?=fin_pagina();?>

