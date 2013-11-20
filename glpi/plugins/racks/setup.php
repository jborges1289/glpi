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

function plugin_init_racks() {
	global $PLUGIN_HOOKS, $CFG_GLPI, $LANG;
   
   $PLUGIN_HOOKS['csrf_compliant']['racks'] = true;
	$PLUGIN_HOOKS['change_profile']['racks'] = array('PluginRacksProfile','changeProfile');
   if (class_exists('PluginRacksRack')) { // only if plugin activated
      $PLUGIN_HOOKS['pre_item_purge']['racks'] = array('Profile'=> 
                                       array('PluginRacksProfile', 'purgeProfiles'));

   }
   
	Plugin::registerClass('PluginRacksRack', array(
		'document_types' => true,
		'unicity_types' => true,
      'linkgroup_tech_types' => true,
      'linkuser_tech_types' => true
   ));
   
   Plugin::registerClass('PluginRacksProfile',
                         array('addtabon' => 'Profile'));
   
   if (class_exists('PluginAppliancesAppliance')) {
      PluginAppliancesAppliance::registerType('PluginRacksRack');
   }
   
   if (class_exists('PluginManufacturersimportsConfig')) {
         PluginManufacturersimportsConfig::registerType('PluginRacksRack');
      }
   
	if (Session::getLoginUserID()) {

		// Display a menu entry ?
		if (plugin_racks_haveRight("racks", "r")) {
			$PLUGIN_HOOKS['menu_entry']['racks'] = 'front/rack.php';
			$PLUGIN_HOOKS['submenu_entry']['racks']['search'] = 'front/rack.php';
			$PLUGIN_HOOKS['submenu_entry']['racks']['add'] = 'front/setup.templates.php?add=1';
		}

		if (plugin_racks_haveRight("model", "r")) {
			$PLUGIN_HOOKS['submenu_entry']['racks']['template'] = 'front/setup.templates.php?add=0';
			$PLUGIN_HOOKS['submenu_entry']['racks']["<img  src='".
			$CFG_GLPI["root_doc"]."/pics/menu_showall.png' title=\"".$LANG['plugin_racks']['setup'][12].
			"\" alt=\"".$LANG['plugin_racks']['setup'][12]."\">"] = 'front/itemspecification.php';
			
		}
			
		if (plugin_racks_haveRight("racks", "r"))  {
			$PLUGIN_HOOKS['submenu_entry']['racks']['config'] = 'front/config.form.php';
			$PLUGIN_HOOKS['use_massive_action']['racks'] = 1;
		}
      
		// Config page
		if (plugin_racks_haveRight("racks", "w") || Session::haveRight("config", "w"))
			$PLUGIN_HOOKS['config_page']['racks'] = 'front/config.form.php';
			
		// Add specific files to add to the header : javascript or css
		//$PLUGIN_HOOKS['add_javascript']['example']="example.js";
		$PLUGIN_HOOKS['add_css']['racks'] = "racks.css";
		
		$PLUGIN_HOOKS['post_init']['racks'] = 'plugin_racks_postinit';
	}
}

function plugin_version_racks() {
	global $LANG;

	return array (
		'name' => $LANG['plugin_racks']['title'][1],
		'version' => '1.3.2',
		'oldname' => 'rack',
		'license' => 'GPLv2+',
		'author'=>'Philippe Béchu, Walid Nouh, Xavier Caillaud',
		'homepage'=>'https://forge.indepnet.net/projects/show/racks',
		'minGlpiVersion' => '0.83.3',// For compatibility / no install in version < 0.83
	);
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_racks_check_prerequisites() {
	if (version_compare(GLPI_VERSION,'0.83.3.','lt') || version_compare(GLPI_VERSION,'0.84','ge')) {
      echo "This plugin requires GLPI >= 0.83.3";
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_racks_check_config() {
	return true;
}

function plugin_racks_haveRight($module,$right) {
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_racks_profile"][$module])
	&&in_array($_SESSION["glpi_plugin_racks_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

?>