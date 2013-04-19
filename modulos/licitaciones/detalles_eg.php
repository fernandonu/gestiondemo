<?
/*
$Author: mari $
$Revision: 1.2 $
$Date: 2006/03/23 20:13:49 $
*/

/*Muestra los detalles para egresos (iva, multas, ganancias......)
la pagina que lo incluye tiene que tener definido $link_cheques para invocar pagina de cheques diferidos
y el arreglo $valores_defecto con los valores por defecto de tipoegreso, proveedor,cuento y plan
  para cada detalle (iva, ganancias....)*/

//query para generar los select
 $query="SELECT * FROM caja.tipo_egreso order by nombre";
 $res_egreso=sql($query) or fin_pagina();
 $query="SELECT razon_social,id_proveedor FROM general.proveedor order by razon_social";
 $res_prov=sql($query) or fin_pagina();
 $query="select * from general.tipo_cuenta order by concepto,plan";
 $res_concepto=sql($query) or fin_pagina();
 $query="SELECT idbanco,nombrebanco FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
 $res_banco=sql($query) or fin_pagina();
 $query="SELECT * FROM bancos.tipo_depósito";
 $res_deposito=sql($query) or fin_pagina();

 
  
if (!$error) {
 	
 if ($id_cobranza !="") $id=$id_cobranza;
     else $id=$id_cob;  //id_primaria
  
 // valores  predeterminados
 
 $id_iva=$valores_defecto['iva']['tipo'];
 $id_gan=$valores_defecto['ganancias']['tipo'];
 $id_rib=$valores_defecto['rib']['tipo'];
 $id_suss=$valores_defecto['suss']['tipo'];  
 $id_mul=$valores_defecto['Multas']['tipo'];
 $id_dep=$valores_defecto['Transferencia']['tipo'];
 $id_banco=$valores_defecto['Transferencia']['banco'];
 $id_deposito=$valores_defecto['Transferencia']['deposito'];
 $id_otro=$valores_defecto['Otros']['tipo'];
 $id_diferido=$valores_defecto['Cheque Diferido']['tipo']; 
 $id_devp=$valores_defecto['Devolucion Prestamo']['tipo']; 
 $id_int=$valores_defecto['Intereses']['tipo'];     
 $id_adm=$valores_defecto['Gastos Administrativos']['tipo']; 
 $id_com=$valores_defecto['Comisiones']['tipo'];      
 $prov_iva=$valores_defecto['iva']['prov'];
 $prov_gan=$valores_defecto['ganancias']['prov'];
 $prov_rib=$valores_defecto['rib']['prov'];
 $prov_suss=$valores_defecto['suss']['prov'];
 $prov_mul=$valores_defecto['Multas']['prov'];
 $prov_dep=$valores_defecto['Transferencia']['prov'];
 $prov_otro=$valores_defecto['Otros']['prov'];
 $prov_diferido=$valores_defecto['Cheque Diferido']['prov'];
 $prov_devp=$valores_defecto['Devolucion Prestamo']['prov']; 
 $prov_int=$valores_defecto['Intereses']['prov'];     
 $prov_adm=$valores_defecto['Gastos Administrativos']['prov']; 
 $prov_com=$valores_defecto['Comisiones']['prov'];   
 $cto_iva=$valores_defecto['iva']['cta'];
 $cto_gan=$valores_defecto['ganancias']['cta'];
 $cto_rib=$valores_defecto['rib']['cta'];
 $cto_suss=$valores_defecto['suss']['cta'];
 $cto_mul=$valores_defecto['Multas']['cta'];
 $cto_dep=$valores_defecto['Transferencia']['cta'];
 $cto_otro=$valores_defecto['Otros']['cta'];
 $cto_diferido=$valores_defecto['Cheque Diferido']['cta']; 
 $cto_devp=$valores_defecto['Devolucion Prestamo']['cta']; 
 $cto_int=$valores_defecto['Intereses']['cta'];     
 $cto_adm=$valores_defecto['Gastos Administrativos']['cta']; 
 $cto_com=$valores_defecto['Comisiones']['cta'];   
   
} else {  // si hubo error recupera datos del post
 $text_iva=$_POST['iva'];
 $text_gan=$_POST['ganancia'];
 $text_rib=$_POST['rib'];
 $text_suss=$_POST['suss'];
 $text_mul=$_POST['multas'];
 $text_dep=$_POST['deposito'];
 $text_otro=$_POST['otro'];
 $text_devp=$_POST['devolucion'];
 $text_int=$_POST['interes'];
 $text_com=$_POST['comisiones'];
 $text_adm=$_POST['gastoadm'];
 $id_iva=$_POST['tipo_iva'];
 $id_gan=$_POST['tipo_gan'];
 $id_mul=$_POST['tipo_multas'];
 $id_rib=$_POST['tipo_rib'];
 $id_suss=$_POST['tipo_suss'];
 $id_dep=$_POST['tipo_dep'];
 $id_otro=$_POST['tipo_otro'];
 $id_devp=$_POST['tipo_devolucion'];
 $id_int=$_POST['tipo_interes'];
 $id_com=$_POST['tipo_comisiones'];
 $id_adm=$_POST['tipo_gastoadm'];
 $prov_iva=$_POST['prov_iva'];
 $prov_gan=$_POST['prov_gan'];
 $prov_rib=$_POST['prov_rib'];
 $prov_suss=$_POST['prov_suss'];
 $prov_mul=$_POST['prov_multas'];
 $prov_dep=$_POST['prov_dep'];
 $prov_otro=$_POST['prov_otro'];
 $prov_devp=$_POST['prov_devolucion'];
 $prov_int=$_POST['prov_interes'];
 $prov_com=$_POST['prov_comisiones'];
 $prov_adm=$_POST['prov_gastoadm'];
 $cto_iva=$_POST['concepto_iva'];
 $cto_gan=$_POST['concepto_gan'];
 $cto_rib=$_POST['concepto_rib'];
 $cto_suss=$_POST['concepto_suss'];
 $cto_mul=$_POST['concepto_multas'];
 $cto_dep=$_POST['concepto_dep'];
 $cto_otro=$_POST['concepto_otro'];
 $cto_devp=$_POST['concepto_devolucion'];
 $cto_int=$_POST['concepto_interes'];
 $cto_com=$_POST['concepto_comisiones'];
 $cto_adm=$_POST['concepto_gastoadm'];
 $id_banco=$_POST['banco'];
 $id_deposito=$_POST['tipo_deposito'];
 $text_ficticio=$_POST['text_ficticio'];
 $text_cheque=$_POST['text_cheque'];
 $text_diferido=$_POST['text_diferido'];
 } 

if ($c_dep==1 || $_POST['chk_dep']==1) $det_visib = "";
  else $det_visib = "none";

?>
<table width="45%" align="center" border="1" cellspacing="2"  bordercolor="#000000">
<tr id="mo"><td colspan="6" align="center">DETALLES DEL EGRESO</td></tr>
<tr id=ma >
<td>&nbsp;</td>
<td>Descripción</td>
<td>Monto</td>
<td>Tipo Egreso</td>
<td>Proveedor</td>
<td>Cuenta y Plan</td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_iva" value=1 onclick="if (!this.checked) document.all.iva.value=''" <?if ( $c_iva==1 || $_POST['chk_iva']==1) echo 'checked' ?>> </td>
  <td align="right">IVA:</td>
  <td><input type="text" name='iva' value='<?=$text_iva?>' size="15" ></td>
  <td> <?=gen_select('tipo_iva',$res_egreso,$id_iva,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_iva',$res_prov,$prov_iva,'id_proveedor','razon_social');?></td>
  <td> <?=gen_select_concepto('concepto_iva',$res_concepto,$cto_iva);?></td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_gan" value=1 onclick="if (!this.checked) document.all.ganancia.value=''"  <?if ($c_gan==1 || $_POST['chk_gan']==1) echo 'checked' ?>> </td>
  <td align="right">GANANCIAS:</td>
  <td><input type="text" name='ganancia' value='<?=$text_gan?>' size="15"></td>
  <td><?=gen_select('tipo_gan',$res_egreso,$id_gan,'id_tipo_egreso','nombre');?></td>
  <td><?=gen_select('prov_gan',$res_prov,$prov_gan,'id_proveedor','razon_social');?> </td>
  <td><?=gen_select_concepto('concepto_gan',$res_concepto,$cto_gan);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_rib" value=1 onclick="if (!this.checked) document.all.rib.value=''" <?if ($c_rib==1 || $_POST['chk_rib']==1) echo 'checked' ?>> </td>
  <td  align="right">RIB:</td>
  <td><input type="text" name='rib' value='<?=$text_rib?>' size="15"></td>
  <td> <?=gen_select('tipo_rib',$res_egreso,$id_rib,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_rib',$res_prov,$prov_rib,'id_proveedor','razon_social');?></td>
  <td> <?=gen_select_concepto('concepto_rib',$res_concepto,$cto_rib);?></td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_suss" value=1 onclick="if (!this.checked) document.all.suss.value=''" <?if ($c_suss==1 || $_POST['chk_suss']==1) echo 'checked' ?>> </td>
  <td  align="right">SUSS:</td>
  <td> <input type="text" name='suss' value='<?=$text_suss?>' size="15"></td>
  <td> <?=gen_select('tipo_suss',$res_egreso,$id_suss,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_suss',$res_prov,$prov_suss,'id_proveedor','razon_social');?></td>
  <td> <?=gen_select_concepto('concepto_suss',$res_concepto,$cto_suss);?></td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_mul" value=1 onclick="if (!this.checked) document.all.multas.value=''" <?if ($c_mul==1 || $_POST['chk_mul']==1) echo 'checked' ?> > </td>
  <td  align="right">MULTAS:</td>
  <td><input type="text" name='multas' value='<?=$text_mul?>' size="15" ></td>
  <td> <?=gen_select('tipo_multas',$res_egreso,$id_mul,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_multas',$res_prov,$prov_mul,'id_proveedor','razon_social');?> </td>
  <td> <?=gen_select_concepto('concepto_multas',$res_concepto,$cto_mul);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_dep" value=1 
        onclick="if (!this.checked) { document.all.deposito.value=''; Ocultar('tabla_dep');}else Mostrar('tabla_dep'); " <?if ($c_dep==1 || $_POST['chk_dep']==1) echo 'checked' ?> > </td>
  <td  align="right">TRANSFERENCIA:</td>
  <td><input type="text" name='deposito' value='<?=$text_dep?>' size="15" ></td>
  <td><?=gen_select('tipo_dep',$res_egreso,$id_dep,'id_tipo_egreso','nombre');?></td>
  <td><?=gen_select('prov_dep',$res_prov,$prov_dep,'id_proveedor','razon_social');?></td>
  <td><?=gen_select_concepto('concepto_dep',$res_concepto,$cto_dep);?></td>
</tr>

<tr id=ma><td colspan="6" align="left">
 <div id='tabla_dep' style='display:<?=$det_visib?>'>
   <table>
   <tr id=ma>
    <td width="20%"></td>
    <td> Banco: <?=gen_select('banco',$res_banco,$id_banco,'idbanco','nombrebanco');?> </td>
    <td>  Tipo de Depósito:<?=gen_select('tipo_deposito',$res_deposito,$id_deposito,'idtipodep','tipodepósito');?> </td>
   </tr>
   </table>
   </div>
 </td>

</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_otro" value=1 onclick="if (!this.checked) document.all.otro.value=''" <?if ($c_otro==1 || $_POST['chk_otro']==1) echo 'checked' ?>> </td>
  <td  align="right">OTROS:</td>
  <td><input type="text" name='otro' value='<?=$text_otro?>' size="15" ></td>
  <td><?=gen_select('tipo_otro',$res_egreso,$id_otro,'id_tipo_egreso','nombre');?></td>
  <td><?=gen_select('prov_otro',$res_prov,$prov_otro,'id_proveedor','razon_social');?></td>
  <td><?=gen_select_concepto('concepto_otro',$res_concepto,$cto_otro);?></td>
</tr>

<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_diferido" value=1 onclick="if (!this.checked) {document.all.diferido_monto.value=document.all.diferido.value;document.all.diferido.value='';}else document.all.diferido.value=document.all.diferido_monto.value;" <?if ($c_diferido==1 || $_POST['chk_diferido']==1) echo 'checked' ?>> </td>
  <td align="right" onclick="ventana_cheques = window.open('<?=$link_cheques?>','','');" style="cursor:hand" title="Haga click aqui para ingresar cheques diferidos">CH. DIFERIDO:</td>
  <td><input type="text" name='diferido' value='<?=$text_diferido?>' readonly size="15">
  <input type="hidden" name='diferido_monto' value='<?=$text_diferido?>'>
  </td>
  <td><?=gen_select('tipo_diferido',$res_egreso,$id_diferido,'id_tipo_egreso','nombre');?></td>
  <td><?=gen_select('prov_diferido',$res_prov,$prov_diferido,'id_proveedor','razon_social');?></td>
  <td><?=gen_select_concepto('concepto_diferido',$res_concepto,$cto_diferido);?></td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_devp" value=1 onclick="if (!this.checked) document.all.devolucion.value=''" <?if ($c_devp==1 || $_POST['chk_devp']==1) echo 'checked' ?> > </td>
  <td  align="right">DEVOLUCION PRESTAMO:</td>
  <td><input type="text" name='devolucion' value='<?=$text_devp?>' size="15" ></td>
  <td> <?=gen_select('tipo_devolucion',$res_egreso,$id_devp,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_devolucion',$res_prov,$prov_devp,'id_proveedor','razon_social');?> </td>
  <td> <?=gen_select_concepto('concepto_devolucion',$res_concepto,$cto_devp);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_int" value=1 onclick="if (!this.checked) document.all.interes.value=''" <?if ($c_int==1 || $_POST['chk_int']==1) echo 'checked' ?> > </td>
  <td  align="right">INTERESES:</td>
  <td><input type="text" name='interes' value='<?=$text_int?>' size="15" ></td>
  <td> <?=gen_select('tipo_interes',$res_egreso,$id_int,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_interes',$res_prov,$prov_int,'id_proveedor','razon_social');?> </td>
  <td> <?=gen_select_concepto('concepto_interes',$res_concepto,$cto_int);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_adm" value=1 onclick="if (!this.checked) document.all.gastoadm.value=''" <?if ($c_adm==1 || $_POST['chk_adm']==1) echo 'checked' ?> > </td>
  <td  align="right">GTOS. ADMINIST.:</td>
  <td><input type="text" name='gastoadm' value='<?=$text_adm?>' size="15" ></td>
  <td> <?=gen_select('tipo_gastoadm',$res_egreso,$id_adm,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_gastoadm',$res_prov,$prov_adm,'id_proveedor','razon_social');?> </td>
  <td> <?=gen_select_concepto('concepto_gastoadm',$res_concepto,$cto_adm);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_com" value=1 onclick="if (!this.checked) document.all.comisiones.value=''" <?if ($c_com==1 || $_POST['chk_com']==1) echo 'checked' ?> > </td>
  <td  align="right">COMISIONES:</td>
  <td><input type="text" name='comisiones' value='<?=$text_com?>' size="15" ></td>
  <td> <?=gen_select('tipo_comisiones',$res_egreso,$id_com,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_comisiones',$res_prov,$prov_com,'id_proveedor','razon_social');?> </td>
  <td> <?=gen_select_concepto('concepto_comisiones',$res_concepto,$cto_com);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_ficticio" value=1 onclick="if (!this.checked) document.all.ficticio.value=''" <?if ($c_ficticio==1 || $_POST['chk_ficticio']==1) echo 'checked' ?>> </td>
  <td  align="right">EFECTIVO:</td>
  <td><input type="text" name='ficticio' value='<?=$text_ficticio?>' size="15" ></td>
  <td colspan=3>&nbsp; </td>
</tr>

<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_cheque" value=1 onclick="if (!this.checked) document.all.cheque.value=''" <?if ($c_cheque==1 || $_POST['chk_cheque']==1) echo 'checked' ?>> </td>
  <td  align="right">CHEQUE:</td>
  <td><input type="text" name='cheque' value='<?=$text_cheque?>' size="15" ></td>
  <td colspan=3>&nbsp; </td>
</tr>
<tr id=ma>
  <td  align="right" colspan="2">TOTAL:</td>
   <td colspan="4"><input type="text" name='total' value='<?=$_POST['total']?>' size="15"  onFocus="if (control_num()) calcular_total(0)" title="Haga click para ver el total"></td>
 </tr>
</table>



