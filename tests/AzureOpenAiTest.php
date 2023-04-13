<?php

$ai = new \Ze\OpenAi\AzureOpenAi(
    'RESOURCE-NAME',
    'API-KEY',
    'API-VERSION'
);

it('list deployments', function () use ($ai) {
    $result = $ai->listDeployments();
    $this->assertStringContainsString('text', $result);
})->group('azure');

it('list models', function () use ($ai) {
    $result = $ai->listModels();
    $this->assertStringContainsString('text', $result);
})->group('azure');

it('emb test', function () use ($ai) {
    $result = $ai->embeddings([
        'model' => 'text-embedding-ada-002',
        'input' => '测试语句',
        'user'  => time(),
    ]);
    $this->assertStringContainsString('text', $result);
})->group('azure');

it('chat test', function () use ($ai) {
    $result = $ai->chat([
        'model'       => 'gpt-35-turbo',
        'messages'    => [
            [
                'role'    => 'system',
                'content' => 'Assistant is an AI chatbot that helps users turn a natural language list into JSON format. After users input a list they want in JSON format, it will provide suggested list of attribute labels if the user has not provided any, then ask the user to confirm them before creating the list.'
            ],
            [
                'role'    => 'user',
                'content' => '我想预订一个会议室',
            ],
            [
                'role'    => 'assistant',
                'content' => '很抱歉，我是一个语言模型，无法为您预订会议 室。但是，我可以告诉您一些预订会议室的建议。您可以通过以下方式预订会议室：1. 在线预订：许多会议室提供在线预订系统，您可以在网上查找并预订。2. 通过电话预订：您可以通过电话联系会议室管理人员进行预订。3. 通过电子邮件预订：您可以通过电子邮件联系会议室管理人员进行预订。请注意，每个会议室的预订方式可能不同，因此最好在预订之前查看其网站或联系管理人员以获取更多信息。'
            ]
        ],
        'temperature' => 0.2,
        'user'        => (string) time(),
    ]);
    $this->assertStringContainsString('text', $result);
})->group('azure');

it('completion', function () use ($ai) {
    $result = $ai->completion([
        'model'       => 'text-davinci-003',
        'prompt'      => '你好',
        'temperature' => 0.2,
        'user'        => time(),
    ]);
    $this->assertStringContainsString('text', $result);
})->group('azure');


