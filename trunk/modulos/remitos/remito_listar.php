<?
/*
Author: GACZ

MODIFICADA POR
$Author: mari $
$Revision: 1.64 $
$Date: 2007/01/04 18:11:43 $
*/

require_once("../../config.php");

if ($parametros["cmd"] == "detalle") {
	include("remito_nuevo.php");
	exit;
}

$backto=$parametros['backto'] or $backto=$_POST['backto'] or $backto=$_ses_rem_lis['backto'];
//print_r($_ses_rem_lis);
//echo "<br>Backto= ".$backto;

if ($_POST["actualizar_numeros"]) {
	
	$db->starttrans();
	
	$sql=" select nro_remito,id_remito from remitos";
	$res=sql($sql) or fin_pagina();
	echo "Actualizando Datos.....<br>";
	for($i=0;$i<$res->recordcount();$i++){
   		$nro_remito=$res->fields["nro_remito"];
   		$id_remito=$res->fields["id_remito"];
        $asterisco="";
   		//echo "<br>Nro Factura: $nro_factura res: ".substr_count("*",$nro_factura);
   		
   		if (substr_count($nro_remito,"*")){
   			//si tiene * el nro de factura es un caso especial
   		       switch (substr_count($nro_remito,"*")){
   		       	case 1:
   		       		  $nro_remito=substr($nro_remito,1);
   		       		  $asterisco="*";
   		       		  break;
   		       	case 2:
   		       		  $nro_remito=substr($nro_remito,2);
   		       		  $asterisco="**";
   		       		  break;	  
   		       }	
          			
   		}
   		$flag = 1;
 		switch (strlen($nro_remito)){
 			                case 0:
 			                	 $flag=0;
 			                	 break;
			    			case 1:
				   				   $nro_remito="0000000".$nro_remito;
				   				   break;
				   			case 2:
				   				  $nro_remito="000000".$nro_remito;
				   				   break;
				   		    case 3:
				   				   $nro_remito="00000".$nro_remito;
				   				   break;
				   			case 4:
				   				   $nro_remito="0000".$nro_remito;
				   				   break;
				   			case 5:
				   				  $nro_remito="000".$nro_remito;
				   				   break;
				   			case 6:
				   				   $nro_remito="00".$nro_remito;
				   				   break;
				   			case 7:
				   				   $nro_remito="0".$nro_remito;
				   				   break;
				   		}
        
        if ($flag) {				   		   		 
	   		$nro_remito=$asterisco.$nro_remito;
	  		$sql="update remitos set nro_remito='$nro_remito' where id_remito=$id_remito";
	   		sql($sql) or fin_pagina();
        }
		$res->movenext();
	}//del for1
	$db->completetrans();
	echo "Termino de actualizar ".$res->recordcount()." facturas <br>";
} //del if de actualizar numeros



echo $html_header;

$informar="";

define("OUTPUT","./pdfs"); //directorio donde se crearan los pdfs

if ($parametros['volver_lic']) {
	phpss_svars_set("_ses_rem_lis",$parametros);
}
$sesion=array("backto"=>$backto);

variables_form_busqueda("rem_lis",$sesion);
//variables_form_busqueda("rem_lis");
//echo "<br> despues de variables";
//print_r($_ses_rem_lis);

if ($cmd == "") {
	$cmd="terminados";
	$_ses_rem_lis["cmd"] = $cmd;
	phpss_svars_set("_ses_rem_lis", $_ses_rem_lis);
}

$datos_barra = array(
					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "pendientes"
						),
					array(
						"descripcion"	=> "en Transitos",
						"cmd"			=> "terminados"
						),
					array(
						"descripcion"	=> "Recibidos",
						"cmd"			=> "recibidos"
						),
					array(
						"descripcion"	=> "Anulados",
						"cmd"			=> "anulados"
						),
					array(
						"descripcion"	=> "Todos",
						"cmd"			=> "todos"
						)
				 );

$filtro = array(
			"res.nro_remito" => "Nº Remito",
			"fecha_remito" => "Fecha Remito",
			"res.pedido" => "Nº Pedido",
			"res.venta" => "Venta",
			"res.direccion" => "Dirección",
			"nro_factura" => "Nº Factura",
			"res.cliente" => "Cliente - Nombre",
			"res.cuit" => "Cliente - C.U.I.T.",
			"res.iib" => "Cliente - Nº I.I.B.",
			"res.iva_tipo" => "Cliente - Condición de I.V.A.",
			"res.iva_tasa" => "Cliente - Tasa de I.V.A.",
			"res.otros" => "Otros",
			"cliente2" => "Recibido por - Nombre",
			"tipo_doc_c2" => "Recibido por - Tipo Documento",
			"nro_doc_c2" => "Recibido por - Nº Documento",
			"res.id_licitacion" => "ID Licitacion",
			"res.tipo_factura" => "Tipo Factura"
		);

$orden = array(
			"default_up" => "0",
			"default" => "1",
			"1" => "res.nro_remito",
			"2" => "nro_factura",
			"3" => "remitos.cliente",
			"4" => "usuario",
			"5" => "tipo_factura",
			"6" => "fecha",
		);


$sql_tmp="select nro_remito,cliente,id_remito,nro_factura,estado, 
          usuario,fecha,tipo_factura,fecha_remito,pedido,venta,
          direccion,cuit,iib,iva_tipo,iva_tasa,otros,
          cliente2,tipo_doc_c2,nro_doc_c2,id_licitacion,id_factura
          from (SELECT remitos.estado,(n_remitos.numeracion || text('-') || remitos.nro_remito) as nro_remito,
          remitos.cliente,remitos.id_remito,
          (n_facturas.numeracion || text('-') || facturas.nro_factura) as nro_factura,
          log.usuario,log.fecha,tipo_factura,fecha_remito,remitos.pedido,remitos.venta,
          remitos.direccion,remitos.cuit,remitos.iib,remitos.iva_tipo,remitos.iva_tasa,remitos.otros,
          remitos.cliente2,tipo_doc_c2,nro_doc_c2,remitos.id_licitacion,remitos.id_factura
          FROM (facturacion.remitos LEFT JOIN facturacion.facturas USING (id_factura)) 
          left join facturacion.numeracion_sucursal as n_facturas on (facturas.id_numeracion_sucursal=n_facturas.id_numeracion_sucursal)
          left JOIN facturacion.log ON (log.id_remito=remitos.id_remito and log.tipo_log='creacion')
          left join facturacion.numeracion_sucursal as n_remitos on  (remitos.id_numeracion_sucursal=n_remitos.id_numeracion_sucursal)) as res
          ";


switch ($cmd) {
	case 'anulados':
					$where_tmp = "res.estado='a'";
					break;
	case 'pendientes':						    
					$where_tmp = "res.estado='p'";
					break;
	case 'recibidos':
					$where_tmp = "res.estado='r'";
					break;
	case 'todos':
					break;
	default: //'terminados': //los que falta cerrar
					$where_tmp ="res.estado='t'";
					break;
}

$fact_rem = $_POST["fact_rem"];

if ($fact_rem == "con") {
	if ($cmd != "todos") {
		$where_tmp .= " AND res.id_factura IS NOT NULL";
	}
	else {
		$where_tmp = "res.id_factura IS NOT NULL";
	}
}
elseif ($fact_rem == "sin") {
	if ($cmd != "todos") {
		$where_tmp .= " AND res.id_factura IS NULL";
	}
	else {
		$where_tmp = "res.id_factura IS NULL";
	}
}
include("../ayuda/ayudas.php");
?>
<form name="form" method="post" action="remito_listar.php">

<table align="center" width="100%" >
 <tr> 
  <td width="98%">
   <? generar_barra_nav($datos_barra); ?>
  </td>
  <td width="2%">
   
    <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/remitos/rem_listar.htm" ?>', 'LISTAR REMITOS')" >    
   
  </td>
 </tr>
</table> 
<!--
<br>
<input type="submit" name="actualizar_numeros" value="actualizar numeros">
<br>
-->
<table align="center" class="bordes" cellpaddindg=2 cellspacing=2 bgcolor=<?=$bgcolor3?>>
<tr>
<td><b>Remitos</b></td>
<td>
 <select name="fact_rem">
  <option value='todos' <?if($fact_rem=="todos")echo "selected"?>>Todos</option>
  <option value='con' <?if($fact_rem=="con")echo "selected"?>>Con Factura</option>
  <option value='sin' <?if($fact_rem=="sin")echo "selected"?>>Sin Factura</option>
 </select> 
</td>
<td>
<?
list($sql,$total_remitos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,"",$where_tmp,"buscar");

$remitos = sql($sql) or die;

if(($remitos->RecordCount()==0)&&($_POST["boton"]=="Buscar")) {
	$informar="<center>No se encontró ningun remito que concuerde con lo buscado</center>";
}
if(($remitos->RecordCount()==0)&&($parametros['volver_lic'])) {
	$informar="<center>No se encontró ningun remito que concuerde con lo buscado</center>";
}
?>
&nbsp;&nbsp;&nbsp;<input type="submit" name="boton" value="Buscar">
</td>
</tr>
</table>
<table align="center">
 <tr>
  <td>
   <input type="button" name="busq_avanzada" value="Busqueda Avanzada" onclick="window.open('busq_avanzada_remitos.php');">
  </td>
 </tr>
</table>
<br>
<?="<b>$informar</b>"?>
    <table class="bordes" cellspacing="2" cellpadding="3" width="95%" align="center">
	<tr><td colspan="<? echo ($cmd=="todos")?"10":"9"; ?>" id="ma">
	<table width="100%" border="0">
	 <tr id="ma">
      <td align="left">Total de Remitos <? if ($cmd) echo ": ".$total_remitos; ?></td>
      <td align="right"><? echo $link_pagina; ?></td>
	 </tr>
	</table>
	</td></tr>
      <tr align="center" id="mo"> 
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"1","up"=>$up)) ?>'>
      <td style="cursor:hand;" title="Ordenar por Nº de Remito" width="106">Nº Remito</td>
</a>
	<?
		if($cmd=="todos") {
			echo "<td width='106'>Estado</td>";
		}
	?>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"5","up"=>$up)) ?>'>
        <td style="cursor:hand;" title="Ordenar por tipo de Factura" width="106">Tipo Factura</td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"2","up"=>$up)) ?>'>
        <td style="cursor:hand;" title="Ordenar por Nº de Factura" width="106">Nº Factura</td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"3","up"=>$up)) ?>'>
        <td style="cursor:hand;" title="Ordenar por nombre de Cliente"  >Cliente</td>
</a>        
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"4","up"=>$up)) ?>'>
        <td style="cursor:hand;" title="Ordenar por usuario de creacion" width="128">Creado por</td>
</a>    
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"6","up"=>$up)) ?>'>
        <td style="cursor:hand;" title="Ordenar por fechas" width="128">Fecha</td>
</a>    
    
	<?
		if ($cmd=="terminados" || $cmd=="recibidos" || $cmd=="todos" || $cmd=="anulados") { 
			echo "<td>&nbsp;</td>";//columna remitos viejos
			echo "<td>&nbsp;</td>";//columna remitos nuevos
			echo "<td>&nbsp;</td>";//columna remitos nuevos
 		}
	?>
</tr>
<?
	while (!$remitos->EOF ) {
		//echo "<br> BACKTO".$backto;
		?>
		 <tr <?=$atrib_tr?> <?if ($backto=='numeros_serie'){?>onclick="window.opener.document.all.nro_remito_asociado.value='<?=$remitos->fields['nro_remito']?>';window.opener.document.all.id_remito_asociado.value='<?=$remitos->fields['id_remito']?>';window.close();" <?}?>  >
		
            <a href="<? if (!window.opener) { echo encode_link("remito_nuevo.php",array("remito"=>$remitos->fields['id_remito'],"cmd"=>$cmd)); }?>" >   
		
		<?
       // echo " <a href='$ref'>";
        echo "<td align='center' style='cursor:hand'>".$remitos->fields['nro_remito']."</td>";
        if($cmd=="todos") {
					echo "<td align='center' style='cursor:hand'>";
					switch ($remitos->fields['estado']) {
		        		case 'a':
		        		case 'A': echo "Anulado";break;
		        		case 'r':
					  	case 'R': echo "Recibido";break;
		        		case 'p':
					  	case 'P': echo "Pendiente";	break;
		        		case 't':
					  	case 'T': echo "Terminado";	break;
					}
					echo "</td>";
        } 
        
      
        echo "<td align='center' style='cursor:hand'>".strtoupper($remitos->fields['tipo_factura'])."</td>";
        echo "<td align='center' style='cursor:hand'>".$remitos->fields['nro_factura']."</td>";
        echo "<td align='left' style='cursor:hand'>".$remitos->fields['cliente']."</td>";
        echo "<td align='center' style='cursor:hand'>".$remitos->fields['usuario']."</td>";
        echo "<td align='center' style='cursor:hand'>".fecha($remitos->fields['fecha'])."</td>";        
		if ($cmd=="terminados" || $cmd=="recibidos" || $cmd=="todos" || $cmd=="anulados") {
			switch ($remitos->fields['estado']) {
				case 'a':
 			    case 'r': 
			    case 't': 
			    $id_remito=$remitos->fields['id_remito'];
			    $sql_seg="select * from facturacion.items_remito where id_remito=$id_remito";
                $res_seg=sql($sql_seg)or fin_pagina();
            
        if ($res_seg->fields['id_renglones_oc'])
	        $seg=1; //el remito se creo desde el menu Produccion/entregas
        else 
        	$seg=0;

			  $link=encode_link("pdf.php", array("id_remito"=>$remitos->fields['id_remito'],"seg"=>$seg,"formato"=>'new'));	
			  $link2=encode_link("pdf.php", array("id_remito"=>$remitos->fields['id_remito'],"seg"=>$seg,"formato"=>'old'));	
			  $link8=encode_link("word.php", array("id_remito"=>$remitos->fields['id_remito'],"seg"=>$seg,"formato"=>'new'));	
				 echo "<td align='center' width='3%' style='cursor:hand'>";
		         echo "<A target='_blank' href='".$link."'><IMG src='$html_root/imagenes/pdf_logo.gif' title='Remitos Nº 5401 o mayor'  height='16' width='16' border='0'> </a>";
		         echo "</td>";
				 echo "<td align='center' width='3%' style='cursor:hand'>";
		         echo "<A target='_blank' href='".$link2."'><IMG src='$html_root/imagenes/pdf_logo.gif' title='Remitos Nº 5300-5400'  height='16' width='16' border='0'></a>";
		         echo "</td>"; 
		         echo "<td align='center' width='3%' style='cursor:hand'>";
		         echo "<A target='_blank' href='".$link8."'><IMG src='$html_root/imagenes/word.gif' height='16' width='16' border='0'></a>";
		         echo "</td>";
		     break;
		     
		     default:
		     	echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
			}
		}
 		echo "</a></tr>";
  		$remitos->MoveNext();
	}
?>

<?
if ($parametros["volver_lic"]) {
		//$ref = encode_link("licitaciones/licitaciones_view",array("cmd1"=>"detalle","ID"=>$parametros["volver_lic"]));
		//echo "<tr><td align=center colspan=6><br><input type=button name=volver style='width:320;' value='Volver a los detalles de la licitacion' onClick=\"parent.document.location='$ref';\"></td></tr>\n";
		echo "<tr><td align=center colspan=6><br><input type=button name=cerrar style='width:320;' value='Volver a los detalles de la licitacion' onclick='window.close();' ></td></tr>\n";
	}
?>

</table>
</form>
<br><? fin_pagina() //echo "Página generada en ".tiempo_de_carga()." segundos.<br>"; ?>
</body>
</html>