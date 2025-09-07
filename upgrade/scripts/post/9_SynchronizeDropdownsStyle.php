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

use Sugarcrm\Sugarcrm\Dropdowns\DropdownsManager;

/**
 * Synchronize dropdowns style
 */
class SugarUpgradeSynchronizeDropdownsStyle extends UpgradeScript
{
    public $order = 9500;
    public $type = self::UPGRADE_DB;

    /**
     * @throws SugarQueryException
     */
    public function run()
    {
        $this->log('Running 9_SynchronizeDropdownsStyle script...');

        if (version_compare($this->from_version, '14.1.0', '<')) {
            $this->log('Synchronizing dropdowns style');
            DropdownsManager::synchronizeDropdownsStyle();
            $this->log('Successfully Synchronized dropdowns style');
        } else {
            $this->log('Not synchronizing dropdowns style');
        }
    }
}
