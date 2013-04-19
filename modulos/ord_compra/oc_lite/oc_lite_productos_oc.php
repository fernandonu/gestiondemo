<?
/*
Autor: Broggi
Fecha: 20/04/05

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2005/04/27 22:59:39 $

*/

/**************************************************************************************************
 Genera una tabla con los datos propios de la OC, como son: Proveedor, Cliente, Forma de Pago, etc.
***************************************************************************************************/

 ////////////////////////////
//Esto que sigue va en el otro archivo

 //traemos las filas de la OC junto con la info de los entregados y recibidos de cada fila
 $query="select descripcion_prod,cantidad,desc_adic,precio_unitario,recibidos,entregados
         from fila left join 
            (select id_fila,sum(cantidad) as recibidos
			   from
			   compras.recibidos
		       where ent_rec=1
		       group by id_fila) r using(id_fila)
		       left join
			(select id_fila,sum(cantidad) as entregados
			   from
			   compras.recibidos
		       where ent_rec=0
		       group by id_fila) e using(id_fila)  
         where nro_orden=$nro_orden";
 $filas=sql($query,"<br>Error al traer los datos de la fila<br>") or fin_pagina();
 //echo $query;
 ?>

 
<br>
<table width="100%" align="center" class="bordes" >
<tr class="tabla_datos">
  <td colspan="4">
   <b><font size="3">Productos</font></b>
  </td>
 </tr>
 <tr class="tabla_datos">
  <td width="100%" colspan="4" >
   <table class="bordes" width="100%" border="1">
    <tr id="mo">     
     <td width="50%">
      Producto
     </td>
     <td width="10%">
      Cantidad
     </td>
     <td width="10%" title="Recibidos/Entregados">
      Recib./Ent.
     </td>
     <td width="15%">
      Precio Unitario
     </td>
     <td width="15%">
      Precio Total
     </td>
    </tr>
    <?
    $total=0;
    while (!$filas->EOF)
    {?>
     <tr >      
      <td align="center"><?=$filas->fields['descripcion_prod']?></td>
      <td align="center"><?=$filas->fields['cantidad']?></td>
      <td align="center"><?if ($filas->fields['recibidos']) echo $filas->fields['recibidos']; else echo "0"; echo "/"; if($filas->fields['entregados']) echo $filas->fields['entregados']; else echo "0";?></td>
      <td align="center"><?=formato_money($filas->fields['precio_unitario'])?></td>
      <?$total=$total+($filas->fields['precio_unitario']*$filas->fields['cantidad']);?>
      <td align="center"><?=formato_money($filas->fields['precio_unitario']*$filas->fields['cantidad'])?></td>
     </tr>
     <? 
     $filas->MoveNext();
    }//de while(!$filas->EOF)
    ?>
    </table>
   </td>
   
   </tr> 
   <tr align="right" class="tabla_datos">
   
    <td align="right" width="82%">
     <b>Total: </b>
    </td>
    <td align="center">
     <b><font color="Red"><?=$simbolo?></font>&nbsp;<?echo formato_money($total);?></b>
    </td>
   </tr> 
   </table>  
  </td>
 </tr>
</table>  