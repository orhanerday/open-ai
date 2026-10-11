<?php

use Orhanerday\OpenAi\OpenAi;

// These tests talk to a real local HTTP server that echoes each request, so the exact
// method, path, headers, and body that leave the client are asserted. No cURL mocks.
beforeAll(function () {
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
    $name = stream_socket_get_name($socket, false);
    fclose($socket);
    $port = (int) substr($name, strrpos($name, ':') + 1);

    $command = [PHP_BINARY, '-S', '127.0.0.1:' . $port, __DIR__ . '/Support/echo-server.php'];
    $descriptors = [0 => ['pipe', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']];
    $GLOBALS['agents_server'] = proc_open($command, $descriptors, $pipes);
    $GLOBALS['agents_port'] = $port;

    for ($i = 0; $i < 50; $i++) {
        $connection = @fsockopen('127.0.0.1', $port, $errno, $error, 0.1);
        if ($connection) {
            fclose($connection);

            return;
        }
        usleep(100000);
    }
});

afterAll(function () {
    if (isset($GLOBALS['agents_server']) && is_resource($GLOBALS['agents_server'])) {
        proc_terminate($GLOBALS['agents_server']);
        proc_close($GLOBALS['agents_server']);
    }
});

function agentsClient(): OpenAi
{
    $client = new OpenAi('test-key');
    $client->setBaseURL('http://127.0.0.1:' . $GLOBALS['agents_port']);

    return $client;
}

function echoed(string $response): array
{
    return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
}

it('sends each agents request to the documented route with the beta header', function ($call, $method, $uri) {
    $seen = echoed($call(agentsClient()));

    expect($seen['method'])->toBe($method);
    expect($seen['uri'])->toBe($uri);
    expect($seen['authorization'])->toBe('Bearer test-key');
    expect($seen['beta'])->toBe('agents=v1');
})->with([
    'create session' => [fn ($c) => $c->createAgentSession(['agent' => ['model' => 'm']]), 'POST', '/v1/agents/sessions'],
    'list sessions' => [fn ($c) => $c->listAgentSessions(['limit' => 20, 'order' => 'desc']), 'GET', '/v1/agents/sessions?limit=20&order=desc'],
    'retrieve session' => [fn ($c) => $c->retrieveAgentSession('sess_1'), 'GET', '/v1/agents/sessions/sess_1'],
    'update session' => [fn ($c) => $c->updateAgentSession('sess_1', ['metadata' => ['a' => 'b']]), 'POST', '/v1/agents/sessions/sess_1'],
    'delete session' => [fn ($c) => $c->deleteAgentSession('sess_1'), 'DELETE', '/v1/agents/sessions/sess_1'],
    'send events' => [fn ($c) => $c->sendAgentSessionEvents('sess_1', [['type' => 'x']]), 'POST', '/v1/agents/sessions/sess_1/events'],
    'list items' => [fn ($c) => $c->listAgentSessionItems('sess_1', ['order' => 'asc', 'limit' => 100]), 'GET', '/v1/agents/sessions/sess_1/items?order=asc&limit=100'],
    'list turns' => [fn ($c) => $c->listAgentSessionTurns('sess_1', ['limit' => 20]), 'GET', '/v1/agents/sessions/sess_1/turns?limit=20'],
    'retrieve turn' => [fn ($c) => $c->retrieveAgentSessionTurn('sess_1', 'turn_1'), 'GET', '/v1/agents/sessions/sess_1/turns/turn_1'],
    'export traces' => [fn ($c) => $c->exportAgentSessionTraces('sess_1', ['limit' => 20, 'order' => 'asc']), 'GET', '/v1/agents/sessions/sess_1/traces?limit=20&order=asc'],
    'create environment' => [fn ($c) => $c->createAgentEnvironment(['environment' => ['type' => 'openai_hosted']]), 'POST', '/v1/agents/environments'],
    'list environments' => [fn ($c) => $c->listAgentEnvironments(), 'GET', '/v1/agents/environments'],
    'retrieve environment' => [fn ($c) => $c->retrieveAgentEnvironment('env_1'), 'GET', '/v1/agents/environments/env_1'],
])->group('transport');

it('sends JSON bodies for agents writes', function () {
    $client = agentsClient();

    $seen = echoed($client->createAgentSession(['agent' => ['model' => 'm'], 'input' => 'hi']));
    expect(json_decode($seen['body'], true))->toBe(['agent' => ['model' => 'm'], 'input' => 'hi']);
    expect($seen['content_type'])->toBe('application/json');

    $seen = echoed($client->sendAgentSessionEvents('sess_1', [['type' => 'agent.session.input.message', 'input' => []]]));
    expect(json_decode($seen['body'], true))->toBe(['events' => [['type' => 'agent.session.input.message', 'input' => []]]]);
})->group('transport');

it('encodes session identifiers in the path', function () {
    $seen = echoed(agentsClient()->retrieveAgentSession('a/b c'));
    expect($seen['uri'])->toBe('/v1/agents/sessions/a%2Fb%20c');
})->group('transport');

it('requests an event stream and passes chunks to the callback', function () {
    $received = '';
    agentsClient()->streamAgentSessionEvents('sess_1', function ($curl, $chunk) use (&$received) {
        $received .= $chunk;

        return strlen($chunk);
    });

    $seen = echoed($received);
    expect($seen['uri'])->toBe('/v1/agents/sessions/sess_1/events?stream=true');
    expect($seen['accept'])->toBe('text/event-stream');
    expect($seen['beta'])->toBe('agents=v1');
})->group('transport');

it('restores caller headers after an agents request', function () {
    $client = agentsClient();
    $client->setHeader(['OpenAI-Beta' => 'responses_multi_agent=v1']);

    $client->retrieveAgentSession('sess_1');
    $afterAgents = echoed($client->request('GET', '/models'));

    expect($afterAgents['beta'])->toBe('responses_multi_agent=v1');
    expect($afterAgents['accept'])->not->toBe('text/event-stream');
})->group('transport');

it('does not leave the beta header behind when the request fails', function () {
    $client = new OpenAi();

    expect(fn () => $client->retrieveAgentSession('sess_1'))->toThrow(Exception::class, 'API key');

    $client->setApiKey('test-key');
    $client->setBaseURL('http://127.0.0.1:' . $GLOBALS['agents_port']);
    expect(echoed($client->request('GET', '/models'))['beta'])->toBeNull();
})->group('transport');
