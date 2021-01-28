<?php
namespace app\modules\mch\models;


/**
 * @property \app\models\SearchKeywords $model;
 */
class HotSearchForm extends MchModel
{
    public $model;
    public $is_delete;

    public $keywords;
    public $is_show;
    public function rules()
    {
        return [
            [['is_show', 'is_delete'], 'integer'],
            [['keywords'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'keywords' => '关键词',
            'is_show' => '是否显示',
            'is_delete' => '是否删除',
        ];
    }
    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        $this->model->keywords = $this->keywords;
        $this->model->is_show = $this->is_show;
        $this->model->is_delete = 0;

        if ($this->model->save()) {
            return [
                'code'=>0,
                'msg'=>'保存成功'
            ];
        } else {
            return $this->getErrorResponse($this->model);
        }
    }
}
