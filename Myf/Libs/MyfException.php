<?php
/**
 * 系统异常
 * User: myf
 * Date: 17/2/23
 * Time: 上午6:58
 */

namespace Myf\Libs;


class MyfException extends \Exception {

    /**
     * 正常输出结果
     */
    const SUCCESS_CODE = 0;

    /**
     * 通用错误
     */
    const NORMAL_ERROR = 1;
    /**
     * 参数错误
     */
    const PARAM_ERROR = 2;

    public function __construct($errorMsg,$code=self::NORMAL_ERROR) {
        parent::__construct($errorMsg, $code);
    }


    public static function throwException(\Exception $e) {
        throw new MyfException($e->getMessage());
    }


    public static function throwExp($msg = null,$code=self::NORMAL_ERROR) {
        throw new MyfException($msg,$code);
    }

}