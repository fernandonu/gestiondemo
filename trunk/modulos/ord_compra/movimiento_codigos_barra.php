<?
/*
Autor: MAC
Fecha: 05/01/05

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.12 $
$Date: 2006/01/04 08:30:59 $

*/
require_once("../../config.php");

/********************************************************************
Este archivo se puede usar para distintos propositos, para agregar
logs de los movimientos que un producto con su respectivo codigo
de barras tiene, dentro de la empresa. Simplemente usar el parametro
que se le pasa a la pagina, de nombre: desde_pagina. LO UNICO QUE
SE DEBE MODIFICAR ES EN LA PARTE DE GUARDAR, EL SWITCH DE LA PAGINA
DESDE DONDE VIENE, PARA AGREGAR LO DESEADO.EL RESTO DEBE TOCARSE
PORQUE  SE USA EN VARIOS LADOS. CUIDADO!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
*********************************************************************/

$desde_pagina=$parametros["desde_pagina"] or $desde_pagina=$_POST["desde_pagina"];

//estas variables tienen que tomar los valores desde ord_compra_fin.php
$producto_nombre=$parametros["producto_nombre"] or $producto_nombre=$_POST["producto_nombre"];
$total_comprado=$parametros["total_comprado"] or $total_comprado=$_POST["total_comprado"];
$total_entregado=$parametros["total_entregado"] or $total_entregado=$_POST["total_entregado"];
$id_producto=$parametros["id_producto"] or $id_producto=$_POST["id_producto"];
$nro_orden=$parametros["nro_orden"] or $nro_orden=$_POST["nro_orden"];
$id_fila=$parametros["id_fila"] or $id_fila=$_POST["id_fila"];
$id_proveedor=$parametros["id_proveedor"] or $id_proveedor=$_POST["id_proveedor"];

if($_POST["guardar"]=="Guardar")
{$db->StartTrans();
 $fecha_hoy=date("Y-m-d H:i:s",mktime());
 $link_cb=encode_link("movimiento_codigos_barra.php",array("desde_pagina"=>"$desde_pagina","total_comprado"=>$_POST['total_comprado'],"total_entregado"=>$_POST["total_entregado"],"producto_nombre"=>$producto_nombre,"id_producto"=>$_POST['id_producto'],"id_proveedor"=>$id_proveedor,"nro_orden"=>$_POST['nro_orden'],"id_fila"=>$_POST['id_fila']));
 $cant_insertada=0;

if($desde_pagina=="lic_ord_compra_fin")
{ $id_dep_ent=$_POST["stock_entrega"];
 //traemos las recepciones y entregas hechas para esta OC, para esta fila,
 //asi podemos controlar el stock que elige. Si elige un stock X,
 //y se han recibido productos en otro stock que aun no han sido entregados,
 //damos error en la funcion de control
 $query="select id_deposito,cantidad,ent_rec from recibidos where id_fila=$id_fila";
 $recep=sql($query,"<br>Error al traer recepciones de la OC") or fin_pagina();

 //armamos un arreglo con el id_deposito como indice, y con las cantidades recibidas y entregadas
 //para ese deposito, como un sub arreglo. Esto nos sirve para controlar que no intenten entregar
 //desde un stock que no es el que tiene los productos reservados para esta OC.
 $ent_rec=array();
 while(!$recep->EOF)
 {
  if($ent_rec[$recep->fields["id_deposito"]]=="")
   $ent_rec[$recep->fields["id_deposito"]]=array();
  if($recep->fields["ent_rec"]==1)
  {$ent_rec[$recep->fields["id_deposito"]]["recibidos"]=$recep->fields["cantidad"];
  }
  elseif($recep->fields["ent_rec"]==0)
  {$ent_rec[$recep->fields["id_deposito"]]["entregados"]=$recep->fields["cantidad"];
  }

  $recep->MoveNext();
 }//de	while(!$recep->EOF)

 //si en el stock elegido no se ha recibido nada y hay recepciones en algun otro stock
 //para este producto, entonces avisamos y damos error
 if($ent_rec[$id_dep_ent]["recibidos"]=="")
 {//recorremos el arreglo $ent_rec para detectar si hay recepcion en algun otro stock

  $hay_recepcion=0;
  $recep->Move(0);
  while(!$hay_recepcion && !$recep->EOF)
  {
   if(($ent_rec[$recep->fields["id_deposito"]]["recibidos"]-$ent_rec[$recep->fields["id_deposito"]]["entregados"])>0)
   {$hay_recepcion=1;
    //traemos el nombre del deposito que tiene la reserva hecha
    $query="select nombre from depositos where id_deposito=".$recep->fields["id_deposito"];
    $dep_nbre=sql($query,"<br>Error al traer nombre del deposito de recepcion<br>") or fin_pagina();
   }

   $recep->MoveNext();
  }//de while(!$hay_recepcion && !$recep->EOF)

  if($hay_recepcion)
  {$error_cb="-----------------------------------------<BR>\n
                        El stock seleccionado no posee ninguna recepción del producto de esta fila.\n
                        Sin embargo, el stock ".$dep_nbre->fields["nombre"]." si poseee una recepción para esta fila.\n
                        Por favor, intente la entrega nuevamente con dicho Stock.\n
                        <BR>-----------------------------------------<BR><BR>\n
                        ";
  }//de if($hay_recepcion)

 }//de if($ent_rec[$id_dep_ent]["recibidos"])

}//de if($desde_pagina=="lic_ord_compra_fin")



 //registramos la entrega de los productos con el codigo de barra indicado en el campo de texto
 for($j=$_POST["primer_nuevo_cb"];!$error_cb && $j<$_POST["total_comprado"];$j++)
 {if($_POST['cod_barra_'.$j]!="" && $_POST['cod_barra_'.$j]!="Entregado sin CB")
  {
   //controlamos que el codigo de barras ingresado, exista. Si no existe, damos cartel de error
  $query="select codigo_barra,id_producto from codigos_barra where codigo_barra='".$_POST['cod_barra_'.$j]."'";
  $error_cb1="<BR>-----------------------------------------<BR>\n
                        ERROR AL BUSCAR CODIGO DE BARRAS.\n
                        <input type='button' value='Volver' onclick=\"document.location='$link_cb'\">\n
                        <BR>-----------------------------------------<BR><BR>\n
                        ";
  $hay=sql($query,$error_cb1) or fin_pagina();
  if($hay->fields["codigo_barra"]!="")
  {
  	//controlamos que el codigo de barras elegido sea de un producto como el que se quiere
  	//entregar (porque por ej: no podemos entregar un mother con codigo de barra X si lo que la OC
  	//compro es un monitor)
  	if($hay->fields["id_producto"]!=$id_producto)
  	{
  	 $error_cb="<font color=red><b>-----------------------------------------<BR>\n
                        El código de barras: '".$_POST['cod_barra_'.$j]."'<br>no está asociado al producto que se intenta entregar.<br>Controle el producto que esta entregando. Si tiene dudas comuniquese con la Division Software de Coradir.<br>\n
                        <BR>-----------------------------------------<BR><BR></b></font>\n
                        ";
  	 $cb_con_error=$_POST['cod_barra_'.$j];
  	}
  	if(!$error_cb)
  	{ //controlamos que el codigo de barras ingresado, no haya sido usado
  	  //para otra entrega.
  	  $query="select codigo_barra,nro_orden from log_codigos_barra where codigo_barra='".$_POST['cod_barra_'.$j]."' and tipo ilike '%entregado%'";
  	  $error_cb1="<br>-----------------------------------------<BR>\n
                        ERROR AL BUSCAR CODIGO DE BARRAS QUE FUE USADO.\n
                        <input type='button' value='Volver' onclick=\"document.location='$link_cb'\">\n
                        <BR>-----------------------------------------<BR><BR>\n
                        ";
  	  $usado=sql($query,$error_cb1) or fin_pagina();
  	  if($usado->fields["codigo_barra"]!="")
  	  {$error_cb="<font color=red><b>-----------------------------------------<BR>\n
                        El código de barras: '".$_POST['cod_barra_'.$j]."',<br> ya fue entregado mediante la Orden de Compra ".$usado->fields["nro_orden"].".<br>\n
                        <BR>-----------------------------------------<BR><BR></b></font>\n
                        ";
  	   $cb_con_error=$_POST['cod_barra_'.$j];
      }

      $rma="null";
      switch ($desde_pagina)
      {
     	case "lic_ord_compra_fin":
     	case "ord_compra_fin":$tipo="Producto entregado mediante la OC $nro_orden";

     	                      break;
      }
       if(!$error_cb)
       {$query="insert into log_codigos_barra(codigo_barra,usuario,fecha,tipo,nro_orden,id_info_rma)
              values('".$_POST['cod_barra_'.$j]."','".$_ses_user["name"]."','$fecha_hoy','$tipo',$nro_orden,$rma)";
        $error_cb1="<BR>-----------------------------------------<BR>\n
                          ERROR AL GUARDAR LOGS.\n
                          <input type='button' value='Volver' onclick=\"document.location='$link_cb'\">\n
                          <BR>-----------------------------------------<BR><BR>\n
                          ";
        sql($query,$error_cb1) or fin_pagina();

        $cant_insertada++;
       } //de if(!$error_cb)
  	  }//de if(!$error_cb)
     }//de if($hay->fields["codigo_barra"]!="")
     else
     {$error_cb="<font color=red><b>-----------------------------------------<BR>\n
                        El código de barras: '".$_POST['cod_barra_'.$j]."'<br>no está cargado en el sistema.<br>\n
                        <BR>-----------------------------------------<BR><BR></b></font>\n
                        ";
      $cb_con_error=$_POST['cod_barra_'.$j];
     }
  }//de if($_POST['cod_barra_'.$j]!="")
 }//de for($j=$_POST["primer_nuevo_cb"];$j<$_POST["total_comprado"];$j++)

 if(!$error_cb)
 {switch ($desde_pagina)
   {
   	case "lic_ord_compra_fin":
   	case "ord_compra_fin":
   	  require_once("fns.php");
   	  //una vez que se insertan los logs de los codigos de barra, insertamos o actualizamos
      //en la tabla recibidos, la cantidad entregada, y el log correspondiente
      $error_cb1="<BR>-----------------------------------------<BR>\n
                        ERROR AL TRAER INFO DE RECIBIDOS LOGS.\n
                        <input type='button' value='Volver' onclick=\"document.location='$link_cb'\">\n
                        <BR>-----------------------------------------<BR><BR>\n
                        ";
      if($desde_pagina=="lic_ord_compra_fin")
      {$id_dep_ent=$_POST["stock_entrega"];
       $query="select id_recibido,cantidad from recibidos where id_fila=$id_fila and ent_rec=0 and id_deposito=$id_dep_ent";
      }
      else
      {$id_dep_ent="null";
       $query="select id_recibido,cantidad from recibidos where id_fila=$id_fila and ent_rec=0";
      }
      $entrego=sql($query,$error_cb1) or fin_pagina();
      $id_recibido=$entrego->fields["id_recibido"];

      //controlamos que la cantidad entregada + la que se va a entregar, sea menor que la cantidad comprada
      $ya_entregado=($entrego->fields["cantidad"])?$entrego->fields["cantidad"]:0;
      if($ya_entregado+$cant_insertada>$total_comprado)
      {echo "Cantidad Comprada para esta fila: $total_comprado<br>Cantidad ya entregada para esta fila: $ya_entregado<br>Cantidad que se intenta entregar: $cant_insertada<br>";
       $error_cb1="<font color='red'><BR>-----------------------------------------<BR>\n
                        LA CANTIDAD ENTREGADA SUPERA A LA ORIGINALMENTE COMPRADA. NO SE PUEDE ENTREGAR ESTA CANTIDAD.<br><br>POSIBLEMENTE YA HA SIDO ENTREGADA LA TOTALIDAD DE ESTA FILA.<br>REVISE EL LOG DE ENTREGA CORRESPONDIENTE.<br>
                        <input type='button' value='Cerrar' onclick='window.opener.location.reload();window.close();'>\n
                        <BR>-----------------------------------------<BR><BR></font>\n
                        ";
       die($error_cb1);
      }

      if($id_recibido=="")
      {//insert
       $query="select nextval('recibidos_id_recibido_seq') as id_recibido";
       $id_rec=sql($query,"<br>Error al traer la secuencia del recibido") or fin_pagina();
       $id_recibido=$id_rec->fields["id_recibido"];
       $query="insert into recibidos(id_recibido,id_deposito,id_fila,cantidad,ent_rec)
       values($id_recibido,$id_dep_ent,$id_fila,$cant_insertada,0)";
      }
      else
      {//update
       if($entrego->fields["cantidad"]!="")
        $query="update recibidos set cantidad=cantidad+$cant_insertada where id_recibido=$id_recibido";
       else
        $query="update recibidos set cantidad=$cant_insertada where id_recibido=$id_recibido";

      }
      $error_cb1="<BR>-----------------------------------------<BR>\n
                        Error al insertar/actualizar los entregados.\n
                        <input type='button' value='Volver' onclick=\"document.location='$link_cb'\">\n
                        <BR>-----------------------------------------<BR><BR>\n
                        ";
      sql($query,$error_cb1) or fin_pagina();

      $query_ins="insert into log_recibido(id_recibido,usuario,fecha,cant)
            values($id_recibido,'".$_ses_user["name"]."','$fecha_hoy',$cant_insertada)";
      $error_cb1="<BR>-----------------------------------------<BR>\n
                        Error al agregar log de recibidos.\n
                        <input type='button' value='Volver' onclick=\"document.location='$link_cb'\">\n
                        <BR>-----------------------------------------<BR><BR>\n
                        ";
      sql($query_ins,$error_cb1) or fin_pagina();


    //descontamos los productos reservados que se hayan recibido
    if($desde_pagina=="ord_compra_fin")
    {
     //primero obtenemos el nombre del proveedor seleccionado
      $query="select razon_social from proveedor where id_proveedor=$id_proveedor";
      $iproveedor=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer el nombre del proveedor.");
      switch($iproveedor->fields['razon_social'])
      {case "Stock San Luis":$dep="San Luis";break;
       case "Stock Buenos Aires":$dep="Buenos Aires";break;
       case "Stock ANECTIS":$dep="ANECTIS";break;
       case "Stock SICSA":$dep="SICSA";break;
       case "Stock New Tree":$dep="New Tree";break;
       case "Stock Virtual":$dep="Virtual";break;
       case "Stock Serv. Tec. Bs. As.":$dep="Serv. Tec. Bs. As.";break;
      }

      //se selecciona el id del deposito
      $query="select id_deposito from depositos where nombre='$dep'";
      $id_dep=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer id del deposito");
    }

      //si se estan entregando productos recibidos para OC asociada a Licitacion, Presupuesto
      //RMA o Serv Tec, se usa el deposito elegido por el usuario, para descontar los productos
      if($desde_pagina=="lic_ord_compra_fin")
      { $id_dep_ent=$_POST["stock_entrega"];
        //descontamos los productos que se van entregando
        $cant_no_insertada=descontar_entregados_especial($nro_orden,$id_producto,$id_dep_ent,$cant_insertada,$id_fila);
        $cant_en_produccion=$cant_insertada-$cant_no_insertada;
      }
      elseif($desde_pagina=="ord_compra_fin")
      {
       //descontamos del stock seleccionado como proveedor
       //la cantidad de productos reservados de acuerdo a cuantos codigos de barra ingresaron
       //(descontamos los productos en la tabla reservados)
       descontar_entregados($nro_orden,$id_producto,$id_dep->fields['id_deposito'],$cant_insertada,$id_fila,$desde_pagina);
       $cant_en_produccion=$cant_insertada;
      }

      //revisamos si la OC esta asociada a licitacion o presupuesto, y de paso traemos el estado de la Licitacion
      $query="select orden_de_compra.id_licitacion,orden_de_compra.es_presupuesto,estado.nombre as estado
               from orden_de_compra left join licitacion using(id_licitacion) join estado using(id_estado)
                where nro_orden=$nro_orden";
      $tipo_oc=sql($query,"<br>Error al consultar el tipo de la OC<br>") or fin_pagina();
      $id_licitacion=$tipo_oc->fields["id_licitacion"];
      //si esta atada a licitacion o presupuesto, entonces agregamos el producto entregado, al stock de produccion
      //(esto solo si la licitacion no tiene estado Entregada)
      if($id_licitacion && $cant_en_produccion>0 && $tipo_oc->fields["estado"]!="Entregada")
       a_stock_produccion($nro_orden,$id_licitacion,$id_producto,$id_proveedor,$cant_en_produccion,$id_fila);

      break;
   }//de switch ($desde_pagina)
 }//de if(!$error_cb)


 if(!$error_cb)
 {$db->CompleteTrans();
  echo "<script>alert('Los códigos de barra entregados se cargaron con éxito');window.opener.location.reload();this.close();</script>";
 }
 else
  //esto obliga a hacer un rollback, aun si no ocurrieron errores
  $db->CompleteTrans(false);
}//DE if($_POST["guardar"]=="Guardar")

if($_POST["borrar"]=="Borrar")
{require_once("fns.php");
 $db->StartTrans();
 //borramos el codigo de barras que indica el hidden
 $a_borrar=$_POST["cb_a_borrar"];
  if($desde_pagina=="ord_compra_fin")
  {//obtenemos el nombre del proveedor seleccionado
   $query="select razon_social from proveedor where id_proveedor=$id_proveedor";
   $iproveedor=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer el nombre del proveedor.");
   switch($iproveedor->fields['razon_social'])
   {case "Stock San Luis":$dep="San Luis";break;
    case "Stock Buenos Aires":$dep="Buenos Aires";break;
    case "Stock ANECTIS":$dep="ANECTIS";break;
    case "Stock SICSA":$dep="SICSA";break;
    case "Stock New Tree":$dep="New Tree";break;
    case "Stock Virtual":$dep="Virtual";break;
    case "Stock Serv. Tec. Bs. As.":$dep="Serv. Tec. Bs. As.";break;
   }
   //se selecciona el id del deposito
   $query="select id_deposito from depositos where nombre='$dep'";
   $id_depo=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer id del deposito");
   $id_dep=$id_depo->fields["id_deposito"];
  }
  elseif($desde_pagina=="lic_ord_compra_fin")
    $id_dep=$_POST["stock_entrega"];
  //volvemos a poner como reservado, el producto bajo ese codigo de barras
  volver_a_reservar($nro_orden,$id_producto,$id_fila,$id_dep,$a_borrar,$desde_pagina);


   $error_cb_borrar="<BR>-----------------------------------------<BR>\n
                        NO SE PUDO BORRAR EL CÓDIGO DE BARRAS: $a_borrar.\n<br>
                        <input type='button' value='Volver' onclick=\"document.location='$link_cb'\">\n
                        <BR>-----------------------------------------<BR><BR>\n
                        ";
   $query="delete from log_codigos_barra where codigo_barra='$a_borrar' and tipo ilike '%Producto entregado%'";
   sql($query,"$error_cb_borrar") or fin_pagina();

   $db->CompleteTrans();
   echo "<script>alert('El codigo de barra $a_borrar fue borrado con éxito de las entregas de la OC $nro_orden');window.opener.location.reload();this.close();</script>";

}//de if($_POST["borrar"]=="Borrar")
echo $error_cb;
echo $html_header;

//traemos los codigos de barra ya cargados, y permitimos agregar los
//que faltan, si es que falta alguno.
if($nro_orden)
 $query="select codigo_barra from codigos_barra join log_codigos_barra using(codigo_barra) where id_producto=$id_producto and nro_orden=$nro_orden and tipo ilike'%entregado%'";
else
 die("Falta Nro Orden.");
$codigos_guardados=sql($query) or fin_pagina();

$cantidad_ingresar=$total_comprado - $total_entregado;
echo $msg;

?>
<script>
function alProximoInput(elmnt,content,next,index)
{
  if (content.length==elmnt.maxLength)
	{

	  if (typeof(next)!="undefined")
		{
		  next.focus();
		}
	  else
	   document.all.guardar.focus();

      if(typeof(boton=eval("document.all.autocompletar_consecutivos_"+index))!="undefined")
      {
         boton.style.visibility='visible';
      }

	}
}

function control_stock_elegido()
{
 if(document.all.stock_entrega.value==-1)
 {alert('Debe elegir un stock desde donde se entregarán los productos');
  return false;
 }

}

</script>
<script src="funciones.js"></script>
<form name="form1" method="POST" action="movimiento_codigos_barra.php">
 <input type="hidden" name="id_producto" value="<?=$id_producto?>">
 <input type="hidden" name="id_proveedor" value="<?=$id_proveedor?>">
 <input type="hidden" name="total_comprado" value="<?=$total_comprado?>">
 <input type="hidden" name="total_entregado" value="<?=$total_entregado?>">
 <input type="hidden" name="nro_orden" value="<?=$nro_orden?>">
 <input type="hidden" name="id_fila" value="<?=$id_fila?>">
 <input type="hidden" name="desde_pagina" value="<?=$desde_pagina?>">
 <input type="hidden" name="producto_nombre" value="<?=$producto_nombre?>">
 <?
  if($desde_pagina=="lic_ord_compra_fin")
  {$query="select nombre,id_deposito from depositos where tipo=0 order by nombre";
   $stocks=sql($query,"<br>Error al traer nombre de depositos<br>");

   //traemos los datos de recepcion o entrega, para saber a cuál deposito se debe hacer la entrega
   //solo en caso de que no haya ninguna de las dos cosas, el usuario podrá elegir a cual
   //stock descontarle las entregas
   $query="select id_recibido,cantidad,ent_rec,id_deposito,nombre from recibidos join depositos using(id_deposito) where id_fila=$id_fila order by ent_rec desc";
   $datos_ent_rec=sql($query ,"<br>Error al traer ent_rec de la fila $id_fila") or fin_pagina();
   if($datos_ent_rec->fields["id_deposito"]!="" && $datos_ent_rec->fields["cantidad"]>0)
   {$id_dep_select=$datos_ent_rec->fields["id_deposito"];
    $nbre_dep_select=$datos_ent_rec->fields["nombre"];
   }

  ?>
    <table align="center" width="95%" border="1">
     <tr>
      <td>
       <b>Seleccione el Stock desde donde se entregan los productos</b><br>
       <div align="center">
       <select name="stock_entrega">
       <?
        if($id_dep_select)
        {?>
         <option value="<?=$id_dep_select?>" selected>
           <?=$nbre_dep_select?>
         </option>
         <?
        }
        else
        {?>
         <option value="-1">Seleccione</option>
        <?
         while(!$stocks->EOF)
         {
          if($stocks->fields["id_deposito"]==$_POST["stock_entrega"])
           $selected_stock_entrega="selected";
          else
           $selected_stock_entrega="";
          ?>
          <option value="<?=$stocks->fields["id_deposito"]?>" <?=$selected_stock_entrega?>>
           <?=$stocks->fields["nombre"]?>
          </option>
          <?
         $stocks->MoveNext();
         }//de while(!$stocks->EOF)
        }//del else de  if($id_dep_select)
         ?>
        </select>
       </div>
      </td>
     </tr>
    </table>
    <br>
  <?
  }//de if($desde_pagina=="lic_ord_compra_fin")

  $title_gestion3="title='Esta funcionalidad no se utiliza más. En su reemplazo utilice el módulo: Pedido de Material'";
  ?>

 <table width="100%" align="center" border="1">
  <tr>
   <td id="ma">
    Ingrese los números de códigos de barra de los productos entregados.<br>Producto:<br>"<?=$producto_nombre?>"
   </td>
  </tr>
  <tr>
   <td>
    <b>Cantidad de Nº a ingresar: <?=$cantidad_ingresar?></b>
   </td>
  </tr>
  <tr>
   <td>
    <table width="100%">
  <?
  //primero mostramos los codigos de barra ya insertados antes en los
  //input, pero con readonly
  $io=0;
  while(!$codigos_guardados->EOF)
  {?>
  	<tr>
    <td>
     &nbsp;&nbsp;&nbsp;&nbsp;<!--para acomodar los campos correctamente-->
     <input type="text" name="cod_barra_<?=$io?>" maxlength="9" tabindex="<?=$io+1?>" size="30" readonly value="<?=$codigos_guardados->fields["codigo_barra"]?>" onkeyup="toUnicode(this,this.value,cod_barra_<?=$io+1?>);" >
     <input type="submit" name="borrar" value="Borrar" style="width:63" onclick="
  																			if(confirm('Se borrará el código de barra <?=$codigos_guardados->fields["codigo_barra"]?> de esta entrega.\n¿Está seguro?'))
  																			{document.all.cb_a_borrar.value='<?=$codigos_guardados->fields["codigo_barra"]?>';
  																			 return true;
  																			}
  																			else
  																			 return false;
  	"
  	<?=$title_gestion3?>
  	>
    </td>
   </tr>
   <?
   $io++;
   $codigos_guardados->MoveNext();
  }
  //guardamos el número a partir del cuál debemos empezar a insertar
  //los nuevos códigos de barra ingresados (el resto de los números
  //ya fueron ingresados antes)
  $foco=$io;
  ?>
  <input type="hidden" name="primer_nuevo_cb" value="<?=$io?>">
  <input type="hidden" name="cb_a_borrar" value="">
  <?
  $acomodo=$io;
  for($io;$io<$total_comprado;$io++)
  {//este if es para tomar en cuenta aquellos productos que se entregan sin codigo de barras
   //(con esto se pone la leyenda "Entregado sin CB" en tantos input para codigos de barra,
   //como productos se hayan entregado sin codigo de barra).
   if($io>=$cantidad_ingresar+$acomodo)
   {$entregado="Entregado sin CB";
    $entregado_disabled="disabled";
    $entregado_readonly="readonly";
    $estilo_error="";
   }
   else
   {if($_POST["cod_barra_$io"])
    {$entregado=$_POST["cod_barra_$io"];
     if($entregado==$cb_con_error)
      $estilo_error="style='color:red'";
     else
      $estilo_error="";
    }
   	else
   	{ $entregado="";
   	 $estilo_error="";
   	}
    $entregado_disabled="";
    $entregado_readonly="";
   }
   ?>
   <tr>
    <td>
     <?
     if($io==$total_comprado-1)
     {
      $third_par="document.all.guardar";

     }
     else
     {
      $third_par="cod_barra_".($io+1);
     }

     if($io<$total_comprado-1)
   	 {?>
      <input type="button" name="autocompletar_consecutivos_<?=$io?>" value="V" title="Autocompletar codigos de barra consecutivos" onclick="autocompletar_codigos_barra(document.all.cod_barra_<?=$io?>.value,'cod_barra_',<?=$io+1?>)" style="visibility:hidden">
     <?
   	 }
   	 else
   	  echo "&nbsp;&nbsp;&nbsp;&nbsp;";
     ?>
     <input type="text" maxlength="9" tabindex="<?=$io+1?>" name="cod_barra_<?=$io?>" value="<?=$entregado?>" <?=$estilo_error?> <?=$entregado_readonly?> size="30" onkeyup="alProximoInput(this,this.value,<?=$third_par?>,<?=$io?>);" >
     <input type="button" name="limpiar_<?=$io?>" value="Limpiar" <?=$entregado_disabled?> onclick="document.all.cod_barra_<?=$io?>.value=''">
    </td>
   </tr>
  <?
  }//for($io;$io<$total_comprado;$io++)
  ?>
  <input type="hidden" name="cant_vacios" value="<?=$io-$foco?>">
    </table>
   </td>
  </tr>
 </table>
 <table width="100%" align="center">
  <tr>
   <td align="center">
    <?
      if($desde_pagina=="lic_ord_compra_fin")
       $onclick_guardar="onclick='return control_stock_elegido()'";
      else
       $onclick_guardar="";
    ?>
    <input type="submit" name="guardar" value="Guardar" <?if($cantidad_ingresar<=0 || $total_entregado==$total_comprado) echo "disabled"?> <?=$onclick_guardar?> disabled <?=$title_gestion3?>>
   </td>
   <td align="center">
    <input type="button" name="cerrar" value="Cerrar" onclick="window.close()">
   </td>
  </tr>
 </table>
 <?if($cantidad_ingresar<=0 || $total_entregado==$total_comprado)
    echo "<h5>Todos los productos de esta fila fueron entregados</h5>";
 ?>

 <script>
  if(typeof(document.all.cod_barra_<?=$foco?>)!="undefined")
   document.all.cod_barra_<?=$foco?>.focus();
 </script>
</from>
</body>
</html>