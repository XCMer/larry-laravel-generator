<?php
namespace Larry;
/**
 * Represents a model for many-many link table, incorporating functions
 * only needed by the MigrationGenerator
 */
class RelationModel
{
    /**
     * The table name of the relation model (link table)
     * @var string
     */
    private $tableName;

    /**
     * The fields corresponding to the primary keys of the
     * two tables
     * @var array
     */
    private $fields = array();

    /**
     * Initialize the table name
     *
     * @param $fromTable string
     * @param $toTable string
     */
    public function __construct($fromTable, $toTable)
    {
        $from = strtolower($fromTable);
        $to = strtolower($toTable);

        $this->tableName = $to . '_' . $from;
        $this->fields[] = new Field("{$from}_id", 'integer');
        $this->fields[] = new Field("{$to}_id", 'integer');
    }

    /**
     * Return the table name
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Return the field array containing the primary keys of both
     * the tables
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Return false for timestamps
     */
    public function getTimestamps()
    {
        return false;
    }
}