<?

/*
AUTOR: MAC

Esta página muestra el resultado de cerrar la chequera

MODIFICADO POR:
$Author: nazabal $
$Revision: 1.1 $
$Date: 2004/12/17 20:04:07 $
*/

require_once("../../config.php");

echo $html_header;

 include("func.php");
 //$chequeras=$parametros['chequeras'];
 $tam=sizeof($chequeras);
 $contenido="";
 for($i=0;$i<$tam;$i++)
 {$cheques=array();
  $res=cerrar_chequera($chequeras[$i]);
  if($res==-1)
  {$contenido.="<table width='65%' align='center'>";
   $contenido.="<tr id=mo>";
   $contenido.="<td colspan='2'>";
   $contenido.="<b>SE HAN ENCONTRADO CHEQUES FALTANTES PARA LA CHEQUERA Nº ".$chequeras[$i]."</b>";
   $contenido.="</td>";
   $contenido.="</tr>";
   $tam1=sizeof($cheques);
   $cnr=1;
   for($j=0;$j<$tam1;$j++)
   {  if ($cnr==1)
      {$color2=$bgcolor2;
       $color=$bgcolor1;
       $atrib ="bgcolor='$bgcolor1'";
       $cnr=0;
      }
      else
      {$color2=$bgcolor1;
       $color=$bgcolor2;
       $atrib ="bgcolor='$bgcolor2'";
       $cnr=1;
      }
     $contenido.="<tr $atrib>";
     $contenido.="<td>";
     $contenido.="<b>Cheque Nº</b>";
     $contenido.="</td>";
     $contenido.="<td>";
     $contenido.="<b>".$cheques[$j]."</b>";
     $contenido.="</td>";
     $contenido.="</tr>";  
     $contenido.="</td>";
     $contenido.="</tr>"; 

   }//de for($j=0;$j<$tam;$j++)
   $contenido.="</table><br>";
  }//de if($cheques!=1) 
  elseif($res==1)
  {$contenido.="<table width='95%' align='center'>";
   $contenido.="<tr id=ma>";
   $contenido.="<td colspan='2'>";
   $contenido.="<font size=2>La Chequera Nº ".$chequeras[$i]." ha sido cerrada con éxito</font>";
   $contenido.="</td>";
   $contenido.="</tr>"; 
   $contenido.="</table><br>";
  }
  elseif($res==0)
  {$contenido.="<table width='95%' align='center'>";
   $contenido.="<tr id=mo>";
   $contenido.="<td colspan='2'>";
   $contenido.="<font size='1'>No se pudo cerrar la chequera Nº ".$chequeras[$i].", debido a un error interno</font>";  
   $contenido.="</td>";
   $contenido.="</tr>"; 
   $contenido.="</table><br>";
  } 
 }//de for($i=0;$i<$tam;$i++) 	 
 
?>
<div style='position:relative; width:100%; height:90%; overflow:auto;'>
<?=$contenido?>

</div>
<br>
<center>

   <input type="button" name="cerrar" value="Volver" onclick="document.location='bancos_listado_chequeras.php';">

 </center>

</body>
</html> 

