<?php
/*
$Author: fernando $
$Revision: 1.6 $
$Date: 2005/09/05 20:02:01 $
*/

require_once("../../config.php");
require_once("funciones.php");

/*
print_r($_POST);
echo "<br>";

print_r($parametros);
 */
echo $html_header;

$id_licitacion=$_POST["id_licitacion"] or $parametros["id_licitacion"];
$id_licitacion_original=$parametros["id_licitacion_original"]  or $id_licitacion_original=$_POST["id_licitacion_original"];
$link_form_busqueda=$parametros["link_form_busqueda"];

//elegir_renglon_otra_licitacion: es para elegir el renglon de una licitaion que no es la que invoco
// a duplicar renglon


if ($_POST["duplicar_renglon"]){

                        $array_renglones=$_POST["id_renglon"];
                        //si !$id_licitacion se copia un renglon de la misma licitacion
                        
                        for($i=0;$i<sizeof($array_renglones);$i++)
                           {

                              $id_renglon=$array_renglones[$i];
                              $msg.=duplicar_renglon($id_licitacion_original,$id_renglon,1);
                           }
                         $msg_destino=" <font color=red><b>En la Licitación con Id:$id_licitacion </b></font><br>";
                         //inserta en $id_licitacion_original , el renglon de $id_licitacion
                         if ($msg) aviso_duplicar($msg,$msg_destino);

                        }
?>
<form name=form1 method=post>
 <input type=hidden name=id_licitacion value="<?=$id_licitacion?>">
 <input type=hidden name=id_licitacion_original value="<?=$id_licitacion_original?>">

<?

           variables_form_busqueda("duplicar_renglon_otra_lic");

            $elegir_renglon_otra_licitacion=1;
            $elegir_opcion=0;
            $elegir_renglon_licitacion=0;


            $orden = array(
            		"default" => "1",
                    "default_up"=>"0",
                    "1"  => "id_licitacion",
            		"2" => "entidad.nombre",
            		"3" => "distrito.nombre",
                    "4" => "nro_lic_codificado"
            	);

            $filtro = array(
            		"id_licitacion" => "ID Licitación",
            		"entidad.nombre" => "Entidad",
                    "distrito.nombre"=> "Distrito",
                    "nro_lic_codificado" => "Número"

            	);



    $sql="select id_licitacion,nro_lic_codificado, entidad.nombre as nombre_entidad
                  ,distrito.nombre as nombre_distrito,estado.color
          from licitaciones.licitacion
          join licitaciones.entidad using(id_entidad)
          join licitaciones.estado using(id_estado)
          join licitaciones.distrito using(id_distrito)
                join (
                select count(id_renglon) as cantidad, id_licitacion from
                       licitaciones.renglon where cantidad>0
                        group by id_licitacion
                ) as cantidad_renglones using (id_licitacion)
          ";
          $where=" borrada='false' ";

    $link_tmp["id_licitacion"]=$id_licitacion;
    $link_tmp["id_licitacion_original"]=$id_licitacion_original;
    $link_tmp["link_form_busqueda"]=1;
?>
    <table width=100% align=center class=bordes>
       <tr>
          <td colspan=5 id=mo>Elija la Licitación para duplicar el renglon correspondiente </td>
          </td>
       </tr>
      <tr>
        <td colspan=6 align=center>
            <?
            list($sql_temp,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,"buscar");
            $result = sql($sql_temp,"error en busqueda") or fin_pagina();
            echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>";
            ?>
         </td>
      </tr>
      <tr id=ma>
       <td colspan=3 align=left>Cantidad: <?=$total?> </td>
       <td colspan=3 align=right><?=$link_pagina?></td>
       </tr>

       <tr id=mo>
          <td width=1%>&nbsp;</td>
          <td ><a id=mo href='<?=encode_link("duplicar_renglon_avanzado.php",array("sort"=>"1","up"=>$up,"id_licitacion_original"=>$id_licitacion_original,"link_form_busqueda"=>1))?>'>Id</a></td>
          <td><a id=mo href='<?=encode_link("duplicar_renglon_avanzado.php",array("sort"=>"2","up"=>$up,"id_licitacion_original"=>$id_licitacion_original,"link_form_busqueda"=>1))?>'>Entidad</a></td>
          <td><a id=mo href='<?=encode_link("duplicar_renglon_avanzado.php",array("sort"=>"3","up"=>$up,"id_licitacion_original"=>$id_licitacion_original,"link_form_busqueda"=>1))?>'>Distrito</a></td>
          <td><a id=mo href='<?=encode_link("duplicar_renglon_avanzado.php",array("sort"=>"4","up"=>$up,"id_licitacion_original"=>$id_licitacion_original,"link_form_busqueda"=>1))?>'>Número</a></td>
       </tr>
       <?
       $cantidad=$result->recordcount();
       for($i=0;$i<$cantidad;$i++){
       $id_tabla="tabla_licitacion_".$result->fields["id_licitacion"];

       $onclick_check=" javascript:(this.checked)?Mostrar('$id_tabla'):Ocultar('$id_tabla')";

       ?>
       <tr <?=atrib_tr()?> >
          <td>
           <input type=checkbox name=radio_id_licitacion value="<?=$result->fields["id_licitacion"]?>" onclick="<?=$onclick_check?>" class="estilos_check">
          </td>
          <td bgcolor="<?=$result->fields["color"]?>"><?=$result->fields["id_licitacion"]?></td>
          <td><?=$result->fields["nombre_entidad"]?></td>
          <td><?=$result->fields["nombre_distrito"]?></td>
          <td><?=$result->fields["nro_lic_codificado"]?></td>
        </tr>

        <tr>
          <td colspan=6>

                  <?
                  $sql=" select codigo_renglon,titulo,id_renglon
                              from renglon where id_licitacion=". $result->fields["id_licitacion"]."order by codigo_renglon";
                  $renglones=sql($sql) or fin_pagina();
                  ?>
                  <div id=<?=$id_tabla?> style='display:none'>
                  <table width=80% align=center class=bordes>
                           <tr id=ma>
                               <td width=1%>&nbsp;</td>
                               <td>Renglon</td>
                               <td>Título</td>
                            </tr>
                            <?
                            $cantidad_renglones=$renglones->recordcount();
                            for ($y=0;$y<$cantidad_renglones;$y++){
                            ?>
                            <tr <?=atrib_tr()?>>
                                 <td><input type=checkbox  name=id_renglon[] value=<?=$renglones->fields["id_renglon"]?> class="estilos_check"></td>
                                 <td><?=$renglones->fields["codigo_renglon"]?></td>
                                 <td><?=$renglones->fields["titulo"]?></td>
                            </tr>
                            <?
                            $renglones->movenext();
                           }
                           ?>
               </table>
               </div>

         </td>
      </tr>

       <?

       $result->movenext();
       }
       ?>
       <tr>
          <td colspan=6 align=center>
            <input type=submit name=duplicar_renglon value=Duplicar>
            &nbsp;
            <input type=button name=cerrar value="Cerrar" onclick="window.close()">
          </td>
       </tr>
    </table>
<input type=hidden name=elegir_renglon_otra_licitacion value=<?=$elegir_renglon_otra_licitacion?>>
<input type=hidden name=elegir_opcion value=<?=$elegir_opcion?>>
<input type=hidden name=elegir_renglon_licitacion value=<?=$elegir_renglon_licitacion?>>
</form>
<?
echo fin_pagina();
?>