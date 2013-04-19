<?php
/*
$Creador:fernando $


$Author: fernando $
$Revision: 1.1 $
$Date: 2004/08/18 22:20:38 $
*/
//ya esta incluido el html header
// y algunas variables ya estan en la pagina que le incluyo
require_once("../../config.php");
echo $html_header;


$ID=$parametros["ID"];

$estilos_tabla="cellpading=0 cellspacin=0 border=1 class=border bgcolor=$bgcolor2";


if ($_POST["aceptar"]=="Aceptar"){
                 $db->StartTrans();
         	 $fecha_subido=date("Y-m-d"); //fecha actual
		 //$fecha_vencimiento=fecha_db($_POST["fecha_vencimiento"]);
                 $fecha_notificacion=fecha_db($_POST["fecha_notificacion"]);
                 $lugar_entrega=$_POST["lugar_entrega"];
                 $nro_orden=$_POST["nro_orden"];
                 $id_dias=$_POST["dias"];
                 $tipo_dias=$_POST["tipo_dias"];
                 $items=$_POST["items"];
                 $fecha_vencimiento=arma_fecha_venc($_POST["fecha_notificacion"],$id_dias,$tipo_dias);
                 $fecha_vencimiento=fecha_db($fecha_vencimiento);
                 /*
                 $sql="select nextval('subido_lic_oc_id_subir_seq') as id_subir ";
                 $res=sql($sql) or fin_pagina();
                 $id_subir=$res->fields["id_subir"];
                 */
                 //entrega estimada es ficticia

                 /*
        	 $sql_subir="insert into subido_lic_oc
                             (id_subir,id_licitacion,fecha_subido,id_entrega_estimada,vence_oc,
                              fecha_notificacion,lugar_entrega,nro_orden,id_dias,tipo_dias)
                              values
                              ($id_subir,$ID,'$fecha_subido',$id_ent,'$fecha_vencimiento',
                              '$fecha_notificacion','$lugar_entrega','$nro_orden',$id_dias,'$tipo_dias')";
                 */

                 //OJO
                 //Esto anda solamente cuando hay solo un subido_lic OC
                 $sql="select id_subir from subido_lic_oc where id_licitacion=$ID";
                 $res=sql($sql) or fin_pagina();
                 $id_subir=$res->fields["id_subir"];


                 $sql_subir="update subido_lic_oc set fecha_notificacion='$fecha_notificacion',
                                                    lugar_entrega='$lugar_entrega',
                                                    nro_orden='$nro_orden',
                                                    id_dias=$id_dias,
                                                    tipo_dias='$tipo_dias'
                                                    where id_subir=$id_subir
                                                    ";
                sql($sql_subir) or fin_pagina();

                 //ahora inserto los renglones que van a comprar
                 //print_r($_POST);
                 for ($i=0;$i<$items;$i++){

                         if ($_POST["items_$i"]==1) {
                                   $id_renglon=$_POST["id_renglon_$i"];
                                   $precio=$_POST["precio_$i"];
                                   $cantidad=$_POST["cant_$i"];
                                   $sql="insert into renglones_oc
                                         (id_subir,id_renglon,precio,cantidad)
                                         values
                                         ($id_subir,$id_renglon,$precio,$cantidad)
                                         ";
                                    sql($sql) or fin_pagina();
                        }
                 } //del for
 if ($db->CompleteTrans()) {
                            $exito=1;
                            $msg="Los cambios se efectuaron con éxito";
                            }
                           else {
                           $exito=0;
                           $msg="Error:No se pudo actualizar los datos";
                           }
} //del if
Aviso($msg);
?>
   <script>
   function borrar_renglones()
   {
    var i=0;
    var cant;
    var sentencias;
    var y=0;
    var j=0;
    var ejecutar;

    sentencias=new Array();
    bloquear=new Array();

    items_aux=parseInt(document.form1.items.value);

    //se fija si hay mas de un chekbox
    if (typeof(document.form1.chk)!='undefined'){
           i=0;
           j=0;
	   while (i < document.form1.chk.length)
		{
			if (document.form1.chk[i].checked)
				 {
				  y=i+1;
                                  bloquear[j]="document.form1.items_"+i+".value=0";
				  sentencias[j]="document.all.tabla_renglones.deleteRow("+ y +")";
                                  j++;
				 }
		 i++;
	         }//del while

           i=sentencias.length-1;
	   while(i>=0)
	      {
              eval(bloquear[i]);
	      eval(sentencias[i]);
	      i--;
              items_aux--;
	      }//del segundo while
     }//del if

     else{
      //se fija que haya al menos un renglon
     if (typeof(document.form1.chk)!='undefined'){
	if (document.form1.chk.checked)  //hay un renglon
         {
         ejecutar="typeof(document.form1.items_"+i+")";
         if (eval(ejecutar)!='undefined')
                                 {
                                 ejecutar="document.form1.items_"+i+".value=0";
                                 eval(ejecutar);
                                 }
	 document.all.tabla_renglones.deleteRow(1);
         items_aux--;
         } //del if
	}//del if
   }
   //document.form1.items.value=items_aux;
   }//del fin de la funcion que borra productos

  function control_datos(){

  var sen;
  var hay_orden;
  var items;

  if (document.form1.nro_orden.value==""){
             alert("Debe ingresar un nro de orden");
             return false;
         }


         if (document.form1.dias.options[document.form1.dias.selectedIndex].value==-1)
         {
             alert("Debe elegir un día");
             return false;
         }

         if (document.form1.tipo_dias.options[document.form1.tipo_dias.selectedIndex].value==-1)
         {
             alert("Debe elegir un tipo de día");
             return false;
         }
        if (document.form1.lugar_entrega.value=="")
         {
             alert("Debe ingresar un lugar de entrega");
             return false;
         }

  items=parseInt(document.form1.items.value);
   for(i=0;i<items;i++){
          //armo la sentencia
             sen="document.form1.items_"+i+".value==1";

             if (eval(sen)) {

                             sen="document.form1.cant_"+i+".value==''";
                             if (eval(sen)) {
                                    alert('Debe ingresar cantidad en renglon');
                                    return false;
                                    }
                             sen="document.form1.precio_"+i+".value==''";
                             if (eval(sen)) {
                                    alert('Debe ingresar precio en renglones');
                                    return false;
                                    }

                            }
         }//del for

  return true;
  }
  </script>
 <?
 //print_r($_POST);

     //traigo los datos de la licitacion
     $sql="select entidad.nombre,fecha_entrega,plazo_entrega,simbolo from
           licitacion join entidad using (id_entidad)
           join moneda using (id_moneda)
           where id_licitacion=$ID";
     $licitacion=sql($sql) or fin_pagina();

  $nombre_entidad=$licitacion->fields["nombre"];
  $simbolo=$licitacion->fields["simbolo"];
  $plazo_entrega=$_POST["plazo_entrega"] or $plazo_entrega=$licitacion->fields["plazo_entrega"];
  $fecha_notificacion=$_POST["fecha_notificacion"] or $fecha_notificacion=fecha($licitacion->fields["fecha_notificacion"]) or $fecha_notificacion=date("d/m/Y");
  $nro_orden=$_POST["nro_orden"] or $nro_orden=$licitacion->fields["nro_orden"];
  $lugar_entrega=$_POST["lugar_entrega"] or $lugar_entrega=$licitacion->fields["lugar_entrega"];
  $cantidad_dias=$_POST["dias"] or $cantidad_dias=$licitacion->fields["id_dias"];


 //si le paso $id_subir es que se va a abrir en una ventana aparte
 //entonces necesito mostrar el html_header y todos los campos deshabilitados
$link=encode_link("actualizar_lic_orden_de_compra.php",array("ID"=>$parametros["ID"]));
cargar_calendario();
 ?>
<form name=form1 method=post action="<?=$link?>">
<table width=100% align=Center <?=$estilos_tabla?>>
 <tr id=mo>
    <td>
      Nueva Orden de Compra de <?=$nombre_entidad?>
    </td>
 </tr>
 <tr>
    <td>
     <table width=100% align=center>
        <tr>
          <tr>
           <td width=40% align=left><b>OC N°:</b></td>
           <td><input type=text name=nro_orden value="<?=$nro_orden?>" size=35 <?=$readonly?>></td>
          </tr>
          <tr>
           <td width=40% align=left><b>Fecha de Notificación:</b></td>
           <td>
           <!-- default fecha de hoy -->
           <input type=text name="fecha_notificacion" value="<?=$fecha_notificacion?>" size=12 readonly>
           <?echo link_calendario("fecha_notificacion");?>
           </td>
        </tr>
        <tr>
           <td width=40% align=left><b>Vencimiento especificado por el cliente:</b></td>
           <td>
           <!-- default fecha entrega de la lic -->
           <?=$plazo_entrega?>
           </td>
        </tr>
        <tr>
           <td><b>Dias Vencimiento OC:</b></td>
           <td>
             <table width=100% align=center>
             <?
             $sql="select * from dias_oc where activo=1 order by dias";
             $dias=sql($sql) or fin_pagina();
             ?>
                  <tr>
                   <td>
                   <b>Días</b> &nbsp;
                   <select name=dias <?=$disabled?>>
                      <option value=-1>Elija una Opción</option>
                      <?

                      for($i=0;$i<$dias->recordcount();$i++){
                       if ($dias->fields["id_dias"]==$cantidad_dias) $selected="selected";
                                                               else  $selected="";
                      ?>
                      <option value="<?=$dias->fields["id_dias"]?>" <?=$selected?>><?=$dias->fields["dias"]?></option>
                      <?
                      $dias->movenext();
                      }
                      ?>
                   </select>
                   </td>
                   <td><b>Tipo</b>&nbsp;
                   <select name=tipo_dias <?=$disabled?>>
                      <option value=-1>Elija una Opción</option>
                      <option  <?if ($_POST["tipo_dias"]=="Hábiles") echo "selected"?>>Hábiles</option>
                      <option  <?if ($_POST["tipo_dias"]=="Corridos" || !$_POST["tipo_dias"]) echo "selected"?>>Corridos</option>
                   </select>
                   </td>
                   </tr>
              </table>
           </td>
        </tr>
        <tr>
          <td Colspan=2><b>Lugar de Entrega:</b></td>
        </tr>
        <tr>
        <td Colspan=2>
        <textarea name='lugar_entrega' rows=3 style="width:100%" <?=$readonly?>><?=$lugar_entrega?></textarea>
        </td>
        </tr>
     </table>
    </td>
 </tr>
 <tr>
 <?
 if (!$_POST["traer_renglones"]) $checked_oc="checked";
                      else{
                       if ($_POST["traer_renglones"]=="todos") $checked_todos="checked";
                                                          else $checked_oc="checked";
                      }
 ?>
 <td align=center>
   <table width=100% align=center>
    <tr>
        <td align=left width=50% id=ma_sf> Productos </td>
        <td>
          Todos
          <input type=radio class='estilos_check' name="traer_renglones" value="todos" <?=$checked_todos?>>
        </td>
        <td>
          Orden de Compra
          <input type=radio class='estilos_check' name="traer_renglones" value="orden_de_compra" <?=$checked_oc?>>
        </td>
        <td>
         <input type=submit name=traer_todos value="Traer" <?=$disabled?>>
        </td>
   </tr>
   </table>
  </td>
 </tr>
 <tr>
     <td>
         <!-- Tabla con los renglones-->
         <table width=100% id="tabla_renglones" align=center >
            <tr id=mo>
               <td width=1%>&nbsp;</td>
               <td>Renglon</td>
               <td>Cant.</td>
               <td>Descripción</td>
               <td>Precio</td>
               <td>Testigo</td>
            </tr>

            <!-- Parte Dinamica de los productos de orden de compra-->
            <?
            //LEER SI QUIEREN ENTENDER LA CONSULTA
            //$ID ya viene cuando incluyo la pagina
            //producto es la tabla de licitaciones
            //productos es la tabla de general
            //hay casos que producto.desc_gral esta vacio y tomo la desc_gral de productos de gral

            if ($exito) {
                 $sql="select renglon.id_renglon,renglon.codigo_renglon,renglon.cantidad,
                        renglon.titulo, renglones_oc.precio as precio,(renglon.cantidad*renglon.total) as precio_testigo
                        from  subido_lic_oc
                        join renglones_oc using(id_subir)
                        join renglon using(id_renglon)
                        where subido_lic_oc.id_subir=$id_subir
                        order by codigo_renglon";
            }
            else {

           if ($_POST["traer_renglones"]=="orden_de_compra" || !$_POST["traer_renglones"])
                     $estado_orden_compra=" and id_renglon in   (select id_renglon from historial_estados where id_estado_renglon=3)";
                     else
                     $estado_orden_compra="";

            $sql="
                 select renglon.id_renglon,renglon.codigo_renglon,renglon.cantidad,
                        renglon.titulo, (renglon.cantidad*renglon.total) as precio
                        from renglon where id_licitacion=$ID
                        $estado_orden_compra
                        order by codigo_renglon
                 ";
            }
            $resultado=sql($sql) or fin_pagina();
            $cantidad=$resultado->recordcount();
            ?>
            <input type=hidden name=items value="<?=$cantidad?>">
            <?
            for ($i=0;$i<$cantidad;$i++){
            ?>
               <input type=hidden name="items_<?=$i?>" value="1">
               <input type=hidden name="id_renglon_<?=$i?>" value="<?=$resultado->fields["id_renglon"]?>">
               <td><input type=checkbox name="chk" value="1"></td>
               <td>
               <input type=text name="codigo_<?=$i?>" value="<?=$resultado->fields["codigo_renglon"]?>" size=15 class="text_4" readonly>
               </td>
               <td>
                <input type=text name="cant_<?=$i?>" value="<?=$resultado->fields["cantidad"]?>" size=3 onkeypress="return filtrar_teclas(event,'0123456789');" <?=$readonly?>>
               </td>
               <td>
                  <input type=text name="descripcion_<?=$i?>" value="<?=$resultado->fields["titulo"]?>" readonly size=70 class="text_4" readonly>
               </td>
               <td>
                <input type=text name="precio_<?=$i?>" value="<?=number_format($resultado->fields["precio"],"2",".","")?>" size=8 onkeypress="return filtrar_teclas(event,'0123456789.');" <?=$readonly?>>
               </td>
               <td>
                   <table width=100% align=center>
                     <tr>
                         <td>
                         <b><?=$simbolo?></b>
                         </td>
                         <td>
                         <?
                         $precio_testigo=$resultado->fields["precio"];
                         ?>
                         <input type=text name="precio_testigo_<?=$i?>" value="<?=formato_money($precio_testigo)?>" readonly size=10 class="text_3">
                         </td>
                    </tr>
                  </table>
               </td>
               </tr>
            <?
            $resultado->movenext();
            }//del for
            ?>
            <!-- Fin de la parte dinamina -->
            <tr id=ma>
               <td colspan=6 align=left>
                      <input type=button  name="ordcompra_borrar" value="Borrar Renglones" style="width:20%" onclick="borrar_renglones();" <?=$disabled?>>
               </td>
            </tr>
         </table>
     </td>
 </tr>
   <tr>
     <td align=center>
       <input type=submit name=aceptar value=Aceptar onclick="return control_datos()">
       &nbsp;
       <input type=button name=cancelar value=Cancelar onclick="window.close()">
     </td>
   </tr>
 </table>
 </form>