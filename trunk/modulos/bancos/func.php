<?
/*AUTOR: MAC

Esta página provee funciones para la parte de chequeras y para bancos en general

MODIFICADO POR:
$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2004/03/02 21:09:26 $
*/

//Intenta cerrar la chequer. Si lo logra devuelve 1. Si da error al cerrar
//devuelve 0. Y si faltan cheques para esa chequera, guarda los nros faltantes
//en un arreglo global (cheques).

function cerrar_chequera($id_chequera)
{global $db,$cheques;
  $query="select primer_cheque,ultimo_cheque,idbanco from chequera where id_chequera=$id_chequera";
  $resultado=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer datos de chequera.$query");
  $primer_cheque=$resultado->fields['primer_cheque'];
  $ultimo_cheque=$resultado->fields['ultimo_cheque'];
  $id_banco=$resultado->fields['idbanco'];
 //para cada cheque dentro del rango entre $primer_cheque y $ultimo_cheque 
 //vemos si existe en la BD. Si existe, seguimos, sino, avisamos,
 //guardando el numero faltante en un arreglo.
 $num_cheque=$primer_cheque;
 
 $i=0;
 while($num_cheque<=$ultimo_cheque)
 {
  $query="select count(númeroch) as cant from cheques where númeroch=$num_cheque and idbanco=$id_banco";
  $result=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer número cheque $num_cheque");
  if($result->fields['cant']==0)
  {$cheques[$i]=$num_cheque;
   $i++;
  } 
  $num_cheque++;	
 }

 //si no hubo ningun cheque faltante
 //cambiamos el estado de la chequera a cerrada
 if($i==0)
 {
  $query="update chequera set cerrada=1 where id_chequera=$id_chequera";
  if($db->Execute($query))// or die($db->ErrorMsg()."<br>Error al actualizar la chequera");
   return 1;
  else 
   return 0; 
 }
 else
 {return -1;
 } 
 		
}	
?>