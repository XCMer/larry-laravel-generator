# Larry, The Laravel Model Generator

Once you design a database, it takes a while to write the schema down, set up the relations, and stuffs like that. Laravel does have Eloquent, a powerful ORM, but the boilterplate code can still get boring.

Laravel does have a couple of code generators, but I wanted to merely create a text file with table information, relations, validations, and have it generate all the necessary `migration` and `model` code. You can then start writing controllers immediately to take advantage of this pre-setup.

### Latest Version

The latest version is currently 1.1. Please use `php artisan larry::version` to find the current version. If this command doesn't work, then you have 1.0.

The features in this release are:
- Parser has been made more intelligent with error handling
- A version system has been added to get the current version of Larry


## How does "Larry" work?

Following is a sample input file that Larry would accept:

    User Post:hm Profile:ho Comment:hm
        email:string,100:unique -> required|mail
        password:string,64 -> required
        timestamps

    Post User:bt Comment:hm Tag:hmb
        title:string -> required
        description:text -> required
        timestamps

    Comment User:bt Post:bt
        content:text -> required
        timestamps

    Tag Post:bt
        name:string

    Profile User:bt
        address:string
        telephone:string


It would then generate the necessary migrations for all tables. You need not specify the foreign keys in your schema, since it will be added automatically as per Laravel's default convention. Also, for `has_many_and_belongs_to` relation, the join table is automatically created for you.

After that, Larry will also generate all the model files, fill the validation details, as well as the relations. All the models extend `Basemodel`, which in turn extends `Eloquent`. This `Basemodel` provides the common functionality amongst all models, like the validation function.

### Example output of the above input

I'm not putting in all the generated files, since that would make this README too long. However, here's one of the generated migration file:

```php
2013_01_01_131249_create_tags_table.php

<?php

class Create_Tags_Table {

    public function up()
    {
        Schema::create('tags', function($table) {
            $table->increments('id');
            $table->string('name');

    });

    }

    public function down()
    {
        Schema::drop('tags');
    }

}
```

Here's one of the generated models:

```php
Post.php

<?php

class Post extends Basemodel
{
    public static $timestamps = true;

    public static $rules = array(
        'title' => 'required',
        'description' => 'required',

    );

    public function comments() {
        return $this->has_many('Comment');
    }

    public function user() {
        return $this->belongs_to('User');
    }

    public function tags() {
        return $this->has_many_and_belongs_to('Tag');
    }


}
```

## Installation

It's recommended that you install Larry via artisan. You can view Larry on Bundles here:http://bundles.laravel.com/bundle/larry/

And here's the command:

    php artisan bundle:install larry

Also, add Larry to your `application/bundles.php` as follows:

    return array(

    	'docs' => array('handles' => 'docs'),
        'larry',

    );

The `docs` comes as a default, so you just need to add `larry` to the array with whatever is already there.


## Running Larry

Install Larry as a bundle inside your "bundles/larry" folder. The folder name has to be "larry." Then create a text file within the root folder of your Laravel installation, and run the following command from there:

    php artisan larry::generate input_file.txt

This will take `input_file.txt` as an input for parsing, and will write all migrations and models to your application folder. **Larry won't ask you before overwriting models! So use with caution.**


## Finding Larry's Version

This feature is available in 1.1 only. You can type:

    php artisan larry::version

It will give you the current version, it's release date, as well as details on all past releases.


## Documentation

Here's a complete documentation of the format of inputs that Larry currently supports.

### 1. Model definition

You have to first define the model and its relations (if any) before you define the fields. This has to be done on an **unindented** line (should not start with a whitespace):

    <SingularModelName> <RelatedModel 1>:bt <RelatedModel 2>:hm <RelatedModel 3>:ho <RelatedModel 4>:hmb

You're specifying model names above, and they should be capitalized and singular, just as Laravel expects it. Larry uses an inflection library to take care of pluralizations.

Related models can be optionally specified in the `<Related Model>:<Relation>` format. The relation can be any of the four given below:

    ho: Has one
    hm: Has many
    bt: Belongs to
    hmb: Has many and belongs to

Foreign keys are added automatically wherever appropriate. Also, Larry knows in which table to create the foreign key. The `link table` for `has_many_and_belongs_to` is automatically created.


### 2. Field definitions

Fields definition has to come after you've defined a model. This has to be indented by at least one space, though the exact indent value is up to you. Also, blank lines are ignored, so you don't need to worry about them.

Fields take the following form:

    <field_name>:<field_type>,<field_param1>,<field_param2>:<field_properties>

This gets translated into:

    $table-><field_type>(<field_name>, <field_param1>, <field_param2>)-><field_property>();

Here's a rundown of what Larry expects as values for the above placeholders:

    field_name: The name of the database column
    field_type: Any field type defined in Laravel's Schema Builder class
    field_param1, field_param2: This is for additional field parameters, like lengths of strings and ranges
    <field_property>: Any of the field property like nullable, primary, unique, indexed, fulltext, unsigned

Here's an example field declaration:

    name:string,50:nullable:unique

As you can see, you can chain multiple field properties. As of now, the `default` property is not supported. I have to update the parser for this, though the core classes of Larry do have support for the `default` property.


### 3. Timestamps

If you want to add Laravel's timestamps to a model, just write:

    timestamps

with no additional parameters.


### 4. Field validations

You can optionally specify the validation parameters for a field right after its definition:

    name:string,50:nullable:unique -> required|max:50|min:5

After your field definition, just add a "->" and your validation rules as you normally write in Laravel. The rules are copied as is with minimal processing (like trimming).

All models do get a static function `validate`. And it uses the rules specified here.

**Foreign keys:** Do not add fields for foreign keys, since it's automatically done for you by Larry.


### 4. That's it, and limitations

Well, that's all Larry does for now. But it is a **huge** time saver.

Since Larry is still young, he does have a couple of limitations:

1. Larry only generates migrations and models, with relations and validations. Controllers, views, and anything else is not supported. (And I'm not planning on adding them, since we already have good generators for that)

2. The parser and the generator are pretty dumb. They won't validate whether a validation rule or a field that you used is valid or not.

3. The error handling is naive. If you give unexpected input (like an unknown relation, or a field without defining a model), then Larry simply throws an exception and shuts down. You will know the content of the "bad data", but not the line number for now.


### 5. You can test and contribute

 Give Larry a test drive, and see if it's working correctly. If you find any bugs or would like to make any enhancement, feel free to fork and send a pull request.