## How to contribute
* Fork the repository. If you are not used to Github, please check out [fork a repository](https://help.github.com/fork-a-repo).
* Branch your repository, to commit the desired changes.
* Test your code.
* Send a pull request to us.

### Recommended soft
* [Visual Studio Code](https://code.visualstudio.com) (all OS)
* [SmartGit](https://www.syntevo.com/smartgit/download/) (all OS), or [Git Extensions](https://github.com/gitextensions/gitextensions/releases) (Windows), or [GitHub Desktop](https://desktop.github.com) (macOS, Windows)

## How to submit an issue
* Use bug report or feature request templates.

## How to submit a pull request
* If you want to send a bug fix, use `Fix` word in the title of your PR (i.e. "Fix page permissions").
* If you want to send a new feature or a new translation, use `Add` word in the title of your PR (i.e `Add new frontpage template`, `Add Chinese translation`).
In any case, the title of each of your commits should continue such a phrase — `If applied, this commit will  ...` (`Update Polish`, etc.)

## Styleguides with examples

### PHP Styleguide
* Use PHP 7.2+
* Use [DocBlock](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/#docblock-formatting) comments for your functions

```php
/**
 * Get array with bubble sorting
 *
 * @param array $array
 * @return array
 */
function getBubbleSortedArray($array)
{
    $count = count($array);
    for ($j = 0; $j < $count - 1; $j++) {
        for ($i = 0; $i < $count - $j - 1; $i++) {
            if ($array[$i] > $array[$i + 1]){
                $tmp_var = $array[$i + 1];
                $array[$i + 1] = $array[$i];
                $array[$i] = $tmp_var;
            }
        }
    }

    return $array;
}

$array = [5, 3, 2, 6, 1, 4, 7];
$result = getBubbleSortedArray($array);
var_dump($result);
```
```php
// Use this
$test = $variable ?: 'default_value';

// Instead of
$test = empty($variable) ? 'default_value' : $variable;

// Use this
$test = $variable ?? 'default_value';

// Instead of
$test = isset($variable) ? $variable : 'default_value';
```

### HTML Styleguide
* Use [HTML5](https://www.w3schools.com/html/html5_syntax.asp)

### CSS Styleguide
* You can use CSS or LESS

```less
#comment_form {
    textarea {
        width: 100%;
        height: 30px;
    }

    button {
        &[name="comment"] {
            margin-top: 10px;
            float: right;
            display: none;
        }
    }
}
```

### JavaScript Styleguide
* Use native JavaScript instead of jQuery.
* Use [strict mode](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Strict_mode) in your scripts or functions.
* Use [`const`](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/const) or [`let`](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/let) instead of `var`.

```js
"use strict";

Array.prototype.bubbleSort = function() {
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
        })
    } while (swapped);

    return this;
}

const arr = [5, 3, 2, 6, 1, 4, 7];
console.log('Source array: ', arr);
// Source array:  (7) [5, 3, 2, 6, 1, 4, 7]

console.log('Sorted array: ', arr.bubbleSort())
// Sorted array:  (7) [1, 2, 3, 4, 5, 6, 7]
```

### Do. Or do not. There is no try
Anyway, you can use [SMF Coding Guidelines](https://wiki.simplemachines.org/smf/Coding_Guidelines) and that will be enough.
