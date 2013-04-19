<?
/*
$Author: fernando $
$Revision: 1.1 $
$Date: 2005/10/17 22:51:58 $
*/

require_once("../../config.php");
variables_form_busqueda("bancos_transferencias");
echo $html_header;





if ($_POST["en_proceso"]){
    
    
    
}


$datos_barra=array(
                array("descripcion"=>"Pendientes",
                      "cmd"=>"pendientes"
                     ),
                array(
                      "descripcion"=>"En Proceso",
                      "cmd"=>"en_proceso",
                     ),
                array(
                      "descripcion"=>"Historial",
                      "cmd"=>"historial",
                     ),
                     
                 );


$sql="
   select * from ( 
           select t.id_transferencias,t.monto,t.observaciones, t.id_estado_transferencias,bo.nombrebanco as nbre_banco_origen, bd.nombrebanco as nbre_banco_destino from
                    bancos.transferencias t
                    join (
                          select nombrebanco,idbanco from  bancos.tipo_banco banco where activo=1
                        ) as bo 
                        on t.banco_origen=bo.idbanco
                    join (
                          select nombrebanco,idbanco from  bancos.tipo_banco banco where activo=1
                        ) as bd 
                        on t.banco_destino=bd.idbanco

   ) as datos
            
";                 
if (!$cmd) $cmd="pendientes"; 

if ($cmd=="pendientes")  $where_tmp=" id_estado_transferencias=1";
if ($cmd=="en_proceso")  $where_tmp=" id_estado_transferencias=2";
if ($cmd=="historial")   $where_tmp=" id_estado_transferencias=3";


                 
$orden=array(
       "default_up"=> 1,
       "1" => "nbre_banco_origen",
       "2" => "nbre_banco_destino",
       "3" => "t.monto",
       ); 
       
$filtro=array(
    "nbre_banco_origen"=>"Banco Origen",
    "nbre_banco_destino" => "Banco Destino",
    "t.monto" => "Monto"
);                       
 


?>
<form name=form1 method=post>
<br>
<?
generar_barra_nav($datos_barra);
?>
<br>
<table width=95% align=center class=bordes>
  <tr>  
    <td align=left>
    <input type=button name=nuevo value="Nueva Transferencia" onclick="window.open('bancos_nueva_transferencia.php')">
    </td>
  </tr>

  <tr>
    <td align=center>
     <?
     list($sql_temp,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
     $result = sql($sql_temp,"error en busqueda") or fin_pagina();
     ?>
     <input type=submit name=form_busqueda value='Buscar'> 
    </td>
  </tr>
  <tr>
     <td>
         <table width=100% align=center>
             <tr id=mo>
                <td width=1%>&nbsp;</td> 
                <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Banco Origen</a></td>
                <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Banco Destino</a></td>
                <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Monto</a></td>
             </tr>
             <?
             for($i=0;$i<$result->recordcount();$i++){
             $id_transferencias =  $result->fields["id_transferencias"];
             $link = encode_link("bancos_nueva_transferencia.php",array("id_transferencias"=>$id_transferencias,"pagina"=>"listado","cmd"=>$cmd));    
             $title = $result->fields["observaciones"];
             tr_tag($link,"title='$title'") ;   
             ?>
             <td><input type=checkbox name=id_transferencias[] value="<?=$id_transferencias?>"></td>
             <td><?=$result->fields["nbre_banco_origen"]?></td>
             <td><?=$result->fields["nbre_banco_destino"]?></td>
             <td align=right><?=formato_money($result->fields["monto"])?></td>
             </tr>
             <?
             $result->movenext();
             }
             ?>
         </table>
     </td>
  </tr>
  <tr>
     <td>
        <table width=60% align=center>
           <tr>
              <td align=center><input type=submit name=en_proceso value="En Proceso" style="width=100" disabled></td>
              <td align=center><input type=submit name=historial value="Historial" style="width=100" disabled></td>
           </tr>
        </table>
     </td>
  </tr>
</table>
</form>
<?
echo fin_pagina();
?>