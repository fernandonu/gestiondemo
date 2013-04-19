<?
/*
Autor: Broggi, Diego

MODIFICADA POR
$Author: ferni $
$Revision: 1.13 $
$Date: 2007/01/10 20:00:20 $

*/

require_once("../../config.php");
$estado=$_POST['estado'];
cargar_calendario();
echo $html_header;
if ($_POST["form_busqueda"]) 
{
 if(!$_POST["filtro_fecha"])
  $_POST["filtro_fecha"]=0;
  
 if(!$_POST["filtro_monto"])
  $_POST["filtro_monto"]=0;
}   
 $var_sesion=array(
               "tipo_factura"=>"",
               "moneda"=>"",
               "filtro_fecha"=>"0",
               "fecha_desde"=>"",
               "fecha_hasta"=>"",
               "filtro_monto"=>"0",
               "monto_desde"=>"",
               "monto_hasta"=>"",
               "estado"=>"",
               );	
variables_form_busqueda("busq_avanzada_facturas",$var_sesion);
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
    "pedido" => "Nº Pedido",
    "venta" => "Venta",
    "sub.descripcion" => "Descripcion Producto",
    "cliente" => "Cliente - Nombre",
    "direccion" => "Cliente - Direccion",
    "cuit" => "Cliente - C.U.I.T.",
    "iib" => "Cliente - Nº I.I.B.",
    "iva_tipo" => "Cliente - Condición I.V.A.",
    "iva_tasa" => "Cliente - Tasa I.V.A.",
    
  ); 

// esta es la consulta q se ejecuta siempre a la cual hay q adicionarle los where dep
// de los campos q elija  
//if ($filter=="descripcion" || $filter=="all") $campos.="descripcion,";
$campos="facturas.id_factura,(numeracion_sucursal.numeracion || text('-') || facturas.nro_factura) as nro_factura,facturas.cliente,
         nro_remito,pedido,venta,facturas.tipo_factura,direccion,cuit,iib,iva_tipo,iva_tasa,
         facturas.fecha_factura,facturas.estado,facturas.id_moneda,facturas.id_licitacion,moneda.simbolo,roc.id_renglones_oc,";
         if ($filter=="sub.descripcion" || $filter=="all")
             $campos.=" descripcion,";
 
$campos.="log.usuario,log.fecha,log.tipo_log,case when estado='a' then 0 else total end as total ";
$sql_tmp="select * from (select distinct $campos FROM facturas
          left join (select count(id_renglones_oc) as id_renglones_oc,id_factura from facturacion.items_factura group by id_factura) as roc using(id_factura)
          left join log on log.id_factura=facturas.id_factura and log.tipo_log='creacion' 
          left join moneda using(id_moneda)
          left join numeracion_sucursal using (id_numeracion_sucursal)
          left join 
             (select sum(precio*cant_prod) as total,id_factura from items_factura group by id_factura) mtotal 
               on facturas.id_factura=mtotal.id_factura ";
if ($filter=="sub.descripcion" || $filter=="all")  $sql_tmp.="left join items_factura on facturas.id_factura=items_factura.id_factura) as sub";
    else $sql_tmp.=" ) as sub";
$where_tmp="";

// se usa en el form busqueda como where de la consulta general
// en where_tmp tengo q agregar el resto de los campos q se eljan
switch ($estado) 
{
	case 'anuladas': $where_tmp=" estado='a'";
	                 $contar="select count(id_factura) from facturas where estado='a'";
			         break;
	case 'terminadas': $where_tmp=" estado='t'";
	                   $contar="select count(id_factura) from facturas where estado='t'";
			           break;
	case 'todas': $contar="select count(id_factura) from facturas";
	              break;
	default: $where_tmp=" estado='p'";
	         $contar="select count(id_factura) from facturas where estado='p'";
	         $estado='pendientes';
	         break;
}

/// no forman parte del arreglo filtro se tiene que agregar a where_tmp
//para ver que tipos de facturs busca (A,B o ambas)
switch($tipo_factura)
{case 'a':if ($where_tmp!="")
            $where_tmp.=" and sub.tipo_factura='a'";
          else
            $where_tmp.=" sub.tipo_factura='a'";
          $contar="buscar";  
          break;
 case 'b':if ($where_tmp!="")
            $where_tmp.=" and sub.tipo_factura='b'";
          else
            $where_tmp.=" sub.tipo_factura='b'";
          $contar="buscar";  
          break;
}

//para ver que tipos de facturs busca (A,B o ambas)
switch($moneda)
{case 'pesos':if ($where_tmp!="")
            $where_tmp.=" and sub.id_moneda=1";
          else
            $where_tmp.=" sub.id_moneda=1";
          $contar="buscar";  
          break;
 case 'dolares':if ($where_tmp!="")
            $where_tmp.=" and sub.id_moneda=2";
          else
            $where_tmp.=" sub.id_moneda=2";
          $contar="buscar";  
          break;
}

if ($filtro_fecha == "1") {
	/*$error = 0;
	if (!FechaOk($fecha_desde)) {
		
		Error("El formato de la fecha de inicio no es válido");
	}
	if (!FechaOk($fecha_hasta)) {
		Error("El formato de la fecha de finalización no es válido");
	}
	if (FechaOK($fecha_desde) && FechaOK($fecha_hasta) && $fecha_hasta<$fecha_desde) {
	    Error("La fecha Desde debe ser menor a la fecha Hasta");
	}    
	if (!$error) {*/
		if ($where_tmp!="")
			$where_tmp.=" and sub.fecha_factura between '".Fecha_db($fecha_desde)."' and '".Fecha_db($fecha_hasta)."'";
		else
			$where_tmp.=" sub.fecha_factura between '".Fecha_db($fecha_desde)."' and '".Fecha_db($fecha_hasta)."'";
	
	$contar="buscar";
}

if ($filtro_monto == "1") {
	/*$error = 0;
	if (!es_numero($monto_desde)) {
		Error("El formato del monto de inicio no es válido");
	}
	if (!es_numero($monto_hasta)) {
		Error("El formato del monto de finalización no es válido");
	}
	if (!$error) {*/
		if ($where_tmp!="")
			$where_tmp.=" and total between $monto_desde and $monto_hasta";
		else
			$where_tmp.=" total between $monto_desde and $monto_hasta";
	
	$contar="buscar";
}

$query.=" ORDER BY";
if ($orden)
	$query.=" $orden";
else 
	$query.=" fecha_factura";





?>

<script>
function borrar()
{if (!document.all.filtro_fecha.checked)
    {document.all.fecha_desde.value="";
     document.all.fecha_hasta.value="";
    }    	
}	
function borrar2()
{if (!document.all.filtro_monto.checked)
    {document.all.monto_desde.value="";
     document.all.monto_hasta.value="";
    }
}    	

function convert_fecha(fecha)
{partes_fecha=fecha.split("/");
 fecha=partes_fecha[2]+"-"+partes_fecha[1]+"-"+partes_fecha[0]; 
 return fecha;
}

function controles()
{var fecha1=document.all.fecha_desde.value;
 var fecha2=document.all.fecha_hasta.value;
 if (document.all.filtro_fecha.checked)
    {if (document.all.fecha_desde.value=="" || document.all.fecha_hasta.value=="")
        {alert ("Debe ingresar ambas fechas para poder buscar entre fechas");
         return false;
        }
     else if (convert_fecha(fecha1)>convert_fecha(fecha2))
             {alert ("La fecha 'Desde' debe ser menor o igual a la fecha 'Hasta'");
              return false;
             }         	   	 
    }
 if (document.all.filtro_monto.checked)
    {if (document.all.monto_desde.value=="" || document.all.monto_hasta.value=="")
        {alert ("Debe ingresar ambos montos para poder buscar entre montos ");
         return false;
        }
     else if (parseInt(document.all.monto_desde.value)>parseInt(document.all.monto_hasta.value))
             {alert ("El monto 'Desde' debe ser menor o igual al monto 'Hasta'");
              return false;
             }     
    }
 return true;   	  
}	 
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

<form name="busq_avanzada_facturas" action="busq_avanzada_facturas.php" method="POST">
<input type="hidden" name="primera_vez" value="1">
<input type="hidden" name="moneda_actual" value="0">
<input type="hidden" name="monto_total" value="0">
<input type="hidden" name="contador" value=0>
<table align="center">
 <tr>
  <td>
   <font size="4"><b>Busqueda Avanzada de Facturas</b></font>
  </td>
 </tr>
</table>
<table align="center">
 <tr>
  <td>
<?//if($keyword!="")
// $contar="buscar";  
$sumas = array(
 		"moneda" => "id_moneda",
 		"campo" => "total",
 		"mask" => array ("\$","U\$S")
);
list($sql,$total_facturas,$link_pagina,$up,$suma) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar",$sumas);

//echo "Diego $sql";
$facturas=sql($sql) or fin_pagina();

    // Calcular los totales por moneda
	// Tener cuidado que si se cambia la consulta principal, sobre todo alguna de las partes
	// de las expresiones regulares de aca abajo, porque no es case sensitive y se debe cambiar
	// los datos del arreglo $pat_busq
	
//	$pat_busq = array(
//						"/^select .+ FROM/s",
//						"/ORDER BY.*LIMIT \d+ OFFSET \d+/s",
//						((($filter=="items_factura.descripcion" || $filter=="all") && ($keyword != ""))?"//":"/left join items_factura on facturas.id_factura=items_factura.id_factura/s")
//						);
//	$reemplazo = array(
//						"SELECT moneda.nombre,moneda.simbolo,sum(case when estado='a' then 0 else total end) as total FROM",
//						"GROUP BY moneda.simbolo,moneda.nombre",
//						""
//						);
////	print_r($pat_busq);
//	$sql_montos = preg_replace($pat_busq,$reemplazo,$sql);
////	echo "<BR>$sql_montos";
//    $result_montos = sql($sql_montos) or fin_pagina();
//	$total_moneda = array();
//	while (!$result_montos->EOF) {
//		$total_moneda[$result_montos->fields["nombre"]] = 
//							array ("nombre" => $result_montos->fields["nombre"],
//									"simbolo" => $result_montos->fields["simbolo"],
//									"total" => formato_money($result_montos->fields["total"])
//									);
//		$result_montos->MoveNext();
//	}
//	arsort($total_moneda);
?>
  </td>
  <td>
   <input type="submit" name="boton" value="Buscar" onclick="return controles()">
  </td> 
 </tr>  
</table>
<table align="center" width="80%" >
 <tr>
  <td width="15%" align="right">
   <b>Tipo Factura</b>
   <select name="tipo_factura">
    <option value='todas' <?if($tipo_factura=='todas') echo "selected"?>>Todas</option>
    <option value='a' <?if($tipo_factura=='a') echo "selected"?>>A</option>
    <option value='b' <?if($tipo_factura=='b') echo "selected"?>>B</option>
   </select>
   &nbsp;&nbsp;&nbsp;&nbsp; 
  </td>
  <td width="15%" align="left">
   &nbsp;&nbsp;&nbsp;&nbsp;
   <b>Moneda</b>
   <select name="moneda">
    <option value='todas' <?if($moneda=='todas') echo "selected"?>>Todas</option>
    <option value='dolares' <?if($moneda=='dolares') echo "selected"?>>Dólares</option>
    <option value='pesos' <?if($moneda=='pesos') echo "selected"?>>Pesos</option>
   </select>
  </td> 
  <td width="15%" align="left">
   &nbsp;&nbsp;&nbsp;&nbsp;
   <b>Estado</b>
   <select name="estado">
    <option value='todas' <?if($estado=='todas') echo "selected"?>>Todas</option>
    <option value='pendientes' <?if($estado=='pendientes') echo "selected"?>>Pendientes</option>
    <option value='anuladas' <?if($estado=='anuladas') echo "selected"?>>Anuladas</option>
    <option value='terminadas' <?if($estado=='terminadas') echo "selected"?>>Terminadas</option>
   </select>
  </td>
 </tr>
 <tr> 
  <td width="70%" colspan="3" align="center">
     <table border="1" bgcolor="#EEEEEE">
      <tr>
       <td>
        <table>
         <tr>
          <td>
           <input type="checkbox" name="filtro_fecha" value="1" <?=($filtro_fecha==1)?"checked":""?> onclick="borrar()">
           <b>Filtrar por fechas:</b> 
          </td> 
         </tr>
         <tr>
          <td> 
           <?if (!$filtro_fecha) {$fecha_desde="";$fecha_hasta="";}?>
           <b>Desde</b><input type="text" size="10" name="fecha_desde" value="<?=$fecha_desde?>" readonly>
           <?=link_calendario("fecha_desde")?>
           <b>Hasta</b><input type="text" size="10" name="fecha_hasta" value="<?=$fecha_hasta?>" readonly>
           <?=link_calendario("fecha_hasta")?>
          </td>
         </tr>
        </table>   
       </td>
       <td>
        <table>
         <tr>
          <td>
           <input type="checkbox" name="filtro_monto" value="1" <?=($filtro_monto==1)?"checked":""?> onclick="borrar2()">
           <b>Filtrar por montos:</b>
          </td>
         </tr>
         <tr> 
          <td>
           <?if (!$filtro_monto) {$monto_desde="";$monto_hasta="";}?> 
           <b>Desde</b><input type="text" size="10" name="monto_desde" value="<?=$monto_desde?>" onkeypress="return filtrar_teclas(event,'0123456789.')">
           <b>Hasta</b><input type="text" size="10" name="monto_hasta" value="<?=$monto_hasta?>" onkeypress="return filtrar_teclas(event,'0123456789.')">
          </td>
         </tr>
        </table>   
       </td>
      </tr> 
     </table>  
    </td>
   </tr>
  </table>

<br><?="<b>".$informar."</b>"?>
<table width="100%" cellpadding="0" cellspacing="0">
 <tr id=ma>
  <td align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Suma 
    <input type="text" name="monto" size="14" style="border-style:none;background-color:'transparent';font-weight: bold;" value="0" size="12" readonly>
    <input type="button" name="boton" value="Reset" onclick="resetear()">
    </b></font>
   </td>
    <td>
      <? echo "TOTAL: <span class='text_4'>".$suma."</span>"?>
    </td>
   <?
//	foreach ($total_moneda as $totales) {
//			echo "<td align=right>Total en ".$totales["nombre"].": <span class='text_4'>".$totales["simbolo"]."&nbsp;".$totales["total"]."</span></td>";
//	}
	/*
   <td align=right>
    Total en pesos: $ <input type="text" class="text_4" readonly name="total_pesos" value="">
   </td>
   <td align=right>
	Total en dolares: U$S <input type="text" class="text_4" readonly name="total_dolares" value="">
   </td>
	*/
	?>
 </tr>
</table>
<table border=0 width=100% cellspacing=2 cellpadding=3  align=center >
  <tr>
   <td align=left id=ma>
    <b>Total:</b><?=$total_facturas?>
   </td>
   <td align=left id=ma>
    <?=($link_pagina)?$link_pagina:"&nbsp;"?>
   </td>
  </tr>
</table>  
<table border=0 width=100% cellspacing=2 cellpadding=3  align=center class="bordes">  
  <tr>
   <td align=right id=mo></td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link("busq_avanzada_facturas.php",array("sort"=>"1","up"=>$up))?>'>Fecha</a>
   </td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link("busq_avanzada_facturas.php",array("sort"=>"2","up"=>$up))?>'>Nº Factura</a>
   </td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link("busq_avanzada_facturas.php",array("sort"=>"3","up"=>$up))?>'>Tipo</a>
   </td>
   <? if($estado=="todas") 
   {?>
    <td align=right id=mo>Estado</td>
<? } ?>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link("busq_avanzada_facturas.php",array("sort"=>"4","up"=>$up))?>'>Cliente</a>
   </td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link("busq_avanzada_facturas.php",array("sort"=>"5","up"=>$up))?>'>Monto Total</a>
   </td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link("busq_avanzada_facturas.php",array("sort"=>"6","up"=>$up))?>'>Creada por</a>
   </td>
<?if ($estado=="terminadas" || $estado=="todas" || $estado=="anuladas") 
  { ?>
   <td align=right id=mo></td> 
<?} ?>
 </tr>
<? 	$l=1;
    while (!$facturas->EOF ) 
    { 
     $ref = encode_link("factura_nueva.php",array("id_factura"=>$facturas->fields['id_factura']));
     ?>
     <tr <?=atrib_tr($bgcolor_out)?>>
      <td><input type="checkbox" name="chk_<?php echo $l; ?>" value="<?php echo number_format($facturas->fields['total'], 2, ',', '.')?>" onclick="return calcular_monto(chk_<?php echo $l; ?>,tipo_moneda_<?php echo $l; ?>);">
      </td>
      <?$nose=$facturas->fields['nro_factura'];
        $parte=ereg_replace("\*","",$nose);
      ?>
      <a href='<?=$ref?>'>
      <td align="center"><font color="#006699" size=-2><b><? echo Fecha($facturas->fields['fecha_factura']) ?></b></font></td>
      <td align="center"><font color="#006699" size=-2><b><? echo $parte ?></b></font></td>
      <td align="center"><font color="#006699" size=-2><b><? echo strtoupper($facturas->fields['tipo_factura']); ?></b></font></td>
      <? if($estado=="todas") { ?>
      <td align="center"><font color="#006699"><b>
      <? switch ($facturas->fields['estado']) {
        		case 'a':
        		case 'A': echo "Anulada";break;
        		case 'p':
			  	case 'P': echo "Pendiente";	break;
        		case 't':
			  	case 'T': echo "Terminada";	break;		
         } ?>
         </b></font></td></a>
      <? } ?>
      <td align="center"><font color="#006699" size=-2><b><? echo $facturas->fields['cliente'] ?></b></font></td>
      <td><table width="100%"><tr>
           
           <input type="hidden" name="tipo_moneda_<?php echo $l; ?>" value="<?php if ($facturas->fields['simbolo']=="$") echo 0; else echo 1; ?>"> 
           <td width="20%" align="left"><font color="#006699" size=-2><b><?=$facturas->fields['simbolo']?></b></center></font></td>
           <td width="80%" align="right"><font color="#006699" size=-2><b><?= number_format($facturas->fields['total'], 2, ',', '.') ?></b></center></font></td>
      </tr></table></td>
      <td align="center"><font color="#006699" size=-2><b><? echo $facturas->fields['usuario'] ?></b></font></td>
      <? if ($estado=="terminadas" || $estado=="todas" || $estado=="anuladas") { ?>
        <td align="center">
        <? switch ($facturas->fields['estado']) {	
        	case 'p': break;
      		case 'a': 
 			case 't': 
              if ($facturas->fields['id_renglones_oc'])
                  $seg=1; //la factura se creo desde el menu Produccion/entregas
              else $seg=0;
			  if ($facturas->fields['tipo_factura']=='a')
			     $link=encode_link("facturaA_pdf.php", array("id_factura"=>$facturas->fields['id_factura'],"seg"=>$seg));	
			  else  
			     $link=encode_link("facturaB_pdf.php", array("id_factura"=>$facturas->fields['id_factura'],"seg"=>$seg));	
		      echo "<a target='_blank' href='".$link."'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'></a>";
		      //Si es factura de Bs AS
		      if ($facturas->fields['tipo_factura']=='a')
			     $link=encode_link("facturaA_bsas_pdf.php", array("id_factura"=>$facturas->fields['id_factura'],"seg"=>$seg));	
			  else  
			     $link=encode_link("facturaB_bsas_pdf.php", array("id_factura"=>$facturas->fields['id_factura'],"seg"=>$seg));	
		      echo "<a target='_blank' href='".$link."' title='Para Bs As'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'></a>";
        } ?>
        </td>
        <? 	}
  		echo   "</a></tr>";
		/*
  		if($facturas->fields['simbolo']=='$')
  		 $total_en_pesos+=$facturas->fields['total'];
  		else 
  		 $total_en_dolares+=$facturas->fields['total'];
		 */
  		$facturas->MoveNext();
  		$l++;
  }
?>
    </table>
    <?
	/*
     <script>
     document.all.total_pesos.value="<?=formato_money($total_en_pesos)?>";
     document.all.total_dolares.value="<?=formato_money($total_en_dolares)?>";
    </script>
	*/
	?>
</form>
<input type="hidden" name="cantidad_check" value="<?php echo $l-1; ?>">

<br>
<?fin_pagina();?>