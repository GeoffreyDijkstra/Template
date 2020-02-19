<?php

namespace Devorto\Template;

/**
 * Convert any file to a template using a specific structure.
 *
 * @package plib\converters
 */
interface TemplateInterface
{
    /**
     * Loads the current template from a file.
     *
     * @param string $file
     *
     * @return TemplateInterface
     */
    public function loadFromFile(string $file): TemplateInterface;

    /**
     * Loads the current template string.
     *
     * @param string $contents
     *
     * @return TemplateInterface
     */
    public function loadFromString(string $contents): TemplateInterface;

    /**
     * Checks if the template has a certain variable.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasVariable(string $name): bool;

    /**
     * Gets the value of a template variable.
     *
     * @param string $name
     *
     * @return string
     */
    public function getVariable(string $name): string;

    /**
     * Sets the value of a template variable.
     *
     * @param string $name
     * @param string|null $value
     * @param bool $append
     *
     * @return TemplateInterface
     */
    public function setVariable(string $name, ?string $value, bool $append = false): TemplateInterface;

    /**
     * Gets a sub template from the current template.
     *
     * @param string $name
     *
     * @return TemplateInterface
     */
    public function getSubTemplate(string $name): TemplateInterface;

    /**
     * Converts $this back to a string.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Converts $this back to a string.
     *
     * @return string
     */
    public function render(): string;
}
