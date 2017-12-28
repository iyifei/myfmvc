<?php
/**
 * 系统异常
 * User: myf
 * Date: 17/2/23
 * Time: 上午6:58
 */

namespace Myf\Libs;


class CmsException extends \Exception {

    const ERROR_CODE = 1;

    public function __construct($errorMsg,$code=self::ERROR_CODE) {
        parent::__construct($errorMsg, $code);
    }


    public static function throwException(\Exception $e) {
        throw new CmsException($e->getMessage());
    }


    public static function throwExp($msg = null,$code=self::ERROR_CODE) {
        throw new CmsException($msg,$code);
    }

}