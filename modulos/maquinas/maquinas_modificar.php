<?
/*
$Author: cestila $
$Revision: 1.2 $
$Date: 2004/02/05 21:48:31 $
*/
require_once("../../config.php");
echo $html_header;
$id=$parametros["id"] or $id=$_POST["id"];
//Valores del formulario de busqueda
$up=$parametros["up"] or $up=$_POST["up"];
$sort=$parametros["sort"] or $sort=$_POST["sort"];
$page=$parametros["page"] or $page=$_POST["page"];
$keyword=$parametros["keyword"] or $keyword=$_POST["keyword"];
$filter=$parametros["filter"] or $filter=$_POST["filter"];

//valores esenciales
$modelo=$_POST["modelo"];
$desde=$_POST["desde"] or $desde=$parametros["desde"];
$hasta=$_POST["hasta"] or $hasta=$parametros["hasta"];

if ($_POST["cmd1"]=="Modificar") {
    if (!FechaOk($fecha))
         $error.="Debe especificar la fecha.<br>";
    else
        $fecha="'".ConvFecha($fecha)."'";
    if (!$garantia)
         $error.="Debe especificar la garantia.<br>";
    $sql="UPDATE clientes_drivers SET nserie='$nserie',fecha=$fecha,garantia='$garantia' where id=$id";
    if (!$error)
         $db->execute($sql) or error($db->errormsg()." - ".$sql);
    else
        error($error);
}
if ($desde and $hasta) {
    $sql="select idcliente from clientes_drivers where nserie='$desde'";
    $nserie=$desde;
    $idcliente=$rs->fields["idcliente"];
}
else {
    $sql="select nserie,idcliente from clientes_drivers where id=$id";
    $rs=$db->Execute($sql) or die ($db->ErrorMsg()." - ".$sql);
    $nserie=$rs->fields["nserie"];
    $idcliente=$rs->fields["idcliente"];
}
if ($parametros["cmd1"]=="eliminar") {
    // Valores del formulario
	//print_r($_POST);
	while (list($key,$cont)=each($parametros)){
		$$key=$cont;
	}$db->begintrans();
    if ($desde and $hasta){
       $s1=ord(substr($desde,strlen($desde)- 4,1)) - 64 . substr($desde,strlen($desde)- 3,3);
       $s2=ord(substr($hasta,strlen($desde)- 4,1)) - 64 . substr($hasta,strlen($desde)- 3,3);
       $prim=substr($desde,0,strlen($desde)- 4);
       while ($s1 <= $s2) {
             //echo $s1 . " - ". $s2 . " - " . $prim;
             $serie=$prim . chr(substr($s1,0,1) + 64) . substr($s1,1,3);
             $sql="DELETE FROM driver WHERE idarchivo=$idarchivo AND nserie='$serie'";
             $db->Execute($sql) or $error.=$db->ErrorMsg();
             $s1++;
       }
    }
    else {
          $sql="DELETE FROM driver WHERE id=$iddriver";
          $db->Execute($sql) or $error.=$db->ErrorMsg();
    }
    if ($error) {
        error($error);
        $db->RollBackTrans();
    }
    else
        $db->committrans();
}
if ($_POST["cmd1"]=="Eliminar") {
    // Valores del formulario
	//print_r($_POST);
	while (list($key,$cont)=each($_POST)){
		$$key=$cont;
	}
	$db->BeginTrans();
    $sql="DELETE FROM driver WHERE nserie='$nserie'";
    $db->execute($sql) or $error.= $db->errormsg();
    $sql="DELETE FROM clientes_drivers WHERE id=$id";
    $db->execute($sql) or $error.= $db->errormsg();
    if (!$error) {
         //echo $sql;
         $db->CommitTrans();
         echo "<script>window.location='".encode_link("maquinas_admin.php",array())."';</script>\n";
    }
    else {
          $db->RollBackTrans();
          error($error);
    }
}
if ($_POST["cmd1"]=="Nuevo") {
    // Valores del formulario
	//print_r($_POST);
	while (list($key,$cont)=each($_POST)){
		$$key=$cont;
	}
	if ($nuevodriver) {
        $db->begintrans();
        if ($desde and $hasta){
            $s1=ord(substr($desde,strlen($desde)- 4,1)) - 64 . substr($desde,strlen($desde)- 3,3);
            $s2=ord(substr($hasta,strlen($desde)- 4,1)) - 64 . substr($hasta,strlen($desde)- 3,3);
            $prim=substr($desde,0,strlen($desde)- 4);
            while ($s1 <= $s2) {
                   //echo $s1 . " - ". $s2 . " - " . $prim. "<br>";
                   $serie=$prim . chr(substr($s1,0,1) + 64) . substr($s1,1,3);
                   $sql="INSERT INTO driver (nserie,idarchivo) VALUES ('$serie',$nuevodriver)";
                   $db->Execute($sql) or $error.=$db->ErrorMsg();
                   $s1++;
            }
		}
        else {
            $sql="INSERT INTO driver (nserie,idarchivo) VALUES ('$nserie',$nuevodriver)";
            $db->Execute($sql) or $error.=$db->ErrorMsg();
        }
    }
    if ($error) {
        error($error);
        $db->RollBackTrans();
    }
    else
        $db->committrans();
}
function llenarModelo(){
         global $modelo,$db;
         echo "<option value='todo' selected>Todos los modelos</option>\n";
         $sql="select distinct modelo from drivers";
         $rs=$db->execute($sql) or die($db->errormsg()." - ".$sql);
         while ($fila=$rs->fetchrow()) {
                echo "<option value='".$fila["modelo"]."' ";
                if ($modelo==$fila["modelo"]) echo "selected";
                echo ">".$fila["modelo"]."</option>\n";
         }
}
function llenardrivers(){
         global $modelo,$db;
         $sql="select id,archivo from drivers";
         if ($modelo && $modelo!="todo") $sql.=" where modelo='$modelo'";
         $rs=$db->Execute($sql) or die($db->errormsg()." - ".$sql);
         while (!$rs->EOF) {
                echo "<option value='".$rs->fields["id"]."'>".$rs->fields["archivo"]."</option>\n";
                $rs->MoveNext();
         }
}
if (!$desde and !$hasta) {
$sql="select nserie,fecha,garantia from clientes_drivers where id=$id";
$rs = $db->execute($sql) or die($db->errormsg()." - ". $sql);
?>
<br>
<script language='javascript' src='../../lib/popcalendar.js'></script>
<form method="POST" action="maquinas_modificar.php" id="frm" name="frm">
<input type=hidden name=sort value='<? echo $sort; ?>'>
<input type=hidden name=id value='<? echo $id; ?>'>
 <table align=center border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-left-width: 0; border-right-width: 0" bordercolor="#111111" width="99%" id="AutoNumber1">
    <tr>
      <td width="100%" style="background-repeat:no-repeat" bgcolor="#F3F3F3" style="background-repeat:no-repeat;" background="imagenes/driver.gif">
      <p style="margin: 6"><b>
      <font face="Trebuchet MS" size="4" color="#008000">Modificar datos de la maquina. </font>
      </p>
      </td>
    </tr>
    <tr>
     <td>
      <table cellpadding="2" cellspacing="2" align=center>
       <tr>
        <td>
         <b>Nro de serie:</b>
        </td>
        <td>
         <input type=text name=nserie value='<? echo $rs->fields["nserie"]; ?>'>
        </td>
        <td>
         <b>Fecha de compra:</b>
        </td>
        <td>
         <input type=text name=fecha value='<? echo ConvFecha($rs->fields["fecha"]); ?>'>
<?
echo link_calendario("fecha");
?>
        </td>
       </tr>
       <tr>
        <td colspan=2 align=right>
         <b>Garantia:</b>
        </td>
        <td colspan=2 align=left>
         <input type=text name=garantia value='<? echo $rs->fields["garantia"]; ?>'>
        </td>
       </tr>
       <tr>
        <td align=right colspan=4>
         <input type=submit name=cmd1 value=Eliminar>
         <input type=submit name=cmd1 value=Modificar>
        </td>
       </tr>
      </table>
     </td>
    </tr>
 </table>
</form>
<? } ?>
<form method="POST" action="maquinas_modificar.php" id="frm" name="frm">
<input type=hidden name=sort value='<? echo $sort; ?>'>
<input type=hidden name=resta value='<? echo $resta; ?>'>
<input type=hidden name=id value='<? echo $id; ?>'>
<input type=hidden name=desde value='<? echo $desde; ?>'>
<input type=hidden name=hasta value='<? echo $hasta; ?>'>
 <table align=center border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-left-width: 0; border-right-width: 0" bordercolor="#111111" width="99%" id="AutoNumber1">
    <tr>
      <td width="100%" style="background-repeat:no-repeat" bgcolor="#F3F3F3" style="background-repeat:no-repeat;" background="imagenes/driver.gif">
      <p style="margin: 6"><b>
      <font face="Trebuchet MS" size="4" color="#008000">Administracion de Drivers para la máquina:
      <?
      if ($desde and $hasta)
          echo $desde . " - " . $hasta;
      else
          echo $nserie;
      ?> </font>
      </p>
      </td>
    </tr>
    <tr>
     <td>
<?
echo "<table width=99% border=0 cellspacing=5 cellpadding=5>\n";
echo "<tr><td colspan=6 align=center>\n";

if (!$sort) $sort=1;

$orden = array(
"1" => "driver.nserie",
"2" => "archivo",
"3" => "tipo",
"4" => "descripcion",
"5" => "size"
);

$filtro = array(
"nserie"         => "Número de serie",
"archivo"       => "Archivo",
"tipo"      => "Tipo de archivo",
"descripcion"       => "Descripción",
"size" => "Tamaño"
);

if ($up == "0") {
    $direction="DESC";
    $up2 = "1";
}
else {
     $up = "1";
     $direction = "ASC";
     $up2 = "0";
}

$tmp=es_numero($keyword);
echo "<b>Buscar:&nbsp;</b><input type='text' name='keyword' value='$keyword' size=20 maxlength=20>\n";
echo "<b>&nbsp;en:&nbsp;<b><select name='filter'>&nbsp;\n";
echo "<option value='all'";
if (!$filter) echo " selected";
echo ">Todos los campos\n";
while (list($key, $val) = each($filtro)) {
       echo "<option value='$key'";
       if ($filter == "$key") echo " selected";
       echo ">$val\n";
}
echo "</select>\n";

if ($keyword) {
    $where = " and ";
    if ($filter == "all" or !$filter) {
        $where_arr = array();
        $where .= "(";
        reset($filtro);
        while (list($key, $val) = each($filtro)) {
               $where_arr[] = "$key like '%$keyword%'";
        }
        $where .= implode(" or ", $where_arr);
        $where .= ")";
    }
    else {
          $where .= "$filter like '%$keyword%'";
    }
}
$sql = "SELECT driver.id,driver.nserie,driver.idarchivo,drivers.archivo,drivers.tipo,drivers.descripcion,drivers.size FROM driver inner join drivers on driver.idarchivo=drivers.id";
$sql .= " WHERE (driver.nserie='$nserie') $where ";
$sql .= " ORDER BY ".$orden[$sort]." $direction LIMIT $itemspp OFFSET ".($page * $itemspp);
$sqlcount="SELECT COUNT(*) AS total FROM driver inner join drivers on driver.idarchivo=drivers.id WHERE (driver.nserie='$nserie') $where";
$rs=$db->Execute($sqlcount) or die($db->errormsg()." - ".$sql);
//echo $sql;
$total=$rs->fields["total"];
$page_n = $page + 1;
$page_p = $page - 1;
$link_pagina_p = "";
$link_pagina_n = "";
$link_form = Array(
"sort" => $sort,
"up" => $up,
"filter" => $filter,
"keyword" => $keyword
);

if ($page > 0) {
	$link_form["page"]=$page_p;
	$link_pagina_p = "<a style='color: white;' href='".encode_link("maquinas_modificar.php",$link_form)."'><<</a>";
}
$sum=0;
if (($total % $itemspp)>0) $sum=1;
$link_pagina_num = "&nbsp;&nbsp;Página&nbsp;".($page+1)."&nbsp;de&nbsp;". (intval($total/$itemspp)+$sum) . "&nbsp;&nbsp;";
if ($total > $page_n*$itemspp) {
    $link_form["page"]=$page_n;
	$link_pagina_n = "<a style='color: white;' href='".encode_link("maquinas_modificar.php",$link_form)."'>>></a>";
}
if ($total > 0 and $total > $itemspp) {
    $link_pagina = $link_pagina_p.$link_pagina_num.$link_pagina_n;
}
else {
    $link_pagina = "&nbsp;";
}
//echo "sql: $sql total: $totalProv link: $link_pagina<Br>"; exit;
echo "<input type=submit name=envia value='Buscar'>&nbsp;&nbsp;";
//echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='index.php?modulo=caso';\">\n";
echo "</td></tr></table><br>\n";
$rs = $db->Execute($sql)  or die($db->errormsg()." - ".$sql);
echo "</form>\n";
echo "<table border=1 width=99% cellspacing=0 cellpadding=1 bordercolor='#ffffff' align=left>";
echo "<tr><td style='border-right: 0;' colspan=2 align=left id=ma>\n";
echo "<b>Total:</b> ".$total." Drivers.</td>\n";
echo "<td style='border-left: 0;' colspan=4 align=right id=ma>$link_pagina</td></tr>\n";
$link_form["page"]=$page;
$link_form["up"]=$up2;
$link_form["sort"]=1;
echo "<tr><td align=right id=mo><a id=mo href='".encode_link("maquinas_modificar.php",$link_form)."'>N° de Serie</a></td>\n";
$link_form["sort"]=2;
echo "<td align=right id=mo><a id=mo href='".encode_link("maquinas_modificar.php",$link_form)."'>Archivo</td>\n";
$link_form["sort"]=3;
echo "<td align=right id=mo><a id=mo href='".encode_link("maquinas_modificar.php",$link_form)."'>Tipo</td>\n";
$link_form["sort"]=4;
echo "<td align=right id=mo><a id=mo href='".encode_link("maquinas_modificar.php",$link_form)."'>Descripción</td>\n";
$link_form["sort"]=5;
echo "<td align=right id=mo><a id=mo href='".encode_link("maquinas_modificar.php",$link_form)."'>Tamaño</td>\n";
echo "<td align=right id=mo>X</td>\n";
echo "</tr>\n";
while (!$rs->EOF) {
    //$ref = "index.php?modo=admin&modulo=clientes&cmd=modificar&id=".$rs->fields["id"];
    //tr_tag($ref,"title='Haga click aqui para ver o modificar los datos del proveedor'");
    $size=number_format(($rs->fields["size"] / 1024));
    echo "<tr bgcolor='#f0f0f0'>\n";
    echo "<td align=center style='font-size: 9pt;'>".$rs->fields["nserie"]."</td>\n";
    echo "<td align=center style='font-size: 9pt;'>".$rs->fields["archivo"]."</td>\n";
    echo "<td align=center style='font-size: 9pt;'>&nbsp;".$rs->fields["tipo"]."</td>\n";
    echo "<td align=center style='font-size: 9pt;'>&nbsp;".$rs->fields["descripcion"]."</td>\n";
    echo "<td align=center style='font-size: 9pt;'>&nbsp;$size Kb</td>\n";
    echo "<td align=center style='font-size: 9pt;'>&nbsp;<a href='".encode_link("maquinas_modificar.php",Array("cmd1"=>"eliminar","iddriver"=>$rs->fields["id"],"idarchivo"=>$rs->fields["idarchivo"],"id"=>$id,"desde"=>$desde,"hasta"=>$hasta))."'><img border=0 src='../../imagenes/error.gif' width=16></a></td>\n";
    echo "</tr>\n";
    $rs->MoveNext();
}
echo "<tr><td colspan=6 align=right>\n";
?>
<form method="POST" action="maquinas_modificar.php" id="formu" name="formu">
<input type=hidden name=sort value='<? echo $sort; ?>'>
<input type=hidden name=resta value='<? echo $resta; ?>'>
<input type=hidden name=id value='<? echo $id; ?>'>
<input type=hidden name=desde value='<? echo $desde; ?>'>
<input type=hidden name=hasta value='<? echo $hasta; ?>'>

<?
//echo "<input type=button OnClick='window.location=\"index.php?modulo=maquinas&modo=admin\"' value='<< Volver'>\n";
echo "<select name=modelo OnChange='document.all.formu.submit();'>\n";
llenarModelo();
echo "</select>\n";
echo "<select name=nuevodriver>\n";
llenardrivers();
echo "</select>\n";
echo "<input type=submit name=cmd1 value='Nuevo'>&nbsp;&nbsp;";
echo "</form>\n";
echo "</td></tr>\n";
echo "</table>\n";
//echo "</form>\n";
$ocurrioerror=0;
?>
     </td>
    </tr>
  </table></form>