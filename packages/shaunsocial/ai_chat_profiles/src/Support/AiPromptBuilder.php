<?php

namespace Packages\ShaunSocial\AiChatProfiles\Support;

use Carbon\Carbon;
use Packages\ShaunSocial\AiChatProfiles\DataObjects\AiChatMessage;
use Packages\ShaunSocial\AiChatProfiles\DataObjects\AiChatRequest;
use Packages\ShaunSocial\AiChatProfiles\Enums\AiMessageLength;
use Packages\ShaunSocial\AiChatProfiles\Models\AiPersonaConfig;
use Packages\ShaunSocial\Core\Models\Gender;
use Packages\ShaunSocial\Core\Models\User;

class AiPromptBuilder
{
    /**
     * @param  array<\Packages\ShaunSocial\Chat\Models\ChatMessage>  $history  Chronological order, text-only
     */
    public function build(AiPersonaConfig $persona, ?User $profile, array $history, ?User $sender = null): AiChatRequest
    {
        $systemText = $this->generateSystemPrompt($persona, $profile, $sender);

        $messages = [AiChatMessage::system($systemText)];

        foreach ($history as $msg) {
            $content = $msg->content;
            if ($content === null || $content === '') {
                continue;
            }

            if ((int) $msg->user_id === $profile->profile_id) {
                $messages[] = AiChatMessage::assistant($content);
            } else {
                $messages[] = AiChatMessage::user($content);
            }
        }

        return new AiChatRequest(
            messages: $messages,
            maxTokens: $this->resolveMaxTokens($persona->message_length),
        );
    }

    private function generateSystemPrompt(AiPersonaConfig $persona, ?User $profile = null, ?User $sender = null): string
    {
        $parts = ['You are roleplaying as a real person on a dating app. Stay in character at all times.'];

        if ($sender) {
            $senderInfo = [];
            if ($sender->name) {
                $senderInfo[] = "The person you are chatting with is named {$sender->name}.";
            }
            if ($sender->birthday) {
                $age = Carbon::parse($sender->birthday)->age;
                $senderInfo[] = "They are {$age} years old.";
            }
            if ($sender->gender_id) {
                $gender = Gender::find($sender->gender_id);
                if ($gender) {
                    $senderInfo[] = 'Their gender is ' . strtolower($gender->name) . '.';
                }
            }
            if ($sender->about) {
                $senderInfo[] = 'About them: ' . trim($sender->about);
            }
            if ($sender->language) {
                $parts[] = "Always reply in {$sender->language}.";
            }
            if ($senderInfo) {
                $parts[] = implode(' ', $senderInfo);
            }
        }

        if ($profile) {
            $identity = [];
            if ($profile->name) {
                $identity[] = "Your name is {$profile->name}.";
            }
            if ($profile->birthday) {
                $age = Carbon::parse($profile->birthday)->age;
                $identity[] = "You are {$age} years old.";
            }
            if ($profile->gender_id) {
                $gender = Gender::find($profile->gender_id);
                if ($gender) {
                    $identity[] = 'Your gender is '.strtolower($gender->name).'.';
                }
            }
            if ($profile->about) {
                $identity[] = 'About you: '.trim($profile->about);
            }
            if ($identity) {
                $parts[] = implode(' ', $identity);
            }
        }

        if ($persona->tone) {
            $parts[] = "Communicate in a {$persona->tone->value} tone.";
        }

        if ($persona->intent) {
            $parts[] = match ($persona->intent->value) {
                'serious'    => 'This profile is looking for a serious, long-term relationship.',
                'casual'     => 'This profile is open to casual connections.',
                'friendship' => 'This profile is interested in friendship.',
                default      => '',
            };
        }

        foreach ([
            'Playfulness'    => $persona->trait_playfulness,
            'Warmth'         => $persona->trait_warmth,
            'Assertiveness'  => $persona->trait_assertiveness,
        ] as $trait => $value) {
            if ($value > 0) {
                $parts[] = "{$trait}: {$this->traitLevel($value)}.";
            }
        }

        if ($persona->message_length) {
            $parts[] = match ($persona->message_length) {
                AiMessageLength::SHORT  => 'Keep replies concise — 1 to 2 sentences.',
                AiMessageLength::MEDIUM => 'Aim for 2 to 4 sentence replies.',
                AiMessageLength::LONG   => 'You may write longer, more detailed replies.',
            };
        }

        $safety = [
            'Never include external links or URLs.',
            'Avoid sensitive, offensive, or explicit content.',
            'Do not share or request personal contact information.',
        ];

        $parts[] = implode(' ', $safety);

        $parts[] = 'Respond only as the profile. Do not break character or reveal you are an AI.';

        if ($persona->custom_system_prompt) {
            $parts[] = $persona->custom_system_prompt;
        }

        return implode("\n", array_filter($parts));
    }

    private function traitLevel(int $value): string
    {
        return match (true) {
            $value >= 80 => 'very high',
            $value >= 60 => 'high',
            $value >= 40 => 'moderate',
            $value >= 20 => 'low',
            default      => 'minimal',
        };
    }

    private function resolveMaxTokens(?AiMessageLength $length): int
    {
        return match ($length) {
            AiMessageLength::SHORT  => 80,
            AiMessageLength::LONG   => 500,
            default                 => 200,
        };
    }
}
