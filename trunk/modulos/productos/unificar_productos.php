<?
/*
Author: Broggi
Fecha: 13/12/2004

MODIFICADA POR
$Author: broggi $
$Revision: 1.7
$Date: 2004/12/10 21:05:12 $
*/


  //   ver que pasa con COMPATIBILIDADES si cambias dos motherboards hay veces que da error
 //    por ejemplo id_producto=117 y id_producto=44 (el 44 es el que tengo que borrar)
 
 
 
require_once("../../config.php");
require_once("fns_unificar_prod.php");
echo $html_header;

$precios_nuevo;
$precios_viejo;
$stock_viejo;
$stock_nuevo;

//POST DEL BOTON CAMBIAR PRODUCTOS
if ($_POST['cambiar']=='Cambiar Productos') {
	
$db->StartTrans();
//revisar esta consulta no deberia trar las tablas en las que el id_producto no depende de general.productos
//ni las tablas que son vistas
$sql="select attname,attrelid,relname,refobjid,nspname from (select substring(consrc2 from  '%#(#\"%#\"#) REFERENCES%' for '#') 
      as attname,conrelid as attrelid from (select * from (select * from (SELECT oid,pg_catalog.pg_get_constraintdef(oid) as consrc2 FROM pg_catalog.pg_constraint WHERE contype = 'f' ) as s where s.consrc2 ilike '%productos(%') as foraneas left join pg_constraint as cons on foraneas.oid=cons.oid ) as n
      UNION
      select atri.attname,atri.attrelid from pg_attribute atri left join pg_class cla on atri.attrelid=cla.oid where atri.attname='id_producto') as todo
      left join pg_class cla on attrelid=cla.oid
      left join pg_depend dep on attrelid=dep.objid
      join pg_namespace esp on refobjid=esp.oid
      order by attrelid desc";
   
$resul_consulta=sql($sql) or fin_pagina($sql);


$id_nuevo=$_POST['id_prod_original'];  
$id_viejo=$_POST['id_prod_cambiar'];
 
$precios_nuevo=recuperar_precios($id_nuevo); //precios del poducto que queda que debe actualizasrce cada vez que hago un insert en precios	
$precios_viejo=recuperar_precios($id_viejo);  //precios del producto a borrar

$stock_nuevo=recuperar_stock($id_nuevo);
$stock_viejo=recuperar_stock($id_viejo);


while (!$resul_consulta->EOF)  {
 //presupuestos_view es una vista 
 //producto_presupuesto no se toma encuenta por el id_producto es un serial
 //producto_proveedor el id_producto esta relacionado con la tabla producto_presupuesto
 //repuestos_casos  relacion con precios la actualizacion hay que hacerla aparte controlar por (producto,proveedor) en precios
        
 if (
    ($resul_consulta->fields['relname']=='productos' && $resul_consulta->fields['nspname']=='general') || 
    ($resul_consulta->fields['relname']=='precios' && $resul_consulta->fields['nspname']=='general') ||
    ($resul_consulta->fields['relname']=='historial_precio' && $resul_consulta->fields['nspname']=='general') || 
    ($resul_consulta->fields['relname']=='compatibilidades' && $resul_consulta->fields['nspname']=='general') || 
    ($resul_consulta->fields['relname']=='presupuestos_view' && $resul_consulta->fields['nspname']=='licitaciones')  ||
    ($resul_consulta->fields['relname']=='producto_presupuesto' && $resul_consulta->fields['nspname']=='licitaciones') || 
    ($resul_consulta->fields['relname']=='producto_proveedor'  && $resul_consulta->fields['nspname']=='licitaciones') ||
    ($resul_consulta->fields['relname']=='repuestos_casos' && $resul_consulta->fields['nspname']=='casos') || 
     $resul_consulta->fields['nspname']=='stock') 
     $resul_consulta->MoveNext();
       	 
 else {
 	$sql = "update ".$resul_consulta->fields['relname']." set ".$resul_consulta->fields['attname']."=$id_nuevo where ".$resul_consulta->fields['attname']."=$id_viejo";
   	$resul_cambio=sql($sql,"cambio productos ") or fin_pagina($sql,"update del producto");
    $resul_consulta->MoveNext();
 } 

 }	
 

//---------------------------------  compatibilidades -------------------------------
//CAMBIO CAMPO MOTHERBOARD

//selecciona datos que del producto que queda
$sql_compat="select componente from compatibilidades
             where motherboard=$id_nuevo";
$res_compat_n=sql($sql_compat,"compatibilidades nuevo") or fin_pagina();

$array_comp=array();

while (!$res_compat_n->EOF) {
 $componente=$res_compat_n->fields['componente'];
 $array_comp[$componente]=1;
$res_compat_n->MoveNext();
}


//selecciona datos que del producto a borrar
$sql_compat_v="select componente from compatibilidades 
             where motherboard=$id_viejo";
$res_compat_v=sql($sql_compat_v,"compatibilidades borrar") or fin_pagina();

$update_comp="";
while (!$res_compat_v->EOF) {
 $componente=$res_compat_v->fields['componente'];
if ($array_comp[$componente] !=1) 
    $update_comp[]="update compatibilidades set motherboard=$id_nuevo where motherboard=$id_viejo and componente=$componente" ;
      
$res_compat_v->MoveNext();
}

if ($update_comp !="") {
   sql($update_comp) or fin_pagina();
} 

$delete_comp="delete from compatibilidades where motherboard=$id_viejo";
sql($delete_comp,"borra compatibilidades 1") or fin_pagina();


//CAMBIO EL CAMPO COMPONENTE
//selecciona datos que del producto que queda
$sql_compat="select motherboard from compatibilidades
             where componente=$id_nuevo";
$res_compat_n=sql($sql_compat,"compatibilidades nuevo") or fin_pagina();
$array_comp=array();
while (!$res_compat_n ->EOF) {
	$motherboard=$res_compat_n->fields['motherboard'];
$array_comp[$motherboard]=1;
$res_compat_n->MoveNext();
}

//selecciona datos que del producto a borrar
$sql_compat_v="select motherboard from compatibilidades 
               where componente=$id_viejo";
$res_compat_v=sql($sql_compat_v,"compatibilidades borrar") or fin_pagina();

$delete_comp="";
$update_comp="";
while (!$res_compat_v->EOF) {
 $motherboard=$res_compat_v->fields['motherboard'];
if ($array_comp[$motherboard] !=1) 
  $update_comp="update compatibilidades set componente=$id_nuevo where motherboard=$motherboard and componente=$id_viejo" ;
	
$res_compat_v->MoveNext();
}


if ($update_comp !="") {
  sql($update_comp) or fin_pagina();
}

$delete_comp="delete from compatibilidades where componente=$id_viejo";
sql($delete_comp) or fin_pagina();

//-------------------------Fin compatibilidades----------------------------------------


//---------------------- repuestos_casos ----------------------------------
//repuestos_casos dependencia con precios (id_proveedor,id_producto)
 
$sql_casos="select id_producto,id_proveedor
            from repuestos_casos where id_producto=$id_viejo";
$res_casos=sql($sql_casos,"respuestos casos") or fin_pagina();

while (!$res_casos->EOF) {
$id_prov=$res_casos->fields["id_proveedor"];

if ( $precios_nuevo[$id_prov]['id_proveedor']=="") { // si no existe creo una entrada para precio

  $precio=$precios_viejo[$id_prov]['precio'];
  $observaciones=$precios_viejo[$id_prov]['observaciones']; 
  $usuario=$precios_viejo[$id_prov]['usuario'];
  $fecha=$precios_viejo[$id_prov]['fecha'];

  $sql2="insert into precios (id_proveedor,id_producto,precio,observaciones,usuario,fecha) 
  values ($id_prov,$id_nuevo,$precio,";
  
  if ($observaciones=="") $sql2.="null,";
     else  $sql2.="'$onservaciones',";
  if ($usuario=="") $sql2.="null,";
     else  $sql2.="'$usuario',";
  if ($fecha=="") $sql2.="null";
     else  $sql2.="'$fecha'";
  
  $sql2.=")";
  sql($sql2,"insert precios") or fin_pagina();
   
  //actualizar $precios_nuevo
    $precios_nuevo[$id_prov]['id_proveedor']=$id_prov;
    $precios_nuevo[$id_prov]['precio']=$precio;
    $precios_nuevo[$id_prov]['observaciones']=$observaciones;
    $precios_nuevo[$id_prov]['usuario']=$usuario;
    $precios_nuevo[$id_prov]['fecha']=$fecha; 
    
}

$res_casos->MoveNext();
}
$sql_casos="update repuestos_casos set id_producto=$id_nuevo where id_producto=$id_viejo";
sql($sql_casos,"repuestos_casos") or fin_pagina();

//------------------------------ FIN repuestos_casos -----------------------------------



//------------------------------------ STOCK DISPONIBLE ---------------------------- 
//echo " stock_disponible<br> ";
$stock_disponible=array();
$sql="select id_producto,id_deposito,cantidad_total 
      from stock_disponible where id_producto=$id_nuevo order by id_deposito";
$res=sql($sql,"stock_disponible") or fin_pagina();

while (!$res->EOF) {
 $id=$res->fields['id_deposito'];
 $stock_disponible[$id]=$res->fields['cantidad_total'];
 $res->MoveNext();
}

$sql="select id_producto,id_deposito,cantidad_total
      from stock_disponible where id_producto=$id_viejo order by id_deposito";
$res=sql($sql,"stock disponible producto a borrar") or fin_pagina();

$sql_disp="";
 while (!$res->EOF) {
 	$id=$res->fields['id_deposito'];
 	if ($stock_disponible[$id] != "" || $stock_disponible[$id] !=null)  {
 		  $suma=$res->fields['cantidad_total'] + $stock_disponible[$id];
 	      $sql_disp[]="update stock_disponible set cantidad_total=$suma where id_producto=$id_nuevo and id_deposito=$id";      
 	      sql($sql_disp,"update stock disponible con sum") or fin_pagina();
 	     }
 	else {
          $sql_disp[]="update stock_disponible set id_producto=$id_nuevo where id_producto=$id_viejo";      
 	      sql($sql_disp,"update stock_disponible con id_producto") or fin_pagina();
 	}
 	 	
 $res->MoveNext();	
 }

   $sql_disp="delete from stock_disponible where id_producto=$id_viejo";
   sql($sql_disp,"borro stock disponible") or fin_pagina();
//------------------------  FIN STOCK DISPONIBLE -----------------------------



//echo "info rma <br>";

$sql_rma="select id_info_rma,id_deposito,id_producto,id_proveedor,nro_ordenp,
          nro_ordenc,id_nota_credito,nrocaso,fecha_historial,user_historial,
          deposito,cantidad,id_ubicacion,garantia_vencida 
          from info_rma where id_producto=$id_viejo";
$res_rma=sql($sql_rma,"recupera info_rma") or fin_pagina();
$sql_delete1="";
$sql_delete2="";

$Nro_ordenp="null";
$nro_ordenc="null";
$id_nota_credito="null";
$nrocaso="null";
$cantidad="null";
$id_ubicacion="null";

if ($res_rma->RecordCount() > 0 ) {
while (!$res_rma->EOF ) {
	
$id_info_rma=$res_rma->fields['id_info_rma'];	
$id_deposito=$res_rma->fields['id_deposito'];
$id_proveedor=$res_rma->fields['id_proveedor'];
$Nro_ordenp=$res_rma->fields['nro_ordenp'];
$nro_ordenc=$res_rma->fields['nro_ordenc'];
$id_nota_credito=$res_rma->fields['id_nota_credito'];
$nrocaso=$res_rma->fields['nrocaso'];
$fecha_historial=$res_rma->fields['fecha_historial'];
$user_historial=$res_rma->fields['user_historial'];
$deposito=$res_rma->fields['deposito'];
$cantidad=$res_rma->fields['cantidad'];
$id_ubicacion=$res_rma->fields['id_ubicacion'];
$garantia_vencida=$res_rma->fields['garantia_vencida'];


verificar_prod($id_nuevo,$id_viejo,$id_proveedor,$id_deposito);

//creo  una entrada en infor rma con un nuevo serial , el producto que queda (id_nuevo) y los datos de la entrada original

 $query_id_rma= "SELECT nextval('stock.info_rma_id_info_rma_seq') as id";
 $res_id= sql($query_id_rma) or fin_pagina();
 $id_info_rma_nuevo = $res_id->fields["id"];
 
 $sql="insert into info_rma (id_info_rma,id_deposito,id_producto,id_proveedor,cantidad,
       nro_ordenp,nro_ordenc,id_nota_credito,nrocaso,
       fecha_historial,user_historial,deposito,id_ubicacion,garantia_vencida) 
       values ($id_info_rma_nuevo,$id_deposito,$id_nuevo,$id_proveedor,$cantidad,";
 
if ($Nro_ordenp=="") $sql.="null,";
   else $sql.="$Nro_ordenp,";

if ($nro_ordenc == "")  $sql.="null,";
  else $sql.="$nro_ordenc,";
 
if ($id_nota_credito =="" ) $sql.="null," ;
    else $sql.="$id_nota_credito,";
 
if ($nrocaso=="" )   $sql.="null,";
     else $sql.="'$nrocaso',";

if ($fecha_historial=="" )  $sql.="null,";
 else  $sql.="'$fecha_historial',";

if ($user_historial =="") $sql.="null,";
  else  $sql.="'$user_historial',";
 
if ($deposito=="")  $sql.="null,";
   else $sql.="'$deposito',";
 
 
if ($id_ubicacion== "" ) $sql.="null,";
     else $sql.="$id_ubicacion,";
     
if ($garantia_vencida=="") $sql.="null";
 else $sql.="$garantia_vencida";

$sql.=")";

 sql($sql,"duplica info_rma") or fin_pagina();
    
  $sql_update[]="update log_cambio_prod set id_info_rma=$id_info_rma_nuevo,id_producto=$id_nuevo where id_info_rma=$id_info_rma";
  $sql_update[]="update log_ubicacion set id_info_rma=$id_info_rma_nuevo,id_producto=$id_nuevo where id_info_rma=$id_info_rma";
  $sql_update[]="update archivos_subidos set id_info_rma=$id_info_rma_nuevo,id_producto=$id_nuevo where id_info_rma=$id_info_rma";
  $sql_update[]="update comentarios_rma set id_info_rma=$id_info_rma_nuevo,id_producto=$id_nuevo where id_info_rma=$id_info_rma";
  $sql_update[]="update descuento set id_info_rma=$id_info_rma_nuevo,id_producto=$id_nuevo where id_info_rma=$id_info_rma";
 
  sql($sql_update,"update tablas") or fin_pagina();
  $sql="delete from info_rma where id_info_rma=$id_info_rma";
  sql($sql,"borra rma viejo") or fin_pagina();
 
$res_rma->MoveNext();
}
}

//echo "termina rma ";

//----------------------------------- mecaderia en transito ----------------------------------------
//echo "mercaderia en transito <br>";
	$nro_orden="";
	$comentarios="";
	$cantidad="";
	$fecha_inicio="";
	$fecha_fin="";
	$id_deposito="";
	$id_producto="";
	$id_proveedor="";

	
$sql_merc="select id_mercaderia_transito,id_deposito,id_producto,id_proveedor,id_movimiento_material,
           nro_orden,comentarios,cantidad,fecha_inicio,fecha_fin from mercaderia_transito 
           where id_producto=$id_viejo";

$res_merc=sql($sql_merc,"mercaderia en transito") or fin_pagina();

//echo "cant ".$res_merc->RecordCount();

if ($res_merc->RecordCount() > 0) {
$i=0;
while (!$res_merc->EOF) {
	
	//echo "en while de mercaderia en transiTO";
	$id_mercaderia_transito=$res_merc->fields['id_mercaderia_transito'];
	$id_movimiento_material=$res_merc->fields['id_movimiento_material'];
	$nro_orden=$res_merc->fields['nro_orden'];
	$comentarios=$res_merc->fields['comentarios'];
	$cantidad=$res_merc->fields['cantidad'];
	$fecha_inicio=$res_merc->fields['fecha_inicio'];
	$fecha_fin=$res_merc->fields['fecha_fin'];
	$id_deposito=$res_merc->fields['id_deposito'];
	$id_producto=$res_merc->fields['id_producto'];
	$id_proveedor=$res_merc->fields['id_proveedor'];
	
	//echo "<br> antes de verificar prod id prov".$id_proveedor."ID deposito ".$id_deposito;
    verificar_prod($id_nuevo,$id_viejo,$id_proveedor,$id_deposito);
   
  // echo "<br> MERCADERIA VIEJO ".$id_mercaderia_transito."I= ".$i;
    
 $query_id_merc = "SELECT nextval('stock.mercaderia_transito_id_mercaderia_transito_seq') as id";
 $res_id= sql($query_id_merc) or fin_pagina();
 $id_merc_nuevo = $res_id->fields["id"];
 

 $sql="insert into mercaderia_transito (id_mercaderia_transito,id_movimiento_material,
       nro_orden,comentarios,cantidad,fecha_inicio,fecha_fin,id_deposito,id_producto,id_proveedor)
       values ($id_merc_nuevo,";
 
 if ($id_movimiento_material=="") $sql.="null,";
    else $sql.=" $id_movimiento_material,";
 if ($nro_orden=="") $sql.="null,";
    else $sql.="$nro_orden,";
 if ($comentarios=="")  $sql.="null,";
    else  $sql.="'$comentarios',";
 if ($cantidad=="")   $sql.="null,";
     else $sql.="$cantidad,";
 if ($fecha_inicio == "" ) $sql.="null,";
   else $sql.="'$fecha_inicio',";
 if ($fecha_fin == ""  ) $sql.="null,";
   else $sql.="'$fecha_fin',";
 
 $sql.="$id_deposito,$id_nuevo,$id_proveedor)";
 
 //echo "<br> sql insert  ".$sql;
 sql($sql,"insert Merc Trans") or fin_pagina();
   // echo "<br> despues de insert  ".$id_merc_nuevo."<br>";
  
  $sql_update[]="update comentarios_merc_trans set id_mercaderia_transito=$id_merc_nuevo,id_producto=$id_nuevo where id_mercaderia_transito=$id_mercaderia_transito";
  $sql_update[]="update descuento set id_mercaderia_transito=$id_merc_nuevo,id_producto=$id_nuevo where id_mercaderia_transito=$id_mercaderia_transito";
 
 // echo "<br>  update ";
   //print_r ($sql_update);
       
  sql($sql_update,"update tablas 2") or fin_pagina();
  //echo "<br> despues de update ";
     

 //echo "SQL ".$sql_del; 
  $sql_del="delete from mercaderia_transito where id_mercaderia_transito=$id_mercaderia_transito";
  //echo "<br> despues delete";
  sql($sql_del,"borra merc transito viejo") or fin_pagina();

 $i++;
$res_merc->MoveNext();

}

//echo "sale del while ";
}
//echo "despues del if <br>"; 
//--------------------- FIN MERCADERIA --------------------------------------------


//------------------------------------- RESERVADOS --------------------

//echo "reservados <br> ";

$sql="select id_reservado,cantidad_total_reservada,id_deposito,id_proveedor 
      from reservados 
	  where id_producto=$id_nuevo";
$res=sql($sql,"reservados producto nuevo") or fin_pagina();

$reserva_nuevo=array();
while (!$res->EOF) {
	$id_prov=$res->fields["id_proveedor"];
	$id_deposito=$res->fields["id_deposito"];
	
	$reserva_nuevo[$id_prov][$id_deposito]["cantidad_total_reservada"]=$res->fields["cantidad_total_reservada"];
	$reserva_nuevo[$id_prov][$id_deposito]["id_reservado"]=$res->fields["id_reservado"];

$res->MoveNext();
}

$sql_reservados="select id_reservado,id_deposito,id_proveedor,cantidad_total_reservada
                 from reservados 
                 where id_producto=$id_viejo";

$res_reservados=sql($sql_reservados,"reservados productos viejo ") or fin_pagina();


if ($res_reservados->RecordCount() > 0) {
	
while (!$res_reservados->EOF) {
	
	$id_reservado=$res_reservados->fields['id_reservado'];
	
	//echo "<br> EN EL WHILE id reservado".$id_reservado;
	$id_deposito=$res_reservados->fields['id_deposito'];
	$id_proveedor=$res_reservados->fields['id_proveedor'];
	
	if ($res_reservados->fields['cantidad_total_reservada']!= "" || $res_reservados->fields['cantidad_total_reservada']!=null) 
	      $cantidad_total_reservada=$res_reservados->fields['cantidad_total_reservada'];  //cant total en tabla reservados
	else  $cantidad_total_reservada=0;     

  //echo "ANTES DE VERIFICAR PRODUCTOS ";
	
    verificar_prod($id_nuevo,$id_viejo,$id_proveedor,$id_deposito);
	
if ($reserva_nuevo[$id_proveedor][$id_deposito]["cantidad_total_reservada"] !="" 
     || $reserva_nuevo[$id_proveedor][$id_deposito]["cantidad_total_reservada"] !=null) 
{
	 //echo "sumar <br>";
    //sumar
    if ($reserva_nuevo[$id_proveedor][$id_deposito]["cantidad_total_reservada"] != "" || $reserva_nuevo[$id_provedor][$id_deposito]["cantidad_total_reservada"] !="")
          $cant_nueva=$reserva_nuevo[$id_proveedor][$id_deposito]["cantidad_total_reservada"];
    else  $cant_nueva=0;
 	
 	$sum=$cantidad_total_reservada + $cant_nueva;
    $id_reservado_nuevo=$reserva_nuevo[$id_proveedor][$id_deposito]["id_reservado"];
     $sql="update reservados set cantidad_total_reservada=$sum where id_producto=$id_nuevo and id_proveedor=$id_proveedor and id_deposito=$id_deposito";
	sql($sql,"update reservados con suma") or fin_pagina();
	
	if ($id_reservado_nuevo != "") {
	  $sql="update detalle_reserva set id_reservado=$id_reservado_nuevo where id_reservado=$id_reservado";
	  sql($sql,"<bR> delete detalle_reservados") or fin_pagina();
	}
	//echo "<br>ID RESERVADO ".$id_reservado;
	
	$sql="delete from reservados where id_reservado=$id_reservado";
	sql($sql,"borra reservados") or fin_pagina();
}

else {  //cambiar el id_producto
   //echo "<br> ACA   en else prov ".$id_proveedor."prod".$id_producto;
		$sql_update="update reservados set id_producto=$id_nuevo where id_producto=$id_viejo and id_proveedor=$id_proveedor and id_deposito=$id_deposito";
		sql($sql,"update reservados cambia productos") or fin_pagina();
	} 
	
$res_reservados->MoveNext();
}

$sql="update reservados set id_producto=$id_nuevo where id_producto=$id_viejo";
sql($sql,"actualiza el producto viejo") or fin_pagina();

}
// ------------------------------   FIN RESERVADOS  ------------------------



//--------------------           descuento  -------------------------------------------
// modifica las entradas en descuento que no tienen id_info_rma o id_mercaderia en transito


$sql="select id_deposito,id_producto,id_proveedor, id_control_stock,observacion,
      cant_desc,id_mercaderia_transito,id_info_rma
      from descuento where id_producto=$id_viejo";
$res=sql($sql,"descuento") or fin_pagina();
$sql_desc="";
while(!$res->EOF) {
$id_proveedor=$res->fields['id_proveedor'];
$id_deposito=$res->fields['id_deposito'];
verificar_prod($id_nuevo,$id_viejo,$id_proveedor,$id_deposito);
$sql_desc[]="update descuento set id_producto=$id_nuevo where id_producto=$id_viejo";
$res->MoveNext();
}

if ($sql_desc!="")
   sql($sql_desc,"actualiza descuento") or fin_pagina();

//------------------------------ FIN  descuento -------------


//actualizo las cantidades si existe id_producto(id_nuevo),id_proveedor,id_deposito
   $cantidad1=0; 
   $cantidad2=0; 
   $sql="select cant_disp,id_proveedor,id_deposito from stock where id_producto=$id_viejo";	
   $res=sql($sql,"cant de stock ") or fin_pagina();

if ($res->RecordCount() > 0 ) {
while (!$res->EOF) {
  $id_proveedor=$res->fields['id_proveedor'];
  $id_deposito=$res->fields['id_deposito'];
   	
    
 //$sql_nuevo="select cant_disp from stock where id_producto=$id_nuevo and id_proveedor=$id_proveedor and id_deposito=$id_deposito";	
 //$res1=sql($sql_nuevo) or fin_pagina();
 // if ($res1->RecorcCount() > 0 ) {
if ($stock_nuevo[$id_proveedor][$id_deposito]["id_proveedor"] !="") {	
  if ($res->fields['cant_disp']!= "") $cantidad1=$res->fields['cant_disp'];
     else $cantidad1=0;
  if ($stock_nuevo[$id_proveedor][$id_deposito]['cant_disp']!="") $cantidad2=$stock_nuevo[$id_proveedor][$id_deposito]['cant_disp'];
     else $cantidad2=0;
   
    $sum=$cantidad1 + $cantidad2;
    $sql_update="update stock set cant_disp=$sum 
         where id_producto=$id_nuevo and id_proveedor=$id_proveedor and id_deposito=$id_deposito";
    sql($sql_update,"actualiza stock con suma") or fin_pagina(); 
}
    else {
    $sql_update="update stock set id_producto=$id_nuevo
         where id_producto=$id_viejo and id_proveedor=$id_proveedor and id_deposito=$id_deposito";
    sql($sql_update,"actualiza stock con producto") or fin_pagina(); 
    
    }
    $res->MoveNext();
 }
}

$sql="update historial_precio set id_producto=$id_nuevo where id_producto=$id_viejo";
sql($sql,"historial precios ") or fin_pagina(); 


//---------------------   borra stock ---------------------------------------
$sql_delete1="delete from stock where id_producto=$id_viejo";
sql($sql_delete1,"borra stock") or fin_pagina();

//borrar precios 

$sql="select id_proveedor,precio,observaciones,usuario,fecha
      from precios where id_producto=$id_viejo";
$res=sql($sql,"precios") or fin_pagina();
$sql_precios="";

while(!$res->EOF) {
$id_proveedor=$res->fields['id_proveedor'];

$sql="select precio from precios where id_producto=$id_nuevo and id_proveedor=$id_proveedor";
$res_precios=sql($sql,"recupera precios") or fin_pagina();

  
if ($res_precios->RecordCount() == 0 ) { // si no existe creo una entrada para precio
   $sql="update precios set id_producto=$id_nuevo where id_producto=$id_viejo and id_proveedor=$id_proveedor";
   sql($sql,"update precios al borrar stock") or fin_pagina();
} 
else {
   $sql="delete from precios where id_producto=$id_viejo and id_proveedor=$id_proveedor";
   sql($sql) or fin_pagina(); 
}
$res->MoveNext();
}

//===================================================

$sql_delete[]="delete from productos where id_producto=$id_viejo";    
$sql_delete[]="delete from descuento where id_producto=$id_viejo";    

sql($sql_delete,"borra descuento,productos ") or fin_pagina(); //borra_precio

if ($db->CompleteTrans())
    Aviso( "El producto se borró con exito"); 
	
}


//
?>
<script>
var wproductos;
var id;
function muestra_des()
{//alert ("el id_original es: "+id_original);
 pagina_prod="<?=encode_link('../general/detalle_productos.php',array('tipo'=>'todos','tipo_producto'=>'todos'))?>";
 window.open(pagina_prod+'&producto='+id,'','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=25,top=10,width=750,height=300');
}	

function cargar(donde)
{document.all.prod_original.value=wproductos.document.all.descripcion.value;
 document.all.id_prod_original.value=wproductos.document.all.id_producto.value; 
 id=wproductos.document.all.id_producto.value;
 wproductos.close();
}	

function cargar_2(donde)
{document.all.prod_cambiar.value=wproductos.document.all.descripcion.value;
 document.all.id_prod_cambiar.value=wproductos.document.all.id_producto.value;   
 id=wproductos.document.all.id_producto.value;
 wproductos.close();
}	

function busca_prod(donde)
{if (donde=='original')
    {pagina_prod="<?=encode_link('../general/productos2.php',array('onclickcargar'=>"window.opener.cargar()",'onclicksalir'=>'window.close()','cambiar'=>0,'viene'=>'rma')) ?>"
     wproductos=window.open(pagina_prod,'','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=25,top=10,width=750,height=300');
    }
 else if (donde=='cambiar')    
         {pagina_prod="<?=encode_link('../general/productos2.php',array('onclickcargar'=>"window.opener.cargar_2()",'onclicksalir'=>'window.close()','cambiar'=>0,'viene'=>'rma')) ?>"
          wproductos=window.open(pagina_prod,'','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=25,top=10,width=750,height=300');
         }
}

</script>

<form name="unificar_productos" method="POST" action="unificar_productos.php">


<br>
<table align="center">
 <tr><td><font size="4"><b>Unificar Productos</b></font></td></tr>
</table>
<br>
<table align="center" width="95%" class="bordes">
 <tr>
  <td align="center"><input name="producto_original" value="Producto Original" type="button" onclick="busca_prod('original')"></td>
  </tr> 
 <tr><td><font size="2"><b>Descripción:&nbsp;</font><font size="3" color="Blue"><input name="prod_original" size="90%" class="text_8" style="cursor:hand"></font></b></td></tr>
 <tr><td><font size="2"><b>Id del producto Original:&nbsp;</b></font><input name="id_prod_original" class="text_8" ></td></tr>
</table>
<br>
<table align="center" width="95%" class="bordes">
 <tr>
  <td align="center"><input name="producto_cambiar" value="Producto a Eliminar" type="button" onclick="busca_prod('cambiar')"></td>
 </tr> 
 <tr><td><font size="2"><b>Descripción:&nbsp;</b></font><input  name="prod_cambiar" size="90%" class="text_8"  style="cursor:hand"></td></tr>
 <tr><td><font size="2"><b>Id del producto a Cambiar:&nbsp;</b></font><input  name="id_prod_cambiar" class="text_8" ></td></tr>
</table>
<br>
<table align="center"> 
 <tr><td><input name="cambiar" value="Cambiar Productos" type="submit"></td></tr>
</table>

<!-- onclick="muestra_des()"-->
<?fin_pagina();?>