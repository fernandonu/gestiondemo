<?php
//INCLUIR gestion/config.php
include("../../../config.php");
//variables globales
global $resultado; //para el resultado de los queries que se ejecuten
global $filas_encontradas;
//global $db;//conexion a la base de datos 

//ejecuta query y devuelve el resultado en la variable $resultado
//y la cantidad de filas encontradas en la variable $filas_encontradas
//el resultado es por indices asociativos
function ejecutar_query($query)
{
 global $resultado,$filas_encontradas, $db;
 db_tipo_res("a");
 $resultado_tmp = $db->Execute($query);
 $filas_encontradas=0;
 if (!$resultado_tmp)
  die($db->ErrorMsg()); //o retornar cero
 else 
  while (!$resultado_tmp->EOF)
  {
   $resultado[$filas_encontradas++]=$resultado_tmp->fields;
   $resultado_tmp->MoveNext();
  }
 
 return 1;
}

/**********************************************************************************
FUNCIONES VIEJAS

//variables globales
$resultado; //para el resultado de los queries que se ejecuten
$filas_encontradas;//
$link;
// Connect
function db_conectar()
{    global $link;
	 $db_host = "192.168.1.50";  // Localización de la base de datos
     $db_name="coradir";
     $db_user="projekt";
     $db_pass="propcp";
     if($link==NULL)
     {$link = pg_connect((($db_host == "") ? "" : "host=$db_host ").(($db_pass == "") ? "" : "password=$db_pass ")."dbname=$db_name user=$db_user") or pg_errormessage("No se pudo conectar");
     }
}

//desconectarse de la base de datos
function db_desconectar()
{global $link;
 	pg_close($link);
   }

//ejecuta query y devuelve el resultado en la variable $resultado
//y la cantidad de filas encontradas en la variable $filas_encontradas
function ejecutar_query($query)
{global $link;global $resultado,$filas_encontradas;
 if($res=pg_query($link,$query))
 {$filas_encontradas=pg_num_rows($res);
  $i=0;
  while($i<$filas_encontradas)
  {$resultado[$i]=pg_fetch_array($res,$i);
   $i++;
  }
  return 1;
 }
 else
 {echo "Error: $query<br>";
  return 0;
 }
}
**********************************************************************************/
?>