<?php
/*
$Author: marco_canderle $
$Revision: 1.12 $
$Date: 2006/05/12 20:58:54 $
*/
require_once("../../config.php");

$id_falla=$parametros["id_falla"];
$sort = $_POST["sort"] or $sort=$parametros["sort"];
$up   = $_POST["up"] or $up=$parametros["up"];

//print_r($parametros);

variables_form_busqueda("caso_fallas_asociadas");
//print_r($consulta);
$query_casos=$parametros["consulta"];
//echo "<br>$query_casos<br>";
$resultado=$db->execute($query_casos) or die($query_casos."<br>".$db->errormsg());
//print_r($parametros);
for($y=0;$y<$resultado->recordcount();$y++){
               $nrocasos[$y]=$resultado->fields["idcaso"];
               $resultado->movenext();
}//del for



$sql=" select desc_falla from fallas ";
if ($id_falla) $sql.="where id_falla=$id_falla";
else $sql.="where id_falla is NULL";
$resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
$desc_falla=$resultado->fields["desc_falla"];
$sql=" select casos_cdr.idcaso,casos_cdr.nrocaso,casos_cdr.nserie,casos_cdr.costofin,casos_cdr.deperfecto,
        entidad.nombre,entidad.id_entidad,cas_ate.nombre as atendido,
        ensamblador.nombre as nombre_ensamblador
        from casos.casos_cdr
        join casos.dependencias using(id_dependencia)
        join licitaciones.entidad using (id_entidad)
        join casos.cas_ate using(idate)
        left join  ordenes.maquina on (maquina.nro_serie=casos_cdr.nserie)
        left join  ordenes.orden_de_produccion using(nro_orden)
        left join  ordenes.ensamblador using (id_ensamblador)
     ";
if ($id_falla)
	$where=" casos_cdr.id_falla=$id_falla and (";
else
	$where=" casos_cdr.id_falla is NULL and (";

for($i=0;$i<sizeof($nrocasos);$i++){
   if ($i==sizeof($nrocasos)-1)
         $where.=" idcaso=".$nrocasos[$i];
         else
         $where.=" idcaso=".$nrocasos[$i]." or ";

}
$where.=")";
//print_r($parametros);
$orden=array(
            "default" => "1",
            "1" => "nrocaso",
            "2" => "nombre",
            "3" => "atendido",
            "4" => "casos_cdr.nserie",
            "5" => "nombre_ensamblador",
            "6" => "costofin",
            "7" => "deperfecto"
           );

$filtro= array(
               "nrocaso"=>"Nro Caso",
               "cas_ate.nombre"=>"Atendido Por",
               "entidad.nombre"=>"Entidad"
                );


$contar="buscar";


//entra si le di al boton generar excel
//print_r($parametros['consulta']);

if ($download=$parametros['download'])
{
	$excel_name="CAS con Fallas.xls";
	//$html_header;
	excel_header($excel_name);
}
else {echo $html_header; ?>

<form name=form1 method=post>
<table width=90% align=Center>
 <tr>
   <td id="mo">
       C.A.S. con las Fallas: <?=$desc_falla?>
   </td>
 </tr>
 <tr>
  <td align=center>

<?
}//del else
//echo $sql;

//imprime lo del form busqueda
if (!$download){
$itemspp=100000;
list($query,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,$contar);
$resultado=$db->execute($query) or die($query."<br>".$db->errormsg());

	echo "<input type=submit name=form_busqueda value='Buscar'>";
 	echo "&nbsp;&nbsp;&nbsp; ";
 	echo "&nbsp;&nbsp;<a target=_blank title=\"Bajar datos en un excel&nbsp;&nbsp;'CAS con Fallas.xls'\" href='". encode_link($_SERVER['SCRIPT_NAME'],array('download'=>1,"consulta"=>$parametros['consulta'],"id_falla"=>$parametros['id_falla'])) ."'><img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' ></a>";
	echo " </td>";
 	echo "</tr>";
	echo "</table>";
}//del if para que no me imprima en el listado e imprima en la pagina

else {//lo ejecuta si va por el excel pero no imprime lo del form busqueda
$itemspp=100000;
echo "<!--";
list($query,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,$contar);
echo "-->";
$resultado=$db->execute($query) or die($query."<br>".$db->errormsg());
}
	//este if es para que no saque por el listado el else lo saca
    if (!$download) {?>
    <table width=90% align=center>
  		<tr>
   			<td>
       			<table width=100% align=center border=1 cellspading=0 cellspacing=0 bordercolor=<?=$bgcolor1?>>

       		<tr>
                 <td id=ma_sf colspan=7>
                 Total:<?=$total?>
                 </td>
          	</tr>

          <tr id=ma>
            <?$link=encode_link("caso_fallas_asociadas.php",array("sort"=>1,"up"=>$up,"id_falla"=>$id_falla,"consulta"=>$query_casos));?>
            <a href=<?=$link?>>
              <td align=center width="15%"><b>Nro de C.A.S.</td>
            </a>
            <?$link=encode_link("caso_fallas_asociadas.php",array("sort"=>7,"up"=>$up,"id_falla"=>$id_falla,"consulta"=>$query_casos));?>
            <a href=<?=$link?>>
              <td align=center width="15%"><b>Falla</td>
            </a>
            <?$link=encode_link("caso_fallas_asociadas.php",array("sort"=>2,"up"=>$up,"id_falla"=>$id_falla,"consulta"=>$query_casos));?>
            <a href=<?=$link?>>
             <td align=center width="45%"><b>Cliente </td>
            </a>
            <?$link=encode_link("caso_fallas_asociadas.php",array("sort"=>3,"up"=>$up,"id_falla"=>$id_falla,"consulta"=>$query_casos));?>
            <a href=<?=$link?>>
             <td align=center width="40%"><b>Atendido Por</td>
            </a>
            <?$link=encode_link("caso_fallas_asociadas.php",array("sort"=>4,"up"=>$up,"id_falla"=>$id_falla,"consulta"=>$query_casos));?>
            <a href=<?=$link?>>
             <td align=center width="40%"><b>Nro Serie</td>
            </a>
            <?$link=encode_link("caso_fallas_asociadas.php",array("sort"=>5,"up"=>$up,"id_falla"=>$id_falla,"consulta"=>$query_casos));?>
            <a href=<?=$link?>>
             <td align=center width="40%"><b>Ensamblador</td>
            </a>
            <?$link=encode_link("caso_fallas_asociadas.php",array("sort"=>6,"up"=>$up,"id_falla"=>$id_falla,"consulta"=>$query_casos));?>
            <a href=<?=$link?>>
             <td align=center width="40%"><b>Precio Final en $</td>
            </a>

          </tr>
          <?
     	}//del if para que no salga en el listado
     	else {
     	?>

     	<table>
   			<td>
       		<table border="1">
   		   <tr id=ma bgcolor="Lime">
            <td align=center width="15%">Nro de C.A.S.</td>
            <td align=center width="15%">Falla</td>
            <td align=center width="45%">Cliente </td>
            <td align=center width="40%">Atendido Por</td>
            <td align=center width="40%">Nro Serie</td>
            <td align=center width="40%">Ensamblador</td>
            <td align=center width="40%">Precio Final en $</td>
           </tr>

          <?
     		}//del else por aca imprime en el listado

     	$cant_casos=$resultado->recordcount();
     	for ($i=0;$i<$cant_casos;$i++)
          {
          $ref = encode_link("caso_estados.php",Array("id"=>$resultado->fields["idcaso"],"id_entidad"=>$resultado->fields['id_entidad']));


          //es un condicional por el listado de excel
          if (!$download){?> <tr <?=$atrib_tr?> onclick="window.open('<?=$ref?>')" >
          <?
          }
          else{?>
          <tr>
          <?}?>

            <td align=center <?echo excel_style("texto")?> ><?=$resultado->fields["nrocaso"]?> </td>
			<td><?=$resultado->fields["deperfecto"]?></td>
          	<td><?=$resultado->fields["nombre"]?> </td>
            <td><?=$resultado->fields["atendido"]?></td>
            <?
            if ($resultado->fields["nserie"])
               {
               ?>
               <td><?=$resultado->fields["nserie"];?></td>
               <?
               if ($resultado->fields["nombre_ensamblador"])
               {
               ?>
                <td><?=$resultado->fields["nombre_ensamblador"];?></td>
                    <?
               }
                    else{
                    ?>
                   <td>&nbsp</td>
              <?
                    }
              }
              else {
             ?>
             <td colspan=2 align=Center> No tiene </td>
             <?
              }
            ?>

          <?
          //convierto a entero el valor texto de la base de datos
          $montofinal=$resultado->fields["costofin"];
          settype($montofinal, "integer");
          ?>
          <td <?echo excel_style("$")?>> <?=$montofinal?></td>

         </tr>



         <?
           $resultado->movenext();
          }//del for
          ?>
       </table>
    <td>
  </tr>
  <tr>
    <td align=center>
      <?if (!$download){?><input type=button name=cerrar value=Cerrar onclick="window.close()"><?}?>
    </td>
  </tr>
</table>
</form>