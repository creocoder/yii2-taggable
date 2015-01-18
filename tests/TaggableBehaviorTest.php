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
 * TaggableBehaviorTest
 */
class TaggableBehaviorTest extends DatabaseTestCase
{
    public function testFindPosts()
    {
        $data = [];
        $models = Post::find()->with('tags')->all();

        foreach ($models as $model) {
            $data[] = $model->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts.php'), $data);
    }

    public function testCreatePost()
    {
        $post = new Post([
            'title' => 'New post title',
            'body' => 'New post body',
        ]);

        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-create-post.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testCreatePostSetTagNames()
    {
        $post = new Post([
            'title' => 'New post title',
            'body' => 'New post body',
            'tagNames' => 'tag4, tag4, tag5, , tag6',
        ]);

        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-create-post-set-tag-names.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testCreatePostSetTagNamesAsArray()
    {
        $post = new Post([
            'title' => 'New post title',
            'body' => 'New post body',
            'tagNames' => ['tag4', 'tag4', 'tag5', '', 'tag6'],
        ]);

        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-create-post-set-tag-names.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testCreatePostAddTagNames()
    {
        $post = new Post([
            'title' => 'New post title',
            'body' => 'New post body',
        ]);

        $post->addTagNames('tag4, tag4, tag5, , tag6');
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-create-post-add-tag-names.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testCreatePostAddTagNamesAsArray()
    {
        $post = new Post([
            'title' => 'New post title',
            'body' => 'New post body',
        ]);

        $post->addTagNames(['tag4', 'tag4', 'tag5', '', 'tag6']);
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-create-post-add-tag-names.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePost()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePostSetTagNames()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->tagNames = 'tag3, tag3, tag4, , tag6';
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-set-tag-names.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePostSetTagNamesAsArray()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->tagNames = ['tag3', 'tag3', 'tag4', '', 'tag6'];
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-set-tag-names.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePostAddTagNames()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->addTagNames('tag3, tag3, , tag6');
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-add-tag-names.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePostAddTagNamesAsArray()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->addTagNames(['tag3', 'tag3', '', 'tag6']);
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-add-tag-names.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePostRemoveTagNames()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->removeTagNames('tag2, tag2, , tag4');
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-remove-tag-names.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePostRemoveTagNamesAsArray()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->removeTagNames(['tag2', 'tag2', '', 'tag4']);
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-remove-tag-names.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testPosHasTagNames()
    {
        $post = Post::findOne(2);
        $this->assertTrue($post->hasTagNames('tag2, tag2, , tag4'));
        $this->assertTrue($post->hasTagNames(['tag2', 'tag2', '', 'tag4']));
        $this->assertFalse($post->hasTagNames('tag3, tag3, , tag5'));
        $this->assertFalse($post->hasTagNames(['tag3', 'tag3', '', 'tag5']));
    }

    public function testDeletePost()
    {
        $post = Post::findOne(2);
        $this->assertEquals(1, $post->delete());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-delete-post.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
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
