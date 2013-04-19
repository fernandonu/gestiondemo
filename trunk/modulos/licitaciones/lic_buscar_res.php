<?
/*
Author: GACZ

MODIFICADA POR
$Author: marcelo $
$Revision: 1.16 $
$Date: 2004/10/22 19:12:40 $
*/

require_once("../../config.php");
$query_comp="SELECT id_competidor,nombre from competidores order by nombre";
$datos_comp=$db->Execute($query_comp) or die($db->ErrorMsg()."<br>".$query_comp);
$query_estados="SELECT id_estado,nombre from estado order by nombre";
$datos_estados=$db->Execute($query_estados) or die($db->ErrorMsg()."<br>".$query_estados);
$query_monedas="SELECT id_moneda, nombre FROM moneda order by nombre";
$datos_monedas=$db->Execute($query_monedas) or die($db->ErrorMsg()."<br>".$query_monedas);
$query_distritos="SELECT id_distrito, nombre FROM distrito order by nombre";
$datos_distritos=$db->Execute($query_distritos) or die($db->ErrorMsg()."<br>".$query_distritos);
$query_entidades="SELECT id_entidad, nombre FROM entidad order by nombre";
$datos_entidades=$db->Execute($query_entidades) or die($db->ErrorMsg()."<br>".$query_entidades);
$query_tipo_entidades="SELECT id_tipo_entidad, nombre FROM tipo_entidad order by nombre";
$datos_tipo_entidades=$db->Execute($query_tipo_entidades) or die($db->ErrorMsg()."<br>".$query_tipo_entidades);
?>
<script language='javascript' src='<?=LIB_DIR.'/popcalendar.js'?>'></script>
<SCRIPT>
/*function habilitar(estado)
{
 document.all.keyword.disabled=estado;
 document.all.select_buscar.disabled=estado;
}*/
function chk_form()
{
var filtrar=0;

if (document.all.chk_busqueda_general.checked)
 	{
 		filtrar++;
 		if (document.all.keyword.value==""){
 			alert('Debes especificar algún valor en el campo Buscar...');
 			return false;
 		}
 	}

if (document.all.chk_precios.checked)
 {
 	filtrar++;
   if (document.all.precio_menor.value=="" ||
   	 document.all.precio_mayor.value=="" )
 	{
		alert ('Debe ingresar montos validos');
		return false;
 	}

 }
 if (document.all.chk_fechas.checked)
 {
 	filtrar++;
   if (document.all.select_fechas.options[document.all.select_fechas.selectedIndex].value==0)
 	{
		alert ('Debe seleccionar el tipo de fecha');
		return false;
 	}
 	if (document.all.fecha_menor.value=='' || document.all.fecha_menor.mayor=='')
 	{
		alert ('Debe seleccionar las dos fechas');
		return false;
 	}
 }
 if (document.all.chk_estado.checked)
 {
 	 filtrar++;
	 if(document.all.select_estado.options[document.all.select_estado.selectedIndex].value==-1)
	 {
	  	alert ('Por favor elija un estado válido');
	  	return false;
	 }
 }
 if (document.all.chk_id_lic.checked)
 {
 	filtrar++;
   if (document.all.id_menor.value=="" || 
	   document.all.id_mayor.value=="" ||
	   parseInt(document.all.id_mayor.value) < parseInt(document.all.id_menor.value))
 	{
		alert ('Debe ingresar ID de licitaciones válidos');
		return false;
 	}

 }

 if (document.all.chk_distrito.checked)
 {
 	 filtrar++;
	 if(document.all.select_distrito.options[document.all.select_distrito.selectedIndex].value==-1)
	 {
	  	alert ('Por favor elija un distrito válido');
	  	return false;
	 }
 }
 if (document.all.chk_entidad.checked)
 {
 	 filtrar++;
	 if(document.all.select_entidad.options[document.all.select_entidad.selectedIndex].value==-1)
	 {
	  	alert ('Por favor elija una entidad válida');
	  	return false;
	 }
 }
 if (document.all.chk_tipo_entidad.checked)
 {
 	 filtrar++;
	 if(document.all.select_tipo_entidad.options[document.all.select_tipo_entidad.selectedIndex].value==-1)
	 {
	  	alert ('Por favor elija un tipo de entidad válido');
	  	return false;
	 }
 }
 if (document.all.chk_competidor.checked)
 {
 	filtrar++;
 	if(document.all.select_competidor.options[document.all.select_competidor.selectedIndex].value==0)
	 {
	  	alert ('Por favor elija un competidor');
	  	return false;
	 }
 }
 if (document.all.chk_competidores.checked)
 {
 	 filtrar++;
    if(document.all.select_comp1.options[document.all.select_comp1.selectedIndex].value==0
	 || document.all.select_comp2.options[document.all.select_comp2.selectedIndex].value==0 )
	 {
	  	alert ('Por favor elija 2 competidores');
	  	return false;
	 }
 }
 
 if (document.all.tipo_c_renglon.checked)
 {
 	 filtrar++;
 }

 if (document.all.cantidad_renglon.checked)
 {
 	 filtrar++;
    if (document.all.cantidad_r_min.value=="" || 
	   document.all.cantidad_r_max.value=="" ||
	   parseInt(document.all.cantidad_r_max.value) < parseInt(document.all.cantidad_r_min.value))
 	{
		alert ('Debe ingresar cantidades de items en renglones validas');
		return false;
 	}
}
  
 if (filtrar==0)
 {
 	alert ('Debes utilizar al menos un criterio para buscar...');
 	return false;
 }
 return true;

}

function change_comp(nombre_chk)
{
 if (!document.all.chk_competidor.checked &&
 	  !document.all.chk_competidores.checked)
 {
 	document.all.chk_competidor.checked=false;
 	document.all.chk_ganador.disabled=false;
 	document.all.chk_ganador_vs.disabled=false;
 	document.all.chk_competidores.checked=false;
   document.all.select_competidor.disabled=false;
 	document.all.select_comp1.disabled=false;
 	document.all.select_comp2.disabled=false;
 }
 else
 if (nombre_chk=='chk_competidor')
 {
 	document.all.select_comp1.disabled=true;
 	document.all.select_comp2.disabled=true;
 	document.all.select_competidor.disabled=false;
 	document.all.chk_ganador.disabled=false;
 	document.all.chk_competidores.checked=false;
 	document.all.chk_ganador_vs.checked=false;
 	document.all.chk_ganador_vs.disabled=true;
 }
 else
 {
 	document.all.chk_ganador.checked=false;
 	document.all.chk_ganador.disabled=true;
 	document.all.select_competidor.disabled=true;
 	document.all.select_comp1.disabled=false;
 	document.all.select_comp2.disabled=false;
 	document.all.chk_competidor.checked=false;
 	document.all.chk_ganador_vs.checked=false;
 	document.all.chk_ganador_vs.disabled=false;
 }
}
/*
function habilitar_filtro(){
if ((document.all.chk_precios.checked)||(document.all.chk_fechas.checked)||
	 (document.all.chk_estado.checked)||(document.all.chk_competidores.checked)||
	 (document.all.chk_distrito.checked)||(document.all.chk_entidad.checked)||
	 (document.all.chk_id_lic.checked)||(document.all.chk_tipo_entidad.checked)||
	 (document.all.chk_competidor.checked) || (document.all.tipo_c_renglon.checked)
	 || (document.all.cantidad_renglon.checked)){

document.all.chk_solo_filtros.checked=true;
document.all.keyword.disabled=1;
document.all.select_buscar.disabled=1;

}
else
{
document.all.chk_solo_filtros.checked=false;
document.all.keyword.disabled=0;
document.all.select_buscar.disabled=0;

}
} //fin de habilitar filtro*/
</SCRIPT>

<form name="form_buscar" method="post" action="<?=$submit_page ?>" target="_new" onsubmit="return (chk_form())">
  <table class="bordes" width="88%" align="center" cellpadding=2 bgcolor=<?=$bgcolor_out?>>
    <tr valign="top">
      <td><strong>Búsqueda General</strong></td>
      <td><input name="chk_busqueda_general" type="checkbox" id="chk_busqueda_general" value="1"></td>
      <td height="29"><strong>Buscar </strong>
        <input type="text" name="keyword" value="<?=($keyword!=-1)?$keyword:''?>" onkeypress="document.all.chk_busqueda_general.checked=1;">
        en
     <!--
        <select name="select_buscar" id="select_buscar">
          <option value=1 selected>ID licitacion</option>
          <option value=3>Numero licitacion</option>
	       <option value=9>Titulo Renglon</option>
	       <option value=8>Entidad</option>
          <option value=7>Distrito</option>
          <option value=4>Comentarios</option>
          <option value=2>Todos los campos</option>
        </select>
      -->
      <select name="select_buscar" id="select_buscar" >
          <option value=1 selected>ID licitacion</option>
          <option value=4>Comentarios</option>
          <option value=5 title="Observaciones del competidor">Observaciones</option>
          <option value=7>Distrito</option>
          <option value=8>Entidad</option>
          <option value=3>Numero licitacion</option>
          <option value=9>Titulo Renglon</option>
          <option value=2>Todos los campos</option>
        </select>
      </td>
      <td width="7%" height="29">&nbsp;</td>
    </tr>
 <!--   <tr>
      <td><strong>Filtrar por</strong></td>
      <td><input name="chk_solo_filtros" type="checkbox" id="chk_solo_filtros" value="1" onclick="habilitar(this.checked)"></td>
      <td>USAR FILTROS COMO BUSQUEDA</td>
      <td>&nbsp;</td>
    </tr>
    -->
    <tr>
      <td height="22">Precios</td>
      <td><input name="chk_precios" type="checkbox" id="chk_precios" value="1"></td>
      <td><select name="select_precios" id="select_precios" onchange="document.all.chk_precios.checked=1;">
          <option value="monto_estimado">Monto Estimado</option>
          <option value="monto_ofertado" selected>Monto Ofertado</option>
          <option value="monto_ganado">Monto Ganado</option>
          <option value="total_ofertado">Total Ofertado</option>
        </select>
        entre
        <input name="precio_menor" type="text" id="precio_menor" size="8" onfocus="document.all.chk_precios.checked=1;" onkeypress="return filtrar_teclas(event,'0123456789.')">
        <
        <input name="precio_mayor" type="text" id="precio_mayor" size="8" onfocus="document.all.chk_precios.checked=1;" onkeypress="return filtrar_teclas(event,'0123456789.')">
        moneda
        <select name="select_moneda" id="select_moneda">
<? while (!$datos_monedas->EOF)
	{
?>
       <option value=<?=$datos_monedas->fields['id_moneda']?>><?=$datos_monedas->fields['nombre']?></option>
<?
		 $datos_monedas->MoveNext();
	}
?>
        </select></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Fechas</td>
      <td><input name="chk_fechas" type="checkbox" id="chk_fechas" value="1"></td>
      <td><select name="select_fechas" id="select_fechas" onchange="document.all.chk_fechas.checked=1;">
          <option selected value=0>Seleccione el tipo de fecha</option>
      	 <option value="fecha_apertura">fecha de apertura</option>
          <option value="fecha_entrega">fecha de entrega</option>
        </select>
        entre
        <input name="fecha_menor" type="text" id="fecha_menor" size="10" readonly>
        <img src=../../imagenes/cal.gif border=0 align=center style='cursor:hand;' alt='Haga click aqui para
seleccionar la fecha'  onClick="javascript:popUpCalendar(fecha_menor, fecha_menor, 'dd/mm/yyyy');">
        y
        <input name="fecha_mayor" type="text" id="fecha_mayor" value="<?=date('d/m/Y') ?>" size="10" readonly>
        <img src=../../imagenes/cal.gif border=0 align=center style='cursor:hand;' alt='Haga click aqui para
seleccionar la fecha'  onClick="javascript:popUpCalendar(fecha_mayor, fecha_mayor, 'dd/mm/yyyy');"></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Estado</td>
      <td><input name="chk_estado" type="checkbox" id="chk_estados" value="1"></td>
      <td><select name="select_estado" id="select_estado" onchange="document.all.chk_estados.checked=1;">
      <option selected value=-1>Seleccione un estado</option>
<? while (!$datos_estados->EOF)
	{
?>
       <option value=<?=$datos_estados->fields['id_estado']?>><?=$datos_estados->fields['nombre']?></option>
<?
		 $datos_estados->MoveNext();
	}
?>
          </select></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td width="16%">Competidor</td>
      <td width="4%"><input name="chk_competidor" type="checkbox" id="chk_competidor" value="1" onclick="change_comp(this.name);">
      </td>
      <td width="73%"> <select name="select_competidor" id="select_competidor" onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange="document.all.chk_competidor.checked=1;">
          <option selected>Seleccione el competidor</option>
          <?
while (!$datos_comp->EOF)
{
?>
          <option value="<?=$datos_comp->fields['id_competidor']?>">
          <?=$datos_comp->fields['nombre'] ?>
          </option>
          <?
$datos_comp->MoveNext();
}
$datos_comp->MoveFirst();
?>
        </select>
        <input name="chk_ganador" type="checkbox" id="chk_ganador" value="1">
        como adjudicado</td>
      <td width="7%">&nbsp;</td>
    </tr>
    <tr>
      <td width="16%">Competidores</td>
      <td width="4%"><input name="chk_competidores" type="checkbox" id="chk_competidores" value="1" onclick="change_comp(this.name);">
      </td>
      <td width="73%"> <select name="select_comp1" id="select_comp1" onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange="document.all.chk_competidores.checked=1;">
          <option selected value=0>Seleccione el competidor</option>
          <?
while (!$datos_comp->EOF)
{
?>
          <option value="<?=$datos_comp->fields['id_competidor']?>"> 
          <?=$datos_comp->fields['nombre'] ?>
          </option>
          <?	
$datos_comp->MoveNext();
}
$datos_comp->MoveFirst();
?>
        </select>
        <input name="chk_ganador_vs" type="checkbox" id="chk_ganador_vs" value="1"> como ganador
        VS 
        <select name="select_comp2" id="select_comp2" onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange="document.all.chk_competidores.checked=1;">
          <option selected value=0>Seleccione el competidor</option>
          <?

while (!$datos_comp->EOF)
{
?>
          <option value="<?=$datos_comp->fields['id_competidor']?>"> 
          <?=$datos_comp->fields['nombre'] ?>
          </option>
          <?	
$datos_comp->MoveNext();
}
$datos_comp->MoveFirst();
?>
        </select> </td>
      <td width="7%">&nbsp;</td>
    </tr>
    <tr>
    <td>Tipo de Renglon</td>
    <td><input type="checkbox" name="tipo_c_renglon" value="t" ></td>
   <td><select name="tipo_renglon" onchange="document.all.tipo_c_renglon.checked=1;">
       <option>PC</option>
       <option>Servidor</option>
       <option>Impresora</option>
       <option>Otro</option>
       </select>
   </td>
   </tr>
    <tr>
    <td>Cantidad en el Renglon</td>
    <td><input type="checkbox" name="cantidad_renglon" value="1"></td>
   <td>
   entre
   <INPUT type="text" name="cantidad_r_min" size="8" id="cantidad_r_min" onfocus="document.all.cantidad_renglon.checked=1;" onkeypress="return filtrar_teclas(event,'0123456789')">
   <
   <INPUT type="text" name="cantidad_r_max" size="8" id="cantidad_r_max" onfocus="document.all.cantidad_renglon.checked=1;" onkeypress="return filtrar_teclas(event,'0123456789')">
   </td>
   </tr>
   <tr>
      <td height="22">ID de Licitación</td>
      <td><input name="chk_id_lic" type="checkbox" id="chk_id_lic" value="1"></td>
      <td>entre
        <input name="id_menor" type="text" id="id_menor" size="8" onfocus="document.all.chk_id_lic.checked=1;" onkeypress="return filtrar_teclas(event,'0123456789')">
        <
        <input name="id_mayor" type="text" id="id_mayor" size="8" onfocus="document.all.chk_id_lic.checked=1;" onkeypress="return filtrar_teclas(event,'0123456789')">
		</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Distrito</td>
      <td><input name="chk_distrito" type="checkbox" id="chk_distrito" value="1"></td>
      <td><select name="select_distrito" id="select_distrito" onchange="document.all.chk_distrito.checked=1;">
      <option selected value=-1>Seleccione un distrito</option>
<? while (!$datos_distritos->EOF)
	{
?>
       <option value=<?=$datos_distritos->fields['id_distrito']?>><?=$datos_distritos->fields['nombre']?></option>
<?
		 $datos_distritos->MoveNext();
	}
?>
          </select></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Entidad</td>
      <td><input name="chk_entidad" type="checkbox" id="chk_entidad" value="1"></td>
      <td><select name="select_entidad" id="select_entidad" onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange="document.all.chk_entidad.checked=1;">
      <option selected value=-1>Seleccione una entidad</option>
<? while (!$datos_entidades->EOF)
	{
?>
       <option value=<?=$datos_entidades->fields['id_entidad']?>><?=$datos_entidades->fields['nombre']?></option>
<?
		 $datos_entidades->MoveNext();
	}
?>
          </select></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Tipo de Entidad</td>
      <td><input name="chk_tipo_entidad" type="checkbox" id="chk_tipo_entidad" value="1"></td>
      <td><select name="select_tipo_entidad" id="select_tipo_entidad" onchange="document.all.chk_tipo_entidad.checked=1;">
      <option selected value=-1>Seleccione un tipo de entidad</option>
<? while (!$datos_tipo_entidades->EOF)
	{
?>
       <option value=<?=$datos_tipo_entidades->fields['id_tipo_entidad']?>><?=$datos_tipo_entidades->fields['nombre']?></option>
<?
		 $datos_tipo_entidades->MoveNext();
	}
?>
          </select></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><b>Ordenar por</b></td>
      <td><input name="chk_ordenar" type="checkbox" id="chk_ordenar" value="1"></td>
      <td><select name="select_ordenar" id="select_ordenar" onchange="document.all.chk_ordenar.checked=1;">
      <option value="licitacion.fecha_apertura">Fecha de apertura</option>
      <option value="tipo_entidad.nombre">Tipo de entidad</option>
      <option value="distrito.nombre">Distrito</option>
      <option value="entidad.nombre">Entidad</option>
      </select>
	  orden
	  <select name="select_ordenar_dir" id="select_ordenar_dir" onchange="document.all.chk_ordenar.checked=1;">
      <option value="ASC">Ascendente</option>
      <option value="DESC">Descendente</option>
      </select>
	  </td>
      <td>&nbsp;</td>
    </tr>
   <tr> 
      <td width="16%" height="31">&nbsp;</td>
      <td width="2%">&nbsp;</td>
		<td width='72%' height='40'><input type='submit' name='buscar' value='Buscar'></td>
      <td width="10%">&nbsp;</td>
    </tr>
  </table>
</form>
<SCRIPT>
document.all.keyword.focus();
</SCRIPT>