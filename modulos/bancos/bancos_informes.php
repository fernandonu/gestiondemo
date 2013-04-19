<?php
/*
$Author: nazabal $
$Revision: 1.9 $
$Date: 2007/06/28 21:38:50 $
*/
// Cabecera Configuracion requerida
require_once("../../config.php");

//print_r($parametros);

$download=$parametros['download'];	

variables_form_busqueda("bancos_informes");

// Declaracion de funciones
function FormInforme($filtro,$orden,$sql_tmp,$where_tmp) {
    global $parametros,$db,$proveedor_inf,$fecha_campo,$datos,$mode,
    $cmd,$Banco,$Fecha_Desde,$Fecha_Hasta,$Fecha_Desde_db,
    $Fecha_Hasta_db,$bgcolor3,$bgcolor2,$tipoinf;
    global $nbre_banco,$nbre_prov,$provinf,$fechasinf,$fcampo;
	 global $download;
	 
	 /*******************************************************************

	 EL CODIGO HTML ESTA TODO EN ECHO PARA PODER USAR LA FUNCION ob_start
	 
	 GACZ.

	 ********************************************************************/
	 
	//almacena en un buffer los datos que escribe la funcion	 al navegador
	if ($download)	 
		ob_start();	

    if ($_POST["Informes_Fechas"] == "1") {
          $fechasinf = " checked";
   }
   else {
          $fechasinf = "";
   }
    if ($_POST["Informes_PorProveedor"] == "1") {
          $provinf = " checked";
   }
   else {
          $provinf = "";
   }
   $filtro_tmp = $filtro;

   if (!$download)   {
	   echo '<script language="javascript">';
	   echo "var filtro=new Array(".count($filtro_tmp).");\n";
	   echo "var datos=new Array(".count($datos).");\n";
	    while(list($tipo,$campos)=each($filtro_tmp)) {
	       echo "filtro['$tipo']=new Array(".count($campos).");\n";
	       echo "datos['$tipo']=new Array(".count($datos[$tipo]["fecha"]).");\n";
	        while(list($key,$val)=each($datos[$tipo]["fecha"])) {
	           echo "datos['$tipo']['$key']='$val';\n";
	       }
	        while(list($key,$val)=each($campos)) {
	           $val_array=explode(":",$val);
	           $filtro_tmp[$tipo][$key]=$val_array[1];
	           echo "filtro['$tipo']['$key']='".$val_array[1]."';\n";
	       }
	   }
		echo '
	        function insertar(objeto,valor,texto) {
	            objeto.length++;
	            objeto.options[objeto.length-1].text=texto;
	            objeto.options[objeto.length-1].value=valor;
	        }
	       function cambiar(val) {
	            obj = document.forms[0].filter;
	           obj2 = document.forms[0].Informe_Fechas_Campo;
	           obj.length = 0;
	           obj2.length = 0;
	           insertar(obj,"all","Todos los campos");
	           obj.options[0].selected = true;
	           for(campo in filtro[val]) {
	               insertar(obj,campo,filtro[val][campo]);
	           }
	           for(campo in datos[val]) {
	               insertar(obj2,campo,datos[val][campo]);
	           }
	       }
	   </script>';
		
		cargar_calendario();
	}
	echo  '<form name="form1" action=bancos_informes.php method=post>
  <input type=hidden name=mode value=forms>
  <input type=hidden name=cmd value=Informes>
  <input type=hidden name=download value=0>
  <table align=center width="90%" border="2" bgcolor='. $bgcolor3.' cellspacing="0" cellpadding="5">
    <tr>
      <td bgcolor='. $bgcolor2.' style="border-top:#000000;border-left:#000000;border-right:#000000;border-bottom:#000000;" colspan="5" align="center">
          <b>Parametros del Informe</b>
      </td>
    </tr>
    <tr>
      <td style="border-top:#000000;border-left:#000000;border-right:#000000;border-bottom:#000000;" width="25%"><div align="center">
          <input name="TipoInforme" onClick="cambiar(this.value);" type="radio" value="cheques"'; if($tipoinf=="cheques") echo " checked>";
	echo   '<b>Cheques</b></div></td>
      <td style="border-top:#000000;border-left:#000000;border-right:#000000;border-bottom:#000000;" width="25%"> <div align="center">
          <input name="TipoInforme" onClick="cambiar(this.value);" type="radio" value="depositos"'; if($tipoinf=="depositos") echo " checked>";
	echo    '<b>Depositos</b></div></td>
      <td style="border-top:#000000;border-left:#000000;border-right:#000000;border-bottom:#000000;" width="25%"> <div align="center">
          <input name="TipoInforme" onClick="cambiar(this.value);" type="radio" value="debitos"'; if($tipoinf=="debitos") echo " checked>";
   echo    '<b>D&eacute;bitos</b></div></td>
      <td style="border-top:#000000;border-left:#000000;border-right:#000000;border-bottom:#000000;" width="25%"> <div align="center">
          <input name="TipoInforme" onClick="cambiar(this.value);" type="radio" value="tarjetas"'; if($tipoinf=="tarjetas") echo " checked>";
   echo    '<b>Tarjetas</b></div></td>
    </tr>
    <tr>
      <td style="border-top:#000000;border-left:#000000;border-right:#000000;border-bottom:#000000;" colspan="5" align="center"><b>Banco:</b>
        <select name="Informes_Banco">';

        echo "<option value='todos' ";
        if ($Banco=="todos")
        {
            echo " selected";
            $nbre_banco="Todos";
            
        }
        echo ">Todos</option>\n";
        $sql1 = "SELECT idbanco,nombrebanco FROM tipo_banco WHERE activo=1 order by nombrebanco";
        $result1 = $db->Execute($sql1) or die($db->ErrorMsg());
        while ($fila = $result1->fetchrow()) {
            echo "<option value=".$fila[idbanco];
            if ($fila[idbanco] == $Banco)
            {
            	 echo " selected";
            	 $nbre_banco=$fila[nombrebanco];
            }
            echo ">".$fila[nombrebanco]."</option>\n";
        }
        echo "</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp";

        $GLOBALS['page'] = $parametros["page"] or $GLOBALS['page'] = 0;                                //pagina actual
         $GLOBALS['filter'] = $_POST["filter"] or $GLOBALS['filter'] = $parametros["filter"];           //campo por el que se esta filtrando
         $GLOBALS['keyword'] = $_POST["keyword"] or $GLOBALS['keyword'] = $parametros["keyword"];       //palabra clave
         if ($parametros["up"]=="") $parametros["up"] = "0";   // 1 ASC 0 DESC
         //prefijo para los links de paginas siguiente y anterior
         $link_tmp = Array('idbanco'=>$Banco,'fechas'=>$_POST["Informes_Fechas"],'fechad'=>$Fecha_Desde_db,'fechah'=>$Fecha_Hasta_db,'tipoinf'=>$tipoinf,'fecha_campo'=>$fecha_campo,'provinf'=>$_POST["Informes_PorProveedor"],'proveedor_inf'=>$proveedor_inf);
         $link_tmp["up"]=$parametros["up"];
         $link_tmp["page"]=$GLOBALS['page'];
         $link_tmp["filter"]=$GLOBALS['filter'];
         $link_tmp["keyword"]=$GLOBALS['keyword'];
         $link_download=array_merge($link_tmp, Array('download'=>1));
		 //condiciones extras de la consulta
         //print_r($link_tmp);
		 list($sql,$total,$link_pagina,$up) = form_busqueda($sql_tmp,$orden[$tipoinf],$filtro_tmp[$tipoinf],$link_tmp,$where_tmp,"buscar");
      echo '</td>
    </tr>
    <tr>
      <td style="border-top:#000000;border-left:#000000;border-right:#000000;border-bottom:#000000;" colspan="1" align="right" valign="middle">
        <input name="Informes_PorProveedor" type="checkbox" value="1"'."$provinf>".'
        <b>Por proveedor:</b>&nbsp;&nbsp;&nbsp;</td>
      <td style="border-top:#000000;border-left:#000000;border-right:#000000;border-bottom:#000000;" colspan="3" align="left">
        <select name="Informes_Proveedor">';
            $sql1 = "SELECT id_proveedor,razon_social FROM proveedor ORDER BY razon_social";
            $result = $db->execute($sql1) or die($db->ErrorMsg());
            while ($fila1 = $result->fetchrow()) {
                echo "<option value='".$fila1[id_proveedor]."'";
                if ($fila1[id_proveedor] == "$proveedor_inf")
                {
                	 echo " selected";
                	$nbre_prov=$fila1[razon_social]; 
                }
                echo ">".$fila1[razon_social]."</option>\n";
            }
        
   echo '     </select>&nbsp;&nbsp;&nbsp;&nbsp;
       </td>
    </tr>
    <tr>
      <td style="border-top:#000000;border-left:#000000;border-right:#000000;border-bottom:#000000;" colspan="1" align="right" valign="middle">
        <input name="Informes_Fechas" type="checkbox" value="1"'. "$fechasinf>".'
        <b>Entre fechas:</b>&nbsp;&nbsp;&nbsp;</td>
      <td style="border-top:#000000;border-left:#000000;border-right:#000000;border-bottom:#000000;" colspan="3" align="center">
        <b>Campo:</b>
        <select name="Informe_Fechas_Campo">';
        
            foreach($datos[$tipoinf]["fecha"] as $campo => $descripcion) {
               echo "<option value='$campo'";
               if ($fecha_campo == $campo)
               { 
               	echo " selected";
               	$fcampo=strtolower($descripcion);
               }
               echo ">$descripcion</option>";
           }
    echo  ' </select>&nbsp;&nbsp;&nbsp;&nbsp;
        <b>Desde: </b>
         <input type=text size=10 name=Informes_Desde value="'.$Fecha_Desde.'" title="Ingrese la fecha de inicio y\nhaga click en Actualizar">';
	 echo link_calendario("Informes_Desde"); 
	 echo '&nbsp;&nbsp;&nbsp;&nbsp;<b>Hasta: </b>
         <input type=text size=10 name=Informes_Hasta value="'.$Fecha_Hasta.'" title="Ingrese la fecha de finalización\ny haga click en Actualizar">';
    echo link_calendario("Informes_Hasta");
    echo ' </td>
    </tr>
     <tr><td style="border-top:#000000;border-left:#000000;border-right:#000000;border-bottom:#000000;" colspan=5 align=center>
     <input type=submit name=Informes_Mostrar value="  Mostrar  ">&nbsp;&nbsp;&nbsp;';
	if (permisos_check("inicio","xls_bancos_informes"))
	{
	echo	'<a target=_blank href="'.encode_link($_SERVER['SCRIPT_NAME'],$link_download).'"><img style="cursor=hand" title="Bajar datos en un excel" src="../../imagenes/excel.gif" width="16" height="16" border="0" ></a>';
	//		'<img style="cursor=hand" onclick=\'wdownload=window.open("'.encode_link('bancos_informes.php',$link_download).'","","toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=790,height=590");\' title="Bajar datos en un excel" src="../../imagenes/excel.gif" width="16" height="16" border="0" >'.
	}
	echo '</form></td></tr>
  </table><br>';

	if ($download)	 
		ob_clean();	
    
return array($sql,$total,$link_pagina,$up);
}
// Variable Banco
if (!$_POST["Informes_Banco"]) {
      if ($parametros[idbanco]) {
          $Banco = $parametros[idbanco];
   }
   else {
        $Banco=4;  // Banco por defecto
   }
}
else {
    $Banco=$_POST["Informes_Banco"];
}
// Variables
$GLOBALS['sort'] = $_POST["sort"] or $GLOBALS['sort'] = $parametros["sort"];
if (!$sort) $sort="default";
$datos = array(
            "cheques" => array(
				"titulo" => "Cheques",
				"sql" => "SELECT cheques.FechaVtoCh,
							cheques.FechaDébCh,
							cheques.FechaEmiCh,
							cheques.FechaPrev,
							cheques.NúmeroCh,
							cheques.ImporteCh,
							proveedor.razon_social,
							cheques.comentarios,
                            tipo_cuenta.concepto,
                            tipo_cuenta.plan
							FROM cheques
							LEFT JOIN proveedor	ON cheques.idprov=proveedor.id_proveedor 
                            LEFT JOIN general.tipo_cuenta USING(numero_cuenta)
                            ",
//				"where" => "cheques.FechaDébCh IS NOT NULL ",
				"where" => "",
				"fecha" => array (
					"cheques.FechaVtoCh" => "Vencimiento",
					"cheques.FechaPrev" => "A debitar",
					"cheques.FechaDébCh" => "Débito",
					"cheques.FechaEmiCh" => "Emisión"
				),
				"sumar" => array(
						  "cheques.ImporteCh" => "bancos.cheques LEFT JOIN proveedor
							ON cheques.idprov=proveedor.id_proveedor 
							LEFT JOIN bancos.tipo_banco USING (idbanco)
                            LEFT JOIN general.tipo_cuenta USING(numero_cuenta)"
                )
			),
			"depositos" => array(
				"titulo" => "Depósitos Acreditados",
				"sql" => "SELECT depósitos.IdDepósito,
							tipo_depósito.TipoDepósito,
							depósitos.FechaDepósito,
							depósitos.ImporteDep,
							depósitos.FechaCrédito
							FROM depósitos
							LEFT JOIN tipo_depósito
							ON depósitos.IdTipoDep=tipo_depósito.IdTipoDep ",
				"where" => "depósitos.FechaCrédito IS NOT NULL ",
				"fecha" => array(
							"depósitos.FechaDepósito" => "Depósito",
							"depósitos.FechaCrédito" => "Crédito"
				),
				"sumar" => array(
							"depósitos.ImporteDep" => "depósitos LEFT JOIN tipo_depósito
							USING (IdTipoDep) LEFT JOIN bancos.tipo_banco 
							USING (idbanco)"
				)
			),
			"debitos" => array(
				"titulo" => "Débitos",
				"sql" => "SELECT débitos.IdDébito,
							tipo_débito.TipoDébito,
							débitos.FechaDébito,
							débitos.ImporteDéb,
							débitos.comentario
							FROM débitos
							LEFT JOIN tipo_débito ON
							débitos.IdTipoDéb=tipo_débito.IdTipoDéb ",
                "where" => "",
				"fecha" => array(
							"débitos.FechaDébito" => "Débito"
				),
				"sumar" => array(
							"débitos.ImporteDéb" => "débitos LEFT JOIN tipo_débito
							USING (IdTipoDéb) LEFT JOIN bancos.tipo_banco
							USING (idbanco)"
				)
			),
			"tarjetas" => array(
				"titulo" => "Tarjetas Acreditadas",
				"sql" => "SELECT tarjetas.IdTarjeta,
							tipo_tarjeta.TipoTarjeta,
							tarjetas.FechaDepTar,
							tarjetas.ImporteDepTar,
							tarjetas.FechaCrédTar,
							tarjetas.ImporteCrédTar
							FROM tarjetas
							LEFT JOIN tipo_tarjeta
							ON tarjetas.idtipotar=tipo_tarjeta.idtipotar ",
				"where" => "tarjetas.fechacrédtar IS NOT NULL ",
				"fecha" => array(
							"tarjetas.FechaDepTar" => "Depósito",
							"tarjetas.FechaCrédTar" => "Crédito"
				),
				"sumar" => array(
							"tarjetas.ImporteCrédTar" => "tarjetas LEFT JOIN tipo_tarjeta
							USING (idtipotar) LEFT JOIN bancos.tipo_banco
							USING (idbanco)"
				)
			)
);
// Cheques
$datos['cheques']['sql'] .="INNER JOIN tipo_banco ON cheques.idbanco=tipo_banco.idbanco ";
if ($Banco!="todos")
	$datos['cheques']['where'] .= "cheques.IdBanco=$Banco";
//	$datos['cheques']['where'] .= "AND cheques.IdBanco=$Banco";
else
    $datos['cheques']['where'] .= "tipo_banco.activo=1";
//    $datos['cheques']['where'] .= "AND tipo_banco.activo=1";
// tarjetas
$datos['tarjetas']['sql'] .="INNER JOIN tipo_banco ON tarjetas.idbanco=tipo_banco.idbanco ";
if ($Banco!="todos")
	$datos['tarjetas']['where'] .= "AND tarjetas.IdBanco=$Banco";
else
    $datos['tarjetas']['where'] .= "AND tipo_banco.activo=1";
// depositos
$datos['depositos']['sql'] .="INNER JOIN tipo_banco ON depósitos.idbanco=tipo_banco.idbanco ";
if ($Banco!="todos")
	$datos['depositos']['where'] .= "AND depósitos.IdBanco=$Banco";
else
    $datos['depositos']['where'] .= "AND tipo_banco.activo=1";
// debitos
$datos['debitos']['sql'] .="INNER JOIN tipo_banco ON débitos.idbanco=tipo_banco.idbanco ";
if ($Banco!="todos")
	$datos['debitos']['where'] .= "débitos.IdBanco=$Banco";
else
    $datos['debitos']['where'] .= "tipo_banco.activo=1";
// Cuerpo
		if (!$_POST["Informes_Mostrar"]) {
           $proveedor_inf = $parametros["proveedor_inf"];
           $_POST["Informes_PorProveedor"] = $parametros["provinf"];
           if ($parametros["tipoinf"]) {
               $tipoinf = $parametros["tipoinf"];
               if ($parametros["fecha_campo"]) {
                   $fecha_campo = $parametros["fecha_campo"];
               }
           }
           else {
               $tipoinf = "cheques";   // Tipo por defecto
           }
           if ($parametros["fechas"] == "1") {
                   $_POST["Informes_Fechas"] = "1"; // Entre fechas
           }
           else {
                   $_POST["Informes_Fechas"] = $parametros["fechas"];
           }
           if ($parametros["idbanco"]) {
               $Banco = $parametros["idbanco"];
            }
           else {
                $Banco=4;  // Banco por defecto
            }
           if ($parametros["fechad"]) {
                $Fecha_Desde = Fecha($parametros["fechad"]);
                $Fecha_Desde_db = $parametros["fechad"];
            }
           else {
               //por defecto, el dia de hoy
                $Fecha_Desde = date("d/m/Y",mktime());
                $Fecha_Desde_db = date("Y-m-d",mktime());
           }
           if ($parametros["fechah"]) {
                $Fecha_Hasta = Fecha($parametros["fechah"]);
                $Fecha_Hasta_db = $parametros["fechah"];
            }
           else {
               //por defecto, los proximos 40 dias
                $Fecha_Hasta = date("d/m/Y",(mktime() + (40 * 24 * 60 * 60)));
                $Fecha_Hasta_db = date("Y-m-d",(mktime() + (40 * 24 * 60 * 60)));
           }
        }
        else {
            $tipoinf = $_POST["TipoInforme"];
            $fecha_campo = $_POST["Informe_Fechas_Campo"];
            $proveedor_inf = $_POST["Informes_Proveedor"];
            $Banco=$_POST['Informes_Banco'];
            list($d,$m,$a) = explode("/", $_POST['Informes_Desde']);
            if (FechaOk($_POST['Informes_Desde'])) {
                $Fecha_Desde = "$d/$m/$a";
                $Fecha_Desde_db = "$a-$m-$d";
            }
            else {
                Error("Fecha de inicio no válida");
                $Fecha_Desde = date("d/m/Y",mktime());
                $Fecha_Desde_db = date("Y-m-d",mktime());
            }
            list($d,$m,$a) = explode("/", $_POST[Informes_Hasta]);
            if (FechaOk($_POST[Informes_Hasta])) {
                $Fecha_Hasta = "$d/$m/$a";
                $Fecha_Hasta_db = "$a-$m-$d";
            }
            else {
                Error("Fecha de finalización no válida");
                $Fecha_Hasta = date("d/m/Y",(mktime() + (40 * 24 * 60 * 60)));
                $Fecha_Hasta_db = date("Y-m-d",(mktime() + (40 * 24 * 60 * 60)));
            }
        }

    //Datos
   // Formulario de busqueda
   // variables que contienen los datos actuales de la busqueda
   $orden = array(
               "cheques" => array(
                            "default" => "3",     //campo por defecto
							"default_up" => "0",
							"3" => "cheques.fechavtoch",
							"5" => "cheques.fechadébch",
							"2" => "cheques.fechaemich",
							"4" => "cheques.fechaprev",
							"6" => "cheques.númeroch",
							"7" => "cheques.importech",
							"1" => "proveedor.razon_social",
							"8" => "cheques.comentarios",
							"9" => "tipo_cuenta.concepto",
							"10" => "tipo_cuenta.plan"
						 ),
			   "depositos" => array(
								"default" => "3",     //campo por defecto
								"default_up" => "0",
								"0" => "depósitos.iddepósito",
								"1" => "tipo_depósito.tipodepósito",
								"2" => "depósitos.fechadepósito",
								"4" => "depósitos.importedep",
								"3" => "depósitos.fechacrédito"
						  ),
			   "debitos" => array(
							"default" => "3",
							"default_up" => "0",
							"1" => "tipo_débito.tipodébito",
							"2" => "débitos.importedéb",
							"3" => "débitos.fechadébito",
							"4" => "débitos.comentario"
						  ),
			   "tarjetas" => array(
							"default" => "4",     //campo por defecto
							"default_up" => "0",
							"0" => "tarjetas.idtarjeta",
							"1" => "tipo_tarjeta.tipotarjeta",
							"2" => "tarjetas.fechadeptar",
							"3" => "tarjetas.importedeptar",
							"4" => "tarjetas.fechacrédtar",
							"5" => "tarjetas.importecrédtar"
						  )
   );
   $filtro = array(
			   "cheques" => array(
							 "proveedor.razon_social" => "t:Proveedor",
							 "cheques.fechaemich" => "f:Fecha de emisión",
							 "cheques.fechavtoch" => "f:Fecha de vencimiento",
							 "cheques.fechaprev" => "f:Fecha prevista",
							 "cheques.fechadébch" => "f:Fecha de débito",
							 "cheques.númeroch" => "t:Número",
							 "cheques.importech" => "n:Importe",
							 "cheques.comentarios" => "t:Comentarios",
							 "tipo_cuenta.concepto" => "t:Concepto",
							 "tipo_cuenta.plan" => "t:Plan"
						  ),
			   "depositos" => array(
							 "tipo_depósito.tipodepósito" => "t:Tipo de depósito",
							 "depósitos.fechadepósito" => "f:Fecha de depósito",
							 "depósitos.fechacrédito" => "f:Fecha de acreditación",
							 "depósitos.importedep" => "n:Importe"
						  ),
			   "debitos" => array(
							 "tipo_débito.tipodébito" => "t:Débito",
							 "débitos.importedéb"     => "n:Importe",
							 "débitos.fechadébito"    => "f:Fecha",
							 "débitos.comentario"     => "t:Comentario"
                          ),
             "tarjetas" => array(
							 "tipo_tarjeta.tipotarjeta" => "t:Tipo de tarjeta",
							 "tarjetas.fechadeptar" => "f:Fecha de depósito",
							 "tarjetas.importedeptar" => "n:Importe de depósito",
							 "tarjetas.fechacrédtar" => "f:Fecha de crédito",
							 "tarjetas.importecrédtar" => "n:Importe de crédito"
                          )
   );
    $sql_tmp = $datos[$tipoinf]["sql"];
    $where_tmp = $datos[$tipoinf]["where"];
    if ($_POST["Informes_Fechas"] == "1") {
       $where_tmp .= " AND $fecha_campo Between '$Fecha_Desde_db' AND '$Fecha_Hasta_db'";
   }
    if ($_POST["Informes_PorProveedor"] == "1") {
       if ($tipoinf == "cheques") {
			   $where_tmp .= " AND cheques.idprov=$proveedor_inf";
       }
       else {
           Aviso("La función de búsqueda por Proveedor es para la sección de Cheques");
       }
   }

if ($download)
	$itemspp=1000000;//para que recupere todos los registros
else 
	echo $html_header;

   list($sql,$total,$link_pagina,$up) = FormInforme($filtro,$orden,$sql_tmp,$where_tmp);

//envio la pagina como archivo excel
/**/
if($download)
{
	header("Pragma: ");//para que se pueda abrir el archivo sin bajarlo
	header("Cache-Control: ");//para que se pueda abrir el archivo sin bajarlo
	if (isset($_SERVER["HTTPS"])) {
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate"); // HTTP/1.1
		header("Cache-Control: post-check=0, pre-check=0", false);
	}

	$boundary = strtoupper(md5(uniqid(time())));
	header("Content-Type: application/xls");
	header("Content-Transfer-Encoding: binary");
	header("Content-Disposition: attachment; filename=\"informe_".strtolower($tipoinf)."(".date("j-m-Y").").xls\"");
	

//	header("Content-Type: application/xls");
//	header("Content-Transfer-Encoding: binary");
//	header("Content-Disposition: attachment; filename=\"informe_".strtolower($tipoinf)."(".date("j-m-Y").").xls\"");
}
/* */
   
   list($sumar_campo,$sumar_tabla) = each($datos[$tipoinf]["sumar"]);
   $sql_suma = eregi_replace("^SELECT(.+)WHERE(.+)ORDER(.+)","SELECT sum($sumar_campo) AS total FROM $sumar_tabla WHERE \\2",$sql);
//   echo "<br>sql: $sql<br>sql: $sql_suma<br>";
   $result = sql($sql_suma) or fin_pagina();
   $res_tmp = $result->fetchrow();
   $total_suma = formato_money($res_tmp["total"]);
   $result = sql($sql) or fin_pagina();
   $SubTotal = 0;

	if ($download)
	{	
		$columnas=count($filtro[$tipoinf]);
?>
		<table width="100%" align="center">
		<tr>
			<td colspan="<?=$columnas?>" >
			<b> Informe de <?=strtoupper($tipoinf) ?></b> (<?= date2("LHM")?>)
			</td>
		</tr>
		<tr>
			<td colspan="<?=$columnas?>">
			</td>
		</tr>
		<tr>
			<td colspan="<?=$columnas?>">
			<b>Banco:</b> <?=$nbre_banco?> 
			</td>
		</tr>
		<tr>
			<td colspan="<?=$columnas?>">
			<b>Palabra a Buscar:</b> <?=(($keyword)?$keyword." - <b>Campo a buscar:</b> ".(($filter=="all")?"Todos los campos":substr($filtro[$tipoinf][$filter],strpos($filtro[$tipoinf][$filter],":")+1)):"Ninguna" )?>
			</td>
		</tr>
		
<? if ($provinf)	{?>
		<tr>
			<td colspan="<?=$columnas?>">
			<b>Filtro de proveedor activado - Proveedor:</b> <?=$nbre_prov ?>  
			</td>
		</tr>
<? }
	else {
?>
		<tr>
			<td colspan="<?=$columnas?>">
			<b>Filtro de proveedor desactivado</b>
			</td>
		</tr>
<?	}
	if ($fechasinf)	{?>
		<tr>
			<td colspan="<?=$columnas?>">
			<b>Filtro de fecha activado - Fecha <?= $fcampo." desde:</b> ". $Fecha_Desde. "<b> hasta:</b> ".$Fecha_Hasta ?> 
			</td>
		</tr>
<?	}
	else {
?>
		<tr>
			<td colspan="<?=$columnas?>">
			<b>Filtro de fecha desactivado</b>
			</td>
		</tr>
<? } ?>		
		<tr>
			<td colspan="<?=$columnas?>">
			</td>
		</tr>
		<tr>
			<td colspan="<?=$columnas?>">
			<b>Total: </b>$<?="$total_suma en $total registros" ?>
			</td>
		</tr>
		<tr>
			<td colspan="<?=$columnas?>">
			</td>
		</tr>
		
		</table>
		
<?		//estilo para tipo moneda pesos en Excel
		$stylemoney='style=\'mso-number-format:"\0022$\0022\\ \#\,\#\#0\.00\;\[Red\]\0022$\0022\\ \\-\#\,\#\#0\.00"\'';
		//estilo para el tipo fecha
		$stylefecha="style='mso-number-format:\"Short Date\"'";	
		$styeletext='style=\'mso-number-format:"\@"\'';
	   echo "<table width='100%' align='center' cellpadding='0' cellspacing='1' border='1' bordercolor='black'>";
	   echo "<tr align='center' height='20' style='color:#E9E9E9;font-weight:bold;' >";
	   $link = Array('idbanco'=>$Banco,'fechas'=>$_POST["Informes_Fechas"],'fechad'=>$Fecha_Desde_db,'fechah'=>$Fecha_Hasta_db,'tipoinf'=>$tipoinf,'fecha_campo'=>$fecha_campo,'provinf'=>$_POST["Informes_PorProveedor"],'proveedor_inf'=>$proveedor_inf);
	   $link["up"] = $up;
	   $link["keyword"] = $keyword;
	   $link["filter"] = $filter;
	   $link['page']=$page;
	   $contar=1;
	   foreach($filtro[$tipoinf] as $campo => $descripcion) {
	        $link['sort']=$contar;
	        $desc_array=explode(":",$descripcion);
            if ($desc_array[0] != "h") {
	            echo "<td bgcolor='black'>".$desc_array[1]."</td>";
                $contar++;
            }
	   }
	   echo "</tr>\n";
	   $i=1;
	   while ($fila = $result->fetchrow()) {
	   	  $color=($i++%2)?"#FFFFFF":"#99CCFF";
	        echo "<tr height=16>\n";
	        foreach($filtro[$tipoinf] as $campo => $descripcion) {
				$indice = substr($campo,strrpos($campo,".")+1);
				$desc_array=explode(":",$descripcion);
				switch ($desc_array[0]) {
					case "t":           // Tipo Texto
						//para el caso de numeros de cheques o campos enteros
						if (is_int($temp=intval($fila[$indice])) && $temp!=0)
							$align="'left' ";
						else 
							$align="'left' $styeletext";
						$texto=$fila[$indice];
						break;
					case "f":           // Tipo Fecha
						$align="'center' $stylefecha";
						$texto=Fecha($fila[$indice]);
						break;
					case "n":           // Tipo Numerico
							$align="'right' $stylemoney";
							$texto=formato_money($fila[$indice]);
						break;
                    case "h":           // Tipo Hidden, no se muestra esta comlumna
                        continue(2);
                        break;
				}
				if ($texto == "") { $texto = "&nbsp;"; }
	            echo "<td align=$align bgcolor='$color'>$texto</td>";
	       }
	        echo "</tr>\n";
	    }
	    echo "</table>\n";		
	}
	else 
	{
	   echo "<table width='95%' align=center cellpadding=2 class=\"bordes\">";// bordercolor=$bgcolor3
	   echo "<tr >";  //bordercolor='#000000'
	   $cant_cols = count($filtro[$tipoinf]);
	   if ($tipoinf == "cheques" && $Banco == "21") $cant_cols++;
	   echo "<td id=ma colspan=".$cant_cols." align=center>";
	   echo "<table id=ma width='100%' cellspacing=0 cellpadding=0><tr>";
	   echo "<td align=left width='33%' $stylemoney>Total:&nbsp;\$&nbsp;$total_suma&nbsp;en&nbsp;$total&nbsp;registros</td>";
	   echo "<td alidn=center width='34%'><font size=+1>&nbsp;&nbsp;".$datos[$tipoinf]["titulo"]."&nbsp;&nbsp;</font></td>";
	   echo "<td align=right width='33%'>$link_pagina</td>";
	   echo "</tr></table>";
	   echo "</td></tr>";
	   echo "<tr id=mo>";  //bordercolor='#000000'
	   $link = Array('idbanco'=>$Banco,'fechas'=>$_POST["Informes_Fechas"],'fechad'=>$Fecha_Desde_db,'fechah'=>$Fecha_Hasta_db,'tipoinf'=>$tipoinf,'fecha_campo'=>$fecha_campo,'provinf'=>$_POST["Informes_PorProveedor"],'proveedor_inf'=>$proveedor_inf);
	   $link["up"] = $up;
//	   $link["keyword"] = $keyword;
//	   $link["filter"] = $filter;
//	   $link['page']=$page;
	   $contar=1;
	   foreach($filtro[$tipoinf] as $campo => $descripcion) {
	        $link['sort']=$contar;
	        $desc_array=explode(":",$descripcion);
            if ($desc_array[0] != "h") {
                echo "<td align=center><a href='".encode_link('bancos_informes.php',$link)."'>".$desc_array[1]."</a></td>";
                $contar++;
            }
	   }
       if ($tipoinf == "cheques" && $Banco == "21") {
			echo "<td align=center>Imprimir</td>";
       }
	   echo "</tr>\n";
	   while ($fila = $result->fetchrow()) {
	//        $SubTotal += $fila[importech];
	        echo "<tr bgcolor=$bgcolor_out >\n";  //bordercolor='#000000'
	        foreach($filtro[$tipoinf] as $campo => $descripcion) {
				$indice = substr($campo,strrpos($campo,".")+1);
				$desc_array=explode(":",$descripcion);
				switch ($desc_array[0]) {
					case "t":       // Tipo Texto
						$align="left";
						$texto=$fila[$indice];
						break;
					case "f":       // Tipo Fecha
						$align="center ";
						$texto=Fecha($fila[$indice]);
						break;
					case "n":       // Tipo Numerico
							$align="right";
							$texto="\$&nbsp;".formato_money($fila[$indice]);
						break;
                    case "h":       // Tipo Hidden, no se muestra esta comlumna
                        continue(2);
                        break;
				}
				if ($texto == "") { $texto = "&nbsp;"; }
	            echo "<td align=$align>$texto</td>";
			}
			
			// El control por banco deberia hacerse por base de datos...
			// Si hay mas bancos con templates para formularios continuos,
			// por ahora hay que abregarlos a mano usando el idbanco guardado en $Banco
			// NO ES SOLAMENTE ESTA PARTE, HAY QUE CAMBIAR EN TODO EL ARCHIVO
			// BUSCANDO ESTO: if ($tipoinf == "cheques"
			
			if ($tipoinf == "cheques" && $Banco == "21") {
				if ($fila["importech"] > 0) {
					echo "<td align=center><a href='".encode_link('cheques_imprimir.php',array("id_banco"=>$Banco,"numero_cheque"=>$fila["númeroch"]))."' target='_new'><img src='$html_root/imagenes/word.gif' alt='Descargar en formato Word 2003' border='0'></a></td>";
				}
				else {
					echo "<td align=center>&nbsp;</td>";
				}
		    }
	        echo "</tr>\n";
	    }
	//    echo "<tr><td colspan=5 align=center><b>Subtotal Pendiente: \$ ".formato_money($SubTotal)."</b></td></tr>";
	    echo "</table>\n";
	}
fin_pagina();
?>