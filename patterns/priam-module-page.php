<?php
/**
 * Block Pattern: PRIAM module page (#15)
 *
 * The layout for the module pages under /platform/: hero with CTA, the
 * problem (3 bullets), how it works (3 steps, each with one screenshot),
 * an optional YouTube clip, who uses it, FAQ (3 questions) and a final CTA band.
 * Built from core blocks, so everything can be edited in the visual editor.
 * Styles: style.css section 30. Media rules: HOW-TO.md → "Module page media".
 *
 * Usage: block editor → + → Patterns → PowerData → PRIAM module page.
 * Replace every [bracketed] placeholder, swap the sample screenshots, and paste
 * the clip's YouTube link into the video block (or delete that section).
 */

register_block_pattern(
	'powerdata/priam-module-page',
	[
		'title'       => __( 'PRIAM module page', 'powerdata-theme' ),
		'description' => __( 'Full module page: hero, problem, three steps with screenshots, clip, roles, FAQ and CTA band.', 'powerdata-theme' ),
		'categories'  => [ 'powerdata' ],
		'keywords'    => [ 'priam', 'module', 'platform', 'product', 'page' ],
		'blockTypes'  => [ 'core/post-content' ],
		'postTypes'   => [ 'page' ],
		'content'     => <<<'PDPATTERN'
<!-- wp:group {"tagName":"section","className":"pd-mod-hero","layout":{"type":"default"}} -->
<section class="wp-block-group pd-mod-hero"><!-- wp:group {"className":"pd-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group pd-wrap"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">[Module name]</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"h-display"} -->
<h1 class="wp-block-heading h-display">[Headline: the outcome in a few words]</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"lede"} -->
<p class="lede">[One sentence: what this module does and the outcome for the buyer.]</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"pd-cta"} -->
<div class="wp-block-button pd-cta"><a class="wp-block-button__link wp-element-button" href="https://bookings.cloud.microsoft/book/PRIAMbyPowerDataSolutions@powerdatainc.com/" target="_blank" rel="noreferrer noopener">Book a 30-minute walkthrough →</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

</div>
<!-- /wp:group -->

</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"pd-mod-section","layout":{"type":"default"}} -->
<section class="wp-block-group pd-mod-section"><!-- wp:group {"className":"pd-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group pd-wrap"><!-- wp:heading {"className":"h-section"} -->
<h2 class="wp-block-heading h-section">The problem</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"pd-mod-problem"} -->
<ul class="wp-block-list pd-mod-problem"><!-- wp:list-item -->
<li>[Problem 1, in the buyer's words]</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>[Problem 2]</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>[Problem 3]</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

</div>
<!-- /wp:group -->

</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"pd-mod-section alt","layout":{"type":"default"}} -->
<section class="wp-block-group pd-mod-section alt"><!-- wp:group {"className":"pd-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group pd-wrap"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">How it works in PRIAM</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"h-section"} -->
<h2 class="wp-block-heading h-section">[How it works: a short headline]</h2>
<!-- /wp:heading -->

<!-- wp:columns {"className":"pd-mod-step"} -->
<div class="wp-block-columns pd-mod-step"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"pd-step-n"} -->
<p class="pd-step-n">Step 1</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">[Step 1 title]</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[What happens in this step, and why it matters.]</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"pd-shot"} -->
<figure class="wp-block-image size-full pd-shot"><img src="https://priamtiv.com/wp-content/uploads/2026/10/priam-tour-01-dashboard.webp" alt="[Describe what the screenshot shows]"/></figure>
<!-- /wp:image -->

</div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns {"className":"pd-mod-step is-reversed"} -->
<div class="wp-block-columns pd-mod-step is-reversed"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"pd-step-n"} -->
<p class="pd-step-n">Step 2</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">[Step 2 title]</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[What happens in this step, and why it matters.]</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"pd-shot"} -->
<figure class="wp-block-image size-full pd-shot"><img src="https://priamtiv.com/wp-content/uploads/2026/10/priam-tour-01-dashboard.webp" alt="[Describe what the screenshot shows]"/></figure>
<!-- /wp:image -->

</div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns {"className":"pd-mod-step"} -->
<div class="wp-block-columns pd-mod-step"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"pd-step-n"} -->
<p class="pd-step-n">Step 3</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">[Step 3 title]</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[What happens in this step, and why it matters.]</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"pd-shot"} -->
<figure class="wp-block-image size-full pd-shot"><img src="https://priamtiv.com/wp-content/uploads/2026/10/priam-tour-01-dashboard.webp" alt="[Describe what the screenshot shows]"/></figure>
<!-- /wp:image -->

</div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"pd-mod-section","layout":{"type":"default"}} -->
<section class="wp-block-group pd-mod-section"><!-- wp:group {"className":"pd-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group pd-wrap"><!-- wp:embed {"type":"video","providerNameSlug":"youtube","responsive":true,"className":"pd-video"} /-->

</div>
<!-- /wp:group -->

</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"pd-mod-section","layout":{"type":"default"}} -->
<section class="wp-block-group pd-mod-section"><!-- wp:group {"className":"pd-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group pd-wrap"><!-- wp:heading {"className":"h-section"} -->
<h2 class="wp-block-heading h-section">Who uses it</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"pd-mod-roles"} -->
<ul class="wp-block-list pd-mod-roles"><!-- wp:list-item -->
<li><strong>[Role]</strong>[What this role does in the module.]</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>[Role]</strong>[What this role does in the module.]</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>[Role]</strong>[What this role does in the module.]</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

</div>
<!-- /wp:group -->

</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"pd-mod-section alt","layout":{"type":"default"}} -->
<section class="wp-block-group pd-mod-section alt"><!-- wp:group {"className":"pd-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group pd-wrap"><!-- wp:heading {"className":"h-section"} -->
<h2 class="wp-block-heading h-section">Questions</h2>
<!-- /wp:heading -->

<!-- wp:details {"className":"pd-faq-item"} -->
<details class="wp-block-details pd-faq-item"><summary>[Question 1]</summary><!-- wp:paragraph -->
<p>[Answer 1]</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"className":"pd-faq-item"} -->
<details class="wp-block-details pd-faq-item"><summary>[Question 2]</summary><!-- wp:paragraph -->
<p>[Answer 2]</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"className":"pd-faq-item"} -->
<details class="wp-block-details pd-faq-item"><summary>[Question 3]</summary><!-- wp:paragraph -->
<p>[Answer 3]</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

</div>
<!-- /wp:group -->

</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"pd-mod-cta band-ink","layout":{"type":"default"}} -->
<section class="wp-block-group pd-mod-cta band-ink"><!-- wp:group {"className":"pd-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group pd-wrap"><!-- wp:heading {"className":"h-section"} -->
<h2 class="wp-block-heading h-section">[Closing headline]</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"lede"} -->
<p class="lede">[One line about the walkthrough.]</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"pd-cta"} -->
<div class="wp-block-button pd-cta"><a class="wp-block-button__link wp-element-button" href="https://bookings.cloud.microsoft/book/PRIAMbyPowerDataSolutions@powerdatainc.com/" target="_blank" rel="noreferrer noopener">Book a 30-minute walkthrough →</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

</div>
<!-- /wp:group -->

</section>
<!-- /wp:group -->
PDPATTERN,
	]
);
