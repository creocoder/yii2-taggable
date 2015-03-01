<?php
/**
 * @link https://github.com/creocoder/yii2-taggable
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use tests\models\Post;
use Yii;
use yii\db\Connection;

/**
 * TaggableQueryBehaviorTest
 */
class TaggableQueryBehaviorTest extends DatabaseTestCase
{
    public function testFindPostsAnyTagValues()
    {
        $data = [];
        $models = Post::find()->with('tags')->anyTagValues('tag 1, tag  1, , tag 2')->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts-any-tag-values.php'), $data);

        $data = [];
        $models = Post::find()->with('tags')->anyTagValues('tag-1, tag-1, , tag-2', 'slug')->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts-any-tag-values.php'), $data);
    }

    public function testFindPostsAnyTagValuesAsArray()
    {
        $data = [];
        $models = Post::find()->with('tags')->anyTagValues(['tag 1', 'tag  1', '', 'tag 2'])->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts-any-tag-values.php'), $data);

        $data = [];
        $models = Post::find()->with('tags')->anyTagValues(['tag-1', 'tag-1', '', 'tag-2'], 'slug')->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts-any-tag-values.php'), $data);
    }

    public function testFindPostsAllTagValues()
    {
        $data = [];
        $models = Post::find()->with('tags')->allTagValues('tag 3, tag  3, , tag 4')->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts-all-tag-values.php'), $data);

        $data = [];
        $models = Post::find()->with('tags')->allTagValues('tag-3, tag-3, , tag-4', 'slug')->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts-all-tag-values.php'), $data);
    }

    public function testFindPostsAllTagValuesAsArray()
    {
        $data = [];
        $models = Post::find()->with('tags')->allTagValues(['tag 3', 'tag  3', '', 'tag 4'])->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts-all-tag-values.php'), $data);

        $data = [];
        $models = Post::find()->with('tags')->allTagValues(['tag-3', 'tag-3', '', 'tag-4'], 'slug')->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts-all-tag-values.php'), $data);
    }

    public function testFindPostsRelatedByTagValues()
    {
        $data = [];
        $models = Post::find()->with('tags')->relatedByTagValues('tag 3, tag  3, tag 4, , tag 5')->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts-related-by-tag-values.php'), $data);

        $data = [];
        $models = Post::find()->with('tags')->relatedByTagValues('tag-3, tag-3, tag-4, , tag-5', 'slug')->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts-related-by-tag-values.php'), $data);
    }

    public function testFindPostsRelatedByTagValuesAsArray()
    {
        $data = [];
        $models = Post::find()->with('tags')->relatedByTagValues(['tag 3', 'tag  3', 'tag 4', '', 'tag 5'])->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts-related-by-tag-values.php'), $data);

        $data = [];
        $models = Post::find()->with('tags')->relatedByTagValues(['tag-3', 'tag-3', 'tag-4', '', 'tag-5'], 'slug')->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts-related-by-tag-values.php'), $data);
    }

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        try {
            Yii::$app->set('db', [
                'class' => Connection::className(),
                'dsn' => 'sqlite::memory:',
            ]);

            Yii::$app->getDb()->open();
            $lines = explode(';', file_get_contents(__DIR__ . '/migrations/sqlite.sql'));

            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    Yii::$app->getDb()->pdo->exec($line);
                }
            }
        } catch (\Exception $e) {
            Yii::$app->clear('db');
        }
    }
}
