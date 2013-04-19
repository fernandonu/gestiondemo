<?php
/*
$Author: elizabeth $
$Revision: 1.32 $
$Date: 2004/02/27 15:56:34 $
*/
require_once("../../config.php");
require_once("./funciones.php");
if ($_POST['bcancelar']=="Cancelar")
{header('location: ./ordenes_ver.php');}
else
{if ($_POST['gorden']=="Guardar orden")
  require("guardar_ordenant.php");
}
//query para traer los preveedores de la tabla de proveedores.

     switch($_POST['estado'])
      {case 0:$query_proveedor="SELECT id_proveedor,razon_social from proveedor where activo='true' ORDER BY(razon_social)";
                      break;
       case 1:$query_proveedor="SELECT id_proveedor,razon_social from proveedor where activo='false' ORDER BY(razon_social)";
                      break;
       case 2:$query_proveedor="SELECT id_proveedor,razon_social from proveedor ORDER BY(razon_social)";
                      break;
      }




 
$resultados_prov=$db->Execute($query_proveedor) or die($db->ErrorMsg()."hola");
$filas_encontradas=$resultados_prov->RecordCount();

 ?>

<html>
<head>
<SCRIPT language='JavaScript' src="funciones.js"></SCRIPT>
<? cargar_calendario(); ?>
<SCRIPT language='JavaScript'>
  function comprueba(valor){
    var flag=0;
    if (valor=="guardar")
  {if (window.document.form.serial2.value=="")
   {alert("Debe generar los numeros de serie antes de guardar la orden");
    window.document.form.gorden.enabled=false;
    return(false);
   } 
   }
  if(valor=="generar")
  {if (window.document.form.f_entrega.value=="") {
                    flag=1;
                    alert("No ha ingresado FECHA DE ENTREGA ");
    				}
    if (window.document.form.cant.value==""){
 					flag=1;
    				alert("No ha ingresado la CANTIDAD DE MAQUINAS");
    			  }
   if ((window.document.form.verif.checked==false) && (window.document.form.ult_nro.value==""))
   {         alert("Debe asignar primer numero de serie o chequear serial desconocido para poder generar los numeros de serie.");
             window.document.form.gorden.enabled=false;
             return(false);
            }
    if (window.document.form.nrord.value==""){
 					flag=1;
    				alert("No ha ingresado el Numero de Orden");
    			  }
    if (flag) {window.document.form.gserial.enabled=false;
    		  return(false);
    		} 			
   }
    return(true);			  
  }


</SCRIPT>
<title>Nueva Orden Temporal</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php
include("../ayuda/ayudas.php");
?>
</head>
</head>

<body bgcolor="#E0E0E0" text="#000000">
<form name="form" action="ordenes_ant.php" method="post">


<div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/ordprod/llenar_ant.htm" ?>', 'LLENAR ORDENES DE PRODUCCION')" >
    </div>


  <table width="90%" border="0">
    <tr bgcolor="#CCCCCC">
      <td width="202"> 
        <div><b>
          Orden N&ordm;:</b>
          <input type="text" name="nrord" value="<? echo $_POST['nrord'];?>">
        </div>
      </td>
      
      <td width="190"> 
        <div align="right"><b>Fecha de inicio: </b>
          <?PHP 
          $finicio=date("d/m/Y");
          echo $finicio;
         ?>
          <input type="hidden" name="f_inicio" value=<?php echo $finicio; ?>>
        </div>
      </td>
      
  </tr>
</table>


  <table width="90%" border="0">
    <tr bgcolor="CCCCCC">
      <td width="100"> 
        <div align="left"><b>Ensamblador:</b></div>
      </td>
      <td width="246"> 
        <select name="ensamble">
        <?php
//for($i=0;$i<4;$i++) $s[$i]=" ";
$sql="select * from ensamblador";
$resultado=$db->Execute($sql) or die($db->ErrorMsg());
while (!$resultado->EOF)
{
if ($resultado->fields['nombre']==$_POST['ensamble'])
{
?>
  <option selected><?php echo $resultado->fields['nombre']; ?></option>
<?php
}
else
{
?>
 <option><?php echo $resultado->fields['nombre']; ?></option>
<?php
}
$resultado->MoveNext();
}
?>
</td>
<td width="134"> 
        <div align="right"><b>Fecha de Entrega:</b></div>
      </td>
      <td width="192"> 
		<input type="text" name="f_entrega" value="<?php echo $_POST['f_entrega']; ?>"><?php echo link_calendario("f_entrega"); ?>
	  </td>
  </tr>
</table>
<p><b>ClienteFinal:&nbsp;</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
    <textarea name="cliente" cols="83" rows="5"><?php echo $_POST['cliente']; ?></textarea>
  </p>
  <p><b>Lugar de Entrega: </b>
    <textarea name="l_entrega" cols="83" rows="5"><?php echo $_POST['l_entrega']; ?></textarea>
  </p>
  <br>
  <table width="90%" border="0">
    <tr  bgcolor="#CCCCCC"> 
      <td width="66"><b>Producto:</b></td>
      <td width="137">
	  <select name="producto" OnChange="beginEditing(this);">
<?php
$s="";
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
          <option id=editable <?php if (($s=="") && ($c=="") && (isset($_POST['producto']))) echo "selected"; ?>><?php if (($s=="") && ($c=="") && (isset($_POST['producto']))) echo $_POST['producto']; else echo "Edite aqui";?></option>
        </select>
      </td>
      <td width="58">&nbsp;</td>
      <td width="410">&nbsp;</td>
    </tr>
    <tr bgcolor="CCCCCC"> 
      <td width="66"> 
        <div align="left"><b>Modelo:</b></div>
      </td>
      <td width="137"> 
        <select name="modelo_cdr" OnChange="beginEditing(this);">
 <?php 
$m="";
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
         <option id=editable <?php if (($e=="") && ($s=="") && ($m=="") && (isset($_POST['modelo_cdr']))) echo "selected"; ?>><?php if (($s=="") && ($e=="") && ($m=="") && (isset($_POST['modelo_cdr']))) echo $_POST['modelo_cdr']; else echo "Edite aqui";?></option>
        </select>
      </td>
      <td width="58"> 
        <div align="right"><b>Cantidad:</b></div>
      </td>
      <td width="410"> 
        <input type="text" name="cant" value=<?php echo $_POST['cant']; ?>>
      </td>
    </tr>
  </table>

  <?php
  if ($_POST['gserial']=="Generar Nro de Serie")
   //gen_serial2($_POST['ensamble'],$_POST['f_entrega'],$_POST['modelo_cdr'],$_POST['producto']);
   gen_serial2($_POST['ensamble'],$_POST['f_entrega'],$_POST['modelo_cdr'],$_POST['ult_nro'], $_POST['letra'], $_POST['verif'],$_POST['producto']);

  elseif( $_POST["filtro"]=="activado")
  {$serialp=$_POST["serial1"];
   $serialu=$_POST["serial2"];
   $pserial=$_POST["serialp"];
   $userial=$_POST["serialu"];
   $primer_ser=$_POST["primero"];
   $parte_serial=$_POST["parte"];
   $letra=$_POST["letra"];
  } 
?>
<input type="hidden" name="primero" value=<?php echo $primer_ser; ?>>
<input type="hidden" name="parte" value=<?php echo $parte_serial; ?>>
<input type="hidden" name="serial1" value=<?php echo $serialp; ?>>
<input type="hidden" name="serial2" value=<?php echo $serialu; ?>>
<input type="hidden" name="serialp" value=<?php echo $pserial; ?>>
<input type="hidden" name="serialu" value=<?php echo $userial; ?>>
<input type="hidden" name="letra" value=<?php echo $letra; ?>>

  <table border="0" cellspacing="0" cellpadding="0" width="90%">
    <tr> 
      <td width="263"> Primer N&ordm;de Serie: 
        <input type="text" name="ult_nro" value="<?php echo $_POST['ult_nro']; ?>">
      </td>
      <td width="405" colspan="2">&nbsp;&nbsp;&nbsp; Letra: 
        <select name="letra" value="<? echo $_POST['letra'];?>">
          <option value="A">A</option>
          <option value="B">B</option>
        </select>
        &nbsp; 
        <input type="submit" name="gserial" value="Generar Nro de Serie" onClick="return comprueba('generar');">
        <?PHP if ($_POST['gserial']=="Generar Nro de Serie") {echo $serialp."......".$serialu;}elseif(($_POST["filtro"]=="activado")&&($_POST["letra"]!="")) {echo $_POST['serial1']."......".$_POST['serial2'];} ?>
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
    <tr>
      <td>Serial Desconocido: <input type="checkbox" name="verif" value="on" <?php if ($_POST['verif']=="on") echo "checked" ?>></td>
      <td></td>
    </tr>  
    <tr>
    <td colspan="3" align="right"><b>Proveedores&nbsp;</b>
       <select name="estado" value="est" Onchange='document.form.filtro.value="activado"; document.form.submit()'>
              <option value="0" <?php echo $act;?>>Activos</option>
              <option value="1" <?php echo $noact;?>>No Activos</option>
              <option value="2" <?php echo $todos;?>>Todos</option>
         </select>
         
     </td>  
    </tr>
 </table>
  <INPUT type="hidden" name="filtro" value=""> 

 <br> 
   <div><b><font face="Georgia, Times New Roman, Times, serif">COMPONENTES
          </font></b></div>

          <table border="0" cellspacing="2" cellpadding="0" height="25">
      <tr BGCOLOR="#006699"> 
        
      <td valign="top"> 
        <div><b><font size="3" align="center" color="#CCCCCC" >Descripci&oacute;n&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</font></b></div>
        </td>
        
      <td valign="top"> 
        <div><b><font size="3" align="center" color="#CCCCCC">Observaciones&nbsp&nbsp&nbsp</font></b></div>
        </td>
        
      <td valign="top"> 
        <div><b><font size="3" align="center" color="#CCCCCC">Proveedor&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</font></b></div>
        </td>
        
      <td> 
        <div><b><font size="2" align="center" color="#CCCCCC">Nº Factura&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</font></b></div>
        </td>
        
      <td valign="top"> 
        <div><b><font size="3" align="center" color="#CCCCCC">Garant&iacute;a&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</font></b></div>
        </td>
      </tr>
    </table> 
  
 
    <table border="0" cellspacing="2" cellpadding="2" width="90%">
      <tr> 
        <td width="62" valign="top" height="42"><b>Sist.Op.</b></td>
        <td colspan="2" valign="top"> 
          <select name="esp1_so" onChange="beginEditing(this);">
             <option>Windows</option>
             <option>Linux</option> 
             <option></option>
             <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td valign="top" colspan="5"> 
          <div> 
            <select name="esp2_so" onChange="beginEditing(this);" OnClick="if(this.options.length==1) beginEditing(this);">
              <option selected>XP Pro</option>
              <option>XP Home</option>
              <option>2000 Pro</option>
              <option>2000 Server</option>
              <option>Me</option>
              <option>98</option>
              <option>95</option>
              <option>NT</option>
              <option>Red Hat</option>
              <option>Suse</option>
              <option>Mandrake</option>
              <option>Corel</option>
              <option ></option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <input type="text" name="observ_so" size="14" value="OEM">
          </div>
        </td>
        <td valign="top" width="138"> 
          <div> 
            <select name="proveedor_so">
<?  
$resultados_prov->MoveFirst();          
for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value='$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}	    
	            
    ?>
         </select>
          </div>
        </td>
        <td valign="top" width="35"> 
          <div> 
            <input type="text" name="fact_so" size="5">
          </div>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <select name="garantia_so" OnChange="beginEditing(this);">
              <option selected>no tiene</option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
      </tr>
      <tr> 
        <td valign="top" width="62" height="42"><b>Tarjeta Madre</b></td>
        <td valign="top" colspan="2"> 
          <select name="esp1_mother" onChange="beginEditing(this);">
            <option selected>PCCHIPS</option>
            <option>INTEL</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td valign="top" colspan="5"> 
          <div> 
            <select name="esp2_mother" OnChange="beginEditing(this);">
              <option>810</option>
              <option>825</option>
              <option selected>845 GLLY</option>
              <option>845 WDA-E</option>
              <option>925</option>
              <option>930</option>
              <option>935</option>
              <option ></option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <input type="text" name="observ_mother" size="14">
          </div>
        </td>
        <td valign="top" width="138"> 
          <div>    
            <select name="proveedor_mother" OnChange="activar_prov();">
            
<? 
$resultados_prov->MoveFirst();
	for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value='$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}		
        
              ?>
            </select>
          </div>
        </td>
        <td valign="top" width="35"> 
          <div> 
            <input type="text" name="fact_mother" size="5">
          </div>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <select name="garantia_mother" OnChange="beginEditing(this); activar_gar();">
              <option selected>no tiene</option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
      </tr>
      <tr> 
        <td valign="top" rowspan="2" width="62"><b>Video</b></td>
        <td valign="top" rowspan="2" colspan="2"> 
          <select name="esp1_video" onchange="beginEditing(this);">
            <option selected>On Board</option>
            <option>PCI</option>
            <option>Ninguno</option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td valign="top" colspan="5" rowspan="2"> 
          <div> 
            <select name="esp2_video" onchange="beginEditing(this);">
            <option>1 MB</option>
            <option>2 MB</option>
            <option>4 MB</option>
            <option>8 MB</option>
            <option>16 MB</option>
            <option>32 MB</option>
            <option>64 MB</option>
            <option>128 MB</option>
            <option>256 MB</option>
            <option selected ></option>
            <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td valign="top" height="42" width="80"> 
          <div> 
            <input type="text" name="observ_video" size="14">
          </div>
        </td>
        <td valign="top" width="138"> 
          <div> 
            <select name="proveedor_video">
<?  
$resultados_prov->MoveFirst();          
for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value='$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}	            
  ?>          
            </select>
          </div>
        </td>
        <td valign="top" width="35"> 
          <div> 
            <input type="text" name="fact_video" size="5">
          </div>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <select name="garantia_video" OnChange="beginEditing(this);">
              <option selected>no tiene</option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
      </tr>
      <tr> 
        <td valign="top" rowspan="2" width="80"> 
          <div> 
            <input type="text" name="observ_sonido" size="14">
          </div>
        </td>
        <td rowspan="2" valign="top" width="138"> 
          <div> 
            <select name="proveedor_sonido">
   <?  
$resultados_prov->MoveFirst();          
for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value='$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}	            
  ?>                  
           </select>
          </div>
        </td>
        <td rowspan="2" valign="top" width="35"> 
          <div> 
            <input type="text" name="fact_sonido" size="5">
          </div>
        </td>
        <td rowspan="2" valign="top" width="80"> 
          <div> 
            <select name="garantia_sonido" OnChange="beginEditing(this);">
              <option selected>no tiene</option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td height="2" width="0"></td>
      </tr>
      <tr> 
        <td valign="top" rowspan="2" width="62"><b>Sonido</b></td>
        <td colspan="7" valign="top" rowspan="2"> 
          <div> 
            <select name="esp1_sonido" onchange="beginEditing(this);">
              <option selected>On Board</option>
              <option>PCI</option>
              <option>Ninguno</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td height="38" width="0"></td>
      <tr> 
        <td valign="top" rowspan="2" width="80"> 
          <div> 
            <input type="text" name="observ_red" size="14">
          </div>
        </td>
        <td valign="top" rowspan="2" width="138"> 
          <div> 
            <select name="proveedor_red">
<?  
$resultados_prov->MoveFirst();          
for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value='$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}	                
	?>
            </select>
          </div>
        </td>
        <td valign="top" rowspan="2" width="35"> 
          <div> 
            <input type="text" name="fact_red" size="5">
          </div>
        </td>
        <td valign="top" rowspan="2" width="80"> 
          <div> 
            <select name="garantia_red" OnChange="beginEditing(this);">
              <option selected>no tiene</option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
      <tr> 
        <td valign="top" width="62" height="40"><b>LAN</b></td>
        <td valign="top" colspan="2"> 
          <select name="esp1_red" onchange="beginEditing(this);">
            <option selected>On Board</option>
            <option>PCI</option>
            <option>Ninguno</option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td valign="top" colspan="5"> 
          <div> 
            <select name="esp2_red" onchange="beginEditing(this);">
              <option>10 Mbps</option>
              <option selected>10/100 Mbps</option>
              <option ></option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
      </tr>
      <tr> 
        <td valign="top" width="62" height="42"><b>Modem</b></td>
        <td colspan="7" valign="top"> 
          <div> 
            <select name="esp1_modem" onchange="beginEditing(this);">
              <option>AMR</option>
              <option selected>On Board</option>
              <option>PCI</option>
              <option>Ninguno</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <input type="text" name="observ_modem" size="14">
          </div>
        </td>
        <td valign="top" width="138"> 
          <div> 
            <select name="proveedor_modem">
            <?  
$resultados_prov->MoveFirst();          
for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value'$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}	
	?>    
            </select>
          </div>
        </td>
        <td valign="top" width="35"> 
          <div> 
            <input type="text" name="fact_modem" size="5">
          </div>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <select name="garantia_modem" OnChange="beginEditing(this);">
              <option selected>no tiene</option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
      </tr>
      <tr> 
        <td valign="top" width="62" height="42"><b>Micro P.</b></td>
        <td valign="top" colspan="2"> 
          <select name="esp1_micro" onchange="beginEditing(this);">
            <option selected>INTEL</option>
            <option>AMD</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td colspan="5" valign="top"> 
          <select name="esp2_micro" onchange="beginEditing(this);">
            <option selected>PENTIUM IV</option>
            <option>CELERON</option>
            <option>Athlon</option>
            <option>Athlon XP</option>
            <option>Duron</option>
            <option>Duron XP</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <input type="text" name="observ_micro" size="14">
          </div>
        </td>
        <td valign="top" width="138"> 
          <div> 
            <select name="proveedor_micro">
<?  
$resultados_prov->MoveFirst();          
for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value='$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}	
	?>                
            </select>
          </div>
        </td>
        <td valign="top" width="35"> 
          <div> 
            <input type="text" name="fact_micro" size="5">
          </div>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <select name="garantia_micro" OnChange="beginEditing(this);">
              <option selected>no tiene</option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
      </tr>
      <tr> 
        <td valign="top" width="62" height="42">&nbsp;</td>
        <td valign="top" colspan="2"> 
          <select name="esp3_micro" onchange="beginEditing(this);">
            <option>1.3 GHz</option>
            <option>1.4 GHz</option>
            <option selected>1.8 GHz</option>
            <option>2.0 GHz</option>
            <option>2.4 GHz</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td valign="top" colspan="2"> 
          <select name="esp4_micro" onchange="beginEditing(this);">
            <option  selected></option>
            <option>FSB_400</option>
            <option>FSB_533</option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td colspan="8" valign="top">&nbsp;</td>
      </tr>
      <tr> 
        <td valign="top" width="62" height="32"> 
          <div> <b>Memoria</b></div>
        </td>
     <td valign="top" width="72"> 
          <select name="esp1_mem" onchange="beginEditing(this);">
            <option>DIMM</option>
            <option selected>DDR</option>
            <option>Ninguna</option>
            <option id="editable">Edite aqui</option> 
          </select>
        </td>
        <td width="0"></td>
        <td width="74" valign="top"> 
          <select name="esp2_mem" onchange="beginEditing(this);">
            <option>32 Mb</option>
            <option>64 Mb</option>
            <option selected>128 Mb</option>
            <option>256 MB</option>
            <option>512 Mb</option>
            <option>1 Gb</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td valign="top" colspan="3" > 
          <select name="esp3_mem" onChange="beginEditing(this);">
            <option  selected></option>
            <option>133 MHz</option>
            <option>266 MHz</option>
            <option>333 MHz</option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td width="0" ></td>
        <td valign="top" width="80" > 
          <div> 
            <input type="text" name="observ_mem" size="14">
          </div>
        </td>
        <td valign="top" width="138" > 
          <div> 
            <select name="proveedor_mem">
<?  
$resultados_prov->MoveFirst();          
for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value='$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}	    
	?>
         </select>
          </div>
        </td>
        <td valign="top" width="35" > 
          <div> 
            <input type="text" name="fact_mem" size="5">
          </div>
        </td>
        <td valign="top" width="80" > 
          <div> 
            <select name="garantia_mem" OnChange="beginEditing(this);">
              <option selected>no tiene</option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
      </tr>
      <tr> 
        <td valign="top" width="62" height="42"> 
          <div> <b>HDD</b> </div>
        </td>
        <td valign="top" colspan="2"> 
          <select name="esp1_hdd" onChange="beginEditing(this);">
            <option>8,4GB</option>
            <option>10GB</option>
            <option>13,2GB</option>
            <option>15GB</option>
            <option selected>20GB</option>
            <option>30GB</option>
            <option>40GB</option>
            <option>60GB</option>
            <option>80GB</option>
            <option>Ninguno</option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td valign="top" width="74"> 
          <select name="esp2_hdd" onchange="beginEditing(this);">
            <option>5400rpm</option>
            <option selected>7200rpm</option>
            <option ></option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td valign="top" colspan="2"> 
          <div> 
            <select name="esp3_hdd" onchange="beginEditing(this);">
              <option>ATA 66</option>
              <option selected>ATA 100</option>
              <option>ATA 133</option>
              <option ></option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
        <td width="0"></td>
        <td valign="top" width="80"> 
          <div> 
            <input type="text" name="observ_hdd" size="14">
          </div>
        </td>
        <td valign="top" width="138"> 
          <div> 
            <select name="proveedor_hdd">
            <?  
$resultados_prov->MoveFirst();          
for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value='$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}	    
	?>
         </select>
          </div>
        </td>
        <td valign="top" width="35"> 
          <div> 
            <input type="text" name="fact_hdd" size="5">
          </div>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <select name="garantia_hdd" OnChange="beginEditing(this);">
              <option selected>no tiene</option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
      </tr>
      <!-- aca agrego el CD -->
      <tr> 
        <td valign="top" width="62" height="42"> 
          <div> <b>CD</b></div>
        </td>
        <td colspan="7" valign="top"> 
          <select name="esp1_cd" onChange="beginEditing(this);">
            <option>12x</option>
            <option>24x</option>
            <option>48x</option>
            <option selected>52x</option>
            <option>56x</option>
            <option>Ninguna</option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td width="80"> 
          <input type="text" name="observ_cd" size="14">
        </td>
        <td valign="top" width="138"> 
          <select name="proveedor_cd">
          
          <?  
$resultados_prov->MoveFirst();          
for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value='$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}	 
	?>   
        </select>
        </td>
        <td valign="top" width="35"> 
          <input type="text" name="fact_cd" size="5">
        </td>
        <td valign="top" width="80"> 
          <div> 
            <select name="garantia_cd" OnChange="beginEditing(this);">
              <option selected>no tiene</option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
      </tr>
      <tr> 
        <td width="62" height="0"></td>
        <td width="72"></td>
        <td width="0"></td>
        <td width="74"></td>
        <td width="28"></td>
        <td width="39"></td>
        <td width="0"></td>
        <td width="0"></td>
        <td width="80"> </td>
        <td width="138"></td>
        <td width="35"></td>
        <td width="80"></td>
        <td width="0"></td>
      </tr>
      <!-- aca termina el agregado del cd -->
      <tr> 
        <td valign="top" width="62" height="42"><b>CD/WR</b></td>
        <td valign="top" colspan="7"> 
          <div> 
            <select name="esp1_graba" onchange="beginEditing(this);">
              <option>52x24x52</option>
              <option>48x24x48</option>
              <option>48x16x48</option>
              <option selected>Ninguna</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <input type="text" name="observ_graba" size="14">
          </div>
        </td>
        <td valign="top" width="138"> 
          <div> 
            <select name="proveedor_graba">
<?  
$resultados_prov->MoveFirst();          
for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value='$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}
	?>	    
           </select>
          </div>
        </td>
        <td valign="top" width="35"> 
          <div> 
            <input type="text" name="fact_graba" size="5">
          </div>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <select name="garantia_graba" OnChange="beginEditing(this);">
              <option selected>no tiene</option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
      </tr>
      <tr> 
        <td valign="top" width="62" height="42"> 
          <div> <b>DVD</b></div>
        </td>
        <td colspan="7" valign="top"> 
          <select name="esp1_dvd" onChange="beginEditing(this);">
            <option>8x</option>
            <option>12x</option>
            <option>24x</option>
            <option selected>Ninguno</option>
            <option id="editable">Edite aqui</option>
          </select>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <input type="text" name="observ_dvd" size="14">
          </div>
        </td>
        <td valign="top" width="138"> 
          <div> 
            <select name="proveedor_dvd">
          <?  
$resultados_prov->MoveFirst();          
for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value='$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}	    
	?>
            </select>
          </div>
        </td>
        <td valign="top" width="35"> 
          <div> 
            <input type="text" name="fact_dvd" size="5">
          </div>
        </td>
        <td valign="top" width="80"> 
          <div> 
            <select name="garantia_dvd" OnChange="beginEditing(this);">
              <option selected>no tiene</option>
              <option>6 meses</option>
              <option>8 meses</option>
              <option>10 meses</option>
              <option>1 año</option>
              <option id="editable">Edite aqui</option>
            </select>
          </div>
        </td>
        <td width="0"></td>
      </tr>
    </table>
</div>
<p><b>Adicionales: </b>
    <textarea name="adicionales" cols="84" rows="4"></textarea>
  </p>
    <div><b><font face="Georgia, Times New Roman, Times, serif">KIT DE ATX
          </font></b></div>
  <table border="0" cellspacing="2" cellpadding="2" width="60%">
    <tr BGCOLOR="#006699">
      <td width="115"><b><font size="3" align="center" color="#CCCCCC">Accesorio</font></b>
	  </td>
      <td width="83"><b><font size="3" align="center" color="#CCCCCC">Modelo</font></b></td>
      <td width="200"><b><font size="3" align="center" color="#CCCCCC">Observaciones</font></b></td>
  </tr>
  <tr>
      <td><b>Teclado</b></td>
    <td><select name="esp1_tecla" onchange="beginEditing(this);">
          <option>DIN</option>
          <option selected>MINI DIN </option>
          <option>Ninguno</option>
          <option id="editable">Edite aqui</option>
        </select></td>
    <td>
        <input type="text" name="observ_tecla" size="33" >
      </td>
  </tr>
  <tr>
      <td><b>Mouse</b></td>
       <td>
        <select name="esp1_mouse" onchange="beginEditing(this);">
          <option selected>PS/2</option>
          <option>SERIAL</option>
		  <option>Ninguno</option>
		  <option id="editable">Edite aqui</option>
        </select>
      </td>
    <td>
        <input type="text" name="observ_mouse" size="33">
      </td>
  </tr>
  <tr>
      <td><b>Parlantes</b></td>
      <td><select name="esp1_parla" onchange="beginEditing(this);">
          <option>220</option>
          <option selected>Interno</option>
		  <option>Ninguno</option>
		  <option id="editable">Edite aqui</option>
        </select>
	  </td>
    <td>
        <input type="text" name="observ_parla" size="33">
      </td>
  </tr>
  <tr>
      <td><b>Micr&oacute;fono</b></td>
      
      <td> 
        <input type="checkbox" name="lleva_microfono" >
      </td>
    <td>
        <input type="text" name="observ_microfono" size="33">
      </td>
  </tr>
  <tr>
      <td><b>Floppy</b></td>
     
      <td>
        <input type="checkbox" name="lleva_floppy" checked>
      </td>
  </tr>
</table>
<br>

  <div><b><font face="Georgia, Times New Roman, Times, serif">MONITOR
          </font></b></div>
  <table border="0" cellspacing="2" cellpadding="2" width="90%">
    <tr BGCOLOR="#006699"> 
      <td width="130"> 
        <div><b><font size="3" align="center" color="#CCCCCC">Marca</font></b></div>
      </td>
      <td width="130"> 
        <div><b><font size="3" align="center" color="#CCCCCC">Modelo</font></b></div>
      </td>
      <td width="63"> 
        <div><b><font size="3" align="center" color="#CCCCCC">Pulgadas</font></b></div>
      </td>
      <td width="71">
	   <div><b><font size="3" align="center" color="#CCCCCC">Proveedor</font></b></div>
	  </td>
      <td width="132">
	   <div><b><font size="3" align="center" color="#CCCCCC">Garantia</font></b></div>
	  </td>
	  <td>
	   <div><b><font size="3" align="center" color="#CCCCCC">Nº Factura</font></b></div>
	  </td>
    </tr>
    <tr> 
      <td width="75">
        <select name="marca_monitor" OnChange="beginEditing(this);">
          <option>SAMSUNG</option>
          <option>IBM</option>
          <option>TREESOMA</option>
          <option>LIKON</option>
          <option>LG</option>
          <option>TVM</option>
          <option>PHILLIPS</option>
          <option>KELLY</option>
          <option>HP</option>
		  <option selected>Ninguno</option>
		  <option id="editable">Edite aqui</option>
        </select>
      </td>
      <td width="130">
        <input type="text" name="modelo_monitor">
      </td>
      <td width="130">
        <select name="pulgadas_monitor" onchange="beginEditing(this);">
          <option value=0 selected> </option>
          <option>14</option>
          <option>15</option>
          <option>17</option>
          <option>19</option>
          <option>24</option>
          <option ></option>
          <option id="editable">Edite aqui</option>
        </select>
      </td>
      <td width="63"> 
        <select name="proveedor_monitor">
        
<?  
$resultados_prov->MoveFirst();          
for($i=0;$i<$filas_encontradas;$i++) {
		$proveedor=$resultados_prov->fields['razon_social'];
		echo "<option value='$proveedor'>".cortar($proveedor)."</option>";
		$resultados_prov->MoveNext();
	}	
	?>    
        </select>
      </td>
      <td width="71">
        <select name="garantia_monitor" align="center" OnChange="beginEditing(this);">
          <option value="1"selected>1 a&ntilde;o</option>
          <option value="2">2 a&ntilde;os</option>
          <option value="3">3 a&ntilde;os</option>
          <option value="0"></option>
          
        </select>
      </td>
      <td width="132">
        <input type="text" name="fact_monitor">
      </td>
    </tr>
   </table>
  <br>
    <div><b><font face="Georgia, Times New Roman, Times, serif">OTROS
          </font></b></div>
  <table width="90%">
    <tr> 
      <td> <b>Software y/o licencias</b> 
        <input name="desc_soft" type="text" size="50">
        <b>Entrega </b>
<input name="observ_soft" type="text" value="CORADIR" size="10">
      </td>
    </tr>
  </table> 
  <p align="center"> 
    <input type="submit" name="gorden" value="Guardar orden" onClick="return comprueba('guardar');">
    <input type="submit" name="bcancelar" value="Cancelar">
  </p>
   </div>
</form>
<?php
// }
//} //fin else
?>
</body>
</html>