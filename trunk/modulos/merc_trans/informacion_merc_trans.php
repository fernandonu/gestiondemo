<?php
/*
Autor: MAC
Fecha: 04/08/04

$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2004/08/13 14:56:15 $
*/
require_once("../../config.php");

$id_producto=$parametros["id_producto"] or $id_producto=$_POST["id_producto"];
$id_deposito=$parametros["id_deposito"] or $id_deposito=$_POST["id_deposito"];
$id_mercaderia_transito=$parametros['id_mercaderia_transito'] or $id_mercaderia_transito=$_POST["id_mercaderia_transito"];
$id_control_stock=$parametros["id_control_stock"] or $id_control_stock=$_POST["id_control_stock"];
$cantidad=$_POST["cantidad"];
$pagina_listado=$_ses_pagina_listado;

$usuario=$_ses_user["name"];
$fecha_actual=date("Y-m-d H:m:s",mktime());

if ($parametros["accion"]=="ver") $ver=1;
								   else $ver=0;


$sql="select productos.desc_gral,tipos_prod.descripcion as tipo_prod,";
$sql.=" proveedor.razon_social,proveedor.id_proveedor,";
$sql.=" descuento.cant_desc,control_stock.id_control_stock,";
$sql.=" control_stock.usuario,control_stock.fecha_modif,";
$sql.=" control_stock.comentario,control_stock.estado,";
$sql.=" productos.desc_gral";
$sql.=" from descuento";
$sql.=" join control_stock using (id_control_stock)";
$sql.=" join productos using(id_producto)";
$sql.=" join proveedor using (id_proveedor)
        join tipos_prod on (tipos_prod.codigo=productos.tipo)";
$sql.=" where id_producto=$id_producto and id_mercaderia_transito=$id_mercaderia_transito";
$sql.=" and id_deposito=$id_deposito";
/*if (!$ver)
		  {
		  $sql.=" and estado='p'";
		  }*/
$sql.=" and id_control_stock=$id_control_stock";
$resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
$cantidad=$resultado->RecordCount();
$comentario=$resultado->fields["comentario"];
$estado=$resultado->fields["estado"];
$cant_desc_bd=$resultado->fields["cant_desc"];

//vemos que estado es para poder determinar si es descuento o incremento
if($estado=="is")
 $cant_desc_inc="Cantidad Incrementada";
elseif($estado=="a") 
 $cant_desc_inc="Cantidad Descontada";

echo $html_header;
?>
<form name=form1 method="POST" action="stock_informacion.php">
<input type=hidden name="id_deposito" value="<?=$id_deposito?>">
<input type=hidden name="id_producto" value="<?=$id_producto?>">
<input type=hidden name="id_mercaderia_transito" value="<?=$id_mercaderia_transito?>">
<input type=hidden name="id_control_stock" value="<?=$id_control_stock?>">
<input type=hidden name=cantidad value="<?=$cantidad?>">
 <table width=80% align=center border="1" cellspacing="0" bordercolor="#A3A3A3" cellpadding="0" >
   <tr id="mo">
	 <td width=100%>
	 Información del Producto
	 </td>
   </tr>
   <tr>
	 <td width=100% align=center id="ma_sf">
			   <b><?=$resultado->fields["tipo_prod"]?>: <font color="Black"><?=$resultado->fields["desc_gral"]?></b>
			  </font> 
	 </td>
   </tr>
   <tr>
    <td width=100%><br><br>
     <table width=100% align=center>
     <tr id="mo">
       <td>Usuario</td>
       <td>Acción</td>
       <td>Fecha</td>
     </tr>
   <?
   $sql="select * from log_stock ";
   $sql.=" where id_control_stock=$id_control_stock";
   $log_stock=$db->execute($sql)  or die($db->errormsg()."<br>".$sql);
   $cant_log=$log_stock->recordcount();

   for($i=0;$i<$cant_log;$i++){
   ?>
   <tr id=ma>
    <td><b><?=$log_stock->fields["usuario"]?>     </b></td>
    <td><b><?=$log_stock->fields["tipo"]?>        </b></td>
    <td><b><?=fecha($log_stock->fields["fecha"])?></b></td>
   </tr>
   <?
   $log_stock->movenext();
   }
   ?>
   </table><br><br>
   </td>
   </tr>
   <tr>
    <td>
     <table width="100%" align="center">
      <tr bgcolor="#ACACAC">
       <td width=70%><b>Proveedor</b></td>
       <td width="30%" align="right">
         <b><?=$resultado->fields["razon_social"]?></b>
       </td>
      </tr>
      <tr bgcolor="#ACACAC">
	   <td align=left > <b><?=$cant_desc_inc?><b></td>
       <td align=right><b><?=$cant_desc_bd?><b></td>
      </tr>
     </table><br><br><br>
    </td>
   </tr>   
   <tr>
	 <td id="mo">
	 Comentarios
	 </td>
   </tr>
   <tr>
	<td align=center>
	  <textarea name="comentario" rows=3 cols=80 readonly><?=$comentario?>
	  </textarea>
	</td>
   </tr>
   <tr>
    <td align="center"> 
     <input type="button" name="Volver" value="Volver" onclick="document.location='historial_merc_trans.php'">
    </td>
   </tr>
  </table>
</form>
</BODY>
</HTML>
