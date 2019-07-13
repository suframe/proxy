
suframe proxy
===============
基于swoole的轻量微服务代理中心，用于api接口注册，代理转发应用库

## 主要功能

* 接口转发
* 服务注册
* 代理接口连接池
* 定时检测接口
* rpc接口自动同步
* rpc生成ide代码提示

## 约定

* 目前单机版本，下版本会提供分布式部署
* https暂不支持，推荐通过nginx 代理https接口转发到此服务
* 后端服务注册约定 /summer 前缀作为系统接口，用与和代理中心进行通信
* 代理中心默认8080端口对外访问，9500端口作为内部服务注册及同步rpc接口信息(可通过配置修改)


## 安装

~~~
composer require suframe/suframe-proxy
~~~

## 命名规范

遵循PSR-2命名规范和PSR-4自动加载规范。

## 参与开发

QQ群：904592189


## 版权信息

suframe遵循Apache2开源协议发布，并提供免费使用。

版权所有Copyright © 2019- by qian <330576744@qq.com>

All rights reserved。