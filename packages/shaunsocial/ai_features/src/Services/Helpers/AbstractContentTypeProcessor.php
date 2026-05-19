<?php

namespace Packages\ShaunSocial\AiFeatures\Services\Helpers;

abstract class AbstractContentTypeProcessor
{
    protected function buildTaskResultFromProvider(array $response): array
    {
        $status = (bool) ($response['status'] ?? false);
        $normalized = $status ? $this->normalizeProviderData($response) : null;

        return [
            'status' => $status,
            'message' => (string) ($response['message'] ?? ''),
            'data' => $normalized,
            'error_code' => $status ? null : 'provider_response',
            'retryable' => ! $status,
        ];
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>|null
     */
    protected function normalizeProviderData(array $response): ?array
    {
        $data = $response['data'] ?? [];
        if (! is_array($data)) {
            $data = [];
        }

        $flagged = array_key_exists('flagged', $data) ? (bool) $data['flagged'] : null;
        $reasons = isset($data['reasons']) && is_array($data['reasons']) ? $data['reasons'] : [];
        $summary = isset($data['summary']) ? (string) $data['summary'] : '';
        $responseJson = null;

        if (isset($data['response']) && is_string($data['response'])) {
            $decoded = json_decode($data['response'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $responseJson = $decoded;
                if ($flagged === null && array_key_exists('flagged', $decoded)) {
                    $flagged = (bool) $decoded['flagged'];
                }
                if (empty($reasons) && isset($decoded['reasons']) && is_array($decoded['reasons'])) {
                    $reasons = $decoded['reasons'];
                }
                if ($summary === '' && isset($decoded['summary'])) {
                    $summary = (string) $decoded['summary'];
                }
            }
        }

        return [
            'flagged' => $flagged,
            'reasons' => $reasons,
            'summary' => $summary,
            'details' => $data,
            'response_json' => $responseJson,
        ];
    }
}
