<?php
/**
 * 函数库文件
 */

/**
 * 获取配置
 *
 * @param string||array $conf_dir 配置文件路径
 * @param any_type $value 赋值
 * @return any_type
 */
function C($conf_dir = NULL, $value = NULL) {
	static $_config = array ('conf' => array () );
	
	//字符串取值或者赋值
	if (is_string ( $conf_dir )) {
		$conf_dir = trim ( $conf_dir );
		$ckeys = explode ( '.', $conf_dir );
		$count = count ( $ckeys );
		//取值
		if (is_null ( $value )) {
			//优先加载config.php的配置，判断conf文件是否存在有该键名
			if (isset ( $_config ['conf'] [$ckeys [0]] )) {
				$dynamic_str = '$_config [\'conf\']';
			} else {
				if (isset ( $_config [$ckeys [0]] )) {
					$dynamic_str = '$_config [\'' . strtolower ( $ckeys [0] ) . '\']';
					unset ( $ckeys [0] );
				} else {
					return null;
				}
			}
		} else {
			//赋值：优先加载config.php的配置，判断conf文件是否存在有该键名
			if (isset ( $_config ['conf'] [$ckeys [0]] ) || $count == 1) {
				$dynamic_str = '$_config [\'conf\']';
			} else {
				$dynamic_str = '$_config [\'' . strtolower ( $ckeys [0] ) . '\']';
				unset ( $ckeys [0] );
			}
		}
		
		foreach ( $ckeys as $v ) {
			$v = strtoupper ( $v );
			$dynamic_str .= '[\'' . $v . '\']';
		}
		//赋值
		if (! is_null ( $value )) {
			if (is_array ( $value )) {
				$dynamic_str .= '=' . var_export ( $value, true );
			} else if (is_numeric ( $value )) {
				$dynamic_str .= '=' . "{$value}";
			} else if (is_string ( $value )) {
				$value = addslashes ( $value );
				$dynamic_str .= '=' . "'{$value}'";
			} else {
				$dynamic_str .= "= '" . var_export ( $value, TRUE ) . "'";
			}
		} else {
			$dynamic_str = "isset({$dynamic_str}) ? {$dynamic_str} : NULL";
		}
		$dynamic_str = "return {$dynamic_str};";
		return eval ( $dynamic_str );
	}
	
	//数字赋值或取值
	if (is_numeric ( $conf_dir )) {
		//取值
		if (is_null ( $value )) {
			return $_config [$conf_dir];
		} else {
			//赋值
			return $_config [$conf_dir] = $value;
		}
	}
	
	//数组批量赋值
	if (is_array ( $conf_dir )) {
		foreach ( $conf_dir as $k => $v ) {
			if (isset ( $_config ['conf'] [$k] )) {
				if (is_array ( $v )) {
					$_config ['conf'] = array_merge ( $_config ['conf'], array_change_key_case ( $v, CASE_UPPER ) );
				} else {
					$_config ['conf'] [strtoupper ( $k )] = $v;
				}
			} else {
				//$_config [$k] = array_merge ( $_config [$k], array_change_key_case ( $v, CASE_UPPER ) );
				if (is_array ( $v )) {
					$_config [$k] = isset ( $_config [$k] ) ? $_config [$k] : array ();
					$_config [$k] = array_merge ( $_config [$k], array_change_key_case ( $v, CASE_UPPER ) );
				} else {
					$_config [$k] = $v;
				}
			}
		}
		return $_config;
	}
	
	//默认获取全部参数
	if ($conf_dir === null && $value === null) {
		return $_config;
	}
	return null;
}

/**
 * 导入所需的类库 同java的Import 本函数有缓存功能
 * 
 * @param string $class 类库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 * @return boolen
 */
function import($class, $baseUrl = '', $ext = '.class.php') {
	static $_file = array ();
	$class = str_replace ( array ('.', '#' ), array ('/', '.' ), $class );
	if (isset ( $_file [$class . $baseUrl] ))
		return true;
	else
		$_file [$class . $baseUrl] = true;
	$class_strut = explode ( '/', $class );
	if (empty ( $baseUrl )) {
		if ('@' == $class_strut [0] || APP_NAME == $class_strut [0]) {
			//加载当前项目应用类库
			$baseUrl = dirname ( LIB_PATH );
			$class = substr_replace ( $class, basename ( LIB_PATH ) . '/', 0, strlen ( $class_strut [0] ) + 1 );
		} else { // 加载其他项目应用类库
			$class = substr_replace ( $class, '', 0, strlen ( $class_strut [0] ) + 1 );
			$baseUrl = APP_PATH . '../' . $class_strut [0] . '/' . basename ( LIB_PATH ) . '/';
		}
	}
	if (substr ( $baseUrl, - 1 ) != '/')
		$baseUrl .= '/';
	$classfile = $baseUrl . $class . $ext;
	if (! class_exists ( basename ( $class ), false )) {
		// 如果类不存在 则导入类库文件
		return require_cache ( $classfile );
	}
}

/**
 * 优化的require_once
 * 
 * @param string $filename 文件地址
 * @return boolen
 */
function require_cache($filename) {
	static $_importFiles = array ();
	if (! isset ( $_importFiles [$filename] )) {
		if (file_exists ( $filename )) {
			require_once $filename;
			$_importFiles [$filename] = true;
		} else {
			$_importFiles [$filename] = false;
		}
	}
	return $_importFiles [$filename];
}

/**
 * 实例化Action的快速方法
 * 
 * @param string $name 类名
 * @param string $app  项目名称
 * @return return_type
 */
function A($name, $app = '@') {
	static $_action = array ();
	if (isset ( $_action [$app . $name] ))
		return $_action [$app . $name];
	$OriClassName = $name;
	if (strpos ( $name, '.' )) {
		$array = explode ( '.', $name );
		$name = array_pop ( $array );
		$className = $name . 'Action';
		import ( $app . '.Action.' . implode ( '.', $array ) . '.' . $className );
	} else {
		$className = $name . 'Action';
		import ( $app . '.Action.' . $className );
	}
	if (class_exists ( $className )) {
		$action = new $className ();
		$_action [$app . $OriClassName] = $action;
		return $action;
	} else {
		return false;
	}
}

/**
 * 错误输出
 * 
 * @param mixed $error 错误
 * @return void
 */
function halt($error) {
	$e = array ();
	if (APP_DEBUG) {
		//调试模式下输出错误信息
		if (! is_array ( $error )) {
			$trace = debug_backtrace ();
			$e ['message'] = $error;
			$e ['file'] = $trace [0] ['file'];
			$e ['class'] = isset ( $trace [0] ['class'] ) ? $trace [0] ['class'] : '';
			$e ['function'] = isset ( $trace [0] ['function'] ) ? $trace [0] ['function'] : '';
			$e ['line'] = $trace [0] ['line'];
			$traceInfo = '';
			$time = date ( 'y-m-d H:i:m' );
			foreach ( $trace as $t ) {
				$traceInfo .= '[' . $time . '] ' . $t ['file'] . ' (' . $t ['line'] . ') ';
				$traceInfo .= $t ['class'] . $t ['type'] . $t ['function'] . '(';
				$traceInfo .= implode ( ', ', $t ['args'] );
				$traceInfo .= ')<br/>';
			}
			$e ['trace'] = $traceInfo;
		} else {
			$e = $error;
		}
	} else {
		//否则定向到错误页面
		$error_page = C ( 'ERROR_PAGE' );
		if (! empty ( $error_page )) {
			redirect ( $error_page );
		} else {
			if (C ( 'SHOW_ERROR_MSG' ))
				$e ['message'] = is_array ( $error ) ? $error ['message'] : $error;
			else
				$e ['message'] = C ( 'ERROR_MESSAGE' );
		}
	}
	// 包含异常页面模板
	include C ( 'TMPL_EXCEPTION_FILE' );
	exit ();
}

/**
 * URL重定向
 * 
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time = 0, $msg = '') {
	//多行URL地址支持
	$url = str_replace ( array ("\n", "\r" ), '', $url );
	if (empty ( $msg ))
		$msg = "系统将在{$time}秒之后自动跳转到{$url}！";
	if (! headers_sent ()) {
		// redirect
		if (0 === $time) {
			header ( 'Location: ' . $url );
		} else {
			header ( "refresh:{$time};url={$url}" );
			echo ($msg);
		}
		exit ();
	} else {
		$str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
		if ($time != 0)
			$str .= $msg;
		exit ( $str );
	}
}

/**
 * 获取客户端IP地址
 * 
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0) {
	$type = $type ? 1 : 0;
	static $ip = NULL;
	if ($ip !== NULL)
		return $ip [$type];
	if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
		$arr = explode ( ',', $_SERVER ['HTTP_X_FORWARDED_FOR'] );
		$pos = array_search ( 'unknown', $arr );
		if (false !== $pos)
			unset ( $arr [$pos] );
		$ip = trim ( $arr [0] );
	} elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
		$ip = $_SERVER ['HTTP_CLIENT_IP'];
	} elseif (isset ( $_SERVER ['REMOTE_ADDR'] )) {
		$ip = $_SERVER ['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$long = ip2long ( $ip );
	$ip = $long ? array ($ip, $long ) : array ('0.0.0.0', 0 );
	return $ip [$type];
}

/**
 * 添加和获取页面Trace记录
 * 
 * @param string $value 变量
 * @param string $label 标签
 * @param string $level 日志级别 
 * @return void
 */
function trace($value = '[atom]', $label = '', $level = 'DEBUG', $record = false) {
	static $_trace = array ();
	if ('[atom]' === $value) { // 获取trace信息
		return $_trace;
	} else {
		$info = ($label ? $label . ':' : '') . print_r ( $value, true );
		if (APP_DEBUG && 'ERR' == $level) { // 调试模式ERR抛出异常
			throw_exception ( $info );
		}
		if (! isset ( $_trace [$level] )) {
			$_trace [$level] = array ();
		}
		$_trace [$level] [] = $info;
		if ((defined ( 'IS_AJAX' ) && IS_AJAX) || ! C ( 'SHOW_PAGE_TRACE' ) || $record) {
			Log::record ( $info, $level, $record );
		}
	}
}

/**
 * 自定义异常处理
 * 
 * @param string $msg 异常消息
 * @param string $type 异常类型 默认为AtomException
 * @param integer $code 异常代码 默认为0
 * @return void
 */
function throw_exception($msg, $type = 'AtomException', $code = 0) {
	if (class_exists ( $type, false )) {
		throw new $type ( $msg, $code, true );
	} else {
		halt ( $msg ); // 异常类型不存在则输出错误信息字串
	}
}

/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <code>
 * G('begin'); // 记录开始标记位
 * // ... 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * echo G('begin','end','m'); // 统计区间内存使用情况
 * 如果end标记位没有定义，则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </code>
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer|string $dec 小数位或者m 
 * @return mixed
 */
function G($start, $end = '', $dec = 4) {
	static $_info = array ();
	static $_mem = array ();
	if (is_float ( $end )) { // 记录时间
		$_info [$start] = $end;
	} elseif (! empty ( $end )) { // 统计时间和内存使用
		if (! isset ( $_info [$end] ))
			$_info [$end] = microtime ( TRUE );
		if (MEMORY_LIMIT_ON && $dec == 'm') {
			if (! isset ( $_mem [$end] ))
				$_mem [$end] = memory_get_usage ();
			return number_format ( ($_mem [$end] - $_mem [$start]) / 1024 );
		} else {
			return number_format ( ($_info [$end] - $_info [$start]), $dec );
		}
	
	} else { // 记录时间和内存使用
		$_info [$start] = microtime ( TRUE );
		if (MEMORY_LIMIT_ON)
			$_mem [$start] = memory_get_usage ();
	}
}

/**
 * 设置时间记录点
 * 
 * @author ruyi@izptec
 * @param string $key 记录点名称
 * @param boolean $start 是否为开始
 * @param int $dec 输出的长度
 * @return array
 */
function setKeepTimer($key, $start = TRUE, $dec = 6) {
	$key = trim ( $key );
	$start = ( boolean ) $start;
	$dec = ( int ) $dec;
	if (! $key) {
		return $key;
	}
	$start = intval ( $start );
	if ($start) {
		C ( GLOBAL_NAME . "." . KEEP_TIMER . ".{$key}.start.time", microtime ( true ) );
		if (MEMORY_LIMIT_ON) {
			C ( GLOBAL_NAME . "." . KEEP_TIMER . ".{$key}.start.mem", memory_get_usage () );
		}
		return C ( GLOBAL_NAME . "." . KEEP_TIMER . ".{$key}.start" );
	} else {
		$start = C ( GLOBAL_NAME . "." . KEEP_TIMER . ".{$key}.start" );
		
		//结束的赋值
		C ( GLOBAL_NAME . "." . KEEP_TIMER . ".{$key}.end.time", microtime ( true ) );
		if (MEMORY_LIMIT_ON) {
			C ( GLOBAL_NAME . "." . KEEP_TIMER . ".{$key}.end.mem", memory_get_usage () );
		}
		$end = C ( GLOBAL_NAME . "." . KEEP_TIMER . ".{$key}.end" );
		
		//赋差值
		C ( GLOBAL_NAME . "." . KEEP_TIMER . ".{$key}.diff.time", number_format ( $end ['TIME'] - $start ['TIME'], $dec ) );
		if (MEMORY_LIMIT_ON) {
			C ( GLOBAL_NAME . "." . KEEP_TIMER . ".{$key}.diff.mem", number_format ( ($end ['MEM'] - $start ['MEM']) / 1024, $dec ) );
		}
		return $end;
	}
}

/**
 * 获取时间记录点
 * 
 * @author ruyi@izptec
 * @param string $key 记录点名称
 * @return array
 */
function getKeepTimer($key = NULL) {
	$key = trim ( $key );
	return $key ? C ( GLOBAL_NAME . "." . KEEP_TIMER . ".{$key}" ) : C ( GLOBAL_NAME . "." . KEEP_TIMER );
}

/**
 * Debug赋值或者取值
 * 
 * @author ruyi@izptec
 * @param string||array $key debug的名称
 * @param any_type $value debug的值
 * @param string||array $out_put_type 当有值时输出，输出类型，all||print_r||dump||var_dump||json
 * @param boolean $exit 是否直接退出
 * @return array||debug
 */
function debugC($key, $value = NULL, $out_type = NULL, $exit = TRUE) {
	$open = ( boolean ) C ( 'ATOM_DEBUG.OPEN_DEBUG' );
	if ($open) {
		if (is_array ( $key )) {
			if ($out_type) {
				C ( array (DEBUG_NAME => $key ) );
				debug ( true, $out_type );
			} else {
				return C ( array (DEBUG_NAME => $key ) );
			}
		} else {
			$key = trim ( ( string ) $key );
			$key = $key ? DEBUG_NAME . '.' . $key : DEBUG_NAME;
			if ($out_type) {
				C ( $key, $value );
				debug ( true, $out_type );
			} else {
				return C ( $key, $value );
			}
		}
	} else {
		throw_exception ( "C ( 'ATOM_DEBUG.OPEN_DEBUG' ) is not open!" );
	}
}

/**
 * debug输出
 * 
 * @author ruyi@izptec
 * @param boolean $force_out 是否强制输出
 * @param string||array $out_put_type 输出类型，all||print_r||dump||var_dump||json，默认为print_r
 * @param boolean $exit 是否直接退出
 * @return print
 */
function debug($force_out = FALSE, $out_type = NULL, $exit = TRUE) {
	$force_out = ( boolean ) $force_out;
	if (! $force_out) {
		$open = C ( 'ATOM_DEBUG.OPEN_DEBUG' ) && isset ( $_GET ['debug'] ) && ( boolean ) $_GET ['debug'];
	} else {
		$open = true;
	}
	if ($open) {
		$out_type = $out_type ? $out_type : C ( 'ATOM_DEBUG.OUT_TYPE' );
		$out_type = isset ( $_GET ['debug_out_type'] ) ? $_GET ['debug_out_type'] : $out_type;
		$out_type = is_string ( $out_type ) ? array ($out_type ) : $out_type;
		$out_type = $out_type ? $out_type : array ('print_r' );
		echo "<pre>";
		echo "<h1>==========Atom Debug Start==========</h1>\n<hr/><hr/>\n";
		foreach ( C ( DEBUG_NAME ) as $key => $value ) {
			echo "<h2>{$key}</h2>\n";
			if (in_array ( 'print_r', $out_type ) || in_array ( 'all', $out_type )) {
				echo "----------print----------<br/>\n";
				print_r ( $value );
				echo "\n<br/>\n";
			}
			if (in_array ( 'dump', $out_type ) || in_array ( 'all', $out_type )) {
				echo "----------dump----------<br/>\n";
				dump ( $value );
				echo "\n<br/>\n";
			}
			
			if (in_array ( 'var_dump', $out_type ) || in_array ( 'all', $out_type )) {
				echo "----------var_dump----------<br/>\n";
				var_dump ( $value );
				echo "\n<br/>\n";
			}
			
			if (in_array ( 'json', $out_type ) || in_array ( 'all', $out_type )) {
				echo "----------json----------<br/>\n";
				echo json_encode ( $value );
				echo "\n<br/>\n";
			}
			echo "<hr/>\n";
		}
		
		//计时结束
		if (( boolean ) $exit) {
			foreach ( getKeepTimer () as $k => $v ) {
				setKeepTimer ( $k, FALSE );
			}
		}
		echo "<h2>Keep Timer</h2>\n";
		print_r ( getKeepTimer () );
		echo "\n<br/>\n";
		echo "<h1>==========Atom Debug End==========</h1>\n<hr/>";
		echo "</pre>";
	}
	return (( boolean ) $exit) ? exit () : null;
}

/**
 * 浏览器友好的变量输出
 * 
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var, $echo = true, $label = null, $strict = true) {
	$label = ($label === null) ? '' : rtrim ( $label ) . ' ';
	if (! $strict) {
		if (ini_get ( 'html_errors' )) {
			$output = print_r ( $var, true );
			$output = '<pre>' . $label . htmlspecialchars ( $output, ENT_QUOTES ) . '</pre>';
		} else {
			$output = $label . print_r ( $var, true );
		}
	} else {
		ob_start ();
		var_dump ( $var );
		$output = ob_get_clean ();
		if (! extension_loaded ( 'xdebug' )) {
			$output = preg_replace ( '/\]\=\>\n(\s+)/m', '] => ', $output );
			$output = '<pre>' . $label . htmlspecialchars ( $output, ENT_QUOTES ) . '</pre>';
		}
	}
	if ($echo) {
		echo ($output);
		return null;
	} else
		return $output;
}
?>