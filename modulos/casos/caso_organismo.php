<?
/*
$Author: ferni $
$Revision: 1.20 $
$Date: 2006/12/05 17:40:34 $
*/
require_once("../../config.php");
if ($parametros['cmd']=="download") {
    $file=$parametros["name"];
    $size=$parametros["size"];
    $path=$parametros["path"];
    Mostrar_Header($file,"application/octet-stream",$size);
    $filefull = "$path/$file";
    readfile($filefull);
    die;
}
include "head.php";

if ($parametros['cmd']=="delete") {
	$q="select * from archivos_cas where id=$parametros[id]";
	$rs=sql($q) or fin_pagina();;
	$path=CAS_DIR."/".$rs->fields["idate"]."/";	
    if (!unlink("$path/".$rs->fields["nombre"]))
         $error="No se encontro el archivo";
    $sql="delete from archivos_cas where id=$parametros[id]";
    sql($sql) or fin_pagina();
    if ($error)
        error($error);
}

//Valores generales de los datos
//print_r($parametros);
if ($parametros["idate"]) {
  $sql = "select * from cas_ate where idate=".$parametros["idate"];
  $result_consulta = sql($sql) or fin_pagina();
 }	

$idate     = $result_consulta->fields["idate"]    or $idate=$_POST["idate"];
$organismo = $result_consulta->fields["nombre"]   or $organismo=$_POST["organismo"];
$contacto  = $result_consulta->fields["contacto"] or $contacto=$_POST["contacto"];
$telefono  = $result_consulta->fields["tel"]      or $telefono=$_POST["telefono"];
$mail      = $result_consulta->fields["mail"]     or $mail=$_POST["mail"];
$direccion = $result_consulta->fields["direccion"]or $direccion=$_POST["direccion"];
$comentario= $result_consulta->fields["comentario"]or $comentario=$_POST["comentario"];
$activo    = $result_consulta->fields["activo"]   or $activo=$_POST["activo"];
$ciudad    = $result_consulta->fields['ciudad']   or $ciudad=$_POST['ciudad'];
$provincia = $result_consulta->fields['id_distrito'] or $provincia=$_POST['provincia'];
$codigo_postal = $result_consulta->fields['cp'] or $codigo_postal=$_POST['cp'];
$mic       = $result_consulta->fields['mic'] or $mic=$_POST['mic'];
$msn       = $result_consulta->fields['msn'] or $msn=$_POST['msn'];
$icq       = $result_consulta->fields['icq'] or $icq=$_POST['icq'];
$estado    = $parametros["estado"] or $estado=$_POST["estado"];
$fecha_inicio = $result_consulta->fields['fecha_inicio'] or $fecha_inicio = $_POST['fecha_inicio'];

if ($_POST["eliminar"]=="Eliminar") {
    if (!$idate)
         $error="No a seleccionado ningun organismo.<br>";
    $sql="DELETE FROM cas_ate where idate=$idate";
    if (!$error){
         sql($sql) or fin_pagina();
         $organismo="";
         $contacto="";
         $telefono="";
         $mail="";
         $direccion="";
         $comentario="";
         $activo=0;
         $ciudad=" ";
         $provincia=" ";
         $codigo_postal=" ";
    }
    else
        error($error);
?>
<script>
window.opener.location.reload();
window.close();
</script>
<?
}
if ($_POST["nuevo"]=="Modificar") {
    $error="";
    if (!$organismo)
         $error.="Debe cargarse el organismo.<br>";
    if (!$activo)
        $activo=0;
     
    $fecha = Fecha_db($fecha_inicio);    
    
    $sql="UPDATE cas_ate set tel='$telefono',contacto='$contacto',mail='$mail',direccion='$direccion',
          comentario='$comentario',nombre='$organismo',activo=$activo, id_distrito=$provincia, 
          ciudad='$ciudad', cp='$codigo_postal', mic='$mic',msn='$msn',icq='$icq',";
   if ($fecha)
      $sql.=" fecha_inicio='$fecha'";
   else 
      $sql.=" fecha_inicio=NULL";
   $sql.=" WHERE idate=$idate";
   
    if (!$error){
         sql($sql) or fin_pagina();;
		if ($_POST["comentario_nuevo"] != "") {
			$sql = nuevo_comentario($idate,"CASOS",$_POST["comentario_nuevo"]);
			sql($sql) or fin_pagina();
		}
         $organismo="";
         $contacto="";
         $telefono="";
         $mail="";
         $direccion="";
         $comentario="";
         $activo=0;
         $ciudad=" ";
         $provincia=" ";
         $codigo_postal=" ";
         $fecha_inicio="";
    }
    else
        error($error);
?>
<script>
window.opener.location.reload();
window.close();
</script>
<?
}
if ($_POST["nuevo"]=="Nuevo") {
    $error="";
    if (!$organismo)
         $error.="Falta el organismo.<br>";
    if (!$activo)
        $activo=0;
    if (!$error){
         $db->starttrans();
         $sql="Select nextval('proveedor_id_proveedor_seq') as id_proveedor";
         $res=sql($sql) or fin_pagina();
         $id_proveedor=$res->fields["id_proveedor"];
         $fecha = Fecha_db($fecha_inicio);
         $sql="INSERT into proveedor (id_proveedor,razon_social,observaciones)
               VALUES ($id_proveedor,'$organismo','$comentario')";
         sql($sql) or fin_pagina();
         $sql="INSERT INTO cas_ate (nombre,contacto,direccion,mail,tel,comentario,activo,id_distrito,ciudad,cp,id_proveedor,mic,msn,icq,fecha_inicio)
              VALUES ('$organismo','$contacto','$direccion','$mail','$telefono','$comentario',$activo,$provincia,'$ciudad','$codigo_postal',$id_proveedor,'$mic','$msn','$icq','$fecha')";

         sql($sql) or fin_pagina();
         $organismo="";
         $contacto="";
         $telefono="";
         $mail="";
         $direccion="";
         $comentario="";
         $activo=0;
         $ciudad=" ";
         $provincia=" ";
         $codigo_postal=" ";
         $fecha_inicio="";
         $db->completetrans();
    }

    else
        error($error);
?>
<script>
window.opener.location.reload();
window.close();
</script>



<?
}
?>
<script>
function control_datos()
{if(document.all.organismo.value=="")
 {alert('Debe especificar un C.A.S');
  return false;
 }
 if(document.all.ciudad.value=="")
 {alert('Debe especificar una Ciudad');
  return false;
 }
 if(document.all.provincia.value=="-1")
 {alert('Debe especificar una Provincia');
  return false;
 }
 return true;
}
var warchivos=false; //ventana para agregar archivos

</script>
<script language='javascript' src='../../lib/popcalendar.js'></script>
<form action='caso_organismo.php' method='POST' name=frm id=frm>
<input type="hidden" name="idate" value="<? echo $idate; ?>">
<table width=100% class="bordes">
<tr> <td id="ma_mg" >Administración de entidades encargadas de atender los CAS.</td></tr>
<tr> <td id="ma" >Tenga en cuenta la importancia requerida en estas entidades.</td></tr>
<tr>
<td><br>
<div align="center">
 <table width="80%" class="bordes">
 <tr>
  <td align=center id=ma_mg>
    Modificar datos de los Organismos
  </td>
 </tr>
 <tr>
  <td>
   <table class="bordes" width=100%>
    
    <tr>
    	<td width="40%" align="right">
    		<b>C A S<font color="#FF0000"><b> * </b> </font>: </b>
    		<input type=text name=organismo value='<?echo $organismo;?>' size=37>
    		<?$link8=encode_link("word_etic_cas.php", array("cas"=>$organismo,"dir"=>$direccion,"ciu"=>$ciudad,"prov"=>$provincia,"cp"=>$codigo_postal,"contacto"=>$contacto,"tel"=>$telefono,"formato"=>'CASO'));	
       		?>
       		<A target='_blank' href='<?=$link8?>'><IMG src='<?=$html_root?>/imagenes/word.gif' height='16' width='16' border='0'></a>       			
    	</td>
    	<td width="40%" align="right">
    		<b>Fecha Inicio: </b>
    		<input type=text name=fecha_inicio value='<?echo fecha($fecha_inicio);?>' size=10>
    		<?
    		echo link_calendario("fecha_inicio");
    		?>
    	</td>    	
    </tr>
    
    <tr>
    	<td width="40%" align="right" colspan="2">
    		<b>Contacto: </b>
    	    <input type=text name=contacto value='<?echo $contacto;?>' size=105>
    	</td>
    </tr>
     
    <tr>
    	<td width="40%" align="right" colspan="2">
    		<b>Dirección: </b>
      		<input type=text name=direccion value='<?echo $direccion;?>' size=105>
    	</td>
    </tr>
    
    <tr>
     <td width=40% align="right">
      <b>Teléfono: </b>
      <input type=text name=telefono value='<?echo $telefono;?>' size=41>
     </td>
     <td width=40% align="right">
      <b>Codigo Postal: </b>
      <input type=text name=cp value='<?echo $codigo_postal;?>' size=41>
     </td> 
    </tr>
    
    <tr>
     <?
      $query="select * from distrito order by nombre";
      $resultado_provincia = sql($query) or fin_pagina();
      $cantidad=$resultado_provincia->RecordCount();
     ?>
     <td width=40% align="right">
      <b>Provincia<font color="#FF0000"><b> *</b> </font>: </b>
      <select name="provincia" style="width=262">
      <option selected value=-1>Elija una Provincia</option>
      <?
       while(!$resultado_provincia->EOF)
       {if ($provincia==$resultado_provincia->fields['id_distrito'])
        $selected="selected";
        else 
        $selected=" ";
      ?>
       <option <?=$selected?> value="<?=$resultado_provincia->fields['id_distrito']?>" >
       <?=$resultado_provincia->fields['nombre']?></option>
      <?
       $resultado_provincia->MoveNext();
       }
     
      ?>
      </select>
     </td> 
     <td width=40% align="right">
	  <b>Ciudad <font color="#FF0000"><b> * </b> </font>: </b>
	  <input type=text name=ciudad value='<?= $ciudad;?>' size=41>
     </td>
    </tr>
    
    
    <tr>
     <td width=40% align="right">
      <b>E-Mail: </b>
      <input type=text name=mail value='<?echo $mail;?>' size=41>
     </td>
     <td width=40% align="right">
      <b>MIC: </b>
      <input type=text name=mic value='<?echo $mic;?>' size=41>
     </td>     
    </tr>
    
     <tr>
     <td width=40% align="right">
      <b>MSN: </b>
      <input type=text name=msn value='<?echo $msn;?>' size=41>
     </td>
     <td width=40% align="right">
      <b>ICQ: </b>
      <input type=text name=icq value='<?echo $icq;?>' size=41>
     </td>     
    </tr>
    
    <tr>
     <td colspan=2 align="right">
      <b>Observaciones: </b>
      <textarea name=comentario cols=104><?echo $comentario;?></textarea>
     </td>
    </tr>
    
    <tr>
     <td width=40% colspan=2 valign=top align="center">
      <p align="center"><b>Activo: </b>
	  <?
		if ($activo==1)
    		echo "<input type=checkbox name=activo value='1' checked>\n";
		else
    		echo "<input type=checkbox name=activo value='1'>\n";
		?>
     </td>
    </tr>
    
	<tr>
	<td colspan='2'><br>
	<?
    if ($idate!="") 
	 gestiones_comentarios($idate,"CASOS",1); 
	?>
	</td>
	</tr>
    
	<tr>
     <td width=40% colspan=2 valign=top align="left"><br>
     	<p><b>NOTA: Los campos marcados con<font color="#FF0000"> * </font>(asterisco) son indispensables para abrir el caso.</b></p>
     </td>
    </tr>
       
   </table>
  </td>
 </tr>
</table>
</tr></td>
<tr>
 <td colspan=2 align=right><br><br>
<?
if ($estado=='Modificar')
{
?>
  <input type="button" name="bagregar" value="Agregar Archivo" style="width:105" onclick="if (typeof(warchivos)=='object' && warchivos.closed || warchivos==false) warchivos=window.open('<?= encode_link($html_root.'/modulos/archivos/archivos_subir.php',array("proc_file"=>"../casos/cas_files_proc.php","idate"=>$idate)) ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1'); else warchivos.focus()">
  <input type=submit name=eliminar value='Eliminar' style='width:10%'>
  <input type=submit name=nuevo value='Modificar' style='width:10%' onclick="return control_datos()">
<?
}
else
{
?>
  <input type=submit name=nuevo value='Nuevo' style='width:10%' onclick="return control_datos()">
<?
}
?>
<input type="button" name="boton" value="Salir" onclick="window.close();" style='width:10%'>
</td></tr>
<tr><td>&nbsp;</td></tr>
</table>
<?if ($idate){?>
<br>
<br>
<?
$q = "SELECT *
	  FROM casos.tecnicos_visitas 
	  where idate=$idate order by apellido";
$res_tec_visitas=sql($q) or fin_pagina();
?>
<table width=99% align=center cellpadding=0 cellspacing=6 border=1 bordercolor="#111111">
<tr> <td id="ma_mg" > Técnico de Visitas</td> </tr>
	<tr>
		<td>
			<table width='100%'  border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
				<td colspan="5" id=ma STYLE="text-align:right">
				 	<?$link_tec=encode_link("nuevo_tecnico_visita.php",array("pagina_viene"=>"caso_organismo"));?>
				 	<input type="button" name=nuevo_tec_visitas value='Nuevo Técnico' style='width:10%' onclick="window.open('<?=$link_tec?>')">
				</td>
				<tr>
					<td align=right id=mo>IUT</td>
					<td align=right id=mo>Apellido</td>
					<td align=right id=mo>Nombre</td>
					<td align=right id=mo>Fecha de Ingreso</td>
					<td align=right id=mo>Activo</td>
				</tr>
				
				<? while (!$res_tec_visitas->EOF) {
				$link1=encode_link('nuevo_tecnico_visita.php',array('id_tecnicos_visitas'=>$res_tec_visitas->fields["id_tecnico_visita"],'pagina_viene'=>'muestra_seleccionado'));
                $onclick="ventana=window.open('$link1')";	
				?>
				<tr <?echo $atrib_tr ?> style="cursor:hand;" onclick="<?=$onclick?>">
					<td align=center><?if ($res_tec_visitas->fields["iut"]=="") 
										echo "&nbsp;"; 
									   else 
									   	echo $res_tec_visitas->fields["iut"] ?></td>
					<td align=center><?=$res_tec_visitas->fields["apellido"] ?></td>
					<td align=center><?=$res_tec_visitas->fields["nombre"] ?></td>
					<td align=center><?=fecha($res_tec_visitas->fields["fecha_inicio_tec_visitas"])?></td>
					<td align=center><?if ($res_tec_visitas->fields["activo"]==1) 
										echo "SI"; 
									   else 
									   	echo "<font color='RED'>NO</font>";?></td>
				</tr>
				<?
					$res_tec_visitas->MoveNext();
				}?>
			</table>
		</td>
	</tr>
</table>
<?
$q = "SELECT archivos_cas.*,usuarios.nombre ||' '|| usuarios.apellido as nbre_completo ";
$q.= "FROM archivos_cas ";
$q.= "join usuarios on archivos_cas.creadopor=usuarios.login ";
$q.= "where idate=$idate";
$rs=sql($q) or fin_pagina();
?>
<br>
<br>
<table width=99% align=center cellpadding=0 cellspacing=6 border=1 bordercolor="#111111">
<tr> <td id="ma_mg" > Archivos </td> </tr>
<tr>
<td>
<table width='100%'  border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
<tr>
 <td colspan=7 style='border-right: 0;' id=ma style="text-align:left">
 <b>Total:</b><?=$total_archivos=$rs->recordcount() ?>
 </td>
<tr><td align=right id=mo>Archivo</td>
<td align=right id=mo>Fecha</td>
<td align=right id=mo>Subido por</td>
<td align=right id=mo>Tamaño</td>
<td align=center id=mo>&nbsp;</td>
</tr>
<? while (!$rs->EOF) {?>
    <tr style='font-size: 9pt' >
    <td align=center>
<?
  	$path=CAS_DIR."/".$rs->fields["idate"];	
    if (is_file("$path/".$rs->fields["nombre"]))
        echo "<a target=_blank href='".encode_link($_SERVER['PHP_SELF'],array("path"=>$path,"name"=>$rs->fields["nombre"],"size"=>$rs->fields["size"],"cmd"=>"download"))."'>";
    echo $rs->fields["nombre"]."</a></td>\n";
?>    
    <td align=center>&nbsp;<?= Fecha($rs->fields["fecha"]) ?></td>
<!--    <td align=center>&nbsp;<?= $rs->fields["comentario"] ?></td>-->
    <td align=center>&nbsp;<?= $rs->fields["nbre_completo"] ?></td>
    
    <td align=center>&nbsp;<?= $size=number_format($rs->fields["size"] / 1024); ?> Kb</td>
    <td align=center>
<?    
		$lnk=encode_link("$_SERVER[PHP_SELF]",Array("cmd"=>"delete","id"=>$rs->fields["id"],"idate"=>$idate));
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
<?}?>
</form>
<?fin_pagina();?>