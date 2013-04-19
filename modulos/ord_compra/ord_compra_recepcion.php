<?
/*
Autor: MAC

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.14 $
$Date: 2006/05/03 22:14:50 $
*/
require_once("../../config.php");
require_once("fns.php");

/***********************************************************************************
 EN ESTE ARCHIVO SE DEBEN INCLUIR TODAS LAS EJECUCIONES DE BOTONES PARA ESTA PAGINA*/
include("proc_recepciones.php");
/*POR FAVOR RESPETEN ESTE FORMATO DE PAGINA
************************************************************************************/


/*************************************************************
 Funcion relacionada con las facturas de proveedores
**************************************************************/
function cargar(&$arr)
{
 $i=0;
 while($i<sizeof($arr)){
    if ($arr[$i][0]!=-1){
	   $id=$arr[$i];
	   $arr[$i][0]=-1;
	   return $id;
	}
	else $i++;
	}
 return false;
}//de function cargar(&$arr)

extract($_POST,EXTR_SKIP);
if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

switch ($modo) {
      	case "oc_serv_tec": $titulo_pagina="Orden de Servicio Técnico";
      						break;
      	default:			$titulo_pagina="Orden de Compra";
      	             		break;
      }

//tengo estas variables que obtengo desde parametros
//mt_id_deposito - mt_id_producto - mt_id_proveedor - mt_id_mt
if ($nro_orden)
{
 $q="select orden_de_compra.id_proveedor,orden_de_compra.estado,orden_de_compra.id_licitacion,orden_de_compra.es_presupuesto,
     orden_de_compra.internacional,orden_de_compra.flag_stock,orden_de_compra.nrocaso,orden_de_compra.flag_honorario,
     orden_de_compra.orden_prod,orden_de_compra.fecha_entrega,orden_de_compra.notas,orden_de_compra.notas_internas,
     proveedor.clasificado,proveedor.razon_social
     from orden_de_compra join proveedor using(id_proveedor) where orden_de_compra.nro_orden=$nro_orden";
 $datos_orden=sql($q,"<br>Error al traer los datos de la OC<br>") or fin_pagina();
 $estado=$datos_orden->fields['estado'];
 $id_proveedor=$datos_orden->fields['id_proveedor'];
 $clasif=$datos_orden->fields['clasificado'];
 $flag_stock=$datos_orden->fields['flag_stock'];
 $internacional=$datos_orden->fields['internacional'];
 $id_licitacion=$datos_orden->fields['id_licitacion'];
 $es_presupuesto=$datos_orden->fields['es_presupuesto'];
 $flag_stock=$datos_orden->fields['flag_stock'];
 $nrocaso=$datos_orden->fields['nrocaso'];
 $flag_honorario=$datos_orden->fields['flag_honorario'];
 $orden_prod=$datos_orden->fields['orden_prod'];
 $fecha_entrega=$datos_orden->fields['fecha_entrega'];
 $nombre_proveedor=$datos_orden->fields['razon_social'];
 $notas = $datos_orden->fields['notas'];
 $notas_internas = $datos_orden->fields['notas_internas'];

  //averiguamos la fecha de recepcion de la OC, para ver que archivo mostramos
  $query="select fecha from log_ordenes where nro_orden=$nro_orden and tipo_log='de recepcion'";
  $fff=sql($query,"<br>Error al traer la fecha de recepcion de la OC<br>") or fin_pagina();
  $fecha_recepcion=$fff->fields["fecha"];
  $fecha_subida_gestion3="2006-01-04 00:00:00";

}//de if ($nro_orden)
else
 die("Error: no se encontro el Nº de Orden");

  $query_asoc="select fact_prov.fecha_emision, factura_asociadas.* from factura_asociadas join fact_prov using(id_factura) where nro_orden=$nro_orden";
  $res_asoc=sql($query_asoc) or fin_pagina();

if (!isset ($cant_factura))
{
  if ($res_asoc->RecordCount()>0)
   $cant_factura=$res_asoc->RecordCount();
  else
   $cant_factura=1;
}//de if (!isset ($cant_factura))

echo $html_header
?>

<style type="text/css">
<!--
.unnamed1 {
	border: medium solid #006699;
}
-->
</style>
<style type="text/css">
<!--
.unnamed2 {
	color: #FFFFFF;
	background-color: #990000;
	font-weight: bold;
	font-size: small;
	text-transform: uppercase;
}
.unnamed3 {
	font-weight: bold;
	font-variant: normal;
	color: #FF0000;
	text-transform: uppercase;
	font-size: small;
}
-->
</style>
<script language="JavaScript" src="funciones.js"></script>
<script>

var contador=0;

/******************************************************
 Funcion que limpia los campos de facturas de proveedor
*******************************************************/
function  limpiar()
{
<?  for ($i=0;$i<$cant_factura;$i++)
    {
      echo "document.all.id_factura_$i.value='';";
    }
?>
}//de function  limpiar()

function control_fact()
{
 cant=document.all.cant_factura.value;
 for (i=0;i<cant;i++)
 {
  obj=eval ("document.all.id_factura_"+ i);
    for(j=i+1;j < cant; j++){
	  obj1=eval ("document.all.id_factura_"+ j);
	   if ((obj.value!="") && (obj.value==obj1.value)) {
	           alert('Ha seleccionado números iguales de factura');
			   return false
			   }
	   }
 }
 return true;
}//de function control_fact()

function habilitar_borrar(valor)
{
	if (valor.checked)
	   contador++;
	else
	   contador--;
	if (contador>=1){
	   window.document.all.borrar_archivo.disabled=0;
	      }
	else{
	    window.document.all.borrar_archivo.disabled=1;
	   }
}//fin function habilitar borrar

function eliminar()
{
	return window.confirm("Esta seguro que quiere eliminar "+contador+" archivos almacenados en el sistema.");
}

function controles()
{ if(control_fact()==true)
   return control_recib_pedido();
  else
   return false;
}//de function controles()
</script>

<div style="overflow:auto;width:100%;position:relative" id="div_formulario">
<?
echo "<center><font color='Red' size=2><b>$msg</b></font></center>";

$link_recepciones=encode_link("ord_compra_recepcion.php",array('nro_orden'=>$nro_orden,'es_stock'=>$es_stock,'mostrar_dolar'=>$mostrar_dolar,"tipo_lic"=>$tipo_lic_text));
?>
<form name="form1" method="post" action="<?=$link_recepciones?>">
 <input type='hidden' name='estado_orden' value='<?=$estado?>'>
 <input type='hidden' name='flag_stock' value='<?=$flag_stock?>'>
 <input type='hidden' name='internacional' value='<?=$internacional?>'>
 <input type='hidden' name='mostrar_dolar' value='<?=$mostrar_dolar?>'>
 <input type='hidden' name='tipo_lic' value='<?=$tipo_lic?>'>
 <input type='hidden' name='es_stock' value='<?=$es_stock?>'>
 <input type="hidden" name="fila_desentregar" value="">
<?
 if ($datos_orden->fields['fecha_factura'] or $datos_orden->fields['nro_factura'])
    echo "<input name='orden_ant' type='hidden' value='1'>";

 switch ($estado)
  {
   	case 'P':
   	case 'p':  $estado_orden="Pendiente"; break;
   	case 'A':
   	case 'a':  $estado_orden="Autorizada"; break;
	case 't':
	case 'T':  $estado_orden="Terminada"; break;
	case 'r':  $estado_orden="Rechazada"; break;
	case 'n':  $estado_orden="Anulada"; break;
	case 'u':  $estado_orden="Por Autorizar"; break;
   	case 'd':  $estado_orden="Parcialmente Pagada"; break;
	case 'e':
	case 'E':  $estado_orden="Enviada"; break;
    case 'g':
    case 'G':  $estado_orden="Totalmente Pagada"; break;
   	default:  $estado_orden="Desconocido";
  }//de switch ($estado)

  if($internacional)
  { $texto_oc="<b>Orden de Compra Internacional Nº: </b>";
    $color_titulo="color='#00C021'";
  }
  else
  {$texto_oc="<b>Orden de Compra Nº: </b>";
   $color_titulo="";
  }
?>
 <table width="100%" class="bordes" align="center" bgcolor='<?=$bgcolor_out?>' cellspacing="3" cellpadding="3">
   <tr>
    <td align="center" id=mo colspan="2">
      <font size="3">
        <?=$texto_oc." ".$nro_orden?>
      </font>
      <input name="nro_orden" type="hidden" value="<?=$nro_orden?>">
    </td>
   </tr>
   <tr>
     <td width="30%">
      <b>Fecha de Entrega</b>
    </td>
    <td width="70%">
     <font color="Blue" size="2">
      <b><?=fecha($fecha_entrega)?></b>
     </font>
    </td>
   </tr>
   <tr>
     <td width="30%">
      <b>Proveedor</b>
    </td>
    <td width="70%">
     <font color="Blue" size="2">
      <b><?=$nombre_proveedor?></b>
     </font>
    </td>
   </tr>
   <tr>
     <td width="30%">
      <b>Tipo de Orden de Compra</b>
    </td>
    <td width="70%">
     <font color="Blue" size="2">
      <b>
      <?
       if($id_licitacion!="" && !$es_presupuesto)
         $tipo_oc="Licitación";
       else if($id_licitacion!="" && $es_presupuesto)
         $tipo_oc="Presupuesto";
       else if($flag_stock)
         $tipo_oc="Stock";
       else if($nrocaso!="")
         $tipo_oc="Servicio Técnico";
       else if($flag_honorario)
         $tipo_oc="Honorarios de Servicio Técnico";
       else if($orden_prod!="")
         $tipo_oc="RMA de Producción";
       else if($internacional)
         $tipo_oc="Internacional";
       else
         $tipo_oc="Otro";

       echo "<font $color_titulo>$tipo_oc</font>";
      ?>
      </b>
     </font>
    </td>
   </tr>
   <tr>
     <td width="30%">
      <b>Estado</b>
    </td>
    <td width="70%">
     <font color="Blue" size="2">
      <b><?=$estado_orden?></b>
     </font>
    </td>
   </tr>
   <tr>
    <td colspan="2">
	  <table class="bordes" width="100%">
	   <tr id="sub_tabla">
	    <td>
	     Facturas del Proveedor
	    </td>
	   </tr>
	   <tr>
		 <td>
		   <b>Cantidad de facturas</b>
		   <input name="cant_factura" type="text" onClick="document.all.cant_factura.value=''" onblur="Actualizar.click();"  size="10" value="<?=$cant_factura ?>">
		   <input name="Actualizar" type="button" value="Actualizar" title="Actualiza el número de facturas" onClick="form1.action='<?= $_SERVER['SCRIPT_NAME'];?>';document.form1.submit();"  <? if ($datos_orden->fields['nro_factura']) echo 'disabled' ?>>
		 </td>
	   </tr>
	   <tr>
	    <td>
	     <table border="1" align="center">
		   <tr>
		    <td width="4%">&nbsp; </td>
		    <td width="49%" align="center"><strong> ID Factura </strong> </td>
		    <td width="47%" align="center" id="td_fecha_factura"><strong>Fecha Factura</strong>&nbsp;
		    </td>
		   </tr>
		   <?
		    $filas=$res_asoc->RecordCount();
			if ($filas>0){ //armo un arreglo con los id  y las fechas asociados a la orden
			  for ($i=0;$i<$filas;$i++){
			     $aux=array();
				 $aux[0]=$res_asoc->fields['id_factura'];
				 $aux[1]=$res_asoc->fields['fecha_emision'];
		         $list[$i]=$aux;
				 $res_asoc->MoveNext();
		      }
		    }

			for ($i=0; $i<$cant_factura;$i++)
			{
			?>
			   <tr>
			    <td><input name="Buscar" type="button" value="Buscar" onClick="window.open('<? echo encode_link("../factura_proveedores/fact_prov_listar.php",array("nro_orden"=>$nro_orden,"estado"=>$estado,"fila"=>"$i","cant_factura"=>$cant_factura))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=50,top=30,width=700,height=450')" <? if ($datos_orden->fields['nro_factura']) echo 'disabled' ?>></td>
			    <?
				$id=cargar($list);
				if ($datos_orden->fields['nro_factura']) $value=$datos_orden->fields['nro_factura'];
				   else
				  $value=$_POST["id_factura_$i"] or $value=$id[0];
				if ($datos_orden->fields['fecha_factura']) $value_fecha=Fecha($datos_orden->fields['fecha_factura']);
			    else
				 $value_fecha= $_POST["fecha_factura_$i"] or $value_fecha=Fecha($id[1]);
				 //Fecha($parametros['fecha']) or?>
			    <td align="center"> <input name="ver_factura_<?=$i?>" type="button"  value="ir" title='ver detalles de la factura'  onclick="<? if ($datos_orden->fields['nro_factura']) {?>alert ('La factura asociada no está cargada'); return false; <? }?> if (document.all.id_factura_<?=$i?>.value=='') return false; window.open('<?=encode_link("../factura_proveedores/fact_prov_subir.php",array("nro_orden"=>$nro_orden,"estado"=>$estado,"fila"=>$i)) ?>&id_fact='+document.all.id_factura_<?=$i ?>.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=50,top=30,width=700,height=400')">
			      <input name="id_factura_<?=$i?>" type="text" value="<?=$value?>"> <input name="Nueva Factura" type="button" value="Nueva"  onclick="window.open('<? echo encode_link("../factura_proveedores/fact_prov_subir.php",array("nro_orden"=>$nro_orden,"estado"=>$estado,"fila"=>"$i"))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=50,top=30,width=700,height=450')" <? if ($datos_orden->fields['nro_factura']) echo 'disabled' ?>>
			    </td>
			    <td align="center"><input name="fecha_factura_<?=$i ?>" <?= $permiso ?> readonly="true" type="text" value="<?=$value_fecha;?>" size="10">
			    </td>
			   </tr>
		   <?
		   }//de for ($i=0; $i<$cant_factura;$i++)
		   ?>
		 </table>
		</td>
	   </tr>
	   <input name="id_proveedor" type="hidden" value="<?=$datos_orden->fields['id_proveedor'] //para poder guardar los comentarios?>" >
	  </table>
	</td>
   </tr>
   <tr>
    <td colspan="2">
	  <table class="bordes" width="100%">
	   <tr id="sub_tabla">
	    <td>
	     Seguimiento Interno del Material de Coradir
	    </td>
	   </tr>
	   <tr>
	    <td align="center">
	     <b>Para seguimiento interno del material en Coradir</b>
	    <td>
	   </tr>
	   <tr>
	    <td align="center">
	     <textarea name="notas_internas" style="width:95%" rows="3" wrap="VIRTUAL" onkeypress="more_rows(this,5)" ><?=$notas_internas?></textarea>
	    </td>
	   </tr>
	  </table>
    </td>
   </tr>
  </table>
  <br>
  <?
  //traemos las cantidades recibidas y entregadas de cada fila de la OC
  $filas_rec_ent=cant_rec_ent_por_fila($nro_orden);

  $filas_cambios_prod=filas_con_cambios_prod($nro_orden);

  //traemos los depositos de tipo stock
  $q="select id_deposito,nombre from depositos where tipo=0 order by nombre";
  $datos_depositos=sql($q,"<br>Error al traer los depositos de tipo stock<br>") or fin_pagina();

  if(!$es_stock)
  {
  ?>
	  <table width="100%" class="bordes" align="center" bgcolor='<?=$bgcolor_out?>'>
	   <tr>
	    <td align="center" id=mo colspan="2">
	      <font size="3">
	        Recepción de Productos
	      </font>
	    </td>
	   </tr>
	   <tr>
	    <td>
	     <?
	      $mostrar_boton_no_recibir=0;
	      generar_form_recepcion($nro_orden);
	     ?>
	    </td>
	   </tr>
	  </table>
  	<?
  	  //si la OC esta asociada a licitacion, o a presupuesto, o a RMA, o a Serv Tec,
	  //generamos la parte de entregar los productos
	  if($tipo_oc=="Licitación" || $tipo_oc=="Presupuesto" ||$tipo_oc=="Servicio Técnico"||$tipo_oc=="RMA de Producción")
	    $generar_entrega=0;
	  else
	    $generar_entrega=-1;
     }//de if(!$es_stock)
     //si en cambio el proveedor SI es un stock, entonces muestra pantalla para entregar productos
     else
        $generar_entrega=1;

     //Si hay que generar la parte de entrega
     //ya sea para una OC asociada a licitacion, o a presupuesto, o a RMA, o a Serv Tec ($generar_entrega==0)
     //o para una OC con con proveedor Stock ($generar_entrega==1)
     if($generar_entrega==0 || $generar_entrega==1)
     {//LA PARTE DE ENTREGA NO SE USA MAS PARA LAS OC, A PARTIR DEL GESTION3. POR LO TANTO SOLO SE MUESTRA COMO
      //SOLO LECTURA, PARA LAS OC CREADAS CON GESTION2. LA FUNCIONALIDAD DE LA PARTE DE ENTREGA ES REEMPLAZADA POR
      //EL NUEVO MODULO DE PEDIDO DE MATERIAL

      //variable para mostrar title avisando este hecho
      $title_gestion3="title='Esta funcionalidad no se utiliza más. En su reemplazo utilice el módulo: Pedido de Material'";

	  if($fecha_recepcion!="" && $fecha_recepcion<$fecha_subida_gestion3)
	  {?>
	      <br>
	      <table width="100%" class="bordes" align="center" bgcolor='<?=$bgcolor_out?>'>
		   <tr>
		    <td align="center" id=mx colspan="2">
		      <font size="3" <?=$color_titulo?>>
		        Entrega de Productos
		      </font>
		    </td>
		   </tr>
		   <tr>
		    <td>
		     <?
    	        generar_form_entrega($nro_orden,$generar_entrega);
		     ?>
		    </td>
		   </tr>
		  </table>
      <?
	  }//de if($fecha_recepcion<$fecha_subida_gestion3)
     }//de if($generar_entrega==0 || $generar_entrega==1)
?>
    <table width="100%" border="0" cellspacing="1" cellpadding="1">
       <tr>
        <td width="55%">
         &nbsp;
        </td>
        <td width="45%">
        <?
        if($prov_stock)
        {?>
         <input name="boton" type="submit" id="boton" value="Guardar Datos" <?=$permiso?> style="width:95px">
        <?
        }
        /*
        else
        {?>
         <input name="boton" type="submit" id="boton" value="Entregar" <?=$permiso?> style="width:95px" disabled <?=$title_gestion3?>>
        <?
        }*/?>
        </td>
 	    <?/*<td width="1%" align="right">
 	     <input name="boton" type="submit" id="boton" value="Generar Remito Interno" style="width:150px" disabled  <?=$title_gestion3?>>
 	    </td>
 	    */?>
 	   </tr>
 	</table>
  <hr>
  <?
  $sql_archivos="select * from compras.archivos_subidos_compra where nro_orden=$nro_orden";
  $consulta_sql_archivos=sql($sql_archivos,"<br>Error al traer los archivos subidos para la OC<br>") or fin_pagina();
  if ($consulta_sql_archivos->RecordCount()!=0)
  {
	  if ($msg!=" ")
	  {
	 ?>
	 <br>
	 <?
	  }//de if ($msg!=" ")
	 ?>
	 <table width="100%" align="center" class="bordes" bgcolor='<?=$bgcolor_out?>'>
	  <tr id=mo>
	   <td align="center" colspan="5">
	    <font size="3"><b>Archivos Subidos</b></font>
	   </td>
	 <tr>
	    <td align="left" colspan="5">
	     <b>Documentos:</b> <? echo $consulta_sql_archivos->RecordCount(); ?>.
	     <input name="cant_archivos" type="hidden" value="<? echo $consulta_sql_archivos->RecordCount(); ?>">
	    </td>
	  </tr>
	<tr id=mo_sf6>
	 <td width='10%'><b><input type="submit" name="borrar_archivo" value="Borrar" title="Eliminar Seleccioneados" disabled onclick="return eliminar();"></b></td>
	 <td width='10%'><b>ID Archivo</b></td>
	 <td width='40%'><b>Nombre</b></td>
	 <td width='20%'><b>Fecha</b></td>
	 <td width='20%'><b>Responsable</b></td>
	</tr>

	<?  $i=0;
		while(!$consulta_sql_archivos->EOF)
		{
		?>
			<tr id="ma" title="<?=$consulta_sql_archivos->fields["comentario"]?>">
				<td>
			     <input type="checkbox" name="eliminar_<? echo $i; ?>" value="<? echo $consulta_sql_archivos->fields['id_archivo_subido']; ?>" onclick="habilitar_borrar(this);" title="Seleccione para eliminar">
			     <input type="hidden" name="id_archivo_<? echo $i; ?>" value="<? echo $consulta_sql_archivos->fields['id_archivo_subido']; ?>">
				</td>
				<td>
				 <?=$consulta_sql_archivos->fields["id_archivo_subido"]?>
				</td>
				<td>
				 <input type="hidden" name="nom_comp_<? echo $i; ?>" value="<? echo $consulta_sql_archivos->fields["nombre_archivo_comp"]; ?>">
				 <a target="_blank" title='<?=$consulta_sql_archivos->fields["nombre_archivo"]?> [<?=number_format($consulta_sql_archivos->fields["filesize_comp"]/1024)?> Kb]' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$consulta_sql_archivos->fields["id_archivo_subido"],"download"=>1,"comp"=>1,"nro_orden"=>$nro_orden,"es_stock"=>$es_stock,"mostrar_dolar"=>$mostrar_dolar,"tipo_lic"=>$tipo_lic))?>'>
				 <img align=middle src=../../imagenes/zip.gif border=0></A>
				 <a title = 'Abrir archivo' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$consulta_sql_archivos->fields["id_archivo_subido"],"download"=>1,"comp"=>0,"nro_orden"=>$nro_orden,"es_stock"=>$es_stock,"mostrar_dolar"=>$mostrar_dolar,"tipo_lic"=>$tipo_lic))?>'>
				 <? echo $consulta_sql_archivos->fields["nombre_archivo"]." (".number_format(($consulta_sql_archivos->fields["filesize_comp"]/1024),"2",".","")."Kb)"?>
				 </a>
				</td>
				<td>
				 <?=fecha($consulta_sql_archivos->fields["fecha"])." ".hora($consulta_sql_archivos->fields["fecha"])?>
				</td>
				<td>
				 <? echo $consulta_sql_archivos->fields["usuario"]?>
				</td>
			</tr>
			<?
			$i++;
			$consulta_sql_archivos->MoveNext();
		}//de while(!$consulta_sql_archivos->EOF)
		?>
		<input type="hidden" name="Cantidad" value="<?=$i?>">
	</table>
	<?
	}//de if ($consulta_sql_archivos->RecordCount()!=0)
	else
	 echo "<table align=center><tr><td><b><font size=3>No hay Archivos Subidos para este Seguimiento.</font></b></td></tr></table>";

	?>
	<br>
	<table align="center">
	 <tr>
	   <td width="12%" align="center"><input name="subir_archivo" type="button" value="Subir Archivos" style="width:90px" onclick="location.href='<?= encode_link("subir_archivo_ord_compra.php",array("nro_orden"=>$nro_orden,"es_stock"=>$es_stock,"mostrar_dolar"=>$mostrar_dolar,"tipo_lic"=>$tipo_lic)) ?>' "></td>
	 </tr>
	</table>
</div><!--div_formulario-->
<?
/********************************************************************************************
  SECCION BOTONERA
*********************************************************************************************/
?>
<div style="background-color:<?=$bgcolor_out?>;height:50px;position:relative" id="div_botonera">
<table width="100%" id=history class="bordessininferior">
 <tr>
  <td width="37.5%">
   Estado: <?=$estado_orden?>
  </td>
  <td width="25%">
   <?=$titulo_pagina?> Nº <?=$nro_orden?>
  </td>
  <td width="37.5%">
   Tipo de Orden de Compra: <font <?=$color_titulo?>><?=$tipo_oc?></font>
  </td>
 </tr>
</table>
<table width="100%" class="bordes">
 <?
 $link_calif=encode_link("../calidad/califique_proveedor.php",array("proveedor"=>"$id_proveedor","desde"=>"2"));
 $query="select * from general.calificacion_proveedor
         where fecha is not null and fecha>(current_date - 7)
         and id_proveedor=$id_proveedor";
 $re=sql($query,"<br>Error al traer informacion de la calificacion del proveedor<br>") or fin_pagina();
 ?>
 <tr>
  <td width="1%">
   <?
   if($mostrar_boton_no_recibir && permisos_check("inicio","permiso_no_recibir_fila_oc"))
   {?>
     <input type="submit" name="no_recibir_filas" value="No Recibir Filas" onclick="if(confirm('¿Está seguro que desea que las filas seleccionadas no sean tomadas en cuenta en la recepción de productos de esta Orden de Compra?'))
                                                                                      return true;
                                                                                    else
                                                                                      return false;"
     >
    <?
   }//de if($mostrar_boton_no_recibir && permisos_check("inicio","permiso_no_recibir_fila_oc"))
   ?>
  </td>
  <td align="right">
    <input name="guardar_datos" type="submit"  value="Guardar Datos" <?=$permiso ?> style="width:95px" onClick="if (controles())
                                                                                                                   { <?if (!$es_stock && $re->RecordCount()==0)
                                                                                                                        echo "window.open('$link_calif','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=230,top=80,width=500,height=400');";
                                                                                                                     ?>
                                                                                                                     return true;
                                                                                                                   }
                                                                                                                   else
                                                                                                                    return false;
                                                                                                                 "
    >
  </td>
  <td align="left" width="1%">
   <input name="boton" type="button" id="boton" value="Volver a OC" style="width:80px" onclick="location.href='<?= encode_link("ord_compra.php",array("nro_orden"=>$nro_orden)) ?>'">
  </td>
  <td align="left">
   <input name="boton" type="button" id="boton" value="Volver a Listado" style="width:100px" onclick="location.href='<?= encode_link("ord_compra_listar.php",array("")) ?>'">
  </td>
  <td width="1%" align="right">
   <input name="subir_archivo" type="button" value="Subir Archivos" style="width:90px" onclick="location.href='<?= encode_link("subir_archivo_ord_compra.php",array("nro_orden"=>$nro_orden,"es_stock"=>$es_stock,"mostrar_dolar"=>$mostrar_dolar,"tipo_lic"=>$tipo_lic)) ?>' ">
  </td>
 </tr>
</table>
</div>
</form>
<script>
//dependiendo del largo del formulario, seteamos el largo del div del formulario
 var largo_form=parseInt(document.body.clientHeight)-parseInt(document.all.div_botonera.style.height);

 document.all.div_formulario.style.height=largo_form+"px";
</script>

<?
if($_ses_user["login"]=="marcos" || $_ses_user["login"]=="fernando" || $_ses_user["login"]=="norberto"
   || $_ses_user["login"]=="gonzalo" || $_ses_user["login"]=="mariela")
  echo fin_pagina();
else
 echo "</body></html>";
?>