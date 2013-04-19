<?php
/*
$Author: nazabal $
$Revision: 1.33 $
$Date: 2007/05/08 20:26:33 $
*/
require_once("../../config.php");
require_once("funciones.php");


$mes=$parametros["mes"]  or $mes=$_POST["mes"];
$año=$parametros["año"]  or $año=$_POST["año"];


$fecha_seleccionada=$año."-".$mes;




$sql="select * from sueldos
     left join legajos using (id_legajo)
     left join afjp using (id_afjp)
     left join tareas_desemp using (id_tarea)
     left join calificacion using (id_calificacion)
     where fecha like '$fecha_seleccionada%' and estado_liquidacion='1'";
//$result = sql($sql) or fin_pagina();
$result=sql($sql) or fin_pagina();
$cantidad=$result->recordcount();
?>
<script language="javascript">
function imprimir(){
 document.all.imprimir.style.visibility="hidden";
 document.all.imprimir2.style.visibility="hidden";
 window.print();
 window.close();
}
</script>
<SCRIPT>
window.opener.windows_sueldo.focus();
</SCRIPT>
<HTML>
<HEAD>
<title>Libro de Sueldo</title>
<link rel="SHORTCUT ICON"  href="/path-to-ico-file/logo.ico">
<link rel=stylesheet type='text/css' href='<? echo "$html_root/lib/estilos.css"?>'>
</HEAD>
<BODY>
<table width="100%">
    <tr>
        <td>
        <input type="button" name="imprimir2" value="Imprimir Libro de Sueldo" onclick="imprimir()">
        </td>
        <td width="100%">
        <?
        $mes_periodo=cambiar($mes);
        echo "<font size=2><b>Libro de Sueldo perteneciente al período ".$mes_periodo." de ".$año."</b></font>";
        ?>
        </td>
    </tr>
</table>
<table width='100%' border='1' cellpadding='0' cellspacing='0'>
<? $j=0;
      for($i=0;$i<$cantidad;$i++){
              $db->SetFetchMode(ADODB_FETCH_ASSOC);
              $datos=$result->fields;
              ///print_r($datos);
              $total=calculo_valores($datos);
              ?>
              <tr>
                <td>
                <table border='0' width='100%'>
                 <tr>
                      <td valign='top'>
                               <table border='0' width='100%' align='center' cellpadding='0' cellspacing='0'>
                                <tr>
                                  <td colspan='2' align='center' class='bordes'><strong>Datos del Empleado</strong></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>Legajo</td>
                                   <td class='lineainf'><?=$datos["id_legajo"]?></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>Apellido y Nombre</td>
                                   <td class='lineainf'><?=$datos["apellido"]."  ".$datos["nombre"]?></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>CUIL</td>
                                   <td class='lineainf'><?=$datos["cuil"]?></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>Domicilio</td>
                                   <td class='lineainf'><?=$datos["domicilio"]?></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>Nacionalidad</td>
                                   <td class='lineainf'></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>Fecha Nacimiento</td>
                                   <td class='lineainf'><?=fecha($datos["fecha_nacimiento"])?></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>Estado Civil</td>
                                   <td class='lineainf'></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>Tipo y Nro. Documento</td>
                                   <td class='lineainf'><?=$datos["dni"]?></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>Fecha Ingreso</td>
                                   <td class='lineainf'><?=fecha($datos["fecha_ingreso"])?></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>AFJP</td>
                                   <td class='lineainf'><?=$datos["nombre_afjp"]?></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>Categoria</td>
                                   <td class='lineainf'><?=$datos["nombre_calificacion"]?></td>
                                </tr>
                                <tr>
                                  <td class='lineainf'>Tarea</td>
                                  <td class='lineainf'><?=$datos["nombre_tarea"]?></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>Básico</td>
                                   <td class='lineainf'><?=formato_money($datos["basico"])?></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>Horario</td>
                                   <td class='lineainf'></td>
                                </tr>
                                <tr>
                                   <td class='lineainf'>Mod. Contratación</td>
                                   <td class='lineainf'></td>
                                </tr>
                            </table>
                         </td>
                         <td valign='top'>
                           <table border='0' width='100%' align='center' cellpadding='0' cellspacing='0'>
                            <tr>
                                 <td align='center' width='33%' class='bordessinderecho'><strong>Remuneraciones   </strong></td>
                                 <td align='center' width='33%' class='bordessinderecho'><strong>Descuentos       </strong></td>
                                 <td align='center' width='34%' class='bordes'>          <strong>Conceptos no Rem.</strong></td>
                            </tr>
                            <tr>
                                <td valign=top class='borderizqinferior'>
                                 <table width='100%'>
                 <?                 
                if ($datos["basico"]!=0){                
                ?>    
                            <tr>
                               <td align='left'>Básico</td>
                               <td align='right'><?=formato_money($datos["basico"])?></td>
                            </tr>
                <?
                }
                if ($datos["presentismo"]!=0){
                ?>            
                             <tr>
                                <td align='left'>Presentismo</td>
                                <td align='right'><?=formato_money($datos["presentismo"])?></td>
                            </tr>
                <?
                }
                
                if ($datos["sueldo_por_horas"]!=0) {
               ?>             
                            <tr>
                              <td align='left'>Horas</td>
                              <td align='right'><?=formato_money($datos["horas"]*$datos["valor_hora"])?></td>
                             </tr>
                <?            
                }
                if ($datos["horas_extras"]!=0){
                ?>                             
                             <tr>
                             <td align='left'>Horas Extras</td>
                             <td align='right'><?=formato_money($total['subtotal_horas']);?></td>
                             </tr>
               <?              
               }
               if ($datos["ausentismo"]!=0){
               ?>
                                      <tr>
                                       <td align='left'>Ausentismo</td>
                                       <td align='right'><?=formato_money($datos["ausentismo"])?></td>
                                      </tr>
               <?  }
                if ($datos["vacaciones_dias"]!=0){
               ?>     
                                      <tr>
                                       <td align='left'>Vacaciones Gozadas</td>
                                       <td align='right'><?=formato_money($datos["vacaciones"])?></td>
                                     </tr>
                <?                      
                 }

                if ($datos["dias_vac"]!=0){
                ?>
                                     <tr>
                                       <td align='left'>Vacaciones</td>
                                       <td align='right'><?=formato_money($datos["total_vac"])?></td>
                                      </tr>
                <?    
                }
                if ($datos['sac']!=0){
                ?>    
                                      <tr>
                                       <td align='left'>S.A.C.</td>
                                       <td align='right'><?=formato_money($total['sac'])?></td>
                                      </tr>
                                      
                <?                      
                }
                if ($datos["acuenta"]!=0){
                ?>    
                                      <tr>
                                       <td align='left'>A Cuenta</td>
                                       <td align='right'><?=formato_money($datos["acuenta"])?></td>
                                      </tr>
                <?                      
                }                      
                if ($datos["dec1529"]!=0){
                ?>    
                                      <tr>
                                       <td align='left'>Decreto 1529</td>
                                       <td align='right'><?=formato_money($datos["dec1529"])?></td>
                                      </tr>
                <?                      
                }
                if ($datos["feriados"]!=0){
                ?>    
                                      <tr>
                                       <td align='left'>Feriados</td>
                                       <td align='right'><?=formato_money($datos["feriados"])?></td>
                                      </tr>
                <?                      
                }
                           
                if ($datos["ajuste_anterior"]!=0){
                ?>    
                                      <tr>
                                       <td align='left'>Ajuste Anterior</td>
                                       <td align='right'><?=formato_money($datos["ajuste_anterior"])?></td>
                                      </tr>
                <?                      
                }
                if ($datos["dias_inasistencia"]!=0){
                ?>    
                                      <tr>
                                       <td align='left'>Inasistencia</td>
                                       <td align='right'>-<?=formato_money($datos["total_inasistencia"])?></td>
                                      </tr>
                 <?                     
                 }           
                 ?>
                 </table>
                 </td>
                 <td valign=top class='borderizqinferior'>
                      <table width='100%'>
                               <tr>
                                <td align='left'>Jubilación</td>
                                <td align='right'><?=formato_money($datos["jubilacion"])?></td>
                               </tr>
                               <tr>
                                <td align='left'>Ley 19032</td>
                                <td align='right'><?=formato_money($datos["ley19032"])?></td>
                               </tr>
                               <tr>
                                <td align='left'>Obra Social</td>
                                <td align='right'><?=formato_money($datos["obra_social"])?></td>
                               </tr>
                               <tr>
                                <td align='left'>Sindicato</td>
                                <td align='right'><?=formato_money($datos["sindicato"])?></td>
                               </tr>
                               <tr>
                                <td align='left'>Faecys</td>
                                <td align='right'><?=formato_money($datos["faecys"])?></td>
                               </tr>
                <?               
                if ($datos["embargo"]<>0){
                ?>    
                               <tr>
                                <td align='left'>Embargo</td>
                                <td align='right'><?=formato_money($datos["embargo"])?></td>
                               </tr>
                <?
                }
                if ($datos["sindicato_familiar"]!=0){                
                ?>               
                               <tr>
                                    <td align='left'>Sindicato Familiar</td>
                                    <td align='right'><?=formato_money($datos["sindicato_familiar"])?></td>
                                </tr>
                <?                
                }
                ?>
                </table> 
                </td>
                <td valign=top class='bordessinsuperior'>
                <table width='100%'>
                <?
                if ($datos["dec1347"]!=0){
                ?>    
                           <tr>
                                 <td align='left'>Decreto 1347</td>
                                 <td align='right'><?=formato_money($datos["dec1347"])?></td> 
                           </tr>
                <?           
                } 
                if ($datos["dec2005_04"]!=0) {
                ?>    
                            <tr>
                                 <td align='left'>Decreto 2005/04</td>
                                 <td align='right'><?=formato_money($datos["dec2005_04"])?></td> 
                            </tr>
                <?            
                }
				
                if ($datos["sac_sobre_indemnizacion"]!=0){
                ?>    
                             <tr>
                                  <td align='left'>SAC Indemnización</td>
                                   <td align='right'><?=formato_money($datos["sac_sobre_indemnizacion"])?></td> 
                             </tr>
                <?        
                }
                if ($datos["sac_sobre_vng"]!=0){
                ?>
                              <tr>
                                  <td align='left'>SAC Vac. no Gozadas</td>
                                  <td align='right'><?=formato_money($datos["sac_sobre_vng"])?></td> 
                               </tr>
                <?        
                }
                if ($datos["sac_sobre_preaviso"]!=0){
                ?>    
                               <tr>
                                  <td align='left'>SAC Preaviso</td>
                                   <td align='right'><?=formato_money($datos["sac_sobre_preaviso"])?></td> 
                               </tr>
                <?        
                }
                if ($datos["salario_familiar"]!=0){
                ?>    
                                <tr>
                                   <td align='left'>Asignaciones Familiares</td>
                                    <td align='right'><?=formato_money($datos["salario_familiar"])?></td> 
                                 </tr>
                <?                 
                }
                if ($datos["ayuda_escolar"]!=0){
                ?>          
                                <tr>
                                   <td align='left'>Ayuda Escolar</td>
                                    <td align='right'><?=formato_money($datos["ayuda_escolar"])?></td> 
                                 </tr> 
                <?                 
                }
                if ($datos["gratificacion"]!=0){
                ?>    
                                    <tr>
                                       <td align='left'>Gratificación</td>
                                       <td align='right'><?=formato_money($datos["gratificacion"])?></td> 
                                    </tr>
                <?                     
                }
                //agrego indemnizacion y pre_aviso
                if ($datos["indemnizacion"]!=0){
                ?>    
                                   <tr>
                                       <td align='left'>Indemnización</td>
                                       <td align='right'><?=formato_money($datos["indemnizacion"])?></td> 
                                    </tr>
                <?
                }
                if ($datos["pre_aviso"]!=0){
                ?>    
                                    <tr>
                                       <td align='left'>Pre Aviso</td>
                                       <td align='right'><?=formato_money($datos["pre_aviso"])?></td> 
                                    </tr>
                <?                     
                }
                if ($datos["vac_no_gozadas"]!=0){
                ?>    
                                    <tr>
                                       <td align='left'>Vacaciones no Gozadas</td>
                                       <td align='right'><?=formato_money($datos["vac_no_gozadas"])?></td> 
                                    </tr> 
                <?                    
                }
                if ($datos["acuerdo_abril_2006"]!=0) {
                ?>
                                    <tr>
                                       <td align='left'>Acuerdo Abril 2006</td>
                                       <td align='right'><?=formato_money($datos["acuerdo_abril_2006"])?></td> 
                                     </tr>
               <?         
               }
               
                if ($datos["anticipo"]!=0) {
                ?>
                                    <tr>
                                       <td align='left'>Anticipo</td>
                                       <td align='right'>-<?=formato_money($datos["anticipo"])?></td> 
                                     </tr>
               <?         
               }
               ?>
               </table>
               </td>
               </tr>
               </table>
                <br>
                <table align='center' border='0' width='100%' cellpadding='0' cellspacing='0'>
                    <tr>
                    <td class='bordessinderecho'><strong>Total<br>Remunerativo</strong></td>
                    <td class='bordessinizquierdo'><?=formato_money($total['total'])?></td>
                    <td class='bordessinderecho'><strong>Total<br>Descuentos</strong></td>
                    <td class='bordessinizquierdo'><?=formato_money($total['total_desc'])?></td>
                    <td class='bordessinderecho'><strong>Total Conceptos<br>no Remumerativos</strong></td>
                    <td class='bordessinizquierdo'><?=formato_money($total['total_no_rem'])?></td>
                    </tr>
                </table>
                <table align='center' border='0' width='100%' cellpadding='0' cellspacing='0'>
                    <tr>
                     <td width='33%'>&nbsp;</td><td width='33%'>&nbsp;</td>
                     <td width='33%'>&nbsp;</td><td width='33%'>&nbsp;</td>
                     <td width='34%'>&nbsp;</td><td width='34%'>&nbsp;</td>
                    </tr>
                    <tr>
                     <td class='bordessinderecho'><strong>Total Neto</strong></td>
                     <td class='bordessinizquierdo'><?=formato_money($total['neto'])?></td>
                     <td>&nbsp;</td><td>&nbsp;</td>
                     <td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                    <tr>
                     <td>&nbsp;</td><td>&nbsp;</td>
                     <td>&nbsp;</td><td>&nbsp;</td>
                     <td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                    <tr>
                     <td>&nbsp;</td><td>&nbsp;</td>
                     <td>&nbsp;</td><td>&nbsp;</td>
                     <td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                    <tr>
                     <td>&nbsp;</td><td>&nbsp;</td>
                     <td>&nbsp;</td><td>&nbsp;</td>
                     <td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                </table>
                </td>
                </tr>
                <tr>
                  <td colspan='2' align='center'>
                       <table width='100%' border='0' cellspacing='0'>
                        <tr>
                         <td colspan='8' align='center' class='bordes'><strong>Familiares a Cargo</strong></td>
                        </tr>
                        <tr>
                         <td class='borderizqinferior'><strong>Parentesco</strong></td>
                         <td class='borderizqinferior'><strong>Nombre y Apellido</strong></td>
                         <td class='borderizqinferior'><strong>Fecha Nacimiento</strong></td>
                         <td class='borderizqinferior'><strong>Asignación</strong></td>
                         <td class='borderizqinferior'><strong>Parentesco</strong></td>
                         <td class='borderizqinferior'><strong>Nombre y Apellido</strong></td>
                         <td class='borderizqinferior'><strong>Fecha Nacimiento</strong></td>
                         <td class='bordessinsuperior'><strong>Asignación</strong></td>
                        </tr>
                        <tr>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='bordessinsuperior'>&nbsp;</td>
                        </tr>
                        <tr>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='bordessinsuperior'>&nbsp;</td>
                        </tr>
                        <tr>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='borderizqinferior'>&nbsp;</td>
                         <td class='bordessinsuperior'>&nbsp;</td>
                        </tr>
                       </table>
                  </td>
                 </tr>
                </table> 
                <?
                if ($j!=0) $j=0;
                        else $j++; 
                if ($j==0) {
                ?>
                <br clear=all style='page-break-before:always'>
                <?
                }
                ?>
                 <hr class="bordedoble">
                 </td>
                </tr>
               <?
                $result->MoveNext(); 
                }//del for
                ?>
               </table>
<br>
<input type="button" name="imprimir" value="Imprimir Libro" onclick="imprimir()">
</BODY>
</HTML>