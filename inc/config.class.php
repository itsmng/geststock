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

class PluginGeststockConfig extends CommonDBTM {

   static $rightname  = 'plugin_geststock';
   const TOVA         = 0;



   static function getTypeName($nb=0) {
      return __('Setup');
   }


   static function install(Migration $mig) {
      global $DB;

      $table = 'glpi_plugin_geststock_configs';
      if (!$DB->tableExists($table)) { //not installed
         $query = "CREATE TABLE `". $table."`(
                     `id` int(11) NOT NULL,
                     `entities_id_stock` int(11) NULL,
                     `stock_status` int(11) NULL,
                     `transit_status` int(11) NULL,
                     `date_mod` datetime default NULL,
                     `users_id` int(11) NULL,
                     `criterion` varchar(100) NOT NULL,
                     PRIMARY KEY  (`id`),
                     KEY `users_id` (`users_id`),
                     KEY `entities_id_stock` (`entities_id_stock`),
                     KEY `stock_status` (`stock_status`),
                     KEY `transit_status` (`transit_status`)
                   ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->queryOrDie($query, 'Error in creating glpi_plugin_geststock_configs'.
                                 "<br>".$DB->error());
      } else { // migration for maedi
         $migration = new Migration(100);
         if (!$DB->fieldExists($table, "stock_status")) {
            $migration->addField($table, 'criterion', 'string', ['value' => 'otherserial',
                                                                 'after' => 'entities_id_stock']);
            $migration->addField($table, 'transit_status', 'integer', ['value' => 3,
                                                                       'after' => 'entities_id_stock']);
            $migration->addField($table, 'stock_status', 'integer', ['value' => 1,
                                                                     'after' => 'entities_id_stock']);
         }
         $migration->executeMigration();
      }
      return true;
   }


   static function uninstall() {
      global $DB;

      if ($DB->tableExists('glpi_plugin_geststock_configs')) {
         $query = "DROP TABLE `glpi_plugin_geststock_configs`";
         $DB->queryOrDie($query, $DB->error());
      }
      return true;
   }


   public function showForm($ID = 1, $options = [])
   {
      if (!Session::haveRight(self::$rightname, UPDATE)) {
         return false;
      }

      $this->getFromDB($ID);

      $form = [
         'action' => $this->getFormURL(),
         'itemtype' => self::class,
         'id' => $ID,
         'content' => [
            'General' => [
               'visible' => true,
               'inputs' => [
                  __('Entity of stock', 'geststock') => [
                     'name' => 'entities_id_stock',
                     'type' => 'select',
                     'itemtype' => Entity::class,
                     'value' => $this->fields['entities_id_stock'] ?? 0,
                     'actions' => getItemActionButtons(['info'], "Entity"),
                  ],
                  __('Status of item in stock', 'geststock') => [
                     'name' => 'stock_status',
                     'type' => 'select',
                     'itemtype' => State::class,
                     'value' => $this->fields['stock_status'] ?? 0,
                     'actions' => getItemActionButtons(['info'], "State"),
                  ],
                  __('Status of item in transit', 'geststock') => [
                     'name' => 'transit_status',
                     'type' => 'select',
                     'itemtype' => State::class,
                     'value' => $this->fields['transit_status'] ?? 0,
                     'actions' => getItemActionButtons(['info'], "State"),
                  ],
                  __('Criterion of items', 'geststock') => [
                     'name' => 'criterion',
                     'type' => 'select',
                     'values' => [
                        'serial' => __('Serial number'),
                        'otherserial' => __('Inventory number'),
                     ],
                     'value' => $this->fields['criterion'] ?? 'otherserial',
                  ],
               ],
            ],
         ],
      ];

      renderTwigForm($form, '', $this->fields);
      return true;
   }

}
