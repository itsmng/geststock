<?php
/*
 -------------------------------------------------------------------------
 LICENSE

 This file is part of GestStock plugin for GLPI.

 GestStock is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 GestStock is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with GestStock. If not, see <http://www.gnu.org/licenses/>.

 @package   geststock
 @author    Nelly Mahu-Lasson
 @copyright Copyright (c) 2017-2021 GestStock plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link
 @since     version 1.0.0
 --------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (isset($_POST["itemtype"]) && isset($_POST["model"])) {
   global $DB;

   $itemtype = $_POST["itemtype"];
   $model = (int)$_POST["model"];

   $config = new PluginGeststockConfig();
   $config->getFromDB(1);
   $entity = $config->fields['entities_id_stock'];

   echo "<table style='width: 100%;'><tr>";
   $find = false;
   $i = 0;

   foreach ($DB->request('glpi_locations', ['entities_id' => $entity]) as $location) {
      // Count available items
      $nb = 0;
      if (class_exists('PluginGeststockReservation_Item')) {
         $nb = PluginGeststockReservation_Item::countAvailable($itemtype, $model, $entity, $location['id']);
      }

      if ($nb > 0) {
         if ($i == 3) {
            echo "</tr><tr>";
         }
         echo "<td style='padding: 5px;'>" . $location['name'] . " <span style='color: blue; font-weight: bold;'>(" . $nb . ")</span></td>";
         echo "<td style='padding: 5px;'>";
         echo "<select name='_nbrereserv[" . $location['id'] . "]' class='form-select' style='width: 80px;'>";
         echo "<option value='0'>0</option>";
         for ($j = 1; $j <= $nb; $j++) {
            echo "<option value='" . $j . "'>" . $j . "</option>";
         }
         echo "</select>";
         $find = true;
         $i++;
         echo "</td>";
         if ($i == 3) {
            $i = 0;
         }
      }
   }

   if (!$find) {
      echo "<td style='color: red; font-weight: bold;'>No free item</td>";
   }
   echo "</tr></table>";
}
