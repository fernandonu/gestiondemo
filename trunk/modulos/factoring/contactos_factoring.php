<?php
 /*
$Creador ;Fernando $
$Author: fernando $
$Revision: 1.2 $
$Date: 2005/03/19 16:30:16 $
*/
include("../../config.php");
$id_contactos_factoring=$parametros["id_contactos_factoring"] or $id_contactos_factoring=$_POST["id_contactos_factoring"];
$id_factoring=$parametros["id_factoring"] or $id_factoring=$_POST["id_factoring"];

if ($id_contactos_factoring && $_POST["aceptar"]){
     $nombre=$_POST["nombre"];
     $direccion=$_POST["direccion"];
     $localidad=$_POST["localidad"];
     $codigo_postal=$_POST["codigo_postal"];
     $icq_msm=$_POST["icq_msm"];
     $fax=$_POST["fax"];
     $id_distrito=$_POST["distrito"];
     $telefono=$_POST["telefono"];
     $mail=$_POST["mail"];

     $db->starttrans();

     $sql="update contactos_factoring set nombre='$nombre',direccion='$direccion',localidad='$localidad',
                                          codigo_postal='$codigo_postal',icq_msm='$icq_msm',id_distrito=$id_distrito,
                                          telefono='$telefono',mail='$mail',fax='$fax'
                                          where id_contactos_factoring=$id_contactos_factoring";


     sql($sql) or fin_pagina();
     if ($db->completetrans()) $msg="Se modifico con éxito el Contacto del Factoring";
                            else $msg="Error: No se pudo modificar el Contacto del Factoring";
    }


if (!$id_contactos_factoring && $_POST["aceptar"]){
     $nombre=$_POST["nombre"];
     $direccion=$_POST["direccion"];
     $localidad=$_POST["localidad"];
     $codigo_postal=$_POST["codigo_postal"];
     $icq_msm=$_POST["icq_msm"];
     $fax=$_POST["fax"];
     $id_distrito=$_POST["distrito"];
     $telefono=$_POST["telefono"];
     $mail=$_POST["mail"];

     $db->starttrans();
     $sql="select nextval('contactos_factoring_id_contactos_factoring_seq') as id_contacto_factoring";
     $res=sql($sql) or fin_pagina();
     $id_contactos_factoring=$res->fields["id_contacto_factoring"];
     $campos="id_contactos_factoring,id_factoring,nombre,direccion,localidad,codigo_postal,icq_msm,fax,id_distrito,telefono,mail";
     $values="$id_contactos_factoring,$id_factoring,'$nombre','$direccion','$localidad','$codigo_postal','$icq_msm','$fax',$id_distrito,'$telefono','$mail'";
     $sql=" insert into contactos_factoring ($campos) values ($values)";
     sql($sql) or fin_pagina();
     if ($db->completetrans()) $msg="Se inserto con éxito el Contacto del Factoring";
                            else $msg="Error: No se pudo insertar el Contacto del Factoring";
    }
echo $html_header;
if ($msg) Aviso($msg);



if ($id_contactos_factoring)
    {
    $sql="select c.*
                 from licitaciones.factoring f
                 join licitaciones.contactos_factoring c using(id_factoring)
                 WHERE  factoring.activo=1 and c.id_contactos_factoring=$id_contactos_factoring";
    $contactos_factoring=sql($sql) or fin_pagina();
    $nombre=$contactos_factoring->fields["nombre"];
    $direccion=$contactos_factoring->fields["direccion"];
    $localidad=$contactos_factoring->fields["localidad"];
    $codigo_postal=$contactos_factoring->fields["codigo_postal"];
    $telefono=$contactos_factoring->fields["telefono"];
    $fax=$contactos_factoring->fields["fax"];
    $mail=$contactos_factoring->fields["mail"];
    $icq_msm=$contactos_factoring->fields["icq_msm"];
    $id_distrito=$contactos_factoring->fields["id_distrito"];
    }
?>
<br>
<form name=form1 action='<?=$_SERVER["PHP_SELF"]?>' method='post'>
<input type=hidden name=id_factoring value='<?=$id_factoring?>'>
<input type=hidden name=id_contactos_factoring value='<?=$id_contactos_factoring?>'>
  <table width=60% align=center class=bordes bgcolor="<?=$bgcolor2?>">
    <tr id=mo><td> Datos del Contacto del Factoring </td></tr>
    <tr>
       <td width=100% align=center>
           <table width=100% align=center>
               <tr>
                 <td id=ma_sf>Nombre</td>
                 <td><input type=text name="nombre" value="<?=$nombre?>" size=75></td>
               </tr>
               <tr>
                 <td id=ma_sf>Teléfono</td>
                 <td><input type=text name="telefono" value="<?=$telefono?>" size=50></td>
               </tr>
               <tr>
                 <td id=ma_sf>Dirección</td>
                 <td><input type=text name="direccion" value="<?=$direccion?>" size=75></td>
               </tr>

               <tr>
                 <td id=ma_sf>Localidad</td>
                 <td><input type=text name="localidad" value="<?=$localidad?>" size=50></td>
               </tr>
               <tr>
                <td id=ma_sf>Distrito</td>
                 <td>
                  <?
                  $sql="select * from distrito order by nombre";
                  $res=sql($sql) or fin_pagina();
                  ?>
                  <select name=distrito>
                   <?
                   for($i=0;$i<$res->recordcount();$i++){
                    $id_distrito_gral=$res->fields["id_distrito"];

                    if ($id_distrito==$id_distrito_gral) $selected="selected";
                                                    else $selected="";
                   ?>
                    <option value="<?=$id_distrito_gral?>" <?=$selected?>><?=$res->fields["nombre"]?></option>
                   <?
                   $res->movenext();
                   }
                   ?>
                  </select>
                 </td>
               </tr>


               <tr>
                 <td id=ma_sf>Codigo Postal</td>
                 <td><input type=text name="codigo_postal" value="<?=$codigo_postal?>" size=50></td>
               </tr>
               <tr>

               <tr>
                 <td id=ma_sf>Mail</td>
                 <td><input type=text name="mail" value="<?=$mail?>" size=50></td>
               </tr>
               <tr>
                 <td id=ma_sf>ICQ/MSM</td>
                 <td><input type=text name="icq_msm" value="<?=$icq_msm?>" size=50></td>
               </tr>
               <tr>
                 <td id=ma_sf>Fax</td>
                 <td><input type=text name="fax" value="<?=$fax?>" size=50></td>
               </tr>

           </table>
       </td>
    </tr>
    <tr>
      <td width=100% align=center>
      <table width=50% align=center>
         <tr>
            <td><input type=submit name=aceptar value=Aceptar style='width:150'></td>
            <td><input type=button name=cancelar value=Cancelar style='width:150' onclick='window.close()'></td>

         </tr>
      </table>
      </td>
    </tr>
  </table>
</form>
<?
echo fin_pagina();
?>