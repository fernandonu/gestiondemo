<?
/*
$Author: mari $
$Revision: 1.1 $
$Date: 2006/01/13 22:05:01 $
*/
require_once("../../config.php");
echo $html_header;
$link=encode_link('../modulo_clientes/nuevo_cliente.php',array('pagina'=>'altas_cheque_dif_atadas'));
echo "<script language='javascript' src='../../lib/popcalendar.js'> </script>\n";
echo "<script language='javascript' src='../../lib/fns.js'> </script>\n";
?>
<input type='hidden' name='num_fila' value=''>
<script src="<?=$html_root."/lib/wddx.js"?>" ></script>
<script>

var primera_vez = 1;
var select_banco = new Array();
select_banco['nombre'] = new Array();
select_banco['id_banco'] = new Array();

var select_pertenece = new Array();
select_pertenece['nombre'] = new Array();
select_pertenece['id_empresa_cheque'] = new Array();

<?
//traigo bancos de cheques diferidos
$sql="select id_banco,nombre from bancos_cheques_dif order by nombre";
$result_banco=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$i=0;
while(!$result_banco->EOF)
{
?>

select_banco['nombre'][<?=$i;?>] ='<?=$result_banco->fields['nombre']?>';
select_banco['id_banco'][<?=$i;?>] ='<?=$result_banco->fields['id_banco']?>';

<?
$result_banco->MoveNext();
$i++;
}


//traigo emrpesas 
$sql="select nombre,id_empresa_cheque from empresas_cheques order by id_empresa_cheque";
$result_empresas = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);

$i=0;
while(!$result_empresas->EOF)
{
?>
select_pertenece['nombre'][<?=$i;?>] = '<?=$result_empresas->fields['nombre']?>';
select_pertenece['id_empresa_cheque'][<?=$i;?>] = '<?=$result_empresas->fields['id_empresa_cheque']?>';
<?
$result_empresas->MoveNext();
$i++;
}
?>

function control_datos()
{var i=0;
 var error = 0;
 var tabla = document.getElementById("tabla_cheques");
 var cheques_diferidos = eval("window.opener.cheques_diferidos");
 var monto_total=0;
 //controlo todas los datos ingresados en las filas
 while(i<tabla.rows.length)
 {nro_cheque = eval('document.all.nro_cheque_'+i);
  banco = eval('document.all.banco_'+i);
  comentario = eval('document.all.comentario_'+i);
  fecha_vencimiento = eval('document.all.fecha_vencimiento_'+i);
  monto = eval('document.all.monto_'+i);
  pertenece = eval('document.all.pertenece_'+i);
  id_cliente = eval('document.all.id_cliente_'+i);
   if ((nro_cheque.value=="") ||
     (banco.value=="") ||
     (comentario.value=="") ||
     (fecha_vencimiento.value=="") ||
     (monto.value=="") ||
     (id_cliente.value=="") ||
     (pertenece.value=="")
     )
     {error=1;
     }
  i++;
 }
 
 if (error==1)
  alert("Los campos son todos obligatorios");
 else  
 {//lleno el arreglo cheques_diferidos de la ventana padre
  i=0;
  while(i<tabla.rows.length)
   {nro_cheque = eval('document.all.nro_cheque_'+i);
    banco = eval('document.all.banco_'+i);
    comentario = eval('document.all.comentario_'+i);
    ubicacion = eval('document.all.ubicacion_'+i);
    fecha_vencimiento = eval('document.all.fecha_vencimiento_'+i);
    monto = eval('document.all.monto_'+i);
    pertenece = eval('document.all.pertenece_'+i);
    id_cliente = eval('document.all.id_cliente_'+i);
    cl = eval('document.all.cliente_'+i);
    
   	monto.value = monto.value.replace(',','.');
    cheques_diferidos.cheques["monto"][i]=monto.value;
    cheques_diferidos.cheques["nro"][i]=nro_cheque.value;
    cheques_diferidos.cheques["comentario"][i] =comentario.value ;
    cheques_diferidos.cheques["ubicacion"][i] = ubicacion.value;
    cheques_diferidos.cheques["fecha_vencimiento"][i] = fecha_vencimiento.value;
    cheques_diferidos.cheques["banco"][i] = banco.value;
    cheques_diferidos.cheques["pertenece"][i] = pertenece.value;
    cheques_diferidos.cheques["id_cliente"][i] = id_cliente.value;
    cheques_diferidos.cheques["cliente"][i] = cl.value;
    
    monto_total = parseFloat(monto_total) + parseFloat(monto.value);
   	i++;
   }
   
   wddxSerializer = new WddxSerializer();
   MyWDDXPacket = wddxSerializer.serialize(cheques_diferidos);
   window.opener.document.all.cheques_diferidos.value=MyWDDXPacket;
   window.opener.document.all.diferido.value=monto_total.toFixed(2);
   window.opener.document.all.chk_diferido.checked=true;
   window.close(); 	
 } 
}


function llenar_selects(indice_fila)
{
 var i = 0;
 var banco = eval("document.all.banco_"+indice_fila);
 var pertenece = eval("document.all.pertenece_"+indice_fila);
 
 while(i<select_banco['nombre'].length)
 {
  add_option(banco,select_banco['id_banco'][i],select_banco['nombre'][i]);
  i++;	
 }
 
 i=0;
 
 while(i<select_pertenece['nombre'].length)
 {
  add_option(pertenece,select_pertenece['id_empresa_cheque'][i],select_pertenece['nombre'][i]);
  i++;	
 }
 
}


function calcular_cant_cheques()
{
var tabla = document.getElementById("tabla_cheques");
var cheques_diferidos = eval("window.opener.cheques_diferidos");
var select_cant = eval("document.all.select_cant_cheques");


var fila;
//alert(cheques_diferidos);
var i;

//vacio tabla principal
while(tabla.rows.length>=1)
 tabla.deleteRow(0);
  
//Verifico si se cargaron cheques
if (cheques_diferidos.cheques['nro'].length>0)
{i=0;
 cant_filas = select_cant.value;
 
 if (primera_vez==1)
 {
 if (select_cant.value>1)
  cant_filas = select_cant.value;
 else
  cant_filas = cheques_diferidos.cheques['nro'].length;
 }
 else
  cant_filas = select_cant.value;
 while(i<cant_filas)
 {
  fila=tabla.insertRow(tabla.rows.length);
  fila.insertCell(0).innerHTML="<table align='center' width='100%' cellspacing='0' class='bordes'>"+
"<tr>"+
"<td><b>Nro de Cheque</td>"+
"<td><input type='text' name='nro_cheque_"+i+"' value='' size='30'></td>"+
"<td><b>Fecha Vencimiento</td>"+
"<td><input type='text' name='fecha_vencimiento_"+i+"' value='' readonly size='30'>"+
"<img src='<?=$html_root;?>/imagenes/cal.gif' border=0 align=middle style='cursor:hand;' alt='Haga click aqui para\nseleccionar la fecha'  onClick=\"javascript:popUpCalendar(fecha_vencimiento_"+i+",fecha_vencimiento_"+i+",'dd/mm/yyyy');\">"+
"</td>"+
"</tr>"+
"<tr>"+
"<td><b>Banco</td>"+
"<td>"+
"<select name='banco_"+i+"' style='cursor:hand'>"+
"<option value=''>Elija Banco</option>"+
"</select>"+
"</td>"+
"<td><b>Monto</td>"+
"<td><input type='text' name='monto_"+i+"' value='' size='30'></td>"+
"</tr>"+
"<tr>"+
"<td><b>Cliente:</td>"+
"<td colspan='3'>"+
"<input type='button' name='elegir_cliente_"+i+"' value='Cliente'"+
" onclick=\"window.open('<?=$link?>','','width=600, height=650');document.all.num_fila.value="+i+"\"\" >"+
"<input type='text' name='cliente_"+i+"' value='' size='50'style=\"border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;\" readonly>"+
"<input type='hidden' name='id_cliente_"+i+"' value=''>"+
"</td>"+
"</tr>"+
"<tr>"+
"<td><b>Pertenece a:</td>"+
"<td colspan='3'>"+
"<select name='pertenece_"+i+"'>"+
"</select>"+
"</td>"+
"</tr>"+
"<tr>"+
"<td colspan='2'>"+
"<table>"+
"<tr>"+
"<td align='right' valign='top'><b>Comentario</td>"+
"<td align='left' valign='top'><textarea name='comentario_"+i+"' cols='50' rows='3'></textarea>"+
"</tr>"+
"</table>"+
"</td>"+
"<td colspan='2'>"+
"<table>"+
"<tr>"+
"<td align='right' valign='top'><b>Ubicacion</td>"+
"<td align='left' valign='top'><textarea name='ubicacion_"+i+"' cols='50' rows='3'></textarea>"+
"</tr>"+
"</table>"+
"</td>"+
"</tr>"+
"</table>";
llenar_selects(i);
nro_cheque = eval('document.all.nro_cheque_'+i);
banco = eval('document.all.banco_'+i);
comentario = eval('document.all.comentario_'+i);
ubicacion = eval('document.all.ubicacion_'+i);
fecha_vencimiento = eval('document.all.fecha_vencimiento_'+i);
monto = eval('document.all.monto_'+i);
pertenece = eval('document.all.pertenece_'+i);
id_cliente = eval('document.all.id_cliente_'+i);
cl = eval('document.all.cliente_'+i);

if (cheques_diferidos.cheques["monto"][i]!=null)
{
monto.value=cheques_diferidos.cheques["monto"][i];
nro_cheque.value=cheques_diferidos.cheques["nro"][i];
comentario.value=cheques_diferidos.cheques["comentario"][i];
ubicacion.value=cheques_diferidos.cheques["ubicacion"][i];
fecha_vencimiento.value=cheques_diferidos.cheques["fecha_vencimiento"][i];
banco.value=cheques_diferidos.cheques["banco"][i];
pertenece.value=cheques_diferidos.cheques["pertenece"][i];
id_cliente.value=cheques_diferidos.cheques["id_cliente"][i];
cl.value=cheques_diferidos.cheques["cliente"][i];
}
i++;
 }
//le doy valor al select de cantidad
select_cant.options.selectedIndex=i-1;

}
else //no hay cheques cargados, cargo la cantidad especificada
{
 cant_filas = select_cant.value;
  
 i=0;
 while(i<cant_filas)
 {fila=tabla.insertRow(tabla.rows.length);
  fila.insertCell(0).innerHTML="<table align='center' width='100%' cellspacing='0' class='bordes'>"+
"<tr>"+
"<td><b>Nro de Cheque</td>"+
"<td><input type='text' name='nro_cheque_"+i+"' value='' size='30'></td>"+
"<td><b>Fecha Vencimiento</td>"+
"<td><input type='text' name='fecha_vencimiento_"+i+"' value='' readonly size='30'>"+
"<img src='<?=$html_root;?>/imagenes/cal.gif' border=0 align=middle style='cursor:hand;' alt='Haga click aqui para\nseleccionar la fecha'  onClick=\"javascript:popUpCalendar(fecha_vencimiento_"+i+",fecha_vencimiento_"+i+",'dd/mm/yyyy');\">"+
"</td>"+
"</tr>"+
"<tr>"+
"<td><b>Banco</td>"+
"<td>"+
"<select name='banco_"+i+"' style='cursor:hand'>"+
"<option value=''>Elija Banco</option>"+
"</select>"+
"</td>"+
"<td><b>Monto</td>"+
"<td><input type='text' name='monto_"+i+"' value='' size='30'></td>"+
"</tr>"+
"<tr>"+
"<td><b>Cliente:</td>"+
"<td colspan='3'>"+
"<input type='button' name='elegir_cliente_"+i+"' value='Cliente'"+
" onclick=\"window.open('<?=$link?>','','width=600, height=650');document.all.num_fila.value="+i+"\" >"+
"<input type='text' name='cliente_"+i+"' value='' size='50'style=\"border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;\" readonly>"+
"<input type='hidden' name='id_cliente_"+i+"' value=''>"+
"</td>"+
"</tr>"+
"<tr>"+
"<tr>"+
"<td><b>Pertenece a:</td>"+
"<td colspan='3'>"+
"<select name='pertenece_"+i+"'>"+
"</select>"+
"</td>"+
"</tr>"+
"<tr>"+
"<td colspan='2'>"+
"<table>"+
"<tr>"+
"<td align='right' valign='top'><b>Comentario</td>"+
"<td align='left' valign='top'><textarea name='comentario_"+i+"' cols='50' rows='3'></textarea>"+
"</tr>"+
"</table>"+
"</td>"+
"<td colspan='2'>"+
"<table>"+
"<tr>"+
"<td align='right' valign='top'><b>Ubicacion</td>"+
"<td align='left' valign='top'><textarea name='ubicacion_"+i+"' cols='50' rows='3'></textarea>"+
"</tr>"+
"</table>"+
"</td>"+
"</tr>"+
"</table>";
llenar_selects(i);
  i++;
 } 
 select_cant.value = cant_filas;
}
primera_vez=0;
//salga el foco del select
document.all.tabla_cheques.focus();
}


</script>
<font size="4"><b>Ingresos de Cheques Diferidos</b></font><br><br>
<font color="Blue"><b>Cant. Cheques: </font>
<select name="select_cant_cheques" onchange="calcular_cant_cheques();">
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
<option value="6">6</option>
<option value="7">7</option>
<option value="8">8</option>
<option value="9">9</option>
<option value="10">10</option>
</select>
<hr>
<table id="tabla_cheques" align="center" width="95%">
</table>
<br>
<center>
<input type="button" name="aceptar" value="Aceptar" style="cursor:hand" onclick="return control_datos();">&nbsp;
&nbsp;&nbsp;
<input type="button" name="cerrar" value="Cerrar" style="cursor:hand" onclick="window.close()">
</center>
<?
echo $html_footer;
?>
<script>calcular_cant_cheques();</script>