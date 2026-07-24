<?php

return [
    'breadcrumb' => [
        'title' => 'Categories Create',
        'home' => 'Home',
        'categories' => 'Categories',
        'page' => 'Create',
    ],
    'content' => [
        'title' => 'Categories Create',
        'inputs' => [
            'parent' => 'Parent Category',
            'parent_note'=> 'Please note that if you want to set the category as main category then leave this field empty!',
            'name_en' => 'English Name',
            'name_ar' => 'Arabic Name',
            'description' => 'Description',
            'image' => 'Image',
            'placeholders' => [
                'choose_file' => 'Image',
            ]
        ],
        'submit' => 'Create',
    ],
];
