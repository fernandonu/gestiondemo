<?php
require_once("../../config.php");
$check=array("xt"=>"t","x"=>"f");

function existe($titulo)
{global $resultado_def;
 $resultado_def->Move(0);  //muevo al principio el indice para realizar la busqueda
 $termine=0;
 while ((!$resultado_def->EOF) && (!$termine))
 {if ($resultado_def->fields['titulo']==$titulo)
  $termine=1;
  else
  $resultado_def->MoveNext();
 }
return $termine; //retorno si existe o no el requisito por defecto y $resultado_def vuelve en la posicion del mismo
}



$exis=0;
$sql="select * from protocolo_leg where id_licitacion=".$parametros['id_lic']." and entidad='".$parametros['entidad']."';";
$resultado_ex=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
if ($resultado_ex->RecordCount()>0) //entonces actualizo el protocolo
{$sql="update protocolo_leg set entidad='".$parametros['entidad']."' , procedimiento='".$_POST['procedimiento']."' , fecha_aper='".$parametros['dia']."' , hora='".$parametros['hora']."' , lugar='".$_POST['lugar']."' , comentarios='".$_POST['comentarios']."' , id_licitacion=".$parametros['id_lic'];  //actualizo protocolo legal para la licitacion y entidad correspondiente
 $sql.=" where id_prolegal=".$resultado_ex->fields['id_prolegal'];
 $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 $exis=1;
 $id_prolegal=$resultado_ex->fields['id_prolegal'];
}
else //inserto un nuevo protocolo
{$sql="insert into protocolo_leg (entidad,procedimiento,fecha_aper,hora,lugar,comentarios,id_licitacion)";  //inserto protocolo legal para la licitacion y entidad correspondiente
 $sql.=" values('".$parametros['entidad']."','".$_POST['procedimiento']."','".$parametros['dia']."','".$parametros['hora']."','".$_POST['lugar']."','".$_POST['comentarios']."',".$parametros['id_lic'].");";
 $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 $sql="select max(id_prolegal) from protocolo_leg;"; 
 $resultado = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 $id_prolegal=$resultado->fields['max'];
}

$sql="select * from requisitos order by id_reg;";
$resultado_todos = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql); 
$resultado_todos->Move(0);
$sql="select * from plantillas_pl where entidad='".$parametros['entidad']."'";  //obtengo si los hay los valores por defecto ya almacenados
$resultado_def = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
while (!$resultado_todos->EOF) 
{$result=str_replace(" ",'_',$resultado_todos->fields['titulo']);
 $result=str_replace('.','_',$result);
 if ($_POST['pordefecto']=="si") //se deben almacenar los valores por defecto
 {if (existe($resultado_todos->fields['titulo'])) //actualizo a los nuevos valores por defecto
  {$sql="update plantillas_pl set titulo='".$resultado_todos->fields['titulo']."', activo='".$check["x".$_POST["check".$result]]."', comentario='".$_POST["text".$result]."', entidad='".$parametros['entidad']."' where id_plant=".$resultado_def->fields['id_plant'];
   $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
  }
  else 
  {$sql="insert into plantillas_pl (titulo,activo,comentario,entidad,tipo_entidad) values ('".$resultado_todos->fields['titulo']."','".$check["x".$_POST["check".$result]]."','".$_POST["text".$result]."','".$parametros['entidad']."',0);";
   $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
  }
 }
//almaceno el protocolo legal en la BD
if ($resultado_ex->RecordCount()>0) //entonces actualizo los items cargados anteriormentes
{$sql="update items_pl set activo='".$check["x".$_POST["check".$result]]."', comentario='".$_POST["text".$result]."' where id_prolegal=".$resultado_ex->fields['id_prolegal']." and titulo='".$resultado_todos->fields['titulo']."';";
 $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
}
else
{
$sql="insert into items_pl (id_prolegal,titulo,activo,comentario) values(".$resultado->fields['max'].",'".$resultado_todos->fields['titulo']."','".$check["x".$_POST["check".$result]]."','".$_POST["text".$result]."');";
$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql."entro por aca");
}
$resultado_todos->MoveNext();
}

if ($_POST['insertar_req1']=="t") //inserto nuevo requisito 
{$sql="insert into requisitos (titulo) values('".$_POST['requisito1']."');";
 $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 $sql="select max(id_reg) from requisitos;";
 $res=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 if ($exis)
  $id=$resultado_ex->fields['id_prolegal'];
 else
  $id=$resultado->fields['max'];
 $sql="insert into items_pl (id_prolegal,titulo,activo,comentario) values(".$id.",'".$_POST['requisito1']."','".$check["x".$_POST['check1']]."','".$_POST["text1"]."');";
 $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql."requisito1");
}


if ($_POST['insertar_req2']=="t") //inserto nuevo requisito para la entidad y la licitacion
{$sql="insert into requisitos (titulo) values('".$_POST['requisito2']."');";
 $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 $sql="select max(id_reg) from requisitos;";
 $res=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 if ($exis)
  $id=$resultado_ex->fields['id_prolegal'];
 else
  $id=$resultado->fields['max'];
 $sql="insert into items_pl (id_prolegal,titulo,activo,comentario) values(".$id.",'".$_POST['requisito2']."','".$check["x".$_POST['check2']]."','".$_POST["text2"]."');";
 $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 
}

require_once("pdf_req_legales.php");
$link=encode_link("licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$parametros['id_lic']));
header("location: $link");
?>