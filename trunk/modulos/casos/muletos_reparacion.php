<?
/*
Author: lizi

modificada por
$Author: fernando $
$Revision: 1.4 $
$Date: 2005/05/04 19:24:23 $
*/

require_once ("../../config.php");

$id_muleto=$parametros['id_muleto'] or $id_muleto=$_POST['id_muleto'];
$pagina=$parametros["pagina"] or $pagina=$_POST["pagina"];
$id_reem_muleto=$parametros['id_reem_muleto'] or $id_reem_muleto=$_POST['id_reem_muleto'];
$idcaso=$parametros['idcaso'] or $idcaso=$_POST['idcaso'];
$est_reem=$parametros['est_reem'] or $est_reem=$_POST['est_reem'];

if ($_POST['guardar']=="Guardar")
{    
    $db->StartTrans();

	$id_muleto=$_POST['h_id_muleto'];
    $id_reem_muleto=$_POST['h_id_reem_muleto'];
    $id_estado_reem=$_POST['estados'];
    $id_estado_reem_anterior=$_POST['estado_anterior'];
    $observaciones=$_POST['observaciones_reem'];
    $descripcion=$_POST['descripcion_reem'];
    $fecha_actual=date("Y-m-d H:i:s");
    $usuario=$_ses_user["name"];

     //controlamos que haya cambiado el estado. si cambio, insertamos el nuevo log, sino
     //dejamos todo como esta
     if($id_estado_reem!=$id_estado_reem_anterior)
     {
      $aux="select estado_reem from estados_reemplazos where id_estado_reemplazo=$id_estado_reem";
      $res_aux=sql($aux, "Error al traer el estado del reemplazo") or fin_pagina(); 
      $estado_aux=$res_aux->fields['estado_reem'];
              
      $q="update casos.reemplazos_muletos set
          descripcion_reem='$descripcion', id_estado_reemplazo=$id_estado_reem
          where id_reem_muleto=$id_reem_muleto";
      sql($q, "Error al actualizar los datos de un reemplazo") or fin_pagina();
      
      $q="select nextval('log_reem_muletos_id_log_reem_seq') as id_log_reem";
      $id_log_reem=sql($q) or fin_pagina();
      $id_log_reem=$id_log_reem->fields['id_log_reem'];
      
      $q="insert into casos.log_reem_muletos
          (id_log_reem, id_reem_muleto, usuario, fecha, tipo)
          values
          ($id_log_reem, $id_reem_muleto, '$usuario', '$fecha_actual', '$estado_aux')";
      sql($q, "Error al insertar logs para los reemplazos") or fin_pagina();
     }
      //insertamos el comentario nuevo, si es que hay uno nuevo
      if($_POST['nuevo_coment']!="")
      {$q="select nextval('comentarios_reemplazo_muletos_id_comentario_r_seq') as id_comentario_r"; 
       $nextval_coment=sql($q) or fin_pagina();  
       $id_comentario_r=$nextval_coment->fields['id_comentario_r'];
       $fecha_hoy=date("Y-m-d H:i:s",mktime());
   	   $query="insert into comentarios_reemplazo_muletos (id_comentario_r,id_reem_muleto,descripcion,
   	           usuario, fecha_comentario)
   	           values ($id_comentario_r,$id_reem_muleto,'".$_POST['nuevo_coment']."','".$_ses_user["name"]."','$fecha_hoy')";
   	   sql($query,"<br>Error al insertar el nuevo comentario<br>") or fin_pagina();
      }//de if($_POST['nuevo_coment']!="")
      
      
    $db->Completetrans();   

    $accion="Los datos se guardaron con Exito";

    $link=encode_link('muletos_listado.php',array("accion"=>$accion));
    header("Location:$link") or die();
}

$rep="select nrocaso, nserie, deperfecto, repuestos_casos.descripcion, descripcion_reem, dias_reem, 
      id_estado_reemplazo, log_reem_muletos.fecha, tipo, marca, modelo, nro_serie, 
      muletos.observaciones , comentarios_reemplazo_muletos.descripcion as observaciones_reem
      from casos_cdr
      left join repuestos_casos using (idcaso)
     left join (select *
                      from casos.reemplazos_muletos
                    where reemplazos_muletos.historial=0 or reemplazos_muletos.historial is null
                     ) as reemplazos_muletos using (idcaso)
      left join muletos using (id_muleto)
      left join estados_reemplazos using (id_estado_reemplazo)
      left join log_reem_muletos using (id_reem_muleto)
      left join comentarios_reemplazo_muletos using (id_reem_muleto)
      where id_reem_muleto=$id_reem_muleto";
     
$res_rep=sql($rep, "Error al traer los datos de los muletos 'en uso' para un caso") or fin_pagina(); 

// son las var q tienen los datos del muleto q esta relacionado con el caso = reparacion
$marca=$res_rep->fields['marca'];
$modelo=$res_rep->fields['modelo'];
$nro_serie=$res_rep->fields['nro_serie'];
$observaciones=$res_rep->fields['observaciones'];
$estado_reem_muleto=$res_rep->fields['id_estado_reemplazo'];

echo $html_header;


//echo "<br>".$est_reem."<br>";
?>

<form name='form1' action='' method='POST'>

<input type="hidden" name="h_id_muleto" value="<?=$id_muleto?>"> 
<input type="hidden" name="h_id_reem_muleto" value="<?=$id_reem_muleto?>"> 
<input type="hidden" name="h_idcaso" value="<?=$idcaso?>"> 
<table width=95% border=0 cellspacing=0 cellpadding=6 bgcolor=<?=$bgcolor2?> align="center" class="bordes">
<tr>
<td>
<table width=100% border=0 cellspacing=0 cellpadding=6 bgcolor=<?=$bgcolor2?> align="center">
 <tr><td id=mo colspan="2"><font size=+1><b> Monitor Para Reparar</b></FONT></td></tr>
 <tr>
   <td width="25%">
     <table>
       <tr><td><b>Caso Nro.<br><font color="Red"><?=$res_rep->fields['nrocaso']?></font></b></td></tr> 
       <tr><td><b>Nro. Serie<br><font color="Red"><?=$res_rep->fields['nserie']?></font></b></td></tr>
     </table>
   </td> 
   <td width="75%"><b>Desperfecto</b><br>
    <textarea style="width:100%" rows="10" name="desperfecto" readonly><?=$res_rep->fields['deperfecto'];?></textarea>
   </td>    
 </tr>
</table>
</td>
</tr>
<tr>
<td>
<table width=100% border=0 cellspacing=0 cellpadding=6 bgcolor=<?=$bgcolor2?> align="center"> 
 <tr> 
   <td align="right" colspan="2"><hr><b>Mostrar datos del Muleto</b> &nbsp;
     <input type="checkbox" name="mostrar_datos_muleto" class='estilos_check' onclick="javascript:(this.checked)?Mostrar('tabla_mail'):Ocultar('tabla_mail');">
   </td>
 </tr>
 <tr>
   <td align="center" colspan="2">
   <div id='tabla_mail' style='display:none'>
    <table align="center" width="100%" class=bordes>
      <tr id=mo>
        <td colspan=2>Datos del muleto</td>
      </tr>
      <tr>
	    <td align="left"><b> Nro. Muleto <font color="Red"><?=$id_muleto;?></font></b></td>
	    <td align="left"><b> Nro. Serie
	      <input type="text" name="nro_serie" value='<?if ($nro_serie!="") echo $nro_serie;?>' size=30 align="right" readonly></b></td>
      </tr>
      <tr><td colspan="2" id=ma><font size=+1><b> Descripción </b></font></td></tr>
      <tr> 
        <td><b> Marca </b></td>
        <td><input type="text" name="marca" value='<? if ($marca!="") echo $marca; ?>' size=50 readonly></td>
      </tr>
      <tr>   
        <td><b> Modelo </b></td>
        <td><input type="text" name="modelo" value='<? if ($modelo!="") echo $modelo; ?>' size=50 readonly></td>
      </tr>
      <tr>
        <td valign=top><b> Observaciones </b></td>
        <td ><textarea style="width:90%" rows="5" name="observaciones" readonly><? if ($observaciones!="") echo $observaciones; ?></textarea></td>
      </tr>
    </table>
    </div>
   </td>
 </tr>
</table>
</td>
</tr>
<tr>
<td>
<hr>
<table width=100% border=0 cellspacing=0 cellpadding=6 bgcolor=<?=$bgcolor2?> align="center">
 <tr> <td align=center id=ma colspan="2">
      <font size=+1><b>Datos del Monitor Original</b></font>
 </td></tr>
 <tr>
   <td valign="top">
    <table border=0 align=Center width=85%>
    <tr>
      <td><b>Descripción del Monitor Original</b></td>
      <td><b>Estado</b></td>
    </tr>
    <tr>
      <td width="70%">
       <textarea name='descripcion_reem' readonly style="width:90%" rows="5"><?=$res_rep->fields['descripcion_reem']?></textarea></td>
      <td width="30%">
      <input type="hidden" name="estado_anterior" value="<?=$estado_reem_muleto?>">
      <select name='estados' <?if ($est_reem=="En Coradir reparado") echo "disabled"?>>
       <? $q_est="select * from estados_reemplazos where mostrar=1";
       $res_q_est=sql($q_est) or fin_pagina();
       $cant_est=$res_q_est->RecordCount();
       for ($i=0;$i<$cant_est;$i++){
    	$estado_reem=$res_q_est->fields['id_estado_reemplazo'];
       ?>
        <option value="<?=$res_q_est->fields['id_estado_reemplazo']?>"
        <? if ($estado_reem_muleto==$estado_reem) echo "selected"?>>
        <?=$res_q_est->fields['estado_reem'];?>
        </option>
       <? $res_q_est->MoveNext(); }?>
      </select>
     </td>
    </tr>
    <tr>
      <td colspan="2" width=100%>
       <table align="center" width="100%" border="1">
        <tr>
         <td valign='top' colspan="2">
          <table width="100%" align="center">
           <tr id=mo>
            <td>
             <font size="3">Comentarios</font>
            </td>
           </tr>
          </table>
         </td>
        </tr>
         <?
         if($id_reem_muleto)
         {
          //traemos los comentarios de este reemplazo de muleto
          $query="select id_comentario_r,descripcion,usuario,fecha_comentario from comentarios_reemplazo_muletos where id_reem_muleto=$id_reem_muleto";
          $comentarios=sql($query,"<br>Error al traer los comentarios del muleto<br>") or fin_pagina();
          //generamos los comentarios ya cargados
          while(!$comentarios->EOF)
          {?>
           <tr>
            <td>
             <table width="100%">
              <tr  id="ma_sf">
               <td width="65%" align="right">
                <b>
                <?
                $fecha=split(" ",$comentarios->fields['fecha_comentario']);
                echo fecha($fecha[0])." ".$fecha[1]; 
                ?>
                </b>
               </td>
              </tr> 
              <tr id="ma_sf">
               <td align="right">
                 <?=$comentarios->fields['usuario']?>
                </td>
               </tr>
              </table>
             </td>
             <td align=center>
              <textarea rows="4" cols="80" readonly name="coment_<?=$comentarios->fields['id_comentario_r']?>"><?=$comentarios->fields['descripcion']?></textarea>
             </td>
            </tr>
           <?
            $comentarios->MoveNext();
           }//de while(!$comentarios->EOF)
         }//de if($id_reem_muleto)
           //y luego damos la opcion a guardar uno mas
           ?>
           <tr>
               <td  id="ma">
                <b>Nuevo Comentario</b>
               </td>
               <td align=center>
                &nbsp;<textarea rows="4" cols="80" name="nuevo_coment" <?if ($est_reem=="En Coradir reparado") echo "disabled"?>></textarea>
               </td>
           </tr>
          </table>
         </td>
        </tr>
       </table>
      </td>
    </tr>
  </table>
  </td>
 </tr>
 <tr>
   <td colspan="2" align="center" width="100%">
     <table>
      <tr>
        <td>
        <input type=submit name="guardar" value="Guardar" <?if ($est_reem=="En Coradir reparado") echo "disabled"?> title="Guardar datos del reemplazo"></td>
        <?
        $link=encode_link("muletos_admin.php", array("id_muleto"=>$id_muleto,"id_reem_muleto"=>$id_reem_muleto,"idcaso"=>$idcaso,"est_reem"=>$est_reem,"pagina"=>"muletos_reparacion.php"));
        if ($est_reem!="En Coradir reparado") $disabled="disabled";
        ?>
        <td>
         <input type="button" name="cambiar_reem" value="Ir a Muletos" onclick="document.location='<?=$link?>'" <?=$disabled?> title="Devuelve Original - Recupera Muleto">
         </td>
      </tr>
      <tr>
        <? $link=encode_link("muletos_listado.php", array());?>
        <td colspan="3" align="center">
        <input type=button name="volver" value="Volver" onclick="document.location='<?=$link?>'" title="Volver al Listado de Muletos"></td>
      </tr>
     </table>
   </td>    
 </tr>
</table>
</td>
</tr>
</table>
</form>
<?=fin_pagina();?>
