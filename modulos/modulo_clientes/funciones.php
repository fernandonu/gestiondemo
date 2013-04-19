<?PHP

function tabla_filtros_nombres($link){

 $abc=array("a","b","c","d","e","f","g","h","i",
            "j","k","l","m","n","ñ","o","p","q",
            "r","s","t","u","v","w","x","y","z");
$cantidad=count($abc);
echo "<table width='98%' height='80%' id='mo'>";
echo "<input type=hidden name='filtro' value=''";
    echo "<tr>";
    for($i=0;$i<$cantidad;$i++){
        $letra=$abc[$i];
       switch ($i) {
                     case 9:
                     case 18:
                     case 27:echo "</tr><tr>";
                          break;
                   default:
                  } //del switch
//echo "<a id='link_load' href=$link><td style='cursor:hand' onclick=\"document.all.filtro.value='$letra'\">$letra</td></a>\n";
echo "<td style='cursor:hand' onclick=\"document.all.filtro.value='$letra';document.all.editar.value=''; document.form.submit();\">$letra</td>";
      }//del for
   echo "</tr>";
   echo "<tr>";
    echo "<td colspan='9' style='cursor:hand' onclick=\"document.all.filtro.value='Todos'; document.all.editar.value='';document.form.submit();\"> Todos";
    echo "</td>";
   echo "</tr>";
   echo "</table>";
}  //de la funcion

function  tabla_datos_muletos(){
global $id_llamadas_tel,$nombre,$apellido,$tel1,$tel2,$direccion,$mail,$dni,$localidad,$provincia,$cp,$observaciones;
?>
   <table width=100% align="center" class="bordes">
     <tr>
      <td id=mo colspan="2">
       <b> Descripción de la Llamada</b>
      </td>
     </tr>
     <tr>
       <td>
        <table>
         <tr>	
           <td align='left'>
            <b> Nombre: &nbsp;&nbsp;&nbsp;<input type='text' name='nombre' value='<?=$nombre;?>' size=50 align='right'
                                       <? if ($id_llamadas_tel) echo "readonly"?>></b>
           </td>
         </tr>
         <tr>
         	<td align='left'>
             <b> Apellido: &nbsp;&nbsp;&nbsp;<input type='text' name='apellido' value='<?=$apellido;?>' size=50 align='right'
                                       <? if ($id_llamadas_tel) echo "readonly"?>></b>
            </td>
         </tr>
         <tr>
         	<td align='left'>
             <b> Telefono1: <input type='text' name='tel1' value='<?=$tel1;?>' size=50 align='right'
                                       <? if ($id_llamadas_tel) echo "readonly"?>></b>
            </td>
         </tr>
          <tr>
         	<td align='left'>
             <b> Telefono2: <input type='text' name='tel2' value='<?=$tel2;?>' size=50 align='right'
                                       <? if ($id_llamadas_tel) echo "readonly"?>></b>
            </td>
         </tr>
          <tr>
         	<td align='left'>
             <b> Domicilio: &nbsp;<input type='text' name='direccion' value='<?=$direccion;?>' size=50 align='right'
                                       <? if ($id_llamadas_tel) echo "readonly"?>></b>
            </td>
         </tr>
         <tr>
         	<td align='left'>
             <b> Mail: &nbsp;&nbsp;&nbsp;&nbsp;<input type='text' name='mail' value='<?=$mail;?>' size=54 align='right'
                                       <? if ($id_llamadas_tel) echo "readonly"?>></b>
            </td>
         </tr>
          <tr>
         	<td align='left'>
             <b> D.N.I.: &nbsp;<input type='text' name='dni' value='<?=$dni;?>' size=54 align='right'
                                       <? if ($id_llamadas_tel) echo "readonly"?>></b>
            </td>
         </tr>
        </table>
      </td>
      <td>
        <table>
          <tr><td valign='top'><b> Observaciones </b></td></tr>
          <tr><td><textarea cols='70' rows='5' name='observaciones' <? if ($id_llamadas_tel) echo "readonly"?>><?=$observaciones;?></textarea></td></tr>
          <tr>
         	<td align='left'>
             <b> Cod.Pos.: &nbsp;&nbsp;<input type='text' name='cp' value='<?=$cp;?>' size=50 align='right'
                                       <? if ($id_llamadas_tel) echo "readonly"?>></b>
            </td>
         </tr>
          <tr>
         	<td align='left'>
             <b> Localidad: <input type='text' name='localidad' value='<?=$localidad;?>' size=50 align='right'
                                       <? if ($id_llamadas_tel) echo "readonly"?>></b>
            </td>
         </tr>
          <tr>
         	<td align='left'>
             <b> Provincia: <input type='text' name='provincia' value='<?=$provincia;?>' size=50 align='right'
                                       <? if ($id_llamadas_tel) echo "readonly"?>></b>
            </td>
         </tr>
        </table>
      </td>
     </tr>
   </table>
     <?if ($id_llamadas_tel){?>
	 <table class="bordes" align="center" width="100%">
		 <tr align="center" id="sub_tabla">
		 	<td>	
		 		Editar Llamada
		 	</td>
		 </tr>
		 
		 <tr>
		    <td align="center">
		      <input type=button name="editar" value="Editar" onclick="editar_campos()" title="Edita Campos" style="width=130px"> &nbsp;&nbsp;
		      <input type="submit" name="guardar_editar" value="Guardar" title="Guarda Llamada" disabled style="width=130px" onclick="return control_nuevos()">&nbsp;&nbsp;
		      <input type="button" name="cancelar_editar" value="Cancelar" title="Cancela Edicion" disabled style="width=130px" onclick="document.location.reload()">&nbsp;
		      <!--<input type="submit" name="eliminar_llamada" value="Eliminar" title="Eliminar" style="width=130px" onclick="return confirm('Esta Seguro que Desea Eliminar?')">&nbsp;-->
		    </td>
		 </tr> 
	 </table>
	<?}
	if (!($id_llamadas_tel)){?>
	 <table width=100% align="center" class="bordes">
      <tr align="center">
       <td>
        <input type='submit' name='guardar' value='Guardar Nueva Llamada' onclick="return control_nuevos()"
         title="Guardar datos de una Nueva Llamada">
       </td>
      </tr>
     </table>
     <?}

}//de function  tabla_datos_muletos()
?>