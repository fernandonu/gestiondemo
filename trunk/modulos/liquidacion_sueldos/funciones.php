<?php
/*
$Author: nazabal $
$Revision: 1.11 $
$Date: 2007/05/04 21:56:45 $
*/ 
function cambiar ($m){
switch ($m){
        case 1: 
                 $cambio="Enero";
                 break;
                
        case 2: 
                $cambio="Febrero";
                 break;
                
        case 3:  
                 $cambio="Marzo";
                 break;
                 
        case 4: 
                $cambio="Abril";
                 break;
                
        case 5: 
                $cambio="Mayo";
                 break;
                
        case 6: 
                 $cambio="Junio";
                 break;
                
        case 7: 
                 $cambio="Julio";
                 break;
                 
        case 8: 
                 $cambio="Agosto";
                 break;
                
        case 9:  
                 $cambio="Septiembre";
                 break;
                 
        case 10: 
                 $cambio="Octubre";
                 break;
                 
        case 11: 
                 $cambio="Noviembre";
                 break;
                 
        case 12: 
                 $cambio="Diciembre";
                 break;
                 
           }
return $cambio;
}


function setea_datos($dato){
  if ($dato == "0") $dato="0.00";
  if($dato!="")
	$return=number_format($dato,2,".","");
  return $return;
} 


function calculo_valores($datos){
     global $fecha, $fecha_pago;

     $subtotal_horas = 0;
     $valor_hora_extra = 0;
     if ($datos["sueldo_por_horas"]) {
        $subtotal=$datos["horas"] * $datos["valor_hora"];
		if ($datos["horas_extras"]) {
		   $valor_hora_extra = $datos["valor_hora"] * $datos["tipo_hora_extras"];
      	   $subtotal_horas=$datos["horas_extras"] * $valor_hora_extra; 
		}
     }
     else {
        if ($fecha_pago>$fecha)
           $subtotal = $datos['basico']+$datos['dec1529']+$datos['dec1347'];
        else
           $subtotal = $datos['basico']+$datos['dec1529'];
        if ($datos["horas_extras"]) {
          	$valor_hora_extra=(($subtotal+$datos['acuenta'])/200) * $datos["tipo_hora_extras"];
			$subtotal_horas=$datos["horas_extras"] * $valor_hora_extra; 
		}
     }       

     $subtotal2 = $datos['presentismo'] + $subtotal + $subtotal_horas + $datos['acuenta'] + $datos["ajuste_anterior"];

     
     $total = $subtotal2+$datos['vacaciones']+$datos['ausentismo'] + $datos["feriados"];
     //esto es para calcular el sac   -sac no tengo en cuenta inasistencia ni vacaciones
     $mes_sac = $datos['mes_sac'];
     $dias_sac = $datos['dias_sac'];
     $mxd_sac = $mes_sac*30;
     $dias_trab = $mxd_sac+$dias_sac;
     $arreglo['sac'] = (($total/2)/180)*$dias_trab;
     
     
     //este muestra el total del sac
     $total-= $datos['total_inasistencia'];
     $total+= $datos['total_vac'];
     
     $total_desc=$datos['jubilacion']+$datos['ley19032'];
     $total_desc+=$datos['obra_social']+$datos['sindicato'];
     $total_desc+=$datos['sindicato_familiar']+$datos['faecys'] + $datos['embargo'];
     if ($fecha_pago>$fecha)
             $total_no_rem=$datos['salario_familiar']+$datos['ayuda_escolar'];
              else
             $total_no_rem=$datos['salario_familiar']+$datos['dec1347']+$datos['ayuda_escolar'];
     $total_no_rem+=$datos['gratificacion']+$datos['vac_no_gozadas'];
     $total_no_rem+=$datos['pre_aviso']+$datos['indemnizacion']+$datos['sac_sobre_vng'];
     $total_no_rem+=$datos['sac_sobre_preaviso']+$datos['dec2005_04']+$datos['sac_sobre_indemnizacion'];
     $total_no_rem+=$datos["acuerdo_abril_2006"];
     //tengo que agregarle lo del sac que si no se marca es cero
     $neto=$neto+$arreglo['sac'];
     $arreglo['subtotal']=$subtotal;
     $arreglo['subtotal2']=$subtotal2;
     $arreglo['subtotal_horas']=$subtotal_horas;
     $arreglo['valor_horas_extras']=$valor_hora_extra;
    
     
     $anticipo=$datos['anticipo'];
     if ($anticipo) $total_no_rem=$total_no_rem-$anticipo;
     
     $arreglo['total']=$total+$arreglo['sac'] ;
     $arreglo['total_desc']=$total_desc;
     $arreglo['total_no_rem']=$total_no_rem;                
     
     $arreglo['neto']=$arreglo['total'] - $arreglo['total_desc'] + $arreglo['total_no_rem'];           
     
     
     return $arreglo;
}


?>