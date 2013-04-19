<?php
require_once("../../config.php");
include_once("./orden_produccion.php");
include_once("funciones.php");
if ($_POST['Submit']=="Cancelar")
{include_once "./ordenes_ver.php";
 exit;}
if ($_POST['Submit']=="Guardar orden")
{require_once "update_orden.php";
 exit;
 }
else {
 if(!isset($_POST['gserial'])) 
  $nro_orden=$_POST['nro_ord'];
 
  if($_POST["filtro"]=="activado")
   $nro_orden=$_POST["numero"];
   
 $ssql="SELECT estado, aprobada, factura_componente.nro_factura, fecha_inicio,";
 $ssql.="fecha_entrega, cliente_final.nombre as nbre_cliente, lugar_entrega, producto,";
 $ssql.="configuracion_maquina.modelo as modelo_a, cantidad, adicionales,";
 $ssql.="ensamblador.nombre as nbre_ens, primera_maquina, ultima_maquina,orden_de_produccion.garantia ";
 $ssql.="FROM orden_de_produccion ";
 $ssql.="LEFT JOIN ensamblador USING (id_ensamblador) ";
 $ssql.="LEFT JOIN cliente_final USING (id_cliente) ";
 $ssql.="LEFT JOIN accesorios USING (nro_orden) ";
 $ssql.="LEFT JOIN configuracion_maquina USING (id_configuracion) ";
 $ssql.="LEFT JOIN componentes USING (id_configuracion) ";
 $ssql.="LEFT JOIN factura_componente USING (id_componente) ";
 $ssql.="WHERE orden_de_produccion.nro_orden=$nro";
 $resultado = $db->Execute($ssql) or Error($ssql."<br>".$db->ErrorMsg());

/*
$ssql="select distinct factura_componente.nro_factura, fecha_inicio,fecha_entrega,autor_orden, cliente_final.nombre as nbre_cliente, lugar_entrega, producto,";
 $ssql.="configuracion_maquina.modelo as modelo_a,cantidad, adicionales, ensamblador.nombre as nbre_ens, primera_maquina, ultima_maquina,orden_de_produccion.garantia";
 $ssql.=" from orden_de_produccion, cliente_final, ensamblador, configuracion_maquina, accesorios";
 $ssql.=" where cliente_final.id_cliente=orden_de_produccion.id_cliente and configuracion_maquina.id_configuracion=orden_de_produccion.id_configuracion"; 
 $ssql.=" and componentes.id_componente=factura_componente.id_componente and ensamblador.id_ensamblador=orden_de_produccion.id_ensamblador";
 $ssql.=" and accesorios.nro_orden=orden_de_produccion.nro_orden and orden_de_produccion.nro_orden=".$nro_orden;
 db_tipo_res('a');
 $resultado=$db->Execute($ssql) or Error($db->ErrorMsg()."ssql");*/
 $garantia=$resultado->fields["garantia"];
 $adicional=$resultado->fields["adicionales"];
 $fecha=Fecha($resultado->fields["fecha_entrega"]);
 $serialp=$resultado->fields["primera_maquina"];
 $serialu=$resultado->fields["ultima_maquina"];
 $cli[0]=$resultado->fields["nbre_cliente"];
 $cli[1]=$resultado->fields["lugar_entrega"];
$dato=1; //uso esta variable  para saber si la funcion gen_serial() fue llamada o no 
?>
<html>
<head>
<SCRIPT language='JavaScript' src="funciones.js"></SCRIPT>
<? cargar_calendario(); ?>
<title>Nueva Orden</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#E0E0E0" text="#000000">
<form name="form" action="ordenes_modif.php?nro_orden=<? echo $nro_orden;?>" method=post>
<input type="hidden" name="modi" value="1">
  <table width="90%" border="0">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="2"> 
        <div><b>Orden N&ordm;: </b> 
          <?PHP if($_POST["filtro"]=="activado")echo $_POST["numero"]; else  echo $nro_orden; ?>
          <input type="hidden" name="numero" value="<?PHP if($_POST["filtro"]=="activado")echo $_POST["numero"]; else  echo $nro_orden; ?>">
        </div>
      </td>
      <td width="326"> 
        <div align="right"><b>Fecha de inicio:</b> 
          <?PHP 
          $finicio=Fecha($resultado->fields["fecha_inicio"]);
          echo $finicio;
         ?>
         <input type="hidden" name="f_inicio" value="<?php echo $finicio; ?>">
        </div>
      </td>
    </tr>
    
    <tr>
      <td height="3"></td>
      <td width="242"></td>
      <td></td>
    </tr>
  </table>
<?
switch($_POST['estado'])
      {case 0:$q="select id_proveedor,razon_social from proveedor where activo='true' order by razon_social";
                      break;
       case 1:$q="select id_proveedor,razon_social from proveedor where activo='false' order by razon_social";
                      break;
       case 2:$q="select id_proveedor,razon_social from proveedor order by razon_social";
                      break;
      }

	$prov=$db->Execute($q) or die($db->ErrorMsg()."prov");
?>

  <table width="90%" border="0">
    <tr bgcolor="CCCCCC">
      <td width="99" height="26"> 
        <div align="right"><b>Ensamblador:</b></div>
      </td>
      <td width="247" height="26"> 
        <select name="ensamble">
	<?php
$sql="select * from ensamblador";
$resultado_ens=$db->Execute($sql) or die($db->ErrorMsg());
while (!$resultado_ens->EOF)
{
if ($_POST['ensamble']=="")
 $ensamblador=$resultado->fields['nbre_ens'];
else
 $ensamblador=$_POST['ensamble'];
 if ($resultado_ens->fields['nombre']==$ensamblador)
{
?>
  <option selected><?php echo $resultado_ens->fields['nombre']; ?></option>
<?php
}
else
{
?>
 <option><?php echo $resultado_ens->fields['nombre']; ?></option>
<?php
}
$resultado_ens->MoveNext();
}
?>
</select>
</td>       
      <td width="138" height="26"> 
        <div align="right"><b>Fecha de Entrega:</b></div>
      </td>
      <td width="188" height="26"> 
	  <?php
		if(isset($_POST['f_entrega'])) $fecha=$_POST['f_entrega'];
	  ?>
        <input type="text" name="f_entrega" value=<?php echo $fecha; ?>><?php echo link_calendario("f_entrega"); ?>
      </td>
  </tr>
</table>
  <p><b>ClienteFinal:&nbsp;</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <?php
	   if(isset($_POST['cliente'])) $cli[0]=$_POST['cliente'];
	?> 
    <textarea name="cliente" cols="83" rows="5"><?php echo $cli[0]; ?></textarea>
  </p>
  <p><b>Lugar de Entrega:</b> 
    <?php
	   if(isset($_POST['l_entrega'])) $cli[1]=$_POST['l_entrega'];
	?>
    <textarea name="l_entrega" cols="83" rows="5"><?php echo $cli[1]; ?></textarea>
  </p>
  <br>
  
  <table width="90%" border="0">
    <tr  bgcolor="#CCCCCC"> 
      <td width="66"><b>Producto:</b></td>
      <td width="137">
	  <select name="producto" onChange="beginEditing(this);">
<?php
if (isset($_POST['producto']))
{$s="";
 $c="";
 if ($_POST['producto']=="Server")
 {$s="selected";
  $c="";
 }
 if ($_POST['producto']=="Computadoras CDR")
 {$c="selected";
  $s="";
 }
?>
          <option <?php echo $c; ?>>Computadoras CDR </option>
          <option <?php echo $s; ?>>Server</option>
          <option id=editable <?php if (($s=="") && ($c=="")) echo "selected"; ?>><?php if (($s=="") && ($c=="")) echo $_POST['producto']; else echo "Edite aqui";?></option>
		</select>
<?php
}
else
{
?>
	      <option selected><?php echo $resultado->fields["producto"]; ?></option>
          <option>Computadoras CDR</option>
		  <option>Server</option>
		  <option id="editable">Edite aqui</option>
        </select>
<?php
}
?>
      </td>
      <td width="58">&nbsp;</td>
      <td width="410">&nbsp;</td>
    </tr>
    <tr bgcolor="CCCCCC"> 
      <td width="66"> 
        <div align="left"><b>Modelo:</b></div>
      </td>
      <td width="137"> 
        <select name="modelo_cdr" value="" onChange="beginEditing(this);">
 <?php
if (isset($_POST['modelo_cdr']))
{$m="";
 $s="";
 $e="";
  switch ($_POST['modelo_cdr'])
  {case "ENTERPRISE":$e="selected";break;
   case "MATRIX":$m="selected";break;
   case "SERVER":$s="selected";break;
  }
?>
          <option <?php echo $e; ?>>ENTERPRISE</option>
          <option <?php echo $m; ?>>MATRIX</option>
          <option <?php echo $s; ?>>SERVER</option>
		  <option id=editable <?php if (($s=="") && ($e=="") && ($m=="")) echo "selected"; ?>><?php if (($s=="") && ($e=="") && ($m=="")) echo $_POST['modelo_cdr']; else echo "Edite aqui";?></option>
        </select>
<?php
}
else
{
?>
         <option selected><?php echo $resultado->fields["modelo_a"]; ?></option>	
          <option <?php echo $e; ?>>ENTERPRISE</option>
          <option <?php echo $m; ?>>MATRIX</option>
          <option <?php echo $s; ?>>SERVER</option>
          <option id="editable">Edite aqui</option>
         </select>
<?php
}
?>
      </td>
      <td width="58"> 
        <div align="right"><b>Cantidad:</b></div>
      </td>
      <td width="410"> 
        <?php echo $resultado->fields["cantidad"]; ?>
		<input type="hidden" name="cant" value=<?php echo $resultado->fields["cantidad"]; ?>>
      </td>
      
       <?php     
      $act="";
	  $noact="";
	  $todos="";	
      switch($_POST['estado'])
      {case 0:$act="selected";
                      break;
       case 1:$noact="selected";
                      break;
       case 2:$todos="selected";
                      break;
      }

	
	?>
   
      
      
    </tr>
  </table>
  <br>

<?php
/*if(isset($_POST['f_entrega']))$fech=$_POST['f_entrega']; else $fech=$fecha;
if ($_POST['gserial']=="Generar Nro de Serie"){
  ?> <input type="hidden" name="dato" value=1>
  <? gen_serial($_POST['ensamble'],$fech,$_POST['modelo_cdr'],$_POST['producto']);
}
else */?>
<!-- <input type="hidden" name="dato" value=0>
<input type="hidden" name="primero" value=<?php //echo $primer_ser; ?>>
<input type="hidden" name="parte" value=<?php //echo $parte_serial; ?>>
<input type="hidden" name="serial1" value=<?php //echo $serialp; ?>>
<input type="hidden" name="serial2" value=<?php //echo $serialu; ?>>
<input type="hidden" name="serialp" value=<?php //echo $pserial; ?>>
<input type="hidden" name="serialu" value=<?php //echo $userial; ?>>
<input type="hidden" name="letra" value=<?php //echo $letra; ?>>
-->
<?php
if(isset($_POST['f_entrega']))$fech=$_POST['f_entrega']; else $fech=$fecha;

if ($_POST['gserial']=="Generar Nro de Serie"){
$longitud=strlen($_POST['serial1']); //calculo serial anterior
$serial_ant=substr($_POST['serial1'],$longitud-3);
  ?> <input type="hidden" name="dato" value=1>
  <? gen_serial($_POST['ensamble'],$fech,$_POST['modelo_cdr'],$_POST['producto']);
}
elseif( $_POST["filtro"]=="activado")
{echo "<input type='hidden' name='dato' value=1>";
 $serialp=$_POST["serial1"];
 $serialu=$_POST["serial2"];
 $pserial=$_POST["serialp"];
 $userial=$_POST["serialu"];
 $primer_ser=$_POST["primero"];
 $parte_serial=$_POST["parte"];
 $letra=$_POST["letra"];
}
else ?>
<input type="hidden" name="primero" value=<?php echo $primer_ser; ?>>
<input type="hidden" name="parte" value=<?php echo $parte_serial; ?>>
<input type="hidden" name="serial1" value=<?php echo $serialp; ?>>
<input type="hidden" name="serial2" value=<?php echo $serialu; ?>>
<input type="hidden" name="serialp" value=<?php echo $pserial; ?>>
<input type="hidden" name="serialu" value=<?php echo $userial; ?>>
<input type="hidden" name="letra" value=<?php echo $letra; ?>>
<INPUT type="hidden" name="filtro" value="">

 <table border="0" cellspacing="0" cellpadding="0" width="90%">
    <tr> 
      <td width="138"><input type="submit" name="gserial" value="Generar Nro de Serie">
      </td>
      <td width="551">&nbsp;&nbsp;&nbsp;

		<?PHP 
		if ($_POST['gserial']=="Generar Nro de Serie") {echo $serialp."......".$serialu;}
		elseif(($_POST["filtro"]=="activado")&&($_POST["letra"]!="")) {echo $_POST['serial1']."......".$_POST['serial2'];}
	       else   {echo $resultado->fields["primera_maquina"]."......".$resultado->fields["ultima_maquina"];}
		   ?>
        &nbsp;</td>
       <td align="left"><b>Proveedor&nbsp;</b>
       <select name="estado" value="est" Onchange='document.form.filtro.value="activado"; document.form.submit()'>
              <option value="0" <?php echo $act;?>>Activos</option>
              <option value="1" <?php echo $noact;?>>No Activos</option>
              <option value="2" <?php echo $todos;?>>Todos</option>
         </select>
         
     </td>  
    </tr>
  </table>
  <br>
    <br>
   <div><b><font face="Georgia, Times New Roman, Times, serif">COMPONENTES
          </font></b></div>
		  
  <?
  /* selecciona  componentes por tipo y nro de orden */
 $ssql="select nro_factura,  componentes.tipo as tipo_c, componentes.observaciones_componente as obs_comp,";
 $ssql.= " componentes.esp1 as esp1, componentes.esp2 as esp2, componentes.esp3,componentes.esp4 as eps4,componentes.garantia as garantia_c, componentes.nombre_proveedor as nbre_prov from ";
 $ssql.=" configuracion_maquina, orden_de_produccion, componentes, factura_componente where ";
 $ssql.=" factura_componente.id_componente=componentes.id_componente and configuracion_maquina.id_configuracion=componentes.id_configuracion";
 $ssql.=" and configuracion_maquina.id_configuracion=orden_de_produccion.id_configuracion and orden_de_produccion.nro_orden=".$nro_orden." order by componentes.tipo" ;
 
 //echo "<br>".$ssql;
 $resultado = $db->Execute($ssql) or Error($db->ErrorMsg()."ssql2");
 $cont=0;
 while(!$resultado->EOF){
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
  <table border="0" cellspacing="2" cellpadding="0" width="100%" >
    <tr BGCOLOR="#006699"> 
      <td valign="top" align="center"  height="25" colspan="4"> <div><b><font size="3" align="center" color="#CCCCCC" >Descripci&oacute;n</font></b></div></td>
      <td  valign="top" align="center" height="25"> <div><b><font size="3" align="center" color="#CCCCCC">Observaciones</font></b></div></td>
      <td  valign="top" align="center" height="25"> <div><b><font size="3" align="center" color="#CCCCCC">Proveedor</font></b></div></td>
      <td  valign="top" align="center"  height="25" nowrap> <div><b><font size="3" align="center" color="#CCCCCC">Nº 
          Factura</font></b></div></td>
      <td  valign="top" align="center"  height="25"> <div><b><font size="3" align="center" color="#CCCCCC">Garantía</font></b></div></td>
    </tr>
    <tr> 
      <td width="69" height="38" valign="top"><b>Sist.Op.</b></td>
      <td width="82" valign="top"> <select name="esp1_so" value="" onChange="beginEditing(this);">
                <option selected> 
                <?php echo $temp[0]->esp1;?>
                </option>
                <option>Windows</option>
                <option>Linux</option>
                <option ></option>
                <option id="editable">Edite aqui</option>
              </select> </td>
      <td valign="top" colspan="2"> <div> 
          <select name="esp2_so" value="" onChange="beginEditing(this);">
            <option selected> 
            <?php echo $temp[0]->esp2; ?>
            </option>
            <option>XP Pro</option>
            <option>XP Home</option>
            <option>2000 Pro</option>
            <option>2000 Server</option>
            <option>Me</option>
            <option>98</option>
            <option>95</option>
            <option>NT</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select>
        </div></td>
      <td width="92" valign="top"> <div align="center"> 
          <input type="text" name="observ_so" size="14" value="<?php echo $temp[0]->obs_comp; ?>">
        </div></td>
      <td width="138" valign="top"> <div> 
          <select name="proveedor_so">
              <option selected> 
              <?php echo $temp[0]->nbre_prov; ?>
              </option>
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>		
	<option value="<? echo $prov->fields['razon_social']; ?>"> 
    <? echo cortar($prov->fields['razon_social']); ?>
    </option>

<?   
    $prov->MoveNext();
	}
?>
            </select>
        </div></td>
      <td valign="top" align="center"> <input name="fact_so" type="text" value="<?php echo $temp[0]->nro_factura;?>" size="5"> 
      </td>
      <td width="81" valign="top"> <div> 
          <select value="" name="garantia_so" onChange="beginEditing(this);">
              <option selected> 
              <?php echo $temp[0]->garantia_c;?>
              </option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
    </tr>
    <tr> 
      <td valign="top"><b>Tarjeta Madre</b></td>
      <td valign="top"> <select name="esp1_mother"  OnChange="beginEditing(this);">
                <option selected> 
                <?php echo $temp[1]->esp1; ?>
                </option>
                <option >PCCHIPS</option>
                <option>INTEL</option>
                <option ></option>
                <option id="editable">Edite aqui</option>
              </select> </td>
      <td valign="top" colspan="2"> <div> 
          <select name="esp2_mother" onChange="beginEditing(this);">
            <option selected> 
            <?php echo $temp[1]->esp2; ?>
            </option>
            <option>810</option>
            <option>825</option>
            <option>845 GLLY</option>
            <option>845 WDA-E</option>
            <option >925</option>
            <option>930</option>
            <option>935</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="observ_mother" size="14" value="<?php echo $temp[1]->obs_comp; ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="proveedor_mother">
          <option selected> 
          <?php echo $temp[1]->nbre_prov; ?>
          </option>
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>
     <option value="<? echo $prov->fields['razon_social']; ?>"> 
     <? echo cortar($prov->fields['razon_social']); ?>
     </option>
<?php
   	$prov->MoveNext();
	}
?>
            </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="fact_mother" size="5" value="<?php echo $temp[1]->nro_factura;  ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="garantia_mother" value="" onChange="beginEditing(this);">
              <option selected> 
              <?php echo $temp[1]->garantia_c;?>
              </option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
    </tr>
    <tr> 
      <td height="32" valign="top" width="69"><b>Video&nbsp;</b></td>
      <td valign="top"> <select name="esp1_video" onChange="beginEditing(this);">
              <option selected><?php echo $temp[2]->esp1; ?></option>
              <option >On Board</option>
              <option>PCI</option>
              <option>Ninguno</option>
              <option id="editable">Edite aqui</option>
            </select> </td>
      <td valign="top" colspan="2"> <div> 
          <select name="esp2_video" onChange="beginEditing(this);">
          <option selected><?php echo $temp[2]->esp2; ?></option>
          <option>1 MB</option>
            <option>2 MB</option>
            <option>4 MB</option>
            <option>8 MB</option>
            <option>16 MB</option>
            <option>32 MB</option>
            <option>64 MB</option>
            <option>128 MB</option>
            <option>256 MB</option>
            <option></option>
            <option id="editable">Edite aqui</option>
          </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="observ_video" size="14" value="<?php echo $temp[2]->obs_comp; ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="proveedor_video">
          <option selected> 
          <?php echo $temp[2]->nbre_prov; ?>
          </option>
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>
	 <option value="<? echo $prov->fields['razon_social']; ?>"> 
     <? echo cortar($prov->fields['razon_social']); ?>
     </option>
<?php
	$prov->MoveNext();
	}
?>
            </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="fact_video" size="5" value="<?php echo $temp[2]->nro_factura;  ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="garantia_video" value="" onChange="beginEditing(this);">
              <option selected> 
              <?php echo $temp[2]->garantia_c;;?>
              </option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
    </tr>
    <tr> 
      <td height="32" valign="top" width="69"><b>Sonido</b></td>
      <td colspan="3" valign="top"> <div> 
          <select name="esp1_sonido" onChange="beginEditing(this);">
              <option selected> 
              <?php echo $temp[3]->esp1; ?>
              </option>
              <option >On Board</option>
              <option>PCI</option>
              <option>Ninguno</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="observ_sonido" size="14" value="<?php echo $temp[3]->obs_comp; ?>" >
        </div></td>
      <td valign="top"> <div> 
          <select name="proveedor_sonido">
          <option selected> 
          <?php echo $temp[3]->nbre_prov; ?>
          </option>
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>
	<option value="<? echo $prov->fields['razon_social']; ?>"> 
    <? echo cortar($prov->fields['razon_social']); ?>
    </option>
<?php
    $prov->MoveNext();
	}
?>
            </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="fact_sonido" size="5" value="<?php echo $temp[3]->nro_factura; ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="garantia_sonido" value="" onChange="beginEditing(this);">
              <option selected> 
              <?php echo $temp[3]->garantia_c;?>
              </option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
    </tr>
    <tr> 
      <td height="31" valign="top" width="69"><b>LAN </b></td>
      <td valign="top"> <select name="esp1_red" onChange="beginEditing(this);">
              <option selected> 
              <?php echo $temp[4]->esp1 ?>
              </option>
              <option >On Board</option>
              <option>PCI</option>
              <option>Ninguno</option>
              <option id="editable">Edite aqui</option>
            </select> </td>
      <td valign="top" colspan="2"> <div> 
          <select name="esp2_red" onChange="beginEditing(this);">
            <option selected><?php echo $temp[4]->esp2; ?></option>
            <option>10 Mbps</option>
            <option >10/100 Mbps</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="observ_red" size="14" value="<?php echo $temp[4]->obs_comp; ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="proveedor_red">
          <option selected> 
          <?php echo $temp[4]->nbre_prov; ?>
          </option>
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>
     <option value="<? echo $prov->fields['razon_social']; ?>"> 
     <? echo cortar($prov->fields['razon_social']); ?>
     </option>
<?php
	 $prov->MoveNext();
	}
?>
           </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="fact_red" size="5" value="<?php echo $temp[4]->nro_factura; ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="garantia_red" value="" onChange="beginEditing(this);">
              <option selected><?php echo $temp[4]->garantia_c;?></option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
    </tr>
    <tr> 
      <td height="28" valign="top" width="69"><b>Modem </b></td>
      <td colspan="3" valign="top"> <div> 
          <select name="esp1_modem" onChange="beginEditing(this);">
              <option ><?php echo $temp[5]->esp1; ?></option>
              <option>AMR</option>
              <option >On Board</option>
              <option>PCI</option>
              <option>Ninguno</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="observ_modem" size="14" value="<?php echo $temp[5]->obs_comp;?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="proveedor_modem">
          <option selected> 
          <?php echo $temp[5]->nbre_prov; ?>
          </option>
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>
	<option value="<? echo $prov->fields['razon_social']; ?>"> 
    <? echo cortar($prov->fields['razon_social']); ?>
    </option>
<?php
    $prov->MoveNext();
	}
?>
            </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="fact_modem" size="5" value="<?php echo $temp[5]->nro_factura; ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="garantia_modem" value="" onChange="beginEditing(this);">
              <option selected> 
              <?php echo $temp[5]->garantia_c;?>
              </option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
    </tr>
    <tr> 
      <td height="28" valign="top" width="69"><b>Micro</b></td>
      <td valign="top"> <select name="esp1_micro" onChange="beginEditing(this);">
            <option selected><?php echo $temp[6]->esp1; ?></option>
            <option>INTEL</option>
            <option>AMD</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select> </td>
      <td colspan="2" valign="top"> <select name="esp2_micro" onChange="beginEditing(this);">
            <option selected><?php echo $temp[6]->esp2; ?></option>
            <option>PENTIUM IV</option>
            <option>CELERON</option>
            <option>Athlon</option>
            <option>Athlon XP</option>
            <option>Duron</option>
            <option>Duron XP</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select> </td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="observ_micro" size="14" value="<?php echo $temp[6]->obs_comp;?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="proveedor_micro">
          <option selected> 
          <?php echo $temp[6]->nbre_prov; ?>
          </option>
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>
	<option value="<? echo $prov->fields['razon_social'];?>"> 
    <? echo cortar($prov->fields['razon_social']); ?>
   </option>
<?php
	$prov->MoveNext();
	}
?>
            </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="fact_micro" size="5" value="<?php echo $temp[6]->nro_factura;?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="garantia_micro" OnChange="beginEditing(this);">
              <option selected> 
              <?php echo $temp[6]->garantia_c;?>
              </option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
    </tr>
    <tr> 
      <td height="28" valign="top">&nbsp;</td>
      <td valign="top"> <select name="esp3_micro" onChange="beginEditing(this);">
            <option selected><?php echo $temp[6]->esp3; ?></option>
            <option>1.3 GHz</option>
            <option>1.4 GHz</option>
            <option>1.8 GHz</option>
            <option>2.0 GHz</option>
            <option>2.4 GHz</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select> </td>
      <td valign="top" colspan="2"> <select name="esp4_micro" onChange="beginEditing(this);">
            <option selected> 
            <?php echo $temp[6]->esp4; ?>
            </option>
            <option>FSB_400</option>
            <option>FSB_533</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select> </td>
      <td valign="top"><div align="center"></div></td>
      <td valign="top">&nbsp;</td>
      <td valign="top"><div align="center"></div></td>
      <td valign="top">&nbsp;</td>
    </tr>
    <tr> 
      <td height="28" valign="top" width="69"> <div> <b>Memoria</b></div></td>
      <td valign="top"> <select name="esp1_mem" onChange="beginEditing(this);">
            <option selected> 
            <?php echo $temp[7]->esp1; ?>
            </option>
            <option>DIMM</option>
            <option>DDR</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select> </td>
      <td valign="top"> <select name="esp2_mem" onChange="beginEditing(this);">
            <option selected><?php echo $temp[7]->esp2; ?></option>
            <option>32 Mb</option>
            <option>64 Mb</option>
            <option>128 Mb</option>
            <option>256 MB</option>
            <option>512 Mb</option>
            <option>1 Gb</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select> </td>
      <td valign="top" > <select name="esp3_mem" onChange="beginEditing(this);">
            <option selected><?php echo $temp[7]->esp3; ?></option>
            <option>133 MHz</option>
            <option>266 MHz</option>
            <option>333 MHz</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select> </td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="observ_mem" size="14" value="<?php echo $temp[7]->obs_comp;?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="proveedor_mem">
          <option selected> 
          <?php echo $temp[7]->nbre_prov; ?>
          </option>
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>					
	<option value="<? echo $prov->fields['razon_social']; ?>"> 
    <? echo cortar($prov->fields['razon_social']); ?>
    </option>
<?php
    $prov->MoveNext();
	}
?>
            </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="fact_mem" size="5"  value="<?php echo $temp[7]->nro_factura;?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="garantia_mem" OnChange="beginEditing(this);">
              <option selected><?php echo $temp[6]->garantia_c;?></option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
    </tr>
    <tr> 
      <td height="31" valign="top" width="69"> <div> <b>HDD</b></div></td>
      <td valign="top"> <select name="esp1_hdd" onchange="beginEditing(this);" >
              <option selected><?php echo $temp[8]->esp1; ?></option>
              <option>8,4GB</option>
              <option>10GB</option>
              <option>13,2GB</option>
              <option>15GB</option>
              <option>20GB</option>
              <option>30GB</option>
              <option>40GB</option>
              <option>60GB</option>
              <option>80GB</option>
              <option ></option>
              <option id="editable">Edite aqui</option>
            </select> </td>
      <td valign="top"> <select name="esp2_hdd" onChange="beginEditing(this);">
            <option selected><?php echo $temp[8]->esp2; ?></option>
            <option>5400rpm</option>
            <option >7200rpm</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select> </td>
      <td><select name="esp3_hdd" onChange="beginEditing(this);">
            <option selected><?php echo $temp[8]->esp3;?></option>
            <option>ATA 66</option>
            <option >ATA 100</option>
            <option>ATA 133</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="observ_hdd" size="14" value="<?php echo $temp[8]->obs_comp; ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="proveedor_hdd">
          <option selected> 
          <?php echo $temp[8]->nbre_prov; ?>
          </option>
          
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>
	<option value="<? echo $prov->fields['razon_social']; ?>"> 
    <? echo cortar($prov->fields['razon_social']); ?>
    </option>
<?php
	$prov->MoveNext();
	}
?>
            </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="fact_hdd" size="5" value="<?php echo $temp[8]->nro_factura;  ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="garantia_hdd" onChange="beginEditing(this);">
              <option selected><?php echo $temp[8]->garantia_c;?></option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
    </tr>
    <!-- juan manuel agregue el cd -->
    <tr> 
      <td valign="top" height="30" width="69"> <div> <b>CD</b></div></td>
      <td colspan="3" valign="top"> <select name="esp1_cd" onChange="beginEditing(this);">
              <option selected><?php echo $temp[11]->esp1; ?></option>
              <option>12x</option>
              <option>24x</option>
              <option>48x</option>
              <option >52x</option>
              <option>56x</option>
              <option>Ninguna</option>
              <option id="editable">Edite aqui</option>
            </select> </td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="observ_cd" size="14" value="<?php echo $temp[11]->obs_comp; ?>">
        </div></td>
      <td valign="top"> <select name="proveedor_cd">
     <option selected> 
     <?php echo $temp[11]->nbre_prov; ?>
     </option>     
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>					
	<option value="<? echo $prov->fields['razon_social']; ?>"> 
        <? echo cortar($prov->fields['razon_social']); ?>
    </option>
<?	$prov->MoveNext();
   }
?>
          </select> </td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="fact_cd" size="5" value="<?php echo $temp[11]->nro_factura;  ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="garantia_cd" onChange="beginEditing(this);">
              <option selected><?php echo $temp[11]->garantia_c;?></option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
    </tr>
    <!-- Juan Manuel fin del agregado del cd-->
    <tr> 
      <td height="29" valign="top" width="69"><b>CD/WR</b></td>
      <td colspan="3" valign="top"> <div> 
          <select name="esp1_graba" onchange="beginEditing(this);" >
              <option selected><?php echo $temp[9]->esp1; ?></option>
              <option>52x24x52</option>
              <option>48x24x48</option>
              <option>48x16x48</option>
              <option>Ninguna</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="observ_graba" size="14" value="<?php echo $temp[9]->obs_comp;?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="proveedor_graba">
          <option selected> 
          <?php echo $temp[9]->nbre_prov; ?>
          </option>     
          
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>					
     <option value="<? echo $prov->fields['razon_social']?>"> 
     <? echo cortar($prov->fields['razon_social']); ?>
     </option>
<?php
 	$prov->MoveNext();
	}
?>
            </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="fact_graba" size="5" value="<?php echo $temp[9]->nro_factura;  ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="garantia_graba" onChange="beginEditing(this);">
              <option selected><?php echo $temp[9]->garantia_c;?></option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
    </tr>
    <tr> 
      <td height="38" valign="top" width="69"> <div> <b>DVD</b></div></td>
      <td colspan="3" valign="top"> <select name="esp1_dvd" onchange="beginEditing(this);" >
              <option selected><?php echo $temp[10]->esp1;?></option>
              <option>8x</option>
              <option>12x</option>
              <option>24x</option>
              <option>Ninguno</option>
              <option id="editable">Edite aqui</option>
            </select> </td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="observ_dvd" size="14" value="<?php echo $temp[10]->obs_comp; ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="proveedor_dvd">
          <option selected> 
          <?php echo $temp[10]->nbre_prov; ?>
          </option>     
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>
	<option value="<? echo $prov->fields['razon_social']; ?>"> 
         <? echo cortar($prov->fields['razon_social']); ?>
    </option>
<?php
		$prov->MoveNext();
	}
?>
            </select>
        </div></td>
      <td valign="top"> <div align="center"> 
          <input type="text" name="fact_dvd" size="5" value="<?php echo $temp[10]->nro_factura; ?>">
        </div></td>
      <td valign="top"> <div> 
          <select name="garantia_dvd" onChange="beginEditing(this);">
              <option selected><?php echo $temp[10]->garantia_c;?></option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
        </div></td>
    </tr>
  </table>
  <p> <b>Adicionales: 
    <textarea name="adicionales" cols="84" rows="4" ><?php echo $adicional;?></textarea>
    </b> </p>
    <div><b><font face="Georgia, Times New Roman, Times, serif">KIT DE ATX
          </font></b></div>
   <?
   /*este for se ejecuta 5 veces para recuperar en cada fila  de la 
    tabla accesorios un tipo distinto*/

  for($i=0;$i<5;$i++)
    {
     $ssql="select distinct esp1, descripcion, tipo from accesorios
      where nro_orden=".$nro_orden." and tipo=".$i;
     db_tipo_res('a');
     $resultado=$db->Execute($ssql) or Error($ssql."ssql3");
     $tmp[$i]->esp1=$resultado->fields["esp1"];
	 $tmp[$i]->desc=$resultado->fields["descripcion"];
	} 
  ?>
  <table border="0" cellspacing="2" cellpadding="2" width="60%">
    <tr BGCOLOR="#006699"> 
      <td width="115"><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Ac 
        cesorio</font></b> </td>
      <td width="83"><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Modelo</font></b></td>
      <td width="200"><b><font size="3" align="center" color=<?php echo $texto_tabla?>>Observaciones</font></b></td>
    </tr>
    <tr> 
      <td height="34"><b>Teclado</b></td>
      <td height="34"> 
        <select name="esp1_tecla" onChange="beginEditing(this);">
          <option selected><?php echo $tmp[0]->esp1; ?></option>
          <option>DIN</option>
          <option>MINI DIN </option>
          <option ></option>
          <option id="editable">Edite aqui</option>
        </select>
      </td>
      <td height="34"> 
	  	<input type="text" name="observ_tecla" size="33" value="<?php echo $tmp[0]->desc; ?>" >
      </td>
    </tr>
    <tr> 
      <td><b>Mouse</b></td>
      <td> 
        <select name="esp1_mouse" onChange="beginEditing(this);">
          <option selected><?php echo $tmp[1]->esp1; ?></option>
          <option>PS/2</option>
          <option>SERIAL</option>
          <option>Ninguno</option>
          <option id="editable">Edite aqui</option>
        </select>
      </td>
      <td> 
        <input type="text" name="observ_mouse" size="33"value="<?php echo $tmp[1]->desc; ?>">
      </td>
    </tr>
    <tr> 
      <td><b>Parlantes</b></td>
      <td><b><font size="3" align="center" color=<?php echo $texto_tabla?>> 
      <select name="esp1_parla" onChange="beginEditing(this);">
          <option selected><?php echo $tmp[2]->esp1; ?></option>
          <option>220</option>
          <option >Interno</option>
          <option>Ninguno</option>
          <option id="editable">Edite aqui</option>
        </select>
        </font></b></td>
      <td> 
        <input type="text" name="observ_parla" size="33" value="<?php echo $tmp[2]->desc; ?>">
      </td>
    </tr>
    <tr> 
      <td><b>Micr&oacute;fono</b></td>
      <td> 
        <input type="checkbox" name="lleva_microfono" <?php if($tmp[3]->esp1=='on') echo 'checked'; ?>>
      </td>
      <td> 
        <input type="text" name="desc_fono" size="33" value="<?php echo $tmp[3]->desc; ?>">
      </td>
    </tr>
    <tr> 
      <td height="23"><b>Floppy</b></td>
      <td> 
        <input type="checkbox" name="lleva_floppy" <?php if($tmp[4]->esp1=='on') echo 'checked'; ?>>
      </td>
      <td valign="top">&nbsp;</td>
    </tr>
  </table>
<br>
<?
  /* Cracteristicas del monitos*/
   
  $ssql="select marca, modelo,garantia, pulgadas, nombre_proveedor, factura_monitor.nro_factura";
   $ssql.=" from (monitor left join factura_monitor on factura_monitor.id_monitor=monitor.id_monitor)";
   $ssql.="where monitor.nro_orden=".$nro_orden;
  //echo $ssql;
  $resultado = $db->Execute($ssql) or Error($db->ErrorMsg()."ssql4");
  if ($resultado->RecordCount()>0)
   $hay_monitor=1;
  else
   $hay_monitor=0;
?>
<input type="hidden" name="exist_monitor" value="<?php echo $hay_monitor; ?>">
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
    <tr> 
      <td width="75">
        <select name="marca_monitor" OnChange="beginEditing(this);" >
          <option selected><?php echo $resultado->fields["marca"]; ?></option>
          <option>ACER</option>
          <option>SAMSUNG</option>
          <option>IBM</option>
          <option>TREESOMA</option>
          <option>LIKON</option>
          <option>LG</option>
          <option>TVM</option>
          <option>PHILLIPS</option>
          <option>KELLY</option>
          <option>HP</option>
		  <option>Ninguno</option>
		  <option id="editable">Edite aqui</option>
        </select>
      </td>
      <td width="130">
        <input type="text" name="modelo_monitor" value="<?php echo $resultado->fields["modelo"]; ?>">
      </td>
      <td width="130">
        <select name="pulgadas_monitor" onChange="beginEditing(this);">
          <option selected><?php echo $resultado->fields["pulgadas"]; ?></option>
          <option >14</option>
          <option>15</option>
          <option>17</option>
          <option>19</option>
          <option>24</option>
          <option value=0></option>
          <option id="editable">Edite aqui</option>
        </select>
      </td>
      <td width="63"> 
        <select name="proveedor_monitor">
        <option selected> 
          <?php echo $resultado->fields["nombre_proveedor"]; ?>
          </option>     
        
<?	
	$prov->MoveFirst();
	while (!$prov->EOF)
	{
?>					
	<option value="<? echo $prov->fields['razon_social']; ?>"> 
    <? echo cortar($prov->fields['razon_social']); ?>
    </option>
<?php
		$prov->MoveNext();
	}
?>
        </select>
      </td>
      <td width="71">
       <?php 
         switch($resultado->fields["garantia"])
         {case 1: $se[0]="selected";break;
          case 2: $se[1]="selected";break;
          case 3: $se[2]="selected";break;
         }
       ?>  
        <select name="garantia_monitor" align="center" onChange="beginEditing(this);">
          <option value="1" <?php echo $se[0]?>>1 a&ntilde;o</option>
          <option value="2" <?php echo $se[1]?>>2 a&ntilde;os</option>
          <option value="3" <?php echo $se[2]?>>3 a&ntilde;os</option>
          <option ></option>
          <option id="editable">Edite aqui</option>
        </select>
      </td>
      <td width="132">
        <input type="text" name="fact_monitor" value="<?php echo $resultado->fields["nro_factura"]; ?>">
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
 $resultado = $db->Execute($ssql) or Error($db->ErrorMsg()."ssql5");
 $soft[0]=$resultado->fields["descripcion"];
  ?>
  <table width="90%">
    <tr> 
      <td> <b>Software y/o licencias
      </b> 
	  	<input name="desc_soft" type="text" size="50" value="<?php echo $soft[0]; ?>">
        <b>Entrega</b> 
        <input name="observ_soft" type="text" value="<?php echo $resultado->fields["observaciones"];?>" size="10">
      </td>
    </tr>
   <tr> 
      <td> <b>Garantía:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      &nbsp;&nbsp;&nbsp;&nbsp;</b> 
	  	<input name="garantia" type="text" size="50" value="<?php echo $garantia; ?>">
     </td>
    </tr>
    <tr> 
      <td></td>
    </tr>
  </table> 
  <p align="center"> 
    <input type="submit" name="Submit" value="Guardar orden">
    <input type="submit" name="Submit" value="Cancelar">
  </p>
</form>
<? }//if?>
</body>
</html>