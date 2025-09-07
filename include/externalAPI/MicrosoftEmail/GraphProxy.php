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

use Microsoft\Graph\GraphServiceClient;

/**
 * A proxy class for the Microsoft Graph API
 */
class GraphProxy extends GraphServiceClient
{
    private $authURL;

    public function __construct(string $refreshToken, array $config)
    {
        $tokenRequestContext = new OnBehalfOfContextUsingRefreshToken(
            tenantId: !empty($config['properties']['oauth2_single_tenant_id']) ? $config['properties']['oauth2_single_tenant_id'] : 'common',
            clientId: $config['properties']['oauth2_client_id'] ?? '',
            clientSecret: $config['properties']['oauth2_client_secret'] ?? '',
            assertion: $refreshToken
        );

        parent::__construct($tokenRequestContext);
    }

    /**
     * Sets the authorization URL to be used by the front-end for authorizing
     * a user with Microsoft servers
     *
     * @param string $url the authorization URL to set
     */
    public function setAuthURL(string $url)
    {
        $this->authURL = $url;
    }

    /**
     * Returns the authorization URL to be used by the front-end for authorizing
     * a user with Microsoft servers. Named 'createAuthURL' rather than
     * 'getAuthURL' to keep consistency with other external API clients
     *
     * @return string|null the authorization URL
     */
    public function createAuthURL()
    {
        return $this->authURL;
    }
}
