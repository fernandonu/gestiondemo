<?
/*
Autor(?): cestila
$Author: nazabal $
$Revision: 1.35 $
$Date: 2007/05/02 21:29:16 $
*/
include "head.php";
if ($_POST["cmd1"]=="Guardar >>") {
	// Valores del formulario
	//print_r($_POST);
	while (list($key,$cont)=each($_POST)){
		$$key=$cont;
		} //del while
	$error="";
	$db->StartTrans();
	$error="";
	if (!$cliente)
		$error.="Debe seleccionar un cliente<br>";
	if ($nuevocliente==1) {
		if (!$contacto)
			$error.="El Organismo no tiene un contacto.<br>";
		if (!$dependencia)
			$error.="Falta dependencia.<br>";
		if (!$direccion)
			$error.="Falta el domicilio del cliente.<br>";
		if (!$telefono)
			$error.="Olvido colocar el Teléfono del contacto.<br>";
		if (!es_numero($provincia)) $provincia="NULL";
		$sql="INSERT INTO dependencias ";
		$sql.="(id_entidad,dependencia,contacto,direccion,lugar,cp,id_distrito,telefono,mail) VALUES ";
		$sql.="($cliente,'$dependencia','$contacto','$direccion','$lugar','$cp',$provincia,'$telefono','$mail')";
		$db->execute($sql) or die($db->errormsg()." - ".$sql);
		$sql="Select id_dependencia from dependencias order by id_dependencia DESC limit 1 offset 0";
		$rs=$db->execute($sql) or $error.=$db->errormsg()." - ". $sql;
		$chdependencia=$rs->fields["id_dependencia"];
		} //fin de insetar la dependencia

	if (fechaok($fechainicio)) {
		$fechainicio=fecha_db($fechainicio);
		}
		else {
			 $error="La Fecha no es válida <br>";
			 }
	$nrocaso=date("Ymd");
	$sql="select nrocaso from casos_cdr order by nrocaso DESC LIMIT 1 OFFSET 0";
	$rs=$db->execute($sql);
	$extra = 1 + substr($rs->fields["nrocaso"],8);
	$extra1=str_pad($extra,5,"0",STR_PAD_LEFT);
	$nrocaso .= $extra1;

	if (!$error) {
		$sql="INSERT INTO casos_cdr ";
		$sql.="(nrocaso,idate,fechainicio,id_dependencia,nserie,deperfecto,idestuser,id_usuario,fecha,sync) ";
		$sql.="VALUES ('$nrocaso',$atendido,'$fechainicio',$chdependencia,'$serie','$defecto',1,".$_ses_user["id"].",'".date ("Y-m-d H:i:s")."',1)";
		$db->execute($sql) or $error.=$db->errormsg()." - ". $sql;
	}

	if (!$error) { 
		Aviso ("Los datos se guardaron satisfactoriamente");
		$db->CompleteTrans();
		
		//Avisar cuando se abre un caso de la AFIP porque nos cobran multa
		$sql = "SELECT m.nro_serie, o.id_entidad, o.id_licitacion
				FROM ordenes.maquina m
				LEFT JOIN ordenes.orden_de_produccion o USING (nro_orden)
				WHERE m.nro_serie = '$serie' AND o.id_licitacion = 3344";
		$serie_afip=$db->execute($sql) or $error.=$db->errormsg()." - ". $sql;
		if (!$error) {
			if ($serie_afip->RecordCount() > 0) {
				$para = "juanmanuel@coradir.com.ar, adrian@coradir.com.ar";
			    $asunto="Nuevo caso de la AFIP (Revisar porque cobran multa)";
			    $mensaje="Se cargo un nuevo caso de la AFIP (ID 3344)";
			    $mensaje.="\n\n";
			    $mensaje.="\n-------------------------- Descripción del Caso --------------------------";
			    $mensaje.="\nNro. de Caso:   ".$nrocaso;
			    $mensaje.="\nFecha Inicio:   ".Fecha($fechainicio);
			    $mensaje.="\nNro. de Serie:  ".$serie;
			    $mensaje.="\nDefecto:        ".$defecto;
			    $mensaje.="\n--------------------------------------------------------------------------";
			    $mensaje.="\n\nEl Tiempo de Respuesta máximo será de veinticuatro (24) horas hábiles (Lunes a Viernes de 8.00 hs a 18.00 hs).";
			    $mensaje.="\nEl Tiempo de Reparación máximo será de cuarenta y ocho (48) horas hábiles (Lunes a Viernes de 8.00 hs a 18.00 hs).";
			    $mensaje.="\n\nServicio Conexo de Buen Funcionamiento:";
                $mensaje.="\n\n      La demora en el cumplimiento de los tiempos de Respuesta y/o de Reparación o cualquier otra obligación ";
                $mensaje.="\nemergente por este servicio, autorizará al Comprador a aplicar una multa de 0.10 % (diez milésimos) del costo total del equipo en cuestión  por cada día de retraso.";
				enviar_mail($para,$asunto,$mensaje,"","","",0);				
			}
		}
		
	}
	else { error($error); }

	$sq="select idcaso from casos_cdr where nrocaso='$nrocaso'";
	$tmp=sql($sq) or fin_pagina();
	$ref = encode_link("caso_estados.php",Array("id"=>$tmp->fields["idcaso"],"id_entidad"=>$cliente));
	//////////////////////////////////////////// GABRIEL /////////////////////////////////////////////
	//inserción (si se da la condición) en la tabla del módulo de "GERENCIA S.T."
	//////////////////////////////////////////////////////////////////////////////////////////////////
	// control por PC
	$consulta="select cc.idcaso, cc.nrocaso, cc.nserie, op.nro_orden
		from casos.casos_cdr cc,
			ordenes.orden_de_produccion op
		where ((nserie >= op.nserie_desde)and(nserie <= op.nserie_hasta))
			and (nserie='".$serie."')";
	$rta_consulta=sql($consulta, "c77") or fin_pagina();
	$nro_de_orden=$rta_consulta->fields["nro_orden"];
	if ($rta_consulta->recordCount()>=3){
		$rta_consulta2=sql("select * from casos.gerencia_st where nro_serie='".$serie."'", "c79") or fin_pagina();
		if ($rta_consulta2->recordCount()==0){
			$consulta="insert into casos.gerencia_st (nro_orden, nro_serie, estado_gst, tipo_gst, fallos) values
			(null, '".$serie."', 'p', 'c', ".$rta_consulta->recordCount().")";
			$rta_consulta=sql($consulta, "c83") or fin_pagina();
		}else{
			$consulta="update casos.gerencia_st set fallos=".$rta_consulta->recordCount()." where id_gerencia_st=".$rta_consulta2->fields["id_gerencia_st"];
			$rta_consulta=sql($consulta, "c88") or fin_pagina();
		}
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////
	// control por Orden de producción
	if ($nro_de_orden){
		$consulta="select casos_op, cantidad_op, (casos_op/(cantidad_op*1.0)) as porcentaje_fallas
			from
					(
						select count(distinct(cc.nserie)) as casos_op, op.nro_orden 
						from casos.casos_cdr cc, ordenes.orden_de_produccion op
						where ((cc.nserie >= op.nserie_desde)and(cc.nserie <= op.nserie_hasta))
						group by nro_orden
					)as cantidad_casos
				join 
					(
						select nro_orden, cantidad as cantidad_op
						from ordenes.orden_de_produccion
					)as cantidad_pcs
				using (nro_orden)
			where nro_orden=".$nro_de_orden."
			group by casos_op, cantidad_op";
		$rta_consulta=sql($consulta, "c110") or fin_pagina();
		
		if ($rta_consulta->fields["porcentaje_fallas"]>=0.3){
			$rta_consulta2=sql("select * from casos.gerencia_st where nro_orden='".$nro_de_orden."'", "c113") or fin_pagina();
			if ($rta_consulta2->recordCount()==0){
				$consulta="insert into casos.gerencia_st (nro_orden, estado_gst, tipo_gst, fallos) values
				(".$nro_de_orden.", 'p', 'p', ".$rta_consulta->fields["porcentaje_fallas"].")";
				$rta_consulta=sql($consulta, "c117") or fin_pagina();
			}else{
				$consulta="update casos.gerencia_st set fallos=".$rta_consulta->fields["porcentaje_fallas"]." where id_gerencia_st=".$rta_consulta2->fields["id_gerencia_st"];
				$rta_consulta=sql($consulta, "c120") or fin_pagina();
			}
		}
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////
	echo "<script>window.location='$ref';</script>\n";
} //del if de guardar



$sql="select nrocaso from casos_cdr order by nrocaso DESC limit 1 offset 0";
$rs=$db->execute($sql) or die($db->errormsg());
$ca = substr($rs->fields["nrocaso"],8,5) + 1;
$ca = date("Ymd"). str_pad($ca,5,"0",STR_PAD_LEFT);

$cliente=$_POST["cliente"];
$chdependencia=$_POST["chdependencia"];
if ($_POST["cmdcliente"]=="cambio") {
	$sql="select id_dependencia from dependencias where id_entidad=$cliente";
	$rs=sql($sql);
	if ($rs->recordcount()>0) $chdependencia=$rs->fields["id_dependencia"];
}
if (es_numero($chdependencia)) {
   $sql="select dependencias.id_distrito,dependencias.dependencia,dependencias.direccion,
   dependencias.lugar as localidad,dependencias.cp as cod_pos,
   dependencias.contacto,dependencias.telefono,
   dependencias.mail from dependencias left join entidad USING (id_entidad)
   where dependencias.id_dependencia=$chdependencia";
   $rs=$db->execute($sql) or die($db->errormsg(). " - " . $sql);
//   $organismo=$rs->fields["organismo"];
   $dependencia=$rs->fields["dependencia"];
   $direccion=$rs->fields["direccion"];
   $lugar=$rs->fields["localidad"];
   $cp=$rs->fields["cod_pos"];
   $provincia=$rs->fields["id_distrito"];
   $contacto=$rs->fields["contacto"];
   $telefono=$rs->fields["telefono"];
   $mail=$rs->fields["mail"];
}
?>
<script language='javascript' src='../../lib/popcalendar.js'></script>
<script language='javascript'>
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





 if(no_error==1)
  return true;
}//fin control datos

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

function cargar_dependencia()
{var i,encontro;
   document.all.dependencia.value="";
   document.all.lugar.value="";
   document.all.telefono.value="";
   document.all.mail.value="";
   document.all.contacto.value="";
   document.all.cp.value="";
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
function cargar()
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
   i++;
  }
  //dependencia=ventana_cliente.dependencias;
  document.all.cliente.value=ventana_cliente.document.all.select_cliente.options[ventana_cliente.document.all.select_cliente.selectedIndex].value;
  document.all.nombre_cliente.value=ventana_cliente.document.all.nombre.value;
  document.all.direccion_cliente.value=ventana_cliente.document.all.direccion.value;
  document.all.telefono_cliente.value=ventana_cliente.document.all.telefono.value;
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
 document.all.direccion_cliente.value=ventana_maquina.document.all.direccion.value;
 document.all.telefono_cliente.value=ventana_maquina.document.all.telefono.value;
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
<br><table align=center border=0 cellspacing=0 cellpadding=0 width=100%>
<tr>
<td>
<center>
<form action='caso_nuevo.php' method='POST' name=frm id=frm>
<input type=hidden name=cmdcliente value="No">
<div align="center">
  <center>
<table align=center border=1 cellspacing=0 cellpadding=3 bgcolor=<? echo $bgcolor2; ?> width=99%>
 <tr>
	<td style="border:<? echo $bgcolor3; ?>;" colspan=4 align=center id=mo><font size=3>
		<b>Formulario de apertura de CAS</b>
	</td>
 </tr>
 <tr>
  <td colspan=4 align=center>
   Nro de C.A.S.: <b><? echo $ca; ?></b>
  </td>
 </tr>
  <tr>
     <td>
      <p align="right" title="Haga click para ingresar maquina"><font face="Trebuchet MS" size="2" onclick="if (document.all.serie.value!='') ventana_maquina=window.open('<?=encode_link('caso_elegir_maquina.php',array('onclickcargar'=>'window.opener.cargar_maquina();','onclicksalir'=>'window.close()'))?>&serie='+document.all.serie.value,'','left=40,top=80,width=700,height=280,resizable=1,status=1'); else alert('Por favor ingrese algun dato para realizar la busqueda');" style="cursor:hand;"><u>Número de Serie</u><b><font color="#FF0000"> * </font>
</b>:
      </font>
     </td>
     <td>
      <input type=text name=serie value='<? echo $serie ?>' size=30>
     </td>
     <td colspan="2">
     &nbsp;
     </td>
    </tr>
 <tr>
  <td colspan=4>
   <table width=100%>
    <tr>
     <td>
	  <p align="right"><font face="Trebuchet MS" size="2"><a href="<? echo encode_link("caso_ate.php",ARRAY());?>" target="nuevo"><font face="Trebuchet MS" color="Black" size="2"><U>Atendido por</U></a><font color="#FF0000"><b> *
</b> </font>
      : </font>
     </td>
     <td>
      <select name=atendido onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()'>
<?
$sql1="select idate,nombre from cas_ate where activo=1 order by nombre";
$rs1=$db->execute($sql1) or die($db->errormsg());
$cas_ate=array();
while ($fila=$rs1->fetchrow()) {
		if (eregi("^Coradir.*$",$fila['nombre'])) {
	       echo "<option value='".$fila['idate']."'";
		   if ($fila['idate']==$atendido) echo " selected";
		   echo ">".$fila['nombre']."</option>\n";
		}
		else {
			$cas_ate[$fila['idate']] = $fila['nombre'];
		}
	}
foreach ($cas_ate as $idate_cas => $nombre_cas) {
	       echo "<option value='".$idate_cas."'";
		   if ($idate_cas==$atendido) echo " selected";
		   echo ">".$nombre_cas."</option>\n";
}
?>
     </td>
     <td>
	  <p align="right"><font face="Trebuchet MS" size="2">Fecha Inicio<font color="#FF0000"><b> *
</b> </font>
      : </font>
     </td>
     <td>
<?
$fechainicio=$_POST["fechainicio"];
if (!$fechainicio) $fechainicio=date("d/m/Y");
?>
      <input type=text name=fechainicio value='<? echo $fechainicio; ?>' size=15 readonly>
<?
echo link_calendario("fechainicio");
?>
     </td>
	</tr>
   </table>
  </td>
 </tr>
 <tr>
  <td style="border:<? echo $bgcolor3; ?>;" align=center>
   <b>
   <font face="Trebuchet MS" color="#009900">Datos del cliente</font></b>
  </td>
 </tr>
 <tr>
  <td colspan="4">
  <table width=100%>
  <tr>
  <td width="1%">
 <?
 if($cliente!="")
 {
 $sql1="select nombre,direccion,telefono from entidad where id_entidad=$cliente";
 $rs_cliente=$db->execute($sql1) or die($db->errormsg(). " - ".$sql1);
 }
 ?>
 <font face="Trebuchet MS" size="2" onclick="ventana_cliente=window.open('<?=encode_link('caso_elegir_cliente.php',array('onclickcargar'=>'window.opener.cargar();','onclicksalir'=>'window.close()'))?>','','left=40,top=80,width=700,height=350,resizable=1');" style="cursor:hand;" title="Haga click para ingresar cliente"><u>Cliente</u></font> <font color="#FF0000"><b> *</b> </font>
 <input type="text" name="nombre_cliente" value="<?=$rs_cliente->fields['nombre']; ?>" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;" size="100" readonly>
 <input type="hidden" name="cliente" value="">
  </td>
  </tr>
<tr>
<td>
<b>Direccion <input type="text" name="direccion_cliente" value="<?=$rs_cliente->fields['direccion']; ?>" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;" size="100" readonly>
 </td>
</tr>
<tr>
<td>
<b>Telefono <input type="text" name="telefono_cliente" value="<?=$rs_cliente->fields['telefono']; ?>" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;" size="100" readonly>
 </td>
</tr>
</table>
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
$rs1=$db->execute($sql1) or die($db->errormsg(). " - ".$sql1);

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
   </td>
  </tr>
  <tr>
	 <td colspan=4 align=left>
<?
echo "<input type=checkbox name=nuevocliente value='1' size=25";
if ($nuevocliente)
	echo " checked";
echo ">\n";
?>
	  <font face="Trebuchet MS" size="2">Agregar dependencia.</font>
	 </td>
	</tr>
	<tr>
	 <td>
	  <font face="Trebuchet MS" size="2">Dependencia<b><font color="#FF0000"> * </font>
</b>:
	  </font>
	 </td>
	 <td>
	  <input type=text name=dependencia value='<? echo $dependencia ?>' size=25>
	 </td>
	 <td>
	  <font face="Trebuchet MS" size="2">Contacto<b><font color="#FF0000"> * </font>
</b>:
      </font>
     </td>
     <td>
      <input type=text name=contacto value='<? echo $contacto ?>' size=25>
     </td>
    </tr>
    <tr>
     <td>
      <font face="Trebuchet MS" size="2">Dirección<b><font color="#FF0000"> * </font>
</b>: </font>
     </td>
     <td>
      <input type=text name=direccion value='<? echo $direccion ?>' size=25>
     </td>
     <td>
      <font face="Trebuchet MS" size="2">Lugar/Ubicación: </font>
     </td>
     <td>
      <input type=text name=lugar value='<? echo $lugar ?>' size=25>
     </td>
    </tr>
    <tr>
     <td>
      <font face="Trebuchet MS" size="2">Codigo Postal: </font>
     </td>
     <td>
      <input type=text name=cp value='<? echo $cp ?>' size=25>
     </td>
     <td>
      <font face="Trebuchet MS" size="2">Provincia</font>
     </td>
	 <td>
	  <select name=provincia>
	  <option>&nbsp;</option>
<?
$sql1="select nombre,id_distrito from distrito order by nombre";
$rs1=$db->execute($sql1) or die($db->errormsg());
while ($fila=$rs1->fetchrow()) {
	   echo "<option value='".$fila['id_distrito']."' ";
	   if ($fila["id_distrito"]==$provincia) echo "selected";

	   echo ">".$fila['nombre']."</option>\n";
}
?>
	 </td>
	</tr>
    <tr>
     <td>
      <font face="Trebuchet MS" size="2">Teléfono<b><font color="#FF0000"> * </font>
</b>: </font>
     </td>
     <td>
      <input type=text name=telefono value='<? echo $telefono ?>' size=25>
     </td>
     <td>
      <font face="Trebuchet MS" size="2">E-M@il: </font>
     </td>
     <td>
      <input type=text name=mail value='<? echo $mail ?>' size=25>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td style="border:<? echo $bgcolor3;?>;"align=left>
   <b>
   <font face="Trebuchet MS" color="#009900">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   &nbsp;&nbsp;&nbsp;&nbsp;Datos del&nbsp; equipo</font></b>
  </td>
 </tr>
 <tr>
  <td>
   <table width=100%>
    <tr>
     <td colspan=2 width=40% valign=top>
      <font face="Trebuchet MS" size="2">Desperfecto: </font>
     </td>
	</tr>
	<tr>
     <td colspan=2>
      <textarea name=defecto rows=8 cols=100><? echo $defecto ?></textarea>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td style="border:<?echo $bgcolor3;?>;"align=center>
   <input type=submit name=cmd1 value='Guardar >>' onclick="return control_datos();">
  </td>
 </tr>
</table>
</center>
</div>
</td>
</tr>
<tr>
 <td>
<p align="left"><font face="Trebuchet MS" size="2">Para abrir un nuevo caso
complete los datos, tenga en cuenta que cuanto mayor sea la información sobre
este cliente más fácil se realizará el seguimiento del caso.&nbsp; <br></br>
<font color="#993300">
<b>NOTA</b>:</font> Los campos marcados con<b><font color="#FF0000"> * </font>
</b>(asterisco) son indispensables para abrir el caso.</font></p>
</td>
</tr>
</table>