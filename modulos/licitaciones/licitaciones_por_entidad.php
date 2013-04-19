<?php
/*
$Author: nazabal $
$Revision: 1.5 $
$Date: 2007/05/28 20:21:38 $
*/

require_once("../../config.php");

$id_entidad=$parametros["id_entidad"];

$sql="select entidad.nombre as nombre_entidad,entidad.direccion,
      distrito.nombre as nombre_distrito
      from entidad
      join distrito using(id_distrito)
      where id_entidad=$id_entidad";
$res=sql($sql) or fin_pagina();
$entidad=$res->fields["nombre_entidad"];
$direccion=$res->fields["direccion"];
$distrito=$res->fields["nombre_distrito"];


echo $html_header;
?>
<table width=100% align=center class=bordes border=0>
   <tr>
      <td id=mo width=100%>Licitaciones de la Entidad</td>
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
            <tr>
             <?
             $link=encode_link("cargar_competidores.php",array("id_entidad"=>$id_entidad,"direccion"=>$direccion,"distrito"=>$distrito,"entidad"=>$entidad));
             ?>
            <td colspan="2"><input type=button name=competidores value="Analizar por competidor" onclick="window.open('<?=$link;?>','','left=40,top=80,width=700,height=300,resizable=1,status=1,scrollbars=1');" style="cursor:hand"></td>
            </tr>
         </table>
     </td>
   </tr>
   <!-- Datos de las licitaciones de la entidad   -->
   <?
   $sql="select licitacion.id_licitacion,fecha_apertura,estado.color,estado.nombre,
                moneda.simbolo,licitacion.nro_lic_codificado
                ,fecha_oc
                ,EXTRACT(EPOCH FROM (date_trunc('day',fecha_oc)-date_trunc('day',fecha_apertura)))/60/60/24 as emision_oc
         from licitaciones.licitacion
         left join licitaciones.estado using(id_estado)
         left join licitaciones.moneda using(id_moneda)
         left join (
			SELECT
			  licitaciones.archivos.id_licitacion,
			  MIN(licitaciones.archivos.subidofecha) AS fecha_oc
			FROM
			  licitaciones.tipo_archivo_licitacion
			  RIGHT OUTER JOIN licitaciones.archivos ON (licitaciones.tipo_archivo_licitacion.id_tipo_archivo = licitaciones.archivos.id_tipo_archivo)
			WHERE
			  (licitaciones.tipo_archivo_licitacion.tipo = 'Orden de Compra')
			GROUP BY
			  licitaciones.archivos.id_licitacion
         ) oc on (licitacion.id_licitacion=oc.id_licitacion)
         where id_entidad=$id_entidad order by id_licitacion DESC
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
                 <td >Emisión OC</td>
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
                       <td width=20% align=center><?=fecha($res->fields["fecha_apertura"])?></td>
                       <td width=20% align=center><?=($res->fields["emision_oc"]!="")?$res->fields["emision_oc"]." días":""?></td>
                       <td width=40% align=Center><?=$res->fields["nro_lic_codificado"]?></td>
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
                  <td colspan=5>
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
<br>
<? fin_pagina(); ?>