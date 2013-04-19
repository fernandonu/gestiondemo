<?php
/* quique
$Author: marco_canderle $
$Revision: 1.19 $
$Date: 2005/12/06 18:45:22 $
*/

require_once("../../config.php");
require_once ("funciones.php");
require_once("../../lib/fns.gacz.php");
//print_r($_POST);
echo $html_header;
?>
<style type="text/css">
.separador{border-bottom-width:2px;border-bottom-style:solid;border-bottom-color:black}
.feriado{border-width:1px;border-style:solid;border-color:red;border-top:none;border-bottom:none}
.actual{border-width:1px;border-style:solid;border-color:black;border-top:none;border-bottom:none}
input.achicar {

	background-color: #F5F5F5;

	font-family: Verdana, sans-serif, helvetica;

	font-size: 8pt;

	border-style: outset;

	border-color: #D5D5D5;

	border-width: 2;

}
</style>
<?
$comen=1;
$tamaño=10;
$come="comentario_1";
cargar_calendario();
if($_POST["Actualizar"]=="Actualizar Fecha")
{
	$tamaño=$_POST["tamaño"];
	$pagina_ant=$_POST["pagina"];
	$des=$_POST["Entre_Fechas_Desde"];
	$has=$_POST["Entre_Fechas_Hasta"];
	$desd=Fecha_db($des);
	$hast=Fecha_db($has);
	list($anio,$mes,$dia)=explode("-",$desd);
	$desd=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));
	list($anio,$mes,$dia)=explode("-",$hast);
	$hast=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));
	if(compara_fechas($hast,$desd)==-1)
	{
	$pagina_ant=0;
	$comentar="Error al ingresar las fechas";	
	}
	?>
	<script>
	document.styleSheets[0].addRule("td", "font-size:"+<?=$tamaño?>);
    document.styleSheets[0].addRule("a", "font-size:"+<?=$tamaño?>);
    document.styleSheets[1].addRule("textarea", "font-size:"+<?=$tamaño?>);
    document.styleSheets[0].addRule("select", "font-size:"+<?=$tamaño?>);
    //alert((parseInt(f.replace("px"," "))+w)+'px');
	</script>
	<?
}

if($_POST["ordenar"]=="Ordenar")
{
	$tamaño=$_POST["tamaño"];
	$ordenar_listado=1;
	
	$tamaño=$_POST["tamaño"];
	$pagina_ant=$_POST["pagina"];
	$des=$_POST["Entre_Fechas_Desde"];
	$has=$_POST["Entre_Fechas_Hasta"];
	$cant_guardar=$_POST["cant_guardar"];
	$contador1=0;
	$j=1;
	
	while($j<=$cant_guardar)
	{   
		$comen=1;
		$idl="idlic_".$j;
		$id_L=$_POST["$idl"];	
		
			$id="id_".$j;
			$inic="inicio_".$j;
			$final="final_".$j;
			$id_sub=$_POST["$id"];	
			$inic=$_POST["$inic"];	
			$final=$_POST["$final"];	
			$post=$id_sub;	
			$valor=$_POST["inter_".$post];
			
			$id="id_".$j;
			$codigo=$_POST["$id"];	
			$come="comentario_".$j;
			$comentar11=$_POST["$come"];	
			$up_comen="update licitaciones.subido_lic_oc set comentarios_lic='$comentar11' where id_subir=$codigo";
			sql($up_comen,"No se pudo actualizar el comentario")or fin_pagina();
			
			$priori="select id_subir from licitaciones.subido_lic_oc where prioridad=$valor";
			$prioridad2=sql($priori,"Error al traer la prioridad") or fin_pagina();
			$priori1="select prioridad from licitaciones.subido_lic_oc where id_subir=$post";
			$prioridad1=sql($priori1,"Error al traer la prioridad") or fin_pagina();		
			$pos1=$prioridad1->fields['prioridad'];
			$id_s=$prioridad2->fields['id_subir'];
			$update_p="update licitaciones.subido_lic_oc set prioridad=$valor where id_subir=$post";
			$update_p1=sql($update_p,"No se pudo actualizar la prioridad1")or fin_pagina();	
			$updat="update licitaciones.subido_lic_oc set prioridad=$pos1 where id_subir=$id_s";
			$update_p2=sql($updat,"No se pudo actualizar la prioridad2")or fin_pagina();		
			$a=0;
			$diferencia=diferencia_dias($des,$has);
			$desde1=$_POST["Entre_Fechas_Desde"];
			$hasta1=$_POST["Entre_Fechas_Hasta"];		
			$desde1=Fecha_db($desde1);
			$desde2=$desde1;
			$desde3=$desde1;
		    $hasta1=Fecha_db($hasta1);
		    list($anio_1,$mes_1,$dia_1)=explode("-",$desde1);
		    $desde1=date("Y_m_d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));	    
			$verd=0;
			$verde=0;
			$roj=0;
			$rojo=0;
			$cele=0;
			$celeste=0;
			$transparente=0;
			$otro=1;
			$inicio_com="null";
			$fin_compra="null";
			$inicio_cd="null";
			$fin_cd="null";
			$inicio_entrega="null";
			$fin_entre="null";
			$comillas=0;
			$v=0;
			$c=0;
			$r=0;
		while($a<$diferencia)
		{
			?>
		    	<script>
		    	
		    	//alert('<?=$v?>');
		    	</script>
		    	
		    	
		    	<?
			list($anio3,$mes3,$dia3)=explode("-",$desde3);
			$fer=Fecha($desde3);
			$dia_letras=date("D",mktime(0,0,0,$mes3,$dia3,$anio3));			
			/*echo"$fer";
			die();*/
			$var='d_'.$j.'_'.$desde1;
		    $dia_actual=$_POST["$var"];
		    if($dia_actual=="#00C823")
		    {
			    if(($celeste==0)&&($rojo==0)&&($transparente==0))
			    {
			    if($verd==0)
			    {
			    $comillas=1;	
			    $verd=1;	
			    $verde=1;	
			    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);
		        $inicio_com=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1,$anio_1));		    	
			    }
			    }
			    else 
			    {	
			    $contador1++;
			    $error1[$contador1]=$id_L;
			    $comen=0;
			    break;	
		    	}
		    }
		    
		    if($dia_actual=="#00FFFF")
		    {
			    if(($rojo==0)&&($transparente==0))
			    {	
			    if($cele==0)
			    {   
			    if(($verde==1)&&($v==0))
			    {
			    $v=1;	
			    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);
			    $fin_compra=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));	
			    }
			    	$comillas=1;
				    $cele=1;
				    $celeste=1;	
				    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);
			        $inicio_cd=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1,$anio_1));	
			    }
			    }
			    else 
			    {
			    	$contador1++;
				    $error1[$contador1]=$id_L;
				    $comen=0;
				    break;	
			    }
		    }		    
		    if($dia_actual=="#FF00FF")
		    {
			    if($transparente==0)
			    {
			    if($roj==0)
			    {
			    if(($verde==1)&&($cele==0)&&($v==0))
			    {
			    $v=1;	
			    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
			    $fin_compra=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));
			    //$inicio_cd=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1,$anio_1));		
			    }
			    if(($cele==1)&&($c==0))
			    {
			    $c=1;	
			    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
			    $fin_cd=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));		
			    }	
			    $comillas=1;	
			    $roj=1;
			    $rojo=1;
			    $otro=0;
			    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
			    $inicio_entrega=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1,$anio_1));	
			    }
			    }
			    else 
			    {   
			    	$contador1++;
				    $error1[$contador1]=$id_L;
				    $comen=0;
				    break;	
			    }		    
		    }
		    if(($dia_letras=='Sun')||(feriado($fer))||($dia_letras=='Sat'))
		    {
		    	//echo"$desde3";
		    	
		    	$si_esta="select * from licitaciones.fecha_color where id_subir=$id_sub and fecha='$desde3'";
				$si=sql($si_esta,"Error al traer los datos de la tabla color") or fin_pagina();
				if(($si->RecordCount()==0)&&($dia_actual!=""))
				{
					$campos="(fecha,id_subir,color)";	
					$ingresar="insert into fecha_color $campos VALUES ".
		            "('$desde3',$id_sub,'$dia_actual')";
		            sql($ingresar,"no se pudo guardar los datos en la tabla color")or fin_pagina();	
				}
				else 
				{
				$update_color="update licitaciones.fecha_color set color='$dia_actual' where id_subir=$id_sub and fecha='$desde3'";
		        sql($update_color,"No se pudo actualizar la tabla color")or fin_pagina();		
				}
				
		    	
		    }
		    else 
		    {
		    if($dia_actual=="")
		    {
		    	
			    /*if(($rojo==1)||($verde==1)||($celeste==1))
			    {*/
			   // $transparente=1;
				    if(($verd==1)&&($roj==0)&&($cele==0))
				    {
				    	if($v==0)
				         {
					    	$v=1;
				         	list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
					    	$fin_compra=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));	
				         }
				    }
				    if(($cele==1)&&($roj==0))
				    {	
				    	if($c==0)
				         {
				       
					    	$c=1;		     
					    	list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
					    	$fin_cd=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));
					    	
				         }	
				         
				    }
				   if(($r==0)&&($roj==1))
				    {	
				    	if($r==0)
				         {
					    	$r=1;		     
					    	list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
					    	$fin_entre=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));
				         }	
				         
				    }
				  
			   // }
		    		    
		    }
		    } 
		    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);
		    list($anio3,$mes3,$dia3)=explode("-",$desde3);
			$desde1=date("Y_m_d",mktime(0,0,0,$mes_1,$dia_1+1,$anio_1));
			$desde3=date("Y-m-d",mktime(0,0,0,$mes3,$dia3+1,$anio3));
			$a++;
			/*if($roj==1)
			{
				$var='d_'.$j.'_'.$desde1;
			    $dia_actual=$_POST["$var"];
			    if($dia_actual!="#FF00FF")
			    {
				    if($otro==0)
				    {
					   	$otro=1;
					    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
					    $fin_entre=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));	
				    }
			    }
			    if(($a==$diferencia)&&($otro==0))
			    {
				    $otro=1;
				    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
				    $fin_entre=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1,$anio_1));			
			    }    
			    	
			}*/
		}
		//echo"$comen";
		
		if($comen==1)
		{
		
		/*echo"ini_compra='$inicio_com',fin_compra='$fin_compra',ini_cdr='$inicio_cd',fin_cdr='$fin_cd',ini_entrega='$inicio_entrega',fin_entrega='$fin_entre'";
		die();*/
		//echo"hasta $hasta1 final $final desde2 $desde2 inicio $inic";
		/*if((compara_fechas($hasta1,$final)!=-1)&&(compara_fechas($inic,$desde2)!=-1))
		{*/	
			
			if(($rojo==0)&&($verde==0)&&($celeste==0))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra=$inicio_com,fin_compra=$fin_compra,ini_cdr=$inicio_cd,fin_cdr=$fin_cd,ini_entrega=$inicio_entrega,fin_entrega=$fin_entre,guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();	
			}
			
			if(($rojo==1)&&($verde==1)&&($celeste==1))
			{
			//echo "if idlic $id_L com $inicio_com fcom $fin_compra --icdr $inicio_cd--fcdr $fin_cd --inent $inicio_entrega--finentrega $fin_entre a $a";		
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra='$inicio_com',fin_compra='$fin_compra',ini_cdr='$inicio_cd',fin_cdr='$fin_cd',ini_entrega='$inicio_entrega',fin_entrega='$fin_entre',guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
			
			if(($rojo==1)&&($verde==0)&&($celeste==0))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra=$inicio_com,fin_compra=$fin_compra,ini_cdr=$inicio_cd,fin_cdr=$fin_cd,ini_entrega='$inicio_entrega',fin_entrega='$fin_entre',guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
			
			if(($rojo==1)&&($verde==1)&&($celeste==0))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra='$inicio_com',fin_compra='$fin_compra',ini_cdr=$inicio_cd,fin_cdr=$fin_cd,ini_entrega='$inicio_entrega',fin_entrega='$fin_entre',guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
			
			if(($rojo==1)&&($verde==0)&&($celeste==1))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra=$inicio_com,fin_compra=$fin_compra,ini_cdr='$inicio_cd',fin_cdr='$fin_cd',ini_entrega='$inicio_entrega',fin_entrega='$fin_entre',guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
			
			if(($rojo==0)&&($verde==1)&&($celeste==0))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra='$inicio_com',fin_compra='$fin_compra',ini_cdr=$inicio_cd,fin_cdr=$fin_cd,ini_entrega=$inicio_entrega,fin_entrega=$fin_entre,guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
			
			if(($rojo==0)&&($verde==1)&&($celeste==1))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra='$inicio_com',fin_compra='$fin_compra',ini_cdr='$inicio_cd',fin_cdr='$fin_cd',ini_entrega=$inicio_entrega,fin_entrega=$fin_entre,guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
			
			if(($rojo==0)&&($verde==0)&&($celeste==1))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra=$inicio_com,fin_compra=$fin_compra,ini_cdr='$inicio_cd',fin_cdr='$fin_cd',ini_entrega=$inicio_entrega,fin_entrega=$fin_entre,guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
		//}	
		}
	  
	  
	 	  $j++;
	}
	
	
	
	
	
	?>
	<script>
	document.styleSheets[0].addRule("td", "font-size:"+<?=$tamaño?>);
    document.styleSheets[0].addRule("a", "font-size:"+<?=$tamaño?>);
    document.styleSheets[1].addRule("textarea", "font-size:"+<?=$tamaño?>);
    document.styleSheets[0].addRule("select", "font-size:"+<?=$tamaño?>);
    //alert((parseInt(f.replace("px"," "))+w)+'px');
	</script>
	<?
	
}

if($_POST["comentarios"]=="Guardar Comentarios")
{
	$pagina_ant=$_POST["pagina"];
	$des=$_POST["Entre_Fechas_Desde"];
	$has=$_POST["Entre_Fechas_Hasta"];
	$desd=Fecha_db($des);
	$hast=Fecha_db($has);
	list($anio,$mes,$dia)=explode("-",$desd);
	$desd=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));
	list($anio,$mes,$dia)=explode("-",$hast);
	$hast=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));
	$s=1;
	$cant_guardar=$_POST["cant_guardar"];
	while($s<=$cant_guardar)
	{  
	if ($_POST['chequeado_'.$s])
		{
			
		$id="id_".$s;
		$codigo=$_POST["$id"];	
		$come="comentario_".$s;
		$comentar11=$_POST["$come"];	
		$up_comen="update licitaciones.subido_lic_oc set comentarios_lic='$comentar11' where id_subir=$codigo";
		sql($up_comen,"No se pudo actualizar el comentario")or fin_pagina();
		}
		$s++;
	}
}

if($_POST["guardar"]=="Guardar")
{	
	$tamaño=$_POST["tamaño"];
	$pagina_ant=$_POST["pagina"];
	$des=$_POST["Entre_Fechas_Desde"];
	$has=$_POST["Entre_Fechas_Hasta"];
	$cant_guardar=$_POST["cant_guardar"];
	$contador1=0;
	$j=1;
	
	while($j<=$cant_guardar)
	{   $comen=1;
		$idl="idlic_".$j;
		$id_L=$_POST["$idl"];	
		if ($_POST['chequeado_'.$j])
		{
			$id="id_".$j;
			$inic="inicio_".$j;
			$final="final_".$j;
			$id_sub=$_POST["$id"];	
			$inic=$_POST["$inic"];	
			$final=$_POST["$final"];	
			$post=$id_sub;	
			$valor=$_POST["inter_".$post];
			
			$id="id_".$j;
			$codigo=$_POST["$id"];	
			$come="comentario_".$j;
			$comentar11=$_POST["$come"];	
			$up_comen="update licitaciones.subido_lic_oc set comentarios_lic='$comentar11' where id_subir=$codigo";
			sql($up_comen,"No se pudo actualizar el comentario")or fin_pagina();
			
			$priori="select id_subir from licitaciones.subido_lic_oc where prioridad=$valor";
			$prioridad2=sql($priori,"Error al traer la prioridad") or fin_pagina();
			$priori1="select prioridad from licitaciones.subido_lic_oc where id_subir=$post";
			$prioridad1=sql($priori1,"Error al traer la prioridad") or fin_pagina();		
			$pos1=$prioridad1->fields['prioridad'];
			$id_s=$prioridad2->fields['id_subir'];
			$update_p="update licitaciones.subido_lic_oc set prioridad=$valor where id_subir=$post";
			$update_p1=sql($update_p,"No se pudo actualizar la prioridad1")or fin_pagina();	
			$updat="update licitaciones.subido_lic_oc set prioridad=$pos1 where id_subir=$id_s";
			$update_p2=sql($updat,"No se pudo actualizar la prioridad2")or fin_pagina();		
			$a=0;
			$diferencia=diferencia_dias($des,$has);
			$desde1=$_POST["Entre_Fechas_Desde"];
			$hasta1=$_POST["Entre_Fechas_Hasta"];		
			$desde1=Fecha_db($desde1);
			$desde2=$desde1;
			$desde3=$desde1;
		    $hasta1=Fecha_db($hasta1);
		    list($anio_1,$mes_1,$dia_1)=explode("-",$desde1);
		    $desde1=date("Y_m_d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));	    
			$verd=0;
			$verde=0;
			$roj=0;
			$rojo=0;
			$cele=0;
			$celeste=0;
			$transparente=0;
			$otro=1;
			$inicio_com="null";
			$fin_compra="null";
			$inicio_cd="null";
			$fin_cd="null";
			$inicio_entrega="null";
			$fin_entre="null";
			$comillas=0;
			$v=0;
			$c=0;
			$r=0;
		while($a<$diferencia)
		{
			?>
		    	<script>
		    	
		    	//alert('<?=$v?>');
		    	</script>
		    	
		    	
		    	<?
			list($anio3,$mes3,$dia3)=explode("-",$desde3);
			$fer=Fecha($desde3);
			$dia_letras=date("D",mktime(0,0,0,$mes3,$dia3,$anio3));			
			/*echo"$fer";
			die();*/
			$var='d_'.$j.'_'.$desde1;
		    $dia_actual=$_POST["$var"];
		    if($dia_actual=="#00C823")
		    {
			    if(($celeste==0)&&($rojo==0)&&($transparente==0))
			    {
			    if($verd==0)
			    {
			    $comillas=1;	
			    $verd=1;	
			    $verde=1;	
			    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);
		        $inicio_com=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1,$anio_1));		    	
			    }
			    }
			    else 
			    {	
			    $contador1++;
			    $error1[$contador1]=$id_L;
			    $comen=0;
			    break;	
		    	}
		    }
		    
		    if($dia_actual=="#00FFFF")
		    {
			    if(($rojo==0)&&($transparente==0))
			    {	
			    if($cele==0)
			    {   
			    if(($verde==1)&&($v==0))
			    {
			    $v=1;	
			    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);
			    $fin_compra=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));	
			    }
			    	$comillas=1;
				    $cele=1;
				    $celeste=1;	
				    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);
			        $inicio_cd=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1,$anio_1));	
			    }
			    }
			    else 
			    {
			    	$contador1++;
				    $error1[$contador1]=$id_L;
				    $comen=0;
				    break;	
			    }
		    }		    
		    if($dia_actual=="#FF00FF")
		    {
			    if($transparente==0)
			    {
			    if($roj==0)
			    {
			    if(($verde==1)&&($cele==0)&&($v==0))
			    {
			    $v=1;	
			    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
			    $fin_compra=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));
			    //$inicio_cd=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1,$anio_1));		
			    }
			    if(($cele==1)&&($c==0))
			    {
			    $c=1;	
			    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
			    $fin_cd=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));		
			    }	
			    $comillas=1;	
			    $roj=1;
			    $rojo=1;
			    $otro=0;
			    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
			    $inicio_entrega=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1,$anio_1));	
			    }
			    }
			    else 
			    {   
			    	$contador1++;
				    $error1[$contador1]=$id_L;
				    $comen=0;
				    break;	
			    }		    
		    }
		    if(($dia_letras=='Sun')||(feriado($fer))||($dia_letras=='Sat'))
		    {
		    	//echo"$desde3";
		    	
		    	$si_esta="select * from licitaciones.fecha_color where id_subir=$id_sub and fecha='$desde3'";
				$si=sql($si_esta,"Error al traer los datos de la tabla color") or fin_pagina();
				if(($si->RecordCount()==0)&&($dia_actual!=""))
				{
					$campos="(fecha,id_subir,color)";	
					$ingresar="insert into fecha_color $campos VALUES ".
		            "('$desde3',$id_sub,'$dia_actual')";
		            sql($ingresar,"no se pudo guardar los datos en la tabla color")or fin_pagina();	
				}
				else 
				{
				$update_color="update licitaciones.fecha_color set color='$dia_actual' where id_subir=$id_sub and fecha='$desde3'";
		        sql($update_color,"No se pudo actualizar la tabla color")or fin_pagina();		
				}
				
		    	
		    }
		    else 
		    {
		    if($dia_actual=="")
		    {
		    	
			    /*if(($rojo==1)||($verde==1)||($celeste==1))
			    {*/
			   // $transparente=1;
				    if(($verd==1)&&($roj==0)&&($cele==0))
				    {
				    	if($v==0)
				         {
					    	$v=1;
				         	list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
					    	$fin_compra=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));	
				         }
				    }
				    if(($cele==1)&&($roj==0))
				    {	
				    	if($c==0)
				         {
				       
					    	$c=1;		     
					    	list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
					    	$fin_cd=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));
					    	
				         }	
				         
				    }
				   if(($r==0)&&($roj==1))
				    {	
				    	if($r==0)
				         {
					    	$r=1;		     
					    	list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
					    	$fin_entre=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));
				         }	
				         
				    }
				  
			   // }
		    		    
		    }
		    } 
		    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);
		    list($anio3,$mes3,$dia3)=explode("-",$desde3);
			$desde1=date("Y_m_d",mktime(0,0,0,$mes_1,$dia_1+1,$anio_1));
			$desde3=date("Y-m-d",mktime(0,0,0,$mes3,$dia3+1,$anio3));
			$a++;
			/*if($roj==1)
			{
				$var='d_'.$j.'_'.$desde1;
			    $dia_actual=$_POST["$var"];
			    if($dia_actual!="#FF00FF")
			    {
				    if($otro==0)
				    {
					   	$otro=1;
					    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
					    $fin_entre=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));	
				    }
			    }
			    if(($a==$diferencia)&&($otro==0))
			    {
				    $otro=1;
				    list($anio_1,$mes_1,$dia_1)=explode("_",$desde1);	
				    $fin_entre=date("Y-m-d H:i:s",mktime(0,0,0,$mes_1,$dia_1,$anio_1));			
			    }    
			    	
			}*/
		}
		//echo"$comen";
		
		if($comen==1)
		{
		
		/*echo"ini_compra='$inicio_com',fin_compra='$fin_compra',ini_cdr='$inicio_cd',fin_cdr='$fin_cd',ini_entrega='$inicio_entrega',fin_entrega='$fin_entre'";
		die();*/
		//echo"hasta $hasta1 final $final desde2 $desde2 inicio $inic";
		/*if((compara_fechas($hasta1,$final)!=-1)&&(compara_fechas($inic,$desde2)!=-1))
		{*/	
			
			if(($rojo==0)&&($verde==0)&&($celeste==0))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra=$inicio_com,fin_compra=$fin_compra,ini_cdr=$inicio_cd,fin_cdr=$fin_cd,ini_entrega=$inicio_entrega,fin_entrega=$fin_entre,guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();	
			}
			
			if(($rojo==1)&&($verde==1)&&($celeste==1))
			{
			//echo "if idlic $id_L com $inicio_com fcom $fin_compra --icdr $inicio_cd--fcdr $fin_cd --inent $inicio_entrega--finentrega $fin_entre a $a";		
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra='$inicio_com',fin_compra='$fin_compra',ini_cdr='$inicio_cd',fin_cdr='$fin_cd',ini_entrega='$inicio_entrega',fin_entrega='$fin_entre',guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
			
			if(($rojo==1)&&($verde==0)&&($celeste==0))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra=$inicio_com,fin_compra=$fin_compra,ini_cdr=$inicio_cd,fin_cdr=$fin_cd,ini_entrega='$inicio_entrega',fin_entrega='$fin_entre',guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
			
			if(($rojo==1)&&($verde==1)&&($celeste==0))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra='$inicio_com',fin_compra='$fin_compra',ini_cdr=$inicio_cd,fin_cdr=$fin_cd,ini_entrega='$inicio_entrega',fin_entrega='$fin_entre',guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
			
			if(($rojo==1)&&($verde==0)&&($celeste==1))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra=$inicio_com,fin_compra=$fin_compra,ini_cdr='$inicio_cd',fin_cdr='$fin_cd',ini_entrega='$inicio_entrega',fin_entrega='$fin_entre',guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
			
			if(($rojo==0)&&($verde==1)&&($celeste==0))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra='$inicio_com',fin_compra='$fin_compra',ini_cdr=$inicio_cd,fin_cdr=$fin_cd,ini_entrega=$inicio_entrega,fin_entrega=$fin_entre,guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
			
			if(($rojo==0)&&($verde==1)&&($celeste==1))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra='$inicio_com',fin_compra='$fin_compra',ini_cdr='$inicio_cd',fin_cdr='$fin_cd',ini_entrega=$inicio_entrega,fin_entrega=$fin_entre,guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
			
			if(($rojo==0)&&($verde==0)&&($celeste==1))
			{
			$guardar_fec="update licitaciones.subido_lic_oc set ini_compra=$inicio_com,fin_compra=$fin_compra,ini_cdr='$inicio_cd',fin_cdr='$fin_cd',ini_entrega=$inicio_entrega,fin_entrega=$fin_entre,guardado=1 where id_subir=$id_sub";
			sql($guardar_fec,"No se pudo guardar las nuevas fechas en la base de datos") or fin_pagina();
			}
		//}	
		}
	  
	  }
	 	  $j++;
	}
	?>
	<script>
	document.styleSheets[0].addRule("td", "font-size:"+<?=$tamaño?>);
    document.styleSheets[0].addRule("a", "font-size:"+<?=$tamaño?>);
    document.styleSheets[1].addRule("textarea", "font-size:"+<?=$tamaño?>);
    document.styleSheets[0].addRule("select", "font-size:"+<?=$tamaño?>);
    //alert((parseInt(f.replace("px"," "))+w)+'px');
	</script>
	
	<?

}


$consulta="SELECT licitacion.id_licitacion,entrega_estimada.nro,prioridad,comentarios_lic,entrega_estimada.id_entrega_estimada,entrega_estimada.comprado,vence_oc,fecha_subido,
entidad.nombre,sistema.usuarios.iniciales,lider,ini_compra,ini_compra1,id_subir,fin_compra,ini_entrega,fin_entrega,ini_cdr,fin_cdr,guardado,entidad.id_distrito
FROM licitaciones.licitacion 
LEFT JOIN licitaciones.entidad USING (id_entidad)
left JOIN licitaciones.entrega_estimada USING (id_licitacion)
LEFT JOIN sistema.usuarios on usuarios.id_usuario=licitacion.lider
JOIN licitaciones.subido_lic_oc USING (id_entrega_estimada)
left join (select ini_compra1,compra1.id_subir,nro_orden from
(select min(fecha) as ini_compra1,id_subir from compras.orden_de_compra group by (id_subir))as compra1 
join compras.orden_de_compra on compra1.id_subir=orden_de_compra.id_subir and compra1.ini_compra1=orden_de_compra.fecha)as compras2 using(id_subir)
where entrega_estimada.finalizada=0 AND borrada='f' ";

if($ordenar_listado==1)
$consulta.="order by ini_entrega asc";
else $consulta.="order by prioridad";

$ejecutar=sql($consulta,"no se pudo recuperar los datos de las licitaciones") or fin_pagina();

//.feriado{border-width:2px;border-style:solid;border-color:red;}
?>

<script>

function alternar_color(obj,blanco,verde,rojo,color,i,dia_che) 
{
		color=color.toLowerCase();
		blanco=blanco.toLowerCase();
		rojo=rojo.toLowerCase();
		verde=verde.toLowerCase();
		var oculto=eval("document.all.d_"+i+"_"+dia_che);
		
		if(oculto.value=="#00C823")
		{
			obj.style.backgroundColor = color;
			oculto.value="#00FFFF";
		}	
		else 
		{
			if(oculto.value=="#00FFFF")
			  {
				  obj.style.backgroundColor = rojo;	
				  oculto.value="#FF00FF";
			  }
			else
			  {
			if(oculto.value=="#FF00FF")
				  {
				   obj.style.backgroundColor ='transparent';
				   oculto.value="";
				  }	
				  else
				  {
					obj.style.backgroundColor = verde;
			        oculto.value="#00C823"; 
				  }	  
			  }
		 }		 
}

function chequear_todos()
{
var cont=1;
if(document.all.check_todos.value==1)
{
	while(cont<=document.all.cant_guardar.value)
	{
		var chec=eval("document.all.chequeado_"+cont);	
		chec.checked=true;
		cont++;
	}
	document.all.check_todos.value=0;	
}
else
{
	while(cont<=document.all.cant_guardar.value)
	{
		var chec=eval("document.all.chequeado_"+cont);	
		chec.checked=false;
		cont++;
	}
	document.all.check_todos.value=1;	
    
}
}

function lic_posterior(id_subir1) {	
	document.all.posterior.value=id_subir1;
	document.all.anterior.value=1;
}

  function zoom2(incremento,incre){
     var x,y,z,w,p;
     y=parseInt(incremento);
     w=parseInt(incre); 
     /*if (document.all.tabla_p.style.width){
           x=document.all.tabla_p.style.width;
           }
           else{
           x=80;
           }       
    var j = new String (x.toString());*/
    p=document.all.tabla_p.style.fontSize;  
    var f = new String (p.toString());
    
    //document.all.tabla_p.style.width=parseInt(j.replace("px"," "))+y;
    var fff=document.all.tamaño.value;
    //alert(fff);
    //var ff=parseInt(f.replace("px"," "))+w;
    
    var ff=parseInt(fff)+w;
    if(ff>0)
    {
	    document.styleSheets[0].addRule("td", "font-size:"+ff);
	    document.styleSheets[0].addRule("a", "font-size:"+ff);
	    document.styleSheets[1].addRule("textarea", "font-size:"+ff);
	    document.styleSheets[0].addRule("select", "font-size:"+ff);
	    //alert((parseInt(f.replace("px"," "))+w)+'px');
	    document.all.tabla_p.style.fontSize=(parseInt(f.replace("px"," "))+w)+'px';  
	    document.all.tamaño.value=ff;  
    } 
    }
</script>
<?
if($pagina_ant!=1)
{
	 $des=date("Y-m-d",mktime());
	 $has=date("Y-m-d",mktime());
	 while(!$ejecutar->EOF)
	 {
		 $ven_oc=$ejecutar->fields['vence_oc'];	
		 $comi=$ejecutar->fields['fecha_subido'];
		 if(compara_fechas($des,$comi)==1)
		 {
		 $des=$comi;	
		 }
		 if(compara_fechas($ven_oc,$has)==1)
		 {
		 $has=$ven_oc;	
		 }
		 $ejecutar->MoveNext();	
	 }
	 list($anio_1,$mes_1,$dia_1)=explode("-",$des);
	 $des=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1-4,$anio_1));
	 list($anio,$mes,$dia)=explode("-",$has);
	 $has=date("Y-m-d",mktime(0,0,0,$mes,$dia+4,$anio));
	 $des=Fecha($des);
	 $has=Fecha($has);
	 $dia_actu=date("Y-m-d",mktime());
	 $contador=diferencia_dias($des,$has);
	 $cont=diferencia_dias($des,$has);
	 $Fecha_Desde=$des;
	 $Fecha_Hasta=$has;
	 $diferencia1=diferencia_dias($Fecha_Desde, $Fecha_Hasta);
	 
	 $des=Fecha_db($des);
	 $has=Fecha_db($has);
	 list($anio_1,$mes_1,$dia_1)=explode("-",$des);
	 list($anio,$mes,$dia)=explode("-",$has);
	 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ant1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia2=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia3=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ult=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));
	 $ejecutar->movefirst();
	
 }               
else
{	 
	 $dia_actu=date("Y-m-d",mktime());
	 $contador=diferencia_dias($des,$has);
	 $cont=diferencia_dias($des,$has);
	 $Fecha_Desde=$des;
	 $Fecha_Hasta=$has;
	 $diferencia1=diferencia_dias($Fecha_Desde, $Fecha_Hasta);
	 $des=Fecha_db($des);
	 $has=Fecha_db($has);
	 list($anio_1,$mes_1,$dia_1)=explode("-",$des);
	 list($anio,$mes,$dia)=explode("-",$has);
	 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ant1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia2=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia3=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ult=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));
}  
?>
<form name='form1' method="POST" action="estado_licitacion.php">
<input type='hidden' name='pagina' value='1'>
<input type='hidden' name='anterior' value=''>
<input type='hidden' name='posterior' value=''>
<input type='hidden' name='tamaño' value='<?=$tamaño?>'>
 
<?
if($comentar) error($comentar);

if($contador1>0)
{
?>
<table align="center">
<tr>
<td><?error('Las siguientes Licitaciones no se guardaron por errores en las fechas');?></td>
</tr>
<tr align="center">
<td align="center"><font color="Red">
<?
$suma=1;
while($suma<=$contador1)
{
?>
<b><?echo"Licitacion N° $error1[$suma]";?>&nbsp;</b>
<?
$suma++;	
}
?>
</font></td>
</tr>
</table>
<?
}?>
<table align="center">
<tr>
<td>
<?

//if ($_ses_user_login == 'mascioni' || $_ses_user_login == 'juanmanuel' || $_ses_user_login == 'quique' || $_ses_user_login == 'marcos')
if (permisos_check("inicio","permiso_boton_ordenar_licitaciones"))
{
?>
	<input type=submit name=ordenar value='Ordenar'>
<?
}

?>
<input type="submit" name="oculto" value="" onclick="return false;"  style="width:0px">
<input type=submit name=guardar value='Guardar'>
<?
 echo "<b>Desde: </b>";
 echo "<input type=text size=10 name=Entre_Fechas_Desde value='$Fecha_Desde' title='Ingrese la fecha de inicio y\nhaga click en Actualizar'>";
 echo link_calendario("Entre_Fechas_Desde");
 echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>Hasta: </b>";
 echo "<input type=text size=10 name=Entre_Fechas_Hasta value='$Fecha_Hasta' title='Ingrese la fecha de finalización\ny haga click en Actualizar'>";
 echo link_calendario("Entre_Fechas_Hasta");//onclick="control()"
 ?>
&nbsp;&nbsp;&nbsp;<input type=submit name=Actualizar value='Actualizar Fecha' >
&nbsp;&nbsp;&nbsp;<input type=submit name=comentarios value='Guardar Comentarios' >
</td>
<td>
<b>Zoom</b>
<input type=button name=zoom value=" + " onclick="zoom2(10,2)">

<input type=button name=zoom value=" - " onclick="zoom2(-10,-2)">

<input type=hidden name=valor value=30>
</td>
</tr> 
</table>

<div style="overflow:auto;height:490;width:100%" id="tabla_licitaciones" >    
<table id="tabla_p" name="tabla_p" border="1" width="80" style="font-size:12px">
<tr>
	 <td align="center" bgcolor="<?=$bgcolor_out?>"><input type="checkbox" name="check_todos" value="1" onclick="chequear_todos()"></td>
	 <td align="center" bgcolor="<?=$bgcolor_out?>"><b>Lic/Cli</b></td>
	 <td bgcolor="<?=$bgcolor_out?>"><b>Lid</b></td>
	 <?	 
	 $fondo="#cccccc";
	 $ar_mes[1]="Enero";
	 $ar_mes[2]="Febrero";
	 $ar_mes[3]="Marzo";
	 $ar_mes[4]="Abril";
	 $ar_mes[5]="Mayo";
	 $ar_mes[6]="Junio";
	 $ar_mes[7]="Julio";
	 $ar_mes[8]="Agosto";
	 $ar_mes[9]="Septiembre";
	 $ar_mes[10]="Octubre";
	 $ar_mes[11]="Noviembre";
	 $ar_mes[12]="Diciembre";
	 while($contador>=0)	
	{   
		 list($anio,$mes,$dia)=explode("-",$dia_ant);
		 $dia_letras=date("D",mktime(00,0,0,$mes,$dia,$anio));
		 $mes_letras=date("n",mktime(0,0,0,$mes,$dia,$anio));
		 $fe_com=date("r",mktime(0,0,0,$mes,$dia,$anio));
		 $dia_feriado=Fecha($dia_ant);
		 if(($dia==01)&&($fondo=="#cccccc")) 
		 {
	     $fondo='#FFFFFF'; 
		 }
		 else 
		 {
		 if(($dia==01)&&($fondo=="#FFFFFF")) 
		 {
	     $fondo="#cccccc"; 
		 }	
		 }  
		 if($dia==01)
		 {
		 if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
		 {	
			 ?>
			 <td bgcolor="<?=$fondo?>" title="<?=$fe_com?>" width="2"><font color="Red"><b><?echo "$dia/$mes";?></b></font></td>
			 <?
		 }
		 else 
		 { 
		  if($dia_actu==$dia_ant)
		 {	
			 ?>
			 <td bgcolor="Blue" class="actual" title="<?=$fe_com?>"><font color="Red"><?echo "$dia/$mes";?></font></td>
			 <?
		 }
		 else 
		 { 	
		 	
			 ?>
			  <td bgcolor="<?=$fondo?>" title="<?=$fe_com?>"><b><?echo "$dia/$mes";?></b></td>
			 <?
		 }
		 }
		 }
		 else 
		 {
          if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
		 {	
			 ?>
			 <td bgcolor="<?=$fondo?>" title="<?=$fe_com?>"><font color="Red"><?echo $dia;?></font></td>
			 <?
		 }
		 else 
		 { 
		  if($dia_actu==$dia_ant)
		 {	
			 ?>
			 <td bgcolor="Blue" class="actual" title="<?=$fe_com?>"><b><?echo $dia;?></b></td>
			 <?
		 }
		 else 
		 { 	
			 ?>
			  <td bgcolor="<?=$fondo?>" title="<?=$fe_com?>" width="4"><b><?echo $dia;?></b></td>
			 <?
		 }
		 }
		 }   
		 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));
	     $contador--; 
	
     }    
    ?>
</tr>
<?
$cantidad_lic=$ejecutar->RecordCount();
$i=1;
$int1=0;
while(!$ejecutar->EOF)
{   $co=1;
    
	$int1++;
    $j=$ejecutar->RecordCount();
	$fin_compra=$ejecutar->fields['fin_compra'];
    $ini_compras=$ejecutar->fields['ini_compra'];
    $lic_com=$ejecutar->fields['comentarios_lic'];
    $fin_cdr=$ejecutar->fields['fin_cdr'];
    $ini_cdr=$ejecutar->fields['ini_cdr'];
    $fin_entrega=$ejecutar->fields['fin_entrega'];
    $ini_entrega=$ejecutar->fields['ini_entrega'];
    $vence_oc=$ejecutar->fields['vence_oc'];
    $id_subir=$ejecutar->fields['id_subir'];
    $id_distrito=$ejecutar->fields['id_distrito'];
    $id_sub=$ejecutar->fields['id_subir'];
    $guardado_base=$ejecutar->fields['guardado'];
    $priorid=$ejecutar->fields['prioridad'];
    $recup="select fecha,color from fecha_color where id_subir=$id_sub";
    $color_dia=sql($recup,"no se pudo recuperar el color del dia")or fin_pagina();
    $sumador=0;
    while(!$color_dia->EOF)
	{
		$fec_col[$sumador]=$color_dia->fields['fecha'];	
		$col_d[$sumador]=$color_dia->fields['color'];	
		$sumador++;
		$color_dia->MoveNext();
	}
    if($guardado_base==1)
    {
    $fin_entrega=Fecha_db(Fecha($fin_entrega));
    $ini_compra=Fecha_db(Fecha($ini_compras));
    $ini_entrega=Fecha_db(Fecha($ini_entrega));	
    $fin_cdr=Fecha_db(Fecha($fin_cdr));
    $ini_cdr=Fecha_db(Fecha($ini_cdr));	
    $fin_compra=Fecha_db(Fecha($fin_compra));
    }
    else 
    {
    $fin_entrega=$vence_oc;
    $ini_compra1=Fecha($ejecutar->fields['ini_compra1']);	
    if($ini_compra1!="")
    {
    	$ini_compra=Fecha_db($ini_compra1);	
    }
    else
    { 
    	$ini_compra=Fecha_db(Fecha($ejecutar->fields["fecha_subido"]));
    }	
       
    //un dia de entrega para id_distrito=12 => Prov Bs As
    //2 dias de entregas en otro distrito
    $fin_entrega1=Fecha($fin_entrega);
    if ($id_distrito==12) 
        $ini_entrega=Fecha_db(dias_habiles_anteriores($fin_entrega1,1)); //resta 1 dias
    else  
       $ini_entrega=Fecha_db(dias_habiles_anteriores($fin_entrega1,2)); //resta 2 dia
   
   
    //$fin_entrega=Fecha_db($fin_entrega);    
	//fin_cdr 
   
     	list($anio,$mes,$dia)=explode("-",$ini_entrega);
     	$fin_cdr=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));
 
//ini_cdr 
  
 	if ($ini_cdr == "" || $ini_cdr == NULL) {
      	$sql_cdr="select sum (renglones_oc.cantidad) as cantidad from licitaciones.renglones_oc 
                  join licitaciones.renglon using (id_renglon) where id_subir=$id_subir and 
                  (tipo='Computadora Enterprise' or tipo='Computadora Matrix')";
        $res_cdr=sql($sql_cdr) or fin_pagina();
        if ($res_cdr->fields['cantidad'] !=NULL && $res_cdr->fields['cantidad']!="") 
        {
        	$cantidad=$res_cdr->fields['cantidad'];
            $sql_cant="select cant_dias from licitaciones.dias_armado_cdr where $cantidad between lim_inf and lim_sup";
            $res_cant=sql($sql_cant,"no se pudo recuperar las cantidades") or fin_pagina();
            $dias=$res_cant->fields['cant_dias']; 
            
            $fin_cdr1=Fecha($fin_cdr);
           
            $id_l=$ejecutar->fields["id_licitacion"];
            $ini_cdr=Fecha_db(dias_habiles_anteriores($fin_cdr1,$dias));
            list($anio,$mes,$dia)=explode("-",$ini_cdr);
     	    $ini_cdr=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));
          
        }
        else 
        {
        	$ini_cdr=$ini_entrega;
        }
      }
      
	 
 
	$fin_compra=$ini_cdr;
	list($anio,$mes,$dia)=explode("-",$fin_compra);
    $fin_compra=date("Y-m-d",mktime(0,0,0,$mes,$dia-1,$anio));
    
    
    }

    if($ordenar_listado==1)
    {
    
    	$update_p="update licitaciones.subido_lic_oc set prioridad=$i where id_subir=$id_sub";
		$update_p1=sql($update_p,"No se pudo actualizar la prioridad1")or fin_pagina();		
    }
    else 
    {
    if($priorid==0)
    {
	    $update_p="update licitaciones.subido_lic_oc set prioridad=$i where id_subir=$id_sub";
		$update_p1=sql($update_p,"No se pudo actualizar la prioridad1")or fin_pagina();	
    }
    }
    $vence=$ejecutar->fields["vence_oc"];
	$comien=$ejecutar->fields["fecha_subido"];
	$id=$ejecutar->fields['id_licitacion'];
	$nombre_e=cortar($ejecutar->fields["nombre"],10);
    $nombre_cli=$ejecutar->fields["nombre"];
	?>
	<tr>
	<input type="hidden" name="id_<?=$i?>" value="<?=$id_sub?>">
	
	<input type="hidden" name="idlic_<?=$i?>" value="<?=$id?>">
	 
	<?
	if($guardado_base==1)
	{
	?>
	<td rowspan="3" bgcolor="Navy" class="separador" align="center"  title="<?=$i?>">
	<input type="checkbox" name="chequeado_<? echo $i; ?>" value="<?=$id_sub?>"><br>
	<?
	$en_la_base=0;
	}
	else 
	{
	?>
	<td rowspan="3" bgcolor="Olive" class="separador" align="center" title="<?=$i?>">
	<input type="checkbox" name="chequeado_<? echo $i; ?>" value="<?=$id_sub?>"  ><br>
	<?
	}
	?>
	<select name="inter_<?=$id_sub?>" onchange="lic_posterior(<?=$id_sub?>)">
	<?
	$int=1;
	while($j>=$int)	
	{
	if($i==$int)
	{
	?>
    <option selected value="<?=$i?>"><?=$i?></option>
    <?
	}
    else
    {
    ?>
    <option value="<?=$int?>"><?=$int?></option>  
    <?
    }
    $int++;
    }
    ?> 
    </select>   	
	</td>
	<td rowspan="2" align="center"  title="<?echo $ejecutar->fields["id_licitacion"]." ".$nombre_cli?>"><b> 
	<a target="_blank" href='<?=encode_link("../ordprod/ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$ejecutar->fields["id_licitacion"], "id_entrega_estimada"=>$ejecutar->fields['id_entrega_estimada'], "nro_orden"=>$ejecutar->fields["nro_orden"],"nro"=>$ejecutar->fields["nro"],"id_subir"=>$ejecutar->fields["id_subir"],"nro_orden_cliente"=>$ejecutar->fields["numero"]));?>'>N° <?=$ejecutar->fields["id_licitacion"];?><br><?=$nombre_e;?></A>
	</b>
	
	</td>
	<?
	if($ejecutar->fields["iniciales"]=="")
	{
		?>
		<td rowspan="2" align="center" title="<?=$ejecutar->fields["iniciales"]?>"><b>Sin Lider</b></td>
		<?
	}
	else 
	{
		?>
		<td rowspan="2" align="center" title="<?=$ejecutar->fields["iniciales"]?>"><b> <?=$ejecutar->fields["iniciales"];?> </b></td>
		<?
	}
	?>
	
	
  <?
while(compara_fechas($dia_ult,$dia1)!=-1)
{	list($anio,$mes,$dia)=explode("-",$dia1);
	$dia_letras=date("D",mktime(0,0,0,$mes,$dia,$anio));
	$dia_feriado=Fecha($dia1);
	if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
	{		
		if(compara_fechas($dia1,$comien)!=-1)//dia1 mayor que comien
		{
		if(compara_fechas($vence,$dia1)!=-1)	//vence mayor que dia1
		{
		?>
		<td bgcolor="#FFCC00" class="feriado" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
		else 
		{
		?>
		<td class="feriado" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}		
		}
		else 
		{
		?>
		<td class="feriado" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
	}
	
	else 
	{
		
		if($dia_actu==$dia1)
	    {		
		if(compara_fechas($dia1,$comien)!=-1)//dia1 mayor que comien
		{
		if(compara_fechas($vence,$dia1)!=-1)	//vence mayor que dia1
		{
		?>
		<td bgcolor="#FFCC00" class="actual" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
		else 
		{
		?>
		<td class="actual" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}		
		}
		else 
		{
		?>
		<td class="actual" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
	    }
	
	else 
	{
		
		if(compara_fechas($dia1,$comien)!=-1)//dia1 mayor que comien
		{
		if(compara_fechas($vence,$dia1)!=-1)	//vence mayor que dia1
		{
		?>
		<td bgcolor="#FFCC00" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
		else 
		{
		?>
		<td title="<?=$lic_com?>">&nbsp;</td>
		<?
		}		
		}
		else 
		{
		?>
		<td title="<?=$lic_com?>">&nbsp;</td>
		<?
		}	
	
	}
   }
	list($anio,$mes,$dia)=explode("-",$dia1);	
	$dia1=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));	
	
}
?>	
</tr>
<tr>
<?
while(compara_fechas($dia_ult,$dia2)!=-1)
{   
	$control=0;
    list($anio,$mes,$dia)=explode("-",$dia2);	
	$dia_letras=date("D",mktime(0,0,0,$mes,$dia,$anio));
	$dia_feriado=Fecha($dia2);
	$dia_che=date("Y_m_d",mktime(0,0,0,$mes,$dia,$anio));	
    $anterior=date("Y_m_d",mktime(0,0,0,$mes,$dia-1,$anio));	
	$posterior=date("Y_m_d",mktime(0,0,0,$mes,$dia+1,$anio));
	$comparar=$dia2;
	$control1=0;
	$conta=0; 
	$sumador1=$sumador;
	$si_dia=0;
	while($conta<$sumador1)
	{
		if(compara_fechas($fec_col[$conta],$comparar)==0)
		{
		$si_dia=1;
		$col_f=$conta;
		$conta=$sumador1;	
		}
		$conta++;	
	}
	if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
	{   
		if($si_dia==1)
		{
		?>
			<td id="td_<?=$i.'_'.$dia_che?>" bgcolor="<?=$col_d[$col_f]?>"  class="feriado" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="<?=$col_d[$col_f]?>">
			</td>
			<?	
		}
	else 
	{	
		if ($ini_compra !=NULL ||$ini_compra !="")
		{	    
		    if(compara_fechas($dia2,$ini_compra)!=-1)
			{
			if(compara_fechas($fin_compra,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
		  	 $colores="#00C823";
			?>
			<td id="td_<?=$i.'_'.$dia_che?>"  class="feriado" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
			</td>
			<?
			$control1=1;
			}	
			}
		}
		if ($ini_cdr !=NULL ||$ini_cdr !="")
		{
		
			if((compara_fechas($dia2,$ini_cdr)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_cdr,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
			 $colores="#00FFFF";
			?>
			<td id="td_<?$i.'_'.$dia_che?>"  class="feriado" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
			</td>
			<?
			$control1=1;
			}	
			}
		}
		if ($ini_entrega !=NULL ||$ini_entrega !="")
		{
			if((compara_fechas($dia2,$ini_entrega)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_entrega,$dia2)!=-1)	//vence mayor que dia1
			{
			$control=1;
			$colores="#FF00FF";
			?>
			<td id=td_"<?$i.'_'.$dia_che?>"  class="feriado" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
			</td>
			<?
			}	
			}
		}
		if($control==0)
		{$colores="transparent";
		?>	
		<td id="td_<?$i.'_'.$dia_che?>" class="feriado" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
		<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
		</td>
		<?	
		}
	}
	}
	
	else 
	{
		
		if($dia_actu==$dia2)
	      {   
		if ($ini_compra !=NULL ||$ini_compra !="")
		{	    
		    if(compara_fechas($dia2,$ini_compra)!=-1)
			{
			if(compara_fechas($fin_compra,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
		  	 $colores="#00C823";
			?>
			<td id="td_<?=$i.'_'.$dia_che?>" bgcolor="#00C823" class="actual" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#00C823">
			</td>
			<?
			$control1=1;
			}	
			}
		}
		if ($ini_cdr !=NULL ||$ini_cdr !="")
		{
		
			if((compara_fechas($dia2,$ini_cdr)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_cdr,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
			 $colores="#00FFFF";
			?>
			<td id="td_<?$i.'_'.$dia_che?>" bgcolor="#00FFFF" class="actual" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#00FFFF">
			</td>
			<?
			$control1=1;
			}	
			}
		}
		if ($ini_entrega !=NULL ||$ini_entrega !="")
		{
			if((compara_fechas($dia2,$ini_entrega)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_entrega,$dia2)!=-1)	//vence mayor que dia1
			{
			$control=1;
			$colores="#FF00FF";
			?>
			<td id=td_"<?$i.'_'.$dia_che?>" bgcolor="#FF00FF" class="actual" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#FF00FF">
			</td>
			<?
			}	
			}
		 }
		if($control==0)
		{$colores="transparent";
		?>	
		<td id="td_<?$i.'_'.$dia_che?>" class="actual" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
		<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
		</td>
		<?	
		}
	    }
	
	    else 
	    {
		
		if ($ini_compra !=NULL ||$ini_compra !="")
		{
			if(compara_fechas($dia2,$ini_compra)!=-1)
			{
			if(compara_fechas($fin_compra,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
		  	 $colores="#00C823";
			?>
			<td id="td_<?=$i.'_'.$dia_che?>" bgcolor="#00C823" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#00C823">
			</td>
			<?
			$control1=1;
			}	
			}
		}
		if ($ini_cdr !=NULL ||$ini_cdr !="")
		{
			if((compara_fechas($dia2,$ini_cdr)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_cdr,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
			 $colores="#00FFFF";
			?>
			<td id="td_<?$i.'_'.$dia_che?>" bgcolor="#00FFFF" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#00FFFF">
			</td>
			<?
			$control1=1;
			}	
			}
		}
		if ($ini_entrega !=NULL ||$ini_entrega !="")
		{
			if((compara_fechas($dia2,$ini_entrega)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_entrega,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
			$colores="#FF00FF";
			?>
			<td id=td_"<?$i.'_'.$dia_che?>" bgcolor="#FF00FF" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#FF00FF">
			</td>
			<?
			}	
			}
		}
		if($control==0)
		{
			$colores="transparent";
			?>	
			<td id="td_<?$i.'_'.$dia_che?>" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
			</td>
			<?	
		}	
	}
   }	
	list($anio,$mes,$dia)=explode("-",$dia2);	
	$dia2=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));					
	}

	?>
	</tr>
	<tr>
	
	<td colspan="2" class="separador" title="<?=$lic_com?>">
	<textarea id="co" class="achicar" name="comentario_<?=$i?>" title="<?=$lic_com?>" rows="1"><? echo "$lic_com";?>
	</textarea>
	</td>
	
	<?

	while(compara_fechas($dia_ult,$dia3)!=-1)
	{   
		list($anio,$mes,$dia)=explode("-",$dia3);	
	    $dia_letras=date("D",mktime(0,0,0,$mes,$dia,$anio));
	    $dia_feriado=Fecha($dia3);
	    $can_come=0;
	    if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
	     {     
		?>
		<td  class="feriado" align="center"" title="<?=$lic_com?>">&nbsp;&nbsp;</td>
		<?
	     }	
	     else 
	     {
	     if($dia_actu==$dia3)
	      { 
	      	?>  	
	     	<td class="actual" align="center"" title="<?=$lic_com?>">&nbsp;&nbsp;</td>
			<?
	      } 
	      else
	      {
			 ?> 
			 <td class="separador" align="center"" title="<?=$lic_com?>">&nbsp;&nbsp;</td>
			 <?
	     }
	     }
		 $dia3=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));		 
	}
	?>
	</tr>
	<?
if(($int1==8)&&($cantidad_lic>$i))
{
$int1=0;	
$contador=$cont;
$dia_ant=$dia_ant1;	
?>	
<tr>
	 <td align="center" bgcolor="<?=$bgcolor_out?>">&nbsp;</td>
	<td align="center" bgcolor="<?=$bgcolor_out?>"><b>Lic/Cli</b></td>
	<td bgcolor="<?=$bgcolor_out?>"><b>Lid</b></td>
	 <?	 
	 $fondo="#cccccc";
	 $ar_mes[1]="Enero";
	 $ar_mes[2]="Febrero";
	 $ar_mes[3]="Marzo";
	 $ar_mes[4]="Abril";
	 $ar_mes[5]="Mayo";
	 $ar_mes[6]="Junio";
	 $ar_mes[7]="Julio";
	 $ar_mes[8]="Agosto";
	 $ar_mes[9]="Septiembre";
	 $ar_mes[10]="Octubre";
	 $ar_mes[11]="Noviembre";
	 $ar_mes[12]="Diciembre";
	 while($contador>=0)	
	{   
		 list($anio,$mes,$dia)=explode("-",$dia_ant);
		 $dia_letras=date("D",mktime(0,0,0,$mes,$dia,$anio));
		 $mes_letras=date("n",mktime(0,0,0,$mes,$dia,$anio));
		 $dia_feriado=Fecha($dia_ant);
		 if(($dia==01)&&($fondo=="#cccccc")) 
		 {
	     $fondo='#FFFFFF'; 
		 }
		 else 
		 {
		 if(($dia==01)&&($fondo=="#FFFFFF")) 
		 {
	     $fondo="#cccccc"; 
		 }	
		 }  
		 if($dia==01)
		 {
		 if(($dia_letras=='Sun')||(feriado($dia_feriado)))
		 {	
			 ?>
			 <td bgcolor="<?=$fondo?>" title="<?=$$ar_mes[$mes_letras]?>"><font color="Red"><b><?echo "$dia/$mes";?></b></font></td>
			 <?
		 }
		 else 
		 { 
		  if($dia_actu==$dia_ant)
		 {	
			 ?>
			 <td bgcolor="Blue" class="actual" title="<?=$ar_mes[$mes_letras]?>"><font color="Red"><?echo "$dia/$mes";?></font></td>
			 <?
		 }
		 else 
		 { 	
		 	
			 ?>
			  <td bgcolor="<?=$fondo?>" title="<?=$ar_mes[$mes_letras]?>"><b><?echo "$dia/$mes";?></b></td>
			 <?
		 }
		 }
		 }
		 else 
		 {
          if(($dia_letras=='Sun')||(feriado($dia_feriado)))
		 {	
			 ?>
			 <td bgcolor="<?=$fondo?>" title="<?=$ar_mes[$mes_letras]?>"><font color="Red"><?echo $dia;?></font></td>
			 <?
		 }
		 else 
		 { 
		  if($dia_actu==$dia_ant)
		 {	
			 ?>
			 <td bgcolor="Blue" class="actual" title="<?=$ar_mes[$mes_letras]?>"><b><?echo $dia;?></b></td>
			 <?
		 }
		 else 
		 { 	
			 ?>
			  <td bgcolor="<?=$fondo?>" title="<?=$ar_mes[$mes_letras]?>"><b><?echo $dia;?></b></td>
			 <?
		 }
		 }
		 }   
		 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));
	     $contador--; 
	
     }    
    ?>
</tr>	
<?		
}
	
 if($pagina_ant!=1)
{
	
	
	 $des=Fecha($des);
	 $has=Fecha($has);
	 $dia_actu=date("Y-m-d",mktime());
	 $contador=diferencia_dias($des,$has);
	 $cont=diferencia_dias($des,$has);
	 $Fecha_Desde=$des;
	 $Fecha_Hasta=$has;
	 $diferencia1=diferencia_dias($Fecha_Desde, $Fecha_Hasta);
	 $des=Fecha_db($des);
	 $has=Fecha_db($has);
	 list($anio_1,$mes_1,$dia_1)=explode("-",$des);
	 list($anio,$mes,$dia)=explode("-",$has);
	 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ant1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia2=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia3=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ult=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));
	 
}
else 
{ 
	 list($anio_1,$mes_1,$dia_1)=explode("-",$des);
	 list($anio,$mes,$dia)=explode("-",$has);
	 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia2=date("y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia3=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ult=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));	
}
  	
	$i++; 
  	$ejecutar->MoveNext();
}
?>	
</table>
</div>
<br>
<table align="center" bgcolor="#FFFFFF" class="bordes">
<tr>
<td width="5%" bgcolor="#00C823">&nbsp;</td><td><b>Compras</b></td>
<td width="5%" bgcolor="#00FFFF"></td><td><b>Produccion</b></td>
<td width="5%" bgcolor="#FF00FF"></td><td><b>Entregas</b></td>
</tr>
<tr>
<td width="5%" bgcolor="Navy"></td><td><b>Ya Fueron Guardadas</b></td>
<td width="5%" bgcolor="Olive"></td><td><b>Nunca Fueron Guardadas</b></td>
</tr>
</table>
<input type="hidden" name="cant_guardar" value="<?=$i-1?>">
<script>
   if(typeof(document.all.<?=$come?>)!="undefined")
   document.all.<?=$come?>.focus();   
 </script>
</form>
</body>
</html>
