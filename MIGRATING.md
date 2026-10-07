# Migrating from 5.x to the upcoming 6.0 release

This refactor removes public methods and therefore targets 6.0.0. Existing GitHub releases already include 5.0 through 5.3.

## Removed APIs

| Removed methods | Replacement |
| --- | --- |
| `complete()`, `completion()` | `createResponse()` with `input`, or `chat()` with `messages` |
| Assistants, Threads, Messages, Runs, and Run Steps methods | Responses and Conversations APIs |
| `answer()`, `classification()`, `search()` | Responses, vector store search, or embeddings, depending on the task |
| `engines()`, `engine()` | `listModels()`, `retrieveModel()` |
| `createEdit()` | `createResponse()` with editing instructions; image editing still uses `imageEdit()` |
| `createImageVariation()` | `imageEdit()` with an input image, a GPT Image model, and variation instructions |
| `setAssistantsBetaVersion()` | Remove the call; the replacement APIs do not use the Assistants beta header |

URL helpers for the removed integrations are also removed. There is no automatic translation of old request payloads.
See [OpenAI's deprecations](https://developers.openai.com/api/docs/deprecations) and
the [Assistants migration guide](https://developers.openai.com/api/docs/assistants/migration).
The [image variations endpoint](https://developers.openai.com/api/reference/resources/images/methods/create_variation)
is retired; use image edits with variation instructions.

## Text generation

Replace a legacy prompt request with:

```php
$result = $open_ai->createResponse([
    'model' => 'gpt-4o-mini',
    'input' => 'Hello',
    'max_output_tokens' => 150,
]);

$response = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
foreach ($response['output'] as $item) {
    if ($item['type'] !== 'message') {
        continue;
    }
    foreach ($item['content'] as $content) {
        if ($content['type'] === 'output_text') {
            echo $content['text'];
        }
    }
}
```

Response text is in message items within `output`; the old `choices[0].text` extraction does not apply.
For streaming, pass a callback as the second argument to `createResponse()` or `chat()`.
Callbacks receive raw SSE chunks and must return `strlen($data)`. Responses uses named events such as
`response.output_text.delta`; the old completion event parser must be replaced. See the [README example](README.md#stream-example).

## Defaults and errors

- `chat()` defaults to `gpt-4o-mini`; embeddings defaults to `text-embedding-3-small`.
- Explicitly supplied models are preserved. Responses and image requests require an explicit model.
- Connection failures throw `Exception` with the cURL error message and code. Valid empty bodies are returned unchanged.
- Invalid UTF-8 or otherwise unencodable JSON request data throws `JsonException` before making a request.
- HTTP API errors remain raw response bodies. Check `getCURLInfo()['http_code']` and the decoded `error` field.

## Files, images, and Realtime

Use file and job IDs returned by your own API requests. File uploads require a purpose supported by the Files API.
Upload Parts uses `data => new CURLFile(...)`; Uploads creation and completion use JSON bodies.
GPT Image models return base64 image data; select a current model explicitly.
Realtime client-secret configuration belongs inside `session`, including `type` and `model`.
Checkpoint permission operations require an admin API key.

## Tests

`composer test` runs native cURL checks and the real OpenAI API suite. Export `OPENAI_API_KEY`
or put it in `.env`; without a key the live tests are explicitly skipped.
`composer test-live` runs only API tests. Live tests may incur API charges and clean up their
own resources. There are no cURL mocks. See [endpoint support](README.md#endpoint-support) for endpoint
coverage and operations requiring additional fixtures or account access.

## Query parameters and headers

List and retrieval methods accept query options, including `include` arrays and pagination.
`retrieveResponse($id, $opts, $callback)` supports resuming HTTP SSE with `stream => true`
and `starting_after`. Streaming creation and retrieval each require their own callback.
`setHeader()` accepts either `['OpenAI-Beta: responses_multi_agent=v1']` or an associative
array such as `['OpenAI-Beta' => 'responses_multi_agent=v1']`, preserving other headers.

## Additional REST support

Stored chat completion CRUD, Decisions, webhook management, voice consents, fine-tuning
graders, job pause/resume, checkpoint permission listing, and Realtime REST call control
are available. Webhook signatures can be checked with `Webhook::unwrap()` using the
original request body. WebSocket connections and mid-turn steering require a separate client.
