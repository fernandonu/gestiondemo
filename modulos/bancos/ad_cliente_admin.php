<?
/*
Author: ferni

modificada por
$Author: ferni $
$Revision: 1.1 $
$Date: 2007/07/02 18:48:54 $
*/

require_once ("../../config.php");

$id_adelantos_clientes=$parametros['id_adelantos_clientes'];

if ($_POST['a_historial']=="A Historial"){
	$id_adelantos_clientes=$_POST['id_adelantos_clientes'];
	$db->StartTrans();
	//actualizo la tabal muletos
	$sql="update bancos.adelantos_clientes set estado=2 where id_adelantos_clientes=$id_adelantos_clientes";
	sql($sql) or fin_pagina();
	$db->CompleteTrans();
    //redirecciono
	$accion="Se Cambio el Estado del Adelanto $id_adelantos_clientes a Historial";
    $link=encode_link('ad_cliente_lis.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}

if ($_POST['guardar']=="Guardar"){   
   $db->StartTrans();
   $id_cliente=$_POST['cliente'];
   $comentario=$_POST['comentario'];
   $monto=$_POST['monto'];


    $q="select nextval('adelantos_clientes_id_adelantos_clientes_seq') as id_adelantos_clientes";
    $id_adelantos_clientes=sql($q) or fin_pagina();
    $id_adelantos_clientes=$id_adelantos_clientes->fields['id_adelantos_clientes'];

    $id_estado=1;
     
    $query="insert into bancos.adelantos_clientes
             (id_adelantos_clientes,id_entidad,monto,comentario,estado)
             values
             ($id_adelantos_clientes, $id_cliente, $monto, '$comentario', $id_estado)";

    sql($query, "Error al insertar/actualizar el adelanto") or fin_pagina();
    
    $accion="Los datos del Adelanto $id_adelantos_clientes se guardaron con Exito";
	    	 
    $db->CompleteTrans();

    $link=encode_link('ad_cliente_lis.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}//de if ($_POST['guardar']=="Guardar nuevo Muleto")

if ($id_adelantos_clientes!='') {
$sql="select id_adelantos_clientes,id_entidad,monto,comentario,estado,nombre
 		from bancos.adelantos_clientes
 		left join licitaciones.entidad using (id_entidad)
 		where id_adelantos_clientes=$id_adelantos_clientes";
$res=sql($sql, "Error al traer los datos del caso") or fin_pagina();

$cliente=$res->fields['nombre'];
$id_entidad=$res->fields['id_entidad'];
$comentario=$res->fields['comentario'];
$monto=$res->fields['monto'];
$estado=$res->fields['estado'];
}
echo $html_header;
?>
<script>
//controlan que ingresen todos los datos necesarios par el muleto
function control_nuevos()
{
 if(document.all.cliente.value==""){
  alert('Debe ingresar un cliente');
  return false;
 }
 if(document.all.monto.value==""){
  alert('Debe ingresar un Monto');
  return false;
 }
 if(document.all.comentario.value==""){
  alert('Debe ingresar un comentario');
  return false;
 }
 
 return true;
}//de function control_nuevos()
</script>

<form name='form1' action='ad_cliente_admin.php' method='POST'>
<br>
<input type="hidden" name="id_adelantos_clientes" value="<?=$id_adelantos_clientes?>">
<table width="60%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
 <tr id="mo">
    <td>
    <?
    if ($id_adelantos_clientes=='') {
    ?>
     <font size=+1><b> Nuevo Adelanto</b></font>
    <? }
        else {
    ?>
      <font size=+1><b>Adelanto</b></font>
    <? } ?>
    </td>
 </tr>
 <tr><td><table width=100% align="center" class="bordes">
     <tr>
      <td id=mo colspan="2">
       <b> Descripción del Adelanto</b>
      </td>
     </tr>
     <tr>
       <td>
        <table>
         <tr>	
           <td align='left'>
            <b> Nro. Adelanto: <font color="Red"><?=($id_adelantos_clientes!='')?$id_adelantos_clientes:"nuevo"?></font> </b>
           </td>
         </tr>
         
         <tr>
         <td align='left'>
         <b> Cliente:</b>
         <?$sql="select id_entidad,nombre from licitaciones.entidad order by nombre";
         $contactos=sql($sql,'no se puede traer las entidades')
         ?>
         <select name="cliente" style="width:500px" onKeypress= "buscar_op(this)" onblur="borrar_buffer()" onclick= "borrar_buffer()" <? if ($id_adelantos_clientes!='') echo "disabled"?>>
          <option value="-1">Seleccione el cliente</option>
          <?while (!$contactos->EOF){?>
	      	<option value="<?=$contactos->fields['id_entidad']?>" <? if ($contactos->fields['id_entidad']==$id_entidad) echo " selected "; ?>><?=$contactos->fields['nombre']?></option>
                 <?
				 $contactos->MoveNext();
				}//de while (!$contactos->EOF)
			?>
            
		 </select>
		 </td>
		 </tr>         
         <tr>
           <td  colspan="2">
            <b> Monto: </b>
            &nbsp;&nbsp;<input type='text' name='monto' value='<?=number_format($monto,2,'.','');?>' size=50
                   <? if ($id_adelantos_clientes!='') echo "readonly"?>>
           </td>
          </tr>
          <tr><td colspan="2"><b> Comentario: </b></td></tr> 
          <tr><td><textarea cols='70' rows='7' name='comentario' <? if ($id_adelantos_clientes!='') echo "readonly"?>><?=$comentario;?></textarea></td></tr>         
        </table>
      </td>      
     </tr>
   </table>
    <?if ($id_adelantos_clientes==''){?>
	 <table width=100% align="center" class="bordes">
      <tr align="center">
       <td>
        <input type='submit' name='guardar' value='Guardar' onclick="return control_nuevos()"
         title="Guardar datos de un Nuevo Adelanto">
       </td>
      </tr>
     </table></td></tr>
     <?}?>
 
<!--Cambios de Estado-->
<tr><td><table width=100% align="center" class="bordes">
 <?if ($estado==1){?>
    <tr id="mo">
   		<td align=center colspan="2">
   			<b>Cambios de Estado</b>
   		</td>
   	</tr>   
    <tr>
    	<td width="25%" align="center" colspan="2"><br>
    	<input type="submit" value="A Historial" title="Pasa Muleto A Historial" name="a_historial" style="width=170" onclick="return confirm ('Esta Seguro que Desea Cambiar el \n Estado del Adelanto A Historial')">
    	</td>
    </tr>
 
 <?}?>
  
</table></td></tr>
<!--Fin de Cambios de Estado-->

 <tr><td><table width=100% align="center" class="bordes">
  <tr align="center">
   <td>
     <input type=button name="volver" value="Volver" onclick="document.location='ad_cliente_lis.php'"title="Volver al Listado de Muletos" style="width=150px">     
   </td>
  </tr>
 </table></td></tr>
 
</table> 
</form>
<?=fin_pagina();// aca termino ?>
