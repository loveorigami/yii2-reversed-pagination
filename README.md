Yii2-reversed-pagination
========================
Use for reversed pagination for Yii2.

In controller
```php
    public function actionIndex()
    {
        $query = Article::find()->all();
        $countQuery = clone $query;
        $pages = new \loveorigami\pagination\ReversePagination(
            [
                'totalCount' => $countQuery->count(),
                'pageSize' => 10, // or in config Yii::$app->params['pageSize']
            ]
        );
        $pages->pageSizeParam = false;
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('index',
            [
                'models'  => $models,
                'pages' => $pages,
            ]
        );
    }
```

In view
```php
    echo \loveorigami\pagination\ReverseLinkPager::widget([
        'pagination' => $pages,
        'registerLinkTags' => true
    ]);
```

