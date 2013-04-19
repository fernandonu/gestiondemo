<?
/*
Autor: MAC
Fecha: 07/09/05

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.7 $
$Date: 2005/10/18 14:30:41 $
*/

require_once("../../config.php");

variables_form_busqueda("listado_oc_serv_tec");

if ($cmd=="")
      {
       $cmd='p';
       $_ses_listado_oc_serv_tec["cmd"]=$cmd;
       phpss_svars_set("_ses_listado_oc_serv_tec",$_ses_listado_oc_serv_tec);
       $page=0;
      }      
      
echo $html_header;

/***********************************************************************************************
 Seccion de Busqueda y generacion de la consulta para traer los datos
************************************************************************************************/
//traemos los datos de las OC que tengan un nrocaso asociado, y cuyo proveedor es el Stock Serv Tec
$query="select nro_orden,nrocaso,orden_de_compra.fecha_entrega,estado,monto_filas,id_moneda,dependencia
         from orden_de_compra 
         join casos_cdr using(nrocaso)
         left join dependencias using(id_dependencia)
         left join proveedor using(id_proveedor)
         left join( select sum(cantidad*precio_unitario) as monto_filas,nro_orden
               from fila group by nro_orden
         	 )as montos using(nro_orden)

         ";
	 
//la parte que trae el autor, se comenta por el momento
 /*left join(select nro_orden,nombre||' '||apellido as autor_oc 
      from log_ordenes join usuarios on user_login=usuarios.login
      where tipo_log='de creacion')as log using(nro_orden)*/

$where=" razon_social='Stock Serv. Tec. Bs. As.' ";

switch ($cmd)
{
	case "p":$where.=" and (estado='p' or estado='r')";//pendientes
	         break;
	case "u":$where.=" and estado='u'";//para autorizar
	         break;
	case "g":$where.=" and estado='g'";//finalizadas
	         break;
	default: break;
}//de switch ($cmd)

$orden=array(
            "default"=>"3",
            "default_up"=>"0",
            "1"=>"nro_orden",
            "2"=>"nrocaso",
            "3"=>"dependencia",
            "4"=>"fecha_entrega",
            "5"=>"monto_filas"
            //"6"=>"autor_oc"
            );
            
$filtro=array(
             "nro_orden"=>"Nº Orden",
             "nrocaso"=>"Nº Caso",
             "dependencia"=>"Dependencia"
             //"autor_oc"=>"Autor Orden"
             );            

$sumas = array(
 		"moneda" => "id_moneda",
 		"campo" => "monto_filas",
 		"mask" => array ("U\$S")
			  );
/***********************************************************************************************
 Fin de Seccion de Busqueda y generacion de la consulta para traer los datos
************************************************************************************************/
?>
<table width="100%" cellpadding="4">
 <tr id=mo>
  <td>
   <font size="2">Ordenes de Servicio Técnico</font>
  </td>
 </tr>
</table>
<?
$datos_barra = array(
                    array(
                        "descripcion"    => "Pendientes",
                        "cmd"            => "p"
                        ),
                    array(
                        "descripcion"    => "Para Autorizar",
                        "cmd"            => "u"
                        ),
                    array(
                        "descripcion"    => "Finalizadas",
                        "cmd"            => "g"
                        ),
                    array(
                        "descripcion"    => "Todas",
                        "cmd"            => "todas"
                        )
);

generar_barra_nav($datos_barra);

$link=encode_link("listado_oc_serv_tec.php",array());
?>
<form action="<?=$link?>" method="POST" name="form1">
<table width="100%">
 <tr>
  <td align="center">
	<?			  
	list($query,$total,$link_pagina,$up,$suma) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar",$sumas);
	$datos_oc=sql($query,"<br>Error al ejecutar consulta de busqueda<br>") or fin_pagina();             
	?>
    <input type="submit" name="buscar" value="Buscar">
  </td>
 </tr>
</table>  
<?
if($msg)
 echo "<div align='center'>$msg</div>";
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr id=ma>
	<td style="text-align:left" >
     <b>
      Total Ordenes de Compra: <?=$total?>
	 </b>
    </td>
    <td>
     <font color="Black">Total <?=$suma?> </font>
    </td>
    <td>
     <?=$link_pagina?>
    <td>
  </tr>
</table>  
<table border="0" cellspacing="2" cellpadding="0" width="100%">
  <tr id=mo>
    <td width="1%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"1","up"=>$up)) ?>'>Nº Orden</a>
    </td>
    <td width="1%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"2","up"=>$up)) ?>'>
      Nº Caso
     </a>
    </td>
    <td width="40%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"3","up"=>$up)) ?>'>
      Dependencia del Caso
     </a>
    </td>
    <td width="1%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"4","up"=>$up)) ?>'>
      Fecha Entrega
     </a>
    </td>
    <td width="10%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"5","up"=>$up)) ?>'>
      Monto
     </a>
    </td>
    <?/*<td width="15%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"6","up"=>$up)) ?>'>
      Autor
     </a>
    </td>
    */?>
  </tr>
  <?
  while (!$datos_oc->EOF)
  {
   $ref=encode_link("ord_compra.php",array("nro_orden"=>$datos_oc->fields["nro_orden"],"modo"=>"oc_serv_tec"));	 	
   //si el estado es rechazado mostramos la fila en amarillo
   if($datos_oc->fields["estado"]=="r")
   {?>
    <tr <?=atrib_tr("#FFFFC0")?>>
   <?
   }
   elseif($datos_oc->fields["estado"]=="n")
   {?>
    <tr <?=atrib_tr("#FF8080")?>>
   <?
   }
   else 
   {?>
    <tr <?=atrib_tr()?>>
   <?
   }
   
   ?>
    <a href='<?=$ref?>'>
    <td>
     <?=$datos_oc->fields["nro_orden"]?>
    </td>
    <td>
     <?=$datos_oc->fields["nrocaso"]?>
    </td>
    <td>
     <?=$datos_oc->fields["dependencia"]?>
    </td>
    <td>
     <?=Fecha($datos_oc->fields["fecha_entrega"])?>
    </td>
    <td>
     <table width="100%">
      <tr>
       <td>
        U$S
       </td>
       <td align="right">  
         <?=number_format($datos_oc->fields["monto_filas"],2,'.','')?>
       </td>
      </tr>
     </table>    
    </td>
    <?/*<td>
     <?=$datos_oc->fields["autor_oc"]?>
    </td>*/?>
    </a> 
   </tr>
   <?
   $datos_oc->MoveNext();
  }//de while(!$datos_oc->EOF)
?>
</table>
<?
if($cmd=="p" || $cmd=="todas")
{?>
	<br>
	<table width='95%' align="center" bgcolor="White" class="bordes">
	<tr>
	 <td><b>Colores de referencia para filas</b></td></tr>
	<?
	if($cmd=="p")
	{
	?>
		<tr>
		 <td width='25%' align='right'>Orden Rechazada </td>
		 <td width='2%' bgcolor="#FFFFC0" bordercolor ='#000000'>&nbsp;</td>
		 <td>&nbsp;</td>
		</tr>
	<?
	}//de if($cmd=="p")
	if($cmd=="todas")
	{
	?>
		<tr>
		 <td width='25%' align='right'>Orden Anulada </td>
		 <td width='2%' bgcolor="#FF8080" bordercolor ='#000000'>&nbsp;</td>
		 <td>&nbsp;</td>
		</tr>
	<?
	}//de if($cmd=="todas")
	?>
	</table>
<?
}//de if($cmd=="p" || $cmd=="todas")
?>
</form>
<?fin_pagina();?>