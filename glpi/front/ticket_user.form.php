<?php
/*
 * @version $Id: ticket_user.form.php 20130 2013-02-04 16:55:15Z moyo $
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
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------

$ticket_user = new Ticket_User();

Session ::checkLoginUser();

if (isset($_REQUEST["update"])) {
   $ticket_user->check($_REQUEST["id"], 'w');

   $ticket_user->update($_REQUEST);
   echo "<script type='text/javascript' >\n";
   echo "window.opener.location.reload();";
   echo "window.close()";
   echo "</script>";

} else if (isset($_REQUEST["id"])) {
   $ticket_user->showUserNotificationForm($_REQUEST["id"]);
}

?>