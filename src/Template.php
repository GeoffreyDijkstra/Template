<?php

namespace gdwebs\template;

/**
 * Convert any file to a template using a specific structure.
 *
 * @package gdwebs\template
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
    private $cleanTemplate = '';

    /**
     * @var string[]
     */
    private $vars = [];

    /**
     * @var TemplateInterface[]
     */
    private $templates = [];

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string[]
     */
    private $values = [];

    /**
     * Create a new template object.
     *
     * @param string $fileOrString
     * @param string $name
     *
     * @throws TemplateException
     */
    public function __construct($fileOrString, $name = '')
    {
        if (!is_string($fileOrString)) {
            throw new TemplateException(
                sprintf(
                    'Expecting type string but got type "%s"!',
                    gettype($fileOrString)
                )
            );
        }
        if (!is_string($name)) {
            throw new TemplateException(
                sprintf(
                    'Expecting type string but got type "%s"!',
                    gettype($name)
                )
            );
        }

        $this->name = $name;
        $this->load($fileOrString);
    }

    /**
     * Loads the current template string or reads it from a file.
     *
     * @param string $fileOrString
     *
     * @return TemplateInterface
     * @throws TemplateException
     */
    public function load($fileOrString)
    {
        if (!is_string($fileOrString)) {
            throw new TemplateException(
                sprintf(
                    'Expecting type string but got type "%s"!',
                    gettype($fileOrString)
                )
            );
        }

        if (file_exists($fileOrString)) {
            $fileOrString = file_get_contents($fileOrString);
        }

        $this->readIgnoreBlock($fileOrString);
        $this->readTemplateVars();

        return $this;
    }

    /**
     * Read the ignore block.
     *
     * @param string $templateString
     *
     * @throws TemplateException
     */
    private function readIgnoreBlock($templateString)
    {
        if (!is_string($templateString)) {
            throw new TemplateException(
                sprintf(
                    'Expecting type string but got type "%s"!',
                    gettype($templateString)
                )
            );
        }

        $templateName = '';
        if ($this->name !== '') {
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
     *
     * @throws TemplateException
     */
    private function readTemplateBlock($string)
    {
        if (!is_string($string)) {
            throw new TemplateException(
                sprintf(
                    'Expecting type string but got type "%s"!',
                    gettype($string)
                )
            );
        }

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
            $this->templates[$name] = new static($template[1], $name);
        }
    }

    /**
     * Read the template variables.
     */
    private function readTemplateVars()
    {
        $this->vars = [];
        $expression = sprintf(
            '/%s(.*?)%s/',
            preg_quote(static::VARIABLE_START),
            preg_quote(static::VARIABLE_END)
        );

        preg_match_all($expression, $this->cleanTemplate, $matches);
        foreach ($matches[1] as $var) {
            $this->vars[] = $var;
        }
    }

    /**
     * Sets the value of a template variable.
     *
     * @param string $name
     * @param string $value
     *
     * @throws TemplateException
     * @return TemplateInterface
     */
    public function setVariable($name, $value)
    {
        if (!is_string($name)) {
            throw new TemplateException(
                sprintf(
                    'Expecting type string but got type "%s"!',
                    gettype($name)
                )
            );
        }
        if (!is_string($value)) {
            throw new TemplateException(
                sprintf(
                    'Expecting type string but got type "%s"!',
                    gettype($value)
                )
            );
        }

        if (!in_array($name, $this->vars)) {
            throw new TemplateException(
                sprintf(
                    'Unknown variable "%s"!',
                    $name
                )
            );
        }

        $this->values[$name] = (string)$value;

        return $this;
    }

    /**
     * Gets the value of a template variable.
     *
     * @param string $name
     *
     * @return string
     * @throws TemplateException
     */
    public function getVariable($name)
    {
        if (!is_string($name)) {
            throw new TemplateException(
                sprintf(
                    'Expecting type string but got type "%s"!',
                    gettype($name)
                )
            );
        }

        if (!in_array($name, $this->vars)) {
            throw new TemplateException(
                sprintf(
                    'Unknown variable "%s"!',
                    $name
                )
            );
        }

        return $this->values[$name];
    }

    /**
     * Gets a sub template from the current template.
     *
     * @param string $name
     *
     * @return TemplateInterface
     * @throws TemplateException
     */
    public function getSubTemplate($name)
    {
        if (!is_string($name)) {
            throw new TemplateException(
                sprintf(
                    'Expecting type string but got type "%s"!',
                    gettype($name)
                )
            );
        }

        if (!array_key_exists($name, $this->templates)) {
            throw new TemplateException(
                sprintf(
                    'Sub template "%s" does not exists!',
                    $name
                )
            );
        }

        return $this->templates[$name];
    }

    /**
     * Converts $this back to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Converts $this back to a string.
     *
     * @return string
     */
    public function render()
    {
        $string = $this->cleanTemplate;

        foreach ($this->vars as $name) {
            if (array_key_exists($name, $this->values)) {
                $string = str_replace(
                    static::VARIABLE_START . $name . static::VARIABLE_END,
                    $this->values[$name],
                    $string
                );
            }
        }

        return $string;
    }
}
