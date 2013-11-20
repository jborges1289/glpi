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

class PluginRacksRack_Item extends CommonDBTM {
   
   // From CommonDBRelation
   public $itemtype_1 = "PluginRacksRack";
   public $items_id_1 = 'plugin_racks_racks_id';

   public $itemtype_2 = 'itemtype';
   public $items_id_2 = 'items_id';
   
   function canCreate() {
      return plugin_racks_haveRight('racks', 'w');
   }

   function canView() {
      return plugin_racks_haveRight('racks', 'r');
   }
   
   static function countForRack(PluginRacksRack $item, $face) {

      return countElementsInTable('glpi_plugin_racks_racks_items',
                                  "`plugin_racks_racks_id` = '".$item->getID()."' 
                                  AND `faces_id` ='".$face."'");
   }


   static function countForItem(CommonDBTM $item) {

      return countElementsInTable('glpi_plugin_racks_racks_items',
                                  "`itemtype`='".$item->getType()."Model'
                                   AND `items_id` = '".$item->getID()."'");
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (!$withtemplate) {
         if ($item->getType()=='PluginRacksRack'
             && count(PluginRacksRack::getTypes(false))) {
            
            $ong = array();
            if ($_SESSION['glpishow_count_on_tabs']) {
               $ong[1]=self::createTabEntry($LANG['plugin_racks'][46], self::countForRack($item,
                                                                        PluginRacksRack::FRONT_FACE));
            } else {
               $ong[1] = $LANG['plugin_racks'][46];
            }
            if ($_SESSION['glpishow_count_on_tabs']) {
               $ong[2]=self::createTabEntry($LANG['plugin_racks'][47], self::countForRack($item,
                                                                        PluginRacksRack::BACK_FACE));
            } else {
               $ong[2] = $LANG['plugin_racks'][47];
            }

            return $ong;

         } else if (in_array($item->getType(), PluginRacksRack::getTypes(true))
                    && $this->canView()) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginRacksRack::getTypeName(2), self::countForItem($item));
            }
            return PluginRacksRack::getTypeName(2);
         }
      }
      return '';
   }

//$PluginRacksRack_Item->showPeripherals($_POST["id"]);

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;
      
      $self=new self();
      
      if ($item->getType()=='PluginRacksRack') {
         switch ($tabnum) {
            case 1 :
               $self->showItemFromPlugin($item->getField('id'), PluginRacksRack::FRONT_FACE);
               return true;

            case 2 :
               $self->showItemFromPlugin($item->getField('id'), PluginRacksRack::BACK_FACE);
               return true;
         }
         break;

      } else if (in_array($item->getType(), PluginRacksRack::getTypes(true))) {
         
         $self->showPluginFromItems(get_class($item),$item->getField('id'));
         $self->showform($CFG_GLPI["root_doc"]."/plugins/racks/front/rack_item.form.php",
                           $item->getField('id'),get_class($item));

      }
      return true;
   }

	function getEmpty() {
		$this->fields["plugin_racks_racks_id"] = 0;
		$this->fields["items_id"] = 0;
		$this->fields["itemtype"] = 0;
		$this->fields["position"] = 1;
		$this->fields["alim1"] = 0;
		$this->fields["alim2"] = 0;
		$this->fields["amps"] = 0.0000;
		$this->fields["flow_rate"] = 0.0000;
		$this->fields["dissipation"] = 0.0000;
		$this->fields["weight"] = 0.0000;
		return true;		
	}
	
	/**
    * Hook called After an item is uninstall or purge
    */
   static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         array('itemtype' => $item->getType(),
               'items_id' => $item->getField('id'))
      );
   }
	
	function showForm ($target,$items_id,$itemtype) {
      global $LANG,$DB;
      
      $itemtype=$itemtype."Model";
      
      $query = "SELECT `id` "
          ." FROM `".$this->getTable()."` "
          ." WHERE `items_id` = '$items_id' "
          ." AND `itemtype` = '$itemtype'  ";
           
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number!=0) {
         $data = $DB->fetch_array($result);
         $id=$data["id"];
         if ($id>0) {
             $spotted = true;
             $this->getFromDB($id);
         } else {
             $spotted = true;
             $this->getEmpty();
         }
         $PluginRacksConfig = new PluginRacksConfig();
         
         echo "<br><div align='center'>";
         echo "<form method='post' action=\"$target\">";
         echo "<table class='tab_cadre_fixe' width='50%' cellpadding='5'>";
         echo "<tr><th colspan='2'>".$LANG['plugin_racks'][50]."</th></tr>";
         
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . $LANG['plugin_racks'][9] . "</td>";
         echo "<td class='center'>";									
         Dropdown::show('PluginRacksConnection', array('name' => "first_powersupply",
                                                   'value' => $this->fields["first_powersupply"]));
         echo "</td>";
         echo "</tr>";
         
         $PluginRacksItemSpecification = new PluginRacksItemSpecification();
         if ($PluginRacksItemSpecification->checkAlimNumber($id) > 1) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . $LANG['plugin_racks'][10] . "</td>";
            echo "<td class='center'>";
            Dropdown::show('PluginRacksConnection', array('name' => "second_powersupply",
                                                   'value' => $this->fields["second_powersupply"]));
            echo "</td>";
            echo "</tr>";
         }
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . $LANG['plugin_racks']['device'][3] . "&nbsp;(amps)</td>";
         echo "<td class='center'>";
         echo "<input type='text' name='amps' 
                                 value=\"".Html::formatNumber($this->fields["amps"],true)."\" size='10'>";
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>".$LANG['plugin_racks']['device'][4]." ("; // Dissipation calorifique
         $PluginRacksConfig->getUnit("dissipation");
         echo ")</td>";
         echo "<td class='center'>";
         echo "<input type='text' name='dissipation' 
                           value=\"".Html::formatNumber($this->fields["dissipation"],true)."\" size='10'>";
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>".$LANG['plugin_racks']['device'][7]." ("; // Débit d'air frais
         $PluginRacksConfig->getUnit("rate");
         echo ")</td>";
         echo "<td class='center'>";
         echo "<input type='text' name='flow_rate' 
                           value=\"".Html::formatNumber($this->fields["flow_rate"],true)."\" size='10'>";
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>".$LANG['plugin_racks']['device'][6]." ("; // poids
         $PluginRacksConfig->getUnit("weight");
         echo ")</td>";
         echo "<td class='center'>";
         echo "<input type='text' name='weight' 
                              value=\"".Html::formatNumber($this->fields["weight"],true)."\" size='10'>";
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td class='tab_bg_2 top' colspan='2'><div align='center'>";
         echo "<input type='hidden' name='id' value='".$id."'>";
         echo "<input type='submit' name='update' value='".$LANG['buttons'][7]."' class='submit'>";
         echo "</td>";
         echo "</tr>";

         echo "</td></tr>";

         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }
	}
	
	function findItems($DB,$type) {
      $queryBranch='';
      // Recherche les items
      $queryitems= "SELECT 
            `items_id` FROM `".$this->getTable()."` 
            WHERE `itemtype` = '".$type."' ";
      if ($resultitems = $DB->query($queryitems)) {
         while ($dataitems = $DB->fetch_array($resultitems)) {
            $items=$dataitems["items_id"];
            $queryBranch .= ",$items";
         }
      }

      return $queryBranch;
   }
	
	function checkPosition($plugin_racks_racks_id,$rack_size, $face,$ID, $itemtype, $spec, $position) {
      global $DB;

      if ($position<=$rack_size && $position>0) {
      // Recherche de la taille de l'equipement pour verifier qu'il y a la place pour l'inserer
      
         $modelfield = getForeignKeyFieldForTable(getTableForItemType($itemtype));
         $table = getTableForItemType(substr($itemtype, 0, -5));
         
         $query = "SELECT `" . $table . "`.`id`, `glpi_plugin_racks_itemspecifications`.*
            FROM `" . $table . "`, `glpi_plugin_racks_itemspecifications` " .
            "WHERE `" . $table . "`.`id` = '$ID' " .
            "AND `glpi_plugin_racks_itemspecifications`.`model_id` = `" . $table . "`.`".$modelfield."` " .
            "AND `glpi_plugin_racks_itemspecifications`.`itemtype` = '".$itemtype."'";

         $result = $DB->query($query);

         $server_size=array();

         while($device=$DB->fetch_array($result)) {
            $device_size=$device["size"];
            for ($y=0;$y<$device_size;$y++)
               $server_size[]=$position-$y;
         }


         //tableau des emplacements occupes
         if ($face==PluginRacksRack::FRONT_FACE) {
           $otherface=PluginRacksRack::BACK_FACE;
         } else {
           $otherface=PluginRacksRack::FRONT_FACE;
         }
         $query_position = "SELECT `".$this->getTable()."`.`position` AS position, 
                              `glpi_plugin_racks_itemspecifications`.`size` AS size, 
                              `glpi_plugin_racks_itemspecifications`.`length` AS length,
                              `".$this->getTable()."`.`faces_id` AS faces_id
                FROM `".$this->getTable()."`,`glpi_plugin_racks_itemspecifications`
                WHERE `glpi_plugin_racks_itemspecifications`.`id` = `".$this->getTable()."`.`plugin_racks_itemspecifications_id`
                AND `".$this->getTable()."`.`items_id` != '".$ID."'
                AND `".$this->getTable()."`.`plugin_racks_racks_id` = '".$plugin_racks_racks_id."'
                AND (`".$this->getTable()."`.`faces_id` ='".$face."' 
                OR `".$this->getTable()."`.`faces_id` = '".$otherface."') ";

         $result_position = $DB->query($query_position);

         $position_table=array();
         $length_table=array();
         while($data_position=$DB->fetch_array($result_position)) {

            for ($i=0;$i<$data_position['size'];$i++)
               if (($data_position['length']==1 && $data_position['faces_id']==$otherface) || $data_position['faces_id']==$face)
                  $position_table[]=$data_position['position']-$i;
               $length_table[$data_position['position']]=$data_position['length'];
         }
         
         $space_left = 0;
         
         $PluginRacksItemSpecification = new PluginRacksItemSpecification;
         $PluginRacksItemSpecification->GetfromDB($spec);
         $length=$PluginRacksItemSpecification->fields["length"];
         
         if(isset($length_table[$position]) && $length_table[$position]==0 && $length==1)
            $space_left = -1;

         foreach ($position_table as $key => $val) {
            foreach ($server_size as $cle => $value) {
               if ($val==$value)
                  $space_left = -1;
            }
         }
         //reste a gerer les inclusions en 1 avec size > 1
         if ($device_size>1 && ($position==1 || $position==01))
            $space_left = -1;
      } else {
         $space_left = -1;
      }

      return $space_left;
   }

	function addItem($plugin_racks_racks_id, $rack_size, $face, $ID, $itemtype, $spec, $position) {
      
      $space_left = $this->checkPosition($plugin_racks_racks_id,$rack_size, $face,$ID, $itemtype, $spec, $position);

      if ($space_left >= 0) {

         $values["plugin_racks_racks_id"] = $plugin_racks_racks_id;
         $values["faces_id"] = $face;
         $values["items_id"] = $ID;
         $values["plugin_racks_itemspecifications_id"] = $spec;
         $values["itemtype"] = $itemtype;
         $values["position"] = $position;

         $PluginRacksItemSpecification = new PluginRacksItemSpecification;
         $PluginRacksItemSpecification->GetfromDB($spec);

         $values["amps"]=$PluginRacksItemSpecification->fields["amps"];
         $values["flow_rate"]=$PluginRacksItemSpecification->fields["flow_rate"];
         $values["dissipation"] =$PluginRacksItemSpecification->fields["dissipation"];
         $values["weight"] = $PluginRacksItemSpecification->fields["weight"];

         $this->add($values);
      }

      return $space_left;
  }

	function updateItem($ID, $itemtype, $plugin_racks_itemspecifications_id,$plugin_racks_racks_id, $rack_size, $faces_id, $items_id, $position) {

      $space_left = $this->checkPosition($plugin_racks_racks_id,$rack_size, $faces_id, $items_id, $itemtype, $plugin_racks_itemspecifications_id, $position);

      if ($space_left >= 0) {
         $values["id"] = $ID;
         $values["position"] = $position;
         $this->update($values);
      }
      return $space_left;
   }
  
   function showAllItems($myname,$value_type=0,$value=0,$entity_restrict=-1) {
      global $DB,$LANG,$CFG_GLPI;

      $types=PluginRacksRack::getTypes();
      $types[]='PluginRacksOther';
      
      $rand=mt_rand();
      $options=array();

      echo "<table border='0'><tr><td>\n";

      echo "<select name='type' id='itemtype$rand'>\n";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>\n";
      
      foreach ($types as $type) {
         
         $item = new $type();
         echo "<option value='".$type."Model'>".$item->getTypeName()."</option>\n";
      }

      echo "</select>";

      $params=array('modeltable'=>'__VALUE__',
     'value'=>$value,
     'myname'=>$myname,
     'entity_restrict'=>$entity_restrict,
     );
      Ajax::UpdateItemOnSelectEvent("itemtype$rand","show_$myname$rand",$CFG_GLPI["root_doc"]."/plugins/racks/ajax/dropdownAllItems.php",$params);

      echo "</td><td>\n"	;
      echo "<span id='show_$myname$rand'>&nbsp;</span>\n";
      echo "</td></tr></table>\n";

      if ($value>0) {
         echo "<script type='text/javascript' >\n";
         echo "document.getElementById('itemtype$rand').value='".$value_type."';";
         echo "</script>\n";

         $params["modeltable"]=$value_type;
         Ajax::updateItem("show_$myname$rand",$CFG_GLPI["root_doc"]."/plugins/racks/ajax/dropdownAllItems.php",$params);

      }
      return $rand;
   }
   
   /**
    * Dropdown of values in an array
    *
    * @param $name select name
    * @param $elements array of elements to display
    * @param $value default value
    * @param $used already used elements key (do not display)
    * 
    */	
   function dropdownArrayValues($name,$elements,$used=array()){
      $rand=mt_rand();
      echo "<select name='$name' id='dropdown_".$name.$rand."'>";

      foreach($elements as $key => $val){
         if (!isset($used[$key])) {
            echo "<option value='".$key."'>".$val."</option>";				
         }
      }

      echo "</select>";	
      return $rand;
   }

   function AddItemToRack($PluginRacksRack,$instID,$face) {
      global $DB, $CFG_GLPI, $LANG;
  
      echo "<form method='post' name='racks_form' id='add_device_form'  action=\"".$CFG_GLPI["root_doc"]."/plugins/racks/front/rack.form.php\">";
    
      // Ajout element
      echo "<div class='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='5'>".$LANG['plugin_racks'][15]."</th></tr>";
      echo "<th>".$LANG['plugin_racks'][16];
      echo "&nbsp;";
      Html::showToolTip(nl2br($LANG['plugin_racks']['setup'][14]),array('link'=>'./itemspecification.php'));
      echo "</th>";
      echo "<th>".$LANG['plugin_racks'][13]."</th>" ;
      echo "<th></th>";
      echo "<input type='hidden' name='plugin_racks_racks_id' value='$instID'>";
      echo "<input type='hidden' name='rack_size' value='".$PluginRacksRack->fields['rack_size']."'>";
      
      echo "<tr>";
      echo "<td class='tab_bg_1 center'>";
      
      $this->showAllItems("itemtype",0,0,($PluginRacksRack->fields['is_recursive']?-1:$PluginRacksRack->fields['entities_id']));
      echo "</td>";
      
      if ($face==PluginRacksRack::FRONT_FACE) {
        $otherface=PluginRacksRack::BACK_FACE;
      } else {
        $otherface=PluginRacksRack::FRONT_FACE;
      }
      //tableau des emplacements occupes
      $query_position = "SELECT `".$this->getTable()."`.`position` AS position, 
                              `glpi_plugin_racks_itemspecifications`.`size` AS size, 
                              `glpi_plugin_racks_itemspecifications`.`length` AS length,
                              `".$this->getTable()."`.`faces_id` AS faces_id
                FROM `".$this->getTable()."`,`glpi_plugin_racks_itemspecifications` 
                WHERE `glpi_plugin_racks_itemspecifications`.`id` = `".$this->getTable()."`.`plugin_racks_itemspecifications_id` 
                AND `".$this->getTable()."`.`plugin_racks_racks_id` = '".$instID."' 
                AND (`".$this->getTable()."`.`faces_id` = '".$face."' 
                OR (`".$this->getTable()."`.`faces_id` = '".$otherface."')) ";
      $result_position = $DB->query($query_position);
      
      $position_table=array();

      while($data_position=$DB->fetch_array($result_position)) {

         for ($i=0;$i<$data_position['size'];$i++)
            if (($data_position['length']==1 && $data_position['faces_id']==$otherface) || $data_position['faces_id']==$face)
               $position_table[]=$data_position['position']-$i;
      }

      echo "<td class='tab_bg_1 center'>";
      
      $racks=array();
      for ($i=0;$i<=$PluginRacksRack->fields['rack_size'];$i++)
        $racks[$i]=$i;

      unset($racks[0]);
      
      $options = array_flip($position_table);
      
      $this->dropdownArrayValues("pos",$racks,$options);
      
      echo "</td>";
      
      echo "<td class='tab_bg_1 center'>";
      echo "<input type='hidden' name='faces_id' value='".$face."'>";
      echo "<input type='submit' name='addDevice' value=\"".$LANG['buttons'][8]."\" class='submit'></tr>";	
      echo "</table></div>";
      Html::closeForm();
    
   }

   function showItemFromPlugin($instID,$face) {
      global $DB, $CFG_GLPI, $LANG;

      if (!$this->canView())	return false;
    
      $rand=mt_rand();
    
      $PluginRacksRack=new PluginRacksRack();
      $PluginRacksConfig = new PluginRacksConfig();
        
      if ($PluginRacksRack->getFromDB($instID)) {

         $canedit=$PluginRacksRack->can($instID,'w');
        
         if ($canedit)
            $this->AddItemToRack($PluginRacksRack,$instID,$face);
        
         //LIST	
         echo "<form method='post' name='racks_form$rand' id='racks_form$rand'  action=\"".$CFG_GLPI["root_doc"]."/plugins/racks/front/rack.form.php\">";
        
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='12'>".$LANG['plugin_racks'][1].":</th></tr><tr>"; 
        
         if ($face==PluginRacksRack::FRONT_FACE) {
            $query = "SELECT `".$this->getTable()."`.* 
              FROM `".$this->getTable()."`,`glpi_plugin_racks_itemspecifications` 
              WHERE `".$this->getTable()."`.`plugin_racks_itemspecifications_id` = `glpi_plugin_racks_itemspecifications`.`id` AND `".$this->getTable()."`.`plugin_racks_racks_id` = '$instID' 
              AND (`".$this->getTable()."`.`faces_id` = '".PluginRacksRack::FRONT_FACE."' OR (`".$this->getTable()."`.`faces_id` ='".PluginRacksRack::BACK_FACE."' AND `glpi_plugin_racks_itemspecifications`.`length` = 1 ))
              ORDER BY `".$this->getTable()."`.`position` ASC" ;
         } else {
            $query = "SELECT `".$this->getTable()."`.* 
              FROM `".$this->getTable()."`,`glpi_plugin_racks_itemspecifications` 
              WHERE `".$this->getTable()."`.`plugin_racks_itemspecifications_id` = `glpi_plugin_racks_itemspecifications`.`id` AND `".$this->getTable()."`.`plugin_racks_racks_id` = '$instID' 
              AND (`".$this->getTable()."`.`faces_id` = '".PluginRacksRack::BACK_FACE."' OR (`".$this->getTable()."`.`faces_id` ='".PluginRacksRack::FRONT_FACE."' AND `glpi_plugin_racks_itemspecifications`.`length` = 1 ))
              ORDER BY `".$this->getTable()."`.`position` ASC" ;
         }
         $result = $DB->query($query);
         $number = $DB->numrows($result);
        
         $amps_tot = 0 ;
         $flow_rate_tot = 0 ;
         $dissip_tot = 0 ;
         $weight_tot = $PluginRacksRack->fields["weight"] ;
         $nbcordons = 0 ;
         $nbcordons_tot = 0 ;
         $cordons_amps_tot = 0 ;

         $computer_tot = 0 ;
         $computer_size_tot = 0 ;
         $networking_tot = 0 ;
         $networking_size_tot = 0 ;
         $peripheral_tot = 0 ;
         $peripheral_size_tot = 0 ;
         $others_tot = 0 ;
         $others_size_tot = 0 ;
         $next = 0 ;
         $device_size = 0 ;

         echo "<th>&nbsp;</th>";
         echo "<th>".$LANG['plugin_racks'][13]."</th>";
         echo "<th>".$LANG['common'][16]."</th>";	// nom
         echo "<th>".$LANG['common'][17]."</th>";	// type de materiel
         echo "<th>".$LANG['common'][22]."</th>";
         echo "<th>".$LANG['plugin_racks'][9]."</th>"; //alim1
         echo "<th>".$LANG['plugin_racks'][10]."</th>"; //alim2
         echo "<th>".$LANG['plugin_racks']['device'][8]."</th>"; // nb cordons
         echo "<th>".$LANG['plugin_racks']['device'][3]."<br>(amps)</th>"; // Courant consommé
         echo "<th>".$LANG['plugin_racks']['device'][4]."<br>"; // Dissipation calorifique
          echo " (";
         $PluginRacksConfig->getUnit("dissipation");
         echo ")</th>"; 
         echo "<th>".$LANG['plugin_racks']['device'][7]."<br>"; // Débit d'air frais
         echo " (";
         $PluginRacksConfig->getUnit("dissipation");
         echo ")</th>"; 
         echo "<th>".$LANG['plugin_racks']['device'][6]."<br>"; // poids
         echo " (";
         $PluginRacksConfig->getUnit("weight");
         echo ")</th>"; 
         echo "</tr>";

         for( $i = $PluginRacksRack->fields['rack_size']; $i >= 1; $i-- ) {
          
            $alim1 = 0 ;
            $alim2 = 0 ;
          
            $j = $i ;
           
            if ($i<10) $j = "0".$i ;
           
            if ($face==PluginRacksRack::FRONT_FACE) {
               // recherche de l'equipement a la position courante	 					
               $query = "SELECT `".$this->getTable()."`.* 
              FROM `".$this->getTable()."`,`glpi_plugin_racks_itemspecifications` 
              WHERE `".$this->getTable()."`.`plugin_racks_itemspecifications_id` = `glpi_plugin_racks_itemspecifications`.`id` AND `".$this->getTable()."`.`plugin_racks_racks_id` = '$instID' 
              AND (`".$this->getTable()."`.`faces_id` = '".PluginRacksRack::FRONT_FACE."' OR (`".$this->getTable()."`.`faces_id` = '".PluginRacksRack::BACK_FACE."' AND `glpi_plugin_racks_itemspecifications`.`length` = 1)) AND `position` ='$j'
              ORDER BY `".$this->getTable()."`.`position` ASC" ;
            } else {
               $query = "SELECT `".$this->getTable()."`.* 
              FROM `".$this->getTable()."`,`glpi_plugin_racks_itemspecifications` 
              WHERE `".$this->getTable()."`.`plugin_racks_itemspecifications_id` = `glpi_plugin_racks_itemspecifications`.`id` AND `".$this->getTable()."`.`plugin_racks_racks_id` = '$instID' 
              AND (`".$this->getTable()."`.`faces_id` = '".PluginRacksRack::BACK_FACE."' OR (`".$this->getTable()."`.`faces_id` = '".PluginRacksRack::FRONT_FACE."' AND `glpi_plugin_racks_itemspecifications`.`length` = 1)) AND `position` ='$j'
              ORDER BY `".$this->getTable()."`.`position` ASC" ;
            }	
            $result = $DB->query($query);
            $number = $DB->numrows($result);
          
            // Si equipement
            if ( $number != 0 ) {
          
               $data=$DB->fetch_array($result);
               
               $class = substr($data["itemtype"], 0, -5);
               $item = new $class();
               $table = getTableForItemType($class);
               $r = $DB->query("SELECT * FROM `".$table."` WHERE `id` = '".$data["items_id"]."' ");
               $device = $DB->fetch_array($r);
               
               $modelclass=$data["itemtype"];
               $model_table = getTableForItemType($modelclass);
               $modelfield = getForeignKeyFieldForTable(getTableForItemType($modelclass));
               
               $query = "SELECT `".$model_table."`.`name` AS model,`".$model_table."`.`id` AS modelid, `glpi_plugin_racks_itemspecifications`.* FROM `glpi_plugin_racks_itemspecifications` "		
                   ." LEFT JOIN `".$model_table."` ON (`glpi_plugin_racks_itemspecifications`.`model_id` = `".$model_table."`.`id`)"
                   ." LEFT JOIN `".$table."` ON (`glpi_plugin_racks_itemspecifications`.`model_id` = `".$table."`.`".$modelfield."` AND `glpi_plugin_racks_itemspecifications`.`itemtype` = '".$modelclass."')"
                   ." WHERE `".$table."`.`id` = '".$data["items_id"]."' ";
               //Rack recursivity .getEntitiesRestrictRequest(" AND ",$table,'','',$item->maybeRecursive())
               $res = $DB->query($query);
               $device_spec = $DB->fetch_array($res);
               $device_size = $device_spec["size"] ;
     
               if ($data["first_powersupply"] > 0) {
                  $nbcordons +=1;
                  $nbcordons_tot +=1;
               }
               
               if ($data["second_powersupply"] > 0) {
                  $nbcordons +=1;
                  $nbcordons_tot +=1;
               }
               if ($data["itemtype"]=='ComputerModel') {
                  $computer_tot += 1 ;
                  $computer_size_tot += $device_spec["size"] ;
               } else if ($data["itemtype"]=='PeripheralModel') {
                  $peripheral_tot += 1 ;
                  $peripheral_size_tot += $device_spec["size"] ;
               } else if ($data["itemtype"]=='NetworkEquipmentModel') {
                  $networking_tot += 1 ;	
                  $networking_size_tot += $device_spec["size"] ;
               } else if ($data["itemtype"]=='PluginRacksOtherModel') {
                  $others_tot += 1 ;	
                  $others_size_tot += $device_spec["size"] ;
               }
               
               for( $t = 0; $t < $device_size; $t++ ) {
                 
                  if ($t==0) {
                   
                     if ($data["itemtype"]=='ComputerModel') {
                        echo "<tr class='plugin_racks_device_computers_color'>";
                     } else if ($data["itemtype"]=='PeripheralModel') {
                        echo "<tr class='plugin_racks_device_peripherals_color'>";
                     } else if ($data["itemtype"]=='NetworkEquipmentModel') {
                        echo "<tr class='plugin_racks_device_networking_color'>";
                     } else if ($data["itemtype"]=='PluginRacksOtherModel') {
                        echo "<tr class='plugin_racks_device_others_color'>";
                     }
                     
                     
                     echo "<td width='10' rowspan='".$device_size."'>";
                     $sel="";                         					 
                     if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
                     echo "<input type='checkbox' name='item[".$data["id"]."]' value='1' $sel>";
                     echo "</td>";
                   
                     echo "<td class='center'>U";
                     if ($canedit){	
                        echo "<input type='text' size='3' name='position".$data["id"]."' value='$j'>";
                        echo "&nbsp;<input type='image' name='updateDevice[".$data["id"]."]' value=\"".$LANG['buttons'][7]."\" src='" . $CFG_GLPI["root_doc"] . "/pics/actualiser.png' class='calendrier'>";
                     } else {
                        echo $j;
                     }	
                     echo "</td>" ;
                     $link=Toolbox::getItemTypeFormURL(substr($data["itemtype"], 0, -5));
                     if ($data["itemtype"]!='PluginRacksOtherModel')
                        $name= "<a href=\"".$link."?id=".$data["items_id"]."\">".$device["name"]."</a>";
                     else
                        $name= $device["name"];
                     echo "<input type='hidden' name='plugin_racks_racks_id' value='".$PluginRacksRack->fields['id']."'>";
                     echo "<input type='hidden' name='rack_size' value='".$PluginRacksRack->fields['rack_size']."'>";
                     echo "<input type='hidden' name='type".$data["id"]."' value='".$data["itemtype"]."'>";
                     echo "<input type='hidden' name='items_id".$data["id"]."' value='".$data["items_id"]."'>";
                     echo "<input type='hidden' name='plugin_racks_itemspecifications_id".$data["id"]."' value='".$data["plugin_racks_itemspecifications_id"]."'>";
                     echo "<input type='hidden' name='update_server' value='1'>"; 	   
                     echo "<input type='hidden' name='faces_id' value='".$face."'>";
                     if ($data["itemtype"]!='PluginRacksOtherModel') {
                        echo "<td class='center' ".(isset($data['is_deleted'])&&$data['is_deleted']?"class='tab_bg_2_2'":"")." >".$name."</td>";								
                     } else {
                        $PluginRacksOther = new PluginRacksOther();
                        $PluginRacksOther->GetfromDB($data["items_id"]);
                        echo "<td class='center'><input type='text' name='name".$data["id"]."' value='".$PluginRacksOther->fields["name"]."' size='10'></td>";
                     }
                     echo "<td class='center'>".$item->getTypeName()."</td>";          			

                     $linkmodel=Toolbox::getItemTypeFormURL($modelclass);
                     echo "<td class='center'><a href=\"".$linkmodel."?id=".$device_spec["modelid"]."\">" . $device_spec["model"] . " (" . $device_spec["size"] . "U)</a></td>";

                     echo "<td class='center'>";									
                     echo Dropdown::getDropdownName("glpi_plugin_racks_connections",$data["first_powersupply"]);
                     echo "</td>";
                     echo "<td class='center'>";
                     echo Dropdown::getDropdownName("glpi_plugin_racks_connections",$data["second_powersupply"]);
                     echo "</td>";

                     echo "<td class='center'>".$nbcordons."</td>";
                   
                     if ($data["amps"]=='0.0000')
                        $amps = $device_spec["amps"];
                     else
                        $amps = $data["amps"];
                   
                     $cordons_amps_tot+=	$amps*$nbcordons;
                     echo "<td class='center'>".Html::formatNumber($amps,true)."</td>";
                     if ($data["dissipation"]=='0.0000')
                        $dissipation = $device_spec["dissipation"];
                     else
                        $dissipation = $data["dissipation"];
                       
                     echo "<td class='center'>".Html::formatNumber($dissipation,true)."</td>";
                     if ($data["flow_rate"]=='0.0000')
                        $flow_rate = $device_spec["flow_rate"];
                     else
                        $flow_rate = $data["flow_rate"];

                     echo "<td class='center'>".Html::formatNumber($flow_rate,true)."</td>";
                     if ($data["weight"]=='0.0000')
                        $weight = $device_spec["weight"];
                     else
                        $weight = $data["weight"];

                     echo "<td class='center'>".Html::formatNumber($weight,true)."</td>";
                     
                     echo "</tr>";

                     if ($data["amps"]=='0.0000')
                        $amps_tot += $device_spec["amps"];
                     else
                        $amps_tot += $data["amps"];

                     if ($data["flow_rate"]=='0.0000')
                        $flow_rate_tot += $device_spec["flow_rate"];
                     else
                        $flow_rate_tot += $data["flow_rate"];

                     if ($data["dissipation"]=='0.0000')
                        $dissip_tot += $device_spec["dissipation"];
                     else
                        $dissip_tot += $data["dissipation"];

                     if ($data["weight"]=='0.0000')
                        $weight_tot += $device_spec["weight"];
                     else
                        $weight_tot += $data["weight"];
                   
                  } else {
                     $name=$j-$t;
                     if ($data["itemtype"]=='ComputerModel') {
                        echo "<tr class='plugin_racks_device_computers_color'>";
                     } else if ($data["itemtype"]=='PeripheralModel') {
                        echo "<tr class='plugin_racks_device_peripherals_color'>";
                     } else if ($data["itemtype"]=='NetworkEquipmentModel') {
                        echo "<tr class='plugin_racks_device_networking_color'>";
                     } else if ($data["itemtype"]=='PluginRacksOtherModel') {
                        echo "<tr class='plugin_racks_device_others_color'>";
                     }
                     echo "<td class='center'>U$name</td><td colspan='10'></td></tr>";
                  }
               }
               
               if ($device_size>1)
                  for( $d = 1; $d < $device_size; $d++ )
                     $i--;
            
            } else { // Si pas d'equipement a la position courante
            
               echo "<tr class='tab_bg_1'><td></td><td class='center'>U$j</td><td colspan='10'></td></tr>";
            }
          
            $nbcordons = 0 ;
         }

         echo "<tr class='tab_bg_1'>";
         echo "<td></td>";
         echo "<td class='center'><b>".$LANG['plugin_racks'][24]."</b></td>";
         echo "<td colspan='3' class='center'><b>";
         if ($computer_tot!=0)
            echo $LANG['plugin_racks'][25]." : ".$computer_tot." (".$computer_size_tot."U)<br>";
         if ($networking_tot!=0)
            echo $LANG['plugin_racks'][26]." : ".$networking_tot." (".$networking_size_tot."U)<br>";
         if ($peripheral_tot!=0)
            echo $LANG['plugin_racks'][27]." : ".$peripheral_tot." (".$peripheral_size_tot."U)<br>";
         if ($others_tot!=0)
            echo $LANG['plugin_racks'][44]." : ".$others_tot." (".$others_size_tot."U)<br>";
        
         //number of U availables
         $available=$PluginRacksRack->fields['rack_size']-$computer_size_tot-$networking_size_tot-$peripheral_size_tot-$others_size_tot;
        
         if ($available > 0)
            echo "<font color='green'>".$available." ".$LANG['plugin_racks'][39]."</font>";
         else
            echo "<font color='red'>".$available." ".$LANG['plugin_racks'][39]."</font>";
         echo "</b></td>";
        
         echo "<td colspan='3' class='center'><b>".$LANG['plugin_racks']['device'][9]." : ".$nbcordons_tot."</b><br>";
         echo "<b>".$LANG['plugin_racks']['device'][10]." : ".$cordons_amps_tot." amps</b></td>";
         echo "<td class='center'><b>".Html::formatNumber($amps_tot,true)." amps</b></td>";
         echo "<td class='center'><b>".Html::formatNumber($dissip_tot,true)." ";
         $PluginRacksConfig->getUnit("dissipation");
         echo "</b></td>";
         echo "<td class='center'><b>".Html::formatNumber($flow_rate_tot,true)." ";
         $PluginRacksConfig->getUnit("rate");
         echo "</b></td>";

         echo "<td class='center'><b>".Html::formatNumber($weight_tot,true)." ";
         $PluginRacksConfig->getUnit("weight");
         echo "</b></td>";
         echo "</tr>";
          
         echo "</table></div>" ;

         if ($canedit)	{
            Html::openArrowMassives("racks_form$rand",true);
            Html::closeArrowMassives(array('deleteDevice' => $LANG['buttons'][6]));

         } else {
            echo "<input type='hidden' name='rack_size' value='".$PluginRacksRack->fields['rack_size']."'>";
            echo "</table></div>";
         }
         Html::closeForm();
          

         ////////////////////////////////////////////////////
         // Recherche des racks a gauche et a droite
         // Recuperation de la rangee
         $qPos = "SELECT `name` 
							FROM `glpi_plugin_racks_roomlocations`
							WHERE `id` = '".$PluginRacksRack->fields['plugin_racks_roomlocations_id']."' ";
         $rPos = $DB->query($qPos) ;
         $nbPos = $DB->numrows($rPos) ;
         $pos = "";
         $next = "";
         $prev = "";
         if ( $nbPos != 0 ) {
            $dataPos = $DB->fetch_array( $rPos ) ;
            $pos = $dataPos['name'];
    		}
    		// Incrementation & docrementation de la lettre de rang
         if (!empty($pos)) {
            // Z is the last letter...
            if ($pos[0] != "Z") {
               $next = chr((ord( $pos[0] )+1 ));
               for($h =1; $h < strlen($pos);$h++) {
                  $next.= $pos[$h];
               }
            }
            // A is the first letter....
            if ($pos[0] != "A") {
               $prev = chr((ord($pos[0] )-1));
               for($h =1; $h < strlen($pos);$h++) {
                  $prev.= $pos[$h];
               }
            }

            $qLeft = "SELECT `glpi_plugin_racks_racks`.`id`, `glpi_plugin_racks_roomlocations`.`name`
            FROM `glpi_plugin_racks_racks`
            LEFT JOIN `glpi_plugin_racks_roomlocations` 
            ON (`glpi_plugin_racks_roomlocations`.`id` = `glpi_plugin_racks_racks`.`plugin_racks_roomlocations_id`)
            WHERE `glpi_plugin_racks_racks`.`is_deleted` = '0' AND `glpi_plugin_racks_roomlocations`.`name` = '".$prev."' "
            .getEntitiesRestrictRequest(" AND ","glpi_plugin_racks_racks",'','',$PluginRacksRack->maybeRecursive());
            $rLeft = $DB->query($qLeft) ;
            $nb = $DB->numrows($rLeft) ;

            echo "<br><br>";

            echo "<div align='center'><table border='0' width='950px'><tr><td class='left'>";		
            if ($nb != 0) {
               $left_racks = $DB->fetch_array($rLeft) ;
               echo "<a href =\"".$CFG_GLPI["root_doc"]."/plugins/racks/front/rack.form.php?id=".$left_racks['id']."\">
               <img src=\"".$CFG_GLPI["root_doc"]."/pics/left.png\" alt=''>&nbsp;".$LANG['plugin_racks'][17]." ".$left_racks['name']."</a>";
            } else {
               echo $LANG['plugin_racks'][19] ;
            }

            echo "</td>";
            echo "<td>";
            echo"</td>";

            echo "<td class='right'>" ;

            $qRight = "SELECT `glpi_plugin_racks_racks`.`id`, `glpi_plugin_racks_roomlocations`.`name` 
            FROM `glpi_plugin_racks_racks`
            LEFT JOIN `glpi_plugin_racks_roomlocations` 
            ON (`glpi_plugin_racks_roomlocations`.`id` = `glpi_plugin_racks_racks`.`plugin_racks_roomlocations_id`)
            WHERE `glpi_plugin_racks_racks`.`is_deleted` = '0' AND `glpi_plugin_racks_roomlocations`.`name` = '".$next."' "
            .getEntitiesRestrictRequest(" AND ","glpi_plugin_racks_racks",'','',$PluginRacksRack->maybeRecursive());
            $rRight = $DB->query($qRight) ;
            $nb = $DB->numrows($rRight) ;

            if ($nb != 0) {
               $right_racks = $DB->fetch_array($rRight) ;
               echo "<a href =\"".$CFG_GLPI["root_doc"]."/plugins/racks/front/rack.form.php?id=".$right_racks['id']."\">"
               .$LANG['plugin_racks'][18]." ".$right_racks['name']."&nbsp;<img src=\"".$CFG_GLPI["root_doc"]."/pics/right.png\" alt=''></a>";
            } else {
               echo $LANG['plugin_racks'][20] ;
            }
            echo "</td></tr></table></div>";
         }
      }
   }

   //peripherals
   /*function plugin_racks_showPeripherals($ID) {
      global $DB,$CFG_GLPI, $LANG;
    
      $ci=new CommonItem;
      $ci2=new CommonItem;

      $query = "SELECT `".$this->getTable()."`.*,`glpi_plugin_racks_racks`.`id` AS racksID,`glpi_connect_wire`.`end1`,`glpi_connect_wire`.`end2` "
          ." FROM `".$this->getTable()."` 
          LEFT JOIN `glpi_plugin_racks_racks` ON (`glpi_plugin_racks_racks`.`id` = `".$this->getTable()."`.`plugin_racks_racks_id`)
          LEFT JOIN `glpi_connect_wire` ON (`glpi_connect_wire`.`end2` = `".$this->getTable()."`.`items_id`) "
          ." WHERE `glpi_plugin_racks_racks`.`id` = '$ID'"
        ." AND `glpi_connect_wire`.`type` = '".'Peripheral'."' "
          . getEntitiesRestrictRequest(" AND ","glpi_plugin_racks_racks",'','',isset(true);
          $query .=" GROUP BY `end1` ORDER BY `end1`";
           
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number!=0) {
      
         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='7'>".$LANG['plugin_racks'][48].":</th></tr>";
         echo "<tr><th>Nom</th>";
         echo "<th>Serial</th>";
         echo "<th>num inventaire</th>";
         echo "<th>Materiel associe</th>";
         echo "</tr>";
    
         for ($i=0; $i < $number; $i++) {
            $tID = $DB->result($result, $i, "end1");

            $ci->getFromDB('Peripheral',$tID);		
        
            echo "<tr class='tab_bg_1".($ci->getField('is_deleted')=='1'?"_2":"")."'>";
            echo "<td class='center b'>";
            echo $ci->getLink();
            echo " - ".Dropdown::getDropdownName("glpi_states",$ci->getField('states_id'));

            echo "</td><td>".$ci->getField('serial');
            echo "</td><td>".$ci->getField('otherserial');
            echo "</td><td>";
            $query_connect = "SELECT `glpi_computers`.`name` FROM `glpi_connect_wire` LEFT JOIN `glpi_computers` ON (`glpi_computers`.`id` = `glpi_connect_wire`.`end2`)
            WHERE `glpi_connect_wire`.`end1` = '$tID' AND `glpi_connect_wire`.`type` = '".'Peripheral'."' ORDER BY `glpi_computers`.`name` ";
            if ($result_connect=$DB->query($query_connect)) {
               $number_connect = $DB->numrows($result_connect);
               if ($result_connect>0) {
                  while ($data= $DB->fetch_array($result_connect)) {
                     //$ci2->getFromDB('Computer',);
                     echo $data["name"]."<br>";
                  }
               }
            } 
            echo "</td></tr>";
         }	
         echo "</table>";	
      }
   }*/
   
   function showPluginFromItems($itemtype,$ID,$withtemplate='') {
      global $DB,$CFG_GLPI, $LANG;
      
      $item = new $itemtype();
      if ($item->getFromDB($ID))
         $entity=$item->fields["entities_id"];
      
      $itemtype=$itemtype."Model";
      
      $query = "SELECT `glpi_plugin_racks_racks`.*,`".$this->getTable()."`.`id` AS items_id, `".$this->getTable()."`.`position`, `".$this->getTable()."`.`faces_id` "
          ." FROM `glpi_plugin_racks_racks`,`".$this->getTable()."` "
          ." WHERE `".$this->getTable()."`.`items_id` = '$ID'"
          ." AND `".$this->getTable()."`.`itemtype` = '$itemtype'"
        ." AND `glpi_plugin_racks_racks`.`id` = `".$this->getTable()."`.`plugin_racks_racks_id` "
          . getEntitiesRestrictRequest(" AND ","glpi_plugin_racks_racks",'','',true);
           
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='7'>".$LANG['plugin_racks'][14].":</th></tr>";
      echo "<tr><th>".$LANG['plugin_racks'][5]."</th>";
      echo "<th>".$LANG['plugin_racks']['device'][2]."</th>";
      echo "<th>".$LANG['plugin_racks'][13]."</th>";
      echo "<th>".$LANG['plugin_racks'][3]."</th>";
      echo "<th>".$LANG['plugin_racks'][11]."</th>";
      echo "<th>".$LANG['plugin_racks'][2]."</th>";
      if ($this->canCreate())
         echo "<th>&nbsp;</th>";
      echo "</tr>";
     
      while ($data= $DB->fetch_array($result)) {
         echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";
         echo "<td class='center'><a href='".$CFG_GLPI["root_doc"]."/plugins/racks/front/rack.form.php?id=".$data["id"]."'>".$data["name"];
         if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
         echo "</a></td>";
         if ($data["faces_id"]==PluginRacksRack::FRONT_FACE) {
            $faces_id=$LANG['plugin_racks'][46]; 
         } else { 
            $faces_id=$LANG['plugin_racks'][47];
         }
         echo "<td class='center'>".$faces_id."</td>";
         echo "<td class='center'>".$data["position"]."</td>";
         echo "<td>".Dropdown::getDropdownName("glpi_locations",$data["locations_id"])."</td>";
         echo "<td class='center'>".Dropdown::getDropdownName("glpi_plugin_racks_roomlocations",$data["plugin_racks_roomlocations_id"],0)."</td>";
         echo "<td>".Dropdown::getDropdownName("glpi_manufacturers",$data["manufacturers_id"])."</td>";
   
         if ($this->canCreate() && ($withtemplate<2)) {
            
            echo "<td class='center tab_bg_2'>";
            Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/racks/front/rack.form.php',
                                    'deleteitem',
                                    $LANG['buttons'][6],
                                    array('id' => $data['items_id']));
            echo "</td>";
         }
         echo "</tr>";
      }
      if (!empty($withtemplate))
         echo "<input type='hidden' name='is_template' value='1'>";
   
      echo "</table></div>";
   }
}

?>