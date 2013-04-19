<?
/*
$Author: mari $
$Revision: 1.13 $
$Date: 2003/12/05 20:13:21 $
*/
require_once("../../config.php");

//DATOS DEL formulario
extract($_POST,EXTR_SKIP);

$campos="p.id_producto, p.desc_gral, s.comentario, s.cant_disp, ".
"d.id_deposito, d.nombre";
$query=
"SELECT $campos FROM 
productos p LEFT JOIN 
stock s on s.id_producto=p.id_producto LEFT JOIN 
depositos d on d.id_deposito=s.id_deposito";

/*******
VER SI SE NECESITA MOSTRAR TODOS LOS DEPOSITOS AUNQUE NO HAYA NADA
Y COMO SE VA A SELECCIONAR EL DEPOSITO EN AÑADIR, SI SE MUESTRAN TODOS LOS DEPOSITOS
ENTONCES NO SE PERMITIRA MODIFICAR EL DEPOSITO UNA VEZ QUE ENTRO A AÑADIR
A STOCK DE LO CONTRARIO SE TENDRA QUE PERMITIR MODIFICAR EL DEPOSITO

VER SI SE REQUIERE QUE EL FILTRO Y LA BUSQUEDA TRABAJEN EN CONJUNTO
*******/

if($_POST["Nuevo_Producto"]=="Nuevo Producto")
{$pagina="altas_prod.php";
 $link=encode_link($html_root."/index.php",array("menu"=>"altas_prod","extra"=>array ("pagina"=>"stock", "tipo"=> $_POST["filtro_select"])));
  
 echo "<html><head><script language=javascript>"; 
 echo "window.parent.location='$link';";
 echo "</script></head></html>";  
 
 //die;
}	

if ($modificar)
{
	$pagina="stock_add.php";
	$link=encode_link($pagina,array ("producto"=>$producto));
	header("location: $link");
	die;
}
//controlar el valor de los botones en otro lado
if ($_POST["boton"]=="Buscar") {
	$page = 0;
	$keyword = $_POST["keyword"];
}
else {
	if ("{$parametros[page]}" != "")
		$page = $parametros["page"];
	elseif ("$_ses_page" != "")
		$page = $_ses_page;
	else
		$page = 0;
}
if (!isset($keyword)) {
	if ("{$parametros[keyword]}" != "")
		$keyword = $parametros["keyword"];
	elseif ("$_ses_keyword" != "")
		$keyword = $_ses_keyword;
	else
		$keyword = "";
}

$filter = $_POST["filter"] or $filter = $parametros["filter"] or $filter = "";
//$sort = $_POST["sort"] or $sort = $parametros["sort"] or $sort = $_ses_sort or $sort = "default";
$sort = $_POST["sort"] or $sort = $parametros["sort"] or $sort = "default";
$up = $_POST["up"] or $up = $parametros["up"] or $up = "";
/*
phpss_svars_set("_ses_page", $page);
phpss_svars_set("_ses_keyword", $keyword);
phpss_svars_set("_ses_filter", $filter);
phpss_svars_set("_ses_sort", $sort);
phpss_svars_set("_ses_up", $up);
*/
$orden = array(
	"default" => "1",
	"1" => "p.desc_gral",
	"2" => "s.cant_disp",
	"3" => "d.nombre",
);
			
$filtro = array(
	"p.desc_gral" => "Nombre del producto",
	"s.cant_disp" => "Stock",
	"d.nombre" => "Deposito",
);
$query_tipos="SELECT * from tipos_prod";
$tipos_prod=$db->Execute($query_tipos) or die($db->ErrorMsg()."<br>".$query_tipos);          	
?>
<html>
<head>
<title>Administracion de Productos y Stock</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script>
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;src.style.cursor="hand";
}
function bajo(src,color_default) {
    src.style.backgroundColor=color_default;src.style.cursor="default";
}
function link_sobre(src) {
    src.id='me';
}
function link_bajo(src) {
    src.id='mi';
}

</script>
<style type="text/css">
<!--
a {
	cursor: hand;text-decoration:none;
}
-->
</style>
</head>
<body bgcolor="#E0E0E0">
<form name="form" method="post" action="stock.php" >
<CENTER>
    <table width="98%" border="0" cellspacing="0" cellpadding="0">
      <tr> 
        <td width="16%" height="30" align="right" nowrap> 
           <select name="filtro_select">
<?
		$last_opt="<option selected>Todos</option>";
		while (!$tipos_prod->EOF) 
		{
			echo "<option value='".$tipos_prod->fields["codigo"]."'";
			if ($tipos_prod->fields["codigo"] == $_POST['filtro_select']) 
			{	
				echo " selected";
				$last_opt="<option>Todos</option>";			
			}
			echo ">".$tipos_prod->fields["descripcion"]."\n";
			$tipos_prod->MoveNext();
		}
		echo $last_opt;
?>        </select></td>
        <td width="9%" align="center" nowrap> <input name="boton" type="submit" id="filtrar" value="Filtrar"></td>
        <td colspan="3" align="right">
          <?
if ($boton=="Filtrar" && $last_opt!="<option selected>Todos</option>")
{
	  $where="p.tipo='".$_POST['filtro_select']."'";
	  $keyword="";
	  $filter="";	  
}
elseif ($last_opt=="<option selected>Todos</option>" && $boton!="Buscar")
{
	  $keyword="";
	  $filter="";	  	
}
 
$itemspp=50;//items por pagina
list($query,$total,$links,$up) = form_busqueda($query,$orden,$filtro,$link,$where);
$stock=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);          	
?>
		</td>
        <td width="18%" nowrap> &nbsp;
           			<input name="boton" type="submit" id="buscar" value="Buscar"> 
        </td>
      </tr>
     <!-- <tr style="visibility: hidden;" id="precios"> 
        <td height="27">&nbsp;</td>
        <td>&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="22%" ><div align="center">valor minimo 
            <input name="minimo" type="text" size="10">
          </div></td>
        <td width="25%" align="center"><input name="maximo" type="text" size="10">
          valor maximo</td>
        <td>&nbsp;</td>
      </tr>-->
    </table>
    <hr>
   <table width="98%" border="0" cellpadding="0" cellspacing="0">
    <tr bgcolor="#c0c6c9">
         <td align="left"> 
          <font color="#006699" face="Georgia, Times New Roman, Times, serif">
           <b>Total de Productos </b></FONT>
           <? echo ": <font color='#006699'><b>$total</b></font>" ?>
           
         </td>
          <? echo "<td align='right'>
						<font color='#006699'>
 						   $links
						</font></td>" ?>
    </tr>
  </table>  
   <div style="position:relative; width:98%; height:68%; overflow:auto;"> 
      <table border="0" cellspacing="2" cellpadding="0" width="100%">
        <tr bgcolor="#006699" align="center"> 
          <td width="20">&nbsp;</td>
          <td width="312"><font color="#c0c6c9"><b><A href="<?=encode_link("stock.php",array("sort"=>"1"))?>">PRODUCTO</A></b></font></td>
          <td width="63"><font color="#c0c6c9"><b><A href="<?=encode_link("stock.php",array("sort"=>"2"))?>">STOCK</A></b></font></td>
          <td width="120"><font color="#c0c6c9"><b><A href="<?=encode_link("stock.php",array("sort"=>"3"))?>">DEPOSITO</A></b></font></td>
        </tr>
<?
  $nro=0;
  $cnr=1;
  while (!$stock->EOF )
  {if ($cnr==1)
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
  $atrib.=" onmouseover=\"this.style.backgroundColor = '#ffffff'\" onmouseout=\"this.style.backgroundColor = '$color'\"";
  $atrib.=" title='$comentario' style=cursor:hand";
?>
        <tr  <?php echo $atrib;?> onclick="document.all.modificar.disabled=0;
<?
if ($stock->RecordCount()>1)
	echo "producto[".$nro++."].checked=true;";
else 
	echo "producto.checked=true;";

?>
"> 
          <td align="center"> <input type="radio" name="producto" value="<? echo $stock->fields['id_producto']."_".$stock->fields['id_deposito']?>"></td>
          <td align="left"><font color="#006699"><b><? echo $stock->fields['desc_gral'] ?></b></font></td>
          <td align="right"><font color="#006699"><b><? echo $stock->fields['cant_disp'] ?></b></font></td>
          <td align="center"><font color="#006699"><b><? echo $stock->fields['nombre'] ?></b></font></td>
          </tr>
        <? 	
 		$stock->MoveNext();
  }
  
?>
      </table>
</div>
<hr>
</CENTER>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr> 
        <td align="right">&nbsp; </td>
        <td width="50%" align="center"> 
          <input name="modificar" type="submit" disabled id="modificar" title="Modifica los valores del articulo seleccionado" value="Añadir a Stock"> 
        </td>
        <td align="center" valign="center">
         <input type="submit" name="Nuevo_Producto" value="Nuevo Producto">
        </td>
      </tr>
    </table>

<?PHP echo $datos ?>  
<SCRIPT >
</SCRIPT>
</form>
</body>
</html>