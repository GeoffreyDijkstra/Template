<?php

namespace Devorto\Template;

use InvalidArgumentException;
use RuntimeException;

/**
 * Convert any file to a template using a specific structure.
 *
 * @package Devorto\Template
 */
class Template implements TemplateInterface
{
    /**
     * Command start.
     */
    const COMMAND_START = '<!--@@';

    /**
     * Command end.
     */
    const COMMAND_END = '@@-->';

    /**
     * Variable start.
     */
    const VARIABLE_START = '@-{';

    /**
     * Variable end.
     */
    const VARIABLE_END = '}-@';

    /**
     * Begin ignore.
     */
    const BEGIN_IGNORE = 'BEGIN_IGNORE';

    /**
     * End ignore.
     */
    const END_IGNORE = 'END_IGNORE';

    /**
     * Begin template.
     */
    const BEGIN_TEMPLATE = 'BEGIN_TEMPLATE';

    /**
     * End template.
     */
    const END_TEMPLATE = 'END_TEMPLATE';

    /**
     * Separator keyword.
     */
    const SEPARATOR = 'AS';

    /**
     * @var string
     */
    protected $cleanTemplate = '';

    /**
     * @var string[]
     */
    protected $vars = [];

    /**
     * @var TemplateInterface[]
     */
    protected $templates = [];

    /**
     * @var string|null
     */
    protected $name;

    /**
     * Loads the current template from a file.
     *
     * @param string $file
     *
     * @return TemplateInterface
     */
    public function loadFromFile(string $file): TemplateInterface
    {
        // To prevent file_exists from dieing when string accidentally contains a space or newline character.
        $file = trim($file);

        if (empty($file) || !file_exists($file)) {
            throw new InvalidArgumentException(sprintf('File "%s" not found.', $file));
        }

        return $this->loadFromString(file_get_contents($file));
    }

    /**
     * Loads the current template string.
     *
     * @param string $contents
     *
     * @return TemplateInterface
     */
    public function loadFromString(string $contents): TemplateInterface
    {
        if (empty(trim($contents))) {
            throw new InvalidArgumentException('Cannot parse empty template content.');
        }

        $this->readIgnoreBlock($contents);
        $this->readTemplateVars();

        return $this;
    }

    /**
     * Read the ignore block.
     *
     * @param string $templateString
     */
    protected function readIgnoreBlock(string $templateString)
    {
        $templateName = '';
        if (!empty($this->name)) {
            $templateName = sprintf(
                '\s+%s',
                preg_quote($this->name)
            );
        }

        $expression = sprintf(
            '/%1$s\s+%2$s%3$s.*?%4$s(.*?)%1$s\s+%5$s%3$s\s+%4$s/s',
            preg_quote(static::COMMAND_START),
            preg_quote(static::BEGIN_IGNORE),
            $templateName,
            preg_quote(static::COMMAND_END),
            preg_quote(static::END_IGNORE)
        );

        preg_match_all($expression, $templateString, $matches);
        for ($i = 0; $i < count($matches[0]); $i++) {
            $this->readTemplateBlock($matches[1][$i]);

            $expression = sprintf(
                '/%1$s\s+%2$s%3$s\s+(%4$s)?(.*?)%5$s/s',
                preg_quote(static::COMMAND_START),
                preg_quote(static::BEGIN_IGNORE),
                $templateName,
                preg_quote(static::SEPARATOR),
                preg_quote(static::COMMAND_END)
            );

            preg_match($expression, $matches[0][$i], $variableSearch);

            $replace = '';
            if (trim($variableSearch[2]) !== '') {
                $replace = static::VARIABLE_START . trim($variableSearch[2]) . static::VARIABLE_END;
            }
            $templateString = str_replace($matches[0][$i], $replace, $templateString);
        }

        $this->cleanTemplate .= $templateString;
    }

    /**
     * Read the template block.
     *
     * @param string $string
     */
    protected function readTemplateBlock(string $string)
    {
        $expression = sprintf(
            '/%1$s\s+%2$s.*?%3$s.*?%1$s\s+%4$s.*?%3$s/s',
            preg_quote(static::COMMAND_START),
            preg_quote(static::BEGIN_IGNORE),
            preg_quote(static::COMMAND_END),
            preg_quote(static::END_IGNORE)
        );

        $tempString = preg_replace($expression, '', $string);
        $expression = sprintf(
            '/%s\s+%s\s+(\S+)\s+%s/s',
            preg_quote(static::COMMAND_START),
            preg_quote(static::BEGIN_TEMPLATE),
            preg_quote(static::COMMAND_END)
        );

        preg_match_all($expression, $tempString, $matches);

        foreach ($matches[1] as $name) {
            $expression = sprintf(
                '/%1$s\s+%2$s\s+%3$s\s+%4$s(.*?)%1$s\s+%5$s\s+%3$s\s+%4$s/s',
                preg_quote(static::COMMAND_START),
                preg_quote(static::BEGIN_TEMPLATE),
                $name,
                preg_quote(static::COMMAND_END),
                preg_quote(static::END_TEMPLATE)
            );

            preg_match($expression, $string, $template);

            $self = new static();
            $self->name = $name;
            $this->templates[$name] = $self->loadFromString($template[1]);
        }
    }

    /**
     * Read the template variables.
     */
    protected function readTemplateVars()
    {
        $this->vars = [];
        $expression = sprintf(
            '/%s(.*?)%s/',
            preg_quote(static::VARIABLE_START),
            preg_quote(static::VARIABLE_END)
        );

        preg_match_all($expression, $this->cleanTemplate, $matches);
        foreach ($matches[1] as $var) {
            $this->vars[$var] = null;
        }
    }

    /**
     * Checks if the template has a certain variable.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasVariable(string $name): bool
    {
        return array_key_exists($name, $this->vars);
    }

    /**
     * Gets the value of a template variable.
     *
     * @param string $name
     *
     * @return string
     */
    public function getVariable(string $name): string
    {
        if (!array_key_exists($name, $this->vars)) {
            throw new InvalidArgumentException(sprintf('Template does not contain variable "%s".', $name));
        }

        return $this->vars[$name];
    }

    /**
     * Sets the value of a template variable.
     *
     * @param string $name
     * @param string $value
     * @param bool $append
     *
     * @return TemplateInterface
     */
    public function setVariable(string $name, string $value, bool $append = false): TemplateInterface
    {
        if (!array_key_exists($name, $this->vars)) {
            throw new InvalidArgumentException(sprintf('Template does not contain variable "%s".', $name));
        }

        if ($append) {
            $this->vars[$name] .= $value;
        } else {
            $this->vars[$name] = $value;
        }

        return $this;
    }

    /**
     * Gets a sub template from the current template.
     *
     * @param string $name
     *
     * @return TemplateInterface
     */
    public function getSubTemplate(string $name): TemplateInterface
    {
        if (!array_key_exists($name, $this->templates)) {
            throw new InvalidArgumentException(sprintf('Sub template "%s" does not exists.', $name));
        }

        return $this->templates[$name];
    }

    /**
     * Converts $this back to a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Converts $this back to a string.
     *
     * @return string
     */
    public function render(): string
    {
        $string = $this->cleanTemplate;

        foreach ($this->vars as $name => $value) {
            if ($value === null) {
                throw new RuntimeException(sprintf('No value set for template variable "%s".', $name));
            }

            $string = str_replace(
                static::VARIABLE_START . $name . static::VARIABLE_END,
                $value,
                $string
            );
        }

        return $string;
    }
}
