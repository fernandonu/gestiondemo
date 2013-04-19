<?
/*
$Author: mari $
$Revision: 1.4 $
$Date: 2006/09/28 18:24:10 $
*/
require_once("../../config.php");

$cmd=$parametros['cmd'];
$sql=$parametros['sql'];
$suma_total=$parametros['suma_total'];

$sql =  ereg_replace("LIMIT 50 OFFSET 0","",$sql);

$resultado=sql($sql,"$sql") or fin_pagina();
$cantidad=$resultado->recordcount();

$nombre="RMA.xls";
$aux=array("/",",");
$aux1=array("","");
$nombre_arch = str_replace($aux,$aux1,$nombre);

excel_header("$nombre_arch");


$id_usuario=$_ses_user['id'];
$sql = "select campo,ver from configurar_vista where id_usuario=$id_usuario";
$rs = sql($sql,"$sql") or fin_pagina();
$cantid=$rs->RecordCount();
while (!$rs->EOF) {
	$r=$rs->fields['campo'];
  	$ver[$r]=$rs->fields['ver'];
  	$rs->MoveNext();
}
?>

<html>
<body>

<table width=100% align=center border=1 bordercolor=#585858 cellspacing="0" cellpadding="5">
<tr>
     <td  <?=excel_style("texto")?>  align=center>
          <b>Monto Total U$S: <?=$suma_total?></b>
     </td>
</tr>
<tr><td>&nbsp;</td></tr>
<tr>  
  <?if(($ver['cant']==1) ||($cantid==0)) {?>
     <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Cant</b>
     </td>
  <?}
  if(($ver['codigo']==1) ||($cantid==0)) { ?>
       <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Tipo</b>
       </td>
  <?}
  if(($ver['descripcion']==1) ||($cantid==0)) {?>
       <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Descripci&oacute;n Producto</b>
       </td>
  <?}
  if (($ver['id_info_rma']==1) ||($cantid==0)) {?>
       <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>N° RMA</b>
       </td>
  <?}	 
  if(($ver['dias_crea']==1) ||($cantid==0)) { ?>
       <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Dc</b>
       </td>
  <?}	 
  if(($ver['dias_env']==1) ||($cantid==0)) { ?>
       <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>De</b>
       </td>
  <?}	 
  if(($ver['nro_caso']==1) ||($cantid==0))	{ ?>
       <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Caso</b>
       </td>
       <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Atendido Por</b>
       </td> 
       <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Pm</b>
       </td>    
  <?}	
  if(($ver['razon_social']==1) ||($cantid==0)) {?>
       <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Proveedor</b>
       </td>    
  <?}
  if(($ver['monto']==1) ||($cantid==0)) {?>
       <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Monto</b>
       </td>   
  <?}
  if($cmd=="cor" || $cmd=="prov" || $cmd=="real" || $cmd=="historial") {
     if(($ver['nombre_corto']==1) ||($cantid==0)) {?>
         <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
            <b>Ubi</b>
         </td>   
     <? }
     if(($ver['lugar']==1) ||($cantid==0)) { ?>
        <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Estado</b>
        </td>
      <? }
  }
  if(($ver['void']==1) ||($cantid==0)) { ?>
        <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Void</b>
        </td>  
  <? }
  if(($ver['fecha_emision']==1) ||($cantid==0)) { ?>
        <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Fec Factura</b>
        </td>
  <? }?>
  </tr>       

<? while (!$resultado->EOF) {
    if(($ver['cant']==1) ||($cantid==0)) { ?>
       <td <?=excel_style("numero")?> width=30% align=Center><b><?=$resultado->fields["cantidad"]?></b></td>
    <?}
    if(($ver['codigo']==1) ||($cantid==0)) {?>
       <td <?=excel_style("texto")?> width=30% align=Center><b><?=$resultado->fields["codigo"]?></b></td>
    <?}
	if(($ver['descripcion']==1) ||($cantid==0)) {?> 
       <td <?=excel_style("texto")?> width=30% align=Center><b><?=$resultado->fields["descripcion"]?></b></td>
    <?}
    if(($ver['id_info_rma']==1) ||($cantid==0)) { ?>
        <td <?=excel_style("numero")?> width=30% align=Center><b><?=$resultado->fields["id_info_rma"]?></b></td>
    <?}
       
    $que=trim($resultado->fields['nombre_corto']);

    if ($que!="P") {
       if(($ver['dias_crea']==1) ||($cantid==0))
		          {
                  $fecha_actual=date("d/m/Y");
                  $fecha_base=fecha($resultado->fields["fecha_hist"]);
                  $color="";
                  $dias=diferencia_dias_habiles($fecha_base,$fecha_actual);
                  if ($dias>3) $color="yellow";
                  if ($dias>8) $color="red";
                  ?>
                  <td <?=excel_style("texto")?> width=30% align=Center><b><?echo $dias?></b></td>
                  <?
                  }
                  if(($ver['dias_env']==1) ||($cantid==0))
		          {
		          ?>
                  <td <?=excel_style("texto")?> width=30% align=Center><b><?echo "&nbsp;"?></b></td>
                  <?
		          }
		         }
              else {
              	    if(($ver['dias_crea']==1) ||($cantid==0))
		            {
                    ?>
                    <td <?=excel_style("texto")?> width=30% align=Center><b><?echo "&nbsp;"?></b></td>
                    <?
		            }

              	    if(($ver['dias_env']==1) ||($cantid==0))
		            {
              	    $fecha_actual=date("d/m/Y");
                    $fecha_base=fecha($resultado->fields["fecha_hist"]);
                    $color="";
                    $dias=diferencia_dias_habiles($fecha_base,$fecha_actual);
                    if ($dias>10) $color="yellow";
                    if ($dias>20) $color="red";
		            ?>
                    <td <?=excel_style("texto")?> width=30% align=Center><b><?echo $dias?></b></td>
            <?     }
            }
 if(($ver['nro_caso']==1) ||($cantid==0)) { ?>
            <td <?=excel_style("texto")?> width=30% align=Center><b><?=$resultado->fields["nrocaso"]?></b></td>
            <td <?=excel_style("texto")?> width=30% align=Center><b><?=$resultado->fields["nombre"]?></b></td>
            <td <?=excel_style("numero")?> width=30% align=Center><b><?=$resultado->fields["pm"]?></b></td>
 <?
 }
 if(($ver['razon_social']==1) ||($cantid==0)) {  ?>
            <td <?=excel_style("texto")?> width=30% align=Center><b><?echo $resultado->fields["razon_social"]?></b></td>
            <?
 }
 if(($ver['monto']==1) ||($cantid==0)) {  ?>
            <td <?=excel_style("texto")?> width=30% align=Center>

            <table width=100% align=Center>
            <tr>
              <td width=20% align=center><b>U$S</b></td>
              <?$tot1=$resultado->fields["precio_stock"];
                $tot=formato_money($tot1 * $resultado->fields["cantidad"]);
              ?>
              <td align=right><b><?=formato_money($resultado->fields["totales"]);?></b></td>
            </tr>
            </table>
            </td>
            <?
  }

         if($cmd=="cor" || $cmd=='prov' || $cmd=="real" || $cmd=="historial")
         {

             if(($ver['nombre_corto']==1) ||($cantid==0))
		     {
		      if($resultado->fields["comentario"]!="")
		      {
         	  ?>
              <td <?=excel_style("texto")?> width=30% align=Center><b><?echo $resultado->fields["comentario"]?></b></td><!--//Borggi-->
              <?
		      }
             else {
             ?>
              <td <?=excel_style("texto")?> width=30% align=Center><b><?echo $resultado->fields["ubicacion"]?></b></td><!--//Borggi-->
              <?
             }
		     }

         	if(($ver['lugar']==1) ||($cantid==0))
		    {
             ?>
          <td <?=excel_style("texto")?> width=30% align=Center><b><?echo $resultado->fields["lugar"]?></b></td>
          <?
		  }
          }
          if(($ver['void']==1) ||($cantid==0))
		  {
          ?>
          <td <?=excel_style("texto")?> width=30% align=Center>
          <?
          $cadena=split(' ',$resultado->fields["voids"]);
          echo $cadena[0];
          ?>
          </b></td>
          <?
		  }
          $oc=$resultado->fields['nro_orden'];
          if(($ver['fecha_emision']==1) ||($cantid==0))
		  {
		  if($oc!="")
		  {
		  ?>
		  <td <?=excel_style("texto")?> width=30% align=Center>
		  <b><?=Fecha($resultado->fields['fecha_emision'])?></b>
		  </td>
          <?}
          else{?>
          <td <?=excel_style("texto")?> width=30% align=Center>
		  &nbsp;&nbsp;
		  </td>
          <?}
		  }?>
            </tr>

          <?
           $resultado->movenext();
        }//del for
	  ?>
</table>
</td>
</tr>
</table>


</body>
</html>

