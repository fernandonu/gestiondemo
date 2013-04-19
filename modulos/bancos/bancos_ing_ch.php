<?
 /*
Autor: marco_canderle
Modificado por:
$Author: nazabal $
$Revision: 1.78 $
$Date: 2007/06/28 21:43:23 $
*/
// Funciones exclusivas
require_once("../../config.php");

if ($parametros["pagina"]=="ord_pago")
	require_once("../ord_pago/fns.php");
else
	require_once("../ord_compra/fns.php");

require_once("../caja/func.php");
$g_id_retiro_para_caja=610;//para guardar el nro de cuenta de "bancos(retiros para caja)"
$g_pagina_bancos_ing_ch=true;
//el parametro $cuenta_def es para la cuenta por default del proveedor
function cuenta_bancos($cuenta_def=-1) {
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
echo "<select name='cuentas' onchange=g_control_retiro_para_caja();>
       <option value=-1> Seleccionar Concepto y Plan </option>";
      for ($j=0; $j<$cant_resul_con; $j++){
	      $cuenta=$resul_con->fields['concepto']."&nbsp;&nbsp;[ ".$resul_con->fields['plan']." ] ";
  	    echo "<option value='".$resul_con->fields['numero_cuenta']."'";
    	  if($cuenta_def!=-1 && $cuenta_def==$resul_con->fields['numero_cuenta'])
      		echo " selected ";
	      elseif($_POST['cuentas']==$cuenta)
				  echo " selected ";
	  		echo"> $cuenta </option>";
	  		//obtiene el nro de cuenta para Bancos[Retiro para caja]
      	/*if (($resul_con->fields['concepto']=="Bancos")&&($resul_con->fields['plan']=="Retiros Para Caja")){
      		$g_id_retiro_para_caja=$resul_con->fields['numero_cuenta'];
      	}*/
	  		$resul_con->MoveNext();
      }
echo "</select></td></tr>";
}


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

// Ingreso de cheque alternativo
if ($_POST['boton_alternativo']=="cheque alternativo")
{header("location: bancos_ing_ch2.php");
 die();
}

// Cabecera Configuracion requerida
if ($_POST['Ingreso_Cheque_Guardar'])
{ //$_POST[cmd] = "Ingreso_Cheque";
  //include_once("./bancos_data.php");
  $banco = $_POST['Ingreso_Cheque_Banco'];
  $proveedor = $_POST['Ingreso_Cheque_Proveedor'];
  $no_a_la_orden = $_POST['no_a_la_orden'];
  if ($no_a_la_orden != "1") $no_a_la_orden = "0";
  $fecha_e = $_POST['Ingreso_Cheque_Fecha_Emision'];
  $fecha_v = $_POST['Ingreso_Cheque_Fecha_Vencimiento'];
  ///////Diego
  $fecha_v_old = $_POST['Ingreso_Cheque_Fecha_Vencimiento_old'];
  $fecha_p = $_POST['Ingreso_Cheque_Fecha_Debito'];
	$numero = $_POST['Ingreso_Cheque_Numero'];
  $importe = $_POST['Ingreso_Cheque_Importe'];
  $comentarios = $_POST['Ingreso_Cheque_Comentarios'];
  //agrego estas variables para recuperar por post los valores de los select
  //$concepto=$_POST['select_concepto'];
  //$plan=$_POST['select_plan'];
  $cuentas=$_POST['cuentas'];

  //$sql_aux="select numero_cuenta from general.tipo_cuenta where (concepto='$concepto' and plan='$plan')";
  //$resultado_aux=$db->execute($sql_aux)or die($db->errormsg()."<br>".$sql_aux);
  //$nro_cuenta=$resultado_aux->fields['numero_cuenta'];

  if ($parametros['pagina']) {
    include_once("../contabilidad/funciones.php");
	 $page=$parametros['pagina']=="ord_pago"?"../ord_pago/ord_pago_conf_pago.php":"../ord_compra/ord_compra_conf_pago.php";
    //generamos el arreglo para retener los datos de imputacion, y lo enviamos por parametro
	//Asi podremos guardarlos correctamente
    $valores_imputacion=retener_datos_imputacion();

    //voy a ord_compra_conf_pago o ord_pago_conf_pago
    $link=encode_link($page,array(
      "pagina"=>$parametros['pagina'],
      "nro_orden"=>$parametros['nro_orden'],
      "pagina_pago"=>"cheque",
      "id_pago"=>$parametros['id_pago'],
      "valor_dolar"=>$parametros['valor_dolar'],
      "banco"=>$banco,
      "proveedor"=>$proveedor,
      "no_a_la_orden"=>$no_a_la_orden,
      "fecha_e"=>$fecha_e,
      "fecha_v"=>$fecha_v,
      "fecha_v_old"=>$fecha_v_old,
      "fecha_p"=>$fecha_p,
      "numero"=>$numero,
      "importe"=>$importe,
      "simbolo"=>"$",
      "comentarios"=>$comentarios,
      "nro_cuenta"=>$cuentas,
      "id_chequera"=>$_POST['radio1'],
			"Ingreso_Cheque_Numero"=>$_POST['Ingreso_Cheque_Numero'],
			"valores_imputacion"=>$valores_imputacion
    ));
		header("Location:$link") or die();
  }
  else
  {
    echo $parametros['pagina'];
 	  list($d,$m,$a) = explode("/",$fecha_e);
  	if (FechaOk($fecha_e)) {
      $fe_db = "$a-$m-$d " . date("H:m:s");      
   	}else{
      Error("La fecha de Emisión ingresada es inválida");
   	}
    list($d,$m,$a) = explode("/",$fecha_v);
 	  if (FechaOk($fecha_v)) {
      $fv_db = "$a-$m-$d";
    }else{
      Error("La fecha de Vencimiento ingresada es inválida");
		}
	  if ($fecha_p == "") {
      $fp_db = "NULL";
   	}else {
      list($d,$m,$a) = explode("/",$fecha_p);
      if (FechaOk($fecha_p)) {
        $fp_db = "'$a-$m-$d'";
      }else {
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
	  }elseif (!es_numero($importe)) {
      Error("El Importe ingresado es inválido");
   	}
		if ($numero=="")
  		$numero=-1;
	  if ($cuentas==-1){
  	  Error("Falta elegir Concepto y Plan para Cuentas");
	  }
  	$sql="select númeroch,nombrebanco from tipo_banco join cheques using (idbanco) where";
	  $sql.=" cheques.númeroch=$numero and cheques.idbanco=$banco";
  	$resultado=$db->execute($sql)or die($db->errormsg()."<br>".$sql);
  	$cantidad_cheques=$resultado->RecordCount();

  	if ($cantidad_cheques) Error("Ese número de cheque ya existe");
   	if (!$error)
   	{
      $db->StartTrans();
	    //actualizamos chequera
  		$sql="update chequera set ultimo_cheque_usado=".$_POST['Ingreso_Cheque_Numero']." where id_chequera=".$_POST['radio1'];
      $resultado=$db->execute($sql)or die($db->errormsg()."<br>".$sql);
//    $sql_aux="select numero_cuenta from general.tipo_cuenta where (concepto='$concepto' and plan='$plan')";
//    $resultado_aux=$db->execute($sql_aux)or die($db->errormsg()."<br>".$sql_aux);
//    $nro_cuenta=$resultado_aux->fields['numero_cuenta'];
      $sql = "INSERT INTO bancos.cheques ";
      $sql .= "(IdBanco, FechaEmiCh, FechaVtoCh, FechaPrev, FechaDébCh, NúmeroCh, ImporteCh, IdProv, Comentarios, numero_cuenta,no_a_la_orden) ";
      $sql .= "VALUES ($banco,'$fe_db','$fv_db',$fp_db,NULL,$numero,$importe,$proveedor,'$comentarios', $cuentas, $no_a_la_orden)";
      $result = $db->query($sql) or die($db->ErrorMsg()."<br>".$sql);
      $sql = "SELECT id_usuario FROM usuarios WHERE login='".$_ses_user["login"]."'";
      $resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
      $id_usuario=$resultado->fields['id_usuario'];
      $sql = "select * from bancos.ultimo_cheque_usuario where id_usuario=$id_usuario";
      $resultado=$db->execute($sql)or die($db->errormsg()."<br>".$sqls);
      $cantidad_usuarios=$resultado->RecordCount();
      if ($cantidad_usuarios>0){
      	//hago update
        $sql="update bancos.ultimo_cheque_usuario set númeroch=$numero , idbanco=$banco, ultima_chequera=".$_POST['radio1']." where id_usuario=$id_usuario";
        $db->execute($sql) or die($db->errormsg()."<br>".$sql);
      }else{
        //si no hago insert ya que es nuevo
        $sql="insert into bancos.ultimo_cheque_usuario (númeroch,idbanco,id_usuario,ultima_chequera) values($numero,$banco,$id_usuario,".$_POST['radio1'].")";
        $db->execute($sql) or die($db->errormsg()."<br>".$sql);
      }
      $nro_cuenta=$_POST["cuentas"];
			$simbolo="$";
      $tipo_pago="Pago con cheque Nº ".$_POST['Ingreso_Cheque_Numero'].", monto: $simbolo ".formato_money($importe);
		 	//traemos el nombre del proveedor
			$query="select razon_social from proveedor where id_proveedor=$proveedor";
			$prov_n=sql($query) or fin_pagina();

		 	//controla la cuenta por default del proveedor, para saber
		 	//si debe avisar por MAIL que se uso una cuenta que no
		 	//es la que el proveedor tiene por default
		 	cuenta_proveedor_default($proveedor,$nro_cuenta,"$tipo_pago",$prov_n->fields['razon_social']);

      /***********************************************************
     	Llamamos a la funcion de imputar pago
    	************************************************************/
      $pago[]=array();
	    $pago["tipo_pago"]="númeroch";
	    $pago["id_pago"]=$numero;
  	  $pago["id_banco"]=$banco;
    	$id_imputacion=$_POST["id_imputacion"];

	    include_once("../contabilidad/funciones.php");
	    imputar_pago($pago,$id_imputacion,$fecha_e);
      ///////////////////////////////////// GABRIEL //////////////////////////////////////////////
      // si el cheque ingresado es un "retiro para caja", ingreso el monto en la caja						//
      // diaria de la pcia. que corresponda según el usuario que hizo el ingreso (p. ej:				//
      // si el ingreso lo hizo Noe, agrego el monto del cheque en la caja de SL)								//
      // luego aviso por mail de esta acción en caso de que el ingreso debiera haber sido en		//
      // otra caja.																																							//
      ////////////////////////////////////////////////////////////////////////////////////////////
      if ($cuentas==$g_id_retiro_para_caja){
      	//hacer ingreso en caja diaria
      	$g_pagina_bancos_ing_ch=true;
				if (guardar_ie("ingreso", $_POST["g_id_distrito"])<5){//OK!
					//envío de e-mail
					$mailto="corapi@coradir.com.ar, juanmanuel@coradir.com.ar, noelia@pcpower.com.ar";
					$subject="Retiro para caja";
					$contenido="Se hizo un 'ingreso automático' en la caja ".$_POST["g_nombre_distrito"]."\nde un 'retiro para caja' con los siguientes datos:\n";
					$contenido.="Datos del cheque:\nNro.:".$_POST["Ingreso_Cheque_Numero"]."\nBanco:"
						.$_POST["h_Ingreso_Cheque_Banco"]."\nMonto: $".formato_money($_POST["text_monto"])."\nFecha de emisión:"
						.$_POST["text_fecha"]."\n";
					$contenido.="-----------------------------------------\n";
					$contenido.="Operación realizada por: ".$_ses_user["name"]." (".$_ses_user["login"].")\nHora:".date("H:i")."\nFecha:".date("d/m/Y")."\n";
					//echo($mailto."<br>".$contenido."<br>");
					enviar_mail($mailto, $subject, $contenido, "", "", "",0);
				}else die("No se pudo hacer el ingreso en la caja de hoy!");
      }
      ////////////////////////////////////////////////////////////////////////////////////////////
     $db->CompleteTrans();
     Aviso("Los datos se ingresaron correctamente");
   	}
	}//del else que va a la pagina
} //del if principal

echo $html_header;
//recupero los datos de la orden de compra
?>
<script>
//////////////// GABRIEL ///////////////////////////
function g_control_retiro_para_caja(){
	if (document.all.cuentas.options[document.all.cuentas.selectedIndex].value==<?=$g_id_retiro_para_caja?>){
		document.all.tabla_aviso.style.display='inline';//retiro para caja
	}else document.all.tabla_aviso.style.display='none';
}

function g_copiar_datos(){
	document.all.text_item.value="Cheque nro. "+document.all.Ingreso_Cheque_Numero.value+" (Banco: "+document.all.Ingreso_Cheque_Banco.options[document.all.Ingreso_Cheque_Banco.selectedIndex].text+")";
	document.all.text_fecha.value=document.all.Ingreso_Cheque_Fecha_Emision.value;
	document.all.text_monto.value=document.all.Ingreso_Cheque_Importe.value;
	document.all.observaciones.value="Inserción automática en caja. Comentario: "+document.all.Ingreso_Cheque_Comentarios.value;
	document.all.h_Ingreso_Cheque_Banco.value=document.all.Ingreso_Cheque_Banco.options[document.all.Ingreso_Cheque_Banco.selectedIndex].text;
}
////////////////////////////////////////////////////
function control_campos(){
if (document.all.Ingreso_Cheque_Banco.value==-1){
   alert("Falta seleccionar Banco");
   return false;}
if (document.all.Ingreso_Cheque_Proveedor.value==""){
   alert("Falta seleccionar Proveedor");
   return false;}
if (document.all.Ingreso_Cheque_Fecha_Vencimiento.value==""){
   alert("Falta seleccionar Fecha de Vencimiento");
   return false;}
if (document.all.Ingreso_Cheque_Comentarios.value==""){
   alert("Falta ingresar Comentario");
   return false;}
if (document.all.Ingreso_Cheque_Importe.value==""){
   alert("Falta ingresar Importe");
   return false;}
if (document.all.Ingreso_Cheque_Numero.value==""){
   alert("Falta seleccionar Numero de Cheque");
   return false;}
if (document.all.cuentas.value==-1){
   alert("Falta seleccionar Concepto y Plan");
   return false;}
if(!control_campos_imputacion())
 return false;

return true;
}
</script>
<?
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
    /////////Diego
    $Ing_Fecha_Vencimiento_old = $_POST['Ingreso_Cheque_Fecha_Vencimiento_old'];
    $Ing_Fecha_Debito = $_POST['Ingreso_Cheque_Fecha_Debito'];
    $Ing_Importe = $_POST['Ingreso_Cheque_Importe'];
    $Ing_Comentarios = $_POST['Ingreso_Cheque_Comentarios'];
	$Ing_Numero = $_POST['Ingreso_Cheque_Numero'];
    echo "<form action=bancos_ing_ch.php method=post>\n";
    echo "<input type=hidden name=Ingreso_Cheque_Proveedor value=$id_prov>\n";
    echo "<input type=hidden name=Ingreso_Cheque_Banco value='$Ing_Banco'>\n";
    echo "<input type=hidden name=Ingreso_Cheque_Fecha_Emision value='$Ing_Fecha_Emision'>\n";
    echo "<input type=hidden name=Ingreso_Cheque_Fecha_Vencimiento value='$Ing_Fecha_Vencimiento'>\n";
    ////////////////////Diego
    echo "<input type=hidden name=Ingreso_Cheque_Fecha_Vencimiento_old value='$Ing_Fecha_Vencimiento_old'>\n";
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
	/////////////Diego
	$Fecha_Vencimiento_old = $_POST['Ingreso_Cheque_Fecha_Vencimiento_old'];
	$Fecha_Debito = $_POST['Ingreso_Cheque_Fecha_Debito'];
	$Importe = $_POST['Ingreso_Cheque_Importe'];
	$Comentarios = $_POST['Ingreso_Cheque_Comentarios'];
	$Numero = $_POST['Ingreso_Cheque_Numero'];
	echo "<br><form action=bancos_ing_ch.php method=post>\n";
	//echo "<input type=hidden name=mode value=data>\n";
	//echo "<input type=hidden name=cmd value=Nuevo_Proveedor>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Banco value='$Banco'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Fecha_Emision value='$Fecha_Emision'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Fecha_Vencimiento value='$Fecha_Vencimiento'>\n";
	/////Diego
	echo "<input type=hidden name=Ingreso_Cheque_Fecha_Vencimiento_old value='$Fecha_Vencimiento_old'>\n";
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
	//////Diego
	$Fecha_Vencimiento_old = $_POST['Ingreso_Cheque_Fecha_Vencimiento_old'];
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
$link=encode_link("./bancos_ing_ch.php",array("pagina"=>$parametros['pagina'],
                                              "nro_orden"=>$parametros['nro_orden'],
                                              "valor_dolar"=>$parametros['valor_dolar'],
                                              "id_pago"=>$parametros['id_pago']));

echo "<script language='javascript' src='../../lib/popcalendar.js'> </script>\n";
?>
<script>
function habilitar_campos(){
document.form1.Ingreso_Cheque_Numero.readOnly=false;
}

//Según el proveedor elegido, si tiene
//cuenta por default, setea el select de cuentas, con la cuenta por
//default del proveedor
function seteo_cuenta(objeto)
{var tam_cuentas=document.all.cuentas.options.length;
 var i;
 var indice=-1;
 if(objeto!="")//si tiene cuenta por default, seteamos el select correspondiente
 {
    //recorremos el select de cuentas para ver cuál es la posicion de la
    //cuenta que es default del proveedor elegido. Asi seteamos con ese
    //indice al select de cuentas
    for(i=0;i<tam_cuentas;i++)
    {
     if(document.all.cuentas.options[i].value==objeto)
     {indice=i;
      i=tam_cuentas;
     }
    }

 	document.all.cuentas.selectedIndex=indice;
 }
 if(indice==-1)
  document.all.cuentas.selectedIndex=0;

}//de function seteo_cuenta(objeto)

</script>
<script src="../../lib/NumberFormat150.js"></script>
<?
echo "<form name=form1 action='$link' method=post>\n";
////////////////////////////////////// GABRIEL /////////////////////////////////////////////////
$consulta="select id_usuario, idbanco, númeroch, login, d.nombre as nombre_distrito, id_distrito, id_entidad, ent.nombre as nombre_entidad, id_tipo_ingreso,
      			nombre_tipo_ingreso, id_moneda, nombre_moneda, id_cuenta_ingreso, nombre_cuenta_ingreso
					from bancos.ultimo_cheque_usuario
						join sistema.usuarios using (id_usuario)
						join licitaciones.distrito d on (id_distrito=pcia_ubicacion)
						join (select id_entidad, id_distrito, nombre
							from licitaciones.entidad where nombre ilike '%".CORADIR."%')as ent using (id_distrito),
						(select id_tipo_ingreso, nombre as nombre_tipo_ingreso
							from caja.tipo_ingreso where nombre ilike '%otros ingresos%')as s_tipo_ingreso,
						(select id_moneda, nombre as nombre_moneda
							from licitaciones.moneda where nombre ilike '%pesos%')as s_moneda,
						(select id_cuenta_ingreso, nombre as nombre_cuenta_ingreso
							from caja.tipo_cuenta_ingreso where nombre ilike '%retiro de bancos%')as s_tipo_cuenta_ingreso
					where login = '".$_ses_user["login"]."'";
$rta_consulta=sql($consulta, "C570") or fin_pagina();
if ($rta_consulta->recordCount()!=1){
	$consulta="select id_usuario, login, d.nombre as nombre_distrito, id_distrito, id_entidad, ent.nombre as nombre_entidad, id_tipo_ingreso,
      			nombre_tipo_ingreso, id_moneda, nombre_moneda, id_cuenta_ingreso, nombre_cuenta_ingreso
  from sistema.usuarios
		join licitaciones.distrito d on (id_distrito=pcia_ubicacion)
		left join (select id_entidad, id_distrito, nombre
			from licitaciones.entidad where nombre ilike '%".CORADIR."%')as ent using (id_distrito),
		(select id_tipo_ingreso, nombre as nombre_tipo_ingreso
			from caja.tipo_ingreso where nombre ilike '%otros ingresos%')as s_tipo_ingreso,
		(select id_moneda, nombre as nombre_moneda
			from licitaciones.moneda where nombre ilike '%pesos%')as s_moneda,
		(select id_cuenta_ingreso, nombre as nombre_cuenta_ingreso
			from caja.tipo_cuenta_ingreso where nombre ilike '%retiro de bancos%')as s_tipo_cuenta_ingreso
		where login ='".$_ses_user["login"]."'";
	$rta_consulta=sql($consulta, "c583") or fin_pagina();
}//elseif ($rta_consulta->recordCount()!=1) die("ERROR: no se pudieron obtener los parámetros para hacer el asiento en la caja.<br>$consulta");
?>
	<table align="center" width="100%" style="display:'none'" id="tabla_aviso">
		<tr><td align="center">
			<font color="Red"><h2>CUIDADO: EL MONTO DEL CHEQUE SE AGREGAR&Aacute; AUTOM&Aacute;TICAMENTE EN LA CAJA DE HOY!</h2></font>
		</td></tr>
	</table>
	<input type="hidden" name="select_entidad" value="<?=(($_POST["select_entidad"])?$_POST["select_entidad"]:$rta_consulta->fields["id_entidad"])?>">
	<input type="hidden" name="select_tipo" value="<?=(($_POST["select_tipo"])?$_POST["select_tipo"]:$rta_consulta->fields["id_tipo_ingreso"])?>">
	<input type="hidden" name="text_monto" value="<?=(($_POST["text_monto"])?$_POST["text_monto"]:$_POST["Ingreso_Cheque_Importe"])?>" onchange="this.value=this.value.replace(',','.'); control_numero(this, 'Monto préstamo');">
	<input type="hidden" name="observaciones" value="<?=(($_POST["observaciones"])?$_POST["observaciones"]:"Inserción automática en caja. Comentario: ".$rta_consulta->fields["Ingreso_Cheque_Comentarios"])?>">
	<input type="hidden" name="text_item" value="<?=(($_POST["text_item"])?$_POST["text_item"]:"Cheque nro. ".$_POST["Ingreso_Cheque_Numero"]." (Banco: ".$_POST["Ingreso_Cheque_Banco"].")")?>">
	<input type="hidden" name="select_cuenta" value="<?=(($_POST["select_cuenta"])?$_POST["select_cuenta"]:$rta_consulta->fields["id_cuenta_ingreso"])?>">
	<input type="hidden" name="text_fecha" value="<?=(($_POST["text_fecha"])?$_POST["text_fecha"]:$_POST["Ingreso_Cheque_Fecha_Emision"])?>">
	<input type="hidden" name="g_id_distrito" value="<?=(($_POST["g_id_distrito"])?$_POST["g_id_distrito"]:$rta_consulta->fields["id_distrito"])?>">
	<input type="hidden" name="g_nombre_distrito" value="<?=(($_POST["g_nombre_distrito"])?$_POST["g_nombre_distrito"]:$rta_consulta->fields["nombre_distrito"])?>">
	<input type="hidden" name="select_moneda" value="<?=(($_POST["select_moneda"])?$_POST["select_moneda"]:$rta_consulta->fields["id_moneda"])?>">
	<input type="hidden" name="h_Ingreso_Cheque_Banco" value="<?=(($_POST["h_Ingreso_Cheque_Banco"])?$_POST["h_Ingreso_Cheque_Banco"]:"")?>">
<?
/////////////////////////////////////////////////////////////////////////////////////////////////
echo "<table align=center cellpadding=2 cellspacing=0 border=1 bordercolor='$bgcolor3' >\n";
echo "<tr bordercolor='#000000'><td id=mo align=center>Ingreso de Cheques</td></tr>";
echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0 bgcolor=$bgcolor_out>";
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
echo "<tr><td align=right><b>A nombre de</b></td>";
echo "<td align=left colspan=3>";
echo "<select name=Ingreso_Cheque_Proveedor onclick='seteo_cuenta(this.options[this.options.selectedIndex].id); g_control_retiro_para_caja();'>\n";
echo "<option value='' selected></option>\n";
$sql = "SELECT proveedor.id_proveedor,razon_social,numero_cuenta FROM general.proveedor left join cuentas on (proveedor.id_proveedor=cuentas.id_proveedor and es_default=1) order by razon_social";
$result = $db->query($sql) or die($db->ErrorMsg());
//orden de compra
if ($orden_compra) {
                   $Proveedor=$orden_compra["proveedor"];
                   $dias_pago=$orden_compra["dias"];
                   $Fecha_Vencimiento_old=$Fecha_Vencimiento;
                   if ($Fecha_Vencimiento==""){
                                               $Fecha_Vencimiento= date("d/m/Y",mktime(0,0,0,date("m"),date("d")+$dias_pago,date("Y")));
                                               ///////Diego
                                               $Fecha_Vencimiento_old= date("d/m/Y",mktime(0,0,0,date("m"),date("d")+$dias_pago,date("Y")));
                                               }//del if de fecha de vencimiento
                   }

while ($fila = $result->fetchrow()) {
	echo "<option id='".$fila['numero_cuenta']."' value='".$fila['id_proveedor']."'";
	if ($fila['id_proveedor'] == "$Proveedor") echo " selected";
	echo ">".$fila['razon_social']."</option>\n";
}
echo "</select></td></tr>\n";
echo "<tr><td align=right><b>No a la orden</b></td>";
echo "<td colspan=3><input type=checkbox name=no_a_la_orden value='1'></td></tr>";
echo "<tr><td align=right><b>Fecha de Emisión</b></td>";
echo "<td align=left>";
echo "<input type=text size=10 name=Ingreso_Cheque_Fecha_Emision value='$Fecha_Emision' title='Ingrese la fecha de emisión del cheque'>";
echo link_calendario("Ingreso_Cheque_Fecha_Emision");
echo "</td>\n";
echo "<td colspan=2 align=right>\n";
// echo "<input type=submit name=Ingreso_Cheque_Nuevo_Proveedor value='Nuevo Proveedor'>\n";
echo "<table class='bordes'>";
echo"<tr bgcolor=$bgcolor3><td colspan=2 align=center><b>Número a utilizar</td></tr>";
$sql = "SELECT id_usuario FROM usuarios WHERE login='".$_ses_user["login"]."'";
$resultado_usuario=$db->execute($sql) or die($sql);
$id_usuario=$resultado_usuario->fields['id_usuario'];
$sql = "select ultima_chequera from bancos.ultimo_cheque_usuario where id_usuario=$id_usuario";
$resultado_ultima=$db->execute($sql)or die($sql);
if (($_ses_user["login"]!="vanesa") && ($_ses_user["login"]!="graciela") && ($_ses_user["login"]!="bianchi"))
{
$sql="select id_chequera,ultimo_cheque_usado,ultimo_cheque from chequera where idbanco=$Banco and cerrada=0";
$resultado_chequera=$db->execute($sql) or die($sql."<br>".$db->errormsg());
}
else //caso para graciela y vanesa
{
$sql="select chequera.id_chequera,ultimo_cheque_usado,ultimo_cheque from chequera join log_chequera using(id_chequera) where log_chequera.tipo='apertura' and log_chequera.usuario='Graciela Tedeschi' and chequera.idbanco=$Banco and chequera.cerrada=0";
$resultado_chequera=$db->execute($sql) or die($sql."<br>".$db->errormsg());
}
$i=1;
while(!$resultado_chequera->EOF)
{
?>
<tr <? if ($resultado_chequera->fields['id_chequera']==$resultado_ultima->fields['ultima_chequera']) echo "bgcolor='green' title='Ultima chequera utilizada por usted'"; ?> <? if (($resultado_chequera->fields['ultimo_cheque_usado']+1)>$resultado_chequera->fields['ultimo_cheque']) echo "bgcolor='red' title='Debería cerrar esta chequera'"; ?> ><td><input type="radio" name="radio1" onclick="document.all.Ingreso_Cheque_Numero.value=<? echo $resultado_chequera->fields['ultimo_cheque_usado']+1; ?>;" value="<? echo $resultado_chequera->fields['id_chequera']; ?>" <? if (($resultado_chequera->fields['ultimo_cheque_usado']+1)>$resultado_chequera->fields['ultimo_cheque']) echo "disabled"; ?>></td><td>Chequera Nº: <input type="text" name="ultimo_<?=$i;?>" value="<? echo ($resultado_chequera->fields['ultimo_cheque_usado']+1); ?>" style="border-style:none;background-color:'transparent'; font-weight: bold;" readonly></td>
</tr>
<?
$resultado_chequera->MoveNext();
}
echo "</table>";
echo "</td><tr>\n";
echo "<tr><td align=right><b>Fecha de Vencimiento</b></td>";
echo "<td align=left >";
echo "<input type=text size=10 name=Ingreso_Cheque_Fecha_Vencimiento value='$Fecha_Vencimiento' title='Ingrese la fecha de vencimiento del cheque'>";
///////Diego
echo "<input type=hidden size=10 name=Ingreso_Cheque_Fecha_Vencimiento_old value='$Fecha_Vencimiento_old' >";
echo link_calendario("Ingreso_Cheque_Fecha_Vencimiento");
echo "</td>";
echo "<td colspan=2><table cellpadding=2 cellspacing=0 border=1 bordercolor='$bgcolor3'>";
if($Proveedor)
{
 //traemos la cuenta por default del proveedor, si es que tiene
 $query="select numero_cuenta from cuentas where id_proveedor=$Proveedor and es_default=1";
 $cuentas=sql($query) or fin_pagina();
 $cuenta_def=$cuentas->fields['numero_cuenta'];
}
cuenta_bancos($cuenta_def); //llamo a la funcion que hace la tabla y las consultas para
?>
<script>g_control_retiro_para_caja();</script>
<?
//traer los conceptos y los planes
echo "</table></td>";
echo "</tr>\n";
echo "<tr><td align=right><b>Fecha Débito</b></td>";
echo "<td align=left colspan=3>";
echo "<input type=text size=10 name=Ingreso_Cheque_Fecha_Debito value='$Fecha_Debito' title='Ingrese la fecha de débito del cheque'>";
echo link_calendario("Ingreso_Cheque_Fecha_Debito");
echo "</td></tr>\n";
echo "<tr><td align=right><b>Número</b>\n";
echo "</td><td align=left>";
/*.($Ultimo_Cheque + 1).*/
echo "<input  type=text name=Ingreso_Cheque_Numero size=10 maxlength=50 readonly value='' >&nbsp;";
echo "</td>\n";

/*echo "<td align=right><b>Ultimo Número</b>\n";
echo "</td><td align=left>";
echo "<input disabled type=text name=Ingreso_Cheque_Ultimo_Numero size=10 maxlength=50 value='$Ultimo_Cheque' >&nbsp;";
echo "</td>";*/

echo "<td align=right><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>\n";
echo "</td><td align=left>";
echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>";
echo "</td>";
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
                   $Comentarios="Pago correspondiente a la/s  Orden/es de ";
                   $Comentarios.=$parametros['pagina']=="ord_pago"?"Pago nro:":"Compra nro:";
                   for($i=0;$i<$tam;$i++)
                    $Comentarios.=" ".$ordenes_atadas[$i];

                   }

include_once("../contabilidad/funciones.php");
echo "<input type=text name=Ingreso_Cheque_Importe value='$Importe' $readonly size=10 maxlength=50 onchange='this.value=this.value.replace(\",\",\".\"); control_numero(this, \"Monto\");setear_montos_imputacion(\"númeroch\");'>&nbsp;";
echo "</td></tr>\n";
echo "<tr><td align=right valign=top><b>Comentarios</b>\n";
echo "</td><td align=left colspan=3>";
echo "<textarea name=Ingreso_Cheque_Comentarios cols=53 rows=3>$Comentarios</textarea>";
echo "</td></tr>\n";
?>
<tr>
 <td>
  &nbsp;
 </td>
 <td colspan="3">
 <?

   tabla_imputacion("",$Importe);
 ?>
  <input type="hidden" name="id_imputacion" value="<?=$id_imputacion?>">
 </td>
</tr>
<?
echo "<tr><td align=center colspan=4>\n";
/*
if (!($pagos_restantes)) $disabled="disabled";
                         else $disabled="";
*/
echo "<input type=submit name=Ingreso_Cheque_Guardar value='Guardar' onclick='g_copiar_datos(); return control_campos()'>&nbsp;&nbsp;&nbsp;\n";
//echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
echo "</td>";
echo "<td align='right'>";
if(permisos_check("inicio","ingreso_cheque"))
{
?>
<input type="button" name="boton_alternativo" value="cheque alternativo" onclick="habilitar_campos();">
<?
}
if ($orden_compra) {

     $page=$parametros['pagina']=="ord_pago"?"../ord_pago/ord_pago_pagar.php":"../ord_compra/ord_compra_pagar.php";
     $link=encode_link($page,array("nro_orden"=>$parametros["nro_orden"],"pagina_viene"=>"ord_compra"));?>
    <input type=button name=Volver value='Volver' title="<?=$parametros['pagina']=="orden_de_compra"?"Volver a la Orden de compra":"Volver a la pagina anterior"?>" OnClick="window.location='<?=$link?>'" class="estilo_boton">
<?
 }
echo "</td>";
echo "</tr>\n";
echo "</table>";
echo "</td></tr>\n";
echo "</table>\n";

?>
