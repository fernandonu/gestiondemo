<?
 /*
$Author: nazabal $
$Revision: 1.2 $
$Date: 2005/06/24 20:28:16 $
*/
// Funciones exclusivas
require_once("../../config.php");
require_once("../ord_compra/fns.php");


function NuevProv ($id=0) {
    global $bgcolor3;
    echo "<br><table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor3'>\n";
    // Titulo del Formulario
    if ($id!=0) echo "<tr bordercolor='#000000'><td id=mo align=center>Datos del Proveedor</td></tr>";
    else echo "<tr bordercolor='#000000'><td id=mo align=center>Nuevo Proveedor</td></tr>";
    echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0>";
    if ($id!=0) {
        $sql="SELECT * FROM proveedor WHERE IdProv=$id";
        $result = $db->query($sql) or die($db->ErrorMsg());
        $fila=$result->fetchrow();
        echo "<tr><td align=right><b>Id Proveedor</b></td>";
        echo "<td align=left><input type=hidden name=Nuevo_Proveedor_Id value='$fila[idprov]'>$fila[idprov]</td</tr>";
    }
    echo "<tr><td align=right><b>Nombre</b></td>";
    echo "<td align=left>";
    echo "<input type=text name=Nuevo_Proveedor_Nombre value='$fila[proveedor]' size=30 maxlength=100>\n";
    echo "</td></tr>\n";
    echo "<tr><td align=right><b>Número C.U.I.T.</b>\n";
    echo "</td><td>";
    echo "<input type=text name=Nuevo_Proveedor_CUIT value='$fila[cuit]' size=30 maxlength=20>";
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
if ($_POST['Ingreso_Cheque_Guardar']) {
    //$_POST[cmd] = "Ingreso_Cheque";
    //include_once("./bancos_data.php");
    
    $banco = $_POST['Ingreso_Cheque_Banco'];
    $proveedor = $_POST['Ingreso_Cheque_Proveedor'];
    $fecha_e = $_POST['Ingreso_Cheque_Fecha_Emision'];
    $fecha_v = $_POST['Ingreso_Cheque_Fecha_Vencimiento'];
    $fecha_p = $_POST['Ingreso_Cheque_Fecha_Debito'];
    $numero = $_POST['Ingreso_Cheque_Numero'];
    $importe = $_POST['Ingreso_Cheque_Importe'];
    $comentarios = $_POST['Ingreso_Cheque_Comentarios'];

    if ($parametros['pagina']){
          //voy a ord_compra_conf_pago
          $link=encode_link("../ord_compra/ord_compra_conf_pago.php",array(
                           "pagina"=>$parametros['pagina'],
                           "nro_orden"=>$parametros['nro_orden'],
                           "pagina_pago"=>"cheque",
                           "id_pago"=>$parametros['id_pago'],
                           "valor_dolar"=>$parametros['valor_dolar'],
                           "banco"=>$banco,
                           "proveedor"=>$proveedor,
                           "fecha_e"=>$fecha_e,
                           "fecha_v"=>$fecha_v,
                           "fecha_p"=>$fecha_p,
                           "numero"=>$numero,
                           "importe"=>$importe,
                           "simbolo"=>"$",
                           "comentarios"=>$comentarios,
                           "id_chequera"=>$_POST['radio1'],
                           "Ingreso_Cheque_Numero"=>$_POST['Ingreso_Cheque_Numero']
                           ));
          header("Location:$link") or die();
    }
    else{
    echo $parametros['pagina'];
    list($d,$m,$a) = explode("/",$fecha_e);
    if (FechaOk($fecha_e)) {
        $fe_db = "$a-$m-$d";
    }
    else {
        Error("La fecha de Emisión ingresada es inválida");
    }
    list($d,$m,$a) = explode("/",$fecha_v);
    if (FechaOk($fecha_v)) {
        $fv_db = "$a-$m-$d";
    }
    else {
        Error("La fecha de Vencimiento ingresada es inválida");
    }
    if ($fecha_p == "") {
        $fp_db = "NULL";
    }
    else {
        list($d,$m,$a) = explode("/",$fecha_p);
        if (FechaOk($fecha_p)) {
            $fp_db = "'$a-$m-$d'";
        }
        else {
            Error("La fecha de Débito ingresada es inválida");
        }
    }
    if ($proveedor == "") {
        Error("Falta ingresar el Proveedor");
    }
    if ($numero == "") {
        Error("Falta ingresar el Número del Cheque");
    }
    if ($importe == "") {
        Error("Falta ingresar el Importe");
    }
    elseif (!es_numero($importe)) {
        Error("El Importe ingresado es inválido");
    }
   if ($numero=="")
    $numero=-1;
   $sql="select númeroch,nombrebanco from tipo_banco join cheques using (idbanco) where";
   $sql.=" cheques.númeroch=$numero and cheques.idbanco=$banco";
   $resultado=$db->execute($sql)or die($db->errormsg());
   $cantidad_cheques=$resultado->RecordCount();

   if ($cantidad_cheques) Error("Ese número de cheque ya existe");

     if (!$error) {

        $db->StartTrans();
        
      //actualizamos chequera
        /*$sql="update chequera set ultimo_cheque_usado=".$_POST['Ingreso_Cheque_Numero']." where id_chequera=".$_POST['radio1'];
        $resultado=$db->execute($sql)or die($sql."<br>".$db->errormsg());
        */$sql = "INSERT INTO bancos.cheques ";
        $sql .= "(IdBanco, FechaEmiCh, FechaVtoCh, FechaPrev, FechaDébCh, NúmeroCh, ImporteCh, IdProv, Comentarios) ";
        $sql .= "VALUES ($banco,'$fe_db','$fv_db',$fp_db,NULL,$numero,$importe,$proveedor,'$comentarios')";
        $result = $db->query($sql) or die($sql."<br>".$db->ErrorMsg());
       /* $sql = "SELECT id_usuario FROM usuarios WHERE login='".$_ses_user["login"]."'";
        $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
        $id_usuario=$resultado->fields['id_usuario'];
        $sql = "select * from bancos.ultimo_cheque_usuario where id_usuario=$id_usuario";
        $resultado=$db->execute($sql)or $db->errormsg($sql."<br>".$db->errormsg());
        $cantidad_usuarios=$resultado->RecordCount();
        if ($cantidad_usuarios>0){
                                 //hago update
                                 $sql="update bancos.ultimo_cheque_usuario set númeroch=$numero , idbanco=$banco, ultima_chequera=".$_POST['radio1']." where id_usuario=$id_usuario";
                                 $db->execute($sql) or die($sql."<br>".$db->errormsg());
                                  }
                                 else
                                 {
                                 //si no hago insert ya que es nuevo
                                 $sql="insert into bancos.ultimo_cheque_usuario (númeroch,idbanco,id_usuario,ultima_chequera) values($numero,$banco,$id_usuario,".$_POST['radio1'].")";
                                 $db->execute($sql) or die($sql."<br>".$db->errormsg());
                                  }
        */Aviso("Los datos se ingresaron correctamente");
        $db->CompleteTrans();
       }
  }//del else que va a la pagina
} //del if principal

echo $html_header;
//recupero los datos de la orden de compra

// Cuerpo de la pagina
if ($_POST["Nuevo_Proveedor_Aceptar"]) {
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
    $sql = "SELECT * FROM proveedor WHERE Proveedor LIKE '$nombre'";
    $result = $db->query($sql) or die($db->ErrorMsg());
    if ($result->RecordCount() > 0) {
        Error("Ya existe un Proveedor con el Nombre '$nombre'!");
    }
    if (!$error) {
        $sql = "INSERT INTO proveedor ";
        $sql .= "(Proveedor, CUIT, Domicilio, CP, Localidad, Provincia, Contacto, Mail, Teléfono, Fax, Comentarios) ";
        $sql .= "VALUES ('$nombre', $cuit, '$domicilio', $cp, '$localidad', '$provincia', '$contacto', '$mail', '$telefono', '$fax', '$comentarios')";
        $result = $db->query($sql) or die($db->ErrorMsg());
        Aviso("Los datos se ingresaron correctamente");
        $sql = "SELECT IdProv FROM proveedor WHERE ";
        $sql .= "Proveedor='$nombre'";
        $result = $db->query($sql) or die($db->ErrorMsg());
        $row = $result->fetchrow();
        $id_prov = $row[0];
    }
    if (!$_POST['Ingreso_Cheque_Banco']) {
        //$cmd="Mant_Proveedores";
        //include "./bancos_forms.php";
        exit();
    }
    $Ing_Banco=$_POST['Ingreso_Cheque_Banco'];
    $Ing_Fecha_Emision = $_POST['Ingreso_Cheque_Fecha_Emision'];
    $Ing_Fecha_Vencimiento = $_POST['Ingreso_Cheque_Fecha_Vencimiento'];
    $Ing_Fecha_Debito = $_POST['Ingreso_Cheque_Fecha_Debito'];
    $Ing_Importe = $_POST['Ingreso_Cheque_Importe'];
    $Ing_Comentarios = $_POST['Ingreso_Cheque_Comentarios'];
    $Ing_Numero = $_POST['Ingreso_Cheque_Numero'];
    echo "<form action=bancos_ing_ch2.php method=post>\n";
    echo "<input type=hidden name=Ingreso_Cheque_Proveedor value=$id_prov>\n";
    echo "<input type=hidden name=Ingreso_Cheque_Banco value='$Ing_Banco'>\n";
    echo "<input type=hidden name=Ingreso_Cheque_Fecha_Emision value='$Ing_Fecha_Emision'>\n";
    echo "<input type=hidden name=Ingreso_Cheque_Fecha_Vencimiento value='$Ing_Fecha_Vencimiento'>\n";
    echo "<input type=hidden name=Ingreso_Cheque_Fecha_Debito value='$Ing_Fecha_Debito'>\n";
    echo "<input type=hidden name=Ingreso_Cheque_Importe value='$Ing_Importe'>\n";
    echo "<input type=hidden name=Ingreso_Cheque_Comentarios value='$Ing_Comentarios'>\n";
    echo "<input type=hidden name=Ingreso_Cheque_Numero value='$Ing_Numero'>\n";
    echo "<br><center>\n";
    echo "<input type=submit name=Volver value='Volver'>\n";
    echo "</center></form>\n";
    exit();
}
if ($_POST['Ingreso_Cheque_Nuevo_Proveedor']) {
	$Banco=$_POST['Ingreso_Cheque_Banco'];
	$Fecha_Emision = $_POST['Ingreso_Cheque_Fecha_Emision'];
	$Fecha_Vencimiento = $_POST['Ingreso_Cheque_Fecha_Vencimiento'];
	$Fecha_Debito = $_POST['Ingreso_Cheque_Fecha_Debito'];
	$Importe = $_POST['Ingreso_Cheque_Importe'];
	$Comentarios = $_POST['Ingreso_Cheque_Comentarios'];
	$Numero = $_POST['Ingreso_Cheque_Numero'];
	echo "<br><form action=bancos_ing_ch2.php method=post>\n";
	//echo "<input type=hidden name=mode value=data>\n";
	//echo "<input type=hidden name=cmd value=Nuevo_Proveedor>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Banco value='$Banco'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Fecha_Emision value='$Fecha_Emision'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Fecha_Vencimiento value='$Fecha_Vencimiento'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Fecha_Debito value='$Fecha_Debito'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Importe value='$Importe'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Comentarios value='$Comentarios'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Numero value='$Numero'>\n";
	NuevProv();
	exit();
}
if (!$_POST['Ingreso_Cheque_Banco']) {
	$Banco=4;  // Banco por defecto
	$Fecha_Emision = date("d/m/Y",mktime());
	$Fecha_Vencimiento = "";
	$Fecha_Debito = "";
	$Proveedor = "";
	$Importe = "";
	$Comentarios = "";
}
else {
	$Banco=$_POST['Ingreso_Cheque_Banco'];
	$Fecha_Emision = $_POST['Ingreso_Cheque_Fecha_Emision'];
	$Fecha_Vencimiento = $_POST['Ingreso_Cheque_Fecha_Vencimiento'];
	$Fecha_Debito = $_POST['Ingreso_Cheque_Fecha_Debito'];
	$Proveedor = $_POST['Ingreso_Cheque_Proveedor'];
	$Importe = $_POST['Ingreso_Cheque_Importe'];
	$Comentarios = $_POST['Ingreso_Cheque_Comentarios'];
	if ($_POST['Ingreso_Cheque_Numero'] != ($Ultimo_Cheque + 1))
	$Numero = $_POST['Ingreso_Cheque_Numero'];
}


/*
$sql = "SELECT Max(NúmeroCh) AS ultimo ";
$sql .= "FROM bancos.cheques ";
$sql .= "WHERE IdBanco=$Banco";
$result = $db->query($sql) or die($db->ErrorMsg());
$res_tmp = $result->fetchrow();
*/
$sql = "SELECT id_usuario FROM usuarios WHERE login='".$_ses_user["login"]."'";
$resultado=$db->execute($sql) or die();
$id_usuario=$resultado->fields['id_usuario'];

$sql = "select * from bancos.ultimo_cheque_usuario where id_usuario=$id_usuario";
$res_tmp=$db->execute($sql) or die($db->errormsg());
$cant_ult_ch=$res_tmp->RecordCount();
if ($cant_ult_ch) {
             $Ultimo_Cheque = $res_tmp->fields['númeroch'];
             }
             else
             {//si no esta registrado en la tabla trae el ultimo numero ingresado
              //de toda la tabla
             $sql = "SELECT Max(NúmeroCh) AS ultimo ";
             $sql .= "FROM bancos.cheques ";
             $sql .= "WHERE IdBanco=$Banco";
             $res_tmp = $db->execute($sql) or die($db->ErrorMsg());
             $Ultimo_Cheque = $res_tmp->fields['ultimo'];
             }

//para cuando viene de orden de compra
$orden_compra=datos_orden_compra($parametros);
if ($orden_compra) {
                    $readonly="readonly";
                    }
                    else
                    {
                    $readonly="";
                    }

$nro_orden=$parametros['nro_orden'];
$link=encode_link("./bancos_ing_ch2.php",array("pagina"=>$parametros['pagina'],
                                              "nro_orden"=>$parametros['nro_orden'],
                                              "valor_dolar"=>$parametros['valor_dolar'],
                                              "id_pago"=>$parametros['id_pago']));

echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";

echo "<form action='$link' method=post>\n";
echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor3'>\n";
echo "<tr bordercolor='#000000'><td id=mo align=center>Ingreso de Cheques</td></tr>";
echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0>";
echo "<tr><td align=right><b>Banco</b></td>";
echo "<td align=left colspan=3>";
echo "<select name=Ingreso_Cheque_Banco  OnChange='document.forms[0].submit();'>\n";
$sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
$result = $db->query($sql) or die($db->ErrorMsg());
while ($fila = $result->fetchrow()) {
	echo "<option value=".$fila['idbanco'];
	if ($fila['idbanco'] == $Banco)
	echo " selected";
	echo ">".$fila['nombrebanco']."</option>\n";
}
echo "</select></td></tr>\n";
echo "<tr><td align=right><b>A la order de</b></td>";
echo "<td align=left colspan=3>";
echo "<select name=Ingreso_Cheque_Proveedor >\n";
echo "<option value='' selected></option>\n";
$sql = "SELECT id_proveedor, razon_social FROM general.proveedor ORDER BY razon_social";
$result = $db->query($sql) or die($db->ErrorMsg());
//orden de compra
if ($orden_compra) {
                   $Proveedor=$orden_compra["proveedor"];
                   $dias_pago=$orden_compra["dias"];
                   if ($Fecha_Vencimiento==""){
                                               $Fecha_Vencimiento= date("d/m/Y",mktime(0,0,0,date("m"),date("d")+$dias_pago,date("Y")));
                                               }//del if de fecha de vencimiento
                   }

while ($fila = $result->fetchrow()) {
	echo "<option value='".$fila['id_proveedor']."'";
	if ($fila['id_proveedor'] == "$Proveedor") echo " selected";
	echo ">".$fila['razon_social']."</option>\n";
}
echo "</select></td></tr>\n";
echo "<tr><td align=right><b>Fecha de Emisión</b></td>";
echo "<td align=left>";
echo "<input type=text size=10 name=Ingreso_Cheque_Fecha_Emision value='$Fecha_Emision' title='Ingrese la fecha de emisión del cheque'>";
echo link_calendario("Ingreso_Cheque_Fecha_Emision");
echo "</td>\n";
echo "<td colspan=2 align=right>\n";
// echo "<input type=submit name=Ingreso_Cheque_Nuevo_Proveedor value='Nuevo Proveedor'>\n";
/*echo "<table border=1>";
echo"<tr><td>&nbsp</td><td><b>Ultimo nro utilizado</td></tr>";
$sql = "SELECT id_usuario FROM usuarios WHERE login='".$_ses_user["login"]."'";
$resultado_usuario=$db->execute($sql) or die($sql);
$id_usuario=$resultado_usuario->fields['id_usuario'];
$sql = "select ultima_chequera from bancos.ultimo_cheque_usuario where id_usuario=$id_usuario";
$resultado_ultima=$db->execute($sql)or die($sql);
$sql="select id_chequera,ultimo_cheque_usado,ultimo_cheque from chequera where idbanco=$Banco and cerrada=0";
$resultado_chequera=$db->execute($sql) or die($sql."<br>".$db->errormsg());
$i=1;
while(!$resultado_chequera->EOF)
{
?>
<tr <? if ($resultado_chequera->fields['id_chequera']==$resultado_ultima->fields['ultima_chequera']) echo "bgcolor='green'"; ?>><td><input type="radio" name="radio1" onclick="document.all.Ingreso_Cheque_Numero.value=<? echo $resultado_chequera->fields['ultimo_cheque_usado']+1; ?>;" value="<? echo $resultado_chequera->fields['id_chequera']; ?>" <? if (($resultado_chequera->fields['ultimo_cheque_usado']+1)>$resultado_chequera->fields['ultimo_cheque']) echo "disabled"; ?>></td><td>Chequera Nº: <input type="text" name="ultimo_<?=$i;?>" value="<? echo $resultado_chequera->fields['ultimo_cheque_usado']; ?>" style="border-style:none;background-color:'transparent'; font-weight: bold;" readonly></td>
</tr>
<?
$resultado_chequera->MoveNext();
}
echo "</table>";
echo "</td><tr>\n";
*/
echo "<tr><td align=right><b>Fecha de Vencimiento</b></td>";
echo "<td align=left colspan=3>";
echo "<input type=text size=10 name=Ingreso_Cheque_Fecha_Vencimiento value='$Fecha_Vencimiento' title='Ingrese la fecha de vencimiento del cheque'>";
echo link_calendario("Ingreso_Cheque_Fecha_Vencimiento");
echo "</td></tr>\n";
echo "<tr><td align=right><b>Fecha Débito</b></td>";
echo "<td align=left colspan=3>";
echo "<input type=text size=10 name=Ingreso_Cheque_Fecha_Debito value='$Fecha_Debito' title='Ingrese la fecha de débito del cheque'>";
echo link_calendario("Ingreso_Cheque_Fecha_Debito");
echo "</td></tr>\n";
echo "<tr><td align=right><b>Número</b>\n";
echo "</td><td align=left>";
/*.($Ultimo_Cheque + 1).*/
echo "<input  type=text name=Ingreso_Cheque_Numero size=10 maxlength=50 value=''>&nbsp;";
echo "</td>\n";
/*echo "<td align=right><b>Ultimo Número</b>\n";
echo "</td><td align=left>";
echo "<input disabled type=text name=Ingreso_Cheque_Ultimo_Numero size=10 maxlength=50 value='$Ultimo_Cheque' >&nbsp;";
echo "</td>*/
echo "</tr>\n";
echo "<tr><td align=right><b>Importe</b>\n";
echo "</td><td align=left colspan=3>";
//orden de compra el importe lo divido segun los numeros de pagos que realize

if ($orden_compra){//importe dolares no lo muestro pero me sirve para insertarlo en la base de datos
                   $importe_dolares=$orden_compra['importe_dolares'];
                   $importe=$orden_compra['importe'];
                   echo "<input type=hidden  name=importe_dolares value=$importe_dolares>";
                     if ($parametros['valor_dolar']) {
                                                     $Importe=number_format($importe_dolares,"2",".","");
                                                     }
                                                     else {
                                                     $Importe=number_format($importe,"2",".","");
                                                     }
                   //generamos el comentario del cheque
                   $ordenes_atadas=PM_ordenes($nro_orden);
                   $tam=sizeof($ordenes_atadas);
                   $Comentarios="Pago correspondiente a la/s  Orden/es de Compra nro:";
                   for($i=0;$i<$tam;$i++)
                    $Comentarios.=" ".$ordenes_atadas[$i];

                   }
echo "<input type=text name=Ingreso_Cheque_Importe value='$Importe' $readonly size=10 maxlength=50>&nbsp;";
echo "</td></tr>\n";
echo "<tr><td align=right valign=top><b>Comentarios</b>\n";
echo "</td><td align=left colspan=3>";
echo "<textarea name=Ingreso_Cheque_Comentarios cols=53 rows=3>$Comentarios</textarea>";
echo "</td></tr>\n";
echo "<tr><td align=center colspan=4>\n";
/*
if (!($pagos_restantes)) $disabled="disabled";
                         else $disabled="";
*/
echo "<input type=submit name=Ingreso_Cheque_Guardar value='Guardar'>&nbsp;&nbsp;&nbsp;\n";
//echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
echo "</td>";
if ($orden_compra){
                   $link=encode_link("../ord_compra/ord_compra_pagar.php",array("nro_orden"=>$parametros["nro_orden"]));
                   echo "<td align='center'>";
                   echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='$link';\">\n";
                   echo "</td>";
                   }
echo "</tr>\n";
echo "</table>";
echo "</td></tr>\n";
echo "</table>\n";

?>