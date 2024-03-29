<?php
/*
 * @version $Id: consumable.class.php 20130 2013-02-04 16:55:15Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

//!  Consumable Class
/**
  This class is used to manage the consumables.
  @see ConsumableItem
  @author Julien Dombre
 */
class Consumable extends CommonDBTM {

   // From CommonDBTM
   protected $forward_entity_to = array('Infocom');
   var $no_form_page = false;


   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>0) {
         return $LANG['Menu'][32];
      }
      return $LANG['consumables'][0];
   }


   function canCreate() {
      return Session::haveRight('consumable', 'w');
   }


   function canView() {
      return Session::haveRight('consumable', 'r');
   }


   function cleanDBonPurge() {
      global $DB;

      $query = "DELETE
                FROM `glpi_infocoms`
                WHERE (`items_id` = '".$this->fields['id']."'
                       AND `itemtype` = '".$this->getType()."')";
      $result = $DB->query($query);
   }


   function prepareInputForAdd($input) {

      $item = new ConsumableItem();
      if ($item->getFromDB($input["tID"])) {
         return array("consumableitems_id" => $item->fields["id"],
                      "entities_id"        => $item->getEntityID(),
                      "date_in"            => date("Y-m-d"));
      }
      return array();
   }


   function post_addItem() {

      $ic = new Infocom();
      $ic->cloneItem('ConsumableItem', $this->fields["consumableitems_id"], $this->fields['id'],
                     $this->getType());
   }


   function restore($input, $history=1) {
      global $DB;

      $query = "UPDATE `".$this->getTable()."`
                SET `date_out` = NULL
                WHERE `id` = '".$input["id"]."'";

      if ($result = $DB->query($query)) {
         return true;
      }
      return false;
   }


   /**
    * UnLink a consumable linked to a printer
    *
    * UnLink the consumable identified by $ID
    *
    *@param $ID : consumable identifier
    *@param $itemtype : itemtype of who we give the consumabl
    *@param $items_id : ID of the item giving the consumable
    *
    *@return boolean
    *
    **/
   function out($ID, $itemtype='', $items_id=0) {
      global $DB;

      if (!empty($itemtype) && $items_id > 0) {
         $query = "UPDATE `".$this->getTable()."`
                   SET `date_out` = '".date("Y-m-d")."',
                       `itemtype` = '$itemtype',
                       `items_id` = '$items_id'
                   WHERE `id` = '$ID'";

         if ($result = $DB->query($query)) {
            return true;
         }
      }
      return false;
   }


   /**
    * count how many consumable for a consumable type
    *
    * count how many consumable for the consumable item $tID
    *
    *@param $tID integer: consumable item identifier.
    *
    *@return integer : number of consumable counted.
    *
    **/
   static function getTotalNumber($tID) {
      global $DB;

      $query = "SELECT `id`
                FROM `glpi_consumables`
                WHERE `consumableitems_id` = '$tID'";
      $result = $DB->query($query);

      return $DB->numrows($result);
   }


   /**
    * count how many old consumable for a consumable type
    *
    * count how many old consumable for the consumable item $tID
    *
    *@param $tID integer: consumable item identifier.
    *
    *@return integer : number of old consumable counted.
    *
    **/
   static function getOldNumber($tID) {
      global $DB;

      $query = "SELECT `id`
                FROM `glpi_consumables`
                WHERE (`consumableitems_id` = '$tID'
                       AND `date_out` IS NOT NULL)";
      $result = $DB->query($query);

      return $DB->numrows($result);
   }


   /**
    * count how many consumable unused for a consumable type
    *
    * count how many consumable unused for the consumable item $tID
    *
    *@param $tID integer: consumable item identifier.
    *
    *@return integer : number of consumable unused counted.
    *
    **/
   static function getUnusedNumber($tID) {
      global $DB;

      $query = "SELECT `id`
                FROM `glpi_consumables`
                WHERE (`consumableitems_id` = '$tID'
                       AND `date_out` IS NULL)";
      $result = $DB->query($query);

      return $DB->numrows($result);
   }


   /**
    * Get the consumable count HTML array for a defined consumable type
    *
    * @param $tID integer: consumable item identifier.
    * @param $alarm_threshold integer: threshold alarm value.
    * @param $nohtml integer: Return value without HTML tags.
    *
    * @return string to display
    *
    **/
   static function getCount($tID, $alarm_threshold, $nohtml=0) {
      global $LANG;

      $out = "";
      // Get total
      $total = self::getTotalNumber($tID);

      if ($total!=0) {
         $unused = self::getUnusedNumber($tID);
         $old    = self::getOldNumber($tID);

         $highlight="";
         if ($unused<=$alarm_threshold) {
            $highlight = "class='tab_bg_1_2'";
         }
         if (!$nohtml) {
            $out .= "<div $highlight>".$LANG['common'][33]."&nbsp;:&nbsp;$total";
            $out .= "<span class='b very_small_space'>";
            if ($unused>1) {
               $out .= $LANG['consumables'][14];
            } else {
               $out .= $LANG['consumables'][20];
            }
            $out .= "&nbsp;:&nbsp;$unused</span>";
            $out .= "<span class='very_small_space'>";
            if ($old>1) {
               $out .= $LANG['consumables'][22];
            } else {
               $out .= $LANG['consumables'][21];
            }
            $out .= "&nbsp;:&nbsp;$old</span></div>";
         } else {
            if ($unused>1) {
               $out .= $LANG['consumables'][14];
            } else {
               $out .= $LANG['consumables'][20];
            }
            $out .= " : $unused   ";
            if ($old>1) {
               $out .= $LANG['consumables'][22];
            } else {
               $out .= $LANG['consumables'][21];
            }
            $out .= " : $old";
         }
      } else {
         if (!$nohtml) {
            $out .= "<div class='tab_bg_1_2'><i>".$LANG['consumables'][9]."</i></div>";
         } else {
           $out .= $LANG['consumables'][9];
         }
      }
      return $out;
   }


   /**
    * Check if a Consumable is New (not used, in stock)
    *
    * @param $cID integer : consumable ID.
    *
    **/
   static function isNew($cID) {
      global $DB;

      $query = "SELECT `id`
                FROM `glpi_consumables`
                WHERE (`id` = '$cID'
                       AND `date_out` IS NULL)";
      $result = $DB->query($query);

      return ($DB->numrows($result) == 1);
   }


   /**
    * Check if a consumable is Old (used, not in stock)
    *
    *@param $cID integer : consumable ID.
    *
    **/
   static function isOld($cID) {
      global $DB;

      $query = "SELECT `id`
                FROM `glpi_consumables`
                WHERE (`id` = '$cID'
                       AND `date_out` IS NOT NULL)";
      $result = $DB->query($query);

      return ($DB->numrows($result) == 1);
   }


   /**
    * Get the localized string for the status of a consumable
    *
    *@param $cID integer : consumable ID.
    *
    *@return string : dict value for the consumable status.
    *
    **/
   static function getStatus($cID) {
      global $LANG;

      if (self::isNew($cID)) {
         return $LANG['consumables'][20];

      } else if (self::isOld($cID)) {
         return $LANG['consumables'][21];
      }
   }


   /**
    * Print out a link to add directly a new consumable from a consumable item.
    *
    * @param $consitem oject of ConsumableItem class
    *
    * @return Nothing (displays)
    **/
   static function showAddForm(ConsumableItem $consitem) {
      global $CFG_GLPI, $LANG;

      $ID = $consitem->getField('id');

      if (!$consitem->can($ID,'w')) {
         return false;
      }

      if ($ID > 0) {
         echo "<div class='firstbloc'>";
         echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/front/consumable.form.php\">";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><td class='tab_bg_2 center'>";
         echo "<input type='submit' name='add_several' value=\"".$LANG['buttons'][8]."\"
                class='submit'>";
         echo "<input type='hidden' name='tID' value='$ID'>\n";
         echo "<span class='small_space'>";
         Dropdown::showInteger('to_add',1,1,100);
         echo "</span>&nbsp;";
         echo $LANG['consumables'][16]."</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }
   }


   /**
    * Print out the consumables of a defined type
    *
    *@param $consitem object of ConsumableItem class
    *@param $show_old boolean : show old consumables or not.
    *
    *@return Nothing (displays)
    **/
   static function showForConsumableItem(ConsumableItem $consitem, $show_old=0) {
      global $DB, $CFG_GLPI, $LANG;

      $tID = $consitem->getField('id');
      if (!$consitem->can($tID,'r')) {
         return false;
      }
      $canedit = $consitem->can($tID,'w');

      $query = "SELECT count(*) AS COUNT
                FROM `glpi_consumables`
                WHERE (`consumableitems_id` = '$tID')";

      if ($result = $DB->query($query)) {
         if (!$show_old && $canedit) {
            echo "<form method='post' action='".$CFG_GLPI["root_doc"]."/front/consumable.form.php'>";
            echo "<input type='hidden' name='tID' value='$tID'>\n";
         }
         echo "<div class='spaced'><table class='tab_cadre_fixe'>";
         if (!$show_old) {
            echo "<tr><th colspan=".($canedit?'6':'4').">";
            echo self::getCount($tID, -1);
            echo "</th></tr>";
         } else { // Old
            echo "<tr><th colspan='".($canedit?'8':'6')."'>".$LANG['consumables'][35]."</th></tr>";
         }
         $i = 0;
         echo "<tr><th>".$LANG['common'][2]."</th><th>".$LANG['consumables'][23]."</th>";
         echo "<th>".$LANG['cartridges'][24]."</th>";
         if ($show_old) {
            echo "<th>".$LANG['consumables'][26]."</th>";
            echo "<th>".$LANG['consumables'][31]."</th>";
         }
         echo "<th width='200px'>".$LANG['financial'][3]."</th>";

         if ($canedit) {
            if (!$show_old && $DB->result($result,0,0)!=0) {
               echo "<th>";
   
               Dropdown::showAllItems("items_id", 0, 0,$consitem->fields["entities_id"],
                                    $CFG_GLPI["consumables_types"]);
   
               echo "&nbsp;<input type='submit' class='submit' name='give' value='".
                              $LANG['consumables'][32]."'>";
               echo "</th><th>".$LANG['rulesengine'][7]."</th>";
            } else {
               echo "<th colspan='2'>".$LANG['rulesengine'][7]."</th>";
            }
         }
         echo "</tr>";

      }

      $where     = "";
      if (!$show_old) { // NEW
         $where = " AND `date_out` IS NULL
                  ORDER BY `date_in`, `id`";
      } else { //OLD
         $where = " AND `date_out` IS NOT NULL
                  ORDER BY `date_out` DESC,
                           `date_in`,
                           `id`";
      }
      $query = "SELECT `glpi_consumables`.*
                FROM `glpi_consumables`
                WHERE `consumableitems_id` = '$tID'
                      $where";

      if ($result = $DB->query($query)) {
         $number = $DB->numrows($result);
         while ($data=$DB->fetch_array($result)) {
            $date_in  = Html::convDate($data["date_in"]);
            $date_out = Html::convDate($data["date_out"]);

            echo "<tr class='tab_bg_1'><td class='center'>".$data["id"]."</td>";
            echo "<td class='center'>".self::getStatus($data["id"])."</td>";
            echo "<td class='center'>".$date_in."</td>";
            if ($show_old) {
               echo "<td class='center'>".$date_out."</td>";
               echo "<td class='center'>";
               $item = new $data['itemtype']();
               if ($item->getFromDB($data['items_id'])) {
                  echo $item->getLink();
               }
               echo "</td>";
            }
            echo "<td class='center'>";
            Infocom::showDisplayLink('Consumable', $data["id"],1);
            echo "</td>";

            if ($canedit) {
               echo "<td class='center'>";
               if (!$show_old) {
                  echo "<input type='checkbox' name='out[".$data["id"]."]'>";
               } else {
                  echo "<a href='".
                        $CFG_GLPI["root_doc"]."/front/consumable.form.php?restore=restore&amp;id=".
                        $data["id"]."&amp;tID=$tID'>".$LANG['consumables'][37]."</a>";
               }
               echo "</td>";
               echo "<td class='center'>";
               echo "<a href='".
                     $CFG_GLPI["root_doc"]."/front/consumable.form.php?delete=delete&amp;id=".
                     $data["id"]."&amp;tID=$tID'><img title=\"".$LANG['buttons'][6]."\" alt=\"".$LANG['buttons'][6]."\" src='".$CFG_GLPI["root_doc"]."/pics/delete.png'></a>";
               echo "</td>";
            }
            echo "</tr>";
         }
      }
      echo "</table></div>";
      if (!$show_old && $canedit) {
         Html::closeForm();
      }
   }


   /**
    * Show the usage summary of consumables by user
    *
    **/
   static function showSummary() {
      global $DB, $LANG;

      if (!Session::haveRight("consumable","r")) {
         return false;
      }

      $query = "SELECT COUNT(*) AS COUNT, `consumableitems_id`, `itemtype`, `items_id`
                FROM `glpi_consumables`
                WHERE `date_out` IS NOT NULL
                      AND `consumableitems_id` IN (SELECT `id`
                                                   FROM `glpi_consumableitems`
                                                   ".getEntitiesRestrictRequest("WHERE",
                                                                           "glpi_consumableitems").")
                GROUP BY `itemtype`, `items_id`, `consumableitems_id`";
      $used = array();

      if ($result=$DB->query($query)) {
         if ($DB->numrows($result)) {
            while ($data=$DB->fetch_array($result)) {
               $used[$data['itemtype'].'####'.$data['items_id']][$data["consumableitems_id"]] = $data["COUNT"];
            }
         }
      }
      $query = "SELECT COUNT(*) AS COUNT, `consumableitems_id`
                FROM `glpi_consumables`
                WHERE `date_out` IS NULL
                      AND `consumableitems_id` IN (SELECT `id`
                                                   FROM `glpi_consumableitems`
                                                   ".getEntitiesRestrictRequest("WHERE",
                                                                           "glpi_consumableitems").")
                GROUP BY `consumableitems_id`";
      $new = array();

      if ($result=$DB->query($query)) {
         if ($DB->numrows($result)) {
            while ($data=$DB->fetch_array($result)) {
               $new[$data["consumableitems_id"]] = $data["COUNT"];
            }
         }
      }

      $types = array();
      $query = "SELECT *
                FROM `glpi_consumableitems`
                ".getEntitiesRestrictRequest("WHERE","glpi_consumableitems");

      if ($result=$DB->query($query)) {
         if ($DB->numrows($result)) {
            while ($data=$DB->fetch_array($result)) {
               $types[$data["id"]] = $data["name"];
            }
         }
      }
      asort($types);
      $total = array();
      if (count($types)>0) {
         // Produce headline
         echo "<div class='center'><table class='tab_cadrehov'><tr>";

         // Type
         echo "<th>".$LANG['consumables'][31]."</th>";

         foreach ($types as $key => $type) {
            echo "<th>$type</th>";
            $total[$key] = 0;
         }
         echo "<th>".$LANG['common'][33]."</th>";
         echo "</tr>";

         // new
         echo "<tr class='tab_bg_2'><td class='b'>".$LANG['consumables'][1]."</td>";
         $tot = 0;
         foreach ($types as $id_type => $type) {
            if (!isset($new[$id_type])) {
               $new[$id_type] = 0;
            }
            echo "<td class='center'>".$new[$id_type]."</td>";
            $total[$id_type] += $new[$id_type];
            $tot += $new[$id_type];
         }
         echo "<td class='center'>".$tot."</td>";
         echo "</tr>";

         foreach ($used as $itemtype_items_id => $val) {
            echo "<tr class='tab_bg_2'><td>";
            list($itemtype,$items_id) = explode('####',$itemtype_items_id);
            $item = new $itemtype();
            if ($item->getFromDB($items_id)) {
               echo $item->getTypeName().' - '.$item->getNameID();
            }
            echo "</td>";
            $tot = 0;
            foreach ($types as $id_type => $type) {
               if (!isset($val[$id_type])) {
                  $val[$id_type] = 0;
               }
               echo "<td class='center'>".$val[$id_type]."</td>";
               $total[$id_type] += $val[$id_type];
               $tot += $val[$id_type];
            }
         echo "<td class='center'>".$tot."</td>";
         echo "</tr>";
         }
         echo "<tr class='tab_bg_1'><td class='b'>".$LANG['common'][33]."</td>";
         $tot = 0;
         foreach ($types as $id_type => $type) {
            $tot += $total[$id_type];
            echo "<td class='center'>".$total[$id_type]."</td>";
         }
         echo "<td class='center'>".$tot."</td>";
         echo "</tr>";
         echo "</table></div>";

      } else {
         echo "<div class='center b'>".$LANG['consumables'][7]."</div>";
      }
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (!$withtemplate && Session::haveRight("consumable","r")) {
         switch ($item->getType()) {
            case 'ConsumableItem' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry($LANG['Menu'][32], self::countForConsumableItem($item));
               }
               return $LANG['Menu'][32];
         }
      }
      return '';
   }


   static function countForConsumableItem(ConsumableItem $item) {

      $restrict = "`glpi_consumables`.`consumableitems_id` = '".$item->getField('id') ."'";

      return countElementsInTable(array('glpi_consumables'), $restrict);
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

         switch ($item->getType()) {
            case 'ConsumableItem' :
               self::showAddForm($item);
               self::showForConsumableItem($item);
               self::showForConsumableItem($item, 1);
               return true;
         }
   }

}

?>