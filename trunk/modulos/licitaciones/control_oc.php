<?
/*
$Author: cesar $
$Revision: 1.6 $
$Date: 2005/04/20 19:31:07 $
*/

require_once("../../config.php");
echo $html_header;

$sql_check="Select id_renglon,renglones_oc.cantidad from renglon join renglones_oc using (id_renglon) where id_licitacion = ".$_GET["id_licitacion"];
$result_check=sql($sql_check,"Error en '$sql_check'") or fin_pagina();

?>
<script>
reng_id_check = new Array(<?=$result_check->RecordCount();?>);
reng_cant_check = new Array(<?=$result_check->RecordCount();?>);

//cargo el resultado de la consulta en js
<?for($i=0;$i<$result_check->RecordCount();$i++) {
	?>
	reng_id_check[<?=$i?>] = <?=$result_check->fields["id_renglon"];?>;
	reng_cant_check[<?=$i?>] = <?=$result_check->fields["cantidad"];?>;
	<?
	$result_check->MoveNext();	
}?>


var i,j;
var id_reng,cant;
var coincide = 0;
//var result_check = new Array(reng_id_check.length);
	//document.writeln(coincide);

//inicializo
//for(i=0;i<reng_id_check.length;i++){
//	result_check[i]=0;
//}

//comparo si hay coincidencias
for (i=0;i<window.opener.document.all.items.value;i++){
    id_reng = eval("window.opener.document.all.id_renglon_"+i);
    cant = eval("window.opener.document.all.cant_"+i);
    if (typeof(id_reng)!='undefined' && typeof(cant)!='undefined'){
		//alert(id_reng.value);
    	for(j=0;j<reng_id_check.length;j++){
    		if (reng_id_check[j]==id_reng.value && reng_cant_check[j]==cant.value){
    			//result_check[j]=1;
    			coincide = 1;
    		}
    	}
	//document.writeln(id_reng.value+' - '+cant.value+'<br>');
	}
}//del for
//	document.writeln(coincide);

//muestro las coincidecias
var text='';
text+='<table width="60%" align="center" class="bordes">\n';
text+='<tr bgcolor="#FF8080"><td style="font-size:12pt; font-weight:bold;">\n';
text+='Se han encontrado coincidencias en renglones con ordenes ya subidas. O sea, el sistema sospecha que usted está subiendo la MISMA Orden de Compra dos(2) veces. <br>¿Está seguro que desea continuar de todas formas?\n';
text+='</td></tr></table>\n';
/*
text+='<center><div style="overflow:auto; height:100px; width:60%;" class="bordes">\n';
text+='<table width="100%">\n';
text+='<tr><td>\n';
*/
if (coincide) document.writeln(text);
/*
for(i=0;i<result_check.length;i++){
	if (result_check[i]==1)
		document.writeln('<tr><td>coincide el '+reng_titulo_check[i]+'</td></tr>');
}
document.writeln('</table></div></center>\n');
*/

function control_monto(){
  if (document.all.monto.value == window.opener.document.form1.total.value ){
     window.opener.document.form1.files_add.value="Aceptar";
	 window.opener.document.form1.control_ya_subido.value = coincide;
     window.opener.document.form1.submit();
     window.close();
  }
  else
    alert("El monto ingresado no coincide con el de la orden de compra");
}

</script>



<!--<form name='form1' action="licitaciones_view.php" method="post">-->
<table align="center" width="60%" bgcolor=<?=$bgcolor_out?> class="bordes">
  <tr> <td colspan="2" align="center"> INGRESE EL MONTO TOTAL DE LA ORDEN DE COMPRA </td> </tr>
  <tr> <td colspan="2" align="center"> $<input type="text" name='monto' onkeypress="return filtrar_teclas(event,'1234567890.');"> </td> </tr>
  <tr> <td align="center"><input type='button' name="aceptar" value='Aceptar' onclick="control_monto();" ></td>
       <td align="center"><input type='button' name="cancelar" value='Cancelar' onclick="window.close();" ></td></tr>
</table>



<!-- </form> -->
</body>
