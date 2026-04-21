<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
'microsoft' => [
    'client_id' => env('MICROSOFT_CLIENT_ID'),
    'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
    'redirect' => env('MICROSOFT_REDIRECT_URI'),
    'tenant' => env('MICROSOFT_TENANT_ID', 'common'),
    // Add these for Azure AD
    'azure_app_id' => env('MICROSOFT_CLIENT_ID'),
    'azure_app_secret' => env('MICROSOFT_CLIENT_SECRET'),
    'azure_redirect' => env('MICROSOFT_REDIRECT_URI'),


    
    // Optional: Specify scopes
    'scopes' => [
        'openid',
        'profile',
        'email',
        'User.Read',
    ],
],

'd365' => [
    'tenant_id' => env('D365_TENANT_ID', env('MICROSOFT_TENANT_ID')),
    'client_id' => env('D365_CLIENT_ID', env('MICROSOFT_CLIENT_ID')),
    'client_secret' => env('D365_CLIENT_SECRET', env('MICROSOFT_CLIENT_SECRET')),
    'scope' => env('D365_SCOPE', 'https://api.businesscentral.dynamics.com/.default'),
    'base_url' => env('D365_BASE_URL'),
    'companies_path' => env('D365_COMPANIES_PATH', '/companies'),
    'projects_path' => env('D365_PROJECTS_PATH', '/projects'),
    'item_lookup_path' => env('D365_ITEM_LOOKUP_PATH', '/item-lookup'),
    'project_lookup_path' => env('D365_PROJECT_LOOKUP_PATH', '/project-lookup'),
    'item_issue_post_path' => env('D365_ITEM_ISSUE_POST_PATH', '/item-issue-post'),
    'item_onhand_path' => env('D365_ITEM_ONHAND_PATH', '/item-onhand'),
    'unit_lookup_path' => env('D365_UNIT_LOOKUP_PATH', '/unit-lookup'),
],

'webapp' => [
    'api_bearer_token' => env('WEBAPP_API_BEARER_TOKEN'),
],
];
