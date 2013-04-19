<?php
/*
$Creador: (A Pedido del Publico) Fernando$
$Fecha: 2004/08/05$

$Author: fernando $
$Revision: 1.3 $
$Date: 2004/08/10 00:10:45 $
*/
require_once("../../config.php");
//Funcion que me modifica las preferencias del usuario
//se le pasas como parametros el tipo de asociacion y que se debe hacer
//valores para tipo= resultado - asociaciones
//valores para accion= agregar - quitar
$msg="";
function modificar_preferencia_boton($tipo,$accion){
global $db,$_ses_user,$msg;
$usuario=$_ses_user["login"];

if ($accion=="agregar") {
            if (!obtener_preferencias($usuario,$tipo)) {
                              $sql=" insert into configuracion_botones (tipo,usuario)
                                     values ('$tipo','$usuario')";
                              if ($db->execute($sql)) // or die($db->errormsg()."<br>".$sql);
                                                    $msg="Se Modificaron los datos con éxito";
                                                    else
                                                    $msg="No se Modificaron los datos con éxito";
                              }
            } //si se agrega estado

if ($accion=="quitar") {
                $sql="delete from configuracion_botones
                      where  usuario='$usuario' and tipo='$tipo'";
                if ($db->execute($sql)) // or die($db->errormsg()."<br>".$sql);
                            $msg="Se Modificaron los datos con éxito";
                            else
                            $msg="No se Modificaron los datos con éxito";

         }

} // de la funcion modificar preferencia


echo $html_header;
$usuario=$_ses_user["login"];

 //print_r($_POST);

if ($_POST["aceptar"]=="Aceptar"){

       //me fijo se quiere resultados

       if ($_POST["ch_resultados"]=="1"){

          modificar_preferencia_boton("resultado","agregar");
          }
          else{
            modificar_preferencia_boton("resultado","quitar");
            }
       if ($_POST["ch_asociaciones"]=="1"){
          modificar_preferencia_boton("asociaciones","agregar");
          }
          else{
            modificar_preferencia_boton("asociaciones","quitar");
            }

} // del post de guardar

if ($msg) aviso($msg);
?>
<form name=form1 method=post action="<?=$_SERVER["PHP_SELF"]?>">
<table width=50% align=center cellspacing=1 cellpading=1 border=1 bgcolor=<?=$bgcolor2;?>>
 <tr id='mo'>
   <td>Configuración de los botones</td>
 </tr>
  <tr>
   <td>
       <table width=100% align=center>
         <tr id=ma>
              <td width=1% align=Center>&nbsp;</td>
              <td align=center><b>Grupo de Botones</b></td>
         </tr>
         <tr>
         <?
         if (obtener_preferencias($_ses_user['login'],"resultado"))
                                      $checked="checked";
                                      else
                                      $checked="";
         ?>
           <td><input type=checkbox name=ch_resultados <?=$checked?> value=1></td>
           <td align=center> <font size=2 color=red> Resultados </font></td>
         </tr>
         <tr>
         <?
         if (obtener_preferencias($_ses_user['login'],"asociaciones"))
                                      $checked="checked";
                                      else
                                      $checked="";
         ?>

           <td><input type=checkbox name=ch_asociaciones <?=$checked?> value=1></td>
           <td align=center> <font size=2 color=red>Asociaciones</font></td>
         </tr>
       </table>
   </td>
 </tr>
 <tr>
    <td align=Center>
       <input type=submit name=aceptar value=Aceptar>
    </td>
 </tr>
</table>
</form>