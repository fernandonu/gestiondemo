<?
/*
$Author: cestila $
$Revision: 1.5 $
$Date: 2005/10/03 22:43:35 $
*/
include ("../../config.php");
$id=$parametros["id"];
$estado=$parametros["estado"];
$sql="select * from cas_ate where idate=$id";
$rs=$db->execute($sql) or die($db->errormsg());
echo $html_header;
?>
<table width=99% style='border: 1px solid black;border-collapse:collapse' cellpadding=0 cellspacing=6 bgcolor=#EFEFEF bordercolor="#111111">
 <tr>
  <td>
   <center><h2>Datos del C.A.S.</h2></center>
  </td>
 </tr>
 <tr>
  <td>
   C.A.S.: <? echo $rs->fields["nombre"];?>
  </td>
 </tr>
 <tr>
  <td>
   Contacto: <? echo $rs->fields["contacto"];?>
  </td>
 </tr>
 <tr>
  <td>
   Dirección: <? echo $rs->fields["direccion"];?>
  </td>
 </tr>
 <tr>
  <td>
   Teléfono: <? echo $rs->fields["tel"];?>
  </td>
 </tr>
 <tr>
  <td>
   E-M@il: <? echo $rs->fields["mail"];?>
  </td>
 </tr>
 <tr>
  <td>
   Comentario: <? echo $rs->fields["comentarios"];?>
  </td>
 </tr>
</table><br>
<center><h2>Casos <? if($estado==1) echo "en Curso"; else echo "Pendientes"; ?></h2></center>
<?
//traigo en curso
$sql="select casos_cdr.idcaso,casos_cdr.nrocaso,casos_cdr.fechainicio,casos_cdr.nserie,dependencias.dependencia,dependencias.id_entidad from estadousuarios join casos_cdr using (idestuser) join dependencias using(id_dependencia) join entidad using(id_entidad) where casos_cdr.idate=".$rs->fields['idate']." and estadousuarios.idestuser=$estado";
//$sql = "SELECT casos_cdr.idcaso,casos_cdr.nrocaso,cas_ate.idate,cas_ate.nombre,casos_cdr.fechainicio,clientes.nombre as organismo,casos_cdr.nserie,estadousuarios.descripcion,casos_cdr.fechacierre FROM casos_cdr JOIN cas_ate using(idate) JOIN dependencias USING(id_dependencia) JOIN estadousuarios using(idestuser) where casos_cdr.idate=$id and casos_cdr.fechacierre is NULL and estadousuarios.idestuser=1 order by casos_cdr.nrocaso";
$rs=$db->execute($sql) or die($db->errormsg());
echo "<table border=1 width=99% cellspacing=0 cellpadding=1 bordercolor='#f0f0f0' align=left>";
echo "<tr><td align=right id=mo>Número de caso</a></td>\n";
echo "<td align=right width=60 id=mo>Fecha Inicio</td>\n";
echo "<td align=right id=mo>Cliente</td>\n";
echo "<td align=right id=mo>Nro. de Serie</td>\n";
echo "<td align=right id=mo>Repuestos</td>\n";
echo "</tr>\n";
while (!$rs->EOF) {
    $es=0;
    $sql="select proveedor,descripcion from repuestos where idcaso='".$rs->fields["idcaso"]."'";
    $result=$db->execute($sql)or die($db->errormsg());
    if ($rs->fields['fechacierre']) $es=1;
	$ref = encode_link("caso_estados.php",Array("id"=>$rs->fields["idcaso"],"id_entidad"=>$rs->fields['id_entidad']));
    //echo "<tr>\n";
	tr_tag($ref);
    echo "<td align=center style='font-size: 9pt;'>".$rs->fields["nrocaso"]."</td>\n";
    echo "<td align=left width=60 style='font-size: 9pt;'>&nbsp;".ConvFecha($rs->fields["fechainicio"])."</td>\n";
    echo "<td align=left style='font-size: 9pt;'>&nbsp;".$rs->fields["dependencia"]."</td>\n";
    echo "<td align=left style='font-size: 9pt;'>&nbsp;".$rs->fields["nserie"]."</td>\n";
    echo "<td align=left style='font-size: 9pt;'>&nbsp;".$result->fields["descripcion"]."</td>\n";
    echo "</tr>\n";
    $rs->MoveNext();
}
echo "</table>\n";
?>
</body>
</html>