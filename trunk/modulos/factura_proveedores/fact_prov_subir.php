<?php
/*
$Author: mari $
$Revision: 1.88 $
$Date: 2006/12/29 14:10:08 $
*/
require_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");
function prepare_distib(&$arr)
{
	global $id_fact;
	foreach ($arr as $index => $sim_arr)
			$arr[$index]['id_factura']=$id_fact;
}

function muestra_detalle($id_fact,$flag) {
	global $bgcolor2,$parametros;

  $query="select fact_prov.*,proveedor.id_proveedor,proveedor.razon_social,proveedor.cuit as cuit_prov,proveedor.nbre_fantasia as nbre_fant from fact_prov join proveedor using (id_proveedor) where id_factura=".$id_fact;
  $res=sql($query,"En muestra detalle ") or fin_pagina();

?>
 <br>
 <div align="center">
<? if ($flag==1) echo "<div align=center> <font color='BLUE' size=4> DATOS DE LA FACTURA EXISTENTE </font> </div>";?>
 <table border=1 cellspacing=0 cellpadding=3 bgcolor=<? echo $bgcolor2; ?>>
   <tr> 
	 <td style='border: <? echo $bgcolor3;?>' colspan=4 align=center id=mo><strong>Detalle Factura: ID <?= $id_fact?> </strong> <br></td>
  </tr>
  <tr>
     <td align="center" colspan="2"><strong>Nº Factura:</strong> <? echo $res->fields['nro_factura']?></td>
     <td align="center" colspan="2"><strong>Tipo Factura:</strong> <? echo $res->fields['tipo_fact']?></td>
  </tr>
  <tr>
     <td align="center" colspan="4" ><strong>Fecha de Emisi&oacute;n :</strong> <? echo Fecha($res->fields['fecha_emision'])?></td>
  </tr>
  <tr>
     <td colspan="4">
	      <table width="100%" id="tabla">
            <tr> 
              <td colspan="2"><strong>Monto en 
                <?= (($res->fields['moneda']==2 && $res->fields['monto_dolar']==0)?"Dolares:":"Pesos:") ?>
                </strong> 
                <?= formato_money($res->fields['monto'])?>
              </td>
              <td width="228"><strong>Impuestos Internos: </strong> <? echo formato_money($res->fields['imp_internos'])?></td>
            </tr>
            <tr>
             <td colspan="2">
              <?
              if($res->fields['percepcion_iva']!="" && $res->fields['percepcion_iva']!=0)
              {?>
               <b>Percepción I.V.A.:</b> <?=formato_money($res->fields['percepcion_iva'])?>
              <?
              }
              else 
              {echo "&nbsp;";
              }?>  
             </td>
             <td colspan="2">
              <?
//para evitar errores en la consulta
if ($id_fact=="")	$id_fact=-1;

//selecciono todos los ingresos brutos de las distintas provincias
$q ="select id_ib,id_distrito,monto_ib,nombre from percepciones_ib ";
$q.="join distrito using(id_distrito) where id_factura=$id_fact order by nombre";
$res_ib=sql($q)or fin_pagina();
//$disable_ib=$res_ib->recordcount()==0;
              if($res_ib->recordcount())
              {
           	  	echo "<table border=0 width=100% cellspacing=1 style='border-color:gray'>\n";
               echo "<tr><td colspan=2><b>Percepción Ingresos Brutos</b></td></tr>\n";
              	do 
              	{
              		echo "<tr><td>".$res_ib->fields["nombre"]."</td><td align=right>".formato_money($res_ib->fields['monto_ib'])."</td></tr>\n";
              		$res_ib->movenext();
              	}while (!$res_ib->EOF);
              	echo "</table>\n";
              } 
              else 
              	echo "&nbsp;";
				  ?>
             </td>
            </tr>
            <? if ($res->fields['monto_dolar']!=0) {?>
            <tr> 
              <td colspan="2"><strong>Monto en Dolares:</strong> 
                <?= formato_money($res->fields['monto_dolar'])?>
              </td>
              <td><strong>Cotizaci&oacute;n Dolar: </strong>
                <?= formato_money($res->fields['cotizacion_dolar'])?>
                </td>
            </tr>
            <? } ?>
            <tr> 
              <td width="209">&nbsp;</td>
              <td width="105">&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr> 
              <td colspan="2" align="right"><strong>Iva 10.5 % </strong>&nbsp;&nbsp;&nbsp;</td>
              <td><? echo formato_money ($res->fields['iva10'])?></td>
            </tr>
            <tr> 
              <td colspan="2" align="right"><strong>Iva 21 % &nbsp;&nbsp;&nbsp;</strong></td>
              <td><? echo formato_money ($res->fields['iva21'])?></td>
            </tr>
            <tr> 
              <td colspan="2" align="right"><strong>Iva 27 %&nbsp;&nbsp;&nbsp;&nbsp; </strong></td>
              <td><? echo formato_money ($res->fields['iva27'])?></td>
            </tr>
            <tr> 
              <td colspan="2">&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr> 
              <td colspan="3" align="center"><strong>Neto 
                <?= (($res->fields['moneda']==2 && $res->fields['monto_dolar']==0)?"Dolares":"Pesos") ?>
                :</strong> <? echo formato_money($res->fields['neto'])?> </td>
            </tr>
          </table>
	</td>
      
	  </tr>
	  <tr>
	   <td colspan="4"><? echo "<strong>Comentario:</strong>"."<br><br>"; if ($res->fields['comentario']!="") echo html_out($res->fields['comentario']); else echo 'No hay comentarios cargados'?></td>
	  </tr>
	   <tr>
	  	   <td colspan="4"><? echo "<strong>Guardada en:</strong>"."<br><br>"; if ($res->fields['guardar_en']!="") echo html_out($res->fields['guardar_en']); else echo 'No se ha especificado un lugar'?></td>
	  </tr>
	  <tr>
	   <td colspan="4">
	     <table>
	        <tr><td> <strong>Proveedor:</strong></td>
	            <td><? echo html_out($res->fields['razon_social'])?></td>
	        </tr>
			<tr> 
			<td><strong>CUIT / CUIL: </strong></td>
			<td><? if ($res->fields['cuit'] !="") echo $res->fields['cuit']; else echo $res->fields['cuit_prov'];?></td>
			</tr>
			<tr> 
			<td><strong>Nombre Fantasía: </strong></td>
			<td> <? if ($res->fields['nbre_fantasia']!="") echo $res->fields['nbre_fantasia']; else echo $res->fields['nbre_fant']; ?></td>
			</tr>
	     </table>
	   </td>
	  </tr>
	  <tr>
	  <? 	if ($res->fields["ultimo_usuario"]) {
				$mm = substr($res->fields["ultimo_usuario_fecha"],5,2);
				$dm = substr($res->fields["ultimo_usuario_fecha"],8,2);
				$ym = substr($res->fields["ultimo_usuario_fecha"],0,4);
				$hm = substr($res->fields["ultimo_usuario_fecha"],11,5);}?>
	  <td colspan=4><strong> Ultima modificación hecha por </strong><? echo $res->fields['ultimo_usuario'] ?> el <? echo $dm.'/'.$mm.'/'.$ym  ?> a las <? echo $hm?>
	  <br> <? if ($res->fields['control_suma']==1) echo 'EL MONTO TOTAL NO COINCIDE CON LA SUMA DEL PORCENTAJE DEL IVA MAS EL NETO';
	  else echo ''?>
	  </td></tr>
	  
      <? $query_arch="select * from arch_prov where id_factura=$id_fact";
		 $res_arch=sql($query_arch,"al seleccionar archivos") or fin_pagina();?>
		<tr> <td colspan='4'><strong>Archivos:</strong>
		<? if ($res_arch->RecordCount()>0){ 
		//echo "</td></tr>";
		echo "<tr><td colspan='4'>";
	    echo "<div align='center'> <table>";
		echo "<tr id=mo>";
		echo "<td align='center'><B>Nombre</B></td>";
		echo "<td align='center'><B>Fecha de Cargado</B></td>";
		echo "<td align='center'><B>Cargado Por</B></td></tr>";
		
		  while (!$res_arch->EOF) {
		   echo "<td>";
			       if (is_file("../../uploads/facturacion/".$id_fact.'-'.$res_arch->fields["nbre_arch"])){
                  	
			       /*	if ($_GET['id_fact'])  {
                 $id_fact=$_GET['id_fact'];
				
				 }
                  else {
				  $id_fact=$parametros['fact'];
				
				  }*/
				  echo "<a href='".encode_link("fact_prov_subir.php",array ("fact"=>$id_fact,"file" =>$res_arch->fields["nbre_arch"],"size" => $res_arch->fields["tam_arch"],"cmd" => "download","fila"=>$parametros['fila']))."'>";
                  echo $res_arch->fields["nbre_arch"]."</a>";}
            echo "</td>\n";
			echo "<td align='center'>"; echo Fecha($res_arch->fields['fecha_carga'])."</td>\n";
			echo "<td align='center'>"; echo $res_arch->fields['subidopor']."</td>\n";
            $res_arch->MoveNext();
			echo "</tr>";
         }
		echo "</table></div>";
		 
		echo "</td></tr>\n";
		echo "</td></tr>\n";
		}
		else {
        echo "<table><tr><td >";
		echo 'No hay archivos cargados';
		echo "</td></tr></table></td></tr>";
		}?>
     
 </table>
<br>
 <? 
 if ($_GET['id_fact']) { 
  $id_fact=$_GET['id_fact'];
  $fecha_factura=$parametros['fecha_factura'];  
  }
 elseif ($parametros['fact']) {
  $id_fact=$parametros['fact']; 
  }
  elseif ($_POST['id_fact_exist']) 
  $id_fact=$_POST['id_fact_exist'];
 

$link2=encode_link("fact_prov_subir.php",array("fact"=>$id_fact,"cmd"=>"Eliminar"));
$link3=encode_link("fact_prov_arch.php", array("fact"=>$id_fact,"nro"=>$res->fields['nro_factura'],"nro_orden"=>$parametros['nro_orden'],"fecha_factura"=>$fecha_factura,"fila"=>$parametros['fila']));?>
 

 <table>
  <tr>
  <? if ($parametros['estado']!='t') { ?>
	<td><input name="Modificar" type="submit" value="Modificar" <?=$permiso?>></td>
  	<td><input name="Subir" type="button" value="Subir Archivo" <?=$permiso?> onClick="location.href='<? echo $link3?>'"></td>
	<? }
	if ($parametros['nro_orden']){
	  if ($cambio_fecha!='') $fech=$cambio_fecha;
	     else $fech=Fecha($res->fields['fecha_emision']);
		  $valor="window.opener.document.all.fecha_factura_".$parametros['fila'].".value='".$fech."';window.close()"; }
	  else $valor="location.href='fact_prov_listar.php'"; ?>
	  <td><input name="volver" type="button" value="Volver" Onclick="<? echo $valor?> "></td>
	
  </tr>
 </table>

  </div>
<?
} //fin muestra_detalle



if (permisos_check("inicio","permiso_ver_fact"))
               $permiso="";
            else
               $permiso="disabled";
               
if (($parametros['fact']) || ($_GET['id_fact'])) {
 if ($_GET['id_fact']) {
   $id_fact=$_GET['id_fact'];
   $fecha_factura=$parametros['fecha_factura'];
   }
 else $id_fact=$parametros['fact'];

$cmd=$parametros["cmd"];

if ($cmd=="download") {
    $file=$parametros["file"];
    $size=$parametros["size"];
    $rma=$parametros["rma"];
    Mostrar_Header($file,"application/octet-stream",$size);
    if($rma==1)
    {
    $id_fact=$parametros["fact"];	
    $filefull = UPLOADS_DIR ."/facturacion/".$id_fact.'-'.$file;
    }
    else
    $filefull = UPLOADS_DIR ."/facturacion/".$id_fact.'-'.$file;
    readfile($filefull);
    exit();
}
}

 if (!$_POST['monto_dolar'])
	$_POST['monto_dolar']=0;
 if (!$_POST['cotizacion_dolar'])
  	$_POST['cotizacion_dolar']=0;

if ($_POST['eliminar']=='Eliminar Archivo'){

$archivos=PostvartoArray('elim_'); //crea un arreglo con los checkbox chequeados
$tam_arch=sizeof($archivos); 

if ($archivos){  //para ver si hay check seleccionados
 $list='(';
 foreach($archivos as $key => $value){
   $list.=$value.',';
 }
 $list=substr_replace($list,')',(strrpos($list,',')));

 $query="select nbre_arch,id_arch_fact from arch_prov where id_arch_fact in $list";
 $res_archivo=sql($query,"en eliminar archivo a seleccionar archivos") or fin_pagina(); 
  while (!$res_archivo->EOF) {
    if (!unlink(UPLOADS_DIR."/facturacion/".$id_fact.'-'.$res_archivo->fields['nbre_arch']))
      Error("No se encontro el archivo");
    if (!$error){
    $id_arch=$res_archivo->fields['id_arch_fact'];
    $query="delete from arch_prov where id_arch_fact=$id_arch";
    sql($query,"Borrar Archivos") or fin_pagina();
  }
 $res_archivo->MoveNext();
}
} //fin de si hay check seleccionados
}

echo $html_header;
cargar_calendario();
?>
<!--incluye funciones para busqueda alfabetica en el select del proveedor  -->
<script src="<?=$html_root."/lib/funciones.js"?>" ></script>

<script language="javascript">

var ventana_prov = "";

function cargar_proveedor()
{
document.all.span_nbreprov.innerText=ventana_prov.proveedores[ventana_prov.document.all.select_proveedor.value]['nombre'];
document.all.cuit.value=ventana_prov.proveedores[ventana_prov.document.all.select_proveedor.value]['cuit'];
document.all.fantasia.value=ventana_prov.proveedores[ventana_prov.document.all.select_proveedor.value]['fantasia'];
document.all.id_proveedor.value=ventana_prov.document.all.select_proveedor.value;
}

if (!Number.toFixed)
	{
	Number.prototype.toFixed=
	function(x) {
   					var temp=this;
   					temp=Math.round(temp*Math.pow(10,x))/Math.pow(10,x);
   					return temp;
					};
	}
	
	
function control_suma(){

  if (document.all.neto.value == '') neto=0;
   else {
   neto= parseFloat(document.all.neto.value);
   var neto= new String(neto.toFixed(2));
   }
    
  if (document.all.monto.value == '') monto=0;
   else {
     monto=parseFloat(document.all.monto.value);
	 var monto= new String(monto.toFixed(2));
	 }
   
  if (document.all.imp_interno.value == '') imp=0;
   else {
      imp=parseFloat(document.all.imp_interno.value);
      var imp= new String(imp.toFixed(2));
	   }
   if (document.all.percepcion_iva_text.value == '') per_iva=0;
   else {
      per_iva=parseFloat(document.all.percepcion_iva_text.value);
      var per_iva= new String(per_iva.toFixed(2));
	   }
   if (document.all.monto_percepcion_ingb.value == '') per_ingb=0;
   else {
      per_ingb=parseFloat(document.all.monto_percepcion_ingb.value);
      var per_ingb= new String(per_ingb.toFixed(2));
	   }
	   	   
  if (document.all.valor_iva1.value == '') iva1=0;
   else {
       iva1=parseFloat(document.all.valor_iva1.value);
       var iva1= new String(iva1.toFixed(2));
       }
	   
  if (document.all.valor_iva2.value == '') iva2=0;
   else {
       iva2=parseFloat(document.all.valor_iva2.value);
       var iva2= new String(iva2.toFixed(2));
	    }
		
 if (document.all.valor_iva3.value == '') iva3=0;
   else {
       iva3=parseFloat(document.all.valor_iva3.value);
       var iva3= new String(iva3.toFixed(2));
	   }
	   
sum= parseFloat(imp) + parseFloat(per_iva) + parseFloat(per_ingb) + parseFloat(iva1) + parseFloat (iva2) + parseFloat(iva3) + parseFloat(neto);
var res = new String(Math.abs(monto-sum).toFixed(2));
if ( res > 0.10 ){
   sigue=confirm ("La suma del neto y porcentaje iva no coinciden con el total. Desea continuar");
   document.all.control.value=1;
   }
else {
      document.all.control.value=0;
      return true;
	 }
if (sigue) return true;
   else return false;

}

function control_datos(){

if (document.all.moneda[1].checked == true ) {  //si esta en dolares
 if (isNaN(document.all.cotizacion_dolar.value)) {
  alert ('Debe ingresar el número valido para cotizacion dolar');
   return false;
}

 if (isNaN(document.all.monto_dolar.value)) {
  alert ('Debe ingresar el número valido para monto dolar');
   return false;
}
}

 if (document.all.nro_fact.value=='')
  {alert ('Debe ingresar el número de factura');
   return false;
  }
 if (document.all.fecha_emision.value=='')
  {alert ('Debe ingresar la fecha de emisión');
   return false;
  }
 
  if ( (document.all.monto.value=='') || (isNaN(document.all.monto.value))) {
    alert ('Debe ingresar el monto valido para la factura');
    return false;
  }

  if ( (document.all.impuesto.checked==true) && isNaN(document.all.imp_interno.value)) { 
     alert ('Debe ingresar número valido para el impuesto interno');
    return false;
  }
  
  if ( (document.all.percepcion_iva.checked==true) && (isNaN(document.all.percepcion_iva_text.value) || document.all.percepcion_iva_text.value=="")) { 
     alert ('Debe ingresar número valido para la Percepción de I.V.A.');
    return false;
  }

  //checkear distritos y montos
  if (document.all.percepcion_ingb.checked && !fnCheckIb()) 
  { 
     alert ('\tPercepción Ingresos Brutos\t\n\nPor favor elija los distritos e ingrese montos numericos validos');
    return false;
  }
  if (document.all.id_proveedor.value==0)
  {alert ('Debe seleccionar un proveedor');
   return false;
  }
 
  if ((document.all.neto.value== '') || (isNaN(document.all.neto.value) ))
  {alert ('Debe ingresar un número valido para valor neto');
   return false;
  }
 
  if (control_suma()) return true;
  else return false;
}


function set_value (valor){
if (valor!=0){
info=eval ("prov_"+ valor);
document.all.cuit.value=info ['cuit'];
document.all.fantasia.value=info ['fantasia'];
}
}

//esta funcion calcula el iva sobre el monto total , o sobre el monto total - impuesto interno en el caso que este chequedo
function calcula_iva (valor,ctrl) {
obj=eval ("document.all.valor_iva"+valor);
obj.disabled= !ctrl;

monto_total=parseFloat(document.all.monto.value);
if (document.all.impuesto.checked==true){
  impuesto=parseFloat(document.all.imp_interno.value);
  monto_total-=impuesto;
  neto=monto_total-impuesto;
  }
  else neto=monto_total;

var per_iva;
if (document.all.percepcion_iva.checked==true){
  per_iva=parseFloat(document.all.percepcion_iva_text.value);
  monto_total-=per_iva;
  neto=monto_total-per_iva;
  }
  else neto=monto_total;

var per_ingb;
if (document.all.percepcion_ingb.checked==true){
  per_ingb=parseFloat(document.all.monto_percepcion_ingb.value);
  monto_total-=per_ingb;
  neto=monto_total-per_ingb;
  }
  else neto=monto_total;  

  
var monto= new String(monto_total.toFixed(2));

switch (valor) {
case 1:  if (document.all.iva1.checked==true) {
           var i1= new String((monto / 1.105 * 0.105).toFixed(2));
           document.all.valor_iva1.value=i1;
           if ((document.all.iva2.checked != true) &&  (document.all.iva3.checked != true))  
		   { var n= new String((monto / 1.105).toFixed(2));
		     document.all.neto.value=n;
		   }
		   else   document.all.neto.value="";
		 }
		 else {
		    document.all.valor_iva1.value="";
		    document.all.neto.value="";
			}
	    break;
case 2: if (document.all.iva2.checked==true) {
           var i1= new String((monto / 1.21 * 0.21).toFixed(2));
           document.all.valor_iva2.value=i1;
           if ((document.all.iva1.checked != true) &&  (document.all.iva3.checked != true))  
		   { var n= new String((monto / 1.21).toFixed(2));
		     document.all.neto.value=n;
		   }
		   else   document.all.neto.value="";
		 }
		 else {
		    document.all.valor_iva2.value="";
		    document.all.neto.value="";
			}
	    break;

case 3: if ( document.all.iva3.checked==true) {
              var i3= new String((monto / 1.27 * 0.27).toFixed(2));   
              document.all.valor_iva3.value=i3;
			  if ((document.all.iva2.checked != true) &&  (document.all.iva1.checked != true))  
 				 { var n= new String((monto / 1.27).toFixed(2));
			      document.all.neto.value=n;
			     }
				 else 
			      document.all.neto.value="";
		}
		else {
		       document.all.valor_iva3.value="";
			   document.all.neto.value="";
			   }
	    break;
}
}

function limpiar (valor){
if (valor==1) {document.all.impuesto.checked=false; document.all.imp_interno.disabled=true;}
if (valor!=0 && valor!=3 && valor!=4) document.all.imp_interno.value="";
if (valor==2) document.all.imp_interno.disabled=!document.all.impuesto.checked;
if (valor==3) 
{document.all.percepcion_iva_text.disabled=!document.all.percepcion_iva.checked;
 if(document.all.percepcion_iva.checked==0)
 {document.all.percepcion_iva_text.value="";
  
 }
}
if (valor==4)
{
	//DESACTIVAR LOS SELECT Y MONTOS INGRESOS BRUTOS
	fnDisableIb(!document.all.percepcion_ingb.checked);
}
document.all.iva1.checked=false;
document.all.iva2.checked=false;
document.all.iva3.checked=false;
document.all.valor_iva1.value="";
document.all.valor_iva2.value="";
document.all.valor_iva3.value="";
document.all.neto.value="";
}
var dolar=0;
function mostrar_dolar(valor)
{
	//variable dolar es global
	if (valor)
	{
		if (!dolar)
		{
		var fila=document.all.tabla.insertRow(1);
		fila.insertCell(0).innerHTML="<td align='right'>&nbsp;</td>";
		fila.insertCell(1).innerHTML="<td>Cotización Dolar</td>";
		fila.insertCell(2).innerHTML="<td align='right' ><input name='cotizacion_dolar' type='text' size='10' value='"+document.all.hcotizacion_dolar.value +"' onkeypress='if (event.keyCode == 13) return (calcular_pesos())' onblur='calcular_pesos()' onchange='limpiar(1)'></td>";
		
		fila=document.all.tabla.insertRow(2);
		fila.insertCell(0).innerHTML="<td align='right'>&nbsp;</td>";
		fila.insertCell(1).innerHTML="<td>Monto en Dolares</td>";
		fila.insertCell(2).innerHTML="<td align='right' ><input name='monto_dolar' type='text' size='20' value='"+document.all.hmonto_dolar.value +"' onkeypress='if (event.keyCode == 13) return (calcular_pesos())' onblur='calcular_pesos()' onchange='limpiar(1)' ></td>";
		dolar=1;
		calcular_pesos();
		document.all.monto.readOnly=1;
		}
	}
	else
	{
		if (dolar)
		{
		 //document.all.monto.value=parseFloat(document.all.hmonto.value);
		document.all.tabla.deleteRow(1);
		document.all.tabla.deleteRow(1);
		dolar=0;
		document.all.monto.readOnly=0;
		}
		if (typeof(document.all.span_text)!='undefined')
			document.all.span_text.innerHTML='Monto en Pesos';
	}

}
function calcular_pesos()
{
	document.all.monto.value=(parseFloat(document.all.monto_dolar.value)*parseFloat(document.all.cotizacion_dolar.value)).toFixed(2);
	if (isNaN(document.all.monto.value))
		document.all.monto.value=0;
	return false;
}

function calcular_neto (){
var sum=0;
if (document.all.impuesto.checked==true) sum +=parseFloat (document.all.imp_interno.value);
if (document.all.percepcion_iva.checked) sum +=parseFloat (document.all.percepcion_iva_text.value);
if (document.all.percepcion_ingb.checked) sum +=parseFloat (document.all.monto_percepcion_ingb.value);
if (document.all.iva1.checked) sum +=parseFloat (document.all.valor_iva1.value);
if (document.all.iva2.checked) sum +=parseFloat (document.all.valor_iva2.value);
if (document.all.iva3.checked) sum +=parseFloat (document.all.valor_iva3.value);
monto=parseFloat (document.all.monto.value);
var neto= new String((monto-sum).toFixed(2));
document.all.neto.value= neto;
}
   function fnAddProv()
   {
   	var otabla=document.all.tabla_percepciones;
   	var fila=otabla.insertRow(otabla.rows.length);
   	var i=otabla.rows.length-2;
  	  	fila.insertCell(0).innerHTML="<input type='hidden' name='id_ib"+i+"'><input type='text' name='monto_ib"+i+"' size=8  value=0 onclick='this.select()' onChange='fnSetMontoIB();limpiar(0)'>";
	  	fila.insertCell(1).innerHTML="<select name='dist_ib"+i+"'>"+document.all.dist_ib0.innerHTML+"</select>";
	  	eval("document.all.dist_ib"+i+".selectedIndex=0");
   }
   function fnDisableIb(boolVal)
   {
   	//le resto uno por el encabezado
   	var max=document.all.tabla_percepciones.rows.length-1;
   	for (var i=0; i < max ; i++)
	  		eval("document.all.bnuevaprov.disabled=document.all.monto_ib"+i+".disabled=document.all.dist_ib"+i+".disabled=boolVal");
   }
   function fnCheckIb()
   {
   	//le resto uno por el encabezado
   	var max=document.all.tabla_percepciones.rows.length-1;
   	var retVal=true;
   	for (var i=0; i < max ; i++)
   	{
	  		eval("retVal=(document.all.dist_ib"+i+".selectedIndex==0)");
	  		eval("retVal=retVal?(document.all.monto_ib"+i+".value==''):true");
	  		if (retVal==false)
	  			return false;
   	}
   	return retVal;
   }
	function fnSetMontoIB()
	{
		//cantidad de distritos mostrados, le resto uno por la fila encabezado
		var max=document.all.tabla_percepciones.rows.length-1;
		var montoib=0;
		for (var i=0; i < max ; i++)
			eval("montoib+=parseFloat(document.all.monto_ib"+i+".value=document.all.monto_ib"+i+".value.replace(',','.'))");
		document.all.monto_percepcion_ingb.value=montoib;
	}
   
</script>
<?
if ($_POST['id_fact_exist'])
  $id_fact=$_POST['id_fact_exist'];
elseif ($_GET['id_fact']){ 
   $id_fact=$_GET['id_fact'];
   //$fecha_factura=$parametros['fecha_factura'];
     }
   else {
   $id_fact=$parametros['fact'];
   //$fecha_factura=$parametros['fecha_factura'];
   
  }
 $link1=encode_link("fact_prov_subir.php",array("fact"=>$id_fact,"nro_orden"=>$parametros['nro_orden'],"fecha_factura"=>$cambio_fecha,"fila"=>$parametros['fila'])); ?>


<form action='<? echo $link1?>'method="post" name="form" enctype='multipart/form-data'>

<input name="control" type="hidden" value="0">
<?


if (($parametros['fact']) || ($_GET['id_fact']) || $_POST['id_fact_exist']) { //tengo el id de la factura

if ($_POST['id_fact_exist']) {
   $id_fact=$_POST['id_fact_exist'];
}
elseif ($_GET['id_fact']) {
   ($_GET['id_fact']);  
}
 else  $id_fact=$parametros['fact'];
 
 
 /*------------------------------ GUARDAR MODIFICACIONES -------------------------*/
if ($_POST['guardar_cambio']=='Guardar')
{
   $db->StartTrans();
    list($d,$m,$a) = explode("/",$_POST['fecha_emision']);
    if (FechaOk($_POST['fecha_emision'])) {
     $fe_emision = "$a-$m-$d";
    }
    else  Error("La fecha de emisión ingresada es inválida");

    if (!es_numero($_POST['monto'])) {
        Error("El Importe ingresado es inválido");
	}
	
	//no se pueden guardar factura con el mismo numero y mismo tipo del mismo proveedor,
    // salvo que sea de tipo EXT 

	$TF=$_POST['tipo_fact'];
	
	if ( $TF != 'Ext') 
	{
	 $num=$_POST['nro_fact'];
	 $prov=$_POST['id_proveedor'];
     $sql="select id_factura 
		  from general.fact_prov 
		  where nro_factura='$num' and id_proveedor=$prov and tipo_fact = '$TF'";
     $res=sql($sql,"EN Guardar Modificaciones") or fin_pagina();
   
    if ($res->RecordCount() >0 && $res->fields['id_factura']!=$id_fact) 
    {
       Error("Existe una Factura con igual número y tipo para el proveedor seleccionado");
       $flag_exist=1;
       $id_fact_exist=$res->fields['id_factura']; //id de la factura que ya existe
       echo "<input type='hidden' name='id_fact_exist' value='$id_fact_exist'>";
    }//de if ($res->RecordCount() >0 && $res->fields['id_factura']!=$id_fact) 
   }//de if ( $TF != 'Ext') 

 if (!$error)
 {   //UPDATE
     if ($_POST['valor_iva1']) $iva1=$_POST['valor_iva1'];
     else $iva1=0;
     if ($_POST['valor_iva2']) $iva2=$_POST['valor_iva2'];
     else $iva2=0;
     if ($_POST['valor_iva3']) $iva3=$_POST['valor_iva3'];
     else $iva3=0;
     if ($_POST['imp_interno']!="") $imp=$_POST['imp_interno'];
     else $imp=0;
     if ($_POST['percepcion_iva_text'])$per_iva=$_POST['percepcion_iva_text'];
     else $per_iva=0;

     
     //actualiza en la base de datos los campos modificados
     $query_update="UPDATE fact_prov SET  nro_factura='".$_POST['nro_fact']."',
     id_proveedor=".$_POST['id_proveedor'].",fecha_emision='$fe_emision',
     moneda=".$_POST['moneda'].",monto=".$_POST['monto'].",
     comentario='".$_POST['comentario']."',tipo_fact='".$_POST['tipo_fact']."',
     guardar_en='".$_POST['guardar_en']."', ultimo_usuario='".$_ses_user['name']."',
     ultimo_usuario_fecha='".date("Y-m-d H:i:s",mktime())."',imp_internos=$imp, 
     percepcion_iva=$per_iva,"//monto_percepcion_ingb=$per_ingb,dist_ingb=$dist_ingb,
     ."iva10=$iva1, iva21=$iva2, iva27=$iva3,neto=".$_POST['neto'].", cuit='".$_POST['cuit']."', 
     nbre_fantasia='".$_POST['fantasia']."',control_suma=".$_POST['control'].",
     monto_dolar=$_POST[monto_dolar],cotizacion_dolar=$_POST[cotizacion_dolar] 
     where id_factura=$id_fact";
     sql($query_update,"en update") or fin_pagina();
     //actualiza en tabla proveedores cuit y nbre fantasia  
     $campo=""; 
      if ($_POST["act_cuit"]) 
      {
       $valor=$_POST['cuit'];
       $campo="cuit='$valor'";
	  }
    if ($_POST['act_fantasia']) 
    {
	   if ($campo !="") $campo.=" ,";
       $valor=$_POST['fantasia'];
       $campo.=" nbre_fantasia ='$valor'";
	}	 
	  
   if ($campo!="")	
   {
     $update_prov="update proveedor set $campo where id_proveedor=".$_POST['id_proveedor'];
     sql($update_prov,"update proveedor") or fin_pagina();
   }
		 
	 $cambio_fecha=Fecha($fe_emision);
	 $link6=encode_link("fact_prov_subir.php",array("fact"=>$id_fact,"nro_orden"=>$parametros['nro_orden'],"fecha_factura"=>$cambio_fecha,"fila"=>$parametros['fila'])); 	
	 
	//actualizo los distritos de percepcion si se tildo el checkbox
	if ($_POST['percepcion_ingb'])
	{
		$arr=PostvartoArray("id_ib,dist_ib,monto_ib,id_factura");
		ArrayChangeKeyName($arr,array("dist_ib"=>"id_distrito"));
		$arr=ArrayRowsAsCols($arr);
		prepare_distib($arr);
		if (replace("percepciones_ib",$arr,array("id_ib"))!=0)
	      Error("Ocurrio un error al actualizar las percepciones de IB");
	   else 
	   	 aviso("Los datos se actualizaron correctamente.");
	}
   else
   {
   	//borro en caso de que se hayan insertado antes
   	$q ="delete from percepciones_ib where id_factura=$id_fact"; 
   	if (sql($q))
   	 aviso("Los datos se actualizaron correctamente.");
   	else 
	      Error("Ocurrio un error al borrar las percepciones de IB");
   }

    unset($_POST);
	
 }//fin !$error
 $db->CompleteTrans();
} //fin de $_POST[guardar_cambio]

 
/*---------------CREO FORMULARIO PARA MODIFICAR DATOS------------------------*/
if ($_POST['Modificar']=='Modificar') { 
if ($_POST['id_fact_exist']) 
      $id_fact=$_POST['id_fact_exist'];
  	elseif ($_GET['id_fact']) 
      $id_fact=$_GET['id_fact'];
    else
  	$id_fact=$parametros['fact'];
  	
   $query="select fact_prov.*,proveedor.id_proveedor,proveedor.razon_social,proveedor.nbre_fantasia from fact_prov join proveedor using (id_proveedor) where id_factura=".$id_fact;
   $res=sql($query," En post de modificar") or fin_pagina();
   
   ?>
   <br>
  <div align="center">
    <table cellspacing=0 cellpadding=3 bgcolor=<? echo $bgcolor2; ?>>
      <tr> 
        <td colspan="2" align="center"><strong> Modificar Datos </strong>ID 
          <?= $id_fact?>
          <br> </tr>
      <tr> 
        <td align="right">Nº Factura:</td>
        <td><input name="nro_fact" type="text" value='<? echo $res->fields['nro_factura']?>'></td>
      </tr>
      <tr> 
        <td align="right">Tipo Factura</td>
        <td> <select name="tipo_fact">
            <option value='A' <? if ($res->fields['tipo_fact']=='A') echo 'selected'?>>A</option>
            <option value='B' <? if ($res->fields['tipo_fact']=='B') echo 'selected'?>>B</option>
            <option value='C' <? if ($res->fields['tipo_fact']=='C') echo 'selected'?>>C</option>
            <option value='C' <? if ($res->fields['tipo_fact']=='E') echo 'selected'?>>E</option>
            <!-- son los monotributos -->
            <option value='M' <? if ($res->fields['tipo_fact']=='M') echo 'selected'?>>M</option>
            <!-- son los responsables inscriptos que el afip define que no puede hacer facturas a -->
            <option value='Pol' <? if ($res->fields['tipo_fact']=='Pol') echo 'selected'?>>Pol</option>
            <? if ($res->fields['tipo_fact']=='Tik/FA') { ?>
            
            <option value='Tik/FA' <? if ($res->fields['tipo_fact']=='Tik/FA') echo 'selected'?>>Tik/FA</option>
            <?}?>
            <option value='Ext' <? if ($res->fields['tipo_fact']=='Ext') echo 'selected'?>>Ext</option>
            <option value='ND' <? if ($res->fields['tipo_fact']=='ND') echo 'selected'?>>ND</option>
            <option value='NC' <? if ($res->fields['tipo_fact']=='NC') echo 'selected'?>>NC</option>
          </select> </td>
      </tr>
      <tr> 
        <td align="right">Fecha de Emisi&oacute;n:</td>
        <td><input name="fecha_emision" type="text" value='<? echo  Fecha($res->fields['fecha_emision'])?>' > 
          <?php echo link_calendario("fecha_emision"); ?></td>
      </tr>
      <tr> 
        <td colspan="2"> <table align="center"  bgcolor="#CCCCCC" id="tabla">
            <tr> 
              <td height="33" colspan="3" align="center">Monto en pesos 
                <input name="moneda" type="radio" value="1" checked onClick="mostrar_dolar(0);limpiar(1);">
                &nbsp;Monto en dolares 
                <input type="radio" name="moneda" value="2" <? if ($res->fields['moneda'] == 2) echo 'checked';?>  onClick="mostrar_dolar(1);limpiar(1)"></td>
            </tr>
            <tr> 
              <td height="30"  align="center">&nbsp;</td>
              <td><strong><?= (($res->fields['moneda']==2 && $res->fields['monto_dolar']==0)?"<span id=span_text align='rigth'>Monto </span>" : "<span id=span_text >Monto en Pesos</span>") ?>
                </strong></td>
              <td align="center"><input name="monto" type="text" id="monto" onChange="limpiar(1)" value='<? echo number_format($res->fields['monto'],2,".","")?>'></td>
            </tr>
            <tr> 
              <td><input name="impuesto" type="checkbox" <? if ($res->fields['imp_internos']!="" && formato_money($res->fields['imp_internos']) != formato_money(0.00)) echo 'checked'?> onClick="limpiar(2)"></td>
              <td>Impuestos Internos</td>
              <td><input type="text" name="imp_interno" value='<? if (formato_money($res->fields['imp_internos']) != formato_money(0.00)) echo number_format($res->fields['imp_internos'],2,".","") ?>'  <? if ($res->fields['imp_internos'] =="" || formato_money($res->fields['imp_internos']) == formato_money(0.00) ) echo 'disabled' ?> onChange="limpiar(0)" ></td>
            </tr>
            <tr> 
              <td><input name="percepcion_iva" type="checkbox" <? if ($res->fields['percepcion_iva']!="" && formato_money($res->fields['percepcion_iva']) != formato_money(0.00)) echo 'checked'?> onClick="limpiar(3)"></td>
              <td>Percepción I.V.A.</td>
              <td><input type="text" name="percepcion_iva_text" value='<? if (formato_money($res->fields['percepcion_iva']) != formato_money(0.00)) echo number_format($res->fields['percepcion_iva'],2,".","") ?>'  <? if ($res->fields['percepcion_iva'] =="" || formato_money($res->fields['percepcion_iva']) == formato_money(0.00) ) echo 'disabled' ?> onChange="limpiar(0)" ></td>
            </tr>
<?
//para evitar errores en la consulta
if ($id_fact=="")	$id_fact=-1;

//selecciono todos los ingresos brutos de las distintas provincias
//selecciono todos los ingresos brutos de las distintas provincias
$q ="select id_ib,id_distrito,monto_ib,nombre from percepciones_ib ";
$q.="join distrito using(id_distrito) where id_factura=$id_fact order by nombre";

$res_ib=sql($q)or fin_pagina();
$disable_ib=$res_ib->recordcount()==0;

?>
            <tr align="center">
             <td>
              &nbsp;<input name="percepcion_ingb" type="checkbox" <? if ($res_ib->recordcount()) echo "checked"?> onClick="limpiar(4);fnSetMontoIB();">
             </td>
             <td colspan="3" class="border"> 
              <table border="1">
               <tr>
                <td id=ma>
                 Percepción Ingresos Brutos <input type="button" name="bnuevaprov" <? if ($disable_ib) echo "disabled" ?> title="Agregar una nueva provincia para IB" value="+" onclick="fnAddProv()">
                </td>
               </tr>
               <tr>
                <td align="center">
                <table id=tabla_percepciones width="100%" align="center" border="0">
                <tr><td align="center" width="30%">Monto</td><td align="center">Provincia</td></tr>
<?
$i=0;
$total_ib=0;
$select_distrito=new HtmlOptionList("dist_ib_$i");
$select_distrito->add_event("onkeypress","buscar_op(this)");
$select_distrito->add_event("onblur","borrar_buffer()");
$select_distrito->add_event("onclick","borrar_buffer()");
$select_distrito->add_option("Seleccione","-1");
$query="select nombre,id_distrito from distrito order by nombre";
$distritos=sql($query,"<br>Error al traer los distritos") or fin_pagina();
$select_distrito->optionsFromResulset($distritos,array("text"=>"nombre","value"=>"id_distrito"));
$select_distrito->disabled=$disable_ib;
$res_ib->movefirst();
  do //imprimo una fila de percepcionIB
  {
?>
               <tr>
               	<input type="hidden" name="id_ib<?=$i?>" value="<?=$res_ib->fields['id_ib']?>">
                 <td><input type="text" name="monto_ib<?=$i?>" <? if ($disable_ib) echo "disabled" ?> size="8" value="<?=number_format($res_ib->fields['monto_ib'],2,".","")?>" onfocus="this.select()" onChange="fnSetMontoIB();limpiar(0)" ></td>
                 <td><? $select_distrito->name="dist_ib$i"; $i++; $select_distrito->setSelected($res_ib->fields['id_distrito']); echo $select_distrito->toBrowser();?></td>
               </tr>
<? $total_ib+=$res_ib->fields['monto_ib']==""?0:$res_ib->fields['monto_ib'];
	$res_ib->movenext(); 
  }while (!$res_ib->EOF);   ?>           
               <input type="hidden" name="monto_percepcion_ingb" value='<?=number_format($total_ib,2,".","")?>'>
               </table>
                </td>
               </tr>
              </table>  
             </td> 
            </tr>
            <tr> 
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr> 
              <td><input type="checkbox" name="iva1" <? if ($res->fields['iva10']!="" && formato_money($res->fields['iva10']) != formato_money(0.00) ) echo 'checked'?> onClick="calcula_iva(1,this.checked)"></td>
              <td align="center">Iva 10.5 % &nbsp;&nbsp;</td>
              <td><input type="text" name="valor_iva1" value='<? if (formato_money($res->fields['iva10']) != formato_money(0.00)) echo number_format($res->fields['iva10'],2,".","")?>' <? if ($res->fields['iva10'] =="" || formato_money($res->fields['iva10']) == formato_money(0.00) ) echo 'disabled'?> ></td>
            </tr>
            <tr> 
              <td><input type="checkbox" name="iva2" <? if (formato_money($res->fields['iva21']) != formato_money(0.00) && $res->fields['iva21'] !="") echo 'checked'?> onClick="calcula_iva(2,this.checked)" ></td>
              <td align="center">Iva 21 % </td>
              <td><input type="text" name="valor_iva2"  value='<? if (formato_money($res->fields['iva21']) != formato_money(0.00)) echo number_format($res->fields['iva21'],2,".","")?>'  <? if ($res->fields['iva21']=="" || formato_money($res->fields['iva21']) == formato_money(0.00)) echo 'disabled'?>></td>
            </tr>
            <tr> 
              <td><input type="checkbox" name="iva3" <? if ($res->fields['iva27'] !="" && formato_money($res->fields['iva27']) !=formato_money(0.00)) echo 'checked'?> onClick="calcula_iva(3,this.checked)"></td>
              <td align="center">Iva 27 %</td>
              <td><input type="text" name="valor_iva3"  value='<? if (formato_money($res->fields['iva27']) != formato_money(0.00)) echo number_format($res->fields['iva27'],2,".","")?>'  <? if ($res->fields['iva27'] =="" && formato_money($res->fields['iva27']) == formato_money(0.00) ) echo 'disabled'?> ></td>
            </tr>
            <tr> 
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr> 
              <td>Neto</td>
              <td colspan="2" align="center"><input name="neto" type="text" value='<? echo number_format($res->fields['neto'],2,".","")?>' onFocus="calcular_neto()"></td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td colspan="2"><table>
            <tr> 
              <td>Comentarios:</td>
              <td><textarea name="comentario" cols="50" rows="3"><? echo $res->fields['comentario']?></textarea></td>
            </tr>
          </table></td>
      </tr>
      <tr>
        <td colspan="2"><table>
            <tr> 
              <td>Guardada en:</td>
              <td><textarea name="guardar_en" cols="50" rows="3"><? echo $res->fields['guardar_en'] ?></textarea></td>
            </tr>
          </table></tr>
      <tr> 
        <td colspan="2"> <table>
            <tr>
              <td> Proveedor:</td>
              <td align="left">
             <? $link = encode_link("../general/cargar_proveedores.php",array("pagina_viene"=>"../factura_proveedores/fact_prov_subir.php","onclick_cargar"=>"window.opener.cargar_proveedor();window.close();","onclick_salir"=>"window.close();"));?>
             <span id="span_nbreprov"><?= $res->fields['razon_social']?></span>
			  <td> &nbsp; &nbsp; <input type="button" name="carga_prov" value="Cargar Proveedor" onclick="ventana_prov = window.open('<?=$link;?>','','')">
			  <input type="hidden" name="id_proveedor" value="<?=$res->fields['id_proveedor']?>">
            </td>
            </tr>
            <tr> 
              <td colspan="2"> <table align="center">
                  <tr> 
                    <td width="103">CUIT / CUIL:</td>
                    <td width="144"><input name="cuit" type="text"  value='<? if ($res->fields['cuit']!="") echo $res->fields['cuit']; else echo $cuit ?>'></td>
                    <td width="100"><input name="act_cuit" type="checkbox" <? if ($_POST['act_cuit']) echo 'checked'?>>
                      (*)</td>
                  </tr>
                  <tr> 
                    <td>Nombre Fantas&iacute;a:</td>
                    <td><input name="fantasia" type="text" value='<? if ($res->fields['nbre_fantasia'] !="") echo $res->fields['nbre_fantasia']; else echo $fantasia;?>' ></td>
                    <td><input name="act_fantasia" type="checkbox" <? if ($_POST['act_factasia']) echo 'checked'?> >
                      (*)</td>
                  </tr>
                  <tr>
                    <td colspan="3"><br>
                      (*) Si la/s casilla/s de verificaci&oacute;n est&aacute;n 
                      chequeadas se actualiza <br>
                      el cuit y/o nombre de fantas&iacute;a en los datos del proveedor 
                    </td>
                  </tr>
                </table></td>
            </tr>
            <? $query_arch="select * from arch_prov where id_factura=$id_fact";
		 $res_arch=sql($query_arch,"seleciona archivos en Modificar") or fin_pagina();
		 ?>
            <tr> 
              <td colspan='4'><strong>Archivos:</strong> 
                <? if ($res_arch->RecordCount()>0){ 
		echo "<tr><td colspan='4'>";
	    echo "<div align='center'> <table>";
		echo "<tr id=mo><td align='center'><B>Eliminar</B></td>";
		echo "<td align='center'>Nombre</td>";
		echo "<td align='center'>Fecha de Cargado</td>";
		echo "<td align='center'>Cargado Por</td></tr>";
		$i=0;
		  while (!$res_arch->EOF) {
		   echo "<tr><td align='center'><input name='elim_".$i."' type='checkbox' value='".$res_arch->fields['id_arch_fact']."' ></td>";
		   echo "<td>";
			       if (is_file("../../uploads/facturacion/".$id_fact.'-'.$res_arch->fields["nbre_arch"])){
                  /*	if ($_GET['id_fact'])  {
                     $id_fact=$_GET['id_fact'];
                  }
                    else {
					 $id_fact=$parametros['fact'];
					
				   }*/
				  echo "<a href='".encode_link("fact_prov_subir.php",array ("fact"=>$id_fact,"file" =>$res_arch->fields["nbre_arch"],"size" => $res_arch->fields["tam_arch"],"cmd" => "download","fila"=>$parametros['fila']))."'>";
                  echo $res_arch->fields["nbre_arch"]."</a>";}
            echo "</td>\n";
			echo "<td align='center'>"; echo Fecha($res_arch->fields['fecha_carga'])."</td>\n";
			echo "<td align='center'>"; echo $res_arch->fields['subidopor']."</td>\n";
            $i++;
			$res_arch->MoveNext();
			echo "</tr>";
         }
		echo "</table></div>";
		 
		echo "</td></tr>\n";
		echo "</td></tr>\n";
		}
		else {
        echo "<table><tr><td >";
		echo 'No hay archivos cargados';
		echo "</td></tr></table></td></tr>";
		}?>
          </table>
    </table>
<br>
 <table>
  <tr>
	<td><input name="guardar_cambio" type="submit" value="Guardar" <?=$permiso?> onClick="return control_datos();"></td>
	<? if ($res_arch->RecordCount() > 0) echo "<td><input name='eliminar' type='submit' value='Eliminar Archivo'> </td>";?>
    <? $cambio_fecha=Fecha($fe_emision);
  
	 $link6=encode_link("fact_prov_subir.php",array("fact"=>$id_fact,"nro_orden"=>$parametros['nro_orden'],"fecha_factura"=>$cambio_fecha,"fila"=>$parametros['fila'])); 	
	 ?>
	<td><input name="volver" type="button" value="Volver" Onclick="location.href='<?echo $link6?>'"></td>
  </tr>
 </table>

  </div>
<?
  
 }  //fin $_POST[modificar]

else { //crea formulario para ver detalle
  if  ($_GET['id_fact']) 
    $id_fact=$_GET['id_fact'];
  else
   $id_fact=$parametros['fact'];
  
     if ($id_fact_exist)  muestra_detalle($id_fact_exist,$flag_exist);
       else muestra_detalle($id_fact,$flag_exist);
}
} //fin de if $parametros || $GET


/************************FORMULARIO PARA NUEVA FACTURA****************************/
else {  
 if ($_POST['guardar']=='Guardar') {
 	
	//recupero el ultimo libro de iva que se cerro
	$q="select mes,anio from libro_iva_compras where fecha_cierre=(select max(fecha_cierre) from libro_iva_compras) ";
	$ultimo_libro=sql($q) or fin_pagina();
	//recupero el mes y año del libro de iva al que debe pertenecer la factura
 	if ($ultimo_libro->fields['mes']!="")
 	{
 		//cambio de año
 		if ($ultimo_libro->fields['mes']==12 && $ultimo_libro->fields['anio']+1==($anio=date('Y'))) 
 			$mes=1;
 		else
 		{
		 $mes=$ultimo_libro->fields['mes']+1;
		 $anio=$ultimo_libro->fields['anio'];
 		}
	} 
	
 list($d,$m,$a) = explode("/",$_POST['fecha_emision']);
 if (FechaOk($_POST['fecha_emision'])) {
     $fe_emision = "$a-$m-$d";
   }
   else  Error("La fecha de emisión ingresada es inválida");

  if (!es_numero($_POST['monto'])) {
        Error("El Importe ingresado es inválido");
	}
	
	//no se pueden guardar factura con el mismo numero y mismo tipo del mismo proveedor,
    // salvo que sea de tipo EXT 

	$TF=$_POST['tipo_fact'];
    if ($TF != 'Ext') { //si el tipo de factura es ext se puede repetir el numero de factura
    $num=$_POST['nro_fact'];
	$prov=$_POST['id_proveedor'];
    $sql="select id_factura 
		  from general.fact_prov 
		  where nro_factura='$num' and id_proveedor=$prov and tipo_fact='$TF'";
    $res=sql($sql,"control de factura existente") or fin_pagina();
  
   if ($res->RecordCount() >0) {
      Error("Existe una factura con igual número y tipo para el proveedor seleccionado");
      $flag_exist=1; 
      $id_fact_exist=$res->fields['id_factura'];
      echo "<input type='hidden' name='id_fact_exist' value='$id_fact_exist'>";
   }
   }
   
if (!$error)
{   //Insert
 $db->StartTrans();
 if ($_POST['valor_iva1']) $iva1=$_POST['valor_iva1'];
 else $iva1=0;
 if ($_POST['valor_iva2']) $iva2=$_POST['valor_iva2'];
 else $iva2=0;
 if ($_POST['valor_iva3']) $iva3=$_POST['valor_iva3'];
 else $iva3=0;
 if ($_POST['imp_interno']!="") $imp=$_POST['imp_interno'];
 else $imp=0;
 if ($_POST['percepcion_iva_text'])$per_iva=$_POST['percepcion_iva_text'];
 else $per_iva=0;

 
 if ($_POST['monto_percepcion_ingb'])$per_ingb=$_POST['monto_percepcion_ingb'];
 else $per_ingb=0;
 if($_POST["dist_ingb"]==-1)
  $dist_ingb="null";
 else 
  $dist_ingb=$_POST["dist_ingb"];
 
 $query="select nextval('fact_prov_id_factura_seq') as id_factura";
 $id=sql($query,"<br>Error al traer la secuencia de factura<br>") or fin_pagina(); 
 //variable que se usa en la funcion prepare()
 $id_fact=$id->fields["id_factura"];
 
 $query_insert="INSERT INTO fact_prov 
 (id_factura,nro_factura,id_proveedor,fecha_emision,moneda,monto,comentario,tipo_fact,guardar_en,
  ultimo_usuario,ultimo_usuario_fecha,imp_internos,percepcion_iva,
  iva10,iva21,iva27,neto,cuit,nbre_fantasia,control_suma,monto_dolar,cotizacion_dolar,
  mes_libro_iva,anio_libro_iva) 
 VALUES ($id_fact,'".$_POST['nro_fact']."',".$_POST['id_proveedor'].",'$fe_emision',
 ".$_POST['moneda'].",".$_POST['monto'].",'".$_POST['comentario']."', '".$_POST['tipo_fact']."',
 '".$_POST['guardar_en']."','".$_ses_user['name']."','".date("Y-m-d H:i:s",mktime())."',$imp,
 $per_iva,$iva1,$iva2,$iva3,".$_POST['neto'].",
 '".$_POST['cuit']."','".$_POST['fantasia']."',".$_POST['control'].",$_POST[monto_dolar],
 $_POST[cotizacion_dolar],$mes,$anio)";
 sql($query_insert) or fin_pagina();
 
 
 $campo=""; 
 if ($_POST["act_cuit"]) {
     $valor=$_POST['cuit'];
	 $campo="cuit='$valor'";
	}
 if ($_POST['act_fantasia']) {
      if ($campo!="") $campo.=" ,";
      $valor=$_POST['fantasia'];
      $campo.=" nbre_fantasia ='$valor'";
	  }	 
	  
if ($campo!="")	  {
  $update_prov="update proveedor set $campo where id_proveedor=".$_POST['id_proveedor'];
  sql($update_prov) or fin_pagina();
   }

 if ($parametros['nro_orden']) {
  if ($res_query->fields['id_factura'] && $res_query->fields['fecha_emision']){
  echo "<script>window.opener.document.all.id_factura_".$parametros['fila'].".value=".$res_query->fields['id_factura'].";";
  echo "window.opener.document.all.fecha_factura_".$parametros['fila'].".value='".Fecha($res_query->fields['fecha_emision'])."';";
  echo "window.close()</script>";
  }
 
 }
	//actualizo los distritos de percepcion si se tildo el checkbox
	if ($_POST['percepcion_ingb'])
	{
		$arr=PostvartoArray("id_ib,dist_ib,monto_ib,id_factura");
		ArrayChangeKeyName($arr,array("dist_ib"=>"id_distrito"));
		$arr=ArrayRowsAsCols($arr);
		prepare_distib($arr);
		if (replace("percepciones_ib",$arr,array("id_ib"))!=0)
       $msg="Ocurrio un error al insertar las percepciones de IB de la factura '{$_POST['nro_fact']}'";
	}
	if ($msg=="")
 		$msg="Los datos de la factura '{$_POST['nro_fact']}' se guardaron correctamente";
 $link=encode_link('fact_prov_subir.php',array("msg" => $msg));
 unset($_POST); 
 
?>
 <script>
    document.location.href='<?=$link?>';
 </script>
 <?
 $db->CompleteTrans();
 } //fin !$error
} //fin de $_POST[guardar]

 if ($_POST['guardar'] != 'Guardar') $FecEmision=date("d/m/Y",mktime());
        else $FecEmision=$_POST['fecha_emision'];
?>
<br>
<? 
   if ($flag_exist==1) { //la factura que esta cargando ya existe ==> la muestra 
      muestra_detalle($id_fact_exist,$flag_exist); 
   }
   else { 
   	$msg=$parametros['msg'];
    echo "<div align='center'> <font color='blue'> $msg  </font></div>";
    unset($parametros);
    ?>
  <div align="center">
 <br> 
  <table cellspacing=0 cellpadding=3 bgcolor=<? echo $bgcolor2; ?>>
      <tr> 
        <td colspan="2" align="center"><strong> Nueva Factura </strong> <br> </tr>
      <tr> 
        <td align="right">Nº Factura:</td>
        <td><input name="nro_fact" type="text" value='<?  if ($error) echo $_POST['nro_fact'];?>'></td>
      </tr>
      <tr> 
        <td align="right">Tipo Factura</td>
        <td> <select name="tipo_fact">
            <option value='A' <? if (error && $_POST['id_fact']=='A') echo 'selected'; elseif(!$error) echo 'selected' ?>>A</option>
            <option value='B' <? if (error && $_POST['id_fact']=='B') echo 'selected'?>>B</option>
            <option value='C' <? if (error && $_POST['id_fact']=='C') echo 'selected'?>>C</option>
            <option value='E' <? if (error && $_POST['id_fact']=='E') echo 'selected'?>>E</option>
            <!-- son los monotributos -->
            <option value='M' <? if (error && $_POST['id_fact']=='M') echo 'selected'?>>M</option>
            <!-- son los responsables inscriptos que el afip define que no puede hacer facturas a -->
            <option value='Pol' <? if ($res->fields['tipo_fact']=='Pol') echo 'selected'?>>Pol</option>
           <!-- <option value='Tik/FA' <?// if ($res->fields['tipo_fact']=='Tik/FA') echo 'selected'?>>Tik/FA</option>-->
            <option value='Ext' <? if ($res->fields['tipo_fact']=='Ext') echo 'selected'?>>Ext</option>
            <option value='ND' <? if ($res->fields['tipo_fact']=='ND') echo 'selected'?>>ND</option>
            <option value='NC' <? if ($res->fields['tipo_fact']=='NC') echo 'selected'?>>NC</option>
          </select> </td>
      </tr>
      <tr> 
        <td align="right">Fecha de Emisi&oacute;n:</td>
        <td><input name="fecha_emision" type="text" value='<? echo $FecEmision ?>' > 
          <?php echo link_calendario("fecha_emision"); ?></td>
      </tr>
      <tr> 
        <td align="center" colspan="2"> <table bgcolor="#cccccc" id="tabla">
            <tr bgcolor="#CCCCCC"> 
              <td height="33" colspan="3" align="center">Monto en pesos 
                <input name="moneda" type="radio" value="1" checked onClick="mostrar_dolar(0);limpiar(1)">
                Monto en dolares 
              <input type="radio" name="moneda" value="2" <? if ($parametros['fact'] && $res->fields['moneda'] == 2) echo 'checked'; elseif ( !$parametros['fact'] && $_POST['moneda']==2) echo 'checked';?> onClick="mostrar_dolar(1);limpiar(1)"></td>
            </tr>
            <tr bgcolor="#CCCCCC"> 
              <td height="30"  align="center">&nbsp;</td>
              <td><strong>Monto Total &nbsp;$ </strong></td>
              <td align="center"><input name="monto" type="text" id="monto" onChange="limpiar(1);" value='<?  if ($error) echo $_POST['monto'];?>'></td>
            </tr>
            <tr> 
              <td><input name="impuesto" type="checkbox"  <? if ($_POST['impuesto']) echo 'checked'?>  onClick="limpiar(2)" ></td>
              <td>Impuestos Internos</td>
              <td><input type="text" name="imp_interno" value='<?  if ($error) echo $_POST['imp_interno'];?>' onChange="limpiar(0)" <? if(!$error) echo 'disabled' ?> ></td>
            </tr>
            <tr> 
              <td><input name="percepcion_iva" type="checkbox" <? if ($res->fields['percepcion_iva']!="" && formato_money($res->fields['percepcion_iva']) != formato_money(0.00)) echo 'checked'?> onClick="limpiar(3)"></td>
              <td>Percepción I.V.A.</td>
              <td><input type="text" name="percepcion_iva_text" value='<? if (formato_money($res->fields['percepcion_iva']) != formato_money(0.00)) echo number_format($res->fields['percepcion_iva'],2,".","") ?>'  <? if ($res->fields['percepcion_iva'] =="" || formato_money($res->fields['percepcion_iva']) == formato_money(0.00) ) echo 'disabled' ?> onChange="limpiar(0)" ></td>
            </tr>
<?
//para evitar errores en la consulta
if ($id_fact=="")	$id_fact=-1;

//selecciono todos los ingresos brutos de las distintas provincias
//selecciono todos los ingresos brutos de las distintas provincias
$q ="select id_ib,id_distrito,monto_ib,nombre from percepciones_ib ";
$q.="join distrito using(id_distrito) where id_factura=$id_fact order by nombre";

$res_ib=sql($q)or fin_pagina();
$disable_ib=$res_ib->recordcount()==0;

?>
            <tr align="center">
             <td>
              &nbsp;<input name="percepcion_ingb" type="checkbox" <? if ($res_ib->recordcount()) echo "checked"?> onClick="limpiar(4);fnSetMontoIB();">
             </td>
             <td colspan="3" class="border"> 
              <table border="1">
               <tr>
                <td id=ma>
                 Percepción Ingresos Brutos <input type="button" name="bnuevaprov" <? if ($disable_ib) echo "disabled" ?> title="Agregar una nueva provincia para IB" value="+" onclick="fnAddProv()">
                </td>
               </tr>
               <tr>
                <td align="center">
                <table id=tabla_percepciones width="100%" align="center" border="0">
                <tr><td align="center" width="30%">Monto</td><td align="center">Provincia</td></tr>
<?
$i=0;
$total_ib=0;
$select_distrito=new HtmlOptionList("dist_ib_$i");
$select_distrito->add_event("onkeypress","buscar_op(this)");
$select_distrito->add_event("onblur","borrar_buffer()");
$select_distrito->add_event("onclick","borrar_buffer()");
$select_distrito->add_option("Seleccione","-1");
$query="select nombre,id_distrito from distrito order by nombre";
$distritos=sql($query,"<br>Error al traer los distritos") or fin_pagina();
$select_distrito->optionsFromResulset($distritos,array("text"=>"nombre","value"=>"id_distrito"));
$select_distrito->disabled=$disable_ib;
$res_ib->movefirst();
  do //imprimo una fila de percepcionIB
  {
?>
               <tr>
               	<input type="hidden" name="id_ib<?=$i?>" value="<?=$res_ib->fields['id_ib']?>">
                 <td><input type="text" name="monto_ib<?=$i?>" <? if ($disable_ib) echo "disabled" ?> size="8" value="<?=number_format($res_ib->fields['monto_ib'],2,".","")?>" onfocus="this.select()" onChange="fnSetMontoIB();limpiar(0)" ></td>
                 <td><? $select_distrito->name="dist_ib$i"; $i++; $select_distrito->setSelected($res_ib->fields['id_distrito']); echo $select_distrito->toBrowser();?></td>
               </tr>
<? $total_ib+=$res_ib->fields['monto_ib']==""?0:$res_ib->fields['monto_ib'];
	$res_ib->movenext(); 
  }while (!$res_ib->EOF);   ?>           
               <input type="hidden" name="monto_percepcion_ingb" value='<?=number_format($total_ib,2,".","")?>'>
               </table>
                </td>
               </tr>
              </table>  
             </td> 
            </tr>
             <tr> 
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr> 
              <td><input type="checkbox" name="iva1" <? if ($_POST['iva1']) echo 'checked'?> onClick="calcula_iva(1,this.checked)"></td>
              <td align="center">Iva 10.5 % &nbsp;&nbsp;</td>
              <td><input type="text" name="valor_iva1" value='<?  if ($error) echo $_POST['valor_iva1'];?>' <? if(!$error) echo 'disabled' ?> ></td>
            </tr>
            <tr> 
              <td><input type="checkbox" name="iva2" <? if ($_POST['iva2']) echo 'checked'?> onClick="calcula_iva(2,this.checked)"></td>
              <td align="center">Iva 21 %</td>
              <td><input type="text" name="valor_iva2" value='<?  if ($error) echo $_POST['valor_iva2'];?>' <? if(!$error) echo 'disabled' ?>></td>
            </tr>
            <tr> 
              <td><input type="checkbox" name="iva3"  <? if ($_POST['iva3']) echo 'checked'?>  onClick="calcula_iva(3,this.checked)"></td>
              <td align="center">Iva 27 %</td>
              <td><input type="text" name="valor_iva3" value='<?  if ($error) echo $_POST['valor_iva3'];?>' <? if(!$error) echo 'disabled' ?>></td>
            </tr>
            <tr> 
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr> 
              <td>Neto</td>
              <td colspan="2" align="center"><input name="neto" type="text" value='<?  if ($error) echo $_POST['neto'];?>' onFocus="calcular_neto();"></td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td colspan="2"><table>
            <tr> 
              <td>Comentarios:</td>
              <td><textarea name="comentario" cols="50" rows="3"><? if ($error) echo $_POST['comentario'] ?></textarea></td>
            </tr>
          </table></td>
      </tr>
      <tr>
        <td colspan="2"><table>
            <tr> 
              <td>Guardada en:</td>
              <td><textarea name="guardar_en" cols="50" rows="3"><? if ($error) echo $_POST['guardar_en'] ?></textarea></td>
            </tr>
          </table></tr>
      <tr> 
        <td colspan="2"> <table>
            <tr>
              <td nowrap> Proveedor:</td>
              <? $link = encode_link("../general/cargar_proveedores.php",array("pagina_viene"=>"../factura_proveedores/fact_prov_subir.php","onclick_cargar"=>"window.opener.cargar_proveedor();window.close();","onclick_salir"=>"window.close();"));?>
			  <td align="left">&nbsp;&nbsp;<span id="span_nbreprov"></span>&nbsp;&nbsp;
			  <input type="button" name="carga_prov" value="Cargar Proveedor" onclick="ventana_prov = window.open('<?=$link;?>','','')">
			  <input type="hidden" name="id_proveedor" value="">
             </td>
            </tr>
            <tr> 
              <td colspan="2"> <table align="center">
                  <tr> 
                    <td width="101">CUIT / CUIL:</td>
                    <td width="144"><input name="cuit" type="text" value='<?  if ($error) echo $_POST['cuit'];?>'></td>
                    <td width="62"><input name="act_cuit" type="checkbox" <? if ($_POST['act_cuit']) echo 'checked'?> title="si chequea esta casilla se actualiza el cuit del proveedor">
                      (*) </td>
                  </tr>
                  <tr> 
                    <td>Nombre Fantas&iacute;a:</td>
                    <td><input name="fantasia" type="text" value='<?  if ($error) echo $_POST['fantasia'];?>'></td>
                    <td><input name="act_fantasia" type="checkbox"  <? if ($_POST['act_fantasia']) echo 'checked'?> title="si chequea esta casilla se actualiza el nombre del proveedor">
                      (*) </td>
                  </tr>
                  <tr>
                    <td colspan="3"><br>
                      (*) Si la/s casilla/s de verificaci&oacute;n est&aacute;n 
                      chequeadas se actualiza <br>
                      el cuit y/o nombre de fantas&iacute;a en los datos del proveedor 
                    </td>
                  </tr>
                </table></td>
            </tr>
          </table></td>
      </tr>
    </table>
<br>
 <table>
  <tr>
   <!-- return control_datos();-->
	<td><input name="guardar" type="submit" value="Guardar" <?=$permiso?> onClick="return control_datos(); "></td>
	 <? 
	 if ($parametros['nro_orden']) {?>
	   <td><input name="volver" type="button" value="Cancelar" Onclick="window.close();" title="Guarda la factura y carga su id para asociarlo a la orden" ></td>
	    <? } else {?>
	       <td><input name="volver" type="button" value="Volver" Onclick="location.href='fact_prov_listar.php'"></td>
			<? }?>
  </tr>
 </table>

  </div>
<? } 
} ?>
		<input type="hidden" name="hcotizacion_dolar" value="<?= number_format($res->fields['cotizacion_dolar'],3,".","") ?>">
		<input type="hidden" name="hmonto_dolar" value="<?=number_format($res->fields['monto_dolar'],3,".","")?>">
<!--		<input type="hidden" name="hmonto" value="<?//=$res->fields['monto']?>"> -->
<script>

<? if ($res->fields['moneda'] == 2 && ($_POST['guardar']=='Guardar' || $_POST['Modificar']=='Modificar') ) 
	{ 
		//esto es para que se puedan editar los valores viejos (el else)
		if ($res->fields['monto_dolar']!=0) 		
			echo "mostrar_dolar(1)";
		else 
			echo "document.all.monto.value='".number_format($res->fields['monto'],3,".","")."'";
	}
?>
</script>
</form>
</body>
</html>