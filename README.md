# Taggable Behavior for Yii 2

[![PayPal Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HJ6LFVXEX8NDW)
[![Build Status](https://img.shields.io/travis/creocoder/yii2-taggable/master.svg?style=flat-square)](https://travis-ci.org/creocoder/yii2-taggable)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/creocoder/yii2-taggable/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/creocoder/yii2-taggable/?branch=master)
[![Code Quality](https://img.shields.io/scrutinizer/g/creocoder/yii2-taggable/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/creocoder/yii2-taggable/?branch=master)

A modern taggable behavior for the Yii framework.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ php composer.phar require creocoder/yii2-taggable:dev-master
```

or add

```
"creocoder/yii2-taggable": "dev-master"
```

to the `require` section of your `composer.json` file.

## Configuring

Configure model as follows

```php
use creocoder\taggable\TaggableBehavior;

/**
 * ...
 * @property string $tagNames
 */
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'taggable' => [
                'class' => TaggableBehavior::className(),
                // 'tagNamesAsArray' => false,
                // 'tagRelation' => 'tags',
                // 'tagNameAttribute' => 'name',
                // 'tagFrequencyAttribute' => 'frequency',
            ],
        ];
    }

    public function rules()
    {
        return [
            //...
            ['tagNames', 'safe'],
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
            ->viaTable('post_tag_assn', ['post_id' => 'id']);
    }
}
```

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
$post->tagNames = 'foo, bar, baz';

// through array
$post->tagNames = ['foo', 'bar', 'baz'];
```

### Adding tags to the entity

To add tags to the entity

```php
$post = Post::findOne(1);

// through string
$post->addTagNames('bar, baz');

// through array
$post->addTagNames(['bar', 'baz']);
```

### Remove tags from the entity

To remove tags from the entity

```php
$post = Post::findOne(1);

// through string
$post->removeTagNames('bar, baz');

// through array
$post->removeTagNames(['bar', 'baz']);
```

### Getting tags from the entity

To get tags from the entity

```php
$posts = Post::find()->with('tags')->all();

foreach ($posts as $post) {
    // as string
    $tagNames = $post->tagNames;

    // as array
    $tagNamesAsArray = $post->getTagNames(true);
}
```

Return type of `getTagNames` can also be configured globally via `tagNamesAsArray` property.

### Checking for tags in the entity

To check for tags in the entity

```php
$post = Post::findOne(1);

// through string
$result = $post->hasTagNames('foo, bar');

// through array
$result = $post->hasTagNames(['foo', 'bar']);
```

### Search entities by any tags

To search entities by any tags

```php
// through string
$posts = Post::find()->anyTagNames('foo, bar')->all();

// through array
$posts = Post::find()->anyTagNames(['foo', 'bar'])->all();
```

### Search entities by all tags

To search entities by all tags

```php
// through string
$posts = Post::find()->allTagNames('foo, bar')->all();

// through array
$posts = Post::find()->allTagNames(['foo', 'bar'])->all();
```

### Search entities related by tags

To search entities related by tags

```php
// through string
$posts = Post::find()->relatedByTagNames('foo, bar')->all();

// through array
$posts = Post::find()->relatedByTagNames(['foo', 'bar'])->all();
```
