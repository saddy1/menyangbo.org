<?php

return [
    'required' => ':attribute अनिवार्य छ।',
    'email' => ':attribute मा मान्य इमेल ठेगाना लेख्नुहोस्।',
    'string' => ':attribute पाठ हुनुपर्छ।',
    'confirmed' => ':attribute को पुष्टि मिलेन।',
    'unique' => ':attribute पहिले नै प्रयोग भएको छ।',
    'exists' => 'छानिएको :attribute मान्य छैन।',
    'date' => ':attribute मान्य मिति हुनुपर्छ।',
    'integer' => ':attribute पूर्णाङ्क हुनुपर्छ।',
    'numeric' => ':attribute संख्या हुनुपर्छ।',
    'image' => ':attribute तस्बिर हुनुपर्छ।',
    'mimes' => ':attribute को फाइल प्रकार :values हुनुपर्छ।',
    'in' => 'छानिएको :attribute मान्य छैन।',
    'min' => ['string' => ':attribute कम्तीमा :min अक्षरको हुनुपर्छ।', 'numeric' => ':attribute कम्तीमा :min हुनुपर्छ।'],
    'max' => ['string' => ':attribute बढीमा :max अक्षरको हुनुपर्छ।', 'file' => ':attribute बढीमा :max किलोबाइट हुनुपर्छ।', 'numeric' => ':attribute बढीमा :max हुनुपर्छ।'],
    'attributes' => [
        'name' => 'नाम', 'email' => 'इमेल', 'password' => 'पासवर्ड',
        'description' => 'सुझाव', 'contact' => 'सम्पर्क', 'display_name' => 'पूरा नाम',
        'display_name_np' => 'नेपाली नाम', 'submitted_name' => 'पठाउने व्यक्तिको नाम',
        'spouse_name' => 'जीवनसाथीको नाम', 'gender' => 'लिङ्ग', 'pusta' => 'पुस्ता',
        'birth_date' => 'जन्म मिति', 'death_date_ad' => 'मृत्यु मिति', 'photo' => 'फोटो',
    ],
];
