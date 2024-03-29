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
	die("Sorry. You can't access directly to this file");
}

class PluginRacksRack extends CommonDBTM {
   
   static $types = array(
         'Computer','NetworkEquipment','Peripheral'
         );
   public $dohistory=true;
   
   const FRONT_FACE = 1;
   const BACK_FACE = 2;
   
   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['plugin_racks']['title'][3];
      }
      return $LANG['plugin_racks']['title'][2];
   }
   
   function canCreate() {
      return plugin_racks_haveRight('racks', 'w');
   }

   function canView() {
      return plugin_racks_haveRight('racks', 'r');
   }
   
   function cleanDBonPurge() {

      $temp = new PluginRacksRack_Item();
      $temp->deleteByCriteria(array('plugin_racks_racks_id' => $this->fields['id']));

	}
  
   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_racks']['title'][1];

      $tab[1]['table'] = $this->getTable();
      $tab[1]['field'] = 'name';
      $tab[1]['name'] = $LANG['plugin_racks'][5];
      $tab[1]['datatype']='itemlink';

      $tab[2]['table'] = 'glpi_locations';
      $tab[2]['field'] = 'completename';
      $tab[2]['name'] = $LANG['plugin_racks'][3];

      $tab[3]['table'] = 'glpi_plugin_racks_roomlocations';
      $tab[3]['field'] = 'completename';
      $tab[3]['name'] = $LANG['plugin_racks'][11];

      $tab[4]['table'] = $this->getTable();
      $tab[4]['field'] = 'rack_size';
      $tab[4]['name'] = $LANG['plugin_racks']['device'][5];
      $tab[4]['datatype'] = 'number';

      $tab[5]['table'] = 'glpi_manufacturers';
      $tab[5]['field'] = 'name';
      $tab[5]['name'] = $LANG['plugin_racks'][2];

      $tab[6]['table'] = 'glpi_groups';
      $tab[6]['field'] = 'name';
      $tab[6]['linkfield'] = 'groups_id_tech';
      $tab[6]['name'] = $LANG['common'][109];

      $tab[7]['table'] = 'glpi_users';
      $tab[7]['field'] = 'name';
      $tab[7]['linkfield'] = 'users_id_tech';
      $tab[7]['name'] = $LANG['common'][10];

      $tab[8]['table'] = $this->getTable();
      $tab[8]['field'] = 'height';
      $tab[8]['name'] = $LANG['plugin_racks'][40];
      $tab[8]['datatype'] = 'decimal';

      $tab[9]['table'] = $this->getTable();
      $tab[9]['field'] = 'width';
      $tab[9]['name'] = $LANG['plugin_racks'][41];
      $tab[9]['datatype'] = 'decimal';

      $tab[10]['table'] = $this->getTable();
      $tab[10]['field'] = 'depth';
      $tab[10]['name'] = $LANG['plugin_racks'][42];
      $tab[10]['datatype'] = 'decimal';

      $tab[11]['table'] = $this->getTable();
      $tab[11]['field'] = 'is_recursive';
      $tab[11]['name'] = $LANG['entity'][9];
      $tab[11]['datatype'] = 'bool';
      
      $tab[12]['table'] = $this->getTable();
      $tab[12]['field'] = 'serial';
      $tab[12]['name'] = $LANG['common'][19];
      
      $tab[13]['table'] = $this->getTable();
      $tab[13]['field'] = 'otherserial';
      $tab[13]['name'] = $LANG['common'][20];
      
      $tab[14]['table']='glpi_plugin_racks_racktypes';
      $tab[14]['field']='name';
      $tab[14]['name']=$LANG['common'][17];
      
      $tab[15]['table']='glpi_plugin_racks_rackstates';
      $tab[15]['field']='name';
      $tab[15]['name']=$LANG['state'][0];
      
      $tab[30]['table'] = $this->getTable();
      $tab[30]['field'] = 'id';
      $tab[30]['name'] = $LANG['common'][2];

      $tab[80]['table'] = 'glpi_entities';
      $tab[80]['field'] = 'completename';
      $tab[80]['name'] = $LANG['entity'][0];
		
		return $tab;
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (!$withtemplate) {
         if ($item->getType()=='PluginRacksRack') {
            return $LANG['title'][26];
         }
      }
      return '';
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;
      
      $self=new self();
      
      if ($item->getType()=='PluginRacksRack') {
         $self->showTotal($item->getField('id'));

      }
      return true;
   }

   function defineTabs($options=array()) {
		global $LANG;
		
		$ong = array();
		
		$this->addStandardTab('PluginRacksRack', $ong,$options);
      $this->addStandardTab('PluginRacksRack_Item', $ong,$options);
      $this->addStandardTab('Infocom', $ong, $options);
      $this->addStandardTab('Document',$ong,$options);
      $this->addStandardTab('Note',$ong,$options);
      $this->addStandardTab('Log',$ong,$options);

      return $ong;
	}
	
	function prepareInputForAdd($input) {
		
		if (isset($input["id"])&&$input["id"]>0) {
			$input["_oldID"]=$input["id"];
		}
		unset($input['withtemplate']);
		unset($input['id']);

		return $input;
	}
	
	function post_addItem() {
		global $DB;
		
		// Manage add from template
		if (isset($this->input["_oldID"])) {
			
			// ADD Documents			
			$query="SELECT documents_id 
				FROM glpi_documents_items 
				WHERE items_id='".$this->input["_oldID"]."' AND itemtype='".$this->getType()."';";
			$result=$DB->query($query);
			if ($DB->numrows($result)>0) {
	
				while ($data=$DB->fetch_array($result)) {
					$docitem=new Document_Item();
					$docitem->add(array('documents_id' => $data["documents_id"],
							'itemtype' => $this->getType(),
							'items_id' => $this->fields["id"]));
				}
			}
		}
	}
	
	function showForm($ID, $options=array()) {
		global $LANG, $CFG_GLPI;
		
		if (!$this->canView()) return false;
		
		if ($ID > 0) {
			$this->check($ID,'r');
		} else {
			// Create item 
			$this->check(-1,'w');
			$this->getEmpty();
		} 

      if (isset($options['withtemplate']) && $options['withtemplate'] == 2) {
         $template = "newcomp";
         $datestring = $LANG['computers'][14]." : ";
         $date = Html::convDateTime($_SESSION["glpi_currenttime"]);
      } else if (isset($options['withtemplate']) && $options['withtemplate'] == 1) {
         $template = "newtemplate";
         $datestring = $LANG['computers'][14]." : ";
         $date = Html::convDateTime($_SESSION["glpi_currenttime"]);
      } else {
         $datestring = $LANG['common'][26].": ";
         $date = Html::convDateTime($this->fields["date_mod"]);
         $template = false;
      }
      $PluginRacksConfig = new PluginRacksConfig();
    
      $this->showTabs($options);
      $this->showFormHeader($options);
    
      //ligne 1
      echo "<tr class='tab_bg_1'>";

      echo "<td>" . $LANG['plugin_racks'][5] . ": </td><td>";
      $objectName = autoName($this->fields["name"], "name", ($template === "newcomp"), 
                                                   $this->getType(),$this->fields["entities_id"]);
      Html::autocompletionTextField($this,'name',array('value'=>$objectName));
      echo "</td>";
      
      echo "<td>" . $LANG['plugin_racks']['device'][5] . ": </td><td>";
      Dropdown::showInteger("rack_size", $this->fields["rack_size"], 1, 100, 1);		
      echo " U</td>";

      echo "</tr>";
      
      //ligne 2
      echo "<tr class='tab_bg_1'>";

      echo "<td>" . $LANG['common'][5] . ": </td><td>";
      Dropdown::show('Manufacturer', array('name' => "manufacturers_id",
                                          'value' => $this->fields["manufacturers_id"]));
      echo "</td>";
      
      echo "<td >" . $LANG['common'][15] . ": 	</td>";
      echo "<td>";
      Dropdown::show('Location', array('name' => "locations_id",
                     'value' => $this->fields["locations_id"], 
                     'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "</tr>";
      
      //ligne 3
      echo "<tr class='tab_bg_1'>";

      echo "<td >".$LANG['common'][10].": </td>";
      echo "<td >";
      User::dropdown(array('name' => 'users_id_tech',
                           'value' => $this->fields["users_id_tech"],
                           'right' => 'interface',
                           'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td>" . $LANG['plugin_racks'][11].": ";
      echo "</td><td>";
      $PluginRacksRoomLocation = new PluginRacksRoomLocation();
      $PluginRacksRoomLocation->dropdownRoomLocations("plugin_racks_roomlocations_id",
                                                      $this->fields["plugin_racks_roomlocations_id"],
                                                      $this->fields["entities_id"]);
      echo "</td>";
      
      echo "</tr>";

      //ligne 4
      echo "<tr class='tab_bg_1'>";

      echo "<td>".$LANG['common'][109].":</td><td>";
      Dropdown::show('Group', array('name' => "groups_id_tech",
                                    'value' => $this->fields["groups_id_tech"], 
                                    'entity' => $this->fields["entities_id"],
                                    'condition' => '`is_assign`'));
      echo "</td>";
      
      echo "<td>" . $LANG['plugin_racks'][41] . ": </td><td>";
      echo "<input type='text' name='width' 
                              value=\"".Html::formatNumber($this->fields["width"],true)."\" size='10'>";
      $PluginRacksConfig->getUnit("size");
      echo "</td>";

      echo "</tr>";
      
      //ligne 5
      echo "<tr class='tab_bg_1'>";
      
      echo "</td>";
      echo "<td>".$LANG['common'][19]."&nbsp;:</td>";
      echo "<td >";
      Html::autocompletionTextField($this,'serial');
      echo "</td>";
      
      echo "<td>" . $LANG['plugin_racks'][40] . ": </td><td>";
      echo "<input type='text' name='height' 
                              value=\"".Html::formatNumber($this->fields["height"],true)."\" size='10'>";
      $PluginRacksConfig->getUnit("size");
      echo "</td>";
      
      echo "</tr>";

      //ligne 6
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['common'][20]."&nbsp;: </td>";
      echo "<td>";
      Html::autocompletionTextField($this,'otherserial');
      echo "</td>";

      echo "<td>" . $LANG['plugin_racks']['device'][6] . ": </td><td>";
      echo "<input type='text' name='weight' 
                              value=\"".Html::formatNumber($this->fields["weight"],true)."\" size='10'>";
      $PluginRacksConfig->getUnit("weight");
      echo "</td>";

      echo "</tr>";
      
      //ligne 7
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_racks'][12]."&nbsp;: </td>";
      echo "<td>";
      Dropdown::show('PluginRacksRackModel', array('name' => "plugin_racks_rackmodels_id",
                                          'value' => $this->fields["plugin_racks_rackmodels_id"]));
      echo "</td>";

      echo "<td>" . $LANG['plugin_racks'][42] . ": </td><td>";
      echo "<input type='text' name='depth' 
                              value=\"".Html::formatNumber($this->fields["depth"],true)."\" size='10'>";
      $PluginRacksConfig->getUnit("size");
      echo "</td>";
      
      echo "</tr>";
      
      //ligne 8
      echo "<tr class='tab_bg_1'>";
      
      echo "<td >";
      echo $LANG['common'][17].": </td><td>";
      Dropdown::show('PluginRacksRackType',
                  array('value'  => $this->fields["plugin_racks_racktypes_id"]));
      echo "</td>";
      
      echo "<td >";
      echo $LANG['state'][0].": </td><td>";
      Dropdown::show('PluginRacksRackState',
                  array('value'  => $this->fields["plugin_racks_rackstates_id"]));
      echo "</td>";
      
      echo "</tr>";
      //ligne 9
      
      echo "<tr class='tab_bg_1'>";

      echo "<td colspan='2' class='center'>";
      echo $LANG['common'][26]."&nbsp;:".Html::convDateTime($this->fields["date_mod"]);
      echo "</td>";
      echo "<td>$datestring</td><td>$date\n";
      if (!$template&&!empty($this->fields['template_name'])) {
         echo "&nbsp;&nbsp;&nbsp;(".$LANG['common'][13].": ".$this->fields['template_name'].")";
      }
      echo "</td>";
      echo "</tr>\n";
      
      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
	}
	
	/*function listTemplates($target,$add=0) {
      global $DB,$CFG_GLPI, $LANG;

      $query = "SELECT * 
          FROM `".$this->getTable()."` 
          WHERE `is_template` = '1' 
          AND `entities_id` = '".$_SESSION["glpiactive_entity"]."' 
          ORDER BY `name` ";

      if ($result = $DB->query($query)) {

         echo "<div align='center'><table class='tab_cadre' width='50%'>";
         if ($add) {
            echo "<tr><th>".$LANG['common'][7]." - ".$LANG['plugin_racks']['title'][2]."</th></tr>";
         } else {
            echo "<tr><th colspan='2'>";
            echo $LANG['common'][14]." - ".$LANG['plugin_racks']['title'][2]." :";
            echo "</th></tr>";
         }
         if ($add) {
            echo "<tr><td class='center tab_bg_1'>";
            echo "<a href=\"$target?id=-1&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;" .
                  $LANG['common'][31] . "&nbsp;&nbsp;&nbsp;</a>";
            echo "</td></tr>";
         }
      
         while ($data= $DB->fetch_array($result)) {

            $templname = $data["template_name"];
            if ($_SESSION["glpiis_ids_visible"]||empty($data["template_name"]))
            $templname.= "(".$data["id"].")";

            echo "<tr>";
            echo "<td class='center tab_bg_1'>";
            if (!$add) {
               echo "<a href=\"$target?id=".$data["id"]."&amp;withtemplate=1\">";
               echo "&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";
               echo "<td class='center tab_bg_2'>";
               if ($data["template_name"]!="Blank Template") {
                  echo "<b><a href=\"setup.templates.php?id=".$data["id"]."&amp;delete=delete\">";
                  echo $LANG['buttons'][6]."</a></b>";
               } else {
                  echo "&nbsp;";
               }
               echo "</td>";
            } else {
               echo "<a href=\"$target?id=".$data["id"]."&amp;withtemplate=2\">
                                    &nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";
            }
            echo "</tr>";
         }
         if (!$add) {
            echo "<tr>";
            echo "<td colspan='2' class='tab_bg_2 center'>";
            echo "<b><a href=\"$target?withtemplate=1\">".$LANG['common'][9]."</a></b>";
            echo "</td>";
            echo "</tr>";
         }
         echo "</table></div>";
      }
   }*/
  
   function showTotal($ID) {
      global $DB,$CFG_GLPI,$LANG;
    
      $this->GetfromDB($ID);

      $PluginRacksConfig = new PluginRacksConfig();
    
      $query = "SELECT SUM(`weight`) AS total_weight, SUM(`amps`) AS total_amps, 
                     SUM(`flow_rate`) AS total_flow_rate, 
                     SUM(`dissipation`) AS total_dissipation, 
                     COUNT(`first_powersupply`) AS total_alim1, 
                     COUNT(`second_powersupply`) AS total_alim2 
        FROM `glpi_plugin_racks_racks_items` 
        WHERE `plugin_racks_racks_id` = '$ID' " ;
              
      $result = $DB->query($query);
    
      $query_alim1 = "SELECT COUNT(`first_powersupply`) AS total_alim1 
        FROM `glpi_plugin_racks_racks_items` 
        WHERE `plugin_racks_racks_id` = '$ID' AND `first_powersupply` > 0 ";
              
      $result_alim1 = $DB->query($query_alim1);
    
      $query_alim2 = "SELECT COUNT(`second_powersupply`) AS total_alim2 
        FROM `glpi_plugin_racks_racks_items` 
        WHERE `plugin_racks_racks_id` = '$ID' AND `second_powersupply` > 0 ";
              
      $result_alim2 = $DB->query($query_alim2);
    
      echo "<form><div class='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='6'>".$LANG['plugin_racks'][24].":</th></tr><tr>";
      //echo "<th colspan='3'>".$LANG['plugin_racks'][16]."</th>";
      echo "<th>".$LANG['plugin_racks'][45]."</th>";
      echo "<th>".$LANG['plugin_racks']['device'][3]."</th>"; // Courant consomm�
      echo "<th>".$LANG['plugin_racks']['device'][4]."</th>"; 
      echo "<th>".$LANG['plugin_racks']['device'][7]."</th>"; 
      echo "<th>".$LANG['plugin_racks']['device'][6]."</th>";
      echo "</tr>";
    
      $total_cordons=0;
      while ($data_alim1= $DB->fetch_array($result_alim1)) {
         $total_cordons+=$data_alim1["total_alim1"];
      }
      while ($data_alim2= $DB->fetch_array($result_alim2)) {
         $total_cordons+=$data_alim2["total_alim2"];
      }	
      while ($data= $DB->fetch_array($result)) {
         echo "<tr class='tab_bg_1'>";

         echo "<td class='center'>".$total_cordons."</td>";	
         echo "<td class='center'><b>".Html::formatNumber($data["total_amps"],true)." amps</b></td>";
         echo "<td class='center'><b>".Html::formatNumber($data["total_dissipation"],true)." ";
         $PluginRacksConfig->getUnit("dissipation");
         echo "</b></td>";
         echo "<td class='center'><b>".Html::formatNumber($data["total_flow_rate"],true)." ";
         $PluginRacksConfig->getUnit("rate");
         echo "</b></td>";

         $total_weight=$data["total_weight"]+$this->fields['weight'];
         echo "<td class='center'><b>".Html::formatNumber($total_weight,true)." ";
         $PluginRacksConfig->getUnit("weight");
         echo "</b></td>";

         echo "</tr>";
      }
      echo "</table></div>";
      Html::closeForm();  
   }
   
   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
   **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }


   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }
}

?>