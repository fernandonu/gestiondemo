<? 
require_once("../../config.php");
include_once ("funciones.php");

if ($_POST['guardar'])  {
	
$id_subir=$_POST['id_subir'];
$venc_orden=fecha_db($_POST['venc_orden']);	
$ini_orden=fecha_db($_POST['ini_orden']);
$fin_compras=fecha_db($_POST['fin_compras']);
$ini_compras=fecha_db($_POST['ini_compras']);
$fin_cdr=fecha_db($_POST['fin_cdr']);
$ini_cdr=fecha_db($_POST['ini_cdr']);
$fin_entrega=fecha_db($_POST['fin_entrega']);
$ini_entrega=fecha_db($_POST['ini_entrega']);
$comentario=$_POST['comentario'];
$id_entrega_estimada=$_POST['id_entrega_estimada'];


if ($_POST['avance_compra']) $avance_compra=$_POST['avance_compra'];
else $avance_compra=0;
if ($_POST['avance_cdr']) $avance_cdr=$_POST['avance_cdr'];
else $avance_cdr=0;
$fecha_actual=$fech=date("Y-m-d H:i:s",mktime()); //fecha actual
 
 if (compara_fechas($venc_orden,$ini_orden) < 0) 
    Error ("La fecha de vencimiento del seguimiento debe se mayor que la fecha de inicio del seguimiento");
 
 if (compara_fechas($fin_compras,$ini_compras) < 0) 
    Error ("La fecha de vencimiento de las compras debe ser mayor que la fecha de inicio de compras ");
 
 if (compara_fechas($fin_cdr,$ini_cdr) < 0 ) 
    Error ("La fecha de vencimiento de armado cdr debe se mayor que la fecha de inicio del armado de cdr");
 
 if (compara_fechas($fin_entrega,$ini_entrega) < 0) 
    Error ("La fecha de vencimiento de entrega debe se mayor que la fecha de inicio de entregas ");

 if (compara_fechas($ini_entrega,$fin_cdr) < 0) 
    Error ("La fecha de inicio de entrega debe se mayor que la fecha de fin del armado de cdr");

 if (compara_fechas($ini_cdr,$fin_compras) < 0) 
    Error ("La fecha de inicio de armado de cdr debe se mayor que la fecha de finalizacion de las compras");

if (!$error) { 
	$db->StartTrans();
	$sql_update="update subido_lic_oc set vence_oc='$venc_orden', 	
           ini_compra='$ini_compras', fin_compra='$fin_compras', 
	       ini_cdr='$ini_cdr', fin_cdr='$fin_cdr',
	       ini_entrega='$ini_entrega', fin_entrega='$fin_entrega',
	       avance_cdr=$avance_cdr, avance_compra=$avance_compra,modificado=1,comentario_gantt='$comentario'
	       where id_subir=$id_subir ";
	
	
	 sql($sql_update) or fin_pagina();
	 
	 $cambio=0;
	 $sql="";
	 //si se cambia la fecha de vencimiento del seguimiento
	 
	 if (compara_fechas(fecha_db($_POST['h_vence_oc']),$venc_orden) !=0 ) {
	 	$cambio=1;
	    $sql[]="insert into log_cambio_fecha 
	                (usuario,fecha,tipo,comentario,id_entrega_estimada) values 
	                ('$_ses_user[name]','$fecha_actual',2,'Fecha Vencimiento Seguimiento',$id_entrega_estimada)";
	 }
	 
	 //cambia fecha finalizacion de compras
	 if (compara_fechas(fecha_db($_POST['h_fin_compra']),$fin_compras) !=0 ) {
	 $sql[]="insert into licitaciones.log_parametros_gantt
	          (tipo,usuario,fecha_cambio,fecha_anterior,id_subir) values 
	          (1,'$_ses_user[name]','$fecha_actual','".fecha_db($_POST['h_fin_compra'])."',$id_subir)";
	 }
 	
	 //cambia fecha inicio cdr
	 if (compara_fechas(fecha_db($_POST['h_ini_cdr']),$ini_cdr) !=0 ) {
	 $sql[]="insert into licitaciones.log_parametros_gantt
	          (tipo,usuario,fecha_cambio,fecha_anterior,id_subir) values 
	          (3,'$_ses_user[name]','$fecha_actual','".fecha_db($_POST['h_ini_cdr'])."',$id_subir)";
	 }
	 
	  //cambia fecha fin cdr
	 if (compara_fechas(fecha_db($_POST['h_fin_cdr']),$fin_cdr) !=0 ) {
	 $sql[]="insert into licitaciones.log_parametros_gantt
	          (tipo,usuario,fecha_cambio,fecha_anterior,id_subir) values 
	          (4,'$_ses_user[name]','$fecha_actual','".fecha_db($_POST['h_fin_cdr'])."',$id_subir)";
	 }
	 
	  //cambia fecha inicio_entregas
	 if (compara_fechas(fecha_db($_POST['h_ini_entrega']),$ini_entrega) !=0 ) {
	 $sql[]="insert into licitaciones.log_parametros_gantt
	          (tipo,usuario,fecha_cambio,fecha_anterior,id_subir) values 
	          (5,'$_ses_user[name]','$fecha_actual','".fecha_db($_POST['h_ini_entrega'])."',$id_subir)";
	 }
	 
	  //cambia fecha fin_entregas
	 if (compara_fechas(fecha_db($_POST['h_fin_entrega']),$fin_entrega) !=0 ) {
	 $sql[]="insert into licitaciones.log_parametros_gantt
	          (tipo,usuario,fecha_cambio,fecha_anterior,id_subir) values 
	          (6,'$_ses_user[name]','$fecha_actual','".fecha_db($_POST['h_fin_entrega'])."',$id_subir)";
	 }
 	
	 //cambia pordentaje avance compras
	 $h_avance_compra=$_POST['h_avance_compra'];
	if ($h_avance_compra != $avance_compra) {
	 	$sql[]="insert into licitaciones.log_porcentaje_gantt
	          (tipo,usuario,fecha_cambio,porcentaje_anterior,id_subir) values 
	          (7,'$_ses_user[name]','$fecha_actual',$h_avance_compra,$id_subir)";
	 }
	 
	 //cambia porcentaje avance armado cdr
	 $h_avance_cdr=$_POST['h_avance_cdr'];
	if ( $h_avance_cdr != $avance_cdr) {
	 	$sql[]="insert into licitaciones.log_porcentaje_gantt
	          (tipo,usuario,fecha_cambio,porcentaje_anterior,id_subir) values 
	          (8,'$_ses_user[name]','$fecha_actual',$h_avance_cdr,$id_subir)";
	 }
	 
	 if ($sql!="") 
	       sql($sql) or fin_pagina();
	 $db->CompleteTrans();
	}

}

$id_subir=$parametros['id_subir'] or $id_subir=$_POST['id_subir'];
  
 $sql="SELECT licitacion.id_licitacion,entrega_estimada.nro,entrega_estimada.id_entrega_estimada,modificado,
       entidad.nombre as nombre_entidad,distrito.nombre as nombre_distrito, distrito.id_distrito,comentario_gantt,
       subido_lic_oc.nro_orden as numero,subido_lic_oc.id_subir,subido_lic_oc.vence_oc as vence,
       subido_lic_oc.fecha_subido,subido_lic_oc.avance_compra,subido_lic_oc.avance_cdr,
       entrega_estimada.fecha_estimada,ini_compra, fin_compra, ini_cdr,fin_cdr,ini_entrega,fin_entrega
	   FROM (licitaciones.licitacion 
	   LEFT JOIN licitaciones.entidad USING (id_entidad)) 
	   LEFT JOIN licitaciones.distrito USING (id_distrito) 
	   LEFT JOIN licitaciones.entrega_estimada USING (id_licitacion) 
	   LEFT JOIN licitaciones.subido_lic_oc using (id_entrega_estimada)
	   WHERE id_subir=$id_subir";
$res=sql($sql) or fin_pagina();

$comentario=$res->fields['comentario_gantt'];
$licitacion=$res->fields['id_licitacion'];
$id_entrega_estimada=$res->fields['id_entrega_estimada']; 
$nro=$res->fields['nro'];
$oc=$res->fields["numero"];
$cliente=$res->fields['nombre_entidad'];
$id_distrito=$res->fields['id_distrito'];
$ini_oc=Fecha($res->fields['fecha_subido']);
if ($res->fields['avance_compra']!=NULL)
   $avance_compra=$res->fields['avance_compra'];
   else $avance_compra=0;
if ($res->fields['avance_cdr'] !=NULL) $avance_cdr=$res->fields['avance_cdr'];
else $avance_cdr=0;
$modificado=$res->fields['modificado'];

//valores de la fechas para el diagrama de gantt

$vence_oc=fecha($res->fields['vence']);  //vencimiento del seguimiento

if ($res->fields['fin_entrega'] !=NULL || $res->fields['fin_entrega']!="" ) 
 $fin_entrega=fecha($res->fields['fin_entrega']);
else //por defecto es la fecha de vencimiento del seguimiento 
 $fin_entrega=$vence_oc;

 
//ini_compras 
 
 if ($res->fields['ini_compra'] !=NULL || $res->fields['ini_compra'] !="") {
    $ini_compra=fecha($res->fields['ini_compra']);
  }
  else {
      //selecciona la fecha de inicio de la primer orden de compra del seguimiento
      $sql_c="select min(fecha) as ini_compra from compras.orden_de_compra where id_licitacion=$licitacion and id_subir=$id_subir and estado <> 'n'";
      $res_c=sql($sql_c) or fin_pagina();
      if ($res_c->fields['ini_compra']!=NULL || $res_c->fields['ini_compra']!="" ) {
          $ini_compra=fecha(substr($res_c->fields['ini_compra'],0,10));
       }
      else {  //selecciona la fecha de inicio de la primer orden de compra de la licitacion
       $sql_c="select min(fecha) as ini_compra from compras.orden_de_compra where id_licitacion=$licitacion and estado <> 'n'";
       $res_c=sql($sql_c) or fin_pagina();
        if ($res_c->fields['ini_compra']!=NULL || $res_c->fields['ini_compra']!="") {
                 $ini_compra=fecha(substr($res_c->fields['ini_compra'],0,10));
        }
                 else {
                    $ini_compra=$ini_oc;  //si no tiene orden de compra busca la fecha de subido del archivo de autorizacion orden de compra 
                    }
   	  }
   	}
   
     
   if (compara_fechas(fecha_db($ini_compra),fecha_db($ini_oc)) < 0)  {// si la fecha ini_compras es menor a la vencida
       $ini_compra=$ini_oc;
   }    	
   	
//ini_entrega

if ($res->fields['ini_entrega'] !=NULL || $res->fields['ini_entrega'] !="") 
 $ini_entrega=fecha($res->fields['ini_entrega']);
 else  {
 //un dia de entrega para id_distrito=12 => Prov Bs As
 //2 dias de entregas en otro distrito
   if ($id_distrito==12) 
       $ini_entrega=dias_habiles_anteriores($fin_entrega,1); //resta 1 dias
   else  
       $ini_entrega=dias_habiles_anteriores($fin_entrega,2); //resta 2 dia
 }
  
//fin_cdr 
if ($res->fields['fin_cdr'] !=NULL || $res->fields['fin_cdr'] !="") 
 $fin_cdr=fecha($res->fields['fin_cdr']);
 else {
 $fin_cdr=$ini_entrega;
 }
  
//ini_cdr 
if ($res->fields['ini_cdr'] !=NULL || $res->fields['ini_cdr']!="") 
 $ini_cdr=fecha($res->fields['ini_cdr']);
 else {
 	if ($ini_cdr == "" || $ini_cdr == NULL) {
      	$sql_cdr="select sum (renglones_oc.cantidad) as cantidad from licitaciones.renglones_oc 
                  join licitaciones.renglon using (id_renglon) where id_subir=$id_subir and 
                  (tipo='Computadora Enterprise' or tipo='Computadora Matrix')";
        $res_cdr=sql($sql_cdr) or fin_pagina();
        if ($res_cdr->fields['cantidad'] !=NULL && $res_cdr->fields['cantidad']!="") {
        	$cantidad=$res_cdr->fields['cantidad'];
            $sql_cant="select cant_dias from licitaciones.dias_armado_cdr where $cantidad between lim_inf and lim_sup";
            $res_cant=sql($sql_cant) or fin_pagina();
            $dias=$res_cant->fields['cant_dias']; 
            $ini_cdr=dias_habiles_anteriores($fin_cdr,$dias);
        }
        else {
        $ini_cdr=$ini_entrega;
        }
      }
      
 }	
 
 //fin_compra

if ($res->fields['fin_compra'] !=NULL || $res->fields['fin_compra']!="") 
 $fin_compra=fecha($res->fields['fin_compra']);
 else 
 $fin_compra=$ini_cdr;

 
echo $html_header;
cargar_calendario();
?>


<form action="parametros_gantt.php" name='form1' method="post">
<input type="hidden" name="id_subir" value="<?=$id_subir?>">
<input type="hidden" name="id_entrega_estimada" value="<?=$id_entrega_estimada?>">
<input type="hidden" name="h_vence_oc" value="<?=$vence_oc?>">	<!--vencimiento del seguimineto-->
<input type="hidden" name="h_fin_compra" value="<?=$fin_compra?>">	
<input type="hidden" name="h_ini_cdr" value="<?=$ini_cdr?>">	
<input type="hidden" name="h_fin_cdr" value="<?=$fin_cdr?>">	
<input type="hidden" name="h_ini_entrega" value="<?=$ini_entrega?>">
<input type="hidden" name="h_fin_entrega" value="<?=$fin_entrega?>">
<input type="hidden" name="h_avance_compra" value="<?=$avance_compra?>">
<input type="hidden" name="h_avance_cdr" value="<?=$avance_cdr?>">

<?
if ($modificado!=0) {
$sql_log="select usuario,fecha_cambio,descripcion 
          from licitaciones.log_parametros_gantt 
          join licitaciones.tipos_log using (tipo)
          where id_subir=$id_subir";
$res=sql($sql_log) or fin_pagina();

$sql_log1="select usuario,fecha_cambio,descripcion from licitaciones.log_porcentaje_gantt
           join licitaciones.tipos_log using (tipo)
           where id_subir=$id_subir";
$res1=sql($sql_log1) or fin_pagina();

$sql_log2="select usuario,fecha as fecha_cambio,comentario as descripcion
           from licitaciones.log_cambio_fecha
           join licitaciones.subido_lic_oc  using(id_entrega_estimada)
           where id_subir=$id_subir";
$res2=sql($sql_log2) or fin_pagina();
?>
<!-- tabla de log estado entregada de renglones_oc-->
<div style="overflow:auto;height:35;" >
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<? while (!$res->EOF) {?>
<tr> <td><b>Usuario: </b><?=$res->fields['usuario']?> </td>
 <td><b>Fecha:</b><?=Fecha($res->fields['fecha_cambio'])?> </td>
 <td><b>Descripcion: </b><?=$res->fields['descripcion']?> </td></tr>
<? $res->MoveNext();}?>
<? while (!$res1->EOF) {?>
<tr> <td><b>Usuario: </b><?=$res1->fields['usuario']?> </td>
 <td><b>Fecha:</b><?=Fecha($res1->fields['fecha_cambio'])?> </td>
 <td><b>Descripcion: </b><?=$res1->fields['descripcion']?> </td></tr>
<? $res1->MoveNext();}?>
<? while (!$res2->EOF) {?>
<tr> <td><b>Usuario: </b><?=$res2->fields['usuario']?> </td>
 <td><b>Fecha: </b><?=Fecha($res2->fields['fecha_cambio'])?> </td>
 <td><b>Descripcion: </b><?=$res2->fields['descripcion']?> </td></tr>
<? $res2->MoveNext(); }?>
</table>
<br>

</div>
<hr>
<? }

?>
<table align="center" width="95%">
<tr>
<td><b>LICITACION CON  ID </b> <?=$licitacion?> <b> SEG N°</b> <?=$nro?></td>
<td colspan="2"> <b>ORDEN DE COMPRA: </b><?=$oc?></td>
</tr>
<tr>
<td colspan=3><b>CLIENTE: </b><?=$cliente?></td>
</tr>
</table>
<br>
<table align="center" cellpadding="4" cellspacing="4">
<tr id="mo" bgcolor="<?=$bgcolor3?>"><td colspan="4">Cambiar parametros para el Diagrama de Gantt</td></tr>
<tr bgcolor=<?=$bgcolor_out?>>
  <td align="right"> <b>Seguimiento</b> </td>
  <td >Fecha Inicio: <input type="text" name="ini_orden" readonly  size="10" value="<?=$ini_oc?>"> </td>
  <td >Vencimiento:<input type="text" name="venc_orden" readonly  size="10" value="<? if (!$error) echo $vence_oc; else echo $_POST['venc_orden']?>"> <?=link_calendario("venc_orden");?> </td>
  <td></td>
</tr>
<tr bgcolor=<?=$bgcolor_out?>>
  <td align="right"><b>Compras</b></td>
  <td>Fecha Inicio: <input type="text" name="ini_compras" readonly size="10" value="<?=$ini_compra;?>"></td>
  <td>Fecha Fin:&nbsp;&nbsp;&nbsp; <input type="text" name="fin_compras" readonly size="10" value=<? if (!$error) echo $fin_compra; else echo $_POST['fin_compras']?>> <?=link_calendario("fin_compras");?></td>
  <td>Avance 
  <select name="avance_compra">
    <? $i=0;
     while ($i<=10) { ?>
        <option value="<?=($i*10)?>" <? if (!$error && $avance_compra==($i*10)) echo 'selected'; elseif ($error && $_POST['avance_compra']==($i*10)) echo 'selected' ?>><? echo ($i*10)."%"?></option>
     <? $i++;
     }?>
   </select></td>
 </tr>
<tr bgcolor=<?=$bgcolor_out?>>
  <td align="right"><b>Armado CDR</b></td>
   <td>Fecha Inicio: <input type="text" name="ini_cdr" readonly  size="10" value=<? if (!$error) echo $ini_cdr; else echo $_POST['ini_cdr']?>> <?=link_calendario("ini_cdr");?></td>
   <td>Fecha Fin:&nbsp;&nbsp;&nbsp; <input type="text" name="fin_cdr"  size="10" readonly value=<? if (!$error) echo $fin_cdr; else echo $_POST['fin_cdr']?>> <?=link_calendario("fin_cdr");?></td>
    <td>Avance
        <select name="avance_cdr">
	   
        <? $i=0;
           while ($i<=10) { ?>
                  <option value="<?=($i*10)?>" <? if (!$error && $avance_cdr==$i*10) echo 'selected'; elseif ($error && $_POST['avance_cdr']==$i*10) echo 'selected' ?> ><? echo ($i*10)."%"?></option>
             <? $i++;
            }?>
          
       </select></td>
</tr>
<tr bgcolor=<?=$bgcolor_out?> >
  <td align="right"><b>Entregas</b></td>
   <td>Fecha Inicio: <input type="text" name="ini_entrega"  size="10" readonly value=<? if (!$error) echo $ini_entrega; else echo $_POST['ini_entrega']?>> <?=link_calendario("ini_entrega");?></td>
   <td>Fecha Fin:&nbsp;&nbsp;&nbsp; <input type="text" name="fin_entrega"  size="10" readonly value=<? if (!$error) echo $fin_entrega; else echo $_POST['fin_entrega']?>> <?=link_calendario("fin_entrega");?></td>
   <td></td>
</tr>
<tr bgcolor=<?=$bgcolor_out?> ><td colspan="4">
<table>
<tr><td><b> Comentario </b></td></tr>
<tr><td align="center"><textarea name='comentario' cols='70' rows='2' wrap='VIRTUAL'  ><?if (!$error) echo $comentario; else echo $_POST['comentario']?></textarea></td></tr>
</td>
</table>
</tr>
</table>
<br>

<div align="center">
<input type="submit" name="guardar" value='Guardar'>
<input type="button" name="cerrar" value='Cerrar' onclick="<? if ($cambio==1){?> window.opener.document.form1.submit(); <?}?>window.close();">

</div>
</form>
<br>
<?

$link=encode_link("prod_graficas.php",array("id_subir"=>$id_subir));
    echo "<div align='center'><img src='$link'  border=0 align=top></div>\n";

?>


