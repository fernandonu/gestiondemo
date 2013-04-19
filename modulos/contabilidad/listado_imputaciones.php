<?/*

----------------------------------------
 Autor: MAC
 Fecha: 26/09/2005
----------------------------------------

MODIFICADA POR
$Author: mari $
$Revision: 1.13 $
$Date: 2006/09/07 14:12:09 $
*/

include_once("../../config.php");


$var_sesion=array(
                  "fecha_desde"=>"",
                  "fecha_hasta"=>"",
                  "filtro_banco"=>"-1",
                  "filtro_cuenta"=>"-1",
                  "monto_buscar"=>""
				  );

if ($_POST && $_POST['monto_buscar']==""){
	$_ses_listado_imputacion["monto_buscar"]="";
    phpss_svars_set("_ses_listado_imputacion",$_ses_listado_imputacion);
}

if ($_POST && $_POST['fecha_desde']==""){
	$_ses_listado_imputacion["fecha_desde"]="";
    phpss_svars_set("_ses_listado_imputacion",$_ses_listado_imputacion);
}

if ($_POST && $_POST['fecha_hasta']==""){
	$_ses_listado_imputacion["fecha_hasta"]="";
    phpss_svars_set("_ses_listado_imputacion",$_ses_listado_imputacion);
}

variables_form_busqueda("listado_imputacion",$var_sesion);


if ($cmd=="")
{
 	$cmd='pendientes';
  	$_ses_listado_imputacion["cmd"]=$cmd;
  	phpss_svars_set("_ses_listado_imputacion",$_ses_listado_imputacion);
    $page=0;
}

$sumas = array(
 		"moneda" => "id_moneda",
 		"campo" => "monto_imputacion",
 		"mask" => array()
		);


$orden = array (
    "default" => "1",
    "default_up" => "0",
    "1" => "imputacion.fecha",
    "2" => "monto_imputacion",
    "3" => "nombre_cuenta_imputacion",
    "4" => "id_ingreso_egreso",
    "5" => "nombrebanco",
    "6" => "númeroch",
    "7" => "iddébito",
    "8" => "log.usuario"
  );

$filtro = array (
    "id_ingreso_egreso" => "ID Egreso de Caja",
    "númeroch"=>"Nº Cheque",
    "iddébito"=>"Id Débito",
    "log.usuario" => "Usuario"
  );



//traemos los datos de las imputaciones cargadas (dependiendo del estado traemos o no, algunos datos extra)
$query="select id_imputacion,id_estado_imputacion,id_ingreso_egreso,imputacion.idbanco,númeroch,iddébito,
        imputacion.valor_dolar,imputacion.fecha,moneda.simbolo,
        case when id_moneda is null then 1
                  else id_moneda end as id_moneda,
        nombrebanco,log.usuario,estado_imputacion.nombre as estado,imputacion.id_estado_imputacion,
        case when ingreso_egreso.monto is not null then (ingreso_egreso.monto * valor_dolar)
             when cheques.importech is not null then cheques.importech
             when débitos.importedéb is not null then débitos.importedéb
             else 0
        end as monto_imputacion,
        case when nro_cuenta_caja is not null then nro_cuenta_caja
             when nro_cuenta_cheque is not null then nro_cuenta_cheque
             when nro_cuenta_debito is not null then nro_cuenta_debito
             else -1
        end as nro_cuenta_imputacion,
        nro_cuenta_caja,nro_cuenta_cheque,nro_cuenta_debito,
        case when cuenta_caja.concepto is not null then cuenta_caja.concepto||' ['||cuenta_caja.plan||']'
             when cuenta_cheque.concepto is not null then cuenta_cheque.concepto||' ['||cuenta_cheque.plan||']'
             when cuenta_debito.concepto is not null then cuenta_debito.concepto||' ['||cuenta_debito.plan||']'
             else '--'
        end as nombre_cuenta_imputacion
        from contabilidad.imputacion
        join contabilidad.estado_imputacion using(id_estado_imputacion)
        left join caja.ingreso_egreso using(id_ingreso_egreso)
        left join caja.caja using(id_caja)
        left join licitaciones.moneda using(id_moneda)
        left join (select numero_cuenta as nro_cuenta_caja,concepto,plan from general.tipo_cuenta) as cuenta_caja on(ingreso_egreso.numero_cuenta=cuenta_caja.nro_cuenta_caja)
        left join bancos.tipo_banco using(idbanco)
        left join bancos.cheques using(idbanco,númeroch)
        left join (select numero_cuenta as nro_cuenta_cheque,concepto,plan from general.tipo_cuenta) as cuenta_cheque on(cheques.numero_cuenta=cuenta_cheque.nro_cuenta_cheque)
        left join bancos.débitos using(iddébito)
	    left join (select numero_cuenta as nro_cuenta_debito,concepto,plan from general.tipo_cuenta) as cuenta_debito on(débitos.numero_cuenta=cuenta_debito.nro_cuenta_debito)
	    left join (select usuario,id_imputacion from contabilidad.log_imputacion where tipo='creación') as log using(id_imputacion)";

$where="";
//filtro dependiendo del estado de la imputacion
switch ($cmd)
{
  case "pendientes":$where.=" (estado_imputacion.nombre='Pendiente')";break;
  case "por_controlar":$where.=" (estado_imputacion.nombre='Por Controlar' or estado_imputacion.nombre='Sin Discriminar (por controlar)')";break;
  case "historial": $where.=" (estado_imputacion.nombre='Finalizado Completo' or estado_imputacion.nombre='Finalizado Sin Discriminar' or estado_imputacion.nombre='Pago Anulado')";break;
  default:break;
}//de switch ($cmd)

//agregamos filtro por fecha si se ingreso alguna de las dos fechas o ambas
if($fecha_desde!="" && $fecha_hasta!="")
{
 if($where!="")
  $where.=" and";
 $where.=" (imputacion.fecha between '".Fecha_db($fecha_desde)." 00:00:00' and '".Fecha_db($fecha_hasta)." 23:59:59')";
}
else if($fecha_desde!="")
{
 if($where!="")
  $where.=" and";
 $where.=" imputacion.fecha>='".Fecha_db($fecha_desde)." 00:00:00'";
}
else if($fecha_hasta!="")
{
 if($where!="")
  $where.=" and";
 $where.=" imputacion.fecha<='".Fecha_db($fecha_hasta)." 23:59:59'";
}

if($monto_buscar!="")
{
 if($where!="")
  $where.=" and";
 $where.=" (ingreso_egreso.monto=$monto_buscar or
           cheques.importech=$monto_buscar or
           débitos.importedéb=$monto_buscar)";
}

//si se selecciono un banco para filtrar, lo agregamos a la consulta
if($filtro_banco!=-1 && $filtro_banco!="")
{
 if($where!="")
  $where.=" and";
 $where.=" imputacion.idbanco=$filtro_banco";
}

//si se selecciono un banco para filtrar, lo agregamos a la consulta
if($filtro_cuenta!=-1 && $filtro_cuenta!="")
{
 if($where!="")
  $where.=" and";
 $where.=" (nro_cuenta_caja=$filtro_cuenta or nro_cuenta_cheque=$filtro_cuenta or nro_cuenta_debito=$filtro_cuenta)";
}

$datos_barra = array(
                    array(
                        "descripcion"    => "Pendientes",
                        "cmd"            => "pendientes"
                        ),
                    array(
                        "descripcion"    => "Por Controlar",
                        "cmd"            => "por_controlar"
                        ),
                    array(
                        "descripcion"    => "Historial",
                        "cmd"            => "historial"
                        )
);
?>
<script>
	function limpia_fecha(){
		document.all.fecha_desde.value="";
		document.all.fecha_hasta.value="";
	}
</script>

<?
echo $html_header;

cargar_calendario();

generar_barra_nav($datos_barra);
?>
<form action="listado_imputaciones.php" method="POST">

<div align="center">
 <b><?=$parametros['msg']?></b>
</div>

<input type=hidden name=excel value=0>
 <table align="center" width="95%" class="bordes">
  <tr>
   <td align="center">
    <?
    list($query_listado,$total_imputaciones,$link_pagina,$up,$suma) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar",$sumas);
    ?>
   </td>
   <td width="55%">
    <table class="bordes" width="90%">
     <tr>
      <td width="35%">
       <b>Desde</b> <input type="text" name="fecha_desde" value="<?=$fecha_desde?>" size="10" readonly><?=link_calendario("fecha_desde")?>
      </td>
      <td width="30%" align="right">
       <input type="button" name="borrar_fecha" value="Limpiar Fechas" onclick="limpia_fecha();">
      </td>
      <td width="35%" align="right">
       <b>Hasta</b> <input type="text" name="fecha_hasta" value="<?=$fecha_hasta?>" size="10" readonly><?=link_calendario("fecha_hasta")?>
      </td>
     </tr>
    </table>
    <? $link=encode_link("imputaciones_excel_listado.php",array("sql"=>$query_listado,"total_imputaciones"=>$total_imputaciones,"suma"=>$suma));?>
   </td>
    <td><img src="../../imagenes/excel.gif" style='cursor:hand;'  onclick="window.open('<?=$link?>')"></td>
  </tr>
  <tr>
   <td align="center" width="45%">
    <?
    //traemos los bancos activos para el filtro
    $query="select nombrebanco,idbanco from tipo_banco where activo=1 order by nombrebanco";
    $bancos=sql($query,"<br>Error al traer los bancos<br>") or fin_pagina();
    ?>
    <b>Bancos</b>&nbsp;
    <select name="filtro_banco" onKeypress="buscar_op(this);" onblur="borrar_buffer();" onclick="borrar_buffer();">
     <option value="-1" selected>Todos</option>
     <?
     while (!$bancos->EOF)
     {?>
      <option value="<?=$bancos->fields["idbanco"]?>" <?if($bancos->fields["idbanco"]==$filtro_banco) echo "selected"?>>
       <?=$bancos->fields["nombrebanco"]?>
      </option>
      <?
      $bancos->MoveNext();
     }//de while(!$bancos->EOF)
     ?>
    </select>
    &nbsp;
    <b>Monto</b>
    <input type="text" name="monto_buscar" value="<?=$monto_buscar?>" size="5">

   </td>
   <td>
    <?
    //traemos las cuentas cargadas en sistema
    $query="select numero_cuenta,concepto||'['||plan||']' as nombre_cuenta from tipo_cuenta order by concepto,plan";
    $cuentas=sql($query,"<br>Error al traer las cuentas<br>") or fin_pagina();
    ?>
    <b>Cuentas</b>&nbsp;
    <select name="filtro_cuenta" onKeypress="buscar_op(this);" onblur="borrar_buffer();" onclick="borrar_buffer();">
     <option value="-1" selected>Todas</option>
     <?
     while (!$cuentas->EOF)
     {?>
      <option value="<?=$cuentas->fields["numero_cuenta"]?>" <?if($cuentas->fields["numero_cuenta"]==$filtro_cuenta) echo "selected"?>>
       <?=$cuentas->fields["nombre_cuenta"]?>
      </option>
      <?
      $cuentas->MoveNext();
     }//de while(!$cuentas->EOF)
     ?>
    </select>
   </td>
   <td>
    &nbsp;
    <input type="submit" name="Buscar" value="Buscar">
   </td>
  </tr>
 </table>
 <?
 $datos_imputaciones=sql($query_listado,"<br>Error al realizar consulta para traer los datos del listado<br>") or fin_pagina();

 ?>

 <table width="100%">
  <tr>
   <td id=ma_sf>
    <table width="100%">
     <tr  id=ma_sf>
      <td align=left>
       <b>Total : </b><?=$total_imputaciones?> imputaciones
       </td>
       <td align="right">
          Total:$ <?=formato_money($suma)?>
       </td>
       <td align="right">
        <?=($link_pagina)?$link_pagina:"&nbsp;"?>
       </td>
      </tr>
    </table>
   </td>
  </tr>
 </table>
 <table width="100%">
  <tr id=mo>
   <td width="5%">
    <a id=mo href='<?=encode_link("listado_imputaciones.php",array("sort"=>"1","up"=>$up))?>'>
     Fecha
    </a>
   </td>
   <td width="10%">
    <a id=mo href='<?=encode_link("listado_imputaciones.php",array("sort"=>"2","up"=>$up))?>'>
     Monto
    </a>
   </td>
   <td width="25%">
    <a id=mo href='<?=encode_link("listado_imputaciones.php",array("sort"=>"3","up"=>$up))?>'>
     Cuenta
    </a>
   </td>
   <td width="5%">
    <a id=mo href='<?=encode_link("listado_imputaciones.php",array("sort"=>"4","up"=>$up))?>'>
     ID Egreso
    </a>
   </td>
   <td width="20%">
    <a id=mo href='<?=encode_link("listado_imputaciones.php",array("sort"=>"5","up"=>$up))?>'>
     Banco
    </a>
   </td>
   <td width="10%">
    <a id=mo href='<?=encode_link("listado_imputaciones.php",array("sort"=>"6","up"=>$up))?>'>
     Nº Cheque
    </a>
   </td>
   <td width="5%">
    <a id=mo href='<?=encode_link("listado_imputaciones.php",array("sort"=>"7","up"=>$up))?>'>
     ID Débito
    </a>
   </td>
   <td width="15%" title="Usuario que generó la Imputación">
    <a id=mo href='<?=encode_link("listado_imputaciones.php",array("sort"=>"8","up"=>$up))?>'>
     Usuario
    </a>
   </td>
  </tr>
  <?

  while (!$datos_imputaciones->EOF)
  {
  	$ref=encode_link("detalle_imputacion.php",array("id_imputacion"=>$datos_imputaciones->fields["id_imputacion"]));
    $onclick_elegir="location.href='$ref'";
    if($datos_imputaciones->fields["estado"]=="Pago Anulado")
    {  $bcolor_fila="#FF8080";
       $title_fila="El pago correspondiente a esta imputación, fue anulado";
    }
    else
      $bcolor_fila=$bgcolor_out;

    ?>
     <tr <?=atrib_tr($bcolor_fila)?> title="<?=$title_fila?>" onclick="<?=$onclick_elegir?>">
      <td>
       <?=fecha($datos_imputaciones->fields["fecha"])?>
      </td>
      <td>
       <?
        $monto_fila=$datos_imputaciones->fields["monto_imputacion"];
       ?>
       <table>
        <tr>
         <td width="1%">
          <?='$'?>
         </td>
         <td align="right">
          <?=formato_money($monto_fila)?>
         </td>
        </tr>
       </table>
      </td>
      <td>
       <?=$datos_imputaciones->fields["nombre_cuenta_imputacion"]?>
      </td>
      <td>
        <?=$datos_imputaciones->fields["id_ingreso_egreso"]?>
      </td>
      <td>
       <?=$datos_imputaciones->fields["nombrebanco"]?>
      </td>
      <td>
       <?=$datos_imputaciones->fields["númeroch"]?>
      </td>
      <td>
       <?=$datos_imputaciones->fields["iddébito"]?>
      </td>
      <td>
       <?=$datos_imputaciones->fields["usuario"]?>
      </td>
     </tr>
    <?
  	$datos_imputaciones->MoveNext();
  }//de while(!$datos_imputaciones->EOF)
  ?>
 </table>

</form>
<?
if($_ses_user["login"]=="marcos")
{
?>
<input type="button" name="pasar_imputacion" value="Generar pagos de imputacion" onclick="window.open('agregar_imputaciones_pagos.php')">
<?
}

fin_pagina();
?>