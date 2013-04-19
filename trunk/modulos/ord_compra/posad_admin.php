<?
/*
Author: ferni

modificada por
$Author: ferni $
$Revision: 1.34 $
$Date: 2005/12/02 15:52:50 $
*/

require_once ("../../config.php");

extract($_POST,EXTR_SKIP);
if ($parametros) extract($parametros,EXTR_OVERWRITE);

if ($_POST['guardar_posad']=="Guardar"){
	$id_posad=$_POST['id_posad'];
	//hace la actualizacion
	$sql="update compras.posad set descripcion='$descripcion', codigo_ncm='$codigo_ncm', derechos=$derechos, estadistica=$estadistica, iva_ganancias=$iva_ganancias where id_posad=$id_posad";
	sql($sql) or fin_pagina();
	//mensaje y direcciona a la pagina padre
	$accion="Se Actualizo el Poasd Numero $id_posad";
	$link=encode_link('listado_posad.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}

if ($_POST['guardar_posad']=="Guardar Nuevo"){
	//inserta
	$sql="insert into compras.posad 
		  (descripcion, codigo_ncm, derechos, estadistica, iva_ganancias,estado_posad)
		  values
		  ('$descripcion', '$codigo_ncm', $derechos, $estadistica, $iva_ganancias, 1) ";
	
	sql($sql) or fin_pagina();
	//mensaje y direcciona a la pagina padre
	$accion="Se dio de Alta un Posad Nuevo";
	$link=encode_link('listado_posad.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}

if ($_POST['eliminar_posad']=="Eliminar"){
	//da baja logica
	$id_posad=$_POST['id_posad'];
	$sql="update compras.posad set estado_posad=0 where id_posad=$id_posad";
	sql($sql) or fin_pagina();
	//mensaje y direcciona a la pagina padre
	$accion="Se Elimino el Posad Número $id_posad";
	$link=encode_link('listado_posad.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}

if ($id_posad) {
$sql="select * from posad where ((estado_posad <> 0) and (id_posad=$id_posad)) ";
$res_posad=sql($sql, "Error al traer los datos del Posad") or fin_pagina();

$descripcion=$res_posad->fields['descripcion'];
$codigo_ncm=$res_posad->fields['codigo_ncm'];
$derechos=$res_posad->fields['derechos'];
$estadistica=$res_posad->fields['estadistica'];
$iva_ganancias=$res_posad->fields['iva_ganancias'];
}

echo $html_header;

?>
<script>
//controlan que ingresen todos los datos necesarios par el muleto
function control_nuevos()
{
 if(document.all.descripcion.value=="")
 {alert('Debe ingresar una Descripción');
  return false;
 }
 if(document.all.codigo_ncm.value=="")
 {alert('Debe ingresar un Código');
  return false;
 }
 if(document.all.derechos.value=="")
 {alert('Debe ingresar un Derecho');
  return false;
 }
 if(document.all.estadistica.value=="")
 {alert('Debe ingresar una Estadistica');
  return false;
 }
 if(document.all.iva_ganancias.value=="")
 {alert('Debe ingresar un IVA Ganancia');
  return false;
 }

 return true;
}//de function control_nuevos()

</script>

<form name='form1' action='posad_admin.php' method='POST'>
<br>

<input type="hidden" name="id_posad" value="<?=$id_posad?>">

<table width=60% border=0 cellspacing=0 cellpadding=6 bgcolor=<?=$bgcolor2?> align="center" class="bordes">
 <tr>
    <td style="border:<?=$bgcolor3?>" align=center id=mo>
    <?
    if (!$id_posad) {
    ?>
     <font size=+1><b> Nuevo Posad</b></font>
    <? }
        else {
    ?>
      <font size=+1><b>Posad</b></font>
    <? } ?>
    </td>
 </tr>
<br>
 <tr>
 	<td>
 	  <table width=60% align="center" class="bordes">
     	<tr>
          <td align="center" colspan="2">
           <b> Nro. Posad <font color="Red"><?if ($id_posad){echo $id_posad;} else {echo "Nuevo";}?></font> </b>
          </td>
     	</tr>
     	
     	<tr>
          <td  colspan="2" align="left">
            <br>
            <b> Descripción </b>
          </td>
        </tr>
        <tr>
          <td colspan="2" align="left">
            <input type='text' name='descripcion' value='<?=$descripcion?>' size=50 >
          </td>
        </tr>
          
        <tr>
           <td  colspan="2">
            <b> Código NCM </b>
           </td>
          </tr>
          <tr>
           <td  colspan="2">
             <input type='text' name='codigo_ncm' value='<?=$codigo_ncm?>' size=50 >
           </td>
        </tr>
        
        <tr>
           <td colspan="2">
            <b> Derechos </b>
           </td>
          </tr>
          <tr>
           <td  colspan="2">
            <input type='text' name='derechos' value='<?=$derechos?>' size=50>
           </td>
        </tr>
        
        <tr>
           <td colspan="2">
            <b> Estadistica </b>
           </td>
          </tr>
          <tr>
           <td  colspan="2">
            <input type='text' name='estadistica' value='<?=$estadistica?>' size=50>
           </td>
        </tr>
        
        <tr>
           <td colspan="2">
            <b> Iva Ganancias </b>
           </td>
          </tr>
          <tr>
           <td  colspan="2">
            <input type='text' name='iva_ganancias' value='<?=$iva_ganancias?>' size=50>
           </td>
        </tr>
        
     </table>
   </td>
 </tr>	
</table>

 <tr align="center">
    <td align="center"><br>
      <center>
      	<?if ($id_posad){?>
	        <input type='submit' name='guardar_posad' value='Guardar' onclick="return control_nuevos()" title="Guardar Posad" style="width=150px"> &nbsp;&nbsp;
	     	<!--<input type='submit' name='eliminar_posad' value='Eliminar' onclick="return confirm('Esta seguro que desea eliminar el Posad');" title="Eliminar Posad" style="width=150px"> &nbsp;&nbsp; -->
	    <?}
	    else{?>
	    	<input type='submit' name='guardar_posad' value='Guardar Nuevo' onclick="return control_nuevos()" title="Guardar Nuevo Posad" style="width=150px"> &nbsp;&nbsp;
	    <?}?>
      	<input type=button name="volver" value="Volver" onclick="document.location='listado_posad.php'" title="Volver al Listado de Posad" style="width=150px">
      </center>
    </td>
 </tr> 
 
</form>
<?=fin_pagina();// aca termino ?>
