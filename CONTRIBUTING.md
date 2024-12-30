## How to contribute

- Fork the repository. If you are not used to GitHub, please check out [fork a repository](https://help.github.com/fork-a-repo).
- Branch your repository, to commit the desired changes.
- Test your code.
- Send a pull request to us.

### Recommended soft

- [Visual Studio Code](https://code.visualstudio.com) (all OS), or [PHPStorm](https://www.jetbrains.com/phpstorm/) (all OS)
- [SmartGit](https://www.syntevo.com/smartgit/download/) (all OS), or [Git Extensions](https://github.com/gitextensions/gitextensions/releases) (Windows), or [GitHub Desktop](https://desktop.github.com) (macOS, Windows)

## How to submit an issue

- Use bug report or feature request templates.

## How to submit a pull request

- Check if the develop branch exists. If it exists use it to pull your request into.
- If you want to send a bug fix, use `Fix` word in the title of your PR (i.e. `Fix page permissions`).
- If you want to send a new feature, use `Add` word in the title of your PR (i.e `Add a new frontpage template`).

In any case, the title of each of your commits should continue such a phrase — `If applied, this commit will  ...` (`Update HelloPortal addon`, etc.)

## Styleguide with examples

### PHP Styleguide

- Use [PHP 8.1+](https://smknstd.github.io/modern-php-cheatsheet/) with tabs instead of spaces

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

#### In short

- Variable names: `$camelCase = true`
- Function names: `snake_case()`
- Class names: `class PascalCase`
- Method names: `$this->camelCase()`
- Array key names: `$var['snake_case']`
- Object key names: `$obj->camelCase`
- Constant names: `CONSTANT_NAME`

### HTML Styleguide

- Use [HTML5](https://www.w3schools.com/html/html5_syntax.asp)

### CSS Styleguide

- Use SASS (see `resources/sass/portal.scss`) to modify desired rules.

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

### JavaScript Styleguide

- Use native JavaScript, [Alpine.js](https://github.com/alpinejs/alpine) (3.x), [htmx](https://htmx.org) (2.x), or [Svelte](https://svelte.dev/) (5.x).
- Use [`const`](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/const) or [`let`](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/let) instead of `var`.

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

const arr = [5, 3, 2, 6, 1, 4, 7];
console.log('Source array: ', arr);
// Source array:  (7) [5, 3, 2, 6, 1, 4, 7]

console.log('Sorted array: ', arr.bubbleSort());
// Sorted array:  (7) [1, 2, 3, 4, 5, 6, 7]
```

### Semantic Versioning

We try using [Major.Minor.Patch](https://medium.com/fiverr-engineering/major-minor-patch-a5298e2e1798) for releases.
