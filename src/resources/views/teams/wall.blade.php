@extends('layouts.app')

@section('title', $team->name . ' — ' . __('messages.wall.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="mb-4">
        <a href="{{ route('teams.show', $team) }}" class="text-sm text-muted hover:underline">&larr; {{ __('messages.common.back') }}</a>
    </div>

    <h1 class="text-xl font-semibold mb-6">{{ $team->name }} — {{ __('messages.wall.title') }}</h1>

    {{-- New Post Form --}}
    <div class="card mb-6" x-data="{ postType: 'message', optionCount: 2 }">
        <div class="card-body">
            <form action="{{ route('team-posts.store', $team) }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="post_type" :value="postType">

                <div class="flex gap-2 mb-2">
                    <button type="button" @click="postType = 'message'"
                        :class="postType === 'message' ? 'btn-primary' : 'btn-secondary'" class="text-sm">
                        {{ __('messages.wall.message') }}
                    </button>
                    <button type="button" @click="postType = 'poll'"
                        :class="postType === 'poll' ? 'btn-primary' : 'btn-secondary'" class="text-sm">
                        {{ __('messages.wall.poll') }}
                    </button>
                </div>

                <textarea name="body" rows="2" class="form-input w-full"
                    :placeholder="postType === 'poll' ? '{{ __('messages.wall.poll_question') }}' : '{{ __('messages.wall.write_message') }}'"
                    required></textarea>

                <template x-if="postType === 'poll'">
                    <div class="space-y-2">
                        <template x-for="i in optionCount" :key="i">
                            <input type="text" :name="'poll_options[' + (i-1) + ']'" class="form-input w-full text-sm"
                                :placeholder="'{{ __('messages.wall.option') }} ' + i" required>
                        </template>
                        <button type="button" @click="optionCount = Math.min(optionCount + 1, 10)" class="text-sm text-primary hover:underline">
                            + {{ __('messages.wall.add_option') }}
                        </button>
                    </div>
                </template>

                <button type="submit" class="btn-primary text-sm">{{ __('messages.wall.post') }}</button>
            </form>
        </div>
    </div>

    {{-- Posts --}}
    @forelse($posts as $post)
        <div class="card mb-4">
            <div class="card-body">
                <div class="flex gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-xs font-medium shrink-0">
                        {{ strtoupper(mb_substr($post->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($post->user->last_name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-sm">{{ $post->user->full_name }}</span>
                            <span class="text-xs text-muted">{{ $post->created_at->diffForHumans() }}</span>
                            @if($post->user_id === auth()->id())
                                <form action="{{ route('team-posts.destroy', $post) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-muted hover:text-danger">{{ __('messages.common.delete') }}</button>
                                </form>
                            @endif
                        </div>
                        <p class="text-sm mt-1">{{ $post->body }}</p>
                    </div>
                </div>

                {{-- Poll options --}}
                @if($post->post_type === 'poll' && $post->pollOptions->isNotEmpty())
                    @php
                        $totalVotes = $post->pollOptions->sum(fn ($o) => $o->pollVotes->count());
                        $userVotedOption = $post->pollOptions->first(fn ($o) => $o->pollVotes->contains('user_id', auth()->id()));
                    @endphp
                    <div class="space-y-2 mb-3">
                        @foreach($post->pollOptions->sortBy('sort_order') as $option)
                            @php
                                $voteCount = $option->pollVotes->count();
                                $pct = $totalVotes > 0 ? round($voteCount / $totalVotes * 100) : 0;
                                $isVoted = $userVotedOption && $userVotedOption->id === $option->id;
                            @endphp
                            <div class="relative">
                                <div class="absolute inset-0 bg-primary-light rounded-lg" style="width: {{ $pct }}%"></div>
                                <div class="relative flex items-center justify-between px-3 py-2">
                                    <form action="{{ route('poll-votes.store', $option) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <button type="submit" class="text-sm {{ $isVoted ? 'font-semibold text-primary' : '' }}">
                                            {{ $option->label }}
                                        </button>
                                    </form>
                                    <span class="text-xs text-muted">{{ $voteCount }} ({{ $pct }}%)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-muted">{{ __('messages.wall.total_votes') }}: {{ $totalVotes }}</p>
                @endif

                {{-- Comments --}}
                @if($post->teamPostComments->isNotEmpty())
                    <div class="mt-3 pt-3 border-t border-border space-y-2">
                        @foreach($post->teamPostComments->sortBy('created_at') as $comment)
                            <div class="flex gap-2">
                                <div class="w-6 h-6 rounded-full bg-primary-light text-primary flex items-center justify-center text-[10px] font-medium shrink-0">
                                    {{ strtoupper(mb_substr($comment->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($comment->user->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="text-xs font-medium">{{ $comment->user->full_name }}</span>
                                    <span class="text-xs text-muted ml-1">{{ $comment->created_at->diffForHumans() }}</span>
                                    <p class="text-sm">{{ $comment->body }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Add comment --}}
                <div class="mt-3 pt-3 border-t border-border">
                    <form action="{{ route('team-post-comments.store', $post) }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="body" placeholder="{{ __('messages.comments.placeholder') }}"
                            class="form-input w-full text-sm" required>
                        <button type="submit" class="btn-ghost text-sm shrink-0">{{ __('messages.messages.send') }}</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.wall.no_posts') }}</p>
            </div>
        </div>
    @endforelse
@endsection
