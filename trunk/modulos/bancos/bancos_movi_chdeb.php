<?
/*
$Author: marco_canderle $
$Revision: 1.37 $
$Date: 2005/10/18 21:20:53 $
*/
require_once("../../config.php");

// para el select del concepto y plan	
function cuenta_bancos($nro_cuenta){
	global $db;
	global $concepto_cuenta,$parametros;
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
variables_form_busqueda("movi_chdeb");
if ($download=$parametros['download'])
{
	ob_start();
	$itemspp=1000000;//para que la buesqueda traiga todos los resultados para el excel
	$page=0;
}
echo $html_header;
// Variables
//$up = $_POST["up"] or $up = $parametros["up"];
//$sort = $_POST["sort"] or $sort = $parametros["sort"] or $sort = "";
//if (!$sort) $sort=1;
if ($_POST["Modificacion_Cheque_Guardar"]) {
    $fecha_debito = $_POST['Modificacion_Cheque_Fecha_Debito'];
    $numero_nuevo = $_POST['Modificacion_Cheque_Numero'];
    $numero=$_POST['Modificacion_Cheque_Numero_Old']; //numero del cheque antes de cambiar
    //$numero = $_POST[Modificacion_Cheque_Numero];
    $importe = $_POST['Modificacion_Cheque_Importe'];
    $comentarios = $_POST['Modificacion_Cheque_Comentarios'];
   // $idbanco = $_POST[Modificacion_Cheque_IdBanco];
    $idbanco1 = $_POST['Modificacion_id_banco_Old'];
    $idbanco = $_POST['Modificacion_Cheque_IdBanco'];
    $proveedor=$_POST['Modificacion_Cheque_IdProveedor'];
    $fech_emis=$_POST['Modificacion_Cheque_Fecha_Emision'];
    $fech_venc=$_POST['Modificacion_Cheque_Fecha_Vencimiento'];
    //recupero concepto y plan
	$nro_cuenta=$_POST['cuentas'];
	
    if ($fecha_debito == "") {
        $fecha_debito = "NULL";
    }
    else {
        list($d,$m,$a) = explode("/",$fecha_debito);
        if (FechaOk($fecha_debito)) {
            $fecha_debito = "'$a-$m-$d'";
        }
        else {
            Error("La fecha de Débito ingresada es inválida");
        }
    }
    if ($importe == "") {
        Error("Falta ingresar el Importe");
    }
    elseif (!es_numero($importe)) {
        Error("El Importe ingresado es inválido");
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
        $sql = "UPDATE bancos.cheques SET ";
        $sql .= "FechaDébCh=$fecha_debito,";
        $sql .= "ImporteCh=$importe,";
        $sql .= "idprov=$proveedor,";
        $sql .= "NúmeroCh=$numero_nuevo,";
        $sql .= "fechaemich=$fech_emis,";
        $sql .= "fechavtoch=$fech_venc,";
        $sql .= "idbanco=$idbanco,";
        $sql .= "Comentarios='$comentarios',";
        $sql .= "numero_cuenta=$nro_cuenta ";
        $sql .= " WHERE NúmeroCh=$numero AND idbanco=$idbanco1";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        Aviso("Los datos se ingresaron correctamente");

 }
else {
      $_POST['Modificar']=1;
      $_POST["Modificar_Cheque_Numero"]=$numero;
      $_POST['id_banco']=$idbanco1;
}
}

	

if ($_POST[Modificar] || $parametros['pagina']=="mail" || $parametros['pagina']=="imputaciones") {
	//para recuperar el numero de cheque
	
	 
	
	if ($parametros['pagina']=="mail" || $parametros['pagina']=="imputaciones") {
		$numero=$parametros["Modificar_Cheque_Numero"];
		$id_banco_nuevo=$parametros['banco'];
		$disabled_mostrar = $parametros["disabled"];
	}
	else {
		$numero = $_POST["Modificar_Cheque_Numero"];
		$id_banco_nuevo=$_POST['id_banco'];
	}
	 
  
    if (es_numero($numero)) {
        $sql = "SELECT ";
        $sql .= "bancos.cheques.IdBanco,";
        $sql .= "bancos.cheques.idprov,";
        $sql .= "bancos.cheques.FechaEmiCh,";
        $sql .= "bancos.cheques.FechaVtoCh,";
        $sql .= "bancos.cheques.FechaDébCh,";
        $sql .= "bancos.cheques.ImporteCh,";
        $sql .= "bancos.cheques.Comentarios, ";
        $sql .= "bancos.cheques.numero_cuenta ";
        $sql .= "FROM bancos.cheques ";
        $sql .= "WHERE bancos.cheques.NúmeroCh=$numero";
 	if($disabled_mostrar != "disabled")
       $sql .= " AND bancos.cheques.idbanco=$id_banco_nuevo";
        $result = $db->execute($sql) or die($db->ErrorMsg().$sql);
        list(
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
            $mod_importe = formato_money($mod_importe);
        echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
        
        /***********************************************
		   Traemos y mostramos el Log de la OC
		************************************************/
		//left join por si alguna vez se elimina el usuario
		$q="select * from log_cheques_debitados where númeroch=$numero";
		$log=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");
		echo "<form action=bancos_movi_chdeb.php method=post>\n";
        echo "<table align=center cellpadding=5 cellspacing=0 class='bordes'>\n";//bordercolor='$bgcolor3'
	
   
    if($disabled_mostrar == "disabled")
       	echo "<tr ><td id=mo align=center>Datos del Cheque</td></tr>";
    else   
        echo "<tr ><td id=mo align=center>Modificación de datos del Cheque</td></tr>";
        echo "<tr ><td align=center>";
        echo "<table cellspacing=5 border=0 bgcolor='$bgcolor_out' >";//bordercolor='$bgcolor3'
        echo "<tr><td align=right><b>Banco</b></td>";
        echo "<td align=left>";
        $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        echo "<select name=Modificacion_Cheque_IdBanco $disabled_mostrar>\n";
        while ($fila = $result->fetchrow()) {
        	echo "<option value=".$fila[idbanco];
            if ($fila[idbanco] == $mod_idbanco)
                echo " selected";
            echo ">".$fila[nombrebanco]."</option>\n";
        }
        echo "</select>\n";
        echo "</td></tr>\n";
        echo "<tr><td align=right><b>Proveedor</b></td>";
        echo "<td align=left>";
        echo "<select name=Modificacion_Cheque_IdProveedor $disabled_mostrar>\n";
        $sql = "SELECT id_proveedor, razon_social FROM general.proveedor ORDER BY razon_social";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        while ($fila = $result->fetchrow()) {
            echo "<option value='".$fila[id_proveedor]."'";
            if ($fila[id_proveedor] == "$mod_proveedor") echo " selected";
            echo ">".$fila[razon_social]."</option>\n";
        }
        echo "</select></td></tr>\n";
        echo "<tr><td align=right><b>Fecha de Emisión</b></td>";
        echo "<td align=left>";
        echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Emision value='$mod_fecha_e' title='Ingrese la fecha de emisión del cheque' $disabled_mostrar>";
	if($disabled_mostrar != "disabled")
		echo link_calendario("Modificacion_Cheque_Fecha_Emision");
        echo "</td>\n";
        echo "</tr>\n";
        echo "<tr><td align=right><b>Fecha de Vencimiento</b></td>";
        echo "<td align=left>";
        echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Vencimiento value='$mod_fecha_v' title='Ingrese la fecha de vencimiento del cheque' $disabled_mostrar>";
	if($disabled_mostrar != "disabled")
		echo link_calendario("Modificacion_Cheque_Fecha_Vencimiento");
        echo "</td></tr>\n";
        echo "<tr><td align=right><b>Fecha Débito</b></td>";
        echo "<td align=left>";
        echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Debito value='$mod_fecha_d' title='Ingrese la fecha de débito del cheque' $disabled_mostrar>";
	if($disabled_mostrar != "disabled")
        echo link_calendario("Modificacion_Cheque_Fecha_Debito");
        echo "</td></tr>\n";
      
        echo "<tr><td align=right><b>Número</b>\n";
        echo "</td><td align=left>";
        echo "<input type=hidden name=Modificacion_Cheque_Numero_Old value='$numero' $disabled_mostrar>";

        //hidden para guardar el valor de id baco y numero por si cambian
        echo "<input type=hidden name=Modificacion_id_banco_Old value='$mod_idbanco'>";
        echo "<input type=text name=Modificacion_Cheque_Numero value='$numero' size=10 maxlength=50 $disabled_mostrar>";
        echo "</td></tr>\n";
        echo "<tr><td align=right><b>Importe</b>\n";
        echo "</td><td align=left>";
        echo "<input type=text name=Modificacion_Cheque_Importe value='$mod_importe' size=10 maxlength=50 $disabled_mostrar>&nbsp;";
        echo "</td></tr>\n";
// concepto y plan cuando se modifica un debito
 	if($disabled_mostrar != "disabled")
       cuenta_bancos($mod_numero_cuenta);
///////////////////////////////////////////////           
        echo "<tr><td align=right valign=top><b>Comentarios</b>\n";
        echo "</td><td align=left>";
        echo "<textarea name=Modificacion_Cheque_Comentarios cols=30 rows=3 $disabled_mostrar>$mod_comentarios</textarea>";
        echo "</td></tr>\n";
        echo "<tr><td align=center colspan=2>\n";
        echo "<table border=0 width=100%>\n";
        echo "<tr><td colspan=2 align=center>\n";
        echo "<input type=hidden name=Modificacion_Cheque_Volver value='$cmd'>\n";
	if($disabled_mostrar == "disabled"){
        echo "<input type=button name=cerrar value='Cerrar' onclick='window.close();'>\n";
	 } 
	 else 
	 {
			
	 
        echo "<input type=submit name=Modificacion_Cheque_Guardar value='Guardar'>\n";
        echo "<input type=button name=Volver value='   Volver   ' OnClick=\"window.location='".encode_link("bancos_movi_chdeb.php",array('idbanco'=>$mod_idbanco))."';\">\n";	 
	  	 }
        echo "</form>\n";
        echo "</td></tr>\n";
        echo "</table>";
        echo "</td></tr>\n";
        echo "</table>";
        echo "</td></tr>\n";
        echo "</table><br>\n";
        exit();
    }
}
    echo "<form action=bancos_movi_chdeb.php method=post>\n";
    //echo "<input type=hidden name=mode value=forms>\n";
    //echo "<input type=hidden name=cmd value=Mov_Cheques_Debitados>\n";
    if (!$_POST[Mov_Cheques_Debitados_Banco]) {
       if ($parametros[idbanco] or $idbanco) {
           $Banco = $parametros[idbanco] or $Banco=$idbanco;
       }
       else {
            $Banco=4;  // Banco por defecto
       }
    }
    else {
        $Banco=$_POST[Mov_Cheques_Debitados_Banco];
    }
    //Total
    $sql = "SELECT sum(ImporteCh) AS total FROM bancos.cheques WHERE FechaDébCh IS NOT NULL";
    $result = $db->execute($sql) or die($db->ErrorMsg());
    $res_tmp = $result->fetchrow();
    $Total = formato_money($res_tmp[total]);

    //Datos
    $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
    $result = $db->execute($sql) or die($db->ErrorMsg());
    echo "<table align=center cellpadding=5 cellspacing=0 border=0 >";
    echo "<tr><td colspan=7 align=center><b>Banco</b>";
    echo "<select name=Mov_Cheques_Debitados_Banco OnChange=\"document.forms[0].submit();\">\n";
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
            echo " selected";
            $banco_nbre=$fila[nombrebanco];
        }
        echo ">".$fila[nombrebanco]."</option>\n";
    }
    echo "</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "<b>Total Debitado: \$ $Total</b>";
    echo "</td></tr>";
   echo "<tr><td align=center colspan=7>";
   // Formulario de busqueda
   // variables que contienen los datos actuales de la busqueda
//   $page = $parametros["page"] or $page = 0;                                //pagina actual
//   $filter = $_POST["filter"] or $filter = $parametros["filter"];           //campo por el que se esta filtrando
//   $keyword = $_POST["keyword"] or $keyword = $parametros["keyword"];       //palabra clave
//   if ($parameros["up"]=="") $parametros["up"] = "0";   // 1 ASC 0 DESC
   if ($up=="") $up = "0";   // 1 ASC 0 DESC
   $orden = array(
			"default" => "2",     //campo por defecto
			"1" => "cheques.fechavtoch",
			"2" => "cheques.fechadébch",
			"3" => "cheques.númeroch",
			"4" => "cheques.importech",
			"5" => "proveedor.razon_social"
   );
   $filtro = array(
			 "proveedor.razon_social" => "Proveedor",
			 "cheques.fechaemich" => "Fecha de emisión",
			 "cheques.fechavtoch" => "Fecha de vencimiento",
			 "cheques.fechaprev" => "Fecha prevista",
			 "cheques.fechadébch" => "Fecha de débito",
			 "cheques.númeroch" => "Número",
			 "cheques.importech" => "Importe",
			 "cheques.comentarios" => "Comentarios"
   );

   // CANMBIOS bancos.cheques.idbanco
   //sentencia sql que sin ninguna condicion
	$sql_tmp = "SELECT bancos.cheques.idbanco, cheques.FechaVtoCh,";
   $sql_tmp .= "cheques.FechaDébCh,";
	$sql_tmp .= "cheques.NúmeroCh,";
   $sql_tmp .= "cheques.ImporteCh,";
	$sql_tmp .= "proveedor.razon_social ";
	$sql_tmp .= "FROM cheques ";
	$sql_tmp .= "INNER JOIN proveedor ";
	$sql_tmp .= "ON cheques.IdProv=proveedor.id_proveedor ";
    $sql_tmp .= "INNER JOIN tipo_banco ";
	$sql_tmp .= "ON cheques.idbanco=tipo_banco.idbanco ";

   //prefijo para los links de paginas siguiente y anterior
   $link_tmp = array("idbanco"=>$Banco);
   //condiciones extras de la consulta
   $where_tmp = "cheques.FechaDébCh IS NOT NULL ";
   if ($Banco!="todos")
	   $where_tmp .= "AND cheques.IdBanco=$Banco";
   else
	   $where_tmp .= "AND tipo_banco.activo=1";
   list($sql,$total_Prov,$link_pagina,$up2) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
   $result = $db->execute($sql) or die($db->ErrorMsg());
   $SubTotal = 0;
   
   echo "&nbsp;&nbsp;&nbsp;<input type=submit name='form_busqueda' value='   Buscar   '>";

   if (permisos_check("inicio","excel_cheques_pen"))
 		echo "&nbsp;&nbsp;<a target=_blank title='Bajar datos en un excel' href='". encode_link($_SERVER['SCRIPT_NAME'],array('download'=>1,"keyword"=>$_POST["keyword"],"filter"=>$_POST["filter"],'idbanco'=>$Banco)) ."'><img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' ></a>";
   
   echo "</td></tr>\n";
	 echo "<tr><td colspan=7 align=center>";
   echo "<input type=submit name=Modificar value='Modificar Datos'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
   echo "</td></tr>\n";
   echo "</table>\n";
   echo "<br>\n";
   echo "<table class='bordes' width='99%' align='center'>";
   echo "<tr id=ma >";
   echo "<td colspan=7 align=center>";
   echo "<table id=ma border=0 width=100%><tr>";
   echo "<td align=center width=70%>Cheques Debitados</td>";
   echo "<td align=center width=30%>$link_pagina&nbsp;</td>";
   echo "</tr></table>";
   echo "</td></tr>";
    echo "<tr id=mo>";
    echo "<td align=center>&nbsp;</td>";
    echo "<td align=center><a id=ma href='".encode_link("bancos_movi_chdeb.php",Array('sort'=>1,'up'=>$up2,'idbanco'=>$Banco))."'>Vencimiento</a></td>";
    echo "<td align=center><a id=ma href='".encode_link("bancos_movi_chdeb.php",Array('sort'=>2,'up'=>$up2,'idbanco'=>$Banco))."'>Débito</a></td>";
    echo "<td align=center><a id=ma href='".encode_link("bancos_movi_chdeb.php",Array('sort'=>3,'up'=>$up2,'idbanco'=>$Banco))."'>Número</a></td>";
    echo "<td align=center><a id=ma href='".encode_link("bancos_movi_chdeb.php",Array('sort'=>4,'up'=>$up2,'idbanco'=>$Banco))."'>Importe</a></td>";
    echo "<td align=center><a id=ma href='".encode_link("bancos_movi_chdeb.php",Array('sort'=>5,'up'=>$up2,'idbanco'=>$Banco))."'>Proveedor</a></td>";
    echo "<td align=center>Días</a></td>";
    echo "</tr>\n";
    
    if ($download)
    {
   		ob_clean();
   		excel_header("cheques_deb.xls");
   		echo "<html xmlns:v=\"urn:schemas-microsoft-com:vml\"\nxmlns:o=\"urn:schemas-microsoft-com:office:office\"\nxmlns:x=\"urn:schemas-microsoft-com:office:excel\"\nxmlns=\"http://www.w3.org/TR/REC-html40\">\n";
			echo "<style type=\"text/css\">\n";
			echo "@page {margin:.39in .39in .39in .39in;}\n";
   		require("../../lib/estilos.css");
   		echo "</style>\n";
    	echo "<table>\n";
	    echo "<tr><td align=center colspan=6><b>CHEQUES DEBITADOS</b></td></tr>\n";
	    echo "<tr><td>&nbsp;</td></tr>\n";
    	echo "<tr><td>&nbsp;</td><td colspan=4><b>Banco:</b> $banco_nbre</td></tr>\n";
    	if ($keyword)
    		echo "<tr><td>&nbsp;</td><td colspan=4><b>Palabra buscada:</b> '$keyword' <i>en ".($filtro[$filter]?"campo ".$filtro[$filter]:"Todos los campos")."</i></td></tr>\n";
	    echo "<tr><td>&nbsp;</td><td colspan=4><b>Registros encontrados:</b>".$result->recordcount()."</td></tr>\n";
		 	echo "<tr><td>&nbsp;</td><td colspan=4><b>Total Debitado: \$ $Total</b></td></tr>\n";
	    echo "<tr><td>&nbsp;</td></tr>\n";
	    echo "<tr align=center bgcolor=#000000 style='color:#E9E9E9;font-weight: bold;' >\n";
	    echo "<td align=center>Vencimiento</td>\n";
	    echo "<td align=center>Débito</td>\n";
	    echo "<td align=center>Número</td>\n";
	    echo "<td align=center>Importe</td>\n";
	    echo "<td align=center>Proveedor</td>\n";
	    echo "<td align=center>Días</td>\n";
	    echo "</tr>\n";
    }
    $i=0;
	 //filas pares
	 $style[0]='bgcolor=white';
	 //filas impares
	 $style[1]='bgcolor=#99CCFF';

    echo "<input type='hidden' name=id_banco value=3>";
    while ($fila = $result->fetchrow()) {
        $SubTotal += $fila[importech];
        list($aa,$mm,$dd) = explode("-",$fila[fechadébch]);
        $fecha1 = mktime(0,0,0,$mm,$dd,$aa);
        list($aa,$mm,$dd) = explode("-",$fila[fechavtoch]);
        $fecha2 = mktime(0,0,0,$mm,$dd,$aa);
        $Dias=($fecha1-$fecha2) / 86400;
        if (!$download)
        {
        echo "<tr bgcolor=$bgcolor_out>\n";// bordercolor='#000000'
        echo "<td align=center><input type=radio name=Modificar_Cheque_Numero onClick='document.all.id_banco.value=\"".$fila[idbanco]."\";' value='".$fila[númeroch]."'></td>";
        echo "<td align=center>".Fecha($fila[fechavtoch])."</td>\n";
        echo "<td align=center>".Fecha($fila[fechadébch])."</td>\n";
        echo "<td align=center>".$fila[númeroch]."</td>\n";
        echo "<td align=right>\$".formato_money($fila[importech])."</td>\n";
        echo "<td align=left>".$fila[razon_social]."&nbsp;</td>\n";
        echo "<td align=center>".$Dias."&nbsp;</td>\n";
        echo "</tr>\n";
        }
        else
        {
        echo "<tr {$style[$i=($i+1)%2]} >\n";// bordercolor='#000000'
        echo "<td align=center>".Fecha($fila[fechavtoch])."</td>\n";
        echo "<td align=center>".Fecha($fila[fechadébch])."</td>\n";
        echo "<td align=center>".$fila[númeroch]."</td>\n";
        echo "<td align=right ".excel_style("$")."><b>".formato_money($fila[importech])."</b></td>\n";
        echo "<td align=left>".$fila[razon_social]."&nbsp;</td>\n";
        echo "<td align=center>".$Dias."&nbsp;</td>\n";
        echo "</tr>\n";
        }
    }

    if ($download)
    {
    	echo "<tr bgcolor=#000000 style='color:#E9E9E9;font-weight: bold;'><td colspan=6 align=center><b>Subtotal Debitado: \$ ".formato_money($SubTotal)."</b></td></tr>";
    	echo "</table>\n";
    	echo "<table><tr><td>&nbsp;</td></tr></table>\n";
    	echo "</html>\n";
    }
    else 
    {
    echo "<tr><td colspan=7 align=center id=ma><b>Subtotal Debitado: \$ ".formato_money($SubTotal)."</b></td></tr>";
    echo "</table></form>\n";
    }
fin_pagina();    
?>