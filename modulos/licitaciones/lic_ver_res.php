<?
/*
Author: GACZ

MODIFICADA POR
$Author: fernando $
$Revision: 1.45 $
$Date: 2005/11/03 18:29:07 $
*/

require_once("../../config.php");


function obtener_montos_renglones($datos){

    $datos->move(0);
    $cantidad=$datos->recordcount();
    $monto_coradir=1;

    for($i=0;$i<$cantidad;$i++){
        if ($datos->fields["id_competidor"]==1)
                {
                $id_renglon=$datos->fields["id_renglon"];
                $monto_coradir=$datos->fields["monto_unitario"];

                $array_montos[$id_renglon]=$monto_coradir;
                }
        $datos->movenext();
        }//del for
 return $array_montos;
}//de la funcion


function obtener_diferencias($monto_coradir,$monto_competidor){

   // echo "$monto_coradir ***** $monto_competidor";
     if ($monto_competidor<$monto_coradir)
                       $diferencia=$monto_competidor/$monto_coradir;
     if ($monto_competidor>$monto_coradir)
                       $diferencia=$monto_coradir/$monto_competidor;
     if ($monto_competidor==$monto_coradir)
                       $diferencia=1;

     $diferencia=number_format($diferencia,"4",",","");
return $diferencia;
}


//extrae las variables de POST
extract($_POST,EXTR_SKIP);
if ($parametros['pagina_volver']=="")
	$parametros['pagina_volver']="licitaciones_view.php";

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);
/*if (!$keyword)
 $keyword=-1;*/

if($boton=="Eliminar")
{
 $query="delete from oferta where id=$radio_oferta";
 if(sql($query))
   $mensaje="<center><b>LA OFERTA SELECCIONADA SE ELIMINO CON EXITO</b></center>";
 else 
   $mensaje="<center><b>NO SE PUDO ELIMINAR LA OFERTA SELECCIONADA</b></center>";  
}

if ($download)
{
 excel_header("Resultados Lic_$keyword.xls");
 ?>
 <html>
 <head> 
             <style type="text/css">
            <!--
            <? require('../../lib/estilos.css') ?>
            -->
            </style>
 </head>
 <body>
 
<?
}
                else
                {
                echo $html_header;
                cargar_calendario();
                include("../ayuda/ayudas.php");
                    
                }

/**
if (!$download) {
    }
    else
        {/*
            ?>
            <?	   	
     }
     */

?>


<br>
<? if (!$download) {?>
<table width="100%">
<tr>
<?if($buscar || $keyword){?>

<td>
<a href='<?php echo encode_link("lic_ver_res2.php",array("keyword"=>$keyword,"pag_ant"=>"lic","pagina_volver"=>$pagina_volver)); ?>' onmouseover="sobre(this,'#ffffff')" onmouseout="bajo(this,'transparent')">Vista 2</a>
</td>

<?/*
<td align="left" width="5%" class='bordes' bgcolor=#EEEEEE>
<font color="Blue" onclick="<?if ($buscar) echo 'window.close();'; else echo 'history.go(-1);';?>" style="cursor:hand;" onmouseover="sobre(this,'#ffffff')" onmouseout="bajo(this,'transparent')"><?if ($buscar) echo 'Cerrar'; else echo 'Volver';?></font>
</td>*/
?>

<?} else?>
<td align="right" width="95%">
<!--<a href="#" onClick="abrir_ventana('<?php // echo "$html_root/modulos/ayuda/licitaciones/ayuda_ver_res.htm" ?>', 'RESULTADOS DE LA LICITACION')"> Ayuda </a>-->
<? 
if (!$buscar) {?>
<img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_ver_res.htm" ?>', 'RESULTADOS DE LA LICITACION')" >
<? } ?>
</td>
</tr>
</table>
<? }?>
<script>
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;src.style.cursor="hand";
}
function bajo(src,color_default) {
    src.style.backgroundColor=color_default;src.style.cursor="default";
}
</script>
<?
//si se requiere buscar requerir el archivo sino no

if (!$buscar)
{
	if ($keyword) {
		$select_buscar = 1;
		$chk_busqueda_general = 1;
		require_once("lic_proc_buscar_res.php");
		$datos=sql($query) or fin_pagina();
	} else {
		$submit_page="lic_ver_res.php";
		require_once("lic_buscar_res.php");
		echo "<br>";
	}
}
else
{
	require_once("lic_proc_buscar_res.php");
	$datos=sql($query) or fin_pagina();
}
//die ("<br>$query<br>query");
//--------------------------------

if ($datos && $datos->RecordCount())
{
?>
  <DIV align="center">
  <b>
   <font color=blue size=3>
   Cantidad de Resultados <?=$datos->RecordCount();?>
   </font>
   </b>
  </DIV>
  <?
   if($pagina=="cargar_resultados")
   {
   	$link=encode_link("lic_ver_res.php",array('keyword'=>$keyword,'pagina'=>"cargar_resultados","pag_ant"=>$pag_ant,"pagina_volver"=>$pagina_volver));
   	echo $mensaje;
   	?>
    <form name="formulario" method="post" action="<?=$link?>" >
    <div style='position:relative; width:100%;height:75%; overflow:auto;'>
<?
   }

 $montos_coradir=obtener_montos_renglones($datos);

 $datos->MoveFirst();
//imprime las licitaciones
while (!$datos->EOF)
{
?>
  <table width="98%" bgcolor=<?=$bgcolor_out?> cellspacing=0 border=1 bordercolor=#000000 <? //if ($download) echo "bgcolor=gray" ?> >
    <tr>
    <? $parametros=array('ID'=>$datos->fields['id_licitacion'],'cmd1'=>'detalle',"pagina"=>$parametros["pag_ant"]); ?>
      <td <?=(($download)?"bgcolor=black":"id=mo") ?> colspan="6" align="center" bordercolor='#000000'>
<? if (!$download) {?>
		<table width="100%">
		<tr>
		  <td width="10">
<?	if (permisos_check("inicio","excel_res_lic"))  {?>
		  <a target="_blank" href="<?=encode_link($_SERVER['SCRIPT_NAME'],array("download"=>true,"keyword"=>$datos->fields['id_licitacion'])) ?>">
			  	<img src="../../imagenes/excel.gif" border="0" align="middle" title="Bajar Resultados en un Excel"></img>
		  </a>
<?  }
	else {
		echo "&nbsp;";
	}	
?>		  
		  </td>
		  <td align="center">
		      <a href="<?=encode_link($pagina_volver,$parametros) ?>" title="Haga click para ver el detalle de la licitacion">
		      &nbsp;Resultados Licitacion N&ordm; &nbsp;<?=$datos->fields['id_licitacion'] ?>
		      &nbsp;&nbsp; - &nbsp; Entidad: &nbsp;<?=$datos->fields['nbre_entidad'] ?>
		      &nbsp;&nbsp; - &nbsp; Disrito: &nbsp;<?=$datos->fields['nbre_distrito'] ?>
		      </a>
	      </td>
	     </tr>
	     </table>
<? } 
   else {
?>	     	<font color=#FFFFFF>
			 <b>
		      &nbsp;Resultados Licitacion N&ordm; &nbsp;<?=$datos->fields['id_licitacion'] ?>
		      &nbsp;&nbsp; - &nbsp; Entidad: &nbsp;<?=$datos->fields['nbre_entidad'] ?>
		      &nbsp;&nbsp; - &nbsp; Disrito: &nbsp;<?=$datos->fields['nbre_distrito'] ?>
		     </b>
		    </font>
<? }?>	
      </td>
    </tr>
    <?
	//imprime los renglones
	$id_lic_actual=$datos->fields['id_licitacion'];



	 while (!$datos->EOF && $id_lic_actual==$datos->fields['id_licitacion'])
	 {
?>
<!-- VALORES VIEJOS
	<tr id=ma bordercolor='#000000'>
      <td width="13%" align="center" nowrap>Renglon &nbsp;
        <?//=$datos->fields['nro_renglon'] ?>
      </td>
      <td width="9%" align="center" nowrap>Item &nbsp;
        <?//=$datos->fields['nro_item'] ?>
      </td>
      <td width="15%" align="center" nowrap>Alternativa &nbsp;
        <?//=$datos->fields['nro_alternativa'] ?>
      </td>
      <td width="13%" align="center" nowrap>Cantidad &nbsp;
        <?//=($datos->fields['cantidad'])?$datos->fields['cantidad']:'&nbsp' ?>
      </td>
      <td colspan="4" align="center">
        <?//=($datos->fields['titulo'])?$datos->fields['titulo']:'&nbsp' ?>
      </td>
    </tr>
 -->
	<tr id=ma bordercolor='#000000'>
      <td width="25%" colspan="1" align="left" nowrap>Renglon &nbsp;
        <?=$datos->fields['codigo_renglon'] ?>
      </td>
      <td width="3%" align="center" nowrap>Cantidad &nbsp;
        <?=($datos->fields['cantidad'])?$datos->fields['cantidad']:'&nbsp;' ?>
      </td>
      <td colspan="4" align="center">
        <?=($datos->fields['titulo'])?$datos->fields['titulo']:'&nbsp;' ?>
      </td>
    </tr>
    <tr id=ma bordercolor='#000000'>
      <td width="20%" nowrap>Competidores</td>
      <td width="3%" nowrap>Marc.   <?=formato_money($datos->fields['ganancia'])?></td>
      <td width="10%" align="center" colspan="1" nowrap>Oferta Unitaria</td>
      <td width="10%" align="center" nowrap>Oferta Total</td>
      <td width="45%" nowrap align="center">Observacion</td>
      <td align=center>Diferencia</td>
    </tr>
    <?
	$id_renglon_actual=$datos->fields['id_renglon'];
		   //imprime los competidores
			while (!$datos->EOF && $id_renglon_actual==$datos->fields['id_renglon'])
			 {
				$parametros['id_moneda']=$datos->fields['id_moneda'];
			 	$parametros['id_lic']=$datos->fields['id_licitacion'];
			 	$parametros['id_renglon']=$datos->fields['id_renglon'];
			 	$parametros['id_competidor']=$datos->fields['id_competidor'];
                $parametros['pagina_viene']="lic_ver_res";
                $parametros['pagina_volver']=$pagina_volver;
			 	$link=encode_link("lic_cargar_res.php",$parametros);

                 if (!$download)
                    {
                    ?>
                    <a href="<?=$link ?>" >
                    	<!--<tr bordercolor='#000000' class="td" onmouseover="sobre(this,'#ffffff')" onmouseout="bajo(this,'transparent')" <?=(($datos->fields['ganada']=='t')? "title='Adjudicada a: ".$datos->fields['nombre']."'":'') ?> > -->
                        <tr class="td" <?echo atrib_tr()?> <?=(($datos->fields['ganada']=='t')? "title='Adjudicada a: ".$datos->fields['nombre']."'":'') ?> >
                	 <?
                	 if($pagina=="cargar_resultados")
                	 {?>
                	     <td colspan="2" >
                		 <input type="radio" name="radio_oferta" value="<?=$datos->fields['id']?>">
                	 <?}
                	 else
                	 {?>
                	  <td colspan="2" >
                	 <?}?>
                	    <?=(($datos->fields['ganada']=='t')?'<font color="#CC0033">':'').(($datos->fields['nombre'])?$datos->fields['nombre']:'&nbsp;').(($datos->fields['ganada']=='t')?'</font>':'')?>
                      </td>
                      <td colspan="1" align="right">
                        <?=$datos->fields['simbolo'].'&nbsp;'.(($datos->fields['monto_unitario'])?formato_money($datos->fields['monto_unitario']):'')?>
                      </td>
                      <td align="right">
                        <?=$datos->fields['simbolo'].'&nbsp;'.(($datos->fields['monto_unitario'])?formato_money($datos->fields['cantidad']*$datos->fields['monto_unitario']):'')?>
                      </td>
                      <td width="5%" align="center">
                		<?=($datos->fields['observaciones'])?$datos->fields['observaciones']:'&nbsp'?>
                      </td>
                      <td>
                         <?=obtener_diferencias($montos_coradir[$datos->fields['id_renglon']], $datos->fields['monto_unitario'])?>
                      </td>
                    </tr>
                </a>
                <?
                }
                else //crea la filas en excel
                {
                ?>
                	<tr bordercolor='#000000' class="td"  >
                	  <td colspan="2" <?=excel_style('texto') ?>>
                	    <?=(($datos->fields['ganada']=='t')?'<font color="#CC0033">':'').(($datos->fields['nombre'])?$datos->fields['nombre']:'&nbsp;').(($datos->fields['ganada']=='t')?'</font>':'')?>
                      </td>
                      <td colspan="1" align="right" <?= excel_style($datos->fields['simbolo']) ?>>
                        <?=(($datos->fields['monto_unitario'])?formato_money($datos->fields['monto_unitario']):"0,00")?>
                      </td>
                      <td align="right" <?= excel_style($datos->fields['simbolo']) ?>>
                        <?=(($datos->fields['monto_unitario'])?formato_money($datos->fields['cantidad']*$datos->fields['monto_unitario']):"0,00")?>
                      </td>
                      <td width="5%" align="center" <?= excel_style('texto') ?>>
                		<?=($datos->fields['observaciones'])?$datos->fields['observaciones']:'&nbsp'?>
                      </td>
                      <td <?= excel_style('numero') ?>>
                         <?=obtener_diferencias($montos_coradir[$datos->fields['id_renglon']], $datos->fields['monto_unitario'])?>
                      </td>
                      
                    </tr>
                <?
                }
                $datos->MoveNext();
		 	 }
	  if (!$datos->EOF && $id_renglon_actual==$datos->fields['id_renglon'])
	    $datos->MoveNext();
	  }
?>
  </table>
  <?
   if($pagina=="cargar_resultados")
   {
   	$parametros['id_lic']=$keyword;
   	$parametros['id_renglon']="";
	$parametros['id_competidor']="";
	$parametros['pagina']=$pag_ant;
	$parametros['pagina_volver']=$pagina_volver;
	$link=encode_link("lic_cargar_res.php",$parametros);
  	?>
  </div>
  <br>
  <center>
    <input type="submit" name="boton" value="Eliminar">
    <input type="button" name="boton" value="Volver" onclick="location.href='<?=$link?>'">
  </center>
 <?}?>
  
<? if (!$datos->EOF && $id_lic_actual==$datos->fields['id_licitacion'])
   	$datos->MoveNext();
	}
}
elseif ($datos && $datos->RecordCount() <=0)
{
   //este require_once lo puse para que vuelva a la pagina
   require_once("lic_buscar_res.php");
	echo "<div align='center'>";
   echo "<b>";
	echo "NO SE ENCONTRARON RESULTADOS PARA MOSTRAR";
   echo "</div>";
}
//esta tabla es para que no extienda el color de las filas
if ($download) {
?>
<table>
	<tr><td>&nbsp;</td></tr>
</table>
<? }?>

</form>
</body>
</html>
<?
echo fin_pagina();
?>