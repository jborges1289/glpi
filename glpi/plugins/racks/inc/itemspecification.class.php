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

class PluginRacksItemSpecification extends CommonDBTM {
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_racks']['setup'][12];
   }
   
   function canCreate() {
      return plugin_racks_haveRight('model', 'w');
   }

   function canView() {
      return plugin_racks_haveRight('model', 'r');
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (!$withtemplate) {
         if (in_array($item->getType(), self::getModelClasses(true))
                    && $this->canView()) {
            /*if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginAppliancesAppliance::getTypeName(2), self::countForItem($item));
            }*/
            //return PluginAppliancesAppliance::getTypeName(2);
            return $LANG['plugin_racks'][50];
         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;
      
      $self=new self();
      
      if (in_array($item->getType(), self::getModelClasses(true))) {
         
         $self->showForm("", array('items_id' => $item->getField('id') ,'itemtype' => get_class($item), 'target' => $CFG_GLPI['root_doc']."/plugins/racks/front/itemspecification.form.php"));

      }
      return true;
   }
	
	function checkAlimNumber($id) {
      global $DB;
      
      $query_device = "SELECT `plugin_racks_itemspecifications_id` FROM `glpi_plugin_racks_racks_items` " .
              "WHERE `id` = '" . $id . "'";
      $result_device = $DB->query($query_device);

      while($model=$DB->fetch_array($result_device)) {
      
         $result = $DB->query("SELECT nb_alim FROM `".$this->getTable()."`
                     WHERE `id` = '" . $model['plugin_racks_itemspecifications_id'] . "' ");
         if ($DB->numrows($result) > 0)
           return $DB->result($result,0,"nb_alim");
         else
           return 0;
      }
   }
   
   function checkIfSpecUsedByRacks($id) {
      global $DB;
      
      $query = "SELECT `id` FROM `glpi_plugin_racks_racks_items` " .
              "WHERE `plugin_racks_itemspecifications_id` = '" . $id . "'";
      $result = $DB->query($query);
      
      if ($DB->numrows($result) > 0)
        return true;
      else
        return false;
   }
	
	static function getModelClasses () {
	
      static $types = array(
         'ComputerModel','NetworkEquipmentModel','PeripheralModel','PluginRacksOtherModel'
         );

      foreach ($types as $key=>$type) {
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
  
   function UpdateItemSpecification($input) {
      global $DB;

      $modelfield = getForeignKeyFieldForTable(getTableForItemType($input['itemtype']));
      $itemtype = substr($input['itemtype'], 0, -5);
      $table = getTableForItemType($itemtype);
      
      //selection de tous les materiels lies au modele
      $query_spec = "SELECT *
            FROM `".$this->getTable()."`
            WHERE `id` = '" . $input["id"] . "' ";
      $result_spec = $DB->query($query_spec);

      while($device=$DB->fetch_array($result_spec)) {

         $query_device = "SELECT `" . $table . "`.`id` FROM `" . $table . "`, `".$this->getTable()."` " .
              "WHERE `".$this->getTable()."`.`model_id` = `" . $table . "`.`".$modelfield."`
              AND `".$this->getTable()."`.`id` = '" . $input["id"] . "'";
         $result_device = $DB->query($query_device);

         while($model=$DB->fetch_array($result_device)) {

            //detail de chaque materiel dans la baie
            $query_content = "SELECT * FROM `glpi_plugin_racks_racks_items` " .
              "WHERE `itemtype` = '" . $input['itemtype'] . "'
              AND `items_id` = '" . $model['id'] . "' ";
            $result_content = $DB->query($query_content);

            while($content=$DB->fetch_array($result_content)) {

               if ($device["amps"]==$content["amps"]
               && $device["flow_rate"]==$content["flow_rate"]
               && $device["dissipation"]==$content["dissipation"]
               && $device["weight"]==$content["weight"]) {

                  //si les params du materiel sont les memes que le modele alors update
                  $PluginRacksRack_Item = new PluginRacksRack_Item();
                  $values["id"] = $content['id'];
                  $values["amps"] = $input["amps"];
                  $values["flow_rate"] = $input["flow_rate"];
                  $values["dissipation"] = $input["dissipation"];
                  $values["weight"] = $input["weight"];

                  $PluginRacksRack_Item->update($values);

               }
            }
         }
      }
      $this->update($input);
   }
  
   function deleteItemSpecification($ID) {
      global $DB;

      $query_spec = "SELECT *
            FROM `".$this->getTable()."`
            WHERE `id` = '" . $ID . "' ";
      $result_spec = $DB->query($query_spec);

      while($device=$DB->fetch_array($result_spec)) {
         $itemtype=$device['itemtype'];
      
         $modelfield = getForeignKeyFieldForTable(getTableForItemType($itemtype));
         $table = getTableForItemType(substr($itemtype, 0, -5));
         
         //delete items from racks
         $query_device = "SELECT `" . $table . "`.`id` FROM `" . $table . "`, `".$this->getTable()."` " .
                "WHERE `".$this->getTable()."`.`model_id` = `" . $table . "`.`".$modelfield."`
                AND `".$this->getTable()."`.`id` = '" . $ID . "'";
         $result_device = $DB->query($query_device);
         while($model=$DB->fetch_array($result_device)) {
            $query = "DELETE
                FROM `glpi_plugin_racks_racks_items`
                WHERE `itemtype` = '" . $itemtype . "'
                AND `items_id` ='" . $model['id'] . "';";
            $result = $DB->query($query);
         }
      }
      $this->delete(array("id"=>$ID));
   }
   
   function getFromDBByModel($itemtype,$id) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->getTable()."`
					WHERE `itemtype` = '$itemtype' 
					AND `model_id` = '$id' ";
		if ($result = $DB->query($query)) {
			if ($DB->numrows($result) != 1) {
				return false;
			}
			$this->fields = $DB->fetch_assoc($result);
			if (is_array($this->fields) && count($this->fields)) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	
	function defineTabs($options=array()) {
		global $LANG;
		/* principal */
		$ong[1] = $LANG['title'][26];

		return $ong;
	}
	
   function showForm ($ID, $options=array()) {
		global $LANG,$DB;
      
      if (!$this->canView())
			return false;
		
		$itemtype = -1;
      if (isset($options['itemtype'])) {
         $itemtype = $options['itemtype'];
      }
      
      $items_id = -1;
      if (isset($options['items_id'])) {
         $items_id = $options['items_id'];
      }

      if($this->getFromDBByModel($itemtype,$items_id))
         $ID = $this->fields["id"];
      
		if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w',$input);
      }
      
      $this->showFormHeader($options);
      
      if ($ID > 0) {
         echo "<input type='hidden' name='itemtype' value='".$this->fields["itemtype"]."'><input type='hidden' name='model_id' value='".$this->fields["model_id"]."'>";
      } else {
         echo "<input type='hidden' name='itemtype' value='$itemtype'><input type='hidden' name='model_id' value='$items_id'>";
      }
      $PluginRacksConfig = new PluginRacksConfig();

      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . $LANG['plugin_racks']['device'][5] . "</td>";
      echo "<td>";
      if ($this->checkIfSpecUsedByRacks($ID))
         echo $this->fields["size"];
      else
         Dropdown::showInteger("size", $this->fields["size"], 1, 100, 1);
      echo " U</td>";
      
      echo "<td>" . $LANG['plugin_racks'][23] . "</td>";
      echo "<td>";
      Dropdown::showYesNo("length",$this->fields["length"]);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . $LANG['plugin_racks'][45] . "</td>";
      echo "<td>";
      Dropdown::showInteger("nb_alim", $this->fields["nb_alim"], 0, 2, 1);
      echo "</td>";
      
      echo "<td>" . $LANG['plugin_racks']['device'][3] . "</td>";
      echo "<td>";
      echo "<input type='text' name='amps' value=\"".Html::formatNumber($this->fields["amps"],true)."\" size='10'>  (amps)";
      echo "</td>";
      
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_racks']['device'][4]; // Dissipation calorifique
      echo "</td>";
      echo "<td>";
      echo "<input type='text' name='dissipation' value=\"".Html::formatNumber($this->fields["dissipation"],true)."\" size='10'> (";
      $PluginRacksConfig->getUnit("dissipation");
      echo ")</td>";
      
      echo "<td>".$LANG['plugin_racks']['device'][7]; // Débit d'air frais
      echo "</td>";
      echo "<td>";
      echo "<input type='text' name='flow_rate' value=\"".Html::formatNumber($this->fields["flow_rate"],true)."\" size='10'> (";
      $PluginRacksConfig->getUnit("rate");
      echo ")</td>";
      
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_racks']['device'][6]; // poids
      echo "</td>";
      echo "<td>";
      echo "<input type='text' name='weight' value=\"".Html::formatNumber($this->fields["weight"],true)."\" size='10'> (";
      $PluginRacksConfig->getUnit("weight");
      echo ")</td>";
      
      echo "<td></td>";
      echo "<td></td>";
      
      echo "</tr>";
      
      $this->showFormButtons($options);

	}
	
	function showList($target,$id,$itemtype,$withtemplate='') {
		
		$rand=mt_rand();
		
		echo "<div align='center'>";
		echo "<div align='center'><form method='post' name='massiveaction_form$rand' id='massiveaction_form$rand'  action=\"$target\">";

		$this->showModels($itemtype,$id,$rand);
	}

	function showModels($itemtype,$id,$rand) {
		global $LANG,$DB,$CFG_GLPI;

      $PluginRacksConfig = new PluginRacksConfig();
      
      $link = Toolbox::getItemTypeFormURL($itemtype);
      $table = getTableForItemType($itemtype);
      $search = Toolbox::getItemTypeSearchURL($itemtype);
      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th>&nbsp;</th>";
      echo "<th>" . $LANG['plugin_racks'][16] . "</th>";
      echo "<th>" . $LANG['plugin_racks']['device'][3] . "<br>(amps)</th>";
      echo "<th>" . $LANG['plugin_racks'][45] . "</th>";
      echo "<th>".$LANG['plugin_racks']['device'][4]."<br> ("; // Dissipation calorifique
      $PluginRacksConfig->getUnit("dissipation");
      echo ")</th>";
      echo "<th>".$LANG['plugin_racks']['device'][7]."<br> ("; // Débit d'air frais
      $PluginRacksConfig->getUnit("rate");
      echo ")</th>";
      echo "<th>" . $LANG['plugin_racks']['device'][5] . " (U)</th>";
      echo "<th>".$LANG['plugin_racks']['device'][6]."<br> ("; // poids
      $PluginRacksConfig->getUnit("weight");
      echo ")</th>";
      echo "<th>" . $LANG['plugin_racks'][23] . "</th>";
      echo "</tr>";
      $modelid=-1;
      $result = $DB->query("SELECT * 
                        FROM `".$this->getTable()."` ".($itemtype != -1?"WHERE `itemtype` = '$itemtype'":"")." ");
      while ($data = $DB->fetch_assoc($result)) {
         $modelid = $data['model_id'];
         $id=$data['id'];
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center'>";
         echo "<input type='checkbox' name='item[$id]' value='1'>";
         echo "</td>";
			echo "<td>";
         echo "<a href=\"".$link."?id=".$modelid."\">";
         echo Dropdown::getDropdownName($table,$modelid);
         echo "</a>";
			echo "</td>";
         echo "<td>" . Html::formatNumber($data['amps'],true) . "</td>";
         echo "<td>" . $data['nb_alim'] . "</td>";
         echo "<td>" . Html::formatNumber($data['dissipation'],true) . "</td>";
         echo "<td>" . Html::formatNumber($data['flow_rate'],true) . "</td>";
         echo "<td>" . $data['size'] . "</td>";
         echo "<td>" . Html::formatNumber($data['weight'],true) . "</td>";
         echo "<td>" . Dropdown::getYesNo($data['length']) . "</td>";
      }

      echo "<tr class='tab_bg_1'><td colspan='10'>";
      if ($this->canCreate()) {
         echo "<div align='center'><a onclick= \"if ( markCheckboxes('massiveaction_form$rand') ) return false;\" href='#'>" . $LANG['buttons'][18] . "</a>";
         echo " - <a onclick= \"if ( unMarkCheckboxes('massiveaction_form$rand') ) return false;\" href='#'>" . $LANG['buttons'][19] . "</a> ";
         echo "<input type='submit' name='deleteSpec' value=\"" . $LANG['buttons'][6] . "\" class='submit' ></div></td></tr>";
         
         echo "<tr class='tab_bg_1 right'><td colspan='10'>";
         echo "<a href=\"".$search."\">";
         echo $LANG['plugin_racks']['setup'][0];
         echo "</a>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";

      }
      else
         echo "</table>";
         Html::closeForm();
         echo "</div>";
	}
}

?>