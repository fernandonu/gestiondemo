<? 
/*
Autor: MAC
Fecha: 14/10/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2004/10/20 15:00:55 $
*/

require_once("../../../config.php");

if ($_POST['bnuevo'])
{
	$nuevo_org=$_POST['nuevo_org'];
	$q ="insert into pcpower_organismos (nombre) values ('$nuevo_org')";
	if (sql($q)) // or fin_pagina();
	 $msg="El nuevo organismo se agrego con éxito";
	else 
	 $msg="<font color=red>No se pudo agregar el organismo o ya existe</font>";
}
elseif ($_POST['bguardar'])
{
	$id_org=$_POST['select_org'];
	//reemplazo las comas por el id_organismo+id_entidad+coma
	$entidades=split(",",$_POST['entidades']);
	//borro las asociaciones anteriores
	$q="delete from pcpower_org_entidades where id_org=$id_org;\n";
	//en caso de que quite todas las entidades
	if ($_POST['entidades'])
	{
	//hago todos los inserts en una consulta simple
	//se ejecuta como una transaccion simple
	foreach ($entidades as $id_entidad)
		$q.="insert into pcpower_org_entidades values ($id_org,$id_entidad);";
	}
	if (sql($q)) //or fin_pagina();
	 $msg="Las entidades se cargaron satisfactoriamente";
	else 
	 $msg="<font color=red>No se pudo actualizar</font>";
}

$letra=$parametros['letra'] or $letra='todas';
$q ="select * from pcpower_organismos ";
//filtra por los que empiecen con la letra $letra
if ($letra!="todas")
 $q.="where nombre ilike '$letra%' ";
$q.="order by nombre";
$organismos= sql($q) or fin_pagina();

//recupero todas las entidades
$q ="select id_entidad,e.nombre ||': '|| d.nombre as nombre,id_org ";
$q.="from ";
$q.="pcpower_entidad e left join ";
$q.="pcpower_org_entidades using (id_entidad) left join ";
$q.="pcpower_distrito d using(id_distrito) ";
//$q.="where d.nombre ilike '%santa fe%'";
$q.="order by nombre";
$entidades= sql($q) or fin_pagina();


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Organismos - Entidades</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
.Estilo9 {
	background-color: #660000;
	color: #FFFFFF;
}
-->
</style>
<script src="../../../lib/fns.js"></script>
<script src="../../../lib/funciones.js"></script>
<script>
//variable que contiene todas las entidades y su respectivo organismo asociado
var entidades=new Array();
<? 
$i=0;
//lleno los datos de las entidades
while (!$entidades->EOF)
{
	$id_entidad=$entidades->fields['id_entidad'];
	$nbre=$entidades->fields['nombre'];
	$id_org=$entidades->fields['id_org'] or $id_org=0;
	echo "entidades[$i]=new Array(3);\n";
	echo "entidades[$i]['id_entidad']=$id_entidad;\n";
	echo "entidades[$i]['nombre']=\"$nbre\";\n";
	echo "entidades[$i]['id_org']=$id_org;\n";//para saber si esta asociada la entidad
	$entidades->movenext();
	$i++;
}
echo "entidades.length=$i;\n";

//pq mas abajo se accede a la variable
$entidades->movefirst();
?>

function get_entidades(id_organismo)
{
	var select1=form1.select_ent_org;//entidades del organismo
	var select2=form1.select_ent;//todas las entidaddes restantes
	var j=0;//cantidad de entidades del organismo
	var k=0;//cantidad que pertenecen a algun organismo
	select1.options.length=0;
	select2.options.length=0;
	for ( i=0; i < entidades.length;i++)
	{
		if (entidades[i]['id_org']==id_organismo)
		{
			add_option(select1,entidades[i]['id_entidad'],entidades[i]['nombre']);
			j++;
		}
		else if (entidades[i]['id_org']==0)
			add_option(select2,entidades[i]['id_entidad'],entidades[i]['nombre']);
		else
			k++;

	}
	var r=String(entidades.length - j - k);//cantidad de entidades restantes
	return (j+'_'+r);
}

//se usa para recuperar los ids  de entidad antes de enviar el formulario
//las guarda en un campo oculto llamado entidades
function get_ent_ids()
{
	var a=new Array();
    var largo=document.form1.select_ent_org.length;
    var i=0;
    for(i;i < largo;i++)
    {a[i]=document.form1.select_ent_org.options[i].value;
    }
	document.form1.entidades.value=a;
}


</script>
<?=$html_header ?>
<form name="form1" method="post" action="<?=$_SERVER['SCRIPT_NAME'] ?>">
<input type="hidden" name="entidades">
<table width="100%" cellspacing="1" border="0" align="center">
    <tr id=mo >
      <td valign="middle" align="center"  height="18">Organismos&nbsp;&nbsp;(<?=$organismos->recordcount()?>)&nbsp;&nbsp;
<? if (permisos_check("inicio","org_lista_doc")) { ?>
      <a target="_blank" href="<?=encode_link("pcpower_org_lista_doc.php",array()) ?>"><img border="0" align="middle" alt="Bajar datos de Organismos y Entidades" src="../../../imagenes/word.gif"></a>
<? }?>      
      </td>
	 </tr>
	  <tr>
	   <td align="center">
	    <table border="0" cellspacing="1" >
	     <tr>
	      <td><b><?=$msg ?></b></td>
	     </tr>
	    </table>
       </td>
    </tr>
    <tr>
      <td align="center">Nuevo Organismo
	  &nbsp;<input name="nuevo_org" type="text" id="nuevo_org" size="40">
	  &nbsp;<input name="bnuevo" type="submit" id="bnuevo" value="Agregar">
		<br><br>
		<select name="select_org" size="10" id="select_org" style="width:94%" 
onchange="
//var tiempo1=new Date();//variables para medir el tiempo de demora
//window.cursor='hand';
var str=get_entidades(this.options[this.selectedIndex].value);
var vector=str.split('_');
document.all.ent_org_count.innerHTML=vector[0];
document.all.ent_count.innerHTML=vector[1];
//var tiempo2=new Date();
//var tiempototal=tiempo2.getSeconds()-tiempo1.getSeconds();
//alert(tiempototal);
">
<!--
onKeypress="buscar_op(this);"
onblur="borrar_buffer();"
onclick="borrar_buffer();"
-->
<?=make_options($organismos,"id_org","nombre","title=''"); ?>		
		</select></td>
    </tr>
  </table>
  <br>
  <table width="100%"  border="0" align="center" cellspacing="1">
    <tr align="center" >
      <td width="47%" id=mo>Clientes disponibles: <span id="ent_count">0</span> </td>
      <td width="6%" valign="bottom" style="border-bottom:none" >
	      <input type="submit" name="bguardar" value="Guardar" title="Guardar los cambios" 
		  onclick=" if (document.all.select_org.selectedIndex!=-1) 
						get_ent_ids();
					else 
					{alert ('Debe seleccionar un organismo');return false}" >
      </td>
      <td width="47%" id=mo >Componentes del Organismo: <span id="ent_org_count">0</span></td>
    </tr>
    <tr>
      <td align="center">
      <select name="select_ent" size="10" multiple id="select_ent" style="width:100%"
onKeypress="buscar_op(this);"
onblur="borrar_buffer();"
onclick="borrar_buffer();"
 >
<?=make_options($entidades,"id_entidad","nombre"); ?>		
      </select></td>
      <td align="center" style="border-top:none">
	  <input name="bagregar" type="button" id="bagregar" value="&nbsp;&nbsp;&nbsp;&gt;&gt;&nbsp;&nbsp;&nbsp;"  title="Agregar al Organismo" onclick="total=move_options(form1.select_ent_org,form1.select_ent);	
if (total)
	{
		document.all.ent_org_count.innerHTML=parseInt(document.all.ent_org_count.innerHTML)+total;
		document.all.ent_count.innerHTML=parseInt(document.all.ent_count.innerHTML)-total;
	}
"> 
	  <br><br>
      <input name="bquitar" type="button" id="bquitar" value="&nbsp;&nbsp;&nbsp;&lt;&lt;&nbsp;&nbsp;&nbsp;" title="Quitar del Organismo" onclick="total=move_options(form1.select_ent,form1.select_ent_org);	
if (total)
	{
		document.all.ent_org_count.innerHTML=parseInt(document.all.ent_org_count.innerHTML)-total;
		document.all.ent_count.innerHTML=parseInt(document.all.ent_count.innerHTML)+total;
	}
">
      </td>
      <td align="center">
      <select name="select_ent_org" size="10" multiple id="select_ent_org" style="width:100%">
      </select></td>
    </tr>
  </table>
</form>
<script >form1.select_org.focus()</script>
</body>
</html>
<?//=fin_pagina(); ?>