<?php

namespace Gigablah\Silex\OAuth;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use OAuth\ServiceFactory;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\TokenStorageInterface;

/**
 * Registry for instantiating and memoizing OAuth service providers.
 *
 * @author Chris Heng <bigblah@gmail.com>
 */
class OAuthServiceRegistry
{
    protected $services;
    protected $config;
    protected $options;
    protected $oauthServiceFactory;
    protected $oauthStorage;
    protected $urlGenerator;

    public function __construct(ServiceFactory $oauthServiceFactory, TokenStorageInterface $oauthStorage, UrlGeneratorInterface $urlGenerator, array $config = array(), array $options = array())
    {
        $this->config = $config;
        $this->options = $options;
        $this->oauthServiceFactory = $oauthServiceFactory;
        $this->oauthStorage = $oauthStorage;
        $this->urlGenerator = $urlGenerator;
    }

    public function getService($service)
    {
        if (isset($this->services[$service])) {
            return $this->services[$service];
        }

        return $this->services[$service] = $this->createService($service);
    }

    protected function createService($service)
    {
        if (!isset($this->config[$service])) {
            throw new \InvalidArgumentException(sprintf('OAuth configuration not defined for the "%s" service.', $service));
        }

        $credentials = new Credentials(
            $this->config[$service]['key'],
            $this->config[$service]['secret'],
            $this->urlGenerator->generate($this->options['callback_route'], array(
                'service' => $service
            ), true)
        );

        $scope = isset($this->config[$service]['scope']) ? $this->config[$service]['scope'] : array();

        return $this->oauthServiceFactory->createService(
            $service,
            $credentials,
            $this->oauthStorage,
            $scope
        );
    }
}