<?PHP
/*
Autor: MAC

$Author: mari $
$Revision: 1.18 $
$Date: 2006/11/22 13:59:50 $
*/
require_once("../../config.php");
variables_form_busqueda("notac");

if ($cmd == "") {
	$cmd="pendientes";
    phpss_svars_set("_ses_notac_cmd", $cmd);
}



$datos_barra = array(
					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "pendientes",
						),
					array(
						"descripcion"	=> "Utilizadas",
						"cmd"			=> "utilizadas"
						),
				    array(
						"descripcion"	=> "Todas",
						"cmd"			=> "todas"
						)
				 );
echo "<br>";
generar_barra_nav($datos_barra); 
echo "<br>";

echo $html_header;


    $orden = array(
		"default" => "1",
        "default_up" => "0",
        "1" => "nota_credito.id_nota_credito",
        "2" => "nota_credito.observaciones",
        "3" => "proveedor.razon_social",
        "4" => "nota_credito.monto",
        "5" => "fecha",
        "6" => "id_info_rma"

        );

    $filtro = array(
        "nota_credito.id_nota_credito" => "Numero nota de credito",
        "nota_credito.monto" => "Monto",
        "proveedor.razon_social"=>"Proveedor",
        "nota_credito.observaciones"=>"Observaciones",
        "fecha"=>"Fecha",
        "id_info_rma"=>"Nº RMA"
    );

    $itemspp = 50;

    $fecha_hoy = date("Y-m-d 23:59:59",mktime());
    echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
    echo "<table cellspacing=2 cellpadding=5 border=0 bgcolor=$bgcolor3 width=100% align=center>\n";
    echo "<tr><td align=center>\n";


$sql_tmp = "SELECT nota_credito.*, moneda.simbolo,moneda.nombre,moneda.id_moneda,proveedor.id_proveedor,proveedor.razon_social,fecha,id_info_rma,
            case when (arch.id_nota_credito is null) then 0 else 1 end as archivo 
            from general.nota_credito JOIN licitaciones.moneda using(id_moneda)
            JOIN general.proveedor Using (id_proveedor)
            left join general.log_nota_credito using (id_nota_credito)
            left join
                (select licitaciones.unir_texto( rma.id_info_rma || text(' ')) as id_info_rma,id_nota_credito
                  from (select id_info_rma,id_nota_credito from stock.info_rma where id_nota_credito is not null ) as rma 
                  group by id_nota_credito) as info_rma  
            using (id_nota_credito) 
            left join (select distinct id_nota_credito from general.arch_notas_credito) as arch using (id_nota_credito)";
$where=" (tipo ilike '%creación' or tipo is null)";
if($cmd=="pendientes")
 $where.="  and (estado=0 or estado=1)";//estado pendiente o reservada
elseif($cmd=="utilizadas")
 $where.=" and estado=2";//estado utilizada

 

 
	list($sql,$total_lic,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where,"buscar");
	$sql_est = "select id_estado,nombre,color from estado";
    $result = sql($sql_est) or die;
    $estados = array();//se guardan los estados que se mostraran en
    $estados[0]['color']="#FF0000";//color rojo
    $estados[0]['texto']="Orden de Compra vencida";
    $estados[1]['color']="#FF8000";//color naranja
    $estados[1]['texto']="Orden de Compra vence en 1 a 2 días";
    $estados[2]['color']="#FFFF80";//color amarillo
    $estados[2]['texto']="Orden de Compra vence en 3 a 5 días";


    echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>\n";
    echo "</td></tr></table><br>\n";
    echo "</form>\n";
    $result = sql($sql) or die;
    echo $parametros['msg'];
    
$color_anulada="#FF6A6C";
if($cmd!="utilizadas")
{?>
 <a name="leyenda">
<table width=95% align="center">
   <tr>
      <td width=40%><b>Se reservó para el pago de alguna Orden de Compra</b> </td>
      <td bgcolor="#FFFFC0" align="left" width="5%">&nbsp;</td>
      <td width="10%"></td>
      <?if ($cmd=='todas'){?>
      <td width="40%"><b>Nota de Crédito Anulada</b></td>
      <td bgcolor="<?=$color_anulada?>" align="left" width="5%">&nbsp;</td>
      <td width="10%"></td>
      <?}
      else { ?>
      <td width="55%"></td>
      <?}
      ?>
   </tr>   
</table>
  
<?    
}
    echo "<table border=0 width=95% cellspacing=2 cellpadding=3 bgcolor=$bgcolor3 align=center>";
    echo "<tr><td colspan=7 align=left id=ma>\n";
    echo "<table width=100%><tr id=ma>\n";
    echo "<td width=30% align=left><b>
    Total:</b> $total_lic Notas de Credito.</td>\n";?>
    <td>
  		<?$link=encode_link("nota_credito_listar_excel.php",array(/*"sql"=>$sql*/));?>
   		<a target=_blank title='Bajar datos en un excel' href='<?=$link?>'>
    	<img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle'></a>
    </td>
	<?echo "<td width=70% align=right>$link_pagina</td>\n";
    echo "</tr></table>\n";
    echo "</td></tr>";
    echo "<tr>";
    echo "<td align=right id=mo title='Numero de la nota de credito'><a id=mo href='".encode_link("nota_credito_listar.php",array("sort"=>"1","up"=>$up))."'>Nro</td>\n";
    echo "<td align=right id=mo title='Fecha creacion de la nota de credito'><a id=mo href='".encode_link("nota_credito_listar.php",array("sort"=>"5","up"=>$up))."'>Fecha Creacion</td>\n";
    echo "<td align=right width=15% id=mo title='Monto de la nota de credito'><a id=mo href='".encode_link("nota_credito_listar.php",array("sort"=>"4","up"=>$up))."'>Monto</td>\n";
    echo "<td align=right width=30% id=mo><a id=mo href='".encode_link("nota_credito_listar.php",array("sort"=>"3","up"=>$up))."'>Proveedor</td>\n";
    echo "<td align=right width=40% id=mo><a id=mo href='".encode_link("nota_credito_listar.php",array("sort"=>"2","up"=>$up))."'>Observaciones</td>\n";
    echo "<td align=right width=15% id=mo><a id=mo href='".encode_link("nota_credito_listar.php",array("sort"=>"6","up"=>$up))."'>Nº RMA</td>\n";
    echo "<td align=right width=15% id=mo><a id=mo href='".encode_link("nota_credito_listar.php",array())."'>Archivo?</td>\n";

    echo "</tr>\n";

    while (!$result->EOF) {

        $ref = encode_link("nota_credito.php",array("pagina"=>"nota_credito_listar","id_nota_credito"=>$result->fields["id_nota_credito"]));
        tr_tag($ref);
     //  echo "<td align=center ".(($result->fields["estado"]==1)?"bgcolor='#FFFFC0' title='Nota Reservada'":""). ">".$result->fields["id_nota_credito"]."</a></td>\n";
        
        if ($result->fields["estado"]==3)  { $anulado= "bgcolor='$color_anulada' title='Nota Anulada'";}
          else $anulado="";    
        ?>
        <td align=center <? if ($result->fields["estado"]==1) {
          	                echo "bgcolor='#FFFFC0' title='Nota Reservada'";} 
          	                elseif ($result->fields["estado"]==3)  echo $anulado?>
        ><?=$result->fields["id_nota_credito"]?></a></td>
        <?         
        echo "<td align=left $anulado";
        echo ">".fecha($result->fields["fecha"])."</td>\n";
        echo "<td align=center $anulado";
        echo "title='Monto de la nota de credito'><table width=100%><tr><td>".$result->fields["simbolo"]."</td><td align='right'> ".formato_money($result->fields["monto"])."</td></tr></table></td>\n";
        echo "<td align=left $anulado>";
        echo "&nbsp;".html_out($result->fields["razon_social"])."</td>\n";
        echo "<td align=left $anulado";
        echo ">".$result->fields["observaciones"]."</td>\n";
        if ($result->fields['id_info_rma']) 
           echo "<td>".$result->fields["id_info_rma"]."</td>\n";
        else echo "<td>  </td>\n"; 
        echo "<td align='center'>";
                if ($result->fields["archivo"]==1) {?><img align=middle src=<?=$html_root?>/imagenes/zip.gif border=0> <?} 
                    else echo "&nbsp;";
        echo "</td>\n";
        $result->MoveNext();
    }
    echo "</table><br>";
fin_pagina();
?>