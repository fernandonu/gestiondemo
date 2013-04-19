<?php
/*
$Author: fernando $
$Revision: 1.1 $
$Date: 2005/10/17 22:51:53 $
*/

/*
Funciones que se van a utilizar en la parte de transferencias entre depositos

*/

function insertar_transferencia($banco_origen,$banco_destino,$monto=0,$observaciones=""){
global $db,$_ses_user;
    
          $usuario=$_ses_user["name"];
          $fecha=date("Y-m-d");
    
    
          $db->starttrans();
                    
          $sql="select nextval('bancos.transferencias_id_transferencias_seq') as id_transferencias";
          $res=sql($sql) or fin_pagina();
          
          $id_transferencias=$res->fields["id_transferencias"];
                  
          
          $campos="id_transferencias,banco_origen,banco_destino,monto,observaciones,id_estado_transferencias";
          $values="$id_transferencias,$banco_origen,$banco_destino,$monto,'$observaciones',1";
          $sql="insert into  bancos.transferencias ($campos) values ($values)";
          sql($sql) or fin_pagina();
          
          
          $tipo="Creación";
          
          $campos="usuario,fecha,tipo,id_transferencias";
          $values="'$usuario','$fecha','$tipo',$id_transferencias";
          $sql="insert into log_transferencias ($campos) values ($values)";
          sql($sql) or fin_pagina();
          
          $db->completetrans(); 
          $msg="Se creo la transferencia con Exito!";
          return $msg;
          
} // de la funcion insertar transferencia

     
function modificar_transferencia($id_transferencias,$banco_origen,$banco_destino,$monto=0,$observaciones=""){     
    global $db,$_ses_user;
    $db->starttrans();
    
          $usuario=$_ses_user["name"];
          $fecha=date("Y-m-d H:i:s");    
    
          $sql=" update transferencias set 
                 banco_origen=$banco_origen,banco_destino=$banco_destino,
                 monto=$monto,observaciones='$observaciones'
                 where id_transferencias=$id_transferencias
                 ";
          sql($sql) or fin_pagina();       
          
          $tipo=" Modifica";
          $campos="usuario,fecha,tipo,id_transferencias";
          $values="'$usuario','$fecha','$tipo',$id_transferencias";
          $sql="insert into log_transferencias ($campos) values ($values)";
          sql($sql) or fin_pagina();
          
          $db->completetrans();
          $msg=" Se modifico la transferencia con Éxito!";
          return $msg;
 } // de la funcion modificar transferencia
 
 
 
function cambiar_estado_transferencia($id_transferencias,$id_estado_transferencias){
    global $db,$_ses_user;
          
          $db->starttrans();
          
          $usuario=$_ses_user["name"];
          $fecha=date("Y-m-d H:i:s");    
          
          
          $sql = "update transferencias set id_estado_transferencias=$id_estado_transferencias
                where id_transferencias=$id_transferencias";
          sql($sql) or fin_pagina();
          
          switch ($id_estado_transferencias) {
              case 2:
                     $tipo = " Paso a Estado: En Proceso";
                     break;
              case 3:
                    $tipo =  "Paso a Estado: Historial";      
                    break;
              default:
                    $tipo="";      
          }

          $campos = "usuario,fecha,tipo,id_transferencias";
          $values = "'$usuario','$fecha','$tipo',$id_transferencias";
          $sql = "insert into log_transferencias ($campos) values ($values)";
          sql($sql) or fin_pagina();
       
        $db->completetrans();
        
        $msg=" Se cambio el estado a la transferencia con Éxito!";
        return ($msg);
} 
?>