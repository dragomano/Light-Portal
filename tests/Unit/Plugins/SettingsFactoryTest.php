<?php

declare(strict_types=1);

use LightPortal\Plugins\SettingsFactory;

describe('SettingsFactory', function () {
    describe('->make()', function () {
        it('creates new SettingsFactory instance', function () {
            $factory = SettingsFactory::make();

            expect($factory)->toBeInstanceOf(SettingsFactory::class);
        });

        it('returns fresh instance each time', function () {
            $factory1 = SettingsFactory::make();
            $factory2 = SettingsFactory::make();

            expect($factory1)->not->toBe($factory2);
        });
    });

    describe('->toArray()', function () {
        it('returns empty array for new instance', function () {
            $factory = SettingsFactory::make();
            $result = $factory->toArray();

            expect($result)->toBe([]);
        });

        it('returns array of added settings', function () {
            $factory = SettingsFactory::make()
                ->text('title')
                ->check('enabled');

            $result = $factory->toArray();

            expect($result)->toHaveCount(2)
                ->and($result[0][0])->toBe('text')
                ->and($result[0][1])->toBe('title')
                ->and($result[1][0])->toBe('check')
                ->and($result[1][1])->toBe('enabled');
        });
    });

    describe('Fluent Interface', function () {
        it('allows method chaining', function () {
            $factory = SettingsFactory::make()
                ->text('title')
                ->check('enabled')
                ->select('type', ['option1', 'option2'])
                ->color('theme_color');

            expect($factory)->toBeInstanceOf(SettingsFactory::class)
                ->and($factory->toArray())->toHaveCount(4);
        });

        it('returns same instance for chaining', function () {
            $factory = SettingsFactory::make();

            $result = $factory->text('title');

            expect($result)->toBe($factory);
        });
    });

    describe('->multiselect()', function () {
        it('creates multiselect field with options', function () {
            $factory = SettingsFactory::make();
            $options = ['option1', 'option2', 'option3'];

            $factory->multiselect('categories', $options);

            $result = $factory->toArray();

            expect($result)->toHaveCount(1)
                ->and($result[0][0])->toBe('multiselect')
                ->and($result[0][1])->toBe('categories')
                ->and($result[0][2])->toBe($options);
        });

        it('creates multiselect with empty options', function () {
            $factory = SettingsFactory::make();

            $factory->multiselect('empty', []);

            $result = $factory->toArray();

            expect($result[0][2])->toBe([]);
        });

        it('creates multiselect with associative array options', function () {
            $factory = SettingsFactory::make();
            $options = [
                'value1' => 'Label 1',
                'value2' => 'Label 2'
            ];

            $factory->multiselect('associative', $options);

            $result = $factory->toArray();

            expect($result[0][2])->toBe($options);
        });
    });

    describe('->select()', function () {
        it('creates select field with options', function () {
            $factory = SettingsFactory::make();
            $options = ['small', 'medium', 'large'];

            $factory->select('size', $options);

            $result = $factory->toArray();

            expect($result[0][0])->toBe('select')
                ->and($result[0][1])->toBe('size')
                ->and($result[0][2])->toBe($options);
        });

        it('creates select field with extra parameters', function () {
            $factory = SettingsFactory::make();
            $options = ['yes', 'no'];
            $extra = ['default' => 'yes'];

            $factory->select('confirmation', $options, $extra);

            $result = $factory->toArray();

            // ...$extra adds array as associative key-value pairs
            expect($result[0][2])->toBe($options)
                ->and($result[0]['default'])->toBe('yes');
        });

        it('creates select field without extra parameters', function () {
            $factory = SettingsFactory::make();
            $options = ['option1', 'option2'];

            $factory->select('simple', $options);

            $result = $factory->toArray();

            expect($result[0])->toHaveCount(3); // type, key, options
        });
    });

    describe('->text()', function () {
        it('creates text field', function () {
            $factory = SettingsFactory::make();

            $factory->text('username');

            $result = $factory->toArray();

            expect($result[0][0])->toBe('text')
                ->and($result[0][1])->toBe('username');
        });

        it('creates text field with extra parameters', function () {
            $factory = SettingsFactory::make();
            $extra = ['maxlength' => 255, 'placeholder' => 'Enter username'];

            $factory->text('username', $extra);

            $result = $factory->toArray();

            // ...$extra adds array as associative key-value pairs
            expect($result[0]['maxlength'])->toBe(255)
                ->and($result[0]['placeholder'])->toBe('Enter username');
        });

        it('creates text field without extra parameters', function () {
            $factory = SettingsFactory::make();

            $factory->text('simple');

            $result = $factory->toArray();

            expect($result[0])->toHaveCount(2); // type, key only
        });

        it('creates text field with empty extra array', function () {
            $factory = SettingsFactory::make();

            $factory->text('empty');

            $result = $factory->toArray();

            expect($result[0])->toHaveCount(2); // type, key only
        });
    });

    describe('->check()', function () {
        it('creates check/checkbox field', function () {
            $factory = SettingsFactory::make();

            $factory->check('enabled');

            $result = $factory->toArray();

            expect($result[0][0])->toBe('check')
                ->and($result[0][1])->toBe('enabled');
        });

        it('creates check field with extra parameters', function () {
            $factory = SettingsFactory::make();
            $extra = ['default' => 1, 'disabled' => false];

            $factory->check('feature', $extra);

            $result = $factory->toArray();

            // ...$extra adds array as associative key-value pairs
            expect($result[0]['default'])->toBe(1)
                ->and($result[0]['disabled'])->toBeFalse();
        });

        it('creates check field without extra parameters', function () {
            $factory = SettingsFactory::make();

            $factory->check('simple');

            $result = $factory->toArray();

            expect($result[0])->toHaveCount(2); // type, key only
        });
    });

    describe('->color()', function () {
        it('creates color field', function () {
            $factory = SettingsFactory::make();

            $factory->color('theme_color');

            $result = $factory->toArray();

            expect($result[0][0])->toBe('color')
                ->and($result[0][1])->toBe('theme_color');
        });

        it('creates color field without extra parameters', function () {
            $factory = SettingsFactory::make();

            $factory->color('bg_color');

            $result = $factory->toArray();

            expect($result[0])->toHaveCount(2); // type, key only
        });
    });

    describe('->int()', function () {
        it('creates integer/number field', function () {
            $factory = SettingsFactory::make();

            $factory->int('age');

            $result = $factory->toArray();

            expect($result[0][0])->toBe('int')
                ->and($result[0][1])->toBe('age');
        });

        it('creates int field with extra parameters', function () {
            $factory = SettingsFactory::make();
            $extra = ['min' => 0, 'max' => 100, 'step' => 1];

            $factory->int('percentage', $extra);

            $result = $factory->toArray();

            // ...$extra adds array as associative key-value pairs
            expect($result[0]['min'])->toBe(0)
                ->and($result[0]['max'])->toBe(100)
                ->and($result[0]['step'])->toBe(1);
        });

        it('creates int field without extra parameters', function () {
            $factory = SettingsFactory::make();

            $factory->int('count');

            $result = $factory->toArray();

            expect($result[0])->toHaveCount(2); // type, key only
        });
    });

    describe('->float()', function () {
        it('creates float field', function () {
            $factory = SettingsFactory::make();

            $factory->float('price');

            $result = $factory->toArray();

            expect($result[0][0])->toBe('float')
                ->and($result[0][1])->toBe('price');
        });

        it('creates float field without extra parameters', function () {
            $factory = SettingsFactory::make();

            $factory->float('rating');

            $result = $factory->toArray();

            expect($result[0])->toHaveCount(2); // type, key only
        });
    });

    describe('->url()', function () {
        it('creates url field', function () {
            $factory = SettingsFactory::make();

            $factory->url('website');

            $result = $factory->toArray();

            expect($result[0][0])->toBe('url')
                ->and($result[0][1])->toBe('website');
        });

        it('creates url field without extra parameters', function () {
            $factory = SettingsFactory::make();

            $factory->url('homepage');

            $result = $factory->toArray();

            expect($result[0])->toHaveCount(2); // type, key only
        });
    });

    describe('->range()', function () {
        it('creates range field', function () {
            $factory = SettingsFactory::make();

            $factory->range('volume');

            $result = $factory->toArray();

            expect($result[0][0])->toBe('range')
                ->and($result[0][1])->toBe('volume');
        });

        it('creates range field with extra parameters', function () {
            $factory = SettingsFactory::make();
            $extra = ['min' => 0, 'max' => 100, 'step' => 5];

            $factory->range('scale', $extra);

            $result = $factory->toArray();

            // ...$extra adds array as associative key-value pairs
            expect($result[0]['min'])->toBe(0)
                ->and($result[0]['max'])->toBe(100)
                ->and($result[0]['step'])->toBe(5);
        });

        it('creates range field without extra parameters', function () {
            $factory = SettingsFactory::make();

            $factory->range('slider');

            $result = $factory->toArray();

            expect($result[0])->toHaveCount(2); // type, key only
        });
    });

    describe('->desc()', function () {
        it('creates description field', function () {
            $factory = SettingsFactory::make();

            $factory->desc('settings_description');

            $result = $factory->toArray();

            expect($result[0][0])->toBe('desc')
                ->and($result[0][1])->toBe('settings_description');
        });

        it('creates desc field without extra parameters', function () {
            $factory = SettingsFactory::make();

            $factory->desc('help_text');

            $result = $factory->toArray();

            expect($result[0])->toHaveCount(2); // type, key only
        });
    });

    describe('->title()', function () {
        it('creates title field', function () {
            $factory = SettingsFactory::make();

            $factory->title('section_title');

            $result = $factory->toArray();

            expect($result[0][0])->toBe('title')
                ->and($result[0][1])->toBe('section_title');
        });

        it('creates title field without extra parameters', function () {
            $factory = SettingsFactory::make();

            $factory->title('plugin_settings');

            $result = $factory->toArray();

            expect($result[0])->toHaveCount(2); // type, key only
        });
    });

    describe('->custom()', function () {
        it('creates custom/callback field', function () {
            $factory = SettingsFactory::make();

            $factory->custom('custom_field', '<div>Custom HTML</div>');

            $result = $factory->toArray();

            expect($result[0][0])->toBe('callback')
                ->and($result[0][1])->toBe('custom_field')
                ->and($result[0][2])->toBe('<div>Custom HTML</div>');
        });

        it('creates custom field with complex HTML', function () {
            $factory = SettingsFactory::make();
            $html = '<input type="text" name="custom" placeholder="Enter value">';

            $factory->custom('complex', $html);

            $result = $factory->toArray();

            expect($result[0][2])->toBe($html);
        });
    });

    describe('Complex Configuration Tests', function () {
        it('creates complete plugin configuration', function () {
            $factory = SettingsFactory::make();

            $settings = $factory
                ->title('Plugin Settings')
                ->text('plugin_name', ['maxlength' => 50])
                ->text('plugin_description', ['maxlength' => 255])
                ->check('enabled', ['default' => 1])
                ->select('theme', ['light', 'dark', 'auto'], ['default' => 'auto'])
                ->color('primary_color')
                ->int('items_per_page', ['min' => 1, 'max' => 100, 'default' => 10])
                ->range('animation_speed', ['min' => 0, 'max' => 2000, 'step' => 100])
                ->url('homepage_url')
                ->multiselect('features', ['feature1', 'feature2', 'feature3'])
                ->desc('settings_help');

            $result = $settings->toArray();

            expect($result)->toHaveCount(11);

            // Verify specific field types and keys
            $keys = array_column(array_map(fn($setting) => [$setting[0], $setting[1]], $result), 1);
            expect($keys)->toContain('plugin_name')
                ->and($keys)->toContain('enabled')
                ->and($keys)->toContain('theme')
                ->and($keys)->toContain('primary_color');
        });

        it('handles nested factory calls', function () {
            $factory1 = SettingsFactory::make()->text('field1');
            $factory2 = SettingsFactory::make()->check('field2');

            expect($factory1->toArray())->toHaveCount(1)
                ->and($factory2->toArray())->toHaveCount(1)
                ->and($factory1->toArray()[0][1])->toBe('field1')
                ->and($factory2->toArray()[0][1])->toBe('field2');
        });

        it('maintains order of added settings', function () {
            $factory = SettingsFactory::make();

            $factory
                ->title('Section 1')
                ->text('field1')
                ->title('Section 2')
                ->check('field2')
                ->title('Section 3')
                ->select('field3', ['a', 'b']);

            $result = $factory->toArray();

            expect($result[0][1])->toBe('Section 1')
                ->and($result[1][1])->toBe('field1')
                ->and($result[2][1])->toBe('Section 2')
                ->and($result[3][1])->toBe('field2')
                ->and($result[4][1])->toBe('Section 3')
                ->and($result[5][1])->toBe('field3');
        });
    });

    describe('Edge Cases and Validation', function () {
        it('handles duplicate keys correctly', function () {
            $factory = SettingsFactory::make();

            $factory->text('same_key');
            $factory->text('same_key');

            $result = $factory->toArray();

            expect($result)->toHaveCount(2)
                ->and($result[0][1])->toBe('same_key')
                ->and($result[1][1])->toBe('same_key');
        });

        it('handles empty key strings', function () {
            $factory = SettingsFactory::make();

            $factory->text('');

            $result = $factory->toArray();

            expect($result[0][1])->toBe('');
        });

        it('handles numeric string keys', function () {
            $factory = SettingsFactory::make();

            $factory->text('123');

            $result = $factory->toArray();

            expect($result[0][1])->toBe('123');
        });

        it('handles special characters in keys', function () {
            $factory = SettingsFactory::make();

            $factory->text('field_with_underscores');
            $factory->check('field-with-dashes');
            $factory->select('field.with.dots', ['a', 'b']);

            $result = $factory->toArray();

            expect($result[0][1])->toBe('field_with_underscores')
                ->and($result[1][1])->toBe('field-with-dashes')
                ->and($result[2][1])->toBe('field.with.dots');
        });

        it('handles complex extra parameter arrays', function () {
            $factory = SettingsFactory::make();

            $complexExtra = [
                'class' => 'form-control',
                'data-theme' => 'dark',
                'attributes' => ['readonly' => true, 'disabled' => false],
                'validation' => ['required' => true, 'minLength' => 3]
            ];

            $factory->text('complex', $complexExtra);

            $result = $factory->toArray();

            // ...$complexExtra adds array as associative key-value pairs
            expect($result[0]['class'])->toBe('form-control')
                ->and($result[0]['data-theme'])->toBe('dark')
                ->and($result[0]['attributes'])->toBe(['readonly' => true, 'disabled' => false])
                ->and($result[0]['validation'])->toBe(['required' => true, 'minLength' => 3]);
        });
    });

    describe('Memory and State Management', function () {
        it('maintains independent state for multiple instances', function () {
            $factory1 = SettingsFactory::make()->text('field1');
            $factory2 = SettingsFactory::make()->check('field2');

            expect($factory1->toArray())->not->toBe($factory2->toArray())
                ->and($factory1->toArray())->toHaveCount(1)
                ->and($factory2->toArray())->toHaveCount(1);
        });

        it('allows modification after toArray() call', function () {
            $factory = SettingsFactory::make();
            $factory->text('field1');

            $firstArray = $factory->toArray();
            expect($firstArray)->toHaveCount(1);

            $factory->text('field2');
            $secondArray = $factory->toArray();
            expect($secondArray)->toHaveCount(2);
        });
    });

    describe('Integration with Different Field Types', function () {
        it('creates configuration with all available field types', function () {
            $factory = SettingsFactory::make();

            $settings = $factory
                ->text('text_field')
                ->check('checkbox_field')
                ->select('select_field', ['opt1', 'opt2'])
                ->multiselect('multiselect_field', ['opt1', 'opt2'])
                ->int('int_field')
                ->float('float_field')
                ->url('url_field')
                ->range('range_field')
                ->color('color_field')
                ->desc('description_field')
                ->title('title_field')
                ->custom('custom_field', 'custom_html');

            $result = $settings->toArray();

            expect($result)->toHaveCount(12);

            $types = array_column($result, 0);
            expect($types)->toContain('text')
                ->and($types)->toContain('check')
                ->and($types)->toContain('select')
                ->and($types)->toContain('multiselect')
                ->and($types)->toContain('int')
                ->and($types)->toContain('float')
                ->and($types)->toContain('url')
                ->and($types)->toContain('range')
                ->and($types)->toContain('color')
                ->and($types)->toContain('desc')
                ->and($types)->toContain('title')
                ->and($types)->toContain('callback');
        });
    });
});
