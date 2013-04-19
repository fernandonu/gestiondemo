<?php
/*
$Author: Broggi, Diego

modificado por
$Author: gabriel $
$Revision: 1.11 $
$Date: 2005/09/12 19:08:03 $
*/

include_once("../../config.php");
include_once("./funciones.php");

$id_entidad=$_POST['id_entidad'];
$entidad_nombre=$_POST['entidad_nombre'];

if ($id_entidad!="")
{$sql_est = "SELECT id_estado,nombre,color FROM licitaciones.estado";
 $result = sql($sql_est,"Error al traer los distintos estados") or fin_pagina();
 $estados = array();
 while (!$result->EOF) 
       {$estados[$result->fields["id_estado"]] = array(
		"color" => $result->fields["color"],
		"texto" => $result->fields["nombre"]
			);
        $result->MoveNext();
	   }


   	$sql="select id_licitacion, entidad.nombre, distrito.nombre as nom_dis, iniciales, 
   	      fecha_apertura, nro_lic_codificado, color
   	      from licitaciones.licitacion 
   	      join licitaciones.entidad using(id_entidad)
   	      left join sistema.usuarios on(lider=id_usuario)
   	      left join licitaciones.distrito using (id_distrito) 
   	      left join licitaciones.estado using (id_estado)
          where id_entidad=$id_entidad order by id_licitacion asc";
    $resul_sql=sql($sql,"Error al traer los id de licitaciones") or fin_pagina();
}	

echo $html_header;


?>


<form name="form1" method="post" action="asociado_a_lic.php">
<input type="hidden" name="id_entidad" value="">
<input type="hidden" name="entidad_nombre" value="">

<?
if ($id_entidad!="")
{
 if ($resul_sql->RecordCount()>0)
 {	
 ?>  
  <table width="100%" align="center" border="0">
   <tr>
    <td align="center">
     <font color="Red" size="3"><b>Entidad asociada a las Licitaciones:</b></font>
    </td>
   </tr>
  </table>
  <br>
  <table align="center" width="95%" class="bordes">
   <tr id="mo">
    <td align="center" width="10%"><b>ID / Est.</b></td>
    <td align="center" width="5%"><b>Lider</b></td>
    <td align="center" width="10%"><b>Apertura</b></td>
    <td align="center" width="40%"><b>Entidad</b></td>
    <td align="center" width="15%"><b>Distrito</b></td>
    <td align="center" width="20%"><b>Número</b></td>  
   </tr>
<?	
   while (!$resul_sql->EOF)
         {$ref = encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$resul_sql->fields["id_licitacion"]));
         	?>
         	<a href="<? echo $ref; ?>">
         	<tr <? echo $atrib_tr; ?> style="cursor:hand">
             <td align="center" bgcolor="<? echo $resul_sql->fields['color']; ?>"><? echo $resul_sql->fields['id_licitacion']; ?></td>
             <td align="center" ><b><? echo $resul_sql->fields['iniciales']; ?></b></td>
             <td align="center" ><? echo fecha($resul_sql->fields['fecha_apertura']); ?></td>
             <td align="center" ><? echo $resul_sql->fields['nombre']; ?></td>
             <td align="center" ><? echo $resul_sql->fields['nom_dis']; ?></td>
             <td align="center" ><b><? echo $resul_sql->fields['nro_lic_codificado']; ?></b></td>         	       	
       	    </tr>
       	    </a>
        	<?
       	$resul_sql->MoveNext();
        }	 	
 ?>
 </table>
 <table width="80%" align="center">
  <tr><td align="center"><input type="button" value="Cerra" onclick="window.close()"></td></tr>
 </table>

 
<?    
    echo "<table width='95%' border=0 align=center>\n";
	echo "<tr><td colspan=6 align=center><br>\n";
	echo "<table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>\n";
	echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia ID/Est:</b></td></tr>\n";
	echo "<tr>\n";
	$cont=0;
	foreach ($estados as $est => $arr) {
	if (!($cont % 3)) { echo "</tr><tr>"; }
		echo "<td width=33% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 wdith=100%><tr>";
		echo "<td width=15 bgcolor='".$estados[$est]["color"]."' bordercolor='#000000' height=15>&nbsp;</td>\n";
		echo "<td bordercolor='#FFFFFF'>".$estados[$est]["texto"]."</td>\n";
		echo "</tr></table></td>";
	   $cont++;
	}
	echo "</tr>\n";
	echo "</table>\n";
	echo "</td></tr>\n";
	echo "</table><br>\n";
 }
 else {
 	?>
 	 <table width="80%" align="center" class="bordes">
 	  <tr>
 	   <td align="center"><font color="Red" size="3"><b>La entidad&nbsp;</font> <font size="4"><? echo $entidad_nombre; ?></font> <font color="Red" size="3">&nbsp;no esta en ninguna Licitación.</b></font></td>
 	  </tr> 	   	  
 	 </table>
 	 <table width="80%" align="center">
 	  <tr><td align="center"><input type="button" value="Cerra" onclick="window.close()"></td></tr>
 	 </table>
 	 
 	<?
 }       
}     
?>

</form>
<?
 if ($id_entidad=="") {
?>
<script> 
 document.all.id_entidad.value=window.opener.document.all.ent.value;
 document.all.entidad_nombre.value=window.opener.document.all.ent_nombre.value;
 document.form1.submit();
</script>
<?
 }
?>