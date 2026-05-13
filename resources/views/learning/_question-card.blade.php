{{--
    Reusable question card component for pretest and posttest.

    Variables:
        $question  — Question model instance (with choices loaded)
        $index     — 0-based position in question list
        $formName  — the HTML form id (e.g. "pretest-form")
--}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition hover:shadow-md"
     id="question-{{ $question->id }}">

    {{-- ── Listening: audio player ─────────────────────────────────────────── --}}
    @if($question->hasAudio())
        <div class="mb-4 p-3 bg-indigo-50 rounded-xl border border-indigo-100 flex items-center gap-3"
             x-data="audioPlayer('{{ Storage::disk('public')->url($question->audio_path) }}')"
             x-init="init()">

            {{-- Play / Pause button --}}
            <button type="button"
                    @click="toggle()"
                    :aria-label="playing ? 'Pause audio' : 'Play audio'"
                    class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center transition active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2">
                {{-- Play icon --}}
                <svg x-show="!playing && !loading" class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                {{-- Pause icon --}}
                <svg x-show="playing && !loading" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" style="display:none">
                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                </svg>
                {{-- Loading spinner --}}
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" style="display:none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </button>

            {{-- Progress bar --}}
            <div class="flex-1">
                <div class="text-xs font-semibold text-indigo-700 mb-1.5">🎧 Dengarkan audio terlebih dahulu</div>
                <div class="relative w-full h-1.5 bg-indigo-200 rounded-full overflow-hidden cursor-pointer"
                     @click="seek($event)">
                    <div class="absolute inset-y-0 left-0 bg-indigo-500 rounded-full transition-all"
                         :style="'width:' + progress + '%'"></div>
                </div>
            </div>

            {{-- Time --}}
            <span class="text-xs text-indigo-500 font-mono flex-shrink-0"
                  x-text="timeDisplay"></span>
        </div>
    @endif

    {{-- ── Question number & prompt ─────────────────────────────────────────── --}}
    <div class="flex gap-3 mb-4">
        <span class="flex-shrink-0 w-7 h-7 rounded-full bg-blue-100 text-blue-700 font-bold text-sm flex items-center justify-center">
            {{ $index + 1 }}
        </span>
        <p class="text-gray-800 font-medium leading-relaxed">{{ $question->prompt }}</p>
    </div>

    {{-- ── MCQ: radio choices ───────────────────────────────────────────────── --}}
    @if(!$question->isWritingType())
        <div class="space-y-2 pl-10">
            @foreach($question->choices as $choice)
                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer
                              hover:border-blue-400 hover:bg-blue-50 transition
                              has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                    <input type="radio"
                           name="answers[{{ $question->id }}]"
                           value="{{ $choice->id }}"
                           class="text-blue-600 focus:ring-blue-400"
                           required>
                    <span class="text-gray-700 text-sm">{{ $choice->text }}</span>
                </label>
            @endforeach
        </div>

    {{-- ── Writing / Typing: free text input ───────────────────────────────── --}}
    @else
        <div class="pl-10">
            <div class="relative">
                <input type="text"
                       name="answers[{{ $question->id }}]"
                       id="writing-{{ $question->id }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-gray-800 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent
                              placeholder-gray-400 transition"
                       placeholder="Ketik jawaban kamu di sini..."
                       autocomplete="off"
                       required>
                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-1.5 text-xs text-gray-400">
                💡 Penulisan besar/kecil tidak berpengaruh. Spasi di awal/akhir diabaikan.
            </p>
        </div>
    @endif
</div>
