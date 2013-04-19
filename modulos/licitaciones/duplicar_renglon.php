<?
/*
$Author: mari $
$Revision: 1.15 $
$Date: 2007/01/05 19:39:21 $
*/


$nro_licitacion=$parametros["licitacion"] or $nro_licitacion=$parametros["ID"];
$id_renglon=$_POST['radio_renglon'];  //id de renglon a duplicar

$usuario_crea = $_ses_user['name'];
$usuario_time = date("Y-m-d H:i:s");


$db->StartTrans();

//recupero los datos del renglon a duplicar
$q_renglon=" select * from renglon where id_licitacion =$nro_licitacion
                                     and id_renglon = $id_renglon";
$res_r=sql($q_renglon) or fin_pagina();

$titulo=$res_r->fields['titulo'];
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
$total=$res_r->fields['total'];


//recupero el id de renglon a insertar
$q_id_renglon= "SELECT nextval('renglon_id_renglon_seq') as id";
$res_id_r= sql($q_id_renglon) or fin_pagina();
$id_renglon_nuevo = $res_id_r->fields["id"];

//inserto los datos del renglon duplicado
$q_insert="insert into renglon
              (id_renglon,id_licitacion,titulo,codigo_renglon,ganancia,cantidad,usuario,usuario_time,tipo,sin_descripcion,id_etap,resumen,total)
              values ($id_renglon_nuevo,$nro_licitacion,'$titulo','$renglon',$ganancia,$cantidad,'$usuario_crea','$usuario_time','$tipo',$sin_descripcion,$id_etap,'$resumen',$total)";

$res_nuevor= sql($q_insert)  or fin_pagina();

//selecciono los productos del renglon a duplicar
$q_prod="select * from licitaciones.producto where id_renglon=$id_renglon";
$res_prod= sql($q_prod) or fin_pagina();
while(!$res_prod->EOF) {
        $id_viejo=$res_prod->fields['id'];
        $marca=$res_prod->fields['marca'];
        $modelo=$res_prod->fields['modelo'];
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
                      (id,id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion,desc_gral,comentarios)
                      values
                      ($id,'$id_renglon_nuevo','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio','$desc_gral',";
       if ($comentarios) $q_insert_prod.="'$comentarios')" ;
                    else $q_insert_prod.="NULL)";
       sql($q_insert_prod)  or fin_pagina();

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

$res_prod->MoveNext();
}

$db->CompleteTrans();


//$link=encode_link('realizar_oferta.php',array('licitacion'=>$nro_licitacion));
//header("Location:$link");
?>