<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Racks plugin for GLPI
 Copyright (C) 2003-2011 by the Racks Development Team.

 https://forge.indepnet.net/projects/racks
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Racks.

 Racks is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Racks is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Racks. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

$title = "Gestor de Compartimentos";

$LANG['plugin_racks']['title'][1] = "".$title."";
$LANG['plugin_racks']['title'][2] = "Compartimento";
$LANG['plugin_racks']['title'][3] = "Bays";

$LANG['plugin_racks'][1] = "Disposición de compartimento";
$LANG['plugin_racks'][2] = "Fabricante";
$LANG['plugin_racks'][3] = "Lugar";
$LANG['plugin_racks'][4] = "Compartimentos";
$LANG['plugin_racks'][5] = "Nombre";
$LANG['plugin_racks'][9] = "Fuente de alimentación #1";
$LANG['plugin_racks'][10] = "Fuente de alimentación #2";
$LANG['plugin_racks'][11] = "Localización";
$LANG['plugin_racks'][12] = "Modelo";
$LANG['plugin_racks'][13] = "Posición";
$LANG['plugin_racks'][14] = "Compartimentos asociados";
$LANG['plugin_racks'][15] = "Añadir equipo";
$LANG['plugin_racks'][16] = "Equipo";
$LANG['plugin_racks'][17] = "Compartimento izquierdo";
$LANG['plugin_racks'][18] = "Compartimento derecho";
$LANG['plugin_racks'][19] = "No existe compartimento a la izquierda";
$LANG['plugin_racks'][20] = "No existe compartimento a la derecha";
$LANG['plugin_racks'][21] = "No more place for insertion";
$LANG['plugin_racks'][23] = "Ocupa toda la longitud del compartimento";
$LANG['plugin_racks'][24] = "Total";
$LANG['plugin_racks'][25] = "Número total de ordenadores";
$LANG['plugin_racks'][26] = "Número total de equipos de electrónica de red";
$LANG['plugin_racks'][27] = "Número total de periféricos";
$LANG['plugin_racks'][28] = "Configuración de unidades";
$LANG['plugin_racks'][29] = "Métrico";
$LANG['plugin_racks'][30] = "Anglosajón";
$LANG['plugin_racks'][31] = "kg";
$LANG['plugin_racks'][32] = "lbs";
$LANG['plugin_racks'][33] = "btu/h";
$LANG['plugin_racks'][34] = "W";
$LANG['plugin_racks'][35] = "m3/h";
$LANG['plugin_racks'][36] = "CFM";
$LANG['plugin_racks'][37] = "mm";
$LANG['plugin_racks'][38] = "pulg.";
$LANG['plugin_racks'][39] = "Unidades disponibles";
$LANG['plugin_racks'][40] = "Altura";
$LANG['plugin_racks'][41] = "Anchura";
$LANG['plugin_racks'][42] = "Profundidad";
$LANG['plugin_racks'][43] = "Otro equipo o elemento";
$LANG['plugin_racks'][44] = "Número total de otros equipos";
$LANG['plugin_racks'][45] = "Fuentes de alimentación";
$LANG['plugin_racks'][46] = "Parte anterior";
$LANG['plugin_racks'][47] = "Parte posterior";
$LANG['plugin_racks'][48] = "Sistemas de alimentación ininterrumpida (SAI)";
$LANG['plugin_racks'][49] = "Conmutador KVM";
$LANG['plugin_racks'][50] = "Especificaciones";

$LANG['plugin_racks']['device'][1] = "Conexión de la fuente de alimentación";
$LANG['plugin_racks']['device'][2] = "Disposición";
$LANG['plugin_racks']['device'][3] = "Corriente eléctrica";
$LANG['plugin_racks']['device'][4] = "Gasto calorífico";
$LANG['plugin_racks']['device'][5] = "Tamaño";
$LANG['plugin_racks']['device'][6] = "Peso";
$LANG['plugin_racks']['device'][7] = "Caudal de aire";
$LANG['plugin_racks']['device'][8] = "Cables de alimentación de tipo C13";
$LANG['plugin_racks']['device'][9] = "Número total de cables de alimentación";
$LANG['plugin_racks']['device'][10] = "Amperaje en los cables de alimentación";

$LANG['plugin_racks']['profile'][0] = "Derechos de gestión";

$LANG['plugin_racks']['setup'][0] = "Añadir especificaciones a los modelos de los servidores";
$LANG['plugin_racks']['setup'][12] = "Especificaciones de los modelos de los equipos";
$LANG['plugin_racks']['setup'][14] = "¿No dispone de ningún equipo a añadir?\n\nAsegúrese de haber establecido las especificaciones de los modelos de los equipos antes de intentar añadirlos en un compartimento e inténtelo de nuevo.\n\nDisculpe las molestias.";

?>