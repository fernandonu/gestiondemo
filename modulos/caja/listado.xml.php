<?
/*
Autor: GACZ
Creado: miercoles 05/05/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.5 $
$Date: 2005/08/05 15:06:18 $
*/

require_once("../../config.php");
$datos_barra = array(
					array(
						"descripcion"	=> "Lista de Ingresos",
						"cmd"	=> "ingreso",
           	"extra" => array('distrito'=>$distrito)
						),
					array(
						"descripcion"	=> "Lista de Egresos",
						"cmd"			=> "egreso",
           	"extra" => array('distrito'=>$distrito)
						),
					array(
						"descripcion"	=> "Caja",
						"cmd"			=> "caja",
           	"extra" => array('distrito'=>$distrito)
						)
				 );

$add_where="";

	if($select_moneda!=-1)//filtrado por el tipo de moneda
  		$add_where.=" and id_moneda = $select_moneda";

  	if($cmd=="caja"){//si es caja
  		switch($estado_caja){//filtrado por caja abierta o cerrada
  			case 1: $add_where.=" and cerrada=0";break; 	
   			case 2: $add_where.=" and cerrada=1";break; 	
   			default:break;
  		}
 	} 
 	if($cmd=="ingreso"){
		if($id_t_ingreso >= 0)//filtrado por tipo de ingreso
  			$add_where.=" and ingreso_egreso.id_tipo_ingreso = $id_t_ingreso";
		if($id_t_cuenta!=-1)//filtrado por tipo_cuenta_ingreso
  			$add_where.=" and ingreso_egreso.id_cuenta_ingreso = $id_t_cuenta"; 		
   	}
  	if($cmd=="egreso"){
		if($id_t_egreso >= 0) //filtrado por tipo de egreso
  			$add_where.=" and ingreso_egreso.id_tipo_egreso = $id_t_egreso"; 		
		if($id_t_cuenta!=-1)//filtrado por tipo_cuenta_egreso
  			$add_where.=" and ingreso_egreso.numero_cuenta = $id_t_cuenta"; 		
    }


	if ($fechas==1) //filtrado por fechas
		if ($desde && $hasta) {
 	  		$add_where.=" and caja.fecha >= '".fecha_db($desde)."' and caja.fecha <= '".fecha_db($hasta)."'";
 		}
 	
	if ($montos==1) //filtrado por montos
		if ($desde_m >=0 && $hasta_m >=0) {
			if ($cmd=="caja")
 	  			$add_where.=" and caja.saldo_total >= '$desde_m' and caja.saldo_total <= '$hasta_m'";
			else
 	  			$add_where.=" and ingreso_egreso.monto >= '$desde_m' and ingreso_egreso.monto <= '$hasta_m'";
 	}
 	
 	if ($entidades==1)//filtrado por entidades sean proveedores en egresos o clientes en ingresos
 	{
		if ($entidad == -1) 
		{
			if($cmd=="ingreso")
				$add_where.=" and id_entidad in (Select id_entidad from entidad where nombre ilike '$letra%')";  
			elseif($cmd=="egreso")
				$add_where.=" and id_proveedor in (Select id_proveedor from proveedor where razon_social ilike '$letra%')";  
		} 
		else
		{
			if($cmd=="ingreso")
				$add_where.=" and id_entidad = $entidad";
			elseif($cmd=="egreso")
				$add_where.=" and id_proveedor = $entidad";
		}
 	}

if ($ie=="ingreso")
{
 $sql_tmp="select caja.fecha,ingreso_egreso.id_ingreso_egreso,ingreso_egreso.item,ingreso_egreso.monto,ingreso_egreso.comentarios,
           moneda.simbolo,caja.id_moneda,entidad.nombre as nombre,tipo_cuenta_ingreso.nombre as cuenta_ingreso
           from caja join ingreso_egreso using(id_caja) left join entidad using(id_entidad) join moneda using(id_moneda)
           left join caja.tipo_cuenta_ingreso using(id_cuenta_ingreso) ";
 $where_tmp="(caja.id_distrito=$distrito and id_tipo_egreso isnull $add_where)";
 $contar="select count(*) from ingreso_egreso join caja using(id_caja) where id_tipo_egreso isnull and caja.id_distrito=$distrito";
 $title="Cliente";
 $orden = array(
		"default_up" => "$orden_by",
		"default" => "$orden_default",
		"1" => "ingreso_egreso.id_ingreso_egreso",
		"2" => "caja.fecha",
		"3" => "ingreso_egreso.item",
		"4" => "ingreso_egreso.monto",
	);
 $filtro = array(
		"caja.fecha" => "Fecha",
		"entidad.nombre" => "Cliente",
		"ingreso_egreso.item" => "Item",
		"ingreso_egreso.id_ingreso_egreso" => "ID",
		"ingreso_egreso.monto" => "Monto",
		"ingreso_egreso.comentarios" => "Comentarios"
	);	
}
elseif ($ie=="egreso") 
{
 $sql_tmp="select caja.fecha,ingreso_egreso.id_ingreso_egreso,ingreso_egreso.item,ingreso_egreso.monto,ingreso_egreso.comentarios,
           moneda.simbolo,caja.id_moneda,proveedor.razon_social as nombre,tipo_cuenta.concepto,tipo_cuenta.plan
           from caja join ingreso_egreso using(id_caja) join proveedor using(id_proveedor) join moneda using(id_moneda) 
           left join  tipo_cuenta using(numero_cuenta)
           ";
 $where_tmp="(caja.id_distrito=$distrito and id_tipo_ingreso isnull $add_where)";
 $contar="select count(*) from ingreso_egreso join caja using(id_caja) where id_tipo_ingreso isnull and caja.id_distrito=$distrito";
 $title="Proveedor";
 $orden = array(
		"default_up" => "$orden_by",
		"default" => "$orden_default",
		"1" => "ingreso_egreso.id_ingreso_egreso",
		"2" => "caja.fecha",
		"3" => "ingreso_egreso.item",
		"4" => "ingreso_egreso.monto",
	);
 $filtro = array(
		"caja.fecha" => "Fecha",
		"proveedor.razon_social" => "Proveedor",
		"ingreso_egreso.item" => "Item",
		"ingreso_egreso.id_ingreso_egreso" => "ID",
		"ingreso_egreso.monto" => "Monto",
		"ingreso_egreso.comentarios" => "Comentarios"
	);	
}
elseif($ie=="caja")
{
 $sql_tmp="SELECT moneda.simbolo,caja.fecha,caja.saldo_total,caja.cerrada,caja.id_caja FROM caja join moneda using(id_moneda)";
 $where_tmp="(caja.id_distrito=$distrito $add_where)";
 $contar="select count(*) from caja where id_distrito=$distrito";
 $title="Proveedor";
 $orden = array(
		"default_up" => "$orden_by",
		"default" => "$orden_default",
		"1" => "caja.id_caja",
		"2" => "caja.fecha",
		"3" => "caja.cerrada"
	);
 $filtro = array(
		"caja.id_caja" => "ID",
		"caja.fecha" => "Fecha",
		"caja.saldo_total" => "Saldo Total",
		"caja.usuario" => "Usuario (solo si esta cerrada)"
	);
}

$sumas = array(
 		"moneda" => "id_moneda",
 		"campo" => "monto",
 		"mask" => array ("\$","U\$S")
);

if($keyword || $_POST['fechas'] || $_POST['select_moneda']!=-1 || $_POST['select_estado']!= -1 )
     $contar="buscar";

	ob_start();
	list($sql,$total_lic,$link_pagina,$up,$suma) = form_busqueda($sql_tmp,$orden,$filtro,array("distrito"=>$distrito),$where_tmp,$contar,$sumas);
	$resultado = sql($sql,"Error en busqueda: $sql");
	$form_busqueda=ob_get_contents();//para que se pueda imprimir cuando se llama para crear el HTML
	ob_clean();
	
	$xml_gen=new XMListGenerator();
	$xml_gen->busqueda->keyword=$keyword;
	$xml_gen->busqueda->campo_buscado=(isset($filtro[$filter])?$filtro[$filter]:"Todos los campos");
	if ($avanzada)
	{
		$xml_gen->busqueda->setFilter("Moneda",$oselect_moneda->options[$oselect_moneda->selectedIndex]->text);
		if ($cmd!="caja")
			$xml_gen->busqueda->setFilter("Tipo de Cuenta",$oselect_tcuenta->options[$oselect_tcuenta->selectedIndex]->text);
		else 
			$xml_gen->busqueda->setFilter("Estado",$oselect_estado->options[$oselect_estado->selectedIndex]->text);

		if ($cmd=="egreso")
	   	$xml_gen->busqueda->setFilter("Tipo de Egreso",$oselect_tegreso->options[$oselect_tegreso->selectedIndex]->text);
	   elseif($cmd=="ingreso")
	    	$xml_gen->busqueda->setFilter("Tipo de Ingreso",$oselect_tingreso->options[$oselect_tingreso->selectedIndex]->text);
		if ($fechas)
		{
		 	$fecha_desde=$desde or $fecha_desde="01/03/1995";
			$fecha_hasta=$hasta or $fecha_hasta=date("d/m/Y");	
	  		$xml_gen->busqueda->setFilter("Entre Fechas","desde $fecha_desde hasta $fecha_hasta");
		}
	  	if ($montos)
	  		$xml_gen->busqueda->setFilter("Entre Montos","desde $desde_m hasta $hasta_m");
	  	if ($entidades)
	  	{
	  		$entidad_nombre=$oselect_entidad->selectedIndex!=0?$oselect_entidad->options[$oselect_entidad->selectedIndex]->text:"Comienza con '".strtoupper($letra)."'";
			$xml_gen->busqueda->setFilter($ie!="egreso"?"Por Cliente":"Por proveedor",$entidad_nombre);
	  	}
	}
  $r=$xml_gen->titulos->addRow();
	if ($ie!="caja")
	{
	  $r->addCol("ID",array(width=>'5px'))->data[0]->link=encode_link($_SERVER['PHP_SELF'],array("sort"=>"1","up"=>$up,"distrito"=>$distrito));
	  $r->addCol("Fecha",array(width=>'9.5px'))->data[0]->link=encode_link($_SERVER['PHP_SELF'],array("sort"=>"2","up"=>$up,"distrito"=>$distrito));
	  $r->addCol("Item",array(width=>'70px'))->data[0]->link=encode_link($_SERVER['PHP_SELF'],array("sort"=>"3","up"=>$up,"distrito"=>$distrito));
	  $r->addCol("Monto",array(width=>'14px'))->data[0]->link=encode_link($_SERVER['PHP_SELF'],array("sort"=>"4","up"=>$up,"distrito"=>$distrito));
	  $r->addCol($ie=='ingreso'?"Cliente":"Proveedor",array(width=>'36.85px'));
      $r->addCol("Cuenta",array(width=>'36.85px'));
	  
	}
	else
	{
	  $r->addCol("ID")->data[0]->link=encode_link($_SERVER['PHP_SELF'],array("sort"=>"1","up"=>$up,"distrito"=>$distrito));
	  $r->addCol("Fecha")->data[0]->link=encode_link($_SERVER['PHP_SELF'],array("sort"=>"2","up"=>$up,"distrito"=>$distrito));
	  $r->addCol("Saldo Actual")->data[0]->link=encode_link($_SERVER['PHP_SELF'],array("sort"=>"3","up"=>$up,"distrito"=>$distrito));
	  $r->addCol("Estado");
	}
while (!$resultado->EOF)
{
	//seteo los links
  if($ie!='caja')//si no es caja (es ingreso o egreso)
  {
  	if($resultado->fields['comentarios']=="")
    	$comentario="Observaciones: no tiene";
   	else
    	$comentario="Observaciones:".$resultado->fields['comentarios'];
   	$link=encode_link("ingresos_egresos.php",array("id_ingreso_egreso"=> $resultado->fields["id_ingreso_egreso"],"pagina"=>$ie,"distrito"=>$distrito));
		$comentario =  ereg_replace("'|\"","",$comentario);
  }
  else
   $link=encode_link("caja_diaria.php",array("id_caja"=> $resultado->fields["id_caja"],"pagina"=>"listado","distrito"=>$distrito));

 
  if($ie!='caja') 
  {
  	$r=$xml_gen->lista->addRow($resultado->fields["id_ingreso_egreso"],$link,$comentario);
  	$r->addCol($resultado->fields["id_ingreso_egreso"]);
  	$r->addCol(fecha($resultado->fields["fecha"]));
  	$r->addCol($resultado->fields["item"]);
  	$c=$r->addCol($resultado->fields["monto"]);
  	$c->data[0]->datatype="money";
  	$c->data[0]->simbol=$resultado->fields["simbolo"];
  	$r->addCol(ereg_replace("\&","&amp;",$resultado->fields["nombre"]));
  	if($ie=="egreso")
  	 $r->addCol($resultado->fields["concepto"]."[".$resultado->fields["plan"]."]");
  	elseif ($ie=="ingreso") 
  	 $r->addCol($resultado->fields["cuenta_ingreso"]);
  }
  else 
  {
  	$r=$xml_gen->lista->addRow($resultado->fields["id_caja"],$link,$comentario);
  	$r->addCol($resultado->fields["id_caja"]);
  	$r->addCol(fecha($resultado->fields["fecha"]));
  	$c=$r->addCol($resultado->fields["saldo_total"]);
  	$c->data[0]->datatype="money";
  	$c->data[0]->simbol=$resultado->fields["simbolo"];
  	$r->addCol($resultado->fields["cerrada"]?"Cerrada":"Abierta");
   }
    $resultado->movenext();
}   //del while
  $xml_gen->encabezado->recordcount=$total_lic;
  $xml_gen->encabezado->suma=($ie!='caja')?"Suma: ".$suma:"";
  $xml_gen->encabezado->link_pagina=$link_pagina;
  $xml_gen->encabezado->tipo_registros=$ie."s";

	$XML_DATA=$xml_gen->saveXML(); 
	//echo $XML_DATA; die;
	
?>