<?PHP
/*
$Author: fernando $
$Revision: 1.10 $
$Date: 2006/09/01 21:22:25 $
*/
require_once("../../config.php");
//require_once("../lib/lib.php");
require_once("../ord_compra/fns.php");

cargar_calendario();

if ($_POST['guardar']=="Guardar"){
 
 $fecha     = date("Y-m-d H:i:s");
 $fecha_nue = fecha_db($_POST['fecha_nueva']);
 ($_POST["pedir_prorroga"])?$pedir_prorroga = $_POST["pedir_prorroga"]:$pedir_prorroga=0;
 
 $sql = "update subido_lic_oc set vence_oc='$fecha_nue',modificado=1,pedir_prorroga=$pedir_prorroga where id_entrega_estimada=".$_POST['id'];  
 $result = sql($sql) or fin_pagina();

 $coment = $_POST['comentarios'];
 $id_entrega_estimada = $_POST['id'];
 $sql = "insert into log_cambio_fecha (usuario,fecha,tipo,comentario,id_entrega_estimada)";
 $sql.= "values ('$_ses_user[name]','$fecha',2,'$coment',$id_entrega_estimada)";
 $result = sql($sql) or fin_pagina();
 
 
?>
<script>
 window.opener.document.form1.submit();
 window.close();
</script> 
<?
}
if ($_POST['s_pedir_prorroga']){
 
 ($_POST["pedir_prorroga"])?$pedir_prorroga = $_POST["pedir_prorroga"]:$pedir_prorroga=0;
 
 $sql = "update subido_lic_oc set pedir_prorroga=$pedir_prorroga where id_entrega_estimada=".$_POST['id'];  
 $result = sql($sql) or fin_pagina();

?>
<script>
 window.opener.document.form1.submit();
 window.close();
</script> 

<?
}
?>



<script>
function control_datos(){
 if(document.all.fecha_nueva.value==""){
  alert('Debe poner una nueva Fecha de Vencimiento');
  return false;
 }
 if(document.all.comentarios.value==""){
  alert('Debe poner un comentario de porque se cambio la Fecha de Vencimiento');
  return false;
 } 
 return true;
}
</script>
<?

 $id = $parametros['id'] or $_POST['id'];
 $sql = " select pedir_prorroga from subido_lic_oc where id_entrega_estimada = $id";
 $res = sql($sql) or fin_pagina();
 

 
 ($res->fields["pedir_prorroga"])?$checked_pedir_prorroga="checked":$checked_pedir_prorroga="";


echo $html_header;
?>
<form action='cambia_fecha_vencimiento.php' method='POST' name='cambia fecha'>
<table align="center" cellpadding="2" width="70" class="bordes" bgcolor=<?=$bgcolor_out?>>
 <tr> 
    <td id="mo" bgcolor="<?=$bgcolor3?>" align="center" > 
    Cambiar fecha de Vencimiento de Orden de Compra
    </td>
 </tr>
  <tr>
    <td align="center">
      <table width="100%" align="center">
 	     <tr>
		     <td width="70%">
		        <b>Fecha de Vencimiento Actual: <?=$parametros['fecha']?></b>
		    </td>
		    <td> 
		     <input type="checkbox" name="pedir_prorroga" value="1" <?=$checked_pedir_prorroga?>>&nbsp;<b>Prorroga</b>
		     &nbsp;
		     <input type="submit" name="s_pedir_prorroga" value="Guardar">
		    </td>
		  </tr>
		  <tr>
			<td width="44%" colspan="2">
			    <b>Ingrese nueva fecha de Vencimiento: </b>
			    <input name="fecha_nueva" type="text" id="fecha_nueva" size="10"  readonly>
			    <input name="id" type="hidden"  value="<?=$parametros['id']?>">
			    <?echo link_calendario("fecha_nueva");?> 
			    
			</td> 
		   </tr>
		</table>   
	 </td>	   
   </tr>	   
   <tr>
      <td >
      <b>Motivo del Cambio de Fecha (<font color="Red">Obligatorio</font>):</b>
      </td>
   </tr>
   <tr>
      <td>
      <textarea name="comentarios" cols="90" rows="5"></textarea>
      </td>
   </tr> 
   <tr>
      <td  align="center">
           <input name="guardar" type="submit"  value="Guardar"  onclick="return control_datos()"> 
           &nbsp;
           <input  type="button"  value="Cancelar" onclick="window.close();"  > 
       </td> 
   </tr>
</table>  
<?
echo fin_pagina();
?>
