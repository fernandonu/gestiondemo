<?
/*
Author: GACZ

MODIFICADA POR
$Author: gabriel $
$Revision: 1.65 $
$Date: 2005/11/07 17:59:26 $
*/
include_once("../../config.php");
include_once("../../lib/funciones_monitoreo_cfc.php");
//extrae las variables de POST
extract($_POST,EXTR_SKIP);
if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

/*	
	//recupero el cliente
$q="select nombre from entidad left join licitacion using (id_entidad) where id_licitacion=$id_lic";
$res=sql($q, "error al ejecutar la consulta") or fin_pagina();
$nombre_entidad=$res->fields['nombre'];
echo "nombre entidad ".$nombre_entidad;
*/
// **********************************************************************************

//                  Version de la página disponible solo para dar de alta
//                  la oferta en una sola página. Esta version no edita.

//***********************************************************************************


//condicional para decidir que página mostrar.
if(($pagina_viene!='lic_ver_res')&&($_POST['editar']!='ok')&&($parametros['pagina']!='editar')) {

//link que pasa el id de licitacion a la ventana para cargar un renglon
//el id es necesario para mostrar los renglones cargados en dicha tabla
$link2=encode_link("lic_res_nuevo_renglon.php",array('id_licitacion'=>$id_lic,"pagina_volver"=>$pagina_volver));

if ($id_lic=="")
 $id_lic="-1";
//consulta con tabla vieja
//	$campos="id_renglon,nro_renglon,nro_item,nro_alternativa,cantidad,titulo";
	$campos="renglon.id_renglon,codigo_renglon,cantidad,titulo,licitacion.id_moneda";
	$query="FROM licitacion JOIN renglon on ".
	"licitacion.id_licitacion=renglon.id_licitacion AND licitacion.id_licitacion=$id_lic ";
	//	"ORDER BY nro_renglon,nro_item,nro_alternativa";
 	if ($id_renglon && $id_competidor)
	{
		$query.="LEFT JOIN oferta on oferta.id_renglon=renglon.id_renglon ".
		"AND oferta.id_renglon=$id_renglon AND oferta.id_competidor=$id_competidor ";
		//"LEFT JOIN competidores on oferta.id_competidor=competidores.id_competidor ";

		$campos.=",oferta.*";
	}
	//$query.=" ORDER BY nro_renglon,codigo_renglon";
    $query.=" ORDER BY codigo_renglon";
	$query="SELECT $campos $query ";
	$datos_lic=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);
	$found=$datos_lic->RecordCount();
	$query="SELECT moneda.id_moneda, moneda.nombre from moneda";
	$datos_moneda=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);
	$query="SELECT id_competidor,nombre from competidores order by nombre";
	$datos_comp=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);

//$datos_lic = variable que tiene los datos de toda la licitacion.
//$datos_moneda = variable que tiene los datos de la moneda.
//$datos_comp = variable que tiene los datos de el competidor.

if ($boton=="Aceptar")
{
// $campos="id_renglon,id_competidor,id_moneda,ganada,monto_unitario,observaciones";

$insert_res=true;
    if (!($competidor >0))
    {   $q="select * from competidores where nombre='$competidor'";
        $datos_comp=$db->Execute($q);

        $q_ncomp="insert into competidores (nombre) values ('$competidor')";
        //echo $q_ncomp;
        //para recuperar el id del último competidor dado de alta.
                 $query="SELECT max(id_competidor) as id_competidor from competidores";
               //  $max_competidor=$db->Execute($query) or die($query);
        if ($datos_comp->RowCount()==0)
        {
            if (!$db->Execute($q_ncomp))
            {
                $guardo="<center><b>ERROR NO SE PUDO GUARDAR EL COMPETIDOR</b></center>";
                $insert_res=false;
            }
            else
            {

                $query="SELECT id_competidor,nombre from competidores";
                $datos_comp=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);

                $q="select id_competidor,nombre from competidores where nombre='$competidor'";
                $datos_ncomp=$db->Execute($q); //or die($db->ErrorMsg()."<br>".$q);
                $competidor=$datos_ncomp->fields['id_competidor'];
            }
        }
        else
        {
            $query="SELECT id_competidor,nombre from competidores";
            $datos_comp=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);

            $q="select id_competidor,nombre from competidores where nombre='$competidor'";
            $datos_ncomp=$db->Execute($q); //or die($db->ErrorMsg()."<br>".$q);
            $competidor=$datos_ncomp->fields['id_competidor'];
        }

    }

//Iteracion para dar de alta en conjunto todos los resultados cargados de una licitacion
//Se guardan los resultados solo de los renglones en los cuales se han cargado.
$g_competidores_cargados="";
for($j=0;$j<$found;$j++) {
     $nomb_montou="monto_u".$j;
     $nomb_montot="monto_o".$j;
     $nomb_obs="observacion".$j;
     $moneda="select_moneda".$j;
     $renglon="renglon".$j;
     $gan_adj="ganada".$j;


   if(($_POST[$nomb_montou]!="")||($_POST[$nomb_montot]!="")){
                        $montt=$_POST[$nomb_montot];
                        $cant_reng=$datos_lic->fields["cantidad"];
                        if (es_numero($_POST[$nomb_montou]) and $_POST[$nomb_montou] != "") {
                            	$montuni=$_POST[$nomb_montou];
						}
						elseif (es_numero($montt)) {
                                $montuni=$montt/$cant_reng;
						}
                        //else

//Si se selecciona el edite aqui => se toma de la base de datos el ultimo elemento dado
//de alta que esta en la variable $datos_ncomp

                        $obse=$_POST[$nomb_obs];
                        if(es_numero($_POST['competidor'])) $comp=$_POST['competidor'];
                        else //$comp=$max_competidor->fields['id_competidor'];
                            $comp=$datos_ncomp->fields['id_competidor'];
                        $adj=$_POST[$gan_adj];
                        if($adj=='') $adj='f';
                        $id_mon=$_POST[$moneda];
                        $id_reng=$_POST[$renglon];

//control para no intentar insertar dos veces una oferta ya insertada con anterioridad.
  $query_control="SELECT id FROM oferta WHERE id_renglon=$id_reng AND id_competidor=$comp";
  $datos_oferta=$db->Execute($query_control) or die($db->ErrorMsg()."<br>".$query_control);
  $cantidad1=$datos_oferta->RecordCount();

  if($cantidad1 == 0) {
 	 $campos="id_renglon,id_competidor,id_moneda,ganada,monto_unitario,observaciones";
     $query="INSERT INTO oferta ($campos) VALUES ($id_reng,$comp,$id_mon,'$adj',$montuni,'$obse')";
     $g_competidores_cargados[]=$comp;
     if($db->Execute($query)) { echo "<center><b>SU RESULTADO SE GUARDO CON EXITO</b></center>";
                                echo "<input type='hidden' name='guardada' value='ok'>";
                              }
                        else { echo "<center><b>ERROR. NO SE PUDO GUARDAR EL RESULTADO</b></center>";
                               echo "<input type='hidden' name='guardada' value='no'>";
                           }
  }  //else echo "<center><b>ERROR. EL RESULTADO YA SE CARGO PREVIAMENTE. SI DESEA EDITAR PRESIONE 'EDITAR'</b></center>";
  $error=$db->ErrorMsg();

                        }
//print_r ($_POST['cantidad']);
//controles necesarios para saber si el alta fue o no exitosa.

$datos_lic->MoveNext();

}
//avisamos que se han cargado resultados
$query="update licitacion set resultados_cargados=1 where id_licitacion=$id_lic";
$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al avisar los resultados cargados");

//recupero el cliente
//$q="select nombre from entidad left join licitacion using (id_entidad) where id_licitacion=$id_lic";
$q="select id_licitacion, e.nombre, lider, u1.apellido||', '||u1.nombre as nombre_lider, 
		patrocinador, u2.apellido||', '||u2.nombre as nombre_patrocinador
	from licitaciones.licitacion l
		left join licitaciones.entidad e using (id_entidad) 
		left join sistema.usuarios u1 on (lider=u1.id_usuario)
		left join sistema.usuarios u2 on (patrocinador=u2.id_usuario)
	where id_licitacion=$id_lic";
$res=sql($q, "error al traer el nombre de la entidad") or fin_pagina();
$nombre_entidad=$res->fields['nombre'];
$nombre_lider=$res->fields["nombre_lider"];
$nombre_patrocinador=$res->fields["nombre_patrocinador"];
// para mandar mail cuando se carga un resultado
$asunto="Carga de Resulatdos para la Licitación Nro.".$id_lic;
$contenido="Se cargaron resultados para la Licitación Nro. <b>".$id_lic."</b><br>
	Cliente: <b>".$nombre_entidad."</b><br>
	Líder: <b>".$nombre_lider."</b><br>
	Patrocinador: <b>".$nombre_patrocinador."</b><br>";

//dir de mails o los saco d la tabal segun los usuarios a los q hay q enviarles el mail
//$para="";
//$para=substr($para, 0 ,strlen($para)-2);   
// en el grupo resultados_lic estan los usuarios a los cuales se les enviara un mail
//cuadno se cargue 1 resultado para tal licitacion

enviar_mail_html(to_group(array("resultados_lic")), $asunto, $contenido, $adjunto, $path, $tipo, $adj);
//echo "se envia el siguiente mail -- <br>".$asunto." <br>".$contenido;    

$datos_lic->MoveFirst();
	/*$insert_res=true;
	if (!($competidor >0))
	{   $q="select * from competidores where nombre='$competidor'";
		$datos_comp=$db->Execute($q);

		$q_ncomp="insert into competidores (nombre) values ('$competidor')";
        echo $q_ncomp;
		if ($datos_comp->RowCount()==0)
		{
			if (!$db->Execute($q_ncomp))
			{
				$guardo="<center><b>ERROR NO SE PUDO GUARDAR EL COMPETIDOR</b></center>";
				$insert_res=false;
			}
			else
			{
				$query="SELECT id_competidor,nombre from competidores";
				$datos_comp=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);

				$q="select id_competidor,nombre from competidores where nombre='$competidor'";
				$datos_ncomp=$db->Execute($q); //or die($db->ErrorMsg()."<br>".$q);
		 		$competidor=$datos_ncomp->fields['id_competidor'];
			}
		}
		else
		{
			$query="SELECT id_competidor,nombre from competidores";
			$datos_comp=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);

			$q="select id_competidor,nombre from competidores where nombre='$competidor'";
			$datos_ncomp=$db->Execute($q); //or die($db->ErrorMsg()."<br>".$q);
	 		$competidor=$datos_ncomp->fields['id_competidor'];
		}

	}   */

 // $ganada= ($ganada)?'t':'f' ;

//eliminar este if
/*
  if ($monto_u=="")
	 $monto_u=$monto_o/$cantidad;

  $campos="id_renglon,id_competidor,id_moneda,ganada,monto_unitario,observaciones";
  $query="INSERT INTO oferta ($campos) VALUES ($radio,$competidor,$select_moneda,'$ganada',$monto_u,'$observacion')";
  if (!$db->Execute($query))
   $error=$db->ErrorMsg();
  if ($insert_res && $error) //die($db->ErrorMsg()."<br>".$query);
  {
	 if (!$guardo)
	 {
		if (stristr($error,"oferta_unica") || stristr($error,"unica_oferta"))
	 	 $guardo="<center><b>EL RESULTADO YA SE HABIA GUARDADO</b></center>";
	 	else
	 	 $guardo="<center><b>ERROR. NO SE PUDO GUARDAR EL RESULTADO</b></center>";
	 }
  }
  else
   if (!$guardo)
  	$guardo="<center><b>SU RESULTADO SE GUARDO CON EXITO</b></center>";
    */
    
  /////////////////////////////////////// GABRIEL ////////////////////////////////////////////
  //control de cfc vencidos
  //IMPORTANTE
	//En caso de modificar estas líneas por algún motivo verificar coherencia en
	//"lic_cargar_res_nuevo", "monitoreo_cfc" y "lic_cargar_res"

  
  $contenido="Competidores con certificados vencidos o con errores en la verificación de C.F.C.:\n";
  $flag_cfc=false;
	for($i=0; $i<count($g_competidores_cargados); $i++){
		$rta_verif=verificarCFC($g_competidores_cargados[$i]);
		if (substr($rta_verif, 0 , 2)=="11"){
			$contenido.="- Id competidor=".$g_competidores_cargados[$i]." --> ".$rta_verif."\n";
			$flag_cfc=true;
		}
	}
	if ($flag_cfc){
		enviar_mail("licitaciones@coradir.com.ar", "Competidores con Certificados Fiscales para Contratar vencidos (licitación id=$id)", $contenido, "", "", "", 0);
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
    }

elseif ($boton=="Guardar")
{
/*
  $ganada= ($ganada)?'t':'f' ;
  if ($monto_u=="")
	 $monto_u=$monto_o/$cantidad;

  $query="UPDATE oferta set id_moneda=$select_moneda,ganada='$ganada',monto_unitario=$monto_u,observaciones='$observacion', ".
  "id_competidor=$competidor ".
  "where id_renglon=$id_renglon AND id_competidor=$id_comp";
  if ($db->Execute($query) && $db->Affected_rows())
  {
  	$guardo="<center><b>SU RESULTADO SE ACTUALIZO CON EXITO</b></center>";
  }
  else
    $guardo="<center><b>NO SE PUDO ACTUALIZAR</b></center>";
    */
}
elseif ($boton=="Ver Resultados")
{
 $link=encode_link("lic_ver_res.php",array('keyword'=>$id_lic,"pag_ant"=>$pagina,'pagina'=>"cargar_resultados","pagina_volver"=>$pagina_volver));
 header("location: $link")	;
}
?>
<!-- <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Licitaciones: Cargar Resultados</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
-->
<?
echo $html_header;
echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
<style type="text/css">
<!--
.tablaEnc {
	background-color: #006699;
	color: #c0c6c9;
	font-weight: bold;
}
-->
</style>
<?php
include("../ayuda/ayudas.php");
?>

</head>
<body bgcolor=#E0E0E0 topmargin="2">
<script src="<?=$html_root."/lib/funciones.js" ?>"> </script>

<script>
/*
calcula el monto total de dos input tipo text
toma la cantidad de una variable global

function calcular(unitario,total)
{

var u=(!isNaN(unitario.value))?unitario.value:0;
 var t=(!isNaN(total.value))?total.value:0;
 total.value=u*cantidad;
 unitario.value=t/cantidad;

} */
</script>
<?$link1=encode_link("lic_cargar_res.php",array('pagina'=>$pagina,"pagina_volver"=>$pagina_volver));?>
<form name="formulario" method="post" action="<?=$link1?>" >
<?=$guardo ?>
<table width="97%" align="center" class="bordes" cellspacing="1" cellpadding="1" bgcolor="<?=$bgcolor_out?>">
  <tr>
      <td width="15%">Id de Licitacion</td>


      <td width="85%"><div align='right'>
<!--<a href="#" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_cargar_res.htm" ?>', 'CARGAR RESULTADOS LICITACIONES')"> Ayuda </a>
-->
<img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_cargar_res.htm" ?>', 'CARGAR RESULTADOS LICITACIONES')" >
</div>
<br>

</td>
  </tr>
  <tr>
    <td><input name="id_lic" type="text" id="id_lic" size="15" value="<? if ($id_lic!=-1) echo $id_lic ?>"> </td>

    <td><input name="boton" type="submit" id="boton" value="Buscar renglones" title="Busca los renglones de la licitacion"><? if ($id_lic!=-1 && $datos_lic && ($datos_lic->RecordCount()<=0))

    

    																																									  echo "&nbsp;&nbsp; No se encontraron Resultados";
    																																									elseif ($id_renglon && $id_competidor)
    
    																																									   echo "<font color='#CC0033' style='text-decoration: blink;'><b>&nbsp;&nbsp; Editando Resultados</b></font>"; ?>
    </td>
    <?
     $link_d=encode_link("lic_cargar_res_nuevo.php",array("id_lic"=>$id_lic));          	
    ?>
    <!--<td><a href="<?=$link_d?>" target="_blank">Diego<input name="avanzada" value="Avanzada" title="Nuevo Formulario para cargar los Resultados" onclick="window.open"></a>&nbsp;&nbsp;</td>-->
    <td><input name="avanzada" type="button" value="Avanzada" title="Nuevo Formulario para cargar los Resultados" onclick="window.open('<?=$link_d?>','','resizable=1,scrollbars=yes,width=800,height=600,left=0,top=0,status=1')"></td>
    </tr>
    <tr>
    </td>
    <td>Seleccione un Competidor  <select name="competidor" id="competidor" onchange="beginEditing(this)">
     <option value='-1' >Seleccione el nombre del competidor</option>

<?

while (!$datos_comp->EOF)
    {
?>
<?//    <option value="<?=$datos_comp->fields['id_competidor']?>" <? if ($datos_comp->fields['id_competidor']==$id_competidor)echo 'selected'?>><?=$datos_comp->fields['nombre']?></option>
?>
         <option <?if($datos_comp->fields['id_competidor']==$competidor) echo 'selected'; ?> value="<?=$datos_comp->fields['id_competidor']?>"><?=$datos_comp->fields['nombre']?></option>

<?
         $datos_comp->MoveNext();
    }
?>
          <option id="editable">Añadir nuevo </option>
        </select> &nbsp;&nbsp;&nbsp;

      </td>
  </tr>
  </table>
<br>
<!-- <div style="position:relative; width:100%;height:80%; overflow:auto;">
-->

<?php
//Iteracion que genera la tabla en donde se muestran todos los renglones.

$i=0;
while (!$datos_lic->EOF) {
$cantidad=$datos_lic->fields['cantidad'];
?>

<br>
<table width="100%" class="bordes" cellspacing="1" cellpadding="1" bgcolor=<?=$bgcolor_out?>>
<tr class="tablaEnc">
      <td width="20%" align="rigth">Renglon: <?=$datos_lic->fields['codigo_renglon'] ?></td>
      <td width="10%"><div align="rigth">Cant: <?=$datos_lic->fields['cantidad'] ?></div></td>
      <td width="75%"><div align="rigth">Titulo: <?=$datos_lic->fields['titulo'] ?></div></td>
      <input type="hidden" name="cant_<?=$datos_lic->fields['id_renglon'] ?>" value=<?=$datos_lic->fields['cantidad']?> >
      <input type="hidden" name="renglon<?=$i?>" value="<?=$datos_lic->fields['id_renglon']?>"
</tr>
<?
/*
</table>
 <table width="100%" border="0" cellspacing="1" cellpadding="1">
<tr>  <td width="28%" align="center"><?=$datos_lic->fields['codigo_renglon'] ?></td>
      <td width="10%" align="center"><?=$datos_lic->fields['cantidad'] ?></td>
      <td width="57%" align="center"><?=$datos_lic->fields['titulo'] ?></td>
      <input type="hidden" name="cant_<?=$datos_lic->fields['id_renglon'] ?>" value=<?=$datos_lic->fields['cantidad']?> >
</tr>
*/
?>
<?
		if ($datos_lic->fields['id_renglon']==$id_renglon && $datos_lic->fields['id_competidor']==$id_competidor)
		{
			$observacion=$datos_lic->fields['observaciones'];
			$monto_u=number_format($datos_lic->fields['monto_unitario'],2,".","");
			$monto_o=number_format($datos_lic->fields['monto_unitario']*$datos_lic->fields['cantidad'],2,".","");
			$ganada=($datos_lic->fields['ganada']=='t')?' checked':'';
		}

?>
  </table>



</div>
  <br>
  <table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="<?=$bgcolor2?>">
    <tr>

      <td width="20%">Tipo de moneda </td>
      <td width="50%">
<?
$nombre_moneda="select_moneda".$i;
echo  "<select name='$nombre_moneda'>";

/*$datos_moneda->Move(0);
echo "Moneda en query".$datos_moneda->fields['id_moneda'];
echo "<br>";
echo "Moneda en tabla oferta".$datos_lic->fields['id_moneda'];  */
$datos_moneda->Move(0);

while (!$datos_moneda->EOF)
{
?>

	 <option <?if(($datos_moneda->fields['id_moneda'])==($datos_lic->fields['id_moneda'])) echo 'selected'?>  value="<?=$datos_moneda->fields['id_moneda']?>"><?=$datos_moneda->fields['nombre']?></option>
<?
	 $datos_moneda->MoveNext();
}

?>
      		</SELECT>
      </td>
      <td width="20%" valign="middle">

<?
  $nganada="ganada".$i;
  //datos necesarios para el query siguiente.
    //$reng=$datos_lic->fields['id_renglon'];
    //$comp=$competidor;
  //query para controlar los datos que están en la base de datos.
 /* $query_oferta="SELECT * from oferta WHERE id_renglon=$reng
  AND id_competidor=$comp";
  $datos_oferta=$db->Execute($query_oferta) or die($db->ErrorMsg()."<br>".$query_oferta);
  */
?>
        <input type="checkbox" name="<?=$nganada?>" value="t" <?=$ganada?>>
        Adjudicada</td>

    </tr>
  </table>
 <table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="<?=$bgcolor2?>">
 <tr>
       <td>Monto Oferta Unitario</td>
       <? //captura.
         //Nombres dinamicos de los campos de texto y del textarea de observacion
         //dichos nombres tienen esta forma porque deben darse de alta en conjunto.
          $nomb_montou="monto_u".$i;
          $nomb_montot="monto_o".$i;
          $nomb_obs="observacion".$i;
          $guardada=$_POST['guardada'];
   //    $reng=$datos_lic->fields['id_renglon'];
   //     $comp=$competidor;
          //query para controlar los datos que están en la base de datos.
          /*$query_oferta="SELECT * from oferta WHERE id_renglon=$reng
          AND id_competidor=$comp";
          $datos_oferta=$db->Execute($query_oferta) or die($db->ErrorMsg()."<br>".$query_oferta);*/
          $obser=$datos_oferta->fields['observaciones'];
       ?>


       <td><input name="<?=$nomb_montou?>" type="text" id="monto_u" value="<?/*=$datos_oferta->fields['monto_unitario']*/ ?>" size="8" onkeypress="//calcular(this,<?=$nomb_montot?>)" <?if ($datos_oferta->fields['monto_unitario']!='') echo 'disabled';?> style="font-family:arial;font-size:12;"</td>
       <td align='center'>Observacion</td>
 </tr>
 <tr>
       <td>Monto Oferta Total</td>
       <td><input name="<?=$nomb_montot?>" type="text" id="monto_o" size="10" value="<?/*if ($guardada=='ok') echo $_POST[$nomb_montot]; */?>" onkeypress="//calcular(<?=$nomb_montou?>,this)" ></td>
       <td align='center'><textarea name="<?=$nomb_obs?>" cols="55" wrap="VIRTUAL" id="observacion"><?/*=$obser*/?></textarea></td>
 </tr>
</table>

<input type="hidden" name="cantidad<?=$i?>" value="<?=($cantidad)?$cantidad:0?>">

<script>
function calcular(unitario,total)
{
 /*
 var u=(!isNaN(unitario.value))?unitario.value:0;
 var t=(!isNaN(total.value))?total.value:0;
 total.value=u*<?=$cantidad?>;
 unitario.value=t/<?=$cantidad?>;
 */
}
</script>

<?

$datos_lic->MoveNext();
$i++; //variable de control para tomar los campos a darse de alta.
  } //fin del while
  ?>

<hr>
  <table width="50%" align="center" cellspacing="1" cellpadding="1">
    <tr>
       <td>
          <input name="boton" type="submit" id="boton" style="width:100%" value="<?=($id_renglon && $id_competidor)?'Guardar': 'Aceptar'?>" onclick="



if (document.all.competidor[document.all.competidor.selectedIndex].value==-1)
{
	alert ('Debe seleccionar un competidor');
	return false;
}
/*
if (document.all.monto_o.value.indexOf(',')!=-1)
{
	alert ('Por favor use el punto para los valores decimales');
	return false;
}
*/

<?
$i=0;
while ($i<$found)
{
?>

       if (document.all.monto_u<?=$i?>.value.indexOf(',')!=-1)
       {
       	alert ('Por favor use el punto para los valores decimales');
        	return false;
       }


if (document.all.monto_o<?=$i?>.value!='')
{
	if (isNaN(parseFloat(document.all.monto_o<?=$i?>.value)))
	{
		alert ('El monto de oferta total debe ser un numero valido');
		return false;
	}
}
if (document.all.monto_u<?=$i?>.value!='')
{
	if (isNaN(parseFloat(document.all.monto_u<?=$i?>.value)))
	{
		alert ('El monto de oferta unitario debe ser un numero valido');
		return false;
	}
}
/*if ((document.all.monto_o<?=$i?>.value=='') && (document.all.monto_u<?=$i?>.value==''))
{
	alert ('Debe ingresar algun monto');
	return false;
}   */

<?$i++;
} ?>//fin del while
">


   		</td>
      <td><input name="boton" type="submit" id="boton" value="Ver Resultados" style="width:100%"></td>
      <td><input name="agregar_r" type="button" id="agregar_r" value="Agregar" title="Agregar renglon" <? if (($id_renglon && $id_competidor) || $id_lic=="-1") echo " disabled"?> onclick="window.open('<?echo $link2; ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=100,width=500,height=325');
			document.all.agregar_r.disabled=1;" style="width:100%"></td>
      <?if($pagina=="lic")
        {?>
         <td><input type="button" align="left" name="volver" value="Volver" onclick="document.location.href='<?=$pagina_volver?>'" style="width:100%">
      <?}?>
      <? //if(($id_lic!=-1)&&($boton!="Aceptar"))
        if($id_lic!=-1)
        {$link=encode_link("$pagina_volver",array("cmd1"=>"detalle","ID"=>$id_lic,"pagina_volver"=>$pagina_volver));?>
         <td><input type="button" align="left" name="volver" value="Volver" onclick="document.location.href='<?echo $link;?>'" style="width:100%">
      <?}?>

    </tr>
  </table>
<!--
  <input type="hidden" name="cantidad" value="<?=($cantidad)?$cantidad:0?>">
-->
<input type="hidden" name="id_renglon" value="<?=($id_renglon)?$id_renglon:0?>">
<input type="hidden" name="id_comp" value="<?=($id_competidor)?$id_competidor:0?>">

<?
}

//***************************************************************************************
//***************************************************************************************

//         Version de la página disponible solo para actualizar (editar) los datos.

//***************************************************************************************
//***************************************************************************************

if(($pagina_viene=='lic_ver_res')||($parametros['pagina']=='editar')) {

//echo "<input type='hidden' name='editar' value='ok'>";

include_once("../../config.php");

//extrae las variables de POST
extract($_POST,EXTR_SKIP);
if ($parametros)
    extract($parametros,EXTR_OVERWRITE);

//link que pasa el id de licitacion a la ventana para cargar un renglon
//el id es necesario para mostrar los renglones cargados en dicha tabla
$link2=encode_link("lic_res_nuevo_renglon.php",array('id_licitacion'=>$id_lic));

if ($id_lic=="")
 $id_lic="-1";
//consulta con tabla vieja
//    $campos="id_renglon,nro_renglon,nro_item,nro_alternativa,cantidad,titulo";
    $campos="renglon.id_renglon,codigo_renglon,cantidad,titulo,licitacion.id_moneda";
    $query="FROM licitacion JOIN renglon on ".
    "licitacion.id_licitacion=renglon.id_licitacion AND licitacion.id_licitacion=$id_lic ";
    //    "ORDER BY nro_renglon,nro_item,nro_alternativa";
     if ($id_renglon && $id_competidor)
    {
        $query.="LEFT JOIN oferta on oferta.id_renglon=renglon.id_renglon ".
        "AND oferta.id_renglon=$id_renglon AND oferta.id_competidor=$id_competidor ";
        //"LEFT JOIN competidores on oferta.id_competidor=competidores.id_competidor ";

        $campos.=",oferta.*";
    }
    $query.=" ORDER BY nro_renglon,codigo_renglon";
    $query="SELECT $campos $query ";
    $datos_lic=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);
    $found=$datos_lic->RecordCount();
    $query="SELECT moneda.id_moneda, moneda.nombre from moneda";
    $datos_moneda=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);
    $query="SELECT id_competidor,nombre from competidores order by nombre";
    $datos_comp=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);

if ($boton=="Aceptar")
{
    $insert_res=true;
    if (!($competidor >0))
    {
        $q="select * from competidores where nombre='$competidor'";
        $datos_comp=$db->Execute($q);
        $q_ncomp="insert into competidores (nombre) values ('$competidor')";
        if ($datos_comp->RowCount()==0)
        {
            if (!$db->Execute($q_ncomp))
            {
                $guardo="<center><b>ERROR NO SE PUDO GUARDAR EL COMPETIDOR</b></center>";
                $insert_res=false;
            }
            else
            {
                $query="SELECT id_competidor,nombre from competidores";
                $datos_comp=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);

                $q="select id_competidor,nombre from competidores where nombre='$competidor'";
                $datos_ncomp=$db->Execute($q); //or die($db->ErrorMsg()."<br>".$q);
                 $competidor=$datos_ncomp->fields['id_competidor'];
            }
        }
        else
        {
            $query="SELECT id_competidor,nombre from competidores";
            $datos_comp=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);

            $q="select id_competidor,nombre from competidores where nombre='$competidor'";
            $datos_ncomp=$db->Execute($q); //or die($db->ErrorMsg()."<br>".$q);
             $competidor=$datos_ncomp->fields['id_competidor'];
        }

    }

  $ganada= ($ganada)?'t':'f' ;

//eliminar este if
  if ($monto_u=="")
     $monto_u=$monto_o/$cantidad;

  $campos="id_renglon,id_competidor,id_moneda,ganada,monto_unitario,observaciones";
  $query="INSERT INTO oferta ($campos) VALUES ($radio,$competidor,$select_moneda,'$ganada',$monto_u,'$observacion')";
  if (!$db->Execute($query))
   $error=$db->ErrorMsg();
  if ($insert_res && $error) //die($db->ErrorMsg()."<br>".$query);
  {
     if (!$guardo)
     {
        if (stristr($error,"oferta_unica") || stristr($error,"unica_oferta"))
          $guardo="<center><b>EL RESULTADO YA SE HABIA GUARDADO</b></center>";
         else
          $guardo="<center><b>ERROR. NO SE PUDO GUARDAR EL RESULTADO</b></center>";
     }
  }
  else
   if (!$guardo)
      $guardo="<center><b>SU RESULTADO SE GUARDO CON EXITO</b></center>";
  
   //avisamos que se han cargado resultados
  $query="update licitacion set resultados_cargados=1 where id_licitacion=$id_lic";
  $db->Execute($query) or die ($db->ErrorMsg()."<br>Error al avisar los resultados cargados");   
}
elseif ($boton=="Guardar")  //guardar------------------------------------------
{
  $ganada= ($ganada)?'t':'f' ;
  if ($monto_u=="")
     $monto_u=$monto_o/$cantidad;
  
  if (($monto_u=="") && ($monto_o==""))
  {$monto_u=$monto_unitario;
   $monto_o=$monto_total;
  }
  
  $query="UPDATE oferta set id_moneda=$select_moneda,ganada='$ganada',monto_unitario=$monto_u,observaciones='$observacion', ".
  "id_competidor=$competidor ".
  "where id_renglon=$id_renglon AND id_competidor=$id_comp";
  if ($db->Execute($query) && $db->Affected_rows())
  {
      $guardo="<center><b>SU RESULTADO SE ACTUALIZO CON EXITO</b></center>";
  }
  else
    $guardo="<center><b>NO SE PUDO ACTUALIZAR</b></center>";

 $link=encode_link("lic_ver_res2.php",array('keyword'=>$id_lic,"pag_ant"=>$pagina,'pagina'=>"cargar_resultados","pagina_volver"=>$pagina_volver));
 header("location: $link")    ;
   
}
elseif ($boton=="Ver Resultados")
{
 $link=encode_link("lic_ver_res.php",array('keyword'=>$id_lic,"pag_ant"=>$pagina,'pagina'=>"cargar_resultados","pagina_volver"=>$pagina_volver));
 header("location: $link")    ;
}
echo "<input type='hidden' name='editar' value='ok'>";

echo $html_header;

echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
<style type="text/css">
<!--
.tablaEnc {
    background-color: #006699;
    color: #c0c6c9;
    font-weight: bold;
}
-->
</style>
<?php
include("../ayuda/ayudas.php");
?>

</head>
<body bgcolor=#E0E0E0 topmargin="2">
<script src="<?=$html_root."/lib/funciones.js" ?>"> </script>

<script>
/*
calcula el monto total de dos input tipo text
toma la cantidad de una variable global
*/
function calcular(unitario,total)
{
/*
var u=(!isNaN(unitario.value))?unitario.value:0;
 var t=(!isNaN(total.value))?total.value:0;
 total.value=u*cantidad;
 unitario.value=t/cantidad;
*/
}
</script>
<?$link1=encode_link("lic_cargar_res.php",array('pagina'=>'editar',"pagina_volver"=>$pagina_volver));?>
<form name="formulario" method="post" action="<?=$link1?>" >
<?=$guardo ?>
<table width="100%" class="bordes" cellspacing="1" cellpadding="1" bgcolor="<?=$bgcolor2?>">
  <tr>
      <td width="15%">Id de Licitacion</td>
    <td width="85%"><div align='right'>
	<img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_cargar_res1.htm" ?>', 'EDITAR RESULTADOS LICITACIONES')" >

</div>
<br>

</td>
  </tr>
  <tr>
    <td><input name="id_lic" type="text" id="id_lic" size="15" value="<? if ($id_lic!=-1) echo $id_lic ?>"> </td>

    <td><input name="boton" type="submit" id="boton" value="Buscar renglones" title="Busca los renglones de la licitacion"><? if ($id_lic!=-1 && $datos_lic && ($datos_lic->RecordCount()<=0))
                                                                                                                                                                          echo "&nbsp;&nbsp; No se encontraron Resultados";
                                                                                                                                                                        elseif ($id_renglon && $id_competidor)
                                                                                                                                                                           echo "<font color='#CC0033' style='text-decoration: blink;'><b>&nbsp;&nbsp; Editando Resultados</b></font>"; ?>
    </td>
    
  </tr>
  </table>
<br>
<table width="100%" border=0 cellspacing="2" cellpadding="1" align="center" >
<tr class="tablaEnc">
      <!--<td width="3%" >&nbsp;</td>-->
      <td width="32%" align="center">Renglon</td>
      <td width="9%"><div align="center">Cantidad</div></td>
      <td width="59%"><div align="center">Titulo</div></td>

</tr>

</table>
<div style="position:relative; width:100%; height:25%; overflow:auto">
 <table width="100%" cellspacing="2" cellpadding="1">


 <?
  while (!$datos_lic->EOF)
  {
?>
<tr bgcolor=<?=$bgcolor_out?>>
      <td  align="center" width="3%"><input type="radio" name="radio" value="<?=$datos_lic->fields['id_renglon'] ?>"
        <? if ($datos_lic->fields['id_renglon']==$id_renglon)
            {
                 echo' checked';
                 $cantidad=$datos_lic->fields['cantidad'];

            }
           elseif ($id_renglon && $id_competidor) echo ' disabled'; ?> onclick="cantidad.value=cant_<?=$datos_lic->fields['id_renglon']?>.value"></td>
      <td width="28%" align="center"><?=$datos_lic->fields['codigo_renglon'] ?></td>
      <td width="10%" align="center"><?=$datos_lic->fields['cantidad'] ?></td>
      <td width="57%" align="center"><?=$datos_lic->fields['titulo'] ?></td>
      <input type="hidden" name="cant_<?=$datos_lic->fields['id_renglon'] ?>" value=<?=$datos_lic->fields['cantidad']?> >
</tr>

<?
        if ($datos_lic->fields['id_renglon']==$id_renglon && $datos_lic->fields['id_competidor']==$id_competidor)
        {
            $observacion=$datos_lic->fields['observaciones'];
            $monto_u=number_format($datos_lic->fields['monto_unitario'],2,".","");
            $monto_o=number_format($datos_lic->fields['monto_unitario']*$datos_lic->fields['cantidad'],2,".","");
            $ganada=($datos_lic->fields['ganada']=='t')?' checked':'';
        }
        $datos_lic->MoveNext();
    }

?>
  </table>
</div>
  <br>
  <table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="<?=$bgcolor2?>">
    <tr>
      <td width="27%">Competidor </td>
      <td width="73%" valign="middle">
          <select name="competidor" id="competidor" onchange="beginEditing(this)">
            <option value='-1' >Seleccione el nombre del competidor</option>
<?
    while (!$datos_comp->EOF)
    {
?>
<?//    <option value="<?=$datos_comp->fields['id_competidor']?>" <? if ($datos_comp->fields['id_competidor']==$id_competidor)echo 'selected'?>><?=$datos_comp->fields['nombre']?></option>

?>
         <option <?if($pagina_viene=='lic_ver_res'){if($datos_comp->fields['id_competidor']==$id_competidor) echo 'selected';} if($datos_comp->fields['id_competidor']==$competidor) echo 'selected'; ?> value="<?=$datos_comp->fields['id_competidor']?>"><?=$datos_comp->fields['nombre']?></option>

<?
         $datos_comp->MoveNext();
    }
?>
          <option id="editable">Añadir nuevo </option>
        </select> &nbsp;&nbsp;&nbsp; <input type="checkbox" name="ganada" value="t" <?=$ganada?>>
        Adjudicada</td>
    </tr>
    <tr>
      <td>Observacion</td>
      <td><textarea name="observacion" cols="45" wrap="VIRTUAL" id="observacion"><?=$observacion?></textarea></td>
    </tr>
    <tr>
      <td>Tipo de moneda</td>
      <td> <select name="select_moneda">
<?
$datos_lic->Move(0);
while (!$datos_moneda->EOF)
{
?>
     <option value="<?=$datos_moneda->fields['id_moneda']?>" <?if($pagina_viene=='lic_ver_res'){if($datos_moneda->fields['id_moneda']==$id_moneda) echo 'selected';} if ($datos_moneda->fields['id_moneda']==$datos_lic->fields['id_moneda']) echo 'selected'?>><?=$datos_moneda->fields['nombre']?></option>
<?
     $datos_moneda->MoveNext();
}

?>
              </SELECT>
      </td>
    </tr>
    <tr>
      <td>Monto Oferta Unitario:</td>
      <td><b><?if($pagina_viene=='lic_ver_res')echo $monto_u?></b>
      <input name="monto_u" type="text" id="monto_u" value="<?/*if($pagina_viene=='lic_ver_res')echo $montuni */?>" size="10" onkeypress="calcular(this,monto_o)"></td>
    </tr>
    <tr>
      <td>Monto Oferta Total:</td>
      <td><b><?if($pagina_viene=='lic_ver_res')echo $monto_o?></b>
      <input name="monto_o" type="text" id="monto_o" size="10" value="<?/*if($pagina_viene=='lic_ver_res')echo $monto_o*/ ?>" onkeypress="calcular(monto_u,this)" ></td>
    </tr>
  </table>
 <input type="hidden" name="monto_unitario" value="<? if($pagina_viene=='lic_ver_res')echo $monto_u ?>">
 <input type="hidden" name="monto_total" value="<? if($pagina_viene=='lic_ver_res')echo $monto_o?> ">
  <table width="50%" align="center" cellspacing="1" cellpadding="1">
    <tr>
       <td>
          <input name="boton" type="submit" id="boton" style="width:100%" value="<?=($id_renglon && $id_competidor)?'Guardar': 'Aceptar'?>" onclick=
"
var checked=false;
if (typeof(document.all.radio)!='undefined' && document.all.radio.length)
    for (var i=0; i < document.all.radio.length ; i++)
    {
        if ((checked=document.all.radio[i].checked))
            break;
    }
else
    checked=document.all.radio.checked;
if (!checked)
{
    alert ('Debe elegir un renglon');
    return false;
}

if (document.all.competidor[document.all.competidor.selectedIndex].value==-1)
{
    alert ('Debe seleccionar un competidor');
    return false;
}
if (document.all.monto_o.value.indexOf(',')!=-1)
{
    alert ('Por favor use el punto para los valores decimales');
    return false;
}
if (document.all.monto_u.value.indexOf(',')!=-1)
{
    alert ('Por favor use el punto para los valores decimales');
    return false;
}
if (document.all.monto_o.value!='')
{
    if (isNaN(parseFloat(document.all.monto_o.value)))
    {
        alert ('El monto de oferta total debe ser un numero valido');
        return false;
    }
}
if (document.all.monto_u.value!='')
{
    if (isNaN(parseFloat(document.all.monto_u.value)))
    {
        alert ('El monto de oferta unitario debe ser un numero valido');
        return false;
    }
}
/*if (document.all.monto_o.value=='' && document.all.monto_u.value=='')
{
    alert ('Debe ingresar algun monto');
    return false;
}*/
">
           </td>
      <td><input name="boton" type="submit" id="boton" value="Ver Resultados" style="width:100%"></td>
      <td><input name="agregar_r" type="button" id="agregar_r" value="Agregar" title="Agregar renglon" <? if (($id_renglon && $id_competidor) || $id_lic=="-1") echo " disabled"?> onclick="window.open('<?echo $link2; ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=100,width=500,height=325');
            document.all.agregar_r.disabled=1;" style="width:100%"></td>
      <?if($pagina=="lic")
        {?>
         <td><input type="button" align="left" name="volver" value="Volver" onclick="document.location.href='<?=$pagina_volver?>'" style="width:100%">
      <?}?>
      <? //if(($id_lic!=-1)&&($boton!="Aceptar"))
        if($id_lic!=-1)
        {$link=encode_link("$pagina_volver",array("cmd1"=>"detalle","ID"=>$id_lic,"pagina_volver"=>$pagina_volver));?>
         <td><input type="button" align="left" name="volver" value="Volver" onclick="document.location.href='<?echo $link;?>'" style="width:100%">
      <?}?>

    </tr>
  </table>
<input type="hidden" name="cantidad" value="<?=($cantidad)?$cantidad:0?>">
<input type="hidden" name="id_renglon" value="<?=($id_renglon)?$id_renglon:0?>">
<input type="hidden" name="id_comp" value="<?=($id_competidor)?$id_competidor:0?>">

</form>
</body>
</html>

<?
} //fin de if($pagina_viene=='lic_ver_res')
?>

</form>
</body>
</html>
