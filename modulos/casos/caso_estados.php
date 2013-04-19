<?
/*
$Author: fernando $
$Revision: 1.105 $
$Date: 2007/03/08 15:36:18 $
*/


require_once ("../../config.php");
//si viene el id de archivo -> borrarlo

/**********************************************
 ************** Datos del caso ****************
 **********************************************/
if ($_POST["ir"]=="Ir") {
    $lic=$_POST['id_li'];
	$link=encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$lic));
	header("Location:$link") or die("No se encontró la página destino");
}

if ($_POST["ordenes_asoc"]=="OC asociadas") {
	$lic=$_POST['id_li'];
	$link_1=encode_link("../ord_compra/ord_compra_listar.php",array("filtro"=>"todas","keyword"=>$lic,"filter"=>"o.id_licitacion","volver_lic"=>$lic, "cmd"=>"todas"));
	 header("Location:$link_1") or die("No se encontró la página destino");
}


if ($_POST["cmd1"]=="Finalizar") {
	// Valores del formulario
        /*
        $sql="Select fechacierre,firma FROM casos_cdr where idcaso=$id";
	$rs=$db->execute($sql) or die($db->errormsg());
	$firma=$rs->fields['firma'];
	$fecha=$rs->fields['fechacierre'];

	if (!$firma) $firma=$_POST['firma'];
	if (fechaok($fecha)) $fecha=$_POST['fechacierre'];
	if (!fechaok($fecha))
		 $fechadb="'".date("Y/m/d")."'";
	else
		$fechadb="'".fecha_db($fecha)."'";
        */
        $tipif_falla=$_POST['tipif_falla'];
        $firma=$_POST["firma"];
        $fechacierre=$_POST["fechacierre"];
        $fechainicio=$_POST["fechainicio"];
        ////////broggi///////////////////////////////
        $numero_mac=$_POST["numero_mac"];
        $id=$_POST['id'];
       
                
    if (!$numero_mac) {
		 error("Debe ingresar el número MAC para poder finalizarlo");

	}
	////////////////////////////////////////////
	if (!$firma) {
		 error("El cliente debe firmar el caso para poder finalizarlo");

	}
        if ($tipif_falla== -1) {
		 error("Debe especificar una falla");

	}

        if (!$fechacierre){
                    error("Falta fecha de cierre");
                          }
        if (!fechainiciio){
                    error("Falta fecha de inicio");
        }

	$fechacierre=fecha_db($_POST["fechacierre"]);
	$fechainicio=fecha_db($_POST["fechainicio"]);
	$res=compara_fechas($fechainicio,$fechacierre);

	if (compara_fechas($fechainicio,$fechacierre)==1)
	   {
		error("La Fecha de  inicio no puede ser mayor que la de cierre");
		}

       $sql="UPDATE casos_cdr SET firma='$firma',fechacierre='$fechacierre',id_falla=$tipif_falla,numero_mac=$numero_mac,idestuser=2,sync=2 where idcaso=$id";
	if (!$error) {
		sql($sql) or fin_pagina();
		Aviso("<font color=red>Se finalizó el caso exitosamente</font>");
	}
}//del if de actualizar


if ($_POST["cmd1"]=="Guardar") {

	// Valores del formulario
	while (list($key,$cont)=each($_POST)){
		$$key=$cont;
	}

	$db->BeginTrans();
	if ($modificardependencia==1 and $chdependencia){
		if (!$contacto)
			 $error.="El Organismo no tiene un contacto.<br>";
		if (!$dependencia)
			 $error.="Falta dependencia.<br>";
		if (!$direccion)
			 $error.="Falta el domicilio del cliente.<br>";
		if (!$telefono)
			 $error.="Olvido colocar el Teléfono del contacto.<br>";
		$sql="UPDATE dependencias SET ";
		$sql.="id_entidad=$cliente,
		dependencia='$dependencia',
		contacto='$contacto',
		direccion='$direccion',
		lugar='$lugar',
		cp='$cp',
		provincia='$provincia',
		telefono='$telefono',
		mail='$mail' WHERE id_dependencia=$chdependencia";
		if (!$error)
			sql($sql) or $error.=fin_pagina();
	}
	if (!$atendido)
		 error("Olvido colocar el caso.");
	if (!$chdependencia)
		 error("Debe seleccionar una dependencia.");
	if (!$serie)
		 error("Olvido colocar el Nro de Serie de la PC.");
	if (!fechaok($fechainicio))
		 error("Debe especificar la fecha en que se inicio el caso.");

        else    {
                $fechainicio_aux=fecha_db($fechainicio);
		$fechainicio="'".fecha_db($fechainicio)."'";
        }

	if (!fechaok($fechacierre))
		 $fechacierre="NULL";
	else    {
                 $fechacierre_aux=fecha_db($fechacierre);
		 $fechacierre="'".fecha_db($fechacierre)."'";
        }
	if (!fechaok($fechafactura))
		 $fechafactura="NULL";
	else
		 $fechafactura="'".fecha_db($fechafactura)."'";
	if (!$pagado) $pagado=0;

    if ($tipif_falla==-1) $tipif_falla='NULL';
    if ($origen_falla==-1) $origen_falla='NULL';

    if ($estadouser==2){
    if (compara_fechas($fechainicio_aux,$fechacierre_aux)==1)
           {
           error("La Fecha de  inicio no puede ser mayor que la de cierre");
            }
    }
	//echo $fechacierre;
	if ($estadouser==7){
		if (!$firma){
			error("Debe introducir la firma para pasar<br>
			el caso a pendiente.");
		}
    }    
	$qu="select sync from casos_cdr where idcaso=$id";
	$re=sql($qu) or fin_pagina();
	if ($re->fields["sync"]==1) $sync=1;
	else $sync=2;
	$sql="UPDATE casos_cdr SET ";
	$sql.="idate=$atendido,
		id_dependencia=$chdependencia,
		nserie='$serie',
		idestuser=$estadouser,
		fechainicio=$fechainicio,
		fechacierre=$fechacierre,
		fechafactura=$fechafactura,
		firma='$firma',
		nfactura='$nfactura',
		costofin='$precio',
        obspago='$obspago',
		pagado=$pagado,
		deperfecto='$defecto',
		sync=$sync,
	    id_falla=$tipif_falla,
	    id_origen_falla=$origen_falla";
	if ($estadouser==2) $sql.=",numero_mac='$numero_mac'";	    
	    $sql.=" WHERE idcaso = $id";

	if (!$error) {
		$query= "select idestuser from casos_cdr where idcaso = $id";
		$res=sql($query) or fin_pagina();
		if ($estadouser!=$res->fields["idestuser"]) {
			if ($estadouser==1) $des="En curso";
			if ($estadouser==7) $des="Pendiente";
			if ($estadouser==2) $des="Finalizado";
			$q="INSERT INTO log_casos (idcaso,fecha,descripcion,id_usuario)
				VALUES ($id,'".date ("Y-m-d H:i:s")."','$des',".$_ses_user["id"].")";
			sql($q) or fin_pagina();
			//echo $q;
		}
		else {
			$q="INSERT INTO log_casos (idcaso,fecha,descripcion,id_usuario)
				VALUES ($id,'".date ("Y-m-d H:i:s")."','Modificado',".$_ses_user["id"].")";
			sql($q) or fin_pagina();
		}
		sql($sql) or fin_pagina();
		Aviso ("Los datos se guardaron con exito");
		$sql= "select id_entidad from dependencias where id_dependencia=$chdependencia";
		$depen = sql($sql) or fin_pagina();
		$cliente = $depen->fields['id_entidad'];
		$db->CommitTrans();
	}
	else {
		error($error);
		$db->RollBackTrans();
	}
	
}
/*if ($_POST["cmd1"]=="Asignar Técnico") {
	//print_r($_POST);
	$idcaso=$_POST["id"];
	//$chdependencia=$_POST["chdependencia"];
	if ($_POST["tecnico_reponsable"]=="-1") 
		$error="Debe seleccionar un responsale.";
	if (!$error) {
		$sql="select id_tecresp,nombre,apellido from tecnico_responsable 
			join usuarios USING(id_usuario) where idcaso=$idcaso";
		$tmp=sql($sql) or fin_pagina();
		if ($tmp->recordcount()==1) 
			$query="UPDATE tecnico_responsable SET id_usuario=".$_POST["tecnico_reponsable"]."where idcaso=$idcaso";
		else
			$query="INSERT INTO tecnico_responsable (id_usuario,idcaso) VALUEs(".$_POST["tecnico_reponsable"].",$idcaso)";
		if (sql($query)) {
			$sql="select id_tecresp,nombre,apellido from tecnico_responsable 
				join usuarios USING(id_usuario) where idcaso=$idcaso";
			$tmp=sql($sql) or fin_pagina();
			$query="INSERT INTO log_casos (idcaso,fecha,descripcion,id_usuario)
				VALUES ($idcaso,'".date ("Y-m-d H:i:s")."','Asignado a ".$tmp->fields["nombre"]." ".$tmp->fields["apellido"]."',".$_ses_user["id"].")";
			sql($query) or fin_pagina();
		}
		else fin_pagina();
	}
	else 
		Error ($error);
}*/

$msg="&nbsp;";
if (($id_file=$parametros['id_file']) && ($filename=$parametros['filename']))
{
	$msg="El archivo '$filename' se eliminó correctamente";
    if (!is_file(UPLOADS_DIR."/archivos/$filename"))
        $error="No se encontro el archivo '$filename'";
    elseif (!unlink(UPLOADS_DIR."/archivos/$filename"))
         $error="No se encontro el archivo '$filename'";
         
    $sql="delete from archivos_casos where id=$id_file;";
    $sql.="delete from subir_archivos where id=$id_file;";
    sql($sql) or $error=$db->errormsg();
    if ($error)
        $msg=$error;
    $goto='#archivos';
    
}

echo $html_header;
cargar_calendario();

/* Script de java para modificacion de dependencia */
?>
<script language='javascript'>
if (<?= ($goto)?1:0; ?>)
 location.href='<?=$goto ?>';

var warchivos=false;//ventana para subir archivos
var ventana_cliente=0;
var ventana_maquina=0;
function control_datos()
{var no_error=1;
 if (document.all.atendido.value=="")
 {alert("Debe elegir lugar de atención");
  no_error=0;
  return false;
 }
 if(document.all.atendido.value.indexOf('"')!=-1)
{
  alert('No se puede ingresar "" en el campo atendido');
  no_error=0;
  return false;
 }

 if (document.all.fechainicio.value=="")
 {alert("Debe ingresar la fecha de inicio");
  no_error=0;
  return false;
 }
  if (document.all.cliente.value=="")
 {alert("No ha ingresado cliente");
  no_error=0;
  return false;
 }
 if(document.all.cliente.value.indexOf('"')!=-1)
{
  alert('No se puede ingresar "" en el campo cliente');
  no_error=0;
  return false;
 }

 if ((document.all.chdependencia.value=="") && (document.all.nuevocliente.checked==false))
 {alert("Debe elegir una dependencia");
  no_error=0;
  return false;
 }
  if (document.all.dependencia.value=="")
 {alert("Ingrese el nombre de la dependencia");
  no_error=0;
  return false;
 }
 if(document.all.dependencia.value.indexOf('"')!=-1)
{
  alert('No se puede ingresar " en el campo dependencia');
  no_error=0;
  return false;
 }

  if (document.all.direccion.value=="")
 {alert("No ingresó la dirección de la dependencia");
  no_error=0;
  return false;
 }
 if(document.all.direccion.value.indexOf('"')!=-1)
{
  alert('No se puede ingresar "" en el campo dirección');
  no_error=0;
  return false;
 }

 if (document.all.contacto.value=="")
 {alert("No ingresó el nombre del contacto");
  no_error=0;
  return false;
 }
 if(document.all.contacto.value.indexOf('"')!=-1)
{
  alert('No se puede ingresar "" en el campo contacto');
  no_error=0;
  return false;
 }


 if (document.all.telefono.value=="")
 {alert("No ingresó el teléfono del contacto");
  no_error=0;
  return false;
 }

 if(document.all.telefono.value.indexOf('"')!=-1)
{
  alert('No se puede ingresar "" en el campo teléfono');
  no_error=0;
  return false;
 }

  if (document.all.serie.value=="")
 {alert("No ingresó el nro de serie de la maquina");
  no_error=0;
  return false;
 }
 if(document.all.serie.value.indexOf('"')!=-1)
{
  alert('No se puede ingresar "" en el campo serie');
  no_error=0;
  return false;
 }


 if (document.all.defecto.value=="")
 {alert("No ingresó el defecto de la maquina");
  no_error=0;
  return false;
 }

 if(document.all.defecto.value.indexOf('"')!=-1)
{
  alert('No se puede ingresar "" en el campo defecto');
  no_error=0;
  return false;
 }
  if(document.all.mail.value.indexOf('"')!=-1)
{
  alert('No se puede ingresar "" en el campo mail');
  no_error=0;
  return false;
 }
 if(document.all.lugar.value.indexOf('"')!=-1)
{
  alert('No se puede ingresar "" en el campo lugar');
  no_error=0;
  return false;
 }

 if(document.all.cp.value.indexOf('"')!=-1)
{
  alert('No se puede ingresar "" en el campo codigo postal');
  no_error=0;
  return false;
 }
 if(document.all.comentario.value.indexOf('"')!=-1)
{
  alert('No se puede ingresar "" en el campo codigo postal');
  no_error=0;
  return false;
 }
 
 if(no_error==1)
  return true;
}//fin control datos

function control_falla(estado) {

if (estado==7 || estado==2 )
   {
   if (document.all.tipif_falla.value == -1) {
	alert ('Debe especificar la falla');
	return false;
   }
   if (document.all.origen_falla.value == -1) {
	alert ('Debe especificar el origen de la falla');
	return false;
   }
  }
if (estado==2)
   {
   if (document.all.fechacierre.value=="")
       {
       alert("Falta fecha de cierre");
       return false;
       }
   if (document.all.firma.value=="")
      {
      alert("Falta firma del cliente");
      return false;
      }
   if (document.all.numero_mac.value=="")
      {
      alert("Falta ingresar número MAC");
      return false;
      }   
   }
return true;
}


var dependencia=new Array();
dependencia['id_dependencia']=new Array();
dependencia['dependencia']=new Array();
dependencia['telefono']=new Array();
dependencia['cp']=new Array();
dependencia['mail']=new Array();
dependencia['id_distrito']=new Array();
dependencia['direccion']=new Array();
dependencia['lugar']=new Array();
dependencia['contacto']=new Array();
dependencia['comentario']=new Array();
<?
$id_cliente=$parametros["id_entidad"] or $id_cliente=$_POST["cliente"] or $id_cliente=$cliente;
//$id_cliente = $cliente;
  $sql="select id_dependencia,dependencia,telefono,cp,mail,id_distrito,direccion,lugar,contacto,comentario from dependencias where id_entidad=$id_cliente order by dependencia";
  $rs1_dep=sql($sql) or fin_pagina();
  $i=0;
  while(!$rs1_dep->EOF)
  {
?>
dependencia['id_dependencia'][<? echo $i; ?>]='<? echo $rs1_dep->fields['id_dependencia']; ?>';
dependencia['dependencia'][<? echo $i; ?>]='<? echo $rs1_dep->fields['dependencia']; ?>';
dependencia['telefono'][<? echo $i; ?>]='<? echo $rs1_dep->fields['telefono']; ?>';
dependencia['cp'][<? echo $i; ?>]='<? echo $rs1_dep->fields['cp']; ?>';
dependencia['mail'][<? echo $i; ?>]='<? echo $rs1_dep->fields['mail']; ?>';
dependencia['id_distrito'][<? echo $i; ?>]='<? echo $rs1_dep->fields['id_distrito']; ?>';
dependencia['direccion'][<? echo $i; ?>]='<? echo $rs1_dep->fields['direccion']; ?>';
dependencia['lugar'][<? echo $i; ?>]='<? echo $rs1_dep->fields['lugar']; ?>';
dependencia['contacto'][<? echo $i; ?>]='<? echo $rs1_dep->fields['contacto']; ?>';
dependencia['comentario'][<? echo $i; ?>]='<? echo $rs1_dep->fields['comentario']; ?>';
<?
  $i++;
  $rs1_dep->MoveNext();
  }
?>

function cargar_dependencia()
{var i,encontro;
   document.all.dependencia.value="";
   document.all.lugar.value="";
   document.all.telefono.value="";
   document.all.mail.value="";
   document.all.contacto.value="";
   document.all.cp.value="";
   document.all.comentario.value="";
   document.all.direccion.value="";
   document.all.provincia.selectedIndex=0;

if(document.all.chdependencia.options.length>0)
{i=0;
 encontro=0;

 while((i<dependencia['id_dependencia'].length) && (!encontro))
 {
  if (dependencia['id_dependencia'][i]==document.all.chdependencia.options[document.all.chdependencia.options.selectedIndex].value)
  {
   document.all.dependencia.value=dependencia['dependencia'][i];
   document.all.lugar.value=dependencia['lugar'][i];
   document.all.telefono.value=dependencia['telefono'][i];
   document.all.mail.value=dependencia['mail'][i];
   document.all.contacto.value=dependencia['contacto'][i];
   document.all.cp.value=dependencia['cp'][i];
   document.all.comentario.value=dependencia['comentario'][i];
   document.all.direccion.value=dependencia['direccion'][i];
   j=0;
   encontro2=0;
   while((j<document.all.provincia.options.length) && (!encontro2))
    {if (document.all.provincia.options[j].value==dependencia['id_distrito'][i])
	 {encontro2=1;
	  document.all.provincia.selectedIndex=j;
	 }
	 j++;
	}//fin while                                                           }
   encontro=1;
  }//fin if
  i++;
 }//fin while
}//fin if
} //fin de la funcion cargar_dependencias()

//cargo datos de ventana anterior
function cargar_cliente()
 {var i=0;
  //cargamos variable dependencia
  i=0;
  while(i<ventana_cliente.dependencias['id_dependencia'].length)
  {dependencia['id_dependencia'][i]=ventana_cliente.dependencias['id_dependencia'][i];
   dependencia['id_distrito'][i]=ventana_cliente.dependencias['id_distrito'][i];
   dependencia['mail'][i]=ventana_cliente.dependencias['mail'][i];
   dependencia['cp'][i]=ventana_cliente.dependencias['cp'][i];
   dependencia['telefono'][i]=ventana_cliente.dependencias['telefono'][i];
   dependencia['direccion'][i]=ventana_cliente.dependencias['direccion'][i];
   dependencia['lugar'][i]=ventana_cliente.dependencias['lugar'][i];
   dependencia['dependencia'][i]=ventana_cliente.dependencias['dependencia'][i];
   dependencia['contacto'][i]=ventana_cliente.dependencias['contacto'][i];
   dependencia['comentario'][i]=ventana_cliente.dependencias['comentario'][i];
   i++;
  }
  //dependencia=ventana_cliente.dependencias;
  document.all.cliente.value=ventana_cliente.document.all.select_cliente.options[ventana_cliente.document.all.select_cliente.selectedIndex].value;
  document.all.nombre_cliente.value=ventana_cliente.document.all.nombre.value;
  //document.all.direccion_cliente.value=ventana_cliente.document.all.direccion.value;
  //document.all.telefono_cliente.value=ventana_cliente.document.all.telefono.value;
  document.all.chdependencia.options.length=0;
  i=0;
  while(i<ventana_cliente.dependencias['dependencia'].length)
   {document.all.chdependencia.options.length++;
    document.all.chdependencia.options[i].text=ventana_cliente.dependencias['dependencia'][i];
    document.all.chdependencia.options[i].value=ventana_cliente.dependencias['id_dependencia'][i];
    i++;
   }
   cargar_dependencia();
 }//fin cargar

function cargar_maquina()
{var i=0;
 document.all.serie.value=ventana_maquina.document.all.nro_serie.value;
 document.all.cliente.value=ventana_maquina.document.all.id_entidad.value;
 document.all.nombre_cliente.value=ventana_maquina.document.all.entidad.value;
 //document.all.direccion_cliente.value=ventana_maquina.document.all.direccion.value;
 //document.all.telefono_cliente.value=ventana_maquina.document.all.telefono.value;
 //cargamos variable dependencia
 i=0;
 while(i<ventana_maquina.dependencias['id_dependencia'].length)
  {dependencia['id_dependencia'][i]=ventana_maquina.dependencias['id_dependencia'][i];
   dependencia['id_distrito'][i]=ventana_maquina.dependencias['id_distrito'][i];
   dependencia['mail'][i]=ventana_maquina.dependencias['mail'][i];
   dependencia['cp'][i]=ventana_maquina.dependencias['cp'][i];
   dependencia['telefono'][i]=ventana_maquina.dependencias['telefono'][i];
   dependencia['direccion'][i]=ventana_maquina.dependencias['direccion'][i];
   dependencia['lugar'][i]=ventana_maquina.dependencias['lugar'][i];
   dependencia['dependencia'][i]=ventana_maquina.dependencias['dependencia'][i];
   dependencia['contacto'][i]=ventana_maquina.dependencias['contacto'][i];
   dependencia['comentario'][i]=ventana_maquina.dependencias['comentario'][i];
   i++;
  }
 //cargamos select
 document.all.chdependencia.options.length=0;
 i=0;
 while(i<ventana_maquina.dependencias['dependencia'].length)
  {document.all.chdependencia.options.length++;
   document.all.chdependencia.options[i].text=ventana_maquina.dependencias['dependencia'][i];
   document.all.chdependencia.options[i].value=ventana_maquina.dependencias['id_dependencia'][i];
   i++;
  }
  cargar_dependencia();
}

</script>
<script>
function modidep() {
	if (document.all.modificardependencia.checked==1) {
		document.all.dependencia.disabled=false;
		document.all.contacto.disabled=false;
		document.all.mail.disabled=false;
		document.all.direccion.disabled=false;
		document.all.lugar.disabled=false;
		document.all.cp.disabled=false;
		document.all.provincia.disabled=false;
		document.all.telefono.disabled=false;
		document.all.comentario.disabled=false;
	}
	else {
		document.all.dependencia.disabled=true;
		document.all.contacto.disabled=true;
		document.all.mail.disabled=true;
		document.all.direccion.disabled=true;
		document.all.lugar.disabled=true;
		document.all.cp.disabled=true;
		document.all.provincia.disabled=true;
		document.all.telefono.disabled=true;
		document.all.comentario.disabled=true;
	}
}
</script>
<?
$id=$parametros["id"] or $id=$_POST["id"];
//Valores del formulario de busqueda
$up=$parametros["up"] or $up=$_POST["up"];
$sort=$parametros["sort"] or $sort=$_POST["sort"];
$page=$parametros["page"] or $page=$_POST["page"];
$keyword=$parametros["keyword"] or $keyword=$_POST["keyword"];
$filter=$parametros["filter"] or $filter=$_POST["filter"];




$sql="Select casos_cdr.* ,usuarios.nombre,usuarios.apellido,
	tecnico_responsable.id_usuario as tecresp
    from casos_cdr
    join usuarios using(id_usuario)
	left join tecnico_responsable USING (idcaso)
    where idcaso=$id";
$rs=sql($sql) or fin_pagina();
$estado_caso=$rs->fields['idestuser'];
$nombre_usuario=$rs->fields["apellido"].", ".$rs->fields["nombre"];
$tecresp=$rs->fields["tecresp"];
//$cliente=$_POST["cliente"];
if ($_POST["cmdcliente"]=="cambio") {
	$sql="select id_dependencia from dependencias where id_entidad=$cliente";
	$rs1=sql($sql);
	if ($rs1->recordcount()>0) $chdependencia=$rs1->fields["id_dependencia"];

}
else {
	$chdependencia=$_POST["chdependencia"];
	if (!$chdependencia) {
		$chdependencia=$rs->fields["id_dependencia"];
		$sql="select id_entidad from dependencias where id_dependencia=$chdependencia";
		$rs1=sql($sql);
		$cliente=$rs1->fields["id_entidad"];
	}
	else {
		$sql="select id_entidad from dependencias where id_dependencia=$chdependencia";
		$rs1=sql($sql);
		$cliente=$rs1->fields["id_entidad"];
	}
}
//echo "$cliente $chdependencia";
if (es_numero($chdependencia)) {
$sql="select dependencias.dependencia,dependencias.direccion,
   dependencias.lugar as localidad,dependencias.cp as cod_pos,
   dependencias.id_distrito as provincia,dependencias.contacto,dependencias.comentario,dependencias.telefono,
   dependencias.mail from dependencias left join entidad USING (id_entidad)
   where dependencias.id_dependencia=$chdependencia";
   $rs1=sql($sql) or fin_pagina();
   $dependencia=$rs1->fields["dependencia"];
   $direccion=$rs1->fields["direccion"];
   $lugar=$rs1->fields["localidad"];
   $cp=$rs1->fields["cod_pos"];
   $provincia=$rs1->fields["provincia"];
   $contacto=$rs1->fields["contacto"];
   $telefono=$rs1->fields["telefono"];
   $mail=$rs1->fields["mail"];
   $comentario=$rs1->fields["comentario"];
}
?>
<?
//chequeo los permisos
$sql="select estado from permisos_tecnicos join usuarios using(id_usuario) where login='".$_ses_user['login']."'";
$rs_permisos=sql($sql) or fin_pagina();
$estado=$rs_permisos->fields['estado'];
//Verifico los permisos del usuario
$nro_caso=$rs->fields["nrocaso"];

?>
<table width=99% align=center cellpadding=0 cellspacing=6 border=1 bordercolor="#111111">
<tr>
	<td id="ma_mg">
		Datos del caso Nro.: <? echo $rs->fields["nrocaso"]."<br>"; 
		$q="select nro_serie from casos.serie_robado where nro_serie ilike '%".$rs->fields["nserie"]."%'";
		$resul=sql($q,"Comprobando Nro. de Serie Robado") or fin_pagina();
		if ($resul && $resul->RecordCount() > 0) {
			echo "<font face='Arial' size=8 color='red'>El nro. de serie del caso a sido robado. Nro Serie: ".$rs->fields["nserie"]."</font>";
		}
		?>
	</td>
</tr>
<tr>
   <td> <b>Caso creado por:&nbsp;<font color=Red><?=$nombre_usuario?></b></font></td>
</tr>
<tr>
<td>
	<table width=100% align=center cellpadding=0 cellspacing=0 border=1> 
	<tr>
		<td colspan=3 id=ma align=center>Estados del caso</td>
	</tr>
	<tr>
		<td id=mo>Fecha</td>
		<td id=mo>Descripción</td>
		<td id=mo>Usuario</td>
	</tr>
<?
	$q="select usuarios.apellido,usuarios.nombre,descripcion,fecha from log_casos
		left join usuarios using (id_usuario) where idcaso=$id order by fecha DESC";
	$res=sql($q,"Error con la consulta de usuarios") or fin_pagina();
	while ($fila=$res->fetchrow()) {
		echo "<tr>\n";
		$fech=substr($fila["fecha"],0,10);
		$hora=substr($fila["fecha"],11,8);
		echo "<td align=center>".fecha($fech)." $hora</td>\n";
		echo "<td align=center>".$fila["descripcion"]."</td>\n";
		echo "<td align=center>".$fila["nombre"]." ".$fila["apellido"]."</td>\n";
		echo "<tr>\n";
	}
?>
	</table>
</td>
</tr>
<tr>
<td>
<?
switch ($estado_caso)
{
case "1":$estado_caso='En curso';break;
case "2":$estado_caso='Finalizado';break;
case "7":$estado_caso='Pendiente';break;
}
?>
<b>Este caso se encuentra en estado <font color="Blue"> <?echo $estado_caso; ?></b>
</td>
</tr>
<td align=center>
<form action='caso_estados.php' method='POST' name=modificar>
<input type=hidden name='id' value='<? echo $id; ?>'>
<input type=hidden name='cmdcliente' value='No'>
<table width=100% align=center>
 <tr>
  <td align=left id="mo_sf">
	Función a realizar
  </td>
 </tr>
 <tr>
  <td align=center>
	<table width=100% align=center>
	  <tr>
	   <td align=center>
	    <input type=button name="cmd"  style="width:70%" value="Informe" onclick="window.location='<? echo encode_link("caso_inf.php",Array("id"=>$id)); ?>';">
	   </td>
	  <? //deposito_origen=2 ==> Buenos Aires
	   $link_mov=encode_link("../mov_material/detalle_movimiento.php",Array("caso"=>$nro_caso,"nro_orden"=>"","licitacion"=>"","pagina"=>"","id_muleto"=>"","modo"=>"asociado_caso","pedido_material"=>1,"deposito_origen"=>2,"boton_cerrar"=>1));
	   $link_det=encode_link("../mov_material/listado_mov_material.php",array("keyword"=>$nro_caso,"filter"=>"nrocaso","cmd"=>"todos","volver_casos"=>1,"pedido_material"=>1));?>
	   <td width=33% align=center>
	    &nbsp; <input type=button name="pedido_material"  style="width:70%" value="Pedido material" onclick="window.open('<?echo $link_mov?>','','');">
	   <!--<input type=submit name="cmd1" style="width:70%" value=Finalizar>-->
	   </td>
	   <?$sql_mov="select id_movimiento_material from 
	           mov_material.movimiento_material where idcaso=$id";
	    $res_mov=sql($sql_mov,"$sql_mov") or fin_pagina();?>
	   
	   <td width=33% align=center>
	     <?if ($res_mov->RecordCount()>0) {?>
	       <input type=button name="ver_material"  style="width:70%" value="Ver Pedido material" onclick="window.open('<?echo $link_det?>','','');">
	       <?}?>
	      <!--<input type=button name="cmd" style="width:70%" value="Lista de Casos" onclick="window.location='<?// echo encode_link("caso_admin.php",Array("id"=>$id,"coradir_bs_as"=>$_ses_global_coradir_bs_as)); ?>';">-->
	    </td>
	   
	 </tr>
   </table>
  </td>
 </tr>
</table>
<table width=100% border="0" cellpadding="2" cellspacing="0" style="border-collapse: collapse; " bordercolor="#9A9A9A">
 <tr>
  <td id="mo_sf"> Modificar datos del CAS </td>
 </tr>
 <tr>
  <td>
   <table width=100%>
	<tr>
	 <td>
	  <a href="<? echo encode_link("caso_ate.php",ARRAY());?>" target="nuevo"><font face="Trebuchet MS" color="Black" size="2"><U>Atendido por</U></a><font color="#FF0000"><b> *
</b> </font>
      : </font>
	 </td>
	 <td>
	  <select name=atendido>
	  <?
	  $sql1="select idate,nombre from cas_ate where (activo=1) OR (idate=".$rs->fields['idate'].") order by nombre";
	  $rs1=sql($sql1) or fin_pagina();
	  while ($fila=$rs1->fetchrow()) {
		 echo "<option value='".$fila['idate']."' ";
		 if ($rs->fields["idate"]==$fila['idate']) echo "selected";
		 echo ">".$fila['nombre']."</option>\n";
		 }
	  ?>
	  </select>
	  <input type="button" name='ver_atendido' value="Ver" alt="Datos del C.A.S." onClick='window.open("<? echo encode_link('caso_organismo.php',array('idate'=>$rs->fields['idate'],'estado'=>'Modificar')); ?>","","left=40,top=80,width=700,height=500,resizable=1,scrollbars=1");'>
	 </td>
	 <td> <b>Fecha Inicio <font color="#FF0000">*</font> : <b> </td>
	 <td>
	  <input type=text name=fechainicio value='<? echo Fecha($rs->fields['fechainicio']); ?>'>
	  <?
	  echo link_calendario("fechainicio");
	  ?>
	 </td>
	</tr>
   </table>
  </td>
 </tr>
 <tr>
  <td id="mo_sf">
   Datos del cliente
  </td>
 </tr>
 <tr>
  <td width=100%>
   <table align=left width=100%>
   <tr>
	  <td align=left onclick="ventana_cliente=window.open('<?=encode_link('caso_elegir_cliente.php',array('onclickcargar'=>'window.opener.cargar_cliente();','onclicksalir'=>'window.close()'))?>','','left=40,top=80,width=700,height=350,resizable=1');" style="cursor:hand;" title="Haga click para ingresar cliente">
		<b><u>Cliente : </u></b>
	   </td>
	   <?
		$sql1="select id_entidad,nombre ";
		$sql1.=" from entidad where id_entidad=$cliente";
		$rs1=sql($sql1) or fin_pagina();
		$nombre_cliente=$rs1->fields["nombre"];
	   ?>
	   <td>
	   <input type=hidden name=cliente value="<?=$cliente?>">
	   <input type=text name=nombre_cliente value="<?=$nombre_cliente?>" readonly size="90" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">	   
	   </td>
	  </tr>
	   <?
		 $sql1="select dependencia from dependencias";
		 $sql1.=" where id_dependencia=$chdependencia";
		 $rs1=sql($sql1) or fin_pagina();
		 $nombre_dependencia=$rs1->fields["dependencia"];
	   ?>
		 <!--<input type=hidden name=chdependencia value="<?=$chdependencia?>">-->
  <tr>

  <td>
   <p align="right">  <font face="Trebuchet MS" size="2">
   Dependencia <font color="#FF0000">
   <b> * </b> </font>
   : </font>
   </td>
   <td colspan=3>
	<select name="chdependencia" onchange="cargar_dependencia();">
	<!--<option>&nbsp;</option>-->

<?
if ($cliente) {
$sql1="select id_dependencia,dependencia from dependencias where id_entidad=$cliente ";
$sql1.="order by dependencia";
$rs1=sql($sql1) or fin_pagina();

if ($rs1->recordcount()>0) {
	while ($fila=$rs1->fetchrow()) {
		echo "<option value='".$fila['id_dependencia']."' ";
		if ($chdependencia==$fila['id_dependencia'])
		echo " selected";
		echo ">".$fila['dependencia']."</option>\n";
	}
}
}
?>
   </select>
   <input type="button" name="modi_dependencia" value="Modificar" alt="Modificar datos de la dependencia" onClick="window.open('<? echo encode_link("dependencias.php",array("id_dependencia"=>$chdependencia,"id_caso"=>$id));?>','','left=40,top=80,width=700,height=350,resizable=1,scrollbars=1');">
   <!--<input type="button" name="mas_casos" value="Otros Casos" alt="Otros casos de la misma dependencia">-->
   </td>
</tr>
	 </table>
	</td>
 </tr>
 <tr>
	<td>
	<table width=100%>
	<tr>
	 <td> <b> Dependencia <font color="#FF0000">*</font> :</b> </td>
	 <td>
	  <input type=text name=dependencia value='<? echo $dependencia ?>' disabled size=25>
	 </td>
	 <td> <b>Contacto <font color="#FF0000">*</font> :</b></td>
	 <td>
	  <input type=text name=contacto value='<? echo $contacto ?>' disabled size=25>
	 </td>
	</tr>
	<tr>
	  <td><b>Dirección <font color="#FF0000">*</font> :</b></td>
	  <td>
	  <input type=text name=direccion value='<? echo $direccion ?>' disabled size=25>
	 </td>
	 <td>
	   <b> Lugar/Ubicación: </b>
	 </td>
	 <td>
	  <input type=text name=lugar value='<? echo $lugar ?>' disabled size=25>
	 </td>
	</tr>
	<tr>
	 <td>
	  <b>Codigo Postal: </b>
	 </td>
	 <td>
	  <input type=text name=cp value='<? echo $cp ?>' disabled size=25>
	 </td>
	 <td><b> Provincia: </b></td>
	 <td>
	  <select name=provincia  disabled>
	  <option>&nbsp;</option>
	  <?
	  $sql1="select nombre,id_distrito from distrito order by nombre";
	  $rs1=sql($sql1) or fin_pagina();
	  while ($fila=$rs1->fetchrow()) {
		echo "<option value='".$fila['id_distrito']."' ";
		if ($fila["id_distrito"]==$provincia) echo "selected";
		echo ">".$fila['nombre']."</option>\n";
	 }
	?>
	 </td>
	</tr>
	<tr>
	 <td><b> Teléfono: </b></td>
	 <td>
	  <input type=text name=telefono value='<? echo $telefono ?>' disabled size=25>
	 </td>
	 <td><b>E-Mail:</b> </td>
	 <td>
	  <input type=text name=mail value='<? echo $mail ?>' disabled size=25>
	 </td>
	</tr>
	<tr>
		<td valign=top>
			<b>Observaciones:</b>
		</td>
		<td colspan=3>
			<textarea name="comentario" disabled rows=5 cols=100></textarea>
			<?$link8=encode_link("word_etic_cas.php", array("cas"=>$nombre_cliente,"dir"=>$direccion,"ciu"=>$lugar,"prov"=>$provincia,"cp"=>$cp,"contacto"=>$contacto,"tel"=>$telefono,"formato"=>'Cliente'));	
       		?>
       		<A target='_blank' href='<?=$link8?>'><IMG src='<?=$html_root?>/imagenes/word.gif' height='16' width='16' border='0'></a>
		</td>
	</tr>	
	</table>
   </td>
 </tr>
 <tr>
 <script>
var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido

function muestra_tabla() {
	oimg=eval("document.all.imagen_abajo");
	obj_table=eval("document.all.t_estadisticas");
	if (obj_table.style.display=='none') {
		oimg.src=img_ext;
		obj_table.style.display='inline';
	}
	else {
		oimg.src=img_cont;
		obj_table.style.display='none';
	}
}
 </script>
   <td id="mo_sf">
   <table border=1 width=100%>
   <tr>
	<td width=20>
		<img style="cursor: hand;" name="imagen_abajo" src="../../imagenes/down2.gif" onClick="muestra_tabla();">
	</td>
	<td>
   <font size=2>Estadisticas de la dependencia.</font>
	</td>
   </tr>
   </table>
   </td>
 </tr>
 <tr>
	<td>
		<table width=100% name="t_estadisticas" id="t_estadisticas" style="display: none;">
		<tr id=ma>
			<td >Nro. Caso</td>
			<td >Atendido por</td>
			<td >Contacto</td>
			<td >Nro. serie</td>
			<td >Estado</td>
		</tr>
<?
$sq="select id_entidad,idcaso,nrocaso,idestuser,cas_ate.nombre,cas_ate.contacto,nserie from casos_cdr 
	join cas_ate USING (idate) 
	join dependencias USING (id_dependencia) 
	join entidad USING (id_entidad) 
	where casos_cdr.id_dependencia=$chdependencia and idcaso<>$id order by idestuser";
$estado_user=ARRAY(
1 => "En curso",
2 => "Terminado",
7 => "Ok del Cliente");

$res=sql($sq) or fin_pagina();
if ($res->RecordCount()>=1) {
	while ($fila=$res->fetchrow()) {
		$ref = encode_link("caso_estados.php",Array("id"=>$fila["idcaso"],"id_entidad"=>$fila['id_entidad']));
		tr_tag($ref,"Casos de la misma dependencia");
		echo "<td>".$fila["nrocaso"]."</td>";
		echo "<td>".$fila["nombre"]."</td>";
		echo "<td>".$fila["contacto"]."</td>";
		echo "<td>".$fila["nserie"]."</td>";
		echo "<td>".$estado_user[$fila["idestuser"]]."</td>";
		echo "</tr>";
	}
}


?>
		</table>
	</td>
 </tr>
 <tr><td>&nbsp;</td></tr>
 <tr>
   <td id="mo_sf"> Datos del equipo  </td>
 </tr>
 <tr>
  <td width="100%">
   <table width=100%>
	<tr>
	 <td valign=top align=left>
	  <b onclick="if (document.all.serie.value!='') ventana_maquina=window.open('<?=encode_link('caso_elegir_maquina.php',array('onclickcargar'=>'window.opener.cargar_maquina();','onclicksalir'=>'window.close()'))?>&serie='+document.all.serie.value,'','left=40,top=80,width=700,height=280,resizable=1,status=1'); else alert('Por favor ingrese algun dato para realizar la busqueda');" style="cursor:hand;"><u>Número de Serie </u><font color="#FF0000" >*</font>:</b>
	  <input type=text name=serie value='<? echo $rs->fields["nserie"]; ?>' size=25>
	  
	 </td>
	  <td valign=top align=left>
	   <input type=button name=boton value='Ver ordenes asociadas' onclick="if (document.all.serie.value!='') ventana_maquina=window.open('<?=encode_link('caso_elegir_maquina_ordcomp.php',array('onclickcargar'=>'','onclicksalir'=>'window.close()'))?>&serie='+document.all.serie.value,'','left=40,top=80,width=800,height=500,resizable=1,status=1,scrollbars=1'); else alert('Por favor ingrese algun dato para realizar la busqueda');" style="cursor:hand">
	   <?
	   	if ($_ses_global_coradir_bs_as=="si"){
	   ?>
	   		<input type=button name=boton1 value='Generar orden de scio. técnico' onclick="window.open('<?=encode_link('../ord_compra/ord_compra.php',array('caso'=>$nro_caso,'modo'=>'oc_serv_tec'))?>','','left=40,top=80,width=800,height=500,resizable=1,status=1,scrollbars=1');" style="cursor:hand"> 
	   	<?
	   	}
	   	?>
	 </td>
	 <td valign=top align=left>
	  <b>ID LIC:</b>
	 
	  <input type=text name="id_li" value='' size=10>
	  <input type="submit" name="ir" value="Ir" onclick="document.all.modificar.target='_blank';">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	  
	  <input type="submit" name="ordenes_asoc"  value='OC asociadas' onclick="document.all.modificar.target='_blank';">
	  
	 </td>
	</tr>
	<tr>
	 <td valign=top align=left>
	  <b>Desperfecto <font color="#FF0000">*</font>:</b>
	</tr>
	<tr>
	 <td align=left colspan="2">
	  <textarea name=defecto rows=5 style="width:100%"><? echo $rs->fields["deperfecto"]; ?></textarea>
	 </td>
	</tr>
   </table>
  </td>
 </tr>
<?
if ($estado==1)
{
?>
 <tr>
  <td id="mo_sf">
	Estado del caso
  </td>
 </tr>
 <tr>
  <td>
   <table width=100%>
	<tr>
	 <td>
	 <b>Estado para usuario <font color="#FF0000">*</font>: </b>
	 </td>
	 <td>
	 <select name=estadouser>
	 <?
	  $sql1="select idestuser,descripcion from estadousuarios";
	  $rs1=sql($sql1) or fin_pagina();
	   while ($fila=$rs1->fetchrow()) {
	   echo "<option value='".$fila['idestuser']."' ";
	   if ($rs->fields["idestuser"]==$fila['idestuser']) echo "selected";
	   echo ">".$fila['descripcion']."</option>\n";
	   }
	  ?>
	 </td>
	 <td><b>Firma del Cliente </b> </td>
	 <td>
	  <input type=text name=firma value='<? echo $rs->fields["firma"] ?>'>
	 </td>
	 <td align="right"><b>Fecha de Cierre</b></td>
	 <td>
	 <?
	if ($rs->fields['fechacierre'])
	$fecha_cierre=fecha($rs->fields['fechacierre']);
	else
	$fecha_cierre="";
	?>
	<input type=text name=fechacierre value="<?=$fecha_cierre?>">
	<?
	echo link_calendario("fechacierre");
	?>
	 </td>
	</tr>
	<tr align="center">
	 <td>
	  &nbsp;
	 </td>
	 <td>
	  &nbsp;
	 </td>
	 <td>
	  <b>Número MAC</b>
	 </td>
	 <td align="left">
	  <?$numero_mac=$rs->fields['numero_mac'] or $numero_mac=$_POST['numero_mac'];?>
      
	  <input type=text name=numero_mac value="<?=$numero_mac?>">&nbsp;&nbsp;&nbsp;
	  <input type=button name=ver_mac value="Ver" <? //if ($rs->fields["idestuser"]!=2) echo "disabled";?> onClick="window.open('<?= encode_link("../ordquem/ver_reportes.php",array("num_mac"=>$numero_mac));?>','','left=40,top=10,width=700,height=470,resizable=1,status=1');">
	 </td>
	 <td>
	  &nbsp;
	 </td>
	 <td>
	  &nbsp;
	 </td>
	</tr>
   </table>
  </td>
<? 
	$sql_tipif="select * from fallas order by desc_falla";
	$res_tipif=sql($sql_tipif) or fin_pagina();
	$sql_origen="select * from origen_falla order by descripcion";
	$res_origen=sql($sql_origen) or fin_pagina();
?>
	
   <tr>
  <td id="mo_sf">
	Tipificación de fallas
  </td>
 </tr>
 <tr>
 <td>
   <table width=100%>
	  <tr>
	   <td align="center">
	     <b>Especifique la falla:</b> <select name="tipif_falla">
		 <option value=-1>Otros</option>
		 <? while(!$res_tipif->EOF)  { ?>
		  <option value='<?=$res_tipif->fields['id_falla']?>' <?if ($rs->fields['id_falla']==$res_tipif->fields['id_falla'] || $_POST['tipif_falla']==$res_tipif->fields['id_falla']) echo 'selected' ?>><?=$res_tipif->fields['desc_falla'];?></option>
		  <? $res_tipif->MoveNext();
		} ?>
		 </select>
	    </td>
		<td align="center">
	     <b>Especifique el origen de la falla:</b> <select name="origen_falla">
		 <option value=-1>Otros</option>
		 <? while(!$res_origen->EOF)  { ?>
		  <option value='<?=$res_origen->fields['id_origen_falla']?>' <?if ($rs->fields['id_origen_falla']==$res_origen->fields['id_origen_falla'] || $_POST['origen_falla']==$res_origen->fields['id_origen_falla']) echo 'selected' ?>><?=$res_origen->fields['descripcion'];?></option>
		  <? $res_origen->MoveNext();
		} ?>
		 </select>
	    </td>
	  </tr>
	  </table>
 </td>
 </tr>
 </tr>

 <?

 //if ($rs->fields["idate"]!=33)
 //{

 ?>
 <tr>
  <td id="mo_sf">
	Facturación
  </td>
 </tr>
 <?
 //para mostrar con que orden esta pagada
 if ($rs->fields["pagado_orden"]==1) {
  $id_fila=$rs->fields["fila"];
  if ($id_fila!=""){
  $sql="select nro_orden from fila where id_fila = $id_fila";
  $resultado=sql($sql) or fin_pagina();
  $nro_orden=$resultado->fields["nro_orden"];
  //echo $sql;
 ?>
 <tr bgcolor=#F0F0F0>
     <td align=center>
      <font color=blue size=2>
      <b>CAS pagado con Orden de Compra Nro:<?=$nro_orden?></b>
      </font>
     </td>
 </tr>
 <?
  }
 }
 ?>
 <tr>
  <td>
   <table width=100%>
	<tr>
	 <td> <b>Nro. Factura</b> </td>
	 <td> <input type=text name=nfactura value='<? echo $rs->fields["nfactura"]; ?>' size=20></td>
	 <td> <b>Fecha de Factura</b> </td>
	 <td>
  	 <?
	 if ($rs->fields['fechafactura'])
	 			  $fechafactura=fecha($rs->fields['fechafactura']);
	 			  else
	 			  $fechafactura="";
	 ?>
	 <input type=text name=fechafactura value="<?=$fechafactura?>">
	 <?
	 echo link_calendario("fechafactura");
	 ?>
	 </td>
	</tr>
        <tr>
          <td><b>Observaciones</b></td>
          <td colspan=3 align=left>
           <input type=text name='obspago' value='<?=$rs->fields["obspago"]?>' size=100>
          </td>
        </tr>
	<tr>
         <td><b>Precio Final</b></td>
	 <td><input type=text name=precio  value='<? echo $rs->fields["costofin"]; ?>' size=20></td>
	 <td><b>Listo para pagar</b></td>
	 <td>
	  <?

	  if ($rs->fields["pagado"])
		   $checked="checked";
		   else
		   $checked="";

	  ?>
	  <input type=checkbox name=pagado value='1' size=20 <?=$checked?>>
	 </td>
	</tr>
   </table>
  </td>
 </tr>
<?
 //}
}
 ?>
 <tr>
 <?
  if ($rs->fields["idestuser"]==2) $disabled="disabled";
                             else  $disabled="";

 if ($_ses_user['login']=='fernando' || $_ses_user['login']=='juanmanuel')
                                    $disabled="";

 ?>
  <td align=center>
   <input type=submit name=cmd1  value="Guardar" <?=$disabled?> onclick="document.all.modificar.target='_self';return control_falla(document.all.estadouser.value);">
  </td>
 </tr>
</table>
 </td>
</tr>
</table>
<? /*
if ($estado==1)  //permiso completo
{
?>
<br>
<table width=99% align=center cellpadding=0 cellspacing=6 border=1 bordercolor="#111111">
<tr>
	<td colspan=2 id="ma_mg">
		Responsable del Caso
	</td>
</tr>
<tr>
	<td align=center width=50%><br>
		<b>Responsable: <select name="tecnico_reponsable">
		<option value="-1" selected>Seleccionar uno</option>
<?

$sql="select nombre,apellido,permisos_tecnicos.id_usuario from permisos_tecnicos
	inner join usuarios using (id_usuario)";
$r=sql($sql) or fin_pagina();
while ($f=$r->FetchRow()) {
	
	echo "<option value='".$f["id_usuario"]."'";

	if ($tecresp == $f["id_usuario"])
                              echo " selected";

	echo ">".$f["nombre"]." ".$f["apellido"]."</option>\n";
}
?>
		</select><br><br>
		<input type=submit name="cmd1" value="Asignar Técnico" ><br><br>
	</td>
	<td align=center width=50%>
		<font color="blue" size=2><b>
		Asignar un responsable para el referente caso,<br>
		el responsable puede ser cambiado en cualquier momento.<br><br>
		Tenga en cuenta que todos los cambios quedaran registrado.
		</b></font>
	</td>
</tr>
</table>
<? } */?>



</form>
<br>
<?
/**********************************************
 ************  del Casos ***************
 **********************************************/
// print_r($_POST);
//die();
//Datos del $_POST
$est_hora=$_POST["est_hora"];
$est_fecha=$_POST["est_fecha"];
$est_descripcion=$_POST["est_descripcion"];
$id_estado=$_POST["idest_caso"];

if ($_POST["accion"]=="estado" and $_POST["guardar"]=="Guardar")
	{
	$fecha_hora=date("Y-m-d H:i:s",mktime());
	$usuario=$_ses_user["name"];
	$error="";
 	 while (list($key,$cont)=each($_POST)){
	  	  $$key=$cont;
	}

if (!$descripcion)
		 $error.="Debe cargarse la descripción del nuevo estado.<br>";
		 $sql="INSERT INTO estadocdr (idcaso,fecha,descripcion,usuario)";
		 $sql.=" VALUES ($id,'$fecha_hora','$descripcion','$usuario')";
	if (!$error) {

		 sql($sql) or fin_pagina();
		 $est_descripcion="";
		 $est_fecha="";
		 $est_hora=strftime("%H:%M:%S");
		 aviso("<font color=red> Se inserto el nuevo estado con éxito</font>");
	}
	else
		error($error);
}
//fin de estado

?>
<table width=99% align=center cellpadding=0 cellspacing=6 border=1 bordercolor="#111111">
   <tr> <td id="ma_mg"> Estados del Caso</td> </tr>
   <tr>
    <td>
<?
$sql="select nrocaso from casos_cdr where idcaso=$id";
$rs=sql($sql) or fin_pagina();
// Barra de consulta para enviarle al formulario
echo "<form name='estado_busca' action='caso_estados.php' method='post'>";
echo "<input type=hidden name=short value='$sort'>\n";
echo "<input type=hidden name='id' value='$id'>";
echo "<table width=99% border=0 cellspacing=2 cellpadding=2>\n";
echo "<tr><td align=center>\n";

if (!$sort) $sort=1;
$orden = array(
"1" => "estadocdr.fecha",
"2" => "estadocdr.descripcion"
);

$filtro = array(
"estadocdr.fecha"      => "Fecha",
"estadocdr.descripcion"       => "Descripcion"
);

if ($up==0 and $up==NULL) {
	$upa="ASC";
	$up2=1;
}
else {
	$upa="DESC";
	$up2=0;
}
$sql = "SELECT idestcdr,fecha,descripcion,usuario FROM estadocdr";
$sql .= " WHERE idcaso=$id order by ".$orden[$sort];


$link_form = Array(
"sort" => $sort,
"up" => $up,
"id" => $id
);
echo "</td></tr>\n";
$rs = sql($sql) or fin_pagina();
?>
</table>
</form>
<!--
Formulario del estado
-->
<form name='estado' action='caso_estados.php' method='POST'>
<input type=hidden name='id' value='<? echo $id; ?>'>
<input type=hidden name=accion value=estado>
<input type=hidden name='cliente' value='<? echo $cliente; ?>'>

<table width='100%'  border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
<tr id="mo_sf">
   <td  align=left colspan=2>
	<b>Total: <? echo $total; ?> Estados</b>
   </td>
</tr>
<?
$link_form["page"]=$page;
$link_form["up"]=$up2;
$link_form["sort"]=1;
echo "<tr  id=ma>";
   echo "<td >";
   echo "<a id=mo href='".encode_link("caso_estados.php",$link_form)."'>";
   echo "Fecha";
   echo "</a>";
   echo "</td>";
   $link_form["sort"]=2;
   echo "<td   width=60%>";
   echo "<a id=mo href='".encode_link("caso_estados.php",$link_form)."'>";
   echo "Descripción";
   echo "</td>\n";
echo "</tr>\n";
while (!$rs->EOF) {
	$fecha1=substr($rs->fields["fecha"],0,10);
	$hora=substr($rs->fields["fecha"],11,8);
	$usuario=$rs->fields["usuario"];
	$descrip = str_replace(chr(13).chr(10),"<n>",$rs->fields["descripcion"]);
	$id_estado=$rs->fields["idestcdr"];
	echo "<tr bgcolor='$bgcolor2'>\n";
	  echo "<td align=center valign=top>";
	  echo "<b>".Fecha($fecha1)." $hora<br><br>$usuario</b>";
	  echo "</td>\n";
	  echo "<td width=80% align=left>";
	  echo "<textarea readonly name=descripcion_estado style='width:100%;' rows=4>";
	  echo  $rs->fields["descripcion"];
	  echo "</textarea>";
	  echo "</td>\n";
	echo "</tr>\n";
	$rs->MoveNext();
}
?>

<tr bgcolor='<?=$bgcolor2?>'>
  <td align=right valign=top>
	<b>Nuevo</b>
  </td>
  <td width=70% align=left>
	<textarea name="descripcion" style='width:100%;' rows=4></textarea>
  </td>
</tr>
<script>
 function limpiar_estados(){
  document.estado.descripcion.value='';
 }
</script>
<tr>
  <td colspan=2 width=100%>
   <table width=100% align=center>
	   <tr>
		  <td width=50% align=right>
		  <input type=submit name=guardar value=Guardar style="width:30%">
		  </td>
		  <td width=50% align=left>
		  <input type=button name=deshacer value=Deshacer style="width:30%" onclick="limpiar_estados();">
		  </td>
	   </tr>
   </table>
  </td>
</tr>
</table>
</td>
</tr>
</table>

<?
/*******************************************
************ Fin de los estados del caso ***
*******************************************/


/*******************************************
**************** Muletos *******************
*******************************************/
if ($_POST ['guardar_muleto']=="Guardar"){
	
	if($_POST ['radio_muleto']=="pendiente"){
		$fecha=date("Y-m-d H:i:s");
		$db->StartTrans();
		//cambio de estado el muleto
		$id_caso_muleto=$_POST ['id'];
		$id_muleto=$_POST ['id_muleto_h'];	
		$sql="update casos.muletos set id_estado_muleto=6, idcaso=$id_caso_muleto, fecha_llegada_estado='$fecha' where id_muleto=$id_muleto";
		sql($sql) or fin_pagina();
		//cargo el log
		$usuario=$_ses_user['name'];
		$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Paso a Pendiente (En Uso)',NULL,$id_caso_muleto)";
		sql($log) or fin_pagina();
		$db->CompleteTrans();
	    //mensaje    
		$accion="Se Cambio el Estado del Muleto con ID $id_muleto a Pendiente (En Uso)";
    	aviso("<font color=red>$accion</font>");
	}
	if($_POST ['radio_muleto']=="en_uso"){
		$fecha=date("Y-m-d H:i:s");
		$id_caso_muleto=$_POST ['id'];
		$id_muleto=$_POST ['id_muleto_h'];	
		$db->StartTrans();
		//cambio de estado el muleto
		$sql="update casos.muletos set id_estado_muleto=2, flag_prueba_vida=0, idcaso=$id_caso_muleto, fecha_llegada_estado='$fecha' where id_muleto=$id_muleto";
		sql($sql) or fin_pagina();
		//cargo el log
		$usuario=$_ses_user['name'];
		$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Paso a En Uso desde Caso',NULL,$id_caso_muleto)";
		sql($log) or fin_pagina();
		
		//empieza el insert del nuevo muleto
		$nro_serie=$_POST['nro_serie_c'];
   		$marca=$_POST['marca_c'];
   		$modelo=$_POST['modelo_c'];
   		$observaciones=$_POST['observaciones_c'];
   		
   		$q="select nextval('muletos_id_muleto_seq') as id_muleto";
    	$id_muleto=sql($q) or fin_pagina();
    	$id_muleto=$id_muleto->fields['id_muleto'];
    	
    	$id_estado_muleto=4;
     
    	$query="insert into casos.muletos
        	     (id_muleto, observaciones, marca, modelo, nro_serie, id_estado_muleto, flag_prueba_vida, idcaso,precio_stock,fecha_llegada_estado,flag_monitor_cliente)
            	 values
           	  ($id_muleto, '$observaciones', '$marca', '$modelo', '$nro_serie', $id_estado_muleto,0, $id_caso_muleto,0,'$fecha',1)";
    	sql($query, "Error al insertar/actualizar el muleto") or fin_pagina();
   	
    	/*cargo los log*/ 
    	$usuario=$_ses_user['name'];
		$fecha=date("Y-m-d H:i:s");
		$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Alta del Muleto desde Caso (En Uso)',NULL,NULL)";
		sql($log) or fin_pagina();
		//termino de insertar
        $db->CompleteTrans();
	    //mensaje 
	    $id_muleto_msg=$_POST ['id_muleto_h'];
		$accion="Se Cambio el Estado del Muleto con ID $id_muleto_msg a En Uso <br> Se dio de Alta el Muleto con ID $id_muleto";
    	aviso("<font color=red>$accion</font>");
	}
	if($_POST ['radio_muleto']=="cliente_final"){
		$fecha=date("Y-m-d H:i:s");
		$id_caso_muleto=$_POST ['id'];
		$nro_serie=$_POST['nro_serie_c'];
   		$marca=$_POST['marca_c'];
   		$modelo=$_POST['modelo_c'];
   		$observaciones=$_POST['observaciones_c'];
   		$precio_stock_c=$_POST['precio_stock_c'];
   		$id_muleto_c=$_POST['id_muleto_cliente_final'];//el muleto que esta actualmente en cliente final
   		
		$db->StartTrans();
   		//doy de alta el nuevo muleto
    	$q="select nextval('muletos_id_muleto_seq') as id_muleto";
    	$id_muleto=sql($q) or fin_pagina();
    	$id_muleto=$id_muleto->fields['id_muleto'];
    	$id_estado_muleto=4;
    	$id_muleto_msg=$id_muleto;
     
    	$query="insert into casos.muletos
        	     (id_muleto, observaciones, marca, modelo, nro_serie, id_estado_muleto, flag_prueba_vida, idcaso,precio_stock,fecha_llegada_estado)
            	 values
           	  ($id_muleto, '$observaciones', '$marca', '$modelo', '$nro_serie', $id_estado_muleto,0, NULL,$precio_stock_c,'$fecha')";
    	sql($query, "Error al insertar/actualizar el muleto") or fin_pagina();
   	
    	/*cargo los log*/ 
    	$usuario=$_ses_user['name'];
		$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Alta del Muleto desde Caso (Cliente Final)',NULL,NULL)";
		sql($log) or fin_pagina();
		//Termino el alta del muelto
		//paso al historial el muleto
		$id_caso_muleto=$_POST ['id'];
		$id_muleto=$_POST ['id_muleto_h'];	
		$sql="update casos.muletos set id_estado_muleto=5, flag_prueba_vida=0, idcaso=NULL, fecha_llegada_estado='$fecha' where id_muleto=$id_muleto";
		sql($sql) or fin_pagina();
	
		$usuario=$_ses_user['name'];
		$fecha=date("Y-m-d H:i:s");
		$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Paso a Historial se entrego al Cliente Final)',NULL,$id_caso_muleto)";
		sql($log) or fin_pagina();
    
		$db->CompleteTrans();
	    //mensaje 
		$accion="Se Cambio el Estado del Muleto con ID $id_muleto a Historial se Entrego al Cliente <br> Se Dio de Alta en Nuevo Muleto con ID $id_muleto_msg en estado A Reparar";
    	aviso("<font color=red>$accion</font>");
	}
}
if ($_POST['a_reparar']=="Devolución del Cliente"){
	$id_caso_muleto=$_POST ['id'];
	$fecha=date("Y-m-d H:i:s");
	$db->StartTrans();
	//cambio de estado
	$id_muleto_a_reparar=$_POST['id_muleto_a_reparar'];
	$sql="update casos.muletos set id_estado_muleto=7, flag_prueba_vida=1, idcaso=NULL, fecha_llegada_estado='$fecha' where id_muleto=$id_muleto_a_reparar";
	sql($sql) or fin_pagina();
	//cargo log
	$usuario=$_ses_user['name'];
	$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto_a_reparar, '$usuario', '$fecha','Paso A Reparados desde Caso (Devolución Cliente)',NULL,NULL)";
	sql($log) or fin_pagina();
	
	/*tengo que pasar al historial el muleto que cargue cuando seleccione en uso
	por que es del cliente NO de coradir*/
	 //selecciono el muleto que cargue saco el id_muleto
	$sql="select id_muleto from casos.muletos where idcaso=$id_caso_muleto";
	$id_muelto_aux1_sql=sql($sql) or fin_pagina();
	$id_muelto_aux1=$id_muelto_aux1_sql->fields['id_muleto'];
	 //paso a historial el muleto que le devolvi el cliente
	$sql="update casos.muletos set id_estado_muleto=5, fecha_llegada_estado='$fecha', idcaso=NULL where id_muleto='$id_muelto_aux1'";
	sql($sql) or fin_pagina();
	$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ('$id_muelto_aux1', '$usuario', '$fecha','Paso A Historial es un Monitor del Cliente',NULL,'$id_caso_muleto')";
	sql($log) or fin_pagina();
    $db->CompleteTrans();
    
	//mensaje
	$accion="Se Cambio el Estado del Muleto $id_muleto_a_reparar A Reparados para Realizar Prueba de Vida <br> Se paso a Historial el Monitor del Cliente con ID $id_muelto_aux1";
    aviso("<font color=red>$accion</font>");
}


$onclick_cargar="window.opener.document.all.id_muleto_h.value=document.all.id_muleto_h.value;
                 window.opener.document.all.observaciones_h.value=document.all.observaciones_h.value;
  				 window.opener.document.all.marca_h.value=document.all.marca_h.value;
  				 window.opener.document.all.modelo_h.value=document.all.modelo_h.value;
  				 window.opener.document.all.nro_serie_h.value=document.all.nro_serie_h.value;              
                 window.close();";
$link_elegir=encode_link("muletos_listado.php",array("pagina_viene"=>"caso_estados.php","onclick_cargar"=>$onclick_cargar,"estado_muleto_de_caso"=>"1"));

$sql="select * from casos.muletos where idcaso=$id";
$result_muleto=sql($sql) or fin_pagina();


?>
<script>
function control_datos_muletos(){
  if(document.all.id_muleto_h.value==""){
  	alert('Debe Asignar un Muleto');
  	return false;
  }
 
 if ((document.all.radio_muleto[2].checked==true)||(document.all.radio_muleto[1].checked==true)){
 	if(document.all.nro_serie_c.value==""){
 		alert('Debe ingresar un Nro de Serie');
	  	return false;
	}
	if(document.all.marca_c.value==""){
		alert('Debe ingresar una Marca');
	  	return false;
	}
	if(document.all.modelo_c.value==""){
		alert('Debe ingresar un Modelo');
	  	return false;
	}
	if(document.all.nro_serie_c.value.indexOf('"')>0){
	 	alert('No se puede Agregar Comillas Dobles en Numero de Serie');
	  	return false;
	}
	if(document.all.marca_c.value.indexOf('"')>0){
	 	alert('No se puede Agregar Comillas Dobles en la Marca');
	  	return false;
	}
	if(document.all.modelo_c.value.indexOf('"')>0){
	 	alert('No se puede Agregar Comillas Dobles en el Modelo');
	  	return false;
	}
	if(document.all.observaciones_c.value.indexOf('"')>0){
	 	alert('No se puede Agregar Comillas Dobles a las Observaciones');
	  	return false;
	}
 }//del if()
 else{
 	return true
 }; 
 return true;
}//de function control_nuevos()
</script> 
<br>
<br>
<table width=99% class="bordes" cellpadding='2' cellspacing='1' align="center">
  <tr><td colspan="4">
  <table width=99% class="bordes" cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff' align="center">
  <tr>
    <td id="ma_mg" colspan="4"> Muletos &nbsp;&nbsp;
     <input type="hidden" name='id_muleto_h' value="<?=$result_muleto->fields['id_muleto']?>">	 
     <input type="button"  name='asignar_muleto' value='Asignar Muleto' onclick="window.open('<?=$link_elegir?>')" <?if ($result_muleto->RecordCount()>0) echo disabled;?>>
     <?if ($result_muleto->RecordCount()==0){?>
     	&nbsp;&nbsp;<b><font color="Red" size="1">No Existen Muletos Asignados a este Caso</font></b>
     <?}?>
    </td>
  </tr>
  </table></td></tr>
  
  <tr><td colspan="4">
  <table width=99% class="bordes" cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff' align="center">
  <tr>
    <td align="right"><b>Observaciones: </b></td>
    <td><input type="text" value="<?=$result_muleto->fields['observaciones']?>" name="observaciones_h" style="width=300" readonly></td>
    <td align="right"><b>Marca: </b></td>
    <td><input type="text" value="<?=$result_muleto->fields['marca']?>" name="marca_h" style="width=300" readonly></td>
  </tr>
  
  <tr>
    <td align="right"><b>Modelo: </b></td>
    <td><input type="text" value="<?=$result_muleto->fields['modelo']?>" name="modelo_h" style="width=300" readonly></td>
    <td align="right"><b>Número de Serie: </b></td>
    <td><input type="text" value="<?=$result_muleto->fields['nro_serie']?>" name="nro_serie_h" style="width=300" readonly></td>
  </tr>
  
  <tr align="center">
  	<td colspan="4" align="center">
  	 <b>
  	 Pendiente: <input name="radio_muleto" type="radio" value="pendiente" 
				<?if ($result_muleto->fields['id_estado_muleto']=="6" or $result_muleto->fields['id_estado_Muleto']=="") {echo "checked";} ?> 
				onclick="javascript: (this.checked)?Ocultar('muestra_tabla') :Mostrar ('muestra_tabla');<?if ($result_muleto->fields['id_estado_muleto']=="2"){?>document.all.a_reparar.disabled=false;<?}?>document.all.precio_stock_c.disabled=true;"> 
  	 &nbsp;&nbsp;&nbsp;&nbsp;
  	 En Uso: <input name="radio_muleto" type="radio" value="en_uso"
				<?if ($result_muleto->fields['id_estado_muleto']=="2") {echo "checked";} ?> 
				onclick="javascript: (this.checked)?Mostrar('muestra_tabla') :Ocultar ('muestra_tabla'); <?if ($result_muleto->fields['id_estado_muleto']=="2"){?>document.all.a_reparar.disabled=false;<?}?>document.all.precio_stock_c.disabled=true;"> 
  	 &nbsp;&nbsp;&nbsp;&nbsp;
  	 Cliente Final: <input name="radio_muleto" type="radio" value="cliente_final"
				<?if ($result_muleto->fields['id_estado_muleto']=="3") {echo "checked";} ?> 
				onclick="javascript: (this.checked)?Mostrar('muestra_tabla') :Ocultar ('muestra_tabla'); <?if ($result_muleto->fields['id_estado_muleto']=="2"){?>document.all.a_reparar.disabled=true;<?}?>document.all.precio_stock_c.disabled=false;">
  	 </b>
    </td>
  </tr>
  </table></td></tr>
</table>

    <div align="center" style='display:none' id="muestra_tabla">
	<table width=99% class="bordes" cellpadding='2' cellspacing='1' align="center">
	  <tr><td colspan="4">
  		<table width=99% class="bordes" cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff' align="center">
  		
	     <tr>
	      <td id=mo colspan="2">
	       <b> Nuevo Muleto</b>
	      </td>
	     </tr>
	     <tr>
	           <td align='left'>
	            <b> Nro. Muleto <font color="Red">Nuevo<input type="hidden" value="<?=$id_muleto?>" name="id_muleto_cliente_final"></font> </b>
	           </td>
	           <td align='left'>
	            <b> Nro. Serie <input type='text' name='nro_serie_c' value='' size=30 align='right'></b>
	           </td>
	     </tr>
	     <tr>
	       <td>
	        <table>
	         <tr>
	           <td  colspan="2">
	            <b> Marca </b>
	           </td>
	          </tr>
	          <tr>
	           <td  colspan="2">
	             <input type='text' name='marca_c' value='' size=50 >
	           </td>
	          </tr>
	          <tr>
	           <td colspan="2">
	            <b> Modelo </b>
	           </td>
	          </tr>
	          <tr>
	           <td  colspan="2">
	            <input type='text' name='modelo_c' value='' size=50>
	           </td>
	          </tr>
	          <tr>
	           <td colspan="2">
	            <b> Precio Stock </b>
	           </td>
	          </tr>
	          <tr>
	           <td  colspan="2">
	            <input type='text' name='precio_stock_c' value='0' size=50>
	           </td>
	          </tr>
	        </table>
	      </td>
	      <td>
	        <table>
	          <tr><td valign='top'><b> Observaciones </b></td></tr>
	          <tr><td><textarea cols='50' rows='10' name='observaciones_c'></textarea></td></tr>
	        </table>
	      </td>
	     </tr>
	  </table>
	 </td></tr>
	</table>
	</div>

	<table width=99% class="bordes" cellpadding='2' cellspacing='1' align="center">
  		<tr><td colspan="4">
  		<table width=99% class="bordes" cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff' align="center">
  			<tr>
    			<td id="ma_mg" colspan="4">
				  	 <input type=submit name=guardar_muleto value=Guardar style="width:25%" onclick="return control_datos_muletos();">
				  	 &nbsp;&nbsp;&nbsp;
				  	 <? if ($result_muleto->fields['id_estado_muleto']=="2"){?>
				  	 <input type="hidden" value="<?=$result_muleto->fields['id_muleto']?>" name="id_muleto_a_reparar">
				 	 <input type="submit" value="Devolución del Cliente" title="Pasa a Estado A Reparar por Devolución del Cliente" name="a_reparar" style="width=300" onclick="confirn ('Esta Seguro que Desea Devolver el Monitor al Cliente');">
				 	 <?}?>
	    		</td>
	  		</tr>
  		</table></td></tr>
  	</table>
	
</form><?//cierra el form de estado y muletos
/*******************************************
************* Fin de Muletos ***************
*******************************************/


/*-------------------------------------------*/
/*******************************************
************ visitas de los casos **********
*******************************************/

/*-------------------------------------------*/

/*if ($_POST["accion"]=="visitas" and $_POST["guardar"]=="Guardar")
   {
	while (list($key,$cont)=each($_POST)) $$key=$cont;
	$usuario=$_ses_user["name"];
	if (!$fecha_visitas)
				  $error="Debe ingresar una fecha para la visita <br>";

	if (!fechaok($fecha_visitas))
				  $error.="Formato de Fecha inválido";
				  else
				  $fecha_visitas=fecha_db($fecha_visitas);

	if (!$comentarios)
				$error.="Debe ingresar un comentario para la visita.<br>";

		 $sql="INSERT INTO visitas (idcaso,fecha_visita,comentario,usuario)";
		 $sql.=" VALUES ($id,'$fecha_visitas','$comentarios','$usuario')";
	if (!$error) {
		 sql($sql) or fin_pagina();
		 aviso("<font color=red> Se inserto la visita con éxito</font>");
	}
	else
		error($error);

   }//del post de guardar visitas
*/

?>
<script>
 function limpiar(){
   document.visitas.comentarios.value='';
 }
</script>
<form name="visitas" method=post action=caso_estados.php>
<input type=hidden name='id' value='<? echo $id; ?>'>
<input type=hidden name=accion value=visitas>
<input type=hidden name='cliente' value='<? echo $cliente; ?>'>
<?
   $link=encode_link('asignar_visitas.php',array("id_caso"=>$id,"nro_caso"=>$nro_caso));
   $onclick="ventana=window.open('$link','','');";
?>


<br>
<br>
<table width=99% border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>

  <tr>
    <td id="ma_mg"> Visitas &nbsp;&nbsp;
     <input type="button"  name='asignar_visita' value='Asignar Visitas' onclick="<?=$onclick?>">    </td>
  </tr>
 <?
$sql="select id_visitas_casos,id_tecnico_visita,vc.direccion,
     contacto,vc.telefono,fecha_visita,
     observaciones,cant_modulos,nombre,apellido,estado
     from casos.visitas_casos vc
     join casos.tecnicos_visitas tv  using (id_tecnico_visita)
      where idcaso=$id  order by fecha_visita asc";
$res_sql=sql($sql,"todas las visitas") or fin_pagina();
//TRAIGO LA PROXIMA VISITA PENDIENTE

$sql="select id_visitas_casos
     from casos.visitas_casos vc
     join casos.tecnicos_visitas tv  using (id_tecnico_visita)
     where idcaso=$id and estado='Pendiente' order by fecha_visita asc limit 1";

 $res_pp=sql($sql,"proxima visita") or fin_pagina();
 $id_visita_pp=$res_pp->fields["id_visitas_casos"];

  for ($i=0;$i<$res_sql->recordcount();$i++){

      $hora=Hora($res_sql->fields['fecha_visita']);
      $fecha=fecha($res_sql->fields['fecha_visita']);
      $tecnico=$res_sql->fields['id_tecnico_visita'];
      $nbre_tecnico=$res_sql->fields['apellido']." ".$res_sql->fields['nombre'];
      $id_visitas_casos=$res_sql->fields['id_visitas_casos'];
      $estado_visita=$res_sql->fields["estado"];
      $id_visita_asignada="";
      $reasigna="";
      if ($estado_visita=="Historial")
                               $solo_lectua=1;
                               else
                               $solo_lectua=0;


      $link1=encode_link('concretar_visitas.php',array("id_caso"=>$id,"nro_caso"=>$nro_caso,"hora" =>$hora,"fecha"=>$fecha,
                         "tecnico"=>$tecnico,"nombre_tecnico"=>$nbre_tecnico,"id_visitas_casos"=>$id_visitas_casos,
                         "id_visita_asignada"=>$id_visita_asignada,"reasigna"=>$reasigna,"pagina"=>"caso_estados","solo_lectura"=>$solo_lectua));

      $onclick1="ventana=window.open('$link1','','');";
      if ($id_visitas_casos==$id_visita_pp)
           $proxima_pendiente=" bgcolor=#FFC0C0";
           else
           $proxima_pendiente="";
  ?>
   <tr>
     <td <?=$proxima_pendiente?>>
        <table align='center' width="100%" align="center" >
           <tr>
              <td align='left'><b>Estado: </b></td>
              <td align='left' ><font color=red><b><?=$estado_visita?></b></font></td>
              <td align="right" colspan=2> <input type='button' name='ver' value='Ver visita' onclick="<?=$onclick1?>"> </td>
           </tr>
           <tr>
           <?
           $sum=($res_sql->fields['cant_modulos']) * 30;
           $horas=split(":",$hora);
           $hora_fin=date("H:i",mktime($horas[0],$horas[1]+$sum,'00'));?>
           <td align='left'> <b>Fecha: </b></td>
           <td align="left"> <?=fecha($res_sql->fields['fecha_visita'])?> </td>
           <td align="left"> <b> Hora Inicio:</b> <?=substr(Hora($res_sql->fields['fecha_visita']),0,5)?></td>
           <td align="left"> <b> Hora Fin:</b> <?=$hora_fin?> </td>
           </tr>

          <tr>
          <td align="left" width=15%> <b> Tecnico Asignado:</b> </td>
          <td colspan=3 align=left> <?=$nbre_tecnico?> </td>

          </tr>
           <tr >
           <td align="left"> <b>Dirección:</b>  </td>
           <td align="left" colspan=3><?=html_out($res_sql->fields['direccion'])?> </td>
           </tr>
           <tr>
            <td  align="left"> <b>Contacto: </b> </td>
            <td  align="left"> <?=html_out($res_sql->fields['contacto']) ?>  </td>
            <td  align="left" colspan=2> <b>Teléfono: </b>  <?=html_out($res_sql->fields['telefono']) ?> </td>
           </tr>
  <tr>
     <td align='left' valign=top > <b>Observaciones:</b> </td>
     <td colspan=3>&nbsp;</td>
 </tr>
 <tr>
     <td>&nbsp;</td>
  	 <td colspan=3 align='left' valign=top>
        <?=html_out($res_sql->fields['observaciones'])?>
     </td>
  </tr>
</table>


</td>
</tr>
<?
$res_sql->movenext();
}
?>
</table>
<br>
<br>
<br>
<? /*
<tr>
<td>
<table width='100%'  border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
<tr id="mo_sf">
   <td  align=left colspan=2>
	<b>Total: <? echo $total; ?> Estados</b>
   </td>
</tr>
<tr id="ma">
   <td> Fecha </td><td>Comentarios</td>
</tr>
<?

$sql="select * from visitas where idcaso=$id";
$rs=sql($sql) or fin_pagina();
// Barra de consulta para enviarle al formulario

$cantidad=$rs->recordcount();
for($i=0;$i<$cantidad;$i++){
$fecha=$rs->fields["fecha_visita"];
$comentario=$rs->fields["comentario"];
?>
  <tr bgcolor='<?=$bgcolor2?>'>
	 <td align=center valign=top >
	 <b>
	  <?=fecha($fecha)?>
	  <br>
	  <?=$usuario?>
	 <b>
	 </td>
	 <td align=center valign=top width=80%>
	 <textarea name=comentario_<?=$i?> rows=4 readonly style="width:100%"><?=$comentario?></textarea>
	 </td>
  </tr>
<?
$rs->movenext();
}//del for
?>
  <tr bgcolor='<?=$bgcolor2?>'>
	 <td align=center valign=top>
	 <input type=text name=fecha_visitas value="" size=13>
	 <?echo link_calendario("fecha_visitas");?>
	 </td>
	 <td align=center valign=top width=80%>
	 <textarea name=comentarios rows=4  style="width:100%"></textarea>
	 </td>
  </tr>
  <tr>
  <td colspan=2 width=100%>
   <table width=100% align=center>
	   <tr>
		  <td width=50% align=right>
		  <input type=submit name=guardar value=Guardar style="width:30%">
		  </td>
		  <td width=50% align=left>
		  <input type=button name=deshacer value=Deshacer style="width:30%" onclick="limpiar()">
		  </td>
	   </tr>
   </table>
  </td>
  </tr>
</table>
</td>
</tr>

</table>
*/?>
</form>

<?
/*******************************************
************ Fin de los visitas a los casos ***
*******************************************/

/*-------------------------------------------*/



/*******************************************
 *********** $clientes del caso ***********
 *******************************************/

//Datos por post
//print_r($_POST);
$proveedor=$_POST["proveedor"];
$descripcion=$_POST["descripcion"];
$enviado=$_POST["enviado"];
$id_respuesto=$_POST["idest_rep"];
//idest

if ($_POST["accion"]=="repuesto" and $_POST["nuevo"]=="Modificar") {
	$error="";
	if (!$proveedor)
		$error.="No se puede cargar el repuesto sin un proveedor.<br>";
	if (!$descripcion)
		 $error.="Debe cargarse la descripción del repuesto.<br>";
	if (!$enviado)
		$enviado=0;
	$sql="UPDATE repuestos set ";
	$sql.=" proveedor='$proveedor',";
	$sql.=" descripcion='$descripcion',";
	$sql.=" enviado=$enviado";
	$sql.=" WHERE idrepuesto=$id_respuesto";
	if (!$error){
		 sql($sql) or fin_pagina();
		 $descripcion="";
		 $proveedor="";
		 $enviado=0;
	}
	else
		error($error);
}
if ($_POST["accion"]=="repuesto" and $_POST["nuevo"]=="Nuevo") {
	$error="";
	if (!$proveedor)
		$error.="No se puede cargar el repuesto sin un proveedor.<br>";
	if (!$descripcion)
		 $error.="Debe cargarse la descripción del repuesto.<br>";
	if (!$enviado)
		$enviado=0;
	$sql="INSERT INTO repuestos (idcaso,descripcion,proveedor,enviado)";
	$sql.=" VALUES ($id,'$descripcion','$proveedor',$enviado)";
	if (!$error){
		 sql($sql) or fin_pagina();
		 $descripcion="";
		 $proveedor="";
		 $enviado=0;
	}
	else
		error($error);


}
/************************************************
					Parte nueva
************************************************/
if ($_POST["accion"]=="repuesto" and $_POST["guardar"]=="Guardar") {
$cantidad_elementos=$_POST["cantidad_filas"];
$db->StartTrans();
      //borro  los repuestos de los casos para simplificar las cosas
      $sql="delete from repuestos_casos where";
      $sql.=" idcaso=$id";
      sql($sql) or fin_pagina();
      //inserto los repuestos que estan en pagina
      //print_r($_POST);
      for($i=0;$i<$cantidad_elementos;$i++)
	       {
            if ($_POST["items_$i"])
             {
        	 $id_producto=$_POST["productos_$i"];
        	 $id_proveedor=$_POST["proveedor_$i"];
        	 $descripcion=$_POST["descripcion_$i"];
        	 $cantidad=$_POST["cantidad_$i"];
        	 $enviado=$_POST["enviado_$i"];
              if (!$enviado) $enviado=0;
        	 $sql="insert into repuestos_casos ";
        	 $sql.=" (idcaso,id_producto,id_proveedor,descripcion,cantidad,enviado)";
             $sql.=" values ";
             $sql.=" ($id,$id_producto,$id_proveedor,'$descripcion',$cantidad,$enviado)";
             sql($sql) or fin_pagina();
             } //del if de los items
	      }//del for

     $db->completetrans();
} //del if de agregar productos

?>
<script>
function rep_radioclick(id,proveedor,descripcion,enviado) {
		 descrip = descripcion.replace("<n>","\n");
		 document.all.repuesto.descripcion.value=descrip;
		 document.all.repuesto.proveedor.value=proveedor;
		 if (enviado==1)
			 document.all.repuesto.enviado.checked = "True";
		 else
			 document.all.repuesto.enviado.checked = "";
		 document.all.repuesto.nuevo.value='Modificar';
}
function rep_radionuevo() {
		 document.all.repuesto.nuevo.value = "Nuevo";
		 document.all.repuesto.descripcion.value = "";
		 document.all.repuesto.proveedor.value = "";
		 document.all.repuesto.enviado.checked = "";
}



//FUNCIONES PARA QUE AGREGUE UNA FILA
//EN PRODUCTOS
var wproductos;


function eliminar_productos(){

if (confirm('Esta seguro que desea eliminar los productos')) borrar_items();
}

function borrar_items()
{

var i=0;
var cant;
var sentencias;
var y=0;
var cant_filas=0;
var ejecutar;
sentencias=new Array();
bloquear=new Array();



items_aux=parseInt(document.repuesto.items.value);
if (typeof(document.all.chk.length)!='undefined'){

	   while (i < document.repuesto.chk.length)
		{
			if (document.repuesto.chk[i].checked)
				 {
				  y=i+1;
                  bloquear[i]="document.repuesto.items_"+i+".value=0";
				  sentencias[i]="document.all.repuestos_agregados.deleteRow("+ y +")";
				 }
		 i++;
	   }//del while
	   i=sentencias.length-1;
	  while(i>=0)
	     {
         eval(bloquear[i]);
	     eval(sentencias[i]);
	     i--;
         items_aux--;
	     }//del segundo while



}//del if

else{
 if (typeof(document.all.chk)!='undefined'){
	if (document.repuesto.chk.checked)
         {

         cant_filas=parseInt(document.repuesto.cantidad_filas.value);
         for(i=0;i<cant_filas;i++){
            ejecutar="typeof(document.repuesto.items_"+i+")";

            if (eval(ejecutar)!='undefined')
                                 {
                                 ejecutar="document.repuesto.items_"+i+".value=0";
                                 eval(ejecutar);
                                 }
         }//del for

		 document.all.repuestos_agregados.deleteRow(1);
         items_aux--;
         } //del if
	}//del else
}
document.repuesto.items.value=items_aux;
}//del fin de la funcion que borra productos



//funcion para agregar productos de la ventana de productos
function cargar()
{
/*Para insertar una fila*/

var items=parseInt(document.repuesto.items.value);

//inserta al final
var fila=document.all.repuestos_agregados.insertRow(document.all.repuestos_agregados.rows.length );

fila.insertCell(0).innerHTML="<input type=hidden name=productos_"+items+" value='"+wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].value+"'>"+
							 "<input type=hidden name=proveedor_"+items+" value='"+wproductos.document.all.select_proveedores[wproductos.document.all.select_proveedores.selectedIndex].value+"'>"+
                             "<input type=hidden name='items_"+items+"' value=1>"+
							 "<div align='center'>"+
							 " <input name='chk' type='checkbox'  value='1'>"+
							 "</div>";

fila.insertCell(1).innerHTML="<div align='center'> <input name='cantidad_"+
items+"' type='text' id='cantidad' size='4' value='1' style='text-align:right'>"+
"</div>";


fila.insertCell(2).innerHTML="<div align='center'><textarea name='descripcion_"+
items +"'  rows='3' wrap='VIRTUAL' id='descripcion' style='width:100%'>"+
wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].text+
"</textarea></div>";

fila.insertCell(3).innerHTML="<div align='center'><input type=text readonly name=desc_prov_"+
items +"'  id='proveedor' style='width:100%' value='"+
wproductos.document.all.select_proveedores[wproductos.document.all.select_proveedores.selectedIndex].text+
"'></div>";


fila.insertCell(4).innerHTML="<div align='center'> <input name='enviado_'"+items+" type='checkbox'  value='1'></div>";

document.repuesto.items.value=items+1;
document.repuesto.cantidad_filas.value=parseInt(document.repuesto.cantidad_filas.value)+1;
} //fin de la funcion de hacer carga



function abrir_producto(){
<?$link=encode_link('caso_seleccionar_productos.php',array('onclickcargar'=>'window.opener.cargar();','onclicksalir'=>'window.close()'));?>

wproductos=window.open('<?=$link?>','','left=0,top=0,width=780,height=400,resizable=1');

}


</script>
<?
if ($nro_caso)
{
?>
<form name='repuesto' action='caso_estados.php' method='POST'>

<input type=hidden name=short value='<?=$sort?>'>
<input type=hidden name='id' value='<?=$id?>'>
<input type='hidden' name=accion value='repuesto'>
<input type=hidden name='cliente' value='<? echo $cliente; ?>'>
<?
 $sql = "select distinct(descripcion_prod),razon_social,cantidad,nro_orden from compras.orden_de_compra
         join compras.fila using(nro_orden)
         join general.proveedor using(id_proveedor)
         where orden_de_compra.estado<>'n' and nrocaso=$nro_caso
         order by nro_orden asc";
 $resul_todos=sql($sql,"Erro al consultar todas la ordenes de compras asociadas al caso") or fin_pagina();
 if ($resul_todos->RecordCount()!=0)
 {
?>
<table width=99% align=center cellpadding=0 cellspacing=6 border=1 bordercolor="#111111">
<tr>
 <td>
  <table width="100%" align="center"> 
   <tr>
    <td id="ma_mg">
     Repuestos Comprados para este Caso
    </td>
   </tr>
  </table>
 </td>
</tr>
<tr>
 <td>
  <table width="100%" align="center">
   <tr id="mo">
    <td align="center">
     Cantidad
    </td>
    <td align="center">
     Descripción
    </td>
    <td align="center">
     Proveedor
    </td>
    <td align="center">
     OC
    </td>
   </tr>
   <?$i=0;
     while (!$resul_todos->EOF)
     { $tr_color=(((++$i)%2)==0)?$bgcolor1:$bgcolor2;
   ?>
    <tr bgcolor='<?= $tr_color ?>'>
     <td align="center" >
      <?=$resul_todos->fields['cantidad']?>
     </td>
     <td align="center">
      <?=$resul_todos->fields['descripcion_prod']?>
     </td>
     <td align="center">
      <?=$resul_todos->fields['razon_social']?>
     </td>
     <td align="center">
      <a href="<?=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$resul_todos->fields["nro_orden"]))?>" target="_blank"><?=$resul_todos->fields['nro_orden']?></a>
     </td>
    </tr>
   <?
     $resul_todos->MoveNext();
     }
    ?>
  </table>
 </td>
</tr>
<?
 }
 ?>
</table>
<input type=hidden name=items value="<?=$cantidad_productos?>">
<input type=hidden name=cantidad_filas value="<?=$cantidad_productos?>">
</form>
</td>
</tr>
</table>
<br>
<?}//del if ($nrocaso)

$sql = "select id_movimiento_material,nombre_cliente, cantidad, descripcion 
	    from mov_material.movimiento_material 
	    left join mov_material.detalle_movimiento 
	    using (id_movimiento_material) 
	    where idcaso=$id"; 
$result = sql($sql,"Error: no se puede traer el PM") or fin_pagina();
if ($result->RecordCount()>0){
?>
<table width=99% align=center cellpadding=0 cellspacing=6 border=1 bordercolor="#111111">
	<tr>
	 <td>
	  <table width="100%" align="center"> 
	   <tr>
	    <td id="ma_mg">
	     Pedidos de Material Vinculados al Caso
	    </td>
	   </tr>
	  </table>
 	 </td>
    </tr>
<tr>
 <td>
  <table width="100%" align="center">
   <tr id="mo">
    <td align="center" width="10%">
     Cantidad
    </td>
    <td align="center" >
     Descripción
    </td>
    <td align="center" width="20%">
     Número del P.M.
    </td>
   </tr>
   <?$i=0;
	 $result->moveFirst();
	 while (!$result->EOF){
     $tr_color=(((++$i)%2)==0)?$bgcolor1:$bgcolor2;
   ?>
    <tr bgcolor='<?= $tr_color ?>'>
     <td align="center" >
      <?=$result->fields['cantidad']?>
     </td>
     <td align="center">
      <?=$result->fields['descripcion']?>
     </td>
     <td align="center">
      <a href="<?=encode_link("../mov_material/detalle_movimiento.php",array("pagina"=>"listado","id"=>$result->fields["id_movimiento_material"]))?>" target="_blank"><?=$result->fields['id_movimiento_material']?></a>
     </td>
    </tr>
    <?
     $result->MoveNext();
     }
    ?>
  </table>
 </td>
</tr>
	
    
</table>
<?}?>
<br>
<?
$q = "SELECT subir_archivos.*,usuarios.nombre ||' '|| usuarios.apellido as nbre_completo ";
$q.= "FROM subir_archivos ";
$q.= "join usuarios on subir_archivos.creadopor=usuarios.login ";
$q.= "join archivos_casos using(id) ";
//$w = "where (acceso ilike '%|$_ses_user[login]|%' OR acceso='Todos' OR creadopor='$_ses_user[login]') and idcaso=$id";
$w = "where idcaso=$id";
$rs=sql($q.$w) or fin_pagina();
?>
<a name=archivos></a>
<table width=99% align=center cellpadding=0 cellspacing=6 border=1 bordercolor="#111111">
<tr> <td id="ma_mg" > Archivos </td> </tr>
<tr>
<td align=right>
<table width="100%">
<tr>
<td align="left">
<b><?=$msg ?></b>
</td>
<td align="right">
<input type="button" name="bagregar" value="Agregar Archivo" style="width:105" onclick="if (typeof(warchivos)=='object' && warchivos.closed || warchivos==false) warchivos=window.open('<?= encode_link($html_root.'/modulos/archivos/archivos_subir.php',array("onclickaceptar"=>"//window.opener.location.reload()","idcaso"=>$id,"proc_file"=>"../casos/caso_files_proc.php")) ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1'); else warchivos.focus()">
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td>
<table width='100%'  border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
<tr>
 <td colspan=7 style='border-right: 0;' id=ma style="text-align:left">
 <b>Total:</b><?=$total_archivos=$rs->recordcount() ?>
 </td>
<tr><td align=right id=mo>Archivo</td>
<td align=right id=mo>Fecha</td>
<!--<td align=right id=mo>Comentario</td>-->
<td align=right id=mo>Subido por</td>
<td align=right id=mo>Tamaño</td>
<td align=center id=mo>&nbsp;</td>
</tr>
<? while (!$rs->EOF) {
?>
    <tr style='font-size: 9pt' > <!-- bgcolor='#f0f0f0' -->
    <td align=center>
<?    
    if (is_file("../../uploads/archivos/".$rs->fields["nombre"]))
        echo "<a target=_blank href='".encode_link("../archivos/archivos_lista.php",array ("file" =>$rs->fields["nombre"],"size" => $rs->fields["size"],"cmd" => "download"))."'>";
    echo $rs->fields["nombre"]."</a></td>\n";
?>    
    <td align=center>&nbsp;<?= Fecha($rs->fields["fecha"]) ?></td>
<!--    <td align=center>&nbsp;<?= $rs->fields["comentario"] ?></td>-->
    <td align=center>&nbsp;<?= $rs->fields["nbre_completo"] ?></td>
    
    <td align=center>&nbsp;<?= $size=number_format($rs->fields["size"] / 1024); ?> Kb</td>
    <td align=center>
<?    
/*    if ($_ses_user_login==$rs->fields["creadopor"]) {
        echo "<a href='".encode_link("../archivos/archivos_modificar.php",array ("id" => $rs->fields["id"]))."'><img src='../../imagenes/modificar.gif' border=0 alt='Has click para modificar el archivo'></a> ";
        echo "<a href='".encode_link("../archivos/archivos_lista.php",array("id" => $rs->fields["id"],"cmd"=>"Eliminar"))."'><img src='../../imagenes/close1.gif' border=0 alt='Has click para eliminar el archivo'></a>";
    }
    else echo "&nbsp;";
*/
		$lnk=encode_link("$_SERVER[PHP_SELF]",Array("id"=>$id,"id_entidad"=>$id_cliente,"id_file"=>$rs->fields["id"],"filename"=>$rs->fields["nombre"]));
        echo "<a href='$lnk'><img src='../../imagenes/close1.gif' border=0 alt='Eliminar el archivo: \"". $rs->fields["nombre"] ."\"'></a>";

?>
    </td>
    </tr>
<?
    $rs->MoveNext();
}
?>
</table>
</td>
</tr>
</table>

<?=fin_pagina();// aca termino ?>