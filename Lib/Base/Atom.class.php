<?php
/**
 * 原生态框架基类
 * 
 * @author ruyi@izptec
 */
class Atom {
	
	//项目分组的信息
	protected static $_app_group_list = array ();
	
	/**
	 * 基类初始化方法
	 * 
	 * @author ruyi@izptec
	 * @return boolean
	 */
	static public function start() {
		//记录该类起始时间
		setKeepTimer ( 'atom class' );
		
		// 设定错误和异常处理
		register_shutdown_function ( array ('Atom', 'fatalError' ) );
		set_error_handler ( array ('Atom', 'appError' ) );
		set_exception_handler ( array ('Atom', 'appException' ) );
		// 注册AUTOLOAD方法
		spl_autoload_register ( array ('Atom', 'autoload' ) );
		
		// 设置系统时区
		date_default_timezone_set ( C ( 'DEFAULT_TIMEZONE' ) );
		
		//定义系统常量
		define ( 'NOW_TIME', $_SERVER ['REQUEST_TIME'] );
		define ( 'REQUEST_METHOD', $_SERVER ['REQUEST_METHOD'] );
		define ( 'IS_GET', REQUEST_METHOD == 'GET' ? true : false );
		define ( 'IS_POST', REQUEST_METHOD == 'POST' ? true : false );
		define ( 'IS_PUT', REQUEST_METHOD == 'PUT' ? true : false );
		define ( 'IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false );
		define ( 'IS_AJAX', ((isset ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && strtolower ( $_SERVER ['HTTP_X_REQUESTED_WITH'] == 'xmlhttprequest' )) || ! empty ( $_POST [C ( 'VAR_AJAX_SUBMIT' )] ) || ! empty ( $_GET [C ( 'VAR_AJAX_SUBMIT' )] )) ? true : false );
		
		//初始化项目组的目录名称
		self::initGroupName ();
		
		//路由解析
		self::routeParse ();
		
		//路由调度
		self::routeDispatcher ();
		return;
	}
	
	/**
	 * 初始化项目组的目录名称
	 * 
	 * @author ruyi@izptec
	 * @return array
	 */
	static public function initGroupName() {
		self::$_app_group_list = C ( 'APP_GROUP_LIST' );
		if (is_string ( self::$_app_group_list )) {
			self::$_app_group_list = explode ( ',', self::$_app_group_list );
		}
		if (! empty ( self::$_app_group_list ) && is_array ( self::$_app_group_list )) {
			foreach ( self::$_app_group_list as $k => $v ) {
				self::$_app_group_list [$k] = ucfirst ( strtolower ( $v ) );
			}
		}
		return C ( 'APP_GROUP_LIST', self::$_app_group_list );
	}
	
	/**
	 * 路由解析
	 * 
	 * @author ruyi@izptec
	 * @return voide
	 */
	public static function routeParse() {
		$group_name = strtolower ( C ( 'DEFAULT_GROUP' ) );
		$module_name = ucfirst ( strtolower ( C ( 'DEFAULT_MODULE' ) ) );
		$action_name = C ( 'DEFAULT_ACTION' );
		//PATH_INFO方式调度
		if (! empty ( $_SERVER ['PATH_INFO'] )) {
			//除去开始符
			$path_info = substr ( $_SERVER ['PATH_INFO'], 1 );
			//除去结束符
			$path_info = substr ( $path_info, - 1, 1 ) == C ( 'URL_PATHINFO_DEPR' ) ? substr ( $path_info, 0, strlen ( $path_info ) - 1 ) : $path_info;
			//开始拆分
			$path_info = explode ( C ( 'URL_PATHINFO_DEPR' ), $path_info );
			
			$temp_group_name = ucfirst ( strtolower ( strip_tags ( $path_info [0] ) ) );
			
			//优先获取项目组的名称
			if ($temp_group_name && self::$_app_group_list && in_array ( $temp_group_name, self::$_app_group_list )) {
				$group_name = $temp_group_name;
				$module_name = ucfirst ( strtolower ( strip_tags ( $path_info [1] ) ) );
				$action_name = ucfirst ( strtolower ( strip_tags ( $path_info [2] ) ) );
				unset ( $path_info [0] );
				unset ( $path_info [1] );
				unset ( $path_info [2] );
			} else {
				$module_name = ucfirst ( strtolower ( strip_tags ( $path_info [0] ) ) );
				$action_name = ucfirst ( strtolower ( strip_tags ( $path_info [1] ) ) );
				unset ( $path_info [0] );
				unset ( $path_info [1] );
			}
			
			//获取参数
			$i = 0;
			foreach ( $path_info as $key => $value ) {
				$i ++;
				if ($i % 2) {
					$_GET [$value] = $path_info [$key + 1];
				}
			}
		}
		
		//连接字符串
		if (! empty ( $_SERVER ['QUERY_STRING'] )) {
			$group_name = isset ( $_GET [C ( 'VAR_GROUP' )] ) ? $_GET [C ( 'VAR_GROUP' )] : $group_name;
			$module_name = isset ( $_GET [C ( 'VAR_MODULE' )] ) ? $_GET [C ( 'VAR_MODULE' )] : $module_name;
			$action_name = isset ( $_GET [C ( 'VAR_ACTION' )] ) ? $_GET [C ( 'VAR_ACTION' )] : $action_name;
		}
		
		//保证$_REQUEST正常取值
		$_REQUEST = array_merge ( $_POST, $_GET );
		
		//定义常量
		defined ( 'GROUP_NAME' ) or define ( 'GROUP_NAME', $group_name );
		defined ( 'MODULE_NAME' ) or define ( 'MODULE_NAME', $module_name );
		defined ( 'ACTION_NAME' ) or define ( 'ACTION_NAME', $action_name );
	}
	
	/**
	 * 路由调度
	 * 
	 * @return null
	 */
	public static function routeDispatcher() {
		if (! defined ( 'MODULE_NAME' )) {
			throw_exception ( 'MODULE_NAME is Empty!' );
		}
		if (! defined ( 'ACTION_NAME' )) {
			throw_exception ( 'ACTION_NAME is Empty!' );
		}
		try {
			if (! preg_match ( '/^[A-Za-z](\w)*$/', ACTION_NAME )) {
				//非法操作
				throw new ReflectionException ();
				//throw_exception ( 'ACTION_NAME is error name!' );
				;
			}
			$method = new ReflectionMethod ( MODULE_NAME, ACTION_NAME );
			if ($method->isPublic ()) {
				$class = new ReflectionClass ( MODULE_NAME );
				//前置操作
				if ($class->hasMethod ( '_before_' . ACTION_NAME )) {
					$before = $class->getMethod ( '_before_' . ACTION_NAME );
					if ($before->isPublic ()) {
						$before->invoke ( MODULE_NAME );
					}
				}
				$method->invoke ( MODULE_NAME );
				//后置操作
				if ($class->hasMethod ( '_after_' . ACTION_NAME )) {
					$after = $class->getMethod ( '_after_' . ACTION_NAME );
					if ($after->isPublic ()) {
						$after->invoke ( MODULE_NAME );
					}
				}
			} else {
				//操作方法不是Public 抛出异常
				throw new ReflectionException ();
				//throw_exception ( 'ACTION_NAME is not public!' );
				;
			}
		} catch ( ReflectionException $e ) {
			//方法调用发生异常后 引导到__call方法处理
			$method = new ReflectionMethod ( MODULE_NAME, '__call' );
			$method->invokeArgs ( MODULE_NAME, array (ACTION_NAME, '' ) );
		}
		return;
	}
	
	/**
	 * 自动加载
	 */
	public static function autoload($class) {
		if (substr ( $class, - 5 ) == "Model") {
			if ((defined ( 'GROUP_NAME' ) && require_cache ( LIB_PATH . 'Model/' . GROUP_NAME . '/' . $class . '.class.php' )) || require_cache ( LIB_PATH . 'Model/' . $class . '.class.php' ) || require_cache ( EXTEND_PATH . 'Model/' . $class . '.class.php' )) {
				return;
			}
		} elseif (substr ( $class, - 6 ) == "Action") {
			if ((defined ( 'GROUP_NAME' ) && require_cache ( LIB_PATH . 'Action/' . GROUP_NAME . '/' . $class . '.class.php' )) || require_cache ( LIB_PATH . 'Action/' . $class . '.class.php' ) || require_cache ( EXTEND_PATH . 'Action/' . $class . '.class.php' )) {
				return;
			}
		}
		//根据自动加载路径设置进行尝试搜索
		if (C ( 'APP_AUTOLOAD_PATH' )) {
			$paths = explode ( ',', C ( 'APP_AUTOLOAD_PATH' ) );
			foreach ( $paths as $path ) {
				if (import ( $path . $class ))
					//如果加载类成功则返回
					return;
			}
		}
		return;
	}
	
	/**
	 * 自定义异常处理
	 * 
	 * @access public
	 * @param mixed $e 异常对象
	 */
	static public function appException($e) {
		halt ( $e->__toString () );
	}
	
	/**
	 * 自定义错误处理
	 * 
	 * @access public
	 * @param int $errno 错误类型
	 * @param string $errstr 错误信息
	 * @param string $errfile 错误文件
	 * @param int $errline 错误行数
	 * @return void
	 */
	static public function appError($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
			case E_ERROR :
			case E_PARSE :
			case E_CORE_ERROR :
			case E_COMPILE_ERROR :
			case E_USER_ERROR :
				ob_end_clean ();
				if (! ini_get ( 'zlib.output_compression' ) && C ( 'OUTPUT_ENCODE' ))
					ob_start ( 'ob_gzhandler' );
				$errorStr = "$errstr " . $errfile . " 第 $errline 行.";
				if (C ( 'LOG_RECORD' ))
					Log::write ( "[$errno] " . $errorStr, Log::ERR );
				function_exists ( 'halt' ) ? halt ( $errorStr ) : exit ( 'ERROR:' . $errorStr );
				break;
			case E_STRICT :
			case E_USER_WARNING :
			case E_USER_NOTICE :
			default :
				$errorStr = "[$errno] $errstr " . $errfile . " 第 $errline 行.";
				trace ( $errorStr, '', 'NOTIC' );
				break;
		}
	}
	
	/**
	 * 致命错误捕获
	 */
	static public function fatalError() {
		if ($e = error_get_last ()) {
			self::appError ( $e ['type'], $e ['message'], $e ['file'], $e ['line'] );
		}
	}
	
	/**
	 * 析构函数
	 */
	public function __destruct() {
		//记录该类结束时间
		setKeepTimer ( 'atom class', false );
	}
}
?>