<?php

namespace Azuriom\Plugin\Jobs\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\Permission;

class JobsServiceProvider extends BasePluginServiceProvider
{
    public function boot(): void
    {
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->registerRouteDescriptions();
        $this->registerAdminNavigation();
        $this->registerPermissions();
    }

    protected function routeDescriptions(): array
    {
        return [
            'jobs.index' => trans('jobs::messages.nav_title'),
        ];
    }

    protected function adminNavigation(): array
    {
        return [
            'jobs' => [
                'name' => trans('jobs::messages.admin_nav'),
                'type' => 'dropdown',
                'icon' => 'bi bi-person-badge',
                'route' => 'jobs.admin.*',
                'items' => [
                    'jobs.admin.applications.index' => [
                        'name' => trans('jobs::messages.admin_applications'),
                        'permission' => 'jobs.applications',
                    ],
                    'jobs.admin.positions.index' => [
                        'name' => trans('jobs::messages.admin_positions'),
                        'permission' => 'jobs.admin',
                    ],
                    'jobs.admin.settings.edit' => [
                        'name' => trans('jobs::messages.admin_settings'),
                        'permission' => 'jobs.admin',
                    ],
                ],
            ],
        ];
    }

    protected function registerPermissions(): void
    {
        Permission::registerPermissions([
            'jobs.admin' => 'jobs::messages.permission_admin',
            'jobs.applications' => 'jobs::messages.permission_applications',
        ]);
    }
}
