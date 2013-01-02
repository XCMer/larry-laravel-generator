<?php
namespace Larry;

/**
 * Generate the content for the model file
 * @author Raahul Seshadri <raahul@musictypes.com>
 */
class ModelGenerator
{
    /**
     * Filename of the model
     * @var string
     */
    private $filename;

    /**
     * Model associated with this model file
     * @var Model
     */
    private $model;

    /**
     * Initialize the model and other variables
     * @param Model $model
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->filename = strtolower($model->getName()) . '.php';
    }

    /**
     * Return the content of the model file as it should be written
     * @return string
     */
    public function getOutput()
    {
        // Get the template file
        $template = file_get_contents(\Bundle::path('larry') . 'templates/Model.template');

        // Set model name
        $template = str_replace(
            '{{ModelName}}',
            $this->model->getName(),
            $template
        );

        // Set the model rules
        foreach ($this->model->getFields() as $field)
        {
            /** @var Field $field  */
            if ($field->getValidationRules())
            {
                $rules = implode('|', $field->getValidationRules());
                $template = str_replace(
                    '{{Rules}}',

                    "'" . $field->getName() . "'"
                        . ' => '
                        . "'" . $rules . "',\n"
                        . $this->whitespace(2) . '{{Rules}}',

                    $template
                );
            }
        }

        // Remove the {{Rules}} text
        $template = str_replace('{{Rules}}', '', $template);

        // Insert hasOne relations if any
        if ($this->model->getHasOne())
        {
            foreach ($this->model->getHasOne() as $modelName)
            {
                $template = str_replace(
                    '{{Relations}}',

                    $this->getRelationsFunction($modelName, 'has_one')
                        . "\n{{Relations}}",

                    $template
                );
            }
        }

        // Insert hasMany relations if any
        if ($this->model->getHasMany())
        {
            foreach ($this->model->getHasMany() as $modelName)
            {
                $template = str_replace(
                    '{{Relations}}',

                    $this->getRelationsFunction($modelName, 'has_many')
                        . "\n{{Relations}}",

                    $template
                );
            }
        }

        // Insert belongsTo relations if any
        if ($this->model->getBelongsTo())
        {
            foreach ($this->model->getBelongsTo() as $modelName)
            {
                $template = str_replace(
                    '{{Relations}}',

                    $this->getRelationsFunction($modelName, 'belongs_to')
                        . "\n{{Relations}}",

                    $template
                );
            }
        }

        // Insert hmabt relations if any
        if ($this->model->getHasManyAndBelongsTo())
        {
            foreach ($this->model->getHasManyAndBelongsTo() as $modelName)
            {
                $template = str_replace(
                    '{{Relations}}',

                    $this->getRelationsFunction($modelName, 'has_many_and_belongs_to')
                        . "\n{{Relations}}",

                    $template
                );
            }
        }

        // Remove {{Relations}} text
        $template = str_replace('{{Relations}}', '', $template);

        // Set timestamps tot rue or false
        if ($this->model->getTimestamps())
        {
            $template = str_replace(
                '{{Timestamps}}',
                'public static $timestamps = true;',
                $template
            );
        }
        else
        {
            $template = str_replace(
                '{{Timestamps}}',
                'public static $timestamps = false;',
                $template
            );
        }

        // Return the output
        return $template;
    }

    /**
     * Generate spaces based on the indent given, assuming 4 spaces per
     * indent
     *
     * @param $indent int
     * @return string
     */
    private function whitespace($indent)
    {
        return str_repeat('    ', $indent);
    }

    /**
     * Returns the relations function, given a model name and relationship
     * type
     * @param string $name
     * @param string $type
     * @return string
     */
    private function getRelationsFunction($name, $type)
    {
        $template = file_get_contents(\Bundle::path('larry') . 'templates/Relation.template');
        $modelName = $name;

        if ($type == 'has_one' or $type == 'belongs_to')
        {
            $functionName = strtolower($name);
        }
        else
        {
            $functionName = Inflect::pluralize(strtolower($name));
        }

        // Put in the function name
        $template = str_replace('{{RelName}}', $functionName, $template);

        // Put in the relationship type
        $template = str_replace('{{RelType}}', $type, $template);

        // Put in the model name
        $template = str_replace('{{ModelName}}', $modelName, $template);

        // Return the output
        return $template;
    }

    /**
     * Get the filename of the model
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
}