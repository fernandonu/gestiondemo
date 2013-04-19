<?
/*
$Author: nazabal $
$Revision: 1.1 $
$Date: 2004/12/17 20:04:07 $
*/

// Funciones exclusivas
function NuevProv ($id=0) {
    global $bgcolor3,$db;
    echo "<br><table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor3'>\n";
    // Titulo del Formulario
    if ($id!=0) echo "<tr bordercolor='#000000'><td id=mo align=center>Datos del Proveedor</td></tr>";
    else echo "<tr bordercolor='#000000'><td id=mo align=center>Nuevo Proveedor</td></tr>";
    echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0>";
    if ($id!=0) {
        $sql="SELECT * FROM bancos.proveedores WHERE IdProv=$id";
        $result = $db->query($sql) or die($db->ErrorMsg());
        $fila=$result->fetchrow();
        echo "<tr><td align=right><b>Id Proveedor</b></td>";
        echo "<td align=left><input type=hidden name=Nuevo_Proveedor_Id value='".$fila["idprov"]."'>".$fila["idprov"]."</td</tr>";
    }
    echo "<tr><td align=right><b>Nombre</b></td>";
    echo "<td align=left>";
    echo "<input type=text name=Nuevo_Proveedor_Nombre value='".$fila["proveedor"]."' size=30 maxlength=100>\n";
    echo "</td></tr>\n";
    echo "<tr><td align=right><b>Número C.U.I.T.</b>\n";
    echo "</td><td>";
    echo "<input type=text name=Nuevo_Proveedor_CUIT value='".$fila[cuit]."' size=30 maxlength=20>";
    echo "</td></tr>\n";
    echo "<tr><td align=right><b>Domicilio</b>\n";
    echo "</td><td>";
    echo "<input type=text name=Nuevo_Proveedor_Domicilio value='$fila[domicilio]' size=30 maxlength=100>";
    echo "</td></tr>\n";
    echo "<tr><td align=right><b>Código Postal</b>\n";
    echo "</td><td>";
    echo "<input type=text name=Nuevo_Proveedor_CP value='$fila[cp]' size=30 maxlength=6>";
    echo "</td></tr>\n";
    echo "<tr><td align=right><b>Localidad</b>\n";
    echo "</td><td>";
    echo "<input type=text name=Nuevo_Proveedor_Localidad value='$fila[localidad]' size=30 maxlength=50>";
    echo "</td></tr>\n";
    echo "<tr><td align=right><b>Provincia</b>\n";
    echo "</td><td>";
    echo "<input type=text name=Nuevo_Proveedor_Provincia value='$fila[provincia]' size=30 maxlength=50>";
    echo "</td></tr>\n";
    echo "<tr><td align=right><b>Contacto</b>\n";
    echo "</td><td>";
    echo "<input type=text name=Nuevo_Proveedor_Contacto value='$fila[contacto]' size=30 maxlength=100>";
    echo "</td></tr>\n";
    echo "<tr><td align=right><b>E-Mail</b>\n";
    echo "</td><td>";
    echo "<input type=text name=Nuevo_Proveedor_Mail value='$fila[mail]' size=30 maxlength=50>";
    echo "</td></tr>\n";
    echo "<tr><td align=right><b>Teléfono</b>\n";
    echo "</td><td>";
    echo "<input type=text name=Nuevo_Proveedor_Telefono value='$fila[teléfono]' size=30 maxlength=50>";
    echo "</td></tr>\n";
    echo "<tr><td align=right><b>Fax</b>\n";
    echo "</td><td>";
    echo "<input type=text name=Nuevo_Proveedor_Fax value='$fila[fax]' size=30 maxlength=50>";
    echo "</td></tr>\n";
    echo "<tr><td align=right valign=top><b>Comentarios</b>\n";
    echo "</td><td>";
    echo "<textarea name=Nuevo_Proveedor_Comentarios cols=21 rows=5>$fila[comentarios]</textarea>";
    echo "</td></tr>\n";
    echo "<tr><td align=center colspan=2>\n";
    echo "<input type=submit name=Nuevo_Proveedor_Aceptar value='Aceptar'>&nbsp;&nbsp;&nbsp;\n";
    if ($_POST['Ingreso_Cheque_Nuevo_Proveedor']=="Nuevo Proveedor"){
        echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos_ing_ch.php';\">\n";
    }
    else {
        echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos_mant_prov.php';\">\n";
    }
    echo "</td></tr>\n";
    echo "</table>";
    echo "</td></tr>\n";
    echo "</table></form><br>\n";
}
// Cabecera Configuracion requerida
require_once("../../config.php");
echo $html_header;
// Variables necesarias
$ID=$_POST["ID"] or $ID = $parametros["ID"];
$sort = $_POST["sort"] or $sort=$parametros["sort"];
$page = $parametros["page"] or $page = 0;
$filter = $_POST["filter"] or $filter = $parametros["filter"];
$keyword = $_POST["keyword"] or $keyword = $parametros["keyword"];
// Cuerpo de la pagina
//print_r($parametros);
if ($_POST["Nuevo_Proveedor_Aceptar"]) {
    $id = $_POST['Nuevo_Proveedor_Id'];
    $nombre = $_POST['Nuevo_Proveedor_Nombre'];
    $cuit = $_POST['Nuevo_Proveedor_CUIT'];
    $domicilio = $_POST['Nuevo_Proveedor_Domicilio'];
    $cp = $_POST['Nuevo_Proveedor_CP'];
    $localidad = $_POST['Nuevo_Proveedor_Localidad'];
    $provincia = $_POST['Nuevo_Proveedor_Provincia'];
    $contacto = $_POST['Nuevo_Proveedor_Contacto'];
    $mail = $_POST['Nuevo_Proveedor_Mail'];
    $telefono = $_POST['Nuevo_Proveedor_Telefono'];
    $fax = $_POST['Nuevo_Proveedor_Fax'];
    $comentarios = $_POST['Nuevo_Proveedor_Comentarios'];
    if ($nombre == "") {
        Error("Falta ingresar el Nombre del Nuevo Proveedor");
    }
    if (!es_numero($cuit)) {
        Error("El Número de CUIT ingresado no es válido");
    }
    if (!es_numero($cp)) {
        Error("El Código Postal ingresado no es válido");
    }
    if ($id){
        $sql = "SELECT * FROM bancos.proveedores WHERE Proveedor='$nombre' and IdProv<>$id";
        $result = $db->query($sql) or die($db->ErrorMsg());
        if ($result->RecordCount() > 0) {
            Error("Ya existe un Proveedor con el Nombre '$nombre'!");
        }
        if (!$error) {
             $sql = "UPDATE bancos.proveedores SET ";
             $sql .= "Proveedor='$nombre', ";
             $sql .= "CUIT=$cuit, ";
             $sql .= "Domicilio='$domicilio', ";
             $sql .= "CP=$cp, ";
             $sql .= "Localidad='$localidad', ";
             $sql .= "Provincia='$provincia', ";
             $sql .= "Contacto='$contacto', ";
             $sql .= "Mail='$mail', ";
             $sql .= "Teléfono='$telefono', ";
             $sql .= "Fax='$fax', ";
             $sql .= "Comentarios='$comentarios'";
             $sql .= "WHERE IdProv=$id";
             $result = $db->query($sql) or die($db->ErrorMsg());
             Aviso("Los datos se ingresaron correctamente");
        }
    }
    else {
          $sql = "SELECT * FROM bancos.proveedores WHERE Proveedor LIKE '$nombre'";
          $result = $db->query($sql) or die($db->ErrorMsg());
          if ($result->RecordCount() > 0) {
              Error("Ya existe un Proveedor con el Nombre '$nombre'!");
          }
          if (!$error) {
               $sql = "INSERT INTO bancos.proveedores ";
               $sql .= "(Proveedor, CUIT, Domicilio, CP, Localidad, Provincia, Contacto, Mail, Teléfono, Fax, Comentarios) ";
               $sql .= "VALUES ('$nombre', $cuit, '$domicilio', $cp, '$localidad', '$provincia', '$contacto', '$mail', '$telefono', '$fax', '$comentarios')";
               $result = $db->query($sql) or die($db->ErrorMsg());
               Aviso("Los datos se ingresaron correctamente");
               $sql = "SELECT IdProv FROM bancos.proveedores WHERE ";
               $sql .= "Proveedor='$nombre'";
               $result = $db->query($sql) or die($db->ErrorMsg());
               $row = $result->fetchrow();
               $id_prov = $row[0];
          }
    }

}

if ($parametros["cmd1"]=="detalle") {
	echo "<br><form action=bancos_mant_prov.php method=post>\n";
	NuevProv($ID);
	exit();
}
if ($_POST['Nuevo_Proveedor']=="Nuevo Proveedor") {
	echo "<br><form action=bancos_mant_prov.php method=post>\n";
	NuevProv($ID);
	exit();
}
// Barra de consulta para enviarle al formulario
echo "<form action='bancos_mant_prov.php' method='post'>";
echo "<input type=hidden name=short value='short'>\n";
echo "<table width=100% border=1 cellspacing=5 cellpadding=5 align=center>\n";
echo "<tr><td colspan=6 align=center>\n";

if (!$sort) $sort=2;

$orden = array(
"default" => "2",
"1" => "IdProv",
"2" => "Proveedor",
"3" => "Contacto",
"4" => "Mail",
"5" => "Teléfono",
"6" => "Comentarios"
);

$filtro = array(
"Proveedor"		=> "Proveedor",
"Contacto"		=> "Contacto",
"Mail"			=> "Mail",
"Teléfono"		=> "Teléfono",
"Comentarios"	=> "Comentarios",
"Domicilio"		=> "Domicilio",
"Provincia"		=> "Provincia",
"CUIT"			=> "CUIT",
"Localidad"		=> "Localidad"
);

$sql_tmp = "SELECT IdProv,Proveedor,Contacto,Mail,Teléfono,Comentarios FROM bancos.proveedores";
$link_tmp = "<a id=ma href='bancos_mant_prov.php";
list($sql,$total_Prov,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,"","buscar");
//	echo "sql: $sql total: $totalProv link: $link_pagina<Br>"; exit;

echo "<input type=submit name=Nuevo_Proveedor value='Nuevo Proveedor'>&nbsp;&nbsp;";
//echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
echo "</form>\n";
echo "</td></tr></table><br>\n";

$result = $db->query($sql) or die($db->ErrorMsg());
echo "<table border=1 width=100% cellspacing=0 cellpadding=3 bordercolor='0' align=center>";
echo "<tr><td colspan=2 align=left id=ma>\n";
echo "<b>Total:</b> $total_Prov Proveedores.</td>\n";
echo "<td colspan=4 align=right id=ma>$link_pagina</td></tr>\n";
echo "<tr><td align=right id=mo><a id=mo href='".encode_link("bancos_mant_prov.php",array ("sort"=>1,"up"=>$up,"page"=>$page,"keyword"=>$keyword,"filter"=>$filter))."'>ID</a></td>\n";
echo "<td align=right id=mo><a id=mo href='".encode_link("bancos_mant_prov.php",array ("sort"=>2,"up"=>$up,"page"=>$page,"keyword"=>$keyword,"filter"=>$filter))."'>Proveedores</td>\n";
echo "<td align=right id=mo><a id=mo href='".encode_link("bancos_mant_prov.php",array ("sort"=>3,"up"=>$up,"page"=>$page,"keyword"=>$keyword,"filter"=>$filter))."'>Contacto</td>\n";
echo "<td align=right id=mo><a id=mo href='".encode_link("bancos_mant_prov.php",array ("sort"=>4,"up"=>$up,"page"=>$page,"keyword"=>$keyword,"filter"=>$filter))."'>Mail</td>\n";
echo "<td align=right id=mo><a id=mo href='".encode_link("bancos_mant_prov.php",array ("sort"=>5,"up"=>$up,"page"=>$page,"keyword"=>$keyword,"filter"=>$filter))."'>Teléfono</td>\n";
echo "<td align=right id=mo><a id=mo href='".encode_link("bancos_mant_prov.php",array ("sort"=>6,"up"=>$up,"page"=>$page,"keyword"=>$keyword,"filter"=>$filter))."'>Comentario</td>\n";
echo "</tr>\n";
while ($row = $result->fetchrow()) {
	$ref = encode_link("bancos_mant_prov.php",array ("cmd1"=>"detalle","ID"=>$row[0]));
	tr_tag($ref,"title='Haga click aqui para ver o modificar los datos del proveedor'");
	echo "<td align=center id=ma><a href='$ref'>$row[0]</a></td>\n";
	echo "<td align=left>&nbsp;$row[1]</td>\n";
	echo "<td align=left>&nbsp;$row[2]</td>\n";
	echo "<td align=left>&nbsp;$row[3]</td>\n";
	echo "<td align=left>&nbsp;$row[4]</td>\n";
	echo "<td align=left>&nbsp;$row[5]</td>\n";
	echo "</tr>\n";
}
echo "</table>\n";
?>