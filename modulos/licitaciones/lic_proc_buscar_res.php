<?
/*
Author: GACZ

MODIFICADA POR
$Author: mari $
$Revision: 1.26 $
$Date: 2005/06/14 20:33:54 $
*/
require_once("../../config.php");
//-------------------------------------------
/*CAMPOS VIEJOS
$campos="licitacion.keyworditacion,renglon.id_renglon,nro_renglon,nro_item,
nro_alternativa,cantidad,titulo,nombre,monto_oferta,monto_unitario,ganada";
*/
$campos="licitacion.id_licitacion,entidad.nombre as nbre_entidad,
distrito.nombre as nbre_distrito,renglon.id_renglon,codigo_renglon,
cantidad,titulo,competidores.nombre,monto_unitario,ganada,oferta.observaciones,
moneda.simbolo,moneda.id_moneda,competidores.id_competidor,oferta.id,".
"case oferta.id_moneda when 2 then monto_unitario*valor_dolar_lic ".
"else monto_unitario end as monto_total,ganancia ";
$query="SELECT $campos from 
licitacion JOIN 
renglon on licitacion.id_licitacion=renglon.id_licitacion LEFT JOIN 
oferta on renglon.id_renglon=oferta.id_renglon LEFT JOIN 
moneda on oferta.id_moneda=moneda.id_moneda LEFT JOIN 
competidores on oferta.id_competidor=competidores.id_competidor LEFT JOIN 
entidad on licitacion.id_entidad=entidad.id_entidad LEFT JOIN 
tipo_entidad on entidad.id_tipo_entidad=tipo_entidad.id_tipo_entidad LEFT JOIN 
distrito on entidad.id_distrito=distrito.id_distrito ";

$do=$select_buscar or $do=2; //por defecto busca en todos los campos	
if ($chk_busqueda_general)
{
	$add_and=" AND ";
	switch ($do)
	{
		case 1://buscar id_licitacion
		     if (!es_numero($keyword))
		       $keyword=-1;
			$query.="where licitacion.id_licitacion=$keyword";break;
		case 3://numero de licitacion
			$query.="where licitacion.nro_lic_codificado ilike '%$keyword%'";break;
		case 4://comentarios
			$query.="where licitacion.observaciones ilike '%$keyword%'";break;
		case 7://distrito 
			$query.="where distrito.nombre ilike '%$keyword%'";break;
		case 8://entidad
			$query.="where entidad.nombre ilike '%$keyword%'";break;
		case 9://titulo de renglon
			/*$query.="where licitacion.id_licitacion in ".
			"(SELECT licitacion.id_licitacion from licitacion JOIN renglon on 
			licitacion.id_licitacion=renglon.id_licitacion where renglon.titulo ilike '%$keyword%') ";*/
			$query.="where renglon.id_renglon in ".
			"(SELECT renglon.id_renglon where renglon.titulo ilike '%$keyword%') ";
			break;
		case 5://observaciones
			$query.="where renglon.id_renglon in (SELECT oferta.id_renglon from oferta where oferta.observaciones ilike '%$keyword%')";
		    break;
		case 10://Por cantidad en Renglones
			$query.="where renglon.cantidad = $keyword";
		    break;
		case 6://tipo de renglon
		 	break;
		case 2://todos los campos
			
	}
}
else
{ 
	$add_and=" ";
	$query.=" where";
}
	
if($tipo_c_renglon)
{if ($tipo_renglon=="PC")
  $query.=$add_and."(renglon.tipo='Computadora Matrix' or renglon.tipo='Computadora Enterprise') ";
 else
  $query.=$add_and." renglon.tipo='$tipo_renglon' ";
 $add_and=" AND ";
}

if ($cantidad_renglon) {
	$query.=$add_and."renglon.cantidad between $cantidad_r_min and $cantidad_r_max ";
	$add_and=" AND ";	
}

if ($chk_precios)
{
	$query.=$add_and."(licitacion.id_moneda=$select_moneda AND (licitacion.$select_precios>=$precio_menor AND licitacion.$select_precios<=$precio_mayor)) ";
	$add_and=" AND ";
}
	
if ($chk_fechas)
{
	if ($select_fechas)
	{
		$query.=$add_and."($select_fechas>='".fecha_db($fecha_menor)."' AND ".
							  "$select_fechas<='".fecha_db($fecha_mayor)."')";
		$add_and=" AND ";
	}
}
	
if ($chk_estado)
{
	$query.=$add_and."licitacion.id_estado=$select_estado ";
	$add_and=" AND ";
}

if ($chk_id_lic)
{
	$query.=$add_and."licitacion.id_licitacion between $id_menor and $id_mayor ";
	$add_and=" AND ";
}

if ($chk_distrito)
{
	$query.=$add_and."entidad.id_distrito=$select_distrito ";
	$add_and=" AND ";
}

if ($chk_entidad)
{
	$query.=$add_and."licitacion.id_entidad=$select_entidad ";
	$add_and=" AND ";
}
	
if ($chk_tipo_entidad)
{
	$query.=$add_and."entidad.id_tipo_entidad=$select_tipo_entidad ";
	$add_and=" AND ";
}
	
if ($chk_competidor)
{
	/*$query.=$add_and."licitacion.id_licitacion in ".
			  "(select licitacion.id_licitacion from licitacion JOIN ".
			  "renglon on licitacion.id_licitacion=renglon.id_licitacion JOIN ".
			  "oferta on renglon.id_renglon=oferta.id_renglon ".
			  "where oferta.id_competidor=$select_competidor";*/
			
	$query.=$add_and."renglon.id_renglon in ".
			  "(select oferta.id_renglon from oferta
			    where oferta.id_competidor=$select_competidor";
			  
	$add_and=" AND ";

	if ($chk_ganador)	
		$query.=" AND oferta.ganada)";
	else 
		$query.=")";
}

if ($chk_competidores)
{
	$query.=$add_and."licitacion.id_licitacion in 
			  (select licitacion.id_licitacion from licitacion JOIN 
			  renglon on licitacion.id_licitacion=renglon.id_licitacion LEFT JOIN 
			  oferta on renglon.id_renglon=oferta.id_renglon 
			  where id_competidor=$select_comp1";
	if ($chk_ganador_vs) {
		$query.=" AND oferta.ganada";
	}
	$query.=") AND licitacion.id_licitacion in 
			  (select licitacion.id_licitacion from licitacion JOIN 
			  renglon on licitacion.id_licitacion=renglon.id_licitacion JOIN 
			  oferta on renglon.id_renglon=oferta.id_renglon 
			  where oferta.id_competidor=$select_comp2)";
	$add_and=" AND ";
}

$query .= " ORDER BY ";
if ($chk_ordenar) {
	$query .= "$select_ordenar $select_ordenar_dir,";
}
$query.="id_licitacion,codigo_renglon,id_renglon,monto_total ASC";
//echo "query: $query";
//  $query.=" ORDER BY licitacion.id_licitacion,nro_renglon,nro_item,nro_alternativa,monto_oferta ASC";
//LOS FILTROS DE LOS COMPETIDORES (AMBOS) FUNCIONAN BIEN
//El FILTRO DE ESTADO FUNCIONA BIEN
//EL FILTRO DE FECHAS FUNCIONA BIEN
//FUNCIONAN TODOS LOS FILTROS Y TIPOS DE BUSQUEDAS
?>
