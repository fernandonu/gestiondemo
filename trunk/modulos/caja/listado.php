<?php
/*
$Author: mari $
$Revision: 1.13 $
$Date: 2007/01/05 20:10:19 $
*/

require_once("../../config.php");
include("func.php");
require_once("../../lib/class.gacz.php");

if ($download=$parametros['download'])
	$itemspp=1000000;
//else 	$itemspp=200;

define("MAX_MONTO","9999999999");

//arreglos de valores por defecto
$arr_default=array(
	"avanzada"=>"",//checkbox avanzada
	"orden_default"=>2,//select ordenar
	"orden_by"=>0,//checkbox orden ascendente

	"select_moneda"=>-1,//select moneda
	"select_t_cuenta"=>-1,
	"select_t_ingreso"=>-1,
	"select_t_egreso"=>-1,
	"select_estado"=>-1,
	);
$arr_fechas=array(
	"fechas"=>"",//checkbox fechas
	"desde"=>"",//input desde fechas
	"hasta"=>""//input hasta fechas
);
$arr_montos=array(
	"montos"=>"",//checkbox montos
	"desde_m"=>"0",//input desde montos
	"hasta_m"=>MAX_MONTO//input hasta montos
);
$arr_entidades=array(
	"entidades"=>"",//checkbox entidades o clientes
	"hentidades"=>"",//hidden (bool) entidades o clientes
	"letra"=>"a",//select letra
	"entidad"=>-1//select entidad
);
$variables=array_merge(array_merge($arr_default,$arr_fechas),array_merge($arr_montos,$arr_entidades));

$distrito=$_GET['distrito'] or $distrito=$parametros['distrito']  or die("No se indico el distrito");//tiene que venir si o si
 if ($parametros["select_moneda"]) $select_moneda=$parametros["select_moneda"];
if  ($_POST['form_busqueda'])
{
	//limpieza de campos
	if (!$_POST["avanzada"])
	{
		$_ses_list_caja=array_merge($_ses_list_caja,$variables);
		$_ses_list_caja['keyword']=$_POST['keyword'];
		$_POST=array_merge($_POST,$_ses_list_caja);
	}
	else
	{
		$arr=array();
		if (!$_POST["fechas"])
		 $arr=array_merge($arr,$arr_fechas);
		if (!$_POST["montos"])
		 $arr=array_merge($arr,$arr_montos);
		if (!$_POST["entidades"])
		 $arr=array_merge($arr,$arr_entidades);
		if (!$_POST['orden_by'])
		 $arr['orden_by']=0;


		$_ses_list_caja=array_merge($_ses_list_caja,$arr);
	}
	phpss_svars_set("_ses_list_caja", $_ses_list_caja);
}
variables_form_busqueda("list_caja",$variables);
if ($cmd == "")
 	$cmd="ingreso";
//alias de las variables
$id_t_cuenta = $select_t_cuenta;
$id_t_ingreso = $select_t_ingreso;
$id_t_egreso = $select_t_egreso;
$estado_caja = $select_estado;
$ie=$cmd;

/************************************RECUPERO TODOS LOS VALORES PARA LOS OBJETOS *****************************************/

//Select moneda
$oselect_moneda=new HtmlOptionList("select_moneda",0);
$oselect_moneda->add_option("Todas",-1);
$q="select nombre,id_moneda from moneda";
$r=$db->Execute($q) or die ($db->ErrorMsg()."<br>".$q);
$oselect_moneda->optionsFromResulset($r,array("text"=>"nombre","value"=>"id_moneda"));
$oselect_moneda->setSelected($select_moneda);

//Select tipo_cuenta
$oselect_tcuenta=new HtmlOptionList("select_t_cuenta",0,"width:100%");
$oselect_tcuenta->add_option("Todas",-1);

//Select Entidad/Proveedor
$oselect_entidad=new HtmlOptionList("entidad",0,"width:100%");
$oselect_entidad->add_option("Todos",-1);

if ($cmd=="ingreso")
{
	$res_t_cuenta= sql("Select * from tipo_cuenta_ingreso order by nombre ASC","Error en combo tipo de cuenta");
	$oselect_tcuenta->optionsFromResulset($res_t_cuenta,array("text"=>"nombre","value"=>"id_cuenta_ingreso"));
	$oselect_tcuenta->setSelected($id_t_cuenta);

	//select tipo_ingreso
	$oselect_tingreso=new HtmlOptionList("select_t_ingreso",0,"width:100%");
	$oselect_tingreso->add_option("Todas",-1);
	$res_t_ingreso = sql("Select * from tipo_ingreso order by nombre ASC","Error en combo tipo de ingreso");
	$oselect_tingreso->optionsFromResulset($res_t_ingreso,array("text"=>"nombre","value"=>"id_tipo_ingreso"));
	$oselect_tingreso->setSelected($id_t_ingreso);

	$sql_entidades = "Select id_entidad,nombre from entidad where nombre ilike '$letra%' order by nombre asc";
	$result_entidades = sql($sql_entidades,"Error en $sql_entidades");
	$oselect_entidad->optionsFromResulset($result_entidades,array("text"=>"nombre","value"=>"id_entidad"));
	$oselect_entidad->setSelected($entidad);
}
elseif ($cmd=="egreso")
{
	$res_t_cuenta = sql("Select * from tipo_cuenta order by concepto ASC","Error en combo tipo de cuenta");
	while (!$res_t_cuenta->EOF)
	{
		$text_t_cuenta = $res_t_cuenta->fields["concepto"]."[".$res_t_cuenta->fields["plan"]."]";
		$id_tipo_cuenta = $res_t_cuenta->fields["numero_cuenta"];
		$oselect_tcuenta->add_option($text_t_cuenta,$id_tipo_cuenta);
 		$res_t_cuenta->MoveNext();
	}
	$oselect_tcuenta->setSelected($id_t_cuenta);

	//select tipo_egreso
	$oselect_tegreso=new HtmlOptionList("select_t_egreso",0,"width:100%");
	$oselect_tegreso->add_option("Todas",-1);
	$res_t_egreso = sql("Select * from tipo_egreso order by nombre ASC","Error en combo tipo de egreso");
	$oselect_tegreso->optionsFromResulset($res_t_egreso,array("text"=>"nombre","value"=>"id_tipo_egreso"));
	$oselect_tegreso->setSelected($id_t_egreso);

	$sql_entidades = "Select id_proveedor,razon_social as nombre from proveedor where razon_social ilike '$letra%' order by nombre asc";
	$result_entidades = sql($sql_entidades,"Error en $sql_entidades");
	$oselect_entidad->optionsFromResulset($result_entidades,array("text"=>"nombre","value"=>"id_proveedor"));
	$oselect_entidad->setSelected($entidad);
}
elseif ($cmd=="caja")
{
	$oselect_estado=new HtmlOptionList("select_estado",0);
	$oselect_estado->add_option("Todas",-1);
	$oselect_estado->add_option("Abiertas",1);
	$oselect_estado->add_option("Cerradas",2);
	$oselect_estado->setSelected($select_estado);
}

//este archivo recupera todos los datos de la busqueda
//La variable $XML_DATA y $form_busqueda se crean en este archivo
require("listado.xml.php");
//echo $XML_DATA;die; //descomentar y manda el archivo en formato XML
$x1=new XMListReader($XML_DATA);
if ($distrito==1)
	$dist_nbre="SanLuis";
else
	$dist_nbre="BsAs";

$excel_name=strtoupper($cmd)."S_{$dist_nbre} (".date("Y-m-j").").xls";
if ($download)
{
	//hacer Excel del XML
	$x1->sendExcel($excel_name);
}

echo $html_header;
cargar_calendario();
generar_barra_nav($datos_barra);
echo "<form name=\"form1\" method=\"post\" action=\"".encode_link($_SERVER['SCRIPT_NAME'],array("distrito"=>$distrito))."\">\n";
echo "<center>";
echo $form_busqueda;//variable que se crea en listado.xml.php, contiene los echo de la funcion form_busqueda()
$pagina="listado";
?>

&nbsp;<b>Avanzada</b> <INPUT name="avanzada" type='checkbox' value="1" <? if ($avanzada==1) echo 'checked'?> onclick='activar(this,document.all.div)'>
&nbsp;
<?
$obbuscar=new HtmlButton("form_busqueda","Buscar","submit");
$obbuscar->style="font-family :georgia, garamond, serif; font-size : 10px; font-weight : bold; color : white; border : solid 3px white;background-color: blue; cursor: hand";
$obbuscar->toBrowser();

 if (permisos_check("inicio","excel_caja"))
 	echo "&nbsp;&nbsp;<a target=_blank title=\"Bajar datos en un excel&nbsp;&nbsp;'$excel_name'\" href='". encode_link($_SERVER['SCRIPT_NAME'],array('download'=>1,"keyword"=>$keyword,"filter"=>$filter,"distrito"=>$distrito,"page"=>0)) ."'><img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' ></a>";
?>
<div id='div'  style='display:<?if ($avanzada==1) echo "all"; else echo "none";?>'><br>
<table width='95%' border='0' bgcolor='#E0E0E0' cellspacing='0' class="bordes">
<TR>
	<TD width="40%" rowspan="3">
	<TABLE id="ma" width="100%">
	<TR><TD width="40%">
 <b> Moneda </b>
 </TD>
 <TD align="right"><?$oselect_moneda->toBrowser();?></TD>
 </TR>
<?
  if($cmd=="caja")
  {
?>
    <TR>
    <TD align="center">
   <b>Estado</b>
 	</TD><TD align="right">
<?	$oselect_estado->toBrowser();?>
    </TD></TR>
 <?}
 if ($cmd=="ingreso")
 {//combo de selección del tipo de ingreso
 	?>
    <TR>
    <TD align="center">
   <b>T. Cuenta</b>
 	</TD>
 	 <TD align="right">
		<?$oselect_tcuenta->toBrowser();?>
    </TD>
    </TR>
    <TR>
    <TD align="center">
   <b>T. Ingreso</b>
 	</TD><TD align="right">
	<?	$oselect_tingreso->toBrowser(); ?>
    </TD></TR>
 <?}
 if ($cmd=="egreso"){//combo de selección del tipo de egreso
 	?>
    <TR>
	   <TD align="center">
	   	<b>T. Cuenta</b>
	 	</TD>
	 	<TD align="right">
			<?$oselect_tcuenta->toBrowser();?>
	   </TD>
    </TR>
    <TR>
    <TD align="center">
   <b>T. Egreso</b>
 	</TD>
 	<TD align="right">
		<?	$oselect_tegreso->toBrowser();?>
   </TD></TR>
 <?}
 ?>
 	<TR>
 	<TD>
 	Ordenar Por:
 	</TD>
 	<TD align="right">
 	<?
	$ooption=new HtmlOptionList("orden_default");
	if($cmd!="caja")
	{
		$ooption->add_option("ID",1);
		$ooption->add_option("Fecha",2);
		$ooption->add_option("Item",3);
		$ooption->add_option("Monto",4);
	}
	else
	{
		$ooption->add_option("ID Caja",1);
		$ooption->add_option("Fecha",2);
		$ooption->add_option("Estado",3);
	}
	$ooption->setSelected($orden_default);
	$ooption->toBrowser();

 	?>
 	</TD>
 	</TR>
 	<TR>
 	<TD colspan="2" align="right">
 	Orden Ascendente:
	<INPUT type="checkbox" name="orden_by" <?if($orden_by==1) echo "checked"?> value="1">
 	</TD>
 	</TR>
 	</TABLE>
 	</TD>
 <TD width="60%">

  <table id="ma">
 <tr>
  <td align="left" colspan="2"> <input name="fechas" type="checkbox" value="1" <? if ($fechas==1) echo 'checked'?> onclick="if (!this.checked) { document.all.desde.value='';document.all.hasta.value='';} " > <b>Entre fechas: </b></td>
  <td colspan="2"> <b>Desde: </b> <input type='text' size=10 name='desde' value='<?=$desde?>' readonly>
	                <? echo link_calendario('desde');?>
	  <b>Hasta: </b><input type='text' size=10 name='hasta' value='<?=$hasta?>' readonly>
                    <? echo link_calendario('hasta'); ?>
  </td>
 </tr>
<TR>
  <td align="left" colspan="2"> <input name="montos" type="checkbox" value="1" <? if ($montos==1) echo 'checked'?> onclick="if (!this.checked) { document.all.desde_m.value='';document.all.hasta_m.value='';} " > <b>Entre montos: </b></td>
  <td colspan="2"> <b>Mínimo: </b> <input type='text' size=10 name='desde_m' value='<?=$desde_m?>' onkeypress="return filtrar_teclas(event,'0123456789.')">
	  <b>Máximo: </b><input type='text' size=10 name='hasta_m' value='<?=$hasta_m?>' onkeypress="return filtrar_teclas(event,'0123456789.')">
  </td>
</tr>
<?
if($cmd!="caja")
{
?>
	<TR>
  	<td align="left" colspan="4">
  	<input name="entidades" type="checkbox" value="1" <? if ($entidades==1) echo 'checked'?> onclick="document.form1.hentidades.value=!document.form1.hentidades.value;document.form1.submit();"> <b><?=($cmd=="egreso")?"Por Proveedor: ":"Por Cliente: "?></b>
  	<input type="hidden" name="hentidades" value=<?=($hentidades?1:0) ?> >
  	</td>
	</tr>
<?
if ($entidades) {
?>
	<TR>
  	<td width="20%">
  	Comienza con:
  	</td>
  	<td width="10%">
<?
  	$ooption=new HtmlOptionList("letra");
 		$ooption->add_option("A","a");
 		$ooption->add_option("B","b");
 		$ooption->add_option("C","c");
 		$ooption->add_option("D","d");
 		$ooption->add_option("E","e");
 		$ooption->add_option("F","f");
 		$ooption->add_option("G","g");
 		$ooption->add_option("H","h");
 		$ooption->add_option("I","i");
 		$ooption->add_option("J","j");
 		$ooption->add_option("K","k");
 		$ooption->add_option("L","l");
 		$ooption->add_option("M","m");
 		$ooption->add_option("N","n");
 		$ooption->add_option("Ñ","ñ");
 		$ooption->add_option("O","o");
 		$ooption->add_option("P","p");
 		$ooption->add_option("Q","q");
 		$ooption->add_option("R","r");
 		$ooption->add_option("S","s");
 		$ooption->add_option("T","t");
 		$ooption->add_option("U","u");
 		$ooption->add_option("V","v");
 		$ooption->add_option("W","w");
 		$ooption->add_option("X","x");
 		$ooption->add_option("Y","y");
 		$ooption->add_option("Z","z");
 		$ooption->setSelected($letra);
 		$ooption->add_event("onchange","document.form1.entidad.selectedIndex=0;document.form1.submit()");
 		$ooption->toBrowser();
?>
  	</td>
 	<TD width="10%">
  	Nombre:
  	</td>
  	<td width="60%">
<?	$oselect_entidad->toBrowser(); ?>
	</TD>
	</TR>
<?
 		}
  }
?>
</table>
 </td>
</tr></table> </div><br>
<SCRIPT>
function activar(obj1,obj2){
	if (obj1.checked) {
		obj2.style.display = 'block';
	} else {
		obj2.style.display= 'none';
	}
}
</SCRIPT>
<?
$x1->setScreenPercent(95.00);
$x1->sendHTML();
?>
</center>
</form>
<br>
<?
fin_pagina();
/* */
?>