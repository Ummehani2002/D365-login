<?php

return [
    'modules' => [
        ['key' => 'super.access', 'label' => 'Super Admin - Full App Access', 'route_name' => null],
        ['key' => 'settings.access', 'label' => 'Admin - Settings Access (Except Token/Credentials)', 'route_name' => 'settings.index'],
        ['key' => 'modules.project-management.item-issue', 'label' => 'Project Management - Item Issue', 'route_name' => 'modules.project-management.item-issue'],
        ['key' => 'modules.procurement.purch-req', 'label' => 'Procurement & Sourcing - Purchase Requisition', 'route_name' => 'modules.procurement.purch-req'],
        ['key' => 'modules.procurement.grn', 'label' => 'Procurement & Sourcing - Goods Receive Note', 'route_name' => 'modules.procurement.grn'],
    ],
];

