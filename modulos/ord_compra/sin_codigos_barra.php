<?
/*
Autor: MAC
Fecha: 11/01/05

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.10 $
$Date: 2005/09/27 18:32:01 $

*/
require_once("../../config.php");



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
 $link_cb=encode_link("sin_codigos_barra.php",array("desde_pagina"=>"ord_compra_fin","total_comprado"=>$total_comprado,"total_entregado"=>$total_entregado,"producto_nombre"=>$producto_nombre,"id_producto"=>$_POST['id_producto'],"id_proveedor"=>$id_proveedor,"nro_orden"=>$_POST['nro_orden'],"id_fila"=>$_POST['id_fila'])); 
 $cant_entregada=$_POST["cant_entregar"];
 require_once("fns.php");
 $error_cb="";
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
   {$error_cb="<BR>-----------------------------------------<BR>\n
                         El stock seleccionado no posee ninguna recepción del producto de esta fila.\n
                         Sin embargo, el stock ".$dep_nbre->fields["nombre"]." si poseee una recepción para esta fila.\n
                         Por favor, intente la entrega nuevamente con dicho Stock.\n
                         <input type='button' value='Volver' onclick=\"document.location='$link_cb'\">\n
                         <BR>-----------------------------------------<BR><BR>\n
                         ";
    
   }//de if($hay_recepcion)
  }//de if($ent_rec[$id_dep_ent]["recibidos"])
 }//de if($desde_pagina=="lic_ord_compra_fin")
 else
  $id_dep_ent="null";
       
   	  //insertamos o actualizamos 
      //en la tabla recibidos, la cantidad entregada, y el log correspondiente
      $error_cb1="<BR>-----------------------------------------<BR>\n
                        ERROR AL TRAER INFO DE RECIBIDOS LOGS.\n
                        <input type='button' value='Volver' onclick=\"document.location='$link_cb'\">\n
                        <BR>-----------------------------------------<BR><BR>\n
                        ";
      $query="select id_recibido,cantidad from recibidos where id_fila=$id_fila and ent_rec=0";
      $entrego=sql($query,$error_cb1) or fin_pagina();
      $id_recibido=$entrego->fields["id_recibido"];
      
      //controlamos que la cantidad entregada + la que se va a entregar, sea menor que la cantidad comprada
      $ya_entregado=($entrego->fields["cantidad"])?$entrego->fields["cantidad"]:0;
      if($ya_entregado+$cant_entregada>$total_comprado)
      {echo "Cantidad Comprada para esta fila: $total_comprado<br>Cantidad entregada para esta fila: $ya_entregado<br>Cantidad que se intenta entregar: $cant_entregada<br>";
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
       values($id_recibido,$id_dep_ent,$id_fila,$cant_entregada,0)";
      }
      else
      {//update 
       if($entrego->fields["cantidad"]!="")
        $query="update recibidos set cantidad=cantidad+$cant_entregada where id_recibido=$id_recibido";
       else
        $query="update recibidos set cantidad=$cant_entregada where id_recibido=$id_recibido";
       }
       
       $error_cb1="<BR>-----------------------------------------<BR>\n
                        Error al insertar/actualizar los entregados.\n
                        <input type='button' value='Volver' onclick=\"document.location='$link_cb'\">\n
                        <BR>-----------------------------------------<BR><BR>\n
                        ";
       sql($query,$error_cb1) or fin_pagina();
   
       // esta consulta inserta en la tabla fila cuantas entregas se hicieron para la fila
       // sin cb y cuando se crea el remito se descuenta 
       // cuando se hace una entrega se vuelve a incrementar
       $scb_rm="update compras.fila set cant_scb_sr=cant_scb_sr+$cant_entregada where id_fila=$id_fila";
       sql($scb_rm,"Error al insertar la cantidad de entregas sin cb y sin remito interno") or fin_pagina();   
     
      
      $query_ins="insert into log_recibido(id_recibido,usuario,fecha,cant)
            values($id_recibido,'".$_ses_user["name"]."','$fecha_hoy',$cant_entregada)";
      $error_cb1="<BR>-----------------------------------------<BR>\n
                        Error al agregar log de recibidos.\n
                        <input type='button' value='Volver' onclick=\"document.location='$link_cb'\">\n
                        <BR>-----------------------------------------<BR><BR>\n
                        ";
      sql($query_ins,$error_cb1) or fin_pagina();
      
      //si se estan entregando productos recibidos para OC asociada a Licitacion, Presupuesto
      //RMA o Serv Tec, se usa el deposito elegido por el usuario, para descontar los productos
      if($desde_pagina=="lic_ord_compra_fin")
      { $id_dep_ent=$_POST["stock_entrega"];

        //descontamos los productos que se van entregando 
        $cant_no_insertada=descontar_entregados_especial($nro_orden,$id_producto,$id_dep_ent,$cant_entregada,$id_fila);
        $cant_en_produccion=$cant_entregada-$cant_no_insertada;
      }
      elseif($desde_pagina=="ord_compra_fin")
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

        //descontamos del stock seleccionado como proveedor
        //la cantidad de productos reservados de acuerdo a cuantos codigos de barra ingresaron
        //(descontamos los productos en la tabla reservados)
        descontar_entregados($nro_orden,$id_producto,$id_dep->fields['id_deposito'],$cant_entregada,$id_fila,$desde_pagina);
        $cant_en_produccion=$cant_entregada;
      }//de elseif($desde_pagina=="ord_compra_fin")
      
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
      

 if(!$error_cb)      
 {$db->CompleteTrans();
 }
 else 
 { //esto obliga a hacer un rollback, aun si no ocurrieron errores
  $db->CompleteTrans(false); 
 } 
 if(!$error_cb)
  echo "<script>alert('La entrega de la cantidad indicada, se cargó con éxito');window.opener.location.reload();this.close();</script>";
}//DE if($_POST["guardar"]=="Guardar")

echo $html_header;

$cantidad_ingresar=$total_comprado - $total_entregado;

?>
<script>
function control_cantidad()
{
  if(typeof(document.all.stock_entrega)!="undefined" && document.all.stock_entrega.value==-1)	
  {alert('Debe elegir un stock desde donde se entregarán los productos');
   return false;
  }	
  if(document.all.cant_entregar.value=="" || parseInt(document.all.cant_entregar.value)<1)	
  {alert('Debe ingresar una cantidad válida para entregar');
   return false
  } 
  if(parseInt(document.all.cant_entregar.value)><?=$cantidad_ingresar?>)
  {alert('La cantidad ingresada supera la cantidad máxima a entregar');
   return false
  }
  return true;
}	
</script>

<form name="form1" method="POST" action="sin_codigos_barra.php">
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
         {?>
          <option value="<?=$stocks->fields["id_deposito"]?>">
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
  ?>
    
 <table width="100%" align="center" border="1">
  <tr>
   <td id="ma">
    Ingrese la cantidad a entregar sin códigos de barra para el producto:<br>"<?=$producto_nombre?>"
   </td>
  </tr>
  <tr>
   <td>
    <b>Cantidad de Nº a ingresar: <?=$cantidad_ingresar?></b>
   </td>
  </tr>
  <tr>
   <td>
    Cantidad a entregar <input type="text" name="cant_entregar" size=8 onkeypress="return filtrar_teclas(event,'0123456789');">
   </td>
  </tr> 
 </table>
 <table width="100%" align="center">
  <tr>
   <td align="center">
    <input type="submit" name="guardar" value="Guardar" <?if($cantidad_ingresar<=0 || $total_entregado==$total_comprado) echo "disabled"?> onclick="return control_cantidad()">
   </td>
   <td align="center"> 
    <input type="button" name="cerrar" value="Cerrar" onclick="window.close()">
   </td>
  </tr> 
 </table> 
  <?if($cantidad_ingresar<=0 || $total_entregado==$total_comprado)
    echo "<h5>Todos los productos de esta fila fueron entregados</h5>";
 ?>
</from>
</body>
</html>