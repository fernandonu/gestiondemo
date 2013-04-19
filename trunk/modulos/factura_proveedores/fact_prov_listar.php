<?php
require_once("../../config.php");
echo $html_header;

if (permisos_check("inicio","permiso_ver_fact"))
               $permiso="";
            else
               $permiso="disabled";

$msg=$parametros['msg'];
echo "<div align='center'><font size=2 color=blue>".$msg."</font></div>";
?>

<script language="javascript">
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;src.style.cursor="hand";
}
function bajo(src,color_default) {
    src.style.backgroundColor=color_default;src.style.cursor="default";
}
</script>


<form name="form1" method="post" action="fact_prov_listar.php">
<?php 
echo "<table align=center cellpadding=5 cellspacing=0 >";
echo "<tr>";
echo "<td> <input type='button' name='Cargar' value='Nueva Factura' $permiso onClick=\"document.location='".encode_link("fact_prov_subir.php",array())."'\" " ; if ($parametros['nro_orden'] || $nro_orden) echo 'disabled'; echo "> &nbsp;&nbsp; </td>";
echo "<td>\n";

// Formulario de busqueda
// Variables necesarias

	
variables_form_busqueda("fact_prov",array("nro_orden" => $parametros['nro_orden'],"estado" => $parametros['estado'],"fila" => $parametros['fila'],"cant_factura" => $parametros['cant_factura']));

$itemspp=50;

// Fin variables necesarias
if ($up=="") $up = "1";   // 1 ASC 0 DESC
$orden = Array (
"default" => "1",
"1" => "nro_factura",
"2" => "razon_social",
"3" => "fecha_emision",
"4" => "monto",
"5" => "id_factura",
"6" => "comentario",
"7" => "fact_prov.nbre_fantasia",
"8" => "fact_prov.cuit",
"9" => "tipo_fact",
"10" => "imp_internos",
"11" => "guardar_en");

$filtro = Array (
"nro_factura" => "Nº Factura",
"razon_social" => "Proveedor",
"fecha_emision" => "Fecha Emisión",
"monto" => "Monto",
"id_factura" => "ID factura",
"comentario" => "Comentario",
"fact_prov.nbre_fantasia" => "Nombre Fantasia",
"fact_prov.cuit" => "cuit",
"tipo_fact" => "Tipo factura",
"imp_internos" => "Impuesto interno",
"guardar_en" => "Guardada en");

$sql_temp="select fact_prov.*,proveedor.razon_social,fa.id2 ";
$sql_temp.="from fact_prov join ";
$sql_temp.="proveedor using (id_proveedor) ";
$sql_temp.="left join (select distinct(id_factura) as id2 from factura_asociadas) fa on fact_prov.id_factura=fa.id2 ";
list($sql,$total,$link_pagina,$up2) = form_busqueda($sql_temp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");

echo "&nbsp;&nbsp;&nbsp;<input type=submit name='form_busqueda' value='   Buscar   '>";
echo "</td>"; 
echo "</tr>\n";
echo "</table>\n";
$res_query = sql($sql) or die();

?>
<br>
<table border=0 width=100% cellspacing=2 cellpadding=3 >
  <tr>
  <td colspan=6 align=left id=ma> <? echo "\n";?>
	<table width=100%>
	 <tr id=ma><? echo "\n";?>
	  <td width=30% align=left><b><? echo "Total:</b> $total Facturas.</td>\n";?>
      <td width=70% align=right><? echo $link_pagina ?></td> <? echo"\n";?>
	 </tr>
	</table> <? echo "\n";?>
  </td>
  </tr>
  <tr>
      <td width="10%" align="center" id=mo><a id=mo href='<? echo encode_link("fact_prov_listar.php",Array('sort'=>5,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>ID Factura</b></a></td>
      <td width="10%" align="center" id=mo><a id=mo href='<? echo encode_link("fact_prov_listar.php",Array('sort'=>1,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Nº Factura</b></a></td>
       <td width="10%" align="center" id=mo><a id=mo href='<? echo encode_link("fact_prov_listar.php",Array('sort'=>9,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Tipo Factura</b></a></td>
      <td width="23%" align="center" id=mo><a id=mo href='<? echo encode_link("fact_prov_listar.php",Array('sort'=>2,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Proveedor</b></a></td>
      <td width="14%" align="center" id=mo><a id=mo href='<? echo encode_link("fact_prov_listar.php",Array('sort'=>3,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Fecha Emisión</b></a></td>
      <td width="18%" align="center" id=mo><a id=mo href='<? echo encode_link("fact_prov_listar.php",Array('sort'=>4,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Monto</b></a></td>
     
  </tr>
<? $cont_filas=0;
  while (!$res_query->EOF )
  {
  if ($cnr==1)
  {$color1=$bgcolor1;
   $color =$bgcolor2;
   $cnr=0;
  }
else
  {$color1=$bgcolor2;
   $color =$bgcolor1;
   $cnr=1;
  }

  //si no tiene orden asociada
  if ($res_query->fields['id2']=="")
  {
  //si son $ controlo que sea mayor o igual a $200
  if ($res_query->fields['moneda']==1 && $res_query->fields['monto']>=200)
   $color="#FF8080";
  elseif ($res_query->fields['monto']>=100 && $res_query->fields['monto']*$res_query->fields['cotizacion_dolar']>=200)
   $color="#FF8080";
  }

  //guardamos en esta variable, las observaciones de la licitacion
 //para mostrarlos en title del nombre de la licitacion
	$title_obs=$res_query->fields['comentario'];

 //LIMITAR OBSERVACIONES: controlamos el ancho y la cantidad de
 //lineas que tienen las observaciones y cortamos el string si
 //se pasa de alguno de los limites
	$long_title=strlen($title_obs);
	//cortamos si el string supera los 600 caracteres
	if($long_title>600)
		{$title_obs=substr($title_obs,0,600);
    	 $title_obs.="   SIGUE >>>";
		}
		$count_n=str_count_letra("\n",$title_obs);
		//cortamos si el string tiene mas de 12 lineas
		if($count_n>12)
		{$cn=0;$j=0;
		 for($i=0;$i<$long_title;$i++)
		 {
		  if($cn>12)
		   $i=$long_title;
		  if($title_obs[$i]=="\n")
		   $cn++;
		  $j++;

		 }
		 $title_obs=substr($title_obs,0,$j);
		 $title_obs.="   SIGUE >>>";
		}
  
 //echo encode_link("../ord_compra/ord_compra_fin", array("fact" =>$res_query->fields["id_factura"],'nro_orden'=>$parametros['nro_orden'],"fila"=>$parametros['fila'],"cant_factura"=>$parametros['cant_factura'],"fecha"=>$res_query->fields['fecha_emision'])); 
?>
   <tr  bgcolor='<?php echo $color; ?>' onMouseOver="sobre(this,'#FFFFFF');" onMouseOut="bajo(this,'<? echo $color?>' );" title="<? echo $title_obs ?>" <? if ($nro_orden){ ?>onClick="nro=window.opener.document.all.id_factura_<?=$fila; }?>;
			 	  nro.value='<? echo $res_query->fields['id_factura']?>' ; fecha=window.opener.document.all.fecha_factura_<?=$fila;?>;fecha.value='<? echo Fecha($res_query->fields['fecha_emision'])?>'; window.close()">
   
   <a href="<? if (!window.opener) { echo encode_link("fact_prov_subir.php", array("fact" =>$res_query->fields["id_factura"])); }?>" >   <!--//entra desde el menu factura de proveedores-->
  
       <td align="center"><font color="<? echo $color1?>"><b><? echo $res_query->fields['id_factura'] ?></b></font></td> 
	   <td align="center" title="tipo <?=$res_query->fields['tipo_fact']?>"><font color="<? echo $color1?>"><b><? echo $res_query->fields['nro_factura'] ?></b></font></td>
	   
	   <td align="center"><font color="<? echo $color1?>"><b><? echo $res_query->fields['tipo_fact'] ?></b></font></td>
       <td align="center"><font color="<? echo $color1?>"><b><? echo $res_query->fields['razon_social'] ?></b></font></td>
       <td align="center"><font color="<? echo $color1?>"><b><? echo Fecha($res_query->fields['fecha_emision']) ?></b></font></td>
       <td align="center"><font color="<? echo $color1?>"><b><? if ($res_query->fields['moneda']==2 && $res_query->fields['monto_dolar']==0) echo 'U$S '.formato_money($res_query->fields['monto']);else echo '$ '.formato_money($res_query->fields['monto']); ?></b></font></td>
      </a>
  </tr>
  <? 		
     $cont_filas++;
	$res_query->MoveNext();
  }  ?>
</table>


<? if ($total==0) echo " <br><div align='center'><font color='red' size=3>" .'NO HAY FACTURAS DE PROVEEDORES CARGADAS'."</font></div>"?>

<p>&nbsp;</p>


</form>

</body>
</html>