<?
/*
Autor: GACZ

MODIFICADA POR
$Author: fernando $
$Revision: 1.5 $
$Date: 2004/10/20 18:35:06 $
*/
require_once("../../config.php");

extract($_POST);
$titulo=trim($titulo);
if ($parametros)
{
	$id_etap=$parametros['id_etap'];
}

if ($boton=="Guardar")
{
	if ($select_titulo==-1)
	{
		$q="insert into etaps (titulo,texto) values ('$titulo','$texto')";
		if ($titulo && $db->Execute($q))
			$msg="La nueva norma $titulo se agrego con exito";
		else
			$msg="<font color=red >No se pudo agregar la norma $titulo</font>";
	}
	elseif ($select_titulo>0)
	{
		$q="update etaps set titulo='$titulo',texto='$texto' where id_etap=$select_titulo";
		if ($db->Execute($q))
			$msg="La norma $titulo se guardo con exito";
		else
			$msg="<font color=red >No se pudo guardar la norma $titulo</font>";
	}

}
elseif ($boton=="Eliminar")
{
		$q="delete from etaps where id_etap=$select_titulo";
		if ($db->Execute($q))
			$msg="La norma $titulo se elimino con exito";
		else
			$msg="<font color=red >No se pudo eliminar la norma $titulo</font>";

}
?>
<html>
<head>
<title>Normas ETAPS</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?=$html_header?>
</head>
<body >
<form name="form1" method="post" action="<?=$_SERVER['SCRIPT_NAME']?>">
<br>
  <h4 align="center" > Normas ETAPS (Est&aacute;ndares Tecnol&oacute;gicos para
    la Administraci&oacute;n P&uacute;blica)</h3>
<?="<center><b>$msg</b></center>"?>
  <table width="100%" border="0" cellspacing="1" cellpadding="1">
    <tr>
      <td valign="top">
<table width="100%" class="bordes"  align="left" cellpadding="2" cellspacing="2">
          <tr>
            <td align="center" id=mo >EDICION</td>
          </tr>
          <tr bgcolor=<?=$bgcolor_out?>>
            <td>Titulo
              <input type="text" name="titulo"></td>
          </tr>
          <tr bgcolor=<?=$bgcolor_out?>>
            <td valign="top"><br>
              Texto <br> <textarea name="texto" cols="50" rows="8" wrap="PHYSICAL" onkeypress="texto.focus()" ></textarea>
            </td>
          </tr>
        </table></td>
      <td width="4%">
      <td width="48%" valign="top" nowrap>
        <table width="100%" class="bordes" cellpadding="2" cellspacing="2">
          <tr>
            <td align="center" id=mo >TITULOS</td>
          </tr>
          <tr bgcolor=<?=$bgcolor_out?>>
            <td height="171" align="center" valign="top" >
              <?
$q="select * from etaps where titulo<>'NO ETAPS' ORDER BY TITULO ";
$etaps=$db->Execute($q) or die($db->ErrorMsg()."<br> $q");
?>
              <select name="select_titulo" size="12" style="width:70%" onchange=
"
if (this.selectedIndex!=0)
{
	titulo.value=this.options[this.selectedIndex].text;
	texto.value=this.options[this.selectedIndex].id;	
	if (boton[1])
		boton[1].disabled=0;
}
else
{
	titulo.value='';
	texto.value='';
	if (boton[1])
		boton[1].disabled=1;
}
" >
                <option selected value="-1" >NUEVA</option>
                <?=		 make_options($etaps,"id_etap","titulo",$id_etap,"texto") ?>
              </select></td>
          </tr>
        </table></td>
  </tr>
</table>

  <br>
  <table width="100%" border=0 align="center" cellpadding="1" cellspacing="1">
    <tr> 
<? if (!$id_etap)
{
?>
    <td width="13%" align="right">&nbsp;</td>
      <td width="37%" align="right"> <input name="boton" type="submit" id="boton" value="Guardar"> 
      </td>
      <td width="30%"><input name="boton" type="submit" id="boton" value="Eliminar" disabled onclick="return confirm('Seguro que desea eliminar?') " ></td>
      <td width="20%">&nbsp;</td>
<?
}
else 
{ //el cerrar la ventana solo funcionara si esta pagina esta cargada en una ventana nueva
?>
      <td align="center"> <input name="boton" type="button" value="Cerrar" onclick="window.close();"> </td><?
}
?>
    </tr>
  </table>
  <p>&nbsp;</p>
<script>
if (<?=($id_etap)?1:0?>)
{
	//es indistinto el document.form y document.all (en IE)
	document.forms[0].texto.value=document.all.select_titulo.options[document.all.select_titulo.selectedIndex].id;
	document.forms[0].titulo.value=document.all.select_titulo.options[document.all.select_titulo.selectedIndex].text;
}
</script>
</form>
</body>
</html>