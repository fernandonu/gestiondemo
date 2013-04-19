<?php
 /*
$Creador :Fernando $
$Author: fernando $
$Revision: 1.1 $
$Date: 2005/03/19 16:19:59 $
*/
include("../../config.php");


if ($_POST["borrar"]){

    $factoring_eliminar=$_POST["chk_factoring"];
    if ($factoring_eliminar) {
    while ((list($key,$id_factoring)=each($factoring_eliminar))) {
                $sql_array[]="update factoring set activo=0  where id_factoring=$id_factoring";
	  		    }
    sql($sql_array) or fin_pagina();
	}
}


echo $html_header;
variables_form_busqueda("listado_factoring");

$orden=array(
	"default" => "1",
	"default_up" => "1",
    "1"=>"f.nombre",
    "2"=>"nombre_distrito",
    "3"=>"f.direccion",
    "4"=>"f.telefono",
    "5"=>"f.mail");

$filtro=array(
        "f.nombre"=>"Nombre",
        "d.nombre"=>"Distrito",
        "f.direccion"=>"Dirección",
        "f.telefono"=>"Teléfono",
        "f.mail"=>"Mail");


$sql_temp="select f.nombre,f.direccion,f.telefono,f.mail,f.id_factoring,d.nombre as nombre_distrito
               from factoring f
               join distrito d using(id_distrito)
       ";
$where_temp=" f.activo=1";
?>
<form name=form1 action='<?=$_SERVER["PHP_SELF"]?>' method='post'>
<br>
<table width=100% align=center class=bordes>

 <tr>
  <td align=center>
  <?
  list($sql,$total_reg,$link_pagina,$up) = form_busqueda($sql_temp,$orden,$filtro,$link_temp,$where_temp,"buscar");
  $resultado = sql($sql) or fin_pagina();

  $link=encode_link("factoring.php",array("accion"=>"nuevo"));
  ?>
  <input  type=submit name=form_busqueda value='Buscar'>
  &nbsp;
  <input type=button name=nuevo value="Nuevo Factoring" onclick="window.open('<?=$link?>')">
  </td>
 </tr>
 <tr>
 <td>
 <table width=100% align=center>
          <tr id=ma>
             <td align=Center colspan=6>
                   <table width=100% align=center>
                   <td align=left><b>Cantidad de Factoring: &nbsp;<?=$total_reg?></b></td>
                   <td align=right><b><?=$link_pagina?></b></td>
                   </table>
            </td>
          </tr>
          <tr>
           <td id=mo width=1%></td>
           <td id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Nombre</a></td>
           <td id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Distrito</a></td>
           <td id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Dirección</a></td>
           <td id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Teléfono</a></td>
           <td id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Mail</a></td>
        </tr>
    <?
    for($i=0;$i<$resultado->recordcount();$i++){
          $id_factoring=$resultado->fields["id_factoring"];
          $link=encode_link("factoring.php",array("id_factoring"=>$id_factoring));

    ?>
         <tr <?=atrib_tr()?>>
         <td align=center><input type=checkbox name=chk_factoring[] value="<?=$id_factoring?>" class="estilos_check"></td>
         <a  href='<?=$link?>'><td ><?=$resultado->fields["nombre"]?></td></a>
         <a  href='<?=$link?>'><td><?=$resultado->fields["nombre_distrito"]?></td> </a>
         <a  href='<?=$link?>'><td><?=$resultado->fields["direccion"]?></td></a>
         <a  href='<?=$link?>'><td><?=$resultado->fields["telefono"]?></td></a>
         <a  href='<?=$link?>'><td><?=$resultado->fields["mail"]?></td></a>
         </tr>
    <?
    $resultado->movenext();
    }
    ?>
     <tr>
       <td colspan=6 align=left><input type=submit name=borrar value='Borrar Factoring'></td>
     </tr>
    </table>
  </td>
  </tr>
 </table>
</form>
<?
echo fin_pagina();
?>