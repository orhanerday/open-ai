# PHP 中的 OpenAI GPT-3 Api 客户端

<br />

<br />

*来自创作者的消息，<br />感谢您访问 __@orhanerday/open-ai__ 存储库！ 我们很高兴看到它已被下载近 3 万次。
如果您发现此存储库有帮助或有用，我们鼓励您在 GitHub 上为它加注星标。 为存储库加注星标是表达您对该项目支持的一种方式。
它还有助于提高项目的知名度并让社区知道它是有价值的。 再次感谢您的支持，我们希望您发现存储库有用！ <br /><br />奥尔罕*

<br />

<br />


[![Latest Version on Packagist](https://img.shields.io/packagist/v/orhanerday/open-ai.svg?style=flat-square)](https://packagist.org/packages/orhanerday/open-ai)
[![Total Downloads](https://img.shields.io/packagist/dt/orhanerday/open-ai.svg?style=flat-square)](https://packagist.org/packages/orhanerday/open-ai)
<br />

<br />

<img src="./openai-elephpant.svg" width="1250" height="300" alt="orhanerday-open-ai-logo">

<br />

<br />

# 与其他包的比较

| 项目名称                   | 所需的 PHP 版本   | 下载                                                                                                                                                                            | 说明                                                                                  | 类型（官方/社区） | 支持                                                                                                    |
|------------------------|--------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------|-----------|-------------------------------------------------------------------------------------------------------|
| **orhanerday/open-ai** | **PHP 7.4+** | **[![Total Downloads](https://img.shields.io/packagist/dt/orhanerday/open-ai.svg?style=flat-square)](https://packagist.org/packages/orhanerday/open-ai) <br>🚀nearly 35K 🚀** | **大多数下载、分叉、贡献、庞大的社区支持，以及用于 OpenAI GPT-3 和 DALL-E 的 PHP SDK。 它还支持类似 chatGPT 的流式传输。** | 社区        | 可用，（[社区驱动的 Discord 服务器](https://discord.gg/mtY2jCsQgx) 或个人邮件 [orhan@duck.com](mailto:orhan@duck.com)） |
| openai-php/client      | PHP 8.1+     | <a href="https://packagist.org/packages/openai-php/client"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/openai-php/client"></a>                        | OpenAI PHP API 客户端。                                                                 | 社区        | -                                                                                                     |

<br />

## 关于这个包

完全开源且安全的社区维护的 PHP SDK，用于访问 OpenAI GPT-3 API。

> #### 更多信息，可以阅读 laravel news [博文](https://laravel-news.com/openai-sdk-for-php)。
> #### 提供免费支持。 [加入我们的不和谐服务器](#join-our-discord-server)
> #### 要开始使用此包，您首先需要熟悉 [OpenAI API 文档](https://beta.openai.com/docs/introduction) 和 [示例](https:/ /beta.openai.com/examples）。 您还可以从我们名为 [#api-support](https://discord.gg/R9CpVUdqQR) 的不和谐频道获得帮助

＃＃ 消息

- orhanerday/open-ai 添加到社区库 php [部分](https://beta.openai.com/docs/libraries/php)。
- orhanerday/open-aiorhanerday/open-ai featured on [PHPStorm blog post](https://blog.jetbrains.com/phpstorm/2022/12/php-annotated-december-2022/#:~:text=orhanerday/open%2Dai%20%E2%80%93%20A%20PHP%20SDK%20for%20accessing%20the%20OpenAI%20GPT%2D3%20API), 谢谢，JetBrains！

> 需要 PHP 7.4+

## 加入我们的不和谐服务器

![Discord 横幅 2](https://discordapp.com/api/guilds/1047074572488417330/widget.png?style=banner2)

[点击此处加入 Discord 服务器](https://discord.gg/6FmA6vDUkS)

## 支持这个项目

您可能知道，OpenAI PHP 是 OpenAI 的开源项目包装工具。 我们依靠社区的支持来继续开发和维护该项目，您可以提供帮助的一种方式是捐款。

捐款使我们能够支付托管费用（用于测试）、开发工具和其他保持项目顺利运行所需的资源等费用。 每一个贡献，无论多小，都有助于我们继续改进
OpenAI PHP
每个人。

如果您从使用 OpenAI PHP 中受益并愿意支持它的持续开发，我们将不胜感激任何数额的捐赠。 您可以通过以下方式捐款；

* [请我喝咖啡](https://www.buymeacoffee.com/orhane)
* [Patreon](https://patreon.com/orhann)
* [点击此处获取 Coinbase QR](#btc) **比特币** > 34w2DftWGkDqDbYMixkmdWWMLmaP9uTRz7
* [点击此处获取 Coinbase QR](#doge) **Dogecoin** > DHiqcZox9M8kYDn7BkesnN6Z2kJ7dYG9Lc
* [点击此处获取 Coinbase QR](#eth) **以太坊** > 0x135E2D5d7AC40c6850f844BA589D68e91a268Ceb

感谢您考虑向 Orhanerday/OpenAI PHP SDK 捐款。 非常感谢您的支持，这有助于确保项目能够继续发展和改进。

*真挚地，*

**Orhan Erday** / 创作者。

# 端点支持

- 楷模
    - [x] [列出模型](https://beta.openai.com/docs/api-reference/models/list)
    - [x] [检索模型](https://beta.openai.com/docs/api-reference/models/retrieve)
- 完成
    - [x] [创建完成](https://beta.openai.com/docs/api-reference/completions/create)
- 编辑
    - [x] [创建编辑](https://beta.openai.com/docs/api-reference/edits/create)
- 图片
    - [x] [创建图像](https://beta.openai.com/docs/api-reference/images/create)
    - [x] [创建图像编辑](https://beta.openai.com/docs/api-reference/images/create-edit)
    - [x] [创建图像变体](https://beta.openai.com/docs/api-reference/images/create-variation)
- 嵌入
    - [x] [创建嵌入](https://beta.openai.com/docs/api-reference/embeddings/create)
- 文件
    - [x] [列表文件](https://beta.openai.com/docs/api-reference/files/list)
    - [x] [上传文件](https://beta.openai.com/docs/api-reference/files/upload)
    - [x] [删除文件](https://beta.openai.com/docs/api-reference/files/delete)
    - [x] [检索文件](https://beta.openai.com/docs/api-reference/files/retrieve)
    - [x] [检索文件内容](https://beta.openai.com/docs/api-reference/files/retrieve-content)
- 微调
    - [x] [创建微调（测试版）](https://beta.openai.com/docs/api-reference/fine-tunes/create)
    - [x] [列表微调（测试版）](https://beta.openai.com/docs/api-reference/fine-tunes/list)
    - [x] [检索微调（测试版）](https://beta.openai.com/docs/api-reference/fine-tunes/retrieve)
    - [x] [取消微调（测试版）](https://beta.openai.com/docs/api-reference/fine-tunes/cancel)
    - [x] [列出微调事件（测试版）](https://beta.openai.com/docs/api-reference/fine-tunes/events)
    - [x] [删除微调模型（测试版）](https://beta.openai.com/docs/api-reference/fine-tunes/delete-model)
- 适度
    - [x] [创建审核](https://beta.openai.com/docs/api-reference/moderations/create)
- ~~引擎~~ *（弃用）*
    - ~~[列出引擎](https://beta.openai.com/docs/api-reference/engines/list)~~
    - ~~[检索引擎](https://beta.openai.com/docs/api-reference/engines/retrieve)~~

＃＃ 安装

您可以通过作曲家安装软件包：

```狂欢
composer require orhanerday/open-ai
```

＃＃ 快速开始
在开始之前，您应该将 OPENAI_API_KEY 设置为 ENV 密钥，并使用以下命令将 OpenAI 密钥设置为 env 值；

_电源外壳_

```powershell
$Env:OPENAI_API_KEY = "sk-gjtv....."
```

_命令_

```命令
设置 OPENAI_API_KEY=sk-gjtv.....
```

_Linux 或 macOS_

```外壳
export OPENAI_API_KEY=sk-gjtv.....
```

> 在设置 env 时遇到问题？ 请阅读[文章](https://help.openai.com/en/articles/5112595-best-practices-for-api-key-safety)。

创建您的 `index.php` 文件并将以下代码部分粘贴到该文件中。

```PHP
<?php

需要 __DIR__ 。 '/vendor/autoload.php'; // 如果您使用 PHP 框架，请删除此行。

使用 Orhanerday\OpenAi\OpenAi；

$open_ai_key = getenv('OPENAI_API_KEY');
$open_ai = new OpenAi($open_ai_key);

$complete = $open_ai->完成([
     '模型'=>'达芬奇'，
     '提示'=>'你好'，
     '温度'=> 0.9，
     'max_tokens' => 150,
     'frequency_penalty' => 0,
     'presence_penalty' => 0.6,
]);

var_dump（$完成）；
```

_使用以下命令运行服务器_

```外壳
php -S localhost:8000 -t 。
```

＃＃ 用法

### 从环境变量加载你的密钥。

> 根据以下代码，`$open_ai` 是所有 open-ai 操作的基础变量。

```PHP
使用 Orhanerday\OpenAi\OpenAi；

$open_ai = new OpenAi(env('OPEN_AI_API_KEY'));
```

## 完成

给定一个提示，模型将返回一个或多个预测的完成，也可以返回概率
每个位置的替代令牌。

  ```PHP
$complete = $open_ai->完成([
     '模型' => 'text-davinci-002',
     '提示'=>'你好'，
     '温度'=> 0.9，
     'max_tokens' => 150,
     'frequency_penalty' => 0,
     'presence_penalty' => 0.6,
]);
```

### 流示例

[ChatGPT](https://chat.openai.com/chat) 中的此功能可能听起来很熟悉。

是否回流部分进度。
如果设置，令牌将作为纯数据 [服务器发送事件](https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events/Usin
g_server-sent_events#event_stream_format）可用时，数据流终止：[DONE] 消息。

  ````php
$open_ai = new OpenAi(env('OPEN_AI_API_KEY'));

$选择= [
     '提示'=>“你好”，
     '温度'=> 0.9，
     "max_tokens" => 150,
     "frequency_penalty" => 0,
     "presence_penalty" => 0.6,
     “流” => 真，
];

header('内容类型：文本/事件流');
header('缓存控制：无缓存');

$open_ai->completion($opts, function ($curl_info, $data) {
     回声 $ 数据。 “<br><br>”；
     echo PHP_EOL;
     ob_flush();
     冲洗（）；
     返回 strlen($数据);
});

````

将此部分添加到 HTML 的 `<body>` 中

  ````php
 
<div id="divID">你好</div>
<脚本>
     var eventSource = new EventSource("/");
     var div = document.getElementById('divID');


     eventSource.onmessage = function (e) {
        如果（e.data ==“[完成]”）
        {
            div.innerHTML += "<br><br>你好";
        }
         div.innerHTML += JSON.parse(e.data).choices[0].text;
     };
     eventSource.onerror = function (e) {
         控制台日志（e）；
     };
</脚本>
````

您应该会看到类似视频中的回复；

https://user-images.githubusercontent.com/22305274/209847128-f72c9345-dd34-46f0-bbc5-daf1d7b6121f.mp4

## 编辑

为提供的输入、指令和参数创建新的编辑

  ```PHP
     $result = $open_ai->createEdit([
         “模型”=>“text-davinci-edit-001”，
         "input" => "今天是星期几？",
         "instruction" => "修正拼写错误",
     ]);
```

## 图片 (DALL·E)

> 此 [repo](https://github.com/orhanerday/DALLE-Examples) 中提供的所有 DALL·E 示例。

给出提示后，模型将以 url 或 base64 编码的形式返回一个或多个生成的图像。

### 创建图像

根据提示创建图像。

  ```PHP
$complete = $open_ai->图像([
     "prompt" => "一只喝牛奶的猫",
     "n" => 1,
     "尺寸" => "256x256",
     "response_format" => "url",
]);
```

### 创建图像编辑

在给定原始图像和提示的情况下创建编辑或扩展图像。
> 您需要上传 HTML 来进行图片编辑或修改吗？ 请查看[DALL·E示例](https://github.com/orhanerday/DALLE-Examples)

````php
$otter = curl_file_create(__DIR__ . './files/otter.png');
$mask = curl_file_create(__DIR__ . './files/mask.jpg');

$result = $open_ai->imageEdit([
     "图片" => $水獭,
     “面具”=> $面具,
     "prompt" => "一只戴着贝雷帽的可爱海獭宝宝",
     "n" => 2,
     "尺寸" => "1024x1024",
]);
````

### 创建图像变体

创建给定图像的变体。

````php
$otter = curl_file_create(__DIR__ . './files/otter.png');

$result = $open_ai->createImageVariation([
     "图片" => $水獭,
     "n" => 2,
     "尺寸" => "256x256",
]);
````

## 搜索

**_（已弃用）_**
> 此端点已弃用，将于 2022 年 12 月 3 日删除
> OpenAI 开发了性能更好的新方法。 [了解更多。](https://help.openai.com/en/articles/6272952-search-transition-guide)

给定一个查询和一组文档或标签，该模型根据每个文档与
提供查询。

```PHP
$search = $open_ai->search([
     '引擎' => 'ada',
     'documents' => ['白宫', '医院', '学校'],
     '查询'=>'总统'，
]);
```

## 嵌入

获取给定输入的矢量表示，机器学习模型和算法可以轻松使用该表示。

相关指南：[Embeddings](https://beta.openai.com/docs/guides/embeddings)

### 创建嵌入

```PHP
$result = $open_ai->嵌入（[
     “模型”=>“文本相似度-babbage-001”，
     "input" => "食物很美味，服务员..."
]);
```

## 答案

**_（已弃用）_**

> 此端点已弃用，将于 2022 年 12 月 3 日删除
> 我们开发了性能更好的新方法。 [了解更多](https://help.openai.com/en/articles/6233728-answers-transition-guide)。

给定一个问题、一组文档和一些示例，API 会根据
文件集中的信息。 这对于基于真实来源的问答应用程序很有用，例如
公司文档或知识库。

   ```PHP
$answer = $open_ai->answer([
     'documents' => ['小狗 A 很开心。', '小狗 B 很伤心。'],
     'question' => '哪只小狗快乐？',
     'search_model' => 'ada',
     '模型'=>'居里'，
     'examples_context' => '2017 年，美国人的预期寿命为 78.6 岁。',
     'examples' => [['美国的人类预期寿命是多少？', '78 岁。']],
     'max_tokens' => 5,
     '停止' => ["\n", '<|endoftext|>'],
]);
```

## 分类

**_（已弃用）_**
> 此端点已弃用，将于 2022 年 12 月 3 日删除
> OpenAI
> 开发了性能更好的新方法。 [了解更多。](https://help.openai.com/en/articles/6272941-classifications-transition-guide)

给定一个查询和一组标记的示例，该模型将预测最可能的查询标签。 作为有用的
任何 ML 分类或 t 的直接替换
ext-to-label 任务。

  ```PHP
$classification = $open_ai->分类([
     '例子'=> [
         ['幸福的时刻', '积极的'],
         ['我很难过', '消极'],
         ['我感觉棒极了', '积极的'],
     ],
     'labels' => ['正面', '负面', '中性'],
     '查询'=>'这是一个下雨天=>（'，
     'search_model' => 'ada',
     '模型'=>'居里'，
]);
```

## 内容审核

给定输入文本，如果模型将其分类为违反 OpenAI 的内容策略，则输出。

```PHP
$flags = $open_ai->节制([
     'input' => '我想杀了他们。
]);
```

在此处了解有关内容审核的更多信息：[OpenAI 审核](https://beta.openai.com/docs/api-reference/moderations)

## 列出引擎

**_（已弃用）_**

> 引擎端点已弃用。
> 请改用它们的替代品 [Models](#list-models)。 [了解更多]（待办事项？）。

列出当前可用的引擎，并提供有关每个引擎的基本信息，例如所有者和可用性。

  ```PHP
$engines = $open_ai->engines();
```

## 文件

文件用于上传文档，这些文档可跨答案、搜索和分类等功能使用

### 列出文件

返回属于用户组织的文件列表。

```PHP
$files = $open_ai->listFiles();
```

＃＃＃ 上传文件

上传包含要跨各种端点/功能使用的文档的文件。 目前，所有规模
一个组织上传的文件最大可达 1 GB。 如果您需要增加存储限制，请联系 OpenAI。

```PHP
$c_file = curl_file_create(__DIR__ . 'files/sample_file_1.jsonl');
$result = $open_ai->uploadFile([
             “目的”=>“答案”，
             "文件" => $c_file,
]);
```

### 使用 HTML 表单上传文件

```PHP
<form action="index.php" method="post" enctype="multipart/form-data">
     选择要上传的文件：
     <input type="file" name="fileToUpload" id="fileToUpload">
     <input type="submit" value="上传文件" name="submit">
</表格>
<?php
需要 __DIR__ 。 '/vendor/autoload.php';

使用 Orhanerday\OpenAi\OpenAi；

如果（$_SERVER['REQUEST_METHOD'] == 'POST'）{
     ob_clean();
     $open_ai = new OpenAi(env('OPEN_AI_API_KEY'));
     $tmp_file = $_FILES['fileToUpload']['tmp_name'];
     $file_name = basename($_FILES['fileToUpload']['name']);
     $c_file = curl_file_create($tmp_file, $_FILES['fileToUpload']['type'], $file_name);

     回声“[”;
     echo $open_ai->上传文件(
         [
             “目的”=>“答案”，
             "文件" => $c_file,
         ]
     );
     回声“，”；
     echo $open_ai->listFiles();
     回声“]”；

}

```

###  删除文件

  ```PHP
$result = $open_ai->deleteFile('file-xxxxxxxx');
```

### 检索文件

  ```PHP
$file = $open_ai->retrieveFile('file-xxxxxxxx');
```

### 检索文件内容

  ```PHP
$file = $open_ai->retrieveFileContent('file-xxxxxxxx');
```

##微调

管理微调作业以根据您的特定训练数据定制模型。

### 创建微调

  ```PHP
$result = $open_ai->createFineTune([
         "training_file" => "文件-U3KoAAtGsjUKSPXwEUDdtw86",
]);
```

### 列表微调

  ```PHP
$fine_tunes = $open_ai->listFineTunes();
```

### 检索微调

  ```PHP
$fine_tune = $open_ai->retrieveFineTune('ft-AF1WoRqd3aJAHsqc9NY7iL8F');
```

###取消微调

  ```PHP
$result = $open_ai->cancelFineTune('ft-AF1WoRqd3aJAHsqc9NY7iL8F');
```

### 列出微调事件

  ```PHP
$fine_tune_events = $open_ai->listFineTuneEvents('ft-AF1WoRqd3aJAHsqc9NY7iL8F');
```

###删除微调模型

  ```PHP
$result = $open_ai->deleteFineTune('居里：ft-acmeco-2021-03-03-21-44-20');
```

## 检索引擎

**_（已弃用）_**

检索引擎实例，提供有关引擎的基本信息，例如所有者和可用性。

  ```PHP
$engine = $open_ai->engine('达芬奇');
```

＃＃ 楷模
列出并描述 API 中可用的各种模型。

### 列出模型

列出当前可用的模型，并提供有关每个模型的基本信息，例如所有者和可用性。

  ```PHP
$result = $open_ai->listModels();
```

### 检索模型

检索模型实例，提供有关模型的基本信息，例如所有者和权限。

  ```PHP
$result = $open_ai->retrieveModel("text-ada-001");
```

## 打印结果 *即* `$search`

  ```PHP
回声$搜索；
```

## 测试

运行所有测试：

```狂欢
作曲家测试
```

仅运行那些对大多数用户有效的测试（排除那些需要丢失文件夹或命中大多数用户不再可用的已弃用端点的测试）：

```狂欢
./vendor/bin/pest --group=working
```

## 变更日志

请参阅 [CHANGELOG](CHANGELOG.md) 以获取有关最近更改内容的更多信息。

## 贡献

有关详细信息，请参阅 [CONTRIBUTING](.github/CONTRIBUTING.md)。

## 安全漏洞

请向 [orhanerday@gmail.com](mailto:orhanerday@gmail.com) 报告安全漏洞

## 学分

- [Orhan Erday](https://github.com/orhanerday)
- [所有贡献者](../../contributors)


##  执照

麻省理工学院李焚香炉（麻省理工学院）。 请参阅[许可证文件](LICENSE.md) 了解更多信息。

## 捐赠

<a href="https://www.buymeacoffee.com/orhane" target="_blank"><img src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" alt= "给我买杯咖啡" style="height: 41px !important;width: 174px !important;box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;" ></a>

#### 比特币
![图片](https://user-images.githubusercontent.com/22305274/209946578-fc7db433-699c-491f-9f8b-1c962f0b9ea2.png)

#### 伦理

![图片](https://user-images.githubusercontent.com/22305274/209946539-24f247d9-68a1-4f46-a18b-62790d943c99.png)

#### 总督
![图片](https://user-images.githubusercontent.com/22305274/209946556-164798d0-e404-4b6c-8669-d63e78f24228.png)
