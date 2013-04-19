<?php
/*
$Author: fernando $
$Revision: 1.3 $
$Date: 2005/04/18 18:17:30 $
*/

//Ventana que me sincroniza las entidades de pymes con las entidades de el gestion

require_once("config_pymes.php");
require_once("../../config.php");




$db_pymes->starttrans();
$db->starttrans();

        //trae las sincronizados
        /*
        $sql="select empresas.*,plantas.telefono,plantas.fax,plantas.codigo_postal,plantas.nbre_loc as localidad,
                     plantas.dom_calle,plantas.dom_nro,plantas.dom_piso,
                     provincias.nbre_prov as nombre_provincia,
                     mails.mail
               from empresas
               left join plantas using(id_empresa)
               left join mails using(id_empresa)
               left join  provincias using(id_provincia)
               where sincronizado=0 and plantas.principal=1 and verificado=1 and mails.principal=1";
          */
        $sql="select e.*,p.telefono,p.fax,p.codigo_postal,p.nbre_loc as localidad,
                     p.dom_calle,p.dom_nro,p.dom_piso,
                     provincias.nbre_prov as nombre_provincia,
                     m.mail
               from  empresas.empresas e
               left join empresas.plantas p on(e.id_empresa=p.id_empresa and p.principal=1)
               left join empresas.mails m on(e.id_empresa=m.id_empresa and m.principal=1)
               left join  provincias using(id_provincia)
               where sincronizado=0 and verificado=1 and descartar=0";


        $pymes=$db_pymes->execute($sql) or die($db_pymes->errormsg()."<br>".$sql);
        $cantidad_pymes=$pymes->recordcount();
        echo "Recupero $cantidad_pymes para sincronizar....<br>";
        for($i=0;$i<$cantidad_pymes;$i++){
            $id_empresa=$pymes->fields["id_empresa"];
            $nombre=$pymes->fields["razon_social"];
            $telefono=$pymes->fields["telefono"];
            $fax=$pymes->fields["fax"];
            $direccion=$pymes->fields["dom_calle"]." ".$pymes->fields["dom_nro"]." ".$pymes->fields["dom_piso"];
            $codigo_postal=$pymes->fields["codigo_postal"];
            $localidad=$pymes->fields["localidad"];
            $mail=$pymes->fields["mail"];
            $provincia_pymes=$pymes->fields["nombre_provincia"];


            $nombre=ereg_replace("\""," ",$nombre);
            $telefono=ereg_replace("\""," ",$telefono);
            $fax=ereg_replace("\""," ",$fax);
            $direccion=ereg_replace("\""," ",$direccion);
            $localidad=ereg_replace("\""," ",$localidad);
            $mail=ereg_replace("\""," ",$mail);

            $nombre=ereg_replace("'"," ",$nombre);
            $telefono=ereg_replace("'"," ",$telefono);
            $fax=ereg_replace("'"," ",$fax);
            $direccion=ereg_replace("'"," ",$direccion);
            $localidad=ereg_replace("'"," ",$localidad);
            $mail=ereg_replace("'"," ",$mail);


            $sql="select id_distrito from distrito where nombre ilike '%$provincia_pymes%'";
            $distrito=sql($sql) or fin_pagina();
            $id_distrito=$distrito->fields["id_distrito"];
            if (!$id_distrito) $id_distrito=2;

            $sql="select id_entidad_pyme from entidad_pymes
                     where id_empresa=$id_empresa ";
            $res=sql($sql) or fin_pagina();
            if ($res->recordcount())
                              {
                               $id_entidad_pyme=$res->fields["id_entidad_pyme"];
                               $sql="update entidad_pymes set nombre='$nombre', telefono='$telefono',fax='$fax',
                                            direccion='$direccion',codigo_postal='$codigo_postal',localidad='$localidad',
                                            mail='$mail',id_distrito=$id_distrito
                                            where id_entidad_pyme=$id_entidad_pyme";
                               sql($sql) or fin_pagina();
                               $inserto=0;
                              //modifico
                              }
                              else
                              {
                               $sql="select nextval('entidades_pymes_id_entidad_pyme_seq') as id_entidad_pyme ";
                               $res_id_entidad=sql($sql) or fin_pagina();
                               $id_entidad_pyme=$res_id_entidad->fields["id_entidad_pyme"];
                               $campos="id_entidad_pyme,id_empresa, nombre,telefono,fax,direccion,codigo_postal,localidad,mail,id_distrito";
                               $values="$id_entidad_pyme,$id_empresa,'$nombre','$telefono','$fax','$direccion','$codigo_postal','$localidad','$mail',$id_distrito";
                               $sql="insert into entidad_pymes ($campos) values ($values)";
                               sql($sql) or fin_pagina();
                               $inserto=1;
                              }

         //manejo los contactos

         $sql="select * from contactos where id_empresa=$id_empresa";
         $contactos_pymes=$db_pymes->execute($sql) or die($sql."<br>".$db->errormsg());
         echo "Recupero ".$contactos_pymes->recordcount()." de entidad Nro: $i de $cantidad_pymes para sincronizar....<br>";

         /*
         //elimino las relaciones de los contactos de esa empresa en el gestion
         $sql="delete from modulos_contacto where
                    id_contacto_general in
                    (
                    select id_contacto_general from
                            contactos_generales where
                            cargado_sistema<>1
                    )
                    and
                    id_contacto_general in
                    (
                    select  id_contacto_general from
                            relaciones_contacto where entidad=$id_entidad
                    )
               ";
         sql($sql) or fin_pagina();
         $sql="delete from relaciones_contacto where entidad=$id_entidad and
                                                id_contacto_general in
                                                (
                                                select id_contacto_general from
                                                contactos_generales where
                                                cargado_sistema<>1
                                                )";
         sql($sql) or fin_pagina();
         */
          $sql="delete from contactos_generales_pymes where id_entidad_pyme=$id_entidad_pyme";
          sql($sql) or fin_pagina();

         for ($y=0;$y<$contactos_pymes->recordcount();$y++)
           {
           $id_contacto_pyme=$contactos_pymes->fields["id_contacto"];
           $nombre=$contactos_pymes->fields["ap_nbre"];
           $tel=$contactos_pymes->fields["telefono"];
           $mail=$contactos_pymes->fields["mail"];
           $fax=$contactos_pymes->fields["fax"];
           $icq=$contactos_pymes->fields["im"];


            $nombre=ereg_replace("\""," ",$nombre);
            $tel=ereg_replace("\""," ",$tel);
            $fax=ereg_replace("\""," ",$fax);
            $mail=ereg_replace("\""," ",$mail);
            $icq=ereg_replace("\""," ",$icq);

            $nombre=ereg_replace("'"," ",$nombre);
            $tel=ereg_replace("'"," ",$tel);
            $fax=ereg_replace("'"," ",$fax);
            $mail=ereg_replace("'"," ",$mail);
            $icq=ereg_replace("'"," ",$icq);

           /*
           $sql="delete from contactos_generales_pymes where id_contacto_pyme=$id_contacto_pyme";
           sql($sql) or fin_pagina();
           */
           $sql="select nextval('contactos_generales_pymes_id_contacto_general_pyme_seq') as id_contacto_general_pyme ";
           $id_c_g=sql($sql) or fin_pagina();
           $id_contacto_general_pyme=$id_c_g->fields["id_contacto_general_pyme"];

           $sql="insert into contactos_generales_pymes (id_contacto_general_pyme,id_entidad_pyme,nombre,tel,mail,fax,icq,direccion,localidad,cod_postal,id_contacto_pyme)
                   values ($id_contacto_general_pyme,$id_entidad_pyme,'$nombre','$tel','$mail','$fax','$icq','$direccion','$localidad','$codigo_postal',$id_contacto_pyme)";
           sql($sql) or fin_pagina();
           $contactos_pymes->movenext();
           }



         //modifica la empresa de pymes para decir que esta sincronizado
         $sql="update empresas set sincronizado=1 where id_empresa=$id_empresa";
         $db_pymes->execute($sql) or die($sql);
         $pymes->movenext();
        }//del for

      if ($db_pymes->completetrans() && $db->completetrans())
                                      $msg="Se sincronizaron con éxito $cantidad_pymes Entidades";
                                 else $msg="No se pude realizar la sincronización";
echo $html_header;
?>
<table width=60% class=bordes align=center>
  <tr id=mo><td>Sincronización</td><tr>
  <tr><td align=center><?=Aviso($msg)?></td></tr>

  <tr><td align=center><input type=button name=cerra value=Cerrar onclick='window.close()'></td></tr>
</table>
<?echo fin_pagina();?>