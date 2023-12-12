---
description: Οδηγίες για τη δημιουργία δικής σας εμφάνισης της πύλης
---

# Δημιουργήστε τη δική σας διάταξη πρώτης σελίδας

:::info

Από την έκδοση 2.2.0 χρησιμοποιούμε το [Latte](https://latte.nette.org/syntax) για την απόδοση των διατάξεων πρώτης σελίδας.

:::

Εκτός από τις υπάρχουσες διατάξεις, μπορείτε πάντα να προσθέσετε τις δικές σας.

Για να το κάνετε αυτό, δημιουργήστε ένα αρχείο «custom.latte» στον κατάλογο «/Themes/default/portal\_layouts»:

```php:line-numbers {9}
{varType array $txt}
{varType array $context}
{varType array $modSettings}

{if empty($context[lp_active_blocks])}
<div class="col-xs">
{/if}

    <div class="lp_frontpage_articles article_custom">
        {do show_pagination()}

            <div
                n:foreach="$context[lp_frontpage_articles] as $article"
                class="col-xs-12 col-sm-6 col-md-4 col-lg-{$context[lp_frontpage_num_columns]}"
            >
                <div n:if="!empty($article[image])">
                    <img src="{$article[image]}" alt="{$article[title]}">
                </div>
                <h3>
                    <a href="{$article[msg_link]}">{$article[title]}</a>
                </h3>
                <p n:if="!empty($article[teaser])">
                    {teaser($article[teaser])}
                </p>
            </div>

        {do show_pagination(bottom)}
    </div>

{if empty($context[lp_active_blocks])}
</div>
{/if}
```

Μετά από αυτό, θα δείτε μια νέα διάταξη πρώτης σελίδας - "Προσαρμοσμένη" - στις ρυθμίσεις της πύλης:

![Select custom template](set_custom_template.png)

Μπορείτε να δημιουργήσετε όσες τέτοιες διατάξεις θέλετε. Χρησιμοποιήστε το «debug.latte» και άλλες διατάξεις στον κατάλογο «/Themes/default/LightPortal/layouts» ως παραδείγματα.

Για να προσαρμόσετε τα φύλλα στυλ, δημιουργήστε ένα αρχείο «portal\_custom.css» στον κατάλογο «/Themes/default/css»:

```css {3}
/* Custom layout */
.article_custom {
  /* Your rules */
}
```

:::tip

Εάν έχετε δημιουργήσει το δικό σας πρότυπο αρχικής σελίδας και θέλετε να το μοιραστείτε με τον προγραμματιστή και άλλους χρήστες, χρησιμοποιήστε τη διεύθυνση https\://codepen.io/pen/ ή άλλους παρόμοιους πόρους.

:::
