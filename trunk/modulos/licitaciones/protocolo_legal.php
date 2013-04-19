<?php
//archivo de configuracion
require_once("../../config.php");

function existe($titulo,$obj)
{$obj->Move(0);  //muevo al principio el indice para realizar la busqueda
 $termine=0;
 while ((!$obj->EOF) && (!$termine))
 {if ($obj->fields['titulo']==$titulo)
  $termine=1;
  else
  $obj->MoveNext();
 }
return $termine; //retorno si existe o no el requisito por defecto y $resultado_def vuelve en la posicion del mismo
}

//verifico si se agrego nuevo requisito si se agrego lo inserto en la BD


//guardamos el protocolo en la BD

?>
<html>
<head>
<title>Protocolo Legal</title>
<script languaje="javascript">
function limpiar(objeto)
{if ((objeto.value=="Ingrese un lugar") || (objeto.value=="Ingrese un comentario(opcional)"))
 objeto.value="";
}
</script>

<?php
include("../ayuda/ayudas.php");
?>
</head>
<body bgcolor="<?php echo $bgcolor3; ?>">

<!-- Descripcion de la entidad -->
<?php
/*
<input type="text" name="procedimiento" size="40" style="border-style:none;background-color:<?php echo $bgcolor1; ?>;color:<?php echo $color2; ?>;font-weight: bold;" value="<?php echo $resultado->fields['procedimiento']; ?>">
*/

$sql="select entidad.nombre,licitacion.fecha_apertura from (licitacion join entidad on licitacion.id_licitacion=".$parametros['id_lic']." and licitacion.id_entidad=entidad.id_entidad);";
$resultado = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$color2=$bgcolor3; //colores de fondo y letras
$color1="white";
$dia=substr($resultado->fields['fecha_apertura'],0,10);
$hora=substr($resultado->fields['fecha_apertura'],11);
$link=encode_link("cargar_protocolo.php",array("id_lic"=>$parametros['id_lic'],"entidad"=>$resultado->fields['nombre'],"dia"=>$dia,"hora"=>$hora));
$sql="select * from protocolo_leg where entidad='".$resultado->fields['nombre']."' and id_licitacion=".$parametros['id_lic']; //verifico si se ha cargado algun protocolo
$resultado_ex=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$exis=$resultado_ex->RecordCount();
?>

<form name="form1" action="<?php echo $link; ?>" method="POST">
<br>
<div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_protocolo.htm" ?>', 'CARGAR PROTOCOLO LEGAL')" >
    </div>
<br>

<table width="95%" align="center" cellspacing="2">
<th bgcolor="white" colspan="2" style="border-color:black black black black;border-style:solid;border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px;"><b>Datos de la Entidad</b></th>
<tr>
<td  width="20%"><b><u>Licitacion Nº:</u></b></td>
<td  width="80%"><b><?php echo $parametros['id_lic']; ?></b></td>
</tr>
<tr>
<td bgcolor="<?php echo $bgcolor1; ?>" width="20%"><font color="<?php echo $color1; ?>"> <b>Cliente</b></td>
<td bgcolor="<?php echo $bgcolor1; ?>" width="80%"><font color="<?php echo $color2; ?>"><b><?php echo $resultado->fields['nombre']; ?></b></td>
</tr>
<tr>
<td bgcolor="<?php echo $bgcolor1; ?>" width="20%"><font color="<?php echo $color1; ?>"><b>Procedimiento</b></td>
<td bgcolor="<?php echo $bgcolor1; ?>" width="80%"><font color="<?php echo $color2; ?>"><input type="text" name="procedimiento" size="70" value="<?php if ($_POST['procedimiento']!="") echo $_POST['procedimiento']; else{if ($exis) echo $resultado_ex->fields['procedimiento']; else echo $parametros['num'];} ?>"></td>
</tr>
<tr>
<td bgcolor="<?php echo $bgcolor1; ?>" width="20%"><font color="<?php echo $color1; ?>"><b>Fecha Apertura</b></td>
<td bgcolor="<?php echo $bgcolor1; ?>" width="80%"><font color="<?php echo $color2; ?>"><b><?php echo $dia; ?></b></td>
</tr>
<tr>
<td bgcolor="<?php echo $bgcolor1; ?>" width="20%"><font color="<?php echo $color1; ?>"><b>Hora</b></td>
<td bgcolor="<?php echo $bgcolor1; ?>" width="80%"><font color="<?php echo $color2; ?>"><b><?php echo $hora; ?></b></td>
</tr>
<tr>
<td bgcolor="<?php echo $bgcolor1; ?>" width="20%"><font color="<?php echo $color1; ?>"><b>Lugar</b></td>
<td bgcolor="<?php echo $bgcolor1; ?>" width="80%"><font color="<?php echo $color2; ?>"><input type="text" name="lugar" size="70" <?php if ($exis) echo "value='".$resultado_ex->fields['lugar']."'"; else echo "value='Ingrese un lugar' onfocus=\"limpiar(document.all.lugar);\""; ?>></td>
</tr>
<tr>
<td bgcolor="<?php echo $bgcolor1; ?>" width="20%"><font color="<?php echo $color1; ?>"><b>Comentarios</b></td>
<td bgcolor="<?php echo $bgcolor1; ?>" width="80%"><font color="<?php echo $color2; ?>"><input type="text" name="comentarios" size="70"  <?php if ($_POST['comentarios']!="") echo "value=".$_POST['comentarios']; else{if ($exis) echo "value='".$resultado_ex->fields['comentarios']."'"; else echo "value='Ingrese un comentario(opcional)' onfocus=\"limpiar(document.all.comentarios);\"";} ?>></td>
</tr>
</table>
<br>
<table align="right" cellspacing="2">
<tr style="cursor:hand;" title="Si usted chequea esta casilla se guardara este protocolo por defecto para la entidad <?php echo $resultado->fields['entidad']; ?> para sus proximas licitaciones ">
<td><b>Guardar por defecto</b></td>
<td><input type="checkbox" name="pordefecto" value="si"></td>
</tr>
</table>
<br>
<br>
<table width="100%">
<tr bgcolor="white">
<td colspan="3" style="border-color:black black black black;border-style:solid;border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px;">
<table width="100%">
  <tr>
  <td width="40%"><b>Requisitos Legales</b></td>
  <td width="5%"></td>
  <td width="55%"><b>Comentarios</b></td>
  </tr>
</table>
</td>
</tr>
<?php
$sql="select * from plantillas_pl where entidad='".$resultado->fields['nombre']."'"; //busco si se cargo un protocolo por defecto
$resultado_def = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$sql="select * from requisitos order by id_reg"; //busco todos los requisitos
$resultado_todos = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
if (!$exis) //si no existe entonces cargo los valores por defecto de esa entidad
{while (!$resultado_todos->EOF) {
 if (existe($resultado_todos->fields['titulo'],&$resultado_def)) //busco si existe ese requisito en los valores por defecto
{ if ($resultado_def->fields['activo']=="t")
   $chequeado="checked";
  else
   $chequeado="";
  $comentario=$resultado_def->fields['comentario'];
}
else
 {$chequeado="";
  $comentario="";
 }
?>
<tr>
<td width="40%" bgcolor="<?php echo $bgcolor1; ?>"><font color="<?php echo $color1; ?>"><b><?php echo $resultado_todos->fields['titulo']; ?></b></font></td>
<td width="5%" bgcolor="<?php echo $bgcolor1; ?>"><input type="checkbox" name="<?php echo "check".$resultado_todos->fields['titulo']; ?>" value="t" <?php echo $chequeado; ?>></td>
<td width="55%" bgcolor="<?php echo $bgcolor1; ?>"><b><input type="text" name="<?php echo "text".$resultado_todos->fields['titulo']; ?>" size="50" value="<?php echo $comentario; ?>"></td>
</tr>
<?php
$resultado_todos->MoveNext();
}//fin while
}
else //el protocolo ya habia sido cargado
{
$sql="select * from items_pl where id_prolegal='".$resultado_ex->fields['id_prolegal']."'"; 
$resultado_ex = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
$resultado_todos->Move(0);
while (!$resultado_todos->EOF)
{if (existe($resultado_todos->fields['titulo'],&$resultado_ex)) //busco si existe ese requisito en los valores por defecto
{ if ($resultado_ex->fields['activo']=="t")
   $chequeado="checked";
  else
   $chequeado="";
  $comentario=$resultado_ex->fields['comentario'];
}
else
 {$chequeado="";
  $comentario="";
 }
?>
<tr>
<td width="40%" bgcolor="<?php echo $bgcolor1; ?>"><font color="<?php echo $color1; ?>"><b><?php echo $resultado_todos->fields['titulo']; ?></b></font></td>
<td width="5%" bgcolor="<?php echo $bgcolor1; ?>"><input type="checkbox" name="<?php echo "check".$resultado_todos->fields['titulo']; ?>" value="t" <?php echo $chequeado; ?>></td>
<td width="55%" bgcolor="<?php echo $bgcolor1; ?>"><b><input type="text" name="<?php echo "text".$resultado_todos->fields['titulo']; ?>" size="50" value="<?php echo $comentario; ?>"></td>
</tr>
<?php
$resultado_todos->MoveNext();
}//fin while
}
?>
<tr></tr>
<tr></tr>
<tr></tr>
<tr>
<td colspan="4" bgcolor="white" style="border-color:black black black black;border-style:solid;border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px;"><font color="black"><b>Nuevos Requisitos: Chequee la casilla de la derecha para insertar un nuevo requisito</b></font></td>
</tr>
<tr>
<td colspan="3" bgcolor="white" style="border-color:black black black black;border-style:solid;border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px;">
<table width="100%">
  <tr>
  <td width="40%"><b>Requisitos Legales</b></td>
  <td width="5%"></td>
  <td width="55%"><b>Comentarios</b></td>
  </tr>
</table>
</td>
</tr>
<tr>
<td width="40%" bgcolor="<?php echo $bgcolor1; ?>"><font color="<?php echo $color1; ?>"><b><input type="text" name="requisito1" size="50" value=""></b></font></td>
<td width="5%" bgcolor="<?php echo $bgcolor1; ?>"><input type="checkbox" name="check1" value="t"> </td>
<td width="55%" bgcolor="<?php echo $bgcolor1; ?>">
<table>
<tr>
<td width="95%"><b><input type="text" name="text1" size="50" value=""></td>
<td width="5%"><input type="checkbox" name="insertar_req1" value="t"></td>
</tr>
</table>
</tr>
<tr>
<td width="40%" bgcolor="<?php echo $bgcolor1; ?>"><font color="<?php echo $color1; ?>"><b><input type="text" name="requisito2" size="50" value=""></b></font></td>
<td width="5%" bgcolor="<?php echo $bgcolor1; ?>"><input type="checkbox" name="check2" value="t"></td>
<td width="55%" bgcolor="<?php echo $bgcolor1; ?>">
<table>
<tr>
<td width="95%"><b><input type="text" name="text2" size="50" value=""></td>
<td width="5%"><input type="checkbox" name="insertar_req2" value="t"></td>
</tr>
</table>
</td>
</tr>
</table>
<br>
<center>
<input type="hidden" name="requisito" value="0">
<input type="submit" name="boton2" value="Cargar Protocolo" style='width:140;' >
<input type="button" name="boton"  value="Volver" style='width:140;' onclick="history.go(-1)">
</center>
<br>
<br>
</form>
</body> 