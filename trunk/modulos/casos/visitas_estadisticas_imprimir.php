<?
/*
$Author: mari $
$Revision: 1.3 $
$Date: 2005/06/14 15:16:39 $
*/

require_once("../../config.php");

?>
<form name='form1' action='visitas_estadisticas_imprimir.php' method='post'>
<?
if ($_POST['mostrar']){

 $tecnicos=$_POST['tecnicos'];
 $estados_sel=$_POST['estados'];
 $lista=$_POST['lista'];
 
 
$where="";
  $fecha_ini=fecha_db($_POST['fecha_ini']);
  $fecha_fin=fecha_db($_POST['fecha_fin']);
  if ($fecha_ini !="" && $fecha_fin!="")
     $where=" and fecha_visita >='$fecha_ini' and fecha_visita <= '$fecha_fin' ";

if ($tecnicos != "") {
$tecnicos='('.$tecnicos.')';
$where.=" and id_tecnico_visita in ".$tecnicos;
}

if ($estados_sel=="") {
  $estados_sel=$lista;
  $estados_sel=$estados_sel;
  $estados=array();
  $l=strlen($lista) - 2;
  $lis=substr($lista,1,$l);
  $estados=explode(",",$lis);
}
else {
	$estados=array();
    $estados=explode(",",$estados_sel);
	$estados_sel='('.$estados_sel.')';
}
$where.=" and id_estado_visita in ".$estados_sel;

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

  $resultado=sql($sql) or fin_pagina();
 
 
$sql="select  id_estado_visita,descripcion from estado_visitas
      where descripcion <> 'Asignada' and descripcion <> 'Modificada' and descripcion <> 'Reasignada' 
      order by descripcion";
$est=sql($sql) or fin_pagina();

$i=0;
while (!$est->EOF) {
$id_estado=$est->fields['id_estado_visita'];
$desc=$est->fields['descripcion'];
$estado_visitas[$id_estado]['desc']=$desc;
$i++;
$est->Movenext();
}


$i=-1;
$tecnico_ant="";
while (!$resultado->EOF) {
   if ($resultado->fields['id_tecnico_visita']!= $tecnico_ant) {  
   	$i++;
    $datos[$i]['nombre']=$resultado->fields['apellido']." ".$resultado->fields['nombre'];
    $id_estado=$resultado->fields['id_estado_visita'];
    $datos[$i]['tarea']=array();
    $j=0;
    $datos[$i]['tarea'][$id_estado]=$resultado->fields['cantidad'];
  }
   else { 
   	$id_estado=$resultado->fields['id_estado_visita'];
   	$j++;
    $datos[$i]['tarea'][$id_estado]=$resultado->fields['cantidad'];
  }
    $tecnico_ant=$resultado->fields['id_tecnico_visita'];	
    $resultado->MoveNext();
    
}
$cantidad=count($datos);


?>

<script>
function imprimir_listado() {

 document.all.imprimir.style.visibility="hidden";
 window.print();
 window.close();

}
</script>

 <? if ($resultado->Recordcount() > 0) {?>
<table width=100% align=center class=bordes>
   <tr>
      <td>
       <input type=button name=imprimir value=Imprimir onclick="imprimir_listado();">
       &nbsp;
       <input type=button name=cerrar value=Cerrar onclick="window.close()">
      </td>
   </tr>
     <tr>
       <td>
         <table width="100%" align=center>
           <tr>
              <? if ($fecha_ini != "") {?>
              <td colspan=2><b>Datos desde <?=fecha($fecha_ini)?> hasta <?=fecha($fecha_fin)?>:</b></td>
              <?}
              else {?>
               <td align=left><b>Datos hasta <?=date("d/m/Y")?></b></td>
               <?}?>
           </tr>
           <tr>
             <td colspan=2><b>Usuario:</b>&nbsp;<b><?=$_ses_user["name"]?></b></td>
           </tr>
         </table>
       </td>
     </tr>
     <tr>
       <td>
           <table width=100% align=center border=1 cellspacing=1 cellpading=1>
             <tr>
               <td colspan=6 width=100% align=center><b>Estadisticas de Visitas</b></td>
             </tr>
            <tr>
            <td align='center'>Técnico</td>
           <?$cant=count($estados);
           for ($i=0;$i<$cant;$i++) { 
           	   $id=$estados[$i];?>
             <td align='center'> <?=$estado_visitas[$id]['desc']?></td>
           
           <?}?>
           </tr>
              
           <tr>
            <? for($i=0;$i<$cantidad;$i++) { ?>
                <tr>
               <td align=center> <?=$datos[$i]['nombre']?></td>
  			    <? $tareas=$datos[$i]['tarea'];
  			       for ($j=0;$j<$cant;$j++) { 
           	       $id=$estados[$j];?>
  			         <td align='center'><?if ($tareas[$id]) echo $tareas[$id]; else echo 0; ?></td>
  			      <? } ?>
           	    </tr>
  			<? }?>
           </tr>
    </table>
    </td>
     </tr>
   </table>
 <? }
 else {
   echo "<div align='center'> No hay datos para las fechas seleccionadas</div> ";
  }
  
} 
else {
	$lista=$parametros['list'];
	
?>

<input type='hidden' name='fecha_ini' value=''>
<input type='hidden' name='fecha_fin' value=''>
<input type='hidden' name='tecnicos' value=''>
<input type='hidden' name='estados' value=''>
<input type='hidden' name='lista' value='<?=$lista?>'>
<input type='hidden' name='mostrar' value='1'>

<script>
 //tecnicos
  if (window.opener.document.all.check_tecnico.checked==true) {
  var datos=new Array(); 
   var c=window.opener.document.all.tecnicos.length;
   var j=0;
   for( i=0;i<c;i++) {
     if (window.opener.document.all.tecnicos.options[i].selected==true) {
         datos[j]=window.opener.document.all.tecnicos.options[i].value;
         j++;
     }
   }
   document.all.tecnicos.value=datos;
  }

  //estados visitas
  if (window.opener.document.all.check_estado.checked==true) {
  var datos=new Array(); 
   var c=window.opener.document.all.estado.length;
   var j=0;
   for( i=0;i<c;i++) {
     if (window.opener.document.all.estado.options[i].selected==true) {
         datos[j]=window.opener.document.all.estado.options[i].value;
         j++
     }
   }
   document.all.estados.value=datos;
  } 
  
  //fecha 
  if (window.opener.document.all.fechas.checked==true) {
     document.all.fecha_ini.value=window.opener.document.all.f_ini.value;
     document.all.fecha_fin.value=window.opener.document.all.f_fin.value;
  }
  
 
 document.form1.submit();  
</script>  
</form> 
<? 
}
