Server Monitor
==========

![Apache2.0](https://img.shields.io/badge/License-Apache2.0-green)
![](https://img.shields.io/packagist/php-v/topthink/think/v5.1.40)


![GitHub Repo stars](https://img.shields.io/github/stars/LittleJake/server-monitor?style=social)
![GitHub followers](https://img.shields.io/github/followers/LittleJake?style=social)


基于ThinkPHP5.1的服务器监控平台，需要配合服务端python脚本，使用Redis存储相关数据。


### 使用
#### 安装
```bash
git clone https://github.com/LittleJake/server-monitor
cd server-monitor
composer install
cp .env.example .env
```

#### 后台登录密钥重置
```bash
php think token:generate
```

### 界面演示
![首页](https://cdn.jsdelivr.net/gh/LittleJake/blog-static-files@imgs/imgs/20210225163426.png)

![信息](https://cdn.jsdelivr.net/gh/LittleJake/blog-static-files@imgs/imgs/20210225163427.png)

![流量](https://cdn.jsdelivr.net/gh/LittleJake/blog-static-files@imgs/imgs/20210225163428.png)

![内存](https://cdn.jsdelivr.net/gh/LittleJake/blog-static-files@imgs/imgs/20210225163422.png)

![温度、根目录空间](https://cdn.jsdelivr.net/gh/LittleJake/blog-static-files@imgs/imgs/20210225163423.png)

![后台登录](https://cdn.jsdelivr.net/gh/LittleJake/blog-static-files@imgs/imgs/20210225163425.png)

![后台管理](https://cdn.jsdelivr.net/gh/LittleJake/blog-static-files@imgs/imgs/20210225163424.png)


### Demo
[测试网站](https://monitor.littlejake.net)


### 开源协议
[Apache 2.0]()

### 鸣谢

MDUI

ThinkPHP

ip-api.com