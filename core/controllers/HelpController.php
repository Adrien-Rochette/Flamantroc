<?php
declare(strict_types=1);

final class HelpController extends Controller
{
    private const API_URL = 'https://api.mistral.ai/v1/chat/completions';

    public function reply(): void
    {
        $payload = $this->readPayload();
        $question = trim((string)($payload['question'] ?? ''));

        if ($question === '') {
            $this->jsonResponse(422, ['error' => 'Question vide.']);
            return;
        }

        if ($this->textLength($question) > 800) {
            $this->jsonResponse(422, ['error' => 'Question trop longue (800 caracteres max).']);
            return;
        }

        $apiKey = Env::get('MISTRAL_API_KEY');
        if ($apiKey === null || trim($apiKey) === '') {
            $this->jsonResponse(500, ['error' => "L'assistant n'est pas configure."]);
            return;
        }

        $model = Env::get('MISTRAL_MODEL', 'mistral-small-latest') ?? 'mistral-small-latest';
        $pageContext = $this->sanitizeText((string)($payload['pageContext'] ?? ''), 5000);
        $history = $this->sanitizeHistory($payload['history'] ?? []);

        $messages = [
            [
                'role' => 'system',
                'content' => "Tu es l'assistant du site Flamantroc. Reponds en francais, de facon concise et utile. "
                    . "Aide l'utilisateur a naviguer et comprendre ce qui est visible sur le site. "
                    . "Si l'information n'est pas dans le contexte fourni, dis-le clairement et propose une action concrete.",
            ],
        ];

        foreach ($history as $message) {
            $messages[] = $message;
        }

        $messages[] = [
            'role' => 'user',
            'content' => "Contexte de la page actuelle:\n"
                . ($pageContext !== '' ? $pageContext : 'Aucun contexte de page transmis.')
                . "\n\nQuestion utilisateur:\n"
                . $question,
        ];

        try {
            $apiPayload = json_encode([
                'model' => $model,
                'temperature' => 0.2,
                'max_tokens' => 400,
                'messages' => $messages,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

            $apiRaw = $this->callMistralApi($apiKey, $apiPayload);
            $apiData = json_decode($apiRaw, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            $this->jsonResponse(502, ['error' => "L'assistant est indisponible pour le moment."]);
            return;
        }

        $answer = trim((string)($apiData['choices'][0]['message']['content'] ?? ''));
        if ($answer === '') {
            $this->jsonResponse(502, ['error' => "Reponse invalide du service d'assistance."]);
            return;
        }

        $this->jsonResponse(200, ['answer' => $answer]);
    }

    private function readPayload(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function sanitizeHistory(mixed $history): array
    {
        if (!is_array($history)) {
            return [];
        }

        $result = [];

        foreach ($history as $item) {
            if (!is_array($item)) {
                continue;
            }

            $role = (string)($item['role'] ?? '');
            if ($role !== 'user' && $role !== 'assistant') {
                continue;
            }

            $content = $this->sanitizeText((string)($item['content'] ?? ''), 600);
            if ($content === '') {
                continue;
            }

            $result[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        if (count($result) > 8) {
            $result = array_slice($result, -8);
        }

        return $result;
    }

    private function sanitizeText(string $value, int $maxLength): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($value)) ?? '';
        if ($normalized === '') {
            return '';
        }

        if ($this->textLength($normalized) > $maxLength) {
            return $this->textSlice($normalized, $maxLength);
        }

        return $normalized;
    }

    private function textLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }

    private function textSlice(string $value, int $limit): string
    {
        return function_exists('mb_substr') ? mb_substr($value, 0, $limit) : substr($value, 0, $limit);
    }

    private function callMistralApi(string $apiKey, string $payload): string
    {
        $curl = curl_init(self::API_URL);
        if ($curl === false) {
            throw new RuntimeException("Initialisation cURL impossible.");
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 20,
        ]);

        $response = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if (!is_string($response) || $response === '') {
            throw new RuntimeException('Aucune reponse de Mistral. ' . $error);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException('Mistral HTTP ' . $httpCode);
        }

        return $response;
    }

    private function jsonResponse(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
    }
}
