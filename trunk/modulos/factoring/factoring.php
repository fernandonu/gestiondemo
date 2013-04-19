<?php
 /*
$Creador ;Fernando $
$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2006/05/10 18:16:48 $
*/
include("../../config.php");
$id_factoring=$parametros["id_factoring"] or $id_factoring=$_POST["id_factoring"];
$accion=$parametros["accion"] or $accion=$_POST["accion"];

if ($_POST["borrar_contactos"]){

    $contactos_eliminar=$_POST["chk_contactos"];
    if ($contactos_eliminar){
      while ((list($key,$id_contactos_factoring)=each($contactos_eliminar))) {
               $sql_array[]="delete from contactos_factoring where id_contactos_factoring=$id_contactos_factoring";
		  	   }
      sql($sql_array) or fin_pagina();
	}
}



if ($id_factoring && $_POST["aceptar"]){
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

     $sql="update factoring set nombre='$nombre',direccion='$direccion',localidad='$localidad',
                                          codigo_postal='$codigo_postal',icq_msm='$icq_msm',id_distrito=$id_distrito,
                                          telefono='$telefono',mail='$mail',fax='$fax'
                                          where id_factoring=$id_factoring";


     sql($sql) or fin_pagina();
     if ($db->completetrans()) $msg="Se modifico con éxito el Factoring";
                            else $msg="Error: No se pudo modificar el Factoring";
    }




if (!$id_factoring && $_POST["aceptar"]){
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
     $sql="select nextval('factoring_id_factoring_seq') as id_factoring";
                           //factoring_id_factoring_seq
     $res=sql($sql) or fin_pagina();
     $id_factoring=$res->fields["id_factoring"];
     $campos="id_factoring,nombre,direccion,localidad,codigo_postal,icq_msm,fax,id_distrito,telefono,mail";
     $values="$id_factoring,'$nombre','$direccion','$localidad','$codigo_postal','$icq_msm','$fax',$id_distrito,'$telefono','$mail'";
     $sql=" insert into factoring ($campos) values ($values)";
     sql($sql) or fin_pagina();
     if ($db->completetrans()) $msg="Se inserto con éxito el Factoring";
                            else $msg="Error: No se pudo insertar el Factoring";
    }
echo $html_header;
if ($msg) Aviso($msg);



if ($id_factoring)
    {
    $sql="select f.*,d.id_distrito
                 from licitaciones.factoring f
                 join licitaciones.distrito d using(id_distrito)
                 WHERE  f.activo=1 and id_factoring=$id_factoring";
    $factoring=sql($sql) or fin_pagina();
    $nombre=$factoring->fields["nombre"];
    $direccion=$factoring->fields["direccion"];
    $localidad=$factoring->fields["localidad"];
    $codigo_postal=$factoring->fields["codigo_postal"];
    $telefono=$factoring->fields["telefono"];
    $fax=$factoring->fields["fax"];
    $mail=$factoring->fields["mail"];
    $icq_msm=$factoring->fields["icq_msm"];
    $id_distrito=$factoring->fields["id_distrito"];
    }
?>
<br>
<form name=form1 action='<?=$_SERVER["PHP_SELF"]?>' method='post'>
<input type=hidden name=id_factoring value=<?=$id_factoring?>>
<input type=hidden name=accion value=<?=$accion?>>
  <table width=75% align=center class=bordes bgcolor="<?=$bgcolor2?>">
    <tr id=mo><td> Datos del Factoring </td></tr>
    <tr>
       <td width=100% align=center>
           <table width=100% align=center>
               <tr>
                 <td id=ma_sf width=20%>Nombre</td>
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
<?
if ($accion!="nuevo") $onclick="onclick='document.location.href=\"listado_factoring.php\"'";
              else $onclick="onclick='window.close()'";
?>
    <tr>
      <td width=100% align=center>
      <table width=50% align=center>
         <tr>
            <td><input type=submit name=aceptar value=Aceptar style='width:150'></td>
            <td><input type=button name=cancelar value=Cancelar style='width:150' <?=$onclick?>></td>

         </tr>
      </table>
      </td>
    </tr>
 </table>
 <br>
<?
if ($id_factoring) {
?>
  <table width=90% align=center class=bordes>
    <tr>
    <?
    $link=encode_link("contactos_factoring.php",array("id_factoring"=>$id_factoring,"accion"=>"nuevo"));
    ?>
      <td><input type=button name="nuevo_contacto" value="Nuevo Contacto" onclick="window.open('<?=$link?>')"></td>
    </tr>
    <tr>
       <td id=mo>Contactos</td>
    </tr>
    <?
    $sql="select * from contactos_factoring where id_factoring=$id_factoring";
    $contactos=sql($sql) or fin_pagina();

    ?>
    <tr>
       <td>
          <table width=100% align=center>

             <tr id=ma>
                <td width=1%></td>
                <td>Nombre</td>
                <td>Dirección</td>
                <td>Telefono</td>
                <td>Mail</td>
                <td>ICQ/MSM</td>
             </tr>

             <?
             for ($i=0;$i<$contactos->recordcount();$i++){

                 $id_contactos_factoring=$contactos->fields["id_contactos_factoring"];
                 $link=encode_link("contactos_factoring.php",array("id_contactos_factoring"=>$id_contactos_factoring,"id_factoring"=>$id_factoring));
             ?>
               <a href='<?=$link?>' target='_blanck'>
               <tr <?=atrib_tr()?>>
                  <td><input  class='estilos_check' type=checkbox name=chk_contactos[] value='<?=$id_contactos_factoring?>'></td>
                  <td><?=$contactos->fields["nombre"]?></td>
                  <td><?=$contactos->fields["direccion"]?></td>
                  <td><?=$contactos->fields["telefono"]?></td>
                  <td><?=$contactos->fields["mail"]?></td>
                  <td><?=$contactos->fields["icq_msm"]?></td>
               </tr>
               </a>
             <?
             //echo "<tr><td>$id_contactos_factoring</td></tr>";
             $contactos->movenext();
             }
             ?>
           <tr>
             <td colspan=6 align=left><input type=submit name='borrar_contactos' value='Borrar Contactos'></td>
           </tr>
          </table>
       </td>
    </tr>
   </table>
    <?
     }
    ?>

</form>
<?
echo fin_pagina();
?>