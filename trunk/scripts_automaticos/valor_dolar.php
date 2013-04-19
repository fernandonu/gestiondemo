<?
/*
AUTOR: ???
MODIFICADO POR:
$Author: nazabal $
$Revision: 1.2 $
$Date: 2006/02/20 19:59:25 $
*/

require_once("funciones_generales.php");

function retorna_indice($array,$campo) {
$cant=count($array);
for($i=0;$i<$cant;$i++) {
   if ($array[$i]['fecha']==$campo) return $i;
}
}//function retorna_indice


//formato de fecha dd/mm/año
function dia_anterior($fecha){
$fecha_aux=$fecha;
$dia_anterior=0;

while(!$dia_anterior) {
  $fecha_total=split("/",$fecha_aux);
  $dfecha=date("d/m/Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-1,$fecha_total[2]));
  $fecha_aux=date("d/m/Y/w",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-1,$fecha_total[2]));
  $fecha_test=split("/",$fecha_aux);
  if($fecha_test[3]!=0 || $fecha_test[3]!=6)
      $dia_anterior=1;
}
$fecha_retornar=split("/",$fecha_aux);
$a=date("d/m/Y",mktime(0,0,0,$fecha_retornar[1],$fecha_retornar[0],$fecha_retornar[2]));
return $a; 
}


/* calcula el proximo dia 
argumento $fecha es dd/mm/aaaa */
function dia_posterior($fecha){
$fecha_aux=$fecha;
$feriado=0;
  $fecha_total=split("/",$fecha_aux);
  $dfecha=date("d/m/Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0]+1,$fecha_total[2]));
  $fecha_aux=date("d/m/Y/w",mktime(0,0,0,$fecha_total[1],$fecha_total[0]+1,$fecha_total[2]));
  $fecha_test=split("/",$fecha_aux);

$fecha_retornar=split("/",$fecha_aux);
$a=date("d/m/Y",mktime(0,0,0,$fecha_retornar[1],$fecha_retornar[0],$fecha_retornar[2]));
return $a; 
}


$fecha_actual=date("d/m/Y");
$anio_inicial=1999; 
$anio_final=2020;
$mes=date("n");
$anio=date("Y");
$numero_dias=date("t",mktime(0,0,0,$mes,1,$anio));  //numero de dias del mes seleccionado
//$numero_dias=5;
//echo $numero_dias;

$comienzo_mes= date('w', mktime(0,0,0,$mes,1,$anio));  //primer dia del mes "0" (domingo) a "6" (sábado)
$fin_mes=date('w', mktime(0,0,0,$mes,$numero_dias,$anio));  //ultimo dia del mes "0" (domingo) a "6" (sábado)

if ($fin_mes==0) $dias_posteriores=$fin_mes;
 else 
   $dias_posteriores=5-$fin_mes;

$dias_semana=array();
$dias_semana[1]='Lunes';
$dias_semana[2]='Martes';
$dias_semana[3]='Miercoles';
$dias_semana[4]='Jueves';
$dias_semana[5]='Viernes';

if ($mes < 10 ) $m='0'.$mes;
  else $m=$mes;

$fecha='01/'.$m.'/'.$anio;

$j=$comienzo_mes;//numero de dia en el que comienza el mes "0" (domingo) a "6" (sábado)
   $i=1;
   if ($j==6) { //es sabado
    	$j=1;
    	$i=3;
    	$restar=2;
    	$fecha_inicio='03/'.$m.'/'.$anio;
    } elseif ($j==0) { //es domingo
         $j++;
    	 $i=2;
    	 $fecha_inicio='02/'.$m.'/'.$anio;
    	 $restar=1;
    }
    else {
    	 $fecha_inicio='01/'.$m.'/'.$anio;
    	 $restar=0;
    }

    if ($mes < 10 ) $m='0'.$mes;
      else $m=$mes;
  
   $dias_anteriores=0;
   $fecha_ant="";
   for($ind=$j-1;$ind >= 1;$ind--) {
      $fecha_ant=dia_anterior($fecha);
      $fecha=$fecha_ant;
      $dias_anteriores++;
   }
  if ($fecha_ant !="") $fecha_inicio=$fecha_ant;
 
  $array_fechas=array();
  
  for ($ind=0; $ind <25;$ind++) {
  	$fec=split("/",$fecha); 
    $num=date('w', mktime(0,0,0,$fec[1],$fec[0],$fec[2]));  //dia  "0" (domingo) a "6" (sábado)
    while ($num==0 || $num==6) {
        $fecha=dia_posterior($fecha);
        $fec=split("/",$fecha); 
        $num=date('w', mktime(0,0,0,$fec[1],$fec[0],$fec[2]));  //dia  "0" (domingo) a "6" (sábado)
        
    }
     $array_fechas[$ind]['fecha']=$fecha; 
     $array_fechas[$ind]['num']=$dias_semana[$num];
     $array_fechas[$ind]['dolar']="";
     $array_fechas[$ind]['comentario']="";
     $fecha=dia_posterior($fecha);
  }        
   
   $cant=count($array_fechas);
   $list='(';
   for($i=0;$i<$cant;$i++){
      $list.="'".fecha_db($array_fechas["$i"]['fecha'])."'".',';
      }
   $list=substr_replace($list,')',(strrpos($list,',')));
   $sql="select valor_dolar,fecha,comentario from dolar_comparacion where fecha in $list";
   $res=$db->execute($sql) or die($sql);
   while (!$res->EOF) {
   $i=retorna_indice($array_fechas,fecha($res->fields['fecha']));  
   $array_fechas[$i]['dolar']=$res->fields['valor_dolar'];
   $array_fechas[$i]['comentario']=$res->fields['comentario'];
   $res->MoveNext();
   }

function busca_segundo_nivel($arreglo,$indice,$argumento)
{
 $cant=count($arreglo);
 $indi=0;
 $control=1;
 while ($control && $indi<$cant)
       {if ($arreglo[$indi][$indice]==$argumento)
           {$control=0;
            return $indi;           	
           }
        $indi++;   	
       }
 return false;      	
}//function busca_segundo_nivel
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$cuerpo="<html>
<body>
<br>
<table align=center>
<tr>
<td> 
<b><u>Valores Dolar cargados durante la semana.</u></b>
</td>
</tr>
</table>
<br>
<table align='center' border='1'>
 <tr > 
  <td> DIA </td>
  <td> VALOR </td>    
 </tr>
";
  for ($j=busca_segundo_nivel($array_fechas,'fecha',$fecha_actual)-4;$j<=busca_segundo_nivel($array_fechas,'fecha',$fecha_actual);$j++) 
 { 
 $cuerpo.="<tr>
  <td> {$array_fechas[$j]['num']} ".substr($array_fechas[$j]['fecha'],0,5)."</td>
  <td> <input type='text' value='".number_format($array_fechas[$j]['dolar'],'2','.','')."' size=4 ></td></tr>";  
 }       
$cuerpo.="</table>
<br>
<br>".(firma_coradir_mail())."
</body>
</html>";
$para="juanmanuel@coradir.com.ar,noelia@coradir.com.ar";
//$para="nazabal@coradir.com.ar";
enviar_mail_html($para,"Valor Dolar",$cuerpo,"","","");
