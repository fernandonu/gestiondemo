<?php
/*
$Author: ferni $
$Revision: 1.3 $
$Date: 2005/09/05 14:57:47 $
*/
require_once("../../config.php");



variables_form_busqueda("listado_componentes");

$orden=array(
	"default" => "1",
	"default_up" => "1",
    "1"=>"cb.codigo_barra",
    "2"=>"p.desc_gral",
    "3"=>"razon_social",
    "4"=>"log.usuario",
    "5"=>"log.fecha");

$filtro=array(
        "cb.codigo_barra"=>"Código Barra",
        "p.desc_gral"=>"Producto",
        "razon_social"=>"Proveedor",
        "log.usuario"=>"Usuario",
        );
if (!$cmd) $cmd="actuales";

$sql_temp="select p.id_producto,p.desc_gral,cb.codigo_barra,id_proveedor,razon_social,
               log.usuario,log.fecha,componentes.observacion,componentes.id_componente_daniado
               from componentes_daniados  componentes
               join general.codigos_barra cb using(codigo_barra)
               left join log_componentes_daniados  log using(id_componente_daniado)
               join general.productos p using (id_producto)
               left join general.log_codigos_barra on(cb.codigo_barra=log_codigos_barra.codigo_barra)
               left join orden_de_compra using(nro_orden)
               left join proveedor using(id_proveedor)

              ";
if ($cmd=='actuales')
                     {
                         $activo=1;
                         $log="creacion";
                     }
if ($cmd=='eliminados')
                      {
                     $activo=0;
                     $log="eliminado";
                      }
$where_temp=" log.tipo='$log' and componentes.activo=$activo";

echo $html_header;
?>
<br>
<form name=form1 action='<?=$_SERVER["PHP_SELF"]?>' method='post'>

<?
$datos_barra = array(
                    array(
                        "descripcion"    => "Actuales",
                        "cmd"            => "actuales"
                        ),
                    array(
                        "descripcion"    => "Eliminados",
                        "cmd"            => "eliminados"
                        )
                     );
generar_barra_nav($datos_barra);
?>



   <table width=100% align=center class=bordes>
     <tr>
      <td width=100% align=center>
          <?
          list($sql,$total_reg,$link_pagina,$up) = form_busqueda($sql_temp,$orden,$filtro,$link_temp,$where_temp,"buscar");
          $resultado = sql($sql) or fin_pagina();
         ?>
         &nbsp
         <input type=submit name=form_busqueda value='Buscar'>
      </td>
	</tr>
     <?/*
      	echo "<tr>";
 	  	$link=encode_link("busq_avanzada_compdaniados.php",array());
 		echo "<td align="left"><input type="button" name="busqueda_avanzada" value="Busqueda Avanzada"  title="Realiza Busquedas Avanzadas" onclick="window.open('<?=$link?>')"></td>";
	    echo "</tr>";
	    */
		?>      
      <tr>
      <?
      $link=encode_link("componentes_daniados.php",array("pagina"=>"nuevo"));
      ?>
         <td align=left>
           <input type=button name=nuevo value="Nuevo" onclick="window.open('<?=$link?>')">
         </td>
      </tr>
      <tr>
      <td>
        <table width=100% align=center>
          <tr id=ma>
             <td align=Center colspan=5>
                   <table width=100% align=center>
                   <td align=left><b>Cantidad de Componentes: &nbsp;<?=$total_reg?></b></td>
                   <td align=right><b><?=$link_pagina?></b></td>
                   </table>
             </td>
          </tr>
          <tr>
            <td id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Código</a></td>
            <td id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Producto</a></td>
            <td id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Proveedor</a></td>
            <td id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Usuario</a></td>
            <td id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Fecha</a></td>
          </tr>
          <?
          $cantidad=$resultado->recordcount();
          for ($i=0;$i<$cantidad;$i++){
           $link=encode_link("componentes_daniados.php",array("id_componente_daniado"=>$resultado->fields["id_componente_daniado"],"pagina"=>"listado"));
           $observacion=$resultado->fields["observacion"];
           tr_tag($link,"title='$observacion'");
          ?>

            <td><?=$resultado->fields["codigo_barra"]?></td>
            <td><?=$resultado->fields["desc_gral"]?></td>
            <td><?=$resultado->fields["razon_social"]?></td>
            <td><?=$resultado->fields["usuario"]?></td>
            <td><?=fecha($resultado->fields["fecha"])?>
          </tr>
          <?
          $resultado->movenext();
          }//del for
          ?>
         </table>
        </td>
      </tr>
   </table>
</form>
<?
echo fin_pagina();
?>