<?
/*
$Author: mari $
$Revision: 1.11 $
$Date: 2006/12/27 15:39:24 $
*/
// Cabecera Configuracion requerida
require_once("../../config.php");

// para el select del concepto y plan
function cuenta_bancos($nro_cuenta){
	global $db;
	global $concepto_cuenta,$parametros;
	$tipo_valor='base';
echo "<tr>
	    <td align=right><b>Concepto y Plan </b></td>
        <td align=right>";
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

$_POST["idbanco"] = $_POST["Mov_Debitos_Banco"];

variables_form_busqueda("movi_debitos",array("idbanco" => ""));
$download=$parametros["download"];
if ($download)
{
	$itemspp=1000000;
	$page=0;
	$nbre_banco="Todos";
	excel_header("Mov_Debitos.xls");
?>

<html>
<style type="text/css">
<!--
<? require("../../lib/estilos.css") ?>
-->
</style>
<?
	switch ($filter)
	{
		case "bancos.tipo_débito.tipodébito": $campo_buscado="Débito"; break;
		case "bancos.débitos.importedéb" :$campo_buscado="Importe"; break;
		case "bancos.débitos.fechadébito":$campo_buscado="Fecha"; break;
		default : $campo_buscado="Todos los Campos"; break;
	}
	ob_start();
}
else
	echo $html_header;

$Banco = $idbanco or $Banco = 4;// Variables

if($_POST["Anular"]=="Anular")
{
	$db->StartTrans();
	$iddebito = $_POST[Modificar_Debito_Id];

	//ponemos en cero el monto del debito
	$query="update bancos.débitos set importedéb=0 where iddébito=$iddebito";
	sql($query,"<br>Error al poner el monto cero en el débito<br>") or fin_pagina();

	$user_login=$_ses_user['name'];
    $fecha_debito=date('Y-m-d H:i:s');
    $tipo_log=5;//anulacion del debito
    $sql = "INSERT INTO bancos.log_debitos
    (iddébito,user_login,fecha,tipo_log,comentario)
    VALUES ($iddebito,'$user_login','$fecha_debito',$tipo_log,'')";
    sql ($sql,"<br>Error, no se puedo insertar el log de anulación")or fin_pagina();

	//y anulamos la imputacion que se habia generado
	include_once("../contabilidad/funciones.php");
	//preparamos el parametro para anular la imputacion de este cheque
	$pago[]=array();
	$pago["tipo_pago"]="iddébito";
	$pago["id_pago"]=$iddebito;
	$pago["id_banco"]="";
	anular_imputacion($pago);

	echo "<center><b>El débito se anuló con éxito</b></center>";

	$db->CompleteTrans();
}//de if($_POST["Anular"]=="Anular")

// Cuerpo de la pagina
if ($_POST["Modificar_Debito_Guardar"]) {
    $fecha = $_POST[Modificar_Debito_Fecha];
    $idbanco = $_POST[Modificar_Debito_Banco];
    $iddebito = $_POST[Modificar_Debito_Id];
    $importe = $_POST[Ingreso_Debito_Importe];
    $idtipo = $_POST[Modificar_Debito_Tipo];
	$comentario = $_POST["comentario"];
	//recupero concepto y plan
	$nro_cuenta=$_POST['cuentas'];
    if ($fecha == "") {
        $fecha = "NULL";
    }
    else {
        list($d,$m,$a) = explode("/",$fecha);
        if (FechaOk($fecha)) {
            $fecha = "'$a-$m-$d'";
        }
        else {
            Error("La fecha de Débito ingresada no es válida");
        }
    }
    if ($importe == "") {
        Error("Falta ingresar el Importe");
    }
    elseif (!es_numero($importe)) {
        Error("El Importe ingresado no es válido");
    }

    if (!$error) {
        $sql = "UPDATE bancos.débitos SET ";
        $sql .= "idtipodéb=$idtipo,";
        $sql .= "idbanco=$idbanco,";
        $sql .= "fechadébito=$fecha,";
        $sql .= "importedéb=$importe,";
        $sql .= "comentario='$comentario',";
        $sql .= "numero_cuenta=$nro_cuenta ";
        $sql .= " WHERE iddébito=$iddebito";
        $result = $db->execute($sql) or die($db->ErrorMsg());

        $user_login=$_ses_user['name'];
        $fecha_log_debito=date('Y-m-d H:i:s');
        $tipo_log=2;//modificacion del debito
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
	    imputar_pago($pago,$id_imputacion);

        Aviso("Los datos se ingresaron correctamente");
    }
}
    if ($_POST["Modificar_Debito"] || $parametros['pagina']=='mail' || $parametros['pagina']=="imputaciones") {
       $iddebito=$_POST["IdDebito"] or $iddebito=$parametros['id_debito'];
       if (es_numero($iddebito)) {
       $sql = "SELECT * FROM bancos.débitos WHERE iddébito=$iddebito";
       $result = $db->execute($sql) or die($db->ErrorMsg());
       $fila = $result->fetchrow();
       $iddebito = $fila["iddébito"];
       $banco = $fila["idbanco"];
       $idtipo = $fila["idtipodéb"];
       $fecha = Fecha($fila["fechadébito"]);
       $importe = $fila["importedéb"];
	   $comentario=$fila["comentario"];
	   $nro_cuenta=$fila['numero_cuenta'];
        echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
        ?>
              
        <script src="../../lib/NumberFormat150.js"></script>
        <?
        echo "<form action=bancos_movi_debitos.php method=post>\n";
        echo "<input type=hidden name=Modificar_Debito_Id value='$iddebito'><br>\n";
	   	if (!$download){
			/***********************************************
			 Traemos y mostramos el Log de la OC
			************************************************/
			$q="select * from bancos.log_debitos where iddébito=$iddebito";
			$q.=" order by fecha desc";
			$log=sql($q,"No Puedo Mostrar log") or fin_pagina();
			?>
			<div align="right">
				<input name="mostrar_ocultar_log" type="checkbox" value="1" onclick="if(!this.checked)
																				  document.all.tabla_logs.style.display='none'
																				 else
																				  document.all.tabla_logs.style.display='block'
																				  "><b>Mostrar Logs</b>
			</div>
			<!-- tabla de Log de la OC -->
			<div style="display:'none';width:98%;overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;'?> " id="tabla_logs" >
			<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
			<?

			while (!$log->EOF){

			 if ($log->fields['tipo_log']==1)$tipo_log="Alta de Débito";
			 if ($log->fields['tipo_log']==2)$tipo_log="Modificación de Débito";
			 if ($log->fields['tipo_log']==5)$tipo_log="Anulación de Débito";
			?>
			<tr>
			      <td height="20" nowrap>Fecha <?=date("j/m/Y H:i:s",strtotime($log->fields['fecha']))?> </td>
			      <td nowrap > Usuario: <?=$log->fields['user_login'];?> </td>
			      <td nowrap > Tipo de Log: <?=$tipo_log;?> </td>
			</tr>
			<?
			 $log->MoveNext();
			}
			?>
			</table>
			</div>
			<?
			/***********************************************
			 Fin de muestra del LOG de la OC
			************************************************/
		}
		/*DATOS PARA LA IMPUTACION*/
		include_once("../contabilidad/funciones.php");
		  if($iddebito)
          {$query="select id_imputacion,nombre from contabilidad.imputacion join 
                    contabilidad.estado_imputacion using(id_estado_imputacion) 
                    where iddébito=$iddebito";
           $imputacion=sql($query,"<br>Error al traer el id de imputacion<br>") or fin_pagina();
		   $id_imputacion=$imputacion->fields["id_imputacion"];
		   $estado_imputacion=$imputacion->fields["nombre"];
		   if ($estado_imputacion=='Finalizado Completo' || $estado_imputacion=='Finalizado Sin Discriminar' || $estado_imputacion=='Pago Anulado')
		      $readonly_importe=" readonly";
		   else   $readonly_importe="";
          }
		
		echo "<br>";
        echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
        echo "<tr bordercolor='#000000'><td id=mo align=center>Modificación del Débito</td></tr>";
        echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0 bgcolor=$bgcolor_out>";
        echo "<tr><td align=right><b>Banco</b></td>";
        echo "<td align=left>";
        echo "<select name=Modificar_Debito_Banco>\n";
        $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        while ($fila = $result->fetchrow()) {
            echo "<option value=".$fila[idbanco];
            if ($fila[idbanco] == $banco)  echo " selected";
            echo ">".$fila[nombrebanco]."</option>\n";
        }
        echo "</select></td></tr>\n";
        echo "<tr><td align=right><b>Tipo de Débito</b></td>";
        echo "<td align=left>";
        echo "<select name=Modificar_Debito_Tipo>\n";
        $sql = "SELECT * FROM bancos.tipo_débito";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        while ($fila = $result->fetchrow()) {
            echo "<option value=".$fila[idtipodéb];
           if ($fila["idtipodéb"] == $idtipo) echo " selected";
           echo ">".$fila[tipodébito]."</option>\n";
        }
        echo "</select></td></tr>\n";
        echo "<tr><td align=right><b>Fecha Débito</b></td>";
        echo "<td align=left>";
        echo "<input type=text size=10 maxlength=10 name=Modificar_Debito_Fecha value='".$fecha."' title='Ingrese la fecha de débito'>";
        echo link_calendario("Modificar_Debito_Fecha");
        echo "</td></tr>\n";
        echo "<tr><td align=right><b>Importe</b>\n";
        echo "</td><td>";
        echo "<input type=text name=Ingreso_Debito_Importe value='".$importe."'  onchange='setear_montos_imputacion(\"iddébito\")' size=22 maxlength=50 $readonly_importe >&nbsp;";
        echo "</td></tr>\n";
// concepto y plan cuando se modifica un debito
        cuenta_bancos($nro_cuenta);
///////////////////////////////////////////////
		echo "<tr><td colspan=2>\n";
		echo "<b>Comentario:</b><br>\n";
		echo "<textarea name='comentario' cols=50 rows=5>$comentario</textarea>\n";
		echo "</td></tr>\n";
		
		?>
		<tr>
		 <td colspan="2" width="100%">
		  <?
		 

          tabla_imputacion($id_imputacion,$importe);
          ?>
          <input type="hidden" name="id_imputacion" value="<?=$id_imputacion?>">
		 </td>
		</tr>
		<?
		
        echo "<tr><td align=center colspan=2>\n";
        echo "<input type=submit name=Modificar_Debito_Guardar value='Guardar' onclick='return (control_campos_imputacion());' >&nbsp;&nbsp;&nbsp;\n";
        if(permisos_check("inicio","permiso_anular_debitos"))
        	echo "<input type=submit name=Anular value='Anular' onclick='return confirm(\"¿Está seguro que desea anular el Débito?\" )'>&nbsp;&nbsp;&nbsp;\n";
        echo "<input type=button name=Volver value='   Volver   ' OnClick=\"window.location='".encode_link("bancos_movi_debitos.php",array('idbanco'=>$banco))."';\">\n";
        echo "</td></tr>\n";
        echo "</table>";
        echo "</td></tr>\n";
        echo "</table>\n";
       exit();
       }
   }
    echo "<form action=bancos_movi_debitos.php method=post>\n";
    //Total
    $sql = "SELECT sum(ImporteDéb) AS total FROM bancos.débitos";
    $result = $db->execute($sql) or die($db->ErrorMsg());
    $res_tmp = $result->fetchrow();
    $Total = formato_money($res_tmp[total]);
    //Datos
    echo "<table align=center cellpadding=5 cellspacing=0>";
    echo "<tr><td align=left colspan=2><b>Banco:</b>";
    $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
    $result = $db->execute($sql) or die($db->ErrorMsg());
    echo "<select name=Mov_Debitos_Banco OnChange=\"document.forms[0].submit();\">\n";
    echo "<option value='todos' ";
    if ($Banco=="todos")
       echo " selected";
    echo ">Todos</option>\n";
    while ($fila = $result->fetchrow()) {
        echo "<option value=".$fila[idbanco];
        if ($fila[idbanco] == $Banco)
        {
            echo " selected";
			$nbre_banco=$fila[nombrebanco];
        }
        echo ">".$fila[nombrebanco]."</option>\n";
    }
    echo "</select></td>\n";
    echo "<td colspan=2 align=right><b>Total Debitados: \$ $Total</b>";
    echo "</td></tr>";
   echo "<tr><td align=center colspan=4>";
   $orden = array(
			"default" => "3",     //campo por defecto
			"default_up" => "0",     //orden por defecto
            "1" => "bancos.tipo_débito.tipodébito",
            "2" => "bancos.débitos.importedéb",
            "3" => "bancos.débitos.fechadébito",
            "4" => "IdDébito"
   );
   $filtro = array(
             "IdDébito"    => "IDDébito",
             "bancos.tipo_débito.tipodébito" => "Débito",
             "bancos.débitos.importedéb"     => "Importe",
             "bancos.débitos.fechadébito"    => "Fecha"
     );
   //sentencia sql que sin ninguna condicion
   $sql_tmp = "SELECT IdDébito,TipoDébito,FechaDébito,ImporteDéb,comentario ";
   $sql_tmp .= "FROM (bancos.débitos ";
   $sql_tmp .= "INNER JOIN bancos.tipo_débito ON ";
   $sql_tmp .= "bancos.débitos.IdTipoDéb=bancos.tipo_débito.IdTipoDéb) ";
   $sql_tmp .= "INNER JOIN tipo_banco ";
   $sql_tmp .= "ON bancos.débitos.idbanco=tipo_banco.idbanco ";
   //prefijo para los links de paginas siguiente y anterior
//   $link_tmp = array("idbanco"=>$Banco);
   $link_tmp = "";
   //condiciones extras de la consulta
   if ($Banco!="todos")
       $where_tmp .= "débitos.idbanco=$Banco";
   else
       $where_tmp .= "tipo_banco.activo=1";
   //control por fecha
   if (($filter=="bancos.débitos.fechadébito") && (FechaOk($keyword)))
    $keyword=fecha_db($keyword);
   list($sql,$total_Prov,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
   echo "&nbsp;&nbsp;&nbsp;<input type=submit name='form_busqueda' value='   Buscar   '>";
   ?>
   &nbsp;&nbsp;<input type="button" name=ultimos_debitos value='Ultimos Debitos' onclick="window.open('ultimos_debitos.php','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=0,top=0,width=950,height=650')">
   <?
   if (permisos_check("inicio","xls_bancos_informes"))
	{
	echo	'&nbsp;&nbsp;<a target=_blank href="'.encode_link($_SERVER['SCRIPT_NAME'],array("download"=>true)).'"><img style="cursor=hand" title="Bajar datos en un excel" src="../../imagenes/excel.gif" width="16" height="16" border="0"></a>';
	//		'<img style="cursor=hand" onclick=\'wdownload=window.open("'.encode_link('bancos_informes.php',$link_download).'","","toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=790,height=590");\' title="Bajar datos en un excel" src="../../imagenes/excel.gif" width="16" height="16" border="0" >'.
	}
   echo "</td></tr>\n";
   echo "<tr><td colspan=4 align=center>";
   echo "<input type=submit name='Modificar_Debito' value=' Modificar '>&nbsp;&nbsp;&nbsp;";
   $result = $db->execute($sql) or die($db->ErrorMsg());
   $SubTotal = 0;
   echo "</table>";

   if ($download)
   {
   	ob_clean();
?>
	<table>
	<tr>
	<td>Banco: <?=$nbre_banco ?></td>
	</tr>
	<tr>
	<td colspan=3>Se busco: '<?=$keyword ?>' en <?=$campo_buscado ?></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	</table>

<?
   }
    echo "<table width='98%' align=center cellpadding=2 cellspacing=2 class='bordes'>";
    echo "<tr id=ma >";
	if (!$download)
	{
    echo "<td colspan=6 align=center>";
   	echo "<table id=ma border=0 width=100%><tr>\n";
	echo "<td align=left width=70%>Débitos</td>\n";
	echo "<td align=right width=30%>$link_pagina&nbsp;</td>\n";
	echo "</tr></table>\n";

	echo "</td>";
	}
	else
	{
    echo "<td colspan=4 id=mo align=center>Débitos";
    echo "</td>";
	}
	echo "</tr>";
   echo "<tr  id=mo>";
   if (!$download)
   {
   	   echo "<td>&nbsp;</td>\n";
	   echo "<td align=center><a id=mo href='".encode_link("bancos_movi_debitos.php",Array('sort'=>4,'up'=>$up))."'>IDDébito</a></td>";
	   echo "<td align=center><a id=mo href='".encode_link("bancos_movi_debitos.php",Array('sort'=>1,'up'=>$up))."'>Débito</a></td>";
	   echo "<td align=center><a id=mo href='".encode_link("bancos_movi_debitos.php",Array('sort'=>2,'up'=>$up))."'>Importe</a></td>";
	   echo "<td align=center><a id=mo href='".encode_link("bancos_movi_debitos.php",Array('sort'=>3,'up'=>$up))."'>Fecha</a></td>";
	   echo "<td align=center>Comentarios</td>";
   }
   else
   {
	   echo "<td align=center>Débito</td>";
	   echo "<td align=center>Importe</td>";
	   echo "<td align=center>Fecha</td>";
	   echo "<td align=center>Comentarios</td>";
   }

   echo "</tr>\n";
   while ($fila = $result->fetchrow()) {
        $SubTotal += $fila[importedéb];

        echo "<tr bgcolor=$bgcolor_out>\n";
        if (!$download)
        {
	        echo "<td align=center><input type=radio name=IdDebito value='".$fila[iddébito]."'></td>\n";
	        echo "<td align=left>".$fila[iddébito]."</td>\n";
	        echo "<td align=left>".$fila[tipodébito]."</td>\n";
	        echo "<td align=right>\$".formato_money($fila[importedéb])."</td>\n";
	        echo "<td align=center>".Fecha($fila[fechadébito])."</td>\n";
	        echo "<td align=center>".$fila[comentario]."</td>\n";
        }
        else
        {
	        echo "<td align=left ".excel_style("texto").">".$fila[tipodébito]."</td>\n";
	        echo "<td align=right ".excel_style('$').">".formato_money($fila[importedéb])."</td>\n";
	        echo "<td align=center ".excel_style("fecha").">".Fecha($fila[fechadébito])."</td>\n";
	         echo "<td align=center ".excel_style("texto").">".$fila[comentario]."</td>\n";
        }

        echo "</tr>\n";
    }

	if (!$download)
		echo "<tr><td colspan=6 align=center bgcolor=$bgcolor3><b>Subtotal Débitos: \$ ".formato_money($SubTotal)."</b></td></tr>";

    echo "</table>";
    if (!$download)
     echo "</form>\n";
    else
    {
		echo "<br>";
    	echo "<table border=1>";
		echo "<tr><td align=center id=ma><b>Subtotal Débitos:</b></td><td colspan=2 ".excel_style('$'). "><b>".formato_money($SubTotal)."</b></td></tr>";
  		echo "</table>";
    	echo "<table><tr><td>&nbsp;</td></tr></table></html>";
    }

?>