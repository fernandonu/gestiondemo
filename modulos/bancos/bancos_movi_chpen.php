<?
/*
$Author: mari $
$Revision: 1.61 $
$Date: 2006/09/07 18:58:34 $

*/
require_once("../../config.php");

// para el select del concepto y plan
function cuenta_bancos($nro_cuenta) {
	global $db;
	global $concepto_cuenta,$parametros,$download;
	$tipo_valor='base';
echo "
       <tr>
	    <td align=right><b>Concepto y Plan </b></td>

        <td align=left>";
//query para traer toda la tabla tipo_cuenta
$con="select * from general.tipo_cuenta order by concepto, plan";
$resul_con=$db->Execute($con) or die ($db->ErrorMsg()."<br>".$con);
$cant_resul_con=$resul_con->RecordCount();
echo "<select name='cuentas'>
       <option value=-1> Seleccionar Concepto y Plan </option>";
      for ($j=0; $j<$cant_resul_con; $j++){
      $numero_cuenta=$resul_con->fields['numero_cuenta'];
      $cuenta=$resul_con->fields['concepto']."&nbsp;&nbsp;[ ".$resul_con->fields['plan']." ] ";
      echo "<option value='".$resul_con->fields['numero_cuenta']."'";
      if($nro_cuenta==$numero_cuenta)
	  echo " selected ";
	  echo"> $cuenta </option>";
	  $resul_con->MoveNext();}
echo "</select></td></tr>";
}

$_POST["idbanco"] = $_POST["Mov_Cheques_Pendientes_Banco"];

if (!$_POST["Entre_Fechas"] && $_POST["form_busqueda"]) $_POST["Entre_Fechas"]='5' ;//para que retenga el chk_fecha

variables_form_busqueda("movi_chpen",array("idbanco" => "",
										   "Entre_Fechas" => "",
										   "Entre_Fechas_Campo" => "",
										   "Entre_Fechas_Desde" => "",
										   "Entre_Fechas_Hasta" => ""));

$Banco = $idbanco or $Banco = 4;

if ($parametros["idbanco"]) $Banco=$parametros["idbanco"];
$datos = array(
               "cheques" => array(
                  "titulo" => "Cheques Debitados",
               "sql" => "SELECT bancos.cheques.FechaVtoCh,
                            bancos.cheques.FechaDébCh,
                            bancos.cheques.FechaEmiCh,
                            bancos.cheques.FechaPrev,
                            bancos.cheques.NúmeroCh,
                            bancos.cheques.ImporteCh,
                            general.proveedor.razon_social
                            FROM bancos.cheques
                            INNER JOIN general.proveedor
                            ON bancos.cheques.IdProv=general.proveedor.id_proveedor ",
               "where" => "bancos.cheques.FechaDébCh IS NOT NULL
                            AND bancos.cheques.IdBanco=$Banco ",
               "fecha" => array (
                           "bancos.cheques.FechaVtoCh" => "Vencimiento",
                              "bancos.cheques.FechaPrev" => "A debitar",
                           "bancos.cheques.FechaDébCh" => "Débito",
                           "bancos.cheques.FechaEmiCh" => "Emisión"
                          ),
               "sumar" => array(
                              "bancos.cheques.ImporteCh" => "bancos.cheques"
                          )
           ),
           "depositos" => array(
                  "titulo" => "Depósitos Acreditados",
                "sql" => "SELECT bancos.depósitos.IdDepósito,
                            bancos.tipo_depósito.TipoDepósito,
                            bancos.depósitos.FechaDepósito,
                            bancos.depósitos.ImporteDep,
                            bancos.depósitos.FechaCrédito
                            FROM bancos.depósitos
                            INNER JOIN bancos.tipo_depósito
                            ON bancos.depósitos.IdTipoDep=bancos.tipo_depósito.IdTipoDep ",
               "where" => "bancos.depósitos.FechaCrédito IS NOT NULL
                            AND bancos.depósitos.IdBanco=$Banco",
                 "fecha" => array(
                            "bancos.depósitos.FechaDepósito" => "Depósito",
                            "bancos.depósitos.FechaCrédito" => "Crédito"
                          ),
               "sumar" => array(
                              "bancos.depósitos.ImporteDep" => "bancos.depósitos"
                          )
           ),
           "debitos" => array(
                "titulo" => "Débitos",
               "sql" => "SELECT bancos.débitos.IdDébito,
                              bancos.tipo_débito.TipoDébito,
                           bancos.débitos.FechaDébito,
                           bancos.débitos.ImporteDéb
                            FROM bancos.débitos
                            INNER JOIN bancos.tipo_débito ON
                            bancos.débitos.IdTipoDéb=bancos.tipo_débito.IdTipoDéb",
                "where" => "IdBanco=$Banco",
               "fecha" => array(
                           "bancos.débitos.FechaDébito" => "Débito"
                          ),
               "sumar" => array(
                              "bancos.débitos.ImporteDéb" => "bancos.débitos"
                          )
           ),
           "tarjetas" => array(
                  "titulo" => "Tarjetas Acreditadas",
                "sql" => "SELECT bancos.tarjetas.IdTarjeta,
                            bancos.tipo_tarjeta.TipoTarjeta,
                            bancos.tarjetas.FechaDepTar,
                            bancos.tarjetas.ImporteDepTar,
                            bancos.tarjetas.FechaCrédTar,
                            bancos.tarjetas.ImporteCrédTar
                            FROM bancos.tarjetas
                            INNER JOIN bancos.tipo_tarjeta
                            ON bancos.tarjetas.idtipotar=bancos.tipo_tarjeta.idtipotar",
               "where" => "bancos.tarjetas.fechacrédtar IS NOT NULL
                            AND bancos.tarjetas.idbanco=$Banco",
               "fecha" => array(
                            "bancos.tarjetas.FechaDepTar" => "Depósito",
                            "bancos.tarjetas.FechaCrédTar" => "Crédito"
                          ),
               "sumar" => array(
                              "bancos.tarjetas.ImporteCrédTar" => "bancos.tarjetas"
                          )
           )
);


// Contenido
if ($_POST["Modificacion_Cheque_Guardar"]=='Guardar') {
    $fecha_debito = $_POST['Modificacion_Cheque_Fecha_Debito'];
    $numero_nuevo = $_POST['Modificacion_Cheque_Numero'];
    $numero=$_POST['Modificacion_Cheque_Numero_Old']; //numero del cheque antes de cambiar
    $importe = $_POST['Ingreso_Cheque_Importe'];
    $comentarios = $_POST['Modificacion_Cheque_Comentarios'];
    $idbanco1 = $_POST['Modificacion_id_banco_Old'];
    $idbanco = $_POST['Modificacion_Cheque_IdBanco'];
    //$idbanco = $_POST['Modificacion_Cheque_IdBanco'];
    $proveedor=$_POST['Modificacion_Cheque_IdProveedor'];
    $fech_emis=$_POST['Modificacion_Cheque_Fecha_Emision'];
    $fech_venc=$_POST['Modificacion_Cheque_Fecha_Vencimiento'];
    //recupero concepto y plan
	$nro_cuenta=$_POST['cuentas'];

    if ($fecha_debito == "") {
        $fecha_debito = "NULL";
    }
    else {
        //list($d,$m,$a) = explode("/",$fecha_debito);
        if (FechaOk($fecha_debito)) {
            $fecha_debito = "'".Fecha_db($fecha_debito)."'";
        }
        else {
            Error("La fecha de Débito ingresada es inválida");
            $error=1;
        }
    }
    if ($importe == "") {
        Error("Falta ingresar el Importe");
         $error=1;
    }
    elseif (!es_numero($importe)) {
        Error("El Importe ingresado es inválido");
        $error=1;
    }

   if ($fech_emis == "") {
           Error("la fecha de emision no puede ser vacia");
           $error=1;
        }
    else {
          if (FechaOk($fech_emis)) {
            $fech_emis = "'".Fecha_db($fech_emis)."'";
           }
           else {
            Error("La fecha de emision ingresada es inválida");
            $error=1;
        }

     }

     if ($fech_venc == "") {
           Error("la fecha de vencimiento no puede ser vacia");
           $error=1;
        }
    else {
          if (FechaOk($fech_venc)) {
            $fech_venc = "'".Fecha_db($fech_venc)."'";
           }
           else {
            Error("La fecha de emision ingresada es inválida");
            $error=1;
        }

     }

   if ($numero_nuevo== "") {
   	    $numero_nuevo="NULL";
        Error("Falta ingresar el Número de cheque");
        $error=1;
    }
 // $idbanco;  //banco modificado
 //$idbanco1;  //banco anterior al cambio
//control si cambio banco y numero
  if (($idbanco!=$idbanco1) &&($numero!=$numero_nuevo)){
   $query_control="select NúmeroCh from cheques where NúmeroCh=$numero_nuevo and idbanco=$idbanco";
    $result = $db->execute($query_control) or die($query_control);
    $rs = $db->query($query_control);
    if ($rs->RecordCount()!=0) {
    Error("El Número de cheque:$numero_nuevo ya existe para el banco seleccionado");
    $error=1;
  }
  }


 elseif ($idbanco!=$idbanco1) {
   //controla si cambio el banco
    $query_control="select NúmeroCh from cheques where NúmeroCh=$numero_nuevo and idbanco=$idbanco";
    $result = $db->execute($query_control) or die($query_control);
    $rs = $db->query($query_control);
    if ($rs->RecordCount()!=0) {
    Error("El Número de cheque:$numero_nuevo ya existe para el banco seleccionado");
    $error=1;
	}
 }
 elseif ($numero!=$numero_nuevo) {
   //controla si cambio el numero
    $query_control="select NúmeroCh from cheques where NúmeroCh=$numero_nuevo and idbanco=$idbanco1";
    $result = $db->execute($query_control) or die($query_control);
    $rs = $db->query($query_control);
    if ($rs->RecordCount()!=0) {
    Error("El Número de cheque: $numero_nuevo ya existe para el banco seleccionado");
    $error=1;
	}
 }

    if (!$error) {
        $db->StartTrans();
        $sql="select * from ordenes_pagos where Númeroch=$numero AND idbanco=$idbanco1 ";
        $resultado=$db->execute($sql) or die($sql);
        $id_pago=$resultado->fields['id_pago'];
        $cantidad_ordenes=$resultado->RecordCount();

        $sql="select * from ultimo_cheque_usuario where Númeroch=$numero AND idbanco=$idbanco1 ";
        $resultado=$db->execute($sql) or die($sql);
        $id_ultimo_cheque=$resultado->fields['id_ultimo_cheque'];
        $cantidad_ultimo_cheque=$resultado->RecordCount();

        if ($cantidad_ordenes)
                  {
                   $sql ="UPDATE compras.ordenes_pagos SET ";
                   $sql.="NúmeroCh=null,idbanco=null where Númeroch=$numero AND idbanco=$idbanco1 AND id_pago=$id_pago";
                   $result = $db->execute($sql) or die($sql."<br>".$db->errormsg());
                  }
       if ($cantidad_ultimo_cheque)
                  {
                   $sql ="UPDATE bancos.ultimo_cheque_usuario  SET ";
                   $sql.="NúmeroCh=null,idbanco=null where Númeroch=$numero AND idbanco=$idbanco1 AND id_ultimo_cheque=$id_ultimo_cheque";
                   $result = $db->execute($sql) or die($sql."<br>".$db->errormsg());
                   }

        $sql = "UPDATE bancos.cheques SET ";
        $sql .= "FechaDébCh=$fecha_debito,";
        $sql .= "ImporteCh=$importe,";
        $sql .= "Comentarios='$comentarios',";
        $sql .= "idprov=$proveedor,";
        $sql .= "NúmeroCh=$numero_nuevo,";
        $sql .= "fechaemich=$fech_emis,";
        $sql .= "fechavtoch=$fech_venc,";
        $sql .= "idbanco=$idbanco, ";
        $sql .= "numero_cuenta=$nro_cuenta ";
        $sql .= "WHERE NúmeroCh=$numero AND idbanco=$idbanco1";
        $result = $db->execute($sql) or die($sql."<br>".$db->errormsg());

        if ($cantidad_ordenes)
                      {
                       $sql ="UPDATE compras.ordenes_pagos SET ";
                       $sql.="NúmeroCh=$numero_nuevo,idbanco=$idbanco where id_pago=$id_pago ";
                       $result = $db->execute($sql) or die($sql."<br>".$db->errormsg());
                       }
       if ($cantidad_ultimo_cheque)
                      {
                      $sql ="UPDATE bancos.ultimo_cheque_usuario  SET ";
                      $sql.="NúmeroCh=$numero_nuevo,idbanco=$idbanco where id_ultimo_cheque=$id_ultimo_cheque";
                      $result = $db->execute($sql) or die($sql."<br>".$db->errormsg());
                      }


        /***********************************************************
	     Llamamos a la funcion de imputar pago
	    ************************************************************/
        $pago[]=array();
	    $pago["tipo_pago"]="númeroch";
	    $pago["id_pago"]=$numero;
	    $pago["id_banco"]=$idbanco1;
	    $id_imputacion=$_POST["id_imputacion"];

	    include_once("../contabilidad/funciones.php");
	    imputar_pago($pago,$id_imputacion);
	    
        $db->CompleteTrans();
        if ($_POST['volver_cheque']==1)
         header("location: control_cheques.php");
        Aviso("Los datos se ingresaron correctamente");
        }
else {
      $_POST['Modificar']=1;
      $_POST["Modificar_Cheque_Numero"]=$numero;
      $_POST['id_banco']=$idbanco1;
}
    echo "</center></form>\n";
}

if ($_POST["Modificar"] || $parametros['pagina']=="mail" || $parametros['pagina']=="imputaciones") {
	echo $html_header;
	if ($parametros['pagina']=="mail" || $parametros['pagina']=="imputaciones")
	  $mod_numero = $parametros["Modificar_Cheque_Numero"];
	else
	  $mod_numero = $_POST["Modificar_Cheque_Numero"];

	if ($parametros['pagina']=="mail" || $parametros['pagina']=="imputaciones")
	  $id_banco_nuevo=$parametros['banco'];
	else
	  $id_banco_nuevo=$_POST['id_banco'];

	if (es_numero($mod_numero)) {
		$sql = "SELECT bancos.tipo_banco.NombreBanco,";
		$sql .= "bancos.cheques.IdBanco,";
		$sql .= "general.proveedor.razon_social,";
		$sql .= "bancos.cheques.FechaEmiCh,";
		$sql .= "bancos.cheques.FechaVtoCh,";
		$sql .= "bancos.cheques.FechaDébCh,";
		$sql .= "bancos.cheques.ImporteCh,";
		$sql .= "bancos.cheques.Comentarios, ";
	    $sql .= "bancos.cheques.numero_cuenta ";
		$sql .= "FROM (bancos.cheques ";
		$sql .= "INNER JOIN bancos.tipo_banco ";
		$sql .= "ON bancos.cheques.IdBanco = bancos.tipo_banco.IdBanco) ";
		$sql .= "INNER JOIN general.proveedor ";
		$sql .= "ON bancos.cheques.IdProv = general.proveedor.id_proveedor ";
		$sql .= "WHERE bancos.cheques.NúmeroCh=$mod_numero AND cheques.idbanco=$id_banco_nuevo";
        $sql .= " AND tipo_banco.activo=1";
        $result = $db->Execute($sql) or die($db->ErrorMsg()." - " . $sql);
		list($mod_banco,
		$mod_idbanco,
		$mod_proveedor,
		$mod_fecha_e,
		$mod_fecha_v,
		$mod_fecha_d,
		$mod_importe,
		$mod_comentarios,
		$mod_numero_cuenta) = $result->fetchrow();
		$mod_fecha_d = Fecha($mod_fecha_d);
		$mod_fecha_v = Fecha($mod_fecha_v);
		$mod_fecha_e = Fecha($mod_fecha_e);
		//$mod_importe = formato_money($mod_importe);

		//			if ($mod_fecha_d == "00/00/2000") {
		//				$mod_fecha_d = "";
		//			}
		echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
		?>
		<script src="../../lib/NumberFormat150.js"></script>
        <script>
        
      
        
        function control_importe()
        {
         var co=eval("document.all.Ingreso_Cheque_Importe");
      
        co=co.value;
//         alert(co);
         if((co!=0)&&(co!=""))
         {
          return true;	
         }
         else
         {
         alert("El importe del cheque no puede tener el valor 0. Si usted quiere anular un cheque utilicé el botón Anular");		
         return false;
         }
        }
        </script>        
		<?
		echo "<form action=bancos_movi_chpen.php method=post>\n";
		//echo "<input type=hidden name=Modificacion_Cheque_Numero value='$mod_numero'>";
		//echo "<input type=hidden name=Modificar value='$mod_idbanco'>";
        echo "<table align=center cellpadding=5 cellspacing=0 class='bordes' >\n";//bordercolor='$bgcolor3'
		echo "<tr bordercolor='#000000'><td id=mo align=center>Modificación de datos del Cheque</td></tr>";
		echo "<tr bordercolor='#000000'><td align=center>";
		echo "<table cellspacing=5 border=0 bgcolor=$bgcolor_out>";//bordercolor='$bgcolor3'
		echo "<tr><td align=right><b>Banco</b></td>";
		//CAMBIOS
		//echo "<td align=left bordercolor=#000000>$mod_banco&nbsp;</td></tr>\n";
		 echo "<td align=left>";
        $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
        $result = $db->execute($sql) or die($db->ErrorMsg());

        /*DATOS PARA LA IMPUTACION*/
        include_once("../contabilidad/funciones.php");
		if($mod_numero)
        {$query="select id_imputacion,nombre from imputacion 
                join contabilidad.estado_imputacion using(id_estado_imputacion) 
                where númeroch=$mod_numero and idbanco=$mod_idbanco";
		 $imputacion=sql($query,"<br>Error al traer el id de imputacion<br>") or fin_pagina();
		 $id_imputacion=$imputacion->fields["id_imputacion"];
		 $estado_imputacion=$imputacion->fields["nombre"];
		 if ($estado_imputacion=='Finalizado Completo' || $estado_imputacion=='Finalizado Sin Discriminar' || $estado_imputacion=='Pago Anulado')
		     $readonly_importe=" readonly";
		   else   $readonly_importe="";
        }
        
         echo "<select name=Modificacion_Cheque_IdBanco>\n";
        while ($fila = $result->fetchrow()) {
            echo "<option value=".$fila[idbanco];
            if ($fila[idbanco] == $mod_idbanco)
                echo " selected";
            echo ">".$fila[nombrebanco]."</option>\n";
        }
        echo "</select>\n";

        echo "</td></tr>\n";


		echo "<tr><td align=right><b>Proveedor</b></td>";

		//echo "<td align=left bordercolor=#000000>$mod_proveedor&nbsp;</td></tr>\n";
		echo "<td align=left>";
		        echo "<select name=Modificacion_Cheque_IdProveedor>\n";
        $sql = "SELECT id_proveedor, razon_social FROM general.proveedor ORDER BY razon_social";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        while ($fila = $result->fetchrow()) {
            echo "<option value='".$fila[id_proveedor]."'";
            if ($fila[razon_social] == "$mod_proveedor") echo " selected";
            echo ">".$fila[razon_social]."</option>\n";
        }
        echo "</select></td></tr>\n";
		echo "<tr><td align=right><b>Fecha de Emisión</b></td>";
		//echo "<td align=left bordercolor=#000000>$mod_fecha_e&nbsp;</td>\n";
		echo "<td align=left>";
        echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Emision value='$mod_fecha_e' title='Ingrese la fecha de emisión del cheque'>";
		echo link_calendario("Modificacion_Cheque_Fecha_Emision");
        echo "</td>\n";

		echo "<tr>\n";
		echo "<tr><td align=right><b>Fecha de Vencimiento</b></td>";
		//echo "<td align=left bordercolor=#000000>$mod_fecha_v&nbsp;</td></tr>\n";
		 echo "<td align=left>";
        echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Vencimiento value='$mod_fecha_v' title='Ingrese la fecha de vencimiento del cheque'>";
		echo link_calendario("Modificacion_Cheque_Fecha_Vencimiento");
        echo "</td></tr>\n";

        echo "<tr><td align=right><b>Fecha Débito</b></td>";
		echo "<td align=left>";
		echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Debito value='$mod_fecha_d' title='Ingrese la fecha de débito del cheque'>";
		echo link_calendario("Modificacion_Cheque_Fecha_Debito");
		echo "</td></tr>\n";

		echo "<tr><td align=right><b>Número</b>\n";
		//echo "</td><td align=left bordercolor=#000000>$mod_numero&nbsp;</td>\n";
		echo "</td><td align=left>";
		//guarda el número de cheque y el id del banco
        echo "<input type=hidden name=Modificacion_Cheque_Numero_Old value='$mod_numero'>";
        echo "<input type=hidden name=Modificacion_id_banco_Old value='$mod_idbanco'>";

        echo "<input type=text name=Modificacion_Cheque_Numero value='$mod_numero' size=10 maxlength=50>";
        echo "</td>";

		echo "</tr>\n";
		echo "<tr><td align=right><b>Importe</b>\n";
		echo "</td><td align=left>";
		echo "<input type=text name=Ingreso_Cheque_Importe value='$mod_importe' size=10 maxlength=50 onchange='setear_montos_imputacion(\"númeroch\")' $readonly_importe >&nbsp;";
		echo "</td></tr>\n";
// concepto y plan cuando se modifica un debito
        cuenta_bancos($mod_numero_cuenta);
///////////////////////////////////////////////
		echo "<tr><td align=right valign=top><b>Comentarios</b>\n";
		echo "</td><td align=left>";
		echo "<textarea name=Modificacion_Cheque_Comentarios cols=30 rows=3>$mod_comentarios</textarea>";
		echo "</td></tr>\n";
		echo "<tr><td align=center colspan=2>\n";

	    tabla_imputacion($id_imputacion,$mod_importe);
        ?>
        <input type="hidden" name="id_imputacion" value="<?=$id_imputacion?>">
        <?
        
		echo "<table border=0 width=100%>\n";
		echo "<tr><td align=center>\n";


if ($volver_cheque==1)
 {$link="control_cheques.php";
?>
<input type="hidden" name="volver_cheque" value="1">
<?
 }
else
 $link=encode_link("bancos_movi_chpen.php",array('idbanco'=>$mod_idbanco));
 $link1=encode_link("bancos_anular_cheque.php",array('idbanco'=>$mod_idbanco,'num'=>$mod_numero));
 //$link = encode_link("partir_entrega.php",array("num"=>$res->fields['codigo_renglon'],"cant"=>$res->fields['cantidad'],"div"=>0,"row"=>$i,"id_oc"=>$res->fields['id_renglones_oc'],"titulo"=>$res->fields['titulo'],"agre"=>$agregado));	
 $link1="window.open(\"$link1\",\"\",\"top=50, left=170, width=800, height=600, scrollbars=1, status=1,directories=0\")";

		echo "<input type=submit name=Modificacion_Cheque_Guardar value='Guardar' onclick='return control_importe() && control_campos_imputacion();' >\n";
		echo "</form>\n";
		echo "</td><td align=center>\n";
		echo "<input type=button name=anular value='Anular' onclick='$link1;'>";
        echo "</td><td align=center>\n";
		echo "<form action=bancos_movi_chpen.php method=post>\n";
		echo "<input type=button name=Volver id=Volver value='   Volver   ' OnClick=\"window.location='".$link."';\">\n";
        echo "</form>\n";
		echo "</td></tr>\n";
		echo "</table>";
		echo "</td></tr>\n";
		echo "</table>";
		echo "</td></tr>\n";
		echo "</table>\n";
        exit();
	}
	else {
		Error("No hay ningún cheque seleccionado"."Numero".$mod_numero);
	}
	
}
if ($_POST["Actualizar"])
{
	$db->StartTrans();

     while (list($var,$val) = each($_POST))
     {
      if (ereg("^Fecha_Cheques_Pendientes_NC",$var))
      {
        $num_cheque = str_replace("Fecha_Cheques_Pendientes_NC","",$var);
        $fecha = $val;
          if ($fecha != "")
          {
            list($d,$m,$a) = explode("/",$fecha);
            $fecha=$d."-".$m."-".$a;
            if (FechaOk($fecha))
            {
                $sql = "UPDATE bancos.cheques ";
                $sql .= "SET FechaDébCh='$a-$m-$d' ";
                $sql .= "WHERE FechaDébCh IS NULL AND ";
                $sql .= "NúmeroCh=$num_cheque";
                $result = $db->Execute($sql) or die($db->ErrorMsg());
                $actualizado = 1;
                Aviso("Los datos del cheque número $num_cheque se actualizaron correctamente.");

                //Se envia el mail de aviso si el cheque debitado no esta asociado a OC
                $sql1="select númeroch from compras.ordenes_pagos where númeroch=$num_cheque";
		        $result1=$db->Execute($sql1) or die($db->ErrorMsg());
			    $nch=$result1->RecordCount();
			    if($nch==0)
			    {
			      $fecha=fecha_db(date("d/m/Y",mktime()));
			      $tipo_lo="de notificacion";
			      $usuario=$_ses_user['name'];
			      $para="juanmanuel@coradir.com.ar,corapi@coradir.com.ar";
			      $asunto="Notificación de cheques débitados que no están asociados a una Orden de Compra";
			      $sql2="select idbanco,idprov,importech,comentarios,fechaemich from cheques where númeroch=$num_cheque ";
			      $resul=sql($sql2,'no es pudo recuperar los datos del cheque ') or fin_pagina();
			      $id_banco=$resul->fields['idbanco'];
			      $id_prov=$resul->fields['idprov'];
			      $importe=$resul->fields['importech'];
			      $comentario=$resul->fields['comentarios'];
			      $fecemi=$resul->fields['fechaemich'];
			      $fecdeb=fecha_db(date("Y/m/d"));
			      $sql_prov = "SELECT  razon_social FROM general.proveedor where id_proveedor=$id_prov";
			      $result_prov = sql($sql_prov,"no se pudo recuperar el nombre del proveedor") or fin_pagina();
			      $mod_proveedor=$result_prov->fields['razon_social'];
			      $contenido="Se debitó el cheque número $num_cheque ,el cual fue debitado con fecha $fecdeb ,por el usuario $usuario ,a nombre de $mod_proveedor en concepto de $comentario ,por la cantidad de $ $importe. El correspondiente cheque no esta asociado a ninguna orden de compra.\nPara comfirmar el débito del cheque vaya al Modulo Bancos->Movimientos Cheques para Debitar";
			      if($usuario=="Noelia Lucero")
			      {
			      $para="juanmanuel@coradir.com.ar";
			      if ($importe > 5000) $para.=',corapi@coradir.com.ar';
			      enviar_mail($para,$asunto,$contenido,"","","");
			      }
			      if($usuario=="Juan Manuel Baretto")
			      {
			      $para="noelia@pcpower.com.ar,";
			       if ($importe > 5000) $para.=',corapi@coradir.com.ar';
			      enviar_mail($para,$asunto,$contenido,"","","");
			      }
			      if($usuario=="Alberto Corapi")
			      {
			      $para="juanmanuel@coradir.com.ar,noelia@pcpower.com.ar";
			      enviar_mail($para,$asunto,$contenido,"","","");
			      }
			      $campos="(fecha,usuario,tipo_log,idbanco,númeroch)";
			      $sql3="INSERT INTO log_cheques_debitados $campos VALUES ".
				  "('$fecha','$usuario','$tipo_lo',$id_banco,$num_cheque)";
				  $result1=sql($sql3,"no se pudo insertar en log_cheques_debitados") or fin_pagina();
			    }//de if($nch==0)
			    
            }//de if (FechaOk($fecha))
            else
            {
                Error("Formato de fecha inválido para el cheque número $num_cheque");
            }
        }//de if ($fecha != "")
      }//de if (ereg("^Fecha_Cheques_Pendientes_NC",$var))

     }//de while (list($var,$val) = each($_POST))

    if (!$actualizado)
	{
        if (!$error) {
            Aviso("No había ningún dato para actualizar");
        }
    }

     echo "</center></form>\n";


	  $db->CompleteTrans();
}//de if ($_POST["Actualizar"])

if ($download=$parametros['download'])
{
	ob_start();
	$itemspp=1000000;//para que la busqueda traiga todos los resultados para el excel
	$page=0;
}
echo $html_header;
echo "<form action=bancos_movi_chpen.php method=post>\n";

if ($Entre_Fechas_Campo) {
    $fecha_campo = $Entre_Fechas_Campo;
}
if ($Entre_Fechas == "1" )//and $_POST["Entre_Fechas"])
{
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
//echo "Fecha desde: $Fecha_Desde Fecha hasta: $Fecha_Hasta Fecha Campo: $Fecha_Campo\n";
//Total
$sql = "SELECT sum(ImporteCh) AS total FROM bancos.cheques WHERE FechaDébCh IS NULL";
$result = $db->execute($sql) or die($db->ErrorMsg());
$res_tmp = $result->fetchrow();
$Total = formato_money($res_tmp[total]);

cargar_calendario();

//Datos
 echo "<table width='95%' align=center cellpadding=5 cellspacing=0 border=0 >"; //bordercolor=$bgcolor3
 echo "<tr><td colspan=5 align=center><b>Banco</b>";
 $sql = "SELECT * FROM tipo_banco WHERE activo=1 order by nombrebanco";
 $result = $db->execute($sql) or die($db->ErrorMsg());
 echo "<select name=Mov_Cheques_Pendientes_Banco OnChange=\"document.forms[0].submit();\">\n";
 echo "<option value='todos' ";
 if ($Banco=="todos")
     echo " selected";
 $banco_nbre="Todos";
 echo ">Todos</option>\n";
 $ciudad_y_supervielle=0;
 while ($fila = $result->fetchrow())
 {
	 echo "<option value=".$fila["idbanco"];
	 if ($fila["idbanco"] == $Banco)
	 {
	     echo " selected";
	     $banco_nbre=$fila["nombrebanco"];
	 }
	 //guardamos los id de los banco Ciudad y Supervielle para generar una opcion que sea la combinacion de ambos
	 if($fila["nombrebanco"]=="Ciudad de Buenos Aires")
	  $id_banco_ciudad=$fila["idbanco"];
	 if($fila["nombrebanco"]=="Supervielle")
	  $id_supervielle=$fila["idbanco"];

	 echo ">".$fila["nombrebanco"]."</option>\n";
 }
  //si la siguiente opcion es elegida, se mostraran todos los cheques pendientes pertenecientes al banco ciudad
   //mas todos los cheques pendientes pertenecientes al banco superville
 ?>
  <option value="<?=" $id_banco_ciudad or cheques.idbanco=$id_supervielle"?>"
      <?if(" $id_banco_ciudad or cheques.idbanco=$id_supervielle" == $Banco)
        {echo "selected";
         $ciudad_y_supervielle=1;
        }

      ?>
  >
   Ciudad y Supervielle
  </option>
 <?

 echo "</select></td>\n";
 echo "<td colspan=3 align=center><b>Total Pendientes: \$ $Total</b>";
 echo "</td></tr>";
 echo "<tr><td colspan=8 align=center>";


// Formulario de busqueda
$orden = array(
         "default" => "1",     //campo por defecto
         "1" => "bancos.cheques.fechavtoch",
         "2" => "bancos.cheques.númeroch",
         "3" => "bancos.cheques.importech",
         "4" => "general.proveedor.razon_social",
         "5" => "bancos.cheques.comentarios",
         "6" => "bancos.cheques.fechaemich",
         "7" => "bancos.tipo_banco.nombrebanco",
);
$filtro = array(
          "general.proveedor.razon_social" => "Proveedor",
          "bancos.cheques.fechaemich" => "Fecha de emisión",
          "bancos.cheques.fechavtoch" => "Fecha de vencimiento",
          "bancos.cheques.fechaprev" => "Fecha prevista",
          "bancos.cheques.fechadébch" => "Fecha de débito",
          "bancos.cheques.númeroch" => "Número",
          "bancos.cheques.importech" => "Importe",
          "bancos.cheques.comentarios" => "Comentarios"
);
//sentencia sql que sin ninguna condicion
$sql_tmp = "SELECT bancos.cheques.idbanco,bancos.cheques.FechaVtoCh,";
$sql_tmp .= "bancos.cheques.FechaDébCh,";
$sql_tmp .= "bancos.cheques.fechaemich,";
$sql_tmp .= "bancos.cheques.NúmeroCh,";
$sql_tmp .= "bancos.cheques.ImporteCh,";
$sql_tmp .= "bancos.cheques.comentarios,";
$sql_tmp .= "general.proveedor.razon_social,tipo_banco.nombrebanco ";
$sql_tmp .= "FROM bancos.cheques ";
$sql_tmp .= "INNER JOIN general.proveedor ";
$sql_tmp .= "ON bancos.cheques.IdProv=general.proveedor.id_proveedor ";
$sql_tmp .= "INNER JOIN tipo_banco ";
$sql_tmp .= "ON bancos.cheques.idbanco=tipo_banco.idbanco ";

//prefijo para los links de paginas siguiente y anterior
$link_tmp = "";
//condiciones extras de la consulta
$where_tmp = "bancos.cheques.FechaDébCh IS NULL ";
if ($Banco!="todos")
    $where_tmp .= "AND (bancos.cheques.IdBanco=$Banco)";
else
    $where_tmp .= "AND tipo_banco.activo=1";
if ($entre_fechas) {
$where_tmp .= " AND $fecha_campo Between '$Fecha_Desde_db' AND '$Fecha_Hasta_db'";
}
$itemspp=5000;
list($sql,$total_Prov,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
 echo "&nbsp;&nbsp;&nbsp;<input type=submit name='form_busqueda' value='   Buscar   '>";
 if (permisos_check("inicio","excel_cheques_pen"))
 {	echo "&nbsp;&nbsp;<a target=_blank title='Bajar datos en un excel' href='". encode_link($_SERVER['SCRIPT_NAME'],array('download'=>1,"keyword"=>$_POST["keyword"],"filter"=>$_POST["filter"],'idbanco'=>$Banco)) ."'><img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' ></a>";
 	echo "</td></tr>";
 }

 echo "</tr>\n";
 echo "<tr><td colspan=3 align='center' valign='middle'>";
 echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
 echo "<input name='Entre_Fechas' type='checkbox' value='1'$fechas_check>";
 echo "<b> Entre fechas:</b></td>";
 echo "<td colspan=5 align=left><b>Campo:</b>";
 echo "<select name=Entre_Fechas_Campo>";
 foreach($datos["cheques"]["fecha"] as $campo => $descripcion)
 {
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
 echo "<input type=submit name=Modificar value='Modificar Datos'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
 echo "<input type=hidden name=IdBanco value='$Banco'>";
 echo "<input type=submit name=Actualizar value='Actualizar Fecha'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
 //echo "<input type=button name=Volver value='        Volver        ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">";
 ?>
 <input type="button" name=ultimos_cheques value='Ultimos Cheques' onclick="window.open('ultimos_cheques.php','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=0,top=0,width=950,height=650')">
 <?echo "</td></tr>\n";
 //echo $sql;

$result = $db->execute($sql) or die($db->ErrorMsg());
$SubTotal = 0;
if(!$download)
{ echo "</table>";
 echo "<table class='bordes' width='99%' align='center'>";
}
else
{ echo "<div align=center><b>Cheques Pendientes</b></div>";
  echo "<table class='bordes' border=1 width='99%' align='center'>";
}
if(!$download)
{
 echo "<tr ><td id=ma colspan=9 align=center>\n";//bordercolor='#000000'
 echo "<table id=ma border=0 width=100%><tr>\n";
 echo "<td align=center width=70%>Cheques Pendientes: $total_Prov</td>\n";
 echo "<td align=center width=30%>$link_pagina&nbsp;</td>\n";
 echo "</tr></table>\n";
 echo "</td></tr>\n";
}

if($download)
{
 //filas pares
 $style[0]='bgcolor=white';
 //filas impares
 $style[1]='bgcolor=#99CCFF';
 ob_clean();
 excel_header("cheques_pendientes.xls");
 echo "<html xmlns:v=\"urn:schemas-microsoft-com:vml\"\nxmlns:o=\"urn:schemas-microsoft-com:office:office\"\nxmlns:x=\"urn:schemas-microsoft-com:office:excel\"\nxmlns=\"http://www.w3.org/TR/REC-html40\">\n";
 echo "<style type=\"text/css\">\n";
 //para que tome la orientacion de la pagina y los margenes
 echo "@page {margin:.39in .39in .39in .39in; mso-page-orientation:landscape;}\n";
 require("../../lib/estilos.css");
 echo "</style>\n";
//para que tome la orientacion de la pagina y los margenes
 echo "<!--[if gte mso 9]><xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Cheques Pendientes</x:Name>
    <x:WorksheetOptions>
     <x:DefaultColWidth>10</x:DefaultColWidth>
     <x:Print>
      <x:ValidPrinterInfo/>
      <x:PaperSizeIndex>9</x:PaperSizeIndex>
      <x:HorizontalResolution>600</x:HorizontalResolution>
      <x:VerticalResolution>600</x:VerticalResolution>
     </x:Print>
     <x:CodeName>Hoja1</x:CodeName>
     <x:Selected/>
     <x:DoNotDisplayGridlines/>
     <x:ProtectContents>False</x:ProtectContents>
     <x:ProtectObjects>False</x:ProtectObjects>
     <x:ProtectScenarios>False</x:ProtectScenarios>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
  <x:WindowHeight>8190</x:WindowHeight>
  <x:WindowWidth>14235</x:WindowWidth>
  <x:WindowTopX>960</x:WindowTopX>
  <x:WindowTopY>1110</x:WindowTopY>
  <x:ProtectStructure>False</x:ProtectStructure>
  <x:ProtectWindows>False</x:ProtectWindows>
 </x:ExcelWorkbook>
</xml><![endif]-->";
 echo "<table width='100%' border=0 >"; //bordercolor=$bgcolor3
 echo "<tr><td colspan=9 align=center><b>CHEQUES PENDIENTES</b><td></tr>";
 echo "<tr><td>&nbsp;<td></tr>";
 echo "<tr><td>&nbsp;</td><td colspan=4><b>Banco:</b> $banco_nbre</td><td></tr>";
 if ($keyword!='')
 	echo "<tr><td>&nbsp;</td><td colspan=4><b>Palabra Buscada: </b> '$keyword' <i>en ".($filtro[$filter]?"campo ".$filtro[$filter]:"Todos los campos")."</i></td></tr>";
 if ($entre_fechas==1)
  echo "<tr><td>&nbsp;</td><td colspan=4><b>Fecha {$datos["cheques"]["fecha"][$fecha_campo]}:</b> <i>entre</i> $Fecha_Desde <i>y</i> $Fecha_Hasta</td></tr>";
 echo "<tr><td>&nbsp;</td><td colspan=4><b>Registros encontrados: ".$result->recordcount()."</b></td></tr>\n";
 echo "<tr><td>&nbsp;</td><td colspan=4><b>Total Pendiente: \$ $Total</b></td></tr>\n";
 echo "<tr><td>&nbsp;</td></tr>";
 echo "<tr align=center bgcolor=#000000 style='color:#E9E9E9;font-weight: bold;'>";
 echo "<td align=center>Vencimiento</td>\n";
 echo "<td align=center>Emisión</td>\n";
 echo "<td align=center>Número</td>";
 echo "<td align=center>Importe</td>";
 echo "<td align=center>Proveedor</td>";
 echo "<td align=center>Comentarios</td>";
 if($ciudad_y_supervielle || $Banco=="todos")
    echo "<td align=center>Banco</td>";
 echo "</tr>";
}
else
{echo "<tr  id=mo>";//bordercolor='#000000'
 echo "<td align=center>&nbsp;</td>";
 echo "<td align=center><a id=mo href='".encode_link('bancos_movi_chpen.php',Array('sort'=>1,'up'=>$up))."'>Vencimiento</a></td>\n";
 echo "<td align=center><a id=mo href='".encode_link('bancos_movi_chpen.php',Array('sort'=>6,'up'=>$up))."'>Emisión</a></td>\n";
 echo "<td align=center><a id=mo href='".encode_link('bancos_movi_chpen.php',Array('sort'=>2,'up'=>$up))."'>Número</a></td>";
 echo "<td align=center><a id=mo href='".encode_link('bancos_movi_chpen.php',Array('sort'=>3,'up'=>$up))."'>Importe</a></td>";
 echo "<td align=center>Débito</td>";
 echo "<td align=center><a id=mo href='".encode_link('bancos_movi_chpen.php',Array('sort'=>4,'up'=>$up))."'>Proveedor</a></td>";
 echo "<td align=center><a id=mo href='".encode_link('bancos_movi_chpen.php',Array('sort'=>5,'up'=>$up))."'>Comentarios</a></td>";
 if($ciudad_y_supervielle || $Banco=="todos")
    echo "<td align=center><a id=mo href='".encode_link('bancos_movi_chpen.php',Array('sort'=>7,'up'=>$up))."'>Bancos</a></td>";
 echo "</tr>\n";
}//del else de if ($download)
$form_element = 1;
if (!$download)
	echo "<input type='hidden' name=id_banco value=3>";
$i=0;
while ($fila = $result->fetchrow())
{
	$SubTotal += $fila['importech'];
	$form_element++;$i++;

	if ($download)
	{
		echo "<tr ".$style[$i%2].">";
		echo "<td align=center>".Fecha($fila['fechavtoch'])."</td>\n";
		echo "<td align=center>".Fecha($fila['fechaemich'])."</td>\n";
		echo "<td align=center>".$fila['númeroch']."</td>\n";
		echo "<td ".excel_style("$")."><b>".formato_money($fila['importech'])."</b></td>\n";
		echo "<td ".excel_style("texto")." style='width:190pt'>".$fila['razon_social']."</td>\n";
		echo "<td ".excel_style("texto")." >".$fila['comentarios']."</td>\n";
		if($ciudad_y_supervielle || $Banco=="todos")
		   echo "<td ".excel_style("texto")." >".$fila['nombrebanco']."</td>\n";
		echo "</tr>\n";
	}
	else
	{
		echo "<tr bgcolor=$bgcolor_out>";
		echo "<td align=center><input type=radio name=Modificar_Cheque_Numero onClick='document.all.id_banco.value=\"".$fila["idbanco"]."\";' value='".$fila[númeroch]."'></td>";
		echo "<td align=center>".Fecha($fila['fechavtoch'])."</td>\n";
		echo "<td align=center>".Fecha($fila['fechaemich'])."</td>\n";
		echo "<td align=center>".$fila['númeroch']."</td>\n";
		echo "<td align=right>\$".formato_money($fila['importech'])."</td>\n";
		echo "<td align=center>";
		echo "<input type=text size=10 maxlength=10 name=Fecha_Cheques_Pendientes_NC".$fila['númeroch']." title='Ingrese la fecha y\nhaga click en Actualizar'>";
		echo link_calendario("Fecha_Cheques_Pendientes_NC".$fila['númeroch']);
	    echo "</td>\n";
		echo "<td align=left style='width:190pt'>".$fila['razon_social']."</td>\n";
		echo "<td align=left>".$fila['comentarios']."</td>\n";
		if($ciudad_y_supervielle || $Banco=="todos")
		   echo "<td align=left>".$fila['nombrebanco']."</td>\n";
		echo "</tr>\n";
	}
}
if ($download)
{
	echo "<tr bgcolor=#000000 style='color:#E9E9E9;font-weight: bold;'><td colspan=6 align=center><b>Subtotal Pendiente: \$ ".formato_money($SubTotal)."</b></td></tr></table>";
	echo "<table><tr><td>&nbsp;</td></tr><table>";
}
else
{
	echo "<tr bgcolor=$bgcolor3><td colspan=9 align=center><b>Subtotal Pendiente: \$ ".formato_money($SubTotal)."</b></td></tr></table>";
	echo "</form>\n";
    fin_pagina();    
}


?>