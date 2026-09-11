// Scatters copies of medias/404.svg at random spots in the viewport, fading
// each one in, while staying clear of the navbar, the "This page could not
// be found" subtitle, the "Go to main menu" button, and any other scattered
// image already on screen — the giant ERROR404 numeral is fair game, stickers
// can land on or near it. Loaded only from includes/views/404.php — this
// effect exists on no other page. Stops for good — no more spawns even
// later — once maxTotalSpawns is reached, or the first time a spawn can't
// find a free spot within maxPlacementAttempts tries (since nothing ever
// disappears, a screen that's full now stays full).

const container = document.querySelector('.error-404__scatter');

if (container) {
  const imageSrc = container.dataset.imageSrc; // set from PHP — see 404.php
  // 90×64 native size, ×1.1 (+10%) then ×0.8 (-20%) = 79.2 wide. Height keeps
  // the source's 90:64 ratio (79.2 × 64/90 = 56.32) rather than shrinking
  // independently, which would distort it.
  const imageWidth = 79.2;
  const imageHeight = 56.32;
  const spawnDelayRange = [400, 900]; // ms, randomized between spawns
  const maxTotalSpawns = 50;
  // Bumped from the original 10/16 to actually get closer to maxTotalSpawns
  // before giving up: a blind random guess gets less and less likely to land
  // in the shrinking free space as the screen fills up, so it needs more
  // tries — and a smaller margin leaves more of that space to find.
  const maxPlacementAttempts = 45;
  const maxRotationDeg = 40; // each image gets a random angle in [-40, 40]

  const active = []; // { rect } for every scattered image currently shown
  let stopped = false; // set once, on either stopping condition above

  // Real, current position of everything already on the page — read live
  // (not cached) so a resize or content reflow is always respected.
  function getExclusionRects() {
    const guarded = [
      document.querySelector('.navbar'),
      document.querySelector('.error-404__subtitle'),
      document.querySelector('.error-404 .button'),
    ].filter(Boolean);
    return guarded.map((el) => el.getBoundingClientRect());
  }

  function overlaps(a, b) {
    return !(
      a.right < b.left || a.left > b.right ||
      a.bottom < b.top || a.top > b.bottom
    );
  }

  function withMargin(rect, margin) {
    return {
      left: rect.left - margin,
      top: rect.top - margin,
      right: rect.right + margin,
      bottom: rect.bottom + margin,
    };
  }

  // JS equivalent of the CSS clamp()s the rest of the site interpolates
  // between the 320px and 1920px ends of its responsive range (see
  // --navbar-clearance, style.css) — a fixed px margin would feel
  // proportionally huge on a phone and negligible on a big desktop screen.
  // Read fresh on every call (window.innerWidth changes on resize).
  function responsiveMargin(minPx, maxPx) {
    const t = Math.min(1, Math.max(0, (window.innerWidth - 320) / (1920 - 320)));
    return minPx + (maxPx - minPx) * t;
  }

  // A rotated rectangle's axis-aligned bounding box is bigger than the
  // rectangle itself (unless the angle is a multiple of 90°) — e.g. 99×70.4
  // at 35° renders as roughly 121×115. Collisions have to be checked against
  // this real, rotated footprint, not the plain image size, or a sticker can
  // end up visually overlapping something it was "placed" clear of.
  function rotatedBoundingBox(width, height, angleDeg) {
    const rad = (Math.abs(angleDeg) * Math.PI) / 180;
    return {
      width: width * Math.cos(rad) + height * Math.sin(rad),
      height: width * Math.sin(rad) + height * Math.cos(rad),
    };
  }

  // Tries random spots until one avoids every exclusion zone, or gives up.
  // footprintWidth/Height is the rotated bounding box for this attempt (see
  // rotatedBoundingBox), not the plain image size.
  function findFreeSpot(footprintWidth, footprintHeight) {
    const exclusions = getExclusionRects();
    const maxX = window.innerWidth - footprintWidth;
    const maxY = window.innerHeight - footprintHeight;

    // Two margins, not one: stickers can pack tightly against each other
    // (cosmetic only, --space-1 to --space-2), but the navbar/subtitle/button
    // are functional — twice as much breathing room (--space-2 to --space-4)
    // so a sticker never reads as touching them, even at a glance. Both
    // scale with the viewport, same as everything else on this page.
    const stickerMargin = responsiveMargin(8, 16);
    const guardedMargin = responsiveMargin(16, 32);

    for (let i = 0; i < maxPlacementAttempts; i++) {
      const x = Math.random() * maxX;
      const y = Math.random() * maxY;
      const rawCandidate = {
        left: x, top: y, right: x + footprintWidth, bottom: y + footprintHeight,
      };

      const blocked = exclusions.some((r) => overlaps(withMargin(rawCandidate, guardedMargin), r))
        || active.some((entry) => overlaps(withMargin(rawCandidate, stickerMargin), entry.rect));

      if (!blocked) return { left: x, top: y };
    }
    return null; // no free spot this cycle — just skip it, no error
  }

  function spawnOne() {
    if (active.length >= maxTotalSpawns) {
      stopped = true;
      return;
    }

    // Rotation is picked before placement, not after: the spot search needs
    // to know the real (rotated) footprint to check collisions against.
    const rotation = -maxRotationDeg + Math.random() * (maxRotationDeg * 2);
    const footprint = rotatedBoundingBox(imageWidth, imageHeight, rotation);

    const spot = findFreeSpot(footprint.width, footprint.height);
    if (!spot) {
      stopped = true; // no room found — the screen won't get any emptier
      return;
    }

    const img = document.createElement('img');
    img.src = imageSrc;
    img.alt = '';
    img.className = 'error-404__scatter-item';
    // spot is the rotated bounding box's top-left corner. Rotation pivots
    // around the element's own center by default, and rotating anything
    // around its own center leaves that center where it is — so centering
    // the plain (unrotated) image inside that box puts its rotated footprint
    // exactly on the box that was just checked for collisions.
    img.style.left = `${spot.left + (footprint.width - imageWidth) / 2}px`;
    img.style.top = `${spot.top + (footprint.height - imageHeight) / 2}px`;
    img.style.transform = `rotate(${rotation}deg)`;

    container.appendChild(img);
    active.push({ rect: img.getBoundingClientRect() });

    // Separate frame so the browser registers the CSS opacity: 0 first —
    // otherwise adding the class immediately skips straight to 1, no
    // transition played at all.
    requestAnimationFrame(() => {
      img.classList.add('active');
    });
  }

  function scheduleNext() {
    if (stopped) return;
    const [min, max] = spawnDelayRange;
    const delay = min + Math.random() * (max - min);
    setTimeout(() => {
      spawnOne();
      scheduleNext();
    }, delay);
  }

  scheduleNext();
}
