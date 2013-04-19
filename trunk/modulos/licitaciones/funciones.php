<?php
/*
$Author: mari $
$Revision: 1.492 $
$Date: 2007/01/05 19:44:04 $
*/
require_once("../../config.php");
require_once("licitaciones_renglones_variables.php");

//elementos basicos para el configurador de la licitacion
//micropocesadores
//Kit
$estilos_select="style='width:100%;height:50px;'";

function aviso_duplicar($msg,$msg_destino){
    echo "<table width=100% align=center>
              <tr>
                 <td id=ma>Renglones Duplicados</td>
              </tr>
              <tr>
                 <td align=left>
                   $msg
                 </td>
              </tr>
              <tr>
                 <td>
                   $msg_destino
                 </td>
              </tr>

          </table>
         ";
}

//Copia en $id_licitacion_destino el renglon con id $id_renglon
function duplicar_renglon ($id_licitacion_destino,$id_renglon,$traer_descripciones=1){
  global $_ses_user,$db;

        $usuario_crea = $_ses_user['name'];
        $usuario_time = date("Y-m-d H:i:s");


        $db->StartTrans();

        //recupero los datos del renglon a duplicar
        $q_renglon=" select * from renglon where  id_renglon = $id_renglon";
        $res_r=sql($q_renglon) or fin_pagina();

        $id_licitacion_fuente=$res_r->fields["id_licitacion"];
        $titulo=$res_r->fields['titulo'];
        $renglon_original=$res_r->fields['codigo_renglon'];
        $renglon=$res_r->fields['codigo_renglon'].'  duplicado';
        $ganancia=$res_r->fields['ganancia'];
        $cantidad=$res_r->fields['cantidad'];
        $tipo=$res_r->fields['tipo'];
        $sin_descripcion=$res_r->fields['sin_descripcion'];

        if (!$sin_descripcion) $sin_descripcion=0;
        if ($res_r->fields['id_etap'])
               $id_etap=$res_r->fields['id_etap'];
               else
               $id_etap="NULL";
        $resumen=$res_r->fields['resumen'];
        ($res_r->fields['total'])?$total=$res_r->fields['total']:$total=0;


        //recupero el id de renglon a insertar
        $q_id_renglon= "SELECT nextval('renglon_id_renglon_seq') as id";
        $res_id_r= sql($q_id_renglon) or fin_pagina();
        $id_renglon_nuevo = $res_id_r->fields["id"];

        //inserto los datos del renglon duplicado
        $q_insert="insert into renglon
                      (id_renglon,id_licitacion,titulo,codigo_renglon,ganancia,cantidad,usuario,usuario_time,tipo,sin_descripcion,id_etap,resumen,total)
                      values ($id_renglon_nuevo,$id_licitacion_destino,'$titulo','$renglon',$ganancia,$cantidad,'$usuario_crea','$usuario_time','$tipo',$sin_descripcion,$id_etap,'$resumen',$total)";
        $res_nuevor= sql($q_insert)  or fin_pagina();

        //selecciono los productos del renglon a duplicar
        $q_prod="select * from licitaciones.producto where id_renglon=$id_renglon";
        $res_prod= sql($q_prod) or fin_pagina();
        while(!$res_prod->EOF) {
                $id_viejo=$res_prod->fields['id'];
                $tipo=$res_prod->fields['tipo'];
                $cantidad=$res_prod->fields['cantidad'];
                $desc_precio=$res_prod->fields['desc_precio_licitacion'];
                $desc_gral=$res_prod->fields['desc_gral'];

                if ($res_prod->fields['precio_licitacion'] !="")
                                $precio=$res_prod->fields['precio_licitacion'];
                                else
                                $precio="NULL";
                if ($res_prod->fields['id_producto'])
                        $id_producto=$res_prod->fields['id_producto'];
                        else
                        $id_producto="NULL";

                if ($res_prod->fields['id_proveedor'])
                       $id_proveedor=$res_prod->fields['id_proveedor'];
                       else $id_proveedor="NULL";

                if ($res_prod->fields['comentarios'])
                       $comentarios=$res_prod->fields['comentarios'];
                       else
                       $comentarios="";
               $sql=" select nextval('producto_id_seq') as id";
               $new_id=sql($sql) or fin_pagina();
               $id=$new_id->fields["id"];

               $q_insert_prod="insert into producto
                              (id,id_renglon,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion,desc_gral,comentarios)
                              values
                              ($id,'$id_renglon_nuevo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio','$desc_gral',";
               if ($comentarios) $q_insert_prod.="'$comentarios')" ;
                            else $q_insert_prod.="NULL)";
               sql($q_insert_prod)  or fin_pagina();


               if ($traer_descripciones){
                       $sql="select * from descripciones_renglon where id=$id_viejo and borrado=0";
                       $descripciones_renglon=sql($sql) or fin_pagina();

                       for($i=0;$i<$descripciones_renglon->recordcount();$i++){
                           $titulo_descripcion=$descripciones_renglon->fields["titulo"];
                           $contenido_descripcion=$descripciones_renglon->fields["contenido"];

                           $titulo_descripcion=ereg_replace("'","\'",$titulo_descripcion);
                           $contenido_descripcion=ereg_replace("'","\'",$contenido_descripcion);

                           $sql="insert into descripciones_renglon (id,titulo,contenido)
                                   values ($id,'$titulo_descripcion','$contenido_descripcion')";
                            sql($sql) or fin_pagina();

                           $descripciones_renglon->movenext();
                           }
              } //del if de traer descripciones
        $res_prod->MoveNext();
        }

        $db->CompleteTrans();
        $msg=" <b>Se Duplicó el renglon \"$renglon_original\" de la Licitación con ID:$id_licitacion_fuente</b><br>";


return $msg;
} //de la funcion duplicar renglon




function generar_desc_productos_renglon($resultado_renglon,$nro_renglon)
{

 global $bgcolor3,$bgcolor2,$bgcolor1,$impresora;
 global $bgcolor_out,$software,$maquina_adicional,$maquina_basica,$otro;

 $id_renglon=$resultado_renglon->fields["id_renglon"];
 $tipo=$resultado_renglon->fields["tipo"];

 $producto_orden=array();
 //elijo el filtro a utilizar

  switch(trim($tipo)){
            case 'Impresora':
                        $producto_orden=$impresora;
                        break;//de Impresora
            case 'Software':
                        $producto_orden=$software;
                        break;//Software
            case 'Otro':
                        $producto_orden=$otro;

                        breaK;//Otro
            default:
                     $producto_orden=array_merge($maquina_basica,$maquina_adicional);
                     //print_r($producto_orden);
                     break;
           }
   ?>
   <table  class='bordes' width='100%' align=center  id="tabla_<?=$id_renglon?>" >
      <tr oncontextmenu="chequea_radio(<?=$nro_renglon?>);">
         <td colspan=4>
           <table align=center width=100% height=100% valign="top" >
           <tr id='mo' >
             <td width='25%' align='left'>
             <b>Renglón:<?=$resultado_renglon->fields['codigo_renglon'];?></b>
             </td>
             <td><b>Título: <?=$resultado_renglon->fields['titulo'];?></b></td>
           <td width='15%'> <b>Cantidad: <?=$resultado_renglon->fields['cantidad']?>
           </td>
           </tr>
           </table>
          </td>
      </tr>
        <tr id='ma' oncontextmenu="chequea_radio(<?=$nro_renglon?>);">
        <td width='10%'><b>Cantidad</td>
        <td width='80%' ><b>Producto
         <?
         if ($id_etap=$resultado_renglon->fields['id_etap'] && $resultado_renglon->fields['titulo_etap']!='NO ETAPS')
                {
  		        $titulo_etap= $resultado_renglon->fields['titulo_etap'];
    	        $title=(($resultado_renglon->fields['texto_etap'])?"title='".$resultado_renglon->fields['texto_etap']."'":"");
		        echo "<font color=red style='cursor:hand' $title onclick=\"window.open('".encode_link('ETAPS.php',array('id_etap'=>$id_etap))."','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=600,height=325');\" >$titulo_etap</font>";
       	        }
         $title=(($resultado_renglon->fields['resumen'])?"title='".$resultado_renglon->fields['resumen']."'":"");
         if ($resultado_renglon->fields['resumen']){
         /*
         <?="$title"?>*/
         $link_resumen=encode_link('renglon_resumen.php',array('id_renglon'=>$id_renglon));
         ?>
         <font color=blue style='cursor:hand'  onclick="window.open('<?=$link_resumen?>')" > Resumen del Renglon</font>
         <!--&nbsp;<IMG src="../../imagenes/admiracion.gif">-->

         <?
         }
         ?>
         </td>
        <td width='10%' colspan=2><b>Precio U$S </td>
        </tr>
        <?
        $sql="select p.precio_licitacion,productos.desc_gral,
              p.precio_licitacion,p.desc_precio_licitacion,
              p.cantidad,p.comentarios,p.tipo,productos.id_producto
              from licitaciones.producto p
              join  general.productos using(id_producto)
              where p.id_renglon = $id_renglon order by p.comentarios desc";
        $desc_renglon=sql($sql) or fin_pagina();
        $cantidad_productos = $desc_renglon->RecordCount();
        $suma_productos=0;
        $total_producto=0;
        $size_filtro=sizeof($producto_orden);
        //echo "size filtro:$size_filtro";
        $k=0;
        $cantidad_productos_basicos=0;
        //muestro los productos basicos
        for($j=0;$j<$size_filtro;$j++){
          $tipo_producto=$producto_orden[$j]['tipo'];
          $desc_renglon->move(0);

          for($i=0;($desc_renglon->fields['comentarios']!='adicionales' && $desc_renglon->fields['tipo']!=$tipo_producto)  && $i<$cantidad_productos;$i++){
             $desc_renglon->movenext();
             }

           if ($desc_renglon->fields['tipo']==$tipo_producto && $desc_renglon->fields['comentarios']!='adicionales')
                   {
                   $cantidad_productos_basicos++;

                        if ($desc_renglon->fields['tipo']!='garantia')
                        {
                        ?>
                        <tr bgcolor="<?=$bgcolor_out?>" oncontextmenu="chequea_radio(<?=$nro_renglon?>);" onclick="alternar_color(this,'#a6c2fc')" >
                        <td align=center> <b><?=$desc_renglon->fields['cantidad'];?></b></td>
                        <td><b><?=$desc_renglon->fields['desc_gral'];?></b></td>
                        <?

                        if($desc_renglon->fields['desc_precio_licitacion']!=null || $desc_renglon->fields['desc_precio_licitacion']!="")
                            $color="bgcolor='#D2DDE3' ";
                            else
                            $color="";
                        $desc_aux=$desc_renglon->fields['desc_precio_licitacion'];
                        $id_aux=$resultado_renglon->fields['id_renglon'];
                        $prod_aux=$desc_renglon->fields['tipo'];
                        $cantidad=$desc_renglon->fields['cantidad'];
                        $precio=$desc_renglon->fields['precio_licitacion'];
                        $total_producto=$precio*$cantidad;
                        ?>
                        <td align='right'
                        <?
                        echo $color;
                        if($color!=""){?>title='<?=$desc_aux?>' onclick="window.open('<?=encode_link('renglon_comentario_mostrar.php',array('var'=>$desc_aux,'id'=>$id_aux,'producto'=>$prod_aux))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');"<?}?>>
                        <b><?echo number_format($total_producto,2,'.','');?></b></td>
                        <?
                        //generamos link para ver historial de comentarios
                        $link=encode_link("historial_comentarios.php",array("id_producto"=>$desc_renglon->fields['id_producto']));
                        ?>
                        <td><a href='<?=$link;?>' target="_blank">H</a></td>
                        </tr>
                        <?
                        }
             else {
                 $garantia=$desc_renglon->fields['desc_gral'];
                 $total_producto=0;
                 } //del else
         $suma_productos+=$total_producto;
        }
       }//del for con el arreglo
       //hago los que no son basicos


       $desc_renglon->move($cantidad_productos_basicos);

       if (!$desc_renglon->EOF && $cantidad_productos_basicos<$desc_renglon->recordcount()) {

       for($i=0;!$desc_renglon->EOF;$i++){
       ?>
                       <tr bgcolor='<?=$bgcolor_out?>' oncontextmenu="chequea_radio(<?=$nro_renglon?>);" onclick="alternar_color(this,'#a6c2fc')">
                        <td align=center bgcolor='#D2DDE3'> <b><?=$desc_renglon->fields['cantidad'];?></b></td>
                        <td><b><?=$desc_renglon->fields['desc_gral'];?></b></td>
                        <?
                        if($desc_renglon->fields['desc_precio_licitacion']!=null || $desc_renglon->fields['desc_precio_licitacion']!="")
                            $color="bgcolor='#D2DDE3' ";
                        else
                            $color="";
                        $desc_aux=$desc_renglon->fields['desc_precio_licitacion'];
                        $id_aux=$resultado_renglon->fields['id_renglon'];
                        $prod_aux=$desc_renglon->fields['tipo'];
                        $cantidad=$desc_renglon->fields['cantidad'];
                        $precio=$desc_renglon->fields['precio_licitacion'];
                        $total_producto=$precio*$cantidad;
                        ?>
                        <td align='right'
                        <?
                        echo $color;
                        if($color!=""){?>title='<?=$desc_aux?>' onclick="window.open('<?=encode_link('renglon_comentario_mostrar.php',array('var'=>$desc_aux,'id'=>$id_aux,'producto'=>$prod_aux))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');"<?}?>>
                        <b><?echo number_format($total_producto,2,'.','');?></b></td>
                        <?
                        //generamos link para ver historial de comentarios
                        $link=encode_link("historial_comentarios.php",array("id_producto"=>$desc_renglon->fields['id_producto']));
                        ?>
                        <td><a href='<?=$link;?>' target="_blank">H</a></td>
                        </tr>
         <?
       $desc_renglon->movenext();
       $suma_productos+=$total_producto;
       }//del for de adicionales

       }
         ?>
  <tr bgcolor="<?=$bgcolor_out?>" oncontextmenu="chequea_radio(<?=$nro_renglon?>);">
    <td ><b>Garantia:</td>
    <td colspan=3> <b><?=$garantia?></b></td>
 </tr>
 <tr id=ma_sf oncontextmenu="chequea_radio(<?=$nro_renglon?>);">
 <td ><font color='#FF0000'><b>Total</b></font></td>
 <td colspan='3' align='right'>
     <font color='#FF0000'> <b>
     U$S <?echo number_format($suma_productos,'2','.','');?>
     </font>
 </td>
 </tr>
 </table>
 <script>
  if (document.all && window.print) {
        document.all.tabla_<?=$id_renglon?>.oncontextmenu = showmenuie5;
 }
 </script>
 <br>
 <br>
<?
}
function informacion_usuario($id_licitacion){
 global $_ses_user;
  
 
 $sql="select id_usuario,login,nombre,apellido from usuarios where tipo_lic='P' or tipo_lic='L' 
              order by nombre";
 $res=sql($sql) or fin_pagina();

 $usuarios=array();
 for($i=0;$i<$res->recordcount();$i++){
         $usuarios[]=$res->fields["login"];
         $res->movenext();
 }
 

  $sql="select id_licitacion,entidad.nombre,fecha_apertura,ubicacion from licitacion
        join entidad using (id_entidad)
        join estado using (id_estado)
        where id_licitacion=$id_licitacion";
  $res=sql($sql) or fin_pagina();
  $fecha_apertura=$res->fields["fecha_apertura"];
  $ubicacion=$res->fields['ubicacion'];
  $cf=compara_fechas(substr($fecha_apertura,0,10),date("Y-m-d"));
  /*echo "cf :$cf <br>";
  echo " fecha aper ".substr($fecha_apertura,0,10)."<br>";
  echo " hoy:".date("Y-m-d")."ubicacion:$ubicacion";
  */
  if ((!in_array($_ses_user["login"],$usuarios)) && (($cf==1 || $cf==0) && ($ubicacion=='ACTUALES')))

        {

         $entidad=$res->fields["nombre"];
         $usuario_name=$_ses_user["name"];
         $fecha=date("Y-m-d H:i:s");
         $sql=" insert into realizar_oferta_log (id_licitacion,entidad,fecha_apertura,usuario,fecha_accedio)
                values ($id_licitacion,'$entidad','$fecha_apertura','$usuario_name','$fecha')";
         sql($sql) or fin_pagina();
        }
}


function setear_titulo($valor){
global $nbre_dist;

 switch ($valor)
   {
   case "Computadora Enterprise":
               $titulo="Computadora Personal CDR Modelo Enterprise";
               if($nbre_dist=="Buenos Aires - GCBA")
                       $titulo.=" Porteña";
                       elseif ($nbre_dist=="Federal")
                           $titulo.=" Argentina";
               break;
  case "Computadora Matrix":
                $titulo="Computadora Personal CDR Modelo Matrix";
		break;
  case "Impresora":
                $titulo="Titulo Impresora";
		break;

  case "Software":
                $titulo="Titulo Software";
		break;
  case "Otro":
                $titulo="Otro";
                break;
  default:
         $titulo="Computadora Personal CDR Modelo Enterprise";
         if($nbre_dist=="Buenos Aires - GCBA")
                 $titulo.=" Porteña";
                  elseif ($nbre_dist=="Federal")
                  $titulo.=" Argentina";
         break;
} //deñ switch
return $titulo;
}//de la funcion



function generar_descripcion_adicionales($id_renglon=-1){
global $cantidad_adicionales;

//cuando modifico los productos
$producto_cargado=0;
if ($id_renglon!=-1){
                     $i=1;
                     $sql="select pl.cantidad,pl.id_producto,pl.desc_precio_licitacion,
                                  pl.desc_gral,pl.precio_licitacion,pl.id ,tipo
                           from producto pl
                           where id_renglon=$id_renglon and comentarios='adicionales'";
                     $resultado=sql($sql) or fin_pagina();
                     while (!$resultado->EOF)
                      {
                      $producto_cargado=1;    
                      $link=encode_link("../productos/listado_productos.php",array("tipo"=>$tipo,"fila"=>$i,"onclick_cargar"=>"window.opener.agregar($i)","pagina_viene"=>"../licitaciones.licitaciones_renglones_ofertas.php"));
                      
                      ($resultado->fields['cantidad'])?$cantidad=$resultado->fields['cantidad']:$cantidad="1";
                      $id_producto=$resultado->fields['id_producto'];
                      $id=$resultado->fields["id"];
                      $tipo=$resultado->fields['tipo']; 
                      $desc_precio_licitacion=$resultado->fields["desc_precio_licitacion"];
                      $desc_gral=$resultado->fields['desc_gral'];
                      $precio_licitacion=$resultado->fields['precio_licitacion']; 
                      $tipo=$resultado->fields["tipo"];
                       ?>
                        <input type="hidden" name="producto<?=$i;?>" value=<?=$id_producto?>>
                        <input type="hidden" name="estado<?=$i;?>" value="0">
                        <input type="hidden" name="id<?=$i;?>" value="<?=$id?>"> 
                        <input type="hidden" name="desc_precio_<?=$i;?>" value="<?=$desc_precio_licitacion?>">
                        <input type="hidden" name="desc_precio_viejo_<?=$i; ?>" value="<?=$desc_precio_licitacion;?>">
                                              
                      <tr>
                        <td width="8%">
                          <input type="text" name="cantidad<?=$i;?>" size='5' value="<?=$cantidad?>">
                        </td>
                        <td>
                        <input type="text" name="tipo<?=$i;?>" style="width=100%" value="<?=$tipo?>" readonly>
                        </td>
                        <td>
                        <input type="text" name="descripcion<?=$i;?>" value="<?=$desc_gral?>" style="width=100%">
                        </td>
                        <td width="22%" align=center>
                        <input type='text' name="precio<?=$i;?>" size='15' value="<?=$precio_licitacion?>">
                        &nbsp;
                        <input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_'.$i,'id'=>$renglon,'producto'=>'Nuevo'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">
                        &nbsp;
                        <input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.producto<?=$i; ?>.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
                        </td>
                        <td>
                        <input type="button" name="boton<?=$i;?>" style='width=51' value="eliminar" onclick="document.all.desc_precio_<?=$i; ?>.value='';switch_func(<?=$i;?>,'<?=$link; ?>');">
                        </td>
                     </tr>
                     <?
                     $i++;
                     $resultado->MoveNext();
                      }
                      while ($i<=$cantidad_adicionales)
                      {
                      $link=encode_link("../productos/listado_productos.php",array("tipo"=>$tipo,"fila"=>$i,"onclick_cargar"=>"window.opener.agregar($i)","pagina_viene"=>"../licitaciones/licitaciones_renglones_oferta.php"));
                      ?>
                       <input type="hidden" name="id<?=$i;?>" value="<?=$id?>">
                       <input type="hidden" name="desc_precio_<?=$i;?>" value="<?=$resultado->fields["desc_precio_licitacion"];?>">                      
                       <input type="hidden" name="producto<?=$i;?>">
                       <input type="hidden" name="estado<?=$i;?>" value="4">
                        
                      <tr>
                       <td width="8%">
                        <input type="text" name="cantidad<?=$i;?>" value="<?="1";?>"size='5'>
                       </td>
                      <td>
                       <input type="text" name="tipo<?=$i;?>" value="" readonly>
                      </td> 
                      <td  >
                        <input type="text" name="descripcion<?=$i;?>" value="" style="width:100%">
                       </td>
                       <td align=center >
                     
                           <input type='text' name="precio<?=$i?>" size='15'>
                           <input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_'.$i,'id'=>$renglon,'producto'=>'Nuevo'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300,resizable=1');">
                           &nbsp;
                           <input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array("id_producto"=>""))?>&id_producto='+document.all.tipo<?=$i; ?>.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">
                       </td>
                       <td>
                       
                           <input type="button" value="agregar" style='width=51' name="boton<?=$i; ?>" onclick="document.all.desc_precio_<?=$i; ?>.value='';switch_func(<?=$i;?>,'<?=$link;?>');">
                       
                       </td>
                      </tr>
<?
                       $i++;
                       }
}  //del if ($id_renglon!=-1)
 else {
      //en este caso se considera un renglon nuevo
      for ($i=1;$i<=$cantidad_adicionales;$i++) {
              $link=encode_link("../productos/listado_productos.php",array("tipo"=>$tipo,"fila"=>$i,"onclick_cargar"=>"window.opener.agregar($i)","pagina_viene"=>"../licitaciones/licitaciones_renglones_oferta.php"));
?>
               <input type="hidden" name="estado<?=$i;?>" value="4"> 
               <input type="hidden" name="desc_precio_<?=$i;?>" value="">
               <input type="hidden" name="id<?=$i;?>" value="">
               <input type="hidden" name="producto<?=$i;?>">
               
                <tr>
                <td>
                 <input type="text" name="cantidad<?=$i;?>" size='5'>
                </td>
                <td>
                <input type="text" name="tipo<?=$i?>" value="" readonly> 
                </td>
                <td>
                <input type="text" name="descripcion<?=$i;?>" value="" style="width:100%">
                </td>
                  <td colspan=2>
               
                  <input type='text' name="precio<?=$i;?>" size='8'>

                  <input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_'.$i,'id'=>$renglon,'producto'=>'Nuevo'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300,resizable=1');">
                  &nbsp;
                  <input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
                  &nbsp;
                 
                  <input type="button" value="agregar" style='width=51' name="boton<?=$i;?>" onclick="document.all.desc_precio_<?=$i;?>.value='';switch_func(<?=$i;?>,'<?php echo $link; ?>');">
               </td>
              </tr>
<?
     }//del for
   }//del else
return $producto_cargado;
} //de la funcion generar_descripcion_adicional


function insertar_adicionales (){
 global $id_licitacion,$_ses_user,$id_proveedor,$cantidad_adicionales,$id_renglon;

 for($i=1;$i<=$cantidad_adicionales;$i++){
             if ($_POST["tipo".$i])
                                {
                                $id_producto=$_POST["producto$i"];    
                                $precio=$_POST["precio$i"];
                                $desc_precio=$_POST["desc_precio_$i"];
                                $cantidad = $_POST["cantidad$i"];
                                $descripcion = $_POST["descripcion$i"];
                                $tipo = $_POST["tipo$i"];

                                 $sql="select nextval('producto_id_seq') as new_id ";
                                 $resultados_new_id=sql($sql) or fin_pagina();
                                 $new_id=$resultados_new_id->fields["new_id"];

                                $sql="insert into producto
                                      (id,id_renglon,precio_licitacion,cantidad,id_producto,tipo,comentarios,desc_gral,desc_precio_licitacion)
                                      values
                                      ($new_id,$id_renglon,$precio,$cantidad,$id_producto,'$tipo','adicionales','$descripcion','$desc_precio')" ;
                                sql($sql) or fin_pagina();
                                if($desc_precio!="")
                                 {
                                  $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  sql($sql) or fin_pagina();
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  sql($sql) or fin_pagina();
                                 }

                                insertar_folletos($id_producto,$new_id,$id_licitacion);

                            } //del if
  }//del for

} //de la funcion insertar adicionales


function modificar_adicionales(){
 global $id_licitacion,$_ses_user,$id_proveedor,$cantidad_adicionales,$id_renglon;
///Esta es la parte que estaba en la modificacion
 for($i=1;$i<=$cantidad_adicionales;$i++)
    {

     switch($_POST["estado$i"])
     {
     //case 0 modifica
     case 0:
             $id_producto=$_POST["producto$i"];
             $id=$_POST["id$i"];
             $tipo=$_POST["tipo$i"];
             $descripcion=$_POST["descripcion$i"];
             $precio=$_POST["precio$i"];
             $desc_precio=$_POST["desc_precio_$i"];
             $cantidad = $_POST["cantidad$i"];
             $desc_precio_viejo = $_POST["desc_precio_viejo_$i"];
              //traigo descripcion de preciod e la bd y comparo con el que viene por post
             /*
             $sql="select desc_precio_licitacion from producto where id= $id";
             $result_desc_precio=sql($sql) or fin_pagina();
             //igual que los otros
              */
             $sql="update producto set 
                       desc_gral='$descripcion', tipo='$tipo', precio_licitacion=$precio,
                       cantidad=$cantidad,id_producto=$id_producto,
                       desc_precio_licitacion='$desc_precio'
                   where id=$id";
             sql($sql) or fin_pagina();

             if($desc_precio_viejo!=$desc_precio && $desc_precio!="")
                {
                 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                 sql($sql) or fin_pagina();
                 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                 sql($sql) or fin_pagina();
                }
         //inserto el folleto del nuevo producto
         insertar_folletos($id_producto,$id,$id_licitacion);
         break;
 //case 1 eliminar
 case 1:
         //Usar la funcion
         $id=$_POST["id$i"];
         eliminar_producto($id);
         break;
 //case2  insertar
 case 2:
         $id_producto=$_POST["producto$i"];
         $tipo=$_POST["tipo$i"];
         $precio=$_POST["precio$i"];
         $desc_precio=$_POST["desc_precio_$i"];
         $cantidad = $_POST["cantidad$i"];
         $descripcion=$_POST["descripcion$i"];
         $sql="select nextval('producto_id_seq') as new_id ";
         $resultados_new_id=sql($sql) or fin_pagina();
         $new_id=$resultados_new_id->fields["new_id"];

         $sql="insert into producto
                (id,id_renglon,tipo,id_producto,comentarios,precio_licitacion,cantidad,desc_gral,id_proveedor,desc_precio_licitacion)
                values
                ($new_id,$id_renglon,'$tipo',$id_producto,'adicionales',$precio,$cantidad,'$descripcion',$id_proveedor,'$desc_precio')" ;
         sql($sql) or fin_pagina();
         if($desc_precio!="")
     		{
       		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                 sql($sql) or fin_pagina();
                 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                 sql($sql) or fin_pagina();
                }
         //usar la funcion
         insertar_folletos($id_producto,$new_id,$id_licitacion);
         break;

 //case 3 eliminar e insertar
 case 3:
         $id_producto=$_POST["producto$i"];
         $id=$_POST["id$i"];
         eliminar_producto($id);

         $tipo=$_POST["tipo$i"];
         $precio=$_POST["precio$i"];
         $desc_precio=$_POST["desc_precio_$i"];
         $cantidad = $_POST["cantidad$i"];
         $descripcion=$_POST["descripcion$i"];

         $sql="select nextval('producto_id_seq') as new_id ";
         $resultados_new_id=sql($sql) or fin_pagina();
         $new_id=$resultados_new_id->fields["new_id"];

         $sql="insert into producto
                (id,id_renglon,tipo,id_producto,comentarios,precio_licitacion,cantidad,desc_gral,id_proveedor,desc_precio_licitacion)
                 values
                ($new_id,$id_renglon,'$tipo',$id_producto,'adicionales',$precio,$cantidad,'$descripcion',$id_proveedor,'$desc_precio')" ;
         sql($sql) or fin_pagina();
          if($desc_precio!="")
     	 	{
       		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                 sql($sql) or fin_pagina();
                 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                 sql($sql) or fin_pagina();
                }
         insertar_folletos($id_producto,$new_id,$id_licitacion);
         break;
        }  //del switch
  } //del for
} // de la funcion




/*****************************************************************************

 * @return void
 * @param $id es el id de PRODUCTO QUE DESEO BORRAR
 * @desc Genera Todo la logica de sql necesaria para eliminar los productos del renglon
          //hay que tener cuidado con los presupuestos
 ****************************************************************************/
function eliminar_producto($id)
 {
 global $db;

 $db->StartTrans();
 $sql="delete from papelera_descripciones where id=$id";
 sql($sql) or fin_pagina();
 $sql="delete from descripciones_renglon where id = $id";
 sql($sql) or fin_pagina();
 $sql="delete from producto where id= $id";
 sql($sql) or fin_pagina();
 $sql = "delete from archivos where id_producto = $id ";
 sql($sql) or  fin_pagina();
 $db->CompleteTrans();
 } //de la funcion eliminar producto

/*****************************************************************************
 * @return void
 * @param $id_producto es el id del productoque deseo obtner los folletos
 * @param $new_id es el id de la tabla producto que relaciono con los archivos
 * @desc Genera Todo la logica de sql necesaria para insertar los folletos de los productos
 ****************************************************************************/
function insertar_producto($tipo,$new_id,$id_renglon,$desc_gral,$tipo,$id_producto,$cantidad=0,$precio=0,$desc_precio=0)
 {


   $campos="id,id_renglon,desc_gral,tipo,id_producto";
   $values="$new_id,$id_renglon,'$desc_gral','$tipo',$id_producto";
   if ($tipo!="garantia" && $tipo!="conexos")
        {
         $campos.=",cantidad,precio_licitacion,desc_precio_licitacion";
         $values.=",$cantidad,$precio,'$desc_precio'";
        }
  if ($tipo=="conexos")
        {
        $campos.=",precio_licitacion,desc_precio_licitacion";
        $values.=",$precio,'$desc_precio'";
        }
  $sql="insert into producto ($campos) values ($values)" ;
  sql($sql) or fin_pagina();


}//de la funcion insertar producto

/*****************************************************************************
 * @return void
 * @param $id_producto es el id del productoque deseo obtner los folletos
 * @param $id es el id de la tabla producto que relaciono con los archivos
 * @desc Genera Todo la logica de sql necesaria para insertar los folletos de los productos
 ****************************************************************************/
function insertar_folletos($id_producto,$id,$id_licitacion){

 global $_ses_user;

 //para recuperar cual es el id del tipo_archivo = folleto
 $cons_id_tipo_arch="select id_tipo_archivo from licitaciones.tipo_archivo_licitacion
                     where tipo = 'Folletos' ";
 $res_id_tipo_arch=sql($cons_id_tipo_arch) or fin_pagina();
 $id_tipo_arch=$res_id_tipo_arch->fields['id_tipo_archivo'];

 $sql = "select * from folletos where id_producto = $id_producto";
 $resultado_fo= sql($sql) or fin_pagina();
 if ($resultado_fo->RecordCount()>0) {
               //significa que existe un archivo asociado
              //aca insertar en tabla archivos
             while (!$resultado_fo->EOF) {
                       //verifico que no se encuentren folletos ya para este producto
                       $sql="select count(idarchivo) as cantidad from archivos where id_licitacion=$id_licitacion and nombre = '".$resultado_fo->fields['nombre_ar']."'";
                       $res=sql($sql) or fin_pagina();
                       if ($res->fields["cantidad"]<=0) {
                                     $name=$resultado_fo->fields['nombre_ar'];
                                     $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                     $tamaño=$resultado_fo->fields['tamaño'];
                                     $tipo=$resultado_fo->fields['tipo'];
                                     $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                     $subidofecha=date("Y-m-d H:i:s", mktime());
                                     $subidousuario=$_ses_user['name'];
                                     //en el insert agregue 1 campo + q es el id del tipo archivo folleto
                                     $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto,id_tipo_archivo)
                                           values
                                           ($id_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id, $id_tipo_arch)";
		                             sql($sql) or fin_pagina();

                                     }//del control de cantidad
                       $resultado_fo->MoveNext();
		       }//del while
 } //del if de resultado_fo

} //de la funcion insertar folleto



/*****************************************************************************
 * @return void
 * @param descripcion ,arreglo que contiene lo que quiero modificar
 * @desc Genera Todo la logica de sql necesaria para modificar los datos
         de un renglon
 ****************************************************************************/
function modificar_renglon($descripcion,$id_renglon){
global $_ses_user,$id_proveedor,$id_licitacion;

 //print_r($descripcion);

$tamaño_arreglo=sizeof($descripcion);

    for($i=0;$i<$tamaño_arreglo;$i++){

        $flag=$_POST[$descripcion[$i]["flag"]];
        $flag=trim($flag);
        //echo "que trae el select:".$descripcion[$i]["select"].$_POST[$descripcion[$i]["select"]]."<br>";

        if ($_POST[$descripcion[$i]["select"]]!=0) {
                              //esto se puede ejecutar en otro lado
                              $id_producto=$_POST[$descripcion[$i]["select"]];
                              $sql="select codigo as tipo,desc_gral from 
                                           productos 
                                           join tipos_prod using(id_tipo_prod)
                                           where id_producto = $id_producto";
                              $resultado=sql($sql) or fin_pagina();
                              $tipo=$resultado->fields['tipo'];
                              $desc_gral=$resultado->fields['desc_gral'];
                              $precio=$_POST[$descripcion[$i]["precio"]];
                              $desc_precio=$_POST[$descripcion[$i]["descripcion_precio"]];
                              $cantidad=$_POST[$descripcion[$i]["cantidad"]];
                              if ($flag!="0") {

                                        $id=$flag;
                                        $id_producto_viejo=$_POST[$descripcion[$i]["idproducto"]];
                                        $id_producto_viejo=trim($id_producto_viejo);
                                        if ($id_producto_viejo!=$id_producto){
                                              //elimino los datos anteriores por que cambio la descripcion
                                              eliminar_producto($id);
                                              if ($_POST[$descripcion[$i]["descripcion_precio_viejo"]]!=$desc_precio && $desc_precio!="")
                                                  {
                                                  $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                                  sql($sql) or fin_pagina();
                                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                                  sql($sql) or fin_pagina();
                                                  }
                                             //hacer diferencia con si es garantia y conexo con el resto
                                             $sql="select nextval('producto_id_seq') as new_id ";
                                             $resultados_new_id=sql($sql) or fin_pagina();
                                             $new_id=$resultados_new_id->fields["new_id"];

                                             //inserto los productos

                                             insertar_producto($descripcion[$i]["tipo"],$new_id,$id_renglon,$desc_gral,$tipo,$id_producto,$cantidad,$precio,$desc_precio);
                                             //inserto los folletos
                                             insertar_folletos($id_producto,$new_id,$id_licitacion);
                                         } //de la comparacion de los id
                                         else
                                          {
                                              /*echo "entra por el else".$descripcion[$i]["flag"];
                                              die();*/
                                            //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                            //no cambio el producto que estaba solo haga un update por cantidad y precios
                                           if ($descripcion[$i]["tipo"]!="garantia") {
                                                   $valores=" precio_licitacion=$precio,desc_precio_licitacion='$desc_precio' ";
                                                   if ($descripcion[$i]["tipo"]!="conexos")
                                                                               $valores.=",cantidad=$cantidad";
                                                   $sql="update producto set $valores where id=$id";
                                                   sql($sql) or fin_pagina();
                                                   if ($_POST[$descripcion[$i]["descripcion_precio_viejo"]]!=$desc_precio && $desc_precio!="")
                                                           {
                                                            $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                                            sql($sql) or fin_pagina();
                                                            $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                                            sql($sql) or fin_pagina();
                                                            }
                                           }//del if que pregunta si es <> garantia
                                           }
                                  }//del then de flag_kit
                                else {
                                    //echo "entra por el else de flag2:<br>";
                                     //diferenciciar entre garantia y conexos
                                     $sql="select nextval('producto_id_seq') as new_id ";
                                     $resultados_new_id=sql($sql) or fin_pagina();
                                     $new_id=$resultados_new_id->fields["new_id"];
                                     //inserto el producto
                                     insertar_producto($descripcion[$i]["tipo"],$new_id,$id_renglon,$desc_gral,$tipo,$id_producto,$cantidad,$precio,$desc_precio);
                                     //inserto los folletos
                                     insertar_folletos($id_producto,$new_id,$id_licitacion);

                                     if($desc_precio!="")
                                              {
                                              $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   	          sql($sql) or fin_pagina();
                                              $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                              sql($sql) or fin_pagina();
                                              }

                                     //busco el folleto del producto seleccionado

			      } //del else de flag kit
                             //relacionado con el precio
                             if ($_POST[$descripcion[$i]["nuevo_precio"]]==1) {
																			$sql="update general.productos SET  precio_licitacion=$precio where id_producto = $id_producto ";
                                      sql($sql) or fin_pagina();
															}

             } //del if select kit
             else {
                if ($flag!="0") {
		        $id=$_POST[$descripcion[$i]["flag"]];
                        eliminar_producto($id);
	              }
       }

    }//del for

}//de la funcion modificar renglon



/*****************************************************************************
 * @return void
 * @param descripcion ,arreglo que contiene lo que quiero insertar
 * @desc Genera Todo la logica de sql necesaria para insertar los datos
         de un renglon
 ****************************************************************************/
function insertar_renglon($descripcion,$id_renglon){
global $_ses_user,$id_proveedor,$id_licitacion;

    $tamaño_arreglo=sizeof($descripcion);

    for($i=0;$i<$tamaño_arreglo;$i++){

       if ($_POST[$descripcion[$i]["select"]]!=0) {
                                 $id_producto=$_POST[$descripcion[$i]["select"]];
                                 $sql="select desc_gral,codigo  as tipo
                                             from productos 
                                             join tipos_prod using (id_tipo_prod)
                                             where id_producto = $id_producto";
                                 $resultado=sql($sql) or fin_pagina();
                                 $desc_gral=$resultado->fields['desc_gral'];
                                 $tipo=$resultado->fields['tipo'];

                                 $cantidad=$_POST[$descripcion[$i]["cantidad"]];
                                 $precio=$_POST[$descripcion[$i]["precio"]];
                                 $desc_precio=$_POST[$descripcion[$i]["descripcion_precio"]];

                                 $sql="select nextval('producto_id_seq') as new_id ";
                                 $resultados_new_id=sql($sql) or fin_pagina();
                                 $new_id=$resultados_new_id->fields["new_id"];

                                 $campos="id,id_renglon,desc_gral,tipo,id_producto";
                                 $values="$new_id,$id_renglon,'$desc_gral','$tipo',$id_producto";

                                 if ($descripcion[$i]["tipo"]!="garantia" && $descripcion[$i]["tipo"]!="conexos")
                                    {
                                     $campos.=",cantidad,precio_licitacion,desc_precio_licitacion";
                                     $values.=",$cantidad,$precio,'$desc_precio'";
                                    }
                                 if ($descripcion[$i]["tipo"]=="conexos")
                                     {
                                     $campos.=",precio_licitacion,desc_precio_licitacion";
                                     $values.=",$precio,'$desc_precio'";

                                     }
                                 $sql="insert into producto ($campos) values ($values)" ;

                                 sql($sql) or fin_pagina();
                                 if($desc_precio!="")
                                                 {
                                                  $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                                  sql($sql) or fin_pagina();
                                                  $sql="insert into historial_comentario_producto
                                                        (id_producto,fecha_comentario,comentario,id_usuario,actual)
                                                         values
                                                        ($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                                  sql($sql) or fin_pagina();
                                                  }
                                insertar_folletos($id_producto,$new_id,$id_licitacion);
                               //actualizo los precios de productos
                                if ($_POST[$descripcion[$i]["nuevo_precio"]]==1 && $descripcion[$i]["tipo"]!="garantia")
                                    {
                                              $sql="update general.productos SET  precio_licitacion=$precio where id_producto = $id_producto";
                                              sql($sql) or fin_pagina();
                                     } //del if
         } //del if
    }//del for                                                                                   S
}//fin de la funcion


/*****************************************************************************
 * generar_descripcion_renglon
 * @return void
 * @param descripcion ,arreglo que contiene lo que quiero generar
 * @param id_renglon si es distinto de -1 es que ya existe y hay que recuperarlo
 * @desc Genera Descripcion del renglon de las licitaciones
 ****************************************************************************/
function generar_descripcion_renglon($descripcion,$id_renglon=-1){
  global $estilos_select,$primera_vez,$id_licitacion;
   $tamaño_arreglo=sizeof($descripcion);
    //print_r($descripcion); 
   $producto_cargado=0;
   for($i=0;$i<$tamaño_arreglo;$i++){

       $tipo=$descripcion[$i]["tipo"];
       //traigo el que esta en la base de datos
       $precio_cargado="";
       $id_producto_cargado="";
       $id_cargado="";
       $precio_cargado="";
       $cantidad_cargado="";
       $desc_precio_licitacion="";

       //traigo los datos cargados en la base de datos
        if ($id_renglon!=-1){
            $sql="select pl.id_producto,pl.precio_licitacion,pl.cantidad,pl.id,
                         pl.desc_precio_licitacion,pl.desc_gral,codigo as tipo
                  from producto pl
                  join productos using(id_producto)
                  join tipos_prod using (id_tipo_prod)
                  where (id_renglon=$id_renglon and codigo='$tipo')
                  and (comentarios <> 'adicionales' or comentarios IS NULL)";

            $resultado_productos_cargados=sql($sql) or fin_pagina();
            //id_producto_cargado es el id de la tabla productos
            $id_producto_cargado=$resultado_productos_cargados->fields['id_producto'];
            //id_cargado es el id de la tabla producto
            $id_cargado=$resultado_productos_cargados->fields['id'];
            $precio_cargado=$resultado_productos_cargados->fields['precio_licitacion'];
            $cantidad_cargado=$resultado_productos_cargados->fields['cantidad'];
            $desc_precio_licitacion=$resultado_productos_cargados->fields["desc_precio_licitacion"];
            $desc_gral_cargado=$resultado_productos_cargados->fields['desc_gral'];
    //echo "Id Renglon:".$id_renglon;
  
     //estos hidden solo se utilizan cuando se modifica el renglon
    ?>
            <input type="hidden" name='<?=$descripcion[$i]["flag"]?>' value="<?if (!$id_cargado) echo "0";else echo $id_cargado;?>">
            <input type="hidden" name='<?=$descripcion[$i]["idproducto"]?>' value="<?if (!$id_producto_cargado) echo "0";else echo $id_producto_cargado;?>">
    <?
    }
    ?>
    <tr>
        <td>
        <?
        if ($descripcion[$i]["cantidad"]){//si no tiene cantidad no muestro nada
                    $value_cantidad=$cantidad_cargado or $value_cantidad=$_POST[$descripcion[$i]["cantidad"]];
                    if (!$value_cantidad) $value_cantidad=1;
                   ?>
                    <input type="text" name="<?=$descripcion[$i]["cantidad"]?>" value="<?=$value_cantidad?>" size="5">
                   <?
       }
       else echo "&nbsp";
       ?>
      </td>
      <td><?=$descripcion[$i]["descripcion"]?></td>
       <?
      //si es micro tiene un trato especial por que hay un script
      //de compatibilidad
      if ($descripcion[$i]["tipo"]=="micro")
                     $script_llamada_funciones="llamada_funciones(document.all.select_micro.options[document.all.select_micro.selectedIndex].value,20);";
                     else
                     $script_llamada_funciones="";

     if ($descripcion[$i]["tipo"]=="placa madre"){

                        if($id_renglon) $valor=10;
                                  else  $valor=20;
                        $script_compatibilidad_madre="<script>
                                                     llamada_funciones(document.all.select_micro.options[document.all.select_micro.selectedIndex].value,$valor);
                                                     </script>";

                        }
                         else  $script_compatibilidad_madre="";

     if ($descripcion[$i]["precio"]){
         $onchange="$script_llamada_funciones document.all.".$descripcion[$i]["precio"].".value=document.all.".$descripcion[$i]["select"].".options[document.all.".$descripcion[$i]["select"].".selectedIndex].id;";
         $onchange.=" document.all.".$descripcion[$i]["descripcion_precio"].".value='';";
         }
    ?>
    <td>
    <?
     if ($id_renglon && $descripcion[$i]['tipo']!='placa madre'){
     //esta consulta se hace para traer los productos para los selects
     $sql="select productos.id_producto,productos.desc_gral, productos.precio_licitacion as precio
            from general.productos
			join general.tipos_prod using (id_tipo_prod)
            where codigo='$tipo'
            order by desc_gral
            ";
     $resultado_productos = sql($sql) or fin_pagina();
    ?>

    <select name="<?=$descripcion[$i]["select"]?>" <?=$estilos_select; ?> onchange="<?=$onchange?>" onKeypress= "buscar_op(this)" onblur="borrar_buffer()" onclick= "borrar_buffer()">
    <option selected value=0 >Seleccione <?=$descripcion[$i]["descripcion"]?></option>
    <?

    $cantidad_productos=$resultado_productos->recordcount();
    for($j=0;$j<$cantidad_productos;$j++){

             $id_producto=$resultado_productos->fields['id_producto'];
             $desc_gral=$resultado_productos->fields['desc_gral'];
             $precio=number_format($resultado_productos->fields['precio'],"2",".","");

             if ($_POST[$descripcion[$i]["select"]]==$id_producto || $id_producto==$id_producto_cargado)
                                 $selected="selected";
                                 else
                                 $selected="";
             echo "<option value='$id_producto' id='$precio' $selected> $desc_gral </option>";
             $resultado_productos->Movenext();
    }
    ?>
    </select>
    <?
     }//del if si se modifica y es placa madre tiene un trato especial
    else{
    if ($id_producto_cargado){
        $sql="select desc_gral from productos where id_producto=$id_producto_cargado";
        $desc_placa_madre=sql($sql) or fin_pagina();
        $desc_gral=$desc_placa_madre->fields["desc_gral"];
    }
     ?>
    <select name="<?=$descripcion[$i]["select"]?>" <?=$estilos_select; ?> onchange="<?=$onchange?>">
    <option selected value=0 >Seleccione <?=$descripcion[$i]["descripcion"]?></option>
    <? if ($id_cargado) {?>
       <option selected value="<?=$id_producto_cargado?>" id="<?=$precio_cargado?>"> <?=$desc_gral?> </option>
    <?
    }
    ?>
    </select>
    <?
     }
    ?>
    </td>
    <?
    $desc_precio_actual=$desc_precio_licitacion or $desc_precio_actual=$_POST[$descripcion[$i]["descripcion_precio"]];
    $value_precio="";
    if ($precio_cargado!="")
             $value_precio=$precio_cargado;
    if ($_POST[$descripcion[$i]["precio"]])
             $value_precio=$_POST[$descripcion[$i]["precio"]];
    //$value_precio=$precio_cargado or $value_precio=$_POST[$descripcion[$i]["precio"]];
    //echo "desc precio actual:$desc_precio_licitacion<br>";
    //si no tiene precio no genero codigo para esto
    if ($descripcion[$i]["precio"]) {
    ?>
          <td>
           <input type="hidden" name="<?=$descripcion[$i]["descripcion_precio"]?>" value="<?=$desc_precio_actual?>">
           <input type="hidden" name="<?=$descripcion[$i]["descripcion_precio_viejo"]?>" value="<?=$desc_precio_licitacion?>">
           <input type="text" name="<?=$descripcion[$i]["precio"]?>" value="<?=$value_precio?>" size='17'>&nbsp;
           <input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>$descripcion[$i]["descripcion_precio"],'id'=>$renglon,'producto'=>$descripcion[$i]["tipo"]))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.<?=$descripcion[$i]["select"]?>.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
          </td>
          <td align="center">
            <input type="checkbox"  name="<?=$descripcion[$i]["nuevo_precio"]?>" value="1">
         </td>
    <?=$script_compatibilidad_madre?>
    <?
    }
    else {
    ?>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    <?
    }

    ?>
  </tr>
  
  <?
  
  //si hay precio entonces en la parte de adicionales  muestro  las tablas

  if (!$producto_cargado && $value_precio){
             $producto_cargado=1 ;       
             }
  }//del for
 
 return $producto_cargado;  
} //de la funcion generar_descripcion_renglon

/*****************************************************************************
 * make_options Gonzalo
 * @return void
 * @param id_renglon, es el renglon que deseo borrar
 * @desc Genera Descripcion del renglon de las licitaciones
 ****************************************************************************/

function kill_reng($id_renglon,$eliminar_renglon=1) {
 global $db;
 $db->StartTrans();
 //elimino los estados de los renglones
  $sql="select id_historial_renglon from historial_estados where id_renglon=$id_renglon";
  $resultado=sql($sql) or fin_pagina();
  while (!$resultado->EOF)
       {
       $sql="delete from log_estado_renglon where id_historial_renglon=".$resultado->fields["id_historial_renglon"];
       sql($sql) or fin_pagina();
       $resultado->movenext();
       }

  $q1="delete from oferta where id_renglon=$id_renglon";
  sql($sql) or fin_pagina();
  $sql="select * from producto where id_renglon=$id_renglon";
  $resultado_pro=sql($sql) or fin_pagina();
  while (!$resultado_pro->EOF)
   {
    $sql="delete from archivos where id_producto=".$resultado_pro->fields['id'];
    sql($sql) or fin_pagina();
    $resultado_pro->MoveNext();
   }
   $q2="delete from descripciones_renglon where id in ".
       "(select id from producto where id_renglon=$id_renglon)";
   sql($q2) or fin_pagina();

   $q2="delete from papelera_descripciones where id in ".
       "(select id from producto where id_renglon=$id_renglon)";
   sql($q2) or fin_pagina();


   $q3="delete from producto where id_renglon=$id_renglon";
   sql($q3) or fin_pagina();
   $sql="delete from log_renglon where id_renglon=$id_renglon";
   sql($sql) or fin_pagina();

   //primero selecciono las ofertas para no perderlas
   $sql="select * from elementos_oferta where id_renglon=$id_renglon";
   $renglones_ofertas=sql($sql) or fin_pagina();
   $filas_encontradas=$renglones_ofertas->RecordCount();
   $renglones_ofertas->MoveFirst();

   //ya las tengo selecciona ahora las elimino
   for($i=0;$i<$filas_encontradas;$i++){
       $id_oferta=$renglones_ofertas->fields['id_oferta'];
       $sql="delete from elementos_oferta where id_oferta=$id_oferta or id_renglon=$id_renglon";
       sql($sql) or fin_pagina();
       $renglones_ofertas->MoveNext();
       }

$renglones_ofertas->MoveFirst();
  for($i=0;$i<$filas_encontradas;$i++){
      $id_oferta=$renglones_ofertas->fields['id_oferta'];
      $sql="delete from oferta_licitacion where id_oferta=$id_oferta";
      sql($sql) or fin_pagina();
      $renglones_ofertas->MoveNext();
      }

/*fin de nueva delete*/
if ($eliminar_renglon){
    $q4="delete from renglon where id_renglon=$id_renglon";
    sql($q4) or fin_pagina();
}
//falta eliminar los archivos correspondientes a los folletos
$db->CompleteTrans();
}


/***************************************************************************
* genera_cotizacion_licitacion
* @result: devuelve nombre del archivo que genera
           si $id_licitacion=1305 el archivo generado:oferta_lic_1035.xls
* @param: $id_licitacion
* @desc: arma una variable buffer con los datos que contendra el archivo
         de oferta de la licitacion y luego invoca a la funcion genera_excel
         para generar el archivo.

**********************************************************************************/

function genera_cotizacion_licitacion($id_licitacion) {
global $db;
 //query para recuperar todos los datos de la licitacion.
  $query1="SELECT licitacion.*,
           firmantes_lic.nombre as firmante,dni, direccion, telefono,localidad           from licitacion
           left join firmantes_lic using(id_firmante_lic)
           join sucursales using(id_sucursal)
           WHERE id_licitacion=$id_licitacion";

  $resultados_lic=$db->Execute($query1) or die($query1);
  $redondear=$resultados_lic->fields["redondear"];
  //Saco todos los datos para mostrar en el excel
  $entidad=$resultados_lic->fields['id_entidad'];
  //$plazo_mantenimiento_oferta=$resultados_lic->fields['mantenimiento_oferta'];
  $plazo_mantenimiento_oferta=$resultados_lic->fields['mant_oferta_especial'];
  $plazo_mantenimiento_oferta_dias=$resultados_lic->fields['mantenimiento_oferta'];
  $plazo_entrega=$resultados_lic->fields['plazo_entrega'];
  $forma_pago=$resultados_lic->fields['forma_de_pago'];
  $lugar_entrega='';
  $nro_lic_cod=$resultados_lic->fields['nro_lic_codificado'];
  $fecha_apertura=$resultados_lic->fields['fecha_apertura'];
  $notas_adic=$resultados_lic->fields['notas_adicionales_lic'];
  
  //controlo que moneda se usa
  if ($resultados_lic->fields['id_moneda']==1){
                                           $moneda="\$";
                                           $valor_dolar=$resultado_lic->fields['valor_dolar_lic'];
                                           }
                                           else $moneda="U\$S";
  //query para seleccionar la entidad de la licitacion.

  $query2="SELECT * from entidad WHERE id_entidad=$entidad";
  $resultados_entidad=$db->Execute($query2) or die($query2);
  $entidad=$resultados_entidad->fields['nombre'];
  //query para traer de la BD el distrito
  $distrito=$resultados_entidad->fields['id_distrito'];
  $query_distrito="SELECT nombre from distrito WHERE id_distrito = $distrito ";
  $resultados_distrito=$db->Execute($query_distrito) or die($query_distrito);
  $buffer= "<html>";
  $buffer.= "<head>";
  $buffer.= "<SCRIPT language='JavaScript' src='funciones.js'></SCRIPT>";
  $buffer.="<style>
            .style0
            {mso-number-format:General;
             text-align:general;
             vertical-align:bottom;
             white-space:nowrap;
             mso-rotate:0;
             mso-background-source:auto;
             mso-pattern:auto;
             color:windowtext;
             font-size:10.0pt;
             font-weight:400;
             font-style:normal;
             text-decoration:none;
             font-family:Arial;
             mso-generic-font-family:auto;
             mso-font-charset:0;
             border:none;
             mso-protection:locked visible;
             mso-style-name:Normal;
             mso-style-id:0;}
             .xl30
             {mso-style-parent:style0;
             color:black;
             text-align:center;
             border:.5pt solid black;
             white-space:normal;}
             .xl31
             {mso-style-parent:style0;
             color:black;
             text-align:justify;
             border-top:none;
             border-right:.5pt solid black;
             border-bottom:.5pt solid black;
             border-left:none;
             white-space:normal;}
             .xl42
             {mso-style-parent:style0;
             color:black;
             text-align:121;
             border-top:none;
             border-right:.5pt solid black;
             border-bottom:.5pt solid black;
             border-left:none;
             white-space:normal;}
             .xl49
             {mso-style-parent:style0;
             color:black;
             text-align:justify;
             border:.5pt solid black;
             white-space:normal;}
             .x160
             {mso-style-parent:style0;
             color:black;
             text-align:justify;
             border:.6pt solid black;
             white-space:normal;}
             </style> ";

$buffer.= "<title>Untitled Document</title>";
$buffer.= "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>";
$buffer.= "</head>";
$buffer.= "<body bgcolor='' text='#000000'>";

$tipo_entidad=$resultados_entidad->fields['id_tipo_entidad'];
if($tipo_entidad==2) {
   $buffer.= "<table align='right'><tr><td></td></tr>
                  <tr><td></td></tr>
                  <tr><td></td></tr>
           </table>";
   $buffer.= "<table border='0' width='500' bordercolor='#000000' align='right'>";
   $buffer.= "<tr>";
   $buffer.= "<td align='center' colspan='6'>";
                  $buffer.="<b><font size='3'>X<br>";
                  $buffer.="DOCUMENTO NO VÁLIDO COMO FACTURA<br>";
                  $buffer.="</b></font>";
   $buffer.="</td>";
   $buffer.="</tr>";
   $buffer.= "</table>";
   $buffer.="<br>";
}
$buffer.= "<table align='right'><tr><td></td></tr>
                  <tr><td></td></tr>
                  <tr><td></td></tr>
           </table>";
$buffer.= "<table border='0' width='500px' bordercolor='#000000' align='right'>";
$buffer.= "<tr>";
$buffer.= "<td colspan='7'>";
$buffer.= "<b>$entidad</b>";
$buffer.="<br>";
$fecha_apertura1=Fecha($fecha_apertura);
$buffer.="<b> $nro_lic_cod</b>"."<br>"."<b>Apertura: $fecha_apertura1</b>";
$buffer.="</td>";
$buffer.= "</tr>";
$buffer.= "</table>";
$buffer.= "<br>";

if($tipo_entidad==1) {
   $buffer.= "<table border='0' width='500' bordercolor='#000000' align='right'>";
   $buffer.= "<tr>";
   $buffer.= "<td class='x160' colspan='7'>";

/*
   $sql="select firmantes_lic.nombre as firmante,dni, direccion, telefono,localidad from licitaciones.firmantes_lic
         join licitaciones.sucursales using (id_sucursal) where activo=1";
   $resultado_firmante=$db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);
   $firmante=$resultado_firmante->fields['firmante'];
   $dni=$resultado_firmante->fields['dni'];
   $calle=$resultado_firmante->fields['direccion'];
   $localidad=$resultado_firmante->fields['localidad'];
   $telefono=$resultado_firmante->fields['telefono'];
  */
   $firmante=$resultados_lic->fields['firmante'];
   $dni=$resultados_lic->fields['dni'];
   $calle=$resultados_lic->fields['direccion'];
   $localidad=$resultados_lic->fields['localidad'];
   $telefono=$resultados_lic->fields['telefono'];


                 $buffer.="El que suscribe <b>$firmante</b> Documento <b>$dni</b> en nombre y representación de la <br>";
                  $buffer.="Empresa Coradir S.A. con domicilio en la Calle <b>$calle</b> Localidad <b>$localidad</b> <br>";
                  $buffer.="Teléfono <b>$telefono</b> N° de CUIT 30-67338016-2  y con poder suficiente para obrar en su nombre, <br> ";
                  $buffer.="según consta en contrato poder que acompaña, luego de interiorizarse de las condiciones particulares <br> ";
                  $buffer.="y técnicas que rigen la presente compulsa, cotiza los siguientes precios:<br>";
   $buffer.="</td>";
   $buffer.="</tr>";
   $buffer.= "</table>";
   $buffer.="<br>";
}

$buffer.= "<br>";
$buffer.="<b><font size='3'>";
$buffer.="<center>PLANILLA DE OFERTA</center><br>";
$buffer.="</b></font>";
$buffer.= "<center>";
$buffer.= "<br>";
$buffer.= "<table border='0' width='500' bordercolor='#000000' align='right'>";
$buffer.= "<tr>";
$buffer.= "<td colspan='5'>&nbsp;</td>";
$buffer.= "<td colspan='2' bgcolor='#B0AEBB' style='border:.5pt solid black;'><b><center>Precio</center></b></td>";
$buffer.= "</tr>";
$buffer.="</table>";

$buffer.= "<table border='1' width='500' bordercolor='#000000' align='right'>";
$buffer.= "<tr bgcolor='#B0AEBB'>";
$buffer.= "<td width='1%'><center><b>Renglón</b></center></td>";
$buffer.= "<td align='center'><b>Cant</b></td>";
$buffer.= "<td colspan='3' align='center' width='1%'><b>Características y descripciones Técnicas</b></td>";
$buffer.= "<td><div align='center'> <b>Unitario</b> </div></td>";
$buffer.= "<td><div align='center'><b>Total</b></div> </td>";
$buffer.= "</tr>";

/*
se cambio la consulta de licitaciones y los productos por la de renglones que tiene todos los totales
s/c redondeo dependiendo de que se guardo
*/
$sql="select total, (total*cantidad) as total_renglon,titulo,sin_descripcion,codigo_renglon,cantidad 
from renglon where id_licitacion = $id_licitacion order by codigo_renglon";

$resultados_renglones=$db->execute($sql) or die($sql);
$filas_encontradas=$resultados_renglones->RecordCount();


while ($i<$filas_encontradas) {
    $buffer.= "<tr bordercolor='#000000'>";
   // $id_renglon=$resultados_renglones->fields['id_renglon'];
    $renglon=$resultados_renglones->fields['codigo_renglon'];
    $titulo=$resultados_renglones->fields['titulo'];
    $long=strlen($titulo);
    $cantidad=$resultados_renglones->fields['cantidad'];
   // $ganancia=$resultados_renglones->fields['ganancia'];
    $sin_descripcion=$resultados_renglones->fields['sin_descripcion'];
    $subtotal_renglon=$resultados_renglones->fields['total'];
    $total_renglon=$resultados_renglones->fields['total_renglon'];
    $subtotal_renglon=number_format($subtotal_renglon,2,',','.');
    $total_renglon=number_format($total_renglon,2,',','.');  
    //muestro segun el tipo de moneda que sea
    /*if ($moneda=="\$"){
                       $subtotal_renglon=($total_renglon * $resultados_lic->fields['valor_dolar_lic'])/$ganancia;
                       //$subtotal_renglon=ceil($subtotal_renglon);
                       //$subtotal_renglon=
                       $total_renglon=$cantidad*$subtotal_renglon;
                       $subtotal_renglon=number_format($subtotal_renglon,2,',','.');
                       $total_renglon=number_format($total_renglon,2,',','.');
                       }
                       else{
                       $subtotal_renglon=$total_renglon/$ganancia;
                       //$subtotal_renglon=ceil($subtotal_renglon);
                       $total_renglon=$subtotal_renglon*$cantidad;
                       $subtotal_renglon=number_format($subtotal_renglon,2,',','.');
                       $total_renglon=number_format($total_renglon,2,',','.');
                        }*/
     //if (!($sin_descripcion)) $titulo.="- Ver Características Técnicas Adjuntas";
    $renglon1=insertar_string($renglon,"<BR>",10);
    $buffer.= "<td class='130' align='center' valign='top'>$renglon1</td>";
    $buffer.= "<td align='center' valign='top'>$cantidad</td>";
    if (!($sin_descripcion)) $titulo.=" - Ver Características Técnicas Adjuntas";
    $titulo1=insertar_string($titulo,"<BR>",30);
    $buffer.= "<td colspan='3'><b>$titulo1</b></td>";
    //$buffer.= "<td colspan='3'><b>$titulo <br> $titulo2</b></td>";
    $buffer.= "<td class='xl42' width='74' valign='top'>$moneda $subtotal_renglon</td>";
    $buffer.= "<td class='xl42' width='74' valign='top'>$moneda  $total_renglon</td>";
    $buffer.= "</tr>";
    $resultados_renglones->Movenext();
  $i++;
}

//obtengo todas las ofertas para esta licitacion
$sql="select * from (licitaciones.licitacion join licitaciones.oferta_licitacion using (id_licitacion))";
$sql.=" where licitacion.id_licitacion = $id_licitacion order by oferta_licitacion.id_oferta";
$resultado_oferta=$db->execute($sql) or die ($sql."<br>".$db->ErrorMsg());
$filas_encontradas =$resultado_oferta->RecordCount();
$i=0;
while ($i<$filas_encontradas) {
   $buffer.="<tr>";
   $buffer.="<td align='right' colspan='5'><B>TOTAL OFERTA ".$resultado_oferta->fields['nombre']."</B></td>";
   $id_oferta = $resultado_oferta->fields['id_oferta'];
   $sql="select * from (licitaciones.renglon join licitaciones.elementos_oferta using (id_renglon))";
   $sql.="where elementos_oferta.id_oferta = $id_oferta";
   $resultado_renglones_oferta=$db->execute($sql) or die($sql);
   $filas_encontradas_renglones = $resultado_renglones_oferta->RecordCount();
   $j=0;
   $total=0;
      //en este while sumo los totales de la oferta
      while($j<$filas_encontradas_renglones) {
          $total+=$resultado_renglones_oferta->fields['total']*$resultado_renglones_oferta->fields['cantidad'];
          $j++;
          $resultado_renglones_oferta->MoveNext();
      }//del segundo while
  $buffer.="<td class='xl42' width='212' colspan='2'>";
  //$total=ceil($total);
  $total=number_format($total,2,',','.');
  $buffer.="<b>";
  $buffer.=$moneda."  ".$total;
  $buffer.="</td>";
  $buffer.="</tr>";
  $i++;
  $resultado_oferta->MoveNext();
  } //fin del while que calcula los totales de la oferta
$buffer.="</table>";
$buffer.="</center>";
//Parte nueva incluyo la funcion que me convierte de numeros a letras
$resultado_oferta->MoveFirst();
$filas_encontradas =$resultado_oferta->RecordCount();
$i=0;
$buffer.="<br>";
while ($i<$filas_encontradas) {
   $buffer.="<tr>";
   $buffer.="<td colspan='8'>";
   $id_oferta = $resultado_oferta->fields['id_oferta'];
   $sql="select * from (licitaciones.renglon join licitaciones.elementos_oferta on renglon.id_renglon = elementos_oferta.id_renglon and elementos_oferta.id_oferta = $id_oferta)";
   $resultado_renglones_oferta=$db->execute($sql) or die($sql);
   $filas_encontradas_renglones = $resultado_renglones_oferta->RecordCount();
   $j=0;
   $total=0;
     //en este while sumo los totales de la oferta
      while($j<$filas_encontradas_renglones) {
          $total+=$resultado_renglones_oferta->fields['total']*$resultado_renglones_oferta->fields['cantidad'];
          $j++;
          $resultado_renglones_oferta->MoveNext();
      }//del segundo while
   $buffer.="<b>";
   $buffer.="Total Oferta  ";
   $buffer.=$resultado_oferta->fields['nombre'];
   if ($moneda=="\$") $buffer.="  Son Pesos:  ";
                       else $buffer.=" Son Dolares: ";
   $total=number_format($total,2,'.','');                    
   list($entero,$decimal)=split('[.]',$total);
   $buffer.= NumerosALetras($entero);
   if (!$decimal) $decimal="00";
   $buffer.="  "."con $decimal/100";
   $buffer.="<br>";
   $buffer.="</td>";
   $buffer.="</tr>";
   $i++;
   $resultado_oferta->MoveNext();
   } //fin del while que calcula los totales de la oferta
$buffer.="<br>";
$buffer.="<b>Los precios incluyen I.V.A, flete, embalaje y seguros.</b><br>";
$buffer.="<br>";
$buffer.="Plazo de mantenimiento de oferta: "; if ($plazo_mantenimiento_oferta_dias!="") $buffer.="$plazo_mantenimiento_oferta_dias días $plazo_mantenimiento_oferta<br>"; else $buffer.="$plazo_mantenimiento_oferta<br>";
$buffer.="Plazo de entrega: $plazo_entrega<br>";
$buffer.="Forma de pago: $forma_pago<br>";
if ($notas_adic) {
$buffer.="<br> NOTA: ";
$notas_adic=ereg_replace("\r\n","<br>",$notas_adic);
$buffer.=$notas_adic;
$buffer.="<br>";
}
$buffer.="<br>";
$buffer.="</body>";
$buffer.="</html>";
//echo $buffer;die();
$nom="";
return genera_excel($buffer,$id_licitacion,$nom);
//echo $buffer;
} //final de la funcion que genera la cotizacion de la licitacion



/***************************************************************************
* genera_cotizacion_licitacion_cd
* @result: devuelve nombre del archivo que genera
           si $id_licitacion=1305 el archivo geenrado:oferta_cd_lic_1035
* @param: $id_licitacion
* @desc: arma una variable buffer con los datos que contendra el archivo
         de oferta de la licitacion y luego invoca a la funcion genera_excel
         para generar el archivo. ES el archivo que se muestra crear el cd de oferta

**********************************************************************************/
function genera_cotizacion_licitacion_cd($id_licitacion) {
global $db;

//query para recuperar todos los datos de la licitacion.
//$query1="SELECT * from licitacion WHERE id_licitacion=$id_licitacion";

  $query1="SELECT licitacion.*,
           firmantes_lic.nombre as firmante,dni, direccion, telefono,localidad           from licitacion
           left join firmantes_lic using(id_firmante_lic)
           join sucursales using(id_sucursal)
           WHERE id_licitacion=$id_licitacion";



$resultados_lic=$db->Execute($query1) or die($query1);
//Saco todos los datos para mostrar en el excel
$entidad=$resultados_lic->fields['id_entidad'];
//$plazo_mantenimiento_oferta=$resultados_lic->fields['mantenimiento_oferta'];
$plazo_mantenimiento_oferta=$resultados_lic->fields['mant_oferta_especial'];
$plazo_mantenimiento_oferta_dias=$resultados_lic->fields['mantenimiento_oferta'];
$plazo_entrega=$resultados_lic->fields['plazo_entrega'];
$forma_pago=$resultados_lic->fields['forma_de_pago'];
$lugar_entrega='';
$nro_lic_cod=$resultados_lic->fields['nro_lic_codificado'];
$fecha_apertura=$resultados_lic->fields['fecha_apertura'];
$notas_adic=$resultados_lic->fields['notas_adicionales_lic'];
//controlo que moneda se usa

if ($resultados_lic->fields['id_moneda']==1){
                                           $moneda="\$";
                                           $valor_dolar=$resultado_lic->fields['valor_dolar_lic'];
                                           }
                                           else $moneda="U\$S";



//query para seleccionar la entidad de la licitacion.

$query2="SELECT * from entidad WHERE id_entidad=$entidad";
$resultados_entidad=$db->Execute($query2) or die($query2);
$entidad=$resultados_entidad->fields['nombre'];
//query para traer de la BD el distrito
$distrito=$resultados_entidad->fields['id_distrito'];
$query_distrito="SELECT nombre from distrito WHERE id_distrito = $distrito ";
$resultados_distrito=$db->Execute($query_distrito) or die($query_distrito);
$buffer= "<html>";
$buffer.= "<head>";
$buffer.= "<SCRIPT language='JavaScript' src='funciones.js'></SCRIPT>";
$buffer.="<style>
.style0
    {mso-number-format:General;
    text-align:general;
    vertical-align:bottom;
    white-space:nowrap;
    mso-rotate:0;
    mso-background-source:auto;
    mso-pattern:auto;
    color:windowtext;
    font-size:10.0pt;
    font-weight:400;
    font-style:normal;
    text-decoration:none;
    font-family:Arial;
    mso-generic-font-family:auto;
    mso-font-charset:0;
    border:none;
    mso-protection:locked visible;
    mso-style-name:Normal;
    mso-style-id:0;}

.xl30
    {mso-style-parent:style0;
    color:black;
    text-align:center;
    border:.5pt solid black;
    white-space:normal;}

.xl31
    {mso-style-parent:style0;
    color:black;
    text-align:justify;
    border-top:none;
    border-right:.5pt solid black;
    border-bottom:.5pt solid black;
    border-left:none;
    white-space:normal;}



.xl42
    {mso-style-parent:style0;
    color:black;
    text-align:121;
    border-top:none;
    border-right:.5pt solid black;
    border-bottom:.5pt solid black;
    border-left:none;
    white-space:normal;}




.xl49
    {mso-style-parent:style0;
    color:black;
    text-align:justify;
    border:.5pt solid black;
    white-space:normal;}
.x160
    {mso-style-parent:style0;
    color:black;
    text-align:justify;
    border:.6pt solid black;
    white-space:normal;}


</style> ";

$buffer.= "<title>Untitled Document</title>";
$buffer.= "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>";
$buffer.= "</head>";
$buffer.= "<body bgcolor='' text='#000000'>";
$tipo_entidad=$resultados_entidad->fields['id_tipo_entidad'];
if($tipo_entidad==2) {
   $buffer.= "<table border='0' width='775' bordercolor='#000000'>";
   $buffer.= "<tr>";
   $buffer.= "<td align='center' colspan='7'>";
                  $buffer.="<b><font size='3'>X<br>";
                  $buffer.="DOCUMENTO NO VÁLIDO COMO FACTURA<br>";
                  $buffer.="</b></font>";
   $buffer.="</td>";
   $buffer.="</tr>";
   $buffer.= "</table>";
   $buffer.="<br>";
}

$buffer.= "<table border='0' width='775' bordercolor='#000000'>";
$buffer.= "<tr>";
$buffer.= "<td colspan='7'>";
$buffer.= "<b>$entidad</b>";
$buffer.="<br>";
$fecha_apertura1=Fecha($fecha_apertura);
$buffer.="<b>Nro: $nro_lic_cod</b>"."<br>"."<b>Apertura: $fecha_apertura1</b>";
$buffer.="</td>";
$buffer.= "</tr>";
$buffer.= "</table>";
$buffer.= "<br>";

if($tipo_entidad==1) {
   $buffer.= "<table border='0' width='775' bordercolor='#000000'>";
   $buffer.= "<tr>";
   $buffer.= "<td class='x160' colspan='8'>";

   $firmante=$resultados_lic->fields['firmante'];
   $dni=$resultados_lic->fields['dni'];
   $calle=$resultados_lic->fields['direccion'];
   $localidad=$resultados_lic->fields['localidad'];
   $telefono=$resultados_lic->fields['telefono'];


       $buffer.="El que suscribe <b>$firmante</b> Documento <b>$dni</b> en nombre y representación de la Empresa Coradir S.A.<br>";
       $buffer.="con domicilio en la Calle <b>$calle</b> Localidad <b>$localidad</b> Teléfono <b>$telefono</b> N° de CUIT 30-67338016-2<br>";
       $buffer.="y con poder suficiente para obrar en su nombre, según consta en contrato poder que acompaña,<br>";
       $buffer.="luego de interiorizarse de las condiciones particulares y técnicas que rigen la presente  <br>";
       $buffer.="compulsa, cotiza los siguientes precios:<br>";

   $buffer.="</td>";
   $buffer.="</tr>";
   $buffer.= "</table>";
   $buffer.="<br>";
}

$buffer.= "<br>";
$buffer.="<b><font size='3'>";
$buffer.="<center>PLANILLA DE OFERTA</center></font>";
$buffer.="</b><br>";
$buffer.= "<center>";
$buffer.= "<br>";
$buffer.= "<table border='0' width='785' bordercolor='#000000'>";
$buffer.= "<tr>";
$buffer.= "<td colspan='6'>&nbsp;</td>";
$buffer.= "<td colspan='2' bgcolor='#B0AEBB' style='border:.5pt solid black;' ><b><center>Precio</center></b></td>";
$buffer.= "</tr>";
$buffer.="</table>";
$espacio="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$buffer.= "<table border='1' width='785' bordercolor='#000000'>";
$buffer.= "<tr bgcolor='#B0AEBB'>";
$buffer.= "<td><center><b>Renglón</b></center></td>";
$buffer.= "<td align='center'><b>Cant</b></td>";
$buffer.= "<td colspan='4'><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Características y descripciones Técnicas &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>";
$buffer.= "<td ><div align='center'> <b>Unitario</b> </div></td>";
$buffer.= "<td ><div align='center'><b>Total</b></div> </td>";
$buffer.= "</tr>";

$sql="select total,(total*cantidad) as total_renglon,titulo,sin_descripcion,codigo_renglon,cantidad
from renglon where id_licitacion = $id_licitacion order by codigo_renglon";

$resultados_renglones=$db->execute($sql) or die($sql);
$filas_encontradas=$resultados_renglones->RecordCount();

while ($i<$filas_encontradas) {
    $buffer.= "<tr bordercolor='#000000'>";
    $renglon=$resultados_renglones->fields['codigo_renglon'];
    $titulo=$resultados_renglones->fields['titulo'];
    $cantidad=$resultados_renglones->fields['cantidad'];
    $sin_descripcion=$resultados_renglones->fields['sin_descripcion'];
    
    
    $subtotal_renglon=$resultados_renglones->fields['total'];
    $total_renglon=$resultados_renglones->fields['total_renglon'];
    $subtotal_renglon=number_format($subtotal_renglon,2,',','.');
    $total_renglon=number_format($total_renglon,2,',','.');  
    
    if (!($sin_descripcion)) $titulo.="- Ver Características Técnicas Adjuntas";
    $buffer.= "<td class='130' align='center' valign='top' >$renglon</td>";
    $buffer.= "<td align='center' valign='top'>$cantidad</td>";
    $titulo1=insertar_string($titulo,"<BR>",40);
    $buffer.= "<td colspan='4'><b>$titulo1</b></td>";
    $buffer.= "<td class='xl42' width='106' valign='top'>$moneda $subtotal_renglon</td>";
    $buffer.= "<td class='xl42' width='106' valign='top'>$moneda  $total_renglon</td>";
    $buffer.= "</tr>";
    $resultados_renglones->Movenext();
  $i++;
}

//obtengo todas las ofertas para esta licitacion
$sql="select * from (licitaciones.licitacion join licitaciones.oferta_licitacion using (id_licitacion))";
$sql.=" where licitacion.id_licitacion = $id_licitacion order by oferta_licitacion.id_oferta";
$resultado_oferta=$db->execute($sql) or die ($sql."<br>".$db->ErrorMsg());
$filas_encontradas =$resultado_oferta->RecordCount();
$i=0;
while ($i<$filas_encontradas) {
   $buffer.="<tr>";
   $buffer.="<td align='right' colspan='6'><B>TOTAL OFERTA ".$resultado_oferta->fields['nombre']."</B></td>";
   $id_oferta = $resultado_oferta->fields['id_oferta'];
   $sql="select * from (licitaciones.renglon join licitaciones.elementos_oferta using (id_renglon))";
   $sql.="where elementos_oferta.id_oferta = $id_oferta";
   $resultado_renglones_oferta=$db->execute($sql) or die($sql);
   $filas_encontradas_renglones = $resultado_renglones_oferta->RecordCount();
   $j=0;
   $total=0;
      //en este while sumo los totales de la oferta
      while($j<$filas_encontradas_renglones) {
          $total+=$resultado_renglones_oferta->fields['total']*$resultado_renglones_oferta->fields['cantidad'];
          $j++;
          $resultado_renglones_oferta->MoveNext();
      }//del segundo while
  $buffer.="<td class='xl42' width='212' colspan='2'>";
  //$total=ceil($total);
  $total=number_format($total,2,',','.');
  $buffer.="<b>";
  $buffer.=$moneda."  ".$total;
  $buffer.="</td>";
  $buffer.="</tr>";
  $i++;
  $resultado_oferta->MoveNext();
  } //fin del while que calcula los totales de la oferta
$buffer.="</table>";
$buffer.="</center>";
//Parte nueva incluyo la funcion que me convierte de numeros a letras
$resultado_oferta->MoveFirst();
$filas_encontradas =$resultado_oferta->RecordCount();
$i=0;
$buffer.="<br>";
while ($i<$filas_encontradas) {
   $buffer.="<tr>";
   $buffer.="<td colspan='8'>";
   $id_oferta = $resultado_oferta->fields['id_oferta'];
   $sql="select * from (licitaciones.renglon join licitaciones.elementos_oferta on renglon.id_renglon = elementos_oferta.id_renglon and elementos_oferta.id_oferta = $id_oferta)";
   $resultado_renglones_oferta=$db->execute($sql) or die($sql);
   $filas_encontradas_renglones = $resultado_renglones_oferta->RecordCount();
   $j=0;
   $total=0;
     //en este while sumo los totales de la oferta
      while($j<$filas_encontradas_renglones) {
          $total+=$resultado_renglones_oferta->fields['total']*$resultado_renglones_oferta->fields['cantidad'];
          $j++;
          $resultado_renglones_oferta->MoveNext();
      }//del segundo while
   $buffer.="<b>";
   $buffer.="Total Oferta  ";
   $buffer.=$resultado_oferta->fields['nombre'];
   if ($moneda=="\$") $buffer.="  Son Pesos:  ";
                       else $buffer.=" Son Dolares: ";

   $total=number_format($total,2,'.','');
   list($entero,$decimal)=split('[.]',$total);
   $buffer.= NumerosALetras($entero);
   if (!$decimal) $decimal="00";
   $buffer.="  "."con $decimal/100";
   $buffer.="<br>";
   $buffer.="</td>";
   $buffer.="</tr>";
   $i++;
   $resultado_oferta->MoveNext();
   } //fin del while que calcula los totales de la oferta
$buffer.="<br>";
$buffer.="<b>Los precios incluyen I.V.A, flete, embalaje y seguros.</b><br>";
$buffer.="<br>";
$buffer.="Plazo de mantenimiento de oferta: "; if ($plazo_mantenimiento_oferta_dias!="") $buffer.="$plazo_mantenimiento_oferta_dias días $plazo_mantenimiento_oferta<br>"; else $buffer.="$plazo_mantenimiento_oferta<br>";
$buffer.="Plazo de entrega: $plazo_entrega<br>";
$buffer.="Forma de pago: $forma_pago<br>";
if ($notas_adic) {
$buffer.="<br> NOTA: ";
$notas_adic=ereg_replace("\r\n","<br>",$notas_adic);
$buffer.= $notas_adic ;
$buffer.="<br>";
}
$buffer.="<br>";
$buffer.="</body>";
$buffer.="</html>";
//echo $buffer;die();
return genera_excel($buffer,$id_licitacion,"CD_");
} //final de la funcion que genera la cotizacion de la licitacion



function genera_excel($buffer,$nro_licitacion,$nom_cd){
global $db,$_ses_user;

 $name=$nom_cd;
 $name.="oferta_";
 $name.="lic_";
 $name.=$nro_licitacion;
 $name.=".xls";
// $path1=UPLOADS_DIR."/Licitaciones/$distrito/$entidad/$fecha/$nro_licitacion";
 $path1=UPLOADS_DIR."/Licitaciones/$nro_licitacion";
 mkdirs($path1);
 $temporal=$path1."/".$name;  //linux
 $fp = fopen($temporal,"w+");
 fwrite($fp,$buffer);
 fclose($fp);
 $FileNameFull= $temporal;
 $FileNameOld = $FileNameFull;
 $FileNameFull = substr($FileNameFull,0,strlen($FileNameFull) - strpos(strrev($FileNameFull),".") - 1).".zip";
 system(" /usr/bin/zip -j -9 -q \"$FileNameFull\" \"$FileNameOld\" ");
 $tamaño=filesize($FileNameOld);
 $nombrecomp = substr($FileNameFull,strrpos($FileNameFull,"/") + 1);
 $tamaño_comprimido=filesize($FileNameFull);
 $tipo="application/ms-excel";
 $subidofecha=date("Y-m-d H:i:s", mktime());
 $subidousuario=$_ses_user['name'];
 //controlamos que la entrada en la BD no haya sido generada previamente, para
 //evitar referencias duplicaciones para un mismo archivo
 $query="select Nombre from archivos where id_licitacion=$nro_licitacion and nombre='$name' and nombrecomp='$nombrecomp'";
 $resultado = $db->execute($query) or die($query);
 $existe=$resultado->RecordCount();
 //si no existe ya la entrada para ese archivo, crearla.
 if(!$existe) {

 //para recuperar el id del tipo de archivo q se esta subiendo
 $cons_id_tipo_archivo="select id_tipo_archivo from licitaciones.tipo_archivo_licitacion";
 if ($nom_cd=='CD_') $cons_id_tipo_archivo.="  where tipo = 'Imagen CD Oferta'";	
 else $cons_id_tipo_archivo.="  where tipo = 'Oferta'";
 $res_tipo_archivo = $db->execute($cons_id_tipo_archivo) or die($cons_id_tipo_archivo);
 $id_tipo_archivo=$res_tipo_archivo->fields['id_tipo_archivo'];
 
 $query="INSERT INTO archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario, id_tipo_archivo)
         VALUES ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario', $id_tipo_archivo)";
 $resultado = $db->execute($query) or die($query);
 }
 //si ya existe, actualizamos sus valores.
 else {
   $query="update archivos set tamaño=$tamaño,tipo='$tipo',tamañocomp='$tamaño_comprimido',subidofecha='$subidofecha',subidousuario='$subidousuario' where id_licitacion=$nro_licitacion and nombre='$name' and nombrecomp='$nombrecomp'";
  $resultado = $db->execute($query) or die($query);
 }

  unlink($FileNameOld);
  return $name;
} //final de la funcion que genera un documento .xls




////////////////////////////////////////////////////////////////////////
           // FUNCIONES PARA GENERAR DESCRIPCION TECNICA DEL RENGLON


function genera_descripcion_renglon($id_renglon) {
  global $db;

 $query="Select * from renglon where id_renglon = $id_renglon";
 $renglon = $db->execute("$query") or die($query);
 //estos datos los paso a genra_word
 $nro_renglon=$renglon->fields['nro_renglon'];
 $codigo = $renglon->fields['codigo_renglon'];
 $nro_licitacion = $renglon->fields['id_licitacion'];
 //consulta para obtnener los datos del renglon
 $query= "select * from ((licitaciones.producto join licitaciones.descripciones_renglon";
 $query.=" on producto.id = descripciones_renglon.id and producto.id_renglon = $id_renglon and borrado=0)";
 $query.=" as prod join licitaciones.prioridades on  prod.titulo = prioridades.titulo) order by id_prioridad";
 $resultado_renglon = $db->execute($query) or die($query);
 $filas_encontradas=$resultado_renglon->RecordCount();
 $buffer= "<html><body><b>";
 $buffer.="<font face='Tahoma' size='2'>" ;
 $buffer.="RENGLON Nº: ";
 $buffer.=$renglon->fields['codigo_renglon'];
 $buffer.="<br>";
 $buffer.="Cantidad: ";
 $buffer.=$renglon->fields['cantidad'];
 $buffer.="<br>";
 $buffer.=$renglon->fields['titulo'];
 $buffer.="<br></b>";
 $buffer.="</font><br>";
 $buffer.="<table align='left' width='100%' cellpadding='0' cellspacing='0' bordercolor='black' border='1'>";
for($i=0;$i<$filas_encontradas;$i++) {
                   $titulo=$resultado_renglon->fields["titulo"];
                   $contenido=$resultado_renglon->fields["contenido"];
                   //tengo que concatenar un br por cada enter que ingreso el usuario
                   $contenido2=ereg_replace( "\n", "</li><li>", $contenido );
                   //control para eliminar el <li> de sobra, provocado por
                   //un '\n' al final de $contendio
                   $tam=strlen($contenido2);
                   $sub=substr($contenido2,$tam-9,9);
                   if(strcmp($sub,"</li><li>")==0)
                    $contenido2=substr($contenido2,0,$tam-10);
//                   $buffer.="<table align='left' width=\"115%\" height='%100%' cellpadding=\"0\" cellspacing=\"0\" border='1' bordercolor='black'>";
                   $buffer.="<tr>";
                   $buffer.="<td width='16%' align='left' style='border:inset black .25pt;'>";
//                   $buffer.="<td width='16%' align='left' style='font-size:12;border-top-width: 0.5px;border-right-width: 0.5px;border-left-width: 0.5px;border-bottom-width:0.5px'>";
                   $buffer.="<font face='Tahoma' style='font-size:8.0pt;'>";
                   $buffer.="<b>&nbsp;$titulo</b></font></td>";
//                   $buffer.="<td width='84%' style='font-size:12;border-top-width: 0.5px;border-right-width: 0.5px;border-left-width: 0.5px;border-bottom-width:0.5px'> ";
                   $buffer.="<td width='84%' style='border:inset black .25pt;'>";
                   //$buffer.="<font size='2' face=\"'Arial',Helvetica,sans-serif' \">";
//                   $buffer.="<li>";
                   $buffer.="<font face='Tahoma' style='font-size:8.0pt'>";
                   $buffer.="<li>$contenido2</li>";
                   $buffer.="</font>";
//                   $buffer.="</font></li>";
                   //$buffer.="</font>";
                   $buffer.="</td>";
                   $buffer.="</tr>";
//                   $buffer.="</table>";
                   //el siguiente item dentro de esa descripcion
                   $resultado_renglon->MoveNext();

}
$buffer.="</body>";
$buffer.="</html>";
$buffer;
genera_word($buffer,$nro_licitacion,$codigo,$id_renglon);
}
 ///FIN DE GENERAR LA DESCRIPCION DEL RENGLON
 ///COMIENZO DE LA FUNCION QUE ME GENERA EL WORD

function genera_word($buffer,$nro_licitacion,$codigo,$id_renglon) {
global $db,$_ses_user;

 $name="Desc_";
 $name.="lic_" ;
 $name.=$nro_licitacion;
 $name.="_renglon_";
 $codigo=ereg_replace(" ","_",$codigo);
 $name.=$codigo;
 $name.=".doc";
 $path1=UPLOADS_DIR."/Licitaciones/$nro_licitacion";
 //$path1=UPLOADS_DIR."/Licitaciones/$distrito/$entidad/$fecha/$nro_licitacion"; //windows
  mkdirs($path1);
 $temporal=$path1."/".$name;  //linux
 //$fp = fopen("c:"."$name","w+");  //local
 $fp=fopen($temporal,"w+");  //servidor
 fwrite($fp,$buffer);
 fclose($fp);
 $FileNameFull= $temporal;
 $FileNameOld = $FileNameFull;
 $FileNameFull = substr($FileNameFull,0,strlen($FileNameFull) - strpos(strrev($FileNameFull),".") - 1).".zip";
 system("/usr/bin/zip -j -9 -q \"$FileNameFull\" \"$FileNameOld\" ");
 $tamaño=filesize($FileNameOld);
 $nombrecomp = substr($FileNameFull,strrpos($FileNameFull,"/") + 1);
 $tamaño_comprimido=filesize($FileNameFull);
 $tipo="application/msword";
 $subidofecha=date("Y-m-d H:i:s", mktime());
 $query="select usuario from renglon where id_renglon=$id_renglon";
 $user_name=$db->Execute($query) or die($db->ErrorMsg().$query);
 $subidousuario=$user_name->fields['usuario'];

 //controlamos que la entrada en la BD no haya sido generada previamente, para
 //evitar referencias duplicaciones para un mismo archivo
 $query="select Nombre from archivos where id_licitacion=$nro_licitacion and nombre='$name' and nombrecomp='$nombrecomp'";
 $resultado = $db->execute($query) or die($query);
 $existe=$resultado->RecordCount();
 if(!$existe) //si no existe ya la entrada para ese archivo, crearla.
 {
  //para recuperar el id del tipo de archivo q se esta subiendo
  $cons_id_tipo_archivo="select id_tipo_archivo from licitaciones.tipo_archivo_licitacion
                       where tipo = 'Descripciones' ";
  $res_id_tipo_archivo=$db->execute($cons_id_tipo_archivo) or die($cons_id_tipo_archivo);
  $id_tipo_archivo=$res_id_tipo_archivo->fields['id_tipo_archivo'];
  
  $query="INSERT INTO archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario, id_tipo_archivo)
          VALUES ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario', $id_tipo_archivo)";
  $resultado = $db->execute($query) or die($query);
 }
 else //si ya existe, actualizamos sus valores.
 {
  $query="update archivos set tamaño=$tamaño,tipo='$tipo',tamañocomp='$tamaño_comprimido',subidofecha='$subidofecha',subidousuario='$subidousuario' where id_licitacion=$nro_licitacion and nombre='$name' and nombrecomp='$nombrecomp'";
  $resultado = $db->execute($query) or die($query);
 }
  //borro el archivo doc
 unlink($FileNameOld);

 }

// funcion para subir folletos a las licitaciones que tienen
// renglones enterprise: porteña o argentina
function subir_folletos($nro_licitacion,$tipo_folleto) {
global $db, $_ses_user;
       $name = array();
// primero sube los archivos con extension .pdf
 if ($tipo_folleto==1){
          $name[0]="CD_Portenia_Pentium_4.pdf";
          $name[1]="CD_Portenia_Celeron.pdf";
          $name[2]="Reverso_Folleto_Porteña_Pentium_4_v2.doc";
          $name[3]="Reverso_Folleto_Porteña_Celeron_v2.doc";
          }
 if ($tipo_folleto==2){
          $name[0]="CD_Argentina_PC-002.pdf";
          $name[1]="CD_Argentina_PC-003.pdf";
          $name[2]="CD_Argentina_PC-004.pdf";
          $name[3]="Reverso_Folleto_Argentina_PC-002_v1.doc";
          $name[4]="Reverso_Folleto_Argentina_PC-003_v1.doc";
          $name[5]="Reverso_Folleto_Argentina_PC-004_v2.doc";
          }
 if ($tipo_folleto==3){ // 0 Certificado ISO9001
         $name[0]="ISO 9001 BVQI Coradir.pdf";
   }
 $path1=UPLOADS_DIR."/Licitaciones/$nro_licitacion";
 mkdirs($path1);
 for ($i=0;$i<sizeof($name);$i++){
   $temporal=$path1."/".$name[$i];
   $FileNameFull= $temporal;
   $FileNameOld = $FileNameFull;
   $FileNameFull = substr($FileNameFull,0,strlen($FileNameFull) - strpos(strrev($FileNameFull),".") - 1).".zip";
   $filefolleto=UPLOADS_DIR."/Licitaciones/folletos_lic/".$name[$i];
   $nombrecomp = substr($FileNameFull,strrpos($FileNameFull,"/") + 1);

   system("cp \"$filefolleto\" \"$path1\"");
   system("/usr/bin/zip -j -9 -q \"$FileNameFull\" \"$FileNameOld\" ");

   $tamaño=filesize($FileNameOld);
   $nombrecomp = substr($FileNameFull,strrpos($FileNameFull,"/") + 1);
   $tamaño_comprimido=filesize($FileNameFull);
   list($nombre, $ext)=split('[.]', $name[$i]);
   if ($ext=="pdf")
             $tipo="application/pdf";
             else
             $tipo="application/msword";

   $subidofecha=date("Y-m-d H:i:s", mktime());
   $query="select Nombre from archivos where id_licitacion=$nro_licitacion and nombre='".$name[$i]."' and nombrecomp='$nombrecomp'";
   $resultado = $db->execute($query) or die($query);
   $existe=$resultado->RecordCount();
   //recupero el id_tipo_archivo para guardarlo en la tabla archivos
   $cons_tipo="select id_tipo_archivo 
               from licitaciones.tipo_archivo_licitacion 
               where tipo='Folletos'";
   $res_cons_tipo=sql($cons_tipo) or fin_pagina();
   $id_tipo_archivo=$res_cons_tipo->fields['id_tipo_archivo'];
   
   if(!$existe) //si no existe ya la entrada para ese archivo, crearla.
           {
            $query="INSERT INTO archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_tipo_archivo)  
                    VALUES ($nro_licitacion,'".$name[$i]."','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','".$_ses_user['name']."',$id_tipo_archivo)";
            $resultado = $db->execute($query) or die($query);
            }
            else //si ya existe, actualizamos sus valores.
              {
               $query="update archivos set tamaño=$tamaño,tipo='$tipo',tamañocomp='$tamaño_comprimido',subidofecha='$subidofecha',subidousuario='$subidousuario' where id_licitacion=$nro_licitacion and nombre='$name' and nombrecomp='$nombrecomp'";
               $resultado = $db->execute($query) or die($query);
              }
  //borro el archivo doc
  unlink($FileNameOld);
  }// cierra el for
}// cierra la funcion
//*Funcion que me crea un nuevo renglon junto con sus despcrioones
?>