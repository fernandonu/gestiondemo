<?php
/*
$Author: ferni $
$Revision: 1.14 $
$Date: 2007/06/26 20:01:42 $
*/
/*
Pagina que modifica el estado de los renglones
estados - Orden de Compra
          Preadjudicada
          Presuntamente ganada
*/
require_once("../../config.php");

$id=$parametros["id"] or $id=$_POST["id"];
$ver=$parametros["ver"];
$link=encode_link("lic_est_esp.php",array("id"=>$id,"ver"=>$ver));

/*
function obtener_estados($id) {
global $db;

$sql="select * from historial_estados ";
$sql.=" where id_renglon=$id and activo=1 order by id_estado_renglon";
$historial=$db->execute($sql) or die($sql."<br>".$db->errormsg());
$cantidad=$historial->recordcount();

for($i=0;$i<$cantidad;$i++){
   switch ($historial->fields["id_estado_renglon"]){

   case 1:
         $estados[1]=1;
         break;
   case 2:
         $estados[2]=2;
         break;
   case 3:
         $estados[3]=3;
         break;

   }//del swicth
 $historial->movenext();
}

return $estados;
}

function eliminar_estado($id_renglon){
global $db;
$sql="delete from historial_estados ";
$sql.=" where id_renglon=$id_renglon";
$db->execute($sql) or die($db->errormsg()."<br>".$sql);

}//fin de la funcion




//$id_renglon le paso el renglon que cambia de estado
//$id_estado  me dice si hay un estado para cambia si no hay nada
// puede ser que se elimine el que estaba o no
// filtrar me pasa el id del estado siempre para poder buscar en la
// base de datos



function insertar_estado($id_renglon,$id_estado,$filtrar=1){
global $db,$_ses_user;


$fecha=date("Y-m-d H:i:s",mktime());
$usuario=$_ses_user["name"];

$db->starttrans();

$sql="select codigo_renglon from renglon where id_renglon=$id_renglon";
$resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
$codigo_renglon=$resultado->fields["codigo_renglon"];
if ($id_estado!=""){
         $sql="select id_historial_renglon,id_renglon,id_estado_renglon,activo from historial_estados ";
         $sql.=" where id_renglon=$id_renglon and id_estado_renglon=$id_estado";
         $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
         $cantidad=$resultado->recordcount();
         $id_historial_renglon=$resultado->fields["id_historial_renglon"];
         $activo=$resultado->fields["activo"];
         if ($cantidad<=0)
             {
             //inserto el estado por que no existe

             $sql="select nextval('historial_estados_id_historial_renglon_seq') as id_historial_renglon";
             $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
             $id_historial_renglon=$resultado->fields["id_historial_renglon"];

             $sql="insert into historial_estados (id_historial_renglon,id_renglon,id_estado_renglon) ";
             $sql.="values ($id_historial_renglon,$id_renglon,$id_estado) ";
             $db->execute($sql) or die($db->errormsg()."<br>".$sql);
             //falta el log

             //obtengo el nombre del estado a insertar
             $sql="select * from estado_renglon where id_estado_renglon=$id_estado";
             $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
             $tipo="Agrego el Estado ".$resultado->fields["nombre"]." al renglon:$codigo_renglon";

             //inserto el log correspondiente
             $sql="insert into log_estado_renglon (id_historial_renglon,tipo,usuario,fecha)";
             $sql.=" values ($id_historial_renglon,'$tipo','$usuario','$fecha')";
             $db->execute($sql) or die($sql."<br>".$db->errormsg());
             }

             else{
               if ($activo==0){
                  //modifico el id del estado
                  //ya existe y le doy de nuebo alta logica
                  $sql="update  historial_estados set activo=1 where ";
                  $sql.=" id_historial_renglon=$id_historial_renglon";
                  $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
                  //obtengo el nombre del estado a insertar
                  $sql="select * from estado_renglon where id_estado_renglon=$id_estado";
                  $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
                  $tipo="Agrego el Estado ".$resultado->fields["nombre"]." al renglon: $codigo_renglon";

                  //inserto el log correspondiente
                  $sql="insert into log_estado_renglon (id_historial_renglon,tipo,usuario,fecha)";
                  $sql.=" values ($id_historial_renglon,'$tipo','$usuario','$fecha')";
                  $db->execute($sql) or die($sql."<br>".$db->errormsg());
               } //del if de activo

             }


       }//que no viene el id_estado y tengo que borrar
       else {
             $sql="select id_historial_renglon,id_renglon,id_estado_renglon,activo from historial_estados ";
             $sql.=" where id_renglon=$id_renglon and id_estado_renglon=$filtrar";
             $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
             $cantidad=$resultado->recordcount();
             $id_historial_renglon=$resultado->fields["id_historial_renglon"];
             $activo=$resultado->fields["activo"];
             if ($cantidad && $activo){
                //realizo la baja logica
                $sql="update  historial_estados set activo=0 where ";
                $sql.=" id_historial_renglon=$id_historial_renglon";
                $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());

                //obtengo el nombre del estado a insertar
                $sql="select * from estado_renglon where id_estado_renglon=$filtrar";
                $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
                $tipo="Elimino  el Estado ".$resultado->fields["nombre"]." al renglon:$codigo_renglon";

                //inserto el log correspondiente
                $sql="insert into log_estado_renglon (id_historial_renglon,tipo,usuario,fecha)";
                $sql.=" values ($id_historial_renglon,'$tipo','$usuario','$fecha')";
                $db->execute($sql) or die($sql."<br>".$db->errormsg());

            }

      }


$db->completetrans();
} //del if de la funcion

*/


if ($_POST["guardar"]){
     //modifico el estado de los renglones
     $cantidad=$_POST["cantidad"];
     $estado_renglon=array();
     $db->StartTrans();

    // print_r($_POST);
     for($i=0;$i<$cantidad;$i++){
         $id_renglon=$_POST["id_renglon_$i"];
         $orden_compra=$_POST["orden_compra_$i"];
         $pre_adjudicado=$_POST["pre_adjudicado_$i"];
         $pres_ganado=$_POST["pres_ganado_$i"];

         insertar_estado($id_renglon,$orden_compra,3);
         insertar_estado($id_renglon,$pre_adjudicado,2);
         insertar_estado($id_renglon,$pres_ganado,1);


         //me fijo cual es el mayor estado
         $estado="";
         if ($orden_compra)
              {
              $estado=$orden_compra;//es estado orden de compra
              }
              else{
                   if ($pre_adjudicado){
                           $estado=$pre_adjudicado; //es estado pre_adjudicado
                          }
                          else{
                               if ($pres_ganado){
                                          $estado=$pres_ganado; //es estado orden de compra

                                          }
                               }
                  } //del primer else


         $estado_renglon[$i]=$estado;
     }//del for


     $estado=max($estado_renglon);
     //actualizo el estado de la licitacion

       switch ($estado) {
          case 1:
                $sql="select id_estado,nombre from estado";
                $sql.=" where nombre='Presuntamente ganada'";
                break;
          case 2:
                $sql="select id_estado,nombre from estado";
                $sql.=" where nombre='Preadjudicada'";
                break;
          case 3:
                $sql="select id_estado,nombre from estado";
                $sql.=" where nombre='Orden de compra'";
                break;
          default:
                 $estado=0;

                 break;
       }//del switch
      if ($estado) {

         $resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
         $id_estado=$resultado->fields["id_estado"];
         //echo "estado de la licitacion:".$id_estado."".$sql."".$resultado->recordcount();

        if ($id_estado)
           {
           $sql="update licitacion set id_estado=$id_estado ";
           $sql.=" where id_licitacion=$id";
           $db->execute($sql) or die($db->errormsg()."<br>".$sql);
           }
      }


     $db->CompleteTrans();
     }


echo $html_header;
?>
<form name=form1 action=<?=$link?> method=POST>
<input type=hidden name=id value=<?=$id?>>
<?
 $sql="select log_estado_renglon.* from licitacion";
 $sql.=" left join renglon using(id_licitacion)";
 $sql.=" left join historial_estados using(id_renglon)";
 $sql.=" left join log_estado_renglon using(id_historial_renglon)";
 $sql.=" where licitacion.id_licitacion=$id order by fecha";
 $logs=$db->execute($sql) or die($sql."<br>".$db->errormsg());
 $cant_logs=$logs->recordcount();
 if ($cant_logs>0) {
?>
<table width=100% align=center>
         <tr id='mo'><td colspan=3>Log de los estados</td></tr>
         <tr id='ma'>
          <td width=50%>Tipo</td>
          <td width=25%>Usuario</td>
          <td width=25%>Fecha</td>
         </tr>
</table>

 <div style="position:relative; width:100%;height:10%; overflow:auto;">
     <table width=100% align=Center cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
        <?
         for($i=0;$i<$cant_logs;$i++)
         {
         ?>
         <tr>
            <td align=left  width=50%> <?=$logs->fields["tipo"]?></td>
            <td align=left  width=25%> <?=$logs->fields["usuario"]?></td>
            <td align=right width=25%><?=fecha($logs->fields["fecha"])?></td>
         </tr>
         <?
         $logs->movenext();
         }
         ?>

     </table>
     </div>
<?
 }
?>

<table width=100% align=center border=1 bordercolor=#969696 cellspading=0 cellspacing=0>
<tr id='mo'>
<td width=100%>Estados de los Renglones</td>
</tr>
<?
$sql="Select nro_lic_codificado,entidad.nombre,valor_dolar_lic,id_moneda from licitacion ";
$sql.=" join entidad using(id_entidad)";
$sql.=" where id_licitacion=$id ";
$resultado=sql($sql,'Error');

$moneda_lic=$resultado->fields['id_moneda'];
$valor_dolar_lic=$resultado->fields['valor_dolar_lic'];

$nombre_entidad=$resultado->fields["nombre"];
$nro_licitacion=$resultado->fields["nro_lic_codificado"];
?>
<tr>
 <td>
    <table width=100% align=Center>
      <tr>
        <td id=ma_sf>Licitación Nro:</td>
        <td ><b><?=$nro_licitacion?></b></td>
      </tr>
      <tr>
        <td id=ma_sf>Entidad:</td>
        <td><b><?=$nombre_entidad?></b></td>
      </tr>
    </table>
  </td>
</tr>
<tr>
 <td>
 <table width=100% align=Center  border=1 bordercolor=#D0D0D0 cellspading=0 cellspacing=0>
 <tr id='ma'>
   <td>Cant</td>
   <td width=45%>Titulo</b>
   <td width=20%>Renglon</td>
   <td width=5%>Pres Ganado</td>
   <td width=5%>Pre Adjudicado</td>
   <td width=5%>Orden de Compra</td>
   <td width=20%>Monto</td>
 </tr>
 <?

 $sql=" select id_renglon,nro_renglon,titulo,codigo_renglon,cantidad,precio_licitacion,ganancia";
 $sql.=" from renglon  ";
 $sql.=" where renglon.id_licitacion=$id";
 $resultado=sql($sql,'ERRor');
 $cantidad=$resultado->recordcount();

 for ($i=0;$i<$cantidad;$i++){
 $id_renglon=$resultado->fields["id_renglon"];
 $estados_renglon=array();
 $estados_renglon=obtener_estados($id_renglon);

 if ($estados_renglon[1]==1) $checked_pg="checked";
                        else $checked_pg="";
 if ($estados_renglon[2]==2) $checked_pa="checked";
                        else $checked_pa="";
 if ($estados_renglon[3]==3) $checked_oc="checked";
                       else $checked_oc="";
 ?>
 <input type='hidden' name='id_renglon_<?=$i?>' value='<?=$resultado->fields["id_renglon"]?>'>
   <tr>
    <td align=center><b><?=$resultado->fields["cantidad"]?></b></td>
    <td><b><?=$resultado->fields["titulo"]?></b></td>
    <td><b><?=$resultado->fields["codigo_renglon"]?></b></td>

     <td align=center><input type=checkbox name="pres_ganado_<?=$i?>"    <?=$checked_pg?> value=1></td>
     <td align=center><input type=checkbox name="pre_adjudicado_<?=$i?>" <?=$checked_pa?> value=2></td>
     <td align=center><input type=checkbox name="orden_compra_<?=$i?>"   <?=$checked_oc?> value=3></td>
     
     <?              
     $sqlr="select sum(cantidad*precio_licitacion) as total_renglon,id_renglon
          		from licitaciones.producto
          		where id_renglon = $id_renglon
          		group by id_renglon";     
     $desc_renglon=sql($sqlr) or fin_pagina(); 
     
     $ganancia = $resultado->fields['ganancia'];     
     $total_renglon=$desc_renglon->fields["total_renglon"];
          
     if ($moneda_lic==1 && $ganancia!=0){
     	$subtotal_renglon=($total_renglon * $valor_dolar_lic)/$ganancia;?>
     	<td><b>$ <?echo number_format($subtotal_renglon*$resultado->fields['cantidad'],'2','.','');?></b></td>
     <?}
     else{
     	$subtotal_renglon=$total_renglon/$ganancia;?>
     	<td><b>$ <?echo number_format($subtotal_renglon*$resultado->fields['cantidad'],'2','.','');?></b></td>
     <?}?>

     
   </tr>
 <?
 $resultado->movenext();
 }//del for
 ?>
 <input type=hidden name=cantidad value=<?=$cantidad?>>
 </table>
 </td>
 </tr>

 <tr>
   <td>
    <table width=100% align=Center>
     <tr>
     <?
     //if (!$ver || $_ses_user['login']=="juanmanuel" || $_ses_user['login']=="corapi" || $_ses_user["fernando"] )
      if (permisos_check("inicio","licitaciones_estados_renglones"))
      {
     ?>
      <td align=right><input type=submit name=guardar value=Guardar></td>
      <td align=left><input type=button name=cerrar value=Cancelar onclick="window.close()"></td>
     <?
     }
     else
     {
     ?>
     <td align=center><input type=button name=cerrar value=Cerrar onclick="window.close()"></td>
     <?
     }
     ?>
     </tr>
    </table>
   </td>
  </tr>
</table>
<?=fin_pagina();?>
</form>