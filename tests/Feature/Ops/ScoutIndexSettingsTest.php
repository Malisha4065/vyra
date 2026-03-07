<?php

it('configures meilisearch index settings for posts and users', function () {
    $settings = config('scout.meilisearch.index-settings');

    expect($settings)->toHaveKeys(['posts', 'users'])
        ->and($settings['posts']['filterableAttributes'])->toContain('hashtags')
        ->and($settings['posts']['rankingRules'])->toContain('desc(published_at)')
        ->and($settings['users']['searchableAttributes'])->toContain('username')
        ->and($settings['users']['rankingRules'])->toContain('asc(username)');
});
