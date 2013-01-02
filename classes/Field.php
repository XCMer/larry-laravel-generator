<?php
namespace Larry;

/**
 * Represent a field of a model
 * @author Raahul Seshadri <raahul@musictypes.com>
 */
class Field
{
    /**
     * Name of the field, lowercase, singular
     * @var string
     */
    private $name = null;

    /**
     * Type of the field as described in Laravel Schema Builder class
     * @var string
     */
    private $type = null;

    /**
     * Additional parameters to the field
     * @var array
     */
    private $params = array();

    /**
     * Is the field nullable
     * @var boolean
     */
    private $nullable = false;

    /**
     * Is the field primary
     * @var boolean
     */
    private $primary = false;

    /**
     * Is the field unique indexed
     * @var boolean
     */
    private $unique = false;

    /**
     * Is the field basic indexed
     * @var boolean
     */
    private $indexed = false;

    /**
     * Is the field fulltext indexed
     * @var boolean
     */
    private $fulltext = false;

    /**
     * Is the field unsigned
     * @var boolean
     */
    private $unsigned = false;

    /**
     * Default value for the field
     * @var null|string
     */
    private $default = null;

    /**
     * Validation rules
     * @var array
     */
    private $validationRules = array();


    /**
     * Initialize the name, type, and optional parameters
     * @param string $name
     * @param string $type
     * @param array $params
     */
    public function __construct($name, $type, $params = array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->params = $params;
    }

    /**
     * Set the field nullable
     * @return Field
     */
    public function setNullable()
    {
        $this->nullable = true;
        return $this;
    }

    /**
     * Set the field primary
     * @return Field
     */
    public function setPrimary()
    {
        $this->primary = true;
        return $this;
    }

    /**
     * Set the field unique
     * @return Field
     */
    public function setUnique()
    {
        $this->unique = true;
        return $this;
    }

    /**
     * Set the field basic indexed
     * @return Field
     */
    public function setIndexed()
    {
        $this->indexed = true;
        return $this;
    }

    /**
     * Set the field fulltext indexed
     * @return Field
     */
    public function setFulltext()
    {
        $this->fulltext = true;
    }

    /**
     * Set the field unsigned
     * @return Field
     */
    public function setUnsigned()
    {
        $this->unsigned = true;
    }

    /**
     * Set a default value for the field
     * @param string $value
     * @return Field
     */
    public function setDefault($value)
    {
        $this->default = $value;
        return $this;
    }

    /**
     * Get the field name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the field type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get field parameters
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get field nullable
     * @return boolean
     */
    public function getNullable()
    {
        return $this->nullable;
    }

    /**
     * Get field primary
     * @return boolean
     */
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     * Get field unique
     * @return boolean
     */
    public function getUnique()
    {
        return $this->unique;
    }

    /**
     * Get field basic indexed
     * @return boolean
     */
    public function getIndexed()
    {
        return $this->indexed;
    }

    /**
     * Get field fulltext
     * @return boolean
     */
    public function getFulltext()
    {
        return $this->fulltext;
    }

    /**
     * Get field unsigned
     * @return boolean
     */
    public function getUnsigned()
    {
        return $this->unsigned;
    }

    /**
     * Get field default
     * @return null|string
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Set validation rules
     * @param string $rules
     * @return Field
     */
    public function setValidationRules($rules)
    {
        $ruleArray = array();
        $ruleArray = explode('|', $rules);
        $ruleArray = array_map('trim', $ruleArray);

        $this->validationRules = $ruleArray;

        return $this;
    }

    /**
     * Return validation rule array
     * @return array
     */
    public function getValidationRules()
    {
        return $this->validationRules;
    }
}