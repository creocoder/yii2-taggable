<?php
/**
 * @link https://github.com/creocoder/yii2-taggable
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace creocoder\taggable;

use yii\base\Behavior;
use yii\db\Expression;

/**
 * TaggableQueryBehavior
 *
 * @property \yii\db\ActiveQuery $owner
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class TaggableQueryBehavior extends Behavior
{
    /**
     * Gets entities by any tags.
     * @param string|string[] $names
     * @return \yii\db\ActiveQuery the owner
     */
    public function anyTagNames($names)
    {
        $model = new $this->owner->modelClass();
        $tagClass = $model->getRelation($model->tagRelation)->modelClass;

        $this->owner
            ->innerJoinWith($model->tagRelation, false)
            ->andWhere([$tagClass::tableName() . '.' . $model->tagNameAttribute => $model->filterTagNames($names)])
            ->addGroupBy(array_map(function ($pk) use ($model) { return $model->tableName() . '.' . $pk; }, $model->primaryKey()));

        return $this->owner;
    }

    /**
     * Gets entities by all tags.
     * @param string|string[] $names
     * @return \yii\db\ActiveQuery the owner
     */
    public function allTagNames($names)
    {
        $model = new $this->owner->modelClass();

        return $this->anyTagNames($names)->andHaving(new Expression('COUNT(*) = ' . count($model->filterTagNames($names))));
    }

    /**
     * Gets entities related by tags.
     * @param string|string[] $names
     * @return \yii\db\ActiveQuery the owner
     */
    public function relatedByTagNames($names)
    {
        return $this->anyTagNames($names)->addOrderBy(new Expression('COUNT(*) DESC'));
    }

    /**
     * Gets entities by any tags slugs.
     * @param string|string[] $slugs
     * @return \yii\db\ActiveQuery the owner
     */
    public function anyTagSlugs($slugs)
    {
        $model = new $this->owner->modelClass();
        $tagClass = $model->getRelation($model->tagRelation)->modelClass;

        $this->owner
            ->innerJoinWith($model->tagRelation, false)
            ->andWhere([$tagClass::tableName() . '.' . $model->tagSlugAttribute => $model->filterTagNames($slugs)])
            ->addGroupBy(array_map(function ($pk) use ($model) { return $model->tableName() . '.' . $pk; }, $model->primaryKey()));

        return $this->owner;
    }

    /**
     * Gets entities by all tags slugs.
     * @param string|string[] $slugs
     * @return \yii\db\ActiveQuery the owner
     */
    public function allTagSlugs($slugs)
    {
        $model = new $this->owner->modelClass();

        return $this->anyTagSlugs($slugs)->andHaving(new Expression('COUNT(*) = ' . count($model->filterTagNames($slugs))));
    }

    /**
     * Gets entities related by tags slugs.
     * @param string|string[] $names
     * @return \yii\db\ActiveQuery the owner
     */
    public function relatedByTagSlugs($slugs)
    {
        return $this->anyTagSlugs($slugs)->addOrderBy(new Expression('COUNT(*) DESC'));
    }
}
