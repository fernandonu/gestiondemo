<?
/*
$Author: fernando $
$Revision: 1.22 $
$Date: 2005/09/06 20:22:34 $
*/
// Cabecera Configuracion requerida
require_once("../../config.php");

$_POST["idbanco"] = $_POST["Mov_Depositos_Pendientes_Banco"];

if ($parametros["idbanco"]) $Banco=$parametros["idbanco"];

variables_form_busqueda("movi_deppen",array("idbanco" => ""));

$Banco = $idbanco or $Banco = 4;




if ($download=$parametros['download'])
{
	ob_start();
	$itemspp=1000000;//para que la busqueda traiga todos los resultados para el excel
	$page=0;
}
echo $html_header;

// Cuerpo de la pagina
if ($_POST["Modificacion_Deposito_Guardar"]) {
    $fecha_credito = $_POST["Modificacion_Deposito_Fecha_Credito"];
    $idbanco = $_POST["Modificar_Deposito_IdBanco"];
    $iddep = $_POST["Modificacion_Deposito_IdDeposito"];
    $importe = $_POST["Modificacion_Deposito_Importe"];
    $fechadep=$_POST['Modificar_Deposito_Fecha_Deposito'];
    $idtipodep=$_POST['Modificar_Deposito_Tipo']; 
    $coment=$_POST['coment'];
    if ($fecha_credito == "") {
        $fecha_credito = "NULL";
    }
    else {
        list($d,$m,$a) = explode("/",$fecha_credito);
        if (FechaOk($fecha_credito)) {
            $fecha_credito = "'".Fecha_db($fecha_credito)."'";
        }
        else {
            Error("La fecha de Cr�dito ingresada es inv�lida");
        }
    }
  if ($fechadep == "") {
        $fechadep = "NULL";
    }
    else {
        list($d,$m,$a) = explode("/",$fechadep);
        if (FechaOk($fechadep)) {
            $fechadep = "'".Fecha_db($fechadep)."'";
        }
        else {
            Error("La fecha de Dep�sito ingresada es inv�lida");
        }
    }
  
    
    
    if ($importe == "") {
        Error("Falta ingresar el Importe");
    }
    elseif (!es_numero($importe)) {
        Error("El Importe ingresado es inv�lido");
    }

    if (!$error) {
        $sql = "UPDATE bancos.dep�sitos SET ";
        $sql .= "FechaCr�dito=$fecha_credito,";
        $sql .= "idbanco=$idbanco,";
        $sql .="idtipodep= $idtipodep,";
         $sql .="fechadep�sito=$fechadep,";
        $sql .= "ImporteDep=$importe, ";
		  $sql .="comentario='$coment' ";
        $sql .= "WHERE IdDep�sito=$iddep";
        $result = $db->query($sql) or die($db->ErrorMsg());
        Aviso("Los datos se ingresaron correctamente");
        
    }
    
    
    
}
if ($_POST["Modificar"] || $parametros["Modificar"]) {
	$mod_numero = $_POST["Modificar_Deposito_Numero"] or $mod_numero = $parametros["Modificar_Deposito_Numero"];
	if (es_numero($mod_numero)) {
		$sql = "SELECT bancos.tipo_banco.NombreBanco,";
		$sql .= "bancos.dep�sitos.IdBanco,";
		$sql .= "bancos.tipo_dep�sito.TipoDep�sito,";
		$sql .= "bancos.dep�sitos.IdDep�sito,";
		$sql .= "bancos.dep�sitos.FechaCr�dito,";
		$sql .= "bancos.dep�sitos.FechaDep�sito,";
		$sql .= "bancos.dep�sitos.ImporteDep, ";
		$sql .= "bancos.dep�sitos.comentario ";
		$sql .= "FROM (bancos.dep�sitos ";
		$sql .= "INNER JOIN bancos.tipo_banco ";
		$sql .= "ON bancos.dep�sitos.IdBanco = bancos.tipo_banco.IdBanco) ";
		$sql .= "INNER JOIN bancos.tipo_dep�sito ";
		$sql .= "ON bancos.dep�sitos.IdTipoDep = bancos.tipo_dep�sito.IdTipoDep ";
		$sql .= "WHERE bancos.dep�sitos.IdDep�sito=$mod_numero";
        $result = $db->query($sql) or die($db->ErrorMsg());
		list($mod_banco,
		$mod_idbanco,
		$mod_tipodep,
		$mod_iddep,
		$mod_fecha_c,
		$mod_fecha_d,
		$mod_importe,
		$coment) = $result->fetchrow();
		$mod_fecha_d = Fecha($mod_fecha_d);
		$mod_fecha_c = Fecha($mod_fecha_c);
		$mod_importe = formato_money($mod_importe);
		//			if ($mod_fecha_c == "00/00/2000") {
		//				$mod_fecha_c = "";
		//			}
		echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
		echo "<form action=bancos_movi_deppen.php method=post>\n";
		echo "<input type=hidden name=Mov_Depositos_Pendientes_Banco value=$Banco>\n";
		echo "<input type=hidden name=Modificacion_Deposito_IdDeposito value='$mod_iddep'>";
		echo "<input type=hidden name=Modificacion_Deposito_IdBanco value='$mod_idbanco'>";
		
		echo "<table align=center cellpadding=5 cellspacing=0 border=1 >\n";//bordercolor='$bgcolor3'
		echo "<tr bordercolor='#000000'><td id=mo align=center>Modificaci�n de datos del Dep�sito</td></tr>";
		echo "<tr bordercolor='#000000'><td align=center>";
		echo "<table cellspacing=5 cellpadding=0 border=0 bgcolor='$bgcolor_out' >";//bordercolor='$bgcolor3'
		echo "<tr><td align=right><b>Banco</b></td>";
		//echo "<td align=left bordercolor=#000000>$mod_banco&nbsp;</td></tr>\n";
		echo "<td align=left>";
        echo "<select name=Modificar_Deposito_IdBanco>\n";
        $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        while ($fila = $result->fetchrow()) {
			echo "<option value=".$fila[idbanco];
            if ($fila[nombrebanco] == $mod_banco) echo " selected";
            echo ">".$fila[nombrebanco]."</option>\n";
        }
        echo "</select></td></tr>\n";
		
		echo "<tr><td align=right><b>Tipo Dep�sito</b></td>";
	//	echo "<td align=left bordercolor=#000000>$mod_tipodep&nbsp;</td></tr>\n";
		
		echo "<td align=left>";
       echo "<select name=Modificar_Deposito_Tipo>\n";
       $sql = "SELECT * FROM bancos.tipo_dep�sito";
       $result = $db->execute($sql) or die($db->ErrorMsg());
       while ($fila = $result->fetchrow()) {
           echo "<option value=".$fila[idtipodep];
           if ($fila["tipodep�sito"] == $mod_tipodep) echo " selected";
           echo ">".$fila[tipodep�sito]."</option>\n";
        }
        echo "</select></td></tr>\n";
        
		echo "<tr><td align=right><b>Fecha de Dep�sito</b></td>";
		//echo "<td align=left bordercolor=#000000>$mod_fecha_d&nbsp;</td>\n";
		echo "<td align=left>";
        echo "<input type=text size=10 maxlength=10 name=Modificar_Deposito_Fecha_Deposito value='".$mod_fecha_d."' title='Ingrese la fecha de dep�sito'>";
		echo link_calendario("Modificar_Deposito_Fecha_Deposito");
        echo "</td></tr>\n";
      
		
		echo "<tr>\n";
		echo "<tr><td align=right><b>Fecha de Cr�dito</b></td>";
		echo "<td align=left>";
		echo "<input type=text size=10 name=Modificacion_Deposito_Fecha_Credito value='$mod_fecha_c' title='Ingrese la fecha de cr�dito del dep�sito'>";
		echo link_calendario("Modificacion_Deposito_Fecha_Credito");
		echo "</td></tr>\n";
		echo "<tr><td align=right><b>Importe</b>\n";
		echo "</td><td align=left>";
		echo "<input type=text name=Modificacion_Deposito_Importe value='$mod_importe' size=10 maxlength=50>&nbsp;";
		echo "</td></tr>\n";
		echo "<tr><td align='right'><b>Comentarios</td>";
		echo "<td><textarea name='coment' cols=25 wrap='FISICAL' >$coment</textarea></td></tr>";
		
		echo "<tr><td align=center colspan=2>\n";
		echo "<table border=0 width=100%>\n";
		echo "<tr><td align=center>\n";
		echo "<input type=submit name=Modificacion_Deposito_Guardar value='Guardar'>\n";
        if ($parametros['Modificar']) {
       	     echo "<input type=button name=volver value='Volver a cobranzas' onclick='window.close();'\n";
        } else
		      echo "<input type=submit name=volver value='Volver'>\n";
        echo "</form>\n";
		echo "</td><td align=center>\n";
		echo "</td></tr>\n";
		echo "</table>";
		echo "</td></tr>\n";
		echo "</table>";
		echo "</td></tr>\n";
		echo "</table>\n";
		exit();
	}
	else {
		Error("No hay ning�n dep�sito seleccionado");
	}
}
if ($_POST["Actualizar"]) {
    while (list($var,$val) = each($_POST)) {
      if (ereg("^Fecha_Depositos_Pendientes_ND",$var)) {
        $num_deposito = str_replace("Fecha_Depositos_Pendientes_ND","",$var);
        $fecha = $val;
        if ($fecha != "") {
            list($d,$m,$a) = explode("/",$fecha);
            if (FechaOk($fecha)) {
                $sql = "UPDATE bancos.dep�sitos ";
				$sql .= "SET FechaCr�dito='$a-$m-$d' ";
                $sql .= "WHERE FechaCr�dito IS NULL AND ";
                $sql .= "IdDep�sito=$num_deposito";
                $result = $db->query($sql) or die($db->ErrorMsg());
                $actualizado = 1;
                Aviso("Los datos del dep�sito n�mero $num_deposito se actualizaron correctamente.");
            }
            else {
                Error("Formato de fecha inv�lido para el deposito n�mero $num_deposito");
            }
        }
      }
    }
    if (!$actualizado) {
        if (!$error) {
            Aviso("No hab�a ning�n dato para actualizar");
        }
    }
}
    //Total
    $sql = "SELECT sum(ImporteDep) AS total FROM bancos.dep�sitos WHERE FechaCr�dito IS NULL";
    $result = $db->execute($sql) or die($db->ErrorMsg());
    $res_tmp = $result->fetchrow();
    $Total = formato_money($res_tmp[total]);

		cargar_calendario();
    echo "<form action=bancos_movi_deppen.php method=post>\n";
    //Datos
    echo "<table align=center cellpadding=5 cellspacing=0 border=0 >";//bordercolor=$bgcolor3
    echo "<tr><td colspan=6 align=center><b>Banco</b>";
    $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
    $result = $db->execute($sql) or die($db->ErrorMsg());
    echo "<select name=Mov_Depositos_Pendientes_Banco OnChange=\"document.forms[0].submit();\">\n";
    echo "<option value='todos' ";
    if ($Banco=="todos")
    {
       echo " selected";
       $banco_nbre="Todos";
    }
    echo ">Todos</option>\n";
    while ($fila = $result->fetchrow()) {
        echo "<option value=".$fila[idbanco];
        if ($fila[idbanco] == $Banco)
        {
        		$banco_nbre=$fila[nombrebanco];
            echo " selected";
        }
        echo ">".$fila[nombrebanco]."</option>\n";
    }
    echo "</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "<b>Total Pendiente: \$ $Total</b>";
    echo "</td></tr>";
   echo "<tr><td align=center colspan=6>";
   // Formulario de busqueda
   if ($up=="") $up = "1"; // 1 ASC 0 DESC
   $orden = array(
			"default" => "3",     //campo por defecto
			"1" => "bancos.dep�sitos.iddep�sito",
			"2" => "bancos.tipo_dep�sito.tipodep�sito",
			"3" => "bancos.dep�sitos.fechadep�sito",
			"4" => "bancos.dep�sitos.importedep"
   );
   $filtro = array(
			 "bancos.tipo_dep�sito.tipodep�sito" => "Tipo de dep�sito",
			 "bancos.dep�sitos.fechadep�sito" => "Fecha de dep�sito",
			 "bancos.dep�sitos.importedep" => "Importe"
   );
   //sentencia sql que sin ninguna condicion
   $sql_tmp = "SELECT bancos.dep�sitos.IdDep�sito,";
   $sql_tmp .= "bancos.tipo_dep�sito.TipoDep�sito,";
   $sql_tmp .= "bancos.dep�sitos.FechaDep�sito,";
   $sql_tmp .= "bancos.dep�sitos.ImporteDep,";
   $sql_tmp .= "bancos.dep�sitos.FechaCr�dito, ";
	$sql_tmp .= "bancos.dep�sitos.comentario ";
   $sql_tmp .= "FROM bancos.dep�sitos ";
   $sql_tmp .= "INNER JOIN bancos.tipo_dep�sito ";
   $sql_tmp .= "ON bancos.dep�sitos.IdTipoDep=bancos.tipo_dep�sito.IdTipoDep ";
   $sql_tmp .= "INNER JOIN tipo_banco ";
   $sql_tmp .= "ON bancos.dep�sitos.idbanco=tipo_banco.idbanco ";

   //prefijo para los links de paginas siguiente y anterior
//   $link_tmp = Array('idbanco'=>$Banco);
   $link_tmp = "";
   //condiciones extras de la consulta
   $where_tmp = "bancos.dep�sitos.FechaCr�dito IS NULL ";
   if ($Banco!="todos")
       $where_tmp .= "AND bancos.dep�sitos.IdBanco=$Banco";
   else
       $where_tmp .= "AND tipo_banco.activo=1";
   list($sql,$total_Prov,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
   echo "&nbsp;&nbsp;&nbsp;<input type=submit name='form_busqueda' value='   Buscar   '>";
   if (permisos_check("inicio","excel_cheques_pen"))
 		echo "&nbsp;&nbsp;<a target=_blank title='Bajar datos en un excel' href='". encode_link($_SERVER['SCRIPT_NAME'],array('download'=>1,"keyword"=>$_POST["keyword"],"filter"=>$_POST["filter"],'idbanco'=>$Banco)) ."'><img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' ></a>";
   
   echo "</td></tr>\n";
   echo "<tr><td colspan=6>\n";
   echo "<table align=center width=100%>";
   echo "<tr><td align=right>";
   echo "<input type=submit name=Modificar value='Modificar Datos'>&nbsp;&nbsp;&nbsp;";
   echo "</td><td align=center>\n";
   echo "<input type=submit name=Actualizar value='     Actualizar    '>&nbsp;&nbsp;&nbsp;";
   echo "</td><td align=left>\n";
   echo "</td></tr>\n";
   echo "</table></td></tr>\n";
   $result = $db->execute($sql) or die($db->ErrorMsg());
   $SubTotal = 0;
   echo "</table>";
   echo "<br>\n";
   echo "<table class='bordes' width='99%' align='center'>";
   echo "<tr >";
   echo "<td id=ma colspan=7 align=center>";
   echo "<table id=ma border=0 width=100%><tr>\n";
	echo "<td align=left width=70%>Dep�sitos Pendientes</td>\n";
	echo "<td align=right width=30%>$link_pagina&nbsp;</td>\n";
	echo "</tr></table>\n";
   echo "</td>";
   echo "</tr>";
   echo "<tr id=mo>";//bordercolor='#000000'
   echo "<td align=center>&nbsp;</td>";
   echo "<td align=center><a id=mo href='".encode_link("bancos_movi_deppen.php",Array('sort'=>1,'up'=>$up))."'>ID</a></td>";
   echo "<td align=center><a id=mo href='".encode_link("bancos_movi_deppen.php",Array('sort'=>2,'up'=>$up))."'>Tipo Dep�sito</a></td>";
   echo "<td align=center><a id=mo href='".encode_link("bancos_movi_deppen.php",Array('sort'=>3,'up'=>$up))."'>Fecha</a></td>";
   echo "<td align=center width=200 >Comentario</td>";
   echo "<td align=center><a id=mo href='".encode_link("bancos_movi_deppen.php",Array('sort'=>4,'up'=>$up))."'>Importe</a></td>";
   echo "<td align=center>Acreditado</td>";
   echo "</tr>\n";

    if ($download)
    {
   		ob_clean();
   		excel_header("dep_pendientes.xls");
   		echo "<html>";
			echo "<style type=\"text/css\">\n";
   		require("../../lib/estilos.css");
   		echo "</style>\n";
    	echo "<table>\n";
	    echo "<tr><td align=center colspan=5><b>DEPOSITOS PENDIENTES</b></td></tr>\n";
	    echo "<tr><td>&nbsp;</td></tr>\n";
    	echo "<tr><td>&nbsp;</td><td colspan=4><b>Banco:</b> $banco_nbre</td></tr>\n";
    	if ($keyword)
    		echo "<tr><td>&nbsp;</td><td colspan=4><b>Palabra buscada:</b> '$keyword' <i>en ".($filtro[$filter]?"campo ".$filtro[$filter]:"Todos los campos")."</i></td></tr>\n";
	    echo "<tr><td>&nbsp;</td><td colspan=4><b>Registros encontrados: ".$result->recordcount()."</b></td></tr>\n";
	    echo "<tr><td>&nbsp;</td><td colspan=4><b>Total Pendiente: \$ $Total</b></td></tr>\n";
//	    echo "<tr><td>&nbsp;</td><td colspan=4><b>Total Busqueda: \$ $SubTotal</b></td></tr>\n";
	    echo "<tr><td>&nbsp;</td></tr>\n";
	    echo "<tr align=center bgcolor=#000000 style='color:#E9E9E9;font-weight: bold;' >\n";
	    echo "<td align=center>ID</td>\n";
	    echo "<td align=center>Tipo Dep�sito</td>\n";
	    echo "<td align=center>Fecha</td>\n";
	    echo "<td align=center>Comentario</td>\n";
	    echo "<td align=center>Importe</td>\n";
	    echo "</tr>\n";
    }
    $i=0;
	 //filas pares
	 $style[0]='bgcolor=white';
	 //filas impares
	 $style[1]='bgcolor=#99CCFF';

   
   while ($fila = $result->fetchrow()) {
   	
        $SubTotal += $fila[importedep];
   			if ($download)
   			{
        echo "<tr {$style[$i=($i+1)%2]}>\n";//bordercolor='#000000'
        echo "<td align=center>".$fila[iddep�sito]."</td>\n";
        echo "<td align=left>".$fila[tipodep�sito]."</td>\n";
        echo "<td align=center>".Fecha($fila[fechadep�sito])."</td>\n";
   	  	echo "<td align=center>".(($fila[comentario])?$fila[comentario]:"&nbsp;")."</td>\n";        
        echo "<td ".excel_style("$")."><b> ".formato_money($fila[importedep])."</b></td>\n";
        echo "</tr>\n";
   			}
   			else {
        echo "<tr bgcolor=$bgcolor_out>\n";//bordercolor='#000000'
        echo "<td align=center><input type=radio name=Modificar_Deposito_Numero value='".$fila[iddep�sito]."'></td>";
        echo "<td align=center>".$fila[iddep�sito]."</td>\n";
        echo "<td align=left>".$fila[tipodep�sito]."</td>\n";
        echo "<td align=center>".Fecha($fila[fechadep�sito])."</td>\n";
   	  	echo "<td align=center>".(($fila[comentario])?$fila[comentario]:"&nbsp;")."</td>\n";        
        echo "<td align=right>\$ ".formato_money($fila[importedep])."</td>\n";
        echo "<td align=center>";
        echo "<input type=text size=10 maxlength=10 name=Fecha_Depositos_Pendientes_ND".$fila[iddep�sito]." title='Ingrese la fecha y\nhaga click en Actualizar'>";
        echo link_calendario("Fecha_Depositos_Pendientes_ND".$fila[iddep�sito]);
        echo "</td>\n";
        echo "</tr>\n";
   			}
   }
   if (!$download)
   {
   echo "<tr bgcolor=$bgcolor3><td colspan=7 align=center><b>Subtotal Pendiente: \$ ".formato_money($SubTotal)."</b></td></tr>";
   echo "</table></form>\n";
   }
   else 
   {
	    echo "<tr bgcolor=#000000 style='color:#E9E9E9;font-weight: bold;'><td colspan=5 align=center><b>Subtotal B�squeda: \$ ".formato_money($SubTotal)."</b></td></tr>\n";   	
    	echo "</table>\n";
    	echo "<table><tr><td>&nbsp;</td></tr></table>\n";
 	
   }
?>