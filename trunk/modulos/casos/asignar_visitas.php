<?
/*
$Author: fernando $
$Revision: 1.67 $
$Date: 2005/05/06 16:05:50 $

*/
require_once("../../config.php");
include("../caja/func.php");


$fecha_actual=date("d/m/Y",mktime());

$id_caso=$parametros['id_caso'] or $id_caso=$_POST['id_caso'] ;
$nro_caso=$parametros['nro_caso'] or $nro_caso=$_POST['nro_caso'];
$id_visita_asignada=$parametros['id_visita_asignada'] or $id_visita_asignada=$_POST['id_visita_asignada'];  //el id de visita para reasignar tecnico/hora
$reasigna=$parametros['reasigna'] or $reasigna=$_POST['reasigna'];
$pagina=$parametros["pagina"] or $pagina=$_POST["pagina"];
$volver=$parametros["volver"] or $volver=$_POST["volver"];
$viene=$parametros['viene'] or $viene=$_POST['viene'];
$msg=$parametros['msg'];

/*if($_POST['datos']) {
$list=$_POST['list'];

$where="";
if ($_POST['fechas']) {
  $fecha_ini=fecha_db($_POST['f_ini']);
  $fecha_fin=fecha_db($_POST['f_fin']);
  $where=" and fecha_visita >='$fecha_ini' and fecha_visita <= '$fecha_fin' ";
}
if ($_POST['check_tecnico']) {
  $cant=count($_POST['tecnicos']);	
  if ($cant > 0) {
   $where.=" and";
   $tecnicos=$_POST['tecnicos'];
   $where.=" (";
   for($i=0;$i<$cant;$i++) {
   $where.=" id_tecnico_visita=".$tecnicos[$i];
   if ($i< $cant-1) $where.=" or ";
   }
   $where.=")";
  }
}
if ($_POST['check_estado']) {
  	
  $cant=count($_POST['estado']);
  if ($cant > 0) {
  $where.=" and";
  $estados=$_POST['estado'];
  $where.=" (";
  for($i=0;$i<$cant;$i++) {
  $where.=" id_estado_visita=".$estados[$i];
  if ($i< $cant-1) $where.=" or ";
  }
  $where.=")";
  $estado_visita=comprimir_variable($estados);
  
  }
}
else {
  	 $where.=" and id_estado_visita in $list  ";
  	 $l=strlen($list)-2;
  	 $list_e=substr($list,1,$l);
     $est=explode(",",$list_e);
     $estado_visita=comprimir_variable($est);
     
}
  
  $sql="select count(id_estado_visita) as cantidad,id_tecnico_visita,
        id_estado_visita,nombre,apellido
        from
        (select id_visitas_casos,id_tecnico_visita,nombre,apellido,fecha_visita,id_estado_visita 
         from casos.visitas_casos join casos.tecnicos_visitas using (id_tecnico_visita) join (select id_estado_visita,id_visitas_casos 
         from casos.log_casos_visitas join (select max(id_log_casos_visitas) as id_log_casos_visitas 
         from casos.log_casos_visitas group by id_visitas_casos) as res using (id_log_casos_visitas) ) as r using (id_visitas_casos)
         where estado = 'Historial'";
  $sql.=" $where";
  
  $sql.=" order by id_tecnico_visita,id_estado_visita) as r2
         group by id_estado_visita,id_tecnico_visita,nombre,apellido
         order by id_tecnico_visita,id_estado_visita";
 
    $link=encode_link('visitas_estadisticas_imprimir.php',array("sql"=>$sql,"fecha_ini"=>$fecha_ini,"fecha_fin"=>$fecha_fin,"estados"=>$estado_visita));
    ?>
    <script>
     window.open('<?=$link?>');
     window.close();
    </script>
    <?
}
*/

if ($pagina!="agenda") {

$sql="select direccion,contacto,telefono,id_dependencia,id_entidad
      from casos.casos_cdr
      join casos.dependencias using (id_dependencia) where idcaso=$id_caso";
$res=sql($sql) or fin_pagina();

$direccion=$res->fields['direccion'];
$telefono=$res->fields['telefono'];
$contacto=$res->fields['contacto'];
$id_dependencia=$res->fields['id_dependencia'];
$id_entidad=$res->fields['id_entidad'];


$recomendar="";
$sql="select count(id_visitas_casos) as cant_visitas,id_tecnico_visita
      from casos.casos_cdr
      join casos.visitas_casos using (idcaso)
      where id_dependencia=$id_dependencia
      group by id_tecnico_visita order by cant_visitas desc limit 1";
$res_sql=sql($sql," recomendar tecnicos") or fin_pagina();

if ($res_sql->RecordCount() > 0)
    $recomendar=$res_sql->fields['id_tecnico_visita'];
else {
$sql="select count(id_visitas_casos) as cant_visitas,id_tecnico_visita
      from casos.casos_cdr
      join casos.visitas_casos using (idcaso)
      join casos.dependencias using (id_dependencia)
      where id_entidad=$id_entidad
      group by id_tecnico_visita order by cant_visitas desc limit 1";
      $res_sql=sql($sql," recomendar tecnicos") or fin_pagina();
      $recomendar=$res_sql->fields['id_tecnico_visita'];
     }
} // del if de agenda

//dia actual
if ($_POST['anterior']==1 )
	$fecha_hoy=dia_habil_anterior($_POST['fecha_hoy']);
elseif ($_POST['posterior'] == 1)
	$fecha_hoy=($_POST['fecha_sig']);
elseif ($_POST['actualizar']) {
  $fecha_total=split("/",$_POST['fecha_sig']);
  $nrodiasemana=date("w",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
  if (!feriado($_POST['fecha']) && $nrodiasemana != 0)
        $fecha_hoy=$_POST['fecha'];
  else {
  	  $fecha_hoy=date("d/m/Y",mktime());
  	  Error ('El día seleccionado corresponde a un día no habil.');    
  }
}
else 
    $fecha_hoy=$parametros['fecha'] or $fecha_hoy=date("d/m/Y",mktime());

    $f=$fecha_hoy;

    
$fecha_total=split("/",$fecha_hoy);
$mes=date("n",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
$dia=date("j",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
$anio=date("Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
$nrodiasemana=date("w",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));

$dia_semana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");


//dia siguiente
$fecha_sig=dia_habil_posterior($fecha_hoy);

$fecha_total=split("/",$fecha_sig);
$mes_sig=date("n",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
$dia_sig=date("j",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
$anio_sig=date("Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
$nrodiasemana_sig=date("w",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
                
$sql_tecnicos="select id_tecnico_visita,tecnicos_visitas.nombre,tecnicos_visitas.apellido 
               from casos.tecnicos_visitas 
               left join casos.cas_ate using (idate) where ((cas_ate.nombre ilike 'CORADIR Bs.As.') and (tecnicos_visitas.activo<>0))";
$res=sql($sql_tecnicos) or fin_pagina();
?>

<script>
function dia_anterior() {
	document.all.anterior.value=1;
	document.all.posterior.value=0;
}


function dia_posterior() {
	document.all.anterior.value=0;
	document.all.posterior.value=1;
}

function control_datos() {

if (document.all.fechas.checked == true) {
   if (document.all.f_ini.value=="" || document.all.f_fin.value=="" ) {
       alert ('Ingrese rango de fechas');
       return false;
   }
   if (document.all.f_fin.value < document.all.f_ini.value) {
       alert ('Fecha finalizacion mayor que fecha inicio');
       return false;
   }
}

if (document.all.check_tecnico.checked == true) {
   cant=document.all.tecnicos.length;
   j=0;
   for (i=0;i<cant;i++) {
        if (document.all.tecnicos.options[i].selected==true) 
           j++;
           
   }
   if (j==0) {
   alert ('Debe seleccionar al menos un técnico')
   return false;
   }
}   
    
if (document.all.check_estado.checked == true) {
   cant=document.all.estado.length;
   j=0;
   for (i=0;i<cant;i++) {
        if (document.all.estado.options[i].selected==true) 
           j++;
           
   }
   if (j==0) {
   alert ('Debe seleccionar al menos un estado')
   return false;
   }
}   

return true;
}
</script>
<?
echo $html_header;
cargar_calendario();
?>

<form name='form1' action="asignar_visitas.php" method="POST" >
<input type="hidden" name="viene" value="<? echo $viene?>">
<input type='hidden' name='anterior' value=''>
<input type='hidden' name='posterior' value=''>
<input type='hidden' name='fecha_hoy' value='<?=$fecha_hoy?>'>
<input type='hidden' name='fecha_sig' value='<?=$fecha_sig?>'>
<input type='hidden' name='id_caso' value='<?=$id_caso?>'>
<input type='hidden' name='nro_caso' value='<?=$nro_caso?>'>
<input type='hidden' name='id_visita_asignada' value='<?=$id_visita_asignada?>'>
<input type='hidden' name='reasigna' value='<?=$reasigna?>'>
<input type='hidden' name='pagina' value='<?=$pagina?>'>
<input type='hidden' name='volver' value='<?=$volver?>'>

<div align="center"><font size="2+" color="<?=$color_celda?>"><b><?=$msg;?></b> </font></div>
<?

//$color_asignado='#3366CC';
$color_modulo='#99CCFF';
$color_sin_asignar='white';
$color_recomedado='#66CC88';
$color_modif='#FFC0C0';
$fecha_ini=fecha_db($fecha_hoy)." 00:00:00";
$fecha_fin=fecha_db($fecha_sig)." 23:59:59";

$colores[1]='#3366CC'; //tarea asignada
$colores[2]='black'; // Fracasada por parte del cliente
$colores[5]='black'; // fracasada por parte del tecnico
$colores[3]='red'; // Concretada no solucionada
$colores[4]='green'; // Concretada solucionada
$colores[6]='#3366CC'; //tarea modificada
$colores[7]='#3366CC'; //tarea reasignada

$sql="select id_visitas_casos,id_tecnico_visita,idcaso,direccion,contacto,telefono,
	  fecha_visita,observaciones,cant_modulos,estado,id_estado_visita 
	  from casos.visitas_casos 
	  join (select id_estado_visita,id_visitas_casos from casos.log_casos_visitas
	  join (select max(id_log_casos_visitas) as id_log_casos_visitas
	  from casos.log_casos_visitas group by id_visitas_casos) as res
	  using (id_log_casos_visitas)
      ) as r using (id_visitas_casos)
      where fecha_visita >='$fecha_ini'
	  and fecha_visita <= '$fecha_fin' order by id_tecnico_visita, fecha_visita";
$res_tecnicos=sql($sql,"datos visitas") or fin_pagina();

$agenda_tecnicos=array();

while(!$res_tecnicos->EOF) {
  	$id_tecnico=$res_tecnicos->fields['id_tecnico_visita'];
  	$fecha_visita=$res_tecnicos->fields['fecha_visita'];
   	$fecha=substr($fecha_visita,0,10);
   	$horas=substr($fecha_visita,11);
   	$hora=substr($horas,0,5);
    $agenda_tecnicos[$id_tecnico][$fecha][$hora]['cant_modulos']=$res_tecnicos->fields['cant_modulos'];
    $agenda_tecnicos[$id_tecnico][$fecha][$hora]['id_visita']=$res_tecnicos->fields['id_visitas_casos'];
    $agenda_tecnicos[$id_tecnico][$fecha][$hora]['estado']=$res_tecnicos->fields['id_estado_visita'];
    $res_tecnicos->MoveNext();
}


if ($pagina!="agenda") { ?>
<table align='center' width="90%">
  <tr>
    <td align='left'> <b>CASO N° </b><font size='2+' color="Blue">  <?=$nro_caso ?> </font> </td>
  </tr>
  <tr>
    <td align='left'> <b>Dirección:</b><font size='2' color="Blue"> <?=$direccion?> </font> </td>
  </tr>
  <tr>
    <td align='left'> <b>Contacto: </b><font size='2' color="Blue"> <?=$contacto?> </font> </td>
  </tr>
  <tr>
    <td align='left'> <b>Teléfono: </b><font size='2' color="Blue"> <?=$telefono?>  </font> </td>
  </tr>

</table>
<?}?>
<br>

<table align="center" class="bordes">
  <tr id="mo" bgcolor="<?=$bgcolor3?>">
       <td colspan="24"> ASIGNAR VISITAS </td>
  </tr>
  <?
  $color_celda="#b6d1b3";
  ?>
  <tr bgcolor=<?=$bgcolor_out?>>
    <td>
     <img src='<?="$html_root/imagenes/left2.gif" ?>' border="0" align="right" onclick="dia_anterior();document.all.form1.submit();">   </td>
      <td align='center' colspan="11" bgcolor=<?=$color_celda?>>
      <input type="text" name="fecha" size=10 value='<?=$f?>' readonly> <?=link_calendario("fecha")?>  <input type='submit' name='actualizar' value='A'>
      <? echo $dia_hoy=$dia_semana[$nrodiasemana]." ".$dia. " de ".$meses[$mes]." del ".$anio ?>
      </td>
      <td align='center' colspan="11">
      <? echo $dia_man=$dia_semana[$nrodiasemana_sig]." ".$dia_sig. " de  ".$meses[$mes_sig]." del ".$anio_sig ?>
      </td>
     <td> <img src='<?="$html_root/imagenes/right2.gif" ?>' border="0"  onclick="dia_posterior();document.all.form1.submit()"> </td>
  </tr>
 <?$hora="8:00";   //para que comienze desde las 9:00 ?>

  <tr bgcolor=<?=$bgcolor_out?>>
    <td>&nbsp; </td>
     <? for ($i=0;$i<11;$i++) {
     	$horas=split(":",$hora);
        if ($i<=10) $bgcolor="bgcolor=$color_celda";
               else $bgcolor="";

     	?>
        <td align='center' <?=$bgcolor?>> <?=$hora=date("H:i",mktime($horas[0],$horas[1]+60,$horas[2]))?> </td>
     <?}?>
     <? $hora="8:00";   //para que comienze desde las 9:00  suma des a una hora
        for ($i=0;$i<11;$i++) {
     	$horas=split(":",$hora);
     	?>
        <td align="center"> <?=$hora=date("H:i",mktime($horas[0],$horas[1]+60,$horas[2]))?> </td>
     <?}?>
    <td>&nbsp; </td>
  </tr>
  <?

while (!$res -> EOF ) {
   ?>
   <tr bgcolor='<?=$bgcolor_out?>'>
   <td <? if ($recomendar==$res->fields['id_tecnico_visita'])
                 echo "bgcolor=$color_recomedado"; ?>>
      <?=$res->fields['apellido']." ".$res->fields['nombre'] ?>
   </td>
   <?
      $nbre_tecnico=$res->fields['apellido']." ".$res->fields['nombre'];
      $tecnico=$res->fields['id_tecnico_visita'];
      $hora="8:30";   //para que comienze desde las 9:00
      $fecha=$fecha_hoy;
     
      if ($agenda_tecnicos[$tecnico][$fech][$hora]['cant_modulos'] =="") {
             $ocupado=0;
             $id_visitas_casos="";
            }
      else {
             $ocupado=$agenda_tecnicos[$tecnico][$fech][$hora]['cant_modulos'];
             $id_visitas_casos=$agenda_tecnicos[$tecnico][$fech][$hora]['id_visita'];
             $id_estado_visita=$agenda_tecnicos[$tecnico][$fech][$hora]['estado'];
     }
      for ($i=0;$i<22;$i++) {
      	if ($hora=='19:30') $hora="8:30";
        if ($i==11) $fecha=$fecha_sig;
        $horas=split(":",$hora);
        $hora=date("H:i",mktime($horas[0],$horas[1]+30,$horas[2]));
        $fech=fecha_db($fecha);

        if ($ocupado==0) {
            if ($agenda_tecnicos[$tecnico][$fech][$hora]['cant_modulos'] =="") {
            $ocupado=0;
            $id_visitas_casos="";
            }
            else {
              $ocupado=$agenda_tecnicos[$tecnico][$fech][$hora]['cant_modulos'];
              $id_visitas_casos=$agenda_tecnicos[$tecnico][$fech][$hora]['id_visita'];
              $id_estado_visita=$agenda_tecnicos[$tecnico][$fech][$hora]['estado'];
           }
          }
//colores

if ($ocupado > 0 ) {  //visita asignada
    if ($ocupado==$agenda_tecnicos[$tecnico][$fech][$hora]['cant_modulos']) { //comienzo de la visita
          if ($id_visita_asignada !="" && $id_visitas_casos==$id_visita_asignada) {
           $color=$color_modif;
          }
          else {
          $color=$colores[$id_estado_visita];
          }
     $hay_link=1;
     }
     else  {   //continua
          if ($id_visita_asignada !="" && $id_visitas_casos==$id_visita_asignada) {
           $color=$color_modif;
           $hay_link=1;
          }
          else {
          $color=$color_modulo;
          $hay_link=0;
          }
     }
     $ocupado--;
 }
 else {
	$ocupado=$agenda_tecnicos[$tecnico][$fech][$hora]['cant_modulos'];
	$id_visitas_casos=$agenda_tecnicos[$tecnico][$fech][$hora]['id_visita'];
	$id_estado_visita=$agenda_tecnicos[$tecnico][$fech][$hora]['estado'];
	 if ($ocupado != "") {
	 	  if ($id_visita_asignada !="" && $id_visitas_casos==$id_visita_asignada) {
           $color=$color_modif;
          }
	 	  else {
          $color=$colores[$id_estado_visita];
	 	  }
          $hay_link=1;
          $ocupado--;
     }
     else  {
     	  $ocupado=0;
     	  if ($id_visita_asignada !="" && $id_visitas_casos==$id_visita_asignada) {
           $color=$color_modif;
          }
     	  else {
          $color=$color_sin_asignar;
     	  }
          $hay_link=1;
     }

}

if ($i<=10) $bgcolor="bgcolor=$color_celda";
             else $bgcolor="";
        ?>
        <td <?=$bgcolor?>>
           <table align="center">
                <tr align="center">
                <?
                  $link1=encode_link('concretar_visitas.php',array("id_caso"=>$id_caso,"nro_caso"=>$nro_caso,"hora" =>$hora,"fecha"=>$fecha,"tecnico" => $tecnico,"nombre_tecnico"=>$nbre_tecnico,"id_visitas_casos"=>$id_visitas_casos,"id_visita_asignada"=>$id_visita_asignada,"reasigna"=>$reasigna,"pagina"=>$pagina,"volver"=>$volver,"viene"=>$viene));
                  $onclick1="ventana=window.open('$link1','','');";
                ?>
                <td style="cursor:hand" bgcolor="<?=$color?>" width="50%">
                 <?
                 if (($fecha_actual > $fecha) || $pagina=='agenda')
                       {

                       $hay_link=0;
                       }

                if ( $hay_link==1)
                {
                ?>
                <a  title="Haga click para concretar/ver la visita" onclick="<?=$onclick1?>"> <font color="<?=$color?>"> c </font> </a>
                <? }
                else {
                ?>
                 <font color="<?=$color?>"> c </font>
                <?}?>
               </td>
               <?
                $horas=split(":",$hora);
                $hora=date("H:i",mktime($horas[0],$horas[1]+30,$horas[2]));
                $fech=fecha_db($fecha);


if ($ocupado > 0 ) {  //visita asignada
    if ($ocupado==$agenda_tecnicos[$tecnico][$fech][$hora]['cant_modulos']) { //comienzo de la visita
          if ($id_visita_asignada !="" && $id_visitas_casos==$id_visita_asignada) {
           $color=$color_modif;
          }
          else {
          $color=$colores[$id_estado_visita];
          }
     $hay_link=1;
     }
     else  {   //continua
          if ($id_visita_asignada !="" && $id_visitas_casos==$id_visita_asignada) {
           $color=$color_modif;
           $hay_link=1;
          }
          else {
          $color=$color_modulo;
          $hay_link=0;
          }
     }
     $ocupado--;
} else {
	$ocupado=$agenda_tecnicos[$tecnico][$fech][$hora]['cant_modulos'];
	$id_visitas_casos=$agenda_tecnicos[$tecnico][$fech][$hora]['id_visita'];
	$id_estado_visita=$agenda_tecnicos[$tecnico][$fech][$hora]['estado'];
	 if ($ocupado != "") {
	 	  if ($id_visita_asignada !="" && $id_visitas_casos==$id_visita_asignada) {
           $color=$color_modif;
          }
	 	  else {
          $color=$colores[$id_estado_visita];
	 	  }
          $hay_link=1;
          $ocupado--;
     }
     else  {
     	  $ocupado=0;
     	  if ($id_visita_asignada !="" && $id_visitas_casos==$id_visita_asignada) {
           $color=$color_modif;
          }
     	  else {
          $color=$color_sin_asignar;
     	  }
          $hay_link=1;
     }

}
?>
<td style="cursor:hand" bgcolor="<?=$color?>" width="50%" >
    <?
    $link1=encode_link('concretar_visitas.php',array("id_caso"=>$id_caso,"nro_caso"=>$nro_caso,"hora" =>$hora,"fecha"=>$fecha, "fechas"=>$fecha_sig,"tecnico" =>$tecnico,"nombre_tecnico"=>$nbre_tecnico,"id_visitas_casos"=>$id_visitas_casos,"id_visita_asignada"=>$id_visita_asignada,"reasigna"=>$reasigna,"pagina"=>$pagina,"volver"=>$volver,"viene"=>$viene));
    $onclick1="ventana=window.open('$link1','','');";

    if (($fecha_actual > $fecha)|| $pagina=="agenda" )
    $hay_link=0;
    if ($hay_link==1) {
    ?>
    <a  title="Haga click para asignar la visita" onclick="<?=$onclick1?>"> <font color="<?=$color?>" > c </font></a> </td>
    <? }
       else {
    ?>
      <font color="<?=$color?>"> c </font> </td>
    <?}?>
    </tr>
    </table>
    </td>
    <?
     }
     ?>
     <td>&nbsp;</td>
 </tr>
 <?
  $res->MoveNext();
  }
  ?>

</table>

<? if (permisos_check("inicio","permiso_estat_casos") && $pagina=="agenda") {
	?>
	<br> <br> <br>
<table align='center' class="bordes"> 
<tr>
<td id=mo colspan=3>
   Estadisticas de visitas
</td>
</tr>
<tr>
   <td><input type='checkbox' name='fechas' value=1> Entre Fechas </td>
   <td><input type="text" name="f_ini" size=10 value='' readonly> <?=link_calendario("f_ini")?>&nbsp;&nbsp;
   <input type="text" name="f_fin" size=10 value='' readonly> <?=link_calendario("f_fin")?></td>
</tr>
<tr>
   <td><input type='checkbox' name='check_tecnico' value=1> Técnicos </td>
    
 <?
 $res->Movefirst();?>
   <td >
   <select name='tecnicos' multiple>
   <? while (!$res->EOF) {?>
     <option value='<?=$res->fields['id_tecnico_visita']?>'><?=$res->fields['apellido']." ".$res->fields['nombre']?></option> 
   <? $res->MoveNext();}?>  
  </select>  
  </td>
</tr>
<tr>
   <td><input type='checkbox' name='check_estado' value=1> Estado </td>
  
   <? $sql="select id_estado_visita,descripcion from estado_visitas
            where descripcion <> 'Asignada' and descripcion <> 'Modificada' and descripcion <> 'Reasignada'
            order by descripcion";
      $res=sql($sql,"recupera estado") or fin_pagina();
      $list='(';?>
   <td >
  <select name='estado' multiple>
   <? while (!$res->EOF) {
   	 $list.=$res->fields['id_estado_visita'].","; ?>
      <option value='<?=$res->fields['id_estado_visita']?>'><?=$res->fields['descripcion']?></option> 
   <? $res->MoveNext();}
   $list=substr_replace($list,')',(strrpos($list,',')));
   
   ?> 
  </select>  
  </td>
</tr>
<input type='hidden' name='list' value='<?=$list?>'>
<?
 $link=encode_link('visitas_estadisticas_imprimir.php',array("list"=>$list));?>

<tr>
   <td colspan=2 align='center'>
     <input align='center' type='button' name='datos' value='Ver Datos' onclick="if (control_datos()) window.open('<?=$link?>')">
   </td>
</tr>
</table> 

<? }?>
<br>
<?if ($pagina=='caso_estados' || $volver=='caso_estados') {

    $sql="select id_entidad from casos.casos_cdr
          join casos.dependencias using (id_dependencia) where idcaso=$id_caso";
    $res=sql($sql,"al recuperar entidad") or fin_pagina();
    $entidad=$res->fields['id_entidad'];
    $link = encode_link("caso_estados.php",array("id"=>$id_caso,"id_entidad"=>$entidad));
?>
    <div align='center'>
   
    <input type='button' name='cerrar' value='Volver al caso' onclick="location.href='<?=$link?>'">
    
    </div>
<?
  }
  elseif($pagina=="agenda" || $viene=="listado"){
?>
    <div align='center'>
    <input type='button' name='cerrar' value='Cerrar' onclick="window.close();">
    </div>

<?
  }
  else {
  ?>
  <div align='center'>
   
    
    <input type='button' name='cerrar' value='Cerrar' onclick='window.opener.visitas.submit();window.close();'>
    
  </div>
<?
}
 ?>


 <br> <br> <br>

<table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>
   <tr>
     <td colspan=2 bordercolor='#FFFFFF'>
       <b>Colores de referencia</b>
     </td>
  </tr>
  <tr>
	<td width=33% bordercolor='#FFFFFF'>
        <table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%><tr>
		<td width=15 bgcolor=<?=$color_recomedado?> bordercolor='#000000' height=15>&nbsp;</td>
		<td bordercolor='#FFFFFF'><?="Tecnico recomendado por el sistema";?></td>
		</tr></table>
    </td>
     <td width=33% bordercolor='#FFFFFF'>
        <table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%><tr>
		<td width=15 bgcolor='white' bordercolor='#000000' height=15>&nbsp;</td>
		<td bordercolor='#FFFFFF'><?="Asignar nueva visita";?></td>
		</tr></table>
     </td>
   </tr>
   
   <tr>
      <td colspan=2 bordercolor='#FFFFFF'>Inicio de la visita:</td>
   </tr>
   <tr>
   <td width=33% bordercolor='#FFFFFF' colspan=2>
        <table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%>
        <tr>
        <td width=15 bgcolor=<?=$colores[1]?> bordercolor='#000000' height=15>&nbsp;</td>
		<td bordercolor='#FFFFFF'><?="Visita asignada";?></td>
		<td width=15 bgcolor=<?=$colores[2]?> bordercolor='#000000' height=15>&nbsp;</td>
		<td bordercolor='#FFFFFF'><?="Fracasada";?></td>
		<td width=15 bgcolor=<?=$colores[3]?> bordercolor='#000000' height=15>&nbsp;</td>
		<td bordercolor='#FFFFFF'><?="Concretada no solucionada";?></td>
		<td width=15 bgcolor=<?=$colores[4]?> bordercolor='#000000' height=15>&nbsp;</td>
		<td bordercolor='#FFFFFF'><?="Concretada solucionada";?></td>
		</tr></table>
   </td>
   </tr>
   <tr>
     <td width=33% bordercolor='#FFFFFF'>
        <table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%>
          <tr>
		    <td width=15 bgcolor=<?=$color_modulo?> bordercolor='#000000' height=15>&nbsp;</td>
		    <td bordercolor='#FFFFFF'><?="Modulos de una visita";?></td>
		  </tr>
	    </table>
     </td>
     <td width=33% bordercolor='#FFFFFF'>
        <table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%>
         <tr>
		   <td width=15 bgcolor=<?=$color_modif?> bordercolor='#000000' height=15>&nbsp;</td>
		   <td bordercolor='#FFFFFF'><?="Visita que se está reasignando";?></td>
		</tr>
	   </table>
    </td>
  </tr>
</table>



</form>
