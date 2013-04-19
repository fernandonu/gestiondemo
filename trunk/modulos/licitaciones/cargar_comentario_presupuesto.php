<?
require_once("../../config.php");
/*
$Author: gonzalo $
$Revision: 1.3 $
$Date: 2004/10/20 22:41:18 $
*/

echo $html_header;
?>
<script language="javascript">
   tabla=eval("window.opener.tabla");
   fila=eval("window.opener.fila");
   columna=eval("window.opener.columna");
   comentario=eval("window.opener.document.all.hidden_comentario_"+tabla+"_"+fila+"_"+columna)
   
function control_boton(valor)
{
 if(cargar!="no")
 {if (window.event.keyCode==13)
  {
  hidden_com=eval('window.opener.document.all.hidden_comentario_'+tabla+'_'+fila+'_'+columna);
  hidden_com.value=document.all.comentario_ventana.value;
  col=eval('window.opener.document.all.columna_'+tabla+'_'+fila+'_'+columna);
  col.title=document.all.comentario_ventana.value;
  window.close();
 }
 
 if (window.event.keyCode==27)
  window.close(); 
 }
}

</script>
<body onkeypress="control_boton();">
<table align="center" cellpadding="2" class="bordes" >
 <tr> 
  <td id="mo" bgcolor="<?=$bgcolor3?>" align="center" colspan="2"> 
   Comentario para proveedor: <font size="2" color="white"><script>
   var prov=eval("window.opener.document.all.hnombreprov_"+columna);
   document.write(prov.value);
   </SCRIPT>
  </td>
 </tr>
 <tr bgcolor=<?=$bgcolor_out?>>
  <td>
   <b>Producto: <font color="Blue" size="2"><SCRIPT> 
   var prod=eval("window.opener.document.all.nbreproducto_"+tabla+"_"+(fila));
   document.write(prod.value);
   </SCRIPT>
   <table>
    <tr>
     <td colspan="2">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td width="44%">
     &nbsp;
     </td>
     <td align="left">
       &nbsp;
     </td> 
    </tr>
    <tr>
     <td colspan="2">
      <b>Ingrese nuevo comentario:</b>
     </td>
    </tr>
    <tr>
     <td colspan="2">
      <textarea name="comentario_ventana" id="comentario_ventana" cols="90" rows="5" onkeypress="cargar='no';" onblur="cargar='si';"></textarea>
      <script>document.all.comentario_ventana.value=comentario.value;
      document.all.comentario_ventana.focus();
      </script>
     </td>
    </tr> 
    <tr>
     <td colspan="2" align="center">
      &nbsp;
      <table width="50%">
       <tr>
        <td width="50%" align="center">
         <input name="guardar" type="button" value="Guardar" onclick="hidden_com=eval('window.opener.document.all.hidden_comentario_'+tabla+'_'+fila+'_'+columna);
   																	  hidden_com.value=document.all.comentario_ventana.value;
																      col=eval('window.opener.document.all.columna_'+tabla+'_'+fila+'_'+columna);
																      col.title=document.all.comentario_ventana.value;
																      window.close();" style="cursor:hand">
        </td>
        <td width="50%" align="center">
         <input  type="button"  value="Cancelar" onclick="window.close();" style="cursor:hand"> 
        </td>
       </tr> 
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr> 
</table>