<?
/*
$Author: fernando $
$Revision: 1.52 $
$Date: 2006/08/07 19:26:40 $
*/
include "head.php";
$id=$_POST["id"] or $id=$parametros["id"];
//datos Post
$nombre=$_POST["nombre"];
$subjet=$_POST["subjet"];
$descrip=$_POST["descrip"];
$basica=$_POST["basica"];
$garantia=$_POST["garantia"];
if ($_POST["cmd1"]=="Enviar") {
	include('../../lib/htmlmimemail/htmlMimeMail.php');
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

	$rs=$db->execute($sql) or die($db->errormsg()." - ". $sql);
	$fila=$rs->fetchrow();

	$sql = "select descripcion from estadocdr where idcaso=$id order by idestcdr ASC limit 1 offset 0";
	$rs=$db->execute($sql) or die($db->errormsg());
	$fila1=$rs->fetchrow();

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
         On Site: ";
if ($garantia==1) $contenido.="Si";
else $contenido.="No";
$contenido.="&nbsp;
         En Laboratorio: ";
if ($garantia==2) $contenido.="Si";
else $contenido.="No";
$contenido.="</td>
	  <td align=center>
         <u>Característica básica:</u><br>
         $basica
	  </td>
      <td align=center>
         <u>Nº de serie:</u><br>
         ".$fila["nserie"]."
      </td>
	  <td align=center width=25%>
		  <u>Nro. Mac:</u><br>
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

$body = "<html><body MARGINWIDTH=0 MARGINHEIGHT=0 LEFTMARGIN=0 TOPMARGIN=0>";
$body.=$contenido;
$body.="</body></html>";

//constuccion de archivo .doc para mandarlo adjunto al mail.
 $name="caso_".$fila["nrocaso"];
 $name.=".doc";
 $path1="../../uploads/casos";
 if (!is_dir($path1)) mkdirs($path1);
 $temporal=$path1."/".$name;  //linux
 if (is_file($temporal))
	unlink($temporal);
 $fp = fopen($temporal,"w+");
 fwrite($fp,$body);
 fclose($fp);
//fin de constuccion de archivo .doc para mandarlo adjunto al mail.
//Agregados a la descripcion del mail
$descrip="<html>\n<body>\n".html_out($descrip);
$descrip.="<br><br>\n
<table border=1 align=center>\n
	<tr>\n
		<td>\n
			<font size=2 color='red'><< IMPORTANTE: CONFIRMAR RECEPCION DE MAIL CON INFORME DE SERVICIO TECNICO A: <a href='mailto:dcristian@coradir.com.ar'>dcristian@coradir.com.ar</a>. >></font>
		</td>\n
	</tr>\n
</table><br><br>\n";
$descrip.=firma_coradir_mail();
$descrip.="</body>\n</html>";
$mail = new htmlMimeMail();
//$imagen = $mail->getFile('imagenes/logo_coradir.png');
$mail->setHtml($descrip);
//$mail->addHtmlImage($imagen, 'logo_coradir.png', 'image/png');
$attach=$mail->getFile($temporal);
$mail->addattachment($attach,$name,'application/doc');

$mail->setFrom('"Servicio Técnico Coradir S.A." <'.$_ses_user["mail"].'>');
$mail->setReturnPath($_ses_user["mail"]);
$mail->setBcc($_ses_user["mail"]);
$mail->setSubject($subjet);
//echo $descrip;

$para_m[0]=$nombre;
$para_m[1]="dcristian@coradir.com.ar";
$nombre=elimina_repetidos($para_m,0);
if ($mail->send(array($nombre))) {

	$sql="insert into mail (idcaso,fecha,correo) VALUES ($id,'".date("Y-m-d H:i:s")."','$nombre')";
	$db->execute($sql) or die ($db->errormsg());
?>
<br>
	  <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="99%">
		  <tr>
			<td width="100%" bgcolor="#006699" style="border-bottom-style: none; border-bottom-width: medium">
			<p style="margin: 3"><font face="Arial" size="2" color="#FFFFFF">
			<a name="inicio"></a></font>
			<font face="Trebuchet MS" size="2" color="#FFFFFF">
			Envio de mail</font></td>
		  </tr>
		  <tr>
			<td width="100%" style="border-top-style: none; border-top-width: medium" background="imagenes/servtec.gif">
			<p style="margin: 4"><font face="Trebuchet MS" size="2">El mail a sido enviado exitosamente a <? echo $nombre; ?>.</td>
		  </tr>
	  </table>
<p align=right><input type=button name=volver value="<< Volver" onClick="window.location='<? echo encode_link("caso_inf.php",Array("id"=>$id)); ?>';"></p>
<?

}
else {
?>
<br>
	  <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="99%">
		  <tr>
			<td width="100%" bgcolor="#006699" style="border-bottom-style: none; border-bottom-width: medium">
			<p style="margin: 3"><font face="Arial" size="2" color="#FFFFFF">
			<a name="inicio"></a></font>
			<font face="Trebuchet MS" size="2" color="#FFFFFF">
			Envio de mail</font></td>
		  </tr>
		  <tr>
			<td width="100%" style="border-top-style: none; border-top-width: medium" background="imagenes/servtec.gif">
			<p style="margin: 4"><font face="Trebuchet MS" size="2">No se a podido enviar el mail al destinatario: <? echo $nombre; ?>.</td>
		  </tr>
	  </table>
<p align=right><input type=button name=volver value="<< Volver" onClick="window.location='<? echo encode_link("caso_inf.php",Array("id"=>$id)); ?>';"></p>
<?
}
}
else {
?>
  <form action=caso_mail.php method=post>
  <input type=hidden name=id value=<? echo $id?>>
  <br>
	  <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="99%">
		  <tr>
			<td width="100%" bgcolor="#006699" style="border-bottom-style: none; border-bottom-width: medium">
			<p style="margin: 3"><font face="Arial" size="2" color="#FFFFFF">
			<a name="inicio"></a></font>
			<font face="Trebuchet MS" size="2" color="#FFFFFF">
			Enviar el informe por E-m@il</font></td>
		  </tr>
		  <tr>
			<td width="100%" style="border-top-style: none; border-top-width: medium" background="imagenes/servtec.gif">
			<p style="margin: 4"><font face="Trebuchet MS" size="2">¿Necesita los
			drivers de su CDR<sup>®</sup></font></p>
			<p style="margin: 4">
			<font face="Trebuchet MS" size="2">
			Complete correctamente
			los siguientes datos para mandar el mail del caso CDR<sup>®</sup>.
			</td>
		  </tr>
	  </table><br>
	  <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="99%">
		  <tr>
			<td width="100%" bgcolor="#006699" style="border-bottom-style: none; border-bottom-width: medium">
			<p style="margin: 3"><font face="Arial" size="2" color="#FFFFFF">
			<a name="inicio"></a></font>
			<font face="Trebuchet MS" size="2" color="#FFFFFF">
			Formulario de envio</font></td>
		  </tr>
		  <tr>
		  <td align=center>
		   <table width=60% cellspcing=0 cellpadding=0 border=0 align=center>
			<tr>
			<td align=left>
			   <b>A quien:<br>
			   <input type=text name=nombre value='<? echo $_POST["mail"]; ?>' size="62">
			</td>
		  </tr>
		  <tr>
			<td align=left>
			   <b>Titulo:<br>
			   <input type=text name=subjet value='Nro. de caso:<? echo $_POST["caso"]; ?>' size="62">
			</td>
		  </tr>
		  <tr>
			<td align=left>
				<b>Contenido:<br>
			   <textarea name=descrip rows=4 cols=60></textarea>
			</td>
		  </tr>
		  <tr>
			 <td align=center>
				 <input type=hidden name=basica value="<?echo $_POST["basica"];?>">
				 <input type=hidden name=garantia value="<?echo $_POST["garantia"];?>">
				 <p><input type=button name=volver value="<< Volver" onClick="window.location='<? echo encode_link("caso_inf.php",Array("id"=>$id)); ?>';">
				 <input type="submit" name="cmd1" value="Enviar"</p>
              </td>
          </tr>
          </table>
          </td>
          </tr>
      </table>
</form>

<?
}
?>