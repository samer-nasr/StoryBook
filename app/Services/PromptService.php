<?php

namespace App\Services;

use App\Models\PromptTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PromptService
{
    /**
     * Get all currently active prompt templates, cached.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getActivePrompts()
    {
        return Cache::remember('prompt_templates', 3600, function () {
            return PromptTemplate::where('is_active', true)->get()->keyBy('key');
        });
    }

    /**
     * Build the final structured prompt dynamically based on type and config.
     *
     * @param string $type        e.g., 'character_generation', 'page_generation'
     * @param array  $storyConfig The configuration saved inside the StoryGeneration record
     * @return string
     */
    public function getPrompt(string $type, array $storyConfig): string
    {
        $prompts = $this->getActivePrompts();

        $global = $prompts->get('global');
        $specific = $prompts->get($type);

        if (!$global) {
            Log::warning("PromptService: Missing active 'global' prompt template.");
        }

        if (!$specific) {
            Log::warning("PromptService: Missing active '{$type}' prompt template.");
        }

        // Merge logic based on the user requirement mapping
        $systemRole = $global?->system_role ?? '';
        $strictRules = $global?->strict_rules ?? '';
        
        // Priority: Use localized $storyConfig if present (preserves historical versions), fallback to global defaults
        $identityBlock = $storyConfig['identity_block'] ?? $global?->identity_block ?? '';
        $styleBlock = $storyConfig['style_block'] ?? $global?->style_block ?? '';
        
        $task = $specific?->task ?? '';
        $constraints = $specific?->constraints ?? '';
        $outputRules = $global?->output_rules ?? '';

        return <<<PROMPT
[SYSTEM ROLE]
{$systemRole}

[STRICT RULES]
{$strictRules}

[IDENTITY BLOCK]
{$identityBlock}

[STYLE BLOCK]
{$styleBlock}

[TASK]
{$task}

[CONSTRAINTS]
{$constraints}

[OUTPUT RULES]
{$outputRules}
PROMPT;
    }
}
