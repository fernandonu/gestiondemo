<?

$para="gonzalo@pcpower.com.ar";
		$msg="";
     	$msg.="<font color='red'>Hola para mi</font>";
//     	$msg.="Nº de Orden $nro_orden\n";
//     	$msg.="Fecha de pago $fecha_pago\n";
//     	$msg.="Monto pagado $simbolo_moneda $importe\n";
$boundary = strtoupper(md5(uniqid(time())));
                   
//para cada orden 
			//se asume que todos tiene la misma cantidad de entradas
	     $mailtext="Este es un mail enviado automaticamente por orden de compra";
	     $mail_header="";
	     $mail_header .= "MIME-Version: 1.0"; 
		  $mail_header .= "\nfrom: Sistema Inteligente de CORADIR <".$clave_valor3[1].">";
		  $mail_header .="\nTo: gonzalo@pcpower.com.ar";
//		  $mail_header .="\nBcc: ordenesdecompraenviadas@coradir.com.ar";
//		  $mail_header .= "\nReply-To: ".$clave_valor3[1];
		  $mail_header .= "\nContent-Type: multipart/mixed; boundary=$boundary"; 
//		  $mail_header .= "\n\nThis is a multi-part message in MIME format "; 
		  // Mail-Text 
		  $mail_header .= "\n--$boundary"; 
		  $mail_header .= "\nContent-Type: text/plain"; 
		  $mail_header .= "\nContent-Transfer-Encoding: 8bit"; 
		  $mail_header .= "\n\n" . $mailtext."\n"; 
		  // Your File  
		  $mail_header .= "\n--$boundary"; 
		  $mail_header .= "\nContent-Type: text/html;"; 
        $mail_header .= "\n\n <html><font color=red>HOLA ESTE TEXTO DEBE SER ROJO </font></html>"; 
	      // End 
	      $mail_header .= "\n--$boundary--"; 

	mail("","Orden Compra PAGADA","",$mail_header)			;
	      echo "LISTO";
?>