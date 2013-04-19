<?
/*
$Author: diegoinga $
$Revision: 1.7 $
$Date: 2005/01/14 13:46:48 $
*/

require_once("../../config.php");
echo $html_header;

variables_form_busqueda("ver_prorrogas");

if ($cmd == "") {
	$cmd="pendientes";
	phpss_svars_set("_ses_prorrogas_cmd", $cmd);
}

$datos_barra = array(
					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "pendientes",
						 ),
					array(
						"descripcion"	=> "Historial",
						"cmd"			=> "historial"
						)
	            );

generar_barra_nav($datos_barra);

?>
<form name="form1" method="post" action="ver_prorrogas.php">
<?


echo "<table align=center cellpadding=5 cellspacing=0 >";
echo "<tr><td>";

$itemspp=50;

// Fin variables necesarias
if ($up=="") $up = "1";   // 1 ASC 0 DESC

$orden = Array (
"default" => "3",
"1" => "licitacion.id_licitacion",
"2" => "numero",
"3" => "vence_oc",
"4" => "entidad.nombre",
"5" => "fecha_estimada",
"6" => "licitacion.id_licitacion",
"7" => "nro",
"8"  =>"entrega_estimada.observaciones"
);

$filtro = Array (
"licitacion.id_licitacion" => "ID LIC",
"subido_lic_oc.nro_orden" => "NRO OC",
"vence_oc" => "Vencimiento Entrega",
"entidad.nombre" => "Cliente",
"fecha_estimada" => "Fecha Estimada ",
"entrega_estimada.observaciones" => "comentarios"

);

$sql_tmp  = "SELECT licitacion.id_licitacion,entrega_estimada.nro,entrega_estimada.id_entrega_estimada,";
$sql_tmp .= " entidad.nombre as nombre_entidad,distrito.nombre as nombre_distrito,entrega_estimada.observaciones,";
$sql_tmp .= " subido_lic_oc.nro_orden as numero,subido_lic_oc.id_subir,subido_lic_oc.vence_oc as vence,garantia_oferta.pedida,garantia_oferta.entregada,";
$sql_tmp .= " entrega_estimada.id_ensamblador,entrega_estimada.fecha_estimada,";
$sql_tmp .= "cant_renglon,cant_parcial,";
$sql_tmp .= " ensamblador.nombre,cant_orden.nro_orden";
$sql_tmp .= " ,cobranzas.nro_factura ";
$sql_tmp .= " FROM (licitaciones.licitacion LEFT JOIN licitaciones.entidad USING (id_entidad))";
$sql_tmp .= " LEFT JOIN licitaciones.distrito USING (id_distrito)";
$sql_tmp .= " LEFT JOIN licitaciones.garantia_oferta USING (id_licitacion)";
$sql_tmp .= " LEFT JOIN licitaciones.entrega_estimada USING (id_licitacion)";
$sql_tmp .= " LEFT JOIN ordenes.ensamblador USING (id_ensamblador) ";
$sql_tmp.= " LEFT JOIN (select count(nro_factura) as nro_factura,id_licitacion from licitaciones.cobranzas  group by id_licitacion) as cobranzas";
$sql_tmp.= " USING (id_licitacion) ";
$sql_tmp .= " LEFT JOIN (select licitacion.id_licitacion, count(nro_orden) as nro_orden from (licitaciones.licitacion left join compras.orden_de_compra USING (id_licitacion)) group by licitacion.id_licitacion)as cant_orden using (id_licitacion)";
$sql_tmp .= " LEFT JOIN licitaciones.subido_lic_oc using (id_entrega_estimada)";
if ($cmd=="pendientes") {
$sql_tmp .=" left join ( select id_subir,count(id_subir) as cant_renglon from licitaciones.renglones_oc group by id_subir ) 
             as total  using (id_subir)
              left join ( select id_subir,count(id_subir) as cant_parcial from licitaciones.renglones_oc where estado=1 group by id_subir ) 
             as parcial  using (id_subir)";
}
else {
$sql_tmp .=" join ( select id_subir,count(id_subir) as cant_renglon from licitaciones.renglones_oc group by id_subir ) 
             as total  using (id_subir)
              left join ( select id_subir,count(id_subir) as cant_parcial from licitaciones.renglones_oc where estado=1 group by id_subir ) 
             as parcial  using (id_subir)";

}
if ($cmd=="pendientes") {
	$where_tmp = "entrega_estimada.finalizada=0 AND borrada='f'";//estado orden de compra
  // $contar="select count(*) from licitacion join entrega_estimada using (id_licitacion) where (entrega_estimada.finalizada=0) AND borrada='f'";
}
else {
    $where_tmp = "entrega_estimada.finalizada=1 AND borrada='f'";//estado entregada
   // $contar="select count(*) from licitacion join entrega_estimada using (id_licitacion) where (entrega_estimada.finalizada=1) AND borrada='f'";
}


	
if($_POST['keyword'] || $keyword)// en la variable de sesion para keyword hay datos)
     $contar="buscar";
    

list($sql,$total,$link_pagina,$up2) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
$res_query = sql($sql) or fin_pagina();

echo "<input type=submit name='buscar' value='Buscar'>";
echo "</td>"; 
echo "</tr>\n";
echo "</table>\n";

$sql_est = "select id_estado,nombre,color from estado";
    $result = sql($sql_est) or die;
    $estados = array();//se guardan los estados que se mostraran en
    $estados[0]['color']="#FF0000";//color rojo
    $estados[0]['texto']="Orden de Compra vencida";
    $estados[1]['color']="#FF8000";//color naranja
    $estados[1]['texto']="Orden de Compra vence en 1 a 2 días";
    $estados[2]['color']="#FFFF80";//color amarillo
    $estados[2]['texto']="Orden de Compra vence en 3 a 5 días";

    
   ?>


<table border=0 width=100% cellspacing=2 cellpadding=3 >
  <tr>
  <td colspan=7 align=left id=ma> <? echo "\n";?>
	<table width=100%>
	 <tr id=ma><? echo "\n";?>
	  <td width=60% align=left><b><? echo "Total:</b> $total Seguimientos de Producción</td>\n";?>
      <td width=40% align=right><? echo $link_pagina ?></td> <? echo"\n";?>
	 </tr>
	</table> <? echo "\n";?>
  </td>
  </tr>
  <tr>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("ver_prorrogas.php",Array('sort'=>1,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>ID LIC</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("ver_prorrogas.php",Array('sort'=>7,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>N° SEG</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("ver_prorrogas.php",Array('sort'=>2,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>NRO OC</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("ver_prorrogas.php",Array('sort'=>3,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>VENCIMIENTO ENTREGA</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("ver_prorrogas.php",Array('sort'=>4,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>CLIENTE</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("ver_prorrogas.php",Array('sort'=>5,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>FECHA ESTIMADA</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("ver_prorrogas.php",Array('sort'=>6,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>STATUS</b></a></td>
  </tr>
   
 <? if ($cmd=='historial') { //busco la fecha de entraga del sueguimiento que es la fecha del ultimo renglon entregado
    $sql_fecha="select max (fecha_entrega) as fecha_finalizacion,id_subir from
				(select id_subir,fecha_entrega
 				from licitaciones.log_renglones_oc join licitaciones.renglones_oc using (id_renglones_oc)) as res
				group by id_subir";
    $res_fechas=sql($sql_fecha) or fin_pagina();
    //armo arreglo con id_subir y fecha del ultimo renglon entregado
    $fechas_vencimiento=array();
    while(!$res_fechas->EOF) {
      $fechas_vencimiento[$res_fechas->fields['id_subir']]=$res_fechas->fields['fecha_finalizacion'];
      $res_fechas->MoveNext();
    }
    }
 while (!$res_query->EOF) {
 	 $id_entrega_estimada=$res_query->fields['id_entrega_estimada'];
     $id=$res_query->fields['id_licitacion'];
     $nro=$res_query->fields['nro'];
     $oc=$res_query->fields['numero'];
     $cliente=$res_query->fields['nombre_entidad'];
     $vencimiento=$res_query->fields["vence"];
         
       //guardamos en esta variable, las observaciones de la licitacion
        //para mostrarlos en title del nombre de la licitacion
        $title_obs=$res_query->fields["observaciones"];

        //LIMITAR OBSERVACIONES: controlamos el ancho y la cantidad de
        //lineas que tienen las observaciones y cortamos el string si
        //se pasa de alguno de los limites
        $long_title=strlen($title_obs);
        //cortamos si el string supera los 600 caracteres
        if($long_title>600)
           {
            $title_obs=substr($title_obs,0,600);
            $title_obs.="   SIGUE >>>";
           }
        $count_n=str_count_letra("\n",$title_obs);
        //cortamos si el string tiene mas de 12 lineas
        if($count_n>12)
           {
            $cn=0;$j=0;
            for($i=0;$i<$long_title;$i++)
              {
              if($cn>12)
                    $i=$long_title;
              if($title_obs[$i]=="\n")
                    $cn++;
              $j++;
              }
          $title_obs=substr($title_obs,0,$j);
          $title_obs.="   SIGUE >>>";
          }
        //FIN DE LIMITAR OBSERVACIONES

     
        $ma = substr($res_query->fields["vence"],5,2);
        $da = substr($res_query->fields["vence"],8,2);
        $ya = substr($res_query->fields["vence"],0,4);
    $fecha_vencimiento=fecha($res_query->fields['vence']);
  if ($cmd=='pendientes') {
        /*mostramos un color de acuerdo al vencimiento de la orden de compra externa
     Rojo=orden vencida
     Naranja=2 dias habiles para vencimiento de la orden
     Amarillo=5 dias habiles para vencimiento de la orden
     Verde oscuro=faltan mas de 5 dias para vencimiento de la orden
    */
  
    $fecha_hoy1 = date("d/m/Y",mktime());
    if ((compara_fechas(fecha_db($fecha_hoy1),fecha_db($fecha_vencimiento)) >= 0))//la fecha actual es mayor a la vencida
       {$color_state="#FF0000";//color rojo
        $texto_state="La orden de compra esta vencida"; //ya vencio la orden
       }
     else
      { 
     	switch(diferencia_dias_habiles($fecha_hoy1,$fecha_vencimiento)){
     	  case 1:
          case 2:$color_state="#FF8000";//color naranja
                 $texto_state="Faltan de 1 a 2 dias para el vencimiento de la orden";break; //1 o 2 dias habiles para vencer
         case 3:
         case 4:
         case 5:$color_state="#FFFF80";//color amarillo
                $texto_state="Faltan de 3 a 5 dias para vencimiento de la orden"; break;//3, 4 o 5 dias habiles para vencer
         default:$color_state="#FFFFFF";//color blanco
                $texto_state="";
                break;
        }
      }
  } 
  else {   //muestra en historial
    /*si la entrega se realizo despues del vencimiento de oc color rojo
    si la entrega se realizo antes del vencimiento de oc color verde
     */
     $id_subir=$res_query->fields['id_subir'];
     $fecha_finalizacion=$fechas_vencimiento[$id_subir];
    if ($fecha_finalizacion) 
       $comp=compara_fechas(fecha_db($fecha_vencimiento),$fecha_finalizacion);
    else $comp=2;   //si no tengo fecha de finalizacion 
  
    if ($comp==1 || $comp==0) {
     $color_state="#00CC66";//color verde
     $texto_state="Fecha finalización ".Fecha($fecha_finalizacion);
    }
    elseif($comp ==-1)  {
        $color_state="#FF0000";//color rojo
        $texto_state="Fecha Finalización ".Fecha($fecha_finalizacion);
    }
    else {
    $color_state="";
    $texto_state="";
    } 
   
  }
  
  
  
    //tr_tag($ref,"title='Haga click para facturar el seguimiento'");
     
     
   //control de prorrogas
   $sql="select id_prorroga from prorroga where id_entrega_estimada = $id_entrega_estimada";
   $result_prorroga=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
   
   $ref=encode_link('prorrogas.php',array("id_prorroga"=>$result_prorroga->fields['id_prorroga'],"nro_orden_cliente"=>$res_query->fields['numero'],"id_entrega_estimada"=>$res_query->fields['id_entrega_estimada']));
   
  ?>
  <tr title="<?=$title_obs?>" <? /*echo ($result_prorroga->fields['id_prorroga']!="")?'bgcolor="#33CC99"':$atrib_tr;*/echo $atrib_tr;?>>
    <a href="<?=$ref?>" target="_blank">
    <td align="center" style="cursor:hand" bgcolor="<?=$color_state?>" title="<?=$texto_state?>"><? echo $res_query->fields['id_licitacion'] ?></td> 
	<td align="center" style="cursor:hand"><?=$res_query->fields['nro'] ?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['numero'] ?></td>
    </a>
     <a href="<?=$ref?>" target="_blank">
	<td align="center" style="cursor:hand"><?=Fecha($res_query->fields['vence'])?>
	<?
	 $link=encode_link('cambia_fecha_vencimiento.php',array("fecha"=>Fecha($res_query->fields['vence']),"id"=>$res_query->fields["id_entrega_estimada"]));
     $onclick="ventana=window.open('$link','','left=40,top=80,width=700,height=300,resizable=1,status=1')";
     
     if (permisos_check('inicio','cambiar_fecha'))
             {
           ?>	</a>
              <input name='cambiar' type='button' value='C' onclick="<?=$onclick?>">
           <?
             }   ?> 
	</td>
	  <a href="<?=$ref?>">
    <td align="center" style="cursor:hand"><?=$res_query->fields['nombre_entidad'] ?></td>
    <td align="center" style="cursor:hand"><?=Fecha($res_query->fields['fecha_estimada']) ?></td>
    <? if ($res_query->fields['cant_renglon']) $total=$res_query->fields['cant_renglon'];
         else $total=0;
       if ($res_query->fields['cant_parcial']) $parcial=$res_query->fields['cant_parcial'];
         else $parcial=0;
         ?>
    <td align="center" style="cursor:hand"><?=$total."/".$parcial?></td>
    </a>
    </tr>
  <? 		
   $res_query->MoveNext();
   } ?>
   
</table>
<br>

<?if ($cmd=='pendientes') {
    echo "<table align='center' border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='98%' cellspacing=0 cellpadding=0>\n";
    echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia:</b></td></tr>\n";
    echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Los colores estan reflejados en el campo de ID de la orden</b></td></tr>\n";
    echo "<tr>\n";
    $cont=0;
    foreach ($estados as $est => $arr) {
    if (!($cont % 3)) { echo "</tr><tr>"; }
        echo "<td width=30% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%><tr>";
        echo "<td width=15 bgcolor='".$estados[$est]["color"]."' bordercolor='#000000' height=15>&nbsp;</td>\n";
        echo "<td bordercolor='#FFFFFF'>".$estados[$est]["texto"]."</td>\n";
        echo "</tr></table></td>";
       $cont++;
    }
    
    
}
else {
	 echo "<table align='center' border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='98%' cellspacing=0 cellpadding=0>\n";
    echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia:</b></td></tr>\n";
    echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Los colores estan reflejados en el campo de ID de la orden</b></td></tr>\n";
    echo "<tr>\n";
   
        echo "<td width=30% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%><tr>";
        echo "<td width=15 bgcolor='#00CC66' bordercolor='#000000' height=15>&nbsp;</td>\n";
        echo "<td bordercolor='#FFFFFF'> La entrega se realizó en termino</td>\n";
         echo "<td width=15 bgcolor='#FF0000' bordercolor='#000000' height=15>&nbsp;</td>\n";
        echo "<td bordercolor='#FFFFFF'> La entrega se realizó fuera de termino</td>\n";
       
        echo "</tr></table></td>";
        
      
}
    
?>

</form>
</html>