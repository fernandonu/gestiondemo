<?
/*
Author: Quique

MODIFICADA POR
$Author: enrique $
$Revision: 1.3 $
$Date: 2005/11/07 14:27:46 $
*/

include_once("../../config.php");
//extrae las variables de POST
$id_com=$parametros["id_competidor"];

extract($_POST,EXTR_SKIP);
 if($boton=="Finalizar Seguimiento")
	$do=1;

    
if ($do==1)
{
 $id_com=$_POST['id_c'];
 $query="update competidores set competidor_activo=0 where id_competidor=$id_com";
 if ($db->Execute($query))
	 $informar="<center><b>El competidor \"$nbre\" fue actualizado con exito</b></center>";	
 else 
	 $informar="<center><b>El competidor \"$nbre\" no se pudo actualizar</b></center>";	
	 
 
}
?>
<!--
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
-->
<?echo $html_header; ?>
<style type="text/css">
<!--
.tablaEnc {
	background-color: #006699;
	color: #c0c6c9;
	font-weight: bold;
}
-->
</style>
<?
include("../ayuda/ayudas.php");
?>
</head>
<body bgcolor=#E0E0E0 leftmargin=4>
<form name="form" method="post" action="detalle_competidores.php">
<div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_competidor.htm" ?>', 'CARGAR COMPETIDOR')" >
    </div>
<br>
<input type="hidden" name="editar" value="">
<?
if($id_com!="")
{
$quer="select * from competidores  where id_competidor=$id_com";	
$res_q=sql($quer,"No se recuperaron los datos del envio") or fin_pagina();
$nomb=$res_q->fields["nombre"];	
$tel_co=$res_q->fields["tel"];	
$dire=$res_q->fields["direccion"];	
$ob=$res_q->fields["observaciones"];	
$cui=$res_q->fields["cuit"];	
$nom_con=$res_q->fields["nombre_contacto"];	
$te_con=$res_q->fields["tel_contacto"];	
$mai=$res_q->fields["mail"];
$fc=Fecha($res_q->fields["fecha_certificado"]);
$fc1=$res_q->fields["fecha_certificado"];
$query="select * from  log_certificados_competidores where id_competidor=$id_com";	
$res_qu=sql($query,"No se recuperaron los datos del envio") or fin_pagina();	
}


?>
  <table width="100%" align="center" border=0 cellspacing="2" cellpadding="0">
    <tr><td width="50%" valign="top">
        <table align="center" class="bordes">
            <tr>
            	<input type="hidden" name="id_c" value="<?=$id_com?>">
                <td  align="center" id="mo">INFORMACION DEL COMPETIDOR</td>
            </tr>
            <tr>
                <td>
                        <table width="99%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor=<?=$bgcolor_out?>>
                                <tr >
                                        <td width="20%" nowrap><strong>Nombre</strong></td>
                                        <td width="70%" nowrap> <?=$nomb?></td>
                                </tr>
                                <tr>
                                        <td  nowrap><strong>Teléfono</strong></td>
                                        <td nowrap> <?=$tel_co?></td>
                                </tr>
                                <tr>
                                        <td  nowrap> <strong>Dirección</strong></td>
                                        <td nowrap> <?=$dire?></td>
                                </tr>
                                <tr>
                                        <td  nowrap><strong>E-mail</strong></td>
                                        <td nowrap> <?=$mai?></td>
                                </tr>
                                <tr>
                                        <td  nowrap><strong>Cuit</strong></td>
                                        <td nowrap> <?=$cui?></td>
                                </tr>
                                <tr>
                                        <td  nowrap><strong>Nombre del Contacto</strong></td>
                                        <td nowrap> <?=$nom_con?></td>
                                </tr>
                                <tr>
                                        <td  nowrap><strong>Teléfono del Contacto</strong></td>
                                        <td nowrap> <?=$te_con?></td>
                                </tr>
                                <tr>
                                        <td   nowrap><strong>Observaciones</strong></td>
                                
                                        <td  nowrap> 
                                                <?echo"$ob";?></td>
                                </tr>
                                <tr>
                                        <td   nowrap colspan="2"><strong>Fecha Certificado</strong></td>
                                </tr>
                                <tr>
                                        <td  nowrap align="center" colspan="2" ><strong> 
                                          <font color="Blue" size="3">      <?echo"$fc";?> </font></strong></td>
                                </tr>
                                
                                
                                <tr> 
								  <td> 
								  <div id='div_com1' style='border-width: 0;overflow: hidden;height: 1'>                   
								  <table width="100%">
								  
								 </table>
								  </td>
								 </tr>
								 
								   <tr>
								   <td colspan="2">
								    <table width="100%" >
								    <tr>
								    <td width="100%">
								    <img src='../../imagenes/drop2.gif' border=0 style='cursor: hand;' 
									onClick='if (this.src.indexOf("drop2.gif")!=-1) 
								    {
									this.src="../../imagenes/dropdown2.gif";
									div_com.style.overflow="visible";
									} 
									else 
									{
									this.src="../../imagenes/drop2.gif";
									div_com.style.overflow="hidden";
									}'>&nbsp;&nbsp;&nbsp; <strong> Historial fechas certificados </strong>
								    </td>
								   
								    </tr>
								    </table>
								   </td>
								  </tr>
								   <tr>
								   <td colspan="2">      
								   <hr>
								   </td>
								   </tr>
								  <tr> 
								  <td colspan="2" align="center">
								  <div id='div_com' style='border-width: 0;overflow: hidden;height: 1'>                    
								  <table>
								                                 
                                 <?
                                 while(!$res_qu->EOF){
                                 if(compara_fechas($fc1,$res_qu->fields['fecha_certificado'])!=0)
                                 {	
                                 ?>
                                        <tr>
                                        <td align="center"><b> <?=Fecha($res_qu->fields['fecha_certificado'])?></b></td>
                                        </tr>
                                 <?
                                 }
                                 $res_qu->MoveNext();
                                 }
                                 ?>          
                                     
                        </table>
                        </td>
						</tr>
                        </table>
                </td>
            </tr>
        </table>

<br>
<TABLE align="center" cellspacing="0">
<tr><td> <input type=button name=cerrar value=Cerrar onclick="window.opener.form1.submit();window.close()">
</TD>
<td>
 <input type="submit" name="boton" value="Finalizar Seguimiento">
</td>
</tr>
</TABLE>
<INPUT type="hidden" name="competidor">
</form>
</body>
</html>