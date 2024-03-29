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

if (!defined('GLPI_ROOT')) {
	define('GLPI_ROOT', '../../..');
}
include (GLPI_ROOT . "/inc/includes.php");

$PluginRacksItemSpecification = new PluginRacksItemSpecification();
$PluginRacksItemSpecification->checkGlobal("r");

if (!isset($_SESSION['glpi_plugin_racks_tab'])) $_SESSION['glpi_plugin_racks_tab']='ComputerModel';
if (isset($_GET['onglet'])) {
  $_SESSION['glpi_plugin_racks_tab']=$_GET['onglet'];
}
	
Html::header($LANG['plugin_racks']['title'][1], '', "plugins", "racks");

$tabs['ComputerModel']=array('title'=>$LANG['common'][52],
'url'=>$CFG_GLPI['root_doc']."/plugins/racks/ajax/itemspecification.tabs.php",
'params'=>"target=".$_SERVER['PHP_SELF']."&id=-1&plugin_racks_tab=".'ComputerModel');
  
$tabs['NetworkEquipmentModel']=array('title'=>$LANG['help'][26],
'url'=>$CFG_GLPI['root_doc']."/plugins/racks/ajax/itemspecification.tabs.php",
'params'=>"target=".$_SERVER['PHP_SELF']."&id=-1&plugin_racks_tab=".'NetworkEquipmentModel');

$tabs['PeripheralModel']=array('title'=>$LANG['help'][29],
'url'=>$CFG_GLPI['root_doc']."/plugins/racks/ajax/itemspecification.tabs.php",
'params'=>"target=".$_SERVER['PHP_SELF']."&id=-1&plugin_racks_tab=".'PeripheralModel');

$tabs['PluginRacksOtherModel']=array('title'=>$LANG['plugin_racks'][43],
'url'=>$CFG_GLPI['root_doc']."/plugins/racks/ajax/itemspecification.tabs.php",
'params'=>"target=".$_SERVER['PHP_SELF']."&id=-1&plugin_racks_tab=".'PluginRacksOtherModel');
        
$tabs['all']=array('title'=>$LANG['common'][66],
'url'=>$CFG_GLPI['root_doc']."/plugins/racks/ajax/itemspecification.tabs.php",
'params'=>"target=".$_SERVER['PHP_SELF']."&id=-1&plugin_racks_tab=all");
        
echo "<div id='tabspanel' class='center-h'></div>";
Ajax::createTabs('tabspanel','tabcontent',$tabs,'PluginRacksItemSpecification');
$PluginRacksItemSpecification->addDivForTabs();

Html::footer();

?>