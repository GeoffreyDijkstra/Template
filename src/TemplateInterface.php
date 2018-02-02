<?php

namespace devorto\template;

/**
 * Convert any file to a template using a specific structure.
 *
 * @package plib\converters
 */
interface TemplateInterface
{
    /**
     * Create a new template object.
     *
     * @param string $fileOrString
     * @param string $name
     */
    public function __construct(string $fileOrString, string $name = '');

    /**
     * Loads the current template string or reads it from a file.
     *
     * @param string $fileOrString
     *
     * @return TemplateInterface
     */
    public function load(string $fileOrString): TemplateInterface;

    /**
     * Sets the value of a template variable.
     *
     * @param string $name
     * @param string $value
     *
     * @return TemplateInterface
     */
    public function setVariable(string $name, string $value): TemplateInterface;

    /**
     * Gets the value of a template variable.
     *
     * @param string $name
     *
     * @return string
     */
    public function getVariable(string $name): string;

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
