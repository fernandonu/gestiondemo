<?
/*
AUTOR: Fernando
FECHA: 29/05/2006

$Author: marco_canderle $
$Revision: 1.24 $
$Date: 2006/07/07 14:13:06 $
*/

require_once("../../config.php");

$id_licitacion=$_POST["id_licitacion"] or $id_licitacion=$parametros["id_licitacion"];
$usuario=$_ses_user["name"];
$fecha=date("Y-m-d H:i:s");


if ($_POST["guardar"] || $_POST["aceptar"]){
    $ingresar_log=1;
    $db->starttrans();
    ($_POST["chk_mantenimiento_de_oferta"])? $chk_mantenimiento_oferta=1: $chk_mantenimiento_oferta=0;
    ($_POST["chk_forma_de_pago"])? $chk_forma_de_pago=1: $chk_forma_de_pago=0;
    ($_POST["chk_plazo_de_entrega"])? $chk_plazo_de_entrega=1: $chk_plazo_de_entrega=0;
    ($_POST["chk_comentarios"])? $chk_comentarios=1: $chk_comentarios=0;
    ($_POST["chk_perfil"])? $chk_perfil=1: $chk_perfil=0;
    ($_POST["chk_precios_pegados"])? $chk_precios_pegados=1: $chk_precios_pegados=0;
    ($_POST["chk_titulo_cantidades"])? $chk_titulo_cantidades=1: $chk_titulo_cantidades=0;
    ($_POST["chk_economica_va_de_basica"])? $chk_economica_va_de_basica=1: $chk_economica_va_de_basica=0;
    ($_POST["chk_alternativa_economica"])? $chk_alternativa_economica=1: $chk_alternativa_economica=0;
    ($_POST["chk_analizo_entidad"])? $chk_analizo_entidad=1: $chk_analizo_entidad=0;
    ($_POST["chk_portenia_argentina"])? $chk_portenia_argentina=1: $chk_portenia_argentina=0;

	if ($_POST["obligatorio_cotizar_items"]==1)  $obligatorio_cotizar_items=1;
	if ($_POST["obligatorio_cotizar_items"]==2)  $obligatorio_cotizar_items=2;
	if (!$_POST["obligatorio_cotizar_items"])    $obligatorio_cotizar_items=0;

    if ($_POST["cotizar_dolares"]==1) $cotizar_dolares=1;
    if ($_POST["cotizar_dolares"]==2) $cotizar_dolares=2;
	if (!$_POST["cotizar_dolares"]) $cotizar_dolares=0;

    if ($id_verificacion_final=$_POST["id_verificacion_final"]){
           //realizo la modificacion
           $values=" confirmar_mantenimiento_de_oferta=$chk_mantenimiento_oferta,";
           $values.=" confirmar_forma_de_pago=$chk_forma_de_pago,";
           $values.=" confirmar_plazo_de_entrega=$chk_plazo_de_entrega,";
           $values.=" cotizar_dolares=$cotizar_dolares,leer_comentarios=$chk_comentarios,leer_perfil=$chk_perfil,";
           $values.=" confirmar_precios_pegados=$chk_precios_pegados,";
           $values.=" confirmar_titulo_cantidad_renglones=$chk_titulo_cantidades,";
           $values.=" economica_basica=$chk_economica_va_de_basica,";
           $values.=" alternativa_economica=$chk_alternativa_economica,";
           $values.=" analizo_entidad=$chk_analizo_entidad,";
           $values.=" obligatorio_cotizar_todos_items=$obligatorio_cotizar_items";

           $sql=" update verificacion_final set $values where id_verificacion_final=".$id_verificacion_final;
           $res=sql($sql) or fin_pagina();
           if ($_POST["guardar"])
		           $accion=" Modificación de la verificación final";
				   else
				   $ingresar_log=0;

      }
      else{
      //realizo la insercion
      $sql=" select nextval('verificacion_final_id_verificacion_final_seq') as id_verificacion_final";
      $res=sql($sql) or fin_pagina();
      $id_verificacion_final=$res->fields["id_verificacion_final"];


      $campos=" id_licitacion,id_verificacion_final,";
      $values.=" $id_licitacion,$id_verificacion_final,";
      $campos.="confirmar_mantenimiento_de_oferta,confirmar_forma_de_pago,confirmar_plazo_de_entrega,";
      $values.="$chk_mantenimiento_oferta,$chk_forma_de_pago,$chk_plazo_de_entrega,";
      $campos.="cotizar_dolares,leer_comentarios,leer_perfil,";
      $values.="$cotizar_dolares,$chk_comentarios,$chk_perfil,";
      $campos.="confirmar_precios_pegados,confirmar_titulo_cantidad_renglones,economica_basica,";
      $values.="$chk_precios_pegados, $chk_titulo_cantidades,$chk_economica_va_de_basica,";
      $campos.="alternativa_economica,analizo_entidad,";
      $values.="$chk_alternativa_economica,$chk_analizo_entidad,";
      $campos.="obligatorio_cotizar_todos_items";
      $values.="$obligatorio_cotizar_items";
      $sql=" insert into verificacion_final ($campos) values ($values)";
      $res=sql($sql) or fin_pagina();

      $accion=" Creación de la verificación final";
     }

    //inserto el log corresponiendte
	if ($ingresar_log){
        $sql=" insert into log_verificacion_final (id_verificacion_final,usuario,fecha,accion) values ($id_verificacion_final,'$usuario','$fecha','$accion')";
        sql($sql) or fin_pagina();
	}

   $db->completetrans();
   Aviso ("Se modificó el protocolo con éxito");

}   //del post de aceptar


if ($_POST["aceptar"]){
     $id_verificacion_final=$_POST["id_verificacion_final"];

	 //traigo los datos que pusieron en la licitacion
	 $sql=" select mantenimiento_oferta,mant_oferta_especial,forma_de_pago,plazo_entrega from licitaciones.licitacion
		    where licitacion.id_licitacion=$id_licitacion";
     $res = sql($sql) or fin_pagina();
     $mantenimiento_oferta = $res->fields["mantenimiento_oferta"];
     $mant_oferta_especial = $res->fields["mant_oferta_especial"];
     $forma_de_pago = $res->fields["forma_de_pago"];
     $plazo_entrega = $res->fields["plazo_entrega"];

     ($mantenimiento_oferta!="")?$mantenimiento_oferta=" $mantenimiento_oferta días $mant_oferta_especial":$mantenimiento_oferta="$mant_oferta_especial";
	 //traigo los datos del protocolo

	 $sql=" select * from licitaciones.verificacion_final
            where id_licitacion=$id_licitacion";
     $res=sql($sql) or fin_pagina();
      $tabla=" <table width=100% border=1 cellpading=0 cellspacing=0><tr><td> Confirmar Mantenimiento de Oferta : <b> $mantenimiento_oferta </b></td><td>";
	  ($res->fields["confirmar_mantenimiento_de_oferta"])?$tabla.=" Si </td></tr> ":$tabla.=" No </td></tr>";

	  $tabla.="<tr><td> Confirmar Forma de Pago: <b> $forma_de_pago </b> </td><td>";
	  ($res->fields["confirmar_forma_de_pago"])?$tabla.=" Si </td>":$tabla.=" No </td>";

	  $tabla.="<tr><td> Confirmar Plazo de Entrega: <b> $plazo_entrega </b> </td><td>";
	  ($res->fields["confirmar_plazo_de_entrega"])?$tabla.=" Si </td></tr>":$tabla.=" </td></tr>";

	   $tabla.="<tr><td> Cotizar Dolares: </td><td>";
      ($res->fields["cotizar_dolares"]==1)?$tabla.=" Si </td></tr> ":$tabla.=" No </td></tr>";

	  $tabla.="<tr><td>Leer Comentarios: </td><td>";
      ($res->fields["leer_comentarios"])?$tabla.=" Si </td></tr> ":$tabla.=" No </td></tr>";

	  $tabla.="<tr><td>Leer Perfil: </td><td>";
      ($res->fields["leer_perfil"])?$tabla.=" Si </td></tr> ":$tabla.=" No </td></tr>";

	  $tabla.="<tr><td>Confirmar Precios Pegados: </td><td>";
      ($res->fields["confirmar_precios_pegados"])?$tabla.=" Si </td></tr> ":$tabla.=" No </td></tr>";

	  $tabla.="<tr><td>Confirmar Título Cantidad Renglones: </td><td>";
      ($res->fields["confirmar_titulo_cantidad_renglones"])?$tabla.=" Si </td></tr> ":$tabla.=" No </td></tr>";

      $tabla.="<tr><td>Económica Basica: </td><td>";
      ($res->fields["economica_basica"])?$tabla.=" Si </td></tr> ":$tabla.=" No </td></tr>";

	  $tabla.="<tr><td>Alternativa Económica: </td><td>";
      ($res->fields["alternativa_economica"])?$tabla.=" Si </td></tr> ":$tabla.=" No </td></tr>";

	  $tabla.="<tr><td>Analizó Entidad: </td><td>";
      ($res->fields["analizo_entidad"])?$tabla.=" Si </td></tr> ":$tabla.=" No </td></tr>";

	  $tabla.="<tr><td>Obligatorio cotizar todos los items: </td><td>";
      ($res->fields["obligatorio_cotizar_todos_items"]==1)?$tabla.=" Si </td></tr> ":$tabla.=" No </td></tr>";

	 $tabla.="</table>";

     $para="adrian@coradir.com.ar";
	 $asunto=" Se termino de cargar el protocolo correspondiente a la Licitacion: $id_licitacion";
	 $desc="<table width=70% align=left>";
	 $desc.=" <tr><td><b> El usuario  $usuario, acepto el  protocolo para el Id: $id_licitacion  </b></td></tr>";
	 $desc.=" <tr><td><b> Protocolo  </b></td></tr>";
     $desc.=" <tr><td> $tabla  </td></tr>";
	 enviar_mail_html($para,$asunto,$desc,0,0,0);

	 $sql=" insert into log_verificacion_final (id_verificacion_final,usuario,fecha,accion) values ($id_verificacion_final,'$usuario','$fecha','Termino de Cargar el Protocolo')";
     sql($sql) or fin_pagina();
	 Aviso ("Se envió el protocolo con éxito");
}


//recupero los datos de la base de datos


$sql=" select mantenimiento_oferta,mant_oferta_especial,forma_de_pago,plazo_entrega from licitaciones.licitacion
       where licitacion.id_licitacion=$id_licitacion";
$res = sql($sql) or fin_pagina();
$mantenimiento_oferta = $res->fields["mantenimiento_oferta"];
$mant_oferta_especial = $res->fields["mant_oferta_especial"];
$forma_de_pago = $res->fields["forma_de_pago"];
$plazo_entrega = $res->fields["plazo_entrega"];


$sql=" select * from licitaciones.verificacion_final
        where id_licitacion=$id_licitacion";
$res=sql($sql) or fin_pagina();

if ($res->recordcount()){


        ($res->fields["confirmar_mantenimiento_de_oferta"])?$chk_mantenimiento_oferta='checked':$chk_mantenimiento_oferta='';
        ($res->fields["confirmar_forma_de_pago"])?$chk_forma_de_pago='checked':$chk_forma_de_pago='';
        ($res->fields["confirmar_plazo_de_entrega"])?$chk_plazo_de_entrega='checked':$chk_plazo_de_entrega='';
        ($res->fields["cotizar_dolares"])?$cotizar_dolares=$res->fields["cotizar_dolares"]:$cotizar_dolares='';
        ($res->fields["leer_comentarios"])?$chk_comentarios='checked':$chk_comentarios='';
        ($res->fields["leer_perfil"])?$chk_perfil='checked':$chk_perfil='';
        ($res->fields["confirmar_precios_pegados"])?$chk_precios_pegados='checked':$chk_precios_pegados='';
        ($res->fields["confirmar_titulo_cantidad_renglones"])?$chk_titulo_cantidades='checked':$chk_titulo_cantidades='';
        ($res->fields["economica_basica"])?$chk_economica_va_de_basica='checked':$chk_economica_va_de_basica='';
        ($res->fields["alternativa_economica"])?$chk_alternativa_economica='checked':$chk_alternativa_economica='';
        ($res->fields["analizo_entidad"])?$chk_analizo_entidad='checked':$chk_analizo_entidad='';
        ($res->fields["obligatorio_cotizar_todos_items"])?$obligatorio_cotizar_items=$res->fields["obligatorio_cotizar_todos_items"]:$obligatorio_cotizar_items='';
         $id_verificacion_final=$res->fields["id_verificacion_final"];
}



if (!($chk_mantenimiento_oferta && $chk_forma_de_pago && $chk_plazo_de_entrega && $cotizar_dolares && $chk_comentarios
    && $chk_comentarios && $chk_perfil && $chk_precios_pegados && $chk_titulo_cantidades && $chk_economica_va_de_basica
	&& $chk_alternativa_economica && $chk_analizo_entidad && $obligatorio_cotizar_items))
    $disabled_aceptar="disabled";
else
   $disabled_aceptar="";
if ($id_verificacion_final){
    $sql=" select * from log_verificacion_final where id_verificacion_final=$id_verificacion_final order by fecha DESC";
    $log=sql($sql) or fin_pagina();

}
echo $html_header;
?>
<form name="form1" method="post" action="verificacion_final.php">
<input type=hidden name=id_verificacion_final value="<?=$id_verificacion_final?>">
<input type=hidden name=id_licitacion value="<?=$id_licitacion?>">
<?
if ($id_verificacion_final){
?>
<div align=center>
<div  style="display:'visible';width:91%;overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;'?> " id="tabla_logs" >
  <table width=100%  align="center" class="bordes" bgcolor="<?=$bgcolor2?>">
    <tr id=mo>
      <td>Fecha</td>
      <td>Usuario</td>
      <td>Acción</td>
    </tr>
    <?
    for($i=0;$i<=$log->recordcount();$i++){
    ?>
      <tr>
         <td><?=fecha($log->fields["fecha"])." ".substr($log->fields["fecha"],10,9)?></td>
         <td><?=$log->fields["usuario"]?></td>
         <td><?=$log->fields["accion"]?></td>
      </tr>
    <?
    $log->movenext();
    }
    ?>
  </table>
 </div>
 </div>
 <?
 }
 ?>
<br>
  <table width="90%" align="center" class="bordes" bgcolor="<?=$bgcolor2?>">
    <tr id=mo>
         <td> VERIFICACION FINAL DEL ID <?=$id_licitacion?></td>
    </tr>
    <tr>
      <td>
        <table width=100% align=center>
          <tr <?=atrib_tr()?>>
             <td width=85% ><b>Confirmar mant. de oferta : </b>
			 <? if ($mantenimiento_oferta!="")
 			        echo " $mantenimiento_oferta días $mant_oferta_especial";
			        else "$mant_oferta_especial"?>
			</td>
             <td width=15% align=left><input type=checkbox name='chk_mantenimiento_de_oferta' value='1' <?=$chk_mantenimiento_oferta?>>&nbsp;Ok</td>
          </tr>

          <tr <?=atrib_tr()?>>
             <td><b>Confirmar forma de pago : </b><?=$forma_de_pago?></td>
             <td align=left><input type=checkbox name='chk_forma_de_pago' value='1' <?=$chk_forma_de_pago?>>&nbsp;Ok</td>
          </tr>

          <tr <?=atrib_tr()?>>
             <td><b>Confirmar plazo de entrega : </b><?=$plazo_entrega?></td>
             <td align=left><input type=checkbox name='chk_plazo_de_entrega' value='1' <?=$chk_plazo_de_entrega?>>&nbsp;Ok</td>
          </tr>

          <tr <?=atrib_tr()?>>
             <td><b>Se puede cotizar en U$S ? </b></td>
             <?
             switch ($cotizar_dolares){
			    case 1: $cotizar_dolares_si="checked";
			            break;
				case 2: $cotizar_dolares_no="checked";
				        break;

				}


             ?>
             <td align=left>
                <input type=radio name=cotizar_dolares value='1' <?=$cotizar_dolares_si?>>Si
                <input type=radio name=cotizar_dolares value='2' <?=$cotizar_dolares_no?>>No
             </td>
          </tr>

          <tr <?=atrib_tr()?>>
             <td><b>Leer comentarios</b></td>
             <td align=left><input type=checkbox name='chk_comentarios' value='1' <?=$chk_comentarios?>>&nbsp;Ok</td>
          </tr>

          <tr <?=atrib_tr()?>>
             <td><b>Leer perfil </b></td>
             <td align=left><input type=checkbox name='chk_perfil' value='1' <?=$chk_perfil?>>&nbsp;Ok</td>
          </tr>

          <tr <?=atrib_tr()?>>
             <td><b>Confirmar precios pegados</b></td>
             <td align=left><input type=checkbox name='chk_precios_pegados' value='1' <?=$chk_precios_pegados?>>&nbsp;Ok</td>
          </tr>

          <tr <?=atrib_tr()?>>
             <td><b>Confirmar título de los renglones y cantidades ( porteña o argentina)</b></td>
             <td align=left><input type=checkbox name='chk_titulo_cantidades' value='1' <?=$chk_titulo_cantidades?>>&nbsp;Ok</td>
          </tr>

          <tr <?=atrib_tr()?>>
             <td><b>La más económica que cumple va de básica?</b></td>
             <td align=left>
                <input type=checkbox name=chk_economica_va_de_basica value='1' <?=$chk_economica_va_de_basica?>>&nbsp;Ok
             </td>
          </tr>

          <tr <?=atrib_tr()?>>
             <td><b>Cotizar alternativa mas económica </b></td>
             <td align=left><input type=checkbox name='chk_alternativa_economica' value='1' <?=$chk_alternativa_economica?>>&nbsp;Ok</td>
          </tr>

          <tr <?=atrib_tr()?>>
             <td> <b>Analizó Entidad? </b></td>
             <td align=left>
                <input type=checkbox name=chk_analizo_entidad value='1' <?=$chk_analizo_entidad?> >&nbsp;OK
             </td>

          </tr>



          <tr <?=atrib_tr()?>>
             <td><b>Es obligatorio cotizar todos los items?</b></td>
             <?
             switch ($obligatorio_cotizar_items){
			 case 1: $obligatorio_cotizar_items_si="checked";
			         break;
			 case 2:$obligatorio_cotizar_items_no="checked";
			         break;
			 }
             ?>
             <td align=left>
                <input type=radio name=obligatorio_cotizar_items value='1' <?=$obligatorio_cotizar_items_si?>>Si
                <input type=radio name=obligatorio_cotizar_items value='2' <?=$obligatorio_cotizar_items_no?>>No
             </td>
          </tr>

        </table>
      </td>
    </tr>
    <tr>
      <td align=center>
        <input type=submit name=aceptar   value=Aceptar <?=$disabled_aceptar?>>
        &nbsp;
        <input type=submit name=guardar   value=Guardar>
        &nbsp;
        <input type=button name=cerrar  value=Cerrar onclick="window.close()">
      </td>
    </tr>
  </table>
</form>