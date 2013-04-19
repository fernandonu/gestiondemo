<?
/*
Author: Fernando

MODIFICADA POR
$Author: fernando $
$Revision: 1.1 $
$Date: 2007/03/06 21:19:19 $
*/
require_once("../../config.php");



$items  = $_POST["items"]  or $items  = $parametros["items"];
$accion = $_POST["accion"] or $accion = $parametros["accion"];
$id     = $_POST["id"]     or $id     = $parametros["id"];




if ($_POST["guardar"]){
	     $tabla = ($items == "Sistema Operativo")?"sistema_operativo_rp":"procesador_rp";
         $nuevo = $_POST["nuevo"];	     
         if ($accion == "Nuevo") {
             $sql = "insert into $tabla (descripcion) values ('$nuevo')";
             sql($sql) or fin_pagina();        	   
         }elseif($accion == "Modificar"){
             $condicion = ($items == "Sistema Operativo")?"id_sistema_operativo_rp=$id":"id_procesador_rp=$id";        	  
             $sql = "update $tabla set descripcion = '$nuevo' where $condicion";	
             sql($sql) or fin_pagina();             
         }
         ?>
		<script>
		 window.opener.document.form1.submit();
		 window.close();
		</script> 
         <?
	
}//del if



echo $html_header
?>
<form name="form1" method="POST" action="<?=$PHP_SELF?>">
<input type="hidden" name="items"  value="<?=$items?>">
<input type="hidden" name="accion" value="<?=$accion?>">
<input type="hidden" name="id"     value="<?=$id?>">

 <table align="center" cellpadding="2" width="50%" class="bordes" bgcolor=<?=$bgcolor_out?>>
   <tr id=mo><td>Agregar Items</td></tr>
   <tr>
     <td width="100%" align="center">
        <table width="100%" align="center">
          <tr>
            <td width="30%"><b><?="$accion $items:";?></b></td>
            <td align="left"><input type="text" name="nuevo" value="<?=$nuevo?>" size="50"></td>                     
          </tr>
        </table>
     </td>

   </tr>
   
   <tr>
     <td align="center">
       <input type="submit" name="guardar" value="Guardar">
       &nbsp;
       <input type="button" name="Cancelar" value="Cancelar" onclick="window.close()">
     </td>
   </tr>
   
 </table>
</form>
<?
echo fin_pagina();
?>
