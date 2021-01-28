<?php
// +----------------------------------------------------------------------
// | TPlusOAuth SDK V2[API基类]
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 曹·风暴烈酒 <stormstout@aliyun.com>
// +----------------------------------------------------------------------
namespace app\utils;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TplusApi {

	const API_VERSION = 'v2'; // API版本号
	const API_HOST = 'http://u20v927245.iask.in'; // API主机信息
	const API_MODULE = '/TPlus/api/'; // API模块
	const EXPIRY_MILLISECOND = 30000; // 过期毫秒数

	public static $options = []; // API实例化参数
	public static $privateRSAKey = ''; // 私钥KEY
	public static $accessToken = ''; // 访问令牌
	public static $accountId = ''; // 账套账号ID
	public static $accountPassword = ''; // 账套账号密码
	public static $accountNumber = ''; // 账套编号
	public static $authUri = ''; // 认证URI
	public static $authParam = ''; // 认证URI
	public static $apiUrl = ''; // API地址
	public static $selectFields = []; // 业务接口筛选字段
	//public static $imageUri = self::API_HOST . '/tplus'; // TPlus上存储的图片URI地址

	/**
	 * 初始化
	 */
	public function __construct($options) {
		# 检查私钥配置
		if (isset($options['cert']) and file_exists($options['cert'])) {
			self::$privateRSAKey = file_get_contents($options['cert']);
		} else {
			die('私钥数据读取失败');
		}
		# 是否使用账套
		if (!isset($options['authmode'])) {
			die('请设置认证模式');
		}
		switch ($options['authmode']) {
		case 'account':
			# 使用账套
			if (!isset($options['account']['id']) or empty($options['account']['id'])) {
				die('您使用的是账套模式，请配置账号ID(account.id)');
			}
			if (!isset($options['account']['password']) or empty($options['account']['password'])) {
				die('您使用的是账套模式，请配置账号密码(account.password)');
			}
			if (!isset($options['account']['number']) or empty($options['account']['number'])) {
				die('您使用的是账套模式，请配置账套编号(account.number)');
			}
			self::$options = [
				'appkey' => (isset($options['appkey'])) ? $options['appkey'] : '',
				'orgid' => '',
				'appsecret' => (isset($options['appsecret'])) ? $options['appsecret'] : '',
			];
			self::$accountId = $options['account']['id'];
			self::$accountPassword = $options['account']['password'];
			self::$accountNumber = $options['account']['number'];
			self::$authUri = '/collaborationapp/GetRealNameTPlusToken?IsFree=1';
			self::$authParam = [
				'_args' => json_encode([
					'userName' => self::$accountId,
					'password' => md5(self::$accountPassword),
					'accNum' => self::$accountNumber,
				], JSON_UNESCAPED_UNICODE),
			];
			break;
		case 'ecloud':
			# 使用企业云ID
			self::$options = [
				'appkey' => (isset($options['appkey'])) ? $options['appkey'] : '',
				'orgid' => (isset($options['orgid'])) ? $options['orgid'] : '',
				'appsecret' => (isset($options['appsecret'])) ? $options['appsecret'] : '',
			];
			self::$authUri = '/collaborationapp/GetAnonymousTPlusToken?IsFree=1';
			self::$authParam = [];
			break;
		default:
			die('无效的认证模式');
			break;
		}
	}

	/**
	 * post请求
	 * @param String $apiUrl             API地址[必填]
	 * @param Array $authorizationHeader 授权报头[必填]
	 * @param Array $apiParam            API参数[选填]
	 * @return Object $responseData      服务端响应数据
	 */
	public static function post($authorizationHeader, &$responseData, $apiParam = []) {
		try {
			$httpClient = new Client();
			$apiParam = [
				'headers' => $authorizationHeader,
				'form_params' => $apiParam,
			];
			$responseData = $httpClient->request('POST', self::$apiUrl, $apiParam)->getBody()->getContents();
			$responseData = json_decode($responseData);
		} catch (RequestException $requestException) {
			$responseData = json_decode($requestException->getResponse()->getBody()->getContents());
		}
	}

	/**
	 * 创建授权报头
	 * @return Array $$authorizationHeader 授权报头
	 */
	public static function createAuthorizationHeader(&$authorizationHeader) {
		if (empty(self::$accessToken)) {
			# 签名1
			self::createSignedTokenA(self::$options, $signedToken);
		} else {
			$customParams['access_token'] = self::$accessToken;
			self::createSignedTokenB(self::$options, $signedToken, $customParams);
		}
		$authorizationHeaderMaps = [
			'appKey' => self::$options['appkey'],
			'authInfo' => $signedToken,
			'orgId' => self::$options['orgid'],
		];
		$authorization = base64_encode(stripslashes(json_encode($authorizationHeaderMaps)));
		$authorizationHeader = [
			'Content-type' => 'application/x-www-form-urlencoded;charset=utf-8',
			'Authorization' => $authorization,
		];
	}

	/**
	 * 创建访问令牌
	 * @param Array $authorizationHeader 授权报头
	 * @return None
	 */
	public static function createAccessToken($authorizationHeader) {
		self::$apiUrl = self::API_HOST . self::API_MODULE . self::API_VERSION . self::$authUri;
		self::post($authorizationHeader, $responseData, self::$authParam);
		self::$accessToken = (isset($responseData->data->StatusCode) and $responseData->data->StatusCode == '400') ? '' : $responseData->access_token;
	}

	/**
	 * 获取毫秒数
	 * @return Float $millisecond 1970-01-01 00:00:00至今的毫秒数
	 */
	public static function getMillisecond(&$millisecond) {
		list($msec, $sec) = explode(' ', microtime());
		$millisecond = (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
	}

	/**
	 * 签名1
	 * @param  Array   $signatureParam 参与签名的参数[必填]
	 * @return String  $signedToken    签名后的令牌
	 *
	 */
	public static function createSignedTokenA($signatureParam, &$signedToken) {
		self::getMillisecond($millisecond);
		$expiryTime = $millisecond + self::EXPIRY_MILLISECOND;
		$payload = [
			'sub' => 'cjt',
			'exp' => $expiryTime,
			'datas' => md5(json_encode($signatureParam)),
		];
		$signedToken = JWT::encode($payload, self::$privateRSAKey, 'RS256');
	}

	/**
	 * 签名2
	 * @param  Array   $signatureParam 签名参数
	 * @param  File    $pemFile        私钥PEM文件
	 * @param  Array   $customParams   签名附加参数
	 * @return String  $signedToken    签名后的令牌
	 */
	public static function createSignedTokenB($signatureParam, &$signedToken, $customParams = []) {
		self::getMillisecond($millisecond);
		$expiryTime = $millisecond + self::EXPIRY_MILLISECOND;
		$payload = [
			'sub' => 'chanjet',
			'exp' => $expiryTime,
			'datas' => md5(json_encode($signatureParam)),
		];
		$payload = array_merge($payload, $customParams);
		$signedToken = JWT::encode($payload, self::$privateRSAKey, 'RS256');
	}

	/**
	 * 设置请求域名
	 * @param String $serviceName 服务名称
	 */
	public static function setAPIUrl($serviceName) {
		self::$apiUrl = self::API_HOST . self::API_MODULE . self::API_VERSION . $serviceName;
	}

	/**
	 * 设置查询字段
	 * @param Array $selectFields 要查询的字段名称
	 */
	public static function setFields($selectFields) {
		self::$selectFields = implode(',', $selectFields);
	}

}
?>
