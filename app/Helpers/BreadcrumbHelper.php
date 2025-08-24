<?php

namespace App\Helpers;

class BreadcrumbHelper
{
    public static function getBreadcrumb($currentPage, $parentPages = [])
    {
        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => '/dashboard']
        ];

        // Add parent pages if any
        foreach ($parentPages as $parent) {
            $breadcrumb[] = [
                'title' => $parent['title'],
                'url' => $parent['url'] ?? null
            ];
        }

        // Add current page (without link)
        $breadcrumb[] = [
            'title' => $currentPage,
            'url' => null
        ];

        return $breadcrumb;
    }
}
