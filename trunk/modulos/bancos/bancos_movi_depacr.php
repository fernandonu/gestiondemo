<?
/*
$Author: nazabal $
$Revision: 1.4 $
$Date: 2005/06/24 20:28:16 $
*/
// Cabecera Configuracion requerida
require_once("../../config.php");

$_POST["idbanco"] = $_POST["Mov_Depositos_Acreditados_Banco"];
variables_form_busqueda("movi_depacr",array("idbanco" => ""));
$Banco = $idbanco or $Banco = 4;

if ($download=$parametros['download'])
{
	ob_start();
	$itemspp=1000000;//para que la busqueda traiga todos los resultados para el excel
	$page=0;
}
echo $html_header;

// Variables
//$sort = $_POST["sort"] or $sort = $parametros["sort"];
//if (!$sort) $sort=1;
// Cuerpo de la pagina
if ($_POST["Modificar_Deposito_Guardar"]) {
   $fecha_deposito = $_POST[Modificar_Deposito_Fecha_Deposito];
   $fecha_credito = $_POST[Modificar_Deposito_Fecha_Credito];
   $idbanco = $_POST[Modificar_Deposito_IdBanco];
   $iddep = $_POST[Modificar_Deposito_IdDeposito];
   $idtipo = $_POST[Modificar_Deposito_Tipo];
   $importe = $_POST[Modificar_Deposito_Importe];
   $volver = $_POST[Modificar_Deposito_Volver];
   $coment=$_POST['coment'];
   if ($fecha_deposito == "") {
        $fecha_deposito = "NULL";
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
   if ($importe == "") {
        Error("Falta ingresar el Importe");
   }
   elseif (!es_numero($importe)) {
        Error("El Importe ingresado no es válido");
   }

   if (!$error) {
        $sql = "UPDATE bancos.depósitos SET ";
        $sql .= "idbanco=$idbanco,";
        $sql .= "idtipodep=$idtipo,";
        $sql .= "fechadepósito=$fecha_deposito,";
        $sql .= "fechacrédito=$fecha_credito,";
        $sql .= "importedep=$importe, ";
        $sql .= "comentario='$coment' ";
        $sql .= "WHERE iddepósito=$iddep";
        $result = $db->execute($sql) or die($db->ErrorMsg());
		Aviso("Los datos se ingresaron correctamente");
   }
}
if ($_POST[Modificar]) {
    $iddeposito = $_POST["Modificar_Deposito_Id"];
    if (es_numero($iddeposito)) {
       $sql = "SELECT * FROM bancos.depósitos WHERE iddepósito=$iddeposito";
       $result = $db->execute($sql) or die($db->ErrorMsg());
       $fila = $result->fetchrow();
       $banco = $fila["idbanco"];
       $idtipo = $fila["idtipodep"];
       $fecha_dep = Fecha($fila["fechadepósito"]);
       $fecha_cre = Fecha($fila["fechacrédito"]);
       $importe = $fila["importedep"];
       $coment = $fila["comentario"];
       echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
       echo "<form action=bancos_movi_depacr.php method=post>\n";
       echo "<input type=hidden name=Modificar_Deposito_IdDeposito value='$iddeposito'><br>\n";
       echo "<table align=center cellpadding=5 cellspacing=0 class='bordes' >\n";//bordercolor='$bgcolor3'
       echo "<tr bordercolor='#000000'><td id=mo align=center>Modificación del Depósito</td></tr>";
       echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0 bgcolor=$bgcolor_out>";
       echo "<tr><td align=right><b>Banco</b></td>";
       echo "<td align=left>";
       echo "<select name=Modificar_Deposito_IdBanco>\n";
       $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
       $result = $db->execute($sql) or die($db->ErrorMsg());
       while ($fila = $result->fetchrow()) {
			echo "<option value=".$fila[idbanco];
            if ($fila[idbanco] == $banco) echo " selected";
            echo ">".$fila[nombrebanco]."</option>\n";
       }
       echo "</select></td></tr>\n";
       echo "<tr><td align=right><b>Tipo de Depósito</b></td>";
       echo "<td align=left>";
       echo "<select name=Modificar_Deposito_Tipo>\n";
       $sql = "SELECT * FROM bancos.tipo_depósito";
       $result = $db->execute($sql) or die($db->ErrorMsg());
       while ($fila = $result->fetchrow()) {
           echo "<option value=".$fila[idtipodep];
           if ($fila["idtipodep"] == $idtipo) echo " selected";
           echo ">".$fila[tipodepósito]."</option>\n";
        }
        echo "</select></td></tr>\n";
        echo "<tr><td align=right><b>Fecha Depósito</b></td>";
        echo "<td align=left>";
        echo "<input type=text size=10 maxlength=10 name=Modificar_Deposito_Fecha_Deposito value='".$fecha_dep."' title='Ingrese la fecha de depósito'>";
		echo link_calendario("Modificar_Deposito_Fecha_Deposito");
        echo "</td></tr>\n";
        echo "<tr><td align=right><b>Fecha Crédito</b></td>";
        echo "<td align=left>";
        echo "<input type=text size=10 maxlength=10 name=Modificar_Deposito_Fecha_Credito value='".$fecha_cre."' title='Ingrese la fecha de crédito del depósito'>";
        echo link_calendario("Modificar_Deposito_Fecha_Credito");
        echo "</td></tr>\n";
		echo "<tr><td align=right><b>Importe</b>\n";
        echo "</td><td>";
        echo "<input type=text name=Modificar_Deposito_Importe value='".$importe."' size=22 maxlength=50>&nbsp;";
        echo "</td></tr>\n";
   	  echo "<tr><td align='right'><b>Comentarios</td>";
		  echo "<td><textarea name='coment' cols=25 wrap='FISICAL' >$coment</textarea></td></tr>";
        echo "<tr><td align=center colspan=2>\n";
        echo "<input type=hidden name=Modificar_Deposito_Volver value='$cmd'>\n";
        echo "<input type=submit name=Modificar_Deposito_Guardar value='Guardar'>&nbsp;&nbsp;&nbsp;\n";
        echo "<input type=button name=Volver value='   Volver   ' OnClick=\"window.location='".encode_link("bancos_movi_depacr.php",Array('idbanco'=>$banco))."';\">\n";
        echo "</td></tr>\n";
        echo "</table>";
        echo "</td></tr>\n";
        echo "</table>\n";
        exit();
    }
}
    echo "<form action=bancos_movi_depacr.php method=post>\n";
    //Total
    $sql = "SELECT sum(ImporteDep) AS total FROM bancos.depósitos WHERE FechaCrédito IS NOT NULL";
    $result = $db->execute($sql) or die($db->ErrorMsg());
    $res_tmp = $result->fetchrow();
    $Total = formato_money($res_tmp[total]);

    //Datos
    echo "<table align=center cellpadding=5 cellspacing=0 border=0 >";//bordercolor=$bgcolor3
    echo "<tr><td colspan=6 align=center><b>Banco</b>";
    $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
    $result = $db->execute($sql) or die($db->ErrorMsg());
    echo "<select name=Mov_Depositos_Acreditados_Banco OnChange=\"document.forms[0].submit();\">\n";
    echo "<option value='todos' ";
    if ($Banco=="todos")
       echo " selected";
    echo ">Todos</option>\n";
    $banco_nbre= "Todos";
    while ($fila = $result->fetchrow()) {
        echo "<option value=".$fila[idbanco];
        if ($fila[idbanco] == $Banco)
        {
            echo " selected";
            $banco_nbre= $fila[nombrebanco];
       }
        echo ">".$fila[nombrebanco]."</option>\n";
    }
    echo "</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "<b>Total Acreditado: \$ $Total</b>";
    echo "</td></tr>";
   echo "<tr><td align=center colspan=6>";
   // Formulario de busqueda
   $orden = array(
			"default" => "5",     //campo por defecto
			"default_up" => "0",     //orden por defecto
			"1" => "bancos.depósitos.iddepósito",
			"2" => "bancos.tipo_depósito.tipodepósito",
			"3" => "bancos.depósitos.fechadepósito",
			"4" => "bancos.depósitos.importedep",
			"5" => "bancos.depósitos.fechacrédito"
   );
   $filtro = array(
			 "bancos.tipo_depósito.tipodepósito" => "Tipo de depósito",
			 "bancos.depósitos.fechadepósito" => "Fecha de depósito",
			 "bancos.depósitos.fechacrédito" => "Fecha de acreditación",
			 "bancos.depósitos.comentario" => "Comentarios",
			 "bancos.depósitos.importedep" => "Importe"
   );
   //sentencia sql que sin ninguna condicion
   $sql_tmp = "SELECT bancos.depósitos.IdDepósito,";
   $sql_tmp .= "bancos.tipo_depósito.TipoDepósito,";
   $sql_tmp .= "bancos.depósitos.FechaDepósito,";
   $sql_tmp .= "bancos.depósitos.ImporteDep,";
   $sql_tmp .= "bancos.depósitos.FechaCrédito, ";
   $sql_tmp .= "bancos.depósitos.comentario ";
   $sql_tmp .= "FROM bancos.depósitos ";
   $sql_tmp .= "INNER JOIN bancos.tipo_depósito ";
   $sql_tmp .= "ON bancos.depósitos.IdTipoDep=bancos.tipo_depósito.IdTipoDep ";
   $sql_tmp .= "INNER JOIN tipo_banco ";
   $sql_tmp .= "ON bancos.depósitos.idbanco=tipo_banco.idbanco ";
   //prefijo para los links de paginas siguiente y anterior
//   $link_tmp = array('idbanco'=>$Banco);
   $link_tmp = "";
   //condiciones extras de la consulta
   $where_tmp = "bancos.depósitos.FechaCrédito IS NOT NULL ";
   if ($Banco!="todos")
       $where_tmp .= "AND bancos.depósitos.IdBanco=$Banco";
   else
       $where_tmp .= "AND tipo_banco.activo=1";
   list($sql,$total_Prov,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
   echo "&nbsp;&nbsp;&nbsp;<input type=submit name='form_busqueda' value='   Buscar   '>";
   if (permisos_check("inicio","excel_cheques_pen"))
 		echo "&nbsp;&nbsp;<a target=_blank title='Bajar datos en un excel' href='". encode_link($_SERVER['SCRIPT_NAME'],array('download'=>1,"keyword"=>$_POST["keyword"],"filter"=>$_POST["filter"],'idbanco'=>$Banco)) ."'><img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' ></a>";
   
   echo "</td></tr>\n";
   echo "<tr><td colspan=6 align=center>";
   echo "<input type=submit name=Modificar value='Modificar Datos'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
   echo "</td></tr>\n";
//   echo $sql;
   $result = $db->execute($sql) or die($db->ErrorMsg());
   $SubTotal = 0;
   echo "</table>";
   echo "<br>\n";
    echo "<table class='bordes' width='99%' align=center cellpadding=2 cellspacing=2>";
   echo "<tr >";
   echo "<td id=ma colspan=7 align=center>";
    echo "<table id=ma border=0 width=100%><tr>\n";
	echo "<td align=left width=70%>Depósitos Acreditados</td>\n";
	echo "<td align=right width=30%>$link_pagina&nbsp;</td>\n";
	echo "</tr></table>\n";
	echo "</td>";
   echo "</tr>";
   echo "<tr id=mo>";//bordercolor='#000000'
   echo "<td align=center>&nbsp;</td>";
   echo "<td align=center><a id=ma href='".encode_link("bancos_movi_depacr.php",Array('sort'=>1,'up'=>$up))."'>ID</a></td>";
   echo "<td align=center><a id=ma href='".encode_link("bancos_movi_depacr.php",Array('sort'=>2,'up'=>$up))."'>Tipo Depósito</a></td>";
   echo "<td align=center><a id=ma href='".encode_link("bancos_movi_depacr.php",Array('sort'=>3,'up'=>$up))."'>Fecha</a></td>";
   echo "<td align=center width=200 >Comentario</td>";
   echo "<td align=center><a id=ma href='".encode_link("bancos_movi_depacr.php",Array('sort'=>4,'up'=>$up))."'>Importe</a></td>";
   echo "<td align=center><a id=ma href='".encode_link("bancos_movi_depacr.php",Array('sort'=>5,'up'=>$up))."'>Acreditado</a></td>";
   echo "</tr>\n";
   
    if ($download)
    {
   		ob_clean();
   		excel_header("dep_acreditados.xls");
   		echo "<html>";
			echo "<style type=\"text/css\">\n";
   		require("../../lib/estilos.css");
   		echo "</style>\n";
    	echo "<table>\n";
	    echo "<tr><td align=center colspan=5><b>DEPOSITOS ACREDITADOS</b></td></tr>\n";
	    echo "<tr><td>&nbsp;</td></tr>\n";
    	echo "<tr><td>&nbsp;</td><td colspan=4><b>Banco:</b> $banco_nbre</td></tr>\n";
    	if ($keyword)
    		echo "<tr><td>&nbsp;</td><td colspan=4><b>Palabra buscada:</b> '$keyword' <i>en ".($filtro[$filter]?"campo ".$filtro[$filter]:"Todos los campos")."</i></td></tr>\n";
	    echo "<tr><td>&nbsp;</td><td colspan=4><b>Registros encontrados: ".$result->recordcount()."</b></td></tr>\n";
	    echo "<tr><td>&nbsp;</td><td colspan=4><b>Total Acreditado: \$ $Total</b></td></tr>\n";
//	    echo "<tr><td>&nbsp;</td><td colspan=4><b>Total Busqueda: \$ $SubTotal</b></td></tr>\n";
	    echo "<tr><td>&nbsp;</td></tr>\n";
	    echo "<tr align=center bgcolor=#000000 style='color:#E9E9E9;font-weight: bold;' >\n";
	    echo "<td align=center>ID</td>\n";
	    echo "<td align=center>Tipo Depósito</td>\n";
	    echo "<td align=center>Fecha</td>\n";
	    echo "<td align=center>Comentario</td>\n";
	    echo "<td align=center>Importe</td>\n";
	    echo "<td align=center>Acreditado</td>\n";
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
        echo "<td align=center>".$fila[iddepósito]."</td>\n";
        echo "<td align=left>".$fila[tipodepósito]."</td>\n";
        echo "<td align=center>".Fecha($fila[fechadepósito])."</td>\n";
   	  	echo "<td align=center>".(($fila[comentario])?$fila[comentario]:"&nbsp;")."</td>\n";        
        echo "<td ".excel_style("$")."><b> ".formato_money($fila[importedep])."</b></td>\n";
        echo "<td align=center>".Fecha($fila[fechacrédito])."</td>\n";
        echo "</tr>\n";
   			}
   			else {
        echo "<tr bgcolor=$bgcolor_out>\n"; //bordercolor='#000000'
        echo "<td align=center><input type=radio name=Modificar_Deposito_Id value='".$fila[iddepósito]."'></td>";
        echo "<td align=center>".$fila[iddepósito]."</td>\n";
        echo "<td align=left>".$fila[tipodepósito]."</td>\n";
        echo "<td align=center>".Fecha($fila[fechadepósito])."</td>\n";
	   	  echo "<td align=center>".(($fila[comentario])?$fila[comentario]:"&nbsp;")."</td>\n";        
        echo "<td align=right>\$ ".formato_money($fila[importedep])."</td>\n";
        echo "<td align=center>".Fecha($fila[fechacrédito])."</td>\n";
        echo "</tr>\n";
   			}
   }
	 if ($download)
   {
    echo "<tr bgcolor=#000000 style='color:#E9E9E9;font-weight: bold;'><td colspan=5 align=center><b>Subtotal Búsqueda: \$ ".formato_money($SubTotal)."</b></td></tr>\n";   	
   	echo "</table>\n";
   	echo "<table><tr><td>&nbsp;</td></tr></table>\n";
   }
   else
   {
	   echo "<tr bgcolor=$bgcolor3><td colspan=7 align=center><b>Subtotal Acreditado: \$ ".formato_money($SubTotal)."</b></td></tr>";
	   echo "</table></form>\n";
   }
?>