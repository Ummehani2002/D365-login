<?php

namespace App\Services;

use RuntimeException;
use Throwable;

class D365GrnService extends D365ItemIssueService
{
    public function lookup(string $dataAreaId, string $purchId = '', string $vendName = '', string $projId = ''): array
    {
        $requestPayload = [
            'DataAreaId' => $dataAreaId,
            'PurchId'    => $purchId,
            'VendName'   => $vendName,
            'ProjId'     => $projId,
        ];

        try {
            return $this->postToConfiguredPath('grn_lookup_path', [
                '_request' => $requestPayload,
            ]);
        } catch (RuntimeException $e) {
            return $this->postToConfiguredPath('grn_lookup_path', $requestPayload);
        }
    }

    public function lookupLines(string $dataAreaId, string $purchId): array
    {
        $payload = [
            'DataAreaId' => $dataAreaId,
            'purchId'    => $purchId,
        ];

        try {
            return $this->postToConfiguredPath('grn_line_lookup_path', $payload);
        } catch (RuntimeException $e) {
            return $this->postToConfiguredPath('grn_line_lookup_path', [
                '_request' => $payload,
            ]);
        }
    }

    public function postPackingSlip(string $dataAreaId, array $header, array $lines): array
    {
        $wrappedPayload = [
            '_request' => [
                'DataAreaId' => $dataAreaId,
                'PurchPackHeader' => $header,
                'PurchPackLines' => $lines,
            ],
        ];
        $plainPayload = $wrappedPayload['_request'];

        $paths = array_values(array_unique(array_filter([
            (string) config('services.d365.grn_post_path'),
            '/api/services/TIWebServiceGroup/PurchPackService/Create',
            '/api/services/TIWebServiceGroup/PurchPackingSlipService/Create',
            '/api/services/TIWebServiceGroup/PurchPackSlipService/Create',
            '/api/services/TIWebServiceGroup/PurchPackingSlipPostingService/Create',
        ])));

        $payloads = [$wrappedPayload, $plainPayload];
        $lastError = null;
        $attempted = [];

        foreach ($paths as $path) {
            foreach ($payloads as $payload) {
                try {
                    return $this->postToPath($path, $payload);
                } catch (Throwable $e) {
                    $lastError = $e;
                    $msg = $e->getMessage();
                    $attempted[] = $path . ' => ' . $msg;
                    // Only try next path/payload when endpoint shape/path is wrong.
                    if (!str_contains($msg, 'status 404') && !str_contains($msg, 'status 405')) {
                        throw $e;
                    }
                }
            }
        }

        if ($lastError instanceof Throwable) {
            throw new RuntimeException(
                $lastError->getMessage() . ' | Tried paths: ' . implode(' || ', array_unique($attempted)),
                0,
                $lastError
            );
        }

        throw new RuntimeException('Unable to post GRN packing slip: no endpoint attempts succeeded.');
    }
}
