# Style Guide

## PHP

* Use PHP **8.2+**.
* Use **tabs** for indentation instead of spaces.

### Naming conventions

| Item              | Convention             |
| ----------------- | ---------------------- |
| Variables         | `$camelCase`           |
| Functions         | `snake_case()`         |
| Classes           | `PascalCase`           |
| Methods           | `$this->camelCase()`   |
| Array keys        | `$array['snake_case']` |
| Object properties | `$object->camelCase`   |
| Constants         | `CONSTANT_NAME`        |

Example:

```php
/**
 * Get array with bubble sorting
 *
 * @param array $array
 * @return array
 */
function getBubbleSortedArray(array $array): array
{
    $count = count($array);

    for ($j = 0; $j < $count - 1; $j++) {
        for ($i = 0; $i < $count - $j - 1; $i++) {
            if ($array[$i] > $array[$i + 1]) {
                $tmp_var = $array[$i + 1];
                $array[$i + 1] = $array[$i];
                $array[$i] = $tmp_var;
            }
        }
    }

    return $array;
}
```

---

## HTML

Use HTML5.

---

## CSS

Use SASS for styling.

See:

```
resources/sass/portal.scss
```

Example:

```scss
#comment_form {
    textarea {
        width: 100%;
        height: 30px;
    }

    button {
        &[name='comment'] {
            margin-top: 10px;
            float: right;
            display: none;
        }
    }
}
```

---

## JavaScript

Preferred technologies:

* Native JavaScript
* Alpine.js 3.x
* htmx 2.x
* Svelte 5.x

Always use `const` or `let` instead of `var`.

Example:

```js
'use strict';

Array.prototype.bubbleSort = function () {
    let swapped;

    do {
        swapped = false;

        this.forEach((item, index) => {
            if (item > this[index + 1]) {
                let temp = item;

                this[index] = this[index + 1];
                this[index + 1] = temp;
                swapped = true;
            }
        });
    } while (swapped);

    return this;
};
```
