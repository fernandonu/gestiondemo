<?
/*
Autor: Marco Canderle

$Author: elizabeth $
$Revision: 1.1 $
$Date: 2004/11/23 12:32:44 $
*/

//require_once("config_local.php");
require_once("../../config.php");
echo $html_header;
variables_form_busqueda("facturas");


//ver dependencia con el archivo pdf.php
define("OUTPUT","./pdfs"); //directorio donde se crearan los pdfs
$total_en_pesos=0;
$total_en_dolares=0;
if ($parametros["cmd"] == "detalle") {
	include("factura_nueva.php");
	exit;
}

//DATOS DE LA FACTURA se extraen del arreglo POST
extract($_POST,EXTR_SKIP);
if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

$backto=$parametros['backto'] or $backto=$_POST['backto'];

if ($backto && $_ses_global_backto!=$backto)
{
	phpss_svars_set("_ses_global_backto", $backto);
	phpss_svars_set("_ses_global_extra", $parametros['_ses_global_extra']);
}

//////////////////////////////////////////////	
// para pasar al form busqueda
// arreglos orden y filtro
$orden = array (
    "default" => "1",
    "1" => "fecha_factura",
    "2" => "nro_factura",
    "3" => "tipo_factura",
    "4" => "cliente",
    "5" => "total",
    "6" => "usuario"
  );
  
$filtro = array (
    "nro_factura" => "Nº Factura",
    "nro_remito" => "Nº Remito",
    "id_licitacion" => "ID Licitación",
    "fecha_factura" => "Fecha Factura",
    "pedido" => "Nº Pedido",
    "venta" => "Venta",
    "cliente" => "Cliente - Nombre",
    "direccion" => "Cliente - Direccion",
    "cuit" => "Cliente - C.U.I.T.",
    "iib" => "Cliente - Nº I.I.B.",
    "iva_tipo" => "Cliente - Condición I.V.A.",
    "iva_tasa" => "Cliente - Tasa I.V.A.",
    "otros" => "Cliente - Otros"
  ); 

// esta es la consulta q se ejecuta siempre a la cual hay q adicionarle los where dep
// de los campos q elija  
$campos="facturas.id_factura,facturas.nro_factura,facturas.cliente,facturas.tipo_factura,
         facturas.fecha_factura,facturas.estado,facturas.id_moneda,facturas.id_licitacion,
         log.usuario,log.fecha,log.tipo_log,case when estado='a' then 0 else total end as total ";
$sql_tmp="select  $campos FROM facturas
          left join log on log.id_factura=facturas.id_factura and log.tipo_log='creacion' 
          left join 
             (select sum(precio*cant_prod) as total,id_factura from items_factura group by id_factura) mtotal 
               on facturas.id_factura=mtotal.id_factura ";

// se usa en el form busqueda como where de la consulta general
// en where_tmp tengo q agregar el resto de los campos q se eljan
switch ($cmd) {
	case 'anuladas': $where_tmp=" estado='a'";
			         break;
	case 'terminadas': $where_tmp=" estado='t'";
			           break;
	case 'todas': break;
	default: $where_tmp=" estado='p'";
	         $cmd='pendientes';
	         break;
}
//////////////////////////////////////////////

//para formatear la fecha correctamente, si se busca por fecha
$bus_text=$_POST['buscar_text'];
if(($_POST['select_campo']=="fecha_factura")||($_POST['select_campo']=="todos"))
{$a=str_count_letra("/",$bus_text);
 if ($a>0)
   $bus_text=Fecha_db($_POST['buscar_text']);
 else
  $bus_text=$_POST['buscar_text'];
}

/// no forman parte del arreglo filtro se tiene que agregar a where_tmp
//para ver que tipos de facturs busca (A,B o ambas)
switch($_POST['tipo_factura'])
{case 'a':if (!(($buscamos)||($cmd!="todas")))
            $where_tmp.=" and facturas.tipo_factura='a'";
          else
            $where_tmp.=" facturas.tipo_factura='a'";
          break;
 case 'b':if (!(($buscamos)||($cmd!="todas")))
            $where_tmp.=" and facturas.tipo_factura='b'";
          else
            $where_tmp.=" facturas.tipo_factura='b'";
          break;
}

//para ver que tipos de facturs busca (A,B o ambas)
switch($_POST['moneda'])
{case 'pesos':if (!(($buscamos)||($cmd!="todas")))
            $where_tmp.=" and facturas.id_moneda=1";
          else
            $where_tmp.=" facturas.id_moneda=1";
          break;
 case 'dolares':if (!(($buscamos)||($cmd!="todas")))
            $where_tmp.=" and facturas.id_moneda=2";
          else
            $where_tmp.=" facturas.id_moneda=2";
          break;
}
if ($_POST["filtro_fecha"] == "1") {
	$error = 0;
	if (FechaOk($_POST["fecha_desde"])) {
		$fecha_desde = "'".Fecha_db($_POST["fecha_desde"])."'";
	}
	else {
		Error("El formato de la fecha de inicio no es válido");
	}
	if (FechaOk($_POST["fecha_hasta"])) {
		$fecha_hasta = "'".Fecha_db($_POST["fecha_hasta"])."'";
	}
	else {
		Error("El formato de la fecha de finalización no es válido");
	}
	if (!$error) {
		if (!(($buscamos)||($cmd!="todas")))
			$where_tmp.=" and facturas.fecha_factura between $fecha_desde and $fecha_hasta";
		else
			$where_tmp.=" and facturas.fecha_factura between $fecha_desde and $fecha_hasta";
	}
}
if ($_POST["filtro_monto"] == "1") {
	$error = 0;
	if (es_numero($_POST["monto_desde"])) {
		$monto_desde = $_POST["monto_desde"];
	}
	else {
		Error("El formato del monto de inicio no es válido");
	}
	if (es_numero($_POST["monto_hasta"])) {
		$monto_hasta = $_POST["monto_hasta"];
	}
	else {
		Error("El formato del monto de finalización no es válido");
	}
	if (!$error) {
		if (!(($buscamos)||($cmd!="todas")))
			$where_tmp.=" and total between $monto_desde and $monto_hasta";
		else
			$where_tmp.=" and total between $monto_desde and $monto_hasta";
	}
}
////q es ????
/*
$query.=" ORDER BY";
if ($orden)
	$query.=" $orden";
else 
	$query.=" fecha_factura";
*/
// Cuenta el total en pesos y en dolares
// no se usa
switch ($cmd) {
	case 'anuladas': $esta="and estado='a'";
					 break;
	case 'terminadas': $esta="and estado='t'";
					   break;
	case 'todas': break;
	default: $esta="and estado='p'";
			 break;
}
/*
$sql="select sum(precio*cant_prod) as total from items_factura left join facturas using (id_factura) where id_moneda=1 $esta";
$total_peso=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql); 

$sql="select sum(precio*cant_prod) as total from items_factura left join facturas using (id_factura) where id_moneda=2 $esta";
$total_dolar=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql); 
print_r($query);*/

?>

<script>
//esto es para truncar los flotantes a x posiciones
//la condicion es para saber si la funcion esta definida para la clase Number
if (!Number.toFixed)
	{
	Number.prototype.toFixed=
	function(x) {
   					var temp=this;
   					temp=Math.round(temp*Math.pow(10,x))/Math.pow(10,x);
   					return temp;
					};
	}

function calcular_monto(check,moneda)
{
var separador,long,string,fin,substr,p;
if (document.all.primera_vez.value==1) //veo que moneda se usa
{document.all.moneda_actual.value=moneda.value;
 document.all.primera_vez.value=0;
 document.all.contador.value=0;
}
else
{if (document.all.moneda_actual.value!=moneda.value)
 {alert("Debe sumar mismo tipo de moneda");
  check.checked=false;
  return 0;
 }
}
 string=check.value;
 string=string.replace('.',"");
 string=string.replace(',',".");
 //check.value=string;
 if (check.checked==true)
  {document.all.monto_total.value=parseFloat(document.all.monto_total.value) + parseFloat(string);
   document.all.contador.value=parseInt(document.all.contador.value)+1;
  }
 else
  {document.all.monto_total.value=parseFloat(document.all.monto_total.value) - parseFloat(string);
   document.all.contador.value=parseInt(document.all.contador.value)-1;
  }
var string= new String(document.all.monto_total.value); 
	if (string.indexOf(".")==-1)
		string=string+".00";
//alert(string);
string=string.replace('.',',');
fin=string.length;
substr=string.substring(fin-3,fin);
p=1;
while(fin>3)
{if (p!=1)
 {aux=string.substring(fin-3,fin);
  substr='.'+aux+substr;
 }
 else
  p=0; 
 fin-=3;
}
substr=string.substring(0,fin)+substr;
string=substr;
document.all.monto.value=string;
if (moneda.value==0)
 document.all.monto.value="$ "+document.all.monto.value;
else
 document.all.monto.value="U$S "+document.all.monto.value;
if (document.all.contador.value==0)
 {document.all.monto.value=0;
  document.all.primera_vez.value=1;
 }
return 1;
}

function resetear()
{var i,objeto;
 i=1;
 while(i<=document.all.cantidad_check.value)
 {objeto=eval("document.all.chk_"+i);
  objeto.checked=false;
  i++;
 }
 document.all.primera_vez.value=1;
 document.all.moneda_actual.value=0;
 document.all.monto_total.value=0;
 document.all.contador.value=0;
 document.all.monto.value=0;
}
</script>
<?php
cargar_calendario();
include("../ayuda/ayudas.php");
?>
</head>
<?= ch_menu($_SERVER['SCRIPT_NAME']) ?>

<form name="form" method="post" action="<?php echo encode_link("factura_listar.php",array('filtro'=>$cmd)); ?>">
<input type="hidden" name="primera_vez" value="1">
<input type="hidden" name="moneda_actual" value="0">
<input type="hidden" name="monto_total" value="0">
<input type="hidden" name="contador" value=0>

<div align="right">
   <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/facturas/fact_listar.htm" ?>', 'FACTURAS')" >
</div> 

<table cellspacing=2 cellpadding=5 border=0 bgcolor=<?=$bgcolor3?> width=100% align=center>
 <tr>
  <td> <? $datos_barra = array(
					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "pendientes"
						),
					array(
						"descripcion"	=> "Terminadas",
						"cmd"			=> "terminadas"
						),
					array(
						"descripcion"	=> "Anuladas",
						"cmd"			=> "anuladas"
						),
					array(
						"descripcion"	=> "Todas",
						"cmd"			=> "todas"
						)
				 );
     generar_barra_nav($datos_barra);  ?>
  </td>
 </tr>
 <tr>
  <td align=center>
    <table cellspacing=2 cellpadding=5 border=0 bgcolor=<?=$bgcolor3?> width=100% align=center>
      <tr>
       <td>
<?
list($sql,$total_facturas,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
$facturas=sql($sql) or fin_pagina();
?>
       </td>
       <td>
         <table>
           <tr>
             <td><b>Tipo Factura</b>
              <select name="tipo_factura">
                 <option value='todas' <?if($_POST['tipo_factura']=='todas') echo "selected"?>>Todas</option>
                 <option value='a' <?if($_POST['tipo_factura']=='a') echo "selected"?>>A</option>
                 <option value='b' <?if($_POST['tipo_factura']=='b') echo "selected"?>>B</option>
              </select> 
             </td>
             <td><b>Moneda</b>
              <select name="moneda">
                 <option value='todas' <?if($_POST['moneda']=='todas') echo "selected"?>>Todas</option>
                 <option value='dolares' <?if($_POST['moneda']=='dolares') echo "selected"?>>Dólares</option>
                 <option value='pesos' <?if($_POST['moneda']=='pesos') echo "selected"?>>Pesos</option>
              </select>
             </td>
          </tr>
          <tr>
            <td><input type="checkbox" name="filtro_fecha" value="1" <?=($_POST["filtro_fecha"])?"checked":""?>>
               <b>Filtrar por fechas:</b> 
               <b>Desde</b><input type="text" size="10" name="fecha_desde" value="<?=$_POST["fecha_desde"]?>">
                 <?=link_calendario("fecha_desde")?>
               <b>Hasta</b><input type="text" size="10" name="fecha_hasta" value="<?=$_POST["fecha_hasta"]?>">
               <?=link_calendario("fecha_hasta")?>
            </td>
            <td><input type="checkbox" name="filtro_monto" value="1" <?=($_POST["filtro_monto"])?"checked":""?>>
               <b>Filtrar por montos:</b>
               <b>Desde</b><input type="text" size="10" name="monto_desde" value="<?=$_POST["monto_desde"]?>">
               <b>Hasta</b><input type="text" size="10" name="monto_hasta" value="<?=$_POST["monto_hasta"]?>">
            </td>
         </tr> 
       </table>  
     </td>    
<td align="left">
<input type="submit" name="boton" value="Buscar">
</td>
<!--<td align="right"><input type="button" name="boton" value="Busqueda Avanzada" style="width:100%"></td>-->
</tr>
</table>
<?
$facturas = sql($sql) or die;
?>
<br><?="<b>".$informar."</b>"?>
	<table width="100%" cellpadding="0" cellspacing="0">
      <tr id=ma>
        <td align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Suma 
         <input type="text" name="monto" size="14" style="border-style:none;background-color:'transparent';font-weight: bold;" value="0" size="12" readonly>
         <input type="button" name="boton" value="Reset" onclick="resetear()">
        </b></font>
        </td>
        <td align=right>
         Total en pesos: $ <input type="text" class="text_4" readonly name="total_pesos" value="">
		</td>
        <td align=right>
		 Total en dolares: U$S <input type="text" class="text_4" readonly name="total_dolares" value="">
	   </td>
    </tr>
    </table>
   <table border=0 width=95% cellspacing=2 cellpadding=3 bgcolor=<?=$bgcolor3?> align=center>
      
      <tr><td align=left id=ma><b>Total:</b><?=$total_facturas?></td><td colspan="2" id=ma></td></tr>
      <tr><td colspan="2" id=ma></td><td align=left id=ma><?=$link_pagina?></td></tr>
   
           
      <tr>
        <td align=right id=mo></td>
        <td align=right id=mo><a id=mo href='<?=encode_link("factura_listar_form_busqueda.php",array("sort"=>"1","up"=>$up))?>'>Fecha</a></td>
        <td align=right id=mo><a id=mo href='<?=encode_link("factura_listar_form_busqueda.php",array("sort"=>"2","up"=>$up))?>'>Nº Factura</a></td>
        <td align=right id=mo><a id=mo href='<?=encode_link("factura_listar_form_busqueda.php",array("sort"=>"3","up"=>$up))?>'>Tipo</td>
        <? if($cmd=="todas") {?>
        <td align=right id=mo>Estado</td>
        <? } ?>
        <td align=right id=mo><a id=mo href='<?=encode_link("factura_listar_form_busqueda.php",array("sort"=>"4","up"=>$up))?>'>Cliente</td>
        <td align=right id=mo><a id=mo href='<?=encode_link("factura_listar_form_busqueda.php",array("sort"=>"5","up"=>$up))?>'>Monto Total</td>
        <td align=right id=mo><a id=mo href='<?=encode_link("factura_listar_form_busqueda.php",array("sort"=>"6","up"=>$up))?>'>Creada por</td>
        <?	if ($cmd=="terminadas" || $cmd=="todas" || $cmd=="anuladas") { ?>
            <td align=right id=mo></td> 
        <? } ?>
     </tr>
<? 	$l=1;
    while (!$facturas->EOF ) { 
     $ref = encode_link("factura_nueva.php",array("id_factura"=>$facturas->fields['id_factura']));
     tr_tag($ref); ?>
      <td><input type="checkbox" name="chk_<?php echo $l; ?>" value="<?php echo number_format($facturas->fields['total'], 2, ',', '.')?>" onclick="return calcular_monto(chk_<?php echo $l; ?>,tipo_moneda_<?php echo $l; ?>);">
      </td>
      <td align="center"><font color="#006699" size=-2><b><? echo Fecha($facturas->fields['fecha_factura']) ?></b></font></td>
      <td align="center"><font color="#006699" size=-2><b><? echo $facturas->fields['nro_factura'] ?></b></font></td>
      <td align="center"><font color="#006699" size=-2><b><? echo strtoupper($facturas->fields['tipo_factura']); ?></b></font></td>
      <? if($cmd=="todas") { ?>
      <td align="center"><font color="#006699"><b>
      <? switch ($facturas->fields['estado']) {
        		case 'a':
        		case 'A': echo "Anulada";break;
        		case 'p':
			  	case 'P': echo "Pendiente";	break;
        		case 't':
			  	case 'T': echo "Terminada";	break;		
         } ?>
         </b></font></td>
      <? } ?>
      <td align="center"><font color="#006699" size=-2><b><? echo $facturas->fields['cliente'] ?></b></font></td>
      <td><table width="100%"><tr>
           <? //buscamos la moneda correspondiente
            $query="select simbolo from moneda where id_moneda=".$facturas->fields['id_moneda'];
            $moneda=$db->Execute($query) or die ($db->ErrorMsg());
           ?> 
           <input type="hidden" name="tipo_moneda_<?php echo $l; ?>" value="<?php if ($moneda->fields['simbolo']=="$") echo 0; else echo 1; ?>"> 
           <td width="20%" align="left"><font color="#006699" size=-2><b><?=$moneda->fields['simbolo']?></b></center></font></td>
           <td width="80%" align="right"><font color="#006699" size=-2><b><?= number_format($facturas->fields['total'], 2, ',', '.') ?></b></center></font></td>
      </tr></table></td>
      <td align="center"><font color="#006699" size=-2><b><? echo $facturas->fields['usuario'] ?></b></font></td>
      <? if ($cmd=="terminadas" || $cmd=="todas" || $cmd=="anuladas") { ?>
        <td align="center">
        <? switch ($facturas->fields['estado']) {	
        	case 'p': break;
      		case 'a': 
 			case 't': 
 			  $id_factura=$facturas->fields['id_factura'];
			  $sql_seg="select id_renglones_oc from facturacion.items_factura where id_factura=$id_factura";
              $res_seg=sql($sql_seg)or fin_pagina();
                        
              if ($res_seg->fields['id_renglones_oc'])
                  $seg=1; //la factura se creo desde el menu Produccion/entregas
              else $seg=0;
			  if ($facturas->fields['tipo_factura']=='a')
			     $link=encode_link("facturaA_pdf.php", array("id_factura"=>$facturas->fields['id_factura'],"seg"=>$seg));	
			  else  
			     $link=encode_link("facturaB_pdf.php", array("id_factura"=>$facturas->fields['id_factura'],"seg"=>$seg));	
		      echo "<A target='_blank' href='".$link."'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'>";
        } ?>
        </td>
        <? 	}
  		echo   "</tr>";
  		if($moneda->fields['simbolo']=='$')
  		 $total_en_pesos+=$facturas->fields['total'];
  		else 
  		 $total_en_dolares+=$facturas->fields['total'];
  		$facturas->MoveNext();
  		$l++;
  }
?>
    </table>
  </center>
      <script>
     document.all.total_pesos.value="<?=formato_money($total_en_pesos)?>";
     document.all.total_dolares.value="<?=formato_money($total_en_dolares)?>";
    </script>
</form>
<input type="hidden" name="cantidad_check" value="<?php echo $l-1; ?>">
<br><? //echo "Página generada en ".tiempo_de_carga()." segundos.<br>"; 
    fin_pagina();
    ?>