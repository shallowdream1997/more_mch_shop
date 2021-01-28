<?php
/**
 * @link http://www.zjhejiang.com/
 * @copyright Copyright (c) 2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 *
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/5/2
 * Time: 14:03
 */


namespace app\modules\api\models\mch;

use app\models\MchOrderTongji;
use app\models\Order;
use app\modules\api\models\ApiModel;
use yii\data\Pagination;

class TongjiYearDataForm extends ApiModel
{
    public $mch_id;
    public $year;
    public $month;
    public $page;
    public function rules()
    {
        return [
            [['year'], 'required'],
            [['year', 'page'], 'integer'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $year_data = $this->getData($this->year);
        $last_year_data = $this->getData($this->year - 1);
        $up_rate = bcsub($year_data['year_sum'], $last_year_data['year_sum'], 2);
        return [
            'code' => 0,
            'data' => [
                'tongji_png' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/shop/img/tongji.png',
                'daily_avg' => $year_data['daily_avg'],
                'year_sum' => $year_data['year_sum'],
                'up_rate' => $up_rate,
                'list' => $year_data['year_month_data']
            ],
        ];
    }

    public function getData($year)
    {
        $start_time = strtotime("{$year}-01-01");
        $end_time = strtotime("{$year}-12-31");
        $year_sum = Order::find()->alias('o')
            ->select("SUM() AS 'pay_price'")
            ->where([
                'AND',
                ['o.mch_id' => $this->mch_id,],
                ['o.is_pay' => 1,],
                ['>=', 'o.addtime', $start_time],
                ['<=', 'o.addtime', $end_time],
            ])
            ->sum('pay_price');

        $days = $this->cal_days_in_year($year);
        $daily_avg = ($year_sum ? $year_sum : 0) / $days;

        $year_month_data = MchOrderTongji::find()->where(['mch_id' => $this->mch_id,'year' => $year])->select('year,month,month_order_sum')->asArray()->all();

        return [
            'daily_avg' => sprintf('%.2f', $daily_avg),
            'year_sum' => sprintf('%.2f', $year_sum),
            'year_month_data' => $year_month_data,
        ];
    }

    private function getMonthLastDay($month, $year) {
        switch ($month) {
            case 4 :
            case 6 :
            case 9 :
            case 11 :
                $days = 30;
                break;
            case 2 :
                if ($year % 4 == 0) {
                    if ($year % 100 == 0) {
                        $days = $year % 400 == 0 ? 29 : 28;
                    } else {
                        $days = 29;
                    }
                } else {
                    $days = 28;
                }
                break;
            default :
                $days = 31;
                break;
        }
        return $days;
    }
    private function cal_days_in_year($year)
    {
        $days = 0;
        for ($month=1;$month<=12;$month++){
            $days += self::getMonthLastDay($month,$year);
        }
        return $days;
    }

    //同年每月数据收成
    public function getYearMonthDataList($year,$month)
    {
        $start_time = strtotime("{$year}-{$month}-01");
        $end_time = strtotime("{$year}-{$month}-31");
        $year_sum = Order::find()->alias('o')
            ->select("SUM() AS 'pay_price'")
            ->where([
                'AND',
                ['o.mch_id' => $this->mch_id,],
                ['o.is_pay' => 1,],
                ['>=', 'o.addtime', $start_time],
                ['<=', 'o.addtime', $end_time],
            ])
            ->sum('pay_price');

        return $year_sum ? $year_sum : bcsub(0,0,2);
    }
}
