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
如果不配置参数，默认上传到本地服务器
```
#阿里云oss
OSS_ACCESS_KEY=
OSS_SECRET_KEY=
OSS_ENDPOINT=
OSS_BUCKET=
OSS_IS_CNAME=false
```
## 自定义表结构文档
/graphql 目录需要可写入，字段调整后将会生成结构文件

通道 http://接口服务器/graphql?Project-Id=1 来访问