<?php
namespace Larry;

/**
 * Represent a database model
 * @author Raahul Seshadri <raahul@musictypes.com>
 */
 class Model
 {
     /**
      * Single word, singular, capitalized, model name
      * @var string
      */
     private $name = null;

     /**
      * Single word, plural, lowercase, table name
      * @var string
      */
     private $tableName = null;

     /**
      * Array of models the current model belongsTo
      * @var array
      */
     private $belongsTo = array();

     /**
      * Array of models the current model hasOne
      * @var array
      */
     private $hasOne = array();

     /**
      * Array of models the current model hasMany
      * @var array
      */
     private $hasMany = array();

     /**
      * Array of models the current model hasManyAndBelongsTo
      * @var array
      */
     private $hasManyAndBelongsTo = array();

     /**
      * Array of fields that belong to this model
      * @var array
      */
     private $fields = array();

     /**
      * Whether this model has Laravel timestamps
      * @var boolean
      */
     private $timestamps = false;


     /**
      * Constructor to initialize model name and calculate table name
      */
     public function __construct($name)
     {
         $this->name = $name;

         // Logic for pluralized table name
         $this->tableName = Inflect::pluralize(strtolower($name));
     }

     /**
      * Get name
      * @return string
      */
     public function getName()
     {
         return $this->name;
     }

     /**
      * Get table name
      * @return string
      */
     public function getTableName()
     {
         return $this->tableName;
     }

     /**
      * Set timestamps
      * @return Model
      */
     public function setTimestamps()
     {
         $this->timestamps = true;
     }

     /**
      * Get timestamps
      * @return boolean
      */
     public function getTimestamps()
     {
         return $this->timestamps;
     }

     /**
      * Add a field
      * @param $field Field
      * @return Model
      */
     public function addField(Field $field)
     {
         $this->fields[] = $field;

         return $this;
     }

     /**
      * Add a belongsTo relation
      * @param $modelName string
      * @return Model
      */
     public function addBelongsTo($modelName)
     {
         $this->belongsTo[] = $modelName;
     }

     /**
      * Add a hasOne relation
      * @param $modelName string
      * @return Model
      */
     public function addHasOne($modelName)
     {
         $this->hasOne[] = $modelName;
     }

     /**
      * Add a hasMany relation
      * @param $modelName string
      * @return Model
      */
     public function addHasMany($modelName)
     {
         $this->hasMany[] = $modelName;
     }

     /**
      * Add a hasManyAndBelongsTo relation
      * @param $modelName string
      * @return Model
      */
     public function addHasManyAndBelongsTo($modelName)
     {
         $this->hasManyAndBelongsTo[] = $modelName;
     }

     /**
      * Return all the fields
      * @return array
      */
     public function getFields()
     {
         return $this->fields;
     }

     /**
      * Return all hasOne relations
      * @return array
      */
     public function getHasOne()
     {
         return $this->hasOne;
     }

     /**
      * Return all hasMany relations
      * @return array
      */
     public function getHasMany()
     {
         return $this->hasMany;
     }

     /**
      * Return all belongsTo relations
      * @return array
      */
     public function getBelongsTo()
     {
         return $this->belongsTo;
     }

     /**
      * Return all hasManyAndBelongsTo relations
      * @return array
      */
     public function getHasManyAndBelongsTo()
     {
         return $this->hasManyAndBelongsTo;
     }
 }