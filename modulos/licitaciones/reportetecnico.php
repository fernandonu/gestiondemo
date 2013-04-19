<?php
/*
$Author: mari $
$Revision: 1.13 $
$Date: 2006/08/30 13:49:24 $
*/
require_once("../../config.php");
// variables de post o parametros
$id_lic=$parametros["id_lic"] or $id_lic=$_POST["id_lic"];
$cliente=$parametros["cliente"] or $cliente=$_POST["cliente"];
$monto_factura=$parametros["monto_factura"] or $monto_factura=$_POST["monto_factura"];
$nro_factura=$parametros["nro_factura"] or $nro_factura=$_POST["nro_factura"];
echo $html_header;

if ($_POST["guardar"] and ($_POST["reporte"])) {
	$db->Starttrans();
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$rta_consulta=sql("select u1.nombre||' '||u1.apellido||' ('||u1.login||')' as lider, 
			u2.nombre||' '||u2.apellido||' ('||u2.login||')' as responsable
		from licitaciones.licitacion l
			left join sistema.usuarios u1 on (lider=id_usuario)
			left join licitaciones_datos_adicionales.responsables_apertura ra on (l.id_responsable_apertura=ra.id_responsable_apertura)
			left join sistema.usuarios u2 on (ra.id_usuario=u2.id_usuario)
		where id_licitacion=$id_lic", "No se pudieron obtener los datos del líder y/o responsable de apertura") or fin_pagina();
	if ($rta_consulta->recordcount()>0){
		$responsable=$rta_consulta->fields["responsable"];
		$lider=$rta_consulta->fields["lider"];
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$reporte=$_POST["reporte"];
	$fecha=date("Y-m-d H:i:s");
	$estado="Pendiente";
	$sql="insert into reporte_tecnico (id_licitacion,fecha,reporte,estado,id_usuario) Values "
		."($id_lic,'$fecha','$reporte','$estado',".$_ses_user["id"].")";
	sql($sql);
    $contenido="
    <table width=100%  bgcolor=$bgcolor2 border=1 cellpading=0 cellspacing=0>
      <tr><td align=center><font color=red size=3><b>NOTA: ESTO SIGNIFICA QUE EL COBRO ESTA PARADO</b></font></td></tr>
      <tr><td>&nbsp;</td></tr>
      <tr><td align=center><b><b>Se reportó un problema técnico en la Lic:$id_lic</b></td></tr>
      <tr><td align=left><b><b><font size=2>Datos Extras</font></b></td></tr>
      <tr>
         <td>
            <table width=100% align=center>
              <tr>
                <td width=20%  valign=top><b>Cliente:</b></td>
                <td>$cliente</td>
              </tr>
             <tr>
               <td width=20%  valign=top><b>Lider:</b></td>
               <td>$lider</td>
             </tr> 
             <tr>
               <td width=20%  valign=top><b>Responsable:</b></td>
               <td>$responsable</td>
             </tr>              
             <tr>
               <td width=20%  valign=top><b>Factura Nro:</b></td>
               <td>$nro_factura</td>
             </tr>              
             <tr>
               <td width=20%  valign=top><b>Monto:</b></td>
               <td>$monto_factura</td>
             </tr>              
             <tr>
               <td width=20%  valign=top><b>Problema Reportado:</b></td>
               <td align=left valign=top>$reporte</td>
             </tr>              
             
            </table>
         </td>
      </tr>
    </table>
    ";
    $para="serviciotecnico@coradir.com.ar";
    $asunto=" Atención!!!!!  Problema en reporte técnico en Lic:$id_lic";
    enviar_mail_html($para,$asunto,$contenido,0,0,0);
     ?>
	<script>
	window.opener.location.reload();
	window.close();
	</script>
    <?
    $db->CompleteTrans();
    }
    ?>
<form action="reportetecnico.php" name="reporte" method="POST">
	<input type=hidden name=id_lic value='<? echo $id_lic; ?>'>
	<input type=hidden name=cliente value='<? echo $cliente; ?>'>
	<input type="hidden" name="monto_factura" value="<?=$monto_factura?>">
	<input type="hidden" name="nro_factura" value="<?=$nro_factura?>">
	<input type="hidden" name="responsable" value="<?=$responsable?>">
	<input type="hidden" name="lider" value="<?=$lider?>">
	<table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=<? echo $bgcolor2; ?> align=center>
	<tr>
		<td colspan=2 style="border:<? echo $bgcolor3; ?>;" colspan=2 align=center id=mo>
			<font size=+1>Reportar Problemas Técnicos</font>
		</td>
	</tr>
	<tr>
		<td>
			ID. de licitacion: <b><? echo $id_lic; ?></b>
		</td>
		<td align=right>
			Cliente: <b><? echo $cliente; ?></b>
		</td>
	</tr>
	<tr>
		<td colspan=2>
			<b>Reporte:</b><br>
			<textarea name=reporte cols=110 rows=20><? echo $reporte; ?></textarea>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2 style="border:<? echo $bgcolor2; ?>">
			<input type=submit name=guardar style="width: 150;" value="Reportar Problema">
		</td>
	</table>
</form>
