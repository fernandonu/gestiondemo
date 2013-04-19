<?
/*
Author: Broggi
Fecha: 11/08/2004

MODIFICADA POR
$Author: mari $
$Revision: 1.25 $
$Date: 2007/01/11 12:53:43 $
*/

require_once("../../config.php");
include_once("../../lib/funciones_monitoreo_cfc.php");


$id=$parametros['id_lic'] or $id=$_POST['id'];
///ACA TENGO QUE PONER TODAS LAS ACTULIZACIONES E INSERCIONES EN LAS TABLAS////////////////
if ($_POST['guardar']=="Guardar")
{$db->StartTrans();
	$g_competidores_cargados="";
 $sql = "update licitaciones.licitacion set resultados_cargados=1 where id_licitacion=$id";
 $id_rec = sql($sql) or fin_pagina("la consulta fallo en $sql");
 if ($_POST['por_defecto']==1)
    {$columnas_nuevas=$_POST['cant_columnas_nuevas'];//estas columnas son los nuevos competidores que se agregan
     $cantidad_filas=$_POST['cant_lineas'];//cantidad de renglones d ela licitacion
     $columna_inicio=3;//empieso en la columna tres ya que las otras tienen los titulos de la fila
     while ($columna_inicio<=$columnas_nuevas)
           {$indice="activo_1_1_$columna_inicio";
           	if ($_POST[$indice]==1)//este es el caso de que tengo que insertarlos en la base, si es que son nuevos, a los que ya estan en la base no los todo
           	   {$linea=2;
        	    while ($linea<$cantidad_filas)
           	          {$modo=$_POST['select_modo_1_1_'.$columna_inicio];//me fijo si los valores que puso son unitario so por el total de los productos
           	           if ($modo==-1) $modo='u';
           	           $moneda="select_moneda_1_1_$columna_inicio";
           	           $id_moneda=split("_",$_POST[$moneda]);//separo el id_moneda del simbolo de la moneda
           	           if ($id_moneda==-1) $id_moneda='$';
           	           $id_competidor=$_POST['id_competidor_'.$columna_inicio];//obtengo el id del competidor
           	           $g_competidores_cargados[]=$_POST['id_competidor_'.$columna_inicio];//para controlar CFC
           	           $id_renglon=$_POST['id_renglon_'.$linea];
           	           $comentario=$_POST['comentario_1_'.$linea.'_'.$columna_inicio];
           	           if ($modo=='u' || $_POST['cant_prod_renglon_'.$linea]==0) $monto=$_POST['monto_1_'.$linea.'_'.$columna_inicio];
           	           elseif ($modo=='t') $monto=($_POST['monto_1_'.$linea.'_'.$columna_inicio]/$_POST['cant_prod_renglon_'.$linea]);
           	           if ($monto!='')//solo inserto los renglones en los que oferto
           	              {//$db->StartTrans();
           	               $sql = "select nextval('oferta_id_seq') as id";
                           $id_rec = sql($sql) or fin_pagina();
                           //////
                           $monto=str_replace(",",".",$monto);
                           //////
                           $sql = "insert into oferta (id,id_renglon,id_competidor,id_moneda,observaciones,monto_unitario)
                                   values (".$id_rec->fields['id'].",$id_renglon,$id_competidor,".$id_moneda[1].",'$comentario',$monto)";
                           $resultado_sql = sql($sql) or fin_pagina();
                           //$db->CompleteTrans();
           	              }
               	       $linea++;
               	      }
           	   }
           	elseif ($_POST[$indice]==0)//este seria el caso en que puso un pero se equivoco y lo quiere sacar
                   {//no hago nada porque no estava en la base asi que no hay problema, pongo el if solo para que quede mas claro.
                   }
            $columna_inicio++;
           }//cierra el while
     }//aca se sierra el caso de que la licitacion no tenga ningun resultado cargado en la base de datos
 elseif ($_POST['por_defecto']==0)
        {$columnas_base=$_POST['cant_columnas_base'];//estas columnas son las que ya estan en la base de datos
         $columnas_nuevas=$_POST['cant_columnas_nuevas'];//estas columnas son los nuevos competidores que se agregan
         if ($columnas_nuevas=='') $columnas_nuevas=$_POST['cantidad_columnas'];
         $cantidad_filas=$_POST['cant_lineas'];//cantidad de renglones d ela licitacion
         $columna_inicio=3;//empieso en la columna tres ya que las otras tienen los titulos de la fila
         while ($columna_inicio<=$columnas_nuevas)
               {$indice="activo_1_1_$columna_inicio";
                if ($_POST[$indice]==1)//este es el caso de que tengo que insertarlos en la base, si es que son nuevos, a los que ya estan en la base no los toco
               	   {if ($columna_inicio<=$columnas_base)
               	       {$linea=2;
               	            While ($linea<$cantidad_filas)
               	                  {$modo=$_POST['select_modo_1_1_'.$columna_inicio];//me fijo si los valores que puso son unitarios o por el total de los productos
               	                   $moneda="select_moneda_1_1_$columna_inicio";
               	                   $id_moneda=split("_",$_POST[$moneda]);//separo el id_moneda del simbolo de la moneda
               	                   $id_competidor=$_POST['id_competidor_'.$columna_inicio];//obtengo el id del competidor
               	                   $g_competidores_cargados[]=$_POST['id_competidor_'.$columna_inicio];
               	                   $id_renglon=$_POST['id_renglon_'.$linea];
               	                   $comentario=$_POST['comentario_1_'.$linea.'_'.$columna_inicio];
               	                   $monto_oculto=$_POST['monto_oculto_1_'.$linea.'_'.$columna_inicio];
               	                   if ($modo=='u' || $_POST['cant_prod_renglon_'.$linea]==0) $monto=$_POST['monto_1_'.$linea.'_'.$columna_inicio];
               	                   elseif ($modo=='t' && $_POST['cant_prod_renglon_'.$linea]>0) $monto=($_POST['monto_1_'.$linea.'_'.$columna_inicio]/$_POST['cant_prod_renglon_'.$linea]);
               	                   if ($monto!='')//solo inserto los renglones en los que oferto
               	                      {if ($monto_oculto!='')//este es el caso en que se modifique el renglon
               	                          {///////
               	                           $monto=str_replace(",",".",$monto);
               	                           ////////
               	                           $sql = "update oferta set id_moneda=".$id_moneda[1].", observaciones='$comentario', monto_unitario=$monto
                                                  where id_renglon=$id_renglon and id_competidor=$id_competidor";
                                           $resultado_sql = sql($sql) or fin_pagina();
                                          }
               	                       elseif ($monto_oculto=='')//este es el caso que oferte en un renglon que no habia ofertado antes
               	                              {//$db->StartTrans();
               	                               $sql = "select nextval('oferta_id_seq') as id";
                                               $id_rec = sql($sql) or fin_pagina();
                                               ///////
                                               $monto=str_replace(",",".",$monto);
                                               ///////
                                               $sql = "insert into oferta (id,id_renglon,id_competidor,id_moneda,observaciones,monto_unitario)
                                                       values (".$id_rec->fields['id'].",$id_renglon,$id_competidor,".$id_moneda[1].",'$comentario',$monto)";
                                               $resultado_sql = sql($sql) or fin_pagina();
                                               //$db->CompleteTrans();
               	                              }
               	                      }
               	                   $linea++;
               	                  }
               	       }
               	   	elseif ($columna_inicio>$columnas_base)//estas columnas no estan insertadas en la base por eso las pongo
               	           {$linea=2;
               	            While ($linea<$cantidad_filas)
               	                  {$modo=$_POST['select_modo_1_1_'.$columna_inicio];//me fijo si los valores que puso son unitario so por el total de los productos
               	                   $moneda="select_moneda_1_1_$columna_inicio";
               	                   $id_moneda=split("_",$_POST[$moneda]);//separo el id_moneda del simbolo de la moneda
               	                   $id_competidor=$_POST['id_competidor_'.$columna_inicio];//obtengo el id del competidor
               	                   $g_competidores_cargados[]=$_POST['id_competidor_'.$columna_inicio];
               	                   $id_renglon=$_POST['id_renglon_'.$linea];
               	                   $comentario=$_POST['comentario_1_'.$linea.'_'.$columna_inicio];                	       	                      
               	                   if ($modo=='u' || $_POST['cant_prod_renglon_'.$linea]==0) $monto=$_POST['monto_1_'.$linea.'_'.$columna_inicio];               	       	      	                  	       	      	                  	       	      	  	
               	                   //if ($modo=='u') $monto=$_POST['monto_1_'.$linea.'_'.$columna_inicio];               	       	      	                  	       	      	                  	       	      	  	
               	                   elseif ($modo=='t' && $_POST['cant_prod_renglon_'.$linea]>0) $monto=($_POST['monto_1_'.$linea.'_'.$columna_inicio]/$_POST['cant_prod_renglon_'.$linea]);               	       	       
               	                   $comentario=$_POST['comentario_1_'.$linea.'_'.$columna_inicio];               	                                                        
               	                   if ($monto!='')//solo inserto los renglones en los que oferto
               	                      {//$db->StartTrans();
               	                       $sql = "select nextval('oferta_id_seq') as id";
                                       $id_rec = sql($sql) or fin_pagina();
                                       /////////
                                       $monto=str_replace(",",".",$monto);
                                       /////////
                                       $sql = "insert into oferta (id,id_renglon,id_competidor,id_moneda,observaciones,monto_unitario)
                                               values (".$id_rec->fields['id'].",$id_renglon,$id_competidor,".$id_moneda[1].",'$comentario',$monto)";
                                       $resultado_sql = sql($sql) or fin_pagina();
                                       //$db->CompleteTrans();
               	                      }
               	                   $linea++;
               	                  }
               	           }//hasta aca es el caso en que se inserta en la base
               	   }
               	elseif ($_POST[$indice]==0)//este es el caso de que tengo que elimnar alguna columna de la base, si la columna no esta aun en la base la ignoro y listo
               	       {$linea=2;
               	       	while ($linea<$cantidad_filas)
               	       	      {$modo="select_modo_1_1_$columna_inicio";//me fijo si los valores que puso son unitario so por el total de los productos
               	       	       $moneda="select_moneda_1_1_$columna_inicio";
               	       	       $id_moneda=split("_",$_POST[$moneda]);//separo el id_moneda del simbolo de la moneda
               	       	       $id_competidor=$_POST['id_competidor_'.$columna_inicio];//obtengo el id del competidor
               	       	       $id_renglon=$_POST['id_renglon_'.$linea];
               	       	       //$db->StartTrans();
                               $sql = "delete from oferta where id_renglon=$id_renglon and id_competidor=$id_competidor";
                               $resultado_sql = sql($sql) or fin_pagina();
                               //$db->CompleteTrans();
               	       	      $linea++;
               	       	     }
               	       }

                $columna_inicio++;         	    
               }	                 
        }
  ///////////////////////////////////////////// GABRIEL ///////////////////////////////////////////////////
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
  $db->CompleteTrans();
//////////////////////////////////////////////////////////////////////////////////////////////////////////
//avisamos que se han cargado resultados
$query="update licitacion set resultados_cargados=1 where id_licitacion=$id";
$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al avisar los resultados cargados");

//recupero el cliente
$q="select nombre from entidad left join licitacion using (id_entidad) where id_licitacion=$id";
$res=sql($q, "error al traer el nombre de la entidad") or fin_pagina();
$nombre_entidad=$res->fields['nombre'];


// para mandar mail cuando se carga un resultado
$asunto="Carga de Resultados para la Licitación Nro.".$id;
$contenido="Se cargaron resultados para la Licitación Nro. <b>".$id."</b><br> Cliente  <b>".$nombre_entidad."</b>";
//
//$para="broggi@coradir.com.ar";
enviar_mail_html(to_group(array("resultados_lic")), $asunto, $contenido, $adjunto, $path, $tipo, $adj);
//echo "se envia el siguiente mail -- <br>".$asunto." <br>".$contenido;
//////////////////////////////////////////////////////////////////////////////////////

  $link=encode_link("lic_ver_res2.php",array("keyword"=>$id,"pag_ant"=>"lic_cargar_res_nuevo.php","pagina_volver"=>"licitaciones_view.php"));

  header("location:$link") or die("error de location");
}//del if de "Guardar"
///////////////////////////////////////////////////////////////////////////////////////////
//Despues hago nuevamente las consultas para que cuando se cargue al pagina nuevamente/////
//no pierda los cambios que hice///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////


$sql_1 = "select renglon.* from renglon where id_licitacion=$id order by codigo_renglon";
         //todos los renglones de una licitacion
$sql_2 = "select distinct id_competidor, competidores.nombre, moneda.simbolo, oferta.id_moneda
          from licitaciones.licitacion
          join licitaciones.renglon using(id_licitacion)
          left join licitaciones.oferta using(id_renglon)
          left join licitaciones.moneda on(oferta.id_moneda=moneda.id_moneda)
          left join licitaciones.competidores using(id_competidor)
          where licitaciones.licitacion.id_licitacion=$id";
         //competidores que ofertaron en todos o algunos de los renglones de la licitacion
         //y con que moneda participaron
$sql_3 = "select id_renglon, id_competidor, oferta.observaciones, codigo_renglon, oferta.monto_unitario
          from licitaciones.renglon join licitaciones.licitacion using(id_licitacion)
          left join licitaciones.oferta using(id_renglon)
          left join licitaciones.competidores using(id_competidor)
          where licitacion.id_licitacion=$id order by codigo_renglon";
         //competidores que ofertaron en algunos o todos los renglones de la licitacion
         //y en que renglones participaron
$sql_4 = "select oferta.*
          from licitaciones.licitacion
          join licitaciones.renglon using(id_licitacion)
          join licitaciones.oferta using(id_renglon)
          where licitaciones.licitacion.id_licitacion=$id"  ;
         //si los renglones tienen ofertas o no

$sql_moneda="select * from moneda";
$resultado_sql_moneda=sql($sql_moneda) or fin_pagina();

$sql_licitacion="select * from licitacion join entidad using(id_entidad) where id_licitacion=$id";
$resultado_sql_licitacion=sql($sql_licitacion) or fin_pagina();

echo $html_header;
?>

<script languaje="javascript" src="../../lib/funciones.js"></script>
<script languaje="javascript" src="../../lib/fns.js"></script>
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
<script language="javascript">
var fila="";
var columna="";
var tabla="1";
var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido
var paso_id_competidor=new Array();
//inserto competidores
var id_competidor="";//le asigno el id del competidor desde la ventana carga_nuevo_competidor.php
var texto_competidor="";//le asigno el nombre del competidor desde la ventana carga_nuevo_competidor.php

//change_image BY GACZ
//cambia las imagenes dependiendo el estado de la columna (oculta/visible)
//@colprov indica el numero de la colmna
function change_image(columna)
{
		oimg=eval("document.all.imagen_"+columna);//objeto tipo IMG
		if (typeof oimg!='undefined')
		{
			//si por defecto esta visible o esta visible
			if (typeof oimg.show=='undefined' || oimg.show)
			{
				oimg.show=0;
				oimg.src=img_cont;
				oimg.title='Mostrar Proveedor';
			}
			else
			{
				oimg.show=1;
				oimg.src=img_ext;
				oimg.title='Ocultar Proveedor';
			}
			//alert ("Estoy en cambiar imagen");			
			hide_col(oimg.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode,columna,oimg.show);
	 }
}//function change_imagen

//HIDE_COL BY GACZ
//funcion que oculta o muestra una columna de una tabla
//@otabla es el objeto tabla
//@colindex es el nro de la columna
//@show si es 0 (cero) oculta, sino hace visible
function hide_col(otabla,columna,show)
{
	//alert ("estoy en hide_col");
	document.getElementById('columna_1_0_'+columna).childNodes[0].rows[1].cells[0].style.display=(show)?'block':'none';
	document.getElementById('columna_1_1_'+columna).childNodes[0].style.display=(show)?'block':'none';
	
	for (j=2; j < cant_lineas; j++ )
	    {//alert ("cantidad filas: "+cant_lineas);
	     //alert ("ocultando fila: "+j);
		 show_hide(document.getElementById('columna_1_'+j+'_'+columna),show);
	    } 
}//function hide_col


//SHOW_HIDE BY GACZ
//funcion que oculta o muestra los objetos dentro de un td
//@otd es un objeto tipo td
//@show si es 0 (cero) oculta, sino hace visible
function show_hide(otd,show)
{
	display=(show)?'inline':'none';
	for (i=0; i < otd.childNodes.length; i++ )
	{
		if(typeof otd.childNodes[i].tagName!='undefined')
			otd.childNodes[i].style.display=display;
	}
}//function show_hide


//funciones para agregar un competidor, que es lo mismo que agregar una columna en la tabla.
function cargar_competidor(tabla)
{var ma_bod;
 table = document.getElementById("tabla_"+tabla);
 rows=table.rows.length;//Obtengo la cantidad de filas existentes en la tabla.
 var col2=col=table.rows[0].cells.length;//Obtengo la cantidad de columnas existentes en la tabla.
 mycurrent_cell=document.createElement("TD");
 mycurrent_cell.setAttribute("id","columna_"+tabla+"_0_"+col);
 mycurrent_cell.setAttribute("title",texto_competidor);
 mycurrent_cell.vAlign="top";
 mycurrent_cell.ondblclick=function (){change_image(col2)};
 mycurrent_cell.innerHTML=
	 "<table width=100% border=0 cellpadding=0 cellspacing=0 align=center>"+
	"<tr>"+
		"<td><img id='imagen_"+col+"' src="+img_ext+" border=0 title='Ocultar Proveedor' align='left' style='cursor:hand;' onclick='change_image("+col+");' /></td>"+
	"</tr>"+
	"<tr>"+
		"<td align=center ><b id=mo>"+texto_competidor+"</b>"+
		"<div align='center'>"+
		"<input type='button' name='boton_borrar_"+tabla+"_0_"+col+"' onclick='borrar_proveedor(this);' value='B' style='cursor:hand;'>"+
		"</div>"+
		"</td>"+
	"</tr>"+
	"</table>";
 table.rows[0].appendChild(mycurrent_cell);
 table.rows[0].cells[table.rows[0].cells.length-1].style.fontWeight="bold";
 mycurrent_cell=document.createElement("TD");
 mycurrent_cell.setAttribute("id","columna_"+tabla+"_1_"+col);
 mycurrent_cell.setAttribute("name","columna_"+tabla+"_1_"+col);
 prueba=(col+1);
 columnas_nuevas=col;
 mycurrent_cell.innerHTML="<table><tr><td>Columna Nro: <b>"+col+"</b></td></tr>"+
                          "<tr><td align='center'>"+
                          "<input name='activo_1_1_"+col+"' value=1 type='hidden'>"+
                          "<input name='id_competidor_"+col+"' value='"+id_competidor+"' type='hidden'>"+
                          "<input name='cant_columnas_nuevas' value='"+col+"' type='hidden'>"+
                          "<select name='select_moneda_1_1_"+col+"' onchange='selecciona_moneda("+col+")'><option selected value=-1>Tipo Moneda</option>"+
                          <?$resultado_sql_moneda->MoveFirst();
                            while (!$resultado_sql_moneda->EOF)
                           {
                          ?>
                          "<option value='<?=$resultado_sql_moneda->fields['simbolo']?>_<?=$resultado_sql_moneda->fields['id_moneda']?>'><?=$resultado_sql_moneda->fields['nombre']?></option>"+
                          <?
                           $resultado_sql_moneda->MoveNext();
                           }
                          ?>

                          "</select></td></tr>"+
                          "<tr><td>"+
                             "<select name='select_modo_1_1_"+col+"' >"+
                              "<option selected value='-1'>Precio:</option>"+
                              "<option value='u'>Unitario</option>"+
                              "<option value='t'>Total</option>"+
                             "</select></td></tr></table>";
 table.rows[1].appendChild(mycurrent_cell);
//////////////////////Pongo todas las celdas a partir de la segunda fila////////////////////
i=2;
while(i<rows)
   {mycurrent_cell=document.createElement("TD");
    mycurrent_cell.setAttribute("id","columna_"+tabla+"_"+i+"_"+col);
    mycurrent_cell.setAttribute("name","columna_"+tabla+"_"+i+"_"+col);
    mycurrent_cell.innerHTML="<table ><tr><td>"+
                             "<input type='text' name='simbolo_moneda_1_"+i+"_"+col+"' size='3' value='' class='text_2'></td>"+
                             "<td><input type='text' name='monto_1_"+i+"_"+col+"' size='13' value='' onkeypress='return filtrar_teclas(event,\".0123456789\");'></td></tr>"+
                             "<input type='hidden' name='monto_oculto_1_"+i+"_"+col+"' size='13' value=''>"+
                             "<tr><td colspan='2'><textarea name='comentario_1_"+i+"_"+col+"' rows='2' cols='20'></textarea></td></tr>"+
                             "</table>";
  	table.rows[i].appendChild(mycurrent_cell);
  	i++;
   }
///////////////////////////////////////////////////////////////////////////////////////////
}//function cargar_competidor

function borrar_columna(nro,objeto)
{var o;
 o=objeto;
 divi=o.name.split("_");
 tabla=nro;
 col=divi[4];
 
 table = document.getElementById("tabla_"+tabla);
 filas=table.rows.length;
 celda=eval('document.all.columna_'+tabla+'_'+0+'_'+col);
 celda.style.display='none';
 eval('document.all.activo_1_1_'+col+'.value=0');//a activo le asigno cero para saber que esa columna fue eliminada
 for(i=1;i<filas;i++)
 {celda=eval('document.all.columna_'+tabla+'_'+i+'_'+col);
  celda.style.display='none';
 }
}//function borrar_columna

function borrar_proveedor(objeto)
{if(typeof(objeto)!="undefined")
  o=objeto;
 else
  o=this;
  borrar_columna(1,o)
}//function borrar_proveedor

function control_boton()
{if (window.event.keyCode==13)
 {document.all.valor_boton.value='Guardar';
  document.all.form.submit();
 }
 if (window.event.keyCode==27)
 {document.all.valor_boton.value='Volver';
  document.all.form.submit();
 }
}//function control_boton

function selecciona_moneda(Pcolumna)
{var contador=2;
 linea=eval("document.all.select_moneda_1_1_"+Pcolumna);
 todo_junto=linea.value;
 simbolo_solo=todo_junto.split('_');
 if (simbolo_solo[0]!=-1)
    {while (contador<cant_lineas)
           {eval("document.lic_cargar_res_nuevo.simbolo_moneda_1_"+contador+"_"+Pcolumna+".value='"+simbolo_solo[0]+"'");
            //eval("document.lic_cargar_res_nuevo.monto_1_"+contador+"_"+Pcolumna+".value=''");
            contador++;
           }
     //alert("Recuerde Ingresar los nuevos Valores!!!!!");
    }
}//function selecciona_moneda

id_renglon="";
codigo_renglon="";
cantidad="";
titulo="";
paso_fila="";

function insRow()
{ 
 var columna=3;
 var v;
 var simbolo;
 var temporal;
 var can_columna=paso_id_competidor.length;//la cantidad de competidores que tengo en la tabla
 fila=paso_fila;
 //alert ("estoy en new fila. la fila es: "+fila);
 table = document.getElementById("tabla_1");
 rows=table.rows.length;//Obtengo la cantidad de filas existentes en la tabla.
 var x=document.getElementById('tabla_1').insertRow(fila+1);
 x.bgColor="<?=$bgcolor_out?>";//le pongo el color de fondo a la fila
 var y=x.insertCell(0);
 var w=x.insertCell(1);
 var z=x.insertCell(2);
 y.innerHTML="<table><tr><td align='center'>"+codigo_renglon+"</td></tr><tr><td><input name='agrega_fila' type='button' title='El nuevo renglon se ingresara debajo de este' value='Agregar Renglon' onclick=\"paso_fila=this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.rowIndex; window.open('<?=encode_link("lic_res_nuevo_renglon_nuevo.php",array('id_licitacion'=>$id))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=0,left=125,top=100,width=500,height=325')\" style='font-size : 10px; font-weight : bold; color : black; border : solid 1px black; cursor: hand'></td></tr></table>"+
             "<input name='id_renglon_"+rows+"' type='hidden' value='"+id_renglon+"'>"+
             "<input name='cant_prod_renglon_"+rows+"' type='hidden' value='"+cantidad+"'>";
 w.innerHTML=cantidad;
 z.innerHTML=titulo;
 while (columna<can_columna)
       {v=x.insertCell(columna);
        v.id="columna_1_"+rows+"_"+columna;
        v.name="columna_1_"+rows+"_"+columna;
        temporal=eval("document.all.simbolo_moneda_1_"+(rows-1)+"_"+columna+"");
        simbolo=temporal.value;
        v.innerHTML="<table ><tr><td>"+
                    "<input type='text' name='simbolo_moneda_1_"+rows+"_"+columna+"' size='3' value='"+simbolo+"' class='text_2'></td>"+
                    "<td><input type='text' name='monto_1_"+rows+"_"+columna+"' size='13' value='' onkeypress='return filtrar_teclas(event,\".0123456789\");'></td></tr>"+
                    "<input type='hidden' name='monto_oculto_1_"+rows+"_"+columna+"' size='13' value=''>"+
                    "<tr><td colspan='2'><textarea name='comentario_1_"+rows+"_"+columna+"' rows='2' cols='20'></textarea></td></tr>"+
                    "</table>"
        columna++;
       }

 cant_lineas++;
 document.all.cant_lineas.value=cant_lineas;
}//function insrows


function controles()
{col_nuevas=columnas_nuevas;
 col_base=3
 while (col_base<=col_nuevas)
       {
       	var ochk=eval("document.all.activo_1_1_"+col_base);
       	moneda=eval("document.all.select_moneda_1_1_"+col_base);
       	if (ochk.value!=0)
       	{
       	if (moneda.value==-1)
       	   {alert("Se olvido de seleccionar el tipo\nde moneda en la columna "+col_base);
       	    return false;
       	   }
       	tipo_monto_control=eval("document.all.select_modo_1_1_"+col_base);//para sber si selecciono unitario o total o ninguno de los dos
       	if (tipo_monto_control.value==-1)
       	   {alert("Se olvido de seleccionar si el precio\n es unitario o total en la columna "+col_base);
       	    return false;
       	   }
       	}
       	col_base++;
       }//del while
}//de la funcion controles
</script>



<form name="lic_cargar_res_nuevo" method="post" action="lic_cargar_res_nuevo.php">
<br>
<input name="id_lic" type="hidden" value=<?=$id?>><!--esto es por que lo necesito en el formulario lic_res_nuevo_renglon-->
<table align="center" width="90%" class="bordes">
 <tr id=mo><td align="center"><font size="3"><b>Licitacion</b></font></td></tr>
 <tr bgcolor="<?=$bgcolor_out?>">
  <td><font size="2"><b>ID. Licitacion:</b>&nbsp;<?=$resultado_sql_licitacion->fields['id_licitacion']?></font></td>
 </tr>
 <tr bgcolor="<?=$bgcolor_out?>">
  <td><font size="2"><b>Entidad:</b>&nbsp;<?=$resultado_sql_licitacion->fields['nombre']?></font></td>
 </tr>
</table>
<br>
<table align="center" width="100%">
  <tr>
   <td align="center">
    <input name="agregar" type="button" value="Agregar competidor" style="cursor:hand" onclick='window.open("<?=encode_link("carga_nuevo_competidor.php",array());?>","","resizable=1,scrollbars=yes,width=700,height=100,left=20,top=50,status=yes");'>
   </td>
  </tr>
 </table>

<?$columna=3;
  $fila=0;
  $resultado_consulta_1 = sql($sql_1) or fin_pagina();
  $resultado_consulta_2 = sql($sql_2) or fin_pagina();
  $resultado_consulta_3 = sql($sql_3) or fin_pagina();
  $resultado_consulta_4 = sql($sql_4) or fin_pagina();
  $link2=encode_link("lic_res_nuevo_renglon_nuevo.php",array('id_licitacion'=>$id));
if ($resultado_consulta_3->recordcount()==0 && $resultado_consulta_1->recordcount()==0)
{echo "<center>";
 echo "<font size='4'><b>No hay renglones para esta licitacion</b></font>";
 echo "</center>";
}
else
{if ($resultado_consulta_4->recordcount()==0)
{
?>
<input name="por_defecto" type="hidden" value=1>
<table id="tabla_1" align="center" class="bordes" cellpadding="2" cellspacing="2" >
 <tr id=mo>
  <td>Renglon</td>
  <td>Cantidad</td>
  <td>Titulo</td>
  <td valign="top" id='columna_1_<?=$fila?>_<?=$columna?>' name='columna_1_<?=$fila?>_<?=$columna?>' ondblclick="change_image(<?=$columna ?>)" title="Coradir S.A">
  <table width="100%" border=0 cellpadding=0 cellspacing=0 align="center">
	<tr>
		<td><img id="imagen_<?=$columna?>" src="<?=$img_ext?>" border=0 title="Ocultar Proveedor" align="left" style="cursor:hand;" onclick="change_image(<?=$columna?>);" ></td>
	</tr>
	<tr>
		<td align="center">
		<b  id=mo>Coradir S.A</b>
		<div align="center">
		<input type="button" id="boton_borrar_1_0_<?=$columna?>" name="boton_borrar_1_0_<?=$columna?>" value="B" onclick='borrar_proveedor(this)'>
		</div>
		</td>
	</tr>
	</table>
  <script>
   paso_id_competidor[3]=1;
  </script>
  <input type="hidden" name="id_competidor_<?=$columna?>" value="1">
  <input type="hidden" name="activo_1_1_<?=$columna?>" value="1">
  <input type="hidden" name="cant_columnas_nuevas" value="<?=$columna?>">
  <input type="hidden" name="cant_columnas_base" value="<?=$columna?>">
  <script>
   columnas_nuevas=<?=$columna?>;
   columnas_base=<?=$columna?>;
  </script>
	</td>
 </tr>
 <?$fila++;?>
 <tr id=ma>
  <td height="20px">&nbsp;</td>
  <td >&nbsp;</td>
  <td >&nbsp;</td>
  <td id='columna_1_<?=$fila?>_<?=$columna?>' name='columna_1_<?=$fila?>_<?=$columna?>'>
   <table>
    <tr>
   	 <td>Columna Nro: <b><?=$columna?></b></td>
   	</tr>
    <tr>
     <td>
     <select name="select_moneda_1_1_<?=$columna?>" onchange='selecciona_moneda(<?=$columna?>)'>
      <option selected value='-1'>Tipo Moneda</option>;
      <?$resultado_sql_moneda->MoveFirst();
        while (!$resultado_sql_moneda->EOF)
              {if ($resultado_consulta_2->fields['id_moneda']==$resultado_sql_moneda->fields['id_moneda']) {$selected="selected";}
               else {$selected=" ";}    
               echo "<option ".$selected." value='".$resultado_sql_moneda->fields['simbolo']."_".$resultado_sql_moneda->fields['id_moneda']."'>".$resultado_sql_moneda->fields['nombre']."</option>";
               $resultado_sql_moneda->MoveNext();              
              }
        echo "</select>";
      ?> 
     </td >
    </tr>
    <tr> 
     <td>     
      <select name="select_modo_1_1_<?=$columna?>" >
       <option selected value="-1">Precio:</option>
       <option value="u">Unitario</option>
       <option value="t">Total</option>
      </select>
     </td>
    </tr>
   </table>
  </td>    
 </tr>
<? 
 $i=2;
 $fila++;
 while (!$resultado_consulta_1->EOF)
 {
?>
 <tr bgcolor="<?=$bgcolor_out?>" onclick="if(window.event.srcElement.tagName!='INPUT') alternar_color(this,'#a6c2fc')">
  <!--<td><?=$resultado_consulta_1->fields['codigo_renglon']?></td>-->
  <?
  echo "<td><table><tr><td align='center'>".$resultado_consulta_1->fields['codigo_renglon']."</td></tr><tr><td><input name='agrega_fila_$fila' type='button' title='El nuevo renglon se ingresara debajo de este' value='Agregar Renglon' onclick=\"paso_fila=this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.rowIndex; window.open('".encode_link("lic_res_nuevo_renglon_nuevo.php",array('id_licitacion'=>$id))."','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=0,left=125,top=100,width=500,height=325')\" style='font-size : 10px; font-weight : bold; color : black; border : solid 1px black;
 cursor: hand'></td></tr></table></td>";//con parentNode estoy accediendo a las propiedades del padre, en este caso para obtener el numero de fila donde esat el boton que llama al otro formulario
  ?>
  <td><?=$resultado_consulta_1->fields['cantidad']?></td>
  <td><?=$resultado_consulta_1->fields['titulo']?></td>
  <input name="id_renglon_<?=$i?>" type="hidden" value="<?=$resultado_consulta_1->fields['id_renglon']?>">
  <input name="cant_prod_renglon_<?=$i?>" type="hidden" value="<?=$resultado_consulta_1->fields['cantidad']?>">
  <td id='columna_1_<?=$fila?>_<?=$columna?>' name='columna_1_<?=$fila?>_<?=$columna?>'>
   <table>
    <tr>     
     <td><input name="simbolo_moneda_1_<?=$i?>_<?=$columna?>" type="text" size="2" class="text_2" size="3"> 
     <td><input name="monto_1_<?=$i?>_<?=$columna?>" size="13" type="text" value="" onkeypress="return filtrar_teclas(event,'.0123456789');"></td>
     <input name="monto_oculto_1_<?=$i?>_<?=$columna?>" size="13" type="hidden" value="">
    </tr>
    <tr>
     <td colspan="2"><textarea name="comentario_1_<?=$i?>_<?=$columna?>" rows="2" cols="20"></textarea></td>
    </tr>
   </table>
  </td>
 </tr>
<?
 $i++;
 $fila++;
 $resultado_consulta_1->MoveNext();
 }
 echo "</table>";  
 ?> 
<script>
var cant_lineas=<?=$i?>;//lo uso en la funcion selecciona moneda para saber la cantidad de renglones en la licitación
</script>
<?
} 
 else 
 {$i=2;
?>
 <table id="tabla_1" align="center" class="bordes" cellpadding="2" cellspacing="2" >
 <input name="por_defecto" type="hidden" value=0>
 <tr id=mo > 
  <td ><b>Renglon</b></td> 
  <td><b>Cantidad</b></td>
  <td><b>Titulo</b></td> 
<?
 while (!$resultado_consulta_2->EOF)
  {if ($resultado_consulta_2->fields['nombre']!='')
   {echo "";
?>
<td valign="top" id='columna_1_<?=$fila?>_<?=$columna?>' name='columna_1_<?=$fila?>_<?=$columna?>' ondblclick="change_image(<?=$columna ?>)" title="<?=$resultado_consulta_2->fields['nombre'] ?>">
  <table width="100%" border=0 cellpadding=0 cellspacing=0 align="center">
	<tr>
		<td><img id="imagen_<?=$columna?>" src="<?=$img_ext?>" border=0 title="Ocultar Proveedor" align="left" style="cursor:hand;" onclick="change_image(<?=$columna?>);" ></td>
	</tr>
	<tr>
		<td align="center">
		<b id=mo><?=$resultado_consulta_2->fields['nombre'] ?></b>
		<div align="center">
		<input type="button" id="boton_borrar_1_<?=$fila?>_<?=$columna?>" name="boton_borrar_1_<?=$fila?>_<?=$columna?>" value="B" onclick='borrar_proveedor(this)'>
		</div>
		</td>
	</tr>
	</table>
<?
    echo "<input type='hidden' name='id_competidor_$columna' value='".$resultado_consulta_2->fields['id_competidor']."'>";
    echo "<input name='cant_columnas_base' type='hidden' value='$columna'>";
    ?>
    <script>
     columnas_nuevas=<?=$columna?>;
     columnas_base=<?=$columna?>;
    </script>
    <?    
    echo "<input name='activo_1_1_$columna' value=1 type='hidden'>";
    ?>
    <script>
     paso_id_competidor[<?=$columna?>]=<?=$resultado_consulta_2->fields['id_competidor']?>;
    </script>    
    <?
   } 
    $columna++;
   $resultado_consulta_2->MoveNext();
  }	 
 echo "</td>"; 
 echo "</tr>"; 
 echo "<tr id=ma><td colspan='3' height='20px'></td>";
 $resultado_consulta_2->MoveFirst();
 $fila++;
 $columna=3;
 while (!$resultado_consulta_2->EOF)
  {if ($resultado_consulta_2->fields['nombre']!='')
   {echo "<input name='cantidad_columnas' type='hidden' value='$columna'>";    
   	echo "<td id='columna_1_"."$fila"."_"."$columna' name='columna_1_"."$fila"."_"."$columna'>
           <table >
   	        <tr>
   	         <td>Columna Nro: <b>$columna</b></td>
   	        </tr>
            <tr>             
             <td>";              
              echo "<select name='select_moneda_1_$fila"."_"."$columna' onchange='selecciona_moneda($columna)'>
               <option selected value='-1'>Tipo Moneda</option>";
              $resultado_sql_moneda->MoveFirst();
              while (!$resultado_sql_moneda->EOF)
                    {if ($resultado_consulta_2->fields['id_moneda']==$resultado_sql_moneda->fields['id_moneda']) {$selected="selected";}
                     else {$selected=" ";}    
                     echo "<option ".$selected." value='".$resultado_sql_moneda->fields['simbolo']."_".$resultado_sql_moneda->fields['id_moneda']."'>".$resultado_sql_moneda->fields['nombre']."</option>";
                     $resultado_sql_moneda->MoveNext();              
                    }
              echo "</select>
             </td>
            </tr>
            <tr>
             <td>
              <select name='select_modo_1_$fila"."_"."$columna' >
               <option value='-1'>Precio:</option>
               <option selected value='u'>Unitario</option>
               <option value='t'>Total</option>
              </select>
             </td>
            </tr>
           </table>
        </td>\n";
   }
   $resultado_consulta_2->MoveNext();
   $columna++;
  }
  $resultado_consulta_2->MoveFirst();
  $columna=3;
  $fila++;
  echo "</tr>";
  $entro=0;

  while (!$resultado_consulta_1->EOF)
   {echo "<tr bgcolor=$bgcolor_out onclick=\"if(window.event.srcElement.tagName!='INPUT') alternar_color(this,'#a6c2fc')\" >";
    echo "<td><table><tr><td align='center'>".$resultado_consulta_1->fields['codigo_renglon']."</td></tr><tr><td><input name='agrega_fila_$fila' type='button' title='El nuevo renglon se ingresara debajo de este' value='Agregar Renglon' onclick=\"paso_fila=this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.rowIndex; window.open('".encode_link("lic_res_nuevo_renglon_nuevo.php",array('id_licitacion'=>$id))."','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=0,left=125,top=100,width=500,height=325')\" style='font-size : 10px; font-weight : bold; color : black; border : solid 1px black;
 cursor: hand'></td></tr></table></td>";//con parentNode estoy accediendo a las propiedades del padre, en este caso para obtener el numero de fila donde esat el boton que llama al otro formulario
    echo "<td>".$resultado_consulta_1->fields['cantidad']."</td>";
    echo "<td>".$resultado_consulta_1->fields['titulo']."</td>";
    echo "<input name='id_renglon_$fila' type='hidden' value='".$resultado_consulta_1->fields['id_renglon']."'>";
    echo "<input name='cant_prod_renglon_$fila' type='hidden' value='".$resultado_consulta_1->fields['cantidad']."'>";
    while (!$resultado_consulta_2->EOF)
      {if ($resultado_consulta_2->fields['id_competidor']!='')
       {while (!$resultado_consulta_3->EOF)
        {if ($resultado_consulta_2->fields['id_competidor']==$resultado_consulta_3->fields['id_competidor'] && $resultado_consulta_1->fields['id_renglon']==$resultado_consulta_3->fields['id_renglon'])
      	   {echo "<td id='columna_1_"."$fila"."_"."$columna' name='columna_1_"."$fila"."_"."$columna'>";
      	   	echo "<table >";
      	    echo "<tr> ";
      	   	echo "<td><input name='simbolo_moneda_1_$fila"."_"."$columna' size='3' class='text_2' value='".$resultado_consulta_2->fields['simbolo']."'> </td>";
      	    echo "<td><input name='monto_1_$fila"."_"."$columna' size='12' value='".number_format($resultado_consulta_3->fields['monto_unitario'],2,".","")."' onkeypress='return filtrar_teclas(event,\".0123456789\");'></td>";
      	    echo "<input name='monto_oculto_1_$fila"."_"."$columna' size='12' type='hidden' value='".$resultado_consulta_3->fields['monto_unitario']."'>";
      	    echo "<tr><td colspan='2'><textarea name='comentario_1_$fila"."_"."$columna' rows='2' cols=20'>".$resultado_consulta_3->fields['observaciones']."</textarea></td></tr>";
      	   	echo "</tr> \n";
      	   	echo "</table>";
      	   	echo "</td>";
      	    $entro=1;
      	   }
      	 $resultado_consulta_3->MoveNext();
        }//del while (!$resultado_consulta_3->EOF)

        if ($entro==0)
         {echo "<td id='columna_1_"."$fila"."_"."$columna' name='columna_1_"."$fila"."_"."$columna'>";
          echo "<table >";
      	  echo "<tr >";
          echo "<td ><input name='simbolo_moneda_1_$fila"."_"."$columna' size='3' class='text_2' value='".$resultado_consulta_2->fields['simbolo']."'> </td>";
          echo "<td ><input name='monto_1_$fila"."_"."$columna' size='12' value='' onkeypress='return filtrar_teclas(event,\".0123456789\");'></td>";
          echo "<input type='hidden' name='monto_oculto_1_$fila"."_"."$columna' size='12' value=''>";
          echo "</tr> \n";
          echo "<tr><td colspan='2'><textarea name='comentario_1_$fila"."_"."$columna' rows='2' cols=20'></textarea></td></tr>";
          echo "</table>";
          echo "</td>";
         }
      $entro=0;
      $resultado_consulta_2->MoveNext();
      $columna++;
      $resultado_consulta_3->MoveFirst();

      }
     else {$resultado_consulta_2->MoveNext();
            $columna++;
           $resultado_consulta_3->MoveFirst();
          }
      }
    echo "</tr>";

    $resultado_consulta_1->MoveNext();
    $columna=3;
    $i++;
    $fila++;
    $resultado_consulta_2->MoveFirst();
   }  	
   }   
 ?>
  
 <input name="cant_lineas" type="hidden" value="">
 </table>
 <script>
 var cant_lineas=<?=$i?>;
 document.all.cant_lineas.value=cant_lineas;
 //alert(document.all.cant_lineas.value);
 </script> 
 <? 
}

if (!($resultado_consulta_3->recordcount()==0 && $resultado_consulta_1->recordcount()==0))
{
?>
 <input name="id" type="hidden" value="<?=$id?>"> 
 <table align="center" width="100%">
  <tr >
   <td align="center">
    <input name="guardar" type="submit" value="Guardar" onclick="return controles()">
   </td> 
   <!--<td align="center">
    <input name="submit" type="submit" value="Subir" >
   </td> -->
   <!--<td align="center">
    <input name="prueba" type="button" value="Agregar" onclick="insRow()">
   </td>-->
  </tr >
 </table >
<?
} 
fin_pagina();

?>