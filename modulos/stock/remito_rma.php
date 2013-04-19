<?
/*
MODIFICADA POR
$Author: mari $
$Revision: 1.5 $
$Date: 2007/01/04 15:36:02 $
*/
require_once("../../config.php");
$id_info_rma=$_POST['id_info_rma'] or $id_info_rma=$parametros['id_info_rma'];
echo $html_header;
cargar_calendario();

if ($_POST['guardar']=="Guardar")
{      $db->StartTrans(); 
       $id_info_rma=$_POST['id_info_rma'];
	   $fecha_remito=Fecha_db($_POST['fecha_remito'])." "."00:00:00";
	   $cant=$_POST['cant'];
	   $defecto=$_POST['defecto'];
	   $nbre="CORADIR";
	   echo"$nbre nom <br>";
	   $dir="Patagones 2538 - Parque Patricios - Cap. Fed. - CP 1282ACD";
	   $direccion=$_POST['direccion'];
	   $prov=$_POST['prov'];
	   $id_p=$_POST['id_p'];
	   $entrega=$_POST['entrega'];
	   //recupero el id que se le asignara a el remito
		$q="select nextval('remito_interno_id_remito_seq') as id_remito";
		$id_remito=$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);
		$id_remito=$id_remito->fields['id_remito'];
		$campos="id_remito,cliente,direccion,entrega";
		$fecha=date("Y/m/j H:i:s");
		$valores="'$nbre','$dir','$entrega','$fecha_remito','p'";
		$campos.=",fecha_remito";
		$campos.=",estado";
		$valores="$id_remito,$valores";
		$q="insert into remito_interno ($campos) values ($valores)";
		if ($db->Execute($q))
		{
		 $q="insert into log_remito_interno (id_remito,tipo_log,usuario,fecha) values ($id_remito,'creacion','".$_ses_user['name']."','$fecha')";
		 $db->Execute($q) or die($db->ErrorMsg()."<br>".$q);
		 $campos="cant_prod,descripcion,id_remito,id_producto";
		 $valores="$cant,'$defecto',$id_remito,NULL";
		 $q="insert into items_remito_interno ($campos) values ($valores)";
         $db->Execute($q)or die($db->ErrorMsg()."<br>".$q);
         $campos="nro_remito,id_info_rma";
		 $valores="$id_remito,$id_info_rma";
		 $q="insert into remito_rma ($campos) values ($valores)";
         $db->Execute($q)or die($db->ErrorMsg()."<br>".$q);
		}
		$db->CompleteTrans();
        ?>
        <script>
        window.close();
        </script>
        <?

}

$sql="select info_rma.cantidad, en_stock.id_prod_esp,estado_rma.nombre_corto,estado_rma.lugar,
          en_stock.id_deposito,en_stock.ubicacion,
          producto_especifico.descripcion,producto_especifico.marca,producto_especifico.modelo,
          producto_especifico.precio_stock,info_rma.id_info_rma,info_rma.id_proveedor,info_rma.nro_orden
          ,proveedor.razon_social,proveedor.id_proveedor,producto_especifico.id_tipo_prod,tipos_prod.codigo,info_rma.void,info_rma.desc_void,info_rma.nrocaso,info_rma.defecto_parte
    from stock.en_stock
    join general.producto_especifico using(id_prod_esp)
    join general.depositos using (id_deposito)
    join stock.info_rma using (id_en_stock)
    join general.tipos_prod using(id_tipo_prod)
    left join general.proveedor using (id_proveedor)
    left join stock.estado_rma using (id_estado_rma)
where id_info_rma=$id_info_rma";
$res=sql($sql) or fin_pagina();

$nom=$res->fields['razon_social'];
$descripcion=$res->fields['descripcion'];
$marca=$res->fields['marca'];
$modelo=$res->fields['modelo'];
$cantidad=$res->fields['cantidad'];
$defecto=$res->fields['defecto_parte'];
$id_prov=$res->fields['id_proveedor'];
$id_prod_e=$res->fields['id_prod_esp'];
$con="select direccion from contactos where id_proveedor=$id_prov";
$eje_con=sql($con,"No se pudo recuperar la direccion del contacto") or fin_pagina();
if (!$fecha_remito)
	$fecha_remito=date("d/m/Y");
$cora="CORADIR";
$total=$eje_con->RecordCount();
$tot=1;
$valor="$nom \nDireccion:\n";
while($tot<$total){
$valor1=$eje_con->fields['direccion'];
$valor.="$valor1 \n";
$tot++;
}
?>
<form action="remito_rma.php" method="POST">
<table width="100%" border="0" cellspacing="1" cellpadding="1" align="center">
<input type="hidden" name="id_p" value="<?=$id_prod_e?>">
<input type="hidden" name="id_info_rma" value="<?=$id_info_rma?>">
<tr>
    <td height="29" colspan="2" align="center" valign="top">
        <font size='4' color=<?=$text_color_over?>><strong>Remito Interno</font>
        </td>
    </tr>
 <tr>
      <td width="54%" align="center" valign="top" nowrap>
        <table width="90%" border="1" cellpadding="0" cellspacing="0" bgcolor=<?=$bgcolor_out?>>
          <tr align="center" id="mo">
            <td height="20" colspan="2"> <strong>Cliente </strong> </td>
          </tr>
          <tr>
                <td><table>
                        <tr>
                                <td width="20%"><strong>Nombre</strong></td>
                                <td align="rigth"><input name="nbre" type="text" value="<?=$cora?>" size="47" disabled></td>
                        </tr>
                        </table></td>
          </tr>
                <td><table>
                        <tr align="left">
                                <td  width="20%"><strong>Dirección</strong></td>
                                <td align="rigth"><input name="dir" type="text" value="Patagones 2538 - Parque Patricios - Cap. Fed. - CP 1282ACD" disabled  size="47"></td>
                        </tr>
                </table></td>
          </tr>      
                <td><table>
                        <tr align="left">
                                <td><strong>Entrega</strong></td>
                                <td align="rigth"><textarea name="entrega" rows="5" cols="60"><?=$valor?></textarea></td>
                       </tr>
                </table></td>
          </tr>
        </table>
      </td>
      <td width="46%" align="center" valign="top" nowrap>
<table width="90%" border="1" cellpadding="0" cellspacing="0" bgcolor=<?=$bgcolor_out?>>
          <tr id="mo">
            <td colspan="2" height="20" align="center"><strong>Remito</strong></td>
          </tr>
          <tr>
            <td id="td_fecha_remito" height="25"><strong>Fecha Remito</strong></td>
            <td align="right"> <input name="fecha_remito" type="text" value="<?= $fecha_remito ?>" readonly size="10">
              <img <?=$permiso?> src=../../imagenes/cal.gif border=0 align=center style='cursor:hand;' alt='Haga click aqui para
               seleccionar la fecha'  onClick="javascript:popUpCalendar(td_fecha_remito, fecha_remito, 'dd/mm/yyyy');">
            </td>
          </tr>
        </table>
      </td>
    </tr>

  </table>
</td>
</tr>
</table>
<table id="productos" width="95%" border="1" cellpadding="0" cellspacing="0" align="center" bgcolor=<?=$bgcolor_out?>>
    <tr bgcolor="#006699" id="mo">
      <td width="10%"><strong>Cantidad</strong></td>
      <td width="85%"><strong>Item - Descripci&oacute;n</strong></td>
    </tr>
    <tr bgcolor="#006699" id="mo">
      <td width="10%"><input type="text" name="cant" value="<?=$cantidad?>" size="5"></td>
      <td width="85%"><textarea name="defecto" rows="4" style="width:80%"><?echo"$descripcion.\n \nDefecto:$defecto";?></textarea>
      </td>
    </tr>
 </TABLE>   
 <table  width="100%">
     <tr>
      <td width="100%" align="center">
      <input name="guardar" type="submit"  value="Guardar">
      <input name="cerrar" type="button"  value="Cerrar" onclick="window.close()">
      </td>
    </tr>
 </TABLE>  