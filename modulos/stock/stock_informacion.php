<?php
/*
$Author: ferni $
$Revision: 1.14 $
$Date: 2006/03/21 19:01:21 $
*/
require_once("../../config.php");
require_once("funciones.php");

$id_en_stock=$parametros["id_en_stock"];
$id_prod_esp=$parametros["id_prod_esp"] or $id_prod_esp=$_POST["id_prod_esp"];
$id_deposito=$parametros["id_deposito"] or $id_deposito=$_POST["id_deposito"];
$id_log_mov_stock=$parametros["id_log_mov_stock"] or $id_log_mov_stock=$_POST["id_log_mov_stock"];
$pagina_listado=$_ses_pagina_listado;
$cantidad=$_POST["cantidad"];
$pagina_listado=$_ses_stock['pagina_listado'];
$cmd=$parametros["cmd"] or $cmd=$_POST["cmd"];

$usuario=$_ses_user["name"];
$fecha_actual=date("Y-m-d H:m:s",mktime());

if ($_POST["autorizar"])
{
      autorizar_rechazar_reserva_manual($id_log_mov_stock,"autorizar",$_POST["comentario"]);
      $exito="Los productos fueron descontados del stock en forma exitosa";
	  $link=encode_link("$pagina_listado",array("exito"=>$exito));
	  header("Location:$link");

}
if ($_POST["rechazar"])
{
      autorizar_rechazar_reserva_manual($id_log_mov_stock,"rechazar",$_POST["comentario"]);
      $exito="Los productos fueron devueltos al stock disponible en forma exitosa";
	  $link=encode_link("$pagina_listado",array("exito"=>$exito));
	  header("Location:$link");
}

$sql="
      select productos.id_prod_esp,productos.descripcion,productos.precio_stock,en_stock.cant_reservada,
             lms.cantidad,lms.fecha_mov,lms.usuario_mov,lms.comentario,tipo_movimiento.nombre,
             en_stock.cant_disp
             from en_stock
             join log_movimientos_stock lms using(id_en_stock)
             join producto_especifico productos using (id_prod_esp)
             join tipo_movimiento using (id_tipo_movimiento)
             where id_log_mov_stock=$id_log_mov_stock ";

$resultado=sql($sql) or fin_pagina($sql);
$cantidad=$resultado->RecordCount();
$cant_disp=$resultado->fields["cant_disp"];
$comentario=$resultado->fields["comentario"];
$id_prod_esp=$resultado->fields["id_prod_esp"];
echo $html_header;
?>
<br>
<form name=form1 method="POST" action="stock_informacion.php">
<input type=hidden name="id_deposito" value="<?=$id_deposito?>">
<input type=hidden name="id_prod_esp" value="<?=$id_prod_esp?>">
<input type=hidden name="id_log_mov_stock" value="<?=$id_log_mov_stock?>">
<input type=hidden name=cantidad value="<?=$cantidad?>">
<input type=hidden name=cmd value="<?=$cmd?>">
 <table width=95% align=center border="1" cellspacing="0" bordercolor="#A3A3A3" cellpadding="0"  bgcolor=<?=$bgcolor_out?>>
   <tr id="mo">
	 <td width=100%>
	 Información
	 </td>
   </tr>
   <tr>
  	 <td width=100%>
        		 <table width=100% align=center>
            		   <tr>
            			 <td width="20%"><font color=<?=$text_color_over?>>Producto:</font></td>
            			 <td width="80%" align=left>
            			 <b><?=$resultado->fields["descripcion"]?></b>
            			 </td>
            	 	   </tr>
                		<tr>
                    		 <td ><font color=<?=$text_color_over?>>Precio de Stock:</font></td>
                    		 <td align=left>
                    		 <b><font color="Red">U$S <?=number_format($resultado->fields["precio_stock"],"2",".","")?></font></b>
                    		 </td>
                		</tr>
                		<tr>
                			 <td width="20%"> <font color=<?=$text_color_over?>>Cantidad Disponible: </font></td>
                			 <td width="80%"><b><?=$cant_disp?></b></td>
                        </tr>
                		<tr>
                			 <td width="20%"> <font color=<?=$text_color_over?>>Cantidad Reservada: </font></td>
                			 <td width="80%"> <b><?=$resultado->fields["cant_reservada"]?></b></td>

                             <td>
                               <?
                                $link=encode_link("stock_excel.php",array("id_deposito"=>$id_deposito,"id_prod_esp"=>$id_prod_esp,"download"=>1));
                                ?>
                               <a target=_blank title='Bajar datos en un excel' href='<?=$link?>'>
                               <img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' >
                               </a>
                              </td>
                		</tr>
      		 </table>
	 </td>
   </tr>
   

<td width=100%>
 <table width=98% align=center class="bordes">
	<tr id="mo">
	 <td width=100% colspan="4">
	   <?if ($cmd=="pendientes") echo "Descuento Solicitado";
		 if ($cmd=="historial") echo "Movimiento de Stock Realizado";?>
	 </td>
    </tr>
   	  
<?if ($cmd=="pendientes"){?>   	  
   	   	<tr>
        	<td width="20%"><font color=<?=$text_color_over?>>Fecha de la Solicitud:</font></td>
            <td width="50%" align=left>
            	<b><?=fecha($resultado->fields["fecha_mov"])?></b>
            </td>
            <td width="6%"><font color=<?=$text_color_over?>>Usuario:</font></td>
            <td width="24%" align=left>
            	<b><?=$resultado->fields["usuario_mov"]?></b>
            </td>
      	</tr>
      	<tr>
        	<td width="20%"><font color=<?=$text_color_over?>>Cantidad a Descontar:</font></td>
            <td width="80%" align=left colspan="3">
            	<b><?=$resultado->fields["cantidad"]?></b>
            </td>
      	</tr>  		
<?}?>    
<?if ($cmd=="historial"){?>   	  
   	   	<tr>
        	<td width="20%"><font color=<?=$text_color_over?>>Fecha de la Solicitud:</font></td>
            <td width="50%" align=left>
            	<b><?=fecha($resultado->fields["fecha_mov"])?></b>
            </td>
            <td width="6%"><font color=<?=$text_color_over?>>Usuario:</font></td>
            <td width="24%" align=left>
            	<b><?=$resultado->fields["usuario_mov"]?></b>
            </td>
      	</tr>
      	<tr>
        	<td width="20%"><font color=<?=$text_color_over?>>Tipo del Movimiento:</font></td>
            <td width="80%" align=left colspan="3">
            	<b><?=$resultado->fields["nombre"]?></b>
            </td>
      	</tr>
      	
      	<tr>
        	<td width="20%"><font color=<?=$text_color_over?>>Cantidad:</font></td>
            <td width="80%" align=left colspan="3">
            	<b><?=$resultado->fields["cantidad"]?></b>
            </td>
      	</tr>  		
<?}?>      	  	
      	<br>
      	<tr> <td colspan="4" align="center"><font size="2" color=<?=$text_color_over?>> <b>Comentarios </b></font></td> </tr>
        <tr>
      	   <td align=center colspan="4">
	         <textarea name="comentario" rows=3 cols=145 readonly><?=$comentario?></textarea>
	       </td>
        </tr>
       <br>
 </table>
</td>
	
    
<tr>
	<td align='center'>  
		<br> 
		<?if ($cmd=='historial'){
			mostrar_ing_egr($id_prod_esp,$id_deposito);
		}?>	 
		<?if (($cmd=="pendientes")&&(permisos_check("inicio","autorizar_rechazar"))){?>
			<input type="submit" name="autorizar" value="Autorizar">&nbsp;
		    <input type="submit" name="rechazar" value="Rechazar">
		<?}?>
			<input type="button" name="volver" value="Volver" onclick="document.location.href='listado_depositos.php'">
		</td>
	</tr>
</table>
</form>
</BODY>
</HTML>
<?
echo fin_pagina();
?>