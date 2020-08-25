## How to contribute
* Fork the repository. If you are not used to Github, please check out [fork a repository](https://help.github.com/fork-a-repo).
* Branch your repository, to commit the desired changes.
* Test your code.
* Send a pull request to us.

### Recommended applications
* [Visual Studio Code](https://code.visualstudio.com)
* [GitKraken](https://www.gitkraken.com), or [Git Extensions](https://github.com/gitextensions/gitextensions/releases) (Windows), or [GitHub Desktop](https://desktop.github.com) (macOS, Windows)

## How to submit an issue
* Use bug report or feature request templates.

## How to submit a pull request
* If you want to send a bug fix, use "Fix" word in the title of your PR (i.e. "Fix page permissions").
* If you want to send a new feature or a new translation, use "Add" word in the title of your PR (i.e "Add new frontpage template").
In any case, the title of each of your commits should continue such a phrase — "If applied, this commit will  ..."

## Styleguides with examples

### PHP Styleguide
* Use PHP 7.2+

```php
function getValue($variable)
{
  if (!empty($variable)) {
    return $variable;
  }
  
  return false;
}

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
* Use HTML5

```html
<ul class="your_class">
  <li>1</li>
  <li>2</li>
  <li>3</li>
</ul>
```

### CSS Styleguide
* You can use CSS or LESS

```css
.classname {
  // rule
}
```

### JavaScript Styleguide
* Use native JavaScript instead of jQuery.
* Use [strict mode](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Strict_mode) in your scripts or functions.
* Use [`const`](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/const) or [`let`](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/let) instead of `var`.

```js
function testFunc(n) {
  for (let i = 0; i < n; i++) {
    console.log(`${i} < ${n}`, i);
  }
}

let x = testFunc(10);
```

Anyway, you can use [SMF Coding Guidelines](https://wiki.simplemachines.org/smf/Coding_Guidelines) and that will be enough.
