<? 
/*
$Author: mari $
$Revision: 1.4 $
$Date: 2006/06/06 22:02:18 $
*/
require_once("../../config.php");
require_once("../personal/gutils.php");
require_once("funciones.php");

echo $html_header;
global $datax;
echo "<form name='form1' action='gen_reporte_casos.php' method='POST'>";
echo "<div align='right'><input type='button' name='cerrar' value='Cerrar' onclick='window.close()'></div>";
echo "<div align='center'><b>SEGUIMIENTO OC ASOCIADAS A HONORARIOS SERV. TECNICO</b></div>";
echo "<br>";

$mes_desde=$_POST["mes_desde"] or $mes_desde=0;  //en el select los meses quedan codificados desde 0  
$atendido=$_POST["atendido"] or $atendido=-1;
$cas=$_POST["cas"] or $cas="";

if ($_POST["mes_hasta"] != "") 
	$mes_hasta=$_POST["mes_hasta"] ;
    else 
    	$mes_hasta=(date("m",mktime()))-1;
    
$anio_desde=$_POST["anio_desde"] or $anio_desde=date("Y",mktime());   
$anio_hasta=$_POST["anio_hasta"] or $anio_hasta=date("Y",mktime());  
$ini=1990;
$fin=date("Y",mktime())+2;

/*$valor_dolar=$_POST['valor_dolar'];
if (!$valor_dolar) {
        $sql_dolar="select valor from general.dolar_general";
        $res_dolar=sql($sql_dolar,"dolar $sql_dolar") or fin_pagina();
        $valor_dolar=$res_dolar->fields['valor'];
}*/
?>
<div align="center">
     <b>Desde:</b>
     <?
     gen_select("mes_desde",$mes_desde);
     echo "&nbsp;";
     g_draw_range_select("anio_desde",$anio_desde,$ini,$fin);
     echo "&nbsp;";echo "&nbsp;";
     ?>
     <b>Hasta:</b>
     <?
     gen_select("mes_hasta",$mes_hasta);
     echo "&nbsp;";
     g_draw_range_select("anio_hasta",$anio_hasta,$ini,$fin);
     ?>   
     
     <?/*<b>Dolar</b>
      <input type='text' size='4' name='valor_dolar' value='<?=number_format($valor_dolar,"2",".","")?>'>
       <img src='<?php echo "$html_root/imagenes/dolar.gif" ?>' border="0"  onclick="window.open('../../lib/consulta_valor_dolar.php','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=0,left=0,top=0,width=160,height=140')"  >
        */?> 

    
	  <?
      $sql_cas="select idate,nombre from cas_ate where activo=1 order by nombre";
	  $res_cas=sql($sql_cas,"$sql_cas") or fin_pagina();?>
	  <b>CAS: </b><select name=atendido onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()'>
	 
	  <?while (!$res_cas->EOF) {
	  	 ?>
		 <option value='<?=$res_cas->fields['idate']?>'
	  	   <? if($res_cas->fields['idate'] == $atendido) {
		 	     echo 'selected';
	  	        }
		 	     ?>>
		      <?=$res_cas->fields['nombre']?> </option>
		 <? 
		 $res_cas->MoveNext();
		 }
	  ?>
	   <option value=-1  <?if(-1 == $atendido) echo 'selected'?>>Todos</option>
	  </select>
     
         
     <input type='submit' name='actualizar' value='Actualizar'>
</div>     
<?
$link=encode_link("grafica_caso.php",array("mes_desde"=>$mes_desde,"anio_desde"=>$anio_desde,"mes_hasta"=>$mes_hasta,"anio_hasta"=>$anio_hasta,"atendido"=>$atendido));
    echo "<br>";
    $mes_hasta++;
    $mes_desde++;
   
    $dia_hasta=ultimoDia($mes_hasta,$anio_hasta);
    $fecha1=$anio_desde."-".$mes_desde."-"."01";
    $fecha2=$anio_hasta."-".$mes_hasta."-".$dia_hasta;
      
    if (compara_fechas($fecha1,$fecha2)==1) 
        Error ("La fecha de inicion debe ser menor que la fecha de Fin");
    if (!$error) 
     	echo "<div align='center'><img src='$link' border=0 align=top></div>\n";
     /*    ?>
         <input type='button' name='b' value='V' onclick="window.open('<?=$link?>','','')">
         <?*/
  $datax=armar_datax($fecha1,$fecha2,0);
  $cant=count($datax);
  
  ?>
   <table align='center'>
   <tr>
    <? for($i=0;$i<$cant;$i++){
    	   $link=encode_link("datos_reporte_casos.php",array("fec"=>$datax[$i],"atendido"=>$atendido,"cas"=>$cas));?>
           <td title='ver ordenes'><a href='<?=$link?>' target="_blank"> <font color='Black'><?=$datax[$i]?></font></a></td>  
          
      <?} ?>    
   <tr>
   </table>
  <?
  

?>
</form>