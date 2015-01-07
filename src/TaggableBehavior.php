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
    public $relation = 'tags';
    /**
     * @var string
     */
    public $name = 'name';
    /**
     * @var string
     */
    public $frequency = 'frequency';
    /**
     * @var array
     */
    private $_tagNames;

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
     * @return string
     */
    public function getTagNames()
    {
        if ($this->_tagNames === null && !$this->owner->isRelationPopulated($this->relation)) {
            $this->populateTagNames();
        }

        return $this->_tagNames === null ? '' : implode(', ', $this->_tagNames);
    }

    /**
     * @param string|string[] $value
     */
    public function setTagNames($value)
    {
        $this->_tagNames = array_unique(preg_split(
            '/\s*,\s*/u',
            preg_replace('/\s+/u', ' ', is_array($value) ? implode(',', $value) : $value),
            -1,
            PREG_SPLIT_NO_EMPTY
        ));
    }

    /**
     * @return void
     */
    public function afterFind()
    {
        if (!$this->owner->isRelationPopulated($this->relation)) {
            return;
        }

        $this->populateTagNames();
    }

    /**
     * @return void
     */
    public function afterSave()
    {
        if ($this->_tagNames === null) {
            return;
        }

        if (!$this->owner->getIsNewRecord()) {
            $this->beforeDelete();
        }

        $relation = $this->owner->getRelation($this->relation);
        $pivot = $relation->via->from[0];
        /* @var ActiveRecord $class */
        $class = $relation->modelClass;
        $rows = [];

        foreach ($this->_tagNames as $name) {
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

    /**
     * @return void
     */
    protected function populateTagNames()
    {
        $this->_tagNames = [];

        /* @var ActiveRecord $tag */
        foreach ($this->owner->{$this->relation} as $tag) {
            $this->_tagNames[] = $tag->getAttribute($this->name);
        }
    }
}
