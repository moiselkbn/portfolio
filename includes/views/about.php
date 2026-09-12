<?php

declare(strict_types=1);

/**
 * About. Wrapped by includes/layout.php.
 * Content sourced from Notion "CV" page: the bio line, the availability
 * status, and the skill list (Compétences = the 4 "important" chips,
 * Logiciels = the rest) — see CLAUDE.md § Contenu éditorial for why this
 * lives here and not duplicated in the repo.
 */
$title = 'About — Moïse Lukebanu';

// Compétences (CV) — the 4 core languages, shown as the darker "important"
// chips. Logiciels (CV) — the tools, shown as the lighter default chips.
$importantSkills = ['JavaScript', 'PHP', 'WordPress', 'SQL'];
$otherSkills = [
    'Figma', 'Blender', 'Marvelous Designer', 'DaVinci Resolve',
    'After Effects', 'Photoshop', 'Illustrator', 'InDesign', 'GitHub',
];

// Skill name → icon filename in public/assets/icons/. Written out rather
// than derived from a slug() of the name because "After Effects" doesn't
// slug to "after-effect.svg" (singular) on its own — keeping the mapping
// explicit means a filename quirk like that one never has to become a
// naming-convention exception elsewhere in the code.
$skillIcons = [
    'JavaScript' => 'javascript.svg',
    'PHP' => 'php.svg',
    'WordPress' => 'wordpress.svg',
    'SQL' => 'sql.svg',
    'Figma' => 'figma.svg',
    'Blender' => 'blender.svg',
    'Marvelous Designer' => 'marvelous-designer.svg',
    'DaVinci Resolve' => 'davinci-resolve.svg',
    'After Effects' => 'after-effect.svg',
    'Photoshop' => 'photoshop.svg',
    'Illustrator' => 'illustrator.svg',
    'InDesign' => 'indesign.svg',
    'GitHub' => 'github.svg',
];
?>

<div class="about">
  <h1 class="about__lead">I&rsquo;m a web developer based in Brussels, Belgium, with a background in design, 3D, and UI/UX. Whatever the field, I&rsquo;ve always learned by building and experimenting.</h1>

  <p class="about__availability">
    Student at Haute École Francisco Ferrer, Brussels.<br>
    Available for an internship from January to June 2027.
  </p>

  <ul class="about__chips">
    <?php foreach ($importantSkills as $skill): ?>
      <li class="chip chip--important">
        <img class="chip__icon" src="<?= e(url('assets/icons/' . $skillIcons[$skill])) ?>"
             alt="" width="16" height="16">
        <?= e($skill) ?>
      </li>
    <?php endforeach; ?>
    <?php foreach ($otherSkills as $skill): ?>
      <li class="chip">
        <img class="chip__icon" src="<?= e(url('assets/icons/' . $skillIcons[$skill])) ?>"
             alt="" width="16" height="16">
        <?= e($skill) ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <a class="about__resume button" href="<?= e(url('cv/moise-lukebanu-cv.pdf')) ?>" download>
    <span class="button__content">
      <img class="button__icon" src="<?= e(url('assets/icons/arrow-external.svg')) ?>"
           alt="" width="24" height="24" style="transform: rotate(90deg);">
      <span>Download the resume</span>
    </span>
  </a>
</div>
