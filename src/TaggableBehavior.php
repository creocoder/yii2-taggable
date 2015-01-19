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
     * @var boolean
     */
    public $tagNamesAsArray = false;
    /**
     * @var string
     */
    public $tagRelation = 'tags';
    /**
     * @var string
     */
    public $tagNameAttribute = 'name';
    /**
     * @var string|false
     */
    public $tagFrequencyAttribute = 'frequency';
    /**
     * @var string[]
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
     * @param boolean|null $asArray
     * @return string|string[]
     */
    public function getTagNames($asArray = null)
    {
        if (!$this->owner->getIsNewRecord()
            && $this->_tagNames === null
            && !$this->owner->isRelationPopulated($this->tagRelation)) {
            $this->populateTagNames();
        }

        if ($asArray === null) {
            $asArray = $this->tagNamesAsArray;
        }

        if ($asArray) {
            return $this->_tagNames === null ? [] : $this->_tagNames;
        } else {
            return $this->_tagNames === null ? '' : implode(', ', $this->_tagNames);
        }
    }

    /**
     * @param string|string[] $names
     */
    public function setTagNames($names)
    {
        $this->_tagNames = array_unique($this->filterTagNames($names));
    }

    /**
     * @param string|string[] $names
     */
    public function addTagNames($names)
    {
        $this->_tagNames = array_unique(array_merge($this->getTagNames(true), $this->filterTagNames($names)));
    }

    /**
     * @param string|string[] $names
     */
    public function removeTagNames($names)
    {
        $this->_tagNames = array_diff($this->getTagNames(true), $this->filterTagNames($names));
    }

    /**
     * @param string|string[] $names
     * @return boolean
     */
    public function hasTagNames($names)
    {
        $tagNames = $this->getTagNames(true);

        foreach ($this->filterTagNames($names) as $name) {
            if (!in_array($name, $tagNames)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return void
     */
    public function afterFind()
    {
        if (!$this->owner->isRelationPopulated($this->tagRelation)) {
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

        $tagRelation = $this->owner->getRelation($this->tagRelation);
        $pivot = $tagRelation->via->from[0];
        /* @var ActiveRecord $class */
        $class = $tagRelation->modelClass;
        $rows = [];

        foreach ($this->_tagNames as $name) {
            /* @var ActiveRecord $tag */
            $tag = $class::findOne([$this->tagNameAttribute => $name]);

            if ($tag === null) {
                $tag = new $class();
                $tag->setAttribute($this->tagNameAttribute, $name);
            }

            if ($this->tagFrequencyAttribute !== false) {
                $frequency = $tag->getAttribute($this->tagFrequencyAttribute);
                $tag->setAttribute($this->tagFrequencyAttribute, ++$frequency);
            }

            if (!$tag->save()) {
                continue;
            }

            $rows[] = [$this->owner->getPrimaryKey(), $tag->getPrimaryKey()];
        }

        if (!empty($rows)) {
            $this->owner->getDb()
                ->createCommand()
                ->batchInsert($pivot, [key($tagRelation->via->link), current($tagRelation->link)], $rows)
                ->execute();
        }
    }

    /**
     * @return void
     */
    public function beforeDelete()
    {
        $tagRelation = $this->owner->getRelation($this->tagRelation);
        $pivot = $tagRelation->via->from[0];

        if ($this->tagFrequencyAttribute !== false) {
            /* @var ActiveRecord $class */
            $class = $tagRelation->modelClass;

            $pks = (new Query())
                ->select(current($tagRelation->link))
                ->from($pivot)
                ->where([key($tagRelation->via->link) => $this->owner->getPrimaryKey()])
                ->column($this->owner->getDb());

            if (!empty($pks)) {
                $class::updateAllCounters([$this->tagFrequencyAttribute => -1], ['in', $class::primaryKey(), $pks]);
            }
        }

        $this->owner->getDb()
            ->createCommand()
            ->delete($pivot, [key($tagRelation->via->link) => $this->owner->getPrimaryKey()])
            ->execute();
    }

    /**
     * @param string|string[] $names
     * @return string[]
     */
    public function filterTagNames($names)
    {
        return preg_split(
            '/\s*,\s*/u',
            preg_replace('/\s+/u', ' ', is_array($names) ? implode(',', $names) : $names),
            -1,
            PREG_SPLIT_NO_EMPTY
        );
    }

    /**
     * @return void
     */
    protected function populateTagNames()
    {
        $this->_tagNames = [];

        /* @var ActiveRecord $tag */
        foreach ($this->owner->{$this->tagRelation} as $tag) {
            $this->_tagNames[] = $tag->getAttribute($this->tagNameAttribute);
        }
    }
}
