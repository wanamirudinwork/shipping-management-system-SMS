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

namespace Sugarcrm\Sugarcrm\CloudDrive\Drives;

use Microsoft\Graph\Generated\Drives\Item\Items\Item\Children\ChildrenRequestBuilderGetQueryParameters;
use Microsoft\Graph\Generated\Drives\Item\Items\Item\Children\ChildrenRequestBuilderGetRequestConfiguration;
use Microsoft\Graph\Generated\Models\AssignedPlan;
use Microsoft\Graph\Generated\Models\DriveItem;
use Microsoft\Graph\Generated\Models\Folder;
use Microsoft\Graph\Generated\Sites\SitesRequestBuilderGetRequestConfiguration;
use Microsoft\Graph\Generated\Users\Item\UserItemRequestBuilderGetQueryParameters;
use Microsoft\Graph\Generated\Users\Item\UserItemRequestBuilderGetRequestConfiguration;
use Microsoft\Graph\Model;
use Sugarcrm\Sugarcrm\CloudDrive\Constants\DriveType;
use Sugarcrm\Sugarcrm\CloudDrive\Model\DriveItemMapper;

class SharepointDrive extends OneDrive
{
    /**
     * List files in a folder
     *
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function listFiles(array $options): array
    {
        $driveId = $options['driveId'] ?? null;
        $siteId = $options['siteId'] ?? null;
        $sortOptions = $options['sortOptions'] ?? null;
        $parentId = $options['folderId'] ?? $options['parentId'] ?? 'root';
        $onlyFolders = $options['onlyFolders'] ?? false;
        $nextPageToken = array_key_exists('nextPageToken', $options) ? $options['nextPageToken'] : null;

        $folderName = array_key_exists('folderName', $options) ? $options['folderName'] : null;

        $client = $this->getClient();
        if (is_array($client) && !$client['success']) {
            return $client;
        }

        try {
            if (!$siteId) {
                // If no siteId, display sites
                $queryParameters = SitesRequestBuilderGetRequestConfiguration::createQueryParameters();
                $queryParameters->search = '"s*"';
                $requestConfiguration = new SitesRequestBuilderGetRequestConfiguration(queryParameters: $queryParameters);

                $sites = $client->sites()->get($requestConfiguration)->wait();
                return $this->handleData($sites) + [
                        'displayingSites' => true,
                        'displayingDocumentDrives' => false,
                    ];
            } elseif (!$driveId) {
                // $siteId && !$driveId
                // Navigating into document libraries
                $drives = $client->sites()->bySiteId($siteId)->drives()->get()->wait();
                return $this->handleData($drives) + [
                        'displayingDocumentDrives' => true,
                        'displayingSites' => false,
                    ];
            } else {
                // $siteId && $driveId
                // Fetch files from a drive
                global $sugar_config;
                $top = $sugar_config['list_max_entries_per_page'];
                $filter = $onlyFolders ? 'folder ne null' : null;
                $orderBy = $sortOptions ? ["{$sortOptions['fieldName']} {$sortOptions['direction']}"] : null;

                $queryParams = new ChildrenRequestBuilderGetQueryParameters(filter: $filter, orderby: $orderBy, top: $top);
                $requestConfig = new ChildrenRequestBuilderGetRequestConfiguration(queryParameters: $queryParams);

                $driveItemsRequest = $client->drives()->byDriveId($driveId)->items()->byDriveItemId($parentId)->children();

                if ($nextPageToken) {
                    $driveItemsRequest = $driveItemsRequest->withUrl($nextPageToken);
                }

                $driveItems = $driveItemsRequest->get($requestConfig)->wait();

                return $this->handleData($driveItems, $folderName) + [
                        'displayingDocumentDrives' => false,
                        'displayingSites' => false,
                    ];
            }
        } catch (\Exception $e) {
            $GLOBALS['log']->error($e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle data from the API
     * @param mixed $dataCollection
     * @param mixed $folderName
     * @return array
     */
    private function handleData($dataCollection, $folderName = null): array
    {
        $nextLink = $dataCollection->getOdataNextLink();

        $mapper = new DriveItemMapper($dataCollection->getValue(), DriveType::SHAREPOINT);
        $mappedData = $mapper->mapToArray();

        if ($folderName) {
            $mappedData = array_values(array_filter($mappedData, function ($item) use ($folderName) {
                return $item->name === $folderName;
            }));
        }

        return [
            'files' => $mappedData,
            'nextPageToken' => $nextLink,
        ];
    }

    /**
     * List folders in a drive
     *
     * @param array $options
     * @return array
     */
    public function listFolders(array $options)
    {
        $options['onlyFolders'] = true;
        // filter only folders
        return $this->listFiles($options);
    }

    /**
     * Create a folder on the drive
     *
     * @param array $options
     * @return null|string
     * @throws \Exception
     */
    public function createFolder(array $options): ?array
    {
        $folderName = $options['name'] ?? null;
        $parentId = $options['parent'] ?? 'root';
        $driveId = $options['driveId'] ?? null;

        if (!$folderName || !$driveId) {
            return null;
        }

        $client = $this->getClient();

        $folder = new Folder();
        $folder->setChildCount(0);

        $driveItem = new DriveItem();
        $driveItem->setName($folderName);
        $driveItem->setFolder($folder);
        $driveItem->setAdditionalData(['@microsoft.graph.conflictBehavior' => 'rename']);

        try {
            if (isset($parentId)) {
                $drive = $client->drives()->byDriveId($driveId)->items()->byDriveItemId($parentId)->children()->post($driveItem)->wait();
            } else {
                $drive = $client->drives()->byDriveId($driveId)->items()->post($driveItem)->wait();
            }
        } catch (\Exception $e) {
            throw new \SugarException(translate('LBL_DRIVE_ERROR_CREATING_FOLDER', 'Administration'));
        }

        $parentReference = $drive->getParentReference();
        $parentDriveId = $parentReference->getDriveId();
        $parentId = $drive->getId();

        return [
            'driveId' => $parentDriveId,
            'id' => $parentId,
        ];
    }

    /**
     * Get microsoft api client
     *
     * @return object|array
     * @throws \Exception
     */
    public function getClient()
    {
        $client = parent::getClient();

        if (is_array($client) && !$client['success']) {
            return $client;
        }

        $u = new UserItemRequestBuilderGetQueryParameters(
            select: ['userPrincipalName','assignedPlans']
        );
        $r = new UserItemRequestBuilderGetRequestConfiguration(
            queryParameters: $u
        );
        $userData = $client->me()->get($r)->wait();

        if (!isset($userData) || !$userData || !$this->hasSharePointService($userData->getAssignedPlans())) {
            return [
                'success' => false,
                'message' => 'LBL_CHECK_MICROSOFT_CONNECTION',
            ];
        }

        return $client;
    }

    /**
     * Get the client's profile including assigned plans
     *
     * @param string $url
     * @param object $client
     * @return array|bool
     */
    public function getClientProfile(string $url, object $client)
    {
        $request = $client->createCollectionRequest('GET', $url);
        $response = false;

        try {
            $requestExecution = $request->execute();
            $response = $requestExecution->getBody();
        } catch (\Exception $e) {
            $GLOBALS['log']->error($e->getMessage());
        }

        return $response;
    }

    /**
     * Checks if the client has SharePoint Service
     *
     * @param AssignedPlan[] $data
     * @return bool
     */
    public function hasSharePointService(array $data): bool
    {
        foreach ($data as $assignedPlan) {
            if ($assignedPlan->getService() === 'SharePoint') {
                return true;
            }
        }

        return false;
    }
}
