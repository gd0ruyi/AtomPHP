<?php
return array (
	/* 输出 */
	'OUTPUT_ENCODE'         =>  true,	//页面压缩输出

	/* 默认设置 */
	'DEFAULT_GROUP'         => 'Home',	//默认分组
    'DEFAULT_MODULE'        => 'Index',	//默认模块名称
    'DEFAULT_ACTION'        => 'index',	//默认操作名称
	'DEFAULT_TIMEZONE'      => 'PRC',	//默认时区

	/* 系统变量名称设置 */
    'VAR_GROUP'             => 'g',		//默认分组获取变量
    'VAR_MODULE'            => 'm',		//默认模块获取变量
    'VAR_ACTION'            => 'a',		//默认操作获取变量

	/* 错误设置 */
    'ERROR_MESSAGE'         => '页面错误！请稍后再试......',//错误显示信息,非调试模式有效
    'ERROR_PAGE'            => '',	//错误定向页面
    'SHOW_ERROR_MSG'        => false,	//显示错误信息

	/* 日志设置 */
    'LOG_RECORD'            => false,	//默认不记录日志
    'LOG_TYPE'              => 3,		//日志记录类型 0 系统 1 邮件 3 文件 4 SAPI 默认为文件方式
    'LOG_DEST'              => '', //日志记录目标
    'LOG_EXTRA'             => '', //日志记录额外信息
    'LOG_LEVEL'             => 'EMERG,ALERT,CRIT,ERR',	//允许记录的日志级别
    'LOG_FILE_SIZE'         => 2097152,	//日志文件大小限制
    'LOG_EXCEPTION_RECORD'  => false,	//是否记录异常信息日志

	/* URL设置 */
	'URL_PATHINFO_DEPR'     => '/',	//PATHINFO模式下，各参数之间的分割符号
	'URL_PATHINFO_FETCH'    =>   'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL', //用于兼容判断PATH_INFO 参数的SERVER替代变量列表

	/* Debug定义 */
	'ATOM_DEBUG' =>	array(
		'OPEN_DEBUG' => false,	//是否打开debug输出
		'OUT_TYPE' => 'print_r',	//输出类型，可以为all||print_r||dump||var_dump||json
	)
);
?>