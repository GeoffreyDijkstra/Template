<?php

namespace gdwebs\template;

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
    public function __construct($fileOrString, $name = '');

    /**
     * Loads the current template string or reads it from a file.
     *
     * @param string $fileOrString
     *
     * @return TemplateInterface
     */
    public function load($fileOrString);

    /**
     * Sets the value of a template variable.
     *
     * @param string $name
     * @param string $value
     *
     * @return TemplateInterface
     */
    public function setVariable($name, $value);

    /**
     * Gets the value of a template variable.
     *
     * @param string $name
     *
     * @return string
     */
    public function getVariable($name);

    /**
     * Gets a sub template from the current template.
     *
     * @param string $name
     *
     * @return TemplateInterface
     */
    public function getSubTemplate($name);

    /**
     * Converts $this back to a string.
     *
     * @return string
     */
    public function __toString();

    /**
     * Converts $this back to a string.
     *
     * @return string
     */
    public function render();
}
