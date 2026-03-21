# Personalized Storybook Project

## Overview
This feature allows users to upload a photo of a child, generate a storybook-style cartoon character based on that photo, and then seamlessly replace the main character inside a given PDF children's book (`Design sans titre.pdf`) with the new AI-generated character.

## AI Provider Used
The project utilizes the **Laravel AI Facade** (`Laravel\Ai\Ai`) for its AI integrations. Based on the `config/ai.php` configuration, it leverages **Gemini** as the default provider for image generation and image-to-image transformations (`'default_for_images' => 'gemini'`). It uses Gemini's multimodal capabilities by passing local images alongside text prompts to generate the character and replace it within the existing PDF artwork.

## Core Components
The core components of this feature are built into the `chatbot-test` Laravel application.

1. **`AIImageService`** (`app/Services/AIImageService.php`)
   - Handles communication with the AI image models to generate and edit images.
   - Takes the uploaded photo and generates the new cartoon character.
   - Performs image-to-image replacement of the character inside the original storybook pages.

2. **`StoryController`** (`app/Http/Controllers/StoryController.php`)
   - The primary entry point for the web application.
   - Validates the incoming photo upload and request data.
   - Temporarily stores the uploaded image.
   - Dispatches the `GeneratePersonalizedStory` background queue job.

3. **`StoryGenerationService`** (`app/Services/StoryGenerationService.php`)
   - Handles the file processing (extracting pages from the original PDF and rebuilding the final PDF).

4. **`GeneratePersonalizedStory` Job** (`app/Jobs/GeneratePersonalizedStory.php`)
   - An asynchronous queue job that runs the actual story generation flow, preventing long wait times on the frontend.

## Step-by-Step Processing Flow

When a user submits a photo, the following step-by-step process occurs in the background via the `GeneratePersonalizedStory` job:

1. **Upload & Dispatch:**
   - The user uploads a photo via the web form. The `StoryController` validates it, saves the photo temporarily, and dispatches the background job.
   
2. **Character Generation:**
   - The job calls `AIImageService::generateCharacterImage()`.
   - The AI uses the uploaded child's photo and a prompt to create a new, cute, storybook-style cartoon character that resembles the child.
   
3. **PDF Extraction:**
   - The `StoryGenerationService` extracts all the individual image pages from the original template PDF (`Design sans titre.pdf`).
   
4. **AI Page Processing (The Magic!):**
   - The system loops through every extracted page of the book.
   - For each page, it calls `AIImageService::replaceCharacterInPage()`.
   - It provides the AI with **both** the original page image and the newly generated child character image.
   - The AI edits the page, seamlessly swapping out the original baby character with the new personalized character while keeping the original art style, colors, and background intact.
   
5. **Rebuilding the PDF:**
   - Once all pages are individually edited by the AI, the `StoryGenerationService` compiles these new images back into a single, cohesive PDF file.
   - The final personalized storybook is saved to the `storage/app/public/generated_stories` directory.
   - Temporary extracted pages are cleaned up.

## How to Test

### Prerequisites
Before testing, make sure your local server and background queue worker are running:
1. Start the Queue Worker:
   ```bash
   php artisan queue:work
   ```
2. Start the Local Server:
   ```bash
   php artisan serve
   ```

### 1. Testing via Web Interface
- Navigate to the upload page (usually `http://localhost:8000/story/create`).
- Upload a photo of the child and fill in the requested details.
- Submit the form. The system will dispatch a background job, and your queue worker terminal will show the job executing.
- Once finished, the output PDF will be saved in `storage/app/public/generated_stories`.

### 2. Testing via Tinker (CLI)
You can directly test the background job via `artisan tinker` using dummy data (assuming `test_image.jpg` and `Design sans titre.pdf` exist in the project root):

```bash
php artisan tinker --execute="use App\Jobs\GeneratePersonalizedStory; GeneratePersonalizedStory::dispatch(base_path('test_image.jpg'), 'TestChild', base_path('Design sans titre.pdf'), 'Replace the baby character in this children\'s storybook illustration with the provided child character while keeping the original art style, colors, and background intact.');"
```
Check the `storage/app/public/generated_stories` directory once the queue worker finishes processing!