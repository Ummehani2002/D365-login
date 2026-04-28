<?php

namespace App\Services;

class D365GrnService extends D365ItemIssueService
{
    public function lookupHeaders(array $payload): array
    {
        return $this->postToConfiguredPath('grn_headers_lookup_path', $payload);
    }

    public function lookupLines(array $payload): array
    {
        return $this->postToConfiguredPath('grn_lines_lookup_path', $payload);
    }

    public function postGrn(array $payload): array
    {
        return $this->postToConfiguredPath('grn_post_path', $payload);
    }
}
