<?php

use yii\helpers\Html;
use yiister\gentelella\widgets\grid\GridView;
use yii\widgets\Pjax;
use common\models\Category;
use yii2mod\editable\EditableColumn;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\CategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Categories';
?>
<div class="category-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Category', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => \yii\grid\SerialColumn::class],

            [
                'attribute' => 'parent_id',
                'value' => 'parent.name',
            ],
            'name',
            [
                'class' => EditableColumn::class,
                'url' => ['change-status'],
                'type' => 'select',
                'editableOptions' => function ($model) {
                    return [
                        'source' => [Category::STATUS_ACTIVE => 'Active', Category::STATUS_DELETED => 'Disabled'],
                        'value' => $model->status,
                    ];
                },
                'filter' => Html::dropDownList('CategorySearch[status]', $searchModel->status, [
                    '' => 'All',
                    Category::STATUS_ACTIVE => 'Active',
                    Category::STATUS_DELETED => 'Disabled'
                ], ['class' => 'form-control']),
                'attribute' => 'status',
                'value' => function($model) {
                    return $model->status === Category::STATUS_ACTIVE ? 'Active' : 'Disabled';
                }
            ],

            [
                'class' => \yii\grid\ActionColumn::class,
                'template' => '{update} {delete}'
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
