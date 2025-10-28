<div class="row justify-content-center">
    <div class="col-lg-10">

        @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="ideasTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list"
                            type="button" role="tab" aria-controls="list" aria-selected="false">
                            <i class="bi bi-collection me-1"></i> Ministry ideas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add"
                            type="button" role="tab" aria-controls="add" aria-selected="true">
                            <i class="bi bi-plus-circle me-1"></i> Add an idea
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="ideasTabContent">

                    <!-- LIST IDEAS TAB -->
                    <div class="tab-pane fade show active" id="list" role="tabpanel" aria-labelledby="list-tab">
                        @if($ideas->isEmpty())
                            <p class="text-muted text-center my-4">No ideas submitted yet.</p>
                        @else
                            <div class="list-group">
                                @foreach($ideas as $idea)
                                    <div class="list-group-item">
                                        <h5 class="mb-1">{{ $idea->description }}</h5>
                                        <small class="text-muted">Submitted by {{ $idea->email }} ({{ $idea->circuit->circuit ?? 'Unknown Circuit' }})</small>
                                        @if($idea->tags->isNotEmpty())
                                            <div class="mt-2">
                                                @foreach($idea->tags as $tag)
                                                    <span class="badge bg-secondary">{{ $tag->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($idea->image)
                                            <div class="mt-3">
                                                <img src="{{ asset('storage/' . $idea->image) }}" class="img-thumbnail" style="max-width:200px;">
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- ADD IDEA TAB -->
                    <div class="tab-pane fade" id="add" role="tabpanel" aria-labelledby="add-tab">

                        <form wire:submit.prevent="submit" enctype="multipart/form-data">
                            <!-- Circuit -->
                            <div class="mb-4">
                                <label class="form-label">Circuit <span class="text-danger">*</span></label>
                                <select wire:model="circuit_id" id="circuit_id_select"
                                    class="form-select @error('circuit_id') is-invalid @enderror">
                                    <option value="">Select a circuit...</option>
                                    @foreach($circuits as $c)
                                        <option value="{{ $c->id }}">{{ $c->circuit }}</option>
                                    @endforeach
                                </select>
                                @error('circuit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-4">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" wire:model="email" id="email_input"
                                    class="form-control @error('email') is-invalid @enderror">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea wire:model="description" rows="8"
                                    class="form-control @error('description') is-invalid @enderror"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            @if ($generatingAI)
                                <div class="alert alert-info py-2">
                                    <div class="spinner-border spinner-border-sm me-2"></div>Generating AI suggestions...
                                </div>
                            @endif

                            @if ($aiTitle || $aiDescription)
                                <div class="card border-secondary mb-4">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-stars me-1"></i>AI Suggestions</span>
                                        <button type="button" wire:click="generateAiSuggestions" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-arrow-clockwise"></i> Refresh
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        @if ($aiTitle)
                                            <h5 class="fw-bold mb-2">{{ $aiTitle }}</h5>
                                            <button type="button" wire:click="$set('idea', '{{ addslashes($aiTitle) }}')" class="btn btn-sm btn-outline-primary mb-3">
                                                <i class="bi bi-check2-circle"></i> Use this as title
                                            </button>
                                        @endif

                                        @if ($aiDescription)
                                            <p class="text-muted">{{ $aiDescription }}</p>
                                            <button type="button" wire:click="$set('description', '{{ addslashes($aiDescription) }}')" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-arrow-down-circle"></i> Replace my description
                                            </button>
                                        @endif

                                        <small class="text-muted d-block mt-2">
                                            You can edit your text after applying any suggestion.
                                        </small>
                                    </div>
                                </div>
                            @endif

                            <button type="button" wire:click="generateAiSuggestions" class="btn btn-outline-secondary btn-sm mb-3">
                                <i class="bi bi-magic"></i> Refresh AI Suggestions
                            </button>

                            <!-- Tags -->
                            <div class="mb-4">
                                <label class="form-label">Subjects <span class="text-danger">*</span></label>
                                <div class="mb-2">
                                    @foreach($tags as $i => $tag)
                                        <span class="badge bg-primary me-1 mb-1">
                                            {{ $tag }}
                                            <button type="button" wire:click="removeTag({{ $i }})"
                                                class="btn-close btn-close-white btn-sm"
                                                style="font-size: 0.7rem; vertical-align: middle;"></button>
                                        </span>
                                    @endforeach
                                </div>
                                <div style="position: relative;">
                                    <input type="text"
                                           wire:model.live.debounce.300ms="tagInput"
                                           wire:keydown.enter.prevent="addTag"
                                           placeholder="Type to search existing subjects or add new ones (press Enter)"
                                           class="form-control"
                                           autocomplete="off">

                                    @if($showTagDropdown && !empty($filteredTags))
                                        <div class="dropdown-menu show w-100"
                                             style="position: absolute; z-index: 1000; max-height: 200px; overflow-y: auto;">
                                            @foreach($filteredTags as $tag)
                                                <button type="button"
                                                        wire:click="selectTag('{{ $tag['name'] }}')"
                                                        class="dropdown-item">
                                                    {{ $tag['name'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="form-text">
                                    Select from existing subjects or type a new one and press Enter.
                                </div>
                                @error('tags') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Image -->
                            <div class="mb-4">
                                <label class="form-label">Image (Optional)</label>
                                <input type="file" wire:model="image"
                                    class="form-control @error('image') is-invalid @enderror">
                                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                <div wire:loading wire:target="image" class="mt-2">
                                    <small class="text-muted">Uploading...</small>
                                </div>

                                @if ($image)
                                    <div class="mt-3">
                                        <img src="{{ $image->temporaryUrl() }}" class="img-thumbnail"
                                             style="max-width:300px; max-height:300px;">
                                    </div>
                                @endif
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <span wire:loading.remove wire:target="submit">
                                        <i class="bi bi-send me-2"></i>Submit Ministry Idea
                                    </span>
                                    <span wire:loading wire:target="submit">
                                        <span class="spinner-border spinner-border-sm me-2"></span>Submitting...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:load', function () {
    let aiTimer = null;

    window.addEventListener('trigger-ai-generation', () => {
        clearTimeout(aiTimer);
        aiTimer = setTimeout(() => {
            Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
                .call('generateAiSuggestions');
        }, 1500); // wait 1.5 seconds after user stops typing
    });

    setTimeout(prefillFromCookies, 500);

    document.addEventListener('click', function(e) {
        const dropdown = document.querySelector('.dropdown-menu.show');
        const input = document.querySelector('input[wire\\:model\\.live\\.debounce\\.300ms="tagInput"]');
        if (dropdown && input && !input.contains(e.target) && !dropdown.contains(e.target)) {
            @this.set('showTagDropdown', false);
        }
    });
});
</script>
@endpush
