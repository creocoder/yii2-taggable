# Taggable Behavior for Yii 2

[![Build Status](https://img.shields.io/travis/creocoder/yii2-taggable/master.svg?style=flat-square)](https://travis-ci.org/creocoder/yii2-taggable)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/creocoder/yii2-taggable/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/creocoder/yii2-taggable/?branch=master)
[![Code Quality](https://img.shields.io/scrutinizer/g/creocoder/yii2-taggable/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/creocoder/yii2-taggable/?branch=master)
[![Packagist Version](https://img.shields.io/packagist/v/creocoder/yii2-taggable.svg?style=flat-square)](https://packagist.org/packages/creocoder/yii2-taggable)

A modern taggable behavior for the Yii framework.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require creocoder/yii2-taggable
```

or add

```
"creocoder/yii2-taggable": "~2.0"
```

to the `require` section of your `composer.json` file.

## Migrations

Run the following command

```bash
$ yii migrate/create create_post_table
```

Open the `/path/to/migrations/m_xxxxxx_xxxxxx_create_post_table.php` file,
inside the `up()` method add the following

```php
$this->createTable('{{%post}}', [
    'id' => Schema::TYPE_PK,
    'title' => Schema::TYPE_STRING . ' NOT NULL',
    'body' => Schema::TYPE_TEXT . ' NOT NULL',
]);
```

Run the following command

```bash
$ yii migrate/create create_tag_table
```

Open the `/path/to/migrations/m_xxxxxx_xxxxxx_create_tag_table.php` file,
inside the `up()` method add the following

```php
$this->createTable('{{%tag}}', [
    'id' => Schema::TYPE_PK,
    'name' => Schema::TYPE_STRING . ' NOT NULL',
    'frequency' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
]);
```

Run the following command

```bash
$ yii migrate/create create_post_tag_assn_table
```

Open the `/path/to/migrations/m_xxxxxx_xxxxxx_create_post_tag_assn_table.php` file,
inside the `up()` method add the following

```php
$this->createTable('{{%post_tag_assn}}', [
    'post_id' => Schema::TYPE_INTEGER . ' NOT NULL',
    'tag_id' => Schema::TYPE_INTEGER . ' NOT NULL',
]);

$this->addPrimaryKey('', '{{%post_tag_assn}}', ['post_id', 'tag_id']);
```

## Configuring

Configure model as follows

```php
use creocoder\taggable\TaggableBehavior;

/**
 * ...
 * @property string $tagValues
 */
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'taggable' => [
                'class' => TaggableBehavior::className(),
                // 'tagValuesAsArray' => false,
                // 'tagRelation' => 'tags',
                // 'tagValueAttribute' => 'name',
                // 'tagFrequencyAttribute' => 'frequency',
            ],
        ];
    }

    public function rules()
    {
        return [
            //...
            ['tagValues', 'safe'],
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public static function find()
    {
        return new PostQuery(get_called_class());
    }

    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])
            ->viaTable('{{%post_tag_assn}}', ['post_id' => 'id']);
    }
}
```

Model `Tag` can be generated using Gii.

Configure query class as follows

```php
use creocoder\taggable\TaggableQueryBehavior;

class PostQuery extends \yii\db\ActiveQuery
{
    public function behaviors()
    {
        return [
            TaggableQueryBehavior::className(),
        ];
    }
}
```

## Usage

### Setting tags to the entity

To set tags to the entity

```php
$post = new Post();

// through string
$post->tagValues = 'foo, bar, baz';

// through array
$post->tagValues = ['foo', 'bar', 'baz'];
```

### Adding tags to the entity

To add tags to the entity

```php
$post = Post::findOne(1);

// through string
$post->addTagValues('bar, baz');

// through array
$post->addTagValues(['bar', 'baz']);
```

### Remove tags from the entity

To remove tags from the entity

```php
$post = Post::findOne(1);

// through string
$post->removeTagValues('bar, baz');

// through array
$post->removeTagValues(['bar', 'baz']);
```

### Remove all tags from the entity

To remove all tags from the entity

```php
$post = Post::findOne(1);
$post->removeAllTagValues();
```

### Getting tags from the entity

To get tags from the entity

```php
$posts = Post::find()->with('tags')->all();

foreach ($posts as $post) {
    // as string
    $tagValues = $post->tagValues;

    // as array
    $tagValues = $post->getTagValues(true);
}
```

Return type of `getTagValues` can also be configured globally via `tagValuesAsArray` property.

### Checking for tags in the entity

To check for tags in the entity

```php
$post = Post::findOne(1);

// through string
$result = $post->hasTagValues('foo, bar');

// through array
$result = $post->hasTagValues(['foo', 'bar']);
```

### Search entities by any tags

To search entities by any tags

```php
// through string
$posts = Post::find()->anyTagValues('foo, bar')->all();

// through array
$posts = Post::find()->anyTagValues(['foo', 'bar'])->all();
```

To search entities by any tags using custom tag model attribute

```php
// through string
$posts = Post::find()->anyTagValues('foo-slug, bar-slug', 'slug')->all();

// through array
$posts = Post::find()->anyTagValues(['foo-slug', 'bar-slug'], 'slug')->all();
```

### Search entities by all tags

To search entities by all tags

```php
// through string
$posts = Post::find()->allTagValues('foo, bar')->all();

// through array
$posts = Post::find()->allTagValues(['foo', 'bar'])->all();
```

To search entities by all tags using custom tag model attribute

```php
// through string
$posts = Post::find()->allTagValues('foo-slug, bar-slug', 'slug')->all();

// through array
$posts = Post::find()->allTagValues(['foo-slug', 'bar-slug'], 'slug')->all();
```

### Search entities related by tags

To search entities related by tags

```php
// through string
$posts = Post::find()->relatedByTagValues('foo, bar')->all();

// through array
$posts = Post::find()->relatedByTagValues(['foo', 'bar'])->all();
```

To search entities related by tags using custom tag model attribute

```php
// through string
$posts = Post::find()->relatedByTagValues('foo-slug, bar-slug', 'slug')->all();

// through array
$posts = Post::find()->relatedByTagValues(['foo-slug', 'bar-slug'], 'slug')->all();
```

## Donating

Support this project and [others by creocoder](https://gratipay.com/creocoder/) via [gratipay](https://gratipay.com/creocoder/).

[![Support via Gratipay](https://cdn.rawgit.com/gratipay/gratipay-badge/2.3.0/dist/gratipay.svg)](https://gratipay.com/creocoder/)
