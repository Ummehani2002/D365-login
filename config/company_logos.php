<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Company code → logo file stem
    |--------------------------------------------------------------------------
    |
    | Dashboard loads public/images/companies/{stem}.png (also .jpg, .webp, …).
    | If your D365 legal entity code differs from the logo filename stem, map it here.
    | Example: company id WM uses the same artwork as WIM.png → set 'WM' => 'WIM'.
    |
    */
    'logo_stem_aliases' => [
        'WM' => 'WIM',
    ],

];
