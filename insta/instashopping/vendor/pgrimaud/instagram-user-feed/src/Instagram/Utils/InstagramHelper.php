<?php

declare(strict_types=1);

namespace Instagram\Utils;

class InstagramHelper
{
    const URL_IG = 'https://www.instagram.com'; /** @todo IMPROVE ME LATER HEEHH */
    const URL_BASE = 'https://www.instagram.com/';
    const URL_AUTH = 'https://www.instagram.com/accounts/login/ajax/';
    const URL_MEDIA_DETAILED = 'https://www.instagram.com/p/';

    const QUERY_HASH_PROFILE = 'c9100bf9110dd6361671f113dd02e7d6';
    const QUERY_HASH_MEDIAS = '42323d64886122307be10013ad2dcc44';
    const QUERY_HASH_STORIES = '5ec1d322b38839230f8e256e1f638d5f';
    const QUERY_HASH_HIGHLIGHTS_FOLDERS = 'ad99dd9d3646cc3c0dda65debcd266a7';
    const QUERY_HASH_HIGHLIGHTS_STORIES = '5ec1d322b38839230f8e256e1f638d5f';

    const PAGINATION_DEFAULT = 12;
}
