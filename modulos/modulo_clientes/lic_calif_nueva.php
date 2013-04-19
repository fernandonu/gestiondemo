<? 
/*
Autor: GACZ

MODIFICADA POR
$Author: ferni $
$Revision: 1.3 $
$Date: 2006/11/23 14:41:31 $
*/

require_once("../../config.php");
require_once("../general/funciones_contactos.php");

if ($_POST[encu_guardar])
{
 extract($_POST,EXTR_SKIP);
 include("lic_calif_proc.php");
 die;
}

if($_POST["no_hacer"]=="No realizar la encuesta")
{
	extract($_POST,EXTR_SKIP);
	$f1=date("Y-m-j H:i:s");
	$q="select nextval('encuesta_lic_id_encuesta_seq') as id";
    $id_encuesta=sql ($q) or fin_pagina();
    $id_encuesta=$id_encuesta->fields[id];
	
	$q="insert into encuesta_lic 
	(id_encuesta,id_licitacion,cliente,cargo,telefono,user_name,user_login,fecha_encuesta,no_hacer,fecha_no_hacer,user_no_hacer) 
	values 
	($id_encuesta,	$id_licitacion,'$nombre','$cargo','$telefono','$_ses_user[name]','$_ses_user[login]','$f1',1,'$f1','$_ses_user[name]')";

 if($db->Execute ($q) or die($db->ErrorMsg()."<br>$q"))
  $msg="Su encuesta se guardo exitosamente"; 
 else 
  $msg="Su encuesta no se pudo guardar"; 
  
 header("location: ". encode_link("lic_calif_lista.php",array("msg"=>$msg)));	
}  

function crear_puntaje($id_pregunta,$selected=false)
{global $db;
	$buffer="<option value=-1>PUNTAJE</option>";
	$i=0;
	//traemos los posibles valores para la pregunta
	$query="select valores.* from encuestas.valores join encuestas.valores_pregunta using (id_valor) where id_pregunta=$id_pregunta order by valoracion";
	$valores=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer valores de la pregunta $id_pregunta (funcion de crear puntaje");
	while (!$valores->EOF)
	{ $buffer.="<option value='".$valores->fields['valoracion']."' ".(($selected==$valores->fields['valoracion'])?"selected":"").">".$valores->fields['nombre']."</option>";
	 $valores->MoveNext();
	}
return  $buffer;
}
?>

<html>
<head>
<title>Satisfaccion de clientes - Encuesta</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?=$html_header?>
</head>
<body>
<script src="<?=$html_root."/lib/fns.js"?>" ></script>
<script>
//variable que almacena las preguntas ocultas (ver al final de la pagina)
var hidden_rows= new Array();

function insertar_fila(start_index,count,pos)
{
var count_aux=0;
	while (count_aux < count)
	{
		var fila=document.all.preguntas.insertRow(pos+count_aux);
		fila.insertCell(0).innerHTML=hidden_rows[start_index+count_aux][0];
		fila.insertCell(1).innerHTML=hidden_rows[start_index+(count_aux++)][1];
		fila.cells[1].align='right';
	}
}
function borrar_fila(index,cantidad)
{

	while (cantidad--)
		document.all.preguntas.deleteRow(index);
}

//variable que contiene el mensaje de error
var msg;
function chk_campos()
{
	var retvalue=0;
	msg ='------------------------------------------------------\n';
	msg+='Falta completar:\n\n';
   if (trim(document.all.nombre.value)=='')
   {
		msg+='\tApellido y Nombre\n';
		retvalue++;
   }
   if (trim(document.all.telefono.value)=='')
   {
		msg+='\tTelefono\n';
		retvalue++;
   }
	msg+='------------------------------------------------------';
 
   if (retvalue) return retvalue;
   else msg='';

   for (var i=0;i < document.forms[0].elements.length ; i++) 
   {
   	var elem=document.forms[0].elements[i];

   	if (elem.type=='select-one' && elem.selectedIndex==0 )
   	{
			msg ='-----------------------------------------------------------------\n\n';
			msg+=' Por favor seleccione un puntaje para cada pregunta\n\n';
			msg+='-----------------------------------------------------------------';
   		return 1;
   	}
   }
}

</script>
<? 
$campos="licitacion.id_licitacion,licitacion.fecha_entrega,entidad.id_entidad,entidad.nombre as nbre_entidad,encuesta_lic.*";
$q = "select  $campos from 
		licitacion join 
		entidad using(id_entidad) left join 
		encuesta_lic using(id_licitacion) 
		where id_licitacion=$parametros[id_licitacion]";
	$datos=sql($q) or fin_pagina();
	
if ($datos->fields[id_encuesta])
{
	$q="select * from resultados where id_encuesta=".$datos->fields[id_encuesta];	
	$res=sql($q) or fin_pagina();
	$_permisos[bguardar]= " disabled ";
	$_permisos[select]= " disabled ";
	$_permisos[text]= " readonly ";
	$_permisos[radio]= " disabled ";
	$_permisos[comentarios]= " readonly ";
	
	$items=0;
	$total=0;
	while (!$res->EOF)
	{
		if ($res->fields[puntaje] > 0)
		{
			$items++;
			$total+=$res->fields[puntaje];
		}
		$res->MoveNext();	
	}
	if($items>0)
	 $media=number_format($total/$items,2,",","");
	$res->MoveFirst();
}
else 
{
	$q="select max(id_encuesta) from encuesta_lic ";	
	$res=sql($q) or fin_pagina();
	$id_encuesta=$res->fields[max]+1;
}

?>
<form name="form1" method="post" action="<?=$_SERVER['SCRIPT_NAME'] ?>">
  
  <table width="95%" border="0" cellspacing="1" cellpadding="1">
  <tr>
    <td width="50%">
     <table width="80%" border="1" align="left" cellpadding="1" cellspacing="1">
<? if ($media)
	{
?>
    		<tr> 
            <td width="20%">PUNTAJE</td>
            <td width="8%" align="right">&nbsp;<?= $media ?> <!-- <input name="promedio" type="text"  size="7" style="border: none;text-align: right;" > -->
            </td>
          </tr>
<? } ?>
          <tr> 
            <td>ID LICITACION</td>
            <td align="right"><?=$parametros[id_licitacion] ?></td>
          </tr>
        </table>
    </td>
    <?
    if(permisos_check("inicio","no_hacer_encuesta") && $_permisos[text]=="")
    {
    ?>
     <td>
      <input type="submit" name="no_hacer" value="No realizar la encuesta">
     </td>
    <?
    }
    ?> 
    <td width="50%" align="right"><b>Fecha de Entrega: <?=fecha($datos->fields['fecha_entrega'])?></b></td>
  </tr>
</table>
<hr>
<font color="Blue">
  <b>
  <br>
  Buenos Días (o Buenas Tardes), necesito hablar con el encargado del área de Informática.-
  <br><br>
  Hace un par de meses ustedes adquirieron equipamiento informático de nuestra empresa, ¿podría hacerle una pequeña encuesta?
  <br><br>
  </b>
  </font>
  <hr>
  <table width="100%" border="1" align="center" cellpadding="1" cellspacing="0">
    <tr> 
      <td width="30%">ENCUESTA N&ordm;&nbsp; 
        <?= ($datos->fields[id_encuesta])?$datos->fields[id_encuesta]:$id_encuesta; ?>
      </td>
      <td width="40%">USUARIO: &nbsp;&nbsp;
        <?= $datos->fields[user_name] ?>
      </td>
      <td>&nbsp;</td>
      <?//<td width="30%">FECHA: <?= ($datos->fields[fecha_encuesta])?date("j/m/Y H:i:s",strtotime($datos->fields[fecha_encuesta])):date("j/m/Y"); </td>?>
    </tr>
  </table>
  <table width="100%">
   <td width="60%">
    <table border="1" cellpadding="1" cellspacing="0">
     <tr> 
      <td width="20%">ENTIDAD</td>
      <td width="80%"><?= $datos->fields[nbre_entidad] ?></td>
     </tr>
     <tr> 
      <td>APELLIDO Y NOMBRE</td>
      <td><input type="text" name="nombre" size="50" value="<?= $datos->fields[cliente] ?>" <?=	$_permisos[text] ?> ></td>
     </tr>
     <tr>
      <td>CARGO / AREA</td>
      <td><input type="text" name="cargo" size="50" value="<?= $datos->fields[cargo] ?>" <?=	$_permisos[text] ?> ></td>
     </tr>
     <tr> 
      <td>TELEFONO</td>
      <td><input type="text" name="telefono" size="50" value="<?= $datos->fields[telefono] ?>" <?=	$_permisos[text] ?> ></td>
     </tr>
    </table> 
   </td> 
   <td width="40%" align="center">
    <table>
     <tr align="center">
      <td colspan="2">
       <b>Contactos</b>
      </td>
     </tr>
     <tr >
      <td>
       <?
         $nuevo_contacto=encode_link("../general/contactos.php",array("modulo"=>"Licitaciones",
										 "id_licitaciones"=>$parametros[id_licitacion],//$ID,
										 "id_general"=>$datos->fields['id_entidad']));//$result->fields['id_entidad']));
       ?>										 
       <input type="button" name="Nuevo" Value="Nuevo Contacto" style="width:100%" onclick="window.open('<?=$nuevo_contacto?>','','toolbar=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=550')">
      </td>
     </tr>
     <tr>
      <td>
       &nbsp
      </td>
     </tr>
     <tr>
      <td align="center">
       <?
  
       contactos_existentes("Licitaciones",$datos->fields['id_entidad']);//$result->fields['id_entidad']);
       ?>
      </td>  
     </tr>
    </table>
   </td>
  </table>
  <BR>
  
<?

if ($datos->fields[id_encuesta])
	$q="select * from preguntas join resultados using(id_pregunta) 
		where id_encuesta=".$datos->fields[id_encuesta]." order by posicion";
else
	$q="select * from preguntas where mostrar='s' order by posicion";

$preguntas=sql($q) or fin_pagina();
$preguntas_ocultas;
$index_preguntas_ocultas=0;

?>

  <table id=preguntas width="100%" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000">
<? while (!$preguntas->EOF)
	{
?>
		  <tr> 
<? 		if ($preguntas->fields['parent'])  
			{
?>
		  <td width="88%" colspan=2><?=$preguntas->fields[pregunta] ?>
        &nbsp;Si&nbsp; <input type="radio" name="select_<?=$preguntas->fields[id_pregunta] ?>" <? if ($preguntas->fields[puntaje]==-1) echo " checked " ?> value=-1 <?=$_permisos[radio] ?> onmouseup="if (document.all.select_<?=$preguntas->fields[id_pregunta] ?>[1].checked || !document.all.select_<?=$preguntas->fields[id_pregunta] ?>[0].checked ) insertar_fila(0,3,4)" > &nbsp; 
        No 
        <input type="radio" name="select_<?=$preguntas->fields[id_pregunta] ?>" <? if ($preguntas->fields[puntaje]==0) echo " checked " ?>  value=0 <?=$_permisos[radio] ?> onmouseup="if (document.all.select_<?=$preguntas->fields[id_pregunta] ?>[0].checked) borrar_fila(4,3)"> 
		  </td>
<?
			}
			else
			{
?>
		  <td width="88%"><?=$preguntas->fields[pregunta] ?></td>
<?				
			}			
			if (!$preguntas->fields['parent']) 
			{	
?>
		      <td width="11%" align="right">
		      	<select name="select_<?=$preguntas->fields[id_pregunta] ?>" <?=	$_permisos[select] ?>>
		          <?= crear_puntaje($preguntas->fields[id_pregunta],$preguntas->fields[puntaje]); ?>
		        </select> 
		      </td>
<?
			}	
?>
		    </tr>
		    
<?
		if ($preguntas->fields['parent'] && $preguntas->fields[puntaje]!=-1)
		{
			$cant=$preguntas->fields['parent'];
			$preguntas->MoveNext();
			while  (!$preguntas->EOF && $cant--)
			{
           //buffer que almacena las preguntas ocultas
			  $preguntas_ocultas[$index_preguntas_ocultas][pregunta]=$preguntas->fields[pregunta];
           $preguntas_ocultas[$index_preguntas_ocultas++][puntuacion]="<select name='select_".$preguntas->fields[id_pregunta]."' $_permisos[select]>".crear_puntaje($preguntas->fields[id_pregunta],$preguntas->fields[puntaje])."</select>";

           $preguntas->MoveNext();
			}
		}
		else 
		  $preguntas->MoveNext();
		
	}
	if($datos->fields[no_hacer])
	{ $fecha=split(" ",$datos->fields[fecha_no_hacer]);
	  echo "<td align='center'><h5>El usuario ".$datos->fields[user_no_hacer]." ha elegido no realizar esta encuesta. Fecha: ".fecha($fecha[0])." ".$fecha[1]."</h5></center></td>";
	}
?>
  </table>
  <br>
   <center>
   <b>Comentarios Adicionales</b><br>
  	<textarea name="comentarios" rows="4" cols="80" <?=$_permisos[comentarios] ?> ><?=$datos->fields['comentarios'] ?></textarea>
  </center>
  <br>
  <hr>
   <font color="Blue">
   <b>
   Agradecemos enormemente su colaboración, gracias a su opinión podremos brindarle un mejor servicio.- 
   </font>
   </b> 
   <hr>
  <table width="100%" border="0" cellspacing="1" cellpadding="1">
    <tr>
      <td width="29%">&nbsp;</td>
      <td width="36%" align="center">
<input type="button" name="encu_volver" style="width:80" value="Volver" onclick="location.href='lic_calif_lista.php'" >
&nbsp;
<input type="submit" name="encu_guardar" style="width:80" value="Guardar" onclick="if (chk_campos()>0){alert (msg); return false;}" <?= $_permisos[bguardar] ?> >
      </td>
      <td width="35%">&nbsp;</td>
    </tr>
  </table>
<script>
<?
$i=0;
while ($i < $index_preguntas_ocultas)
{
?>
	hidden_rows[<?= $i ?>]=new Array();
	//columna 1
	hidden_rows[<?= $i ?>][0]="<?= $preguntas_ocultas[$i][pregunta] ?>"; 
	//columna 2
	hidden_rows[<?= $i ?>][1]="<?= $preguntas_ocultas[$i][puntuacion] ?>";
	
<?
	$i++;
}
?>
</script>
<input type="hidden" name="id_licitacion" value="<?= $parametros[id_licitacion] ?>">
  </form>
</body>
</html>
