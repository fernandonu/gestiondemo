<?php
/*
$Author: fernando $
$Revision: 1.4 $
$Date: 2005/05/20 20:08:58 $
*/
require_once ("../../config.php");

$id_usuario=$_ses_user["id"];

variables_form_busqueda("listado_visitas");




$datos_barra = array(
                    array(
                        "descripcion"    => "Pendientes",
                        "cmd"            => "pendientes"
                        ),
                    array(
                        "descripcion"    => "Historial",
                        "cmd"            => "historial"
                        )
                     );



if (!$cmd)
          $cmd="pendientes";

$sql="
      select v.id_visitas_casos,v.direccion,v.fecha_visita,substring(v.fecha_visita from 12 for 18) as hora,
             (t.apellido || text (', ')|| t.nombre) as tecnico,cant_modulos,
             c.nrocaso,c.idcaso
             from casos.visitas_casos v
             join casos.casos_cdr c using(idcaso)
             join casos.tecnicos_visitas t using(id_tecnico_visita)

      ";



if ($_POST["mis_visitas"]){
   phpss_svars_set("_ses_global_mis_visitas", "1");
   }
   else
   {
       if ($_POST["form_busqueda"])
           phpss_svars_set("_ses_global_mis_visitas", "0");
   }






if ($cmd=="pendientes")  $estado="Pendiente";
if ($cmd=="historial")   $estado="Historial";
if ($_ses_global_mis_visitas) $mis_visitas=" and t.id_usuario=$id_usuario";






if ($_POST["desde"])
             {
              if ($_POST["hasta"])
                     $desde=$_POST["desde"];
                     $hasta=$_POST["hasta"];
                     $desde_db=fecha_db($desde);
                     $hasta_db=fecha_db($hasta);
                     $desde_hasta=" and (fecha_visita >= '$desde_db' and fecha_visita<='$hasta_db')";
             }
             else
                $msg="Falta elegir una de las fechas";


$where=" v.estado='$estado'  $mis_visitas $desde_hasta";



$orden=array(
	"default" => "2",
	"default_up" => "1",
    "1"=>"v.id_visitas_casos",
    "2"=>"v.fecha_visita",
    "3"=>"hora",
    "4"=>"tecnico",
    "5"=>"c.nrocaso",
    "6"=>"v.direccion",
     );

$filtro=array(
        "v.id_visitas_casos"=>"Id Visita",
        "v.fecha_visita" =>"Fecha",
        //"hora"=>"Hora",
        //"tecnico"=>"Técnico",
        "v.direccion" =>"Dirección",
        "c.nrocaso" =>"Nro Caso"
        );

echo $html_header;
cargar_calendario();
?>
<form name=form1 method=post>
  <table width=100% class=bordes>
     <tr>
       <td align=center>
       <?
       generar_barra_nav($datos_barra);
       ?>
       </td>
     </tr>
     <tr>
     <td align=center>
     <?
     list($sql,$total_reg,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_temp,$where,"buscar");
     $resultado = sql($sql) or fin_pagina();
     ?>
     &nbsp;
     <b>Desde:</b>
     <input type=text name=desde value="<?=$desde?>" size=10>
     <?=link_calendario("desde")?>
     &nbsp;
     <b>Hasta:</b></b>
     <input type=text name=hasta value="<?=$hasta?>" size=10>
     <?=link_calendario("hasta")?>

     &nbsp;
     <input type=submit name=form_busqueda value=Buscar>

     </td>
     </tr>
     <?
     $link=encode_link("asignar_visitas.php",array("pagina"=>"agenda"));
     ?>
     <tr>
        <td align=center>
          <input type=button name=agenda value=Agenda onclick="window.open('<?=$link?>')" >
          <?
          if ($_ses_global_mis_visitas) {
                $checked_mis_visitas="checked";

                }
          ?>
          &nbsp;
          <input type=checkbox name=mis_visitas value="Mis visitas" <?=$checked_mis_visitas?> >
          &nbsp;
          <b>Mis Visitas</b>
          &nbsp;
          <?
          $link=encode_link("listado_visitas_imprimir.php",array("sql"=>$sql));
          ?>
         <input type=button name=imprimir value="Imprimir Listado" onclick="window.open('<?=$link?>')">


        </td>
     </tr>
     <tr>
        <td align=center>
           <table width=100% align=center>
              <tr id=mo>
                  <td align=left colspan=2>Cantidad de Visitas:<?=$total_reg?> </td>
                  <td align=right colspan=4><?=$link_pagina?> </td>
              </tr>

              <tr>
                <td id=mo width=5%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Id</a></td>
                <td id=mo width=10%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Fecha</a></td>
                <td id=mo width=10%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Hora</a></td>
                <td id=mo width=15%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Técnico</a></td>
                <td id=mo width=15%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Nro Caso</a></td>
                <td id=mo><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Dirección</a></td>
              </tr>
            <?
            $cantidad_visitas=$resultado->recordcount();

            for ($i=0;$i<$cantidad_visitas;$i++){

            $fecha=fecha($resultado->fields["fecha_visita"]);
            $hora=substr($resultado->fields["hora"],0,5);

            $cant_modulos=$resultado->fields["cant_modulos"];
            $sum=($cant_modulos) * 30;
            $horas=split(":",$hora);
            $hora_fin=date("H:i",mktime($horas[0],$horas[1]+$sum,'00'));

            if ($cmd=="historial")   $solo_lectura=1;
                               else $solo_lectura=0;
            $id_caso=$resultado->fields["idcaso"];
            $nro_caso=$resultado->fields["nrocaso"];
            $link=encode_link("concretar_visitas.php",array("id_visitas_casos"=>$resultado->fields["id_visitas_casos"],
                                                            "solo_lectura"=>$solo_lectura,
                                                            "pagina"=>"listado",
                                                            "id_caso"=>$id_caso,
                                                            "nro_caso"=>$nro_caso));
            tr_tag($link);
            ?>

              <td><?=$resultado->fields["id_visitas_casos"]?></td>
              <td align=center><?=$fecha?></td>
              <td align=center><?=$hora." -- ".$hora_fin?></td>
              <td>&nbsp;<?=$resultado->fields["tecnico"]?></td>
              <td align=center><?=$resultado->fields["nrocaso"]?></td>
              <td>&nbsp;<?=html_out($resultado->fields["direccion"])?></td>
              </tr>
            <?
            $resultado->movenext();
            }
            ?>
           </table>
        </td>
     </tr>
  </table>
</form>
<?echo fin_pagina()?>