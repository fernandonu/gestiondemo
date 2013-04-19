<?php

require_once("../../config.php");


//envio los mail
if ($_POST['boton2']=="Enviar mail")
{
$boundary = strtoupper(md5(uniqid(time())));
$filepath="./pdf/";
$i=1;

//para cada orden
while ($nro_orden=each($_POST['array_ordenes']))
{
         $sql_renglon="select codigo_renglon,id_renglon,renglon.id_licitacion from
                             orden_de_produccion join
                             renglon using(id_renglon)
                             where nro_orden=".$nro_orden[1];
         $renglon=sql($sql_renglon) or fin_pagina();
         $hay_doc=0;
         $id_licitacion=$renglon->fields["id_licitacion"];
         $file_path_doc=UPLOADS_DIR."/Licitaciones/$id_licitacion/";
         $codigo=$renglon->fields["codigo_renglon"];
         if ($renglon->recordcount()){
                  $codigo=ereg_replace(" ","_",$codigo);
                  $nombre_doc="Desc_lic_$id_licitacion";
                  $nombre_doc.="_renglon_";
                  $nombre_doc.=$codigo.".zip";
                  $hay_doc=1;
                  }

         $mail=each($_POST['email']);//recupero el email

	     $filename="ordendeproduccion_".$nro_orden[1].".pdf";

         $mailtext="Este es un mail enviado automaticamente por orden de produccion";
	     $mail_header="";
	     $mail_header .= "MIME-Version: 1.0";
		 $mail_header .= "\nFrom: Sistema Inteligente de CORADIR <>";
		 $mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
         $mail_header .="\nTo: ".$mail[1];
		 $mail_header .="\nBcc: ordenesdeproduccionenviadas@coradir.com.ar";
		 //$mail_header .= "\nReply-To: ".$clave_valor3[1];
		 $mail_header .= "\nContent-Type: multipart/mixed; boundary=$boundary";
		 $mail_header .= "\n\nThis is a multi-part message in MIME format ";
		 // Mail-Text
		 $mail_header .= "\n--$boundary";
		 $mail_header .= "\nContent-Type: text/plain";
		 $mail_header .= "\nContent-Transfer-Encoding: 8bit";
		 $mail_header .= "\n\n" . $mailtext."\n";
		 // Your File
         /*
		 $mail_header .= "\n--$boundary";
         
		 $mail_header .= "\nContent-Type: application/pdf; name=\"$filename\"";
	     // Read from Array $contenttypes the right MIME-Typ
	     $mail_header .= "\nContent-Transfer-Encoding: base64";
	     $mail_header .= "\n\n".$archivo=chunk_split(base64_encode(fread(fopen($filepath.$filename, "r"), filesize($filepath.$filename))));
          */
        if ($hay_doc && file_exists($file_path_doc.$nombre_doc)){
      //   if ($hay_doc){
                      $mail_header .= "\n--$boundary";
                     $mail_header .= "\nContent-Type: application/zip; name=\"$nombre_doc\"";
                      $mail_header .= "\nContent-Transfer-Encoding: base64";
                     $mail_header .= "\n\n".$archivo=chunk_split(base64_encode(fread(fopen($file_path_doc.$nombre_doc, "r"), filesize($file_path_doc.$nombre_doc))));

                      }

       // die($file_path_doc.$nombre_doc);
          $mail_header .= "\n--$boundary";
		 $mail_header .= "\nContent-Type: text/plain";
		 $mail_header .= "\nContent-Transfer-Encoding: 8bit";
		 $mail_header .= "\n\n" . firma_coradir()."\n";
	     // End
	     $mail_header .= "\n--$boundary--";
	     if (mail("","Orden de Producción Autorizada Nro: ".$nro_orden[1],"",$mail_header))
	     {
			$sql[]="update orden_de_produccion set estado='E' where nro_orden=".$nro_orden[1];
			$sql[]="INSERT INTO log_ord_prod (nro_orden,fecha,descripcion,id_usuario) "
				."VALUES (".$nro_orden[1].",'".date("Y-m-d H:i:s")."','Enviada',".$_ses_user["id"].")";
			sql($sql) or fin_pagina();
	     }
      }
$link="ordenes_ver.php?est=1";
header("location: $link");
die;
}
echo $html_header;
?>
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

var contador=0;

function habilitar_mail(valor)
{if (valor.checked)
  contador++;
 else
  contador--;
 if (contador>=1)
  window.document.all.boton[1].disabled=0;
 else
  window.document.all.boton[1].disabled=1;
}//fin function
</script>
<?
$ordenes="";
$i=0;
while ($i<=$_POST['cant_check']) {
   if($_POST['check'][$i]!="")
        $ordenes.=$_POST['check'][$i].",";
   $i++;
}

$ordenes=substr($ordenes,0,strlen($ordenes)-1); //quita la ultima coma

$sql="select orden_de_produccion.nro_orden,ensamblador.nombre as nombre_ens,entidad.nombre as nombre_cliente,ensamblador.email 
       from (
             (orden_de_produccion join ensamblador on orden_de_produccion.id_ensamblador=ensamblador.id_ensamblador) 
             join entidad USING (id_entidad)) 
             where orden_de_produccion.nro_orden in ($ordenes)";
$ordenes=sql($sql) or fin_pagina();


$sql = "select * from ordenes_mail";
$res = sql($sql) or fin_pagina();
for($i=0;$i<$res->recordcount();$i++){
	$mail.= ($i == $res->recordcount() - 1)?$res->fields["mail"]:$res->fields["mail"].",";	
	$res->movenext();
}//del for

?>  
<form name="form" method="post" action="ordenes_mail.php">
  <center>
    <table width="100%" border="0">
      <tr bgcolor="#c0c6c9">
        <td align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Total 
          - Ordenes de Produccion para Enviar <?=  ": ".$ordenes->RecordCount(); ?>
          </b></font> 
          <?
          $link_mail = encode_link("mail_ordenes.php",array());
          ?>
          <input type="button" name="mail_ordenes" value="E-Mail" onclick="window.open('<?=$link_mail?>')" title="Haga click si desea agregar, eliminar o modificar una direccion de mail de la lista">          
        </td>
    </tr>
  </table>
    <table class="bordes" width="100%">
      <tr id=mo> 
        <td width="10%"0"><b>Nro Orden</b></td>
        <td width="40%"><b>Cliente</b></td>
        <td width="20%"><b>Ensamblador</b></td>
        <td ><b>E-mail</b></td>
      </tr>
<?
$j=0;
while (!$ordenes->EOF) {	
?>
      <tr <?=atrib_tr()?>> 
        <td align="center"><?=$ordenes->fields['nro_orden']       ?></td>
        <td align="left"> <?=$ordenes->fields['nombre_cliente']  ?></td>
        <td align="left"><?= $ordenes->fields['nombre_ens']     ?></td>
        <td align="center"><input name="email[<?=$j?>]"  style="width:95%" type="text" value="<?=$mail?>" size="32"></td>
        <input type="hidden" name="array_ordenes[<?=$j ?>]" value="<?=$ordenes->fields['nro_orden']?>" >
	 </tr>
<? 
    $j++;
	$ordenes->MoveNext();
}
?>
</table>
<br>
<table align="center">
<tr> 
<td><input type="button" name="boton" value="Cancelar" onclick="history.go(-1);">
    <input type="submit" name="boton2" value="Enviar mail"> </td>
<td>&nbsp; </td>
</tr>
</table>
</center>
</form>
<?=fin_pagina();?>