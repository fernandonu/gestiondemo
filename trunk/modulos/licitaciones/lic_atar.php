<?
/*
$Author: mari $
$Revision: 1.10 $
$Date: 2007/01/08 15:25:46 $
*/
require_once("../../config.php");
echo $html_header;
$id=$parametros["id"] or $id=$_POST["id"] or $id=$link_temp["id"];
$id_licitacion=$parametros["id_licitacion"] or $id_licitacion=$parametros["lic"] or $id_licitacion=$_POST["id_licitacion"] or $id_licitacion=$link_temp["id_licitacion"];
$nro_factura=$parametros["nro_factura"] or $nro_factura=$parametros["nro"] or $nro_factura=$_POST["nro_factura"] or $nro_factura=$link_temp["nro_factura"];

$cmd1=$_POST["cmd1"];
variables_form_busqueda("atar_facturas");
if (!$cmd1) {
	$cmd1="select";
}
if ($cmd1=="Guardar") {

	$atadas=$_POST["atadas"];
	if (!$atadas) {
		error("Seleccione las cobranzas que quiere atar");
	}
	else {
		$db->BeginTrans();
		// Reservar los comentarios de cada factura
		$ok=1;
		$sql="select id_comentario from gestiones_comentarios where id_gestion=$id";
		$res=$db->execute($sql);
		while (($fila=$res->fetchrow()) and $ok) {
			$sql="Insert Into atadas_comentarios (id_cobranza,id_comentario) Values ($id,".$fila["id_comentario"].")";
			$ok=$db->execute($sql);
		}
		reset ($atadas);
		while ((list($key,$atar)=each($atadas)) and $ok) {
			$sql="select id_comentario from gestiones_comentarios where id_gestion=$atar";
			$res=$db->execute($sql);
			while (($fila=$res->fetchrow()) and $ok) {
				$sql="Insert Into atadas_comentarios (id_cobranza,id_comentario) Values ($atar,".$fila["id_comentario"].")";
				$ok=$db->execute($sql);
			}
		}
		reset ($atadas);
		while ((list($key,$atar)=each($atadas)) and $ok) {
			$sql="update gestiones_comentarios SET "
				."id_gestion=$id where id_gestion=$atar";
			$ok=$db->execute($sql);
			if (!$ok) {
				break;
			}
		        $sql="INSERT INTO atadas (id_primario,id_secundario) "
			    ."Values ($id,$atar)";
			$ok=$db->execute($sql);

                       //las facturas atadas las pongo en activo=0
                       $sql="UPDATE cobranzas set activo = 0 where id_cobranza=$atar";
                       $ok1=$db->execute($sql);
		}
		if ($ok && $ok1) {
			$db->commitTrans();
		}
		else {
			$db->RollbackTrans();
			echo $sql;
			error("Se produjo un error");
		}
?>
<script>
//opener.parent.window.location='<?//=encode_link('../../index.php',array('menu'=>'lic_cobranzas','extra'=>array('cmd1'=>"detalle_cobranza","id"=>$id)));?>';
window.opener.location.href='<?=encode_link('lic_cobranzas.php',array('cmd1'=>"detalle_cobranza","id"=>$id));?>';
window.close();
</script>
<?
	}
}
if ($cmd1=="select"){
?>
<br>
<form action='lic_atar.php' method=post>
<input type=hidden name=id value=<?=$id?>>
<input type=hidden name=id_licitacion value=<?=$id_licitacion?>>
<input type=hidden name=nro_factura value=<?=$nro_factura?>>
<!--
<table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=<?=$bgcolor2;?> align=center>
<tr>
	<td align=center id=mo>
		<h4><b>Licitacion Nro: <?=$id_licitacion;?></h4>
		<h4>Seleccione las facturas que quiera atar con la factura N°: <?=$nro_factura;?></h4>
	</td>
</tr>
</table>
-->
<?
$orden = array(
	"default" => "1",
	"default_up" => "1",
    "1" => "nro_factura",
    "2" => "id_licitacion",
	"3" => "nro_carpeta",
	"4" => "entidad.nombre",
	"5" => "cobranzas.nombre",
);
$filtro = array(
	"nro_carpeta" => "Carpeta",
	"id_licitacion" => "ID Licitación",
	"entidad.nombre" => "Cliente",
	"nro_factura" => "Número de factura",
);

$link_temp = array("id" => $id,"id_licitacion"=>$id_licitacion,"nro_factura"=>$nro_factura);

$sql_temp="select id_cobranza,cobranzas.nombre as nombre_cobranzas,nro_carpeta,nro_factura,
                  id_licitacion,entidad.nombre as entidad,id_primario
                  from cobranzas
                  left join entidad using (id_entidad)
                  left join atadas on (id_secundario=0)
                  ";

$where_temp=" id_cobranza<>$id and estado<>'FINALIZADA' and activo=1";

$itemspp=10000;
?>
<table border=1 width=99% cellspacing=0 cellpadding=1 bordercolor='#ffffff' align=center>

   <tr><td id=mo colspan=6>
        <font size=3><b>Seleccione las facturas a atar</b></font>
        </td></tr>
     <tr>
      <td colspan=6 align=left>
      <font size=2><b>Licitacion Nro: <font color=red><?=$id_licitacion?></font></b></font>
      </td>
      </tr>
     <tr>
      <td colspan=6 align=left>
      <font size=2><b>Factura Nro: <font color=red><?=$nro_factura?></font></b></font>
      </td>
      </tr>

   <tr>
      <td colspan=6 align=center>
      <?
      list($sql,$total_reg,$link_pagina,$up) = form_busqueda($sql_temp,$orden,$filtro,$link_temp,$where_temp,"buscar");
      $result = sql($sql) or fin_pagina();
      ?>
      <input  type=submit name=form_busqueda value='Buscar'>

      </td>
   </tr>
   <tr>
    <td style='border-right: 0;' align=left colspan=3 id=ma> <b>Total:</b> <?=$total_reg?> Facturas</td>
    <td style='border-left: 0;' colspan=4 align=right id=ma>&nbsp;<?=$link_pagina?>&nbsp;</td></tr>
  <tr>
  <td>&nbsp</td>

   <td align=right id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up,"id"=>$id,"id_licitacion"=>$id_licitacion,"nro_factura"=>$nro_factura))?>'> Nro Factura </a></td>
   <td align=right id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up,"id"=>$id,"id_licitacion"=>$id_licitacion,"nro_factura"=>$nro_factura))?>'> Licitación </a></td>
   <td align=right id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up,"id"=>$id,"id_licitacion"=>$id_licitacion,"nro_factura"=>$nro_factura))?>'> Nro Carpeta </a></td>
   <td align=right id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up,"id"=>$id,"id_licitacion"=>$id_licitacion,"nro_factura"=>$nro_factura))?>'> Cliente </a></td>
   <td align=right id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up,"id"=>$id,"id_licitacion"=>$id_licitacion,"nro_factura"=>$nro_factura))?>'> Nombre </a></td>
  </tr>
<?
/*
<tr>
	<td align=center>
		<select style="font-family: courier;" name="atadas[]" multiple size=10>


while (!$result->EOF) {
	echo "<option value='".$result->fields["id_cobranza"]."'>Lic.: ".str_pad($result->fields["id_licitacion"],5," ",STR_PAD_LEFT)
		." | Factura: ".str_pad($result->fields["nro_factura"],5," ",STR_PAD_LEFT)
		." | Entidad: ".$result->fields["entidad"]
//		." Nombre: ".$result->fields["nombre"]
		."</option>\n";
	$result->MoveNext();
}
		</select>
	</td>
</tr>

*/
?>

<?
for ($i=0;$i<$result->recordcount();$i++){
   	$ref = encode_link("copiar_comentario.php",Array("id_cobranza"=>$id_cobranza,"copiar"=>$rs1->fields["id_cobranza"],"modo"=>"Copiar"));
?>
	<tr <?=atrib_tr()?>>
     <td width=2%><input type=checkbox name='atadas[]' value='<?=$result->fields['id_cobranza']?>'></td>
     <td align=left style='font-size: 9pt;'>&nbsp;<?=$result->fields["nro_factura"]?></td>
     <td align=left width=80 style='font-size: 9pt;'>&nbsp;<?=$result->fields["id_licitacion"]?></td>
     <td align=left style='font-size: 9pt;'>&nbsp;<?=$result->fields["nro_carpeta"]?></td>
	 <td align=left style='font-size: 9pt;'>&nbsp;<?=$result->fields["entidad"]?></td>
	 <td align=left style='font-size: 9pt;'>&nbsp;<?=$result->fields["nombre_cobranzas"]?></td>
	</tr>


<?
$result->movenext();
}
?>
</table>
<table width=100% align=center>
<tr>
	<td style="border:<?=$bgcolor2?>;" align=center>
		<input type=submit name='cmd1' style='width:160;' value='Guardar'>
		<input type=button name=volver style='width:160;' value='Volver' onClick="window.close();">
	</td>
</tr>
</table>
</form>
<?
}
echo fin_pagina();
?>