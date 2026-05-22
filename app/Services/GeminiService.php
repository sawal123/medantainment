<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GeminiService
{
    /**
     * Generate SEO title and description using Gemini API or local fallback.
     */
    public static function generateSeo(string $title, string $content): array
    {
        $apiKey = env('GEMINI_API_KEY');

        // Clean content for local fallback and text processing
        $plainContent = strip_tags($content);
        $plainContent = html_entity_decode($plainContent);
        $plainContent = preg_replace('/\s+/', ' ', $plainContent);
        $plainContent = trim($plainContent);

        if (empty($apiKey)) {
            // Local fallback extraction
            $seoDescription = Str::limit($plainContent, 160, '...');

            return [
                'title' => $title,
                'description' => $seoDescription,
                'is_ai' => false,
            ];
        }

        try {
            $excerpt = Str::limit($plainContent, 1500);
            
            // Call official Gemini API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "You are an SEO expert. Generate a JSON response with 'title' (maximum 60 characters, catchy and optimized for SEO) and 'description' (maximum 160 characters, summaries the post, including a call to action if appropriate) for a blog post. Do not include markdown formatting or backticks, just raw JSON.
                                Title: {$title}
                                Content: {$excerpt}"
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $rawText = $response->json('candidates.0.content.parts.0.text');
                // Clean potential markdown wrappers if returned
                $cleanedJson = preg_replace('/```json\s*|```/', '', $rawText);
                $result = json_decode(trim($cleanedJson), true);

                if (isset($result['title']) && isset($result['description'])) {
                    return [
                        'title' => $result['title'],
                        'description' => $result['description'],
                        'is_ai' => true,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silently fail and use fallback
        }

        // Final fallback
        return [
            'title' => $title,
            'description' => Str::limit($plainContent, 160, '...'),
            'is_ai' => false,
        ];
    }
}
