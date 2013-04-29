<?php
/**
 * 入口文件
 */

/**
 * 系统常量配置
 */
//系统目录定义
defined ( 'ATOM_PATH' ) or define ( 'ATOM_PATH', dirname ( __FILE__ ) . '/' );
//应用程序目录
defined ( 'APP_PATH' ) or define ( 'APP_PATH', dirname ( $_SERVER ['SCRIPT_FILENAME'] ) . '/' );
//项目配置目录
defined ( 'COMMON_PATH' ) or define ( 'COMMON_PATH', APP_PATH . 'Common/' );
//项目配置目录
defined ( 'CONF_PATH' ) or define ( 'CONF_PATH', APP_PATH . 'Conf/' );
//项目日志配置目录
defined ( 'LOG_PATH' ) or define ( 'LOG_PATH', APP_PATH . 'Log/' );
//项目类库目录
defined ( 'LIB_PATH' ) or define ( 'LIB_PATH', APP_PATH . 'Lib/' );
//控制层目录
defined ( 'ACTION_PATH' ) or define ( 'ACTION_PATH', LIB_PATH . 'Action/' );
//模式扩展目录
defined ( 'MODE_PATH' ) or define ( 'MODE_PATH', LIB_PATH . 'Mode/' );
//定义基础文件夹名称
defined ( 'BASE_FLODER' ) or define ( 'BASE_FLODER', 'Base/' );
//定义示例文件名称
defined ( 'EXAMPLE_FLODER' ) or define ( 'EXAMPLE_FLODER', 'Example/' );
//定义全局变量统一名称
defined ( 'GLOBAL_NAME' ) or define ( 'GLOBAL_NAME', '_atom_global' );
//定义保存时间点的名称
defined ( 'KEEP_TIMER' ) or define ( 'KEEP_TIMER', 'KEEP_TIMER' );
//是否开启内存
define ( 'MEMORY_LIMIT_ON', function_exists ( 'memory_get_usage' ) );
//定义Debug数据存储的名称
defined ( 'DEBUG_NAME' ) or define ( 'DEBUG_NAME', '_atom_debug' );

// 是否调试模式
defined ( 'APP_DEBUG' ) or define ( 'APP_DEBUG', TRUE );
//defined ( 'APP_DEBUG' ) or define ( 'APP_DEBUG', FALSE );
//是否动态加载（初始）配置，动态加载时不会写入runtime.php文件
defined ( 'DYNAMIC_CONF' ) or define ( 'DYNAMIC_CONF', TRUE );

//生成的临时文件
defined ( 'RUNTIME_PATH' ) or define ( 'RUNTIME_PATH', APP_PATH . 'Runtime/' );
defined ( 'RUNTIME_FILE_PATH' ) or define ( 'RUNTIME_FILE_PATH', RUNTIME_PATH . 'runtime.php' );

//系统信息
if (version_compare ( PHP_VERSION, '5.3.0', '<' )) {
	set_magic_quotes_runtime ( 0 );
	define ( 'MAGIC_QUOTES_GPC', get_magic_quotes_gpc () ? TRUE : False );
} else {
	define ( 'MAGIC_QUOTES_GPC', TRUE );
}
define ( 'IS_CGI', substr ( PHP_SAPI, 0, 3 ) == 'cgi' ? 1 : 0 );
define ( 'IS_WIN', strstr ( PHP_OS, 'WIN' ) ? 1 : 0 );
define ( 'IS_CLI', PHP_SAPI == 'cli' ? 1 : 0 );

// 项目名称
defined ( 'APP_NAME' ) or define ( 'APP_NAME', basename ( dirname ( $_SERVER ['SCRIPT_FILENAME'] ) ) );

//当前系统类型判断
if (! IS_CLI) {
	// 当前文件名
	if (! defined ( '_PHP_FILE_' )) {
		if (IS_CGI) {
			//CGI/FASTCGI模式下
			$_temp = explode ( '.php', $_SERVER ['PHP_SELF'] );
			define ( '_PHP_FILE_', rtrim ( str_replace ( $_SERVER ['HTTP_HOST'], '', $_temp [0] . '.php' ), '/' ) );
		} else {
			define ( '_PHP_FILE_', rtrim ( $_SERVER ['SCRIPT_NAME'], '/' ) );
		}
	}
	if (! defined ( '__ROOT__' )) {
		// 网站URL根目录
		if (strtoupper ( APP_NAME ) == strtoupper ( basename ( dirname ( _PHP_FILE_ ) ) )) {
			$_root = dirname ( dirname ( _PHP_FILE_ ) );
		} else {
			$_root = dirname ( _PHP_FILE_ );
		}
		define ( '__ROOT__', (($_root == '/' || $_root == '\\') ? '' : $_root) );
	}
}

/**
 * 系统内置所需方法
 */

/**
 * 获取PHP文件的代码
 * 
 * @param string $path 文件路径或者文件夹路径
 * @param string $type 文件类型file||conf
 * @return string
 */
function get_php_code($path, $type = 'file') {
	$php_str = '';
	if (! file_exists ( $path )) {
		return $php_str;
	}
	
	if (is_file ( $path )) {
		$str = php_strip_whitespace ( $path );
		$str = substr ( $str, 5 );
		$str = substr ( $str, 0, strlen ( $str ) - 2 );
		$str = trim ( $str );
		if ($type == 'conf') {
			$str = substr ( $str, 6 );
			$str = substr ( $str, 0, strlen ( $str ) - 1 );
			$str = trim ( $str );
			$php_str .= 'C(array (\'' . strtolower ( basename ( $path, '.php' ) ) . '\'=> ' . $str . '));';
		} else {
			$php_str .= $str;
		}
	} else if (is_dir ( $path )) {
		$dh = opendir ( $path );
		if ($dh) {
			while ( ($file_name = readdir ( $dh )) !== false ) {
				//只遍历当前指定的文件夹，不进行递归遍历。
				if (substr ( $file_name, - 4, 4 ) == '.php' && is_file ( $path . $file_name )) {
					$str = php_strip_whitespace ( $path . $file_name );
					$str = substr ( $str, 5 );
					$str = substr ( $str, 0, strlen ( $str ) - 2 );
					$str = trim ( $str );
					if ($type == 'conf') {
						$str = substr ( $str, 6 );
						$str = substr ( $str, 0, strlen ( $str ) - 1 );
						$str = trim ( $str );
						$php_str .= 'C(array (\'' . strtolower ( basename ( $file_name, '.php' ) ) . '\'=> ' . $str . '));';
					} else {
						$php_str .= $str;
					}
				}
			}
		}
		closedir ( $dh );
	}
	return $php_str;
}

/**
 * 动态加载配置文件
 * 
 * @author ruyi@izptec
 * @param string $path
 * @return boolean||array
 */
function require_once_conf($path) {
	static $_conf_files = array ();
	if (! file_exists ( $path )) {
		return false;
	}
	if (is_file ( $path )) {
		if (isset ( $_conf_files [$path] )) {
			return $_conf_files [$path];
		} else {
			$_conf_files [$path] = require_once $path;
		}
	} else if (is_dir ( $path )) {
		$dh = opendir ( $path );
		if ($dh) {
			while ( ($file_name = readdir ( $dh )) !== false ) {
				if (is_file ( $path . $file_name )) {
					if (isset ( $_conf_files [$path . $file_name] )) {
						return $_conf_files [$path . $file_name];
					} else {
						$_conf_files [$path . $file_name] = require_once $path . $file_name;
					}
				}
			}
		}
	}
	return $_conf_files;
}

/**
 * 导入文件并压缩成一个文件
 * 
 * @example $file_list = array(filename=>array(type,is_build))
 * @param array $file_list 文件路径 ；$file_list['type']文件类型，php文件或者conf文件；$file_list['is_build']，是否进行压缩，默认为true；
 * @return array
 */
function build_runtime_file($file_list = array()) {
	static $_path = array ();
	static $content = '';
	if (empty ( $file_list )) {
		return array ('_path' => $_path, 'content' => $content );
	}
	$_path = array_merge ( $_path, $file_list );
	if (is_array ( $_path )) {
		$content = '';
		foreach ( $_path as $k => $v ) {
			$v ['is_build'] = isset ( $v ['is_build'] ) ? $v ['is_build'] : true;
			if (file_exists ( $k ) && $v ['is_build']) {
				$content .= get_php_code ( $k, $v ['type'] );
				$_path [$k] ['is_build'] = false;
			}
		}
	}
	$content = '<?php ' . $content . ' ?>';
	if (! file_exists ( RUNTIME_PATH )) {
		mkdir ( RUNTIME_PATH, 0777 );
	}
	file_put_contents ( RUNTIME_FILE_PATH, $content );
	return array ('_path' => $_path, 'content' => $content );
}

//是否使用缓存文件
if (! APP_DEBUG && is_file ( RUNTIME_FILE_PATH )) {
	require_once RUNTIME_FILE_PATH;
} else {
	//导入文件列表
	$_require_files = array ();
	//函数库文件
	$_require_files [COMMON_PATH . BASE_FLODER . 'common.php'] = array ('type' => 'file', 'is_build' => true );
	$_require_files [COMMON_PATH] = array ('type' => 'file', 'is_build' => true );
	//配置文件
	if (! DYNAMIC_CONF) {
		$_require_files [CONF_PATH . BASE_FLODER . 'conf.php'] = array ('type' => 'conf', 'is_build' => true );
		$_require_files [CONF_PATH . EXAMPLE_FLODER . 'conf.php'] = array ('type' => 'conf', 'is_build' => true );
		$_require_files [CONF_PATH] = array ('type' => 'conf', 'is_build' => true );
	}
	//基类文件
	$_require_files [LIB_PATH . BASE_FLODER . 'Atom.class.php'] = array ('type' => 'file', 'is_build' => true );
	$_require_files [LIB_PATH . BASE_FLODER . 'AtomException.class.php'] = array ('type' => 'file', 'is_build' => true );
	$_require_files [LIB_PATH . BASE_FLODER . 'Log.class.php'] = array ('type' => 'file', 'is_build' => true );
	build_runtime_file ( $_require_files );
	//创建日志文件夹
	if (! file_exists ( LOG_PATH )) {
		mkdir ( LOG_PATH, 0777 );
	}
	require_once RUNTIME_FILE_PATH;
}

//是否动态加载入配置文件
if (DYNAMIC_CONF) {
	require_once_conf ( CONF_PATH . BASE_FLODER . 'conf.php' );
	require_once_conf ( CONF_PATH . EXAMPLE_FLODER . 'conf.php' );
	require_once_conf ( CONF_PATH );
}
?>