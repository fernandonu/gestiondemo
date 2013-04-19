<?php

$id_mensaje=$_GET['id_mensaje'];

require_once("../../config.php");

switch ($_POST['Submit']){
	case "Volver":{ 
					if($donde==1)  header('location: ./mensajeria.php');
	    			if($donde==0)  header('location: ./mensajes.php');
	    			exit;
	   				break;
	}
   
    default:{ 

?>

<html>
<head>
<title>Ver Mensaje</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body bgcolor="#E0E0E0">
<table width="90%" border="0" align="center">
  <tr bgcolor="#c0c6c9">
      <td>
          
      <div align="left"><font color="#006699" size="2" face="Arial, helvetica, sans-serif"><b>&nbsp&nbspVer 
        mensaje </b></font></div>
      </td>
    </tr>
  </table>
<center>
  <?php
	db_tipo_res('a');
    $actualizar_mensaje="UPDATE mensajes set recibido='t' where id_mensaje=".$id_mensaje;
	$ssql_busca="select * from mensajes where id_mensaje=".$id_mensaje;
 	$result=$db->Execute($ssql_busca) or die($db->ErrorMsg());
	
// 	if (!$resultado['recibido'])
// 	{
      $db->Execute($actualizar_mensaje) or die($db->ErrorMsg());
// 	}
 	?>
  <input type="hidden" name="id_m" value="<? echo $id_mensaje;?>">
  <input type="hidden" name="tipo_m" value=0>
  <input type="hidden" name="tipo2" value='MRU'>
	<br>
  <table width="704" border="0">
    <tr> 
      <td valign="top" width="43"><b>Tipo1:</b></td>
      <td colspan="2" valign="top" > 
        <div align="left"> 
          <? switch($result->fields['tipo1']){
		         case 'MCP': echo 'Mensaje de caracter personal';break;   
		         case 'ODP': echo 'Orden de Producción';break;   
				 case 'LIC': echo 'Licitaciones';break;  
				 }
		  ?>
        </div>
      </td>
      <td width="43" valign="top" ><b>Tipo2:</b></td>
      <td valign="top" colspan="2" > 
        <?
         db_tipo_res('a');
         $sql_tipo2="select titulo from tipo_de_mensaje where tipo2='".$result->fields['tipo2']."'";
         $result_sql=$db->Execute($sql_tipo2) or die($db->ErrorMsg());
         echo $result_sql->fields['titulo'];
         ?>
      </td>
    </tr>
    <tr> 
      <td valign="top" colspan="2">&nbsp;</td>
      <td valign="top" width="151" > 
        <div align="left"></div>
      </td>
      <td valign="top" colspan="2" >&nbsp;</td>
      <td valign="top" width="228" >&nbsp; </td>
    </tr>
    <tr> 
      <td valign="top" colspan="2"><b>&nbsp;Id mensaje:</b></td>
      <td valign="top" width="151" > 
        <div align="left"> 
          <? echo $result->fields['id_mensaje']; ?>
        </div>
      </td>
      <td valign="top" colspan="2" ><b>Fecha Entrega:</b></td>
      <td valign="top" width="228" > 
        <?php 
		   $fecha=substr($result->fields['fecha_entrega'],0,10); 
		   substr($result->fields['fecha_entrega'],10,16);
		   list($a,$m,$d) = explode("-",$fecha);
		   echo $d.'/'.$m.'/'.$a.substr($result->fields['fecha_entrega'],10,16);
		?>
      </td>
    </tr>
    
	<tr> 
      <td valign="top" colspan="2" > 
        <div align="left"><b>Numero Mensaje: </b></div>
      </td>
      <td valign="top" > 
        <? echo $result->fields['numero']; ?>
      </td>
      <td valign="top" colspan="2" ><b>Fecha Recibo:</b></td>
      <td valign="top" > 
        <?php 
        if($result->fields['fecha_recibo']!=''){
			$fecha=substr($result->fields['fecha_recibo'],0,10); 
			list($a,$m,$d) = explode("-",$fecha);
			echo $d.'/'.$m.'/'.$a.substr($result->fields['fecha_recibo'],10,16);
			}
		 else echo'-'; ?>
      </td>
    </tr>
    <tr> 
      <td valign="top" colspan="2" ><b>Numero Orden:</b></td>
      <td valign="top" > 
        <? echo $result->fields['nro_orden']; ?>
      </td>
      <td valign="top" colspan="2" ><b>Fecha Vencimiento:</b></td>
      <td valign="top" > 
        <?php
		$fecha=substr($result->fields['fecha_vencimiento'],0,10); 
		substr($result->fields['fecha_vencimiento'],10,16);
		list($a,$m,$d) = explode("-",$fecha);
		echo $d.'/'.$m.'/'.$a.substr($result->fields['fecha_vencimiento'],10,16);
		  ?>
      </td>
    </tr>
    <tr> 
      <td colspan="3" valign="top">&nbsp;</td>
      <td valign="top" colspan="2"><b>Fecha Terminado:</b></td>
      <td valign="top"> 
        <?php if($result->fields['fecha_terminado']!=''){ 
		        $fecha=substr($result->fields['fecha_terminado'],0,10); 
				substr($result->fields['fecha_terminado'],10,16);
		   		list($a,$m,$d) = explode("-",$fecha);
		   		echo $d.'/'.$m.'/'.$a.substr($result->fields['fecha_terminado'],10,16);
		        }
				else echo '-';
		?>
      </td>
    </tr>
    <tr> 
      <td colspan="3" valign="top" >&nbsp; </td>
      <td valign="top" colspan="2" ><b>Estado Final:</b></td>
      <td valign="top" > 
        <? if($result->fields['estado_final']!='') echo $result->fields['estado_final']; else echo '-';?>
      </td>
    </tr>
	 <tr> 
      <td colspan="3" valign="top" >&nbsp; </td>
      <td valign="top" colspan="2" >&nbsp;</td>
      <td valign="top" >&nbsp; </td>
    </tr>
    <tr> 
      <td colspan="2" valign="top"><b><u>Usuario Origen</u>:</b></td>
      <td valign="top"> 
        <b><? //obtengo el nombre del usuario dado el login 
		 $sql="select nombre from usuarios where login='".$result->fields['usuario_origen']."';";
		 $nombre_o=$db->Execute($sql) or die($db->ErrorMsg());
		 echo $nombre_o->fields['nombre'];
         //echo $result->fields['usuario_origen'];?></b>
      </td>
      <td colspan="2" valign="top"><b><u>Usuario Destino</u>:</b> </td>
      <td valign="top"> 
        <b><? $sql="select nombre from usuarios where login='".$result->fields['usuario_destino']."';";
		      $nombre_d=$db->Execute($sql) or die($db->ErrorMsg());
		      echo $nombre_d->fields['nombre']; 
        //echo $result->fields['usuario_destino'];?></b>
      </td>
    </tr>
    
    <tr> 
      <td valign="top" colspan="2"><b>Usuario Finaliza:</b></td>
      <td colspan="4" valign="top"> 
        <? if($result->fields['usuario_finaliza']!='') {
        $sql="select nombre from usuarios where login='".$result->fields['usuario_finaliza']."';";
		$nombre_d=$db->Execute($sql) or die($db->ErrorMsg());
		echo $nombre_d->fields['nombre']; 
	    /*echo $result->fields['usuario_finaliza'];*/} else echo '-';?>
      </td>
    </tr>
    <tr> 
      <td valign="top" colspan="2"><b>T&iacute;tulo: </b></td>
      <td colspan="4" valign="top"> 
        <?php echo $result->fields['titulo'];?>
      </td>
    </tr>
    <tr> 
      <td valign="top" colspan="2"><b>Mensaje:</b></td>
      <td colspan="4" valign="top" border=1> 
        <div align="left"> 
          <?php echo $result->fields['comentario']; ?>
        </div>
      </td>
    </tr>
    <tr> 
      <td valign="top" colspan="2"><b>Este mensaje:</b></td>
      <td colspan="4" valign="top"> 
        <? if($result->fields['termnado']=='t') echo 'fue Terminado por el Sistema';
		   elseif($result->fields['desetimado']=='t') echo 'fue Desestimado por el Usuario';
		   elseif(($result->fields['recibido']=='f')) echo 'No ha sido recibido';
		   else echo 'Ha sido recibido.';
		?>
      </td>
    </tr>
    <tr> 
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
  </table>
  <br>
    <table width="704" border="0">
	<tr> 
	
      <td height="75" colspan="4" valign="top"> 
        <form name="form" method="post" action="ver_mens.php">
          <div align="left"> 
            <hr>
            <input type="Submit" name="Submit" value="Volver">
          </div>
        </form>
      </td>
    </tr>
  </table>
    
    </center>
  <center>
  </center>

<? }
}
?>
</body>
</html>