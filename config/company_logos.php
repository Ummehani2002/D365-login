<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Company code → logo file stem
    |--------------------------------------------------------------------------
    |
    | Dashboard loads public/images/companies/{stem}.png (also .jpg, .webp, …).
    | If the D365 legal entity code differs from the logo filename, map it here.
    |
    | Water in Motion: canonical file WM.png (not WIM.png).
    | Acacia Garden Center: canonical file GC.png (not AGC.png).
    |
    */
    'logo_stem_aliases' => [
        'WIM' => 'WM',
        'AGC' => 'GC',
    ],

];
