<div class="d-flex align-items-center justify-content-between gap-2">
    <div class="page-title-wrap mb-3">
        <h3 class="mb-2">{{translate('SEO Settings')}}</h3>
        <p class="text-muted">{{ translate('Complete the necessary setup to help users find your site') }}</p>
    </div>
</div>

<ul class="nav nav--tabs nav--tabs__style2 mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $webPage == 'error_logs' ? 'active' : '' }}" href="{{route('admin.business-settings.seo.setting', ['page_type' => 'error_logs'])}}">{{translate('404 Logs')}}</a>
    </li>
</ul>
