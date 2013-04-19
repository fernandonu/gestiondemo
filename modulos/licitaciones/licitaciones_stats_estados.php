<?php
/*
$Author: fernando $
$Revision: 1.2 $
$Date: 2004/07/21 22:43:16 $
*/
require_once("../../config.php");
echo $html_header;
cargar_calendario();

if ($_POST["buscar"]=="Buscar"){

      if ($_POST["distritos"]!=-1){
       $join="join entidad using(id_entidad) join distrito using(id_distrito)";
       $where=" and (id_distrito=".$_POST["distritos"].")";
      }
      if (($_POST["fecha_desde"])&&($_POST["fecha_hasta"])){
       $fecha_desde=fecha_db($_POST["fecha_desde"]);
       $fecha_hasta=fecha_db($_POST["fecha_hasta"]);

       $where.=" and (fecha_apertura>='$fecha_desde' AND fecha_apertura<='$fecha_hasta 23:59:59')";
      }


      $sql="select count(id_estado) as cantidad,estado.nombre from licitacion
            join estado using (id_estado) $join
            where es_presupuesto=0 and borrada='f' and (id_estado=1  or id_estado=5 or id_estado=6 or id_estado=8)
            $where
            group by estado.nombre order by cantidad DESC";
       $res_consulta=$db->execute($sql) or die($sql."<br>".$db->errormsg());
       // echo $sql."<br>";
       $terminadas=array();
       $total_terminadas=0;
       for($i=0;$i<$res_consulta->recordcount();$i++)
       {
        $terminadas[$res_consulta->fields["nombre"]]=$res_consulta->fields["cantidad"];
        $total_terminadas+=$res_consulta->fields["cantidad"];
        $res_consulta->movenext();

       }
      $sql="select count(id_estado) as cantidad,estado.nombre from licitacion
            join estado using (id_estado) $join
            where es_presupuesto=0 and borrada='f' and (id_estado=0  or id_estado=2 or id_estado=3 or id_estado=7)
            $where
            group by estado.nombre order by cantidad DESC";
       $res_consulta=$db->execute($sql) or die($sql."<br>".$db->errormsg());
       //echo "<br>$sql<br>";
       $presentadas=array();
       $total_presentadas=0;
       for($i=0;$i<$res_consulta->recordcount();$i++)
       {
        $presentadas[$res_consulta->fields["nombre"]]=$res_consulta->fields["cantidad"];
        $total_presentadas+=$res_consulta->fields["cantidad"];
        $res_consulta->movenext();

       }

}

?>
<form name=form1 method=post>
<table width=100% border=1 cellspacing=1 cellpadding=2 bgcolor=<?=$bgcolor2?> align=center>
   <tr>
     <td id="mo" colspan=2>
     Estadisticas de las licitaciones
     </td>
   </tr>
   <tr>
     <td width=10%><b>Fecha:</b></td>
     <td valign=top width=90%>
        <table width=100% align=center>
           <tr>
            <td align=center>
             <table width=100% align=center>
             <tr>
               <td>
                  <b>Desde</b>
               </td>
               <td>
               <input type=text name="fecha_desde" size=10 value="<?=$_POST["fecha_desde"];?>">
               <?=link_calendario("fecha_desde")?>
               </td>
               <td>
               <b>Hasta</b>
               </td>
               <td>
               <input type=text name="fecha_hasta" size=10 value="<?=$_POST["fecha_hasta"];?>">
               <?=link_calendario("fecha_hasta")?>
               </td>
              </tr>
             </table>
            </td>
           </tr>
        </table>
     </td>
   </tr>
   <td><b>Distrito:</b></td>
   <?
   $sql="select * from distrito order by nombre ASC";
   $distrito=$db->execute($sql) or die($sql."<br>".$db->errormsg());
   ?>
   <td align=center>
   <select name=distritos onKeypress= "buscar_op(this)" onblur="borrar_buffer()" onclick= "borrar_buffer()" style="width:40%">
   <option value=-1>Todos</option>
   <?
   for($i=0;$i<$distrito->recordcount();$i++)
   {
    $id_distrito=$distrito->fields["id_distrito"];
    $nombre_distrito=$distrito->fields["nombre"];
   ?>
   <option value="<?=$id_distrito?>" <? if($id_distrito==$_POST["distritos"]) echo "selected"; ?>><?=$nombre_distrito?></option>
   <?
   $distrito->movenext();
   }
   ?>
   </select>
   </td>
   </tr>
   <tr>
     <td colspan=2 align=center>
     <input type=submit name=buscar value="Buscar">
     &nbsp;
     <input type=reset name=cancelar value="Cancelar">
     </td>
   </tr>
<?
if ($res_consulta){
?>
    <tr id=ma_sf>
      <td colspan=2 >
      Resultados  de la búsqueda:
      </td>
    </tr>
    <tr>
       <td colspan=2 valig=top>
        <table align=left width=100%>
          <tr>
           <td id=ma width=20%>
           Terminadas<br>
           (<?=$total_terminadas?>)
           </td>
           <td>
             <table width=100% align=center border=1 cellspacing=0 cellpadding=0  bordercolor=<?=$bgcolor5?>>
               <tr id=mo>
                 <td width=33% align=center><b>Estado</b></td>
                 <td width=33% align=center> <b>Cantidad</b></td>
                 <td width=34% align=center><b>%</b></td>
               </tr>
               <?

               foreach ($terminadas as $key => $value){
                $data_terminadas[]=$value;
                $leyenda_terminadas[]=$key;
               ?>
               <tr>
                <td><b><?=$key?></td>
                <td align=center><?=$value?></td>
                <td align=center><?=number_format(($value*100)/$total_terminadas,2,".","");?></td>
               </tr>
               <?
               }
               ?>
             </table>
           </td>
          </tr>
          <tr><td colspan=2>&nbsp;</td></tr>
          <tr>
            <td id=ma >
            Presentadas<br>
            (<?=$total_presentadas?>)
            </td>
            <td>
             <table width=100% align=center border=1 cellspacing=0 cellpadding=0  bordercolor=<?=$bgcolor5?>>
               <tr id=mo>
                 <td width=33% align=center><b>Estado</b></td>
                 <td width=33% align=center> <b>Cantidad</b></td>
                 <td width=34% align=center><b>%</b></td>
               </tr>

               <?

               foreach ($presentadas as $key => $value){
                Switch ($key) {
                case "Presuntamente ganada":
                      $key="P. Ganadas";
                      break;
                case "Orden de compra":
                     $key="O. de Compra";
                     break;
                }
                 $leyenda_presentadas[]=$key;
                 $data_presentadas[]=$value;
                ?>
               <tr>
                <td><b><?=$key?></td>
                <td align=center><?=$value?></td>
                <td align=center><?=number_format(($value*100)/$total_presentadas,2,".","");?></td>
               </tr>
               <?
               }
               ?>
             </table>

            </td>
          </tr>
        </table>
       </td>
    </tr>
   <tr>
   <td  align=center  colspan=2>
   <table width=100% align=Center>
   <tr>
   <td width=50% align=Center>
    <?
    if($total_terminadas>0)
    {
    $link_s=encode_link("lic_graficas.php",array("data"=>$data_terminadas,"leyenda"=>$leyenda_terminadas,"titulo"=>"Licitaciones Terminadas","tamaño"=>"small"));
    $link_l=encode_link("lic_graficas.php",array("data"=>$data_terminadas,"leyenda"=>$leyenda_terminadas,"titulo"=>"Licitaciones Terminadas","tamaño"=>"large"));
    echo "<a href='$link_l' target='_blank'><img src='$link_s'  border=0 align=top></a>\n";
    }
    ?>
    </td>
   <td width=50% align=center>
   <? if ($total_presentadas>0)
      {
      $link_s=encode_link("lic_graficas.php",array("data"=>$data_presentadas,"leyenda"=>$leyenda_presentadas,"titulo"=>"Licitaciones Presentadas","tamaño"=>"small"));
      $link_l=encode_link("lic_graficas.php",array("data"=>$data_presentadas,"leyenda"=>$leyenda_presentadas,"titulo"=>"Licitaciones Presentadas","tamaño"=>"large"));
      echo "<a href='$link_l' target='_blank'><img src='$link_s'  border=0 align=top></a>\n";
      }
    ?>
    </td>
    </tr>
    </table>
</td>
</tr>
<?
}
?>
</table>
</form>
<?
echo $TTF_DIR;
?>