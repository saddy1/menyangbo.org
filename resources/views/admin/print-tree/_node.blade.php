{{--
  Recursive tree node partial.
  Variables: $node (array with person/spouses/children/depth), $showDepthLabel
--}}
@php
  $person  = $node['person'];
  $spouses = $node['spouses'];
  $kids    = $node['children'];
  $depth   = $node['depth'];

  $genderClass = match($person->gender ?? 'unknown') {
    'male'    => 'male',
    'female'  => 'female',
    default   => '',
  };
  $genderIcon = match($person->gender ?? 'unknown') {
    'male'    => '♂',
    'female'  => '♀',
    default   => '·',
  };
  $isDeceased = (bool)($person->is_deceased ?? false);

  $birthYear = $person->birth_date ? $person->birth_date->format('Y') : null;
  $deathYear = $person->death_date ? $person->death_date->format('Y') : null;
@endphp

<li class="tree-node depth-{{ $depth }}">

  {{-- ── Person card ── --}}
  <div class="inline-flex items-start flex-wrap gap-1">

    <div class="person-card {{ $genderClass }} {{ $isDeceased ? 'deceased' : '' }}">
      <span class="gender-icon" style="color: {{ $person->gender === 'male' ? '#3b82f6' : ($person->gender === 'female' ? '#ec4899' : '#94a3b8') }}">{{ $genderIcon }}</span>

      <div class="person-info">
        <div class="person-name">
          @if($isDeceased)<span style="color:#b91c1c; margin-right:3px;">†</span>@endif
          {{ $person->display_name }}
          @if($person->member_no)
            <span class="member-no">{{ $person->member_no }}</span>
          @endif
        </div>

        @if($person->display_name_np && $person->display_name_np !== $person->display_name)
          <div class="person-name-np">{{ $person->display_name_np }}</div>
        @endif

        <div class="person-meta">
          @if($person->pusta)
            <span><span class="pusta-badge">P{{ $person->pusta }}</span></span>
          @endif

          @if($birthYear || $deathYear)
            <span>
              @if($birthYear)b.{{ $birthYear }}@endif
              @if($birthYear && $deathYear) – @endif
              @if($deathYear)d.{{ $deathYear }}@endif
            </span>
          @endif

          @if($isDeceased && !$deathYear)
            <span class="deceased-badge">स्वर्गीय</span>
          @endif
        </div>
      </div>
    </div>

    {{-- ── Spouses inline ── --}}
    @if(count($spouses) > 0)
      <div class="spouse-wrap">
        @foreach($spouses as $sp)
          <span class="spouse-sep">×</span>
          <span class="spouse-link">
            {{ $sp->gender === 'male' ? '♂' : ($sp->gender === 'female' ? '♀' : '·') }}
            {{ $sp->display_name }}
          </span>
        @endforeach
      </div>
    @endif

    {{-- ── Generation depth marker (only on first generation shown) ── --}}
    @if($depth === 0)
      <span class="depth-label no-print">Root / जड</span>
    @endif

  </div>

  {{-- ── Children ── --}}
  @if(count($kids) > 0)
    <ul class="tree-children">
      @foreach($kids as $child)
        @include('admin.print-tree._node', ['node' => $child, 'showDepthLabel' => false])
      @endforeach
    </ul>
  @endif

</li>
