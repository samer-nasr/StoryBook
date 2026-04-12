<?php

namespace App\Constants;

class Prompts
{
    /**
     * Full static prompt sent to OpenAI for every 4-panel grid edit.
     * Used directly by AIImageService::replaceCharacterInGrid().
     *
     * DO NOT interpolate dynamic content here — this is used as-is.
     */
    public const GRID_FACE_REPLACEMENT = <<<'PROMPT'
SYSTEM INSTRUCTION — GRID FACE REPLACEMENT (STRICT MODE)

You are editing a 1024x1024 image that contains EXACTLY 4 independent panels arranged in a 2x2 grid:

* Top-left panel     (x: 0–512,    y: 150–512)
* Top-right panel    (x: 512–1024, y: 150–512)
* Bottom-left panel  (x: 0–512,    y: 512–874)
* Bottom-right panel (x: 512–1024, y: 512–874)

NOTE: The regions (y: 0–150) and (y: 874–1024) are white padding. DO NOT edit these areas.

---

## HARD RULES (DO NOT VIOLATE)

* DO NOT change layout, panel size, or positions
* DO NOT resize, crop, or distort the image
* DO NOT modify or regenerate ANY text
* DO NOT change fonts, colors, or text placement
* DO NOT modify backgrounds, environments, or lighting
* DO NOT add or remove any objects
* DO NOT blend or mix panels together
* DO NOT alter image style or color palette

---

## TASK (ONLY THIS ACTION IS ALLOWED)

In EACH of the 4 panels:

* Locate the child character
* Replace ONLY the FACE (eyes, nose, mouth, facial structure)

IMPORTANT:

* Keep the ORIGINAL:
  * hair
  * head shape
  * body
  * pose
  * clothes
  * position

* DO NOT modify hair or hairstyle
* DO NOT modify head shape
* DO NOT move or scale the character
* Blend the new face naturally into the existing head

---

## CHARACTER CONSISTENCY

* The new face must match the reference character exactly:
  * same identity
  * same facial features
  * same skin tone

* The SAME character must appear in all 4 panels
* Adapt ONLY the orientation:
  * match left / right / front view of each panel

---

## CRITICAL CLARIFICATION

* The reference image defines ONLY the FACE identity
* The grid image defines:
  * layout
  * text
  * hair
  * pose
  * composition

NEVER override the original layout or text

---

## OUTPUT REQUIREMENT

* The output must be IDENTICAL to the input image
* EXCEPT for the face replacement in each panel
* Text must remain pixel-perfect unchanged
* Panel structure must remain EXACT
* No other visual changes are allowed

---

## BACKGROUND AND ENVIRONMENT ARE LOCKED

* Background must remain EXACTLY identical at pixel level
* Do NOT modify lighting, colors, glow, atmosphere, or gradients
* Do NOT add effects, particles, or enhancements
* Do NOT apply color correction or recoloring
* ANY modification outside the face region is strictly forbidden

---

## PIXEL PRESERVATION RULE

* Treat the input image as a fixed template
* All non-face pixels MUST remain EXACTLY unchanged
* Preserve original pixels wherever possible
* Do NOT redraw or reinterpret the image

---

## TEXT IMMUTABILITY RULE

* Text is a locked visual element
* Text must remain EXACTLY identical at pixel level
* Do NOT redraw, re-render, sharpen, blur, or regenerate text
* Do NOT change spacing, kerning, or alignment

---

## PANEL BOUNDARY PROTECTION

* Panel borders are fixed and untouchable
* Do NOT modify or blend across panel edges
* Do NOT introduce artifacts at panel seams

---

## FACE REPLACEMENT BOUNDARY

* Replace ONLY the internal facial area
* Do NOT modify:
  * hairline
  * outer head contour
  * ears
  * neck
* Face replacement must stay strictly inside facial region

---

## NO REINTERPRETATION RULE

* Do NOT reinterpret or redraw the scene
* Do NOT enhance, stylize, or "improve" the image
* This is a surgical replacement, NOT generation

---

## CONSISTENCY ENFORCEMENT

* The same face identity must appear in all panels
* No variation in:
  * eye shape
  * facial proportions
  * skin tone
* Only orientation changes are allowed

## FINAL CONTROL RULES (ADDITIONS)

* DO NOT re-render or regenerate entire panels

* Only modify pixels strictly inside the face region

* PROCESS EACH PANEL INDEPENDENTLY:
  Complete one panel fully before moving to the next
  Do NOT process all panels simultaneously

* DO NOT enhance, sharpen, upscale, or apply any filters

* The 1024x1024 grid and all 512x512 panel boundaries must remain mathematically exact

* If a face is not clearly visible in a panel, DO NOT modify that panel
PROMPT;
}
