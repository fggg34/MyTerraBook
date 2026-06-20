<?php

$linkRepeater = [
    ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
    ['key' => 'href', 'type' => 'text', 'label' => 'Link'],
];

$faqItemRepeater = [
    ['key' => 'num', 'type' => 'text', 'label' => 'Number'],
    ['key' => 'question', 'type' => 'text', 'label' => 'Question', 'required' => true, 'columnSpanFull' => true],
    ['key' => 'answer', 'type' => 'textarea', 'label' => 'Answer', 'required' => true, 'columnSpanFull' => true],
    ['key' => 'open', 'type' => 'toggle', 'label' => 'Open by default'],
];

$heroFields = [
    ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
];

$sectionHeaderFields = [
    ['key' => 'tag', 'type' => 'text', 'label' => 'Section tag'],
    ['key' => 'heading', 'type' => 'textarea', 'label' => 'Section heading', 'columnSpanFull' => true],
];

$ctaFields = [
    ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
    ['key' => 'primaryLabel', 'type' => 'text', 'label' => 'Primary label'],
    ['key' => 'primaryHref', 'type' => 'text', 'label' => 'Primary link'],
    ['key' => 'secondaryLabel', 'type' => 'text', 'label' => 'Secondary label'],
    ['key' => 'secondaryHref', 'type' => 'text', 'label' => 'Secondary link'],
];

$globalSeoFields = [
    ['key' => 'siteName', 'type' => 'text', 'label' => 'Site name'],
    ['key' => 'titleSuffix', 'type' => 'text', 'label' => 'Title suffix', 'helperText' => 'Appended to page titles, e.g. " | MyTerraBook"'],
    ['key' => 'defaultDescription', 'type' => 'textarea', 'label' => 'Default meta description', 'rows' => 3, 'columnSpanFull' => true],
    ['key' => 'defaultOgImage', 'type' => 'image', 'label' => 'Default share image (OG)'],
];

$seoFields = [
    ['key' => 'title', 'type' => 'text', 'label' => 'Meta title', 'helperText' => 'Leave empty to use page default', 'columnSpanFull' => true],
    ['key' => 'description', 'type' => 'textarea', 'label' => 'Meta description', 'rows' => 3, 'columnSpanFull' => true],
    ['key' => 'ogImage', 'type' => 'image', 'label' => 'Share image (OG)'],
    ['key' => 'robots', 'type' => 'select', 'label' => 'Search indexing', 'options' => ['index' => 'Allow indexing', 'noindex' => 'Hide from search']],
];

$pageSeoSection = ['label' => 'SEO', 'fields' => $seoFields];

$iconImageField = ['key' => 'iconImage', 'type' => 'image', 'label' => 'Custom icon image (optional)', 'allowSvg' => true, 'helperText' => 'Overrides the preset icon'];

$trustIconOptions = ['star' => 'Star rating', 'check' => 'Check', 'shield' => 'Shield', 'phone' => 'Phone'];
$whyFeatureIconOptions = ['campervan' => 'Campervan', 'car' => 'Car', 'house' => 'Guesthouse', 'host' => 'Host', 'shield' => 'Shield', 'phone' => 'Phone'];
$specTypeOptions = ['gearbox' => 'Gearbox', 'seat' => 'Seats', 'bed' => 'Beds', 'bag' => 'Bags', 'drive' => 'Drive', 'wifi' => 'Wi-Fi', 'room' => 'Rooms', 'bath' => 'Bath'];
$socialIconOptions = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'twitter' => 'Twitter / X', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'custom' => 'Custom SVG'];

$trustItemFields = [
    ['key' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => $trustIconOptions],
    $iconImageField,
    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
    ['key' => 'subtitle', 'type' => 'text', 'label' => 'Subtitle'],
    ['key' => 'stars', 'type' => 'number', 'label' => 'Star count', 'visibleWhen' => ['field' => 'icon', 'value' => 'star']],
];

$whyFeatureFields = [
    ['key' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => $whyFeatureIconOptions],
    $iconImageField,
    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
    ['key' => 'description', 'type' => 'textarea', 'label' => 'Description', 'columnSpanFull' => true],
    ['key' => 'expandedText', 'type' => 'textarea', 'label' => 'Expanded text', 'columnSpanFull' => true],
];

$productSpecFields = [
    ['key' => 'type', 'type' => 'select', 'label' => 'Spec icon', 'options' => $specTypeOptions],
    ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
];

$productCardFields = [
    ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
    ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
    ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Photo alt text'],
    ['key' => 'badge', 'type' => 'text', 'label' => 'Badge'],
    ['key' => 'price', 'type' => 'text', 'label' => 'Price'],
    ['key' => 'per', 'type' => 'text', 'label' => 'Per (day/night)'],
    ['key' => 'href', 'type' => 'text', 'label' => 'Link'],
    ['key' => 'specs', 'type' => 'repeater', 'label' => 'Specs', 'fields' => $productSpecFields, 'columnSpanFull' => true],
];

$howStepFields = [
    ['key' => 'num', 'type' => 'text', 'label' => 'Step number'],
    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
    ['key' => 'description', 'type' => 'textarea', 'label' => 'Description', 'columnSpanFull' => true],
    ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
    ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Photo alt text'],
    ['key' => 'tags', 'type' => 'tags', 'label' => 'Tags'],
];

$blogPostFields = [
    ['key' => 'slug', 'type' => 'text', 'label' => 'Slug'],
    ['key' => 'featured', 'type' => 'toggle', 'label' => 'Featured layout'],
    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
    ['key' => 'description', 'type' => 'textarea', 'label' => 'Description', 'columnSpanFull' => true],
    ['key' => 'meta', 'type' => 'text', 'label' => 'Meta line'],
    ['key' => 'metaExtra', 'type' => 'text', 'label' => 'Meta extra'],
    ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
    ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Photo alt text'],
    ['key' => 'kicker', 'type' => 'text', 'label' => 'Kicker'],
    ['key' => 'aurora', 'type' => 'toggle', 'label' => 'Aurora effect (no image)'],
];

$reviewCardFields = [
    ['key' => 'quote', 'type' => 'textarea', 'label' => 'Quote', 'columnSpanFull' => true],
    ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
    ['key' => 'fill', 'type' => 'text', 'label' => 'Card colour (hex)'],
    ['key' => 'rot', 'type' => 'text', 'label' => 'Rotation'],
    ['key' => 'ty', 'type' => 'text', 'label' => 'Vertical offset'],
];

$hostReviewFields = [
    ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
    ['key' => 'role', 'type' => 'text', 'label' => 'Role'],
    ['key' => 'quote', 'type' => 'textarea', 'label' => 'Quote', 'columnSpanFull' => true],
    ['key' => 'fill', 'type' => 'text', 'label' => 'Card colour (hex)'],
];

$listingTabFields = [
    ['key' => 'id', 'type' => 'text', 'label' => 'Tab ID'],
    ['key' => 'label', 'type' => 'text', 'label' => 'Tab label'],
];

$trustPointFields = [
    ['key' => 'html', 'type' => 'textarea', 'label' => 'HTML', 'columnSpanFull' => true],
];

$bookingStepFields = [
    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
    ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
    ['key' => 'tag', 'type' => 'text', 'label' => 'Tag (optional)'],
];

$proofStackFields = [
    ['key' => 'type', 'type' => 'select', 'label' => 'Type', 'options' => ['stat' => 'Stat', 'photo' => 'Photo']],
    ['key' => 'variant', 'type' => 'select', 'label' => 'Stat colour', 'options' => ['clay' => 'Clay', 'moss' => 'Moss', 'ochre' => 'Ochre', 'espresso' => 'Espresso'], 'visibleWhen' => ['field' => 'type', 'value' => 'stat']],
    ['key' => 'big', 'type' => 'text', 'label' => 'Stat value', 'visibleWhen' => ['field' => 'type', 'value' => 'stat']],
    ['key' => 'desc', 'type' => 'textarea', 'label' => 'Stat description', 'columnSpanFull' => true, 'visibleWhen' => ['field' => 'type', 'value' => 'stat']],
    ['key' => 'name', 'type' => 'text', 'label' => 'Name', 'visibleWhen' => ['field' => 'type', 'value' => 'photo']],
    ['key' => 'role', 'type' => 'text', 'label' => 'Role', 'visibleWhen' => ['field' => 'type', 'value' => 'photo']],
    ['key' => 'image', 'type' => 'image', 'label' => 'Photo', 'visibleWhen' => ['field' => 'type', 'value' => 'photo']],
];

$proofStatFields = [
    ['key' => 'tall_name', 'type' => 'text', 'label' => 'Featured host name', 'path' => 'tall.name'],
    ['key' => 'tall_role', 'type' => 'text', 'label' => 'Featured host role', 'path' => 'tall.role'],
    ['key' => 'tall_image', 'type' => 'image', 'label' => 'Featured host photo', 'path' => 'tall.image'],
    ['key' => 'stack', 'type' => 'repeater', 'label' => 'Stack items', 'fields' => $proofStackFields, 'columnSpanFull' => true],
];

$searchRoutingFields = [
    ['key' => 'id', 'type' => 'text', 'label' => 'Type ID'],
    ['key' => 'route', 'type' => 'text', 'label' => 'Route path'],
    ['key' => 'hcatMode', 'type' => 'select', 'label' => 'Category mode', 'options' => ['vehicle' => 'Vehicle', 'guesthouse' => 'Guesthouse']],
    ['key' => 'categoryNames', 'type' => 'tags', 'label' => 'Category names', 'columnSpanFull' => true],
    ['key' => 'breadcrumb', 'type' => 'text', 'label' => 'Breadcrumb label'],
    ['key' => 'unitSingular', 'type' => 'text', 'label' => 'Unit singular'],
    ['key' => 'unitPlural', 'type' => 'text', 'label' => 'Unit plural'],
    ['key' => 'defaultSeats', 'type' => 'number', 'label' => 'Default seats'],
    ['key' => 'defaultSleeps', 'type' => 'number', 'label' => 'Default sleeps'],
    ['key' => 'defaultBags', 'type' => 'number', 'label' => 'Default bags'],
];

$searchCopyFields = [
    ['key' => 'titleLead', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
    ['key' => 'loadMoreLabel', 'type' => 'text', 'label' => 'Load more label'],
];

$listingSectionDescriptionFields = [
    ['key' => 'amenities', 'type' => 'textarea', 'label' => 'Amenities / features section', 'rows' => 2, 'columnSpanFull' => true],
    ['key' => 'optionalExtras', 'type' => 'textarea', 'label' => 'Optional extras section (vehicles only)', 'rows' => 2, 'columnSpanFull' => true],
    ['key' => 'conditions', 'type' => 'textarea', 'label' => 'Rental conditions / house rules section', 'rows' => 2, 'columnSpanFull' => true],
    ['key' => 'pickupDropoff', 'type' => 'textarea', 'label' => 'Pick-up & drop-off section (vehicles only)', 'rows' => 2, 'columnSpanFull' => true],
    ['key' => 'roomDetails', 'type' => 'textarea', 'label' => 'Room details section (guesthouses only)', 'rows' => 2, 'columnSpanFull' => true],
    ['key' => 'location', 'type' => 'textarea', 'label' => 'Location section', 'rows' => 2, 'columnSpanFull' => true],
];

$listingLabelFields = [
    ['key' => 'id', 'type' => 'text', 'label' => 'Type ID'],
    ['key' => 'categoryNames', 'type' => 'tags', 'label' => 'Category names', 'columnSpanFull' => true],
    ['key' => 'archiveRoute', 'type' => 'text', 'label' => 'Archive route'],
    ['key' => 'archiveLabel', 'type' => 'text', 'label' => 'Archive label'],
    ['key' => 'dateStartLabel', 'type' => 'text', 'label' => 'Start date label'],
    ['key' => 'dateEndLabel', 'type' => 'text', 'label' => 'End date label'],
    ['key' => 'rateUnit', 'type' => 'text', 'label' => 'Rate unit'],
    ['key' => 'rateLabelDefault', 'type' => 'text', 'label' => 'Rate label'],
    ['key' => 'bookCta', 'type' => 'text', 'label' => 'Book CTA'],
    ['key' => 'similarTitle', 'type' => 'text', 'label' => 'Similar title'],
    ['key' => 'guestPhotosTitle', 'type' => 'text', 'label' => 'Guest photos title'],
    ['key' => 'reviewsTitle', 'type' => 'text', 'label' => 'Reviews title'],
    ['key' => 'bookingModalTitle', 'type' => 'text', 'label' => 'Booking modal title'],
    ['key' => 'bookingModalLead', 'type' => 'textarea', 'label' => 'Booking modal lead', 'columnSpanFull' => true],
    ['key' => 'bookingModalFootnote', 'type' => 'text', 'label' => 'Booking modal footnote', 'columnSpanFull' => true, 'helperText' => 'Small text beside the modal CTA button.'],
    ['key' => 'faqLead', 'type' => 'textarea', 'label' => 'FAQ lead', 'columnSpanFull' => true],
];

return [
    'pages' => [
        'global' => [
            'label' => 'Global chrome',
            'group' => 'Global',
            'preview_route' => '/',
            'sort_order' => 0,
            'sections' => [
                'branding' => [
                    'label' => 'Branding & logo',
                    'fields' => [
                        ['key' => 'logoMode', 'type' => 'select', 'label' => 'Logo mode', 'options' => ['text' => 'Text', 'image' => 'Image']],
                        ['key' => 'prefix', 'type' => 'text', 'label' => 'Text prefix'],
                        ['key' => 'accent', 'type' => 'text', 'label' => 'Accent text'],
                        ['key' => 'suffix', 'type' => 'text', 'label' => 'Text suffix'],
                        ['key' => 'logoImage', 'type' => 'image', 'label' => 'Logo image', 'allowSvg' => true],
                        [
                            'key' => 'favicon',
                            'type' => 'image',
                            'label' => 'Favicon image',
                            'allowSvg' => true,
                            'acceptedFileTypes' => [
                                'image/png',
                                'image/jpeg',
                                'image/webp',
                                'image/svg+xml',
                                'image/x-icon',
                                'image/vnd.microsoft.icon',
                            ],
                            'helperText' => 'Square icon shown in the browser tab. Use PNG, SVG, or ICO (32×32 or 64×64 recommended).',
                        ],
                    ],
                ],
                'topbar' => [
                    'label' => 'Top bar',
                    'fields' => [
                        ['key' => 'text', 'type' => 'text', 'label' => 'Banner text (desktop)', 'columnSpanFull' => true],
                        ['key' => 'mobileText', 'type' => 'text', 'label' => 'Banner text (mobile)', 'helperText' => 'Shorter copy for small screens. Uses desktop text if empty.', 'columnSpanFull' => true],
                        ['key' => 'linkLabel', 'type' => 'text', 'label' => 'Link label'],
                        ['key' => 'linkHref', 'type' => 'text', 'label' => 'Link URL'],
                    ],
                ],
                'header' => [
                    'label' => 'Header navigation',
                    'fields' => [
                        ['key' => 'navLinks', 'type' => 'repeater', 'label' => 'Nav links', 'fields' => $linkRepeater, 'columnSpanFull' => true],
                        ['key' => 'ctaLabel', 'type' => 'text', 'label' => 'CTA label'],
                        ['key' => 'ctaHref', 'type' => 'text', 'label' => 'CTA link'],
                        ['key' => 'currencyLabel', 'type' => 'text', 'label' => 'Currency label'],
                        ['key' => 'signInLabel', 'type' => 'text', 'label' => 'Sign in label'],
                        ['key' => 'signInHref', 'type' => 'text', 'label' => 'Sign in link'],
                    ],
                ],
                'footer' => [
                    'label' => 'Footer',
                    'fields' => [
                        ['key' => 'tagline', 'type' => 'textarea', 'label' => 'Tagline', 'columnSpanFull' => true],
                        ['key' => 'address', 'type' => 'textarea', 'label' => 'Address', 'columnSpanFull' => true],
                        ['key' => 'copyright', 'type' => 'text', 'label' => 'Copyright'],
                        ['key' => 'locale', 'type' => 'text', 'label' => 'Locale label'],
                        ['key' => 'currency', 'type' => 'text', 'label' => 'Currency label'],
                        ['key' => 'columns', 'type' => 'repeater', 'label' => 'Columns', 'columnSpanFull' => true, 'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Column title'],
                            ['key' => 'links', 'type' => 'repeater', 'label' => 'Links', 'fields' => [
                                ...$linkRepeater,
                                ['key' => 'badge', 'type' => 'text', 'label' => 'Badge'],
                            ]],
                        ]],
                        ['key' => 'legal', 'type' => 'repeater', 'label' => 'Legal links', 'fields' => $linkRepeater, 'columnSpanFull' => true],
                        ['key' => 'social', 'type' => 'repeater', 'label' => 'Social links', 'fields' => [
                            ...$linkRepeater,
                            ['key' => 'icon', 'type' => 'select', 'label' => 'Icon preset', 'options' => $socialIconOptions],
                            ['key' => 'iconImage', 'type' => 'file', 'label' => 'Custom icon (SVG)', 'helperText' => 'Optional SVG when preset is Custom.', 'visibleWhen' => ['field' => 'icon', 'value' => 'custom']],
                        ], 'columnSpanFull' => true],
                    ],
                ],
                'newsSection' => [
                    'label' => 'Newsletter block',
                    'fields' => [
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'columnSpanFull' => true],
                        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Heading accent'],
                        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
                        ['key' => 'backgroundImage', 'type' => 'image', 'label' => 'Background image'],
                        ['key' => 'placeholder', 'type' => 'text', 'label' => 'Email placeholder'],
                        ['key' => 'successMessage', 'type' => 'text', 'label' => 'Success message'],
                    ],
                ],
                'faqSection' => [
                    'label' => 'FAQ block (layouts)',
                    'fields' => [
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading'],
                        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
                        ['key' => 'phone', 'type' => 'text', 'label' => 'Phone'],
                        ['key' => 'email', 'type' => 'text', 'label' => 'Email'],
                        ['key' => 'items', 'type' => 'repeater', 'label' => 'FAQ items', 'fields' => $faqItemRepeater, 'columnSpanFull' => true],
                    ],
                ],
                'seo' => ['label' => 'SEO defaults', 'fields' => $globalSeoFields],
            ],
        ],
        'home' => [
            'label' => 'Homepage',
            'group' => 'Marketing',
            'preview_route' => '/',
            'sort_order' => 1,
            'sections' => [
                'hero' => [
                    'label' => 'Hero',
                    'fields' => [
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading (desktop)', 'columnSpanFull' => true],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle (desktop)', 'columnSpanFull' => true],
                        ['key' => 'backgroundImage', 'type' => 'image', 'label' => 'Background image (desktop)'],
                        ['key' => 'mobileHeading', 'type' => 'text', 'label' => 'Heading (mobile)', 'helperText' => 'Uses desktop heading if empty.', 'columnSpanFull' => true],
                        ['key' => 'mobileSubtitle', 'type' => 'textarea', 'label' => 'Subtitle (mobile)', 'helperText' => 'Uses desktop subtitle if empty.', 'columnSpanFull' => true],
                        ['key' => 'mobileBackgroundImage', 'type' => 'image', 'label' => 'Background image (mobile)', 'helperText' => 'Uses desktop image if empty.'],
                        ['key' => 'tabs', 'type' => 'repeater', 'label' => 'Search tabs', 'fields' => [
                            ['key' => 'id', 'type' => 'text', 'label' => 'ID'],
                            ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                        ], 'columnSpanFull' => true],
                        ['key' => 'searchLabel', 'type' => 'text', 'label' => 'Search button'],
                        ['key' => 'footerHint', 'type' => 'text', 'label' => 'Footer hint'],
                        ['key' => 'footerLinkLabel', 'type' => 'text', 'label' => 'Footer link label'],
                        ['key' => 'footerLinkHref', 'type' => 'text', 'label' => 'Footer link URL'],
                        ['key' => 'experienceLabel', 'type' => 'text', 'label' => 'Experience label', 'columnSpanFull' => true],
                        ['key' => 'experiencePlaceholder', 'type' => 'text', 'label' => 'Experience placeholder'],
                        ['key' => 'datesLabel', 'type' => 'text', 'label' => 'Dates label'],
                        ['key' => 'startDateLabel', 'type' => 'text', 'label' => 'Start date label'],
                        ['key' => 'endDateLabel', 'type' => 'text', 'label' => 'End date label'],
                        ['key' => 'travelersLabel', 'type' => 'text', 'label' => 'Travelers label'],
                        ['key' => 'travelersValue', 'type' => 'text', 'label' => 'Travelers default value'],
                    ],
                ],
                'trustItems' => [
                    'label' => 'Trust strip',
                    'fields' => [
                        ['key' => 'trustItems', 'type' => 'repeater', 'label' => 'Items', 'fields' => $trustItemFields, 'columnSpanFull' => true, 'isRootList' => true],
                    ],
                ],
                'rentSection' => [
                    'label' => 'What we rent',
                    'fields' => [
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading'],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                        ['key' => 'cards', 'type' => 'repeater', 'label' => 'Cards', 'fields' => [
                            ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
                            ['key' => 'tagline', 'type' => 'text', 'label' => 'Tagline'],
                            ['key' => 'listingCount', 'type' => 'text', 'label' => 'Listing count'],
                            ['key' => 'href', 'type' => 'text', 'label' => 'Link'],
                            ['key' => 'image', 'type' => 'image', 'label' => 'Image'],
                            ['key' => 'alt', 'type' => 'text', 'label' => 'Image alt'],
                        ], 'columnSpanFull' => true],
                    ],
                ],
                'whySection' => [
                    'label' => 'Why MyTerraBook',
                    'fields' => [
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'columnSpanFull' => true],
                        ['key' => 'subheading', 'type' => 'textarea', 'label' => 'Subheading', 'columnSpanFull' => true],
                        ['key' => 'photo', 'type' => 'image', 'label' => 'Photo'],
                        ['key' => 'badge_rating', 'type' => 'text', 'label' => 'Badge rating', 'path' => 'whySection.badge.rating'],
                        ['key' => 'badge_reviewBold', 'type' => 'text', 'label' => 'Badge bold text', 'path' => 'whySection.badge.reviewBold'],
                        ['key' => 'badge_reviewRest', 'type' => 'text', 'label' => 'Badge rest text', 'path' => 'whySection.badge.reviewRest'],
                        ['key' => 'featuresLeft', 'type' => 'repeater', 'label' => 'Left column features', 'path' => 'whySection.featuresLeft', 'fields' => $whyFeatureFields, 'columnSpanFull' => true],
                        ['key' => 'featuresRight', 'type' => 'repeater', 'label' => 'Right column features', 'path' => 'whySection.featuresRight', 'fields' => $whyFeatureFields, 'columnSpanFull' => true],
                    ],
                ],
                'picksSection' => [
                    'label' => 'Hand-picked',
                    'fields' => [
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading'],
                        ['key' => 'tabs', 'type' => 'repeater', 'label' => 'Tabs', 'fields' => [
                            ['key' => 'id', 'type' => 'text', 'label' => 'Tab ID'],
                            ['key' => 'label', 'type' => 'text', 'label' => 'Tab label'],
                            ['key' => 'allLabel', 'type' => 'text', 'label' => 'View all label'],
                            ['key' => 'allHref', 'type' => 'text', 'label' => 'View all link'],
                        ], 'columnSpanFull' => true],
                        ['key' => 'camperPicks', 'type' => 'repeater', 'label' => 'Campervan cards', 'path' => 'picksSection.items.camper', 'fields' => $productCardFields, 'columnSpanFull' => true],
                        ['key' => 'carPicks', 'type' => 'repeater', 'label' => 'Car cards', 'path' => 'picksSection.items.car', 'fields' => $productCardFields, 'columnSpanFull' => true],
                    ],
                ],
                'howSection' => [
                    'label' => 'How it works',
                    'fields' => [
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'columnSpanFull' => true],
                        ['key' => 'steps', 'type' => 'repeater', 'label' => 'Steps', 'fields' => $howStepFields, 'columnSpanFull' => true],
                    ],
                ],
                'staySection' => [
                    'label' => 'Guesthouses',
                    'fields' => [
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading'],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                        ['key' => 'allLabel', 'type' => 'text', 'label' => 'View all label'],
                        ['key' => 'allHref', 'type' => 'text', 'label' => 'View all link'],
                        ['key' => 'cards', 'type' => 'repeater', 'label' => 'Fallback cards', 'fields' => $productCardFields, 'columnSpanFull' => true],
                    ],
                ],
                'blogSection' => [
                    'label' => 'Blog bento',
                    'fields' => [
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading'],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                        ['key' => 'allLabel', 'type' => 'text', 'label' => 'View all label'],
                        ['key' => 'allHref', 'type' => 'text', 'label' => 'View all link'],
                        ['key' => 'posts', 'type' => 'repeater', 'label' => 'Fallback posts', 'fields' => $blogPostFields, 'columnSpanFull' => true],
                    ],
                ],
                'hostCtaSection' => [
                    'label' => 'Host CTA',
                    'fields' => [
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'columnSpanFull' => true],
                        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
                        ['key' => 'earnAmount', 'type' => 'text', 'label' => 'Earn amount'],
                        ['key' => 'earnNote', 'type' => 'textarea', 'label' => 'Earn note', 'columnSpanFull' => true],
                        ['key' => 'points', 'type' => 'tags', 'label' => 'Bullet points'],
                        ['key' => 'primaryLabel', 'type' => 'text', 'label' => 'Primary CTA'],
                        ['key' => 'primaryHref', 'type' => 'text', 'label' => 'Primary link'],
                        ['key' => 'secondaryLabel', 'type' => 'text', 'label' => 'Secondary CTA'],
                        ['key' => 'secondaryHref', 'type' => 'text', 'label' => 'Secondary link'],
                        ['key' => 'houseImage', 'type' => 'image', 'label' => 'House photo'],
                        ['key' => 'vanImage', 'type' => 'image', 'label' => 'Van photo'],
                        ['key' => 'chipAmount', 'type' => 'text', 'label' => 'Chip amount'],
                        ['key' => 'chipLabel', 'type' => 'text', 'label' => 'Chip label'],
                    ],
                ],
                'reviewsSection' => [
                    'label' => 'Reviews',
                    'fields' => [
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading'],
                        ['key' => 'googleEnabled', 'type' => 'toggle', 'label' => 'Connect Google Reviews', 'helperText' => 'Pull live rating and reviews from your Google Business profile. Requires a Google Maps API key in Global Configuration.'],
                        ['key' => 'googlePlaceId', 'type' => 'text', 'label' => 'Google Place ID', 'helperText' => 'Find this in Google Maps → your business → Share → Embed a map, or use the Place ID finder.', 'visibleWhen' => ['field' => 'googleEnabled', 'value' => true], 'columnSpanFull' => true],
                        ['key' => 'rating', 'type' => 'text', 'label' => 'Rating (demo)', 'visibleWhen' => ['field' => 'googleEnabled', 'value' => false]],
                        ['key' => 'ratingCount', 'type' => 'text', 'label' => 'Rating count (demo)', 'visibleWhen' => ['field' => 'googleEnabled', 'value' => false]],
                        ['key' => 'reviews', 'type' => 'repeater', 'label' => 'Demo review cards', 'helperText' => 'Shown when Google Reviews is off or cannot be loaded.', 'fields' => $reviewCardFields, 'visibleWhen' => ['field' => 'googleEnabled', 'value' => false], 'columnSpanFull' => true],
                    ],
                ],
                'guestHousesHighlight' => [
                    'label' => 'Guest houses highlight',
                    'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                        ['key' => 'featured_slugs', 'type' => 'tags', 'label' => 'Featured slugs'],
                        ['key' => 'ctaLabel', 'type' => 'text', 'label' => 'CTA label'],
                        ['key' => 'ctaHref', 'type' => 'text', 'label' => 'CTA link'],
                    ],
                ],
                'seo' => $pageSeoSection,
            ],
        ],
        'about' => [
            'label' => 'About',
            'group' => 'Marketing',
            'preview_route' => '/about',
            'sort_order' => 2,
            'sections' => [
                'hero' => ['label' => 'Hero', 'fields' => array_merge($heroFields, [
                    ['key' => 'primaryLabel', 'type' => 'text', 'label' => 'Primary button label'],
                    ['key' => 'primaryHref', 'type' => 'text', 'label' => 'Primary button link'],
                    ['key' => 'secondaryLabel', 'type' => 'text', 'label' => 'Secondary button label'],
                    ['key' => 'secondaryHref', 'type' => 'text', 'label' => 'Secondary button link'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Hero image'],
                    ['key' => 'pinTitle', 'type' => 'text', 'label' => 'Location pin title'],
                    ['key' => 'pinSubtitle', 'type' => 'text', 'label' => 'Location pin subtitle'],
                ])],
                'storySection' => ['label' => 'Story section header', 'fields' => $sectionHeaderFields],
                'storyBlocks' => ['label' => 'Story chapters', 'fields' => [
                    ['key' => 'storyBlocks', 'type' => 'repeater', 'label' => 'Chapters', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
                        ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                        ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Photo alt text'],
                    ]],
                ]],
                'storyBody' => ['label' => 'Story body (rich text)', 'fields' => [
                    ['key' => 'body', 'type' => 'richtext', 'label' => 'Body', 'columnSpanFull' => true, 'isRoot' => true, 'helperText' => 'Optional. When story chapters are empty, paragraph text from this field is used instead.'],
                ]],
                'stats' => ['label' => 'Stats', 'fields' => [
                    ['key' => 'stats', 'type' => 'repeater', 'label' => 'Stats', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'value', 'type' => 'text', 'label' => 'Value'],
                        ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                        ['key' => 'sub', 'type' => 'text', 'label' => 'Subtext'],
                    ]],
                ]],
                'valuesSection' => ['label' => 'Values section header', 'fields' => $sectionHeaderFields],
                'pillars' => ['label' => 'Values', 'fields' => [
                    ['key' => 'pillars', 'type' => 'repeater', 'label' => 'Pillars', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => ['shield' => 'Shield', 'price' => 'Price', 'route' => 'Route']],
                        $iconImageField,
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
                    ]],
                ]],
                'offeringsSection' => ['label' => 'Offerings section header', 'fields' => $sectionHeaderFields],
                'cta' => ['label' => 'Bottom CTA', 'fields' => $ctaFields],
                'seo' => $pageSeoSection,
            ],
        ],
        'faq' => [
            'label' => 'FAQ',
            'group' => 'Marketing',
            'preview_route' => '/faq',
            'sort_order' => 3,
            'sections' => [
                'hero' => ['label' => 'Hero', 'fields' => array_merge($heroFields, [
                    ['key' => 'searchPlaceholder', 'type' => 'text', 'label' => 'Search placeholder', 'columnSpanFull' => true],
                ])],
                'stats' => ['label' => 'Support highlights', 'fields' => [
                    ['key' => 'stats', 'type' => 'repeater', 'label' => 'Stats', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'value', 'type' => 'text', 'label' => 'Value'],
                        ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                    ]],
                ]],
                'items' => ['label' => 'FAQ items', 'fields' => [
                    ['key' => 'items', 'type' => 'repeater', 'label' => 'Items', 'fields' => $faqItemRepeater, 'columnSpanFull' => true, 'isRootList' => true],
                ]],
                'categories' => ['label' => 'Category tabs', 'fields' => [
                    ['key' => 'categories', 'type' => 'repeater', 'label' => 'Categories', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'id', 'type' => 'text', 'label' => 'ID'],
                        ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                        ['key' => 'nums', 'type' => 'tags', 'label' => 'Question numbers', 'columnSpanFull' => true],
                    ]],
                ]],
                'helpCard' => ['label' => 'Sidebar help card', 'fields' => [
                    ['key' => 'tag', 'type' => 'text', 'label' => 'Tag'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                    ['key' => 'body', 'type' => 'textarea', 'label' => 'Body', 'columnSpanFull' => true],
                    ['key' => 'phone', 'type' => 'text', 'label' => 'Phone'],
                    ['key' => 'email', 'type' => 'text', 'label' => 'Email'],
                    ['key' => 'buttonLabel', 'type' => 'text', 'label' => 'Button label'],
                    ['key' => 'buttonHref', 'type' => 'text', 'label' => 'Button link'],
                ]],
                'quickLinks' => ['label' => 'Sidebar quick links', 'fields' => [
                    ['key' => 'quickLinks', 'type' => 'repeater', 'label' => 'Links', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                        ['key' => 'href', 'type' => 'text', 'label' => 'Link'],
                        ['key' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => ['chat' => 'Chat', 'book' => 'Book', 'home' => 'Home']],
                        $iconImageField,
                    ]],
                ]],
                'emptyState' => ['label' => 'Empty search state', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'body', 'type' => 'textarea', 'label' => 'Body', 'columnSpanFull' => true],
                    ['key' => 'buttonLabel', 'type' => 'text', 'label' => 'Button label'],
                ]],
                'cta' => ['label' => 'Bottom CTA', 'fields' => $ctaFields],
                'seo' => $pageSeoSection,
            ],
        ],
        'contact' => [
            'label' => 'Contact',
            'group' => 'Marketing',
            'preview_route' => '/contact',
            'sort_order' => 4,
            'sections' => [
                'hero' => ['label' => 'Hero', 'fields' => $heroFields],
                'details' => ['label' => 'Contact details', 'isRootSection' => true, 'fields' => [
                    ['key' => 'phone', 'type' => 'text', 'label' => 'Phone'],
                    ['key' => 'email', 'type' => 'text', 'label' => 'Email'],
                    ['key' => 'address', 'type' => 'textarea', 'label' => 'Address', 'columnSpanFull' => true],
                    ['key' => 'hours', 'type' => 'text', 'label' => 'Hours'],
                    ['key' => 'show_form', 'type' => 'toggle', 'label' => 'Show contact form'],
                ]],
                'formLabels' => ['label' => 'Form labels', 'fields' => [
                    ['key' => 'name', 'type' => 'text', 'label' => 'Name field'],
                    ['key' => 'email', 'type' => 'text', 'label' => 'Email field'],
                    ['key' => 'message', 'type' => 'text', 'label' => 'Message field'],
                    ['key' => 'submit', 'type' => 'text', 'label' => 'Submit button'],
                ]],
                'seo' => $pageSeoSection,
            ],
        ],
        'terms' => [
            'label' => 'Terms',
            'group' => 'Marketing',
            'preview_route' => '/terms',
            'sort_order' => 5,
            'sections' => [
                'hero' => ['label' => 'Hero', 'fields' => $heroFields],
                'body' => ['label' => 'Body', 'fields' => [
                    ['key' => 'body', 'type' => 'richtext', 'label' => 'Body', 'columnSpanFull' => true, 'isRoot' => true],
                ]],
                'seo' => $pageSeoSection,
            ],
        ],
        'privacy' => [
            'label' => 'Privacy',
            'group' => 'Marketing',
            'preview_route' => '/privacy',
            'sort_order' => 6,
            'sections' => [
                'hero' => ['label' => 'Hero', 'fields' => $heroFields],
                'body' => ['label' => 'Body', 'fields' => [
                    ['key' => 'body', 'type' => 'richtext', 'label' => 'Body', 'columnSpanFull' => true, 'isRoot' => true],
                ]],
                'seo' => $pageSeoSection,
            ],
        ],
        'cookies' => [
            'label' => 'Cookies',
            'group' => 'Marketing',
            'preview_route' => '/cookies',
            'sort_order' => 7,
            'sections' => [
                'hero' => ['label' => 'Hero', 'fields' => $heroFields],
                'body' => ['label' => 'Body', 'fields' => [
                    ['key' => 'body', 'type' => 'richtext', 'label' => 'Body', 'columnSpanFull' => true, 'isRoot' => true],
                ]],
                'seo' => $pageSeoSection,
            ],
        ],
        'become-a-host' => [
            'label' => 'Become a host',
            'group' => 'Marketing',
            'preview_route' => '/become-a-host',
            'sort_order' => 8,
            'sections' => [
                'topbar' => ['label' => 'Top bar', 'fields' => [
                    ['key' => 'text', 'type' => 'text', 'label' => 'Text', 'columnSpanFull' => true],
                ]],
                'hero' => ['label' => 'Hero', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
                    ['key' => 'earnAmount', 'type' => 'text', 'label' => 'Average earnings amount'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Background photo'],
                    ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Background photo alt text'],
                    ['key' => 'submitLabel', 'type' => 'text', 'label' => 'Submit label'],
                ]],
                'proof' => ['label' => 'Social proof marquee', 'fields' => [
                    ['key' => 'chipAmount', 'type' => 'text', 'label' => 'Chip amount'],
                    ['key' => 'chipLabel', 'type' => 'text', 'label' => 'Chip label'],
                    ['key' => 'stats', 'type' => 'repeater', 'label' => 'Marquee columns', 'fields' => $proofStatFields, 'columnSpanFull' => true],
                ]],
                'howTabs' => ['label' => 'How it works tabs', 'fields' => [
                    ['key' => 'howTabs', 'type' => 'repeater', 'label' => 'Tabs', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                        ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Photo alt text'],
                        ['key' => 'caption', 'type' => 'textarea', 'label' => 'Caption', 'columnSpanFull' => true],
                        ['key' => 'muted', 'type' => 'textarea', 'label' => 'Muted caption', 'columnSpanFull' => true],
                    ]],
                ]],
                'features' => ['label' => 'Feature bento', 'fields' => [
                    ['key' => 'features', 'type' => 'repeater', 'label' => 'Features', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
                        ['key' => 'image', 'type' => 'image', 'label' => 'Photo', 'helperText' => 'Optional. Cards 2–4 show a photo; card 1 uses the map widget.'],
                        ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Photo alt text'],
                    ]],
                ]],
                'reviews' => ['label' => 'Host reviews', 'fields' => [
                    ['key' => 'up', 'type' => 'repeater', 'label' => 'Top row reviews', 'path' => 'reviews.up', 'fields' => $hostReviewFields, 'columnSpanFull' => true],
                    ['key' => 'down', 'type' => 'repeater', 'label' => 'Bottom row reviews', 'path' => 'reviews.down', 'fields' => $hostReviewFields, 'columnSpanFull' => true],
                ]],
                'faqItems' => ['label' => 'FAQ', 'fields' => [
                    ['key' => 'faqItems', 'type' => 'repeater', 'label' => 'FAQ items', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => $faqItemRepeater],
                ]],
                'cta' => ['label' => 'Bottom CTA', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
                    ['key' => 'submitLabel', 'type' => 'text', 'label' => 'Submit label'],
                    ['key' => 'patternImage', 'type' => 'image', 'label' => 'Background pattern icon', 'helperText' => 'Repeating watermark behind the green CTA box. Leave empty to use the default MyTerraBook mark.'],
                ]],
                'footer' => ['label' => 'Footer', 'fields' => [
                    ['key' => 'tagline', 'type' => 'text', 'label' => 'Tagline'],
                    ['key' => 'copyright', 'type' => 'text', 'label' => 'Copyright'],
                ]],
                'seo' => $pageSeoSection,
            ],
        ],
        'good-to-know' => [
            'label' => 'Good to Know',
            'group' => 'Marketing',
            'preview_route' => '/good-to-know',
            'sort_order' => 10,
            'sections' => [
                'header' => ['label' => 'Page header', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
                ]],
                'seo' => $pageSeoSection,
            ],
        ],
        'campsite-map' => [
            'label' => 'Campsite map',
            'group' => 'Marketing',
            'preview_route' => '/campsite-map',
            'sort_order' => 9,
            'sections' => [
                'header' => ['label' => 'Page header', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Header photo'],
                    ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Header photo alt text'],
                ]],
                'map' => ['label' => 'Map', 'fields' => [
                    ['key' => 'embedUrl', 'type' => 'text', 'label' => 'Google Maps embed URL', 'columnSpanFull' => true, 'helperText' => 'Paste the full iframe src URL from Google My Maps.'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Static map image', 'helperText' => 'Optional fallback image when no embed URL is set.'],
                    ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Map image alt text'],
                ]],
                'footnote' => ['label' => 'Footnote', 'isRootSection' => true, 'fields' => [
                    ['key' => 'note', 'type' => 'textarea', 'label' => 'Note below map', 'columnSpanFull' => true],
                ]],
                'photos' => ['label' => 'Photo gallery', 'fields' => [
                    ['key' => 'photos', 'type' => 'repeater', 'label' => 'Photos', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                        ['key' => 'alt', 'type' => 'text', 'label' => 'Alt text'],
                        ['key' => 'caption', 'type' => 'textarea', 'label' => 'Caption', 'columnSpanFull' => true],
                    ]],
                ]],
                'seo' => $pageSeoSection,
            ],
        ],
        'search-campervan' => [
            'label' => 'Search, Campervans',
            'group' => 'Transactional',
            'preview_route' => '/campervans',
            'sort_order' => 10,
            'sections' => [
                'routing' => ['label' => 'Routing & defaults', 'isRootSection' => true, 'fields' => $searchRoutingFields],
                'content' => ['label' => 'Page copy', 'isRootSection' => true, 'fields' => $searchCopyFields],
                'seo' => $pageSeoSection,
            ],
        ],
        'search-car' => [
            'label' => 'Search, Cars',
            'group' => 'Transactional',
            'preview_route' => '/cars',
            'sort_order' => 11,
            'sections' => [
                'routing' => ['label' => 'Routing & defaults', 'isRootSection' => true, 'fields' => $searchRoutingFields],
                'content' => ['label' => 'Page copy', 'isRootSection' => true, 'fields' => $searchCopyFields],
                'seo' => $pageSeoSection,
            ],
        ],
        'search-guesthouse' => [
            'label' => 'Search, Guesthouses',
            'group' => 'Transactional',
            'preview_route' => '/guesthouses',
            'sort_order' => 12,
            'sections' => [
                'routing' => ['label' => 'Routing & defaults', 'isRootSection' => true, 'fields' => array_merge($searchRoutingFields, [
                    ['key' => 'introLocationDefault', 'type' => 'text', 'label' => 'Default location intro'],
                ])],
                'content' => ['label' => 'Page copy', 'isRootSection' => true, 'fields' => $searchCopyFields],
                'seo' => $pageSeoSection,
            ],
        ],
        'listing-campervan' => [
            'label' => 'Listing, Campervan',
            'group' => 'Transactional',
            'preview_route' => '/campervans',
            'sort_order' => 13,
            'sections' => [
                'labels' => ['label' => 'Labels & copy', 'isRootSection' => true, 'fields' => $listingLabelFields],
                'tabs' => ['label' => 'Detail tabs', 'fields' => [
                    ['key' => 'tabs', 'type' => 'repeater', 'label' => 'Tabs', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => $listingTabFields],
                ]],
                'trustPoints' => ['label' => 'Trust points', 'fields' => [
                    ['key' => 'trustPoints', 'type' => 'repeater', 'label' => 'Points', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => $trustPointFields],
                ]],
                'bookingSteps' => ['label' => 'Booking steps', 'fields' => [
                    ['key' => 'bookingSteps', 'type' => 'repeater', 'label' => 'Steps', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => $bookingStepFields],
                ]],
                'sectionDescriptions' => ['label' => 'Detail section descriptions', 'fields' => $listingSectionDescriptionFields],
            ],
        ],
        'listing-car' => [
            'label' => 'Listing, Car',
            'group' => 'Transactional',
            'preview_route' => '/cars',
            'sort_order' => 14,
            'sections' => [
                'labels' => ['label' => 'Labels & copy', 'isRootSection' => true, 'fields' => $listingLabelFields],
                'tabs' => ['label' => 'Detail tabs', 'fields' => [
                    ['key' => 'tabs', 'type' => 'repeater', 'label' => 'Tabs', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => $listingTabFields],
                ]],
                'trustPoints' => ['label' => 'Trust points', 'fields' => [
                    ['key' => 'trustPoints', 'type' => 'repeater', 'label' => 'Points', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => $trustPointFields],
                ]],
                'bookingSteps' => ['label' => 'Booking steps', 'fields' => [
                    ['key' => 'bookingSteps', 'type' => 'repeater', 'label' => 'Steps', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => $bookingStepFields],
                ]],
                'sectionDescriptions' => ['label' => 'Detail section descriptions', 'fields' => $listingSectionDescriptionFields],
            ],
        ],
        'listing-guesthouse' => [
            'label' => 'Listing, Guesthouse',
            'group' => 'Transactional',
            'preview_route' => '/guesthouses',
            'sort_order' => 15,
            'sections' => [
                'labels' => ['label' => 'Labels & copy', 'isRootSection' => true, 'fields' => $listingLabelFields],
                'tabs' => ['label' => 'Detail tabs', 'fields' => [
                    ['key' => 'tabs', 'type' => 'repeater', 'label' => 'Tabs', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => $listingTabFields],
                ]],
                'trustPoints' => ['label' => 'Trust points', 'fields' => [
                    ['key' => 'trustPoints', 'type' => 'repeater', 'label' => 'Points', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => $trustPointFields],
                ]],
                'bookingSteps' => ['label' => 'Booking steps', 'fields' => [
                    ['key' => 'bookingSteps', 'type' => 'repeater', 'label' => 'Steps', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => $bookingStepFields],
                ]],
                'sectionDescriptions' => ['label' => 'Detail section descriptions', 'fields' => $listingSectionDescriptionFields],
            ],
        ],
        'checkout' => [
            'label' => 'Checkout',
            'group' => 'Transactional',
            'preview_route' => '/checkout',
            'sort_order' => 16,
            'sections' => [
                'stepper' => ['label' => 'Stepper', 'fields' => [
                    ['key' => 'stepperSteps', 'type' => 'repeater', 'label' => 'Steps', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'num', 'type' => 'number', 'label' => 'Number'],
                        ['key' => 'sk', 'type' => 'text', 'label' => 'Short label'],
                        ['key' => 'sl', 'type' => 'text', 'label' => 'Label'],
                    ]],
                ]],
                'labels' => ['label' => 'Step titles', 'fields' => [
                    ['key' => 'tripTitle', 'type' => 'text', 'label' => 'Trip step', 'path' => 'labels.tripTitle'],
                    ['key' => 'extrasTitle', 'type' => 'text', 'label' => 'Extras step', 'path' => 'labels.extrasTitle'],
                    ['key' => 'detailsTitle', 'type' => 'text', 'label' => 'Details step', 'path' => 'labels.detailsTitle'],
                    ['key' => 'paymentTitle', 'type' => 'text', 'label' => 'Payment step', 'path' => 'labels.paymentTitle'],
                    ['key' => 'dueOnApprovalLabel', 'type' => 'text', 'label' => 'Due on approval row', 'path' => 'labels.dueOnApprovalLabel', 'helperText' => 'Use {percent} for the shop deposit percentage.'],
                ]],
                'settings' => ['label' => 'Settings', 'isRootSection' => true, 'fields' => [
                    ['key' => 'prepayPercent', 'type' => 'number', 'label' => 'Prepay percent'],
                ]],
                'seo' => $pageSeoSection,
            ],
        ],
        'auth-login' => [
            'label' => 'Login',
            'group' => 'App',
            'preview_route' => '/login',
            'sort_order' => 17,
            'sections' => [
                'content' => ['label' => 'Page copy', 'isRootSection' => true, 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                    ['key' => 'emailLabel', 'type' => 'text', 'label' => 'Email label'],
                    ['key' => 'passwordLabel', 'type' => 'text', 'label' => 'Password label'],
                    ['key' => 'rememberLabel', 'type' => 'text', 'label' => 'Remember me label'],
                    ['key' => 'submitLabel', 'type' => 'text', 'label' => 'Submit label'],
                    ['key' => 'registerPrompt', 'type' => 'text', 'label' => 'Register prompt'],
                    ['key' => 'registerLink', 'type' => 'text', 'label' => 'Register link text'],
                ]],
                'seo' => $pageSeoSection,
            ],
        ],
        'auth-register' => [
            'label' => 'Register',
            'group' => 'App',
            'preview_route' => '/register',
            'sort_order' => 18,
            'sections' => [
                'content' => ['label' => 'Page copy', 'isRootSection' => true, 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                    ['key' => 'submitLabel', 'type' => 'text', 'label' => 'Submit label'],
                    ['key' => 'loginPrompt', 'type' => 'text', 'label' => 'Login prompt'],
                    ['key' => 'loginLink', 'type' => 'text', 'label' => 'Login link text'],
                ]],
                'seo' => $pageSeoSection,
            ],
        ],
        'auth-host-register' => [
            'label' => 'Host register',
            'group' => 'App',
            'preview_route' => '/host/register',
            'sort_order' => 19,
            'sections' => [
                'content' => ['label' => 'Page copy', 'isRootSection' => true, 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                    ['key' => 'submitLabel', 'type' => 'text', 'label' => 'Submit label'],
                ]],
                'seo' => $pageSeoSection,
            ],
        ],
        'user-dashboard' => [
            'label' => 'User dashboard',
            'group' => 'App',
            'preview_route' => '/dashboard',
            'sort_order' => 20,
            'sections' => [
                'content' => ['label' => 'Page copy', 'isRootSection' => true, 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'historyTitle', 'type' => 'text', 'label' => 'History title'],
                    ['key' => 'historySubtitle', 'type' => 'text', 'label' => 'History subtitle'],
                    ['key' => 'emptyBookings', 'type' => 'text', 'label' => 'Empty bookings message'],
                    ['key' => 'emptyStays', 'type' => 'text', 'label' => 'Empty stays message'],
                    ['key' => 'emptyHistory', 'type' => 'text', 'label' => 'Empty history message'],
                    ['key' => 'emptyHistoryText', 'type' => 'text', 'label' => 'Empty history description'],
                    ['key' => 'exportLabel', 'type' => 'text', 'label' => 'Export CSV label'],
                    ['key' => 'downloadPdfLabel', 'type' => 'text', 'label' => 'Download PDF label'],
                    ['key' => 'addToCalendarLabel', 'type' => 'text', 'label' => 'Add to calendar label'],
                    ['key' => 'cancelBookingLabel', 'type' => 'text', 'label' => 'Cancel booking label'],
                    ['key' => 'viewListingLabel', 'type' => 'text', 'label' => 'View listing label'],
                    ['key' => 'sidebarLinks', 'type' => 'repeater', 'label' => 'Sidebar links', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'id', 'type' => 'text', 'label' => 'ID'],
                        ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                    ]],
                ]],
            ],
        ],
        'admin-dashboard' => [
            'label' => 'Admin dashboard',
            'group' => 'App',
            'preview_route' => '/admin',
            'sort_order' => 21,
            'sections' => [
                'content' => ['label' => 'Page copy', 'isRootSection' => true, 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'filamentLink', 'type' => 'text', 'label' => 'Admin panel link label'],
                ]],
                'statsLabels' => ['label' => 'Stat labels', 'fields' => [
                    ['key' => 'orders', 'type' => 'text', 'label' => 'Orders label', 'path' => 'statsLabels.orders'],
                    ['key' => 'revenue', 'type' => 'text', 'label' => 'Revenue label', 'path' => 'statsLabels.revenue'],
                    ['key' => 'activeRentals', 'type' => 'text', 'label' => 'Active rentals label', 'path' => 'statsLabels.activeRentals'],
                ]],
            ],
        ],
        'client-panel' => [
            'label' => 'Client panel',
            'group' => 'App',
            'preview_route' => '/dashboard',
            'sort_order' => 22,
            'sections' => [
                'content' => ['label' => 'Shell copy', 'isRootSection' => true, 'fields' => [
                    ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                    ['key' => 'sidebarTitle', 'type' => 'text', 'label' => 'Sidebar title'],
                    ['key' => 'heroText', 'type' => 'text', 'label' => 'Hero subtitle'],
                    ['key' => 'signOutLabel', 'type' => 'text', 'label' => 'Sign out label'],
                    ['key' => 'navItems', 'type' => 'repeater', 'label' => 'Nav items', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'to', 'type' => 'text', 'label' => 'Route'],
                        ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                    ]],
                ]],
            ],
        ],
        'host-panel' => [
            'label' => 'Host panel',
            'group' => 'App',
            'preview_route' => '/host',
            'sort_order' => 23,
            'sections' => [
                'content' => ['label' => 'Shell copy', 'isRootSection' => true, 'fields' => [
                    ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                    ['key' => 'sidebarTitle', 'type' => 'text', 'label' => 'Sidebar title'],
                    ['key' => 'signOutLabel', 'type' => 'text', 'label' => 'Sign out label'],
                    ['key' => 'navItems', 'type' => 'repeater', 'label' => 'Nav items', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'to', 'type' => 'text', 'label' => 'Route'],
                        ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                    ]],
                ]],
            ],
        ],
        'newsletter-unsubscribe' => [
            'label' => 'Newsletter unsubscribe',
            'group' => 'App',
            'preview_route' => '/newsletter/unsubscribe',
            'sort_order' => 24,
            'sections' => [
                'content' => ['label' => 'Page copy', 'isRootSection' => true, 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'successMessage', 'type' => 'text', 'label' => 'Success message'],
                    ['key' => 'errorMessage', 'type' => 'text', 'label' => 'Error message'],
                    ['key' => 'backLabel', 'type' => 'text', 'label' => 'Back link label'],
                ]],
            ],
        ],
        'under-construction' => [
            'label' => 'Under construction',
            'group' => 'App',
            'preview_route' => '/',
            'sort_order' => 24,
            'sections' => [
                'content' => ['label' => 'Page copy', 'isRootSection' => true, 'fields' => [
                    ['key' => 'badge', 'type' => 'text', 'label' => 'Badge'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                    ['key' => 'hint', 'type' => 'text', 'label' => 'Hint'],
                    ['key' => 'footer', 'type' => 'text', 'label' => 'Footer'],
                ]],
            ],
        ],
    ],
];
