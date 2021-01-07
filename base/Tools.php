<?php
namespace bricksasp\base;

use Yii;

/**
 * 工具函数
 */
class Tools extends \yii\helpers\ArrayHelper {
	/**
	 * 返回当前的毫秒时间戳
	 */
	public static function mstime() {
		list($tmp1, $tmp2) = explode(' ', microtime());
		return sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);
	}

	/**
	 * 生成编号
	 */
	public static function get_sn($type = 0) {
		switch ($type) {
		case 1: //订单编号
			$str = $type . substr(static::mstime() . rand(0, 9), 1);
			break;
		case 2: //支付单编号
			$str = $type . substr(static::mstime() . rand(0, 9), 1);
			break;
		case 3: //商品编号
			$str = 'G' . substr(static::mstime() . rand(0, 5), 1);
			break;
		case 4: //单品编号
			$str = 'P' . substr(static::mstime() . rand(0, 5), 1);
			break;
		case 5: //售后单编号
			$str = $type . substr(static::mstime() . rand(0, 9), 1);
			break;
		case 6: //退款单编号
			$str = $type . substr(static::mstime() . rand(0, 9), 1);
			break;
		case 7: //退货单编号
			$str = $type . substr(static::mstime() . rand(0, 9), 1);
			break;
		case 8: //发货单编号
			$str = $type . substr(static::mstime() . rand(0, 9), 1);
			break;
		case 9: //提货单号
			$str = 'T' . $type . substr(static::mstime() . rand(0, 5), 1);
			break;
		case 10: //文件编号
			$str = 'F' . $type . substr(static::mstime() . rand(0, 5), 1);
		case 11: //单品条码
			$str = $type . substr(static::mstime() . rand(0, 5), 1);
			break;
        case 12: //提款编号
            $str = $type . substr(static::mstime() . rand(0, 9), 1);
            break;
        case 13: //流水号
            $str = $type . substr(static::mstime() . rand(0, 9), 1);
            break;
		default:
			$str = substr(static::mstime() . rand(0, 9), 1);
		}
		return $str;
	}

	/**
	 * 格式化数据化手机号码
	 */
	public static function format_number($number, $type = 1) {
		switch ($type) {
		case 1: //两位小数
			return sprintf('%.2f', $number);
			break;
		case 2: //手机号码
			return substr($number, 0, 5) . '****' . substr($number, 9, 2);
			break;

		default:
			return false;
			break;
		}
	}

	/**
	 * 判断是否手机号
	 * @param $mobile
	 * @return bool
	 */
	public static function is_mobile($mobile = '') {
		if (preg_match('/^1[3456789]{1}\d{9}$/', $mobile)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 判断是否固话
	 * @param $tel
	 * @return bool
	 */
	public static function is_telephone($tel = '') {
		if (preg_match('/^([0-9]{3,4}-)?[0-9]{7,8}$/', $tel)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 秒转换为天，小时，分钟
	 * @param int $second
	 * @return string
	 */
	public static function secondConversion($second = 0) {
		$newtime = '';
		$d = floor($second / (3600 * 24));
		$h = floor(($second % (3600 * 24)) / 3600);
		$m = floor((($second % (3600 * 24)) % 3600) / 60);
		if ($d > '0') {
			if ($h == '0' && $m == '0') {
				$newtime = $d . '天';
			} else {
				$newtime = $d . '天' . $h . '小时' . $m . '分';
			}
		} else {
			if ($h != '0') {
				if ($m == '0') {
					$newtime = $h . '小时';
				} else {
					$newtime = $h . '小时' . $m . '分';
				}
			} else {
				$newtime = $m . '分';
			}
		}
		return $newtime;
	}

	/**
	 * 获取最近天数的日期和数据
	 * @param $day
	 * @param $data
	 * @return array
	 */
	public static function get_lately_days($day, $data) {
		$day = $day - 1;
		$days = [];
		$d = [];
		for ($i = $day; $i >= 0; $i--) {
			$d[] = date('d', strtotime('-' . $i . ' day')) . '日';
			$days[date('Y-m-d', strtotime('-' . $i . ' day'))] = 0;
		}
		foreach ($data as $v) {
			$days[$v['day']] = $v['nums'];
		}
		$new = [];
		foreach ($days as $v) {
			$new[] = $v;
		}
		return ['day' => $d, 'data' => $new];
	}

	public static function findChild(&$arr, $id, $relation) {
		$childs = [];
		foreach ($arr as $k => $v) {
			if ($v[$relation] == $id) {
				$childs[] = $v;
			}

		}
		return $childs;
	}

	/**
	 * 数组tree结构
	 * @param  [array] $data
	 */
	public static function build_tree($data, $root_id = 0, $relation = 'parent_id', $key = 'id', $childname = 'children') {
		$childs = static::findChild($data, $root_id, $relation);
		if (empty($childs)) {
			return null;
		}

		foreach ($childs as $k => $v) {
			$rescurTree = static::build_tree($data, $v[$key], $relation, $key);
			if (null != $rescurTree) {
				$childs[$k][$childname] = $rescurTree;
			}

		}
		return $childs;
	}
	/**
	 * 读取所有目录
	 */
	public static function read_all_dir($path, $deep = 0) {
		$result = [];
		$handle = opendir($path); //读资源
		if ($handle) {
			$file = readdir($handle);
			while (($file = readdir($handle)) !== false) {
				if ($file != '.' && $file != '..' && !is_file($file)) {
					$cur_path = $path . DIRECTORY_SEPARATOR . $file;

					$result[] = $cur_path;
				}
			}
			closedir($handle);
		}
		return $result;
	}

	/**
	 * 读取所有文件
	 */
	public static function read_all_file($path) {
		$result = [];
		$handle = opendir($path); //读资源
		if ($handle) {
			$file = readdir($handle);
			while (($file = readdir($handle)) !== false) {
				if ($file != '.' && $file != '..') {
					$cur_path = $path . DIRECTORY_SEPARATOR . $file;
					if (is_dir($cur_path)) {
						//判断是否为目录，递归读取文件
						$result = array_merge($result, self::read_all_dir($cur_path));
					} else {
						$result[] = $cur_path;
					}
				}
			}
			closedir($handle);
		}
		return $result;
	}

	/**
	 * 下载远程文件
	 * @param  string $url
	 * @param  [type] $name
	 * @param  string $path
	 * @return string
	 */
	public static function download_file($url, $name, $path = '') {

		$file = $path . '/' . $name;
		if (!file_exists($path)) {
			self::make_dir($path);
		}
		ob_start(); //打开输出
		try {
			@readfile($url); //输出内容
		} catch (Exception $e) {
			return false;
		}
		$content = ob_get_contents(); //得到浏览器输出
		ob_end_clean(); //清除输出并关闭
		file_put_contents($file, $content);
		return $file;
	}

	/**
	 * 递归生成目录
	 * @param  [type] $dir [description]
	 * @return [type]      [description]
	 */
	public static function make_dir($dir) {
		return is_dir($dir) or self::make_dir(dirname($dir)) and mkdir($dir, 0777);
	}

	/**
	 * 删除文件
	 * @param  string $path 文件路径
	 * @return bool
	 */
	public static function deleteFile($path) {
		if (file_exists($path)) {
			return @unlink($path);
		}
		return false;
	}

	/**
	 * tcp 通信
	 * @param  string $host 地址
	 * @param  string $cmd  [description]
	 * @param  [type] $data [description]
	 * @param  array  $ext  [description]
	 * @param  string $eof  结束标记
	 * @return [type]       [description]
	 */
	public static function srequest(string $host, string $cmd, $data, $ext = [], $eof = '\r\n\r\n') {
		$fp = stream_socket_client($host, $errno, $errstr);
		if (!$fp) {
			throw new Exception("stream_socket_client fail errno={$errno} errstr={$errstr}");
		}

		$req = [
			'cmd' => $cmd,
			'data' => $data,
			'ext' => $ext,
		];
		// $req = [
		//     'method'  => $cmd,
		//     'params' => $data,
		//     'ext' => $ext,
		// ];

		$data = json_encode($req) . $eof;
		fwrite($fp, $data);

		$result = '';
		while (!feof($fp)) {
			$tmp = stream_socket_recvfrom($fp, 1024);
			$pos = strpos($tmp, $eof);
			if ($pos !== false) {
				$result .= substr($tmp, 0, $pos);
				break;
			} else {
				$result .= $tmp;
			}
		}

		fclose($fp);
		return json_decode($result, true);
	}

	/**
	 * 数组字段格式化
	 * @param  array  $data
	 * @param  array  $rule ['filed'=>['json_decode',['###',true]]]
	 * @param  int  $fg 一维二维
	 * @return array
	 * @author  <[<bricksasp 649909457@qq.com>]>
	 */
	public static function format_array($data = [], $rule = [], $fg = 1) {
		if ($fg == 1) {
			$res[] = $data;
		} else {
			$res = $data;
		}

		$res = array_map(function ($item) use ($rule) {
			foreach ($rule as $field => $v) {
				if (empty($v[2])) {
					$ags = str_replace('###', $item[$field], $v[1]);
				} else {
					foreach ($v[1] as &$vv) {
						if (is_array($vv) && isset($item[$field])) {
							$vv = str_replace('###', $item[$field], $vv);
						}

					}
					$ags = $v[1];
				}
				if ((isset($ags[1][1]) && $ags[1][1] == '###') || ($v[0] == 'implode' && $field == 'file_url' && count(array_filter(end($ags)))<2)) {
					$item[$field] = '';
				}else{
					$item[$field] = call_user_func_array($v[0], $ags);
				}
			}
			return $item;
		}, $res);
		return $fg == 1 ? $res[0] : $res;
	}

	/**
	 * 实现二维数组的笛卡尔积组合
	 * $input 要进行笛卡尔积的二维数组
	 * $callback 自定义拼接格式 参数 $p1 $p2 格式[key,val]
	 * $output 最终实现的笛卡尔积组合,可不写 默认格式 1,2,3
	 * @return array
	 * @author  <[<bricksasp 649909457@qq.com>]>
	 */
	public static function cartesian($input, $callback = null, $output = []) {
		//去除第一个元素
		$first = array_shift($input);
		//判断是否是第一次进行拼接
		if (count($output) > 1) {
			foreach ($output as $k => $v) {
				foreach ($first as $k2 => $v2) {
					//可根据具体需求进行变更
					if ($callback == null) {
						$output2[] = $v . ',' . $v2;
					} else {
						$output2[] = $callback([$k, $v], [$k2, $v2]);
					}
				}
			}
		} else {
			foreach ($first as $k => $v) {
				//可根据具体需求进行变更
				if ($callback == null) {
					$output2[] = $v;
				} else {
					$output2[] = $callback([$k, $v]);
				}
			}
		}

		//递归进行拼接
		if (count($input) > 0) {
			$output2 = self::cartesian($input, $callback, $output2);
		}
		//返回最终笛卡尔积
		return $output2;
	}

	/**
	 * 使用异常中断操作
	 */
	public static function breakOff($msg, $status = 200) {
		throw new \yii\web\HttpException($status, $msg);
	}

	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
	 * @return mixed
	 */
	public static function client_ip($type = 0, $adv = false) {
		$type = $type ? 1 : 0;
		static $ip = NULL;
		if ($ip !== NULL) {
			return $ip[$type];
		}

		if ($adv) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				$pos = array_search('unknown', $arr);
				if (false !== $pos) {
					unset($arr[$pos]);
				}

				$ip = trim($arr[0]);
			} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (isset($_SERVER['REMOTE_ADDR'])) {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$long = sprintf('%u', ip2long($ip));
		$ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
		return $ip[$type];
	}

	/**
	 * 判断微信端
	 */
	public static function is_wechat()
	{
	    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
	        return true;
	    }  
        return false;
	}

	/**
	 * 汉子拼音首字母
	 * @param  [type] $s0 [description]
	 * @return [type]     [description]
	 */
	public static function zhFirstChar($str){
        if (empty($str)) {
            return '';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;

	    if($asc >= -20319 and $asc <= -20284) return 'A';
	    if($asc >= -20283 and $asc <= -19776) return 'B';
	    if($asc >= -19775 and $asc <= -19219) return 'C';
	    if($asc >= -19218 and $asc <= -18711) return 'D';
	    if($asc >= -18710 and $asc <= -18527) return 'E';
	    if($asc >= -18526 and $asc <= -18240) return 'F';
	    if($asc >= -18239 and $asc <= -17923) return 'G';
	    if($asc >= -17922 and $asc <= -17418) return 'I';
	    if($asc >= -17417 and $asc <= -16475) return 'J';
	    if($asc >= -16474 and $asc <= -16213) return 'K';
	    if($asc >= -16212 and $asc <= -15641) return 'L';
	    if($asc >= -15640 and $asc <= -15166) return 'M';
	    if($asc >= -15165 and $asc <= -14923) return 'N';
	    if($asc >= -14922 and $asc <= -14915) return 'O';
	    if($asc >= -14914 and $asc <= -14631) return 'P';
	    if($asc >= -14630 and $asc <= -14150) return 'Q';
	    if($asc >= -14149 and $asc <= -14091) return 'R';
	    if($asc >= -14090 and $asc <= -13319) return 'S';
	    if($asc >= -13318 and $asc <= -12839) return 'T';
	    if($asc >= -12838 and $asc <= -12557) return 'W';
	    if($asc >= -12556 and $asc <= -11848) return 'X';
	    if($asc >= -11847 and $asc <= -11056) return 'Y';
	    if($asc >= -11055 and $asc <= -10247) return 'Z';
	    return null;
	}

	/**
	 * 中文字符首字母
	 * @param  [type] $zh [description]
	 * @return [type]     [description]
	 */
	public static function zhPinYin($zh){
	    $ret = '';
	    for($i = 0; $i < strlen($zh); $i++){
            $ret .= static::zhFirstChar(mb_substr($zh,$i,1));
	    }
	    return strtolower($ret);
	}

	/**
	 * @param $lat1
	 * @param $lon1
	 * @param $lat2
	 * @param $lon2
	 * @param float $radius  星球半径 KM
	 * @return float
	 */
	public static function distance($lat1, $lon1, $lat2,$lon2,$radius = 6378.137)
	{
	    $rad = floatval(M_PI / 180.0);

	    $lat1 = floatval($lat1) * $rad;
	    $lon1 = floatval($lon1) * $rad;
	    $lat2 = floatval($lat2) * $rad;
	    $lon2 = floatval($lon2) * $rad;

	    $theta = $lon2 - $lon1;

	    $dist = acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($theta));

	    if ($dist < 0 ) {
	        $dist += M_PI;
	    }
	    return $dist * $radius;
	}

    /**
     * @param int $randLength 长度
     * @param int $addtime 是否加入当前时间戳
     * @param int $includenumber 是否包含数字
     * @return string
     */
    public static function random_str($randLength = 8, $includenumber = 1)
    {
        if ($includenumber) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
        } else {
            $chars = 'abcdefghijklmnopqrstuvwxyz';
        }
        $len = strlen($chars);
        $randStr = '';
        for ($i = 0; $i < $randLength; $i++) {
            $randStr .= $chars[mt_rand(0, $len - 1)];
        }

        return $randStr;

    }
}
