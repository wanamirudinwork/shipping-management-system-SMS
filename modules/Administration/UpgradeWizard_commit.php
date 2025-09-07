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

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;
use Sugarcrm\Sugarcrm\Security\Csrf\CsrfAuthenticator;
use Sugarcrm\Sugarcrm\PackageManager\PackageManager;

MlpLogger::replaceDefault();

$request = InputValidation::getService();
$mode = $request->getValidInputRequest(
    'mode',
    [
        'Assert\Choice' => [
            'choices' => [
                'PreCheck',
                'Scan',
                'RectorScan',
                'Install',
                'Uninstall',
                'Disable',
                'Enable',
            ],
        ],
    ],
    ''
);
if (empty($mode)) {
    sugar_die(htmlspecialchars(translate('ERR_UW_NO_MODE', 'Administration'), ENT_COMPAT));
}

$removeTables = $request->getValidInputRequest('remove_tables');
$overwrite = $request->getValidInputRequest('radio_overwrite', null, 'overwrite');
$overwriteFiles = $overwrite !== 'do_not_overwrite';

$historyId = $request->getValidInputRequest('package_id');
if (empty($historyId)) {
    sugar_die(htmlspecialchars(translate('ERR_UW_NO_PACKAGE_FILE', 'Administration'), ENT_COMPAT));
}

$upgradeHistory = BeanFactory::retrieveBean('UpgradeHistory', $historyId, ['encode' => false]);
if (!$upgradeHistory instanceof UpgradeHistory || empty($upgradeHistory->id)) {
    sugar_die(htmlspecialchars(translate('ERR_UW_NO_PACKAGE_FILE', 'Administration'), ENT_COMPAT));
}

try {
    $manifest = $upgradeHistory->getPackageManifest();
    $installType = $manifest->getPackageType();
    $removeTables = is_null($removeTables) ? $manifest->shouldTablesBeRemoved() : $removeTables === 'true';
} catch (Exception $e) {
    sugar_die($e->getMessage());
}

$shouldClearCache = true;
$packageManager = new PackageManager();
$packageManager->setSilent(false);
try {
    switch ($mode) {
        case 'PreCheck':
            ob_end_clean();
            $fieldsToReturn = ['error', 'error_page', 'message', 'pre_install_step'];
            $packageManager->preCheckPackage($upgradeHistory);
            echo json_encode($upgradeHistory->getProcessStatus($fieldsToReturn));
            exit;
        case 'Scan':
            $content = ob_get_clean();

            $result = $packageManager->scanPreparedPackage($upgradeHistory);

            $fieldsToReturn = ['error', 'error_page', 'message', 'pre_install_step'];
            $processStatus = $upgradeHistory->getProcessStatus($fieldsToReturn);

            if (!$result) {
                $processStatus['error'] = $content . $processStatus['error'];
            }

            echo json_encode($processStatus);

            exit;
        case 'RectorScan':
            $content = ob_get_clean();

            $result = $packageManager->rectorScanPreparedPackage($upgradeHistory);

            $fieldsToReturn = ['error', 'error_page', 'message', 'pre_install_step', 'files_for_rector_scan_initial_count', 'files_for_rector_scan'];
            $processStatus = $upgradeHistory->getProcessStatus($fieldsToReturn);
            $remainedFileCount = count($processStatus['files_for_rector_scan'] ?? []);
            $totalFileCount = $processStatus['files_for_rector_scan_initial_count'] ?? 0;
            $processStatus['files_scanned'] = $totalFileCount - $remainedFileCount;
            $processStatus['files_remained'] = $remainedFileCount;
            $processStatus['scan_progress'] = round(($totalFileCount > 0
                ? $processStatus['files_scanned'] / $totalFileCount
                : 0) * 100);
            unset($processStatus['files_for_rector_scan']);

            if (!$result) {
                $processStatus['error'] = $content . $processStatus['error'];
            }

            echo json_encode($processStatus);

            exit;
        case 'Install':
            $packageManager->installCheckedAndScannedPackage($upgradeHistory);
            $shouldClearCache = false;
            break;
        case 'Uninstall':
            $packageManager->uninstallPackage($upgradeHistory, $removeTables);
            break;
        case 'Enable':
            $packageManager->enablePackage($upgradeHistory, $overwriteFiles);
            break;
        case 'Disable':
            $packageManager->disablePackage($upgradeHistory, $overwriteFiles);
            break;
    }
} catch (SugarException $e) {
    sugar_die($e->getMessage());
} catch (Exception $e) {
    sugar_die(htmlspecialchars(translate('ERR_UW_NO_PACKAGE_FILE', 'Administration'), ENT_COMPAT));
}

if ($shouldClearCache) {
    MetaDataManager::clearAPICache();
}

$resultText = sprintf(
    '%s %s %s',
    htmlspecialchars(translate('LBL_UW_TYPE_' . strtoupper($installType), 'Administration'), ENT_COMPAT),
    htmlspecialchars(translate('LBL_UW_MODE_' . strtoupper($mode), 'Administration'), ENT_COMPAT),
    htmlspecialchars(translate('LBL_UW_SUCCESSFULLY', 'Administration'), ENT_COMPAT)
);
$buttonText = htmlspecialchars(translate('LBL_UW_BTN_BACK_TO_MOD_LOADER', 'Administration'), ENT_COMPAT);
$csrfFieldName = CsrfAuthenticator::FORM_TOKEN_FIELD;
$csrfToken = htmlspecialchars(CsrfAuthenticator::getInstance()->getFormToken(), ENT_QUOTES, 'UTF-8');

echo <<<HTML
<form action="index.php?module=Administration&view=module&action=UpgradeWizard" method="post">
<input type="hidden" name="reloadMetadata" value="true" />
<input type="hidden" name="$csrfFieldName" value="$csrfToken" />
<div>
    $resultText<br /><br />
    <input type=submit value="$buttonText" /><br />
</div>
</form>
HTML;
