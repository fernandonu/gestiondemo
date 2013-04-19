<?php
/*
$Author: diegoinga
*/

require_once("../../config.php");

$id_entidad=$parametros["id_entidad"];

$entidad=$parametros["entidad"];
$direccion=$parametros["direccion"];
$distrito=$parametros["distrito"];


echo $html_header;
?>
<table width=100% align=center class=bordes border=0>
   <tr>
      <td id=mo width=100%>Competidores relacionados a esta entidad</td>
   </tr>
   <tr>
     <td>
         <table width=100% align=center>
            <tr>
               <td id=ma_sf width=15%>Entidad:</td>
               <td ><font color=red size=2><b><?=$entidad?></b></td>
            </tr>
            <tr>
               <td id=ma_sf>Dirección</td>
               <td><font color=red size=2><b><?=$direccion?></b></td>
            </tr>
            <tr>
               <td id=ma_sf>Distrito</td>
               <td><font color=red size=2><b><?=$distrito?></b></td>
            </tr>

         </table>
     </td>
   </tr>
   <!-- Datos de las licitaciones de la entidad   -->
<?
   $sql="select distinct competidores.id_competidor,competidores.nombre,competidores.direccion,competidores.tel,competidores.mail from entidad join licitacion using(id_entidad) join renglon using (id_licitacion) join oferta using(id_renglon) join competidores using(id_competidor) where id_entidad=$id_entidad";
   $resultado_competidores=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
?>
   <tr>
      <td width=100% align=Center>
          <table width=100% align=center >
              <tr id=mo><td colspan=6>Competidores</td></tr>
              <tr id=ma>
                 <td width=1%></td>
                 <td >Competidor</td>
                 <td >Direccion</td>
                 <td >Telefono</td>
                 <td >Mail</td>
              </tr>
              <?
              //Traigo todas las licitaciones de esa entidad
              for($i=0;$i<$resultado_competidores->recordcount();$i++){
                  
              ?>
                  <tr bgcolor=<?=$bgcolor_out?>>
                       <td><input type=checkbox class='estilos_check' name=chk value=1 onclick="javascript:(this.checked)?Mostrar('fila_<?=$resultado_competidores->fields['id_competidor']?>'):Ocultar('fila_<?=$resultado_competidores->fields['id_competidor']?>');"></td>
                       <td><?=$resultado_competidores->fields['nombre'];?></td>
                       <td><?=$resultado_competidores->fields['direccion'];?></td>
                       <td><?=$resultado_competidores->fields['tel'];?></td>
                       <td><?=$resultado_competidores->fields['mail'];?></td>
                   </tr>
                   <?
                 //Traigo los renglones de esta licitacion
   
   $sql="select distinct id_licitacion,fecha_apertura,licitacion.nro_lic_codificado,entidad.nombre
         from competidores join oferta using(id_competidor)
         join renglon using(id_renglon)
         join licitacion using(id_licitacion)
         join entidad using(id_entidad)
         where id_competidor=".$resultado_competidores->fields['id_competidor'] ." order by id_licitacion DESC";
   
   $resultado_licitacion=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
   
                     ?>
                 <tr>
                 <td></td>
                 <td colspan=4>
                 <div id='fila_<?=$resultado_competidores->fields['id_competidor'];?>' style='display:none'>
                      <?
                      //No hay renglones  que mostrar
                      if ($resultado_licitacion->recordcount()<=0){
                      ?>
                         <table  width=100% align=Center bgcolor=<?=$bgcolor3?> cellspading=0 cellpading=0 class="bordes" border=0>
                         <tr><td colspan=6 align=center><b>No Licitaciones asociadas a este Competidor</b></td></tr>
                         </table>
                      <?
                      }//del then
                      else{
                     ?>
                           <table  width=100% align=Center bgcolor=<?=$bgcolor3?> cellspacing=0 cellpading=0 border=1 bordercolor=#ACACAC>
                                <tr>
                                 <td width="10%">Id Lic</td>
                                 <td width="60%">Entidad</td>
                                 <td width="20%">Fecha Apertura</td>
                                 <td width="10%">Res.</td>
                              </tr>
                               <?
                               //muestro los renglones
                                  for($j=0;$j<$resultado_licitacion->recordcount();$j++){

                                  ?>
                                  <?$link=encode_link("licitaciones_view.php",array("ID"=>$resultado_licitacion->fields["id_licitacion"],"pag_ant"=>"cargar_competidores","pagina_volver"=>"cargar_competidores.php","cmd1"=>"detalle"));?>
                                  <a href="<?=$link?>" target='_blank'>
                                  <tr <?=atrib_tr();?>>
                                    <td>
                                    <b><?=$resultado_licitacion->fields["id_licitacion"]?></b></td>
                                    <td><b><?=$resultado_licitacion->fields["nombre"]?></b></td>
                                    <td align=center><b><?=$resultado_licitacion->fields["fecha_apertura"]?></b></td>
                                    <td align=center>
                                    <?$link=encode_link("lic_ver_res2.php",array("keyword"=>$resultado_licitacion->fields["id_licitacion"],"pag_ant"=>"cargar_competidores","pagina_volver"=>"cargar_competidores.php"));?>
                                     <a href="<?=$link?>" target='_blank'>R</a>
                                     </td>
                                  </tr>
                                  </a>
                                 <?
                                  $resultado_licitacion->movenext();
                                   }
                                   ?>
                               </table>
                               <?
                               } //del else
                               ?>
                    </div>
                  </td>
              </tr>
              <?
              $resultado_competidores->movenext();
              }
              ?>
          </table>
      </td>
   </tr>
   <tr>
   <td align=center><input type=button name=cerrar value=Cerrar onclick="window.close()" style="cursor:hand;"></td>
   </tr>
</table>