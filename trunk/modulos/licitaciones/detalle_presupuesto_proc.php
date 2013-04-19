<?
/*
Autor: GACZ
Creado: miercoles 15/09/04

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.15 $
$Date: 2005/04/22 16:26:59 $
*/

/*********************************************
Esta pagina se llama desde:
detalle_presupuesto.php
presup_auto.php (el cual se incluye en el lib)
**********************************************/

require_once("../../config.php");

$id_lic_prop=$_POST['id_lic_prop'];
$id_lic=$parametros['ID'];
$titulo=$_POST['titulo'];
$comentarios=$_POST['comentarios'];
$beliminar=$_POST['beliminar'];//boton que elimina el presupuesto
$entrega_estimada_producto=$_POST['entrega_estimada_producto'];
if (!FechaOk($entrega_estimada_producto)) { $entrega_estimada_producto = "NULL"; }
else { $entrega_estimada_producto = "'".fecha_db($entrega_estimada_producto)."'"; }

$db->StartTrans();
//si es un presupuesto nuevo
if ($id_lic_prop==-1)
{
	$q ="insert into licitacion_presupuesto_new ";
	$q.="(id_licitacion,id_entrega_estimada,titulo,comentarios,entrega_estimada_producto) ";
	$q.="values ($id_lic,$id_entrega_estimada,'$titulo','$comentarios',$entrega_estimada_producto)";
	sql($q) or fin_pagina();
	
	$q="select max(id_licitacion_prop) as id from licitacion_presupuesto_new";
	$r=sql($q) or fin_pagina();
	$id_lic_prop=$r->fields['id'];
	$q = "INSERT INTO log_presupuesto (id_licitacion_prop,id_usuario,fecha,descripcion) VALUES "
		."($id_lic_prop,".$_ses_user["id"].",'".date("Y-m-d H:i:s")."','Creado')";
	sql($q) or fin_pagina();
	
}
//sino actualizar
else
{
	$q ="UPDATE licitacion_presupuesto_new set ";
	$q.="titulo='$titulo', ";
	$q.="comentarios='$comentarios', ";
	$q.="entrega_estimada_producto=$entrega_estimada_producto ";
	$q.="where id_licitacion_prop=$id_lic_prop ";
	sql($q) or fin_pagina();
	$q = "INSERT INTO log_presupuesto (id_licitacion_prop,id_usuario,fecha,descripcion) VALUES "
		."($id_lic_prop,".$_ses_user["id"].",'".date("Y-m-d H:i:s")."','Modificado')";
	sql($q) or fin_pagina();
}


//ids de los proveedores que hay en el presupuesto
$proveedores=PostvartoArray("hidden_idprov_");//sufijo $col_prov
//print_r($proveedores);
//echo "<br><br>";

//ids de renglones de presupuesto
$renglonesp=PostvartoArray("hidden_idrenglon_");//sufijo $i
//print_r($renglonesp);
//echo "<br><br>";

if ($beliminar)
{
	if (is_array($renglonesp) && count($renglonesp))
	{
	//renglones del presupuesto
	$borrar_reng=implode(",",$renglonesp);
	
	//borro los productos de producto_proveedor 
	$q ="delete from producto_proveedor_new where id_producto_presupuesto in ";
	$q.="(select id_producto_presupuesto from producto_presupuesto_new ";
	$q.="where id_renglon_prop in ($borrar_reng));";
	//borro los productos de producto_presupuesto
	$q.="delete from producto_presupuesto_new where id_producto_presupuesto in ";
	$q.="(select id_producto_presupuesto from producto_presupuesto_new ";
	$q.="where id_renglon_prop in ($borrar_reng));";
	//borro los renglones de renglon_presupuesto	
	$q.="delete from renglon_presupuesto_new where id_licitacion_prop=$id_lic_prop;";
	//borro el presupuesto de licitacion_presupuesto
	$q.="delete from licitacion_presupuesto_new where id_licitacion_prop=$id_lic_prop";
	}
	else
	//borro el presupuesto de licitacion_presupuesto
	$q="delete from licitacion_presupuesto_new where id_licitacion_prop=$id_lic_prop";
		
	sql($q) or fin_pagina();
	$db->CompleteTrans();
	return;//termino la ejecucion de este archivo
}

//ids de renglones de licitacion
$renglones=PostvartoArray("hidrenglon_");//sufijo $i
//print_r($renglones);
//echo "<br><br>";

//ids de los renglones de presupuesto borrados
//evito la primer coma
$borrar_reng=substr($_POST['hborrar_reng'],1);

//ids de los renglones de presupuesto borrados
//evito la primer coma
$borrar_prod=substr($_POST['hborrar_prod'],1);

//----------------------(1) BORRAR TODO LO QUE SE ELIMINO-------------------------------------
//borro de producto_presupuesto y producto_proveedor
//los productos que pertenecen a los renglones de presupuesto borrados
//borro tambien los renglones del presupuesto
if ($borrar_reng)
{
$q ="delete from producto_proveedor_new ";
$q.="where id_producto_presupuesto in "; 
$q.="(select id_producto_presupuesto from producto_presupuesto_new ";
$q.="where id_renglon_prop in ($borrar_reng)); "; 
$q.="delete from producto_presupuesto_new ";
$q.="where id_producto_presupuesto in "; 
$q.="(select id_producto_presupuesto from producto_presupuesto_new ";
$q.="where id_renglon_prop in ($borrar_reng)); "; 
$q.="delete from renglon_presupuesto_new where id_renglon_prop in ($borrar_reng); "; 
$q2[]=$q;
}

//borro de producto_presupuesto y producto_proveedor
//los productos que se borraron de los renglones
if ($borrar_prod)
{
	$q ="delete from producto_proveedor_new ";
	$q.="where id_producto_presupuesto in ($borrar_prod) "; 
	$q2[]=$q;
	
	$q ="delete from producto_presupuesto_new ";
	$q.="where id_producto_presupuesto in ($borrar_prod);"; 
	$q2[]=$q;
}


if ($q2)
	sql($q2) or fin_pagina();

//----------------------(2) ACTUALIZAR O INSERTAR --------------------------------------------

//para cada renglon de la licitacion
foreach ($renglones as $nro_renglon => $idrenglon)
{
	$cantidad=$_POST['cant_renglon_'.$nro_renglon];
	
	//si el renglon no esta en el presupuesto, debo insertarlo
	if (!$idrenglonp=$renglonesp[$nro_renglon])
	{
		$q="insert into renglon_presupuesto_new (id_licitacion_prop,id_renglon,cantidad) values ($id_lic_prop,$idrenglon,$cantidad);";
		$q.="select max(id_renglon_prop) as id_renglon from renglon_presupuesto_new ;";
		$r=sql($q) or fin_pagina();
		$idrenglonp=$r->fields['id_renglon'];
	}
	//sino actualizo
	else
	{
		$q="update renglon_presupuesto_new set cantidad=$cantidad where id_renglon_prop=$idrenglonp";
		$r=sql($q) or fin_pagina();
	}
	
	//Recupero los 
	//ids de productos para el renglon
	$idsproductos=PostvartoArray("hidproducto_".$nro_renglon."_");
	//print_r($idsproductos);echo "<br>";
	
	//inserto los productos del renglon que no estan en el presupuesto
	//SE SUPONE QUE HABRA AL MENOS UN PRODUCTO POR RENGLON
	foreach ($idsproductos as $nro_prod => $idprod )
	{
		$desc_orig=$_POST["hdescorig_".$nro_renglon."_".$nro_prod];
		$desc_adic=$_POST["hdescadic_".$nro_renglon."_".$nro_prod];
		$cantidad=$_POST["cantidad_prod_".$nro_renglon."_".$nro_prod];
		$adicional=$_POST["hadicional_".$nro_renglon."_".$nro_prod] or $adicional="null";
		$precio_prod=$_POST["precio_prod_".$nro_renglon."_".$nro_prod] or $precio_prod="null";
		//si no tiene id de producto_presupuesto debo
		//insertar el producto en el presupuesto
		if (!$idproductop=$_POST["producto_".$nro_renglon."_".$nro_prod])
		{
			$idprod_orig=$_POST["hidproducto_orig_".$nro_renglon."_".$nro_prod] or $idprod_orig=$idprod;
			
			//si se agrego un adicional
			if ($adicional!='null')
			{
				$sentmail=1;
				$contenido[$_POST["hrenglon_name_$nro_renglon"]].="<t>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</t>$desc_orig $desc_adic<br>";
			}
			
			$q ="insert into producto_presupuesto_new (id_renglon_prop,precio_presupuesto,desc_orig,desc_adic,id_producto,id_producto_orig,cantidad,adicional) ";
			$q.="values ($idrenglonp,$precio_prod,'$desc_orig','$desc_adic',$idprod,$idprod_orig,$cantidad,$adicional)  ";
			sql($q) or fin_pagina();
			
			$q="select max(id_producto_presupuesto) as id_producto_presupuesto from producto_presupuesto_new";
			$nuevoprod=sql($q) or fin_pagina();
			
			//reasigno el valor en POST para uso posterior
			$_POST["producto_".$nro_renglon."_".$nro_prod]=$nuevoprod->fields['id_producto_presupuesto'];
		}
		//actualizo la descripcion del producto
		else //if ($desc_adic!='')
		{
			$q ="update producto_presupuesto_new set ";
			$q.="desc_orig='$desc_orig', desc_adic='$desc_adic', id_producto=$idprod ";
			$q.="where id_producto_presupuesto=$idproductop";
			sql($q) or fin_pagina();
		}
	}
		
	if (is_array($proveedores))
	{
		//recupero los datos de los proveedores
		foreach ($proveedores as $colprov => $idprov)
		{
			foreach ($idsproductos as $nro_prod => $idprod)
			{
	
				$precio=$_POST["texto_precio_".$nro_renglon."_".$nro_prod."_".$colprov] or $precio=0;
				//para insertar la cantidad por proveedor y poder comprar a varios proveedores el mismo producto
				$cant_prov=$_POST["hcantidadprov_".$nro_renglon."_".$nro_prod."_".$colprov] or $cant_prov=$cantidad;
				$comentario=$_POST["hidden_comentario_".$nro_renglon."_".$nro_prod."_".$colprov];
				$elegido=$_POST["hidden_precio_elegido_".$nro_renglon."_".$nro_prod."_".$colprov] or $elegido=0;
				//id del producto_presupuesto
				$idprodp=$_POST["producto_".$nro_renglon."_".$nro_prod];
				
				//si hay que insertar el proveedor en la BD
				if ($_POST["hflagprov_$colprov"]=="insertar" || 
						//si es un renglon nuevo (no tiene id_renglon_prop)
						$renglonesp[$nro_renglon]=="" ||
						//si es un producto adicional y el proveedor existe
						$_POST["hflagprov_$colprov"]=="existe" && $_POST["hadicional_".$nro_renglon."_".$nro_prod])
				{
					//parte de Marcelo
					if ($precio > 0){
						$q="insert into log_modif_precio_presupuesto (fecha,monto,id_proveedor,id_producto_presupuesto,id_usuario)
						values ('".date("Y-m-d H:i:s")."',$precio,$idprov,$idprodp,".$_ses_user["id"].")";
						sql($q) or fin_pagina();
					}
					//fin parte de marcelo
					$q ="insert into producto_proveedor_new ";
					$q.="(id_proveedor,id_producto_presupuesto,monto_unitario,comentario,activo,cantidad) ";
					$q.="values ($idprov,$idprodp,$precio,'$comentario',$elegido,$cant_prov); ";
					sql($q) or fin_pagina();
				}
				//si el proveedor existe en la DB y hay que borrarlo
				//borrar
				elseif ($_POST["hflagprov_$colprov"]=="borrar")
				{
					$q ="delete from producto_proveedor_new ";
					$q.="where id_producto_presupuesto=$idprodp AND id_proveedor=$idprov";
					sql($q) or fin_pagina();
				}
				//si el proveedor existe en la BD y no hay que borrarlo
				//actualizar
				elseif ($_POST["hflagprov_$colprov"]=="existe")
				{
					//parte de Marcelo
					$q = "select monto_unitario from producto_proveedor_new where id_proveedor=$idprov and id_producto_presupuesto = $idprodp";
					$result = sql($q) or fin_pagina();
					if ($precio > 0 && $result->fields["monto_unitario"]!=$precio){
						$q="insert into log_modif_precio_presupuesto (fecha,monto,id_proveedor,id_producto_presupuesto,id_usuario)
						values ('".date("Y-m-d H:i:s")."',$precio,$idprov,$idprodp,".$_ses_user["id"].")";
						sql($q) or fin_pagina();
					}
					//fin parte de marcelo
					
					$q ="update producto_proveedor_new ";
					$q.="set monto_unitario=$precio,comentario='$comentario',activo=$elegido,cantidad=$cant_prov ";
					$q.="where id_proveedor=$idprov AND id_producto_presupuesto=$idprodp";
					sql($q) or fin_pagina();
					
				}
			}
		}
	}
}
if (!$db->CompleteTrans())
	die("<font color=red><b>Hubo un error al insertar/actualizar el presupuesto</b></font>");

if ($sentmail)
{
	//variables para enviar mail
	$mail_content ="<b>El usuario '{$_ses_user['name']}' agregó productos adicionales que no reemplazaron ningún producto ya existente.</b><br><br>";
	$mail_content.="Por favor verifique:<br> ";
	$mail_content.="Licitación: $id_lic - Seguimiento Nº: {$_POST['hnro_seg']} - Presupuesto: '$titulo'<br>";
	$mail_to= to_group(array("compras")).",adrian@coradir.com.ar,juanmanuel@coradir.com.ar";//juan adrian y todo compras
	foreach ($contenido as $renglon => $text )
			$mail_content.="<br>Renglon: $renglon <br> $text";
	enviar_mail_html($mail_to,"Se añadio un producto en el Presupuesto...",$mail_content,"","","",0);
//	echo $mail_content."<br>$mail_to";die;
}

?>