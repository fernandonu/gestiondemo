<?
/*
$Creador: Mari$
$Author: mari $
$Revision: 1.22 $
$Date: 2006/12/29 11:41:52 $
*/

require_once("../../config.php");
include("../caja/func.php");
echo $html_header;

$id_caso=$parametros['id_caso'] or $id_caso=$_POST['id_caso'];
$nro_caso=$parametros['nro_caso'] or $nro_caso=$_POST['nro_caso'];
$id_visitas_casos=$parametros['id_visitas_casos'] or $id_visitas_casos=$_POST['id_visitas_casos'];
$id_visita_asignada=$parametros['id_visita_asignada'] or $id_visita_asignada=$_POST['id_visita_asignada'];
$reasigna=$parametros['reasigna'] or $reasigna=$_POST['reasigna'];
$solo_lectura=$parametros['solo_lectura'] or $solo_lectura=$_POST['solo_lectura'];
$pagina=$parametros['pagina'] or $pagina=$_POST['pagina'];
$volver=$parametros['volver'] or $volver=$_POST['volver'];
$viene=$parametros['viene'] or $viene=$_POST['viene'];

function H_asignadas($tecnico,$id_visita_asignada="") {
if ($id_visita_asignada!="")
  $and= " and id_visitas_casos != $id_visita_asignada";
  else $and="";
$sql="select id_visitas_casos,id_tecnico_visita,idcaso,direccion,contacto,telefono,
	     fecha_visita,observaciones,cant_modulos,estado
		 from casos.visitas_casos where id_tecnico_visita=$tecnico $and order by fecha_visita";

$res_tecnicos=sql($sql,"datos visitas") or fin_pagina();
 
$agenda_tecnicos=array();

while(!$res_tecnicos->EOF) {

  	$fecha_completa=$res_tecnicos->fields['fecha_visita'];
   	$fecha_asignada=substr($fecha_completa,0,10);
   	$horas_asignadas=substr($fecha_completa,11);
   	$hora_asignada=substr($horas_asignadas,0,5);
   	$agenda_tecnicos[$tecnico][$fecha_asignada][$hora_asignada]=1;
    $cant=$res_tecnicos->fields['cant_modulos'];
    for ($i=0;$i<$cant-1;$i++) {
      $horas_asignadas=split(":",$hora_asignada);
      $hora_asignada=date("H:i",mktime($horas_asignadas[0],$horas_asignadas[1]+30,'00'));
      $agenda_tecnicos[$tecnico][$fecha_asignada][$hora_asignada]=1;
    }
  $res_tecnicos->MoveNext();
 }

 return $agenda_tecnicos;
}


if ($_POST['reasignar']) {  //muestra la pagina con la agenda de los tecnicos

if ($pagina=='caso_estados') $volver='caso_estados';

   $id_visita_asignada=$_POST['id_visitas_casos'];
   $id_caso=$_POST['id_caso'];
   $nro_caso=$_POST['nro_caso'];
   $fecha=$_POST['fecha'];
   $ref = encode_link('asignar_visitas.php',array("id_visita_asignada"=>$id_visita_asignada,"reasigna"=>1,"id_caso"=>$id_caso,"nro_caso"=>$nro_caso,"msg"=>'Seleccione un nuevo técnico o una nueva fecha y hora',"fecha"=>$fecha,"pagina"=>"reasignar","volver"=>$volver,"viene"=>$viene));
?>
<script>
  window.opener.location.href='<?=$ref?>';
  window.close();
</script>
<?
}


if ($_POST['guardar']) {

$db->StartTrans();

$id_visitas_casos=$_POST['id_visitas_casos'];
$id_visita_asignada=$_POST['id_visita_asignada'];
$nbre_usuario=$_ses_user['name'];
$direccion=$_POST['dir'];
$contacto=$_POST['cont'];
$telefono=$_POST['telefono'];
$fecha=fecha_db($_POST['fecha']);
$hora=$_POST['hora'];
$modulos=$_POST['modulos'];
$observaciones=$_POST['observaciones'];
$id_caso=$_POST['id_caso'];
$nro_caso=$_POST['nro_caso'];
$tecnico=$_POST['tecnico'];
$tecnico_anterior=$_POST['tecnico_anterior'];
$fecha_visita=$fecha." ".$hora.":00";
$fecha_hoy=date("Y-m-d H:i",mktime());
$reasigna=$_POST['reasigna'];


if ($id_visita_asignada !="" && $reasigna==1 ) { //update reasigna tecncio y fecha
if ($tecnico == $tecnico_anterior) {
    $agenda_tecnicos=H_asignadas($tecnico,$id_visita_asignada);
} else
     $agenda_tecnicos=H_asignadas($tecnico);

$i=0;
while ($i < $modulos-1)  {
   $horas=split(":",$hora);
   $hora=date("H:i",mktime($horas[0],$horas[1]+30,'00'));
   if ($agenda_tecnicos[$tecnico][$fecha][$hora] == "") {
       
    }   
   else {
     Error("La hora " .$hora."  para el día ". fecha_db($fecha)." ya esta asignada.");
     break; 
    }
  $i++;  
}

if (!$error) {
$sql="update visitas_casos set id_tecnico_visita=$tecnico,fecha_visita='$fecha_visita',cant_modulos=$modulos
      where id_visitas_casos=$id_visita_asignada";

$res=sql($sql,"reasignar tecnico/hora") or fin_pagina();

$sql="select id_estado_visita from estado_visitas where descripcion='Reasignada'";
$res=sql($sql,'id de modificda') or fin_pagina();
$id_estado=$res->fields['id_estado_visita'];

$sql="insert into log_casos_visitas (id_visitas_casos,nbre_usuario,fecha,descripcion,id_estado_visita)
       values ($id_visita_asignada,'$nbre_usuario','$fecha_hoy','',$id_estado)";

$res=sql($sql,"log ") or fin_pagina();
$msg="La visita fue reasignada exitosamente";
}
}
elseif ($id_visitas_casos != "") { //actualiza datos de la visita

$sql="update visitas_casos set
     direccion='$direccion',contacto='$contacto',telefono='$telefono',fecha_visita='$fecha_visita',
     cant_modulos=$modulos,observaciones='$observaciones',id_tecnico_visita=$tecnico,idcaso=$id_caso,estado='Pendiente'
     where id_visitas_casos=$id_visitas_casos";

$res=sql($sql,"insertar en casos_visitas") or fin_pagina();

$sql="select id_estado_visita from estado_visitas where descripcion='Modificada'";
$res=sql($sql,'id de modificda') or fin_pagina();
$id_estado=$res->fields['id_estado_visita'];

$sql="insert into log_casos_visitas (id_visitas_casos,nbre_usuario,fecha,descripcion,id_estado_visita)
       values ($id_visitas_casos,'$nbre_usuario','$fecha_hoy','',$id_estado)";

$res=sql($sql,"log ") or fin_pagina();
$msg=" Las modificaciones se realizaron con exito.";
}

else  {  //guardar

$sum=($modulos-1) * 30;
$horas=split(":",$hora);
$hora_fin=date("H:i",mktime($horas[0],$horas[1]+$sum,'00'));


if ($hora_fin > '19:30') {
    Error ("La hora de finalización de la visita supera las 19:30 hs");
}

//horas asginadas del tecnico seleccionado

$agenda_tecnicos=H_asignadas($tecnico);

$i=0;
while ($i < $modulos-1)  {
   $horas=split(":",$hora);  
   $hora=date("H:i",mktime($horas[0],$horas[1]+30,'00'));
   if ($agenda_tecnicos[$tecnico][$fecha][$hora] == "") {
       
    }   
   else {
     Error("La hora " .$hora."  para el día ". fecha_db($fecha)." ya esta asignada.");
     break; 
    }
  $i++;  
}


if (!$error) {

$sql="SELECT nextval('casos.visitas_casos_id_visitas_casos_seq') as id";
$res=sql($sql) or fin_pagina();
$id_visita=$res->fields['id'];

$sql="insert into visitas_casos
     (id_visitas_casos,direccion,contacto,telefono,fecha_visita,cant_modulos,observaciones,id_tecnico_visita,idcaso,estado)
      values ($id_visita,'$direccion','$contacto','$telefono','$fecha_visita',$modulos,'$observaciones',$tecnico,$id_caso,'Pendiente')";


$res=sql($sql,"insertar en casos_visitas") or fin_pagina();
// Cambiar el campo de sincronizacion
$sql="UPDATE casos.casos_cdr SET sync=2 where idcaso=$id_caso and sync <> 1";
sql($sql,"Sincronizar") or fin_pagina();

$sql="select id_estado_visita from estado_visitas where descripcion='Asignada'";
$res=sql($sql,'id de asignada') or fin_pagina();
$id_estado=$res->fields['id_estado_visita'];

$sql="insert into log_casos_visitas (id_visitas_casos,nbre_usuario,fecha,descripcion,id_estado_visita)
       values ($id_visita,'$nbre_usuario','$fecha_hoy','',$id_estado)";

$res=sql($sql,"log ") or fin_pagina();
$msg=" Se insertó la visita exitosamente.";
}
}

if ($db->CompleteTrans() && !$error) {
 $ref = encode_link('asignar_visitas.php',array("id_caso"=>$id_caso,"nro_caso"=>$nro_caso,"fecha"=>fecha($fecha),"msg"=>$msg,"pagina"=>$pagina,"volver"=>$volver,"viene"=>$viene));
 /* if ($pagina!="caso_estados" || $pagina=="reasignar") {*/
 ?>
  <script>
      window.opener.location.href='<?=$ref?>';
      window.close();
  </script>
<?
 /* }
  else

  {
      ?>
        <script>
        window.opener.visitas.submit();
        window.close();
       </script>

      <?
  }*/
}
}



if ($_POST['guardar_estados']) {

$db->StartTrans();

   $fecha=date("Y-m-d H:i:s");
   $nbre_usuario=$_ses_user["name"];
   $descripcion=$_POST["descripcion"];
   $id_estado_visita=$_POST["select_estado"];
   $id_visitas_casos=$_POST["id_visitas_casos"];
   $sql="insert into log_casos_visitas
          (id_estado_visita,id_visitas_casos,fecha,descripcion,nbre_usuario)
          values
          ($id_estado_visita,$id_visitas_casos,'$fecha','$descripcion','$nbre_usuario')

        ";
   sql($sql) or fin_pagina();
   $sql="update  visitas_casos set estado='Historial' where id_visitas_casos=$id_visitas_casos";
   sql($sql) or fin_pagina();
   // Cambiar el campo de sincronizacion
	$sql="UPDATE casos.casos_cdr SET sync=2 where idcaso=$id_caso and sync <> 1";
	sql($sql,"Sincronizar") or fin_pagina();

   if ($db->Completetrans())
                          {
                           ?>
                           <script>
                           if (window.opener) {
                            window.opener.document.visitas.submit();
                            window.close();
                           }
                           </script>
                           <?
                          }
}
?>

<script>
function control_datos() {
 if (document.all.observaciones.value=='') {
	alert ('Ingrese una observación');
    return false;
 }
else return true;
}

function control_datos_1(){
 if(document.all.select_estado.options[document.all.select_estado.selectedIndex].value==-1) {
     alert('Seleccione un estado para la visita.');
  return false;
 } 
return true;
}
</script>


<form name='form1' action="concretar_visitas.php" method="POST">
<?


if ($id_visitas_casos !="" || $id_visita_asignada !="") {
	$id_visita=$id_visitas_casos or $id_visita=$id_visita_asignada;
$sql="select idcaso,id_tecnico_visita,vc.direccion,contacto,nombre,apellido,observaciones,
       vc.telefono,fecha_visita,observaciones,cant_modulos,estado
       from casos.visitas_casos vc
       join casos.tecnicos_visitas using (id_tecnico_visita)
       where id_visitas_casos=$id_visita";
$res_visitas=sql($sql,"datos visitas") or fin_pagina();
$direccion=$res_visitas->fields['direccion'];
$telefono=$res_visitas->fields['telefono'];
$contacto=$res_visitas->fields['contacto'];
$observaciones=$res_visitas->fields['observaciones'];

$cant_modulos=$res_visitas->fields['cant_modulos'];
$estado=$res_visitas->fields['estado'];

if ($id_visita_asignada !="") {  
$tecnico_anterior=$res_visitas->fields['id_tecnico_visita'];	
$tecnico=$parametros['tecnico'] or $tecnico=$_POST['tecnico'];
$hora=$parametros['hora'] or $hora=$_POST['hora'] ;
$nbre_tecnico=$parametros['nombre_tecnico'] or $$nbre_tecnico['nombre_tecnico'];
$fecha=$parametros['fecha'] or $fecha=$_POST['fecha'] ;
} else { 
$fecha=fecha($res_visitas->fields['fecha_visita']);
$hora=substr(Hora($res_visitas->fields['fecha_visita']),0,5);
$tecnico=$res_visitas->fields['id_tecnico_visita'];
$nbre_tecnico=$res_visitas->fields['apellido'] ." ".$res_visitas->fields['nombre'];
}

} else {

$sql="select direccion,contacto,telefono from casos.casos_cdr 
join casos.dependencias using (id_dependencia) where idcaso=$id_caso";
$res=sql($sql) or fin_pagina();

$direccion=$res->fields['direccion'];
$telefono=$res->fields['telefono'];
$contacto=$res->fields['contacto'];
$hora=$parametros['hora'] or $hora=$_POST['hora'] ;
$fecha=$parametros['fecha'] or $fecha=$_POST['fecha'] ;
$tecnico=$parametros['tecnico'] or $tecnico=$_POST['tecnico'];
$nbre_tecnico=$parametros['nombre_tecnico'] or  $nbre_tecnico=$_POST['nombre_tecnico'];
$cant_modulos=$parametros['cant_modulos'] or $cant_modulos=$_POST['cant_modulos'] or $cant_modulos=2;

}

$dia_semana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

$fecha_total=split("/",$fecha);
  $mes=date("n",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
  $dia=date("j",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
  $anio=date("Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
 $nrodiasemana=date("w",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));

$fecha_visita=$dia_semana[$nrodiasemana]." ".$dia. " de  ".$meses[$mes]." del ".$anio; 
?>

<input type="hidden" name="viene" value="<? echo $viene; ?>">
<input type='hidden' name='id_caso' value='<?=$id_caso?>'>
<input type='hidden' name='nro_caso' value='<?=$nro_caso?>'>
<input type=hidden name='fecha' value='<?=$fecha?>'>
<input type=hidden name='hora' value='<?=$hora?>'>
<input type=hidden name='tecnico' value='<?=$tecnico?>'>
<input type=hidden name='id_visitas_casos' value='<?=$id_visitas_casos?>'>
<input type=hidden name='id_visita_asignada' value='<?=$id_visita_asignada?>'>
<input type=hidden name='reasigna' value='<?=$reasigna?>'>
<input type=hidden name='solo_lectura' value='<?=$solo_lectura?>'>
<input type=hidden name='pagina' value='<?=$pagina?>'>
<input type=hidden name='volver' value='<?=$volver?>'>

<? if ($id_visitas_casos != "" || $id_visita_asignada!= "") { ?>
<input type=hidden name='modulos' value='<?=$cant_modulos?>'> 
<? } ?>

<?
if ($id_visitas_casos !="" || $id_visita_asignada!= "") {
	$id_visita=$id_visitas_casos or $id_visita=$id_visita_asignada;
$sql="select fecha,nbre_usuario,estado_visitas.descripcion,log_casos_visitas.descripcion as observaciones
      from casos.log_casos_visitas
      join casos.estado_visitas using (id_estado_visita) where id_visitas_casos=$id_visita
      order by fecha desc";
$log=sql($sql,"log") or fin_pagina();

if ($log->RecordCount() > 0 ) {

?>

<!-- tabla de registro -->
<div style="overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;' ?> "  >
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<tr>
  <td><b>Fecha</b></td>
  <td><b>Hora</b></td>
  <td><b>Usuario</b></td>
  <td><b>Descripcion</b></td>
  <td><b>Observaciones</b></td>
</tr>
<? while (!$log->EOF) { ?>
<tr>

  <td> <?=fecha($log->fields['fecha'])?>  </td>
  <td><?=Hora($log->fields['fecha'])?>  </td>
  <td> <?=$log->fields['nbre_usuario']; ?> </td>
  <td> <?=$log->fields['descripcion']; ?> </td>
  <td><? if ($log->fields['observaciones']) echo $log->fields['observaciones'];else echo "&nbsp;"; ?> </td>
</tr>
<?
 $log->MoveNext();
}
 ?>
</table>
</div>

<?} ?>
<hr>
<?}
?>


<br>
<?


?>
<table align='center' width="90%" align="center" class="bordes">

  <tr id="mo" bgcolor="<?=$bgcolor3?>">
      <td colspan="2" align="center"> CONCRETAR CITA CON EL CLIENTE </td>
  </tr>
  <tr bgcolor=<?=$bgcolor_out?>>
    <td align='center'> <b>CASO N° </b>
    <? if ($pagina=="listado") {
    	$sql="select id_entidad from casos.casos_cdr 
              join casos.dependencias using (id_dependencia) where idcaso=$id_caso";
    	$res=sql($sql,"al recuperar entidad") or fin_pagina();
    	$entidad=$res->fields['id_entidad'];
    	$link = encode_link("caso_estados.php",array("id"=>$id_caso,"id_entidad"=>$entidad));
    	?>
       <font size=3 color='red' >
        <a href="<?=$link?>"  target="_blank"><U>
             <?=$nro_caso?></U></a>
       </font> 
       <?}
       else echo $nro_caso;?>   
    </td>
    <td> <b> Tecnico Asignado:</b> <?=$nbre_tecnico?> </td>
  </tr>
  <tr bgcolor=<?=$bgcolor_out?>>
    <td colspan=2> <b>Dirección: <input type='text' name='dir' size='60' value='<?=$direccion?>'> </td>
  </tr>  
  <tr bgcolor=<?=$bgcolor_out?>>
    <td> <b>Contacto: </b> <input type='text' name='cont' size='40' value='<?=$contacto?>'>  </td>
    <td> <b>Teléfono: </b> <input type='text' name='telefono' size='35'value='<?=$telefono?>' > </td> 
  </tr>
  <tr bgcolor=<?=$bgcolor_out?>>
     <td> <b>Fecha:</b> <?=$fecha_visita?></td>
     <td> <b> Hora:</b> <?=$hora?> 
       &nbsp;&nbsp;&nbsp; <b>Cantidad de modulos </b>
      <select name='modulos' <?if ($id_visitas_casos !="" && $id_visita_asignada =="") echo 'disabled';?> title="cada modulo tiene 30 minutos">
      <? for ($i=1;$i<10;$i++) {?>
          <option value=<?=$i?> <?if ($cant_modulos==$i) echo 'selected' ?>><?=$i?> </option>
        <? } ?> 
      </select>  
     
     </td>
  </tr>
  
  <tr bgcolor=<?=$bgcolor_out?>>   <td align='center' colspan=2> <b>Observaciones</b> </td> </tr>
  <tr bgcolor=<?=$bgcolor_out?>>
   	 <td colspan=2 align='center'> <textarea name=observaciones rows=4 style="width:80%"><?=$observaciones?></textarea></td>
  </tr>

 <tr align="center" bgcolor=<?=$bgcolor_out?>> 
 <td colspan=2 > 
  <? if ($estado=="" || $estado=='Pendiente')  { ?>
        <input type='submit' name='guardar' value='Guardar' 
                   onclick='return control_datos();'> 
        &nbsp;&nbsp;&nbsp;&nbsp
  <? }

   if ($parametros["pagina"]=="listado")
                $disabled_tecnico="disabled";
                else
                $disabled_tecnico="";
   if ($estado=='Pendiente' && $reasigna!=1 ) {?>
     <input type='submit' name='reasignar' value='Cambiar Tecnico/Hora' <?=$disabled_tecnico?>> &nbsp;&nbsp;&nbsp;&nbsp;
 <?  } ?>
    <input type='button' name='cerrar' value='Cerrar' onclick='window.close();' <?=$disabled_tecnico?>>
    </td>
 </tr> 
</table>
<?

if (($pagina=="caso_estados" || $pagina=="listado") && $pagina!="reasignar" ) {

$sql="select * from estado_visitas
      where descripcion <> 'Asignada' and descripcion <> 'Modificada' and descripcion <> 'Reasignada' ";
$estados=sql($sql) or fin_pagina();
?>
<br>
<table width=90% border=1 align=center>
   <tr id=mo>
     <td>Estado de la Visita</td>
   </tr>
   <tr>
     <td width=100%>
      <table width="100%" align=center>
        <tr>
           <td><b>Estado:</b></td>
           <td align=left>
             <select name=select_estado>
                <option value=-1>seleccionar estado</option>
                <?
                for($i=0;$i<$estados->recordcount();$i++){
                  $id_estado_visita=$estados->fields["id_estado_visita"];
                  $descripcion=$estados->fields["descripcion"];
                ?>
                 <option value="<?=$id_estado_visita?>" ><?=$descripcion?> </option>
                <?
                $estados->movenext();
                }
                ?>
             </select>
           </td>
        </tr>
        <tr>
          <td valign=top><b>Observaciones:</b></td>
          <td>
           <textarea name=descripcion rows=5 style="width:100%"></textarea>
          </td>
        </tr>
        <tr>
          <td colspan=2 align=center>
          <?
          if ($solo_lectura) $disabled="disabled";
          ?>
            <input type=submit name=guardar_estados value=Guardar  <?=$disabled?> onclick='return control_datos_1()'>
            &nbsp;
          <?
          if ($pagina=='listado') {
          ?>
            <input type=button name=cancelar_estado value="Volver al Listado" onclick="document.location='listado_visitas.php'">
          <?
          }
          ?>
          </td>
        </tr>
      </table>
      </td>
   </tr>
</table>
<?
}
?>
</form>