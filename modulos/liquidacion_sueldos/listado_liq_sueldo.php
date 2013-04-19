<?php
/*
$Author: fernando $
$Revision: 1.25 $
$Date: 2005/11/29 22:40:46 $
*/

require_once ("../../config.php");
require_once("funciones.php");
variables_form_busqueda("liq_sueldo");

//si agrego esto en variables_form_busquada no funciona, no hace las consultas
//, array("liq_sueldo_mes"=>$_POST['mes_buscar'], "liq_sueldo_año"=>$_POST['año_buscar'])
/*

if($_POST['mes_buscar'] && $_ses_global_liq_sueldo_mes!=$_POST['mes_buscar'])
         phpss_svars_set("_ses_global_liq_sueldo_mes", $_POST['mes_buscar']);
if($_POST['mes_buscar'] && $_ses_global_liq_sueldo_año!=$_POST['año_buscar'])
          phpss_svars_set("_ses_global_liq_sueldo_año", $_POST['año_buscar']);
 
if ($mes_buscar=="")
        $mes_buscar=$_ses_global_liq_sueldo_mes; 
if ($año_buscar=="")
        $año_buscar=$_ses_global_liq_sueldo_año;   
  
//agrego esto para probar si quedan marcados los select 
//de mes y año del libro de sueldo  
if($_POST['mes'] && $parametros["mes"] && $_ses_global_liq_sueldo_mes!=$_POST['mes'])
        phpss_svars_set("_ses_global_liq_sueldo_mes", $_POST['mes']);
        
if($_POST['año'] && $parametros["año"] && $_ses_global_liq_sueldo_año!=$_POST['año'])
        phpss_svars_set("_ses_global_liq_sueldo_año", $_POST['año']);
 
if ($mes=="")
      $mes=$_ses_global_liq_sueldo_mes; 
if ($año=="")
      {
      if (!$_ses_global_liq_sueldo_año)
                    phpss_svars_set("_ses_global_liq_sueldo_año", date("Y"));
      $año=$_ses_global_liq_sueldo_año; 
      
          
      }
      
echo "año:".$_ses_global_liq_sueldo_año;      
//////

*/


if($_ses_global_liq_sueldo_mes!=$_POST['mes_buscar'] && $_POST["mes_buscar"])
         phpss_svars_set("_ses_global_liq_sueldo_mes", $_POST['mes_buscar']);
         
if( $_ses_global_liq_sueldo_año!=$_POST['año_buscar'] && $_POST["año_buscar"])
         phpss_svars_set("_ses_global_liq_sueldo_año", $_POST['año_buscar']);
 
if($_ses_global_liq_sueldo_mes_libro_sueldo!=$_POST['mes_libro_sueldo'] && $_POST['mes_libro_sueldo'])
         phpss_svars_set("_ses_global_liq_sueldo_mes_libro_sueldo", $_POST['mes_libro_sueldo']);
         
if( $_ses_global_liq_sueldo_año_libro_sueldo!=$_POST['año_libro_sueldo'] && $_POST['año_libro_sueldo'])
         phpss_svars_set("_ses_global_liq_sueldo_año_libro_sueldo", $_POST['año_libro_sueldo']);





if (!$_ses_global_liq_sueldo_año)
              $_ses_global_liq_sueldo_año=date("Y");
              
if (!$_ses_global_liq_sueldo_mes)
              $_ses_global_liq_sueldo_mes=date("m"); 
              
if (!$_ses_global_liq_sueldo_año_libro_sueldo)
              $_ses_global_liq_sueldo_año_libro_sueldo=date("Y");
              
if (!$_ses_global_liq_sueldo_mes_libro_sueldo)
              $_ses_global_liq_sueldo_mes_libro_sueldo=date("m");               
                            

                          


$mes_buscar=$_ses_global_liq_sueldo_mes; 
$año_buscar=$_ses_global_liq_sueldo_año; 

$mes_libro_sueldo=$_ses_global_liq_sueldo_mes_libro_sueldo;
$año_libro_sueldo=$_ses_global_liq_sueldo_año_libro_sueldo;
      


if ($_POST['libro_sueldo']=="Libro de Sueldo"){
             if ($mes_libro_sueldo<10)
                {         
                $fecha_seleccionada=$año_libro_sueldo."-0".$mes_libro_sueldo;
                $fecha_seleccionada2=$año_libro_sueldo."-0".$mes_libro_sueldo."-31";
                }
                else
                    {
                    $fecha_seleccionada=$año_libro_sueldo."-".$mes_libro_sueldo;
                    $fecha_seleccionada2=$año_libro_sueldo."-".$mes_libro_sueldo."-31";
                        
                    }
                
             $fecha_seleccionada2=date("Y-m-d",mktime(0,0,0,$mes_libro_sueldo+1,"01",$año_libro_sueldo));
             
             //con esto obtengo la cantidad de liquidaciones para el periodo elegido
             $sql="select count (id_legajo) as liquidaciones 
                        from personal.sueldos 
                        left join personal.legajos using (id_legajo)
                        where tipo_liq=1 and estado_liquidacion=1 and fecha like '$fecha_seleccionada%'";
             $resultado2=sql($sql) or fin_pagina();
             $cant_liq=$resultado2->fields['liquidaciones'];
             
             //con esto obtengo la cantidad de legajos que hay, para compararlo con la cantidad
             //de liquidaciones que hay hechas para un periodo elegido
             $sql="select count (id_legajo) as cantidad from personal.legajos
                       where (fecha_baja is null or fecha_baja>'$fecha_seleccionada2') 
                       and fecha_ingreso<'$fecha_seleccionada2' and tipo_liq=1";
             $resultado=sql($sql) or fin_pagina();
             $cant_leg=$resultado->fields['cantidad'];
             if ($cant_liq < $cant_leg){
              $mensaje_error="<font  size=2><center><b>No se puede generar el Libro de Sueldos, faltan liquidaciones para el período elegido</b></center></font>";
              $abrir_ventana=0;
                 }    //fin del if por cantidad de legajos y liquidaciones
              else {
                 if ($cant_liq > $cant_leg){
                 $mensaje_error="<font  size=2><center><b>No se puede generar el Libro de Sueldos, hay mas liquidaciones para un empleado en el período elegido</b></center></font>";
                 $abrir_ventana=0;
                     }
                 else {
                 if ($mes_libro_sueldo<10)
                                $mes_libro_sueldo_aux="0".$mes_libro_sueldo;
                                else
                                $mes_libro_sueldo_aux=$mes_libro_sueldo;
                     
                 $link=encode_link("libro_sueldo.php",array ("mes"=>$mes_libro_sueldo_aux,"año"=>$año));
                 ?>
                 <script>
                 window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=0,resizable=1');
                 </script>
                 <? }  //fin del else para el encode_link
                 } //fin del else para el if por post libro sueldo
                 if ($mes_libro_sueldo<10)
                                $mes_libro_sueldo_aux="0".$mes_libro_sueldo;
                                else
                                $mes_libro_sueldo_aux=$mes_libro_sueldo;
                                
                 $link=encode_link("libro_sueldo.php",array ("mes"=>$mes_libro_sueldo_aux,"año"=>$año_libro_sueldo));
                 ?>
                 <script>
                 window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=0,resizable=1');
                 </script>
                 <?
                 
       } //fin del if por post libro de sueldo


echo $html_header;

$orden = array(
        "default" => "1",
        "1" => "apellido",
        "2" => "nombre",
        "3" => "fecha"
        );
$filtro = array(
        "apellido" => "Apellido",
        "nombre" => "Nombre",
        "fecha" => "Fecha"
        );
if (!$cmd)
          $cmd="pendientes";
          
$datos_barra = array(
                 array(
                    "descripcion"    => "Pendientes",
                    "cmd"            => "pendientes",
                    "extra"=>array("mes"=>$_ses_global_liq_sueldo_mes,"año"=>$_ses_global_liq_sueldo_año)
                     ),
                 array(
                    "descripcion"    => "Terminados",
                    "cmd"            => "terminados",
                    "extra"=>array("mes"=>$_ses_global_liq_sueldo_mes,"año"=>$_ses_global_liq_sueldo_año)
                     )
                 );
                 
                 

generar_barra_nav($datos_barra);

    $sql_tmp = "SELECT id_legajo,apellido,nombre,id_sueldo,fecha_pago,fecha 
                FROM legajos join sueldos using (id_legajo)";

                
    if($cmd == "pendientes") 
                           $where_tmp = "(estado_liquidacion = 0 or estado_liquidacion is NULL) ";
    if($cmd == "terminados")
                           $where_tmp = "estado_liquidacion = 1";
    //esto es para buscar por periodo: mes y año
    
    
    
    

        
        if ($mes_buscar<10 && $mes_buscar<>"00")
          $fecha_seleccionada=$año_buscar."-0".$mes_buscar;
          elseif($mes_buscar>=10  && $mes_buscar<>"00")
          $fecha_seleccionada=$año_buscar."-".$mes_buscar;
          elseif($mes_buscar=="00")
                $fecha_seleccionada=$año_buscar;
          
      
         $where_tmp.=" and fecha ilike '$fecha_seleccionada-%'"; 

                     
    ?>
    <form name=form1 action="listado_liq_sueldo.php" method=POST>
    <?
    echo "<table cellspacing=2 cellpadding=5 border=0 bgcolor=$bgcolor3 width=100% align=center>\n";
    echo "<tr><td align=center>\n";
    echo "<table cellspacing=2 cellpadding=5 border=0 bgcolor=$bgcolor3 width=100% align=center>\n";
    echo "<tr> <td>";
    list($sql,$total_leg,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
    ?>
    </td>
    <td><b>Período:</b></td>
    <td><select name='mes_buscar'>
        <option value='00' selected>Todos</option>
           <?
           for ($i=1;$i<sizeof($meses);$i++){
               ?>
               <option value='<?=$i?>' <? if ($mes_buscar==$i) echo 'selected' ?>><?=$meses[$i]?></option>
               <?
            }
           ?>
        </select>
      </td>
      <td><b>Año:</b></td>
      <td>
      <select name='año_buscar'>
           <?
           for($i=2003;$i<2013;$i++){
           ?>
           <option value='<?=$i?>' <? if ($año_buscar==$i) echo 'selected' ?>><?=$i?></option>
           <?
           }
           ?>            
       </select>
       </td>
       <td><input type=submit name=buscar value='Buscar'></td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    <?
    echo $mensaje_error;
    $result = sql($sql) or fin_pagina();
    ?>
    <table border=0 width=95% cellspacing=2 cellpadding=3 bgcolor=<?=$bgcolor3?> align=center>
    <tr>
    <td colspan=4 align=left id=ma>
       <table width=100%>
           <tr id=ma>
             <td width=30% align=left><b>Total:</b><?= $total_leg ?>legajo/s.</td>
             <td width=70% align=right><?=$link_pagina?></td>
           </tr>
       </table>
    </td>
    </tr>
    <tr>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_liq_sueldo.php",array("sort"=>"4","up"=>$up,"mes_buscar"=>$mes_buscar,"año_buscar"=>$año_buscar))?>'>Período Liquidación</a>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_liq_sueldo.php",array("sort"=>"1","up"=>$up,"mes_buscar"=>$mes_buscar,"año_buscar"=>$año_buscar))?>'>Apellido, </a>
                          <a id=mo href='<?=encode_link("listado_liq_sueldo.php",array("sort"=>"2","up"=>$up,"mes_buscar"=>$mes_buscar,"año_buscar"=>$año_buscar))?>'>Nombre</td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_liq_sueldo.php",array("sort"=>"3","up"=>$up,"mes_buscar"=>$mes_buscar,"año_buscar"=>$año_buscar))?>'>Fecha Pago Liquidación</td>
    </tr>
    <?
    while (!$result->EOF) {
        $ref = encode_link("liq_sueldo.php",array("id_sueldo"=>$result->fields['id_sueldo'],"pagina"=>"listado_liq_sueldo.php"));
        tr_tag($ref);
        list($año, $mes, $dia)=split('[-]', $result->fields['fecha']);
        $m=cambiar($mes);
    ?>        
        <td align=center width=20%><b><? echo $m." ".$año ?></td>
        <td align=left width=45%><b><? echo $result->fields["apellido"].", ".$result->fields["nombre"]?></td>
        <td align=center width=35%><b><? echo fecha($result->fields["fecha_pago"]) ?></td>
    </tr>
    <?
        $result->MoveNext();
    }
    ?>
    </table>
    <?
    if ($cmd=="terminados"){
    ?>
    <table cellspacing=2 cellpadding=5 border=0 bgcolor=<?=$bgcolor3?> width=60% align=center>
    <tr><td></td></tr>
    <tr>
           <td><b>Período:</b></td>
           <td><select name='mes_libro_sueldo'>
                   <?
                   for ($i=1;$i<sizeof($meses);$i++){
                       ?>
                       <option value='<?=$i?>' <? if ($mes_libro_sueldo==$i) echo 'selected' ?>><?=$meses[$i]?></option>
                       <?
                    }
                   ?>
           </select>
          </td>
          <td><b>Año:</b></td>
          <td><select name='año_libro_sueldo'>
                   <?
                   for($i=2003;$i<2013;$i++){
                   ?>
                   <option value='<?=$i?>' <? if ($año_libro_sueldo==$i) echo 'selected' ?>><?=$i?></option>
                   <?
                   }
                   ?>
           </select>
           </td>
           <td>
           <input type=submit name='libro_sueldo' value='Libro de Sueldo' title='Vista previa Libro de Sueldo'>
           </td>
    </tr>
    </table>
    <?
    }
    ?>

</form>
<?
fin_pagina();
?>