<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Idea;
use App\Models\Tag;
use App\Models\Circuit;
use Illuminate\Support\Str;

class MinistryIdeaForm extends Component
{
    use WithFileUploads;

    public $circuit_id;
    public $email;
    public $idea;
    public $description;
    public $image;
    public $tags = [];
    public $ideas = [];
    public $tagInput = '';
    public $circuits;
    public $availableTags = [];
    public $filteredTags = [];
    public $showTagDropdown = false;
    public $aiTitle = null;
    public $aiDescription = null;
    public $generatingAI = false;

    public function mount($prefilledCircuit = null, $prefilledEmail = null)
    {
        $this->circuit_id = $prefilledCircuit;
        $this->email = $prefilledEmail;        
        $this->circuits = Circuit::orderBy('circuit')->get();
        $this->availableTags = Tag::orderBy('name')->get();
        $this->ideas = Idea::with('tags')->latest()->get();
    }

    public function generateAiSuggestions()
    {
        if (strlen($this->description) < 10) {
            $this->aiTitle = null;
            $this->aiDescription = null;
            return;
        }

        $this->generatingAI = true;

        try {
            $prompt = "You are helping church members refine community project ideas.
            Suggest a short, catchy title (max 8 words) and an improved version of this idea description.
            Return your answer as JSON with fields 'title' and 'description'.
            
            Idea description: {$this->description}";

            $response = DeepSeek::chat([
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an assistant that improves and titles ministry ideas.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $content = trim($response['choices'][0]['message']['content'] ?? '');

            // Try to decode JSON from model output
            $data = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($data['title'])) {
                $this->aiTitle = $data['title'];
                $this->aiDescription = $data['description'] ?? null;
            } else {
                // Fallback if response wasn't JSON
                $this->aiTitle = strtok($content, "\n");
                $this->aiDescription = $content;
            }
        } catch (\Throwable $e) {
            $this->aiTitle = null;
            $this->aiDescription = null;
        } finally {
            $this->generatingAI = false;
        }
    }

    public function updatedDescription($value)
    {
        // Reset AI fields whenever user edits their description
        $this->reset(['aiTitle', 'aiDescription']);
        $this->dispatch('trigger-ai-generation');
    }


    protected $rules = [
        'idea' => 'required|string|min:3',
        'circuit_id' => 'required|exists:circuits,id',
        'email' => 'required|email|max:199',
        'description' => 'required|string|min:10',
        'image' => 'nullable|image|max:2048',
        'tags' => 'required|array|min:1',
        'tags.*' => 'string',
    ];

    protected $messages = [
        'circuit_id.required' => 'Please select a circuit.',
        'circuit_id.exists' => 'The selected circuit is invalid.',
        'email.required' => 'Please provide your email address.',
        'email.email' => 'Please provide a valid email address.',
        'description.required' => 'Please provide a description.',
        'description.min' => 'The description must be at least 10 characters.',
        'image.image' => 'The file must be an image.',
        'image.max' => 'The image must not be larger than 2MB.',
        'tags.required' => 'Please add at least one subject.',
        'tags.min' => 'Please add at least one subject.',
    ];

    public function updatedTagInput($value)
    {
        if (empty($value)) {
            $this->filteredTags = [];
            $this->showTagDropdown = false;
            return;
        }
        // Filter available tags based on input
        $filtered = collect($this->availableTags)->filter(function($tag) use ($value) {
            // Handle both objects and arrays
            $tagName = is_object($tag) ? $tag->name : (is_array($tag) ? $tag['name'] : $tag);
            return stripos($tagName, $value) !== false;
        })->take(10)->map(function($tag) {
            // Normalize to array format
            return is_object($tag) ? ['id' => $tag->id, 'name' => $tag->name] : (is_array($tag) ? $tag : ['name' => $tag]);
        })->values()->toArray();
        $this->filteredTags = $filtered;
        $this->showTagDropdown = count($this->filteredTags) > 0;
    }

    public function selectTag($tagName)
    {
        if (!in_array($tagName, $this->tags)) {
            $this->tags[] = $tagName;
        }
        $this->tagInput = '';
        $this->showTagDropdown = false;
        $this->filteredTags = [];
    }

    public function addTag()
    {
        $tag = trim($this->tagInput);
        if ($tag && !in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
        $this->tagInput = '';
        $this->showTagDropdown = false;
        $this->filteredTags = [];
    }

    public function removeTag($index)
    {
        unset($this->tags[$index]);
        $this->tags = array_values($this->tags);
    }

    public function submit()
    {
        $this->validate();

        // Save image
        $imagePath = $this->image ? $this->image->store('ministry-ideas', 'public') : null;

        // Create idea
        $idea = Idea::create([
            'idea' => $this->idea,
            'circuit_id' => $this->circuit_id,
            'email' => $this->email,
            'description' => $this->description,
            'image' => $imagePath,
            'published' => false,
        ]);

        // Attach tags
        $tagIds = [];
        foreach ($this->tags as $tagInput) {
            if (is_numeric($tagInput)) {
                $tagIds[] = $tagInput;
            } else {
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($tagInput)],
                    ['type' => 'idea'],
                    ['name' => $tagInput]
                );
                $tagIds[] = $tag->id;
            }
        }

        $idea->tags()->sync($tagIds);

        // Set cookies for circuit and email (1 year expiry = 525600 minutes)
        // Using the correct Laravel cookie syntax
        $circuitCookie = cookie('user_circuit', $this->circuit_id, 525600, '/');
        $emailCookie = cookie('user_email', $this->email, 525600, '/');
        
        cookie()->queue($circuitCookie);
        cookie()->queue($emailCookie);

        // Flash success message
        session()->flash('success', 'Thank you! Your ministry idea has been submitted and will be reviewed before publication.');

        // Reset form except circuit_id and email
        $this->reset(['description', 'image', 'tags', 'tagInput']);
    }

    public function render()
    {
        return view('livewire.ministry-idea-form', [
            'circuits' => $this->circuits,
            'availableTags' => $this->availableTags,
        ]);
    }
}