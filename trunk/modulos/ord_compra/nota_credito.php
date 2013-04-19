<?
/*
Autor: MAC

$Author: mari $
$Revision: 1.33 $
$Date: 2007/01/04 12:30:56 $
*/

require_once("../../config.php");

$rma_selec=descomprimir_variable($parametros['rma_selec']) or $rma_selec=descomprimir_variable($_POST['rma_selec']);

if ($parametros["download"]) {

$sql = "select * from arch_notas_credito where id_archivo = ".$parametros["id_archivo"];
$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");

	if ($parametros["comp"]) {
		$FileName = $result->fields["nombre_comp"];
		$FileNameFull = UPLOADS_DIR."/notas_credito/$FileName";
		$FileType="application/zip";
		$FileSize = $result->fields["tamano_comp"];
		FileDownload(1,$FileName,$FileNameFull,$FileType,$FileSize);
	} else {
		$FileName = $result->fields["nbre_arch"];
		$FileNameFull = UPLOADS_DIR."/notas_credito/$FileName";
		$FileType = $result->fields["tipo"];
		$FileSize = $result->fields["tam_arch"];
		FileDownload(0,$FileName,$FileNameFull,$FileType,$FileSize);
	}
}

$flag=$parametros["flag"];
if($parametros['pagina_volver']!="")
 $pagina_volver=$parametros['pagina_volver'];
else
 $pagina_volver="nota_credito_listar.php";

if($_POST['Guardar']=="Guardar Cambios")
{

  $db->StartTrans();
  $id_proveedor=$_POST['select_proveedor'];
  $id_deposito=$_POST['id_deposito'];
 // $id_info_rma=$_POST['id_info_rma'];
  $rma_selec=descomprimir_variable($_POST['rma_selec']);
  $id_producto=$_POST['id_producto'];
  $cantidad_desc_rma=$_POST['cantidad_desc_rma'];
  // $nro_nota_credito=$_POST['text_nro_credito'];
   $descripcion=$_POST['text_descripcion'];
   $monto=$_POST['text_monto'];
   $observaciones=$_POST['text_observaciones'];
   $moneda=$_POST['select_moneda'];
   $id_nota_credito=$parametros['id_nota_credito'];
   $pagina=$parametros['pagina'];

   if(($pagina=='nota_credito')||($pagina=='')||$pagina=='RMA')
   {
   	$query="select nextval('nota_credito_id_nota_credito_seq') as id_nota_credito";
   	$id_nc=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer secuencia de nota de credito");
   	$id_nota_credito=$id_nc->fields['id_nota_credito'];
	$insertar="INSERT INTO nota_credito
	(id_nota_credito,id_proveedor,descripcion,observaciones,monto,id_moneda,estado)
	 VALUES ($id_nota_credito,$id_proveedor,'$descripcion','$observaciones',$monto,$moneda,0)";
	sql($insertar,"<br>Error al insertar la nota de credito<br>") or fin_pagina();

     // insertar el log de las notas de credito
     $fecha_hoy_nota=date("Y-m-d H:i:s",mktime());
     $usuario_nota=$_ses_user['name'];
     $tipo_nota="creación";
     $query_nota="insert into log_nota_credito(fecha,usuario,tipo,id_nota_credito)
     values('$fecha_hoy_nota','$usuario_nota','$tipo_nota',$id_nota_credito)";
     sql($query_nota,"<br>Error al generar el log de la nota de credito<br>") or fin_pagina();
     // llave por query_nota
     $msg="<b><center>Los datos se insertaron con exito</b></center>";

	  //si venimos de RMA, actualizamos la entrada correspondiente
	  //para ligarlo a esta nota de credito
	  if($parametros['pagina']=='RMA')
	  {
	  //descontamos del stock de RMA porque se ha recibido una nota de credito
	   include_once("../stock/funciones.php");

	   eliminar_rma($rma_selec,'Baja por Nota de Credito',$id_nota_credito);

	  }//de if($parametros['pagina']=='RMA')


	 $enviar_mail_nota=1;

   }//de if(($pagina=='nota_credito')||($pagina=='')||$pagina=='RMA')

   if($pagina=='nota_credito_listar' || $pagina=='stock_rma')
	{
	  $enviar_mail_nota=0;

	  $actualizar="UPDATE nota_credito SET id_proveedor=$id_proveedor,descripcion='$descripcion',
				 observaciones='$observaciones',monto=$monto,id_moneda=$moneda
				 WHERE id_nota_credito=$id_nota_credito";
      if($db->Execute($actualizar)){ // or die($db->ErrorMsg()."<br>".$insertar);

     // actualizar el log de las notas de credito
     $fecha_hoy_nota=date("Y-m-d H:i:s",mktime());
     $usuario_nota=$_ses_user['name'];
     $tipo_nota="modificación";
     $query_nota="insert into log_nota_credito(fecha,usuario,tipo,id_nota_credito)
     values('$fecha_hoy_nota','$usuario_nota','$tipo_nota',$id_nota_credito)";
     // fin de actualizar el log tipo = modificacion
      if($db->Execute($query_nota)or die ($db->ErrorMsg().$query_nota))
        $msg="<b><center>Los datos se actualizaron con exito</b></center>";
       else
      $msg="<b><center>No se pudieron actualizar los datos</b></center>";
      }
	 else
	  $msg="<b><center>No se pudieron actualizar los datos</b></center>";
	}

    $db->CompleteTrans();

    //si la Nota de credito se creo, enviamos un mail. Si solo actualizan datos, no es necesario el mail
    if($enviar_mail_nota==1)
    {
    	 //traemos el simbolo de la moneda elegida
		 $query="select simbolo from licitaciones.moneda where id_moneda=$moneda";
		 $mond=sql($query,"<br>Error al traer el simbolo de la moneda NC<br>") or fin_pagina();
		 $simbolo_nota=$mond->fields["simbolo"];

		 //traemos el nombre del proveedor
		 $query="select proveedor.razon_social from general.proveedor where id_proveedor=$id_proveedor";
		 $prov_nota=sql($query,"<br>Error al traer el nombre del proveedor NC<br>") or fin_pagina();
		 $prov_nombre=$prov_nota->fields["razon_social"];

		 //enviamos el mail avisando que se creó una Nota de Credito
		 $para="noelia@coradir.com.ar";
		 $asunto="Se creó la nota de crédito Nº $id_nota_credito por el monto de $simbolo_nota $monto";
		 $texto="Se creó la Nota de Crédito Nº $id_nota_credito. Los datos de la misma son los siguientes:\n";
		 $texto.="Descripción: $descripcion\n";
		 $texto.="Monto: $simbolo_nota $monto\n";
	 	 $texto.="Proveedor: $prov_nombre\n";
	 	 $texto.="Observaciones: $observaciones\n";
		 $texto.="Fecha de Creación: ".fecha($fecha_hoy_nota)." ".hora($fecha_hoy_nota)." \n\n";
		 $texto.="Usuario que creó la Nota de Crédito: $usuario_nota\n\n\n";

		 enviar_mail($para,$asunto,$texto,'','','','','');
    }//de if($enviar_mail_nota==1)


    if($pagina_volver=="../stock/stock_descontar_rma.php")
     $pg="historial";
	 if ($pagina_volver=='../stock/listar_rma.php')
	   $link=$link=encode_link("$pagina_volver",array("exito"=>$msg));
	   else
       $link=encode_link("$pagina_volver",array("msg"=>$msg,"id_deposito"=>$id_deposito,"id_proveedor"=>$id_proveedor,"id_producto"=>$id_producto,"pagina_listado"=>$pg,"id_info_rma"=>$id_info_rma));
	header("location:$link");
}//de if($_POST['Guardar']=="Guardar Cambios")

if ($_POST['anular']) {
    $db->StartTrans();
	$id_nota_credito=$parametros['id_nota_credito'];
    //busco si esta asociada a rma
    $sql_asociada="select id_info_rma from info_rma where id_nota_credito=$id_nota_credito";
    $res=sql($sql_asociada) or fin_pagina();
	
    if ($res->RecordCount() > 0){
        $msg="<font size=2 color=red><b><center>Error: La Nota de credito Nro $id_nota_credito no se puede anular porque está asociada a RMA</b></center></font>";
    }
    else {
	 $sql="update nota_credito set estado=3
          where id_nota_credito=$id_nota_credito";
	 sql($sql,"<br>Error al anular la nota de credito nro $id_nota_credito<br>") or fin_pagina();
    
	// insertar el log de las notas de credito
     $fecha_hoy_nota=date("Y-m-d H:i:s",mktime());
     $usuario_nota=$_ses_user['name'];
     $tipo_nota="anulación";
     $query_nota="insert into log_nota_credito(fecha,usuario,tipo,id_nota_credito)
     values('$fecha_hoy_nota','$usuario_nota','$tipo_nota',$id_nota_credito)";
     sql($query_nota,"<br>Error al generar el log de la nota de credito<br>") or fin_pagina();
     $msg="<font size=2 color=red><b><center>La Nota de crédito Nro $id_nota_credito se anuló con exito</b></center></font>";
    }
     
     $link=encode_link("$pagina_volver",array("msg"=>$msg));
	 header("location:$link");
	 
     $db->CompleteTrans();
}

//validas solo si la pagina desde donde llegamos es RMA
$id_info_rma=descomprimir_variable($parametros['id_info_rma']); //arreglo con los id de info rma

function show_encabezado($titulo1,$titulo2,$color){
echo "<hr>
<TABLE width='90%' align='center' border='0' cellspacing='2' cellpadding='0'>";
if($color!="")
 echo "<tr bgcolor='$color'>";
else
 echo "<tr id=mo>";             //#BA0105;
echo "<td width='60%' align='center'><strong>";
if($color!=""){
			   echo "<font color='#FDF2F3'>";
			   echo $titulo1;
			   echo "</font>";
			  }
else echo $titulo1;
	  echo "</strong>
	  </td>
	  <td width='40%' height='20' align='center' ><strong>";
if($color!=""){
			   echo "<font color='#FDF2F3'>";
			   echo $titulo2;
			   echo "</font>";
			  }
else echo $titulo2;

	  echo "</strong> </td>
  </tr>
</table>
";
} //fin de show_encabezado


function tabla_filtros_nombres($link) {

 $abc=array("a","b","c","d","e","f","g","h","i",
			"j","k","l","m","n","ñ","o","p","q",
			"r","s","t","u","v","w","x","y","z");

$cantidad=count($abc);

echo "<table align='center' width='80%' height='80%' id=mo>";
echo "<input type=hidden name='filtro' value=''";
	echo "<tr>";
	for($i=0;$i<$cantidad;$i++){
		$letra=$abc[$i];
	   switch ($i) {
					 case 9:
					 case 18:
					 case 27:echo "</tr><tr>";
						  break;
				   default:
				  } //del switch
//echo "<a id='link_load' href=$link><td style='cursor:hand' onclick=\"document.all.filtro.value='$letra'\">$letra</td></a>\n";
?>
 <td style='cursor:hand' onclick="document.all.filtro.value='<?=$letra?>';document.form1.submit();"><?=$letra?></td>
 <?
}//del for
   echo "</tr>";
   echo "<tr>";
   echo "<td colspan='9' style='cursor:hand' onclick=\"document.all.filtro.value='todos'; document.form1.submit();\"> Todos";
   echo "</td>";
   echo "</tr>";
   echo "</table>";
}  //de la funcion  para las letras en la lista de clientes y proveedores


$disabled_filtro_prov=0;
if($parametros['pagina']=='nota_credito_listar' || $parametros['pagina']=="stock_rma") {
  $datos="SELECT nota_credito.*, moneda.id_moneda,proveedor.id_proveedor,
		  proveedor.razon_social,nota_credito.estado
		  FROM general.nota_credito JOIN licitaciones.moneda using(id_moneda)
		  JOIN general.proveedor Using (id_proveedor)
		  WHERE id_nota_credito=".$parametros['id_nota_credito']."
		  ";
  $resultados=$db->Execute($datos) or die($db->ErrorMsg()."<br>".$datos);

  $id_nota_credito=$parametros['id_nota_credito'];
  $id_proveedor=$resultados->fields['id_proveedor'];
//  $nro_nota_credito=$resultados->fields['nro_nota_credito'];
  $descripcion=$resultados->fields['descripcion'];
  $monto=$resultados->fields['monto'];
  $moneda=$resultados->fields['id_moneda'];

  if ($_POST["filtro"]=="") $letra=substr($resultados->fields["razon_social"],0,1);

  $observaciones=$resultados->fields['observaciones'];
  $estado=$resultados->fields["estado"];

  if (($estado==1)||($estado==2 || $estado==3))
				$disabled="disabled";
				else
				$disabled="";

} //fin de if($parametros['pagina']=='nota_credito_listar') {
elseif($parametros['pagina']=='RMA') {
	$descripcion=$parametros['descripcion'];
	$observaciones=$descripcion;
	//traemos el nombre del proveedor
	if (!$flag) {
		$id_proveedor=$parametros['id_proveedor'];
	    $query="select razon_social from proveedor where id_proveedor=$id_proveedor";
	    $prov=sql($query,"Error al traer el nombre del proveedor: $query") or fin_pagina();
	    $nombre_prov=$prov->fields['razon_social'];
	    $letra=substr($nombre_prov,0,1);
	}
	$monto=number_format($parametros['monto'],'2','.','');
	$moneda=2;
	//para no mostrar el filtro de letras del proveedor
	//$disabled_filtro_prov=1;
	//readonly al select de proveedores
	//$readonly_prov="readonly";
}//de elseif($parametros['pagina']=='RMA') {

$pagina=$parametros['pagina'];
  if ($flag)
		   {
		   $moneda=$_POST["select_moneda"];
		   $observaciones=$_POST["text_observaciones"];
		   $descripcion=$_POST["text_descripcion"];
		   $monto=$_POST["text_monto"];
		   }

echo $html_header;
?>
<!--<html>
  <head>
	 <?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
	<style type="text/css">
	</style>
-->
<script>
  function control_datos()
  {if(document.all.text_monto.value=="")
   {alert('Debe ingresar un monto para la nota de crédito');
	return false;
   }
   else if(control_numero(document.all.text_monto,"Monto"))
	return false;

   if(document.all.select_moneda.options[document.all.select_moneda.selectedIndex].value==-1)
   {alert('Debe seleccionar un tipo de moneda');
	return false
   }
   if(document.all.select_proveedor.value=="")
   {alert('Debe seleccionar un proveedor');
	return false
   }
   return true;
  }
 </script>
 </head>
  <body bgcolor="#E0E0E0">
  <script src="../../lib/funciones.js"></script>

  <center>
<?
$link_pagina=encode_link("nota_credito.php",array("pagina"=>$pagina,"pagina_volver"=>$pagina_volver,"id_nota_credito"=>$id_nota_credito,"flag"=>1));

///////////////////////////////////
if($id_nota_credito)
{$query="select * from general.log_nota_credito where id_nota_credito=".$id_nota_credito;
 $log=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer log");
?>
<center>
<div style='position:relative; width:90%; height:11%; overflow:auto;'>
<table width="100%" cellpadding="1" cellspacing="1" align="center">
<?
while(!$log->EOF)
{list($fecha,$hora)=split(" ",$log->fields['fecha']);
 ?>
 <tr id=ma>
  <td align="left">
   Fecha de <?=$log->fields['tipo']?>: <?=fecha($fecha)?> <?=$hora?>
  </td>
  <td align="right">
   Usuario: <?=$log->fields['usuario']?>
  </td>
 </tr>
 <?
 $log->MoveNext();
}
?>
</table>
</div>
</center>
<? }?>
  <form name="form1" method="post" action="<?=$link_pagina?>">

  <?
 if ($estado=='3') echo "<font color='red' size=2>Nota de Crédito Anulada</font>";
show_encabezado("Informacion de la Nota de credito","Proveedores","");
?>
<table id=ma width='90%'>
  <td width='60%'>
   <table id=ma>
   <?if ($pagina=='nota_credito_listar' || $pagina=="stock_rma") {?>
	<tr>
	  <td align='left'>Nota de Credito Nro: </td>
	  <td align='left'><font color="black"><?=$id_nota_credito?></font></td>
	</tr>
   <? }?>
	<tr>
	   <td align='left'> Descripción </td>
	   <td align='left'> <input type='text' name='text_descripcion' value='<?=$descripcion?>' <?=$disabled?>></td>
   </tr>

	<tr>
	   <td align='left'> Moneda </td>
	   <td align='left'>
	   <?$query="select * from moneda order by id_moneda";
		$moneda_list=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer los datos de moneda");
	   ?>
		 <select name='select_moneda' <?=$disabled?>>
		  <option value=-1>Seleccione</option>
		  <?
		  while(!$moneda_list->EOF)
		  {
		  ?>
		  <option value=<?=$moneda_list->fields['id_moneda']?> <?if($moneda_list->fields['id_moneda']==$moneda)echo "selected";?>><?=$moneda_list->fields['nombre']?></option>
		  <?
		  $moneda_list->MoveNext();
		  }
		  ?>
		</select>
	   </td>
   </tr>

   <tr>
	   <td align='left'> Monto: </td>
	   <td align='left'> <input type='text' name='text_monto' value='<?=$monto?>' <?=$disabled?>></td>
   </tr>
   <tr>
	  <td align='left'>Observaciones:</td>
   </tr>
   <tr>
	<td colspan='2'><textarea name="text_observaciones" cols="40" wrap="VIRTUAL" rows='3' <?=$disabled?>><?=$observaciones?></textarea></td>
   </tr>


   </table>
   </td>


   <td width='40%'>
	 <table width='100%' id=ma>

   <tr>
	 <td align='center'>
	 <?//  <select name='select_proveedor' size='12' style='width:85%' onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()'>
     if($disabled_filtro_prov==0)
	  tabla_filtros_nombres("");
	 ?>
	<!--HIDDENS PARA MANTENER INFO QUE VIENE DESDE EL MODULO RMA-->
	<input type="hidden" name="rma_selec" value="<?=comprimir_variable($rma_selec)?>">
	<input type="hidden" name="id_proveedor" value="<?=$id_proveedor?>">

	<select name='select_proveedor' <?=$readonly_prov?> size='10' style='width:85%' onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' <?=$disabled?>>
	<?

	if($_POST['filtro']=="todos")
	 $filtro="";
	else if($_POST['filtro']!="")
	 $filtro=$_POST['filtro'];
	else
	 $filtro='a';

	if (($letra!="")&&($filtro!="")) $filtro=$letra;
    //si venimos de la pagina de RMA, ya conocemos el id de proveedor
    //y no se lo puede cambiar, entoces solo traemos ese proveedor para mostrar
	/*if($parametros['pagina']=='RMA')
    {$query="SELECT id_proveedor,razon_social FROM general.proveedor WHERE id_proveedor=".$id_proveedor;
	 $resultados_proveedor=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);
    }
    else
    {*/$query="SELECT id_proveedor,razon_social FROM general.proveedor WHERE razon_social ilike '$filtro%' order by razon_social";
	 $resultados_proveedor=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);
   /* } */
	while(!$resultados_proveedor->EOF) {
    ?>
      	<option value='<?=$resultados_proveedor->fields['id_proveedor']?>' <?if($id_proveedor==$resultados_proveedor->fields['id_proveedor']) echo "selected"?>>
              <?=$resultados_proveedor->fields['razon_social']?>
              </option>";
      <?
      $resultados_proveedor->MoveNext();

      }
    ?>

     </select>
    </td>
    </tr>
   </table>
   </td>
</table>
<?
//traemos las ordenes de compra con las que esta relacionada la nota de credito
//(solo si el estado de la nota de credito es utilizada o reservada)
if($resultados->fields['estado']>0)
{
 $query="select nro_orden,usuario,fecha from n_credito_orden where id_nota_credito=".$parametros['id_nota_credito'];
 $result_oc=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer datos de la orden relacioanda");
 ?>
 <br>
 <table width="100%" align="center">
 <tr id=ma>
   <td colspan="2">
    <?
    if($resultados->fields['estado']==1)
     $es="reservada";
    else
     $es="utilizada";
    ?>
    <font size=2>Nota de Crédito <?=$es?> para el pago de
    <?
    $cant_nc=$result_oc->RecordCount();
    if($cant_nc>1)
     echo "las Ordenes de Compra Nº ";
    else
     echo "la Orden de Compra Nº ";
    $count_coma=0;
    while(!$result_oc->EOF)
    {if($count_coma>0 && $count_coma<$cant_nc)
      echo ", ";
     echo $result_oc->fields['nro_orden'];
     $count_coma++;
     $result_oc->MoveNext();
	}
    $result_oc->Move(0);
    ?>
    </font>
   </td>
  </tr>
  <tr id=ma>
   <td align="center">
   Usuario: <?=$result_oc->fields['usuario']?>
   </td>
   <td>
     Fecha: <? $fecha_oc=$result_oc->fields['fecha'];
              $f_oc=split(" ",$fecha_oc);
              echo fecha($f_oc[0])." ".$f_oc[1]?>
   </td>
  </tr>
 </table>
 <?
}//de if($resultados->fields['estado']>0)
$link_archivo=encode_link("subir_arch_creditos.php",array("id_nota_credito"=>$parametros['id_nota_credito']));
?>
<br>
<?if($id_nota_credito!="" && permisos_check("inicio","permiso_subirarch_nota_cred")){?>
<input type="button" name="boton_subir_arch" value="Subir Archivos" onclick="window.open('<?=$link_archivo;?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1')">
<?}?>
<br><br>
<?
if($id_nota_credito!="")
{
$sql="select * from arch_notas_credito where id_nota_credito=$id_nota_credito";
$result_archivos=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
if ($result_archivos->recordCount()>0) //hay archivos subidos
{
?>
<table class="bordes" width="100%">
<tr><td colspan="4" align="left"><b>Archivos Subidos</td></tr>
<tr id="mo"><td></td><td>Nombre</td><td>Fecha Subido</td><td>Usuario</td></tr>
<?
$clase="id=mo";
while (!$result_archivos->EOF)
{
$clase=($clase=="id=ma")?"id=mo":"id=ma";
?>
<tr <?=$clase;?>>
<td><a title='<?=$result_archivos->fields["nombre_comp"]?> [<?=number_format($result_archivos->fields["tamano_comp"]/1024)?> Kb]' href='<?=encode_link($_SERVER["PHP_SELF"],array("id_archivo"=>$result_archivos->fields["id_archivo"],"download"=>1,"comp"=>1))?>'>
<img align=middle src=<?=$html_root?>/imagenes/zip.gif border=0></A></td>
<td><a title = 'Abrir archivo' href='<?=encode_link($_SERVER["PHP_SELF"],array("id_archivo"=>$result_archivos->fields["id_archivo"],"download"=>1,"comp"=>0))?>'><?=$result_archivos->fields['nbre_arch'];?></a></td>
<td><?=$result_archivos->fields['fecha_carga'];?></td>
<?
$sql="select nombre,apellido from usuarios where id_usuario=".$result_archivos->fields['id_usuario'];
$result_usuario_nota=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
?>
<td><?=$result_usuario_nota->fields['nombre']." ".$result_usuario_nota->fields['apellido'];?></td>
</tr>
<?
$result_archivos->movenext();
}
?>
</table>
<?
}
}
?>
<center>
<hr>
<? 
if(permisos_check("inicio","permiso_anular_nota_cred") && $estado==0 && $estado!="")  {?>
   <input type='submit' name='anular' value='Anular' onclick="if(confirm('¿Está seguro que desea anular?'))return true; else return false;" style="cursor:hand" >
<? }
  //si tienen permisos permiso_guardar_nota_cred se puede hacer una nueva nota de credito desde el menu
  if ($pagina == '' ) {
      if (permisos_check("inicio","permiso_guardar_nota_cred")) {?>
    <input type='submit' name='Guardar' value='Guardar Cambios' style="cursor:hand" onclick="return control_datos()" <?=$disabled?>>
 <? }
}
else {
?>
   <input type='submit' name='Guardar' value='Guardar Cambios' style="cursor:hand" onclick="return control_datos()" <?=$disabled?>>
 <?
}
if($pagina=="RMA")
 $link=encode_link("$pagina_volver",array("id_deposito"=>$id_deposito,"id_producto"=>$id_producto,"id_proveedor"=>$id_proveedor,"id_info_rma"=>$id_info_rma,"pagina_listado"=>"real"));
else
 $link=$pagina_volver;

if ($pagina=='stock_rma') {?>
    <input type='button' name='Cerrar' value='Cerrar' style="cursor:hand" onclick="window.close();" >
<?}
 else { ?>
   <input type='button' name='Volver' value='Volver' style="cursor:hand" onclick="window.location='<?=$link?>'" >
   <?}?>
</center>
</form>
</body>
</html>