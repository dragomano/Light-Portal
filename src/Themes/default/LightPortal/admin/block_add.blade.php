@include('admin.partials.entity_add', [
    'title'       => $txt['lp_blocks'],
    'description' => $txt['lp_blocks_add_instruction'],
    'formName'    => 'block_add_form',
    'items'       => $context['lp_all_blocks'],
    'enityKey'    => 'block',
    'extraInputs' => '<input type="hidden" name="placement" value="' . $context['lp_current_block']['placement'] . '">',
    'script'      => 'const block = new Block()',
])
