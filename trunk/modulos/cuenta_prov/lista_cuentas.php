<? 
require_once("../../config.php");
echo $html_header;

//obtengo valor $filtro ->letra seleccionada en el filtro, es 'a' si es la primera vez que carga, es 'vacio' si selecciona 'Todos'
if($letra!="")
 $filtro=$letra;
elseif($_POST['filtro']=="Todos" ) 
 $filtro="";
elseif($_POST['filtro']=="") {
 if ($_POST['filter']=='Todos')  $filtro="";	
 elseif ($_POST['filter']!="") $filtro=$_POST['filter'];
 else $filtro="a";
}
else 
 $filtro=$_POST['filtro'];

 //recupero filtro desde parametros al recargar la pagina cuando se presiona 
 //en los titulos de cada fila para ordenar el resultado por cada campo
if ($parametros)
$filtro=$parametros['filtro'];

//el orden para ordenar la consulta
if ($parametros['dir'] == 'ASC') $direccion='DESC';
else $direccion='ASC';

//muestra tabla para filtar proveedores
function tabla_filtros_nombres(){
 $abc=array("a","b","c","d","e","f","g","h","i",
            "j","k","l","m","n","ñ","o","p","q",
            "r","s","t","u","v","w","x","y","z");

$cantidad=count($abc);

echo "<table id='mo'>";
echo "<input type=hidden name='filtro' value=''";
    echo "<tr>";
    for($i=0;$i<$cantidad;$i++){
        $letra=$abc[$i];
       switch ($i) {
                    // case 9:
                    // case 18:
                     case 27:echo "</tr><tr>";
                          break;
                   default:
                  } //del switch

echo "<td style='cursor:hand' onclick=\"document.all.filtro.value='$letra';document.all.select_proveedor.value=0; document.form1.submit();\">$letra</td>";
      }//del for
   echo "</tr>";
   echo "<tr>";
    echo "<td colspan='27' align='center' style='cursor:hand' onclick=\"document.all.filtro.value='Todos';document.all.select_proveedor.value=0; document.form1.submit();\"> Todos";
    echo "</td>";
   echo "</tr>";
   echo "</table>";
}  //de la funcion


$query_prov="SELECT id_proveedor, razon_social FROM proveedor where activo='true' and razon_social ilike '$filtro%' order by razon_social";
$prov=sql($query_prov) or fin_pagina();

?>
<script language="javascript">
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;
}
function bajo(src,color_default) {
    src.style.backgroundColor=color_default;
}
</script>

<script src="<?=$html_root."/lib/funciones.js"?>" ></script>
<form method="post" name="form1" action="lista_cuentas.php">
<input type='hidden' name='filter' value='<?if ($filtro) echo $filtro;else echo 'Todos'?>'>
 <table width="95%" id="ma" align="center"><tr><td>
    PROVEEDORES
    </td></tr>
   </table>
<table  align="center">
<tr><td colspan="2"><br></td></tr>
<tr><td><? tabla_filtros_nombres();?></td>
    <td>&nbsp;</td></tr>
<tr>
<td>  
 <select name="select_proveedor" size='8' onKeypress= "buscar_op(this);" onblur="borrar_buffer()" onclick= "borrar_buffer()" > 
            <option value=0>Seleccione un proveedor </option> 
            <?
            while (!$prov->EOF)
            { ?>
            <option value="<?=$prov->fields['id_proveedor']?>"<?if ($prov->fields['id_proveedor']==$_POST['select_proveedor'] || $prov->fields['id_proveedor']==$parametros['prov'] ){echo 'selected'; $nbre_prov=$prov->fields['razon_social'];}?>><?=$prov->fields['razon_social']?>
            </option>
            <? 
            $prov->MoveNext();
            }?>
           </select>
</td>
<td valign="bottom"> <input type="submit" name="ver_datos" value="Ver Datos"></td>

<? if ($_POST['select_proveedor']!=0 || isset($parametros['col']))  {
$id_prov=$_POST['select_proveedor'] or $id_prov=$parametros['prov'];
?>
<td valign="bottom">
    &nbsp;&nbsp;
    <? $id_prov=$_POST['select_proveedor'] or $id_prov=$parametros['prov'];
    $link=encode_link("cuentas_excel2.php",array("id_proveedor"=>$id_prov)); ?>
    <a target=_blank title='Bajar datos en un excel' href='<?=$link?>'>
    <img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' >
    </a>
 </td>
</tr>
</table>
<?
//selecciona notas de credito.ESTADOS: pendientes(estado=0)  reservadas (estado=1)
//  utilizadas (estado=2)

$query_nc="select nota_credito.id_nota_credito,nota_credito.estado,res.fecha, nota_credito.id_moneda, moneda.nombre, moneda.simbolo, monto from  general.nota_credito join licitaciones.moneda using (id_moneda) 
join general.proveedor using (id_proveedor) left join 
(select * from general.log_nota_credito where tipo='creación') as res
using (id_nota_credito) where id_proveedor=$id_prov";

switch($parametros['col']){
			  case "0": $query_nc.=" order by nota_credito.id_nota_credito";break;
			  case "1": $query_nc.=" order by nota_credito.estado";break;
			  case "2": $query_nc.=" order by res.fecha";break;
			  case "3": $query_nc.=" order by monto";break;
			  default:$query_nc.=" order by nota_credito.id_nota_credito";break;
			}
$query_nc.= " ".$direccion;	

$nc=sql($query_nc) or fin_pagina();
$filas_nc=$nc->RecordCount();

$query_oc="select * from 
(select sum(cantidad*precio_unitario) as monto,orden.nro_orden,orden.id_moneda,orden.estado,orden.simbolo,orden.fecha_entrega,orden.valor_dolar 
from compras.fila join 
(select nro_orden,id_moneda,simbolo,estado,fecha_entrega,valor_dolar from compras.orden_de_compra join licitaciones.moneda using (id_moneda)
 join general.proveedor using (id_proveedor) where id_proveedor=$id_prov and estado <> 'n' ) as orden using (nro_orden)
group by orden.nro_orden,orden.id_moneda,orden.estado,orden.fecha_entrega,orden.valor_dolar,orden.simbolo order by orden.estado)  as res1
left join 
(select sum (monto) as falta_pago,nro_orden  from compras.orden_de_compra 
join compras.pago_orden using (nro_orden)
join compras.ordenes_pagos using (id_pago)
where id_proveedor=$id_prov and estado ='d'
and idbanco isnull and iddébito isnull and id_ingreso_egreso isnull
group by nro_orden )as res2 
using (nro_orden)";

switch($parametros['col']){
			  case "0": $query_oc.=" order by nro_orden";break;
			  case "1": $query_oc.=" order by estado";break;
			  case "2": $query_oc.=" order by fecha_entrega";break;
			  case "3": $query_oc.=" order by monto";break;
			  case "4": $query_oc.=" order by valor_dolar";break;
			  default : $query_oc.=" order by nro_orden";break;
			}

$query_oc.= " ".$direccion;

$oc=sql($query_oc) or fin_pagina();
$filas_oc=$oc->RecordCount();
//calcula el haber para el proveedor (lo que se le debe )
$suma_p=0;
$suma_d=0;
while (!$oc->EOF) { 
 if ($oc->fields['estado'] !='g' and $oc->fields['estado'] != 'd'){
   if ($oc->fields['id_moneda']==1) $suma_p+=$oc->fields['monto'];
   else 
   	   $suma_d+=$oc->fields['monto'];
  }
  elseif ($oc->fields['estado'] == 'd') {
  	if ($oc->fields['id_moneda']==1)  $suma_p+=$oc->fields['falta_pago'];
     else 
  		 $suma_d+=$oc->fields['falta_pago'];
  }
  
 
  $oc->MoveNext();
}
//calcula el debe del proveedor (lo que el proveedor debe)
$debe_p=0;
$debe_d=0;
while (!$nc->EOF) { 
 if ($nc->fields['estado'] !=2 && $nc->fields['estado'] !=3) {  //la nota no esta utilizada o no esta anulada
   if ($nc->fields['id_moneda']==1) $debe_p+=$nc->fields['monto'];
   else $debe_d+=$nc->fields['monto'];
  }
  $nc->MoveNext();
}

?>
<br>

<table width="95%" id="mo" align="center"><tr><td>
CUENTAS DEL PROVEEDOR <?=strtoupper($nbre_prov)?>
</td></tr>
</table>
<table align="center" id="ma" border=1 cellspacing="3" width="95%">
<tr>
<td>HABER (del proveedor)</td>
<td><?='$ '.formato_money($suma_p) ?></td>
<td><?='U$S '.formato_money($suma_d) ?></td>
</tr>
<tr>
<td>DEBE (del proveedor) </td>
<td><?= '$ '.formato_money($debe_p)?></td>
<td><?= 'U$S '.formato_money($debe_d)?></td>

</tr>
<tr>
<td>SALDO <FONT color="red">(*)</font></td>
<td><?='$ '.formato_money($suma_p - $debe_p)?></td>
<td><?='U$S '.formato_money($suma_d - $debe_d)?></td>
</tr>
</table>
<table width="95%" align="center"><tr><td>
   <FONT color="red"><b>(*)</b></font> Saldo positivo indica el monto que se debe al proveedor
</td></tr>
</table>

<br>
<? if ($filas_oc > 0 ) {?>

<table width="95%" id="ma" align="center"><tr><td>
HISTORIAL DE ORDENES DE COMPRA PARA EL PROVEEDOR <?=strtoupper($nbre_prov)?></td>
<td align="right">Total:<?=$filas_oc?></td>
</tr></table>

<table align="center" width="95%">
<tr id="ma">
<td id="mo"> <a style="background:inherit" href=<?=encode_link('lista_cuentas.php',array('col'=>0,'prov'=>$id_prov,'filtro'=>$filtro,'dir'=>$direccion))?>><font size="2" family="helvetica, sans-serif" color="#c0c6c9" >NRO_ORDEN</font></a> </td>
<td id="mo"> <a style="background:inherit" href=<?=encode_link('lista_cuentas.php',array('col'=>1,'prov'=>$id_prov,'filtro'=>$filtro,'dir'=>$direccion))?>><font size="2" family="helvetica, sans-serif" color="#c0c6c9" >ESTADO</font></a> </td>
<td id="mo"> <a style="background:inherit" href=<?=encode_link('lista_cuentas.php',array('col'=>2,'prov'=>$id_prov,'filtro'=>$filtro,'dir'=>$direccion))?>><font size="2" family="helvetica, sans-serif" color="#c0c6c9" >FECHA ENTREGA</font></a> </td>
<td id="mo"> <a style="background:inherit" href=<?=encode_link('lista_cuentas.php',array('col'=>3,'prov'=>$id_prov,'filtro'=>$filtro,'dir'=>$direccion))?>><font size="2" family="helvetica, sans-serif" color="#c0c6c9" >MONTO</font></a> </td>
<td id="mo"> <a style="background:inherit" href=<?=encode_link('lista_cuentas.php',array('col'=>4,'prov'=>$id_prov,'filtro'=>$filtro,'dir'=>$direccion))?>><font size="2" family="helvetica, sans-serif" color="#c0c6c9" >VALOR DOLAR</font></a> </td>
</tr>

<? $oc->MoveFirst();
while (!$oc->EOF) { 
	if ($cnr==1)
  {$color1=$bgcolor1;
   $color =$bgcolor2;
   $cnr=0;
  }
else
  {$color1=$bgcolor2;
   $color =$bgcolor1;
   $cnr=1;
  }	?>

<tr bgcolor="<? echo $color1?>" onMouseOver="sobre(this,'#FFFFFF')" onMouseOut="bajo(this,'<? echo $color1?>');">
<? $link = encode_link('../ord_compra/ord_compra.php', array('nro_orden' => $oc->fields['nro_orden']));
   if ($oc->fields['id_moneda'] == 2) $mon='Dólares';
   else $mon="";
   $link1=encode_link("../ord_compra/ord_compra_resumen_pagos.php",array("nro_orden"=>$oc->fields['nro_orden'],"moneda"=>$mon,"pagina"=>"ord_compra"));?>
<a onClick="window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=50,top=30,width=700,height=450')">
<td title="ver orden de compra" style="cursor:hand"><?=$oc->fields['nro_orden']?></td> </a>
<? if ($oc->fields['estado']=='g' || $oc->fields['estado']=='d' ) {?><a onclick="window.open('<?=$link1;?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=0,width=800,height=500')"> <? }?>
<td align="center" <? if ($oc->fields['estado']=='g'  || $oc->fields['estado']=='d') { ?> style="cursor:hand" title="ver resumen de pagos"<? }?>><? switch ($oc->fields['estado']) {
    case a: echo 'AUTORIZADA';break; 
    case g: echo 'PAGADA';break;
    case e: echo 'ENVIADA';break;
    case n: echo 'ANULADA';break;
    case p: echo 'PENDIENTE'; break;
    case d: echo 'PARCIALMENTE PAGADA'; break;
    case u: echo 'POR AUTORIZAR'; break;
    }?></td> </a>
<td align="center"><?=fecha($oc->fields['fecha_entrega'])?>    
<td align="center"><?=$oc->fields['simbolo']." ".formato_money($oc->fields['monto'])?></td>
<td align="center"><?=formato_money($oc->fields['valor_dolar'])?></td>

</tr>
<? $oc->MoveNext();
}
?>
</table>

<? }
else {?>
   <table width="95%" id="ma" align="center"><tr><td>
    NO HAY ORDENES DE COMPRAS REGISTRADAS PARA EL PROVEEDOR <?=strtoupper($nbre_prov)?>
    </td></tr>
   </table>
<?} ?>
<br>

<? if ($filas_nc > 0 ) {?>
<table width="95%" id="ma" align="center"><tr>
<td>HISTORIAL NOTAS DE CREDITO PARA EL PROVEEDOR <?=strtoupper($nbre_prov)?></td> 
<td align="right">Total: <?=$filas_nc ?></td>
</tr></table>
<table align="center" width="95%">
<tr id="ma">
<td id="mo"> <a style="background:inherit" href=<?=encode_link('lista_cuentas.php',array('col'=>0,'prov'=>$id_prov,'filtro'=>$filtro,'dir'=>$direccion))?>><font size="2" family="helvetica, sans-serif" color="#c0c6c9" >ID</font></a> </td>
<td id="mo"> <a style="background:inherit" href=<?=encode_link('lista_cuentas.php',array('col'=>1,'prov'=>$id_prov,'filtro'=>$filtro,'dir'=>$direccion))?>><font size="2" family="helvetica, sans-serif" color="#c0c6c9" >ESTADO </font></a> </td>
<td id="mo"> <a style="background:inherit" href=<?=encode_link('lista_cuentas.php',array('col'=>2,'prov'=>$id_prov,'filtro'=>$filtro,'dir'=>$direccion))?>><font size="2" family="helvetica, sans-serif" color="#c0c6c9" >FECHA CREACION </font></a></td>
<td id="mo"> <a style="background:inherit" href=<?=encode_link('lista_cuentas.php',array('col'=>3,'prov'=>$id_prov,'filtro'=>$filtro,'dir'=>$direccion))?>><font size="2" family="helvetica, sans-serif" color="#c0c6c9" >MONTO </font></a></td>
</tr>

<? $nc->MoveFirst();
  while (!$nc->EOF) { 
  if ($cnr==1)
  {$color1=$bgcolor1;
   $color =$bgcolor2;
   $cnr=0;
  }
else
  {$color1=$bgcolor2;
   $color =$bgcolor1;
   $cnr=1;
  }	

  if ($nc->fields['estado']==3) $color_anulado="bgcolor=#FF6A6C";
  else $color_anulado="";
  ?>
<tr bgcolor="<? echo $color1?>" onMouseOver="sobre(this,'#FFFFFF')" onMouseOut="bajo(this,'<? echo $color1?>');">
<? //$link para mostrar nota de credito paso como parametros en pagina nota_credito_listar por que el el parametro
//que recibe el archivo cuando se invoca desde el listado 
$link = encode_link('../ord_compra/nota_credito.php', array('pagina'=>'nota_credito_listar','id_nota_credito' => $nc->fields['id_nota_credito']));?>
<a onClick="window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=50,top=30,width=700,height=450')">
<td title="ver nota de credito" style="cursor:hand" <?=$color_anulado?>> <?=$nc->fields['id_nota_credito'] ?></td></a>
<td align="center"><? switch ($nc->fields['estado']) {
    case 0: echo 'PENDIENTE';break; 
    case 1: echo 'RESERVADA';break;
    case 2: echo 'UTILIZADA';break;
    case 3: echo 'ANULADA';break;
    }?></td>
<td align="center"><?=fecha($nc->fields['fecha'])?></td>    
<td align="center"><?=$nc->fields['simbolo']." ".formato_money($nc->fields['monto'])?></td>

</tr>
<? $nc->MoveNext();
}
?>
</table>

<? }
else {?>
	<table width="95%" id="ma" align="center"><tr><td>
    NO HAY NOTAS DE CREDITO REGISTRADAS PARA EL PROVEEDOR <?=strtoupper($nbre_prov)?>
    </td></tr>
   </table>
  <? } ?>
<? }


?>
</form>


