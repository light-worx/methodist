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

                {{-- ── Tabs ── --}}
                <ul class="nav nav-tabs mb-4" id="ideasTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="list-tab" data-bs-toggle="tab"
                                data-bs-target="#list" type="button" role="tab"
                                aria-controls="list" aria-selected="true">
                            <i class="bi bi-collection me-1"></i> Ministry Ideas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-tab" data-bs-toggle="tab"
                                data-bs-target="#add" type="button" role="tab"
                                aria-controls="add" aria-selected="false">
                            <i class="bi bi-plus-circle me-1"></i> Add an Idea
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="ideasTabContent">

                    {{-- ══════════════════════════════════════════════ --}}
                    {{-- LIST IDEAS TAB                                 --}}
                    {{-- ══════════════════════════════════════════════ --}}
                    <div class="tab-pane fade show active" id="list" role="tabpanel" aria-labelledby="list-tab">
                        @if($ideas->isEmpty())
                            <p class="text-muted text-center my-4">No ideas have been published yet.</p>
                        @else
                            <div class="list-group">
                                @foreach($ideas as $idea)
                                    <div class="list-group-item list-group-item-action py-3">
                                        <div class="d-flex w-100 justify-content-between align-items-start">
                                            <b class="mb-1">{{ $idea->idea }}</b>
                                            <small class="text-muted ms-2 text-nowrap">
                                                {{ $idea->circuit->circuit ?? 'Unknown Circuit' }} {{$idea->circuit->reference ?? ''}}
                                            </small>
                                        </div>
                                        @if($idea->tags->isNotEmpty())
                                            <div class="mt-2">
                                                @foreach($idea->tags as $tag)
                                                    <span class="badge bg-secondary me-1">{{ $tag->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($idea->image)
                                            <div class="mt-3">
                                                <img src="{{ asset('storage/' . $idea->image) }}"
                                                     class="img-thumbnail" style="max-width: 200px;">
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- ══════════════════════════════════════════════ --}}
                    {{-- ADD IDEA TAB                                   --}}
                    {{-- ══════════════════════════════════════════════ --}}
                    <div class="tab-pane fade" id="add" role="tabpanel" aria-labelledby="add-tab">

                        <form wire:submit.prevent="submit">

                            {{-- Circuit --}}
                            <div class="mb-4">
                                <label class="form-label fw-medium">Circuit <span class="text-danger">*</span></label>
                                <select wire:model="circuit_id"
                                        class="form-select @error('circuit_id') is-invalid @enderror">
                                    <option value="">Select a circuit…</option>
                                    @foreach($circuits as $c)
                                        <option value="{{ $c->id }}">{{ $c->circuit }}</option>
                                    @endforeach
                                </select>
                                @error('circuit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="mb-4">
                                <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                                <input type="email" wire:model.lazy="email"
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Description + AI trigger --}}
                            <div class="mb-3">
                                <label class="form-label fw-medium">
                                    Describe Your Idea <span class="text-danger">*</span>
                                    <span class="text-muted fw-normal small">— write in your own words first</span>
                                </label>
                                <textarea wire:model="description" rows="6"
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Describe what your church does, how it works in practice, and why it has been valuable…"></textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- AI generate button --}}
                            <div class="mb-4">
                                <button type="button"
                                        wire:click="generateAiSuggestions"
                                        wire:loading.attr="disabled"
                                        wire:target="generateAiSuggestions"
                                        class="btn btn-outline-secondary btn-sm">
                                    <span wire:loading.remove wire:target="generateAiSuggestions">
                                        <i class="bi bi-stars me-1"></i>Get AI suggestions
                                    </span>
                                    <span wire:loading wire:target="generateAiSuggestions">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Generating…
                                    </span>
                                </button>
                                <span class="text-muted small ms-2">Suggests a title, refined description and subject tags.</span>
                            </div>

                            {{-- AI error --}}
                            @if($aiError)
                                <div class="alert alert-warning small py-2 mb-4" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $aiError }}
                                </div>
                            @endif

                            {{-- ── AI Suggestion Panel ── --}}
                            @if($aiTitle || $aiDescription || count($aiTags))
                                <div class="card border-primary mb-4">
                                    <div class="card-header bg-primary bg-opacity-10 text-primary d-flex align-items-center gap-2">
                                        <i class="bi bi-stars"></i>
                                        <span class="small fw-semibold text-uppercase">AI Suggestions</span>
                                        <button type="button" wire:click="generateAiSuggestions"
                                                wire:loading.attr="disabled" wire:target="generateAiSuggestions"
                                                class="btn btn-sm btn-outline-primary ms-auto">
                                            <i class="bi bi-arrow-clockwise"></i> Refresh
                                        </button>
                                    </div>
                                    <div class="card-body d-flex flex-column gap-3">

                                        {{-- Suggested title --}}
                                        @if($aiTitle && !$titleAccepted)
                                            <div class="border rounded p-3 bg-light">
                                                <p class="text-muted small mb-1 fw-medium">Suggested Title</p>
                                                <p class="fw-semibold mb-3">{{ $aiTitle }}</p>
                                                <div class="d-flex gap-2">
                                                    <button type="button" wire:click="acceptAiTitle"
                                                            class="btn btn-primary btn-sm">
                                                        <i class="bi bi-check-lg me-1"></i>Use this title
                                                    </button>
                                                    <button type="button" wire:click="rejectAiTitle"
                                                            class="btn btn-outline-secondary btn-sm">
                                                        <i class="bi bi-x-lg me-1"></i>Ignore
                                                    </button>
                                                </div>
                                            </div>
                                        @elseif($titleAccepted)
                                            <div class="d-flex align-items-center gap-2 text-success small fw-medium">
                                                <i class="bi bi-check-circle-fill"></i>
                                                Title applied — you can still edit it below.
                                            </div>
                                        @endif

                                        {{-- Suggested description --}}
                                        @if($aiDescription && !$descriptionAccepted)
                                            <div class="border rounded p-3 bg-light">
                                                <p class="text-muted small mb-1 fw-medium">Suggested Description</p>
                                                <p class="small lh-base mb-3">{{ $aiDescription }}</p>
                                                <div class="d-flex gap-2">
                                                    <button type="button" wire:click="acceptAiDescription"
                                                            class="btn btn-primary btn-sm">
                                                        <i class="bi bi-check-lg me-1"></i>Use this description
                                                    </button>
                                                    <button type="button" wire:click="rejectAiDescription"
                                                            class="btn btn-outline-secondary btn-sm">
                                                        <i class="bi bi-x-lg me-1"></i>Ignore
                                                    </button>
                                                </div>
                                            </div>
                                        @elseif($descriptionAccepted)
                                            <div class="d-flex align-items-center gap-2 text-success small fw-medium">
                                                <i class="bi bi-check-circle-fill"></i>
                                                Description applied — you can still edit it in the field above.
                                            </div>
                                        @endif

                                        {{-- Suggested tags --}}
                                        @if(count($aiTags))
                                            <div class="border rounded p-3 bg-light">
                                                <p class="text-muted small mb-2 fw-medium">Suggested Subjects</p>
                                                <div class="d-flex flex-wrap gap-2 mb-3">
                                                    @foreach($aiTags as $aiTag)
                                                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary fw-medium px-2 py-1"
                                                              style="font-size: 0.8rem;">
                                                            {{ $aiTag }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                                <div class="d-flex gap-2 flex-wrap">
                                                    <button type="button" wire:click="acceptAllAiTags"
                                                            class="btn btn-primary btn-sm">
                                                        <i class="bi bi-check-all me-1"></i>Accept all
                                                    </button>
                                                    @foreach($aiTags as $aiTag)
                                                        <button type="button" wire:click="acceptAiTag('{{ $aiTag }}')"
                                                                class="btn btn-outline-primary btn-sm">
                                                            + {{ $aiTag }}
                                                        </button>
                                                        <button type="button" wire:click="rejectAiTag('{{ $aiTag }}')"
                                                                class="btn btn-outline-secondary btn-sm">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            @endif

                            {{-- Idea title --}}
                            <div class="mb-4">
                                <label class="form-label fw-medium">
                                    Idea Title <span class="text-danger">*</span>
                                    <span class="text-muted fw-normal small">(edit or write your own)</span>
                                </label>
                                <input type="text" wire:model.live="idea"
                                       placeholder="A clear, descriptive title"
                                       class="form-control @error('idea') is-invalid @enderror">
                                @error('idea')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tags --}}
                            <div class="mb-4 position-relative">
                                <label class="form-label fw-medium">
                                    Subjects <span class="text-danger">*</span>
                                </label>

                                @if(count($tags))
                                    <div class="mb-2">
                                        @foreach($tags as $i => $tag)
                                            <span class="badge bg-primary me-1 mb-1 d-inline-flex align-items-center gap-1">
                                                {{ $tag }}
                                                <button type="button" wire:click="removeTag({{ $i }})"
                                                        class="btn-close btn-close-white"
                                                        style="font-size: 0.6rem;"
                                                        aria-label="Remove {{ $tag }}"></button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <input type="text"
                                       wire:model.live="tagInput"
                                       wire:keydown.enter.prevent="addTag"
                                       placeholder="Search or type a subject and press Enter…"
                                       class="form-control @error('tags') is-invalid @enderror"
                                       autocomplete="off">

                                @error('tags')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                @if($showTagDropdown && count($filteredTags))
                                    <div class="dropdown-menu show w-100 mt-1"
                                         style="position: absolute; z-index: 1050; max-height: 200px; overflow-y: auto;">
                                        @foreach($filteredTags as $tag)
                                            <button type="button"
                                                    wire:click="selectTag('{{ $tag['name'] }}')"
                                                    class="dropdown-item small py-2">
                                                {{ $tag['name'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="form-text">Select from existing subjects or type a new one and press Enter.</div>
                            </div>

                            {{-- Image --}}
                            <div class="mb-4">
                                <label class="form-label fw-medium">
                                    Image <span class="text-muted fw-normal small">(optional)</span>
                                </label>
                                <input type="file" wire:model="image" accept="image/*"
                                       class="form-control @error('image') is-invalid @enderror">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div wire:loading wire:target="image" class="mt-2">
                                    <small class="text-muted">
                                        <span class="spinner-border spinner-border-sm me-1"></span>Uploading…
                                    </small>
                                </div>
                                @if($image)
                                    <img src="{{ $image->temporaryUrl() }}" alt="Preview"
                                         class="img-thumbnail mt-2" style="max-width: 300px; max-height: 300px;">
                                @endif
                            </div>

                            {{-- Submit --}}
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary btn-lg px-5"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="submit">
                                        <i class="bi bi-send me-2"></i>Submit Ministry Idea
                                    </span>
                                    <span wire:loading wire:target="submit">
                                        <span class="spinner-border spinner-border-sm me-2"></span>Submitting…
                                    </span>
                                </button>
                            </div>

                        </form>
                    </div>{{-- end add tab --}}

                </div>{{-- end tab-content --}}
            </div>
        </div>
    </div>
</div>