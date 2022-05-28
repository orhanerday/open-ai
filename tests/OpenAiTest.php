<?php


use Orhanerday\OpenAi\OpenAi;

$open_ai = new OpenAi('OPEN-AI-KEY');


it('should handle simple completion', function () use ($open_ai) {
    $result = $open_ai->complete([
        'engine' => 'davinci',
        'prompt' => "Hello",
        'temperature' => 0.9,
        "max_tokens" => 150,
        "frequency_penalty" => 0,
        "presence_penalty" => 0.6,
    ]);

    $this->assertStringContainsString('text', $result);
});


it('should handle search', function () use ($open_ai) {
    $result = $open_ai->search([
        'engine' => 'ada',
        'documents' => ["White House", "hospital", "school"],
        'query' => "the president",
    ]);
    $this->assertStringContainsString('data', $result);
});


it('should handle file upload', function () use ($open_ai) {
    $c_file = curl_file_create(__DIR__ . './files/sample_file_1.jsonl');
    $result = $open_ai->uploadFile([
            "purpose" => "answers",
            "file" => $c_file,
        ]);

    $this->assertStringContainsString('uploaded', $result);
});

it('should handle list files', function () use ($open_ai) {
    $result = $open_ai->listFiles();

    $this->assertStringContainsString('data', $result);
});

it('should handle retrieve the file', function () use ($open_ai) {
    $result = $open_ai->retrieveFile('file-hrlLUmPhddnplQUVa2CTzg5k');

    $this->assertStringContainsString('file', $result);
});

it('should handle delete the file', function () use ($open_ai) {
    $result = $open_ai->deleteFile('file-hrlLUmPhddnplQUVa2CTzg5k');

    $this->assertStringContainsString('file', $result);
});

it('should handle answers', function () use ($open_ai) {
    $result = $open_ai->answer([
        "documents" => ["Puppy A is happy.", "Puppy B is sad."],
        "question" => "which puppy is happy?",
        "search_model" => "ada",
        "model" => "curie",
        "examples_context" => "In 2017, U.S. life expectancy was 78.6 years.",
        "examples" => [["What is human life expectancy in the United States?", "78 years."]],
        "max_tokens" => 5,
        "stop" => ["\n", "<|endoftext|>"],
    ]);

    $this->assertStringContainsString('selected_documents', $result);
});

it('should handle classification', function () use ($open_ai) {
    $result = $open_ai->classification([
        'examples' => [
            ["A happy moment", "Positive"],
            ["I am sad.", "Negative"],
            ["I am feeling awesome", "Positive"],
        ],
        'labels' => ["Positive", "Negative", "Neutral"],
        'query' => "It is a raining day =>(",
        'search_model' => "ada",
        'model' => "curie",
    ]);

    $this->assertStringContainsString('selected_examples', $result);
});

it('should handle engines', function () use ($open_ai) {
    $result = $open_ai->engines();
    $this->assertStringContainsString('data', $result);
});


it('should handle engine', function () use ($open_ai) {
    $engine_id = 'davinci';
    $result = $open_ai->engine($engine_id);
    $this->assertStringContainsString($engine_id, $result);
});
