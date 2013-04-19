<?
/*
$Author: fernando $
$Revision: 1.16 $
$Date: 2005/06/24 18:13:10 $
*/
include "../../config.php";
?>
<html>
<head>
<title>Imprimir</title>
</head>
<body onload="window.print(); window.close();">
<link rel=stylesheet type='text/css' href='<?echo "$html_root/lib/estilos.css"?>'>
<?php
$id=$_GET["id"];
$garantia=$_GET["garantia"];
$basica=$_GET["basica"];

$sql="SELECT ";
$sql.=" casos_cdr.idcaso,casos_cdr.nrocaso,casos_cdr.fechainicio,";
$sql.=" casos_cdr.nserie,casos_cdr.deperfecto,";
$sql.=" entidad.nombre as organismo,dependencia,";
$sql.=" dependencias.direccion as domicilio,dependencias.lugar as localidad,";
$sql.=" distrito.nombre as provincia,dependencias.cp,";
$sql.=" dependencias.contacto,dependencias.telefono,dependencias.mail as email,";
$sql.=" cas_ate.nombre,cas_ate.tel,cas_ate.mail ";
$sql.=" from casos_cdr ";
$sql.="	left join dependencias USING(id_dependencia) ";
$sql.="	left join distrito on dependencias.id_distrito=distrito.id_distrito ";
$sql.="	left join entidad USING(id_entidad) ";
$sql.=" left join cas_ate USING(idate) ";
$sql.="	where casos_cdr.idcaso=$id";

$rs=$db->execute($sql) or die($db->errormsg());
$fila=$rs->fetchrow();

$contenido="
<TABLE width='99%' border=0 cellpadding='2' cellspacing='0' bgcolor='white'>
<tr>
	<td width=50% align=center valign=center>
	   <font size=5>
	   <b>Caso N°: ".$fila["nrocaso"]."</b>
	   </font>
   </td>
   <td width=50% align=center valign=center>
	  <img src='../ordprod/logo_coradir_prod.png' border=0>
   </td>
</tr>
</table>
<TABLE width='99%' border=1 cellpadding='2' cellspacing='0' bgcolor='white' bordercolor='#000000'>
   <TR>
      <TD colspan=2 align=center>
         <h1>Informe de Servicio Técnico</h1>
      </TD>
   </TR>
   <tr>
      <td align=center>
         <h3>C.A.S.: ".$fila["nombre"]."</h3>
      </td>
	  <td align=center>
         <h3>Fecha: ".fecha($fila["fechainicio"])."</h3>
      </td>
   </tr>
      <td colspan=2 align=left>
       <b>
       La empresa no se responsabiliza por el soft (programas), datos o cualquier
       otra información que se encuentra instalado en el Disco Rígido del CPU.
       Se recomienda hacer un BACKUP (resguardo de archivos) de la información
       importante.
       </b>
      </td>
   </tr>

   </tr>
</TABLE>
<TABLE width='99%' border=1 cellpadding='0' cellspacing='0' bgcolor='white' bordercolor='#000000'>
   <TR>
      <td colspan=4 align=center>
         <h2>Datos del Cliente</h2>
      </td>
   </tr>
   <tr>
      <td width=50% colspan=2 align=center>
         <u>Organismo y Dependencia:</u><br>
         ".$fila["organismo"]." - ".$fila["dependencia"]."
      </td>
      <td align=center>
		 <b><u>Dirección:</u><br>
         ".$fila["domicilio"]."</b>
      </td>
      <td align=center>
         <u>Localidad:</u><br>
         ".$fila["localidad"]."
      </td>
   </tr>
   <tr>
      <td align=center>
         <b><u>Nombre del Contacto</u><br>
         ".$fila["contacto"]."</b>
      </td>
      <td align=center>
         <u>Teléfono:</u><br>
         ".$fila["telefono"]."
      </td>
      <td align=center>
         <u>Provincia:</u><br>
         ".$fila["provincia"]."
	  </td>
      <td align=center>
         <u>Código Postal:</u><br>
         ".$fila["cp"]."
      </td>
   </tr>
   <TR>
      <td colspan=4 align=center>
         <h2>Datos del Equipo</h2>
      </td>
   </tr>
   <tr>
	  <td align=center>
         <u>Tipo garantía</u><br>
         On Site: <input type=radio name=garantia value=1";
if ($garantia==1) $contenido.=" checked";
$contenido.=">&nbsp;
         En Laboratorio: <input type=radio name=garantia value=2";
if ($garantia==2) $contenido.=" checked";
$contenido.=">
	  </td>
	  <td align=center>
         <u>Característica básica:</u><br>
         <input type=text name=basica value='$basica'>
	  </td>
      <td align=center>
         <u>Nº de serie:</u><br>
         ".$fila["nserie"]."
      </td>
	  <td align=center width=25%>
		  <u>Dirección Física (Nro. Mac):</u><br>
		  &nbsp;
	  </td>
   </tr>
   <tr>
      <td colspan=4>
      <b>Falla que informa el cliente:</b>&nbsp;".$fila["deperfecto"].".
	  </td>
   </tr>
   <tr>
      <td colspan=4>";
$sql = "select descripcion from estadocdr where idcaso=$id order by idestcdr ASC limit 1 offset 0";
$rs=$db->execute($sql) or die($db->errormsg());
$fila1=$rs->fetchrow();
$contenido.="<b>Trabajo estimado a realizar:</b> ".$fila1["descripcion"]."
		</td>
   </tr>
   <tr>
	   <td colspan=4 align=center>
		   <TABLE width='100%' border=0 cellpadding='0' cellspacing='0'>
		   <tr>
			   <td style='border: solid;border-width:1;border-color:#000000;' colspan=5 align=center>
				   <h2>Informe de lo realizado por el CAS:</h2>
			   </td>
		   </tr>
		   <tr>
			   <td width=25% style='border: solid;border-width:1;border-color:#000000;' align=center>
				   <b>Parte Dañada:</b>
			   </td>
			   <td style='border: solid;border-width:1;border-color:#000000;'>
				   Descripción:<br>&nbsp;
			   </td>
			   <td style='border: solid;border-width:1;border-color:#000000;'>
				   Marca:<br>&nbsp;
			   </td>
			   <td style='border: solid;border-width:1;border-color:#000000;'>
				   Modelo:<br>&nbsp;
			   </td>
			   <td style='border: solid;border-width:1;border-color:#000000;'>
				   N° de serie:<br>&nbsp;
			   </td>
		   </tr>
		   <tr>
			   <td width=25% align=center style='border: solid;border-width:1;border-color:#000000;'>
				   <b>Parte OK Recibida:</b>
			   </td>
			   <td style='border: solid;border-width:1;border-color:#000000;'>
				   Descripción:<br>&nbsp;
			   </td>
			   <td style='border: solid;border-width:1;border-color:#000000;'>
				   Marca:<br>&nbsp;
			   </td>
			   <td style='border: solid;border-width:1;border-color:#000000;'>
				   Modelo:<br>&nbsp;
			   </td>
			   <td style='border: solid;border-width:1;border-color:#000000;'>
				   N° de serie:<br>&nbsp;
			   </td>
		   </tr>
		   <tr>
			   <td colspan=5 style='border: solid;border-width:1;border-color:#000000;'>
				   <b>Trabajo Realizado:</b>
				   <hr class='hr_punto'>
				   <hr class='hr_punto'>
				   <hr class='hr_punto'>
				   <hr class='hr_punto'>
			   </td>
		   </tr>
		   </table>
	   </td>
   </tr>
   <tr>
      <td colspan=4 align=center>
	  <TABLE width='100%' border='1' bordercolor='#000000' cellpadding='0' cellspacing='0'>
	  <tr>
		<td rowspan=3 width=10%>
         <h3>Responsable<br>del Servicio<br>Técnico</h3>
      </td>
      <td whidt=40%>
         <u>Firma:</u><br><br>
		 <hr class='hr_punto'>
      </td>
      <td rowspan=3 width=10%>
         <h3>Aprobación<br>
         del Cliente</h3>
      </td>
      <td width=40%>
         <u>Firma:</u><br><br>
		 <hr class='hr_punto'>
      </td>
   </tr>
      <td>
         <u>Aclaración:</u><br><br>
		 <hr class='hr_punto'>
      </td>
      <td>
         <u>Aclaración:</u><br><br>
		 <hr class='hr_punto'>
      </td>
   </tr>
   </tr>
      <td height='10'>
         <u>Cargo:</u><br><br>
		 <hr class='hr_punto'>
	  </td>
      <td height='10'>
         <u>Fecha:</u><br><br>
		 <hr class='hr_punto'>
	  </td></tr></table>
      </td>
   </tr>
</table><br>
<table width='99%' style='border: 1px solid #000000;' cellpadding='2' cellspacing='0' bgcolor='white'>
<tr>
   <td>
      <p style='font-size: 8px;'>Casa Central: San Martín 454- (5700) San Luis.  Telefax (02652) 431134 / 435940<br>
      Sucursal: Patagones 2538 - Parque Patricios - (C1071AAI) Bs. As.  Tel/Fax: (011)5354-0300 y rotativas.<br>
      http://www.coradir.com.ar - e-mail: serviciotecnico@coradir.com.ar</p>
   </td>
</tr>
</table><br>";
echo $contenido;
?>
</body>
</html>