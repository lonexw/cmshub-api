# cmshub-server

## 安装
## 配置
### 邮件配置，将使用邮件发送功能
```
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"
```
### 配置阿里云oss参数
提供两种上传oss方式，一种是前端直传阿里云，另一种是通过服务器接口上传

1、通过服务器方式

如果不配置参数，默认上传到本地服务器
```
#阿里云oss
OSS_ACCESS_KEY=
OSS_SECRET_KEY=
OSS_ENDPOINT=
OSS_BUCKET=
OSS_IS_CNAME=false
```

2、通过前端直传方式，需配置

```
# 阿里云oss(直传)
ALIYUN_OSS_ACCESS_KEY=
ALIYUN_OSS_ACCESS_SECRET=
ALIYUN_OSS_ROLE_ARN=
ALIYUN_OSS_REGION_ID=
ALIYUN_OSS_REGION=
ALIYUN_OSS_BUCKET=
```
## 自定义表结构文档
api域名/graphql 目录需要可写入，字段调整后将会生成结构文件

通道 http://接口服务器/graphql?Project-Id=1 来访问

接口权限通过token机制来访问，每个token可以对应多个自定义表，并且可以设置多个权限（query 查询/mutation 增删改/open 开放），
在Header中传入token字段后接口会根据token权限来判断是否有权限操作
## 模型关联，附件关联
附件关联是字段name后+Asset，可查询对应的附件对象
模型关联是字段name后+Reference，可查询对应的模型对象
如字段name是banners，那么使用bannersReference可查询到关联模型的具体属性

## 上传文件
api域名/api/upload-image 使用laravel的api接口，不使用graphql接口

## 命令行添加用户
根据提示输入信息即可，邮箱必填，作为用户名使用
```
php artisan command:add_user
```
##多语言设置
1.执行数据库迁移
```
php artisan migrate
```
2.执行数据库填充
```
php artisan db:seed --class=LanguageSeeder
```
3.生成动态语句
```
php artisan command:update_cust_graphql
```
4.前端调用查询
在原有的查询接口中，往headers里添加lang参数