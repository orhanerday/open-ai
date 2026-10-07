# API coverage audit

Audited 2026-10-07 against the supplied guides and the current official OpenAI API reference.
The scope is the pasted documentation and existing endpoint families, including their current REST
subresources. This is an HTTP/cURL client. It does not implement every OpenAI product or transport.

## Pasted guides

| Guide | PHP support | Boundary |
| --- | --- | --- |
| Counting tokens | `countResponseInputTokens($opts)` → `POST /v1/responses/input_tokens` | Server-side counts; no local tokenizer |
| Compaction | `compactResponse($opts)` → `POST /v1/responses/compact`; `context_management` passes through `createResponse()` | Model must support compaction |
| File inputs | Files/Uploads wrappers; `input_file` content passes through `createResponse()` | File purpose, format, and model restrictions still apply |
| Webhooks | Seven management operations plus `Webhook::unwrap()` and `verifySignature()` | The application hosts its receiver and deduplicates deliveries |
| Multi-agent | Responses HTTP methods accept `multi_agent` options; `setHeader()` preserves `OpenAI-Beta: responses_multi_agent=v1` | Hosted collaboration is performed by the API; custom function tools are handled by the application |
| Mid-turn steering | No WebSocket transport | Requires a separate WebSocket client |
| WebSocket Mode | No persistent WebSocket connection or WebSocket event helpers | HTTP SSE is supported; WebSocket-only fields such as `stream_id` must not be sent over HTTP |
| Streaming API responses | Per-request callback on `createResponse()` and `retrieveResponse()` | Raw SSE chunks; callbacks return the byte count and callers buffer/parse events |
| Background mode | `background` option, retrieve/poll, cancel, streaming create and GET resumption with `starting_after` | Retrieval streaming requires a response originally created with streaming |
| Conversation state | Responses input/`previous_response_id`/`conversation`; Conversation and Item CRUD | All request fields pass through; no automatic history manager |
| Decisions | `createDecision($opts)` → `POST /v1/decisions` | Beta; explicitly choose an enabled model |
| Migrate to Responses | Responses wrappers and [MIGRATING.md](MIGRATING.md) | Legacy payloads are not automatically rewritten |

## REST route inventory

Every path below is relative to `https://api.openai.com/v1`. Optional options on GET methods are
query parameters, with repeated `include[]` fields, nested filters, and textual boolean values.
POST bodies use JSON except file/part/voice uploads, image edits, transcription, and WebRTC call creation,
which use multipart where appropriate. A call's session array is serialized as a JSON multipart field.

| Family | Method | HTTP route |
| --- | --- | --- |
| Responses | `createResponse` | `POST /responses` |
| Responses | `retrieveResponse` | `GET /responses/{response_id}` |
| Responses | `deleteResponse` | `DELETE /responses/{response_id}` |
| Responses | `cancelResponse` | `POST /responses/{response_id}/cancel` |
| Responses | `compactResponse` | `POST /responses/compact` |
| Responses | `countResponseInputTokens` | `POST /responses/input_tokens` |
| Responses | `listResponseInputItems` | `GET /responses/{response_id}/input_items` |
| Conversations | `createConversation` | `POST /conversations` |
| Conversations | `retrieveConversation` | `GET /conversations/{conversation_id}` |
| Conversations | `updateConversation` | `POST /conversations/{conversation_id}` |
| Conversations | `deleteConversation` | `DELETE /conversations/{conversation_id}` |
| Conversations | `createConversationItem` | `POST /conversations/{conversation_id}/items` |
| Conversations | `retrieveConversationItem` | `GET /conversations/{conversation_id}/items/{item_id}` |
| Conversations | `listConversationItems` | `GET /conversations/{conversation_id}/items` |
| Conversations | `deleteConversationItem` | `DELETE /conversations/{conversation_id}/items/{item_id}` |
| Chat | `chat` | `POST /chat/completions` |
| Chat | `retrieveChatCompletion` | `GET /chat/completions/{completion_id}` |
| Chat | `listChatCompletions` | `GET /chat/completions` |
| Chat | `updateChatCompletion` | `POST /chat/completions/{completion_id}` |
| Chat | `deleteChatCompletion` | `DELETE /chat/completions/{completion_id}` |
| Chat | `listChatCompletionMessages` | `GET /chat/completions/{completion_id}/messages` |
| Decisions | `createDecision` | `POST /decisions` |
| Images | `image` | `POST /images/generations` |
| Images | `imageEdit` | `POST /images/edits` |
| Embeddings | `embeddings` | `POST /embeddings` |
| Moderation | `moderation` | `POST /moderations` |
| Audio | `tts` | `POST /audio/speech` |
| Audio | `transcribe` | `POST /audio/transcriptions` |
| Audio | `translate` | `POST /audio/translations` |
| Audio | `createVoice` | `POST /audio/voices` |
| Audio | `createVoiceConsent` | `POST /audio/voice_consents` |
| Audio | `listVoiceConsents` | `GET /audio/voice_consents` |
| Audio | `retrieveVoiceConsent` | `GET /audio/voice_consents/{consent_id}` |
| Audio | `updateVoiceConsent` | `POST /audio/voice_consents/{consent_id}` |
| Audio | `deleteVoiceConsent` | `DELETE /audio/voice_consents/{consent_id}` |
| Files | `uploadFile` | `POST /files` |
| Files | `listFiles` | `GET /files` |
| Files | `retrieveFile` | `GET /files/{file_id}` |
| Files | `retrieveFileContent` | `GET /files/{file_id}/content` |
| Files | `deleteFile` | `DELETE /files/{file_id}` |
| Fine-tuning | `createFineTune` | `POST /fine_tuning/jobs` |
| Fine-tuning | `listFineTunes` | `GET /fine_tuning/jobs` |
| Fine-tuning | `retrieveFineTune` | `GET /fine_tuning/jobs/{job_id}` |
| Fine-tuning | `cancelFineTune` | `POST /fine_tuning/jobs/{job_id}/cancel` |
| Fine-tuning | `pauseFineTune` | `POST /fine_tuning/jobs/{job_id}/pause` |
| Fine-tuning | `resumeFineTune` | `POST /fine_tuning/jobs/{job_id}/resume` |
| Fine-tuning | `listFineTuneEvents` | `GET /fine_tuning/jobs/{job_id}/events` |
| Fine-tuning | `listFineTuningCheckpoints` | `GET /fine_tuning/jobs/{job_id}/checkpoints` |
| Fine-tuning | `listFineTuningCheckpointPermissions` | `GET /fine_tuning/checkpoints/{checkpoint_id}/permissions` |
| Fine-tuning | `createFineTuningCheckpointPermission` | `POST /fine_tuning/checkpoints/{checkpoint_id}/permissions` |
| Fine-tuning | `deleteFineTuningCheckpointPermission` | `DELETE /fine_tuning/checkpoints/{checkpoint_id}/permissions/{permission_id}` |
| Fine-tuning | `runFineTuningGrader` | `POST /fine_tuning/alpha/graders/run` |
| Fine-tuning | `validateFineTuningGrader` | `POST /fine_tuning/alpha/graders/validate` |
| Models | `listModels` | `GET /models` |
| Models | `retrieveModel` | `GET /models/{model_id}` |
| Models | `deleteFineTune` | `DELETE /models/{model_id}` (deletes a model, not a job) |
| Vector stores | `createVectorStore` | `POST /vector_stores` |
| Vector stores | `listVectorStores` | `GET /vector_stores` |
| Vector stores | `retrieveVectorStore` | `GET /vector_stores/{store_id}` |
| Vector stores | `updateVectorStore` | `POST /vector_stores/{store_id}` |
| Vector stores | `deleteVectorStore` | `DELETE /vector_stores/{store_id}` |
| Vector stores | `searchVectorStore` | `POST /vector_stores/{store_id}/search` |
| Vector stores | `createVectorStoreFile` | `POST /vector_stores/{store_id}/files` |
| Vector stores | `listVectorStoreFiles` | `GET /vector_stores/{store_id}/files` |
| Vector stores | `retrieveVectorStoreFile` | `GET /vector_stores/{store_id}/files/{file_id}` |
| Vector stores | `updateVectorStoreFile` | `POST /vector_stores/{store_id}/files/{file_id}` |
| Vector stores | `deleteVectorStoreFile` | `DELETE /vector_stores/{store_id}/files/{file_id}` |
| Vector stores | `retrieveVectorStoreFileContent` | `GET /vector_stores/{store_id}/files/{file_id}/content` |
| Vector stores | `createVectorStoreFileBatch` | `POST /vector_stores/{store_id}/file_batches` |
| Vector stores | `retrieveVectorStoreFileBatch` | `GET /vector_stores/{store_id}/file_batches/{batch_id}` |
| Vector stores | `cancelVectorStoreFileBatch` | `POST /vector_stores/{store_id}/file_batches/{batch_id}/cancel` |
| Vector stores | `listVectorStoreFileBatchFiles` | `GET /vector_stores/{store_id}/file_batches/{batch_id}/files` |
| Batches | `createBatch` | `POST /batches` |
| Batches | `retrieveBatch` | `GET /batches/{batch_id}` |
| Batches | `cancelBatch` | `POST /batches/{batch_id}/cancel` |
| Batches | `listBatches` | `GET /batches` |
| Uploads | `createUpload` | `POST /uploads` |
| Uploads | `addUploadPart` | `POST /uploads/{upload_id}/parts` |
| Uploads | `completeUpload` | `POST /uploads/{upload_id}/complete` |
| Uploads | `cancelUpload` | `POST /uploads/{upload_id}/cancel` |
| Realtime | `createRealtimeClientSecret` | `POST /realtime/client_secrets` |
| Realtime | `createRealtimeTranslationClientSecret` | `POST /realtime/translations/client_secrets` |
| Realtime | `createRealtimeCall` | `POST /realtime/calls` |
| Realtime | `acceptRealtimeCall` | `POST /realtime/calls/{call_id}/accept` |
| Realtime | `hangupRealtimeCall` | `POST /realtime/calls/{call_id}/hangup` |
| Realtime | `referRealtimeCall` | `POST /realtime/calls/{call_id}/refer` |
| Realtime | `rejectRealtimeCall` | `POST /realtime/calls/{call_id}/reject` |
| Webhooks | `createWebhookEndpoint` | `POST /webhook_endpoints` |
| Webhooks | `listWebhookEndpoints` | `GET /webhook_endpoints` |
| Webhooks | `retrieveWebhookEndpoint` | `GET /webhook_endpoints/{endpoint_id}` |
| Webhooks | `updateWebhookEndpoint` | `POST /webhook_endpoints/{endpoint_id}` |
| Webhooks | `deleteWebhookEndpoint` | `DELETE /webhook_endpoints/{endpoint_id}` |
| Webhooks | `rotateWebhookEndpointSecret` | `POST /webhook_endpoints/{endpoint_id}/rotate_secret` |
| Webhooks | `testWebhookEndpoint` | `POST /webhook_endpoints/{endpoint_id}/test` |

## Real tests and remaining validation

`composer test` uses native cURL and makes actual OpenAI calls when `OPENAI_API_KEY` is set.
Without that key, API cases are skipped explicitly. No cURL functions are replaced or mocked.

Live lifecycle coverage includes Responses (stream/resume, stored retrieval, input items, deletion, cancellation,
token count and compaction), Conversations/items, stored Chat CRUD/messages, Files, Uploads, Vector Stores/files
and file batches, Batches, Decisions, embeddings, image generation, moderation, model retrieval,
Realtime client secrets, webhook listing, fine-tuning grader validation/execution, and multipart image edit SSE.
Files, stores, chats, and conversations created by tests are deleted; cancelled Batch records remain on the
API because it has no batch deletion endpoint. Tests do not operate on unrelated user resources.

Account- or fixture-dependent cases must not be reported as verified merely because their wrapper exists:

| Operation | Required fixture or access | Current limitation |
| --- | --- | --- |
| Custom voices/consents | Eligible account; actual consent recording/audio sample for creation | `OPENAI_TEST_CUSTOM_VOICES=1` enables a real list check; it returned HTTP 404 with the local account |
| Speech, transcription, translation | Supported model and actual audio fixture for transcription/translation | REST wrappers remain; these media workflows are not covered by the default live suite |
| Image/audio SSE | Streaming-capable model and input audio for transcription | Image edit SSE and multiple multipart images are live-tested; audio streaming and image generation SSE need separate validation |
| Realtime translation secrets and calls | Enabled translation model; a real WebRTC offer or SIP call | Credential REST and call-control wrappers exist; no media session client |
| Fine-tuning jobs/checkpoints/permissions | Training fixture, owned job/checkpoint; admin key for permissions | Default suite lists jobs and runs graders; it does not create training jobs, pause user jobs, or change checkpoint access |
| Webhook create/update/delete/rotate/test | An owned receiver URL and subscription | Default suite lists endpoints and verifies signatures locally; it does not send events to arbitrary receivers |
| Multi-agent | Supported beta model and beta header | HTTP fields and header pass through; orchestration and every beta event are not separately tested |

## Observed API failure

On 2026-10-07, `cancelVectorStoreFileBatch()` repeatedly returned HTTP 500 with `server_error`.
The path and POST method match the official reference. The failure reproduced with an empty JSON
body and a bodyless request, and with the reference's beta header on an `in_progress` batch.
This points to an API-side problem; cancellation has not been successfully validated.
The real test stays enabled and fails when the API returns that error.
One reproducible request ID was `req_1904dc07962a4c949bb8b194e7cc0786`.

Final local `composer test` result: **42 passed, 1 failed, 1 skipped** (196 assertions) on PHP 8.4.
The failure is vector file-batch cancellation; the custom voice account test is explicitly skipped.
Docker runtime verification on Linux ARM64 also completed on 2026-10-07:

| Runtime | Native transport and webhook tests | Real API tests |
| --- | --- | --- |
| PHP 7.4.33 / Pest 1.23.1 | 16 passed (31 assertions) | 25 passed, 2 failed, 1 skipped; image streaming passed on an isolated rerun |
| PHP 8.4.26 / Pest 4.7.8 | 16 passed (31 assertions) | 26 passed, 1 failed, 1 skipped |

Vector file-batch cancellation returned HTTP 500 on both runtimes. The additional PHP 7.4 failure
was an OpenSSL TLS read error during image-edit streaming; the same test passed when rerun alone.
Custom voice consent listing was skipped because the account capability flag was not enabled.
All source and test files passed syntax checks in both containers. Docker logs and JUnit reports
are saved locally under ignored `build/docker/`; no cURL mocks were used.
All 29 official documentation links in the current guides resolved successfully during this audit.

Run `vendor/bin/pest --group=live --filter 'vector store file batch'` with your key to reproduce.
No mock, expected-error assertion, or skip is used to turn this failure into a pass.

## Removed and intentionally retained references

Dead integrations and their examples are removed from current code and examples. Migration notes and
historical changelog entries name removed methods so existing users can find their replacements.
Archived API dumps under ignored `sources/` are reference evidence, not shipped documentation.
The `assistants` file purpose remains valid for vector store ingestion despite the Assistants API retirement.
Audio translation/speech compatibility wrappers remain while their API routes exist; their deprecated
model requirements are documented rather than replaced with a model belonging to a different transport.
`setCustomURL()` remains a deprecated compatibility alias for `setBaseURL()`.

Administration, Agents, Live, Evals, ChatKit, Containers, Skills, Safety, and other separate API families
are outside this audit's agreed scope. Do not describe this release as implementing every OpenAI API endpoint.

## Official references

- [Responses API](https://developers.openai.com/api/reference/resources/responses)
- [Conversations API](https://developers.openai.com/api/reference/resources/conversations)
- [Background mode](https://developers.openai.com/api/docs/guides/background)
- [Decisions](https://developers.openai.com/api/docs/guides/decisions)
- [Webhooks](https://developers.openai.com/api/docs/guides/webhooks)
- [Create a webhook endpoint](https://developers.openai.com/api/reference/resources/webhooks/methods/create)
- [Stored Chat](https://developers.openai.com/api/reference/resources/chat/subresources/completions)
- [Audio voices](https://developers.openai.com/api/reference/resources/audio/subresources/voices/methods/create)
- [Voice consents](https://developers.openai.com/api/reference/resources/audio/subresources/voice_consents)
- [Fine-tuning graders](https://developers.openai.com/api/reference/resources/fine_tuning/subresources/alpha/subresources/graders)
- [Checkpoint permissions](https://developers.openai.com/api/reference/resources/fine_tuning/subresources/checkpoints/subresources/permissions)
- [Realtime calls](https://developers.openai.com/api/reference/resources/realtime/subresources/calls)
- [Realtime translation client secrets](https://developers.openai.com/api/reference/resources/realtime/subresources/translations/subresources/client_secrets/methods/create)
- [Deprecations](https://developers.openai.com/api/docs/deprecations)
