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

// Make a select box
if (isset($_POST["itemtype"]) && isset($_POST["myname"])) {
   $itemtype = $_POST['itemtype'];
   $myname = $_POST['myname'];
   $entity = isset($_POST['entity']) ? $_POST['entity'] : -1;

   // Determine the model class name
   $modelclass = $itemtype . 'Model';
   if ($itemtype == "PluginSimcardSimcard") {
      $modelclass = 'PluginSimcardSimcardType';
   }

   // Get available models
   if (class_exists($modelclass)) {
      $model = new $modelclass();
      $models = $model->find([], 'name');

      $rand = mt_rand();
      echo "<select name='" . $myname . "' id='dropdown_" . $myname . $rand . "' class='form-select' style='width: 80%;'>";
      echo "<option value='0'>-----</option>";

      foreach ($models as $m) {
         echo "<option value='" . $m['id'] . "'>" . $m['name'] . "</option>";
      }
      echo "</select>";

      // Setup AJAX listener for model changes
      $field_id = 'dropdown_' . $myname . $rand;

      echo "<span id='show_number_" . $rand . "' style='spacing:5px;'>&nbsp;</span>";

      echo "<script>";
      echo "jQuery(document).ready(function() {
         var modelDropdown = jQuery('#" . $field_id . "');

         // When model changes, call dropdownNumber.php
         modelDropdown.on('change', function() {
            var modelValue = jQuery(this).val();
            if (modelValue > 0) {
               jQuery.ajax({
                  url: '" . Plugin::getWebDir('geststock') . "/ajax/dropdownNumber.php',
                  type: 'POST',
                  data: {
                     itemtype: '" . addslashes($itemtype) . "',
                     model: modelValue,
                     entity: '" . $entity . "'
                  },
                  success: function(data) {
                     jQuery('#show_number_" . $rand . "').html(data);
                  }
               });
            } else {
               jQuery('#show_number_" . $rand . "').html('&nbsp;');
            }
         });
      });";
      echo "</script>";
   }
}