<?
/*
Autor: GACZ

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.5 $
$Date: 2004/07/28 21:45:13 $
*/

require_once("../../config.php");

$f1=date("Y-m-j H:i:s");

$respuestas=PostvartoArray("select_");
//echo "array_respuestas: ";print_r($respuestas)."<br>";

$q="select * from preguntas where mostrar='s' order by posicion";
$preguntas=sql($q) or fin_pagina();

$q="select nextval('encuesta_lic_id_encuesta_seq') as id";
$id_encuesta=sql ($q) or fin_pagina();
$id_encuesta=$id_encuesta->fields[id];

$q="insert into encuesta_lic 
	(id_encuesta,id_licitacion,cliente,cargo,telefono,user_name,user_login,fecha_encuesta,comentarios) 
	values 
	($id_encuesta,	$id_licitacion,'$nombre','$cargo','$telefono','$_ses_user[name]','$_ses_user[login]','$f1','$comentarios')";

$res=sql ($q) or fin_pagina();
$array_preguntas;
$i=0;
	while (!$preguntas->EOF)
	{
		$array_preguntas[$i][id_resultado]="";
		$array_preguntas[$i][id_pregunta]=$preguntas->fields[id_pregunta];
		$array_preguntas[$i][id_encuesta]=$id_encuesta;
		$array_preguntas[$i][puntaje]=$respuestas[$preguntas->fields[id_pregunta]] 
		or
		$array_preguntas[$i][puntaje]=0;
		$i++;
		$preguntas->MoveNext();
	}
	if (0 == replace("resultados",$array_preguntas,array("id_resultado")))
		$msg="Su encuesta se guardo exitosamente";
	else 
		$msg="Su encuesta no se pudo guardar";

	 header("location: ". encode_link("lic_calif_lista.php",array("msg"=>$msg)));	
	
?>