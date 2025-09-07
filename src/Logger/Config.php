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

namespace Sugarcrm\Sugarcrm\Logger;

use Monolog\Logger;
use Monolog\Level;

/**
 * Logger configuration
 */
class Config
{
    /**
     * Mapping of SugarCRM log levels to the Monolog's ones
     *
     * @var array
     */
    protected static $levels = [
        'debug' => Level::Debug->value,
        'info' => Level::Info->value,
        'warn' => Level::Warning->value,
        'deprecated' => Level::Notice->value,
        'error' => Level::Error->value,
        'fatal' => Level::Alert->value,
        'security' => Level::Critical->value,
    ];

    /**
     * @var \SugarConfig
     */
    protected $config;

    /**
     * Constructor
     *
     * @param \SugarConfig $config Application configuration
     */
    public function __construct(\SugarConfig $config)
    {
        $params = [
            'log_dir' => 'logger.handlers.file.dir',
            'logger.file' => 'logger.handlers.file',
        ];

        // copy file logger settings under the "handlers" section for unification
        foreach ($params as $src => $dst) {
            $config->get($dst, $config->get($src));
        }

        $this->config = $config;
    }

    /**
     * Returns the configuration of the given channel. It will contain all needed parameters for handlers.
     *
     * @param string $channel Channel name
     * @return array
     */
    public function getChannelConfig($channel)
    {
        $config = $this->config->get('logger.channels.' . $channel, []);

        if (isset($config['handlers'])) {
            $config['handlers'] = $this->normalizeConfig($config['handlers']);
        } else {
            // set empty handler definition which will be later populated with the default values
            $config['handlers'] = [[]];
        }

        if (isset($config['processors'])) {
            $config['processors'] = $this->normalizeConfig($config['processors']);
        } else {
            $config['processors'] = [];
        }

        $config = $this->expandChannelConfig($channel, $config);

        return $config;
    }

    /**
     * Normalizes the value retrieved from the configuration file
     *
     * @param mixed $components
     * @return array
     */
    protected function normalizeConfig($components)
    {
        $result = [];

        if (is_string($components)) {
            $components = [$components];
        }

        $isAssociativeArray = array_keys($components) !== range(0, safeCount($components) - 1);
        foreach ($components as $key => $value) {
            if (is_string($value)) {
                $result[] = [
                    'type' => $value,
                ];
            } else {
                $normalized = [];
                if (isset($value['type'])) {
                    $normalized['type'] = $value['type'];
                    unset($value['type']);
                } elseif ($isAssociativeArray) {
                    $normalized['type'] = $key;
                }

                if (isset($value['level']) && $this->isValidLoggerValue($value['level'])) {
                    $normalized['level'] = Logger::toMonologLevel($value['level'])->value;
                    unset($value['level']);
                }

                if ($value) {
                    $normalized['params'] = $value;
                }

                $result[] = $normalized;
            }
        }

        return $result;
    }

    /**
     * Populates configuration of the channel handlers with the values from the channel configuration,
     * default handler configuration and default system configuration.
     *
     * @param string $channel Channel name
     * @param array $config Channel configuration
     *
     * @return array
     */
    protected function expandChannelConfig($channel, array $config)
    {
        foreach ($config['handlers'] as &$handler) {
            if (!isset($handler['type'])) {
                $handler['type'] = $this->config->get('logger.handler', 'file');
            }
            $type = $handler['type'];
            if (!isset($handler['level'])) {
                $handler['level'] = $this->getHandlerLevel($channel, $type);
            }

            $params = $this->config->get('logger.handlers.' . $type, []);
            if (isset($handler['params'])) {
                $params = array_merge($params, $handler['params']);
            }
            $handler['params'] = $params;
            unset($handler);
        }

        foreach ($config['processors'] as &$processor) {
            $processor['params'] = [];
            unset($processor);
        }

        return [
            'handlers' => $config['handlers'],
            'processors' => $config['processors'],
        ];
    }

    /**
     * Returns handler level for the given channel in case if it's not explicitly defined
     *
     * @param string $channel Channel name
     * @param string $handler Handler type
     *
     * @return int
     */
    protected function getHandlerLevel($channel, $handler)
    {
        $levels = array_filter([
            $this->config->get('logger.channels.' . $channel . '.level'),
            $this->config->get('logger.handlers.' . $handler . '.level'),
        ]);
        $level = array_shift($levels);
        if ($level && $this->isValidLoggerValue($level)) {
            return Logger::toMonologLevel($level)->value;
        }

        $level = $this->config->get('logger.level');
        if ($level === 'off') {
            return 0;
        }

        if ($level && isset(self::$levels[$level])) {
            $level = self::$levels[$level];
        } else {
            $level = LeveL::Alert->value;
        }

        return $level;
    }

    /**
     * Validates if the given logger level is a valid value.
     *
     * This function checks if the provided logger level, either as an integer or a string,
     * matches any of the allowed levels defined in `Level::NAMES` or `Level::VALUES`.
     *
     * @param int|string $level The logger level to validate. It can be an integer or a string.
     * @return bool Returns true if the level is valid, false otherwise.
     */
    public function isValidLoggerValue(int|string $level): bool
    {
        $levelStr = strtoupper((string)$level);
        $allowedLevels = array_merge(array_map('strtoupper', Level::NAMES), Level::VALUES);

        return in_array($levelStr, $allowedLevels);
    }
}
