<?php

require_once __DIR__ . '/../vendor/autoload.php';

class SwaggerEndpointTester
{
    private $baseUrl = 'http://localhost:8081';
    private $swaggerPath = __DIR__ . '/../storage/api-docs/api-docs.json';
    private $outputPath;
    private $client;
    private $accessToken;
    private $results = [];

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false
        ]);
        $this->outputPath = __DIR__ . '/../storage/logs/swagger_test_' . date('Y-m-d_H-i-s') . '.json';
    }

    public function run()
    {
        echo "Starting Swagger endpoint tests...\n";

        // 1. Get OAuth token
        $this->getOAuthToken();
        if (!$this->accessToken) {
            throw new \Exception("Failed to obtain access token");
        }
        echo "Access token obtained successfully.\n";

        // 2. Load Swagger spec
        $swagger = $this->loadSwaggerSpec();
        echo "Loaded Swagger specification.\n";

        // 3. Test each endpoint
        foreach ($swagger['paths'] as $path => $methods) {
            foreach ($methods as $method => $details) {
                // Skip oauth/token endpoint
                if (strpos($path, '/oauth/token') !== false) {
                    continue;
                }

                $this->testEndpoint($path, $method, $details);
            }
        }

        // 4. Save results
        $this->saveResults();
        echo "\nTest results saved to: " . $this->outputPath . "\n";
    }

    private function getOAuthToken()
    {
        try {
            $response = $this->client->post('/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => '4',
                    'client_secret' => 'CoB1cFspfzXv09srdUFolBLmNIqTf32nJX87SjdT',
                    'username' => 'test+bot@bankapi.com',
                    'password' => 'Password123',
                    'scope' => '',
                ]
            ]);

            echo "OAuth token response: " . $response->getBody() . "\n";
            $data = json_decode((string) $response->getBody(), true);
            
            if (!isset($data['access_token'])) {
                throw new \Exception("No access_token in response: " . json_encode($data));
            }
            
            $this->accessToken = $data['access_token'];
            echo "Token obtained successfully.\n";
        } catch (\Exception $e) {
            echo "Error getting OAuth token: " . $e->getMessage() . "\n";
            echo "Response status code: " . ($response->getStatusCode() ?? 'unknown') . "\n";
            throw $e;
        }
    }

    private function loadSwaggerSpec()
    {
        if (!file_exists($this->swaggerPath)) {
            throw new \Exception("Swagger spec not found at: " . $this->swaggerPath);
        }
        return json_decode(file_get_contents($this->swaggerPath), true);
    }

    private function testEndpoint($path, $method, $details)
    {
        $fullPath = str_replace(['{', '}'], ['1', ''], $path); // Replace path params with dummy values
        echo "\nTesting $method $fullPath\n";

        try {
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Accept' => 'application/json'
                ]
            ];

            // Add example request body if needed
            if (isset($details['requestBody'])) {
                $options['json'] = $this->generateDummyRequestBody($details['requestBody']);
            }

            $start = microtime(true);
            $response = $this->client->request(strtoupper($method), $fullPath, $options);
            $duration = round((microtime(true) - $start) * 1000);

            // Handle rate limiting
            if ($response->getStatusCode() === 429) {
                $retryAfter = $response->getHeader('Retry-After')[0] ?? 30;
                echo "Rate limited. Waiting {$retryAfter} seconds...\n";
                sleep((int)$retryAfter);
                return $this->testEndpoint($path, $method, $details);
            }

            $responseBody = (string)$response->getBody();
            $this->results[] = [
                'path' => $path,
                'method' => strtoupper($method),
                'status' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'response' => json_decode($responseBody, true) ?? $responseBody
            ];

            echo "Status: " . $response->getStatusCode() . " ({$duration}ms)\n";

        } catch (\Exception $e) {
            $this->results[] = [
                'path' => $path,
                'method' => strtoupper($method),
                'status' => 'ERROR',
                'error' => $e->getMessage()
            ];
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    private function generateDummyRequestBody($requestBody)
    {
        // Generate dummy data based on schema
        $schema = $requestBody['content']['application/json']['schema'] ?? [];
        if (isset($schema['properties'])) {
            $dummy = [];
            foreach ($schema['properties'] as $prop => $details) {
                $dummy[$prop] = $this->generateDummyValue($details);
            }
            return $dummy;
        }
        return [];
    }

    private function generateDummyValue($property)
    {
        switch ($property['type'] ?? '') {
            case 'string':
                return 'test_' . uniqid();
            case 'integer':
            case 'number':
                return 1;
            case 'boolean':
                return true;
            case 'array':
                return [];
            default:
                return null;
        }
    }

    private function saveResults()
    {
        file_put_contents(
            $this->outputPath,
            json_encode(['results' => $this->results], JSON_PRETTY_PRINT)
        );
    }
}

// Run tests
try {
    $tester = new SwaggerEndpointTester();
    $tester->run();
} catch (\Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}