@include('admin.partials.entity_add', [
    'title'       => $txt['lp_pages'],
    'description' => $txt['lp_pages_add_instruction'],
    'formName'    => 'page_add_form',
    'items'       => $context['lp_all_pages'],
    'enityKey'    => 'page',
    'script'      => 'const page = new Page()',
])
