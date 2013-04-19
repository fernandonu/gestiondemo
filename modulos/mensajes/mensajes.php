<?php
require_once("../../config.php");
require_once("funciones.php");

switch ($_POST['boton'])
{case "Enviar Nuevo Mensaje":{require_once("../mensajes/nuevo_mens.php");
							  break;
							 }
 case "Reenviar Mensaje": {require_once("../mensajes/redirige.php");
							break;
						   }
 case "Borrar Mensaje":{require_once("../mensajes/borrar.php");
						break;
					   }
 case "Mensajeria":{require_once('../mensajes/mensajeria.php');
					exit;
					break;
					   }

default:{ $filas_encontradas=0;
echo $html_header;
?>
<script>
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
	src.style.backgroundColor=color_entrada;src.style.cursor="hand";
}
function bajo(src,color_default) {
	src.style.backgroundColor=color_default;src.style.cursor="default";
}
function comprueba()
{var i;
if (document.form.cantr.value==0)
{alert("Debe seleccionar un mensaje");
 return false;
}
 if (document.form.cantr.value==1)
 {if(document.form.radio.checked)
  return true;
 }
 for(i=0;i<document.form.radio.length;i++)
 {if(document.form.radio[i].checked)
  return true;
 }//for
alert("Debe seleccionar un mensaje");
return false;
}

function borrar()
{var valor;
 if (!comprueba())
 return false;
 else
 {valor=prompt('Dime el motivo por el cual desestimas este mensaje','');
  if ((valor==null) || (valor==""))
   return false;
  else
  {window.document.form.mensaje.value=valor;
   return true;
  }
 }
}
function controlnoc(){
  alert("Hay ordenes de compra que están por vencer.");
}
function controlvenc(){
  alert("Hay ordenes de compra vencidas.");
}

</script>
<?php
//este codigo es el que controla si ordenes de compra por vencer o mensajes que venecen hoy
if(($_POST['boton1']!='Mensajeria')&&($_ses_mensajes_primera_v != 1)){
   phpss_svars_set("_ses_mensajes_primera_v",1);
   $fecha_actual=date("Y-m-d H:m:s");
   $fecha_proxima=date("Y-m-d",mktime(0,0,0,date("m"),(date("d")+7),date("Y")));
   $sql = "select count(id_mensaje) as cant from mensajes where tipo1='LIC' and tipo2='NOC' and terminado='f' and desestimado='f' and usuario_destino='".$_ses_user['login']."' and fecha_vencimiento < '$fecha_actual'";
   $result=$db->Execute($sql) or die($db->ErrorMsg());
   $vench=$result->fields["cant"];
   $sql = "select count(id_mensaje) as cant from mensajes where tipo1='LIC' and tipo2='NOC' and terminado='f' and desestimado='f' and usuario_destino='".$_ses_user['login']."' and fecha_vencimiento <= '$fecha_proxima'";
   $result=$db->Execute($sql) or die($db->ErrorMsg());
   $noc=$result->fields["cant"];

/*   list($aa,$ma,$da) = explode("-",$fecha_a);
   db_tipo_res('a');
   $sql="select * from usuarios where login='".$_ses_user_login."'";
   $result=$db->Execute($sql) or die($db->ErrorMsg());
   $user_name=$result->fields['nombre'];
   $sql="select tipo1, tipo2, fecha_vencimiento from mensajes where terminado='f' and desestimado='f' and usuario_destino='".$user_name."'";
   db_tipo_res('a');
   $result2 = $db->Execute($sql) or die($db->ErrorMsg());
   $noc=0;
   $vench=0;
  while (!$result2->EOF)
  {
	 $fecha_v=substr($result2->fields['fecha_vencimiento'],0,10);
	 $hora_v=substr($result2->fields['fecha_vencimiento'],11,16);
	 list($a,$m,$d) = explode("-",$fecha_v);
	 list($hv,$miv,$sv)= explode(":",$hora_v);

	 if(($result2->fields['tipo1']=='LIC')&&($result2->fields['tipo2']=='NOC'))
	   {
		for($i=0;$i<=7;$i++)
		{
		 $fecha=date( "Y-m-d", mktime(0,0,0,$ma,$da+$i,$aa));
		  if($fecha_v==$fecha) $noc=1;
		  if (($aa>$a) || (($aa==$a) && ($ma>$m)) || (($aa==$a) && ($ma==$m) && ($da-$d>1)))
			$vench=1;
		  elseif(($aa==$a)&&($ma==$m)&& ($da==$d)&&(($ha>=$hv)&&($mia>$miv))) $vench=1;
		}//for

	 }//if
  $result2->MoveNext();
  }//while
  */
 if($noc){
 ?>
 <script language='JavaScript'>
  controlnoc();
 </script>
 <?
 }//if noc

 if($vench){
 	?>
   <script language='JavaScript'>
	 controlvenc();
   </script>
 <? }//vence hoy
 }//if

 // actualizo fecha de recibido y bit de recibido de los que no lo tienen
$sql1="select id_mensaje,fecha_recibo from mensajes where recibido='f' or fecha_recibo is null and usuario_destino='".$_ses_user['login']."'";
$result1=$db->Execute($sql1) or die($db->ErrorMsg());
$fecha_r=date("Y-m-d H:i:s");
while (!$result1->EOF)
{
 if($result1->fields['fecha_recibo']==''){
   $sql="update mensajes set fecha_recibo='".$fecha_r."' where id_mensaje=".$result1->fields['id_mensaje'];
   $result=$db->Execute($sql) or die($db->ErrorMsg());
   }
$result1->MoveNext();      
}
 ?>
<form name="form" method="post" action="mensajes.php">
<center>
    <table width="90%" border="0">
      <tr bgcolor="#c0c6c9"> 
        <td width="619" height="21" valign="top"> <left><font color="#006699" size="2" face="Arial, helvetica, sans-serif"><b>&nbsp&nbspTareas:  (Hora: <?PHP echo $juan1=date("H");?>:<?php echo $juan2=date("i");?>)
          </b></font></left></td>
        <td width="72" valign="top"> 
          <div align="right">
		   <?php if(($_ses_user['login']=='corapi')||($_ses_user['login']=='juanmanuel')||($_ses_user['login']=='carlos')||($_ses_user['login']=='nazabal')||($_ses_user['login']=='programador') || ($_ses_user['login']=='test'))
           echo'<input style="border-style: outset;	border-width: 1px; border-color: #000000; background-color: #F5F5F5;
				color: #000000;font-size=8pt;text-align: center;cursor:hand;" type="submit" name="boton" value="Mensajeria">';?>
          </div>
        </td>
      </tr>
    </table>
    <table border="0" cellspacing="2" cellpadding="0" width="90%">
	  <tr bgcolor="#006699">
        <td width="26" height="19" valign="top">&nbsp;</td>
        <td width="129" valign="top"> 
          <center>
            <a style="text-decoration:none" href=<?php echo "mensajes.php?est=0"; ?>><font size="2" family="helvetica, sans-serif" color="#c0c6c9"><b>Fecha entrega</b></font></a> 
          </center>
        </td>
        <td width="391"> 
          <center>
            <a  style="text-decoration:none" href=<?php echo "mensajes.php?est=1"; ?>><font size="2" family="helvetica, sans-serif" color="#c0c6c9"><b>Mensaje</b></font></a> 
          </center>
        </td>
        <td width="145"> 
          <a  style="text-decoration:none " href=<?php echo "mensajes.php?est=2"; ?>><div align="center"><font size="2" family="helvetica, sans-serif" color="#c0c6c9"><b>Vencimiento</b></font></div></a>
        </td>
      </tr>
    </table>
		  
		  <div style="position:relative; width:100%; height:55%; overflow:auto;">
          
      <table border="0" cellspacing="2" cellpadding="0" width="90%">
        <?php
		   //$fecha_actual=date("Y/m/d H:i:s");
		   $fecha_actual=date("Y/m/d");
		   $hora_actual=date("H:i");
		   //$fecha_a=substr($fecha_actual,0,10);
		   //$hora_a=substr($fecha_actual,10,16);
			list($aa,$ma,$da) = explode("/",$fecha_actual);
			list($ha,$mia)= explode(":",$hora_actual);
			switch($est){
			  case "0": $orden=" order by fecha_entrega";break;
			  case "1": $orden=" order by comentario";break;
			  case "2": $orden=" order by fecha_vencimiento";break;
			  default:$orden=" order by fecha_recibo desc";break;
			}
			$sql="select tipo1,recibido,id_mensaje,comentario ,titulo, fecha_entrega, fecha_vencimiento,fecha_recibo from mensajes where terminado='f' and desestimado='f' and usuario_destino='".$_ses_user['login']."'".$orden;
			$result=$db->Execute($sql) or die($db->ErrorMsg());
	        $cantidad+=$result->RecordCount();
	   			$i=0;
	   			while(!$result->EOF){ 
               	if ($i==0)
	   				{$color=$bgcolor2;
	    			 $color2=$bgcolor1;
	    			 $i=1;
	   				}
	   			else
	   			{$color=$bgcolor1;
	    		 $color2=$bgcolor2;
	    		 $i=0;
	   			}
			 $fecha_v=substr($result->fields['fecha_vencimiento'],0,10);
             $hora_v=substr($result->fields['fecha_vencimiento'],11,16);
			 list($a,$m,$d) = explode("-",$fecha_v);
             list($hv,$miv,$sv)= explode(":",$hora_v);
			 if (($aa>$a) || (($aa==$a) && ($ma>$m)) || (($aa==$a) && ($ma==$m) && ($da-$d>=1)))
				$color1="FF0000";//rojo
			 elseif((($aa==$a)&&($ma==$m)&&($da==$d)&&($ha>$hv))||(($aa==$a)&&($ma==$m)&&($da==$d)&&($ha==$hv)&&($mia>$miv))) $color1="FF0000";  // saque esto &&($mia>$miv) porque no funciona con la condicion and
			 else $color1=''; //rojo
			 if ((($aa==$a)&&($ma==$m)&&(($da+1)==$d))||(($aa==$a)&&($ma+1==$m)&&($d==1)&&(($da==30)||($da==31)))) $color1="FFFF00"; //amarillo
			 elseif (($aa==$a)&&($ma==$m)&&($da==$d)&&(($ha<$hv)||(($ha==$hv)&&($mia<=$miv)))) $color1="FFFF00"; //amarillo

             if  ($result->fields['recibido']=='f') $color1="00FF00";  //verde
			 if ($result->fields['fecha_recibo']=="")
 					{$filas_encontradas++;
  					 $id[]=$result->fields['id_mensaje'];
 					}
 				
         ?>
		<input type="hidden" name="tipo1" value="<?php echo $result->fields['tipo1']; ?>">
        <a href="ver_mens.php?id_mensaje=<? echo $result->fields['id_mensaje'];?>&donde=0"> 
        <tr bgcolor="<?php echo $color; ?>"  title="<?php echo $result->fields['titulo']; ?>" onMouseOver="sobre(this,'#FFFFFF');" onMouseOut="bajo(this,'<? echo $color;?>' );"> 
          <td width="24" height="18" valign="top" bgcolor="<?php echo $color1;?>" > 
            <input type="radio" name="radio" value="<?php echo $result->fields['id_mensaje'] ?>">
          </td>
          <td width="131" valign="top" > 
            <center>
              <font size=2 color="<?php echo $color2; ?>"> 
              <?php 
			  $fecha1=fecha(substr($result->fields['fecha_entrega'],0,10));
			  $tiempo1=substr($result->fields['fecha_entrega'],10,18);
			  echo $fecha1.$tiempo1;
			  ?>
              </font> 
            </center>
		  </td>
          <td width="388" valign="top"> <font size=2 color="<?php echo $color2; ?>"> 
            <center>
              <?php echo $result->fields['comentario'];?>
            </center>
            </font> </td>
          <td width="145" valign="top" bgcolor="<?php echo $colorfv;?>"><font size=2 color="<?php echo $color2; ?>"> 
            <center>
              <?php 
					$fecha=fecha(substr($result->fields['fecha_vencimiento'],0,10));
					$tiempo=substr($result->fields['fecha_vencimiento'],10,18);
					echo $fecha.$tiempo;?>
            </center>
            </font></td>
        </tr>
        </a> 
        <input type="hidden" name="comentario[<?php echo $result->fields['id_mensaje']; ?>]" value="<?php echo $resultado['comentario']; ?>">
        
		<? 	 
   $result->MoveNext();
	}//while 
?>
	  </table>
          
    </div>
   <hr size="10">
    <table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='90%'>
      <tr> 
		<td width=19 valign="top" height=23 bgcolor='#FFFF00' bordercolor='#000000'>&nbsp;</td>
        <td width=237 valign="top" bordercolor='#FFFFFF'><font face="Arial, Helvetica, sans-serif" size="2">Mensaje 
          próximo a vencer</font></td>
        <td width=19 valign="top" bgcolor='#00FF00' bordercolor='#000000'>&nbsp;</td>
        <td width="147" valign="top" bordercolor='#FFFFFF'> <font size="2" face="Arial, Helvetica, sans-serif">Mensaje 
          Nuevo</font> </td>
        <td width=19 valign="top" bgcolor='#FF7878' bordercolor='#000000'>&nbsp;</td>
        <td width="193" valign="top" bordercolor='#FFFFFF'><font size="2" face="Arial, Helvetica, sans-serif">Mensaje 
          vencido</font></td>
      </tr>
    </table><br>
<input type="hidden" name="cantr" value="<?PHP echo $cantidad; ?>">
<input type="hidden" name="mensaje" value="">
<input type="submit" name="boton" value="Enviar Nuevo Mensaje">
&nbsp&nbsp&nbsp<input type="submit" name="boton" value="Reenviar Mensaje" onClick="return comprueba();">
&nbsp&nbsp&nbsp<input type="submit" name="boton" value="Borrar Mensaje" onClick="return borrar();">
</center>
</form>
</body>
</html>
<?php
 }
}// fin switch

?>