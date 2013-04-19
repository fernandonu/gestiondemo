<?
/*
Author: diegoinga 
MODIFICADO POR 
$Author: fernando $
$Revision: 1.7 $
$Date: 2005/09/07 22:28:47 $

*/
require_once("../../config.php");

if ($_POST['Modificar']) //Modifica el cheque
{
 $link = encode_link("modificar_cheque_dif.php",array("id_chequedif"=>$_POST['Modificar_Cheque_Id']));
 header("location: $link");
}

if ($_POST['Depositar']) //Modifica el cheque
{
 $link = encode_link("deposito_diferido.php",array("id_chequedif"=>$_POST['Modificar_Cheque_Id']));
 header("location: $link");
}

if ($_POST['Ingresar']) //Modifica el cheque
{
 $link = encode_link("ingresos_chequedif.php",array("id_chequedif"=>$_POST['Modificar_Cheque_Id'],"distrito"=>1,"pagina_viene"=>"cheques diferidos"));
 header("location: $link");
}


variables_form_busqueda("chequesdif_pend",array("id_banco" => "",
										   "Entre_Fechas" => "",
										   "Entre_Fechas_Campo" => "",
										   "Entre_Fechas_Desde" => "",
										   "Entre_Fechas_Hasta" => ""));
										   
$Banco = $_POST["Mov_Cheques_Pendientes_Banco"] or $Banco = 0;
if ($parametros["id_banco"]) $Banco=$parametros["id_banco"];

$datos = array(
               "cheques" => array(
               "titulo" => "Cheques Diferidos Pendientes",
               "sql" => "SELECT bancos.cheques_diferidos.fecha_vencimiento,
                            bancos.cheques_diferidos.fecha_ingreso,
                            bancos.cheques_diferidos.monto,
                            bancos.cheques_diferidos.nro_cheque,
                            bancos.cheques_diferidos.comentario,               
                            bancos.cheques_diferidos.ubicacion,               
                            licitaciones.entidad.nombre as entidad,
                            bancos.empresas_cheques.nombre as pertenece,  
                            FROM bancos.cheques_diferidos
                            JOIN licitaciones.entidad using(id_entidad)
                            JOIN empresas_cheques using(id_empresa_cheque)
                            ON bancos.cheques_diferidos.id_entidad=licitaciones.entidad.id_entidad ",
               "where" => "bancos.cheques_diferidos.IdDepósito IS NULL and bancos.cheques_diferidos.id_ingreso_egreso IS NULL",
               "fecha" => array (
                            "bancos.cheques_diferidos.fecha_vencimiento" => "Vencimiento",
                            "bancos.cheques_diferidos.fecha_ingreso" => "Emisión"
                          ),
               "sumar" => array(
                              "bancos.cheques_diferidos.monto" => "bancos.cheques_diferidos"
                          )
           )
           
);

echo $html_header;
?>
<script>

function habilitar()
{document.all.Modificar.disabled=false;
 document.all.Depositar.disabled=false;
 document.all.Ingresar.disabled=false;
}

function chequea_radio(indice)
 {
 if (document.all.Modificar_Cheque_Id.length>1)
  document.all.Modificar_Cheque_Id[indice].checked="true";
 else
  document.all.Modificar_Cheque_Id.checked="true";
 habilitar();
 }
 
</script>
<!--Menu Contextual -->
<div id="ie5menu" class="skin1" onMouseover="highlightie5()" onMouseout="lowlightie5()" onClick="jumptoie5();">
<div class="menuitems" url="javascript:document.all.Depositar.click();">Depositar</div>
<div class="menuitems" url="javascript:document.all.Modificar.click()">Modificar</div>
<div class="menuitems" url="javascript:document.all.Ingresar.click()">Ingresar Caja</div>
</div>
<!--Fin menu contextual-->
<script>
//llama al menu contextual
if (document.all && window.print) {
ie5menu.className = menuskin;
document.body.oncontextmenu = showmenuie5;
document.body.onclick = hidemenuie5;
}
</script>
<br>
<font size="4"><b>Cheques Diferidos Pendientes</b></font>
<hr>
<?
echo "<form action=ver_chequesdif_pend.php method=post>\n";

if ($Entre_Fechas_Campo) {
    $fecha_campo = $Entre_Fechas_Campo;
}
if ($Entre_Fechas == "1" and $_POST["Entre_Fechas"]) {
    $entre_fechas = "1";
    $fechas_check = " checked";
}
else {
    $fechas_check = "";
}
if (FechaOk($Entre_Fechas_Desde)) {
    $Fecha_Desde = $Entre_Fechas_Desde;
    $Fecha_Desde_db = Fecha_db($Entre_Fechas_Desde);
}
else {
    //por defecto, el dia de hoy
   /* $Fecha_Desde = date("d/m/Y",mktime());
    $Fecha_Desde_db = date("Y-m-d",mktime());*/
   //cambiado por Pablo Rojo:ahora se restan 40 días mas a fecha Desde.
	$Fecha_Desde = date("d/m/Y",mktime() + (-40 * 24 * 60 * 60));
    $Fecha_Desde_db = date("Y-m-d",mktime() + (-40 * 24 * 60 * 60));

}
if (FechaOk($Entre_Fechas_Hasta)) {
    $Fecha_Hasta = $Entre_Fechas_Hasta;
    $Fecha_Hasta_db = Fecha_db($Entre_Fechas_Hasta);
}
else {
    //por defecto, los proximos 40 dias
/*    $Fecha_Hasta = date("d/m/Y",(mktime() + (40 * 24 * 60 * 60)));
    $Fecha_Hasta_db = date("Y-m-d",(mktime() + (40 * 24 * 60 * 60)));*/
    //cambiado por Pablo Rojo:ahora se agregan 30 días mas a fecha Hasta.
    $Fecha_Hasta = date("d/m/Y",(mktime() + (30 * 24 * 60 * 60)));
    $Fecha_Hasta_db = date("Y-m-d",(mktime() + (30 * 24 * 60 * 60)));

}

cargar_calendario();

//Total
$sql = "SELECT sum(monto) AS total FROM cheques_diferidos WHERE IdDepósito IS NULL and id_ingreso_egreso IS NULL";
$result = $db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$res_tmp = $result->fetchrow();
$Total = formato_money($res_tmp[total]);

//Datos

echo "<table width='95%' align=center cellpadding=5 cellspacing=0 border=0 >"; //bordercolor=$bgcolor3
echo "<tr><td colspan=5 align=center><b>Banco</b>";
$sql="select id_banco,nombre from bancos_cheques_dif order by nombre";
$result=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
echo "<select name=Mov_Cheques_Pendientes_Banco OnChange=\"document.forms[0].submit();\">\n";
echo "<option value='todos' ";
if ($Banco==0)
    echo " selected";
echo ">Todos</option>\n";
while ($fila = $result->fetchrow()) {
echo "<option value=".$fila["id_banco"];
if ($fila["id_banco"] == $Banco)
    echo " selected";
echo ">".$fila["nombre"]."</option>\n";
}
echo "</select></td>\n";
echo "<td colspan=3 align=center><b>Total Pendientes: \$ $Total</b>";
echo "</td></tr>";
echo "<tr><td colspan=8 align=center>";
// Formulario de busqueda
$orden = array(
         "default" => "1",     //campo por defecto
         "1" => "bancos.cheques_diferidos.fecha_vencimiento",
         "2" => "bancos.cheques_diferidos.fecha_ingreso",
         "3" => "bancos.cheques_diferidos.nro_cheque",
         "4" => "bancos.cheques_diferidos.monto",
         "5" => "bancos.cheques_diferidos.comentario",
         "6" => "bancos.cheques_diferidos.ubicacion",
         "7" => "bancos.empresas_cheques.nombre",
         "8" => "licitaciones.entidad.nombre",
);
$filtro = array(
          "bancos.cheques_diferidos.fecha_vencimiento" => "Fecha de vencimiento",
          "bancos.cheques_diferidos.fecha_ingreso" => "Fecha de emisión",
          "bancos.cheques_diferidos.nro_cheque" => "Número",
          "bancos.cheques_diferidos.monto" => "Importe",
          "bancos.cheques_diferidos.comentario" => "Comentarios",
          "bancos.cheques_diferidos.ubicacion" => "Ubicacion",
          "bancos.empresas_cheques.nombre" => "Pertenece a",
          "licitaciones.entidad.nombre"=> "Entidad"
);
//sentencia sql que sin ninguna condicion
$sql_tmp = "SELECT bancos.cheques_diferidos.id_chequedif,bancos.cheques_diferidos.id_banco,bancos.cheques_diferidos.fecha_vencimiento,";
$sql_tmp .= "bancos.cheques_diferidos.fecha_ingreso,";
$sql_tmp .= "bancos.cheques_diferidos.nro_cheque,";
$sql_tmp .= "bancos.cheques_diferidos.monto,";
$sql_tmp .= "bancos.cheques_diferidos.comentario,";
$sql_tmp .= "bancos.cheques_diferidos.ubicacion,";
$sql_tmp .= "licitaciones.entidad.nombre as entidad,";
$sql_tmp .= "bancos.empresas_cheques.nombre as pertenece ";
$sql_tmp .= "FROM bancos.cheques_diferidos left join entidad using(id_entidad) ";
$sql_tmp .= "join bancos.empresas_cheques using(id_empresa_cheque) ";

//prefijo para los links de paginas siguiente y anterior
$link_tmp = "";
//condiciones extras de la consulta
$where_tmp = "cheques_diferidos.IdDepósito IS NULL and cheques_diferidos.id_ingreso_egreso IS NULL and activo=1 ";
if ($Banco!="todos")
    $where_tmp .= "AND bancos.cheques_diferidos.id_banco=$Banco";

if ($entre_fechas) {
$where_tmp .= " AND $fecha_campo Between '$Fecha_Desde_db' AND '$Fecha_Hasta_db'";
}
$itemspp=5000;
list($sql,$total_pend,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");

echo "&nbsp;&nbsp;&nbsp;<input type=submit name='form_busqueda' value='   Buscar   '>";
echo "</td></tr>\n";
echo "<tr><td colspan=3 align='center' valign='middle'>";
echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<input name='Entre_Fechas' type='checkbox' value='1'$fechas_check>";
echo "<b> Entre fechas:</b></td>";
echo "<td colspan=5 align=left><b>Campo:</b>";
echo "<select name=Entre_Fechas_Campo>";
foreach($datos["cheques"]["fecha"] as $campo => $descripcion) {
echo "<option value='$campo'";
if ($fecha_campo == $campo) echo " selected";
   echo ">$descripcion</option>";
}
echo "</select>&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<b>Desde: </b>";
echo "<input type=text size=10 name=Entre_Fechas_Desde value='$Fecha_Desde' title='Ingrese la fecha de inicio y\nhaga click en Actualizar'>";
echo link_calendario("Entre_Fechas_Desde");
echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>Hasta: </b>";
echo "<input type=text size=10 name=Entre_Fechas_Hasta value='$Fecha_Hasta' title='Ingrese la fecha de finalización\ny haga click en Actualizar'>";
echo link_calendario("Entre_Fechas_Hasta");
echo "</td></tr>";
echo "<tr><td colspan=8 align=center>";
echo "<input type=submit name=Modificar value='Modificar Datos' disabled style='cursor:hand'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<input type=submit name=Depositar value='Depositar Cheque' disabled style='cursor:hand'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<input type=submit name=Ingresar value='Ingresar en Caja' disabled style='cursor:hand'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
echo "</td></tr>\n";
//echo $sql;
$result = $db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$SubTotal = 0;
echo "</table>";
echo "<table class='bordes' width='99%' align='center'>";
echo "<tr ><td id=ma colspan=9 align=center>\n";//bordercolor='#000000'
echo "<table id=ma border=0 width=100%><tr>\n";
echo "<td align=center width=70%>Cheques Pendientes: $total_pend</td>\n";
echo "<td align=center width=30%>$link_pagina&nbsp;</td>\n";
echo "</tr></table>\n";
echo "</td></tr>\n";
echo "<tr  id=mo>";//bordercolor='#000000'
echo "<td align=center>&nbsp;</td>";
echo "<td align=center><a id=mo href='".encode_link('ver_chequesdif_pend.php',Array('sort'=>1,'up'=>$up))."'>Vencimiento</a></td>\n";
echo "<td align=center><a id=mo href='".encode_link('ver_chequesdif_pend.php',Array('sort'=>2,'up'=>$up))."'>Emisión</a></td>\n";
echo "<td align=center><a id=mo href='".encode_link('ver_chequesdif_pend.php',Array('sort'=>3,'up'=>$up))."'>Número</a></td>";
echo "<td align=center><a id=mo href='".encode_link('ver_chequesdif_pend.php',Array('sort'=>4,'up'=>$up))."'>Importe</a></td>";
echo "<td align=center><a id=mo href='".encode_link('ver_chequesdif_pend.php',Array('sort'=>7,'up'=>$up))."'>Pertenece</a></td>";
echo "<td align=center><a id=mo href='".encode_link('ver_chequesdif_pend.php',Array('sort'=>8,'up'=>$up))."'>Entidad</a></td>";
echo "<td align=center><a id=mo href='".encode_link('ver_chequesdif_pend.php',Array('sort'=>5,'up'=>$up))."'>Comentarios</a></td>";
echo "<td align=center><a id=mo href='".encode_link('ver_chequesdif_pend.php',Array('sort'=>6,'up'=>$up))."'>Ubicacion</a></td>";

echo "</tr>\n";
$i=0;
while ($fila = $result->fetchrow()) {
	echo "<tr bgcolor=$bgcolor_out oncontextmenu='chequea_radio($i)'>\n";//bordercolor='#000000'
	echo "<td align=center><input type=radio name=Modificar_Cheque_Id onclick='habilitar();' value='".$fila["id_chequedif"]."'></td>";
	echo "<td align=center>".Fecha($fila['fecha_vencimiento'])."</td>\n";
	echo "<td align=center>".Fecha($fila['fecha_ingreso'])."</td>\n";
	echo "<td align=center>".$fila['nro_cheque']."</td>\n";
	echo "<td align=right>\$".formato_money($fila['monto'])."</td>\n";
	echo "<td align=center>".$fila['pertenece']."</td>\n";
	echo "<td align=left>".$fila['entidad']."&nbsp;</td>\n";
	echo "<td align=left>".$fila['comentario']."&nbsp;</td>\n";
	echo "<td align=left>".$fila['ubicacion']."&nbsp;</td>\n";
	echo "</tr>\n";
	$SubTotal += $fila['monto'];
	$i++;
}

echo "<tr bgcolor=$bgcolor3><td colspan=9 align=center><b>Subtotal Pendiente: \$ ".formato_money($SubTotal)."</b></td></tr>";
echo "</table></form>\n";


?>