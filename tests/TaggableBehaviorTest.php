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
        $posts = Post::find()->with('tags')->all();

        foreach ($posts as $post) {
            $data[] = $post->toArray([], ['tags']);
        }

        $this->assertEquals(require(__DIR__ . '/data/test-find-posts.php'), $data);
    }

    public function testFindPost()
    {
        $post = Post::findOne(2);
        $this->assertEquals('tag2, tag3, tag4', $post->tagValues);
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

    public function testCreatePostSetTagValues()
    {
        $post = new Post([
            'title' => 'New post title',
            'body' => 'New post body',
            'tagValues' => 'tag4, tag4, tag5, , tag6',
        ]);

        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-create-post-set-tag-values.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testCreatePostSetTagValuesAsArray()
    {
        $post = new Post([
            'title' => 'New post title',
            'body' => 'New post body',
            'tagValues' => ['tag4', 'tag4', 'tag5', '', 'tag6'],
        ]);

        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-create-post-set-tag-values.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testCreatePostAddTagValues()
    {
        $post = new Post([
            'title' => 'New post title',
            'body' => 'New post body',
        ]);

        $post->addTagValues('tag4, tag4, tag5, , tag6');
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-create-post-add-tag-values.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testCreatePostAddTagValuesAsArray()
    {
        $post = new Post([
            'title' => 'New post title',
            'body' => 'New post body',
        ]);

        $post->addTagValues(['tag4', 'tag4', 'tag5', '', 'tag6']);
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-create-post-add-tag-values.xml');
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

    public function testUpdatePostSetTagValues()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->tagValues = 'tag3, tag3, tag4, , tag6';
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-set-tag-values.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePostSetTagValuesAsArray()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->tagValues = ['tag3', 'tag3', 'tag4', '', 'tag6'];
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-set-tag-values.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePostAddTagValues()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->addTagValues('tag3, tag3, , tag6');
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-add-tag-values.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePostAddTagValuesAsArray()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->addTagValues(['tag3', 'tag3', '', 'tag6']);
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-add-tag-values.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePostRemoveTagValues()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->removeTagValues('tag2, tag2, , tag4');
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-remove-tag-values.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePostRemoveTagValuesAsArray()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->removeTagValues(['tag2', 'tag2', '', 'tag4']);
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-remove-tag-values.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdatePostRemoveAllTagValues()
    {
        $post = Post::findOne(2);
        $post->title = 'Updated post title 2';
        $post->body = 'Updated post body 2';
        $post->removeAllTagValues();
        $this->assertTrue($post->save());

        $dataSet = $this->getConnection()->createDataSet(['post', 'tag', 'post_tag_assn']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-update-post-remove-all-tag-values.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testPostHasTagValues()
    {
        $post = Post::findOne(2);
        $this->assertTrue($post->hasTagValues('tag2, tag2, , tag4'));
        $this->assertTrue($post->hasTagValues(['tag2', 'tag2', '', 'tag4']));
        $this->assertFalse($post->hasTagValues('tag3, tag3, , tag5'));
        $this->assertFalse($post->hasTagValues(['tag3', 'tag3', '', 'tag5']));
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
