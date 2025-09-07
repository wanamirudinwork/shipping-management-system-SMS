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

namespace Sugarcrm\IdentityProvider\Tests\Unit\Authentication\Provider;

use Sugarcrm\IdentityProvider\Authentication\Provider\SAMLAuthenticationProvider;
use Sugarcrm\IdentityProvider\Authentication\Token\SAML\ConsumeLogoutToken;
use Sugarcrm\IdentityProvider\Authentication\Token\SAML\AcsToken;
use Sugarcrm\IdentityProvider\Authentication\Token\SAML\IdpLogoutToken;
use Sugarcrm\IdentityProvider\Authentication\Token\SAML\InitiateLogoutToken;
use Sugarcrm\IdentityProvider\Authentication\Token\SAML\InitiateToken;
use Sugarcrm\IdentityProvider\Authentication\Token\SAML\ResultToken;
use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\Authentication\UserProvider\SAMLUserProvider;
use Sugarcrm\IdentityProvider\Tests\IDMFixturesHelper;
use Sugarcrm\IdentityProvider\Saml2\Request\AuthnRequest;
use Sugarcrm\IdentityProvider\Authentication\UserMapping\SAMLUserMapping;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

use OneLogin\Saml2\Response;

/**
 * Class covers all step of SAML authentication.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Sugarcrm\IdentityProvider\Authentication\Provider\SAMLAuthenticationProvider::class)]
class SAMLAuthenticationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var SAMLUserProvider | \PHPUnit_Framework_MockObject_MockObject */
    protected $samlUserProvider = null;
    /**
     * @var Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response = null;

    /**
     * @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session = null;

    /**
     * @var SAMLUserMapping
     */
    protected $userMapping = null;

    /**
     * @var UserCheckerInterface
     */
    protected $samlUserChecker;

    /**
     * @var User
     */
    protected $user;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createMock(User::class);
        $this->samlUserProvider = $this->createMock(SAMLUserProvider::class);
        $this->samlUserProvider->method('loadUserByIdentifier')->willReturn($this->user);
        $this->samlUserChecker = $this->getMockBuilder(UserCheckerInterface::class)->getMock();
        $this->session = $this->createMock(SessionInterface::class);
        $this->response = $this->createMock(Response::class);
        $this->userMapping = $this->getMockBuilder(SAMLUserMapping::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['map', 'mapIdentity'])
            ->getMock();
        $this->userMapping->method('map')->willReturn([]);
    }

    /**
     * Provides valid settings for tests.
     * @see testInitiateLogin
     * @return array
     */
    public static function samlSettingsProvider()
    {
        return [
            'neededValidationRequestId' => [
                'settings' => array_replace_recursive(
                    IDMFixturesHelper::getOktaParameters(),
                    ['security' => ['validateRequestId' => true]]
                ),
                'setIdInvocation' => self::once(),
            ],
            'notNeededValidationRequestId' => [
                'settings' => array_replace_recursive(
                    IDMFixturesHelper::getOktaParameters(),
                    ['security' => ['validateRequestId' => false]]
                ),
                'setIdInvocation' => self::never(),
            ],
        ];
    }

    /**
     * @param array $settings
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $setIdInvocation
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('samlSettingsProvider')]
    public function testInitiateLogin(array $settings, $setIdInvocation)
    {
        $samlProvider = new SAMLAuthenticationProvider(
            $settings,
            $this->samlUserProvider,
            $this->samlUserChecker,
            $this->session,
            $this->userMapping
        );

        $token = new InitiateToken();
        $this->session->expects($setIdInvocation)->method('set')->with(
            $this->equalTo(SAMLAuthenticationProvider::REQUEST_ID_KEY),
            $this->callback(function ($id) {
                $this->assertStringStartsWith(AuthnRequest::REQUEST_ID_PREFIX, $id);
                $this->assertEquals(AuthnRequest::REQUEST_ID_LENGTH, strlen($id));
                return true;
            })
        );

        $returnTo = 'http://local.host';
        $token->setAttribute('returnTo', $returnTo);
        $returnedToken = $samlProvider->authenticate($token);

        $this->assertFalse($returnedToken->isAuthenticated());

        $ssoUrl = $settings['idp']['singleSignOnService']['url'];
        self::assertStringContainsString($ssoUrl . '?SAMLRequest', $returnedToken->getAttribute('url'));
        self::assertStringContainsString('RelayState=' . urlencode($returnTo), $returnedToken->getAttribute('url'));
    }

    /**
     * Provides set of data for check POST binding initiate logout logic.
     *
     * @return array
     */
    public static function initiatePostLogoutProvider()
    {
        $oktaSettings = IDMFixturesHelper::getOktaParameters();
        return [
            'oktaWithRelayState' => [
                'settings' => $oktaSettings,
                'returnTo' => 'http://test.com',
                'expectedUrl' => $oktaSettings['idp']['singleLogoutService']['url'],
                'expectedMethod' => 'POST',
            ],
            'oktaWithoutRelayState' => [
                'settings' => $oktaSettings,
                'returnTo' => null,
                'expectedUrl' => $oktaSettings['idp']['singleLogoutService']['url'],
                'expectedMethod' => 'POST',
            ],
        ];
    }

    /**
     * @param array $settings
     * @param string $returnTo
     * @param string $expectedUrl
     * @param string $expectedMethod
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('initiatePostLogoutProvider')]
    public function testInitiateLogoutPostBinding(array $settings, $returnTo, $expectedUrl, $expectedMethod)
    {
        $samlProvider = new SAMLAuthenticationProvider(
            $settings,
            $this->samlUserProvider,
            $this->samlUserChecker,
            $this->session,
            $this->userMapping
        );
        $token = new InitiateLogoutToken();
        $token->setAttribute('returnTo', $returnTo);

        $returnedToken = $samlProvider->authenticate($token);

        self::assertStringContainsString($expectedUrl, $returnedToken->getAttribute('url'));
        $this->assertEquals($expectedMethod, $returnedToken->getAttribute('method'));

        $parameters = $returnedToken->getAttribute('parameters');
        $this->assertArrayHasKey('SAMLRequest', $parameters);
        if ($returnTo) {
            $this->assertArrayHasKey('RelayState', $parameters);
            $this->assertEquals($returnTo, $parameters['RelayState']);
        } else {
            $this->assertArrayNotHasKey('RelayState', $parameters);
        }
    }

    /**
     * Provides set of data for check REDIRECT binding initiate logout logic.
     *
     * @return array
     */
    public static function initiateRedirectLogoutProvider()
    {
        $oneLoginSettings = IDMFixturesHelper::getOneLoginParameters();
        return [
            'OneLoginWithRelayState' => [
                'settings' => $oneLoginSettings,
                'returnTo' => 'http://test.com',
                'expectedUrl' => $oneLoginSettings['idp']['singleLogoutService']['url'].'?SAMLRequest=',
                'expectedMethod' => 'GET',
            ],
        ];
    }

    /**
     * @param array $settings
     * @param string $returnTo
     * @param string $expectedUrl
     * @param string $expectedMethod
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('initiateRedirectLogoutProvider')]
    public function testInitiateLogoutRedirectBinding(array $settings, $returnTo, $expectedUrl, $expectedMethod)
    {
        $samlProvider = new SAMLAuthenticationProvider(
            $settings,
            $this->samlUserProvider,
            $this->samlUserChecker,
            $this->session,
            $this->userMapping
        );
        $token = new InitiateLogoutToken();
        $token->setAttribute('returnTo', $returnTo);

        $returnedToken = $samlProvider->authenticate($token);

        self::assertStringContainsString($expectedUrl, $returnedToken->getAttribute('url'));
        $this->assertEquals($expectedMethod, $returnedToken->getAttribute('method'));
        self::assertStringContainsString('RelayState='.$returnTo, urldecode($returnedToken->getAttribute('url')));
    }

    /**
     * @return array
     */
    public static function logoutConsumeProvider()
    {
        $oktaLoginResponse = IDMFixturesHelper::getSAMLFixture('Okta/SignedAssertion/Response.xml');
        $oktaLogoutResponse = IDMFixturesHelper::getSAMLFixture('Okta/Logout/LogoutResponse.xml');
        $adfsLoginResponse = IDMFixturesHelper::getSAMLFixture('ADFS/SignedResponse/Response.xml');

        return [
            'validOktaLoginResponse' => [
                'settings' => IDMFixturesHelper::getOktaParameters(),
                'response' => base64_encode($oktaLoginResponse),
                'expectedException' => AuthenticationException::class,
            ],
            'validOktaLogoutResponse' => [
                'settings' => IDMFixturesHelper::getOktaParameters(),
                'response' => base64_encode($oktaLogoutResponse),
                'expectedException' => null,
            ],
            'invalidResponse' => [
                'settings' => IDMFixturesHelper::getOktaParameters(),
                'response' => base64_encode($adfsLoginResponse),
                'expectedException' => AuthenticationException::class,
            ],
        ];
    }

    /**
     * @param array $settings
     * @param $response
     * @param $expectedException
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('logoutConsumeProvider')]
    public function testLogoutConsume(array $settings, $response, $expectedException)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
        }
        $samlProvider = new SAMLAuthenticationProvider(
            $settings,
            $this->samlUserProvider,
            $this->samlUserChecker,
            $this->session,
            $this->userMapping
        );
        $logoutToken = new ConsumeLogoutToken($response);

        $returnedToken = $samlProvider->authenticate($logoutToken);

        $this->assertFalse($returnedToken->isAuthenticated());
    }

    /**
     * Provides valid settings for test.
     * @see testConsume
     * @return array
     */
    public static function consumeProvider()
    {
        return [
            'neededValidationRequestId' => [
                'settings' => array_replace_recursive(
                    IDMFixturesHelper::getOktaParameters(),
                    ['security' => ['validateRequestId' => true]]
                ),
                'requestId' => 'ONELOGIN_124d7f4dc1ee343111c5b134c4e9e93d3a2a2a07',
                'removeIdInvocation' => self::once(),
            ],
            'notNeededValidationRequestId' => [
                'settings' => array_replace_recursive(
                    IDMFixturesHelper::getOktaParameters(),
                    ['security' => ['validateRequestId' => false]]
                ),
                'requestId' => null,
                'removeIdInvocation' => self::never(),
            ],
        ];
    }
    
    /**
     * Checks consume logic.
     * @param array $settings
     * @param string|null $requestId
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $removeIdInvocation
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('consumeProvider')]
    public function testConsume(array $settings, $requestId, $removeIdInvocation)
    {
        $response = base64_encode(IDMFixturesHelper::getSAMLFixture('Okta/SignedAssertion/Response.xml'));
        $idpSessionIndex =  'ONELOGIN_124d7f4dc1ee343111c5b134c4e9e93d3a2a2a07';

        /** @var SAMLAuthenticationProvider|\PHPUnit_Framework_MockObject_MockObject $samlProvider */
        $samlProvider = $this->getMockBuilder(SAMLAuthenticationProvider::class)
            ->onlyMethods(['buildLoginResponse'])
            ->setConstructorArgs([
                $settings,
                $this->samlUserProvider,
                $this->samlUserChecker,
                $this->session,
                $this->userMapping
            ])
            ->getMock();
        $samlProvider->method('buildLoginResponse')->willReturn($this->response);

        $this->response
            ->method('isValid')
            ->with($requestId)
            ->willReturn(true);
        $this->response
            ->method('getSessionIndex')
            ->willReturn($idpSessionIndex);

        $this->session->expects($removeIdInvocation)
            ->method('remove')
            ->with(SAMLAuthenticationProvider::REQUEST_ID_KEY)
            ->willReturn($requestId);

        $token = $this->getMockBuilder(AcsToken::class)
            ->setConstructorArgs([$response])
            ->onlyMethods(['setUser', 'setAuthenticated', 'setAttribute'])
            ->setMockClassName('SAMLAcsToken')
            ->getMock();
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $this->userMapping->expects($this->once())
            ->method('mapIdentity')
            ->willReturn(
                [
                    'value' => 'identityValue',
                    'field' => 'identityField',
                ]
            );
        $this->samlUserProvider->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with('identityValue')
            ->willReturn($user);

        $result = $samlProvider->authenticate($token);

        $this->assertEquals($idpSessionIndex, $result->getAttribute('IdPSessionIndex'));
        $this->assertInstanceOf(User::class, $result->getUser());
        $this->assertTrue($result->isAuthenticated());
    }

    public function testConsumeInvalidResponse()
    {
        $this->expectException(\Sugarcrm\IdentityProvider\Authentication\Exception\SAMLResponseException::class);

        $response = base64_encode(IDMFixturesHelper::getSAMLFixture('Okta/SignedAssertion/Response.xml'));

        /** @var SAMLAuthenticationProvider|\PHPUnit_Framework_MockObject_MockObject $samlProvider */
        $samlProvider = $this->getMockBuilder(SAMLAuthenticationProvider::class)
            ->onlyMethods(['buildLoginResponse'])
            ->setConstructorArgs([
                array_replace_recursive(
                    IDMFixturesHelper::getOktaParameters(),
                    ['security' => ['validateRequestId' => true]]
                ),
                $this->samlUserProvider,
                $this->samlUserChecker,
                $this->session,
                $this->userMapping
            ])
            ->getMock();
        $samlProvider->method('buildLoginResponse')->willReturn($this->response);

        $this->response->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $this->response->expects($this->once())
            ->method('getError')
            ->willReturn('error');

        $token = $this->getMockBuilder(AcsToken::class)
            ->setConstructorArgs([$response])
            ->setMockClassName('SAMLAcsToken')
            ->getMock();

        $samlProvider->authenticate($token);
    }

    /**
     * Checks consume logic when response is invalid.
     */
    public function testConsumeWithInvalidResponse()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);

        $settings = IDMFixturesHelper::getOktaParameters();
        $response = base64_encode(IDMFixturesHelper::getSAMLFixture('ADFS/SignedResponse/Response.xml'));
        $this->session->expects($this->once())
            ->method('remove')
            ->with(SAMLAuthenticationProvider::REQUEST_ID_KEY)
            ->willReturn('someRequestId');
        $samlProvider = new SAMLAuthenticationProvider(
            $settings,
            $this->samlUserProvider,
            $this->samlUserChecker,
            $this->session,
            $this->userMapping
        );
        $token = $this->getMockBuilder(AcsToken::class)
                      ->setConstructorArgs([$response])
                      ->onlyMethods(['setUser', 'setAuthenticated', 'setAttribute'])
                      ->setMockClassName('SAMLAcsToken')
                      ->getMock();
        $samlProvider->authenticate($token);
    }

    /**
     *
     * @param $settings
     * @param $mapping
     * @param $response
     * @param $expectedAttributes
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('samlSettingsAndResponseWithUserAttributesProvider')]
    public function testConsumeWithUserAttributes($settings, $mapping, $response, $expectedAttributes)
    {
        $userProvider = $this->createMock(SAMLUserProvider::class);
        $userProvider->method('loadUserByIdentifier')->willReturn(new User('foo'));
        $samlProvider = new SAMLAuthenticationProvider(
            $settings,
            $userProvider,
            $this->samlUserChecker,
            $this->session,
            new SAMLUserMapping($mapping)
        );
        $token = new AcsToken($response);
        $userAttributes = $samlProvider->authenticate($token)->getUser()->getAttribute('attributes');
        ksort($userAttributes);
        ksort($expectedAttributes);
        $this->assertEquals($expectedAttributes, $userAttributes);
    }

    /**
     * @return array
     */
    public static function samlSettingsAndResponseWithUserAttributesProvider()
    {
        $oktaResponse = IDMFixturesHelper::getSAMLFixture('Okta/SignedAssertionWithUserAttributes/Response.xml');
        $oneLoginResponse = IDMFixturesHelper::getSAMLFixture('OneLogin/SignedResponseWithUserAttributes/Response.xml');
        $adfsLoginResponse = IDMFixturesHelper::getSAMLFixture('ADFS/SignedResponseWithUserAttributes/Response.xml');
        return [
            'Okta Identity Provider' => [
                IDMFixturesHelper::getOktaParameters(),
                'mapping' => [
                    'name_id' => 'attributes.email',
                    'attribute1' => 'attributes.title',
                    'attribute2' => 'attributes.department'
                ],
                base64_encode($oktaResponse),
                ['email' => 'sugarcrm.idm.developer@gmail.com', 'title' => 'Foo', 'department' => 'Bar'],
            ],
            'Onelogin Identity Provider' => [
                IDMFixturesHelper::getOneLoginParameters(),
                'mapping' => ['name_id' => 'attributes.email', 'attribute1' => 'attributes.department'],
                base64_encode($oneLoginResponse),
                ['email' => 'sugarcrm.idm.developer@gmail.com', 'department' => 'Development'],
            ],
            'ADFS Identity Provider' => [
                IDMFixturesHelper::getADFSParameters(),
                'mapping' => [
                    'name_id' => 'attributes.email',
                    'surname' => 'attributes.last_name',
                    'givenname' => 'attributes.first_name'
                ],
                base64_encode($adfsLoginResponse),
                ['email' => 'sugardeveloper@test.com', 'last_name' => 'Developer', 'first_name' => 'Sugar'],
            ],
        ];
    }

    public function testConsumeUsesPostAuth()
    {
        $settings = IDMFixturesHelper::getOktaParameters();
        $response = base64_encode(IDMFixturesHelper::getSAMLFixture('Okta/SignedAssertion/Response.xml'));
        $token = $this->getMockBuilder(AcsToken::class)
            ->setConstructorArgs([$response])
            ->onlyMethods(['setUser', 'setAuthenticated', 'setAttribute'])
            ->setMockClassName('SAMLAcsToken')
            ->getMock();
        $matcher = $this->exactly(5);
        $this->user->expects($matcher)
            ->method('setAttribute')->willReturnCallback(function () use ($matcher) {
                return match ($matcher->numberOfInvocations()) {
                    1 => ['provision', $this->anything()],
                    2 => ['identityField', $this->anything()],
                    3 => ['identityValue', $this->anything()],
                    4 => ['attributes', $this->anything()],
                    5 => ['XMLResponse', $this->anything()],
                };
            });
        $this->samlUserChecker->expects($this->once())
            ->method('checkPostAuth')
            ->with($this->user);
        $this->userMapping->method('mapIdentity')->willReturn([
            'field' => 'username',
            'value' => 'phpunit'
        ]);
        $samlProvider = new SAMLAuthenticationProvider(
            $settings,
            $this->samlUserProvider,
            $this->samlUserChecker,
            $this->session,
            $this->userMapping
        );
        $samlProvider->authenticate($token);
    }
    public function testConsumeReactsOnPostAuthException()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);
        $this->expectExceptionMessage('User was not matched to db-user');

        $settings = IDMFixturesHelper::getOktaParameters();
        $response = base64_encode(IDMFixturesHelper::getSAMLFixture('Okta/SignedAssertion/Response.xml'));
        $token = $this->getMockBuilder(AcsToken::class)
            ->setConstructorArgs([$response])
            ->setMockClassName('SAMLAcsToken')
            ->getMock();
        $this->samlUserChecker
            ->method('checkPostAuth')
            ->will($this->throwException(new AuthenticationException('User was not matched to db-user')));
        $this->userMapping->method('mapIdentity')->willReturn([
            'field' => 'username',
            'value' => 'phpunit'
        ]);
        $samlProvider = new SAMLAuthenticationProvider(
            $settings,
            $this->samlUserProvider,
            $this->samlUserChecker,
            $this->session,
            $this->userMapping
        );
        $samlProvider->authenticate($token);
    }

    /**
     * Checks idpLogout logic when request is not valid.
     */
    public function testIdpLogoutWithInvalidRequest()
    {
        $this->expectException(\Sugarcrm\IdentityProvider\Authentication\Exception\SAMLRequestException::class);

        $request = base64_encode(IDMFixturesHelper::getSAMLFixture('OneLogin/Logout/idpLogoutRequest.xml'));
        $settings = IDMFixturesHelper::getOktaParameters();
        $settings['strict'] = true;

        $samlProvider = new SAMLAuthenticationProvider(
            $settings,
            $this->samlUserProvider,
            $this->samlUserChecker,
            $this->session,
            $this->userMapping
        );
        $token = $this->getMockBuilder(IdpLogoutToken::class)
                      ->setConstructorArgs([$request])
                      ->onlyMethods(['setAuthenticated', 'setAttribute'])
                      ->getMock();
        $samlProvider->authenticate($token);
    }

    /**
     * Checks idpLogout logic for redirect binding.
     */
    public function testRedirectBindingIdpLogout()
    {
        $request = base64_encode(IDMFixturesHelper::getSAMLFixture('OneLogin/Logout/idpLogoutRequest.xml'));
        $settings = IDMFixturesHelper::getOneLoginParameters();
        $expectedResult = [
            'url' =>
                'https://sugarcrm-idmeloper-dev.onelogin.com/trust/saml2/http-redirect/slo/622315?SAMLResponse=',
            'method' => 'GET',
            'parameters' => [
                'nameId' => 'ddolbik@sugarcrm.com',
            ],
        ];
        $samlProvider = new SAMLAuthenticationProvider(
            $settings,
            $this->samlUserProvider,
            $this->samlUserChecker,
            $this->session,
            $this->userMapping
        );

        $token = $this->getMockBuilder(IdpLogoutToken::class)
                      ->setConstructorArgs([$request])
                      ->onlyMethods(['setAuthenticated', 'setAttribute'])
                      ->getMock();

        $result = $samlProvider->authenticate($token);

        $this->assertInstanceOf(ResultToken::class, $result);
        self::assertStringContainsString($expectedResult['url'], $result->getAttribute('url'));
        $this->assertEquals($expectedResult['method'], $result->getAttribute('method'));
        $this->assertEquals($expectedResult['parameters'], $result->getAttribute('parameters'));
        $this->assertFalse($result->isAuthenticated());
    }

    /**
     * Checks idpLogout logic for post binding.
     */
    public function testPostBindingIdpLogout()
    {
        $request = base64_encode(IDMFixturesHelper::getSAMLFixture('Okta/Logout/idpLogoutRequest.xml'));
        $settings = IDMFixturesHelper::getOktaParameters();
        $expectedResult = [
            'url' =>
                'https://dev-432366.oktapreview.com/app/sugarcrmdev432366_sugarcrmidmdev_1/exk9f6zk3cchXSMkP0h7/slo/saml',
            'method' => 'POST',
            'parameters' => [
                'SAMLResponse' => '',
            ],
        ];
        $samlProvider = new SAMLAuthenticationProvider(
            $settings,
            $this->samlUserProvider,
            $this->samlUserChecker,
            $this->session,
            $this->userMapping
        );

        $token = $this->getMockBuilder(IdpLogoutToken::class)
                      ->setConstructorArgs([$request])
                      ->onlyMethods(['setAuthenticated', 'setAttribute'])
                      ->getMock();

        $result = $samlProvider->authenticate($token);

        $this->assertInstanceOf(ResultToken::class, $result);
        self::assertStringContainsString($expectedResult['url'], $result->getAttribute('url'));
        $this->assertEquals($expectedResult['method'], $result->getAttribute('method'));
        $this->assertArrayHasKey('SAMLResponse', $result->getAttribute('parameters'));
        $this->assertFalse($result->isAuthenticated());
    }

    /**
     * Test to check that SAML provider supports only valid tokens.
     *
     * @param array $settings
     * @param mixed $setIdInvocation dummy argument to reuse data provider and avoid deprecation notice
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('samlSettingsProvider')]
    public function testSupports(array $settings, $setIdInvocation)
    {
        $samlProvider = new SAMLAuthenticationProvider(
            $settings,
            $this->samlUserProvider,
            $this->samlUserChecker,
            $this->session,
            $this->userMapping
        );
        $this->assertTrue($samlProvider->supports(new InitiateToken()));
        $this->assertTrue($samlProvider->supports(new AcsToken('')));
        $this->assertTrue($samlProvider->supports(new InitiateLogoutToken()));
        $this->assertTrue($samlProvider->supports(new ConsumeLogoutToken('')));
        $this->assertFalse($samlProvider->supports(new UsernamePasswordToken('username', 'password', 'saml')));
    }

    /**
     * Exception must be thrown when no authentication service for some IdP configured.
     */
    public function testExceptionWhenNoAuthenticationServiceConfigured()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid authentication services configuration');

        $samlProvider = new SAMLAuthenticationProvider(
            [],
            $this->samlUserProvider,
            $this->samlUserChecker,
            $this->session,
            $this->userMapping
        );
        $token = new InitiateToken();
        $token->setAttribute('returnTo', 'http://local.host');
        $samlProvider->authenticate($token);
    }
}
