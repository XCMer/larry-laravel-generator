<?php
namespace Larry;

/**
 * Generate content for the migration file from the model
 * @author Raahul Seshadri <raahul@musictypes.com>
 */
class MigrationGenerator
{
    /**
     * Filename of the migration file
     * @var string
     */
    private $filename = null;

    /**
     * Capitalized plural table name
     * @var string
     */
    private $capitalizedTableName = null;

    /**
     * Lowercased plural table name
     * @var string
     */
    private $lowerTableName = null;

    /**
     * Model associated with this migration
     * @var Model|RelationModel
     */
    private $model = null;


    /**
     * Initialize all variables from the given model
     * @param Model|RelationModel $model
     */
    public function __construct($model)
    {
        $this->model = $model;

        // Fill in filename
        $this->filename = date('Y_m_d_His')
            . '_create_'
            . $model->getTableName()
            . '_table.php';

        // Fill in capitalized table name
        $this->capitalizedTableName = ucfirst($model->getTableName());

        // Fill in lowercase table name
        $this->lowerTableName = $model->getTableName();
    }

    /**
     * Get the file contents of the migration as it should be
     * written
     * @return string
     */
    public function getOutput()
    {
        // Load the template
        $template = file_get_contents(\Bundle::path('larry') . 'templates/Migration.template');

        // Put in lowercase table name
        $template = str_replace('{{TableLname}}', $this->lowerTableName, $template);

        // Put in the uppercase table name
        $template = str_replace('{{TableCname}}', $this->capitalizedTableName, $template);

        // Write the autoincrement field
        $template = str_replace(
            '{{FieldNames}}',
            "\$table->increments('id');" . "\n" . $this->whitespace(3) . '{{FieldNames}}',
            $template);

        // Write all of the fields
        foreach ($this->model->getFields() as $field)
        {
            $fieldText = $this->getFieldOutput($field);
            $template = str_replace(
                '{{FieldNames}}',
                $fieldText . "\n" . $this->whitespace(3) . '{{FieldNames}}',
                $template);
        }

        // Write the timestamps
        if ($this->model->getTimestamps())
        {
            $template = str_replace(
                '{{FieldNames}}',
                '$table->timestamps();' . "\n" . $this->whitespace(3) . '{{FieldNames}}',
                $template);
        }

        // Remove the {{FieldNames}}
        $template = str_replace('{{FieldNames}}', '', $template);

        // Return the final output
        return $template;
    }

    /**
     * Return the filename of the migration
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
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
     * Generate the field insertion line
     *
     * @param Field $field
     * @return string
     */
    private function getFieldOutput($field)
    {
        // The "$table->field(" part
        $output = '$table->' . $field->getType() . '(';

        // The field name part "'fieldname'"
        $output .= "'" . $field->getName() . "'";

        // The field parameter list
        if ($field->getParams())
        {
            $paramList = implode(', ', $field->getParams());
            $output .= ", {$paramList}";
        }

        // The closing bracket ")"
        $output .= ')';

        // Set default
        if ($field->getDefault())
        {
            $output .= "->default('" . $field->getDefault() . "')";
        }

        // Set primary
        if ($field->getPrimary())
        {
            $output .= '->primary()';
        }

        // Set unique
        if ($field->getUnique())
        {
            $output .= '->unique()';
        }

        // Set nullable
        if ($field->getNullable())
        {
            $output .= '->nullable()';
        }

        // Set unsigned
        if ($field->getUnsigned())
        {
            $output .= '->unsigned()';
        }

        // Set basic index
        if ($field->getIndexed())
        {
            $output .= '->index()';
        }

        // Set fulltext
        if ($field->getFulltext())
        {
            $output .= '->fulltext()';
        }

        // Closing semicolon
        $output .= ';';

        // Return the output
        return $output;
    }
}