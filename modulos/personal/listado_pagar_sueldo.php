<?
/*
Author: Broggi
Fecha: 11/08/2004

MODIFICADA POR
$Author: broggi $
$Revision: 1.1 $
$Date: 2004/08/31 14:24:50 $
*/


require_once("../../config.php");
echo $html_header;
variables_form_busqueda("listado_pagar_sueldo");

//print_r($_POST);



function armo_fecha($fecha)
{switch ($fecha)
    {case 1: {$cambio="Enero";
         break;
         }
     case 2: {$cambio="Febrero";
         break;
         }
     case 3: {$cambio="Marzo";
         break;
         }
     case 4: {$cambio="Abril";
         break;
         }
     case 5: {$cambio="Mayo";
         break;
         }
     case 6: {$cambio="Junio";
         break;
         }
     case 7: {$cambio="Julio";
         break;
         }
     case 8: {$cambio="Agosto";
         break;
         }
     case 9: {$cambio="Septiembre";
         break;
         }
     case 10: {$cambio="Octubre";
         break;
         }
     case 11: {$cambio="Noviembre";
         break;
         }
     case 12: {$cambio="Diciembre";
         break;
         }
   }
 return $cambio;  
}


$orden = array(
       "default" => "1",
       "1" => "apellido",
       "2" => "nombre",
       "3" => "fecha",
       "4" => "fecha_pago"
      );
$filtro = array(
       "apellido" => "Apellido",
       "nombre" => "Nombre",
       "fecha" => "Fecha"
      );
if ($cmd == "" )
   {$cmd="pendientes";}
$datos_barra = array(
                array(
                    "descripcion"    => "Pendientes",
                    "cmd"            => "pendientes"
                    ),
                array(
                    "descripcion"    => "Terminados",
                    "cmd"            => "terminados"
                    )
                 );
                 
$anio=$_POST['select_anio'];
$mes=$_POST['select_mes'];                 

generar_barra_nav($datos_barra);
$sql_tmp = "SELECT id_legajo,apellido,id_sueldo,nombre,id_sueldo,fecha_pago,fecha 
            FROM legajos join sueldos using (id_legajo)";
if($cmd == "pendientes") $where_tmp = "(estado_liquidacion=1 and (estado_pagado=0 or estado_pagado is NULL))";
if($cmd == "terminados") $where_tmp = "estado_pagado=1 and estado_liquidacion=1";
//esto es para buscar por periodo: mes y año
$mes_buscar=$mes;
$año_buscar=$anio;
if (($mes_buscar!=00)&&($año_buscar!="00")) {
     $fecha_seleccionada=$año_buscar."-".$mes_buscar;
     $where_tmp.=" and fecha like '$fecha_seleccionada-%'"; }
if ($mes_buscar!=00) $where_tmp.=" and fecha like '%-$mes_buscar-%'";
if ($año_buscar!=00) $where_tmp.=" and fecha like '$año_buscar-%'";



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

?>
<form name="listado_pagar_sueldo" action="listado_pagar_sueldo.php" method="POST">
<br>
<table align="center">
 <tr>
  <td>
  <?
   list($sql,$total_leg,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
   $resultado_consulta = sql($sql) or fin_pagina();   
  ?>
  </td>
  <td>&nbsp; <b>Periodo: &nbsp; <select name="select_mes" id="select_mes" ><?make_select_mes($mes);?></select>
   &nbsp; <b>Año:</b> &nbsp; <select name="select_anio" id="select_anio" ><?make_select_anio($anio);?></select>&nbsp; 
  </td>
  <td>
   <input type="submit" name=buscar value='Buscar'>
  </td>
 </tr>
</table>
<br>
<table width='95%' align="center" cellspacing="2" cellpadding="2" class="bordes">
 <tr id=ma>
  <td align="left" colspan="2">
   <b>Total:</b> <?=$total_leg?> <b>Liquidación/es Encontrada/s.</b>   
  </td>
  <td align="right" >
   <?=$link_pagina?>
  </td>
 </tr>
 <tr id=mo>
  <td align="center"><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'><b>Periodo Liquidación</b></a></td> 
  <td align="center"><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'><b>Apellido, Nombre</b></a></td> 
  <td align="center"><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'><b>Fecha Pago Liquidación</b></a></td> 
 </tr>
 <?
  
  while (!$resultado_consulta->EOF)
  {$fecha_separada=split("-",$resultado_consulta->fields['fecha']);
   $link = encode_link("pago_sueldo.php",array("id_personal"=>$resultado_consulta->fields['id_legajo'],"mes"=>$fecha_separada[1],"anio"=>$fecha_separada[0],
           "id_sueldo"=>$resultado_consulta->fields['id_sueldo'],"cmd"=>$cmd));   	
 ?>
 <a href='<?=$link?>' target="_blank" >
 <tr <?=atrib_tr()?>>
  <td align="center"><b><?=armo_fecha($fecha_separada[1])." ".$fecha_separada[0]?></b></td>
  <td align="center"><b><?=$resultado_consulta->fields['apellido'].", ".$resultado_consulta->fields['nombre']?></b></td>
  <td align="center"><b><?=fecha($resultado_consulta->fields['fecha_pago'])?></b></td>
 </tr>
 </a>
 <?
  $resultado_consulta->MoveNext();
  }
 ?> 
</table>

<?
fin_pagina();
?>
