<?php
/*
$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2006/01/06 22:30:50 $
*/
require_once("../../config.php");

$db->StartTrans();

$query="select id_movimiento_material,id_renglon,renglon.id_licitacion
        from movimiento_material join renglon using(id_renglon)
        where id_renglon is not null";
$movs=sql($query,"<br>Error al traer los movimientos de material cargados con renglon<br>") or fin_pagina();

//por cada mov, le ponemos el id de licitacion correpondiente
while (!$movs->EOF)
{
 $query="update movimiento_material set id_licitacion=".$movs->fields["id_licitacion"]." where id_movimiento_material=".$movs->fields["id_movimiento_material"];
 sql($query,"<br>Error al actualizar el movimiento de material ".$movs->fields["id_movimiento_material"]."<br> $query<br>") or fin_pagina();

 $movs->MoveNext();
}//de while(!$movs->EOF)


$query="select id_en_produccion,id_renglon,renglon.id_licitacion
        from en_produccion join renglon using(id_renglon)
        where id_renglon is not null";
$movs=sql($query,"<br>Error al traer los movimientos de material cargados con renglon<br>") or fin_pagina();

//por cada mov, le ponemos el id de licitacion correpondiente
while (!$movs->EOF)
{
 $query="update en_produccion set id_licitacion=".$movs->fields["id_licitacion"]." where id_en_produccion=".$movs->fields["id_en_produccion"];
 sql($query,"<br>Error al actualizar el movimiento de material ".$movs->fields["id_movimiento_material"]."<br> $query<br>") or fin_pagina();

 $movs->MoveNext();
}//de while(!$movs->EOF)

$db->CompleteTrans();
?>