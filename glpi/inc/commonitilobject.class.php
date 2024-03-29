<?php
/*
 * @version $Id: commonitilobject.class.php 20146 2013-02-06 11:07:53Z moyo $
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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// Tracking class
abstract class CommonITILObject extends CommonDBTM {

   /// Users by type
   protected $users       = array();
   public $userlinkclass  = '';
   /// Groups by type
   protected $groups      = array();
   public $grouplinkclass = '';

   /// Use user entity to select entity of the object
   protected $userentity_oncreate = false;


   // Requester
   const REQUESTER = 1;
   // Assign
   const ASSIGN    = 2;
   // Observer
   const OBSERVER  = 3;

   const MATRIX_FIELD         = '';
   const URGENCY_MASK_FIELD   = '';
   const IMPACT_MASK_FIELD    = '';
   const STATUS_MATRIX_FIELD  = '';


   function post_getFromDB() {

      if (!empty($this->grouplinkclass)) {
         $class = new $this->grouplinkclass();
         $this->groups = $class->getActors($this->fields['id']);
      }

      if (!empty($this->userlinkclass)) {
         $class = new $this->userlinkclass();
         $this->users  = $class->getActors($this->fields['id']);
      }
   }


   /**
    * Retrieve an item from the database with datas associated (hardwares)
    *
    * @param $ID ID of the item to get
    * @param $purecontent boolean : true : nothing change / false : convert to HTML display
    *
    * @return true if succeed else false
   **/
   function getFromDBwithData($ID, $purecontent) {
      global $DB, $LANG;

      if ($this->getFromDB($ID)) {
         if (!$purecontent) {
            $this->fields["content"] = nl2br(preg_replace("/\r\n\r\n/", "\r\n",
                                             $this->fields["content"]));
         }
         $this->getAdditionalDatas();
         return true;
      }
      return false;
   }


   function getAdditionalDatas() {
   }


   function canAdminActors(){
      return false;
   }


   function canAssign(){
      return false;
   }


   /**
    * Is a user linked to the object ?
    *
    * @param $type type to search (see constants)
    * @param $users_id integer user ID
    *
    * @return boolean
   **/
   function isUser($type, $users_id) {

      if (isset($this->users[$type])) {
         foreach ($this->users[$type] as $data) {
            if ($data['users_id'] == $users_id) {
               return true;
            }
         }
      }

      return false;
   }


   /**
    * get users linked to a object
    *
    * @param $type type to search (see constants)
    *
    * @return array
   **/
   function getUsers($type) {

      if (isset($this->users[$type])) {
         return $this->users[$type];
      }

      return array();
   }


   /**
    * get groups linked to a object
    *
    * @param $type type to search (see constants)
    *
    * @return array
   **/
   function getGroups($type) {

      if (isset($this->groups[$type])) {
         return $this->groups[$type];
      }

      return array();
   }


   /**
    * count users linked to object by type or global
    *
    * @param $type type to search (see constants) / 0 for all
    *
    * @return integer
   **/
   function countUsers($type=0) {

      if ($type>0) {
         if (isset($this->users[$type])) {
            return count($this->users[$type]);
         }

      } else {
         if (count($this->users)) {
            $count = 0;
            foreach ($this->users as $u) {
               $count += count($u);
            }
            return $count;
         }
      }
      return 0;
   }


   /**
    * count groups linked to object by type or global
    *
    * @param $type type to search (see constants) / 0 for all
    *
    * @return integer
   **/
   function countGroups($type=0) {

      if ($type>0) {
         if (isset($this->groups[$type])) {
            return count($this->groups[$type]);
         }

      } else {
         if (count($this->groups)) {
            $count = 0;
            foreach ($this->groups as $u) {
               $count += count($u);
            }
            return $count;
         }
      }
      return 0;
   }


   /**
    * Is a group linked to the object ?
    *
    * @param $type type to search (see constants)
    * @param $groups_id integer group ID
    *
    * @return boolean
   **/
   function isGroup($type, $groups_id) {

      if (isset($this->groups[$type])) {
         foreach ($this->groups[$type] as $data) {
            if ($data['groups_id']==$groups_id) {
               return true;
            }
         }
      }
      return false;
   }


   /**
    * Is one of groups linked to the object ?
    *
    * @param $type type to search (see constants)
    * @param $groups array of group ID
    *
    * @return boolean
   **/
   function haveAGroup($type, $groups) {

      if (is_array($groups) && count($groups) && isset($this->groups[$type])) {
         foreach ($groups as $groups_id) {
            foreach ($this->groups[$type] as $data) {
               if ($data['groups_id']==$groups_id) {
                  return true;
               }
            }
         }
      }
      return false;
   }


   /**
    * Get Default actor when creating the object
    *
    * @param $type type to search (see constants)
    *
    * @return boolean
   **/
   function getDefaultActor($type) {

      /// TODO own_ticket -> own_itilobject
      if ($type == self::ASSIGN) {
         if (Session::haveRight("own_ticket","1")) {
            return Session::getLoginUserID();
         }
      }
      return 0;
   }


   /**
    * Get Default actor when creating the object
    *
    * @param $type type to search (see constants)
    *
    * @return boolean
   **/
   function getDefaultActorRightSearch($type) {

      if ($type == self::ASSIGN) {
         return "own_ticket";
      }
      return "all";
   }


   /**
    * Count active ITIL Objects requested by an user
    *
    * @since version 0.83
    *
    * @param $users_id integer ID of the User
    *
    * @return integer
   **/
   function countActiveObjectsForUser($users_id) {

      $linkclass = new $this->userlinkclass();
      $itemtable = $this->getTable();
      $itemtype  = $this->getType();
      $itemfk    = $this->getForeignKeyField();
      $linktable = $linkclass->getTable();

      return countElementsInTable(array($itemtable,$linktable),
                                  "`$linktable`.`$itemfk` = `$itemtable`.`id`
                                    AND `$linktable`.`users_id` = '$users_id'
                                    AND `$linktable`.`type` = '".self::REQUESTER."'
                                    AND `$itemtable`.`is_deleted` = 0
                                    AND `$itemtable`.`status`
                                       NOT IN ('".implode("', '",
                                                          array_merge($this->getSolvedStatusArray(),
                                                                      $this->getClosedStatusArray())
                                                          )."')");
   }


   /**
    * Count active ITIL Objects assigned to an user
    *
    * @since version 0.83
    *
    * @param $users_id integer ID of the User
    *
    * @return integer
   **/
   function countActiveObjectsForTech($users_id) {

      $linkclass = new $this->userlinkclass();
      $itemtable = $this->getTable();
      $itemtype  = $this->getType();
      $itemfk    = $this->getForeignKeyField();
      $linktable = $linkclass->getTable();

      return countElementsInTable(array($itemtable,$linktable),
                                  "`$linktable`.`$itemfk` = `$itemtable`.`id`
                                    AND `$linktable`.`users_id` = '$users_id'
                                    AND `$linktable`.`type` = '".self::ASSIGN."'
                                    AND `$itemtable`.`is_deleted` = 0
                                    AND `$itemtable`.`status`
                                       NOT IN ('".implode("', '",
                                                          array_merge($this->getSolvedStatusArray(),
                                                                      $this->getClosedStatusArray())
                                                          )."')");
   }


   function cleanDBonPurge() {

      if (!empty($this->grouplinkclass)) {
         $class = new $this->grouplinkclass();
         $class->cleanDBonItemDelete($this->getType(), $this->fields['id']);
      }

      if (!empty($this->userlinkclass)) {
         $class = new $this->userlinkclass();
         $class->cleanDBonItemDelete($this->getType(), $this->fields['id']);
      }
   }


   function prepareInputForUpdate($input) {
      global $LANG;

      // Add document if needed
      $this->getFromDB($input["id"]); // entities_id field required
      if (!isset($input['_donotadddocs']) || !$input['_donotadddocs']) {
         $docadded = $this->addFiles($input["id"]);
      }

      if (isset($input["document"]) && $input["document"]>0) {
         $doc = new Document();
         if ($doc->getFromDB($input["document"])) {
            $docitem = new Document_Item();
            if ($docitem->add(array('documents_id' => $input["document"],
                                    'itemtype'     => $this->getType(),
                                    'items_id'     => $input["id"]))) {
               // Force date_mod of tracking
               $input["date_mod"]     = $_SESSION["glpi_currenttime"];
               $input['_doc_added'][] = $doc->fields["name"];
            }
         }
         unset($input["document"]);
      }

      if (isset($input["date"]) && empty($input["date"])) {
         unset($input["date"]);
      }

      if (isset($input["closedate"]) && empty($input["closedate"])) {
         unset($input["closedate"]);
      }

      if (isset($input["solvedate"]) && empty($input["solvedate"])) {
         unset($input["solvedate"]);
      }

      if (isset($input['_itil_requester'])) {
         if (isset($input['_itil_requester']['_type'])) {
            $input['_itil_requester']['type']                      = self::REQUESTER;
            $input['_itil_requester'][$this->getForeignKeyField()] = $input['id'];

            switch ($input['_itil_requester']['_type']) {
               case "user" :
                  if (!empty($this->userlinkclass)) {
                     if (isset($input['_itil_requester']['alternative_email'])
                         && $input['_itil_requester']['alternative_email']
                         && !NotificationMail::isUserAddressValid($input['_itil_requester']['alternative_email'])) {
                        $input['_itil_requester']['alternative_email'] = '';
                        Session::addMessageAfterRedirect($LANG['mailing'][111].' : '.$LANG['mailing'][110],
                                                         false, ERROR);
                     }
                     if ((isset($input['_itil_requester']['alternative_email'])
                          && $input['_itil_requester']['alternative_email'])
                         || $input['_itil_requester']['users_id']>0) {
                        $useractors = new $this->userlinkclass();
                        if (isset($input['_auto_update'])
                            || $useractors->can(-1,'w',$input['_itil_requester'])) {
                           $useractors->add($input['_itil_requester']);
                           $input['_forcenotif'] = true;
                        }
                     }
                  }
                  break;

               case "group" :
                  if (!empty($this->grouplinkclass) && $input['_itil_requester']['groups_id']>0) {
                     $groupactors = new $this->grouplinkclass();
                     if (isset($input['_auto_update'])
                         || $groupactors->can(-1,'w',$input['_itil_requester'])) {
                        $groupactors->add($input['_itil_requester']);
                        $input['_forcenotif'] = true;
                     }
                  }
                  break;
            }
         }
      }

      if (isset($input['_itil_observer'])) {
         if (isset($input['_itil_observer']['_type'])) {
            $input['_itil_observer']['type']                      = self::OBSERVER;
            $input['_itil_observer'][$this->getForeignKeyField()] = $input['id'];

            switch ($input['_itil_observer']['_type']) {
               case "user" :
                  if (!empty($this->userlinkclass)) {
                     if (isset($input['_itil_observer']['alternative_email'])
                         && $input['_itil_observer']['alternative_email']
                         && !NotificationMail::isUserAddressValid($input['_itil_observer']['alternative_email'])) {
                        $input['_itil_observer']['alternative_email'] = '';
                        Session::addMessageAfterRedirect($LANG['mailing'][111].' : '.$LANG['mailing'][110],
                                                         false, ERROR);
                     }
                     if ((isset($input['_itil_observer']['alternative_email'])
                          && $input['_itil_observer']['alternative_email'])
                         || $input['_itil_observer']['users_id']>0) {
                        $useractors = new $this->userlinkclass();
                        if (isset($input['_auto_update'])
                            || $useractors->can(-1,'w',$input['_itil_observer'])) {
                           $useractors->add($input['_itil_observer']);
                           $input['_forcenotif'] = true;
                        }
                     }
                  }
                  break;

               case "group" :
                   if (!empty($this->grouplinkclass) && $input['_itil_observer']['groups_id']>0) {
                     $groupactors = new $this->grouplinkclass();
                     if (isset($input['_auto_update'])
                         || $groupactors->can(-1,'w',$input['_itil_observer'])) {
                        $groupactors->add($input['_itil_observer']);
                        $input['_forcenotif'] = true;
                     }
                  }
                  break;
            }
         }
      }

      if (isset($input['_itil_assign'])) {
         if (isset($input['_itil_assign']['_type'])) {
            $input['_itil_assign']['type']                      = self::ASSIGN;
            $input['_itil_assign'][$this->getForeignKeyField()] = $input['id'];

            switch ($input['_itil_assign']['_type']) {
               case "user" :
                  if (!empty($this->userlinkclass) && $input['_itil_assign']['users_id']>0) {
                     $useractors = new $this->userlinkclass();
                     if (isset($input['_auto_update'])
                         || $useractors->can(-1,'w',$input['_itil_assign'])) {
                        $useractors->add($input['_itil_assign']);
                        $input['_forcenotif'] = true;
                        if ((!isset($input['status']) && in_array($this->fields['status'], $this->getNewStatusArray()))
                            || (isset($input['status']) && in_array($input['status'], $this->getNewStatusArray()))) {
                           $input['status'] = 'assign';
                        }
                     }
                  }
                  break;

               case "group" :
                  if (!empty($this->grouplinkclass) && $input['_itil_assign']['groups_id']>0) {
                     $groupactors = new $this->grouplinkclass();

                     if (isset($input['_auto_update'])
                         || $groupactors->can(-1,'w',$input['_itil_assign'])) {
                        $groupactors->add($input['_itil_assign']);
                        $input['_forcenotif'] = true;
                        if ((!isset($input['status']) && in_array($this->fields['status'], $this->getNewStatusArray()))
                            || (isset($input['status']) && in_array($input['status'], $this->getNewStatusArray()))) {
                           $input['status'] = 'assign';
                        }
                     }
                  }
                  break;
            }
         }
      }

      // set last updater if interactive user
      $lastupdater = Session::getLoginUserID(false);
      if (is_numeric($lastupdater)) {
         $input['users_id_lastupdater'] = $lastupdater;
      }

      if (isset($input["status"])
          && !in_array($input["status"],array_merge($this->getSolvedStatusArray(),
                                                    $this->getClosedStatusArray()))) {
         $input['solvedate'] = 'NULL';
      }

      if (isset($input["status"]) && !in_array($input["status"],$this->getClosedStatusArray())) {
         $input['closedate'] = 'NULL';
      }

      return $input;
   }


   function pre_updateInDB() {
      global $LANG, $CFG_GLPI;

      if (in_array($this->fields['status'], $this->getNewStatusArray())) {
         if (in_array("suppliers_id_assign",$this->updates)
             && $this->input["suppliers_id_assign"]>0) {

            if (!in_array('status', $this->updates)) {
               $this->oldvalues['status'] = $this->fields['status'];
               $this->updates[]           = 'status';
            }
            $this->fields['status'] = 'assign';
            $this->input['status']  = 'assign';
         }
      }

      // Setting a solution or solution type means the problem is solved
      if ((in_array("solutiontypes_id",$this->updates) && $this->input["solutiontypes_id"] >0)
          || (in_array("solution",$this->updates) && !empty($this->input["solution"]))) {

         if (!in_array('status', $this->updates)) {
            $this->oldvalues['status'] = $this->fields['status'];
            $this->updates[]           = 'status';
         }

         // Special case for Ticket : use autoclose
         if ($this->getType() == 'Ticket') {
            $autoclosedelay =  EntityData::getUsedConfig('autoclose_delay', $this->getEntityID(),
                                                         '', EntityData::CONFIG_NEVER);

            // 0 = immediatly
            if ($autoclosedelay == 0) {
               $this->fields['status'] = 'closed';
               $this->input['status']  = 'closed';
            } else {
               $this->fields['status'] = 'solved';
               $this->input['status']  = 'solved';
            }
         } else {

            $this->fields['status'] = 'solved';
            $this->input['status']  = 'solved';
         }
      }



      // Check dates change interval due to the fact that second are not displayed in form
      if (($key=array_search('date',$this->updates)) !== false
          && (substr($this->fields["date"],0,16) == substr($this->oldvalues['date'],0,16))) {
         unset($this->updates[$key]);
         unset($this->oldvalues['date']);
      }

      if (($key=array_search('closedate',$this->updates)) !== false
          && (substr($this->fields["closedate"],0,16) == substr($this->oldvalues['closedate'],0,16))) {
         unset($this->updates[$key]);
         unset($this->oldvalues['closedate']);
      }

      if (($key=array_search('due_date',$this->updates)) !== false
          && (substr($this->fields["due_date"],0,16) == substr($this->oldvalues['due_date'],0,16))) {
         unset($this->updates[$key]);
         unset($this->oldvalues['due_date']);
      }

      if (($key=array_search('solvedate',$this->updates)) !== false
          && (substr($this->fields["solvedate"],0,16) == substr($this->oldvalues['solvedate'],0,16))) {
         unset($this->updates[$key]);
         unset($this->oldvalues['solvedate']);
      }

      if (isset($this->input["status"])) {
         if ($this->input["status"] != 'waiting'
             && isset($this->input["suppliers_id_assign"])
             && $this->input["suppliers_id_assign"] == 0
             && $this->countUsers(self::ASSIGN) == 0
             && $this->countGroups(self::ASSIGN) == 0
             && !in_array($this->fields['status'],array_merge($this->getSolvedStatusArray(),
                                                              $this->getClosedStatusArray()))
            ) {

            if (!in_array('status', $this->updates)) {
               $this->oldvalues['status'] = $this->fields['status'];
               $this->updates[] = 'status';
            }
            $this->fields['status'] = 'new';
         }

         if (in_array("status",$this->updates) && in_array($this->input["status"],
                                                           $this->getSolvedStatusArray())) {
            $this->updates[]              = "solvedate";
            $this->oldvalues['solvedate'] = $this->fields["solvedate"];
            $this->fields["solvedate"]    = $_SESSION["glpi_currenttime"];
            // If invalid date : set open date
            if ($this->fields["solvedate"] < $this->fields["date"]) {
               $this->fields["solvedate"] = $this->fields["date"];
            }
         }

         if (in_array("status",$this->updates) && in_array($this->input["status"],
                                                           $this->getClosedStatusArray())) {
            $this->updates[]              = "closedate";
            $this->oldvalues['closedate'] = $this->fields["closedate"];
            $this->fields["closedate"]    = $_SESSION["glpi_currenttime"];
            // If invalid date : set open date
            if ($this->fields["closedate"] < $this->fields["date"]) {
               $this->fields["closedate"] = $this->fields["date"];
            }
            // Set solvedate to closedate
            if (empty($this->fields["solvedate"])) {
               $this->updates[]              = "solvedate";
               $this->oldvalues['solvedate'] = $this->fields["solvedate"];
               $this->fields["solvedate"]    = $this->fields["closedate"];
            }
         }

      }

      // check dates

      // check due_date (SLA)
      if ((in_array("date",$this->updates) || in_array("due_date",$this->updates))
          && !is_null($this->fields["due_date"])) { // Date set

         if ($this->fields["due_date"] < $this->fields["date"]) {
            Session::addMessageAfterRedirect($LANG['tracking'][3].$this->fields["due_date"],
                                             false, ERROR);

            if (($key=array_search('date',$this->updates)) !== false) {
               unset($this->updates[$key]);
               unset($this->oldvalues['date']);
            }
            if (($key=array_search('due_date',$this->updates)) !== false) {
               unset($this->updates[$key]);
               unset($this->oldvalues['due_date']);
            }
         }
      }

      // Status close : check dates
      if (in_array($this->fields["status"], $this->getClosedStatusArray())
          && (in_array("date",$this->updates) || in_array("closedate",$this->updates))) {

         // Invalid dates : no change
         // closedate must be > solvedate
         if ($this->fields["closedate"] < $this->fields["solvedate"]) {
            Session::addMessageAfterRedirect($LANG['tracking'][3], false, ERROR);

            if (($key=array_search('closedate',$this->updates)) !== false) {
               unset($this->updates[$key]);
               unset($this->oldvalues['closedate']);
            }
         }

         // closedate must be > create date
         if ($this->fields["closedate"]<$this->fields["date"]) {
            Session::addMessageAfterRedirect($LANG['tracking'][3], false, ERROR);
            if (($key=array_search('date',$this->updates)) !== false) {
               unset($this->updates[$key]);
               unset($this->oldvalues['date']);
            }
            if (($key=array_search('closedate',$this->updates)) !== false) {
               unset($this->updates[$key]);
               unset($this->oldvalues['closedate']);
            }
         }
      }

      if (($key=array_search('status',$this->updates)) !== false
          && $this->oldvalues['status'] == $this->fields['status']) {

         unset($this->updates[$key]);
         unset($this->oldvalues['status']);
      }

      // Status solved : check dates
      if (in_array($this->fields["status"], $this->getSolvedStatusArray())
          && (in_array("date",$this->updates) || in_array("solvedate",$this->updates))) {

         // Invalid dates : no change
         // solvedate must be > create date
         if ($this->fields["solvedate"] < $this->fields["date"]) {
            Session::addMessageAfterRedirect($LANG['tracking'][3], false, ERROR);

            if (($key=array_search('date',$this->updates)) !== false) {
               unset($this->updates[$key]);
               unset($this->oldvalues['date']);
            }
            if (($key=array_search('solvedate',$this->updates)) !== false) {
               unset($this->updates[$key]);
               unset($this->oldvalues['solvedate']);
            }
          }
      }





      // Manage come back to waiting state
      if (!is_null($this->fields['begin_waiting_date'])
          && ($key=array_search('status',$this->updates) !== false)
          && ($this->oldvalues['status'] == 'waiting'
               // From solved to another state than closed
              || (in_array($this->oldvalues["status"], $this->getSolvedStatusArray())
                   && !in_array($this->fields["status"], $this->getClosedStatusArray())))) {
         // Compute ticket waiting time use calendar if exists
         $calendars_id = EntityData::getUsedConfig('calendars_id', $this->fields['entities_id']);
         $calendar     = new Calendar();
         $delay_time   = 0;


         // Compute ticket waiting time use calendar if exists
         // Using calendar
         if ($calendars_id>0 && $calendar->getFromDB($calendars_id)) {
            $delay_time = $calendar->getActiveTimeBetween($this->fields['begin_waiting_date'],
                                                          $_SESSION["glpi_currenttime"]);
         } else { // Not calendar defined
            $delay_time = strtotime($_SESSION["glpi_currenttime"])
                           -strtotime($this->fields['begin_waiting_date']);
         }


         // SLA case : compute sla duration
         if (isset($this->fields['slas_id']) && $this->fields['slas_id']>0) {
            $sla = new SLA();
            if ($sla->getFromDB($this->fields['slas_id'])) {
               $sla->setTicketCalendar($calendars_id);
               $delay_time_sla  = $sla->getActiveTimeBetween($this->fields['begin_waiting_date'],
                                                             $_SESSION["glpi_currenttime"]);
               $this->updates[] = "sla_waiting_duration";
               $this->fields["sla_waiting_duration"] += $delay_time_sla;
            }

            // Compute new due date
            $this->updates[]          = "due_date";
            $this->fields['due_date'] = $sla->computeDueDate($this->fields['date'],
                                                             $this->fields["sla_waiting_duration"]);
            // Add current level to do
            $sla->addLevelToDo($this);

         } else {
            // Using calendar
            if ($calendars_id>0 && $calendar->getFromDB($calendars_id)) {
               if ($this->fields['due_date'] > 0) {
                  // compute new due date using calendar
                  $this->updates[]          = "due_date";
                  $this->fields['due_date'] = $calendar->computeEndDate($this->fields['due_date'],
                                                                        $delay_time);
               }

            } else { // Not calendar defined
               if ($this->fields['due_date'] > 0) {
                  // compute new due date : no calendar so add computed delay_time
                  $this->updates[]          = "due_date";
                  $this->fields['due_date'] = date('Y-m-d H:i:s',
                                                   $delay_time+strtotime($this->fields['due_date']));
               }
            }
         }

         $this->updates[]                          = "waiting_duration";
         $this->fields["waiting_duration"] += $delay_time;

         // Reset begin_waiting_date
         $this->updates[]                    = "begin_waiting_date";
         $this->fields["begin_waiting_date"] = 'NULL';
      }


      // Set begin waiting date if needed
      if (($key=array_search('status',$this->updates)) !== false
          && ($this->fields['status'] == 'waiting' || in_array($this->fields["status"], $this->getSolvedStatusArray()))) {
         $this->updates[]                    = "begin_waiting_date";
         $this->fields["begin_waiting_date"] = $_SESSION["glpi_currenttime"];

         // Specific for tickets
         if (isset($this->fields['slas_id']) && $this->fields['slas_id']>0) {
            SLA::deleteLevelsToDo($this);
         }
      }

      // solve_delay_stat : use delay between opendate and solvedate
      if (in_array("solvedate",$this->updates)) {
         $this->updates[]                  = "solve_delay_stat";
         $this->fields['solve_delay_stat'] = $this->computeSolveDelayStat();
      }
      // close_delay_stat : use delay between opendate and closedate
      if (in_array("closedate",$this->updates)) {
         $this->updates[]                  = "close_delay_stat";
         $this->fields['close_delay_stat'] = $this->computeCloseDelayStat();
      }

      // Do not take into account date_mod if no update is done
      if ((count($this->updates)==1 && ($key=array_search('date_mod',$this->updates)) !== false)) {
         unset($this->updates[$key]);
      }
   }


   function prepareInputForAdd($input) {
      global $CFG_GLPI;

      // Set default status to avoid notice
      if (!isset($input["status"])) {
         $input["status"] = "new";
      }

      if (!isset($input["urgency"]) || !($CFG_GLPI['urgency_mask']&(1<<$input["urgency"]))) {
         $input["urgency"] = 3;
      }
      if (!isset($input["impact"]) || !($CFG_GLPI['impact_mask']&(1<<$input["impact"]))) {
         $input["impact"] = 3;
      }
      if (!isset($input["priority"])) {
         $input["priority"] = $this->computePriority($input["urgency"], $input["impact"]);
      }

      // set last updater if interactive user
      $lastupdater = Session::getLoginUserID(false);
      if (is_numeric($lastupdater)) {
         $input['users_id_lastupdater'] = $lastupdater;
      }

      // No Auto set Import for external source
      if (!isset($input['_auto_import'])) {
         if (!isset($input["_users_id_requester"])) {
            if ($uid = Session::getLoginUserID()) {
               $input["_users_id_requester"] = $uid;
            }
         }
      }

      // No Auto set Import for external source
      if (($uid=Session::getLoginUserID()) && !isset($input['_auto_import'])) {
         $input["users_id_recipient"] = $uid;
      } else if (isset($input["_users_id_requester"]) && $input["_users_id_requester"]) {
         $input["users_id_recipient"] = $input["_users_id_requester"];
      }



      // No name set name
      $input["name"]    = ltrim($input["name"]);
      $input['content'] = ltrim($input['content']);
      if (empty($input["name"])) {
         $clean_content = Toolbox::stripslashes_deep($input['content']);
         $input["name"] = preg_replace('/\r\n/',' ',$clean_content);
         $input["name"] = preg_replace('/\n/',' ',$input['name']);
         // For mailcollector
         $input["name"] = preg_replace('/\\\\r\\\\n/',' ',$input['name']);
         $input["name"] = preg_replace('/\\\\n/',' ',$input['name']);
         $input["name"] = Toolbox::substr($input['name'],0,70);
         $input['name'] = Toolbox::addslashes_deep($input['name']);
      }


      // Set default dropdown
      $dropdown_fields = array('entities_id', 'suppliers_id_assign', 'itilcategories_id');
      foreach ($dropdown_fields as $field ) {
         if (!isset($input[$field])) {
            $input[$field] = 0;
         }
      }

      if (((isset($input["_users_id_assign"]) && $input["_users_id_assign"]>0)
           || (isset($input["_groups_id_assign"]) && $input["_groups_id_assign"]>0)
           || (isset($input["suppliers_id_assign"]) && $input["suppliers_id_assign"]>0))
          && in_array($input['status'], $this->getNewStatusArray())) {

         $input["status"] = "assign";
      }

      $input = $this->computeDefaultValuesForAdd($input);

      return $input;
   }

   /// Compute default values for Add (to be passed in prepareInputForAdd before and after rules if needed)
   function computeDefaultValuesForAdd($input) {
      if (!isset($input["status"])) {
         $input["status"] = "new";
      }

      if (!isset($input["date"]) || empty($input["date"])) {
         $input["date"] = $_SESSION["glpi_currenttime"];
      }

      if (isset($input["status"]) && in_array($input["status"],
                                              $this->getSolvedStatusArray())) {
         if (isset($input["date"])) {
            $input["solvedate"] = $input["date"];
         } else {
            $input["solvedate"] = $_SESSION["glpi_currenttime"];
         }
      }

      if (isset($input["status"]) && in_array($input["status"],
                                              $this->getClosedStatusArray())) {
         if (isset($input["date"])) {
            $input["closedate"] = $input["date"];
         } else {
            $input["closedate"] = $_SESSION["glpi_currenttime"];
         }
         $input['solvedate'] = $input["closedate"];
      }

      // Set begin waiting time if status is waiting
      if (isset($input["status"]) && $input["status"]=="waiting") {
         $input['begin_waiting_date'] = $input['date'];
      }

      return $input;
   }

   function post_addItem() {

      // Add document if needed, without notification
      $this->addFiles($this->fields['id'], 0);

      $useractors = NULL;
      // Add user groups linked to ITIL objects
      if (!empty($this->userlinkclass)) {
         $useractors = new $this->userlinkclass();
      }
      $groupactors = NULL;
      if (!empty($this->grouplinkclass)) {
         $groupactors = new $this->grouplinkclass();
      }

      if (!is_null($useractors)) {
         if (isset($this->input["_users_id_requester"])
             && ($this->input["_users_id_requester"]>0
                 || (isset($this->input["_users_id_requester_notif"]['alternative_email'])
                     && !empty($this->input["_users_id_requester_notif"]['alternative_email'])))) {

            $input2 = array($useractors->getItilObjectForeignKey()
                                       => $this->fields['id'],
                           'users_id'  => $this->input["_users_id_requester"],
                           'type'      => self::REQUESTER);

            if (isset($this->input["_users_id_requester_notif"])) {
               foreach ($this->input["_users_id_requester_notif"] as $key => $val) {
                  $input2[$key] = $val;
               }
            }

            $useractors->add($input2);
         }

         if (isset($this->input["_users_id_observer"])
             && ($this->input["_users_id_observer"]>0
                 || (isset($this->input["_users_id_observer_notif"]['alternative_email'])
                     && !empty($this->input["_users_id_observer_notif"]['alternative_email'])))) {
            $input2 = array($useractors->getItilObjectForeignKey()
                                       => $this->fields['id'],
                           'users_id'  => $this->input["_users_id_observer"],
                           'type'      => self::OBSERVER);

            if (isset($this->input["_users_id_observer_notif"])) {
               foreach ($this->input["_users_id_observer_notif"] as $key => $val) {
                  $input2[$key] = $val;
               }
            }

            $useractors->add($input2);
         }

         if (isset($this->input["_users_id_assign"]) && $this->input["_users_id_assign"]>0) {
            $input2 = array($useractors->getItilObjectForeignKey()
                                       => $this->fields['id'],
                           'users_id'  => $this->input["_users_id_assign"],
                           'type'      => self::ASSIGN);

            if (isset($this->input["_users_id_assign_notif"])) {
               foreach ($this->input["_users_id_assign_notif"] as $key => $val) {
                  $input2[$key] = $val;
               }
            }

            $useractors->add($input2);
         }
      }

      if (!is_null($groupactors)) {
         if (isset($this->input["_groups_id_requester"]) && $this->input["_groups_id_requester"]>0) {
            $groupactors->add(array($groupactors->getItilObjectForeignKey()
                                                => $this->fields['id'],
                                    'groups_id' => $this->input["_groups_id_requester"],
                                    'type'      => self::REQUESTER));
         }

         if (isset($this->input["_groups_id_assign"]) && $this->input["_groups_id_assign"]>0) {
            $groupactors->add(array($groupactors->getItilObjectForeignKey()
                                                => $this->fields['id'],
                                    'groups_id' => $this->input["_groups_id_assign"],
                                    'type'      => self::ASSIGN));
         }

         if (isset($this->input["_groups_id_observer"]) && $this->input["_groups_id_observer"]>0) {
            $groupactors->add(array($groupactors->getItilObjectForeignKey()
                                                => $this->fields['id'],
                                    'groups_id' => $this->input["_groups_id_observer"],
                                    'type'      => self::OBSERVER));
         }
      }

      // Additional groups actors
      if (!is_null($groupactors)) {
         // Requesters
         if (isset($this->input['_additional_groups_requesters'])
             && is_array($this->input['_additional_groups_requesters'])
             && count($this->input['_additional_groups_requesters'])) {

            $input2 = array($groupactors->getItilObjectForeignKey() => $this->fields['id'],
                            'type'                                  => self::REQUESTER);

            foreach ($this->input['_additional_groups_requesters'] as $tmp) {
               if ($tmp > 0) {
                  $input2['groups_id'] = $tmp;
                  $groupactors->add($input2);
               }
            }
         }

         // Observers
         if (isset($this->input['_additional_groups_observers'])
             && is_array($this->input['_additional_groups_observers'])
             && count($this->input['_additional_groups_observers'])) {

            $input2 = array($groupactors->getItilObjectForeignKey() => $this->fields['id'],
                            'type'                                  => self::OBSERVER);

            foreach ($this->input['_additional_groups_observers'] as $tmp) {
               if ($tmp > 0) {
                  $input2['groups_id'] = $tmp;
                  $groupactors->add($input2);
               }
            }
         }

         // Assigns
         if (isset($this->input['_additional_groups_assigns'])
             && is_array($this->input['_additional_groups_assigns'])
             && count($this->input['_additional_groups_assigns'])) {

            $input2 = array($groupactors->getItilObjectForeignKey() => $this->fields['id'],
                            'type'                                  => self::ASSIGN);

            foreach ($this->input['_additional_groups_assigns'] as $tmp) {
               if ($tmp > 0) {
                  $input2['groups_id'] = $tmp;
                  $groupactors->add($input2);
               }
            }
         }
      }

      // Additional actors : using default notification parameters
      if (!is_null($useractors)) {
         // Observers : for mailcollector
         if (isset($this->input["_additional_observers"])
             && is_array($this->input["_additional_observers"])
             && count($this->input["_additional_observers"])) {

            $input2 = array($useractors->getItilObjectForeignKey() => $this->fields['id'],
                            'type'                                 => self::OBSERVER);

            foreach ($this->input["_additional_observers"] as $tmp) {
               if (isset($tmp['users_id'])) {
                  foreach ($tmp as $key => $val) {
                     $input2[$key] = $val;
                  }
                  $useractors->add($input2);
               }
            }
         }

         if (isset($this->input["_additional_assigns"])
             && is_array($this->input["_additional_assigns"])
             && count($this->input["_additional_assigns"])) {

            $input2 = array($useractors->getItilObjectForeignKey() => $this->fields['id'],
                            'type'                                 => self::ASSIGN);

            foreach ($this->input["_additional_assigns"] as $tmp) {
               if (isset($tmp['users_id'])) {
                  foreach ($tmp as $key => $val) {
                     $input2[$key] = $val;
                  }
                  $useractors->add($input2);
               }
            }
         }

         if (isset($this->input["_additional_requesters"])
             && is_array($this->input["_additional_requesters"])
             && count($this->input["_additional_requesters"])) {

            $input2 = array($useractors->getItilObjectForeignKey() => $this->fields['id'],
                            'type'                                 => self::REQUESTER);

            foreach ($this->input["_additional_requesters"] as $tmp) {
               if (isset($tmp['users_id'])) {
                  foreach ($tmp as $key => $val) {
                     $input2[$key] = $val;
                  }
                  $useractors->add($input2);
               }
            }
         }
      }
   }


   /**
    * add files (from $_FILES) to an ITIL object
    * create document if needed
    * create link from document to ITIL object
    *
    * @param $id        Integer  ID of the ITIL object
    * @param $donotif   Boolean  if we want to raise notification
    *
    * @return array of doc added name
   **/
   function addFiles($id, $donotif=1) {
      global $LANG, $CFG_GLPI;

      if (!isset($_FILES) || !isset($_FILES['filename'])) {
         return array();
      }
      $docadded = array();
      $doc      = new Document();
      $docitem  = new Document_Item();

      // if multiple files are uploaded
      $TMPFILE = array();
      if (is_array($_FILES['filename']['name'])) {
         foreach ($_FILES['filename']['name'] as $key => $filename) {
            if (!empty($filename)) {
               $TMPFILE[$key]['filename']['name']     = $filename;
               $TMPFILE[$key]['filename']['type']     = $_FILES['filename']['type'][$key];
               $TMPFILE[$key]['filename']['tmp_name'] = $_FILES['filename']['tmp_name'][$key];
               $TMPFILE[$key]['filename']['error']    = $_FILES['filename']['error'][$key];
               $TMPFILE[$key]['filename']['size']     = $_FILES['filename']['size'][$key];
            }
         }
      } else {
         $TMPFILE = array( $_FILES );
      }

      foreach ($TMPFILE as $_FILES) {
         if (isset($_FILES['filename'])
             && count($_FILES['filename']) > 0
             && $_FILES['filename']["size"] > 0) {

            // Check for duplicate
            if ($doc->getFromDBbyContent($this->fields["entities_id"],
                                         $_FILES['filename']['tmp_name'])) {
               $docID = $doc->fields["id"];

            } else {
               $input2         = array();
               $input2["name"] = addslashes($LANG['tracking'][24]." $id");

               if ($this->getType() == 'Ticket') {
                  $input2["tickets_id"]           = $id;
               }
               $input2["entities_id"]             = $this->fields["entities_id"];
               $input2["documentcategories_id"]   = $CFG_GLPI["documentcategories_id_forticket"];
               $input2["_only_if_upload_succeed"] = 1;
               $input2["entities_id"]             = $this->fields["entities_id"];
               $docID = $doc->add($input2);
            }

            if ($docID>0) {
               if ($docitem->add(array('documents_id' => $docID,
                                       '_do_notif'    => $donotif,
                                       'itemtype'     => $this->getType(),
                                       'items_id'     => $id))) {
                  $docadded[] = stripslashes($doc->fields["name"]." - ".$doc->fields["filename"]);
               }
            }

         } else if (!empty($_FILES['filename']['name'])
                    && isset($_FILES['filename']['error'])
                    && $_FILES['filename']['error']) {
            Session::addMessageAfterRedirect($LANG['document'][46], false, ERROR);
         }
         // Only notification for the first New doc
         $donotif = 0;
      }
      unset ($_FILES);
      return $docadded;
   }


   /**
    * Compute Priority
    *
    * @param $itemtype itemtype
    * @param $urgency integer from 1 to 5
    * @param $impact integer from 1 to 5
    *
    * @return integer from 1 to 5 (priority)
   **/
   static function computeGenericPriority($itemtype, $urgency, $impact) {
      global $CFG_GLPI;

      if (isset($CFG_GLPI[constant($itemtype.'::MATRIX_FIELD')][$urgency][$impact])) {
         return $CFG_GLPI[constant($itemtype.'::MATRIX_FIELD')][$urgency][$impact];
      }
      // Failback to trivial
      return round(($urgency+$impact)/2);
   }


   /**
    * Dropdown of ITIL object priority
    *
    * @param $name select name
    * @param $value default value
    * @param $complete see also at least selection (major included)
    * @param $major display major priority
    *
    * @return string id of the select
   **/
   static function dropdownPriority($name, $value=0, $complete=false, $major=false) {
      global $LANG;

      $id = "select_$name".mt_rand();
      echo "<select id='$id' name='$name'>";
      if ($complete) {
         echo "<option value='0' ".($value==0?" selected ":"").">".$LANG['common'][66]."</option>";
         echo "<option value='-5' ".($value==-5?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][3]."</option>";
         echo "<option value='-4' ".($value==-4?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][4]."</option>";
         echo "<option value='-3' ".($value==-3?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][5]."</option>";
         echo "<option value='-2' ".($value==-2?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][6]."</option>";
         echo "<option value='-1' ".($value==-1?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][7]."</option>";
      }

      if ($complete || $major) {
         echo "<option value='6' ".($value==6?" selected ":"").">".$LANG['help'][2]."</option>";
      }

      echo "<option value='5' ".($value==5?" selected ":"").">".$LANG['help'][3]."</option>";
      echo "<option value='4' ".($value==4?" selected ":"").">".$LANG['help'][4]."</option>";
      echo "<option value='3' ".($value==3?" selected ":"").">".$LANG['help'][5]."</option>";
      echo "<option value='2' ".($value==2?" selected ":"").">".$LANG['help'][6]."</option>";
      echo "<option value='1' ".($value==1?" selected ":"").">".$LANG['help'][7]."</option>";

      echo "</select>";

      return $id;
   }


   /**
    * Get ITIL object priority Name
    *
    * @param $value status ID
   **/
   static function getPriorityName($value) {
      global $LANG;

      switch ($value) {
         case 6 :
            return $LANG['help'][2];

         case 5 :
            return $LANG['help'][3];

         case 4 :
            return $LANG['help'][4];

         case 3 :
            return $LANG['help'][5];

         case 2 :
            return $LANG['help'][6];

         case 1 :
            return $LANG['help'][7];
      }
   }


   /**
    * Dropdown of ITIL object Urgency
    *
    * @param $itemtype itemtype
    * @param $name select name
    * @param $value default value
    * @param $complete see also at least selection
    *
    * @return string id of the select
   **/
   static function dropdownGenericUrgency($itemtype, $name, $value=0, $complete=false) {
      global $LANG, $CFG_GLPI;

      $id = "select_$name".mt_rand();
      echo "<select id='$id' name='$name'>";

      if ($complete) {
         echo "<option value='0' ".($value==0?" selected ":"").">".$LANG['common'][66]."</option>";
         echo "<option value='-5' ".($value==-5?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][42]."</option>";
         echo "<option value='-4' ".($value==-4?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][43]."</option>";
         echo "<option value='-3' ".($value==-3?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][44]."</option>";
         echo "<option value='-2' ".($value==-2?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][45]."</option>";
         echo "<option value='-1' ".($value==-1?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][46]."</option>";
      }

      if (isset($CFG_GLPI[constant($itemtype.'::URGENCY_MASK_FIELD')])) {
         if ($complete || ($CFG_GLPI[constant($itemtype.'::URGENCY_MASK_FIELD')] & (1<<5))) {
            echo "<option value='5' ".($value==5?" selected ":"").">".$LANG['help'][42]."</option>";
         }

         if ($complete || ($CFG_GLPI[constant($itemtype.'::URGENCY_MASK_FIELD')] & (1<<4))) {
            echo "<option value='4' ".($value==4?" selected ":"").">".$LANG['help'][43]."</option>";
         }

         echo "<option value='3' ".($value==3?" selected ":"").">".$LANG['help'][44]."</option>";

         if ($complete || ($CFG_GLPI[constant($itemtype.'::URGENCY_MASK_FIELD')] & (1<<2))) {
            echo "<option value='2' ".($value==2?" selected ":"").">".$LANG['help'][45]."</option>";
         }

         if ($complete || ($CFG_GLPI[constant($itemtype.'::URGENCY_MASK_FIELD')] & (1<<1))) {
            echo "<option value='1' ".($value==1?" selected ":"").">".$LANG['help'][46]."</option>";
         }
      }

      echo "</select>";

      return $id;
   }


   /**
    * Get ITIL object Urgency Name
    *
    * @param $value urgency ID
   **/
   static function getUrgencyName($value) {
      global $LANG;

      switch ($value) {
         case 5 :
            return $LANG['help'][42];

         case 4 :
            return $LANG['help'][43];

         case 3 :
            return $LANG['help'][44];

         case 2 :
            return $LANG['help'][45];

         case 1 :
            return $LANG['help'][46];
      }
   }


   /**
    * Dropdown of ITIL object Impact
    *
    * @param $itemtype itemtype
    * @param $name select name
    * @param $value default value
    * @param $complete see also at least selection (major included)
    *
    * @return string id of the select
   **/
   static function dropdownGenericImpact($itemtype, $name, $value=0, $complete=false) {
      global $LANG, $CFG_GLPI;

      $id = "select_$name".mt_rand();
      echo "<select id='$id' name='$name'>";

      if ($complete) {
         echo "<option value='0' ".($value==0?" selected ":"").">".$LANG['common'][66]."</option>";
         echo "<option value='-5' ".($value==-5?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][47]."</option>";
         echo "<option value='-4' ".($value==-4?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][48]."</option>";
         echo "<option value='-3' ".($value==-3?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][49]."</option>";
         echo "<option value='-2' ".($value==-2?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][50]."</option>";
         echo "<option value='-1' ".($value==-1?" selected ":"").">".$LANG['search'][16]." ".
                $LANG['help'][51]."</option>";
      }

      if (isset($CFG_GLPI[constant($itemtype.'::IMPACT_MASK_FIELD')])) {
         if ($complete || ($CFG_GLPI[constant($itemtype.'::IMPACT_MASK_FIELD')] & (1<<5))) {
            echo "<option value='5' ".($value==5?" selected ":"").">".$LANG['help'][47]."</option>";
         }

         if ($complete || ($CFG_GLPI[constant($itemtype.'::IMPACT_MASK_FIELD')] & (1<<4))) {
            echo "<option value='4' ".($value==4?" selected ":"").">".$LANG['help'][48]."</option>";
         }

         echo "<option value='3' ".($value==3?" selected ":"").">".$LANG['help'][49]."</option>";

         if ($complete || ($CFG_GLPI[constant($itemtype.'::IMPACT_MASK_FIELD')] & (1<<2))) {
            echo "<option value='2' ".($value==2?" selected ":"").">".$LANG['help'][50]."</option>";
         }

         if ($complete || ($CFG_GLPI[constant($itemtype.'::IMPACT_MASK_FIELD')] & (1<<1))) {
            echo "<option value='1' ".($value==1?" selected ":"").">".$LANG['help'][51]."</option>";
         }
      }

      echo "</select>";

      return $id;
   }


   /**
    * Get ITIL object Impact Name
    *
    * @param $value status ID
   **/
   static function getImpactName($value) {
      global $LANG;

      switch ($value) {
         case 5 :
            return $LANG['help'][47];

         case 4 :
            return $LANG['help'][48];

         case 3 :
            return $LANG['help'][49];

         case 2 :
            return $LANG['help'][50];

         case 1 :
            return $LANG['help'][51];
      }
   }


   /**
    * Get the ITIL object status list
    *
    * @param $withmetaforsearch boolean
    *
    * @return an array
   **/
   static function getAllStatusArray($withmetaforsearch=false) {

      // To be overridden by class
      $tab = array();

      return $tab;
   }


   /**
    * Get the ITIL object closed status list
    *
    * @since version 0.83
    *
    * @return an array
   **/
   static function getClosedStatusArray() {
      // To be overridden by class
      $tab = array();

      return $tab;
   }


   /**
    * Get the ITIL object solved status list
    *
    * @since version 0.83
    *
    * @return an array
   **/
   static function getSolvedStatusArray() {
      // To be overridden by class
      $tab = array();

      return $tab;
   }

   /**
    * Get the ITIL object new status list
    *
    * @since version 0.83
    *
    * @return an array
   **/
   static function getNewStatusArray() {
      // To be overridden by class
      $tab = array();

      return $tab;
   }
   
   /**
    * Get the ITIL object process status list
    *
    * @since version 0.83
    *
    * @return an array
   **/
   static function getProcessStatus() {
      // To be overridden by class
      $tab = array();

      return $tab;
   }


   /**
    * check is the user can change from / to a status
    *
    * @param $itemtype itemtype
    * @param $old string value of old/current status
    * @param $new string value of target status
    *
    * @return boolean
   **/
   static function genericIsAllowedStatus($itemtype, $old, $new) {

      if (isset($_SESSION['glpiactiveprofile'][constant($itemtype.'::STATUS_MATRIX_FIELD')][$old][$new])
          && !$_SESSION['glpiactiveprofile'][constant($itemtype.'::STATUS_MATRIX_FIELD')][$old][$new]) {
         return false;
      }

      if (array_key_exists(constant($itemtype.'::STATUS_MATRIX_FIELD'),
                           $_SESSION['glpiactiveprofile'])) { // Not set for post-only)
         return true;
      }

      return false;
   }


   /**
    * Get the ITIL object status allowed for a current status
    *
    * @param $itemtype itemtype
    * @param $current status
    *
    * @return an array
   **/
   static function getAllowedStatusArray($itemtype, $current) {

      $item = new $itemtype();
      $tab = $item->getAllStatusArray();

      if (!isset($current)) {
         $current = 'new';
      }

      foreach ($tab as $status => $label) {
         if ($status != $current
             && !$item->isAllowedStatus($current, $status)) {
            unset($tab[$status]);
         }
      }
      return $tab;
   }


   /**
    * Dropdown of object status
    *
    * @param $itemtype itemtype
    * @param $name select name
    * @param $value default value
    * @param $option list proposed 0:normal, 1:search, 2:allowed
    *
    * @return nothing (display)
   **/
   static function dropdownGenericStatus($itemtype, $name, $value='new', $option=0) {

      $item = new $itemtype();

      if ($option == 2) {
         $tab = $item->getAllowedStatusArray($itemtype, $value);
      } else if ($option == 1) {
         $tab = $item->getAllStatusArray(true);
      } else {
         $tab = $item->getAllStatusArray(false);
      }

      echo "<select name='$name'>";
      foreach ($tab as $key => $val) {
         echo "<option value='$key' ".($value==$key?" selected ":"").">$val</option>";
      }
      echo "</select>";
   }


   /**
    * Get ITIL object status Name
    *
    * @param $itemtype itemtype
    * @param $value status ID
   **/
   static function getGenericStatus($itemtype, $value) {

      $item = new $itemtype();
      $tab  = $item->getAllStatusArray(true);
      return (isset($tab[$value]) ? $tab[$value] : '');
   }


   /**
    * show tooltip for user notification informations
    *
    * @param $type integer : user type
    * @param $canedit boolean : can edit ?
    *
    * @return nothing display
   **/
   function showGroupsAssociated($type, $canedit) {
      global $CFG_GLPI,$LANG;

      $showgrouplink = 0;
      if (Session::haveRight('group','r')) {
         $showgrouplink = 1;
      }

      $groupicon = self::getActorIcon('group',$type);
      $group     = new Group();

      if (isset($this->groups[$type]) && count($this->groups[$type])) {
         foreach ($this->groups[$type] as $d) {
            $k = $d['groups_id'];
            echo "$groupicon&nbsp;";
            if ($group->getFromDB($k)) {
               echo $group->getLink($showgrouplink);
            }
            if ($canedit) {
               echo "&nbsp;<a href='".$this->getFormURL()."?delete_group=delete_group&amp;id=".
                     $d['id']."&amp;".$this->getForeignKeyField()."=".$this->fields['id'].
                     "' title=\"".$LANG['reservation'][6]."\">
                     <img src='".$CFG_GLPI["root_doc"]."/pics/delete.png'
                      alt=\"".$LANG['buttons'][6]."\" title=\"".$LANG['buttons'][6]."\"></a>";
            }
            echo '<br>';
         }
      }
   }


   /**
    * display a value according to a field
    *
    * @since version 0.83
    *
    * @param $field     String name of the field
    * @param $values    Array with the value to display
    * @param $options   Array of option
    *
    * @return a string
   **/

   static function getSpecificValueToDisplay($field, $values, $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'urgency':
            return self::getUrgencyName($values[$field]);

         case 'impact':
            return self::getImpactName($values[$field]);

         case 'priority':
            return self::getPriorityName($values[$field]);
      }
      return '';
   }

   function getSearchOptionsStats() {
      global $LANG;

      $tab = array();
      $tab['stats'] = $LANG['Menu'][13];

      $tab[151]['table']         = $this->getTable();
      $tab[151]['field']         = 'solve_delay_stat';
      $tab[151]['name']          = $LANG['stats'][21];
      $tab[151]['datatype']      = 'timestamp';
      $tab[151]['forcegroupby']  = true;
      $tab[151]['massiveaction'] = false;

      $tab[152]['table']         = $this->getTable();
      $tab[152]['field']         = 'close_delay_stat';
      $tab[152]['name']          = $LANG['stats'][22];
      $tab[152]['datatype']      = 'timestamp';
      $tab[152]['forcegroupby']  = true;
      $tab[152]['massiveaction'] = false;

      $tab[153]['table']         = $this->getTable();
      $tab[153]['field']         = 'waiting_duration';
      $tab[153]['name']          = $LANG['stats'][25];
      $tab[153]['datatype']      = 'timestamp';
      $tab[153]['forcegroupby']  = true;
      $tab[153]['massiveaction'] = false;



      return $tab;
   }

   function getSearchOptionsActors() {
      global $LANG;

      $tab = array();

      $tab['requester'] = $LANG['job'][4];

      $tab[4]['table']         = 'glpi_users';
      $tab[4]['field']         = 'name';
      $tab[4]['datatype']      = 'dropdown';
      $tab[4]['name']          = $LANG['job'][4];
      $tab[4]['forcegroupby']  = true;
      $tab[4]['massiveaction'] = false;
      $tab[4]['joinparams']    = array('beforejoin'
                                       => array('table' => getTableForItemType($this->userlinkclass),
                                                'joinparams'
                                                        => array('jointype'  => 'child',
                                                                 'condition' => 'AND NEWTABLE.`type` ' .
                                                                                '= '.self::REQUESTER)));

      $tab[71]['table']         = 'glpi_groups';
      $tab[71]['field']         = 'completename';
      $tab[71]['datatype']      = 'dropdown';
      $tab[71]['name']          = $LANG['common'][35];
      $tab[71]['forcegroupby']  = true;
      $tab[71]['massiveaction'] = false;
      $tab[71]['condition']     = 'is_requester';
      $tab[71]['joinparams']    = array('beforejoin' =>
                                           array('table' => getTableForItemType($this->grouplinkclass),
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => 'AND NEWTABLE.`type` ' .
                                                                                 '= '.self::REQUESTER)));

      $tab[22]['table']     = 'glpi_users';
      $tab[22]['field']     = 'name';
      $tab[22]['datatype']      = 'dropdown';
      $tab[22]['linkfield'] = 'users_id_recipient';
      $tab[22]['name']      = $LANG['common'][37];

      $tab['observer'] = $LANG['common'][104];

      $tab[66]['table']         = 'glpi_users';
      $tab[66]['field']         = 'name';
      $tab[66]['datatype']      = 'dropdown';
      $tab[66]['name']          = $LANG['common'][104]." - ".$LANG['common'][34];
      $tab[66]['forcegroupby']  = true;
      $tab[66]['massiveaction'] = false;
      $tab[66]['joinparams']    = array('beforejoin'
                                        => array('table' => getTableForItemType($this->userlinkclass),
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => 'AND NEWTABLE.`type` ' .
                                                                                 '= '.self::OBSERVER)));

      $tab[65]['table']         = 'glpi_groups';
      $tab[65]['field']         = 'completename';
      $tab[65]['datatype']      = 'dropdown';
      $tab[65]['name']          = $LANG['common'][104]." - ".$LANG['common'][35];
      $tab[65]['forcegroupby']  = true;
      $tab[65]['massiveaction'] = false;
      $tab[65]['condition']     = 'is_requester';
      $tab[65]['joinparams']    = array('beforejoin'
                                        => array('table' => getTableForItemType($this->grouplinkclass),
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => 'AND NEWTABLE.`type` ' .
                                                                                 '= '.self::OBSERVER)));

      $tab['assign'] = $LANG['job'][5];

      $tab[5]['table']         = 'glpi_users';
      $tab[5]['field']         = 'name';
      $tab[5]['datatype']      = 'dropdown';
      $tab[5]['name']          = $LANG['job'][5]." - ".$LANG['job'][6];
      $tab[5]['forcegroupby']  = true;
      $tab[5]['massiveaction'] = false;
      $tab[5]['joinparams']    = array('beforejoin'
                                       => array('table' => getTableForItemType($this->userlinkclass),
                                                'joinparams'
                                                        => array('jointype'  => 'child',
                                                                 'condition' => 'AND NEWTABLE.`type` ' .
                                                                                '= '.self::ASSIGN)));
      $tab[5]['filter']        = 'own_ticket';

      $tab[6]['table']     = 'glpi_suppliers';
      $tab[6]['field']     = 'name';
      $tab[6]['datatype']  = 'dropdown';
      $tab[6]['linkfield'] = 'suppliers_id_assign';
      $tab[6]['name']      = $LANG['job'][5]." - ".$LANG['financial'][26];

      $tab[8]['table']         = 'glpi_groups';
      $tab[8]['field']         = 'completename';
      $tab[8]['datatype']      = 'dropdown';
      $tab[8]['name']          = $LANG['job'][5]." - ".$LANG['common'][35];
      $tab[8]['forcegroupby']  = true;
      $tab[8]['massiveaction'] = false;
      $tab[8]['condition']     = 'is_assign';
      $tab[8]['joinparams']    = array('beforejoin'
                                       => array('table' => getTableForItemType($this->grouplinkclass),
                                                'joinparams'
                                                        => array('jointype'  => 'child',
                                                                 'condition' => 'AND NEWTABLE.`type` ' .
                                                                                '= '.self::ASSIGN)));

      $tab['notification'] = $LANG['setup'][704];

      $tab[35]['table']      = getTableForItemType($this->userlinkclass);
      $tab[35]['field']      = 'use_notification';
      $tab[35]['massiveaction'] = false;
      $tab[35]['name']       = $LANG['job'][19];
      $tab[35]['datatype']   = 'bool';
      $tab[35]['joinparams'] = array('jointype'  => 'child',
                                     'condition' => 'AND NEWTABLE.`type` = '.self::REQUESTER);


      $tab[34]['table']      = getTableForItemType($this->userlinkclass);
      $tab[34]['field']      = 'alternative_email';
      $tab[34]['name']       = $LANG['joblist'][27];
      $tab[34]['datatype']   = 'email';
      $tab[34]['massiveaction'] = false;
      $tab[34]['joinparams'] = array('jointype'  => 'child',
                                     'condition' => 'AND NEWTABLE.`type` = '.self::REQUESTER);



      return $tab;
   }


   /**
    * show Icon for Actor
    *
    * @param $user_group string : 'user or 'group'
    * @param $type integer : user/group type
    *
    * @return nothing display
   **/
   static function getActorIcon($user_group, $type) {
      global $LANG, $CFG_GLPI;

      switch ($user_group) {
         case 'user' :
            $icontitle = $LANG['common'][34].' - '.$type;
            switch ($type) {
               case self::REQUESTER :
                  $icontitle = $LANG['common'][34].' - '.$LANG['job'][4];
                  break;

               case self::OBSERVER :
                  $icontitle = $LANG['common'][34].' - '.$LANG['common'][104];
                  break;

               case self::ASSIGN :
                  $icontitle = $LANG['job'][6];
                  break;
            }
            return "<img width=20 src='".$CFG_GLPI['root_doc']."/pics/users.png'
                     alt=\"$icontitle\" title=\"$icontitle\">";

         case 'group' :
            $icontitle = $LANG['common'][35];
            switch ($type) {
               case self::REQUESTER :
                  $icontitle = $LANG['setup'][249];
                  break;

               case self::OBSERVER :
                  $icontitle = $LANG['setup'][251];
                  break;

               case self::ASSIGN :
                  $icontitle = $LANG['setup'][248];
                  break;
            }
            return  "<img width=20 src='".$CFG_GLPI['root_doc']."/pics/groupes.png'
                      alt=\"$icontitle\" title=\"$icontitle\">";

         case 'supplier' :
            $icontitle = $LANG['financial'][26];
            return  "<img width=20 src='".$CFG_GLPI['root_doc']."/pics/supplier.png'
                      alt=\"$icontitle\" title=\"$icontitle\">";

      }
      return '';

   }


   /**
    * show tooltip for user notification informations
    *
    * @param $type integer : user type
    * @param $canedit boolean : can edit ?
    *
    * @return nothing display
   **/
   function showUsersAssociated($type, $canedit) {
      global $CFG_GLPI, $LANG;

      $showuserlink = 0;
      if (Session::haveRight('user','r')) {
         $showuserlink = 2;
      }
      $usericon = self::getActorIcon('user',$type);
      $user     = new User();

      if (isset($this->users[$type]) && count($this->users[$type])) {
         foreach ($this->users[$type] as $d) {
            $k = $d['users_id'];
            $save_showuserlink = $showuserlink;

            echo "$usericon&nbsp;";

            if ($k) {
               $userdata = getUserName($k, $showuserlink);
            } else {
               $email         = $d['alternative_email'];
               $userdata      = "<a href='mailto:$email'>$email</a>";
               $showuserlink  = false;
            }

            if ($showuserlink) {
               echo $userdata['name']."&nbsp;".Html::showToolTip($userdata["comment"],
                                                                 array('link'    => $userdata["link"],
                                                                       'display' => false));
            } else {
               echo $userdata;
            }

            if ($CFG_GLPI['use_mailing']) {
               $text = $LANG['job'][19]."&nbsp;:&nbsp;".Dropdown::getYesNo($d['use_notification']).
                       '<br>';

               if ($d['use_notification']) {
                  $uemail = $d['alternative_email'];
                  if (empty($uemail) && $user->getFromDB($d['users_id'])) {
                     $uemail = $user->getDefaultEmail();
                  }
                  $text .= $LANG['mailing'][118]."&nbsp;:&nbsp;".$uemail;
                  if (!NotificationMail::isUserAddressValid($uemail)) {
                     $text .= "<span class='red'>".$LANG['mailing'][110]."</span>";
                  }
               }
               echo "&nbsp;";

               if ($canedit
                   || $d['users_id'] == Session::getLoginUserID()) {
                  $opt = array('img'   => $CFG_GLPI['root_doc'].'/pics/edit.png',
                               'popup' => 'edit_user_notification&amp;id='.$d['id']);
                  Html::showToolTip($text, $opt);
               }
            }

            if ($canedit) {
               echo "&nbsp;<a href='".$this->getFormURL()."?delete_user=delete_user&amp;id=".
                     $d['id']. "&amp;".$this->getForeignKeyField()."=".$this->fields['id'].
                     "' title=\"".$LANG['buttons'][6]."\">
                     <img src='".$CFG_GLPI["root_doc"]."/pics/delete.png'
                      alt=\"".$LANG['buttons'][6]."\" title=\"".$LANG['buttons'][6]."\"></a>";
            }
            echo "<br>";

            $showuserlink = $save_showuserlink;
         }
      }
   }


   /**
    * show actor add div
    *
    * @param $type string : actor type
    * @param $rand_type integer rand value of div to use
    * @param $entities_id integer entity ID
    * @param $is_hidden array of hidden fields (if empty consider as not hidden)
    * @param $withgroup boolean : allow adding a group (true by default)
    * @param $withsupplier boolean : allow adding a supplier (only one possible in ASSIGN case)
    * @param $inobject boolean display in ITIL object ?
    *
    * @return nothing display
   **/
   static function showActorAddForm($type, $rand_type, $entities_id, $is_hidden = array(),
                                    $withgroup = true, $withsupplier=false, $inobject=true) {
      global $LANG, $CFG_GLPI;

      $types = array(''      => Dropdown::EMPTY_VALUE,
                     'user'  => $LANG['common'][34],
                     'group' => $LANG['common'][35]);

      if ($withsupplier && $type == self::ASSIGN) {
         $types['supplier'] = $LANG['financial'][26];
      }

      switch ($type) {
         case self::REQUESTER :
            $typename = 'requester';
            if (isset($is_hidden['_users_id_requester']) && $is_hidden['_users_id_requester']) {
               unset($types['user']);
            }
            if (isset($is_hidden['_groups_id_requester']) && $is_hidden['_groups_id_requester']) {
               unset($types['group']);
            }
            break;

         case self::OBSERVER :
            $typename = 'observer';
            if (isset($is_hidden['_users_id_observer']) && $is_hidden['_users_id_observer']) {
               unset($types['user']);
            }
            if (isset($is_hidden['_groups_id_observer']) && $is_hidden['_groups_id_observer']) {
               unset($types['group']);
            }
            break;

         case self::ASSIGN :
            $typename = 'assign';
            if (isset($is_hidden['_users_id_assign']) && $is_hidden['_users_id_assign']) {
               unset($types['user']);
            }
            if (isset($is_hidden['_groups_id_assign']) && $is_hidden['_groups_id_assign']) {
               unset($types['group']);
            }
            if (isset($types['supplier'])
               && isset($is_hidden['suppliers_id_assign']) && $is_hidden['suppliers_id_assign']) {
               unset($types['supplier']);
            }
            break;

         default :
            return false;
      }

      if (isset($types['group']) && !$withgroup) {
         unset($types['group']);
      }

      if (count($types)>1) {
         echo "<div ".($inobject?"style='display:none'":'')." id='itilactor$rand_type'>";
         $rand   = Dropdown::showFromArray("_itil_".$typename."[_type]", $types);
         $params = array('type'            => '__VALUE__',
                        'actortype'       => $typename,
                        'allow_email'     => ($type==self::OBSERVER || $type==self::REQUESTER),
                        'entity_restrict' => $entities_id);

         Ajax::updateItemOnSelectEvent("dropdown__itil_".$typename."[_type]$rand",
                                       "showitilactor".$typename."_$rand",
                                       $CFG_GLPI["root_doc"]."/ajax/dropdownItilActors.php",
                                       $params);
         echo "<span id='showitilactor".$typename."_$rand'>&nbsp;</span>";
         if ($inobject) {
            echo "<hr>";
         }
         echo "</div>";
      }
   }


   /**
    * show user add div on creation
    *
    * @param $type integer : actor type
    * @param $options array options for default values ($options of showForm)
    *
    * @return nothing display
   **/
   function showActorAddFormOnCreate($type, $options) {
      global $LANG, $CFG_GLPI;

      switch ($type) {
         case self::REQUESTER :
            $typename = 'requester';
            break;

         case self::OBSERVER :
            $typename = 'observer';
            break;

         case self::ASSIGN :
            $typename = 'assign';
            break;

         default :
            return false;
      }

      $itemtype = $this->getType();

      echo self::getActorIcon('user', $type);
      // For ticket templates : mandatories
      if ($itemtype == 'Ticket' && isset($options['_tickettemplate'])) {
         echo $options['_tickettemplate']->getMandatoryMark("_users_id_".$typename);
      }
      echo "&nbsp;";

      if (!isset($options["_right"])) {
         $right = $this->getDefaultActorRightSearch($type);
      } else {
         $right = $options["_right"];
      }

      if ($options["_users_id_".$typename] == 0) {
         $options["_users_id_".$typename] = $this->getDefaultActor($type);
      }
      $rand   = mt_rand();
      $params = array('name'        => '_users_id_'.$typename,
                      'value'       => $options["_users_id_".$typename],
                      'right'       => $right,
                      'rand'        => $rand,
                      'ldap_import' => true);

      if ($this->userentity_oncreate && $type == self::REQUESTER) {
         $params['on_change'] = 'submit()';
      } else { // Force entity search if needed
         $params['entity'] = $options['entities_id'];
      }


      if ($CFG_GLPI['use_mailing']) {
         $paramscomment = array('value' => '__VALUE__',
                                'field' => "_users_id_".$typename."_notif",
                                'allow_email'
                                        => ($type==self::REQUESTER || $type==self::OBSERVER),
                                'use_notification'
                                        => $options["_users_id_".$typename."_notif"]['use_notification']);

         if (isset($options["_users_id_".$typename."_notif"]['alternative_email'])) {
            $paramscomment['alternative_email'] = $options["_users_id_".$typename."_notif"]['alternative_email'];
         }


         $params['toupdate'] = array('value_fieldname' => 'value',
                                     'to_update'       => "notif_".$typename."_$rand",
                                     'url'             => $CFG_GLPI["root_doc"]."/ajax/uemailUpdate.php",
                                     'moreparams'      => $paramscomment);
      }

      if ($itemtype == 'Ticket' && $type == self::ASSIGN) {
         $toupdate = array();
         if (isset($params['toupdate']) && is_array($params['toupdate'])) {
            $toupdate[] = $params['toupdate'];
         }

         $toupdate[] = array('value_fieldname' => 'value',
                              'to_update'       => "countassign_".$typename."_$rand",
                              'url'             => $CFG_GLPI["root_doc"]."/ajax/ticketassigninformation.php",
                              'moreparams'      => array('users_id_assign' => '__VALUE__'));
         $params['toupdate'] = $toupdate;
      }

      // List all users in the active entities
      User::dropdown($params);

      if ($itemtype == 'Ticket') {

         // display opened tickets for user
         if ($type == self::REQUESTER
             && $options["_users_id_".$typename] > 0
             && $_SESSION["glpiactiveprofile"]["interface"] != "helpdesk") {

            $options2['field'][0]      = 4; // users_id
            $options2['searchtype'][0] = 'equals';
            $options2['contains'][0]   = $options["_users_id_".$typename];
            $options2['link'][0]       = 'AND';

            $options2['field'][1]      = 12; // status
            $options2['searchtype'][1] = 'equals';
            $options2['contains'][1]   = 'notold';
            $options2['link'][1]       = 'AND';

            $options2['reset'] = 'reset';

            $url = $this->getSearchURL()."?".Toolbox::append_params($options2,'&amp;');

            echo "&nbsp;<a href='$url' title=\"".$LANG['joblist'][21]."\" target='_blank'>(".
                  $LANG['joblist'][21]."&nbsp;:&nbsp;".
                  $this->countActiveObjectsForUser($options["_users_id_".$typename]).")</a>";
         }

         // Display active tickets for a tech
         // Need to update information on dropdown changes
         if ($type == self::ASSIGN) {
            echo "<span id='countassign_".$typename."_$rand'>";
            echo "</span>";

            echo "<script type='text/javascript'>";
            Ajax::updateItemJsCode("countassign_".$typename."_$rand",
                                 $CFG_GLPI["root_doc"]."/ajax/ticketassigninformation.php",
                                 array('users_id_assign' => '__VALUE__'),
                                 "dropdown__users_id_".$typename.$rand);
            echo "</script>";
         }
      }

      if ($CFG_GLPI['use_mailing']) {
         echo "<div id='notif_".$typename."_$rand'>";
         echo "</div>";

         echo "<script type='text/javascript'>";
         Ajax::updateItemJsCode("notif_".$typename."_$rand",
                                $CFG_GLPI["root_doc"]."/ajax/uemailUpdate.php", $paramscomment,
                                "dropdown__users_id_".$typename.$rand);
         echo "</script>";
      }
   }


   /**
    * show actor part in ITIL object form
    *
    * @param $ID integer ITIL object ID
    * @param $options array options for default values ($options of showForm)
    *
    * @return nothing display
   **/
   function showActorsPartForm($ID, $options) {
      global $LANG, $CFG_GLPI;

      $showuserlink = 0;
      if (Session::haveRight('user','r')) {
         $showuserlink = 1;
      }

      // check is_hidden fields
      foreach (array('_users_id_requester', '_groups_id_requester',
                     '_users_id_observer', '_groups_id_observer',
                     '_users_id_assign', '_groups_id_assign',
                     'suppliers_id_assign') as $f) {
         $is_hidden[$f] = false;
         if (isset($options['_tickettemplate'])
            && $options['_tickettemplate']->isHiddenField($f)) {
            $is_hidden[$f] = true;
         }
      }

      // Manage actors : requester and assign
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th rowspan='2' width='13%'>".$LANG['common'][103]."&nbsp;:</th>";
      echo "<th width='29%'>";
      if (!$is_hidden['_users_id_requester'] || !$is_hidden['_groups_id_requester']) {
         echo $LANG['job'][4];
      }
      $rand_requester = -1;
      $candeleterequester    = false;

      if ($ID && $this->canAdminActors()
         && (!$is_hidden['_users_id_requester'] || !$is_hidden['_groups_id_requester'])) {
         $rand_requester = mt_rand();
         echo "&nbsp;&nbsp;";

         echo "<img title=\"".$LANG['buttons'][8]."\" alt=\"".$LANG['buttons'][8]."\"
                    onClick=\"Ext.get('itilactor$rand_requester').setDisplayed('block')\"
                    class='pointer' src='".$CFG_GLPI["root_doc"]."/pics/add_dropdown.png'>";
         $candeleterequester = true;
      }
      echo "</th>";

      echo "<th width='29%'>";
      if (!$is_hidden['_users_id_observer'] || !$is_hidden['_groups_id_observer']) {
         echo $LANG['common'][104];
      }
      $rand_observer = -1;
      $candeleteobserver    = false;

      if ($ID && $this->canAdminActors()
         && (!$is_hidden['_users_id_observer'] || !$is_hidden['_groups_id_observer'])) {
         $rand_observer = mt_rand();

         echo "&nbsp;&nbsp;";
         echo "<img title=\"".$LANG['buttons'][8]."\" alt=\"".$LANG['buttons'][8]."\"
                    onClick=\"Ext.get('itilactor$rand_observer').setDisplayed('block')\"
                    class='pointer' src='".$CFG_GLPI["root_doc"]."/pics/add_dropdown.png'>";

         $candeleteobserver = true;

      } else if ($ID > 0 && !$is_hidden['_users_id_observer']
                 && !$this->isUser(self::OBSERVER, Session::getLoginUserID())
                 && !$this->isUser(self::REQUESTER, Session::getLoginUserID())) {
         echo "&nbsp;&nbsp;";
         echo "&nbsp;&nbsp;<a href='".$CFG_GLPI["root_doc"].
              "/front/ticket.form.php?addme_observer=addme_observer".
              "&amp;tickets_id=".$this->fields['id']."' title=\"".$LANG['tracking'][5]."\">".
              $LANG['tracking'][5]."</a>";
      }
      echo "</th>";

      echo "<th width='29%'>";
      if ((!$is_hidden['_users_id_assign'] || !$is_hidden['_groups_id_assign']
               || !$is_hidden['suppliers_id_assign'])) {
         echo $LANG['job'][5];
      }
      $rand_assign = -1;
      $candeleteassign    = false;

      if ($ID && ($this->canAssign() || $this->canAssignToMe())
         && (!$is_hidden['_users_id_assign'] || !$is_hidden['_groups_id_assign']
               || !$is_hidden['suppliers_id_assign'])) {
         $rand_assign = mt_rand();

         echo "&nbsp;&nbsp;";
         echo "<img title=\"".$LANG['buttons'][8]."\" alt=\"".$LANG['buttons'][8]."\"
                    onClick=\"Ext.get('itilactor$rand_assign').setDisplayed('block')\"
                    class='pointer' src='".$CFG_GLPI["root_doc"]."/pics/add_dropdown.png'>";
      }

      if ($ID && $this->canAssign()) {
         $candeleteassign = true;
      }
      echo "</th></tr>";

      echo "<tr class='tab_bg_1 top'>";
      echo "<td>";

      if ($rand_requester>=0) {
         self::showActorAddForm(self::REQUESTER, $rand_requester,
                                $this->fields['entities_id'], $is_hidden);
      }

      // Requester
      if (!$ID) {

         $reqdisplay=false;
         if ($this->canAdminActors() && !$is_hidden['_users_id_requester']) {
            $this->showActorAddFormOnCreate(self::REQUESTER, $options);
            $reqdisplay=true;
         } else {
            $delegating = User::getDelegateGroupsForUser($options['entities_id']);
            if (count($delegating) && !$is_hidden['_users_id_requester']) {
               //$this->getDefaultActor(self::REQUESTER);
               $options['_right'] = "delegate";
               $this->showActorAddFormOnCreate(self::REQUESTER, $options);
               $reqdisplay=true;
            } else { // predefined value
               if (isset($options["_users_id_requester"]) && $options["_users_id_requester"]) {
                  echo self::getActorIcon('user', self::REQUESTER)."&nbsp;";
                  echo Dropdown::getDropdownName("glpi_users", $options["_users_id_requester"]);
                  echo "<input type='hidden' name='_users_id_requester' value=\"".$options["_users_id_requester"]."\">";
                  echo '<br>';
                  $reqdisplay=true;
               }
            }
         }

         //If user have access to more than one entity, then display a combobox : Ticket case
         if ($this->userentity_oncreate
             && isset($this->countentitiesforuser)
             && $this->countentitiesforuser > 1) {
            echo "<br>";
            $rand = Dropdown::show('Entity', array('value'       => $this->fields["entities_id"],
                                                   'entity'      => $this->userentities,
                                                   'on_change'   => 'submit()'));
         } else {
            echo "<input type='hidden' name='entities_id' value='".$this->fields["entities_id"]."'>";
         }
         if ($reqdisplay) {
            echo '<hr>';
         }

      } else if (!$is_hidden['_users_id_requester']) {
         $this->showUsersAssociated(self::REQUESTER, $candeleterequester);
      }

      // Requester Group
      if (!$ID) {

         if ($this->canAdminActors() && !$is_hidden['_groups_id_requester']) {
            echo self::getActorIcon('group', self::REQUESTER);
            /// For ticket templates : mandatories
            if (isset($options['_tickettemplate'])) {
               echo $options['_tickettemplate']->getMandatoryMark('_groups_id_requester');
            }
            echo "&nbsp;";

            Dropdown::show('Group', array('name'      => '_groups_id_requester',
                                          'value'     => $options["_groups_id_requester"],
                                          'entity'    => $this->fields["entities_id"],
                                          'condition' => '`is_requester`'));
         } else { // predefined value
            if (isset($options["_groups_id_requester"]) && $options["_groups_id_requester"]) {
               echo self::getActorIcon('group', self::REQUESTER)."&nbsp;";
               echo Dropdown::getDropdownName("glpi_groups", $options["_groups_id_requester"]);
               echo "<input type='hidden' name='_groups_id_requester' value=\"".$options["_groups_id_requester"]."\">";
               echo '<br>';
            }
         }
      } else if (!$is_hidden['_groups_id_requester']) {
         $this->showGroupsAssociated(self::REQUESTER, $candeleterequester);
      }
      echo "</td>";

      echo "<td>";
      if ($rand_observer>=0) {
         self::showActorAddForm(self::OBSERVER, $rand_observer,
                                $this->fields['entities_id'], $is_hidden);
      }

      // Observer
      if (!$ID) {

         if ($this->canAdminActors() && !$is_hidden['_users_id_observer']) {
            $this->showActorAddFormOnCreate(self::OBSERVER, $options);
            echo '<hr>';
         } else { // predefined value
            if (isset($options["_users_id_observer"]) && $options["_users_id_observer"]) {
               echo self::getActorIcon('user', self::OBSERVER)."&nbsp;";
               echo Dropdown::getDropdownName("glpi_users", $options["_users_id_observer"]);
               echo "<input type='hidden' name='_users_id_observer' value=\"".$options["_users_id_observer"]."\">";
               echo '<hr>';
            }
         }
      } else if (!$is_hidden['_users_id_observer']) {
         $this->showUsersAssociated(self::OBSERVER, $candeleteobserver);
      }

      // Observer Group
      if (!$ID) {

         if ($this->canAdminActors() && !$is_hidden['_groups_id_observer']) {
            echo self::getActorIcon('group', self::OBSERVER);
            /// For ticket templates : mandatories
            if (isset($options['_tickettemplate'])) {
               echo $options['_tickettemplate']->getMandatoryMark('_groups_id_observer');
            }
            echo "&nbsp;";

            Dropdown::show('Group', array('name'      => '_groups_id_observer',
                                          'value'     => $options["_groups_id_observer"],
                                          'entity'    => $this->fields["entities_id"],
                                          'condition' => '`is_requester`'));
         } else { // predefined value
            if (isset($options["_groups_id_observer"]) && $options["_groups_id_observer"]) {
               echo self::getActorIcon('group', self::OBSERVER)."&nbsp;";
               echo Dropdown::getDropdownName("glpi_groups", $options["_groups_id_observer"]);
               echo "<input type='hidden' name='_groups_id_observer' value=\"".$options["_groups_id_observer"]."\">";
               echo '<br>';
            }
         }
      } else if (!$is_hidden['_groups_id_observer']) {
         $this->showGroupsAssociated(self::OBSERVER, $candeleteobserver);
      }
      echo "</td>";

      echo "<td>";
      if ($rand_assign>=0) {
         self::showActorAddForm(self::ASSIGN, $rand_assign, $this->fields['entities_id'],
                                $is_hidden, $this->canAssign(),
                                $this->canAssign() && ($this->fields["suppliers_id_assign"]==0));
      }

      // Assign User
      if (!$ID) {

         if ($this->canAssign() && !$is_hidden['_users_id_assign']) {
            $this->showActorAddFormOnCreate(self::ASSIGN, $options);
            echo '<hr>';
         } else if ($this->canAssignToMe() && !$is_hidden['_users_id_assign']) {
            echo self::getActorIcon('user', self::ASSIGN)."&nbsp;";
            User::dropdown(array('name'        => '_users_id_assign',
                                 'value'       => $options["_users_id_assign"],
                                 'entity'      => $this->fields["entities_id"],
                                 'ldap_import' => true));
            echo '<hr>';
         } else { // predefined value
            if (isset($options["_users_id_assign"]) && $options["_users_id_assign"]) {
               echo self::getActorIcon('user', self::ASSIGN)."&nbsp;";
               echo Dropdown::getDropdownName("glpi_users", $options["_users_id_assign"]);
               echo "<input type='hidden' name='_users_id_assign' value=\"".$options["_users_id_assign"]."\">";
               echo '<hr>';
            }
         }
      } else if (!$is_hidden['_users_id_assign']) {
         $this->showUsersAssociated(self::ASSIGN, $candeleteassign);
      }

      // Assign Groups
      if (!$ID) {
         if ($this->canAssign() && !$is_hidden['_groups_id_assign']) {
            echo self::getActorIcon('group', self::ASSIGN);
            /// For ticket templates : mandatories
            if (isset($options['_tickettemplate'])) {
               echo $options['_tickettemplate']->getMandatoryMark('_groups_id_assign');
            }
            echo "&nbsp;";

            Dropdown::show('Group', array('name'      => '_groups_id_assign',
                                          'value'     => $options["_groups_id_assign"],
                                          'entity'    => $this->fields["entities_id"],
                                          'condition' => '`is_assign`'));
            echo '<hr>';
         } else { // predefined value
            if (isset($options["_groups_id_assign"]) && $options["_groups_id_assign"]) {
               echo self::getActorIcon('group', self::ASSIGN)."&nbsp;";
               echo Dropdown::getDropdownName("glpi_groups", $options["_groups_id_assign"]);
               echo "<input type='hidden' name='_groups_id_assign' value=\"".$options["_groups_id_assign"]."\">";
               echo '<hr>';
            }
         }

      } else if (!$is_hidden['_groups_id_assign']) {
         $this->showGroupsAssociated(self::ASSIGN, $candeleteassign);
      }

      // Supplier
      if ($this->canAssign() && !$is_hidden['suppliers_id_assign']
         && ($this->fields["suppliers_id_assign"] || !$ID)) {
         echo self::getActorIcon('supplier', self::ASSIGN);
         /// For ticket templates : mandatories
         if (isset($options['_tickettemplate'])) {
            echo $options['_tickettemplate']->getMandatoryMark('suppliers_id_assign');
         }
         echo "&nbsp;";

         Dropdown::show('Supplier', array('name'   => 'suppliers_id_assign',
                                          'value'  => $this->fields["suppliers_id_assign"],
                                          'entity' => $this->fields["entities_id"]));
         echo '<br>';
      } else if (!$is_hidden['suppliers_id_assign']) {
         if ($this->fields["suppliers_id_assign"]) {
            echo self::getActorIcon('supplier', self::ASSIGN)."&nbsp;";
            echo Dropdown::getDropdownName("glpi_suppliers", $this->fields["suppliers_id_assign"]);
            if (!$ID) {
               echo "<input type='hidden' name='suppliers_id_assign' value=\"".$this->fields["suppliers_id_assign"]."\">";
            }
            echo '<br>';
         }
      }
      echo "</td>";
      echo "</tr>";
      echo "</table>";
   }


   static function getActionTime($actiontime) {
      return Html::timestampToString($actiontime, false);
   }


   static function getAssignName($ID, $itemtype, $link=0) {

      switch ($itemtype) {
         case 'User' :
            if ($ID == 0) {
               return "";
            }
            return getUserName($ID,$link);

         case 'Supplier' :
         case 'Group' :
            $item = new $itemtype();
            if ($item->getFromDB($ID)) {
               $before = "";
               $after  = "";
               if ($link) {
                  return $item->getLink(1);
               }
               return $item->getNameID();
            }
            return "";
      }
   }


   /**
    * Form to add a solution to an ITIL object
    *
    * @param $knowbase_id_toload integer load a kb article as solution (0 = no load)
   **/
   function showSolutionForm($knowbase_id_toload=0) {
      global $LANG, $CFG_GLPI;

      $this->check($this->getField('id'), 'r');

      $canedit = $this->canSolve();
      $options = array();

      if ($knowbase_id_toload > 0) {
         $kb = new KnowbaseItem();
         if ($kb->getFromDB($knowbase_id_toload)) {
            $this->fields['solution'] = $kb->getField('answer');
         }
      }

      $this->showFormHeader($options);

      $show_template = $canedit;
//                        && $this->getField('solutiontypes_id') == 0
//                        && empty($this->fields['solution']);
      $rand_template = mt_rand();
      $rand_text     = $rand_type = 0;
      if ($canedit) {
         $rand_text = mt_rand();
         $rand_type = mt_rand();
      }
      if ($show_template) {
         echo "<tr class='tab_bg_2'>";
         echo "<td>".$LANG['jobresolution'][6]."&nbsp;:&nbsp;</td><td>";

         Dropdown::show('SolutionTemplate',
                        array('value'    => 0,
                              'entity'   => $this->getEntityID(),
                              'rand'     => $rand_template,
                              // Load type and solution from bookmark
                              'toupdate' => array('value_fieldname' => 'value',
                                                  'to_update' => 'solution'.$rand_text,
                                                  'url'       => $CFG_GLPI["root_doc"].
                                                                 "/ajax/solution.php",
                                                  'moreparams' => array('type_id'
                                                                        => 'dropdown_solutiontypes_id'.
                                                                           $rand_type))));

         echo "</td><td colspan='2'>";
         if (Session::haveRight('knowbase','r') || Session::haveRight('faq','r')) {
            echo "<a title\"".$LANG['job'][23]."\"
                  href='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?itemtype=".$this->getType().
                  "&amp;items_id=".$this->getField('id')."'>".$LANG['job'][23]."</a>";
         }
         echo "</td></tr>";
      }

      echo "<tr class='tab_bg_2'>";
      echo "<td>".$LANG['job'][48]."&nbsp;:&nbsp;</td><td>";

      $current = $this->fields['status'];
      // Settings a solution will set status to solved
      if ($canedit) {
         Dropdown::show('SolutionType',
                        array('value'  => $this->getField('solutiontypes_id'),
                              'rand'   => $rand_type,
                              'entity' => $this->getEntityID()));
      } else {
         echo Dropdown::getDropdownName('glpi_solutiontypes',
                                        $this->getField('solutiontypes_id'));
      }
      echo "</td><td colspan='2'>&nbsp;</td></tr>";
      if ($canedit && Session::haveRight('knowbase','w')) {
         echo "<tr class='tab_bg_2'><td>".$LANG['job'][25]."</td><td>";
         Dropdown::showYesNo('_sol_to_kb', false);
         echo "</td><td colspan='2'>&nbsp;</td></tr>";
      }

      echo "<tr class='tab_bg_2'>";
      echo "<td>".$LANG['joblist'][6]."&nbsp;: </td><td colspan='3'>";

      if ($canedit) {
         $rand = mt_rand();
         Html::initEditorSystem("solution$rand");

         echo "<div id='solution$rand_text'>";
         echo "<textarea id='solution$rand' name='solution' rows='12' cols='80'>".
                $this->getField('solution')."</textarea></div>";

      } else {
         echo Toolbox::unclean_cross_side_scripting_deep($this->getField('solution'));
      }
      echo "</td></tr>";

      $options['candel']  = false;
      $options['canedit'] = $canedit;
      $this->showFormButtons($options);

   }


   /**
    * Update date mod of the ITIL object
    *
    * @param $ID ID of the ITIL object
    * @param $no_stat_computation boolean do not cumpute take into account stat
    * @param $users_id_lastupdater integer to force last_update id (default 0 = not used)
   **/
   function updateDateMod($ID, $no_stat_computation=false, $users_id_lastupdater=0) {
      global $DB;

      if ($this->getFromDB($ID)) {
         // Force date mod and lastupdater
         $query = "UPDATE `".$this->getTable()."`
                   SET `date_mod` = '".$_SESSION["glpi_currenttime"]."'";

         // set last updater if interactive user
         $lastupdater = Session::getLoginUserID(false);
         if (is_numeric($lastupdater)) {
            $users_id_lastupdater = $lastupdater;
         }
         if ($users_id_lastupdater > 0) {
            $query .= ", `users_id_lastupdater` = '$users_id_lastupdater' ";
         }

         $query .= "WHERE `id` = '$ID'";
         $DB->query($query);
      }
   }


   /**
    * Update actiontime of the object based on actiontime of the tasks
    *
    * @param $ID ID of the object
    *
    * @return boolean : success
   **/
   function updateActionTime($ID) {
      global $DB;

      $tot       = 0;
      $tasktable = getTableForItemType($this->getType().'Task');

      $query = "SELECT SUM(`actiontime`)
                FROM `$tasktable`
                WHERE `".$this->getForeignKeyField()."` = '$ID'";

      if ($result = $DB->query($query)) {
         $sum = $DB->result($result,0,0);
         if (!is_null($sum)) {
            $tot += $sum;
         }
      }
      $query2 = "UPDATE `".$this->getTable()."`
                 SET `actiontime` = '$tot'
                 WHERE `id` = '$ID'";

      return $DB->query($query2);
   }


   /**
    * Get all available types to which an ITIL object can be assigned
    *
   **/
   static function getAllTypesForHelpdesk() {
      global $PLUGIN_HOOKS, $CFG_GLPI;

      /// TODO ticket_types -> itil_types

      $types = array();

      //Types of the plugins (keep the plugin hook for right check)
      if (isset($PLUGIN_HOOKS['assign_to_ticket'])) {
         foreach ($PLUGIN_HOOKS['assign_to_ticket'] as $plugin => $value) {
            $types = Plugin::doOneHook($plugin, 'AssignToTicket', $types);
         }
      }

      //Types of the core (after the plugin for robustness)
      foreach($CFG_GLPI["ticket_types"] as $itemtype) {
         if ($item = getItemForItemtype($itemtype)) {
            if (!isPluginItemType($itemtype) // No plugin here
                && in_array($itemtype,$_SESSION["glpiactiveprofile"]["helpdesk_item_type"])) {
               $types[$itemtype] = $item->getTypeName();
            }
         }
      }
      ksort($types); // core type first... asort could be better ?

      return $types;
   }


   /**
    * Check if it's possible to assign ITIL object to a type (core or plugin)
    *
    * @param $itemtype the object's type
    *
    * @return true if ticket can be assign to this type, false if not
   **/
   static function isPossibleToAssignType($itemtype) {
      global $PLUGIN_HOOKS;
      /// TODO : assign_to_ticket to assign_to_itil
      // Plugin case
      if (isPluginItemType($itemtype)) {
         /// TODO maybe only check plugin of itemtype ?
         //If it's not a core's type, then check plugins
         $types = array();
         if (isset($PLUGIN_HOOKS['assign_to_ticket'])) {
            foreach ($PLUGIN_HOOKS['assign_to_ticket'] as $plugin => $value) {
               $types = Plugin::doOneHook($plugin, 'AssignToTicket', $types);
            }
            if (array_key_exists($itemtype,$types)) {
               return true;
            }
         }
      // standard case
      } else {
         if (in_array($itemtype, $_SESSION["glpiactiveprofile"]["helpdesk_item_type"])) {
            return true;
         }
      }

      return false;
   }

   /// Compute solve delay stat of the current ticket
   function computeSolveDelayStat() {

      if (isset($this->fields['id'])
          && !empty($this->fields['date'])
          && !empty($this->fields['solvedate'])) {

         $calendars_id = EntityData::getUsedConfig('calendars_id', $this->fields['entities_id']);
         $calendar     = new Calendar();

         // Using calendar
         if ($calendars_id>0 && $calendar->getFromDB($calendars_id)) {
            return max(0, $calendar->getActiveTimeBetween($this->fields['date'],
                                                          $this->fields['solvedate'])
                                                            -$this->fields["waiting_duration"]);
         }
         // Not calendar defined
         return max(0, strtotime($this->fields['solvedate'])-strtotime($this->fields['date'])
                                                     -$this->fields["waiting_duration"]);
      }
      return 0;
   }


   /// Compute close delay stat of the current ticket
   function computeCloseDelayStat() {

      if (isset($this->fields['id'])
          && !empty($this->fields['date'])
          && !empty($this->fields['closedate'])) {

         $calendars_id = EntityData::getUsedConfig('calendars_id', $this->fields['entities_id']);
         $calendar     = new Calendar();

         // Using calendar
         if ($calendars_id>0 && $calendar->getFromDB($calendars_id)) {
            return max(0, $calendar->getActiveTimeBetween($this->fields['date'],
                                                          $this->fields['closedate'])
                                                             -$this->fields["waiting_duration"]);
         }
         // Not calendar defined
         return max(0, strtotime($this->fields['closedate'])-strtotime($this->fields['date'])
                                                     -$this->fields["waiting_duration"]);
      }
      return 0;
   }

   function showStats() {
      global $LANG;

      if (!Session::haveRight('observe_ticket',1) || !isset($this->fields['id'])) {
         return false;
      }

      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".$LANG['common'][99]."</th></tr>";
      echo "<tr class='tab_bg_2'><td>".$LANG['reports'][60]."&nbsp;:</td>";
      echo "<td>".Html::convDateTime($this->fields['date'])."</td></tr>";
      echo "<tr class='tab_bg_2'><td>".$LANG['sla'][5]."&nbsp;:</td>";
      echo "<td>".Html::convDateTime($this->fields['due_date'])."</td></tr>";

      if ($this->fields['status']=='solved' || $this->fields['status']=='closed') {
         echo "<tr class='tab_bg_2'><td>".$LANG['reports'][64]."&nbsp;:</td>";
         echo "<td>".Html::convDateTime($this->fields['solvedate'])."</td></tr>";
      }

      if ($this->fields['status']=='closed') {
         echo "<tr class='tab_bg_2'><td>".$LANG['reports'][61]."&nbsp;:</td>";
         echo "<td>".Html::convDateTime($this->fields['closedate'])."</td></tr>";
      }
      echo "<tr><th colspan='2'>".$LANG['common'][100]."</th></tr>";

      if (isset($this->fields['takeintoaccount_delay_stat'])) {
         echo "<tr class='tab_bg_2'><td>".$LANG['stats'][12]."&nbsp;:</td><td>";
         if ($this->fields['takeintoaccount_delay_stat']>0) {
            echo Html::timestampToString($this->fields['takeintoaccount_delay_stat'],0);
         } else {
            echo '&nbsp;';
         }
         echo "</td></tr>";
      }

      if ($this->fields['status']=='solved' || $this->fields['status']=='closed') {
         echo "<tr class='tab_bg_2'><td>".$LANG['stats'][9]."&nbsp;:</td><td>";

         if ($this->fields['solve_delay_stat']>0) {
            echo Html::timestampToString($this->fields['solve_delay_stat'],0);
         } else {
            echo '&nbsp;';
         }
         echo "</td></tr>";
      }

      if ($this->fields['status']=='closed') {
         echo "<tr class='tab_bg_2'><td>".$LANG['stats'][10]."&nbsp;:</td><td>";
         if ($this->fields['close_delay_stat']>0) {
            echo Html::timestampToString($this->fields['close_delay_stat']);
         } else {
            echo '&nbsp;';
         }
         echo "</td></tr>";
      }

      echo "<tr class='tab_bg_2'><td>".$LANG['joblist'][26]."&nbsp;:</td><td>";
      if ($this->fields['waiting_duration']>0) {
         echo Html::timestampToString($this->fields['waiting_duration'],0);
      } else {
         echo '&nbsp;';
      }
      echo "</td></tr>";

      echo "</table>";
      echo "</div>";
   }


   /** Get users_ids of itil object between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct users_ids which have itil object
   **/
   function getUsedAuthorBetween($date1='', $date2='') {
      global $DB;

      $linkclass = new $this->userlinkclass();
      $linktable = $linkclass->getTable();


      $query = "SELECT DISTINCT `glpi_users`.`id` AS users_id, `glpi_users`.`name` AS name,
                                `glpi_users`.`realname` AS realname,
                                `glpi_users`.`firstname` AS firstname
                FROM `".$this->getTable()."`
                LEFT JOIN `$linktable`
                     ON (`$linktable`.`".$this->getForeignKeyField()."` = `".$this->getTable()."`.`id`
                         AND `$linktable`.`type` = '".self::REQUESTER."')
                INNER JOIN `glpi_users` ON (`glpi_users`.`id` = `$linktable`.`users_id`)
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY realname, firstname, name";

      $result = $DB->query($query);
      $tab    = array();
      if ($DB->numrows($result) >= 1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["users_id"];
            $tmp['link'] = formatUserName($line["users_id"], $line["name"], $line["realname"],
                                          $line["firstname"], 1);
            $tab[] = $tmp;
         }
      }
      return $tab;
   }


   /** Get recipient of itil object  between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct recipents which have itil object
   **/
   function getUsedRecipientBetween($date1='', $date2='') {
      global $DB;


      $query = "SELECT DISTINCT `glpi_users`.`id` AS user_id,
                                `glpi_users`.`name` AS name,
                                `glpi_users`.`realname` AS realname,
                                `glpi_users`.`firstname` AS firstname
                FROM `".$this->getTable()."`
                LEFT JOIN `glpi_users`
                     ON (`glpi_users`.`id` = `".$this->getTable()."`.`users_id_recipient`)
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY realname, firstname, name";

      $result = $DB->query($query);
      $tab    = array();

      if ($DB->numrows($result) >= 1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["user_id"];
            $tmp['link'] = formatUserName($line["user_id"], $line["name"], $line["realname"],
                                          $line["firstname"], 1);
            $tab[] = $tmp;
         }
      }
      return $tab;
   }

   /** Get groups which have tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct groups of tickets
   **/
   function getUsedGroupBetween($date1='', $date2='') {
      global $DB;

      $linkclass = new $this->grouplinkclass();
      $linktable = $linkclass->getTable();

      $query = "SELECT DISTINCT `glpi_groups`.`id`, `glpi_groups`.`completename`
                FROM `".$this->getTable()."`
                LEFT JOIN `$linktable`
                     ON (`$linktable`.`".$this->getForeignKeyField()."` = `".$this->getTable()."`.`id`
                         AND `$linktable`.`type` = '".self::REQUESTER."')
                LEFT JOIN `glpi_groups`
                     ON (`$linktable`.`groups_id` = `glpi_groups`.`id`)
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY `glpi_groups`.`completename`";

      $result = $DB->query($query);
      $tab    = array();

      if ($DB->numrows($result) >=1 ) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["id"];
            $tmp['link'] = $line["completename"];
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }

   /** Get recipient of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    * @param title : indicates if stat if by title (true) or type (false)
    *
    * @return array contains the distinct recipents which have tickets
   **/
   function getUsedUserTitleOrTypeBetween($date1='', $date2='', $title=true) {
      global $DB;

      $linkclass = new $this->userlinkclass();
      $linktable = $linkclass->getTable();

      if ($title) {
         $table = "glpi_usertitles";
         $field = "usertitles_id";
      } else {
         $table = "glpi_usercategories";
         $field = "usercategories_id";
      }

      $query = "SELECT DISTINCT `glpi_users`.`$field`
                FROM `".$this->getTable()."`
                INNER JOIN `$linktable`
                     ON (`".$this->getTable()."`.`id` = `$linktable`.`".$this->getForeignKeyField()."`)
                INNER JOIN `glpi_users` ON (`glpi_users`.`id` = `$linktable`.`users_id`)
                LEFT JOIN `$table` ON (`$table`.`id` = `glpi_users`.`$field`)
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1)||!empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`", $date1, $date2).") ";
      }
      $query .=" ORDER BY `glpi_users`.`$field`";

      $result = $DB->query($query);
      $tab    = array();
      if ($DB->numrows($result) >=1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line[$field];
            $tmp['link'] = Dropdown::getDropdownName($table, $line[$field]);
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }


   /**
    * Get priorities of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct priorities of tickets
   **/
   function getUsedPriorityBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `priority`
                FROM `".$this->getTable()."`
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY `priority`";

      $result = $DB->query($query);
      $tab    = array();
      if ($DB->numrows($result) >= 1) {
         $i = 0;
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["priority"];
            $tmp['link'] = self::getPriorityName($line["priority"]);
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }


   /**
    * Get urgencies of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct priorities of tickets
   **/
   function getUsedUrgencyBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `urgency`
                FROM `".$this->getTable()."`
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`",$date1,$date2).") ";
      }
      $query .= " ORDER BY `urgency`";

      $result = $DB->query($query);
      $tab    = array();

      if ($DB->numrows($result) >= 1) {
         $i = 0;
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["urgency"];
            $tmp['link'] = self::getUrgencyName($line["urgency"]);
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }


   /**
    * Get impacts of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct priorities of tickets
   **/
   function getUsedImpactBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `impact`
                FROM `".$this->getTable()."`
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY `impact`";
      $result = $DB->query($query);
      $tab    = array();

      if ($DB->numrows($result) >= 1) {
         $i = 0;
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["impact"];
            $tmp['link'] = self::getImpactName($line["impact"]);
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }


   /**
    * Get request types of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct request types of tickets
   **/
   function getUsedRequestTypeBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `requesttypes_id`
                FROM `".$this->getTable()."`
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY `requesttypes_id`";

      $result = $DB->query($query);
      $tab    = array();
      if ($DB->numrows($result) >= 1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["requesttypes_id"];
            $tmp['link'] = Dropdown::getDropdownName('glpi_requesttypes', $line["requesttypes_id"]);
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }

   /**
    * Get solution types of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct request types of tickets
   **/
   function getUsedSolutionTypeBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `solutiontypes_id`
                FROM `".$this->getTable()."`
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY `solutiontypes_id`";

      $result = $DB->query($query);
      $tab    = array();
      if ($DB->numrows($result) >=1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["solutiontypes_id"];
            $tmp['link'] = Dropdown::getDropdownName('glpi_solutiontypes',
                                                     $line["solutiontypes_id"]);
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }



   /** Get users which have intervention assigned to  between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct users which have any intervention assigned to.
   **/
   function getUsedTechBetween($date1='',$date2='') {
      global $DB;

      $linkclass = new $this->userlinkclass();
      $linktable = $linkclass->getTable();

      $query = "SELECT DISTINCT `glpi_users`.`id` AS users_id,
                                `glpi_users`.`name` AS name,
                                `glpi_users`.`realname` AS realname,
                                `glpi_users`.`firstname` AS firstname
                FROM `".$this->getTable()."`
                LEFT JOIN `$linktable`
                           ON (`$linktable`.`".$this->getForeignKeyField()."` = `".$this->getTable()."`.`id`
                               AND `$linktable`.`type` = '".self::ASSIGN."')
                LEFT JOIN `glpi_users` ON (`glpi_users`.`id` = `$linktable`.`users_id`)
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1)||!empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY realname, firstname, name";

      $result = $DB->query($query);
      $tab    = array();

      if ($DB->numrows($result) >=1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["users_id"];
            $tmp['link'] = formatUserName($line["users_id"], $line["name"], $line["realname"],
                                          $line["firstname"], 1);
            $tab[] = $tmp;
         }
      }
      return $tab;
   }


   /** Get users which have followup assigned to  between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct users which have any followup assigned to.
   **/
   function getUsedTechTaskBetween($date1='',$date2='') {
      global $DB;

      $tasktable = getTableForItemType($this->getType().'Task');


      $query = "SELECT DISTINCT `glpi_users`.`id` AS users_id,
                                `glpi_users`.`name` AS name,
                                `glpi_users`.`realname` AS realname,
                                `glpi_users`.`firstname` AS firstname
                FROM `".$this->getTable()."`
                LEFT JOIN `$tasktable`
                     ON (`".$this->getTable()."`.`id` = `$tasktable`.`".$this->getForeignKeyField()."`)
                LEFT JOIN `glpi_users` ON (`glpi_users`.`id` = `$tasktable`.`users_id`)
                LEFT JOIN `glpi_profiles_users`
                     ON (`glpi_users`.`id` = `glpi_profiles_users`.`users_id`)
                LEFT JOIN `glpi_profiles`
                     ON (`glpi_profiles`.`id` = `glpi_profiles_users`.`profiles_id`)
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`", $date1, $date2).") ";
      }
      $query .="     AND `glpi_profiles`.`own_ticket` = 1
                     AND `$tasktable`.`users_id` <> '0'
                     AND `$tasktable`.`users_id` IS NOT NULL
               ORDER BY realname, firstname, name";

      $result = $DB->query($query);
      $tab    = array();

      if ($DB->numrows($result) >= 1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["users_id"];
            $tmp['link'] = formatUserName($line["users_id"], $line["name"], $line["realname"],
                                          $line["firstname"], 1);
            $tab[] = $tmp;
         }
      }
      return $tab;
   }


   /** Get enterprises which have followup assigned to between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct enterprises which have any tickets assigned to.
   **/
   function getUsedSupplierBetween($date1='', $date2='') {
      global $DB,$CFG_GLPI;

      $query = "SELECT DISTINCT `glpi_suppliers`.`id` AS suppliers_id_assign,
                                `glpi_suppliers`.`name` AS name
                FROM `".$this->getTable()."`
                LEFT JOIN `glpi_suppliers`
                     ON (`glpi_suppliers`.`id` = `".$this->getTable()."`.`suppliers_id_assign`)
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY name";

      $tab    = array();
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp["id"]   = $line["suppliers_id_assign"];
            $tmp["link"] = "<a href='".$CFG_GLPI["root_doc"]."/front/supplier.form.php?id=".
                           $line["suppliers_id_assign"]."'>".$line["name"]."</a>";
            $tab[] = $tmp;
         }
      }
      return $tab;
   }



   /** Get groups assigned to tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct groups assigned to a tickets
   **/
   function getUsedAssignGroupBetween($date1='', $date2='') {
      global $DB;

      $linkclass = new $this->grouplinkclass();
      $linktable = $linkclass->getTable();

      $query = "SELECT DISTINCT `glpi_groups`.`id`, `glpi_groups`.`completename`
                FROM `".$this->getTable()."`
                LEFT JOIN `$linktable`
                     ON (`$linktable`.`".$this->getForeignKeyField()."` = `".$this->getTable()."`.`id`
                         AND `$linktable`.`type` = '".self::ASSIGN."')
                LEFT JOIN `glpi_groups`
                     ON (`$linktable`.`groups_id` = `glpi_groups`.`id`)
                WHERE NOT `".$this->getTable()."`.`is_deleted` ".
                getEntitiesRestrictRequest("AND", $this->getTable());

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`".$this->getTable()."`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`".$this->getTable()."`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY `glpi_groups`.`completename`";

      $result = $DB->query($query);
      $tab    = array();
      if ($DB->numrows($result) >=1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["id"];
            $tmp['link'] = $line["completename"];
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }



}
?>
