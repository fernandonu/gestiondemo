<?
/*
$Author: gabriel $
$Revision: 1.7 $
$Date: 2006/01/31 19:35:00 $
*/
require_once("../../config.php");

$fecha_hoy=$parametros['fecha'];
$prov_comida=$parametros['proveedor'];

$pedido_usuario="
select pedido_usuario.id_guarnicion, guarnicion.nombre_guarnicion, plato.nombre_plato, plato.id_grupo_comidas as grupo_plato, 
	guarnicion.id_grupo_comidas as grupo_guarnicion, pedido_usuario.fecha_pedido, pedido_usuario.id_usuario,
	usuarios.nombre, usuarios.apellido, nro_grupo
from comidas.pedido_usuario 
	left join sistema.usuarios using (id_usuario)
        left join comidas.plato using (id_plato) 
        left join comidas.guarnicion using (id_guarnicion)
	left join comidas.grupos_comidas using (login)
where fecha_pedido='".Fecha_db($fecha_hoy)."' 
	and pedido_usuario.id_proveedor_comida=$prov_comida
order by nro_grupo, usuarios.nombre";

$res_pedido_usuario=sql($pedido_usuario) or fin_pagina();
$cant_pedidos=$res_pedido_usuario->Recordcount();

$cons_platos="select * from comidas.plato
              where plato.habilitado=1 and  id_proveedor_comida=$prov_comida order by nombre_plato";
$res_platos=sql($cons_platos) or fin_pagina();
$cant_platos=$res_platos->Recordcount();

$cons_guarniciones="select * from comidas.guarnicion 
                    where guarnicion.habilitado=1 and id_proveedor_comida=$prov_comida order by nombre_guarnicion";
$res_guarniciones=sql($cons_guarniciones) or fin_pagina();
$cant_guarniciones=$res_guarniciones->Recordcount();

?>

<script language="javascript">

function imprimir(){
 document.all.imprimir.style.visibility="hidden";
 window.print();
 window.close();
}

</script>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Listado de los Pedidos de Comidas</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<link rel=stylesheet type='text/css' href='<? echo "$html_root/lib/estilos.css"?>'>
<table>
  <tr>
    <td><input type="button" name="imprimir" value="Imprimir Pedido de Comidas" onclick="imprimir()"></td>
  </tr>
</table>
<br><br>
<table width="100%"  border="1" cellspacing="0" cellpadding="0">
  <tr>
    <th scope="col">Nombre y Apellido </th>
    <th scope="col">Pedido de comidas </th>
  </tr>
  <?
  
  for ($i=0;$i<$cant_pedidos;$i++) {
  	$nro_grupo=$res_pedido_usuario->fields['nro_grupo'];
  ?>
  <tr>
    <td><?=$res_pedido_usuario->fields['nombre']." ".$res_pedido_usuario->fields['apellido']?></td>
    <?
    if ($res_pedido_usuario->fields['grupo_plato']==2 || $res_pedido_usuario->fields['grupo_plato']==3) {
    ?>
    <td><?=$res_pedido_usuario->fields['nombre_plato']." con ".$res_pedido_usuario->fields['nombre_guarnicion']?></td>
    <? } 
    else { 
    ?>
     <td><?=$res_pedido_usuario->fields['nombre_plato']?></td>
    <? } ?>
  </tr>
  <? $res_pedido_usuario->MoveNext(); 
  if ($nro_grupo!=$res_pedido_usuario->fields['nro_grupo']){
  	echo("<tr><td colspan=2 align='center' bgcolor='#DDDDDD'>".(($res_pedido_usuario->fields['nro_grupo'])?"<b>Los de al lado</b>":"")."&nbsp</td></tr>");
  }
} ?>
</table>
<br><br>
<?
$cant_pedidos_us="select count( pedido_usuario.id_plato ) as cantidad_pedidos, 
                  pedido_usuario.id_plato, pedido_usuario.id_guarnicion,
                  nombre_plato, nombre_guarnicion  
                  from comidas.pedido_usuario 
                  left join comidas.plato using (id_plato)
                  left join comidas.guarnicion using (id_guarnicion) 
                  where fecha_pedido='".fecha_db($fecha_hoy)."'
                  and pedido_usuario.id_proveedor_comida=$prov_comida
                  group by id_guarnicion, id_plato, nombre_plato, nombre_guarnicion
                  order by nombre_plato";
$res_cant_pedidos_us=sql($cant_pedidos_us) or fin_pagina();
$cant_pedidos_comidas=$res_cant_pedidos_us->recordcount();
?>
<table width="90%"  border="1" cellspacing="0" cellpadding="0">
  <tr>
    <th scope="col" colspan="2" align="center"><b>Comidas Pedidas para el <?=$fecha_hoy?></b></th>  
  </tr>
  <tr>
    <th scope="col"><b>Cant.</b></th>
    <th scope="col"><b>Comidas</b></th>
    
  </tr>
  <? $cantidad_comidas=0; 
    for ($a=0;$a<$cant_pedidos_comidas;$a++) {?>
  <tr>
    <td align="right"><?=$res_cant_pedidos_us->fields['cantidad_pedidos']?>&nbsp;&nbsp;</td>  
    <td>&nbsp;&nbsp;
      <?
      $cantidad_comidas+=$res_cant_pedidos_us->fields['cantidad_pedidos'];
      if ($res_cant_pedidos_us->fields['nombre_guarnicion']=='') echo $res_cant_pedidos_us->fields['nombre_plato']; 
      else echo $res_cant_pedidos_us->fields['nombre_plato']." con ".$res_cant_pedidos_us->fields['nombre_guarnicion']?></td>  
    </td>  
    
  </tr>
<? $res_cant_pedidos_us->MoveNext();
    } ?>
  <tr>
    <td colspan="2"><b>Total de Comidas: <?=$cantidad_comidas?></b></td>
  </tr>  
</table>

</body>
</html>
