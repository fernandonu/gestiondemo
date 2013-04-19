<?
/*
Author: Broggi
Fecha: 11/08/2004

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.5 $
$Date: 2004/12/06 19:17:43 $
*/


require_once("../../config.php");





$anio=$parametros['anio'] or $anio=$_POST['select_anio'];
$mes=$parametros['mes'] or $mes=$_POST['select_mes'];
$empleados=$parametros['id_personal'] or $empleados=$_POST['empleados'];
$id_sueldo=$parametros['id_sueldo'] or $id_sueldo=$_POST['id_sueldo'];
$paso_cmd=$parametros['cmd'] or $paso_cmd=$_POST['cmd'];

if ($_POST['caja_sanluis']=="San Luis")
{$control=1;
 $cuentas = array();
 $cuotas = array();
 while ($control<=$_POST['cantidad_cuentas'])
 {$cuentas[$control]=$_POST['cuenta_'.$control];
  $cuota_inicio=$_POST['cuota_inicial_'.$_POST['cuenta_'.$control]];
  $cuota_fin=$_POST['cuota_final_'.$_POST['cuenta_'.$control]];
  $cuotas[$_POST['cuenta_'.$control]]=array();
  $cuotas[$_POST['cuenta_'.$control]]['cuota_inicio']=$_POST['cuota_inicial_'.$_POST['cuenta_'.$control]];//guardo la posicion desde donde empieso a recorre el segundo arreglo
  $control_hay_cuotas=0;
  while($cuota_inicio<=$cuota_fin)
     {if ($_POST['cuota_'.$cuota_inicio.'_'.$_POST['cuenta_'.$control]])
         {$control_hay_cuotas=1;
          $cuotas[$_POST['cuenta_'.$control]]['cuota_final']=$cuota_inicio;//guardo la posicion de la ultiam cuota que se paga con este sueldo
          $cuotas[$_POST['cuenta_'.$control]][$cuota_inicio]=$_POST['id_cuota_'.$cuota_inicio.'_'.$_POST['cuenta_'.$control]];          
         }//del if
      $cuota_inicio++;   	
     }//del wuile de cuota	
 if ($control_hay_cuotas==0) $cuotas[$_POST['cuenta_'.$control]]['cuota_inicio']=0;//esto es por si no pago ninguna cuota de esa cuenta
 $control++; 
 }  
 $link=encode_link("../caja/ingresos_egresos.php",array("pagina"=>"egreso","bolsillo"=>number_format($_POST['bolsillo'],2,'.',''),
                                                   "pagina_viene"=>"pago_sueldo","id_sueldo"=>$id_sueldo,"cuotas"=>$cuotas,"distrito"=>1,
                                                   "cuentas"=>$cuentas,"cantidad_cuentas"=>$_POST['cantidad_cuentas'],"mes"=>$mes,"anio"=>$anio));                                                  
 header("location:$link");
}//para la caja de san luis

elseif ($_POST['caja_bsas']=="Buenos Aires")	
{$control=1;
 $cuentas = array();
 $cuotas = array();
 while ($control<=$_POST['cantidad_cuentas'])
 {$cuentas[$control]=$_POST['cuenta_'.$control];
  $cuota_inicio=$_POST['cuota_inicial_'.$_POST['cuenta_'.$control]];
  $cuota_fin=$_POST['cuota_final_'.$_POST['cuenta_'.$control]];
  $cuotas[$_POST['cuenta_'.$control]]=array();
  $cuotas[$_POST['cuenta_'.$control]]['cuota_inicio']=$_POST['cuota_inicial_'.$_POST['cuenta_'.$control]];//guardo la posicion desde donde empieso a recorre el segundo arreglo
  $control_hay_cuotas=0;
  while($cuota_inicio<=$cuota_fin)
     {if ($_POST['cuota_'.$cuota_inicio.'_'.$_POST['cuenta_'.$control]])
         {$control_hay_cuotas=1;
          $cuotas[$_POST['cuenta_'.$control]]['cuota_final']=$cuota_inicio;//guardo la posicion de la ultiam cuota que se paga con este sueldo
          $cuotas[$_POST['cuenta_'.$control]][$cuota_inicio]=$_POST['id_cuota_'.$cuota_inicio.'_'.$_POST['cuenta_'.$control]];          
         }//del if
      $cuota_inicio++;   	
     }//del wuile de cuota	
 if ($control_hay_cuotas==0) $cuotas[$_POST['cuenta_'.$control]]['cuota_inicio']=0;//esto es por si no pago ninguna cuota de esa cuenta
 $control++; 
 }  
 $link=encode_link("../caja/ingresos_egresos.php",array("pagina"=>"egreso","bolsillo"=>number_format($_POST['bolsillo'],2,'.',''),
                                                   "pagina_viene"=>"pago_sueldo","id_sueldo"=>$id_sueldo,"cuotas"=>$cuotas,"distrito"=>2,
                                                   "cuentas"=>$cuentas,"cantidad_cuentas"=>$_POST['cantidad_cuentas'],"mes"=>$mes,"anio"=>$anio));                                                   
 header("location:$link");
}//para la caja de buenos aires	




$control_2=$_POST['todas'];




///////////////////////CALCULO EL NETO////////////////////////////////////
$fecha_2=$anio."-".$mes;
$sql_3 = "select * from sueldos where id_legajo=$empleados and fecha ilike '$fecha_2%'";
$resultado= sql($sql_3) or fin_pagina();
  $subtotal=$resultado->fields['basico']+$resultado->fields['dec1529'];
  $subtotal2=$resultado->fields['presentismo']+$subtotal+$resultado->fields['acuenta'];
  $total=$subtotal2+$resultado->fields['vacaciones']+$resultado->fields['ausentismo'];
  //esto es para calcular el sac
  $mes_sac=$resultado->fields['mes_sac'];
  $dias_sac=$resultado->fields['dias_sac'];
  $mxd_sac=$mes_sac*30;
  $arreglo['sac']=(($total/2)/180)*($mxd_sac+$dias_sac);
  //este muestra el total del sac 
  $total_desc=$resultado->fields['jubilacion']+$resultado->fields['ley19032'];
  $total_desc+=$resultado->fields['obra_social']+$resultado->fields['sindicato'];
  $total_desc+=$resultado->fields['sindicato_familiar']+$resultado->fields['faecys'];
  $total_no_rem=$resultado->fields['salario_familiar']+$resultado->fields['dec1347']+$resultado->fields['ayuda_escolar'];
  $neto=$total-$total_desc+$total_no_rem;
  //tengo que agregarle lo del sac que si no se marca es cero
  $neto=$neto+$arreglo['sac'];
  $arreglo['subtotal']=$subtotal;
  $arreglo['subtotal2']=$subtotal2;
  $arreglo['total']=$total+$arreglo['sac'] ;
  $arreglo['total_desc']=$total_desc;
  $arreglo['total_no_rem']=$total_no_rem;
  $arreglo['neto']=$neto;
//////////////////////////////////////////////////////////////////////////



$meses[1]="Enero";
$meses[2]="Febrero";
$meses[3]="Marzo";
$meses[4]="Abril";
$meses[5]="Mayo";
$meses[6]="Junio";
$meses[7]="Julio";
$meses[8]="Agosto";
$meses[9]="Setiembre";
$meses[10]="Octubre";
$meses[11]="Noviembre";
$meses[12]="Diciembre";

function make_select_mes($selected)
{
	global $meses;
	for ($i=1; $i <= 12 ; $i++)
     echo "<option value=".(($i<10)?"0$i":$i) .(($i==$selected)?' selected>':'>')."$meses[$i]</option>";
	
}

function make_select_anio($selected)
{
	$i=date('Y')-10; //diez anios antes
	$j=$i+30; //diez anios despues
	$i=$i-1;
	while (++$i <= $j)
     echo "<option ".(($i==$selected)?'selected>':'>')."$i</option>";
	
}

echo $html_header;
?>

<script languaje='javascript' src='<?=$html_root?>/lib/NumberFormat150.js'></script>
<script>
function calcular_bolsillo(ejecutar)
{neto=parseFloat(document.pago_sueldo.neto_2.value);
 if (ejecutar==1)
 {i=1;  
  while (i<=cantidad_cuentas)
  {nro_cuenta=eval("document.pago_sueldo.cuenta_"+i+".value");
   cuota_inicial=eval("document.pago_sueldo.cuota_inicial_"+nro_cuenta+".value");
   cuota_final=eval("document.pago_sueldo.cuota_final_"+nro_cuenta+".value");
   while (cuota_inicial<=cuota_final)
    {chequeado=eval("document.pago_sueldo.cuota_"+cuota_inicial+"_"+nro_cuenta+".checked");    
    	if (chequeado)
    	 {descontar=eval("parseFloat(document.pago_sueldo.monto_cuota_"+cuota_inicial+"_"+nro_cuenta+".value);");   	  
    	  neto=neto-descontar;   	  
    	 }	   
    	cuota_inicial++;
    }	        
   i++;
  }
 } 
 document.pago_sueldo.bolsillo.value=formato_money(neto);
}

function calcular_bolsillo_2(valor,cuota,cheq)
{partes=cuota.split('_');
 cheq_pasado=cheq.split('_'); 
 if (valor.checked)
    {cuota_inicio=eval('document.pago_sueldo.cuota_inicial_'+partes[3]+'.value');
     if (cuota_inicio!=cheq_pasado[1])
        {anterior=parseInt(cheq_pasado[1])-1;
         anterior_cheq=eval('document.pago_sueldo.cuota_'+anterior+'_'+cheq_pasado[2]+'.checked')
         if (!anterior_cheq)
            {strin=eval('document.pago_sueldo.cuota_'+cheq_pasado[1]+'_'+cheq_pasado[2]+'.checked=0');            
             alert("Tiene cuotas anterioes por pagar");
             return false;
            }	
          
        }	     
     descontar=eval("parseFloat(document.pago_sueldo."+cuota+".value)");
     neto=neto-descontar;
     document.pago_sueldo.bolsillo.value=formato_money(neto);
    }	        
 else
   {cuota_fin=eval('document.pago_sueldo.cuota_final_'+partes[3]+'.value');
    if (cuota_fin!=cheq_pasado[1])
       {siguiente=parseInt(cheq_pasado[1])+1;        
        siguiente_cheq=eval('document.pago_sueldo.cuota_'+siguiente+'_'+cheq_pasado[2]+'.checked')
         if (siguiente_cheq)
            {strin=eval('document.pago_sueldo.cuota_'+cheq_pasado[1]+'_'+cheq_pasado[2]+'.checked=1');
             alert("No puede desmarcar esta cuota,\nporque tiene cuotas mas nuevas seleccionadas");
             return false;
            }	
          
        }	
   	descontar=eval("parseFloat(document.pago_sueldo."+cuota+".value)");
    neto=neto+descontar;
    document.pago_sueldo.bolsillo.value=formato_money(neto);
   }	    
}


//////////////////////PARA MOSTRAR LA VENTANA QUE ELIGE LA CAJA///////////////////
function show_div(obj)
{
  var leftpos=0;
  var toppos=0;
  if (document.all.div_mail.style.visibility=='hidden')
  {
	  document.all.div_mail.left=pago_sueldo.pagar.offsetParent.offseLeft;
	  document.all.div_mail.top=pago_sueldo.pagar.offsetParent.offsetTop + obj.offsetHeight +2;
	  document.all.div_mail.style.visibility='visible';
	  window.event.cancelBubble=true;
	  //window.event.stopPropagation();	  
	  document.onclick=hide_div;
  }
  else
  {
  	hide_div();
  } 
}
function hide_div()
{
 document.all.div_mail.style.visibility="hidden";
 document.onclick=null;
}
//////////////////////////////////////////////////////////////////////////////////

</script>


<form name="pago_sueldo" action="pago_sueldo.php" method="POST" >
<br>
<?$ejecutar_java=0;?>
<table align="center" width="80%">
 <tr id=mo>
  <td colspan="2"><font size="4"><b>Pago de Sueldos</b></font></td>
 </tr>
 <tr> 
  <td width="50%" align="center" valign="middle"  > <b>Periodo:</b> &nbsp; <select name="select_mes" id="select_mes" onchange="cargar_pagina()"><?make_select_mes($mes);?></select>
   &nbsp; <b>Año</b> &nbsp; <select name="select_anio" id="select_anio" onchange="cargar_pagina()"><?make_select_anio($anio);?></select> 
  </td>
  <td width="50%" align="center">
   <?$sql = "Select * from personal.legajos order by apellido,nombre";
     $resultado_legajos=sql($sql) or fin_pagina();   
   ?>
   <b>Empleado</b>: &nbsp;<select name="empleados" onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange="cargar_pagina()">
     <option selected value=-1>Selecione Empleado</option>
     <?while (!$resultado_legajos->EOF)
       {if ($empleados==$resultado_legajos->fields['id_legajo'])
         $selected="selected";
        else 
         $selected=" ";
      ?>
       <option <?=$selected?> value="<?=$resultado_legajos->fields['id_legajo']?>">
       <?=$resultado_legajos->fields['apellido'].", ".$resultado_legajos->fields['nombre']?>
      <? 
       $resultado_legajos->MoveNext();	
       }	 
     ?>
   </select>  
  </td>
 </tr>
</table> 
<br> 
<hr width="90%" size="2" style="color:black">  
<br>  
<?
 if ($paso_cmd=="pendientes")
 {
?> 
<input type="hidden" name="cmd" value="<?=$paso_cmd?>">	 
<table  align="center" width="70%" cellspacing="2" cellpadding="2" class="bordes">
 <tr>
  <td align="center" colspan="3"><b>Sueldo Neto: &nbsp;$</b><input name="neto" type="text" size="13" value="<?=formato_money($arreglo['neto'])?>" readonly> </td>
  <input type="hidden" name="neto_2" value="<?=$arreglo['neto']?>">  
  <input type="hidden" name="id_sueldo" value="<?=$id_sueldo?>">   
 </tr>
 <tr>
  <td colspan="3">
   <table  align="center" width="80%" >
    <tr id=mo>
     <td align="center" colspan="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     <font size="2"><b>Cancelación de Adelantos</b></font>               
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  	         
     <input name="todas" value="Todas" title="Muestra todas las cuotas restantes" type="submit" ></td>     
    </tr>            
    <?if ($empleados!="" && $resultado->RecordCount()>0)
    {
     $sql_2="select id_cuenta, cuota, monto_cuota, fecha_de_pago, id_cuota from personal.cuenta join personal.cuota using(id_cuenta) 
             where (id_legajo=$empleados and cuenta.estado=3 and cuota.estado=1)";
     $resultado_consulta_2=sql($sql_2) or fin_pagina();
     $sql_4="select distinct id_cuenta, id_legajo from personal.legajos join personal.cuenta using(id_legajo) 
             where id_legajo=$empleados and cuenta.estado=3";
     $resultado_consulta_4=sql($sql_4) or fin_pagina();             
     $cantidad_cuentas=$resultado_consulta_4->RecordCount();
     
     ?>
      <script>
       cantidad_cuentas=<?=$resultado_consulta_4->RecordCount()?>;
      
       if (cantidad_cuentas==0) {document.pago_sueldo.todas.disabled=1}
      </script>
     <?
     
     echo "<input type='hidden' name='cantidad_cuentas' value='".$cantidad_cuentas."'>";
     $cant_cuentas=0;//me da la cantidad de cuentas que tengo
      while (!$resultado_consulta_4->EOF)
     {$cant_cuentas++;
      $control=1;
      $ctrl_2=1;
      $ctrl_3=1;     
     ?>
     
     </tr>
     <tr id=ma>
      <td colspan="4" align="center"><b>Número de Adelanto:&nbsp;<?=$resultado_consulta_4->fields['id_cuenta'];?></b></td>
      <input type="hidden" name="cuenta_<?=$cant_cuentas?>" value="<?=$resultado_consulta_4->fields['id_cuenta'];?>">      
     </tr>        
    <tr id=ma>
     <td align="center"><font color="Black"><b>Nro. Cuota</b></font></td>
     <td align="center"><font color="Black"><b>Monto</b></font></td>
     <td align="center"><font color="Black"><b>Fecha Pago</b></font></td>
     <td align="center"><font color="Black"><b>Pagar</b></font></td>
    </tr>
     <?        
     while (!$resultado_consulta_2->EOF && $control==1 && $resultado_consulta_2->fields['id_cuenta']==$resultado_consulta_4->fields['id_cuenta'])
      {$fecha_control=split("/",date_spa("m/Y",$resultado_consulta_2->fields['fecha_de_pago']));           
       $suma_1=$fecha_control[1].$fecha_control[0];
       $suma_2=$anio.$mes;  
       if ($suma_1>$suma_2 && $ctrl_2==1 && $control_2=="")
       {
       	?>
       	 <tr><td align="center" colspan="4"><font size="2"><b>No hay cuotas para pagar este Mes</b></font></td></tr>
       	<?
       	$control=0;
       	$ctrl_2=0;
       }	   
      else 
      {if ($suma_1<=$suma_2) echo "<tr ".atrib_tr("red").">";
       else echo "<tr ".atrib_tr().">";      
       if ($ctrl_3==1) 
          {
          ?>
           <input type="hidden" name="cuota_inicial_<?=$resultado_consulta_4->fields['id_cuenta']?>" value="<?=$resultado_consulta_2->fields['cuota']?>">
          <?	
          $ctrl_3=0;
          }	
    ?>          
      <td align="center">
       <?=$resultado_consulta_2->fields['cuota'];$ctrl_2=0;?>
      </td>
      <td align="center">
       <?=formato_money($resultado_consulta_2->fields['monto_cuota'])?>
      </td>
      
      <td align="center">
       <?=date_spa("m/Y",$resultado_consulta_2->fields['fecha_de_pago'])?>
      </td>
      <td align="center">
       <?$ejecutar_java=1;
         $pasa_checkbox="cuota_".$resultado_consulta_2->fields['cuota']."_".$resultado_consulta_4->fields['id_cuenta'];
         $pasa_cuota="monto_cuota_".$resultado_consulta_2->fields['cuota']."_".$resultado_consulta_4->fields['id_cuenta'];
       ?>              
       <input type="checkbox" name="cuota_<?=$resultado_consulta_2->fields['cuota']."_".$resultado_consulta_4->fields['id_cuenta']?>" <?if ($suma_1<=($suma_2+1)) {?>  checked<?}?> onclick="calcular_bolsillo_2(this,'<?=$pasa_cuota?>','<?=$pasa_checkbox?>')">       
       <input type="hidden" name="monto_cuota_<?=$resultado_consulta_2->fields['cuota']."_".$resultado_consulta_4->fields['id_cuenta']?>" value="<?=$resultado_consulta_2->fields['monto_cuota']?>">
       <input type="hidden" name="id_cuota_<?=$resultado_consulta_2->fields['cuota']."_".$resultado_consulta_4->fields['id_cuenta']?>" value="<?=$resultado_consulta_2->fields['id_cuota']?>">
      </td>
     </tr>
    <?
      $nro_auxiliar=$resultado_consulta_2->fields['cuota'];
      $resultado_consulta_2->MoveNext();      
       if ($suma_1>($suma_2+1) && $control_2=="")
          {
          	
           $control=0;
           while ($resultado_consulta_4->fields['id_cuenta']==$resultado_consulta_2->fields['id_cuenta'])
           {$resultado_consulta_2->MoveNext(); } 
          }	            
      
      }//del else que controla en caso qeu no tenga cuotas para pagar ese mes 
      }//sierra el while de la consulta_2 que es la qeu guarda las cuotas
     ?>
      <input type="hidden" name="cuota_final_<?=$resultado_consulta_4->fields['id_cuenta']?>" value="<?=$nro_auxiliar?>">
     <? 
     while ($resultado_consulta_4->fields['id_cuenta']==$resultado_consulta_2->fields['id_cuenta'])
           {$resultado_consulta_2->MoveNext(); }
           
      $resultado_consulta_4->MoveNext();      
  
     }//sierra el while de la consulta_4 que es al que guarda la cantidad de cuentas que tiene esa persona
    }
    else {if ($empleados!="")
             {if ($resultado->RecordCount()==0 && $empleados!="")
                 {
                 ?>
                 <tr><td align="center"><font size="3" color="Red"><b>Este mes aun no se Liquida</b></font> </td></tr>
                 <?	
                 }	
             }
         }    
    ?>
   </table>
  </td>  
 </tr>
 <tr><td colspan="3">&nbsp;</td></tr> 
 <tr>
  <td align="center" ><b>Total Bolsillo: &nbsp;$</b> <input name="bolsillo" type="text" size="13" value="" readonly>
 
    </td>
  <td align="right">


  <input name="pagar" value="Pagar" type="button" onclick="show_div(this)">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td> 
 </tr>  
</table>
<table align="center">
<tr>
<td>
 <!-- ---------------------------------------------------------------- -->
<div id="div_mail"  style="width:250px;visibility:hidden; position:absolute; z-index:+999;"  >
<table  border="1" cellspacing=0 bgcolor=#CCCCCC  bordercolor=black width="100%">
 <tr>
  <td> 
   <table align="center">
    <tr>
     <td colspan="3" align="center">
      <b>Selecione la Caja desde </b>
     </td>
    </tr>
    <tr>
     <td colspan="3" align="center">
      <b>donde se descontara el Pago</b>
     </td>
    </tr>    
    <tr>
     <td>&nbsp;</td> 
     <td align="center"><input type="submit" name="caja_sanluis" value="San Luis"></td>
     <td align="center"><input type="submit" name="caja_bsas" value="Buenos Aires"></td>     
    </tr> 
    <tr>
     <td>&nbsp;</td> 
     <td colspan="2">&nbsp;</td>     
    </tr>
   </table>
  </td> 
 </tr>
</table>
</div>
<!-- ---------------------------------------------------------------- -->
</td>
</tr>
</table>
<script>
mes=<?=$mes?>;
anio=<?=$anio?>;
<?if ($ejecutar_java==1)
 {
 ?>
 ejecutar=1;
 <?
 }
 else {?> ejecutar=0;<?}?>
calcular_bolsillo(ejecutar);

</script>
<?
 }//hasta aca se hace todo si bien de pendientes
 elseif ($paso_cmd=="terminados")
 {//esto es por si viene de terminado
  $sql="select id_cuenta, usuario, monto, fecha_creacion, id_sueldo, cuota, monto_cuota, fecha_de_pago, id_ingreso_egreso
        from sueldos left join cuota using(id_sueldo) 
        left join caja.ingreso_egreso using(id_ingreso_egreso)
        where id_sueldo=".$parametros['id_sueldo'];
  $resultado_sql=sql($sql) or fin_pagina();
  ?>
  <input type="hidden" name="cmd" value="<?=$paso_cmd?>">
  <table align="center" width="70%" cellspacing="2" cellpadding="2">
   <tr>
    <td align="center" colspan="2"><font size="2"><b>Log de Pago</b><font></td>
   </tr> 
   <tr>
    <td><b>Usuario: </b><?=$resultado_sql->fields['usuario']?></td>
    <td><b>Fecha efectiva de pago: </b><?=fecha($resultado_sql->fields['fecha_creacion'])?></td>
   </tr>
   <tr>
    <td colspan="2"><b>Monto pagado: </b><?=formato_money($resultado_sql->fields['monto'])?></td>        
   </tr>
  </table>
  <table align="center" width="70%" cellspacing="2" cellpadding="2" class="bordes">   
  <tr id=mo>
   <td align="center" colspan="3"><font size="2"><b>Cuotas Pagadas</b></font></td>
  </tr>
  <tr id=ma>
   <td align="center"><font color="Black"><b>Nro. Cuota</b></font></td>
   <td align="center"><font color="Black"><b>Monto</b></font></td>
   <td align="center"><font color="Black"><b>Fecha Pago</b></font></td>     
  </tr>
  <?
  if ($resultado_sql->fields['cuota']!="")
  {while (!$resultado_sql->EOF)
    {echo "<tr ".atrib_tr().">"
   ?>
    <td align="center"><b><?=$resultado_sql->fields['cuota']?></b></td>
    <td align="center"><b><?=formato_money($resultado_sql->fields['monto_cuota'])?></b></td>
    <td align="center"><b><?=fecha($resultado_sql->fields['fecha_de_pago'])?></b></td>
   <?
    $resultado_sql->MoveNext(); 	
    }
  }
  else
  {
  ?>
   <tr >
    <td colspan="3" align="center"><font size="2"><b>Con este pago no se cancelo ninguna cuota</b></font></td>
   </tr>
  <?	 
  }	  	
  ?>
  
  </table>
  <?  
 } 
?>       



</form>