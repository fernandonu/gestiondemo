<?php
/*
Autor: charly
Modificado por:
$Author: mari $
$Revision: 1.116 $
$Date: 2007/01/05 20:11:31 $
*/
include "head.php";

$coradir_bs_as=$_GET["coradir_bs_as"] or $coradir_bs_as=$parametros["coradir_bs_as"] or $coradir_bs_as=$_POST["coradir_bs_as"];

$colores=array();
$colores[0]=array("color"=>"#FFFFC0","texto"=>"caso enviado(Col. Nro. caso)");
$colores[1]=array("color"=>"#00AA00","texto"=>"archivos subidos (Col. Atendido por)");
$colores[2]=array("color"=>"#FF8080","texto"=>"tiene orden de compra"); //toda la fila
$colores[3]=array("color"=>"#C1E9AA","texto"=>"Tiene Orden de Pago asociada (Col.Tiempo Transcurrido)"); //Tiempo Transcurrido
//$colores[3]=array("color"=>"#00ff00","texto"=>"Tiene Orden de Pago asociada"); //Tiempo Transcurrido
$colores[4]=array("color"=>"#FFC0C0","texto"=>"Tiene Respuestos (Col. clientes)"); //Col clientes
$colores[5]=array("color"=>"yellow", "texto"=>"Caso con visita pendiente"); //boton AV
$colores[6]=array("color"=>"#66FFFF","texto"=>"Ordenes de Compra Asociadas (Col. Fecha Inicio)"); //col. fechainicio
$colores[7]=array("color"=>"#66FFFF","texto"=>"Asociado a PM (Col. Nro Serie)");
$colores[8]=array("color"=>"#D0A5EE","texto"=>"Tiene Factura (Col. Contacto)"); //Col. Contacto
$colores[9]=array("color"=>"#30C717","texto"=>"Se pagó la Orden de Pago (Col.Tiempo Transcurrido)"); //Tiempo Transcurrido
//#FFCC99 #999966
$extra_session=array("nro_orden"=>"","id_muleto"=>"","modo"=>"","pag"=>"","check"=>-1);

variables_form_busqueda("caso_admin",$extra_session);

$backto=$parametros['backto'] or $backto=$_POST['backto'];//variable para volver a otra pagina como ordcompra, remitos o facturas
if ($coradir_bs_as=="si"){
   phpss_svars_set("_ses_global_coradir_bs_as", "si");
}
if ($coradir_bs_as=="no"){
phpss_svars_set("_ses_global_coradir_bs_as", "no");
}

if ($backto && $_ses_global_backto!=$backto)
{
	phpss_svars_set("_ses_global_backto", $backto);
	phpss_svars_set("_ses_global_nro_orden_asociada", $parametros['nro_orden']);
	phpss_svars_set("_ses_global_id_muleto", $parametros['id_muleto']);
	phpss_svars_set("_ses_global_modo", $parametros['modo']);
	phpss_svars_set("_ses_global_pag", $parametros['pag']);
	phpss_svars_set("_ses_global_extra", $parametros['_ses_global_extra']);
}
//print_r($parametros);


if ($_POST["sync"]=="Sincronizar") {
	$db_name = 'coradir';
	//$db_host = 'devel.local';
	$dbtemp = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
	$dbtemp->Connect($db_host, $db_user, $db_password, $db_name);
	$sql="select ccdr.idcaso,ccdr.idestuser,ccdr.idate,ccdr.id_dependencia,ccdr.nrocaso,ccdr.fechainicio,ccdr.nserie,
		ccdr.deperfecto,ccdr.fechacierre,entidad.id_entidad,dependencias.dependencia,dependencias.direccion,
		dependencias.lugar,dependencias.cp,dependencias.contacto,dependencias.telefono,
		dependencias.mail,dependencias.id_distrito,ccdr.sync,
		entidad.id_distrito as ent_distrito,entidad.nombre as ent_nombre,entidad.direccion as ent_direccion,
		entidad.localidad as ent_localidad,entidad.codigo_postal,entidad.cuit,entidad.contacto as ent_contacto,
		entidad.telefono as ent_telefono,entidad.fax as ent_fax,entidad.mail as ent_mail,
		cas_ate.id_distrito as cas_distrito,cas_ate.nombre,cas_ate.tel,cas_ate.direccion as cas_direccion,cas_ate.mail as cas_mail,cas_ate.contacto as cas_contacto,cas_ate.ciudad,cas_ate.cp as cas_cp,cas_ate.mic,cas_ate.icq,cas_ate.msn
		from casos.casos_cdr ccdr left join casos.dependencias using (id_dependencia)
		left join casos.cas_ate using (idate)
		left join licitaciones.entidad using (id_entidad)
		where ccdr.sync is not null and ccdr.sync<>0";
	$resultado=sql($sql) or fin_pagina();
	//print_r($resultado->fields);
	$dbtemp->BeginTrans();
	$error = NULL;
	while ($fila=$resultado->FetchRow()) {
		//echo "entro";
		
		if ($fila["sync"]==2 or $fila["sync"]==1) {
			// Agregar o Modificar entidad
			$q="select entidad.id_entidad from general.entidad where entidad.id_entidad=".$fila["id_entidad"];
			$res=$dbtemp->execute($q) or $error[]=1;
			if (is_object($res) && $res->RecordCount() <= 0) {
				$q_entidad="insert into general.entidad (id_entidad,id_distrito,nombre,direccion,localidad,codigo_postal,cuil,contacto,telefono,fax,mail) VALUES
				(".$fila["id_entidad"].",".$fila["ent_distrito"].",'".$fila["ent_nombre"]."','".$fila["ent_direccion"]."','".$fila["ent_localidad"]."'
				,'".$fila["codigo_postal"]."','".$fila["cuit"]."','".$fila["ent_contacto"]."','".$fila["ent_telefono"]."','".$fila["ent_fax"]."','".$fila["ent_mail"]."')";
				$dbtemp->execute($q_entidad) or $error[]=$dbtemp->errormsg()." - $q_entidad";
			}
			// Agregar o Modificar Dependencia
			$q="select dependencia.id_dependencia from general.dependencia where dependencia.id_dependencia=".$fila["id_dependencia"];
			$res=$dbtemp->execute($q) or $error[]=1;
			if (is_object($res) && $res->RecordCount() <= 0) {
				if ($fila["id_distrito"]) $distrito=$fila["id_distrito"]; else $distrito=2;
				$q_cliente="INSERT INTO general.dependencia (id_dependencia,id_distrito,id_entidad,nombre,direccion,localidad,codigo_postal,contacto,telefono,mail) VALUES
				(".$fila["id_dependencia"].",$distrito,'".$fila['id_entidad']."','".$fila['dependencia']."','".$fila['direccion']."','".$fila['lugar']."',
				'".$fila['cp']."','".$fila['contacto']."','".$fila['telefono']."','".$fila['mail']."')";
			}
			else {
				if ($fila["id_distrito"]) $distrito=$fila["id_distrito"]; else $distrito=2;
				$q_cliente="UPDATE general.dependencia SET
					id_entidad=".$fila['id_entidad'].",
					nombre='".$fila['dependencia']."',
					direccion='".$fila['direccion']."',
					localidad='".$fila['lugar']."',
					codigo_postal='".$fila['cp']."',
					id_distrito=$distrito,
					contacto='".$fila['contacto']."',
					telefono='".$fila['telefono']."',
					mail='".$fila['mail']."'
					where id_dependencia=".$fila['id_dependencia'];
			}
			$dbtemp->execute($q_cliente) or $error[]=$dbtemp->errormsg()." - $q_cliente";
			// Agregar o modificar c.a.s.
			$q="select cas.id_cas from caso.cas where cas.id_cas=".$fila["idate"];
			$res=$dbtemp->execute($q) or $error[]=$dbtemp->errormsg()." - $q";
			if (is_object($res) && $res->RecordCount() <= 0) {
				$q_cas="insert into caso.cas(id_cas,id_distrito,nombre,telefono,direccion,mail,contacto,localidad,codigo_postal,mic,icq,msn) values
				(".$fila["idate"].",".$fila["cas_distrito"].",'".$fila["nombre"]."','".$fila["tel"]."','".$fila["cas_direccion"]."'
				,'".$fila["cas_mail"]."','".$fila["cas_contacto"]."','".$fila["ciudad"]."','".$fila["cas_cp"]."','".$fila["mic"]."'
				,'".$fila["icq"]."','".$fila["msn"]."')";
				$dbtemp->execute($q_cas) or $error[]=$dbtemp->errormsg()." - $q_cas";
			}
			elseif (is_object($res) && $res->RecordCount() > 0) {
				$q_cas="UPDATE caso.cas SET id_distrito=".$fila["cas_distrito"].",
					nombre='".$fila["nombre"]."',
					telefono='".$fila["tel"]."',
					direccion='".$fila["cas_direccion"]."',
					mail='".$fila["cas_mail"]."',
					contacto='".$fila["cas_contacto"]."',
					localidad='".$fila["ciudad"]."',
					codigo_postal='".$fila["cas_cp"]."',
					mic='".$fila["mic"]."',
					icq='".$fila["icq"]."',
					msn='".$fila["msn"]."' where id_cas=".$fila["idate"];
					$dbtemp->execute($q_cas) or $error[]=$dbtemp->errormsg()." - $q_cas";
			}
			if ($fila["sync"]==1) {
				// Agregar caso
				if (fechaok(fecha($fila["fechainicio"]))) $fechainicio="'".$fila["fechainicio"]."'";
				else $fechainicio="NULL";
				if (fechaok(fecha($fila["fechacierre"]))) $fechacierre="'".$fila["fechacierre"]."'";
				else $fechacierre="NULL";
				$q_caso="insert into caso.caso_cdr(nro_caso,id_estado,id_cas,id_dependencia,fecha_inicio,fecha_cierre,nro_serie,deperfecto) values
		('".$fila["nrocaso"]."',".$fila["idestuser"].",".$fila["idate"].",".$fila["id_dependencia"].",$fechainicio,
		$fechacierre,'".$fila["nserie"]."','".$fila["deperfecto"]."')";
				$dbtemp->execute($q_caso) or $error[]=$dbtemp->errormsg()." - $q_caso";
			}
			elseif ($fila["sync"]==2) {
				// Modificar caso
				if (fechaok(fecha($fila["fechainicio"]))) $fechainicio="'".$fila["fechainicio"]."'";
				else $fechainicio="NULL";
				if (fechaok(fecha($fila["fechacierre"]))) $fechacierre="'".$fila["fechacierre"]."'";
				else $fechacierre="NULL";
				$q_caso="UPDATE caso.caso_cdr SET
					id_estado=".$fila["idestuser"].",
					id_cas=".$fila["idate"].",
					id_dependencia=".$fila["id_dependencia"].",
					nro_serie='".$fila["nserie"]."',
					deperfecto='".$fila["deperfecto"]."',
					fecha_inicio=$fechainicio,
					fecha_cierre=$fechacierre
					where nro_caso=".$fila["nrocaso"];
				$dbtemp->execute($q_caso) or $error[]=$dbtemp->errormsg()." - $q_caso";
			}
			// Agregar o modificar datos de las visitas
			$sql="select vc.id_visitas_casos,vc.id_tecnico_visita,vc.fecha_visita,
			vc.observaciones,t_v.idate,(t_v.nombre || ' ' || t_v.apellido) as nombre,t_v.telefono,t_v.email,
			t_v.direccion
			from casos.visitas_casos vc
			join casos.tecnicos_visitas t_v USING (id_tecnico_visita)
			where vc.idcaso=".$fila["idcaso"];
			$vis=sql($sql) or fin_pagina();
			while ($visita=$vis->fetchrow()) {
				// Agregar o modificar tecnico de la visita
				$sql="select id_tecnico from caso.tecnico where id_tecnico=".$visita["id_tecnico_visita"];
				$res=$dbtemp->execute($sql) or $error[]=$dbtemp->errormsg()." - $sql";
				if (is_object($res) && $res->RecordCount() <= 0) {
					$q_tec="insert into caso.tecnico (id_tecnico,id_cas,nombre,direccion,telefono,mail) values
					(".$visita["id_tecnico_visita"].",".$visita["idate"].",'".$visita["nombre"]."','".$visita["direccion"]."',
					'".$visita["telefono"]."','".$visita["email"]."')";
					$dbtemp->execute($q_tec) or $error[]=$dbtemp->errormsg()." - $q_tec";
				}
				else {
					$q_tec="UPDATE caso.tecnico SET id_cas=".$visita["idate"].",
					nombre='".$visita["nombre"]."',
					direccion='".$visita["direccion"]."',
					telefono='".$visita["telefono"]."',
					mail='".$visita["mail"]."' where id_tecnico=".$visita["id_tecnico_visita"];
					$dbtemp->execute($q_tec) or $error[]=$dbtemp->errormsg()." - $q_tec";
				}
				// Agregar o Modificar estados de la visita
				$q="select log_casos_visitas.id_estado_visita,log_casos_visitas.descripcion
					from casos.log_casos_visitas where log_casos_visitas.id_visitas_casos=".$visita["id_visitas_casos"]." order by fecha DESC limit 1 offset 0";
				$r=sql($q) or fin_pagina();
				if (is_object($r) && $r->recordcount() > 0) {
					// Agregar o modificar estado
					$id_estado=$r->fields["id_estado_visita"];
					$sql="select estado_visitas.id_estado_visita from casos.estado_visitas where estado_visitas.id_estado_visita=".$r->fields["id_estado_visita"];
					$res=$db->execute($sql) or fin_pagina();
					if (is_object($res) && $res->recordcount() <= 0) {
						$q_estado="insert into caso.estado_visita(id_estado_visita,descripcion) values
						(".$res->fields["id_estado_visita"].",'".$res->fields["descripcion"]."')";
						$dbtemp->execute($sql) or die($dbtemp->ErrorMsg());
					}
				}
				else {
					$id_estado="NULL";
				}
				// Agregar o modificar la visita
				$sql="select visita.id_visita from caso.visita where visita.id_visita=".$visita["id_visitas_casos"];
				$res=$dbtemp->execute($sql) or $error[]=$dbtemp->errormsg()." - $sql";
				if (is_object($res) && $res->RecordCount() <= 0) {
					$q_vis="insert into caso.visita (id_visita,nro_caso,id_tecnico,fecha,comentario,id_estado_visita) values
					(".$visita["id_visitas_casos"].",'".$fila["nrocaso"]."',".$visita["id_tecnico_visita"].",'".$visita["fecha_visita"]."'
					,'".$visita["observaciones"]."',$id_estado)";
					$dbtemp->execute($q_vis) or $error[]=$dbtemp->errormsg()." - $q_vis";
				}
				else {
					$q_vis="UPDATE caso.visita SET nro_caso='".$fila["nrocaso"]."',
					id_tecnico=".$visita["id_tecnico_visita"].",
					fecha='".$visita["fecha_visita"]."',
					comentario='".$visita["observaciones"]."',
					id_estado_visita=$id_estado where id_visita=".$visita["id_visitas_casos"];
					$dbtemp->execute($q_vis) or $error[]=$dbtemp->errormsg()." - $q_vis";
				}
			}
		}
		//print_r($error);
		if (!$error) {
			$sq[]="UPDATE casos_cdr SET sync=NULL where idcaso=".$fila["idcaso"];
		}
		else break;
	}
		//if ($fila["sync"]!=0) {
	//print_r($sq);
	if (!$error and is_array($sq)) {
		$dbtemp->committrans();
		sql($sq) or fin_pagina();
		aviso ("La sincronización se realizo con exito");
	}
	elseif (is_array($sq)) {
		error ("Se a cometido un error. Comuniquese con el administrador.");
		$dbtemp->RollBackTrans();
		//fin_pagina();
	}
	//}
//	fin_pagina();
}

//Valores del formulario de busqueda
if ($cmd == "") {
	$cmd="en_curso";
	$check=-1;
	$_ses_caso_admin["cmd"] = $cmd;
	$_ses_caso_admin["check"]=$check;
	//print_r($_ses_caso_admin);
	phpss_svars_set("_ses_caso_admin", $_ses_caso_admin);
}

if($_POST["form_busqueda"] && $_POST["check"]=="" && $check==1)
{ $check="";
  $_ses_caso_admin["check"]=$check;

  phpss_svars_set("_ses_caso_admin", $_ses_caso_admin);
}

if($check==1)
{
	$_ses_global_coradir_bs_as='no';
	$and_coradir_bs=" and idate!=3";
	$check=1;

}


$link=encode_link("caso_admin.php",array("backto"=>$backto,"coradir_bs_as"=>$_ses_global_coradir_bs_as));
echo "<form name='form1' action='$link' method='post'>";
echo "<input type=hidden name=sort value='$sort'>\n";
echo "<input type=hidden name=backto value='$backto'>\n";
echo "<input type=hidden name=coradir_bs_as value='si'>\n";
echo "<table width=99% border=0 cellspacing=5 cellpadding=5>\n";
echo "<tr><td colspan=6 align=center>\n";
if (!$sort) $sort="1";
//if (!$cmd) $cmd="pendiente";
echo "<input type=hidden name=cmd value='$cmd'>\n";
if (!$sort) $sort=3;
$datos_barra = array(
					array (
					  "descripcion" => "En Curso",
					  "cmd" => "en_curso",
                      "extra"=>array("coradir_bs_as"=>$_ses_global_coradir_bs_as,"check"=>$check),
						),

					array(
						"descripcion"	=> "Casos Pendientes",
						"cmd"			=> "pendiente",
                        "extra"=>array("coradir_bs_as"=>$_ses_global_coradir_bs_as,"check"=>$check),
						),
					array(
						"descripcion"	=> "Casos Finalizados",
						"cmd"			=> "final",
                        "extra"=>array("coradir_bs_as"=>$_ses_global_coradir_bs_as,"check"=>$check),
						),
					array(
						"descripcion"	=> "Todos",
						"cmd"			=> "todos" ,
                        "extra"=>array("coradir_bs_as"=>$_ses_global_coradir_bs_as,"check"=>$check),
						),

				 );

$orden = array(
"default" => "3",
"default_up" => 0,
"1" => "nrocaso",
"2" => "nombre_cas",
"3" => "fechainicio",
"4" => "organismo",
"5" => "contacto",
"6" => "nserie",
"7" => "tiempo",
);

$seleccion = array ("descrip" => " idcaso in (select idcaso from casos.casos_cdr left join casos.estadocdr using(idcaso) where descripcion ilike '%$keyword%')",
                    //"prov" => " idcaso in (select idcaso from casos.casos_cdr left join casos.repuestos_casos using(idcaso) left join general.proveedor using(id_proveedor) where proveedor.razon_social ILIKE '%$keyword%')",
					"tecnico" => " idcaso in (select idcaso from tecnico_responsable left join usuarios using (id_usuario) where apellido ilike '%$keyword%' or nombre ilike '%$keyword%')",
					"visitas" => " idcaso in (select idcaso from visitas where comentario ilike '%$keyword%')",
					"licitacion" => " idcaso in (select idcaso from casos_cdr join maquina on nserie=nro_serie join orden_de_produccion using (nro_orden) where id_licitacion ilike '%$keyword%' )",
					"provin" => "idcaso in (select idcaso from casos.casos_cdr
					 left join casos.dependencias using(id_dependencia)
					 left join licitaciones.distrito using(id_distrito)
					 where distrito.nombre ilike '%$keyword%' )"
					 );
$ignorar = array(0  => "descrip",
                 //1  => "prov",
				 2	=> "tecnico",
				 3	=> "visitas",
				 4 => "licitacion",
				 5 => "provin"
);

generar_barra_nav($datos_barra);
/*$filtro = array(
   "nrocaso"     => "Número de caso",
   "entidad.nombre"        => "Cliente",
   "nserie"      =>"Nro de Serie"
   );*/
if ($_ses_global_backto=="../ord_compra/ord_compra.php")
{
  //aca va a entrar cuando venga de ordenes de compra
	$filtro = array(
	"nombre_cas"        => "Atendido por",
	"nrocaso"     => "Número de caso",
	"organismo"        => "Cliente",
	"deperfecto"  => "Desperfecto",
	"descripcion"       => "Estado",
	"nserie"      =>"Nro de Serie",
	"nfactura"    =>"Nro de Factura",
	"dependencia" =>"Dependencia",
	"contacto" =>"Contacto",
	"telefono" =>"Telefono",
	"direccion" =>"Dirección Dependencia",
	"contacto" => "Contacto",
	"descrip" =>"Descripcion del Estado",
	//"prov" =>"Proveedor",
	"visitas" => "Visitas",
	"tecnico" => "Técnico Responsable",
	"licitacion" => "Licitacion"
	);
//??????????????????????????????????????????????????????????????????????????????????????????????????????
  $sql_tmp = "select * from(
    SELECT casos_cdr.idcaso,casos_cdr.nrocaso,casos_cdr.deperfecto,dependencias.telefono, cas_ate.idate,
    	cas_ate.nombre as nombre_cas,estadousuarios.descripcion,fila, casos_cdr.fechainicio ,entidad.nombre as organismo,
    	dependencias.dependencia, casos_cdr.fechacierre,casos_cdr.nserie,casos_cdr.idestuser, dependencias.direccion,
    	dependencias.contacto, casos_cdr.nfactura,(coalesce(fechacierre,CURRENT_DATE)-casos_cdr.fechainicio) as tiempo, cantidad_casos
    FROM casos.casos_cdr
      LEFT JOIN casos.cas_ate USING(idate)
      LEFT JOIN casos.estadousuarios USING(idestuser)
      LEFT JOIN casos.dependencias USING(id_dependencia)
      LEFT JOIN licitaciones.entidad USING(id_entidad)
      left join (select count(orden_de_compra.nrocaso) as cantidad_casos, orden_de_compra.nrocaso from compras.orden_de_compra group by orden_de_compra.nrocaso) as ncasos using (nrocaso)
     )as  p
     /*left  join(select count(repuestos_casos.id) as cantidad ,casos_cdr.idcaso from casos.casos_cdr
     	join casos.repuestos_casos using(idcaso) join (select fila.id_producto,orden_de_compra.nrocaso from compras.orden_de_compra join compras.fila using(nro_orden)
     ) as oc using (nrocaso,id_producto)
     group by casos_cdr.idcaso order by casos_cdr.idcaso)as q using (idcaso)*/";
//??????????????????????????????????????????????????????????????????????????????????????????????????????
    if ($cmd=="en_curso")
                $where_temp="p.idestuser<>7 and p.idestuser<>2";
    if ($cmd=="pendiente")
                 $where_temp="p.idestuser=7";
    if ($cmd=="final")
                 $where_temp="p.idestuser=2";
   }
   else
   {
   $filtro = array(
	"cas_ate.nombre"        => "Atendido por",
	"casos_cdr.nrocaso"     => "Número de caso",
	"entidad.nombre"        => "Cliente",
	"casos_cdr.deperfecto"  => "Desperfecto",
	"estadousuarios.descripcion"       => "Estado",
	"casos_cdr.nserie"      =>"Nro de Serie",
	"casos_cdr.nfactura"    =>"Nro de Factura",
	"dependencias.dependencia" =>"Dependencia",
	"dependencias.telefono" =>"Telefono",
	"dependencias.contacto" =>"Contacto",
	"dependencias.direccion" =>"Dirección Dependencia",
	"descrip" => "Descripcion del Estado",
	//"prov" => "Proveedor",
	"visitas" => "Visitas",
	"tecnico" => "Técnico Responsable",
	"licitacion" => "Licitacion",
	"provin" => "Provincia",
	"lugar" => "Lugar"
	);
  //aca va a entrar cuando no venga de ordenes de compra
  /*$sql_tmp = "SELECT casos_cdr.idcaso,casos_cdr.nrocaso,costofin, oc_caso_vinculado.cantidad_orden, cas_ate.idate,cas_ate.nombre as nombre_cas,fila,
    casos_cdr.fechainicio,entidad.nombre as organismo,entidad.id_entidad, casos_cdr.fechacierre,casos_cdr.nserie,casos_cdr.idestuser,
    dependencias.direccion,dependencias.contacto,dependencias.telefono,dependencias.dependencia, (coalesce(fechacierre,CURRENT_DATE)-casos_cdr.fechainicio) as tiempo ,repuesto.cantidad_repuestos
    FROM casos_cdr LEFT JOIN cas_ate USING(idate) LEFT JOIN estadousuarios USING(idestuser) LEFT JOIN dependencias USING(id_dependencia)
      LEFT JOIN entidad USING(id_entidad) LEFT JOIN
      (select count(idcaso) as  cantidad_repuestos,idcaso from repuestos_casos group by idcaso ) as repuesto using(idcaso)
      LEFT JOIN (select count(nro_orden)as cantidad_orden, nrocaso from compras.orden_de_compra where estado <> 'n'	group by nrocaso
			)as oc_caso_vinculado using (nrocaso)";*/

  //added by Archangel => todas las consultas en una
  $sql_tmp="SELECT casos_cdr.idcaso,casos_cdr.nrocaso,casos_cdr.costofin, oc_caso_vinculado.cantidad_orden, cas_ate.idate,cas_ate.nombre as nombre_cas,casos_cdr.fila,
    casos_cdr.fechainicio,entidad.nombre as organismo,entidad.id_entidad, casos_cdr.fechacierre,casos_cdr.nserie,casos_cdr.idestuser,
    dependencias.direccion,dependencias.contacto as contacto,dependencias.telefono,dependencias.dependencia,
		(coalesce(casos_cdr.fechacierre,CURRENT_DATE)-casos_cdr.fechainicio) as tiempo ,repuesto.cantidad_repuestos,
		ec.descripcion, cantidad_mail, cas_ate.contacto as cas_contacto, cantidad_archivos,visitas_pendientes,pm_cantidad,casos_cdr.pagado_orden,casos_cdr.nfactura,dependencias.lugar 
	FROM casos.casos_cdr
	left join (select count(idcaso) as pm_cantidad,idcaso from movimiento_material group by idcaso) as pm using(idcaso)
	LEFT JOIN casos.cas_ate USING(idate)
    LEFT JOIN casos.estadousuarios USING(idestuser)
    LEFT JOIN casos.dependencias USING(id_dependencia)
    LEFT JOIN licitaciones.entidad on dependencias.id_entidad=entidad.id_entidad
    LEFT JOIN (select count(idcaso) as  cantidad_repuestos,idcaso from casos.repuestos_casos group by idcaso ) as repuesto using(idcaso)
    LEFT JOIN (select count(nro_orden)as cantidad_orden, nrocaso from compras.orden_de_compra where estado <> 'n'
			group by nrocaso)as oc_caso_vinculado using (nrocaso)
		left join (select count(casos_cdr.nrocaso) as  cantidad_mail, casos_cdr.idcaso from casos.casos_cdr join casos.mail using(idcaso) group by casos_cdr.idcaso) as cantm using(idcaso)
		left join (select count(idcaso) as cantidad_archivos, idcaso
			from casos.archivos_casos group by idcaso) as carch using (idcaso)
		left join (select estadocdr.idcaso, licitaciones.unir_texto(estadocdr.fecha||': '||estadocdr.descripcion) as descripcion
			from casos.estadocdr
				left join (select max(fecha) as max_fecha, idcaso from casos.estadocdr group by idcaso) as maximo using (idcaso)
				where fecha=maximo.max_fecha
				group by estadocdr.idcaso) ec using (idcaso)
		left join casos.mail using (idcaso)
		left join casos.archivos_casos ac using (idcaso)
		left join (select count(id_visitas_casos) as visitas_pendientes, idcaso
			from casos.visitas_casos
			where (estado not ilike 'historial')
			group by idcaso
		)as vc using(idcaso)";

    if ($_ses_global_coradir_bs_as=="si")
             $and_coradir_bs=" and idate=3";



    if ($cmd=="en_curso")
                $where_temp="(casos_cdr.idestuser<>7 and casos_cdr.idestuser<>2) $and_coradir_bs";
    if ($cmd=="pendiente")
                 $where_temp="(casos_cdr.idestuser=7) $and_coradir_bs";
    if ($cmd=="final")
                 $where_temp="(casos_cdr.idestuser=2) $and_coradir_bs";
    if (($cmd=="todos")&&($check==1))
                 $where_temp="idate!=3";

		$where_temp.=" group by casos_cdr.idcaso,casos_cdr.nrocaso,casos_cdr.costofin, oc_caso_vinculado.cantidad_orden, cas_ate.idate,cas_ate.nombre,casos_cdr.fila,
      casos_cdr.fechainicio,entidad.nombre,entidad.id_entidad, casos_cdr.fechacierre,casos_cdr.nserie,casos_cdr.idestuser,
      dependencias.direccion,dependencias.contacto,dependencias.telefono,dependencias.lugar,dependencias.dependencia, cantidad_archivos,
			(coalesce(fechacierre,CURRENT_DATE)-casos_cdr.fechainicio),repuesto.cantidad_repuestos, cas_ate.contacto,
			ec.descripcion, visitas_pendientes, cantidad_mail, pm_cantidad,casos_cdr.pagado_orden,casos_cdr.nfactura ";
   }

   $link_temp = Array(
				   "sort" => $sort,
				   "up" => $up,
				   "filter" => $filter,
				   "keyword" => $keyword,
                   "backto"=> $backto,
				   );
//$q="select valor from dolar_general";
//$dolar=sql($q) or fin_pagina();
$link_temp=array("coradir_bs_as"=>$coradir_bs_as);
if($check==1)
{
	echo"<b>Ocultar BS.AS</b>";
	echo"<input type=checkbox name=check value=1 checked>";
}
else
{
	echo"<b>Ocultar BS.AS</b>";
	echo"<input type=checkbox name=check value=1> ";
}
list($sql,$total,$link_pagina,$up2) = form_busqueda($sql_tmp,$orden,$filtro,$link_temp,$where_temp,"buscar","",$ignorar,$seleccion);

$rs1 = sql($sql) or fin_pagina();
$rs1->MoveFirst();
//echo $sql;
//print_r($_ses_caso_admin);
echo "<input type='submit' name='form_busqueda' value='Buscar'>\n";
if (!$_ses_global_backto) {
	$q="select idcaso,sync from casos_cdr where sync=1 or sync=2";
	$r=sql($q) or fin_pagina;
	if ($r->recordcount()>=1)
	 $botoncolor="style='background-color: red;'";
	echo "<input type='submit' name='sync' $botoncolor value='Sincronizar'>\n";
}
if(permisos_check("inicio","permiso_boton_stock_stec_bs_as"))
{
 $link_stock_st_ba=encode_link("../stock/stock_st_ba.php",array());
?>
 <!--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-->
 <input type="button" name="stock_serv_tec" value="Stock ST BsAs" onclick="window.open('<?=$link_stock_st_ba?>')">
<?
}
$link_reporte=encode_link("gen_reporte_casos.php",array());
$link_informe=encode_link("informe_casos.php",array("cmd"=>"informe"));
$link_graficos=encode_link("caso_graficos.php",array());
?>
 <input type="button" name="reporte" value="Reporte" onclick="window.open('<?=$link_reporte?>')">
 <input type="button" name="reporte" value="Informe" title='Informe' onclick="window.open('<?=$link_informe?>')">
 <input type="button" name="graficos" value="Graficos" title='Graficos de casos Abiertos / Cerrados' onclick="window.open('<?=$link_graficos?>','','width=1000, height=650,left=0,top=0,status=yes,resizable=yes,scrollbars=yes')">
<?
echo "<br><br>\n";


echo "<table class='bordes' width=98% cellspacing=2 align=\"center\">";
if ($_ses_global_coradir_bs_as=="si" && ($cmd=="en_curso" || $cmd=="pendiente")) $colspan=3;
else $colspan=2;
echo "<tr><td style='border-right: 0;' colspan=$colspan align=left id=ma>\n";
echo "<b>Total:</b> ".$total." Casos.</td>\n";
if ($cmd=="todos") $columnas=7;
else $columnas=6;
echo "<td style='border-left: 0;' colspan=$columnas align=right id=ma>$link_pagina</td></tr>\n";
$link_temp["page"]=$page;
$link_temp["up"]=$up2;
$link_temp["sort"]="1";
$link_temp["cmd"]=$cmd;
$link_temp["coradir_bs_as"]=$_ses_global_coradir_bs_as;

echo "<tr>";
if ($_ses_global_coradir_bs_as=="si" && ($cmd=="en_curso" || $cmd=="pendiente")) echo "<td id='mo'>&nbsp;</td>\n";
echo "<td align=right id=mo><a id=mo href='".encode_link("caso_admin.php",$link_temp)."'>Número de caso</a></td>\n";
$link_temp["sort"]=2;
if ($_ses_global_coradir_bs_as=="no")
  echo "<td align=right id=mo><a id=mo href='".encode_link("caso_admin.php",$link_temp)."'>Atendido por</a></td>\n";

$link_temp["sort"]=3;
echo "<td align=right width=60 id=mo><a id=mo href='".encode_link("caso_admin.php",$link_temp)."'>Fecha Inicio</a></td>\n";
$link_temp["sort"]=6;
echo "<td align=right id=mo><a id=mo href='".encode_link("caso_admin.php",$link_temp)."'>Cliente</a></td>\n";
$link_temp["sort"]=5;
echo "<td align=right id=mo><a id=mo href='".encode_link("caso_admin.php",$link_temp)."'>Contacto</a></td>\n";
$link_temp["sort"]=4;
echo "<td align=right id=mo><a id=mo href='".encode_link("caso_admin.php",$link_temp)."'>Nro Serie</a></td>\n";
$link_temp["sort"]=7;
echo "<td id=mo><a id=mo href='".encode_link("caso_admin.php",$link_temp)."'>Tiempo Transcurrido</a></td>\n";
//echo "<td id=mo>Costo</td>\n";
if ($cmd=="todos")
	echo "<td id=mo>Estado</td>\n";
echo "</tr>\n";
$i=0;

if ( (($_ses_user['login']=="ferni") or ($_ses_user['name']=="Juan Manuel Baretto")) and ($cmd=="todos") ) {
?>
<input type="button" style="width:80pt" name="Reportes" value="Reportes" onclick="window.open('caso_admin_listado.php')">

<?}//del if que muestra o no el boton


while (!$rs1->EOF) {
		//si viene de la pagina de ordcompra

	if ($_ses_global_backto)
	    {
			$ref = encode_link($_ses_global_backto,array("id_caso"=>$rs1->fields["idcaso"],"caso"=>$rs1->fields["nrocaso"],"nro_orden"=>$_ses_global_nro_orden_asociada,"licitacion"=>"","pagina"=>$_ses_global_pag,"id_muleto"=>$_ses_global_id_muleto,"modo"=>$_ses_global_modo,"_ses_global_extra"=>$_ses_global_extra));
        }
		else
			$ref = encode_link("caso_estados.php",Array("id"=>$rs1->fields["idcaso"],"id_entidad"=>$rs1->fields['id_entidad']));
	//$es=0;
	/*$sql="select fecha,descripcion from estadocdr where idcaso='".$rs1->fields["idcaso"]."' order by idestcdr DESC limit 1 offset 0";
	$result=sql($sql)or fin_pagina();
    $descripcion=$result->fields["descripcion"];
    $descripcion=str_replace("'"," ",$descripcion);
    $descripcion=str_replace("\""," ",$descripcion);
	$title="Fecha: ".fecha($result->fields["fecha"])."\nEstado: $descripcion";*/
	$title="Descripción: ".$rs1->fields["descripcion"];
        //verifico si el caso tiene una orden de compra para poder
        //diferenciarse bien y no hacer dos veces la misma orden
        if ($_ses_global_backto){
                           /*$sql="select count(nrocaso) as cantidad_casos from compras.orden_de_compra
                                 where nrocaso=".$rs1->fields["nrocaso"];
                           $result_caso=sql($sql) or fin_pagina();
                           $cantidad=$result_caso->fields["cantidad_casos"];*/
                           $cantidad=$rs1->fields["cantidad_casos"];
                           }
                         
        if ($cantidad>0)
                     //tr_tag("","title=\"$title\"",$colores[2]["color"]);
                     //tr_tag($ref,"title=\"$title\"",$colores[2]["color"]);
                     echo "<tr ".atrib_tr($colores[2]["color"])." title='$title'>";
         else
                       echo "<tr ".atrib_tr()." title='$title'>";
	                  //tr_tag("","title=\"$title\"");
	                  //tr_tag($ref,"title=\"$title\"");

        //me fijo si se enviaron mail el caso
        $bgcolor="";
        /*$sql="select count(nrocaso) as  cantidad_mail from casos_cdr join mail using(idcaso) where nrocaso=".$rs1->fields["nrocaso"];
        $result_caso=sql($sql) or fin_pagina();
        $cantidad_mail=$result_caso->fields["cantidad_mail"];*/
        $cantidad_mail=$rs1->fields["cantidad_mail"];
        if ($cantidad_mail>0 && !$_ses_global_backto) $bgcolor="bgcolor='".$colores[0]["color"]."'";
        ///////////////////BROGGI/////////////////////
        $nro_caso=$rs1->fields["nrocaso"];
        $id=$rs1->fields['idcaso'];
        $link_d=encode_link('asignar_visitas.php',array("id_caso"=>$id,"nro_caso"=>$nro_caso,"viene"=>"listado"));
        $onclick_d="window.open('$link_d','','');";
        /////////////////////////// Gabriel ///////////////////////////////
        /*$visitas_pendientes="select id_visitas_casos,id_tecnico_visita,vc.direccion, contacto,vc.telefono,fecha_visita, observaciones,cant_modulos,nombre,apellido,estado
			    from casos.visitas_casos vc join casos.tecnicos_visitas tv  using (id_tecnico_visita)
      		where idcaso=$id  order by fecha_visita desc";
        $resultado_visitas_pendientes=sql($visitas_pendientes, "No se pudo obtener la lista de visitas del caso (L463)") or fin_pagina();
        while ((!$resultado_visitas_pendientes->EOF)&&($resultado_visitas_pendientes->fields["estado"]=="Historial"))	$resultado_visitas_pendientes->moveNext();
        if (!$resultado_visitas_pendientes->EOF) $color_boton="style='background.color:yellow'";
        else $color_boton="";*/
        if (($rs1->fields["visitas_pendientes"])&&(intval($rs1->fields["visitas_pendientes"])>0))
        	$color_boton="style='background.color:yellow'";
        else $color_boton="";
       	///////////////////////////////////////////////////////////////////
        if ($_ses_global_coradir_bs_as=="si" && ($cmd=="en_curso" || $cmd=="pendiente")) echo "<td align='center'><input type='button' $color_boton name='asignar_visita' value='AV' onclick=\"$onclick_d\"></td>";
        //////////////////////////////////////////
        echo "<a href=\"$ref\"><td align=center style='font-size: 9pt;' $bgcolor>".$rs1->fields["nrocaso"]."</td>\n";

        //me fijo si subieron archivos
        $bgcolor="";
        /*$sql="select count(idcaso) as  cantidad_archivos from archivos_casos where  idcaso=".$rs1->fields["idcaso"];
        $result_caso=sql($sql) or fin_pagina();
        $cantidad_archivos=$result_caso->fields["cantidad_archivos"];*/
        $cantidad_archivos=$rs1->fields["cantidad_archivos"];
        if ($cantidad_archivos>0 && !$_ses_global_backto) $bgcolor="bgcolor='".$colores[1]["color"]."'";
        else  $bgcolor="";
       	if ($_ses_global_coradir_bs_as=="no")
	   echo "<td align=left style='font-size: 9pt;' $bgcolor>&nbsp;".$rs1->fields["nombre_cas"]."</td>\n";

	   /*************  verifica que tenga o no orden de compra asociado ******************/
	   if ($rs1->fields["cantidad_orden"]>0) $bgcolor="bgcolor='".$colores[6]["color"]."'";
                                                    else  $bgcolor="";
	   echo "<td align=left width=80 style='font-size: 9pt;' $bgcolor>&nbsp;".Fecha($rs1->fields["fechainicio"])."</td>\n";

       if ($rs1->fields["cantidad_repuestos"]>0 && !$_ses_global_backto) $bgcolor="bgcolor='".$colores[4]["color"]."'";
                                                           else
                                                           $bgcolor="";
      echo "<td align=left style='font-size: 9pt;' $bgcolor>&nbsp;".$rs1->fields["organismo"] ."</td>\n";
    
      if ($rs1->fields["nfactura"] != "" && !$_ses_global_backto) $bgcolor="bgcolor='".$colores[8]["color"]."'";
                                                    else  $bgcolor="";
      echo "<td align=left style='font-size: 9pt;' $bgcolor >&nbsp;".$rs1->fields["contacto"]."</td>\n";
      // color para pedido material
	  if ($rs1->fields["pm_cantidad"])
		$pm_color="bgcolor='".$colores[7]["color"]."'";
	  else
		$pm_color="";
	  echo "<td align=left style='font-size: 9pt;' $pm_color>&nbsp;".$rs1->fields["nserie"]."</td>\n";
	  // Para sacar los costos del caso
	  /*$sql="select distinct(fila.cantidad),id_moneda,fila.precio_unitario from repuestos_casos
		    inner join fila using(id_producto)
		    inner join orden_de_compra using(nro_orden)
		    where orden_de_compra.estado<>'n' and idcaso=".$rs1->fields["idcaso"]." and nrocaso=".$rs1->fields["nrocaso"];
	  $orden=sql($sql) or fin_pagina();
	  $precio=0;
	  $t=0;
	while ($fila=$orden->fetchrow()) {
		//print_r($fila);
		$precio=$fila["cantidad"] * $fila["precio_unitario"];
		$t=$t+$precio;
		if ($orden->fields["id_moneda"]==2)
			$t=$t * $dolar->fields["valor"];
		//echo $fila["nrocaso"]." - ".$fila["nro_orden"]." - ".$fila["id_producto"]." - ".formato_money($precio)." = ".$fila["cantidad"]. " * ". formato_money($fila["precio_unitario"]). "<br>";
	}
	$costo=intval($rs1->fields["costofin"]);*/
	//else $costo=0;
	//echo $costo." - " . $rs1->fields["costofin"];
	//$total=$costo+$t;
	//$total=$t;
	// fin costos
    if ($rs1->fields["idestuser"]==1) $estado_caso="En Curso";
    if ($rs1->fields["idestuser"]==2) $estado_caso="Finalizado";
    if ($rs1->fields["idestuser"]==7) $estado_caso="Pendientes";
    
    if ($rs1->fields["pagado_orden"]==1) $col="bgcolor='".$colores[9]["color"]."'";   
      elseif ($rs1->fields["fila"]) $col="bgcolor='".$colores[3]['color']."'";
	else $col="";
	echo "<td align=center $col>".$rs1->fields["tiempo"]." Dias.</td>";

	//echo "<td align=center $col>$ ".formato_money($total)."</td>";
	if ($cmd=="todos") echo "<td align=center>$estado_caso</td>";
	echo "</a></tr>\n";

	$rs1->MoveNext();
	$i++;
}
echo "</table><br>\n";

//tabla con las referencias de los colores
echo "<table width='95%' border=0 align=center>\n";
echo "<tr>";
echo "<td colspan=6 align=center><br>\n";
echo "<table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>\n";
   echo "<tr>";
   echo "<td colspan=10 bordercolor='#FFFFFF'>";
   echo "<b>Colores de referencia</b></td>";
   echo "</tr>\n";
   echo "<tr>\n";
	$cont=0;
	foreach ($colores as $est => $arr) {
	if (!($cont % 3)) { echo "</tr><tr>"; }
	    if ($cont==0) $w="40%"; else $w="30%";
		echo "<td width=$w bordercolor='#FFFFFF'>";
                echo "<table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 wdith=100%><tr>";
		echo "<td width=15 bgcolor='".$colores[$est]["color"]."' bordercolor='#000000' height=15>&nbsp;</td>\n";
		echo "<td bordercolor='#FFFFFF'>".$colores[$est]["texto"]."</td>\n";
		echo "</tr></table></td>";
	   $cont++;
	}
  echo "</tr>\n";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "</table>";

$ocurrioerror=0;
echo $html_footer;
echo "<div align=left>".fin_pagina()."</div>";
?>