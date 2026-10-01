@extends('layouts.admin')

@section('content')
@php
    $config = [
        'title' => 'API keys',
        'intro' => "Keys are encrypted before they're stored. Saved keys show only their last 4 characters — leave a field untouched to keep the existing key. Use Test to verify each key.",
        'saveLabel' => 'Save keys',
        'sections' => [[
            'title' => 'AI provider keys',
            'note' => 'Add keys only for the engines you selected on the AI Engines page. Pollinations needs no key at all.',
            'keys' => [
                ['key' => 'openai_api_key',           'label' => 'OpenAI — DALL·E images, GPT text, TTS audio',      'provider' => 'openai',           'help_url' => 'https://platform.openai.com/api-keys'],
                ['key' => 'gemini_api_key',           'label' => 'Google Gemini — Imagen 3 images + Gemini text',    'provider' => 'gemini',           'help_url' => 'https://aistudio.google.com/apikey'],
                ['key' => 'claude_api_key',           'label' => 'Anthropic Claude — text tools',                    'provider' => 'claude',           'help_url' => 'https://console.anthropic.com/settings/keys'],
                ['key' => 'deepseek_api_key',         'label' => 'DeepSeek — text tools',                            'provider' => 'deepseek',         'help_url' => 'https://platform.deepseek.com/api_keys'],
                ['key' => 'mistral_api_key',          'label' => 'Mistral — text tools',                             'provider' => 'mistral',          'help_url' => 'https://console.mistral.ai/api-keys'],
                ['key' => 'groq_api_key',             'label' => 'Groq — very fast Llama text',                      'provider' => 'groq',             'help_url' => 'https://console.groq.com/keys'],
                ['key' => 'stable_diffusion_api_key', 'label' => 'Stability AI — images',                            'provider' => 'stable_diffusion', 'help_url' => 'https://platform.stability.ai/account/keys'],
                ['key' => 'clipdrop_api_key',         'label' => 'Clipdrop — background removal',                   'provider' => 'clipdrop',         'help_url' => 'https://clipdrop.co/apis'],
                ['key' => 'removebg_api_key',         'label' => 'remove.bg — background removal',                  'provider' => 'removebg',         'help_url' => 'https://www.remove.bg/api'],
                ['key' => 'elevenlabs_api_key',       'label' => 'ElevenLabs — text-to-audio voices',                'provider' => 'elevenlabs',       'help_url' => 'https://elevenlabs.io/app/settings/api-keys'],
            ],
        ]],
    ];
@endphp
@include('admin.partials.settings-form')
@endsection
