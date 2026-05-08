<div class="report-head">
    <h1 class="report-title">{{ $title ?? 'Report' }}</h1>
    @if(!empty($subtitle))
        <p class="report-subtitle">{{ $subtitle }}</p>
    @endif
</div>
