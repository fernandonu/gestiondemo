<?
/*
Autor: MAC
Fecha: 14/10/04

$Author: ferni $
$Revision: 1.6 $
$Date: 2006/01/12 19:34:01 $
*/

require_once("../../../config.php");

if ($_POST['dir_cambio']==1) unset($_POST["id_entidad"]);
if ($_POST["new_aceptar"]) {
   	echo $html_header;
	$id_entidad = $_POST["id_entidad"];
	//$new_tipo_entidad = $_POST["new_tipo_entidad"];
	$new_mant = $_POST["new_mant"];
	$new_forma = $_POST["new_forma"];
	$new_plazo_ent = $_POST["new_plazo_ent"];
	$new_numero = $_POST["new_numero"];
	$new_exp= $_POST["new_exp"];
	$new_valor = $_POST["new_valor"];
	$new_moneda = $_POST["new_moneda"];
	$new_ofertado = $_POST["new_ofertado"];
	$new_estimado = $_POST["new_estimado"];
	$new_ganado = $_POST["new_ganado"];
	$new_fecha_apertura = $_POST["new_fecha_apertura"];
	$new_hora_apertura = $_POST["new_hora_apertura"];
	$new_comentarios = $_POST["new_comentarios"];
	$nbre = $_POST["nbre"];
	$dir = $_POST["dir"];
	//dir entidad
	$new_dir=$_POST["dir_entidad"];
	$error = 0;
	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td colspan=2 align=center bgcolor=$bgcolor3><b>";
	
	//if ($new_tipo_entidad == "") { Error("Falta seleccionar el Tipo de Entidad"); }
	if ($new_valor == "") { $new_valor_ok="0"; } else { $new_valor_ok="$new_valor"; }
	if ($new_mant == "") { $new_mant_ok="NULL"; $new_mant_cond=" is "; } else { $new_mant_ok="'$new_mant'"; $new_mant_cond="="; }
	if ($new_forma == "") { $new_forma_ok="NULL"; $new_forma_cond=" is "; } else { $new_forma_ok="'$new_forma'"; $new_forma_cond="="; }
	if ($new_ofertado == "") { $new_ofertado_ok="0"; } else { $new_ofertado_ok=$new_ofertado; }
	if ($new_estimado == "") { $new_estimado_ok="0"; } else { $new_estimado_ok=$new_estimado; }
	if ($new_ganado == "") { $new_ganado_ok="0"; } else { $new_ganado_ok=$new_ganado; }
	if ($new_comentarios == "") { $new_comentarios_ok="NULL"; $new_comentarios_cond=" is "; } else { $new_comentarios_ok="'$new_comentarios'"; $new_comentarios_cond="="; }
	$new_plazo_ent = "'$new_plazo_ent'";
	$new_plazo_ent_cond="=";

	
	if (!$error) {
		$msg = "";
		$fecha_modif = date("Y-m-d H:i:s",mktime());
		$db->StartTrans();
		
		//controlamos si eligió el cliente. Si no lo hizo entonces
		//debemos insertar lo ingresado en la tabla pcpower_entidad
		if(!$id_entidad)
		{
	 	 $query="select nextval('pcpower_entidad_id_entidad_seq') as id_entidad";
	     $result=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer secuencia de pcpower_entidad");
	     $id_entidad=$result->fields['id_entidad'];

	     //traemos el id del distrito san luis
	     $query="select id_distrito from pcpower_distrito where nombre='San Luis'";
	     $distr=sql($query) or fin_pagina();
	     $id_distrito=$distr->fields['id_distrito'];
	     $insert="insert into pcpower_entidad(id_entidad,id_tipo_entidad,id_distrito,nombre,direccion)
	              values($id_entidad,1,$id_distrito,'$nbre','$dir')";
	     sql($insert) or fin_pagina();
	     
	     $insert="insert into pcpower_org_entidades(id_entidad,id_org) 
	              values($id_entidad,2)";
	     sql($insert) or fin_pagina();
		}	
		
		$query="select nextval('pcpower_licitacion_id_licitacion_seq') as id_licitacion";
		$result=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer secuencia de pcpower_licitacion");
		$new_lic_id=$result->fields['id_licitacion'];
		$sql = "insert into pcpower_licitacion (id_licitacion,id_entidad,";
		$sql .= "mant_oferta_especial,forma_de_pago,id_moneda,observaciones,id_estado,";
		$sql .= "ultimo_usuario,ultimo_usuario_fecha,plazo_entrega,dir_entidad,es_presupuesto) ";
		$sql .= "values ($new_lic_id,$id_entidad,";
		$sql .= "$new_mant_ok,$new_forma_ok,$new_moneda,";
		$sql .= "$new_comentarios_ok,10,'$_ses_user_name', '$fecha_modif',";
		$sql .= "$new_plazo_ent, '$new_dir',1)";
		$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error al insertar el presupuesto");
		
		if ($new_lic_id) {
			
			//agregamos una entrada para esta licitacion en la tabla entregar_lic
			$query="insert into pcpower_entregar_lic(id_licitacion,orden_subida,vence,mostrar,oferta_subida)values($new_lic_id,0,'01/01/2003',1,0)";
			$db->Execute($query) or die($db->ErrorMsg().$query);
			//agregamos una entrada para esta licitacion en la tabla candado
			$query="insert into pcpower_candado(id_licitacion,estado)values($new_lic_id,0)";
			$db->Execute($query) or die($db->ErrorMsg().$query);
		}
		//guardo la dirección de la entidad por defecto en la tabla entidad
		if ($_POST["guardar_dir"]=='SI'){ 
	    $query_ent="update pcpower_entidad set direccion='$new_dir' where id_entidad=$id_entidad";	
	    $rs = $db->Execute($query_ent) or die($db->ErrorMsg());
		}
		
		$db->CompleteTrans();
		if ($msg) {
			Error("No se pudo agregar el nuevo Presupuesto.<br>$msg");
		}
		else {
			$link = encode_link($html_root."/index.php", array("menu"=>"presupuestos/pcpower_presupuestos_view","extra"=>array("cmd1"=>"detalle","ID"=>$new_lic_id)));
			Aviso("Los datos se cargaron correctamente<br>ID Asignado al presupuesto: <a href='$link' target=_top><font size=4 color=#0000ff>$new_lic_id</font></a><br>");
			unset($_POST);
		}
	}
	echo "</b></td></tr></table>\n";
	form_new_lic();
}
else {
	form_new_lic();
	}
	
function form_new_lic() {
	global $html_header, $db, $bgcolor2, $bgcolor3, $html_root;
	echo $html_header;
	cargar_calendario();
	$new_distrito = $_POST["new_distrito"];
	$id_entidad = $_POST["id_entidad"];
	//$new_tipo_entidad = $_POST["new_tipo_entidad"];
	$new_mant = $_POST["new_mant"];
	$new_forma = $_POST["new_forma"];
	$new_plazo_ent = $_POST["new_plazo_ent"];
	$new_numero = $_POST["new_numero"];
	$new_exp= $_POST["new_exp"];
	$new_valor = $_POST["new_valor"];
	$new_moneda = $_POST["new_moneda"];
	$new_ofertado = $_POST["new_ofertado"];
	$new_estimado = $_POST["new_estimado"];
	$new_ganado = $_POST["new_ganado"];
	$new_fecha_apertura = $_POST["new_fecha_apertura"];
	$new_hora_apertura = $_POST["new_hora_apertura"];
	$new_comentarios = $_POST["new_comentarios"];
	$new_dir=$_POST["dir_entidad"];
	echo "<SCRIPT language='JavaScript' src='../licitaciones/funcion.js'></SCRIPT>";
    echo "<SCRIPT language='JavaScript'>";
    ?>
    
    var wcliente=0;

    function cargar_cliente()
    {
     document.all.id_cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
     document.all.cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].text;
     if (wcliente.document.all.chk_direccion.checked)
	  document.all.entrega.value=wcliente.document.all.direccion.value; 
    }
    
    function control_datos(){
       if(document.all.cliente_cargado.value!=1 && document.all.nbre.value=="")
           {
            alert('Debe elegir un cliente para este presupuesto');
            return false;
          }

    return true;
    }
    <?
    echo "</SCRIPT>";

	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<form action='pcpower_presupuestos_new.php' method=post>\n";
   echo "<input name='dir_cambio' type='hidden' value='0'>";
    echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=3>
	<b>Nuevo Presupuesto</b></td></tr>";
    echo "<tr>";
     echo "<td colspan=2>";
     echo "<font color=red>";
      echo "<b>No ingresar datos con comillas dobles (\"\")</b>  ";
     echo "</font>";
     echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td colspan='2'>";
?>
   <input type="hidden" name="cliente_cargado" value=0>
   <table width="100%" cellspacing="1" cellpadding="1" border="1">
    <tr> 
        <tr align="center"> 
  <? $link=encode_link('../pcpower/Entidades/pcpower_nuevo_cliente.php',array('pagina'=>'facturas'))?>
           <td colspan="3" height="20">
            <strong>Cliente </strong>
           </td>
          </tr>
          <tr> 
           <td colspan="2">
            <input type="hidden" name="id_entidad" value="<?=$id_entidad?>">
            <table align="center">
              <tr> 
               <td align="right"> 
                 <strong>Nombre&nbsp;&nbsp;</strong> 
                 <input name="nbre" type="text" value="<? if($cliente!=""){echo $cliente;}?>"  size="67">
              </td>
              <td align="left" width="80%"> 
                <?$link=encode_link('../Entidades/pcpower_nuevo_cliente.php',array('pagina'=>'facturas'))?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" name="clientes" value="Elegir cliente" title="Permite elegir cliente para la factura" onclick="if(wcliente==0 || wcliente.closed) wcliente=window.open(<?echo "'$link'"?>,'','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=190,top=0,width=600,height=550'); else wcliente.focus()"> 
               </td> 
             </tr>
            </table> 
           </td>  
          </tr>
          <tr align="left"> 
            <td height="30" colspan="3"> <strong>Dirección</strong> 
              <input name="dir" type="text"  value="<?= $direccion ?>"   size="67">
            </td>
          </tr>
        <!--  <tr> 
            <td width="52%" height="24" align="left" nowrap><strong>C.U.I.T</strong> 
              &nbsp; &nbsp; 
              <input name="cuit" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $cuit ?>"  <? if (!$can_finish) echo " disabled " ?> size="18" > </td>
            <td width="48%" height="24" colspan="2" align="left" nowrap>&nbsp; 
              <strong>I.I.B.</strong> 
              <input name="iib" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $iib ?>" <? if (!$can_finish) echo " disabled " ?> size="17" > </td>
          </tr>
          <tr align="left"> 
            <td height="35" colspan="3"><strong>Condición I.V.A</strong> 
              <input name="condicion_iva" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $condicion_iva ?>"  <? if (!$can_finish) echo " disabled " ?> size="20" >
              &nbsp;&nbsp; <strong> I.V.A %</strong> 
              <input name="iva" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $iva ?>"  <? if (!$can_finish) echo " disabled " ?> size="6" ></td>
          </tr>
          <tr> 
            <td height="60" colspan="3"> 
              <div align="left"> <strong>Otros</strong> 
                <textarea name="otros" title="Para editar los campos del cliente presione el boton elegir cliente"  cols="50" rows="3" wrap="VIRTUAL" <? if (!$can_finish) echo " disabled " ?>><?= $otros ?></textarea>
              </div></td>
          </tr>-->
  </table>   
    
<?      
    echo "</td>";
    echo "</tr>";	
	echo "<tr>\n";
	echo "<td align=left>";
	echo "<table width=100%><tr>";
	echo "<td align=right width=50% nowrap><b>Mantenimiento de oferta:</b></td>\n";
	echo "<td align=left width=50%>";
	echo "<select name=new_mant OnChange='beginEditing(this);'>";
	echo "<option></option>";
	$array_mant = array("5 días hábiles",
						"5 días corridos",
						"10 días hábiles",
						"10 días corridos",
						"15 días hábiles",
						"15 días corridos",
						"30 días hábiles",
						"30 días corridos",
						"60 días hábiles",
						"60 días corridos",
						"90 días hábiles",
						"90 días corridos");
	foreach ($array_mant as $key => $val) {
		echo "<option value='$val'";
		if ($key == 6) echo " selected";
		echo ">$val";
	}
	echo "<option id=editable>Edite aquí";
	echo "</select></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right nowrap><b>Forma de pago:</b></td>\n";
	echo "<td align=left>";
	echo "<select name=new_forma OnChange='beginEditing(this);'>";
	echo "<option></option>";
	$array_forma = array("30 dias a partir de la recepcion definitiva",
                        "Contado contra entrega",
						"10 días de la fecha de entrega",
						"10 días de la recepción de los bienes",
						"15 días de la fecha de entrega",
						"15 días de la recepción de los bienes",
						"30 días de la fecha de entrega",
						"30 días de la recepción de los bienes",
						"60 días de la fecha de entrega",
						"60 días de la recepción de los bienes"
                        );
	foreach ($array_forma as $key => $val) {
		echo "<option value='$val'";
		if ($key == 0) echo " selected";
		echo ">$val";
	}
	echo "<option id=editable>Edite aquí";
	echo "</select></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right nowrap><b>Plazo de entrega:</b></td>\n";
	echo "<td align=left>";
	echo "<select name=new_plazo_ent OnChange='beginEditing(this);'>";
	echo "<option></option>";
	$array_plazo = array("Inmediato",
						"5 días corridos",
						"5 días hábiles",
						"10 días corridos",
						"10 días hábiles",
						"15 días corridos",
						"15 días hábiles",
						"30 días corridos",
						"30 días hábiles",
						"45 días corridos",
						"45 días hábiles");
	foreach ($array_plazo as $key => $val) {
		echo "<option value='$val'";
		if ($key == 0) echo " selected";
		echo ">$val";
	}
	echo "<option id=editable>Edite aquí";
	echo "</select></td>\n";
	echo "</tr></table>\n";
	echo "</td><td align=center valign=top>";
	echo "<table width=100%><tr>";
	/*echo "<td align=right><b>Número:</b></td>\n";
	echo "<td align=left width=10%><input type=text name=new_numero value='$new_numero'></td>\n";
	echo "</tr> ";*/
	echo "<tr><td align=right width=10%><b>Moneda:</b></td>\n";
	echo "<td align=left>";
	echo "<select name=new_moneda>\n";
	$result1 = $db->Execute("select id_moneda,nombre from pcpower_moneda") or die($db->ErrorMsg());
	while (!$result1->EOF) {
		echo "<option value='".$result1->fields["id_moneda"]."'";
		if ($result1->fields["id_moneda"] == $new_moneda) echo " selected";
		echo ">".$result1->fields["nombre"]."\n";
		$result1->MoveNext();
	}
	echo "</select>\n";
	echo "</tr>";
	echo "</table>\n";	
	echo "<tr>\n";
	echo "<td align=left valign=top colspan=4>";
	echo "<table width=100%><tr>";
	echo "<td valign=top align=right><b>Comentarios/Seguimiento:</b></td>";
	echo "<td align=left><textarea name='new_comentarios' cols=70 rows=10>$new_comentarios</textarea></td>\n";
	echo "</tr></table></td>";
	echo "</tr><tr>\n";
	echo "<td style=\"border:$bgcolor3;\" colspan=2 align=center><br>\n";
	echo "<input type=submit name=new_aceptar value='Aceptar' onclick='return control_datos();'>&nbsp;&nbsp;&nbsp;\n";
	echo "<input type=reset name=new_cancelar value='Cancelar'><br><br>\n";
	echo "</td>";
	echo "</tr>\n";
	echo "</table><br>\n";
}
?>