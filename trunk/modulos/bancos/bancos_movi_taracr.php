<?
 /*
$Author: nazabal $
$Revision: 1.17 $
$Date: 2005/06/24 20:28:16 $
*/
// Cabecera Configuracion requerida
require_once("../../config.php");

$_POST["idbanco"] = $_POST["Mov_Tarjetas_Acreditadas_Banco"];
variables_form_busqueda("movi_taracr",array("idbanco" => ""));
$Banco = $idbanco or $Banco = 4;// Variables

if ($download=$parametros['download'])
{
	ob_start();
	$itemspp=1000000;//para que la busqueda traiga todos los resultados para el excel
	$page=0;
}
echo $html_header;

// Cuerpo de la pagina
if ($_POST['Modificar_Tarjeta_Guardar']) {
    $fecha_deposito = $_POST[Modificar_Tarjeta_Fecha_Deposito];
    $importe_deposito = $_POST[Modificar_Tarjeta_Importe_Deposito];
    $fecha_credito = $_POST[Modificar_Tarjeta_Fecha_Credito];
    $importe_credito = $_POST[Modificar_Tarjeta_Importe_Credito];
    $idbanco = $_POST[Modificar_Tarjeta_IdBanco];
    $idtar = $_POST[Modificar_Tarjeta_IdTarjeta];
    $tipotar = $_POST[Modificar_Tarjeta_Tipo];
    $volver = $_POST[Modificar_Tarjeta_Volver];

    if ($fecha_deposito == "") {
        Error("Falta ingresar la fecha de Depósito");
    }
    else {
        list($d,$m,$a) = explode("/",$fecha_deposito);
        if (FechaOk($fecha_deposito)) {
            $fecha_deposito = "'$a-$m-$d'";
        }
        else {
            Error("La fecha de Depósito ingresada no es válida");
        }
    }
    if ($fecha_credito == "") {
        $fecha_credito = "NULL";
	}
    else {
        list($d,$m,$a) = explode("/",$fecha_credito);
        if (FechaOk($fecha_credito)) {
            $fecha_credito = "'$a-$m-$d'";
        }
        else {
            Error("La fecha de Crédito ingresada no es válida");
        }
    }
    if ($importe_deposito == "") {
        Error("Falta ingresar el Importe del Depósito");
    }
    elseif (!es_numero($importe_deposito)) {
        Error("El Importe del Depósito ingresado no es válido");
    }
    if ($importe_credito == "") {
        $importe_credito = "NULL";
    }
    elseif (!es_numero($importe_deposito)) {
        Error("El Importe del Depósito ingresado no es válido");
    }

    if (!$error) {
        $sql = "UPDATE bancos.tarjetas SET ";
        $sql .= "idtipotar=$tipotar,";
		$sql .= "idbanco=$idbanco,";
        $sql .= "fechadeptar=$fecha_deposito,";
        $sql .= "importedeptar=$importe_deposito,";
        $sql .= "fechacrédtar=$fecha_credito,";
        $sql .= "importecrédtar=$importe_credito ";
        $sql .= "WHERE idtarjeta=$idtar";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        Aviso("Los datos se ingresaron correctamente");
    }
    $parametros['idbanco']=$idbanco;
}
if ($_POST[Modificar]) {
    $mod_numero = $_POST[Modificar_Tarjeta_IdTarjeta];
    if (es_numero($mod_numero)) {
        $sql = "SELECT bancos.tipo_banco.NombreBanco,";
        $sql .= "bancos.tarjetas.IdBanco,";
        $sql .= "bancos.tipo_tarjeta.TipoTarjeta,";
        $sql .= "bancos.tarjetas.IdTarjeta,";
        $sql .= "bancos.tarjetas.FechaDepTar,";
        $sql .= "bancos.tarjetas.ImporteDepTar,";
        $sql .= "bancos.tarjetas.FechaCrédTar,";
        $sql .= "bancos.tarjetas.ImporteCrédTar ";
        $sql .= "FROM (bancos.tarjetas ";
        $sql .= "INNER JOIN bancos.tipo_banco ";
        $sql .= "ON bancos.tarjetas.IdBanco=bancos.tipo_banco.IdBanco) ";
        $sql .= "INNER JOIN bancos.tipo_tarjeta ";
		$sql .= "ON bancos.tarjetas.IdTipoTar=bancos.tipo_tarjeta.IdTipoTar ";
        $sql .= "WHERE bancos.tarjetas.IdTarjeta=$mod_numero";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        list($mod_banco,
            $mod_idbanco,
            $mod_tarjeta,
            $mod_idtarjeta,
            $mod_fecha_d,
            $mod_importe_d,
            $mod_fecha_c,
            $mod_importe_c) = $result->fetchrow();
        $mod_fecha_d = Fecha($mod_fecha_d);
        $mod_fecha_c = Fecha($mod_fecha_c);
        echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
        echo "<form action=bancos_movi_taracr.php method=post>\n";
        echo "<input type=hidden name=Modificar_Tarjeta_IdTarjeta value='$mod_numero'>";
        echo "<input type=hidden name=Modificar_Tarjeta_IdBanco value='$mod_idbanco'>";
        echo "<table align=center cellpadding=5 cellspacing=0 border=1 >\n";//bordercolor='$bgcolor3'
        echo "<tr bordercolor='#000000'><td id=mo align=center>Modificación de datos de la Tarjeta</td></tr>";
        echo "<tr bordercolor='#000000'><td align=center>";
        echo "<table cellspacing=5 border=0 bgcolor=$bgcolor_out>";//bordercolor='$bgcolor3'
        echo "<tr><td align=right><b>Banco</b></td>";
        echo "<td align=left>";
        echo "<select name=Modificar_Tarjeta_IdBanco>\n";
        $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
        $result = $db->execute($sql) or die($db->ErrorMsg());
		while ($fila = $result->fetchrow()) {
            echo "<option value=".$fila[idbanco];
            if ($fila[idbanco] == $mod_idbanco) echo " selected";
            echo ">".$fila[nombrebanco]."</option>\n";
        }
        echo "</select></td></tr>\n";
        echo "<tr><td align=right><b>Tarjeta</b></td>";
        echo "<td align=left>";
          echo "<select name=Modificar_Tarjeta_Tipo>\n";
        $sql = "SELECT * FROM bancos.tipo_tarjeta";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        while ($fila = $result->fetchrow()) {
            echo "<option value=".$fila["idtipotar"];
           if ($fila["tipotarjeta"] == $mod_tarjeta) echo " selected";
           echo ">".$fila[tipotarjeta]."</option>\n";
        }
        echo "</select></td></tr>\n";
        echo "<tr><td align=right><b>Fecha de Depósito</b></td>";
        echo "<td align=left>";
        echo "<input type=text size=10 name=Modificar_Tarjeta_Fecha_Deposito value='$mod_fecha_d' title='Ingrese la fecha de depósito de la tarjeta'>";
		echo link_calendario("Modificar_Tarjeta_Fecha_Deposito");
        echo "</td></tr>\n";
        echo "<tr><td align=right><b>Importe Depósito</b>\n";
        echo "</td><td align=left>";
        echo "<input type=text name=Modificar_Tarjeta_Importe_Deposito value='$mod_importe_d' size=10 maxlength=50>&nbsp;";
        echo "</td></tr>\n";
		echo "<tr><td align=right><b>Fecha de Crédito</b></td>";
        echo "<td align=left>";
        echo "<input type=text size=10 name=Modificar_Tarjeta_Fecha_Credito value='$mod_fecha_c' title='Ingrese la fecha de crédito de la tarjeta'>";
        echo link_calendario("Modificar_Tarjeta_Fecha_Credito");
        echo "</td></tr>\n";
        echo "<tr><td align=right><b>Importe Crédito</b>\n";
        echo "</td><td align=left>";
        echo "<input type=text name=Modificar_Tarjeta_Importe_Credito value='$mod_importe_c' size=10 maxlength=50>&nbsp;";
        echo "</td></tr>\n";
        echo "<tr><td align=center colspan=2>\n";
        echo "<table border=0 width=100%>\n";
        echo "<tr><td colspan=2 align=center>\n";
        echo "<input type=submit name=Modificar_Tarjeta_Guardar value='Guardar'>&nbsp;&nbsp;&nbsp;\n";
        echo "<input type=button name=Volver value='   Volver   ' OnClick=\"window.location='".encode_link("bancos_movi_taracr.php",array('idbanco'=>$mod_idbanco))."';\">\n";
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
        Error("No hay ninguna tarjeta seleccionada");
    }
}
    echo "<form action=bancos_movi_taracr.php method=post>\n";
    //Total
    $sql = "SELECT sum(ImporteCrédTar) AS total FROM bancos.tarjetas WHERE FechaCrédTar IS NOT NULL";
    $result = $db->execute($sql) or die($db->ErrorMsg());
    $res_tmp = $result->fetchrow();
    $Total = formato_money($res_tmp[total]);

    //Datos
    echo "<table align=center cellpadding=5 cellspacing=0>";
    echo "<tr><td colspan=9 align=center><b>Banco</b>";
    $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
    $result = $db->execute($sql) or die($db->ErrorMsg());
    echo "<select name=Mov_Tarjetas_Acreditadas_Banco OnChange=\"document.forms[0].submit();\">\n";
    echo "<option value='todos' ";
		if ($Banco=="todos")
       echo " selected";
    $banco_nbre="Todos";   
    echo ">Todos</option>\n";
    while ($fila = $result->fetchrow()) {
        echo "<option value=".$fila[idbanco];
        if ($fila[idbanco] == $Banco)
        {
           echo " selected";
           $banco_nbre=$fila[nombrebanco];
        }
        echo ">".$fila[nombrebanco]."</option>\n";
    }
    echo "</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "<b>Total Acreditado: \$ $Total</b>";
    echo "</td></tr>";
    echo "<tr><td align=center colspan=9>";
	$orden = array(
			"default" => "5",     //campo por defecto
			"default_up" => "0",     //orden por defecto
			"1" => "bancos.tarjetas.idtarjeta",
			"2" => "bancos.tipo_tarjeta.tipotarjeta",
			"3" => "bancos.tarjetas.fechadeptar",
			"4" => "bancos.tarjetas.importedeptar",
			"5" => "bancos.tarjetas.fechacrédtar",
			"6" => "bancos.tarjetas.importecrédtar"
    );
    $filtro = array(
             "bancos.tipo_tarjeta.tipotarjeta" => "Tipo de tarjeta",
             "bancos.tarjetas.fechadeptar" => "Fecha de depósito",
             "bancos.tarjetas.importedeptar" => "Importe de depósito",
             "bancos.tarjetas.fechacrédtar" => "Fecha de crédito",
             "bancos.tarjetas.importecrédtar" => "Importe de crédito"
    );
    //sentencia sql que sin ninguna condicion
    $sql_tmp = "SELECT bancos.tarjetas.IdTarjeta,";
    $sql_tmp .= "bancos.tipo_tarjeta.TipoTarjeta,";
    $sql_tmp .= "bancos.tarjetas.FechaDepTar,";
    $sql_tmp .= "bancos.tarjetas.ImporteDepTar,";
    $sql_tmp .= "bancos.tarjetas.FechaCrédTar,";
    $sql_tmp .= "bancos.tarjetas.ImporteCrédTar ";
    $sql_tmp .= "FROM bancos.tarjetas ";
    $sql_tmp .= "INNER JOIN bancos.tipo_tarjeta ";
    $sql_tmp .= "ON bancos.tarjetas.idtipotar=bancos.tipo_tarjeta.idtipotar ";
    $sql_tmp .= "INNER JOIN tipo_banco ";
    $sql_tmp .= "ON bancos.tarjetas.idbanco=tipo_banco.idbanco ";
    //prefijo para los links de paginas siguiente y anterior
    //$link_tmp = array('idbanco'=>$Banco);
    $link_tmp = "";
    //condiciones extras de la consulta
    $where_tmp = "bancos.tarjetas.fechacrédtar IS NOT NULL ";
    if ($Banco!="todos")
       $where_tmp .= "AND bancos.tarjetas.IdBanco=$Banco";
   else
       $where_tmp .= "AND tipo_banco.activo=1";
   list($sql,$total_Prov,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
    echo "&nbsp;&nbsp;&nbsp;<input type=submit name='form_busqueda' value='   Buscar   '>";
	  if (permisos_check("inicio","excel_cheques_pen"))
 			echo "&nbsp;&nbsp;<a target=_blank title='Bajar datos en un excel' href='". encode_link($_SERVER['SCRIPT_NAME'],array('download'=>1,"keyword"=>$_POST["keyword"],"filter"=>$_POST["filter"],'idbanco'=>$Banco)) ."'><img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' ></a>";
    
    echo "</td></tr>\n";
    echo "<tr><td colspan=9 align=center>";
    echo "<input type=submit name=Modificar value='Modificar Datos'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "</td></tr>\n";
    $result = $db->execute($sql) or die($db->ErrorMsg());
    $SubTotal = 0;
	echo "</table>";
    echo "<table width='98%' align=center class='bordes' border=0>";
    echo "<tr ><td id=ma colspan=9>";//bordercolor='#000000'
	echo "<table id=ma border=0 width=100%><tr>\n";
	echo "<td align=left width=70%>Tarjetas Acreditadas</td>\n";
	echo "<td align=right width=30%>$link_pagina&nbsp;</td>\n";
	echo "</tr></table>\n";
	echo "</td></tr>";
    echo "<tr id=mo>";//bordercolor='#000000'
    echo "<td align=center>&nbsp;</td>";
    echo "<td align=center><a id=ma href='".encode_link("bancos_movi_taracr.php",Array('sort'=>1,'up'=>$up))."'>ID</a></td>";
    echo "<td align=center><a id=ma href='".encode_link("bancos_movi_taracr.php",Array('sort'=>2,'up'=>$up))."'>Tarjeta</a></td>";
    echo "<td align=center><a id=ma href='".encode_link("bancos_movi_taracr.php",Array('sort'=>3,'up'=>$up))."'>Fecha Depósito</a></td>";
    echo "<td align=center><a id=ma href='".encode_link("bancos_movi_taracr.php",Array('sort'=>4,'up'=>$up))."'>Importe Depósito</a></td>";
    echo "<td align=center><a id=ma href='".encode_link("bancos_movi_taracr.php",Array('sort'=>5,'up'=>$up))."'>Fecha Crédito</a></td>";
    echo "<td align=center><a id=ma href='".encode_link("bancos_movi_taracr.php",Array('sort'=>6,'up'=>$up))."'>Importe Crédito</a></td>";
    echo "<td align=center>Días</td>";
    echo "<td align=center>% Descuento</td>";
    echo "</tr>\n";
    
    if ($download)
    {
   		ob_clean();
   		excel_header("tar_acreditadas.xls");
   		echo "<html>";
			echo "<style type=\"text/css\">\n";
   		require("../../lib/estilos.css");
   		echo "</style>\n";
    	echo "<table>\n";
	    echo "<tr><td align=center colspan=8><b>TARJETAS ACREDITADAS</b></td></tr>\n";
	    echo "<tr><td>&nbsp;</td></tr>\n";
    	echo "<tr><td>&nbsp;</td><td colspan=7><b>Banco: </b>$banco_nbre</td></tr>\n";
    	if ($keyword)
    		echo "<tr><td>&nbsp;</td><td colspan=7><b>Palabra buscada:</b> '$keyword' <i>en ".($filtro[$filter]?"campo ".$filtro[$filter]:"Todos los campos")."</i></td></tr>\n";
	    echo "<tr><td>&nbsp;</td><td colspan=7><b>Registros encontrados: ".$result->recordcount()."</b></td></tr>\n";
	    echo "<tr><td>&nbsp;</td><td colspan=7><b>Total Acreditado: \$ $Total</b></td></tr>\n";
//	    echo "<tr><td>&nbsp;</td><td colspan=7><b>Total Busqueda: \$ $SubTotal</b></td></tr>\n";
	    echo "<tr><td>&nbsp;</td></tr>\n";
	    echo "<tr align=center bgcolor=#000000 style='color:#E9E9E9;font-weight: bold;' >\n";
	    echo "<td align=center>ID</td>\n";
	    echo "<td align=center>Tarjeta</td>\n";
	    echo "<td align=center>Fecha Depósito</td>\n";
	    echo "<td align=center>Importe Depósito</td>\n";
	    echo "<td align=center>Fecha Crédito</td>\n";
	    echo "<td align=center>Importe Crédito</td>\n";
	    echo "<td align=center>Días</td>\n";
	    echo "<td align=center>% Descuento</td>\n";
	    echo "</tr>\n";
    }
    $i=0;
	 //filas pares
	 $style[0]='bgcolor=white';
	 //filas impares
	 $style[1]="bgcolor=$bgcolor_out";

    while ($fila = $result->fetchrow()) {
        $SubTotal += $fila[importecrédtar];
        list($aa,$mm,$dd) = explode("-",$fila[fechacrédtar]);
        $fecha1 = mktime(0,0,0,$mm,$dd,$aa);
        list($aa,$mm,$dd) = explode("-",$fila[fechadeptar]);
        $fecha2 = mktime(0,0,0,$mm,$dd,$aa);
        $Dias=($fecha1 - $fecha2) / 86400;
		if ($fila[importedeptar] == 0) {
			$Porcentaje = 100;
		}
		else {
			$Porcentaje=100 - (($fila[importecrédtar] * 100)/$fila[importedeptar]);
		}
        $Porcentaje=sprintf("%0.2f",$Porcentaje);
        
        if ($download)
        {
	        echo "<tr {$style[$i=($i+1)%2]} >\n";// bordercolor='#000000'
	        echo "<td align=center>".$fila[idtarjeta]."</td>\n";
	        echo "<td align=left>".$fila[tipotarjeta]."</td>\n";
	        echo "<td align=center>".Fecha($fila[fechadeptar])."</td>\n";
	        echo "<td ".excel_style("$").">".formato_money($fila[importedeptar])."</td>\n";
	        echo "<td align=center>".Fecha($fila[fechacrédtar])."</td>\n";
	        echo "<td ".excel_style("$").">".formato_money($fila[importecrédtar])."</td>\n";
	        echo "<td align=center>$Dias</td>\n";
	        echo "<td align=right>$Porcentaje%</td>\n";
	        echo "</tr>\n";
        }
        else 
        {
	        echo "<tr bgcolor=$bgcolor_out>\n";// bordercolor='#000000'
	        echo "<td align=center><input type=radio name=Modificar_Tarjeta_IdTarjeta value='".$fila[idtarjeta]."'></td>";
	        echo "<td align=center>".$fila[idtarjeta]."</td>\n";
	        echo "<td align=left>".$fila[tipotarjeta]."</td>\n";
	        echo "<td align=center>".Fecha($fila[fechadeptar])."</td>\n";
	        echo "<td align=right>\$ ".formato_money($fila[importedeptar])."</td>\n";
	        echo "<td align=center>".Fecha($fila[fechacrédtar])."</td>\n";
	        echo "<td align=right>\$ ".formato_money($fila[importecrédtar])."</td>\n";
	        echo "<td align=center>$Dias</td>\n";
	        echo "<td align=right>$Porcentaje%</td>\n";
	        echo "</tr>\n";
        }
    }
    
	 if ($download)
   {
    echo "<tr bgcolor=#000000 style='color:#E9E9E9;font-weight: bold;'><td colspan=8 align=center><b>Subtotal Búsqueda: \$ ".formato_money($SubTotal)."</b></td></tr>\n";   	
   	echo "</table>\n";
   	echo "<table><tr><td>&nbsp;</td></tr></table>\n";
   }
   else
   {
		echo "<tr><td colspan=9 align=center bgcolor=$bgcolor3><b>Subtotal Acreditado: \$ ".formato_money($SubTotal)."</b></td></tr>";
    echo "</table></form>\n";
   }
?>