<?php
namespace Larry;

/**
 * Parse an input text file and create the necessary data structure
 */
class Parser
{
    /**
     * Models to be written as 'name' => Model
     * @var array Field
     */
    private $models = array();

    /**
     * RelationModels to be written
     * @var array
     */
    private $relModels = array();

    /**
     * Stores the foreign keys that need to be added in
     * 'Model Name' => array('user_id'...) .... form
     * @var array
     */
    private $foreignKeys = array();

    /**
     * Current line number that is being parsed
     */
    private $currentLineNumber = 0;

    /**
     * Initialize the parser with the filename/filepath
     * @param $file string
     */
    public function __construct($file)
    {
        $contents = file_get_contents($file);
        $this->parse($contents);
    }

    /**
     * Parse the file and create the necessary model/migration objects
     * @param $contents string
     * @throws \Exception
     */
    private function parse($contents)
    {
        /*
         * Algorithm:
         * Foreach model, create a new model and store the model file in
         * a 'Name' => Model assoc. This will help access it later for relations
         *
         * Foreach foreign key detected, add it to a foreign key array as
         * 'Model Name' => array(keys,.. 'user_id'), since the model that a foreign key represents
         * might not have been created yet.
         *
         * Also, add a foreign key only if not already there. This will prevent duplicates
         * on hasOne/hasMany and the belongsTo, and will also allow a belongsTo without
         * hasOne and vice versa.
         *
         * Also, add the appropriate relation in the current model.
         *
         * If the relation is many-to-many, also create a relations model for migrations.
         *
         * For the current model, add all the columns found, with their validation rules
         *
         * After all models and columns have been added, add the foreign keys to all the models
         *
         * Now, the models have been created. Create a new migrationgenerator object for each
         * model/relationalmodel and write them to a files. Also, create a modelgenerator for
         * each model (not relationalmodel) and write them to files.
         *
         */

        /** @var $currentModel Model */
        $currentModel = null;

        foreach (explode("\n", $contents) as $line) // Parse each line
        {
            // Increment current line number
            $this->currentLineNumber++;

            if (trim($line) == '')
            {
                continue; // Ignore blank lines
            }

            // For lines starting with space(s), assume it to be a column
            if ($line[0] == ' ')
            {
                // Exception if no model defined
                if (is_null($currentModel))
                {
                    $this->throwParseError("Field specified, but no model created yet.");
                }

                if (trim($line) == 'timestamps')
                {
                    // Set timestamps true
                    $currentModel->setTimestamps();
                }
                else
                {
                    // Get field from line, and add it to the current model
                    $col = $this->getFieldFromLine($line);
                    $currentModel->addField($col);
                }
            }
            // Else, assume it to be the model definition line
            else
            {
                // Get a new model, a relation model if valid, and other meta datas
                // of a model, given a line
                $modelData = $this->getModelDataFromLine($line);

                // Set current model
                $currentModel = $modelData['model'];

                // Add current model to the list
                $this->models[$modelData['name']] = $modelData['model'];

                // Merge all new foreign key relations found
                foreach ($modelData['foreignKeys'] as $modelName => $fks)
                {
                    // If we're seeing this model for the first time, add everything
                    // to the array
                    if (!isset($this->foreignKeys[$modelName]))
                    {
                        $this->foreignKeys[$modelName] = $fks;
                    }
                    else
                    {
                        // Add only foreign keys that don't already exist
                        foreach ($fks as $fk)
                        {
                            if (!in_array($fk, $this->foreignKeys[$modelName]))
                            {
                                $this->foreignKeys[$modelName][] = $fk;
                            }
                        }
                    }
                } // End merging of all foreign key relations

                // Add relations in the current model
                foreach ($modelData['relations'] as $modelName => $rel)
                {
                    if ($rel == 'has_one')
                    {
                        $currentModel->addHasOne($modelName);
                    }
                    else if ($rel == 'belongs_to')
                    {
                        $currentModel->addBelongsTo($modelName);
                    }
                    else if ($rel == 'has_many')
                    {
                        $currentModel->addHasMany($modelName);
                    }
                    else if ($rel == 'has_many_and_belongs_to')
                    {
                        $this->relModels[] = new RelationModel($currentModel->getName(), $modelName);
                        $currentModel->addHasManyAndBelongsTo($modelName);
                    }
                }
            }

        } // End parsing

        // After parsing, add in foreign key fields to all columns
        foreach ($this->foreignKeys as $modelName => $fks)
        {
            foreach ($fks as $fk)
            {
                if (!isset($this->models[$modelName]))
                {
                    $this->throwParseError('Model "' . $modelName . '" referenced in a relation, but not defined.');
                }
                $this->models[$modelName]->addField(new Field($fk, 'integer'));
            }
        }
    }

    /**
     * Given a line, create a complete field object from it
     * @param $line string
     * @return Field
     * @throws \Exception
     */
    private function getFieldFromLine($line)
    {
        $line = trim($line);
        $parts = explode('->', $line);

        // Parse the field name, params, and properties
        $fieldDetails = explode(':', trim($parts[0]));

        // If field details doesn't have at least two elements, then it's an error
        if (count($fieldDetails) < 2)
        {
            $this->throwParseError('Field type not specified.');
        }

        // Actual field details
        $name = trim($fieldDetails[0]);
        $type = explode(',', trim($fieldDetails[1]));

        // Actual field object
        $fieldObj = new Field($name, $type[0], array_slice($type, 1));

        // Other field parameters
        foreach (array_slice($fieldDetails, 2) as $property)
        {
            // Forget about defaults for now
            if ($property === 'nullable')
            {
                $fieldObj->setNullable();
            }
            else if ($property == 'fulltext')
            {
                $fieldObj->setFulltext();
            }
            else if ($property == 'index')
            {
                $fieldObj->setIndexed();
            }
            else if ($property == 'primary')
            {
                $fieldObj->setPrimary();
            }
            else if ($property == 'unique')
            {
                $fieldObj->setUnique();
            }
            else if ($property == 'unsigned')
            {
                $fieldObj->setUnsigned();
            }
            else
            {
                $this->throwParseError('Unknown field type: ' . $property);
            }
        }

        // Check if validation data present, and add if necessary
        if (isset($parts[1]))
        {
            $fieldObj->setValidationRules(trim($parts[1]));
        }

        // Return the field object
        return $fieldObj;
    }

    /**
     * Get model data from the given line describing the model
     * @param $line string
     * @return array
     * @throws \Exception
     *
     * Should return
     * 1. 'name' => string name
     * 2. 'model' => model object
     * 3. 'foreignKeys' => array('Model Name' => array('user_id', 'profile_id' ...) ...)
     * 4. 'relations' => array('Other Model Name' => 'belongs_to, has_one, has_many, has_many_and_belongs_to')
     */
    private function getModelDataFromLine($line)
    {
        $outputArray = array();
        $outputArray['foreignKeys'] = array();
        $outputArray['relations'] = array();

        $parts = explode(' ', trim($line));

        // Create the model object
        $modelObj = new Model(trim($parts[0]));
        $outputArray['name'] = trim($parts[0]);
        $outputArray['model'] = $modelObj;

        // Now to the relations
        foreach (array_slice($parts, 1) as $relation)
        {
            $relparts = explode(':', $relation);
            $relName = trim($relparts[0]);
            $relType = trim($relparts[1]);

            // Update relations
            if ($relType == 'ho')
            {
                $outputArray['relations'][$relName] = 'has_one';
            }
            else if ($relType == 'hm')
            {
                $outputArray['relations'][$relName] = 'has_many';
            }
            else if ($relType == 'bt')
            {
                $outputArray['relations'][$relName] = 'belongs_to';
            }
            else if ($relType == 'hmb')
            {
                $outputArray['relations'][$relName] = 'has_many_and_belongs_to';
            }
            else
            {
                $this->throwParseError('Undefined relation: ' . $relType);
            }

            // Now to foreign keys
            $thisModelName = $outputArray['name'];
            /*if ($relType == 'ho')
            {
                $outputArray['foreignKeys'][$thisModelName][] = strtolower($relName) . '_id';
            }
            else */
            if ($relType == 'hm' or $relType == 'ho')
            {
                $outputArray['foreignKeys'][$relName][] = strtolower($thisModelName) . '_id';
            }
            // We need not do anything for has many and belongs to
        }

        return $outputArray;
    }

    /**
     * Return the models
     * @return array
     */
    public function getModels()
    {
        return $this->models;
    }

    /**
     * Return relation models
     * @return array
     */
    public function getRelationModels()
    {
        return $this->relModels;
    }

    /**
     * Show the parse error message with the current line number and die
     */
    private function throwParseError($message)
    {
        echo "[Line No. {$this->currentLineNumber}] {$message}\n";
        die();
    }
}