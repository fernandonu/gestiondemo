<?PHP
/****************************************************************************
CHKPROTOCOLO por GACZ
funcion que chequea la existencia del protocolo (sin importar el usuario)
@licitacion entero mayor que cero
@renglon entero mayor que cero
@item entero mayor que cero
@protocolo entero entre 1...5
retorna 
    -un valor mayor de cero si existe (retorrna el numero de version mayor del
                                       protocolo)
    -cero si no se encontro

****************************************************************************/
global $err;

function chkprotocolo($licitacion,$renglon,$item)
{
 global $resultado; 
 global $filas_encontradas;
 global $link;
 global $db;
 
 if (($licitacion=="") || ($renglon==""))
 {$licitacion=-1;
  $renglon=-1;
 }
 
 $query="select * from protocolo where id_renglon=".$renglon.
       " AND nro_licitacion=".$licitacion."";
 $resultado_old=$resultado;
 $filas_old=$filas_encontradas;
 $resultado = $db->Execute($query) or die($db->ErrorMsg());
 $filas_encontradas=$resultado->RecordCount();
 if ($filas_encontradas)
 {
   $ver_max=1;
   $ver_max_index=0; //para las filas con datos
   $ver_max_index_check=0; //para las filas con checkbox 
   for($i=0; $i < $filas_encontradas; $i++)
   {
  	if ($resultado->fields['nro_version'] > $ver_max)
  	{
  	 $ver_max=$resultado->fields['nro_version'];
  	 //controla que no sea una fila de checkbox solamente
  	 if ($resultado->fields['check_data']!='f' || $resultado->fields['checkdata']!='t')
  	   $ver_max_index=$i;
  	 else 
  	   $ver_max_index_check=$i;
  	}
  	$resultado->MoveNext();
   }
   $resultado=$resultado_old;
   $filas_encontradas=$filas_old;
   return $ver_max;
 }
 else 
 {
  $resultado=$resultado_old;
  $filas_encontradas=$filas_old;
  return 0;
 } 
}

/****************************************************************************
CHKUSERNAME por GACZ
funcion que chequea la existencia del usuario en la base de Datos (schema public)
retorna 
    -un valor mayor de cero si existe
    -cero si no se encontro

****************************************************************************/
/*
function chkusername($username)
{global $db;
 global $resultado; 
 global $filas_encontradas;
 global $link;
 global $_ses_user_login;
 $query="select * from usuarios where login='".$_ses_user_login."'";
 $resultado_old=$resultado;
 $filas_old=$filas_encontradas;
 $resultado = $db->Execute($query) or die($db->ErrorMsg());
 $filas_encontradas=$resultado->RecordCount();
 if ($filas_encontradas)
 {
   $resultado=$resultado_old;
   $f1=$filas_encontradas;
   $filas_encontradas=$filas_old;
   return $f1;
 }
 else 
  return 0;
}
*/
function error_mens($title,$error)
{echo "
<html>
<head>
<title>".$title."</title>
</head>
<body bgcolor=\"E0E0E0\">
<div align=\"center\">
  <p><left><font color=\"#006699\" face=\"Georgia, Times New Roman, Times, serif\"><b>".$title."</b></font></left></p>
  <p><left></left><left><font color=\"#006699\" face=\"Georgia, Times New Roman, Times, serif\"><b>".$error."</b></font></left></p>
  <p><left></left></p>
</div>
<div align=\"center\">
    <input type=\"button\" name=\"boton\" value=\"Volver\" onclick=\"history.go(-1)\">
</div>
</body>
</html>";
}
?>