<?
/*
Author: Broggi
Fecha: 28/07/2004

MODIFICADA POR
$Author: ferni $
$Revision: 1.19 $
$Date: 2005/11/29 15:02:41 $
*/

require_once("../../config.php");

echo $html_header;
cargar_calendario();
if (!$contador_pagina) phpss_svars_set("contador_pagina",0);
if ($filtrar_por_forma_pago) 
   {echo "imprimi".$filtrar_por_forma_pago;
   	die();
   	phpss_svars_set("filtrar_por_forma_pago", "");
   }	
   
?>
<form name="busq_avanzada_armo" action="busq_avanzada_armo.php" method="post">
<table align="center">
 <tr>
  <td>
   <font size="4"><b>Busquedas Avanzadas</b></font>
  </td>
 </tr>
</table>
<br>

<script src='busq_avanzada_controles.js'> 

</script>
<br>
<br>
<?$fecha_actual=$fecha=date("d/m/Y");?>
<table align="center" class="bordes" cellpadding="2" cellspacing="2" width="90%">
 <tr>
  <td>
   <b>Busqueda General</b>
  </td>
  <td>
   <input name="usar_bus_general" type="checkbox" value="usar_bus_general" onclick="check_bus_general()">
  </td> 
  <td>
  <?/*onkeypress="if (getkey(event)==13) buscar.onclick();"*/?>
   <b>Buscar:</b>&nbsp;&nbsp;<input name="keyword" type="text" size="20" onchange="habilitar_keyword()">&nbsp;&nbsp;<b>en:</b>
   <select name="opcion_busq_general">
    <option selected value="all">Todos los Campos</option>
    <option value="nro_orden">N° de Orden</option>
    <option value="nro_factura">N° de Factura</option>
    <option value="id_licitacion">Id Licitacion</option>
    <option value="lugar_entrega">Lugar de Entrega</option>
    <option value="cliente">Cliente</option>
    <option value="notas">Comentarios</option>
    <option value="razon_social">Proveedor</option>
    <!--<option value="(fila.descripcion_prod || fila.desc_adic)">Productos</option>-->
    <option value="descripcion_prod">Productos</option>
    
   </select>
  </td> 
 </tr> 
 <tr>
  <td>Número de Orden</td>
 
  <td><input name="filtrar_nro_orden" type="checkbox" value="filtrar_nro_orden"  onclick="check_nro_orden()"> </td>
  <td><input name="nro_orden" type="text" onkeypress="return filtrar_teclas(event,'0123456789'); " onchange="habilitar_nro_orden()">(Específico) </td>
 <!-- <td align="center"><input name="ordenar_nro_orden" type="checkbox" value="ordenar_nro_orden"  onclick="habilitar_ordenar(this,'nro_orden')" disabled ></td>-->
 </tr>
 <tr>
  <td>Estado de la Orden</td>
  <td><input name="filtrar_estado" type="checkbox" value="filtrar_estado" onclick="check_estado()"></td>
  <td>
   <select name="estado" style="width='50%'" onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange="habilitar_estado()">
    <option selected value=-1>Elija Estado de la Orden</option>
    <option value="p">Pendiente</option>
    <option value="a">Autorizada</option>
    <option value="r">Rechazada</option>
    <option value="e">Enviada al Proveedor</option>
    <option value="d">Pagadas Parcialmente</option>
    <option value="g">Pagadas Totalmente</option>
    <option value="n">Anuladas</option>
    <option value="u">Por Autorizar</option>
   </select> 
  </td>
  <td align="center"><input name="ordenar_estado" type="checkbox" value="ordenar_estado"  onclick="habilitar_ordenar(this,'estado')" disabled></td>  
 </tr>
 <tr>
  <td>Tipo de Orden</td>
  <td><input name="filtrar_tipo_orden" type="checkbox" value="filtrar_tipo_orden" onclick="check_tipo_orden()"  ></td>
  <td >
   <select name="tipo_orden" style="width='50%'" onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange='habilitar_tipo_orden()' >
    <option selected value=-1>Elija el tipo de Orden</option>
    <option value="l">Licitación</option>
    <option value="p">Presupuesto</option>
    <option value="s">Servicio Técnico</option>
    <option value="h">Honorarios Servicio Técnico</option>
    <option value="sc">Stock Coradir</option>
    <option value="r">RMA de Producción</option>
    <option value="i">Internacional</option>
    <option value="o">Otro</option>
   </select> 
  </td>  
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>Monto</td>
  <td><input name="filtrar_por_monto" type="checkbox" value="filtrar_por_monto" onclick="check_monto()"></td>
  <td>&nbsp; <input name="monto_1" type="text" id="monto_1" size="10" onkeypress="return filtrar_teclas(event,'0123456789.');" onchange="habilitar_monto()">&nbsp; <b> <= OC <= </b>&nbsp; 
                   <input name="monto_2" type="text" id="monto_2" size="10" onkeypress="return filtrar_teclas(event,'0123456789.');" onchange="habilitar_monto()">&nbsp;&nbsp; <b>No use separador de Miles</b></td>
  <td align="center"><input name="ordenar_monto" type="checkbox" value="ordenar_monto"  onclick="habilitar_ordenar(this,'monto')"  disabled></td>
 </tr>
 <tr>
  <td>Forma de Pago</td>
  <td>
    <input name="filtrar_por_forma_pago" type="checkbox" value="filtrar_por_forma_pago"  onclick="check_forma_pago()">
   </td>
   <td>
   <table>
   <tr>
   <td>
   <?
   $sql="select id_plantilla_pagos, descripcion from plantilla_pagos where mostrar=1 order by descripcion";
    $resultado_forma_pago=sql($sql) or fin_pagina();
   ?> 
  
  <select name="forma_pago" onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange='habilitar_forma_pago();if(this.value!=-1)document.all.forma_pago_texto.value="";'> 
   <option selected value=-1>Elija Forma de Pago</option>
  <?
   while (!$resultado_forma_pago->EOF)
         {?>
       <option value="<?=$resultado_forma_pago->fields['descripcion']?>" >
       <?=$resultado_forma_pago->fields['descripcion']?></option>
   <?
     $resultado_forma_pago->MoveNext();
     }
   ?>
   </select>

   </td>
  <td>
   <input name="forma_pago_texto" type="text" onchange='habilitar_forma_pago();if(this.value!="")document.all.forma_pago.selectedIndex=0'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<!--<b>Dias: </b> <input name="forma_pago_dias" size="4" type="text" onkeypress="return filtrar_teclas(event,'0123456789');">-->     
  </td> 
  </tr> 
  </table>
  </td>
 
  <td align="center">&nbsp;</td>
 </tr>
 <tr>
  <td>Tipo de Productos</td>
  <td><input name="filtrar_productos" type="checkbox" value="filtrar_productos"  onclick="check_habilitar_productos()"></td>
  <td>
   <?
    $sql="select tipos_prod.codigo, descripcion from tipos_prod order by descripcion";
    $resultado_productos=sql($sql) or fin_pagina();
   ?>
   <select name="productos" onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange="habilitar_productos()">
   <option selected value=-1>Elija tipo de Producto</option>
   <?
     while(!$resultado_productos->EOF)
     {
             	
   ?>
       <option value="<?=$resultado_productos->fields['codigo']?>" >
       <?=$resultado_productos->fields['descripcion']?></option>
   <?
     $resultado_productos->MoveNext();
     }
   ?>
   </select>
  </td>
  <!--<td align="center"><input name="ordenar_productos" type="checkbox" value="ordenar_productos"  onclick="habilitar_ordenar(this,'productos')" ></td>-->
  <td>&nbsp;</td>
 </tr> 
 <tr>
  <td>Entregados/Recibidos</td>
  <td><input name="filtrar_re_en" type="checkbox" value="filtrar_re_en"  onclick="check_habilitar_re_en()"></td>
  <td>
   <select name="entregado_recibido" onchange="habilitar_re_en()">
    <option selected value="-1">Seleccione Entregado/Recibido</option>
    <option value="1">Todos Entregados</option>
    <option value="2">Todos Recibidos</option>
    <option value="5">Falta Entregar</option>
    <option value="6">Falta Recibir</option>
    <option value="3">Todos Ent. y todos Rec.</option>
    <option value="4">Falta Ent. y falta Rec.</option>
   </select>
  </td>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>ID. de Licitación</td>
  <td><input name="filtrar_id_licitacion" type="checkbox" value="filtrar_id_licitacion" onclick="check_id_licitacion()"></td>
  <td><input name="id_licitacion" type="text" onkeypress="return filtrar_teclas(event,'0123456789');" onchange="habilitar_id_licitacion()"></td>
  <td align="center"><input name="ordenar_id_licitacion" type="checkbox" value="ordenar_id_licitacion"  onclick="habilitar_ordenar(this,'id_licitacion')" disabled></td>
 </tr>
 <tr>
  <td>Proveedor</td>
  <td><input name="filtrar_id_proveedor" type="checkbox" value="filtrar_id_proveedor"  onclick="check_proveedor()"></td>
  <td>
   <?
    $sql="select proveedor.razon_social, proveedor.id_proveedor from proveedor order by razon_social";
    $resultado_proveedor=sql($sql) or fin_pagina();
   ?>
   <select name="proveedor" onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange="habilitar_proveedor()">
   <option selected value=-1>Elija un Proveedor</option>
   <?
     while(!$resultado_proveedor->EOF)
     {             	
   ?>
       <option value=<?=$resultado_proveedor->fields['id_proveedor']?> >
       <?=$resultado_proveedor->fields['razon_social']?></option>
   <?
     $resultado_proveedor->MoveNext();
     }
   ?>
   </select>
  </td>
  <td align="center"><input name="ordenar_proveedor" type="checkbox" value="ordenar_proveedor"  onclick="habilitar_ordenar(this,'proveedor')" disabled></td>
 </tr> 
 <tr>
  <td>Tipo de Moneda</td>
  <td><input name="filtrar_id_moneda" type="checkbox" value="filtrar_id_moneda"  onclick="check_moneda()"> </td>
  <td>
   <?
    $sql="select moneda.nombre, moneda.id_moneda from moneda order by nombre";
    $resultado_moneda=sql($sql) or fin_pagina();
   ?>
    <select name="moneda" onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange="habilitar_moneda()">
    <option selected value=-1>Elija tipo de Moneda</option>
   <?
    while(!$resultado_moneda->EOF)
     {
   ?>
       <option value=<?=$resultado_moneda->fields['id_moneda']?> >
       <?=$resultado_moneda->fields['nombre']?></option>
   <?
   
     $resultado_moneda->MoveNext();
     }   
   ?>
   </select>  
  </td>
  <!--<td align="center"><input name="ordenar_moneda" type="checkbox" value="ordenar_moneda"  onclick="habilitar_ordenar(this,'moneda')" ></td>-->
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td >Entidad </td>
  <td><input name="filtrar_id_entidad" type="checkbox" value="filtrar_id_entidad"  onclick="check_entidad()"> </td> 
  <td>
   <?
    $sql="select entidad.nombre, entidad.id_entidad from entidad order by nombre";
    $resultado_entidad=sql($sql) or fin_pagina();
   ?>
    <select name="entidad" style="width='50%'" onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange="habilitar_entidad()">
    <option selected value=-1>Elija Entidad</option>
   <?
    while(!$resultado_entidad->EOF)
     {
      if ($resultado_entidad->fields['nombre']!=" ")       	
      {
   ?>
       <option value=<?=$resultado_entidad->fields['id_entidad']?> >
       <?=$resultado_entidad->fields['nombre']?></option>
       
   <?
      }//del if
     $resultado_entidad->MoveNext();
     }
   
   ?>
   </select>  
  </td>
  <td align="center"><input name="ordenar_entidad" type="checkbox" value="ordenar_entidad"  onclick="habilitar_ordenar(this,'entidad')" disabled></td>
 </tr>
 
 <tr>
  <td>Orden de Producción </td>
  <td><input name="filtrar_orden_prod" type="checkbox" value="filtrar_orden_prod"  onclick="check_orden_prod()"> </td>
  <td><input name="orden_prod" type="text" onkeypress="return filtrar_teclas(event,'0123456789');" onchange="habilitar_orden_prod()"> </td>
  <td align="center"><input name="ordenar_orden_prod" type="checkbox" value="ordenar_orden_prod"  onclick="habilitar_ordenar(this,'orden_prod')" disabled></td>
 </tr> 
 <tr>
  <td>Nro. de Factura</td>
  <td><input name="filtrar_nro_factura" type="checkbox" value="filtrar_nro_factura" onclick="check_factura()"></td>
  <td><input name="nro_factura" type="text" onchange="habilitar_nro_factura()"> </td>
  <td align="center"><input name="ordenar_nro_factura" type="checkbox" value="ordenar_nro_factura"  onclick="habilitar_ordenar(this,'nro_factura')" disabled></td>
 </tr>
 <tr>
  <td>Fecha de Factura</td>
  <td><input name="filtrar_fecha_factura" type="checkbox" onclick="check_fecha_factura()"></td>
  <td><b>Entre</b> <input name="fecha_factura_1" type="text" id="fecha_factura_1" size="10"  readonly onchange="habilitar_fecha_factura()">&nbsp;<?=link_calendario("fecha_factura_1");?> <b>y</b> 
            <input name="fecha_factura_2" type="text" id="fecha_factura_2" value="<?=$fecha_actual?>"size="10"  readonly onchange="habilitar_fecha_factura()">&nbsp;<?=link_calendario("fecha_factura_2");?>
  </td>
  <td align="center"><input name="ordenar_fecha_factura" type="checkbox" value="ordenar_fecha_factura"  onclick="habilitar_ordenar(this,'fecha_factura')" disabled></td>
 </tr>
 <tr>
  <td>Lugar de Entrega</td>
  <td><input name="filtrar_lugar_entrega" type="checkbox" value="filtrar_lugar_entrega"  onclick="check_entrega()"></td>
  <td><input name="lugar_entrega" type="text" onchange="habilitar_lugar_entrega()"></td>
  <td align="center"><input name="ordenar_lugar_entrega" type="checkbox" value="ordenar_lugar_entrega"  onclick="habilitar_ordenar(this,'lugar_entrega')" disabled></td>
 </tr>
 <tr>
  <td>Fecha Entrega</td>
  <td><input name="filtrar_fecha_entrega" type="checkbox" value="filtrar_fecha_entrega" onclick="check_fecha_entrega()"></td>
  <td><b>Entre</b> <input name="fecha_entrega_1" type="text" id="fecha_entrega_1" size="10"  readonly onchange="habilitar_fecha_entrega()">&nbsp;<?=link_calendario("fecha_entrega_1");?>     
  <b>y</b><input name="fecha_entrega_2" type="text" id="fecha_entrega_2" value="<?=$fecha_actual?>"size="10"  readonly onchange="habilitar_fecha_entrega()">&nbsp;<?=link_calendario("fecha_entrega_2");?>
  </td>
  <td align="center"><input name="ordenar_fecha_entrega" type="checkbox" value="ordenar_fecha_entrega"  onclick="habilitar_ordenar(this,'fecha_entrega')" disabled></td>
 </tr>
  
 
 <tr>
  <td align="center" colspan="4">&nbsp;&nbsp;<input type=button name=buscar value='Buscar' onclick="return control_general()"></td>
 </tr>
 
</table>
<SCRIPT>
document.all.keyword.focus();
</SCRIPT>

<br>
<?

//fin_pagina();
?>