<?php
/**
 * RESTful请求客户端
 * 
 * @author izp_php_ruyi
 * @version 1.0
 * @uses  基于ThinkPHP风格，需实例化调用。 
 *
 */
class RestFulClient {
	
	protected $_ch = null;
	private $_url = '';
	private $_method = 'GET';
	private $_data = null;
	private $_options = array ();
	
	//用于迭代器返回的数组
	private $_arr = array ();
	
	private $_res;
	
	/**
	 * 初始化
	 *
	 * @param array $options 其它选项|超时|查询字符串分割符
	 */
	public function __construct($options = array('timeout'=>3, 'split'=>'/')) {
		if (! function_exists ( 'curl_init' ))
			die ( 'RestFulClint function __construct() : function curl_init not exists' );
		if (! is_array ( $options ))
			die ( 'RestFulClint function __construct() : param $options is not array()!' );
		$this->_options = $options;
	}
	
	/**
	 * 设置配置可选参数
	 *
	 * @param array $options
	 */
	public function setOptions($options) {
		if (! is_array ( $options ))
			die ( 'RestFulClint function setOptions() : param $options is not array()!' );
		$this->_options = $options;
	}
	
	/**
	 * 发送请求
	 *
	 * @param string $url 请求链接
	 * @param string $method HTTP提交方式
	 * @param array $data 提交数据
	 * @param array $options 其它选项|超时
	 * @return array res 结果数组
	 */
	public function send($url, $method = 'get', $data = null, $options = array()) {
		//URL校验
		if (! $url)
			die ( "RestFulClint url is empty!" );
		$this->_url = $url;
		
		//提交方法校验
		$method = strtolower ( $method );
		if (! in_array ( $method, array ('post', 'get', 'head', 'delete', 'put', 'head', 'options' ) ))
			die ( "RestFulClint method is not found! method={$method}" );
		$this->_method = strtoupper ( $method );
		
		//提交数据校验
		$this->_data = $data;
		
		//校验选项参数
		if (! is_array ( $options ))
			die ( 'RestFulClint param $options is not array()!' );
		$this->_options = $options ? $options : $this->_options;
		//选项参数—超时校验，默认值为3秒
		$timeout = isset ( $options ['timeout'] ) ? intval ( $options ['timeout'] ) : 0;
		$timeout = $timeout ? $timeout : 3;
		//链接字符串分割符
		$split = isset($options ['split']) && $options ['split'] ? $options ['split'] : '/';
		
		//初始化CURL
		if (! function_exists ( 'curl_init' ))
			die ( "RestFulClint CURL is not set up!" );
		$this->_ch = curl_init ();
		
		curl_setopt ( $this->_ch, CURLOPT_URL, $method == 'post' ? $url : $this->buildUrl ( $url, $data, $split ) ); //进行URL链接设置
		curl_setopt ( $this->_ch, CURLOPT_CONNECTTIMEOUT, $timeout ); //设置请求链接超时时间
		curl_setopt ( $this->_ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 ); //采用1.1版的HTTP协议
		curl_setopt ( $this->_ch, CURLOPT_RETURNTRANSFER, true ); //把结果返回，而非直接输出
		curl_setopt ( $this->_ch, CURLOPT_FORBID_REUSE, true ); //处理完后，关闭连接，释放资源
		$method == 'post' ? curl_setopt ( $this->_ch, CURLOPT_POSTFIELDS, $this->formatPostData ( $this->_data ) ) : ''; //传递参数
		curl_setopt ( $this->_ch, CURLOPT_CUSTOMREQUEST, $this->_method ); //设置HTTP的提交方式
		

		curl_setopt ( $this->_ch, CURLOPT_FAILONERROR, true ); //是否抛出错误代码
		

		$this->_res ['http_content'] = curl_exec ( $this->_ch );
		$this->_res ['http_error'] = curl_getinfo ( $this->_ch, CURLINFO_HTTP_CODE );
		$this->_res ['http_error'] = $this->_res ['http_error'] ? $this->_res ['http_error'] : curl_errno ( $this->_ch );
		$this->_res ['http_msg'] = curl_error ( $this->_ch );
		
		return $this->_res;
	}
	
	/**
	 * 获取响应的数据内容
	 *
	 * @param string $k res的键值，其中有error|msg|content；或者all
	 * @return value
	 */
	public function getRes($k = 'http_content') {
		return strtolower ( $k ) == 'all' ? $this->_res : $this->_res [$k];
	}
	
	/**
	 * 以get方式请求，（ Read ）得到一个资源表述。
	 *
	 * @param string $url
	 * @param array $data
	 * @param array $options
	 * @return array
	 */
	public function get($url, $data = null, $options = array()) {
		return $this->send ( $url, 'get', $data, $options );
	}
	
	/**
	 * 以post方式请求，（Create）用于创建多个新资源或对资源进行多种其他变更。
	 *
	 * @param string $url
	 * @param array $data
	 * @param array $options
	 * @return array
	 */
	public function post($url, $data = null, $options = array()) {
		return $this->send ( $url, 'post', $data, $options );
	}
	
	/**
	 * 以put方式请求，（Update）建立或更新一个资源。
	 *
	 * @param string $url
	 * @param array $data
	 * @param array $options
	 * @return array
	 */
	public function put($url, $data = null, $options = array()) {
		return $this->send ( $url, 'put', $data, $options );
	}
	
	/**
	 * 以delete方式请求，（Delete）删除资源
	 *
	 * @param string $url
	 * @param array $data
	 * @param array $options
	 * @return array
	 */
	public function delete($url, $data = null, $options = array()) {
		return $this->send ( $url, 'delete', $data, $options );
	}
	
	/**
	 * 按传入数组合并成ThinkPHP格式的链接字符串的地址
	 *
	 * @param string $url
	 * @param array $data {k=>v, k1=>{ka=>vb}}
	 * @param string $split
	 * @return string
	 */
	public function buildUrl($url, $data, $split = '/') {
		$split = $split ? $split : '/';
		$url .= substr ( $url, - 1 ) == $split ? '' : $split; //自动在后面补分割符
		if (! is_array ( $data ))
			return $url . $data;
		foreach ( $data as $k => $v ) {
			$k = trim ( $k );
			$url .= $k . $split;
			if (is_array ( $v )) {
				$strArray = array ();
				$assoc = false;
				//二维处理
				foreach ( $v as $k1 => $v1 ) {
					$k1 = trim ( $k1 );
					//如果存在三维
					if (is_array ( $v1 )) {
						$assoc = true;
						$strArray [] = $k1 . ':' . implode ( ',', array_values ( $v1 ) );
					} else {
						$assoc = false;
						//如果键名为数字，则不加入键名
						$strArray [] = (is_numeric ( $k1 ) ? '' : ($k1 . ':')) . $v1;
					}
				}
				$url .= (is_array ( $strArray ) ? implode ( ($assoc ? ';' : ','), $strArray ) : $v) . $split;
			}
		}
		return $url;
	}
	
	/**
	 * 获取传入的url
	 *
	 * @param boolean $flag 默认为true获取组合以后的url，false为获取传入的url
	 * @return string
	 */
	public function getUrl($flag = true) {
		return $flag && $this->_method != 'POST' ? $this->buildUrl ( $this->_url, $this->_data, $this->_options ['split'] ) : $this->_url;
	}
	
	/**
	 * 当传入数组为二维数组时进行http的post提交格式化
	 * 注意：由于HTTP提交的过程中，只支持二维数组，这里只是进行了二维数组的一个转换。
	 *
	 * @param array||string $data 
	 * @return array||string
	 */
	public function formatPostData($data) {
		$this->_arr = array ();
		$this->formatPostDataIterator ( $data );
		return $this->_arr;
	}
	
	/**
	 * post参数迭代器
	 *
	 * @param array $data
	 * @param string $tk
	 * @return array
	 */
	private function formatPostDataIterator($data, $tk = '') {
		if (is_array ( $data )) {
			foreach ( $data as $k => $v ) {
				$k = $tk ? $tk . "[$k]" : $k;
				$this->formatPostDataIterator ( $v, $k );
			}
		} else {
			$this->_arr [$tk] = $data;
		}
		return $this->_arr;
	}
	
	/**
	 * 析构函数，用于关闭curl链接
	 *
	 */
	public function __destruct() {
		curl_close ( $this->_ch );
	}

}
/**
 * 
 * @author izp_php_ruyi
 * @deprecated 不赞成使用HEAD方式请求，经测试，请求时间为15秒。。。
 * @example 使用示例
 * $rfc = new RestFulClint();
 * 
 * //指定发放请求，返回格式为array('http_error','http_msg','http_content')
 * $rfc->send($url, $method='get', $data=null, $options = array('timeout'=>3, 'split'=>'/');
 * 
 * //get方法请求，当data传入数组时将自动合并成字符串的URL
 * $rfc->get($url, $data=null, $options = array('timeout'=>3, 'split'=>'/');
 * 
 * //post方法请求
 * $rfc->post($url, $data=null, $options = array('timeout'=>3, 'split'=>'/');
 * 注：
 * $data将转换成post方式的格式数组提交
 * 例如：
 * 1.二维数组
 * $data = array( 'birthday' => array('year' => '2012年', 'month' => '3月', 'day' => '21日'))
 * 转换成
 * $_POST[birthday[year]] = '2012年',
 * $_POST[birthday[month]] = '3月',
 * $_POST[birthday[day]] = '21日',
 * 2.多维数组
 * $data = array(
 * 'birthday' => array(
 * 'year' => array('CN'=>'2012年','EN'=>'2012'),
 * 'month' => array('CN'=>'3月','EN'=>'3'),
 * 'day' => array('CN'=>'21日','EN'=>'21')
 * )
 * )
 * 转换成
 * $_POST[birthday[year][CN]] = '2012年',
 * $_POST[birthday[month][CN]] = '3月',
 * $_POST[birthday[day][CN]] = '21日',
 * 
 * //put方法请求，当data传入数组时将自动合并成字符串的URL
 * $rfc->put($url, $data=null, $options = array('timeout'=>3, 'split'=>'/');
 * 
 * //delete方法请求，当data传入数组时将自动合并成字符串的URL
 * $rfc->delete($url, $data=null, $options = array('timeout'=>3, 'split'=>'/');
 * 
 * 以上返回值的格式都为array('error'=>'错误数值', 'msg'=>'错误消息|其它提示信息', 'content'=>'返回的真正数据或信息|body content')
 * 
 * 如果需要直接取内容值使用：$rfc->getRes('http_error'|'http_msg'|'http_content默认'|'all全部');
 * 
 * 注:
 * 1.$data可支持三维维数组，转成字符串后第二维的数组用分号分隔，三维数组用逗号分隔。
 * 示例：
 * array('name'=>'ruyi', 'email' => 'gd0ruyi@163.com', 'birthday'=>array('year'=>'1982','month'=>'05','day'=>'12'),'area'=>array('x', 'y'),'group'=>array('k1'=>'a', 'b', 'k3'=>array('v3_1','v3_2')))
 * http://ruyi.datacenter/index.php/RestTest/index/id/1/sex/men,woman,secrecy/name/email/birthday/year:1982,month:05,day:12/area/x,y/group/k1:a;b;k3:v3_1,v3_2/
 * 
 * 2.url可以支持三维维数组。
 * 示例：
 * http://ruyi.datacenter/index.php/RestTest/index/id/1/sex/men,woman,secrecy/name/email/birthday/year:1982,month:05,day:12/area/x,y/group/k1:a;b;k3:v3_1,v3_2/
 * 
 * 3.支持url结束符补全功能，例如： http://ruyi.datacenter/index.php/RestTest/id/1 将自动补全为 http://ruyi.datacenter/index.php/RestTest/id/1/
 *
 */
?>