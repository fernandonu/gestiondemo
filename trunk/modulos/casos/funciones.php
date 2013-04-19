<?
/*
Author: ferni

modificada por
$Author: ferni $
$Revision: 1.5 $
$Date: 2006/04/26 20:58:44 $
*/

require_once ("../../config.php");

function  tabla_datos_muletos()
{
global $id_muleto, $nro_serie, $modelo, $marca, $observaciones, $precio_stock, $estado, $boton;
?>
   <table width=100% align="center" class="bordes">
     <tr>
      <td id=mo colspan="2">
       <b> Descripción del Muleto</b>
      </td>
     </tr>
     <tr>
       <td>
        <table>
         <tr>	
           <td align='left'>
            <b> Nro. Muleto: <font color="Red"><?=($id_muleto)?$id_muleto:"nuevo"?></font> </b>
           </td>
         </tr>
         <tr>
         	<td align='left'>
             <b> Nro. Serie: <input type='text' name='nro_serie' value='<?=$nro_serie;?>' size=30 align='right'
                                       <? if ($id_muleto) echo "readonly"?>></b>
            </td>
         </tr>
         <tr>
           <td  colspan="2">
            <b> Marca: </b>
            &nbsp;&nbsp;<input type='text' name='marca' value='<?=$marca;?>' size=50
                   <? if ($id_muleto) echo "readonly"?>>
           </td>
          </tr>
          <tr>
           <td colspan="2">
            <b> Modelo: </b>
            <input type='text' name='modelo' value='<?=$modelo;?>' size=50
                   <? if ($id_muleto) echo "readonly"?>>
           </td>
          </tr>
          <tr>
           <td colspan="2">
            <b> Precio Stock: </b>
            <input type='text' name='precio_stock' value='<?if ($id_muleto) {echo number_format($precio_stock,2,',','.');}?>' size=44
                   <? if ($id_muleto) echo "readonly"?>>
           </td>
          </tr>
        </table>
      </td>
      <td>
        <table>
          <tr><td valign='top'><b> Observaciones </b></td></tr>
          <tr><td><textarea cols='70' rows='7' name='observaciones' <? if ($id_muleto) echo "readonly"?>><?=$observaciones;?></textarea></td></tr>
        </table>
      </td>
     </tr>
   </table>
     <?if (($id_muleto)&&(permisos_check("inicio","permiso_editar_muleto"))){?>
	 <table class="bordes" align="center" width="100%">
		 <tr align="center" id="sub_tabla">
		 	<td>	
		 		Editar Muleto
		 	</td>
		 </tr>
		 
		 <tr>
		    <td align="center">
		      <input type=button name="editar" value="Editar" onclick="editar_campos()" title="Edita Campos" style="width=130px"> &nbsp;&nbsp;
		      <input type="submit" name="guardar_editar" value="Guardar" title="Guarda Muleto" disabled style="width=130px" onclick="return control_nuevos()">&nbsp;&nbsp;
		      <input type="button" name="cancelar_editar" value="Cancelar" title="Cancela Edicion de Muletos" disabled style="width=130px" onclick="document.location.reload()">
		      <?if (permisos_check("inicio","permiso_boton_eliminar_muleto")){?>
		      	<input type="submit" name="eliminar_muleto" value="Eliminar" title="Eliminar Monitor RMA" style="width=130px" onclick="return confirm('Esta Seguro que Desea Eliminar?')">&nbsp;&nbsp;
		      <?}?>
		    </td>
		 </tr> 
	 </table>
	<?}
	if (!($id_muleto)){?>
	 <table width=100% align="center" class="bordes">
      <tr align="center">
       <td>
        <input type='submit' name='guardar' value='Guardar nuevo Muleto' onclick="return control_nuevos()"
         title="Guardar datos de un Nuevo Muleto">
       </td>
      </tr>
     </table>
     <?}

}//de function  tabla_datos_muletos()


function gen_select($name="sel", $selected=0, $datos=array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"), $size=1, $extra=""){
	    echo "<select id='$name' name='$name' size='$size' $extra>";
		$cant=count($datos);
		for ($i=0;$i<$cant;$i++) {
			if($i==$selected) $selec="selected";
			    else $selec="";
			echo "<option value=$i $selec>".$datos[$i]."</option>";
		}
		echo "</select>";
}

function ultimoDia($mes,$ano){ 
    $ultimo_dia=28; 
    while (checkdate($mes,$ultimo_dia + 1,$ano)){ 
       $ultimo_dia++; 
    } 
    return $ultimo_dia; 
} 

function mes_num_a_let($m) {
  $meses=array("Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic");
return $meses[--$m]; 
}

function mes_let_a_num($m) {
  $meses["Ene"]="1";
  $meses["Feb"]="2";
  $meses["Mar"]="3";
  $meses["Abr"]="4";
  $meses["May"]="5";
  $meses["Jun"]="6";
  $meses["Jul"]="7";
  $meses["Ago"]="8";
  $meses["Sep"]="9";
  $meses["Oct"]="10";
  $meses["Nov"]="11";
  $meses["Dic"]="12";
return $meses[$m]; 
}


//$fecha_ini $aaaa-m-dd
//$fechha_fin $aaaa-m-dd
//si flag ==0 muestroel naño con los 4 digitos
function armar_datax($fecha_ini,$fecha_fin,$flag=1) {

 $datax=array();
 
 $i=0;

 while (compara_fechas($fecha_ini,$fecha_fin) <=0) {
 $fecha_split=split("-",$fecha_ini);
 $a=$fecha_split[0];
 $anio=mes_num_a_let($fecha_split[1])."-";
     if ($flag==0) $anio.=$a;
         else $anio.=substr($a,2,4);
     
 $datax[$i++]=$anio;    
 $m=$fecha_split[1]+1;
   if($m==13) {
   	     $m=1;
   	     $a=$fecha_split[0]+1;
   }
   else $a=$fecha_split[0];
   $fecha_ini=$a."-".$m."-01";	
 }
 
 return $datax;
}

?>