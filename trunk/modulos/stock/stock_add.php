<?
/*
Autor: MAC

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.46 $
$Date: 2005/09/08 19:41:55 $
*/

require_once("../../config.php");

$tipo_p=$parametros['tipo'];

$id_prod=$parametros['id_producto'];
$id_deposito=$parametros['id_deposito'];
$id_proveedor=$parametros['id_proveedor'];
$cant_disp=$_POST['cantidad'];
$fecha_hoy = date("Y-m-d H:i:s");
$comentarios=$_POST['observacion'];

if ($_POST['Guardar']=="Guardar")  //se presiono el boton guardar.
    {
     $db->starttrans();
     $id_deposito=$_POST["id_deposito"];
     // insertar en la tabla de stock o actualizamos la misma tabla.

     $query="SELECT count(id_producto) as count from stock where id_producto=$id_prod and id_proveedor=$id_proveedor and id_deposito = $id_deposito";
     $result_prod=$db->Execute($query) or die ($db->ErrorMsg().$query);

     if ((($result_prod->fields['count']==0)&&($_POST["cambio_dep"]=="0"))||($_POST["cambio_dep"]=="1")&&($result_prod->fields['count']==0))
         {
         $query="INSERT into stock (id_deposito,id_producto,cant_disp,last_user,last_modif,comentario,id_proveedor)
                values ($id_deposito,$id_prod,$cant_disp,'$_ses_login','$fecha_hoy','$comentarios',$id_proveedor)";
         $resultados=$db->Execute($query) or die ($db->ErrorMsg().$query);
         
     }
     else {
 	   $query="SELECT cant_disp from stock where id_producto=$id_prod and id_proveedor=$id_proveedor and id_deposito = $id_deposito";
           $cant=$db->Execute($query) or die ($db->ErrorMsg().$query);
 	   $cant_prev=$cant->fields["cant_disp"];
	   $query="UPDATE stock SET
                  id_deposito = $id_deposito ,id_producto=$id_prod,
                  cant_disp=$cant_disp+$cant_prev,last_user='$_ses_login',
                  last_modif='$fecha_hoy',comentario='$comentarios',
                  id_proveedor=$id_proveedor
                  where id_producto=$id_prod and id_proveedor=$id_proveedor and id_deposito = $id_deposito";
          $resultados=$db->Execute($query) or die ($db->ErrorMsg().$query);
         }

//si es deposito RMA, insertamos la informacion requerida, en la
//tabla info_rma
//--------------------------------------------------------------
//para eso buscamos si el $id_deposito es RMA, en la BD.
$query="select nombre from depositos where id_deposito=$id_deposito";
$rma=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer el nombre del deposito de id: $id_deposito");
//si es igual a RMA, insertamos la info en info_rma
if($rma->fields['nombre']=='RMA')
{$query="select nextval('info_rma_id_info_rma_seq') as id_info";
 $id=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer el id de info_rma");
 $id_info_rma=$id->fields['id_info'];         
 $query="insert into info_rma(id_info_rma,id_deposito,id_producto,id_proveedor,cantidad)
          values($id_info_rma,$id_deposito,$id_prod,$id_proveedor,$cant_disp)";
 $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar informacion de RMA<br>$query");
 //Agregamos un comentario para indicar que el productos entro
 //desde el modulo productos, y no desde Orden de Compra
 $fecha_hoy=date("Y-m-d H:i:s",mktime());
 $user_name=$_ses_user['name'];
 $query="insert into comentarios_rma(id_info_rma,id_deposito,id_producto,id_proveedor,texto,fecha,usuario)
          values($id_info_rma,$id_deposito,$id_prod,$id_proveedor,'Producto ingresado manualmente a RMA por el usuario $user_name','$fecha_hoy','$user_name')";
 $db->Execute($query) or die($db->ErrorMsg()."<bR>Error al insertar comentarios de rma");
}	 
         
 $link4 = encode_link($html_root."/index.php",array("menu"=>"productos1","extra"=>array("tipo"=>$tipo_p,"texto" => $parametros["texto"],
                                                    "campo" => $parametros["campo"]) ));


//MODIFICO EL LOG DE LOS PRODUCTO
$usuario=$_ses_user["name"];

$query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
$resultado=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock");
$id_control_stock=$resultado->fields["id_control_stock"];

$coment_stock=$comentarios?$comentarios:'Inserción manual del stock';
$query="insert into control_stock
       (id_control_stock,fecha_modif,usuario,comentario,estado)
        values($id_control_stock,'$fecha_hoy','$usuario','$coment_stock','is')";
$db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en control_stock");

if($id_info_rma=="")
 $id_info_rma="NULL";
$query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc,id_info_rma)
        values($id_deposito,$id_prod,$id_proveedor,$id_control_stock,$cant_disp,$id_info_rma)";
$db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en descuento<br>".$query);

$tipo_log=$comentarios?$comentarios:'Ingreso de Stock';
$query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
        values ($id_control_stock,'$usuario','$fecha_hoy','$tipo_log')";
$db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en log_stock");

$db->completetrans();
/*
echo "<html><head><script language=javascript>";
echo "window.parent.location='$link4';";
echo "</script></head></html>";
*/
}
if ($_POST['Volver']=="Volver")	 {
$link4 = encode_link($html_root."/index.php",array("menu"=>"productos1","extra"=>array("tipo"=>$tipo_p,"texto" => $parametros["texto"],
                                                   "campo" => $parametros["campo"]) ));

 echo "<html><head><script language=javascript>";
 echo "window.parent.location='$link4';";
 echo "</script></head></html>";
}

$query="select * from productos where id_producto=$id_prod";
$producto=$db->Execute($query) or die($db->ErrorMsg().$query);
$query="select razon_social from proveedor where id_proveedor=$id_proveedor";
$proveedor=$db->Execute($query)or die($db->ErrorMsg().$query);
$query="select sum(cant_disp) as cant from stock join depositos on depositos.id_deposito=stock.id_deposito and stock.id_producto=$id_prod and stock.id_proveedor=$id_proveedor";
$stock_r=$db->Execute($query)or die($db->ErrorMsg().$query);

$q="select * from depositos where tipo=0 or tipo =2";
$depositos=$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
</head>
<body  bgcolor="#E0E0E0" topmargin="2">
<form name="form1" method="post" action="<? encode_link("stock_add.php",array("tipo"=>$parametros["tipo"],"id_producto" => $id_prod,"id_proveedor" => $id_proveedor,"id_deposito" => $id_deposito, "texto" => $parametros["texto"],
                         "campo" => $parametros["campo"]));?>">

<table width="60%" border="1" cellpadding="1" cellspacing="1" align="center">
	    <tr>
	      <td  colspan="2" align="center"><strong>INFORMACION DEL PRODUCTO</strong></td>
	    </tr>
	    <tr>
	      <td width="30%"><strong>Tipo de Producto</strong></td>
	      <td width="70%"><? echo $producto->fields['tipo'] ?> </td>
	    </tr>
	    <tr>
	      <td><strong>Marca</strong></td>
	      <td><?=($producto->fields['marca'])?$producto->fields['marca'] :'&nbsp;' ?></td>
	    </tr>
	    <tr>
	      <td><strong>Modelo</strong></td>
	      <td><?=($producto->fields['modelo'])?$producto->fields['modelo'] :'&nbsp;' ?></td>
	    </tr>
	    <tr>
	      <td><strong>Descripcion</strong></td>
	      <td><?=($producto->fields['desc_gral'])?$producto->fields['desc_gral'] :'&nbsp;' ?></td>
	    </tr>
	  </table><br>

<table width="60%" border="1" cellpadding="1" cellspacing="1" align="center">
     <tr>
      <td width="30%"><strong>Deposito</strong></td>
      <td align="right" >
          <select name="id_deposito" id="id_deposito" onchange="document.all.cambio_dep.value='1'">
            <option value="0" selected>Seleccione un deposito&nbsp;&nbsp;&nbsp;</option>
            <? while (!$depositos->EOF)
	          	{
	          ?>
            <option value='<?=$depositos->fields['id_deposito']?>' <?php if(($depositos->fields['id_deposito']==$id_deposito)||($depositos->fields['id_deposito']==$_POST["id_deposito"]))echo 'selected';else echo ' ';?> ><?php echo $depositos->fields['nombre'];?></option>
            <?


	          $depositos->MoveNext();
	          	}
				 ?>
          </select>
      </td>
    </tr>
    <tr>

    <td><strong>Proveedor</strong><br></td>
      <td align="right">
         <?PHP echo $proveedor->fields["razon_social"]; ?>
        </td>
    </tr>
    <tr>
      <td><strong>Cantidad en Stock de este proveedor</strong></td>
      <td align="center">
        <?php if($stock_r->fields['cant']==null) echo 0; else echo $stock_r->fields['cant'];?>
      </td>
    </tr>
    <tr>
      <td><strong>Cantidad Añadida</strong></td>
      <td  align="center"><input name="cantidad" id="cantidad" type="text" size="10" value=""></td>
    </tr>
    <tr>
      <td nowrap><strong>Observaciones</strong></td>
      <td align="right" nowrap> <textarea name="observacion" cols="35" rows="4" wrap="VIRTUAL"></textarea></td>
    </tr>
</table>
<table width="100%" align="center">
 <tr>
      <td colspan="2" align="center">
          <input type="submit" name="Guardar" value="Guardar" onclick=
				 "

				 	if (document.form1.id_deposito.options[document.form1.id_deposito.selectedIndex].value==0)
				 	{
				 		alert ('Por favor selecciona un deposito');
				 		return false;
				 	}
					if (cantidad.value=='')
					{
						alert ('La cantidad debe ser un numero valido');
						return false;
					}
				 	if (cantidad.value!='')
					{
						if (isNaN(parseInt(cantidad.value)))
						{
							alert ('La cantidad debe ser un numero valido');
							return false;
						}
						else if (cantidad.value <= 0)
						{
							alert ('La cantidad debe ser un numero valido MAYOR QUE CERO');
							return false;
						}

					}

				 ">
          <input type="submit" name="Volver" value="Volver">

          <input type="hidden" name="cambio_dep" value="0">
      </td>
    </tr>

</table>
<br>
</form>
</body>
</html>
