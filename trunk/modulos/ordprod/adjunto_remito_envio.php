<? 
/*
Autor: lizi

MODIFICADA POR
$Author: mari $
$Revision: 1.14 $
$Date: 2006/10/17 16:02:24 $
*/

require_once("../../config.php");


$id_remito=$_POST['id_remito'] or $id_remito=$parametros['id_remito'];
$id_envio_renglones=$_POST['id_envio_renglones'] or $id_envio_renglones=$parametros['id_envio_renglones'];
$fecha=date("d/m/Y",mktime());
// funcion envio a word
function enviar($nombre_archivo) {
	global $buffer;
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: must-revalidate");
	header("Content-Transfer-Encoding: binary");
	Header('Content-Type: application/dummy');
	Header('Content-Length: '.strlen($buffer));
	Header('Content-disposition: attachment; filename='.$nombre_archivo);
	echo $buffer;
}
// funciones de cabecera y pie de pagina
function cabecera (){
global $fecha, $nro_remito_asoc, $nombre_transporte, $contacto_transporte, $telefono_transporte, $direccion_transporte;
global $hoja_de, $hoja_total, $iter_nro, $hoja_nro;	
 $cabecera="<tr>
   <td width='50%' style='border: 1px solid #000000'> 
      <table align='center' width='100%' class='estilo9' cellpadding='2' cellspacing='2'>
        <tr>
          <td align='left'>
            <img src='https://gestion.coradir.com.ar/imagenes/LogoCoradir.png'>
          </td> 
        </tr>
        <tr>
          <td align='right'>
            <font size='4'><b>Coradir S.A.</b></font><br>Casa Matriz: San Martín 454 - San Luis<br>
            Te: (02652) - 431134 y líneas rotativas<br>Sucursal Buenos Aires: Patagones 2538<br>
            Te: (011) - 5354-0300 y líneas rotativas<br><u>www.coradir.com.ar - mail: info@coradir.com.ar   
          </td>
        </tr>
      </table>
   </td>
   <td  class='estilo6'> 
      <table align='center' cellpadding='0' cellspacing='0' width='100%' class='estilo8'>
        <tr>
          <td>
            <table width='100%'>
              <tr>
                <td align='center' class='estilo5'><font size='8'><b>X</b></td>
                <td class='estilo6'>
                  <table width='100%' class='estilo9' cellpadding='1' cellspacing='1'>
                    <tr><td class='estilo6'>Documento no Válido como Factura</td></tr>
                    <tr><td align='center'><font size='4'><b>Fecha:&nbsp;&nbsp;$fecha</b></td></tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr><td>&nbsp</td></tr>
        <tr>
          <td align='center' class='estilo6'>
            Adjunto al Remito Nº $nro_remito_asoc<br>Hoja Adjunta:  $hoja_de / ";
			if ($_POST["imprimir"]) $cabecera.=$_POST["hoja_total_$iter_nro_$hoja_nro"]; 
			else $cabecera.="<input type=text readonly name='hoja_total_$iter_nro_$hoja_nro' value=1>";
		  $cabecera.="<br><br></td>
        </tr>
        <tr>
          <td align='center'>
            <table width='100%' align='left' class='estilo9' cellpadding='1' cellspacing='1'>
              <tr><td align='left'> Transporte:</td><td> $nombre_transporte</td></tr>
              <tr><td align='left'> Contacto:</td><td> $contacto_transporte</td></tr>
              <tr><td align='left'> Teléfono:</td><td> $telefono_transporte</td></tr>
              <tr><td align='left'> Dirección:</td><td> $direccion_transporte</td></tr>            
            </table>     
          </td>
        </tr>
      </table>
   </td>
 </tr>";
 return $cabecera;
}

function pie(){
global $nros_despacho, $cantidad_total, $origen, $destino, $envio_nro, $id_envio_renglones;	
$pie="<tr><td colspan=2><table class='estilo9' cellpadding='1' width='100%' cellspacing='1'>
<tr>
   <td colspan='2' class='estilo6'>
     <table width='100%' class='estilo9' cellpadding='0' cellspacing='0'>
       <tr><td class='estilo6' bgcolor='e1e1e1'><b>Números de Despacho</b></td></tr>
       <tr><td>$nros_despacho</td></tr> 
     </table>
     <br><br>  
   </td>
 </tr>
 <tr>
   <td class='estilo5'><font size='4'><b>Cantidad de Bultos: &nbsp;&nbsp;$cantidad_total</b></td>";
   $envio_nro=str_pad($id_envio_renglones, 10, '0', STR_PAD_LEFT);
 $pie.="  
   <td class='estilo6'><b>Nro. Envío: &nbsp;&nbsp;$origen-$destino-$envio_nro</b></td>
 </tr>
 <tr>
   <td colspan='2' class='estilo6'></td>
 </tr>
 <tr>
   <td colspan='2'>
     <table cellpadding='0' cellspacing='0' width='100%'>
       <tr><td align='center' class='estilo6' bgcolor='e1e1e1'>Conforme Recepción de Mercaderías</td></tr>
       <tr><td>
         <table align='center' width='100%' class='estilo9' cellpadding='0' cellspacing='0'>
           <tr><td>&nbsp;&nbsp;</td></tr>
           <tr> 
             <td width=30%>Fecha: ___ / ___ / ______</td>
             <td width=40%>Firma, Aclaración: .......................</td>
             <td width=30%>D.N.I.: ..............................</td>
           </tr>
           <tr><td>&nbsp;&nbsp;</td></tr>
         </table>
       </td></tr>
     </table>
   </td>
 </tr></table></td></tr>";
return $pie;
} 

if ($_POST["imprimir"]) {
$buffer="MIME-Version: 1.0
Content-Type: multipart/related; boundary=\"----=_NextPart_01C4D850.CC47B010\"

------=_NextPart_01C4D850.CC47B010
Content-Location: file:///C:/2EEB2DE1/word2003-mejorULTIMA.htm
Content-Type: text/html; charset=\"us-ascii\"

<html xmlns:v=3D\"urn:schemas-microsoft-com:vml\"
xmlns:o=3D\"urn:schemas-microsoft-com:office:office\"
xmlns:w=3D\"urn:schemas-microsoft-com:office:word\"
xmlns=3D\"http://www.w3.org/TR/REC-html40\">
<head>
<title>Adjunto Remito</title>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=3Dus-ascii\">
<meta name=3DProgId content=3DWord.Document>

<style type=\"text/css\">
<!--
<!-- este estilo es el borde izq y der -->
.estilo2 {
	border-right-width: 1px;
	border-left-width: 1px;
	border-right-style: solid;
	border-left-style: none;
	border-right-color: #000000;
	border-left-color: #000000;
}
<!-- este estilo es el borde de afuera de la tabla -->
.estilo3 {
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-right-style: solid;
	border-bottom-style: solid;
	border-left-style: solid;
	border-top-color: #000000;
	border-right-color: #000000;
	border-bottom-color: #000000;
	border-left-color: #000000;
}
.estilo4 {
	border-top-width: 1px;
	border-top-style: solid;
	border-top-color: #000000;
	border-right-color: #000000;
	border-bottom-color: #000000;
	border-left-color: #000000;
}
.estilo5 {
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-bottom-style: solid;
	border-top-color: #000000;
	border-right-color: #000000;
	border-bottom-color: #000000;
	border-left-color: #000000;
	border-right-style: solid;
}
.estilo6 {
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-bottom-style: solid;
	border-top-color: #000000;
	border-right-color: #000000;
	border-bottom-color: #000000;
	border-left-color: #000000;
}
.Estilo7 {color: #FF0000}
.estilo1 {
	border: 2px solid #000000;
}
.estilo8 {
	border-right-width: 1px;
	border-left-width: 1px;
	border-right-style: none;
	border-left-style: solid;
	border-right-color: #000000;
	border-left-color: #000000;
}
.estilo9{
    font-size: 10pt;
}
.estilo10{
    font-family: Verdana, sans-serif, helvetica;
	font-size: 8pt;
}
@page Section1
	{size:612.0pt 792.0pt;
	margin:1.0cm 1.0cm 1.0cm 1.0cm;
	mso-header-margin:35.45pt;
	mso-footer-margin:35.45pt;
	mso-paper-source:0;}
div.Section1
	{page:Section1;}
-->
</style>

</head>
<body>

<div class=3DSection1>";
// hacer el control dentro de la apgina de los adjuntos asi no se recarga la apgina de preparar_envios !
$q="select distinct id_remito from licitaciones_datos_adicionales.renglones_bultos 
	where id_envio_renglones=$id_envio_renglones and not id_remito is null";
$res_q=sql($q, "Error al traer los nros. de remitos asociados q este envío") or fin_pagina();
$result=$res_q->RecordCount();
	
$q_ns="select nro_serie from licitaciones_datos_adicionales.nro_serie_renglon
	   left join licitaciones_datos_adicionales.renglones_bultos using (id_renglones_bultos)
	   where id_envio_renglones=$id_envio_renglones and (nro_serie <> '' and not nro_serie is null )";
$res_q2=sql($q_ns, "Error al traer los nros. de serie") or fin_pagina();
$result2=$res_q2->RecordCount();
if ($result==0 || $result2==0) {
	$msg="Faltan ingresar el remito asociado a este Envío";
	$msg2="Faltan ingresar los números de serie para los renglones de este Envío";
	$buffer.="<table align='center' width='50%'>
      <tr><td align='center'><b><font size='3'>Mensaje:<br><font color='Red'>$msg<br>$msg2</td></tr>
      <tr><td align='center'>
      <input type='button' name='cerrar' value='Cerrar Ventana' onclick='window.close();'>
      </tr></td>
    </table>"; 
}
else {
$q_e_t="select cantidad_total, nombre_transporte, direccion_transporte,
		 telefono_transporte
		from licitaciones_datos_adicionales.envio_renglones
		left join licitaciones_datos_adicionales.transporte using (id_transporte)
		where id_envio_renglones=$id_envio_renglones";
$res_q_e_t=sql ($q_e_t, "Error al traer los Datos del Envío y Transporte") or fin_pagina();
$cantidad_total=$res_q_e_t->fields['cantidad_total'];
$nombre_transporte=$res_q_e_t->fields['nombre_transporte'];
//$contacto_transporte=$res_q_e_t->fields['contacto_transporte'];
$telefono_transporte=$res_q_e_t->fields['telefono_transporte'];
$direccion_transporte=$res_q_e_t->fields['direccion_transporte'];

$q_d="select deco_envio_origen, deco_envio_destino 
	  from licitaciones_datos_adicionales.datos_envio
	  left join licitaciones_datos_adicionales.envio_origen using (id_envio_origen)
	  left join licitaciones_datos_adicionales.envio_destino using (id_envio_destino) 
	  where id_envio_renglones=$id_envio_renglones";
$res_q_d=sql($q_d, "Error la traer los datos del origen y destinod el envío") or fin_pagina();
$origen=$res_q_d->fields['deco_envio_origen'];
$destino=$res_q_d->fields['deco_envio_destino'];
$iter_nro=1;

	while (!$res_q->EOF) {
		//echo "   nro de iteracion:    $iter_nro<br>";
		/////////////////
		$id_remito=$res_q->fields['id_remito'];
        $q_e_b="select id_renglones_bultos, cantidad_enviada, titulo_mod, nro_despacho, 
                numeracion_sucursal.numeracion || text('-') || remitos.nro_remito as nro_remito, id_renglones_oc
				from licitaciones_datos_adicionales.renglones_bultos
				left join facturacion.remitos using (id_remito)
				left join facturacion.numeracion_sucursal using (id_numeracion_sucursal)
				where id_envio_renglones=$id_envio_renglones and id_remito=$id_remito";
		$res_q_e_b=sql($q_e_b, "Error al traer los Datos de los Bultos del Envío") or fin_pagina();
		$nros_despacho=$res_q_e_b->fields['nro_despacho'];
		$nro_remito_asoc=$res_q_e_b->fields['nro_remito'];
		$res_q_e_b->MoveNext();
		while (!$res_q_e_b->EOF) {
			$nros_despacho.=" - ".$res_q_e_b->fields['nro_despacho'];
			$res_q_e_b->MoveNext();
		}
		$res_q_e_b->MoveFirst(); 
		$res_q->MoveNext();
		$buffer.="<table width='100%' cellpadding='0' cellspacing='0' class='estilo1'>";
		$cant_hojas_adjuntas=1;
		$hoja_de=1;
		$hoja_nro=1; 
		$buffer.=cabecera();
		$contador=0; 
		$buffer.="<tr>
			   <td colspan='2'  class='estilo6'>
			     <table width='100%' cellpadding='4' cellspacing='0' class='estilo9'>
			       <tr>
			         <td width='1%' class='estilo5' bgcolor='e1e1e1'><b>Cantidad</b></td>
			         <td width='90%' align='center' class='estilo6' bgcolor='e1e1e1'><b>Descripción</b></td>
			       </tr>";
		$res_q_e_b->Move(0);
		while (!$res_q_e_b->EOF) {
		   	//echo "                  nro de hoja:    $hoja_nro<br>";
		   	// agrego el control para q los datos se impriman bien 
		    if ($contador >= 24) {
				$hoja_nro++;
			    $buffer.=pie();
			    $cant_hojas_adjuntas++;
			    // incluir salto de pagina
			    $buffer.="</table>
			             </td></tr></table>
			             <br clear=all style='page-break-before:always'>
			             <table width='100%' cellpadding='0' cellspacing='0' class='estilo1'>"; 
			    $hoja_de++; 
			    $buffer.=cabecera();
			    $contador=0;
			    $buffer.="<tr>
			              <td colspan='2'  class='estilo6'>
			                  <table width='100%' cellpadding='4' cellspacing='0' class='estilo9'>
			                     <tr>
							         <td width='1%' class='estilo5' bgcolor='e1e1e1'><b>Cantidad</b></td>
							         <td width='90%' align='center' class='estilo6' bgcolor='e1e1e1'><b>Descripción</b></td>
			                     </tr>";
			}	
			$cantidad=$res_q_e_b->fields['cantidad_enviada'];
			$descripcion=$res_q_e_b->fields['titulo_mod'];
			$id_renglones_bultos=$res_q_e_b->fields['id_renglones_bultos'];
			$id_renglones_oc=$res_q_e_b->fields['id_renglones_oc'];
			               
			$q_n_s="select nro_serie from licitaciones_datos_adicionales.nro_serie_renglon
				where id_renglones_bultos=$id_renglones_bultos";
			$res_qn_s=sql($q_n_s, "Error al traer los Números de Serie de los Renglones") or fin_pagina();
			            
			$nros_serie=$res_qn_s->fields['nro_serie'];
			$res_qn_s->MoveNext();
			while (!$res_qn_s->EOF) {
				$nros_serie.=" - ".$res_qn_s->fields['nro_serie'];
				$res_qn_s->MoveNext();
			}
			$contenido_fila=strtoArray("$descripcion\n$nros_serie", 75); 
			//$long_fila=strlen("$descripcion\n$nros_serie");  
			$long_fila=sizeof($contenido_fila);  
			$contador+=$long_fila; 
			//echo $long_fila; 
			$buffer.="<tr> 
			         <td width='1%' align='right' class='estilo5'>$cantidad&nbsp;&nbsp;</td>
			         <td width='90%' class='estilo6'>&nbsp;&nbsp;<b>$descripcion</b><br>&nbsp;&nbsp;$nros_serie</td>
			       </tr>";
			$res_q_e_b->MoveNext();
			          
		} // while (!$res_q_e_b->EOF)
		if ($contador<24) {
			$buffer.="<tr><td>";  
			while ($contador<24) {
				$buffer.="<br>"; 
				$contador++; 
			}
			$buffer.="</td></tr>"; 
		}    
		$buffer.="</table> 
			   </td>
			 </tr>";
		$buffer.=pie();
		$hoja_total=$cant_hojas_adjuntas;
		$buffer.="</table>
			<br clear=all style='page-break-before:always'>
			<script>";
			for ($e=1;$e<=$hoja_total;$e++) {
				$buffer.="document.all.hoja_total_$iter_nro_$e.value=$hoja_total";
			}
			$buffer.="</script>";	    
			$iter_nro++;
	} // fin del while grande
}  // fin del else
$buffer.="</div></body>
</html>

------=_NextPart_01C4D850.CC47B010--";
enviar("prueba.doc");
}
?>

<script language="javascript">

function imprimir(){
 document.all.imprimir.style.visibility="hidden";
 window.print();
 window.close();
}

</script>

<html>
<head>
<title>Adjunto Remito</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<style type="text/css">
<!--
<!-- este estilo es el borde izq y der -->
.estilo2 {
	border-right-width: 1px;
	border-left-width: 1px;
	border-right-style: solid;
	border-left-style: none;
	border-right-color: #000000;
	border-left-color: #000000;
}
<!-- este estilo es el borde de afuera de la tabla -->
.estilo3 {
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-right-style: solid;
	border-bottom-style: solid;
	border-left-style: solid;
	border-top-color: #000000;
	border-right-color: #000000;
	border-bottom-color: #000000;
	border-left-color: #000000;
}
.estilo4 {
	border-top-width: 1px;
	border-top-style: solid;
	border-top-color: #000000;
	border-right-color: #000000;
	border-bottom-color: #000000;
	border-left-color: #000000;
}
.estilo5 {
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-bottom-style: solid;
	border-top-color: #000000;
	border-right-color: #000000;
	border-bottom-color: #000000;
	border-left-color: #000000;
	border-right-style: solid;
}
.estilo6 {
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-bottom-style: solid;
	border-top-color: #000000;
	border-right-color: #000000;
	border-bottom-color: #000000;
	border-left-color: #000000;
}
.Estilo7 {color: #FF0000}
.estilo1 {
	border: 2px solid #000000;
}
.estilo8 {
	border-right-width: 1px;
	border-left-width: 1px;
	border-right-style: none;
	border-left-style: solid;
	border-right-color: #000000;
	border-left-color: #000000;
}
.estilo9{
    font-family: Verdana, sans-serif, helvetica;
	font-size: 10pt;
}
.estilo10{
    font-family: Verdana, sans-serif, helvetica;
	font-size: 8pt;
}
-->
</style>

</head>
<body>

<?
// hacer el control dentro de la apgina de los adjuntos asi no se recarga la apgina de preparar_envios !
$q="select distinct id_remito from licitaciones_datos_adicionales.renglones_bultos 
	where id_envio_renglones=$id_envio_renglones and not id_remito is null";
$res_q=sql($q, "Error al traer los nros. de remitos asociados q este envío") or fin_pagina();
$result=$res_q->RecordCount();
	
$q_ns="select nro_serie from licitaciones_datos_adicionales.nro_serie_renglon
	   left join licitaciones_datos_adicionales.renglones_bultos using (id_renglones_bultos)
	   where id_envio_renglones=$id_envio_renglones and (nro_serie <> '' and not nro_serie is null )";
$res_q2=sql($q_ns, "Error al traer los nros. de serie") or fin_pagina();
$result2=$res_q2->RecordCount();
if ($result==0 || $result2==0) {
	$msg="Faltan ingresar el remito asociado a este Envío";
	$msg2="Faltan ingresar los números de serie para los renglones de este Envío"; ?>
	<table align="center" width="50%">
      <tr><td align="center"><b><font size="3">Mensaje:<br><font color="Red"><?=$msg."<br>".$msg2?></td></tr>
      <tr><td align="center">
      <input type="button" name="cerrar" value="Cerrar Ventana" onclick="window.close();">
      </tr></td>
    </table> 
<?	
fin_pagina();
}
else { ?>
<?
$q_e_t="select cantidad_total, nombre_transporte, direccion_transporte,

		telefono_transporte

		from licitaciones_datos_adicionales.envio_renglones
		left join licitaciones_datos_adicionales.transporte using (id_transporte)
		where id_envio_renglones=$id_envio_renglones";
$res_q_e_t=sql ($q_e_t, "Error al traer los Datos del Envío y Transporte") or fin_pagina();
$cantidad_total=$res_q_e_t->fields['cantidad_total'];
$nombre_transporte=$res_q_e_t->fields['nombre_transporte'];
//$contacto_transporte=$res_q_e_t->fields['contacto_transporte'];
$telefono_transporte=$res_q_e_t->fields['telefono_transporte'];
$direccion_transporte=$res_q_e_t->fields['direccion_transporte'];

$q_d="select deco_envio_origen, deco_envio_destino 
	  from licitaciones_datos_adicionales.datos_envio
	  left join licitaciones_datos_adicionales.envio_origen using (id_envio_origen)
	  left join licitaciones_datos_adicionales.envio_destino using (id_envio_destino) 
	  where id_envio_renglones=$id_envio_renglones";
$res_q_d=sql($q_d, "Error la traer los datos del origen y destinod el envío") or fin_pagina();
$origen=$res_q_d->fields['deco_envio_origen'];
$destino=$res_q_d->fields['deco_envio_destino'];
$iter_nro=1;

	while (!$res_q->EOF) {
		//echo "   nro de iteracion:    $iter_nro<br>";
		/////////////////
		$id_remito=$res_q->fields['id_remito'];
        $q_e_b="select id_renglones_bultos, cantidad_enviada, titulo_mod, nro_despacho, 
                numeracion_sucursal.numeracion || text('-') || remitos.nro_remito as nro_remito, id_renglones_oc
				from licitaciones_datos_adicionales.renglones_bultos
				left join facturacion.remitos using (id_remito)
				left join facturacion.numeracion_sucursal using (id_numeracion_sucursal)
				where id_envio_renglones=$id_envio_renglones and id_remito=$id_remito";
		$res_q_e_b=sql($q_e_b, "Error al traer los Datos de los Bultos del Envío") or fin_pagina();
		$nros_despacho=$res_q_e_b->fields['nro_despacho'];
		$nro_remito_asoc=$res_q_e_b->fields['nro_remito'];
		$res_q_e_b->MoveNext();
		while (!$res_q_e_b->EOF) {
			$nd=$res_q_e_b->fields['nro_despacho'];
			if ($nd!="") $nros_despacho.=" - ".$nd;
			$res_q_e_b->MoveNext();
		}
		$res_q_e_b->MoveFirst(); 
		$res_q->MoveNext();
		////////////////////// ?>
			<form action="adjunto_remito_envio.php" method="post">
			<input type="hidden" name="id_envio_renglones" value="<?=$id_envio_renglones;?>">
			<input type="hidden" name="id_remito" value="<?=$id_remito;?>">
			<table width="100%" cellpadding="0" cellspacing="0" class="estilo1">
			 <? $cant_hojas_adjuntas=1;
			    $hoja_de=1;
			    $hoja_nro=1; 
			    echo cabecera();
			    $contador=0; 
			    ?>
			 <tr>
			   <td colspan="2"  class="estilo6">
			     <table width="100%" cellpadding="4" cellspacing="0" class="estilo9">
			       <tr>
			         <td width="1%" class="estilo5" bgcolor="e1e1e1"><b>Cantidad</b></td>
			         <td width="90%" align="center" class="estilo6" bgcolor="e1e1e1"><b>Descripción</b></td>
			       </tr>
			       <?
			        $res_q_e_b->Move(0);
			       
			        while (!$res_q_e_b->EOF) {
			        	//echo "                  nro de hoja:    $hoja_nro<br>";
			        	// agrego el control para q los datos se impriman bien 
			          if ($contador >= 24) {
			          	 $hoja_nro++;
			             echo pie();
			             $cant_hojas_adjuntas++;
			             // incluir salto de pagina
			             ?>
			             </table>
			             </td></tr></table>
			             <br clear=all style='page-break-before:always'>
			             <table width="100%" cellpadding="0" cellspacing="0" class="estilo1"> 
			             <?
			             $hoja_de++; 
			             
			             echo cabecera();
			             $contador=0;
			             ?>
			             <tr>
			              <td colspan="2"  class="estilo6">
			                  <table width="100%" cellpadding="4" cellspacing="0" class="estilo9">
			                     <tr>
							         <td width="1%" class="estilo5" bgcolor="e1e1e1"><b>Cantidad</b></td>
							         <td width="90%" align="center" class="estilo6" bgcolor="e1e1e1"><b>Descripción</b></td>
			                     </tr>
			             <?        
			          }	
			       	    $cantidad=$res_q_e_b->fields['cantidad_enviada'];
			       	    $descripcion=$res_q_e_b->fields['titulo_mod'];
			       	    $id_renglones_bultos=$res_q_e_b->fields['id_renglones_bultos'];
			       	    $id_renglones_oc=$res_q_e_b->fields['id_renglones_oc'];
			             //and id_renglones_oc=$id_renglones_oc  
			       	    $q_n_s="select nro_serie from licitaciones_datos_adicionales.nro_serie_renglon
			       	            where id_renglones_bultos=$id_renglones_bultos ";
			            $res_qn_s=sql($q_n_s, "Error al traer los Números de Serie de los Renglones") or fin_pagina();
			            
			            $nros_serie=$res_qn_s->fields['nro_serie'];
			            $res_qn_s->MoveNext();
			            while (!$res_qn_s->EOF) {
			                $nros_serie.=" - ".$res_qn_s->fields['nro_serie'];
			                $res_qn_s->MoveNext();
			                 }
			            $contenido_fila=strtoArray("$descripcion\n$nros_serie", 75); 
			            //$long_fila=strlen("$descripcion\n$nros_serie");  
			            $long_fila=sizeof($contenido_fila);  
			            $contador+=$long_fila; 
			            //echo $long_fila; 
			        	?>
			       <tr> 
			         <td width="1%" align="right" class="estilo5"><?=$cantidad?>&nbsp;&nbsp;</td>
			         <td width="90%" class="estilo6">&nbsp;&nbsp;<b><?=$descripcion?></b><br>&nbsp;&nbsp;<?=$nros_serie?></td>
			       </tr>
			      <? $res_q_e_b->MoveNext();
			          
			           } // while (!$res_q_e_b->EOF)
			       if ($contador<24) {
			        echo "<tr><td>";  
			       	while ($contador<24) {
			             echo "<br>"; 
			             $contador++; 
			         }
			        echo "</td></tr>"; 
			       }    
			        ?>
			      </table> 
			   </td>
			 </tr>
			<? echo pie();
			   $hoja_total=$cant_hojas_adjuntas;
			?>
			</table>
			<br clear=all style='page-break-before:always'>
			<script>
			<? for ($e=1;$e<=$hoja_total;$e++){ ?>
			document.all.hoja_total_<?=$iter_nro."_".$e?>.value=<?=$hoja_total?>;
			<? } ?>
			</script>	    
			<?  $iter_nro++;
	    } // fin del while grande
	}  // fin del else
?>
<center>
<input type="submit" name="imprimir" value="imprimir">
</center>
</form>
</body>
</html>