<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


class SugarFieldDate extends SugarFieldDatetime
{
    /**
     * Handles export field sanitizing for field type
     *
     * @param $value string value to be sanitized
     * @param $vardef array representing the vardef definition
     * @param $focus SugarBean object
     * @param $row Array of a row of data to be exported
     *
     * @return string sanitized value
     */
    public function exportSanitize($value, $vardef, $focus, $row = [])
    {
        $timedate = TimeDate::getInstance();
        $db = DBManagerFactory::getInstance();
        //If it's in ISO format, convert it to db format
        if (preg_match('/(\d{4})\-?(\d{2})\-?(\d{2})T(\d{2}):?(\d{2}):?(\d{2})\.?\d*([Z+-]?)(\d{0,2}):?(\d{0,2})/i', (string)$value)) {
            $value = $timedate->fromIso($value)->asDbDate(false);
        }

        return $timedate->to_display_date($db->fromConvert($value, 'date'), false);
    }

    /**
     * {@inheritdoc}
     */
    public function fixForFilter(&$value, $columnName, SugarBean $bean, SugarQuery $q, SugarQuery_Builder_Where $where, $op)
    {
        if (in_array($op, $this->amountDaysOperators)) {
            $this->fixForAmountDaysFilter($value, $columnName, $bean, $where, $op);
            return false;
        }

        if ('$dateRange' === $op && in_array($value, $this->largeRangeOperators)) {
            $this->fixForLargeRangeFilter($value, $columnName, $bean, $where, $op);
            return false;
        }
        return true;
    }

    /**
     * pass value through
     * @param $value
     * @return string
     */
    public function apiUnformatField($value)
    {
        global $current_user;
        if (strlen(trim($value)) < 11) {
            $newValue = TimeDate::getInstance()->fromIsoDate($value, $current_user);
        } else {
            $newValue = TimeDate::getInstance()->fromIso($value, $current_user);
        }

        if (is_object($newValue)) {
            $value = $newValue->asDbDate();
        }

        return $value;
    }

    /**
     * Sanitize date value
     * @param string $value
     * @param $vardef
     * @param SugarBean $focus
     * @param ImportFieldSanitize $settings
     * @return bool|string
     */
    public function importSanitize($value, $vardef, $focus, ImportFieldSanitize $settings)
    {
        try {
            $date = SugarDateTime::createFromFormat(
                $settings->dateformat,
                $value
            );
            if ($date === false) {
                return false;
            }
            if (intval($date->year) < 100) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        return $date->asDbDate(false);
    }
}
