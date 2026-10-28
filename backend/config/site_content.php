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

$reviewCardFields = [
    ['key' => 'quote', 'type' => 'textarea', 'label' => 'Quote', 'columnSpanFull' => true],
    ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
    ['key' => 'relativeTime', 'type' => 'text', 'label' => 'Relative time'],
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

$searchPresentationFields = [
    ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
    ['key' => 'insuranceNote', 'type' => 'text', 'label' => 'Result note', 'columnSpanFull' => true],
    ['key' => 'breadcrumbHome', 'type' => 'text', 'label' => 'Breadcrumb home'],
    ['key' => 'breadcrumbSection', 'type' => 'text', 'label' => 'Breadcrumb section'],
    ['key' => 'breadcrumbPlace', 'type' => 'text', 'label' => 'Breadcrumb place'],
    ['key' => 'pickupLabel', 'type' => 'text', 'label' => 'First field label'],
    ['key' => 'datesLabel', 'type' => 'text', 'label' => 'Dates label'],
    ['key' => 'dropoffLabel', 'type' => 'text', 'label' => 'Third field label'],
    ['key' => 'whoLabel', 'type' => 'text', 'label' => 'Guests label'],
    ['key' => 'whoValue', 'type' => 'text', 'label' => 'Guests default'],
    ['key' => 'dropoffSame', 'type' => 'text', 'label' => 'Same drop-off'],
    ['key' => 'updateLabel', 'type' => 'text', 'label' => 'Update button'],
    ['key' => 'filtersLabel', 'type' => 'text', 'label' => 'Filters button'],
    ['key' => 'filtersTitle', 'type' => 'text', 'label' => 'Filters title'],
    ['key' => 'sortTitle', 'type' => 'text', 'label' => 'Sort title'],
    ['key' => 'partnerLabel', 'type' => 'text', 'label' => 'Partner label'],
    ['key' => 'showAllLabel', 'type' => 'text', 'label' => 'Show all'],
    ['key' => 'bundleTitle', 'type' => 'text', 'label' => 'Bundle title', 'columnSpanFull' => true],
    ['key' => 'bundleText', 'type' => 'textarea', 'label' => 'Bundle text', 'columnSpanFull' => true],
    ['key' => 'bundleButton', 'type' => 'text', 'label' => 'Bundle button'],
    ['key' => 'bundleHref', 'type' => 'text', 'label' => 'Bundle link'],
    ['key' => 'showingLabel', 'type' => 'text', 'label' => 'Showing line'],
    ['key' => 'loadMoreLabel', 'type' => 'text', 'label' => 'Show more'],
    ['key' => 'bookLabel', 'type' => 'text', 'label' => 'Book button'],
    ['key' => 'typeLabel', 'type' => 'text', 'label' => 'Type badge'],
    ['key' => 'clearLabel', 'type' => 'text', 'label' => 'Clear'],
    ['key' => 'showLabel', 'type' => 'text', 'label' => 'Show results'],
    ['key' => 'onBoardLabel', 'type' => 'text', 'label' => 'On board label'],
    ['key' => 'fromLabel', 'type' => 'text', 'label' => 'Price prefix'],
];

$listingSectionDescriptionFields = [
    ['key' => 'amenities', 'type' => 'textarea', 'label' => 'Amenities / features section', 'rows' => 2, 'columnSpanFull' => true],
    ['key' => 'optionalExtras', 'type' => 'textarea', 'label' => 'Optional extras section (vehicles only)', 'rows' => 2, 'columnSpanFull' => true],
    ['key' => 'conditions', 'type' => 'textarea', 'label' => 'Rental conditions / house rules section', 'rows' => 2, 'columnSpanFull' => true],
    ['key' => 'pickupDropoff', 'type' => 'textarea', 'label' => 'Pick-up & drop-off section (vehicles only)', 'rows' => 2, 'columnSpanFull' => true],
    ['key' => 'roomDetails', 'type' => 'textarea', 'label' => 'Room details section (guesthouses only)', 'rows' => 2, 'columnSpanFull' => true],
    ['key' => 'location', 'type' => 'textarea', 'label' => 'Location section', 'rows' => 2, 'columnSpanFull' => true],
];

$listingFrameFields = [
    ['key' => 'typeLabel', 'type' => 'text', 'label' => 'Type badge'],
    ['key' => 'aboutTitle', 'type' => 'text', 'label' => 'About title'],
    ['key' => 'featuresTitle', 'type' => 'text', 'label' => 'Features title'],
    ['key' => 'includedTitle', 'type' => 'text', 'label' => 'Included title'],
    ['key' => 'included', 'type' => 'repeater', 'label' => 'Included items', 'columnSpanFull' => true, 'fields' => [
        ['key' => 'text', 'type' => 'text', 'label' => 'Item'],
    ]],
    ['key' => 'extrasTitle', 'type' => 'text', 'label' => 'Extras title'],
    ['key' => 'extrasLead', 'type' => 'text', 'label' => 'Extras lead', 'columnSpanFull' => true],
    ['key' => 'pickupTitle', 'type' => 'text', 'label' => 'Pick-up title'],
    ['key' => 'knowTitle', 'type' => 'text', 'label' => 'Good to know title'],
    ['key' => 'know', 'type' => 'repeater', 'label' => 'Good to know cards', 'columnSpanFull' => true, 'fields' => [
        ['key' => 'icon', 'type' => 'text', 'label' => 'Icon'],
        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
    ]],
    ['key' => 'conditionsLink', 'type' => 'text', 'label' => 'Conditions link'],
    ['key' => 'reviewsTitle', 'type' => 'text', 'label' => 'Reviews title'],
    ['key' => 'writeReview', 'type' => 'text', 'label' => 'Write review button'],
    ['key' => 'emptyReviews', 'type' => 'text', 'label' => 'Empty reviews title'],
    ['key' => 'emptyReviewsLead', 'type' => 'text', 'label' => 'Empty reviews text', 'columnSpanFull' => true],
    ['key' => 'hostKicker', 'type' => 'text', 'label' => 'Host kicker'],
    ['key' => 'hostButton', 'type' => 'text', 'label' => 'Host button'],
    ['key' => 'bookCta', 'type' => 'text', 'label' => 'Book button'],
    ['key' => 'processLink', 'type' => 'text', 'label' => 'Booking process link'],
    ['key' => 'insuranceTitle', 'type' => 'text', 'label' => 'Insurance title'],
    ['key' => 'stayTitle', 'type' => 'text', 'label' => 'Add stay title'],
    ['key' => 'stayText', 'type' => 'text', 'label' => 'Add stay text'],
    ['key' => 'similarTitle', 'type' => 'text', 'label' => 'Similar title'],
    ['key' => 'similarButton', 'type' => 'text', 'label' => 'Similar button'],
    ['key' => 'staysEyebrow', 'type' => 'text', 'label' => 'Stays eyebrow'],
    ['key' => 'staysTitle', 'type' => 'text', 'label' => 'Stays title'],
    ['key' => 'staysButton', 'type' => 'text', 'label' => 'Stays button'],
    ['key' => 'winter', 'type' => 'textarea', 'label' => 'Winter note', 'columnSpanFull' => true],
    ['key' => 'priceLabel', 'type' => 'text', 'label' => 'Price label'],
    ['key' => 'roomTitle', 'type' => 'text', 'label' => 'Room details title'],
    ['key' => 'rulesTitle', 'type' => 'text', 'label' => 'House rules title'],
    ['key' => 'locationTitle', 'type' => 'text', 'label' => 'Location title'],
    ['key' => 'locationNote', 'type' => 'text', 'label' => 'Location note', 'columnSpanFull' => true],
    ['key' => 'mapButton', 'type' => 'text', 'label' => 'Map button'],
    ['key' => 'chargeNote', 'type' => 'text', 'label' => 'Nothing charged note', 'columnSpanFull' => true],
    ['key' => 'addVehicleTitle', 'type' => 'text', 'label' => 'Add vehicle title'],
    ['key' => 'addVehicleText', 'type' => 'text', 'label' => 'Add vehicle text'],
    ['key' => 'rules', 'type' => 'repeater', 'label' => 'House rules', 'columnSpanFull' => true, 'fields' => [
        ['key' => 'icon', 'type' => 'text', 'label' => 'Icon'],
        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
        ['key' => 'text', 'type' => 'text', 'label' => 'Text'],
    ]],
    ['key' => 'process', 'type' => 'repeater', 'label' => 'Booking process', 'columnSpanFull' => true, 'fields' => [
        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
    ]],
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
    ['key' => 'shareLabel', 'type' => 'text', 'label' => 'Share label'],
    ['key' => 'similarTitle', 'type' => 'text', 'label' => 'Similar title'],
    ['key' => 'guestPhotosTitle', 'type' => 'text', 'label' => 'Guest photos title'],
    ['key' => 'reviewsTitle', 'type' => 'text', 'label' => 'Reviews title'],
    ['key' => 'bookingModalTitle', 'type' => 'text', 'label' => 'Booking modal title'],
    ['key' => 'bookingModalLead', 'type' => 'textarea', 'label' => 'Booking modal lead', 'columnSpanFull' => true],
    ['key' => 'bookingModalFootnote', 'type' => 'text', 'label' => 'Booking modal footnote', 'columnSpanFull' => true, 'helperText' => 'Small text beside the modal CTA button.'],
    ['key' => 'faqLead', 'type' => 'textarea', 'label' => 'FAQ lead', 'columnSpanFull' => true],
];

$bindFields = function (string $root, array $fields): array {
    return array_map(function (array $field) use ($root): array {
        if (! isset($field['path']) && isset($field['key'])) {
            $field['path'] = $root.'.'.$field['key'];
        }

        return $field;
    }, $fields);
};

$pickFields = function (array $fields, array $keys): array {
    $byKey = [];
    foreach ($fields as $field) {
        $byKey[$field['key']] = $field;
    }
    $picked = [];
    foreach ($keys as $key) {
        if (isset($byKey[$key])) {
            $picked[] = $byKey[$key];
        }
    }

    return $picked;
};

$searchFigmaSections = [
    'routing' => ['label' => 'Catalog routing', 'isRootSection' => true, 'fields' => $searchRoutingFields],
    'results' => ['label' => 'Results header', 'fields' => $bindFields('presentation', $pickFields($searchPresentationFields, ['title', 'insuranceNote', 'showingLabel', 'loadMoreLabel']))],
    'breadcrumb' => ['label' => 'Breadcrumb', 'fields' => $bindFields('presentation', $pickFields($searchPresentationFields, ['breadcrumbHome', 'breadcrumbSection', 'breadcrumbPlace']))],
    'searchBar' => ['label' => 'Search bar', 'fields' => $bindFields('presentation', $pickFields($searchPresentationFields, ['pickupLabel', 'datesLabel', 'dropoffLabel', 'whoLabel', 'whoValue', 'dropoffSame', 'updateLabel']))],
    'filters' => ['label' => 'Filters and sort', 'fields' => $bindFields('presentation', $pickFields($searchPresentationFields, ['filtersLabel', 'filtersTitle', 'sortTitle', 'clearLabel', 'showLabel', 'showAllLabel']))],
    'cards' => ['label' => 'Result cards', 'fields' => $bindFields('presentation', $pickFields($searchPresentationFields, ['typeLabel', 'partnerLabel', 'onBoardLabel', 'fromLabel', 'bookLabel']))],
    'bundle' => ['label' => 'Bundle banner', 'fields' => $bindFields('presentation', $pickFields($searchPresentationFields, ['bundleTitle', 'bundleText', 'bundleButton', 'bundleHref']))],
    'seo' => $pageSeoSection,
];

$listingFigmaSections = [
    'badge' => ['label' => 'Badge', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['typeLabel']))],
    'about' => ['label' => 'About', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['aboutTitle', 'winter']))],
    'included' => ['label' => 'Included', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['includedTitle', 'included']))],
    'features' => ['label' => 'Features', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['featuresTitle']))],
    'extras' => ['label' => 'Extras', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['extrasTitle', 'extrasLead']))],
    'pickup' => ['label' => 'Pick-up and drop-off', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['pickupTitle']))],
    'room' => ['label' => 'Room details', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['roomTitle']))],
    'rules' => ['label' => 'House rules', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['rulesTitle', 'rules']))],
    'know' => ['label' => 'Good to know', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['knowTitle', 'know', 'conditionsLink']))],
    'location' => ['label' => 'Location', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['locationTitle', 'locationNote', 'mapButton']))],
    'reviews' => ['label' => 'Reviews', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['reviewsTitle', 'writeReview', 'emptyReviews', 'emptyReviewsLead']))],
    'host' => ['label' => 'Host', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['hostKicker', 'hostButton']))],
    'booking' => ['label' => 'Booking card', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['bookCta', 'processLink', 'insuranceTitle', 'priceLabel', 'chargeNote', 'process']))],
    'stay' => ['label' => 'Add a stay or vehicle', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['stayTitle', 'stayText', 'addVehicleTitle', 'addVehicleText']))],
    'similar' => ['label' => 'Similar listings', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['similarTitle', 'similarButton']))],
    'stays' => ['label' => 'Places to stay strip', 'fields' => $bindFields('frame', $pickFields($listingFrameFields, ['staysEyebrow', 'staysTitle', 'staysButton']))],
    'seo' => $pageSeoSection,
];

$aboutSections = [
    'hero' => ['label' => 'Hero', 'fields' => [
        ['key' => 'crumb', 'type' => 'text', 'label' => 'Breadcrumb'],
        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'headingBefore', 'type' => 'text', 'label' => 'Heading before italic'],
        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'headingAfter', 'type' => 'text', 'label' => 'Heading after italic'],
        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
        ['key' => 'primaryLabel', 'type' => 'text', 'label' => 'Primary button'],
        ['key' => 'primaryHref', 'type' => 'text', 'label' => 'Primary link'],
        ['key' => 'secondaryLabel', 'type' => 'text', 'label' => 'Secondary button'],
        ['key' => 'secondaryHref', 'type' => 'text', 'label' => 'Secondary link'],
        ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
    ]],
    'stats' => ['label' => 'Stats', 'fields' => [
        ['key' => 'stats', 'type' => 'repeater', 'label' => 'Stats', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
            ['key' => 'icon', 'type' => 'text', 'label' => 'Icon'],
            ['key' => 'value', 'type' => 'text', 'label' => 'Value'],
            ['key' => 'text', 'type' => 'text', 'label' => 'Text', 'columnSpanFull' => true],
        ]],
    ]],
    'story' => ['label' => 'Story', 'fields' => [
        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'headingBefore', 'type' => 'text', 'label' => 'Heading before italic'],
        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'headingAfter', 'type' => 'text', 'label' => 'Heading after italic'],
        ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
        ['key' => 'address', 'type' => 'text', 'label' => 'Address', 'columnSpanFull' => true],
        ['key' => 'paragraphs', 'type' => 'repeater', 'label' => 'Paragraphs', 'columnSpanFull' => true, 'fields' => [
            ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
        ]],
    ]],
    'values' => ['label' => 'Values', 'fields' => [
        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'headingBefore', 'type' => 'text', 'label' => 'Heading before italic'],
        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'headingAfter', 'type' => 'text', 'label' => 'Heading after italic'],
        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
        ['key' => 'items', 'type' => 'repeater', 'label' => 'Values', 'columnSpanFull' => true, 'fields' => [
            ['key' => 'icon', 'type' => 'text', 'label' => 'Icon'],
            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
            ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
        ]],
    ]],
    'products' => ['label' => 'Products', 'fields' => [
        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'headingBefore', 'type' => 'text', 'label' => 'Heading before italic'],
        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'headingAfter', 'type' => 'text', 'label' => 'Heading after italic'],
        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
        ['key' => 'button', 'type' => 'text', 'label' => 'Header link'],
        ['key' => 'href', 'type' => 'text', 'label' => 'Header link URL'],
        ['key' => 'items', 'type' => 'repeater', 'label' => 'Cards', 'columnSpanFull' => true, 'fields' => [
            ['key' => 'icon', 'type' => 'text', 'label' => 'Icon'],
            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
            ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
            ['key' => 'meta', 'type' => 'text', 'label' => 'Meta line'],
            ['key' => 'button', 'type' => 'text', 'label' => 'Button'],
            ['key' => 'href', 'type' => 'text', 'label' => 'Link'],
            ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
        ]],
    ]],
    'cta' => ['label' => 'Closing CTA', 'fields' => [
        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'headingBefore', 'type' => 'text', 'label' => 'Heading before italic'],
        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'headingAfter', 'type' => 'text', 'label' => 'Heading after italic'],
        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
        ['key' => 'primaryLabel', 'type' => 'text', 'label' => 'Primary button'],
        ['key' => 'primaryHref', 'type' => 'text', 'label' => 'Primary link'],
        ['key' => 'secondaryLabel', 'type' => 'text', 'label' => 'Secondary button'],
        ['key' => 'secondaryHref', 'type' => 'text', 'label' => 'Secondary link'],
    ]],
    'seo' => $pageSeoSection,
];

$faqSections = [
    'hero' => ['label' => 'Hero', 'fields' => [
        ['key' => 'crumb', 'type' => 'text', 'label' => 'Breadcrumb'],
        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'headingBefore', 'type' => 'text', 'label' => 'Heading before italic'],
        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'headingAfter', 'type' => 'text', 'label' => 'Heading after italic'],
        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
        ['key' => 'searchPlaceholder', 'type' => 'text', 'label' => 'Search placeholder'],
        ['key' => 'searchButton', 'type' => 'text', 'label' => 'Search button'],
        ['key' => 'popularLabel', 'type' => 'text', 'label' => 'Popular label'],
        ['key' => 'chips', 'type' => 'tags', 'label' => 'Popular chips'],
    ]],
    'categories' => ['label' => 'Category tabs', 'fields' => [
        ['key' => 'categories', 'type' => 'repeater', 'label' => 'Tabs', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
            ['key' => 'id', 'type' => 'text', 'label' => 'ID'],
            ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
            ['key' => 'icon', 'type' => 'text', 'label' => 'Icon'],
        ]],
    ]],
    'items' => ['label' => 'Questions', 'fields' => [
        ['key' => 'items', 'type' => 'repeater', 'label' => 'Questions', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
            ['key' => 'num', 'type' => 'text', 'label' => 'Number'],
            ['key' => 'cat', 'type' => 'text', 'label' => 'Category ID'],
            ['key' => 'label', 'type' => 'text', 'label' => 'Category label'],
            ['key' => 'q', 'type' => 'text', 'label' => 'Question', 'columnSpanFull' => true],
            ['key' => 'a', 'type' => 'textarea', 'label' => 'Answer', 'columnSpanFull' => true],
            ['key' => 'linkLabel', 'type' => 'text', 'label' => 'Link label'],
            ['key' => 'linkHref', 'type' => 'text', 'label' => 'Link'],
            ['key' => 'extra', 'type' => 'toggle', 'label' => 'Hidden until show more'],
        ]],
    ]],
    'help' => ['label' => 'Need a human', 'fields' => [
        ['key' => 'headingBefore', 'type' => 'text', 'label' => 'Heading before italic'],
        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'headingAfter', 'type' => 'text', 'label' => 'Heading after italic'],
        ['key' => 'body', 'type' => 'textarea', 'label' => 'Body', 'columnSpanFull' => true],
        ['key' => 'phoneLabel', 'type' => 'text', 'label' => 'Phone label'],
        ['key' => 'phone', 'type' => 'text', 'label' => 'Phone'],
        ['key' => 'emailLabel', 'type' => 'text', 'label' => 'Email label'],
        ['key' => 'email', 'type' => 'text', 'label' => 'Email'],
        ['key' => 'hoursLabel', 'type' => 'text', 'label' => 'Hours label'],
        ['key' => 'hours', 'type' => 'text', 'label' => 'Hours'],
        ['key' => 'buttonLabel', 'type' => 'text', 'label' => 'Button'],
        ['key' => 'buttonHref', 'type' => 'text', 'label' => 'Button link'],
    ]],
    'guide' => ['label' => 'Guide card', 'fields' => [
        ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
        ['key' => 'body', 'type' => 'textarea', 'label' => 'Body', 'columnSpanFull' => true],
        ['key' => 'buttonLabel', 'type' => 'text', 'label' => 'Button'],
        ['key' => 'buttonHref', 'type' => 'text', 'label' => 'Button link'],
    ]],
    'more' => ['label' => 'Show more', 'fields' => [
        ['key' => 'prompt', 'type' => 'text', 'label' => 'Prompt', 'columnSpanFull' => true],
        ['key' => 'button', 'type' => 'text', 'label' => 'Button'],
        ['key' => 'empty', 'type' => 'text', 'label' => 'Empty search', 'columnSpanFull' => true],
    ]],
    'seo' => $pageSeoSection,
];

$contactSections = [
    'hero' => ['label' => 'Hero', 'fields' => [
        ['key' => 'crumb', 'type' => 'text', 'label' => 'Breadcrumb'],
        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'headingBefore', 'type' => 'text', 'label' => 'Heading before italic'],
        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'headingAfter', 'type' => 'text', 'label' => 'Heading after italic'],
        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
    ]],
    'methods' => ['label' => 'Contact methods', 'fields' => [
        ['key' => 'methods', 'type' => 'repeater', 'label' => 'Methods', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
            ['key' => 'icon', 'type' => 'text', 'label' => 'Icon'],
            ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
            ['key' => 'value', 'type' => 'text', 'label' => 'Value', 'columnSpanFull' => true],
            ['key' => 'note', 'type' => 'text', 'label' => 'Note', 'columnSpanFull' => true],
            ['key' => 'action', 'type' => 'text', 'label' => 'Action'],
            ['key' => 'href', 'type' => 'text', 'label' => 'Link'],
        ]],
    ]],
    'social' => ['label' => 'Social', 'fields' => [
        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'text', 'type' => 'text', 'label' => 'Text', 'columnSpanFull' => true],
        ['key' => 'links', 'type' => 'repeater', 'label' => 'Links', 'columnSpanFull' => true, 'fields' => [
            ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
            ['key' => 'href', 'type' => 'text', 'label' => 'Link'],
            ['key' => 'icon', 'type' => 'text', 'label' => 'Icon'],
        ]],
    ]],
    'form' => ['label' => 'Message form', 'fields' => [
        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading'],
        ['key' => 'lead', 'type' => 'text', 'label' => 'Lead', 'columnSpanFull' => true],
        ['key' => 'nameLabel', 'type' => 'text', 'label' => 'Name label'],
        ['key' => 'emailLabel', 'type' => 'text', 'label' => 'Email label'],
        ['key' => 'topicLabel', 'type' => 'text', 'label' => 'Topic label'],
        ['key' => 'messageLabel', 'type' => 'text', 'label' => 'Message label'],
        ['key' => 'topics', 'type' => 'tags', 'label' => 'Topics'],
        ['key' => 'privacy', 'type' => 'text', 'label' => 'Privacy note', 'columnSpanFull' => true],
        ['key' => 'submit', 'type' => 'text', 'label' => 'Submit button'],
        ['key' => 'sending', 'type' => 'text', 'label' => 'Sending label'],
        ['key' => 'email', 'type' => 'text', 'label' => 'Inbox email', 'path' => 'email', 'helperText' => 'Messages are delivered here.'],
    ]],
    'map' => ['label' => 'Map', 'fields' => [
        ['key' => 'title', 'type' => 'text', 'label' => 'Card title'],
        ['key' => 'address', 'type' => 'text', 'label' => 'Address'],
        ['key' => 'button', 'type' => 'text', 'label' => 'Button'],
        ['key' => 'href', 'type' => 'text', 'label' => 'Directions link', 'columnSpanFull' => true],
        ['key' => 'embed', 'type' => 'text', 'label' => 'Map embed URL', 'columnSpanFull' => true],
    ]],
    'seo' => $pageSeoSection,
];

$legalSections = [
    'hero' => ['label' => 'Hero', 'fields' => [
        ['key' => 'crumb', 'type' => 'text', 'label' => 'Breadcrumb'],
        ['key' => 'kicker', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'titleBefore', 'type' => 'text', 'label' => 'Title before italic'],
        ['key' => 'titleEm', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'titleAfter', 'type' => 'text', 'label' => 'Title after italic'],
        ['key' => 'updated', 'type' => 'text', 'label' => 'Last updated'],
        ['key' => 'company', 'type' => 'text', 'label' => 'Company line'],
        ['key' => 'intro', 'type' => 'textarea', 'label' => 'Intro', 'columnSpanFull' => true],
        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
    ]],
    'chapters' => ['label' => 'Chapters', 'fields' => [
        ['key' => 'chapters', 'type' => 'repeater', 'label' => 'Chapters', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
            ['key' => 'id', 'type' => 'text', 'label' => 'Anchor'],
            ['key' => 'num', 'type' => 'text', 'label' => 'Number'],
            ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
            ['key' => 'body', 'type' => 'textarea', 'label' => 'Paragraphs', 'columnSpanFull' => true, 'helperText' => 'Separate paragraphs with a blank line.'],
            ['key' => 'bullets', 'type' => 'tags', 'label' => 'Bullet list'],
            ['key' => 'calloutTitle', 'type' => 'text', 'label' => 'Callout title'],
            ['key' => 'calloutBody', 'type' => 'textarea', 'label' => 'Callout body', 'columnSpanFull' => true],
        ]],
    ]],
    'ask' => ['label' => 'Questions card', 'fields' => [
        ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
        ['key' => 'body', 'type' => 'textarea', 'label' => 'Body', 'columnSpanFull' => true],
    ]],
    'seo' => $pageSeoSection,
];

$guideSections = [
    'hero' => ['label' => 'Hero', 'fields' => [
        ['key' => 'crumb', 'type' => 'text', 'label' => 'Breadcrumb'],
        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'headingBefore', 'type' => 'text', 'label' => 'Heading before italic'],
        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'headingAfter', 'type' => 'text', 'label' => 'Heading after italic'],
        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
        ['key' => 'searchPlaceholder', 'type' => 'text', 'label' => 'Search placeholder'],
        ['key' => 'categories', 'type' => 'tags', 'label' => 'Category tabs'],
    ]],
    'list' => ['label' => 'Guide list', 'fields' => [
        ['key' => 'heading', 'type' => 'text', 'label' => 'List heading'],
        ['key' => 'sortNewest', 'type' => 'text', 'label' => 'Newest label'],
        ['key' => 'sortOldest', 'type' => 'text', 'label' => 'Oldest label'],
        ['key' => 'readLabel', 'type' => 'text', 'label' => 'Read label'],
        ['key' => 'featuredLabel', 'type' => 'text', 'label' => 'Featured label'],
        ['key' => 'featuredButton', 'type' => 'text', 'label' => 'Featured button'],
    ]],
    'newsletter' => ['label' => 'Newsletter', 'fields' => [
        ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'columnSpanFull' => true],
        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
        ['key' => 'placeholder', 'type' => 'text', 'label' => 'Email placeholder'],
        ['key' => 'button', 'type' => 'text', 'label' => 'Button'],
    ]],
    'seo' => $pageSeoSection,
];

$campSections = [
    'hero' => ['label' => 'Hero', 'fields' => [
        ['key' => 'crumb', 'type' => 'text', 'label' => 'Breadcrumb'],
        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'headingBefore', 'type' => 'text', 'label' => 'Heading before italic'],
        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'headingAfter', 'type' => 'text', 'label' => 'Heading after italic'],
        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
        ['key' => 'button', 'type' => 'text', 'label' => 'Button'],
        ['key' => 'buttonHref', 'type' => 'text', 'label' => 'Button link'],
    ]],
    'filters' => ['label' => 'Filters', 'fields' => [
        ['key' => 'searchPlaceholder', 'type' => 'text', 'label' => 'Search placeholder'],
        ['key' => 'regionLabel', 'type' => 'text', 'label' => 'Region label'],
        ['key' => 'regions', 'type' => 'tags', 'label' => 'Regions'],
        ['key' => 'winterLabel', 'type' => 'text', 'label' => 'Winter filter'],
        ['key' => 'clearLabel', 'type' => 'text', 'label' => 'Clear label'],
    ]],
    'sites' => ['label' => 'Campsites', 'fields' => [
        ['key' => 'sites', 'type' => 'repeater', 'label' => 'Campsites', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
            ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
            ['key' => 'region', 'type' => 'text', 'label' => 'Region'],
            ['key' => 'place', 'type' => 'text', 'label' => 'Place'],
            ['key' => 'season', 'type' => 'text', 'label' => 'Season'],
            ['key' => 'winter', 'type' => 'toggle', 'label' => 'Open in winter'],
            ['key' => 'facilities', 'type' => 'tags', 'label' => 'Facilities', 'helperText' => 'bath, zap, wifi, house, sparkles'],
        ]],
    ]],
    'seo' => $pageSeoSection,
];

$loginSections = [
    'visual' => ['label' => 'Photo panel', 'fields' => [
        ['key' => 'kicker', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'headingBefore', 'type' => 'text', 'label' => 'Heading before italic'],
        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'headingAfter', 'type' => 'text', 'label' => 'Heading after italic'],
        ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
        ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
        ['key' => 'pills', 'type' => 'repeater', 'label' => 'Pills', 'columnSpanFull' => true, 'fields' => [
            ['key' => 'icon', 'type' => 'text', 'label' => 'Icon'],
            ['key' => 'text', 'type' => 'text', 'label' => 'Text'],
        ]],
    ]],
    'chrome' => ['label' => 'Form chrome', 'isRootSection' => true, 'fields' => [
        ['key' => 'backLabel', 'type' => 'text', 'label' => 'Back link'],
        ['key' => 'newPrompt', 'type' => 'text', 'label' => 'New account prompt'],
        ['key' => 'newLink', 'type' => 'text', 'label' => 'Create account link'],
        ['key' => 'copyright', 'type' => 'text', 'label' => 'Copyright'],
    ]],
    'form' => ['label' => 'Sign-in form', 'isRootSection' => true, 'fields' => [
        ['key' => 'formKicker', 'type' => 'text', 'label' => 'Eyebrow'],
        ['key' => 'formHeadingBefore', 'type' => 'text', 'label' => 'Heading before italic'],
        ['key' => 'formHeadingAccent', 'type' => 'text', 'label' => 'Italic word'],
        ['key' => 'formHeadingAfter', 'type' => 'text', 'label' => 'Heading after italic'],
        ['key' => 'formLead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
        ['key' => 'emailLabel', 'type' => 'text', 'label' => 'Email label'],
        ['key' => 'passwordLabel', 'type' => 'text', 'label' => 'Password label'],
        ['key' => 'rememberLabel', 'type' => 'text', 'label' => 'Remember me'],
        ['key' => 'forgotPasswordLabel', 'type' => 'text', 'label' => 'Forgot password'],
        ['key' => 'submitLabel', 'type' => 'text', 'label' => 'Sign in button'],
        ['key' => 'signingInLabel', 'type' => 'text', 'label' => 'Signing in label'],
        ['key' => 'orLabel', 'type' => 'text', 'label' => 'Divider'],
        ['key' => 'registerLink', 'type' => 'text', 'label' => 'Create account button'],
        ['key' => 'hostSignInLabel', 'type' => 'text', 'label' => 'Host sign-in link'],
    ]],
    'seo' => $pageSeoSection,
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
                'colors' => [
                    'label' => 'Site colors',
                    'fields' => [
                        ['key' => 'navy', 'type' => 'color', 'label' => 'Navy', 'helperText' => 'Top bar, headings, and dark buttons.'],
                        ['key' => 'ink', 'type' => 'color', 'label' => 'Ink', 'helperText' => 'Main body text.'],
                        ['key' => 'slate', 'type' => 'color', 'label' => 'Slate', 'helperText' => 'Secondary text.'],
                        ['key' => 'slateLight', 'type' => 'color', 'label' => 'Slate light', 'helperText' => 'Captions and muted labels.'],
                        ['key' => 'green', 'type' => 'color', 'label' => 'Green', 'helperText' => 'Primary buttons and success states.'],
                        ['key' => 'greenDark', 'type' => 'color', 'label' => 'Green hover', 'helperText' => 'Primary button hover.'],
                        ['key' => 'blue', 'type' => 'color', 'label' => 'Blue', 'helperText' => 'Links and secondary actions.'],
                        ['key' => 'blueSoft', 'type' => 'color', 'label' => 'Blue soft', 'helperText' => 'Soft panels and selected chips.'],
                        ['key' => 'line', 'type' => 'color', 'label' => 'Line', 'helperText' => 'Borders and dividers.'],
                        ['key' => 'bg', 'type' => 'color', 'label' => 'Background', 'helperText' => 'Page background.'],
                        ['key' => 'red', 'type' => 'color', 'label' => 'Red', 'helperText' => 'Errors and destructive actions.'],
                        ['key' => 'accent', 'type' => 'color', 'label' => 'Accent', 'helperText' => 'Calendar highlights and extra emphasis.'],
                    ],
                ],
                'googleMaps' => [
                    'label' => 'Google Maps',
                    'fields' => [
                        [
                            'key' => 'mapsApiKey',
                            'type' => 'password',
                            'label' => 'Google Maps API key',
                            'helperText' => 'Used for Google Reviews, host address autocomplete, and listing map previews. Enable Places API and Maps JavaScript API, then restrict the key to your site referrers in Google Cloud. This key is not shown on the public site.',
                            'columnSpanFull' => true,
                        ],
                    ],
                ],
                'topbar' => [
                    'label' => 'Top bar',
                    'fields' => [
                        ['key' => 'text', 'type' => 'text', 'label' => 'Banner text (desktop)', 'columnSpanFull' => true],
                        ['key' => 'mobileText', 'type' => 'text', 'label' => 'Banner text (mobile)', 'helperText' => 'Shorter copy for small screens. Uses desktop text if empty.', 'columnSpanFull' => true],
                        ['key' => 'linkLabel', 'type' => 'text', 'label' => 'Link label'],
                        ['key' => 'mobileLinkLabel', 'type' => 'text', 'label' => 'Link label (mobile)', 'helperText' => 'Shorter link text for small screens. Uses desktop link label if empty.'],
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
                        ['key' => 'currencyMenuLabel', 'type' => 'text', 'label' => 'Currency menu heading'],
                        ['key' => 'signInLabel', 'type' => 'text', 'label' => 'Sign in label'],
                        ['key' => 'signInHref', 'type' => 'text', 'label' => 'Sign in link'],
                        ['key' => 'accountLabel', 'type' => 'text', 'label' => 'Signed-in label'],
                        ['key' => 'currencyNote', 'type' => 'text', 'label' => 'Currency note', 'columnSpanFull' => true],
                        ['key' => 'hostSignInLabel', 'type' => 'text', 'label' => 'Host sign-in link', 'helperText' => 'Shown under the login form.'],
                        ['key' => 'supportPhone', 'type' => 'text', 'label' => 'Support phone'],
                        ['key' => 'supportEmail', 'type' => 'text', 'label' => 'Support email'],
                        ['key' => 'supportHours', 'type' => 'text', 'label' => 'Support hours', 'columnSpanFull' => true],
                    ],
                ],
                'cookieBanner' => [
                    'label' => 'Cookie banner',
                    'fields' => [
                        ['key' => 'message', 'type' => 'textarea', 'label' => 'Message', 'columnSpanFull' => true],
                        ['key' => 'acceptLabel', 'type' => 'text', 'label' => 'Accept label'],
                        ['key' => 'rejectLabel', 'type' => 'text', 'label' => 'Reject label'],
                        ['key' => 'policyLabel', 'type' => 'text', 'label' => 'Policy link label'],
                        ['key' => 'policyHref', 'type' => 'text', 'label' => 'Policy URL'],
                    ],
                ],
                'searchForm' => [
                    'label' => 'Search form & filters',
                    'fields' => [
                        ['key' => 'pickupLabel', 'type' => 'text', 'label' => 'Pick-up location label'],
                        ['key' => 'dropoffLabel', 'type' => 'text', 'label' => 'Drop-off location label'],
                        ['key' => 'locationPlaceholder', 'type' => 'text', 'label' => 'Location placeholder'],
                        ['key' => 'vehicleDatesLabel', 'type' => 'text', 'label' => 'Vehicle dates label'],
                        ['key' => 'pickupDateLabel', 'type' => 'text', 'label' => 'Pick-up date label'],
                        ['key' => 'dropoffDateLabel', 'type' => 'text', 'label' => 'Drop-off date label'],
                        ['key' => 'cityLabel', 'type' => 'text', 'label' => 'City label'],
                        ['key' => 'cityPlaceholder', 'type' => 'text', 'label' => 'City placeholder'],
                        ['key' => 'cityPlaceholderShort', 'type' => 'text', 'label' => 'City placeholder (short)'],
                        ['key' => 'stayDatesLabel', 'type' => 'text', 'label' => 'Stay dates label'],
                        ['key' => 'checkInLabel', 'type' => 'text', 'label' => 'Check-in label'],
                        ['key' => 'checkOutLabel', 'type' => 'text', 'label' => 'Check-out label'],
                        ['key' => 'guestsLabel', 'type' => 'text', 'label' => 'Guests label'],
                        ['key' => 'guestSingular', 'type' => 'text', 'label' => 'Guest singular'],
                        ['key' => 'guestPlural', 'type' => 'text', 'label' => 'Guest plural'],
                        ['key' => 'emptyLocationsHint', 'type' => 'textarea', 'label' => 'Empty locations hint', 'columnSpanFull' => true],
                        ['key' => 'emptyLocationsHintShort', 'type' => 'textarea', 'label' => 'Empty locations hint (search page)', 'columnSpanFull' => true],
                        ['key' => 'updateLabel', 'type' => 'text', 'label' => 'Update search button'],
                        ['key' => 'priceChipLabel', 'type' => 'text', 'label' => 'Price chip'],
                        ['key' => 'priceHeading', 'type' => 'text', 'label' => 'Price heading', 'helperText' => 'Use {unit} for day or night.'],
                        ['key' => 'transmissionLabel', 'type' => 'text', 'label' => 'Transmission label'],
                        ['key' => 'anyLabel', 'type' => 'text', 'label' => 'Any option'],
                        ['key' => 'automaticLabel', 'type' => 'text', 'label' => 'Automatic label'],
                        ['key' => 'manualLabel', 'type' => 'text', 'label' => 'Manual label'],
                        ['key' => 'allFiltersLabel', 'type' => 'text', 'label' => 'All filters label'],
                        ['key' => 'filtersTitle', 'type' => 'text', 'label' => 'Filters panel title'],
                        ['key' => 'vehicleTypeLabel', 'type' => 'text', 'label' => 'Vehicle type label'],
                        ['key' => 'featuresLabel', 'type' => 'text', 'label' => 'Features label'],
                        ['key' => 'minSeatsLabel', 'type' => 'text', 'label' => 'Minimum seats label'],
                        ['key' => 'minSleepsLabel', 'type' => 'text', 'label' => 'Minimum sleeps label'],
                        ['key' => 'resetRangeLabel', 'type' => 'text', 'label' => 'Reset range label'],
                        ['key' => 'clearAllLabel', 'type' => 'text', 'label' => 'Clear all label'],
                        ['key' => 'clearAllChipLabel', 'type' => 'text', 'label' => 'Clear all chip'],
                        ['key' => 'sortPrefix', 'type' => 'text', 'label' => 'Sort prefix'],
                        ['key' => 'loadingLabel', 'type' => 'text', 'label' => 'Loading label'],
                        ['key' => 'unavailableLabel', 'type' => 'text', 'label' => 'Unavailable label'],
                        ['key' => 'loadingPlural', 'type' => 'text', 'label' => 'Loading results', 'helperText' => 'Use {plural} for vans, cars, or stays.'],
                        ['key' => 'loadFailedPlural', 'type' => 'text', 'label' => 'Load failed', 'helperText' => 'Use {plural} for vans, cars, or stays.'],
                        ['key' => 'foundSuffix', 'type' => 'text', 'label' => 'Found suffix'],
                        ['key' => 'editLabel', 'type' => 'text', 'label' => 'Edit search label'],
                        ['key' => 'showPrefix', 'type' => 'text', 'label' => 'Show results prefix'],
                        ['key' => 'sortRecommended', 'type' => 'text', 'label' => 'Sort: recommended'],
                        ['key' => 'sortPriceLow', 'type' => 'text', 'label' => 'Sort: price low to high'],
                        ['key' => 'sortPriceHigh', 'type' => 'text', 'label' => 'Sort: price high to low'],
                        ['key' => 'sortSeats', 'type' => 'text', 'label' => 'Sort: most seats'],
                        ['key' => 'sortSleeps', 'type' => 'text', 'label' => 'Sort: sleeps the most'],
                        ['key' => 'sortGuests', 'type' => 'text', 'label' => 'Sort: most guests'],
                    ],
                ],
                'cards' => [
                    'label' => 'Listing cards',
                    'fields' => [
                        ['key' => 'priceFromLabel', 'type' => 'text', 'label' => 'Price prefix'],
                        ['key' => 'bookLabel', 'type' => 'text', 'label' => 'Book button'],
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
                        ['key' => 'paymentsLabel', 'type' => 'text', 'label' => 'Payments label'],
                        ['key' => 'newsletterHeading', 'type' => 'text', 'label' => 'Newsletter heading'],
                        ['key' => 'newsletterLead', 'type' => 'text', 'label' => 'Newsletter lead', 'columnSpanFull' => true],
                        ['key' => 'newsletterPlaceholder', 'type' => 'text', 'label' => 'Newsletter placeholder'],
                        ['key' => 'newsletterButton', 'type' => 'text', 'label' => 'Newsletter button'],
                        ['key' => 'logoutLabel', 'type' => 'text', 'label' => 'Log out label'],
                        ['key' => 'hostSubtitle', 'type' => 'text', 'label' => 'Host link subtitle'],
                        ['key' => 'columns', 'type' => 'repeater', 'label' => 'Columns', 'columnSpanFull' => true, 'fields' => [
                            ['key' => 'key', 'type' => 'text', 'label' => 'Column key', 'helperText' => 'book, company, or account. Keeps the column in place if you rename the title.'],
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
                        ['key' => 'buttonLabel', 'type' => 'text', 'label' => 'Button label'],
                        ['key' => 'buttonLoadingLabel', 'type' => 'text', 'label' => 'Button loading label'],
                        ['key' => 'footnote', 'type' => 'text', 'label' => 'Footnote', 'columnSpanFull' => true],
                        ['key' => 'backgroundAlt', 'type' => 'text', 'label' => 'Background image alt'],
                        ['key' => 'successMessage', 'type' => 'text', 'label' => 'Success message'],
                        ['key' => 'errorMessage', 'type' => 'text', 'label' => 'Error message', 'columnSpanFull' => true],
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
                'cover' => [
                    'label' => 'Cover',
                    'fields' => [
                        ['key' => 'visible', 'type' => 'toggle', 'label' => 'Show section'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                        ['key' => 'headingAccent', 'type' => 'text', 'label' => 'Italic ending'],
                        ['key' => 'backgroundImage', 'type' => 'image', 'label' => 'Cover photo'],
                        ['key' => 'backgroundAlt', 'type' => 'text', 'label' => 'Cover photo alt'],
                        ['key' => 'whereLabel', 'type' => 'text', 'label' => 'Where label'],
                        ['key' => 'wherePlaceholder', 'type' => 'text', 'label' => 'Where placeholder'],
                        ['key' => 'whenLabel', 'type' => 'text', 'label' => 'When label'],
                        ['key' => 'whenPlaceholder', 'type' => 'text', 'label' => 'When placeholder'],
                        ['key' => 'whoLabel', 'type' => 'text', 'label' => 'Who label'],
                        ['key' => 'whoValue', 'type' => 'text', 'label' => 'Who default'],
                        ['key' => 'tabs', 'type' => 'repeater', 'label' => 'Search tabs', 'fields' => [
                            ['key' => 'id', 'type' => 'text', 'label' => 'ID'],
                            ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                        ], 'columnSpanFull' => true],
                    ],
                ],
                'countries' => [
                    'label' => 'Destinations',
                    'fields' => [
                        ['key' => 'visible', 'type' => 'toggle', 'label' => 'Show section'],
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                        ['key' => 'tabs', 'type' => 'tags', 'label' => 'Region tabs', 'helperText' => 'First tab should be All.'],
                        ['key' => 'cards', 'type' => 'repeater', 'label' => 'Country cards', 'fields' => [
                            ['key' => 'code', 'type' => 'text', 'label' => 'Code'],
                            ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
                            ['key' => 'region', 'type' => 'text', 'label' => 'Region'],
                            ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                            ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Photo alt'],
                            ['key' => 'listings', 'type' => 'text', 'label' => 'Listings line'],
                            ['key' => 'emptyCta', 'type' => 'text', 'label' => 'Empty call to action'],
                            ['key' => 'href', 'type' => 'text', 'label' => 'Link'],
                            ['key' => 'campers', 'type' => 'text', 'label' => 'Camper count'],
                            ['key' => 'cars', 'type' => 'text', 'label' => 'Car count'],
                            ['key' => 'stays', 'type' => 'text', 'label' => 'Stay count'],
                        ], 'columnSpanFull' => true],
                    ],
                ],
                'roadSigns' => [
                    'label' => 'Road signs',
                    'fields' => [
                        ['key' => 'visible', 'type' => 'toggle', 'label' => 'Show section'],
                        ['key' => 'signs', 'type' => 'repeater', 'label' => 'Signs', 'fields' => [
                            ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                            ['key' => 'distance', 'type' => 'text', 'label' => 'Distance'],
                        ], 'columnSpanFull' => true],
                    ],
                ],
                'popularCampervans' => [
                    'label' => 'Popular campervans',
                    'fields' => [
                        ['key' => 'visible', 'type' => 'toggle', 'label' => 'Show section'],
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                        ['key' => 'buttonLabel', 'type' => 'text', 'label' => 'Button label'],
                        ['key' => 'buttonHref', 'type' => 'text', 'label' => 'Button link'],
                    ],
                ],
                'seasons' => [
                    'label' => 'Seasons',
                    'fields' => [
                        ['key' => 'visible', 'type' => 'toggle', 'label' => 'Show section'],
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                        ['key' => 'items', 'type' => 'repeater', 'label' => 'Seasons', 'fields' => [
                            ['key' => 'icon', 'type' => 'text', 'label' => 'Icon'],
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                            ['key' => 'months', 'type' => 'text', 'label' => 'Months'],
                            ['key' => 'text', 'type' => 'text', 'label' => 'Line'],
                        ], 'columnSpanFull' => true],
                    ],
                ],
                'popularCars' => [
                    'label' => 'Popular cars',
                    'fields' => [
                        ['key' => 'visible', 'type' => 'toggle', 'label' => 'Show section'],
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                        ['key' => 'buttonLabel', 'type' => 'text', 'label' => 'Button label'],
                        ['key' => 'buttonHref', 'type' => 'text', 'label' => 'Button link'],
                    ],
                ],
                'howTicket' => [
                    'label' => 'How it works',
                    'fields' => [
                        ['key' => 'visible', 'type' => 'toggle', 'label' => 'Show section'],
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                        ['key' => 'steps', 'type' => 'repeater', 'label' => 'Steps', 'fields' => [
                            ['key' => 'num', 'type' => 'text', 'label' => 'Step label'],
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                            ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
                        ], 'columnSpanFull' => true],
                    ],
                ],
                'placesToStay' => [
                    'label' => 'Places to stay',
                    'fields' => [
                        ['key' => 'visible', 'type' => 'toggle', 'label' => 'Show section'],
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                        ['key' => 'tabs', 'type' => 'tags', 'label' => 'Stay tabs', 'helperText' => 'First tab should be All.'],
                        ['key' => 'buttonLabel', 'type' => 'text', 'label' => 'Button label'],
                        ['key' => 'buttonHref', 'type' => 'text', 'label' => 'Button link'],
                    ],
                ],
                'guides' => [
                    'label' => 'Guides',
                    'fields' => [
                        ['key' => 'visible', 'type' => 'toggle', 'label' => 'Show section'],
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                        ['key' => 'buttonLabel', 'type' => 'text', 'label' => 'Button label'],
                        ['key' => 'buttonHref', 'type' => 'text', 'label' => 'Button link'],
                        ['key' => 'items', 'type' => 'repeater', 'label' => 'Guides', 'fields' => [
                            ['key' => 'tag', 'type' => 'text', 'label' => 'Tag'],
                            ['key' => 'minutes', 'type' => 'text', 'label' => 'Read time'],
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                            ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
                            ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                            ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Photo alt'],
                            ['key' => 'href', 'type' => 'text', 'label' => 'Link'],
                        ], 'columnSpanFull' => true],
                    ],
                ],
                'hostBanner' => [
                    'label' => 'Host CTA',
                    'fields' => [
                        ['key' => 'visible', 'type' => 'toggle', 'label' => 'Show section'],
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'heading', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                        ['key' => 'buttonLabel', 'type' => 'text', 'label' => 'Button label'],
                        ['key' => 'buttonHref', 'type' => 'text', 'label' => 'Button link'],
                        ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                        ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Photo alt'],
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
            'sections' => $aboutSections,
        ],
        'faq' => [
            'label' => 'FAQ',
            'group' => 'Marketing',
            'preview_route' => '/faq',
            'sort_order' => 3,
            'sections' => $faqSections,
        ],
        'contact' => [
            'label' => 'Contact',
            'group' => 'Marketing',
            'preview_route' => '/contact',
            'sort_order' => 4,
            'sections' => $contactSections,
        ],
        'terms' => [
            'label' => 'Terms',
            'group' => 'Marketing',
            'preview_route' => '/terms',
            'sort_order' => 5,
            'sections' => $legalSections,
        ],
        'privacy' => [
            'label' => 'Privacy',
            'group' => 'Marketing',
            'preview_route' => '/privacy',
            'sort_order' => 6,
            'sections' => $legalSections,
        ],
        'refund-policy' => [
            'label' => 'Refund Policy',
            'group' => 'Marketing',
            'preview_route' => '/refund-policy',
            'sort_order' => 7,
            'sections' => $legalSections,
        ],
        'cookies' => [
            'label' => 'Cookies',
            'group' => 'Marketing',
            'preview_route' => '/cookies',
            'sort_order' => 8,
            'sections' => $legalSections,
        ],
        'cardholder-terms' => [
            'label' => 'Cardholder Terms',
            'group' => 'Marketing',
            'preview_route' => '/cardholder-terms',
            'sort_order' => 9,
            'sections' => $legalSections,
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
                    ['key' => 'earnSuffix', 'type' => 'text', 'label' => 'Earnings suffix'],
                    ['key' => 'points', 'type' => 'tags', 'label' => 'Trust points', 'columnSpanFull' => true],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Background photo'],
                    ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Background photo alt text'],
                    ['key' => 'submitLabel', 'type' => 'text', 'label' => 'Submit label'],
                    ['key' => 'submittingLabel', 'type' => 'text', 'label' => 'Submitting label'],
                    ['key' => 'signupTitle', 'type' => 'text', 'label' => 'Signup title'],
                    ['key' => 'individualLabel', 'type' => 'text', 'label' => 'Individual tab'],
                    ['key' => 'businessLabel', 'type' => 'text', 'label' => 'Business tab'],
                    ['key' => 'signupLead', 'type' => 'textarea', 'label' => 'Signup lead', 'columnSpanFull' => true],
                    ['key' => 'nameLabel', 'type' => 'text', 'label' => 'Name label'],
                    ['key' => 'namePlaceholder', 'type' => 'text', 'label' => 'Name placeholder'],
                    ['key' => 'emailLabel', 'type' => 'text', 'label' => 'Email label'],
                    ['key' => 'emailPlaceholder', 'type' => 'text', 'label' => 'Email placeholder'],
                    ['key' => 'phoneLabel', 'type' => 'text', 'label' => 'Phone label'],
                    ['key' => 'passwordLabel', 'type' => 'text', 'label' => 'Password label'],
                    ['key' => 'passwordPlaceholder', 'type' => 'text', 'label' => 'Password placeholder'],
                    ['key' => 'confirmPasswordLabel', 'type' => 'text', 'label' => 'Confirm password label'],
                    ['key' => 'confirmPasswordPlaceholder', 'type' => 'text', 'label' => 'Confirm password placeholder'],
                    ['key' => 'loginPrompt', 'type' => 'text', 'label' => 'Login prompt'],
                    ['key' => 'loginLink', 'type' => 'text', 'label' => 'Login link'],
                    ['key' => 'forgotPasswordLabel', 'type' => 'text', 'label' => 'Forgot password link'],
                ]],
                'howSection' => ['label' => 'How it works heading', 'fields' => [
                    ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'columnSpanFull' => true],
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
                'featuresSection' => ['label' => 'Earn more section', 'fields' => [
                    ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'columnSpanFull' => true, 'path' => 'featuresSection.heading'],
                    ['key' => 'subheading', 'type' => 'textarea', 'label' => 'Subheading', 'columnSpanFull' => true, 'path' => 'featuresSection.subheading'],
                ]],
                'features' => ['label' => 'Feature bento', 'fields' => [
                    ['key' => 'features', 'type' => 'repeater', 'label' => 'Features', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
                        ['key' => 'widgetLabel', 'type' => 'text', 'label' => 'Widget label', 'helperText' => 'Shown on the first card when no photo is uploaded.'],
                        ['key' => 'widgetChips', 'type' => 'tags', 'label' => 'Widget chips'],
                        ['key' => 'image', 'type' => 'image', 'label' => 'Photo', 'helperText' => 'Optional on every card. Card 1 shows the map widget when empty; upload a photo to replace it. Cards 2–4 use photo layouts.'],
                        ['key' => 'imageAlt', 'type' => 'text', 'label' => 'Photo alt text'],
                    ]],
                ]],
                'reviews' => ['label' => 'Host reviews', 'fields' => [
                    ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'columnSpanFull' => true, 'path' => 'reviews.heading'],
                    ['key' => 'subheading', 'type' => 'textarea', 'label' => 'Subheading', 'columnSpanFull' => true, 'path' => 'reviews.subheading'],
                    ['key' => 'rating', 'type' => 'text', 'label' => 'Rating', 'path' => 'reviews.rating'],
                    ['key' => 'ratingLabel', 'type' => 'text', 'label' => 'Rating label', 'path' => 'reviews.ratingLabel'],
                    ['key' => 'up', 'type' => 'repeater', 'label' => 'Top row reviews', 'path' => 'reviews.up', 'fields' => $hostReviewFields, 'columnSpanFull' => true],
                    ['key' => 'down', 'type' => 'repeater', 'label' => 'Bottom row reviews', 'path' => 'reviews.down', 'fields' => $hostReviewFields, 'columnSpanFull' => true],
                ]],
                'faqSection' => ['label' => 'FAQ heading', 'fields' => [
                    ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'columnSpanFull' => true],
                    ['key' => 'subheading', 'type' => 'textarea', 'label' => 'Subheading', 'columnSpanFull' => true],
                    ['key' => 'contactLabel', 'type' => 'text', 'label' => 'Contact label'],
                    ['key' => 'contactEmail', 'type' => 'text', 'label' => 'Contact email'],
                ]],
                'faqItems' => ['label' => 'FAQ', 'fields' => [
                    ['key' => 'faqItems', 'type' => 'repeater', 'label' => 'FAQ items', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => $faqItemRepeater],
                ]],
                'cta' => ['label' => 'Bottom CTA', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Lead', 'columnSpanFull' => true],
                    ['key' => 'submitLabel', 'type' => 'text', 'label' => 'Submit label'],
                    ['key' => 'secondaryLabel', 'type' => 'text', 'label' => 'Secondary label'],
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
            'sections' => $guideSections,
        ],
        'campsite-map' => [
            'label' => 'Campsite map',
            'group' => 'Marketing',
            'preview_route' => '/campsite-map',
            'sort_order' => 9,
            'sections' => $campSections,
        ],
        'search-campervan' => [
            'label' => 'Search, Campervans',
            'group' => 'Transactional',
            'preview_route' => '/campervans',
            'sort_order' => 10,
            'sections' => $searchFigmaSections,
        ],
        'search-car' => [
            'label' => 'Search, Cars',
            'group' => 'Transactional',
            'preview_route' => '/cars',
            'sort_order' => 11,
            'sections' => $searchFigmaSections,
        ],
        'search-guesthouse' => [
            'label' => 'Search, Guesthouses',
            'group' => 'Transactional',
            'preview_route' => '/guesthouses',
            'sort_order' => 12,
            'sections' => array_replace($searchFigmaSections, [
                'routing' => ['label' => 'Catalog routing', 'isRootSection' => true, 'fields' => array_merge($searchRoutingFields, [
                    ['key' => 'introLocationDefault', 'type' => 'text', 'label' => 'Default location intro'],
                ])],
            ]),
        ],
        'listing-campervan' => [
            'label' => 'Listing, Campervan',
            'group' => 'Transactional',
            'preview_route' => '/campervans',
            'sort_order' => 13,
            'sections' => $listingFigmaSections,
        ],
        'listing-car' => [
            'label' => 'Listing, Car',
            'group' => 'Transactional',
            'preview_route' => '/cars',
            'sort_order' => 14,
            'sections' => $listingFigmaSections,
        ],
        'listing-guesthouse' => [
            'label' => 'Listing, Guesthouse',
            'group' => 'Transactional',
            'preview_route' => '/guesthouses',
            'sort_order' => 15,
            'sections' => $listingFigmaSections,
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
            'sections' => array_merge(array_diff_key($loginSections, ['seo' => true]), [
                'hostLogin' => ['label' => 'Host sign in', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                    ['key' => 'submitLabel', 'type' => 'text', 'label' => 'Submit label'],
                    ['key' => 'signingInLabel', 'type' => 'text', 'label' => 'Signing in label'],
                    ['key' => 'forgotPasswordLabel', 'type' => 'text', 'label' => 'Forgot password label'],
                    ['key' => 'registerPrompt', 'type' => 'text', 'label' => 'Register prompt'],
                    ['key' => 'registerLink', 'type' => 'text', 'label' => 'Register link text'],
                    ['key' => 'heroTitle', 'type' => 'text', 'label' => 'Side title', 'columnSpanFull' => true],
                    ['key' => 'heroText', 'type' => 'textarea', 'label' => 'Side text', 'columnSpanFull' => true],
                ]],
                'forgot' => ['label' => 'Forgot password', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'hostTitle', 'type' => 'text', 'label' => 'Host title'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                    ['key' => 'hostSubtitle', 'type' => 'textarea', 'label' => 'Host subtitle', 'columnSpanFull' => true],
                    ['key' => 'heroTitle', 'type' => 'text', 'label' => 'Side title'],
                    ['key' => 'hostHeroTitle', 'type' => 'text', 'label' => 'Host side title'],
                    ['key' => 'heroText', 'type' => 'textarea', 'label' => 'Side text', 'columnSpanFull' => true],
                    ['key' => 'emailLabel', 'type' => 'text', 'label' => 'Email label'],
                    ['key' => 'submitLabel', 'type' => 'text', 'label' => 'Submit label'],
                    ['key' => 'sendingLabel', 'type' => 'text', 'label' => 'Sending label'],
                    ['key' => 'sentMessage', 'type' => 'textarea', 'label' => 'Sent message', 'columnSpanFull' => true],
                    ['key' => 'retryHint', 'type' => 'textarea', 'label' => 'Retry hint', 'columnSpanFull' => true],
                    ['key' => 'backLabel', 'type' => 'text', 'label' => 'Back to sign in'],
                    ['key' => 'rememberPrompt', 'type' => 'text', 'label' => 'Remember prompt'],
                    ['key' => 'signInLabel', 'type' => 'text', 'label' => 'Sign in link'],
                ]],
                'reset' => ['label' => 'Reset password', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                    ['key' => 'heroTitle', 'type' => 'text', 'label' => 'Side title', 'columnSpanFull' => true],
                    ['key' => 'heroText', 'type' => 'textarea', 'label' => 'Side text', 'columnSpanFull' => true],
                    ['key' => 'emailLabel', 'type' => 'text', 'label' => 'Email label'],
                    ['key' => 'emailPlaceholder', 'type' => 'text', 'label' => 'Email placeholder'],
                    ['key' => 'passwordLabel', 'type' => 'text', 'label' => 'New password label'],
                    ['key' => 'confirmLabel', 'type' => 'text', 'label' => 'Confirm password label'],
                    ['key' => 'passwordHint', 'type' => 'text', 'label' => 'Password hint'],
                    ['key' => 'submitLabel', 'type' => 'text', 'label' => 'Submit label'],
                    ['key' => 'submittingLabel', 'type' => 'text', 'label' => 'Submitting label'],
                    ['key' => 'backPrompt', 'type' => 'text', 'label' => 'Back prompt'],
                    ['key' => 'guestSignInLabel', 'type' => 'text', 'label' => 'Guest sign-in link'],
                    ['key' => 'orLabel', 'type' => 'text', 'label' => 'Or label'],
                    ['key' => 'hostSignInLabel', 'type' => 'text', 'label' => 'Host sign-in link'],
                ]],
                'seo' => $pageSeoSection,
            ]),
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
                    ['key' => 'heroTitle', 'type' => 'text', 'label' => 'Side title', 'columnSpanFull' => true],
                    ['key' => 'heroText', 'type' => 'textarea', 'label' => 'Side text', 'columnSpanFull' => true],
                    ['key' => 'nameLabel', 'type' => 'text', 'label' => 'Name label'],
                    ['key' => 'namePlaceholder', 'type' => 'text', 'label' => 'Name placeholder'],
                    ['key' => 'emailLabel', 'type' => 'text', 'label' => 'Email label'],
                    ['key' => 'emailPlaceholder', 'type' => 'text', 'label' => 'Email placeholder'],
                    ['key' => 'phoneLabel', 'type' => 'text', 'label' => 'Phone label'],
                    ['key' => 'phonePlaceholder', 'type' => 'text', 'label' => 'Phone placeholder'],
                    ['key' => 'passwordLabel', 'type' => 'text', 'label' => 'Password label'],
                    ['key' => 'confirmPasswordLabel', 'type' => 'text', 'label' => 'Confirm password label'],
                    ['key' => 'passwordHint', 'type' => 'text', 'label' => 'Password hint'],
                    ['key' => 'submitLabel', 'type' => 'text', 'label' => 'Submit label'],
                    ['key' => 'submittingLabel', 'type' => 'text', 'label' => 'Submitting label'],
                    ['key' => 'loginPrompt', 'type' => 'text', 'label' => 'Login prompt'],
                    ['key' => 'loginLink', 'type' => 'text', 'label' => 'Login link text'],
                    ['key' => 'forgotPasswordLabel', 'type' => 'text', 'label' => 'Forgot password label'],
                    ['key' => 'features', 'type' => 'repeater', 'label' => 'Side features', 'isRootList' => true, 'columnSpanFull' => true, 'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'columnSpanFull' => true],
                    ]],
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
        'under-construction' => [
            'label' => 'Under construction',
            'group' => 'App',
            'preview_route' => '/',
            'sort_order' => 25,
            'sections' => [
                'content' => ['label' => 'Page copy', 'isRootSection' => true, 'fields' => [
                    ['key' => 'badge', 'type' => 'text', 'label' => 'Badge'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'columnSpanFull' => true],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle', 'columnSpanFull' => true],
                    ['key' => 'hint', 'type' => 'text', 'label' => 'Hint', 'columnSpanFull' => true],
                    ['key' => 'footer', 'type' => 'text', 'label' => 'Footer'],
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
                    ['key' => 'missingMessage', 'type' => 'text', 'label' => 'Missing link message', 'columnSpanFull' => true],
                    ['key' => 'successMessage', 'type' => 'text', 'label' => 'Success message'],
                    ['key' => 'errorMessage', 'type' => 'text', 'label' => 'Error message'],
                    ['key' => 'backLabel', 'type' => 'text', 'label' => 'Back link label'],
                ]],
            ],
        ],
    ],
];
