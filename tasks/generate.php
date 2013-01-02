<?php

class Larry_Generate_Task
{
    public function run($arguments)
    {
        if (!isset($arguments[0]))
        {
            die("Please enter an input filename.\n");
        }

        if (!file_exists($arguments[0]))
        {
            die("Please enter a valid filename\n");
        }

        $inputFilename = $arguments[0];
        $migrationsDirectory = 'application/migrations/';
        $modelsDirectory = 'application/models/';

        // Parse the file
        $parser = new Larry\Parser($inputFilename);

        // All good, now get the models and the relational models
        $models = $parser->getModels();
        $relationalModels = $parser->getRelationModels();


        // Write model migrations
        foreach ($models as $model)
        {
            $migration = new Larry\MigrationGenerator($model);
            file_put_contents(
                $migrationsDirectory . $migration->getFilename(),
                $migration->getOutput()
            );

            echo "[MIGRATION WRITTEN] " . $migration->getFilename() . "\n";
        }

        // Write relational model migrations
        foreach ($relationalModels as $model)
        {
            $migration = new Larry\MigrationGenerator($model);
            file_put_contents(
                $migrationsDirectory . $migration->getFilename(),
                $migration->getOutput()
            );

            echo "[MIGRATION WRITTEN] " . $migration->getFilename() . "\n";
        }

        // Write the model file
        foreach ($models as $model)
        {
            $modelGenerator = new Larry\ModelGenerator($model);
            file_put_contents(
                $modelsDirectory . $modelGenerator->getFilename(),
                $modelGenerator->getOutput()
            );

            echo "[MODEL WRITTEN] " . $modelGenerator->getFilename() . "\n";
        }

        // Copy basemodel
        $baseModelContents = file_get_contents(\Bundle::path('larry') . 'templates/Basemodel.template');
        file_put_contents(
            $modelsDirectory . 'Basemodel.php',
            $baseModelContents
        );

        echo "[MODEL WRITTEN] " . 'Basemodel.php' . "\n";

        echo "\n<<<<< Process successfully completed. >>>>>\n";
    }
}