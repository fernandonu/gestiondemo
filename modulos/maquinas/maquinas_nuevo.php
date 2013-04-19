<?
/*
$Author: cestila $
$Revision: 1.1 $
$Date: 2004/02/05 21:47:23 $
*/
require_once("../../config.php");
echo $html_header;

if ($_POST["cmd1"]=="Guardar >>") {
    // Valores del formulario
	//print_r($_POST);
	while (list($key,$cont)=each($_POST)){
		$$key=$cont;
	}
	$error="";
    $db->BeginTrans();
    if ($nuevocliente==1) {
        if (!$contacto)
             $error.="El Organismo no tiene un contacto.<br>";
        if (!$organismo)
             $error.="Falta Organismo.<br>";
        if (!$dependencia)
             $error.="Falta dependencia.<br>";
        if (!$direccion)
             $error.="Falta el domicilio del cliente.<br>";
        if (!$telefono)
             $error.="Olvido colocar el Teléfono del contacto.<br>";
        $sql="INSERT INTO clientes ";
        $sql.="(nombre,contacto,direccion,localidad,cod_pos,provincia,telefono,mail,observaciones,cuit,iib,id_iva,id_condicion) VALUES ";
        $sql.="('$organismo','$contacto','$direccion','$lugar','$cp','$provincia','$telefono','$mail','$observaciones','$cuit','$iib',$iva,$condicion)";
        if (!$error)
            $db->execute($sql) or die($db->errormsg()." - ".$sql);	
    }
    if ($nuevocliente) {
        $sql="SELECT id_cliente FROM clientes ORDER BY id_cliente DESC LIMIT 1 offset 0";
        $rs=$db->execute($sql) or die($db->errormsg()." - ". $sql);
        if (!$error) {
			echo "entro";
            $cliente=$rs->fields['id_cliente'];
			$sql="INSERT INTO dependencia (id_cliente,dependencia) VALUES ($cliente,'$dependencia')";
			$rs=$db->execute($sql) or $error.=$db->errormsg()." - ". $sql;
		}
    }
    if (!FechaOk($fecha))
         $error.="Debe especificar la fecha.<br>";
    else
         $fecha=Fecha_db($fecha);
    if (!$garantia)
         $error.="Debe especificar la garantia.<br>";
    if ($r1==1){
        if (!$nserie)
             $error.="Olvido colocar el Nro de Serie de la PC.<br>";
        $sql = "INSERT INTO clientes_drivers "
          ."(idcliente,nserie,garantia,fecha) VALUES ('$cliente','$nserie',$garantia,'$fecha')";
        if (!$error) {
             $db->execute($sql) or $error.=$db->errormsg()." - ". $sql;
             if (!$error){
                  //echo $sql;
                  $db->CommitTrans();
             }
        }
        else {
              $db->RollbackTrans();
        }
    }
    else { //echo $rs->fields["id_cliente"];
          if ($nserie1 and $nseriefin1 and $nseriefin2) {
              $serie1=$nserie1 . chr(64 + $nserieselect1) . str_pad($nseriefin1,4 - strlen($nseriefin1),"0",STR_PAD_LEFT);
              $serie2=$nserie1 . chr(64 + $nserieselect2) . str_pad($nseriefin2,4 - strlen($nseriefin2),"0",STR_PAD_LEFT);
              $serie="$serie1 - $serie2";
              $s1=$nserieselect1 . str_pad($nseriefin1,4 - strlen($nseriefin1));
              $s2=$nserieselect2 . str_pad($nseriefin2,4 - strlen($nseriefin2));
          }
          else
              $error.="Debe colocar el numero de serie.<br>";
          while ($s1<=$s2) {
                 $serie=$nserie1 . chr(64 + substr($s1,0,1)) . substr($s1,1,3);
                 $sql = "INSERT INTO clientes_drivers "
                 ."(idcliente,nserie,garantia,fecha) VALUES ('$cliente','$serie',$garantia,'$fecha')";
                 if (!$error) {
                      $db->execute($sql) or $error.=$db->errormsg()." - ". $sql;
                 }
                 else {
                       $db->RollbackTrans();
                       break;
                 }
                 $s1++;
          }
    }
    if (!$error) {
       $db->CommitTrans();
       //echo "<script>window.location='index.php?modulo=maquinas&modo=admin';</script>";
    }
    else {
          error($error);
    }
}
?>
<script language='javascript' src='../../lib/popcalendar.js'></script>
<br>
  <table border="1" align=center cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-left-width: 0; border-right-width: 0" bordercolor="#111111" width="99%" id="AutoNumber1">
    <tr>
      <td width="100%" bgcolor="#006699" style="border-left-color: #111111; border-left-width: 1; border-right-color: #111111; border-right-width: 1">
      <p style="margin: 3"><font face="Trebuchet MS" size="2" color="#FFFFFF">
      Cargar datos de máquinas nuevas.</font></td>
    </tr>
    <tr>
      <td width="100%" style="border-left-color: #111111; border-left-width: 1; border-right-color: #111111; border-right-width: 1">
      <p style="margin: 4" align="justify"><font face="Trebuchet MS" size="2">
      Complete los datos para asociar los Drivers que aparecen en la WEB a cada
      máquina nueva, mientras más datos se tengan del cliente será más fácil
      ubicarlo.</font></p>
      <p style="margin: 4" align="justify">
      <font face="Trebuchet MS" size="2" color="#CC0000">Nota: Antes de cargar
      cualquier maquina nueva asegúrese de que los Drivers se hayan cargado en
      la sección de Drivers.</font><p style="margin: 4" align="justify">
      <font face="Trebuchet MS" size="2" color="#CC0000">Los datos marcados con
      </font><b><font face="Trebuchet MS" size="2" color="#FF0000">*</font></b><font face="Trebuchet MS" size="2" color="#CC0000">
      deberán ser completados.</font></td>
    </tr>
    <tr>
      <td width="100%" style="border-left-style: none; border-left-width: medium; border-right-style: none; border-right-width: medium">&nbsp;</td>
    </tr>
    <tr>
      <td width="100%" style="background-repeat:no-repeat" bgcolor="#F3F3F3" style="background-repeat:no-repeat;" background="imagenes/driver.gif">
      <p style="margin: 6"><b>
      <font face="Trebuchet MS" size="4" color="#008000">Número de serie </font>
      <font face="Trebuchet MS" size="4" color="#FF0000">*</font></b></p>
      <form method="POST" action="maquinas_nuevo.php" id="maquinas" name="maquinas">
        <input type=hidden name=cmd value=Nuevo>
        <p style="margin: 6">
        <font face="Trebuchet MS">
        <input type="radio" value="1" checked name="r1"><font size="2">ingresar
        una única máquina con un número de serie:</font></font></p>
        <p style="margin: 6">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="text" name="nserie" maxlength=12 value='<? echo $nserie; ?>' size="20" onChange='document.all.nserie.value = document.all.nserie.value.toUpperCase();'></p>
        <p style="margin: 6"><font face="Trebuchet MS">
        <input type="radio" name="r1" value="2"><font size="2">Si el cliente
        compro más de una maquina:</font></font></p>
        <p style="margin: 6"><font face="Trebuchet MS" size="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        Desde la PC&nbsp; <input type="text" name="nserie1" value='<? echo $nserie1; ?>' maxlength=8 size="19" onChange='document.all.nserie1.value = document.all.nserie1.value.toUpperCase();'><select size="1" name="nserieselect1">
        <option value=1>A</option>
        <option value=2>B</option>
        <option value=3 selected>C</option>
        <option value=4>D</option>
        <option value=5>E</option>
        <option value=6>F</option>
        <option value=7>G</option>
        <option value=8>H</option>
        <option value=9>I</option>
        <option value=10>J</option>
        <option value=11>K</option>
        <option value=12>L</option>
        <option value=13>M</option>
        <option value=14>N</option>
        <option value=15>O</option>
        <option value=16>P</option>
        <option value=17>Q</option>
        <option value=18>R</option>
        <option value=19>S</option>
        <option value=20>T</option>
        <option value=21>U</option>
        <option value=22>V</option>
        <option value=23>W</option>
        <option value=24>X</option>
        <option value=25>Y</option>
        <option value=26>Z</option>
        </select><input type="text" name="nseriefin1" value='<? echo $nseriefin1; ?>' maxlength=3 size="3"></font></p>
        <p style="margin: 6"><font face="Trebuchet MS" size="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        Hasta la PC&nbsp; <input type="text" name="nserie2" maxlength=8 value='<? echo $nserie2; ?>' size="19" onChange='document.all.nserie2.value = document.all.nserie2.value.toUpperCase();'><select size="1" name="nserieselect2">
        <option value=1>A</option>
        <option value=2>B</option>
        <option value=3 selected>C</option>
        <option value=4>D</option>
        <option value=5>E</option>
        <option value=6>F</option>
        <option value=7>G</option>
        <option value=8>H</option>
        <option value=9>I</option>
        <option value=10>J</option>
        <option value=11>K</option>
        <option value=12>L</option>
        <option value=13>M</option>
        <option value=14>N</option>
        <option value=15>O</option>
        <option value=16>P</option>
        <option value=17>Q</option>
        <option value=18>R</option>
        <option value=19>S</option>
        <option value=20>T</option>
        <option value=21>U</option>
        <option value=22>V</option>
        <option value=23>W</option>
        <option value=24>X</option>
        <option value=25>Y</option>
        <option value=26>Z</option>
        </select><input type="text" name="nseriefin2" value='<? echo $nseriefin2; ?>' maxlength=3 size="3"></font></p>
      <p style="margin: 6"><b>Fecha :</p>
      <p style="margin: 6">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=text name=fecha value='<? echo $fecha; ?>'>
<?
echo link_calendario("fecha");
?></p>
      <p style="margin: 6"><b>
      <p style="margin: 6">Garantia (en Años):</p>
      <p style="margin: 6">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name=garantia value='<? echo $garantia; ?>' maxlength=1 size=20></p>
      <p style="margin: 6"><b>
      <font face="Trebuchet MS" size="4" color="#008000">Datos del cliente.</font></b></p>
         <table width=100%>
    <tr>
     <td width=40% colspan=1>
      <p align="right"><font face="Trebuchet MS" size="2">Cliente<font color="#FF0000"><b> *
</b> </font>
      : </font>
     </td>
     <td colspan=3>
      <select name=cliente>
<?
$sql1="select clientes.id_cliente,nombre as organismo,dependencia.dependencia from clientes 
inner join dependencia USING(id_cliente) 
order by organismo";
$rs1=$db->execute($sql1) or die($db->errormsg(). " - ".$sql1);
while ($fila=$rs1->fetchrow()) {
       echo "<option value='".$fila['id_cliente']."' ";
       if ($cliente==$fila['id_cliente'])
           echo " selected";
       echo ">".$fila['organismo']." - ".$fila['dependencia']."</option>\n";
}
?>
     </td>
    </tr>
    <tr>
     <td width=40% colspan=4 align=left>
<?
echo "<input type=checkbox name=nuevocliente value='1' size=25";
if ($nuevocliente)
    echo " checked";
echo ">\n";
?>
      <font face="Trebuchet MS" size="2">Nuevo Cliente.</font>
     </td>
    </tr>
    <tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Organismo<b><font color="#FF0000"> * </font>
</b>:
      </font>
     </td>
     <td>
      <input type=text name=organismo value='<? echo $organismo ?>' size=25>
     </td>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Dependencia<b><font color="#FF0000"> * </font>
</b>:
      </font>
     </td>
     <td>
      <input type=text name=dependencia value='<? echo $dependencia ?>' size=25>
     </td>
    </tr>
    <tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Contacto<b><font color="#FF0000"> * </font>
</b>:
      </font>
     </td>
     <td>
      <input type=text name=contacto value='<? echo $contacto ?>' size=25>
     </td>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Dirección<b><font color="#FF0000"> * </font>
</b>: </font>
     </td>
     <td>
      <input type=text name=direccion value='<? echo $direccion ?>' size=25>
     </td>
    </tr>
    <tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Lugar/Ubicación: </font>
     </td>
     <td>
      <input type=text name=lugar value='<? echo $lugar ?>' size=25>
     </td>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Codigo Postal: </font>
     </td>
     <td>
      <input type=text name=cp value='<? echo $cp ?>' size=25>
     </td>
    </tr>
    <tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Provincia</font>
     </td>
     <td>
      <select name=provincia>
      <option>&nbsp;</option>
<?
$sql1="select nombre from distrito order by nombre";
$rs1=$db->execute($sql1) or die($db->errormsg());
while ($fila=$rs1->fetchrow()) {
       echo "<option value='".$fila['nombre']."' ";
       if ($fila["nombre"]==$provincia) echo "selected";
       echo ">".$fila['nombre']."</option>\n";
}
?>
     </td>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Teléfono<b><font color="#FF0000"> * </font>
</b>: </font>
     </td>
     <td>
      <input type=text name=telefono value='<? echo $telefono ?>' size=25>
     </td>
    </tr>
    <tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">E-M@il: </font>
     </td>
     <td>
      <input type=text name=mail value='<? echo $mail ?>' size=25>
     </td>
	 <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Nº I.I.B.: </font>
     </td>
     <td>
      <input type=text name=iib value='<? echo $iib ?>' size=25>
     </td>
    </tr>
	<tr>
     <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Tasa IVA: </font>
     </td>
     <td>
		<select name=iva>
			<option value=""></option>
<?
$sql="select id_iva,porcentaje from tasa_iva";
$rs=sql($sql) or die; 
while ($fila=$rs->fetchrow()){
	echo "<option value='".$fila["id_iva"]."'";
	if ($fila["id_iva"]==$iva) echo "selected";
	echo ">".$fila["porcentaje"]."</option>\n";
}
?>
     </td>
	 <td width=40%>
      <p align="right"><font face="Trebuchet MS" size="2">Condición IVA: </font>
     </td>
         <td>
		<select name=condicion>
			<option value=""></option>
<?
$sql="select id_condicion,nombre from condicion_iva";
$rs=sql($sql) or die; 
while ($fila=$rs->fetchrow()){
	echo "<option value='".$fila["id_condicion"]."'";
	if ($fila["id_condicion"]==$condicion) echo "selected";
	echo ">".$fila["nombre"]."</option>\n";
}
?>
     </td>
    </tr>
	<tr>
		<td valign=top align=right>
			C.U.I.T.:
		</td>
		<td valign=top>
			<input type=text name=cuit value='<? echo $cuit;?>'>
		<td align=right valign=top>
			Observaciones:
		</td>
		<td align=left valign=top>
			<textarea name='observaciones' rows=5 cols=20><? echo $observaciones;?></textarea>
		</td>
	</tr>
            <td width="100%" colspan="4">
            <p style="margin: 6;">
            <input type="submit" value="Guardar &gt;&gt;" name="cmd1"></p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>
<br>