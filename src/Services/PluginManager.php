<?php

namespace Obrainwave\Paygate\Services;

use Obrainwave\Paygate\Contracts\PluginInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PluginManager
{
    protected array $plugins = [];
    protected array $hooks = [];
    protected array $config = [];

    public function __construct()
    {
        $this->loadPlugins();
    }

    /**
     * Load all registered plugins
     */
    protected function loadPlugins(): void
    {
        $pluginConfigs = config('paygate.plugins', []);
        
        foreach ($pluginConfigs as $pluginClass => $config) {
            if (class_exists($pluginClass) && is_subclass_of($pluginClass, PluginInterface::class)) {
                try {
                    $plugin = new $pluginClass();
                    $plugin->setConfig($config);
                    
                    if ($plugin->isEnabled()) {
                        $plugin->initialize();
                        $this->plugins[$plugin->getName()] = $plugin;
                        $this->registerPluginHooks($plugin);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to load plugin', [
                        'plugin' => $pluginClass,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Register plugin hooks
     */
    protected function registerPluginHooks(PluginInterface $plugin): void
    {
        $hooks = $plugin->registerHooks();
        
        foreach ($hooks as $hook) {
            if (!isset($this->hooks[$hook])) {
                $this->hooks[$hook] = [];
            }
            $this->hooks[$hook][] = $plugin;
        }
    }

    /**
     * Execute hook
     */
    public function executeHook(string $hook, array $data = []): array
    {
        $results = [];
        
        if (!isset($this->hooks[$hook])) {
            return $results;
        }

        foreach ($this->hooks[$hook] as $plugin) {
            try {
                $result = $plugin->handleHook($hook, $data);
                if ($result !== null) {
                    $results[$plugin->getName()] = $result;
                }
            } catch (\Exception $e) {
                Log::error('Plugin hook execution failed', [
                    'plugin' => $plugin->getName(),
                    'hook' => $hook,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Register a plugin
     */
    public function registerPlugin(PluginInterface $plugin): void
    {
        $this->plugins[$plugin->getName()] = $plugin;
        $this->registerPluginHooks($plugin);
        
        Log::info('Plugin registered', [
            'plugin' => $plugin->getName(),
            'version' => $plugin->getVersion()
        ]);
    }

    /**
     * Unregister a plugin
     */
    public function unregisterPlugin(string $pluginName): void
    {
        if (isset($this->plugins[$pluginName])) {
            unset($this->plugins[$pluginName]);
            
            // Remove plugin from all hooks
            foreach ($this->hooks as $hook => $plugins) {
                $this->hooks[$hook] = array_filter($plugins, function($plugin) use ($pluginName) {
                    return $plugin->getName() !== $pluginName;
                });
            }
            
            Log::info('Plugin unregistered', ['plugin' => $pluginName]);
        }
    }

    /**
     * Get all plugins
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Get plugin by name
     */
    public function getPlugin(string $name): ?PluginInterface
    {
        return $this->plugins[$name] ?? null;
    }

    /**
     * Get available hooks
     */
    public function getHooks(): array
    {
        return array_keys($this->hooks);
    }

    /**
     * Get plugins for hook
     */
    public function getPluginsForHook(string $hook): array
    {
        return $this->hooks[$hook] ?? [];
    }

    /**
     * Check if hook has plugins
     */
    public function hasHook(string $hook): bool
    {
        return isset($this->hooks[$hook]) && !empty($this->hooks[$hook]);
    }

    /**
     * Get plugin configuration
     */
    public function getPluginConfig(string $pluginName): array
    {
        $plugin = $this->getPlugin($pluginName);
        return $plugin ? $plugin->getConfig() : [];
    }

    /**
     * Set plugin configuration
     */
    public function setPluginConfig(string $pluginName, array $config): void
    {
        $plugin = $this->getPlugin($pluginName);
        if ($plugin) {
            $plugin->setConfig($config);
        }
    }

    /**
     * Enable plugin
     */
    public function enablePlugin(string $pluginName): bool
    {
        $plugin = $this->getPlugin($pluginName);
        if ($plugin) {
            $config = $plugin->getConfig();
            $config['enabled'] = true;
            $plugin->setConfig($config);
            
            Log::info('Plugin enabled', ['plugin' => $pluginName]);
            return true;
        }
        
        return false;
    }

    /**
     * Disable plugin
     */
    public function disablePlugin(string $pluginName): bool
    {
        $plugin = $this->getPlugin($pluginName);
        if ($plugin) {
            $config = $plugin->getConfig();
            $config['enabled'] = false;
            $plugin->setConfig($config);
            
            Log::info('Plugin disabled', ['plugin' => $pluginName]);
            return true;
        }
        
        return false;
    }

    /**
     * Get plugin statistics
     */
    public function getPluginStatistics(): array
    {
        $total = count($this->plugins);
        $enabled = 0;
        $disabled = 0;
        
        foreach ($this->plugins as $plugin) {
            if ($plugin->isEnabled()) {
                $enabled++;
            } else {
                $disabled++;
            }
        }
        
        return [
            'total' => $total,
            'enabled' => $enabled,
            'disabled' => $disabled,
            'hooks' => count($this->getHooks())
        ];
    }
}
