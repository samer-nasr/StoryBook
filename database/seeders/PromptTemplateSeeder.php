<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PromptTemplate;

class PromptTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('constant.prompts');

        if (!$config) {
            $this->command->warn('No prompt configuration found in constant.php.');
            return;
        }

        // Global Record
        PromptTemplate::updateOrCreate(
            ['key' => 'global'],
            [
                'name' => 'Global Defaults',
                'system_role' => $config['system_role'] ?? '',
                'strict_rules' => $config['strict_rules'] ?? '',
                'output_rules' => $config['output_rules'] ?? '',
                'identity_block' => $config['defaults']['identity_block'] ?? '',
                'style_block' => $config['defaults']['style_block'] ?? '',
                'version' => 1,
                'is_active' => true,
            ]
        );

        // Character Generation Record
        PromptTemplate::updateOrCreate(
            ['key' => 'character_generation'],
            [
                'name' => 'Character Generation',
                'task' => $config['character_generation']['task'] ?? '',
                'constraints' => $config['character_generation']['constraints'] ?? '',
                'version' => 1,
                'is_active' => true,
            ]
        );

        // Page Generation Record
        PromptTemplate::updateOrCreate(
            ['key' => 'page_generation'],
            [
                'name' => 'Page Generation',
                'task' => $config['page_generation']['task'] ?? '',
                'constraints' => $config['page_generation']['constraints'] ?? '',
                'version' => 1,
                'is_active' => true,
            ]
        );

        $this->command->info('Prompt templates seeded successfully.');
    }
}
