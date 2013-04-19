<?
/*
$Author: cestila $
$Revision: 1.1 $
$Date: 2004/01/28 17:28:05 $
*/
include "head.php";
$id=$parametros["id"] or $id=$_POST["id"];

if ($_POST["cmd1"]=="Eliminar") {
    $sql="DELETE FROM cas_cdr WHERE id_caso=$cod";
    if ($db->execute($sql))
        echo "<script> window.location='index.php?modulo=caso&modo=admin';</script>\n";
}
if ($_POST["cmd1"]=="Finalizar") {
	// Valores del formulario
	while (list($key,$cont)=each($_POST)){
		$$key=$cont;
	}
    $sql="Select fechacierre,firma FROM casos_cdr where idcaso=$id";
    $rs=$db->execute($sql) or die($db->errormsg());
    $firma=$rs->fields['firma'];
    $fecha=$rs->fields['fechacierre'];
    if (!fechaok($fecha))
         $fechadb="'".date("Y/m/d")."'";
    else
        $fechadb="'".fecha_db($fecha)."'";
    $sql="UPDATE casos_cdr SET firma='$firma',fechacierre=$fechadb,cerrado=1,idestuser=2 where idcaso=$id";
    if (!$firma) {
         error("El cliente debe firmar el caso para poder finalizarlo");
    }
    elseif ($db->execute($sql))
        echo "<script> window.location='index.php?modulo=caso&modo=admin';</script>\n";
}
if ($_POST["cmd1"]=="Guardar >>") {
    // Valores del formulario
	while (list($key,$cont)=each($_POST)){
		$$key=$cont;
	}
	if (!$atendido)
         error("Olvido colocar el caso.");
    if (!$cliente)
         error("El caso no tiene cliente.");
    if (!$serie)
         error("Olvido colocar el Nro de Serie de la PC.");
//    if (!$defecto)
//         error("Falta el defecto de la PC.");
    if (!fechaok($fechainicio))
         error("Debe especificar la fecha en que se inicio el caso.");
    else
        $fechainicio="'".fecha_db($fechainicio)."'";
    if (!fechaok($fechacierre))
         $fechacierre="NULL";
    else
         $fechacierre="'".fecha_db($fechacierre)."'";
    if (!fechaok($fechafactura))
         $fechafactura="NULL";
    else
         $fechafactura="'".fecha_db($fechafactura)."'";
    if (!$pagado) $pagado=0;
    $sql="UPDATE casos_cdr SET ";
    $sql.="idate=$atendido,"
        ."idcliente=$cliente,"
        ."nserie='$serie',"
        ."idestuser=$estadouser,
        fechainicio=$fechainicio,
        fechacierre=$fechacierre,
        fechafactura=$fechafactura,
        firma='$firma',
        nfactura='$nfactura',
        costofin='$precio',
        pagado=$pagado,
        deperfecto='$defecto'
        WHERE idcaso = $id";
    if ($db->execute($sql))
        Aviso ("Los datos se guardaron con exito.");
    else
        error($db->errormsg()." - ".$sql);
}
$sql="Select * from casos_cdr where idcaso=$id";
$rs=$db->execute($sql) or die($db->errormsg());
?>
<script language='javascript' src='../../lib/popcalendar.js'></script>
<table width=99% align=center style='border: 1px solid black;border-collapse:collapse' cellpadding=0 cellspacing=6 bgcolor=#EFEFEF bordercolor="#111111">
<tr>
<td>
<p align="left"><font face="Trebuchet MS" size="2">Modifique los datos de este
CAS, tenga en cuenta la importancia requerida en el estado del CAS, si este se
ingresa la palabra &quot;Finalizado&quot; se cierra el evento técnico.</font></p>
<p align="left"><font face="Trebuchet MS" size="2">
<font color="#993300">
<b>NOTA</b>:</font> Los campos marcados con<b><font color="#FF0000"> * </font>
</b>(asterisco) son indispensables para abrir el caso.</font></p>
<center>
<form action='caso_modi.php' method='POST' name=frm id=frm>
<input type=hidden name='id' value='<? echo $id; ?>'>
<table width=100%>
 <tr>
  <td align=center>
   <p class=menutitulo style='margin-bottom: 0;'><b>
   <font face="Trebuchet MS" color="#009900">Funciones a realizar</font></b></p>
  </td>
 </tr>
 <tr>
  <td>
   Por hacer:
   <input type=button name=cmd value="Estados del Caso" onclick="window.location='<? echo encode_link("caso_estados.php",Array("id"=>$id)); ?>';">
   <input type=button name=cmd value="Repuestos" onclick="window.location='<? echo encode_link("caso_repuesto.php",Array("id"=>$id)); ?>';">
   <input type=button name=cmd value="Lista de Casos" onclick="window.location='<? echo encode_link("caso_admin.php",Array("id"=>$id)); ?>';">
   <input type=button name=cmd value="Informe" onclick="window.location='<? echo encode_link("caso_inf.php",Array("id"=>$id)); ?>';">
   <input type=submit name=cmd1 value=Finalizar>
  </td>
 </tr>
</table>
<div align="center">
  <center>
<table width=325 border="0" cellpadding="2" cellspacing="0" style="border-collapse: collapse; " bordercolor="#9A9A9A">
 <tr>
  <td align=center>
   <p class=menutitulo style='margin-bottom: 0;'><b>
   <font face="Trebuchet MS" color="#009900">Modificar datos del&nbsp; CAS</font></b></p>
  </td>
 </tr>
 <tr>
  <td>
   <table width=100%>
    <tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Atendido por<font color="#FF0000"><b> *
</b> </font>
      : </font>
     </td>
     <td>
      <select name=atendido>
<?
$sql1="select idate,nombre from cas_ate where (activo=1) OR (idate=".$rs->fields['idate'].") order by nombre";
$rs1=$db->execute($sql1) or die($db->errormsg());
while ($fila=$rs1->fetchrow()) {
       echo "<option value='".$fila['idate']."' ";
       if ($rs->fields["idate"]==$fila['idate']) echo "selected";
       echo ">".$fila['nombre']."</option>\n";
}
?>
     </td>
    </tr>
    <tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Fecha Inicio<font color="#FF0000"><b> *
</b> </font>
      : </font>
     </td>
     <td>
      <input type=text name=fechainicio value='<? echo ConvFecha($rs->fields['fechainicio']); ?>'>
<?
echo link_calendario("fechainicio");
?>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td align=center>
   <p class=menutitulo style='margin-bottom: 0;'><b>
   <font face="Trebuchet MS" color="#009900">Datos del cliente</font></b></p>
  </td>
 </tr>
 <tr>
  <td>
   <table width=100%>
   <tr>
  <td width=40%>
   <p align="right"><font face="Trebuchet MS" size="2">Cliente <font color="#FF0000"><b> *
</b> </font>
      : </font>
   </td>
   <td>
    <select name=cliente>
<?
$sql1="select id_cliente,nombre,dependencia from clientes left join dependencia USING (id_cliente)";
$rs1=$db->execute($sql1) or die($db->errormsg(). " - ".$sql1);
while ($fila=$rs1->fetchrow()) {
       echo "<option value='".$fila['id_cliente']."' ";
       if ($rs->fields["idcliente"]==$fila['id_cliente']) echo "selected";
       echo ">".$fila["nombre"]." - ".$fila['dependencia']."</option>\n";
}
?>
   </td>
  </tr>
    </table>
   </td>
  </tr>
  <tr>
   <td align=center>
    <p class=menutitulo style='margin-bottom: 0;'><b>
    <font color="#009900" face="Trebuchet MS">Datos de equipo</font></b></p>
   </td>
 </tr>
 <tr>
  <td>
   <table width=100%>
    <tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Número de Serie<b><font color="#FF0000"> * </font>
</b>:
      </font>
     </td>
     <td>
      <input type=text name=serie value='<? echo $rs->fields["nserie"]; ?>' size=25>
     </td>
    </tr>
    <tr>
     <td width=40% valign=top>
      <p align="right"><font face="Trebuchet MS" size="2">Desperfecto: </font>
     </td>
     <td>
      <textarea name=defecto rows=5 cols=20><? echo $rs->fields["deperfecto"]; ?></textarea>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td align=center>
   <p class=menutitulo style='margin-bottom: 0;'><b>
   <font face="Trebuchet MS" color="#009900">Estado del caso</font></b></p>
  </td>
 </tr>
 <tr>
  <td>
   <table width=100%>
    <tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2" color="#0033CC">Estado
      para usuario</font><font face="Trebuchet MS" size="2"><b><font color="#FF0000"> * </font>
</b></font><font face="Trebuchet MS" size="2" color="#0033CC">: </font>
     </td>
     <td>
     <select name=estadouser>
<?
$sql1="select idestuser,descripcion from estadousuarios";
$rs1=$db->execute($sql1) or die($db->errormsg());
while ($fila=$rs1->fetchrow()) {
       echo "<option value='".$fila['idestuser']."' ";
       if ($rs->fields["idestuser"]==$fila['idestuser']) echo "selected";
       echo ">".$fila['descripcion']."</option>\n";
}
?>
     </td>
    <tr>
     <td width=40%>
      <p align="right"><font size="2" face="Trebuchet MS">Firma del cliente:
      </font>
     </td>
     <td>
      <input type=text name=firma value='<? echo $rs->fields["firma"] ?>'>
     </td>
    </tr>
    </tr>
    <tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Fecha de Cierre:</font>
     </td>
     <td>
<?
if ($rs->fields['fechacierre'])
    echo "<input type=text name=fechacierre value='".ConvierteFecha($rs->fields['fechacierre'])."'>\n";
else
    echo "<input type=text name=fechacierre>\n";
echo link_calendario("fechacierre");
?>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td align=center>
   <p class=menutitulo style='margin-bottom: 0;'><b>
   <font face="Trebuchet MS" color="#009900">Facturación</font></b></p>
  </td>
 </tr>
 <tr>
  <td>
   <table width=100%>
    <tr>
     <td width=40%>
      <p align="right"><font size="2" face="Trebuchet MS">Nro. Factura:</font>
     </td>
     <td>
      <input type=text name=nfactura value='<? echo $rs->fields["nfactura"]; ?>' size=20>
     </td>
    </tr>
    <tr>
     <td width=40%>
      <p align="right"><font size="2" face="Trebuchet MS">Fecha: </font>
     </td>
     <td>
<?
if ($rs->fields['fechafactura'])
    echo "<input type=text name=fechafactura value='".ConvierteFecha($rs->fields['fechafactura'])."'>\n";
else
    echo "<input type=text name=fechafactura>\n";
echo link_calendario("fechafactura");
?>
     </td>
    </tr>
    <tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Precio: </font>
     </td>
     <td>
      <input type=text name=precio value='<? echo $rs->fields["costofin"]; ?>' size=20>
     </td>
    </tr>
    <tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2" color="#0033CC">¿Se 
      pagó?</font></td>
     <td>
      <?
      if ($rs->fields["pagado"])
         echo "<input type=checkbox name=pagado value='1' size=20 checked>\n";
      else
         echo "<input type=checkbox name=pagado value='1' size=20>\n";
      ?>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td align=center>
   <? if (!$rs->fields["cerrado"]) { ?>
   <input type=submit name=cmd1 value="Guardar &gt;&gt;">
   <? } ?>
  </td>
 </tr>
</table>
  </center>
</div>
</td>
</tr>
</table>