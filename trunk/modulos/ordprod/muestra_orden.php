<?php
require_once("../../config.php");
 $ssql="select estado, autor_orden, aprobada, factura_componente.nro_factura, fecha_inicio,fecha_entrega, cliente_final.nombre as nbre_cliente, lugar_entrega, producto,";
 $ssql.="configuracion_maquina.modelo as modelo_a, cantidad, adicionales, ensamblador.nombre as nbre_ens, primera_maquina, ultima_maquina ";
 $ssql.=" from orden_de_produccion, cliente_final, ensamblador, configuracion_maquina, accesorios";
 $ssql.=" where cliente_final.id_cliente=orden_de_produccion.id_cliente and configuracion_maquina.id_configuracion=orden_de_produccion.id_configuracion"; 
 $ssql.=" and componentes.id_componente=factura_componente.id_componente and ensamblador.id_ensamblador=orden_de_produccion.id_ensamblador";
 $ssql.=" and accesorios.nro_orden=orden_de_produccion.nro_orden and orden_de_produccion.nro_orden=".$nro_orden;
 
 $resultado=&$db->Execute($ssql) or Error($db->ErrorMsg());
 $adicional=$resultado->fields["adicionales"];
 $ano=substr($resultado->fields["fecha_entrega"],0,4);
 $mes=substr($resultado->fields["fecha_entrega"],5,2);
 $dia=substr($resultado->fields["fecha_entrega"],8,2);
 $fecha=$dia.'/'.$mes.'/'.$ano;
  
?>

<html>
<head>
<SCRIPT language='JavaScript' src="funciones.js"></SCRIPT>
<? cargar_calendario(); ?>
<title>Muestra Orden</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body bgcolor="#E0E0E0" text="#000000">

<table width="90%" border="0">
  <tr bgcolor="#CCCCCC"> 
    <td width="341" height="20" valign="top"> 
      <div>&nbsp;<b>Orden N&ordm;:</b> 
        <?PHP echo $nro_orden ?>
      </div>
    </td>
    <td width="410" valign="top" height="20"> 
      <div align="right"><b>Fecha de inicio:</b> 
        <?PHP 
          $finicio=date("d/m/Y");
          echo $finicio;
         ?>
        <input type="hidden" name="f_inicio" value=<?php $resultado->fields["fecha_inicio"]; ?>>
      </div>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC"> 
    <td colspan="2" height="16" valign="top">&nbsp;<b>Estado:&nbsp;</b> 
      <?php switch($resultado->fields["estado"]){
	        case 0: echo "Pendiente"; break;
			case 1: echo "En proceso"; break;
			case 2: echo "Terminada"; break;
			case 3: echo "Anulada"; break;
			}		
	 switch($resultado->fields["aprobada"]){
	        case 0: echo "&nbsp;-&nbsp;No aprobada"; break;
			case 1: echo "&nbsp;-&nbsp;Aprobada"; break;
			case 2: echo "&nbsp;-&nbsp;Rechazada"; break;
			}		  
	?>
    </td>
  </tr>
<tr bgcolor="#CCCCCC"> 
    <td colspan="2" height="16" valign="top">&nbsp;<b>Autor:&nbsp;</b> 
      <? echo $resultado->fields["autor_orden"];?>
	
    </td>
  </tr>

</table>


  
<table width="90%" border="0">
  <tr bgcolor="CCCCCC"> 
    <td width="340" valign="top"> 
      <div align="left">&nbsp;<b>Ensamblador:</b> 
        <?PHP echo $resultado->fields["nbre_ens"]; ?>
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
        

		 <?php echo decod_modelo($resultado->fields["modelo_a"]); ?>

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
<?php
if ($_POST['gserial']=="Generar Nro de Serie")
 gen_serial($_POST['ensamble'],$_POST['f_entrega'],$_POST['modelo_cdr']);
?>
<input type="hidden" name="primero" value=<?php echo $primer_ser; ?>>
<input type="hidden" name="parte" value=<?php echo $parte_serial; ?>>
  <table border="0" cellspacing="0" cellpadding="0" width="90%">
    <tr> 
      <td width="601">&nbsp;<b> Numero de Serie: 
        <?PHP echo $resultado->fields["primera_maquina"]."......".$resultado->fields["ultima_maquina"]; ?>
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
 $ssql="select nro_factura,  componentes.tipo as tipo_c, componentes.observaciones_componente as obs_comp,";
 $ssql.= " componentes.esp1 as esp1, componentes.esp2 as esp2, componentes.esp3,componentes.garantia as garantia_c, componentes.nombre_proveedor as nbre_prov from ";
 $ssql.=" configuracion_maquina, orden_de_produccion, componentes, factura_componente where ";
 $ssql.=" factura_componente.id_componente=componentes.id_componente and configuracion_maquina.id_configuracion=componentes.id_configuracion";
 $ssql.=" and configuracion_maquina.id_configuracion=orden_de_produccion.id_configuracion and orden_de_produccion.nro_orden=".$nro_orden." order by componentes.tipo" ;
 
 //echo "<br>".$ssql;
 $resultado = &$db->Execute($ssql) or Error($db->ErrorMsg());
 while (!$resultado->EOF){
  $temp[$cont]->obs_soft=$resultado->fields["obs_soft"];
  $temp[$cont]->tipo_c=$resultado->fields["tipo_c"];
  $temp[$cont]->obs_comp=$resultado->fields["obs_comp"];
  $temp[$cont]->nro_factura=$resultado->fields["nro_factura"];
  $temp[$cont]->esp1=$resultado->fields["esp1"];
  $temp[$cont]->esp2=$resultado->fields["esp2"];
  $temp[$cont]->esp3=$resultado->fields["esp3"];
  $temp[$cont]->garantia_c=$resultado->fields["garantia_c"];
  $temp[$cont]->nbre_prov=$resultado->fields["nbre_prov"];
 $resultado->MoveNext(); 
 }
 
 ?>		  
  
<table border="0" cellspacing="3" cellpadding="0" width="90%">
  <tr BGCOLOR="#006699"> 
    <td height="42" valign="top" colspan="4"> 
      <div><b><font size="3" align="center" color=<?php echo $texto_tabla?> >Descripci&oacute;n</font></b></div>
    </td>
    <td width="100" valign="top" > 
      <div><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Observaciones</font></b></div>
    </td>
    <td width="71" valign="top"> 
      <div><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Proveedor</font></b></div>
    </td>
    <td width="61" valign="top"> 
      <div><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Nº 
        Factura</font></b></div>
    </td>
    <td width="73" valign="top"> 
      <div><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Garant&iacute;a</font></b></div>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC"> 
    <td width="114" height="46" valign="top"><b>&nbsp;Sistema &nbsp;Operativo</b></td>
    <td width="73" valign="top"> 
      <div> 
        <div align="center"> 
          <?php echo $temp[0]->esp1;?>
        </div>
      </div>
    </td>
    <td valign="top" colspan="2"> 
      <div align="center"> 
        <?php echo $temp[0]->esp2; ?>
      </div>
    </td>
    <td> 
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
    <td> 
      <div> 
        <div >&nbsp; 
          <?php echo $temp[0]->nro_factura; ?>
        </div>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[0]->garantia_c;?>
        </div>
      </div>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC" > 
    <td valign="top" height="46" width="114"><b>&nbsp;Tarjeta Madre</b></td>
    <td valign="top" width="73"> 
      <div> 
        <div align="center"> 
          <?php echo $temp[1]->esp1; ?>
        </div>
      </div>
    </td>
    <td valign="top" colspan="2"> 
      <div align="center"> 
        <?php echo $temp[1]->esp2; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[1]->obs_comp; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[1]->nbre_prov; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <div align="center">&nbsp; 
          <?php echo $temp[1]->nro_factura;  ?>
        </div>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <div align="center">&nbsp; 
          <?php echo $temp[1]->garantia_c;?>
        </div>
      </div>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC"> 
    <td height="32" valign="top" width="114">&nbsp;<b>Placa de video</b></td>
    <td valign="top" width="73"> 
      <div align="center" > 
        <?php echo $temp[2]->esp1; ?>
      </div>
    </td>
    <td valign="top" colspan="2"> 
      <div align="center"> 
        <?php echo $temp[2]->esp2; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[2]->obs_comp; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[2]->nbre_prov; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <div align="center">&nbsp; 
          <?php echo $temp[2]->nro_factura;  ?>
        </div>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[2]->garantia_c;;?>
        </div>
      </div>
    </td>
  <tr bgcolor="#CCCCCC"> 
    <td height="32" valign="top" width="114">&nbsp;<b>Placa de sonido</b></td>
    <td colspan="3" valign="top"> 
      <div align="center"> 
        <?php echo $temp[3]->esp1; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[3]->obs_comp; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[3]->nbre_prov; ?>
      </div>
    </td>
    <td> 
      <div align="center">&nbsp; 
        <?php echo $temp[3]->nro_factura; ?>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[3]->garantia_c;?>
        </div>
      </div>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC"> 
    <td height="47" valign="top" width="114">&nbsp;<b>LAN</b></td>
    <td valign="top" width="73"> 
      <div align="center"> 
        <?php echo $temp[4]->esp1 ?>
      </div>
    </td>
    <td valign="top" colspan="2"> 
      <div align="center"> 
        <?php echo $temp[4]->esp2; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[4]->obs_comp; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[4]->nbre_prov; ?>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[4]->nro_factura; ?>
        </div>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[4]->garantia_c;?>
        </div>
      </div>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC"> 
    <td height="32" valign="top" width="114">&nbsp;<b>Modem </b></td>
    <td valign="top" colspan="3"> 
      <div align="center"> 
        <?php echo $temp[5]->esp1; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[5]->obs_comp;?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[5]->nbre_prov; ?>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[5]->nro_factura; ?>
        </div>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[5]->garantia_c;?>
        </div>
      </div>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC"> 
    <td height="46" valign="top" width="114"><b>&nbsp;Micro</b></td>
    <td valign="top" width="73"> 
      <div align="center"> 
        <?php echo $temp[6]->esp1; ?>
      </div>
    </td>
    <td colspan="2" valign="top"> 
      <div align="center"> 
        <?php echo $temp[6]->esp2;?>
      </div>
    </td>
    <td valign="top"> 
      <div align="center"> 
        <?php echo $temp[6]->obs_comp; ?>
      </div>
    </td>
    <td valign="top"> 
      <div align="center"> 
        <?php echo $temp[6]->nbre_prov; ?>
      </div>
    </td>
    <td valign="top"> 
      <div align="center">&nbsp; 
        <?php echo $temp[6]->nro_factura;  ?>
      </div>
    </td>
    <td valign="top"> 
      <div align="center">&nbsp; 
        <?php echo $temp[6]->garantia_c;?>
      </div>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC"> 
    <td height="46" valign="top" width="114"><b>&nbsp;Micro</b></td>
    <td valign="top" width="73"> 
      <div align="center"> 
        <?php echo $temp[6]->esp3; ?>
      </div>
    </td>
    <td colspan="2" valign="top"> 
      <div align="center"> 
        <?php echo $temp[6]->esp4;?>
      </div>
    </td>
    <td colspan="4" valign="top">&nbsp;</td>
  </tr>
  <tr bgcolor="#CCCCCC"> 
    <td height="42" valign="top" width="114"><b>&nbsp;Memoria</b></td>
    <td valign="top" width="73"> 
      <div> 
        <?php echo $temp[7]->esp1; ?>
      </div>
    </td>
    <td valign="top" width="108"> 
      <div align="center"> 
        <?php echo $temp[7]->esp2;?>
      </div>
    </td>
    <td valign="top" width="55"> 
      <div align="center"> 
        <?php echo $temp[7]->esp3;?>
      </div>
    </td>
    <td valign="top"> 
      <div align="center"> 
        <?php echo $temp[7]->obs_comp; ?>
      </div>
    </td>
    <td valign="top"> 
      <div align="center"> 
        <?php echo $temp[7]->nbre_prov; ?>
      </div>
    </td>
    <td valign="top"> 
      <div align="center">&nbsp; 
        <?php echo $temp[7]->nro_factura;  ?>
      </div>
    </td>
    <td valign="top"> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[7]->garantia_c;?>
        </div>
      </div>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC" > 
    <td valign="top" height="46" width="114"><b>&nbsp;HDD</b></td>
    <td valign="top" width="73"> 
      <div> 
        <div align="center"> 
          <?php echo $temp[8]->esp1; ?>
        </div>
      </div>
    </td>
    <td valign="top"> 
      <div align="center"> 
        <?php echo $temp[8]->esp2; ?>
      </div>
    </td>
    <td valign="top"> 
      <?php echo $temp[8]->esp3;?>
    </td>
    <td> 
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
    <td> 
      <div align="center">&nbsp; 
        <?php echo $temp[8]->nro_factura;  ?>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[8]->garantia_c;?>
        </div>
      </div>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC"> 
    <td height="42" valign="top" width="114"><b>&nbsp;Grabadora de &nbsp;Cd</b>&nbsp;</td>
    <td valign="top" colspan="3"> 
      <div align="center"> 
        <?php echo $temp[9]->esp1; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[9]->obs_comp;?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[9]->nbre_prov; ?>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[9]->nro_factura;  ?>
        </div>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[9]->garantia_c;?>
        </div>
      </div>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC"> 
    <td height="32" valign="top" width="114"><b>&nbsp;DVD</b></td>
    <td valign="top" colspan="3"> 
      <div align="center"> 
        <?php echo $temp[10]->esp1;?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[10]->obs_comp; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[10]->nbre_prov; ?>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[10]->nro_factura; ?>
        </div>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; &nbsp; 
          <?php echo $temp[10]->garantia_c;?>
        </div>
      </div>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC"> 
    <td height="32" valign="top" width="114"><b>&nbsp;Lectora de CD</b></td>
    <td valign="top" colspan="3"> 
      <div align="center"> &nbsp; 
        <?php echo $temp[11]->esp1; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[11]->obs_comp; ?>
      </div>
    </td>
    <td> 
      <div align="center"> 
        <?php echo $temp[11]->nbre_prov;?>
      </div>
    </td>
    <td> 
      <div align="center">&nbsp; 
        <?php echo $temp[11]->nro_factura;  ?>
      </div>
    </td>
    <td> 
      <div> 
        <div align="center">&nbsp; 
          <?php echo $temp[11]->garantia_c;?>
        </div>
      </div>
    </td>
  </tr>
  <tr> 
    <td height="1" width="114"></td>
    <td width="73"></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
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
    {
     $ssql="select distinct esp1, descripcion, tipo from accesorios where accesorios.nro_orden=".$nro_orden." and tipo=".$i;
     //echo $ssql;
     $resultado=&$db->Execute($ssql) or Error($db->ErrorMsg());
	 $tmp[$i]->esp1=$resultado->fields["esp1"];
	 $tmp[$i]->desc=$resultado->fields["descripcion"];
	} 
  ?>
  <table border="0" cellspacing="2" cellpadding="2" width="50%">
    <tr BGCOLOR="#006699"> 
      <td width="115"><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Ac 
        cesorio</font></b> </td>
      <td width="83"><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Modelo</font></b></td>
      <td width="200"><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Observaciones</font></b></td>
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
   $ssql.=" from monitor, factura_monitor where factura_monitor.id_monitor=monitor.id_monitor ";
   $ssql.=" and monitor.nro_orden=".$nro_orden;
  //echo $ssql;
   $resultado=&$db->Execute($ssql) or Error($db->ErrorMsg());
  
?>
  <div><b><font face="Georgia, Times New Roman, Times, serif">MONITOR
          </font></b></div>
  <table border="0" cellspacing="2" cellpadding="2" width="90%">
    <tr BGCOLOR="#006699"> 
      <td width="130"> 
        <div><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Marca</font></b></div>
      </td>
      <td width="130"> 
        <div><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Modelo</font></b></div>
      </td>
      <td width="63"> 
        <div><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Pulgadas</font></b></div>
      </td>
      <td width="71">
	   <div><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Proveedor</font></b></div>
	  </td>
      <td width="132">
	   <div><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Garantia</font></b></div>
	  </td>
	  <td>
	   <div><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Nº Factura</font></b></div>
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
  $ssql="select descripcion, observaciones from software where nro_orden=".$nro_orden;
   //echo $ssql;
 $resultado=&$db->Execute($ssql) or Error($db->ErrorMsg());
  ?>
  
<table width="90%">
  <tr bgcolor="#CCCCCC"> 
    <td width="309" valign="top">&nbsp; <b>Software y/o licencias:</b> 
      <?php echo $resultado->fields["descripcion"]; ?>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td valign="top" width="433">&nbsp;<b>Entrega: </b> 
      <?php echo $resultado->fields["observaciones"];?>
    </td>
  </tr>
</table> 
  <p align="center">&nbsp; </p>
</body>
</html>