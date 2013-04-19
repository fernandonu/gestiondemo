<?
/*
$Author: fernando $
$Revision: 1.1 $
$Date: 2004/07/22 22:17:16 $
*/
require_once("../../config.php");

$terminadas=$parametros["terminadas"];
$total_terminadas=$parametros["total_terminadas"];
$presentadas=$parametros["presentadas"];
$total_presentadas=$parametros["total_presentadas"];
$fecha_desde=$parametros["fecha_desde"];
$fecha_hasta=$parametros["fecha_hasta"];
$id_distrito=$parametros["id_distrito"];

//echo $html_header;
excel_header("licitaciones.xls");
?>
<table width=100% border=1 cellspacing=1 cellpadding=2 align=center>
   <tr>
     <td  colspan=2 <?=excel_style("texto")?> align=center >
     <b>Estadísticas de las licitaciones</b>
     </td>
   </tr>
    <tr >
      <td colspan=2 <?=excel_style("texto")?> >
      <b>Resultados  de la búsqueda:</b>
      </td>
    </tr>
    <tr>
      <td colspan=2 <?=excel_style("texto")?>>
      <b>Fecha:</b>
      </td>
    </tr>
    <tr>
      <td colspan=2 <?=excel_style("texto")?>>
      <b>Desde:&nbsp;<?=$fecha_desde?>&nbsp;&nbsp;&nbsp;&nbsp;   Hasta:&nbsp;<?=$fecha_hasta?></b>
      </td>
    </tr>
    <?
    $sql="select nombre from distrito where id_distrito=$id_distrito";
    $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
    $distrito=$resultado->fields["nombre"];
    ?>
    <tr>
       <td colspan=2 <?=excel_style("texto")?>>
       <b>Distrito:&nbsp<?=$distrito?></b>
       </td>
    </tr>

    <tr>
       <td colspan=2 valig=top>
        <table align=left width=100%>
          <tr>

            <td width=20%  <?=excel_style("texto")?> align=center>
           <b>Terminadas<br>
           (<?=$total_terminadas?>)
           </b>
           </td>
           <td>
             <table width=100% align=center border=1 cellspacing=0 cellpadding=0  bordercolor=<?=$bgcolor5?>>
               <tr >
                 <td width=33% align=center <?=excel_style("texto")?> > <b>Estado</b></td>
                 <td width=33% align=center <?=excel_style("texto")?> > <b>Cantidad</b></td>
                 <td width=34% align=center <?=excel_style("texto")?> ><b>%</b></td>
               </tr>
               <?

               foreach ($terminadas as $key => $value){

                $porcentaje=number_format(($value*100)/$total_terminadas,2,".","");
                $data_terminadas[]=$porcentaje;
                $leyenda_terminadas[]=$key;
               ?>
               <tr>
                <td <?=excel_style("texto")?> ><b><?=$key?></td>
                <td align=center <?=excel_style("texto")?> ><?=$value?></td>
                <td align=center <?=excel_style("texto")?> ><?=$porcentaje;?></td>
               </tr>
               <?
               }
               ?>
             </table>
           </td>
          </tr>
          <tr><td colspan=2>&nbsp;</td></tr>
          <tr>
            <td  <?=excel_style("texto")?> align=center>
            <b>
            Presentadas<br>
            (<?=$total_presentadas?>)
            </b>
            </td>
            <td>
             <table width=100% align=center border=1 cellspacing=0 cellpadding=0  bordercolor=<?=$bgcolor5?>>
               <tr >
                 <td width=33% <?=excel_style("texto")?>  align=center><b>Estado</b></td>
                 <td width=33% <?=excel_style("texto")?>  align=center> <b>Cantidad</b></td>
                 <td width=34% <?=excel_style("texto")?>  align=center><b>%</b></td>
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
                 $porcentaje=number_format(($value*100)/$total_presentadas,2,".","");
                 $leyenda_presentadas[]=$key;
                 $data_presentadas[]=$porcentaje;
                ?>
               <tr>
                <td <?=excel_style("texto")?> ><b><?=$key?></td>
                <td <?=excel_style("texto")?>  align=center><?=$value?></td>
                <td <?=excel_style("texto")?>  align=center><?=$porcentaje;?></td>
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

</table>