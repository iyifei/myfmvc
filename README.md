# MyfMVC框架

php编写的简单mvc框架

### 特性说明

- 框架简单易学，内部引用类均是作者多年积累的实用函数
- 框架已经在多个中小型项目中使用，比较成熟
- 框架封装比较简洁，适合入门者学习研究使用

### 快速使用

#### 1、使用composer 引用到项目中，在项目根目录添加composer.json内容如下：

```php

{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/iyifei/myfmvc.git"
    }
  ],
  "config": {
    "secure-http": false
  },
  "require": {
    "iyifei/myfmvc": "dev-master"
  }
}

```
进入根目录执行 composer install
 

#### 2、创建对应的项目依赖目录，结构如下：

![img](http://chuantu.biz/t6/213/1516760251x-1404793274.png)

#### 3、Common/Config目录下创建两个文件：
db.config.php
```php

<?php
/**
 * 数据库配置
 */
return array(
    //数据库链接配置
    'database' => array(
        'default' => array(
            'host' => 'localhost',
            'port' => '3306',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'myfmvc',
            'charset' => 'utf8',
            'prefix' => ''
        ),
    ),
    //默认db
    'default_db' => 'default',
);

```
namespace.config.php

```php
<?php
/**
 * 命名空间配置文件
 */
return [
   'autoload'=>[
       "Common\Model" => SYS_PATH . "/Common/Model",
   ]
];

```
#### 4、在根目录创建index.php

```php
<?php
/**
 * 入口文件
 * User: myf
 * Date: 2017/12/28
 * Time: 11:44
 */

define('APP_PATH', __DIR__.'/');
define("SYS_PATH",dirname(APP_PATH));
define('COMMON_PATH', SYS_PATH.'/Common/');
define('OP_CONF_DIR','/data/www/opconfig/');
require_once SYS_PATH.'/vendor/autoload.php';

```
#### 5、配置nginx

```php

server {
    listen       80;
    server_name mvc.myf.cn;
    root /data/www/myfmvc-demo/App;
    index index.php index.html index.htm;

    location / {
        if (!-e $request_filename) {
            rewrite  ^(.*)$  /index.php?_url=$1  last;
            break;
        }
    }


    location ~ \.php$ {
        include /usr/local/etc/nginx/fastcgi.conf;
        fastcgi_intercept_errors on;
        fastcgi_pass   127.0.0.1:9000;
    }

}


```

#### 6、在Controller目录下创建IndexController.php

```php
<?php

namespace Controller;

use Common\Model\UserModel;
use Myf\Libs\Controller;

/**
 * demo
 */
class IndexController extends Controller
{

    public function indexAction(){
        echo "hello world";
    }

    public function testAction(){
        $this->assign('title',"MyfMVC DEMO");

        $userModel = new UserModel();
        $users = $userModel->findAll();
        $this->assign('users',$users);

        $this->display();
    }

}

```

#### 7、浏览器输入访问：
http://XXX/index/test

#### 8、脚本访问
进入App目录，执行
php index.php test/abc


### 针对此项目也写了个demo，欢迎大家下载使用
https://github.com/iyifei/myfmvc-demo