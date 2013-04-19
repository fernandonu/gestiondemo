<?
/*
MODIFICADA POR
$Author: enrique $
$Revision: 1.1 $
$Date: 2005/12/13 12:41:57 $
*/

require_once("../../config.php");
 //$id_remito=$_POST['id_remito'] or $id_remito=$parametros['id_remito'];
 $des=$_POST["des"] or $des=$parametros['des'];
 $has=$_POST["has"] or $des=$parametros['has'];
 $ordenar_listado=$_POST["ordenar_listado"] or $ordenar_listado=$parametros['ordenar_listado'];
function enviar($nombre_archivo)
{
global $buffer;
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: must-revalidate");
header("Content-Transfer-Encoding: binary");
Header('Content-Type: application/dummy');
Header('Content-Length: '.strlen($buffer));
Header('Content-disposition: attachment; filename='.$nombre_archivo);
echo $buffer;
}
	
	$consulta="SELECT licitacion.id_licitacion,entrega_estimada.nro,prioridad,comentarios_lic,entrega_estimada.id_entrega_estimada,entrega_estimada.comprado,vence_oc,fecha_subido,
entidad.nombre,sistema.usuarios.iniciales,lider,ini_compra,ini_compra1,id_subir,fin_compra,ini_entrega,fin_entrega,ini_cdr,fin_cdr,guardado,entidad.id_distrito
FROM licitaciones.licitacion 
LEFT JOIN licitaciones.entidad USING (id_entidad)
left JOIN licitaciones.entrega_estimada USING (id_licitacion)
LEFT JOIN sistema.usuarios on usuarios.id_usuario=licitacion.lider
JOIN licitaciones.subido_lic_oc USING (id_entrega_estimada)
left join (select ini_compra1,compra1.id_subir,nro_orden from
(select min(fecha) as ini_compra1,id_subir from compras.orden_de_compra group by (id_subir))as compra1 
join compras.orden_de_compra on compra1.id_subir=orden_de_compra.id_subir and compra1.ini_compra1=orden_de_compra.fecha)as compras2 using(id_subir)
where entrega_estimada.finalizada=0 AND borrada='f' ";

if($ordenar_listado==1)
$consulta.="order by ini_entrega asc";
else $consulta.="order by prioridad";

$ejecutar=sql($consulta,"no se pudo recuperar los datos de las licitaciones") or fin_pagina();
	
	
     $dia_actu=date("Y-m-d",mktime());
	 $contador=diferencia_dias($des,$has);
	 $cont=diferencia_dias($des,$has);
	 $Fecha_Desde=$des;
	 $Fecha_Hasta=$has;
	 $diferencia1=diferencia_dias($Fecha_Desde, $Fecha_Hasta);
	 $des=Fecha_db($des);
	 $has=Fecha_db($has);
	 list($anio_1,$mes_1,$dia_1)=explode("-",$des);
	 list($anio,$mes,$dia)=explode("-",$has);
	 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ant1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia2=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia3=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ult=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));	


$buffer="<table id='tabla_p' name='tabla_p' border='1' width='80' style='font-size:12px'>
<tr>
	 <td align='center' bgcolor='$bgcolor_out'><input type='checkbox' name='check_todos' value='1' onclick='chequear_todos()'></td>
	 <td align='center' bgcolor='$bgcolor_out'><b>Lic/Cli</b></td>
	 <td bgcolor='$bgcolor_out'><b>Lid</b></td>";
	  
	 $fondo="#cccccc";
	 $ar_mes[1]="Enero";
	 $ar_mes[2]="Febrero";
	 $ar_mes[3]="Marzo";
	 $ar_mes[4]="Abril";
	 $ar_mes[5]="Mayo";
	 $ar_mes[6]="Junio";
	 $ar_mes[7]="Julio";
	 $ar_mes[8]="Agosto";
	 $ar_mes[9]="Septiembre";
	 $ar_mes[10]="Octubre";
	 $ar_mes[11]="Noviembre";
	 $ar_mes[12]="Diciembre";
	 while($contador>=0)	
	{   
		 list($anio,$mes,$dia)=explode("-",$dia_ant);
		 $dia_letras=date("D",mktime(00,0,0,$mes,$dia,$anio));
		 $mes_letras=date("n",mktime(0,0,0,$mes,$dia,$anio));
		 $fe_com=date("r",mktime(0,0,0,$mes,$dia,$anio));
		 $dia_feriado=Fecha($dia_ant);
		 if(($dia==01)&&($fondo=="#cccccc")) 
		 {
	     $fondo='#FFFFFF'; 
		 }
		 else 
		 {
		 if(($dia==01)&&($fondo=="#FFFFFF")) 
		 {
	     $fondo="#cccccc"; 
		 }	
		 }  
		 if($dia==01)
		 {
		 if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
		 {	
			 
			 $buffer.="<td bgcolor='<?=$fondo?>' title='$fe_com' width='2'><font color='Red'><b>$dia/$mes</b></font></td>";
			
		 }
		 else 
		 { 
		  if($dia_actu==$dia_ant)
		 {	
			
			 $buffer.="<td bgcolor='Blue' class='actual' title='$fe_com'><font color='Red'>$dia/$mes</font></td>";
			
		 }
		 else 
		 { 	
		 	
			
			  $buffer.="<td bgcolor='<?=$fondo?>' title='$fe_com'><b> $dia/$mes</b></td>";
			
		 }
		 }
		 }
		 else 
		 {
          if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
		 {	
			 
			 $buffer.="<td bgcolor='<?=$fondo?>' title='$fe_com'><font color='Red'> $dia</font></td>";
			 
		 }
		 else 
		 { 
		  if($dia_actu==$dia_ant)
		 {	
			 
			 $buffer.="<td bgcolor='Blue' class='actual' title='$fe_com'><b> $dia</b></td>";
			
		 }
		 else 
		 { 	
			 
			  $buffer.="<td bgcolor='$fondo' title='$fe_com' width='4'><b> $dia</b></td>";
			
		 }
		 }
		 }   
		 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));
	     $contador--; 
	
     }    
   
$buffer.="</tr>";

$cantidad_lic=$ejecutar->RecordCount();
$i=1;
$int1=0;
while(!$ejecutar->EOF)
{   $co=1;
    
	$int1++;
    $j=$ejecutar->RecordCount();
	$fin_compra=$ejecutar->fields['fin_compra'];
    $ini_compras=$ejecutar->fields['ini_compra'];
    $lic_com=$ejecutar->fields['comentarios_lic'];
    $fin_cdr=$ejecutar->fields['fin_cdr'];
    $ini_cdr=$ejecutar->fields['ini_cdr'];
    $fin_entrega=$ejecutar->fields['fin_entrega'];
    $ini_entrega=$ejecutar->fields['ini_entrega'];
    $vence_oc=$ejecutar->fields['vence_oc'];
    $id_subir=$ejecutar->fields['id_subir'];
    $id_distrito=$ejecutar->fields['id_distrito'];
    $id_sub=$ejecutar->fields['id_subir'];
    $guardado_base=$ejecutar->fields['guardado'];
    $priorid=$ejecutar->fields['prioridad'];
    $recup="select fecha,color from fecha_color where id_subir=$id_sub";
    $color_dia=sql($recup,"no se pudo recuperar el color del dia")or fin_pagina();
    $sumador=0;
    while(!$color_dia->EOF)
	{
		$fec_col[$sumador]=$color_dia->fields['fecha'];	
		$col_d[$sumador]=$color_dia->fields['color'];	
		$sumador++;
		$color_dia->MoveNext();
	}
    if($guardado_base==1)
    {
    $fin_entrega=Fecha_db(Fecha($fin_entrega));
    $ini_compra=Fecha_db(Fecha($ini_compras));
    $ini_entrega=Fecha_db(Fecha($ini_entrega));	
    $fin_cdr=Fecha_db(Fecha($fin_cdr));
    $ini_cdr=Fecha_db(Fecha($ini_cdr));	
    $fin_compra=Fecha_db(Fecha($fin_compra));
    }
    else 
    {
    $fin_entrega=$vence_oc;
    $ini_compra1=Fecha($ejecutar->fields['ini_compra1']);	
    if($ini_compra1!="")
    {
    	$ini_compra=Fecha_db($ini_compra1);	
    }
    else
    { 
    	$ini_compra=Fecha_db(Fecha($ejecutar->fields["fecha_subido"]));
    }	
       
    //un dia de entrega para id_distrito=12 => Prov Bs As
    //2 dias de entregas en otro distrito
    $fin_entrega1=Fecha($fin_entrega);
    if ($id_distrito==12) 
        $ini_entrega=Fecha_db(dias_habiles_anteriores($fin_entrega1,1)); //resta 1 dias
    else  
       $ini_entrega=Fecha_db(dias_habiles_anteriores($fin_entrega1,2)); //resta 2 dia
   
   
    //$fin_entrega=Fecha_db($fin_entrega);    
	//fin_cdr 
   
     	list($anio,$mes,$dia)=explode("-",$ini_entrega);
     	$fin_cdr=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));
 
//ini_cdr 
  
 	if ($ini_cdr == "" || $ini_cdr == NULL) {
      	$sql_cdr="select sum (renglones_oc.cantidad) as cantidad from licitaciones.renglones_oc 
                  join licitaciones.renglon using (id_renglon) where id_subir=$id_subir and 
                  (tipo='Computadora Enterprise' or tipo='Computadora Matrix')";
        $res_cdr=sql($sql_cdr) or fin_pagina();
        if ($res_cdr->fields['cantidad'] !=NULL && $res_cdr->fields['cantidad']!="") 
        {
        	$cantidad=$res_cdr->fields['cantidad'];
            $sql_cant="select cant_dias from licitaciones.dias_armado_cdr where $cantidad between lim_inf and lim_sup";
            $res_cant=sql($sql_cant,"no se pudo recuperar las cantidades") or fin_pagina();
            $dias=$res_cant->fields['cant_dias']; 
            
            $fin_cdr1=Fecha($fin_cdr);
           
            $id_l=$ejecutar->fields["id_licitacion"];
            $ini_cdr=Fecha_db(dias_habiles_anteriores($fin_cdr1,$dias));
            list($anio,$mes,$dia)=explode("-",$ini_cdr);
     	    $ini_cdr=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));
          
        }
        else 
        {
        	$ini_cdr=$ini_entrega;
        }
      }
      
	 
 
	$fin_compra=$ini_cdr;
	list($anio,$mes,$dia)=explode("-",$fin_compra);
    $fin_compra=date("Y-m-d",mktime(0,0,0,$mes,$dia-1,$anio));
    
    
    }

    if($ordenar_listado==1)
    {
    
    	$update_p="update licitaciones.subido_lic_oc set prioridad=$i where id_subir=$id_sub";
		$update_p1=sql($update_p,"No se pudo actualizar la prioridad1")or fin_pagina();		
    }
    else 
    {
    if($priorid==0)
    {
	    $update_p="update licitaciones.subido_lic_oc set prioridad=$i where id_subir=$id_sub";
		$update_p1=sql($update_p,"No se pudo actualizar la prioridad1")or fin_pagina();	
    }
    }
    $vence=$ejecutar->fields["vence_oc"];
	$comien=$ejecutar->fields["fecha_subido"];
	$id=$ejecutar->fields['id_licitacion'];
	$nombre_e=cortar($ejecutar->fields["nombre"],10);
    $nombre_cli=$ejecutar->fields["nombre"];
	?>
	<tr>
	<input type="hidden" name="id_<?=$i?>" value="<?=$id_sub?>">
	
	<input type="hidden" name="idlic_<?=$i?>" value="<?=$id?>">
	 
	<?
	if($guardado_base==1)
	{
	
	
	$buffer.="<td rowspan='3' bgcolor='Navy' class='separador' align='center'  title='$i'>
	
	<input type='checkbox' value='$id_sub'><br>";
	
	$en_la_base=0;
	}
	else 
	{
	
	$buffer.="<td rowspan='3' bgcolor='Olive' class='separador' align='center' title='$i'>
	
	<input type='checkbox' value='$id_sub'>
	<br>";
	}
	
	$buffer.="<select onchange='lic_posterior($id_sub)'>";
	
	$int=1;
	while($j>=$int)	
	{
	if($i==$int)
	{
	
    $buffer.="<option selected value='$i'>$i</option>";
    
	}
    else
    {
    
    $buffer.="<option value='$int'>$int</option>";  
   
    }
    $int++;
    }
   
    $buffer.="</select>";   	
	
    $buffer.="</td>
	<td rowspan='2' align='center'  title='$ejecutar->fields['id_licitacion'].' '.$nombre_cli'><b> 
	N° $ejecutar->fields['id_licitacion']'<br>'$nombre_e'
	</b>
	
	</td>";
	
	if($ejecutar->fields["iniciales"]=="")
	{
		
		$buffer.="<td rowspan='2' align='center' title='$ejecutar->fields['iniciales']'><b>Sin Lider</b></td>";
		
	}
	else 
	{
		
		$buffer.="<td rowspan='2' align='center' title=''$ejecutar->fields['iniciales']'><b> '$ejecutar->fields['iniciales']' </b></td>";
		
	}
	
while(compara_fechas($dia_ult,$dia1)!=-1)
{	list($anio,$mes,$dia)=explode("-",$dia1);
	$dia_letras=date("D",mktime(0,0,0,$mes,$dia,$anio));
	$dia_feriado=Fecha($dia1);
	if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
	{		
		if(compara_fechas($dia1,$comien)!=-1)//dia1 mayor que comien
		{
		if(compara_fechas($vence,$dia1)!=-1)	//vence mayor que dia1
		{
		
		$buffer.="<td bgcolor='#FFCC00' class='feriado' title='$lic_com'>&nbsp;</td>";
		
		}
		else 
		{
		
		$buffer.="<td class='feriado' title='$lic_com'>&nbsp;</td>";
		
		}		
		}
		else 
		{
		
		$buffer.="<td class='feriado' title='$lic_com'>&nbsp;</td>";
		
		}
	}
	
	else 
	{
		
		if($dia_actu==$dia1)
	    {		
		if(compara_fechas($dia1,$comien)!=-1)//dia1 mayor que comien
		{
		if(compara_fechas($vence,$dia1)!=-1)	//vence mayor que dia1
		{
		
		$buffer.="<td bgcolor='#FFCC00' class='actual' title='$lic_com'>&nbsp;</td>";
		
		}
		else 
		{
		
		$buffer.="<td class='actual' title='$lic_com'>&nbsp;</td>";
		
		}		
		}
		else 
		{
		
		$buffer.="<td class='actual' title='$lic_com?'>&nbsp;</td>";
		
		}
	    }
	
	else 
	{
		
		if(compara_fechas($dia1,$comien)!=-1)//dia1 mayor que comien
		{
		if(compara_fechas($vence,$dia1)!=-1)	//vence mayor que dia1
		{
		
		$buffer.="<td bgcolor='#FFCC00' title='$lic_com'>&nbsp;</td>";
		
		}
		else 
		{
		
		$buffer.="<td title='$lic_com'>&nbsp;</td>";
		
		}		
		}
		else 
		{
		
		$buffer.="<td title='$lic_com'>&nbsp;</td>";
		
		}	
	
	}
   }
	list($anio,$mes,$dia)=explode("-",$dia1);	
	$dia1=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));	
	
}
?>	
</tr>
<tr>
<?
while(compara_fechas($dia_ult,$dia2)!=-1)
{   
	$control=0;
    list($anio,$mes,$dia)=explode("-",$dia2);	
	$dia_letras=date("D",mktime(0,0,0,$mes,$dia,$anio));
	$dia_feriado=Fecha($dia2);
	$dia_che=date("Y_m_d",mktime(0,0,0,$mes,$dia,$anio));	
    $anterior=date("Y_m_d",mktime(0,0,0,$mes,$dia-1,$anio));	
	$posterior=date("Y_m_d",mktime(0,0,0,$mes,$dia+1,$anio));
	$comparar=$dia2;
	$control1=0;
	$conta=0; 
	$sumador1=$sumador;
	$si_dia=0;
	while($conta<$sumador1)
	{
		if(compara_fechas($fec_col[$conta],$comparar)==0)
		{
		$si_dia=1;
		$col_f=$conta;
		$conta=$sumador1;	
		}
		$conta++;	
	}
	if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
	{   
		if($si_dia==1)
		{
		$buffer.="<td id='td_<?=$i.'_'.$dia_che?>' bgcolor='$col_d[$col_f]'  class='feriado' onClick='alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','$i','$dia_che')' title='$lic_com'>&nbsp;";
			?>
			
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="<?=$col_d[$col_f]?>">
			
			<?	
			$buffer.="</td>";
		}
	else 
	{	
		if ($ini_compra !=NULL ||$ini_compra !="")
		{	    
		    if(compara_fechas($dia2,$ini_compra)!=-1)
			{
			if(compara_fechas($fin_compra,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
		  	 $colores="#00C823";
			$buffer.="<td id='td_<?=$i.'_'.$dia_che?>'  class='feriado' onClick='alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','$i','$dia_che')' title='$lic_com'>&nbsp;";
		  	 ?>
			
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
			
			<?
			$buffer.="</td>";
			$control1=1;
			}	
			}
		}
		if ($ini_cdr !=NULL ||$ini_cdr !="")
		{
		
			if((compara_fechas($dia2,$ini_cdr)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_cdr,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
			 $colores="#00FFFF";
			$buffer.="<td id='td_<?$i.'_'.$dia_che?>'  class='feriado' onClick='alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','$i','$dia_che')' title='$lic_com'>&nbsp;";
			 ?>
			
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
			
			<?
			$buffer.="</td>";
			$control1=1;
			}	
			}
		}
		if ($ini_entrega !=NULL ||$ini_entrega !="")
		{
			if((compara_fechas($dia2,$ini_entrega)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_entrega,$dia2)!=-1)	//vence mayor que dia1
			{
			$control=1;
			$colores="#FF00FF";
			$buffer.="<td  class='feriado' onClick='alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','$i','$dia_che')' title='$lic_com'>&nbsp;";
			?>
			
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
			
			<?
			$buffer.="</td>";
			}	
			}
		}
		if($control==0)
		{$colores="transparent";
		$buffer.="<td  class='feriado' onClick='alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','$i','$dia_che')' title='$lic_com'>&nbsp;";
		?>	
		
		<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
		
		<?
		$buffer.="</td>";	
		}
	}
	}
	
	else 
	{
		
		if($dia_actu==$dia2)
	      {   
		if ($ini_compra !=NULL ||$ini_compra !="")
		{	    
		    if(compara_fechas($dia2,$ini_compra)!=-1)
			{
			if(compara_fechas($fin_compra,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
		  	 $colores="#00C823";
			$buffer.="<td  bgcolor='#00C823' class='actual' onClick='alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','$i','$dia_che')' title='$lic_com'>&nbsp;";
		  	 ?>
			
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#00C823">
			
			<?
			$buffer.="</td>";
			$control1=1;
			}	
			}
		}
		if ($ini_cdr !=NULL ||$ini_cdr !="")
		{
		
			if((compara_fechas($dia2,$ini_cdr)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_cdr,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
			 $colores="#00FFFF";
			$buffer.="<td  bgcolor='#00FFFF' class='actual' onClick='alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','$i','$dia_che')' title='$lic_com'>&nbsp;";
			 ?>
			
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#00FFFF">
			
			<?
			$buffer.="</td>";
			$control1=1;
			}	
			}
		}
		if ($ini_entrega !=NULL ||$ini_entrega !="")
		{
			if((compara_fechas($dia2,$ini_entrega)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_entrega,$dia2)!=-1)	//vence mayor que dia1
			{
			$control=1;
			$colores="#FF00FF";
			$buffer.="<td  bgcolor='#FF00FF' class='actual' onClick='alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','$i','$dia_che')' title='$lic_com'>&nbsp;";
			?>
			
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#FF00FF">
			
			<?
			$buffer.="</td>";
			}	
			}
		 }
		if($control==0)
		{$colores="transparent";
		$buffer.="<td  class='actual' onClick='alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','$i','$dia_che')' title='$lic_com'>&nbsp;";
		?>	
		
		<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
		
		<?
		$buffer.="</td>";	
		}
	    }
	
	    else 
	    {
		
		if ($ini_compra !=NULL ||$ini_compra !="")
		{
			if(compara_fechas($dia2,$ini_compra)!=-1)
			{
			if(compara_fechas($fin_compra,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
		  	 $colores="#00C823";
			?>
			<td id="td_<?=$i.'_'.$dia_che?>" bgcolor="#00C823" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#00C823">
			</td>
			<?
			$control1=1;
			}	
			}
		}
		if ($ini_cdr !=NULL ||$ini_cdr !="")
		{
			if((compara_fechas($dia2,$ini_cdr)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_cdr,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
			 $colores="#00FFFF";
			 $buffer.="<td  bgcolor='#00FFFF'  title='$lic_com'>&nbsp;";
			 ?>
			
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#00FFFF">
			
			<?
			$buffer.="</td>";
			$control1=1;
			}	
			}
		}
		if ($ini_entrega !=NULL ||$ini_entrega !="")
		{
			if((compara_fechas($dia2,$ini_entrega)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_entrega,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
			$colores="#FF00FF";
			
			$buffer.="<td bgcolor='#FF00FF' title='$lic_com'>&nbsp;";
			?>
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#FF00FF">
			
			<?
			$buffer.="</td>";
			}	
			}
		}
		if($control==0)
		{
			$colores="transparent";
			
			$buffer.="<td  title='$lic_com'>&nbsp;";
			?>	
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
			
			<?
			$buffer.="</td>";	
		}	
	}
   }	
	list($anio,$mes,$dia)=explode("-",$dia2);	
	$dia2=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));					
	}

	
	$buffer.="
	</tr>
	<tr>
	
	<td colspan='2' class='separador' title='$lic_com'>
	
	<textarea id='co' class='achicar' name='comentario_$i' title='$lic_com' rows='1'>'$lic_com'
	</textarea>
	
	</td>";
	
	

	while(compara_fechas($dia_ult,$dia3)!=-1)
	{   
		list($anio,$mes,$dia)=explode("-",$dia3);	
	    $dia_letras=date("D",mktime(0,0,0,$mes,$dia,$anio));
	    $dia_feriado=Fecha($dia3);
	    $can_come=0;
	    if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
	     {     
		
		$buffer.="<td  class='feriado' align='center' title='$lic_com'>&nbsp;&nbsp;</td>";
		
	     }	
	     else 
	     {
	     if($dia_actu==$dia3)
	      { 
	      	  	
	     	$buffer.="<td class='actual' align='center' title='$lic_com'>&nbsp;&nbsp;</td>";
			
	      } 
	      else
	      {
			  
			 $buffer.="<td class='separador' align='center' title='$lic_com'>&nbsp;&nbsp;</td>";
			 
	     }
	     }
		 $dia3=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));		 
	}
	?>
	</tr>
	<?
/*if(($int1==8)&&($cantidad_lic>$i))
{
$int1=0;	
$contador=$cont;
$dia_ant=$dia_ant1;	
	
$buffer.="<tr>
	 <td align='center' bgcolor='$bgcolor_out'>&nbsp;</td>
	<td align='center' bgcolor='$bgcolor_out'><b>Lic/Cli</b></td>
	<td bgcolor='$bgcolor_out'><b>Lid</b></td>";
		 
	 $fondo="#cccccc";
	 $ar_mes[1]="Enero";
	 $ar_mes[2]="Febrero";
	 $ar_mes[3]="Marzo";
	 $ar_mes[4]="Abril";
	 $ar_mes[5]="Mayo";
	 $ar_mes[6]="Junio";
	 $ar_mes[7]="Julio";
	 $ar_mes[8]="Agosto";
	 $ar_mes[9]="Septiembre";
	 $ar_mes[10]="Octubre";
	 $ar_mes[11]="Noviembre";
	 $ar_mes[12]="Diciembre";
	 while($contador>=0)	
	{   
		 list($anio,$mes,$dia)=explode("-",$dia_ant);
		 $dia_letras=date("D",mktime(0,0,0,$mes,$dia,$anio));
		 $mes_letras=date("n",mktime(0,0,0,$mes,$dia,$anio));
		 $dia_feriado=Fecha($dia_ant);
		 if(($dia==01)&&($fondo=="#cccccc")) 
		 {
	     $fondo='#FFFFFF'; 
		 }
		 else 
		 {
		 if(($dia==01)&&($fondo=="#FFFFFF")) 
		 {
	     $fondo="#cccccc"; 
		 }	
		 }  
		 if($dia==01)
		 {
		 if(($dia_letras=='Sun')||(feriado($dia_feriado)))
		 {	
			 
			 $buffer.="<td bgcolor='$fondo' title='$ar_mes[$mes_letras]'><font color='Red'><b>'$dia/$mes'</b></font></td>";
			 
		 }
		 else 
		 { 
		  if($dia_actu==$dia_ant)
		 {	
			 
			 $buffer.="<td bgcolor='Blue' class='actual' title='$ar_mes[$mes_letras]'><font color='Red'>'$dia/$mes'</font></td>";
			
		 }
		 else 
		 { 	
		 	
			
			  $buffer.="<td bgcolor='$fondo' title='$ar_mes[$mes_letras]'><b>'$dia/$mes'</b></td>";
			 
		 }
		 }
		 }
		 else 
		 {
          if(($dia_letras=='Sun')||(feriado($dia_feriado)))
		 {	
			 
			 $buffer.="<td bgcolor='$fondo' title='$ar_mes[$mes_letras]'><font color='Red'>$dia</font></td>";
			 
		 }
		 else 
		 { 
		  if($dia_actu==$dia_ant)
		 {	
			
			 $buffer.="<td bgcolor='Blue' class='actual' title='$ar_mes[$mes_letras]'><b>$dia</b></td>";
			
		 }
		 else 
		 { 	
			 
			  $buffer.="<td bgcolor='$fondo' title='$ar_mes[$mes_letras]'><b>$dia</b></td>";
			
		 }
		 }
		 }   
		 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));
	     $contador--; 
	
     }    
    
$buffer.="</tr>";	
		
}*/
	
 if($pagina_ant!=1)
{
	
	
	 $des=Fecha($des);
	 $has=Fecha($has);
	 $dia_actu=date("Y-m-d",mktime());
	 $contador=diferencia_dias($des,$has);
	 $cont=diferencia_dias($des,$has);
	 $Fecha_Desde=$des;
	 $Fecha_Hasta=$has;
	 $diferencia1=diferencia_dias($Fecha_Desde, $Fecha_Hasta);
	 $des=Fecha_db($des);
	 $has=Fecha_db($has);
	 list($anio_1,$mes_1,$dia_1)=explode("-",$des);
	 list($anio,$mes,$dia)=explode("-",$has);
	 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ant1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia2=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia3=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ult=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));
	 
}
else 
{ 
	 list($anio_1,$mes_1,$dia_1)=explode("-",$des);
	 list($anio,$mes,$dia)=explode("-",$has);
	 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia2=date("y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia3=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ult=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));	
}
  	
	$i++; 
  	$ejecutar->MoveNext();
}
	
$buffer.="</table></body>
</html>";
/*</div>
<br>*/

enviar("etiqueta.doc");



//enviar("etiqueta_".$id_remito.".doc");
//echo "$buffer";
?>