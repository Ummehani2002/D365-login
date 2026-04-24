<?php

namespace App\Services;

class D365PurchReqService extends D365ItemIssueService
{
    public function postPurchReq(array $payload): array
    {
        return $this->postToConfiguredPath('purch_req_post_path', $payload);
    }
}
