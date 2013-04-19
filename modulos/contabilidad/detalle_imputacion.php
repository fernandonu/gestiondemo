<?/*

----------------------------------------
 Autor: MAC
 Fecha: 27/09/2005
----------------------------------------

MODIFICADA POR
$Author: mari $
$Revision: 1.13 $
$Date: 2006/09/07 14:11:19 $
*/

include_once("../../config.php");
include_once("funciones.php");

$id_imputacion=$parametros["id_imputacion"] or $id_imputacion=$_POST["id_imputacion"];

//si se apreto el boton guardar o el boton Terminar, se guardan los cambios en ambos casos
if($_POST["guardar"]=="Guardar" || $_POST["terminar"]=="Terminar")
{
 $db->StartTrans();
 $msg="";

 $pago[]=array();
 $pago["tipo_pago"]=$_POST["tipo_pago"];
 $pago["id_pago"]=$_POST["id_pago"];
 if($_POST["id_banco"]!="")
  $pago["id_banco"]=$_POST["id_banco"];
 imputar_pago($pago,$id_imputacion,fecha($_POST["fecha_imputacion"]));

 if ($db->CompleteTrans()) {
 	$link=encode_link("listado_imputaciones.php",array("msg"=>$msg));
    header("location: $link");
 }
}//de if($_POST["guardar"]=="Guardar")

if($_POST["terminar"]=="Terminar")
{
 $db->StartTrans();

 //si no esta chequeada la opcion de finalizar sin discriminar, ponemos el estado Finalizado Completo
 if($_POST["finalizar_sin_discriminar"]!=1)
 {
  //traemos el id del estado "Finalizado Completo"
  $query="select estado_imputacion.id_estado_imputacion from contabilidad.estado_imputacion where estado_imputacion.nombre='Finalizado Completo'";
  $estado_imp=sql($query,"<br>Error al traer id del estado finalizado completo <br>") or fin_pagina();

  //pasamos el estado de la imputacion a Finalizado Completo
  $query="update contabilidad.imputacion set id_estado_imputacion=".$estado_imp->fields["id_estado_imputacion"]." where id_imputacion=$id_imputacion";
  sql($query,"<br>Error al terminar la imputacion con Nº $id_imputacion<br>") or fin_pagina();

  $estado="Finalizado Completo";
 }
 else
 { $estado="Finalizado Sin Discriminar";
 	//traemos el id del estado "Finalizado Completo"
   $query="select estado_imputacion.id_estado_imputacion from contabilidad.estado_imputacion where estado_imputacion.nombre='Finalizado Sin Discriminar'";
   $estado_imp=sql($query,"<br>Error al traer id del estado finalizado completo <br>") or fin_pagina();

   //pasamos el estado de la imputacion a Finalizado Completo
   $query="update contabilidad.imputacion set id_estado_imputacion=".$estado_imp->fields["id_estado_imputacion"]." where id_imputacion=$id_imputacion";
   sql($query,"<br>Error al terminar la imputacion con Nº $id_imputacion<br>") or fin_pagina();
 }

  $fecha=date("Y-m-d H:i:s",mktime());
  $tipo="se Terminó imputación";
  $detalle="La imputación se pasó a estado Terminada";
  $usuario=$_ses_user['name'];
  $campos="(tipo,detalle,fecha,usuario,id_imputacion)";
  $query_insert="INSERT INTO contabilidad.log_imputacion $campos VALUES ".
  "('$tipo','$detalle','$fecha','$usuario',$id_imputacion)";
  sql($query_insert,"No se pudo dar de alta en log imputacion")or fin_pagina();

 $msg="La Imputación Nº $id_imputacion se terminó con éxito";
 if ($db->CompleteTrans()){
    $link=encode_link("listado_imputaciones.php",array("msg"=>$msg));
    header("location: $link");
 }
}//de if($_POST["terminar"]=="Terminar")

if($_POST["volver_estado"]=="Volver Estado Pendiente/Por controlar")
{
	$db->StartTrans();
	$id_imputacion=$_POST["id_imputacion"];
	$query="select detalle_imputacion.id_tipo_imputacion from contabilidad.detalle_imputacion
			where detalle_imputacion.id_imputacion=$id_imputacion";
    $estado_imp=sql($query,"<br>Error al traer id del tipo imputacion <br>") or fin_pagina();
    $verificar=0;
    while((!$estado_imp->EOF)&&($verificar==0))
    {

    	$tempo=$estado_imp->fields['id_tipo_imputacion'];
    	if( $tempo!=7)
    	{
    		$verificar=1;
    	}
    	$estado_imp->MoveNext();

    }
    if($verificar==1)
    {
    	$query="select estado_imputacion.id_estado_imputacion from contabilidad.estado_imputacion
    			where estado_imputacion.nombre='Por Controlar'";
    	$estado_imp=sql($query,"<br>Error al traer id del tipo imputacion <br>") or fin_pagina();
    	$estad=$estado_imp->fields['id_estado_imputacion'];
    	$query="update contabilidad.imputacion set id_estado_imputacion=$estad where id_imputacion=$id_imputacion";
  		sql($query,"<br>Error al terminar la imputacion con Nº $id_imputacion<br>") or fin_pagina();

  		$campos="(tipo,detalle,fecha,usuario,id_imputacion)";
    	$fecha=date("Y-m-d H:i:s",mktime());
    	$tipo="volvio a Por Controlar";
    	$detalle="Se Volvió a estado a Por Controlar";
    	$usuario=$_ses_user['name'];
	    $query_insert="INSERT INTO contabilidad.log_imputacion $campos VALUES ".
	    "('$tipo','$detalle','$fecha','$usuario',$id_imputacion)";
	    sql($query_insert,"No se pudo dar de alta en log imputacion")or fin_pagina();
    }
    else
    {
    	$query="select estado_imputacion.id_estado_imputacion from contabilidad.estado_imputacion
    			where estado_imputacion.nombre='Pendiente'";
    	$estado_imp=sql($query,"<br>Error al traer id del tipo imputacion <br>") or fin_pagina();
    	$estad=$estado_imp->fields['id_estado_imputacion'];
    	$query="update contabilidad.imputacion set id_estado_imputacion=$estad where id_imputacion=$id_imputacion";
  		sql($query,"<br>Error al terminar la imputacion con Nº $id_imputacion<br>") or fin_pagina();

    	$campos="(tipo,detalle,fecha,usuario,id_imputacion)";
    	$fecha=date("Y-m-d H:i:s",mktime());
    	$tipo="volvió a Pendiente";
    	$detalle="Se Volvió a estado a Pendiente";
    	$usuario=$_ses_user['name'];
	    $query_insert="INSERT INTO contabilidad.log_imputacion $campos VALUES ".
	    "('$tipo','$detalle','$fecha','$usuario',$id_imputacion)";
	    sql($query_insert,"No se pudo dar de alta en log imputacion")or fin_pagina();
    }
	$db->CompleteTrans();
}

//traemos los datos de la imputacion
$query="select imputacion.id_imputacion,imputacion.id_estado_imputacion,imputacion.id_ingreso_egreso,
			imputacion.idbanco,imputacion.númeroch,imputacion.iddébito,imputacion.valor_dolar,imputacion.fecha,
	        tipo_banco.nombrebanco,estado_imputacion.nombre as estado,moneda.simbolo,caja.id_distrito,cheques.fechadébch,
	        case when ingreso_egreso.monto is not null then ingreso_egreso.monto
	             when cheques.importech is not null then cheques.importech
	             when débitos.importedéb is not null then débitos.importedéb
	             else 0
	        end as monto_imputacion,
	        case when cuenta_caja.numero_cuenta is not null then cuenta_caja.numero_cuenta
	             when cuenta_cheque.numero_cuenta is not null then cuenta_cheque.numero_cuenta
	             when cuenta_debito.numero_cuenta is not null then cuenta_debito.numero_cuenta
	             else -1
	        end as nro_cuenta_imputacion,
	        case when cuenta_caja.concepto is not null then cuenta_caja.concepto||' ['||cuenta_caja.plan||']'
	             when cuenta_cheque.concepto is not null then cuenta_cheque.concepto||' ['||cuenta_cheque.plan||']'
	             when cuenta_debito.concepto is not null then cuenta_debito.concepto||' ['||cuenta_debito.plan||']'
	             else '--'
	        end as nombre_cuenta_imputacion,
	        ingreso_egreso.item as descripcion_egreso,débitos.comentario as descripcion_debito,cheques.comentarios as descripcion_cheque
        from contabilidad.imputacion
        join contabilidad.estado_imputacion using(id_estado_imputacion)
        left join caja.ingreso_egreso using(id_ingreso_egreso)
        left join caja.caja using(id_caja)
        left join licitaciones.moneda using(id_moneda)
        left join (select * from general.tipo_cuenta) as cuenta_caja on(ingreso_egreso.numero_cuenta=cuenta_caja.numero_cuenta)
        left join bancos.tipo_banco using(idbanco)
        left join bancos.cheques using(idbanco,númeroch)
        left join (select * from general.tipo_cuenta) as cuenta_cheque on(cheques.numero_cuenta=cuenta_cheque.numero_cuenta)
        left join bancos.débitos using(iddébito)
        left join (select * from general.tipo_cuenta) as cuenta_debito on(débitos.numero_cuenta=cuenta_debito.numero_cuenta)
        where id_imputacion=$id_imputacion
        ";
$datos=sql($query,"<br>Error al traer datos de la imputacion con ID $id_imputacion<br>") or fin_pagina();

$simbolo_moneda=($datos->fields["simbolo"])?$datos->fields["simbolo"]:'$';
$monto_total=$datos->fields["monto_imputacion"];
if(!$id_imputacion)
 $id_imputacion=$datos->fields["id_imputacion"];
if(!$estado)
 $estado=$datos->fields["estado"];

if($estado=="Finalizado Completo" || $estado=="Finalizado Sin Discriminar" ||$estado=="Pago Anulado")

   $permiso_editar="disabled";


   else

   $permiso_editar="";

echo $html_header;
?>
<script src="../../lib/NumberFormat150.js"></script>

<form name='form1' method="POST" action="detalle_imputacion.php">
 <input type="hidden" name="id_imputacion" value="<?=$id_imputacion?>">
 <input type="hidden" name="estado" value="<?=$estado?>">

<div style="overflow:auto;width:100%;height:95%;position:relative">
<table align="center" width="90%">
 <tr>
  <td align="right">
   <input type="checkbox" name="ver_logs" value="1" onclick="if (this.checked) document.all.logs.style.display='block'; else document.all.logs.style.display='none';" class="estilos_check"> Mostrar logs
  </td>
 </tr>
 <tr>
  <td>
   	 <?//traemos el log de la imputacion
		$query="select log_imputacion.tipo,log_imputacion.detalle,log_imputacion.fecha,log_imputacion.usuario
				from contabilidad.log_imputacion where log_imputacion.id_imputacion=$id_imputacion
				order by log_imputacion.fecha DESC";
		$log_imputacion=sql($query,"<br>Error al traer el log de la imputación<br>") or fin_pagina();
	?>
	<div id="logs" style="display:none; overflow:auto; height:80;">
	 <table align="center" width="100%">
	  <tr id="mo">
	   <td width="20%">
	    Fecha
	   </td>
	   <td width="25%">
	    Usuario
	   </td>
	   <td align="55%">
	   	Detalle
	   </td>
	  </tr>
	   <?
	   while (!$log_imputacion->EOF)
	   {
	    ?>
	     <tr id="ma_sf">
		  <td>
			<?=fecha($log_imputacion->fields["fecha"])." ".Hora($log_imputacion->fields["fecha"])?>
		  </td>
		  <td>
		  	<?=$log_imputacion->fields["usuario"]?>
		  </td>
		  <td>
		   <?=$log_imputacion->fields["detalle"]?>
		  </td>
		 </tr>
	   <?

	   	$log_imputacion->MoveNext();
	  }//de while(!$log_imputacion->EOF)
	  ?>
	 </table>
	</div>
  </td>
 </tr>
</table>
<div align="center">
 <b><?=$msg?></b>
</div>
<table align="center" width="95%" cellpadding="2" cellspacing="2" class="bordes">
 <tr id="mo">
  <td>
   <font size="3">Detalle Imputación Nº <?=$id_imputacion?> - Estado: <?=$estado?></font>
  </td>
 </tr>
</table>
<table align="center" width="95%" class="bordes"  bgcolor="<?=$bgcolor_out?>">
 <tr>
  <td width="10%">
   <font size="2"><b>Fecha</b></font>
  </td>
  <td width="20%">
   <input type="hidden" name="fecha_imputacion" value="<?=$datos->fields["fecha"]?>">
   <font color="Blue" size="2"><b><?=Fecha($datos->fields["fecha"])?></b></font>
  </td>
  <td width="20%">
   <font size="2"><b>Cuenta </b></font>
  </td>
  <td width="50%">
   <font color="Blue" size="2"><b><?=$datos->fields["nombre_cuenta_imputacion"]?></b></font>
  </td>
 </tr>
 <tr>
  <td colspan="4">
   <hr>
  </td>
 </tr>
 <tr>
  <td>
    <font size="2"><b>Monto</b></font>
   </td>
   <td>
    <font color="Red" size="2"><b><?=$simbolo_moneda?></b></font> <font color="Blue" size="2"><b><?=formato_money($monto_total)?></b></font>
   </td>
   <td>
    <?if($datos->fields["simbolo"]=="U\$S")
      {?>
       <font size="2"><b>Valor Dolar</b></font>
      <?
      }
      else
       echo "&nbsp;";
      ?>
   </td>
   <td>
    <?if($datos->fields["simbolo"]=="U\$S")
      {?>
       <font color="Blue" size="2"><b><?=($datos->fields["valor_dolar"])?formato_money($datos->fields["valor_dolar"]):'-'?></b></font>
      <?
      }
      else
       echo "&nbsp;";
      ?>
   </td>
 </tr>
 <tr>
  <td colspan="4">
   <hr>
  </td>
 </tr>
 <?
 //dependiendo de si el pago asociado es Caja, Cheque o Débito, mostramos los datos correspondientes
 if($datos->fields["id_ingreso_egreso"]!="")//datos del egreso de caja
 {
  ?>
  <tr>
   <td>
    <input type="hidden" name="tipo_pago" value="id_ingreso_egreso">
    <font size="2"><b>ID Egreso</b></font>
   </td>
   <td>
    <input type="hidden" name="id_pago" value="<?=$datos->fields["id_ingreso_egreso"]?>">
    <font color="Blue" size="2">
     <b>
      <?$link=encode_link("../caja/ingresos_egresos.php",array("id_ingreso_egreso"=> $datos->fields["id_ingreso_egreso"],"pagina"=>"egreso","distrito"=>$datos->fields["id_distrito"]));?>
      <a href="<?=$link?>" target="_blank"><?=$datos->fields["id_ingreso_egreso"]?></a>
     </b>
    </font>
   </td>
   <td>
    <font size="2"><b>Descripción Egreso</b></font>
   </td>
   <td>
    <font color="Blue" size="2"><b><?=$datos->fields["descripcion_egreso"]?></b></font>
   </td>
  </tr>
  <?
 }//de if($datos->fields["id_ingreso_egreso"]!="")
 else if($datos->fields["idbanco"]!="" && $datos->fields["númeroch"]!="")//datos del cheque
 {
  ?>
  <tr>
   <td>
    <input type="hidden" name="tipo_pago" value="númeroch">
    <font size="2"><b>Nº Cheque</b></font>
   </td>
   <td>
    <input type="hidden" name="id_pago" value="<?=$datos->fields["númeroch"]?>">
    <input type="hidden" name="id_banco" value="<?=$datos->fields["idbanco"]?>">
    <font color="Blue" size="2">
     <b>
      <?if($datos->fields["fechadébch"]=="")
         $link=encode_link("../bancos/bancos_movi_chpen.php",array("Modificar_Cheque_Numero"=> $datos->fields["númeroch"],"banco"=>$datos->fields["idbanco"],"pagina"=>"imputaciones"));
        else
         $link=encode_link("../bancos/bancos_movi_chdeb.php",array("Modificar_Cheque_Numero"=> $datos->fields["númeroch"],"banco"=>$datos->fields["idbanco"],"pagina"=>"imputaciones"));
      ?>
      <a href="<?=$link?>" target="_blank"><?=$datos->fields["númeroch"]?></a>
     </b>
    </font>
   </td>
   <td>
    <font size="2"><b>Banco</b></font>
   </td>
   <td>
    <font color="Blue" size="2"><b><?=$datos->fields["nombrebanco"]?></b></font>
   </td>
  </tr>
  <?
 }//de else if($datos->fields["idbanco"]!="" && $datos->fields["númeroch"]!="")
 else if($datos->fields["iddébito"]!="")//datos del debito
 {
  ?>
  <tr>
   <td>
    <input type="hidden" name="tipo_pago" value="iddébito">
    <font size="2"><b>ID Débito</b></font>
   </td>
   <td>
    <input type="hidden" name="id_pago" value="<?=$datos->fields["iddébito"]?>">
    <font color="Blue" size="2">
     <b>
      <?$link=encode_link("../bancos/bancos_movi_debitos.php",array("id_debito"=>$datos->fields["iddébito"],"pagina"=>"imputaciones"));?>
      <a href="<?=$link?>" target="_blank"><?=$datos->fields["iddébito"]?></a>
     </b>
    </font>
   </td>
   <td>
    <font size="2"><b>Descripción Débito</b></font>
   </td>
   <td>
    <font color="Blue" size="2"><b><?=$datos->fields["descripcion_debito"]?></b></font>
   </td>
  </tr>
  <?
 }//de else if($datos->fields["idbanco"]!="" && $datos->fields["númeroch"]!="")
 ?>
 <tr>
  <td colspan="4">
   <hr>
  </td>
 </tr>
 <tr>
  <td colspan="4">
   <table align="center" width="60%">
    <tr>
     <td>
      <?
      tabla_imputacion($id_imputacion,$monto_total,$simbolo_moneda);
      ?>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</div>
<?
if(permisos_check("inicio","permiso_terminar_imputacion"))
 $disabled_terminar="";
else
 $disabled_terminar="disabled";
?>

<div align="center">
   <?
   if(permisos_check('inicio','volver_pendiente_porcontrolar')&& ($estado=="Finalizado Completo" || $estado=="Finalizado Sin Discriminar"))
   {
   ?>
   <input type="submit" name="volver_estado" value="Volver Estado Pendiente/Por controlar">&nbsp;
   <?
   }
   ?>
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   <?
   if(permisos_check("inicio","permiso_guardar_imputacion"))
	 $disabled_guardar="";
	else
	 $disabled_guardar="disabled";
   ?>
   <input type="submit" name="guardar" value="Guardar" <?=$disabled_guardar?> onclick="return control_campos_imputacion()" <?=$permiso_editar?>>&nbsp;
   <input type="submit" name="terminar" value="Terminar" <?=$disabled_terminar?>  <?=$permiso_editar?>>&nbsp;
   <input type="button" name="volver" value="Volver" onclick="window.location='listado_imputaciones.php'">
</div>
</form>
</body>
</html>