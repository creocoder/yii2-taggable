<?php
/**
 * @link https://github.com/creocoder/yii2-taggable
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace creocoder\taggable;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * TaggableBehavior
 *
 * @property ActiveRecord $owner
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class TaggableBehavior extends Behavior
{
    /**
     * @var string
     */
    public $attribute = 'tagNames';
    /**
     * @var string
     */
    public $name = 'name';
    /**
     * @var string
     */
    public $frequency = 'frequency';
    /**
     * @var string
     */
    public $relation = 'tags';

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * @return void
     */
    public function afterFind()
    {
        $items = [];

        /* @var ActiveRecord $tag */
        foreach ($this->owner->{$this->relation} as $tag) {
            $items[] = $tag->getAttribute($this->name);
        }

        $this->owner->{$this->attribute} = implode(', ', $items);
    }

    /**
     * @return void
     */
    public function afterSave()
    {
        $attribute = $this->owner->{$this->attribute};

        if ($attribute === null) {
            return;
        }

        if (!$this->owner->getIsNewRecord()) {
            $this->beforeDelete();
        }

        $names = array_unique(preg_split(
            '/\s*,\s*/u',
            preg_replace('/\s+/u', ' ',  $attribute),
            -1,
            PREG_SPLIT_NO_EMPTY
        ));

        $relation = $this->owner->getRelation($this->relation);
        $pivot = $relation->via->from[0];
        /* @var ActiveRecord $class */
        $class = $relation->modelClass;
        $rows = [];

        foreach ($names as $name) {
            /* @var ActiveRecord $tag */
            $tag = $class::findOne([$this->name => $name]);

            if ($tag === null) {
                $tag = new $class();
                $tag->setAttribute($this->name, $name);
            }

            $frequency = $tag->getAttribute($this->frequency);
            $tag->setAttribute($this->frequency, ++$frequency);

            if (!$tag->save()) {
                continue;
            }

            $rows[] = [$this->owner->getPrimaryKey(), $tag->getPrimaryKey()];
        }

        if (!empty($rows)) {
            $this->owner->getDb()
                ->createCommand()
                ->batchInsert($pivot, [key($relation->via->link), current($relation->link)], $rows)
                ->execute();
        }
    }

    /**
     * @return void
     */
    public function beforeDelete()
    {
        $relation = $this->owner->getRelation($this->relation);
        $pivot = $relation->via->from[0];
        /* @var ActiveRecord $class */
        $class = $relation->modelClass;
        $query = new Query();

        $pks = $query
            ->select(current($relation->link))
            ->from($pivot)
            ->where([key($relation->via->link) => $this->owner->getPrimaryKey()])
            ->column($this->owner->getDb());

        if (!empty($pks)) {
            $class::updateAllCounters([$this->frequency => -1], ['in', $class::primaryKey(), $pks]);
        }

        $this->owner->getDb()
            ->createCommand()
            ->delete($pivot, [key($relation->via->link) => $this->owner->getPrimaryKey()])
            ->execute();
    }
}
