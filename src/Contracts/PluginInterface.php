<?php

namespace Obrainwave\Paygate\Contracts;

interface PluginInterface
{
    /**
     * Get plugin name
     */
    public function getName(): string;

    /**
     * Get plugin version
     */
    public function getVersion(): string;

    /**
     * Get plugin description
     */
    public function getDescription(): string;

    /**
     * Initialize plugin
     */
    public function initialize(): void;

    /**
     * Register plugin hooks
     */
    public function registerHooks(): array;

    /**
     * Handle plugin hook
     */
    public function handleHook(string $hook, array $data = []): mixed;

    /**
     * Check if plugin is enabled
     */
    public function isEnabled(): bool;

    /**
     * Get plugin configuration
     */
    public function getConfig(): array;

    /**
     * Set plugin configuration
     */
    public function setConfig(array $config): void;
}
