<?php
/*
$Author: enrique $
$Revision: 1.2 $
$Date: 2005/10/31 19:29:57 $
*/

require_once("../../config.php");

$id_competidor=$parametros["id_competidor"];

$sql="select *
      from competidores
       where id_competidor=$id_competidor";
$res=sql($sql) or fin_pagina();
$comp=$res->fields["nombre"];
$direccion=$res->fields["direccion"];
$cuit=$res->fields["cuit"];


echo $html_header;
?>
<table width=100% align=center class=bordes border=0>
   <tr>
      <td id=mo width=100%>Licitaciones del Competidor</td>
   </tr>
   <tr>
     <td>
         <table width=100% align=center>
            <tr>
               <td id=ma_sf width=15%>Competidor:</td>
               <td ><font color=red size=2><b><?=$comp?></b></td>
            </tr>
            <tr>
               <td id=ma_sf>Dirección</td>
               <td><font color=red size=2><b><?=$direccion?></b></td>
            </tr>
            <tr>
               <td id=ma_sf>Cuit</td>
               <td><font color=red size=2><b><?=$cuit?></b></td>
            </tr>
         </table>
     </td>
   </tr>
   <!-- Datos de las licitaciones de la entidad   -->
   <?
   $sql="select distinct id_licitacion,fecha_apertura,licitacion.nro_lic_codificado,entidad.nombre,estado.color
         from licitaciones.competidores join licitaciones.oferta using(id_competidor)
         join licitaciones.renglon using(id_renglon)
         join licitaciones.licitacion using(id_licitacion)
         join licitaciones.entidad using(id_entidad)
         join licitaciones.estado using(id_estado)
         where id_competidor=$id_competidor 
   ";
   $res=sql($sql) or fin_pagina();
   ?>
   <tr>
      <td width=100% align=Center>
          <table width=100% align=center >
              <tr id=mo><td colspan=6>Licitaciones</td></tr>
              <tr id=ma>
                 <td width=1%></td>
                 <td >Id Lic</td>
                 <td >Fecha Apertura</td>
                 <td >Número</td>
                 <td >Resultados</td>
              </tr>
              <?
              //Traigo todas las licitaciones de esa entidad
              for($i=0;$i<$res->recordcount();$i++){
                  $id_licitacion=$res->fields["id_licitacion"];
                  $simbolo=$res->fields["simbolo"];
              ?>
                  <tr bgcolor=<?=$bgcolor_out?>>
                       <td width=1%><input type=checkbox class='estilos_check' name=chk value=1 onclick="javascript:(this.checked)?Mostrar('fila_<?=$id_licitacion?>'):Ocultar('fila_<?=$id_licitacion?>');"></td>
                       <td width=10%  align=center bgcolor=<?=$res->fields["color"]?> title='<?=$res->fields["nombre"]?>'>
                                 <?$link=encode_link("licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id_licitacion));?>
                                 <a style='color="<?=contraste($res->fields["color"],"#000000","#ffffff");?>"' href="<?=$link?>" target='_blank'>
                                  <?=$id_licitacion?>
                                 </a>
                       </td>
                       <td width=45% align=center><?=fecha($res->fields["fecha_apertura"])?></td>
                       <td width=45% align=Center><?=$res->fields["nro_lic_codificado"]?></td>
                       <td align=center>
                                 <?$link=encode_link("lic_ver_res2.php",array("keyword"=>$id_licitacion,"pag_ant"=>"lic","pagina_volver"=>"licitaciones_por_entidad.php"));?>
                                  <a href="<?=$link?>" target='_blank'>R</a>
                       </td>
                   </tr>
                   <?
                 //Traigo los renglones de esta licitacion
                   $sql="
                       select * from
                        (
                        select titulo,codigo_renglon,total,ganancia,id_renglon,cantidad from renglon
                        where id_licitacion=$id_licitacion and (titulo ilike'%computadora%' or titulo ilike '%otro%')
                        ) as renglones
                        left join
                        (
                        select id_renglon, max(id_estado_renglon) as id_estado_renglon
                        from licitaciones.historial_estados
                        where activo=1 group by id_renglon order by id_renglon
                        ) as estado_actual
                        using(id_renglon)
                        left join
                        (
                        select nombre as nombre_estado,id_estado_renglon from licitaciones.estado_renglon
                        ) as estado_nombre
                        on estado_actual.id_estado_renglon = estado_nombre.id_estado_renglon
                        ";

                     $renglones=sql($sql) or fin_pagina();
                     ?>
                 <tr>
                 <td></td>
                  <td colspan=4>
                  <div id='fila_<?=$id_licitacion?>' style='display:none'>
                      <?
                      //No hay renglones  que mostrar
                      if ($renglones->recordcount()<=0){
                      ?>
                         <table  width=100% align=Center bgcolor=<?=$bgcolor3?> cellspading=0 cellpading=0 class="bordes" border=0>
                         <tr><td colspan=6 align=center><b>No Hay Renglones Tipo Computadoras u Otros para esta licitación</b></td></tr>
                         </table>
                      <?
                      }//del then
                      else{
                     ?>
                           <table  width=100% align=Center bgcolor=<?=$bgcolor3?> cellspacing=0 cellpading=0 border=1 bordercolor=#ACACAC>
                                <tr>
                                 <td id=ma align=center>Título</td>
                                 <td id=ma align=center>Renglon</td>
                                 <td id=ma align=center>Estado</td>
                                 <td id=ma align=center>Ganancia</td>
                                 <td id=ma align=center>Cant.</td>
                                 <td id=ma>Total</td>
                              </tr>
                               <?
                               //muestro los renglones
                                  for($j=0;$j<$renglones->recordcount();$j++){

                                   if ($renglones->fields["nombre_estado"])
                                              $nombre_estado=$renglones->fields["nombre_estado"];
                                              else $nombre_estado="&nbsp;"
                               ?>
                                  <tr>
                                    <td><b><?=$renglones->fields["titulo"]?></b></td>
                                    <td align=center><b><?=$renglones->fields["codigo_renglon"]?></b></td>
                                    <td><b><?=$nombre_estado?></b></td>
                                    <td align=center><b><?=$renglones->fields["ganancia"]?></b></td>
                                    <td align=center><b><?=$renglones->fields["cantidad"]?></b></td>
                                    <td align=center>
                                        <table width=100% align=center>
                                          <tr>
                                            <td> <b><?=$simbolo?></b></td>
                                            <td> <b><?=formato_money($renglones->fields["total"])?></b></td>
                                          </tr>
                                        </table>
                                    </td>
                                  </tr>
                                   <?
                                   $renglones->movenext();
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
              $res->movenext();
              }
              ?>
          </table>
      </td>
   </tr>
   <tr>
   <td align="center">
        <input type=button name=cerrar value=Cerrar onclick="window.close()">
   </td>
   </tr>
</table>