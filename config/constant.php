<?php

return [
    'prompts' => [
        'system_role' => "You are generating a children's storybook illustration.",
        
        'strict_rules' => "- The character MUST remain EXACTLY identical to the provided reference image
- The reference image is the ONLY source of truth for identity

- Facial identity consistency is the highest priority over style
- Apply Pixar style ONLY to rendering, NOT to facial structure

- Do NOT modify face structure, proportions, eyes, nose, or facial features
- Do NOT reinterpret, redesign, or stylize the face differently

- Eye shape, size, color, spacing, and proportions MUST remain EXACTLY identical to the reference image
- Do NOT modify eye size, iris color, pupil proportions, or eyelid structure
- Do NOT exaggerate or restyle the eyes in any way
- Eye expression must remain consistent and not be reinterpreted across pages
- Eye highlights and reflections must remain natural and consistent

- Hair style, hair structure, and hair shape MUST remain EXACTLY identical to the reference image
- Do NOT change hairstyle, hair length, or hair arrangement
- Do NOT tie, untie, restyle, or reinterpret the hair in any way
- Hair position, volume, and silhouette MUST remain consistent across all pages
- The silhouette of the head (including hair) must remain identical
- The exact position of all hair elements (including buns, clips, and tied sections) must remain identical
- Do NOT shift, resize, or reposition any part of the hairstyle

- Do NOT alter the face, eyes, or hair due to lighting, shadows, or environment conditions

- ZERO deviation from the reference is allowed",
        
        'output_rules' => "- Preserve any existing text from the template image exactly as it is
- Do NOT remove, modify, or distort any text elements
- Preserve any logos, watermarks, or brand elements exactly as they are
- Do NOT modify, blur, replace, or distort any logos
- Maintain original layout including text and logo placement
- Clean high-quality illustration",
        
        'defaults' => [
            'style_block' => "Pixar-style children's illustration,
soft cinematic lighting,
pastel color palette,
smooth shading,
high detail,
magical atmosphere,
consistent rendering across all pages",

            'identity_block' => "The provided character reference MUST be followed perfectly.
Maintain exact same character identity in ALL aspects.
Under no condition should the face, eyes, or hair change.

Eyes must match the reference exactly in:
- shape
- size
- color
- spacing
- iris and pupil proportions
- eyelid structure
- expression baseline

Hair must match the reference exactly in:
- shape
- volume
- curls/waves
- length
- structure
- position and arrangement (including tied sections like buns or ponytails)
- overall silhouette",
        ],

        'character_generation' => [
            'task' => "Recreate the character from the provided reference image in Pixar-style while preserving EXACT identity with no changes.",
            
            'constraints' => "- Neutral standing pose
- Centered in frame
- No background or environment
- Only the character visible
- Transparent background (PNG with alpha channel)",
        ],

        'page_generation' => [
            'task' => "Place the character into the provided template scene while preserving EXACT character identity.",
            
            'constraints' => "- The character reference image is the PRIMARY source of truth
- The template image is ONLY for pose, composition, and layout
- If conflict occurs, ALWAYS preserve character identity over template accuracy
- Match pose, composition, and layout of the template including text and logo areas
- Do NOT modify or overwrite any text regions
- Do NOT modify or overwrite any logos or brand elements
- Only integrate the character into the scene
- Keep character positioned LEFT or RIGHT (never center)
- Maintain consistent lighting and rendering style",
        ]
    ]
];