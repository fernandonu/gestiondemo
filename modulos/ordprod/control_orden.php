<?php
require_once("../../config.php");
$nro=$HTTP_GET_VARS['nro_orden'];
global $aprobada;
include("funciones.php");
$aprobada=0;
switch ($_POST['b_orden'])
{
 case "Para Autorizar" :{
	$sql="select recive from produccion_mail_aviso where envia='".$_ses_user['login']."' and boton='Para Autorizar'";
    $resultado=$db->execute($sql) or $error=$db->ErrorMsg()."<br>".$sql;
    $para=$resultado->fields['recive'];
    $sql="select nombre from ensamblador join orden_de_produccion_old on ensamblador.id_ensamblador=orden_de_produccion_old.id_ensamblador where orden_de_produccion_old.nro_orden_old=$nro";
    $resultado=$db->execute($sql) or $error=$db->ErrorMsg()."<br>".$sql;
    $ensamblador=$resultado->fields['nombre'];
    $mailtext=$_POST['contenido'];
    $asunto="Pedido de autorización para la orden de producción Nº $nro - Ensamblador: $ensamblador";
    $mail_header="";
    $mail_header .= "MIME-Version: 1.0";
    $mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
    $mail_header .= "\nFrom: Sistema Inteligente de CORADIR <>";
    $mail_header .="\nTo: $para";
    $mail_header .= "\nContent-Type: text/plain";
    $mail_header .= "\nContent-Transfer-Encoding: 8bit";
    $mail_header .= "\n\n" . $mailtext."\n";
    $mail_header .= "\n\n" . firma_coradir()."\n"; 
    if (!$error)
		mail("",$asunto,"",$mail_header);
	else
		error($error);
    break;
}
case "Guardar":{$aprobada=$_POST['aprobada'];
                 if ($aprobada==1)
                  {$query="update orden_de_produccion_old set estado=1 where nro_orden_old=".$nro.";";
                  $db->Execute($query) or Error($db->ErrorMsg());
                  require("pdf.php");
                  }
 	             if ($aprobada==3)
                  {$query="update orden_de_produccion_old set estado=2 where nro_orden_old=".$nro.";";
                  $db->Execute($query) or Error($db->ErrorMsg());
                  }
                 if ($aprobada==4)
                  {$query="update orden_de_produccion_old set estado=3 where nro_orden_old=".$nro.";";
                  $db->Execute($query) or Error($db->ErrorMsg());
                  }

                 $query="update orden_de_produccion_old set aprobada=".$aprobada.", observaciones='".$_POST['observaciones']."' where nro_orden_old=".$nro.";";
                 $db->Execute($query) or Error($db->ErrorMsg());
 	             header('location: ./ordenes_ver.php');
 	             break;
                 }
}//fin switch
if ($_POST['b_orden']=="Modificar Orden")
		   { include_once("./ordenes_modif.php");
			exit;
			}                                           
else {
?>
<html>
<head>
<title>Control Ordenes</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script languaje="javascript">
	function cuerpo(){
		var valor;
		valor=prompt("Ingrese el texto a enviar en el mail","");
		if (!valor)
		return false;
		document.all.contenido.value=valor;
		return true;
	}
</script>
<SCRIPT language='JavaScript' src="funciones.js"></SCRIPT>
<? cargar_calendario(); ?>
<?php
include("../ayuda/ayudas.php");
?>
</head>
<body bgcolor="#E0E0E0" text="#000000" style="margin:0">
<form name="form" action="" method=post>
<script language="JavaScript">
var winW=window.screen.Width;
var valor=(winW*25)/100;
</script>
<?php
 $ssql="SELECT estado, aprobada, factura_componente.nro_factura, fecha_inicio,id_licitacion,";
 $ssql.="fecha_entrega, cliente_final.nombre as nbre_cliente, lugar_entrega, producto,";
 $ssql.="configuracion_maquina.modelo as modelo_a, cantidad, adicionales,";
 $ssql.="ensamblador.nombre as nbre_ens, primera_maquina, ultima_maquina,orden_de_produccion_old.garantia ";
 $ssql.="FROM orden_de_produccion_old ";
 $ssql.="LEFT JOIN ensamblador USING (id_ensamblador) ";
 $ssql.="LEFT JOIN cliente_final USING (id_cliente) ";
 $ssql.="LEFT JOIN accesorios on accesorios.nro_orden=orden_de_produccion_old.nro_orden_old ";
 $ssql.="LEFT JOIN configuracion_maquina USING (id_configuracion) ";
 $ssql.="LEFT JOIN componentes USING (id_configuracion) ";
 $ssql.="LEFT JOIN factura_componente USING (id_componente) ";
 $ssql.="WHERE orden_de_produccion_old.nro_orden_old=$nro";
 $resultado = $db->Execute($ssql) or Error($ssql."<br>".$db->ErrorMsg());
 $garantia=$resultado->fields["garantia"];
 $adicional=$resultado->fields["adicionales"];
 $fecha=Fecha($resultado->fields["fecha_entrega"]);

?>
    <table width="90%" border="0">
    <tr>
    <td>
    <?php
     if ($resultado->fields["id_licitacion"]!="")
		{$lic=$resultado->fields["id_licitacion"];
   ?>
 		<b><font color="Red">Orden de Producciòn asociada con Licitación Nº <?php echo $lic; ?></font></b>
   <?php
		}
   else
	{
	?>
 		<b><font color="Red">Esta orden no esta asociada a ninguna licitacion</font></b>
 	<?php
	}
	?>
	</td>
	<td>
	</td>
      <tr bgcolor="#CCCCCC">
        <td width="313" height="20" valign="top">
          <div>&nbsp;<b>Orden N&ordm;:</b>
            <?PHP echo $nro; ?>
            <input type="hidden" name="nro_ord" value="<?PHP echo $nro; ?>">
          </div>
        </td>
          
        <td width="377" valign="top" height="20">
          <div align="right"><b>Fecha de inicio:</b>
            <?PHP
          //$finicio=date("d/m/Y");
          $finicio=Fecha($resultado->fields['fecha_inicio']);
          echo $finicio;
         ?>
            <input type="hidden" name="f_inicio" value="<?php $resultado->fields["fecha_inicio"]; ?>>
          </div>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td colspan="2" height="16" valign="top">&nbsp;<b>Estado:&nbsp;</b>
          <?php switch($resultado->fields["estado"]){
	        case 0: echo "Pendiente"; break;
			case 1: echo "En proceso"; break;
			case 2: echo "Terminada"; break;
			case 3: echo "ANULADA"; break;
			}
	 switch($resultado->fields["aprobada"]){
	        case 0: echo "&nbsp;-&nbsp;No aprobada"; break;
			case 1: echo "&nbsp;-&nbsp;Aprobada"; break;
			case 2: echo "&nbsp;-&nbsp;Rechazada"; break;
			}
	?>
        </td>
      </tr>

    </table>



<table width="90%" border="0">
  <tr bgcolor="CCCCCC">
    <td width="340" valign="top">
      <div align="left">&nbsp;<b>Ensamblador:</b>
        <?PHP echo $resultado->fields["nbre_ens"] ?>
      </div>
    </td>
    <td width="408" valign="top">
      <div align="right">&nbsp;<b>Fecha de Entrega:&nbsp;</b>
        <?php echo $fecha; ?>
      </div>
    </td>
  </tr>
</table>

<p><b>ClienteFinal:&nbsp;</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <?php echo $resultado->fields["nbre_cliente"] ?>
</p>

<p><b>Lugar de Entrega:</b>
  <?php echo $resultado->fields["lugar_entrega"]; ?>
</p>
  <br>

  <table width="90%" border="0">
    <tr  bgcolor="#CCCCCC">

    <td width="66">&nbsp;<b>Producto:</b></td>
      <td width="137">
	  <?php echo $resultado->fields["producto"]; ?>

      </td>
      <td width="58">&nbsp;</td>
      <td width="410">&nbsp;</td>
    </tr>
    <tr bgcolor="CCCCCC">
      <td width="66">

      <div align="left">&nbsp;<b>Modelo:</b></div>
      </td>
      <td width="137">


		 <?php echo $resultado->fields["modelo_a"]; ?>

      </td>
      <td width="58">

      <div align="right"><b>Cantidad:</b></div>
      </td>
      <td width="410">
        <?php echo $resultado->fields["cantidad"]; ?>
      </td>
    </tr>
  </table>
  <br>
  <table border="0" cellspacing="0" cellpadding="0" width="90%">
    <tr>
      <td width="601">&nbsp;<b> Numero de Serie:
        <?php echo $resultado->fields["primera_maquina"]."......".$resultado->fields["ultima_maquina"]; ?>
      </b></td>
      <td width="82">&nbsp;&nbsp;&nbsp;&nbsp;</td>
    </tr>
  </table>
  <br>
    <br>
   <div><b><font face="Georgia, Times New Roman, Times, serif">COMPONENTES
          </font></b></div>

<?
  /* selecciona  componentes por tipo y nro de orden */
 $ssql="select factura_componente.nro_factura, componentes.tipo as tipo_c, componentes.observaciones_componente as obs_comp,";
 $ssql.= " componentes.esp1 as esp1, componentes.esp2 as esp2,componentes.esp4 as esp4, componentes.esp3,componentes.garantia as garantia_c, componentes.nombre_proveedor as nbre_prov from ";
 $ssql.=" configuracion_maquina, orden_de_produccion_old, componentes, factura_componente where ";
 $ssql.=" factura_componente.id_componente=componentes.id_componente and configuracion_maquina.id_configuracion=componentes.id_configuracion";
 $ssql.=" and configuracion_maquina.id_configuracion=orden_de_produccion_old.id_configuracion and orden_de_produccion_old.nro_orden_old=".$nro." order by componentes.tipo" ;
 //db_tipo_res('a');
 $resultado = $db->Execute($ssql) or Error($ssql);
 $cont=0;
 while (!$resultado->EOF){
  $temp[$cont]->obs_soft=$resultado->fields["obs_soft"];
  $temp[$cont]->tipo_c=$resultado->fields["tipo_c"];
  $temp[$cont]->obs_comp=$resultado->fields["obs_comp"];
  $temp[$cont]->nro_factura=$resultado->fields["nro_factura"];
  $temp[$cont]->esp1=$resultado->fields["esp1"];
  $temp[$cont]->esp2=$resultado->fields["esp2"];
  $temp[$cont]->esp3=$resultado->fields["esp3"];
  $temp[$cont]->esp4=$resultado->fields["esp4"];
  $temp[$cont]->garantia_c=$resultado->fields["garantia_c"];
  
  $temp[$cont]->nbre_prov=$resultado->fields["nbre_prov"];
  $cont++;
  $resultado->MoveNext(); 
 }

 ?>
    <table border="0" cellspacing="3" cellpadding="0" width="90%">
      <tr BGCOLOR="#006699">
        <td height="42" valign="top" colspan="4">
          <div><b><font size="3" align="center" color='#cccccc'>Descripci&oacute;n</font></b></div>
        </td>
        <td width="109" valign="top" >
          <div><b><font size="3" align="center" color='#cccccc'>Observaciones</font></b></div>
        </td>
        <td valign="top">
          <div><b><font size="3" align="center" color='#cccccc'>Proveedor</font></b></div>
        </td>
        <td width="51" valign="top">
          <div><b><font size="3" align="center" color='#cccccc'>NºFact</font></b></div>
        </td>
        <td width="160" valign="top">
          <div><b><font size="3" align="center" color='#cccccc'>Garant&iacute;a</font></b></div>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td width="104" height="46" valign="top"><b>&nbsp;Sistema &nbsp;Operativo</b></td>
        <td width="60">
          <div>
            <div align="center">
              <?php echo $temp[0]->esp1;?>
            </div>
          </div>
        </td>
        <td colspan="2">
          <div align="center">
            <?php echo $temp[0]->esp2; ?>
          </div>
        </td>
        <td width="109">
          <div align="center">
            <?php echo $temp[0]->obs_comp; ?>
            <div align="center"></div>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[0]->nbre_prov; ?>
          </div>
        </td>
        <td width="51">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[0]->nro_factura; ?>
            </div>
          </div>
        </td>
        <td width="160">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[0]->garantia_c;?>
            </div>
          </div>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC" >
        <td valign="top" height="46" width="104"><b>&nbsp;Tarjeta Madre</b></td>
        <td width="60">
          <div>
            <div align="center">
              <?php echo $temp[1]->esp1; ?>
            </div>
          </div>
        </td>
        <td colspan="2">
          <div align="center">
            <?php echo $temp[1]->esp2; ?>
          </div>
        </td>
        <td width="109">
          <div align="center">
            <?php echo $temp[1]->obs_comp; ?>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[1]->nbre_prov; ?>
          </div>
        </td>
        <td width="51">
          <div align="center">
            <div align="center">&nbsp;
              <?php echo $temp[1]->nro_factura;  ?>
            </div>
          </div>
        </td>
        <td width="160">
          <div align="center">
            <div align="center">&nbsp;
              <?php echo $temp[1]->garantia_c;?>
            </div>
          </div>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td height="38" valign="top" width="104">&nbsp;<b>Placa de video</b></td>
        <td width="60">
          <div align="center" >
            <?php echo $temp[2]->esp1; ?>
          </div>
        </td>
        <td colspan="2">
          <div align="center">
            <?php echo $temp[2]->esp2; ?>
          </div>
        </td>
        <td width="109">
          <div align="center">
            <?php echo $temp[2]->obs_comp; ?>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[2]->nbre_prov; ?>
          </div>
        </td>
        <td width="51">
          <div align="center">
            <div align="center">&nbsp;
              <?php echo $temp[2]->nro_factura;  ?>
            </div>
          </div>
        </td>
        <td width="160">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[2]->garantia_c;;?>
            </div>
          </div>
        </td>
      <tr bgcolor="#CCCCCC">
        <td height="38" valign="top" width="104">&nbsp;<b>Placa de sonido</b></td>
        <td colspan="3">
          <div align="center">
            <?php echo $temp[3]->esp1; ?>
          </div>
        </td>
        <td width="109">
          <div align="center">
            <?php echo $temp[3]->obs_comp; ?>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[3]->nbre_prov; ?>
          </div>
        </td>
        <td width="51">
          <div align="center">&nbsp;
            <?php echo $temp[3]->nro_factura; ?>
          </div>
        </td>
        <td width="160">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[3]->garantia_c;?>
            </div>
          </div>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td height="47" valign="top" width="104">&nbsp;<b>LAN</b></td>
        <td width="60">
          <div align="center">
            <?php echo $temp[4]->esp1 ?>
          </div>
        </td>
        <td colspan="2">
          <div align="center">
            <?php echo $temp[4]->esp2; ?>
          </div>
        </td>
        <td width="109">
          <div align="center">
            <?php echo $temp[4]->obs_comp; ?>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[4]->nbre_prov; ?>
          </div>
        </td>
        <td width="51">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[4]->nro_factura; ?>
            </div>
          </div>
        </td>
        <td width="160">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[4]->garantia_c;?>
            </div>
          </div>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td height="32" valign="top" width="104">&nbsp;<b>Modem </b></td>
        <td colspan="3">
          <div align="center">
            <?php echo $temp[5]->esp1; ?>
          </div>
        </td>
        <td width="109">
          <div align="center">
            <?php echo $temp[5]->obs_comp;?>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[5]->nbre_prov; ?>
          </div>
        </td>
        <td width="51">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[5]->nro_factura; ?>
            </div>
          </div>
        </td>
        <td width="160">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[5]->garantia_c;?>
            </div>
          </div>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td height="46" valign="top" width="104"><b>&nbsp;Micro</b></td>
        <td width="60">
          <div align="center">
            <?php echo $temp[6]->esp1; ?>
          </div>
        </td>
        <td colspan="2">
          <div align="center">
            <?php echo $temp[6]->esp2;?>
          </div>
        </td>
        <td width="109" >
          <div align="center">
            <?php echo $temp[6]->obs_comp; ?>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[6]->nbre_prov; ?>
          </div>
        </td>
        <td width="51">
          <div align="center">&nbsp;
            <?php echo $temp[6]->nro_factura;  ?>
          </div>
        </td>
        <td width="160">
          <div align="center">&nbsp;
            <?php echo $temp[6]->garantia_c;?>
          </div>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td height="46" valign="top" width="104">&nbsp;</td>
        <td width="60">
          <div align="center">
            <?php echo $temp[6]->esp3; ?>
          </div>
        </td>
        <td colspan="3">
          <div align="center">
            <?php echo $temp[6]->esp4;?>
          </div>
        </td>
        <td colspan="3" >&nbsp;</td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td height="42" valign="top" width="104"><b>&nbsp;Memoria</b></td>
        <td width="60">
          <div align="center">
            <?php echo $temp[7]->esp1; ?>
          </div>
        </td>
        <td width="58" >
          <div align="center">
            <?php echo $temp[7]->esp2;?>
          </div>
        </td>
        <td width="60">
          <div align="center">
            <?php echo $temp[7]->esp3;?>
          </div>
        </td>
        <td width="109" >
          <div align="center">
            <?php echo $temp[7]->obs_comp; ?>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[7]->nbre_prov; ?>
          </div>
        </td>
        <td width="51">
          <div align="center">&nbsp;
            <?php echo $temp[7]->nro_factura;  ?>
          </div>
        </td>
        <td width="160">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[7]->garantia_c;?>
            </div>
          </div>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC" >
        <td valign="top" height="46" width="104"><b>&nbsp;HDD</b></td>
        <td width="60">
          <div align="center">
            <div align="center">
              <?php echo $temp[8]->esp1; ?>
            </div>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[8]->esp2; ?>
          </div>
        </td>
        <td>
		 <div align="center">
          <?php echo $temp[8]->esp3;?>
		 </div>
        </td>
        <td width="109">
          <div align="center">
            <?php echo $temp[8]->obs_comp; ?>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[8]->nbre_prov; ?>
            <div align="center"></div>
            <div align="center"></div>
            <div align="center"></div>
          </div>
        </td>
        <td width="51">
          <div align="center">&nbsp;
            <?php echo $temp[8]->nro_factura;  ?>
          </div>
        </td>
        <td width="160">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[8]->garantia_c;?>
            </div>
          </div>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td height="42" valign="top" width="104"><b>&nbsp;Grabadora de &nbsp;Cd</b>&nbsp;</td>
        <td colspan="3">
          <div align="center">
            <?php echo $temp[9]->esp1; ?>
          </div>
        </td>
        <td width="109">
          <div align="center">
            <?php echo $temp[9]->obs_comp;?>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[9]->nbre_prov; ?>
          </div>
        </td>
        <td width="51">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[9]->nro_factura;  ?>
            </div>
          </div>
        </td>
        <td width="160">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[9]->garantia_c;?>
            </div>
          </div>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td height="32" valign="top" width="104"><b>&nbsp;DVD</b></td>
        <td colspan="3">
          <div align="center">
            <?php echo $temp[10]->esp1;?>
          </div>
        </td>
        <td width="109">
          <div align="center">
            <?php echo $temp[10]->obs_comp; ?>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[10]->nbre_prov; ?>
          </div>
        </td>
        <td width="51">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[10]->nro_factura; ?>
            </div>
          </div>
        </td>
        <td width="160">
          <div>
            <div align="center">&nbsp; &nbsp;
              <?php echo $temp[10]->garantia_c;?>
            </div>
          </div>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td height="38" valign="top" width="104"><b>&nbsp;Lectora de CD</b></td>
        <td colspan="3">
          <div align="center"> &nbsp;
            <?php echo $temp[11]->esp1; ?>
          </div>
        </td>
        <td width="109">
          <div align="center">
            <?php echo $temp[11]->obs_comp; ?>
          </div>
        </td>
        <td>
          <div align="center">
            <?php echo $temp[11]->nbre_prov;?>
          </div>
        </td>
        <td width="51">
          <div align="center">&nbsp;
            <?php echo $temp[11]->nro_factura;  ?>
          </div>
        </td>
        <td width="160">
          <div>
            <div align="center">&nbsp;
              <?php echo $temp[11]->garantia_c;?>
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <td height="1" width="104"></td>
        <td width="60"></td>
        <td></td>
        <td></td>
        <td width="109"></td>
        <td width="69"></td>
        <td width="51"></td>
        <td width="160"></td>
      </tr>
    </table>


<p> <b>Adicionales: </b>
  <?php echo $adicional;?>
</p>
    <div><b><font face="Georgia, Times New Roman, Times, serif">KIT DE ATX
          </font></b></div>
   <?
   /*este for se ejecuta 5 veces para recuperar en cada fila  de la
    tabla accesorios un tipo distinto*/

  for($i=0;$i<5;$i++)
    {db_tipo_res('a');
     $ssql="select distinct esp1, descripcion, tipo from accesorios where accesorios.nro_orden=".$nro." and tipo=".$i;
     //echo $ssql;
     $resultado=$db->Execute($ssql) or Error($db->ErrorMsg());
	 $tmp[$i]->esp1=$resultado->fields["esp1"];
	 $tmp[$i]->desc=$resultado->fields["descripcion"];
	}
  ?>
  <table border="0" cellspacing="2" cellpadding="2" width="50%">
    <tr BGCOLOR="#006699">
      <td width="115"><b><font size="3" align="center" color='#cccccc'>Accesorio</font></b> </td>
      <td width="83"><b><font size="3" align="center" color='#cccccc'>Modelo</font></b></td>
      <td width="200"><b><font size="3" align="center" color='#cccccc'>Observaciones</font></b></td>
    </tr>
    <tr bgcolor="#CCCCCC">

    <td height="21">&nbsp;<b>Teclado</b></td>

    <td height="21">
      <div align="center"><?php echo $tmp[0]->esp1; ?></div>
    </td>

    <td height="21">
      <div align="center"><?php echo $tmp[0]->desc ?></div>
    </td>
    </tr>
    <tr bgcolor="#CCCCCC">
      <td>&nbsp;<b>Mouse</b></td>

    <td>

      <div align="center"><?php echo $tmp[1]->esp1; ?></div>
    </td>

    <td>

      <div align="center"><?php echo $tmp[1]->desc; ?></div>
    </td>
    </tr>
    <tr bgcolor="#CCCCCC">
      <td>&nbsp;<b>Parlantes</b></td>
      <td>
      <div align="center"><font size="3" align="center" color=<?php echo $texto_tabla?>>
        <?php echo $tmp[2]->esp1; ?>
        </font></div>
    </td>

    <td>

      <div align="center"><?php echo $tmp[2]->desc; ?></div>
    </td>
    </tr>
    <tr bgcolor="#CCCCCC">
      <td>&nbsp;<b>Micr&oacute;fono</b></td>

    <td>

      <div align="center"><?php if($tmp[3]->esp1=='on') echo 'Si'; else echo 'No'; ?></div>
    </td>

    <td>

      <div align="center"> <?php echo $tmp[3]->desc; ?></div>
    </td>
    </tr>
    <tr bgcolor="#CCCCCC">
      <td height="23">&nbsp;<b>Floppy</b></td>

    <td>

      <div align="center"><?php if($tmp[4]->esp1=='on') echo 'Si'; else echo 'No'; ?></div>
    </td>
      <td valign="top">&nbsp;</td>
    </tr>
  </table>
<br>
<?
  /* Cracteristicas del monitos*/

   $ssql="select marca, modelo,garantia, pulgadas, nombre_proveedor, factura_monitor.nro_factura";
   $ssql.=" from (monitor left join factura_monitor on factura_monitor.id_monitor=monitor.id_monitor)";
   $ssql.="where monitor.nro_orden=".$nro;
  //echo $ssql;
   //db_tipo_res('a');
   $resultado=$db->Execute($ssql) or Error($db->ErrorMsg());

?>
  <div><b><font face="Georgia, Times New Roman, Times, serif">MONITOR
          </font></b></div>
  <table border="0" cellspacing="2" cellpadding="2" width="90%">
    <tr BGCOLOR="#006699">
      <td width="130">
        <div><b><font size="3" align="center" color='#cccccc'>Marca</font></b></div>
      </td>
      <td width="130">
        <div><b><font size="3" align="center" color='#cccccc'>Modelo</font></b></div>
      </td>
      <td width="63">
        <div><b><font size="3" align="center" color='#cccccc'>Pulgadas</font></b></div>
      </td>
      <td width="71">
	   <div><b><font size="3" align="center" color='#cccccc'>Proveedor</font></b></div>
	  </td>
      <td width="132">
	   <div><b><font size="3" align="center" color='#cccccc'>Garantia</font></b></div>
	  </td>
	  <td>
	   <div><b><font size="3" align="center" color='#cccccc'>Nº Factura</font></b></div>
	  </td>
    </tr>
    <tr bgcolor="#CCCCCC">
      <td width="75">
        <?php echo $resultado->fields["marca"]; ?>
      </td>
      <td width="130">
        <?php echo $resultado->fields["modelo"]; ?>
      </td>
      <td width="130">
        <?php echo $resultado->fields["pulgadas"]; ?>
      </td>
      <td width="63">
       <?php echo $resultado->fields["nombre_proveedor"]; ?>
      </td>
      <td width="71">
        <?php echo $resultado->fields["garantia"];?>
      </td>
      <td width="132">
        <?php echo $resultado->fields["nro_factura"]; ?>
      </td>
    </tr>
   </table>
  <br>
    <div><b><font face="Georgia, Times New Roman, Times, serif">OTROS
          </font></b></div>
  <?
  /*caracteristicas del software*/
  $ssql="select descripcion, observaciones from software where nro_orden=".$nro;
   //echo $ssql;
 db_tipo_res('a');
 $resultado=$db->Execute($ssql) or Error($db->ErrorMsg());
  ?>

<table width="90%">
  <tr bgcolor="#CCCCCC">
    <td width="309" valign="top">&nbsp; <b>Software y/o licencias:</b>
      <?php echo $resultado->fields["descripcion"]; ?>
    </td>
    <td valign="top" width="300">&nbsp;<b>Entrega: </b>
      <?php echo $resultado->fields["observaciones"];?>
    </td>
  </tr>
 <tr bgcolor="#CCCCCC">
 <td width="700" valign="top">&nbsp; <b>Garantía:</b>
      <?php echo $garantia; ?>
 </td>
 <td></td>  
 </tr>
</table>
<input type="hidden" name="select_en" value="<? echo $_GET['campo']; ?>">
<input type="hidden" name="est" value="<? echo $_GET['est']; ?>">
<input type="hidden" name="keyword" value="<? echo $_GET['keyword']; ?>">
</form>
<? } //else?>
</body>
</html>