<?
/*
$Author: mari $
$Revision: 1.32 $
$Date: 2006/12/27 15:39:01 $
*/
// Cabecera Configuracion requerida
require_once("../../config.php");

if ($parametros["pagina"]=="ord_pago")
	require_once("../ord_pago/fns.php");
else
	require_once("../ord_compra/fns.php");

$nro_orden=$parametros['nro_orden'];
function cuenta_bancos(){
	global $db;
	global $concepto_cuenta,$parametros;
	$tipo_valor='base';
echo "<tr>
	    <td colspan='2'><b> Cuenta : Concepto y Plan </b></td>
      </tr>
      <tr>
	    <td>";
//query para traer toda la tabla tipo_cuenta
$con="select * from general.tipo_cuenta order by concepto, plan";
$resul_con=$db->Execute($con) or die ($db->ErrorMsg()."<br>".$con);
$cant_resul_con=$resul_con->RecordCount();
echo "<select name='cuentas'>
       <option value=-1> Seleccionar Concepto y Plan </option>";
      for ($j=0; $j<$cant_resul_con; $j++){
      $cuenta=$resul_con->fields['concepto']."&nbsp;&nbsp;[ ".$resul_con->fields['plan']." ] ";
      echo "<option value='".$resul_con->fields['numero_cuenta']."'";
      if($_POST['cuentas']==$cuenta)
	  echo " selected ";
	  echo"> $cuenta </option>";
	  $resul_con->MoveNext();}
echo "</select></td></tr>";
}

// Cuerpo de la pagina
if ($_POST["Ingreso_Debito_Guardar"]) {

    $banco = $_POST['Ingreso_Debito_Banco'];
    $tipo = $_POST['Ingreso_Debito_Tipo'];
    $fecha_imputacion=$fecha = $_POST['Ingreso_Debito_Fecha'];
    $importe = $_POST['Ingreso_Debito_Importe'];

    $cuentas=$_POST['cuentas'];
 	$comentario= $_POST['comentario'];

    if ($parametros['pagina']){

    	  include_once("../contabilidad/funciones.php");
    	  //generamos el arreglo para retener los datos de imputacion, y lo enviamos por parametro
		  //Asi podremos guardarlos correctamente
          $valores_imputacion=retener_datos_imputacion();

    	  //voy a ord_compra_conf_pago
          $link=encode_link($parametros['pagina']=='ord_pago'?"../ord_pago/ord_pago_conf_pago.php":"../ord_compra/ord_compra_conf_pago.php",
                           array("pagina"=>$parametros['pagina'],
                                 "nro_orden"=>$parametros['nro_orden'],
                                 "valor_dolar"=>$parametros['valor_dolar'],
                                 "id_pago"=>$parametros['id_pago'],
                                 "pagina_pago"=>"transferencia","comentario_pagos"=>$_POST['comentario'],
                                 "banco"=>$banco,
                                 "tipo"=>$tipo,
                                 "fecha_debito"=>$fecha,
                                 "simbolo"=>"$",
                                 "importe"=>$importe,
                                 "nro_cuenta"=>$cuentas,
                                 "valores_imputacion"=>$valores_imputacion
                                 ));
                                 header("Location:$link");
    }//del then
    else{
     list($d,$m,$a) = explode("/",$fecha);
    if (FechaOk($fecha)) {
        $fecha = "$a-$m-$d";
    }
    else {
        Error("La fecha de débito es inválida");
    }
    if ($tipo == "") {
        Error("Falta ingresar el Tipo de Débito");
    }
    if ($importe == "") {
        Error("Falta ingresar el Importe del Débito");
    }
    elseif (!es_numero($importe)) {
        Error("El Importe ingresado no es válido");
    }
    if ($cuentas==-1){
        Error("Falta elegir Concepto y Plan para Cuentas");
       }

    if (!$error) {
    	$db->StartTrans();
 //       $sql_aux="select numero_cuenta from general.tipo_cuenta where (concepto='$concepto' and plan='$plan')";
 //       $result_aux=$db->query($sql_aux) or die($db->ErrorMsg()."<br>".$sql_aux);
 //       $nro_cuenta=$result_aux->fields['numero_cuenta'];
        $query="select nextval('débitos_iddébito_seq') as id_debito";
        $id_deb=sql($query,"<br>Error al traer id de debito<br>") or fin_pagina();
        $iddebito=$id_deb->fields["id_debito"];

        $sql = "INSERT INTO bancos.débitos ";
        $sql .= "(iddébito,IdBanco, FechaDébito, IdTipoDéb, ImporteDéb, numero_cuenta, comentario) ";
        $sql .= "VALUES ($iddebito,$banco,'$fecha',$tipo,$importe, $cuentas, '$comentario')";
        $result = $db->query($sql) or die($db->ErrorMsg()."<br>".$sql);

        $user_login=$_ses_user['name'];
        $fecha_log_debito=date('Y-m-d H:i:s');
        $tipo_log=1;//alta del debito
        $sql = "INSERT INTO bancos.log_debitos
        (iddébito,user_login,fecha,tipo_log,comentario)
        VALUES ($iddebito,'$user_login','$fecha_log_debito',$tipo_log,'$comentario')";
        sql ($sql,"No se puede insertar el log")or fin_pagina();

        /***********************************************************
	     Llamamos a la funcion de imputar pago
	    ************************************************************/
        $pago[]=array();
	    $pago["tipo_pago"]="iddébito";
	    $pago["id_pago"]=$iddebito;
	    $id_imputacion=$_POST["id_imputacion"];

	    include_once("../contabilidad/funciones.php");
	    imputar_pago($pago,$id_imputacion,$fecha_imputacion);

        $db->CompleteTrans();
        Aviso("Los datos se ingresaron correctamente!!");
            }
   }  //del else de parametros pagina
  } //del post de guardar
echo $html_header;
$Fecha_Hoy=date("Y-m-d",mktime());
$Banco_Default=4;
?>
<script>
function control_campos(){

if (document.all.Ingreso_Debito_Tipo.value=="")
{
   alert("Falta seleccionar el tipo de débito");
   return false;
}
if (document.all.Ingreso_Debito_Fecha.value=="")
{
   alert("Falta ingresar Fecha Débito");
   return false;
}
if (document.all.Ingreso_Debito_Importe.value=="")
{
   alert("Falta ingresar Importe");
   return false;
}
if (document.all.cuentas.value==-1)
{
   alert("Falta seleccionar Concepto y Plan");
   return false;
}
if (document.all.comentario.value=="")
{
   alert("Falta ingresar Comentario");
   return false;
}
if(!control_campos_imputacion())
 return false;

return true;
}
</script>
<script src="../../lib/NumberFormat150.js"></script>
<?
//**********************************************
//para cuando viene de orden de compra
$orden_compra=datos_orden_compra($parametros);
if ($orden_compra)
  $readonly="Readonly";
///*********************************************
$link=encode_link("./bancos_ing_deb.php",array("pagina"=>$parametros['pagina'],
                                               "nro_orden"=>$parametros['nro_orden'],
                                               "valor_dolar"=>$parametros['valor_dolar'],
                                               "id_pago"=>$parametros['id_pago']));
echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";

echo "<form action=$link method=post>\n";
echo "<table align=center cellpadding=2 cellspacing=0 border=1 bordercolor='$bgcolor3'>\n";

echo "<tr bordercolor='#000000'><td id=mo align=center>Ingreso de Débitos</td></tr>";
/*
$pagos_restantes=1;
if ($orden_compra) {
                   $pagos_restantes=$orden_compra["numeros_pagos"]-$orden_compra["pagos_realizados"];
                   echo "<tr><td><b>Orden de Compra: Faltan $pagos_restantes Pagos</td></tr>";
                   }
*/
echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0 bgcolor=$bgcolor_out>";
echo "<tr><td align=right><b>Banco</b></td>";
echo "<td align=left>";
echo "<select name=Ingreso_Debito_Banco>\n";
$sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
$result = $db->query($sql) or die($db->ErrorMsg());

while ($fila = $result->fetchrow()) {
	echo "<option value=".$fila['idbanco'];
	if ($fila['idbanco'] == $Banco_Default)
	echo " selected";
	echo ">".$fila['nombrebanco']."</option>\n";
}
echo "</select></td></tr>\n";
echo "<tr><td align=right><b>Tipo de Débito</b></td>";
echo "<td align=left>";
if ($orden_compra)
  $Fecha_Emision = date("d/m/Y",mktime());

echo "<select name=Ingreso_Debito_Tipo >\n";
echo "<option value='' selected ></option>\n";
$sql = "SELECT * FROM bancos.tipo_débito";
$result = $db->query($sql) or die($db->ErrorMsg());
while ($fila = $result->fetchrow()) {

    if (($orden_compra)||($fila['tipodébito']=="MEP")) $selected="selected";

  	echo "<option value=".$fila['idtipodéb']." $selected>".$fila['tipodébito']."</option>\n";
}
echo "</select></td></tr>\n";
echo "<tr><td align=right><b>Fecha Débito</b></td>";
echo "<td align=left>";
echo "<input type=text size=10 name=Ingreso_Debito_Fecha value='$Fecha_Emision' title='Ingrese la fecha de débito'>";
echo link_calendario("Ingreso_Debito_Fecha");
echo "</td></tr>\n";
echo "<tr><td align=right><b>Importe</b>\n";
echo "</td><td>";

if ($orden_compra) {
                    $importe_dolares=number_format($orden_compra["importe_dolares"],"2",".","");
                    echo "<input type=hidden  name=importe_dolares value=$importe_dolares>";
                    if ($parametros['valor_dolar']) $importe=number_format($importe_dolares,"2",".","");
                                                     else $importe=number_format($orden_compra["importe"],"2",".","");
                    }
echo "<input $readonly type=text name=Ingreso_Debito_Importe value='$importe' size=22 maxlength=50 onchange='setear_montos_imputacion(\"iddébito\")'>&nbsp;";

echo "</td>";
echo "</tr>\n";
echo "<tr><td align=center colspan=2><table cellpadding=2 cellspacing=0 border=1 bordercolor='$bgcolor3'>";
//llamo a la funcion que muestras las cuentas con los conceptos y los planes
cuenta_bancos();
echo "</table></td></tr>";

if($nro_orden)
{
	//generamos el comentario del cheque
	$ordenes_atadas=PM_ordenes($nro_orden);
	$tam=sizeof($ordenes_atadas);
	$Comentarios="Pago correspondiente a la/s  Orden/es de ";
	$Comentarios.=$parametros['pagina']=='ord_pago'?"Pago Nº:":"Compra nro:";
	for($i=0;$i<$tam;$i++)
	 $Comentarios.=" ".$ordenes_atadas[$i];
}
echo "<tr><td colspan=2>\n";
echo "Comentario:<br>\n";
echo "<textarea name='comentario' cols=50 rows=5>$Comentarios</textarea>\n";
echo "</td></tr>\n";
?>
<tr>
 <td colspan="2">
 <?
   include_once("../contabilidad/funciones.php");

   tabla_imputacion("",$importe);
 ?>
  <input type="hidden" name="id_imputacion" value="<?=$id_imputacion?>">
 </td>
</tr>
<?
echo "<tr><td align=center colspan=2>\n";
echo "<input type=submit name=Ingreso_Debito_Guardar value='Guardar' onclick='return control_campos()'>&nbsp;&nbsp;&nbsp;\n";
echo "</td>";
$page=$parametros['pagina']=='ord_pago'?"../ord_pago/ord_pago_pagar.php":"../ord_compra/ord_compra_pagar.php";
if ($orden_compra){
                   $link=encode_link($page,array("nro_orden"=>$parametros["nro_orden"]));
                   echo "<td align='center'>";
                   echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='$link';\">\n";
                   echo "</td>";
                   }

echo "</tr>\n";
echo "</table>";
echo "</td></tr>\n";
echo "</table>\n";
?>