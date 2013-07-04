<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

class CRM_Core_DAO_AllCoreTables {ldelim}

  static private $tables = null;
  static private $daoToClass = null;

  static private function init() {ldelim}
    static $init = FALSE;
    if ($init) return;

    $entityTypes = array(
{foreach from=$tables key=tableName item=table}
      '{$table.className}' => array(
        'name' => '{$table.objectName}',
        'class' => '{$table.className}',
        'table' => '{$tableName}',
      ),
{/foreach}
    );

    CRM_Utils_Hook::entityTypes($entityTypes);

    self::$tables = array();
    self::$daoToClass = array();
    foreach ($entityTypes as $entityType) {ldelim}
      self::registerEntityType($entityType['name'], $entityType['class'], $entityType['table']);
    {rdelim}

    $init = TRUE;
  {rdelim}

  static private function registerEntityType($daoName, $className, $tableName) {ldelim}
    self::$daoToClass[$daoName] = $className;
    self::$tables[$tableName] = $className;
  {rdelim}

  static public function tables() {ldelim}
    self::init();
    return self::$tables;
  {rdelim}

  static public function daoToClass() {ldelim}
    self::init();
    return self::$daoToClass;
  {rdelim}

  static public function getCoreTables() {ldelim}
    return self::tables();
  {rdelim}

  static public function isCoreTable($tableName) {ldelim}
    return FALSE !== array_search($tableName, self::tables());
  {rdelim}

  static public function getClasses() {ldelim}
    return array_values(self::daoToClass());
  {rdelim}

  static public function getClassForTable($tableName) {ldelim}
    return CRM_Utils_Array::value($tableName, self::tables());
  {rdelim}

  static public function getFullName($daoName) {ldelim}
    return CRM_Utils_Array::value($daoName, self::daoToClass());
  {rdelim}

  static public function getBriefName($className) {ldelim}
    return CRM_Utils_Array::value($className, array_flip(self::daoToClass()));
  {rdelim}

{rdelim}
