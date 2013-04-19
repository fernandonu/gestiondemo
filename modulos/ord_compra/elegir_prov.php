<?
require_once("config_local.php");
$q="select * from proveedor ";

extract($_POST,EXTR_SKIP);
if ($parametros)
 extract($parametros,EXTR_OVERWRITE);

//0 no activos
//1 activos
//2 todos
if ($activos)
  $select_activos=$activos;
elseif ($select_activos=="") 
  $select_activos=1;

if (!$select_filtro)
   $select_filtro="t";

if ($select_activos!=2)
 $q.="where activo=".(($select_activos==0)?"'f'":"'t'");

if ($select_activos!=2 && $select_filtro!='t')
 $q.=" AND filtro ilike '%$select_filtro%'";
elseif ($select_filtro!='t')
 $q.=" where filtro ilike '%$select_filtro%'";

if ($parametros['id_proveedor'])
  $id_proveedor=$parametros['id_proveedor'];
elseif ($_POST['select_proveedor'])
	$id_proveedor=$_POST['select_proveedor'];

$proveedores=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");   	

$link=encode_link("elegir_prov.php",$parametros);
?><head><title>Proveedores</title></head>

<form name="form1" method="post" action="<?=$link ?>">
  <table width="100%" border="0" cellspacing="1" cellpadding="1">
    <tr> 
      <td height="27" colspan="2" align="center"><b>PROVEEDORES</b></td>
    </tr>
    <tr> 
      <td width="49%" align="center">MOSTRAR 
        <select name="select_activos" onchange="form1.submit()">
          <option value="1" <? if ($select_activos==1) echo " selected"?>>Activos</option>
          <option value="0" <? if ($select_activos==0) echo " selected"?>>No Activos</option>
          <option value="2" <? if ($select_activos==2) echo " selected"?>>Todos</option>
        </select></td>
      <td width="51%" align="center">PROVEEDORES DE &nbsp;
        <select name="select_filtro" onchange="form1.submit()">
          <option value="l" <? if ($select_filtro=='l') echo " selected"?>>Licitaciones</option>
          <option value="b" <? if ($select_filtro=='b') echo " selected"?>>Bancos</option>
          <option value="c" <? if ($select_filtro=='c') echo " selected"?>>Compras</option>
          <option value="t" <? if ($select_filtro=='t') echo " selected"?>>Todos</option>
        </select></td>
    </tr>
    <tr> 
      <td height="190" colspan="2" align="center"> 
        <select name="select_proveedor" size="10"  onchange="if (this.selectedIndex!=-1 && aceptar.disabled) aceptar.disabled=0;">
<?
while (!$proveedores->EOF)
{
?>
        <option value="<?=$proveedores->fields['id_proveedor'] ?>" ><?=$proveedores->fields['razon_social'] ?> </option>
<?
	$proveedores->MoveNext();
}
?>
        </select></td>
    </tr>
    <tr> 
      <td colspan="2" align="center"><input name="aceptar" type="button" value="Aceptar" disabled onclick="form1.action='<?=encode_link($volver,$parametros)?>';form1.submit();"> &nbsp;&nbsp;
        <input name="cancelar" type="button" value="Cancelar" onclick="location.href='<?=encode_link($volver,$parametros)?>'"></td>
    </tr>
  </table>
  <input type="hidden" name="volver" value="<?=$volver?>">
  <input type="hidden" name="id_proveedor" value="<?=$id_proveedor?>">

</form>
