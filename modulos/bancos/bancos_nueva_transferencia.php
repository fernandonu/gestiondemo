<?php
/*
$Author: fernando $
$Revision: 1.2 $
$Date: 2005/10/17 23:08:00 $
*/

require_once ("../../config.php");
require("bancos_funciones_transferencia.php");


$id_transferencias=$parametros["id_transferencias"]  or $id_transferencias=$_POST["id_transferencias"];
$pagina=$parametros["pagina"] or $pagina=$_POST["pagina"];
$cmd=$parametros["cmd"] or $cmd=$_POST["cmd"];
if ($_POST["guardar"]){
    
      $banco_origen=$_POST["banco_origen"];
      $banco_destino=$_POST["banco_destino"];
      $monto=$_POST["monto"];
      $observaciones=$_POST["observaciones"];
      
      
        if (!$id_transferencias)
             $msg=insertar_transferencia($banco_origen,$banco_destino,$monto,$observaciones);
             else
             $msg=modificar_transferencia($id_transferencias,$banco_origen,$banco_destino,$monto,$observaciones);
      }      


if ($_POST["en_proceso"]) 
        $msg=cambiar_estado_transferencia($id_transferencias,2); 
        
if ($_POST["historial"]) 
        $msg=cambiar_estado_transferencia($id_transferencias,3);     
        

if ($id_transferencias){
    
            $sql =  " select t.id_transferencias,t.monto,t.observaciones, t.id_estado_transferencias,banco_origen, banco_destino 
                            from
                            bancos.transferencias t
                       where id_transferencias=$id_transferencias   
                    ";
            $resultado=sql($sql) or fin_pagina();
            
            $banco_origen=$resultado->fields["banco_origen"];
            $banco_destino=$resultado->fields["banco_destino"];
            $monto=$resultado->fields["monto"];
            $observaciones=$resultado->fields["observaciones"];
   }        

echo $html_header;


if ($id_transferencias){
   $sql="select * from log_transferencias where id_transferencias=$id_transferencias
         order by fecha ASC";
   $log=sql($sql) or fin_pagina();
         
   }

$sql="select * from bancos.tipo_banco where activo=1";
$bancos=sql($sql) or fin_pagina();
$cantidad_banco=$bancos->recordcount();
?>
<script>
  function control_datos(){
  
  
  if (document.form1.banco_origen.value==document.form1.banco_destino.value)
         {
         alert("El banco origen debe ser distinto que el banco destino");
         return false;
         }
  if (document.form1.banco_origen.value==-1)     
         {
         alert("Debe elegir un banco origen");
         return false;
         }
  if (document.form1.banco_destino.value==-1)     
         {
         alert("Debe elegir un banco destino");
         return false;
         }         
   
  
   if (isNaN(document.form1.monto.value) || (document.form1.monto.value==""))      
       {
       alert("Debe Ingresar un monto válido");
       return false;
       }
  return true;
  }
</script>
<form name=form1 method=post>
<input type=hidden name=id_transferencias  value="<?=$id_transferencias?>">
<input type=hidden name=pagina value="<?=$pagina?>">
<input type=hidden name=cmd value="<?=$cmd?>">
<?
if ($msg)  Aviso($msg);
?>
  <table width=70% align=center class=bordes>
     <tr id=mo>
        <td>Transferencias</td>
     </tr>
     <tr>
       <td>
         <table width=100%>
           <tr>
            <td>
            <input type=checkbox name=mostrar_log onclick="javascript:(this.checked)?Mostrar('log'):Ocultar('log')">
            &nbsp;<b>Ver log</b>
            </td>
           </tr>
           <tr> 
            <td>
            <?
            if ($id_transferencias) {
            ?>
                
                <div  id=log style='display:none'>
                    <table width=100% align=center border=0>
                    <tr id='mo'>
	                    <td>Fecha</td>
	                    <td>Usuario</td>
	                    <td>Descripción</td>
                    </tr>
                    <?
                    for($i=0;$i<$log->recordcount();$i++){
                    ?>
                    <tr <?=atrib_tr()?>>
                       <td align=center><?=fecha($log->fields["fecha"])?></td>
                       <td align=center><?=$log->fields["usuario"]?></td>
                       <td align=center><?=$log->fields["tipo"]?></td>
                    </tr>
                    <?
                    $log->movenext();
                    }
                    ?>
                    </table>
                </div>
            <?
            }
            ?>
            </td>
          </tr>           
         
         </table>
       </td>
     </tr>
      <tr>
        <td>
           <table width=100% align=center>
              <tr>
                 <td width=35% id=ma_sf>Banco Origen</td>
                 <td>
                   <select name=banco_origen>
                      <option value=-1>Seleccione un Banco</option>
                      <?
                      for ($i=0;$i<$cantidad_banco;$i++){
                        $nombre_banco=$bancos->fields["nombrebanco"];  
                        $id_banco=$bancos->fields["idbanco"];
                        if ($id_banco==$banco_origen) $selected=" selected";
                                              else    $selected=" ";
                      ?>
                       <option value="<?=$id_banco?>" <?=$selected?>><?=$nombre_banco?></option>
                      <?
                      $bancos->movenext();
                      }
                      ?>
                   </select>
                 </td>
              </tr>
              <tr>
                 <td id=ma_sf>Banco Destino</td>
                 <td>
                   <select name=banco_destino>
                      <option value=-1>Seleccione un Banco</option>
                      <?
                      $bancos->move(0);
                      for ($i=0;$i<$cantidad_banco;$i++){
                        $nombre_banco=$bancos->fields["nombrebanco"];  
                        $id_banco=$bancos->fields["idbanco"];
                        if ($id_banco==$banco_destino) $selected=" selected";
                                              else    $selected=" ";

                      ?>
                       <option value="<?=$id_banco?>" <?=$selected?>><?=$nombre_banco?></option>
                      <?
                      $bancos->movenext();
                      }
                      ?>
                   </select>
                 </td>
              </tr>
              <tr>
                 <td id=ma_sf>Monto</td>
                 <td><input type=text name=monto value="<?=number_format($monto,"2",".","")?>" size=10></td>
              </tr>
              <tr>
                 <td id=ma_sf colspan=2>Observaciones</td>
              </tr>
              <tr>
                 <td colspan=2>
                 <textarea  name=observaciones rows=5 style="width:100%"><?=$observaciones?></textarea>
                 </td>
              </tr>
              
           </table>
        </td>
     </tr>
     <tr>
<?
if ($pagina=="listado")
            { 
            $onclick = "document.location='bancos_listado_transferencias.php';";
            $value = "Volver";
            }
            else
            {
            $onclick = "window.close();";
            $value = "Cancelar";
            }
$disabled_historial = "";
$disabled_en_proceso    = "";
$disabled_guardar   = "";
            
if ($cmd=="historial")
          {
              $disabled_historial = "disabled";
              $disabled_en_proceso    = "disabled";
              $disabled_guardar   = "disabled";
          }            
if ($cmd=="en_proceso")
          {
              
              $disabled_en_proceso    = "disabled";
              $disabled_guardar   = "disabled";
          }  
if (!$pagina)
         {
              $disabled_historial = "disabled";
              $disabled_en_proceso    = "disabled";
             
         }                    
          
?>
     
     
        <td align=center>
                <table width=90% align=center>
                   <tr>
                     <td>
                       <input type=submit name=guardar value=Guardar style="width:100px" <?=$disabled_guardar?>   onclick="return control_datos()">
                     </td> 
                     <td>
                        <input type=submit name=en_proceso value="En Proceso" style="width:100px" <?=$disabled_en_proceso?>>
                     </td>
                     <td>
                        <input type=submit name=historial value="Historial" style="width:100px"  <?=$disabled_historial?>>
                     </td>
                     <td>
                    <td>
                     <input type=button name=Cancelar value=<?= $value ?> style="width:100px" onclick="<?=$onclick?>">
                    </td>  
                  
                   </tr>
                </table>   
        </td>
     </tr>
  </table>
  
  
 
 
</form>
<?
echo fin_pagina();
?>
 