<?
/*
Autor: GACZ

MODIFICADA POR
$Author: mari $
$Revision: 1.3 $
$Date: 2007/01/05 14:32:01 $
*/

require_once("../../config.php");
require("fns.php");

//error_reporting(8);
extract($_POST,EXTR_SKIP);
if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

$f1=date("Y/m/j H:i:s");
	
if ($boton2=="Cancelar")
{
$link=encode_link("ord_pago_listar.php",array('msg'=>"Envio de Email Cancelado"));
header("location: $link");
die;	
}
//print_r($_POST);die;
if ($boton2=="Enviar mail")
{

$boundary = strtoupper(md5(uniqid(time())));
                      $filepath="./PDF/";
                      $i=1;

                     
$email=PostvartoArray("email_");

//para cada orden

while ($clave_valor=each($array_ordenes))
{
	
			//se asume que todos tiene la misma cantidad de entradas
		  $clave_valor2=each($email);//recupero el email
		  $clave_valor3=each($reply_mails);//recupero los mail de respuesta
		 $nro_orden=$clave_valor[1];
		 include("../ord_pago/ord_pago_pdf.php");
          $filename="orden_de_pago_$clave_valor[1].pdf";
	     $mailtext="Este es un mail enviado automaticamente por orden de compra.\n";
		 $mailtext.="\n\n".detalle_orden($nro_orden,1);
		 $mailtext.= "\n\nSr. proveedor:
                     Por favor responda este mail dando su conformidad para realizar esta transacción en los términos definidos.
                     En caso de haber diferencias por favor márquelas.
                     Al concurrir a CORADIR con la mercadería, el proveedor deberá llevar la factura y una copia impresa de la Orden de Compra correspondiente, como así también un recibo de pago.
                     De no concurrir con alguna de estas premisas, la demora ocasionada al transportista como así también la eventual devolución serán TOTALMENTE ABONADAS POR EL PROVEEDOR, sin aceptación de justificativo alguno."; 
	     $mail_header="";
	     $mail_header .= "MIME-Version: 1.0"; 
		  $mail_header .= "\nFrom: Sistema Inteligente de CORADIR <$clave_valor3[1]>";
		   $mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
		  $mail_header .="\nTo: $clave_valor2[1]"; 
//		  $mail_header .="\nBcc: ordenesdecompraenviadas@coradir.com.ar";
		  		  
		  $mail_header .= "\nReply-To: $clave_valor3[1]";
		  $mail_header .= "\nContent-Type: multipart/mixed; boundary=$boundary"; 
		  $mail_header .= "\n\nThis is a multi-part message in MIME format "; 
		  // Mail-Text 
		  $mail_header .= "\n--$boundary"; 
		  $mail_header .= "\nContent-Type: text/plain"; 
		  $mail_header .= "\nContent-Transfer-Encoding: 8bit"; 
		  $mail_header .= "\n\n" . $mailtext."\n"; 
		  // Your File  
		  $mail_header .= "\n--$boundary"; 
		  $mail_header .= "\nContent-Type: application/pdf; name=\"$filename\""; 
	     // Read from Array $contenttypes the right MIME-Typ 
	      $mail_header .= "\nContent-Transfer-Encoding: base64"; 
	   //   $mail_header .= "\n\n".$archivo=chunk_split(base64_encode(fread(fopen($filepath.$filename, "r"), filesize($filepath.$filename)))); 
	   $mail_header .= "\n\n".$archivo=chunk_split(base64_encode($pdf->Output("compra", false, true))); 
		  $mail_header .= "\n--$boundary"; 
		  $mail_header .= "\nContent-Type: text/plain"; 
		  $mail_header .= "\nContent-Transfer-Encoding: 8bit"; 
		  $mail_header .= "\n\n" . firma_coradir()."\n"; 
	      // End 
	      $mail_header .= "\n--$boundary--"; 
	      //echo 'Buffer'.$pdf->Output("compra", false, true)."<br>";

	     
	      if (mail("","Orden de Compra Autorizada Nro: $clave_valor[1]","",$mail_header))
	     { //echo "".$mailtext;
	      $q="update orden_de_compra set estado='m' where estado='e' AND nro_orden=$clave_valor[1]";
			sql($q,"$q") or fin_pagina();	      
			$q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($clave_valor[1],'de envio','".$_ses_user['login']."','$f1')";
			sql($q, "$q") or fin_pagina();
			$q="insert into ord_compra_mails (nro_orden,asunto,para,fecha_envio,user_login,user_name) values ($clave_valor[1],'Orden de Compra Autorizada Nro: $clave_valor[1]','$clave_valor2[1]','$f1','$_ses_user[login]','$_ses_user[name]')";
			sql($q,"$q") or fin_pagina();
	      } 
}

$link=encode_link("ord_pago_listar.php",array('msg'=>"Sus Mensajes se enviaron con exito "));
header("location: $link");
die;	
	
}
else 
{
$query.=" ORDER BY $orden";	

while ($clave_valor=each($_POST))
{
  if (is_int(strpos($clave_valor[0],"chk_"))) //verifico si fue chequeado o no
  {
  			$ordenes.=$clave_valor[1].",";
  			$array_ordenes[]=$clave_valor[1];
  			
  }
}
$ordenes=substr($ordenes,0,strlen($ordenes)-1);//quita la ultima coma
//echo "<br>ordenes : $ordenes<br>";

$campos="orden_de_compra.nro_orden,usuarios.mail as reply_mail,contactos.*,proveedor.razon_social as nbreprov";
$sql="select $campos from 
orden_de_compra JOIN 
proveedor on orden_de_compra.id_proveedor=proveedor.id_proveedor LEFT JOIN 
log_ordenes l on l.nro_orden=orden_de_compra.nro_orden AND l.tipo_log='de creacion' LEFT JOIN
contactos on orden_de_compra.id_contacto=contactos.id_contacto LEFT JOIN 
usuarios on l.user_login=usuarios.login 
where orden_de_compra.nro_orden in ($ordenes)";

//$ordenes=$db->Execute($sql) or reportar_error($sql,__FILE__,__LINE__);
$ordenes=sql($sql,"$sql") or fin_pagina();
?>
<html>
<head>
<link rel=stylesheet type='text/css' href='<? echo $html_root; ?>/lib/estilos.css'>
<title>Lista de Remitos</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<!-- libreria que contiene la funcion trim() -->
<script language="JavaScript"src="../ord_compra/funciones.js"></script>
<script>
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;src.style.cursor="hand";
}
function bajo(src,color_default) {
    src.style.backgroundColor=color_default;src.style.cursor="default";
}
function link_sobre(src) {
    src.id='me';
}
function link_bajo(src) {
    src.id='mi';
}

function chk_email(str)
{
 if (trim(str)=='')
  return false;
 var user_domain=new Array();
 if ((index=str.indexOf("@"))==-1)
  return false;
 else
 {
   user_domain[0]=str.substring(0,index-1);
   user_domain[1]=str.substring(index+1);
   if (trim(user_domain[0])=='' || trim(user_domain[1])=='') return false;
	if (user_domain[1].indexOf('.')==-1)  return false;
 }  
  return true;
}

function chk_mails()
{
for (var i=0; i < document.all.total_mails.value; i++)
 {

  if (!chk_email(eval('document.all.email_'+i.toString()+'.value')))
	 return false;
 }
return true;

}
</script>

</head>
<?=$html_header;?>
<form name="form" method="post" action="<?php echo encode_link($_SERVER['SCRIPT_NAME'],array('filtro'=>$filtro)); ?>">
  <center>
    <table width="100%" border="0">
      <tr bgcolor="#c0c6c9">
        <td align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Total 
          - Ordenes de Compra para Enviar <?=  ": ".$ordenes->RecordCount(); ?>
          </b></font> </td>
    </tr>
  </table>
    <table border="0" cellspacing="2" cellpadding="0" width="100%">
      <tr bgcolor="#006699" align="center"> 
        <td width="70" height="13"><font color="#c0c6c9"><b>Nro Orden</b></font></td>
        <td width="291"><font color="#c0c6c9"><b>Proveedor</b></font></td>
        <td width="237"><font color="#c0c6c9"><b>Contacto</b></font></td>
        <td width="164"><font color="#c0c6c9"><b>E-mail</b></font></td>
      </tr>
<?
$j=0;
while (!$ordenes->EOF)
{
	
?>
      <tr bgcolor='#c0c6c9'"> 
        <td height="18" align="center"><font color="#006699"><b><? echo $ordenes->fields['nro_orden'] ?></b></font></td>
        <td align="center"><font color="#006699"><b><?= $ordenes->fields['nbreprov'] ?></b></font></td>
        <td align="center"><font color="#006699"><b><?= $ordenes->fields['nombre'] ?></b></font></td>
        <td align="center"><input name="email_<?=$j ?>" type="text" value="<?= $ordenes->fields['mail'] ?>" size="32"></td>
        <input type="hidden" name="array_ordenes[<?=$j ?>]" value="<?= $ordenes->fields['nro_orden'] ?>" >
		 <input type="hidden" name="reply_mails[<?=$j++ ?>]" value="<?= $ordenes->fields['reply_mail'] ?>"> 
  		</tr>
<? 
	$ordenes->MoveNext();
}
?>
        <input type="hidden" name="idorden" value="">
        <input type="hidden" name="cantidad_check" value="">
        <input type="hidden" name="total_mails" value="<?= $j ?>">
    </table>
<br>
<table align="center">
<tr> 
<td><input type="submit" name="boton2" value="Cancelar" >
          <input type="submit" name="boton2" value="Enviar mail" onclick="if (!chk_mails()) {alert('Por favor ingrese los emails correctamente');return false }" > </td>
        <td>&nbsp; </td>
</tr>
</table>

  </center>
</form>
</body>
</html>
<? 
}
?>