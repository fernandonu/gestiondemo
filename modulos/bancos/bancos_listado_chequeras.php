<?
/*
AUTOR: MAC

Esta página maneja el ABM de las chequeras de la empresa

MODIFICADO POR:
$Author: nazabal $
$Revision: 1.1 $
$Date: 2004/12/17 20:04:07 $
*/

require_once("../../config.php");

if($_POST['boton']=="Cerrar Chequeras")
{$chequeras=$_POST['cerrar'];
 /*$link=encode_link('bancos_result_cerrarch.php',array('chequeras'=>$chequeras));
 echo "<script>";
 echo "var ventana;";
 echo "ventana=window.open('$link','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=200,top=40,width=350,height=300');"; 
 echo "ventana.focus();";
 echo "</script>"; */
 include("bancos_result_cerrarch.php");
}//de if($_POST['boton']=="Cerrar Chequeras") 
else 
{

echo $html_header;


variables_form_busqueda("chequeras");
	
if ($cmd == "") {
	$cmd="en uso";
    phpss_svars_set("_ses_chequeras_cmd", $cmd);
}



$datos_barra = array(
					array(
						"descripcion"	=> "Chequeras en Uso",
						"cmd"			=> "en uso",
						),
					array(
						"descripcion"	=> "Chequeras Cerradas",
						"cmd"			=> "cerradas"
						),
				        array(
						"descripcion"	=> "Todas",
						"cmd"			=> "todas"
						)
				 );
echo "<br>";
generar_barra_nav($datos_barra);

if($cmd!="todas")
{ $orden = array(
		"default" => "1",
 //		"default_up" => "1",
		"1" => "chequera.id_chequera",
		"2" => "chequera.nombrebanco"
	);
}
else 
{ $orden = array(
		"default" => "1",
 //		"default_up" => "1",
		"1" => "chequera.id_chequera",
		"2" => "tipo_banco.nombrebanco",
		"3" => "chequera.cerrada",
		"4" => "chequera.nro_chequera"
	);
}

$filtro = array(
		"chequera.id_chequera" => "ID Chequera",
		"cheques.númeroch" => "Nº de Cheque",
		"tipo_banco.nombrebanco" => "Banco",
		"chequera.nro_chequera" => "Nº Chequera"
		
	);

$query="select distinct chequera.*,tipo_banco.nombrebanco";

if($_POST['filter']=="cheques.númeroch")
 $query.=",ch.númeroch";
$query.=" from chequera join tipo_banco using(idbanco)";

if($_POST['filter']=="cheques.númeroch")
 $query.=" join (select númeroch,idbanco from cheques join tipo_banco using(idbanco) where númeroch=".intval($_POST['keyword'])." ) as ch using(idbanco)";	
 
 
$where="";
if($cmd=="en uso")
 $where=" cerrada=0";
elseif($cmd=="cerradas") 
 $where=" cerrada=1";

if($_POST['filter']=="cheques.númeroch") 
{if($cmd!="todas")
  $where.=" and ";
 $where.="(primer_cheque <= ".intval($_POST['keyword'])." and ".intval($_POST['keyword'])."<= ultimo_cheque)";
}
?>
<? 
/*
select bancos.chequera.*,bancos.tipo_banco.nombrebanco,ch.númeroch  
 
from bancos.chequera join bancos.tipo_banco using(idbanco) 
join  (select númeroch,idbanco from bancos.cheques join bancos.tipo_banco  using (idbanco) where     númeroch=1)
              as ch
        using (idbanco) 
where primer_cheque <= 1 and 1 <= ultimo_cheque

*/
?>

<script>
var contador=0;
//esta funcion sirve para habilitar el boton de cerrar 
function habilitar_cerrar(valor)
{
 if (valor.checked)
             contador++;
             else
             contador--;
 if (contador>=1)
        window.document.all.boton.disabled=0;
        else
         window.document.all.boton.disabled=1;
}//fin function
</script>
<form name="form" method="post" action="bancos_listado_chequeras.php">
<br>
<center>

<?
$itemspp = 50;

list($sql,$total_ch,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar");

$result = $db->Execute($sql) or die($db->ErrorMsg().$sql);
?>

&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>
</center>
<br>
<?=$parametros['msg']?>
<table class="bordessininferior" width="95%" align="center" cellpadding="3" cellspacing='0' bgcolor=<?=$bgcolor3?>>
 <tr id=ma>
    <td align="left">
     <b>Total:</b> <?=$total_ch?> chequeras.
    </td>
	<td align=right>
	 <?=$link_pagina?>
	</td>
  </tr>
</table>

<div style='position:relative; width:100%; height:70%; overflow:auto;'>

<table width='95%' class="bordessinsuperior" cellspacing='2' align="center">
<tr id=mo>
 <?
  if($cmd!="cerradas")
{?>
  <td></td>
 <?
}
 ?>
 <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>ID-Ch</a></b></td>
  <td width='12%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Nº Chequera</a></b></td>
 <td width='32%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Banco</a></b></td>
 <td width='17%'><b>Primer Cheque</b></td>
 <td width='19%'><b>Último Cheque</b></td>
<?
if($cmd=="todas") 
{?>
 <td width="7%"><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Estado</a></b></td>
<?
}?>
</tr>
<?
$cnr=1;
while(!$result->EOF)
{
  if ($cnr==1)
  {$color2=$bgcolor2;
   $color=$bgcolor1;
   $atrib ="bgcolor='$bgcolor1'";
   $cnr=0;
  }
  else
  {$color2=$bgcolor1;
   $color=$bgcolor2;
   $atrib ="bgcolor='$bgcolor2'";
   $cnr=1;
  }
//  $atrib.="onmouseover=\"this.style.backgroundColor = '$bgcolor_sobre'; this.style.color = '$text_color2'\" onmouseout=\"this.style.backgroundColor = '$color'; this.style.color = '$text_color1'\"";

$link = encode_link("bancos_chequeras.php",array("pagina"=>"listado","id_chequera"=>$result->fields["id_chequera"]));
?>
<tr <?echo atrib_tr()?>  style="cursor:hand">
 <?
 if($result->fields['cerrada']==0)
 {?>
  <td width="3%"><input type="checkbox" name="cerrar[]" value="<?=$result->fields['id_chequera']?>" onclick="habilitar_cerrar(this)"> </td>
 <?
 }
 elseif ($cmd =='todas') {?>
 <td>&nbsp; </td>
 <? }
 ?>
 <a href='<?=$link?>'>
 <td align="center">
  <?=$result->fields['id_chequera']?>
 </td>
 <td align="center">
  <?=$result->fields['nro_chequera']?>
 </td>
 <td>
  <?=$result->fields['nombrebanco']?> 
 </td>
 <td>
  <?=$result->fields['primer_cheque']?> 
 </td>
 <td>
  <?=$result->fields['ultimo_cheque']?> 
 </td> 
<?
if($cmd=="todas") 
{?>

 <td>
  <?if($result->fields['cerrada']==1)
     echo "Cerrada";
    else 
     echo "En Uso";  
  ?> 
 </td>
<?
}
?> 
</a></tr>
<?
$result->MoveNext();
}
?>
</table>
<?
if($cmd!="cerradas")
{?>
<center><input type="submit" name="boton" value="Cerrar Chequeras" disabled></center>
<?
}

?>

</form>
</div>
</body>
</html>
<?
}//del else de if($_POST['boton']=="Cerrar Chequeras")
?>