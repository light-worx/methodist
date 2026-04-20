<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Idea;
use App\Models\Tag;
use App\Models\Circuit;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lightworx\FilamentPwa\Facades\PushNotification;

class MinistryIdeaForm extends Component
{
    use WithFileUploads;

    // ── Form fields ──────────────────────────────────────────────────────────
    public $circuit_id;
    public $email;
    public $idea;
    public $description;
    public $image;
    public $tags = [];
    public $tagInput = '';

    // ── Data sources ─────────────────────────────────────────────────────────
    public $circuits;
    public $availableTags = [];

    // ── Tag dropdown ─────────────────────────────────────────────────────────
    public $filteredTags = [];
    public $showTagDropdown = false;

    // ── AI state ─────────────────────────────────────────────────────────────
    public $aiTitle             = null;
    public $aiDescription       = null;
    public $aiTags              = [];
    public $aiError             = null;
    public $titleAccepted       = false;
    public $descriptionAccepted = false;

    // ── List filtering ───────────────────────────────────────────────────────
    public string $search       = '';
    public ?string $filterTag   = null;

    // ── Idea detail view ─────────────────────────────────────────────────────
    public ?int $viewingIdeaId  = null;

    // ─────────────────────────────────────────────────────────────────────────

    public function mount($prefilledCircuit = null, $prefilledEmail = null): void
    {
        // Read circuit_id and email from the PWA device preference (custom_settings JSON),
        // falling back to any explicitly passed parameters.
        $pwaPreference = request()->pwaPreference;
        $settings      = $pwaPreference?->custom_settings ?? [];

        // custom_settings may be a JSON string (if not auto-cast) or already an array
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }

        $this->circuit_id = $prefilledCircuit ?? $settings['circuit_id'] ?? null;
        $this->email      = $prefilledEmail   ?? $settings['email']      ?? null;

        $this->circuits      = Circuit::orderBy('circuit')->get();
        $this->availableTags = Tag::orderBy('name')->get();
    }

    // ── AI generation ────────────────────────────────────────────────────────

    public function generateAiSuggestions(): void
    {
        $this->aiError = null;

        if (strlen(trim($this->description)) < 10) {
            $this->resetAi();
            return;
        }

        $existingTagNames = collect($this->availableTags)
            ->map(fn($t) => is_object($t) ? $t->name : $t['name'])
            ->implode(', ');

        try {
            $prompt = <<<PROMPT
You are helping Methodist church members document and share practical ministry ideas with other circuits and congregations.

The purpose is peer knowledge sharing — someone is describing something their church does or has tried, so that others can learn from it and adapt it for their own context. The tone should be informative and practical, written as one practitioner sharing with another. It should NOT sound like an invitation, advertisement, or event promotion.

Given the raw idea description below, return ONLY a valid JSON object with exactly three fields:
- "title": a clear, descriptive title that tells other churches what the idea is (maximum 8 words)
- "description": a well-written version of the idea (2–4 sentences) that explains what is done, how it works in practice, and why it is valuable — written as shared experience, not as an invitation
- "tags": an array of 2–5 relevant subject tag names; prefer tags from this existing list where appropriate: [{$existingTagNames}]; you may suggest new ones if none fit

Return ONLY the JSON object. No markdown, no code fences, no explanation.

Raw description: {$this->description}
PROMPT;

            $response = Http::timeout(15)
                ->connectTimeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.groq.key'),
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'       => 'llama-3.3-70b-versatile',
                    'temperature' => 0.7,
                    'max_tokens'  => 600,
                    'messages'    => [
                        [
                            'role'    => 'system',
                            'content' => 'You are an assistant that helps Methodist church members document practical ministry ideas for peer knowledge sharing. Always respond with a single raw JSON object and nothing else.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            if (in_array($response->status(), [401, 403])) {
                Log::error('Groq authentication failed', ['body' => $response->json()]);
                throw new \RuntimeException('AUTH_FAILED');
            }

            if ($response->status() === 429) {
                Log::warning('Groq rate limited', ['body' => $response->json()]);
                throw new \RuntimeException('RATE_LIMITED');
            }

            if ($response->failed()) {
                Log::error('Groq API error', ['status' => $response->status(), 'body' => $response->json()]);
                throw new \RuntimeException('Groq API error: ' . $response->status());
            }

            $raw = $response->json('choices.0.message.content', '');

            Log::debug('Groq raw response', ['raw' => $raw]);

            // Strip any accidental markdown code fences
            $cleaned = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
            $cleaned = preg_replace('/\s*```$/', '', $cleaned);
            $cleaned = trim($cleaned);

            $data = json_decode($cleaned, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data['title'])) {
                Log::warning('Groq JSON parse failed', [
                    'json_error' => json_last_error_msg(),
                    'cleaned'    => $cleaned,
                ]);
                throw new \RuntimeException('Could not parse Groq response as JSON.');
            }

            $this->aiTitle       = trim($data['title']);
            $this->aiDescription = trim($data['description'] ?? '');
            $this->aiTags        = array_map('trim', $data['tags'] ?? []);

            $this->titleAccepted       = false;
            $this->descriptionAccepted = false;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('Groq connection/timeout error: ' . $e->getMessage());
            $this->aiError = 'AI suggestions timed out. Please try again in a moment.';
            $this->resetAi();
        } catch (\Throwable $e) {
            Log::warning('Groq AI suggestion failed: ' . $e->getMessage());

            $this->aiError = match ($e->getMessage()) {
                'RATE_LIMITED' => 'Too many requests — please wait a moment and try again.',
                'AUTH_FAILED'  => 'AI suggestions are not configured correctly. Please contact the site administrator.',
                default        => 'AI suggestions are unavailable right now. You can continue filling in the form manually.',
            };

            $this->resetAi();
        }
    }

    // ── AI accept / reject ───────────────────────────────────────────────────

    public function acceptAiTitle(): void
    {
        $this->idea          = $this->aiTitle;
        $this->titleAccepted = true;
    }

    public function rejectAiTitle(): void
    {
        $this->aiTitle       = null;
        $this->titleAccepted = false;
    }

    public function acceptAiDescription(): void
    {
        $this->description          = $this->aiDescription;
        $this->descriptionAccepted  = true;
    }

    public function rejectAiDescription(): void
    {
        $this->aiDescription       = null;
        $this->descriptionAccepted = false;
    }

    public function acceptAiTag(string $tagName): void
    {
        if (!in_array($tagName, $this->tags)) {
            $this->tags[] = $tagName;
        }
        $this->aiTags = array_values(array_filter($this->aiTags, fn($t) => $t !== $tagName));
    }

    public function rejectAiTag(string $tagName): void
    {
        $this->aiTags = array_values(array_filter($this->aiTags, fn($t) => $t !== $tagName));
    }

    public function acceptAllAiTags(): void
    {
        foreach ($this->aiTags as $tagName) {
            if (!in_array($tagName, $this->tags)) {
                $this->tags[] = $tagName;
            }
        }
        $this->aiTags = [];
    }

    private function resetAi(): void
    {
        $this->aiTitle             = null;
        $this->aiDescription       = null;
        $this->aiTags              = [];
        $this->titleAccepted       = false;
        $this->descriptionAccepted = false;
    }

    // ── Tag input handling ───────────────────────────────────────────────────

    public function updatedTagInput($value): void
    {
        if (empty($value)) {
            $this->filteredTags    = [];
            $this->showTagDropdown = false;
            return;
        }

        $this->filteredTags = collect($this->availableTags)
            ->filter(fn($tag) => stripos(is_object($tag) ? $tag->name : $tag['name'], $value) !== false)
            ->take(10)
            ->map(fn($tag) => is_object($tag) ? ['id' => $tag->id, 'name' => $tag->name] : $tag)
            ->values()
            ->toArray();

        $this->showTagDropdown = count($this->filteredTags) > 0;
    }

    public function selectTag(string $tagName): void
    {
        if (!in_array($tagName, $this->tags)) {
            $this->tags[] = $tagName;
        }
        $this->tagInput        = '';
        $this->showTagDropdown = false;
        $this->filteredTags    = [];
    }

    public function addTag(): void
    {
        $tag = trim($this->tagInput);
        if ($tag && !in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
        $this->tagInput        = '';
        $this->showTagDropdown = false;
        $this->filteredTags    = [];
    }

    public function removeTag(int $index): void
    {
        unset($this->tags[$index]);
        $this->tags = array_values($this->tags);
    }

    // ── Validation ───────────────────────────────────────────────────────────

    protected $rules = [
        'idea'        => 'required|string|min:3',
        'circuit_id'  => 'required|exists:circuits,id',
        'email'       => 'required|email|max:199',
        'description' => 'required|string|min:10',
        'image'       => 'nullable|image|max:2048',
        'tags'        => 'required|array|min:1',
        'tags.*'      => 'string',
    ];

    protected $messages = [
        'circuit_id.required'  => 'Please select a circuit.',
        'circuit_id.exists'    => 'The selected circuit is invalid.',
        'email.required'       => 'Please provide your email address.',
        'email.email'          => 'Please provide a valid email address.',
        'description.required' => 'Please provide a description.',
        'description.min'      => 'The description must be at least 10 characters.',
        'image.image'          => 'The file must be an image.',
        'image.max'            => 'The image must not be larger than 2MB.',
        'tags.required'        => 'Please add at least one subject.',
        'tags.min'             => 'Please add at least one subject.',
    ];

    // ── Submission ───────────────────────────────────────────────────────────

    public function submit(): void
    {
        $this->validate();

        $imagePath = $this->image ? $this->image->store('ministry-ideas', 'public') : null;

        $idea = Idea::create([
            'idea'        => $this->idea,
            'circuit_id'  => $this->circuit_id,
            'email'       => $this->email,
            'description' => $this->description,
            'image'       => $imagePath,
            'published'   => false,
        ]);

        $tagIds = [];
        foreach ($this->tags as $tagInput) {
            if (is_numeric($tagInput)) {
                $tagIds[] = (int) $tagInput;
            } else {
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($tagInput)],
                    ['name' => $tagInput, 'type' => 'idea']
                );
                $tagIds[] = $tag->id;
            }
        }
        $idea->tags()->sync($tagIds);

        session()->flash('success', 'Thank you! Your ministry idea has been submitted and will be reviewed before publication.');
        $result = PushNotification::toPhone(
                    phone: env('ADMIN_PHONE'),
                    title: 'New ministry idea submitted',
                    body:  $idea->idea,
                    url:   '/',
                );
        $this->reset(['idea', 'description', 'image', 'tags', 'tagInput']);
        $this->resetAi();
    }

    // ── List actions ─────────────────────────────────────────────────────────

    public function viewIdea(int $id): void
    {
        $this->viewingIdeaId = $id;
    }

    public function clearDetail(): void
    {
        $this->viewingIdeaId = null;
    }

    public function filterByTag(string $tag): void
    {
        $this->filterTag     = $tag;
        $this->viewingIdeaId = null; // return to list when tag clicked from detail
    }

    public function clearFilter(): void
    {
        $this->filterTag = null;
        $this->search    = '';
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $ideas = Idea::with(['tags', 'circuit'])
            ->where('published', true)
            ->when($this->search, fn($q) =>
                $q->where('idea', 'like', '%' . $this->search . '%')
            )
            ->when($this->filterTag, fn($q) =>
                $q->whereHas('tags', fn($q2) =>
                    $q2->where('name', $this->filterTag)
                )
            )
            ->latest()
            ->get();

        $viewingIdea = $this->viewingIdeaId
            ? Idea::with(['tags', 'circuit'])->find($this->viewingIdeaId)
            : null;

        return view('livewire.ministry-idea-form', [
            'circuits'      => $this->circuits,
            'availableTags' => $this->availableTags,
            'ideas'         => $ideas,
            'viewingIdea'   => $viewingIdea,
        ]);
    }
}