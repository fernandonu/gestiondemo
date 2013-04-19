<?
/*
Autor: Fernando
Creado: jueves 13/05/04

MODIFICADA POR
$Author: fernando $
$Revision: 1.2 $
$Date: 2006/05/15 20:38:08 $
*/
require_once("../../config.php");
echo $html_header;

$id_factura=$parametros["id_factura"] or $id_factura=$_POST["id_factura"];
$nro_factura=$parametros["nro_factura"] or $nro_factura=$_POST["nro_factura"];
if ($_POST["guardar"]){
   
    ($_POST["renglones_entregados"])?$renglones_entregados=1:$renglones_entregados=0;
    ($_POST["licitacion_entregada"])?$licitacion_entregada=1:$licitacion_entregada=0;
    $sql="update licitaciones.cobranzas 
          set renglones_entregados=$renglones_entregados, licitacion_entregada=$licitacion_entregada
          where cobranzas.id_factura=$id_factura
          "; 
     sql($sql) or fin_pagina(); 
     $msg="Se modificaron las banderas con éxito";       
}


if ($id_factura){
    $sql=" select c.licitacion_entregada, c.renglones_entregados ,c.nro_factura
           from licitaciones.cobranzas c
           where c.id_factura=$id_factura";
    $res=sql($sql) or fin_pagina();
    ($res->fields["licitacion_entregada"])?$check_licitaciones='checked':$check_licitaciones='';      
    ($res->fields["renglones_entregados"])?$check_renglones='checked':$check_renglones='';
    $nro_factura=$res->fields["nro_factura"];
}

if ($msg) Aviso($msg);
?>
<form name=form1 method=post action="administrar_banderas_cobranzas.php">
<input type=hidden name=id_factura value="<?=$id_factura?>">
  <table width=40% align=Center class=bordes>
     <tr id=mo>
        <td colspan=2> Estado de las Banderas en Cobranzas</td>
     </tr>
     <tr <?=atrib_tr()?>>
       <td> <b>Factura N°: </b></td>
       <td><?=$nro_factura?></td>
     </tr>
     <tr <?=atrib_tr()?>>
        <td> <b>Licitación Entregada</b></td>  
        <td> <input type=checkbox name='licitacion_entregada' value=1 <?=$check_licitaciones?>></td>
     </tr>
     <tr <?=atrib_tr()?>>
        <td> <b>Renglones Entregados</b></td>  
        <td> <input type=checkbox name='renglones_entregados' value=1 <?=$check_renglones?>></td>
     </tr>
     <tr>
       <td colspan=2 align=center>
          <input type=submit name=guardar value=Guardar>
          <input type=button name=cerrar value=Cerrar onclick='window.close()'> 
       </td>
     </tr>   
     <tr>
       <td colspan=2 bgcolor="white"><b>Para que la factura sea considerada en el balance <BR> Las 2 opciones deben estar seleccionadas</b></td>
     </tr>  
  </table>
</form>
<?=fin_pagina()?>