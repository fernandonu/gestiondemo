<?php
require_once("../../config.php");
require_once("./funciones.php");
$nro_licitacion=$parametros['licitacion'];
$id_renglon=$parametros['renglon'];
$pagina_viene=$parametros['pagina'];
$ult_ok=$parametros['ult_ok'];
$moneda = $parametros['moneda'];
$valor_moneda = $parametros['valor_moneda'];

//********************************************************************
//DECLARACION DE LINKS
//*********************************************************************
$link= encode_link("enviar_mensajes.php",array("renglon" => $id_renglon,
                                          "licitacion" => $nro_licitacion,"ult_ok"=>$ult_ok,
                                          "pagina" =>$pagina_viene));
$link2= encode_link("guardar_descripcion.php",array("renglon" => $id_renglon,
                                          "licitacion" => $nro_licitacion));
$link3= encode_link($html_root."/modulos/licitaciones/realizar_oferta.php",array("renglon" => $id_renglon,
                                          "licitacion" => $nro_licitacion));
$link4=encode_link("agregar_producto.php",array("renglon" => $id_renglon,
                                          "licitacion" => $nro_licitacion,
                                           "moneda"=>$moneda,
                                           "valor_moneda"=>$valor_moneda));
$link5=encode_link("realizar_oferta.php",array("renglon" => $id_renglon,
                                           "licitacion" => $nro_licitacion ));

//$ref = encode_link("licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$nro_licitacion));
$ref = encode_link($html_root."/index.php",array("menu"=>"licitaciones_view","extra"=>array("cmd1"=>"detalle","ID"=>$nro_licitacion)));
//*****************************************************************
//   FIN DE DECLARACION DE LINKS
//******************************************************************

if (strcmp($_POST['enviar'],"Enviar mensaje")==0) { //envio el mensaje
                                          $usuario_destino = $_POST['usuario_destino'];
                                          $query= "Select * from usuarios where nombre = '$usuario_destino'";
                                          $resultado=$db->execute($query);
                                          $para=$resultado->fields['login'];
                                          $hora=$_POST['hora'];
                                          $minutos=$_POST['minutos'];
                                          $hora_vencimiento=$_POST['hora'];
                                          $fecha_vencimiento = $_POST['f_entrega'];
                                          if ($pagina_viene=='agregar_producto') {
                                              $id_renglon=$parametros['renglon'];
                                              $query = "Select * from renglon where id_renglon = $id_renglon " ;
                                              $resultado = $db->execute($query);
                                              $control_renglon=$resultado->fields['codigo_renglon'];
                                              $mensaje = "Licitacion: $nro_licitacion  Renglon: $control_renglon <b><a target=_top href=$ref> ir </a></b>";;
                                              $tipo1= 'LIC';
                                              $tipo2 = 'LCR';
                                              genera_descripcion_renglon($parametros['renglon']);
                                              enviar_mensaje($hora_vencimiento,$fecha_vencimiento,$mensaje,$tipo1,$tipo2,$para);
                                              header("Location:$link3");
                                                      }
                                               elseif($pagina_viene=='vista_previa')
										{   $usuario_destino = $_POST['usuario_destino'];
                                          $query= "Select * from usuarios where nombre = '$usuario_destino'";
                                          $resultado=$db->execute($query);
                                          $para=$resultado->fields['login'];
                                          $hora=$_POST['hora'];
                                          $minutos=$_POST['minutos'];
                                          $hora_vencimiento=$_POST['hora'];
                                          $fecha_vencimiento = $_POST['f_entrega'];
                                              $id_renglon=$parametros['renglon'];
                                              $query = "Select * from renglon where id_renglon = $id_renglon " ;
                                              $resultado = $db->execute($query);
                                              $control_renglon=$resultado->fields['codigo_renglon'];
                                              $mensaje = "Licitacion: $nro_licitacion  Renglon: $control_renglon <b><a target=_top href=$ref> ir </a></b>";;
                                              $tipo1= 'LIC';
                                              $tipo2 = 'NAS';//nuevo archivo subido
                                              genera_descripcion_renglon($id_renglon);
                                              enviar_mensaje($hora_vencimiento,$fecha_vencimiento,$mensaje,$tipo1,$tipo2,$para);
                                              header("Location:$link3");
                                         }
                                          elseif($pagina_viene=='vista_previa_guarda')
										{   $usuario_destino = $_POST['usuario_destino'];
                                          $query= "Select * from usuarios where nombre = '$usuario_destino'";
                                          $resultado=$db->execute($query);
                                          $para=$resultado->fields['login'];
                                          $hora=$_POST['hora'];
                                          $minutos=$_POST['minutos'];
                                          $hora_vencimiento=$_POST['hora'];
                                          $fecha_vencimiento = $_POST['f_entrega'];
                                              $id_renglon=$parametros['renglon'];
                                              $query = "Select * from renglon where id_renglon = $id_renglon " ;
                                              $resultado = $db->execute($query);
                                              $control_renglon=$resultado->fields['codigo_renglon'];
                                              $mensaje = "El renglon $control_renglon de la licitacion $nro_licitacion, esta listo para la revision Nº 1 <b><a target=_top href=$ref> ir </a></b>";;
                                              $tipo1= 'LIC';
                                              $tipo2 = 'RPO'; //renglon en espera de primer OK
                                              //genera_descripcion_renglon($id_renglon);
                                              enviar_mensaje($hora_vencimiento,$fecha_vencimiento,$mensaje,$tipo1,$tipo2,$para);
                                              header("Location:$link3");
                                         }
										elseif($pagina_viene=='vista_previa_ok')
										{   $usuario_destino = $_POST['usuario_destino'];
                                          $query= "Select * from usuarios where nombre = '$usuario_destino'";
                                          $resultado=$db->execute($query);
                                          $para=$resultado->fields['login'];
                                          $hora=$_POST['hora'];
                                          $minutos=$_POST['minutos'];
                                          $hora_vencimiento=$_POST['hora'];
                                          $fecha_vencimiento = $_POST['f_entrega'];
                                              $id_renglon=$parametros['renglon'];
                                              $query = "Select * from renglon where id_renglon = $id_renglon " ;
                                              $resultado = $db->execute($query);
                                              $control_renglon=$resultado->fields['codigo_renglon'];
                                              $o=$ult_ok+1;
                                              $mensaje = "El renglon $control_renglon de la licitacion $nro_licitacion, esta listo para la revision Nº $o <b><a target=_top href=$ref> ir </a></b>";
                                              $tipo1= 'LIC';
                                              $tipo2 = 'RSO'; //renglon en espera de primer OK
                                              genera_descripcion_renglon($id_renglon);
                                              enviar_mensaje($hora_vencimiento,$fecha_vencimiento,$mensaje,$tipo1,$tipo2,$para);
                                              header("Location:$link3");
                                         }

                                               else
                                               { //parte de Pablo
                                                 //Cuando se termina el excel es porque se termina la licitacion
                                                 // Despues de generarse el mensaje se retorna a la página  licitaciones view.
                                                $id_renglon=$parametros['renglon'];
                                                $query = "Select * from renglon where id_renglon = $id_renglon " ;
                                                $resultado = $db->execute($query);
                                                $control_renglon=$resultado->fields['codigo_renglon'];
                                                //$mensaje = "Lic_archivos_$nro_licitacion Renglon: $control_renglon<a href=$ref>";
                                                $mensaje = "Licitacion numero:$nro_licitacion Finalizada ";
                                                $tipo1= 'LIC';
                                                $tipo2 = 'LIF';
                                                genera_cotizacion_licitacion($nro_licitacion);
                                                enviar_mensaje($hora_vencimiento,$fecha_vencimiento,$mensaje,$tipo1,$tipo2,$para);
                                                header("Location:$link5");


                                               }

                                            }


?>
<html>
<head>
<title>Nuevo Mensaje</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<? cargar_calendario(); ?>
<SCRIPT language='JavaScript'>

function comprueba()
{if(document.form.f_entrega.value=='') {
 alert("Debe seleccionar fecha de vencimiento.");
 return false;
 }
 if(document.form.usuario_destino.value=='?') {
 alert("Debe seleccionar usuario.");
 return false;
 }
 if('<?echo $pagina_viene?>'=='vista_previa')
  alert('Se va a generar archivo con las descripciones');
 else if(('<?echo $pagina_viene?>'!='vista_previa_guarda')&&('<?echo $pagina_viene?>'!='vista_previa_ok'))
  alert('Se va a generar archivo con la planilla de cotización'); 
 return true;
}

</SCRIPT>
<link rel=stylesheet type='text/css' href='../layout/css/win.css'>
</head>
<body bgcolor="#E0E0E0">
<table width="90%" border="0" align="center">
  <tr bgcolor="#c0c6c9">
      <td>
      <div align="left"><font color="#006699" size="2" face="Arial, helvetica, sans-serif"><b>&nbsp&nbspEnviar
        nuevo mensaje</b></font></div>
      </td>
    </tr>
  </table>
<br>
<form name="form" action="<?echo $link;?>" method="post"  enctype='multipart/form-data'>
<center>
    <br>
    <table width="90%" border="0">
      <tr>
        <td width="53" height="49" valign="top">&nbsp;Para:</td>
        <td width="153" valign="top" >
          <input type="hidden" name="tipo_m" value=1>
          <div align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif">
            <SELECT  name="usuario_destino">
            <?
            $query = "Select * from usuarios";
            $resultado = $db->execute("$query");
            $filas_encontradas = $resultado->RecordCount();
            $i=1;
            while ($i<=$filas_encontradas){
              $usuario = $resultado->fields["nombre"];
              
              if (!permisos_check('licitaciones','licitaciones_view'))
                               {
                                echo "<option>$usuario</option>";
                                }
               $i++;
               $resultado->movenext();
                                            }
          ?>
          </SELECT>

            </font></div>
        </td>
        <td colspan="2" valign="top" >
          <div align="left">
            <p align="right">&nbsp;Fecha de Vencimieto:</p>
          </div>
        </td>
        <td valign="top" width="309">
          <div align="left">
           <input type="text" name="f_entrega" value=<?php echo $_POST['f_entrega']; ?>><?php echo link_calendario("f_entrega"); ?>
          </div>
        </td>
      </tr>
      <tr>
        <td colspan="4" height="30" valign="top" >
          <div align="right">Hora de Vencimiento: </div>
        </td>
        <td valign="top" width="309" >
          <input type="text" name="hora" value="00:00">
        </td>
      </tr>
      <tr>
        <td colspan="5" height="108">
          <div align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif">

            </font></div>
        </td>
      </tr>
      <tr>
        <td height="54" valign="top" colspan="3">
          <div align="left">
            <input type="submit" name="enviar" value="Enviar mensaje" onclick="return comprueba();" >
          </div>
        </td>
        <td colspan="2" valign="top">
          <div align="left">
            <input type="button" name="cancelar" value="Cancelar" onclick="document.location='<? if ($parametros['pagina']=='agregar_producto') echo $link4;
                                                                                                          else echo $link5; ?>'; ">
          </div>
        </td>
      </tr>
      <tr>
        <td height="3" width="53"></td>
        <td width="153"></td>
        <td width="108"></td>
        <td width="37"></td>
        <td width="309"></td>
      </tr>
    </table>

    <hr size="10">
  </center>
  <center>
  </center>
</form>

</body>
</html>