<?php
namespace app\modules\mch\models;

use app\models\RefundAddress;

/**
 * @property \app\models\RefundAddress $model;
 */
class RefundResonForm extends MchModel
{
    public $model;
    public $is_delete;

    public $refund_reason;
    public $refund_status;
    public $type;
    public function rules()
    {
        return [
            [['is_delete'], 'integer'],
            [['refund_reason', 'refund_status'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'refund_reason' => '退款原因',
            'refund_status' => '货物状态',
            'is_delete' => '是否删除 （0-否 1-是）',
        ];
    }
    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        $this->model->refund_reason = $this->refund_reason;
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
