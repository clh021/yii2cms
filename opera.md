#安装composer
```
curl -sS https://getcomposer.org/installer | php
```
#安装yii框架
```
./composer.phar global require "fxp/composer-asset-plugin:~1.0.0" #执行完成功，项目内没有变动
#./composer.phar require yiisoft/yii2
./composer.phar create-project yiisoft/yii2-app-advanced advanced 2.0.6
```
